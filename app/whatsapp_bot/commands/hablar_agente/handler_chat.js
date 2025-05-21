/**
 * @file handler_chat.js
 * @description Maneja la lógica de interacción cuando un usuario solicita hablar con un agente,
 * incluyendo la recolección de datos del paciente y la gestión del chat con el agente.
 */

const fs = require('fs').promises;
const path = require('path');
const mime = require('mime-types');
const crypto = require('crypto'); // Needed for UUID generation if saving files
const { MessageMedia } = require('whatsapp-web.js');
const moment = require('moment');

// --- Helper Functions ---

/**
 * Capitaliza la primera letra de cada palabra en un texto.
 * @param {string} texto - El texto a capitalizar.
 * @returns {string} El texto capitalizado.
 */
function capitalizarTexto(texto) {
    if (!texto) return '';
    return texto
        .toLowerCase()
        .split(" ")
        .map(palabra => palabra.charAt(0).toUpperCase() + palabra.slice(1))
        .join(" ");
}

/**
 * Convierte una fecha de formato DD/MM/AAAA a AAAA-MM-DD para la base de datos.
 * @param {string} fecha - Fecha en formato DD/MM/AAAA.
 * @returns {string} Fecha en formato AAAA-MM-DD.
 */
function convertirFechaFormatoDB(fecha) {
    if (!/^\d{2}\/\d{2}\/\d{4}$/.test(fecha)) return null; // Basic validation
    const partes = fecha.split("/");
    return `${partes[2]}-${partes[1]}-${partes[0]}`;
}

// Mapeos para opciones seleccionables por el usuario (mejoraría si vinieran de la BD)
const opcionesSexo = { "1": "Masculino", "2": "Femenino", "3": "X" };
const opcionesTipoDocumento = { "1": "DNI", "2": "CI", "3": "LC", "4": "LE", "5": "Cédula Mercosur", "6": "CUIT", "7": "CUIL", "8": "Pasaporte extranjero", "9": "Cédula de Identidad extranjera", "10": "Otro Documento extranjero", "11": "No posee", "12": "En tramite" };
const opcionesIdentidadGenero = { "1": "Travesti", "2": "Mujer cis", "3": "Mujer trans", "4": "No binarie", "5": "Varón cis", "6": "Varón trans", "7": "Gay", "8": "Lesbiana", "9": "Genero fluido", "10": "Ninguna de las anteriores" };


// --- Core Functions ---

/**
 * Inicia el flujo para hablar con un agente. Verifica si el paciente existe.
 * @param {object} client - Instancia del cliente de WhatsApp.
 * @param {string} numero - Número del usuario (ej: '5492214380474@c.us').
 * @param {object} userState - Objeto de estado del usuario.
 * @param {object} connection - Pool de conexión a la base de datos (con promesas).
 * @param {function} decryptData - Función para desencriptar datos.
 */
async function handleChatAgent(client, numero, userState, connection, decryptData) {
    console.log(`[Agent Handler] User ${numero} requested agent. Verifying DB...`);
    try {
        // Busca paciente por número de teléfono
        const [rows] = await connection.query(
            "SELECT id, nombres, apellidos FROM pacientes_chat WHERE telefono = ? LIMIT 1",
            [numero]
        );

        if (rows.length > 0) {
            // Paciente encontrado
            const { id, nombres, apellidos } = rows[0];
            // Desencriptar nombres y apellidos para mostrarlos
            const nombreDesc = decryptData(nombres);
            const apellidoDesc = decryptData(apellidos);

            console.log(`[Agent Handler] Patient found: ${nombreDesc} ${apellidoDesc} (ID: ${id}). Asking confirmation.`);
            userState.step = 'agent_confirmarPaciente'; // Estado: esperando confirmación SI/NO
            userState.data = { pacienteId: id }; // Guardar ID del paciente encontrado

            await client.sendMessage(numero, `Hola ${nombreDesc} ${apellidoDesc}, ¿la consulta es para ti? Responde *SI* o *NO*.`);

        } else {
            // Paciente no encontrado, iniciar recolección de datos
            console.log(`[Agent Handler] Patient not found for ${numero}. Requesting data...`);
            await solicitarDatosPaciente(client, numero, userState, connection);
        }
    } catch (error) {
        console.error(`❌ Error in handleChatAgent for ${numero}:`, error);
        userState.step = null; // Reset state on error
        userState.data = {};
        await client.sendMessage(numero, "⚠️ Ocurrió un error al intentar conectarte con un agente. Por favor, intenta de nuevo.");
    }
}

/**
 * Inicia el proceso de solicitud de datos para un nuevo paciente o para una consulta de terceros.
 * @param {object} client - Instancia del cliente de WhatsApp.
 * @param {string} numero - Número del usuario.
 * @param {object} userState - Objeto de estado del usuario.
 * @param {object} connection - Pool de conexión a la base de datos.
 * @param {boolean} [isThirdParty=false] - Indica si los datos son para un tercero.
 */
