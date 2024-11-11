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

<script>
    $(document).ready(function() {
        $('#servicioSelectNew').select2();
        $('#permisosSelect').select2();
        $('#dniSelect').select2();
    });

    function newUser() {
        back.style.display = "flex";
        neUser.style.display = "flex";
    }

    function addDocs(dni) {
        back.style.display = "flex";
        addDocsDiv.style.display = "flex";
        docsDniHidden.value = dni;
        infoModule.style.display = "none";
    }
</script>

<div class="content">

    <div class="modulo" style="text-align: center;">
        <h3 style="margin-bottom: .5vw;">Sistema de administración de HSI</h3>
        <p>Este sistema está oreintado a la gestion y administración de los </br> usuarios de HSI para los administradores institucionales.</p>
    </div>

    <div class="admInst" style="position: relative; top: -6vw; left: -29vw;">
        <a class="btn-tematico" style="text-decoration: none;" href="hsi.php"><i class="fa-solid fa-toolbox"></i> <b>Acceder a panel general</b></a>
    </div>

    <div class="back" id="back">

        <div class="divBackForm" id="neUser" style="display: none;">
            <div class="close" style="width: 100%; display: flex; justify-content: flex-end; padding: .5vw">
                <button class="btn-red" onclick="back.style.display = 'none'; neUser.style.display = 'none'; newUserForm.reset(); $('#dniSelect').val(null).trigger('change'); $('#servicioSelectNew').val(null).trigger('change'); $('#permisosSelect').val(null).trigger('change');" style="width: 2.3vw; height: 2.3vw;"><b><i class="fa-solid fa-xmark"></i></b></button>
            </div>
            <h3>Agregar nuevo usuario</h3>

            <form action="/SGH/public/layouts/modules/hsiPanel/controllers/newUserAdm.php" method="post" class="backForm" id="newUserForm">

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
                    <select name="permisos[]" id="permisosSelect" style="width: 95%;" multiple="multiple" placeholder="Seleccionar permiso(s)" required>
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
                    <button type="submit" class="btn-green"><b><i class="fa-solid fa-plus"></i> Agregar nuevo usuario</b></button>
                </div>
            </form>
        </div>

        <div class="divBackForm" id="addDocsDiv" style="display: none;">
            <div class="close" style="width: 100%; display: flex; justify-content: flex-end; padding: .5vw">
                <button class="btn-red" onclick="back.style.display = 'none'; addDocsDiv.style.display = 'none'; addDocsForm.reset();" style="width: 2.3vw; height: 2.3vw;"><b><i class="fa-solid fa-xmark"></i></b></button>
            </div>
            <h3>Agregar documentación</h3>
            <p style="color: red;">* documentos obligatorios en formato pdf</p>

            <form action="/SGH/public/layouts/modules/hsiPanel/controllers/docsUploadAdm.php" class="backForm" method="post" id="addDocsForm" enctype="multipart/form-data">
                <input type="hidden" name="docsDniHidden" id="docsDniHidden">
                <div style="margin-top: 6vw;">
                    <label for="docsDni">Documento Nacional de Identidad <br> (Frente y dorso en un archivo) <b style="color: red;">*</b></label>
                    <input type="file" name="docsDni" id="docsDni" accept="application/pdf">
                </div>
                <div>
                    <label for="docsMatricula">Matricula Profesional <br> (frente y dorso en un archivo) si corresponde</label>
                    <input type="file" name="docsMatricula" id="docsMatricula" accept="application/pdf">
                </div>
                <div>
                    <label for="docsAnexoI">Solicitud de alta de usuario para HSI <br> (ANEXO I) <b style="color: red;">*</b></label>
                    <input type="file" name="docsAnexoI" id="docsAnexoI" accept="application/pdf">
                </div>
                <div>
                    <label for="docsAnexoII">Declaración Jurada - Convenio de confidencialidad usuarios HSI <br> (ANEXO II) <b style="color: red;">*</b></label>
                    <input type="file" name="docsAnexoII" id="docsAnexoII" accept="application/pdf">
                </div>
                <div>
                    <label for="docsPrescriptor">Declaración Jurada - Usuario prescriptor</label>
                    <input type="file" name="docsPrescriptor" id="docsPrescriptor" accept="application/pdf">
                </div>

                <button class="btn-green" type="submit"><i class="fa-solid fa-file-arrow-up"></i> Subir archivos</button>
            </form>
        </div>

        <div id="warnBajaRes" class="divBackForm" style="display: none; padding: 3vw;">
            <h3>¡¡ATENCIÓN!!</h3>
            <p style="margin-top: 2vw;">Está por solicitar la baja de todos los usuarios habilitados como residentes, esto causará que:</p>
            <ul style="margin-top: 2vw;">
                <li>Todos los usuarios se marquen para deshabilitar.</li>
                <li>Todos los usuarios pasaran a pendiente.</li>
                <li>Se enviará una notificación a los usuarios solicitantes sobre como rehabilitarlos.</li>
            </ul>

            <h4 style="margin-top: 2vw;">¿Desea continuar?</h4>
            <div>
                <button class="btn-red" onclick="back.style.display = 'none'; warnBajaRes.style.display = 'none';"><i class="fa-solid fa-xmark"></i> <b>Cancelar acción</b></button>

                <a class="btn-yellow" href="controllers/bajaRes.php"><i class="fa-solid fa-triangle-exclamation"></i> <b>Establecer baja de residentes</b></a>
            </div>
        </div>

        <div class="divBackForm infoModule" id="infoModule" style="display: none;">
            <div class="close" style="width: 100%; display: flex; justify-content: flex-end; padding: .5vw; margin-bottom: -3.5vw;">
                <button class="btn-red close-btn" onclick="cerrarVista();" style="width: 2.3vw; height: 2.3vw;"><b><i class="fa-solid fa-xmark"></i></b></button>
            </div>
            <h3>Información de usuario</h3>
            <div class="cuerpoInfo" id="infoUsuario">
                <!-- Contenido generado dinámicamente -->
            </div>
        </div>

        <div class="divBackForm" id="rolesModule" style="display: none;">
            <div class="close" style="width: 100%; display: flex; justify-content: flex-end; padding: .5vw; margin-bottom: -3.5vw;">
                <button class="btn-red close-btn" onclick="back.style.display = 'none'; rolesModule.style.display = 'none';" style="width: 2.3vw; height: 2.3vw;"><b><i class="fa-solid fa-xmark"></i></b></button>
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
            <button class="btn-green" onclick="newUser()"><b><i class="fa-solid fa-plus"></i> Agregar nuevo usuario</b></button>
            <button class="btn-tematico" onclick="back.style.display = 'flex'; warnBajaRes.style.display = 'flex';"><b><i class="fa-solid fa-user-graduate"></i></i> Establecer baja para usuarios residentes</b></button>
            <button class="btn-tematico" onclick="back.style.display = 'flex'; rolesModule.style.display = 'flex';"><i class="fa-solid fa-check-to-slot"></i> <b>Gestionar roles</b></button>
        </div>

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

                $queryPendientes = "SELECT hsi.*, p.nombre AS nombre_persona, p.apellido AS apellido_persona, s.servicio AS nombre_servicio 
                    FROM hsi 
                    LEFT JOIN personal AS p ON hsi.dni COLLATE utf8mb4_spanish2_ci = p.dni COLLATE utf8mb4_spanish2_ci 
                    LEFT JOIN servicios AS s ON hsi.servicio = s.id 
                    WHERE hsi.estado = :estado 
                    ORDER BY id ASC";

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

                        $getRolesAct = "SELECT u.id, u.id_rol, r.rol AS nombre_rol FROM usuarios_roles_hsi u JOIN roles_hsi r ON u.id_rol = r.id WHERE u.dni = :dni";

                        $stmtRolesAct = $pdo->prepare($getRolesAct);
                        $stmtRolesAct->execute([':dni' => $rowPendientes['dni']]); // Pasar el parámetro :dni

                        while ($row = $stmtRolesAct->fetch(PDO::FETCH_ASSOC)) {
                            echo '<div><i class="fa-solid fa-chevron-right"></i>' . htmlspecialchars($row['nombre_rol']) . '</div>';
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
                                    case 'Declaración Jurada - Usuario prescriptor':
                                        $documento_nombre = 'Prescriptor';
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
                <select name="selectServicioFilter" id="selectServicioFilter" class="select2" placeholder="Seleccionar un servicio para filtrar" style="width: 45%;">
                    <?php
                    echo '<option value=""' . ($selectServicioFilter == '' ? ' selected' : '') . '>Sin filtro por servicio...</option>';
                    $getServicios = "SELECT id, servicio FROM servicios WHERE estado = 'Activo'";
                    $stmtServicios = $pdo->query($getServicios);

                    while ($row = $stmtServicios->fetch(PDO::FETCH_ASSOC)) {
                        $selected = ($row['id'] == $selectServicioFilter) ? 'selected' : ''; // Marcamos como seleccionado si coincide con el filtro actual
                        echo '<option value="' . $row['id'] . '" ' . $selected . '>' . $row['servicio'] . '</option>';
                    }
                    ?>
                </select>



                <input type="text" name="searchInput" id="searchInput" style="width: 45%; height: 3vw; margin-left: 2vw;" placeholder="Buscar por DNI..." oninput="formatNumber(this)">

                <script>
                    function formatNumber(input) {
                        // Eliminar caracteres que no son números
                        const inputValue = input.value.replace(/\D/g, '');

                        // Formatear el número con puntos si no está vacío, de lo contrario, dejar en blanco
                        const formattedNumber = inputValue !== '' ? Number(inputValue).toLocaleString('es-AR') : '';

                        // Actualizar el valor del campo de entrada
                        input.value = formattedNumber;
                    }

                    $(document).ready(function() {
                        $("#selectServicioFilter").select2();

                        // Función para generar los botones de paginación
                        function generarBotonesPaginacion(total_paginas) {
                            var contenedorPaginacion = document.getElementById("contenedorPaginacion");

                            contenedorPaginacion.innerHTML = "";

                            // Generar botones de paginación
                            for (var i = 1; i <= total_paginas; i++) {
                                var botonPagina = document.createElement("button");
                                botonPagina.textContent = i;
                                botonPagina.setAttribute("class", "btn-green paginationBtn");
                                botonPagina.setAttribute("data-pagina", i);
                                botonPagina.addEventListener("click", function() {
                                    var pagina = this.getAttribute("data-pagina");
                                    actualizarTabla(pagina);
                                });
                                contenedorPaginacion.appendChild(botonPagina);
                            }
                        }

                        // Función para actualizar la tabla con los resultados filtrados
                        function actualizarTabla(pagina, searchTerm, selectServicioFilter) {
                            // Ocultar la tabla mientras se cargan los nuevos resultados
                            $("#tablaHabilitados").hide();
                            $(".lds-dual-ring").show(); // Mostrar el elemento de carga

                            // Realizar la solicitud AJAX al controlador PHP para actualizar la tabla
                            $.ajax({
                                url: "controllers/tablaHabilitadosAdm.php",
                                type: "GET",
                                dataType: "html",
                                data: {
                                    pagina: pagina,
                                    searchTerm: searchTerm,
                                    selectServicioFilter: selectServicioFilter
                                },
                                success: function(response) {
                                    // Actualizar la tabla con los nuevos resultados
                                    $("#tablaHabilitados").html(response);
                                    // Mostrar la tabla después de cargar los nuevos resultados
                                    $("#tablaHabilitados").show();
                                    $(".lds-dual-ring").hide(); // Ocultar el elemento de carga


                                    // Generar botones de paginación
                                    generarBotonesPaginacion(response.total_paginas);
                                },
                                error: function(xhr, status, error) {
                                    console.log("Error al realizar la solicitud: " + error);
                                }
                            });
                        }

                        // Evento change del select para actualizar la tabla al cambiar el servicio
                        $("#selectServicioFilter").on("change", function() {
                            var selectServicioFilterValue = $(this).val(); // Obtener el valor seleccionado del select2
                            actualizarTabla(1, $("#searchInput").val(), selectServicioFilterValue); // Llamar a actualizarTabla con el nuevo valor
                        });

                        // Cargar la tabla con los resultados iniciales
                        actualizarTabla(1, $("#searchInput").val(), $("#selectServicioFilter").val());

                        // Función para realizar la búsqueda en tiempo real con retardo
                        var timeout = null;
                        $("#searchInput").on("input", function() {
                            clearTimeout(timeout); // Limpiar el temporizador existente si hay alguno
                            // Configurar un nuevo temporizador para retrasar la búsqueda
                            timeout = setTimeout(function() {
                                // Obtener el valor del campo de búsqueda
                                var searchTerm = $("#searchInput").val();

                                // Obtener el valor seleccionado del select2
                                var selectServicioFilterValue = $("#selectServicioFilter").val();

                                // Llamar a la función actualizarTabla para enviar la solicitud al servidor
                                actualizarTabla(1, searchTerm, selectServicioFilterValue);
                            }, 500); // Retardo de 500 milisegundos (0.5 segundos)
                        });

                        // Función para cambiar de página al hacer clic en los botones de paginación
                        function cambiarPagina(pagina) {
                            // Obtener el valor del campo de búsqueda
                            var searchTerm = $("#searchInput").val();

                            // Obtener el valor seleccionado del select2
                            var selectServicioFilter = $("#selectServicioFilter").val();

                            // Llamar a la función actualizarTabla para enviar la solicitud al servidor con la nueva página
                            actualizarTabla(pagina, searchTerm, selectServicioFilter);
                        }

                        // Código JavaScript para la paginación
                        $("#contenedorPaginacion").on("click", ".paginationBtn", function() {
                            var pagina = $(this).data("pagina");
                            var searchTerm = $("#searchInput").val();
                            var selectServicioFilter = $("#selectServicioFilter").val();
                            actualizarTabla(pagina, searchTerm, selectServicioFilter);
                        });
                    });
                </script>
            </div>

            <div class="tablaHabilitados" id="tablaHabilitados"></div>
            <div class="lds-dual-ring" style="transform: translate(36vw, 0);"></div>
            <div id="contenedorPaginacion"></div>

            <div class="disabledSearch" style="margin-top: 2vw;">
                <h4>Buscar usuario por D.N.I.</h4>
                <input type="text" name="disabledInput" id="disabledInput" style="width: 45%; height: 3vw; margin-left: 2vw;" placeholder="Buscar por DNI..." oninput="formatNumber(this)">

                <button class="btn-green" onclick="loadInfoDelet(disabledInput.value); disabledInput.value=''"><i class="fa-solid fa-magnifying-glass"></i></button>
            </div>


        </div>
    </div>
</div>


<script src="/SGH/public/layouts/modules/hsiPanel/js/hsiAdmin.js"></script>
<?php require_once '../../base/footer.php'; ?>