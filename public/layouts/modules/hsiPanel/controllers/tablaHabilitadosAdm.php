<?php
require_once '../../../../../app/db/db.php';

// Crear una instancia de la clase DB
$db = new DB();
$pdo = $db->connect();

// Definir el número de resultados por página
$resultados_por_pagina = 10;

// Obtener el término de búsqueda (si se proporciona)
$searchTerm = isset($_GET['searchTerm']) ? $_GET['searchTerm'] : '';

$selectServicioFilter = isset($_GET['selectServicioFilter']) ? $_GET['selectServicioFilter'] : '';

// Obtener el número de página actual
$pagina = isset($_GET['pagina']) ? $_GET['pagina'] : 1;

// Calcular el desplazamiento
$offset = ($pagina - 1) * $resultados_por_pagina;

// Consulta para obtener los resultados paginados con término de búsqueda y filtro de servicio
$query = "SELECT hsi.*, s.servicio AS servicio, p.nombre AS nombre, p.apellido AS apellido
          FROM hsi 
          LEFT JOIN servicios AS s ON hsi.servicio = s.id
          LEFT JOIN personal AS p ON hsi.dni COLLATE utf8mb4_spanish2_ci = p.dni COLLATE utf8mb4_spanish2_ci 
          WHERE hsi.estado = 'habilitado'";

// Si se proporciona un valor para el servicio, buscar por servicio_id
if (!empty($selectServicioFilter)) {
    $query .= " AND hsi.servicio = :selectServicioFilter";
}

// Agregar el filtro de búsqueda por DNI si se proporciona un término de búsqueda válido
if (!empty($searchTerm)) {
    $query .= " AND (hsi.dni LIKE :searchTerm)";
}

// Consulta para obtener el número total de resultados de personal activo con los filtros aplicados
$queryTotal = "SELECT COUNT(*) AS total FROM hsi 
               LEFT JOIN personal AS p ON hsi.dni COLLATE utf8mb4_spanish2_ci = p.dni COLLATE utf8mb4_spanish2_ci 
               WHERE hsi.estado = 'habilitado'";

// Agregar el filtro de búsqueda por DNI si se proporciona un término de búsqueda válido
if (!empty($searchTerm)) {
    $queryTotal .= " AND (hsi.dni LIKE :searchTerm)";
}

// Si se proporciona un valor para el servicio, buscar por servicio_id
if (!empty($selectServicioFilter)) {
    $queryTotal .= " AND hsi.servicio = :selectServicioFilter";
}

// Preparar la consulta para obtener el número total de resultados
$stmtTotal = $pdo->prepare($queryTotal);

// Bindear los valores de los parámetros de búsqueda
if (!empty($searchTerm)) {
    $searchTerm = "%" . $searchTerm . "%";
    $stmtTotal->bindValue(':searchTerm', $searchTerm, PDO::PARAM_STR);
}

if (!empty($selectServicioFilter)) {
    $stmtTotal->bindValue(':selectServicioFilter', $selectServicioFilter, PDO::PARAM_STR);
}

// Ejecutar la consulta para obtener el número total de resultados
$stmtTotal->execute();
$total_resultados = $stmtTotal->fetchColumn();

// Calcular el número total de páginas
$total_paginas = ceil($total_resultados / $resultados_por_pagina);

// Agregar LIMIT y OFFSET para la paginación
$query .= " LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($query);

// Bindear los valores de los parámetros de búsqueda y paginación
if (!empty($searchTerm)) {
    $stmt->bindValue(':searchTerm', $searchTerm, PDO::PARAM_STR);
}

if (!empty($selectServicioFilter)) {
    $stmt->bindValue(':selectServicioFilter', $selectServicioFilter, PDO::PARAM_STR);
}

$stmt->bindValue(':limit', $resultados_por_pagina, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

// Ejecutar la consulta
$stmt->execute();


echo '<table id="habilitado">

                <thead>
                    <tr>

                        <th>ID</th>
                        <th>Apellido</th>
                        <th>Nombre</th>
                        <th>DNI</th>
                        <th>Servicio</th>
                        <th>Permisos</th>
                        <th>Acciones</th>

                    </tr>
                </thead>
                <tbody>';

if ($stmt->rowCount() == 0) {
    // Si no hay resultados con estado 'Aproved'
    echo '<tr><td colspan="7">No hay usuarios habilitados</td></tr>';
} else {
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo '<tr>';

        echo '<td class="table-center table-middle">' . $row['id'] . '</td>';

        echo '<td class="table-center table-middle">' . $row['apellido'] . '</td>';
        echo '<td class="table-center table-middle">' . $row['nombre'] . '</td>';

        echo '<td class="table-center table-middle">' . $row['dni'] . '</td>';

        echo '<td class="table-center table-middle">' . $row['servicio'] . '</td>';

        echo '<td class="table-left table-middle">';
        $permisoAproved_array = json_decode($row['permisos'], true);

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
                <button class="btn-green" onclick="loadInfo(\'' . $row['dni'] . '\')"><i class="fa-solid fa-hand-pointer"></i></button>
            </td>';

        echo '</tr>';
    }
}

echo '</tbody>
</table>';

// Mostrar los botones de paginación
echo '<div class="pagination">';
for ($i = 1; $i <= $total_paginas; $i++) {
    $claseBoton = ($pagina == $i) ? 'active' : '';
    echo '<button class="' . $claseBoton . ' btn-green buttonPagination" onclick="cambiarPagina(' . $i . ')">Página ' . $i . '</button>';
}
echo '</div>';
