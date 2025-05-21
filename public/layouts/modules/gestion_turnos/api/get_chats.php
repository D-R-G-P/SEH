<?php

header('Content-Type: application/json');

require_once '../../../../../app/db/db.php';

// Cargar variables de entorno
cargarEntorno(dirname(__DIR__, 5) . '/.env'); // ✅ Sube dos niveles hasta SGH/

// header('Content-Type: application/json');

// Clave de encriptación (debe coincidir con Node.js)
$ENCRYPTION_KEY = isset($_ENV['ENCRYPTION_KEY']) ? $_ENV['ENCRYPTION_KEY'] : null;
$IV_LENGTH = 16; // Longitud del IV en AES-256-CBC

// Eliminar comillas alrededor de la clave de encriptación (si las hay)
if ($ENCRYPTION_KEY) {
    $ENCRYPTION_KEY = trim($ENCRYPTION_KEY, '"'); // Eliminar comillas dobles
}

// Verificar la longitud de la clave
if (strlen($ENCRYPTION_KEY) !== 32) {
    // Si la clave no es de 32 caracteres, generamos un hash truncado o hacemos padding
    if (strlen($ENCRYPTION_KEY) > 32) {
        $ENCRYPTION_KEY = substr($ENCRYPTION_KEY, 0, 32); // Truncamos si es más larga
    } else {
        // Hacemos padding con caracteres hasta llegar a 32
        $ENCRYPTION_KEY = str_pad($ENCRYPTION_KEY, 32, "0"); // O usa cualquier otro carácter para padding
    }
}

if (!$ENCRYPTION_KEY || strlen($ENCRYPTION_KEY) !== 32) {
    echo json_encode(["error" => "🔑 La clave de encriptación es inválida o no está configurada."]);
    exit;
}

/**
 * Desencripta un texto en formato "IV:EncryptedData" usando AES-256-CBC.
 * @param string $encryptedData Datos encriptados.
 * @return string|null Texto desencriptado o null en caso de error.
 */
function decryptData($encryptedData)
{
    global $ENCRYPTION_KEY, $IV_LENGTH;

    try {
        // Debug: Verificar el valor recibido
        // error_log("🔒 Intentando desencriptar los datos: " . $encryptedData); // Comentado para reducir logs

        // Verificar si los datos contienen el formato correcto "IV:EncryptedData"
        if (strpos($encryptedData, ':') === false) {
            // Si no tiene el formato esperado, podría ser un dato no encriptado o un error.
            // Dependiendo de tu lógica, podrías devolver el dato tal cual o null.
            // Aquí, asumimos que si no tiene el formato IV:Data, no es un dato encriptado válido.
            // throw new Exception("El formato de los datos encriptados es incorrecto (falta ':').");
            // error_log("❌ Formato de datos encriptados incorrecto (falta ':'). Datos: " . substr($encryptedData, 0, 100) . '...');
            return null; // Devolvemos null si el formato es incorrecto
        }

        list($ivBase64, $encryptedText) = explode(':', $encryptedData, 2);

        if (!$encryptedText || !$ivBase64) {
            // error_log("❌ El formato de los datos encriptados es incorrecto (partes vacías).");
            // throw new Exception("El formato de los datos encriptados es incorrecto.");
            return null; // Devolvemos null si las partes están vacías
        }

        // Decodificar IV y datos encriptados
        $iv = base64_decode($ivBase64);
        $encryptedBinary = base64_decode($encryptedText);

        // Debug: Verificar longitud del IV
        if (strlen($iv) !== $IV_LENGTH) {
            error_log("❌ Longitud del IV incorrecta. Se esperaba: $IV_LENGTH, pero se recibió: " . strlen($iv) . ". Datos: " . substr($encryptedData, 0, 100) . '...');
            // throw new Exception("Longitud del IV incorrecta.");
            return null; // Devolvemos null si la longitud del IV es incorrecta
        }

        // Desencriptar los datos
        $decrypted = openssl_decrypt($encryptedBinary, 'aes-256-cbc', $ENCRYPTION_KEY, OPENSSL_RAW_DATA, $iv);

        // Verificar si la desencriptación fue exitosa
        if ($decrypted === false) {
            error_log("❌ No se pudo desencriptar el texto con openssl_decrypt. Datos: " . substr($encryptedData, 0, 100) . '...');
            // throw new Exception("No se pudo desencriptar el texto.");
            return null; // Devolvemos null si openssl_decrypt falla
        }

        // Debug: Verificar el texto desencriptado
        // error_log("🔑 Desencriptación exitosa: " . $decrypted); // Comentado para reducir logs


        return $decrypted; // Devolvemos el texto desencriptado (puede ser una cadena vacía si el original era vacío)

    } catch (Exception $e) {
        error_log("❌ Error general al desencriptar: " . $e->getMessage() . ". Datos: " . substr($encryptedData, 0, 100) . '...');
        return null; // Devolvemos null en caso de cualquier otra excepción
    }
}


