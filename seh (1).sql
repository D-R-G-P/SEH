-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 24-06-2024 a las 05:34:21
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `seh`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cargos`
--

CREATE TABLE `cargos` (
  `id` int(11) NOT NULL,
  `cargo` longtext NOT NULL,
  `estado` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `cargos`
--

INSERT INTO `cargos` (`id`, `cargo`, `estado`) VALUES
(1, 'Secretario', 'Activo'),
(2, 'Administrativo', 'Activo'),
(3, 'Medico', 'Activo'),
(4, 'Enfermero', 'Activo'),
(5, 'Director', 'Activo'),
(7, 'Psicologo', 'Activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `equipos`
--

CREATE TABLE `equipos` (
  `id` int(11) NOT NULL,
  `marca` varchar(255) NOT NULL,
  `modelo` longtext NOT NULL,
  `tipo` varchar(255) NOT NULL,
  `estado` varchar(255) NOT NULL,
  `problema` longtext NOT NULL,
  `servicio` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `especialidades`
--

CREATE TABLE `especialidades` (
  `id` int(11) NOT NULL,
  `especialidad` longtext NOT NULL,
  `servicio_id` int(11) NOT NULL,
  `estado` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `especialidades`
--

INSERT INTO `especialidades` (`id`, `especialidad`, `servicio_id`, `estado`) VALUES
(3, 'Gestión de Camas', 1, 'Activo'),
(4, 'Gestión de Turnos', 1, 'Activo'),
(5, 'Secretaría de Salud Mental', 4, 'Activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `guardias`
--

CREATE TABLE `guardias` (
  `id` int(11) NOT NULL,
  `mes` date NOT NULL,
  `usuario_registro` varchar(10) NOT NULL,
  `especialidad` varchar(255) NOT NULL,
  `dia` varchar(255) NOT NULL,
  `regimen` varchar(255) NOT NULL,
  `especialista` varchar(10) NOT NULL,
  `estado` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `hsi`
--

CREATE TABLE `hsi` (
  `id` int(11) NOT NULL,
  `dni` varchar(10) NOT NULL,
  `servicio` varchar(255) NOT NULL,
  `mail` varchar(255) NOT NULL,
  `telefono` varchar(255) NOT NULL,
  `permisos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`permisos`)),
  `documentos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`documentos`)),
  `observaciones` longtext NOT NULL,
  `estado` varchar(255) NOT NULL,
  `new` varchar(2) NOT NULL,
  `fecha_solicitud` date NOT NULL DEFAULT current_timestamp(),
  `pedido` longtext NOT NULL,
  `id_persona` varchar(255) NOT NULL,
  `id_usuario` varchar(255) NOT NULL,
  `nombre_usuario` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

--
-- Volcado de datos para la tabla `hsi`
--

INSERT INTO `hsi` (`id`, `dni`, `servicio`, `mail`, `telefono`, `permisos`, `documentos`, `observaciones`, `estado`, `new`, `fecha_solicitud`, `pedido`, `id_persona`, `id_usuario`, `nombre_usuario`) VALUES
(1, '43.255.000', '2', 'crslamas@gmail.com', '221 438-0474', '[{\"permiso\":\"Especialista M\\u00e9dix\",\"activo\":\"no\"},{\"permiso\":\"Profesional de la Salud\",\"activo\":\"no\"},{\"permiso\":\"Administrativx\",\"activo\":\"si\"},{\"permiso\":\"Enfermero\",\"activo\":\"no\"},{\"permiso\":\"Enfermerx Adultx Mayor\",\"activo\":\"no\"},{\"permiso\":\"Administrador de Agenda\",\"activo\":\"si\"},{\"permiso\":\"Especialista odontol\\u00f3gico\",\"activo\":\"no\"},{\"permiso\":\"Administrador de Camas\",\"activo\":\"no\"},{\"permiso\":\"Personal de Im\\u00e1genes\",\"activo\":\"no\"},{\"permiso\":\"Personal de Laboratorio\",\"activo\":\"no\"},{\"permiso\":\"Personal de Farmacia\",\"activo\":\"no\"},{\"permiso\":\"Personal de Estad\\u00edstica\",\"activo\":\"si\"},{\"permiso\":\"Administrador institucional\",\"activo\":\"si\"}]', '[{\"documento\":\"Copia de DNI\",\"activo\":\"verificado\"},{\"documento\":\"Copia de matr\\u00edcula profesional\",\"activo\":\"no\"},{\"documento\":\"Solicitud de alta de usuario para HSI (ANEXO I)\",\"activo\":\"verificado\"},{\"documento\":\"Declaraci\\u00f3n Jurada - Convenio de confidecialidad usuarios HSI (ANEXO II)\",\"activo\":\"verificado\"}]', '', 'habilitado', 'no', '2024-03-20', '', '1408941', '25484', 'clamas'),
(11, '28.129.557', '4', 'giorjettialejandromauro@gmail.com', '2215775324', '[{\"permiso\":\"Especialista M\\u00e9dix\",\"activo\":\"no\"},{\"permiso\":\"Profesional de la Salud\",\"activo\":\"no\"},{\"permiso\":\"Administrativx\",\"activo\":\"si\"},{\"permiso\":\"Enfermero\",\"activo\":\"no\"},{\"permiso\":\"Enfermerx Adultx Mayor\",\"activo\":\"no\"},{\"permiso\":\"Administrador de Agenda\",\"activo\":\"no\"},{\"permiso\":\"Especialista odontol\\u00f3gico\",\"activo\":\"no\"},{\"permiso\":\"Administrador de Camas\",\"activo\":\"no\"},{\"permiso\":\"Personal de Im\\u00e1genes\",\"activo\":\"no\"},{\"permiso\":\"Personal de Laboratorio\",\"activo\":\"no\"},{\"permiso\":\"Personal de Farmacia\",\"activo\":\"no\"},{\"permiso\":\"Personal de Estad\\u00edstica\",\"activo\":\"no\"},{\"permiso\":\"Administrador institucional\",\"activo\":\"no\"}]', '[{\"documento\":\"Copia de DNI\",\"activo\":\"verificado\"},{\"documento\":\"Copia de matr\\u00edcula profesional\",\"activo\":\"no\"},{\"documento\":\"Solicitud de alta de usuario para HSI (ANEXO I)\",\"activo\":\"verificado\"},{\"documento\":\"Declaraci\\u00f3n Jurada - Convenio de confidecialidad usuarios HSI (ANEXO II)\",\"activo\":\"verificado\"}]', 'Usuario creado correctamente, las credenciales fueron enviadas al mail del agente. Cuenta con 48 horas para acceder. (31/05/2024 14:30)', 'habilitado', 'no', '2024-05-31', '', '2068867', '31347', ''),
(12, '34.818.147', '4', 'isabel-caamano@hotmail.com', '2215763830', '[{\"permiso\":\"Especialista M\\u00e9dix\",\"activo\":\"no\"},{\"permiso\":\"Profesional de la Salud\",\"activo\":\"no\"},{\"permiso\":\"Administrativx\",\"activo\":\"si\"},{\"permiso\":\"Enfermero\",\"activo\":\"no\"},{\"permiso\":\"Enfermerx Adultx Mayor\",\"activo\":\"no\"},{\"permiso\":\"Administrador de Agenda\",\"activo\":\"no\"},{\"permiso\":\"Especialista odontol\\u00f3gico\",\"activo\":\"no\"},{\"permiso\":\"Administrador de Camas\",\"activo\":\"no\"},{\"permiso\":\"Personal de Im\\u00e1genes\",\"activo\":\"no\"},{\"permiso\":\"Personal de Laboratorio\",\"activo\":\"no\"},{\"permiso\":\"Personal de Farmacia\",\"activo\":\"no\"},{\"permiso\":\"Personal de Estad\\u00edstica\",\"activo\":\"no\"},{\"permiso\":\"Administrador institucional\",\"activo\":\"no\"}]', '[{\"documento\":\"Copia de DNI\",\"activo\":\"verificado\"},{\"documento\":\"Copia de matr\\u00edcula profesional\",\"activo\":\"no\"},{\"documento\":\"Solicitud de alta de usuario para HSI (ANEXO I)\",\"activo\":\"verificado\"},{\"documento\":\"Declaraci\\u00f3n Jurada - Convenio de confidecialidad usuarios HSI (ANEXO II)\",\"activo\":\"verificado\"}]', 'Usuario creado correctamente, las credenciales fueron enviadas al mail del agente. Cuenta con 48 horas para acceder. (31/05/2024 14:33)', 'habilitado', 'no', '2024-05-31', '', '2068935', '31348', 'Facundo Surraco'),
(13, '31.531.781', '4', 'luisgabrielpenalva@gmail.com', '2215578616', '[{\"permiso\":\"Especialista M\\u00e9dix\",\"activo\":\"no\"},{\"permiso\":\"Profesional de la Salud\",\"activo\":\"no\"},{\"permiso\":\"Administrativx\",\"activo\":\"si\"},{\"permiso\":\"Enfermero\",\"activo\":\"no\"},{\"permiso\":\"Enfermerx Adultx Mayor\",\"activo\":\"no\"},{\"permiso\":\"Administrador de Agenda\",\"activo\":\"no\"},{\"permiso\":\"Especialista odontol\\u00f3gico\",\"activo\":\"no\"},{\"permiso\":\"Administrador de Camas\",\"activo\":\"no\"},{\"permiso\":\"Personal de Im\\u00e1genes\",\"activo\":\"no\"},{\"permiso\":\"Personal de Laboratorio\",\"activo\":\"no\"},{\"permiso\":\"Personal de Farmacia\",\"activo\":\"no\"},{\"permiso\":\"Personal de Estad\\u00edstica\",\"activo\":\"no\"},{\"permiso\":\"Administrador institucional\",\"activo\":\"no\"}]', '[{\"documento\":\"Copia de DNI\",\"activo\":\"verificado\"},{\"documento\":\"Copia de matr\\u00edcula profesional\",\"activo\":\"no\"},{\"documento\":\"Solicitud de alta de usuario para HSI (ANEXO I)\",\"activo\":\"verificado\"},{\"documento\":\"Declaraci\\u00f3n Jurada - Convenio de confidecialidad usuarios HSI (ANEXO II)\",\"activo\":\"verificado\"}]', 'Usuario creado correctamente, las credenciales fueron enviadas al mail del agente. Cuenta con 48 horas para acceder. (31/05/2024 14:36)', 'habilitado', 'no', '2024-05-31', '', '1213627', '23361', 'luis.peñalva');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `licencias`
--

CREATE TABLE `licencias` (
  `id` int(11) NOT NULL,
  `dni` varchar(10) NOT NULL,
  `fecha_desde` date NOT NULL,
  `fecha_hasta` date NOT NULL,
  `tipo_licencia` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `licencias`
--

INSERT INTO `licencias` (`id`, `dni`, `fecha_desde`, `fecha_hasta`, `tipo_licencia`) VALUES
(5, '43.255.000', '2024-04-18', '2024-04-18', 'Clave 26 - Por causas particulares');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mantenimiento`
--

