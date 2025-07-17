/**
 * @file bot.js
 * @description Archivo principal del bot de WhatsApp. Inicializa el cliente, maneja eventos
 * (QR, conexión, desconexión, mensajes, llamadas) y gestiona el estado de los usuarios.
 * Incluye funcionalidad para que los agentes inicien chats y para rehidratar sesiones activas al inicio.
 */

const devMode = false;
const allowedNumbers = ['5492214380474@c.us', '5492212024818@c.us', '5492215065414@c.us'];

const { Client, LocalAuth, MessageMedia } = require('whatsapp-web.js');
const qrcode = require('qrcode');
const qrcodeTerminal = require('qrcode-terminal');
const fs = require('fs').promises; // Usar fs.promises para operaciones asíncronas
const path = require('path');
const moment = require('moment-timezone');
const cron = require('node-cron');

// --- Dependencias Locales ---
const connection = require('./db_conn.js'); // Pool de conexión a BD (con promesas)
const { encryptData, decryptData } = require('./encryption.js'); // Funciones de encriptación

// Importar funciones de manejo de chat y otros comandos
const AgentHandler = require('./commands/hablar_agente/handler_chat.js');
const infoEspecialidad = require('./commands/info_especialidad/info_especialidad.js'); // ¡NUEVO! Importar el módulo de información

// --- Constantes y Configuración ---
const QR_FOLDER = path.join(__dirname, 'qrcodes');
const MEDIA_BASE_DIR = path.join(__dirname, '../whatsapp_files'); // ¡VERIFICAR RUTA!
const INACTIVITY_TIMEOUT_MINUTES = 30; // Minutos para timeout de inactividad
const DAYS_TO_KEEP_MEDIA = 30; // Días a mantener los archivos multimedia antes de limpiar
const AGENT_SYNC_INTERVAL_MS = 2000; // Intervalo para buscar mensajes DEL AGENTE para enviar al paciente (2 segundos)
const AGENT_INITIATED_CHECK_INTERVAL_MS = 7000; // Intervalo para buscar chats iniciados POR AGENTE (7 segundos)

// --- Cliente de WhatsApp ---
const client = new Client({
    authStrategy: new LocalAuth(),
    puppeteer: {
        // headless: false, // Descomentar para ver el navegador
        args: ['--no-sandbox', '--disable-setuid-sandbox'], // Necesario en algunos entornos Linux/Docker
    },
    webVersionCache: { // Para intentar mantener la sesión más estable
        type: 'remote',
        remotePath: 'https://raw.githubusercontent.com/wppconnect-team/wa-version/main/html/2.2412.54.html',
    }
});

// --- Gestión de Estado de Usuarios ---
const userStates = new Map();

// --- Funciones Auxiliares ---
/**
 * Obtiene o inicializa el estado para un usuario específico.
 * @param {string} numero - El ID de WhatsApp del usuario (ej: 'xxxxxxxx@c.us').
 * @returns {object} El objeto de estado del usuario.
 */
function getUserState(numero) {
    if (!userStates.has(numero)) {
        userStates.set(numero, {
            step: null,
            data: {},
            inactivityTimerId: null,
            syncAgentTimerId: null, // Timer para syncAgentMessages Y para startWaitingForAgent
            chatId: null,
            isAgentChatting: false,
            isWaitingAgent: false,     // Indica si está esperando que un agente tome el chat 'pendiente'
            // agentInitiatedCheckTimerId: null, // Este timer es global, no por usuario
        });
        console.log(`[State] Estado inicializado para ${numero}. Total usuarios: ${userStates.size}`);
    }
    return userStates.get(numero);
}

/**
 * Reinicia o establece el timer de inactividad para un usuario.
 * @param {string} numero - El ID de WhatsApp del usuario.
 */
async function resetInactivityTimer(numero) {
    const userState = getUserState(numero);

    if (userState.inactivityTimerId) {
        clearTimeout(userState.inactivityTimerId);
    }

    userState.inactivityTimerId = setTimeout(async () => {
        if (userState.chatId) {
            try {
                const [chatStatusRows] = await connection.query(
                    "SELECT estado FROM chats WHERE id = ?",
                    [userState.chatId]
                );
                if (chatStatusRows.length > 0) {
                    const currentDbChatState = chatStatusRows[0].estado;
                    // Si el chat está activo ('chatting'), pendiente de agente, o iniciado por agente,
                    // el bot no lo cierra por inactividad del *paciente*. El agente debe cerrarlo.
                    if (currentDbChatState === 'chatting' || currentDbChatState === 'pendiente' || currentDbChatState === 'agent_initiated') {
                        console.log(`[Inactivity] Timeout for ${numero}, but chat ${userState.chatId} is '${currentDbChatState}'. Timer reset, session remains active for agent.`);
                        await resetInactivityTimer(numero); // Reinicia el timer, pero no cierra la sesión de agente.
                        return;
                    }
                }
            } catch (dbError) {
                console.error(`[Inactivity] Error checking chat status for ${userState.chatId} before timeout:`, dbError);
            }
        }

        // Si no hay chatId, o el estado del chat no es uno que deba protegerse de la inactividad del paciente:
        console.log(`[Inactivity] Timeout for ${numero}. Cleaning up state and notifying.`);
        // stopAgentChatSession se encarga de limpiar timers y, si es por timeout, actualizar BD.
        await stopAgentChatSession(numero, userState, connection, true); // true para indicar que es por inactividad del bot

        // Solo borrar el estado del mapa si stopAgentChatSession no lo hizo (o para asegurar)
        if (userStates.has(numero)) {
            userStates.delete(numero);
            console.log(`[State] State for ${numero} deleted due to inactivity. Total usuarios: ${userStates.size}`);
        }

        try {
            await client.sendMessage(numero, "📴 La conversación se ha cerrado por inactividad. Puedes escribir nuevamente cuando necesites ayuda.");
        } catch (sendError) {
            console.error(`[Inactivity] Error sending timeout message to ${numero}:`, sendError);
        }

    }, INACTIVITY_TIMEOUT_MINUTES * 60 * 1000);
}

