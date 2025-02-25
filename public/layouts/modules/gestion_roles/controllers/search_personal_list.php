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
$consulta = "
    SELECT SQL_CALC_FOUND_ROWS 
        p.id AS personal_id,
        p.nombre,
        p.apellido,
        p.dni,
        p.servicio_id,
        se.servicio AS servicio,
        GROUP_CONCAT(DISTINCT r.nombre ORDER BY r.id SEPARATOR ', ') AS roles,
        GROUP_CONCAT(DISTINCT s.nombre ORDER BY s.id SEPARATOR ', ') AS subroles
    FROM personal p
    LEFT JOIN servicios se ON p.servicio_id = se.id
    LEFT JOIN usuarios_roles ur ON p.dni = ur.dni
    LEFT JOIN roles r ON ur.rol_id = r.id
    LEFT JOIN usuarios_subroles us ON p.dni = us.dni
    LEFT JOIN subroles s ON us.subrol_id = s.id
    WHERE p.estado = 'Activo'";

// Añadir filtros según los parámetros
$params = [];
if ($select && $select !== "clr") {
    $consulta .= " AND p.servicio_id = :selectedService";
    $params[':selectedService'] = $select;
}

if ($search) {
    $consulta .= " AND (p.dni LIKE :searchTerm OR p.nombre LIKE :searchTerm2 OR p.apellido LIKE :searchTerm3)";
    $params[':searchTerm'] = "%$search%";
    $params[':searchTerm2'] = "%$search%";
    $params[':searchTerm3'] = "%$search%";
}

// Agrupar y ordenar la consulta con paginación
$consulta .= " GROUP BY p.id ORDER BY p.id ASC LIMIT :inicio, :regpagina";

// Preparar la consulta
$registros = $pdo->prepare($consulta);

// Bindear los valores de manera segura
foreach ($params as $key => &$value) {
    $registros->bindParam($key, $value);
}

$registros->bindParam(':inicio', $inicio, PDO::PARAM_INT);
$registros->bindParam(':regpagina', $regpagina, PDO::PARAM_INT);

// Ejecutar la consulta
$registros->execute();
$registros = $registros->fetchAll();

// Obtener el total de registros
$totalregistros = $pdo->query("SELECT FOUND_ROWS() AS total");
$totalregistros = $totalregistros->fetch()['total'];

$numeropaginas = ceil($totalregistros / $regpagina);
