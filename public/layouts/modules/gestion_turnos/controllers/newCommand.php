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
            $id = isset($input["id_command"]) ? intval($input["id_command"]) : null;
            $status = $input["status"] ?? null;

            if (!$id || !in_array($status, ['activo', 'inactivo'])) {
                responder_json(['success' => false, 'message' => 'Datos inválidos.'], 400);
            }

            $stmt = $pdo->prepare("UPDATE comandos SET estado = ? WHERE id = ?");
            $stmt->execute([$status, $id]);

            responder_json(['success' => true, 'message' => 'Estado actualizado.']);
        } else {
            // 🧾 FORMULARIO HTML tradicional
            session_start();

            $id = $_POST['id_command'] ?? null;
            $comando = trim($_POST['comando'] ?? '');
            $texto = trim($_POST['texto'] ?? '');
            $estado = $_POST['estado'] ?? 'activo';

            if ($id) {
                // EDITAR contacto
                $stmt = $pdo->prepare("UPDATE comandos SET `comando` = ?, `texto` = ?, `estado` = ? WHERE id = ?");
                $stmt->execute([$comando, $texto, $estado, $id]);

                $_SESSION['toast_message'] = ['message' => 'Comando actualizado correctamente', 'type' => 'success'];
            } else {
                // NUEVO contacto
                $stmt = $pdo->prepare("INSERT INTO comandos (`comando`, `texto`, `estado`) VALUES (?, ?, ?)");
                $stmt->execute([$comando, $texto, $estado]);

                $_SESSION['toast_message'] = ['message' => 'Comando agregado correctamente', 'type' => 'success'];
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