// --- Conexión a la Base de Datos ---
$db = new DB(); // Instancia de tu clase DB
$pdo = $db->connect(); // Obtiene la conexión PDO


// --- Obtener Parámetros de la Solicitud ---
$estado = $_GET['estado'] ?? 'chatting'; // Estado por defecto es 'chatting'
$dni_agente = $_GET['dni'] ?? null; // DNI del agente, necesario para 'chatting'
$adminMode = $_GET['adminMode'] ?? false; // Modo de administrador (para mostrar todos los chats)


// --- Validación de Parámetros ---
$estados_validos = ['finalizado', 'chatting', 'pendiente', 'agent_initiated'];
if (!in_array($estado, $estados_validos)) {
    echo json_encode(["error" => "Estado no válido proporcionado."]);
    error_log("❌ ERROR: Estado de chat no válido recibido: " . htmlspecialchars($estado));
    exit; // Detiene la ejecución si el estado no es válido
}

if (($estado === 'chatting' || $estado == 'agent_initiated') && ($dni_agente === null || $dni_agente === '') && !$adminMode) {
    // Para el estado 'chatting', el DNI del agente es obligatorio para filtrar chats asignados
    echo json_encode(["error" => "El DNI del agente es requerido para obtener chats en estado 'chatting'."]);
    error_log("❌ ERROR: DNI del agente no proporcionado para estado 'chatting'.");
    exit; // Detiene la ejecución
}


// --- Construcción de la Consulta SQL ---
$sql = ""; // Inicializa la variable SQL

