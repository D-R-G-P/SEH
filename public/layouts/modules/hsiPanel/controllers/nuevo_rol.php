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
            $_SESSION['error_message'] = '<div class="notisContent"><div class="notiserror" id="notis" style="text-align: center;">El rol ya existe.</div></div><script>setTimeout(() => {notis.classList.toggle("active");out();}, 1);function out() {setTimeout(() => {notis.classList.toggle("active");}, 25000);}</script>';
            header("Location: ../hsiAdmin.php");
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
            $_SESSION['success_message'] = '<div class="notisContent"><div class="notis" id="notis" style="text-align: center;">Rol agregado correctamente</div></div><script>setTimeout(() => {notis.classList.toggle("active");out();}, 1);function out() {setTimeout(() => {notis.classList.toggle("active");}, 2500);} window.addEventListener("DOMContentLoaded", () => { back.style.display = "flex"; rolesModule.style.display = "flex"; })</script>';
            header("Location: ../hsiAdmin.php");
            exit(); // Finalizar el script después de la redirección
        } else {
            // Error al registrar el usuario, mostrar un mensaje de error
            $_SESSION['error_message'] = '<div class="notisContent"><div class="notiserror" id="notis">Error al registrar el rol.</div></div><script>setTimeout(() => {notis.classList.toggle("active");out();}, 1);function out() {setTimeout(() => {notis.classList.toggle("active");}, 2500);} window.addEventListener("DOMContentLoaded", () => { back.style.display = "flex"; rolesModule.style.display = "flex"; })</script>';
            header("Location: ../hsiAdmin.php");
            exit(); // Finalizar el script después de la redirección
        }
    } else {
        // No se recibieron todos los campos necesarios, mostrar un mensaje de error
        $_SESSION['error_message'] = '<div class="notisContent"><div class="notiserror" id="notis">Por favor verifique los datos.</div></div><script>setTimeout(() => {notis.classList.toggle("active");out();}, 1);function out() {setTimeout(() => {notis.classList.toggle("active");}, 2500);} window.addEventListener("DOMContentLoaded", () => { back.style.display = "flex"; rolesModule.style.display = "flex"; })</script>';
        header("Location: ../hsi.php");
        exit(); // Finalizar el script después de la redirección
    }
}