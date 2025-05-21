<?php
require_once '../../../../../app/db/db.php';
$db = new DB();
$pdo = $db->connect();

header('Content-Type: application/json');

// Validar entrada
$texto_opcion = trim($_POST['texto_opcion'] ?? '');
$paso_origen_id = intval($_POST['paso_origen_id'] ?? 0);
$paso_destino_id = isset($_POST['paso_destino_id']) && $_POST['paso_destino_id'] !== '' ? intval($_POST['paso_destino_id']) : null;
$estado = $_POST['estado'] ?? 'activo';

if (!$texto_opcion || !$paso_origen_id || !in_array($estado, ['activo', 'inactivo'])) {
    echo json_encode(['success' => false, 'mensaje' => 'Datos inválidos.']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO opciones (texto_opcion, paso_origen_id, paso_destino_id, estado) VALUES (?, ?, ?, ?)");
    $stmt->execute([$texto_opcion, $paso_origen_id, $paso_destino_id, $estado]);

    echo json_encode(['success' => true, 'mensaje' => 'Opción creada con éxito.']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'mensaje' => 'Error: ' . $e->getMessage()]);
}
