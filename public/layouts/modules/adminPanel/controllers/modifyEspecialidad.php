<?php
require_once '../../../../../app/db/db.php';

$db = new DB();
$pdo = $db->connect();

session_start();

if (isset($_POST['idModEsp'], $_POST['especialidadMod'])) {
    try {
        $id = $_POST['idModEsp'];
        $servicio = $_POST['especialidadMod'];

        $stmt = $pdo->prepare("UPDATE especialidades SET especialidad = ? WHERE id = ?");
        $stmt->execute([$servicio, $id]);

        $_SESSION['success_message'] = '<div class="notisContent"><div class="notis" id="notis">Cambio realizado correctamente.</div></div><script>setTimeout(() => {notis.classList.toggle("active");out();}, 1);function out() {setTimeout(() => {notis.classList.toggle("active");}, 2500);}</script>';
    } catch (PDOException $e) {
        $_SESSION['error_message'] = '<div class="notisContent"><div class="notiserror" id="notis">Error en la base de datos: ' . $e->getMessage() . '</div></div><script>setTimeout(() => {notis.classList.toggle("active");out();}, 1);function out() {setTimeout(() => {notis.classList.toggle("active");}, 2500);}</script>';
    } catch (Exception $e) {
        $_SESSION['error_message'] = '<div class="notisContent"><div class="notiserror" id="notis">Error: ' . $e->getMessage() . '.</div></div><script>setTimeout(() => {notis.classList.toggle("active");out();}, 1);function out() {setTimeout(() => {notis.classList.toggle("active");}, 2500);}</script>';
    }
} else {
    $_SESSION['error_message'] = '<div class="notisContent"><div class="notiserror" id="notis">Error al enviar los parámetros.</div></div><script>setTimeout(() => {notis.classList.toggle("active");out();}, 1);function out() {setTimeout(() => {notis.classList.toggle("active");}, 2500);}</script>';
}

header('Location: ' . $_SERVER['HTTP_REFERER']);
exit;
