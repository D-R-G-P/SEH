// info_especialidad.js
// Maneja el flujo de información de especialidades y servicios para el bot de WhatsApp.

const fetch = require('node-fetch'); // Necesario para hacer solicitudes HTTP en Node.js

// URL base de tus APIs PHP. Asegúrate de que esta URL sea accesible desde donde se ejecuta tu bot.
// Por ejemplo, si tu bot está en un servidor y tus APIs PHP están en el mismo servidor web,
// podría ser "http://localhost/SGH/public/layouts/modules/gestion_turnos/controllers/"
// O si está en un servidor remoto, la URL pública.
const API_BASE_URL = "http://localhost/SGH/public/layouts/modules/gestion_turnos/controllers/";

/**
 * Función auxiliar para hacer solicitudes a las APIs PHP.
 * @param {string} endpoint - El nombre del archivo PHP (ej: "servicios_api.php").
 * @param {string} action - La acción a realizar (ej: "getAll", "getById").
 * @param {object} [params={}] - Parámetros de la URL (para GET).
 * @returns {Promise<object>} - La respuesta JSON de la API.
 */
async function callApi(endpoint, action, params = {}) {
    // Siempre añadir el filtro de estado 'activo' por defecto para las consultas de lectura
    // Excepto para servicios_api.php, ya que la tabla servicios_turnos_bot no tiene columna de estado.
    const defaultParams = (endpoint === "servicios_api.php") ? {} : { state: 'activo' };
    const mergedParams = { ...defaultParams, ...params, action };

    const query = new URLSearchParams(mergedParams).toString();
    const url = `${API_BASE_URL}${endpoint}?${query}`;
    console.log(`[API Call] Fetching: ${url}`);
    try {
        const response = await fetch(url);
        if (!response.ok) {
            const errorText = await response.text();
            console.error(`[API Error] HTTP error! Status: ${response.status}, Response: ${errorText}`);
            throw new Error(`HTTP error! status: ${response.status}, response: ${errorText}`);
        }
        const jsonResponse = await response.json();
        console.log(`[API Call] Response for ${url}:`, JSON.stringify(jsonResponse, null, 2)); // Log the full JSON response
        return jsonResponse;
    } catch (error) {
        console.error(`[API Error] Failed to fetch from ${url}:`, error.message);
        throw error;
    }
}

/**
 * Inicia el flujo de información de especialidades.
 * Muestra una lista de servicios disponibles.
 * @param {object} client - Instancia del cliente de WhatsApp.
 * @param {string} numero - ID de WhatsApp del usuario.
 * @param {object} userState - Objeto de estado del usuario.
 * @param {object} connection - Pool de conexión a la BD.
 */
async function initiateInfoFlow(client, numero, userState, connection) {
    try {
        const response = await callApi("servicios_api.php", "getAll"); // No se filtra por estado aquí

        if (!response.success || !response.data || response.data.length === 0) {
            await client.sendMessage(numero, "Actualmente no hay especialidades configuradas. Por favor, intenta más tarde.");
            userState.step = null; // Vuelve al menú principal
            userState.data = {};
            return;
        }

        userState.data = {
            currentMenu: 'service_selection',
            services: response.data,
            navigationHistory: [] // Limpiar historial al inicio de un nuevo flujo
        };
        // Añadir el paso actual al historial
        userState.data.navigationHistory.push({ step: 'info_service_selection', params: {} });


        let message = "Selecciona una especialidad para obtener más información:\n\n";
        response.data.forEach((service, index) => {
            message += `${index + 1}️⃣ ${service.nombre}\n`;
        });
        message += "\nEscribe *0* para volver al menú principal.";
        message += "\nEscribe */cancelar* para salir de este flujo.";

        await client.sendMessage(numero, message);
        userState.step = 'info_service_selection'; // Actualizar el paso del usuario
        console.log(`[InfoFlow] User ${numero} is in step: ${userState.step}`);

    } catch (error) {
        console.error(`❌ Error initiating info flow for ${numero}:`, error);
        await client.sendMessage(numero, "Hubo un error al cargar las especialidades. Por favor, intenta de nuevo más tarde.");
        userState.step = null;
        userState.data = {};
    }
}

