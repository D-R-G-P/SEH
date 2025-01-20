$(document).ready(function () {
    $('#permisosSelect').select2();
    $('#dniSelect').select2();
    $('#servicioSelect').select2();
    $('#selectServicioFilter').select2();
    $('#printServicio').select2();
});

$(".js-example-language").select2({
    language: "es"
});

function formatNumber(input) {
    // Eliminar caracteres que no son números
    const inputValue = input.value.replace(/\D/g, '');

    // Formatear el número con puntos si no está vacío, de lo contrario, dejar en blanco
    const formattedNumber = inputValue !== '' ? Number(inputValue).toLocaleString('es-AR') : '';

    // Actualizar el valor del campo de entrada
    input.value = formattedNumber;
}

function newUser() {
    back.style.display = "flex";
    neUser.style.display = "flex";
}

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

function addDocs(dni) {
    back.style.display = "flex";
    addDocsDiv.style.display = "flex";
    docsDniHidden.value = dni;
}

function loadInfo(dni) {
    
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            // Actualizar el contenido de la información del usuario con la respuesta recibida
            document.getElementById("infoUsuario").innerHTML = this.responseText;
        }
    };
    // Enviar la solicitud POST con el DNI como parámetro
    xhttp.open("POST", "controllers/infoGen.php", true);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhttp.send("dni=" + dni);
    back.style.display = "flex";
    infoModule.style.display = "flex";
}

function buttonSol(dni, action) {
    // Construir la URL con las variables dni y action
    var url = 'controllers/solis.php?dni=' + dni + '&action=' + action;
    
    // Redirigir a la página con la URL construida
    window.location.href = url;
}

function printInforme() {
    back.style.display = "flex";
    printModal.style.display = "flex";
    $('#printServicio').val(servicioUser).trigger('change');
}