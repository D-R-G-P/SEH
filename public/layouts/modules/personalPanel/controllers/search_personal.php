<?php

require_once '../../../../app/db/db.php';

$db = new DB();
$pdo = $db->connect();

// Obtener parámetros de la URL
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$select = isset($_GET['selectServicioFilter']) ? $_GET['selectServicioFilter'] : '';
$search = isset($_GET['searchInput']) ? $_GET['searchInput'] : '';

$regpagina = 10;
$inicio = ($pagina > 1) ? (($pagina * $regpagina) - $regpagina) : 0;

// Consulta base
$consulta = "SELECT SQL_CALC_FOUND_ROWS * FROM personal WHERE estado = 'Activo'";

// Añadir filtros según los parámetros
if ($select && $select !== "clr") {
    $consulta .= " AND servicio_id = :selectedService";
}

if ($search) {
    $consulta .= " AND (dni LIKE :searchTerm OR nombre LIKE :searchTerm2 OR apellido LIKE :searchTerm3)";
}

$consulta .= " ORDER BY id ASC LIMIT :inicio, :regpagina";

// Preparar la consulta
$registros = $pdo->prepare($consulta);

// Bindear las variables de forma segura
$registros->bindParam(':inicio', $inicio, PDO::PARAM_INT);
$registros->bindParam(':regpagina', $regpagina, PDO::PARAM_INT);

if ($select && $select !== "clr") {
    $registros->bindValue(':selectedService', $select, PDO::PARAM_STR);
}

if ($search) {
    $searchTerm = "%" . $search . "%";
    $registros->bindValue(':searchTerm', $searchTerm, PDO::PARAM_STR);
    $registros->bindValue(':searchTerm2', $searchTerm, PDO::PARAM_STR);
    $registros->bindValue(':searchTerm3', $searchTerm, PDO::PARAM_STR);
}

// Ejecutar la consulta
$registros->execute();
$registros = $registros->fetchAll();

// Obtener el total de registros
$totalregistros = $pdo->query("SELECT FOUND_ROWS() AS total");
$totalregistros = $totalregistros->fetch()['total'];

$numeropaginas = ceil($totalregistros / $regpagina);