/**
 * Maneja la selección de una especialidad.
 * @param {object} client - Instancia del cliente de WhatsApp.
 * @param {object} message - Objeto del mensaje de WhatsApp.
 * @param {object} userState - Objeto de estado del usuario.
 * @param {object} connection - Pool de conexión a la BD.
 */
async function handleServiceSelection(client, message, userState, connection) {
    const numero = message.from;
    const messageBody = message.body.trim();

    if (messageBody === '0') {
        return goBack(client, numero, userState, connection); // Volver al menú principal
    }

    const selectedIndex = parseInt(messageBody) - 1;
    const services = userState.data.services;

    if (isNaN(selectedIndex) || selectedIndex < 0 || selectedIndex >= services.length) {
        await client.sendMessage(numero, "Opción inválida. Por favor, selecciona un número de la lista.");
        return;
    }

    const selectedService = services[selectedIndex];
    userState.data.selectedServiceId = selectedService.id;

    console.log(`[InfoFlow] User ${numero} selected service: ${selectedService.nombre} (ID: ${selectedService.id})`);
    // No pasamos texto_contenido aquí porque es el inicio del flujo de opciones principales,
    // y el texto_contenido de la opción padre (el servicio) no es relevante para la lista de opciones.
    await listOptions(client, numero, userState, connection, { service_id: selectedService.id });
}

/**
 * Lista las opciones (principales o sub-opciones de menú) para un servicio o una opción padre.
 * Esta función reemplaza y generaliza a listPrincipalOptions.
 * @param {object} client - Instancia del cliente de WhatsApp.
 * @param {string} numero - ID de WhatsApp del usuario.
 * @param {object} userState - Objeto de estado del usuario.
 * @param {object} connection - Pool de conexión a la BD.
 * @param {object} filterParams - Objeto con service_id o parent_opcion_id.
 * @param {string|null} [introText=null] - Texto introductorio a mostrar antes de la lista de opciones.
 */
async function listOptions(client, numero, userState, connection, filterParams, introText = null) {
    console.log(`[Options List] Called with filterParams:`, filterParams);
    console.log(`[Options List] Intro text:`, introText);
    try {
        let response;
        let action;

        if (filterParams.service_id) {
            action = "getByServiceId";
            response = await callApi("opciones_principales_api.php", action, { service_id: filterParams.service_id });
        } else if (filterParams.parent_opcion_id) {
            action = "getByParentOpcionId";
            response = await callApi("opciones_principales_api.php", action, { parent_opcion_id: filterParams.parent_opcion_id });
        } else {
            await client.sendMessage(numero, "Error interno: No se especificó un filtro válido para listar opciones.");
            userState.step = null;
            userState.data = {};
            return;
        }

        console.log(`[Options List] API Response success: ${response.success}, data length: ${response.data ? response.data.length : 0}`);
        console.log(`[Options List] Full API Response data:`, JSON.stringify(response.data, null, 2));


        if (!response.success || !response.data || response.data.length === 0) {
            let msg = "No hay más opciones disponibles aquí. Por favor, escribe *0* para volver atrás.";
            await client.sendMessage(numero, msg);
            return;
        }

        userState.data.currentOptions = response.data;

        const lastHistoryItem = userState.data.navigationHistory[userState.data.navigationHistory.length - 1];
        const isSameStep = lastHistoryItem && lastHistoryItem.step === 'info_principal_option_selection' &&
                           JSON.stringify(lastHistoryItem.params) === JSON.stringify(filterParams);

        if (!isSameStep) {
             userState.data.navigationHistory.push({ step: 'info_principal_option_selection', params: filterParams });
        }

        let message = "";
        if (introText) {
            message += `${introText}\n\n`; // Agrega el texto introductorio si existe
        }
        
        message += "Selecciona una opción:\n\n"; // Título para la lista de opciones

        response.data.forEach((opcion, index) => {
            message += `${index + 1}️⃣ ${opcion.texto_opcion}\n`;
        });
        message += "\nEscribe *0* para volver a la opción anterior.";
        message += "\nEscribe */cancelar* para salir de este flujo.";

        await client.sendMessage(numero, message);
        userState.step = 'info_principal_option_selection';
        console.log(`[InfoFlow] User ${numero} is in step: ${userState.step}`);

    } catch (error) {
        console.error(`❌ Error listing options for ${numero} with params ${JSON.stringify(filterParams)}:`, error);
        await client.sendMessage(numero, "Hubo un error al cargar las opciones. Por favor, intenta de nuevo más tarde.");
        if (userState.data.navigationHistory.length > 1) {
            userState.data.navigationHistory.pop();
            await goBack(client, numero, userState, connection);
        } else {
            userState.step = null;
            userState.data = {};
        }
    }
}

