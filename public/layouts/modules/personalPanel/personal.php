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


        <div class="back" id="back" style="display: none;">
            <div class="divBackForm" id="newPersonal" style="display: none;">
                <div class="close" style="width: 100%; display: flex; justify-content: flex-end; padding: .5vw">
                    <button class="btn-red" onclick="back.style.display = 'none'; newPersonal.style.display = 'none'; newPersonalForm.reset(); $('#selectServicio').val(null).trigger('change'); $('#selectEspecialidad').val(null).trigger('change'); $('#selectCargo').val(null).trigger('change'); $('#selectRol').val(null).trigger('change');" style="width: 2.3vw; height: 2.3vw;"><b><i class="fa-solid fa-xmark"></i></b></button>
                </div>
                <h3>Declarar nuevo personal</h3>
                <form action="#" method="post" class="backForm" id="newPersonalForm">
                    <div style="margin-top: 15vw;">
                        <label for="apellido">Apellido</label>
                        <input type="text" name="apellido" id="apellido" required>
                    </div>
                    <div>
                        <label for="nombre">Nombre</label>
                        <input type="text" name="nombre" id="nombre" required>
                    </div>
                    <div>
                        <label for="dni">D.N.I.</label>
                        <input type="text" name="dni" id="dni" width="100%" oninput="formatNumber(this)" required>
                    </div>
                    <div>
                        <label for="selectServicio">Servicio</label>
                        <select id="selectServicio" class="select2" name="servicio" style="width: 100%;" required onchange="selectChange()">
                            <option value="" selected disabled>Seleccionar servicio...</option>
                            <?php

                            // Realiza la consulta a la tabla servicios
                            $getServicio = "SELECT id, servicio FROM servicios WHERE estado = 'Activo'";
                            $stmtServicio = $pdo->query($getServicio);

                            // Itera sobre los resultados y muestra las filas en la tabla
                            while ($row = $stmtServicio->fetch(PDO::FETCH_ASSOC)) {
                                echo '<option value=' . $row['id'] . '>' . $row['servicio'] . '</option>';
                            }

                            ?>
                        </select>
                    </div>
                    <div>
                        <label for="selectEspecialidad">Especialidad</label>
                        <select id="selectEspecialidad" class="select2" name="especialidad" style="width: 100%;" required>
                            <option value="" selected disabled>Seleccionar especialidad...</option>
                        </select>
                    </div>
                    <div style="display: flex; flex-direction: row; justify-content: space-evenly; width: 100%;">
                        <div style="display: flex; flex-direction: column;">
                            <label for="mn">M.N.</label>
                            <input style="width: 100%;" type="number" name="mn" id="mn">
                        </div>

                        <div style="display: flex; flex-direction: column;">
                            <label for="mp">M.P.</label>
                            <input style="width: 100%;" type="number" name="mp" id="mp">
                        </div>
                    </div>
                    <div>
                        <label for="selectCargo">Cargo</label>
                        <select id="selectCargo" class="select2" name="cargo" style="width: 100%;" required>
                            <option value="" selected disabled>Seleccionar cargo...</option>
                            <?php

                            // Realiza la consulta a la tabla cargo
                            $getCargo = "SELECT cargo FROM cargos WHERE estado = 'Activo'";
                            $stmtCargo = $pdo->query($getCargo);

                            // Itera sobre los resultados y muestra las filas en la tabla
                            while ($row = $stmtCargo->fetch(PDO::FETCH_ASSOC)) {
                                echo '<option value=' . $row['cargo'] . '>' . $row['cargo'] . '</option>';
                            }

                            ?>
                        </select>
                    </div>
                    <div>
                        <label for="selectRol">Rol</label>
                        <select id="selectRol" class="select2" name="rol" style="width: 100%;" required>
                            <option value="" disabled selected>Seleccione rol...</option>
                            <option value="Administrador">Administrador</option>
                            <option value="Dirección">Dirección</option>
                            <option value="Deposito">Deposito</option>
                            <option value="Mantenimiento">Mantenimiento</option>
                            <option value="Patrimoniales">Patrimoniales</option>
                            <option value="Informatica">Informatica</option>
                            <option value="Jefe de servicio">Jefe de servicio</option>
                        </select>
                    </div>
                    <div style="display: flex; flex-direction: row; justify-content: center;">
                        <button class="btn-green"><b><i class="fa-solid fa-plus"></i> Declarar nuevo personal</b></button>
                    </div>
                </form>
            </div>

            <div class="divBackForm" id="editPersonal" style="display: none;">
                <div class="close" style="width: 100%; display: flex; justify-content: flex-end; padding: .5vw">
                    <button class="btn-red" onclick="back.style.display = 'none'; newPersonal.style.display = 'none'; newPersonalForm.reset(); $('#selectServicio').val(null).trigger('change'); $('#selectEspecialidad').val(null).trigger('change'); $('#selectCargo').val(null).trigger('change'); $('#selectRol').val(null).trigger('change');" style="width: 2.3vw; height: 2.3vw;"><b><i class="fa-solid fa-xmark"></i></b></button>
                </div>
                <h3>Declarar nuevo personal</h3>
                <form action="#" method="post" class="backForm" id="editPersonalForm">
                    <div style="margin-top: 15vw;">
                        <label for="editapellido">Apellido</label>
                        <input type="text" name="editapellido" id="editapellido" required>
                    </div>
                    <div>
                        <label for="editnombre">Nombre</label>
                        <input type="text" name="editnombre" id="editnombre" required>
                    </div>
                    <div>
                        <label for="editdni">D.N.I.</label>
                        <input type="text" name="editdni" id="editdni" width="100%" oninput="formatNumber(this)" required>
                    </div>
                    <div>
                        <label for="editselectServicio">Servicio</label>
                        <select id="editselectServicio" class="select2" name="editservicio" style="width: 100%;" required onchange="selectChange()">
                            <option value="" selected disabled>Seleccionar servicio...</option>
                            <?php

                            // Realiza la consulta a la tabla servicios
                            $getServicio = "SELECT id, servicio FROM servicios WHERE estado = 'Activo'";
                            $stmtServicio = $pdo->query($getServicio);

                            // Itera sobre los resultados y muestra las filas en la tabla
                            while ($row = $stmtServicio->fetch(PDO::FETCH_ASSOC)) {
                                echo '<option value=' . $row['id'] . '>' . $row['servicio'] . '</option>';
                            }

                            ?>
                        </select>
                    </div>
                    <div>
                        <label for="editselectEspecialidad">Especialidad</label>
                        <select id="editselectEspecialidad" class="select2" name="editespecialidad" style="width: 100%;" required>
                            <option value="" selected disabled>Seleccionar especialidad...</option>
                        </select>
                    </div>
                    <div style="display: flex; flex-direction: row; justify-content: space-evenly; width: 100%;">
                        <div style="display: flex; flex-direction: column;">
                            <label for="editmn">M.N.</label>
                            <input style="width: 100%;" type="number" name="editmn" id="editmn">
                        </div>

                        <div style="display: flex; flex-direction: column;">
                            <label for="editmp">M.P.</label>
                            <input style="width: 100%;" type="number" name="editmp" id="editmp">
                        </div>
                    </div>
                    <div>
                        <label for="editselectCargo">Cargo</label>
                        <select id="editselectCargo" class="select2" name="editcargo" style="width: 100%;" required>
                            <option value="" selected disabled>Seleccionar cargo...</option>
                            <?php

                            // Realiza la consulta a la tabla cargo
                            $getCargo = "SELECT cargo FROM cargos WHERE estado = 'Activo'";
                            $stmtCargo = $pdo->query($getCargo);

                            // Itera sobre los resultados y muestra las filas en la tabla
                            while ($row = $stmtCargo->fetch(PDO::FETCH_ASSOC)) {
                                echo '<option value=' . $row['cargo'] . '>' . $row['cargo'] . '</option>';
                            }

                            ?>
                        </select>
                    </div>
                    <div>
                        <label for="editselectRol">Rol</label>
                        <select id="editselectRol" class="select2" name="editrol" style="width: 100%;" required>
                            <option value="" disabled selected>Seleccione rol...</option>
                            <option value="Administrador">Administrador</option>
                            <option value="Dirección">Dirección</option>
                            <option value="Deposito">Deposito</option>
                            <option value="Mantenimiento">Mantenimiento</option>
                            <option value="Patrimoniales">Patrimoniales</option>
                            <option value="Informatica">Informatica</option>
                            <option value="Jefe de servicio">Jefe de servicio</option>
                        </select>
                    </div>
                    <div style="display: flex; flex-direction: row; justify-content: center;">
                        <button class="btn-green"><b><i class="fa-solid fa-plus"></i> Confirmar edición</b></button>
                    </div>
                </form>
            </div>
        </div>


        <div>
            <button class="btn-green" onclick="back.style.display = 'flex'; newPersonal.style.display = 'flex';"><b><i class="fa-solid fa-plus"></i> Declarar nuevo personal</b></button>
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
                $getPersonal = "SELECT * FROM personal WHERE estado != 'Eliminado'";
                $stmtPersonal = $pdo->query($getPersonal);

                // Itera sobre los resultados y muestra las filas en la tabla
                while ($row = $stmtPersonal->fetch(PDO::FETCH_ASSOC)) {
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
                    echo '<td>
                    
                    Editar
                    Licencias
                    Jubilacion
                    Eliminar

                    </td>';
                    echo '</tr>';
                }


                ?>
            </tbody>
        </table>
    </div>


</div>

<script src="/SGH/public/layouts/modules/personalPanel/js/personal.js"></script>
<?php require_once '../../base/footer.php'; ?>