/**
 * Limpia el timer de inactividad para un usuario.
 * @param {string} numero - El ID de WhatsApp del usuario.
 */
function clearInactivityTimer(numero) {
    if (userStates.has(numero)) {
        const userState = userStates.get(numero);
        if (userState.inactivityTimerId) {
            clearTimeout(userState.inactivityTimerId);
            userState.inactivityTimerId = null;
        }
    }
}

/**
 * Verifica si la hora actual está dentro del horario de atención definido en la BD.
 * @param {string} numero - El ID de WhatsApp del usuario (para logging).
 * @returns {Promise<{available: boolean, message?: string}>}
 */
async function checkAttentionHours(numero) {
    try {
        const now = moment().tz("America/Argentina/Buenos_Aires");
        const dayName = now.format("dddd");
        const currentTime = now.format("HH:mm:ss");

        const daysMap = { "Monday": "Lunes", "Tuesday": "Martes", "Wednesday": "Miércoles", "Thursday": "Jueves", "Friday": "Viernes", "Saturday": "Sábado", "Sunday": "Domingo" };
        const dayNameInDB = daysMap[dayName] || dayName;

        const [rows] = await connection.query(
            "SELECT enabled, start_time, end_time FROM atention_days_turnos WHERE day_name = ? LIMIT 1",
            [dayNameInDB]
        );

        if (rows.length === 0 || !rows[0].enabled) {
            console.log(`[Hours] Attention disabled or not defined for today (${dayNameInDB}) for user ${numero}.`);
            return { available: false, message: "Lo sentimos, nuestro servicio no está habilitado en este momento." };
        }
        let { start_time, end_time } = rows[0];
        // Convertir a formato 12 horas sin segundos y con am/pm
        const formatTo12Hour = (timeStr) => {
            // timeStr esperado: "HH:mm:ss"
            const [hour, minute] = timeStr.split(':');
            const dateObj = moment({ hour, minute });
            return dateObj.format('hh:mm A');
        };
        const startTime12h = formatTo12Hour(start_time);
        const endTime12h = formatTo12Hour(end_time);
        const isWithinHours = currentTime >= start_time && currentTime <= end_time;

        if (!isWithinHours) {

            return { available: false, message: `Lo sentimos, estás fuera del horario de atención de hoy (${startTime12h} - ${endTime12h}). \n \n Nuestro horario de atención es de lunes a viernes de ${startTime12h} a ${endTime12h}. \n \n Si necesitas ayuda con otra cosa, no dudes en preguntar.` };
        }
        return { available: true };

    } catch (error) {
        console.error(`❌ Error checking attention hours for ${numero}:`, error);
        return { available: false, message: "Hubo un problema al verificar nuestro horario de atención. Intenta más tarde." };
    }
}


/**
 * Inicia formalmente la sesión de chat con un agente.
 * Esta función es llamada cuando el PACIENTE completa los datos o confirma identidad.
 * @param {object} client - Instancia del cliente de WhatsApp.
 * @param {string} numero - ID de WhatsApp del usuario.
 * @param {number} pacienteId - ID del paciente en la tabla 'pacientes_chat'.
 * @param {object} userState - Objeto de estado del usuario.
 * @param {object} connection - Pool de conexión a la BD.
 * @param {object|null} [initialData=null] - Datos recolectados (si es consulta de tercero) para guardar como mensaje inicial.
 */
