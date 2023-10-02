<?php
session_set_cookie_params(3600);
include_once 'app/login/user.php';
include_once 'app/login/user_session.php';

$userSession = new UserSession();
$user = new User();
$errorLogin = "";

if (isset($_SESSION['user'])) {
    // Si está iniciada, va a include
    $user->setUser($userSession->getCurrentUser());
    include_once 'public/index.php';
} else if (isset($_POST['username']) && isset($_POST['password'])) {
    // echo "Validación de login";

    $userForm = $_POST['username'];
    $passForm = $_POST['password'];

    $user = new User();
    if($user->userExists($userForm, $passForm)) {
        // echo "usuario validado";
        $userSession->setCurrentUser($userForm);
        $user->setUser($userForm);

        include_once 'public/index.php';
    } else {
        // echo "Nombre de usuario o contraseña incorrecto";
        $errorLogin = "Nombre de usuario o contraseña incorrecto";
        include_once 'login.php';
    }

} else {
    // echo "Login";
    include_once 'login.php';
}

?>