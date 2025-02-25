<?php
session_start(); // Inicia la sesión (si aún no se ha iniciado)

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Verifica que el formulario se ha enviado por el método POST

    // Si no hay errores, procesa el formulario
    $dni = $_POST['finContratoDniHidden'];
    $finContratoFecha = $_POST["finContratoFecha"];
    $finContratoMotivo = $_POST['finContratoMotivo'];

    // Realiza la conexión a la base de datos (utiliza tu propia lógica para la conexión)
    require_once '../../../../../app/db/db.php';

    $db = new DB();
    $pdo = $db->connect();

    try {

        // Prepara la consulta SQL para la inserción
        $stmt = $pdo->prepare('UPDATE personal SET estado = CONCAT("Fin de contrato por: ", ?, " desde el: ", ?), password = "" WHERE dni = ?');
        $stmt->execute([$finContratoMotivo, $finContratoFecha, $dni]);

        $rol = $pdo->prepare('DELETE FROM usuarios_roles WHERE dni = ?');
        $rol->execute([$dni]);

        $subrol = $pdo->prepare('DELETE FROM usuarios_subroles WHERE dni = ?');
        $subrol->execute([$dni]);

        // Cierra la conexión a la base de datos
        $pdo = null;

        // Almacena un mensaje de éxito en la sesión y redirige a una página de éxito
        $_SESSION['toast_message'] = [
            'message' => 'Baja de agente realizado correctamente',
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
