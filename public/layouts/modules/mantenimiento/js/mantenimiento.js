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

function loadTable(servicioFilter) {
    $.ajax({
        url: 'controllers/listado_servicio_pendiente.php',
        type: 'GET',
        data: { servicioFilter: servicioFilter },
        success: function(response) {
            $('#tablePen').html(response);
        },
        error: function(xhr, status, error) {
            console.error('Error al cargar los datos:', error);
            console.error('Detalles:', xhr.responseText);
            $('#tablePen').html('<tr><td colspan="6" style="text-align: center;">Error al cargar los datos</td></tr>');
        }
    });
}

$(document).ready(function() {
    var initialServicio = $('#servicioSelect').val();
    loadTable(initialServicio);

    $('#servicioSelect').change(function() {
        var selectedServicio = $(this).val();
        loadTable(selectedServicio);
    });
});

function checkNews(id) {
    // Implementa la lógica para manejar la acción del botón
    alert('Check news for ID: ' + id);
}