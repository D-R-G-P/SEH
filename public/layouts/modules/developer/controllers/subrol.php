<?php
require_once '../../../../../app/db/db.php';
session_start();

$db = new DB();
$pdo = $db->connect();

// Verificar si se recibieron los datos necesarios
if (isset($_POST['subrol']) && isset($_POST['subrol_name'])) {
    $id = $_POST['id'] ?? null;
    $subrol = $_POST['subrol'];
    $subrol_name = $_POST['subrol_name'];
    $modulo_subrol_select = $_POST['modulo_subrol_select'] ?? null;
    $rol_subrol_select = $_POST['rol_subrol_select'] ?? null;
    $descripcion = $_POST['subDesc'] ?? null;
    $estado = $_POST['subrol_estado'] ?? "Activo";

    try {
        if ($id) {
            // Actualizar rol existente
            $query = "UPDATE subroles SET rol_id = :rol_id, subrol = :subrol, nombre = :nombre, modulo = :modulo, descripcion = :descripcion, estado = :estado WHERE id = :id";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        } else {
            // Insertar un nuevo rol
            $query = "INSERT INTO subroles (rol_id, subrol, nombre, modulo, descripcion, estado) VALUES (:rol_id, :subrol, :nombre, :modulo, :descripcion, :estado)";
            $stmt = $pdo->prepare($query);
        }

        // Parámetros comunes
        $stmt->bindParam(':rol_id', $rol_subrol_select, PDO::PARAM_STR);
        $stmt->bindParam(':subrol', $subrol, PDO::PARAM_STR);
        $stmt->bindParam(':nombre', $subrol_name, PDO::PARAM_INT);
        $stmt->bindParam(':modulo', $modulo_subrol_select, PDO::PARAM_STR);
        $stmt->bindParam(':descripcion', $descripcion, PDO::PARAM_STR);
        $stmt->bindParam(':estado', $estado, PDO::PARAM_STR);

        $stmt->execute();

        // Mensaje de éxito
        $_SESSION['toast_message'] = [
            'message' => 'Subrol registrado/actualizado correctamente.',
            'type' => 'success'
        ];
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    } catch (Exception $e) {
        // Manejar errores de la base de datos
        $_SESSION['toast_message'] = [
            'message' => 'Error al registrar el Subrol: ' . htmlspecialchars($e->getMessage()),
            'type' => 'error'
        ];
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    }
} else {
    // Si los datos no están completos
    http_response_code(400);
    $_SESSION['toast_message'] = [
        'message' => 'Error al registrar el Subrol: Datos incompletos.',
        'type' => 'error'
    ];
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit();
}
