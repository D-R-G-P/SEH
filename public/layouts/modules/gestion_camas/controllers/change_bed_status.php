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

// Asegurarse de que cama_id sea un entero
$cama_id = isset($_POST['cama_id']) ? intval($_POST['cama_id']) : null;
$cama_status = isset($_POST['cama_status']) ? $_POST['cama_status'] : null;
$editor = $user->getDni(); // DNI del usuario actual

// Validar que los parámetros esenciales no sean nulos
if ($cama_id === null || $cama_status === null || $editor === null) {
    $_SESSION['toast_message'] = [
        'message' => 'Parámetros de solicitud inválidos o incompletos.',
        'type' => 'error'
    ];
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit();
}

try {
    // Obtener el estado actual de la cama primero para una lógica más clara
    $stmt_current_status = $pdo->prepare("SELECT bed_status FROM beds WHERE id = :cama_id");
    $stmt_current_status->bindParam(':cama_id', $cama_id, PDO::PARAM_INT);
    $stmt_current_status->execute();
    $current_bed_status = $stmt_current_status->fetchColumn(); // Obtiene solo el valor del estado

    if ($current_bed_status === false) { // La cama no existe en la base de datos
        $_SESSION['toast_message'] = [
            'type' => 'error',
            'message' => 'Cama no encontrada.'
        ];
    } else { // La cama existe, proceder con el cambio de estado
        $success_message = '';
        $error_message = '';
        $is_status_changed = false;

        switch ($cama_status) {
            case 'Reservada':
                $reason_reserva = isset($_POST['reservation_reason']) ? $_POST['reservation_reason'] : null;
                if ($current_bed_status == 'Reservada') {
                    $error_message = 'La cama ya se encuentra Reservada.';
                } else if ($current_bed_status == 'Ocupada' || $current_bed_status == 'Bloqueada') {
                    $error_message = 'No se puede reservar una cama ' . $current_bed_status . '.';
                } else if ($reason_reserva === null) {
                    $error_message = 'Debe proporcionar una razón para la reserva.';
                } else {
                    // Insertar en bed_blocked
                    $stmt_reserve = $pdo->prepare("INSERT INTO bed_blocked (bed_id, date_blocked, blocked_by, blocked_type, reason) VALUES (:bed_id, NOW(), :blocked_by, 'Reserva', :reason)");
                    $stmt_reserve->bindParam(':bed_id', $cama_id, PDO::PARAM_INT);
                    $stmt_reserve->bindParam(':blocked_by', $editor, PDO::PARAM_STR); // DNI como STR
                    $stmt_reserve->bindParam(':reason', $reason_reserva, PDO::PARAM_STR);
                    $stmt_reserve->execute();

                    // Actualizar estado en beds
                    $stmt_update_bed = $pdo->prepare("UPDATE beds SET bed_status = 'Reservada' WHERE id = :cama_id");
                    $stmt_update_bed->bindParam(':cama_id', $cama_id, PDO::PARAM_INT);
                    $stmt_update_bed->execute();

                    if ($stmt_update_bed->rowCount() > 0) {
                        $success_message = 'Cama reservada correctamente.';
                        $is_status_changed = true;
                    } else {
                        $error_message = 'Error al reservar la cama.';
                    }
                }
                break;

            case 'Bloquear':
                $bloqueo_tipo = isset($_POST['bloqueo_tipo']) ? $_POST['bloqueo_tipo'] : null;
                $reason_block = isset($_POST['bloqueo_reason']) ? $_POST['bloqueo_reason'] : null;

                if ($current_bed_status != 'Libre' && $current_bed_status != null) {
                    switch ($current_bed_status) {
                        case 'Bloqueada':
                            $error_message = 'La cama ya se encuentra bloqueada';
                            break;
                        case 'Ocupada':
                            $error_message = 'La cama no puede estar ocupada para bloquearse';
                            break;
                        case 'Reservada':
                            $error_message = 'La cama no puede estar reservada para bloquearse';
                            break;
                        case 'eliminado':
                            $error_message = 'No puede bloquearse la cama, esta está eliminada';
                            break;
                    }
                } else if ($reason_block === null) {
                    $error_message = 'Debe proporcionar una razón para el bloqueo.';
                } else {
                    // Insertar en bed_bloqued
                    $stmt_block = $pdo->prepare("INSERT INTO bed_blocked (bed_id, date_blocked, blocked_by, blocked_type, reason) VALUES (:bed_id, NOW(), :blocked_by, :blocked_type, :reason)");
                    $stmt_block->bindParam(':bed_id', $cama_id, PDO::PARAM_INT);
                    $stmt_block->bindParam(':blocked_by', $editor, PDO::PARAM_STR); // DNI como STR
                    $stmt_block->bindParam(':blocked_type', $bloqueo_tipo, PDO::PARAM_STR);
                    $stmt_block->bindParam(':reason', $reason_block, PDO::PARAM_STR);
                    $stmt_block->execute();

                    // Actualizar estado en beds
                    $stmt_update_bed = $pdo->prepare("UPDATE beds SET bed_status = 'Bloqueada' WHERE id = :cama_id");
                    $stmt_update_bed->bindParam(':cama_id', $cama_id, PDO::PARAM_INT);
                    $stmt_update_bed->execute();

                    if ($stmt_update_bed->rowCount() > 0) {
                        $success_message = 'Cama bloqueada correctamente.';
                        $is_status_changed = true;
                    } else {
                        $error_message = 'Error al bloquear la cama.';
                    }
                }
                break;

            case 'Desbloquear': // Este es el caso que estabas ajustando
                if ($current_bed_status == 'Libre') {
                    $error_message = 'La cama ya se encuentra Libre (desbloqueada).';
                } else if ($current_bed_status == 'Bloqueada') {
                    // Actualizar el registro de bloqueo en la tabla 'bed_blocked' (CORREGIDO: Usando 'bed_blocked')
                    $stmt_unblock = $pdo->prepare("UPDATE bed_blocked SET date_unblocked = NOW(), unblocked_by = :unblocked_by WHERE bed_id = :bed_id AND date_unblocked IS NULL");
                    $stmt_unblock->bindParam(':unblocked_by', $editor, PDO::PARAM_STR);
                    $stmt_unblock->bindParam(':bed_id', $cama_id, PDO::PARAM_INT);
                    $stmt_unblock->execute();

                    // Actualizar el estado de la cama a 'Libre' en la tabla 'beds'
                    $stmt_update_bed = $pdo->prepare("UPDATE beds SET bed_status = 'Libre' WHERE id = :cama_id");
                    $stmt_update_bed->bindParam(':cama_id', $cama_id, PDO::PARAM_INT);
                    $stmt_update_bed->execute();

                    if ($stmt_update_bed->rowCount() > 0) {
                        $success_message = 'Cama desbloqueada correctamente.';
                        $is_status_changed = true;
                    } else {
                        $error_message = 'Error al desbloquear la cama o la cama ya no estaba en estado "Bloqueada".';
                    }
                } else { // Cualquier otro estado que no sea 'Bloqueada' o 'Libre'
                    $error_message = 'No se puede desbloquear una cama con estado: ' . $current_bed_status . '.';
                }
                break;

            case 'Liberar': // Este es el caso que estabas ajustando
                if ($current_bed_status == 'Libre') {
                    $error_message = 'La cama ya se encuentra Libre (liberada).';
                } else if ($current_bed_status == 'Reservada') {
                    // Actualizar el registro de bloqueo en la tabla 'bed_blocked' (CORREGIDO: Usando 'bed_blocked')
                    $stmt_unblock = $pdo->prepare("UPDATE bed_blocked SET date_unblocked = NOW(), unblocked_by = :unblocked_by WHERE bed_id = :bed_id AND date_unblocked IS NULL");
                    $stmt_unblock->bindParam(':unblocked_by', $editor, PDO::PARAM_STR);
                    $stmt_unblock->bindParam(':bed_id', $cama_id, PDO::PARAM_INT);
                    $stmt_unblock->execute();

                    // Actualizar el estado de la cama a 'Libre' en la tabla 'beds'
                    $stmt_update_bed = $pdo->prepare("UPDATE beds SET bed_status = 'Libre' WHERE id = :cama_id");
                    $stmt_update_bed->bindParam(':cama_id', $cama_id, PDO::PARAM_INT);
                    $stmt_update_bed->execute();

                    if ($stmt_update_bed->rowCount() > 0) {
                        $success_message = 'Cama liberada correctamente.';
                        $is_status_changed = true;
                    } else {
                        $error_message = 'Error al liberar la cama o la cama ya no estaba en estado "Libre".';
                    }
                } else { // Cualquier otro estado que no sea 'Bloqueada' o 'Libre'
                    $error_message = 'No se puede liberar una cama con estado: ' . $current_bed_status . '.';
                }
                break;

            default:
                $error_message = 'Estado de cama no válido.';
                break;

        }

        // Establecer el mensaje de toast final
        if ($success_message) {
            $_SESSION['toast_message'] = [
                'message' => $success_message,
                'type' => 'success'
            ];
        } else if ($error_message) {
            $_SESSION['toast_message'] = [
                'message' => $error_message,
                'type' => 'error'
            ];
        }
    }

} catch (PDOException $e) {
    // Captura cualquier error de PDO en toda la lógica
    $_SESSION['toast_message'] = [
        'message' => 'Error en la base de datos: ' . $e->getMessage(),
        'type' => 'error',
        'duration' => 50000
    ];
}

// Redirige al usuario a la página anterior
header("Location: " . $_SERVER['HTTP_REFERER']);
exit();

?>