CREATE TABLE tipo_sitio_grupo (
  id int(11) NOT NULL AUTO_INCREMENT,
  nombre varchar(255) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

CREATE TABLE tipo_sitio (
  id int(11) NOT NULL AUTO_INCREMENT,
  nombre varchar(255) NOT NULL,
  grupo_id int(11) NOT NULL,
  PRIMARY KEY (id),
  KEY grupo_id (grupo_id),
  CONSTRAINT tipo_sitio_ibfk_1 FOREIGN KEY (grupo_id) REFERENCES tipo_sitio_grupo (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

CREATE TABLE `arquitectura` (
  `id` int(11) NOT NULL,
  `servicio` int(11) DEFAULT NULL,
  `nombre` varchar(255) NOT NULL,
  `observaciones` longtext NOT NULL,
  `u_padre` int(11) DEFAULT NULL,
  `tipo_sitio_id` int(11) NOT NULL,
  `estado` varchar(255) NOT NULL,
  `has_children` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

ALTER TABLE `arquitectura`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_u_padre` (`u_padre`),
  ADD KEY `fk_tipo_sitio` (`tipo_sitio_id`);
  ALTER TABLE `arquitectura`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

  ALTER TABLE `arquitectura`
  ADD CONSTRAINT `fk_tipo_sitio` FOREIGN KEY (`tipo_sitio_id`) REFERENCES `tipo_sitio` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_u_padre` FOREIGN KEY (`u_padre`) REFERENCES `arquitectura` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- Grupos de sitios físicos
INSERT INTO tipo_sitio_grupo (nombre) VALUES
('👨‍⚕️ Áreas de Atención Profesional'),
('🏢 Áreas Administrativas'),
('🔧 Áreas de Apoyo y Servicios'),
('⚙️ Infraestructura Técnica'),
('🌳 Espacios Comunes');

-- Áreas de Atención Médica
INSERT INTO tipo_sitio (nombre, grupo_id) VALUES
('Consultorios', 1),
('Consultorio', 1),
('Sala de internación', 1),
('Habitación', 1),
('Sala de partos', 1),
('Sala de neonatología', 1),
('Quirófano', 1),
('Sala de emergencia', 1),
('Guardia', 1),
('Sala de rehabilitación', 1),
('Laboratorio', 1);

-- Áreas Administrativas
INSERT INTO tipo_sitio (nombre, grupo_id) VALUES
('Dirección', 2),
('Oficina', 2),
('Archivo de historias clínicas', 2),
('Mesa de entrada', 2);

-- Áreas de Apoyo y Servicios
INSERT INTO tipo_sitio (nombre, grupo_id) VALUES
('Farmacia', 3),
('Depósito', 3),
('Cocina/comedor', 3),
('Lavandería', 3),
('Sala de mantenimiento', 3),
('Sala de esterilización', 3),
('Sala de servicios', 3);

-- Infraestructura Técnica
INSERT INTO tipo_sitio (nombre, grupo_id) VALUES
('Sala de servidores', 4),
('Sala de telefonía', 4),
('Sala de máquinas', 4);

-- Espacios Comunes
INSERT INTO tipo_sitio (nombre, grupo_id) VALUES
('Pasillo', 5),
('Sala de espera', 5),
('Baño', 5),
('Jardín/patio', 5),
('Pabellón', 5),
('Planta', 5);

INSERT INTO `arquitectura` (`id`, `servicio`, `nombre`, `observaciones`, `u_padre`, `tipo_sitio_id`, `estado`, `has_children`) VALUES
(1, NULL, 'Hospital Interzonal General de Agudos General San Martín', '', NULL, 79, 'activo', 1);