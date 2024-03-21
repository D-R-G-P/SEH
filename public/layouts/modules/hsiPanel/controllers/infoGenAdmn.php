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
                echo '<form style="overflow-y: hidden; max-height: max-content;" action="#" method="post">';
                echo '<table style="max-width: max-content;">';
                echo '<thead>';
                echo '<tr>';
                echo '<th colspan="2">Datos del agente <button style="position: relative; right: -26%;" class="btn-green"><i class="fa-solid fa-floppy-disk"></i></button></th>';
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
                echo '</tr>';

                echo '<tr>';
                echo '<td class="table-middle">Mail</td>';
                echo '<td class="table-middle"><input type="email" style="width: 100%;" name="mail" id="mail" value="' . $rowInfo['mail'] . '"></input></td>';
                echo '</tr>';

                echo '<tr>';
                echo '<td class="table-middle">Telefono</td>';
                echo '<td class="table-middle"><input type="tel" style="width: 100%;" name="phone" id="phone" value="' . $rowInfo['telefono'] . '"></input></td>';
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
                echo '<td class="table-middle"><input type="number" style="width: 100%;" name="idPersona" id="idPersona" value="' . $rowInfo['id_persona'] . '"></input></td>';
                echo '</tr>';

                echo '<tr>';
                echo '<td class="table-middle">Nombre de usuario</td>';
                echo '<td class="table-middle"><input type="text" style="width: 100%;" name="nombreUsuario" id="nombreUsuario" value="' . $rowInfo['nombre_usuario'] . '"></input></td>';
                echo '</tr>';

                echo '<tr>';
                echo '<td class="table-middle">ID del usuario</td>';
                echo '<td class="table-middle"><input type="number" style="width: 100%;" name="idUsuario" id="idUsuario" value="' . $rowInfo['id_usuario'] . '"></input></td>';
                echo '</tr>';

                echo '</tbody>';
                echo '</table>';
                echo '</form>';

                echo '<table>';
                echo '<thead>';
                echo '<tr>';
                echo '<th colspan=2>Documentos y permisos</th>';
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
                                $verificarBtn = '<button class="btn-green" title="Marcar como verificado"><i class="fa-solid fa-check"></i> Marcar como verificado</button>';
                                break;
                            case 'verificado':
                                $simbolo = 'Verificado';
                                $abrirBtn = '</br><a target="_blank" class="btn-green" title="Ver archivo" href="/SGH/app/hsiDocs/' . $rowInfo["dni"] . '-' . $documento_row . '"><i class="fa-solid fa-file-lines"></i></a>';
                                $verificarBtn = '<button class="btn-red" title="Marcar como sin verificar"><i class="fa-solid fa-xmark"></i></button>';
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
                $permisosInfo_array = json_decode($rowInfo['permisos'], true);

                if ($permisosInfo_array !== null) {
                    foreach ($permisosInfo_array as $permisoInfo) {
                        $permiso = $permisoInfo['permiso'];
                        $activo = $permisoInfo['activo'];

                        switch ($activo) {
                            case 'si':
                                $permisoBtn = '<button class="btn-green"><i class="fa-solid fa-check"></i></button>';
                                break;
                            case 'no':
                                $permisoBtn = '<button class="btn-red"><i class="fa-solid fa-xmark"></i></button>';
                                break;
                        }

                        $icono = '<i class="fa-solid fa-chevron-right"></i>';

                        echo $permiso . ' ' . $permisoBtn;
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
                echo '<button class="btn-red" onclick="buttonSol(\'' . $rowInfo['dni'] . '\', \'baja\')"><i class="fa-solid fa-user-xmark"></i> Notificar baja de usuario</button>';
                echo '<button class="btn-yellow" onclick="buttonSol(\'' . $rowInfo['dni'] . '\', \'password\')"><i class="fa-solid fa-user-lock"></i> Notificar reinicio de contraseña</button>';
                
                echo '</br><div class="modulo" style="width: 100%;"><h4><u>Pedido</u></h4></br> '.$rowInfo["pedido"].'</div>';
                
                echo '</td>';
                echo '</tr>';
                echo '<tr>';

                echo '<td>';
                echo '<form action="/SGH/public/layouts/modules/hsiPanel/controllers/pedidoForm.php" id="infoForm" style="display: flex; justify-content: center; align-items: center; flex-direction: column;" method="post">';
                echo '<h4 style="margin-bottom: .3vw;">Agregar observación</h4>';
                echo '<input type="hidden" name="dniInfo" value="' . $rowInfo['dni'] . '">';
                echo '<div style="display: flex; flex-direction: row; justify-content: center;"><input type="checkbox" name="notiCheck" style="margin-right: .2vw;"></input> Realizar notificación</div>';
                echo '<textarea name="pedidoInfo" id="pedidoInfo" style="width: 90%; height: 13vw; resize: none;" required></textarea>';
                echo '<button class="btn-green">Agregar observación</button>';
                echo '</form>';
                echo '</td>';
                echo '</tr>';
                echo '</tbody>';
                echo '</table>';
            }
        }
        ?>