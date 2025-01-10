<?php
// Conexión a la base de datos
require_once '../../../../../app/db/db.php';
$db = new DB();
$pdo = $db->connect();

if (isset($_POST['modulo_id'])) {
    $modulo_id = intval($_POST['modulo_id']);

    // Si no hay un módulo asociado, devolver vacío
    if ($modulo_id === 0) {
        echo json_encode([]);
        exit;
    }

    // Consulta para obtener los roles asociados al módulo
    $query = "SELECT id, nombre FROM roles WHERE modulo = :modulo_id";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['modulo_id' => $modulo_id]);

    // Obtener resultados
    $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Enviar los resultados como JSON
    echo json_encode($roles);
    exit;
} else {
    // Responder vacío si no se envía módulo_id
    echo json_encode([]);
    exit;
}
?>
