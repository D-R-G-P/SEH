<?php
require_once '../../../../../app/db/db.php';
session_start();

$db = new DB();
$pdo = $db->connect();

// Verificar si se recibieron los datos necesarios
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    try {
        // Actualizar el módulo existente
        $query = "DELETE FROM updates WHERE id = :id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        // Ejecutar la consulta
        $stmt->execute();

        // Mensaje de éxito
        $_SESSION['success_message'] = '<div class="notisContent"><div class="notis" id="notis" style="text-align: center;">Update eliminado correctamente.</div></div><script>setTimeout(() => {notis.classList.toggle("active");out();}, 1);function out() {setTimeout(() => {notis.classList.toggle("active");}, 2500);}</script>';
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    } catch (Exception $e) {
        // Manejar errores de la base de datos
        $_SESSION['error_message'] = '<div class="notisContent"><div class="notiserror" id="notis">Error al eliminar la update: ' . htmlspecialchars($e->getMessage()) . '</div></div><script>setTimeout(() => {notis.classList.toggle("active");out();}, 1);function out() {setTimeout(() => {notis.classList.toggle("active");}, 2500);}</script>';
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    }
} else {
    // Si los datos no están completos
    http_response_code(400);
    $_SESSION['error_message'] = '<div class="notisContent"><div class="notiserror" id="notis">Error al eliminar la update: Datos incompletos.</div></div><script>setTimeout(() => {notis.classList.toggle("active");out();}, 1);function out() {setTimeout(() => {notis.classList.toggle("active");}, 2500);}</script>';
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit();
}
