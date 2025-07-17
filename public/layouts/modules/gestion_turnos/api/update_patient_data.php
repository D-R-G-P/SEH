<?php

require_once '../../../../../app/db/db.php';

// Cargar variables de entorno
// Asegúrate de que esta función 'cargarEntorno' y el archivo '.env' existan y sean accesibles.
// Por ejemplo:
// function cargarEntorno($envPath) {
//     if (!file_exists($envPath)) {
//         throw new Exception(".env file not found at $envPath");
//     }
//     $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
//     foreach ($lines as $line) {
//         if (strpos(trim($line), '#') === 0) {
//             continue;
//         }
//         list($name, $value) = explode('=', $line, 2);
//         $_ENV[$name] = $value;
//         $_SERVER[$name] = $value;
//     }
// }
cargarEntorno(dirname(__DIR__, 5) . '/.env');

header('Content-Type: application/json');

// Clave de encriptación
$ENCRYPTION_KEY = $_ENV['ENCRYPTION_KEY'] ?? null;
$IV_LENGTH = 16; // Longitud del IV en AES-256-CBC

// Normalizar la clave de encriptación
$ENCRYPTION_KEY = $ENCRYPTION_KEY ? trim($ENCRYPTION_KEY, '"') : '';
// Asegurarse de que la clave tenga 32 caracteres (256 bits)
$ENCRYPTION_KEY = str_pad(substr($ENCRYPTION_KEY, 0, 32), 32, "0");

if (strlen($ENCRYPTION_KEY) !== 32) {
    echo json_encode(["error" => "🔑 Clave de encriptación inválida."]);
    exit;
}

/**
 * Encripta un texto usando AES-256-CBC y devuelve el resultado en formato "IV_Base64:EncryptedData_Base64".
 * @param string $plainText Texto a encriptar.
 * @return string|null Texto encriptado o null en caso de error.
 */
function encryptData($plainText) {
    global $ENCRYPTION_KEY, $IV_LENGTH;

    try {
        // Generar un IV aleatorio
        $iv = openssl_random_pseudo_bytes($IV_LENGTH);
        if ($iv === false) {
            throw new Exception("No se pudo generar un IV aleatorio.");
        }

        // Encriptar los datos
        $encryptedBinary = openssl_encrypt($plainText, 'aes-256-cbc', $ENCRYPTION_KEY, OPENSSL_RAW_DATA, $iv);

        // Verificar si la encriptación fue exitosa
        if ($encryptedBinary === false) {
            error_log("❌ No se pudo encriptar el texto.");
            throw new Exception("No se pudo encriptar el texto.");
        }

        // Codificar IV y datos encriptados en Base64
        $ivBase64 = base64_encode($iv);
        $encryptedTextBase64 = base64_encode($encryptedBinary);

        // Combinar IV y datos encriptados en el formato deseado
        $finalEncryptedData = $ivBase64 . ':' . $encryptedTextBase64;

        return $finalEncryptedData;
    } catch (Exception $e) {
        error_log("❌ Error al encriptar: " . $e->getMessage());
        return null;
    }
}

$db = new DB();
$pdo = $db->connect();

$response = ['success' => false, 'message' => ''];

