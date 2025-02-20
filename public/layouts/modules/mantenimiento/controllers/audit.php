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

    $_SESSION['toast_message'] = [
        'message' => 'Se ha dirigido el caso al sector auditado.',
        'type' => 'success'
    ];
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit(); // Finalizar el script después de la redirección
} else {
    // Si no se proporcionó un ID, devolver un mensaje de error
    http_response_code(400);
    $_SESSION['toast_message'] = [
        'message' => 'Error al redirigir el caso.',
        'type' => 'error'
    ];
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit(); // Finalizar el script después de la redirección
}
