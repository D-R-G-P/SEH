document.addEventListener('DOMContentLoaded', () => {
    // Referencias a los elementos del DOM
    const instanciasBody = document.getElementById('dateTimeRows');
    const addInstanciaBtn = instanciasBody.querySelector('.btn-green');
    const form = document.getElementById('newCapForm'); // [Inferencia] Asumo que el formulario se llama 'newCapForm'.

    // Función para activar/desactivar el botón de eliminar
    function toggleEliminarBtns() {
        const instancias = instanciasBody.querySelectorAll('.instancia-item');
        instancias.forEach(item => {
            const btn = item.querySelector('.eliminar-instancia-btn');
            if (instancias.length > 1) {
                btn.disabled = false;
                btn.title = "Eliminar";
            } else {
                btn.disabled = true;
                btn.title = "Debe haber al menos una fecha";
            }
        });
    }

    // Función para agregar una nueva fila (instancia) a la tabla
    window.addInstancia = function () {
        const today = new Date().toISOString().split('T')[0];
        const newRow = document.createElement('tr');
        newRow.classList.add('instancia-item');
        newRow.innerHTML = `
            <td><input type="date" name="fecha[]" required style="width: 100%;" min="${today}"></td>
            <td><input type="time" name="hora[]" required style="width: 100%;"></td>
            <td><input type="text" name="lugar[]" required style="width: 100%;"></td>
            <td class="table-middle table-center">
                <button type="button" class="eliminar-instancia-btn btn-red" title="Eliminar">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </td>
        `;
        instanciasBody.insertBefore(newRow, addInstanciaBtn.closest('tr'));
        toggleEliminarBtns();
    }

    // Función para reiniciar el formulario y la tabla
    window.resetTableAndForm = function () {

        back.style.display = 'none';
        newCap.style.display = 'none';
        editCap.style.display = 'none';
        // Reinicia el formulario principal (título, descripción, etc.)
        if (form) {
            form.reset();
        }

        $('#rol_asociated').select2({
            placeholder: 'Seleccionar roles...'
        });

        // Reinicia el Select2 (asumiendo que está en un campo con id 'rol_asociated')
        const rolAsociadoSelect = $('#rol_asociated');
        if (rolAsociadoSelect.length) {
            rolAsociadoSelect.val(null).trigger('change');
        }

        // Elimina todas las filas de instancias
        const instanciaRows = instanciasBody.querySelectorAll('.instancia-item');
        instanciaRows.forEach(row => row.remove());

        // Crea una nueva fila inicial con la fecha de hoy
        const today = new Date().toISOString().split('T')[0];
        const initialRow = document.createElement('tr');
        initialRow.classList.add('instancia-item');
        initialRow.innerHTML = `
            <td><input type="date" name="fecha[]" required style="width: 100%;" min="${today}"></td>
            <td><input type="time" name="hora[]" required style="width: 100%;"></td>
            <td><input type="text" name="lugar[]" required style="width: 100%;"></td>
            <td class="table-middle table-center">
                <button type="button" class="eliminar-instancia-btn btn-red" title="Debe haber al menos una fecha" disabled>
                    <i class="fa-solid fa-trash"></i>
                </button>
            </td>
        `;

        // Inserta la nueva fila antes del botón de "Nuevo horario"
        instanciasBody.insertBefore(initialRow, addInstanciaBtn.closest('tr'));

        // Deshabilita el botón de eliminar de la nueva fila única
        toggleEliminarBtns();
    }

    // Manejador de clic para eliminar instancias
    instanciasBody.addEventListener('click', (event) => {
        const btnEliminar = event.target.closest('.eliminar-instancia-btn');
        if (btnEliminar && !btnEliminar.disabled) {
            const instanciaItem = btnEliminar.closest('tr');
            instanciaItem.remove();
            toggleEliminarBtns();
        }
    });

    // Esta función se encarga de procesar los datos cuando se envía el formulario.
    function handleSubmit(event) {
        // event.preventDefault();
        const instancias = [];
        const instanciaItems = instanciasBody.querySelectorAll('.instancia-item');
        instanciaItems.forEach(item => {
            const inputs = item.querySelectorAll('input');
            const instancia = {
                fecha: inputs[0].value,
                hora: inputs[1].value,
                lugar: inputs[2].value,
            };
            instancias.push(instancia);
        });

        if (instancias.some(inst => !inst.fecha || !inst.hora || !inst.lugar)) {
            console.error('Todos los campos de las instancias deben estar completos.');
            return;
        }

        console.log('Datos de las Instancias:', instancias);
    }

    // Código de inicialización: Agrega la primera fila con la fecha de hoy
    window.resetTableAndForm();
});

