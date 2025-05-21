document.addEventListener("DOMContentLoaded", function() {
    console.log("DOM fully loaded and parsed");

    document.querySelectorAll(".day-checkbox").forEach(checkbox => {
        const row = checkbox.closest(".day-row");
        const timeInputs = row.querySelectorAll("input[type='time']");

        function toggleInputs(enabled) {
            console.log("Toggling inputs:", enabled);
            row.style.opacity = enabled ? "1" : "0.5";
            timeInputs.forEach(input => input.disabled = !enabled);
        }

        console.log("Initial checkbox state:", checkbox.checked);
        toggleInputs(checkbox.checked);

        checkbox.addEventListener("change", function() {
            console.log("Checkbox changed:", this.checked);
            toggleInputs(this.checked);
        });
    });

    console.log("Termino la carga del DOM");
});
