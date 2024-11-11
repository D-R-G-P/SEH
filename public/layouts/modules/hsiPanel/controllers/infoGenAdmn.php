<?php

require_once '../../../../../app/db/db.php';

$db = new DB();
$pdo = $db->connect();

// Verificar si se recibió el DNI a través de la solicitud POST
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['dni'])) {
    // Recibir el DNI enviado desde la solicitud AJAX
    $dni = $_POST['dni'];

    $queryInfo = "SELECT hsi.*, p.nombre AS nombre_persona, p.apellido AS apellido_persona FROM hsi LEFT JOIN personal AS p ON hsi.dni COLLATE utf8mb4_spanish2_ci = p.dni COLLATE utf8mb4_spanish2_ci WHERE hsi.dni = :dni";


    $stmtInfo = $pdo->prepare($queryInfo);
    $stmtInfo->bindParam(':dni', $dni, PDO::PARAM_INT);
    $stmtInfo->execute();

    while ($rowInfo = $stmtInfo->fetch(PDO::FETCH_ASSOC)) {
        if ($rowInfo['residente'] == "si") {
            echo '<div style="position: absolute; top: 2vw;
    left: 5.6vw;" class="btn-tematico"><i class="fa-solid fa-user-graduate"></i> Residente</div>';
        }

        echo '<form id="infoForm" style="overflow-y: hidden; max-height: max-content;" action="/SGH/public/layouts/modules/hsiPanel/controllers/agentForm.php" method="post">';
        echo '<table style="max-width: max-content;">';
        echo '<thead>';
        echo '<tr>';

        echo '<th colspan="2"><div style="display: flex;
    flex-direction: row;
    flex-wrap: nowrap;
    justify-content: space-between;
    align-items: center;"><p style="color: #6ebcc5;">' . $rowInfo['id'] . '</p> <p>Datos del agente</p> 
        <div style="display: flex; flex-wrap: nowrap; flex-direction: row; justify-content: flex-end; align-items: center; width: fit-content;">
    <a class="btn-tematico" style="border: .2vw #fff solid;
    border-radius: .8vw; justify-content: center;
    display: flex;
    align-items: center;" href="https://shc.ms.gba.gov.ar/institucion/484/pacientes/profile/' . $rowInfo['id_persona'] . '" target="_blank"><img src="/SGH/public/resources/image/hsiLogo.svg" style="width: 1.5vw;
    height: auto;"></img></a>
    <a class="btn-green" href="https://wa.me/549' . $rowInfo['telefono'] . '" target="_blank"><i class="fa-brands fa-whatsapp"></i></a>
    <button class="btn-green"><i class="fa-solid fa-floppy-disk"></i></button></div></div></th>';

        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';

        echo '<tr>';
        echo '<td class="table-middle">Nombre del agente</td>';
        echo '<td class="table-middle">' .  $rowInfo['apellido_persona'] . ' ' . $rowInfo['nombre_persona'] . '</td>';
        echo '</tr>';

        echo '<tr>';
        echo '<td class="table-middle">Documento del agente</td>';
        echo '<td class="table-middle">' . $rowInfo['dni'] . '</td>';
        echo '<input type="hidden" name="dni" value="' . $rowInfo['dni'] . '"></input>';
        echo '</tr>';

        echo '<script>
                    
            </script>
            ';

        echo '<tr>';
        echo '<td class="table-middle">Mail</td>';
        echo '<td class="table-middle"><input type="email" onchange="marcarCambio()" style="width: 100%;" name="mail" id="mail" value="' . $rowInfo['mail'] . '"></input></td>';
        echo '</tr>';

        echo '<tr>';
        echo '<td class="table-middle">Telefono</td>';
        echo '<td class="table-middle"><input onchange="marcarCambio()" type="tel" style="width: 100%;" name="phone" id="phone" value="' . $rowInfo['telefono'] . '"></input></td>';
        echo '</tr>';

        echo '<tr>';
        echo '<td class="table-middle">Servicio</td>';
        echo '<td class="table-middle">
                        <select name="servicioSelect" id="servicioSelect" class="select2" style="width: 16vw; transform: translateY(-0.5vw);" required>
                            <option value="" selected disabled>Seleccionar un servicio...</option>';

        // Realiza la consulta a la tabla servicios
        $getServicio = "SELECT id, servicio FROM servicios WHERE estado = 'Activo'";
        $stmtServicio = $pdo->query($getServicio);

        // Itera sobre los resultados y muestra las opciones en el select
        while ($row = $stmtServicio->fetch(PDO::FETCH_ASSOC)) {
            echo '<option value="' . $row['id'] . '">' . $row['servicio'] . '</option>';
        }
        echo '</select>
                <style>
                .select2-container--default .select2-selection--single, .select2-container--default .select2-selection--multiple .select2-selection__arrow {
                    transform: translateY(0vw);
                }
                </style>';
        echo '</td>';

        echo '</tr>';

        echo '<tr>';
        echo '<td class="table-middle">Fecha de solicitud</td>';
        $fechaSol = date("d/m/Y", strtotime($rowInfo['fecha_solicitud']));
        echo '<td class="table-middle">' . $fechaSol . '</td>';
        echo '</tr>';

        echo '<tr>';
        echo '<td class="table-middle">ID de persona</td>';
        echo '<td class="table-middle"><input onchange="marcarCambio()" type="number" style="width: 100%;" name="idPersona" id="idPersona" value="' . $rowInfo['id_persona'] . '"></input></td>';
        echo '</tr>';

        echo '<tr>';
        echo '<td class="table-middle">Nombre de usuario</td>';
        echo '<td class="table-middle"><input onchange="marcarCambio()" type="text" style="width: 100%;" name="nombreUsuario" id="nombreUsuario" value="' . $rowInfo['nombre_usuario'] . '"></input></td>';
        echo '</tr>';

        echo '<tr>';
        echo '<td class="table-middle">ID del usuario</td>';
        echo '<td class="table-middle"><input onchange="marcarCambio()" type="number" style="width: 100%;" name="idUsuario" id="idUsuario" value="' . $rowInfo['id_usuario'] . '"></input></td>';
        echo '</tr>';

        echo '</tbody>';
        echo '</table>';
        echo '</form>';

        echo '<table>';
        echo '<thead>';
        echo '<tr>';
        echo '<th colspan=2>Documentos y permisos <button onclick="addDocs(\'' . $rowInfo['dni'] . '\')" class="btn-green" style="position: relative; right: -18%;"><i class="fa-solid fa-file-arrow-up"></i></button></th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';

        $documentos_array = json_decode($rowInfo['documentos'], true);

        if ($documentos_array !== null) {
            foreach ($documentos_array as $documentoInfo) {
                $documento = $documentoInfo['documento'];
                $activo = $documentoInfo['activo'];

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

                switch ($documento) {
                    case 'Copia de DNI':
                        $documento_row = 'docsDni.pdf';
                        break;
                    case 'Copia de matrícula profesional':
                        $documento_row = 'docsMatricula.pdf';
                        break;
                    case 'Solicitud de alta de usuario para HSI (ANEXO I)':
                        $documento_row = 'docsAnexoI.pdf';
                        break;
                    case 'Declaración Jurada - Convenio de confidecialidad usuarios HSI (ANEXO II)':
                        $documento_row = 'docsAnexoII.pdf';
                        break;
                    case 'Declaración Jurada - Usuario prescriptor':
                        $documento_row = 'docsPrescriptor.pdf';
                        break;
                }

                switch ($activo) {
                    case 'no':
                        $simbolo =  'No subido';
                        $abrirBtn = '';
                        $verificarBtn = '';
                        break;
                    case 'pendiente':
                        $simbolo = 'Pendiente de verificación';
                        $abrirBtn = '</br><a target="_blank" class="btn-green" title="Ver archivo" href="/SGH/app/hsiDocs/' . $rowInfo["dni"] . '-' . $documento_row . '"><i class="fa-solid fa-file-lines"></i></a>';
                        $verificarBtn = '<button class="btn-green" onclick="verificDoc(\'' . $rowInfo['dni'] . '\', \'' . $documento . '\', \'verificar\', \'' . $rowInfo['servicio'] . '\')" title="Marcar como verificado"><i class="fa-solid fa-check"></i> Marcar como verificado</button>';
                        break;
                    case 'verificado':
                        $simbolo = '<div style="display: flex; flex-direction: column;">Verificado';
                        $abrirBtn = '<a style="text-decoration: none; margin: .3vw .5vw;" target="_blank" class="btn-green" title="Ver archivo" href="/SGH/app/hsiDocs/' . $rowInfo["dni"] . '-' . $documento_row . '"><i class="fa-solid fa-file-lines"></i> Visualizar documento subido</a>';
                        $verificarBtn = '<div><button class="btn-yellow" onclick="verificDoc(\'' . $rowInfo['dni'] . '\', \'' . $documento . '\', \'desverificar\', \'' . $rowInfo['servicio'] . '\')"" title="Marcar como pendiente">Pasar a pendiente</button>
                                <button class="btn-red" onclick="verificDoc(\'' . $rowInfo['dni'] . '\', \'' . $documento . '\', \'anular\', \'' . $rowInfo['servicio'] . '\')"" title="Marcar como no subido">Pasar a faltante</button></div></div>';
                        break;
                    default:
                        $simbolo = 'Error';
                        $abrirBtn = '';
                        $verificarBtn = '';
                        break;
                }

                // Imprimir el nombre del documento y el símbolo en las dos columnas del grid
                echo '<tr>';
                echo '<td class="table-middle">' . $documento_nombre . ':</td>';
                echo '<td class="table-middle">' . $simbolo . ' ' . $abrirBtn . ' ' . $verificarBtn . '</td>';
                echo '</tr>';
            }
        }

        echo '<td colspan=2 class="table-middle table-left" style="width: max-content;"><div style="display: grid; grid-template-columns: auto min-content;
                align-items: center; justify-content: start;">';

        // Consulta para obtener todos los roles activos o los roles inactivos que el usuario ya tiene asignados
        $getRolesAct = "
    SELECT r.*, urh.id_rol AS usuario_tiene_rol 
    FROM roles_hsi r
    LEFT JOIN usuarios_roles_hsi urh ON r.id = urh.id_rol AND urh.dni = :dni
    WHERE r.estado = 'activo' OR urh.id_rol IS NOT NULL
";

        // Preparar la consulta para obtener los roles
        $stmtRolesAct = $pdo->prepare($getRolesAct);
        $stmtRolesAct->bindParam(':dni', $rowInfo['dni']);

        // Ejecutar la consulta
        $stmtRolesAct->execute();

        // Recorrer todos los roles que cumplen la condición
        while ($row = $stmtRolesAct->fetch(PDO::FETCH_ASSOC)) {
            // Si el usuario tiene este rol (usuario_tiene_rol no es null), mostrar el botón verde o gris
            if ($row['usuario_tiene_rol']) {
                $buttonClass = ($row['estado'] === 'activo') ? 'btn-green' : 'btn-green';
                echo $row['rol'] . ' <button class="' . $buttonClass . '" onclick="modifyPermiso(\'' . $rowInfo['dni'] . '\', \'' . $row['id'] . '\', \'' . $rowInfo['servicio'] . '\', \'si\')"><i class="fa-solid fa-check"></i></button><br>';
            } else {
                // Si el usuario no tiene el rol y este es activo, mostrar el botón rojo
                echo $row['rol'] . ' <button class="btn-red" onclick="modifyPermiso(\'' . $rowInfo['dni'] . '\', \'' . $row['id'] . '\', \'' . $rowInfo['servicio'] . '\')"><i class="fa-solid fa-xmark"></i></button><br>';
            }
        }




        echo '</div></td>';

        echo '</tbody>';
        echo '</table>';

        echo '<table>';
        echo '<thead>';
        echo '<tr>';
        echo '<th>Menú de acciones</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        echo '<tr>';
        echo '<td class="table-center" style="height: max-content">';
        echo '<button class="btn-red" onclick="buttonNoti(\'' . $rowInfo['dni'] . '\', \'baja\')"><i class="fa-solid fa-user-xmark"></i> Notificar baja de usuario</button>';
        echo '<button class="btn-yellow" onclick="buttonNoti(\'' . $rowInfo['dni'] . '\', \'password\')"><i class="fa-solid fa-user-lock"></i> Notificar reinicio de contraseña</button>';
        echo '<button class="btn-green" onclick="buttonNoti(\'' . $rowInfo['dni'] . '\', \'habilita\')"><i class="fa-solid fa-user-check"></i> Notificar habilitación de usuario</button>';
        echo '<button class="btn-tematico" onclick="buttonNoti(\'' . $rowInfo['dni'] . '\', \'habilitar\')"><i class="fa-solid fa-user-graduate"></i> Notificar habilitación como residente</button>';

        echo '</br><div class="modulo" style="width: 100%;"><h4><u>Pedido</u></h4></br> ' . $rowInfo["pedido"] . '</div>';

        echo '</td>';
        echo '</tr>';
        echo '<tr>';

        echo '<td style="height: 100%;">';
        echo '<form id="obserForm" action="/SGH/public/layouts/modules/hsiPanel/controllers/observacionForm.php" style="display: flex; justify-content: center; align-items: center; flex-direction: column;" method="post">';
        echo '<h4 style="margin-bottom: .3vw;">Agregar observación</h4>';
        echo '<input type="hidden" name="dniInfo" value="' . $rowInfo['dni'] . '">';
        echo '<div style="display: flex; flex-direction: row; justify-content: center;"><input type="checkbox" name="notiCheck" style="margin-right: .2vw;"></input> Realizar notificación</div>';
        echo '<div style="display: flex; flex-direction: row; justify-content: center;"><input type="checkbox" name="habiCheck" style="margin-right: .2vw;"></input> Habilitar usuario</div>';
        echo '<textarea onchange="marcarCambio()" name="observacionInfo" id="observacionInfo" style="width: 90%; height: 13vw; resize: none;">' . $rowInfo['observaciones'] . '</textarea>';
        echo '<button class="btn-green">Agregar/Modificar observación</button>';
        echo '</form>';
        echo '</td>';
        echo '</tr>';
        echo '</tbody>';
        echo '</table>';
    }
}
