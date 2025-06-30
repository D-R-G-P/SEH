<?php
// Ruta base del proyecto
define('BASE_PATH', '/SGH');

// Rutas de módulos
define('MODULE_PATH', BASE_PATH . '/public/layouts/modules/');
define('CSS_PATH', BASE_PATH . '/public/resources/css/');
define('JS_PATH', BASE_PATH . '/public/resources/js/');
define('IMG_PATH', BASE_PATH . '/public/resources/image/');

// Función para generar URL base
function getBaseURL()
{
    return str_replace('\\', '/', dirname(dirname($_SERVER['SCRIPT_NAME'])));
}

// Timezone
date_default_timezone_set('America/Argentina/Buenos_Aires');

// Verificar acceso a roles
function hasAccess(array $requiredRoles): bool
{
    $db = new DB();
    $pdo = $db->connect();

    global $user;
    $dni = $user->getDni();

    if (!$dni) {
        error_log("El DNI del usuario no está disponible.");
        return false;
    }

    try {
        $query = "
            SELECT r.role
            FROM usuarios_roles ur
            JOIN roles r ON ur.rol_id = r.id
            WHERE ur.dni = :dni
        ";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':dni', $dni, PDO::PARAM_STR);
        $stmt->execute();

        $userRoles = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($requiredRoles as $role) {
            if (in_array($role, $userRoles)) {
                return true;
            }
        }

        return false;
    } catch (PDOException $e) {
        error_log('Error en hasAccess: ' . $e->getMessage());
        return false;
    }
}

// Verificar acceso a roles
function hasSubAccess(array $requiredSubRoles): bool
{
    $db = new DB();
    $pdo = $db->connect();

    global $user;
    $dni = $user->getDni();

    if (!$dni) {
        error_log("El DNI del usuario no está disponible.");
        return false;
    }

    try {
        $query = "
            SELECT s.subrol 
            FROM usuarios_subroles usr 
            JOIN subroles s ON usr.subrol_id = s.id 
            WHERE usr.dni = :dni
        ";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':dni', $dni, PDO::PARAM_STR);
        $stmt->execute();

        $userSubRoles = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($requiredSubRoles as $subRole) {
            if (in_array($subRole, $userSubRoles)) {
                return true;
            }
        }

        return false;
    } catch (PDOException $e) {
        error_log('Error en hasAccess: ' . $e->getMessage());
        return false;
    }
}

// Redirigir si no tiene acceso a roles
function requireRole(array $requiredRoles)
{
    if (!hasAccess($requiredRoles)) {
        header("Location: " . BASE_PATH . "/index.php");
        exit;
    }
}

// Redirigir si no tiene acceso a subroles
function requireSubRole(array $requiredSubRoles)
{
    if (!hasSubAccess($requiredSubRoles) && !hasAccess(['administrador', 'direccion'])) {
        // header("Location: " . BASE_PATH . "/index.php");
        // exit; 
    }
}

function getLastUpdate(): ?string
{
    $db = new DB();
    $pdo = $db->connect();

    try {
        $query = "
            SELECT version
            FROM updates
            ORDER BY id DESC
            LIMIT 1
        ";
        $stmt = $pdo->prepare($query);
        $stmt->execute();

        $lastUpdate = $stmt->fetch(PDO::FETCH_ASSOC);

        return $lastUpdate ? $lastUpdate['version'] : null;
    } catch (PDOException $e) {
        error_log('Error en getLastUpdate: ' . $e->getMessage());
        return null;
    }
}