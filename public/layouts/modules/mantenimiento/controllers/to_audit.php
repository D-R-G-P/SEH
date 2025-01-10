<?php
require_once '../../../../../app/db/db.php';
session_start();

$db = new DB();
$pdo = $db->connect();

// Verificar si se recibió el ID a través de la solicitud GET
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Realizar la actualización del estado en la base de datos
    $query = "UPDATE mantenimiento SET destino = 'auditoria' WHERE id = :id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    $_SESSION['success_message'] = '<div class="notisContent"><div class="notis" id="notis" style="text-align: center;">Se ha marcado el caso para auditar correctamente.</div></div><script>setTimeout(() => {notis.classList.toggle("active");out();}, 1);function out() {setTimeout(() => {notis.classList.toggle("active");}, 2500);}</script>';
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit(); // Finalizar el script después de la redirección
} else {
    // Si no se proporcionó un ID, devolver un mensaje de error
    http_response_code(400);
    $_SESSION['error_message'] = '<div class="notisContent"><div class="notiserror" id="notis">Error al marcar para auditoria.</div></div><script>setTimeout(() => {notis.classList.toggle("active");out();}, 1);function out() {setTimeout(() => {notis.classList.toggle("active");}, 2500);}</script>';
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit(); // Finalizar el script después de la redirección
}
