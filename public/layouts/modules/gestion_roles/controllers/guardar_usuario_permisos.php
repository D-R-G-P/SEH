<?php
require_once '../../../../../app/db/db.php';

$db = new DB();
$pdo = $db->connect();

$dni = $_POST['dni'] ?? null;
$rol_id = $_POST['rol_id'] ?? null;
$accion = $_POST['accion'] ?? null;
$subroles = json_decode($_POST['subroles'] ?? '[]', true); // Asegurar que es un array

if (!$dni || !$rol_id || !$accion) {
    switch (true) {
        case !$dni:
            $message = "DNI no proporcionado.";
            break;
        case !$rol_id:
            $message = "Rol no proporcionado.";
            break;
        case !$accion:
            $message = "Acción no proporcionada.";
            break;
    }
    echo json_encode(["success" => false, "message" => $message]);
    exit;
}

try {
    if ($accion === 'agregar') {
        // Agregar rol si no existe
        $stmt = $pdo->prepare("INSERT IGNORE INTO usuarios_roles (dni, rol_id) VALUES (?, ?)");
        $stmt->execute([$dni, $rol_id]);

        // Agregar subroles (evitar duplicados)
        if (!empty($subroles)) {
            $stmt = $pdo->prepare("INSERT IGNORE INTO usuarios_subroles (dni, rol_id, subrol_id) VALUES (?, ?, ?)");
            foreach ($subroles as $subrol_id) {
                $stmt->execute([$dni, $rol_id, $subrol_id]);
            }
        }

        echo json_encode(["success" => true, "message" => "Rol agregado correctamente."]);

    } elseif ($accion === 'quitar') {
        // Eliminar subroles y luego el rol
        $stmt = $pdo->prepare("DELETE FROM usuarios_subroles WHERE dni = ? AND rol_id = ?");
        $stmt->execute([$dni, $rol_id]);

        $stmt = $pdo->prepare("DELETE FROM usuarios_roles WHERE dni = ? AND rol_id = ?");
        $stmt->execute([$dni, $rol_id]);

        echo json_encode(["success" => true, "message" => "Rol eliminado correctamente."]);

    } elseif ($accion === 'actualizar') {

        if (empty($subroles)) {
            // Eliminar subroles anteriores y agregar nuevos
            $stmt = $pdo->prepare("DELETE FROM usuarios_subroles WHERE dni = ? AND rol_id = ?");
            $stmt->execute([$dni, $rol_id]);

            echo json_encode(["success" => true, "message" => "Subrol/es eliminado correctamente."]);
        }

        if (!empty($subroles)) {
            $stmt = $pdo->prepare("INSERT IGNORE INTO usuarios_subroles (dni, rol_id, subrol_id) VALUES (?, ?, ?)");
            foreach ($subroles as $subrol_id) {
                $stmt->execute([$dni, $rol_id, $subrol_id]);
            }

            echo json_encode(["success" => true, "message" => "Subrol/es agregados correctamente."]);
        }

    } else {
        echo json_encode(["success" => false, "message" => "Acción inválida."]);
    }
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Error SQL: " . $e->getMessage()]);
}
?>