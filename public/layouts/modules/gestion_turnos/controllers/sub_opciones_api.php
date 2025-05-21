<?php
// sub_opciones_api.php - Maneja las operaciones CRUD para la tabla `sub_opciones`

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
        getAllSubOpciones($pdo, $state_filter);
        break;
    case 'getById':
        getSubOpcionById($pdo, $state_filter);
        break;
    case 'getByPasoOrigenId':
        getSubOpcionesByPasoOrigenId($pdo, $state_filter);
        break;
    case 'create':
        createSubOpcion($pdo);
        break;
    case 'update':
        updateSubOpcion($pdo);
        break;
    case 'delete':
        deleteSubOpcion($pdo);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Acción no válida.']);
        break;
}

/**
 * Obtiene todas las sub-opciones.
 * Incluye el título del contenido de origen y destino si existen.
 * @param PDO $pdo Conexión a la base de datos.
 * @param string|null $state_filter Filtra por estado si no es nulo.
 */
function getAllSubOpciones(PDO $pdo, ?string $state_filter) {
    $query = "
        SELECT
            so.id,
            so.paso_origen_id,
            cpo.titulo AS paso_origen_titulo,
            so.texto_sub_opcion,
            so.paso_destino_id,
            cpd.titulo AS paso_destino_titulo,
            so.estado,
            so.created_at,
            so.updated_at
        FROM
            sub_opciones so
        JOIN
            contenido_pasos cpo ON so.paso_origen_id = cpo.id
        LEFT JOIN
            contenido_pasos cpd ON so.paso_destino_id = cpd.id";
    if ($state_filter) {
        $query .= " WHERE so.estado = :estado";
    }
    $query .= " ORDER BY so.paso_origen_id, so.texto_sub_opcion ASC";
    $stmt = $pdo->prepare($query);
    if ($state_filter) {
        $stmt->bindParam(':estado', $state_filter);
    }
    $stmt->execute();
    $sub_opciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'data' => $sub_opciones]);
}

/**
 * Obtiene una sub-opción por su ID.
 * @param PDO $pdo Conexión a la base de datos.
 * @param string|null $state_filter Filtra por estado si no es nulo.
 */
function getSubOpcionById(PDO $pdo, ?string $state_filter) {
    $id = $_GET['id'] ?? null;
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'ID de sub-opción requerido.']);
        return;
    }

    $query = "
        SELECT
            so.id,
            so.paso_origen_id,
            cpo.titulo AS paso_origen_titulo,
            so.texto_sub_opcion,
            so.paso_destino_id,
            cpd.titulo AS paso_destino_titulo,
            so.estado,
            so.created_at,
            so.updated_at
        FROM
            sub_opciones so
        JOIN
            contenido_pasos cpo ON so.paso_origen_id = cpo.id
        LEFT JOIN
            contenido_pasos cpd ON so.paso_destino_id = cpd.id
        WHERE
            so.id = :id";
    if ($state_filter) {
        $query .= " AND so.estado = :estado";
    }
    $query .= " LIMIT 1";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    if ($state_filter) {
        $stmt->bindParam(':estado', $state_filter);
    }
    $stmt->execute();
    $sub_opcion = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($sub_opcion) {
        echo json_encode(['success' => true, 'data' => $sub_opcion]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Sub-opción no encontrada.']);
    }
}

/**
 * Obtiene sub-opciones filtradas por ID de paso de origen.
 * @param PDO $pdo Conexión a la base de datos.
 * @param string|null $state_filter Filtra por estado si no es nulo.
 */