async function solicitarDatosPaciente(client, numero, userState, connection, isThirdParty = false) {
    console.log(`[Agent Handler] Requesting patient data for ${numero}. Third party: ${isThirdParty}`);
    userState.step = 'agent_pedirApellido'; // Estado inicial de recolección
    userState.data = { isThirdParty }; // Guardar si es para tercero

    // Mensaje introductorio
    await client.sendMessage(numero, '✨ Antes de conectar con un agente, necesito registrar algunos datos.\n\nQuiero que sepas que estas preguntas están diseñadas con respeto por tu identidad de género. Por eso, te pediré que ingreses tu apellido, nombre y sexo tal como figuran en tu DNI.\n\nAdemás, registraremos tu identidad y nombre autopercibidos y *te trataremos de acuerdo a ellos*. 💙');

    // Verificar si el número ya está asociado a *otro* paciente (esto parece redundante si handleChatAgent no lo encontró)
    // Esta lógica podría simplificarse. Si llegó aquí, es porque el *telefono* no estaba.
    // Quizás la intención era verificar si el *numero* (ID de WhatsApp) ya estaba asociado a alguien?
    // La query original usaba 'numero', que no parece ser una columna estándar. Asumiendo que se refería a 'telefono':
    /*
    const [pac] = await connection.query("SELECT id FROM pacientes_chat WHERE telefono = ?", [numero]);
    if (!pac.length > 0) { // Si el teléfono NO está registrado
        await client.sendMessage(numero, "*Estos serán tus datos y estarán asociados a tu número de teléfono. Si la consulta no es para vos, primero decime tus datos y luego podrás ingresar los de la otra persona.* 😊");
    }
    */
    // Si es para un tercero, ajustar el mensaje
    if (isThirdParty) {
        await client.sendMessage(numero, "Ahora, por favor, decime los datos de la persona para quien es la consulta, empezando por su/s apellido/s.");
    } else {
        // Mensaje si es la primera vez que se registra este número
        await client.sendMessage(numero, "*Estos datos quedarán asociados a tu número de teléfono.* 😊\nPor favor, decime tu/s apellido/s.");
    }
}

/**
 * Procesa las respuestas del usuario durante la recolección de datos o la confirmación.
 * @param {object} client - Instancia del cliente de WhatsApp.
 * @param {object} message - Objeto del mensaje recibido.
 * @param {object} userState - Objeto de estado del usuario.
 * @param {object} connection - Pool de conexión a la base de datos.
 * @param {function} encryptData - Función para encriptar datos.
 * @param {function} decryptData - Función para desencriptar datos. (No se usa directamente aquí, pero podría ser útil)
 * @param {function} startAgentChatSession - Función para iniciar formalmente el chat con agente (crear registro en DB, etc.).
 */
