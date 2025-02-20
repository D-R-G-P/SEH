<?php
session_start(); // Inicia la sesión (si aún no se ha iniciado)

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Verifica que el formulario se ha enviado por el método POST

    // Realiza las validaciones necesarias antes de procesar el formulario
    $errors = array();

    // Validación para el campo "servicio"
    if (empty($_POST["cargo"])) {
        $errors[] = "El nombre del cargo es obligatorio.";
    }

    // Si hay errores, almacena mensajes de error en la sesión y redirige al formulario
    if (!empty($errors)) {
        $_SESSION['toast_message'] = [
            'message' => implode("<br>", $errors),
            'type' => 'error' // Puede ser "success", "error", "warning" o "info"
        ];
        header("Location: ../adminPanel.php");
        exit;
    }

    // Si no hay errores, procesa el formulario
    $cargo = $_POST["cargo"];

    // Realiza la conexión a la base de datos (utiliza tu propia lógica para la conexión)
    require_once '../../../../../app/db/db.php';

    $db = new DB();
    $pdo = $db->connect();

    try {

        // Prepara la consulta SQL para la inserción
        $stmt = $pdo->prepare("INSERT INTO cargos (cargo, estado) VALUES (?, 'Activo')");

        // Ejecuta la consulta con los valores proporcionados
        $stmt->execute([$cargo]);

        // Cierra la conexión a la base de datos
        $pdo = null;

        // Almacena un mensaje de éxito en la sesión y redirige a una página de éxito
        $_SESSION['toast_message'] = [
            'message' => 'El cargo se ha agregado correctamente',
            'type' => 'success' // Puede ser "success", "error", "warning" o "info"
        ];
        header("Location: ../adminPanel.php");
        exit;
    } catch (PDOException $e) {
        // Si hay un error en la base de datos, almacena el mensaje de error y redirige al formulario
        $_SESSION['toast_message'] = [
            'message' => 'Error al conectar a la base de datos: ' . $e->getMessage(),
            'type' => 'error' // Puede ser "success", "error", "warning" o "info"
        ];
        header("Location: ../adminPanel.php");
        exit;
    }
} else {
    // Si alguien trata de acceder directamente a este script sin enviar el formulario, redirige al formulario
    $_SESSION['toast_message'] = [
        'message' => 'Acceso no autorizado',
        'type' => 'warning' // Puede ser "success", "error", "warning" o "info"
    ];
    header("Location: ../adminPanel.php");
    exit;
}