// Verifica que la petición sea POST y que el contenido sea JSON
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        $response['message'] = 'Error: Datos JSON no válidos.';
        echo json_encode($response);
        exit;
    }

    // Validar y sanear los datos
    $id_paciente = filter_var($data['id_paciente'] ?? null, FILTER_SANITIZE_NUMBER_INT);
    $apellidos = filter_var($data['apellidos'] ?? null, FILTER_SANITIZE_STRING);
    $nombres = filter_var($data['nombres'] ?? null, FILTER_SANITIZE_STRING);
    $sexo = filter_var($data['sexo'] ?? null, FILTER_SANITIZE_STRING);
    $tipo_documento = filter_var($data['tipo_documento'] ?? null, FILTER_SANITIZE_STRING);
    $documento = filter_var($data['documento'] ?? null, FILTER_SANITIZE_STRING);
    $fecha_nacimiento = filter_var($data['fecha_nacimiento'] ?? null, FILTER_SANITIZE_STRING);
    $identidad_genero = filter_var($data['identidad_genero'] ?? null, FILTER_SANITIZE_STRING);
    $nombre_autopercibido = filter_var($data['nombre_autopercibido'] ?? null, FILTER_SANITIZE_STRING);
    $provincia = filter_var($data['provincia'] ?? null, FILTER_SANITIZE_STRING);
    $partido = filter_var($data['partido'] ?? null, FILTER_SANITIZE_STRING);
    $ciudad = filter_var($data['ciudad'] ?? null, FILTER_SANITIZE_STRING);
    $calle = filter_var($data['calle'] ?? null, FILTER_SANITIZE_STRING);
    $numero = filter_var($data['numero'] ?? null, FILTER_SANITIZE_STRING);
    $piso = filter_var($data['piso'] ?? null, FILTER_SANITIZE_STRING);
    $departamento = filter_var($data['departamento'] ?? null, FILTER_SANITIZE_STRING);
    $telefono = filter_var($data['telefono'] ?? null, FILTER_SANITIZE_STRING); // No se encripta
    $mail = filter_var($data['mail'] ?? null, FILTER_SANITIZE_EMAIL);
    $obra_social = filter_var($data['obra_social'] ?? null, FILTER_SANITIZE_STRING);

    // Validaciones básicas
    if (empty($id_paciente) || empty($apellidos) || empty($nombres) || empty($telefono)) {
        $response['message'] = 'Error: Los campos Apellidos, Nombres y Teléfono son obligatorios.';
        echo json_encode($response);
        exit;
    }

    // Encriptar los datos antes de guardarlos en la base de datos
    // El número de teléfono NO se encripta
    $apellidos_enc = encryptData($apellidos);
    $nombres_enc = encryptData($nombres);
    $sexo_enc = encryptData($sexo);
    $tipo_documento_enc = encryptData($tipo_documento);
    $documento_enc = encryptData($documento);
    $fecha_nacimiento_enc = encryptData($fecha_nacimiento);
    $identidad_genero_enc = encryptData($identidad_genero);
    $nombre_autopercibido_enc = encryptData($nombre_autopercibido);
    $provincia_enc = encryptData($provincia);
    $partido_enc = encryptData($partido);
    $ciudad_enc = encryptData($ciudad);
    $calle_enc = encryptData($calle);
    $numero_enc = encryptData($numero);
    $piso_enc = encryptData($piso);
    $departamento_enc = encryptData($departamento);
    $mail_enc = encryptData($mail);
    $obra_social_enc = encryptData($obra_social);

    // Verificar si alguna encriptación falló
    if (in_array(null, [$apellidos_enc, $nombres_enc, $sexo_enc, $tipo_documento_enc, $documento_enc, $fecha_nacimiento_enc, $identidad_genero_enc, $nombre_autopercibido_enc, $provincia_enc, $partido_enc, $ciudad_enc, $calle_enc, $numero_enc, $piso_enc, $departamento_enc, $mail_enc, $obra_social_enc], true)) {
        $response['message'] = 'Error: Fallo la encriptación de uno o más campos.';
        echo json_encode($response);
        exit;
    }

    try {
        $stmt = $pdo->prepare("UPDATE pacientes_chat SET
            apellidos = :apellidos,
            nombres = :nombres,
            sexo = :sexo,
            tipo_documento = :tipo_documento,
            documento = :documento,
            fecha_nacimiento = :fecha_nacimiento,
            identidad_genero = :identidad_genero,
            nombre_autopercibido = :nombre_autopercibido,
            provincia = :provincia,
            partido = :partido,
            ciudad = :ciudad,
            calle = :calle,
            numero = :numero,
            piso = :piso,
            departamento = :departamento,
            telefono = :telefono,
            mail = :mail,
            obra_social = :obra_social
            WHERE id = :id_paciente");

        $stmt->bindParam(':id_paciente', $id_paciente);
        $stmt->bindParam(':apellidos', $apellidos_enc);
        $stmt->bindParam(':nombres', $nombres_enc);
        $stmt->bindParam(':sexo', $sexo_enc);
        $stmt->bindParam(':tipo_documento', $tipo_documento_enc);
        $stmt->bindParam(':documento', $documento_enc);
        $stmt->bindParam(':fecha_nacimiento', $fecha_nacimiento_enc);
        $stmt->bindParam(':identidad_genero', $identidad_genero_enc);
        $stmt->bindParam(':nombre_autopercibido', $nombre_autopercibido_enc);
        $stmt->bindParam(':provincia', $provincia_enc);
        $stmt->bindParam(':partido', $partido_enc);
        $stmt->bindParam(':ciudad', $ciudad_enc);
        $stmt->bindParam(':calle', $calle_enc);
        $stmt->bindParam(':numero', $numero_enc);
        $stmt->bindParam(':piso', $piso_enc);
        $stmt->bindParam(':departamento', $departamento_enc);
        $stmt->bindParam(':telefono', $telefono); // Sin encriptar
        $stmt->bindParam(':mail', $mail_enc);
        $stmt->bindParam(':obra_social', $obra_social_enc);

        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Datos del paciente actualizados correctamente.';
        } else {
            $response['message'] = 'Error al ejecutar la actualización en la base de datos.';
            error_log("Error PDO al actualizar paciente: " . implode(":", $stmt->errorInfo()));
        }

    } catch (PDOException $e) {
        $response['message'] = 'Error de base de datos: ' . $e->getMessage();
        error_log("PDO Exception: " . $e->getMessage());
    }
} else {
    $response['message'] = 'Método de solicitud no permitido.';
}

echo json_encode($response);
?>