async function procesarRespuestaAgente(client, message, userState, connection, encryptData, decryptData, startAgentChatSession) {
    const numero = message.from;
    const mensajeTexto = message.body.trim();
    const currentStep = userState.step; // Paso actual ej: 'agent_pedirApellido'

    console.log(`[Agent Handler] Processing response from ${numero} for step ${currentStep}: "${mensajeTexto}"`);

    if (!currentStep || !currentStep.startsWith('agent_')) {
        console.warn(`[Agent Handler] Received message from ${numero} but not in an agent-related step (${currentStep}). Ignoring.`);
        // Podrías enviar un mensaje genérico o reiniciar el flujo si es necesario.
        return;
    }

    try {
        switch (currentStep) {
            case 'agent_confirmarPaciente':
                if (/^si|sí$/i.test(mensajeTexto)) {
                    console.log(`[Agent Handler] User ${numero} confirmed identity. Starting chat session.`);
                    // Iniciar sesión de chat para el paciente existente
                    await startAgentChatSession(client, numero, userState.data.pacienteId, userState, connection);
                    // Estado se limpia dentro de startAgentChatSession o al finalizar

                } else if (/^no$/i.test(mensajeTexto)) {
                    console.log(`[Agent Handler] User ${numero} denied identity. Requesting data for third party.`);
                    // Iniciar recolección de datos para un tercero
                    await solicitarDatosPaciente(client, numero, userState, connection, true); // Marcar como tercero

                } else {
                    await client.sendMessage(numero, "⚠️ Respuesta inválida. Por favor, responde con *SI* o *NO*.");
                    // No cambiar de estado, esperar respuesta válida
                }
                break;

            case 'agent_pedirApellido':
                userState.data.apellidos = capitalizarTexto(mensajeTexto);
                userState.step = 'agent_pedirNombre';
                await client.sendMessage(numero, "Gracias. Ahora decime tu/s nombre/s.");
                break;

            case 'agent_pedirNombre':
                userState.data.nombres = capitalizarTexto(mensajeTexto);
                userState.step = 'agent_pedirSexo';
                await client.sendMessage(numero, "¿Cuál es tu sexo según DNI? Responde con el número:\n1 - Masculino\n2 - Femenino\n3 - X");
                break;

            case 'agent_pedirSexo':
                const sexoSeleccionado = opcionesSexo[mensajeTexto];
                if (!sexoSeleccionado) {
                    await client.sendMessage(numero, "Por favor, responde con 1 (Masculino), 2 (Femenino) o 3 (X).");
                    return; // Esperar respuesta válida
                }
                userState.data.sexo = sexoSeleccionado;
                userState.step = 'agent_pedirTipoDocumento';
                const tiposDocTexto = Object.entries(opcionesTipoDocumento).map(([key, value]) => `${key} - ${value}`).join("\n");
                await client.sendMessage(numero, `¿Qué tipo de documento tienes? Responde con el número:\n${tiposDocTexto}`);
                break;

            case 'agent_pedirTipoDocumento':
                const tipoDocSeleccionado = opcionesTipoDocumento[mensajeTexto];
                if (!tipoDocSeleccionado) {
                    await client.sendMessage(numero, "Por favor, responde con un número válido del listado.");
                    return;
                }
                userState.data.tipo_documento = tipoDocSeleccionado;
                userState.step = 'agent_pedirDNI';
                await client.sendMessage(numero, "Ahora decime tu número de documento (sin puntos ni espacios, incluir letras si las tiene).");
                break;

            case 'agent_pedirDNI':
                // Permitir letras y números, validar longitud podría ser útil
                if (!/^[a-zA-Z0-9]+$/.test(mensajeTexto)) {
                    await client.sendMessage(numero, "Por favor, ingresa un número de documento válido (solo letras y números).");
                    return;
                }
                userState.data.documento = mensajeTexto.toUpperCase(); // Guardar en mayúsculas por consistencia
                userState.step = 'agent_pedirFechaNacimiento';
                await client.sendMessage(numero, "¿Cuál es tu fecha de nacimiento? (Formato: DD/MM/AAAA)");
                break;

            case 'agent_pedirFechaNacimiento':
                const fechaConvertida = convertirFechaFormatoDB(mensajeTexto);
                if (!fechaConvertida) {
                    await client.sendMessage(numero, "Por favor, ingresa la fecha en formato DD/MM/AAAA.");
                    return;
                }
                userState.data.fecha_nacimiento = fechaConvertida;
                userState.step = 'agent_pedirIdentidadGenero';
                const identidadesGeneroTexto = Object.entries(opcionesIdentidadGenero).map(([key, value]) => `${key} - ${value}`).join("\n");
                await client.sendMessage(numero, `¿Cuál es tu identidad de género autopercibida? Responde con el número:\n${identidadesGeneroTexto}`);
                break;

            case 'agent_pedirIdentidadGenero':
                const identidadSeleccionada = opcionesIdentidadGenero[mensajeTexto];
                if (!identidadSeleccionada) {
                    await client.sendMessage(numero, "Por favor, responde con un número válido del listado.");
                    return;
                }
                userState.data.tipo_genero = identidadSeleccionada;
                userState.step = 'agent_pedirNombreAutopercibido';
                // Corregido: nombreAutopercibido
                await client.sendMessage(numero, "¿Cuál es tu nombre autopercibido? (Si es el mismo que tu nombre de DNI o no aplica, ingresa 0)");
                break;

            case 'agent_pedirNombreAutopercibido': // Corregido
                userState.data.nombreAutopercibido = (mensajeTexto === "0" || mensajeTexto.toLowerCase() === 'no aplica') ? null : capitalizarTexto(mensajeTexto);
                userState.step = 'agent_pedirProvincia';
                await client.sendMessage(numero, "¿En qué provincia vives?");
                break;

            case 'agent_pedirProvincia':
                userState.data.provincia = capitalizarTexto(mensajeTexto);
                userState.step = 'agent_pedirPartido';
                await client.sendMessage(numero, "¿En qué partido/departamento vives?");
                break;

            case 'agent_pedirPartido':
                userState.data.partido = capitalizarTexto(mensajeTexto);
                userState.step = 'agent_pedirCiudad';
                await client.sendMessage(numero, "¿En qué ciudad/localidad vives?");
                break;

            case 'agent_pedirCiudad':
                userState.data.ciudad = capitalizarTexto(mensajeTexto);
                userState.step = 'agent_pedirCalle';
                await client.sendMessage(numero, "¿Cuál es tu calle?");
                break;

            case 'agent_pedirCalle':
                userState.data.calle = capitalizarTexto(mensajeTexto); // Capitalizar también la calle
                userState.step = 'agent_pedirNumeroCasa';
                await client.sendMessage(numero, "Por favor, proporciona el número de la calle.");
                break;

            case 'agent_pedirNumeroCasa':
                // Validar que sea principalmente numérico, puede tener letras (ej: 123 BIS)
                if (!mensajeTexto || mensajeTexto.length > 10) { // Validación simple
                    await client.sendMessage(numero, "Por favor, ingresa un número de calle válido.");
                    return;
                }
                userState.data.numeroCasa = mensajeTexto;
                userState.step = 'agent_pedirPiso';
                await client.sendMessage(numero, "Ingresa el piso (Si no corresponde, ingresá 0).");
                break;

            case 'agent_pedirPiso':
                // Validar número o '0'
                if (!/^\d+$/.test(mensajeTexto)) {
                    await client.sendMessage(numero, "Por favor, ingresa un número válido para el piso (o 0).");
                    return;
                }
                userState.data.piso = (mensajeTexto === "0") ? null : mensajeTexto;
                userState.step = 'agent_pedirDepartamento';
                await client.sendMessage(numero, "Ingresa el departamento (Si no corresponde, ingresá 0).");
                break;

            case 'agent_pedirDepartamento':
                // Validar alfanumérico o '0'
                if (!/^[a-zA-Z0-9]+$/.test(mensajeTexto)) {
                    await client.sendMessage(numero, "Por favor, ingresa un departamento válido (letras/números o 0).");
                    return;
                }
                userState.data.departamento = (mensajeTexto === "0") ? null : mensajeTexto;
                userState.step = 'agent_pedirMail';
                await client.sendMessage(numero, "Por favor, proporcioná un correo electrónico.");
                break;

            case 'agent_pedirMail':
                // Validación básica de email
                if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(mensajeTexto)) {
                    await client.sendMessage(numero, "Por favor, ingresa un correo electrónico válido.");
                    return;
                }
                userState.data.mail = mensajeTexto.toLowerCase(); // Guardar en minúsculas
                userState.step = 'agent_pedirObraSocial';
                await client.sendMessage(numero, "¿Tienes obra social o prepaga? Si es así, decime el nombre. Si no, responde 'No tengo'.");
                break;

            case 'agent_pedirObraSocial':
                userState.data.obra_social = (/^no tengo|no$/i.test(mensajeTexto)) ? null : capitalizarTexto(mensajeTexto);

                console.log(`[Agent Handler] Data collection complete for ${numero}. Proceeding to save/start chat.`);

                // --- Datos Recolectados ---
                const datos = userState.data;
                let pacienteIdParaChat = datos.pacienteId; // ID si era un paciente existente consultando por otro

                // Mostrar resumen al usuario (opcional pero recomendado)
                const resumen = `
*Resumen de Datos Ingresados:*
Apellidos: ${datos.apellidos}
Nombres: ${datos.nombres}
Sexo (DNI): ${datos.sexo}
Tipo Doc: ${datos.tipo_documento}
Documento: ${datos.documento}
Fecha Nac.: ${datos.fecha_nacimiento} (AAAA-MM-DD)
Identidad Género: ${datos.tipo_genero}
Nombre Autopercibido: ${datos.nombreAutopercibido || 'N/A'}
Provincia: ${datos.provincia}
Partido: ${datos.partido}
Ciudad: ${datos.ciudad}
Calle: ${datos.calle} ${datos.numeroCasa}${datos.piso ? ` Piso ${datos.piso}` : ''}${datos.departamento ? ` Depto ${datos.departamento}` : ''}
Email: ${datos.mail}
Obra Social: ${datos.obra_social || 'No tiene'}
                `.trim();
                await client.sendMessage(numero, resumen);
                await client.sendMessage(numero, "Verificando y guardando tus datos..."); // Mensaje intermedio

                // --- Lógica de Guardado ---
                if (datos.isThirdParty) {
                    // Si es para un tercero, NO creamos un nuevo paciente con el número del consultante.
                    // Solo guardamos los datos como un mensaje inicial en el chat del paciente original.
                    if (!pacienteIdParaChat) {
                        console.error(`❌ [Agent Handler] Error: Trying to save third-party data but no original pacienteId found for ${numero}.`);
                        await client.sendMessage(numero, "⚠️ Hubo un problema interno al asociar la consulta. Por favor, contacta soporte.");
                        userState.step = null; userState.data = {}; // Reset state
                        return;
                    }
                    console.log(`[Agent Handler] Saving third-party data as initial message for pacienteId ${pacienteIdParaChat}.`);
                    // Iniciar la sesión de chat y pasar los datos para que se guarden como mensaje inicial
                    await startAgentChatSession(client, numero, pacienteIdParaChat, userState, connection, datos);

                } else {
                    // Si NO es para tercero, es un nuevo paciente registrándose con SU número.
                    console.log(`[Agent Handler] Saving new patient data for ${numero}.`);
                    try {
                        // Obtener foto de perfil (mejor manejar errores aquí)
                        let profilePicUrl = "https://i.pinimg.com/236x/d9/d8/8e/d9d88e3d1f74e2b8ced3df051cecb81d.jpg"; // Default
                        try {
                            profilePicUrl = await client.getProfilePicUrl(numero) || profilePicUrl;
                        } catch (picError) {
                            console.warn(`[Agent Handler] Could not get profile pic for ${numero}: ${picError.message}`);
                        }

                        // Encriptar todos los campos necesarios ANTES de insertar
                        const [result] = await connection.query(
                            `INSERT INTO pacientes_chat
                             (telefono, apellidos, nombres, sexo, tipo_documento, documento, fecha_nacimiento,
                              identidad_genero, nombre_autopercibido, provincia, partido, ciudad, calle,
                              numero, piso, departamento, mail, obra_social, profile_pic, fecha_registro)
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())`,
                            [
                                numero, // El número de WhatsApp del paciente
                                encryptData(datos.apellidos),
                                encryptData(datos.nombres),
                                encryptData(datos.sexo),
                                encryptData(datos.tipo_documento),
                                encryptData(datos.documento),
                                encryptData(datos.fecha_nacimiento),
                                encryptData(datos.tipo_genero),
                                datos.nombreAutopercibido ? encryptData(datos.nombreAutopercibido) : null,
                                encryptData(datos.provincia),
                                encryptData(datos.partido),
                                encryptData(datos.ciudad),
                                encryptData(datos.calle),
                                encryptData(datos.numeroCasa), // Asumiendo que 'numero' en DB es el de la casa
                                datos.piso ? encryptData(datos.piso) : null,
                                datos.departamento ? encryptData(datos.departamento) : null,
                                encryptData(datos.mail),
                                datos.obra_social ? encryptData(datos.obra_social) : null,
                                encryptData(profilePicUrl) // Encriptar URL de foto? Considerar implicaciones
                            ]
                        );
                        pacienteIdParaChat = result.insertId;
                        console.log(`[Agent Handler] New patient created with ID: ${pacienteIdParaChat} for number ${numero}.`);

                        // Iniciar sesión de chat para el nuevo paciente
                        await startAgentChatSession(client, numero, pacienteIdParaChat, userState, connection);

                    } catch (dbError) {
                        console.error(`❌ Error saving new patient for ${numero}:`, dbError);
                        // Verificar si el error es por DNI duplicado (requiere índice UNIQUE en 'documento')
                        if (dbError.code === 'ER_DUP_ENTRY') {
                            console.warn(`[Agent Handler] Duplicate entry detected for DNI ${datos.documento}.`);
                            await client.sendMessage(numero, `⚠️ Ya existe un paciente registrado con el documento ${datos.documento}. Si crees que es un error, contacta a soporte.`);
                            // Podrías buscar el paciente existente por DNI y preguntar si es él/ella.
                        } else {
                            await client.sendMessage(numero, "⚠️ Ocurrió un error al guardar tus datos. Por favor, intenta de nuevo más tarde.");
                        }
                        userState.step = null; userState.data = {}; // Reset state on error
                        return;
                    }
                }
                break;

            default:
                console.warn(`[Agent Handler] Reached unknown step '${currentStep}' for user ${numero}. Resetting state.`);
                await client.sendMessage(numero, "🤔 Hubo un problema con el paso actual. Volvamos a empezar.");
                userState.step = null; // Reset state
                userState.data = {};
                // Podrías reenviar el menú principal aquí si es apropiado
                break;
        }
    } catch (error) {
        console.error(`❌ Error processing agent response for step ${currentStep}, user ${numero}:`, error);
        await client.sendMessage(numero, "⚠️ Ocurrió un error procesando tu respuesta. Intenta de nuevo.");
        // Considerar resetear el estado aquí también si el error es grave
        // userState.step = null; userState.data = {};
    }
}


