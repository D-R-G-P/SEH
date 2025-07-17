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

$cama_id = isset($_POST['cama_id']) ? intval($_POST['cama_id']) : null;

if ($cama_id) {
    $sql_select_base = "SELECT b.name, b.description, b.ubicacion_arquitectura_id, b.complexity, b.bed_status";
    $sql_joins = "";
    $sql_select_additional = "";

    // Verifica las condiciones de acceso para incluir los campos adicionales de 'beds'
    if (hasSubAccess(['administador_camas']) || hasAccess(['administrador', 'direccion'])) {
        $sql_select_additional .= ", CONCAT(creator.nombre, ' ', creator.apellido) AS created_by_name";
        $sql_select_additional .= ", b.date_created";
        $sql_select_additional .= ", CONCAT(updater.nombre, ' ', updater.apellido) AS updated_by_name";
        $sql_select_additional .= ", b.date_updated";
        $sql_select_additional .= ", CONCAT(deleter.nombre, ' ', deleter.apellido) AS deleted_by_name";
        $sql_select_additional .= ", b.date_deleted";
        $sql_select_additional .= ", b.deleted_reason";

        $sql_joins .= " LEFT JOIN personal AS creator ON b.created_by = creator.dni";
        $sql_joins .= " LEFT JOIN personal AS updater ON b.updated_by = updater.dni";
        $sql_joins .= " LEFT JOIN personal AS deleter ON b.deleted_by = deleter.dni";
    }

    // Une las partes de la consulta SQL para 'beds'
    $sql = $sql_select_base . $sql_select_additional . " FROM beds AS b" . $sql_joins . " WHERE b.id = :id ORDER BY b.id DESC LIMIT 1";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $cama_id, PDO::PARAM_INT);
        $stmt->execute();
        $cama = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$cama) {
            echo "Cama no encontrada.";
            exit;
        }

        $name = $cama['name'];
        $description = $cama['description'];
        $ubicacion_arquitectura_id = $cama['ubicacion_arquitectura_id'];
        $complexity = $cama['complexity'];
        $bed_status = $cama['bed_status'];
        $created_by_name = isset($cama['created_by_name']) ? $cama['created_by_name'] : '';
        $date_created = isset($cama['date_created']) ? $cama['date_created'] : '';
        $updated_by_name = isset($cama['updated_by_name']) ? $cama['updated_by_name'] : '';
        $date_updated = isset($cama['date_updated']) ? $cama['date_updated'] : '';
        $deleted_by_name = isset($cama['deleted_by_name']) ? $cama['deleted_by_name'] : '';
        $date_deleted = isset($cama['date_deleted']) ? $cama['date_deleted'] : '';
        $deleted_reason = isset($cama['deleted_reason']) ? $cama['deleted_reason'] : '';

        // --- Inicio de la sección corregida para bed_blocked ---
        $sql_select_blocked_base = "SELECT bb.id, bb.date_blocked, bb.blocked_type, bb.reason, bb.date_unblocked";
        $sql_blocked_joins = "";
        $sql_select_blocked_additional = "";

        // Verifica las condiciones de acceso para incluir los nombres del personal en 'bed_blocked'
        if (hasSubAccess(['administador_camas']) || hasAccess(['administrador', 'direccion'])) {
            $sql_select_blocked_additional .= ", CONCAT(blocker.nombre, ' ', blocker.apellido) AS blocked_by_name";
            $sql_select_blocked_additional .= ", CONCAT(unblocker.nombre, ' ', unblocker.apellido) AS unblocked_by_name";

            $sql_blocked_joins .= " LEFT JOIN personal AS blocker ON bb.blocked_by = blocker.dni";
            $sql_blocked_joins .= " LEFT JOIN personal AS unblocker ON bb.unblocked_by = unblocker.dni";
        }

        // Une las partes de la consulta SQL para 'bed_blocked'
        $registros_sql = $sql_select_blocked_base . $sql_select_blocked_additional . " FROM bed_blocked AS bb" . $sql_blocked_joins . " WHERE bb.bed_id = :id";
        // --- Fin de la sección corregida para bed_blocked ---

        $registros_stmt = $pdo->prepare($registros_sql);
        $registros_stmt->bindParam(':id', $cama_id, PDO::PARAM_INT);
        $registros_stmt->execute();
        $registros = $registros_stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        echo "Error en la consulta: " . $e->getMessage();
        exit; // Termina la ejecución en caso de error de la base de datos
    }
} else {
    // Si no se proporciona un ID de cama, inicializa las variables
    $name = '';
    $description = '';
    $ubicacion_arquitectura_id = 'null';
    $complexity = '';
    $bed_status = '';
    $created_by_name = '';
    $date_created = '';
    $updated_by_name = '';
    $date_updated = '';
    $deleted_by_name = '';
    $date_deleted = '';
    $deleted_reason = '';
    $registros = [];
}

