// Función para mostrar u ocultar el mensaje de alerta al hacer clic en el botón
function toggleAlert(alert_id) {
    var alertElement = document.getElementById(alert_id);
    if (alertElement.style.display === "none") {
        alertElement.style.display = "block";
    } else {
        alertElement.style.display = "none";
    }
    // Evita que el evento se propague al documento
    event.stopPropagation();
}

// Cerrar el mensaje de alerta al hacer clic en cualquier otro lugar de la pantalla
document.addEventListener("click", function (event) {
    var alertSigns = document.getElementsByClassName("alertSign");
    for (var i = 0; i < alertSigns.length; i++) {
        var alertSign = alertSigns[i];
        if (event.target !== alertSign && !alertSign.contains(event.target)) {
            alertSign.style.display = "none";
        }
    }
});

        // Función para realizar la consulta AJAX y actualizar la tabla
        function updateTable() {
            $.ajax({
                url: 'controllers/tableEquip.php',
                type: 'GET',
                success: function(response) {
                    $('#tableEquip').html(response); // Actualiza el contenido de la tabla
                },
                error: function(xhr, status, error) {
                    console.error('Error al obtener los datos de la tabla: ' + error);
                }
            });
        }

        // Ejecuta la función updateTable() al cargar la página por primera vez
        $(document).ready(function() {
            updateTable();
            setInterval(updateTable, 60000);
        });