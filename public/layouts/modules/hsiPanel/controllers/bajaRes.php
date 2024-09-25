<?php
require_once '../../../../../app/db/db.php';
session_start();

$db = new DB();
$pdo = $db->connect();

$observaciones = 'Se estableció este usuario para la baja, dado que pertenece a un residente y finalizó el año de rotación. En caso de que el residente continúe en el servicio, deberá actualizar el "Anexo 1 - Solicitud de usuario", deberá estar indicado el servicio, firmado por el jefe/a del Servicio y por el jefe/a de Docencia';
$estado = 'working';
$new = 'si';
$pedido = 'Mensaje del sistema: Se establece el usuario para su deshabilitación, recuerde también eliminar los roles dado que pertenece a un residente.';

// Realizar la actualización del estado en la base de datos
$query = "UPDATE hsi SET observaciones = ?, estado = ?, new = ?, pedido = ? WHERE residente = 'si'";
$stmt = $pdo->prepare($query);
$stmt->bindValue(1, $observaciones, PDO::PARAM_STR);
$stmt->bindValue(2, $estado, PDO::PARAM_STR);
$stmt->bindValue(3, $new, PDO::PARAM_STR);
$stmt->bindValue(4, $pedido, PDO::PARAM_STR);
if ($stmt->execute()) {
    // Si la consulta se ejecuta con éxito, redirigir al usuario a una página de éxito o mostrar un mensaje de éxito
    $_SESSION['success_message'] = '<div class="notisContent"><div class="notis" id="notis" style="text-align: center;">Se establecieron los usuarios de residentes para la baja correctamente.</div></div><script>setTimeout(() => {notis.classList.toggle("active");out();}, 1);function out() {setTimeout(() => {notis.classList.toggle("active");}, 2500);}</script>';
    header("Location: ../hsiAdmin.php");
    exit(); // Finalizar el script después de la redirección
} else {
    // Manejar el error si la consulta no se ejecuta correctamente
    $_SESSION['error_message'] = '<div class="notisContent"><div class="notiserror" id="notis">Error al solicitar la baja.</div></div><script>setTimeout(() => {notis.classList.toggle("active");out();}, 1);function out() {setTimeout(() => {notis.classList.toggle("active");}, 2500);}</script>';
    header("Location: ../hsi.php");
    exit(); // Finalizar el script después de la redirección
}
?>
