<?php
// SGH/public/resources/encrypter/encrypt.php

// Incluimos db.php para asegurar que las variables de entorno (incluida ENCRYPTION_KEY)
// y la lógica de conexión a la BD estén disponibles.
// Ruta relativa desde SGH/public/resources/encrypter/ hasta SGH/app/db.php
require_once __DIR__ . '/../../../app/db/db.php';

// La clave de encriptación ahora está disponible en $_ENV
$encryption_key = $_ENV['ENCRYPTION_KEY'] ?? null;
if (str_starts_with($encryption_key, '"') && str_ends_with($encryption_key, '"')) {
    $encryption_key = substr($encryption_key, 1, -1);
} elseif (str_starts_with($encryption_key, "'") && str_ends_with($encryption_key, "'")) {
    $encryption_key = substr($encryption_key, 1, -1);
}
// echo $encryption_key;

// Validación de la clave: Es crucial que la clave exista y tenga la longitud correcta.
if (!$encryption_key || strlen($encryption_key) !== 32) {
    error_log("ERROR CRÍTICO: La clave de encriptación (ENCRYPTION_KEY) no está definida o no tiene 32 bytes.");
    // En un entorno de producción, puedes mostrar un mensaje de error genérico al usuario o redirigir.
    die("Error de configuración de seguridad. Contacte al administrador.");
}

/**
 * Encripta una cadena de texto usando AES-256-CBC.
 * Genera un IV aleatorio para cada encriptación y lo adjunta al texto cifrado.
 *
 * @param string|null $data El texto a encriptar. Si es null o vacío, retorna null.
 * @return string|null La cadena encriptada (IV_Base64:Ciphertext_Base64) o null.
 */
function encryptData(?string $data): ?string
{
    // Usamos la variable $encryption_key que ya fue cargada en el ámbito global del archivo.
    global $encryption_key;
    if (empty($data)) {
        return null;
    }

    $cipher = 'aes-256-cbc';
    $iv_length = openssl_cipher_iv_length($cipher);
    $iv = openssl_random_pseudo_bytes($iv_length);

    $encrypted = openssl_encrypt($data, $cipher, $encryption_key, OPENSSL_RAW_DATA, $iv);

    if ($encrypted === false) {
        error_log("Error en la encriptación de datos: " . openssl_error_string());
        return null;
    }

    return base64_encode($iv . $encrypted);
}

/**
 * Genera un hash criptográfico (SHA-256) de una cadena de texto para propósitos de búsqueda.
 * Es crucial que esta función sea determinista: la misma entrada siempre debe producir la misma salida.
 * Los datos se normalizan (quitar espacios, convertir a minúsculas) para asegurar consistencia en la búsqueda.
 *
 * @param string|null $data El texto a hashear. Si es null o vacío, retorna el hash de una cadena vacía.
 * @return string El hash SHA-256 del texto normalizado.
 */
function hashDataForSearch(?string $data): string
{
    if ($data === null) {
        $normalizedData = '';
    } else {
        $normalizedData = trim($data);
        // Aplica normalización específica para tipos de datos si es necesario
        // Ejemplo para ciertos campos de selección o texto corto:
        $normalizedData = mb_strtolower($normalizedData, 'UTF-8');
    }
    return hash('sha256', $normalizedData);
}