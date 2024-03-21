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


function select(servicio) {
    $(document).ready(function () {
        $('#servicioSelect').select2();
    });
    $("#servicioSelect").val(servicio).trigger("change");
}