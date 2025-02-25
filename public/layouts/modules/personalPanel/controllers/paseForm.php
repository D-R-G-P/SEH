<?php
session_start(); // Inicia la sesión (si aún no se ha iniciado)

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Verifica que el formulario se ha enviado por el método POST

    // Si no hay errores, procesa el formulario
    $id = $_POST['paseId'];
    $servicio = $_POST["paseSelectServicio"];

    // Realiza la conexión a la base de datos (utiliza tu propia lógica para la conexión)
    require_once '../../../../../app/db/db.php';

    $db = new DB();
    $pdo = $db->connect();

    try {

        // Prepara la consulta SQL para la inserción
        $stmt = $pdo->prepare('UPDATE personal SET servicio_id = ?, especialidad = "", password = "" WHERE id = ?');
        $stmt->execute([$servicio, $id]);

        $dniStmt = $pdo->prepare('SELECT dni FROM personal WHERE id = ?');
        $dniStmt->execute([$id]);
        $dni = $dniStmt->fetchColumn();

        $rol = $pdo->prepare('DELETE FROM usuarios_roles WHERE dni = ?');
        $rol->execute([$dni]);

        $subrol = $pdo->prepare('DELETE FROM usuarios_subroles WHERE dni = ?');
        $subrol->execute([$dni]);

        // Cierra la conexión a la base de datos
        $pdo = null;

        // Almacena un mensaje de éxito en la sesión y redirige a una página de éxito
        $_SESSION['toast_message'] = [
            'message' => 'Pase de servicio realizado correctamente',
            'type' => 'success'
        ];
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    } catch (PDOException $e) {
        // Si hay un error en la base de datos, almacena el mensaje de error y redirige al formulario
        $_SESSION['toast_message'] = [
            'message' => 'Error al conectar a la base de datos: ' . $e->getMessage(),
            'type' => 'error'
        ];
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    }
} else {
    // Si alguien trata de acceder directamente a este script sin enviar el formulario, redirige al formulario
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
}
?>
