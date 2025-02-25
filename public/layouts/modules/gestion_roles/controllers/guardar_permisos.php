<?php
header('Content-Type: application/json'); // 🔥 Asegurar JSON

require_once '../../../../../app/db/db.php';

$db = new DB();
$pdo = $db->connect();

$groupId = $_POST['groupId'] ?? null;
$roleId = $_POST['roleId'] ?? null;
$isChecked = isset($_POST['isChecked']) && $_POST['isChecked'] === "true";
$subroles = isset($_POST['subroles']) ? json_decode($_POST['subroles'], true) : [];

// 🔥 Evitar error en foreach si subroles no es un array
if (!is_array($subroles)) {
    $subroles = [];
}

if (!$groupId || !$roleId) {
    echo json_encode(["success" => false, "message" => "Error: Datos incompletos"]);
    exit;
}

try {
    if ($isChecked) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM grupos_permisos WHERE subrol_id = ? AND enabled_rol_id = ?");
        $stmt->execute([$groupId, $roleId]);
        $exists = $stmt->fetchColumn();

        if (!$exists) {
            $pdo->prepare("INSERT INTO grupos_permisos (subrol_id, enabled_rol_id, enabled_subrol_id) VALUES (?, ?, NULL)")
                ->execute([$groupId, $roleId]);
        }

        $pdo->prepare("DELETE FROM grupos_permisos WHERE subrol_id = ? AND enabled_rol_id = ? AND enabled_subrol_id IS NOT NULL")
            ->execute([$groupId, $roleId]);

        foreach ($subroles as $subroleId) {
            $pdo->prepare("INSERT INTO grupos_permisos (subrol_id, enabled_rol_id, enabled_subrol_id) VALUES (?, ?, ?)")
                ->execute([$groupId, $roleId, $subroleId]);
        }

        echo json_encode(["success" => true, "message" => "Permisos actualizados."]);
    } else {
        $pdo->prepare("DELETE FROM grupos_permisos WHERE subrol_id = ? AND enabled_rol_id = ?")
            ->execute([$groupId, $roleId]);

        echo json_encode(["success" => true, "message" => "Permisos eliminados."]);
    }
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Error en BD: " . $e->getMessage()]);
}
