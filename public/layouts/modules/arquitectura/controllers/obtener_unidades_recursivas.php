<?php
header('Content-Type: application/json');
include_once '../../../../../app/db/db.php'; // Conexión a la DB

$db = new DB();
$pdo = $db->connect();

// Obtener el parámetro u_padre del frontend
$u_padre = isset($_POST['u_padre']) && $_POST['u_padre'] !== 'null' ? $_POST['u_padre'] : null;

// Preparar la consulta para obtener las unidades hijas
$query = "SELECT id, nombre, has_children FROM arquitectura WHERE " . ($u_padre === null ? "u_padre IS NULL" : "u_padre = :u_padre") . " AND estado = 'activo'";

$stmt = $pdo->prepare($query);
if ($u_padre !== null) {
    $stmt->bindParam(':u_padre', $u_padre, PDO::PARAM_INT);
}
$stmt->execute();

$unidades = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($unidades);
?>