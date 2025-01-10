<?php
// Configuración de encabezados
header('Content-Type: application/json');

// Verificar si se recibió una solicitud POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Incluir la conexión a la base de datos
    require_once '../../../../../app/db/db.php'; // Cambia el archivo según tu implementación

    // Obtener los datos del cuerpo de la solicitud
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    // Verificar que los datos requeridos estén presentes
    if (!isset($data['id']) || !isset($data['dni'])) {
        echo json_encode(['success' => false, 'message' => 'Faltan datos requeridos.']);
        exit;
    }

    // Asignar datos a variables
    $id = $data['id'];
    $dni = $data['dni'];
    $fechaActual = date('d/m/Y H:i:s'); // Fecha y hora actual

    try {
        // Conectar a la base de datos
        $db = new DB();
        $pdo = $db->connect();

        // Consultar si las columnas "first" están vacías
        $query = "
            SELECT fecha_apertura_first, usuario_apertura_first 
            FROM mantenimiento 
            WHERE id = :id
        ";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            if (empty($result['fecha_apertura_first']) && empty($result['usuario_apertura_first'])) {
                // Registrar en las columnas "first"
                $updateQuery = "
                    UPDATE mantenimiento
                    SET fecha_apertura_first = :fecha,
                        usuario_apertura_first = :dni
                    WHERE id = :id
                ";
                $stmt = $pdo->prepare($updateQuery);
                $stmt->bindParam(':fecha', $fechaActual);
                $stmt->bindParam(':dni', $dni);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
            } else {
                // Registrar en las columnas "latest"
                $updateQuery = "
                    UPDATE mantenimiento
                    SET fecha_apertura_latest = :fecha,
                        usuario_apertura_latest = :dni
                    WHERE id = :id
                ";
                $stmt = $pdo->prepare($updateQuery);
                $stmt->bindParam(':fecha', $fechaActual);
                $stmt->bindParam(':dni', $dni);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
            }

            echo json_encode(['success' => true, 'message' => 'Registro exitoso.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'El registro con el ID proporcionado no existe.']);
        }
    } catch (PDOException $e) {
        error_log('Error en la base de datos: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error interno del servidor.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
}
?>
