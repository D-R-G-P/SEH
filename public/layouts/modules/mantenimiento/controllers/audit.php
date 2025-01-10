<?php
require_once '../../../../../app/db/db.php';
session_start();

$db = new DB();
$pdo = $db->connect();

// Verificar si se recibió el ID a través de la solicitud GET
if (isset($_POST['id']) && $_POST['destino']) {
    $id = $_POST['id'];
    $destino = $_POST['destino'];

    // Realizar la actualización del estado en la base de datos
    $query = "UPDATE mantenimiento SET destino = :destino, fecha_reclamante = NOW() WHERE id = :id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':destino', $destino, PDO::PARAM_STR);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    $_SESSION['success_message'] = '<div class="notisContent"><div class="notis" id="notis" style="text-align: center;">Se ha dirigido el caso al sector auditado.</div></div><script>setTimeout(() => {notis.classList.toggle("active");out();}, 1);function out() {setTimeout(() => {notis.classList.toggle("active");}, 2500);}</script>';
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit(); // Finalizar el script después de la redirección
} else {
    // Si no se proporcionó un ID, devolver un mensaje de error
    http_response_code(400);
    $_SESSION['error_message'] = '<div class="notisContent"><div class="notiserror" id="notis">Error al redirigir el caso.</div></div><script>setTimeout(() => {notis.classList.toggle("active");out();}, 1);function out() {setTimeout(() => {notis.classList.toggle("active");}, 2500);}</script>';
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit(); // Finalizar el script después de la redirección
}
