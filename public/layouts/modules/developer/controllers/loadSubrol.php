<?php
require_once '../../../../../app/db/db.php';
session_start();

$db = new DB();
$pdo = $db->connect();

if (isset($_POST['id'])) {
    $id = $_POST['id'];

    try {
        $query = "SELECT id, rol_id, subrol, nombre, modulo, descripcion, estado FROM subroles WHERE id = :id LIMIT 1";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $subrol = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($subrol) {
            echo json_encode($subrol); // Devolver el subrol en formato JSON
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Subrol no encontrado']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al cargar el subrol: ' . $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'ID no proporcionado']);
}
