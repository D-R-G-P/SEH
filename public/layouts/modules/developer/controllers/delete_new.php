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
        $_SESSION['toast_message'] = [
            'message' => 'Update eliminado correctamente.',
            'type' => 'success'
        ];
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    } catch (Exception $e) {
        // Manejar errores de la base de datos
        $_SESSION['toast_message'] = [
            'message' => 'Error al eliminar la update: ' . htmlspecialchars($e->getMessage()),
            'type' => 'error'
        ];
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    }
} else {
    // Si los datos no están completos
    http_response_code(400);
    $_SESSION['toast_message'] = [
        'message' => 'Error al eliminar la update: Datos incompletos.',
        'type' => 'error'
    ];
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit();
}
