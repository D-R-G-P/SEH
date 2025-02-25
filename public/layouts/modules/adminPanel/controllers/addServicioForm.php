<?php
session_start(); // Inicia la sesión (si aún no se ha iniciado)

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Verifica que el formulario se ha enviado por el método POST

    // Realiza las validaciones necesarias antes de procesar el formulario
    $errors = array();

    // Validación para el campo "servicio"
    if (empty($_POST["servicio"])) {
        $errors[] = "El nombre del servicio es obligatorio.";
    }

    // Validación para el campo "jefe"
    if (empty($_POST["jefe"])) {
        $errors[] = "Debes seleccionar un Jefe del servicio.";
    }

    // Si hay errores, almacena mensajes de error en la sesión y redirige al formulario
    if (!empty($errors)) {
        $_SESSION['toast_message'] = [
            'message' => implode("<br>", $errors),
            'type' => 'error' // Puede ser "success", "error", "warning" o "info"
        ];
        header("Location: ../adminPanel.php");
        exit;
    }

    // Si no hay errores, procesa el formulario
    $nombreServicio = $_POST["servicio"];
    $jefeServicio = $_POST["jefe"];

    // Realiza la conexión a la base de datos (utiliza tu propia lógica para la conexión)
    require_once '../../../../../app/db/db.php';

    $db = new DB();
    $pdo = $db->connect();

    try {
        // Validación adicional para verificar si el DNI ya está registrado como jefe en otro servicio
        $checkDNIQuery = "SELECT COUNT(*) FROM servicios WHERE jefe = ?";
        $checkDNIStmt = $pdo->prepare($checkDNIQuery);
        $checkDNIStmt->execute([$jefeServicio]);
        $existingDNICount = $checkDNIStmt->fetchColumn();

        if ($existingDNICount > 0) {
            $_SESSION['toast_message'] = [
                'message' => 'El DNI ya está registrado como jefe en otro servicio.',
                'type' => 'error' // Puede ser "success", "error", "warning" o "info"
            ];
            header("Location: ../adminPanel.php");
            exit;
        }

        // Prepara la consulta SQL para la inserción
        $stmt = $pdo->prepare("INSERT INTO servicios (servicio, jefe, estado) VALUES (?, ?, 'Activo')");

        // Ejecuta la consulta con los valores proporcionados
        $stmt->execute([$nombreServicio, $jefeServicio]);

        // Obtener el ID del servicio recién insertado
        $newServiceId = $pdo->lastInsertId();

        $stmtJefe = $pdo->prepare("UPDATE personal SET servicio_id = ?, cargo = ? WHERE dni = ?");
        $stmtJefe->execute([$newServiceId, "Jefe de servicio", $jefeServicio]);

        $dni = $jefeServicio;

        $rol = $pdo->prepare('DELETE FROM usuarios_roles WHERE dni = ?');
        $rol->execute([$dni]);

        $subrol = $pdo->prepare('DELETE FROM susuarios_ubroles WHERE dni = ?');
        $subrol->execute([$dni]);

        $rol = $pdo->prepare('INSERT INTO usuarios_roles (dni, rol_id) VALUES (?, 12)');
        $rol->execute([$dni]);

        $subrol = $pdo->prepare('INSERT INTO usuarios_subroles, rol_id, subrol_id) VALUES (?, 12, 12');
        $subrol->execute([$dni]);

        // Cierra la conexión a la base de datos
        $pdo = null;

        // Almacena un mensaje de éxito en la sesión y redirige a una página de éxito
        $_SESSION['toast_message'] = [
            'message' => 'El servicio se ha agregado correctamente',
            'type' => 'success' // Puede ser "success", "error", "warning" o "info"
        ];
        header("Location: ../adminPanel.php");
        exit;
    } catch (PDOException $e) {
        // Si hay un error en la base de datos, almacena el mensaje de error y redirige al formulario
        $_SESSION['toast_message'] = [
            'message' => 'Error al conectar a la base de datos' . $e->getMessage() . '.',
            'type' => 'error' // Puede ser "success", "error", "warning" o "info"
        ];
        header("Location: ../adminPanel.php");
        exit;
    }
} else {
    // Si alguien trata de acceder directamente a este script sin enviar el formulario, redirige al formulario
    header("Location: ../adminPanel.php");
    exit;
}
