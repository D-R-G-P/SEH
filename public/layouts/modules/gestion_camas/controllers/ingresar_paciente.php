<?php

header('Content-Type: application/json');

require_once '../../../../../app/db/db.php';
require_once '../../../../../app/db/user_session.php';
require_once '../../../../../app/db/user.php';
require_once '../../../../config.php';

// Cargar variables de entorno (asumiendo que cargarEntorno está disponible)
// Asegúrate de que esta función esté definida en config.php o en un archivo incluido.
// cargarEntorno(dirname(__DIR__, 5) . '/.env'); 

$db = new DB();
$pdo = $db->connect();

$user = new User();
$userSession = new UserSession();
$currentUser = $userSession->getCurrentUser(); // Obtiene el DNI del usuario actual
$user->setUser($currentUser); // Establece el usuario en el objeto User

// Obtener los datos del POST. Si se envía JSON, se usa file_get_contents('php://input')
$input = file_get_contents('php://input');
$data = json_decode($input, true);

$cama_id = isset($data['cama_id']) ? intval($data['cama_id']) : null;
$patient_id = isset($data['patient_id']) ? intval($data['patient_id']) : null;
$admitted_by_user_dni = $user->getDni(); // DNI del usuario que realiza el ingreso

// Validar que los IDs no sean nulos
if (!$cama_id || !$patient_id) {
    echo json_encode(['success' => false, 'message' => 'ID de cama o paciente no proporcionado.']);
    http_response_code(400); // Bad Request
    exit();
}

try {
    // 1. Verificar el estado de la cama: debe estar 'Libre' o 'Reservada'
    $con_cama = "SELECT id FROM beds WHERE id = :id AND (bed_status = 'Libre' OR bed_status = 'Reservada')";
    $stmt_cama = $pdo->prepare($con_cama);
    $stmt_cama->bindParam(':id', $cama_id, PDO::PARAM_INT);
    $stmt_cama->execute();

    if ($stmt_cama->rowCount() == 0) {
        // Si no se encuentra la cama o no está en estado 'Libre'/'Reservada'
        echo json_encode(['success' => false, 'message' => 'La cama no está disponible para ingreso o no existe.']);
        exit();
    }

    // 2. Verificar si el paciente ya está ingresado (activo)
    $con_paciente = "SELECT patient_id FROM patients_admitteds WHERE patient_id = :patient_id AND date_discharged IS NULL";
    $stmt_paciente = $pdo->prepare($con_paciente);
    $stmt_paciente->bindParam(':patient_id', $patient_id, PDO::PARAM_INT);
    $stmt_paciente->execute();

    if ($stmt_paciente->rowCount() > 0) {
        // Si se encuentra una fila, significa que el paciente ya está ingresado
        echo json_encode(['success' => false, 'message' => 'Error: El paciente ya se encuentra ingresado en otra cama.']);
        exit();
    }

    // 3. Actualizar el estado de la cama a "Ocupada"
    // Usar 'bed_status' para consistencia con la consulta anterior
    $updt_cama = "UPDATE beds SET bed_status = 'Ocupada' WHERE id = :id";
    $stmt_updt_cama = $pdo->prepare($updt_cama);
    $stmt_updt_cama->bindParam(':id', $cama_id, PDO::PARAM_INT);
    $stmt_updt_cama->execute();

    // 4. Ingresar al paciente en patients_admitteds
    $admit = "INSERT INTO patients_admitteds (patient_id, bed_id, admission_date, admitted_by, date_discharged, discharged_by) 
              VALUES (:patient_id, :bed_id, NOW(), :admitted_by, NULL, NULL)";
    $stmt_admit = $pdo->prepare($admit);
    $stmt_admit->bindParam(':patient_id', $patient_id, PDO::PARAM_INT);
    $stmt_admit->bindParam(':bed_id', $cama_id, PDO::PARAM_INT);
    $stmt_admit->bindParam(':admitted_by', $admitted_by_user_dni, PDO::PARAM_STR); // Corregido: bindParam para admitted_by
    $stmt_admit->execute();

    echo json_encode(['success' => true, 'message' => 'Paciente ingresado exitosamente.', 'cama_id' => $cama_id, 'patient_id' => $patient_id]);

} catch (PDOException $e) {
    error_log("Error PDO al ingresar paciente: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error interno del servidor al ingresar paciente.']);
    // No exponer el mensaje de error directo de PDO al cliente en producción
} catch (Exception $e) {
    error_log("Error general al ingresar paciente: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Ocurrió un error inesperado al ingresar paciente.']);
}

?>
