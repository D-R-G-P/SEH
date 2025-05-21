<?php
// servicios_api.php - Maneja las operaciones CRUD para la tabla `servicios_turnos_bot`

header('Content-Type: application/json'); // Asegura que la respuesta sea JSON
require_once '../../../../../app/db/db.php'; // Incluye el archivo de conexión a la base de datos

$db = new DB();
$pdo = $db->connect(); // Usar $pdo como en tu configuración

// Si la conexión falla, devuelve un error
if (!$pdo) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos.']);
    exit();
}

$action = $_GET['action'] ?? $_POST['action'] ?? ''; // Obtiene la acción de la URL o POST
$state_filter = $_GET['state'] ?? null; // Obtiene el parámetro de estado para filtrar (no usado para esta tabla)

switch ($action) {
    case 'getAll':
        getAllServicios($pdo, $state_filter);
        break;
    case 'getById':
        getServicioById($pdo, $state_filter);
        break;
    case 'create':
        createServicio($pdo);
        break;
    case 'update':
        updateServicio($pdo);
        break;
    case 'delete':
        deleteServicio($pdo);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Acción no válida.']);
        break;
}

/**
 * Obtiene todos los servicios.
 * @param PDO $pdo Conexión a la base de datos.
 * @param string|null $state_filter Filtra por estado si no es nulo. (No aplicable a esta tabla)
 */
function getAllServicios(PDO $pdo, ?string $state_filter) {
    // Asumiendo que la tabla 'servicios_turnos_bot' no tiene una columna 'estado'.
    $query = "SELECT id, nombre, descripcion, created_at, updated_at FROM servicios_turnos_bot ORDER BY nombre ASC";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $servicios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'data' => $servicios]);
}

/**
 * Obtiene un servicio por su ID.
 * @param PDO $pdo Conexión a la base de datos.
 * @param string|null $state_filter Filtra por estado si no es nulo. (No aplicable a esta tabla)
 */
function getServicioById(PDO $pdo, ?string $state_filter) {
    $id = $_GET['id'] ?? null;
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'ID de servicio requerido.']);
        return;
    }

    $query = "SELECT id, nombre, descripcion, created_at, updated_at FROM servicios_turnos_bot WHERE id = :id LIMIT 1";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $servicio = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($servicio) {
        echo json_encode(['success' => true, 'data' => $servicio]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Servicio no encontrado.']);
    }
}

/**
 * Crea un nuevo servicio.
 * @param PDO $pdo Conexión a la base de datos.
 */
function createServicio(PDO $pdo) {
    $data = json_decode(file_get_contents('php://input'), true); // Para datos JSON en body
    if (!$data) {
        $data = $_POST; // Fallback para datos de formulario
    }

    $nombre = $data['nombre'] ?? null;
    $descripcion = $data['descripcion'] ?? null;

    if (!$nombre) {
        echo json_encode(['success' => false, 'message' => 'El nombre del servicio es obligatorio.']);
        return;
    }

    $query = "INSERT INTO servicios_turnos_bot (nombre, descripcion) VALUES (:nombre, :descripcion)";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':descripcion', $descripcion);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Servicio creado exitosamente.', 'newId' => $pdo->lastInsertId()]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al crear el servicio.']);
    }
}

/**
 * Actualiza un servicio existente.
 * @param PDO $pdo Conexión a la base de datos.
 */
function updateServicio(PDO $pdo) {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        $data = $_POST;
    }

    $id = $data['id'] ?? null;
    $nombre = $data['nombre'] ?? null;
    $descripcion = $data['descripcion'] ?? null;

    if (!$id || !$nombre) {
        echo json_encode(['success' => false, 'message' => 'ID y nombre del servicio son obligatorios para actualizar.']);
        return;
    }

    $query = "UPDATE servicios_turnos_bot SET nombre = :nombre, descripcion = :descripcion WHERE id = :id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':descripcion', $descripcion);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Servicio actualizado exitosamente.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar el servicio.']);
    }
}

/**
 * Elimina un servicio.
 * @param PDO $pdo Conexión a la base de datos.
 */
function deleteServicio(PDO $pdo) {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        $data = $_POST;
    }
    $id = $data['id'] ?? null;

    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'ID de servicio requerido para eliminar.']);
        return;
    }

    $query = "DELETE FROM servicios_turnos_bot WHERE id = :id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Servicio eliminado exitosamente.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al eliminar el servicio.']);
    }
}
?>
