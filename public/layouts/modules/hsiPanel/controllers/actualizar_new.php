<?php
require_once '../../../../../app/db/db.php';
session_start();

$db = new DB();
$pdo = $db->connect();

// Verificar si se recibió el ID a través de la solicitud POST
if (isset($_POST['id'])) {
    $id = $_POST['id'];

    // Realizar la actualización del estado en la base de datos
    $query = "UPDATE hsi SET new = 'no' WHERE id = :id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    $_SESSION['toast_message'] = [
        'message' => 'Marcado como notificado',
        'type' => 'success'
    ];
} else {
    // Si no se proporcionó un ID, devolver un mensaje de error
    http_response_code(400);
    $_SESSION['toast_message'] = [
        'message' => 'Error al generar la contraseña.',
        'type' => 'error'
    ];
}