-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 20-03-2024 a las 22:32:20
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
(1, '43.255.000', '2', 'crslamas@gmail.com', '221 438-0474', '[{\"permiso\":\"Especialista M\\u00e9dix\",\"activo\":\"no\"},{\"permiso\":\"Profesional de la Salud\",\"activo\":\"no\"},{\"permiso\":\"Administrativx\",\"activo\":\"si\"},{\"permiso\":\"Enfermero\",\"activo\":\"no\"},{\"permiso\":\"Enfermerx Adultx Mayor\",\"activo\":\"no\"},{\"permiso\":\"Administrador de Agenda\",\"activo\":\"si\"},{\"permiso\":\"Especialista odontol\\u00f3gico\",\"activo\":\"no\"},{\"permiso\":\"Administrador de Camas\",\"activo\":\"si\"},{\"permiso\":\"Personal de Im\\u00e1genes\",\"activo\":\"no\"},{\"permiso\":\"Personal de Laboratorio\",\"activo\":\"no\"},{\"permiso\":\"Personal de Farmacia\",\"activo\":\"no\"},{\"permiso\":\"Personal de Estad\\u00edstica\",\"activo\":\"no\"},{\"permiso\":\"Administrador institucional\",\"activo\":\"si\"}]', '[{\"documento\":\"Copia de DNI\",\"activo\":\"no\"},{\"documento\":\"Copia de matr\\u00edcula profesional\",\"activo\":\"no\"},{\"documento\":\"Solicitud de alta de usuario para HSI (ANEXO I)\",\"activo\":\"no\"},{\"documento\":\"Declaraci\\u00f3n Jurada - Convenio de confidecialidad usuarios HSI (ANEXO II)\",\"activo\":\"no\"}]', 'Usuario pendiente de aprobación', 'habilitado', 'no', '2024-03-20', '', '', '', ''),
(2, '31.795.339', '2', 'drgphigasanmartin@gmail.com', '221', '[{\"permiso\":\"Especialista M\\u00e9dix\",\"activo\":\"no\"},{\"permiso\":\"Profesional de la Salud\",\"activo\":\"no\"},{\"permiso\":\"Administrativx\",\"activo\":\"si\"},{\"permiso\":\"Enfermero\",\"activo\":\"si\"},{\"permiso\":\"Enfermerx Adultx Mayor\",\"activo\":\"no\"},{\"permiso\":\"Administrador de Agenda\",\"activo\":\"si\"},{\"permiso\":\"Especialista odontol\\u00f3gico\",\"activo\":\"no\"},{\"permiso\":\"Administrador de Camas\",\"activo\":\"si\"},{\"permiso\":\"Personal de Im\\u00e1genes\",\"activo\":\"no\"},{\"permiso\":\"Personal de Laboratorio\",\"activo\":\"no\"},{\"permiso\":\"Personal de Farmacia\",\"activo\":\"no\"},{\"permiso\":\"Personal de Estad\\u00edstica\",\"activo\":\"no\"},{\"permiso\":\"Administrador institucional\",\"activo\":\"no\"}]', '[{\"documento\":\"Copia de DNI\",\"activo\":\"no\"},{\"documento\":\"Copia de matr\\u00edcula profesional\",\"activo\":\"no\"},{\"documento\":\"Solicitud de alta de usuario para HSI (ANEXO I)\",\"activo\":\"no\"},{\"documento\":\"Declaraci\\u00f3n Jurada - Convenio de confidecialidad usuarios HSI (ANEXO II)\",\"activo\":\"no\"}]', 'Usuario pendiente de aprobación', 'habilitado', 'no', '2024-03-20', '', '', '', ''),
(3, '37.934.567', '2', 'drgphigasanmartin@gmail.com', '221', '[{\"permiso\":\"Especialista M\\u00e9dix\",\"activo\":\"no\"},{\"permiso\":\"Profesional de la Salud\",\"activo\":\"no\"},{\"permiso\":\"Administrativx\",\"activo\":\"si\"},{\"permiso\":\"Enfermero\",\"activo\":\"no\"},{\"permiso\":\"Enfermerx Adultx Mayor\",\"activo\":\"no\"},{\"permiso\":\"Administrador de Agenda\",\"activo\":\"no\"},{\"permiso\":\"Especialista odontol\\u00f3gico\",\"activo\":\"no\"},{\"permiso\":\"Administrador de Camas\",\"activo\":\"si\"},{\"permiso\":\"Personal de Im\\u00e1genes\",\"activo\":\"no\"},{\"permiso\":\"Personal de Laboratorio\",\"activo\":\"no\"},{\"permiso\":\"Personal de Farmacia\",\"activo\":\"no\"},{\"permiso\":\"Personal de Estad\\u00edstica\",\"activo\":\"no\"},{\"permiso\":\"Administrador institucional\",\"activo\":\"no\"}]', '[{\"documento\":\"Copia de DNI\",\"activo\":\"no\"},{\"documento\":\"Copia de matr\\u00edcula profesional\",\"activo\":\"no\"},{\"documento\":\"Solicitud de alta de usuario para HSI (ANEXO I)\",\"activo\":\"no\"},{\"documento\":\"Declaraci\\u00f3n Jurada - Convenio de confidecialidad usuarios HSI (ANEXO II)\",\"activo\":\"no\"}]', 'Usuario pendiente de aprobación', 'working', 'no', '2024-03-20', '', '', '', '');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `hsi`
--
ALTER TABLE `hsi`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `dni` (`dni`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `hsi`
--
ALTER TABLE `hsi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
