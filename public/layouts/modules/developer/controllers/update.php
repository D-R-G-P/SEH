<?php
require_once '../../../../../app/db/db.php';
session_start();

$db = new DB();
$pdo = $db->connect();

// Verificar si se recibieron los datos necesarios
if (isset($_POST['version']) && isset($_POST['descripcion'])) {
    $version = $_POST['version'];
    $descripcion = $_POST['descripcion'];

    try {

        // Insertar un nuevo rol
        $query = "INSERT INTO updates (version, descripcion) VALUES (:version, :descripcion)";
        $stmt = $pdo->prepare($query);

        // Parámetros comunes
        $stmt->bindParam(':version', $version, PDO::PARAM_STR);
        $stmt->bindParam(':descripcion', $descripcion, PDO::PARAM_STR);
        $stmt->execute();

        // Mensaje de éxito
        $_SESSION['toast_message'] = [
            'message' => 'Update registrado correctamente.',
            'type' => 'success'
        ];
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    } catch (Exception $e) {
        // Manejar errores de la base de datos
        $_SESSION['toast_message'] = [
            'message' => 'Error al registrar la update: ' . htmlspecialchars($e->getMessage()),
            'type' => 'error'
        ];
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    }
} else {
    // Si los datos no están completos
    http_response_code(400);
    $_SESSION['toast_message'] = [
        'message' => 'Error al registrar la update: Datos incompletos.',
        'type' => 'error'
    ];
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit();
}
