<?php

require_once '../../../../app/db/user_session.php';
require_once '../../../../app/db/user.php';
require_once '../../../../app/db/user.php';

$user = new User();
$userSession = new UserSession();
$currentUser = $userSession->getCurrentUser();
$user->setUser($currentUser);

$title = "Panel de administración"


?>

<?php require_once '../../base/header.php'; ?>
<link rel="stylesheet" href="/SGH/public/layouts/modules/personalPanel/css/personal.css">


<div class="content">
    <div class="title">
        <h3>Sistema de gestión de personal</h3>
        <p>Este sistema está oreintado a la declaración del personal a cargo y administración de privilegios de los mismos dentro de este sistema.</p>
    </div>


</div>


<?php require_once '../../base/footer.php'; ?>