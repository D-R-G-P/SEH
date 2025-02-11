<?php

require_once '../../../../app/db/db.php';
require_once '../../../../app/db/user_session.php';
require_once '../../../../app/db/user.php';
require_once '../../../config.php';
require_once '../../../resources/unidades/generar_selector_unidades.php';

$user = new User();
$userSession = new UserSession();
$currentUser = $userSession->getCurrentUser();
$user->setUser($currentUser);

requireRole(['administrador', 'direccion', 'arquitectura']);

$title = "Arquitectura";

$db = new DB();
$pdo = $db->connect();

$servicioFilter = $user->getServicio();

?>

<?php require_once '../../base/header.php'; ?>
<link rel="stylesheet" href="css/arquitectura.css">
<div class="content">

    <div class="modulo" style="text-align: center;">
        <h3>Arquitectura</h3>
        <p>Este módulo está destinado a gestiones del servicio y <br> definir los espacios físicos del hospital para
            distintos <br> usos dentro del sistema.</p>
    </div>

    <div class="back" id="back" style="display: none;">
        <div class="divBackForm" id="arquitectura_new" style="display: none;">
            <div class="close" style="width: 100%; display: flex; justify-content: flex-end; padding: .5vw">
                <button class="btn-red" onclick="cerrarFormulario()" style="width: 2.3vw; height: 2.3vw;"><b><i
                            class="fa-solid fa-xmark"></i></b></button>
            </div>
            <h3 class="formTitle">Nuevo sitio</h3>
            <span style="color: red;" class="formTitle"><b>*</b> Campos obligatorios</span>

            <form action="controllers/arqui_new.php" method="post" class="backForm" id="arquiNew">


                <div style="margin-top: 0vw">
                    <label for="nombre">Nombre del sitio <b style="color: red;">*</b></label>
                    <input type="text" name="nombre" id="nombre" oninput="lettering(this)" required>
                </div>

                <div>
                    <label for="servicio">Servicio</label>
                    <select name="servicio" id="servicio" class="select2" style="width: 95%;">
                        <option value=""></option>
                        <?php
                        $sql = "SELECT * FROM servicios";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute();
                        $servicios = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($servicios as $servicio) {
                            echo "<option value='" . $servicio['id'] . "'>" . $servicio['servicio'] . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <div>
                    <label for="tipo">Tipo de sitio <b style="color: red;">*</b></label>
                    <select name="tipo" id="tipo" class="select2" required style="width: 95%;">
                        <option value=""></option>
                        <?php
                        // Consulta para obtener los grupos de sitios
                        $sql_grupos = "SELECT * FROM tipo_sitio_grupo";
                        $stmt_grupos = $pdo->prepare($sql_grupos);
                        $stmt_grupos->execute();
                        $grupos = $stmt_grupos->fetchAll(PDO::FETCH_ASSOC);

                        // Iterar sobre cada grupo
                        foreach ($grupos as $grupo) {
                            echo '<optgroup label="' . htmlspecialchars($grupo['nombre']) . '">';

                            // Consulta para obtener los sitios de este grupo
                            $sql_sitios = "SELECT * FROM tipo_sitio WHERE grupo_id = :grupo_id";
                            $stmt_sitios = $pdo->prepare($sql_sitios);
                            $stmt_sitios->execute(['grupo_id' => $grupo['id']]);
                            $sitios = $stmt_sitios->fetchAll(PDO::FETCH_ASSOC);

                            // Iterar sobre los sitios de este grupo
                            foreach ($sitios as $sitio) {
                                echo '<option value="' . $sitio['id'] . '">' . htmlspecialchars($sitio['nombre']) . '</option>';
                            }

                            echo '</optgroup>';
                        }

                        ?>
                    </select>
                </div>

                <div id="recorrido-container"></div>
                <div id="selector-container"></div>
                <script>
                    $(document).ready(function () {
                        actualizarUnidades(null, 'selector-container', 'recorrido-container');
                    });
                </script>

                <div>
                    <label for="observaciones">Observaciones</label>
                    <textarea name="observaciones" id="observaciones"
                        style="width: 95%; height: 7vw; resize: none;"></textarea>
                </div>

                <div style="display: flex; align-items: center;">
                    <button class="btn-green" type="submit" style="width: max-content;">
                        <i class="fa-solid fa-plus"></i> <b>Nuevo sitio</b>
                    </button>
                </div>

            </form>

        </div>

        <div class="divBackForm" id="arquitectura_edit" style="display: none;">
            <div class="close" style="width: 100%; display: flex; justify-content: flex-end; padding: .5vw">
                <button class="btn-red" onclick="cerrarFormularioEdit()" style="width: 2.3vw; height: 2.3vw;"><b><i
                            class="fa-solid fa-xmark"></i></b></button>
            </div>
            <h3 class="formTitle">Editar sitio</h3>
            <span style="color: red;" class="formTitle"><b>*</b> Campos obligatorios</span>

            <form action="controllers/arqui_edit.php" method="post" class="backForm" id="arquiEdit">

                <input type="hidden" name="id_sitio" id="id_sitio">

                <div style="margin-top: 0vw">
                    <label for="nombre">Nombre del sitio <b style="color: red;">*</b></label>
                    <input type="text" name="nombre" id="nombre-edit" required>
                </div>

                <div>
                    <label for="servicio">Servicio</label>
                    <select name="servicio" id="servicio-edit" class="select2" style="width: 95%;">
                        <option value=""></option>
                        <?php
                        $sql = "SELECT * FROM servicios";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute();
                        while ($servicio = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            echo "<option value='{$servicio['id']}'>{$servicio['servicio']}</option>";
                        }
                        ?>
                    </select>
                </div>

                <div>
                    <label for="tipo">Tipo de sitio <b style="color: red;">*</b></label>
                    <select name="tipo" id="tipo-edit" class="select2" required style="width: 95%;">
                        <option value=""></option>
                        <?php
                        $sql_grupos = "SELECT * FROM tipo_sitio_grupo";
                        $stmt_grupos = $pdo->prepare($sql_grupos);
                        $stmt_grupos->execute();
                        while ($grupo = $stmt_grupos->fetch(PDO::FETCH_ASSOC)) {
                            echo '<optgroup label="' . htmlspecialchars($grupo['nombre']) . '">';
                            $sql_sitios = "SELECT * FROM tipo_sitio WHERE grupo_id = :grupo_id";
                            $stmt_sitios = $pdo->prepare($sql_sitios);
                            $stmt_sitios->execute(['grupo_id' => $grupo['id']]);
                            while ($sitio = $stmt_sitios->fetch(PDO::FETCH_ASSOC)) {
                                echo "<option value='{$sitio['id']}'>" . htmlspecialchars($sitio['nombre']) . "</option>";
                            }
                            echo '</optgroup>';
                        }
                        ?>
                    </select>
                </div>

                <div id="recorrido-container-edit"></div>
                <div id="selector-container-edit"></div>
                <script>
                    $(document).ready(function () {
                        actualizarUnidades(null, 'selector-container-edit', 'recorrido-container-edit');
                    });
                </script>

                <div>
                    <label for="observaciones">Observaciones</label>
                    <textarea name="observaciones" id="observaciones-edit"
                        style="width: 95%; height: 7vw; resize: none;"></textarea>
                </div>

                <div>
                    <label>Estado</label>
                    <div style="display: flex; flex-direction: row;">
                        <div style="display: flex; flex-direction: row; align-items: center;">
                            <input type="radio" name="estado" value="activo" id="est_activo" style="width: 25%;"><label for="est_activo">Activo</label></div>
                        <div style="display: flex; flex-direction: row; align-items: center;">
                            <input type="radio" name="estado" value="inactivo" id="est_inactivo" style="width: 25%;"><label for="est_inactivo">Inactivo</label></div>
                    </div>
                </div>


                <div style="display: flex; align-items: center;">
                    <button class="btn-green" type="submit" style="width: max-content;">
                        <i class="fa-solid fa-save"></i> <b>Guardar cambios</b>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="modulo" style="width: 98%;">
        <div>
            <button class="btn-tematico"
                onclick="back.style.display = 'flex'; arquitectura_new.style.display = 'flex'; $('#unidad_select_contenedor-unidades').select2();"><i
                    class="fa-solid fa-plus"></i> <b>Nuevo sitio</b></button>
        </div>

        <div id="tree-container" class="tree"></div>

    </div>

</div>

<script defer src="/SGH/public/resources/unidades/unidades.js"></script>
<script src="js/arquitectura.js"></script>
<?php require_once '../../base/footer.php'; ?>