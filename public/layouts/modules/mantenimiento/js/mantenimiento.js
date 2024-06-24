$(document).ready(function () {
    $('#servicioSelect').select2();
    $('#solicitudServicio').select2();
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