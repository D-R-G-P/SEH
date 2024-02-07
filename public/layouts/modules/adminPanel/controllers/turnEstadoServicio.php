<?php
// Realiza la conexión a la base de datos (utiliza tu propia lógica para la conexión)
require_once '../../../../../app/db/db.php';

$db = new DB();
$pdo = $db->connect();

session_start();

// Verifica si se han enviado los parámetros id y action en la URL
if(isset($_GET['id']) && isset($_GET['action'])) {
    // Obtén los valores de id y action desde la URL
    $id = $_GET['id'];
    $action = $_GET['action'];

    try {
        // Prepara la consulta SQL para actualizar el estado según la acción
        $estado = '';
        switch($action) {
            case 'activar':
                $estado = 'Activo';
                break;
            case 'desactivar':
                $estado = 'Inactivo';
                break;
            case 'eliminar':
                $estado = 'Eliminado';
                break;
            default:
                throw new Exception('Acción no válida');
        }

        $stmt = $pdo->prepare("UPDATE servicios SET estado = ? WHERE id = ?");
        $stmt->execute([$estado, $id]);

        // Almacena un mensaje de éxito en la sesión
        $_SESSION['success_message'] = '<div class="notisContent"><div class="notis" id="notis">Cambio realizado correctamente.</div></div><script>setTimeout(() => {notis.classList.toggle("active");out();}, 1);function out() {setTimeout(() => {notis.classList.toggle("active");}, 2500);}</script>';

        // Redirige de vuelta a donde viniste
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    } catch (PDOException $e) {
        // Si hay un error en la base de datos, almacena el mensaje de error en la sesión
        $_SESSION['error_message'] = '<div class="notisContent"><div class="notiserror" id="notis">Error en la base de datos: '.$e->getMessage().'</div></div><script>setTimeout(() => {notis.classList.toggle("active");out();}, 1);function out() {setTimeout(() => {notis.classList.toggle("active");}, 2500);}</script>';

        // Redirige de vuelta a donde viniste
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    } catch (Exception $e) {
        // Si hay un error en la acción, almacena el mensaje de error en la sesión
        $_SESSION['error_message'] = '<div class="notisContent"><div class="notiserror" id="notis">Error: '.$e->getMessage().'.</div></div><script>setTimeout(() => {notis.classList.toggle("active");out();}, 1);function out() {setTimeout(() => {notis.classList.toggle("active");}, 2500);}</script>';

        // Redirige de vuelta a donde viniste
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }
} else {
    // Si no se enviaron los parámetros necesarios, muestra un mensaje de error y redirige
    $_SESSION['error_message'] = '<div class="notisContent"><div class="notiserror" id="notis">Error al enviar los parametros.</div></div><script>setTimeout(() => {notis.classList.toggle("active");out();}, 1);function out() {setTimeout(() => {notis.classList.toggle("active");}, 2500);}</script>';

    // Redirige de vuelta a donde viniste
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}
