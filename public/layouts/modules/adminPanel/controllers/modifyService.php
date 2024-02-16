<?php
require_once '../../../../../app/db/db.php';

$db = new DB();
$pdo = $db->connect();

session_start();

if (isset($_POST['idMod'], $_POST['servicioMod'], $_POST['jefeMod'])) {
    try {
        $id = $_POST['idMod'];
        $servicio = $_POST['servicioMod'];
        $jefe = $_POST['jefeMod'];

        $stmt = $pdo->prepare("UPDATE servicios SET servicio = ?, jefe = ? WHERE id = ?");
        $stmt->execute([$servicio, $jefe, $id]);

        $stmtJefe = $pdo->prepare("UPDATE personal SET servicio_id = ?, cargo = ? WHERE dni = ?");
        $stmtJefe->execute([$id, "Jefe de servicio", $jefe]);

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
