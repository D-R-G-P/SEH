<?php

$title = "Inicio";

// Verificar si el usuario está actualmente logueado
if ($user->getPr() == "si") {
    // Si no hay un usuario en la sesión, redirigir a la página de inicio
    $_SESSION['success_message'] = '<div class="notisContent"><div class="notis" id="notis">Por favor, modifique su contraseña.</div></div><script>setTimeout(() => {notis.classList.toggle("active");out();}, 1);function out() {setTimeout(() => {notis.classList.toggle("active");}, 2500);}</script>';
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
        <a href="/SGH/public/resources/docs/Menú de usuario.pdf" class="btn-tematico" target="_blank" style="text-decoration: none; color: #fff; display: flex; align-items: center;"><i style="font-size: 2.5vw;" class="fa-solid fa-file-pdf"></i> <b style="margin-left: 1vw;">Manúal de usuario</b></a>
    </div>

    <div class="modulo" style="text-align: center;">
        <h3>Actualizaciónes</h3>

        <div style="margin-top: 1vw;" class="modulo">
            <h4 style="text-align: start;">Version Beta 1.0.3</h4>
            <p style="margin-top: .5vw; text-align: start;">Se ha implementado la funcionalidad inicial de los modulos Inicio, Gestión de personal, Solicitudes de HSI (lado solicitable y lado administrador), Administración (lado dirección y lado servicios) y Mi Usuario.</p>
        </div>
    </div>
</div>


<?php require_once 'layouts/base/footer.php' ?>