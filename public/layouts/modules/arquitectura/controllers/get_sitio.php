<?php
include_once '../../../../../app/db/db.php'; // Conexión a la base de datos

header('Content-Type: application/json'); // Asegurar que la respuesta sea JSON

$db = new DB();
$pdo = $db->connect();

if (isset($_POST['id'])) {
    $id = intval($_POST['id']); // Convertir a entero para seguridad

    // Verificar si existe el sitio
    $sql = "SELECT * FROM arquitectura WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $sitio = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($sitio) {
        // Convertir "estado" a booleano para el checkbox
        $sitio['activo'] = ($sitio['estado'] == 'Activo') ? 1 : 0;
        echo json_encode($sitio);
    } else {
        echo json_encode(['error' => 'No se encontró el sitio']);
    }
} else {
    echo json_encode(['error' => 'ID no proporcionado']);
}