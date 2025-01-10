<?php

require_once '../../../../app/db/db.php';

$db = new DB();
$pdo = $db->connect();

// Obtener parámetros de la URL
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$select = isset($_GET['selectServicioFilter']) ? $_GET['selectServicioFilter'] : '';

$regpagina = 10;
$inicio = ($pagina > 1) ? (($pagina * $regpagina) - $regpagina) : 0;

// Consulta base con JOIN a la tabla 'servicios' para traer el nombre del servicio
$consulta = "SELECT SQL_CALC_FOUND_ROWS m.*, s.servicio AS nombre_servicio
             FROM mantenimiento m
             LEFT JOIN servicios s ON m.servicio = s.id
             WHERE m.estado_reclamante = 'Completado'"; // Filtra registros activos en mantenimiento

// Añadir filtros opcionales según los parámetros
if ($select && $select !== "clr") {
    $consulta .= " AND m.servicio = :selectedService"; // Filtro por servicio en hsi
}

if ($gestionMode) {
    if (hasSubAccess(['personal_mantenimiento'])) {
        $consulta .= " AND destino = 'mantenimiento'";
    } else if (hasSubAccess(['personal_arquitectura'])) {
        $consulta .= " AND destino = 'arquitectura'";
    } else if (hasSubAccess(['personal_informatica'])) {
        $consulta .= " AND destino = 'informatica'";
    } else if (hasSubAccess(['personal_ingenieria_clinica'])) {
        $consulta .= " AND destino = 'ingenieria_clinica'";
    } else if (hasSubAccess(['auditoria'])) {
        $consulta .= " AND destino = 'auditoria'";
    } else if (hasAccess(['administrador', 'direccion'])) {
        $consulta .= "";
    }
}

// Añadir orden y límite
$consulta .= " ORDER BY m.id ASC LIMIT :inicio, :regpagina";

// Preparar la consulta
$registros = $pdo->prepare($consulta);

// Bindear las variables de forma segura
$registros->bindParam(':inicio', $inicio, PDO::PARAM_INT);
$registros->bindParam(':regpagina', $regpagina, PDO::PARAM_INT);

if ($select && $select !== "clr") {
    $registros->bindValue(':selectedService', $select, PDO::PARAM_STR);
}

// Ejecutar la consulta
$registros->execute();
$registros = $registros->fetchAll();

// Obtener el total de registros
$totalregistros = $pdo->query("SELECT FOUND_ROWS() AS total");
$totalregistros = $totalregistros->fetch()['total'];

$numeropaginas = ceil($totalregistros / $regpagina);

?>
