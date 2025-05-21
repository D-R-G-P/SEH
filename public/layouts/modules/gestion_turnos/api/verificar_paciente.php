<?php
require_once '../../../../../app/db/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $telefono = $_POST['telefono'] ?? '';

    if (empty($telefono)) {
        echo json_encode(['error' => 'Número de teléfono requerido']);
        exit;
    }

    try {
        $db = new DB();
        $pdo = $db->connect();

        $stmt = $pdo->prepare("SELECT * FROM pacientes_chat WHERE telefono = ?");
        $stmt->execute([$telefono]);
        $paciente = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($paciente) {
            echo json_encode(['status' => 'exists', 'paciente' => $paciente]);
        } else {
            echo json_encode(['status' => 'not_found']);
        }
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]);
    }
}
?>
