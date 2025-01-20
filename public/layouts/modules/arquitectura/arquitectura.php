<?php

require_once '../../../../app/db/db.php';
require_once '../../../../app/db/user_session.php';
require_once '../../../../app/db/user.php';
require_once '../../../config.php';

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

    <div class="back" id="back"></div>

    <div class="modulo" style="width: 98%;">


    <div id="tree-container" class="tree"></div>

    </div>

</div>

<script src="js/arquitectura.js"></script>
<?php require_once '../../base/footer.php'; ?>