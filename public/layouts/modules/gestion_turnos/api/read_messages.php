<?php

header('Content-Type: application/json');

require_once '../../../../../app/db/db.php';

$chatId = $_GET['chatId'] ?? null;
$agent = $_GET['agent'] ?? null;

if (empty($chatId) || empty($agent)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Parámetros requeridos ausentes'
    ]);
    exit;
}

try {
    $db = new DB();
    $pdo = $db->connect();

    // CORREGIDO: uso de IS NULL
    $stmt = $pdo->prepare("UPDATE wsp_messages SET open = 1, opened_at = NOW(), opened_for = ? WHERE chat_id = ? AND open IS NULL");
    $stmt->execute([$agent, $chatId]);

    echo json_encode([
        'status' => 'success',
        'message' => 'Mensajes marcados como leídos'
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error al marcar mensajes como leídos: ' . $e->getMessage()
    ]);
    exit;
}
