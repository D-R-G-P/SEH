<?php
session_start(); // Iniciar la sesión

// Verificar si se recibieron los parámetros en la URL
if (isset($_GET['dni']) && isset($_GET['action'])) {
    // Obtener los datos del formulario de la URL
    $dni = $_GET['dni'];
    $action = $_GET['action'];

    switch ($action) {
        case 'baja':
            $pedido = "Se solicita baja del usuario.";
            $message = "Solicitud de baja realizada correctamente.";
            $type = "success";
            break;
        case 'password':
            $pedido = "Se solicita reinicio de contraseña";
            $message = "Solicitud de reinicio de contraseña realizado correctamente";
            $type = "success";
            break;
    }

    // Aquí deberías insertar los datos en tu base de datos
    // Esto es solo un ejemplo de cómo podrías hacerlo, asegúrate de adaptarlo a tu esquema de base de datos

    // Requerir el archivo de conexión a la base de datos
    require_once '../../../../../app/db/db.php';
    $estado = "working";

    // Crear una instancia de la clase DB para conectarse a la base de datos
    $db = new DB();
    $pdo = $db->connect();

    // Preparar la consulta SQL para actualizar el pedido en la base de datos
    $query = "UPDATE hsi SET estado = :estado, pedido = :pedido WHERE dni = :dni";
    $statement = $pdo->prepare($query);

    // Enlazar los parámetros
    $statement->bindParam(':dni', $dni, PDO::PARAM_STR);
    $statement->bindParam(':estado', $estado, PDO::PARAM_STR);
    $statement->bindParam(':pedido', $pedido, PDO::PARAM_STR);

    // Ejecutar la consulta
    if ($statement->execute()) {
        // Si la consulta se ejecuta con éxito, redirigir al usuario a una página de éxito o mostrar un mensaje de éxito
        $_SESSION['toast_message'] = [
            'message' => $message,
            'type' => $type
        ];
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit(); // Finalizar el script después de la redirección
    } else {
        $_SESSION['toast_message'] = [
            'message' => 'Error al procesar el pedido. Por favor, inténtalo de nuevo.',
            'type' => 'error'
        ];
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit(); // Finalizar el script después de la redirección
    }
} else {
    $_SESSION['toast_message'] = [
        'message' => 'No se recibieron todos los datos necesarios del formulario.',
        'type' => 'error'
    ];
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit(); // Finalizar el script después de la redirección
}