function getSubOpcionesByPasoOrigenId(PDO $pdo, ?string $state_filter) {
    $paso_origen_id = $_GET['paso_origen_id'] ?? null;
    if (!$paso_origen_id) {
        echo json_encode(['success' => false, 'message' => 'ID de paso de origen requerido para filtrar sub-opciones.']);
        return;
    }

    $query = "
        SELECT
            so.id,
            so.paso_origen_id,
            cpo.titulo AS paso_origen_titulo,
            so.texto_sub_opcion,
            so.paso_destino_id,
            cpd.titulo AS paso_destino_titulo,
            so.estado,
            so.created_at,
            so.updated_at
        FROM
            sub_opciones so
        JOIN
            contenido_pasos cpo ON so.paso_origen_id = cpo.id
        LEFT JOIN
            contenido_pasos cpd ON so.paso_destino_id = cpd.id
        WHERE
            so.paso_origen_id = :paso_origen_id";
    if ($state_filter) {
        $query .= " AND so.estado = :estado";
    }
    $query .= " ORDER BY so.texto_sub_opcion ASC";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':paso_origen_id', $paso_origen_id, PDO::PARAM_INT);
    if ($state_filter) {
        $stmt->bindParam(':estado', $state_filter);
    }
    $stmt->execute();
    $sub_opciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'data' => $sub_opciones]);
}

/**
 * Crea una nueva sub-opción.
 * @param PDO $pdo Conexión a la base de datos.
 */
function createSubOpcion(PDO $pdo) {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        $data = $_POST;
    }

    $paso_origen_id = $data['paso_origen_id'] ?? null;
    $texto_sub_opcion = $data['texto_sub_opcion'] ?? null;
    $paso_destino_id = $data['paso_destino_id'] ?? null;
    $estado = $data['estado'] ?? 'activo';

    if (!$paso_origen_id || !$texto_sub_opcion) {
        echo json_encode(['success' => false, 'message' => 'ID de paso de origen y texto de sub-opción son obligatorios.']);
        return;
    }

    $query = "INSERT INTO sub_opciones (paso_origen_id, texto_sub_opcion, paso_destino_id, estado) VALUES (:paso_origen_id, :texto_sub_opcion, :paso_destino_id, :estado)";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':paso_origen_id', $paso_origen_id, PDO::PARAM_INT);
    $stmt->bindParam(':texto_sub_opcion', $texto_sub_opcion);
    $stmt->bindParam(':paso_destino_id', $paso_destino_id, PDO::PARAM_INT); // Puede ser NULL
    $stmt->bindParam(':estado', $estado);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Sub-opción creada exitosamente.', 'newId' => $pdo->lastInsertId()]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al crear la sub-opción.']);
    }
}

/**
 * Actualiza una sub-opción existente.
 * @param PDO $pdo Conexión a la base de datos.
 */
function updateSubOpcion(PDO $pdo) {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        $data = $_POST;
    }

    $id = $data['id'] ?? null;
    $paso_origen_id = $data['paso_origen_id'] ?? null;
    $texto_sub_opcion = $data['texto_sub_opcion'] ?? null;
    $paso_destino_id = $data['paso_destino_id'] ?? null;
    $estado = $data['estado'] ?? null;

    if (!$id || !$paso_origen_id || !$texto_sub_opcion) {
        echo json_encode(['success' => false, 'message' => 'ID, ID de paso de origen y texto de sub-opción son obligatorios para actualizar.']);
        return;
    }

    $query = "UPDATE sub_opciones SET paso_origen_id = :paso_origen_id, texto_sub_opcion = :texto_sub_opcion, paso_destino_id = :paso_destino_id, estado = :estado WHERE id = :id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':paso_origen_id', $paso_origen_id, PDO::PARAM_INT);
    $stmt->bindParam(':texto_sub_opcion', $texto_sub_opcion);
    $stmt->bindParam(':paso_destino_id', $paso_destino_id, PDO::PARAM_INT);
    $stmt->bindParam(':estado', $estado);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Sub-opción actualizada exitosamente.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar la sub-opción.']);
    }
}

/**
 * Elimina una sub-opción.
 * @param PDO $pdo Conexión a la base de datos.
 */
function deleteSubOpcion(PDO $pdo) {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        $data = $_POST;
    }
    $id = $data['id'] ?? null;

    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'ID de sub-opción requerido para eliminar.']);
        return;
    }

    $query = "DELETE FROM sub_opciones WHERE id = :id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Sub-opción eliminada exitosamente.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al eliminar la sub-opción.']);
    }
}
?>
