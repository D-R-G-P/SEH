<?php

// send_files.php - Script para recibir mensajes y archivos enviados por AJAX FormData

// 1. Configuración inicial
// ========================
// Configurar la visualización de errores (útil para depuración, desactivar en producción)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluir archivos necesarios (asegúrate de que las rutas son correctas)
// Estas líneas ya las has añadido
require_once '../../../../../app/db/db.php'; // Conexión a base de datos
require_once '../../../../../app/db/user_session.php'; // Manejo de sesión de usuario
require_once '../../../../../app/db/user.php'; // Funciones relacionadas con usuarios
require_once '../../../../config.php'; // Archivo de configuración (si contiene settings relevantes)

// Establecer la cabecera para la respuesta JSON
header('Content-Type: application/json');

// Función para generar un UUID v4 (Universally Unique Identifier)
// Se basa en la recomendación RFC 4122
// Adaptada para funcionar con diferentes versiones de PHP
if (!function_exists('generateUuidV4')) {
    function generateUuidV4()
    {
        // Generar bytes aleatorios de forma segura
        if (function_exists('random_bytes')) {
            $data = random_bytes(16);
        } elseif (function_exists('openssl_random_pseudo_bytes')) {
            $data = openssl_random_pseudo_bytes(16);
        } else {
            // Fallback menos seguro si las funciones de criptografía no están disponibles
            // NO RECOMENDADO para entornos de alta seguridad
            $data = mt_rand(0, 0xffff) . mt_rand(0, 0xffff) . mt_rand(0, 0xffff);
            $data .= mt_rand(0, 0x0fff) | 0x4000; // Version 4
            $data .= mt_rand(0, 0x3fff) | 0x8000; // Varianta RFC 4122
            $data .= mt_rand(0, 0xffff) . mt_rand(0, 0xffff) . mt_rand(0, 0xffff);
            // Asegurar que $data tenga 16 bytes para el formato correcto
            $data = pack("H*", md5($data)); // Usamos md5 solo para obtener 16 bytes, no por seguridad criptográfica
        }

        // Establecer los bits de la versión y la variante
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10

        // Formatear como UUID (xxxx-xx-xx-xx-xxxxxx)
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}


// Definir el directorio base donde se guardarán los archivos.
// ¡IMPORTANTE! Este directorio debe existir y tener permisos de escritura para el servidor web.
// Considera usar una ruta fuera del directorio raíz del documento web por seguridad.
// La ruta que has puesto parece indicar una estructura de directorios específica de tu proyecto.
$baseUploadDir = '../../../../../app/whatsapp_files';

// 2. Preparar el directorio del día
// =================================
// Crear la ruta completa incluyendo el directorio del día de hoy (YYYY-MM-DD)
date_default_timezone_set('America/Argentina/Buenos_Aires'); // Establecer la zona horaria de Argentina
$todayDir = $baseUploadDir . DIRECTORY_SEPARATOR . date('Y-m-d');

// Asegurarse de que el directorio del día de hoy existe. Si no, intentar crearlo.
// El tercer parámetro 'true' en mkdir permite crear directorios anidados si es necesario.
// Se usan permisos 0777 para máxima compatibilidad, pero considera permisos más restrictivos como 0755
// si sabes que el usuario del servidor web tiene los permisos adecuados.
if (!is_dir($todayDir)) {
    // Si no existe la carpeta con el día de hoy, intentar crearla
    // mkdir(ruta, permisos, recursivo?)
    if (!mkdir($todayDir, 0777, true)) {
        // Si falla la creación del directorio, registrar un error y responder.
        error_log("Error: No se pudo crear el directorio diario: " . $todayDir);
        echo json_encode(['success' => false, 'error' => 'Error interno del servidor al configurar la subida de archivos (directorio diario).']);
        exit;
    }
    // Opcional: ajustar permisos después de crear si 0777 es demasiado permisivo inicialmente
    // chmod($todayDir, 0755);
}

// Verificar que el directorio del día de hoy es escribible
if (!is_writable($todayDir)) {
    // Si no se puede escribir en el directorio, registrar un error y responder.
    error_log("Error: Directorio diario no escribible: " . $todayDir);
    echo json_encode(['success' => false, 'error' => 'Error interno del servidor: El directorio del día no tiene permisos de escritura.']);
    exit;
}


// El directorio de subida final para esta petición será el directorio del día
$uploadDir = $todayDir;

// La línea de depuración que tenías:
// echo $uploadDir . " - - - "; // Para depuración, puedes comentar esta línea en producción


// 3. Verificar el método de la petición
// ======================================
// Asegurarse de que la petición es POST, como espera la función sendMessage
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Si no es POST, responder con un error
    echo json_encode(['success' => false, 'error' => 'Método de petición no permitido.']);
    exit; // Detener la ejecución
}

