<?php
require_once '../../../../../app/db/db.php';
$db = new DB();
$pdo = $db->connect();

// Obtener todos los pasos
$stmt = $pdo->prepare("SELECT * FROM pasos WHERE estado = 'activo' ORDER BY id");
$stmt->execute();
$pasos = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($pasos as &$paso) {
    $stmtOpc = $pdo->prepare("
        SELECT o.texto_opcion, p2.titulo AS paso_destino
        FROM opciones o
        LEFT JOIN pasos p2 ON o.paso_destino_id = p2.id
        WHERE o.paso_origen_id = ? AND o.estado = 'activo'
        ORDER BY o.orden IS NULL, o.orden ASC, o.id ASC
    ");
    $stmtOpc->execute([$paso['id']]);
    $paso['opciones'] = $stmtOpc->fetchAll(PDO::FETCH_ASSOC);
}

header('Content-Type: application/json');
echo json_encode($pasos);
