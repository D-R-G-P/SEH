<?php
require_once '../../../../../app/db/db.php';

function responder_json($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

// Detectar si es llamada JSON (fetch) o formulario
$isJson = strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = new DB();
    $pdo = $db->connect();

    try {
        if ($isJson) {
            // 🔁 LLAMADA DESDE fetch()
            $input = json_decode(file_get_contents("php://input"), true);
            $id = isset($input["id_contact"]) ? intval($input["id_contact"]) : null;
            $status = $input["status"] ?? null;

            if (!$id || !in_array($status, ['activo', 'inactivo'])) {
                responder_json(['success' => false, 'message' => 'Datos inválidos.'], 400);
            }

            $stmt = $pdo->prepare("UPDATE contacts SET status = ? WHERE id = ?");
            $stmt->execute([$status, $id]);

            responder_json(['success' => true, 'message' => 'Estado actualizado.']);
        } else {
            // 🧾 FORMULARIO HTML tradicional
            session_start();

            $id = $_POST['id_contact'] ?? null;
            $nombre = trim($_POST['nombre'] ?? '');
            $telefono = trim($_POST['telefono'] ?? '');
            $status = $_POST['status'] ?? 'activo';

            if ($id) {
                // EDITAR contacto
                $stmt = $pdo->prepare("UPDATE contacts SET `name` = ?, `number` = ?, `status` = ?, `timestamp` = current_timestamp() WHERE id = ?");
                $stmt->execute([$nombre, $telefono, $status, $id]);

                $_SESSION['toast_message'] = ['message' => 'Contacto actualizado correctamente', 'type' => 'success'];
            } else {
                // NUEVO contacto
                $stmt = $pdo->prepare("INSERT INTO contacts (`name`, `number`, `status`, `timestamp`) VALUES (?, ?, ?, current_timestamp())");
                $stmt->execute([$nombre, $telefono, $status]);

                $_SESSION['toast_message'] = ['message' => 'Contacto agregado correctamente', 'type' => 'success'];
            }

            header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '/'));
            exit;
        }
    } catch (PDOException $e) {
        if ($isJson) {
            responder_json(['success' => false, 'message' => 'Error DB: ' . $e->getMessage()], 500);
        } else {
            session_start();
            $_SESSION['toast_message'] = ['message' => 'Error DB: ' . $e->getMessage(), 'type' => 'error'];
            header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '/'));
            exit;
        }
    }
} else {
    if ($isJson) {
        responder_json(['success' => false, 'message' => 'Método no permitido'], 405);
    } else {
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '/'));
        exit;
    }
}
