<?php
// opciones_principales_api.php - Maneja las operaciones CRUD para la tabla `opciones_principales`

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
        getAllOpcionesPrincipales($pdo, $state_filter);
        break;
    case 'getById':
        getOpcionPrincipalById($pdo, $state_filter);
        break;
    case 'getByServiceId':
        getOpcionesPrincipalesByServiceId($pdo, $state_filter); // Ahora obtiene solo opciones de primer nivel para el servicio
        break;
    case 'getByParentOpcionId': // NUEVA ACCIÓN para sub-niveles de opciones principales
        getOpcionesPrincipalesByParentOpcionId($pdo, $state_filter);
        break;
    case 'create':
        createOpcionPrincipal($pdo);
        break;
    case 'update':
        updateOpcionPrincipal($pdo);
        break;
    case 'delete':
        deleteOpcionPrincipal($pdo);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Acción no válida.']);
        break;
}

/**
 * Obtiene todas las opciones principales.
 * Incluye el nombre del servicio y el texto de la opción padre si existen.
 * @param PDO $pdo Conexión a la base de datos.
 * @param string|null $state_filter Filtra por estado si no es nulo.
 */
function getAllOpcionesPrincipales(PDO $pdo, ?string $state_filter)
{
    $query = "
        SELECT
            op.id,
            op.servicio_id,
            s.nombre AS servicio_nombre,
            op.parent_opcion_id,
            opp.texto_opcion AS parent_opcion_texto,
            op.texto_opcion,
            op.paso_asociado_id,
            cp.titulo AS paso_asociado_titulo,
            op.texto_contenido,
            op.estado,
            op.created_at,
            op.updated_at
        FROM
            opciones_principales op
        JOIN
            servicios_turnos_bot s ON op.servicio_id = s.id
        LEFT JOIN
            opciones_principales opp ON op.parent_opcion_id = opp.id
        LEFT JOIN
            contenido_pasos cp ON op.paso_asociado_id = cp.id";
    if ($state_filter) {
        $query .= " WHERE op.estado = :estado";
    }
    $query .= " ORDER BY op.servicio_id, op.parent_opcion_id ASC, op.texto_opcion ASC";
    $stmt = $pdo->prepare($query);
    if ($state_filter) {
        $stmt->bindParam(':estado', $state_filter);
    }
    $stmt->execute();
    $opciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'data' => $opciones]);
}

/**
 * Obtiene una opción principal por su ID.
 * @param PDO $pdo Conexión a la base de datos.
 * @param string|null $state_filter Filtra por estado si no es nulo.
 */
function getOpcionPrincipalById(PDO $pdo, ?string $state_filter)
{
    $id = $_GET['id'] ?? null;
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'ID de opción principal requerido.']);
        return;
    }

    $query = "
        SELECT
            op.id,
            op.servicio_id,
            s.nombre AS servicio_nombre,
            op.parent_opcion_id,
            opp.texto_opcion AS parent_opcion_texto,
            op.texto_opcion,
            op.paso_asociado_id,
            cp.titulo AS paso_asociado_titulo,
            op.texto_contenido,
            op.estado,
            op.created_at,
            op.updated_at
        FROM
            opciones_principales op
        JOIN
            servicios_turnos_bot s ON op.servicio_id = s.id
        LEFT JOIN
            opciones_principales opp ON op.parent_opcion_id = opp.id
        LEFT JOIN
            contenido_pasos cp ON op.paso_asociado_id = cp.id
        WHERE
            op.id = :id";
    if ($state_filter) {
        $query .= " AND op.estado = :estado";
    }
    $query .= " LIMIT 1";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    if ($state_filter) {
        $stmt->bindParam(':estado', $state_filter);
    }
    $stmt->execute();
    $opcion = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($opcion) {
        echo json_encode(['success' => true, 'data' => $opcion]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Opción principal no encontrada.']);
    }
}