/**
 * Maneja la selección de una opción principal o sub-opción de menú.
 * @param {object} client - Instancia del cliente de WhatsApp.
 * @param {object} message - Objeto del mensaje de WhatsApp.
 * @param {object} userState - Objeto de estado del usuario.
 * @param {object} connection - Pool de conexión a la BD.
 */
async function handleOpcionPrincipalSelection(client, message, userState, connection) {
    const numero = message.from;
    const messageBody = message.body.trim();

    if (messageBody === '0') {
        return goBack(client, numero, userState, connection);
    }

    const selectedIndex = parseInt(messageBody) - 1;
    const currentOptions = userState.data.currentOptions;

    if (isNaN(selectedIndex) || selectedIndex < 0 || selectedIndex >= currentOptions.length) {
        await client.sendMessage(numero, "Opción inválida. Por favor, selecciona un número de la lista.");
        return;
    }

    const selectedOpcion = currentOptions[selectedIndex];
    userState.data.selectedOpcionId = selectedOpcion.id;

    console.log(`[InfoFlow] User ${numero} selected option: ${selectedOpcion.texto_opcion} (ID: ${selectedOpcion.id})`);
    console.log(`[InfoFlow] Selected Option Details:`, selectedOpcion);
    console.log(`[InfoFlow] selectedOpcion.paso_asociado_id: ${selectedOpcion.paso_asociado_id}`);
    console.log(`[InfoFlow] selectedOpcion.texto_contenido: ${selectedOpcion.texto_contenido}`);

    try {
        // PRIORIDAD 1: Verificar si esta opción tiene opciones hijas (sub-menús)
        const childOptionsResponse = await callApi("opciones_principales_api.php", "getByParentOpcionId", { parent_opcion_id: selectedOpcion.id });

        if (childOptionsResponse.success && childOptionsResponse.data && childOptionsResponse.data.length > 0) {
            console.log(`[InfoFlow] Option has child options. Listing them (parent_opcion_id: ${selectedOpcion.id}).`);
            // Pasamos el texto_contenido de la opción actual como introText para el siguiente menú
            await listOptions(client, numero, userState, connection, { parent_opcion_id: selectedOpcion.id }, selectedOpcion.texto_contenido);
        } else if (selectedOpcion.paso_asociado_id) {
            // PRIORIDAD 2: Mostrar contenido de paso asociado si existe
            console.log(`[InfoFlow] Option has paso_asociado_id. Displaying content.`);
            await displayContenidoPaso(client, numero, userState, connection, selectedOpcion.paso_asociado_id);
        } else if (selectedOpcion.texto_contenido) {
            // PRIORIDAD 3: Mostrar texto_contenido directo si no hay sub-opciones ni paso asociado
            console.log(`[InfoFlow] Option has texto_contenido. Displaying direct content.`);
            await displayDirectContent(client, numero, userState, selectedOpcion.texto_contenido);
        } else {
            // Fallback si no hay opciones hijas, ni paso asociado, ni contenido directo
            await client.sendMessage(numero, `No hay más información disponible para "${selectedOpcion.texto_opcion}". Por favor, escribe *0* para volver atrás.`);
            userState.step = 'info_principal_option_selection';
        }
    } catch (error) {
        console.error(`❌ Error handling option selection for ${numero}:`, error);
        await client.sendMessage(numero, "Hubo un error al procesar tu selección. Por favor, intenta de nuevo más tarde.");
        if (userState.data.navigationHistory.length > 1) {
            userState.data.navigationHistory.pop();
            await goBack(client, numero, userState, connection);
        } else {
            userState.step = null;
            userState.data = {};
        }
    }
}

