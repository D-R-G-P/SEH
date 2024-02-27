<?php

// Realiza la conexión a la base de datos (utiliza tu propia lógica para la conexión)
require_once '../../../../../app/db/db.php';

$db = new DB();
$pdo = $db->connect();

if (isset($_GET['servicioId'])) {
    $servicioId = $_GET['servicioId'];

    // Realiza la consulta SQL para obtener las especialidades correspondientes al servicio seleccionado
    $getEspecialidades = "SELECT especialidad, servicio_id FROM especialidades WHERE servicio_id = ?";
    $stmtEspecialidades = $pdo->prepare($getEspecialidades);
    $stmtEspecialidades->execute([$servicioId]);

    // Construye las opciones del select con la opción predeterminada y las especialidades obtenidas de la consulta
    $options = '<option value="" selected disabled>Seleccionar especialidad...</option>';
    while ($row = $stmtEspecialidades->fetch(PDO::FETCH_ASSOC)) {
        $options .= '<option value="' . $row['especialidad'] . '">' . $row['especialidad'] . '</option>';
    }

    // Devuelve las opciones como respuesta AJAX
    echo $options;
} else {
    // Si no se recibió el servicioId, devuelve un mensaje de error
    echo 'Error: No se recibió el servicioId.';
}
