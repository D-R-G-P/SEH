<?php

require_once '../../../../../app/db/db.php';

// Cargar variables de entorno
cargarEntorno(dirname(__DIR__, 5) . '/.env'); 

header('Content-Type: application/json');

// Clave de encriptación
$ENCRYPTION_KEY = $_ENV['ENCRYPTION_KEY'] ?? null;
$IV_LENGTH = 16;

// Normalizar la clave de encriptación
$ENCRYPTION_KEY = $ENCRYPTION_KEY ? trim($ENCRYPTION_KEY, '"') : '';
$ENCRYPTION_KEY = str_pad(substr($ENCRYPTION_KEY, 0, 32), 32, "0");

if (strlen($ENCRYPTION_KEY) !== 32) {
    echo json_encode(["error" => "🔑 Clave de encriptación inválida."]);
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
        if (strpos($encryptedData, ':') === false) {
            throw new Exception("Formato incorrecto encriptado.");
        }

        list($ivBase64, $encryptedText) = explode(':', $encryptedData, 2);
        $iv = base64_decode($ivBase64);
        $encryptedBinary = base64_decode($encryptedText);

        if (strlen($iv) !== $IV_LENGTH) {
            throw new Exception("Longitud del IV incorrecta.");
        }

        $decrypted = openssl_decrypt($encryptedBinary, 'aes-256-cbc', $ENCRYPTION_KEY, OPENSSL_RAW_DATA, $iv);

        return $decrypted ?: null;
    } catch (Exception $e) {
        error_log("❌ Error desencriptando: " . $e->getMessage());
        return null;
    }
}

$db = new DB();
$pdo = $db->connect();

// Obtener el chat_id desde la solicitud
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, true);

$chat_id = $input['chat_id'] ?? null;

if (!$chat_id) {
    echo json_encode(["error" => "⚠️ Falta el ID del chat."]);
    exit;
}


// Consultar el paciente_id asociado al chat
$sql = "SELECT paciente_id FROM chats WHERE id = :chat_id";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(":chat_id", $chat_id, PDO::PARAM_INT);
$stmt->execute();
$chat_data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$chat_data) {
    echo json_encode(["error" => "Chat no encontrado."]);
    exit;
}

$paciente_id = $chat_data['paciente_id'];

// Consultar los datos del paciente en la tabla pacientes_chat
$sql = "SELECT apellidos, nombres, sexo, tipo_documento, documento, fecha_nacimiento, 
               identidad_genero, nombre_autopercibido, provincia, partido, ciudad, calle, 
               numero, piso, departamento, telefono, mail, obra_social 
        FROM pacientes_chat 
        WHERE id = :paciente_id";

$stmt = $pdo->prepare($sql);
$stmt->bindParam(":paciente_id", $paciente_id, PDO::PARAM_INT);
$stmt->execute();
$paciente = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$paciente) {
    echo json_encode(["error" => "Paciente no encontrado."]);
    exit;
}

// Lista de campos a desencriptar
$campos_encriptados = [
    "apellidos", "nombres", "sexo", "tipo_documento", "documento", "fecha_nacimiento",
    "identidad_genero", "nombre_autopercibido", "provincia", "partido", "ciudad", 
    "calle", "numero", "piso", "departamento", "mail", "obra_social"
];

// Aplicar desencriptación
foreach ($campos_encriptados as $campo) {
    if (!empty($paciente[$campo])) {
        $paciente[$campo] = decryptData($paciente[$campo]);
    }
}

// Dejar teléfono y obra social sin desencriptar
echo json_encode($paciente);


?>
