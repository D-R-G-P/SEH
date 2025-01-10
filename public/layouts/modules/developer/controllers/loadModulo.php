<?php
require_once '../../../../../app/db/db.php';
header('Content-Type: application/json'); // Establecer el tipo de respuesta como JSON

try {
    // Verificar que se envió el ID mediante POST
    if (isset($_POST['id']) && !empty($_POST['id'])) {
        $id = intval($_POST['id']); // Sanitizar el ID

        // Conexión a la base de datos
        $db = new DB();
        $pdo = $db->connect();

        // Consultar el módulo con el ID proporcionado
        $query = "SELECT id, modulo, descripcion, estado FROM modulos WHERE id = :id LIMIT 1";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        // Verificar si se encontró un registro
        if ($stmt->rowCount() > 0) {
            $modulo = $stmt->fetch(PDO::FETCH_ASSOC);

            // Retornar los datos del módulo como JSON
            echo json_encode($modulo);
            exit();
        } else {
            // Si no se encontró un módulo con el ID proporcionado
            echo json_encode(['error' => 'Módulo no encontrado.']);
            http_response_code(404);
            exit();
        }
    } else {
        // Si no se envió un ID válido
        echo json_encode(['error' => 'ID no válido o no proporcionado.']);
        http_response_code(400);
        exit();
    }
} catch (Exception $e) {
    // Capturar errores y retornar un mensaje genérico
    echo json_encode(['error' => 'Ocurrió un error al obtener los datos del módulo.', 'details' => $e->getMessage()]);
    http_response_code(500);
    exit();
}
