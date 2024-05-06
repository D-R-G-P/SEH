<?php
require_once '../../../../../app/db/db.php';
$db = new DB();
$pdo = $db->connect();

$query = "SELECT id, marca, modelo, tipo, estado, problema FROM equipos ORDER BY tipo";

$stmt = $pdo->prepare($query);
$stmt->execute();

if ($stmt->rowCount() == 0) {
    echo 'No hay equipos registrados';
} else {
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

        echo '<div class="modulo tarjetaEquipo">';

        echo '<div class="firsti">';
        echo '<div class="top"><b>' . $row['tipo'] . '</b></div>';
        echo '<div class="bottom">' . $row['marca'] . ' - ' . $row['modelo'] . '</div>';
        echo '</div>';

        echo '<hr style="margin: .5vw;">';

        echo '<div class="seccond">';

        switch ($row['estado']) {
            case 'operativo':
                $style = "operativo";
                $text = "Equipo operativo";
                break;
            case 'problema':
                $style = "problema";
                $text = "Equipo con problema";
                break;
            case 'desoperativo':
                $style = "desoperativo";
                $text = "Equipo no operativo";
                break;
            default:
                $style = "";
                $text = "Error";
                break;
        }

        echo '<div class="estado">';
        echo '<div class="' . $style . '">' . $text . '</div>';
        echo '</div>';
        if ($row['estado'] != "operativo") {
            $alert_id = "alert_" . $row['id'];
            echo '<button id="btn_' . $row['id'] . '" style="width: 2.5vw; height: 2.5vw; justify-content: center; border-radius: .8vw;" class="btn-yellow" onclick="toggleAlert(\'' . $alert_id . '\')"><i style="margin: 0;" class="fa-solid fa-exclamation"></i></button>';

            echo '<div id="' . $alert_id . '" class="alertSign" style="display: none;">
        <b>Problema reportado</b>
        <p>' . $row['problema'] . '</p>
    </div>';
        }
        echo '</div>';

        echo '</div>';
    }
}
?>