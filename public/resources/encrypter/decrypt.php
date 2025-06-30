<?php
// SGH/public/resources/encrypter/decrypt.php

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

// Validación de la clave: Es crucial que la clave exista y tenga la longitud correcta.
if (!$encryption_key || strlen($encryption_key) !== 32) {
    error_log("ERROR CRÍTICO: La clave de encriptación (ENCRYPTION_KEY) no está definida o no tiene 32 bytes.");
    die("Error de configuración de seguridad. Contacte al administrador.");
}

/**
 * Desencripta una cadena de texto previamente encriptada con encryptData().
 * Extrae el IV del texto cifrado para la desencriptación.
 *
 * @param string|null $encryptedData La cadena encriptada (IV_Base64:Ciphertext_Base64). Si es null o vacío, retorna null.
 * @return string|null El texto original desencriptado o null si falla.
 */
function decryptData(?string $encryptedData): ?string
{
    // Usamos la variable $encryption_key que ya fue cargada en el ámbito global del archivo.
    global $encryption_key;
    if (empty($encryptedData)) {
        return null;
    }

    $data = base64_decode($encryptedData);
    if ($data === false) {
        error_log("Error al decodificar Base64 en desencriptación: " . $encryptedData);
        return null;
    }

    $cipher = 'aes-256-cbc';
    $iv_length = openssl_cipher_iv_length($cipher);

    if (strlen($data) < $iv_length) {
        error_log("Datos encriptados inválidos: longitud de IV insuficiente para " . $encryptedData);
        return null;
    }

    $iv = substr($data, 0, $iv_length);
    $ciphertext = substr($data, $iv_length);

    $decrypted = openssl_decrypt($ciphertext, $cipher, $encryption_key, OPENSSL_RAW_DATA, $iv);

    if ($decrypted === false) {
        error_log("Error en la desencriptación de datos: " . openssl_error_string() . " for IV: " . bin2hex($iv));
        return null;
    }

    return $decrypted;
}