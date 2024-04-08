<?php
require_once '../../../../../app/db/db.php';

$db = new DB();
$pdo = $db->connect();

session_start();

if (isset($_POST['idModCargo'], $_POST['cargoMod'])) {
    try {
        $id = $_POST['idModCargo'];
        $cargo = $_POST['cargoMod'];

        $stmt = $pdo->prepare("UPDATE cargos SET cargo = ? WHERE id = ?");
        $stmt->execute([$cargo, $id]);

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
