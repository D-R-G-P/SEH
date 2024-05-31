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
            ':mes' => $servicioSelect,
            ':usuario_registro' => $mail,
            ':especialidad' => $phone,
            ':dia' => $idPersona,
            ':regimen' => $nombreUsuario,
            ':especialista' => $idUsuario,
            ':estado' => 'afectado'
        ];

        // Ejecutar la consulta preparada para registrar el usuario
        $stmt = $pdo->prepare($query);
        if ($stmt->execute($params)) {
            // Registro exitoso, mostrar un mensaje de éxito
            $_SESSION['success_message'] = '<div class="notisContent"><div class="notis" id="notis" style="text-align: center;">Datos modificados correctamente.</div></div><script>setTimeout(() => {notis.classList.toggle("active");out();}, 1);function out() {setTimeout(() => {notis.classList.toggle("active");}, 2500);}; document.addEventListener("DOMContentLoaded", function() { loadInfo(\''.$dni.'\', \''.$servicioSelect.'\'); });</script>';
            header("Location: ../guardias.php");
            exit(); // Finalizar el script después de la redirección
        } else {
            // Error al registrar el usuario, mostrar un mensaje de error
            $_SESSION['error_message'] = '<div class="notisContent"><div class="notiserror" id="notis">Error al modificar los datos.</div></div><script>setTimeout(() => {notis.classList.toggle("active");out();}, 1);function out() {setTimeout(() => {notis.classList.toggle("active");}, 2500);}</script>';
            header("Location: ../guardias.php");
            exit(); // Finalizar el script después de la redirección
        }
    } else {
        // No se recibieron todos los campos necesarios, mostrar un mensaje de error
        $_SESSION['error_message'] = '<div class="notisContent"><div class="notiserror" id="notis">Por favor verifique todos los datos obligatorios.</div></div><script>setTimeout(() => {notis.classList.toggle("active");out();}, 1);function out() {setTimeout(() => {notis.classList.toggle("active");}, 2500);}</script>';
        header("Location: ../guardias.php");
        exit(); // Finalizar el script después de la redirección
    }
}