/**
 * Obtiene opciones principales filtradas por ID de servicio y que son de primer nivel (parent_opcion_id IS NULL).
 * @param PDO $pdo Conexión a la base de datos.
 * @param string|null $state_filter Filtra por estado si no es nulo.
 */
function getOpcionesPrincipalesByServiceId(PDO $pdo, ?string $state_filter)
{
    $service_id = $_GET['service_id'] ?? null;
    if (!$service_id) {
        echo json_encode(['success' => false, 'message' => 'ID de servicio requerido para filtrar opciones.']);
        return;
    }

    $query = "
        SELECT
            op.id,
            op.servicio_id,
            s.nombre AS servicio_nombre,
            op.parent_opcion_id,
            opp.texto_opcion AS parent_opcion_texto,
            op.texto_opcion,
            op.paso_asociado_id,
            cp.titulo AS paso_asociado_titulo,
            op.texto_contenido,
            op.estado,
            op.created_at,
            op.updated_at
        FROM
            opciones_principales op
        JOIN
            servicios_turnos_bot s ON op.servicio_id = s.id
        LEFT JOIN
            opciones_principales opp ON op.parent_opcion_id = opp.id
        LEFT JOIN
            contenido_pasos cp ON op.paso_asociado_id = cp.id
        WHERE
            op.servicio_id = :service_id AND op.parent_opcion_id IS NULL"; // <-- CRUCIAL: Solo opciones de primer nivel
    if ($state_filter) {
        $query .= " AND op.estado = :estado";
    }
    $query .= " ORDER BY op.texto_opcion ASC";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':service_id', $service_id, PDO::PARAM_INT);
    if ($state_filter) {
        $stmt->bindParam(':estado', $state_filter);
    }
    $stmt->execute();
    $opciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'data' => $opciones]);
}

/**
 * Obtiene opciones principales filtradas por ID de opción padre.
 * Esto es para los sub-niveles de menú.
 * @param PDO $pdo Conexión a la base de datos.
 * @param string|null $state_filter Filtra por estado si no es nulo.
 */
function getOpcionesPrincipalesByParentOpcionId(PDO $pdo, ?string $state_filter)
{
    $parent_opcion_id = $_GET['parent_opcion_id'] ?? null;
    if ($parent_opcion_id === null) {
        echo json_encode(['success' => false, 'message' => 'ID de opción padre requerido para filtrar opciones.']);
        return;
    }

    $query = "
        SELECT
            op.id,
            op.servicio_id,
            s.nombre AS servicio_nombre,
            op.parent_opcion_id,
            opp.texto_opcion AS parent_opcion_texto,
            op.texto_opcion,
            op.paso_asociado_id,
            cp.titulo AS paso_asociado_titulo,
            op.texto_contenido,
            op.estado,
            op.created_at,
            op.updated_at
        FROM
            opciones_principales op
        JOIN
            servicios_turnos_bot s ON op.servicio_id = s.id
        LEFT JOIN
            opciones_principales opp ON op.parent_opcion_id = opp.id
        LEFT JOIN
            contenido_pasos cp ON op.paso_asociado_id = cp.id
        WHERE
            op.parent_opcion_id = :parent_opcion_id";
    if ($state_filter) {
        $query .= " AND op.estado = :estado";
    }
    $query .= " ORDER BY op.texto_opcion ASC";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':parent_opcion_id', $parent_opcion_id, PDO::PARAM_INT);
    if ($state_filter) {
        $stmt->bindParam(':estado', $state_filter);
    }
    $stmt->execute();
    $opciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'data' => $opciones]);
}


/**
 * Crea una nueva opción principal.
 * @param PDO $pdo Conexión a la base de datos.
 */
