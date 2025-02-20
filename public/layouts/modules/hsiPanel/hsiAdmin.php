<?php

require_once '../../../../app/db/db.php';
require_once '../../../../app/db/user_session.php';
require_once '../../../../app/db/user.php';
require_once 'controllers/search_user.php';
require_once '../../../config.php';

$user = new User();
$userSession = new UserSession();
$currentUser = $userSession->getCurrentUser();
$user->setUser($currentUser);

requireRole(['administrador', 'direccion', 'hsi']);

$title = "Administración de HSI";

$db = new DB();
$pdo = $db->connect();


$servicioFilter = $user->getServicio();

// Obtener el parámetro 'selectServicioFilter' de la URL, si no está se establece en null
$sel = isset($_GET['selectServicioFilter']) ? $_GET['selectServicioFilter'] : null;

// Si el parámetro 'selectServicioFilter' no coincide con el servicio del usuario
if (!$sel) {

    // Asignar el servicio del usuario a 'selectServicioFilter' si no es válido
    $selectServicioFilter = "clr";

    // Redirigir con el nuevo parámetro selectServicioFilter
    $url = "hsiAdmin.php?pagina=$pagina";
    if ($selectServicioFilter) {
        // Asegurarse de que el valor del servicio sea correctamente escapado para la URL
        $url .= "&selectServicioFilter=" . urlencode($selectServicioFilter);
    }

    // Redirigir al usuario a la URL con el servicio correcto
    header("Location: $url");
    exit();
}

?>

<?php require_once '../../base/header.php'; ?>
<link rel="stylesheet" href="/SGH/public/layouts/modules/hsiPanel/css/hsi.css">

<script>
    $(document).ready(function () {
        $('#servicioSelectNew').select2();
        $('#permisosSelect').select2();
        $('#dniSelect').select2();
    });

    function newUser() {
        back.style.display = "flex";
        neUser.style.display = "flex";
    }

    function addDocs(dni, servicio) {
        back.style.display = "flex";
        addDocsDiv.style.display = "flex";
        docsDniHidden.value = dni;
        docsServicio.value = servicio;
        infoModule.style.display = "none";
    }
</script>