async function startAgentChatSession(client, numero, pacienteId, userState, connection, initialData = null) {
    console.log(`[Agent Chat] Starting session for user ${numero}, pacienteId ${pacienteId}.`);
    try {
        // Busca si ya existe un chat NO finalizado/cancelado para este paciente_id
        const [existingChats] = await connection.query(
            "SELECT id, estado FROM chats WHERE paciente_id = ? AND estado NOT IN ('finalizado', 'finalizado_inactividad', 'cancelado') ORDER BY fecha_inicio DESC LIMIT 1",
            [pacienteId]
        );

        let chatId;
        let chatState;

        if (existingChats.length > 0) {
            chatId = existingChats[0].id;
            chatState = existingChats[0].estado;
            console.log(`[Agent Chat] Found existing chat ID ${chatId} with state '${chatState}' for pacienteId ${pacienteId}.`);
            if (chatState === 'chatting') {
                await client.sendMessage(numero, "👋 ¡Hola! Ya tienes una conversación activa con un agente. Puedes continuar escribiendo aquí.");
            } else if (chatState === 'pendiente' || chatState === 'agent_initiated') {
                // Si estaba pendiente o iniciado por agente, y el usuario escribe, es una continuación.
                // El bot ya debería estar esperando/sincronizando.
                await client.sendMessage(numero, "👍 Tu solicitud para hablar con un agente ya está registrada. Te contactarán pronto o puedes continuar la conversación si ya te respondieron.");
            }
        } else {
            // No hay chat activo/pendiente, crear uno nuevo
            const [result] = await connection.query(
                "INSERT INTO chats (paciente_id, numero, estado, fecha_inicio) VALUES (?, ?, 'pendiente', NOW())",
                [pacienteId, numero]
            );
            chatId = result.insertId;
            chatState = 'pendiente';
            console.log(`[Agent Chat] New chat created with ID ${chatId}, state 'pendiente' for pacienteId ${pacienteId}.`);
            await client.sendMessage(numero, "👍 ¡Gracias! Hemos registrado tus datos. Un agente se comunicará contigo pronto. Por favor, espera. 😊");

            if (initialData) { // Si es una consulta para un tercero, guardar los datos del tercero
                const initialMessageContent = `Consulta para tercero (registrada por ${numero}):\n` +
                    Object.entries(initialData)
                        .filter(([key]) => key !== 'isThirdParty' && key !== 'pacienteId')
                        .map(([key, value]) => `${key.replace(/_/g, ' ')}: ${value || 'N/A'}`)
                        .join('\n');
                const encryptedInitialMessage = encryptData(initialMessageContent);
                await connection.query(
                    "INSERT INTO wsp_messages (numero, mensaje, chat_id, estado, remitente, timestamp) VALUES (?, ?, ?, 'recibido', 'sistema', NOW())",
                    [numero, encryptedInitialMessage, chatId] // 'numero' aquí es el del que consulta
                );
                console.log(`[Agent Chat] Initial third-party data saved as message for chat ID ${chatId}.`);
            }
        }

        // Actualizar estado local del usuario
        userState.step = null; // Limpiar paso de recolección
        userState.data = {};   // Limpiar datos recolectados
        userState.chatId = chatId;
        userState.isAgentChatting = (chatState === 'chatting');
        userState.isWaitingAgent = (chatState === 'pendiente' || chatState === 'agent_initiated');

        if (userState.isAgentChatting) {
            console.log(`[Agent Chat] Chat ${chatId} is already 'chatting'. Ensuring message sync is active.`);
            startAgentMessageSync(client, numero, userState, connection);
        } else if (userState.isWaitingAgent) {
            console.log(`[Agent Chat] Chat ${chatId} is '${chatState}'. Starting/ensuring status check for agent pickup.`);
            startWaitingForAgent(client, numero, userState, connection);
        }
        await resetInactivityTimer(numero); // Reiniciar inactividad ahora que está en espera/chat

    } catch (error) {
        console.error(`❌ Error in startAgentChatSession for ${numero}, pacienteId ${pacienteId}:`, error);
        userState.step = null; userState.data = {}; userState.chatId = null; userState.isAgentChatting = false; userState.isWaitingAgent = false;
        await client.sendMessage(numero, "⚠️ Ocurrió un error al iniciar la sesión de chat. Por favor, intenta de nuevo más tarde.");
    }
}

/**
 * Inicia un intervalo para verificar si un chat 'pendiente' o 'agent_initiated' ha sido tomado por un agente.
 * @param {object} client - Instancia del cliente de WhatsApp.
 * @param {string} numero - ID de WhatsApp del usuario.
 * @param {object} userState - Objeto de estado del usuario.
 * @param {object} connection - Pool de conexión a la BD.
 */
function startWaitingForAgent(client, numero, userState, connection) {
    if (userState.syncAgentTimerId) {
        clearInterval(userState.syncAgentTimerId);
        console.log(`[Agent Wait] Cleared previous sync/wait timer for ${numero} before starting new wait.`);
    }
    console.log(`[Agent Wait] Starting status check interval for user ${numero}, chat ${userState.chatId}`);

    userState.syncAgentTimerId = setInterval(async () => {
        const currentChatId = userState.chatId; // Usar el chatId del estado actual al momento de la ejecución del intervalo
        if (!userState.isWaitingAgent || !currentChatId) { // Si ya no está esperando o no hay chatId, detener.
            console.warn(`[Agent Wait] Interval for ${numero} but no longer waiting or no chatId. Stopping.`);
            clearInterval(userState.syncAgentTimerId);
            userState.syncAgentTimerId = null;
            return;
        }

        try {
            const [chatStatus] = await connection.query(
                "SELECT estado, asignado FROM chats WHERE id = ?", // <--- CORREGIDO: Debería ser 'asignado' si esa es la columna del DNI del agente
                [currentChatId]
            );

            if (chatStatus.length === 0) {
                console.error(`[Agent Wait] Chat ID ${currentChatId} not found for user ${numero}. Stopping check.`);
                await stopAgentChatSession(numero, userState, connection); // Limpiar estado y timers
                return;
            }
            const newStatus = chatStatus[0].estado;
            const agentDni = chatStatus[0].asignado; // <--- CORREGIDO: Debería ser 'asignado'

            if (newStatus === 'chatting') { // El agente (o el bot via checkForAgentInitiatedChats) cambió el estado
                console.log(`[Agent Wait] Agent (DNI: ${agentDni || 'N/A'}) has taken chat ${currentChatId} for user ${numero}. Status: ${newStatus}.`);
                clearInterval(userState.syncAgentTimerId); // Detener este timer de espera
                userState.syncAgentTimerId = null;

                userState.isWaitingAgent = false;
                userState.isAgentChatting = true; // Ahora está chateando activamente

                let agentName = "Un agente";
                if (agentDni) {
                    try {
                        // Asumiendo que la tabla 'personal' tiene 'dni' y 'nombre' (encriptado)
                        const [agentNameRows] = await connection.query("SELECT nombre FROM personal WHERE dni = ? LIMIT 1", [agentDni]);
                        
                    } catch (nameError) { console.error("Error fetching agent name:", nameError); }
                }
                await client.sendMessage(numero, `💬 ¡${agentName} se ha unido al chat! Puedes empezar a escribir tu consulta.`);

                startAgentMessageSync(client, numero, userState, connection); // Iniciar el sync de mensajes DEL AGENTE
                await resetInactivityTimer(numero); // Reiniciar inactividad al iniciar chat real

            } else if (newStatus === 'finalizado' || newStatus === 'finalizado_inactividad' || newStatus === 'cancelado') {
                console.warn(`[Agent Wait] Chat ${currentChatId} for ${numero} was ${newStatus} by external action while waiting. Stopping.`);
                await stopAgentChatSession(numero, userState, connection); // Limpiar estado
            }
        } catch (error) {
            console.error(`❌ Error during agent wait check for user ${numero}, chat ${currentChatId}:`, error);
        }
    }, 5000); // Verificar cada 5 segundos
}

