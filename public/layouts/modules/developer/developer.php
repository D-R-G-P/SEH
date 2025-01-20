<?php

require_once '../../../../app/db/db.php';
require_once '../../../../app/db/user_session.php';
require_once '../../../../app/db/user.php';
require_once '../../../config.php';


$user = new User();
$userSession = new UserSession();
$currentUser = $userSession->getCurrentUser();
$user->setUser($currentUser);

requireRole(['developer']);

$title = "Developer";

$db = new DB();
$pdo = $db->connect();

?>

<?php require_once '../../base/header.php'; ?>
<link rel="stylesheet" href="/SGH/public/layouts/modules/developer/css/developer.css">

<div class="content">

    <div class="modulo" style="text-align: center;">
        <h3>Desarrollo</h3>
        <p>Sistema con acceso a las herramientas de desarrollo.</p>
    </div>

    <div class="back" id="back">

        <div class="divBackForm" id="vistaModulo" style="display: none;">
            <div class="close" style="width: 100%; display: flex; justify-content: flex-end; padding: .5vw">
                <button class="btn-red"
                    onclick="document.getElementById('back').style.display = 'none'; document.getElementById('vistaModulo').style.display = 'none'; moduloNew.reset();"
                    style="width: 2.3vw; height: 2.3vw;"><b><i class="fa-solid fa-xmark"></i></b></button>
            </div>
            <h3 class="formTitle">Nuevo módulo</h3>

            <form action="controllers/modulo.php" method="post" class="backForm" id="moduloNew">

                <input type="hidden" name="id" id="modulo_id">

                <div>
                    <label for="modulo">Nombre del módulo</label>
                    <input type="text" name="modulo" id="modulo">
                </div>

                <div>
                    <label for="descripcion">Descripción</label>
                    <textarea name="descripcion" id="descripcion"
                        style="width: 95%; height: 10vw; resize: none;"></textarea>
                </div>

                <div id="modulo_estado_div" style="display: none;">
                    <span>Estado:</span>
                    <div style="display: flex; flex-direction: row; justify-content: center; width: 95%" class="modulo">
                        <div style="display: flex; flex-direction: row; width: fit-content;">
                            <input type="radio" name="modulo_estado" id="modulo_estado_activo" value="Activo">
                            <label for="modulo_estado_activo">Activo</label>
                        </div>
                        <div style="display: flex; flex-direction: row; width: fit-content; margin-left: 1vw">
                            <input type="radio" name="modulo_estado" id="modulo_estado_inactivo" value="Inactivo">
                            <label for="modulo_estado_inactivo">Inactivo</label>
                        </div>
                    </div>
                </div>

                <div style="display: flex; align-items: center;">
                    <button class="btn-green" type="submit" style="width: max-content;">
                        <i class="fa-solid fa-floppy-disk"></i><b>Guardar modulo</b>
                    </button>
                </div>

            </form>

        </div>

        <div class="divBackForm" id="vistaRol" style="display: none;">
            <div class="close" style="width: 100%; display: flex; justify-content: flex-end; padding: .5vw">
                <button class="btn-red"
                    onclick="document.getElementById('back').style.display = 'none'; document.getElementById('vistaRol').style.display = 'none'; newRol.reset(); $('#modulo_rol_select').val(null).trigger('change');"
                    style="width: 2.3vw; height: 2.3vw;"><b><i class="fa-solid fa-xmark"></i></b></button>
            </div>
            <h3 class="formTitle">Nuevo rol</h3>

            <form action="controllers/rol.php" method="post" class="backForm" id="newRol">

                <input type="hidden" id="rol_id" name="id">

                <div>
                    <label for="rol_rol">Rol</label>
                    <input type="text" name="rol" id="rol_rol" class="singleWord">
                </div>

                <div>
                    <label for="rol_name">Nombre del rol</label>
                    <input type="text" name="rol_name" id="rol_name">
                </div>

                <div>
                    <label for="modulo_rol_select">Modulo al que pertenece</label>
                    <select name="modulo_rol_select" id="modulo_rol_select" class="select2" style="width: 95%;">
                        <option value="" disabled selected>Seleccionar una opción</option>
                        <option value="">Sin modulo asociado</option>

                        <?php

                        $getModulos = "SELECT * FROM modulos";
                        $stmtModulos = $pdo->query($getModulos);

                        while ($rowModulos = $stmtModulos->fetch(PDO::FETCH_ASSOC)) {
                            echo '<option value=' . $rowModulos['id'] . '>' . $rowModulos['modulo'] . '</option>';
                        }

                        ?>
                    </select>
                </div>

                <div>
                    <label for="rol_descripcion">Descripción</label>
                    <textarea name="descripcion" id="rol_descripcion"
                        style="width: 95%; height: 10vw; resize: none;"></textarea>
                </div>

                <div id="rol_estado_div" style="display: none;">
                    <span>Estado:</span>
                    <div style="display: flex; flex-direction: row; justify-content: center; width: 95%" class="modulo">
                        <div style="display: flex; flex-direction: row; width: fit-content;">
                            <input type="radio" name="rol_estado" id="rol_estado_activo" value="Activo">
                            <label for="rol_estado_activo">Activo</label>
                        </div>
                        <div style="display: flex; flex-direction: row; width: fit-content; margin-left: 1vw">
                            <input type="radio" name="rol_estado" id="rol_estado_inactivo" value="Inactivo">
                            <label for="rol_estado_inactivo">Inactivo</label>
                        </div>
                    </div>
                </div>

                <div style="display: flex; align-items: center;">
                    <button class="btn-green" type="submit" style="width: max-content;">
                        <i class="fa-solid fa-floppy-disk"></i>
                        <b>Guardar rol</b>
                    </button>
                </div>

            </form>

        </div>

        <div class="divBackForm" id="vistaSubrol" style="display: none;">
            <div class="close" style="width: 100%; display: flex; justify-content: flex-end; padding: .5vw">
                <button class="btn-red"
                    onclick="document.getElementById('back').style.display = 'none'; document.getElementById('vistaSubrol').style.display = 'none'; newSubrol.reset(); $('#modulo_subrol_select').val(null).trigger('change'); $('#rol_subrol_select').val(null).trigger('change');"
                    style="width: 2.3vw; height: 2.3vw;"><b><i class="fa-solid fa-xmark"></i></b></button>
            </div>
            <h3 class="formTitle">Nuevo rol</h3>

            <form action="controllers/subrol.php" method="post" class="backForm" id="newSubrol">

                <input type="hidden" name="id" id="subrol_id">

                <div>
                    <label for="subrol">Subrol</label>
                    <input type="text" name="subrol" id="subrol" class="singleWord">
                </div>

                <div>
                    <label for="subrol_name">Nombre del subrol</label>
                    <input type="text" name="subrol_name" id="subrol_name">
                </div>

                <div>
                    <label for="modulo_subrol_select">Módulo al que pertenece</label>
                    <select name="modulo_subrol_select" id="modulo_subrol_select" class="select2" style="width: 95%;"
                        required>
                        <option value="" disabled selected>Seleccionar una opción</option>
                        <option value="">Sin módulo asociado</option>
                        <?php
                        $getModulos = "SELECT * FROM modulos";
                        $stmtModulos = $pdo->query($getModulos);

                        while ($rowModulos = $stmtModulos->fetch(PDO::FETCH_ASSOC)) {
                            echo '<option value="' . $rowModulos['id'] . '">' . $rowModulos['modulo'] . '</option>';
                        }
                        ?>
                    </select>
                </div>

                <div>
                    <label for="rol_subrol_select">Seleccionar rol</label>
                    <select name="rol_subrol_select" id="rol_subrol_select" style="width: 95%;" disabled required>
                        <option value="" disabled selected>Seleccionar una opción</option>
                    </select>
                </div>


                <div>
                    <label for="subDesc">Descripción</label>
                    <textarea name="subDesc" id="subDesc" style="width: 95%; height: 8vw; resize: none;"></textarea>
                </div>

                <div id="subrol_estado_div" style="display: none;">
                    <span>Estado:</span>
                    <div style="display: flex; flex-direction: row; justify-content: center; width: 95%" class="modulo">
                        <div style="display: flex; flex-direction: row; width: fit-content;">
                            <input type="radio" name="subrol_estado" id="subrol_estado_activo" value="Activo">
                            <label for="subrol_estado_activo">Activo</label>
                        </div>
                        <div style="display: flex; flex-direction: row; width: fit-content; margin-left: 1vw">
                            <input type="radio" name="subrol_estado" id="subrol_estado_inactivo" value="Inactivo">
                            <label for="subrol_estado_inactivo">Inactivo</label>
                        </div>
                    </div>
                </div>

                <div style="display: flex; align-items: center;">
                    <button class="btn-green" type="submit" style="width: max-content;">
                        <i class="fa-solid fa-floppy-disk"></i>
                        <b>Guardar subrol</b>
                    </button>
                </div>

            </form>

        </div>

        <div class="divBackForm" id="newUpdate" style="display: none;">
            <div class="close" style="width: 100%; display: flex; justify-content: flex-end; padding: .5vw">
                <button class="btn-red"
                    onclick="document.getElementById('back').style.display = 'none'; document.getElementById('newUpdate').style.display = 'none'; newUpdateForm.reset();"
                    style="width: 2.3vw; height: 2.3vw;"><b><i class="fa-solid fa-xmark"></i></b></button>
            </div>
            <h3 class="formTitle">Nuevo update</h3>

            <form action="controllers/update.php" class="backForm" method="post">
                <div>
                    <label for="version">Versión</label>
                    <input type="text" name="version" id="version"
                        placeholder="Ultimo update: <?php echo getLastUpdate(); ?>">
                </div>

                <div>
                    <label for="descripcion">Descripción del update</label>
                    <textarea name="descripcion" id="descripcion"
                        style="width: 95%; height: 20vw; resize: none;"></textarea>
                </div>

                <button class="btn-green" type="submit"><i class="fa-solid fa-plus"></i> Nuevo update</button>
            </form>
        </div>

    </div>

    <div class="modulo">
        <h4>Modulos</h4>

        <button class="btn-green" onclick="vistaModulo();" style="width: max-content;"><i class="fa-solid fa-plus"></i>
            <b>Nuevo modulo</b></button>

        <div class="tabla_modulos">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Modulo</th>
                        <th>Descripción</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $modulos_consulta = "SELECT * FROM modulos";
                    $modulos = $pdo->query($modulos_consulta)->fetchAll(PDO::FETCH_ASSOC);

                    if (empty($modulos)) {
                        echo '<tr><td colspan="5">No hay módulos registrados</td></tr>';
                    } else {
                        foreach ($modulos as $modulo): ?>
                            <tr>
                                <td class="table-center table-middle"><?= $modulo['id'] ?></td>
                                <td class="table-middle"><?= htmlspecialchars($modulo['modulo']) ?></td>
                                <td class="table-middle"><?= htmlspecialchars($modulo['descripcion']) ?></td>
                                <td class="table-middle table-center"><?= htmlspecialchars($modulo['estado']) ?></td>
                                <td class="table-middle table-center">
                                    <button class="btn-green" onclick="vistaModulo(<?= htmlspecialchars($modulo['id']) ?>)">
                                        <i class="fa-solid fa-hand-pointer"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach;
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="modulo">
        <h4>Roles</h4>

        <button class="btn-green" onclick="vistaRol();" style="width: max-content;"><i class="fa-solid fa-plus"></i>
            <b>Nuevo rol</b></button>

        <div class="tabla_roles">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Rol</th>
                        <th>Nombre</th>
                        <th>Modulo</th>
                        <th>Descripción</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $roles_consulta = "SELECT r.*, m.modulo FROM roles r LEFT JOIN modulos m ON r.modulo = m.id";
                    $roles = $pdo->query($roles_consulta)->fetchAll(PDO::FETCH_ASSOC);

                    if (empty($roles)) {
                        echo '<tr><td colspan="7">No hay roles registrados</td></tr>';
                    } else {
                        foreach ($roles as $rol): ?>
                            <tr>
                                <td class="table-center table-middle"><?= $rol['id'] ?></td>
                                <td class="table-middle"><?= htmlspecialchars($rol['role']) ?></td>
                                <td class="table-middle"><?= htmlspecialchars($rol['nombre']) ?></td>
                                <td class="table-middle">
                                    <?= empty($rol['modulo']) ? 'No asociado a módulo' : htmlspecialchars($rol['modulo']) ?>
                                </td>
                                <td class="table-middle"><?= htmlspecialchars($rol['descripcion']) ?></td>
                                <td class="table-middle table-center"><?= htmlspecialchars($rol['estado']) ?></td>
                                <td class="table-middle table-center" onclick="vistaRol(<?= htmlspecialchars($rol['id']) ?>)">
                                    <button class="btn-green">
                                        <i class="fa-solid fa-hand-pointer"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach;
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="modulo">
        <div class="tabla_subroles">
            <h4>Subroles</h4>

            <button class="btn-green" onclick="vistaSubrol();" style="width: max-content;"><i
                    class="fa-solid fa-plus"></i> <b>Nuevo subrol</b></button>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Rol</th>
                        <th>Subrol</th>
                        <th>Nombre</th>
                        <th>Modulo</th>
                        <th>Descripción</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $subroles_consulta = "SELECT sr.*, r.nombre AS rol_nombre, m.modulo AS modulo_nombre  FROM subroles sr LEFT JOIN modulos m ON sr.modulo = m.id LEFT JOIN roles r ON sr.rol_id = r.id";
                    $subroles = $pdo->query($subroles_consulta)->fetchAll(PDO::FETCH_ASSOC);

                    if (empty($subroles)) {
                        echo '<tr><td colspan="8">No hay subroles registrados</td></tr>';
                    } else {
                        foreach ($subroles as $subrol): ?>
                            <tr>
                                <td class="table-center table-middle"><?= $subrol['id'] ?></td>
                                <td class="table-center table-middle"><?= htmlspecialchars($subrol['rol_nombre']) ?></td>
                                <td class="table-center table-middle"><?= htmlspecialchars($subrol['subrol']) ?></td>
                                <td class="table-center table-middle"><?= htmlspecialchars($subrol['nombre']) ?></td>
                                <td class="table-center table-middle">
                                    <?= empty($subrol['modulo_nombre']) ? 'No asociado a módulo' : htmlspecialchars($subrol['modulo_nombre']) ?>
                                </td>
                                <td class="table-middle"><?= htmlspecialchars($subrol['descripcion']) ?></td>
                                <td class="table-center table-middle"><?= htmlspecialchars($subrol['estado']) ?></td>
                                <td class="table-middle table-center">
                                    <button class="btn-green" onclick="vistaSubrol(<?= htmlspecialchars($subrol['id']) ?>)">
                                        <i class="fa-solid fa-hand-pointer"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach;
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="modulo" id="tabla_versions">
        <div class="tabla_versiones">
            <h4>Versiones</h4>

            <button class="btn-green" onclick="back.style.display = 'flex'; newUpdate.style.display = 'flex';"
                style="width: max-content;"><i class="fa-solid fa-plus"></i> <b>Nueva versión</b></button>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Version</th>
                        <th>Fecha</th>
                        <th>Descripcion</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $version_consulta = "SELECT * FROM updates";
                    $versiones = $pdo->query($version_consulta)->fetchAll(PDO::FETCH_ASSOC);

                    if (empty($versiones)) {
                        echo '<tr><td colspan="8">No hay versiones registradas</td></tr>';
                    } else {
                        foreach ($versiones as $version): ?>
                            <tr>
                                <td class="table-center table-middle"><?= $version['id'] ?></td>
                                <td class="table-center table-middle"><?= htmlspecialchars($version['version']) ?></td>
                                <td class="table-center table-middle">
                                    <span style="display: flex; width: max-content;"><?php

                                    echo date('d/m/Y', strtotime($version['fecha']));
                                    ?></span>
                                </td>
                                <td class="table-middle"><?= htmlspecialchars($version['descripcion']) ?></td>
                                <td class="table-middle table-center">
                                    <button class="btn-red" onclick="location.href='controllers/delete_new.php?id=<?= $version['id']; ?>';">
                                    <i class="fa-solid fa-trash"></i></i>
                                    </buton>
                                </td>
                            </tr>
                        <?php endforeach;
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script src="/SGH/public/layouts/modules/developer/js/developer.js"></script>
<?php require_once '../../base/footer.php'; ?>