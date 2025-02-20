<?php
require_once '../../../../../app/db/db.php';

$db = new DB();
$pdo = $db->connect();

session_start();

if (isset($_POST['idMod'], $_POST['servicioMod'], $_POST['jefeMod'])) {
    try {
        $id = $_POST['idMod'];
        $servicio = $_POST['servicioMod'];
        $jefe = $_POST['jefeMod'];

        $stmt = $pdo->prepare("UPDATE servicios SET servicio = ?, jefe = ? WHERE id = ?");
        $stmt->execute([$servicio, $jefe, $id]);

        $stmtJefe = $pdo->prepare("UPDATE personal SET servicio_id = ?, cargo = ? WHERE dni = ?");
        $stmtJefe->execute([$id, "Jefe de servicio", $jefe]);

        $_SESSION['toast_message'] = [
            'message' => 'Cambio realizado correctamente.',
            'type' => 'success'
        ];
        } catch (PDOException $e) {
        $_SESSION['toast_message'] = [
            'message' => 'Error en la base de datos: ' . $e->getMessage(),
            'type' => 'error'
        ];
        } catch (Exception $e) {
        $_SESSION['toast_message'] = [
            'message' => 'Error: ' . $e->getMessage(),
            'type' => 'error'
        ];
        }
    } else {
        $_SESSION['toast_message'] = [
        'message' => 'Error al enviar los parámetros.',
        'type' => 'error'
        ];
}

header('Location: ' . $_SERVER['HTTP_REFERER']);
exit;
