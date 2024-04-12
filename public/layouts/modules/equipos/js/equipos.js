$(document).ready(function () {
    $('#tipo_equipo').select2();
    $('#servicio').select2();
});

$(document).ready(function() {
    // Función para cargar los tipos de equipo desde el servidor
    function cargarTiposEquipo() {
        $.ajax({
            type: 'GET',
            url: 'controllers/getTiposEquipo.php', // Ruta al controlador PHP que obtiene los tipos de equipo
            success: function(response) {
                $('#tipo_equipo').html(response);
                // Aplicar nuevamente la funcionalidad de select2 si es necesario
                $('#tipo_equipo').select2();
                backTipo.style.display = 'none';
            },
            error: function(xhr, status, error) {
                // Maneja los errores de la solicitud AJAX
                alert('Error al cargar los tipos de equipo: ' + error);
            }
        });
    }

    // Manejar el envío del formulario para agregar un nuevo tipo de equipo
    $('#addTipoForm').submit(function(e) {
        e.preventDefault(); // Evita el comportamiento predeterminado del formulario

        // Obtén los datos del formulario
        var formData = $(this).serialize();

        // Realiza la solicitud AJAX para agregar el tipo de equipo
        $.ajax({
            type: 'POST',
            url: 'controllers/addTipoEquipo.php', // Ruta al controlador PHP que procesa el formulario
            data: formData,
            success: function(response) {
                // Mostrar notificación de éxito
                $('#notis').html('El tipo de equipo se ha agregado correctamente');
                $('#notis').addClass('active');
                setTimeout(function() {
                    $('#notis').removeClass('active');
                }, 2500);
                $('#addTipoForm')[0].reset();

                // Llama a la función para cargar los tipos de equipo nuevamente
                cargarTiposEquipo();
            },
            error: function(xhr, status, error) {
                // Maneja los errores de la solicitud AJAX
                alert('Error al enviar el formulario: ' + error);
            }
        });
    });
});