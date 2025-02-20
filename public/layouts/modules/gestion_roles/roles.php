<?php

require_once '../../../../app/db/db.php';
require_once '../../../../app/db/user_session.php';
require_once '../../../../app/db/user.php';
require_once '../../../config.php';


$user = new User();
$userSession = new UserSession();
$currentUser = $userSession->getCurrentUser();
$user->setUser($currentUser);

requireRole([ 'administrador', 'direccion', 'gestion_roles' ]);

$title = "Gestión de Roles";

$db = new DB();
$pdo = $db->connect();

?>

<?php require_once '../../base/header.php'; ?>
<link rel="stylesheet" href="/SGH/public/layouts/modules/gestion_roles/css/roles.css">

<div class="content">

    <div class="modulo" style="text-align: center;">
        <h3>Gestión de Roles</h3>
        <p>Este modulo está destinado a otorgar acceso a las diferentes utilidades del sistema.</p>
    </div>

    <div class="back" id="back"></div>

    <div class="modulo"></div>

</div>

<script src="/SGH/public/layouts/modules/gestion_roles/js/roles.js"></script>
<?php require_once '../../base/footer.php'; ?>