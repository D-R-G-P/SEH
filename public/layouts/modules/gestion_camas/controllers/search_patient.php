<?php

header('Content-Type: application/json');

// Tus require_once y la inicialización de $db_instance y $pdo
require_once '../../../../config.php';
require_once __DIR__ . '/../../../../../app/db/db.php';
require_once __DIR__ . '/../../../../../app/db/user_session.php';
require_once __DIR__ . '/../../../../../app/db/user.php';
require_once __DIR__ . '/../../../../resources/encrypter/encrypt.php';
require_once __DIR__ . '/../../../../resources/encrypter/decrypt.php';

cargarEntorno(dirname(__DIR__, 5) . '/.env');

$db = new DB();
$pdo = $db->connect();

$user = new User();
$userSession = new UserSession();
$currentUser = $userSession->getCurrentUser();
$user->setUser($currentUser);


$codigo_dni = $_POST['codigo_dni'] ?? null; // Usa el operador null coalescing para mayor limpieza

// Lógica principal para la búsqueda de paciente
// Si $codigo_dni no está presente, significa que los datos vienen de campos individuales del formulario.
if (!$codigo_dni) {
    $tipo_documento_raw = $_POST['tipo_documento'] ?? null;
    $documento_raw = $_POST['documento'] ?? null;
    $sexo_raw = $_POST['sexo'] ?? null;

    // VALIDACIÓN BÁSICA: Asegurémonos de que los campos necesarios no estén vacíos.
    if (!$tipo_documento_raw || !$documento_raw || !$sexo_raw) {
        echo json_encode(['success' => false, 'message' => 'Faltan datos para la búsqueda (campos individuales).']);
        exit();
    }

    // 1. Hashear los datos de búsqueda para coincidir con las columnas de hash en la DB
    $hashed_tipo_documento = hashDataForSearch($tipo_documento_raw);
    $hashed_documento = hashDataForSearch($documento_raw);
    $hashed_sexo = hashDataForSearch($sexo_raw);

    // Llamar a la función de búsqueda genérica
    searchPatientByHashes($pdo, $hashed_tipo_documento, $hashed_documento, $hashed_sexo);

} else {
    // Si $codigo_dni tiene un valor, significa que viene del escaneo del DNI.
    // Formatos posibles:
    // 00461618812@LAMAS@CRISTIAN JONATHAN@M@43255000@A@13/12/2000@21/10/2016@202
    // 00461618812"LAMAS"CRISTIAN JONATHAN"M"43255000"A"13-12-2000"21-10-2016"202

    $delimiter = str_contains($codigo_dni, '@') ? '@' : '"';
    $parts = explode($delimiter, $codigo_dni);

    // Validar que tenemos suficientes partes
    if (count($parts) < 5) { // Necesitamos al menos hasta el número de documento (índice 4)
        echo json_encode(['success' => false, 'message' => 'Formato de código DNI escaneado inválido.']);
        exit();
    }

    // Extraer los datos relevantes
    $tipo_documento_raw = 'DNI'; // Siempre DNI para este tipo de escaneo
    $sexo_abbr = strtoupper(trim($parts[3])); // 'M', 'F', 'X'
    $documento_raw = trim($parts[4]); // Número de documento

    // Reemplazar sexo según corresponda
    $sexo_raw = '';
    switch ($sexo_abbr) {
        case 'M':
            $sexo_raw = 'Masculino';
            break;
        case 'F':
            $sexo_raw = 'Femenino';
            break;
        case 'X':
            $sexo_raw = 'X';
            break;
        default:
            // Si el sexo no es reconocido, puedes manejarlo como un error o un valor por defecto.
            error_log("Sexo no reconocido en DNI escaneado: " . $sexo_abbr);
            echo json_encode(['success' => false, 'message' => 'Sexo no reconocido en el código DNI escaneado.']);
            exit();
    }

    // 1. Hashear los datos de búsqueda para coincidir con las columnas de hash en la DB
    $hashed_tipo_documento = hashDataForSearch($tipo_documento_raw);
    $hashed_documento = hashDataForSearch($documento_raw);
    $hashed_sexo = hashDataForSearch($sexo_raw);

    // Llamar a la función de búsqueda genérica
    searchPatientByHashes($pdo, $hashed_tipo_documento, $hashed_documento, $hashed_sexo);
}


/**
 * Función genérica para buscar un paciente en la base de datos por sus hashes.
 *
 * @param PDO $pdo Objeto PDO de conexión a la base de datos.
 * @param string $hashed_tipo_documento Hash del tipo de documento.
 * @param string $hashed_documento Hash del número de documento.
 * @param string $hashed_sexo Hash del sexo.
 * @return void Envía una respuesta JSON y termina la ejecución.
 */
function searchPatientByHashes(PDO $pdo, string $hashed_tipo_documento, string $hashed_documento, string $hashed_sexo): void {
    try {
        // Seleccionamos todas las columnas necesarias, incluyendo las encriptadas,
        // para luego desencriptarlas si el paciente es encontrado.
        // Asegúrate de que todas estas columnas existan en tu tabla `patients`.
        $sql = "SELECT id, document_type, document, gender, last_name, name, birth_date,
                       phone_number, family_phone_number, email, provincia, partido, ciudad,
                       codigo_postal, calle, numero, piso, departamento, barrio,
                       health_insurance, health_insurance_number, administrative_name, 
                       gender_identity, self_perceived_name, dni_rectified,
                       phone_number_alt, family_phone_number_alt
                FROM patients 
                WHERE document_type_hash = :document_type_hash
                AND document_hash = :document_hash
                AND gender_hash = :gender_hash";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':document_type_hash', $hashed_tipo_documento, PDO::PARAM_STR);
        $stmt->bindParam(':document_hash', $hashed_documento, PDO::PARAM_STR);
        $stmt->bindParam(':gender_hash', $hashed_sexo, PDO::PARAM_STR);
        $stmt->execute();

        $patient_found = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($patient_found) {
            // Desencriptar TODOS los datos sensibles antes de enviarlos
            // Itera sobre las columnas que sabes que están encriptadas y desencripta.
            // Esto es más seguro que desencriptar 'id' si 'id' no es un campo encriptado.
            // La columna 'id' INT no debería ser encriptada.
            
            // Lista de columnas que deben ser desencriptadas
            $columns_to_decrypt = [
                'document', 'document_type', 'gender', 'name', 'last_name', 'birth_date',
                'phone_number', 'family_phone_number', 'email', 'provincia', 'partido', 'ciudad',
                'codigo_postal', 'calle', 'numero', 'piso', 'departamento', 'barrio',
                'health_insurance', 'health_insurance_number', 'administrative_name', 
                'gender_identity', 'self_perceived_name',
                'phone_number_alt', 'family_phone_number_alt'
            ];

            foreach ($columns_to_decrypt as $col) {
                if (isset($patient_found[$col])) {
                    $patient_found[$col] = decryptData($patient_found[$col]);
                }
            }
            
            // El campo dni_rectified es TINYINT(1), no necesita desencriptación.

            echo json_encode(['success' => true, 'patient' => $patient_found]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Paciente no encontrado con los datos proporcionados.']);
        }

    } catch (PDOException $e) {
        error_log("Error PDO al buscar paciente: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error en la base de datos al buscar paciente. Detalles: ' . $e->getMessage()]);
    }
    exit(); // Termina la ejecución después de enviar la respuesta JSON
}

?>