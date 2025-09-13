<?php

// Asegúrate de que este script solo sea accedido por POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Lee la clave de API desde el archivo .env
$env_path = '../../../../../.env';
if (!file_exists($env_path)) {
    http_response_code(500);
    exit(json_encode(['error' => 'Archivo .env no encontrado.']));
}
$env_vars = parse_ini_file($env_path);
$gemini_api_key = $env_vars['GEMINI_API_KEY'] ?? null;

if (empty($gemini_api_key)) {
    http_response_code(500);
    exit(json_encode(['error' => 'Clave de API de Gemini no configurada en el archivo .env.']));
}

// Obtener el JSON del cuerpo de la solicitud
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

if (!isset($data['razonSocial'])) {
    http_response_code(400);
    exit(json_encode(['error' => 'Falta el parámetro razonSocial.']));
}

$razon_social = $data['razonSocial'];

// --- Lógica de la API de Gemini ---
$url_gemini = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-preview-05-20:generateContent?key=$gemini_api_key";
$payload_gemini = json_encode([
    "contents" => [[
        "parts" => [
            ["text" => "Separa el siguiente texto de una 'Razon Social' en 'nombre' y 'apellido'. Si el texto no parece un nombre de persona, asigna el valor 'N/A' a ambos campos."],
            ["text" => "Texto a separar: $razon_social"]
        ]
    ]],
    "generationConfig" => [
        "responseMimeType" => "application/json",
        "responseSchema" => [
            "type" => "OBJECT",
            "properties" => [
                "nombre" => ["type" => "STRING"],
                "apellido" => ["type" => "STRING"]
            ],
            "required" => ["nombre", "apellido"]
        ]
    ]
]);

// Inicializar cURL para la solicitud a Gemini
$ch_gemini = curl_init($url_gemini);
curl_setopt($ch_gemini, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch_gemini, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen($payload_gemini)
]);
curl_setopt($ch_gemini, CURLOPT_POST, true);
curl_setopt($ch_gemini, CURLOPT_POSTFIELDS, $payload_gemini);

$response_gemini = curl_exec($ch_gemini);
$http_status_gemini = curl_getinfo($ch_gemini, CURLINFO_HTTP_CODE);
curl_close($ch_gemini);

if ($http_status_gemini !== 200) {
    http_response_code(500);
    exit(json_encode(['error' => 'Error al llamar a Gemini API.']));
}

$data_gemini = json_decode($response_gemini, true);
$json_string = $data_gemini['candidates'][0]['content']['parts'][0]['text'] ?? '{}';
$nombres_separados = json_decode($json_string, true);

// Devolver solo el nombre y el apellido
echo json_encode($nombres_separados);

?>
