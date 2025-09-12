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
    // 1. Validar el ID de la capacitación a editar
    $id = $_POST['edit_id'] ?? null;
    if ($id === null) {
        http_response_code(400);
        echo json_encode(['error' => 'ID de capacitación no proporcionado.']);
        exit();
    }
    
    // 2. Validar los datos del formulario principal
    $titulo = $_POST['title'] ?? '';
    $descripcion = $_POST['description'] ?? '';
    
    // Obtener roles asociados. El campo se espera como un array.
    $roles_asociados = $_POST['rol_asociated'] ?? [];
    
    // Unir los roles en una cadena separada por comas para guardar en la base de datos
    $roles_asociados_string = is_array($roles_asociados) ? implode(',', $roles_asociados) : $roles_asociados;
    
    // Obtener datos para las nuevas columnas de seguimiento
    $modified_by = $user->getDni(); 
    echo $modified_by;
    $date_modified = date('Y-m-d H:i:s');
    
    // Validar que los campos principales no estén vacíos
    if (empty($titulo) || empty($descripcion) || empty($roles_asociados_string)) {
        http_response_code(400);
        echo json_encode(['error' => 'Por favor, completa todos los campos principales.']);
        exit();
    }
    
    // 3. Validar los datos de las instancias de capacitación
    // Se usan los nombres de los campos correctos del formulario POST
    $fechas = $_POST['fecha_capacitacion'] ?? [];
    $horas = $_POST['hora_capacitacion'] ?? [];
    $lugares = $_POST['lugar_capacitacion'] ?? [];
    
    // Validar que exista al menos una instancia y que todos sus campos estén completos
    if (empty($fechas) || count($fechas) != count($horas) || count($fechas) != count($lugares)) {
        http_response_code(400);
        echo json_encode(['error' => 'Datos de fechas, horarios o lugares incompletos o inválidos.']);
        exit();
    }
    
    // Inicia una transacción para asegurar que todas las operaciones se completen o ninguna
    $pdo->beginTransaction();
    
    // 4. Actualizar la capacitación principal
    $stmt = $pdo->prepare("UPDATE capacitaciones_hsi SET titulo = ?, descripcion = ?, rol_asociado = ?, updated_by = ?, updated_by = ? WHERE id = ?");
    $stmt->execute([$titulo, $descripcion, $roles_asociados_string, $modified_by, $date_modified, $id]);
    
    // 5. Eliminar todas las instancias de capacitación antiguas
    // Esto es vital para asegurar que no queden instancias "huérfanas"
    $stmt_delete_instancias = $pdo->prepare("DELETE FROM instancias_capacitacion WHERE capacitacion_id = ?");
    $stmt_delete_instancias->execute([$id]);
    
    // 6. Insertar cada una de las nuevas instancias de capacitación
    $stmt_instancia = $pdo->prepare("INSERT INTO instancias_capacitacion (capacitacion_id, fecha, hora, lugar) VALUES (?, ?, ?, ?)");
    
    for ($i = 0; $i < count($fechas); $i++) {
        // Validar cada instancia individualmente
        if (empty($fechas[$i]) || empty($horas[$i]) || empty($lugares[$i])) {
            throw new Exception('Hay una fila de fechas y horarios incompleta.');
        }
        
        $stmt_instancia->execute([$id, $fechas[$i], $horas[$i], $lugares[$i]]);
    }
    
    // 7. Si todo ha ido bien, confirma la transacción
    $pdo->commit();
    
    // Envía una respuesta de éxito
    echo json_encode(['success' => 'Capacitación editada con éxito.', 'id' => $id]);

} catch (Exception $e) {
    // Si algo sale mal, revierte la transacción
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    // Envía una respuesta de error
    http_response_code(500);
    echo json_encode(['error' => 'Ocurrió un error al editar la capacitación: ' . $e->getMessage()]);
}

// Es una buena práctica no redirigir con un header en una API,
// deja que el cliente (JavaScript) maneje la redirección.
// header("Location: " . $_SERVER['HTTP_REFERER']);
// No se recomienda usar esta línea en un script de API, lo he comentado.
// El cliente de JS recibe la respuesta y decide qué hacer.

?>
