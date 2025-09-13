function formatNumber(input) {
    // Eliminar caracteres que no son números
    const inputValue = input.value.replace(/\D/g, '');

    // Formatear el número con puntos si no está vacío, de lo contrario, dejar en blanco
    const formattedNumber = inputValue !== '' ? Number(inputValue).toLocaleString('es-AR') : '';

    // Actualizar el valor del campo de entrada
    input.value = formattedNumber;
}

$(document).ready(function () {
    $('#selectServicio').select2();
    $('#selectEspecialidad').select2();
    $('#selectCargo').select2();
    $('#editselectServicio').select2();
    $('#editselectespecialidad').select2();
    $('#editselectcargo').select2();
    $('#paseSelectServicio').select2();
    $('#licenciaTipo').select2();
    $('#finContratoMotivo').select2();
    $('#selectServicioFilter').select2();
});

$(".js-example-language").select2({
    language: "es"
});

function selectChange() {
    var servicioValue = selectServicio.value; // Obtén el valor del servicio seleccionado

    // Realiza una consulta AJAX al servidor para obtener las especialidades correspondientes al servicio seleccionado
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'controllers/getEspecialidades.php?servicioId=' + servicioValue, true);
    xhr.onload = function () {
        if (xhr.status >= 200 && xhr.status < 400) {
            // Actualiza el select de especialidades con los nuevos valores recibidos del servidor
            document.getElementById('selectEspecialidad').innerHTML = xhr.responseText;
        }
    };
    xhr.send();
};

document.addEventListener('DOMContentLoaded', function () {
    const inputs = document.querySelectorAll('input[type="text"]');

    inputs.forEach(input => {
        input.addEventListener('input', function () {
            // Obtén el valor del input y convierte la primera letra de cada palabra a mayúscula
            let words = this.value.split(' ');
            for (let i = 0; i < words.length; i++) {
                words[i] = words[i].charAt(0).toUpperCase() + words[i].slice(1);
            }
            // Une las palabras nuevamente y establece el valor del input
            this.value = words.join(' ');
        });
    });
});


function editselectChange(especialidad) {
    var editservicioValue = $('#editselectServicio').val(); // Obtén el valor del servicio seleccionado

    // Realiza una consulta AJAX al servidor para obtener las especialidades correspondientes al servicio seleccionado
    $.ajax({
        url: 'controllers/getEspecialidades.php',
        type: 'GET',
        data: { servicioId: editservicioValue },
        success: function (response) {
            // Actualiza el select de especialidades con los nuevos valores recibidos del servidor
            $('#editselectespecialidad').html(response);

            // Establecemos el valor seleccionado en el select de especialidades
            $('#editselectespecialidad').val(especialidad).trigger('change');
        },
        error: function (xhr, status, error) {
            console.error(xhr.responseText);
        }
    });
}

// Agregamos un listener de evento para cerrar el menú si se hace clic fuera de él
document.addEventListener('click', function (event) {
    var menus = document.querySelectorAll('.menu');
    for (var i = 0; i < menus.length; i++) {
        if (!menus[i].contains(event.target)) {
            menus[i].classList.remove('activo');
            menus[i].classList.remove('menu');
        }
    }
});

function menuPersona(id) {
    var menu = document.getElementById('menu-' + id);

    // Si el menú ya está activo, lo cerramos
    if (menu.classList.contains('activo')) {
        menu.classList.remove('activo');
    } else {
        // Si no está activo, lo abrimos
        var buttonsDivs = document.getElementsByClassName('buttons-div');
        for (var i = 0; i < buttonsDivs.length; i++) {
            buttonsDivs[i].classList.remove('activo');
        }
        menu.classList.add('activo');

        // Añadimos la clase 'menu' con un pequeño retardo
        setTimeout(function () {
            menu.classList.add('menu');
        }, 5);
    }
}

function jefeCheck(dni) {
    $.ajax({
        url: 'controllers/verificar_jefe_servicio.php?dni=' + dni,
        method: 'POST',
        data: { dni: dni },
        success: function (response) {
            if (response === 'true') {
                // Si el DNI está asociado a un jefe de servicio, agregar una opción adicional al select
                $('#jefeCheckeado').prop('disabled', false);
                $('#editselectcargo').val("Jefe de servicio").trigger('change');
            }
        }
    });
};

