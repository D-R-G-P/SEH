<?php

require_once '../../../../../app/db/db.php';
require_once '../../../../../app/db/user_session.php';
require_once '../../../../../app/db/user.php';
require_once '../../../../config.php';

$db = new DB();
$pdo = $db->connect();

$user = new User();
$userSession = new UserSession();
$currentUser = $userSession->getCurrentUser();
$user->setUser($currentUser);
$userDNI = $user->getDni();

$patient_id = isset($_GET['patient_id']) ? intval($_GET['patient_id']) : null;
$cama_id = isset($_GET['cama_id']) ? intval($_GET['cama_id']) : null;

if (!$patient_id) {
    echo json_encode(['success' => false, 'message' => 'ID de paciente no proporcionado.']);
    http_response_code(400); // Bad Request
    exit();
}
if (!$cama_id) {
    echo json_encode(['success' => false, 'message' => 'ID de cama no proporcionado.']);
    http_response_code(400); // Bad Request
    exit();
}

try {
    // 1. Verificar si el paciente está en estado 'Ocupado'
    $con_paciente = "SELECT id FROM patients_admitteds WHERE patient_id = :patient_id AND date_discharged IS NULL";
    $stmt_paciente = $pdo->prepare($con_paciente);
    $stmt_paciente->bindParam(':patient_id', $patient_id, PDO::PARAM_INT);
    $stmt_paciente->execute();

    if ($stmt_paciente->rowCount() == 0) {
        // Si no se encuentra la fila, significa que el paciente no está en estado 'Ocupado'
        echo json_encode(['success' => false, 'message' => 'El paciente no está en estado "Ocupado" para ser egresado.']);
        exit();
    }

    // 2. Actualizar el estado de la fila a "Egresado"
    $updt_paciente = "UPDATE patients_admitteds SET date_discharged = NOW(), discharged_by = :discharged_by WHERE patient_id = :patient_id AND date_discharged IS NULL";
    $stmt_updt_paciente = $pdo->prepare($updt_paciente);
    $stmt_updt_paciente->bindParam(':patient_id', $patient_id, PDO::PARAM_INT);
    $stmt_updt_paciente->bindParam(':discharged_by', $userDNI, PDO::PARAM_STR); // Corregido: bindParam para discharged_by
    $stmt_updt_paciente->execute();

    echo json_encode(['success' => true, 'message' => 'Paciente egresado exitosamente.']);

    // 3. Actualizar el estado de la cama a "Libre"
    $updt_cama = "UPDATE beds SET bed_status = 'Libre' WHERE id = :id";
    $stmt_updt_cama = $pdo->prepare($updt_cama);
    $stmt_updt_cama->bindParam(':id', $cama_id, PDO::PARAM_INT);
    $stmt_updt_cama->execute();
    echo json_encode(['success' => true, 'message' => 'Cama actualizada a estado "Libre".']);

} catch (PDOException $e) {
    // Manejo de errores de la base de datos
    echo json_encode(['success' => false, 'message' => 'Error al egresar al paciente: ' . $e->getMessage()]);
    http_response_code(500); // Internal Server Error
} catch (Exception $e) {
    // Manejo de errores generales
    echo json_encode(['success' => false, 'message' => 'Error inesperado: ' . $e->getMessage()]);
    http_response_code(500); // Internal Server Error
}

header("Location: " . $_SERVER['HTTP_REFERER']);

?>