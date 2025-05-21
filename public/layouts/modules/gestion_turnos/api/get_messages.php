<?php

require_once '../../../../../app/db/db.php';

// Cargar variables de entorno
cargarEntorno(dirname(__DIR__, 5) . '/.env'); // ✅ Sube dos niveles hasta SGH/

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
function decryptData($encryptedData) {
    global $ENCRYPTION_KEY, $IV_LENGTH;

    try {
        // Verificar si los datos contienen el formato correcto "IV:EncryptedData"
        if (strpos($encryptedData, ':') === false) {
            throw new Exception("El formato de los datos encriptados es incorrecto (falta ':').");
        }

        list($ivBase64, $encryptedText) = explode(':', $encryptedData, 2);

        if (!$encryptedText || !$ivBase64) {
            throw new Exception("El formato de los datos encriptados es incorrecto.");
        }

        // Decodificar IV y datos encriptados
        $iv = base64_decode($ivBase64);
        $encryptedBinary = base64_decode($encryptedText);

        // Verificar longitud del IV
        if (strlen($iv) !== $IV_LENGTH) {
            throw new Exception("Longitud del IV incorrecta.");
        }

        // Desencriptar los datos
        $decrypted = openssl_decrypt($encryptedBinary, 'aes-256-cbc', $ENCRYPTION_KEY, OPENSSL_RAW_DATA, $iv);

        // Verificar si la desencriptación fue exitosa
        if ($decrypted === false) {
            throw new Exception("No se pudo desencriptar el texto.");
        }

        return $decrypted ?: null;
    } catch (Exception $e) {
        return null;
    }
}

$db = new DB();
$pdo = $db->connect();

// Obtener el ID del chat
$chat_id = isset($_GET['chat_id']) ? $_GET['chat_id'] : null;
if (!$chat_id) {
    echo json_encode(["error" => "Falta el ID del chat."]);
    exit;
}

// Obtener los mensajes encriptados del chat
$sql = "SELECT 
            wsp_messages.id, 
            wsp_messages.numero, 
            wsp_messages.mensaje, 
            wsp_messages.remitente, 
            wsp_messages.timestamp, 
            wsp_messages.estado,
            personal.nombre AS remitente_agente
        FROM wsp_messages
        LEFT JOIN personal ON wsp_messages.remitente = personal.dni
        WHERE wsp_messages.chat_id = :chat_id";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(":chat_id", $chat_id, PDO::PARAM_INT);
$stmt->execute();
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Desencriptar los mensajes si el remitente es paciente
foreach ($messages as &$message) {
    // Solo desencriptar si el remitente es 'paciente'
    if ($message['remitente'] === 'paciente') {
        $decrypted_message = decryptData($message['mensaje']);
        if ($decrypted_message) {
            $message['mensaje'] = $decrypted_message;
            $message['mensaje'] = nl2br($message['mensaje']);
        } else {
            $message['mensaje'];
        }
    }
}

// Devolver los mensajes en formato JSON
echo json_encode($messages);

?>
