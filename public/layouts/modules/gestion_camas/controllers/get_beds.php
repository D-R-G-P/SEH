<?php

require_once '../../../../../app/db/db.php';
require_once '../../../../../app/db/user_session.php';
require_once '../../../../../app/db/user.php';
require_once '../../../../config.php';

header('Content-Type: application/json');

$db = new DB();
$pdo = $db->connect();

$user = new User();
$userSession = new UserSession();
$currentUser = $userSession->getCurrentUser();
$user->setUser($currentUser);

$location_id = isset($_GET['location_id']) ? $_GET['location_id'] : null;

if ($location_id) {
    try {
        $query = "SELECT id, name, complexity, bed_status FROM beds WHERE ubicacion_arquitectura_id = :location_id AND bed_status NOT IN ('eliminado', 'Ocupada', 'Bloqueada') ORDER BY name ASC";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':location_id', $location_id, PDO::PARAM_INT);
        $stmt->execute();
        $beds = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode([
            'success' => true,
            'beds' => $beds
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
        exit;
    }
} else {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'location_id parameter is required'
    ]);
}