<?php
// SGH/public/controllers/update_patient.php

header('Content-Type: application/json');

// Incluir archivos necesarios
require_once '../../../../config.php';
require_once __DIR__ . '/../../../../../app/db/db.php';
require_once __DIR__ . '/../../../../../app/db/user_session.php';
require_once __DIR__ . '/../../../../../app/db/user.php';
require_once __DIR__ . '/../../../../resources/encrypter/encrypt.php'; // Asegúrate de que encrypt.php contiene hashDataForSearch()
require_once __DIR__ . '/../../../../resources/encrypter/decrypt.php';

cargarEntorno(dirname(__DIR__, 5) . '/.env');

$db = new DB();
$pdo = $db->connect();

// Obtener el cuerpo de la solicitud JSON
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Validar datos mínimos
if (!isset($data['id']) || !is_numeric($data['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID de paciente no válido.']);
    http_response_code(400);
    exit();
}

if (empty($data['name']) || empty($data['last_name']) || empty($data['document_type']) || empty($data['document'])) {
    echo json_encode(['success' => false, 'message' => 'Nombres, Apellidos, Tipo y Número de Documento son obligatorios.']);
    http_response_code(400);
    exit();
}

$patientId = $data['id'];

// Obtener el usuario actual para 'updated_by'
$userSession = new UserSession();
$currentUser = $userSession->getCurrentUser();

// Campos a actualizar y encriptar
$fields_to_update = [
    'last_name', 'name', 'gender', 'document_type', 'document', 'birth_date',
    'phone_number', 'family_phone_number', 'email', 'provincia', 'partido', 'ciudad',
    'codigo_postal', 'calle', 'numero', 'piso', 'departamento', 'barrio',
    'health_insurance', 'health_insurance_number', 'administrative_name',
    'gender_identity', 'self_perceived_name', 'phone_number_alt',
    'family_phone_number_alt', 'country'
];

// Campos booleanos
$boolean_fields = ['dni_rectified'];

$updates = [];
$params = [':id' => $patientId];

foreach ($fields_to_update as $field) {
    $value = $data[$field] ?? null;
    
    $encrypted_value = ($value !== null && $value !== '') ? encryptData($value) : $value;
    
    $updates[] = "`{$field}` = :{$field}";
    $params[":{$field}"] = $encrypted_value;
    
    // También actualiza los campos hash si existen
    // Y AHORA USAMOS hashDataForSearch() PARA CONSISTENCIA
    if (in_array($field, ['last_name', 'name', 'gender', 'document_type', 'document', 'birth_date', 'phone_number', 'family_phone_number', 'email', 'provincia', 'partido', 'ciudad', 'codigo_postal', 'calle', 'numero', 'piso', 'departamento', 'barrio', 'health_insurance', 'health_insurance_number', 'administrative_name', 'gender_identity', 'self_perceived_name', 'phone_number_alt', 'family_phone_number_alt', 'country'])) {
        $hash_field = "{$field}_hash";
        $updates[] = "`{$hash_field}` = :{$hash_field}";
        // ¡¡¡CAMBIO CLAVE AQUÍ: USA hashDataForSearch()!!!
        $params[":{$hash_field}"] = hashDataForSearch((string)$value);
    }
}

foreach ($boolean_fields as $field) {
    if (isset($data[$field])) {
        $value = (bool)$data[$field];
        $updates[] = "`{$field}` = :{$field}";
        $params[":{$field}"] = $value;
    }
}

// Añadir campos de auditoría
$updates[] = "`updated_by` = :updated_by";
$params[':updated_by'] = $currentUser;
$updates[] = "`date_updated` = NOW()";

try {
    $sql = "UPDATE patients SET " . implode(', ', $updates) . " WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    if ($stmt->rowCount()) {
        echo json_encode(['success' => true, 'message' => 'Paciente actualizado exitosamente.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'No se encontraron cambios o el paciente no existe.']);
    }

} catch (PDOException $e) {
    error_log("Error PDO al actualizar paciente: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error en la base de datos al actualizar paciente.']);
    http_response_code(500);
}
?>