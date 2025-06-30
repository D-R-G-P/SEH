<?php
// SGH/public/layouts/modules/gestion_camas/views/info_patient.php

// Incluir archivos necesarios para la DB y encriptación/desencriptación
// Ajusta las rutas según la ubicación real de estos archivos en tu proyecto.
require_once '../../../../config.php'; // Ajusta la ruta si es necesario
require_once __DIR__ . '/../../../../../app/db/db.php';
require_once __DIR__ . '/../../../../../app/db/user_session.php';
require_once __DIR__ . '/../../../../../app/db/user.php';
require_once __DIR__ . '/../../../../resources/encrypter/encrypt.php'; // Necesario para decryptData
require_once __DIR__ . '/../../../../resources/encrypter/decrypt.php'; // Necesario para decryptData

// Cargar variables de entorno (asumiendo que cargarEntorno está disponible)
cargarEntorno(dirname(__DIR__, 5) . '/.env'); // Ajusta la ruta para llegar al .env

$db = new DB();
$pdo = $db->connect();

$patient_data = []; // Inicializamos como un array vacío por defecto
$patient_id_from_url = $_GET['id'] ?? null; // Obtener ID de la URL si existe

if ($patient_id_from_url) {
    try {
        // Consulta para obtener el paciente por su ID
        $sql = "SELECT
                    id,
                    document,
                    document_type,
                    gender,
                    name,
                    last_name,
                    birth_date,
                    phone_number,
                    family_phone_number,
                    email,
                    country,
                    provincia,
                    partido,
                    ciudad,
                    codigo_postal,
                    calle,
                    numero,
                    piso,
                    departamento,
                    barrio,
                    health_insurance,
                    health_insurance_number,
                    administrative_name,
                    gender_identity,
                    self_perceived_name,
                    dni_rectified,
                    phone_number_alt,
                    family_phone_number_alt
                FROM patients
                WHERE id = :id";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $patient_id_from_url, PDO::PARAM_INT);
        $stmt->execute();

        $patient_data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($patient_data) {
            // Desencriptar TODAS las columnas necesarias
            $columns_to_decrypt = [
                'document',
                'document_type',
                'gender',
                'name',
                'last_name',
                'birth_date',
                'phone_number',
                'family_phone_number',
                'email',
                'country',
                'provincia',
                'partido',
                'ciudad',
                'codigo_postal',
                'calle',
                'numero',
                'piso',
                'departamento',
                'barrio',
                'health_insurance',
                'health_insurance_number',
                'administrative_name',
                'gender_identity',
                'self_perceived_name',
                'phone_number_alt',
                'family_phone_number_alt'
            ];

            foreach ($columns_to_decrypt as $col) {
                if (isset($patient_data[$col])) {
                    $patient_data[$col] = $patient_data[$col] !== null ? decryptData($patient_data[$col]) : null;
                }
            }
            // Convertir dni_rectified a booleano
            $patient_data['dni_rectified'] = (bool) ($patient_data['dni_rectified'] ?? false);

        } else {
            error_log("Paciente con ID " . $patient_id_from_url . " no encontrado.");
            $patient_data = ['error' => 'Paciente no encontrado.'];
        }

    } catch (PDOException $e) {
        error_log("Error PDO al obtener información del paciente: " . $e->getMessage());
        $patient_data = ['error' => 'Error en la base de datos al obtener información del paciente.'];
    }
}

$patient_admitted = false; // Inicializar como false

if ($patient_id_from_url) {
    $cons = "SELECT id, date_discharged FROM patients_admitteds WHERE patient_id = :patient_id AND date_discharged IS NULL ORDER BY date_discharged DESC LIMIT 1";
    $stmt = $pdo->prepare($cons);
    $stmt->bindParam(':patient_id', $patient_id_from_url, PDO::PARAM_INT);
    $stmt->execute();

    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        $patient_admitted = true;
        // echo "<pre>Paciente admitido: " . htmlspecialchars(print_r($result, true)) . "</pre>";
    }
}
?>

