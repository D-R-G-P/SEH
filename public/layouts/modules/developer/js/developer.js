$(document).ready(function () {
    $('#modulo_rol_select').select2();
    $('#modulo_subrol_select').select2();
    $('#rol_subrol_select').select2();
});

$(".js-example-language").select2({
    language: "es"
});

// Obtener referencias a los selectores
const moduloSelect = document.getElementById('modulo_subrol_select');
const rolSelect = document.getElementById('rol_subrol_select');

// Asignar directamente la función al evento onchange
moduloSelect.onchange = async function () {
    const moduloId = moduloSelect.value;

    // Si no se selecciona un módulo, desactivar el selector de roles y vaciarlo
    if (!moduloId) {
        rolSelect.innerHTML = '<option value="" disabled selected>Seleccionar una opción</option>';
        rolSelect.disabled = true;
        return;
    }

    // Solicitud AJAX para obtener los roles asociados al módulo
    try {
        const response = await fetch('controllers/get_roles.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `modulo_id=${encodeURIComponent(moduloId)}`
        });

        // Validar la respuesta del servidor
        if (!response.ok) {
            throw new Error(`Error HTTP: ${response.status}`);
        }

        const roles = await response.json();

        // Actualizar el selector de roles
        rolSelect.innerHTML = '<option value="" disabled selected>Seleccionar una opción</option>';
        if (roles.length > 0) {
            roles.forEach(rol => {
                const option = document.createElement('option');
                option.value = rol.id;
                option.textContent = rol.nombre;
                rolSelect.appendChild(option);
            });
            rolSelect.disabled = false; // Habilitar si hay roles
        } else {
            // Si no hay roles asociados, vaciar y desactivar
            rolSelect.disabled = true;
        }
    } catch (error) {
        console.error('Error al cargar los roles:', error);
        rolSelect.innerHTML = '<option value="" disabled selected>Error al cargar roles</option>';
        rolSelect.disabled = true;
    }
};

const input = document.querySelector('.singleWord');

input.addEventListener('input', () => {
    // Remueve cualquier espacio ingresado
    input.value = input.value.replace(/\s+/g, '');
});


async function vistaModulo(id) {
    const vistaM = document.getElementById('vistaModulo');
    const moduloForm = document.getElementById('moduloNew');
    const moduloIdInput = document.getElementById('modulo_id');
    const moduloInput = document.getElementById('modulo');
    const descripcionInput = document.getElementById('descripcion');
    const estadoDiv = document.getElementById('modulo_estado_div');
    const estadoActivoRadio = document.getElementById('modulo_estado_activo');
    const estadoInactivoRadio = document.getElementById('modulo_estado_inactivo');

    if (id) {
        // Si se pasa un ID, obtener los datos del servidor
        try {
            const response = await fetch('controllers/loadModulo.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id=${encodeURIComponent(id)}`
            });

            // Validar la respuesta del servidor
            if (!response.ok) {
                throw new Error(`Error HTTP: ${response.status}`);
            }

            const modulo = await response.json(); // Suponemos que el servidor devuelve un objeto JSON con los datos del módulo

            // Actualizar los inputs del formulario con los datos recibidos
            back.style.display = 'flex';
            vistaM.style.display = 'flex';

            // Llenar el formulario con los datos del módulo
            moduloIdInput.value = modulo.id || ''; // ID oculto
            moduloInput.value = modulo.modulo || ''; // Nombre del módulo
            descripcionInput.value = modulo.descripcion || ''; // Descripción

            // Mostrar y ajustar los valores del estado si están disponibles
            if (modulo.estado) {
                estadoDiv.style.display = 'block'; // Mostrar el div de estado
                if (modulo.estado === 'Activo') {
                    estadoActivoRadio.checked = true;
                } else if (modulo.estado === 'Inactivo') {
                    estadoInactivoRadio.checked = true;
                }
            } else {
                estadoDiv.style.display = 'none'; // Ocultar el div si no hay estado
            }
        } catch (error) {
            console.error('Error al cargar módulo:', error);
            alert('Error al cargar los datos del módulo. Intente nuevamente.');
        }
    } else {
        // Si no se pasa un ID, resetear el formulario
        back.style.display = 'flex';
        vistaM.style.display = 'flex';
        moduloForm.reset(); // Limpiar todos los campos del formulario
        estadoDiv.style.display = 'none'; // Ocultar el div de estado
    }
}

