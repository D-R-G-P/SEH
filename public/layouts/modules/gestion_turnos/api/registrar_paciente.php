<?php
require_once '../../../../../app/db/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data) {
        echo json_encode(['error' => 'Datos inválidos']);
        exit;
    }

    try {
        $db = new DB();
        $pdo = $db->connect();

        $stmt = $pdo->prepare("
            INSERT INTO pacientes_chat (
                apellidos, nombres, tipo_documento, dni, fecha_nacimiento, 
                identidad_genero, nombre_autopercibido, provincia, partido, ciudad, 
                calle, numero, piso, departamento, telefono, mail, obra_social
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
            )
        ");

        $stmt->execute([
            $data['apellidos'], $data['nombres'], $data['tipo_documento'], $data['dni'],
            $data['fecha_nacimiento'], $data['identidad_genero'], $data['nombre_autopercibido'],
            $data['provincia'], $data['partido'], $data['ciudad'], $data['calle'],
            $data['numero'], $data['piso'], $data['departamento'], $data['telefono'],
            $data['mail'], $data['obra_social']
        ]);

        echo json_encode(['status' => 'success']);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]);
    }
}
?>