/**
 * Inicia el intervalo para sincronizar (enviar) mensajes del agente al paciente.
 * @param {object} client - Instancia del cliente de WhatsApp.
 * @param {string} numero - ID de WhatsApp del usuario.
 * @param {object} userState - Objeto de estado del usuario.
 * @param {object} connection - Pool de conexión a la BD.
 */
async function startAgentMessageSync(client, numero, userState, connection) {
    if (userState.syncAgentTimerId) {
        clearInterval(userState.syncAgentTimerId);
        console.log(`[Agent Sync] Cleared previous sync/wait timer for ${numero} before starting new message sync.`);
    }
    await resetInactivityTimer(numero);
    console.log(`[Agent Sync] Starting message sync interval for user ${numero}, chat ${userState.chatId}`);

    userState.syncAgentTimerId = setInterval(async () => {
        if (!userState.isAgentChatting || !userState.chatId) {
            console.warn(`[Agent Sync] Interval for ${numero} but no longer chatting or no chatId. Stopping.`);
            clearInterval(userState.syncAgentTimerId);
            userState.syncAgentTimerId = null;
            return;
        }
        await AgentHandler.syncAgentMessages(client, numero, userState, connection, stopAgentChatSession, MEDIA_BASE_DIR);
    }, AGENT_SYNC_INTERVAL_MS);
}

/**
 * Limpia el estado relacionado con el chat del agente y detiene los timers.
 * @param {string} numero - ID de WhatsApp del usuario.
 * @param {object} userState - Objeto de estado del usuario.
 * @param {object} connection - Pool de conexión (para marcar chat como finalizado si es necesario).
 * @param {boolean} [isTimeout=false] - Indica si se llama debido a inactividad del bot.
 */
async function stopAgentChatSession(numero, userState, connection, isTimeout = false) {
    console.log(`[Agent Chat] Stopping agent chat session state for user ${numero}. Called by timeout: ${isTimeout}`);
    if (userState.syncAgentTimerId) {
        clearInterval(userState.syncAgentTimerId);
        userState.syncAgentTimerId = null;
        console.log(`[Agent Chat] Agent sync/wait timer cleared for ${numero}.`);
    }

    const chatIdToClose = userState.chatId;

    userState.isAgentChatting = false;
    userState.isWaitingAgent = false;
    userState.chatId = null;
    userState.step = null;
    userState.data = {};

    if (isTimeout && chatIdToClose) {
        try {
            const [updateResult] = await connection.query(
                "UPDATE chats SET estado = 'finalizado_inactividad', fecha_cierre = NOW() WHERE id = ? AND estado IN ('chatting', 'pendiente', 'agent_initiated')",
                [chatIdToClose]
            );
            if (updateResult.affectedRows > 0) {
                console.log(`[Inactivity] Chat ID ${chatIdToClose} marked as 'finalizado_inactividad' in DB.`);
            } else {
                console.log(`[Inactivity] Chat ID ${chatIdToClose} was not 'chatting', 'pendiente', or 'agent_initiated', or not found. No DB update for inactivity.`);
            }
        } catch (dbError) {
            console.error(`[Inactivity] Error marking chat ${chatIdToClose} as 'finalizado_inactividad':`, dbError);
        }
    }
    if (!isTimeout) {
        await resetInactivityTimer(numero);
    }
}

/**
 * NUEVA FUNCIÓN: Verifica periódicamente chats marcados como 'agent_initiated' en la BD.
 * Si los encuentra, activa el chat desde el lado del bot.
 */
