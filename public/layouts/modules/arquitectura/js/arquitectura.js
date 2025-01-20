$(document).ready(function () {
    // Función para cargar los nodos raíz al iniciar
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
            const isLastChild = !node.u_hijo; // Verificar si es último hijo
            const icon = isLastChild 
                ? '<i class="fa-solid fa-grip-lines"></i>' 
                : '<i class="fa-solid fa-chevron-down tree-icon close"></i>';
            
            html += `
                <li>
                    <div class="tree-node" data-id="${node.id}" data-level="${level}" style="margin-left: ${marginLeft}px;">
                        ${icon}
                        <span class="node-name">${node.nombre}</span>
                        <button class="btn-tematico" style="margin: 0 5px;" onclick="editNode(${node.id})">
                            <i class="fa fa-pen"></i>
                        </button>
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
$(document).on('click', '.tree-node', function () {
    const node = $(this);
    const container = node.closest('li');
    const level = parseInt(node.data('level')) + 1; // Determinar el nivel del nodo hijo

    // Seleccionamos el icono de la flecha (no los iconos de edición)
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



    // Función para editar nodo (puedes personalizarla)
    window.editNode = function (id) {
        alert('Editar nodo con ID: ' + id);
    };

    // Cargar los nodos raíz al iniciar
    loadRootNodes();
});
