<?php
session_start(); // Iniciar la sesión

// Verificar si se recibieron los datos del formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verificar si se recibieron todos los campos necesarios
    if (isset($_POST['rol_new'])) {
        // Incluir el archivo de conexión a la base de datos
        require_once '../../../../../app/db/db.php';

        // Crear una instancia de la clase DB para la conexión a la base de datos
        $db = new DB();
        $pdo = $db->connect();

        // Obtener los datos del formulario
        $rol = $_POST['rol_new'];

        // Verificar si el DNI ya existe en la base de datos
        $stmt_check_rol = $pdo->prepare("SELECT rol FROM roles_hsi WHERE rol = :rol");
        $stmt_check_rol->execute([':rol' => $rol]);
        $existing_rol = $stmt_check_rol->fetchColumn();

        if ($existing_rol) {
            $_SESSION['toast_message'] = [
                'message' => 'El rol ya existe.',
                'type' => 'error'
            ];
            header("Location: " . $_SERVER['HTTP_REFERER']);
            exit(); // Finalizar el script después de la redirección
        }

        // Crear una consulta preparada para insertar los datos en la base de datos
        $query = "INSERT INTO roles_hsi (rol, estado) VALUES (:rol, :estado)";

        // Parámetros de la consulta preparada
        $params = [
            ':rol' => $rol,
            ':estado' => 'activo'
        ];

        // Ejecutar la consulta preparada para registrar el usuario
        $stmt = $pdo->prepare($query);
        if ($stmt->execute($params)) {
            // Registro exitoso, mostrar un mensaje de éxito
            $_SESSION['toast_message'] = [
                'message' => 'Rol agregado correctamente',
                'type' => 'success'
            ];
            header("Location: " . $_SERVER['HTTP_REFERER']);
            exit(); // Finalizar el script después de la redirección
        } else {
            // Error al registrar el usuario, mostrar un mensaje de error
            $_SESSION['toast_message'] = [
                'message' => 'Error al registrar el rol.',
                'type' => 'error'
            ];
            header("Location: " . $_SERVER['HTTP_REFERER']);
            exit(); // Finalizar el script después de la redirección
        }
    } else {
        // No se recibieron todos los campos necesarios, mostrar un mensaje de error
        $_SESSION['toast_message'] = [
            'message' => 'Por favor verifique los datos.',
            'type' => 'error'
        ];
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit(); // Finalizar el script después de la redirección
    }
}