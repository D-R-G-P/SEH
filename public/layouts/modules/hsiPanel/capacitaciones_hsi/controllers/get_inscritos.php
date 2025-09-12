<?php
// SUGERENCIA DE DEPURACIÓN: Descomenta estas líneas TEMPORALMENTE
// para ver el error completo en la pantalla.
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

// =============================================================
// VERIFICA QUE ESTAS RUTAS SEAN LAS CORRECTAS PARA TU PROYECTO
// El error 500 probablemente se debe a que una de estas rutas es incorrecta.
// =============================================================
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
    // 1. Obtener el ID de la instancia de capacitación desde la URL
    $instancia_id = $_GET['instancia_id'] ?? null;
    if (!$instancia_id || !is_numeric($instancia_id)) {
        http_response_code(400);
        echo json_encode(['error' => 'ID de instancia inválido o no proporcionado.']);
        exit();
    }

    // 2. Consulta para obtener los usuarios inscritos de las tablas 'personal' y 'temporal_users'
    $stmt = $pdo->prepare("
        (
            SELECT 
                u.nombre, 
                u.apellido, 
                u.dni, 
                s.servicio, 
                i.estado,
                'Planta' AS tipo_agente
            FROM inscripciones i
            JOIN personal u ON i.agente_dni = u.dni
            JOIN servicios s ON u.servicio_id = s.id
            WHERE i.instancia_id = ? AND i.estado = 'inscripto'
        )
        UNION
        (
            SELECT
                t.nombres AS nombre,
                t.apellidos AS apellido,
                t.dni,
                s.servicio,
                i.estado,
                'Temporario' AS tipo_agente
            FROM inscripciones i
            JOIN temporal_users t ON i.temporal_user_id = t.id
            JOIN servicios s ON t.service_id = s.id
            WHERE i.instancia_id = ? AND i.estado = 'inscripto'
        )
    ");
    
    $stmt->execute([$instancia_id, $instancia_id]);
    $inscriptos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. Devuelve los datos en formato JSON
    echo json_encode($inscriptos);

} catch (Exception $e) {
    // Registra el error en el registro de errores del servidor para depurar
    error_log('Error en get_inscritos.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Ocurrió un error al obtener los inscriptos: ' . $e->getMessage()]);
}

?>