if ($estado === 'chatting' || $estado === 'agent_initiated') {
    // Consulta para chats en estado 'chatting' asignados a un agente específico
    $sql = "SELECT c.id, c.numero, c.paciente_id,
            p.profile_pic AS profile_pic, p.nombres AS nombres, p.apellidos AS apellidos, p.nombre_autopercibido AS nombre_autopercibido,
            c.estado
            FROM chats c
            LEFT JOIN pacientes_chat p ON c.paciente_id = p.id -- ✅ Usamos LEFT JOIN para incluir chats sin paciente
            WHERE c.asignado = :dni AND (c.estado = 'chatting' OR c.estado = 'agent_initiated')"; // Filtra por agente asignado y estado 'chatting' o 'agent_initiated'

            if ($adminMode == true) {
                $sql = "SELECT c.id, c.numero, c.paciente_id,
            p.profile_pic AS profile_pic, p.nombres AS nombres, p.apellidos AS apellidos, p.nombre_autopercibido AS nombre_autopercibido,
            c.estado, c.asignado, pe.nombre AS nombre_agente
            FROM chats c
            LEFT JOIN pacientes_chat p ON c.paciente_id = p.id -- ✅ Usamos LEFT JOIN para incluir chats sin paciente
            LEFT JOIN personal pe ON c.asignado = pe.dni 
            WHERE c.estado = 'chatting' OR c.estado = 'agent_initiated'"; // Filtra por estado 'chatting' o 'agent_initiated'
            }

} elseif ($estado === 'pendiente') {
    // Consulta para chats en estado 'pendiente' (usualmente sin asignar)
// Incluimos la condición estado = 'pendiente' en el WHERE para la tabla chats
    $sql = "SELECT c.id, c.numero, c.paciente_id,
            p.profile_pic AS profile_pic, p.nombres AS nombres, p.apellidos AS apellidos, p.nombre_autopercibido AS nombre_autopercibido,
            c.estado
            FROM chats c
            LEFT JOIN pacientes_chat p ON c.paciente_id = p.id -- ✅ Usamos LEFT JOIN para incluir chats sin paciente
            WHERE c.estado = 'pendiente'"; // Filtra solo por estado 'pendiente'

} elseif ($estado === 'finalizado') {
    // Consulta para chats en estado 'finalizado' (todos pueden verlos)
    // Incluimos la condición estado = 'finalizado' en el WHERE para la tabla chats
    // ✅ CORRECCIÓN: Definimos la consulta base para finalizado sin ORDER BY ni LIMIT aquí
    $sql = "SELECT c.id, c.numero, c.paciente_id, c.fecha_cierre,
        p.profile_pic AS profile_pic, p.nombres AS nombres, p.apellidos AS apellidos, p.nombre_autopercibido AS nombre_autopercibido,
        c.estado, c.asignado, pe.nombre AS nombre_agente
        FROM chats c
        LEFT JOIN pacientes_chat p ON c.paciente_id = p.id
        LEFT JOIN personal pe ON c.asignado = pe.dni 
        WHERE c.estado = 'finalizado'"; // Filtra solo por estado 'finalizado'

}

// Ordenar los resultados (ej. por fecha de creación del chat)
// ✅ Mantenemos la concatenación de ORDER BY como estaba en tu código original
$sql .= " ORDER BY c.id DESC";

// ✅ Añadir LIMIT 50 SOLO si el estado es 'finalizado'
if ($estado === 'finalizado' && !$adminMode) {
    $sql .= " LIMIT 50";
}


// --- Preparar y Ejecutar la Consulta ---
// error_log("SQL Final: " . $sql); // Debug: Loguear la consulta final antes de preparar
$stmt = $pdo->prepare($sql);

// Solo se enlaza el DNI si el estado es 'chatting' (ya que la query lo usa)
if (($estado === 'chatting' || $estado === 'agent_initiated') && !$adminMode) {
    $stmt->bindParam(":dni", $dni_agente, PDO::PARAM_STR); // Enlaza el DNI del agente
}

$stmt->execute(); // Ejecuta la consulta
$chats = $stmt->fetchAll(PDO::FETCH_ASSOC); // Obtiene todos los resultados como array asociativo


