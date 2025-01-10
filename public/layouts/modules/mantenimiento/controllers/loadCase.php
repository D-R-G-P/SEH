<?php

require_once '../../../../../app/db/db.php';
require_once '../../../../../app/db/user_session.php';
require_once '../../../../../app/db/user.php';
require_once '../../../../config.php';

$db = new DB();
$pdo = $db->connect();
$user = new User();
$userSession = new UserSession();
$currentUser = $userSession->getCurrentUser();
$user->setUser($currentUser);

// Verificar si se recibió el DNI a través de la solicitud POST
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    // Recibir el DNI enviado desde la solicitud AJAX
    $id = $_POST['id'];
    $gestion = $_POST['gestionMode'];
    if ($gestion == "reclamante") {
        $gestionMode = false;
    } else if ($gestion == "destino") {
        $gestionMode = true;
    }



    $queryInfo = "
    SELECT 
        m.*, 
        CONCAT(p.apellido, ' ', p.nombre) AS nombre_reclamante,
        CONCAT(pf.apellido, ' ', pf.nombre) AS usuario_apertura_first_nombre,
        CONCAT(pl.apellido, ' ', pl.nombre) AS usuario_apertura_latest_nombre,
        s.servicio AS nombre_servicio
    FROM mantenimiento m
    INNER JOIN personal p ON m.reclamante = p.dni
    LEFT JOIN personal pf ON m.usuario_apertura_first = pf.dni
    LEFT JOIN personal pl ON m.usuario_apertura_latest = pl.dni
    LEFT JOIN servicios s ON m.servicio = s.id
    WHERE m.id = :id
