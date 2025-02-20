<?php
session_start();
// Verificar si se ha enviado el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Incluir la conexión a la base de datos
    require_once '../../../../../app/db/db.php';

    // Crear una instancia de la clase DB
    $db = new DB();
    $pdo = $db->connect();

    // Obtener los valores del formulario
    $idUsuario = $_POST['idUser'];
    $apellido = $_POST['apellido'];
    $nombre = $_POST['nombre'];
    $dni = $_POST['dni'];
    $mn = $_POST['mn'];
    $mp = $_POST['mp'];
    $pr = "no";

    // Verificar si la contraseña ha sido modificada
    if (!empty($_POST['updatePassword'])) {
        $password = md5($_POST['updatePassword']); // Aplicar hash MD5 a la nueva contraseña
        // Actualizar el usuario con la nueva contraseña
        $stmt = $pdo->prepare("UPDATE personal SET apellido = ?, nombre = ?, dni = ?, password = ?, mn = ?, mp = ?, pr = ? WHERE id = ?");
        $stmt->execute([$apellido, $nombre, $dni, $password, $mn, $mp, $pr, $idUsuario]);
    } else {
        // Si la contraseña no ha sido modificada, actualizar el usuario sin cambiar la contraseña
        $stmt = $pdo->prepare("UPDATE personal SET apellido = ?, nombre = ?, dni = ?, mn = ?, mp = ?, pr = ? WHERE id = ?");
        $stmt->execute([$apellido, $nombre, $dni, $mn, $mp, $pr, $idUsuario]);
    }

    $_SESSION['toast_message'] = [
        'message' => 'Perfil modificado correctamente.',
        'type' => 'success'
    ];
    header("Location: ../miUsuario.php");
    exit;
} else {
    $_SESSION['toast_message'] = [
        'message' => 'Error al registrar el formulario.',
        'type' => 'error'
    ];
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
}