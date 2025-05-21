/* 
ROL: gestion_turnos
SUBROL: chat_turnos, chat_turnos_adm, bot_turnos
 */
CREATE TABLE
    `atention_days_turnos` (
        `id` int (11) NOT NULL,
        `day_name` varchar(10) NOT NULL,
        `enabled` tinyint (1) NOT NULL DEFAULT 0,
        `start_time` time DEFAULT NULL,
        `end_time` time DEFAULT NULL
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `atention_days_turnos`
--
INSERT INTO
    `atention_days_turnos` (
        `id`,
        `day_name`,
        `enabled`,
        `start_time`,
        `end_time`
    )
VALUES
    (1, 'lunes', 1, '08:00:00', '14:00:00'),
    (2, 'martes', 1, '08:00:00', '14:00:00'),
    (3, 'miércoles', 1, '08:00:00', '14:00:00'),
    (4, 'jueves', 1, '08:00:00', '14:00:00'),
    (5, 'viernes', 1, '08:00:00', '14:00:00'),
    (6, 'sábado', 0, NULL, NULL),
    (7, 'domingo', 0, NULL, NULL);

-- --------------------------------------------------------
--
-- Estructura de tabla para la tabla `chats`
--
CREATE TABLE
    `chats` (
        `id` int (11) NOT NULL,
        `paciente_id` int (11) NOT NULL,
        `numero` varchar(255) NOT NULL,
        `asignado` varchar(10) NOT NULL,
        `estado` varchar(255) NOT NULL,
        `fecha_inicio` timestamp NOT NULL DEFAULT current_timestamp(),
        `fecha_cierre` timestamp NULL DEFAULT NULL
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_spanish_ci;

-- --------------------------------------------------------
--
-- Estructura de tabla para la tabla `comandos`
--
CREATE TABLE
    `comandos` (
        `id` int (11) NOT NULL,
        `comando` varchar(255) NOT NULL,
        `texto` longtext NOT NULL,
        `estado` varchar(255) NOT NULL
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_spanish_ci;

-- --------------------------------------------------------
--
-- Estructura de tabla para la tabla `contacts`
--
CREATE TABLE
    `contacts` (
        `id` int (11) NOT NULL,
        `name` varchar(255) NOT NULL,
        `number` varchar(20) NOT NULL,
        `status` varchar(255) NOT NULL,
        `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_spanish_ci;

-- --------------------------------------------------------
--
-- Estructura de tabla para la tabla `contenido_pasos`
--
CREATE TABLE
    `contenido_pasos` (
        `id` int (11) NOT NULL,
        `titulo` varchar(255) NOT NULL,
        `texto_completo` text NOT NULL,
        `estado` enum ('activo', 'inactivo') DEFAULT 'activo',
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_spanish_ci;

-- --------------------------------------------------------
--
-- Estructura de tabla para la tabla `opciones_principales`
--
CREATE TABLE
    `opciones_principales` (
        `id` int (11) NOT NULL,
        `servicio_id` int (11) NOT NULL,
        `parent_opcion_id` int (11) DEFAULT NULL,
        `texto_opcion` varchar(255) NOT NULL,
        `paso_asociado_id` int (11) DEFAULT NULL,
        `texto_contenido` text DEFAULT NULL,
        `estado` enum ('activo', 'inactivo') DEFAULT 'activo',
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_spanish_ci;

-- --------------------------------------------------------
--
-- Estructura de tabla para la tabla `pacientes_chat`
--
CREATE TABLE
    `pacientes_chat` (
        `id` int (11) NOT NULL,
        `apellidos` longtext DEFAULT NULL,
        `nombres` longtext DEFAULT NULL,
        `sexo` longtext DEFAULT NULL,
        `tipo_documento` longtext DEFAULT NULL,
        `documento` longtext DEFAULT NULL,
        `fecha_nacimiento` longtext DEFAULT NULL,
        `identidad_genero` longtext DEFAULT NULL,
        `nombre_autopercibido` longtext DEFAULT NULL,
        `provincia` longtext DEFAULT NULL,
        `partido` longtext DEFAULT NULL,
        `ciudad` longtext DEFAULT NULL,
        `calle` longtext DEFAULT NULL,
        `numero` longtext DEFAULT NULL,
        `piso` longtext DEFAULT NULL,
        `departamento` longtext DEFAULT NULL,
        `telefono` varchar(20) DEFAULT NULL,
        `profile_pic` longtext NOT NULL,
        `mail` longtext DEFAULT NULL,
        `obra_social` longtext DEFAULT NULL,
        `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
        `estado_conversacion` enum (
            'inicio',
            'esperando_nombre',
            'esperando_datos',
            'completado'
        ) DEFAULT 'inicio'
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_spanish_ci;

-- --------------------------------------------------------
--
-- Estructura de tabla para la tabla `servicios_turnos_bot`
--
CREATE TABLE
    `servicios_turnos_bot` (
        `id` int (11) NOT NULL,
        `nombre` varchar(255) NOT NULL,
        `descripcion` text DEFAULT NULL,
        `estado` enum ('activo', 'inactivo') DEFAULT 'activo',
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_spanish_ci;

-- --------------------------------------------------------
--
-- Estructura de tabla para la tabla `sub_opciones`
--
CREATE TABLE
    `sub_opciones` (
        `id` int (11) NOT NULL,
        `paso_origen_id` int (11) NOT NULL,
        `texto_sub_opcion` varchar(255) NOT NULL,
        `paso_destino_id` int (11) DEFAULT NULL,
        `estado` enum ('activo', 'inactivo') DEFAULT 'activo',
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_spanish_ci;

-- --------------------------------------------------------
--
-- Estructura de tabla para la tabla `wsp_messages`
--
CREATE TABLE
    `wsp_messages` (
        `id` int (11) NOT NULL,
        `message_sid` text NOT NULL,
        `numero` varchar(20) NOT NULL,
        `mensaje` longtext NOT NULL,
        `chat_id` int (11) NOT NULL,
        `estado` varchar(255) NOT NULL,
        `remitente` varchar(255) NOT NULL,
        `timestamp` datetime NOT NULL,
        `open` int (1) DEFAULT NULL,
        `opened_at` datetime DEFAULT NULL,
        `opened_for` varchar(10) DEFAULT NULL
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_spanish_ci;

-- --------------------------------------------------------
--
-- Estructura de tabla para la tabla `wsp_responses`
--
CREATE TABLE
    `wsp_responses` (
        `id` int (11) NOT NULL,
        `response_to` varchar(255) NOT NULL,
        `message` longtext NOT NULL,
        `state` varchar(20) NOT NULL
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `wsp_responses`
--
INSERT INTO
    `wsp_responses` (`id`, `response_to`, `message`, `state`)
VALUES
    (
        1,
        'welcome',
        '📢 ¡Bienvenido/a al sistema de turnos del HIGA General San Martín!\r\n\r\nHola 👋, soy el asistente virtual de gestión de turnos del hospital. Estoy aquí para ayudarte a:\r\n\r\n1️⃣ Hablar con un agente\r\n2️⃣ Obtener información sobre especialidades y horarios\r\n\r\n💡 Responde con el número de la opción que necesitas y te guiaré paso a paso.\r\n\r\n📅 Horario de atención con agentes humanos: Lunes a viernes de 8:00 a 17:00 hs.\r\n🚨 En caso de urgencia, dirígete a la guardia o llama al 107.',
        'on'
    ),
    (
        2,
        'farewell_default',
        '¡Gracias por comunicarte con el sistema de Gestión de Turnos del HIGA General San Martín! 📅🏥 Si necesitas más información, puedes escribirnos en cualquier momento. Recuerda que la atención con agentes está disponible de 8:00 a 17:00 hs. Para urgencias, te recomendamos comunicarte directamente al hospital. ¡Que tengas un buen día! 😊',
        'on'
    );

--
-- Índices para tablas volcadas
--
--
-- Indices de la tabla `atention_days_turnos`
--
ALTER TABLE `atention_days_turnos` ADD PRIMARY KEY (`id`),
ADD UNIQUE KEY `day_name` (`day_name`);

--
-- Indices de la tabla `chats`
--
ALTER TABLE `chats` ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `comandos`
--
ALTER TABLE `comandos` ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `contacts`
--
ALTER TABLE `contacts` ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `contenido_pasos`
--
ALTER TABLE `contenido_pasos` ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `opciones_principales`
--
ALTER TABLE `opciones_principales` ADD PRIMARY KEY (`id`),
ADD KEY `servicio_id` (`servicio_id`),
ADD KEY `parent_opcion_id` (`parent_opcion_id`),
ADD KEY `paso_asociado_id` (`paso_asociado_id`);

--
-- Indices de la tabla `pacientes_chat`
--
ALTER TABLE `pacientes_chat` ADD PRIMARY KEY (`id`),
ADD UNIQUE KEY `telefono` (`telefono`);

--
-- Indices de la tabla `servicios_turnos_bot`
--
ALTER TABLE `servicios_turnos_bot` ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `sub_opciones`
--
ALTER TABLE `sub_opciones` ADD PRIMARY KEY (`id`),
ADD KEY `paso_origen_id` (`paso_origen_id`),
ADD KEY `paso_destino_id` (`paso_destino_id`);

--
-- Indices de la tabla `wsp_messages`
--
ALTER TABLE `wsp_messages` ADD PRIMARY KEY (`id`),
ADD KEY `chat_id` (`chat_id`);

--
-- Indices de la tabla `wsp_responses`
--
ALTER TABLE `wsp_responses` ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--
--
-- AUTO_INCREMENT de la tabla `atention_days_turnos`
--
ALTER TABLE `atention_days_turnos` MODIFY `id` int (11) NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 8;

--
-- AUTO_INCREMENT de la tabla `chats`
--
ALTER TABLE `chats` MODIFY `id` int (11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `comandos`
--
ALTER TABLE `comandos` MODIFY `id` int (11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `contacts`
--
ALTER TABLE `contacts` MODIFY `id` int (11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `contenido_pasos`
--
ALTER TABLE `contenido_pasos` MODIFY `id` int (11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `opciones_principales`
--
ALTER TABLE `opciones_principales` MODIFY `id` int (11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pacientes_chat`
--
ALTER TABLE `pacientes_chat` MODIFY `id` int (11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `servicios_turnos_bot`
--
ALTER TABLE `servicios_turnos_bot` MODIFY `id` int (11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `sub_opciones`
--
ALTER TABLE `sub_opciones` MODIFY `id` int (11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `wsp_messages`
--
ALTER TABLE `wsp_messages` MODIFY `id` int (11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `wsp_responses`
--
ALTER TABLE `wsp_responses` MODIFY `id` int (11) NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 3;

--
-- Restricciones para tablas volcadas
--
--
-- Filtros para la tabla `opciones_principales`
--
ALTER TABLE `opciones_principales` ADD CONSTRAINT `opciones_principales_ibfk_1` FOREIGN KEY (`servicio_id`) REFERENCES `servicios_turnos_bot` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `opciones_principales_ibfk_2` FOREIGN KEY (`parent_opcion_id`) REFERENCES `opciones_principales` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
ADD CONSTRAINT `opciones_principales_ibfk_3` FOREIGN KEY (`paso_asociado_id`) REFERENCES `contenido_pasos` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `sub_opciones`
--
ALTER TABLE `sub_opciones` ADD CONSTRAINT `sub_opciones_ibfk_1` FOREIGN KEY (`paso_origen_id`) REFERENCES `contenido_pasos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `sub_opciones_ibfk_2` FOREIGN KEY (`paso_destino_id`) REFERENCES `contenido_pasos` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;

/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;

/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;