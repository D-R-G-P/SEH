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
    const toToggle = document.getElementsByClassName("headerLeftP");
    const toToggleInfo = document.getElementsByClassName("info")[0];
    const headerToggle = document.getElementsByClassName("headerLeft")[0];
    const menuState = getCookie("menuState");

    console.log("Menu state on load:", menuState);

    // Lógica para abrir o cerrar el menú según el estado almacenado en la cookie
    for (let i = 0; i < toToggle.length; i++) {
        if (menuState === "open") {
            toToggle[i].classList.add("open");
        } else if (menuState === "close") {
            toToggle[i].classList.add("close");
        }
    }

    // Lógica para abrir o cerrar la información según el estado almacenado en la cookie
    // ... (puedes agregar lógica similar si es necesario)

    // Lógica para abrir o cerrar el menú según el estado almacenado en la cookie
    // ...

    // Obtener el ancho de headerLeft
    const headerLeftWidth = headerToggle.offsetWidth;

    // Aplicar el ancho como variable CSS
    document.documentElement.style.setProperty('--headerLeftWidth', `${headerLeftWidth}px`);
});

// Función para toggle del menú
function toggleMenu() {
    const toToggle = document.getElementsByClassName("headerLeftP");
    const toToggleInfo = document.getElementsByClassName("info")[0];
    const headerToggle = document.getElementsByClassName("headerLeft")[0];

    // Obtener el estado actual del menú (abierto o cerrado)
    const isMenuOpen = headerToggle.classList.contains("open");

    // Verificar si la cookie ya existe antes de establecerla
    const existingMenuState = getCookie("menuState");
    if (!existingMenuState) {
        // Establecer la cookie solo si no existe
        document.cookie = `menuState=${isMenuOpen ? "open" : "close"}; path=/; samesite=Lax`;
    }

    // Lógica para realizar el toggle entre las clases open y close para "headerLeftP"
    for (let i = 0; i < toToggle.length; i++) {
        toToggle[i].classList.toggle("open");
        toToggle[i].classList.toggle("close");
    }

    // Lógica para realizar el toggle entre las clases open y close para "info"
    toToggleInfo.classList.toggle("open");
    toToggleInfo.classList.toggle("close");

    // Lógica para realizar el toggle entre las clases open y close para "info"
    headerToggle.classList.toggle("open");
    headerToggle.classList.toggle("close");

    // Obtener el ancho de headerLeft
    const headerLeftWidth = headerToggle.offsetWidth;

    // Aplicar el ancho como variable CSS
    document.documentElement.style.setProperty('--headerLeftWidth', `${headerLeftWidth}px`);
}
