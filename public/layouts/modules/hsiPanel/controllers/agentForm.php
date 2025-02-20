<?php
session_start(); // Iniciar la sesión

// Verificar si se recibieron los datos del formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verificar si se recibieron todos los campos necesarios
    if (isset($_POST['dni'])) {
        // Incluir el archivo de conexión a la base de datos
        require_once '../../../../../app/db/db.php';

        // Crear una instancia de la clase DB para la conexión a la base de datos
        $db = new DB();
        $pdo = $db->connect();

        // Obtener los datos del formulario
        $dni = $_POST['dni'];
        $mail = $_POST['mail'];
        $phone = $_POST['phone'];
        $servicioSelect = $_POST['servicioSelect'];
        $idPersona = $_POST['idPersona'];
        $nombreUsuario = $_POST['nombreUsuario'];
        $idUsuario = $_POST['idUsuario'];

        // Crear una consulta preparada para insertar los datos en la base de datos
        $query = "UPDATE hsi SET servicio = :servicioSelect, mail = :mail, telefono = :phone, id_persona = :idPersona, id_usuario = :idUsuario, nombre_usuario = :nombreUsuario WHERE dni = :dni";

        // Parámetros de la consulta preparada
        $params = [
            ':servicioSelect' => $servicioSelect,
            ':mail' => $mail,
            ':phone' => $phone,
            ':idPersona' => $idPersona,
            ':nombreUsuario' => $nombreUsuario,
            ':idUsuario' => $idUsuario,
            ':dni' => $dni
        ];

        // Ejecutar la consulta preparada para registrar el usuario
        $stmt = $pdo->prepare($query);
        if ($stmt->execute($params)) {
            // Registro exitoso, mostrar un mensaje de éxito
            $_SESSION['toast_message'] = [
                'message' => 'Datos modificados correctamente.',
                'type' => 'success'
            ];
            $_SESSION['load_info'] = [
                'dni' => $dni,
                'servicio' => $servicio
            ];
            header("Location: " . $_SERVER['HTTP_REFERER']);
            exit(); // Finalizar el script después de la redirección
        } else {
            // Error al registrar el usuario, mostrar un mensaje de error
            $_SESSION['toast_message'] = [
                'message' => 'Error al modificar los datos.',
                'type' => 'error'
            ];
            header("Location: " . $_SERVER['HTTP_REFERER']);
            exit(); // Finalizar el script después de la redirección
        }
    } else {
        // No se recibieron todos los campos necesarios, mostrar un mensaje de error
        $_SESSION['toast_message'] = [
            'message' => 'Por favor verifique todos los datos obligatorios.',
            'type' => 'error'
        ];
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit(); // Finalizar el script después de la redirección
    }
}
