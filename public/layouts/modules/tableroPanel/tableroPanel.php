<?php

require_once '../../../../app/db/db.php';
require_once '../../../../app/db/user_session.php';
require_once '../../../../app/db/user.php';
require_once '../../../config.php';

$user = new User();
$userSession = new UserSession();
$currentUser = $userSession->getCurrentUser();
$user->setUser($currentUser);

requireRole(['administrador', 'direccion', 'tab_mando']);

$title = "Tablero de mando";

$db = new DB();
$pdo = $db->connect();

$servicioFilter = $user->getServicio();
?>

<?php require_once '../../base/header.php'; ?>
<link rel="stylesheet" href="/SGH/public/layouts/modules/tableroPanel/css/tableroPanel.css">

<div class="content">

    <div class="back">

    </div>

    <div class="modulo" style="text-align: center;">
        <h3>Tablero de mando</h3>
        <p>Este sistema está oreintado a la gestion e <br> información relevante del hospital</p>
    </div>

    <div class="modulo">
        <h3 style="margin-bottom: 1vw;">Estado de equipos</h3>
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); justify-items: center; align-items: center; align-content: stretch;" id="tableEquip">
        </div>
    </div>

</div>

<script src="/SGH/public/layouts/modules/tableroPanel/js/tableroPanel.js"></script>
<?php require_once '../../base/footer.php'; ?>