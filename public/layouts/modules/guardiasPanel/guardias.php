<?php

require_once '../../../../app/db/db.php';
require_once '../../../../app/db/user_session.php';
require_once '../../../../app/db/user.php';
require_once '../../../config.php';

$user = new User();
$userSession = new UserSession();
$currentUser = $userSession->getCurrentUser();
$user->setUser($currentUser);

requireRole(['administrador', 'direccion', 'guardias']);

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

    function addEspecialista(fecha, asignante, especialidad, dia) {

        back.style.display = 'flex';
        addEspDiv.style.display = 'flex';
        fechaHidden.value = fecha;
        asignanteHidden.value = asignante;
        diaText.value = dia;
        diaHidden.value = dia;
        especialidadText.value = especialidad;
        especialidadHidden.value = especialidad;
    }

    function desafectEspecialistaWarning(apellido, nombre, especialista, dia, id) {
        document.getElementById('especialistaWarn').innerHTML = apellido + ' ' + nombre;
        document.getElementById('documentoWarn').innerHTML = especialista;
        document.getElementById('diaWarn').innerHTML = dia;
        document.getElementById('desafecButton').setAttribute('href', '/SGH/public/layouts/modules/guardiasPanel/controllers/desafectar.php?id=' + id);
        document.getElementById('back').style.display = "flex";
        document.getElementById('alert').style.display = 'block';
    }
</script>

<div class="content">

    <div class="modulo" style="text-align: center;">
        <h3>Esquemas de guardia</h3>
        <p>Este sistema está oreintado a la gestion e informe de los <br> esquemas de guardia de cada servicio</p>
    </div>

    <div class="back" id="back">

        <div class="divBackForm" id="addEspDiv" style="display: none;">
            <div class="close" style="width: 100%; display: flex; justify-content: flex-end; padding: .5vw">
                <button class="btn-red" onclick="back.style.display = 'none'; addEspDiv.style.display = 'none'; addEspForm.reset(); $('#dniSelect').val(null).trigger('change');" style="width: 2.3vw; height: 2.3vw;"><b><i class="fa-solid fa-xmark"></i></b></button>
            </div>
            <h3 class="formTitle">Agregar especialista</h3>

            <form action="/SGH/public/layouts/modules/guardiasPanel/controllers/new_especialista.php" class="backForm" id="addEspForm" method="post">

                <input type="hidden" id="fechaHidden" name="fecha" required>
                <input type="hidden" id="asignanteHidden" name="asignante" required>
                <div>
                    <label for="diaText">Día</label>
                    <input type="text" id="diaText" disabled>
                    <input type="hidden" id="diaHidden" name="dia" required>
                </div>

                <div>
                    <label for="especialidadText">Especialidad</label>
                    <input type="text" id="especialidadText" disabled>
                    <input type="hidden" id="especialidadHidden" name="especialidad" required>
                </div>

                <div>
                    <label for="dniSelect">Seleccionar agente</label>
                    <select name="dniSelect" class="select2" id="dniSelect" style="width: 95%;" required>
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

                <div>
                    <div>Regimen</div>
                    <div id="regimen" style="display: flex; flex-direction: row;">
                        <div style="display: flex;
    flex-direction: row;
    justify-content: center;
    align-items: center;">
                            <input type="radio" id="doce" name="regimen" style="width: 2vw; margin-right: 1vw;" value="12" required></input>
                            <label for="doce">12 horas</label>
                        </div>

                        <div style="display: flex;
    flex-direction: row;
    justify-content: center;
    align-items: center;">
                            <input type="radio" id="veinticuatro" name="regimen" style="width: 2vw; margin-right: 1vw;" value="24" required></input>
                            <label for="veinticuatro">24 horas</label>
                        </div>
                    </div>
                </div>

                <button class="btn-green"><i class="fa-solid fa-plus"></i> Agregar especialista</button>

            </form>

        </div>



        <div class="alert modulo" id="alert" style="width: 40%; text-align: center; display: none; background-color: #ededed;">
            <h2>¡Atención!</h2>
            <p>Esta por desafectar a un especialista del listado, esta acción es irreversible</p>

            <div class="modulo" style="margin-top: .8vw; width: 100%; background-color: #e7e4e4;">
                <div style="display: flex; flex-direction: row;"><b style="margin-right: .3vw;">Especialista: </b>
                    <p id="especialistaWarn"></p>
                </div>
                <div style="display: flex; flex-direction: row;"><b style="margin-right: .3vw;">D.N.I: </b>
                    <p id="documentoWarn"></p>
                </div>
                <div style="display: flex; flex-direction: row;"><b style="margin-right: .3vw;">Día desafectado: </b>
                    <p id="diaWarn"></p>
                </div>
            </div>

            <div>
                <button class="btn-green" onclick="back.style.display = 'none'; document.getElementById('alert').style.display = 'none';"><i class="fa-solid fa-x"></i> Cancelar</button>
                <a class="btn-red" id="desafecButton" href="" style="text-decoration: none;"><i class="fa-solid fa-user-minus"></i> Desafectar</a>
            </div>
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
            <button class="dia" onclick="setDia('Lunes', '<?php echo $user->getDni(); ?>')">Lunes <div class="diaCuad"><i class="fa-solid fa-calendar-day"></i></div></button>
            <button class="dia" onclick="setDia('Martes', '<?php echo $user->getDni(); ?>')">Martes <div class="diaCuad"><i class="fa-solid fa-calendar-day"></i></div></button>
            <button class="dia" onclick="setDia('Miércoles', '<?php echo $user->getDni(); ?>')">Miércoles <div class="diaCuad"><i class="fa-solid fa-calendar-day"></i></div></button>
            <button class="dia" onclick="setDia('Jueves', '<?php echo $user->getDni(); ?>')">Jueves <div class="diaCuad"><i class="fa-solid fa-calendar-day"></i></div></button>
            <button class="dia" onclick="setDia('Viernes', '<?php echo $user->getDni(); ?>')">Viernes <div class="diaCuad"><i class="fa-solid fa-calendar-day"></i></div></button>
            <button class="dia" onclick="setDia('Sábado', '<?php echo $user->getDni(); ?>')">Sábado <div class="diaCuad"><i class="fa-solid fa-calendar-day"></i></div></button>
            <button class="dia" onclick="setDia('Domingo', '<?php echo $user->getDni(); ?>')">Domingo <div class="diaCuad"><i class="fa-solid fa-calendar-day"></i></div></button>
        </div>

        <div id="tabla"></div>

        <script>
            function setDia(dia, dniUser) {
                var xhttp = new XMLHttpRequest();
                xhttp.onreadystatechange = function() {
                    if (this.readyState == 4 && this.status == 200) {
                        document.getElementById("tabla").innerHTML = this.responseText;
                    }
                };
                xhttp.open("POST", "controllers/table.php", true);
                xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                xhttp.send("dia=" + dia + "&dniUser=" + dniUser);
            }
        </script>

    </div>

</div>

<script src="/SGH/public/layouts/modules/guardiasPanel/js/guardias.js"></script>
<?php require_once '../../base/footer.php'; ?>