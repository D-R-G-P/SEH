<?php

require_once '../../../../app/db/db.php';
require_once '../../../../app/db/user_session.php';
require_once '../../../../app/db/user.php';

$user = new User();
$userSession = new UserSession();
$currentUser = $userSession->getCurrentUser();
$user->setUser($currentUser);

$title = "Mi usaurio";

$db = new DB();
$pdo = $db->connect();

?>

<?php require_once '../../base/header.php'; ?>
<link rel="stylesheet" href="/SGH/public/layouts/modules/miUsuario/css/miUsuario.css">

<div class="content">

    <div class="modulo" style="text-align: center;">
        <h3>Gestión de mi usuario</h3>
        <p>Este sistema está oreintado a la gestion de </br> nuestro usuario.</p>
    </div>

    <div class="modulo formModulo">
        <div style="width: 100%; align-items: center;">
            <h3>Editar mi usuario</h3>
        </div>
        <form action="/SGH/public/layouts/modules/miUsuario/controllers/miUsuarioForm.php" method="post" style="align-items: center;">
            <input type="hidden" name="idUser" id="idUser" value="<?php echo $user->getId(); ?>">
            <div>
                <label for="apellido">Apellido</label>
                <input type="text" name="apellido" id="apellido" style="width: 95%;" value="<?php echo $user->getApellido(); ?>">
            </div>
            <div>
                <label for="nombre">Nombre</label>
                <input type="text" name="nombre" id="nombre" style="width: 95%;" value="<?php echo $user->getNombre(); ?>">
            </div>
            <div>
                <label for="dni">DNI</label>
                <input type="text" name="dni" id="dni" style="width: 95%;" oninput="formatNumber(this)" value="<?php echo $user->getDni(); ?>">
            </div>
            <div>
                <label for="updatePassword">Contraseña</label>
                <input type="password" name="updatePassword" id="updatePassword" style="width: 95%;">
            </div>
            <div style="display: flex; flex-direction: row; width: 94%;">
                <div style="width: 50%; display: flex; flex-direction: column; align-items: center;">
                    <label for="mn">Matricula nacional</label>
                    <input type="text" name="mn" id="mn" style="width: 85%;" value="<?php echo $user->getMn(); ?>">
                </div>
                <div style="width: 50%; display: flex; align-items: center; align-items: center;">
                    <label for="mp">Matricula provincial</label>
                    <input type="text" name="mp" id="mp" style="width: 85%;" value="<?php echo $user->getMp(); ?>">
                </div>
            </div>

            <div style="width: 95%; text-align: center;">
            <button class="btn-green"><i class="fa-solid fa-pencil"></i> Editar perfil</button>
            </div>
        </form>
    </div>

</div>

<script src="/SGH/public/layouts/modules/miUsuario/js/miUsuario.js"></script>
<?php require_once '../../base/footer.php'; ?>