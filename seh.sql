-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 03-04-2024 a las 18:46:19
-- Versión del servidor: 10.4.28-MariaDB
-- Versión de PHP: 8.2.4

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
(2, 'Administrativo', 'activo'),
(3, 'Medico', 'activo'),
(4, 'Enfermero', 'activo'),
(5, 'Director', 'activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `especialidades`
--

CREATE TABLE `especialidades` (
  `id` int(11) NOT NULL,
  `especialidad` longtext NOT NULL,
  `servicio_id` int(11) NOT NULL
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
(1, '43.255.000', '1', 'crslamas@gmail.com', '221 438-0474', '[{\"permiso\":\"Especialista M\\u00e9dix\",\"activo\":\"no\"},{\"permiso\":\"Profesional de la Salud\",\"activo\":\"no\"},{\"permiso\":\"Administrativx\",\"activo\":\"si\"},{\"permiso\":\"Enfermero\",\"activo\":\"no\"},{\"permiso\":\"Enfermerx Adultx Mayor\",\"activo\":\"no\"},{\"permiso\":\"Administrador de Agenda\",\"activo\":\"si\"},{\"permiso\":\"Especialista odontol\\u00f3gico\",\"activo\":\"no\"},{\"permiso\":\"Administrador de Camas\",\"activo\":\"no\"},{\"permiso\":\"Personal de Im\\u00e1genes\",\"activo\":\"no\"},{\"permiso\":\"Personal de Laboratorio\",\"activo\":\"no\"},{\"permiso\":\"Personal de Farmacia\",\"activo\":\"no\"},{\"permiso\":\"Personal de Estad\\u00edstica\",\"activo\":\"no\"},{\"permiso\":\"Administrador institucional\",\"activo\":\"si\"}]', '[{\"documento\":\"Copia de DNI\",\"activo\":\"verificado\"},{\"documento\":\"Copia de matr\\u00edcula profesional\",\"activo\":\"no\"},{\"documento\":\"Solicitud de alta de usuario para HSI (ANEXO I)\",\"activo\":\"verificado\"},{\"documento\":\"Declaraci\\u00f3n Jurada - Convenio de confidecialidad usuarios HSI (ANEXO II)\",\"activo\":\"verificado\"}]', '', 'habilitado', 'si', '2024-03-20', '', '1408941', '25484', 'clamas');

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
(1, 'Lamas', 'Cristian Jonathan', '43.255.000', '464741107fbef0000f0b7b88d0911df8', 2, 'Administrativo', '', '', '', '[{\"sistema\":\"Deposito\",\"activo\":\"si\"},{\"sistema\":\"Mantenimiento\",\"activo\":\"si\"},{\"sistema\":\"Informatica\",\"activo\":\"si\"}]', 'Administrador', 'Activo', 'no');

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
(1, 'Dirección Hospitalaria', '', 'Activo'),
(2, 'Direccion de Redes y Gestión de Personas', '0', 'Activo');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `cargos`
--
ALTER TABLE `cargos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `especialidades`
--
ALTER TABLE `especialidades`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `fk_servicio_id` (`servicio_id`);

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
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `cargos`
--
ALTER TABLE `cargos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `especialidades`
--
ALTER TABLE `especialidades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `hsi`
--
ALTER TABLE `hsi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `licencias`
--
ALTER TABLE `licencias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `personal`
--
ALTER TABLE `personal`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `especialidades`
--
ALTER TABLE `especialidades`
  ADD CONSTRAINT `especialidades_ibfk_1` FOREIGN KEY (`servicio_id`) REFERENCES `servicios` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_servicio_id` FOREIGN KEY (`servicio_id`) REFERENCES `servicios` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
