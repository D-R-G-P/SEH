/* Rol: gestion_camas
Subroles: administrador_camas */

CREATE TABLE
    `beds` (
        `id` INT NOT NULL AUTO_INCREMENT,
        `name` VARCHAR(255) NOT NULL,
        `description` VARCHAR(255) NULL,
        `ubicacion_arquitectura_id` INT NOT NULL,
        `complexity` ENUM (
            'Mínima',
            'Intermedia',
            'Neutropénica',
            'Intensiva'
        ) NOT NULL,
        `bed_status` ENUM (
            'Libre',
            'Reservada',
            'Ocupada',
            'Bloqueada',
            'Eliminado'
        ) NOT NULL DEFAULT 'Libre',
        `created_by` VARCHAR(10) NOT NULL,
        `date_created` DATETIME NOT NULL,
        `updated_by` VARCHAR(10) NULL,
        `date_updated` DATETIME NULL,
        `deleted_by` VARCHAR(10) NULL,
        `date_deleted` DATETIME NULL,
        `deleted_reason` VARCHAR(255) NULL,
        PRIMARY KEY (`id`),
        FOREIGN KEY (`created_by`) REFERENCES personal (`dni`),
        FOREIGN KEY (`updated_by`) REFERENCES personal (`dni`),
        FOREIGN KEY (`deleted_by`) REFERENCES personal (`dni`),
        FOREIGN KEY (`ubicacion_arquitectura_id`) REFERENCES `arquitectura` (`id`)
    );

CREATE TABLE
    `bed_blocked` (
        `id` INT NOT NULL AUTO_INCREMENT,
        `bed_id` INT NOT NULL,
        `date_blocked` DATETIME NOT NULL,
        `blocked_by` VARCHAR(10) NOT NULL,
        `blocked_type` VARCHAR(255) NOT NULL,
        `reason` TEXT NOT NULL,
        `date_unblocked` DATETIME NULL,
        `unblocked_by` VARCHAR(10) NULL,
        PRIMARY KEY (`id`),
        FOREIGN KEY (`bed_id`) REFERENCES beds (`id`),
        FOREIGN KEY (`blocked_by`) REFERENCES personal (`dni`)
    );

CREATE TABLE
    `patients_admitteds` (
        `id` INT NOT NULL AUTO_INCREMENT,
        `patient_id` INT NOT NULL,
        `bed_id` INT NOT NULL,
        `admission_date` DATETIME NOT NULL,
        `admitted_by` VARCHAR(10) NOT NULL,
        `date_discharged` DATETIME NULL,
        `discharged_by` VARCHAR(10) NULL,
        PRIMARY KEY (`id`),
        FOREIGN KEY (`patient_id`) REFERENCES patients (`id`),
        FOREIGN KEY (`bed_id`) REFERENCES beds (`id`),
        FOREIGN KEY (`admitted_by`) REFERENCES personal (`dni`),
        FOREIGN KEY (`discharged_by`) REFERENCES personal (`dni`)
    );

CREATE TABLE `pass_history` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `patient_id` INT NOT NULL,
    `admitted_id` INT NOT NULL,
    `bed_old` INT NOT NULL,
    `bed_new` INT NOT NULL,
    `date_passed` DATETIME NOT NULL,
    `passed_by` VARCHAR(10) NOT NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`patient_id`) REFERENCES patients (`id`),
    FOREIGN KEY (`admitted_id`) REFERENCES patients_admitteds (`id`),
    FOREIGN KEY (`bed_old`) REFERENCES beds (`id`),
    FOREIGN KEY (`bed_new`) REFERENCES beds (`id`),
    FOREIGN KEY (`passed_by`) REFERENCES personal (`dni`)
);

