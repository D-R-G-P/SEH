<?php

require_once '../../../../app/db/db.php';
require_once '../../../../app/db/user_session.php';
require_once '../../../../app/db/user.php';

$user = new User();
$userSession = new UserSession();
$currentUser = $userSession->getCurrentUser();
$user->setUser($currentUser);

$title = "Esquema de guardias";

$db = new DB();
$pdo = $db->connect();

$servicioFilter = $user->getServicio();

// Crear un objeto DateTime
$fecha_actual = new DateTime();

// Obtener el nombre del mes en español
$meses_espanol = array(
    'January' => 'Enero',
    'February' => 'Febrero',
    'March' => 'Marzo',
    'April' => 'Abril',
    'May' => 'Mayo',
    'June' => 'Junio',
    'July' => 'Julio',
    'August' => 'Agosto',
    'September' => 'Septiembre',
    'October' => 'Octubre',
    'November' => 'Noviembre',
    'December' => 'Diciembre'
);

$nombre_mes = $meses_espanol[$fecha_actual->format('F')];
$fechaMes = $fecha_actual->format('Y/m/01');

// Obtener el año
$anio = $fecha_actual->format('Y');

// Formatear la fecha en el formato deseado
$fecha_formateada = $nombre_mes . ' de ' . $anio;

?>

<?php require_once '../../base/header.php'; ?>
<link rel="stylesheet" href="/SGH/public/layouts/modules/guardiasPanel/css/guardias.css">

<script>
    $(document).ready(function() {
        $('#dniSelect').select2();
    });
</script>

<div class="content">

    <div class="modulo" style="text-align: center;">
        <h3>Esquemas de guardia</h3>
        <p>Este sistema está oreintado a la gestion e informe de los <br> esquemas de guardia de cada servicio</p>
    </div>

    <div class="back" style="display: flex;" id="back">

        <div class="divBackForm" id="addEspDiv">
            <div class="close" style="width: 100%; display: flex; justify-content: flex-end; padding: .5vw">
                <button class="btn-red" onclick="back.style.display = 'none'; addEspDiv.style.display = 'none'; addEspForm.reset(); $('#dniSelect').val(null).trigger('change');" style="width: 2.3vw; height: 2.3vw;"><b><i class="fa-solid fa-xmark"></i></b></button>
            </div>
            <h3 class="formTitle">Agregar especialista</h3>

            <form action="" class="backForm" id="addEspForm" method="post">

                <div>
                    <label for=""></label>
                    <input type="text" value="" id="dia" disabled>
                    <input type="hidden" name="" value="">
                </div>

                <div>
                    <label for=""></label>
                    <input type="text" value="" id="" disabled>
                    <input type="hidden" name="" value="">
                </div>

                <div>
                    <label for="dniSelect">Seleccionar agente</label>
                    <select name="dniSelect" class="select2" id="dniSelect">
                        <option value="" selected disabled>Seleccionar agente...</option>
                        <?php

                        if ($user->getRol() == "Administrador" || $user->getRol() == "Direccion") {
                            // Realiza la consulta a la tabla servicios
                            $getPersonal = "SELECT apellido, nombre, dni FROM personal";
                            $stmt = $pdo->query($getPersonal);

                            // Itera sobre los resultados y muestra las filas en la tabla
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo '<option value=' . $row['dni'] . '>' . $row['apellido'] . ' ' . $row['nombre'] . ' - ' . $row['dni'] . '</option>';
                            }
                        } else {
                            // Realiza la consulta a la tabla servicios
                            $getPersonal = "SELECT apellido, nombre, dni FROM personal WHERE servicio_id = $servicioFilter";
                            $stmt = $pdo->query($getPersonal);

                            // Itera sobre los resultados y muestra las filas en la tabla
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo '<option value=' . $row['dni'] . '>' . $row['apellido'] . ' ' . $row['nombre'] . ' - ' . $row['dni'] . '</option>';
                            }
                        }

                        ?>
                    </select>
                </div>

            </form>

        </div>

    </div>

    <div class="modulo">

        <?php
        $queryServ = "SELECT servicio FROM servicios WHERE id = :id";
        $params = array(':id' => $servicioFilter);

        // Preparar la consulta usando la conexión PDO
        $stmt = $pdo->prepare($queryServ);

        // Ejecutar la consulta
        $resultServ = $stmt->execute($params);

        // Verificar si la consulta tuvo éxito
        if ($resultServ) {
            // Obtener la fila de resultados
            $rowServ = $stmt->fetch(PDO::FETCH_ASSOC);

            // Verificar si se encontró el servicio
            if ($rowServ) {
                // Obtener el nombre del servicio
                $nombreServicio = $rowServ['servicio'];
            } else {
                // Si no se encontró el servicio, asignar un mensaje de error
                $nombreServicio = "Servicio no encontrado";
            }
        } else {
            // Si la consulta falló, asignar un mensaje de error
            $nombreServicio = "Error al consultar el servicio";
        }
        ?>

        <div><b>Servicio registro:</b> <?php echo $nombreServicio ?></div>
        <div><b>Visualizando registro: </b><?php echo $fecha_formateada; ?></div>
        <hr>

        <div class="semanaContainer">
            <button class="dia">Lunes <div class="diaCuad"><i class="fa-solid fa-calendar-day"></i></div></button>
            <button class="dia">Martes <div class="diaCuad"><i class="fa-solid fa-calendar-day"></i></div></button>
            <button class="dia">Miercoles <div class="diaCuad"><i class="fa-solid fa-calendar-day"></i></div></button>
            <button class="dia">Jueves <div class="diaCuad"><i class="fa-solid fa-calendar-day"></i></div></button>
            <button class="dia">Viernes <div class="diaCuad"><i class="fa-solid fa-calendar-day"></i></div></button>
            <button class="dia">Sabado <div class="diaCuad"><i class="fa-solid fa-calendar-day"></i></div></button>
            <button class="dia">Domingo <div class="diaCuad"><i class="fa-solid fa-calendar-day"></i></div></button>
        </div>

        <?php
        echo '<hr>';
        $diaSelected = "lunes";

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
                echo '<button class="btn-green" onclick="addEspecialista(' . $fechaMes . ', ' . $user->getDni() . ', ' . $row['especialidad'] . ', ' . $diaSelected . ')"><i class="fa-solid fa-plus"></i> Agregar especialista</button>';
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
                echo '<button class="btn-red" onclick="desafectEspecialistaWarning(' . $row['especialista'] . ')"><i class="fa-solid fa-minus"></i></button>';
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
                echo '<button class="btn-green" onclick="addEspecialista(' . $fechaMes . ', ' . $user->getDni() . ', \'' . $especialidad . '\', ' . $diaSelected . ')"><i class="fa-solid fa-plus"></i> Agregar especialista</button>';
                echo '</div>';
                echo '<div class="modulo" style="display: flex; flex-direction: row; flex-wrap: nowrap; align-items: center;">';
                echo 'No hay especialistas asignados';
                echo '</div>';
            }
        }
        ?>



    </div>

</div>

<script src="/SGH/public/layouts/modules/guardiasPanel/js/guardias.js"></script>
<?php require_once '../../base/footer.php'; ?>