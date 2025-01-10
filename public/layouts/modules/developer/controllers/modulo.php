<?php
require_once '../../../../../app/db/db.php';
session_start();

$db = new DB();
$pdo = $db->connect();

// Verificar si se recibieron los datos necesarios
if (isset($_POST['modulo']) && isset($_POST['descripcion'])) {
    $id = $_POST['id'] ?? null; // Tomar el ID si está presente, o dejarlo como null
    $modulo = $_POST['modulo'];
    $descripcion = $_POST['descripcion'];
    $estado = $_POST['modulo_estado'] ?? "Activo";

    try {
        if ($id) {
            // Actualizar el módulo existente
            $query = "UPDATE modulos SET modulo = :modulo, descripcion = :descripcion, estado = :estado WHERE id = :id";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        } else {
            // Insertar un nuevo módulo
            $query = "INSERT INTO modulos (modulo, descripcion, estado) VALUES (:modulo, :descripcion, :estado)";
            $stmt = $pdo->prepare($query);
        }

        // Parámetros comunes para ambos casos
        $stmt->bindParam(':modulo', $modulo, PDO::PARAM_STR);
        $stmt->bindParam(':descripcion', $descripcion, PDO::PARAM_STR);
        $stmt->bindParam(':estado', $estado, PDO::PARAM_STR);

        // Ejecutar la consulta
        $stmt->execute();

        // Mensaje de éxito
        $_SESSION['success_message'] = '<div class="notisContent"><div class="notis" id="notis" style="text-align: center;">Módulo registrado/actualizado correctamente.</div></div><script>setTimeout(() => {notis.classList.toggle("active");out();}, 1);function out() {setTimeout(() => {notis.classList.toggle("active");}, 2500);}</script>';
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    } catch (Exception $e) {
        // Manejar errores de la base de datos
        $_SESSION['error_message'] = '<div class="notisContent"><div class="notiserror" id="notis">Error al registrar el módulo: ' . htmlspecialchars($e->getMessage()) . '</div></div><script>setTimeout(() => {notis.classList.toggle("active");out();}, 1);function out() {setTimeout(() => {notis.classList.toggle("active");}, 2500);}</script>';
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    }
} else {
    // Si los datos no están completos
    http_response_code(400);
    $_SESSION['error_message'] = '<div class="notisContent"><div class="notiserror" id="notis">Error al registrar el módulo: Datos incompletos.</div></div><script>setTimeout(() => {notis.classList.toggle("active");out();}, 1);function out() {setTimeout(() => {notis.classList.toggle("active");}, 2500);}</script>';
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit();
}
