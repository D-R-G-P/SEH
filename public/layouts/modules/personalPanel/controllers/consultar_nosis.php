<?php

// Asegúrate de que este script solo sea accedido por POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

// Configura las cabeceras para permitir CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Considera restringir esto a tu dominio en producción

// Obtener el JSON del cuerpo de la solicitud
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

if (!isset($data['dni'])) {
    http_response_code(400);
    exit(json_encode(['error' => 'Falta el parámetro DNI.']));
}

$dni = $data['dni'];

// Limpiar el DNI: eliminar puntos, comas o cualquier otro carácter no deseado.
$dni = str_replace('.', '', $dni);

// --- Lógica de la API de Nosis ---
$url_nosis = 'https://informes.nosis.com/Home/Buscar';
$payload_nosis = json_encode([
    "Texto" => $dni,
    "Tipo" => "P",
    "EdadDesde" => "-1",
    "EdadHasta" => "-1",
    "IdProvincia" => "-1",
    "Localidad" => "",
    "recaptcha_response_field" => "enganio al captcha",
    "recaptcha_challenge_field" => "enganio al captcha",
    "encodedResponse" => ""
]);

// Inicializar cURL para la solicitud a Nosis
$ch = curl_init($url_nosis);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen($payload_nosis)
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload_nosis);

$response_nosis = curl_exec($ch);
$http_status_nosis = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_status_nosis !== 200) {
    http_response_code($http_status_nosis);
    exit($response_nosis);
}

$data_nosis = json_decode($response_nosis, true);

// Devolver el array completo de EntidadesEncontradas
echo json_encode(['EntidadesEncontradas' => $data_nosis['EntidadesEncontradas'] ?? []]);

?>
