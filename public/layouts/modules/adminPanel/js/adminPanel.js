$(document).ready(function () {
    $('#selectBoss').select2();
    $('#modifyBoss').select2();
});

$(".js-example-language").select2({
    language: "es"
});

function showDeleteConfirmation(id, servicio, jefe) {
    document.getElementById('back').style.display = "flex";
    document.getElementById('advertenciaDelete').style.display = "flex";
    document.getElementById('servicioName').innerHTML = servicio;
    document.getElementById('servicioJefe').innerHTML = jefe;
    btnDelete.onclick = function() {
        window.location.href = '/SGH/public/layouts/modules/adminPanel/controllers/turnEstadoServicio.php?id='+id+'&action=eliminar';
    }
}