<?php

require_once '../../../../../app/db/db.php'; // Asegúrate que la ruta es correcta

header('Content-Type: application/json'); // Correcto: la respuesta será JSON

// 1. Leer el cuerpo JSON de la solicitud
$json_data = file_get_contents('php://input');

// 2. Decodificar el JSON a un objeto PHP (o array asociativo si pones 'true')
$data = json_decode($json_data);

// 3. Validar que el JSON se decodificó y tiene las propiedades esperadas
//    Usamos 'isset' o 'property_exists' en lugar de solo verificar si son falsy,
//    porque un valor podría ser 0 o una cadena vacía legítimamente.
if ($data === null ||
    !property_exists($data, 'numero') ||
    !property_exists($data, 'mensaje') ||
    !property_exists($data, 'chat_id') ||
    !property_exists($data, 'remitente')) {

    // Cambiamos el formato de respuesta para ser consistente (success/error)
    echo json_encode(["success" => false, "error" => "Faltan parámetros requeridos o JSON inválido"]);
    exit;
}

// 4. Asignar las propiedades del objeto a variables (opcional, pero más claro)
$numero = $data->numero;
$mensaje = $data->mensaje;
$chat_id = $data->chat_id;
$remitente = $data->remitente;

// 5. Definir la variable $estado (¡IMPORTANTE!)
//    Ajusta este valor según tu lógica (p. ej., 'enviado', 'recibido_servidor', etc.)
$estado = 'pendiente'; // <- DEFINE UN VALOR POR DEFECTO APROPIADO

try {
    $db = new DB();
    $pdo = $db->connect();

    // La consulta SQL parece correcta, ahora $estado está definido
    $stmt = $pdo->prepare("INSERT INTO wsp_messages (numero, mensaje, chat_id, estado, remitente, timestamp, open) VALUES (?, ?, ?, ?, ?, NOW(), '1')");

    // Ejecutar con las variables correctas
    $stmt->execute([$numero, $mensaje, $chat_id, $estado, $remitente]);
    $lastInsertedId = $pdo->lastInsertId();

    // Respuesta de éxito consistente
    echo json_encode(["success" => true, "message_id" => $lastInsertedId, "error" => null]); // O puedes añadir un "message" si quieres

} catch (PDOException $e) {
    // Loguear el error detallado en el servidor (¡buena práctica!)
    error_log("Error DB en send_message.php: " . $e->getMessage());

    // Enviar una respuesta de error consistente al cliente
    echo json_encode(["success" => false, "error" => "Error interno del servidor al guardar el mensaje."]);
    // Opcionalmente, podrías enviar $e->getMessage() si es seguro y útil para el frontend,
    // pero generalmente es mejor dar mensajes genéricos por seguridad.
    // echo json_encode(["success" => false, "error" => "Error al enviar el mensaje: " . $e->getMessage()]);
}