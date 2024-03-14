<?php
// Incluir la conexión a la base de datos
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

// Consulta para obtener los resultados paginados con término de búsqueda
$query = "SELECT * FROM personal WHERE estado = 'Activo'";

// Agregar el filtro de búsqueda si se proporciona un término de búsqueda válido
if (!empty($searchTerm)) {
    $query .= " AND (dni LIKE :searchTerm OR nombre LIKE :searchTerm2 OR apellido LIKE :searchTerm3)";

    // Si se proporciona un valor para el servicio, buscar por servicio_id
    if (!empty($selectServicioFilter)) {
        $query .= " AND servicio_id = :selectServicioFilter";
    }
}

// Agregar LIMIT y OFFSET para la paginación
$query .= " LIMIT :offset, :limit";
$stmt = $pdo->prepare($query);

// Bindear los valores de los parámetros de búsqueda y paginación
if (!empty($searchTerm)) {
    $searchTerm = "%" . $searchTerm . "%";
    $stmt->bindValue(':searchTerm', $searchTerm, PDO::PARAM_STR);
    $stmt->bindValue(':searchTerm2', $searchTerm, PDO::PARAM_STR);
    $stmt->bindValue(':searchTerm3', $searchTerm, PDO::PARAM_STR);
}

if (!empty($selectServicioFilter)) {
    $stmt->bindValue(':selectServicioFilter', $selectServicioFilter, PDO::PARAM_STR);
}

$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':limit', $resultados_por_pagina, PDO::PARAM_INT);

// Ejecutar la consulta
$stmt->execute();





// Mostrar los resultados en formato JSON
echo '<thead>
<tr>
  <th class="table-middle table-center">ID</th>
  <th class="table-middle">Nombre y apellido</th>
  <th class="table-middle table-center">DNI</th>
  <th class="table-middle">Servicio</th>
  <th class="table-middle">Especialidad</th>
  <th class="table-middle table-center">Matricula</th>
  <th class="table-middle table-center">Cargo</th>
  <th class="table-middle table-center">Sistemas</th>
  <th class="table-middle table-center">Rol</th>
  <th class="table-middle table-center">Acciones</th>
