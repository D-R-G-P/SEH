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

$cama_id = isset($_POST['cama_id']) ? $_POST['cama_id'] : null;
$deleted_reaseon = isset($_POST['deleted_reason']) ? $_POST['deleted_reason'] : null;
$editor = $user->getDni();

if (!hasSubAccess(['administrador_camas']) || !hasAccess(['administrador', 'direccion'])) {
    $_SESSION['toast_message'] = [
        'type' => 'error',
        'message' => 'No tienes permisos para eliminar camas.'
    ];
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit();
}

$sql = "SELECT id FROM beds WHERE id = :cama_id AND bed_status = 'Ocupada'";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':cama_id', $cama_id, PDO::PARAM_INT);
$stmt->execute();
$stmt = $stmt->fetchColumn();
if ($stmt) {
    $_SESSION['toast_message'] = [
        'type' => 'error',
        'message' => 'No se puede eliminar una cama ocupada.'
    ];
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit();
}

if ($cama_id) {
    $sql = "UPDATE beds SET bed_status = 'eliminado', deleted_by = :deleted_by, date_deleted = NOW(), deleted_reason = :deleted_reason WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $cama_id, PDO::PARAM_INT);
    $stmt->bindParam(':deleted_by', $editor, PDO::PARAM_INT);
    $stmt->bindParam(':deleted_reason', $deleted_reaseon, PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $_SESSION['toast_message'] = [
            'type' => 'success',
            'message' => 'Cama eliminada correctamente.'
        ];
    } else {
        $_SESSION['toast_message'] = [
            'type' => 'error',
            'message' => 'No se pudo eliminar la cama.'
        ];
    }
} else {
    $_SESSION['toast_message'] = [
        'type' => 'error',
        'message' => 'ID de cama no proporcionado.'
    ];
}

header("Location: " . $_SERVER['HTTP_REFERER']);

?>