/**
 * Guarda un mensaje entrante del paciente durante una sesión de chat activa con un agente.
 * Encripta el mensaje antes de guardarlo.
 * @param {object} message - Objeto del mensaje de WhatsApp.
 * @param {object} userState - Estado del usuario (debe contener userState.chatId).
 * @param {object} connection - Pool de conexión a la BD.
 * @param {function} encryptData - Función para encriptar.
 * @param {string} MEDIA_BASE_DIR - Directorio base para guardar archivos multimedia.
 */
async function saveIncomingAgentChatMessage(client, message, userState, connection, encryptData, MEDIA_BASE_DIR) {
    const numero = message.from;
    const chatId = userState.chatId; // ID del chat activo

    if (!chatId) {
        console.error(`❌ Error: Cannot save message from ${numero}. No active chatId found in userState.`);
        // Esto no debería ocurrir si isAgentChatting está bien gestionado
        return;
    }

    console.log(`[Agent Chat] Saving incoming message from ${numero} for chat ID ${chatId}. Type: ${message.type}`);

    try {
        let messageContentForDb;
        let isMedia = message.hasMedia;

        if (isMedia) {
            console.log('[Agent Chat] Message has media. Downloading...');
            const media = await message.downloadMedia();

            if (media) {
                console.log(`[Agent Chat] Media downloaded: Type=${media.mimetype}, Name=${media.filename}, Size=${media.data.length}`);

                const uuid = crypto.randomUUID();
                let extension = 'dat'; // Default extension

                // Extract extension from filename or mimetype
                if (media.filename) {
                    const parts = media.filename.split('.');
                    if (parts.length > 1) extension = parts.pop().toLowerCase();
                } else if (media.mimetype) {
                    // Basic mime to ext mapping (can be expanded)
                    const mimeMap = { 'jpeg': 'jpg', 'png': 'png', 'gif': 'gif', 'webp': 'webp', 'pdf': 'pdf', 'msword': 'doc', 'vnd.openxmlformats-officedocument.wordprocessingml.document': 'docx', 'plain': 'txt' };
                    const subtype = media.mimetype.split('/')[1];
                    extension = mimeMap[subtype] || extension;
                }

                // Define save path (daily directory)
                const today = new Date();
                const datePart = today.toISOString().split('T')[0]; // YYYY-MM-DD
                const todayDir = path.join(MEDIA_BASE_DIR, datePart);
                const targetFilePath = path.join(todayDir, `${uuid}.${extension}`);

                // Ensure directory exists
                try {
                    await fs.promises.mkdir(todayDir, { recursive: true });
                } catch (dirError) {
                    console.error(`❌ Error creating directory ${todayDir}:`, dirError);
                    throw new Error("Failed to create media directory"); // Propagate error
                }

                // Decode Base64 and save
                const base64Data = media.data.split(';base64,').pop();
                const fileBuffer = Buffer.from(base64Data, 'base64');

                await fs.promises.writeFile(targetFilePath, fileBuffer);
                console.log(`[Agent Chat] Media file saved to: ${targetFilePath}`);

                // Format message content for DB
                const originalFilename = media.filename || `archivo.${extension}`;
                const caption = message.body ? `, ${message.body.trim()}` : ''; // Add caption if present
                messageContentForDb = `!fileTypeMessage, ${uuid}.${extension}, ${originalFilename}${caption}`;

            } else {
                console.error(`❌ [Agent Chat] Failed to download media for message from ${numero}.`);
                // Save a fallback message indicating the failure
                messageContentForDb = `!fileDownloadFailed, ${message.id.id}`; // Use message ID
                isMedia = false; // Treat as text message in DB regarding content
            }
        } else {
            // Plain text message
            messageContentForDb = message.body.trim();
            if (!messageContentForDb) {
                console.warn(`[Agent Chat] Received empty text message from ${numero}. Skipping save.`);
                return; // Don't save empty messages
            }
        }

        // Encrypt the message content before saving
        const encryptedMessage = encryptData(messageContentForDb);

        // Insert into wsp_messages
        await connection.query(
            "INSERT INTO wsp_messages (numero, mensaje, chat_id, estado, remitente, timestamp) VALUES (?, ?, ?, 'recibido', 'paciente', NOW())",
            [numero, encryptedMessage, chatId] // Mark if it was originally media
        );

        console.log(`[Agent Chat] Incoming message from ${numero} (Chat ID: ${chatId}) saved to DB.`);

        // Optional: Update profile picture (consider doing this less frequently)
        try {
            let profilePicUrl = await client.getProfilePicUrl(numero);
            if (profilePicUrl) {
                // Encrypt pic URL? Depends on requirements.
                await connection.query("UPDATE pacientes_chat SET profile_pic = ? WHERE telefono = ?", [encryptData(profilePicUrl), numero]);
            }
        } catch (picError) {
            console.warn(`[Agent Chat] Could not update profile pic for ${numero}: ${picError.message}`);
        }

    } catch (error) {
        console.error(`❌ Error in saveIncomingAgentChatMessage for ${numero}, chat ${chatId}:`, error);
        // Notify user? Depends on desired behavior.
        // await client.sendMessage(numero, "⚠️ Hubo un problema al procesar tu último mensaje.");
    }
}


