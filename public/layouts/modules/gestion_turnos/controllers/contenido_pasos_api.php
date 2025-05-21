<?php
// contenido_pasos_api.php - Maneja las operaciones CRUD para la tabla `contenido_pasos`

header('Content-Type: application/json');
require_once '../../../../../app/db/db.php'; // Ruta de conexión actualizada

$db = new DB();
$pdo = $db->connect(); // Usar $pdo

if (!$pdo) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos.']);
    exit();
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$state_filter = $_GET['state'] ?? null; // Obtiene el parámetro de estado para filtrar

switch ($action) {
    case 'getAll':
        getAllContenidoPasos($pdo, $state_filter);
        break;
    case 'getById':
        getContenidoPasoById($pdo, $state_filter);
        break;
    case 'create':
        createContenidoPaso($pdo);
        break;
    case 'update':
        updateContenidoPaso($pdo);
        break;
    case 'delete':
        deleteContenidoPaso($pdo);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Acción no válida.']);
        break;
}

/**
 * Obtiene todos los contenidos de pasos.
 * @param PDO $pdo Conexión a la base de datos.
 * @param string|null $state_filter Filtra por estado si no es nulo.
 */
function getAllContenidoPasos(PDO $pdo, ?string $state_filter) {
    $query = "SELECT id, titulo, texto_completo, estado, created_at, updated_at FROM contenido_pasos";
    if ($state_filter) {
        $query .= " WHERE estado = :estado";
    }
    $query .= " ORDER BY titulo ASC";
    $stmt = $pdo->prepare($query);
    if ($state_filter) {
        $stmt->bindParam(':estado', $state_filter);
    }
    $stmt->execute();
    $contenidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'data' => $contenidos]);
}

/**
 * Obtiene un contenido de paso por su ID.
 * @param PDO $pdo Conexión a la base de datos.
 * @param string|null $state_filter Filtra por estado si no es nulo.
 */
function getContenidoPasoById(PDO $pdo, ?string $state_filter) {
    $id = $_GET['id'] ?? null;
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'ID de contenido de paso requerido.']);
        return;
    }

    $query = "SELECT id, titulo, texto_completo, estado, created_at, updated_at FROM contenido_pasos WHERE id = :id";
    if ($state_filter) {
        $query .= " AND estado = :estado";
    }
    $query .= " LIMIT 1";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    if ($state_filter) {
        $stmt->bindParam(':estado', $state_filter);
    }
    $stmt->execute();
    $contenido = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($contenido) {
        echo json_encode(['success' => true, 'data' => $contenido]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Contenido de paso no encontrado.']);
    }
}

/**
 * Crea un nuevo contenido de paso.
 * @param PDO $pdo Conexión a la base de datos.
 */
function createContenidoPaso(PDO $pdo) {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        $data = $_POST;
    }

    $titulo = $data['titulo'] ?? null;
    $texto_completo = $data['texto_completo'] ?? null;
    $estado = $data['estado'] ?? 'activo';

    if (!$titulo || !$texto_completo) {
        echo json_encode(['success' => false, 'message' => 'Título y texto completo son obligatorios.']);
        return;
    }

    $query = "INSERT INTO contenido_pasos (titulo, texto_completo, estado) VALUES (:titulo, :texto_completo, :estado)";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':titulo', $titulo);
    $stmt->bindParam(':texto_completo', $texto_completo);
    $stmt->bindParam(':estado', $estado);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Contenido de paso creado exitosamente.', 'newId' => $pdo->lastInsertId()]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al crear el contenido de paso.']);
    }
}

/**
 * Actualiza un contenido de paso existente.
 * @param PDO $pdo Conexión a la base de datos.
 */
function updateContenidoPaso(PDO $pdo) {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        $data = $_POST;
    }

    $id = $data['id'] ?? null;
    $titulo = $data['titulo'] ?? null;
    $texto_completo = $data['texto_completo'] ?? null;
    $estado = $data['estado'] ?? null;

    if (!$id || !$titulo || !$texto_completo) {
        echo json_encode(['success' => false, 'message' => 'ID, título y texto completo son obligatorios para actualizar.']);
        return;
    }

    $query = "UPDATE contenido_pasos SET titulo = :titulo, texto_completo = :texto_completo, estado = :estado WHERE id = :id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':titulo', $titulo);
    $stmt->bindParam(':texto_completo', $texto_completo);
    $stmt->bindParam(':estado', $estado);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Contenido de paso actualizado exitosamente.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar el contenido de paso.']);
    }
}

/**
 * Elimina un contenido de paso.
 * @param PDO $pdo Conexión a la base de datos.
 */
function deleteContenidoPaso(PDO $pdo) {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        $data = $_POST;
    }
    $id = $data['id'] ?? null;

    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'ID de contenido de paso requerido para eliminar.']);
        return;
    }

    $query = "DELETE FROM contenido_pasos WHERE id = :id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Contenido de paso eliminado exitosamente.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al eliminar el contenido de paso.']);
    }
}
?>
