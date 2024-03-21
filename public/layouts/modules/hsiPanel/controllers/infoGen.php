<table style="max-width: max-content;">
    <thead>
        <tr>
            <th colspan="2">Datos del agente</th>
        </tr>
    </thead>
    <tbody>
        <?php

        require_once '../../../../../app/db/db.php';

        $db = new DB();
        $pdo = $db->connect();

        // Verificar si se recibió el DNI a través de la solicitud POST
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['dni'])) {
            // Recibir el DNI enviado desde la solicitud AJAX
            $dni = $_POST['dni'];

            $queryInfo = "SELECT hsi.*, p.nombre AS nombre_persona, p.apellido AS apellido_persona, s.servicio AS nombre_servicio FROM hsi LEFT JOIN personal AS p ON hsi.dni COLLATE utf8mb4_spanish2_ci = p.dni COLLATE utf8mb4_spanish2_ci LEFT JOIN servicios AS s ON hsi.servicio = s.id WHERE hsi.dni = :dni";

            $stmtInfo = $pdo->prepare($queryInfo);
            $stmtInfo->bindParam(':dni', $dni, PDO::PARAM_INT);
            $stmtInfo->execute();

            while ($rowInfo = $stmtInfo->fetch(PDO::FETCH_ASSOC)) {
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
                echo '<td class="table-middle">' . $rowInfo['mail'] . '</td>';
                echo '</tr>';

                echo '<tr>';
                echo '<td class="table-middle">Telefono</td>';
                echo '<td class="table-middle">' . $rowInfo['telefono'] . '</td>';
                echo '</tr>';

                echo '<tr>';
                echo '<td class="table-middle">Servicio</td>';
                echo '<td class="table-middle">' . $rowInfo['nombre_servicio'] . '</td>';
                echo '</tr>';

                echo '<tr>';
                echo '<td class="table-middle">Fecha de solicitud</td>';
                $fechaSol = date("d/m/Y", strtotime($rowInfo['fecha_solicitud']));
                echo '<td class="table-middle">' . $fechaSol . '</td>';
                echo '</tr>';

                echo '<tr>';
                echo '<td class="table-middle" style="min-width: 9vw;">ID de persona</td>';
                echo '<td class="table-middle">' . $rowInfo['id_persona'] . '</td>';
                echo '</tr>';

                echo '<tr>';
                echo '<td class="table-middle">Nombre de usuario</td>';
                echo '<td class="table-middle">' . $rowInfo['nombre_usuario'] . '</td>';
                echo '</tr>';

                echo '<tr>';
                echo '<td class="table-middle">ID del usuario</td>';
                echo '<td class="table-middle">' . $rowInfo['id_usuario'] . '</td>';
                echo '</tr>';

                echo '</tbody>
              </table>
              <table>
                <thead>
                  <tr>
                    <th colspan=2>Documentos y permisos</th>
                  </tr>
                </thead>
                <tbody>';

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

                        switch ($activo) {
                            case 'no':
                                $simbolo =  'No subido';
                                break;
                            case 'pendiente':
                                $simbolo = 'Pendiente de verificación';
                                break;
                            case 'verificado':
                                $simbolo = 'Verificado';
                                break;
                            default:
                                $simbolo = 'Error';
                                break;
                        }

                        // Imprimir el nombre del documento y el símbolo en las dos columnas del grid
                        echo '<tr>';
                        echo '<td class="table-middle">' . $documento_nombre . ':</td>';
                        echo '<td class="table-middle">' . $simbolo . '</td>';
                        echo '</tr>';
                    }
                }

                echo '<td colspan=2 class="table-middle table-left" style="width: max-content;">';
                $permisosInfo_array = json_decode($rowInfo['permisos'], true);

                if ($permisosInfo_array !== null) {
                    $permisos_activos = [];
                    foreach ($permisosInfo_array as $permisoInfo) {
                        $nombre_permiso = $permisoInfo['permiso'];
                        $activo = $permisoInfo['activo'];

                        if ($activo == "si") {
                            // Determinar el símbolo a mostrar según el estado activo
                            $icono = '<i class="fa-solid fa-chevron-right"></i>';

                            // Imprimir el nombre del permiso y el icono en las dos columnas del grid
                            $permisos_activos[] =
                                '<div>' . $icono . ' ' . $nombre_permiso . '</div>';
                        }
                    }
                    echo implode('', $permisos_activos);
                }
                echo '</td>';

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
                echo '<button class="btn-green" onclick="addDocs(\'' . $rowInfo['dni'] . '\'); infoModule.style.display = \'none\';"><i class="fa-solid fa-file-arrow-up"></i> Subir documentación</button> <br>';
                echo '<button class="btn-red" onclick="buttonSol(\'' . $rowInfo['dni'] . '\', \'baja\')"><i class="fa-solid fa-user-xmark"></i> Solicitar baja de usuario</button>';
                echo '<button class="btn-yellow" onclick="buttonSol(\'' . $rowInfo['dni'] . '\', \'password\')"><i class="fa-solid fa-user-lock"></i> Solicitar reinicio de contraseña</button>';
                echo '</td>';
                echo '</tr>';
                echo '<tr>';
                echo '<td>';
                echo '<form action="/SGH/public/layouts/modules/hsiPanel/controllers/pedidoForm.php" id="infoForm" style="display: flex; justify-content: center; align-items: center; flex-direction: column;" method="post">';
                echo '<h4 style="margin-bottom: .3vw;">Realizar pedido de modificación</h4>';
                echo '<input type="hidden" name="dniInfo" value="' . $rowInfo['dni'] . '">';
                echo '<textarea name="pedidoInfo" id="pedidoInfo" style="width: 90%; height: 13vw; resize: none;" required></textarea>';
                echo '<button class="btn-green">Realizar pedido</button>';
                echo '</form>';
                echo '</td>';
                echo '</tr>';
                echo '</tbody>';
                echo '</table>';
            }
        }
        ?>