/**
 * Verifica periódicamente si hay mensajes del agente pendientes de enviar al paciente
 * y los envía. También maneja la señal de finalización del chat.
 * @param {object} client - Instancia del cliente de WhatsApp.
 * @param {string} numero - Número del usuario.
 * @param {object} userState - Estado del usuario (debe contener chatId y syncAgentTimerId).
 * @param {object} connection - Pool de conexión a la BD.
 * @param {function} stopAgentChatSession - Función para limpiar el estado al finalizar.
 */
async function syncAgentMessages(client, numero, userState, connection, stopAgentChatSession, MEDIA_BASE_DIR) {
    const chatId = userState.chatId;

    // Verifica si hay un chat activo para este usuario
    if (!chatId || !userState.isAgentChatting) {
        // Si no hay chat activo, limpiar el timer si existe y salir
        if (userState.syncAgentTimerId) {
            clearInterval(userState.syncAgentTimerId);
            userState.syncAgentTimerId = null;
        }
        return; // No hay nada que sincronizar
    }

    try {
        // --- Procesar mensajes con estado 'pendiente_info' primero ---
        // Estos son mensajes que se enviaron, pero no pudimos adjuntar el listener .on('ack')
        const [mensajesPendienteInfo] = await connection.query(
            `SELECT id, message_sid, estado FROM wsp_messages WHERE chat_id = ? AND estado = 'pendiente_info' LIMIT 20`, // Limitar para no sobrecargar
            [chatId]
        );

        if (mensajesPendienteInfo.length > 0) {
            console.log(`[Agent Sync] Found ${mensajesPendienteInfo.length} messages with status 'pendiente_info'. Attempting to get info...`);
            for (const msgInfo of mensajesPendienteInfo) {
                if (msgInfo.message_sid) {
                    try {
                        // Intentar obtener el objeto Message completo usando el SID guardado
                        // Esto puede fallar si el bot se reinició o hay problemas con WWebJS
                        const message = await client.getMessageById(msgInfo.message_sid);

                        if (message) {
                            // Usar getInfo() para obtener el estado de entrega
                            const info = await message.getInfo();
                            console.log(`[Agent Sync] getInfo() result for SID ${msgInfo.message_sid} (DB ID: ${msgInfo.id}):`, info);

                            let newState = null;
                            // Mapear estados de getInfo a tus estados de DB
                            // getInfo() devuelve arrays de contactos que han recibido/leído el mensaje.
                            if (info) {
                                if (info.read && info.read.length > 0) {
                                    newState = 'leido';
                                } else if (info.delivery && info.delivery.length > 0) {
                                    newState = 'entregado';
                                }
                                // getInfo() no parece reportar errores de ACK directamente de la misma forma que el listener.
                                // Si getInfo() devuelve un objeto pero no indica entrega/lectura, puede seguir en estado 'enviado' o 'pendiente_info'.
                            }

                            // Actualizar estado si se determinó un estado final (entregado o leido)
                            if (newState && msgInfo.estado !== newState) {
                                await connection.query("UPDATE wsp_messages SET estado = ? WHERE id = ?", [newState, msgInfo.id]);
                                console.log(`[Agent Sync] Message DB ID ${msgInfo.id} status updated to '${newState}' via getInfo().`);
                            } else if (!info) {
                                // Si getInfo devuelve null (mensaje no encontrado o no enviado por este bot/sesión)
                                console.warn(`[Agent Sync] getInfo() returned null for SID ${msgInfo.message_sid} (DB ID: ${msgInfo.id}). Message might not exist or was not sent by this bot instance.`);
                                // Podrías marcarlo como error definitivo después de varios intentos
                                // Por ahora, lo dejamos en 'pendiente_info' para reintentar.
                            } else {
                                // Si getInfo no dio un estado final (ej. aún solo enviado), no hacemos nada.
                                console.log(`[Agent Sync] getInfo() for DB ID ${msgInfo.id} did not indicate final state. Keeping as '${msgInfo.estado}'.`);
                            }

                        } else {
                            console.warn(`[Agent Sync] getMessageById() returned null for SID ${msgInfo.message_sid} (DB ID: ${msgInfo.id}). Message object not found in current session.`);
                            // Si no podemos obtener el objeto Message, no podemos usar getInfo().
                            // Podrías marcarlo como error o dejarlo para que otro intento de rehidratación lo recoja.
                            // Por ahora, lo dejamos en 'pendiente_info'.
                        }
                    } catch (getInfoError) {
                        console.error(`❌ [Agent Sync] Error calling getInfo() or getMessageById() for DB ID ${msgInfo.id} (SID: ${msgInfo.message_sid}):`, getInfoError);
                        // Si hay un error al llamar a getInfo(), lo dejamos en 'pendiente_info' para reintentar.
                    }
                } else {
                    console.warn(`[Agent Sync] Message DB ID ${msgInfo.id} has status 'pendiente_info' but no message_sid. Cannot use getInfo(). Marking as error.`);
                    await connection.query("UPDATE wsp_messages SET estado = 'error_sin_sid' WHERE id = ?", [msgInfo.id]);
                }
            }
        }

        // --- Buscar mensajes del agente que no sean del paciente/sistema y no estén ya leídos, con error, o pendiente_info ---
        const [mensajesAgente] = await connection.query(
            `SELECT m.id, m.mensaje, m.remitente, p.nombre AS nombre_agente_enc, m.timestamp, m.estado
             FROM wsp_messages m
             LEFT JOIN personal p ON m.remitente = p.dni COLLATE utf8mb4_unicode_ci
             WHERE m.chat_id = ?
               AND m.remitente != 'paciente'
               AND m.remitente != 'sistema'
               AND m.estado NOT IN ('leido', 'entregado', 'error_envio_definitivo', 'error_ack', 'pendiente_info', 'enviado') -- Añadido 'pendiente_info' y 'enviado' para evitar re-enviar
             ORDER BY m.id ASC;`, // Procesar en orden cronológico
            [chatId]
        );

        // Si no hay mensajes pendientes de envío, salir
        if (mensajesAgente.length === 0) {
            return; // No hay nada más que sincronizar en este ciclo
        }

        console.log(`[Agent Sync] Found ${mensajesAgente.length} pending/unsent agent messages for chat ${chatId}.`);

        // Iterar sobre cada mensaje pendiente del agente
        for (const msg of mensajesAgente) {
            // Doble verificación por si el estado cambió justo antes de este loop
            if (['leido', 'entregado', 'error_envio_definitivo', 'error_ack', 'pendiente_info', 'enviado'].includes(msg.estado)) {
                console.log(`[Agent Sync] Message ID ${msg.id} already processed (estado: ${msg.estado}). Skipping send.`);
                continue;
            }

            // --- Manejo de Señal de Finalización de Chat ---
            if (msg.mensaje == "!hsmfinishchat") {
                console.log(`🔴 [Agent Sync] Termination signal (ID: ${msg.id}) received for chat ${chatId}. Finalizing...`);
                // Marcar la señal como procesada ('leido') y finalizar el chat en la BD
                await connection.query("UPDATE wsp_messages SET estado = 'entregado' WHERE id = ?", [msg.id]);
                await connection.query("UPDATE chats SET estado = 'finalizado', fecha_cierre = NOW() WHERE id = ?", [chatId]);

                // Obtener y enviar mensaje de despedida
                const [farewellRows] = await connection.query("SELECT message FROM wsp_responses WHERE response_to = 'farewell_default' LIMIT 1");
                const farewellMessage = farewellRows.length > 0 ? farewellRows[0].message : "Gracias por comunicarte. ¡Hasta pronto!";

                try {
                    await client.sendMessage(numero, "🎉 Chat finalizado por el agente.");
                    await client.sendMessage(numero, farewellMessage);
                } catch (sendError) {
                    console.error(`❌ Error sending farewell message to ${numero} for chat ${chatId}:`, sendError);
                }

                // Detener la sesión de chat y limpiar el estado local
                stopAgentChatSession(numero, userState, connection); // Pasa connection
                console.log(`[Agent Sync] Agent chat session stopped and state cleaned for ${numero}.`);
                return; // Salir de la función syncAgentMessages, ya no hay más que hacer para este chat.
            }

            // --- Preparación y Envío de Mensajes Normales (Texto o Archivo) ---
            let messageToSend;
            let options = {};
            let isFileMessage = false;

            try {
                // Si es un mensaje de archivo (identificado por el prefijo)
                if (msg.mensaje.startsWith('!fileTypeMessage')) {
                    isFileMessage = true;
                    const filePrefix = '!fileTypeMessage, ';
                    const content = msg.mensaje.substring(filePrefix.length);
                    const parts = content.split(',').map(p => p.trim());

                    // Validar formato esperado: !fileTypeMessage, UUID.ext, nombre_original[, caption]
                    if (parts.length < 2) {
                        console.error(`[Agent Sync] Invalid file format in DB for message ID ${msg.id}: ${msg.mensaje}. Marking as error.`);
                        await connection.query("UPDATE wsp_messages SET estado = 'error_formato_archivo' WHERE id = ?", [msg.id]);
                        continue; // Saltar este mensaje y procesar el siguiente
                    }

                    const [uuidExt, originalName, ...captionParts] = parts;
                    const fileCaption = captionParts.join(', ').trim();

                    // Construir ruta al archivo usando la fecha del mensaje y el UUID+extensión
                    const messageDate = new Date(msg.timestamp);
                    const fileDatePart = moment(messageDate).tz("America/Argentina/Buenos_Aires").format('YYYY-MM-DD');
                    const filePath = path.join(MEDIA_BASE_DIR, fileDatePart, uuidExt);

                    console.log(`[Agent Sync] Preparing to send file: ${filePath} (Original: ${originalName}) for DB msg ID ${msg.id}`);

                    // Intentar leer el archivo
                    try {
                        const fileBuffer = await fs.readFile(filePath);
                        const mimeType = mime.lookup(filePath) || 'application/octet-stream'; // Determinar MIME type
                        // Crear objeto MessageMedia para enviar
                        messageToSend = new MessageMedia(mimeType, fileBuffer.toString('base64'), originalName);
                        if (fileCaption) options.caption = fileCaption; // Añadir caption si existe
                    } catch (fileReadError) {
                        // Si no se puede leer el archivo (ej. no existe)
                        console.error(`❌ Error reading file ${filePath} for message ID ${msg.id}:`, fileReadError);
                        // Marcar error en BD y notificar al paciente
                        await connection.query("UPDATE wsp_messages SET estado = 'error_archivo_no_encontrado' WHERE id = ?", [msg.id]);
                        try {
                            await client.sendMessage(numero, `⚠️ No pudimos enviar un archivo adjunto (Ref: ${msg.id}). El agente ha sido notificado.`);
                        } catch (userNotifyError) { console.error("Error notifying user about missing file:", userNotifyError); }
                        continue; // Saltar este mensaje
                    }
                } else {
                    // Si es un mensaje de texto plano
                    messageToSend = msg.mensaje;
                    // Opcional: Añadir firma del agente
                    if (msg.nombre_agente_enc) {
                        try {
                            // Asumiendo que tienes la función decryptData disponible
                            // Nota: decryptData no está definida en este fragmento, asegúrate de que esté accesible
                            const agentName = msg.nombre_agente_enc
                            if (agentName) messageToSend += `\n\n_${agentName}_`;
                            messageToSend += "\nEquipo de Gestión de Turnos" // Añadir nombre en cursiva
                        } catch (e) { console.warn("Could not decrypt agent name for signature."); }
                    }
                }

                // --- Envío del Mensaje y Manejo de ACK ---
                console.log(`[Agent Sync] Attempting to send message ID ${msg.id} to ${numero}.`);
                // Enviar el mensaje (texto o archivo con opciones)
                const sentMessageCandidate = await client.sendMessage(numero, messageToSend, options);

                // *** CORRECCIÓN INICIO: Manejo mejorado del objeto retornado ***
                // Verificar si el objeto devuelto es un objeto Message válido con método .on()
                if (sentMessageCandidate && typeof sentMessageCandidate.on === 'function' && sentMessageCandidate.id && typeof sentMessageCandidate.id._serialized === 'string') {
                    const sentMessage = sentMessageCandidate; // Es válido, usarlo
                    const messageSID = sentMessage.id._serialized; // ID único de WhatsApp
                    console.log(`[Agent Sync] Message ID ${msg.id} sent to WhatsApp server. Message SID: ${messageSID}`);

                    // Actualizar estado a 'enviado' y guardar el SID en la BD
                    await connection.query("UPDATE wsp_messages SET estado = 'enviado', message_sid = ? WHERE id = ?", [messageSID, msg.id]);
                    console.log(`[Agent Sync] Message ID ${msg.id} DB status updated to 'enviado'.`);

                    // Adjuntar listener para eventos ACK (entregado, leído, error)
                    sentMessage.on('ack', async (ack) => {
                        console.log(`[ACK Handler] ACK received for message SID ${messageSID} (DB ID: ${msg.id}): ${ack}`);
                        try {
                            let newState = null;
                            if (ack === 2) { // Entregado
                                newState = 'entregado';
                                // Solo actualiza si el estado actual no es ya 'leido' o 'entregado'
                                await connection.query("UPDATE wsp_messages SET estado = ? WHERE id = ? AND estado NOT IN ('leido', 'entregado')", [newState, msg.id]);
                            } else if (ack === 3) { // Leído
                                newState = 'leido';
                                // Solo actualiza si el estado actual no es ya 'leido'
                                await connection.query("UPDATE wsp_messages SET estado = ? WHERE id = ? AND estado != 'leido'", [newState, msg.id]);
                            } else if (ack === -1) { // Error de ACK
                                newState = 'error_ack';
                                // Solo actualiza si el estado actual no es ya 'leido'
                                await connection.query("UPDATE wsp_messages SET estado = ? WHERE id = ? AND estado NOT IN ('leido')", [newState, msg.id]);
                                console.error(`[ACK Handler] Error ACK (-1) for message DB ID ${msg.id} (SID: ${messageSID}). Marked as '${newState}'.`);
                            }

                            if (newState) {
                                console.log(`[ACK Handler] Message DB ID ${msg.id} (SID: ${messageSID}) DB status updated to '${newState}'.`);
                            }
                            // Opcional: Remover listener una vez leído
                            // if (ack === 3) sentMessage.removeAllListeners('ack');
                        } catch (dbError) {
                            console.error(`❌ [ACK Handler] Error updating DB for message SID ${messageSID} (DB ID: ${msg.id}), ACK ${ack}:`, dbError);
                        }
                    });
                } else {
                    // Si client.sendMessage no devolvió un objeto Message válido para adjuntar listener .on()
                    console.error(`[Agent Sync] ERROR: client.sendMessage for DB ID ${msg.id} did NOT return a valid Message object with .on() method for ACK tracking.`);
                    // Loguear el objeto que sí se recibió para entender qué pasó
                    console.error(`[Agent Sync] Received object from sendMessage:`, JSON.stringify(sentMessageCandidate, null, 2));
                    // Marcar con un estado de error específico en la BD y guardar el SID si está disponible
                    const messageSID = sentMessageCandidate && sentMessageCandidate.id && typeof sentMessageCandidate.id._serialized === 'string' ? sentMessageCandidate.id._serialized : null;
                    // Usamos 'pendiente_info' y guardamos el SID para intentar rastrear con getInfo() después.
                    await connection.query("UPDATE wsp_messages SET estado = 'pendiente_info', message_sid = ? WHERE id = ?", [messageSID, msg.id]);
                    console.warn(`[Agent Sync] Message ID ${msg.id} marked as 'pendiente_info'. Message likely sent but ACK tracking failed. SID: ${messageSID}`);
                }
                // *** CORRECCIÓN FIN ***

            } catch (sendError) {
                // Error durante la preparación o el envío inicial
                console.error(`❌ Error sending agent message ID ${msg.id} to ${numero}:`, sendError);
                try {
                    // Marcar como error de envío genérico
                    // Considerar un estado diferente si es un error de envío inicial vs error de ACK
                    await connection.query("UPDATE wsp_messages SET estado = 'error_envio' WHERE id = ?", [msg.id]);
                    console.warn(`[Agent Sync] Message ID ${msg.id} marked as 'error_envio' in DB due to send failure.`);
                } catch (dbError) {
                    console.error(`❌ CRITICAL: Failed to mark message ID ${msg.id} as 'error_envio' after send failure:`, dbError);
                }
                // Continuar con el siguiente mensaje en el bucle
            }
        } // Fin del bucle for (msg of mensajesAgente)

        // La lógica para procesar 'pendiente_info' ahora está al principio de la función.

    } catch (error) {
        // Error general en la función syncAgentMessages (ej. error en la query inicial)
        console.error(`❌ General error during agent message sync for chat ${chatId}, user ${numero}:`, error);
        // Considerar detener el intervalo si el error es grave
        // stopAgentChatSession(numero, userState, connection);
    }
}


module.exports = {
    handleChatAgent,
    procesarRespuestaAgente,
    saveIncomingAgentChatMessage,
    syncAgentMessages,
    // No exportar solicitarDatosPaciente directamente si solo se llama internamente
};