function editselectChange(especialidad) {
    var editservicioValue = $('#editselectServicio').val(); // Obtén el valor del servicio seleccionado

    // Realiza una consulta AJAX al servidor para obtener las especialidades correspondientes al servicio seleccionado
    $.ajax({
        url: 'controllers/getEspecialidades.php',
        type: 'GET',
        data: { servicioId: editservicioValue },
        success: function (response) {
            // Actualiza el select de especialidades con los nuevos valores recibidos del servidor
            $('#editselectespecialidad').html(response);

            // Establecemos el valor seleccionado en el select de especialidades
            $('#editselectespecialidad').val(especialidad).trigger('change');
        },
        error: function (xhr, status, error) {
            console.error(xhr.responseText);
        }
    });
}

// Agregamos un listener de evento para cerrar el menú si se hace clic fuera de él
document.addEventListener('click', function (event) {
    var menuLic = document.querySelectorAll('.menuLic');
    for (var i = 0; i < menuLic.length; i++) {
        if (!menuLic[i].contains(event.target)) {
            menuLic[i].classList.remove('activoLic');
            menuLic[i].classList.remove('menuLic');
        }
    }
});

function avisoLicencia(id) {
    var menuLic = document.getElementById('aviso-' + id);

    // Si el menú ya está activo, lo cerramos
    if (menuLic.classList.contains('activoLic')) {
        menuLic.classList.remove('activoLic');
    } else {
        // Si no está activo, lo abrimos
        var avisoWar = document.getElementsByClassName('avisoWar');
        for (var i = 0; i < avisoWar.length; i++) {
            avisoWar[i].classList.remove('activoLic');
        }
        menuLic.classList.add('activoLic');

        // Añadimos la clase 'menu' con un pequeño retardo
        setTimeout(function () {
            menuLic.classList.add('menuLic');
        }, 5);
    }
}

function updateSistem(id, sistema, estado, pagina) {
    // Construir la URL con los parámetros
    var url = 'controllers/actualizar_sistema.php?id=' + encodeURIComponent(id) + '&sistema=' + encodeURIComponent(sistema) + '&estado=' + encodeURIComponent(estado) + '&pagina=' + encodeURIComponent(pagina);

    // Redireccionar a la URL
    window.location.href = url;
}

function updatePassword(id, dni, pagina) {
    // Construir la URL con los parámetros
    var url = 'controllers/actualizar_contrasena.php?id=' + encodeURIComponent(id) + '&dni=' + encodeURIComponent(dni) + '&pagina=' + encodeURIComponent(pagina);

    // Redireccionar a la URL
    window.location.href = url;
}

function setDatos(id, apellido, nombre, dni, servicio, cargo, especialidad, mn, mp, rol) {
    $('#back').css('display', 'flex');
    $('#editPersonal').css('display', 'flex');

    $('#editid').val(id);
    $('#editapellido').val(apellido);
    $('#editnombre').val(nombre);
    $('#editdni').val(dni);
    $('#editselectServicio').val(servicio).trigger('change');
    $('#editmn').val(mn);
    $('#editmp').val(mp);
    $('#editselectcargo').val(cargo).trigger('change');

    // Llama a la función editselectChange para actualizar el select de especialidades
    editselectChange(especialidad);
    jefeCheck(dni);
}

function setDatosPase(id, apellido, nombre, dni) {
    $('#back').css('display', 'flex');
    $('#newPase').css('display', 'flex');

    $('#paseId').val(id);
    $('#paseApellido').val(apellido);
    $('#paseNombre').val(nombre);
    $('#paseDni').val(dni);
}

function setLicencia(apellido, nombre, dni) {

    $('#back').css('display', 'flex');
    $('#newLicencia').css('display', 'flex');

    $('#licenciaApellido').val(apellido);
    $('#licenciaNombre').val(nombre);
    $('#licenciaDni').val(dni);
    $('#licenciaDniHidden').val(dni);
}

function setDatosFinContrato(apellido, nombre, dni) {
    $('#back').css('display', 'flex');
    $('#newFinContrato').css('display', 'flex');

    $('#finContratoApellido').val(apellido);
    $('#finContratoNombre').val(nombre);
    $('#finContratoDni').val(dni);
    $('#finContratoDniHidden').val(dni);
}

function setDatosJubilar(apellido, nombre, dni) {
    $('#back').css('display', 'flex');
    $('#newJubilacion').css('display', 'flex');

    $('#jubilarApellido').val(apellido);
    $('#jubilarNombre').val(nombre);
    $('#jubilarDni').val(dni);
    $('#jubilarDniHidden').val(dni);
}

