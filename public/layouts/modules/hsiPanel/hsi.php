<?php

require_once '../../../../app/db/db.php';
require_once '../../../../app/db/user_session.php';
require_once '../../../../app/db/user.php';
require_once '../../../../app/db/user.php';

$user = new User();
$userSession = new UserSession();
$currentUser = $userSession->getCurrentUser();
$user->setUser($currentUser);

$title = "Solicitudes de HSI";

$db = new DB();
$pdo = $db->connect();

?>

<?php require_once '../../base/header.php'; ?>
<link rel="stylesheet" href="/SGH/public/layouts/modules/hsiPanel/css/hsi.css">

<div class="content">

    <div class="modulo" style="text-align: center;">
        <h3 style="margin-bottom: .5vw;">Sistema de gestión de HSI</h3>
        <p>Este sistema está oreintado a la gestion y </br> solicitud de los usuarios de HSI del personal a cargo.</p>
    </div>

    <div class="back">

    </div>

    <div class="modulo">
        <div class="inlineDiv">
            <button class="btn-green"><i class="fa-solid fa-plus"></i> Solicitar nuevo usuario</button>
        </div>
    </div>

</div>

<script src="/SGH/public/layouts/modules/hsiPanel/js/hsi.js"></script>
<?php require_once '../../base/footer.php'; ?>