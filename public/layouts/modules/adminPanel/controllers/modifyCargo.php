<?php
require_once '../../../../../app/db/db.php';

$db = new DB();
$pdo = $db->connect();

session_start();

if (isset($_POST['idModCargo'], $_POST['cargoMod'])) {
    try {
        $id = $_POST['idModCargo'];
        $cargo = $_POST['cargoMod'];

        $stmt = $pdo->prepare("UPDATE cargos SET cargo = ? WHERE id = ?");
        $stmt->execute([$cargo, $id]);

        $_SESSION['toast_message'] = [
            'message' => 'Cambio realizado correctamente.',
            'type' => 'success' // Puede ser "success", "error", "warning" o "info"
        ];
    } catch (PDOException $e) {
        $_SESSION['toast_message'] = [
            'message' => 'Error en la base de datos: ' . $e->getMessage() . '.',
            'type' => 'error' // Puede ser "success", "error", "warning" o "info"
        ];
    } catch (Exception $e) {
        $_SESSION['toast_message'] = [
            'message' => 'Error: ' . $e->getMessage() . '.',
            'type' => 'error' // Puede ser "success", "error", "warning" o "info"
        ];
    }
} else {
    $_SESSION['toast_message'] = [
        'message' => 'Error al enviar los parametros.',
        'type' => 'error' // Puede ser "success", "error", "warning" o "info"
    ];
}

header('Location: ' . $_SERVER['HTTP_REFERER']);
exit;
