<?php
require_once '../../../../../app/db/db.php';
session_start();

$db = new DB();
$pdo = $db->connect();

if (isset($_POST['id'])) {
    $id = $_POST['id'];

    try {
        $query = "SELECT id, role, nombre, modulo, descripcion, estado FROM roles WHERE id = :id LIMIT 1";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $rol = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($rol) {
            echo json_encode($rol); // Devolver el rol en formato JSON
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Rol no encontrado']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al cargar el rol: ' . $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'ID no proporcionado']);
}
