<?php
require_once __DIR__ . '/../../config.php';
// require_once BASE_PATH . '/app/db/roleManager.php';

$user = new User();  // Suponemos que la clase User está cargada
$currentUser = $userSession->getCurrentUser(); // Suponemos que $userSession está disponible

// Verificar si el usuario está logueado
if (!$userSession->getCurrentUser()) {
    header("Location: " . getBaseURL() . "/index.php"); // Redirección usando la función getBaseURL
    exit();
}

// Set user instance
$user->setUser($currentUser);

// Asegura que las rutas locales se resuelvan correctamente
$basePath = realpath(dirname(__DIR__));

// Función para generar las URLs relativas
?>

<!DOCTYPE html>
<html lang="es-AR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>S.G.H. - <?php echo $title ?></title>
    <link rel="icon" href="/SGH/node_modules/@fortawesome/fontawesome-free/svgs/solid/notes-medical.svg"
        type="image/svg+xml">

    <meta name="author" content="Cristian Lamas Jonathan">

    <!-- FontAwesome -->
    <script src="/SGH/node_modules/@fortawesome/fontawesome-free/js/all.js"></script>

    <!-- JQuery -->
    <script src="/SGH/node_modules/jquery/dist/jquery.min.js"></script>

    <!-- Select2 -->
    <link rel="stylesheet" href="/SGH/node_modules/select2/dist/css/select2.min.css">
    <script src="/SGH/node_modules/select2/dist/js/select2.min.js"></script>

    <link rel="stylesheet" href="/SGH/public/resources/css/base.css">
    <link rel="stylesheet" href="/SGH/public/resources/css/header.css">

    <link rel="stylesheet" href="<?php echo CSS_PATH ?>base.css">
    <link rel="stylesheet" href="<?php echo CSS_PATH ?>header.css">
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
                <img src="<?php echo IMG_PATH ?>hsmlogoheadersvg.svg" alt="HIGA San Martín - Logo">
                <h2>H I G A </br> General San Martín</h2>
            </div>
        </div>

    </header>

    <!-- Panel Desplegable con Botón flotante -->
    <div id="errorReportPanel" class="error-report">
        <div id="reportButton" class="floating-button" onclick="toggleErrorReport()">
            <i class="fa-solid fa-chevron-left" style="margin-left: .2vw;"></i>
            <i class="fa-solid fa-bug"></i>
            <i class="fa-solid fa-chevron-right"></i>
            <span>Reportar un error</span>
        </div>

        <div class="error-report-header">
            <h3>Reportar un Error</h3>
        </div>
        <div class="error-report-body">
            <label for="errorDescription">Descripción del Error:</label>
            <input type="hidden" name="user" id="user" value="<?= $user->getDni() ?>">
            <textarea id="errorDescription" name="errorDescription" placeholder="Describe el error..."></textarea>
            <button class="btn-tematico" onclick="sendErrorReport()">Enviar</button>
        </div>
    </div>

    <article>

        <div id="toast-container" class="notisContent"></div>

        <?php
        if (isset($_SESSION['toast_message'])) {
            $message = $_SESSION['toast_message']['message'];
            $type = $_SESSION['toast_message']['type'];
            $duration = $_SESSION['toast_message']['duration'] ?? '';
            echo "<script>document.addEventListener('DOMContentLoaded', function() { toast('$message', '$type', $duration); });</script>";
            unset($_SESSION['toast_message']); // Eliminar la variable de sesión después de usarla
        }
        ?>


        <div class="headerLeft open">

            <div class="info open">
                <p>Bienvenido/a</p>
                <p style="white-space: nowrap;"><?php echo $user->getApellido() . ' ' . $user->getNombre(); ?></p>
            </div>

            <div class="sistemas">
                <hr>
                <a href="<?php echo BASE_PATH ?>/index.php" class="header" title="Inicio"><i
                        class="fa-solid fa-house"></i>
                    <p class="headerLeftP open">Inicio</p>
                </a>

                <?php if (hasAccess(['administrador', 'direccion', 'tab_mando'])): ?>
                    <a href="<?php echo MODULE_PATH ?>tableroPanel/tableroPanel.php" class="header"
                        title="Tablero de mando">
                        <i class="fa-solid fa-house-medical-flag"></i>
                        <p class="headerLeftP open">Tablero de mando</p>
                    </a>
                <?php endif; ?>

                <?php if (hasAccess(['administrador', 'direccion', 'gest_personal'])): ?>
                    <a href="<?php echo MODULE_PATH ?>personalPanel/personal.php" class="header"
                        title="Gestión de personal">
                        <i class="fa-solid fa-hospital-user"></i>
                        <p class="headerLeftP open">Gestión de personal</p>
                    </a>
                <?php endif; ?>

                <?php if (hasAccess(['administrador', 'direccion', 'gestion_roles'])): ?>
                    <a href="<?php echo MODULE_PATH ?>gestion_roles/roles.php" class="header" title="Gestión de roles">
                        <i class="fa-solid fa-user-cog"></i>
                        <p class="headerLeftP open">Gestión de roles</p>
                    </a>
                <?php endif; ?>

                <?php if (hasAccess(['administrador', 'direccion', 'gestion_turnos'])): ?>
                    <a href="<?php echo MODULE_PATH ?>gestion_turnos/gestion_turnos.php" class="header" title="Gestión de turnos">
                    <i class="fa-solid fa-calendar-days"></i>
                        <p class="headerLeftP open">Gestión de turnos</p>
                    </a>
                <?php endif; ?>

                <?php if (hasAccess(['administrador', 'direccion', 'gestion_camas'])): ?>
                    <a href="<?php echo MODULE_PATH ?>gestion_camas/gestion_camas.php" class="header" title="Gestión de camas">
                    <i class="fa-solid fa-bed"></i>
                        <p class="headerLeftP open">Gestión de camas</p>
                    </a>
                <?php endif; ?>

                <?php if (hasAccess(['administrador', 'direccion', 'hsi'])): ?>
                    <a href="<?php echo MODULE_PATH ?>hsiPanel/hsi.php" class="header" title="Solicitudes HSI">
                        <img src="<?php echo IMG_PATH ?>hsiLogo.svg" alt="HSI logo" style="width: 1.5vw; height: auto">
                        <p class="headerLeftP open">Solicitudes de HSI</p>
                    </a>
                <?php endif; ?>

                <?php if (hasAccess(['administrador', 'direccion', 'guardias'])): ?>
                    <a href="<?php echo MODULE_PATH ?>guardiasPanel/guardias.php" class="header"
                        title="Esquema de guardias">
                        <i class="fa-solid fa-calendar-week"></i>
                        <p class="headerLeftP open">Esquema de guardias</p>
                    </a>
                <?php endif; ?>

                <?php if (hasAccess(['administrador', 'direccion', 'inf_equipos'])): ?>
                    <a href="<?php echo MODULE_PATH ?>equipos/equipos.php" class="header" title="Informes de equipos">
                        <i class="fa-solid fa-x-ray"></i>
                        <p class="headerLeftP open">Informes de equipos</p>
                    </a>
                <?php endif; ?>

                <?php if (hasAccess(['administrador', 'camillero'])): ?>
                    <a href="<?php echo MODULE_PATH ?>camilleros/camilleros.php" class="header" title="Camilleros">
                        <img src="<?php echo IMG_PATH ?>camilla.svg" alt="Camilla logo"
                            style="width: 1.5vw; height: auto; color: #fff;">
                        <p class="headerLeftP open">Camilleros</p>
                    </a>
                <?php endif; ?>

                <?php if (hasAccess(['administrador', 'direccion', 'mantenimiento'])): ?>
                    <a href="<?php echo MODULE_PATH ?>mantenimiento/mantenimiento.php" class="header" title="Mantenimiento">
                        <i class="fa-solid fa-screwdriver-wrench"></i>
                        <p class="headerLeftP open">Mantenimiento</p>
                    </a>
                <?php endif; ?>

                <?php if (hasAccess(['administrador', 'direccion', 'arquitectura'])): ?>
                    <a href="<?php echo MODULE_PATH ?>arquitectura/arquitectura.php" class="header" title="Arquitectura">
                        <i class="fa-solid fa-compass-drafting"></i>
                        <p class="headerLeftP open">Arquitectura</p>
                    </a>
                <?php endif; ?>

                <?php if (hasAccess(['administrador', 'direccion', 'administracion'])): ?>
                    <a href="<?php echo MODULE_PATH ?>adminPanel/adminPanel.php" class="header" title="Administración">
                        <i class="fa-solid fa-hammer"></i>
                        <p class="headerLeftP open">Administración</p>
                    </a>
                <?php endif; ?>

                <?php if (hasAccess(['developer'])): ?>
                    <a href="<?php echo MODULE_PATH ?>developer/developer.php" class="header" title="Desarrollador">
                        <i class="fa-solid fa-code"></i>
                        <p class="headerLeftP open">Desarrollador</p>
                    </a>
                <?php endif; ?>

                <hr>
            </div>

            <div class="user">
                <a href="<?php echo MODULE_PATH ?>miUsuario/miUsuario.php" class="header" title="Mi usuario">
                    <i class="fa-solid fa-user"></i>
                    <p class="headerLeftP open">Mi usuario</p>
                </a>
                <a href="<?php echo BASE_PATH ?>/app/db/logout.php" class="logout" title="Cerrar sesión">
                    <i class="fa-solid fa-power-off"></i>
                    <p class="headerLeftP open">Cerrar sesión</p>
                </a>
            </div>

        </div>