/**
 * Muestra el contenido de un paso asociado.
 * @param {object} client - Instancia del cliente de WhatsApp.
 * @param {string} numero - ID de WhatsApp del usuario.
 * @param {object} userState - Objeto de estado del usuario.
 * @param {object} connection - Pool de conexión a la BD.
 * @param {number} contenidoId - ID del contenido de paso a mostrar.
 */
async function displayContenidoPaso(client, numero, userState, connection, contenidoId) {
    try {
        const response = await callApi("contenido_pasos_api.php", "getById", { id: contenidoId });

        if (!response.success || !response.data) {
            await client.sendMessage(numero, "No se pudo cargar el contenido asociado. Por favor, intenta de nuevo o vuelve atrás.");
            return;
        }

        const contenido = response.data;
        userState.data.selectedContenidoPasoId = contenido.id;
        const lastHistoryItem = userState.data.navigationHistory[userState.data.navigationHistory.length - 1];
        const isSameContentStep = lastHistoryItem && lastHistoryItem.step === 'info_content_display' &&
                                  lastHistoryItem.params.contenido_id === contenido.id;

        if (!isSameContentStep) {
            userState.data.navigationHistory.push({ step: 'info_content_display', params: { contenido_id: contenido.id } });
        }

        let message = `*${contenido.titulo}*\n\n${contenido.texto_completo}\n\n`;

        message += "\nEscribe *0* para volver a la opción anterior.";
        message += "\nEscribe */cancelar* para salir de este flujo.";

        await client.sendMessage(numero, message);
        userState.step = 'info_content_display';
        console.log(`[InfoFlow] User ${numero} is in step: ${userState.step}`);

    } catch (error) {
        console.error(`❌ Error displaying contenido paso for ${numero}:`, error);
        await client.sendMessage(numero, "Hubo un error al mostrar el contenido. Por favor, intenta de nuevo más tarde.");
    }
}

/**
 * Muestra contenido directamente desde la opción principal (texto_contenido).
 * @param {object} client - Instancia del cliente de WhatsApp.
 * @param {string} numero - ID de WhatsApp del usuario.
 * @param {object} userState - Objeto de estado del usuario.
 * @param {string} contentText - El texto de contenido directo a mostrar.
 */
async function displayDirectContent(client, numero, userState, contentText) {
    let message = `${contentText}\n\n`;
    message += "Escribe *0* para volver a la opción anterior.";
    message += "\nEscribe */cancelar* para salir de este flujo.";
    await client.sendMessage(numero, message);

    const lastHistoryItem = userState.data.navigationHistory[userState.data.navigationHistory.length - 1];
    const isSameDirectContentStep = lastHistoryItem && lastHistoryItem.step === 'info_content_display' &&
                                    lastHistoryItem.params.direct_content_text === contentText;

    if (!isSameDirectContentStep) {
        userState.data.navigationHistory.push({ step: 'info_content_display', params: { direct_content_text: contentText } });
    }

    userState.step = 'info_content_display';
    console.log(`[InfoFlow] User ${numero} is in step: ${userState.step}`);
}


/**
 * Maneja la selección de una sub-opción (de la tabla sub_opciones).
 * NOTA: Esta función ahora solo se usaría si las sub_opciones son acciones
 * específicas *después* de un contenido, no para la navegación de menú anidado.
 * @param {object} client - Instancia del cliente de WhatsApp.
 * @param {object} message - Objeto del mensaje de WhatsApp.
 * @param {object} userState - Objeto de estado del usuario.
 * @param {object} connection - Pool de conexión a la BD.
 */
