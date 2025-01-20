<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Verifica que el formulario se ha enviado por el método POST

    // Sanear y validar entradas
    $apellido = htmlspecialchars(trim($_POST["apellido"]));
    $nombre = htmlspecialchars(trim($_POST["nombre"]));
    $dni = htmlspecialchars(trim($_POST["dni"]));
    $servicio = htmlspecialchars(trim($_POST["servicio"]));
    $especialidad = htmlspecialchars(trim($_POST["especialidad"])) ?: ""; // Si está vacío, asigna "0"
    $mn = htmlspecialchars(trim($_POST["mn"])) ?: "";
    $mp = htmlspecialchars(trim($_POST["mp"])) ?: "";
    $sistemas = json_encode([
        ["sistema" => "Deposito", "activo" => "no"],
        ["sistema" => "Mantenimiento", "activo" => "no"],
        ["sistema" => "Informatica", "activo" => "no"]
    ]);
    $cargo = htmlspecialchars(trim($_POST["cargo"]));
    $rol = htmlspecialchars(trim($_POST["rol"]));

    // Realiza la conexión a la base de datos (utiliza tu propia lógica para la conexión)
    require_once '../../../../../app/db/db.php';

    $db = new DB();
    $pdo = $db->connect();

    try {
        // Prepara la consulta SQL para la inserción
        $stmt = $pdo->prepare("INSERT INTO personal (apellido, nombre, dni, servicio_id, cargo, especialidad, mn, mp, sistemas, rol, estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$apellido, $nombre, $dni, $servicio, $cargo, $especialidad, $mn, $mp, $sistemas, $rol, 'Activo']);

        // Cierra la conexión a la base de datos
        $pdo = null;

        // Almacena un mensaje de éxito en la sesión y redirige a una página de éxito
        $_SESSION['success_message'] = '<div class="notisContent"><div class="notis" id="notis">Personal agregado correctamente</div></div><script>setTimeout(() => {notis.classList.toggle("active");out();}, 1);function out() {setTimeout(() => {notis.classList.toggle("active");}, 2500);}</script>';
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    } catch (PDOException $e) {
        // Si hay un error en la base de datos, almacena el mensaje de error y redirige al formulario
        error_log($e->getMessage()); // Log del error para depuración
        $_SESSION['error_message'] = '<div class="notisContent"><div class="notiserror" id="notis">Error al conectar a la base de datos. Por favor, inténtelo de nuevo más tarde. '. $e->getMessage() .'</div></div><script>setTimeout(() => {notis.classList.toggle("active");out();}, 1);function out() {setTimeout(() => {notis.classList.toggle("active");}, 2500);}</script>';
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    }
} else {
    // Si alguien trata de acceder directamente a este script sin enviar el formulario, redirige al formulario
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
}
?>
