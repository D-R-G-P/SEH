// Inicializar Select2 en selects dinámicos
function inicializarSelects(place) {
    // console.log("PLACE: " + place);
    $('.unidad-select').select2({
        placeholder: place,
        allowClear: false
    });
}

function actualizarUnidades(id, contenedor, recorridoId) {
    if (!id || id == null) {
        id = 1;
    }
    // console.log("Actualizar unidades");
    // console.log("Contenedor:", contenedor);
    // console.log("Recorrido ID:", recorridoId);
    // console.log("ID:", id);

    $.ajax({
        url: '/SGH/public/resources/unidades/generar_selector_unidades.php',
        type: 'GET',
        data: { id: id, contenedor: contenedor, recorrido: recorridoId },
        dataType: 'json',
        success: function(response) {
            // console.log("AJAX success");
            // console.log("Response received:", response);

            if (response.recorrido && response.selector) {
                // Solo vaciar el recorrido si hay un nuevo recorrido disponible
                if (response.recorrido.trim() !== "") {
                    $("#" + recorridoId).html(response.recorrido);
                }

                // Vaciar el contenedor antes de insertar el nuevo select
                $("#" + contenedor).empty().html(response.selector);

                
            } else {
                console.error("Respuesta inválida del servidor.");
            }
        },
        error: function(xhr, status, error) {
            console.error("Error en AJAX:", error);
        }
    });
}


$(document).on('change', '.unidad-select', function() {
    let nuevoId = $(this).val();  
    let contenedor = $(this).data('contenedor');  
    let recorrido = $(this).data('recorrido');  

    if (nuevoId) {
        actualizarUnidades(nuevoId, contenedor, recorrido);
    }
});


function volverANivel(id, contenedor, recorridoId) {
    // console.log("Volver a nivel:", id);
    actualizarUnidades(id, contenedor, recorridoId);
}

