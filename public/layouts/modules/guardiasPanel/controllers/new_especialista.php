<?php
session_start(); // Iniciar la sesión

// Verificar si se recibieron los datos del formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verificar si se recibieron todos los campos necesarios
    if (isset($_POST['dniSelect'])) {
        // Incluir el archivo de conexión a la base de datos
        require_once '../../../../../app/db/db.php';

        // Crear una instancia de la clase DB para la conexión a la base de datos
        $db = new DB();
        $pdo = $db->connect();

        // Obtener los datos del formulario
        $fecha = $_POST['fecha'];
        $asignante = $_POST['asignante'];
        $dia = $_POST['dia'];
        $especialidad = $_POST['especialidad'];
        $dniSelect = $_POST['dniSelect'];
        $regimen = $_POST['regimen'];

        // Crear una consulta preparada para insertar los datos en la base de datos
        $query = "INSERT INTO guardias (mes, usuario_registro, especialidad, dia, regimen, especialista, estado) VALUES (:mes, :usuario_registro, :especialidad, :dia, :regimen, :especialista, :estado)";

        // Parámetros de la consulta preparada
        $params = [
            ':mes' => $fecha,
            ':usuario_registro' => $asignante,
            ':especialidad' => $especialidad,
            ':dia' => $dia,
            ':regimen' => $regimen,
            ':especialista' => $dniSelect,
            ':estado' => 'afectado'
        ];

        // Ejecutar la consulta preparada para registrar el usuario
        $stmt = $pdo->prepare($query);
        if ($stmt->execute($params)) {
            // Registro exitoso, mostrar un mensaje de éxito
            $_SESSION['toast_message'] = [
                'message' => 'Datos modificados correctamente.',
                'type' => 'success'
            ];
            header("Location: ../guardias.php");
            exit(); // Finalizar el script después de la redirección
        } else {
            // Error al registrar el usuario, mostrar un mensaje de error
            $_SESSION['toast_message'] = [
                'message' => 'Error al modificar los datos.',
                'type' => 'error'
            ];
            header("Location: ../guardias.php");
            exit(); // Finalizar el script después de la redirección
        }
    } else {
        // No se recibieron todos los campos necesarios, mostrar un mensaje de error
        $_SESSION['toast_message'] = [
            'message' => 'Por favor verifique todos los datos obligatorios.',
            'type' => 'error'
        ];
        header("Location: ../guardias.php");
        exit(); // Finalizar el script después de la redirección
    }
}