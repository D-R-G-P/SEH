$(document).ready(function () {
    // --- Variables Globales ---
    let currentServiceFilter = ''; // ID de la especialidad seleccionada para filtrar
    let currentEditingPasoContenidoId = null; // ID del contenido de paso que se está editando en el área lateral
    let currentOpcionPrincipalIdForContentEdit = null; // ID de la opción principal cuyo contenido estamos editando

    // Mock data (mantener para referencia o si el backend no está completamente listo)
    // En un entorno de producción, estos arrays deberían ser eliminados o solo usados para un fallback muy específico.
    let mockServicesData = []; // Ahora se cargará desde el backend
    let mockContenidoPasosData = []; // Ahora se cargará desde el backend
    let mockOpcionesPrincipalesData = []; // Ahora se cargará desde el backend
    let mockSubOpcionesData = []; // Ahora se cargará desde el backend

    // Inicialización al cargar el documento
    // ------------------------------------
    $('#serviceFilter').select2();
    $('#opcionPrincipalServicioId').select2({
        dropdownParent: $('#opcionPrincipalModal')
    });
    $('#opcionPrincipalParentOpcionId').select2({
        dropdownParent: $('#opcionPrincipalModal')
    });
    $('#subOpcionPasoDestino').select2({
        dropdownParent: $('#subOpcionModal')
    });

    // Cargar datos iniciales desde el backend
    loadAllDataAndPopulateUI();

    // --- Abrir modales ---
    $('#openNewServiceModalBtn').on('click', function () {
        resetServiceForm();
        openModal('serviceModal');
    });
    $('#openNewOpcionPrincipalModalBtn').on('click', function () {
        resetOpcionPrincipalForm();
        const selectedServiceId = $('#opcionPrincipalServicioId').val();
        populateParentOpcionSelect(null, selectedServiceId);
        openModal('opcionPrincipalModal');
    });
    $('#opcionPrincipalServicioId').on('change', function () {
        const selectedServiceId = $(this).val();
        populateParentOpcionSelect($('#opcionPrincipalId').val(), selectedServiceId);
    });
    // --- Manejador de Eventos para Filtro de Servicio ---
    $('#serviceFilter').on('change', function() {
        currentServiceFilter = $(this).val();
        loadOpcionesPrincipales(); // Recargar las opciones principales con el nuevo filtro
        hideContentEditingArea(); // Ocultar el área de edición al cambiar el filtro principal
    });

    // --- Manejadores de Eventos para Formularios ---
    // --------------------------------------------

    // Manejador para el formulario de Servicios
    $('#serviceForm').on('submit', function (e) {
        e.preventDefault();
        const id = $('#serviceId').val();
        const nombre = $('#serviceName').val().trim();
        const descripcion = $('#serviceDescription').val().trim();
        const estado = 'activo'; // Por defecto, se asume activo al crear/actualizar desde este formulario

        if (!nombre) {
            toast("El nombre de la especialidad es obligatorio.", 'error', 2500);
            return;
        }

        const serviceData = { nombre, descripcion, estado };
        let url = '';
        let method = 'POST';

        if (id) {
            url = 'controllers/servicios_api.php?action=update';
            serviceData.id = id;
        } else {
            url = 'controllers/servicios_api.php?action=create';
        }

        $.ajax({
            url: url,
            method: method,
            data: serviceData, // jQuery serializa esto como x-www-form-urlencoded por defecto para POST
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    toast(response.message, 'success', 2500);
                    closeModal('serviceModal');
                    resetServiceForm();
                    loadAllDataAndPopulateUI(); // Recargar todos los datos y actualizar la UI
                } else {
                    toast(response.message, 'error', 2500);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                toast("Error en la operación del servicio: " + textStatus, 'error', 2500);
                console.error("AJAX error:", textStatus, errorThrown, jqXHR.responseText);
            }
        });
    });

    // Manejador para el formulario de Opciones Principales
    $('#opcionPrincipalForm').on('submit', function (e) {
        e.preventDefault();

        const id = $('#opcionPrincipalId').val();
        const servicio_id = $('#opcionPrincipalServicioId').val();
        const parent_opcion_id = $('#opcionPrincipalParentOpcionId').val() || null;
        const texto_opcion = $('#opcionPrincipalTexto').val().trim();
        const texto_contenido_directo = $('#opcionPrincipalTextoContenido').val().trim();
        const estado = $('#opcionPrincipalEstado').val();

        if (!servicio_id || !texto_opcion) {
            toast("Completa los campos obligatorios: Especialidad y Texto de la Opción.", 'error', 2500);
            return;
        }

        let paso_asociado_id = null;
        let promiseContenido = Promise.resolve(); // Promesa que se resuelve inmediatamente si no hay contenido

        if (texto_contenido_directo) {
            // Si hay contenido directo, manejamos la creación/actualización del contenido_paso primero
            const contenidoData = {
                titulo: texto_opcion, // Usar el texto de la opción como título del contenido
                texto_completo: texto_contenido_directo,
                estado: estado
            };

            if (id) {
                // Si estamos editando una opción, verificamos si ya tiene un paso_asociado_id
                const existingOpcion = mockOpcionesPrincipalesData.find(o => o.id == id);
                if (existingOpcion && existingOpcion.paso_asociado_id) {
                    // Actualizar contenido existente
                    urlContenido = 'controllers/contenido_pasos_api.php?action=update';
                    contenidoData.id = existingOpcion.paso_asociado_id;
                    paso_asociado_id = existingOpcion.paso_asociado_id;
                } else {
                    // Crear nuevo contenido para una opción existente que no tenía
                    urlContenido = 'controllers/contenido_pasos_api.php?action=create';
                }
            } else {
                // Crear nuevo contenido para una nueva opción
                urlContenido = 'controllers/contenido_pasos_api.php?action=create';
            }

            promiseContenido = $.ajax({
                url: urlContenido,
                method: 'POST',
                data: contenidoData,
                dataType: 'json'
            }).then(function (response) {
                if (response.success) {
                    if (response.newId) { // Si se creó un nuevo contenido
                        paso_asociado_id = response.newId;
                    }
                    toast(`Contenido asociado ${id ? 'actualizado' : 'creado'}.`, 'info', 1500);
                } else {
                    toast(`Error al manejar el contenido asociado: ${response.message}`, 'error', 2500);
                    return Promise.reject(`Error al manejar el contenido: ${response.message}`);
                }
            }).catch(function (jqXHR, textStatus, errorThrown) {
                toast("Error de AJAX al manejar el contenido asociado.", 'error', 2500);
                console.error("AJAX error (contenido):", textStatus, errorThrown, jqXHR.responseText);
                return Promise.reject("Error de AJAX al manejar el contenido asociado.");
            });
        } else {
            // Si no hay texto de contenido directo, y la opción tenía un paso asociado, lo desvinculamos
            if (id) {
                const existingOpcion = mockOpcionesPrincipalesData.find(o => o.id == id);
                if (existingOpcion && existingOpcion.paso_asociado_id) {
                    toast('Contenido asociado desvinculado de la opción.', 'info', 1500);
                    // No eliminamos el contenido de paso, solo lo desvinculamos.
                    // La eliminación del contenido de paso se hace explícitamente desde su sección.
                }
            }
            paso_asociado_id = null;
        }

        promiseContenido.then(() => {
            const opcionData = {
                servicio_id: parseInt(servicio_id),
                parent_opcion_id: parent_opcion_id ? parseInt(parent_opcion_id) : null,
                texto_opcion: texto_opcion,
                paso_asociado_id: paso_asociado_id, // Usamos el ID del contenido recién creado/actualizado
                texto_contenido: texto_contenido_directo || null, // Guardamos el texto directo también
                estado: estado
            };

            let urlOpcion = '';
            if (id) {
                urlOpcion = 'controllers/opciones_principales_api.php?action=update';
                opcionData.id = id;
            } else {
                urlOpcion = 'controllers/opciones_principales_api.php?action=create';
            }

            $.ajax({
                url: urlOpcion,
                method: 'POST',
                data: opcionData,
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        toast(response.message, 'success', 2500);
                        closeModal('opcionPrincipalModal');
                        resetOpcionPrincipalForm();
                        loadAllDataAndPopulateUI();
                    } else {
                        toast(response.message, 'error', 2500);
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    toast("Error en la operación de opción principal: " + textStatus, 'error', 2500);
                    console.error("AJAX error (opcion principal):", textStatus, errorThrown, jqXHR.responseText);
                }
            });
        }).catch(() => {
            // La promesa de contenido falló, no hacemos nada más aquí ya que el toast ya se mostró
        });
    });

    // Manejador para el formulario de Contenido de Paso
    $('#contenidoPasoForm').on('submit', function (e) {
        e.preventDefault();

        const id = $('#contenidoPasoId').val();
        const titulo = $('#contenidoPasoTitulo').val().trim();
        const texto_completo = $('#contenidoPasoTextoCompleto').val().trim();
        const estado = $('#contenidoPasoEstado').val();

        if (!titulo || !texto_completo) {
            toast("Completa el título y el texto completo del Contenido.", 'error', 2500);
            return;
        }

        const data = { titulo, texto_completo, estado };
        let url = '';

        if (id) {
            url = 'controllers/contenido_pasos_api.php?action=update';
            data.id = id;
        } else {
            url = 'controllers/contenido_pasos_api.php?action=create';
        }

        $.ajax({
            url: url,
            method: 'POST',
            data: data,
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    toast(response.message, 'success', 2500);
                    closeModal('contenidoPasoModal');
                    resetContenidoPasoForm();
                    loadAllDataAndPopulateUI(); // Recargar todo para asegurar consistencia
                    if (currentEditingPasoContenidoId) {
                        // Si estábamos editando un contenido desde el área lateral, recargar sus sub-opciones
                        showContentEditingArea(currentEditingPasoContenidoId, currentOpcionPrincipalIdForContentEdit);
                    }
                } else {
                    toast(response.message, 'error', 2500);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                toast("Error en la operación de contenido de paso: " + textStatus, 'error', 2500);
                console.error("AJAX error:", textStatus, errorThrown, jqXHR.responseText);
            }
        });
    });

    // Manejador para el formulario de Sub-Opciones
    $('#subOpcionForm').on('submit', function (e) {
        e.preventDefault();

        const id = $('#subOpcionId').val();
        const paso_origen_id = $('#subOpcionPasoOrigenId').val();
        const texto_sub_opcion = $('#subOpcionTexto').val().trim();
        const paso_destino_id = $('#subOpcionPasoDestino').val() || null;
        const estado = $('#subOpcionEstado').val();

        if (!paso_origen_id || !texto_sub_opcion) {
            toast("Completa los campos obligatorios de la Sub-Opción.", 'error', 2500);
            return;
        }

        const data = {
            paso_origen_id: parseInt(paso_origen_id),
            texto_sub_opcion: texto_sub_opcion,
            paso_destino_id: paso_destino_id ? parseInt(paso_destino_id) : null,
            estado: estado
        };
        let url = '';

        if (id) {
            url = 'controllers/sub_opciones_api.php?action=update';
            data.id = id;
        } else {
            url = 'controllers/sub_opciones_api.php?action=create';
        }

        $.ajax({
            url: url,
            method: 'POST',
            data: data,
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    toast(response.message, 'success', 2500);
                    closeModal('subOpcionModal');
                    resetSubOpcionForm();
                    loadAllDataAndPopulateUI(); // Recargar todo
                    loadSubOpciones(currentEditingPasoContenidoId); // Recargar sub-opciones específicas
                } else {
                    toast(response.message, 'error', 2500);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                toast("Error en la operación de sub-opción: " + textStatus, 'error', 2500);
                console.error("AJAX error:", textStatus, errorThrown, jqXHR.responseText);
            }
        });
    });


    // --- Funciones de Modales ---
    // ----------------------------
    window.openModal = function openModal(modalId) {
        $(`#${modalId}`).css('display', 'flex');
    }

    window.closeModal = function closeModal(modalId) {
        $(`#${modalId}`).css('display', 'none');
        if (currentOpcionPrincipalIdForContentEdit) {
            $('#contentEditingArea').addClass('active');
        }
    }

    // --- Funciones de Carga, Edición y Reset de Datos ---
    // ----------------------------------------------------

    /**
     * Carga todos los datos desde el backend y actualiza la UI.
     * Esto es útil después de cualquier operación CRUD para asegurar que la UI esté sincronizada.
     */
    async function loadAllDataAndPopulateUI() {
        try {
            const [servicesResponse, contenidosResponse, opcionesResponse, subOpcionesResponse] = await Promise.all([
                $.get('controllers/servicios_api.php?action=getAll'),
                $.get('controllers/contenido_pasos_api.php?action=getAll'),
                $.get('controllers/opciones_principales_api.php?action=getAll'),
                $.get('controllers/sub_opciones_api.php?action=getAll')
            ]);

            mockServicesData = servicesResponse.success ? servicesResponse.data : [];
            mockContenidoPasosData = contenidosResponse.success ? contenidosResponse.data : [];
            mockOpcionesPrincipalesData = opcionesResponse.success ? opcionesResponse.data : [];
            mockSubOpcionesData = subOpcionesResponse.success ? subOpcionesResponse.data : [];

            loadServices();
            populateServiceFilterSelect();
            populateOpcionPrincipalServiceSelect();
            populateContenidoPasosSelects();
            loadOpcionesPrincipales(); // Esto también maneja el filtro
            // Si hay un área de edición de contenido abierta, recargarla
            if (currentEditingPasoContenidoId && currentOpcionPrincipalIdForContentEdit) {
                showContentEditingArea(currentEditingPasoContenidoId, currentOpcionPrincipalIdForContentEdit);
            } else {
                hideContentEditingArea();
            }

        } catch (error) {
            toast("Error al cargar todos los datos iniciales.", 'error', 3000);
            console.error("Error loading all data:", error);
        }
    }


    /**
     * Carga y muestra la lista de servicios.
     */
    function loadServices() {
        $('#servicesList').empty();
        if (mockServicesData.length === 0) {
            $('#servicesList').append('<div style="padding: 15px; text-align: center; color: #666;">No hay especialidades configuradas.</div>');
        }
        mockServicesData.forEach(service => {
            // Genera un color único y fuerte para cada service.id usando HSL
            function getUniqueColorById(id) {
                // Usa el id para generar un ángulo de tono bien distribuido
                const hue = (parseInt(id, 10) * 137) % 360; // 137 es un número primo para mejor dispersión
                return `hsl(${hue}, 50%, 45%)`; // Saturación y luminosidad altas para colores fuertes
            }
            const color = getUniqueColorById(service.id);
            const $serviceItem = $(`
                <div class="service-item">
                    <div class="item-details">
                        <div class="item-header">
                            <span class="service-name-tag" style="background:${color};color:#fff;padding:2px 8px;border-radius:4px;">
                                ${service.nombre}
                            </span>
                            <span class="item-id">ID: ${service.id}</span>
                        </div>
                        <div class="item-text-snippet"><em>${service.descripcion || 'Sin descripción'}</em></div>
                    </div>
                    <div class="item-actions">
                        <button class="btn-green" data-id="${service.id}" data-type="service">Editar</button>
                        <button class="btn-red" data-id="${service.id}" data-type="service">Eliminar</button>
                    </div>
                </div>
            `);
            $('#servicesList').append($serviceItem);
        });
        $('#servicesList').find('.btn-green').off('click').on('click', function () {
            editService($(this).data('id'));
            openModal('serviceModal');
        });
        $('#servicesList').find('.btn-red').off('click').on('click', function () { deleteItem($(this).data('id'), 'service'); });
    }

    /**
     * Carga los datos de un Servicio en el formulario para edición.
     */
    function editService(id) {
        $.ajax({
            url: 'controllers/servicios_api.php?action=getById&id=' + id,
            method: 'GET',
            dataType: 'json',
            success: function (response) {
                if (response.success && response.data) {
                    const service = response.data;
                    $('#serviceId').val(service.id);
                    $('#serviceName').val(service.nombre);
                    $('#serviceDescription').val(service.descripcion);
                } else {
                    toast(`Especialidad con ID ${id} no encontrada.`, 'error', 2500);
                    resetServiceForm();
                }
            },
            error: function () {
                toast("Error al cargar la especialidad para edición.", 'error', 2500);
            }
        });
    }

    /**
     * Limpia el formulario de Servicios.
     */
    window.resetServiceForm = function resetServiceForm() {
        $('#serviceForm')[0].reset();
        $('#serviceId').val('');
    }

    /**
     * Rellena el selector de filtro de servicios.
     */
    function populateServiceFilterSelect() {
        const $serviceFilterSelect = $('#serviceFilter');
        $serviceFilterSelect.empty();
        $serviceFilterSelect.append('<option value="">-- Mostrar Todas las Opciones --</option>');
        mockServicesData.forEach(service => {
            $serviceFilterSelect.append(`<option value="${service.id}">${service.id} - ${service.nombre}</option>`);
        });
        $serviceFilterSelect.val(currentServiceFilter).trigger('change.select2');
    }

    /**
     * Rellena el selector de servicio en el formulario de Opciones Principales.
     */
    function populateOpcionPrincipalServiceSelect() {
        const $select = $('#opcionPrincipalServicioId');
        $select.empty();
        $select.append('<option value="">-- Seleccionar Especialidad --</option>');
        mockServicesData.forEach(service => {
            $select.append(`<option value="${service.id}">${service.id} - ${service.nombre}</option>`);
        });
        if (currentServiceFilter) {
            $select.val(currentServiceFilter);
        }
        $select.trigger('change.select2');
    }

    /**
     * Rellena el selector de opción padre en el formulario de Opciones Principales.
     * @param {number} currentOpcionId - El ID de la opción que se está editando (para excluirla).
     * @param {number} serviceIdFilter - El ID de la especialidad para filtrar las opciones.
     */
    function populateParentOpcionSelect(currentOpcionId = null, serviceIdFilter = null) {
        const $select = $('#opcionPrincipalParentOpcionId');
        $select.empty();
        $select.append('<option value="">-- Sin Padre (Opción Principal) --</option>');

        let url = 'controllers/opciones_principales_api.php?action=getAll';
        if (serviceIdFilter) {
            url = `controllers/opciones_principales_api.php?action=getByServiceId&service_id=${serviceIdFilter}`;
        }

        $.ajax({
            url: url,
            method: 'GET',
            dataType: 'json',
            success: function (response) {
                if (response.success && response.data) {
                    response.data.forEach(opcion => {
                        if (opcion.id != currentOpcionId) { // Importante: usar != para comparar string con number
                            $select.append(`<option value="${opcion.id}">${opcion.id} - ${opcion.texto_opcion}</option>`);
                        }
                    });
                }
                $select.trigger('change.select2');
            },
            error: function () {
                toast("Error al cargar opciones padre.", 'error', 2500);
                $select.trigger('change.select2'); // Asegurar que Select2 se actualice incluso con error
            }
        });
    }

    /**
     * Carga y muestra la lista de opciones principales, aplicando el filtro de servicio si está activo.
     */
    function loadOpcionesPrincipales() {
        $('#opcionesPrincipalesList').empty();
        let url = 'controllers/opciones_principales_api.php?action=getAll';
        if (currentServiceFilter) {
            url = `controllers/opciones_principales_api.php?action=getByServiceId&service_id=${currentServiceFilter}`;
        }

        $.ajax({
            url: url,
            method: 'GET',
            dataType: 'json',
            success: function (response) {
                if (response.success && response.data) {
                    const filteredOpciones = response.data; // Ya vienen filtradas si se usó serviceIdFilter

                    if (filteredOpciones.length === 0 && currentServiceFilter) {
                        $('#opcionesPrincipalesList').append('<div style="padding: 15px; text-align: center; color: #666;">No hay opciones principales configuradas para esta especialidad.</div>');
                    } else if (filteredOpciones.length === 0) {
                        $('#opcionesPrincipalesList').append('<div style="padding: 15px; text-align: center; color: #666;">No hay opciones principales configuradas.</div>');
                    }

                    filteredOpciones.forEach(opcion => {
                        const servicioNombre = mockServicesData.find(s => s.id == opcion.servicio_id)?.nombre || 'Desconocido';
                        let contenidoTitulo = 'Sin Contenido / Finaliza';
                        let contenidoIdDisplay = '';
                        let isContentClickable = false;

                        if (opcion.paso_asociado_id) {
                            const contenidoAsociado = mockContenidoPasosData.find(p => p.id == opcion.paso_asociado_id);
                            if (contenidoAsociado) {
                                contenidoTitulo = contenidoAsociado.titulo;
                                contenidoIdDisplay = ` (ID: ${opcion.paso_asociado_id})`;
                                isContentClickable = true;
                            }
                        } else if (opcion.texto_contenido) {
                            contenidoTitulo = 'Contenido Directo';
                        }

                        const flowContentClass = isContentClickable ? 'flow-to-content' : '';

                        const parentOpcion = opcion.parent_opcion_id ? mockOpcionesPrincipalesData.find(op => op.id == opcion.parent_opcion_id) : null;
                        const parentInfo = parentOpcion ? `<div class="opcion-flow">Padre: <strong>${parentOpcion.texto_opcion}</strong> (ID: ${parentOpcion.id})</div>` : '';

                        const color = (() => {
                            // Genera el mismo color único que en loadServices()
                            const hue = (parseInt(opcion.servicio_id, 10) * 137) % 360;
                            return `hsl(${hue}, 50%, 45%)`;
                        })();
                        const $opcionItem = $(`
                            <div class="opcion-principal-item">
                                <div class="item-details">
                                    <div class="item-header">
                                        <span class="service-name-tag" style="background:${color};color:#fff;padding:2px 8px;border-radius:4px;">
                                            ${servicioNombre}
                                        </span>
                                        <span class="item-id">ID: ${opcion.id}</span>
                                        <span class="item-status status-${opcion.estado}">${opcion.estado === 'activo' ? '🟢 Activa' : '🔴 Inactiva'}</span>
                                    </div>
                                    <div class="item-title"><strong>${opcion.texto_opcion}</strong></div>
                                    ${parentInfo}
                                    <div class="opcion-flow">
                                        Lleva a Contenido: <span class="${flowContentClass}" data-content-id="${opcion.paso_asociado_id}">${contenidoTitulo}${contenidoIdDisplay}</span>
                                    </div>
                                </div>
                                <div class="item-actions">
                                    <button class="btn-green" data-id="${opcion.id}" data-type="opcionPrincipal">Editar</button>
                                    <button class="btn-red" data-id="${opcion.id}" data-type="opcionPrincipal">Eliminar</button>
                                </div>
                            </div>
                        `);
                        $('#opcionesPrincipalesList').append($opcionItem);
                    });

                    $('#opcionesPrincipalesList').find('.btn-green').off('click').on('click', function () {
                        editOpcionPrincipal($(this).data('id'));
                        openModal('opcionPrincipalModal');
                    });
                    $('#opcionesPrincipalesList').find('.btn-red').off('click').on('click', function () { deleteItem($(this).data('id'), 'opcionPrincipal'); });
                    $('#opcionesPrincipalesList').find('.flow-to-content').off('click').on('click', function () {
                        const contentId = $(this).data('content-id');
                        if (contentId) {
                            editContenidoPaso(contentId);
                            openModal('contenidoPasoModal');
                            $('#contentEditingArea').removeClass('active');
                        } else {
                            toast('Esta opción no tiene un contenido asociado con un ID para editar.', 'info', 2500);
                        }
                    });
                } else {
                    toast("Error al cargar opciones principales: " + response.message, 'error', 2500);
                }
                hideContentEditingArea();
            },
            error: function (jqXHR, textStatus, errorThrown) {
                toast("Error de AJAX al cargar opciones principales.", 'error', 2500);
                console.error("AJAX error:", textStatus, errorThrown, jqXHR.responseText);
                hideContentEditingArea();
            }
        });
    }

    /**
     * Carga los datos de una Opción Principal en el formulario para edición
     * y prepara el área de edición de Contenido/Sub-Opciones si aplica.
     */
    function editOpcionPrincipal(id) {
        $.ajax({
            url: 'controllers/opciones_principales_api.php?action=getById&id=' + id,
            method: 'GET',
            dataType: 'json',
            success: function (response) {
                if (response.success && response.data) {
                    const opcion = response.data;
                    $('#opcionPrincipalId').val(opcion.id);
                    $('#opcionPrincipalServicioId').val(opcion.servicio_id).trigger('change.select2');
                    populateParentOpcionSelect(opcion.id, opcion.servicio_id);
                    $('#opcionPrincipalParentOpcionId').val(opcion.parent_opcion_id).trigger('change.select2');
                    $('#opcionPrincipalTexto').val(opcion.texto_opcion);
                    $('#opcionPrincipalTextoContenido').val(opcion.texto_contenido || ''); // Carga el texto_contenido directo
                    $('#opcionPrincipalEstado').val(opcion.estado);

                    if (opcion.paso_asociado_id) {
                        showContentEditingArea(opcion.paso_asociado_id, opcion.id);
                    } else {
                        hideContentEditingArea();
                    }
                } else {
                    toast(`Opción principal con ID ${id} no encontrada.`, 'error', 2500);
                    hideContentEditingArea();
                    resetOpcionPrincipalForm();
                }
            },
            error: function () {
                toast("Error al cargar la opción principal para edición.", 'error', 2500);
                hideContentEditingArea();
            }
        });
    }

    /**
     * Limpia el formulario de Opciones Principales.
     */
    window.resetOpcionPrincipalForm = function resetOpcionPrincipalForm() {
        $('#opcionPrincipalForm')[0].reset();
        $('#opcionPrincipalId').val('');
        $('#opcionPrincipalEstado').val('activo');
        $('#opcionPrincipalTextoContenido').val('');
        if (currentServiceFilter) {
            $('#opcionPrincipalServicioId').val(currentServiceFilter).trigger('change.select2');
        } else {
            $('#opcionPrincipalServicioId').val('').trigger('change.select2');
        }
        const selectedServiceId = $('#opcionPrincipalServicioId').val();
        populateParentOpcionSelect(null, selectedServiceId);
        $('#opcionPrincipalParentOpcionId').val('').trigger('change.select2');
        populateContenidoPasosSelects();
    }

    /**
     * Muestra el área de edición de Contenido y Sub-Opciones.
     * @param {number} pasoContenidoId - El ID del contenido de paso a cargar.
     * @param {number} opcionPrincipalId - El ID de la opción principal que estamos editando.
     */
    function showContentEditingArea(pasoContenidoId, opcionPrincipalId) {
        $('#contentEditingArea').addClass('active');
        currentEditingPasoContenidoId = pasoContenidoId;
        currentOpcionPrincipalIdForContentEdit = opcionPrincipalId;

        $.ajax({
            url: 'controllers/contenido_pasos_api.php?action=getById&id=' + pasoContenidoId,
            method: 'GET',
            dataType: 'json',
            success: function (response) {
                if (response.success && response.data) {
                    const currentContenido = response.data;
                    const contenidoTitulo = currentContenido.titulo;
                    const contenidoTexto = currentContenido.texto_completo;

                    $('#contentEditingArea').html(`
                        <h2>Detalle del Contenido y Sub-Opciones</h2>
                        <h3>Contenido Asociado: ${contenidoTitulo} (ID: ${pasoContenidoId})</h3>
                        <div class="contenido-paso-display">
                            <div class="item-title">${contenidoTitulo}</div>
                            <div class="item-text-snippet">${contenidoTexto}</div>
                            <div class="item-actions">
                                <button class="btn-green" data-id="${pasoContenidoId}" data-type="contenidoPaso">Editar Contenido</button>
                                <button class="btn-red" data-id="${pasoContenidoId}" data-type="contenidoPaso">Eliminar Contenido</button>
                            </div>
                        </div>

                        <h3 style="margin-top: 30px;">Sub-Opciones de este Contenido</h3>
                        <div id="subOpcionesList" class="item-list"></div>
                        <div class="btn-group">
                            <button type="button" id="openNewSubOpcionModalBtn" class="btn-tematico"><b>+</b> Nueva Sub-Opción</button>
                        </div>
                    `);

                    $('#contentEditingArea').find('.btn-green[data-type="contenidoPaso"]').off('click').on('click', function () {
                        editContenidoPaso($(this).data('id'));
                        openModal('contenidoPasoModal');
                    });
                    $('#contentEditingArea').find('.btn-red[data-type="contenidoPaso"]').off('click').on('click', function () {
                        deleteItem($(this).data('id'), 'contenidoPaso');
                    });
                    $('#openNewSubOpcionModalBtn').off('click').on('click', function () {
                        resetSubOpcionForm();
                        openModal('subOpcionModal');
                    });

                    loadSubOpciones(pasoContenidoId);
                    $('#subOpcionPasoOrigenId').val(pasoContenidoId);
                    resetSubOpcionForm();
                } else {
                    toast(`Contenido de paso con ID ${pasoContenidoId} no encontrado.`, 'error', 2500);
                    hideContentEditingArea();
                }
            },
            error: function () {
                toast("Error al cargar el contenido de paso para edición.", 'error', 2500);
                hideContentEditingArea();
            }
        });
    }

    /**
     * Oculta el área de edición de Contenido y Sub-Opciones.
     */
    function hideContentEditingArea() {
        $('#contentEditingArea').removeClass('active');
        $('#contentEditingArea').html(`
            <h2>Detalle del Contenido y Sub-Opciones</h2>
            <p>Selecciona una Opción Principal para ver y editar su contenido y sub-opciones.</p>
        `);
        currentEditingPasoContenidoId = null;
        currentOpcionPrincipalIdForContentEdit = null;
        resetContenidoPasoForm();
        resetSubOpcionForm();
    }


    /**
     * Rellena los selectores de contenido de pasos (para Opciones Principales y Sub-Opciones).
     */
    function populateContenidoPasosSelects() {
        const $subOpcionPasoDestino = $('#subOpcionPasoDestino');

        $subOpcionPasoDestino.empty().append('<option value="">-- Finaliza aquí / Sin destino específico --</option>');

        $.ajax({
            url: 'controllers/contenido_pasos_api.php?action=getAll',
            method: 'GET',
            dataType: 'json',
            success: function (response) {
                if (response.success && response.data) {
                    response.data.forEach(contenido => {
                        const associatedOpcion = mockOpcionesPrincipalesData.find(op => op.paso_asociado_id == contenido.id);
                        const optionText = associatedOpcion ? ` (Asociado a Opción Principal: ${associatedOpcion.texto_opcion})` : '';
                        $subOpcionPasoDestino.append(`<option value="${contenido.id}">${contenido.id} - ${contenido.titulo}${optionText}</option>`);
                    });
                }
                $subOpcionPasoDestino.trigger('change.select2');
            },
            error: function () {
                toast("Error al cargar contenidos para selectores.", 'error', 2500);
                $subOpcionPasoDestino.trigger('change.select2');
            }
        });
    }

    /**
     * Carga los datos de un Contenido de Paso en su formulario para edición.
     */
    function editContenidoPaso(id) {
        $.ajax({
            url: 'controllers/contenido_pasos_api.php?action=getById&id=' + id,
            method: 'GET',
            dataType: 'json',
            success: function (response) {
                if (response.success && response.data) {
                    const contenido = response.data;
                    $('#contenidoPasoId').val(contenido.id);
                    $('#contenidoPasoTitulo').val(contenido.titulo);
                    $('#contenidoPasoTextoCompleto').val(contenido.texto_completo);
                    $('#contenidoPasoEstado').val(contenido.estado);
                } else {
                    toast(`Contenido con ID ${id} no encontrado.`, 'error', 2500);
                    resetContenidoPasoForm();
                }
            },
            error: function () {
                toast("Error al cargar el contenido de paso para edición.", 'error', 2500);
            }
        });
    }

    /**
     * Limpia el formulario de Contenido de Paso.
     */
    window.resetContenidoPasoForm = function resetContenidoPasoForm() {
        $('#contenidoPasoForm')[0].reset();
        $('#contenidoPasoId').val('');
        $('#contenidoPasoEstado').val('activo');
    }


    /**
     * Carga y muestra la lista de sub-opciones para un contenido de paso específico.
     */
    function loadSubOpciones(pasoOrigenId) {
        $('#subOpcionesList').empty();
        $.ajax({
            url: 'controllers/sub_opciones_api.php?action=getByPasoOrigenId&paso_origen_id=' + pasoOrigenId,
            method: 'GET',
            dataType: 'json',
            success: function (response) {
                if (response.success && response.data) {
                    const filteredSubOpciones = response.data;

                    if (filteredSubOpciones.length === 0) {
                        $('#subOpcionesList').append('<div style="padding: 15px; text-align: center; color: #666;">No hay sub-opciones configuradas para este contenido.</div>');
                    }

                    filteredSubOpciones.forEach(subOpcion => {
                        const destinoContenido = mockContenidoPasosData.find(p => p.id == subOpcion.paso_destino_id);
                        let destinoTitulo = 'FINALIZA O SIN DESTINO';
                        let destinoIdDisplay = '';
                        let isDestinoClickable = false;

                        if (destinoContenido) {
                            destinoTitulo = destinoContenido.titulo;
                            destinoIdDisplay = ` (ID: ${subOpcion.paso_destino_id})`;
                            isDestinoClickable = true;
                        }

                        const flowDestinationClass = isDestinoClickable ? 'flow-destination' : '';

                        const $subOpcionItem = $(`
                            <div class="sub-opcion-item">
                                <div class="item-details">
                                    <div class="item-header">
                                        <span class="item-id">ID: ${subOpcion.id}</span>
                                        <span class="item-status status-${subOpcion.estado}">${subOpcion.estado === 'activo' ? '🟢 Activa' : '🔴 Inactiva'}</span>
                                    </div>
                                    <div class="item-title"><strong>${subOpcion.texto_sub_opcion}</strong></div>
                                    <div class="opcion-flow">
                                        Lleva a Contenido: <span class="${flowDestinationClass}" data-content-id="${subOpcion.paso_destino_id}">${destinoTitulo}${destinoIdDisplay}</span>
                                    </div>
                                </div>
                                <div class="item-actions">
                                    <button class="btn-green" data-id="${subOpcion.id}" data-type="subOpcion">Editar</button>
                                    <button class="btn-red" data-id="${subOpcion.id}" data-type="subOpcion">Eliminar</button>
                                </div>
                            </div>
                        `);
                        $('#subOpcionesList').append($subOpcionItem);
                    });

                    $('#subOpcionesList').find('.btn-green').off('click').on('click', function () {
                        editSubOpcion($(this).data('id'));
                        openModal('subOpcionModal');
                    });
                    $('#subOpcionesList').find('.btn-red').off('click').on('click', function () { deleteItem($(this).data('id'), 'subOpcion'); });
                    $('#subOpcionesList').find('.flow-destination').off('click').on('click', function () {
                        const contentId = $(this).data('content-id');
                        if (contentId) {
                            editContenidoPaso(contentId);
                            openModal('contenidoPasoModal');
                            $('#contentEditingArea').removeClass('active');
                        } else {
                            toast('Esta sub-opción no tiene un contenido asociado con un ID para editar.', 'info', 2500);
                        }
                    });
                } else {
                    toast("Error al cargar sub-opciones: " + response.message, 'error', 2500);
                }
            },
            error: function () {
                toast("Error de AJAX al cargar sub-opciones.", 'error', 2500);
            }
        });
    }

    /**
     * Carga los datos de una Sub-Opción en su formulario para edición.
     */
    function editSubOpcion(id) {
        $.ajax({
            url: 'controllers/sub_opciones_api.php?action=getById&id=' + id,
            method: 'GET',
            dataType: 'json',
            success: function (response) {
                if (response.success && response.data) {
                    const subOpcion = response.data;
                    $('#subOpcionId').val(subOpcion.id);
                    $('#subOpcionPasoOrigenId').val(subOpcion.paso_origen_id);
                    $('#subOpcionTexto').val(subOpcion.texto_sub_opcion);
                    $('#subOpcionPasoDestino').val(subOpcion.paso_destino_id === null ? '' : subOpcion.paso_destino_id).trigger('change.select2');
                    $('#subOpcionEstado').val(subOpcion.estado);
                } else {
                    toast(`Sub-opción con ID ${id} no encontrada.`, 'error', 2500);
                    resetSubOpcionForm();
                }
            },
            error: function () {
                toast("Error al cargar la sub-opción para edición.", 'error', 2500);
            }
        });
    }

    /**
     * Limpia el formulario de Sub-Opciones.
     */
    window.resetSubOpcionForm = function resetSubOpcionForm() {
        $('#subOpcionForm')[0].reset();
        $('#subOpcionId').val('');
        $('#subOpcionEstado').val('activo');
        $('#subOpcionPasoOrigenId').val(currentEditingPasoContenidoId);
        populateContenidoPasosSelects();
        $('#subOpcionPasoDestino').val('').trigger('change.select2');
    }


    /**
     * Función genérica para eliminar items.
     * @param {number} id - ID del item a eliminar.
     * @param {string} type - Tipo de item ('service', 'opcionPrincipal', 'contenidoPaso', 'subOpcion').
     */
    function deleteItem(id, type) {
        if (confirm(`¿Estás seguro de que quieres eliminar este ${type} con ID ${id}? Esta acción es irreversible y podría afectar relaciones.`)) {
            let url = `controllers/${type}s_api.php?action=delete`; // Construye la URL dinámicamente
            if (type === 'opcionPrincipal') {
                url = `controllers/opciones_principales_api.php?action=delete`;
            } else if (type === 'contenidoPaso') {
                url = `controllers/contenido_pasos_api.php?action=delete`;
            } else if (type === 'subOpcion') {
                url = `controllers/sub_opciones_api.php?action=delete`;
            }

            $.ajax({
                url: url,
                method: 'POST', // O DELETE, dependiendo de cómo configures tu backend
                data: { id: id },
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        toast(response.message, 'success', 2500);
                        loadAllDataAndPopulateUI(); // Recargar todos los datos después de la eliminación
                        hideContentEditingArea(); // Ocultar el área de edición si el elemento principal fue eliminado
                    } else {
                        toast(response.message, 'error', 2500);
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    toast("Error al eliminar el item: " + textStatus, 'error', 2500);
                    console.error("AJAX error:", textStatus, errorThrown, jqXHR.responseText);
                }
            });
        }
    }
});
