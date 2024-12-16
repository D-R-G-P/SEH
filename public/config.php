<?php
// Ruta base del proyecto
define('BASE_PATH', '/SGH');

// Rutas de módulos
define('MODULE_PATH', BASE_PATH . '/public/layouts/modules/');
define('CSS_PATH', BASE_PATH . '/public/resources/css/');
define('JS_PATH', BASE_PATH . '/public/resources/js/');
define('IMG_PATH', BASE_PATH . '/public/resources/image/');

// Función para generar URL base
function getBaseURL() {
    return str_replace('\\', '/', dirname(dirname($_SERVER['SCRIPT_NAME'])));
}

function hasAccess(array $requiredRoles): bool {
    // Iniciar conexión usando la clase DB
    $db = new DB();
    $pdo = $db->connect();

    // Obtener el DNI del usuario logueado
    global $user;
    $dni = $user->getDni();

    try {
        // Consulta para obtener los roles del usuario
        $query = "
            SELECT r.role
            FROM usuarios_roles ur
            JOIN roles r ON ur.rol_id = r.id
            WHERE ur.dni = :dni
        ";

        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':dni', $dni, PDO::PARAM_STR);
        $stmt->execute();

        // Obtener todos los roles del usuario
        $userRoles = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Verificar si el usuario tiene al menos uno de los roles requeridos
        foreach ($requiredRoles as $role) {
            if (in_array($role, $userRoles)) {
                return true;
            }
        }
        return false;
    } catch (PDOException $e) {
        // Manejo de errores
        error_log('Error en hasAccess: ' . $e->getMessage());
        return false;
    }
}

function requireRole(array $requiredRoles) {
    if (!hasAccess($requiredRoles)) {
        header("Location: " . BASE_PATH . "/index.php");
        exit;
    }
}

?>
