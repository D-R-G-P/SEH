<?php
session_start(); // Inicia la sesión (si aún no se ha iniciado)

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Verifica que el formulario se ha enviado por el método POST

    // Realiza las validaciones necesarias antes de procesar el formulario
    $errors = array();

    // Validación para el campo "tipoAdd"
    if (empty($_POST["tipoAdd"])) {
        $errors[] = "El tipo de equipo es obligatorio.";
    }

    // Si hay errores, almacena mensajes de error en la sesión y redirige al formulario
    if (!empty($errors)) {
        $_SESSION['toast_message'] = [
            'message' => implode("<br>", $errors),
            'type' => 'error' // Puede ser "success", "error", "warning" o "info"
        ];
        exit("error");
    }

    // Si no hay errores, procesa el formulario
    $tipoAdd = $_POST["tipoAdd"];

    // Realiza la conexión a la base de datos (utiliza tu propia lógica para la conexión)
    require_once '../../../../../app/db/db.php';

    $db = new DB();
    $pdo = $db->connect();

    try {
        // Prepara la consulta SQL para la inserción
        $stmt = $pdo->prepare("INSERT INTO tipos_equipo (tipo_equipo) VALUES (?)");

        // Ejecuta la consulta con los valores proporcionados
        $stmt->execute([$tipoAdd]);

        // Cierra la conexión a la base de datos
        $pdo = null;

        // Almacena un mensaje de éxito en la sesión
        $_SESSION['toast_message'] = [
            'message' => 'Tipo de equipo agregado exitosamente.',
            'type' => 'success' // Puede ser "success", "error", "warning" o "info"
        ];
        exit("success");
    } catch (PDOException $e) {
        // Si hay un error en la base de datos, almacena el mensaje de error en la sesión y redirige al formulario
        $_SESSION['toast_message'] = [
            'message' => 'Error al agregar el tipo de equipo.',
            'type' => 'error' // Puede ser "success", "error", "warning" o "info"
        ];
        exit("error");
    }
} else {
    // Si alguien trata de acceder directamente a este script sin enviar el formulario, retorna un error
    $_SESSION['toast_message'] = [
        'message' => 'Acceso no autorizado.',
        'type' => 'error' // Puede ser "success", "error", "warning" o "info"
    ];
    exit("error");
}
?>
