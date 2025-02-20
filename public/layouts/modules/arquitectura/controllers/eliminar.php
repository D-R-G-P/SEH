<?php
include_once '../../../../../app/db/db.php'; // Conexión a la DB
session_start();

$db = new DB();
$pdo = $db->connect();

// Validar que el ID es numérico y está presente en la URL
if (!isset($_GET['id']) || empty($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['toast_message'] = [
        'message' => 'ID inválido.',
        'type' => 'error'
    ];
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit();
}

$id = intval($_GET['id']);

try {
    // Verificar si hay unidades que tengan este sitio como `u_padre`
    $sql_check = "SELECT COUNT(*) FROM arquitectura WHERE u_padre = :id";
    $stmt_check = $pdo->prepare($sql_check);
    $stmt_check->execute([':id' => $id]);
    $count = $stmt_check->fetchColumn();

    if ($count > 0) {
        $_SESSION['toast_message'] = [
            'message' => 'No se puede eliminar este sitio porque tiene unidades dependientes.',
            'type' => 'error'
        ];
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    }

    // Obtener el u_padre del nodo que se va a eliminar
    $sql_get_parent = "SELECT u_padre FROM arquitectura WHERE id = :id";
    $stmt_get_parent = $pdo->prepare($sql_get_parent);
    $stmt_get_parent->execute([':id' => $id]);
    $u_padre = $stmt_get_parent->fetchColumn();

    // Proceder con la eliminación
    $sql_delete = "DELETE FROM arquitectura WHERE id = :id";
    $stmt_delete = $pdo->prepare($sql_delete);
    $stmt_delete->execute([':id' => $id]);

    // Si tenía un `u_padre`, verificar si quedó sin hijos y actualizar `has_children`
    if ($u_padre) {
        $sql_check_siblings = "SELECT COUNT(*) FROM arquitectura WHERE u_padre = :u_padre";
        $stmt_check_siblings = $pdo->prepare($sql_check_siblings);
        $stmt_check_siblings->execute([':u_padre' => $u_padre]);
        $siblings_count = $stmt_check_siblings->fetchColumn();

        if ($siblings_count == 0) {
            $sql_update_parent = "UPDATE arquitectura SET has_children = 0 WHERE id = :u_padre";
            $stmt_update_parent = $pdo->prepare($sql_update_parent);
            $stmt_update_parent->execute([':u_padre' => $u_padre]);
        }
    }

    $_SESSION['toast_message'] = [
        'message' => 'Sitio eliminado correctamente.',
        'type' => 'success'
    ];
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit();
} catch (Exception $e) {
    $_SESSION['toast_message'] = [
        'message' => 'Error al eliminar el sitio: ' . htmlspecialchars($e->getMessage()),
        'type' => 'error'
    ];
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit();
}
?>
