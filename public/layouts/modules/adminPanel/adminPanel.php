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
<link rel="stylesheet" href="/SGH/public/layouts/modules/adminPanel/css/adminPanel.css">


<div class="content">
    <?php

    if ($user->getRol() === "Administrador") {

    ?>


        <div class="modulo" style="flex-direction: column; text-align: center;">
            <h3>Panel de administración</h3>
            <p>Este modulo esta orientado a la configuración del sistema.</p>
        </div>

        <div class="modulo">
            <div>
                <button class="btn-green"><b><i class="fa-solid fa-plus"></i> Agregar servicio</b></button>

                <div class="back">
                    <form action="">
                        <div>
                            <label for="servicio">Nombre del servicio</label>
                            <input type="text" name="servicio" id="servicio">
                        </div>

                        <div>
                            <label for="jefe">Jefe del servicio</label>
                            <input type="text" name="servicio" id="servicio">
                        </div>

                        
                    </form>
                </div>
            </div>
            <table>
                <thead>
                    <tr>
                        <th class="table-center table-middle">ID</th>
                        <th>Servicio</th>
                        <th>Jefe</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="table-center table-middle">a</td>
                        <td>b</td>
                        <td>c</td>
                        <td>d</td>
                        <td>e</td>
                    </tr>
                </tbody>
            </table>

        </div>


    <?php

    } else {
        echo 'Acceso denegado, no cuenta con los permisos para acceder a este sistema.';
    }

    ?>
</div>


<?php require_once '../../base/footer.php'; ?>