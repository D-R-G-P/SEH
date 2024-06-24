<?php

require_once '../../../../app/db/db.php';
require_once '../../../../app/db/user_session.php';
require_once '../../../../app/db/user.php';

$user = new User();
$userSession = new UserSession();
$currentUser = $userSession->getCurrentUser();
$user->setUser($currentUser);

$title = "Mantenimiento";

$db = new DB();
$pdo = $db->connect();

$servicioFilter = $user->getServicio();

?>

<?php require_once '../../base/header.php'; ?>
<link rel="stylesheet" href="/SGH/public/layouts/modules/mantenimiento/css/mantenimiento.css">


<div class="content">

    <div class="modulo" style="text-align: center;">
        <h3>Solicitudes a mantenimiento</h3>
        <p>Este sistema está oreintado a las solicitudes a mantenimiento</p>
    </div>

    <?php
    // Función para obtener los roles por DNI
    $dni = $user->getDni();

    function getPermisosPorDni($dni)
    {
        $db = new DB();
        $pdo = $db->connect();

        $queryInst = "SELECT rol FROM personal WHERE dni = :dni";
        $stmtInst = $pdo->prepare($queryInst);
        $stmtInst->bindParam(':dni', $dni, PDO::PARAM_STR);
        $stmtInst->execute();

        // Obtener el resultado de la consulta
        $result = $stmtInst->fetch(PDO::FETCH_ASSOC);

        // Verificar si se ha obtenido algún resultado
        if ($result !== false) {
            $rolMant = $result['rol'];
        } else {
            // Manejo del caso en que no se encuentre el DNI
            return null;
        }

        $tieneRolMantenimiento = false;

        if ($rolMant == "Mantenimiento") { // Corrección del operador de comparación
            $tieneRolMantenimiento = true;
        }

        // Si el usuario tiene el permiso de "Mantenimiento", mostrar el botón
        if ($tieneRolMantenimiento) {
            echo '<div class="admInst" style="position: relative; top: -6vw; left: -29vw;">';
            echo '<a class="btn-tematico" style="text-decoration: none;" href="hsiAdmin.php"><i class="fa-solid fa-toolbox"></i> <b>Acceder a panel de mantenimiento</b></a>';
            echo '</div>';
        }

        // Cerrar la conexión
        $pdo = null;
    }

    // Llamar a la función
    getPermisosPorDni($dni);
    ?>


    <div id="back" class="back" style="display: flex;">

        <div id="newSolicitud" class="divBackForm" style="display: none;">
            <div class="close" style="width: 100%; display: flex; justify-content: flex-end; padding: .5vw">
                <button class="btn-red" onclick="back.style.display = 'none'; newSolicitud.style.display = 'none'; newSolicitudForm.reset(); $('#solicitudServicio').val(null).trigger('change');" style="width: 2.3vw; height: 2.3vw;"><b><i class="fa-solid fa-xmark"></i></b></button>
            </div>

            <h3 class="formTitle">Solicitar nuevo usuario</h3>

            <form action="/SGH/public/layouts/modules/mantenimiento/controllers/newSolicitud.php" id="newSolicitudForm" method="post" class="backForm">
                <input type="hidden" name="reclamante" value="<?php echo $user->getDni(); ?>" required>
                <div>
                    <label for="solicitudServicio">Servicio</label>
                    <select name="solicitudServicio" id="solicitudServicio" class="select2" required>
                        <?php
                        if ($user->getRol() == 'Administrador' || $user->getRol() == 'Dirección') {
                            // Si el usuario tiene el id del servicio igual a 1 o el rol es administrador, generamos todos los servicios
                            echo '<option value="" selected disabled>Seleccionar un servicio...</option>';

                            $getServicios = "SELECT id, servicio FROM servicios WHERE estado = 'Activo'";
                            $stmtServicios = $pdo->query($getServicios);

                            while ($row = $stmtServicios->fetch(PDO::FETCH_ASSOC)) {
                                echo '<option value=' . $row['id'] . '>' . $row['servicio'] . '</option>';
                            }
                        } else {
                            // Si no, generamos solo el servicio al que corresponde el usuario
                            $servicioUsuario = $user->getServicio();
                            $getServicioUsuario = "SELECT id, servicio FROM servicios WHERE id = ?";
                            $stmtServicioUsuario = $pdo->prepare($getServicioUsuario);
                            $stmtServicioUsuario->execute([$servicioUsuario]);
                            $rowServicioUsuario = $stmtServicioUsuario->fetch(PDO::FETCH_ASSOC);

                            echo '<option value="' . $rowServicioUsuario['id'] . '" selected>' . $rowServicioUsuario['servicio'] . '</option>';
                        }
                        ?>
                        <script>
                            $('#solicitudServicio').val('<?php echo $user->getServicio() ?>').trigger('change');
                        </script>
                    </select>
                </div>
                <div>
                    <label for="problema">Problema</label>
                    <textarea name="problema" id="problema" style="min-height: 7vw; resize: none; width: 100%;" required></textarea>
                </div>
                <div>
                    <label for="problema_locate">Localización del problema</label>
                    <textarea name="problema_locate" id="problema_locate" style="min-height: 7vw; resize: none; width: 100%;" required></textarea>
                </div>

                <div style="display: flex; flex-direction: row; justify-content: center;">
                    <button type="submit" class="btn-green"><b><i class="fa-solid fa-plus"></i> Realizar solicitud</b></button>
                </div>
            </form>
        </div>

        <div id="newSolicitud" class="divBackForm" style="display: flex;">
            <div class="close" style="width: 100%; display: flex; justify-content: flex-end; padding: .5vw">
                <button class="btn-red" onclick="back.style.display = 'none'; newSolicitud.style.display = 'none'; newSolicitudForm.reset(); $('#solicitudServicio').val(null).trigger('change');" style="width: 2.3vw; height: 2.3vw;"><b><i class="fa-solid fa-xmark"></i></b></button>
            </div>

            <h3 class="formTitle">Solicitud ID: 1</h3>

            <div class="caso">
                <div class="left" style="margin-right: 1vw;">
                    <table>
                        <thead>
                            <tr>
                                <th colspan="2">Descripción del caso</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="2"><b>Servicio</b></td>
                            </tr>
                            <tr>
                                <td colspan="2">DRGP</td>
                            </tr>
                            <tr>
                                <td colspan="2"><b>Localización</b></td>
                            </tr>
                            <tr>
                                <td colspan="2">Locate</td>
                            </tr>
                            <tr>
                                <td colspan="2"><b>Problema</b></td>
                            </tr>
                            <tr>
                                <td colspan="2">Problem</td>
                            </tr>
                            <tr>
                                <td><b>Estado</b></td>
                                <td>
                                    <select name="" id="">
                                        <option value="Pendiente">Pendiente</option>
                                        <option value="Cumplido">Cumplido</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2" style="text-align: center;"><b>Reclamante</b></td>
                            </tr>
                            <tr>
                                <td colspan="2" style="text-align: center;">Lamas Cristian Jonathan</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="right">
                    <table>
                        <thead>
                            <tr>
                                <th colspan="2" style="width: auto;">Intervención de mantenimiento</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="2"><b>Observación</b></td>
                            </tr>
                            <tr>
                                <td colspan="2" rowspan="4" style="height: 100%;"></td>
                            </tr>
                            <tr>
                                
                            </tr>
                            <tr>
                                
                            </tr>
                            <tr>
                                
                            </tr>
                            <tr>
                                <td><b>Estado</b></td>
                                <td>
                                    <select name="" id="">
                                        <option value="Pendiente">Pendiente</option>
                                        <option value="Cumplido">Cumplido</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">Visualizador</td>
                            </tr>
                            <tr>
                                <td colspan="2">Saraza</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

    </div>

    <div class="modulo">
        <h4>Notificaciones</h4>
        <div style="margin-top: 1vw;">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Servicio</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <th>Observaciones</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $queryNews = "SELECT * FROM mantenimiento WHERE servicio = :servicioFilter AND new_reclamante = 'si'";
                    $stmtNews = $pdo->prepare($queryNews);
                    $stmtNews->bindParam(':servicioFilter', $servicioFilter, PDO::PARAM_INT);
                    $stmtNews->execute();

                    if ($stmtNews->rowCount() == 0) {
                        // Si no hay resultados con estado 'news'
                        echo '<tr><td colspan="6" style="text-align: center;">No hay notificaciones pendientes</td></tr>';
                    } else {
                        while ($rowNews = $stmtNews->fetch(PDO::FETCH_ASSOC)) {
                            echo '<tr>';

                            echo '<td class="table-center table-middle">' . $rowNews['id'] . '</td>';

                            $stmtServicioNews = $pdo->prepare("SELECT servicio FROM servicios WHERE id = ?");
                            $stmtServicioNews->execute([$rowNews['servicio']]);
                            $rowServicioNews = $stmtServicioNews->fetch(PDO::FETCH_ASSOC);

                            if ($rowServicioNews) {
                                echo '<td class="table-center table-middle">' . $rowServicioNews['servicio'] . '</td>';
                            } else {
                                echo '<td>Error al obtener los datos</td>';
                            }

                            $date = new DateTime($rowNews['fecha']);
                            $formattedDate = $date->format('d/m/Y H:i');

                            echo '<td class="table-center table-middle">' . $formattedDate . '</td>';

                            echo '<td class="table-center table-middle">' . $rowNews['estado_reclamante'] . '</td>';

                            echo '<td class="table-center table-middle">' . $rowNews['observacion_mantenimiento'] . '</td>';

                            echo '<td class="table-center table-middle">
                <button onclick="checkNews(' . $rowNews['id'] . ')" class="btn-green" title="Marcar como visto"><i class="fa-solid fa-check"></i></button>
            </td>';

                            echo '</tr>';
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="modulo">
        <div>
            <button class="btn-green" onclick="back.style.display = 'flex'; newSolicitud.style.display = 'flex'; $('#solicitudServicio').val('<?php echo $user->getServicio() ?>').trigger('change');"><i class="fa-solid fa-plus"></i> Nueva solicitud</button>
            <select name="servicioSelect" id="servicioSelect" class="select2">
                <?php
                if ($user->getRol() == 'Administrador' || $user->getRol() == 'Dirección') {
                    // Si el usuario tiene el id del servicio igual a 1 o el rol es administrador, generamos todos los servicios
                    echo '<option value="" selected disabled>Seleccionar un servicio...</option>';

                    $getServicios = "SELECT id, servicio FROM servicios WHERE estado = 'Activo'";
                    $stmtServicios = $pdo->query($getServicios);

                    while ($row = $stmtServicios->fetch(PDO::FETCH_ASSOC)) {
                        echo '<option value=' . $row['id'] . '>' . $row['servicio'] . '</option>';
                    }
                } else {
                    // Si no, generamos solo el servicio al que corresponde el usuario
                    $servicioUsuario = $user->getServicio();
                    $getServicioUsuario = "SELECT id, servicio FROM servicios WHERE id = ?";
                    $stmtServicioUsuario = $pdo->prepare($getServicioUsuario);
                    $stmtServicioUsuario->execute([$servicioUsuario]);
                    $rowServicioUsuario = $stmtServicioUsuario->fetch(PDO::FETCH_ASSOC);

                    echo '<option value="' . $rowServicioUsuario['id'] . '" selected>' . $rowServicioUsuario['servicio'] . '</option>';
                }
                ?>
                <script>
                    $('#servicioSelect').val('<?php echo $user->getServicio() ?>').trigger('change');
                </script>
            </select>
        </div>

        <div id="tabla" style="margin-top: 1vw;">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Servicio</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <th>Problema</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="tablePen">
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- id, fecha, servicio, localizacion_explicada, problema, estado_reclamante, reclamante, observacion_mantenimiento, estado_mantenimiento, modificador_manteimiento, fecha_reclamante, fecha_mantenimiento -->



<script src="/SGH/public/layouts/modules/mantenimiento/js/mantenimiento.js"></script>
<?php require_once '../../base/footer.php'; ?>