let isAdmin = false;
let lastSearchTerm = "";

document.getElementById('search-input').addEventListener('input', function () {
    lastSearchTerm = this.value.toLowerCase();
    filterChatList();
});

window.filterChatList = function () {
    const searchTerm = lastSearchTerm;
    const chatList = document.querySelector(".chat-list");
    const chatItems = chatList.querySelectorAll(".chat-item");

    chatItems.forEach(item => {
        const chatName = item.querySelector(".div2").textContent.toLowerCase();
        const chatNumero = item.querySelector(".div3").textContent.toLowerCase();

        if (chatName.includes(searchTerm) || chatNumero.includes(searchTerm)) {
            item.style.display = "grid";
        } else {
            item.style.display = "none";
        }
    });
}

/**
 * Muestra un mensaje toast (notificación emergente).
 * @param {string} message - El mensaje a mostrar.
 * @param {'success' | 'error' | 'info'} type - El tipo de toast (afecta el estilo).
 * @param {number} [duration=2500] - La duración en milisegundos que el toast será visible.
 */
window.toast = function (message, type, duration = 2500) {
    let toastContainer = document.getElementById("toast-container");

    if (!toastContainer) {
        toastContainer = document.createElement("div");
        toastContainer.id = "toast-container";
        document.body.appendChild(toastContainer);
    }

    let toast = document.createElement("div");
    toast.className = `toast ${type}`;
    toast.innerText = message; // Usar innerText para seguridad contra XSS si el mensaje no es HTML.

    toastContainer.appendChild(toast);

    setTimeout(function () {
        if (toastContainer.contains(toast)) { // Verificar si el toast aún existe antes de intentar removerlo
            toastContainer.removeChild(toast);
        }
    }, duration);
};

/**
     * Formatea un número de teléfono para visualización.
     * @param {string} number - El número de teléfono a formatear.
     * @returns {string} El número formateado o el original si falla el formateo.
     */
function formatPhoneNumber(number) {
    number = number.replace("@c.us", "");

    try {
        let telefono = new TelefonoArgentino(number);  // Crear una instancia
        return telefono._format(telefono.data);  // Llamar al método correcto
    } catch (error) {
        console.error("Error al formatear el número:", error);
        return number;  // Devuelve el número sin cambios si hay error
    }
}



// Inicializa select2 en los elementos con la clase 'select2' cuando el DOM está listo.
$(document).ready(function () {
    $('.select2').select2();
});

/**
 * Cambia el formulario visible para crear un nuevo chat basado en la selección del tipo de chat.
 * @param {'contacto' | 'paciente' | 'numero'} value - El tipo de chat seleccionado.
 */
function changeForm(value) {
    // Oculta todos los formularios primero
    $('#newContacto').hide();
    $('#newPaciente').hide();
    $('#newNumero').hide();

    // Muestra el formulario correspondiente
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
    if (!emojiList) return;

    const isVisible = emojiList.style.display === 'block';
    emojiList.style.display = isVisible ? 'none' : 'block';
}

// Event listener para el botón que muestra/oculta la lista de emojis.
document.getElementById('emojiList').addEventListener('click', function (event) {
    event.stopPropagation(); // Evita que el clic se propague y cierre el picker inmediatamente.
    turnEmojiList();
});

// Event listener para cerrar el picker de emojis si se hace clic fuera de él.
document.addEventListener('click', function (event) {
    const emojiList = document.querySelector('emoji-picker');
    if (!emojiList) return;

    const messageInputRef = document.getElementById('messageInput'); // Referencia al input de mensaje

    // Si el picker está visible y el clic no fue dentro del picker ni en el botón que lo abre ni en el input de mensaje
    if (emojiList.style.display === 'block' &&
        !emojiList.contains(event.target) &&
        event.target.id !== 'emojiList' &&
        !event.target.closest('#emojiList') &&
        event.target.id !== 'messageInput') {
        emojiList.style.display = 'none';
    }
});

const emojiPicker = document.querySelector('emoji-picker');
// const messageInputGlobal = document.getElementById('messageInput'); // Se usará messageInput definido en DOMContentLoaded

// Event listener para cuando se selecciona un emoji del picker.
if (emojiPicker) {
    emojiPicker.addEventListener('emoji-click', event => {
        const emoji = event.detail.unicode;
        const currentMessageInput = document.getElementById('messageInput'); // Asegurarse de tener la referencia correcta
        insertAtCursor(currentMessageInput, emoji);
        currentMessageInput.focus(); // Devuelve el foco al input de mensaje.
    });
}

/**
 * Inserta texto en la posición actual del cursor en un campo de entrada.
 * @param {HTMLInputElement|HTMLTextAreaElement} input - El campo de entrada.
 * @param {string} textToInsert - El texto a insertar.
 */
function insertAtCursor(input, textToInsert) {
    const start = input.selectionStart;
    const end = input.selectionEnd;

    const textBefore = input.value.substring(0, start);
    const textAfter = input.value.substring(end);

    input.value = textBefore + textToInsert + textAfter;

    const newPos = start + textToInsert.length;
    input.selectionStart = input.selectionEnd = newPos;
}

/**
 * Iguala el ancho de un elemento al ancho del contenedor de la lista de chats.
 * Usado para los paneles de datos del paciente y opciones.
 * @param {string} elementId - El ID del elemento cuyo ancho se ajustará.
 */
function igualarAnchoElementoAlDeChats(elementId) {
    const chatsContainer = document.querySelector('.chats');
    const elemento = document.getElementById(elementId);

    if (chatsContainer && elemento) {
        const anchoChats = window.getComputedStyle(chatsContainer).width;
        elemento.style.width = anchoChats;
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
    function formatState(state) {
        if (!state.id) {
            return state.text;
        }
        var flagCode = state.element.getAttribute('data-flag');
        if (!flagCode) return state.text;
        var flagUrl = 'https://flagicons.lipis.dev/flags/4x3/' + flagCode + '.svg';
        var $state = $(
            '<span><img src="' + flagUrl + '" class="img-flag" style="width: 20px; height: auto; margin-right: 10px;" /> ' + state.text + '</span>'
        );
        return $state;
    }

    $(".js-example-templating").select2({
        templateResult: formatState,
        templateSelection: formatState,
        width: 'resolve'
    });
});

/**
 * Muestra u oculta el campo para ingresar un código de país personalizado.
 * @param {string} value - El valor seleccionado en el dropdown de país.
 */
