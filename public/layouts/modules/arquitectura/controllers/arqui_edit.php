<?php
include_once '../../../../../app/db/db.php'; // Conexión a la DB
session_start();

$db = new DB();
$pdo = $db->connect();

try {
    // Recibir datos del formulario
    $id_sitio = isset($_POST['id_sitio']) ? intval($_POST['id_sitio']) : null;
    $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : null;
    $servicio = isset($_POST['servicio']) && $_POST['servicio'] !== '' ? $_POST['servicio'] : null;
    $tipo_sitio_id = isset($_POST['tipo']) ? $_POST['tipo'] : null;
    $u_padre = isset($_POST['unidad_id']) ? $_POST['unidad_id'] : null;
    $observaciones = isset($_POST['observaciones']) ? trim($_POST['observaciones']) : '';
    $estado = isset($_POST['estado']) && $_POST['estado'] === 'inactivo' ? 'inactivo' : 'activo';

    // Validar campos obligatorios
    if (!$id_sitio || !$nombre || !$tipo_sitio_id || !$u_padre) {
        $_SESSION['error_message'] = '<div class="notisContent"><div class="notiserror" id="notis">Todos los campos obligatorios deben completarse.</div></div>
        <script>setTimeout(() => {notis.classList.toggle("active");out();}, 1);function out() {setTimeout(() => {notis.classList.toggle("active");}, 2500);}</script>';
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    }

    // Verificar si el sitio cambió de nodo padre
    $stmt = $pdo->prepare("SELECT u_padre FROM arquitectura WHERE id = :id_sitio");
    $stmt->execute(['id_sitio' => $id_sitio]);
    $sitioActual = $stmt->fetch(PDO::FETCH_ASSOC);
    $u_padre_anterior = $sitioActual ? $sitioActual['u_padre'] : null;

    // Actualizar los datos en la base de datos
    $sql = "UPDATE arquitectura 
            SET servicio = :servicio, nombre = :nombre, observaciones = :observaciones, 
                u_padre = :u_padre, tipo_sitio_id = :tipo_sitio_id, estado = :estado 
            WHERE id = :id_sitio";
    $stmt = $pdo->prepare($sql);
    
    $stmt->execute([
        ':id_sitio' => $id_sitio,
        ':servicio' => $servicio,
        ':nombre' => $nombre,
        ':observaciones' => $observaciones,
        ':u_padre' => $u_padre,
        ':tipo_sitio_id' => $tipo_sitio_id,
        ':estado' => $estado
    ]);

    // Si cambió de nodo padre, verificar si el anterior quedó sin hijos y actualizar `has_children`
    if ($u_padre_anterior && $u_padre_anterior != $u_padre) {
        $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM arquitectura WHERE u_padre = :u_padre_anterior AND estado = 'activo'");
        $stmtCheck->execute([':u_padre_anterior' => $u_padre_anterior]);
        $tieneHijos = $stmtCheck->fetchColumn();

        if ($tieneHijos == 0) {
            $stmtUpdate = $pdo->prepare("UPDATE arquitectura SET has_children = 0 WHERE id = :u_padre_anterior");
            $stmtUpdate->execute([':u_padre_anterior' => $u_padre_anterior]);
        }
    }

    // Actualizar `has_children` en el nuevo nodo padre
    if ($u_padre) {
        $stmtUpdate = $pdo->prepare("UPDATE arquitectura SET has_children = 1 WHERE id = :u_padre");
        $stmtUpdate->execute([':u_padre' => $u_padre]);
    }

    $_SESSION['success_message'] = '<div class="notisContent"><div class="notis" id="notis" style="text-align: center;">Sitio actualizado correctamente.</div></div>
    <script>setTimeout(() => {notis.classList.toggle("active");out();}, 1);function out() {setTimeout(() => {notis.classList.toggle("active");}, 2500);}</script>';
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit();
} catch (Exception $e) {
    $_SESSION['error_message'] = '<div class="notisContent"><div class="notiserror" id="notis">Error al actualizar el sitio: ' . htmlspecialchars($e->getMessage()) . '</div></div>
    <script>setTimeout(() => {notis.classList.toggle("active");out();}, 1);function out() {setTimeout(() => {notis.classList.toggle("active");}, 2500);}</script>';
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit();
}
?>