async function vistaModulo(id) {
    const vistaM = document.getElementById('vistaModulo');
    const moduloForm = document.getElementById('moduloNew');
    const moduloIdInput = document.getElementById('modulo_id');
    const moduloInput = document.getElementById('modulo');
    const descripcionInput = document.getElementById('descripcion');
    const estadoDiv = document.getElementById('modulo_estado_div');
    const estadoActivoRadio = document.getElementById('modulo_estado_activo');
    const estadoInactivoRadio = document.getElementById('modulo_estado_inactivo');

    if (id) {
        // Si se pasa un ID, obtener los datos del servidor
        try {
            const response = await fetch('controllers/loadModulo.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id=${encodeURIComponent(id)}`
            });

            // Validar la respuesta del servidor
            if (!response.ok) {
                throw new Error(`Error HTTP: ${response.status}`);
            }

            const modulo = await response.json(); // Suponemos que el servidor devuelve un objeto JSON con los datos del módulo

            // Actualizar los inputs del formulario con los datos recibidos
            back.style.display = 'flex';
            vistaM.style.display = 'flex';

            // Llenar el formulario con los datos del módulo
            moduloIdInput.value = modulo.id || ''; // ID oculto
            moduloInput.value = modulo.modulo || ''; // Nombre del módulo
            descripcionInput.value = modulo.descripcion || ''; // Descripción

            // Mostrar y ajustar los valores del estado si están disponibles
            if (modulo.estado) {
                estadoDiv.style.display = 'block'; // Mostrar el div de estado
                if (modulo.estado === 'Activo') {
                    estadoActivoRadio.checked = true;
                } else if (modulo.estado === 'Inactivo') {
                    estadoInactivoRadio.checked = true;
                }
            } else {
                estadoDiv.style.display = 'none'; // Ocultar el div si no hay estado
            }
        } catch (error) {
            console.error('Error al cargar módulo:', error);
            alert('Error al cargar los datos del módulo. Intente nuevamente.');
        }
    } else {
        // Si no se pasa un ID, resetear el formulario
        back.style.display = 'flex';
        vistaM.style.display = 'flex';
        moduloForm.reset(); // Limpiar todos los campos del formulario
        estadoDiv.style.display = 'none'; // Ocultar el div de estado
    }
}


function vistaRol(id) {
    const vistaR = document.getElementById('vistaRol');
    if (id) {
        async function get_vistaRol(id) {
            try {
                const response = await fetch('controllers/loadRol.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `id=${encodeURIComponent(id)}`
                });

                // Validar la respuesta del servidor
                if (!response.ok) {
                    throw new Error(`Error HTTP: ${response.status}`);
                }

                const rol = await response.json();

                // Actualizar los inputs del formulario
                document.getElementById('rol_id').value = rol.id || '';
                document.getElementById('rol_rol').value = rol.role || '';
                document.getElementById('rol_name').value = rol.nombre || '';
                $('#modulo_rol_select').val(rol.modulo).trigger('change');
                document.getElementById('rol_descripcion').value = rol.descripcion || '';

                if (rol.estado === 'Activo') {
                    document.getElementById('rol_estado_activo').checked = true;
                } else if (rol.estado === 'Inactivo') {
                    document.getElementById('rol_estado_inactivo').checked = true;
                }


                back.style.display = 'flex';
                vistaR.style.display = 'flex';
                rol_estado_div.style.display = 'flex';
            } catch (error) {
                console.error('Error al cargar rol:', error);
            }
        }

        get_vistaRol(id);
    } else {
        // Si no hay ID, limpiar y mostrar formulario vacío
        back.style.display = 'flex';
        vistaR.style.display = 'flex';
        rol_estado_div.style.display = 'none';
        newRol.reset();
    }
}


