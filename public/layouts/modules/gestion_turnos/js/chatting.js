// chatting.js

// Variables globales para el estado de la aplicación
let isAdmin = false; // Indica si el usuario actual tiene permisos de administrador
let lastSearchTerm = ""; // Almacena el último término de búsqueda para filtrar la lista de chats

/**
 * Filtra la lista de chats basándose en el término de búsqueda actual.
 * Se activa con la entrada en el campo de búsqueda.
 */
document.getElementById('search-input').addEventListener('input', function () {
    lastSearchTerm = this.value.toLowerCase();
    filterChatList();
});

/**
 * Función global para filtrar los elementos de la lista de chats.
 * Muestra u oculta los chats según si su nombre o número coincide con el término de búsqueda.
 */
window.filterChatList = function () {
    const searchTerm = lastSearchTerm;
    const chatList = document.querySelector(".chat-list");
    const chatItems = chatList.querySelectorAll(".chat-item");

    chatItems.forEach(item => {
        const chatName = item.querySelector(".div2").textContent.toLowerCase();
        const chatNumero = item.querySelector(".div3").textContent.toLowerCase();

        if (chatName.includes(searchTerm) || chatNumero.includes(searchTerm)) {
            item.style.display = "grid"; // Muestra el elemento si coincide
        } else {
            item.style.display = "none"; // Oculta el elemento si no coincide
        }
    });
}

/**
 * Muestra un mensaje toast (notificación emergente) en la parte superior de la pantalla.
 * @param {string} message - El mensaje a mostrar.
 * @param {'success' | 'error' | 'info' | 'warning'} type - El tipo de toast (afecta el estilo y color).
 * @param {number} [duration=2500] - La duración en milisegundos que el toast será visible (por defecto 2.5 segundos).
 */
window.toast = function (message, type, duration = 2500) {
    let toastContainer = document.getElementById("toast-container");

    // Crea el contenedor si no existe
    if (!toastContainer) {
        toastContainer = document.createElement("div");
        toastContainer.id = "toast-container";
        document.body.appendChild(toastContainer);
    }

    let toast = document.createElement("div");
    toast.className = `toast ${type}`;
    toast.innerText = message; // Usar innerText para seguridad contra inyección de HTML (XSS)

    toastContainer.appendChild(toast);

    // Activa la animación de entrada
    setTimeout(() => {
        toast.classList.add('active');
    }, 10); // Pequeño retraso para asegurar que la animación se aplique

    // Elimina el toast después de la duración especificada
    setTimeout(function () {
        if (toastContainer.contains(toast)) {
            toast.classList.remove('active'); // Inicia la animación de salida
            toast.addEventListener('transitionend', () => {
                if (toastContainer.contains(toast)) {
                    toastContainer.removeChild(toast);
                }
            }, { once: true }); // Elimina el elemento solo después de que termine la transición
        }
    }, duration);
};

/**
 * Formatea un número de teléfono para una visualización legible en Argentina.
 * Requiere la clase `TelefonoArgentino` definida en otro archivo.
 * @param {string} number - El número de teléfono a formatear (ej. "5492214380474@c.us").
 * @returns {string} El número formateado o el original si ocurre un error.
 */
function formatPhoneNumber(number) {
    // Elimina el sufijo de WhatsApp si está presente
    number = number.replace("@c.us", "");

    try {
        // Intenta usar la clase TelefonoArgentino para formatear
        let telefono = new TelefonoArgentino(number);
        return telefono._format(telefono.data);
    } catch (error) {
        console.error("Error al formatear el número:", error);
        return number; // Devuelve el número sin cambios si hay un error
    }
}

// Inicializa Select2 en todos los elementos con la clase 'select2' cuando el DOM está listo.
// Select2 es una librería de jQuery para mejorar los selectores HTML.
$(document).ready(function () {
    $('.select2').select2();
});

/**
 * Cambia el formulario visible para crear un nuevo chat basándose en el tipo de chat seleccionado.
 * @param {'contacto' | 'paciente' | 'numero'} value - El tipo de chat a mostrar.
 */
function changeForm(value) {
    // Oculta todos los formularios de creación de chat
    $('#newContacto').hide();
    $('#newPaciente').hide();
    $('#newNumero').hide();

    // Muestra el formulario correspondiente al valor seleccionado
    if (value === 'contacto') {
        $('#newContacto').show();
    } else if (value === 'paciente') {
        $('#newPaciente').show();
    } else if (value === 'numero') {
        $('#newNumero').show();
    }
}

/**
 * Muestra u oculta el selector de emojis.
 */
function turnEmojiList() {
    const emojiList = document.querySelector('emoji-picker');
    if (!emojiList) return; // Si el picker no existe, no hace nada

    const isVisible = emojiList.style.display === 'block';
    emojiList.style.display = isVisible ? 'none' : 'block'; // Alterna la visibilidad
}

// Event listener para el botón que muestra/oculta la lista de emojis.
document.getElementById('emojiList').addEventListener('click', function (event) {
    event.stopPropagation(); // Evita que el clic se propague y cierre el picker inmediatamente.
    turnEmojiList();
});

// Event listener global para cerrar el picker de emojis si se hace clic fuera de él.
document.addEventListener('click', function (event) {
    const emojiList = document.querySelector('emoji-picker');
    if (!emojiList) return;

    // Si el picker está visible y el clic no fue dentro del picker, ni en el botón que lo abre, ni en el input de mensaje
    if (emojiList.style.display === 'block' &&
        !emojiList.contains(event.target) &&
        event.target.id !== 'emojiList' &&
        !event.target.closest('#emojiList') && // Considera también clics en elementos hijos del botón
        event.target.id !== 'messageInput') {
        emojiList.style.display = 'none';
    }
});

const emojiPicker = document.querySelector('emoji-picker');

// Event listener para cuando se selecciona un emoji del picker.
if (emojiPicker) {
    emojiPicker.addEventListener('emoji-click', event => {
        const emoji = event.detail.unicode; // Obtiene el emoji seleccionado
        const currentMessageInput = document.getElementById('messageInput'); // Referencia al input de mensaje
        insertAtCursor(currentMessageInput, emoji); // Inserta el emoji en el input
        currentMessageInput.focus(); // Devuelve el foco al input de mensaje para que el usuario pueda seguir escribiendo
    });
}

/**
 * Inserta texto en la posición actual del cursor en un campo de entrada (input o textarea).
 * @param {HTMLInputElement|HTMLTextAreaElement} input - El campo de entrada HTML.
 * @param {string} textToInsert - El texto a insertar en el campo.
 */
function insertAtCursor(input, textToInsert) {
    const start = input.selectionStart; // Posición inicial de la selección del cursor
    const end = input.selectionEnd;     // Posición final de la selección del cursor

    const textBefore = input.value.substring(0, start); // Texto antes del cursor
    const textAfter = input.value.substring(end);       // Texto después del cursor

    input.value = textBefore + textToInsert + textAfter; // Reconstruye el valor con el texto insertado

    const newPos = start + textToInsert.length; // Calcula la nueva posición del cursor
    input.selectionStart = input.selectionEnd = newPos; // Mueve el cursor a la nueva posición
}

/**
 * Iguala el ancho de un elemento al ancho del contenedor de la lista de chats.
 * Usado para los paneles laterales de datos del paciente y opciones.
 * @param {string} elementId - El ID del elemento HTML cuyo ancho se ajustará.
 */
function igualarAnchoElementoAlDeChats(elementId) {
    const chatsContainer = document.querySelector('.chats'); // Contenedor de la lista de chats
    const elemento = document.getElementById(elementId); // Elemento a ajustar

    if (chatsContainer && elemento) {
        const anchoChats = window.getComputedStyle(chatsContainer).width; // Obtiene el ancho calculado del contenedor de chats
        elemento.style.width = anchoChats; // Aplica ese ancho al elemento
    }
}

// Ajusta el ancho de los paneles laterales cuando se redimensiona la ventana, si están activos.
window.addEventListener("resize", () => {
    if (document.getElementById("patientData").classList.contains("active")) {
        igualarAnchoElementoAlDeChats("patientData");
    }
    if (document.getElementById("patientOptions").classList.contains("active")) {
        igualarAnchoElementoAlDeChats("patientOptions");
    }
});

// Configuración de Select2 para el selector de país con banderas.
$(document).ready(function () {
    /**
     * Función para formatear las opciones de país en Select2, mostrando banderas.
     * @param {object} state - El objeto de estado de la opción de Select2.
     * @returns {jQuery} Un elemento jQuery con la bandera y el texto.
     */
    function formatState(state) {
        if (!state.id) {
            return state.text; // Si no hay ID, devuelve solo el texto (ej. placeholder)
        }
        var flagCode = state.element.getAttribute('data-flag'); // Obtiene el código de la bandera del atributo data-flag
        if (!flagCode) return state.text; // Si no hay código de bandera, devuelve solo el texto
        var flagUrl = 'https://flagicons.lipis.dev/flags/4x3/' + flagCode + '.svg'; // URL de la imagen de la bandera
        var $state = $( // Crea un elemento span con la imagen de la bandera y el texto
            '<span><img src="' + flagUrl + '" class="img-flag" style="width: 20px; height: auto; margin-right: 10px;" /> ' + state.text + '</span>'
        );
        return $state;
    }

    // Aplica la función de formato a los selectores con la clase 'js-example-templating'
    $(".js-example-templating").select2({
        templateResult: formatState,    // Formato para las opciones en el dropdown
        templateSelection: formatState, // Formato para la opción seleccionada
        width: 'resolve'                // Ajusta el ancho del Select2 automáticamente
    });
});

/**
 * Muestra u oculta el campo de entrada para un código de país personalizado ("Otro País").
 * Se activa cuando el usuario selecciona la opción "Otro" en el selector de país.
 * @param {string} value - El valor seleccionado en el dropdown de país.
 */
function checkOther(value) {
    const otherCountryInput = document.getElementById('otherCountry');
    if (value === 'un') { // 'un' es el valor para la opción "Otro"
        otherCountryInput.style.display = 'block'; // Muestra el campo
        otherCountryInput.required = true; // Lo hace obligatorio
    } else {
        otherCountryInput.style.display = 'none'; // Oculta el campo
        otherCountryInput.required = false; // Deja de ser obligatorio
        otherCountryInput.value = ''; // Limpia su valor
    }
}

