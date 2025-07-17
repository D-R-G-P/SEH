<?php

// require_once '../../../../../app/db/db.php'; // Comentado según tu ejemplo

// Cargar variables de entorno
// Asegúrate de que esta función 'cargarEntorno' y el archivo '.env' existan y sean accesibles.
// Por ejemplo:
// function cargarEntorno($envPath) {
//     if (!file_exists($envPath)) {
//         throw new Exception(".env file not found at $envPath");
//     }
//     $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
//     foreach ($lines as $line) {
//         if (strpos(trim($line), '#') === 0) {
//             continue;
//         }
//         list($name, $value) = explode('=', $line, 2);
//         $_ENV[$name] = $value;
//         $_SERVER[$name] = $value;
//     }
// }
// cargarEntorno(dirname(__DIR__, 5) . '/.env'); // ✅ Sube dos niveles hasta SGH/

// header('Content-Type: application/json'); // Comentado según tu ejemplo

// Clave de encriptación (debe coincidir con Node.js)
$ENCRYPTION_KEY = isset($_ENV['ENCRYPTION_KEY']) ? $_ENV['ENCRYPTION_KEY'] : null;
$IV_LENGTH = 16; // Longitud del IV en AES-256-CBC

// Eliminar comillas alrededor de la clave de encriptación (si las hay)
if ($ENCRYPTION_KEY) {
    $ENCRYPTION_KEY = trim($ENCRYPTION_KEY, '"'); // Eliminar comillas dobles
}

// Verificar la longitud de la clave
if (strlen($ENCRYPTION_KEY) !== 32) {
    // Si la clave no es de 32 caracteres, generamos un hash truncado o hacemos padding
    if (strlen($ENCRYPTION_KEY) > 32) {
        $ENCRYPTION_KEY = substr($ENCRYPTION_KEY, 0, 32); // Truncamos si es más larga
    } else {
        // Hacemos padding con caracteres hasta llegar a 32
        $ENCRYPTION_KEY = str_pad($ENCRYPTION_KEY, 32, "0"); // O usa cualquier otro carácter para padding
    }
}

if (!$ENCRYPTION_KEY || strlen($ENCRYPTION_KEY) !== 32) {
    // [Inferencia] Esto es una inferencia basada en la lógica del código y la necesidad de una clave válida.
    // [No verificado] No tengo acceso a la configuración real del entorno.
    echo json_encode(["error" => "🔑 La clave de encriptación es inválida o no está configurada."]);
    exit;
}

/**
 * Desencripta un texto en formato "IV:EncryptedData" usando AES-256-CBC.
 * @param string $encryptedData Datos encriptados.
 * @return string|null Texto desencriptado o null en caso de error.
 */
function decryptData($encryptedData) {
    global $ENCRYPTION_KEY, $IV_LENGTH;

    try {
        // Debug: Verificar el valor recibido
        error_log("🔒 Intentando desencriptar los datos: " . $encryptedData);

        // Verificar si los datos contienen el formato correcto "IV:EncryptedData"
        if (strpos($encryptedData, ':') === false) {
            throw new Exception("El formato de los datos encriptados es incorrecto (falta ':').");
        }

        list($ivBase64, $encryptedText) = explode(':', $encryptedData, 2);

        if (!$encryptedText || !$ivBase64) {
            throw new Exception("El formato de los datos encriptados es incorrecto.");
        }

        // Decodificar IV y datos encriptados
        $iv = base64_decode($ivBase64);
        $encryptedBinary = base64_decode($encryptedText);

        // Debug: Verificar longitud del IV
        if (strlen($iv) !== $IV_LENGTH) {
            error_log("❌ Longitud del IV incorrecta. Se esperaba: $IV_LENGTH, pero se recibió: " . strlen($iv));
            throw new Exception("Longitud del IV incorrecta.");
        }

        // Desencriptar los datos
        $decrypted = openssl_decrypt($encryptedBinary, 'aes-256-cbc', $ENCRYPTION_KEY, OPENSSL_RAW_DATA, $iv);

        // Verificar si la desencriptación fue exitosa
        if ($decrypted === false) {
            error_log("❌ No se pudo desencriptar el texto.");
            throw new Exception("No se pudo desencriptar el texto.");
        }

        // Debug: Verificar el texto desencriptado
        error_log("🔑 Desencriptación exitosa: " . $decrypted);

        return $decrypted ?: null;
    } catch (Exception $e) {
        error_log("❌ Error al desencriptar: " . $e->getMessage());
        return null;
    }
}

/**
 * Encripta un texto usando AES-256-CBC y devuelve el resultado en formato "IV_Base64:EncryptedData_Base64".
 * @param string $plainText Texto a encriptar.
 * @return string|null Texto encriptado o null en caso de error.
 */
function encryptData($plainText) {
    global $ENCRYPTION_KEY, $IV_LENGTH;

    try {
        // Debug: Verificar el valor recibido
        error_log("🔐 Intentando encriptar los datos: " . $plainText);

        // Generar un IV aleatorio
        $iv = openssl_random_pseudo_bytes($IV_LENGTH);
        if ($iv === false) {
            throw new Exception("No se pudo generar un IV aleatorio.");
        }

        // Encriptar los datos
        $encryptedBinary = openssl_encrypt($plainText, 'aes-256-cbc', $ENCRYPTION_KEY, OPENSSL_RAW_DATA, $iv);

        // Verificar si la encriptación fue exitosa
        if ($encryptedBinary === false) {
            error_log("❌ No se pudo encriptar el texto.");
            throw new Exception("No se pudo encriptar el texto.");
        }

        // Codificar IV y datos encriptados en Base64
        $ivBase64 = base64_encode($iv);
        $encryptedTextBase64 = base64_encode($encryptedBinary);

        // Combinar IV y datos encriptados en el formato deseado
        $finalEncryptedData = $ivBase64 . ':' . $encryptedTextBase64;

        // Debug: Verificar el texto encriptado
        error_log("✅ Encriptación exitosa: " . $finalEncryptedData);

        return $finalEncryptedData;
    } catch (Exception $e) {
        error_log("❌ Error al encriptar: " . $e->getMessage());
        return null;
    }
}

// --- Ejemplo de uso (puedes eliminar esto en producción) ---
/*
$textoOriginal = "Este es un texto de prueba que quiero encriptar.";
$textoEncriptado = encryptData($textoOriginal);

if ($textoEncriptado) {
    echo "Texto Original: " . $textoOriginal . "\n";
    echo "Texto Encriptado: " . $textoEncriptado . "\n";

    $textoDesencriptado = decryptData($textoEncriptado);
    if ($textoDesencriptado) {
        echo "Texto Desencriptado: " . $textoDesencriptado . "\n";
    } else {
        echo "Fallo la desencriptación.\n";
    }
} else {
    echo "Fallo la encriptación.\n";
}
*/