async function checkForAgentInitiatedChats() {
    try {
        const [chatsToActivate] = await connection.query(
            // Asegúrate que 'c.asignado' sea la columna correcta para el DNI del agente
            `SELECT c.id as chatId, c.numero, c.paciente_id, c.estado, c.asignado as asignado, p_personal.nombre as nombre_agente_enc 
             FROM chats c
             LEFT JOIN personal p_personal ON c.asignado = p_personal.dni COLLATE utf8mb4_unicode_ci
             WHERE c.estado = 'agent_initiated'`
        );

        if (chatsToActivate.length === 0) {
            return;
        }

        console.log(`[Agent Initiated Check] Found ${chatsToActivate.length} chat(s) to activate by agent.`);

        for (const chat of chatsToActivate) {
            const numero = chat.numero;
            const chatId = chat.chatId;
            const agentDni = chat.asignado; // Usar el alias 'asignado' de la query
            let agentName = "Un agente";

            if (chat.nombre_agente_enc) {
                try {
                    agentName = chat.nombre_agente_enc || agentName;
                } catch (e) { console.error(`Error decrypting agent name for DNI ${agentDni}:`, e); }
            }

            console.log(`[Agent Initiated Check] Activating chat ID ${chatId} for user ${numero} with agent ${agentDni}.`);

            const userState = getUserState(numero);

            if ((userState.isAgentChatting || userState.isWaitingAgent) && userState.chatId) {
                if (userState.chatId === chatId) {
                    console.log(`[Agent Initiated Check] Chat ${chatId} for ${numero} is already being handled (state: ${userState.isAgentChatting ? 'chatting' : 'waiting'}). Ensuring it's active.`);
                    await connection.query("UPDATE chats SET estado = 'chatting', asignado = ? WHERE id = ?", [agentDni, chatId]); // Usar 'asignado'

                    userState.isWaitingAgent = false;
                    userState.isAgentChatting = true;
                    userState.chatId = chatId;

                    if (!userState.syncAgentTimerId || !userState.isAgentChatting) {
                        console.log(`[Agent Initiated Check] Restarting agent message sync for already handled chat ${chatId}.`);
                        startAgentMessageSync(client, numero, userState, connection);
                    }
                    await resetInactivityTimer(numero);
                    continue;
                } else {
                    console.warn(`[Agent Initiated Check] User ${numero} was in a different chat (ID: ${userState.chatId}). Agent ${agentDni} is activating new chat (ID: ${chatId}). Stopping old chat state.`);
                    await stopAgentChatSession(numero, userState, connection);
                }
            }

            userState.chatId = chatId;
            userState.isAgentChatting = true;
            userState.isWaitingAgent = false;
            userState.step = null;

            await connection.query("UPDATE chats SET estado = 'chatting', asignado = ? WHERE id = ?", [agentDni, chatId]); // Usar 'asignado'
            console.log(`[Agent Initiated Check] Chat ${chatId} status updated to 'chatting' in DB.`);

            try {
                await client.sendMessage(numero, `👋 ¡Hola! ${agentName}, de la central de turnos, se ha unido para iniciar o continuar una conversación contigo.`);
                console.log(`[Agent Initiated Check] Notification sent to ${numero} for chat ${chatId}.`);
            } catch (sendError) {
                console.error(`❌ Error sending agent initiated notification to ${numero}:`, sendError);
            }

            startAgentMessageSync(client, numero, userState, connection);
            await resetInactivityTimer(numero);
        }

    } catch (error) {
        console.error("❌ Error in checkForAgentInitiatedChats:", error);
    }
}

/**
 * NUEVA FUNCIÓN: Rehidrata estados de chats activos/pendientes al inicio del bot.
 */
async function rehydrateActiveChats() {
    console.log('[Rehydrate] Attempting to rehydrate active/pending chats from DB...');
    try {
        const [activeChatsFromDB] = await connection.query(
            // Asegúrate que 'c.asignado' sea la columna correcta para el DNI del agente
            `SELECT c.id as chatId, c.numero, c.paciente_id, c.estado, c.asignado as asignado, p_personal.nombre as nombre_agente_enc
             FROM chats c
             LEFT JOIN personal p_personal ON c.asignado = p_personal.dni COLLATE utf8mb4_unicode_ci
             WHERE c.estado IN ('pendiente', 'chatting', 'agent_initiated')`
        );

        if (activeChatsFromDB.length === 0) {
            console.log('[Rehydrate] No active or pending chats found in DB to rehydrate.');
            return;
        }

        console.log(`[Rehydrate] Found ${activeChatsFromDB.length} chat(s) to rehydrate.`);

        for (const chat of activeChatsFromDB) {
            const numero = chat.numero;
            const chatId = chat.chatId;
            const dbChatState = chat.estado;
            const agentDni = chat.asignado; // Usar el alias 'asignado' de la query

            console.log(`[Rehydrate] Processing chat ID ${chatId} for user ${numero}, DB state: ${dbChatState}, agent: ${agentDni || 'N/A'}`);

            try {
                const userState = getUserState(numero);

                if ((userState.isAgentChatting || userState.isWaitingAgent) && userState.chatId && userState.chatId !== chatId) {
                    console.warn(`[Rehydrate] User ${numero} had an active local state for different chat ${userState.chatId}. Stopping it before rehydrating ${chatId}.`);
                    await stopAgentChatSession(numero, userState, connection);
                }

                userState.chatId = chatId;
                userState.step = null;
                userState.data = {};

                if (dbChatState === 'chatting') {
                    userState.isAgentChatting = true;
                    userState.isWaitingAgent = false;
                    console.log(`[Rehydrate] Chat ${chatId} for ${numero} is 'chatting'. Starting agent message sync.`);
                    startAgentMessageSync(client, numero, userState, connection);
                } else if (dbChatState === 'pendiente' || dbChatState === 'agent_initiated') {
                    userState.isAgentChatting = false;
                    userState.isWaitingAgent = true;
                    console.log(`[Rehydrate] Chat ${chatId} for ${numero} is '${dbChatState}'. Starting wait for agent pickup / processing.`);
                    startWaitingForAgent(client, numero, userState, connection);
                } else {
                    console.warn(`[Rehydrate] Chat ${chatId} for ${numero} has an unexpected state '${dbChatState}' during rehydration. Skipping timer start.`);
                }

                await resetInactivityTimer(numero);
                console.log(`[Rehydrate] Chat ID ${chatId} for ${numero} rehydrated. Local state: isAgentChatting=${userState.isAgentChatting}, isWaitingAgent=${userState.isWaitingAgent}`);

            } catch (chatError) {
                console.error(`❌ Error rehydrating individual chat ID ${chatId} for user ${numero}:`, chatError);
            }
        }
        console.log('[Rehydrate] Finished rehydrating all applicable chats.');

    } catch (error) {
        console.error("❌ Error in rehydrateActiveChats (main DB query):", error);
    }
}