// 4. Recibir y sanitizar los datos de texto
// =========================================
// Los datos de texto vienen en $_POST cuando se usa FormData
$numero = $_POST['numero'] ?? null; // Número del paciente
$chatId = $_POST['chat_id'] ?? null; // ID del chat actual
$remitente = $_POST['remitente'] ?? null; // Identificador del remitente (DNI)
$mensaje = $_POST['mensaje'] ?? ''; // Contenido del mensaje de texto (puede estar vacío)

// Sanitizar las entradas para prevenir inyecciones de código o ataques
// Usar htmlspecialchars para limpiar cadenas destinadas a ser mostradas en HTML
// O usar filter_var para tipos específicos (ej: números)
// Para datos que irán a la DB, usa consultas preparadas, la sanitización aquí es para otras salidas o validaciones tempranas.
$numero = htmlspecialchars($numero, ENT_QUOTES, 'UTF-8');
$chatId = htmlspecialchars($chatId, ENT_QUOTES, 'UTF-8');
$remitente = htmlspecialchars($remitente, ENT_QUOTES, 'UTF-8');
$mensaje = htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8');


// 5. Validar datos requeridos
// ===========================
// Verificar que los datos esenciales para identificar el chat y remitente estén presentes
if (empty($numero) || empty($chatId) || empty($remitente)) {
    echo json_encode(['success' => false, 'error' => 'Datos requeridos (número, chat_id, remitente) faltantes.']);
    exit; // Detener la ejecución
}

// 6. Procesar los archivos subidos
// =================================
// $_FILES contiene la información de los archivos subidos.
// Iteramos sobre $_FILES buscando las claves que empiezan con 'archivo_'
$uploadedFilesInfo = []; // Array para almacenar información de archivos subidos exitosamente (para la DB)
$errors = []; // Array para almacenar errores de procesamiento de archivos (para la respuesta)

