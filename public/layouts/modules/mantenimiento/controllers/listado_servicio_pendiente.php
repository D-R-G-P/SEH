<?php
require_once '../../../../../app/db/db.php'; // Asegúrate de que la ruta sea correcta

// Conexión a la base de datos
$db = new DB();
$pdo = $db->connect();

if (isset($_GET['servicioFilter'])) {
    $servicioFilter = $_GET['servicioFilter'];
    
    $queryP = "SELECT * FROM mantenimiento WHERE servicio = :servicioFilter AND estado_reclamante = 'Pendiente'";
    $stmtP = $pdo->prepare($queryP);
    $stmtP->bindParam(':servicioFilter', $servicioFilter, PDO::PARAM_INT);

    try {
        $stmtP->execute();

        if ($stmtP->rowCount() == 0) {
            echo '<tr><td colspan="6" style="text-align: center;">No hay notificaciones pendientes</td></tr>';
        } else {
            while ($rowP = $stmtP->fetch(PDO::FETCH_ASSOC)) {
                echo '<tr>';

                echo '<td class="table-center table-middle">' . $rowP['id'] . '</td>';

                $stmtServicioP = $pdo->prepare("SELECT servicio FROM servicios WHERE id = ?");
                $stmtServicioP->execute([$rowP['servicio']]);
                $rowServicioP = $stmtServicioP->fetch(PDO::FETCH_ASSOC);

                if ($rowServicioP) {
                    echo '<td class="table-center table-middle">' . $rowServicioP['servicio'] . '</td>';
                } else {
                    echo '<td>Error al obtener los datos</td>';
                }

                $date = new DateTime($rowP['fecha']);
                $formattedDate = $date->format('d/m/Y H:i');

                echo '<td class="table-center table-middle">' . $formattedDate . '</td>';

                echo '<td class="table-center table-middle">' . $rowP['estado_reclamante'] . '</td>';

                echo '<td class="table-center table-middle">' . $rowP['problema'] . '</td>';

                echo '<td class="table-center table-middle">
                        <button onclick="checkNews(' . $rowP['id'] . ')" class="btn-green" title="Marcar como visto"><i class="fa-solid fa-hand-pointer"></i></button>
                    </td>';

                echo '</tr>';
            }
        }
    } catch (PDOException $e) {
        echo '<tr><td colspan="6" style="text-align: center;">Error en la base de datos: ' . htmlspecialchars($e->getMessage()) . '</td></tr>';
    }
} else {
    echo '<tr><td colspan="6" style="text-align: center;">Parámetro de servicio no proporcionado</td></tr>';
}
?>
