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
                <button class="btn-red close-btn" onclick="cerrarVista();" style="width: 2.3vw; height: 2.3vw;"><b><i class="fa-solid fa-xmark"></i></b></button>
            </div>
            <h3>Información de usuario</h3>
            <div class="cuerpoInfo" id="infoUsuario">
                <!-- Contenido generado dinámicamente -->
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