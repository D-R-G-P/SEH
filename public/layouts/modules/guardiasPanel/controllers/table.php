<?php

require_once '../../../../../app/db/db.php';

$db = new DB();
$pdo = $db->connect();

$fecha_actual = new DateTime();
$fechaMes = $fecha_actual->format('Y/m/01');

// Verificar si se recibió el DNI a través de la solicitud POST
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['dia'])) {
    // Recibir el DNI enviado desde la solicitud AJAX
    $diaSelected = $_POST['dia'];
    $dniUser = $_POST['dniUser'];

    echo '<hr>';

    echo '<h3 style="width: 100%; text-align: center; text-transform: capitalize; font-size: 2vw; margin-bottom: 2vw;">' . $diaSelected . '</h3>';

    // Especialidades esperadas
    $especialidades_esperadas = array(
        'Jefe de guardia',
        'Emergentologos',
        'Cirugía General',
        'Traumatologos',
        'Neurocirujanos',
        'Clínica de admisión',
        'Clinica Salas Pab Rossi (incluye UTMO)',
        'Gastroenterología CETUS',
        'Ginecología',
        'Neonatología',
        'Nefrología',
        'Obstetricia',
        'Cardiología y UCO',
        'Cirugía Vascular',
        'UTI'
    );

    $Jefe_de_guardia = false;
    $Emergentologos = false;
    $Cirugía_General = false;
    $Traumatologos = false;
    $Neurocirujanos = false;
    $Clínica_de_admisión = false;
    $Clinica_Salas_Pab_Rossi = false;
    $Gastroenterología_CETUS = false;
    $Ginecología = false;
    $Neonatología = false;
    $Nefrología = false;
    $Obstetricia = false;
    $Cardiología_y_UCO = false;
    $Cirugía_Vascular = false;
    $UTI = false;

    // Variables para rastrear el estado de cada especialidad
    $especialidades_estado = array(
        'Jefe de guardia' => $Jefe_de_guardia,
        'Emergentologos' => $Emergentologos,
        'Cirugía General' => $Cirugía_General,
        'Traumatologos' => $Traumatologos,
        'Neurocirujanos' => $Neurocirujanos,
        'Clínica de admisión' => $Clínica_de_admisión,
        'Clinica Salas Pab Rossi (incluye UTMO)' => $Clinica_Salas_Pab_Rossi,
        'Gastroenterología CETUS' => $Gastroenterología_CETUS,
        'Ginecología' => $Ginecología,
        'Neonatología' => $Neonatología,
        'Nefrología' => $Nefrología,
        'Obstetricia' => $Obstetricia,
        'Cardiología y UCO' => $Cardiología_y_UCO,
        'Cirugía Vascular' => $Cirugía_Vascular,
        'UTI' => $UTI
    );

    // Consulta SQL para obtener los datos de guardias
    $query = "SELECT guardias.*, p.nombre AS nombre, p.apellido AS apellido 
FROM guardias 
LEFT JOIN personal AS p ON COALESCE(guardias.especialista, 'Error al obtener el dato') COLLATE utf8mb4_spanish2_ci = p.dni COLLATE utf8mb4_spanish2_ci 
WHERE dia = :diaSelected AND guardias.estado != 'desafectado'
ORDER BY 
    CASE guardias.especialidad
        WHEN 'Jefe de guardia' THEN 1
        WHEN 'Emergentologos' THEN 2
        WHEN 'Cirugía General' THEN 3
        WHEN 'Traumatologos' THEN 4
        WHEN 'Neurocirujanos' THEN 5
        WHEN 'Clínica de admisión' THEN 6
        WHEN 'Clinica Salas Pab Rossi (incluye UTMO)' THEN 7
        WHEN 'Gastroenterología CETUS' THEN 8
        WHEN 'Ginecología' THEN 9
        WHEN 'Neonatología' THEN 10
        WHEN 'Nefrología' THEN 11
        WHEN 'Obstetricia' THEN 12
        WHEN 'Cardiología y UCO' THEN 13
        WHEN 'Cirugía Vascular' THEN 14
        WHEN 'UTI' THEN 15
        ELSE 16
    END
";

    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':diaSelected', $diaSelected);
    $stmt->execute();

    // Variable para rastrear la especialidad actual
    $currentSpecialty = null;

    // Iterar sobre las filas de resultados
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Verificar si la especialidad actual es diferente a la especialidad en la fila actual
        if ($row['especialidad'] != $currentSpecialty) {
            // Imprimir el título de la especialidad si ha cambiado
            echo '<div class="modulo" style="width: 70%; display: flex; flex-direction: row; justify-content: space-between; align-items: center;">';
            echo '<h3>' . $row['especialidad'] . '</h3>';
            echo '<button class="btn-green" onclick="addEspecialista(\'' . htmlspecialchars($fechaMes) . '\', \'' . htmlspecialchars($dniUser) . '\', \'' . htmlspecialchars($row['especialidad']) . '\', \'' . htmlspecialchars($diaSelected) . '\')"><i class="fa-solid fa-plus"></i> Agregar especialista</button>';

            echo '</div>';
            // Actualizar la especialidad actual
            $currentSpecialty = $row['especialidad'];

            // Verificar si hay especialistas asignados para esta especialidad
            $especialista_asignado = false;
        }
        // Imprimir los detalles del especialista
        if (!empty($row['apellido']) && !empty($row['nombre'])) {
            echo '<div class="modulo" style="display: flex; flex-direction: row; flex-wrap: nowrap; align-items: center;">';
            echo '<div><b style="margin-bottom: -1.5vw;">';
            echo $row['apellido'] . ' ' . $row['nombre'] . '</b></br>';
            echo $row['especialista'] . ' - Regimen de ' . $row['regimen'] . ' hs.</div>';
            echo '<button class="btn-red" onclick="desafectEspecialistaWarning(\'' . $row['apellido'] . '\', \'' . $row['nombre'] . '\', \'' . $row['especialista'] . '\', \'' . $row['dia'] . '\', \'' . $row['id'] . '\')"><i class="fa-solid fa-minus"></i></button>';
            echo '</div>';
            // Si hay al menos un especialista asignado, actualiza la bandera
            $especialista_asignado = true;
        }

        // Actualizar el estado de la especialidad actual
        $especialidades_estado[$row['especialidad']] = true;
    }

    // Verificar si no se encontraron especialistas para alguna especialidad y mostrar un mensaje en ese caso
    foreach ($especialidades_estado as $especialidad => $estado) {
        if (!$estado) {
            echo '<div class="modulo" style="width: 70%; display: flex; flex-direction: row; justify-content: space-between; align-items: center;">';
            echo '<h3>' . $especialidad . '</h3>';
            echo '<button class="btn-green" onclick="addEspecialista(\'' . htmlspecialchars($fechaMes) . '\', \'' . htmlspecialchars($dniUser) . '\', \'' . htmlspecialchars($especialidad) . '\', \'' . htmlspecialchars($diaSelected) . '\')"><i class="fa-solid fa-plus"></i> Agregar especialista</button>';
            echo '</div>';
            echo '<div class="modulo" style="display: flex; flex-direction: row; flex-wrap: nowrap; align-items: center;">';
            echo 'No hay especialistas asignados';
            echo '</div>';
        }
    }
}
?>
