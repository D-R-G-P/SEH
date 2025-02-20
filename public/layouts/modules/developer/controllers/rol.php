<?php
require_once '../../../../../app/db/db.php';
session_start();

$db = new DB();
$pdo = $db->connect();

// Verificar si se recibieron los datos necesarios
if (isset($_POST['rol']) && isset($_POST['rol_name'])) {
    $id = $_POST['id'] ?? null;
    $rol = $_POST['rol'];
    $rol_name = $_POST['rol_name'];
    $modulo_rol_select = $_POST['modulo_rol_select'] ?? null;
    $descripcion = $_POST['descripcion'] ?? null;
    $estado = $_POST['rol_estado'] ?? "Activo";

    try {
        if ($id) {
            // Actualizar rol existente
            $query = "UPDATE roles SET role = :role, nombre = :nombre, modulo = :modulo, descripcion = :descripcion, estado = :estado WHERE id = :id";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        } else {
            // Insertar un nuevo rol
            $query = "INSERT INTO roles (role, nombre, modulo, descripcion, estado) VALUES (:role, :nombre, :modulo, :descripcion, :estado)";
            $stmt = $pdo->prepare($query);
        }

        // Parámetros comunes
        $stmt->bindParam(':role', $rol, PDO::PARAM_STR);
        $stmt->bindParam(':nombre', $rol_name, PDO::PARAM_STR);
        $stmt->bindParam(':modulo', $modulo_rol_select, PDO::PARAM_INT);
        $stmt->bindParam(':descripcion', $descripcion, PDO::PARAM_STR);
        $stmt->bindParam(':estado', $estado, PDO::PARAM_STR);

        $stmt->execute();

        // Mensaje de éxito
        $_SESSION['toast_message'] = [
            'message' => 'Rol registrado/actualizado correctamente.',
            'type' => 'success'
        ];
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    } catch (Exception $e) {
        // Manejar errores de la base de datos
        $_SESSION['toast_message'] = [
            'message' => 'Error al registrar el Rol: ' . htmlspecialchars($e->getMessage()),
            'type' => 'error'
        ];
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    }
} else {
    // Si los datos no están completos
    http_response_code(400);
    $_SESSION['toast_message'] = [
        'message' => 'Error al registrar el Rol: Datos incompletos.',
        'type' => 'error'
    ];
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit();
}
