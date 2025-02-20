<?php
require_once '../../../app/db/db.php';
require_once '../../../app/db/user_session.php';
require_once '../../../app/db/user.php';

header('Content-Type: application/json');

$userSession = new UserSession();
$currentUser = $userSession->getCurrentUser();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['user'], $_POST['description']) || empty(trim($_POST['user'])) || empty(trim($_POST['description']))) {
        echo json_encode(['success' => false, 'error' => 'Todos los campos son requeridos']);
        exit;
    }

    $user = trim($_POST['user']);
    $description = trim($_POST['description']);

    try {
        $db = new DB();
        $pdo = $db->connect();
        
        $sql = "INSERT INTO error_report (user, description, estado) VALUES (:user, :description, 'new')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['user' => $user, 'description' => $description]);

        echo json_encode(['success' => true]);
        exit;
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Error en la base de datos: ' . $e->getMessage()]);
        exit;
    }
}
