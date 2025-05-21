require('dotenv').config({ path: require('path').join(__dirname, '../../.env') });
const crypto = require('crypto');

const ENCRYPTION_KEY = process.env.ENCRYPTION_KEY; // Clave de 32 bytes
const IV_LENGTH = 16; // Longitud del IV para AES-256-CBC

if (!ENCRYPTION_KEY || ENCRYPTION_KEY.length !== 32) {
    throw new Error("🔑 La clave de encriptación debe tener exactamente 32 caracteres.");
}

/**
 * Encripta un texto con AES-256-CBC.
 * @param {string} data - Texto a encriptar.
 * @returns {string} - Texto encriptado en formato base64 (IV:EncryptedData).
 */
function encryptData(data) {
    const iv = crypto.randomBytes(IV_LENGTH); // Generar IV aleatorio
    const cipher = crypto.createCipheriv('aes-256-cbc', Buffer.from(ENCRYPTION_KEY), iv);

    let encrypted = cipher.update(data, 'utf8', 'base64');
    encrypted += cipher.final('base64');

    return `${iv.toString('base64')}:${encrypted}`; // IV y datos encriptados separados por ":"
}

/**
 * Desencripta un texto encriptado con AES-256-CBC.
 * @param {string} encryptedData - Datos encriptados en formato "IV:EncryptedData".
 * @returns {string} - Texto desencriptado.
 */
function decryptData(encryptedData) {
    try {
        const [ivBase64, encryptedText] = encryptedData.split(':');
        const iv = Buffer.from(ivBase64, 'base64');
        const encryptedBuffer = Buffer.from(encryptedText, 'base64');

        const decipher = crypto.createDecipheriv('aes-256-cbc', Buffer.from(ENCRYPTION_KEY), iv);
        let decrypted = decipher.update(encryptedBuffer, 'base64', 'utf8');
        decrypted += decipher.final('utf8');

        return decrypted;
    } catch (error) {
        console.error("❌ Error al desencriptar:", error);
        return null;
    }
}

// 🔹 Prueba de encriptación y desencriptación
// let textoPrueba = "Juan Pérez"; // Datos de ejemplo
// let datosEncriptados = encryptData(textoPrueba);
// console.log("🔒 Datos encriptados:", datosEncriptados);

// dato = "AmP5PedyGuB536JNFh1Zew==:SkGbdZu20aoUJsKgL3BK4A=="
// let datosDesencriptados = decryptData(dato);
// console.log("🔓 Datos desencriptados:", datosDesencriptados);

// Exportar funciones para usarlas en otros archivos
module.exports = { encryptData, decryptData };
