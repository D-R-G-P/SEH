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
    $('#selectRol').select2();
    $('#editselectServicio').select2();
    $('#editselectespecialidad').select2();
    $('#editselectcargo').select2();
    $('#editselectrol').select2();
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

// Función para realizar la búsqueda en tiempo real
document.getElementById('searchInput').addEventListener('input', function () {
    // Obtener el valor del campo de búsqueda
    var searchTerm = this.value;

    // Realizar la solicitud al servidor mediante AJAX
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'controllers/buscar_personal.php?searchTerm=' + encodeURIComponent(searchTerm), true);
    xhr.onload = function () {
        if (xhr.status === 200) {
            // Actualizar el contenido de la tabla con los resultados de la búsqueda
            document.getElementById('tablaPersonal').innerHTML = xhr.responseText;
        } else {
            // Manejar errores
            console.log('Error al realizar la solicitud: ' + xhr.status);
        }
    };
    xhr.send();
});

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
    $('#editselectrol').val(rol).trigger('change');

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

$(document).ready(function () {
    $("#selectServicioFilter").select2();
    $('#selectServicioFilter').val(serviceId).trigger('change');

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
            botonPagina.addEventListener("click", function () {
                var pagina = this.getAttribute("data-pagina");
                actualizarTabla(pagina);
            });
            contenedorPaginacion.appendChild(botonPagina);
        }
    }

    // Función para actualizar la tabla con los resultados filtrados
    // function actualizarTabla(pagina, searchTerm, selectServicioFilter) {
    //   // Ocultar la tabla mientras se cargan los nuevos resultados
    //   $("#tablaPersonal").hide();
    //   $(".lds-dual-ring").show(); // Mostrar el elemento de carga

    //   // Realizar la solicitud AJAX al controlador PHP para actualizar la tabla
    //   $.ajax({
    //     url: "controllers/buscar_personal.php",
    //     type: "GET",
    //     dataType: "html",
    //     data: {
    //       pagina: pagina,
    //       searchTerm: searchTerm,
    //       selectServicioFilter: selectServicioFilter
    //     },
    //     success: function(response) {
    //       // Actualizar la tabla con los nuevos resultados
    //       $("#tablaPersonal").html(response);
    //       // Mostrar la tabla después de cargar los nuevos resultados
    //       $("#tablaPersonal").show();
    //       $(".lds-dual-ring").hide(); // Ocultar el elemento de carga


    //       // Generar botones de paginación
    //       generarBotonesPaginacion(response.total_paginas);
    //     },
    //     error: function(xhr, status, error) {
    //       console.log("Error al realizar la solicitud: " + error);
    //     }
    //   });
    // }

    // Evento change del select para actualizar la tabla al cambiar el servicio
    $("#selectServicioFilter").on("change", function () {
        var selectServicioFilterValue = $(this).val(); // Obtener el valor seleccionado del select2
        actualizarTabla(1, $("#searchInput").val(), selectServicioFilterValue); // Llamar a actualizarTabla con el nuevo valor
    });

    // Cargar la tabla con los resultados iniciales
    actualizarTabla(1, $("#searchInput").val(), $("#selectServicioFilter").val());

    // Función para realizar la búsqueda en tiempo real con retardo
    var timeout = null;
    $("#searchInput").on("input", function () {
        clearTimeout(timeout); // Limpiar el temporizador existente si hay alguno
        // Configurar un nuevo temporizador para retrasar la búsqueda
        timeout = setTimeout(function () {
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
    $("#contenedorPaginacion").on("click", ".paginationBtn", function () {
        var pagina = $(this).data("pagina");
        var searchTerm = $("#searchInput").val();
        var selectServicioFilter = $("#selectServicioFilter").val();
        actualizarTabla(pagina, searchTerm, selectServicioFilter);
    });
});

function cambiarPaginar(pagina) {
    cambiarPagina(pagina);
}

// Función para cambiar de página al hacer clic en los botones de paginación
function cambiarPagina(pagina) {
    // Obtener el valor del campo de búsqueda
    var searchTerm = $("#searchInput").val();

    // Obtener el valor seleccionado del select2
    var selectServicioFilter = $("#selectServicioFilter").val();

    // Llamar a la función actualizarTabla para enviar la solicitud al servidor con la nueva página
    actualizarTabla(pagina, searchTerm, selectServicioFilter);
}

function actualizarTabla(pagina, searchTerm, selectServicioFilter) {
    const scrollPos = $(window).scrollTop();

    $("#tablaPersonal").hide();
    $(".lds-dual-ring").show();

    $.ajax({
        url: "controllers/buscar_personal.php",
        type: "GET",
        dataType: "html",
        data: {
            pagina,
            searchTerm,
            selectServicioFilter
        },
        success: function (response) {
            $("#tablaPersonal").html(response).show();
            $(".lds-dual-ring").hide();
            $(window).scrollTop(scrollPos); // Fuerza la restauración de la posición del scroll
        },
        error: function (xhr, status, error) {
            console.log("Error al realizar la solicitud: " + error);
        }
    });
}



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
        botonPagina.addEventListener("click", function () {
            var pagina = this.getAttribute("data-pagina");
            actualizarTabla(pagina);
        });
        contenedorPaginacion.appendChild(botonPagina);
    }
}