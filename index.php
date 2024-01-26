<?php
session_set_cookie_params(3600);
require_once 'app/db/user.php';
require_once 'app/db/user_session.php';

$userSession = new UserSession();
$user = new User();
$errorLogin = "";

if (isset($_SESSION['user'])) {
    // Si está iniciada, va a require
    $user->setUser($userSession->getCurrentUser());
    require_once 'public/index.php';
} else if (isset($_POST['dni']) && isset($_POST['password'])) {
    // echo "Validación de login";

    $dniForm = $_POST['dni'];
    $passForm = $_POST['password'];

    $user = new User();
    if ($user->userExists($dniForm, $passForm)) {
        // echo "usuario validado";
        $userSession->setCurrentUser($dniForm);
        $user->setUser($dniForm);

        require_once 'public/index.php';
    } else {
        // echo "Nombre de usuario o contraseña incorrecto";
        $errorLogin = "Número de documento o contraseña incorrecto";
        require_once 'login.php';
    }
} else {
    // echo "Login";
    require_once 'login.php';
}