document.addEventListener('DOMContentLoaded', () => {
    // Referencias a elementos del DOM
    const dniInput = document.getElementById('dni');
    const apellidoInput = document.getElementById('apellido');
    const nombreInput = document.getElementById('nombre');
    const button = document.getElementById('btn-dni');
    
    // Asume la existencia de un contenedor de carga y uno de error en tu HTML
    const loadingContainer = document.getElementById('loadingContainer');
    const errorContainer = document.getElementById('errorContainer');
    const errorMessage = document.getElementById('errorMessage');

    // Referencias al nuevo modal de resultados
    const resultsModal = document.getElementById('resultsModal');
    const resultsList = document.getElementById('resultsList');
    const closeButton = document.getElementById('closeModal');

    // URLs de tus scripts PHP
    const nosisProxyUrl = 'controllers/consultar_nosis.php';
    const geminiProxyUrl = 'controllers/separar_nombre.php';

    // Función para manejar el clic en el botón
    button.addEventListener('click', async () => {
        const dniValue = dniInput.value.trim();

        // Limpiar campos y mensajes anteriores
        apellidoInput.value = '';
        nombreInput.value = '';
        if (errorContainer) errorContainer.style.display = 'none';
        
        if (dniValue) {
            // Mostrar spinner de carga
            if (loadingContainer) loadingContainer.style.display = 'flex';
            await fetchData(dniValue);
        } else {
            displayError('Por favor, ingresa un número de DNI.');
        }
    });

    // Función para hacer la llamada a tu script PHP
    async function fetchData(dni) {
        try {
            const payload = {
                dni: dni
            };

            // Primera llamada: a la API de Nosis a través del proxy
            const response = await fetch(nosisProxyUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(payload)
            });

            // Manejo de errores de la API
            if (!response.ok) {
                if (response.status === 429) {
                    throw new Error('Has superado el límite de uso gratuito de la API. Por favor, inténtalo de nuevo más tarde.');
                }
                throw new Error(`Error de red: ${response.status}`);
            }

            const data = await response.json();

            if (data.EntidadesEncontradas && data.EntidadesEncontradas.length > 1) {
                // Hay más de un resultado, mostrar el modal para selección del usuario
                showResultsModal(data.EntidadesEncontradas);
            } else if (data.EntidadesEncontradas && data.EntidadesEncontradas.length === 1) {
                // Solo hay un resultado, procesar directamente con la segunda llamada
                const entidad = data.EntidadesEncontradas[0];
                const { nombre, apellido } = await fetchNameFromGemini(entidad.RazonSocial);
                fillFormFields(nombre, apellido);
            } else {
                throw new Error('No se encontraron resultados para el DNI.');
            }
        } catch (error) {
            console.error('Error al obtener la información:', error);
            displayError(error.message || 'Hubo un error al procesar la solicitud.');
        } finally {
            // Ocultar spinner de carga
            if (loadingContainer) loadingContainer.style.display = 'none';
        }
    }

    // Muestra el modal con los resultados
    function showResultsModal(results) {
        resultsList.innerHTML = '';
        results.forEach(entidad => {
            const listItem = document.createElement('li');
            listItem.classList.add('cursor-pointer', 'p-2', 'hover:bg-gray-100', 'border-b', 'border-gray-200', 'last:border-b-0');
            listItem.textContent = entidad.RazonSocial;
            listItem.addEventListener('click', async () => {
                const { nombre, apellido } = await fetchNameFromGemini(entidad.RazonSocial);
                fillFormFields(nombre, apellido);
                hideResultsModal();
            });
            resultsList.appendChild(listItem);
        });
        resultsModal.style.display = 'flex';
    }
    
    // Oculta el modal de resultados
    function hideResultsModal() {
        resultsModal.style.display = 'none';
    }

    // Rellena los campos del formulario
    function fillFormFields(nombre, apellido) {
        apellidoInput.value = apellido;
        nombreInput.value = nombre;
    }

    // Nueva función para llamar al proxy de Gemini en el servidor
    async function fetchNameFromGemini(razonSocial) {
        try {
            const response = await fetch(geminiProxyUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ razonSocial: razonSocial })
            });
            
            if (!response.ok) throw new Error('Error al llamar a Gemini API a través del servidor.');
            
            const data = await response.json();
            return { nombre: data.nombre, apellido: data.apellido };
            
        } catch (error) {
            console.error("Error al separar nombre y apellido con IA:", error);
            return { nombre: "N/A", apellido: "N/A" };
        }
    }

    // Función para mostrar errores en la UI
    function displayError(message) {
        if (errorContainer && errorMessage) {
            errorMessage.textContent = message;
            errorContainer.style.display = 'block';
        }
    }

    // Event listener para cerrar el modal con el botón
    if (closeButton) {
        closeButton.addEventListener('click', hideResultsModal);
    }
});
