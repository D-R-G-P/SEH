<?php
header('Content-Type: application/json');
include_once '../../../../../app/db/db.php'; // Conexión a la DB

$db = new DB();
$pdo = $db->connect();

if (!isset($_POST['u_padre']) || empty($_POST['u_padre'])) {
    echo json_encode(['error' => 'ID de unidad padre no proporcionado']);
    exit;
}

$u_padre = intval($_POST['u_padre']);

function obtenerRecorrido($conexion, $u_padre) {
    $recorrido = [];
    $idsRecorrido = [];

    while ($u_padre) {
        $sql = "SELECT id, nombre, u_padre FROM arquitectura WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(1, $u_padre, PDO::PARAM_INT);
        $stmt->execute();
        $stmt->execute();
        
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            array_unshift($recorrido, $row['nombre']);
            array_unshift($idsRecorrido, $row['id']);
            $u_padre = $row['u_padre']; // Subimos un nivel en la jerarquía
        } else {
            break;
        }
    }

    return ['recorrido' => $recorrido, 'ids' => $idsRecorrido];
}

$recorridoData = obtenerRecorrido($pdo, $u_padre);
echo json_encode($recorridoData);