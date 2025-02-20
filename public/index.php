<?php

require_once 'app/db/db.php';
require_once 'app/db/user_session.php';
require_once 'app/db/user.php';
require_once 'config.php';

$user = new User();
$currentUser = $userSession->getCurrentUser();
$user->setUser($currentUser);
$db = new DB();
$pdo = $db->connect();

$title = "Inicio";

// Verificar si el usuario está actualmente logueado
if ($user->getPr() == "si") {
    // Si no hay un usuario en la sesión, redirigir a la página de inicio
    $_SESSION['toast_message'] = [
        'message' => 'Por favor, modifique su contraseña.',
        'type' => 'warning'
    ];
    header("Location: /SGH/public/layouts/modules/miUsuario/miUsuario.php");
    exit(); // Asegurarse de que el script se detenga después de redirigir
}

?>

<?php require_once 'layouts/base/header.php' ?>
<link rel="stylesheet" href="/SGH/public/resources/css/index.css">



<div class="content">
    <div class="modulo" style="text-align: center;">
        <h3>Bienvenido/a <?php echo $user->getApellido() . ' ' . $user->getNombre(); ?></h3>
        <p>Sistema de Gestión Hospitalaria</p>
    </div>

    <div class="modulo" style="text-align: center;">
        <h3>Descargas</h3>
        <a href="/SGH/public/resources/docs/Menú de usuario.pdf" class="btn-tematico" target="_blank"
            style="text-decoration: none; color: #fff; display: flex; align-items: center;"><i style="font-size: 2.5vw;"
                class="fa-solid fa-file-pdf"></i> <b style="margin-left: 1vw;">Manúal de usuario</b></a>
    </div>

    <div class="modulo" style="text-align: center;">
        <h3>Actualizaciónes</h3>

        <?php
        $query = "SELECT * FROM updates ORDER BY id DESC LIMIT 5";
        $stmt = $pdo->prepare($query);
        $stmt->execute();

        if ($stmt->rowCount() == 0) {
            // Si no hay resultados con estado ''
            echo '<div class="modulo">No hay ninguna update para mostrar</div>';
        } else {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                ?>

                <div class="modulo" style="margin-top: 1vw;">
                    <h4 style="text-align: .5vw; text-align: start;">Version <?= $row['version'] ?></h4>
                    <p style="margin-top: .5vw; text-align: start;"><?= $row['descripcion'] ?></p>
                </div>

                <?php
            }
        }
        ?>
    </div>
</div>


<?php require_once 'layouts/base/footer.php' ?>