if ($bed_status == 'Ocupada') {
    $sql = "SELECT patient_id FROM patients_admitteds WHERE bed_id = :bed_id AND date_discharged IS NULL LIMIT 1";
    $sql = $pdo->prepare($sql);
    $sql->bindParam(':bed_id', $cama_id);
    $sql->execute();

    $patient_data = $sql->fetch(PDO::FETCH_ASSOC);
    $patient_id = $patient_data['patient_id'];
}

function formatDate($date)
{
    if (!$date) {
        return '';
    }
    return date('d/m/Y H:i:s', strtotime($date));
}

?>

<div class="close" style="width: 100%; display: flex; justify-content: flex-end; padding: .5vw">
    <button class="btn-red" onclick="back.style.display = 'none'; info_cama.style.display = 'none';"
        style="width: 2.3vw; height: 2.3vw;"><b><i class="fa-solid fa-xmark"></i></b></button>
</div>

<h3 class="formTitle" style="width: fit-content">Información de la Cama <?php if ($cama_id) {
    echo 'ID: ' . $cama_id;
} ?></h3>

<?php if ($cama_id) { ?>
    <div class="acciones">
        <h3>Acciones</h3>
        <hr>
        <div>

            <script>
                let patient_id_let = <?php echo isset($patient_id) ? $patient_id : 'null'; ?>;
                let cama_id_let = <?php echo $cama_id; ?>;
            </script>

            <?php if ($bed_status == 'Libre' || $bed_status == 'Reservada') { ?>
                <button class="btn-green" id="ingresarBtn"><i class="fa-solid fa-plus"></i> Ingresar</button>
            <?php } ?>

            <?php if ($bed_status == 'Libre') { ?>
                <button class="btn-yellow" id="reservarBtn"><i class="fa-solid fa-clock-rotate-left"></i> Reservar</button>
            <?php } ?>

            <?php if ($bed_status == 'Reservada') { ?>
                <button class="btn-green" id="liberarReservaBtn"><i class="fa-solid fa-clock-rotate-left"></i> Liberar
                    reserva</button>
            <?php } ?>

            <?php if ($bed_status == 'Ocupada') { ?>
                <button class="btn-green" id="paseBtn"><i class="fa-solid fa-diagram-next"></i> Realizar pase</button>
            <?php } ?>

            <?php if ($bed_status == 'Ocupada') { ?>
                <button class="btn-red" id="egresarBtn"><i class="fa-solid fa-person-walking-dashed-line-arrow-right"></i>
                    Egresar</button>
            <?php } ?>

            <?php if ($bed_status == 'Ocupada') { ?>
                <!-- <button class="btn-yellow" id="camilleroBtn"><img style="width: 1.6vw;" src="../../../resources/image/camilla.svg"
                        alt=""> Solicitar camillero</button> -->
            <?php } ?>

            <?php if ($bed_status == 'Libre') { ?>
                <button class="btn-grey" id="bloquearBtn"><i class="fa-solid fa-ban"></i> Bloquear</button>
            <?php } ?>

            <?php if ($bed_status == 'Bloqueada') { ?>
                <button class="btn-yellow" id="desbloquearBtn"><i class="fa-solid fa-unlock"></i> Desbloquear</button>
            <?php } ?>

            <?php if ($bed_status == 'Ocupada') { ?>
                <button class="btn-tematico" id="verInfoBtn"
                    onclick="setInfo_paciente(<?php echo $patient_id; ?>, <?php echo $cama_id; ?>);"><i
                        class="fa-solid fa-address-card"></i> Ver información</button>
            <?php } ?>

        </div>
    </div>
<?php } ?>