CREATE TABLE
    `patients` (
        `id` INT NOT NULL AUTO_INCREMENT,
        `last_name` LONGTEXT NOT NULL,
        `last_name_hash` VARCHAR(64) BINARY NULL DEFAULT '', -- Hash para búsqueda de apellido
        `name` LONGTEXT NOT NULL,
        `name_hash` VARCHAR(64) BINARY NULL DEFAULT '', -- Hash para búsqueda de nombre
        `administrative_name` LONGTEXT NULL, -- Nombre para trámites administrativos (nuevo)
        `administrative_name_hash` VARCHAR(64) BINARY NULL DEFAULT '', -- Hash para búsqueda de nombre administrativo
        `gender` LONGTEXT NOT NULL,
        `gender_hash` VARCHAR(64) BINARY NULL DEFAULT '', -- Hash para búsqueda de sexo (según DNI)
        `gender_identity` LONGTEXT NULL, -- Identidad de Género autopercibida (nuevo)
        `gender_identity_hash` VARCHAR(64) BINARY NULL DEFAULT '', -- Hash para búsqueda de identidad de género
        `self_perceived_name` LONGTEXT NULL, -- Nombre de pila autopercibido (nuevo)
        `self_perceived_name_hash` VARCHAR(64) BINARY NULL DEFAULT '', -- Hash para búsqueda de nombre autopercibido
        `dni_rectified` TINYINT (1) NULL DEFAULT 0, -- ¿DNI rectificado? (nuevo, booleano)
        `document_type` LONGTEXT NOT NULL,
        `document_type_hash` VARCHAR(64) BINARY NULL DEFAULT '', -- Hash para búsqueda de tipo de documento
        `document` LONGTEXT NOT NULL,
        `document_hash` VARCHAR(64) BINARY NULL DEFAULT '', -- Hash para búsqueda de número de documento
        `birth_date` LONGTEXT NOT NULL,
        `birth_date_hash` VARCHAR(64) BINARY NULL DEFAULT '', -- Hash para búsqueda de fecha de nacimiento
        `phone_number` LONGTEXT NULL,
        `phone_number_hash` VARCHAR(64) BINARY NULL DEFAULT '', -- Hash para búsqueda de teléfono 1
        `phone_number_alt` LONGTEXT NULL, -- Teléfono 2 (nuevo)
        `phone_number_alt_hash` VARCHAR(64) BINARY NULL DEFAULT '', -- Hash para búsqueda de teléfono 2
        `family_phone_number` LONGTEXT NULL,
        `family_phone_number_hash` VARCHAR(64) BINARY NULL DEFAULT '', -- Hash para búsqueda de teléfono familiar 1
        `family_phone_number_alt` LONGTEXT NULL, -- Teléfono familiar 2 (nuevo)
        `family_phone_number_alt_hash` VARCHAR(64) BINARY NULL DEFAULT '', -- Hash para búsqueda de teléfono familiar 2
        `email` LONGTEXT NULL,
        `email_hash` VARCHAR(64) BINARY NULL DEFAULT '', -- Hash para búsqueda de email
        `country` LONGTEXT NULL, -- País (nuevo)
        `country_hash` VARCHAR(64) BINARY NULL DEFAULT '', -- Hash para búsqueda de país
        `provincia` LONGTEXT NULL,
        `provincia_hash` VARCHAR(64) BINARY NULL DEFAULT '', -- Hash para búsqueda de provincia
        `partido` LONGTEXT NULL,
        `partido_hash` VARCHAR(64) BINARY NULL DEFAULT '', -- Hash para búsqueda de partido
        `ciudad` LONGTEXT NULL,
        `ciudad_hash` VARCHAR(64) BINARY NULL DEFAULT '', -- Hash para búsqueda de ciudad
        `codigo_postal` LONGTEXT NULL,
        `codigo_postal_hash` VARCHAR(64) BINARY NULL DEFAULT '', -- Hash para búsqueda de código postal
        `calle` LONGTEXT NULL,
        `calle_hash` VARCHAR(64) BINARY NULL DEFAULT '', -- Hash para búsqueda de calle
        `numero` LONGTEXT NULL,
        `numero_hash` VARCHAR(64) BINARY NULL DEFAULT '', -- Hash para búsqueda de número de calle
        `piso` LONGTEXT NULL,
        `piso_hash` VARCHAR(64) BINARY NULL DEFAULT '', -- Hash para búsqueda de piso
        `departamento` LONGTEXT NULL,
        `departamento_hash` VARCHAR(64) BINARY NULL DEFAULT '', -- Hash para búsqueda de departamento
        `barrio` LONGTEXT NULL,
        `barrio_hash` VARCHAR(64) BINARY NULL DEFAULT '', -- Hash para búsqueda de barrio
        `document_uploaded` LONGTEXT NULL,
        `document_uploaded_date` DATETIME NULL,
        `document_uploaded_by` LONGTEXT NULL,
        `health_insurance` LONGTEXT NULL,
        `health_insurance_hash` VARCHAR(64) BINARY NULL DEFAULT '', -- Hash para búsqueda de obra social/prepaga
        `health_insurance_number` LONGTEXT NULL,
        `health_insurance_number_hash` VARCHAR(64) BINARY NULL DEFAULT '', -- Hash para búsqueda de número de afiliado
        `OS_uploaded` LONGTEXT NULL,
        `OS_uploaded_date` DATETIME NULL,
        `OS_uploaded_by` LONGTEXT NULL,
        `created_by` VARCHAR(10) NOT NULL,
        `date_created` DATETIME NOT NULL,
        `updated_by` VARCHAR(10) NULL,
        `date_updated` DATETIME NULL,
        PRIMARY KEY (`id`),
        FOREIGN KEY (`created_by`) REFERENCES personal (`dni`),
        FOREIGN KEY (`updated_by`) REFERENCES personal (`dni`),
        -- Índices para las columnas de hash (OPTIMIZACIÓN DE BÚSQUEDAS)
        INDEX `idx_last_name_hash` (`last_name_hash`),
        INDEX `idx_name_hash` (`name_hash`),
        INDEX `idx_administrative_name_hash` (`administrative_name_hash`),
        INDEX `idx_gender_hash` (`gender_hash`),
        INDEX `idx_gender_identity_hash` (`gender_identity_hash`),
        INDEX `idx_self_perceived_name_hash` (`self_perceived_name_hash`),
        INDEX `idx_document_type_hash` (`document_type_hash`),
        INDEX `idx_document_hash` (`document_hash`),
        INDEX `idx_birth_date_hash` (`birth_date_hash`),
        INDEX `idx_phone_number_hash` (`phone_number_hash`),
        INDEX `idx_phone_number_alt_hash` (`phone_number_alt_hash`),
        INDEX `idx_family_phone_number_hash` (`family_phone_number_hash`),
        INDEX `idx_family_phone_number_alt_hash` (`family_phone_number_alt_hash`),
        INDEX `idx_email_hash` (`email_hash`),
        INDEX `idx_country_hash` (`country_hash`),
        INDEX `idx_provincia_hash` (`provincia_hash`),
        INDEX `idx_partido_hash` (`partido_hash`),
        INDEX `idx_ciudad_hash` (`ciudad_hash`),
        INDEX `idx_codigo_postal_hash` (`codigo_postal_hash`),
        INDEX `idx_calle_hash` (`calle_hash`),
        INDEX `idx_numero_hash` (`numero_hash`),
        INDEX `idx_piso_hash` (`piso_hash`),
        INDEX `idx_departamento_hash` (`departamento_hash`),
        INDEX `idx_barrio_hash` (`barrio_hash`),
        INDEX `idx_health_insurance_hash` (`health_insurance_hash`),
        INDEX `idx_health_insurance_number_hash` (`health_insurance_number_hash`)
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE
    `patients_documents` (
        `id` INT NOT NULL AUTO_INCREMENT,
        `patient_id` INT NOT NULL,
        `document_type` ENUM (
            'Documento',
            'Carnet OS',
            'Denuncia',
            'Informe',
            'Carta documento',
            'Otro'
        ) NOT NULL,
        `document_name` VARCHAR(255) NOT NULL,
        `uploaded_by` VARCHAR(10) NOT NULL,
        `date_uploaded` DATETIME NOT NULL,
        PRIMARY KEY (`id`),
        FOREIGN KEY (`patient_id`) REFERENCES patients (`id`),
        FOREIGN KEY (`uploaded_by`) REFERENCES personal (`dni`)
    );

/* dni
carnet os
denuncias (alta compl, dennun int, den presupuesto)
ficha anestesia
informes
carta doc.
den cuc ioma
den op pami */