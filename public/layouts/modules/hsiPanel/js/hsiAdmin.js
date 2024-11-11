$(document).ready(function () {
    $('#servicioFilter').select2();
});

function loadInfo(dni, servicio) {
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            // Actualizar el contenido de la información del usuario con la respuesta recibida
            document.getElementById("infoUsuario").innerHTML = this.responseText;
            // Llamar a la función select después de cargar el contenido
            select(servicio);
        }
    };
    // Enviar la solicitud POST con el DNI como parámetro
    xhttp.open("POST", "controllers/infoGenAdmn.php", true);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhttp.send("dni=" + dni);
    back.style.display = "flex";
    infoModule.style.display = "flex";
}

function loadInfoDelet(dni) {
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            // Actualizar el contenido de la información del usuario con la respuesta recibida
            document.getElementById("infoUsuario").innerHTML = this.responseText;
            // Llamar a la función select después de cargar el contenido
            selectDelet();
        }
    };
    // Enviar la solicitud POST con el DNI como parámetro
    xhttp.open("POST", "controllers/infoGenAdmn.php", true);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhttp.send("dni=" + dni);
    back.style.display = "flex";
    infoModule.style.display = "flex";
}

function selectDelet() {
    $(document).ready(function () {
        $('#servicioSelect').select2();
    });
}

function select(servicio) {
    $(document).ready(function () {
        $('#servicioSelect').select2();
    });

    // Asignar el valor al select
    $("#servicioSelect").val(servicio).trigger("change");

    // Agregar el evento onchange
    $('#servicioSelect').on('change', function() {
        marcarCambio();
    });
}

function buttonNoti(dni, action) {
    window.location.href = "/SGH/public/layouts/modules/hsiPanel/controllers/buttonNoti.php?dni=" + dni + "&action=" + action;
}

function verificDoc(dni, documento, action, servicio) {
    window.location.href = "/SGH/public/layouts/modules/hsiPanel/controllers/buttonsDoc.php?dni=" + dni + "&documento=" + documento + "&action=" + action + "&servicio=" + servicio;
}

function modifyPermiso(dni, permiso, servicio) {
    window.location.href = "/SGH/public/layouts/modules/hsiPanel/controllers/buttonPermisos.php?dni=" + dni + "&permiso=" + permiso + "&servicio=" + servicio;
}

// Función para realizar la búsqueda en tiempo real
document.getElementById('searchInput').addEventListener('input', function () {
    // Obtener el valor del campo de búsqueda
    var searchTerm = this.value;

    // Realizar la solicitud al servidor mediante AJAX
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'controllers/tablaHabilitadosAdm.php?searchTerm=' + encodeURIComponent(searchTerm), true);
    xhr.onload = function () {
        if (xhr.status === 200) {
            // Actualizar el contenido de la tabla con los resultados de la búsqueda
            document.getElementById('tablaHabilitados').innerHTML = xhr.responseText;
        } else {
            // Manejar errores
            console.log('Error al realizar la solicitud: ' + xhr.status);
        }
    };
    xhr.send();
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
        success: function (response) {
            // Actualizar la tabla con los nuevos resultados
            $("#tablaHabilitados").html(response);
            // Mostrar la tabla después de cargar los nuevos resultados
            $("#tablaHabilitados").show();
            $(".lds-dual-ring").hide(); // Ocultar el elemento de carga


            // Generar botones de paginación
            generarBotonesPaginacion(response.total_paginas);
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
        botonPagina.addEventListener("click", function() {
            var pagina = this.getAttribute("data-pagina");
            actualizarTabla(pagina);
        });
        contenedorPaginacion.appendChild(botonPagina);
    }
}