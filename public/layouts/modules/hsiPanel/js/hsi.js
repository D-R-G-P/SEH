$(document).ready(function () {
    $('#permisosSelect').select2();
    $('#dniSelect').select2();
    $('#servicioSelect').select2();
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