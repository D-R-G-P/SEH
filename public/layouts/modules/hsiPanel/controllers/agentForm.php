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
            $_SESSION['success_message'] = '<div class="notisContent"><div class="notis" id="notis" style="text-align: center;">Datos modificados correctamente.</div></div><script>setTimeout(() => {notis.classList.toggle("active");out();}, 1);function out() {setTimeout(() => {notis.classList.toggle("active");}, 2500);}; document.addEventListener("DOMContentLoaded", function() { loadInfo(\''.$dni.'\', \''.$servicioSelect.'\'); });</script>';
            header("Location: ../hsiAdmin.php");
            exit(); // Finalizar el script después de la redirección
        } else {
            // Error al registrar el usuario, mostrar un mensaje de error
            $_SESSION['error_message'] = '<div class="notisContent"><div class="notiserror" id="notis">Error al modificar los datos.</div></div><script>setTimeout(() => {notis.classList.toggle("active");out();}, 1);function out() {setTimeout(() => {notis.classList.toggle("active");}, 2500);}</script>';
            header("Location: ../hsiAdmin.php");
            exit(); // Finalizar el script después de la redirección
        }
    } else {
        // No se recibieron todos los campos necesarios, mostrar un mensaje de error
        $_SESSION['error_message'] = '<div class="notisContent"><div class="notiserror" id="notis">Por favor verifique todos los datos obligatorios.</div></div><script>setTimeout(() => {notis.classList.toggle("active");out();}, 1);function out() {setTimeout(() => {notis.classList.toggle("active");}, 2500);}</script>';
        header("Location: ../hsiAdmin.php");
        exit(); // Finalizar el script después de la redirección
    }
}