// Comprobar si se subieron archivos
if (!empty($_FILES)) {
    foreach ($_FILES as $key => $file) {
        // Verificar si la clave del archivo coincide con el formato esperado (archivo_X)
        if (strpos($key, 'archivo_') === 0) {

            // Verificar si no hay errores de subida (UPLOAD_ERR_OK es 0)
            if ($file['error'] === UPLOAD_ERR_OK) {

                $originalFileName = basename($file['name']); // Obtener solo el nombre del archivo
                $fileExtension = pathinfo($originalFileName, PATHINFO_EXTENSION); // Obtener la extensión (sin el punto)

                // Generar un nombre de archivo único usando UUID v4
                // El nombre del archivo será el UUID + la extensión original
                $uuid = generateUuidV4();
                $uniqueFileName = $uuid . '.' . strtolower($fileExtension); // Usamos la extensión en minúsculas por consistencia

                // Construir la ruta completa donde se guardará el archivo temporalmente
                $targetFilePath = $uploadDir . DIRECTORY_SEPARATOR . $uniqueFileName;


                // Validaciones de archivo: Tipo y Tamaño
                // ======================================
                // Lista de tipos MIME permitidos según tu requerimiento: imágenes, pdf, word, powerpoint, excel
                $allowedTypes = [
                    // Imágenes
                    'image/jpeg',
                    'image/png',
                    'image/gif',
                    'image/webp', // Añadimos webp que es común
                    'image/svg+xml', // Añadimos SVG si aplica

                    // Documentos
                    'application/pdf',
                    'application/msword', // .doc
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // .docx
                    'application/vnd.ms-powerpoint', // .ppt
                    'application/vnd.openxmlformats-officedocument.presentationml.presentation', // .pptx
                    'application/vnd.ms-excel', // .xls
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // .xlsx
                    // Considera añadir otros si es necesario, ej: .csv, .txt, etc.
                    'text/csv',
                    // 'text/plain',
                ];

                // Tamaño máximo de archivo permitido (Ejemplo: 10 MB)
                $maxFileSize = 10 * 1024 * 1024; // 10 MB


                // Validar el tipo MIME (usando el proporcionado por el navegador - es básico, ver nota de seguridad)
                // NOTA DE SEGURIDAD: El tipo MIME proporcionado por el navegador ($file['type']) es fácilmente falsificable.
                // Para una validación robusta, VERIFICA EL TIPO REAL DEL ARCHIVO DESPUÉS DE LA SUBIDA
                // usando funciones como finfo_open() o mime_content_type().
                if (!in_array($file['type'], $allowedTypes)) {
                    $errors[] = "Archivo '{$originalFileName}': Tipo de archivo no permitido ({$file['type']}).";
                    // Opcional: podrías intentar verificar la extensión si confías más en ella para ciertos tipos
                    // $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx'];
                    // if (!in_array(strtolower($fileExtension), $allowedExtensions)) { ... }
                    continue; // Saltar al siguiente archivo si el tipo no es permitido
                }

                // Validar el tamaño del archivo
                if ($file['size'] > $maxFileSize) {
                    $errors[] = "Archivo '{$originalFileName}': Tamaño excede el límite de " . ($maxFileSize / 1024 / 1024) . " MB.";
                    continue; // Saltar al siguiente archivo si es demasiado grande
                }

                // Mover el archivo subido desde la ubicación temporal a nuestro directorio diario
                // move_uploaded_file es la forma segura de manejar subidas
                // Se mueve a la ruta $targetFilePath que incluye el directorio del día y el nombre UUID
                if (move_uploaded_file($file['tmp_name'], $targetFilePath)) {
                    // Si se movió correctamente, almacenar la información para la base de datos
                    $uploadedFilesInfo[] = [
                        'original_name' => $originalFileName,
                        'saved_path' => $targetFilePath, // Ruta completa del archivo guardado
                        'file_type' => $file['type'], // Tipo MIME reportado por el navegador
                        'file_size' => $file['size'],
                        'uuid' => $uuid // Guardamos el UUID también si es necesario en la DB
                    ];
                } else {
                    // Si hubo un error al mover el archivo (ej: permisos, espacio en disco)
                    $errors[] = "Archivo '{$originalFileName}': Error al guardar el archivo en el servidor.";
                    // Considerar eliminar el archivo temporal si move_uploaded_file falló por alguna razón inesperada
                    // if (is_uploaded_file($file['tmp_name'])) { unlink($file['tmp_name']); }
                }

            } else {
                // Manejar otros errores de subida según el código de error de $file['error']
                // Listado de códigos de error: https://www.php.net/manual/es/features.file-upload.errors.php
                switch ($file['error']) {
                    case UPLOAD_ERR_INI_SIZE:
                    case UPLOAD_ERR_FORM_SIZE:
                        $errors[] = "Archivo '" . htmlspecialchars(basename($file['name']), ENT_QUOTES, 'UTF-8') . "': El tamaño excede el límite permitido por el servidor.";
                        break;
                    case UPLOAD_ERR_PARTIAL:
                        $errors[] = "Archivo '" . htmlspecialchars(basename($file['name']), ENT_QUOTES, 'UTF-8') . "': La subida fue parcial.";
                        break;
                    case UPLOAD_ERR_NO_FILE:
                        // Esto no debería pasar si la clave existe en $_FILES y no hay archivos
                        $errors[] = "Error interno: No se recibió archivo para el campo " . htmlspecialchars($key, ENT_QUOTES, 'UTF-8') . ".";
                        break;
                    case UPLOAD_ERR_NO_TMP_DIR:
                        $errors[] = "Error interno del servidor: Falta el directorio temporal para subidas.";
                        break;
                    case UPLOAD_ERR_CANT_WRITE:
                        $errors[] = "Error interno del servidor: Fallo al escribir el archivo en disco.";
                        break;
                    case UPLOAD_ERR_EXTENSION:
                        $errors[] = "Error interno del servidor: Una extensión de PHP detuvo la subida.";
                        break;
                    default:
                        $errors[] = "Archivo '" . htmlspecialchars(basename($file['name']), ENT_QUOTES, 'UTF-8') . "': Error de subida desconocido (código: {$file['error']}).";
                        break;
                }
            }
        }
    }
}


// 7. Implementar lógica de base de datos y guardado final de archivos
// ===================================================================
// *** ESTA ES LA SECCIÓN QUE DEBES ADAPTAR A TU PROYECTO ***
// Aquí debes usar la conexión a tu base de datos (ya tienes includeds al inicio)
// e insertar los datos del mensaje y los archivos subidos exitosamente ($uploadedFilesInfo).