/**
 * Elimina directorios de archivos multimedia antiguos.
 */
async function cleanOldMediaDirectories() {
    console.log(`[Cleanup] Starting cleanup of old media directories in: ${MEDIA_BASE_DIR}`);
    const thresholdDate = moment().subtract(DAYS_TO_KEEP_MEDIA, 'days').startOf('day');
    console.log(`[Cleanup] Deleting directories (YYYY-MM-DD) older than: ${thresholdDate.format('YYYY-MM-DD')}`);

    try {
        const items = await fs.readdir(MEDIA_BASE_DIR);
        let deletedCount = 0;
        for (const item of items) {
            const itemPath = path.join(MEDIA_BASE_DIR, item);
            try {
                const stats = await fs.stat(itemPath);
                if (stats.isDirectory() && /^\d{4}-\d{2}-\d{2}$/.test(item)) {
                    const dirDate = moment(item, 'YYYY-MM-DD').startOf('day');
                    if (dirDate.isValid() && dirDate.isBefore(thresholdDate)) {
                        await fs.rm(itemPath, { recursive: true, force: true });
                        deletedCount++;
                    }
                }
            } catch (itemError) {
                console.error(`❌ [Cleanup] Error processing item ${itemPath}:`, itemError.message);
            }
        }
        console.log(`[Cleanup] Cleanup finished. Deleted ${deletedCount} old directories.`);
    } catch (error) {
        if (error.code === 'ENOENT') {
            console.warn(`[Cleanup] Base directory ${MEDIA_BASE_DIR} not found. Skipping cleanup.`);
        } else {
            console.error(`❌ [Cleanup] Error reading base directory ${MEDIA_BASE_DIR}:`, error);
        }
    }
}

// --- Eventos del Cliente WhatsApp ---
client.on('qr', async (qr) => {
    console.log("⚡ Escanea este QR para conectar el bot:");
    qrcodeTerminal.generate(qr, { small: true });
    try {
        await fs.mkdir(QR_FOLDER, { recursive: true });
        const qrFilePath = path.join(QR_FOLDER, 'whatsapp-qr.png');
        await qrcode.toFile(qrFilePath, qr);
        console.log(`QR code saved to ${qrFilePath}`);
    } catch (err) {
        console.error("Error saving QR code image:", err);
    }
});

client.on('ready', async () => {
    console.log(`✅ Bot conectado como ${client.info.pushname || client.info.wid.user} (${client.info.wid._serialized})`);
    try {
        const qrFilePath = path.join(QR_FOLDER, 'whatsapp-qr.png');
        await fs.unlink(qrFilePath);
    } catch (err) {
        if (err.code !== 'ENOENT') { console.error("Error deleting QR on ready:", err); }
    }

    await cleanOldMediaDirectories();
    cron.schedule('0 3 * * *', () => {
        console.log('[Cron] Executing daily cleanup of old media directories...');
        cleanOldMediaDirectories();
    }, {
        timezone: "America/Argentina/Buenos_Aires"
    });
    console.log('[Cron] Media cleanup task scheduled daily at 3:00 AM (Buenos Aires time).');

    setInterval(checkForAgentInitiatedChats, AGENT_INITIATED_CHECK_INTERVAL_MS);
    console.log(`[System] Agent-initiated chat check started (Interval: ${AGENT_INITIATED_CHECK_INTERVAL_MS}ms).`);

    await rehydrateActiveChats();
});

client.on('disconnected', (reason) => {
    console.warn(`❌ Bot disconnected: ${reason}. Attempting to reconnect...`);
});

client.on('auth_failure', (msg) => {
    console.error('❌ AUTHENTICATION FAILURE:', msg);
    console.error('❌ Please delete the .wwebjs_auth folder and scan the QR code again.');
    process.exit(1);
});

client.on('change_state', state => {
    console.log(`[State Change] Client state changed to: ${state}`);
});


