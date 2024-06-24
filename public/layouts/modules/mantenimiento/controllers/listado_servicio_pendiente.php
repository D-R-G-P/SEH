<?php
// Incluir la conexión a la base de datos
require_once '../../../../../app/db/db.php';

// Crear una instancia de la clase DB
$db = new DB();
$pdo = $db->connect();

$selectServicioFilter = isset($_GET['selectServicioFilter']) ? $_GET['selectServicioFilter'] : '';

// Consulta para obtener los resultados paginados con término de búsqueda y filtro de servicio
$query = "SELECT * FROM mantenimiento WHERE estado_reclamante = 'Pendiente'";

// Si se proporciona un valor para el servicio, buscar por servicio_id
if (!empty($selectServicioFilter)) {
    $query .= " AND servicio = :selectServicioFilter";
}

$stmtTotal = $pdo->prepare($queryTotal);

if (!empty($selectServicioFilter)) {
    $stmtTotal->bindValue(':selectServicioFilter', $selectServicioFilter, PDO::PARAM_STR);
}

// Ejecutar la consulta para obtener el número total de resultados
$stmtTotal->execute();
$total_resultados = $stmtTotal->fetchColumn();