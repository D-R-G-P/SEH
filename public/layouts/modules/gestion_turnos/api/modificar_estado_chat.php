<?php
require_once '../../../../../app/db/db.php';

header('Content-Type: application/json');

// Obtener datos del cuerpo de la solicitud
$input = json_decode(file_get_contents("php://input"), true);
$chat_id = $input['chat_id'] ?? null;
$estado = $input['estado'] ?? null;
$agente = $input['agente'] ?? null;

if (empty($chat_id) || empty($estado)) {
    echo json_encode(['error' => 'Id del chat y estado son requeridos']);
    exit;
}

try {
    $db = new DB();
    $pdo = $db->connect();

    // Actualizar el estado del chat
    $stmt = $pdo->prepare("UPDATE chats SET estado = ?, asignado = ? WHERE id = ?");
    $stmt->execute([$estado, $agente, $chat_id]);

    if ($estado == 'finalizado') {
        $stmt = $pdo->prepare("UPDATE chats SET fecha_cierre = NOW() WHERE id = ?");
        $stmt->execute([$chat_id]);

        $consulta = $pdo->prepare("SELECT numero FROM chats WHERE id = ? LIMIT 1");
        $consulta->execute([$chat_id]);

        $numero = $consulta->fetchColumn();
        $mensaje = "!hsmfinishchat";
        $estadoMsg = "pendiente";

        $message = $pdo->prepare("INSERT INTO wsp_messages (numero, mensaje, chat_id, estado, remitente, timestamp) VALUES (?, ?, ?, ?, ?, NOW())");
        $message->execute([$numero, $mensaje, $chat_id, $estadoMsg, $agente]);
    }

    // Verificar si la consulta afectó alguna fila
    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Chat actualizado correctamente.'
        ]);

    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No se encontró el chat, el estado ya estaba actualizado, o no hubo cambios en los datos proporcionados'
        ]);

    }
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error en la base de datos: ' . $e->getMessage()
    ]);
}