<?php

include_once '../../../../../app/db/db.php'; // Archivo que contiene la conexión a la base de datos.
$db = new DB();
$pdo = $db->connect();

if ((isset($_POST['pendientes']) || isset($_POST['activos']) || isset($_POST['deshabilitados'])) && isset($_POST['printServicio'])) {
    $pendientes = $_POST['pendientes'] ?? false;
    $activo = $_POST['activos'] ?? false;
    $deshabilitados = $_POST['deshabilitados'] ?? false;
    $servicio = $_POST['printServicio'];

    // Inicializar consulta
    $consulta = "
        SELECT 
            h.id, h.estado, h.dni, h.documentos, p.nombre, p.apellido, s.servicio 
        FROM 
            hsi h
        LEFT JOIN 
            personal p ON h.dni = p.dni
        LEFT JOIN 
            servicios s ON h.servicio = s.id
        WHERE 
            1=1
    ";

    // Filtros dinámicos
    $params = [];
    $estados = [];
    if ($pendientes) {
        $estados[] = 'working';
    }
    if ($activo) {
        $estados[] = 'habilitado';
    }
    if ($deshabilitados) {
        $estados[] = 'disabled';
    }

    // Verificar si hay estados para incluir en la consulta
    if (!empty($estados)) {
        $placeholders = implode(',', array_fill(0, count($estados), '?'));
        $consulta .= " AND h.estado IN ($placeholders)";
        $params = array_merge($params, $estados);
    }

    if ($servicio != "clr") {
        $consulta .= " AND h.servicio = ?";
        $params[] = $servicio;
    }

    // Ejecutar la consulta
    $stmt = $pdo->prepare($consulta);
    $stmt->execute($params);
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    ?>

<!DOCTYPE html>
<html lang="es-AR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGH - Impresión de informe</title>

    <style>
    @font-face {
        font-family: 'Roboto';
        src: url('/SGH/app/fonts/Roboto/Roboto-Regular.ttf') format('truetype');
        font-weight: normal;
        font-style: normal;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: "Roboto", sans-serif;
        font-size: 1.3vw;
    }

    :root {
        --color1: #417099;
        --color2: #00AEC3;
        --color3: #00c4da;
        --color4: #E81F76;
    }

    body {
        padding: 1vw;
    }

    table {
        border: 0.2vw solid #000;
        border-collapse: separate;
        border-left: 0;
        border-radius: 0.8vw;
        border-spacing: 0px;
        width: 100%;
        font-size: 1vw;
    }

    thead {
        display: table-header-group;
        border-color: inherit;
        border-collapse: separate;
        background-color: var(--color2);
        border-radius: 0.8vw;
        color: #fff;
    }

    tr {
        display: table-row;
        vertical-align: inherit;
        border-color: inherit;
    }

    th,
    td {
        padding: 0.4vw;
        vertical-align: top;
        border-left: 0.2vw solid #000;
    }

    td {
        border-top: 0.2vw solid #000;
        background-color: #e3e3e3;
    }

    thead:first-child tr:first-child th:first-child,
    tbody:first-child tr:first-child td:first-child {
        border-top-left-radius: 0.7vw;
    }

    thead:last-child tr:last-child th:first-child,
    tbody:last-child tr:last-child td:first-child {
        border-bottom-left-radius: 0.7vw;
    }

    thead:first-child tr:first-child th:last-child {
        border-top-right-radius: 0.7vw;
    }

    tbody:last-child tr:last-child td:last-child {
        border-bottom-right-radius: 0.7vw;
    }

    .table-left {
        text-align: left;
    }

    .table-center {
        text-align: center;
    }

    .table-right {
        text-align: right;
    }

    .table-top {
        vertical-align: top;
    }

    .table-middle {
        vertical-align: middle;
    }

    .table-bottom {
        vertical-align: bottom;
    }

    .btn-tematico {
        color: #fff;
        background-color: var(--color2);
        border: var(--color2);
        padding: 0.5vw;
        border-radius: 0.5vw;
        font-size: 1vw;
        margin: .5vw .5vw;
        cursor: pointer;
        transition: all 0.15s ease-in-out;
    }

    .btn-tematico:hover {
        background-color: var(--color3);
        border: var(--color3);
        transition: all 0.15s ease-in-out;
    }

    @media print {
        .btn-tematico {
            display: none;
        }
    }
</style>

<!-- FontAwesome -->
<script src="/SGH/node_modules/@fortawesome/fontawesome-free/js/all.js"></script>
</head>
<body>
<button onclick="print();" class="btn-tematico" style="position: fixed; right: 1vw; border: #000 .3vw solid; "><i class="fa-solid fa-print"></i> <b>Imprimir informe</b></button>
    <?php
        // Paso 6: Mostrar resultados obtenidos
        if ($stmt->rowCount() > 0) {
            // Crear un arreglo para almacenar los resultados agrupados por estado
            $resultadosAgrupados = [];
    
            // Agrupar los resultados por estado
            foreach ($resultados as $fila) {
                $resultadosAgrupados[$fila['estado']][] = $fila;
            }
    
            // Mostrar una tabla para cada estado
            foreach ($resultadosAgrupados as $estado => $grupo) {
                switch ($estado) {
                    case 'habilitado':
                        $estadoCorrected = "habilitados";
                        break;
                    case 'disabled':
                        $estadoCorrected = "deshabilitados";
                        break;
                    case 'working':
                        $estadoCorrected = "pendientes";
                        break;
                }
                ?>
                <h3>Usuarios <?= $estadoCorrected ?></h3>
                <table>
                    <thead>
                        <tr>
                            <th class='table-center table-middle'>ID</th>
                            <th class='table-center table-middle'>DNI</th>
                            <th>Nombre</th>
                            <th>Servicio</th>
                            <?php
    
                            if ($estado == 'habilitado' || $estado == 'working') {
                                echo '<th>Roles</th>';
                                echo '<th>Documentos</th>';
                            }
    
                            ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($grupo as $fila) { ?>
                            <tr>
                                <td class='table-center table-middle'><?= $fila['id'] ?></td>
                                <td class='table-center table-middle'><?= $fila['dni'] ?></td>
                                <td class='table-middle'><?= $fila['apellido'] . " " . $fila['nombre'] ?></td>
                                <td class='table-middle'><?= $fila['servicio'] ?></td>
    
                                <?php if ($estado == 'habilitado' || $estado == 'working') { ?>
                                    <td class='table-middle'>
                                        <?php
                                        $stmtRoles = $pdo->prepare("SELECT r.rol AS nombre_rol FROM usuarios_roles_hsi u JOIN roles_hsi r ON u.id_rol = r.id WHERE u.dni = :dni");
                                        $stmtRoles->execute([':dni' => $fila['dni']]);
                                        while ($rol = $stmtRoles->fetch(PDO::FETCH_ASSOC)) {
                                            echo '<div><i class="fa-solid fa-chevron-right"></i> ' . htmlspecialchars($rol['nombre_rol']) . '</div>';
                                        }
    
                                        echo '<td class="table-middle table-left" style="width: max-content;"><div style="display: grid; grid-template-columns: auto min-content; align-items: center;">';
    
                                        $documentos_array = json_decode($fila['documentos'], true);
    
                                        if ($documentos_array !== null) {
                                            foreach ($documentos_array as $documentoWorking) {
                                                $documento = $documentoWorking['documento'];
                                                $activo = $documentoWorking['activo'];
    
                                                // Utilizar un switch para cambiar el nombre del documento en cada caso
                                                switch ($documento) {
                                                    case 'Copia de DNI':
                                                        $documento_nombre = 'D.N.I';
                                                        break;
                                                    case 'Copia de matrícula profesional':
                                                        $documento_nombre = 'Matricula';
                                                        break;
                                                    case 'Solicitud de alta de usuario para HSI (ANEXO I)':
                                                        $documento_nombre = 'ANEXO I';
                                                        break;
                                                    case 'Declaración Jurada - Convenio de confidecialidad usuarios HSI (ANEXO II)':
                                                        $documento_nombre = 'ANEXO II';
                                                        break;
                                                    case 'Declaración Jurada - Usuario prescriptor':
                                                        $documento_nombre = 'Prescriptor';
                                                        break;
                                                    default:
                                                        $documento_nombre = $documento; // Si no hay una coincidencia, mantener el nombre original
                                                        break;
                                                }
    
                                                switch ($activo) {
                                                    case 'no':
                                                        $simbolo = '<i class="fa-solid fa-xmark"></i>';
                                                        break;
                                                    case 'pendiente':
                                                        $simbolo = '<i class="fa-regular fa-clock"></i>';
                                                        break;
                                                    case 'verificado':
                                                        $simbolo = '<i class="fa-solid fa-check"></i>';
                                                        break;
                                                    default:
                                                        $simbolo = '<i class="fa-solid fa-question"></i>';
                                                        break;
                                                }
    
                                                // Imprimir el nombre del documento y el símbolo en las dos columnas del grid
                                                echo '<div>' . $documento_nombre . ':</div>';
                                                echo '<div>' . $simbolo . '</div>';
                                            }
                                        }
                                        echo '</div></td>';
                                        ?>
    
                                    </td> <?php } ?>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table><br>
            <?php }
        } else {
            ?>
            <h1>No se encontraron resultados</h1>
        <?php }
    } else { ?>
        <h1>Debe seleccionar por lo menos un elemento</h1>
    <?php } ?>
</body>
</html>