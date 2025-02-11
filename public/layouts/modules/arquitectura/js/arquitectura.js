$(document).ready(function () {
    $('#servicio').select2({
        placeholder: "Seleccionar un servicio",
        allowClear: false
    });
    $('#tipo').select2({
        placeholder: "Seleccionar un tipo",
        allowClear: false
    });
    $('#unidad_0').select2({
        placeholder: "Seleccionar una unidad padre",
        allowClear: false
    });
    $('#servicio-edit').select2({
        placeholder: "Seleccionar un servicio",
        allowClear: false
    });
    $('#tipo-edit').select2({
        placeholder: "Seleccionar un tipo",
        allowClear: false
    });
});

$(".js-example-language").select2({
    language: "es"
});

function lettering(input) {
    let words = input.value.split(' ');
    if (words.length > 0) {
        words[0] = words[0].charAt(0).toUpperCase() + words[0].slice(1);
    }
    input.value = words.join(' ');
}

function cerrarFormulario() {
    // Ocultar el formulario
    document.getElementById('back').style.display = 'none';
    document.getElementById('arquitectura_new').style.display = 'none';

    // Resetear el formulario completamente
    document.getElementById('arquiNew').reset();

    // Resetear Select2 a valores iniciales
    $('#servicio').val(null).trigger('change');
    $('#tipo').val(null).trigger('change');
    actualizarUnidades(null, 'selector-container', 'recorrido-container');
}

// Función para cerrar el formulario y limpiar los campos
function cerrarFormularioEdit() {
    document.getElementById('back').style.display = 'none';
    document.getElementById('arquitectura_edit').style.display = 'none';
    document.getElementById('arquiEdit').reset();
    $('#servicio, #tipo').val(null).trigger('change');
    actualizarUnidades(null, 'selector-container-edit', 'recorrido-container-edit');
    document.getElementById('id_sitio').value = '';
    $("#est_activo").prop("checked", false);
    $("#est_inactivo").prop("checked", false);
}





function editarSitio(id) {
    $.ajax({
        url: 'controllers/get_sitio.php',
        method: 'POST',
        data: { id: id },
        dataType: 'json',
        success: function (data) {
            // console.log("Datos recibidos:", data);

            // Asignar valores a los campos de edición
            $("#id_sitio").val(id);

            $("#nombre-edit").val(data.nombre || '');

            if (!data.servicio) {
                $('#servicio-edit').select2({ placeholder: "Sin servicio seleccionado", allowClear: false });
            } else {
                $('#servicio-edit').val(data.servicio || '').trigger('change');
            }

            $('#tipo-edit').val(data.tipo_sitio_id || '').trigger('change');

            $("#observaciones-edit").val(data.observaciones || '');

            // Marcar el estado correcto
            if (data.estado === 'activo') {
                $("#est_activo").prop("checked", true);
            } else {
                $("#est_inactivo").prop("checked", true);
            }

            // Generar dinámicamente el selector de unidades basado en el ID
            actualizarUnidades(data.u_padre, 'selector-container-edit', 'recorrido-container-edit');

            // Mostrar el modal o sección de edición
            $("#back").css("display", "flex");
            $("#arquitectura_edit").css("display", "flex");
        },
        error: function (xhr, status, error) {
            console.error("Error al cargar los datos:", error);
        }
    });
}

function eliminarSitio(id) {
    if (confirm('¿Estás seguro de que deseas eliminar este sitio?')) {
        window.location.href = `controllers/eliminar.php?id=${id}`;
    }
}


// CARGA DE ARBOL DE NODOS
$(document).ready(function () {
    // Cargar los nodos raíz al iniciar
    function loadRootNodes() {
        $.ajax({
            url: 'controllers/getChildren.php',
            method: 'POST',
            data: { u_padre: null }, // Solicita nodos raíz
            success: function (response) {
                renderNodes(response, $('#tree-container'), 0); // Nivel raíz = 0
            },
            error: function () {
                alert('Error al cargar los nodos raíz.');
            },
        });
    }

    // Renderizar nodos en un contenedor con desplazamiento dinámico
    function renderNodes(nodes, container, level) {
        let html = '<ul>';
        nodes.forEach(node => {
            const marginLeft = level * 30; // Desplazamiento basado en el nivel
            const icon = node.has_children === 0
                ? '<i class="fa-solid fa-grip-lines"></i>' // Si no tiene hijos
                : '<i class="fa-solid fa-chevron-down tree-icon close"></i>'; // Si tiene hijos

            let deleteButton = ''; // Inicializamos el botón vacío

            // Mostrar el botón de eliminar solo si el nodo está inactivo
            if (node.estado === 'inactivo') {
                if (node.has_children === 0) {
                    // Si no tiene hijos, el botón se habilita completamente
                    deleteButton = `<a class="btn-red" onclick="eliminarSitio(${node.id})" title="Eliminar este sitio">
                                    <i class="fa-solid fa-trash"></i> Eliminar
                                </a>`;
                } else {
                    // Si tiene hijos, el botón se deshabilita con un mensaje
                    deleteButton = `<button disabled class="btn-red" title="Debe corregir todos los sitios que apuntan a este antes de eliminarlo">
                                    <i class="fa-solid fa-trash"></i>
                                </button>`;
                }
            }

            html += `
        <li>
            <div class="tree-node" data-id="${node.id}" data-level="${level}" style="margin-left: ${marginLeft}px; width: fit-content;">
                ${icon}
                ${node.estado === 'inactivo' ? '<span title="Sitio inactivo" style="color: red;"><i class="fa-solid fa-ban"></i></span>' : ''} 
                <span class="node-name">${node.nombre}</span>
                ${node.id !== 1 ? `<button class="btn-tematico" style="margin: 0 5px;" onclick="editarSitio(${node.id})">
                    <i class="fa fa-pen"></i>
                </button>` : ''}
                ${deleteButton}
            </div>
        </li>
        `;
        });
        html += '</ul>';
        container.append(html);
    }


    // Cargar nodos hijos al expandir
    function loadChildren(node, container, level) {
        const nodeId = node.data('id');
        $.ajax({
            url: 'controllers/getChildren.php',
            method: 'POST',
            data: { u_padre: nodeId },
            success: function (response) {
                renderNodes(response, container, level); // Incrementa el nivel para los hijos
            },
            error: function () {
                alert('Error al cargar los nodos hijos.');
            },
        });
    }

    // Manejar clic para expandir/colapsar nodos
    $(document).on('click', '.tree-node', function (e) {
        if ($(e.target).hasClass('btn-tematico') || $(e.target).closest('.btn-tematico').length > 0) {
            return; // No ejecutar si se clickea un botón con la clase btn-tematico o su contenido
        }
        const node = $(this);
        const container = node.closest('li');
        const level = parseInt(node.data('level')) + 1; // Determinar el nivel del nodo hijo
        const arrowIcon = node.find('.tree-icon');

        if (node.hasClass('expanded')) {
            node.removeClass('expanded');
            arrowIcon.removeClass('expanded').addClass('close');
            container.find('ul').remove();
        } else {
            node.addClass('expanded');
            arrowIcon.removeClass('close').addClass('expanded'); // Flecha hacia arriba
            if (container.find('ul').length === 0) {
                loadChildren(node, container, level);
            }
        }
    });

    // Cargar los nodos raíz al iniciar
    loadRootNodes();
});
