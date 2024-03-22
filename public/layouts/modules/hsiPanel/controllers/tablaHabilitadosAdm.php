<?php
require_once '../../../../../app/db/db.php';

session_start(); // Iniciar sesión para usar variables de sesión

// Función para obtener el total de registros en la tabla
function getTotalRows($pdo)
{
    $stmt = $pdo->query("SELECT COUNT(*) AS total FROM hsi WHERE estado = 'habilitado'");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row['total'];
}

// Establecer el número de registros por página y calcular el total de páginas
$perPage = 10; // Cambia este valor según tus necesidades
$totalRows = getTotalRows($pdo);
$totalPages = ceil($totalRows / $perPage);

// Obtener el número de página actual
if (isset($_GET['page']) && $_GET['page'] > 0 && $_GET['page'] <= $totalPages) {
    $currentPage = $_GET['page'];
} else {
    $currentPage = 1;
}

// Calcular el desplazamiento para la consulta SQL
$offset = ($currentPage - 1) * $perPage;

// Realizar la consulta SQL para obtener los registros de la página actual
$queryAproved = "SELECT * FROM hsi WHERE estado = 'habilitado' LIMIT :perPage OFFSET :offset";
$stmtAproved = $pdo->prepare($queryAproved);
$stmtAproved->bindValue(':perPage', $perPage, PDO::PARAM_INT);
$stmtAproved->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmtAproved->execute();

// Mostrar la tabla con los registros
echo '<div style="display: flex; flex-direction: row; margin-bottom: 1vw; justify-content: center; margin-top: .5vw;">';
echo '<select name="servicioFilter" id="servicioFilter" class="select2" style="width: 30vw;">';
echo '<option value="">Sin filtro por servicio</option>';
$getPersonal = "SELECT id, servicio FROM servicios";
$stmt = $pdo->query($getPersonal);
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo '<option value=' . $row['id'] . '>' . $row['servicio'] . '</option>';
}
echo '</select>';
echo '<input type="text" name="inputFilter" id="inputFilter">';
echo '</div>';
echo '<table id="habilitado">';
echo '<thead>';
echo '<tr>';
echo '<th>ID</th>';
echo '<th>Apellido</th>';
echo '<th>Nombre</th>';
echo '<th>DNI</th>';
echo '<th>Servicio</th>';
echo '<th>Permisos</th>';
echo '<th>Acciones</th>';
echo '</tr>';
echo '</thead>';
echo '<tbody>';

while ($rowAproved = $stmtAproved->fetch(PDO::FETCH_ASSOC)) {
    echo '<tr>';
    echo '<td class="table-center table-middle">' . $rowAproved['id'] . '</td>';

    $stmtDniAproved = $pdo->prepare("SELECT nombre, apellido FROM personal WHERE dni = ?");
    $stmtDniAproved->execute([$rowAproved['dni']]);
    $rowDatAproved = $stmtDniAproved->fetch(PDO::FETCH_ASSOC);

    if ($rowDatAproved) {
        echo '<td class="table-center table-middle">' . $rowDatAproved['apellido'] . '</td>';
        echo '<td class="table-center table-middle">' . $rowDatAproved['nombre'] . '</td>';
    } else {
        echo '<td colspan="2">Error al obtener los datos</td>';
    }

    echo '<td class="table-center table-middle">' . $rowAproved['dni'] . '</td>';

    $stmtServicioAproved = $pdo->prepare("SELECT servicio FROM servicios WHERE id = ?");
    $stmtServicioAproved->execute([$rowAproved['servicio']]);
    $rowServicioAproved = $stmtServicioAproved->fetch(PDO::FETCH_ASSOC);

    if ($rowServicioAproved) {
        echo '<td class="table-center table-middle">' . $rowServicioAproved['servicio'] . '</td>';
    } else {
        echo '<td>Error al obtener los datos</td>';
    }

    echo '<td class="table-left table-middle">';
    $permisoAproved_array = json_decode($rowAproved['permisos'], true);

    if ($permisoAproved_array !== null) {
        $permisos_activos = [];
        foreach ($permisoAproved_array as $permisAproved) {
            $nombre_permiso = $permisAproved['permiso'];
            $activo = $permisAproved['activo'];

            if ($activo == "si") {
                $permisos_activos[] = '<div style="width: max-content;"><i class="fa-solid fa-chevron-right"></i> ' . $nombre_permiso;
            }
        }
        echo implode('</div>', $permisos_activos);
    }
    echo '</td>';

    echo '<td class="table-center table-middle">
        <button class="btn-green" onclick="loadInfo(\'' . $rowAproved['dni'] . '\')"><i class="fa-solid fa-hand-pointer"></i></button>
    </td>';

    echo '</tr>';
}

echo '</tbody>';
echo '</table>';

// Mostrar los enlaces de paginación
echo '<div class="pagination">';
echo '<ul>';
for ($i = 1; $i <= $totalPages; $i++) {
    echo '<li><a href="?page=' . $i . '">' . $i . '</a></li>';
}
echo '</ul>';
echo '</div>';