function checkOther(value) {
    const otherCountryInput = document.getElementById('otherCountry');
    if (value === 'un') {
        otherCountryInput.style.display = 'block';
        otherCountryInput.required = true;
    } else {
        otherCountryInput.style.display = 'none';
        otherCountryInput.required = false;
        otherCountryInput.value = '';
    }
}

// Manejo del envío del formulario para derivar chat.
$(document).ready(function () {
    $('#derivarForm').submit(function (event) {
        event.preventDefault();

        var formData = {
            chat_id: $('#id_chat').val(),
            estado: 'chatting',
            agente: $('#agenteSelect').val()
        };

        $.ajax({
            type: 'POST',
            url: 'api/modificar_estado_chat.php',
            data: JSON.stringify(formData),
            contentType: 'application/json',
            dataType: 'json',
            success: function (response) {
                if (response && response.success) {
                    toast(response.message || 'Chat derivado correctamente', 'success');
                    $('#back').hide();
                    $('#derivar').hide();
                    $('#derivarForm')[0].reset();
                    $('#agenteSelect').val(null).trigger('change');
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

let archivosSeleccionados = []; // Para los archivos seleccionados

document.addEventListener("DOMContentLoaded", function () {
    const chatList = document.querySelector(".chat-list");
    const buttons = document.querySelectorAll(".btn-chat");
    const chatBody = document.querySelector("#chatBody"); // Usado como messageContainer en fetchMessages
    const chatHeader = document.querySelector("#chatHeader");
    const messageInput = document.querySelector("#messageInput"); // Variable para el input de mensajes
    const sendMessageButton = document.querySelector("#sendMessageButton");
    const docInput = document.getElementById("docInput"); // Para selección de archivos
    const emojiButton = document.getElementById("emojiList");
    const fileUploadLabel = document.querySelector(".custom-file-upload");
    const filePreview = document.getElementById("filePreview"); // Para previsualización de archivos

    const patientDataContainer = document.getElementById("patientData");
    const patientOptionsContainer = document.getElementById("patientOptions");

    let currentChatId = null;
    let messageInterval = null;
    window.pacienteNumero = null;


    const buttonNewChat = document.createElement("button");
    buttonNewChat.textContent = "Nuevo chat";
    buttonNewChat.classList.add("btn-tematico");
    buttonNewChat.style.margin = "0 auto";
    buttonNewChat.style.fontWeight = "bold";
    buttonNewChat.addEventListener("click", function () {
        document.getElementById('back').style.display = "flex";
        document.getElementById('newChat').style.display = "flex";
        $('#newContacto')[0].reset();
        $('#newPaciente')[0].reset();
        $('#newNumero')[0].reset();
        $('#contacto').val(null).trigger('change');
        $('#paciente').val(null).trigger('change');
        changeForm('contacto');
    });

    function initializeChatView() {
        chatHeader.innerHTML = "";
        chatHeader.appendChild(buttonNewChat);
        chatBody.innerHTML = "";

        emojiButton.disabled = true;
        fileUploadLabel.classList.add("disabled");
        docInput.disabled = true;
        messageInput.disabled = true;
        messageInput.value = "";
        sendMessageButton.disabled = true;

        if (patientDataContainer) {
            patientDataContainer.innerHTML = "";
            patientDataContainer.classList.remove("active");
        }
        if (patientOptionsContainer) {
            patientOptionsContainer.innerHTML = "";
            patientOptionsContainer.classList.remove("active");
        }

        archivosSeleccionados = [];
        renderPreview(); // Usar la función restaurada

        document.querySelectorAll('.chat-item.active-chat').forEach(item => item.classList.remove('active-chat'));

        currentChatId = null;
        window.pacienteNumero = null;
        if (messageInterval) {
            clearInterval(messageInterval);
            messageInterval = null;
        }
    }
    initializeChatView();

    function formatTimestampToHHMM(timestampString) {
        try {
            const date = new Date(timestampString);
            if (isNaN(date.getTime())) throw new Error("Invalid date string");
            const hours = date.getHours().toString().padStart(2, '0');
            const minutes = date.getMinutes().toString().padStart(2, '0');
            return `${hours}:${minutes}`;
        } catch (e) {
            console.error("Error formateando marca de tiempo:", timestampString, e);
            return '--:--';
        }
    }

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
            default: return 'fa-file';
        }
    }

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

    function isImageExtension(extension) {
        if (!extension) return false;
        const imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp', 'ico'];
        return imageExtensions.includes(extension.toLowerCase());
    }

    /**
     * Obtiene y muestra los mensajes de un chat. Actualiza los estados de los mensajes existentes.
     * @param {string} chatId - El ID del chat del cual obtener los mensajes.
     */
    function fetchMessages(chatId) {
        if (!chatId) return;
        const messageContainer = chatBody;

        fetch(`api/get_messages.php?chat_id=${chatId}`)
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

                const scrollPosition = messageContainer.scrollTop;
                const isAtBottom = scrollPosition + messageContainer.clientHeight >= messageContainer.scrollHeight - 20; // Umbral para considerar "al final"

                data.forEach(message => {
                    const existingMessageElement = messageContainer.querySelector(`.message[data-id="${message.id}"]`);

                    if (!existingMessageElement) {
                        // --- Lógica para crear un nuevo elemento de mensaje ---
                        const messageElement = document.createElement("div");
                        messageElement.classList.add("message", message.remitente === "paciente" ? "received" : "sent");
                        messageElement.dataset.id = message.id;

                        const contentDiv = document.createElement("div");
                        contentDiv.classList.add("message-content");

                        const filePrefix = '!fileTypeMessage, ';
                        if (message.mensaje && typeof message.mensaje === 'string' && message.mensaje.startsWith(filePrefix)) {
                            // --- Lógica para mensajes de archivo ---
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
                                const linkElement = document.createElement('a');
                                linkElement.href = fileUrl;
                                linkElement.target = '_blank';
                                linkElement.title = originalName;
                                linkElement.classList.add('message-file-link', 'message-image-preview-link');

                                const imgElement = document.createElement('img');
                                imgElement.src = fileUrl;
                                imgElement.alt = originalName;
                                imgElement.classList.add('message-image-preview');
                                imgElement.onerror = () => {
                                    console.error("Error cargando imagen:", fileUrl);
                                    linkElement.innerHTML = `<i class="fa-solid fa-image"></i> Error al cargar: ${originalName}`;
                                    linkElement.classList.remove('message-image-preview-link');
                                    linkElement.href = '#';
                                };
                                linkElement.appendChild(imgElement);
                                contentDiv.appendChild(linkElement);
                            } else {
                                const linkElement = document.createElement('a');
                                linkElement.href = fileUrl;
                                linkElement.download = originalName;
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
                                const textElement = document.createElement('p');
                                textElement.textContent = optionalText;
                                textElement.classList.add('message-file-caption');
                                contentDiv.appendChild(textElement);
                            }
                        } else {
                            // --- Lógica para mensajes de texto ---
                            const textElement = document.createElement('p');
                            // Importante: Asumir que el backend NO devuelve HTML. Si lo hace, sanitizar aquí.
                            // textElement.textContent = message.mensaje; // Más seguro si no esperas HTML
                            textElement.innerHTML = message.mensaje; // Usar innerHTML si el backend puede enviar formato básico como <br>
                            textElement.classList.add('message-text');
                            contentDiv.appendChild(textElement);
                        }
                        messageElement.appendChild(contentDiv);

                        const infoDiv = document.createElement("div");
                        infoDiv.classList.add("message-info");
                        const timeSpan = document.createElement("span");
                        timeSpan.classList.add("message-time");
                        timeSpan.textContent = formatTimestampToHHMM(message.timestamp);
                        infoDiv.appendChild(timeSpan);

                        // --- Lógica para añadir el tick de estado INICIAL ---
                        if (message.remitente !== 'paciente') {
                            const tickSpan = document.createElement("span");
                            tickSpan.classList.add("message-tick");
                            const tickIcon = document.createElement("i");
                            tickIcon.classList.add("fa-solid"); // Clase base

                            // Mapeo de estados de la BD a clases CSS e iconos FontAwesome
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
                            const defaultStateInfo = { cssClass: 'pending', iconClass: 'fa-question-circle' }; // Estado por defecto

                            const currentState = message.estado;
                            const stateInfo = stateMap[currentState] || defaultStateInfo;

                            if (!stateMap[currentState]) {
                                console.warn("Estado de mensaje desconocido al crear:", currentState, "Mensaje ID:", message.id);
                            }

                            tickIcon.classList.add(stateInfo.iconClass);
                            tickSpan.classList.add(stateInfo.cssClass);

                            tickSpan.appendChild(tickIcon);
                            infoDiv.appendChild(tickSpan);
                        }
                        messageElement.appendChild(infoDiv);
                        messageContainer.appendChild(messageElement);

                        if (message.remitente != 'paciente' && isAdmin) {
                            const adminElement = document.createElement('div');
                            adminElement.classList.add('remitente-admin');
                            adminElement.innerHTML = `Remitente: ${message.remitente_agente}`;
                            contentDiv.appendChild(adminElement);
                        }

                    } else {

                        const statusMap = {
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

                        /**
                         * Actualiza el icono y la clase CSS de un tick de mensaje, manejando la conversión de Font Awesome a SVG.
                         * @param {HTMLElement} tickSpan - El elemento span que contiene el tick.
                         * @param {string} newStatus - El nuevo estado del mensaje (ej: 'sent', 'delivered', 'read', 'pending', 'error').
                        */
                        function updateMessageTick(tickSpan, newStatus) {
                            // console.warn(`[updateMessageTick] INICIO. newStatus=${newStatus}. tickSpan classList ANTES: ${tickSpan ? tickSpan.className : 'N/A'}`);

                            if (!tickSpan) {
                                console.error("[updateMessageTick] tickSpan es null. No se puede actualizar.");
                                return;
                            }

                            const statusInfo = statusMap[newStatus] || statusMap['unknown'];
                            // console.log(`[updateMessageTick] Estado a aplicar (statusInfo) para '${newStatus}':`, JSON.parse(JSON.stringify(statusInfo)));

                            // 1. Remover clases anteriores
                            Object.values(statusMap).forEach(s => {
                                if (s.cssClass && tickSpan.classList.contains(s.cssClass)) {
                                    // console.log(`[updateMessageTick] Removiendo clase '${s.cssClass}' del tickSpan.`);
                                    tickSpan.classList.remove(s.cssClass);
                                }
                            });
                            // console.log(`[updateMessageTick] tickSpan classList DESPUÉS de remover: ${tickSpan.className}`);

                            // 2. Añadir clase nueva
                            if (statusInfo && statusInfo.cssClass) {
                                // console.log(`[updateMessageTick] Añadiendo clase '${statusInfo.cssClass}' al tickSpan.`);
                                tickSpan.classList.add(statusInfo.cssClass);
                            } else {
                                console.warn(`[updateMessageTick] No se encontró cssClass válida en statusInfo para el estado '${newStatus}'.`);
                            }
                            // console.log(`[updateMessageTick] tickSpan classList DESPUÉS de añadir: ${tickSpan.className}`);

                            // 3. Actualizar ícono (asumiendo que estás usando <i> o querés cambiar a eso)
                            if (statusInfo.iconClass) {
                                // console.log(`[updateMessageTick] Reemplazando ícono por '${statusInfo.iconClass}'`);
                                tickSpan.innerHTML = `<i class="fa-solid ${statusInfo.iconClass}"></i>`;
                            } else {
                                console.warn(`[updateMessageTick] No se encontró iconClass para el estado '${newStatus}'.`);
                            }

                            // console.log(`[updateMessageTick] FIN. tickSpan classList FINAL: ${tickSpan.className}`);
                        }


                        // --- Lógica para ACTUALIZAR el estado de un mensaje existente ---
                        if (message.remitente !== 'paciente') {
                            // console.log(`[fetchMessages] Verificando tick para mensaje ID ${message.id}...`);
                            const tickSpan = existingMessageElement.querySelector(".message-tick");
                            // const tickIcon = tickSpan ? tickSpan.querySelector("i.fa-solid") : null; // YA NO ES NECESARIO ASÍ

                            if (tickSpan) { // Solo necesitamos verificar que tickSpan exista
                                // console.log(`[fetchMessages] Tick encontrado para mensaje ID ${message.id}.`);

                                let currentDOMStatusKey = 'unknown';
                                // console.log(`[fetchMessages] Estado inicial en DOM: ${currentDOMStatusKey}`);

                                for (const key in statusMap) {
                                    if (statusMap.hasOwnProperty(key) && tickSpan.classList.contains(statusMap[key].cssClass)) {
                                        currentDOMStatusKey = key;
                                        // console.log(`[fetchMessages] Estado DOM encontrado (desde tickSpan): ${currentDOMStatusKey} para mensaje ID ${message.id}.`);
                                        break;
                                    }
                                }

                                if (currentDOMStatusKey === 'unknown') {
                                    console.warn(`[fetchMessages] No se pudo determinar el estado DOM para el mensaje ID: ${message.id}`);
                                }

                                const newStatusFromDB = message.estado || 'unknown';
                                // console.log(`[fetchMessages] Estado desde la base de datos para mensaje ID ${message.id}: ${newStatusFromDB}`);

                                if (newStatusFromDB !== currentDOMStatusKey) {
                                    // console.log(`[fetchMessages] Actualizando estado para mensaje ID <span class="math-inline">\{message\.id\}\: DB state '</span>{newStatusFromDB}' vs DOM state '${currentDOMStatusKey}'.`);
                                    // Solo pasamos tickSpan y el nuevo estado.
                                    updateMessageTick(tickSpan, newStatusFromDB);
                                } else {
                                    // console.log(`[fetchMessages] No se requiere actualización para mensaje ID ${message.id}.`);
                                }
                            } else {
                                console.warn(`[fetchMessages] tickSpan no encontrado para mensaje ID ${message.id}.`);
                            }
                        }
                    }
                });

                // Mantener scroll al final solo si ya estaba al final antes de añadir/actualizar mensajes
                if (isAtBottom) {
                    // Usar un pequeño timeout puede ayudar si hay imágenes cargando
                    setTimeout(() => messageContainer.scrollTop = messageContainer.scrollHeight, 50);
                }
            })
            .catch(error => {
                console.error("Error fetching messages:", error);
                // Considerar mostrar un toast o mensaje en la UI aquí también
            });

        // Marcar mensajes como leídos (esta parte parece correcta)
        // console.log("[fetchMessages] Marcando mensajes como leídos para chatId:", chatId, "agente:", dni);
        fetch(`api/read_messages.php?chatId=${chatId}&agent=${dni}`) // Asumiendo que 'dni' es la variable global del agente
            .then(response => {
                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                return response.json();
            })
            .then(data => {
                if (data.error) {
                    console.error("API Error (read_messages):", data.error);
                } else {
                    // console.log("[fetchMessages] Mensajes marcados como leídos:", data);
                }
            })
            .catch(error => {
                console.error("Error marking messages as read:", error);
            });
    }


    window.openChat = function (chatId, chatName, estadoChat, numeroPaciente) {
        if (messageInterval) {
            clearInterval(messageInterval);
            messageInterval = null;
        }
        chatBody.innerHTML = ""; // Limpiar mensajes del chat anterior
        if (patientDataContainer) {
            patientDataContainer.innerHTML = ""; // Limpiar datos del paciente anterior
            patientDataContainer.classList.remove("active");
        }
        if (patientOptionsContainer) {
            patientOptionsContainer.innerHTML = ""; // Limpiar opciones del paciente anterior
            patientOptionsContainer.classList.remove("active"); // Si usa clase active
        }
        messageInput.value = ""; // Limpiar input de mensaje
        archivosSeleccionados = []; // Limpiar archivos seleccionados
        renderPreview(); // Limpiar previsualización de archivos

        currentChatId = chatId;
        window.pacienteNumero = numeroPaciente; // Guardar el número del paciente actual

        // Actualizar cabecera del chat
        chatHeader.innerHTML = ""; // Limpiar cabecera anterior
        const chatTitle = document.createElement('span');
        chatTitle.textContent = chatName || "Chat"; // Nombre del chat o "Chat" por defecto
        chatTitle.style.fontWeight = "bold";
        chatHeader.appendChild(chatTitle);

        // Resaltar chat activo en la lista
        document.querySelectorAll('.chat-item.active-chat').forEach(item => item.classList.remove('active-chat'));
        const currentChatItem = document.querySelector(`.chat-item[data-chat-id="${currentChatId}"]`);
        if (currentChatItem) {
            currentChatItem.classList.add('active-chat');
            currentChatItem.classList.remove('unread'); // Quitar clase de no leído
            const unreadBadge = currentChatItem.querySelector('.div3.un'); // Encontrar el badge de no leídos
            if (unreadBadge) {
                unreadBadge.textContent = '0'; // Poner contador a 0
                unreadBadge.style.opacity = '0'; // Ocultar badge
                unreadBadge.style.visibility = 'hidden';
            }
        }


        // Habilitar/deshabilitar campos de entrada y botones
        emojiButton.disabled = false;
        fileUploadLabel.classList.remove("disabled");
        docInput.disabled = false;
        messageInput.disabled = false;
        sendMessageButton.disabled = false;
        messageInput.focus(); // Poner foco en el input de mensaje

        // --- Lógica para botones de la cabecera del chat (Reclamar, Datos, Acciones, Cerrar) ---
        const buttonContainer = document.createElement("div");
        buttonContainer.classList.add("chat-header-buttons");

        // Botón "Reclamar" (si el chat está pendiente)
        if (estadoChat === 'pendiente') {
            const buttonReclamar = document.createElement("button");
            buttonReclamar.innerHTML = '<i class="fa-solid fa-hand-holding"></i> Reclamar';
            buttonReclamar.classList.add("btn-tematico", "button-switch", "start");
            buttonReclamar.addEventListener("click", function () {
                fetch('api/modificar_estado_chat.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ chat_id: currentChatId, estado: 'chatting', agente: dni }) // 'dni' debe estar disponible globalmente
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            toast(data.message || "Chat reclamado.", 'success');
                            fetchChats('chatting'); // Actualizar lista de chats en "chatting"
                            selectButton('chatting'); // Seleccionar visualmente la pestaña "chatting"
                            // No es necesario llamar a openChat de nuevo aquí, fetchChats debería actualizar la UI
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

        // Botones "Datos" y "Acciones" (si el chat está activo)
        if (estadoChat === 'chatting') {
            const buttonDatos = document.createElement("button");
            buttonDatos.innerHTML = '<i class="fa-solid fa-circle-info"></i> Datos';
            buttonDatos.classList.add("btn-tematico", "button-switch", "start");
            buttonDatos.addEventListener("click", function () {
                patientDataContainer.classList.toggle("active");
                patientOptionsContainer.classList.remove("active"); // Ocultar el otro panel
                igualarAnchoElementoAlDeChats("patientData");
                if (patientDataContainer.classList.contains("active")) {
                    // Cargar datos del paciente si el panel está activo y vacío
                    if (patientDataContainer.innerHTML.trim() === "") {
                        patientDataContainer.innerHTML = "<p>Cargando datos...</p>";
                        fetch(`api/get_patient_data.php`, { // Asumiendo que existe este endpoint
                            method: "POST", // O GET, según tu API
                            headers: { "Content-Type": "application/json" },
                            body: JSON.stringify({ chat_id: currentChatId }) // Enviar chat_id para obtener datos
                        })
                            .then(response => response.json())
                            .then(data => {
                                if (data.error) {
                                    console.error("Error al obtener datos del paciente:", data.error);
                                    patientDataContainer.innerHTML = `<p class="error-text">Error: ${data.error}</p>`;
                                    return;
                                }
                                if (data.telefono) window.pacienteNumero = data.telefono; // Actualizar número si es necesario
                                const fechaNacimiento = data.fecha_nacimiento ? new Date(data.fecha_nacimiento).toLocaleDateString('es-AR', { timeZone: 'UTC' }) : 'N/A';
                                patientDataContainer.innerHTML = `
                                <p><strong>Apellidos:</strong> ${data.apellidos || 'N/A'}</p>
                                <p><strong>Nombres:</strong> ${data.nombres || 'N/A'}</p>
                                <p><strong>Sexo:</strong> ${data.sexo || 'N/A'}</p>
                                <p><strong>Documento:</strong> ${data.tipo_documento || ''} ${data.documento || 'N/A'}</p>
                                <p><strong>Nacimiento:</strong> ${fechaNacimiento}</p>
                                <p><strong>Identidad genero:</strong> ${data.identidad_genero}</p>
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
                            })
                            .catch(error => {
                                console.error("Error fetch datos paciente:", error);
                                patientDataContainer.innerHTML = `<p class="error-text">Error de conexión al cargar datos.</p>`;
                            });
                    }
                }
            });
            buttonContainer.appendChild(buttonDatos);

            const buttonAcciones = document.createElement('button');
            buttonAcciones.innerHTML = '<i class="fa-solid fa-bars"></i> Acciones';
            buttonAcciones.classList.add("btn-tematico", "button-switch");
            buttonAcciones.addEventListener("click", function () {
                patientOptionsContainer.classList.toggle("active");
                patientDataContainer.classList.remove("active"); // Ocultar el otro panel
                igualarAnchoElementoAlDeChats("patientOptions");
                if (patientOptionsContainer.classList.contains("active")) {
                    // Cargar opciones si el panel está activo y vacío
                    if (patientOptionsContainer.innerHTML.trim() === "") {
                        const toWait = document.createElement("button");
                        toWait.innerHTML = '<i class="fa-solid fa-hourglass-half"></i> Mover a Espera';
                        toWait.classList.add("btn-yellow", "btn-block");
                        toWait.addEventListener("click", function () {
                            fetch('api/modificar_estado_chat.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ chat_id: currentChatId, estado: 'pendiente', agente: null }) // Enviar agente como null o no enviarlo
                            })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        toast(data.message || "Chat movido a espera.", 'success');
                                        initializeChatView(); // Cerrar vista actual
                                        fetchChats(document.querySelector(".btn-chat.active")?.id || "chatting"); // Recargar lista de chats
                                    } else {
                                        toast(data.message || "Error al mover a espera.", 'error');
                                    }
                                }).catch(err => {
                                    console.error("Error mover a espera:", err);
                                    toast("Error de conexión.", 'error');
                                });
                        });

                        const toEnd = document.createElement("button");
                        toEnd.innerHTML = '<i class="fa-solid fa-circle-check"></i> Finalizar Chat';
                        toEnd.classList.add("btn-red", "btn-block");
                        toEnd.addEventListener("click", function () {
                            fetch('api/modificar_estado_chat.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ chat_id: currentChatId, estado: 'finalizado', agente: dni })
                            })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        toast(data.message || "Chat finalizado.", 'success');
                                        initializeChatView();
                                        fetchChats(document.querySelector(".btn-chat.active")?.id || "chatting");
                                    } else {
                                        toast(data.message || "Error al finalizar chat.", 'error');
                                    }
                                }).catch(err => {
                                    console.error("Error finalizar chat:", err);
                                    toast("Error de conexión.", 'error');
                                });
                        });

                        const toDerivar = document.createElement("button");
                        toDerivar.innerHTML = '<i class="fa-solid fa-share-from-square"></i> Derivar Chat';
                        toDerivar.classList.add("btn-tematico", "btn-block");
                        toDerivar.addEventListener("click", function () {
                            document.getElementById('back').style.display = "flex";
                            document.getElementById('derivar').style.display = "flex";
                            document.getElementById('id_chat').value = currentChatId; // Poner el ID del chat actual en el form de derivar
                            $('#agenteSelect').val(null).trigger('change'); // Resetear select2
                            patientOptionsContainer.classList.remove("active"); // Ocultar panel de opciones
                        });

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
            initializeChatView(); // Restablecer la vista de chat
        });
        buttonContainer.appendChild(buttonCloseChat);

        chatHeader.appendChild(buttonContainer);
        // --- Fin Lógica para botones de la cabecera ---

        // Cargar mensajes iniciales y establecer intervalo de actualización
        fetchMessages(chatId);
        messageInterval = setInterval(() => {
            if (currentChatId) { // Solo buscar si hay un chat activo
                fetchMessages(currentChatId);
            }
        }, 3000); // Intervalo de actualización de mensajes (ej. 5 segundos)
    }

    // --- SECCIÓN DE ENVÍO DE MENSAJES Y ARCHIVOS (VERSIÓN DE download (1).js) ---
    /**
     * Envía un mensaje de texto y/o archivos al chat activo.
     */
    function sendMessage() {
        const messageText = messageInput.value.trim();

        if (!messageText && archivosSeleccionados.length === 0) {
            // toast("Escribe un mensaje o selecciona un archivo.", "info"); // Opcional: notificar al usuario
            return; // No enviar nada vacío
        }

        // window.pacienteNumero se usa aquí, asegúrate que esté actualizado en openChat
        if (window.pacienteNumero === null || !currentChatId) {
            console.error("Error: Número del paciente o ID del chat no disponible.");
            toast("Error: No se puede enviar el mensaje. Reabre el chat.", "error");
            return;
        }

        // Si hay solo mensaje de texto (sin archivos)
        if (archivosSeleccionados.length === 0) {
            const tempId = `temp_${Date.now()}`; // ID temporal para el mensaje
            // const messageText = messageInput.value.trim(); // Ya está definida arriba

            // Añadir mensaje al DOM con estado 'pendiente' (Optimistic UI Update)
            const messageElement = document.createElement("div");
            messageElement.classList.add("message", "sent"); // Asumimos que es 'sent' (enviado por el agente)
            messageElement.dataset.id = tempId; // Usar ID temporal

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
            tickIcon.classList.add("fa-solid", "fa-clock");
            tickSpan.appendChild(tickIcon);
            infoDiv.appendChild(tickSpan);
            messageElement.appendChild(infoDiv);

            chatBody.appendChild(messageElement);
            chatBody.scrollTop = chatBody.scrollHeight; // Scroll al final
            const originalMessageInputValue = messageInput.value; // Guardar el valor por si falla
            messageInput.value = ""; // Limpiar input

            // Petición Fetch
            fetch(`api/send_message.php`, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    numero: window.pacienteNumero,
                    mensaje: messageText,
                    chat_id: currentChatId,
                    remitente: dni // 'dni' debe estar disponible globalmente
                })
            })
                .then(response => response.json())
                .then(data => {
                    const sentMessageElement = chatBody.querySelector(`.message[data-id="${tempId}"]`); // Encontrar el mensaje temporal

                    if (data.success && data.message_id) { // Asumimos que el backend devuelve el ID real del mensaje
                        if (sentMessageElement) {
                            sentMessageElement.dataset.id = data.message_id; // Actualizar al ID real
                            const tick = sentMessageElement.querySelector(".message-tick");
                            if (tick) {
                                tick.classList.remove("pending");
                                tick.classList.add("sent"); // O el estado que devuelva el backend como 'enviado'
                                tick.innerHTML = '<i class="fa-solid fa-check"></i>';
                            }
                        }
                        // No es necesario limpiar el input aquí si la respuesta es exitosa, ya se limpió antes.
                    } else {
                        console.error("Error al enviar mensaje:", data.error || 'Error desconocido del servidor');
                        toast(data.error || "Error al enviar mensaje.", "error");
                        if (sentMessageElement) { // Marcar el mensaje temporal como error
                            const tick = sentMessageElement.querySelector(".message-tick");
                            if (tick) {
                                tick.classList.remove("pending");
                                tick.classList.add("error"); // Clase para estado de error
                                tick.innerHTML = '<i class="fa-solid fa-exclamation-circle"></i>'; // Icono de error
                            }
                        }
                        messageInput.value = originalMessageInputValue; // Restaurar el mensaje en el input
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
                    messageInput.value = originalMessageInputValue; // Restaurar
                });
        }
        // Si hay archivos (con o sin texto)
        else {
            const formData = new FormData();
            formData.append("numero", window.pacienteNumero);
            formData.append("chat_id", currentChatId);
            formData.append("remitente", dni); // 'dni' debe estar disponible globalmente

            if (messageText) {
                formData.append("mensaje", messageText); // Adjuntar texto si existe
            }

            archivosSeleccionados.forEach((file, index) => {
                formData.append(`archivo_${index}`, file);
            });

            const xhr = new XMLHttpRequest();
            xhr.open("POST", "api/send_files.php", true);

            xhr.upload.onprogress = function (event) {
                if (event.lengthComputable) {
                    const porcentaje = Math.round((event.loaded / event.total) * 100);
                    // console.log(`Progreso de subida: ${porcentaje}%`);
                }
            };

            xhr.onload = function () {
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            messageInput.value = "";
                            docInput.value = "";
                            archivosSeleccionados = [];
                            renderPreview();
                            fetchMessages(currentChatId); // Recargar mensajes para ver el archivo enviado
                            setTimeout(() => chatBody.scrollTop = chatBody.scrollHeight, 300);
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

            xhr.onerror = function () {
                console.error("Error de red al enviar archivos.");
                toast("Error de red al intentar enviar archivos.", "error");
            };

            xhr.send(formData);
        }
    }

    sendMessageButton.addEventListener("click", sendMessage);
    messageInput.addEventListener("keydown", function (event) {
        if (event.key === "Enter" && !event.shiftKey) {
            event.preventDefault();
            sendMessage();
        }
    });
    // --- FIN DE SECCIÓN DE ENVÍO (VERSIÓN DE download (1).js) ---


    window.selectButton = function (estado) {
        buttons.forEach(btn => btn.classList.remove("active"));
        const selectedButton = document.getElementById(estado);
        if (selectedButton) selectedButton.classList.add("active");

        // Actualizar URL sin recargar
        const newUrl = new URL(window.location);
        newUrl.searchParams.set("estado", estado);
        window.history.pushState({ path: newUrl.href }, "", newUrl.href);
    }

    /**
     * Obtiene y muestra la lista de chats para un estado dado.
     * @param {string} estado - El estado de los chats a obtener (ej: 'chatting', 'pendiente').
     */
    window.fetchChats = function (estado) {

        if (!estado) {
            estado = document.querySelector(".btn-chat.active")?.id || "chatting"; // Obtener el estado actual si no se especificó
        }

        fetch(isAdmin ? `api/get_chats.php?estado=${estado}&adminMode=true` : `api/get_chats.php?estado=${estado}&dni=${dni}`)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    console.error("Error en API get_chats:", data.error);
                    console.warn(data)
                    chatList.innerHTML = `<p class="error-text">${data.error}</p>`;
                    return;
                }

                const chats = Array.isArray(data) ? data : [];
                chatList.innerHTML = ""; // Limpiar lista actual

                if (chats.length === 0) {
                    chatList.innerHTML = `<p class="no-chats-message">No hay chats en estado "${estado}".</p>`;
                } else {
                    chats.forEach(chat => {
                        const chatItem = document.createElement("div");
                        chatItem.classList.add("chat-item");
                        const unreadCount = parseInt(chat.unread_messages_count) || 0;

                        if (unreadCount > 0 && chat.id !== currentChatId) { // Solo marcar no leído si no es el chat activo
                            chatItem.classList.add("unread");
                        }
                        if (chat.id === currentChatId) { // Marcar el chat activo
                            chatItem.classList.add("active-chat");
                        }

                        // Guardar datos del chat en el elemento para fácil acceso
                        chatItem.dataset.chatId = chat.id;
                        chatItem.dataset.chatName = chat.nombre_paciente || 'Desconocido';
                        chatItem.dataset.chatNumero = chat.numero; // Número del paciente
                        chatItem.dataset.chatEstado = estado; // Estado actual del chat (ej. 'chatting')


                        const profilePic = chat.profile_pic || 'https://i.pinimg.com/236x/d9/d8/8e/d9d88e3d1f74e2b8ced3df051cecb81d.jpg';

                        let fechaCierre = chat.fecha_cierre ? new Date(chat.fecha_cierre).toLocaleString('es-AR', { timeZone: 'UTC' }) : '';

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

                        chatItem.addEventListener("click", () => {
                            openChat(
                                chatItem.dataset.chatId,
                                chatItem.dataset.chatName,
                                chatItem.dataset.chatEstado,
                                chatItem.dataset.chatNumero // Pasar el número del paciente a openChat
                            );
                        });
                        chatList.appendChild(chatItem);
                    });
                    // Ejecutar el filtrado solo después de que se hayan cargado todos los chats
                    filterChatList()
                }
            })
            .catch(error => {
                console.error("Error al obtener chats:", error);
                chatList.innerHTML = `<p class="error-text">Error de conexión al cargar chats.</p>`;
            });
    }

    // Cargar estado inicial de la URL o por defecto
    const params = new URLSearchParams(window.location.search);
    const estadoInicial = params.get("estado") || "chatting"; // "chatting" como estado por defecto

    selectButton(estadoInicial);
    fetchChats(estadoInicial);

    // Event listeners para los botones de filtro de estado
    buttons.forEach(button => {
        button.addEventListener("click", function () {
            const estado = this.id;
            if (currentChatId) { // Si hay un chat abierto, ciérralo antes de cambiar de pestaña
                initializeChatView();
            }
            selectButton(estado);
            fetchChats(estado);
        });
    });

    // Manejar navegación del historial del navegador (botones atrás/adelante)
    window.addEventListener("popstate", function (event) {
        const newParams = new URLSearchParams(window.location.search);
        const newEstado = newParams.get("estado") || "chatting";
        if (currentChatId) {
            initializeChatView();
        }
        selectButton(newEstado);
        fetchChats(newEstado);
    });

    // Intervalo para recargar la lista de chats (polling)
    setInterval(() => {
        const isModalOpen = document.getElementById('back').style.display === 'flex'; // Verificar si hay modales abiertos
        if (!isModalOpen) { // Solo recargar si no hay modales activos
            const estadoActivo = document.querySelector(".btn-chat.active")?.id || estadoInicial;
            fetchChats(estadoActivo);
        }
    }, 10000); // Intervalo de actualización de la lista de chats (ej. 10 segundos)
});


// Manejo de formularios de "Nuevo Chat"
$(document).ready(function () {
    $("form#newContacto, form#newPaciente, form#newNumero").submit(function (event) {
        event.preventDefault();
        var form = $(this);
        var formData = new FormData(form[0]); // Usar FormData para enviar archivos si fuera necesario en el futuro

        // Validar "Otro país"
        const otherCountryInput = document.getElementById('otherCountry');
        if (otherCountryInput && otherCountryInput.style.display === 'block' && !otherCountryInput.value) {
            toast('Debe ingresar el código de país.', 'error');
            return;
        }
        if (otherCountryInput && otherCountryInput.style.display === 'block' && otherCountryInput.value) {
            formData.set('country', otherCountryInput.value); // Asegurar que el valor de "otro país" se envíe
        }

        $.ajax({
            url: form.attr("action"), // Debería ser 'api/iniciar_chat.php'
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json', // Esperar respuesta JSON
            success: function (response) {
                if (response && response.success && response.chat) {
                    toast(response.message || "Iniciando chat...", "success", 1500);
                    $('#back').css('display', 'none'); // Ocultar modal
                    $('#newChat').css('display', 'none'); // Ocultar modal
                    form[0].reset(); // Limpiar formulario
                    // Abrir el chat recién creado
                    openChat(response.chat.id, response.chat.nombre_paciente, 'chatting', response.chat.numero);
                    selectButton('chatting'); // Seleccionar la pestaña "En curso"
                    fetchChats('chatting'); // Recargar la lista de chats
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

// Lógica para sugerencias de comandos
const commandInput = document.getElementById("messageInput");
const suggestionsList = document.getElementById("sugerencias");
let selectedSuggestionIndex = -1;
let filteredSuggestions = [];

if (commandInput && suggestionsList && typeof comandosCache !== 'undefined') { // Asegurar que comandosCache exista
    commandInput.addEventListener("input", (e) => {
        const value = e.target.value;
        if (value.startsWith("/")) {
            const searchTerm = value.slice(1).toLowerCase();
            filteredSuggestions = comandosCache.filter(c => c.comando.toLowerCase().startsWith(searchTerm));

            if (comandosCache.length === 0) { // Si no hay comandos definidos
                suggestionsList.style.display = "none";
                return;
            }

            if (filteredSuggestions.length > 0) {
                suggestionsList.innerHTML = filteredSuggestions.map((c, i) =>
                    `<li data-index="${i}" title="${c.texto}" style="cursor:pointer; padding: 5px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">/${c.comando} - ${c.texto.substring(0, 50)}${c.texto.length > 50 ? '...' : ''}</li>`
                ).join("");

                const inputRect = commandInput.getBoundingClientRect();
                const chatFooterRect = commandInput.closest('.chat-footer').getBoundingClientRect();

                suggestionsList.style.position = 'absolute';
                suggestionsList.style.bottom = `${chatFooterRect.height}px`; // Posicionar encima del input
                suggestionsList.style.left = `0px`;
                suggestionsList.style.width = `${inputRect.width}px`;
                suggestionsList.style.display = "block";
                selectedSuggestionIndex = -1; // Resetear selección
            } else {
                suggestionsList.style.display = "none"; // Ocultar si no hay sugerencias
            }
        } else {
            suggestionsList.style.display = "none"; // Ocultar si no empieza con "/"
        }
    });

    commandInput.addEventListener("keydown", (e) => {
        const items = suggestionsList.querySelectorAll("li");
        if (suggestionsList.style.display === "block" && items.length > 0) {
            if (e.key === "ArrowDown") {
                e.preventDefault();
                selectedSuggestionIndex = (selectedSuggestionIndex + 1) % items.length;
                items[selectedSuggestionIndex].scrollIntoView({ block: 'nearest' });
            } else if (e.key === "ArrowUp") {
                e.preventDefault();
                selectedSuggestionIndex = (selectedSuggestionIndex - 1 + items.length) % items.length;
                items[selectedSuggestionIndex].scrollIntoView({ block: 'nearest' });
            } else if (e.key === "Enter" || e.key === "Tab") {
                if (selectedSuggestionIndex >= 0) {
                    e.preventDefault();
                    commandInput.value = filteredSuggestions[selectedSuggestionIndex].texto; // Usar el texto completo del comando
                    suggestionsList.style.display = "none";
                    commandInput.focus();
                }
            } else if (e.key === "Escape") {
                suggestionsList.style.display = "none";
                return;
            }

            // Resaltar sugerencia seleccionada
            items.forEach((el, i) => {
                el.style.background = i === selectedSuggestionIndex ? "#f0f0f0" : "";
            });
        }
    });

    suggestionsList.addEventListener("click", (e) => {
        const targetLi = e.target.closest('li');
        if (targetLi) {
            const index = targetLi.getAttribute("data-index");
            if (index !== null && filteredSuggestions[index]) {
                commandInput.value = filteredSuggestions[index].texto; // Usar el texto completo
                suggestionsList.style.display = "none";
                commandInput.focus();
            }
        }
    });
}

// --- SECCIÓN DE MANEJO DE ARCHIVOS (VERSIÓN DE download (1).js) ---
// docInput, filePreview, archivosSeleccionados y messageInput ya están definidos arriba en DOMContentLoaded.

// Función para crear el ícono según tipo MIME (de download (1).js)
function getIconByType(type) {
    if (!type) return "fa-file"; // Default icon if type is undefined
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

// Renderizar la vista previa (de download (1).js)
function renderPreview() {
    const localFilePreview = document.getElementById("filePreview"); // Usar una variable local para claridad
    if (!localFilePreview) return;

    localFilePreview.innerHTML = "";

    if (archivosSeleccionados.length === 0) {
        localFilePreview.style.display = "none";
        return;
    }

    localFilePreview.style.display = "flex";

    archivosSeleccionados.forEach((file, index) => {
        const item = document.createElement("div");
        item.className = "preview-item";
        item.title = file.name;

        const removeBtn = document.createElement("button");
        removeBtn.className = "remove-preview";
        removeBtn.innerHTML = "&times;";
        removeBtn.onclick = (e) => {
            e.stopPropagation();
            archivosSeleccionados.splice(index, 1);
            if (docInput) docInput.value = ""; // Resetear el input de archivo
            renderPreview();
        };
        item.appendChild(removeBtn);

        if (file.type.startsWith("image/")) {
            const img = document.createElement("img");
            img.src = URL.createObjectURL(file);
            img.onload = () => URL.revokeObjectURL(img.src);
            item.appendChild(img);
        } else {
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
            fileName.style.fontSize = "0.7vw";
            fileName.style.textAlign = "center";
            fileName.style.marginTop = ".2vw";
            fileName.style.wordBreak = "break-all";
            container.appendChild(fileName);

            item.appendChild(container);
        }
        localFilePreview.appendChild(item);
    });
}

// Manejar selección de archivos (de download (1).js)
if (docInput) {
    docInput.addEventListener("change", function () {
        archivosSeleccionados = [...this.files];
        renderPreview();
    });
}

// Manejar archivos pegados en el textarea (de download (1).js, adaptado para usar messageInput)
if (messageInput) {
    messageInput.addEventListener("paste", function (e) {
        const items = [...(e.clipboardData || window.clipboardData).items]; // Compatibilidad
        const filesPasted = items
            .filter(item => item.kind === "file")
            .map(item => item.getAsFile());

        if (filesPasted.length > 0) {
            e.preventDefault();
            archivosSeleccionados.push(...filesPasted);
            renderPreview();
            toast(`${filesPasted.length} archivo(s) pegado(s) y listo(s) para enviar.`, 'info');
        }
    });
}
// --- FIN DE SECCIÓN DE MANEJO DE ARCHIVOS (VERSIÓN DE download (1).js) ---

document.getElementById('adm_mode').addEventListener('change', function () {
    isAdmin = this.checked;
    console.log(`[ADMIN MODE] ${isAdmin ? 'ON' : 'OFF'}`);
    fetchChats();
});

document.getElementById('contactButton').addEventListener('click', function () {
    document.getElementById('contactDiv').style.display = 'flex';
    document.getElementById('back').style.display = 'flex';
});

function editContact(id, nombre, telefono) {
    document.getElementById('id_contact').value = id;
    document.getElementById('editNombre').value = nombre;
    document.getElementById('editTelefono').value = telefono;
    document.getElementById('editContact').style.display = 'flex';
    document.getElementById('newContact').style.display = 'none';
}

function toggleContactStatus(id, status) {
    const newStatus = status === 'activo' ? 'inactivo' : 'activo';

    fetch('controllers/newContact.php', {
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
                location.reload(); // Recargar la página para ver los cambios
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

document.getElementById('commandosButton').addEventListener('click', function () {
    document.getElementById('comandosDiv').style.display = 'flex';
    document.getElementById('back').style.display = 'flex';
});

function editCommand(id, comando, texto) {
    document.getElementById('id_command').value = id;
    document.getElementById('editComando').value = comando;
    document.getElementById('editTexto').value = texto;
    document.getElementById('editCommandD').style.display = 'flex';
    document.getElementById('newCommand').style.display = 'none';
    console.log(`ID: ${id}, Comando: ${comando}, Texto: ${texto}`);
}

function toggleCommandStatus(id, status) {
    const newStatus = status === 'activo' ? 'inactivo' : 'activo';

    fetch('controllers/newCommand.php', {
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
                location.reload(); // Recargar la página para ver los cambios
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