<?php
session_start();
// Verificar si se ha enviado el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Incluir la conexión a la base de datos
    require_once '../../../../../app/db/db.php';

    // Crear una instancia de la clase DB
    $db = new DB();
    $pdo = $db->connect();

    // Obtener los valores del formulario
    $idUsuario = $_POST['idUser'];
    $apellido = $_POST['apellido'];
    $nombre = $_POST['nombre'];
    $dni = $_POST['dni'];
    $mn = $_POST['mn'];
    $mp = $_POST['mp'];
    $pr = "no";

    // Verificar si la contraseña ha sido modificada
    if (!empty($_POST['updatePassword'])) {
        $password = md5($_POST['updatePassword']); // Aplicar hash MD5 a la nueva contraseña
        // Actualizar el usuario con la nueva contraseña
        $stmt = $pdo->prepare("UPDATE personal SET apellido = ?, nombre = ?, dni = ?, password = ?, mn = ?, mp = ?, pr = ? WHERE id = ?");
        $stmt->execute([$apellido, $nombre, $dni, $password, $mn, $mp, $pr, $idUsuario]);
    } else {
        // Si la contraseña no ha sido modificada, actualizar el usuario sin cambiar la contraseña
        $stmt = $pdo->prepare("UPDATE personal SET apellido = ?, nombre = ?, dni = ?, mn = ?, mp = ?, pr = ? WHERE id = ?");
        $stmt->execute([$apellido, $nombre, $dni, $mn, $mp, $pr, $idUsuario]);
    }

    $_SESSION['success_message'] = '<div class="notisContent"><div class="notis" id="notis">Perfil modificado correctamente.</div></div><script>setTimeout(() => {notis.classList.toggle("active");out();}, 1);function out() {setTimeout(() => {notis.classList.toggle("active");}, 2500);}</script>';
        header("Location: ../miUsuario.php");
        exit;
} else {
    $_SESSION['error_message'] = '<div class="notisContent"><div class="notiserror" id="notis">Error al registrar el formulario.</div></div><script>setTimeout(() => {notis.classList.toggle("active");out();}, 1);function out() {setTimeout(() => {notis.classList.toggle("active");}, 2500);}</script>';
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
}