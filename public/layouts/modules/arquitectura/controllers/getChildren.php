<?php
header('Content-Type: application/json');
include_once '../../../../../app/db/db.php'; // Archivo que contiene la conexión a la base de datos.
$db = new DB();
$pdo = $db->connect();

$u_padre = isset($_POST['u_padre']) ? $_POST['u_padre'] : null;

try {
    if ($u_padre === null || $u_padre === '') {
        // Obtener nodos raíz (sin u_padre)
        $query = $pdo->prepare('SELECT id, nombre, estado, u_hijo FROM arquitectura WHERE u_padre IS NULL');
    } else {
        // Obtener nodos hijos
        $query = $pdo->prepare('SELECT id, nombre, estado, u_hijo FROM arquitectura WHERE u_padre = :u_padre');
        $query->bindParam(':u_padre', $u_padre, PDO::PARAM_INT);
    }
    $query->execute();
    $results = $query->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($results);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
