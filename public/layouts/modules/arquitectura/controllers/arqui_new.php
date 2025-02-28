<?php
include_once '../../../../../app/db/db.php'; // Conexión a la DB

$db = new DB();
$pdo = $db->connect();

try {
    // Recibir datos del formulario
    $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : null;
    $servicio = isset($_POST['servicio']) && $_POST['servicio'] !== '' ? $_POST['servicio'] : null;
    $tipo_sitio_id = isset($_POST['tipo']) ? $_POST['tipo'] : null;
    $u_padre = isset($_POST['unidad_id']) ? $_POST['unidad_id'] : null;
    $observaciones = isset($_POST['observaciones']) ? trim($_POST['observaciones']) : '';

    // Validar campos obligatorios
    if (!$nombre || !$tipo_sitio_id || !$u_padre) {
        $_SESSION['toast_message'] = [
            'message' => 'Todos los campos obligatorios deben completarse.',
            'type' => 'error'
        ];
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    }

    // Insertar en la base de datos
    $sql = "INSERT INTO arquitectura (servicio, nombre, observaciones, u_padre, tipo_sitio_id, estado, has_children) 
            VALUES (:servicio, :nombre, :observaciones, :u_padre, :tipo_sitio_id, 'activo', 0)";
    $stmt = $pdo->prepare($sql);
    
    $stmt->execute([
        ':servicio' => $servicio,
        ':nombre' => $nombre,
        ':observaciones' => $observaciones,
        ':u_padre' => $u_padre,
        ':tipo_sitio_id' => $tipo_sitio_id
    ]);

    // Actualizar la columna has_children del nodo padre si es necesario
    if ($u_padre) {
        $update_sql = "UPDATE arquitectura SET has_children = 1 WHERE id = :u_padre";
        $update_stmt = $pdo->prepare($update_sql);
        $update_stmt->execute([':u_padre' => $u_padre]);
    }

    $_SESSION['toast_message'] = [
        'message' => 'Sitio registrado correctamente.',
        'type' => 'success'
    ];
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit();
} catch (Exception $e) {
    $_SESSION['toast_message'] = [
        'message' => 'Error al generar el sitio: ' . htmlspecialchars($e->getMessage()),
        'type' => 'error'
    ];
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit();
}
?>
