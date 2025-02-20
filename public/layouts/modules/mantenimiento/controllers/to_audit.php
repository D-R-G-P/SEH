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

    $_SESSION['toast_message'] = [
        'message' => 'Se ha marcado el caso para auditar correctamente.',
        'type' => 'success'
    ];
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit(); // Finalizar el script después de la redirección
} else {
    // Si no se proporcionó un ID, devolver un mensaje de error
    http_response_code(400);
    $_SESSION['toast_message'] = [
        'message' => 'Error al marcar para auditoria.',
        'type' => 'error'
    ];
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit(); // Finalizar el script después de la redirección
}
