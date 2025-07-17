<?php
// SGH/public/controllers/create_patient.php

header('Content-Type: application/json');

// Incluir archivos necesarios
require_once '../../../../config.php';
require_once __DIR__ . '/../../../../../app/db/db.php';
require_once __DIR__ . '/../../../../../app/db/user_session.php';
require_once __DIR__ . '/../../../../../app/db/user.php';
require_once __DIR__ . '/../../../../resources/encrypter/encrypt.php';

cargarEntorno(dirname(__DIR__, 5) . '/.env');

$db = new DB();
$pdo = $db->connect();

$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Validar datos mínimos
if (empty($data['name']) || empty($data['last_name']) || empty($data['document_type']) || empty($data['document'])) {
    echo json_encode(['success' => false, 'message' => 'Nombres, Apellidos, Tipo y Número de Documento son obligatorios.']);
    http_response_code(400);
    exit();
}

$userSession = new UserSession();
$currentUser = $userSession->getCurrentUser(); // DNI del usuario que crea

$fields_to_insert = [];
$placeholders = [];
$params = [];

// Definir los campos que serán encriptados y sus valores
$encrypted_fields_map = [
    'last_name' => $data['last_name'] ?? null,
    'name' => $data['name'] ?? null,
    'gender' => $data['gender'] ?? null,
    'document_type' => $data['document_type'] ?? null,
    'document' => $data['document'] ?? null,
    'birth_date' => $data['birth_date'] ?? null,
    'phone_number' => $data['phone_number'] ?? null,
    'family_phone_number' => $data['family_phone_number'] ?? null,
    'email' => $data['email'] ?? null,
    'provincia' => $data['provincia'] ?? null,
    'partido' => $data['partido'] ?? null,
    'ciudad' => $data['ciudad'] ?? null,
    'codigo_postal' => $data['codigo_postal'] ?? null,
    'calle' => $data['calle'] ?? null,
    'numero' => $data['numero'] ?? null,
    'piso' => $data['piso'] ?? null,
    'departamento' => $data['departamento'] ?? null,
    'barrio' => $data['barrio'] ?? null,
    'health_insurance' => $data['health_insurance'] ?? null,
    'health_insurance_number' => $data['health_insurance_number'] ?? null,
    'administrative_name' => $data['administrative_name'] ?? null,
    'gender_identity' => $data['gender_identity'] ?? null,
    'self_perceived_name' => $data['self_perceived_name'] ?? null,
    'phone_number_alt' => $data['phone_number_alt'] ?? null,
    'family_phone_number_alt' => $data['family_phone_number_alt'] ?? null,
    'country' => $data['country'] ?? null,
];

foreach ($encrypted_fields_map as $field => $value) {
    $fields_to_insert[] = "`{$field}`";
    $placeholders[] = ":{$field}";
    $encrypted_value = ($value !== null && $value !== '') ? encryptData($value) : null; // Guardar null si es vacío
    $params[":{$field}"] = $encrypted_value;

    // Campos hash
    $hash_field = "{$field}_hash";
    $fields_to_insert[] = "`{$hash_field}`";
    $placeholders[] = ":{$hash_field}";
    $params[":{$hash_field}"] = hash('sha256', (string)($value ?? '')); // Hashear el valor *original*
}

// Campos booleanos (no encriptados)
$fields_to_insert[] = "`dni_rectified`";
$placeholders[] = ":dni_rectified";
$params[':dni_rectified'] = (bool)($data['dni_rectified'] ?? false);

// Campos de auditoría
$fields_to_insert[] = "`created_by`";
$placeholders[] = ":created_by";
$params[':created_by'] = $currentUser;

$fields_to_insert[] = "`date_created`";
$placeholders[] = "NOW()";


try {
    $sql = "INSERT INTO patients (" . implode(', ', $fields_to_insert) . ") VALUES (" . implode(', ', $placeholders) . ")";
    $stmt = $pdo->prepare($sql);

    // Remover NOW() de los parámetros para que MySQL lo maneje directamente
    $final_params = $params;
    unset($final_params[':date_created']); // Si se usara un placeholder
    
    // Asegurarse de no pasar 'NOW()' como un valor vinculado.
    // PDO ya maneja el NOW() directamente en la consulta.
    $stmt->execute($final_params);

    $newPatientId = $pdo->lastInsertId();

    echo json_encode(['success' => true, 'message' => 'Paciente creado exitosamente.', 'id' => $newPatientId]);

} catch (PDOException $e) {
    error_log("Error PDO al crear paciente: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error en la base de datos al crear paciente.']);
    http_response_code(500);
}
?>