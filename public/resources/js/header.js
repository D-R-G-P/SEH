function toggleMenu() {
    const toToggle = document.getElementsByClassName("headerLeftP");
    const toToggleInfo = document.getElementsByClassName("info")[0];
    const headerToggle = document.getElementsByClassName("headerLeft")[0];

    // Lógica para realizar el toggle entre las clases open y close para "headerLeftP"
    for (let i = 0; i < toToggle.length; i++) {
        if (toToggle[i].classList.contains("open")) {
            toToggle[i].classList.remove("open");
            toToggle[i].classList.add("close");
        } else {
            toToggle[i].classList.remove("close");
            toToggle[i].classList.add("open");
        }
    }

    // Lógica para realizar el toggle entre las clases open y close para "info"
    if (toToggleInfo.classList.contains("open")) {
        toToggleInfo.classList.remove("open");
        toToggleInfo.classList.add("close");
    } else {
        toToggleInfo.classList.remove("close");
        toToggleInfo.classList.add("open");
    }

    // Lógica para realizar el toggle entre las clases open y close para "info"
    if (headerToggle.classList.contains("open")) {
        headerToggle.classList.remove("open");
        headerToggle.classList.add("close");
    } else {
        headerToggle.classList.remove("close");
        headerToggle.classList.add("open");
    }

    // Obtén el ancho de headerLeft
    const headerLeftWidth = headerToggle.offsetWidth;

    // Aplica el ancho como variable CSS
    document.documentElement.style.setProperty('--headerLeftWidth', `${headerLeftWidth}px`);
}

window.addEventListener('load', function () {
    const headerToggle = document.getElementsByClassName("headerLeft")[0];
    // Obtén el ancho de headerLeft
    const headerLeftWidth = headerToggle.offsetWidth;

    // Aplica el ancho como variable CSS
    document.documentElement.style.setProperty('--headerLeftWidth', `${headerLeftWidth}px`);
})