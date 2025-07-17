<?php

require_once '../../../../../app/db/db.php';
require_once '../../../../../app/db/user_session.php';
require_once '../../../../../app/db/user.php';
require_once '../../../../config.php';

header('Content-Type: application/json');

$db = new DB();
$pdo = $db->connect();

$user = new User();
$userSession = new UserSession();
$currentUser = $userSession->getCurrentUser();
$user->setUser($currentUser);

$old_bed = isset($_POST['cama_id']) ? intval($_POST['cama_id']) : null;
$new_bed = isset($_POST['new_bed_id']) ? intval($_POST['new_bed_id']) : null;
$patient = isset($_POST['patient_id']) ? intval($_POST['patient_id']) : null;

// Validar que todos los datos necesarios estén presentes
if (is_null($old_bed) || is_null($new_bed) || is_null($patient)) {
    http_response_code(400); // Bad Request
    echo json_encode([
        'success' => false,
        'message' => 'Datos incompletos para realizar el pase de cama.'
    ]);
    exit;
}

try {
    // Iniciar una transacción para asegurar la atomicidad de las operaciones
    $pdo->beginTransaction();

    // 1. Verificar el estado actual de la nueva cama antes de asignarla
    $query_check_new_bed = "SELECT bed_status FROM beds WHERE id = :new_bed";
    $stmt_check_new_bed = $pdo->prepare($query_check_new_bed);
    $stmt_check_new_bed->bindParam(':new_bed', $new_bed, PDO::PARAM_INT);
    $stmt_check_new_bed->execute();
    $new_bed_status_data = $stmt_check_new_bed->fetch(PDO::FETCH_ASSOC);

    if (!$new_bed_status_data || ($new_bed_status_data['bed_status'] !== 'Libre' && $new_bed_status_data['bed_status'] !== 'Reservada')) {
        $pdo->rollBack(); // Revertir la transacción
        http_response_code(409); // Conflict
        echo json_encode([
            'success' => false,
            'message' => 'La cama de destino no está disponible para un pase (estado: ' . ($new_bed_status_data ? $new_bed_status_data['bed_status'] : 'desconocido') . ').'
        ]);
        exit;
    }

    // 2. Liberar la cama anterior (si la cama anterior es diferente a la nueva)
    // Esto es importante para evitar errores si por alguna razón old_bed y new_bed son iguales
    if ($old_bed !== $new_bed) {
        $query_old_bed = "UPDATE beds SET bed_status = 'Libre' WHERE id = :old_bed";
        $stmt_old_bed = $pdo->prepare($query_old_bed);
        $stmt_old_bed->bindParam(':old_bed', $old_bed, PDO::PARAM_INT);
        $stmt_old_bed->execute();
    }

    // 3. Asignar la nueva cama al estado 'Ocupada'
    $query_new_bed = "UPDATE beds SET bed_status = 'Ocupada' WHERE id = :new_bed";
    $stmt_new_bed = $pdo->prepare($query_new_bed);
    $stmt_new_bed->bindParam(':new_bed', $new_bed, PDO::PARAM_INT);
    $stmt_new_bed->execute();

    // 4. Obtener el ID de la internación activa del paciente
    // Se asume que solo hay una internación activa por paciente (date_discharged IS NULL)
    $query_admitted_id = "SELECT id FROM patients_admitteds WHERE patient_id = :patient AND date_discharged IS NULL LIMIT 1";
    $stmt_admitted_id = $pdo->prepare($query_admitted_id);
    $stmt_admitted_id->bindParam(':patient', $patient, PDO::PARAM_INT);
    $stmt_admitted_id->execute();
    $admitted_data = $stmt_admitted_id->fetch(PDO::FETCH_ASSOC);

    if (!$admitted_data) {
        $pdo->rollBack(); // Revertir la transacción
        http_response_code(404); // Not Found
        echo json_encode([
            'success' => false,
            'message' => 'No se encontró una internación activa para el paciente especificado.'
        ]);
        exit;
    }
    $admitted_id = $admitted_data['id']; // Acceso correcto al ID

    // 5. Asignar el paciente a la nueva cama en la tabla de internaciones
    $query_update_admitted = "UPDATE patients_admitteds SET bed_id = :new_bed WHERE id = :admitted_id";
    $stmt_update_admitted = $pdo->prepare($query_update_admitted);
    $stmt_update_admitted->bindParam(':new_bed', $new_bed, PDO::PARAM_INT);
    $stmt_update_admitted->bindParam(':admitted_id', $admitted_id, PDO::PARAM_INT); // Uso correcto de $admitted_id
    $stmt_update_admitted->execute();

    // 6. Registrar el pase en la tabla de historial (si existe)
    // [Inferencia] Asumo que tienes una tabla para el historial de pases, por ejemplo 'bed_transfer_history'
    // Si no la tienes, puedes omitir esta parte o crearla.
$user_id = $user->getDni(); // Asumiendo que $user->getDni() devuelve el DNI del usuario actual
// O $user_id = $user->getId(); // Si 'personal' tiene un ID numérico que usas para el usuario

// Establecer la fecha y hora actual para el campo date_passed
$transfer_date = date('Y-m-d H:i:s');

$query_insert_history = "INSERT INTO pass_history (patient_id, admitted_id, bed_old, bed_new, date_passed, passed_by) 
                         VALUES (:patient_id, :admitted_id, :old_bed_id, :new_bed_id, :transfer_date, :passed_by)";
$stmt_insert_history = $pdo->prepare($query_insert_history);
$stmt_insert_history->bindParam(':patient_id', $patient, PDO::PARAM_INT);
$stmt_insert_history->bindParam(':admitted_id', $admitted_id, PDO::PARAM_INT); // ¡Importante añadir esto!
$stmt_insert_history->bindParam(':old_bed_id', $old_bed, PDO::PARAM_INT);
$stmt_insert_history->bindParam(':new_bed_id', $new_bed, PDO::PARAM_INT);
$stmt_insert_history->bindParam(':transfer_date', $transfer_date);
$stmt_insert_history->bindParam(':passed_by', $user_id); // Este DNI debe ser VARCHAR
$stmt_insert_history->execute();

    // Confirmar la transacción si todas las operaciones fueron exitosas
    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Pase realizado correctamente.'
    ]);

} catch (PDOException $e) {
    // Revertir la transacción en caso de cualquier error
    $pdo->rollBack();
    http_response_code(500); // Internal Server Error
    echo json_encode([
        'success' => false,
        'message' => 'Error al realizar el pase de cama: ' . $e->getMessage()
    ]);
    // Considera loggear $e->getMessage() a un archivo de log en producción, no solo mostrarlo.
    exit;
} catch (Exception $e) {
    // Capturar otras excepciones no PDO (ej. si $user->getId() falla)
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error inesperado: ' . $e->getMessage()
    ]);
    exit;
}

header("Location: " . $_SERVER['HTTP_REFERER']);

?>