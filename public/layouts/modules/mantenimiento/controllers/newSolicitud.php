<?php
session_start(); // Inicia la sesión (si aún no se ha iniciado)

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Verifica que el formulario se ha enviado por el método POST

    // Realiza las validaciones necesarias antes de procesar el formulario
    $errors = array();

    // Validación para el campo "servicio"
    if (empty($_POST["reclamante"])) {
        $errors[] = "Error al obtener la sesión.";
    }

    if (empty($_POST["solicitudServicio"])) {
        $errors[] = "El servicio es obligatorio.";
    }

    if (empty($_POST["problema"])) {
        $errors[] = "El problema es obligatorio.";
    }

    if (empty($_POST["problema_locate"])) {
        $errors[] = "La localización del problema es obligatoria.";
    }

    // Si hay errores, almacena mensajes de error en la sesión y redirige al formulario
    if (!empty($errors)) {
        $_SESSION['error_message'] = implode("<br>", $errors);
        header("Location: ../mantenimiento.php");
        exit;
    }

    // Si no hay errores, procesa el formulario
    $reclamante = $_POST["reclamante"];
    $solicitudServicio = $_POST["solicitudServicio"];
    $problema = $_POST["problema"];
    $problema_locate = $_POST["problema_locate"];
    $estado = "Pendiente";

    // Realiza la conexión a la base de datos (utiliza tu propia lógica para la conexión)
    require_once '../../../../../app/db/db.php';

    $db = new DB();
    $pdo = $db->connect();

    try {

        // Prepara la consulta SQL para la inserción
        $stmt = $pdo->prepare("INSERT INTO mantenimiento (servicio, localizacion_explicada, problema, estado_reclamante, reclamante, estado_mantenimiento, new_reclamante, new_mantenimiento) VALUES (?, ?, ?, ?, ?, ?, 'no', 'si')");

        // Ejecuta la consulta con los valores proporcionados
        $stmt->execute([$solicitudServicio, $problema_locate, $problema, $estado, $reclamante, $estado]);

        // Cierra la conexión a la base de datos
        $pdo = null;

        // Almacena un mensaje de éxito en la sesión y redirige a una página de éxito
        $_SESSION['success_message'] = '<div class="notisContent"><div class="notis" id="notis">Solicitud realizada correctamente</div></div><script>setTimeout(() => {notis.classList.toggle("active");out();}, 1);function out() {setTimeout(() => {notis.classList.toggle("active");}, 2500);}</script>';
        header("Location: ../mantenimiento.php");
        exit;
    } catch (PDOException $e) {
        // Si hay un error en la base de datos, almacena el mensaje de error y redirige al formulario
        $_SESSION['error_message'] = '<div class="notisContent"><div class="notiserror" id="notis">Error al conectar a la base de datos' . $e->getMessage() . '.</div></div><script>setTimeout(() => {notis.classList.toggle("active");out();}, 1);function out() {setTimeout(() => {notis.classList.toggle("active");}, 2500);}</script>';
        header("Location: ../mantenimiento.php");
        exit;
    }
} else {
    // Si alguien trata de acceder directamente a este script sin enviar el formulario, redirige al formulario
    header("Location: ../mantenimiento.php");
    exit;
}