// --- Procesar Resultados (Desencriptar y Formatear) ---
// Usamos una referencia (&) para modificar el array $chats directamente
foreach ($chats as &$chat) {

    // ✅ Verificar si hay datos de paciente asociados (LEFT JOIN encontró match)
    // Si $chat['paciente_id'] es NULL o 0, o no encontró match, $chat['nombres'] será NULL
    if (isset($chat['nombres']) && $chat['nombres'] !== null) { // ✅ Añadido isset check
        // --- Hay datos de paciente, intentar desencriptar ---
        $nombre = decryptData($chat['nombres']);
        $apellido = decryptData($chat['apellidos']);
        $nombre_autopercibido = isset($chat['nombre_autopercibido']) ? decryptData($chat['nombre_autopercibido']) : null; // ✅ Añadido isset check

        // Manejar casos donde la desencriptación falló o los datos originales eran null/vacío
        $nombre = $nombre ?? ''; // Usa '' si desencriptación falló o dato original era null/vacío
        $apellido = $apellido ?? '';
        $nombre_autopercibido = $nombre_autopercibido ?? '';
        // La foto de perfil también puede ser null si el paciente no tiene una guardada
        $profile_pic = isset($chat['profile_pic']) ? decryptData($chat['profile_pic']) : null; // ✅ Añadido isset check
        // Usar una imagen por defecto si no hay foto de perfil o falló desencriptación
        $profile_pic = $profile_pic ?? "https://i.pinimg.com/236x/d9/d8/8e/d9d88e3d1f74e2b8ced3df051cecb81d.jpg"; // Ajusta URL por defecto


        // Asignar el nombre completo para mostrar: usar autopercibido si existe, sino Apellido Nombre
        // Usamos trim() para limpiar espacios extra
        $chat['nombre_paciente'] = $nombre_autopercibido !== '' ? $nombre_autopercibido : trim("$apellido $nombre");
        $chat['profile_pic'] = $profile_pic;

    } else {
        // --- No hay datos de paciente asociados (paciente_id es NULL/0 o no hay match) ---
        // console.log(`[obtener_chats] Chat ID ${chat['id']} sin paciente asociado. Usando datos por defecto.`);
        $chat['nombre_paciente'] = "Usuario Desconocido"; // Nombre por defecto
        $chat['profile_pic'] = "https://i.pinimg.com/236x/d9/d8/8e/d9d88e3d1f74e2b8ced3df051cecb81d.jpg"; // Foto por defecto
    }

    // Eliminar los campos encriptados o brutos que no se deben enviar al frontend
    unset($chat['nombres'], $chat['apellidos'], $chat['nombre_autopercibido']); // Estos campos ya no se usan después de procesar


    // --- Obtener mensajes no leídos para chats en estado 'chatting' ---
    // Esta lógica estaba dentro del foreach en tu código original, lo cual es ineficiente
    // si tienes muchos chats en estado chatting, ya que hace una query por cada chat.
    // Sin embargo, para mantener la funcionalidad, la dejo aquí, pero considera optimizarla.
    // Nota: Basándonos en tu Node.js, los mensajes entrantes se guardan con estado 'recibido' y open=1.
    // Asumo que "no leído" significa open=1. Ajusta la condición si tu interfaz usa otro valor/columna.
    if ($estado == 'chatting' || $estado == 'agent_initiated') {
        // Contar mensajes no leídos para este chat específico
        // ✅ Corregida la consulta para contar mensajes con open = 1
        $nchat = "SELECT COUNT(id) AS unread_count FROM wsp_messages WHERE chat_id = :chat_id AND remitente = 'paciente' AND `open` IS NULL";
        $stmt_nchat = $pdo->prepare($nchat);
        $stmt_nchat->bindParam(":chat_id", $chat['id'], PDO::PARAM_INT);
        $stmt_nchat->execute();
        $unreadResult = $stmt_nchat->fetch(PDO::FETCH_ASSOC);
        $chat['unread_messages_count'] = (int) $unreadResult['unread_count']; // Añadir el conteo (como entero)
        // Si usas 'open IS NULL' en Node.js, cambia `= 1` a `IS NULL` aquí.
        // Pero si Node.js guarda '1', la condición debe ser ' = 1'.
        // Dejé `= 1` basado en la versión final de tu código Node.js que envía '1'.
    }
    // La clave 'paciente_id' puede ser útil en el frontend, así que la mantenemos
    // unset($chat['paciente_id']); // Opcional: eliminar paciente_id si no lo necesita el frontend
}

// Eliminar la referencia al último elemento después del bucle
unset($chat);

// Debug: Ver los resultados antes de enviarlos
// error_log("📤 Respuesta JSON (parcial): " . substr(json_encode($chats), 0, 500) . '...'); // Log parcial para evitar logs muy largos


// --- Enviar Respuesta JSON ---
echo json_encode($chats);

?>