";


    $stmtInfo = $pdo->prepare($queryInfo);
    $stmtInfo->bindParam(':id', $id, PDO::PARAM_STR);
    $stmtInfo->execute();

    $resultados = $stmtInfo->fetchAll(PDO::FETCH_ASSOC);

    // Verificar si hay resultados
    if (!empty($resultados)) {
        foreach ($resultados as $fila) {
?>
            <div class="close" style="width: 100%; display: flex; justify-content: flex-end; padding: .5vw; margin-top: 17vw;">
                <button class="btn-red" onclick="back.style.display = 'none'; infoCaseBase.style.display = 'none'; document.querySelectorAll(form).reset(); $('#solicitudServicio').val(null).trigger('change');" style="width: 2.3vw; height: 2.3vw; z-index: 100;"><b><i class="fa-solid fa-xmark"></i></b></button>
            </div>
            <h3 class="formTitle" style="display: flex; justify-content: center; align-items: center; margin-bottom: 1vw;">
                Solicitud ID: <?= htmlspecialchars($fila['id'] ?? 'N/A') ?>
            </h3>

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
                                <td colspan="2"><?= htmlspecialchars($fila['nombre_servicio'] ?? '#error') ?></td>
                            </tr>
                            <tr>
                                <td colspan="2"><b>Localización</b></td>
                            </tr>
                            <tr>
                                <td colspan="2"><?= htmlspecialchars($fila['ubicacion'] ?? 'Sin ubicación') ?></td>
                            </tr>
                            <tr>
                                <td colspan="2"><b>Problema</b></td>
                            </tr>
                            <tr>
                                <td colspan="2"><?= htmlspecialchars($fila['short_description'] ?? 'Sin descripción breve') . ' - ' . htmlspecialchars($fila['long_description'] ?? 'Sin descripción detallada') ?></td>
                            </tr>
                            <tr>



                                <td class="table-middle">
                                    <b>Estado</b>
                                </td>
                                <td class="table-center table-middle">

                                    <?php if (!$gestionMode && $fila['estado_reclamante'] != "Completado") { ?>
                                        <form action="controllers/procesar_estado.php" method="post" style="display: flex; margin: 0; width: 100%; align-items: center;">
                                            <input type="hidden" name="id" value="<?= htmlspecialchars($fila['id']) ?>">
                                            <input type="hidden" name="sender" value="reclamante">
                                            <select id="estado" name="estado" onchange="document.getElementById('state_reclamante').style.display = 'inline-block'; finished(this.value);">
                                                <option class="pendiente" value="Pendiente" <?= $fila['estado_reclamante'] === 'Pendiente' ? 'selected' : '' ?>>Pendiente</option>
                                                <option class="en_proceso" value="En proceso" <?= $fila['estado_reclamante'] === 'En proceso' ? 'selected' : '' ?>>En proceso</option>
                                                <option class="completado" value="Completado" <?= $fila['estado_reclamante'] === 'Completado' ? 'selected' : '' ?>>Completado</option>
                                                <option class="rechazado" value="Rechazado" <?= $fila['estado_reclamante'] === 'Rechazado' ? 'selected' : '' ?>>Rechazado</option>
                                                <option class="programado" value="Programado" <?= $fila['estado_reclamante'] === 'Programado' ? 'selected' : '' ?>>Programado</option>
                                            </select>
                                            <button title="Guardar" id="state_reclamante" type="submit" class="btn-green" style="display: none; margin-left: 10px;">
                                                <i class="fa-solid fa-floppy-disk"></i>
                                            </button>
                                        </form>
                                    <?php } else { ?>
                                        <?= htmlspecialchars($fila['estado_reclamante'] ?? 'Sin estado') ?>
                                    <?php } ?>
                                </td>



                            </tr>

                            <tr>
                                <td colspan="2" class="table-center"><b>Observación del reclamante</b></td>
                            </tr>
                            <tr>

                                <?php if (!$gestionMode && $fila['estado_reclamante'] != "Completado") { ?>
                                    <td colspan="2">
                                        <form action="controllers/procesar_observaciones.php" method="post" style="height: auto; width: 100%;">
                                            <input type="hidden" name="id" value="<?= htmlspecialchars($fila['id']) ?>">
                                            <input type="hidden" name="sender" value="reclamante">
                                            <div style="display: flex; flex-direction: row;">
                                                <textarea oninput="obs_reclamante.style.display = 'block';" name="observaciones" id="observaciones_reclamante" style="width: 100%; height: 5vw; resize: none;" placeholder="Escribe aquí la observación que consideres..."><?= htmlspecialchars($fila['observaciones_reclamante']) ?></textarea>

                                                <button title="Guardar" style="margin: 0 0 0 .3vw; display: none;" id="obs_reclamante" type="submit" class="btn-green"><i class="fa-solid fa-floppy-disk"></i></button>
                                            </div>
                                        </form>
                                    </td>
                                <?php } else { ?>
                                    <td colspan="2" class="table-center">
                                        <?= htmlspecialchars(trim($fila['observaciones_reclamante']) ?: 'Sin observaciones') ?>
                                    </td>
                                <?php } ?>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2" style="text-align: center;"><b>Reclamante</b></td>
                            </tr>
                            <tr>
                                <td colspan="2" style="text-align: center;">
                                    <?= htmlspecialchars($fila['nombre_reclamante'] ?? 'N/A') ?>
                                </td>
                            </tr>
                            <tr>
                                <td><b><?php if ($fila['fecha_reclamante']) {
                                            echo 'Fecha auditado';
                                        } else {
                                            echo 'Fecha de registro';
                                        } ?></b></td>
                                <td>
                                    <?php if ($fila['destino'] == 'auditoria') {
                                        echo 'En evaluación';
                                    } else {
                                        echo !empty($fila['fecha_reclamante']) ? date('d/m/Y H:i', strtotime($fila['fecha_reclamante'])) : date('d/m/Y H:i', strtotime($fila['fecha_registro']));
                                    } ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="right">
                    <table>
                        <thead>
                            <tr>
                                <th colspan="2" style="width: auto;">Intervención del área</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="2" class="table-center"><b>Observación del destino</b></td>
                            </tr>
                            <tr>
                                <?php
                                if (
                                    (hasSubAccess(['personal_mantenimiento', 'personal_arquitectura', 'personal_informatica', 'personal_ingenieria_clinica']) 
                                    || hasAccess(['administrador']))
                                    && $gestionMode 
                                    && $fila['estado_reclamante'] !== "Completado" 
                                    && $fila['estado_destino'] !== "Completado"
                                ) { ?>
                                    <td colspan="2">
                                        <form action="controllers/procesar_observaciones.php" method="post" style="height: auto; width: 100%;">
                                            <input type="hidden" name="id" value="<?= htmlspecialchars($fila['id']) ?>">
                                            <input type="hidden" name="sender" value="destino">
                                            <div style="display: flex; flex-direction: row;">
                                                <textarea oninput="obs_destino.style.display = 'block';" name="observaciones" id="observaciones_destino" style="width: 100%; height: 5vw; resize: none;" placeholder="Escribe aquí la observación que consideres..."><?= htmlspecialchars($fila['observaciones_destino']) ?></textarea>

                                                <button title="Guardar" style="margin: 0 0 0 .3vw; display: none;" id="obs_destino" type="submit" class="btn-green"><i class="fa-solid fa-floppy-disk"></i></button>
                                            </div>
                                        </form>
                                    </td>
                                <?php } else { ?>
                                    <td colspan="2"><?= htmlspecialchars(trim($fila['observaciones_destino']) ?: 'Sin observaciones') ?></td>
                                <?php } ?>
                            </tr>
                            <tr>
                                <td><b>Estado</b></td>
                                <td>
                                    <?php if (
    (hasSubAccess(['personal_mantenimiento', 'personal_arquitectura', 'personal_informatica', 'personal_ingenieria_clinica']) 
    || hasAccess(['administrador']))
    && $gestionMode 
    && $fila['estado_reclamante'] !== "Completado" 
    && $fila['estado_destino'] !== "Completado"
) { ?>
                                        <form action="controllers/procesar_estado.php" method="post" style="display: flex; margin: 0; width: 100%; align-items: center;">
                                            <input type="hidden" name="id" value="<?= htmlspecialchars($fila['id']) ?>">
                                            <input type="hidden" name="sender" value="destino">
                                            <select id="estado" name="estado" onchange="document.getElementById('state_destino').style.display = 'inline-block';">
                                                <option class="pendiente" value="Pendiente" <?= $fila['estado_destino'] === 'Pendiente' ? 'selected' : '' ?>>Pendiente</option>
                                                <option class="en_proceso" value="En proceso" <?= $fila['estado_destino'] === 'En proceso' ? 'selected' : '' ?>>En proceso</option>
                                                <option class="completado" value="Completado" <?= $fila['estado_destino'] === 'Completado' ? 'selected' : '' ?>>Completado</option>
                                                <option class="rechazado" value="Rechazado" <?= $fila['estado_destino'] === 'Rechazado' ? 'selected' : '' ?>>Rechazado</option>
                                                <option class="programado" value="Programado" <?= $fila['estado_destino'] === 'Programado' ? 'selected' : '' ?>>Programado</option>
                                            </select>
                                            <button title="Guardar" id="state_destino" type="submit" class="btn-green" style="display: none; margin-left: 10px;">
                                                <i class="fa-solid fa-floppy-disk"></i>
                                            </button>
                                        </form>
                                    <?php } else { ?>
                                        <?= htmlspecialchars($fila['estado_destino'] ?? 'Sin estado') ?>
                                    <?php } ?>
                                </td>
                            </tr>
                            <tr>
                                <td><b>Prioridad</b></td>
                                <td><?= htmlspecialchars($fila['prioridad'] ?? 'No definida') ?></td>
                            </tr>
                            <tr>
                                <td class="table-middle"><b>Destino</b></td>
                                <td>
                                    <?= ucfirst(htmlspecialchars($fila['destino'] ?? 'Sin destino')) ?>
                                    <?php
                                    if ($gestionMode && ($fila['estado_reclamante'] !== "Completado" && $fila['estado_destino'] !== "Completado")) { ?>
                                        <button class="btn-yellow" title="Marcar para auditar" style="padding: .5vw;" onclick="audit(<?= htmlspecialchars($fila['id']); ?>);"><i class="fa-solid fa-circle-exclamation" style="font-size: 1vw;"></i></button>
                                    <?php } ?>

                                </td>
                            </tr>
                            <tr>
                                <td><b>Fecha del destino</b></td>
                                <td>
                                    <?= !empty($fila['fecha_destino']) ? date('d/m/Y H:i', strtotime($fila['fecha_destino'])) : 'Sin fecha' ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <?php if (hasAccess(['administrador', 'direccion']) || (hasAccess(['mantenimiento']) && (['auditoria'])) && $fila['destino'] == 'auditoria') { ?>
                        <table style="margin-top: .5vw;">
                            <thead>
                                <tr>
                                    <th colspan="2" style="width: auto;">Auditoría</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="table-middle"><b>Nuevo destino</b></td>
                                    <td>
                                        <?php if (hasAccess(['direccion', 'administrador'])  && $gestionMode || hasSubAccess(['auditoria']) && $gestionMode) { ?>
                                            <form action="controllers/audit.php" method="post" style="display: flex; flex-direction: row; align-items: center;">
                                                <input type="hidden" name="id" value="<?= htmlspecialchars($fila['id']) ?>">
                                                <select name="destino" id="destino" onchange="auditSave.style.display = 'block';" required>
                                                    <option value="" selected disabled>Seleccionar un destino...</option>
                                                    <option value="mantenimiento">Mantenimiento</option>
                                                    <option value="arquitectura">Arquitectura</option>
                                                    <option value="informatica">Informatica</option>
                                                    <option value="ingenieria_clinica">Ingeniería Clínica</option>
                                                </select>
                                                <button type="submit" class="btn-green" id="auditSave" style="display: none; margin-left: 10px;"><i class="fa-solid fa-floppy-disk"></i></button>
                                            </form>
                                        <?php } else {
                                            echo $fila['destino'];
                                        } ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td><b>Nueva fecha y hora</b></td>
                                    <td>
                                        <span id="newDate"><?php echo date('d/m/Y H:i'); ?></span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    <?php } ?>

                    <?php if (hasAccess(['administrador', 'direccion'])) { ?>
                        <table style="margin-top: .5vw;">
                            <thead>
                                <tr>
                                    <th colspan="2">Visor de accesos</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr><td colspan="2" class="table-center"><b>Primer acceso</b></td></tr>
                                <tr>
                                    <td><b>Fecha y hora</b></td>
                                    <td><?= htmlspecialchars($fila['fecha_apertura_first'] ?? 'Sin acceso') ?></td>
                                </tr>
                                <tr>
                                    <td><b>Usuario</b></td>
                                    <td><?= htmlspecialchars($fila['usuario_apertura_first_nombre'] ?? 'Sin acceso') ?></td>
                                </tr>
                                <tr><td colspan="2" class="table-center"><b>Último acceso</b></td></tr>
                                <tr>
                                    <td><b>Fecha y hora</b></td>
                                    <td><?= htmlspecialchars($fila['fecha_apertura_latest_nombre'] ?? 'Sin acceso') ?></td>
                                </tr>
                                <tr>
                                    <td><b>Usuario</b></td>
                                    <td><?= htmlspecialchars($fila['usuario_apertura_latest'] ?? 'Sin acceso') ?></td>
                                </tr>
                            </tbody>
                        </table>
                    <?php } ?>
                </div>
            </div>


<?php
        }
    } else {
        echo "<p>No se encontraron registros para el ID proporcionado.</p>";
    }
} else {
    echo "<p>Error: No se recibió un ID válido.</p>";
}
?>