<?php

    
$user = new User();
$currentUser = $userSession->getCurrentUser();
$user->setUser($currentUser);

// Verificar si el usuario está actualmente logueado
if (!$userSession->getCurrentUser()) {
    // Si no hay un usuario en la sesión, redirigir a la página de inicio
    header("Location: /SGH/index.php");
    exit(); // Asegurarse de que el script se detenga después de redirigir
}

?>

<!DOCTYPE html>
<html lang="es-AR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>S.G.H. - <?php echo $title ?></title>
    <!-- Sistema de emergentes hospitalarios -->
    <link rel="icon" href="/SGH/node_modules/@fortawesome/fontawesome-free/svgs/solid/notes-medical.svg" type="image/svg+xml">

    <!-- FontAwesome -->
    <script src="/SGH/node_modules/@fortawesome/fontawesome-free/js/all.js"></script>

    <!-- JQuery -->
    <script src="/SGH/node_modules/jquery/dist/jquery.min.js"></script>

    <!-- Select2 -->
    <link rel="stylesheet" href="/SGH/node_modules/select2/dist/css/select2.min.css">
    <script src="/SGH/node_modules/select2/dist/js/select2.min.js"></script>

    <link rel="stylesheet" href="/SGH/public/resources/css/base.css">
    <link rel="stylesheet" href="/SGH/public/resources/css/header.css">
</head>

<body>

    <header>

        <div class="first">
            <div class="bars">
                <button onclick="toggleMenu()"><i class="fa-solid fa-bars"></i></button>
            </div>

            <div class="titleLogo">
                <i class="fa-solid fa-notes-medical iconoLogo"></i>
                <h1>Sistema de Gestión Hospitalaria</h1>
            </div>
        </div>

        <div class="seccond">
            <div class="logoHSM">
                <img src="/SGH/public/resources/image/hsmlogoheadersvg.svg" alt="HIGA San Martín - Logo">
                <h2>H I G A </br> General San Martín</h2>
            </div>
        </div>

    </header>

    <article>

        <?php
        if (isset($_SESSION['success_message'])) {
            echo '<div class="success-message">' . $_SESSION['success_message'] . '</div>';
            // Borrar el mensaje de éxito de la variable de sesión para no mostrarlo nuevamente
            unset($_SESSION['success_message']);
        }
        if (isset($_SESSION['error_message'])) {
            echo '<div class="error-message">' . $_SESSION['error_message'] . '</div>';
            // Borrar el mensaje de éxito de la variable de sesión para no mostrarlo nuevamente
            unset($_SESSION['error_message']);
        }
        ?>

        <div class="headerLeft open">

            <div class="info open">
                <p>Bienvenido/a</p>
                <p><?php echo $user->getApellido() . ' ' . $user->getNombre(); ?></p>
            </div>

            <div class="sistemas">

                <hr>
                <a href="/SGH/index.php" class="header" title="Inicio"><i class="fa-solid fa-house"></i>
                    <p class="headerLeftP open">Inicio</p>
                </a>
                <a href="" class="header" title="Tablero de mando"><i class="fa-solid fa-house-medical-flag"></i>
                    <p class="headerLeftP open">Tablero de mando</p>
                </a>
                <a href="/SGH/public/layouts/modules/personalPanel/personal.php" class="header" title="Gestión de personal"><i class="fa-solid fa-hospital-user"></i>
                    <p class="headerLeftP open">Gestión de personal</p>
                </a>
                <a href="/SGH/public/layouts/modules/hsiPanel/hsi.php" class="header" title="Solicitudes HSI"><img src="/SGH/public/resources/image/hsiLogo.svg" alt="HSI logo" style="width: 1.5vw; height: auto">
                    <p class="headerLeftP open">Solicitudes de HSI</p>
                </a>
                <a href="" class="header" title="Informes de turnos"><i class="fa-regular fa-calendar"></i>
                    <p class="headerLeftP open">Informes de turnos</p>
                </a>
                <a href="" class="header" title="Informes de camas"><i class="fa-solid fa-bed"></i>
                    <p class="headerLeftP open">Informes de camas</p>
                </a>
                <a href="" class="header" title="Informes de stock"><i class="fa-solid fa-box"></i>
                    <p class="headerLeftP open">Control de stock</p>
                </a>
                <a href="" class="header" title="Informes de equipos"><i class="fa-solid fa-hard-drive"></i>
                    <p class="headerLeftP open">Informes de equipos</p>
                </a>
                <a href="" class="header" title="Mantenimiento"><i class="fa-solid fa-screwdriver-wrench"></i>
                    <p class="headerLeftP open">Mantenimiento</p>
                </a>
                <a href="/SGH/public/layouts/modules/adminPanel/adminPanel.php" class="header" title="Patrimoniales"><i class="fa-solid fa-clipboard-check"></i></i>
                    <p class="headerLeftP open">Patrimoniales</p>
                </a>
                <a href="/SGH/public/layouts/modules/adminPanel/adminPanel.php" class="header" title="Informatica"><i class="fa-solid fa-computer"></i>
                    <p class="headerLeftP open">Informática</p>
                </a>
                <a href="/SGH/public/layouts/modules/adminPanel/adminPanel.php" class="header" title="Administración"><i class="fa-solid fa-hammer"></i>
                    <p class="headerLeftP open">Administración</p>
                </a>
                <hr>
            </div>

            <div class="user">
                <a href="" class="header" title="Mi usuario"><i class="fa-solid fa-user"></i>
                    <p class="headerLeftP open">Mi usuario</p>
                </a>
                <a href="/SGH/app/db/logout.php" class="logout" title="Cerrar sesión"><i class="fa-solid fa-power-off"></i>
                    <p class="headerLeftP open">Cerrar sesión</p>
                </a>
            </div>

        </div>