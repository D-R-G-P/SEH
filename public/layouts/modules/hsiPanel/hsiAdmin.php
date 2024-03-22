<?php

require_once '../../../../app/db/db.php';
require_once '../../../../app/db/user_session.php';
require_once '../../../../app/db/user.php';

$user = new User();
$userSession = new UserSession();
$currentUser = $userSession->getCurrentUser();
$user->setUser($currentUser);

$title = "Administración de HSI";

$db = new DB();
$pdo = $db->connect();

?>

<?php require_once '../../base/header.php'; ?>
<link rel="stylesheet" href="/SGH/public/layouts/modules/hsiPanel/css/hsi.css">

<div class="content">

    <div class="modulo" style="text-align: center;">
        <h3 style="margin-bottom: .5vw;">Sistema de administración de HSI</h3>
        <p>Este sistema está oreintado a la gestion y administración de los </br> usuarios de HSI para los administradores institucionales.</p>
    </div>

    <div class="admInst" style="position: relative; top: -6vw; left: -29vw;">
    <a class="btn-tematico" style="text-decoration: none;" href="hsi.php"><i class="fa-solid fa-toolbox"></i> <b>Acceder a panel general</b></a>
    </div>

    <div class="back" id="back">
        <div class="divBackForm infoModule" id="infoModule" style="display: none;">
            <div class="close" style="width: 100%; display: flex; justify-content: flex-end; padding: .5vw; margin-bottom: -3.5vw;">
                <button class="btn-red" onclick="back.style.display = 'none'; infoModule.style.display = 'none'; infoForm.reset();" style="width: 2.3vw; height: 2.3vw;"><b><i class="fa-solid fa-xmark"></i></b></button>
            </div>
            <h3>Información de usuario</h3>

            <div class="cuerpoInfo" id="infoUsuario">
            </div>
        </div>
    </div>

    <div class="modulo" style="text-align: center;">
        <h4>Pendientes</h4>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Apellido</th>
                    <th>Nombre</th>
                    <th>DNI</th>
                    <th>Servicio</th>
                    <th>Permisos</th>
                    <th>Documentos</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php

                $estado = "working";

                $queryPendientes = "SELECT hsi.*, p.nombre AS nombre_persona, p.apellido AS apellido_persona, s.servicio AS nombre_servicio FROM hsi LEFT JOIN personal AS p ON hsi.dni COLLATE utf8mb4_spanish2_ci = p.dni COLLATE utf8mb4_spanish2_ci LEFT JOIN servicios AS s ON hsi.servicio = s.id WHERE hsi.estado = :estado";

                $stmtPendientes = $pdo->prepare($queryPendientes);
                $stmtPendientes->bindParam(':estado', $estado, PDO::PARAM_INT);
                $stmtPendientes->execute();

                if ($stmtPendientes->rowCount() == 0) {
                    // Si no hay resultados con estado 'news'
                    echo '<tr><td colspan="8">No hay usuarios pendientes</td></tr>';
                } else {
                    while ($rowPendientes = $stmtPendientes->fetch(PDO::FETCH_ASSOC)) {
                        echo '<tr>';
                        echo '<td class="table-middle">' . $rowPendientes['id'] . '</td>';
                        echo '<td class="table-middle">' . $rowPendientes['apellido_persona'] . '</td>';
                        echo '<td class="table-middle">' . $rowPendientes['nombre_persona'] . '</td>';
                        echo '<td class="table-middle">' . $rowPendientes['dni'] . '</td>';
                        echo '<td class="table-middle">' . $rowPendientes['nombre_servicio'] . '</td>';
                        echo '<td class="table-middle table-left" style="width: max-content;">';
                        $permisosPendientes_array = json_decode($rowPendientes['permisos'], true);

                        if ($permisosPendientes_array !== null) {
                            $permisos_activos = [];
                            foreach ($permisosPendientes_array as $permisoPendientes) {
                                $nombre_permiso = $permisoPendientes['permiso'];
                                $activo = $permisoPendientes['activo'];

                                if ($activo == "si") {
                                    $permisos_activos[] = '<div style="width: max-content;"><i class="fa-solid fa-chevron-right"></i> ' . $nombre_permiso;
                                }
                            }
                            echo implode('</div>', $permisos_activos);
                        }
                        echo '</td>';
                        echo '<td class="table-middle table-left" style="width: max-content;"><div style="display: grid; grid-template-columns: auto min-content; align-items: center;">';
                        $documentos_array = json_decode($rowPendientes['documentos'], true);

                        if ($documentos_array !== null) {
                            foreach ($documentos_array as $documentoPendientes) {
                                $documento = $documentoPendientes['documento'];
                                $activo = $documentoPendientes['activo'];

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
                                        $simbolo =  '<i class="fa-solid fa-xmark"></i>';
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
                        echo '<td class="table-middle table-center"><button class="btn-green" onclick="loadInfo(\'' . $rowPendientes['dni'] . '\', \'' . $rowPendientes['servicio'] . '\')"><i class="fa-solid fa-hand-pointer"></i></button></td>';
                        echo '</tr>';
                    }
                }

                ?>
            </tbody>
        </table>

        <div class="habilitado">

            <style>
                .select2-container--default .select2-selection--single,
                .select2-container--default .select2-selection--multiple .select2-selection__arrow {
                    transform: translateY(0.0vw);
                }

                #inputFilter {
                    margin-left: 3vw;
                    width: 30vw;
                }
            </style>

            <h4>Habilitados</h4>
            <div style="display: flex; flex-direction: row; margin-bottom: 1vw; justify-content: center; margin-top: .5vw;">
                <select name="servicioFilter" id="servicioFilter" class="select2" style="width: 30vw;">
                    <option value="">Sin filtro por servicio</option>
                    <?php

                    // Realiza la consulta a la tabla servicios
                    $getPersonal = "SELECT id, servicio FROM servicios";
                    $stmt = $pdo->query($getPersonal);

                    // Itera sobre los resultados y muestra las filas en la tabla
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo '<option value=' . $row['id'] . '>' . $row['servicio'] . '</option>';
                    }

                    ?>
                    <select>

                        <input type="text" name="inputFilter" id="inputFilter">
            </div>
            <div class="tablaHabilitados"></div>

            <script>
    $(document).ready(function() {
        // Función para cargar la tabla de usuarios habilitados
        function cargarTablaHabilitados(page) {
            var servicioFilter = $("#servicioFilter").val();
            var inputFilter = $("#inputFilter").val();

            $.ajax({
                url: "controllers/tablaHabilitadosAdm.php",
                type: "GET",
                data: {
                    page: page,
                    servicioFilter: servicioFilter,
                    inputFilter: inputFilter
                },
                success: function(response) {
                    $("#tablaHabilitados").html(response);
                }
            });
        }

        // Cargar la tabla al cargar la página
        cargarTablaHabilitados(1);

        // Manejar la paginación
        $(document).on("click", ".pagination li", function() {
            var page = $(this).text();
            cargarTablaHabilitados(page);
        });

        // Manejar el filtrado
        $("#servicioFilter, #inputFilter").on("change keyup", function() {
            cargarTablaHabilitados(1);
        });
    });
</script>


        </div>
    </div>
</div>


<script src="/SGH/public/layouts/modules/hsiPanel/js/hsiAdmin.js"></script>
<?php require_once '../../base/footer.php'; ?>