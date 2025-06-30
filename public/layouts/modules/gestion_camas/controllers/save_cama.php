<?php

require_once '../../../../../app/db/db.php';
require_once '../../../../../app/db/user_session.php';
require_once '../../../../../app/db/user.php';
require_once '../../../../config.php';

$db = new DB();
$pdo = $db->connect();

$user = new User();
$userSession = new UserSession();
$currentUser = $userSession->getCurrentUser();
$user->setUser($currentUser);

$cama_id = isset($_POST['cama_id']) ? $_POST['cama_id'] : null;
$cama_name = isset($_POST['cama_name']) ? $_POST['cama_name'] : null;
$complejidad = isset($_POST['complejidad']) ? $_POST['complejidad'] : null;
$description = isset($_POST['description']) ? $_POST['description'] : null; // Asegúrate de que esta variable sea correcta para el INSERT
$unidad_id = isset($_POST['unidad_id']) ? $_POST['unidad_id'] : null; // Esto es ubicacion_arquitectura_id
$editor = $user->getDni(); // Esto es el DNI del usuario actual, para created_by o updated_by

// Validar tipo de unidad con arquitectura

$sql = "SELECT
    a.id,
    a.nombre,
    a.tipo_sitio_id,
    ts.nombre AS tipo_sitio_nombre,
    a.estado
FROM
    arquitectura AS a
LEFT JOIN
    tipo_sitio AS ts ON a.tipo_sitio_id = ts.id
WHERE
    ts.nombre = 'Habitación' AND a.id = :unidad_id";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':unidad_id', $unidad_id, PDO::PARAM_INT);
$stmt->execute();

if (!hasSubAccess(['administrador_camas']) || !hasAccess(['administrador', 'direccion'])) {
    $_SESSION['toast_message'] = [
        'message' => 'No tienes permisos para realizar esta acción.',
        'type' => 'error'
    ];
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit();
}

$unidad = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$unidad) {
    $_SESSION['toast_message'] = [
        'message' => 'La unidad seleccionada no es una habitación.',
        'type' => 'error'
    ];
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit();
}

$sql2 = "SELECT estado FROM arquitectura WHERE id = :unidad_id";
$stmt2 = $pdo->prepare($sql2);
$stmt2->bindParam(':unidad_id', $unidad_id, PDO::PARAM_INT);
$stmt2->execute();

$unidad_estado = $stmt2->fetchColumn();
if ($unidad_estado !== 'Activo') {
    $_SESSION['toast_message'] = [
        'message' => 'La unidad seleccionada no está activa.',
        'type' => 'error'
    ];
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit();
}

if ($cama_id && $cama_name && $complejidad && $description && $unidad_id && $editor) {
    // Lógica para ACTUALIZAR una cama existente
    try {
        $stmt = $pdo->prepare("UPDATE beds SET name = ?, complexity = ?, description = ?, ubicacion_arquitectura_id = ?, updated_by = ?, date_updated = now() WHERE id = ?");
        // Los parámetros aquí están correctamente ordenados para el UPDATE
        $stmt->execute([$cama_name, $complejidad, $description, $unidad_id, $editor, $cama_id]);

        if ($stmt->rowCount() > 0) {
            $_SESSION['toast_message'] = [
                'message' => 'Cama actualizada correctamente',
                'type' => 'success'
            ];
        } else {
            $_SESSION['toast_message'] = [
                'message' => 'No se realizaron cambios o la cama no fue encontrada.',
                'type' => 'error'
            ];
        }
    } catch (PDOException $e) {
        $_SESSION['toast_message'] = [
            'message' => 'Error en la base de datos: ' . $e->getMessage(),
            'type' => 'error',
            'duration' => 50000
        ];
    }
} else if (!$cama_id && $cama_name && $complejidad && $unidad_id && $editor) {
    // Lógica para INSERTAR una nueva cama (cuando $cama_id es nulo)
    try {
        $stmt = $pdo->prepare("INSERT INTO beds (name, complexity, description, ubicacion_arquitectura_id, created_by, date_created, bed_status) VALUES (?, ?, ?, ?, ?, now(), 'Libre')");
        // Aquí los parámetros están en el orden correcto para el INSERT
        $stmt->execute([$cama_name, $complejidad, $description, $unidad_id, $editor]); 

        if ($stmt->rowCount() > 0) {
            $_SESSION['toast_message'] = [
                'message' => 'Cama creada correctamente',
                'type' => 'success'
            ];
        } else {
            $_SESSION['toast_message'] = [
                'message' => 'Error al crear la cama.',
                'type' => 'error'
            ];
        }
    } catch (PDOException $e) {
        $_SESSION['toast_message'] = [
            'message' => 'Error en la base de datos: ' . $e->getMessage(),
            'type' => 'error',
            'duration' => 50000
        ];
    }
} else {
    // Si faltan parámetros necesarios en cualquiera de los casos
    $_SESSION['toast_message'] = [
        'message' => 'Parámetros ingresados inválidos o incompletos.',
        'type' => 'error'
    ];
}

header("Location: " . $_SERVER['HTTP_REFERER']);

?>