// --- Manejador Principal de Mensajes ---
client.on('message_create', async (message) => {
    // CORREGIDO: Filtro simplificado. Ignorar solo propios, de estado o grupos.
    if (message.id.fromMe || message.isStatus || message.from.endsWith('@g.us')) {
        return;
    }
    // Si necesitas filtrar para NÚMEROS ESPECÍFICOS durante pruebas, usa:
    
    if (!allowedNumbers.includes(message.from) && devMode) {
        console.log(`[Filter] Ignoring message from unallowed number: ${message.from}`);
        return;
    }

    const numero = message.from;
    const contact = await message.getContact();
    const senderName = contact.pushname || contact.name || numero;
    const messageBody = (message.body || '').trim();

    console.log(`\n📩 Message received from: ${senderName} (${numero}) | Type: ${message.type} | Body: "${messageBody.substring(0, 50)}..."`);

    const userState = getUserState(numero);
    await resetInactivityTimer(numero);

    const unhandledTypes = {
        'location': "No puedo procesar ubicaciones compartidas.",
        'vcard': "No puedo procesar contactos compartidos (vCard).",
        'livelocation': "No puedo procesar ubicaciones en tiempo real.",
        'call_log': null,
        'revoked': null,
        'unknown': null,
        'reaction': null,
        'gp2': null,
        'broadcast_notification': null,
        'e2e_notification': null,
        'notification_template': null,
    };
    if (unhandledTypes.hasOwnProperty(message.type)) {
        const rejectionMessage = unhandledTypes[message.type];
        if (rejectionMessage) {
            console.log(`[Filter] Rejecting type '${message.type}' from ${numero}. Sending message.`);
            try { await client.sendMessage(numero, rejectionMessage); }
            catch (sendError) { console.error(`❌ Error sending rejection message for type ${message.type} to ${numero}:`, sendError); }
        } else {
            console.log(`[Filter] Silently ignoring type '${message.type}' from ${numero}.`);
        }
        return;
    }


    try {
        // Si el usuario está en un chat con agente o esperando uno, priorizar el handler del agente
        if (userState.isAgentChatting) {
            console.log(`[Router] Message from ${numero} (chatId: ${userState.chatId}) routed to saveIncomingAgentChatMessage.`);
            await AgentHandler.saveIncomingAgentChatMessage(client, message, userState, connection, encryptData, MEDIA_BASE_DIR);
            return;
        }

        if (userState.isWaitingAgent) {
            console.log(`[Router] Message from ${numero} (chatId: ${userState.chatId}) while waiting for agent. Saving message.`);
            await AgentHandler.saveIncomingAgentChatMessage(client, message, userState, connection, encryptData, MEDIA_BASE_DIR);
            await client.sendMessage(numero, "⏳ He guardado tu mensaje. Por favor, espera a que un agente se conecte para responderte.");
            return;
        }

        if (userState.step && userState.step.startsWith('agent_')) {
            console.log(`[Router] Message from ${numero} routed to procesarRespuestaAgente (Step: ${userState.step}).`);
            await AgentHandler.procesarRespuestaAgente(client, message, userState, connection, encryptData, decryptData, startAgentChatSession);
            return;
        }

        // Manejar el comando /cancelar para cualquier flujo
        if (messageBody.toLowerCase() === '/cancelar') {
            console.log(`[Command] User ${numero} requested /cancelar.`);
            clearInactivityTimer(numero);
            await stopAgentChatSession(numero, userState, connection); // Asegura que el chat con agente se cierre si estaba activo
            userState.step = null;
            userState.data = {};
            await client.sendMessage(numero, "Acción cancelada. Volviendo al menú principal.");
            return; // Importante para no seguir procesando
        }

        // --- Manejo del flujo de Información de Especialidad ---
        if (userState.step && userState.step.startsWith('info_')) {
            console.log(`[Router] Message from ${numero} routed to infoEspecialidad handler (Step: ${userState.step}).`);
            switch (userState.step) {
                case 'info_service_selection':
                    await infoEspecialidad.handleServiceSelection(client, message, userState, connection);
                    break;
                case 'info_principal_option_selection':
                    await infoEspecialidad.handleOpcionPrincipalSelection(client, message, userState, connection);
                    break;
                case 'info_content_display':
                    // Si el usuario envía un 0, es para volver. Cualquier otra cosa, es un mensaje no esperado.
                    if (messageBody === '0') {
                        await infoEspecialidad.goBack(client, numero, userState, connection);
                    } else {
                        await client.sendMessage(numero, "Por favor, escribe *0* para volver a la opción anterior, o */cancelar* para ir al menú principal.");
                    }
                    break;
                case 'info_sub_option_selection':
                    await infoEspecialidad.handleSubOpcionSelection(client, message, userState, connection);
                    break;
                default:
                    console.warn(`[InfoFlow] Unhandled info step: ${userState.step} for ${numero}. Resetting to main menu.`);
                    await client.sendMessage(numero, "Hubo un problema con el flujo de información. Volviendo al menú principal.");
                    userState.step = null;
                    userState.data = {};
                    break;
            }
            return; // Terminar el procesamiento del mensaje aquí si fue manejado por infoEspecialidad
        }

        // --- Menú Principal del Bot ---
        if (!userState.step) {
            console.log(`[Router] New conversation or reset state for ${numero}. Sending welcome menu.`);
            let welcomeMessage = "Hola 👋, soy tu asistente virtual.\n¿Cómo puedo ayudarte hoy?\n\n";
            welcomeMessage += "1️⃣ Hablar con un Agente\n";
            welcomeMessage += "2️⃣ Información de Especialidad\n\n"; // Opción para el nuevo flujo
            welcomeMessage += "Responde con el número de la opción que deseas.\n";
            welcomeMessage += "Escribe /cancelar para volver a este menú en cualquier momento.";
            try {
                const [dbWelcome] = await connection.query("SELECT message FROM wsp_responses WHERE response_to = 'welcome' LIMIT 1");
                if (dbWelcome.length > 0 && dbWelcome[0].message) {
                    welcomeMessage = dbWelcome[0].message;
                }
            } catch (dbError) { console.error("Error fetching welcome message from DB:", dbError); }

            await client.sendMessage(numero, welcomeMessage);
            userState.step = 'esperandoOpcion';
            await resetInactivityTimer(numero);
            return; // Terminar el procesamiento del mensaje
        }

        // Si el usuario está en 'esperandoOpcion' (menú principal)
        if (userState.step === 'esperandoOpcion') {
            console.log(`[Router] Message from ${numero} selecting initial option from menu.`);
            switch (messageBody) {
                case "1":
                    console.log(`[Option] ${numero} selected: 1. Hablar con agente`);
                    const hoursCheck = await checkAttentionHours(numero);
                    if (hoursCheck.available) {
                        await AgentHandler.handleChatAgent(client, numero, userState, connection, decryptData);
                    } else {
                        await client.sendMessage(numero, hoursCheck.message);
                        userState.step = 'esperandoOpcion'; // Mantener en el menú principal
                    }
                    break;

                case "2":
                    console.log(`[Option] ${numero} selected: 2. Información de Especialidad`);
                    await infoEspecialidad.initiateInfoFlow(client, numero, userState, connection);
                    break;

                default:
                    console.log(`[Option] Invalid option ('${messageBody}') from ${numero}. Re-sending menu prompt.`);
                    await client.sendMessage(numero, "⚠️ Opción inválida. Por favor, responde con el número de la opción que deseas (ej: *1* o *2*).");
                    break;
            }
            return;
        }

    } catch (error) {
        console.error(`❌ ------ ERROR PROCESSING MESSAGE from ${senderName} (${numero}) ------ ❌`);
        console.error(error);
        console.error(`❌ ------ END ERROR STACK TRACE ------ ❌`);
        try {
            await client.sendMessage(numero, "⚠️ ¡Ups! Ocurrió un error inesperado al procesar tu mensaje. Hemos registrado el problema. Por favor, intenta de nuevo o escribe /cancelar para volver al menú principal.");
        } catch (sendError) {
            console.error(`❌ Error sending general error message to ${numero}:`, sendError);
        }
    }
});


