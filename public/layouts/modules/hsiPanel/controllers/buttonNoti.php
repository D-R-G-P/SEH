<?php
require_once '../../../../../app/db/db.php';

session_start(); // Iniciar sesión para usar variables de sesión

// Verificar si se recibió el parámetro GET esperado
if(isset($_GET['dni']) && isset($_GET['action'])) {
    // Obtener el valor del parámetro GET
    $dni = $_GET['dni'];
    $action = $_GET['action'];
    $new = "si";
    $fecha = date("d/m/Y H:i");

    switch ($action) {
        case 'baja':
            $observaciones = "Usuario deshabilitado.";
            $estado = "disabled";
            break;
        case 'password':
            $observaciones = "Contraseña reiniciada, credenciales enviadas al mail del agente. Cuenta con 48 horas para acceder. (".$fecha.")";
            $estado = "habilitado";
            break;
        case 'habilita':
            $observaciones = "Usuario creado correctamente, las credenciales fueron enviadas al mail del agente. Cuenta con 48 horas para acceder. (".$fecha.")";
            $estado = "habilitado";
            break;
    }

    // Crear una instancia de la clase DB para conectarse a la base de datos
    $db = new DB();
    $pdo = $db->connect();

    // Verificar si la conexión fue exitosa
    if($pdo) {
        try {
            // Actualizar la contraseña en la base de datos
            $sql_update = "UPDATE hsi SET observaciones = ?, new = ?, estado = ? WHERE dni = ?";
            $stmt_update = $pdo->prepare($sql_update);
            $stmt_update->execute([$observaciones, $new, $estado, $dni]);

            $_SESSION['success_message'] = '<div class="notisContent"><div class="notis" id="notis" style="text-align: center;">Notificación enviada correctamente</div></div><script>setTimeout(() => {notis.classList.toggle("active");out();}, 1);function out() {setTimeout(() => {notis.classList.toggle("active");}, 25000);}</script>';
        } catch (PDOException $e) {
            $_SESSION['error_message'] = '<div class="notisContent"><div class="notiserror" id="notis">Error al enviar la notificación</div></div><script>setTimeout(() => {notis.classList.toggle("active");out();}, 1);function out() {setTimeout(() => {notis.classList.toggle("active");}, 2500);}</script>';
        }
    } else {
        $_SESSION['error_message'] = '<div class="notisContent"><div class="notiserror" id="notis">Error al conectar a la base de datos.</div></div><script>setTimeout(() => {notis.classList.toggle("active");out();}, 1);function out() {setTimeout(() => {notis.classList.toggle("active");}, 2500);}</script>';
    }
} else {
    $_SESSION['error_message'] = '<div class="notisContent"><div class="notiserror" id="notis">Error al obtener los parametros.</div></div><script>setTimeout(() => {notis.classList.toggle("active");out();}, 1);function out() {setTimeout(() => {notis.classList.toggle("active");}, 2500);}</script>';
}

// Redireccionar de nuevo a la página anterior
header("Location: " . $_SERVER['HTTP_REFERER']);