<?php

require_once '../../../../app/db/db.php';
require_once '../../../../app/db/user_session.php';
require_once '../../../../app/db/user.php';

$user = new User();
$userSession = new UserSession();
$currentUser = $userSession->getCurrentUser();
$user->setUser($currentUser);

$title = "Solicitudes de HSI";

$db = new DB();
$pdo = $db->connect();

$servicioFilter = $user->getServicio();

?>

<?php require_once '../../base/header.php'; ?>
<link rel="stylesheet" href="/SGH/public/layouts/modules/hsiPanel/css/hsi.css">

<div class="content">

    <div class="modulo" style="text-align: center;">
        <h3 style="margin-bottom: .5vw;">Sistema de administración de HSI</h3>
        <p>Este sistema está oreintado a la gestion y administración de los </br> usuarios de HSI para los administradores institucionales.</p>
    </div>


    <script src="/SGH/public/layouts/modules/hsiPanel/js/hsi.js"></script>
    <?php require_once '../../base/footer.php'; ?>