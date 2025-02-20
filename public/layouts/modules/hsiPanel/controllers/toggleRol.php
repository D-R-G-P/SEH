<?php
require_once '../../../../../app/db/db.php';
session_start();

$db = new DB();
$pdo = $db->connect();

$rol = $_GET['rol'];
$estado = $_GET['toggle'];

if ($estado == "activar") {
    $estadoNew = "activo";
} else if ($estado == "desactivar") {
    $estadoNew = "desactivado";
}

// Realizar la actualización del estado en la base de datos
$query = "UPDATE roles_hsi SET estado = ? WHERE id = ?";
$stmt = $pdo->prepare($query);
$stmt->bindValue(1, $estadoNew, PDO::PARAM_STR);
$stmt->bindValue(2, $rol, PDO::PARAM_STR);
if ($stmt->execute()) {
    // Si la consulta se ejecuta con éxito, redirigir al usuario a una página de éxito o mostrar un mensaje de éxito
    $_SESSION['toast_message'] = [
        'message' => 'Se cambió correctamente el estado del rol.',
        'type' => 'success'
    ];
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit(); // Finalizar el script después de la redirección
} else {
    // Manejar el error si la consulta no se ejecuta correctamente
    $_SESSION['toast_message'] = [
        'message' => 'Error al cambiar el estado del rol.',
        'type' => 'error'
    ];
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit(); // Finalizar el script después de la redirección
}
?>
