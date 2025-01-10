<?php
// Include database connection
include_once '../../../../../app/db/db.php';  // Archivo de configuración para conectar a la base de datos
include_once '../../../../config.php';  // Archivo de configuración

// Conexión a la base de datos
$db = new DB();
$pdo = $db->connect();

session_start(); // Iniciar la sesión

// Validar si se ha enviado el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['sender'], $_POST['estado'])) {
    // Obtener y sanitizar valores del formulario
    $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
    $sender = filter_input(INPUT_POST, 'sender', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $estado = filter_input(INPUT_POST, 'estado', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    // Validar valores
    if (empty($id) || empty($sender) || empty($estado)) {
        $_SESSION['error_message'] = '<div class="notisContent"><div class="notiserror" id="notis">Datos inválidos.</div></div><script>setTimeout(() => {notis.classList.toggle("active");out();}, 1);function out() {setTimeout(() => {notis.classList.toggle("active");}, 2500);}</script>';
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    }

    // Inicializar la columna de estado
    $estadoColumn = null;

    // Determinar columna según el sender

    if ($sender == 'reclamante') {
        $estadoColumn = "estado_reclamante";
        $act = "reclamante";
    } else if ($sender == 'destino') {
        $estadoColumn = "estado_destino";
        $act = "destino";
    }

    if ($estadoColumn === null) {
        $_SESSION['error_message'] = '<div class="notisContent"><div class="notiserror" id="notis">Datos inválidos.</div></div><script>setTimeout(() => {notis.classList.toggle("active");out();}, 1);function out() {setTimeout(() => {notis.classList.toggle("active");}, 2500);} window.addEventListener("DOMContentLoaded", () => { loadInfo("' . htmlspecialchars($id) . '", "' . htmlspecialchars($act) . '"); });</script>';
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    }

    // Actualizar el estado
    try {
        $query = "UPDATE mantenimiento SET $estadoColumn = :estado";
        if ($estado === 'Completado') {
            $query .= ", fecha_destino = NOW(), new_reclamante_data = :new_data";
        }
        $query .= " WHERE id = :id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':estado', $estado, PDO::PARAM_STR);
        if ($estado === 'Completado') {
            $newData = "El servicio marcó este caso como resuelto, para finalizar la solicitud marque el estado como 'Completado'.";
            $stmt->bindParam(':new_data', $newData, PDO::PARAM_STR);
        }
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = '<div class="notisContent"><div class="notis" id="notis" style="text-align: center;">Estado cambiado correctamente.</div></div><script>setTimeout(() => {notis.classList.toggle("active");out();}, 1);function out() {setTimeout(() => {notis.classList.toggle("active");}, 2500);} window.addEventListener("DOMContentLoaded", () => { loadInfo("' . htmlspecialchars($id) . '", "' . htmlspecialchars($act) . '"); });</script>';
            header("Location: " . $_SERVER['HTTP_REFERER']);
            exit;
        } else {
            $_SESSION['error_message'] = '<div class="notisContent"><div class="notiserror" id="notis">Error al actualizar el estado. Por favor, inténtalo de nuevo.</div></div><script>setTimeout(() => {notis.classList.toggle("active");out();}, 1);function out() {setTimeout(() => {notis.classList.toggle("active");}, 2500);} window.addEventListener("DOMContentLoaded", () => { loadInfo("' . htmlspecialchars($id) . '", "' . htmlspecialchars($act) . '"); });</script>';
            header("Location: " . $_SERVER['HTTP_REFERER']);
            exit;
        }
    } catch (PDOException $e) {
        $_SESSION['error_message'] = '<div class="notisContent"><div class="notiserror" id="notis">Error al procesar el pedido: ' . $e->getMessage() . '. Por favor, inténtalo de nuevo.</div></div><script>setTimeout(() => {notis.classList.toggle("active");out();}, 1);function out() {setTimeout(() => {notis.classList.toggle("active");}, 2500);} window.addEventListener("DOMContentLoaded", () => { loadInfo("' . htmlspecialchars($id) . '", "' . htmlspecialchars($act) . '"); });</script>';
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    }
} else {
    echo "Acceso no permitido.";
    exit;
}