<div class="content">

    <div class="modulo" style="text-align: center;">
        <h3 style="margin-bottom: .5vw;">Sistema de administración de HSI</h3>
        <p>Este sistema está oreintado a la gestion y administración de los </br> usuarios de HSI para los
            administradores institucionales.</p>
    </div>

    <div class="admInst" style="position: relative; top: -6vw; left: -29vw;">
        <a class="btn-tematico" style="text-decoration: none;" href="hsi.php"><i class="fa-solid fa-toolbox"></i>
            <b>Acceder a panel general</b></a>
    </div>

    <div class="back" id="back">

        <div class="divBackForm" id="neUser" style="display: none;">
            <div class="close" style="width: 100%; display: flex; justify-content: flex-end; padding: .5vw">
                <button class="btn-red"
                    onclick="back.style.display = 'none'; neUser.style.display = 'none'; newUserForm.reset(); $('#dniSelect').val(null).trigger('change'); $('#servicioSelectNew').val(null).trigger('change'); $('#permisosSelect').val(null).trigger('change');"
                    style="width: 2.3vw; height: 2.3vw;"><b><i class="fa-solid fa-xmark"></i></b></button>
            </div>
            <h3>Agregar nuevo usuario</h3>

            <form action="/SGH/public/layouts/modules/hsiPanel/controllers/newUserAdm.php" method="post"
                class="backForm" id="newUserForm">

                <div>
                    <label for="dniSelect">DNI</label>
                    <select name="dni" id="dniSelect" required style="width: 95%;">
                        <option value="" selected disabled>Seleccionar agente...</option>
                        <?php

                        // Realiza la consulta a la tabla personal, excluyendo los dni que están en la tabla hsi
                        $getPersonal = "SELECT apellido, nombre, dni FROM personal WHERE CONVERT(dni USING utf8mb4) COLLATE utf8mb4_spanish_ci NOT IN (SELECT CONVERT(dni USING utf8mb4) COLLATE utf8mb4_spanish_ci FROM hsi)";
                        $stmt = $pdo->query($getPersonal);

                        // Itera sobre los resultados y muestra las filas en la tabla
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            echo '<option value=' . $row['dni'] . '>' . $row['apellido'] . ' ' . $row['nombre'] . ' - ' . $row['dni'] . '</option>';
                        }

                        ?>


                    </select>
                </div>

                <div>
                    <label for="servicioSelectNew">Servicio</label>
                    <select name="servicio" id="servicioSelectNew" style="width: 95%;">
                        <option value="" selected disabled>Seleccionar un servicio...</option>
                        <?php

                        // Realiza la consulta a la tabla servicios
                        $getServicio = "SELECT id, servicio FROM servicios";
                        $stmt = $pdo->query($getServicio);

                        // Itera sobre los resultados y muestra las filas en la tabla
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            echo '<option value=' . $row['id'] . '>' . $row['servicio'] . '</option>';
                        }

                        ?>
                    </select>
                </div>
                <div>
                    <label for="email">E-mail</label>
                    <input type="email" name="email" id="email" required>
                </div>
                <div>
                    <label for="phone">Telefono (WhatsApp)</label>
                    <input type="tel" name="phone" id="phone" required>
                </div>
                <div>
                    <label for="permisosSelect">Permisos</label>
                    <select name="permisos[]" id="permisosSelect" style="width: 95%;" multiple="multiple"
                        placeholder="Seleccionar permiso(s)" required>
                        <?php

                        $getRoles = "SELECT * FROM roles_hsi WHERE estado = 'activo'";
                        $stmt = $pdo->query($getRoles);

                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            echo '<option value=' . $row['id'] . '>' . $row['rol'] . '</option>';
                        }

                        ?>
                    </select>
                </div>

                <div style="display: flex; flex-direction: row; justify-content: center;">
                    <button type="submit" class="btn-green"><b><i class="fa-solid fa-plus"></i> Agregar nuevo
                            usuario</b></button>
                </div>
            </form>
        </div>

        <div class="divBackForm" id="addDocsDiv" style="display: none;">
            <div class="close" style="width: 100%; display: flex; justify-content: flex-end; padding: .5vw">
                <button class="btn-red"
                    onclick="back.style.display = 'none'; addDocsDiv.style.display = 'none'; addDocsForm.reset();"
                    style="width: 2.3vw; height: 2.3vw;"><b><i class="fa-solid fa-xmark"></i></b></button>
            </div>
            <h3>Agregar documentación</h3>
            <p style="color: red;">* documentos obligatorios en formato pdf</p>

            <form action="/SGH/public/layouts/modules/hsiPanel/controllers/docsUploadAdm.php" class="backForm"
                method="post" id="addDocsForm" enctype="multipart/form-data">
                <input type="hidden" name="docsDniHidden" id="docsDniHidden">
                <input type="hidden" name="docsServicio" id="docsServicio">
                <div style="margin-top: 6vw;">
                    <label for="docsDni">Documento Nacional de Identidad <br> (Frente y dorso en un archivo) <b
                            style="color: red;">*</b></label>
                    <input type="file" name="docsDni" id="docsDni" accept="application/pdf">
                </div>
                <div>
                    <label for="docsMatricula">Matricula Profesional <br> (frente y dorso en un archivo) si
                        corresponde</label>
                    <input type="file" name="docsMatricula" id="docsMatricula" accept="application/pdf">
                </div>
                <div>
                    <label for="docsAnexoI">Solicitud de alta de usuario para HSI <br> (ANEXO I) <b
                            style="color: red;">*</b></label>
                    <input type="file" name="docsAnexoI" id="docsAnexoI" accept="application/pdf">
                </div>
                <div>
                    <label for="docsAnexoII">Declaración Jurada - Convenio de confidencialidad usuarios HSI <br> (ANEXO
                        II) <b style="color: red;">*</b></label>
                    <input type="file" name="docsAnexoII" id="docsAnexoII" accept="application/pdf">
                </div>
                <div>
                    <label for="docsPrescriptor">Declaración Jurada - Usuario prescriptor</label>
                    <input type="file" name="docsPrescriptor" id="docsPrescriptor" accept="application/pdf">
                </div>

                <button class="btn-green" type="submit"><i class="fa-solid fa-file-arrow-up"></i> Subir
                    archivos</button>
            </form>
        </div>

        <div id="warnBajaRes" class="divBackForm" style="display: none; padding: 3vw;">
            <h3>¡¡ATENCIÓN!!</h3>
            <p style="margin-top: 2vw;">Está por solicitar la baja de todos los usuarios habilitados como residentes,
                esto causará que:</p>
            <ul style="margin-top: 2vw;">
                <li>Todos los usuarios se marquen para deshabilitar.</li>
                <li>Todos los usuarios pasaran a pendiente.</li>
                <li>Se enviará una notificación a los usuarios solicitantes sobre como rehabilitarlos.</li>
            </ul>

            <h4 style="margin-top: 2vw;">¿Desea continuar?</h4>
            <div>
                <button class="btn-red" onclick="back.style.display = 'none'; warnBajaRes.style.display = 'none';"><i
                        class="fa-solid fa-xmark"></i> <b>Cancelar acción</b></button>

                <a class="btn-yellow" href="controllers/bajaRes.php"><i class="fa-solid fa-triangle-exclamation"></i>
                    <b>Establecer baja de residentes</b></a>
            </div>
        </div>

        <div class="divBackForm infoModule" id="infoModule" style="display: none;">
            <div class="close"
                style="width: 100%; display: flex; justify-content: flex-end; padding: .5vw; margin-bottom: -3.5vw;">
                <button class="btn-red close-btn" onclick="cerrarVista();" style="width: 2.3vw; height: 2.3vw;"><b><i
                            class="fa-solid fa-xmark"></i></b></button>
            </div>
            <h3>Información de usuario</h3>
            <div class="cuerpoInfo" id="infoUsuario">
                <!-- Contenido generado dinámicamente -->
            </div>
        </div>

        <div class="divBackForm" id="rolesModule" style="display: none;">
            <div class="close"
                style="width: 100%; display: flex; justify-content: flex-end; padding: .5vw; margin-bottom: -3.5vw;">
                <button class="btn-red close-btn"
                    onclick="back.style.display = 'none'; rolesModule.style.display = 'none';"
                    style="width: 2.3vw; height: 2.3vw;"><b><i class="fa-solid fa-xmark"></i></b></button>
            </div>

            <h3>Roles de HSI</h3>

            <div class="nuevo" style="width: 100%; display: flex; flex-direction: column; align-items: center;">
                <form action="controllers/nuevo_rol.php" class="backForm" method="post">
                    <div>
                        <label for="rol_new">Nuevo rol</label>
                        <div style="display: flex; flex-direction: row;">
                            <input type="text" id="rol_new" name="rol_new" style="width: 95%;">
                            <button type="submit" class="btn-green"><i class="fa-solid fa-plus"></i></button>
                        </div>
                    </div>
                </form>
            </div>

            <hr style="color: #000; width: 90%; margin: 1vw">
            <div class="lista" style="overflow: auto">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Rol</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $queryRoles = "SELECT * FROM roles_hsi";

                        $stmtRoles = $pdo->prepare($queryRoles);
                        $stmtRoles->execute();

                        // Handling de error
                        if ($stmtRoles->errorCode() !== '00000') {
                            $errorInfo = $stmtRoles->errorInfo();
                            echo "Error: " . $errorInfo[2];
                        } else if ($stmtRoles->rowCount() == 0) {
                            echo '<tr><td colspan="3" class="table-middle table-center">No hay roles creados</td></tr>';
                        } else {
                            while ($rowRoles = $stmtRoles->fetch(PDO::FETCH_ASSOC)) {
                                echo '<tr>';
                                echo '<td class="table-middle table-center">' . $rowRoles['id'] . '</td>';
                                echo '<td class="table-middle">' . $rowRoles['rol'] . '</td>';
                                echo '<td class="table-middle table-center" style="padding: .8vw;">';
                                if ($rowRoles['estado'] == "activo") {
                                    echo '<a class="btn-green" title="Click para desactivar" href="controllers/toggleRol.php?rol=' . $rowRoles['id'] . '&toggle=desactivar"><i class="fa-solid fa-power-off"></i></a>';
                                } else {
                                    echo '<a class="btn-red" title="Click para activar" href="controllers/toggleRol.php?rol=' . $rowRoles['id'] . '&toggle=activar"><i class="fa-solid fa-power-off"></i></a>';
                                }
                                echo '</tr>';
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="divBackForm" id="printModal" style="display: none;">
            <div class="close"
                style="width: 100%; display: flex; justify-content: flex-end; padding: .5vw; margin-bottom: -3.5vw;">
                <button class="btn-red"
                    onclick="back.style.display = 'none'; printModal.style.display = 'none'; printModalForm.reset();"
                    style="width: 2.3vw; height: 2.3vw;"><b><i class="fa-solid fa-xmark"></i></b></button>
            </div>
            <h3>Impresión de informe</h3>

            <form action="controllers/print.php" method="post" class="backForm" id="printModalForm" target="_blank">

                <div>
                    <label for="printServicio">Seleccionar servicio</label>
                    <select name="printServicio" id="printServicio" class="select2">
                        <option value="clr" selected>Seleccionar todos los servicios</option>
                        <?php
                        // Realiza la consulta a la tabla servicios
                        $getServicio = "SELECT id, servicio FROM servicios";
                        $stmt = $pdo->query($getServicio);

                        // Itera sobre los resultados y muestra las filas en la tabla
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            echo '<option value=' . $row['id'] . '>' . $row['servicio'] . '</option>';
                        }

                        ?>
                    </select>
                </div>

                <div style="display: flex; flex-direction: row; justify-content: flex-start; align-items: center;">
                    <input type="checkbox" name="pendientes" id="pendientes" style="width: auto; margin-right: 1vw;">
                    <label for="pendientes">¿Incluir pendientes?</label>
                </div>

                <div style="display: flex; flex-direction: row; justify-content: flex-start; align-items: center;">
                    <input type="checkbox" name="activos" id="activos" style="width: auto; margin-right: 1vw;">
                    <label for="activos">¿Incluir activos?</label>
                </div>

                <div style="display: flex; flex-direction: row; justify-content: flex-start; align-items: center;">
                    <input type="checkbox" name="deshabilitados" id="deshabilitados"
                        style="width: auto; margin-right: 1vw;">
                    <label for="deshabilitados">¿Incluir deshabilitados?</label>
                </div>

                <button class="btn-green"><i class="fa-solid fa-print"></i> Imprimir informe</button>

            </form>
        </div>
    </div>

    <script>
        // Variable para rastrear si ha habido cambios
        var cambiosRealizados = false;

        // Función para verificar cambios antes de cerrar la vista y resetear el formulario
        function cerrarVista() {
            if (cambiosRealizados) {
                if (!confirm('¿Estás seguro de que quieres salir? Hay cambios sin guardar en el formulario.') == true) {
                    return false
                } else {
                    cerrarVistaTrue();
                }
            } else {
                cerrarVistaTrue();
            }
        }

        // Función para marcar que ha habido cambios
        function marcarCambio() {
            cambiosRealizados = true;
        }

        function cerrarVistaTrue() {
            document.getElementById('back').style.display = 'none';
            document.getElementById('infoModule').style.display = 'none';
            cambiosRealizados = false

            // Obtener el elemento div #infoUsuario
            var infoUsuario = document.getElementById('infoUsuario');

            // Eliminar todos los elementos hijos del div #infoUsuario
            while (infoUsuario.firstChild) {
                infoUsuario.removeChild(infoUsuario.firstChild);
            }
        }
    </script>





    <div class="modulo" style="text-align: center;">

        <div class="inlineDiv">
            <button class="btn-green" onclick="newUser()"><b><i class="fa-solid fa-plus"></i> Agregar nuevo
                    usuario</b></button>
            <button class="btn-tematico"
                onclick="back.style.display = 'flex'; warnBajaRes.style.display = 'flex';"><b><i
                        class="fa-solid fa-user-graduate"></i></i> Establecer baja para usuarios residentes</b></button>
            <button class="btn-tematico" onclick="back.style.display = 'flex'; rolesModule.style.display = 'flex';"><i
                    class="fa-solid fa-check-to-slot"></i> <b>Gestionar roles</b></button>
            <button class="btn-tematico" onclick="printInforme()"><i class="fa-solid fa-print"></i> <b>Imprimir informe
                    de
                    usuarios</b></button>
        </div>

        <?php
        $estado = "working";

        $queryPendientes = "SELECT hsi.*, 
                           p.nombre AS nombre_persona, 
                           p.apellido AS apellido_persona, 
                           s.servicio AS nombre_servicio 
                    FROM hsi 
                    LEFT JOIN personal AS p ON hsi.dni = p.dni 
                    LEFT JOIN servicios AS s ON hsi.servicio = s.id 
                    WHERE hsi.estado = :estado 
                    ORDER BY hsi.id ASC";

        $stmtPendientes = $pdo->prepare($queryPendientes);
        $stmtPendientes->bindParam(':estado', $estado, PDO::PARAM_STR); // Cambiado a PDO::PARAM_STR para cadenas de texto
        $stmtPendientes->execute();

        // Contar registros
        $totalPendientes = $stmtPendientes->rowCount();
        ?>

        <h4>Pendientes (<?php echo $totalPendientes; ?>)</h4>

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
                <?php if ($totalPendientes == 0): ?>
                    <tr>
                        <td colspan="8">No hay usuarios pendientes</td>
                    </tr>
                <?php else: ?>
                    <?php while ($rowPendientes = $stmtPendientes->fetch(PDO::FETCH_ASSOC)): ?>
                        <tr>
                            <td class="table-middle"><?php echo $rowPendientes['id']; ?></td>
                            <td class="table-middle"><?php echo htmlspecialchars($rowPendientes['apellido_persona']); ?></td>
                            <td class="table-middle"><?php echo htmlspecialchars($rowPendientes['nombre_persona']); ?></td>
                            <td class="table-middle"><?php echo htmlspecialchars($rowPendientes['dni']); ?></td>
                            <td class="table-middle"><?php echo htmlspecialchars($rowPendientes['nombre_servicio']); ?></td>
                            <td class="table-middle table-left" style="width: max-content;">
                                <?php
                                $getRolesAct = "SELECT u.id, u.id_rol, r.rol AS nombre_rol 
                                        FROM usuarios_roles_hsi u 
                                        JOIN roles_hsi r ON u.id_rol = r.id 
                                        WHERE u.dni = :dni";

                                $stmtRolesAct = $pdo->prepare($getRolesAct);
                                $stmtRolesAct->execute([':dni' => $rowPendientes['dni']]);

                                while ($row = $stmtRolesAct->fetch(PDO::FETCH_ASSOC)) {
                                    echo '<div style="text-wrap-mode: nowrap;"><i class="fa-solid fa-chevron-right"></i> ' . htmlspecialchars($row['nombre_rol']) . '</div>';
                                }
                                ?>
                            </td>
                            <td class="table-middle table-left" style="width: max-content;">
                                <div style="display: grid; grid-template-columns: auto min-content; align-items: center;">
                                    <?php
                                    $documentos_array = json_decode($rowPendientes['documentos'], true);

                                    if ($documentos_array !== null) {
                                        foreach ($documentos_array as $documentoPendientes) {
                                            $documento = $documentoPendientes['documento'];
                                            $activo = $documentoPendientes['activo'];

                                            // Mapeo de nombres de documentos
                                            $nombresDocumentos = [
                                                'Copia de DNI' => 'D.N.I',
                                                'Copia de matrícula profesional' => 'Matricula',
                                                'Solicitud de alta de usuario para HSI (ANEXO I)' => 'ANEXO I',
                                                'Declaración Jurada - Convenio de confidecialidad usuarios HSI (ANEXO II)' => 'ANEXO II',
                                                'Declaración Jurada - Usuario prescriptor' => 'Prescriptor'
                                            ];

                                            $documento_nombre = $nombresDocumentos[$documento] ?? $documento;

                                            // Estado del documento
                                            $simbolos = [
                                                'no' => '<i class="fa-solid fa-xmark"></i>',
                                                'pendiente' => '<i class="fa-regular fa-clock"></i>',
                                                'verificado' => '<i class="fa-solid fa-check"></i>'
                                            ];

                                            $simbolo = $simbolos[$activo] ?? '<i class="fa-solid fa-question"></i>';

                                            echo '<div>' . htmlspecialchars($documento_nombre) . ':</div>';
                                            echo '<div>' . $simbolo . '</div>';
                                        }
                                    }
                                    ?>
                                </div>
                            </td>
                            <td class="table-middle table-center">
                                <button class="btn-green"
                                    onclick="loadInfo('<?php echo htmlspecialchars($rowPendientes['dni']); ?>', '<?php echo htmlspecialchars($rowPendientes['servicio']); ?>')">
                                    <i class="fa-solid fa-hand-pointer"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
            </tbody>
        </table>

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

            <h4>Habilitados (<?php echo $totalregistros; ?>)</h4>
            <div style="width: 100%;">

                <form action="hsiAdmin.php#habilitado" method="get" id="formFiltro" style="display: flex; flex-direction: row; flex-wrap: nowrap; align-items: center; overflow-y: hidden;">
                    <input type="hidden" name="pagina"
                        value="<?php echo isset($_GET['pagina']) ? htmlspecialchars($_GET['pagina']) : 1; ?>">

                    <div
                        style="display: grid; grid-template-columns: repeat(2, 1fr); grid-template-rows: 1fr; grid-column-gap: 1vw; grid-row-gap: 0px; overflow-y: hidden;">
                        <select name="selectServicioFilter" id="selectServicioFilter" class="select2">
                            <?php
                            $selectedServicio = isset($_GET['selectServicioFilter']) ? htmlspecialchars($_GET['selectServicioFilter']) : '';

                            echo '<option value="" selected disabled>Seleccionar un servicio...</option>';
                            echo '<option value="clr"' . ($selectedServicio === 'clr' ? ' selected' : '') . '>Seleccionar todos los servicios</option>';

                            $getServicios = "SELECT id, servicio FROM servicios WHERE estado = 'Activo'";
                            $stmtServicios = $pdo->query($getServicios);

                            while ($row = $stmtServicios->fetch(PDO::FETCH_ASSOC)) {
                                $selected = ($selectedServicio == $row['id']) ? ' selected' : '';
                                echo '<option value="' . $row['id'] . '"' . $selected . '>' . $row['servicio'] . '</option>';
                            }
                            ?>
                        </select>

                        <input type="text" name="searchInput" id="searchInput" style="width: 100%;"
                            placeholder="Buscar por DNI o nombre..."
                            value="<?php echo isset($_GET['searchInput']) ? htmlspecialchars($_GET['searchInput']) : ''; ?>">

                    </div>
                    <button type="submit" class="btn-green"><i class="fa-solid fa-magnifying-glass"></i></button>
                </form>


            </div>

            <script>
                function formatNumber(input) {
                    // Eliminar caracteres que no son números
                    const inputValue = input.value.replace(/\D/g, '');

                    // Formatear el número con puntos si no está vacío, de lo contrario, dejar en blanco
                    const formattedNumber = inputValue !== '' ? Number(inputValue).toLocaleString('es-AR') : '';

                    // Actualizar el valor del campo de entrada
                    input.value = formattedNumber;
                }
            </script>
        </div>

        <div class="tablaHabilitados" id="tablaHabilitados">
            <?php
            if (count($registros) > 0):
                ?>
                <table id="habilitado">
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
                    <tbody>
                        <?php foreach ($registros as $reg): ?>
                            <tr>
                                <td class="table-center table-middle"><?= htmlspecialchars($reg['id']) ?></td>
                                <td class="table-center table-middle"><?= htmlspecialchars($reg['apellido']) ?></td>
                                <td class="table-center table-middle"><?= htmlspecialchars($reg['nombre']) ?></td>
                                <td class="table-center table-middle"><?= htmlspecialchars($reg['dni']) ?></td>
                                <td class="table-center table-middle"><?= htmlspecialchars($reg['nombre_servicio']) ?></td>
                                <td class="table-left table-middle">
                                    <?php
                                    // Obtener los roles asociados a cada usuario
                                    $getRolesAct = "SELECT u.id, u.id_rol, r.rol AS nombre_rol 
                                FROM usuarios_roles_hsi u 
                                JOIN roles_hsi r ON u.id_rol = r.id 
                                WHERE u.dni = :dni";
                                    $stmtRolesAct = $pdo->prepare($getRolesAct);
                                    $stmtRolesAct->execute([':dni' => $reg['dni']]);
                                    while ($rowRol = $stmtRolesAct->fetch(PDO::FETCH_ASSOC)) {
                                        echo '<div><i class="fa-solid fa-chevron-right"></i>' . htmlspecialchars($rowRol['nombre_rol']) . '</div>';
                                    }
                                    ?>
                                </td>
                                <td class="table-center table-middle">
                                    <button class="btn-green"
                                        onclick="loadInfo('<?= $reg['dni'] ?>', '<?= $reg['servicio'] ?>')">
                                        <i class="fa-solid fa-hand-pointer"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <tr>
                    <td class="table-center table-middle" colspan="7">No hay registros para mostrar.</td>
                </tr>
            <?php endif; ?>
        </div>

        <div id="contenedorPaginacion">
            <?php
            if ($totalregistros >= 1):
                // Número máximo de páginas que se mostrarán
                $max_paginacion = 8;

                // Cálculos de inicio y fin para las páginas visibles
                $inicio_pagina = max(1, $pagina - floor($max_paginacion / 2));
                $fin_pagina = min($numeropaginas, $pagina + floor($max_paginacion / 2));

                // Si la cantidad de páginas disponibles es menor que el máximo, ajustamos los límites
                if ($pagina - floor($max_paginacion / 2) < 1) {
                    $fin_pagina = min($numeropaginas, $fin_pagina + (1 - ($pagina - floor($max_paginacion / 2))));
                }
                if ($pagina + floor($max_paginacion / 2) > $numeropaginas) {
                    $inicio_pagina = max(1, $inicio_pagina - ($pagina + floor($max_paginacion / 2) - $numeropaginas));
                }
                ?>

                <nav aria-label="Page navigation">
                    <ul class="pagination">
                        <!-- Botón de "anterior" -->
                        <?php if ($pagina == 1): ?>
                            <li class="page-item disabled"><a class="page-link" href="#">&laquo;</a></li>
                        <?php else: ?>
                            <li class="page-item"><a class="page-link"
                                    href="hsiAdmin.php?pagina=<?php echo $pagina - 1; ?>&selectServicioFilter=<?php echo isset($_GET['selectServicioFilter']) ? urlencode($_GET['selectServicioFilter']) : ''; ?>&searchInput=<?php echo isset($_GET['searchInput']) ? urlencode($_GET['searchInput']) : ''; ?>">&laquo;</a>
                            </li>
                        <?php endif; ?>

                        <!-- Página 1 -->
                        <?php if ($inicio_pagina > 1): ?>
                            <li class="page-item"><a class="page-link"
                                    href="hsiAdmin.php?pagina=1&selectServicioFilter=<?php echo isset($_GET['selectServicioFilter']) ? urlencode($_GET['selectServicioFilter']) : ''; ?>&searchInput=<?php echo isset($_GET['searchInput']) ? urlencode($_GET['searchInput']) : ''; ?>">1</a>
                            </li>
                            <li class="page-item disabled"><span class="page-link">...</span></li>
                        <?php endif; ?>

                        <!-- Páginas intermedias -->
                        <?php for ($i = $inicio_pagina; $i <= $fin_pagina; $i++): ?>
                            <?php if ($pagina == $i): ?>
                                <li class="page-item active"><a class="page-link"
                                        href="hsiAdmin.php?pagina=<?php echo $i; ?>&selectServicioFilter=<?php echo isset($_GET['selectServicioFilter']) ? urlencode($_GET['selectServicioFilter']) : ''; ?>&searchInput=<?php echo isset($_GET['searchInput']) ? urlencode($_GET['searchInput']) : ''; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php else: ?>
                                <li class="page-item"><a class="page-link"
                                        href="hsiAdmin.php?pagina=<?php echo $i; ?>&selectServicioFilter=<?php echo isset($_GET['selectServicioFilter']) ? urlencode($_GET['selectServicioFilter']) : ''; ?>&searchInput=<?php echo isset($_GET['searchInput']) ? urlencode($_GET['searchInput']) : ''; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endif; ?>
                        <?php endfor; ?>

                        <!-- Última página -->
                        <?php if ($fin_pagina < $numeropaginas): ?>
                            <li class="page-item disabled"><span class="page-link">...</span></li>
                            <li class="page-item"><a class="page-link"
                                    href="hsiAdmin.php?pagina=<?php echo $numeropaginas; ?>&selectServicioFilter=<?php echo isset($_GET['selectServicioFilter']) ? urlencode($_GET['selectServicioFilter']) : ''; ?>&searchInput=<?php echo isset($_GET['searchInput']) ? urlencode($_GET['searchInput']) : ''; ?>"><?php echo $numeropaginas; ?></a>
                            </li>
                        <?php endif; ?>

                        <!-- Botón de "siguiente" -->
                        <?php if ($pagina == $numeropaginas): ?>
                            <li class="page-item disabled"><a class="page-link" href="#">&raquo;</a></li>
                        <?php else: ?>
                            <li class="page-item"><a class="page-link"
                                    href="hsiAdmin.php?pagina=<?php echo $pagina + 1; ?>&selectServicioFilter=<?php echo isset($_GET['selectServicioFilter']) ? urlencode($_GET['selectServicioFilter']) : ''; ?>&searchInput=<?php echo isset($_GET['searchInput']) ? urlencode($_GET['searchInput']) : ''; ?>">&raquo;</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>

            <?php endif; ?>
        </div>

        <div class="disabledSearch" style="margin-top: 2vw;">
            <h4>Buscar usuario por D.N.I.</h4>
            <input type="text" name="disabledInput" id="disabledInput"
                style="width: 45%; height: 3vw; margin-left: 2vw;" placeholder="Buscar por DNI..."
                oninput="formatNumber(this)">

            <button class="btn-green" onclick="loadInfoDelet(disabledInput.value); disabledInput.value=''"><i
                    class="fa-solid fa-magnifying-glass"></i></button>
        </div>
    </div>
</div>
</div>


<script src="/SGH/public/layouts/modules/hsiPanel/js/hsiAdmin.js"></script>
<?php require_once '../../base/footer.php'; ?>