<?php

require_once '../../../app/db/user_session.php';
require_once '../../../app/db/user.php';
require_once '../../../app/db/user.php';

$user = new User();
$userSession = new UserSession();
$currentUser = $userSession->getCurrentUser();
$user->setUser($currentUser);

$title = "Gestion de personal"


?>

<?php
require_once '../base/header.php';

require_once '../base/footer.php'
?>