CREATE TABLE `mantenimiento` (
  `id` int(11) NOT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `servicio` varchar(255) NOT NULL,
  `localizacion_explicada` longtext NOT NULL,
  `problema` longtext NOT NULL,
  `estado_reclamante` varchar(255) NOT NULL,
  `reclamante` varchar(10) NOT NULL,
  `observacion_mantenimiento` longtext NOT NULL,
  `estado_mantenimiento` varchar(255) NOT NULL,
  `modificador_mantenimiento` varchar(10) NOT NULL,
  `fecha_reclamante` date NOT NULL,
  `fecha_mantenimiento` date NOT NULL,
  `new_reclamante` varchar(255) NOT NULL,
  `new_mantenimiento` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

--
-- Volcado de datos para la tabla `mantenimiento`
--

INSERT INTO `mantenimiento` (`id`, `fecha`, `servicio`, `localizacion_explicada`, `problema`, `estado_reclamante`, `reclamante`, `observacion_mantenimiento`, `estado_mantenimiento`, `modificador_mantenimiento`, `fecha_reclamante`, `fecha_mantenimiento`, `new_reclamante`, `new_mantenimiento`) VALUES
(4, '2024-06-23 21:29:50', '2', 'dsa', 'asd', 'Pendiente', '43.255.000', '', 'Pendiente', '', '0000-00-00', '0000-00-00', 'no', 'si');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `personal`
--

CREATE TABLE `personal` (
  `id` int(11) NOT NULL,
  `apellido` varchar(255) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `dni` varchar(10) NOT NULL,
  `password` varchar(255) NOT NULL,
  `servicio_id` int(11) NOT NULL,
  `cargo` varchar(255) NOT NULL,
  `especialidad` longtext NOT NULL,
  `mn` varchar(7) NOT NULL,
  `mp` varchar(7) NOT NULL,
  `sistemas` longtext NOT NULL,
  `rol` varchar(255) NOT NULL,
  `estado` varchar(255) NOT NULL,
  `pr` varchar(2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `personal`
--

INSERT INTO `personal` (`id`, `apellido`, `nombre`, `dni`, `password`, `servicio_id`, `cargo`, `especialidad`, `mn`, `mp`, `sistemas`, `rol`, `estado`, `pr`) VALUES
(1, 'Lamas', 'Cristian Jonathan', '43.255.000', 'c609e2679e857de8513425ed61fab135', 2, 'Administrativo', '', '', '', '[{\"sistema\": \"Deposito\", \"activo\": \"no\"}, {\"sistema\": \"Mantenimiento\", \"activo\": \"no\"}, {\"sistema\": \"Informatica\", \"activo\": \"no\"}]', 'Administrador', 'Activo', 'si'),
(11, 'Kopelovich', 'Mercedes', '30.777.309', '', 3, 'Jefe de servicio', '', '', '', '[{\"sistema\":\"Deposito\",\"activo\":\"si\"},{\"sistema\":\"Mantenimiento\",\"activo\":\"si\"},{\"sistema\":\"Informatica\",\"activo\":\"si\"}]', 'Jefe de servicio', 'Activo', ''),
(12, 'Spoto', 'Marisa Laura', '21.706.596', '', 4, 'Jefe de servicio', '', '', '', '[{\"sistema\":\"Deposito\",\"activo\":\"no\"},{\"sistema\":\"Mantenimiento\",\"activo\":\"no\"},{\"sistema\":\"Informatica\",\"activo\":\"no\"}]', 'Jefe de servicio', 'Activo', ''),
(13, 'Ramos Romero', 'Graciela', '13.143.151', '', 1, 'Jefe de servicio', '', '', '', '[{\"sistema\":\"Deposito\",\"activo\":\"no\"},{\"sistema\":\"Mantenimiento\",\"activo\":\"no\"},{\"sistema\":\"Informatica\",\"activo\":\"no\"}]', 'Dirección', 'Activo', ''),
(14, 'Russo', 'Deborah Maria', '31.795.339', '', 2, 'Jefe de servicio', '', '', '', '[{\"sistema\":\"Deposito\",\"activo\":\"no\"},{\"sistema\":\"Mantenimiento\",\"activo\":\"no\"},{\"sistema\":\"Informatica\",\"activo\":\"no\"}]', 'Jefe de servicio', 'Activo', ''),
(15, 'Giorgetti', 'Alejandro Mauro', '28.129.557', '', 4, 'Secretario', 'Secretaría de Salud Mental', '', '', '[{\"sistema\":\"Deposito\",\"activo\":\"no\"},{\"sistema\":\"Mantenimiento\",\"activo\":\"no\"},{\"sistema\":\"Informatica\",\"activo\":\"no\"}]', 'Sin rol', 'Activo', ''),
(16, 'Surraco', 'Facundo', '34.818.147', '', 4, 'Secretario', 'Secretaría de Salud Mental', '', '', '[{\"sistema\":\"Deposito\",\"activo\":\"no\"},{\"sistema\":\"Mantenimiento\",\"activo\":\"no\"},{\"sistema\":\"Informatica\",\"activo\":\"no\"}]', 'Sin rol', 'Activo', ''),
(17, 'Peñalva', 'Luis Gabriel', '31.531.781', '', 4, 'Secretario', 'Secretaría de Salud Mental', '', '', '[{\"sistema\":\"Deposito\",\"activo\":\"no\"},{\"sistema\":\"Mantenimiento\",\"activo\":\"no\"},{\"sistema\":\"Informatica\",\"activo\":\"no\"}]', 'Sin rol', 'Activo', '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `servicios`
--

CREATE TABLE `servicios` (
  `id` int(11) NOT NULL,
  `servicio` longtext NOT NULL,
  `jefe` varchar(10) NOT NULL,
  `estado` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `servicios`
--

INSERT INTO `servicios` (`id`, `servicio`, `jefe`, `estado`) VALUES
(1, 'Dirección Hospitalaria', '13.143.151', 'Activo'),
(2, 'Direccion de Redes y Gestión de Personas', '31.795.339', 'Activo'),
(3, 'Salud Mental', '30.777.309', 'Activo'),
(4, 'Estadística', '21.706.596', 'Activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipos_equipo`
--

CREATE TABLE `tipos_equipo` (
  `id` int(11) NOT NULL,
  `tipo_equipo` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `tipos_equipo`
--

INSERT INTO `tipos_equipo` (`id`, `tipo_equipo`) VALUES
(1, 'Tomografo Axial Computado'),
(22, 'Resonador Magnético Nuclear');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `cargos`
--
ALTER TABLE `cargos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `equipos`
--
ALTER TABLE `equipos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `especialidades`
--
ALTER TABLE `especialidades`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `fk_servicio_id` (`servicio_id`);

--
-- Indices de la tabla `guardias`
--
ALTER TABLE `guardias`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `hsi`
--
ALTER TABLE `hsi`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `dni` (`dni`);

--
-- Indices de la tabla `licencias`
--
ALTER TABLE `licencias`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `mantenimiento`
--
ALTER TABLE `mantenimiento`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `personal`
--
ALTER TABLE `personal`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `servicios`
--
ALTER TABLE `servicios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `jefe` (`jefe`);

--
-- Indices de la tabla `tipos_equipo`
--
ALTER TABLE `tipos_equipo`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `cargos`
--
ALTER TABLE `cargos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `equipos`
--
ALTER TABLE `equipos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `especialidades`
--
ALTER TABLE `especialidades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `guardias`
--
ALTER TABLE `guardias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `hsi`
--
ALTER TABLE `hsi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `licencias`
--
ALTER TABLE `licencias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `mantenimiento`
--
ALTER TABLE `mantenimiento`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `personal`
--
ALTER TABLE `personal`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `servicios`
--
ALTER TABLE `servicios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `tipos_equipo`
--
ALTER TABLE `tipos_equipo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `especialidades`
--
ALTER TABLE `especialidades`
  ADD CONSTRAINT `fk_servicio_id` FOREIGN KEY (`servicio_id`) REFERENCES `servicios` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
