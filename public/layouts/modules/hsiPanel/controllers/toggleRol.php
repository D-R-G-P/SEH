<?php
require_once '../../../../../app/db/db.php';
session_start();

$db = new DB();
$pdo = $db->connect();

$rol = $_GET['rol'];
$estado = $_GET['toggle'];

if ($estado == "activar") {
    $estadoNew = "activo";
} else if ($estado == "desactivar") {
    $estadoNew = "desactivado";
}

// Realizar la actualización del estado en la base de datos
$query = "UPDATE roles_hsi SET estado = ? WHERE id = ?";
$stmt = $pdo->prepare($query);
$stmt->bindValue(1, $estadoNew, PDO::PARAM_STR);
$stmt->bindValue(2, $rol, PDO::PARAM_STR);
if ($stmt->execute()) {
    // Si la consulta se ejecuta con éxito, redirigir al usuario a una página de éxito o mostrar un mensaje de éxito
    $_SESSION['success_message'] = '<div class="notisContent"><div class="notis" id="notis" style="text-align: center;">Se cambió correctamente el estado del rol.</div></div><script>setTimeout(() => {notis.classList.toggle("active");out();}, 1);function out() {setTimeout(() => {notis.classList.toggle("active");}, 2500);} window.addEventListener("DOMContentLoaded", () => { back.style.display = "flex"; rolesModule.style.display = "flex"; })</script>';
    header("Location: ../hsiAdmin.php");
    exit(); // Finalizar el script después de la redirección
} else {
    // Manejar el error si la consulta no se ejecuta correctamente
    $_SESSION['error_message'] = '<div class="notisContent"><div class="notiserror" id="notis">Error al cambiar el estado del rol.</div></div><script>setTimeout(() => {notis.classList.toggle("active");out();}, 1);function out() {setTimeout(() => {notis.classList.toggle("active");}, 2500);} window.addEventListener("DOMContentLoaded", () => { back.style.display = "flex"; rolesModule.style.display = "flex"; })</script>';
    header("Location: ../hsiAdmin.php");
    exit(); // Finalizar el script después de la redirección
}
?>