// Manejo del envío del formulario para derivar un chat a otro agente.
$(document).ready(function () {
    $('#derivarForm').submit(function (event) {
        event.preventDefault(); // Evita el envío tradicional del formulario

        // Recolecta los datos del formulario
        var formData = {
            chat_id: $('#id_chat').val(),
            estado: 'chatting', // El chat se derivará a un agente, por lo tanto, sigue en estado 'chatting'
            agente: $('#agenteSelect').val() // ID del agente al que se deriva
        };

        // Realiza la petición AJAX
        $.ajax({
            type: 'POST',
            url: 'api/modificar_estado_chat.php', // Endpoint para modificar el estado del chat
            data: JSON.stringify(formData), // Envía los datos como JSON
            contentType: 'application/json', // Indica que el contenido es JSON
            dataType: 'json', // Espera una respuesta JSON
            success: function (response) {
                if (response && response.success) {
                    toast(response.message || 'Chat derivado correctamente', 'success');
                    $('#back').hide(); // Oculta el fondo oscuro del modal
                    $('#derivar').hide(); // Oculta el modal de derivación
                    $('#derivarForm')[0].reset(); // Resetea el formulario
                    $('#agenteSelect').val(null).trigger('change'); // Limpia y resetea el Select2 del agente
                } else {
                    toast(response.message || 'Error al derivar el chat', 'error');
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.error("Error AJAX al derivar:", textStatus, errorThrown);
                toast('Error de conexión al derivar el chat.', 'error');
            }
        });
    });
});

let archivosSeleccionados = []; // Array global para almacenar los archivos seleccionados para enviar

// Se ejecuta cuando todo el DOM ha sido cargado.
document.addEventListener("DOMContentLoaded", function () {
    // Referencias a elementos del DOM
    const chatList = document.querySelector(".chat-list");
    const buttons = document.querySelectorAll(".btn-chat"); // Botones de filtro de estado de chat
    const chatBody = document.querySelector("#chatBody"); // Contenedor de los mensajes del chat
    const chatHeader = document.querySelector("#chatHeader"); // Cabecera del chat
    const messageInput = document.querySelector("#messageInput"); // Campo de entrada de mensajes
    const sendMessageButton = document.querySelector("#sendMessageButton"); // Botón para enviar mensajes
    const docInput = document.getElementById("docInput"); // Input para seleccionar archivos
    const emojiButton = document.getElementById("emojiList"); // Botón para el selector de emojis
    const fileUploadLabel = document.querySelector(".custom-file-upload"); // Label personalizado para el input de archivo
    const filePreview = document.getElementById("filePreview"); // Contenedor para previsualizar archivos

    const patientDataContainer = document.getElementById("patientData"); // Contenedor de datos del paciente
    const patientOptionsContainer = document.getElementById("patientOptions"); // Contenedor de opciones del paciente

    // Referencias a elementos del modal de edición de paciente
    const editPatientModal = document.getElementById('editPatientModal');
    const editPatientForm = document.getElementById('editPatientForm');
    const closeEditPatientModal = document.getElementById('closeEditPatientModal');


    let currentChatId = null; // ID del chat actualmente abierto
    let messageInterval = null; // Variable para el intervalo de actualización de mensajes
    window.pacienteNumero = null; // Número de teléfono del paciente del chat actual (global)

    // Botón "Nuevo chat" que se añade dinámicamente a la cabecera
    const buttonNewChat = document.createElement("button");
    buttonNewChat.textContent = "Nuevo chat";
    buttonNewChat.classList.add("btn-tematico");
    buttonNewChat.style.margin = "0 auto";
    buttonNewChat.style.fontWeight = "bold";
    buttonNewChat.addEventListener("click", function () {
        document.getElementById('back').style.display = "flex"; // Muestra el fondo oscuro del modal
        document.getElementById('newChat').style.display = "flex"; // Muestra el modal de nuevo chat
        // Resetea todos los formularios de nuevo chat y Select2
        $('#newContacto')[0].reset();
        $('#newPaciente')[0].reset();
        $('#newNumero')[0].reset();
        $('#contacto').val(null).trigger('change');
        $('#paciente').val(null).trigger('change');
        changeForm('contacto'); // Establece el formulario por defecto a 'contacto'
    });

    /**
     * Inicializa o restablece la vista del chat a su estado predeterminado (sin chat abierto).
     * Oculta el área de chat, deshabilita inputs y limpia previsualizaciones.
     */
    function initializeChatView() {
        chatHeader.innerHTML = ""; // Limpia la cabecera del chat
        chatHeader.appendChild(buttonNewChat); // Añade el botón "Nuevo chat"
        chatBody.innerHTML = ""; // Limpia el cuerpo de mensajes

        // Deshabilita los elementos de entrada de mensajes y envío
        emojiButton.disabled = true;
        fileUploadLabel.classList.add("disabled");
        docInput.disabled = true;
        messageInput.disabled = true;
        messageInput.value = "";
        sendMessageButton.disabled = true;

        // Oculta y limpia los contenedores de datos y opciones del paciente
        if (patientDataContainer) {
            patientDataContainer.innerHTML = "";
            patientDataContainer.classList.remove("active");
        }
        if (patientOptionsContainer) {
            patientOptionsContainer.innerHTML = "";
            patientOptionsContainer.classList.remove("active");
        }

        archivosSeleccionados = []; // Limpia la lista de archivos seleccionados
        renderPreview(); // Actualiza la previsualización (la oculta si no hay archivos)

        // Elimina la clase 'active-chat' de cualquier chat previamente seleccionado en la lista
        document.querySelectorAll('.chat-item.active-chat').forEach(item => item.classList.remove('active-chat'));

        currentChatId = null; // Reinicia el ID del chat activo
        window.pacienteNumero = null; // Reinicia el número del paciente activo
        // Detiene el intervalo de actualización de mensajes si está activo
        if (messageInterval) {
            clearInterval(messageInterval);
            messageInterval = null;
        }
    }
    initializeChatView(); // Llama a la función para inicializar la vista al cargar la página

    /**
     * Formatea una marca de tiempo (timestamp) a formato HH:MM.
     * @param {string} timestampString - La cadena de texto de la marca de tiempo (ej. "2023-10-27 10:30:00").
     * @returns {string} La hora formateada (ej. "10:30") o "--:--" si hay un error.
     */
    function formatTimestampToHHMM(timestampString) {
        try {
            const date = new Date(timestampString);
            if (isNaN(date.getTime())) throw new Error("Invalid date string"); // Valida la fecha
            const hours = date.getHours().toString().padStart(2, '0');
            const minutes = date.getMinutes().toString().padStart(2, '0');
            return `${hours}:${minutes}`;
        } catch (e) {
            console.error("Error formateando marca de tiempo:", timestampString, e);
            return '--:--';
        }
    }

    /**
     * Obtiene la clase de icono de Font Awesome basada en la extensión de un archivo.
     * @param {string} extension - La extensión del archivo (ej. "pdf", "docx").
     * @returns {string} La clase de Font Awesome correspondiente (ej. "fa-file-pdf").
     */
    function getFontAwesomeIconClassByExtension(extension) {
        const lowerExtension = extension ? extension.toLowerCase() : '';
        switch (lowerExtension) {
            case 'pdf': return 'fa-file-pdf';
            case 'doc': case 'docx': return 'fa-file-word';
            case 'ppt': case 'pptx': return 'fa-file-powerpoint';
            case 'xls': case 'xlsx': return 'fa-file-excel';
            case 'txt': return 'fa-file-alt';
            case 'csv': return 'fa-file-csv';
            case 'zip': case 'rar': return 'fa-file-archive';
            case 'mp3': case 'wav': return 'fa-file-audio';
            case 'mp4': case 'mov': case 'avi': return 'fa-file-video';
            default: return 'fa-file'; // Icono por defecto para extensiones desconocidas
        }
    }

    /**
     * Extrae la parte de la fecha (YYYY-MM-DD) de una marca de tiempo.
     * Utilizado para construir rutas de archivos en el servidor.
     * @param {string} timestampString - La cadena de texto de la marca de tiempo.
     * @returns {string} La fecha formateada (ej. "2023-10-27") o "unknown-date" si hay un error.
     */
    function getTimestampDatePart(timestampString) {
        try {
            const date = new Date(timestampString);
            if (isNaN(date.getTime())) throw new Error("Invalid date string");
            const year = date.getFullYear();
            const month = (date.getMonth() + 1).toString().padStart(2, '0');
            const day = date.getDate().toString().padStart(2, '0');
            return `${year}-${month}-${day}`;
        } catch (e) {
            console.error("Error formateando parte de la fecha:", timestampString, e);
            return 'unknown-date';
        }
    }

    /**
     * Verifica si una extensión de archivo corresponde a un tipo de imagen.
     * @param {string} extension - La extensión del archivo.
     * @returns {boolean} True si es una extensión de imagen, false en caso contrario.
     */
    function isImageExtension(extension) {
        if (!extension) return false;
        const imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp', 'ico'];
        return imageExtensions.includes(extension.toLowerCase());
    }

    /**
     * Obtiene y muestra los mensajes de un chat específico.
     * También se encarga de actualizar el estado de los mensajes existentes (ticks).
     * @param {string} chatId - El ID del chat del cual obtener los mensajes.
     */
    function fetchMessages(chatId) {
        if (!chatId) return; // No hace nada si no hay ID de chat
        const messageContainer = chatBody; // El div donde se muestran los mensajes

        fetch(`api/get_messages.php?chat_id=${chatId}`) // Petición a la API para obtener mensajes
            .then(response => {
                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                return response.json();
            })
            .then(data => {
                if (data.error) {
                    console.error("API Error (get_messages):", data.error);
                    toast(`Error al cargar mensajes: ${data.error}`, 'error');
                    return;
                }

                // Guarda la posición de scroll y verifica si el usuario está al final
                const scrollPosition = messageContainer.scrollTop;
                const isAtBottom = scrollPosition + messageContainer.clientHeight >= messageContainer.scrollHeight - 20;

                data.forEach(message => {
                    const existingMessageElement = messageContainer.querySelector(`.message[data-id="${message.id}"]`);

                    if (!existingMessageElement) {
                        // --- Lógica para crear un NUEVO elemento de mensaje ---
                        const messageElement = document.createElement("div");
                        messageElement.classList.add("message", message.remitente === "paciente" ? "received" : "sent");
                        messageElement.dataset.id = message.id; // Asigna el ID del mensaje

                        const contentDiv = document.createElement("div");
                        contentDiv.classList.add("message-content");

                        const filePrefix = '!fileTypeMessage, '; // Prefijo para identificar mensajes de archivo
                        if (message.mensaje && typeof message.mensaje === 'string' && message.mensaje.startsWith(filePrefix)) {
                            // --- Lógica para mensajes de ARCHIVO ---
                            const fileInfoString = message.mensaje.substring(filePrefix.length);
                            let uuidExt = '', originalName = '', optionalText = '';
                            const firstCommaSpace = fileInfoString.indexOf(', ');

                            if (firstCommaSpace !== -1) {
                                uuidExt = fileInfoString.substring(0, firstCommaSpace);
                                const rest = fileInfoString.substring(firstCommaSpace + 2);
                                const secondCommaSpace = rest.indexOf(', ');
                                if (secondCommaSpace !== -1) {
                                    originalName = rest.substring(0, secondCommaSpace);
                                    optionalText = rest.substring(secondCommaSpace + 2);
                                } else {
                                    originalName = rest;
                                }
                            } else {
                                uuidExt = fileInfoString;
                                originalName = fileInfoString;
                            }

                            let uuid = '', extension = '';
                            const lastDot = uuidExt.lastIndexOf('.');
                            if (lastDot !== -1) {
                                uuid = uuidExt.substring(0, lastDot);
                                extension = uuidExt.substring(lastDot + 1);
                            } else {
                                uuid = uuidExt;
                            }
                            if (!originalName && uuidExt) originalName = uuidExt;

                            const fileDatePart = getTimestampDatePart(message.timestamp);
                            const fileUrl = `/SGH/app/whatsapp_files/${fileDatePart}/${uuidExt}`;

                            if (isImageExtension(extension)) {
                                // Si es una imagen, muestra una previsualización
                                const linkElement = document.createElement('a');
                                linkElement.href = fileUrl;
                                linkElement.target = '_blank';
                                linkElement.title = originalName;
                                linkElement.classList.add('message-file-link', 'message-image-preview-link');

                                const imgElement = document.createElement('img');
                                imgElement.src = fileUrl;
                                imgElement.alt = originalName;
                                imgElement.classList.add('message-image-preview');
                                imgElement.onerror = () => { // Manejo de error si la imagen no carga
                                    console.error("Error cargando imagen:", fileUrl);
                                    linkElement.innerHTML = `<i class="fa-solid fa-image"></i> Error al cargar: ${originalName}`;
                                    linkElement.classList.remove('message-image-preview-link');
                                    linkElement.href = '#'; // Inhabilita el enlace si hay error
                                };
                                linkElement.appendChild(imgElement);
                                contentDiv.appendChild(linkElement);
                            } else {
                                // Si no es imagen, muestra un icono y el nombre del archivo
                                const linkElement = document.createElement('a');
                                linkElement.href = fileUrl;
                                linkElement.download = originalName; // Permite descargar el archivo
                                linkElement.title = originalName;
                                linkElement.classList.add('message-file-link');

                                const iconElement = document.createElement('i');
                                iconElement.classList.add('fa-solid', getFontAwesomeIconClassByExtension(extension), 'message-file-icon');

                                const nameSpan = document.createElement('span');
                                nameSpan.textContent = originalName;
                                nameSpan.classList.add('message-file-name');

                                linkElement.appendChild(iconElement);
                                linkElement.appendChild(nameSpan);
                                contentDiv.appendChild(linkElement);
                            }

                            if (optionalText) {
                                // Si hay texto opcional (caption) para el archivo
                                const textElement = document.createElement('p');
                                textElement.textContent = optionalText;
                                textElement.classList.add('message-file-caption');
                                contentDiv.appendChild(textElement);
                            }
                        } else {
                            // --- Lógica para mensajes de TEXTO normales ---
                            const textElement = document.createElement('p');
                            // Se asume que el backend NO devuelve HTML complejo, solo saltos de línea si es necesario.
                            textElement.innerHTML = message.mensaje; // Usar innerHTML si el backend puede enviar formato básico como <br>
                            textElement.classList.add('message-text');
                            contentDiv.appendChild(textElement);
                        }
                        messageElement.appendChild(contentDiv);

                        // Información del mensaje (hora y estado de envío/lectura)
                        const infoDiv = document.createElement("div");
                        infoDiv.classList.add("message-info");
                        const timeSpan = document.createElement("span");
                        timeSpan.classList.add("message-time");
                        timeSpan.textContent = formatTimestampToHHMM(message.timestamp);
                        infoDiv.appendChild(timeSpan);

                        // --- Lógica para añadir el tick de estado INICIAL (solo para mensajes enviados por el agente) ---
                        if (message.remitente !== 'paciente') {
                            const tickSpan = document.createElement("span");
                            tickSpan.classList.add("message-tick");
                            const tickIcon = document.createElement("i");
                            tickIcon.classList.add("fa-solid"); // Clase base de Font Awesome

                            // Mapeo de estados de la base de datos a clases CSS e iconos de Font Awesome
                            const stateMap = {
                                leido: { cssClass: 'read', iconClass: 'fa-check-double' },
                                entregado: { cssClass: 'delivered', iconClass: 'fa-check-double' },
                                enviado: { cssClass: 'sent', iconClass: 'fa-check' },
                                pendiente_info: { cssClass: 'sent', iconClass: 'fa-check' }, // Tratar igual que 'enviado'
                                pendiente: { cssClass: 'pending', iconClass: 'fa-clock' },
                                error_sin_sid: { cssClass: 'error', iconClass: 'fa-exclamation-triangle' },
                                error_envio_definitivo: { cssClass: 'error', iconClass: 'fa-exclamation-triangle' },
                                error_ack: { cssClass: 'error', iconClass: 'fa-exclamation-triangle' },
                                error_formato_archivo: { cssClass: 'error', iconClass: 'fa-exclamation-triangle' },
                                error_archivo_no_encontrado: { cssClass: 'error', iconClass: 'fa-exclamation-triangle' },
                                error_envio: { cssClass: 'error', iconClass: 'fa-exclamation-triangle' }
                            };
                            const defaultStateInfo = { cssClass: 'pending', iconClass: 'fa-question-circle' }; // Estado por defecto para desconocidos

                            const currentState = message.estado;
                            const stateInfo = stateMap[currentState] || defaultStateInfo;

                            // Si el estado es desconocido, loguea una advertencia
                            if (!stateMap[currentState]) {
                                console.warn("Estado de mensaje desconocido al crear:", currentState, "Mensaje ID:", message.id);
                            }

                            tickIcon.classList.add(stateInfo.iconClass); // Añade el icono específico del estado
                            tickSpan.classList.add(stateInfo.cssClass); // Añade la clase CSS específica del estado

                            tickSpan.appendChild(tickIcon);
                            infoDiv.appendChild(tickSpan);
                        }
                        messageElement.appendChild(infoDiv);
                        messageContainer.appendChild(messageElement); // Añade el mensaje al contenedor

                        // Muestra el remitente si es un mensaje enviado por el agente y el modo admin está activado
                        if (message.remitente != 'paciente' && isAdmin) {
                            const adminElement = document.createElement('div');
                            adminElement.classList.add('remitente-admin');
                            adminElement.innerHTML = `Remitente: ${message.remitente_agente}`;
                            contentDiv.appendChild(adminElement);
                        }

                    } else {
                        // --- Lógica para ACTUALIZAR el estado de un mensaje existente ---
                        // Mapeo de estados para la actualización
                        const statusMap = {
                            leido: { cssClass: 'read', iconClass: 'fa-check-double' },
                            entregado: { cssClass: 'delivered', iconClass: 'fa-check-double' },
                            enviado: { cssClass: 'sent', iconClass: 'fa-check' },
                            pendiente_info: { cssClass: 'sent', iconClass: 'fa-check' },
                            pendiente: { cssClass: 'pending', iconClass: 'fa-clock' },
                            error_sin_sid: { cssClass: 'error', iconClass: 'fa-exclamation-triangle' },
                            error_envio_definitivo: { cssClass: 'error', iconClass: 'fa-exclamation-triangle' },
                            error_ack: { cssClass: 'error', iconClass: 'fa-exclamation-triangle' },
                            error_formato_archivo: { cssClass: 'error', iconClass: 'fa-exclamation-triangle' },
                            error_archivo_no_encontrado: { cssClass: 'error', iconClass: 'fa-exclamation-triangle' },
                            error_envio: { cssClass: 'error', iconClass: 'fa-exclamation-triangle' }
                        };

                        /**
                         * Actualiza el icono y la clase CSS de un "tick" de estado de mensaje.
                         * @param {HTMLElement} tickSpan - El elemento span que contiene el tick (el icono de estado).
                         * @param {string} newStatus - El nuevo estado del mensaje (ej: 'sent', 'delivered', 'read').
                         */
                        function updateMessageTick(tickSpan, newStatus) {
                            if (!tickSpan) {
                                console.error("Error: tickSpan es null. No se puede actualizar el tick.");
                                return;
                            }

                            const statusInfo = statusMap[newStatus] || statusMap['unknown']; // Obtiene la info del estado o un valor por defecto

                            // 1. Remueve todas las clases de estado anteriores del tickSpan
                            Object.values(statusMap).forEach(s => {
                                if (s.cssClass && tickSpan.classList.contains(s.cssClass)) {
                                    tickSpan.classList.remove(s.cssClass);
                                }
                            });

                            // 2. Añade la nueva clase de estado
                            if (statusInfo && statusInfo.cssClass) {
                                tickSpan.classList.add(statusInfo.cssClass);
                            } else {
                                console.warn(`No se encontró cssClass válida para el estado '${newStatus}'.`);
                            }

                            // 3. Actualiza el ícono de Font Awesome
                            if (statusInfo.iconClass) {
                                tickSpan.innerHTML = `<i class="fa-solid ${statusInfo.iconClass}"></i>`;
                            } else {
                                console.warn(`No se encontró iconClass para el estado '${newStatus}'.`);
                            }
                        }

                        // Solo actualiza el tick si el mensaje fue enviado por el agente
                        if (message.remitente !== 'paciente') {
                            const tickSpan = existingMessageElement.querySelector(".message-tick");

                            if (tickSpan) {
                                let currentDOMStatusKey = 'unknown';
                                // Determina el estado actual del mensaje en el DOM
                                for (const key in statusMap) {
                                    if (statusMap.hasOwnProperty(key) && tickSpan.classList.contains(statusMap[key].cssClass)) {
                                        currentDOMStatusKey = key;
                                        break;
                                    }
                                }

                                const newStatusFromDB = message.estado || 'unknown';
                                // Si el estado de la base de datos es diferente al del DOM, actualiza
                                if (newStatusFromDB !== currentDOMStatusKey) {
                                    updateMessageTick(tickSpan, newStatusFromDB);
                                }
                            } else {
                                console.warn(`tickSpan no encontrado para mensaje ID ${message.id}.`);
                            }
                        }
                    }
                });

                // Mantiene el scroll al final si el usuario ya estaba cerca del final
                if (isAtBottom) {
                    setTimeout(() => messageContainer.scrollTop = messageContainer.scrollHeight, 50);
                }
            })
            .catch(error => {
                console.error("Error fetching messages:", error);
                toast("Error al cargar mensajes.", 'error');
            });

        // Marca los mensajes como leídos en la base de datos
        fetch(`api/read_messages.php?chatId=${chatId}&agent=${dni}`) // 'dni' debe ser una variable global con el DNI del agente
            .then(response => {
                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                return response.json();
            })
            .then(data => {
                if (data.error) {
                    console.error("API Error (read_messages):", data.error);
                }
            })
            .catch(error => {
                console.error("Error marking messages as read:", error);
                toast("Error al marcar mensajes como leídos.", 'error');
            });
    }

    /**
     * Abre un chat específico en la interfaz, carga sus mensajes y configura la vista.
     * @param {string} chatId - El ID del chat a abrir.
     * @param {string} chatName - El nombre del chat/paciente.
     * @param {string} estadoChat - El estado actual del chat (ej. 'chatting', 'pendiente').
     * @param {string} numeroPaciente - El número de teléfono del paciente asociado al chat.
     */
    window.openChat = function (chatId, chatName, estadoChat, numeroPaciente) {
        // Detiene el intervalo de mensajes anterior si existe
        if (messageInterval) {
            clearInterval(messageInterval);
            messageInterval = null;
        }
        // Limpia la vista del chat y los paneles laterales
        chatBody.innerHTML = "";
        if (patientDataContainer) {
            patientDataContainer.innerHTML = "";
            patientDataContainer.classList.remove("active");
        }
        if (patientOptionsContainer) {
            patientOptionsContainer.innerHTML = "";
            patientOptionsContainer.classList.remove("active");
        }
        messageInput.value = ""; // Limpia el input de mensaje
        archivosSeleccionados = []; // Limpia la lista de archivos seleccionados
        renderPreview(); // Limpia la previsualización de archivos

        currentChatId = chatId; // Establece el chat ID actual
        window.pacienteNumero = numeroPaciente; // Guarda el número del paciente actual

        // Actualiza la cabecera del chat con el nombre del chat
        chatHeader.innerHTML = "";
        const chatTitle = document.createElement('span');
        chatTitle.textContent = chatName || "Chat";
        chatTitle.style.fontWeight = "bold";
        chatHeader.appendChild(chatTitle);

        // Resalta el chat activo en la lista y quita el badge de no leídos
        document.querySelectorAll('.chat-item.active-chat').forEach(item => item.classList.remove('active-chat'));
        const currentChatItem = document.querySelector(`.chat-item[data-chat-id="${currentChatId}"]`);
        if (currentChatItem) {
            currentChatItem.classList.add('active-chat');
            currentChatItem.classList.remove('unread');
            const unreadBadge = currentChatItem.querySelector('.div3.un');
            if (unreadBadge) {
                unreadBadge.textContent = '0';
                unreadBadge.style.opacity = '0';
                unreadBadge.style.visibility = 'hidden';
            }
        }

        // Habilita los campos de entrada de mensajes y botones
        emojiButton.disabled = false;
        fileUploadLabel.classList.remove("disabled");
        docInput.disabled = false;
        messageInput.disabled = false;
        sendMessageButton.disabled = false;
        messageInput.focus(); // Pone el foco en el input de mensaje

        // --- Lógica para los botones de la cabecera del chat (Reclamar, Datos, Acciones, Cerrar) ---
        const buttonContainer = document.createElement("div");
        buttonContainer.classList.add("chat-header-buttons");

        // Botón "Reclamar" (visible solo si el chat está en estado 'pendiente')
        if (estadoChat === 'pendiente') {
            const buttonReclamar = document.createElement("button");
            buttonReclamar.innerHTML = '<i class="fa-solid fa-hand-holding"></i> Reclamar';
            buttonReclamar.classList.add("btn-tematico", "button-switch", "start");
            buttonReclamar.addEventListener("click", function () {
                fetch('api/modificar_estado_chat.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ chat_id: currentChatId, estado: 'chatting', agente: dni }) // 'dni' debe ser una variable global
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            toast(data.message || "Chat reclamado.", 'success');
                            fetchChats('chatting'); // Actualiza la lista de chats en la pestaña "En curso"
                            selectButton('chatting'); // Selecciona visualmente la pestaña "En curso"
                        } else {
                            toast(data.message || "Error al reclamar chat.", 'error');
                        }
                    })
                    .catch(error => {
                        console.error("Error al reclamar chat:", error);
                        toast("Error de conexión al reclamar.", 'error');
                    });
            });
            buttonContainer.appendChild(buttonReclamar);
        }

        // Botones "Datos" y "Acciones" (visibles solo si el chat está en estado 'chatting')
        if (estadoChat === 'chatting') {
            const buttonDatos = document.createElement("button");
            buttonDatos.innerHTML = '<i class="fa-solid fa-circle-info"></i> Datos';
            buttonDatos.classList.add("btn-tematico", "button-switch", "start");
            buttonDatos.addEventListener("click", function () {
                patientDataContainer.classList.toggle("active"); // Alterna la visibilidad del panel de datos
                patientOptionsContainer.classList.remove("active"); // Oculta el panel de opciones
                igualarAnchoElementoAlDeChats("patientData"); // Ajusta el ancho del panel
                if (patientDataContainer.classList.contains("active")) {
                    // Carga los datos del paciente si el panel está activo y vacío
                    patientDataContainer.innerHTML = "<p>Cargando datos...</p>";
                    fetch(`api/get_patient_data.php`, { // Endpoint para obtener datos del paciente
                        method: "POST",
                        headers: { "Content-Type": "application/json" },
                        body: JSON.stringify({ chat_id: currentChatId }) // Envía el ID del chat
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.error) {
                                console.error("Error al obtener datos del paciente:", data.error);
                                patientDataContainer.innerHTML = `<p class="error-text">Error: ${data.error}</p>`;
                                return;
                            }
                            if (data.telefono) window.pacienteNumero = data.telefono; // Actualiza el número del paciente global
                            // Formatea la fecha de nacimiento
                            const fechaNacimiento = data.fecha_nacimiento ? new Date(data.fecha_nacimiento + 'T00:00:00').toLocaleDateString('es-AR', { timeZone: 'UTC' }) : 'N/A';
                            // Rellena el panel de datos del paciente
                            patientDataContainer.innerHTML = `
                                <button class="btn-green" id="editPatient" style="float: right;"><i class="fa-solid fa-pencil"></i></button>
                                <p><strong>Apellidos:</strong> ${data.apellidos || 'N/A'}</p>
                                <p><strong>Nombres:</strong> ${data.nombres || 'N/A'}</p>
                                <p><strong>Sexo:</strong> ${data.sexo || 'N/A'}</p>
                                <p><strong>Documento:</strong> ${data.tipo_documento || ''} ${data.documento || 'N/A'}</p>
                                <p><strong>Nacimiento:</strong> ${fechaNacimiento}</p>
                                <p><strong>Identidad genero:</strong> ${data.identidad_genero || 'N/A'}</p>
                                <p><strong>Nombre autopercibido:</strong> ${data.nombre_autopercibido || 'N/A'}</p>
                                <p><strong>Provincia:</strong> ${data.provincia || 'N/A'}</p>
                                <p><strong>Partido:</strong> ${data.partido || 'N/A'}</p>
                                <p><strong>Ciudad:</strong> ${data.ciudad || 'N/A'}</p>
                                <p><strong>Calle:</strong> ${data.calle || 'N/A'}</p>
                                <p><strong>Número:</strong> ${data.numero || 'N/A'}</p>
                                <p><strong>Piso:</strong> ${data.piso || 'N/A'}</p>
                                <p><strong>Departamento:</strong> ${data.departamento || 'N/A'}</p>
                                <p><strong>Teléfono:</strong> ${data.telefono ? formatPhoneNumber(data.telefono) : 'N/A'}</p>
                                <p><strong>Email:</strong> ${data.mail || 'N/A'}</p>
                                <p><strong>Obra Social:</strong> ${data.obra_social || 'N/A'}</p>
                            `;

                            console.log(data);

                            // Agrega el event listener al botón de editar paciente
                            document.getElementById('editPatient').addEventListener('click', () => {
                                openEditPatientModal(data);
                            });
                        })
                        .catch(error => {
                            console.error("Error fetch datos paciente:", error);
                            patientDataContainer.innerHTML = `<p class="error-text">Error de conexión al cargar datos.</p>`;
                        });
                }
            });
            buttonContainer.appendChild(buttonDatos);

            const buttonAcciones = document.createElement('button');
            buttonAcciones.innerHTML = '<i class="fa-solid fa-bars"></i> Acciones';
            buttonAcciones.classList.add("btn-tematico", "button-switch");
            buttonAcciones.addEventListener("click", function () {
                patientOptionsContainer.classList.toggle("active"); // Alterna la visibilidad del panel de opciones
                patientDataContainer.classList.remove("active"); // Oculta el panel de datos
                igualarAnchoElementoAlDeChats("patientOptions"); // Ajusta el ancho del panel
                if (patientOptionsContainer.classList.contains("active")) {
                    // Carga las opciones si el panel está activo y vacío
                    if (patientOptionsContainer.innerHTML.trim() === "") {
                        // Botón para mover a Espera
                        const toWait = document.createElement("button");
                        toWait.innerHTML = '<i class="fa-solid fa-hourglass-half"></i> Mover a Espera';
                        toWait.classList.add("btn-yellow", "btn-block");
                        toWait.addEventListener("click", function () {
                            fetch('api/modificar_estado_chat.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ chat_id: currentChatId, estado: 'pendiente', agente: null }) // Agente null para mover a espera
                            })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        toast(data.message || "Chat movido a espera.", 'success');
                                        initializeChatView(); // Cierra la vista actual del chat
                                        fetchChats(document.querySelector(".btn-chat.active")?.id || "chatting"); // Recarga la lista de chats
                                    } else {
                                        toast(data.message || "Error al mover a espera.", 'error');
                                    }
                                }).catch(err => {
                                    console.error("Error mover a espera:", err);
                                    toast("Error de conexión.", 'error');
                                });
                        });

                        // Botón para Finalizar Chat
                        const toEnd = document.createElement("button");
                        toEnd.innerHTML = '<i class="fa-solid fa-circle-check"></i> Finalizar Chat';
                        toEnd.classList.add("btn-red", "btn-block");
                        toEnd.addEventListener("click", function () {
                            fetch('api/modificar_estado_chat.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ chat_id: currentChatId, estado: 'finalizado', agente: dni }) // Agente actual finaliza el chat
                            })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        toast(data.message || "Chat finalizado.", 'success');
                                        initializeChatView(); // Cierra la vista actual del chat
                                        fetchChats(document.querySelector(".btn-chat.active")?.id || "chatting"); // Recarga la lista de chats
                                    } else {
                                        toast(data.message || "Error al finalizar chat.", 'error');
                                    }
                                }).catch(err => {
                                    console.error("Error finalizar chat:", err);
                                    toast("Error de conexión.", 'error');
                                });
                        });

                        // Botón para Derivar Chat
                        const toDerivar = document.createElement("button");
                        toDerivar.innerHTML = '<i class="fa-solid fa-share-from-square"></i> Derivar Chat';
                        toDerivar.classList.add("btn-tematico", "btn-block");
                        toDerivar.addEventListener("click", function () {
                            document.getElementById('back').style.display = "flex"; // Muestra el fondo oscuro del modal
                            document.getElementById('derivar').style.display = "flex"; // Muestra el modal de derivación
                            document.getElementById('id_chat').value = currentChatId; // Pasa el ID del chat actual al formulario de derivación
                            $('#agenteSelect').val(null).trigger('change'); // Resetea el Select2 del agente
                            patientOptionsContainer.classList.remove("active"); // Oculta el panel de opciones
                        });

                        // Añade los botones al contenedor de opciones
                        patientOptionsContainer.appendChild(toWait);
                        patientOptionsContainer.appendChild(toEnd);
                        patientOptionsContainer.appendChild(toDerivar);
                    }
                }
            });
            buttonContainer.appendChild(buttonAcciones);
        }

        // Botón "Cerrar" (siempre visible cuando un chat está abierto)
        const buttonCloseChat = document.createElement("button");
        buttonCloseChat.innerHTML = '<i class="fa-solid fa-xmark"></i> Cerrar';
        buttonCloseChat.classList.add("btn-red", "button-switch", "end");
        buttonCloseChat.addEventListener("click", () => {
            initializeChatView(); // Restablece la vista del chat al estado inicial
        });
        buttonContainer.appendChild(buttonCloseChat);

        chatHeader.appendChild(buttonContainer); // Añade el contenedor de botones a la cabecera
        // --- Fin de la lógica para botones de la cabecera ---

        // Carga los mensajes iniciales del chat y establece un intervalo para actualizarlos
        fetchMessages(chatId);
        messageInterval = setInterval(() => {
            if (currentChatId) { // Solo busca mensajes si hay un chat activo
                fetchMessages(currentChatId);
            }
        }, 3000); // Actualiza cada 3 segundos
    }

    /**
     * Abre el modal de edición de paciente y rellena el formulario con los datos.
     * @param {Object} patientData - Objeto con los datos del paciente.
     */
    function openEditPatientModal(patientData) {
        // Rellenar el formulario con los datos del paciente
        document.getElementById('edit_patient_id').value = patientData.id;
        document.getElementById('edit_apellidos').value = patientData.apellidos || '';
        document.getElementById('edit_nombres').value = patientData.nombres || '';
        // Selecciona el input radio correspondiente al sexo
        if (patientData.sexo === "Masculino") {
            document.getElementById('edit_sexo_masculino').checked = true;
        } else if (patientData.sexo === "Femenino") {
            document.getElementById('edit_sexo_femenino').checked = true;
        } else if (patientData.sexo === "X") {
            document.getElementById('edit_sexo_x').checked = true;
        } else {
            // Si no hay valor, deselecciona todos
            document.getElementById('edit_sexo_masculino').checked = false;
            document.getElementById('edit_sexo_femenino').checked = false;
            document.getElementById('edit_sexo_x').checked = false;
        }
        document.getElementById('edit_tipo_documento').value = patientData.tipo_documento || '';
        document.getElementById('edit_documento').value = patientData.documento || '';
        // Formatear la fecha para el input type="date"
        document.getElementById('edit_fecha_nacimiento').value = patientData.fecha_nacimiento || '';
        document.getElementById('edit_identidad_genero').value = patientData.identidad_genero || '';
        document.getElementById('edit_nombre_autopercibido').value = patientData.nombre_autopercibido || '';
        document.getElementById('edit_provincia').value = patientData.provincia || '';
        document.getElementById('edit_partido').value = patientData.partido || '';
        document.getElementById('edit_ciudad').value = patientData.ciudad || '';
        document.getElementById('edit_calle').value = patientData.calle || '';
        document.getElementById('edit_numero').value = patientData.numero || '';
        document.getElementById('edit_piso').value = patientData.piso || '';
        document.getElementById('edit_departamento').value = patientData.departamento || '';
        document.getElementById('edit_telefono').value = patientData.telefono ? patientData.telefono.replace("@c.us", "") : ''; // Eliminar @c.us
        document.getElementById('edit_mail').value = patientData.mail || '';
        document.getElementById('edit_obra_social').value = patientData.obra_social || '';

        // Mostrar el modal y el fondo oscuro
        editPatientModal.style.display = 'flex';
        document.getElementById('back').style.display = 'flex';
    }

    // Event listener para cerrar el modal de edición de paciente
    if (closeEditPatientModal) {
        closeEditPatientModal.addEventListener('click', () => {
            editPatientModal.style.display = 'none';
            document.getElementById('back').style.display = 'none';
        });
    }

    // Manejo del envío del formulario de edición de paciente
    if (editPatientForm) {
        editPatientForm.addEventListener('submit', function (event) {
            event.preventDefault(); // Evita el envío tradicional del formulario

            const formData = new FormData(this);
            const jsonData = {};
            formData.forEach((value, key) => {
                jsonData[key] = value;
            });

            // Añadir el sufijo @c.us al número de teléfono si no lo tiene
            if (jsonData.telefono && !jsonData.telefono.endsWith('@c.us')) {
                jsonData.telefono += '@c.us';
            }

            fetch('api/update_patient_data.php', { // Endpoint para actualizar datos del paciente
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(jsonData)
            })
                .then(async response => {
                    const contentType = response.headers.get("Content-Type");
                    if (contentType && contentType.includes("application/json")) {
                        const data = await response.json();
                        if (data.success) {
                            toast(data.message || "Datos del paciente actualizados.", 'success');
                            editPatientModal.style.display = 'none';
                            document.getElementById('back').style.display = 'none';
                            // Recargar los datos del paciente en el panel lateral
                            if (currentChatId) {
                                // Forzar la recarga de los datos del paciente en el panel lateral
                                patientDataContainer.innerHTML = ""; // Limpiar para forzar recarga
                                fetch(`api/get_patient_data.php`, { // Endpoint para obtener datos del paciente
                        method: "POST",
                        headers: { "Content-Type": "application/json" },
                        body: JSON.stringify({ chat_id: currentChatId }) // Envía el ID del chat
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.error) {
                                console.error("Error al obtener datos del paciente:", data.error);
                                patientDataContainer.innerHTML = `<p class="error-text">Error: ${data.error}</p>`;
                                return;
                            }
                            if (data.telefono) window.pacienteNumero = data.telefono; // Actualiza el número del paciente global
                            // Formatea la fecha de nacimiento
                            const fechaNacimiento = data.fecha_nacimiento ? new Date(data.fecha_nacimiento + 'T00:00:00').toLocaleDateString('es-AR', { timeZone: 'UTC' }) : 'N/A';
                            // Rellena el panel de datos del paciente
                            patientDataContainer.innerHTML = `
                                <button class="btn-green" id="editPatient" style="float: right;"><i class="fa-solid fa-pencil"></i></button>
                                <p><strong>Apellidos:</strong> ${data.apellidos || 'N/A'}</p>
                                <p><strong>Nombres:</strong> ${data.nombres || 'N/A'}</p>
                                <p><strong>Sexo:</strong> ${data.sexo || 'N/A'}</p>
                                <p><strong>Documento:</strong> ${data.tipo_documento || ''} ${data.documento || 'N/A'}</p>
                                <p><strong>Nacimiento:</strong> ${fechaNacimiento}</p>
                                <p><strong>Identidad genero:</strong> ${data.identidad_genero || 'N/A'}</p>
                                <p><strong>Nombre autopercibido:</strong> ${data.nombre_autopercibido || 'N/A'}</p>
                                <p><strong>Provincia:</strong> ${data.provincia || 'N/A'}</p>
                                <p><strong>Partido:</strong> ${data.partido || 'N/A'}</p>
                                <p><strong>Ciudad:</strong> ${data.ciudad || 'N/A'}</p>
                                <p><strong>Calle:</strong> ${data.calle || 'N/A'}</p>
                                <p><strong>Número:</strong> ${data.numero || 'N/A'}</p>
                                <p><strong>Piso:</strong> ${data.piso || 'N/A'}</p>
                                <p><strong>Departamento:</strong> ${data.departamento || 'N/A'}</p>
                                <p><strong>Teléfono:</strong> ${data.telefono ? formatPhoneNumber(data.telefono) : 'N/A'}</p>
                                <p><strong>Email:</strong> ${data.mail || 'N/A'}</p>
                                <p><strong>Obra Social:</strong> ${data.obra_social || 'N/A'}</p>
                            `;

                            console.log(data);

                            // Agrega el event listener al botón de editar paciente
                            document.getElementById('editPatient').addEventListener('click', () => {
                                openEditPatientModal(data);
                            });
                        })
                        .catch(error => {
                            console.error("Error fetch datos paciente:", error);
                            patientDataContainer.innerHTML = `<p class="error-text">Error de conexión al cargar datos.</p>`;
                        });
                                document.getElementById("patientData").classList.add("active"); // Asegurar que el panel esté activo
                                document.getElementById("patientData").click(); // Simular clic para recargar
                            }
                        } else {
                            toast(data.message || "Error al actualizar datos del paciente.", 'error');
                        }
                    } else {
                        throw new Error("Respuesta no válida del servidor.");
                    }
                })
                .catch(err => {
                    console.error("Error al actualizar datos del paciente:", err);
                    toast("Error al comunicarse con el servidor para actualizar datos.", 'error');
                });
        });
    }

    /**
     * Envía un mensaje de texto y/o archivos al chat activo.
     * Gestiona la previsualización optimista del mensaje y el envío AJAX.
     */
    function sendMessage() {
        const messageText = messageInput.value.trim(); // Obtiene el texto del input

        // Si no hay texto ni archivos seleccionados, no hace nada
        if (!messageText && archivosSeleccionados.length === 0) {
            return;
        }

        // Valida que el número del paciente y el ID del chat estén disponibles
        if (window.pacienteNumero === null || !currentChatId) {
            console.error("Error: Número del paciente o ID del chat no disponible.");
            toast("Error: No se puede enviar el mensaje. Reabre el chat.", "error");
            return;
        }

        // --- Lógica para enviar solo MENSAJES DE TEXTO (sin archivos) ---
        if (archivosSeleccionados.length === 0) {
            const tempId = `temp_${Date.now()}`; // Genera un ID temporal para el mensaje (Optimistic UI)

            // Añade el mensaje al DOM con estado 'pendiente' (Optimistic UI Update)
            const messageElement = document.createElement("div");
            messageElement.classList.add("message", "sent"); // Mensaje enviado por el agente
            messageElement.dataset.id = tempId; // Asigna el ID temporal

            const contentDiv = document.createElement("div");
            contentDiv.classList.add("message-content");
            const textElement = document.createElement('p');
            textElement.textContent = messageText;
            textElement.classList.add('message-text');
            contentDiv.appendChild(textElement);
            messageElement.appendChild(contentDiv);

            const infoDiv = document.createElement("div");
            infoDiv.classList.add("message-info");
            const timeSpan = document.createElement("span");
            timeSpan.classList.add("message-time");
            timeSpan.textContent = formatTimestampToHHMM(new Date().toISOString()); // Hora actual
            infoDiv.appendChild(timeSpan);

            const tickSpan = document.createElement("span");
            tickSpan.classList.add("message-tick", "pending"); // Estado inicial PENDIENTE
            const tickIcon = document.createElement("i");
            tickIcon.classList.add("fa-solid", "fa-clock"); // Icono de reloj para pendiente
            tickSpan.appendChild(tickIcon);
            infoDiv.appendChild(tickSpan);
            messageElement.appendChild(infoDiv);

            chatBody.appendChild(messageElement); // Añade el mensaje al cuerpo del chat
            chatBody.scrollTop = chatBody.scrollHeight; // Hace scroll al final
            const originalMessageInputValue = messageInput.value; // Guarda el valor original por si falla el envío
            messageInput.value = ""; // Limpia el input de mensaje

            // Petición Fetch para enviar el mensaje al backend
            fetch(`api/send_message.php`, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    numero: window.pacienteNumero,
                    mensaje: messageText,
                    chat_id: currentChatId,
                    remitente: dni // DNI del agente actual (variable global)
                })
            })
                .then(response => response.json())
                .then(data => {
                    const sentMessageElement = chatBody.querySelector(`.message[data-id="${tempId}"]`); // Encuentra el mensaje temporal en el DOM

                    if (data.success && data.message_id) { // Si el envío fue exitoso y se recibió un ID real
                        if (sentMessageElement) {
                            sentMessageElement.dataset.id = data.message_id; // Actualiza el ID temporal al ID real
                            const tick = sentMessageElement.querySelector(".message-tick");
                            if (tick) {
                                tick.classList.remove("pending");
                                tick.classList.add("sent"); // O el estado que devuelva el backend como 'enviado'
                                tick.innerHTML = '<i class="fa-solid fa-check"></i>'; // Cambia el icono a un check
                            }
                        }
                    } else {
                        console.error("Error al enviar mensaje:", data.error || 'Error desconocido del servidor');
                        toast(data.error || "Error al enviar mensaje.", "error");
                        if (sentMessageElement) { // Si falla, marca el mensaje temporal como error
                            const tick = sentMessageElement.querySelector(".message-tick");
                            if (tick) {
                                tick.classList.remove("pending");
                                tick.classList.add("error"); // Clase para estado de error
                                tick.innerHTML = '<i class="fa-solid fa-exclamation-circle"></i>'; // Icono de error
                            }
                        }
                        messageInput.value = originalMessageInputValue; // Restaura el mensaje en el input
                    }
                })
                .catch(error => {
                    console.error("Error en la conexión al enviar mensaje:", error);
                    toast("Error de conexión al enviar mensaje.", "error");
                    const sentMessageElement = chatBody.querySelector(`.message[data-id="${tempId}"]`);
                    if (sentMessageElement) {
                        const tick = sentMessageElement.querySelector(".message-tick");
                        if (tick) {
                            tick.classList.remove("pending");
                            tick.classList.add("error");
                            tick.innerHTML = '<i class="fa-solid fa-exclamation-circle"></i>';
                        }
                    }
                    messageInput.value = originalMessageInputValue; // Restaura el mensaje
                });
        }
        // --- Lógica para enviar ARCHIVOS (con o sin texto) ---
        else {
            const formData = new FormData(); // Usa FormData para enviar archivos
            formData.append("numero", window.pacienteNumero);
            formData.append("chat_id", currentChatId);
            formData.append("remitente", dni);

            if (messageText) {
                formData.append("mensaje", messageText); // Adjunta el texto si existe
            }

            // Adjunta cada archivo seleccionado al FormData
            archivosSeleccionados.forEach((file, index) => {
                formData.append(`archivo_${index}`, file);
            });

            const xhr = new XMLHttpRequest(); // Usa XMLHttpRequest para controlar el progreso
            xhr.open("POST", "api/send_files.php", true);

            // Evento para monitorear el progreso de la subida
            xhr.upload.onprogress = function (event) {
                if (event.lengthComputable) {
                    const porcentaje = Math.round((event.loaded / event.total) * 100);
                    // console.log(`Progreso de subida: ${porcentaje}%`); // Puedes mostrar esto en una barra de progreso
                }
            };

            // Evento cuando la petición se completa
            xhr.onload = function () {
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            messageInput.value = ""; // Limpia el input de texto
                            docInput.value = ""; // Limpia el input de archivo
                            archivosSeleccionados = []; // Limpia la lista de archivos
                            renderPreview(); // Limpia la previsualización
                            fetchMessages(currentChatId); // Recarga los mensajes para mostrar el archivo enviado
                            setTimeout(() => chatBody.scrollTop = chatBody.scrollHeight, 300); // Scroll al final
                        } else {
                            console.error("Error al enviar archivos:", response.error || (response.errors ? response.errors.join(', ') : 'Error desconocido.'));
                            toast(response.error || (response.errors ? response.errors.join(', ') : "Error al enviar archivos."), "error");
                        }
                    } catch (e) {
                        console.error("Error al parsear respuesta JSON de send_files.php:", e, xhr.responseText);
                        toast("Error en la respuesta del servidor al enviar archivos.", "error");
                    }
                } else {
                    console.error("Error en el servidor al enviar archivos:", xhr.status, xhr.responseText);
                    toast(`Error del servidor (${xhr.status}) al enviar archivos.`, "error");
                }
            };

            // Evento si hay un error de red
            xhr.onerror = function () {
                console.error("Error de red al enviar archivos.");
                toast("Error de red al intentar enviar archivos.", "error");
            };

            xhr.send(formData); // Envía el FormData
        }
    }

    // Event listeners para el envío de mensajes
    sendMessageButton.addEventListener("click", sendMessage); // Al hacer clic en el botón de enviar
    messageInput.addEventListener("keydown", function (event) {
        if (event.key === "Enter" && !event.shiftKey) { // Al presionar Enter (sin Shift)
            event.preventDefault(); // Evita el salto de línea por defecto
            sendMessage(); // Envía el mensaje
        }
    });

    /**
     * Selecciona visualmente un botón de estado de chat y actualiza la URL.
     * @param {string} estado - El ID del botón a seleccionar (ej. 'chatting', 'pendiente').
     */
    window.selectButton = function (estado) {
        buttons.forEach(btn => btn.classList.remove("active")); // Quita la clase 'active' de todos los botones
        const selectedButton = document.getElementById(estado);
        if (selectedButton) selectedButton.classList.add("active"); // Añade 'active' al botón seleccionado

        // Actualiza la URL del navegador sin recargar la página
        const newUrl = new URL(window.location);
        newUrl.searchParams.set("estado", estado); // Añade o actualiza el parámetro 'estado'
        window.history.pushState({ path: newUrl.href }, "", newUrl.href); // Empuja el nuevo estado al historial
    }

    /**
     * Obtiene y muestra la lista de chats para un estado dado desde el backend.
     * @param {string} [estado] - El estado de los chats a obtener (ej: 'chatting', 'pendiente').
     * Si no se proporciona, usa el estado del botón activo o "chatting" por defecto.
     */
    window.fetchChats = function (estado) {
        if (!estado) {
            estado = document.querySelector(".btn-chat.active")?.id || "chatting"; // Obtiene el estado del botón activo o usa "chatting"
        }

        // Construye la URL de la API, incluyendo el modo admin si está activado
        const apiUrl = isAdmin ? `api/get_chats.php?estado=${estado}&adminMode=true` : `api/get_chats.php?estado=${estado}&dni=${dni}`;

        fetch(apiUrl)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    console.error("Error en API get_chats:", data.error);
                    console.warn(data)
                    chatList.innerHTML = `<p class="error-text">${data.error}</p>`;
                    return;
                }

                const chats = Array.isArray(data) ? data : []; // Asegura que 'data' sea un array
                chatList.innerHTML = ""; // Limpia la lista actual de chats

                if (chats.length === 0) {
                    chatList.innerHTML = `<p class="no-chats-message">No hay chats en estado "${estado}".</p>`;
                } else {
                    chats.forEach(chat => {
                        const chatItem = document.createElement("div");
                        chatItem.classList.add("chat-item");
                        const unreadCount = parseInt(chat.unread_messages_count) || 0;

                        // Marca el chat como "no leído" si tiene mensajes pendientes y no es el chat activo
                        if (unreadCount > 0 && chat.id !== currentChatId) {
                            chatItem.classList.add("unread");
                        }
                        // Marca el chat como "activo" si es el chat actualmente abierto
                        if (chat.id === currentChatId) {
                            chatItem.classList.add("active-chat");
                        }

                        // Almacena datos del chat en atributos `data-` para fácil acceso
                        chatItem.dataset.chatId = chat.id;
                        chatItem.dataset.chatName = chat.nombre_paciente || 'Desconocido';
                        chatItem.dataset.chatNumero = chat.numero; // Número del paciente
                        chatItem.dataset.chatEstado = estado; // Estado actual del chat

                        // URL de la imagen de perfil (con fallback si no hay)
                        const profilePic = chat.profile_pic || 'https://i.pinimg.com/236x/d9/d8/8e/d9d88e3d1f74e2b8ced3df051cecb81d.jpg';

                        // Formatea la fecha de cierre si el chat está finalizado
                        let fechaCierre = chat.fecha_cierre ? new Date(chat.fecha_cierre).toLocaleString('es-AR', { timeZone: 'UTC' }) : '';

                        // Rellena el HTML del elemento de chat
                        chatItem.innerHTML = `
                            <div class="div1">
                                <img src="${profilePic}" alt="Perfil"
                                    onerror="this.onerror=null; this.src='https://i.pinimg.com/236x/d9/d8/8e/d9d88e3d1f74e2b8ced3df051cecb81d.jpg';">
                            </div>
                            <div class="div2">
                                <b>${chat.nombre_paciente || 'Cargando...'}</b>
                                <p>${chat.numero ? formatPhoneNumber(chat.numero) : 'Número Desconocido'}</p>
                                <p>${chat.estado === 'finalizado' ? fechaCierre : ''}</p>
                                <p>${isAdmin === true && chat.estado !== 'pendiente' ? chat.nombre_agente : ''}</p>
                            </div>
                            <b class="div3 un" style="${(unreadCount === 0 || chat.id === currentChatId) ? 'opacity: 0; display: none;' : ''}">${unreadCount}</b>
                        `;

                        // Añade un event listener para abrir el chat al hacer clic
                        chatItem.addEventListener("click", () => {
                            openChat(
                                chatItem.dataset.chatId,
                                chatItem.dataset.chatName,
                                chatItem.dataset.chatEstado,
                                chatItem.dataset.chatNumero
                            );
                        });
                        chatList.appendChild(chatItem); // Añade el chat a la lista
                    });
                    filterChatList(); // Ejecuta el filtrado después de cargar todos los chats
                }
            })
            .catch(error => {
                console.error("Error al obtener chats:", error);
                chatList.innerHTML = `<p class="error-text">Error de conexión al cargar chats.</p>`;
            });
    }

    // Carga el estado inicial de la URL o "chatting" por defecto al cargar la página
    const params = new URLSearchParams(window.location.search);
    const estadoInicial = params.get("estado") || "chatting";

    selectButton(estadoInicial); // Selecciona el botón de estado inicial
    fetchChats(estadoInicial); // Carga los chats del estado inicial

    // Event listeners para los botones de filtro de estado
    buttons.forEach(button => {
        button.addEventListener("click", function () {
            const estado = this.id;
            if (currentChatId) { // Si hay un chat abierto, lo cierra antes de cambiar de pestaña
                initializeChatView();
            }
            selectButton(estado); // Selecciona el botón
            fetchChats(estado); // Carga los chats del nuevo estado
        });
    });

    // Maneja la navegación del historial del navegador (botones atrás/adelante)
    window.addEventListener("popstate", function (event) {
        const newParams = new URLSearchParams(window.location.search);
        const newEstado = newParams.get("estado") || "chatting";
        if (currentChatId) {
            initializeChatView();
        }
        selectButton(newEstado);
        fetchChats(newEstado);
    });

    // Intervalo para recargar la lista de chats periódicamente (polling)
    setInterval(() => {
        const isModalOpen = document.getElementById('back').style.display === 'flex'; // Verifica si hay modales abiertos
        if (!isModalOpen) { // Solo recarga si no hay modales activos
            const estadoActivo = document.querySelector(".btn-chat.active")?.id || estadoInicial;
            fetchChats(estadoActivo);
        }
    }, 10000); // Actualiza cada 10 segundos
});


