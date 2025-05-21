<?php
require_once '../../../../../app/db/db.php';
require_once 'decrypt.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $agente = $_POST['agente'];
    $tipo = $_POST['tipo'];
    $telefono = $_POST['telefono'];

    if (empty($telefono)) {
        echo json_encode(['success' => false, 'message' => 'Teléfono es requerido']);
        exit;
    }

    try {
        $db = new DB();
        $pdo = $db->connect();

        if ($tipo == "contacto") {
            $telefono = $telefono . "@c.us";

            $activeChat = "SELECT id FROM chats WHERE numero = ? AND estado = 'chatting' LIMIT 1";
            $stmt = $pdo->prepare($activeChat);
            $stmt->execute([$telefono]);
            $chat = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($chat) {
                echo json_encode(['success' => false, 'message' => 'Ya existe un chat activo con este contacto']);
                exit;
            }

            $sql = "INSERT INTO chats (numero, asignado, estado) VALUES (?, ?, 'agent_initiated')";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$telefono, $agente]);

            echo json_encode([
                'success' => true,
                'message' => 'Iniciando chat con contacto...',
                'chat' => [
                    'id' => $pdo->lastInsertId(),
                    'nombre_paciente' => null,
                    'numero' => $telefono
                ]
            ]);
            exit;

        } else if ($tipo == 'paciente') {
            $activeChat = "SELECT id FROM chats WHERE numero = ? AND estado = 'chatting' LIMIT 1";
            $stmt = $pdo->prepare($activeChat);
            $stmt->execute([$telefono]);
            $chat = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($chat) {
                echo json_encode(['success' => false, 'message' => 'Ya existe un chat activo con este contacto']);
                exit;
            }

            $checkPaciente = "SELECT id, nombres FROM pacientes_chat WHERE telefono = ? LIMIT 1";
            $stmt = $pdo->prepare($checkPaciente);
            $stmt->execute([$telefono]);
            $paciente = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$paciente) {
                echo json_encode(['success' => false, 'message' => 'Paciente no encontrado']);
                exit;
            }

            $sql = "INSERT INTO chats (paciente_id, numero, asignado, estado) VALUES (?, ?, ?, 'agent_initiated')";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$paciente['id'], $telefono, $agente]);
            $nombre_paciente = decryptData($paciente['nombres']);

            echo json_encode([
                'success' => true,
                'message' => 'Iniciando chat con paciente...',
                'chat' => [
                    'id' => $pdo->lastInsertId(),
                    'nombre_paciente' => $nombre_paciente,
                    'numero' => $telefono
                ]
            ]);
            exit;

        } else if ($tipo == 'numero') {
            $code = $_POST['country'] ?? null;
            if ($code == "un") {
                $code = $_POST['otherCountry'];
            }

            $telefono = $code . $telefono . "@c.us";

            $activeChat = "SELECT id FROM chats WHERE numero = ? AND estado = 'chatting' LIMIT 1";
            $stmt = $pdo->prepare($activeChat);
            $stmt->execute([$telefono]);
            $chat = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($chat) {
                echo json_encode(['success' => false, 'message' => 'Ya existe un chat activo con este contacto']);
                exit;
            }

            $checkPaciente = "SELECT id, nombres FROM pacientes_chat WHERE telefono = ? LIMIT 1";
            $stmt = $pdo->prepare($checkPaciente);
            $stmt->execute([$telefono]);
            $paciente = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$paciente) {
                $sql = "INSERT INTO chats (numero, asignado, estado) VALUES (?, ?, 'agent_initiated')";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$telefono, $agente]);

                echo json_encode([
                    'success' => true,
                    'message' => 'Iniciando chat con número...',
                    'chat' => [
                        'id' => $pdo->lastInsertId(),
                        'nombre_paciente' => null,
                        'numero' => $telefono
                    ]
                ]);
                exit;
            } else {
                $sql = "INSERT INTO chats (paciente_id, numero, asignado, estado) VALUES (?, ?, ?, 'agent_initiated')";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$paciente['id'], $telefono, $agente]);
                $nombre_paciente = decryptData($paciente['nombres']);

                echo json_encode([
                    'success' => true,
                    'message' => 'Paciente encontrado, iniciando chat...',
                    'chat' => [
                        'id' => $pdo->lastInsertId(),
                        'nombre_paciente' => $nombre_paciente,
                        'numero' => $telefono
                    ]
                ]);
                exit;
            }
        }

    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
    }
}