<div id="info_paciente_content" data-patient-data='<?php echo json_encode($patient_data); ?>'>
    <div class="close" style="width: 100%; display: flex; justify-content: flex-end; padding: .5vw, z-index: 100;">
        <button class="btn-red" onclick="back.style.display = 'none'; info_paciente.style.display = 'none';"
            style="width: 2.3vw; height: 2.3vw; z-index: 100;"><b><i class="fa-solid fa-xmark"></i></b></button>
    </div>

    <h3 class="formTitle" style="z-index: 0;">Información del paciente</h3>

    <div class="patient_group">
        <h3>Información personal</h3>
        <hr>

        <div class="patient_row" id="nombre_administrativo_row" style="display: none;">
            <div class="patient_row_son">
                <label for="nombre_administrativo_final">Nombre para trámites administrativos</label>
                <input type="text" id="nombre_administrativo_final"
                    value="<?php echo htmlspecialchars($patient_data['administrative_name'] ?? ''); ?>">
            </div>
        </div>

        <div class="patient_row">
            <div class="patient_row_son">
                <label for="nombres_dni">Nombres (según DNI)</label>
                <input type="text" name="name" id="nombres_dni"
                    value="<?php echo htmlspecialchars($patient_data['name'] ?? ''); ?>">
            </div>

            <div class="patient_row_son">
                <label for="apellidos_dni">Apellidos (según DNI)</label>
                <input type="text" name="last_name" id="apellidos_dni"
                    value="<?php echo htmlspecialchars($patient_data['last_name'] ?? ''); ?>">
            </div>
        </div>

        <div class="patient_row">
            <div class="patient_row_son">
                <label for="sexo_dni">Sexo (según DNI)</label>
                <select name="gender" id="sexo_dni" class="select2" style="width: 100%;">
                    <option value="">Seleccione</option>
                    <option value="Femenino" <?php echo (isset($patient_data['gender']) && $patient_data['gender'] == 'Femenino') ? 'selected' : ''; ?>>Femenino</option>
                    <option value="Masculino" <?php echo (isset($patient_data['gender']) && $patient_data['gender'] == 'Masculino') ? 'selected' : ''; ?>>Masculino</option>
                    <option value="X" <?php echo (isset($patient_data['gender']) && $patient_data['gender'] == 'X') ? 'selected' : ''; ?>>X</option>
                </select>
            </div>

            <div class="patient_row_son">
                <label for="fecha_nacimiento">Fecha de nacimiento</label>
                <input type="date" name="birth_date" max="<?php echo date('Y-m-d'); ?>" id="fecha_nacimiento"
                    value="<?php echo htmlspecialchars($patient_data['birth_date'] ?? ''); ?>">
            </div>
        </div>

        <div class="patient_row">
            <div class="patient_row_son">
                <label for="tipo_documento_patient">Tipo de documento</label>
                <select name="document_type" id="tipo_documento_patient" class="select2" style="width: 100%;">
                    <option value="">Seleccione</option>
                    <option value="DNI" <?php echo (isset($patient_data['document_type']) && $patient_data['document_type'] == 'DNI') ? 'selected' : ''; ?>>DNI</option>
                    <option value="CI" <?php echo (isset($patient_data['document_type']) && $patient_data['document_type'] == 'CI') ? 'selected' : ''; ?>>CI</option>
                    <option value="LC" <?php echo (isset($patient_data['document_type']) && $patient_data['document_type'] == 'LC') ? 'selected' : ''; ?>>LC</option>
                    <option value="LE" <?php echo (isset($patient_data['document_type']) && $patient_data['document_type'] == 'LE') ? 'selected' : ''; ?>>LE</option>
                    <option value="Cédula Mercosur" <?php echo (isset($patient_data['document_type']) && $patient_data['document_type'] == 'Cédula Mercosur') ? 'selected' : ''; ?>>Cédula Mercosur
                    </option>
                    <option value="CUIT" <?php echo (isset($patient_data['document_type']) && $patient_data['document_type'] == 'CUIT') ? 'selected' : ''; ?>>CUIT</option>
                    <option value="CUIL" <?php echo (isset($patient_data['document_type']) && $patient_data['document_type'] == 'CUIL') ? 'selected' : ''; ?>>CUIL</option>
                    <option value="Pasaporte extranjero" <?php echo (isset($patient_data['document_type']) && $patient_data['document_type'] == 'Pasaporte extranjero') ? 'selected' : ''; ?>>Pasaporte
                        extranjero</option>
                    <option value="Cédula de Identidad Extranjera" <?php echo (isset($patient_data['document_type']) && $patient_data['document_type'] == 'Cédula de Identidad Extranjera') ? 'selected' : ''; ?>>Cédula
                        de Identidad Extranjera</option>
                    <option value="Otro Documento Extranjero" <?php echo (isset($patient_data['document_type']) && $patient_data['document_type'] == 'Otro Documento Extranjero') ? 'selected' : ''; ?>>Otro
                        Documento Extranjero</option>
                    <option value="No posee" <?php echo (isset($patient_data['document_type']) && $patient_data['document_type'] == 'No posee') ? 'selected' : ''; ?>>No posee</option>
                    <option value="En trámite" <?php echo (isset($patient_data['document_type']) && $patient_data['document_type'] == 'En trámite') ? 'selected' : ''; ?>>En trámite</option>
                </select>
            </div>

            <div class="patient_row_son">
                <label for="numero_documento">Número de documento</label>
                <input type="text" name="document" id="numero_documento"
                    value="<?php echo htmlspecialchars($patient_data['document'] ?? ''); ?>">
            </div>
        </div>

        <div class="patient_row">
            <div class="patient_row_son">
                <label for="identidad_genero">Identidad de Género autopercibida</label>
                <select name="gender_identity" id="identidad_genero" class="select2" style="width: 100%;">
                    <option value="">Seleccione</option>
                    <option value="Mujer cis" <?php echo (isset($patient_data['gender_identity']) && $patient_data['gender_identity'] == 'Mujer cis') ? 'selected' : ''; ?>>Mujer cis</option>
                    <option value="Varón cis" <?php echo (isset($patient_data['gender_identity']) && $patient_data['gender_identity'] == 'Varón cis') ? 'selected' : ''; ?>>Varón cis</option>
                    <option value="Mujer trans" <?php echo (isset($patient_data['gender_identity']) && $patient_data['gender_identity'] == 'Mujer trans') ? 'selected' : ''; ?>>Mujer trans</option>
                    <option value="Varón trans" <?php echo (isset($patient_data['gender_identity']) && $patient_data['gender_identity'] == 'Varón trans') ? 'selected' : ''; ?>>Varón trans</option>
                    <option value="No binarie" <?php echo (isset($patient_data['gender_identity']) && $patient_data['gender_identity'] == 'No binarie') ? 'selected' : ''; ?>>No binarie</option>
                    <option value="Travesti" <?php echo (isset($patient_data['gender_identity']) && $patient_data['gender_identity'] == 'Travesti') ? 'selected' : ''; ?>>Travesti</option>
                    <option value="Género fluido" <?php echo (isset($patient_data['gender_identity']) && $patient_data['gender_identity'] == 'Género fluido') ? 'selected' : ''; ?>>Género fluido</option>
                    <option value="Otro" <?php echo (isset($patient_data['gender_identity']) && $patient_data['gender_identity'] == 'Otro') ? 'selected' : ''; ?>>Otro</option>
                    <option value="Prefiero no especificar" <?php echo (isset($patient_data['gender_identity']) && $patient_data['gender_identity'] == 'Prefiero no especificar') ? 'selected' : ''; ?>>Prefiero no
                        especificar</option>
                </select>
            </div>

            <div class="patient_row_son" id="nombre_autopercibido_container" style="display: none;">
                <label for="nombre_autopercibido">Nombre de pila autopercibido</label>
                <input type="text" name="self_perceived_name" id="nombre_autopercibido"
                    value="<?php echo htmlspecialchars($patient_data['self_perceived_name'] ?? ''); ?>">
            </div>
        </div>

        <div class="patient_row_son_check" id="dni_rectificado_container" style="display: none;">
            <input type="checkbox" name="dni_rectified" id="dni_rectificado" class="toggle-switch-checkbox" <?php echo (isset($patient_data['dni_rectified']) && $patient_data['dni_rectified']) ? 'checked' : ''; ?>>
            <label for="dni_rectificado" class="toggle-switch-label">¿DNI rectificado (nombre/sexo) según Ley
                26.743?</label>
        </div>

    </div>

    <div class="patient_group">
        <h3>Información de contacto</h3>
        <hr>

        <div class="patient_row">
            <div class="patient_row_son">
                <label for="phone_number">Teléfono 1</label>
                <input type="tel" name="phone_number" id="phone_number"
                    value="<?php echo htmlspecialchars($patient_data['phone_number'] ?? ''); ?>">
            </div>

            <div class="patient_row_son">
                <label for="family_phone_number">Teléfono familiar 1 (Emergencia)</label>
                <input type="tel" name="family_phone_number" id="family_phone_number"
                    value="<?php echo htmlspecialchars($patient_data['family_phone_number'] ?? ''); ?>">
            </div>
        </div>

        <div class="patient_row">
            <div class="patient_row_son">
                <label for="phone_number_alt">Teléfono 2 (Alternativo)</label>
                <input type="tel" name="phone_number_alt" id="phone_number_alt"
                    value="<?php echo htmlspecialchars($patient_data['phone_number_alt'] ?? ''); ?>">
            </div>
            <div class="patient_row_son">
                <label for="family_phone_number_alt">Teléfono familiar 2 (Alternativo)</label>
                <input type="tel" name="family_phone_number_alt" id="family_phone_number_alt"
                    value="<?php echo htmlspecialchars($patient_data['family_phone_number_alt'] ?? ''); ?>">
            </div>
        </div>

        <div class="patient_row">
            <div class="patient_row_son">
                <label for="email">Email</label>
                <input type="email" name="email" id="email"
                    value="<?php echo htmlspecialchars($patient_data['email'] ?? ''); ?>">
            </div>
        </div>

    </div>

    <div class="patient_group">
        <h3>Dirección</h3>
        <hr>
        <div class="patient_row">
            <div class="patient_row_son">
                <label for="pais">País</label>
                <select name="pais" id="pais" class="select2">
                    <option value="">Seleccione un país</option>
                </select>
            </div>

            <div class="patient_row_son">
                <label for="provincia">Provincia</label>
                <select name="provincia" id="provincia" class="select2" disabled>
                    <option value="">Seleccione una provincia</option>
                </select>
            </div>

            <div class="patient_row_son">
                <label for="partido">Partido</label>
                <select name="partido" id="partido" class="select2" disabled>
                    <option value="">Seleccione un partido</option>
                </select>
            </div>
        </div>

        <div class="patient_row">
            <div class="patient_row_son">
                <label for="ciudad">Ciudad / Localidad</label>
                <select name="ciudad" id="ciudad" class="select2" disabled>
                    <option value="">Seleccione una ciudad/localidad</option>
                </select>
            </div>

            <div class="patient_row_son">
                <label for="codigo_postal">Código Postal</label>
                <input type="text" name="codigo_postal" id="codigo_postal" placeholder=""
                    value="<?php echo htmlspecialchars($patient_data['codigo_postal'] ?? ''); ?>">
            </div>

            <div class="patient_row_son">
                <label for="calle">Calle</label>
                <input type="text" name="calle" id="calle"
                    value="<?php echo htmlspecialchars($patient_data['calle'] ?? ''); ?>">
            </div>
        </div>

        <div class="patient_row">
            <div class="patient_row_son">
                <label for="numero">Número</label>
                <input type="text" name="numero" id="numero"
                    value="<?php echo htmlspecialchars($patient_data['numero'] ?? ''); ?>">
            </div>

            <div class="patient_row_son">
                <label for="piso">Piso</label>
                <input type="text" name="piso" id="piso"
                    value="<?php echo htmlspecialchars($patient_data['piso'] ?? ''); ?>">
            </div>

            <div class="patient_row_son">
                <label for="departamento">Departamento</label>
                <input type="text" name="departamento" id="departamento"
                    value="<?php echo htmlspecialchars($patient_data['departamento'] ?? ''); ?>">
            </div>
        </div>

        <div class="patient_row">
            <div class="patient_row_son">
                <label for="barrio">Barrio</label>
                <input type="text" name="barrio" id="barrio"
                    value="<?php echo htmlspecialchars($patient_data['barrio'] ?? ''); ?>">
            </div>
        </div>
    </div>

    <div class="patient_group">
        <h3>Información de Seguro de Salud</h3>
        <hr>
        <div class="patient_row">
            <div class="patient_row_son">
                <label for="health_insurance">Obra Social/Prepaga</label>
                <input type="text" name="health_insurance" id="health_insurance"
                    value="<?php echo htmlspecialchars($patient_data['health_insurance'] ?? ''); ?>">
            </div>

            <div class="patient_row_son">
                <label for="health_insurance_number">Número de Afiliado</label>
                <input type="text" name="health_insurance_number" id="health_insurance_number"
                    value="<?php echo htmlspecialchars($patient_data['health_insurance_number'] ?? ''); ?>">
            </div>
        </div>

        <div class="patient_row">

            <?php if ($patient_id_from_url) { ?>
                <button class="btn-green" id="savePatientBtn"><i class="fa-solid fa-floppy-disk"></i> Guardar</button>
            <?php } else { ?>
                <button class="btn-tematico" id="savePatientBtn"><i class="fa-solid fa-plus"></i> Nuevo paciente</button>
            <?php } ?>

            <button class="btn-green" id="editPatientBtn"><i class="fa-solid fa-pencil" type="button"></i> Editar</button>
            <button class="btn-red" id="cancelPatientBtn"><i class="fa-solid fa-xmark" type="button"></i> Cancelar</button>

            <?php if ($patient_admitted == false) { ?>
                <button class="btn-green" id="ingresarPaciente"><i class="fa-solid fa-bed" type="button"></i> Ingresar paciente</button>
            <?php } ?>
        </div>
    </div>
</div>