// --- COMIENZO DE LA SECCIÓN DE BASE DE DATOS Y LÓGICA DE NEGOCIO ---

// Asumiendo que 'db.php' te da acceso a una conexión a base de datos, por ejemplo, una instancia PDO
// Necesitas adaptar esto a cómo manejas tu conexión. Ejemplo:

try {
    $db = new DB(); // Si tu clase DB crea y devuelve la conexión
    $pdo = $db->connect(); // Si tiene un método connect() que devuelve PDO
    // O si la conexión es una variable global después del require_once:
    // global $pdo; // Asegúrate de que $pdo está disponible aquí
} catch (\Exception $e) {
    error_log("Error al obtener conexión a la base de datos: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Error interno del servidor al conectar a la base de datos.']);
    exit;
}

// --- Ejemplo usando PDO, adapta a tu estructura de DB ---

// Iniciar una transacción si vas a hacer múltiples inserts (mensaje + archivos)
// Esto asegura que si alguna parte falla, toda la operación se revierte.
$pdo->beginTransaction();

try {
    $success = false; // Inicializamos success como false por si algo falla

    // NOTA: La función getFileTypeMessagePrefix ya no es necesaria ya que el prefijo es fijo.
    // function getFileTypeMessagePrefix(...) { ... } // <-- Puedes eliminar esta función


    // Lógica para guardar en la base de datos: un mensaje por cada archivo subido exitosamente
    if (!empty($uploadedFilesInfo)) {

        // Preparamos la sentencia SQL para insertar mensajes fuera del bucle para mejor rendimiento
        // Columnas de wsp_messages: numero, mensaje, chat_id, estado, remitente, timestamp, open
        $stmtMessage = $pdo->prepare("INSERT INTO wsp_messages (numero, mensaje, chat_id, estado, remitente, timestamp, `open`) VALUES (?, ?, ?, ?, ?, NOW(), '1')");

        $totalUploadedFiles = count($uploadedFilesInfo); // Número total de archivos subidos exitosamente

        // Iterar sobre cada archivo subido exitosamente
        foreach ($uploadedFilesInfo as $index => $fileInfo) {
            // Construir el contenido del mensaje para este archivo según el formato:
            // !fileTypeMessage, UUID.extensión, nombre original
            $fileExtension = pathinfo($fileInfo['original_name'], PATHINFO_EXTENSION); // Obtener la extensión del nombre original (sin punto)

            // Construimos la parte fija del mensaje del archivo: !fileTypeMessage, UUID.extensión, nombre original
            $messageContentForFile = '!fileTypeMessage, ' // El comando literal para el bot
                                     . $fileInfo['uuid'] . '.' . strtolower($fileExtension) // UUID.extensión (en minúsculas)
                                     . ', ' . $fileInfo['original_name']; // Añadimos ', ' y el nombre original

            // Verificar si este es el último archivo en la lista Y si hay texto original ($mensaje)
            $isLastFile = ($index === $totalUploadedFiles - 1);

            if ($isLastFile && !empty($mensaje)) {
                // Si es el último archivo Y hay texto original, adjuntar el texto
                // Añadimos ', ' antes del texto original como separador
                $messageContentForFile .= ', ' . $mensaje;
            }

            // Insertar el registro del mensaje para este archivo en wsp_messages
            // Los valores para execute deben coincidir con el orden de las columnas en la sentencia prepare:
            // numero, mensaje, chat_id, estado, remitente
            $stmtMessage->execute([
                $numero, // Número del paciente (asumo que es la columna 'numero' en wsp_messages)
                $messageContentForFile, // El contenido del mensaje que construimos
                $chatId, // ID del chat
                'pendiente', // Estado del mensaje (ajusta si usas otro valor)
                $remitente, // Remitente del mensaje
                // timestamp y open se manejan en la sentencia SQL
            ]);
            $lastInsertedId = $pdo->lastInsertId();

            // NOTA: Como se aclaró, no hay tabla 'archivos' separada.
            // Toda la información necesaria para el bot (UUID, extensión, nombre original, texto)
            // está incluida en el campo 'mensaje' de wsp_messages.

        } // Fin del bucle foreach ($uploadedFilesInfo as ...)

        // Si el bucle terminó sin lanzar excepciones, podemos confirmar la transacción
        $pdo->commit();
        $success = true; // Marcar como éxito si todo fue bien en la DB

    } elseif (!empty($mensaje) && empty($_FILES)) {
        // Caso: Solo hay mensaje de texto, no archivos subidos (y no hubo errores en $_FILES)
        // Esto solo debería ocurrir si el JS envía peticiones de solo texto a este endpoint.
        // Si tu JS envía solo texto a otro script (send_message.php), esta parte no se ejecutará.
        // Si decides manejar el envío de solo texto aquí, descomenta y adapta esta lógica.
        /*
        $stmtMessageOnlyText = $pdo->prepare("INSERT INTO wsp_messages (numero, mensaje, chat_id, estado, remitente, timestamp, open) VALUES (?, ?, ?, ?, ?, NOW(), '1')");
        $stmtMessageOnlyText->execute([
            $numero,
            $mensaje,
            $chatId,
            '1', // Estado
            $remitente
        ]);
        $pdo->commit();
        $success = true;
        */
       // Si no hay archivos subidos exitosamente y no hay errores en $_FILES, y tu JS NO envía texto solo aquí,
       // simplemente consideramos que no había nada que procesar relacionado con archivos por ESTE script.
       // La respuesta general dependerá de si hubo errores de subida de archivos (aunque empty($_FILES) debería prevenir esto).
       $success = empty($errors); // Si no hay archivos Y no hay errores de subida, consideramos éxito (nada que insertar de archivos)


    } else {
        // Si llegamos aquí, no hay ni texto (que debamos manejar en este script de archivos)
        // ni archivos subidos exitosamente. Esto debería ser validado por el JS.
        // Si ocurre, no hay nada que guardar en la DB en este script.
        // Si no hay errores de subida, respondemos éxito.
         $success = empty($errors); // Si no hay archivos Y no hay errores de subida, consideramos éxito.
    }


} catch (\Exception $e) {
    // Si algo falló en la transacción (en cualquier inserción de wsp_messages), hacer rollback
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $success = false; // Marcar como fallo si hubo una excepción
    // Registrar el error de base de datos detallado en el log del servidor
    error_log("Error de base de datos al guardar mensaje(s) de archivo: " . $e->getMessage());
    // Añadir un mensaje de error genérico para el usuario en la respuesta si no hay errores de archivo ya reportados
    if (empty($errors)) {
        $errors[] = 'Error interno del servidor al guardar la información.';
    } else {
        // Si ya hay errores de archivo, puedes añadir un error genérico de DB o no.
        $errors[] = 'Error de base de datos durante el proceso de guardado.';
    }
}

// --- FIN DE LA SECCIÓN DE BASE DE DATOS Y LÓGICA DE NEGOCIO ---


// 8. Preparar la respuesta JSON
// ==============================
// La respuesta general es exitosa si no hubo errores reportados durante el
// procesamiento de archivos ($errors está vacío) Y la lógica de base de datos
// (si se ejecutó, es decir, si había archivos subidos exitosamente o texto que este script maneja)
// finalizó marcando $success como true.

// Si había archivos subidos exitosamente, $success debe ser true.
// Si no había archivos subidos exitosamente Y no hubo errores de subida ($errors está vacío),
// $success se establece a true (nada que guardar de archivos).
// Si había errores de subida ($errors no está vacío), $overallSuccess será false.
// Si la lógica de DB se ejecutó (había archivos) y lanzó una excepción, $success será false y $errors contendrá el error de DB.


// Determinar el éxito general: true solo si no hubo errores de subida de archivo Y $success es true
$overallSuccess = empty($errors) && ($success === true);


$response = [
    'success' => $overallSuccess,
    'message_id' => $lastInsertedId,
];

// Si no fue exitoso, añadir los mensajes de error
if (!$overallSuccess && !empty($errors)) {
    // Enviamos el array de errores. El JS puede decidir cómo mostrarlos.
    $response['errors'] = $errors;
} elseif (!$overallSuccess && empty($errors)) {
     // Este caso es poco probable si los errores se añaden a $errors.
     // Podría indicar un fallo lógico donde $overallSuccess se volvió false sin añadir a $errors.
     // O un caso donde no hubo archivos/texto y $success se inicializó a false y nunca se cambió.
     // Si $success se inicializa a empty($errors) cuando no hay archivos, este caso no debería ocurrir.
     // Mantenemos un mensaje genérico por si acaso.
     $response['error'] = 'Ocurrió un error desconocido durante el proceso.';
}

// 9. Enviar la respuesta JSON
// ==========================
// La línea de depuración que tenías:
// echo $uploadDir . " - - - "; // Asegúrate de comentar o eliminar esta línea en producción antes de enviar JSON
echo json_encode($response);

?>