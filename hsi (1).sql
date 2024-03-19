-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 19-03-2024 a las 01:30:12
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
  `fecha_solicitud` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

--
-- Volcado de datos para la tabla `hsi`
--

INSERT INTO `hsi` (`id`, `dni`, `servicio`, `mail`, `telefono`, `permisos`, `documentos`, `observaciones`, `estado`, `new`, `fecha_solicitud`) VALUES
(1, '43.255.000', '2', 'crslamas@gmail.com', '221 438-0474', '[\n  {\n    \"permiso\": \"Especialista Médix\",\n    \"activo\": \"si\"\n  },\n  {\n    \"permiso\": \"Profesional de la Salud\",\n    \"activo\": \"si\"\n  },\n  {\n    \"permiso\": \"Administrativx\",\n    \"activo\": \"no\"\n  },\n  {\n    \"permiso\": \"Enfermero\",\n    \"activo\": \"no\"\n  },\n  {\n    \"permiso\": \"Enfermerx Adultx Mayor\",\n    \"activo\": \"no\"\n  },\n  {\n    \"permiso\": \"Administrador de Agenda\",\n    \"activo\": \"no\"\n  },\n  {\n    \"permiso\": \"Especialista odontológico\",\n    \"activo\": \"no\"\n  },\n  {\n    \"permiso\": \"Administrador de Camas\",\n    \"activo\": \"no\"\n  },\n  {\n    \"permiso\": \"Personal de Imágenes\",\n    \"activo\": \"no\"\n  },\n  {\n    \"permiso\": \"Personal de Laboratorio\",\n    \"activo\": \"no\"\n  },\n  {\n    \"permiso\": \"Personal de Farmacia\",\n    \"activo\": \"no\"\n  },\n  {\n    \"permiso\": \"Personal de Estadística\",\n    \"activo\": \"no\"\n  },\n{\n    \"permiso\": \"Administrador institucional\",\n    \"activo\": \"si\"\n  }\n]', '    [{             \"documento\": \"Copia de DNI\",             \"activo\": \"si\"         },         {             \"documento\": \"Copia de matrícula profesional\",             \"activo\": \"no\"         },         {             \"documento\": \"Solicitud de alta de usuario para HSI (ANEXO I)\",             \"activo\": \"no\"         },         {             \"documento\": \"Declaración Jurada - Convenio de confidecialidad usuarios HSI (ANEXO II)\",             \"activo\": \"no\"         }     ]', 'Usuario perfecto', 'habilitado', 'no', '2024-03-18'),
(13, '31.795.339', '2', 'drgphigasanmartin@gmail.com', '221', '[{\"permiso\":\"Especialista M\\u00e9dix\",\"activo\":\"si\"},{\"permiso\":\"Profesional de la Salud\",\"activo\":\"si\"},{\"permiso\":\"Enfermero\",\"activo\":\"si\"}]', '[{\"documento\":\"Copia de DNI\",\"activo\":\"no\"},{\"documento\":\"Copia de matr\\u00edcula profesional\",\"activo\":\"no\"},{\"documento\":\"Solicitud de alta de usuario para HSI (ANEXO I)\",\"activo\":\"no\"},{\"documento\":\"Declaraci\\u00f3n Jurada - Convenio de confidecialidad usuarios HSI (ANEXO II)\",\"activo\":\"no\"}]', 'Usuario pendiente de aprobación', 'working', 'no', '2024-03-19');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `hsi`
--
ALTER TABLE `hsi`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `hsi`
--
ALTER TABLE `hsi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