<div class="info">
    <form action="controllers/save_cama.php" method="post" style="max-height: 100%;">
        <input type="hidden" name="cama_id" id="cama_id" value="<?php echo $cama_id; ?>">
        <h3>Detalles de la cama</h3>
        <hr>
        <div style="flex-direction: row;">
            <div>
                <label for="cama_name">Nombre de la Cama</label>
                <input type="text" name="cama_name" id="cama_name" style="height: 100%;" value="<?php echo $name; ?>"
                    readonly disabled>
            </div>

            <div>
                <label for="compejidad">Complejidad</label>
                <select name="complejidad" id="complejidad" class="select2" style="width: 100%;" disabled>
                    <option value=""></option>
                    <option value="Mínima" <?php echo ($complexity == 'Mínima') ? 'selected' : ''; ?>>Mínima</option>
                    <option value="Intermedia" <?php echo ($complexity == 'Intermedia') ? 'selected' : ''; ?>>Intermedia
                    </option>
                    <option value="Neutropénica" <?php echo ($complexity == 'Neutropénica') ? 'selected' : ''; ?>>
                        Neutropénica</option>
                    <option value="Intensiva" <?php echo ($complexity == 'Intensiva') ? 'selected' : ''; ?>>Intensiva
                    </option>
                </select>
            </div>
        </div>

        <div>
            <label for="description">Descripción</label>
            <textarea name="description" id="description" style="width: 100%; height: 6vw; resize: none;" disabled
                readonly><?php echo $description; ?></textarea>
        </div>

        <h3>Ubicación de la Cama</h3>
        <hr>
        <div>
            <div id="recorrido-container"></div>
            <div id="selector-container"></div>
            <script>
                $(document).ready(function () {
                    actualizarUnidades(<?php echo $ubicacion_arquitectura_id; ?>, 'selector-container', 'recorrido-container');
                });
            </script>
        </div>

        <?php if (hasSubAccess(['administador_camas']) || hasAccess(['administrador', 'direccion'])) { ?>

            <h3 style="margin-top: 1vw;">Auditoría</h3>
            <hr>
            <div style="flex-direction: row; margin-top: 1.5vw;">
                <div>
                    <label for="created_by">Creado por</label>
                    <input type="text" id="created_by" value="<?php echo $created_by_name; ?>" name="created_by" readonly
                        disabled>
                </div>

                <div>
                    <label for="created_date">Fecha de creación</label>
                    <input type="datetime" id="created_by" name="created_by"
                        style="height: 100%; border: #242424 0.2vw solid; border-radius: 0.8vw; padding: 0.5vw; font-size: 1.3vw;"
                        value="<?php echo formatDate($date_created); ?>" readonly disabled>
                </div>
            </div>

            <div style="flex-direction: row; margin-top: 1.5vw;">
                <div>
                    <label for="updated_by">Actualizada por</label>
                    <input type="text" id="updated_by" name="updated_by" value="<?php echo $updated_by_name; ?>" readonly
                        disabled>
                </div>

                <div>
                    <label for="updated_date">Fecha de actualización</label>
                    <input type="datetime" id="updated_by" name="updated_by"
                        style="height: 100%; border: #242424 0.2vw solid; border-radius: 0.8vw; padding: 0.5vw; font-size: 1.3vw;"
                        value="<?php echo formatDate($date_updated); ?>" readonly disabled>
                </div>
            </div>

            <div style="flex-direction: row; margin-top: 1.5vw;">
                <div>
                    <label for="deleted_by">Eliminado por</label>
                    <input type="text" id="deleted_date" name="deleted_date"
                        value="<?php echo htmlspecialchars($deleted_by_name); ?>" readonly disabled>
                </div>

                <div>
                    <label for="deleted_date">Fecha de eliminación</label>
                    <input type="datetime" id="deleted_date" name="deleted_date"
                        style="height: 100%; border: #242424 0.2vw solid; border-radius: 0.8vw; padding: 0.5vw; font-size: 1.3vw;"
                        value="<?php echo formatDate($date_deleted); ?>" readonly disabled>
                </div>
            </div>


            <div>
                <label for="delete_motivo">Motivo de eliminación</label>
                <textarea name="delete_motivo" id="deleted_motivo" style="width: 100%; height: 6vw; resize: none;" readonly
                    disabled><?php echo $deleted_reason; ?></textarea>
            </div>

            <button type="button" id="editBtnBed" style="display: none;" class="btn-green"><i
                    class="fa-solid fa-pencil"></i> Editar</button>

            <button class="btn-red" type="button" id="deleteBtnBed" style="display: none;"><i class="fa-solid fa-trash"></i>
                Eliminar</button>

            <button type="submit" id="newBtnBed" style="display: none;" class="btn-tematico"><i
                    class="fa-solid fa-plus"></i> Nueva cama</button>

            <button type="button" id="canBtnBed" style="display: none;" class="btn-red"><i class="fa-solid fa-xmark"></i>
                Cancelar</button>

            <button type="submit" id="saveBtnBed" style="display: none;" class="btn-green"><i
                    class="fa-solid fa-floppy-disk"></i> Guardar</button>
        <?php } ?>
    </form>

    <?php if (hasSubAccess(['administrador_camas']) || hasAccess(['administrador', 'direccion'])) { ?>
        <div class="historial_bloqueos_popup">
            <div class="historial_bloqueos_content">
                <h3 style="margin-bottom: 1vw;">Historial de bloqueos</h3>

                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Fecha de bloqueo</th>
                            <th>Bloqueada por</th>
                            <th>Tipo de bloqueo</th>
                            <th>Razon</th>
                            <th>Fecha de desbloqueo</th>
                            <th>Desbloqueada por</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($registros) < 1) { ?>
                            <tr>
                                <td colspan="7" style="text-align: center;">No se encontraron registros</td>
                            </tr>
                        <?php } else {
                            foreach ($registros as $reg): ?>
                                <tr>
                                    <td class="table-middle"><?= htmlspecialchars($reg['id']); ?></td>
                                    <td class="table-middle"><?= formatDate(htmlspecialchars($reg['date_blocked'])); ?></td>
                                    <td class="table-middle"><?= htmlspecialchars($reg['blocked_by_name']); ?></td>
                                    <td class="table-middle"><?= htmlspecialchars($reg['blocked_type']); ?></td>
                                    <td class="table-middle"><?= htmlspecialchars($reg['reason']); ?></td>
                                    <td class="table-middle"><?= formatDate(htmlspecialchars($reg['date_unblocked'])); ?></td>
                                    <td class="table-middle"><?= htmlspecialchars($reg['unblocked_by_name']); ?></td>
                                </tr>
                            <?php endforeach;
                        } ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php } ?>

    <div class="delete_poup" id="delete_poup">
        <div class="delete_poup_content">
            <h3 style="margin-bottom: 1vw;">Eliminar Cama</h3>
            <p>¿Estás seguro de que deseas eliminar esta cama?</p>
            <form action="controllers/delete_cama.php" method="post">
                <input type="hidden" name="cama_id" value="<?php echo $cama_id; ?>">

                <label for="deleted_reason">Motivo de eliminación:</label>
                <textarea name="deleted_reason" id="deleted_reason" required></textarea>

                <div style="display: flex; flex-direction: row; justify-content: space-between; margin-top: 1vw;">
                    <button type="submit" class="btn-red"><i class="fa-solid fa-trash"></i> Eliminar</button>
                    <button type="button" class="btn-grey" id="cancelDeleteBtn"><i class="fa-solid fa-xmark"></i>
                        Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <div class="reserve_popup" id="reserve_popup">
        <div class="reserve_popup_content">
            <h3 style="margin-bottom: 1vw;">Reservar Cama</h3>
            <form action="controllers/change_bed_status.php" method="post">
                <input type="hidden" name="cama_id" value="<?php echo $cama_id; ?>">
                <input type="hidden" name="cama_status" value="Reservada">

                <label for="reservation_reason">Motivo de la Reserva:</label>
                <textarea name="reservation_reason" id="reservation_reason" required></textarea>

                <div style="display: flex; flex-direction: row; justify-content: space-between; margin-top: 1vw;">
                    <button type="submit" class="btn-yellow"><i class="fa-solid fa-clock-rotate-left"></i>
                        Reservar</button>
                    <button type="button" class="btn-grey" id="cancelReserveBtn"><i class="fa-solid fa-xmark"></i>
                        Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <div class="bloquear_popup" id="bloquear_popup">
        <div class="bloquear_popup_content">
            <h3 style="margin-bottom: 1vw;">Bloquear Cama</h3>
            <form action="controllers/change_bed_status.php" method="post"
                style="display: flex; flex-direction: column;">
                <input type="hidden" name="cama_id" value="<?php echo $cama_id; ?>">
                <input type="hidden" name="cama_status" value="Bloquear">

                <label for="bloqueo_tipo">Tipo de bloqueo</label>
                <select name="bloqueo_tipo" id="bloqueo_tipo" class="select2" required>
                    <option value=""></option>
                    <option value="Cama rota">Cama rota</option>
                    <option value="Habitación inhabitable (problemas estructurales o edilicios graves)">Habitación
                        inhabitable (problemas estructurales o edilicios graves)</option>
                    <option value="Aislamiento">Aislamiento</option>
                    <option value="Falta de personal">Falta de personal</option>
                    <option value="Otro">Otro</option>
                </select>

                <label for="bloqueo_reason">Motivo del bloqueo:</label>
                <textarea name="bloqueo_reason" id="bloqueo_reason" required></textarea>

                <div style="display: flex; flex-direction: row; justify-content: space-between; margin-top: 1vw;">
                    <button type="submit" class="btn-red"><i class="fa-solid fa-ban"></i>
                        Bloquear</button>
                    <button type="button" class="btn-grey" id="cancelBlockBtn"><i class="fa-solid fa-xmark"></i>
                        Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <div class="ingresar_popup" id="ingresar_popup">
        <div class="ingresar_popup_content">
            <h3 style="margin-bottom: 1vw;">Ingresar paciente</h3>
            <form method="post" id="search_patient" style="display: flex; flex-direction: column;">

                <div style="display: flex; flex-direction: column; align-items: center;">
                    <div style="display: flex; flex-direction: row; width: 80%;">
                        <div style="width: 33%;">
                            <label for="tipo_documento">Tipo de documento</label>
                            <select name="tipo_documento" id="tipo_documento" class="select2">
                                <option value="">Seleccione</option>
                                <option value="DNI" selected>DNI</option>
                                <option value="CI">CI</option>
                                <option value="LC">LC</option>
                                <option value="LE">LE</option>
                                <option value="Cédula Mercosur">Cédula Mercosur</option>
                                <option value="CUIT">CUIT</option>
                                <option value="CUIL">CUIL</option>
                                <option value="Pasaporte extranjero">Pasaporte extranjero</option>
                                <option value="Cédula de Identidad Extranjera">Cédula de Identidad Extranjera</option>
                                <option value="Otro Documento Extranjero">Otro Documento Extranjero</option>
                                <option value="No posee">No posee</option>
                                <option value="En trámite">En trámite</option>
                            </select>
                        </div>

                        <div style="width: 66%;">
                            <label for="documento">Número de documento</label>
                            <input type="text" name="documento" id="documento" style="height: 100%;"
                                placeholder="Nro. de documento" autocomplete="off">
                        </div>
                    </div>

                    <div style="display: flex; align-items: start; width: 80%;">
                        <div style="width: 50%; margin-top: 1.5vw; text-align: start;">
                            <label for="sexo">Sexo</label>
                            <div id="sexo" style="flex-direction: row;">
                                <input type="radio" style="width: auto;" name="sexo" id="Femenino" value="Femenino">
                                <label for="Femenino">Femenino</label>
                                <input type="radio" style="width: auto;" name="sexo" id="Masculino" value="Masculino">
                                <label for="Masculino">Masculino</label>
                                <input type="radio" style="width: auto;" name="sexo" id="X" value="X"> <label
                                    for="X">X</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div style="margin: 1.5vw 0;">
                    <div style="display: flex; align-items: center; width: 100%; flex-direction: row;">
                        <div style="flex: 1; border-bottom: 1px solid #ccc; margin-right: 0.5em;"></div>
                        <span style="margin: 0 0.5em; font-weight: bold; white-space: nowrap;">O</span>
                        <div style="flex: 1; border-bottom: 1px solid #ccc; margin-left: 0.5em;"></div>
                    </div>
                </div>

                <div>
                    <label for="scan_dni">Escaneá el DNI</label>
                    <input type="text" id="codigoDNI" name="codigo_dni"
                        style="-webkit-text-security: disc; text-security: disc;" autocomplete="off">

                </div>

                <div style="display: flex; flex-direction: row; justify-content: space-between; margin-top: 1vw;">
                    <button type="button" class="btn-grey" id="cancelBlockBtnIng"><i class="fa-solid fa-xmark"></i>
                        Cancelar</button>
                    <button type="submit" class="btn-green"><i class="fa-solid fa-magnifying-glass"></i> Buscar</button>
                </div>
            </form>
        </div>
    </div>

    <div id="pase_popup" class="pase_popup">
        <div class="pase_popup_content">
            <h3 style="margin-bottom: 1vw;">Realizar Pase</h3>
            <form action="controllers/pase_paciente.php" method="post">
                <input type="hidden" name="cama_id" value="<?php echo $cama_id; ?>">
                <input type="hidden" name="patient_id" id="patient_id"
                    value="<?php echo isset($patient_id) ? $patient_id : ''; ?>">
                <input type="hidden" name="new_bed_id" id="new_bed_id">

                <div>
                    <div id="recorrido-pase"></div>
                    <div id="selector-pase"></div>
                    <script>
                        $(document).ready(function () {
                            actualizarUnidades(<?php echo $ubicacion_arquitectura_id; ?>, 'selector-pase', 'recorrido-pase');
                        });
                    </script>
                </div>

                <div id="beds_result"
                    style="margin-top: 1vw; display: flex; flex-direction: row; justify-content: center;">
                </div>


                <div style="display: flex; flex-direction: row; justify-content: space-between; margin-top: 1vw;">
                    <button type="submit" id="submit_pase_btn" class="btn-green" disabled><i
                            class="fa-solid fa-diagram-next"></i> Realizar
                        Pase</button>
                    <button type="button" class="btn-grey" id="cancelPaseBtn"><i class="fa-solid fa-xmark"></i>
                        Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>