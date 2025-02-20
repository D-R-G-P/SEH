// Función para obtener el valor de una cookie por su nombre
function getCookie(name) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);

    if (parts.length === 2) {
        return parts.pop().split(';').shift();
    }
}

// Al cargar la página, establecer el estado del menú según la cookie si no existe
window.addEventListener('load', function () {
    const toggleHeader = document.getElementsByClassName("headerLeft")[0];
    const toggleInfo = document.getElementsByClassName("info")[0];
    const toggleHeaderP = document.getElementsByClassName("headerLeftP");
    const cookie = getCookie("menuState");

    if (cookie == "open") {

        toggleHeader.classList.remove("close");
        toggleHeader.classList.add("open");

        toggleInfo.classList.remove("close");
        toggleInfo.classList.add("open");

        for (let i = 0; i < toggleHeaderP.length; i++) {
            toggleHeaderP[i].classList.remove("close");
            toggleHeaderP[i].classList.add("open");
        }
    } else if (cookie == "close") {
        toggleHeader.classList.remove("open");
        toggleHeader.classList.add("close");

        toggleInfo.classList.remove("open");
        toggleInfo.classList.add("close");

        for (let i = 0; i < toggleHeaderP.length; i++) {
            toggleHeaderP[i].classList.remove("open");
            toggleHeaderP[i].classList.add("close");
        }
    }
});

// Función para toggle del menú
function toggleMenu() {
    const toggleHeader = document.getElementsByClassName("headerLeft")[0];
    const toggleInfo = document.getElementsByClassName("info")[0];
    const toggleHeaderP = document.getElementsByClassName("headerLeftP");
    const cookie = getCookie("menuState");


    if (cookie == "open") {

        toggleHeader.classList.remove("open");
        toggleHeader.classList.add("close");

        toggleInfo.classList.remove("open");
        toggleInfo.classList.add("close");

        for (let i = 0; i < toggleHeaderP.length; i++) {
            toggleHeaderP[i].classList.remove("open");
            toggleHeaderP[i].classList.add("close");
        }

        document.cookie = "menuState=close; expires=Thu, 01 Jan 2100 00:00:00 UTC; path=/; samesite=Lax"
    } else if (cookie == "close") {
        toggleHeader.classList.remove("close");
        toggleHeader.classList.add("open");

        toggleInfo.classList.remove("close");
        toggleInfo.classList.add("open");

        for (let i = 0; i < toggleHeaderP.length; i++) {
            toggleHeaderP[i].classList.remove("close");
            toggleHeaderP[i].classList.add("open");
        }

        document.cookie = "menuState=open; expires=Thu, 01 Jan 2100 00:00:00 UTC; path=/; samesite=Lax"
    } else {
        document.cookie = "menuState=close; expires=Thu, 01 Jan 2100 00:00:00 UTC; path=/; samesite=Lax"
        location.reload();
    }
}

function toast(message, type, duration = 2500) {
    let toastContainer = document.getElementById("toast-container");

    if (!toastContainer) {
        toastContainer = document.createElement("div");
        toastContainer.id = "toast-container";
        document.body.appendChild(toastContainer);
    }

    const toast = document.createElement("div");
    toast.className = `toast ${type}`;
    
    // Definir el icono según el tipo de notificación
    let icon;
    switch (type) {
        case "success":
            icon = '<i class="fa-solid fa-circle-check"></i>';
            break;
        case "error":
            icon = '<i class="fa-solid fa-circle-xmark"></i>';
            break;
        case "warning":
            icon = '<i class="fa-solid fa-triangle-exclamation"></i>';
            break;
        default:
            icon = '<i class="fa-solid fa-circle-info"></i>';
            break;
    }

    // Insertar icono y mensaje
    toast.innerHTML = `${icon} <span>${message}</span>`;

    // Agregar al contenedor
    toastContainer.appendChild(toast);

    // Activar animación
    setTimeout(() => {
        toast.classList.add("active");
    }, 100);

    // Cerrar al hacer clic
    toast.addEventListener("click", () => removeToast(toast));

    // Desaparecer tras la duración especificada
    setTimeout(() => {
        removeToast(toast);
    }, duration);
}

function removeToast(toast) {
    toast.classList.remove("active");
    setTimeout(() => toast.remove(), 500); // Espera a que termine la transición
}


function toggleErrorReport() {
    let panel = document.getElementById("errorReportPanel");
    panel.classList.toggle("open");
}


function sendErrorReport() {
    let user = document.getElementById("user").value;
    let description = document.getElementById("errorDescription").value;

    if (!user || !description.trim()) {
        toast("Todos los campos son requeridos.", "error");
        return;
    }

    $.ajax({
        url: '/SGH/public/resources/controllers/error.php',
        method: 'POST',
        data: { user, description },
        dataType: 'json',
        success: function (response) {
            if (response.success) {
                toast("Error reportado correctamente.", "success");
                document.getElementById("errorDescription").value = "";
                toggleErrorReport();
            } else {
                toast("Error: " + (response.error || "No se pudo reportar el error."), "error");
            }
        },
        error: function (xhr, status, error) {
            console.error("Error AJAX:", xhr.responseText || error);
            toast("Error: " + (xhr.responseText || error), "error");
        }
    });
}