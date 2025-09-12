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

// VERIFICACIÓN: Revisa si la conexión a la base de datos se estableció correctamente.
if (!$pdo) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión con la base de datos. Por favor, revisa tus credenciales.']);
    exit();
}

try {
    // Consulta SQL para obtener todas las capacitaciones
    $stmt = $pdo->query("SELECT id, titulo, descripcion, rol_asociado FROM capacitaciones_hsi");
    $capacitaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Array para almacenar el resultado final
    $data = [];

    // Itera sobre cada capacitación para obtener sus instancias y resolver los roles
    foreach ($capacitaciones as $capacitacion) {
        $capacitacion_id = $capacitacion['id'];

        // -------------------------------------------------------------
        // NUEVA LÓGICA: Obtener los nombres de los roles desde la tabla 'roles_hsi'
        // -------------------------------------------------------------
        
        // Divide la cadena de IDs de roles en un array
        $role_ids = explode(',', $capacitacion['rol_asociado']);
        
        // Filtra cualquier valor vacío y limpia espacios en blanco
        $role_ids = array_filter(array_map('trim', $role_ids));

        if (!empty($role_ids)) {
            // Prepara una consulta con marcadores de posición dinámicos
            $placeholders = implode(',', array_fill(0, count($role_ids), '?'));
            $stmt_roles = $pdo->prepare("SELECT rol FROM roles_hsi WHERE id IN ($placeholders)");
            $stmt_roles->execute($role_ids);
            
            // Obtiene los nombres de los roles en un array
            $roles_nombres = $stmt_roles->fetchAll(PDO::FETCH_COLUMN, 0);

            // Reemplaza la cadena de IDs por los nombres de los roles en la capacitación
            $capacitacion['rol_asociado'] = implode(', ', $roles_nombres);
        } else {
            // En caso de que no haya roles asociados, asigna un valor por defecto
            $capacitacion['rol_asociado'] = 'Sin rol asociado';
        }
        
        // -------------------------------------------------------------
        // FIN DE LA NUEVA LÓGICA
        // -------------------------------------------------------------

        // Consulta para obtener las instancias de capacitación
        $stmt_instancias = $pdo->prepare("SELECT id, fecha, hora, lugar FROM instancias_capacitacion WHERE capacitacion_id = ?");
        $stmt_instancias->execute([$capacitacion_id]);
        $instancias = $stmt_instancias->fetchAll(PDO::FETCH_ASSOC);
        
        // Itera sobre cada instancia para obtener el conteo de asistentes
        foreach ($instancias as &$instancia) {
            $instancia_id = $instancia['id'];
            $stmt_asistentes = $pdo->prepare("SELECT COUNT(*) as asistentes FROM inscripciones WHERE instancia_id = ? AND estado = 'inscripto'");
            $stmt_asistentes->execute([$instancia_id]);
            $asistentes = $stmt_asistentes->fetch(PDO::FETCH_ASSOC);
            $instancia['asistentes'] = $asistentes['asistentes'];
        }
        
        // Agrega las instancias y sus asistentes a la capacitación
        $capacitacion['instancias'] = $instancias;
        $data[] = $capacitacion;
    }
    
    // Devuelve los datos en formato JSON
    echo json_encode($data);

} catch (Exception $e) {
    // En caso de error, devuelve un mensaje JSON con el error
    http_response_code(500);
    echo json_encode(['error' => 'Ocurrió un error al obtener las capacitaciones: ' . $e->getMessage()]);
}
?>