// --- Manejador de Llamadas Entrantes ---
client.on('call', async (call) => {
    console.log(`📞 Incoming call detected from: ${call.from}, Is Group: ${call.isGroup}, Is Video: ${call.isVideo}`);
    if (call.isGroup) {
        console.log(`[Call] Ignoring group call from ${call.from}.`);
        return;
    }
    try {
        console.log(`[Call] Attempting to reject call from ${call.from}...`);
        await call.reject();
        console.log(`[Call] Call from ${call.from} rejected successfully.`);

        if (call.from !== client.info.wid._serialized) {
            await client.sendMessage(call.from, "Hola 👋. Este número es solo para mensajes de chat y no podemos atender llamadas por aquí. Por favor, envíame un mensaje de texto con tu consulta. 😊");
            console.log(`[Call] Explanation message sent to ${call.from}.`);
        }
    } catch (error) {
        if (error.message && error.message.includes('Call already ended')) {
            console.warn(`[Call] Could not reject call from ${call.from} because it already ended.`);
        } else {
            console.error(`❌ Error rejecting/handling call from ${call.from}:`, error);
        }
    }
});


// --- Inicialización del Bot ---
(async () => {
    try {
        console.log("🚀 Initializing WhatsApp client...");
        await client.initialize();
        console.log("🚀 WhatsApp client initialized successfully.");
        await fs.mkdir(MEDIA_BASE_DIR, { recursive: true });
        console.log(`[Media Dir] Base directory for media ensured: ${MEDIA_BASE_DIR}`);
    } catch (initError) {
        console.error("❌ Fatal Error during client initialization:", initError);
        process.exit(1);
    }
})();

// --- Manejo de Cierre Limpio ---
async function gracefulShutdown() {
    console.log('\n🔌 Shutting down gracefully...');
    if (client) {
        try {
            await client.destroy();
            console.log('WhatsApp client destroyed.');
        } catch (e) {
            console.error('Error destroying WhatsApp client:', e);
        }
    }
    if (connection) {
        try {
            await connection.end();
            console.log('Database connection pool closed.');
        } catch (e) {
            console.error('Error closing database connection pool:', e);
        }
    }
    userStates.forEach(state => {
        if (state.inactivityTimerId) clearTimeout(state.inactivityTimerId);
        if (state.syncAgentTimerId) clearInterval(state.syncAgentTimerId);
    });
    console.log('All user-specific timers cleared.');
    process.exit(0);
}

process.on('SIGINT', gracefulShutdown);
process.on('SIGTERM', gracefulShutdown);
