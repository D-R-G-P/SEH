$(document).ready(function () {
    $('#selectServicioFilter').select2();
    $('#solicitudServicio').select2();
    $('#destino').select2();
    $('#prioridad').select2();
});

$(".js-example-language").select2({
    language: "es"
});

function checkNews(id) {
    $.ajax({
        type: "POST",
        url: "controllers/actualizar_new.php",
        data: { id: id },
        success: function (response) {
            // La solicitud fue exitosa
            // Actualizar la página después de que la actualización del estado haya tenido éxito
            location.reload();
        },
        error: function (xhr, status, error) {
            // Ocurrió un error al realizar la solicitud
            console.error("Error al actualizar el estado:", error);
        }
    });
}

function loadTable(servicioFilter) {
    $.ajax({
        url: 'controllers/listado_servicio_pendiente.php',
        type: 'GET',
        data: { servicioFilter: servicioFilter },
        success: function (response) {
            $('#tablePen').html(response);
        },
        error: function (xhr, status, error) {
            console.error('Error al cargar los datos:', error);
            console.error('Detalles:', xhr.responseText);
            $('#tablePen').html('<tr><td colspan="6" style="text-align: center;">Error al cargar los datos</td></tr>');
        }
    });
}

$(document).ready(function () {
    var initialServicio = $('#servicioSelect').val();
    loadTable(initialServicio);

    $('#servicioSelect').change(function () {
        var selectedServicio = $(this).val();
        loadTable(selectedServicio);
    });
});

function checkAccept() {
    var check = document.getElementById('accept');
    var buttSend = document.getElementById('btn-send');

    if (check.checked) {
        // Si el checkbox está marcado, habilitar el botón
        buttSend.disabled = false;  // Deshabilitar disabled
    } else {
        // Si el checkbox no está marcado, deshabilitar el botón
        buttSend.disabled = true;   // Activar disabled
    }
}

function loadInfo(id, gestionMode) {
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            // Actualizar el contenido de la información del usuario con la respuesta recibida
            document.getElementById("info").innerHTML = this.responseText;

            back.style.display = "flex";  // Solo si el elemento existe
            infoCaseBase.style.display = "flex";  // Solo si el elemento existe
        }
    };
    // Enviar la solicitud POST con los parámetros
    xhttp.open("POST", "controllers/loadCase.php", true);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

    // Pasar los parámetros id y gestionMode
    xhttp.send("id=" + id + "&gestionMode=" + gestionMode);
}

function user_register(id, dni) {
    // Crear el cuerpo de datos que será enviado al servidor
    const data = {
        id: id,
        dni: dni
    };

    // Enviar los datos al servidor con fetch
    fetch('controllers/register_user.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data) // Convertir los datos a JSON
    })
    .then(response => response.json()) // Parsear la respuesta como JSON
    .catch(error => {
        console.error('Error en la solicitud:', error);
        alert('Hubo un problema al realizar el registro.');
    });
}


function finished(estado) {
    // Implementa la lógica para manejar la acción del botón
    if (estado === "Completado") {
        alert("ATENCIÓN: Una vez guardado el estado como completado, el estado no se puede volver a modificar.");
    }
}

function audit(id) {
    // Implementa la lógica para manejar la acción del botón
    if (window.confirm("¿Está seguro que desea marcar este caso para auditar? Esta acción no se puede deshacer.")) {
        // Si el usuario confirma la acción, realizar la solicitud AJAX
        location.href = "controllers/to_audit.php?id=" + id;
    }
}