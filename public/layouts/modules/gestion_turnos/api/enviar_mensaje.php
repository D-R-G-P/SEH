<?php
require_once '../../../../../app/db/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $chat_id = $_POST['chat_id'] ?? '';
    $mensaje = $_POST['mensaje'] ?? '';

    if (empty($chat_id) || empty($mensaje)) {
        echo json_encode(['error' => 'Chat ID y mensaje requeridos']);
        exit;
    }

    try {
        $db = new DB();
        $pdo = $db->connect();

        // Guardar mensaje del paciente
        $stmt = $pdo->prepare("INSERT INTO mensajes (chat_id, remitente, mensaje) VALUES (?, 'paciente', ?)");
        $stmt->execute([$chat_id, $mensaje]);

        // Lógica de respuesta automática
        $respuesta = "Gracias por tu consulta. Un agente te responderá pronto.";

        // Guardar respuesta del bot
        $stmt = $pdo->prepare("INSERT INTO mensajes (chat_id, remitente, mensaje) VALUES (?, 'bot', ?)");
        $stmt->execute([$chat_id, $respuesta]);

        echo json_encode(['status' => 'success', 'respuesta' => $respuesta]);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]);
    }
}
?>
