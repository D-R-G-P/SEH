<?php
require_once '../../../../../app/db/db.php';

session_start(); // Iniciar sesión para usar variables de sesión

// Verificar si se recibió el parámetro GET esperado
if(isset($_GET['id']) && isset($_GET['dni'])) {
    // Obtener el valor del parámetro GET
    $id = $_GET['id'];
    $dni = $_GET['dni'];

    // Almacenar los mensajes en variables de sesión
    $_SESSION['success_message'] = "ID: $id, DNI: $dni"; // Mensaje de éxito

    // Generar la contraseña MD5 a partir del DNI
    $contrasena_md5 = md5($dni);

    // Crear una instancia de la clase DB para conectarse a la base de datos
    $db = new DB();
    $pdo = $db->connect();

    // Verificar si la conexión fue exitosa
    if($pdo) {
        try {
            // Actualizar la contraseña en la base de datos
            $sql_update = "UPDATE personal SET password=? WHERE id=?";
            $stmt_update = $pdo->prepare($sql_update);
            $stmt_update->execute([$contrasena_md5, $id]);

            $_SESSION['success_message'] = '<div class="notisContent"><div class="notis" id="notis" style="text-align: center;">Contraseña actualizada correctamente.
            </br>
            Deberá ingresar con su dni separado por punto (<b>'.$dni.'</b>)</div></div><script>setTimeout(() => {notis.classList.toggle("active");out();}, 1);function out() {setTimeout(() => {notis.classList.toggle("active");}, 25000);}</script>';
        } catch (PDOException $e) {
            $_SESSION['error_message'] = '<div class="notisContent"><div class="notiserror" id="notis">Error al generar la contraseña: ' . $e->getMessage() . '.</div></div><script>setTimeout(() => {notis.classList.toggle("active");out();}, 1);function out() {setTimeout(() => {notis.classList.toggle("active");}, 2500);}</script>';
        }
    } else {
        $_SESSION['error_message'] = '<div class="notisContent"><div class="notiserror" id="notis">Error al conectar a la base de datos.</div></div><script>setTimeout(() => {notis.classList.toggle("active");out();}, 1);function out() {setTimeout(() => {notis.classList.toggle("active");}, 2500);}</script>';
    }
} else {
    $_SESSION['error_message'] = '<div class="notisContent"><div class="notiserror" id="notis">Error al obtener los parametros.</div></div><script>setTimeout(() => {notis.classList.toggle("active");out();}, 1);function out() {setTimeout(() => {notis.classList.toggle("active");}, 2500);}</script>';
}

// Redireccionar de nuevo a la página anterior
header("Location: " . $_SERVER['HTTP_REFERER']);
?>
