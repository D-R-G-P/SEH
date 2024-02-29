<?php

// Realiza la conexión a la base de datos (utiliza tu propia lógica para la conexión)
require_once '../../../../../app/db/db.php';

// Obtén el DNI enviado desde la solicitud AJAX
$dni = $_GET['dni']; // Cambiado a $_GET para obtener el parámetro de la URL

// Realiza la consulta a la base de datos para verificar si el DNI está asociado a un jefe de servicio
$db = new DB();
$pdo = $db->connect();

try {
    // Realiza la consulta SQL para verificar si el DNI está asociado a un jefe de servicio
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM servicios WHERE jefe = ?");
    $stmt->execute([$dni]);
    
    // Obtiene el resultado de la consulta
    $count = $stmt->fetchColumn();

    // Verifica si el DNI está asociado a un jefe de servicio
    if ($count > 0) {
        // Si está asociado, devuelve "true"
        echo 'true';
    } else {
        // Si no está asociado, devuelve "false"
        echo 'false';
    }
} catch (PDOException $e) {
    // Si hay un error en la base de datos, devuelve un mensaje de error
    echo 'error';
}