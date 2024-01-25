<?php

require_once 'app/db/db.php';
require_once 'app/login/user.php';
require_once 'app/login/user_session.php';

$db = new DB();
$pdo = $db->connect();

?>

<!DOCTYPE html>
<html lang="es-AR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>S.G.H. - <?php echo $title ?></title>
    <!-- Sistema de emergentes hospitalarios -->
    <link rel="icon" href="/SGH/node_modules/@fortawesome/fontawesome-free/svg/notes-medical-solid.svg" type="image/svg+xml">

    <link rel="stylesheet" href="/SGH/public/resources/css/base.css">
    <link rel="stylesheet" href="/SGH/public/resources/css/header.css">

    <!-- FontAwesome -->
    <script src="/SGH/node_modules/@fortawesome/fontawesome-free/js/all.js"></script>
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
                <img src="/SGH/public/resources/image/hsmlogosol.png" alt="HIGA San Martín - Logo">
                <h2>H I G A </br> General San Martín</h2>
            </div>
        </div>

    </header>

    <div class="headerLeft open">

        <div class="info open">
            <p>Bienvenido/a</p>
            <p><?php echo $user->getApellido() . ' ' . $user->getNombre(); ?></p>
        </div>

        <div class="sistemas">

            <hr>
            <a href="" class="header" title="Inicio"><i class="fa-solid fa-house"></i>
                <p class="headerLeftP open">Inicio</p>
            </a>
            <a href="" class="header" title="Tablero de mando"><i class="fa-solid fa-house-medical-flag"></i>
                <p class="headerLeftP open">Tablero de mando</p>
            </a>
            <a href="" class="header" title="Gestión de personal"><i class="fa-solid fa-hospital-user"></i>
                <p class="headerLeftP open">Gestión de personal</p>
            </a>
            <a href="" class="header" title="Informes de turnos"><i class="fa-regular fa-calendar"></i>
                <p class="headerLeftP open">Informes de turnos</p>
            </a>
            <a href="" class="header" title="Informes de camas"><i class="fa-solid fa-bed"></i>
                <p class="headerLeftP open">Informes de camas</p>
            </a>
            <a href="" class="header" title="Informes de stock"><i class="fa-solid fa-box"></i>
                <p class="headerLeftP open">Informes de stock</p>
            </a>
            <a href="" class="header" title="Informes de equipos"><i class="fa-solid fa-hard-drive"></i>
                <p class="headerLeftP open">Informes de equipos</p>
            </a>

            <hr>
        </div>

        <div class="user">
            <a href="" class="header" title="Mi usuario"><i class="fa-solid fa-user"></i>
                <p class="headerLeftP open">Mi usuario</p>
            </a>
            <a href="" class="logout" title="Cerrar sesión"><i class="fa-solid fa-power-off"></i>
                <p class="headerLeftP open">Cerrar sesión</p>
            </a>
        </div>

    </div>

    <script>
        function toggleMenu() {
            const toToggle = document.getElementsByClassName("headerLeftP");
            const toToggleInfo = document.getElementsByClassName("info")[0];
            const headerToggle = document.getElementsByClassName("headerLeft")[0];

            // Lógica para realizar el toggle entre las clases open y close para "headerLeftP"
            for (let i = 0; i < toToggle.length; i++) {
                if (toToggle[i].classList.contains("open")) {
                    toToggle[i].classList.remove("open");
                    toToggle[i].classList.add("close");
                } else {
                    toToggle[i].classList.remove("close");
                    toToggle[i].classList.add("open");
                }
            }

            // Lógica para realizar el toggle entre las clases open y close para "info"
            if (toToggleInfo.classList.contains("open")) {
                toToggleInfo.classList.remove("open");
                toToggleInfo.classList.add("close");
            } else {
                toToggleInfo.classList.remove("close");
                toToggleInfo.classList.add("open");
            }

            // Lógica para realizar el toggle entre las clases open y close para "info"
            if (toToggleInfo.classList.contains("open")) {
                headerToggle.classList.remove("open");
                headerToggle.classList.add("close");
            } else {
                headerToggle.classList.remove("close");
                headerToggle.classList.add("open");
            }
        }
    </script>