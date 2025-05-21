<?php
require_once '../../../../app/db/db.php';
require_once '../../../../app/db/user_session.php';
require_once '../../../../app/db/user.php';
require_once '../../../config.php';

$user = new User();
$userSession = new UserSession();
$currentUser = $userSession->getCurrentUser();
$user->setUser($currentUser);

requireRole(['administrador', 'direccion', 'gestion_turnos']);
requireSubRole(['bot_turnos']);

$title = "GDT -> Configuración del Bot";

$db = new DB();
$pdo = $db->connect();

// Obtener datos de la base de datos
$stmt = $pdo->query("SELECT * FROM atention_days_turnos ORDER BY id");
$days = $stmt->fetchAll(PDO::FETCH_ASSOC);

$default_message = "SELECT message FROM wsp_responses WHERE response_to = ?";
$default_message = $pdo->prepare($default_message);
$default_message->execute(['welcome']);
$welcome_message = $default_message->fetchColumn();

$default_message->execute(['farewell_default']);
$farewell_default_message = $default_message->fetchColumn();

?>
<?php require_once '../../base/header.php'; ?>
<link rel="stylesheet" href="/SGH/public/layouts/modules/gestion_turnos/css/config_bot_turnos.css">

<div class="content">

    <a class="btn-tematico" href="gestion_turnos.php" style="position: fixed; left: 20vw; top: 6.5vw;">
        <i class="fa-solid fa-arrow-left"></i> <b>Volver</b>
    </a>

    <div class="modulo" style="text-align: center;">
        <h3>Configuración del bot</h3>
        <p>Este modulo está destinado a configurar las opciónes del bot de Gestión de Turnos.</p>
    </div>

    <div class="back" id="back"></div>

    <?php if (hasAccess(['administrador', 'direccion']) || hasSubAccess(['bot_turnos'])): ?>

        <h2 style="font-size: 2vw">Horario de atención</h2>
        <div class="modulo">
            <form action="controllers/atention_day.php" method="post" class="horario_form" style="max-height: max-content;">
                <?php foreach ($days as $day): ?>
                    <?php
                    $checked = $day['enabled'] ? 'checked' : '';
                    $disabled = $day['enabled'] ? '' : 'disabled';
                    $opacity = $day['enabled'] ? '1' : '0.5';
                    ?>
                    <div class="day-row" style="display: flex; align-items: center; opacity: <?= $opacity ?>;">
                        <div>
                            <label class="switch">
                                <input type="checkbox" class="day-checkbox" name="enabled[<?= $day['id'] ?>]" <?= $checked ?>>
                                <span class="slider round"></span>
                            </label>
                            <label style="margin: 0 .5vw;"><b><?= ucfirst($day['day_name']) ?>:</b></label>
                        </div>
                        <div style="display: flex; flex-direction: row;">
                            De <input style="margin: 0 .5vw" type="time" name="start_time[<?= $day['id'] ?>]"
                                value="<?= $day['start_time'] ?>" <?= $disabled ?>> a
                            <input style="margin: 0 .5vw" type="time" name="end_time[<?= $day['id'] ?>]"
                                value="<?= $day['end_time'] ?>" <?= $disabled ?>>
                        </div>
                    </div>
                <?php endforeach; ?>
                <button type="submit" class="btn-green"><i class="fa-solid fa-floppy-disk"></i> Guardar cambios</button>
            </form>
        </div>

        <h2 style="font-size: 2vw">Mensajes predeterminados</h2>
        <div class="modulo" style="display: flex; flex-direction: column;">

            <form action="controllers/save_message.php" id="ASD" method="post" style="max-height: max-content;">
                <h3>Mensaje de Bienvenida</h3>

                <div class="welcome_parent parent_predefinido">
                    <div class="welcome_div1 div1_predefinido" style="display: flex; flex-direction: column;">
                        <label for="response_to">Responde a:</label>
                        <input type="text" readonly id="response_to" name="response_to" value="welcome" style="width: 95%;"
                            title="Esta propiedad no puede cambiarse para este selector">
                    </div>

                    <div class="welcome_div2 div2_predefinido"
                        style="display: flex; flex-direction: column; align-items: center;">
                        <span>Estado</span>
                        <div style="display: flex; flex-direction: row; margin-left: 5vw;">
                            <label for="on_welcome" style="margin-right: 1vw; margin-top: 1vw">Off</label>
                            <label class="switch" style="margin-top: 1.2vw">
                                <input type="checkbox" id="on_welcome" name="state" class="role-checkbox" checked readonly
                                    title="Esta propiedad no puede cambiarse para este selector">
                                <span class="slider round"></span>
                            </label>
                            <label for="on_welcome" style="margin-left: 1vw; margin-top: 1vw">On</label>
                        </div>
                    </div>
                </div>

                <div style="display: flex; flex-direction: column;">
                    <label for="message">Mensaje</label>
                    <textarea name="message" id="message"
                        style="width: 50vw; resize: vertical; min-height: 10vw; max-height: 30vw; field-sizing: content; background-color:rgb(239, 239, 239);"><?= $welcome_message ?></textarea>
                </div>



                <button class="btn-green" type="submit"><i class="fa-solid fa-floppy-disk"></i> Guardar</button>
            </form>



            <hr style="margin: 2vw 0">

            <form action="controllers/save_message.php" method="post" style="max-height: max-content;">
                <h3>Mensaje de Despedida</h3>

                <div class="farewell_default_parent parent_predefinido">
                    <div class="farewell_default_div1 div1_predefinido" style="display: flex; flex-direction: column;">
                        <label for="response_to">Responde a:</label>
                        <input type="text" readonly id="response_to" name="response_to" value="farewell_default"
                            style="width: 95%;" title="Esta propiedad no puede cambiarse para este selector">
                    </div>

                    <div class="farewell_default_div2 div2_predefinido"
                        style="display: flex; flex-direction: column; align-items: center;">
                        <span>Estado</span>
                        <div style="display: flex; flex-direction: row; margin-left: 5vw;">
                            <label for="on_farewell_default" style="margin-right: 1vw; margin-top: 1vw">Off</label>
                            <label class="switch" style="margin-top: 1.2vw">
                                <input type="checkbox" id="on_farewell_default" name="state" class="role-checkbox" checked
                                    readonly title="Esta propiedad no puede cambiarse para este selector">
                                <span class="slider round"></span>
                            </label>
                            <label for="on_farewell_default" style="margin-left: 1vw; margin-top: 1vw">On</label>
                        </div>
                    </div>
                </div>

                <div style="display: flex; flex-direction: column;">
                    <label for="message">Mensaje</label>
                    <textarea name="message" id="message"
                        style="width: 50vw; resize: vertical; min-height: 10vw; max-height: 30vw; field-sizing: content; background-color:rgb(226, 223, 214);"><?= $farewell_default_message ?></textarea>
                </div>



                <button class="btn-green" type="submit"><i class="fa-solid fa-floppy-disk"></i> Guardar</button>
            </form>
        </div>

    <?php endif; ?>

</div>
<script src="/SGH/public/layouts/modules/gestion_turnos/js/config_bot_turnos.js"></script>
<?php require_once '../../base/footer.php'; ?>