async function handleSubOpcionSelection(client, message, userState, connection) {
    const numero = message.from;
    const messageBody = message.body.trim();

    if (messageBody === '0') {
        return goBack(client, numero, userState, connection); // Volver al contenido padre
    }

    const selectedIndex = parseInt(messageBody) - 1;
    const currentOptions = userState.data.currentOptions; // Las sub-opciones que se mostraron

    if (isNaN(selectedIndex) || selectedIndex < 0 || selectedIndex >= currentOptions.length) {
        await client.sendMessage(numero, "Opción inválida. Por favor, selecciona un número de la lista.");
        return;
    }

    const selectedSubOpcion = currentOptions[selectedIndex];
    console.log(`[InfoFlow] User ${numero} selected sub-option: ${selectedSubOpcion.texto_sub_opcion} (ID: ${selectedSubOpcion.id})`);

    if (selectedSubOpcion.paso_destino_id) {
        await displayContenidoPaso(client, numero, userState, connection, selectedSubOpcion.paso_destino_id);
    } else {
        await client.sendMessage(numero, "Esta sub-opción no tiene un destino específico. Por favor, escribe *0* para volver atrás.");
        // Permanece en el mismo paso para que el usuario pueda volver
    }
}

/**
 * Maneja la acción de "volver atrás" en el flujo de información.
 * @param {object} client - Instancia del cliente de WhatsApp.
 * @param {string} numero - ID de WhatsApp del usuario.
 * @param {object} userState - Objeto de estado del usuario.
 * @param {object} connection - Pool de conexión a la BD.
 */
async function goBack(client, numero, userState, connection) {
    // Elimina el paso actual del historial
    userState.data.navigationHistory.pop();

    if (userState.data.navigationHistory.length === 0) {
        // Si el historial está vacío después de hacer pop, significa que estamos volviendo al menú principal del bot.
        await client.sendMessage(numero, "Volviendo al menú principal.");
        userState.step = null; // Reinicia el paso para el menú principal del bot
        userState.data = {}; // Limpia todos los datos del flujo de info
        return;
    }

    const previousStep = userState.data.navigationHistory[userState.data.navigationHistory.length - 1];
    console.log(`[InfoFlow] Going back to previous step:`, previousStep);

    switch (previousStep.step) {
        case 'info_service_selection':
            await initiateInfoFlow(client, numero, userState, connection);
            break;
        case 'info_principal_option_selection':
            // Re-listar las opciones principales o sub-niveles de opciones principales
            await listOptions(client, numero, userState, connection, previousStep.params);
            break;
        case 'info_content_display':
            // Si el paso anterior fue mostrar un contenido (ya sea de contenido_pasos o directo)
            if (previousStep.params.contenido_id) {
                await displayContenidoPaso(client, numero, userState, connection, previousStep.params.contenido_id);
            } else if (previousStep.params.direct_content_text) {
                await displayDirectContent(client, numero, userState, previousStep.params.direct_content_text);
            }
            break;
        // El caso 'info_sub_option_selection' ya no es necesario aquí, ya que handleSubOpcionSelection
        // siempre lleva a un 'info_content_display' o permanece en el mismo estado.
        // La lógica de volver desde una sub-opción ahora se maneja regresando al 'info_content_display' anterior.
        default:
            // Caso por defecto: si algo sale mal, volver al menú principal del bot
            await client.sendMessage(numero, "No se pudo volver atrás. Volviendo al menú principal.");
            userState.step = null;
            userState.data = {};
            break;
    }
}


module.exports = {
    initiateInfoFlow,
    handleServiceSelection,
    handleOpcionPrincipalSelection,
    handleSubOpcionSelection,
    displayContenidoPaso, // Exportar si necesitas llamarla directamente
    displayDirectContent, // Exportar si necesitas llamarla directamente
    goBack // Exportar para el comando /cancelar o para uso general
};
