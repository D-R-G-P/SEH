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