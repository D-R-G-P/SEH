<?php
session_start(); // Iniciar la sesión
// Verificar si se recibió el formulario mediante el método POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verificar si se recibió el DNI y el pedido
    if (isset($_POST['dniInfo']) && isset($_POST['observacionInfo'])) {
        // Obtener los datos del formulario
        $dni = $_POST['dniInfo'];
        $observacion = $_POST['observacionInfo'];
        $notificacion = $_POST['notiCheck'];
        $habilitacion = $_POST['habiCheck'];

        if ($notificacion == "on") {
            $new = "si";
        } else {
            $new = "no";
        }

        if ($habilitacion == "on") {
            $estado = "habilitado";
        } else {
            $estado = "working";
        }

        // Aquí deberías insertar los datos en tu base de datos
        // Esto es solo un ejemplo de cómo podrías hacerlo, asegúrate de adaptarlo a tu esquema de base de datos

        // Requerir el archivo de conexión a la base de datos
        require_once '../../../../../app/db/db.php';

        // Crear una instancia de la clase DB para conectarse a la base de datos
        $db = new DB();
        $pdo = $db->connect();

        // Preparar la consulta SQL para actualizar el pedido en la base de datos
        $query = "UPDATE hsi SET estado = :estado, observaciones = :observacion, new = :new WHERE dni = :dni";
        $statement = $pdo->prepare($query);

        // Enlazar los parámetros
        $statement->bindParam(':dni', $dni, PDO::PARAM_STR);
        $statement->bindParam(':estado', $estado, PDO::PARAM_STR);
        $statement->bindParam(':observacion', $observacion, PDO::PARAM_STR);
        $statement->bindParam(':new', $new, PDO::PARAM_STR);

        // Ejecutar la consulta
        if ($statement->execute()) {
            // Si la consulta se ejecuta con éxito, redirigir al usuario a una página de éxito o mostrar un mensaje de éxito
            $_SESSION['toast_message'] = [
                'message' => 'Observación realizada correctamente.',
                'type' => 'success'
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
} else {
    $_SESSION['toast_message'] = [
        'message' => 'El formulario no se envió correctamente.',
        'type' => 'error'
    ];
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit(); // Finalizar el script después de la redirección
}