document.addEventListener('DOMContentLoaded', () => {
    // Referencia al cuerpo de la tabla donde se insertarán las filas
    const tableBody = document.getElementById('capacitacionesTableBody');
    // URL del endpoint PHP para obtener todos los datos
    const allDataUrl = 'http://localhost/SGH/public/layouts/modules/hsiPanel/capacitaciones_hsi/controllers/get_capacitaciones_hsi.php';
    // URL del endpoint PHP para obtener los datos de una sola capacitación
    const singleDataUrl = 'http://localhost/SGH/public/layouts/modules/hsiPanel/capacitaciones_hsi/controllers/get_capacitacion_hsi_by_id.php';
    // URL del endpoint PHP para obtener los inscriptos de una instancia
    const inscritosUrl = 'http://localhost/SGH/public/layouts/modules/hsiPanel/capacitaciones_hsi/controllers/get_inscritos.php';

    // Función asincrónica para obtener los datos y renderizar la tabla
    async function fetchAndRenderCapacitaciones() {
        try {
            const response = await fetch(allDataUrl);
            if (!response.ok) {
                throw new Error(`Error en la petición: ${response.statusText}`);
            }
            const capacitaciones = await response.json();
            tableBody.innerHTML = '';

            const capitalize = str => str.charAt(0).toUpperCase() + str.slice(1);
            const formatDate = dateStr => {
                const date = new Date(dateStr);
                const day = String(date.getDate()).padStart(2, '0');
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const year = String(date.getFullYear()).slice(-2);
                return `${day}/${month}/${year}`;
            }
            const formatTime = timeStr => {
                // timeStr expected format: "HH:mm"
                const [hours, minutes] = timeStr.split(':');
                return `${hours.padStart(2, '0')}:${minutes.padStart(2, '0')}`;
            }

            capacitaciones.forEach(capacitacion => {
                const row = document.createElement('tr');
                const fechasHTML = capacitacion.instancias.map(inst => formatDate(inst.fecha)).join('<br>');
                const horasHTML = capacitacion.instancias.map(inst => formatTime(inst.hora)).join('<br>');
                const lugaresHTML = capacitacion.instancias.map(inst => capitalize(inst.lugar)).join('<br>');
                const asistentesHTML = capacitacion.instancias.map(inst => inst.asistentes + ' inscriptos').join('<br>');

                row.innerHTML = `
                    <td class="table-middle">${capacitacion.id}</td>
                    <td class="table-middle">${capacitacion.titulo}</td>
                    <td class="table-middle">${capacitacion.descripcion}</td>
                    <td class="table-middle">${capacitacion.rol_asociado}</td>
                    <td class="table-middle" style="white-space: nowrap;">${fechasHTML}</td>
                    <td class="table-middle" style="white-space: nowrap;">${horasHTML}</td>
                    <td class="table-middle" style="white-space: nowrap;">${lugaresHTML}</td>
                    <td class="table-middle" style="white-space: nowrap;">${asistentesHTML}</td>
                    <td class="table-middle table-center">
                        <button class="btn-yellow" onclick="handleEdit(${capacitacion.id})">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </button>
                        <button class="btn-tematico" onclick="handleView(${capacitacion.id})">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </td>
                `;
                tableBody.appendChild(row);
            });
        } catch (error) {
            console.error('Error al cargar las capacitaciones:', error);
            tableBody.innerHTML = `<tr><td colspan="9" class="text-center text-red-500">Error al cargar los datos.</td></tr>`;
        }
    }

    async function handleEdit(id) {
        back.style.display = 'flex';
        editCap.style.display = 'flex';
        // Referencias a los elementos del DOM (IDs corregidos)
        const editInput = document.getElementById('edit_id');
        const titleInput = document.getElementById('edit_title');
        const descriptionTextarea = document.getElementById('edit_description');
        // Usamos jQuery para Select2, asumiendo que está disponible
        const rolSelect = $('#edit_rol_asociated');
        $('#edit_rol_asociated').select2({
            placeholder: 'Seleccionar roles...'
        });
        const dateTimeRowsBody = document.getElementById('edit_dateTimeRows');

        try {
            // Realizar la llamada a la API para obtener los datos de la capacitación
            const response = await fetch(`${singleDataUrl}?id=${id}`);
            if (!response.ok) {
                // Manejar errores si la respuesta de la red no es exitosa
                throw new Error(`Error al obtener los detalles: ${response.statusText}`);
            }
            const capacitacion = await response.json();

            if (capacitacion.error) {
                // Manejar errores si la API devuelve un mensaje de error
                console.error('Error al obtener los datos:', capacitacion.error);
                // Puedes mostrar un mensaje al usuario aquí si lo deseas
                return;
            }

            // --- LÓGICA PARA AGREGAR DATOS AL FORMULARIO ---

            // 1. Llenar los campos de texto
            edit_id.value = capacitacion.id;
            titleInput.value = capacitacion.titulo;
            descriptionTextarea.value = capacitacion.descripcion;

            // 2. Llenar el campo de selección de roles (Select2)
            // Convertir el string de roles a un array para usarlo con Select2
            // La cadena de texto con los IDs (ejemplo: "1,2,3")
            const rolAsociadoIdsString = capacitacion.rol_asociado_ids;

            // Si la cadena de roles existe y no está vacía, la dividimos para obtener un array de IDs.
            // De lo contrario, usamos un array vacío.
            const roles = rolAsociadoIdsString ? rolAsociadoIdsString.split(',') : [];

            // Ahora Select2 recibirá un array de IDs correcto y los seleccionará
            rolSelect.val(roles).trigger('change');

            // 3. Llenar la tabla de instancias dinámicas

            // Limpiar las filas existentes de la tabla, excepto la última que es el botón de "Agregar"
            const lastRow = dateTimeRowsBody.lastElementChild;
            while (dateTimeRowsBody.firstChild !== lastRow) {
                dateTimeRowsBody.removeChild(dateTimeRowsBody.firstChild);
            }

            // Iterar sobre las instancias y crear una fila de tabla para cada una
            if (capacitacion.instancias && Array.isArray(capacitacion.instancias)) {
                capacitacion.instancias.forEach((instancia) => {
                    const newRow = document.createElement('tr');
                    newRow.classList.add('instancia-item');
                    // Usar un template string para crear el HTML de la fila
                    newRow.innerHTML = `
                    <td><input type="date" name="fecha_capacitacion[]" value="${instancia.fecha}" required></td>
                    <td><input type="time" name="hora_capacitacion[]" value="${instancia.hora}" required></td>
                    <td><input type="text" name="lugar_capacitacion[]" value="${instancia.lugar}" required></td>
                    <td class="table-middle table-center" style="display: flex; flex-direction: row; flex-wrap: nowrap;"><button type="button" class="btn-red eliminar-instancia-btn" onclick="removeInstancia(this);"><i class="fa-solid fa-trash"></i></button>
                    
                    <select name="estado[]" class="select2 stateSelect" required style="width: 100%;">
                            <option ${instancia.estado == 'programada' ? 'selected' : ''} value="programada">Programada</option>
                            <option ${instancia.estado == 'completada' ? 'selected' : ''} value="completada">Completada</option>
                            <option ${instancia.estado == 'cancelada' ? 'selected' : ''} value="cancelada">Cancelada</option>
                            <option ${instancia.estado == 'en_curso' ? 'selected' : ''} value="en_curso">En curso</option>
                            <option ${instancia.estado == 'reprogramada' ? 'selected' : ''} value="programada">Reprogramada</option>
                            <option ${instancia.estado == 'cerrad' ? 'selected' : ''} value="cerrada">Cerrada</option>
                        </select>
                        </td>
                `;
                    // Insertar la nueva fila antes del botón de "Agregar"
                    dateTimeRowsBody.insertBefore(newRow, lastRow);
                    $('.stateSelect').select2();
                });
            }

        } catch (error) {
            console.error('Error en handleEdit:', error);
        }
    }



    // FUNCIÓN handleView COMPLETA PARA MOSTRAR UN MODAL
    async function handleView(id) {
        const modal = document.getElementById('viewModal');
        const modalContent = document.getElementById('viewModalContent');
        const modalTitle = document.getElementById('viewModalTitle');

        modalTitle.textContent = 'Cargando...';
        modalContent.innerHTML = 'Por favor, espera...';
        modal.style.display = 'flex';

        try {
            const response = await fetch(`${singleDataUrl}?id=${id}`);
            if (!response.ok) {
                throw new Error(`Error al obtener los detalles: ${response.statusText}`);
            }
            const capacitacion = await response.json();

            if (capacitacion.error) {
                modalTitle.textContent = 'Error';
                modalContent.innerHTML = `<p>${capacitacion.error}</p>`;
                return;
            }

            modalTitle.textContent = `Detalles de ${capacitacion.titulo}`;
            let instanciasHTML = '<h3>Instancias de Capacitación</h3>';
            const capitalize = str => str.charAt(0).toUpperCase() + str.slice(1);
            const formatDate = dateStr => {
                const options = { year: 'numeric', month: 'long', day: 'numeric' };
                return new Date(dateStr).toLocaleDateString('es-ES', options);
            }
            const formatTime = timeStr => {
                // timeStr expected format: "HH:mm"
                const [hours, minutes] = timeStr.split(':');
                const h = parseInt(hours, 10);
                const ampm = h < 12 ? 'AM' : 'PM';
                // Formato 24h con am/pm
                return `${hours.padStart(2, '0')}:${minutes.padStart(2, '0')} ${ampm}`;
            }
            capacitacion.instancias.forEach(instancia => {
                instanciasHTML += `
                    <div class="instancia-card">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <p><strong>Fecha:</strong> ${formatDate(instancia.fecha)}</p>
                                <p><strong>Hora:</strong> ${formatTime(instancia.hora)}</p>
                                <p><strong>Lugar:</strong> ${capitalize(instancia.lugar)}</p>
                            </div>
                            <div>
                                <span class="pill pill-${instancia.estado}">${capitalize(instancia.estado)}</span>
                            </div>
                        </div>
                        <p>
                            <strong>Asistentes:</strong> ${instancia.asistentes} inscriptos
                            <button class="btn-tematico btn-small" onclick="toggleInscritos(${instancia.id})">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </p>
                        <ul id="inscritos-${instancia.id}" class="lista-inscritos"></ul>
                    </div>
                `;
            });

            modalContent.innerHTML = `
                <p><strong>ID:</strong> ${capacitacion.id}</p>
                <p><strong>Descripción:</strong> ${capacitacion.descripcion}</p>
                <p><strong>Roles Asociados:</strong> ${capacitacion.rol_asociado}</p>
                ${instanciasHTML}
            `;
        } catch (error) {
            console.error('Error en handleView:', error);
            modalTitle.textContent = 'Error';
            modalContent.innerHTML = `<p>No se pudieron cargar los detalles: ${error.message}</p>`;
        }
    }

    // NUEVA FUNCIÓN: Maneja el despliegue de la lista de inscriptos
    window.toggleInscritos = async function (instanciaId) {
        const listaInscritos = document.getElementById(`inscritos-${instanciaId}`);
        if (listaInscritos.style.display === 'block') {
            listaInscritos.style.display = 'none';
        } else {
            listaInscritos.innerHTML = '<li>Cargando inscriptos...</li>';
            listaInscritos.style.display = 'block';
            try {
                const response = await fetch(`${inscritosUrl}?instancia_id=${instanciaId}`);
                if (!response.ok) {
                    throw new Error(`Error al obtener inscriptos: ${response.statusText}`);
                }
                const inscriptos = await response.json();

                if (inscriptos.error) {
                    listaInscritos.innerHTML = `<li>Error: ${inscriptos.error}</li>`;
                    return;
                }

                if (inscriptos.length === 0) {
                    listaInscritos.innerHTML = '<li>No hay inscriptos.</li>';
                } else {
                    let html = '';
                    inscriptos.forEach(inscrito => {
                        const capitalize = str => str.charAt(0).toUpperCase() + str.slice(1);

                        html += `<div style="display: flex; flex-direction: row; flex-wrap: nowrap; align-items: center; justify-content: space-between;">
                            <li title="DNI: ${inscrito.dni}">${capitalize(inscrito.apellido)} ${capitalize(inscrito.nombre)}, ${capitalize(inscrito.servicio)}</li>
                            <span class="pill pill-${inscrito.estado}">${capitalize(inscrito.estado)}</span>
                        </div>`;
                    });
                    listaInscritos.innerHTML = html;
                }

            } catch (error) {
                console.error('Error al cargar la lista de inscriptos:', error);
                listaInscritos.innerHTML = `<li>Error al cargar la lista: ${error.message}</li>`;
            }
        }
    }

    // Llama a la función para cargar los datos cuando la página se cargue
    fetchAndRenderCapacitaciones();

    // Puedes agregar el HTML para el modal al final de tu cuerpo <body>
    const modalHTML = `
        <div id="viewModal" class="modal-hsi">
            <div class="modal-content">
                <span class="close-btn" onclick="document.getElementById('viewModal').style.display='none'">&times;</span>
                <h2 id="viewModalTitle"></h2>
                <div id="viewModalContent"></div>
            </div>
        </div>
    `;
    document.body.insertAdjacentHTML('beforeend', modalHTML);

    // Adjunta las funciones al objeto global 'window' para que estén disponibles en el onclick
    window.handleEdit = handleEdit;
    window.handleView = handleView;
});