// Manejo de formularios de "Nuevo Chat" (Contacto, Paciente, Número)
$(document).ready(function () {
    $("form#newContacto, form#newPaciente, form#newNumero").submit(function (event) {
        event.preventDefault(); // Evita el envío tradicional del formulario
        var form = $(this);
        var formData = new FormData(form[0]); // Crea un objeto FormData para recolectar los datos del formulario

        // Valida el campo "Otro país" si está visible
        const otherCountryInput = document.getElementById('otherCountry');
        if (otherCountryInput && otherCountryInput.style.display === 'block' && !otherCountryInput.value) {
            toast('Debe ingresar el código de país.', 'error');
            return; // Detiene el envío si la validación falla
        }
        if (otherCountryInput && otherCountryInput.style.display === 'block' && otherCountryInput.value) {
            formData.set('country', otherCountryInput.value); // Asegura que el valor de "otro país" se envíe
        }

        // Envía el formulario vía AJAX
        $.ajax({
            url: form.attr("action"), // URL de la API (ej. 'api/iniciar_chat.php')
            type: "POST",
            data: formData,
            processData: false, // No procesar los datos (necesario para FormData)
            contentType: false, // No establecer el tipo de contenido (necesario para FormData)
            dataType: 'json', // Espera una respuesta JSON
            success: function (response) {
                if (response && response.success && response.chat) {
                    toast(response.message || "Iniciando chat...", "success", 1500);
                    $('#back').css('display', 'none'); // Oculta el fondo del modal
                    $('#newChat').css('display', 'none'); // Oculta el modal
                    form[0].reset(); // Limpia el formulario
                    // Abre el chat recién creado en la interfaz
                    openChat(response.chat.id, response.chat.nombre_paciente, 'chatting', response.chat.numero);
                    selectButton('chatting'); // Selecciona la pestaña "En curso"
                    fetchChats('chatting'); // Recarga la lista de chats para actualizarla
                } else {
                    toast(response.message || "Error al iniciar el chat.", "error");
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.error("Error AJAX al iniciar chat:", textStatus, errorThrown, jqXHR.responseText);
                toast("Error de conexión al iniciar chat.", "error");
            }
        });
    });
});

// Lógica para sugerencias de comandos (autocompletado de comandos con '/')
const commandInput = document.getElementById("messageInput"); // Input donde se escriben los mensajes/comandos
const suggestionsList = document.getElementById("sugerencias"); // Lista donde se muestran las sugerencias
let selectedSuggestionIndex = -1; // Índice de la sugerencia actualmente seleccionada
let filteredSuggestions = []; // Sugerencias filtradas

// Asegura que los elementos y la caché de comandos existan antes de añadir listeners
if (commandInput && suggestionsList && typeof comandosCache !== 'undefined') {
    commandInput.addEventListener("input", (e) => {
        const value = e.target.value;
        if (value.startsWith("/")) { // Solo muestra sugerencias si el texto empieza con '/'
            const searchTerm = value.slice(1).toLowerCase(); // Obtiene el término de búsqueda sin la '/'
            // Filtra los comandos de la caché que coincidan con el término de búsqueda
            filteredSuggestions = comandosCache.filter(c => c.comando.toLowerCase().startsWith(searchTerm));

            if (comandosCache.length === 0) { // Si no hay comandos definidos en la caché
                suggestionsList.style.display = "none";
                return;
            }

            if (filteredSuggestions.length > 0) {
                // Rellena la lista de sugerencias con los comandos filtrados
                suggestionsList.innerHTML = filteredSuggestions.map((c, i) =>
                    `<li data-index="${i}" title="${c.texto}" style="cursor:pointer; padding: 5px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">/${c.comando} - ${c.texto.substring(0, 50)}${c.texto.length > 50 ? '...' : ''}</li>`
                ).join("");

                // Posiciona la lista de sugerencias encima del input de mensaje
                const inputRect = commandInput.getBoundingClientRect();
                const chatFooterRect = commandInput.closest('.chat-footer').getBoundingClientRect();

                suggestionsList.style.position = 'absolute';
                suggestionsList.style.bottom = `${chatFooterRect.height}px`; // Posicionar encima del footer
                suggestionsList.style.left = `0px`;
                suggestionsList.style.width = `${inputRect.width}px`;
                suggestionsList.style.display = "block";
                selectedSuggestionIndex = -1; // Resetea la selección al filtrar
            } else {
                suggestionsList.style.display = "none"; // Oculta si no hay sugerencias
            }
        } else {
            suggestionsList.style.display = "none"; // Oculta si el texto no empieza con '/'
        }
    });

    // Manejo de teclado para navegar y seleccionar sugerencias
    commandInput.addEventListener("keydown", (e) => {
        const items = suggestionsList.querySelectorAll("li");
        if (suggestionsList.style.display === "block" && items.length > 0) {
            if (e.key === "ArrowDown") { // Flecha abajo: siguiente sugerencia
                e.preventDefault();
                selectedSuggestionIndex = (selectedSuggestionIndex + 1) % items.length;
                items[selectedSuggestionIndex].scrollIntoView({ block: 'nearest' }); // Scroll para que sea visible
            } else if (e.key === "ArrowUp") { // Flecha arriba: sugerencia anterior
                e.preventDefault();
                selectedSuggestionIndex = (selectedSuggestionIndex - 1 + items.length) % items.length;
                items[selectedSuggestionIndex].scrollIntoView({ block: 'nearest' });
            } else if (e.key === "Enter" || e.key === "Tab") { // Enter o Tab: selecciona la sugerencia
                if (selectedSuggestionIndex >= 0) {
                    e.preventDefault();
                    commandInput.value = filteredSuggestions[selectedSuggestionIndex].texto; // Rellena el input con el texto completo
                    suggestionsList.style.display = "none"; // Oculta la lista
                    commandInput.focus(); // Devuelve el foco al input
                }
            } else if (e.key === "Escape") { // Escape: oculta la lista de sugerencias
                suggestionsList.style.display = "none";
                return;
            }

            // Resalta visualmente la sugerencia seleccionada
            items.forEach((el, i) => {
                el.style.background = i === selectedSuggestionIndex ? "#f0f0f0" : "";
            });
        }
    });

    // Manejo de clic en las sugerencias
    suggestionsList.addEventListener("click", (e) => {
        const targetLi = e.target.closest('li'); // Encuentra el <li> más cercano al clic
        if (targetLi) {
            const index = targetLi.getAttribute("data-index");
            if (index !== null && filteredSuggestions[index]) {
                commandInput.value = filteredSuggestions[index].texto; // Rellena el input con el texto completo
                suggestionsList.style.display = "none";
                commandInput.focus();
            }
        }
    });
}

// --- SECCIÓN DE MANEJO DE ARCHIVOS ---

/**
 * Obtiene la clase de icono de Font Awesome basada en el tipo MIME de un archivo.
 * @param {string} type - El tipo MIME del archivo (ej. "image/png", "application/pdf").
 * @returns {string} La clase de Font Awesome correspondiente (ej. "fa-file-image").
 */
function getIconByType(type) {
    if (!type) return "fa-file";
    if (type.includes("pdf")) return "fa-file-pdf";
    if (type.includes("word")) return "fa-file-word";
    if (type.includes("spreadsheetml") || type.includes("excel")) return "fa-file-excel";
    if (type.includes("presentationml") || type.includes("powerpoint")) return "fa-file-powerpoint";
    if (type.startsWith("image/")) return "fa-file-image";
    if (type.startsWith("audio/")) return "fa-file-audio";
    if (type.startsWith("video/")) return "fa-file-video";
    if (type.includes("zip") || type.includes("compressed")) return "fa-file-archive";
    if (type.startsWith("text/")) return "fa-file-alt";
    return "fa-file";
}

/**
 * Renderiza la previsualización de los archivos seleccionados en el área designada.
 * Muestra miniaturas para imágenes y iconos para otros tipos de archivos.
 */
function renderPreview() {
    const localFilePreview = document.getElementById("filePreview");
    if (!localFilePreview) return;

    localFilePreview.innerHTML = ""; // Limpia la previsualización actual

    if (archivosSeleccionados.length === 0) {
        localFilePreview.style.display = "none"; // Oculta si no hay archivos
        return;
    }

    localFilePreview.style.display = "flex"; // Muestra el contenedor de previsualización

    archivosSeleccionados.forEach((file, index) => {
        const item = document.createElement("div");
        item.className = "preview-item";
        item.title = file.name; // Muestra el nombre completo del archivo al pasar el mouse

        // Botón para eliminar el archivo de la previsualización
        const removeBtn = document.createElement("button");
        removeBtn.className = "remove-preview";
        removeBtn.innerHTML = "&times;"; // Símbolo de "x"
        removeBtn.onclick = (e) => {
            e.stopPropagation();
            archivosSeleccionados.splice(index, 1); // Elimina el archivo del array
            if (docInput) docInput.value = ""; // Resetea el input de archivo (para permitir seleccionar el mismo archivo de nuevo)
            renderPreview(); // Vuelve a renderizar la previsualización
        };
        item.appendChild(removeBtn);

        if (file.type.startsWith("image/")) {
            // Si es una imagen, crea una miniatura
            const img = document.createElement("img");
            img.src = URL.createObjectURL(file); // Crea una URL temporal para la imagen
            img.onload = () => URL.revokeObjectURL(img.src); // Libera la URL cuando la imagen ha cargado
            item.appendChild(img);
        } else {
            // Para otros tipos de archivos, muestra un icono y el nombre
            const container = document.createElement("div");
            container.style.display = "flex";
            container.style.flexDirection = "column";
            container.style.alignItems = "center";
            container.style.justifyContent = "center";
            container.style.textAlign = "center";

            const icon = document.createElement("i");
            icon.className = `fa-solid ${getIconByType(file.type)} preview-icon`;
            container.appendChild(icon);

            const fileName = document.createElement("small");
            const displayName = file.name.length > 15 ? file.name.slice(0, 12) + "..." : file.name;
            fileName.textContent = displayName;
            fileName.style.fontSize = "0.7rem"; // Usar rem para tamaño de fuente
            fileName.style.textAlign = "center";
            fileName.style.marginTop = "0.2rem"; // Usar rem
            fileName.style.wordBreak = "break-all";
            container.appendChild(fileName);

            item.appendChild(container);
        }
        localFilePreview.appendChild(item); // Añade el elemento de previsualización al contenedor
    });
}

// Maneja la selección de archivos a través del input de tipo "file"
if (docInput) {
    docInput.addEventListener("change", function () {
        archivosSeleccionados = [...this.files]; // Convierte FileList a Array
        renderPreview(); // Renderiza la previsualización
    });
}

// Maneja archivos pegados directamente en el textarea de mensajes
if (messageInput) {
    messageInput.addEventListener("paste", function (e) {
        const items = [...(e.clipboardData || window.clipboardData).items]; // Obtiene los elementos del portapapeles
        const filesPasted = items
            .filter(item => item.kind === "file") // Filtra solo los que son archivos
            .map(item => item.getAsFile()); // Obtiene el objeto File

        if (filesPasted.length > 0) {
            e.preventDefault(); // Evita el comportamiento por defecto de pegar
            archivosSeleccionados.push(...filesPasted); // Añade los archivos al array de seleccionados
            renderPreview(); // Renderiza la previsualización
            toast(`${filesPasted.length} archivo(s) pegado(s) y listo(s) para enviar.`, 'info');
        }
    });
}

// Maneja el cambio del modo administrador (checkbox)
document.getElementById('adm_mode').addEventListener('change', function () {
    isAdmin = this.checked; // Actualiza la variable global isAdmin
    console.log(`[ADMIN MODE] ${isAdmin ? 'ON' : 'OFF'}`);
    fetchChats(); // Recarga la lista de chats con el nuevo modo admin
});

// Muestra el modal para gestionar contactos
document.getElementById('contactButton').addEventListener('click', function () {
    document.getElementById('contactDiv').style.display = 'flex';
    document.getElementById('back').style.display = 'flex';
});

/**
 * Rellena el formulario de edición de contacto con los datos proporcionados.
 * @param {string} id - ID del contacto.
 * @param {string} nombre - Nombre del contacto.
 * @param {string} telefono - Número de teléfono del contacto.
 */
function editContact(id, nombre, telefono) {
    document.getElementById('id_contact').value = id;
    document.getElementById('editNombre').value = nombre;
    document.getElementById('editTelefono').value = telefono;
    document.getElementById('editContact').style.display = 'flex'; // Muestra el formulario de edición
    document.getElementById('newContact').style.display = 'none'; // Oculta el formulario de nuevo contacto
}

/**
 * Cambia el estado (activo/inactivo) de un contacto.
 * @param {string} id - ID del contacto a modificar.
 * @param {string} status - Estado actual del contacto ('activo' o 'inactivo').
 */
function toggleContactStatus(id, status) {
    const newStatus = status === 'activo' ? 'inactivo' : 'activo'; // Determina el nuevo estado

    fetch('controllers/newContact.php', { // Petición a la API para actualizar el estado
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            id_contact: id,
            status: newStatus
        })
    })
    .then(async response => {
        const contentType = response.headers.get("Content-Type");
        if (contentType && contentType.includes("application/json")) {
            const data = await response.json();
            if (data.success) {
                toast(data.message || "Estado actualizado.", 'success');
                location.reload(); // Recarga la página para ver los cambios reflejados
            } else {
                toast(data.message || "Error al actualizar estado.", 'error');
            }
        } else {
            throw new Error("Respuesta no válida del servidor.");
        }
    })
    .catch(err => {
        console.error("Error al modificar estado de contacto:", err);
        toast("Error al comunicarse con el servidor.", 'error');
    });
}

