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

// Es VITAL que la conexión a la base de datos se establezca aquí, antes del bloque try.
$db = new DB();
$pdo = $db->connect();

// VERIFICACIÓN: Revisa si la conexión a la base de datos se estableció correctamente.
// Si $pdo es nulo, la conexión falló.
if (!$pdo) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión con la base de datos. Por favor, revisa tus credenciales.']);
    exit();
}

// Solo acepta peticiones POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido.']);
    exit();
}

try {
    // 1. Validar los datos del formulario principal
    $titulo = $_POST['title'] ?? '';
    $descripcion = $_POST['description'] ?? '';
    
    // Obtener roles asociados. El campo ahora se espera como un array.
    $roles_asociados = $_POST['rol_asociated'] ?? [];
    
    // Unir los roles en una cadena separada por comas para guardar en la base de datos
    $roles_asociados_string = is_array($roles_asociados) ? implode(',', $roles_asociados) : $roles_asociados;
    
    // Obtener datos para las nuevas columnas
    $created_by = $user->getDni(); 
    $date_created = date('Y-m-d H:i:s');
    
    // Validar que los campos principales no estén vacíos
    if (empty($titulo) || empty($descripcion) || empty($roles_asociados_string)) {
        http_response_code(400);
        echo json_encode(['error' => 'Por favor, completa todos los campos principales.']);
        exit();
    }
    
    // 2. Validar los datos de las instancias de capacitación
    $fechas = $_POST['fecha'] ?? [];
    $horas = $_POST['hora'] ?? [];
    $lugares = $_POST['lugar'] ?? [];
    
    // Validar que exista al menos una instancia y que todos sus campos estén completos
    if (empty($fechas) || count($fechas) != count($horas) || count($fechas) != count($lugares)) {
        http_response_code(400);
        echo json_encode(['error' => 'Datos de fechas y horarios incompletos o inválidos.']);
        exit();
    }
    
    // Inicia una transacción para asegurar que todas las inserciones se completen o ninguna
    $pdo->beginTransaction();
    
    // 3. Insertar la capacitación principal
    $stmt = $pdo->prepare("INSERT INTO capacitaciones_hsi (titulo, descripcion, rol_asociado, created_by, date_created) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$titulo, $descripcion, $roles_asociados_string, $created_by, $date_created]);
    
    // Obtener el ID de la capacitación recién insertada
    $capacitacion_id = $pdo->lastInsertId();
    
    // 4. Insertar cada una de las instancias de capacitación
    $stmt_instancia = $pdo->prepare("INSERT INTO instancias_capacitacion (capacitacion_id, fecha, hora, lugar) VALUES (?, ?, ?, ?)");
    
    for ($i = 0; $i < count($fechas); $i++) {
        // Validar cada instancia individualmente
        if (empty($fechas[$i]) || empty($horas[$i]) || empty($lugares[$i])) {
            throw new Exception('Hay una fila de fechas y horarios incompleta.');
        }
        
        $stmt_instancia->execute([$capacitacion_id, $fechas[$i], $horas[$i], $lugares[$i]]);
    }
    
    // 5. Si todo ha ido bien, confirma la transacción
    $pdo->commit();
    
    // Envía una respuesta de éxito
    echo json_encode(['success' => 'Capacitación registrada con éxito.', 'id' => $capacitacion_id]);

} catch (Exception $e) {
    // Si algo sale mal, revierte la transacción
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    // Envía una respuesta de error
    http_response_code(500);
    echo json_encode(['error' => 'Ocurrió un error al registrar la capacitación: ' . $e->getMessage()]);
}

header("Location: " . $_SERVER['HTTP_REFERER']);

?>
