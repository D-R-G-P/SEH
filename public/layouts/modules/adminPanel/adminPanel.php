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


<?php

    if ()

?>


<?php require_once '../../base/footer.php'; ?>