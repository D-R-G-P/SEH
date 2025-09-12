<?php
// Asegúrate de que los archivos de conexión estén en la ruta correcta
require_once '../../../../../../app/db/db.php';
require_once '../../../../../../app/db/user_session.php';
require_once '../../../../../../app/db/user.php';
require_once '../../../../../config.php';

// Configura la respuesta como JSON
header('Content-Type: application/json');

$user = new User();
$userSession = new UserSession();
$currentUser = $userSession->getCurrentUser();
$user->setUser($currentUser);

$db = new DB();
$pdo = $db->connect();

if (!$pdo) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión con la base de datos. Por favor, revisa tus credenciales.']);
    exit();
}

try {
    // 1. Obtener el ID de la capacitación desde la URL
    $capacitacion_id = $_GET['id'] ?? null;
    if (!$capacitacion_id || !is_numeric($capacitacion_id)) {
        http_response_code(400);
        echo json_encode(['error' => 'ID de capacitación inválido o no proporcionado.']);
        exit();
    }

    // 2. Consulta para obtener la capacitación principal
    $stmt = $pdo->prepare("SELECT id, titulo, descripcion, rol_asociado FROM capacitaciones_hsi WHERE id = ?");
    $stmt->execute([$capacitacion_id]);
    $capacitacion = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$capacitacion) {
        http_response_code(404);
        echo json_encode(['error' => 'Capacitación no encontrada.']);
        exit();
    }

    // 3. Obtener los nombres de los roles desde la tabla 'roles_hsi'
    $role_by_id = $capacitacion['rol_asociado'];
    // Guardar el valor original de rol_asociado (IDs) en la respuesta
    $capacitacion['rol_asociado_ids'] = $role_by_id;
    $role_ids = explode(',', $capacitacion['rol_asociado']);
    $role_ids = array_filter(array_map('trim', $role_ids));
    if (!empty($role_ids)) {
        $placeholders = implode(',', array_fill(0, count($role_ids), '?'));
        $stmt_roles = $pdo->prepare("SELECT rol FROM roles_hsi WHERE id IN ($placeholders)");
        $stmt_roles->execute($role_ids);
        $roles_nombres = $stmt_roles->fetchAll(PDO::FETCH_COLUMN, 0);
        $capacitacion['rol_asociado'] = implode(', ', $roles_nombres);
    } else {
        $capacitacion['rol_asociado'] = 'Sin rol asociado';
    }

    // 4. Obtener las instancias de capacitación y el conteo de asistentes
    $stmt_instancias = $pdo->prepare("SELECT id, fecha, hora, lugar, estado FROM instancias_capacitacion WHERE capacitacion_id = ?");
    $stmt_instancias->execute([$capacitacion_id]);
    $instancias = $stmt_instancias->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($instancias as &$instancia) {
        $instancia_id = $instancia['id'];
        $stmt_asistentes = $pdo->prepare("SELECT COUNT(*) as asistentes FROM inscripciones WHERE instancia_id = ? AND estado = 'inscripto'");
        $stmt_asistentes->execute([$instancia_id]);
        $asistentes = $stmt_asistentes->fetch(PDO::FETCH_ASSOC);
        $instancia['asistentes'] = $asistentes['asistentes'];
    }
    
    $capacitacion['instancias'] = $instancias;
    
    // 5. Devuelve los datos en formato JSON
    echo json_encode($capacitacion);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Ocurrió un error al obtener la capacitación: ' . $e->getMessage()]);
}

?>