function vistaSubrol(id) {
    const vistaS = document.getElementById('vistaSubrol');
    const back = document.getElementById('back');
    const subrol_estado_div = document.getElementById('subrol_estado_div');
    const rolSelect = document.getElementById('rol_subrol_select');
    const moduloSelect = document.getElementById('modulo_subrol_select'); // Selector de módulos

    if (!id) {
        // Mostrar vista inicial sin datos
        back.style.display = 'flex';
        vistaS.style.display = 'flex';
        subrol_estado_div.style.display = 'none';
        resetForm();
        return;
    }

    async function get_vistaSubrol(id) {
        try {
            const response = await fetch('controllers/loadSubrol.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id=${encodeURIComponent(id)}`
            });

            if (!response.ok) {
                throw new Error(`Error HTTP: ${response.status}`);
            }

            const subrol = await response.json();

            // Actualizar los inputs del formulario con los datos recibidos
            setValue('subrol_id', subrol.id);
            setValue('subrol', subrol.subrol);
            setValue('subrol_name', subrol.nombre);
            setValue('subDesc', subrol.descripcion);

            if (subrol.estado === 'Activo') {
                setChecked('subrol_estado_activo', true);
            } else if (subrol.estado === 'Inactivo') {
                setChecked('subrol_estado_inactivo', true);
            }

            // Actualizar el módulo y cargar los roles asociados
            setModulo(subrol.modulo); // Nuevo: Seleccionar el módulo
            await loadSubrolSelect(subrol.modulo, subrol.rol_id);

            // Mostrar las vistas necesarias
            back.style.display = 'flex';
            vistaS.style.display = 'flex';
            subrol_estado_div.style.display = 'flex';
        } catch (error) {
            console.error('Error al cargar los datos del subrol:', error);
        }
    }

    function setModulo(moduloId) {
        if (moduloSelect) {
            moduloSelect.value = moduloId || '';
            $('#modulo_subrol_select').trigger('change'); // Trigger para Select2 si se usa
        } else {
            console.warn('Selector de módulo no encontrado.');
        }
    }

    async function loadSubrolSelect(moduloId, subrol_id) {
        try {
            if (!moduloId) {
                rolSelect.innerHTML = '<option value="" disabled selected>Seleccionar una opción</option>';
                rolSelect.disabled = true;
                return;
            }

            const response = await fetch('controllers/get_roles.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `modulo_id=${encodeURIComponent(moduloId)}`
            });

            if (!response.ok) {
                throw new Error(`Error HTTP: ${response.status}`);
            }

            const roles = await response.json();

            rolSelect.innerHTML = '<option value="" disabled selected>Seleccionar una opción</option>';
            roles.forEach(rol => {
                const option = document.createElement('option');
                option.value = rol.id;
                option.textContent = rol.nombre;
                rolSelect.appendChild(option);
            });

            rolSelect.disabled = roles.length === 0;

            // Seleccionar el rol si está disponible
            if (subrol_id) {
                $('#rol_subrol_select').val(subrol_id).trigger('change');
            }
        } catch (error) {
            console.error('Error al cargar los roles del módulo:', error);
            rolSelect.innerHTML = '<option value="" disabled selected>Error al cargar roles</option>';
            rolSelect.disabled = true;
        }
    }

    function resetForm() {
        // Restablece los valores del formulario
        const fields = ['subrol_id', 'subrol', 'subrol_name', 'subDesc'];
        fields.forEach(field => setValue(field, ''));
        setChecked('subrol_estado_activo', false);
        setChecked('subrol_estado_inactivo', false);
        $('#modulo_subrol_select').val(null).trigger('change');
        $('#rol_subrol_select').val(null).trigger('change');
    }

    function setValue(id, value) {
        const element = document.getElementById(id);
        if (element) {
            element.value = value || '';
        } else {
            console.warn(`Elemento con ID "${id}" no encontrado.`);
        }
    }

    function setChecked(id, checked) {
        const element = document.getElementById(id);
        if (element) {
            element.checked = checked;
        } else {
            console.warn(`Elemento con ID "${id}" no encontrado.`);
        }
    }

    // Ejecutar la carga del subrol
    get_vistaSubrol(id);
}
