<?php

require_once 'user_session.php';

$userSession = new UserSession();
$userSession->closeSession();

header('Location: /SGH/index.php');