// Muestra el modal para gestionar comandos
document.getElementById('commandosButton').addEventListener('click', function () {
    document.getElementById('comandosDiv').style.display = 'flex';
    document.getElementById('back').style.display = 'flex';
});

/**
 * Rellena el formulario de edición de comando con los datos proporcionados.
 * @param {string} id - ID del comando.
 * @param {string} comando - El comando (ej. "/hola").
 * @param {string} texto - El texto de respuesta asociado al comando.
 */
function editCommand(id, comando, texto) {
    document.getElementById('id_command').value = id;
    document.getElementById('editComando').value = comando;
    document.getElementById('editTexto').value = texto;
    document.getElementById('editCommandD').style.display = 'flex'; // Muestra el formulario de edición
    document.getElementById('newCommand').style.display = 'none'; // Oculta el formulario de nuevo comando
}

/**
 * Cambia el estado (activo/inactivo) de un comando.
 * @param {string} id - ID del comando a modificar.
 * @param {string} status - Estado actual del comando ('activo' o 'inactivo').
 */
function toggleCommandStatus(id, status) {
    const newStatus = status === 'activo' ? 'inactivo' : 'activo'; // Determina el nuevo estado

    fetch('controllers/newCommand.php', { // Petición a la API para actualizar el estado
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            id_command: id,
            status: newStatus
        })
    })
    .then(async response => {
        const contentType = response.headers.get("Content-Type");
        if (contentType && contentType.includes("application/json")) {
            const data = await response.json();
            if (data.success) {
                toast(data.message || "Estado actualizado.", 'success');
                location.reload(); // Recarga la página para ver los cambios reflejados
            } else {
                toast(data.message || "Error al actualizar estado.", 'error');
            }
        } else {
            throw new Error("Respuesta no válida del servidor.");
        }
    })
    .catch(err => {
        console.error("Error al modificar estado de contacto:", err);
        toast("Error al comunicarse con el servidor.", 'error');
    });
}
