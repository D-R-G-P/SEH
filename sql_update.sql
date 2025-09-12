/* Rol: hsi
Subroles: capacitaciones_hsi */

CREATE TABLE capacitaciones_hsi (
    id INT NOT NULL AUTO_INCREMENT,
    titulo VARCHAR(255) NOT NULL,
    descripcion TEXT NOT NULL,
    rol_asociado VARCHAR(255) NOT NULL,
    created_by VARCHAR(10) NOT NULL,
    date_created DATETIME NOT NULL,
    updated_by VARCHAR(10) NULL,
    date_updated DATETIME NULL,
    deleted_by VARCHAR(10) NULL,
    date_deleted DATETIME NULL,
    deleted_reason VARCHAR(255) NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (created_by) REFERENCES personal (dni),
    FOREIGN KEY (updated_by) REFERENCES personal (dni),
    FOREIGN KEY (deleted_by) REFERENCES personal (dni)
);

CREATE TABLE instancias_capacitacion (
    id INT NOT NULL AUTO_INCREMENT,
    capacitacion_id INT NOT NULL,
    fecha DATE NOT NULL,
    hora TIME NOT NULL,
    lugar VARCHAR(255) NOT NULL,
    estado ENUM (
        'programada',
        'completada',
        'cancelada',
        'en_curso',
        'reprogramada',
        'cerrada'
    ) NOT NULL DEFAULT 'programada',
    PRIMARY KEY (id),
    FOREIGN KEY (capacitacion_id) REFERENCES capacitaciones_hsi (id)
);

CREATE TABLE temporal_users (
    id INT NOT NULL AUTO_INCREMENT,
    apellidos VARCHAR(255) NOT NULL,
    nombres VARCHAR(255) NOT NULL,
    dni VARCHAR(10) NOT NULL UNIQUE,
    service_id INT NOT NULL,
    fecha_nacimiento DATE NOT NULL,
    telefono VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    PRIMARY KEY (id)
    FOREIGN KEY (service_id) REFERENCES servicios (id)
);

CREATE TABLE inscripciones (
    id INT NOT NULL AUTO_INCREMENT,
    instancia_id INT NOT NULL,
    agente_dni VARCHAR(10) NULL, -- Clave foránea para usuarios registrados (puede ser NULL)
    temporal_user_id INT NULL,   -- Clave foránea para usuarios temporales (puede ser NULL)
    estado ENUM ('inscripto', 'presente', 'ausente', 'cancelado') NOT NULL DEFAULT 'inscripto',
    date_inscripcion DATETIME NOT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (instancia_id) REFERENCES instancias_capacitacion (id),
    FOREIGN KEY (agente_dni) REFERENCES personal (dni),
    FOREIGN KEY (temporal_user_id) REFERENCES temporal_users (id),
    CONSTRAINT chk_one_user_type CHECK ( (agente_dni IS NOT NULL AND temporal_user_id IS NULL) OR (agente_dni IS NULL AND temporal_user_id IS NOT NULL) )
);