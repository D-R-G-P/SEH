<?php
require_once '../../../../../app/db/db.php';

$db = new DB();
$pdo = $db->connect();

session_start();

if (isset($_POST['idModEsp'], $_POST['especialidadMod'])) {
    try {
        $id = $_POST['idModEsp'];
        $servicio = $_POST['especialidadMod'];

        $stmt = $pdo->prepare("UPDATE especialidades SET especialidad = ? WHERE id = ?");
        $stmt->execute([$servicio, $id]);

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
        'message' => 'Error al enviar los parámetros.',
        'type' => 'error' // Puede ser "success", "error", "warning" o "info"
    ];
}

header('Location: ' . $_SERVER['HTTP_REFERER']);
exit;
