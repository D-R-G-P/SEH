<?php
// Realiza la conexión a la base de datos
require_once '../../../../../app/db/db.php';
$db = new DB();
$pdo = $db->connect();

// Realiza la consulta a la tabla tipos_equipo
$getTiposEquipo = "SELECT id, tipo_equipo FROM tipos_equipo";
$stmt = $pdo->query($getTiposEquipo);

// Genera el HTML para las opciones del select
$options = '<option value="" selected disabled>Seleccionar un tipo de equipo...</option>';
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $options .= '<option value="' . $row['id'] . '">' . $row['tipo_equipo'] . '</option>';
}

// Imprime el HTML generado
echo $options;
?>
