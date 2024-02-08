<?php

require_once '../../../../app/db/db.php';
require_once '../../../../app/db/user_session.php';
require_once '../../../../app/db/user.php';
require_once '../../../../app/db/user.php';

$user = new User();
$userSession = new UserSession();
$currentUser = $userSession->getCurrentUser();
$user->setUser($currentUser);

$title = "Gestión de personal";

$db = new DB();
$pdo = $db->connect();

?>

<?php require_once '../../base/header.php'; ?>
<link rel="stylesheet" href="/SGH/public/layouts/modules/personalPanel/css/personal.css">


<div class="content">
    <div class="modulo" style="text-align: center;">
        <h3 style="margin-bottom: .5vw;">Sistema de gestión de personal</h3>
        <p>Este sistema está oreintado a la declaración del personal <br> a cargo y administración de privilegios de los mismos dentro del sistema.</p>
    </div>

    <div class="modulo">


        <div class="back" id="back" style="display: flex;">
            <div class="divBackForm" id="newPersonal">
                <div class="close" style="width: 100%; display: flex; justify-content: flex-end; padding: .5vw">
                    <button class="btn-red" onclick="back.style.display = 'none'; newPersonal.style.display = 'none'" style="width: 2.3vw; height: 2.3vw;"><b><i class="fa-solid fa-xmark"></i></b></button>
                </div>
                <h3>Declarar nuevo personal</h3>
                <form action="#" method="post" class="backForm">
                    <div style="margin-top: 15vw;">
                        <label for="apellido">Apellido</label>
                        <input type="text" name="apellido" id="apellido">
                    </div>
                    <div>
                        <label for="nombre">Nombre</label>
                        <input type="text" name="nombre" id="nombre">
                    </div>
                    <div>
                        <label for="dni">D.N.I.</label>
                        <input type="text" name="dni" id="dni" width="100%" oninput="formatNumber(this)">
                    </div>
                    <div>
                        <label for="selectServicio">Servicio</label>
                            <select id="selectServicio" class="select2" name="servicio" style="width: 100%;" required>
                                <option value="" selected disabled>Seleccionar servicio...</option>
                                <?php

                                // Realiza la consulta a la tabla servicios
                                $getPersonal = "SELECT id, servicio FROM servicios WHERE estado = 'Activo'";
                                $stmt = $pdo->query($getPersonal);

                                // Itera sobre los resultados y muestra las filas en la tabla
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    echo '<option value=' . $row['id'] . '>' . $row['servicio'] . '</option>';
                                }

                                ?>
                        </select>
                    </div>
                    <div>
                        <label for="selectEspecialidad">Especialidad</label>
                        <select id="selectEspecialidad" class="select2" name="especialidad" style="width: 100%;" required>
                            <option value="" selected disabled>Seleccionar especialidad...</option>
                            <?php

                            // Realiza la consulta a la tabla servicios
                            $getPersonal = "SELECT apellido, nombre, dni FROM personal";
                            $stmt = $pdo->query($getPersonal);

                            // Itera sobre los resultados y muestra las filas en la tabla
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo '<option value=' . $row['dni'] . '>' . $row['apellido'] . ' ' . $row['nombre'] . ' - ' . $row['dni'] . '</option>';
                            }

                            ?>
                        </select>
                    </div>
                    <div style="display: flex; flex-direction: row; justify-content: space-evenly; width: 100%;">
                        <div style="display: flex; flex-direction: column;">
                            <label for="">M.N.</label>
                            <input style="width: 100%;" type="number" name="" id="">
                        </div>

                        <div style="display: flex; flex-direction: column;">
                            <label for="">M.P.</label>
                            <input style="width: 100%;" type="number" name="" id="">
                        </div>
                    </div>
                    <div>
                        <label for="">Cargo</label>
                        <input type="text" name="" id="">
                    </div>
                    <div>
                        <label for="">Rol</label>
                        <input type="text" name="" id="">
                    </div>
                    <div style="display: flex; flex-direction: row; justify-content: center;">
                        <button class="btn-green"><b><i class="fa-solid fa-plus"></i> Declarar nuevo personal</b></button>
                    </div>
                </form>
            </div>
        </div>


        <div>
            <button class="btn-green"><b><i class="fa-solid fa-plus"></i> Declarar nuevo personal</b></button>
        </div>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre y apellido</th>
                    <th>DNI</th>
                    <th>Servicio</th>
                    <th>Especialidad</th>
                    <th>Matricula</th>
                    <th>Cargo</th>
                    <th>Sistemas</th>
                    <th>Rol</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php

                // Realiza la consulta a la tabla servicios
                $getTable = "SELECT * FROM personal WHERE estado != 'Eliminado'";
                $stmt = $pdo->query($getTable);

                // Itera sobre los resultados y muestra las filas en la tabla
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo '<tr>';
                    echo '<td class="table-center table-middle">' . $row['id'] . '</td>';
                    echo '<td class="table-middle">' . $row['apellido'] . ' ' . $row['nombre'] . '</td>';
                    echo '<td class="table-middle table-center">' . $row['dni'] . '</td>';

                    if ($row['servicio_id'] != "0") {
                        // Realiza una consulta para obtener el nombre y apellido del jefe de servicio
                        $getservicioQuery = "SELECT servicio FROM servicios WHERE id = ?";
                        $getservicioStmt = $pdo->prepare($getservicioQuery);
                        $getservicioStmt->execute([$row['servicio_id']]);
                        $servicioInfo = $getservicioStmt->fetch(PDO::FETCH_ASSOC);
                        // Muestra el nombre y apellido del jefe de servicio
                        if ($servicioInfo) {
                            echo '<td class="table-middle">' . $servicioInfo['servicio'] . '</td>';
                        } else {
                            echo '<div>No se encontró la información del servicio</div>';
                        }
                    } else {
                        echo '<td class="table-middle"> No hay servicio asignado';
                    }
                    echo '</td>';
                    
                    echo '<td class="table-middle"> ' . $row['especialidad'] . '</td>';
                    echo '<td class="table-middle"> M.N: ' . $row['mn'] . ' </br> M.P: ' . $row['mp'] . '</td>';
                    echo '<td class="table-middle"> ' . $row['cargo'] . '</td>';
                    echo '<td class="table-middle"> ' . $row['sistemas'] . '</td>';
                    echo '<td class="table-middle"> ' . $row['rol'] . '</td>';
                    echo '<td></td>';
                    echo '</tr>';
                }


                ?>
            </tbody>
        </table>
    </div>


</div>

<script src="/SGH/public/layouts/modules/personalPanel/js/personal.js"></script>
<?php require_once '../../base/footer.php'; ?>