</tr>
</thead>';

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo '<tr>';
    echo '<td class="table-center table-middle">' . $row['id'] . '</td>';

    $fechaHoy = date("Y-m-d");

    $stmtLicencias = $pdo->prepare("SELECT tipo_licencia, fecha_desde, fecha_hasta FROM licencias WHERE dni = ? AND fecha_desde <= ? AND fecha_hasta >= ?");
    $stmtLicencias->execute([$row['dni'], $fechaHoy, $fechaHoy]);
    $licencias = $stmtLicencias->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($licencias)) {
        echo '<td class="table-middle">';
        echo '<div style="display: flex; flex-direction: row; align-items: center;"><div>' . $row['apellido'] . ' ' . $row['nombre'] . '</div>';

        echo '<button class="avisoWarButton" onclick="avisoLicencia(' . $row['id'] . ')">
      <i class="fa-solid fa-triangle-exclamation"></i>
    </button>';

        foreach ($licencias as $licencia) {
            // Formatear las fechas
            $fecha_desde = date("d/m/Y", strtotime($licencia['fecha_desde']));
            $fecha_hasta = date("d/m/Y", strtotime($licencia['fecha_hasta']));

            echo '<div id="aviso-' . $row['id'] . '" class="avisoWar" style="position: relative">
      
      <div class="aviso"><h4>El agente se encuentra de licencia.</h4></br>
          <b>Tipo de licencia:</b> ' . $licencia['tipo_licencia'] . '. </br>
          <div style="margin-top: .3vw;"><b>Desde:</b> ' . $fecha_desde . '</br><b>Hasta:</b> ' . $fecha_hasta . '.</div>
      </div>
      
  </div>';
        }

        echo '</div></td>';
    } else {
        echo '<td class="table-middle">' . $row['apellido'] . ' ' . $row['nombre'] . '</td>';
    }

    echo '<td class="table-middle table-center">' . $row['dni'] . '</td>';

    if ($row['servicio_id'] != "0") {
        // Realiza una consulta para obtener el nombre y apellido del jefe de servicio
        $getservicioQuery = "SELECT servicio FROM servicios WHERE id = ?";
        $getservicioStmt = $pdo->prepare($getservicioQuery);
        $getservicioStmt->execute([$row['servicio_id']]);
        $servicioInfo = $getservicioStmt->fetch(PDO::FETCH_ASSOC);
        // Muestra el nombre y apellido del jefe de servicio
        if ($servicioInfo) {
            echo '<td class="table-middle">' . $servicioInfo['servicio'] . '</td>';
        } else {
            echo '<div>No se encontró la información del servicio</div>';
        }
    } else {
        echo '<td class="table-middle"> No hay servicio asignado';
    }
    echo '</td>';

    echo '<td class="table-middle"> ' . $row['especialidad'] . '</td>';

    echo '<td class="table-middle"> M.N: ' . $row['mn'] . ' </br> M.P: ' . $row['mp'] . '</td>';

    echo '<td class="table-middle"> ' . $row['cargo'] . '</td>';

    echo '<td class="table-middle"><div style="display: grid; grid-template-columns: repeat(2, 1fr); align-content: center;
    justify-content: center;
    align-items: center;
    justify-items: center;">';


    // Suponiendo que $sistemas es el array que contiene los datos de la base de datos
    // Convertir la cadena JSON en un array PHP
    $sistemas_array = json_decode($row['sistemas'], true);

    // Verificar si la conversión fue exitosa
    if ($sistemas_array !== null) {
        // Iterar sobre el array de sistemas
        foreach ($sistemas_array as $sistema) {
            // Acceder a cada propiedad del objeto sistema
            $nombre_sistema = $sistema['sistema'];
            $activo = $sistema['activo'];

            // Determinar la clase del botón en función del estado activo
            $claseBoton = ($activo == 'si') ? 'btn-green' : 'btn-red';

            // Determinar el icono del botón en función del nombre del sistema
            switch ($nombre_sistema) {
                case 'Deposito':
                    $icono = 'fa-box';
                    $variables = "" . $row['id'] . ", 'Deposito', '" . $activo . "'";
                    break;
                case 'Mantenimiento':
                    $icono = 'fa-screwdriver-wrench';
                    $variables = "" . $row['id'] . ", 'Mantenimiento', '" . $activo . "'";
                    break;
                case 'Informatica':
                    $icono = 'fa-computer';
                    $variables = "" . $row['id'] . ", 'Informatica', '" . $activo . "'";
                    break;
                default:
                    $icono = 'fa-question';
                    break;
            }
            // Imprimir el botón con la clase y el icono correspondientes
            echo '<button style="text-align: center; width: 2.7vw; height: 2.7vw" title="' . $nombre_sistema . '" class="' . $claseBoton . '" onclick="updateSistem(' . $variables . ')"><i class="fa-solid ' . $icono . '"></i></button>';
        }
    } else {
        // Manejar el caso en el que la conversión de JSON falla
        echo 'Error al decodificar la cadena JSON.';
    }


    // Botón "Generar contraseña" fuera del bucle foreach
    echo '<button style="width: 2.7vw; height: 2.7vw" class="btn-yellow" title="Generar contraseña" onclick="updatePassword(' . $row['id'] . ', \'' . $row['dni'] . '\')"><i class="fa-solid fa-key"></i></button>';


    echo '</div></td>';


    echo '<td class="table-middle"> ' . $row['rol'] . '</td>';

    echo '<td class="table-middle table-center">
    
          <div class="contenedor-de-botones">

            <button class="btn-green" title="Abrir menu de acciones"  onclick="menuPersona(' . $row['id'] . ')"><i class="fa-solid fa-hand-pointer"></i></button>
          
            <div class="buttons-div" id="menu-' . $row['id'] . '">
          
              <button class="btn-green" title="Editar" onclick="setDatos(\'' . $row['id'] . '\', \'' . $row['apellido'] . '\', \'' . $row['nombre'] . '\', \'' . $row['dni'] . '\', \'' . $row['servicio_id'] . '\', \'' . $row['cargo'] . '\', \'' . $row['especialidad'] . '\', \'' . $row['mn'] . '\', \'' . $row['mp'] . '\', \'' . $row['rol'] . '\')"><i class="fa-solid fa-pen"></i> Editar</button>
              
              <button class="btn-green" title="Pase" onclick="setDatosPase(\'' . $row['id'] . '\', \'' . $row['apellido'] . '\', \'' . $row['nombre'] . '\', \'' . $row['dni'] . '\')"><i class="fa-solid fa-right-from-bracket"></i> Pase</button>
              
              <button class="btn-green" title="Licencias" onclick="setLicencia(\'' . $row['apellido'] . '\', \'' . $row['nombre'] . '\', \'' . $row['dni'] . '\')"><i class="fa-solid fa-person-walking-luggage"></i> Licencias</button>
              
              <button class="btn-yellow" title="Jubilar" onclick="setDatosJubilar(\'' . $row['apellido'] . '\', \'' . $row['nombre'] . '\', \'' . $row['dni'] . '\')"><i class="fa-solid fa-person-walking-with-cane"></i> Jubilar</button>
              
              <button class="btn-yellow" title="Fin contrato" onclick="setDatosFinContrato(\'' . $row['apellido'] . '\', \'' . $row['nombre'] . '\', \'' . $row['dni'] . '\')"><i class="fas fa-calendar-times"></i> Fin contrato</button>
            
              </div>
          </div>
        </td>';

    echo '</tr>';
}