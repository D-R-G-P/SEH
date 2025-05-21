<?php require_once '../../../../app/db/db.php';
require_once '../../../../app/db/user_session.php';
require_once '../../../../app/db/user.php';
require_once '../../../config.php';
$user = new User();
$userSession = new
    UserSession();
$currentUser = $userSession->getCurrentUser();
$user->setUser($currentUser);

requireRole(['administrador', 'direccion', 'gestion_turnos']);

$title = "Gestión de Turnos";

$db = new DB();
$pdo = $db->connect();

?>
<?php require_once '../../base/header.php'; ?>
<link rel="stylesheet" href="/SGH/public/layouts/modules/gestion_turnos/css/gestion_turnos.css">

<div class="content">

    <div class="modulo" style="text-align: center;">
        <h3>Gestión de Turnos</h3>
        <p>Este modulo está destinado a otorgar acceso a las diferentes utilidades del sistema.</p>
    </div>

    <?php if (hasAccess(['administrador', 'direccion']) || hasSubAccess(['chat_turnos', 'chat_turnos_adm'])): ?>
        <div style="position: fixed; left: 20vw; top: 6.5vw; display: flex; flex-direction: column;">
            <a href="chatting.php" class="btn-tematico">
                <i class="fa-brands fa-whatsapp"></i> <b>WhatsApp</b>
            </a>
        </div>
    <?php endif; ?>

    <?php if (hasAccess(['administrador', 'direccion']) || hasSubAccess(['bot_turnos', 'chat_turnos_adm'])): ?>
        <div style="position: fixed; left: 20vw; top: 10vw; display: flex; flex-direction: column;">
            <a href="info_servicios.php" class="btn-tematico">
                <i class="fa-solid fa-circle-info"></i> <b>Info servicios</b>
            </a>
        </div>
    <?php endif; ?>

    <div class="back" id="back"></div>

    <?php if (hasAccess(['administrador', 'direccion']) || hasSubAccess(['bot_turnos'])): ?>

        <div class="modulo">
            <div class="controls" style="display: flex; justify-content: center; flex-direction: column;">
                <button id="startBot" style="display: none;" class="btn-green"><i class="fa-solid fa-power-off"></i>
                    Encender</button>
                <button id="stopBot" style="display: none;" class="btn-red"><i class="fa-solid fa-power-off"></i>
                    Apagar</button>
                <button id="restartBot" style="display: none;" class="btn-yellow"><i class="fa-solid fa-rotate-right"></i>
                    Reiniciar</button>
                <a style="text-align: center;" href="config_bot_turnos.php" class="btn-tematico"><i
                        class="fa-solid fa-gear"></i> Configuración</a>

                <div style="display: flex; flex-direction: row;">
                    <h3>Estado: </h3>
                    <div id="loader" style="margin-left: 1vw; align-items: center; display: none;">
                        <div style="margin-top: .2vw;" class="loader"></div>
                    </div>
                    <h3><span id="botStatus">Desconocido</span></h3>
                </div>
                <img id="qrImage" style="display: none;">

            </div>
        </div>

    <?php endif; ?>

</div>
<script src="/SGH/public/layouts/modules/gestion_turnos/js/gestion_turnos.js"></script>
<?php require_once '../../base/footer.php'; ?>