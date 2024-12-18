<?php
session_start(); // Inicia la sesión (si aún no se ha iniciado)

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Verifica que el formulario se ha enviado por el método POST

    // Si no hay errores, procesa el formulario
    $dni = $_POST['jubilarDniHidden'];
    $jubilarFecha = $_POST["jubilarFecha"];

    // Realiza la conexión a la base de datos (utiliza tu propia lógica para la conexión)
    require_once '../../../../../app/db/db.php';

    $db = new DB();
    $pdo = $db->connect();

    try {

        // Prepara la consulta SQL para la inserción
        $stmt = $pdo->prepare("UPDATE personal SET estado = CONCAT('Jubilado: ', ?) WHERE dni = ?");
$stmt->execute([$jubilarFecha, $dni]);



        // Cierra la conexión a la base de datos
        $pdo = null;

        // Almacena un mensaje de éxito en la sesión y redirige a una página de éxito
        $_SESSION['success_message'] = '<div class="notisContent"><div class="notis" id="notis">Pase de servicio realizado correctamente</div></div><script>setTimeout(() => {notis.classList.toggle("active");out();}, 1);function out() {setTimeout(() => {notis.classList.toggle("active");}, 2500);}</script>';
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    } catch (PDOException $e) {
        // Si hay un error en la base de datos, almacena el mensaje de error y redirige al formulario
        $_SESSION['error_message'] = '<div class="notisContent"><div class="notiserror" id="notis">Error al conectar a la base de datos' . $e->getMessage() . '.</div></div><script>setTimeout(() => {notis.classList.toggle("active");out();}, 1);function out() {setTimeout(() => {notis.classList.toggle("active");}, 2500);}</script>';
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    }
} else {
    // Si alguien trata de acceder directamente a este script sin enviar el formulario, redirige al formulario
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
}