function createOpcionPrincipal(PDO $pdo)
{
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        $data = $_POST;
    }

    $servicio_id = $data['servicio_id'] ?? null;
    // Sanitiza el valor null enviado por AJAX (puede venir como string 'null' o vacío)
    $parent_opcion_id = isset($data['parent_opcion_id']) && $data['parent_opcion_id'] !== '' && $data['parent_opcion_id'] !== 'null'
        ? $data['parent_opcion_id']
        : null;
    $texto_opcion = $data['texto_opcion'] ?? null;
    $paso_asociado_id = $data['paso_asociado_id'] ?? null;
    $texto_contenido = $data['texto_contenido'] ?? null;
    $estado = $data['estado'] ?? 'activo';

    if (!$servicio_id || !$texto_opcion) {
        echo json_encode(['success' => false, 'message' => 'ID de servicio y texto de opción son obligatorios.']);
        return;
    }

    $query = "INSERT INTO opciones_principales (servicio_id, parent_opcion_id, texto_opcion, paso_asociado_id, texto_contenido, estado) VALUES (:servicio_id, :parent_opcion_id, :texto_opcion, :paso_asociado_id, :texto_contenido, :estado)";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':servicio_id', $servicio_id, PDO::PARAM_INT);
    $stmt->bindParam(':parent_opcion_id', $parent_opcion_id, PDO::PARAM_INT); // Puede ser NULL
    $stmt->bindParam(':texto_opcion', $texto_opcion);
    $stmt->bindParam(':paso_asociado_id', $paso_asociado_id, PDO::PARAM_INT); // Puede ser NULL
    $stmt->bindParam(':texto_contenido', $texto_contenido); // Puede ser NULL
    $stmt->bindParam(':estado', $estado);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Opción principal creada exitosamente.', 'newId' => $pdo->lastInsertId()]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al crear la opción principal.']);
    }
}

/**
 * Actualiza una opción principal existente.
 * @param PDO $pdo Conexión a la base de datos.
 */
function updateOpcionPrincipal(PDO $pdo)
{
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        $data = $_POST;
    }

    $id = $data['id'] ?? null;
    $servicio_id = $data['servicio_id'] ?? null;
    $parent_opcion_id = isset($data['parent_opcion_id']) && $data['parent_opcion_id'] !== '' && $data['parent_opcion_id'] !== 'null'
        ? $data['parent_opcion_id']
        : null;
    $texto_opcion = $data['texto_opcion'] ?? null;
    $paso_asociado_id = $data['paso_asociado_id'] ?? null;
    $texto_contenido = $data['texto_contenido'] ?? null;
    $estado = $data['estado'] ?? null;

    if (!$id || !$servicio_id || !$texto_opcion) {
        echo json_encode(['success' => false, 'message' => 'ID, ID de servicio y texto de opción son obligatorios para actualizar.']);
        return;
    }

    $query = "UPDATE opciones_principales SET servicio_id = :servicio_id, parent_opcion_id = :parent_opcion_id, texto_opcion = :texto_opcion, paso_asociado_id = :paso_asociado_id, texto_contenido = :texto_contenido, estado = :estado WHERE id = :id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':servicio_id', $servicio_id, PDO::PARAM_INT);
    $stmt->bindParam(':parent_opcion_id', $parent_opcion_id, PDO::PARAM_INT);
    $stmt->bindParam(':texto_opcion', $texto_opcion);
    $stmt->bindParam(':paso_asociado_id', $paso_asociado_id, PDO::PARAM_INT);
    $stmt->bindParam(':texto_contenido', $texto_contenido);
    $stmt->bindParam(':estado', $estado);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Opción principal actualizada exitosamente.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar la opción principal.']);
    }
}

/**
 * Elimina una opción principal.
 * @param PDO $pdo Conexión a la base de datos.
 */
function deleteOpcionPrincipal(PDO $pdo)
{
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        $data = $_POST;
    }
    $id = $data['id'] ?? null;

    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'ID de opción principal requerido para eliminar.']);
        return;
    }

    $query = "DELETE FROM opciones_principales WHERE id = :id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Opción principal eliminada exitosamente.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al eliminar la opción principal.']);
    }
}
?>