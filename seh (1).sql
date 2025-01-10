-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 10-01-2025 a las 17:22:06
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
-- Estructura de tabla para la tabla `mantenimiento`
--

CREATE TABLE `mantenimiento` (
  `id` int(11) NOT NULL,
  `reclamante` varchar(255) NOT NULL,
  `servicio` int(11) NOT NULL,
  `destino` varchar(255) NOT NULL,
  `short_description` varchar(255) NOT NULL,
  `prioridad` varchar(255) NOT NULL,
  `interno` varchar(255) NOT NULL,
  `mail` varchar(255) NOT NULL,
  `ubicacion` longtext NOT NULL,
  `long_description` longtext NOT NULL,
  `observaciones_reclamante` longtext NOT NULL,
  `observaciones_destino` longtext NOT NULL,
  `estado_reclamante` varchar(255) NOT NULL,
  `estado_destino` varchar(255) NOT NULL,
  `new_reclamante` varchar(255) NOT NULL,
  `new_reclamante_data` varchar(255) NOT NULL,
  `new_destino` varchar(255) NOT NULL,
  `fecha_registro` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_reclamante` datetime DEFAULT NULL,
  `fecha_destino` datetime DEFAULT NULL,
  `fecha_apertura_first` varchar(255) DEFAULT NULL,
  `usuario_apertura_first` varchar(255) DEFAULT NULL,
  `fecha_apertura_latest` varchar(255) DEFAULT NULL,
  `usuario_apertura_latest` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `mantenimiento`
--

INSERT INTO `mantenimiento` (`id`, `reclamante`, `servicio`, `destino`, `short_description`, `prioridad`, `interno`, `mail`, `ubicacion`, `long_description`, `observaciones_reclamante`, `observaciones_destino`, `estado_reclamante`, `estado_destino`, `new_reclamante`, `new_reclamante_data`, `new_destino`, `fecha_registro`, `fecha_reclamante`, `fecha_destino`, `fecha_apertura_first`, `usuario_apertura_first`, `fecha_apertura_latest`, `usuario_apertura_latest`) VALUES
(5, '43.255.000', 6, 'mantenimiento', 'Ventana rota', 'Baja', '0', 'clamas@ms.gba.gov.ar', 'Pabellón Cieza Rodriguez, 1° piso, Oficina de Gestión de Camas', 'Contamos con un vidrio roto en una de las ventanas, por el cual, los días de lluvia ingresa agua y corren riesgo las computadoras de mojarse.', '', '', 'Programado', 'Programado', 'no', 'El servicio marcó este caso como resuelto, para finalizar la solicitud marque el estado como \'Completado\'.', '', '2024-12-18 00:00:00', '2025-01-03 09:39:41', '2025-01-02 11:52:19', '03/01/2025 14:37:51', '43.255.000', NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `modulos`
--

CREATE TABLE `modulos` (
  `id` int(11) NOT NULL,
  `modulo` varchar(255) NOT NULL,
  `descripcion` varchar(255) NOT NULL,
  `estado` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `modulos`
--

INSERT INTO `modulos` (`id`, `modulo`, `descripcion`, `estado`) VALUES
(1, 'Tablero de mando', 'Tablero con información variada del sistema y estadísticas.', 'Activo'),
(2, 'Gestión de personal', 'Sistema de registro del personal hospitalario y base de datos de personal para la gestión dentro de todo el sistema.', 'Activo'),
(3, 'Solicitudes de HSI', 'Sistema que permite solicitar usuarios para la HSI, asi como gestionar la creación de estos.', 'Activo'),
(4, 'Esquema de guardia', 'Sistema que permite el registro del personal de guardia', 'Activo'),
(5, 'Informes de equipos', 'Sistema que permite el registro de los equipos por servicio, con el fin de informar el estado de estos, dando advertencias por el sistema.', 'Activo'),
(6, 'Mantenimiento', 'Sistema que permite crear solicitudes a los distintos servicios de mantenimiento y reparación.', 'Activo'),
(7, 'Administración', 'Sistema que permite el acceso (Según el nivel) de administrar distintos aspectos del sistema.', 'Activo'),
(8, 'Camillero', 'Sistema para solicitud y gestión de traslados intrahospitalarios', 'Activo'),
(9, 'Gestión de roles', 'Sistema que permite gestionar los roles (o grupos de roles) pertenecientes a los usuarios autorizados.', 'Activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `role` varchar(255) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `modulo` int(11) DEFAULT NULL,
  `descripcion` varchar(255) NOT NULL,
  `estado` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id`, `role`, `nombre`, `modulo`, `descripcion`, `estado`) VALUES
(1, 'administrador', 'Administrador', NULL, 'Acceso completo al sistema', 'Activo'),
(2, 'direccion', 'Dirección', NULL, 'Otorga acceso completo al sistema en contexto de Director', 'Activo'),
(3, 'tab_mando', 'Tablero de mando', 1, 'Acceso al tablero de mando', 'Activo'),
(4, 'gest_personal', 'Gestión de personal', 2, 'Acceso a Gestión de personal', 'Activo'),
(5, 'hsi', 'Solicitudes de HSI', 3, 'Acceso a Solicitudes de HSI', 'Activo'),
(6, 'guardias', 'Esquema de guardia', 4, 'Acceso a Esquemas de Guardia', 'Activo'),
(7, 'inf_equipos', 'Informes de equipos', 5, 'Acceso a Informes de Equipos', 'Activo'),
(8, 'mantenimiento', 'Mantenimiento', 6, 'Acceso a Mantenimiento', 'Activo'),
(9, 'administracion', 'Administración', 7, 'Acceso a Administración', 'Activo'),
(10, 'camilleros', 'Camilleros', 8, 'Acceso a Camilleros', 'Activo'),
(11, 'developer', 'Desarrollador', NULL, 'Módulo que otorga acceso a menús de desarrollo.', 'Activo'),
(12, 'gestion_roles', 'Gestión de roles', 9, 'Acceso al tablero de gestión de roles.', 'Activo'),
(13, 'tab_mando', 'Tablero de mando', 1, 'Acceso al tablero de mandoa', 'Activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `subroles`
--

CREATE TABLE `subroles` (
  `id` int(11) NOT NULL,
  `rol_id` int(11) NOT NULL,
  `subrol` varchar(255) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `modulo` int(11) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `estado` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `subroles`
--

INSERT INTO `subroles` (`id`, `rol_id`, `subrol`, `nombre`, `modulo`, `descripcion`, `estado`) VALUES
(1, 8, 'personal_mantenimiento', 'Personal de mantenimiento', 6, 'Acceso al tablero de administración de mantenimiento', 'Activo'),
(2, 8, 'personal_arquitectura', 'Personal de arquitectura', 6, 'Acceso a administración como personal de arquitectura', 'Activo'),
(3, 8, 'personal_informatica', 'Personal de informática', 6, 'Acceso a administración como personal de informatica', 'Activo'),
(4, 8, 'personal_ingenieria_clinica', 'Personal de ingeniería clínica', 6, 'Acceso a administración como personal de informatica', 'Activo'),
(5, 8, 'auditoria', 'Auditoria', 6, 'Acceso al sistema de auditoría del módulo de mantenimiento.', 'Activo'),
(6, 8, 'mantenimiento_work', 'Solo trabajador', 6, 'Otorgando este subrol, concederá acceso solo al panel de administración del modulo mantenimiento.', 'Activo'),
(7, 9, 'jefe_servicioa', 'Jefe de Servicio', 7, 'Otorga acceso a agregar y modificar subespecialidades.', 'Activo'),
(8, 12, 'jefe_servicio_gestion_roles', 'Jefe de Servicio - Gestión de roles', 9, 'Acceso a otorgar roles como jefe de servicio.', 'Activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios_roles`
--

CREATE TABLE `usuarios_roles` (
  `id` int(11) NOT NULL,
  `dni` varchar(10) NOT NULL,
  `rol_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `usuarios_roles`
--

INSERT INTO `usuarios_roles` (`id`, `dni`, `rol_id`) VALUES
(2, '43.255.000', 1),
(4, '43.255.000', 11);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios_subroles`
--

CREATE TABLE `usuarios_subroles` (
  `id` int(11) NOT NULL,
  `dni` varchar(10) NOT NULL,
  `rol_id` int(11) NOT NULL,
  `subrol_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `usuarios_subroles`
--

INSERT INTO `usuarios_subroles` (`id`, `dni`, `rol_id`, `subrol_id`) VALUES
(5, '43.255.000', 8, 1);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `mantenimiento`
--
ALTER TABLE `mantenimiento`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `modulos`
--
ALTER TABLE `modulos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `modulo` (`modulo`);

--
-- Indices de la tabla `subroles`
--
ALTER TABLE `subroles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `rol_id` (`rol_id`),
  ADD KEY `modulo` (`modulo`);

--
-- Indices de la tabla `usuarios_roles`
--
ALTER TABLE `usuarios_roles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dni` (`dni`),
  ADD KEY `rol_id` (`rol_id`);

--
-- Indices de la tabla `usuarios_subroles`
--
ALTER TABLE `usuarios_subroles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dni` (`dni`),
  ADD KEY `rol_id` (`rol_id`),
  ADD KEY `subrol_id` (`subrol_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `mantenimiento`
--
ALTER TABLE `mantenimiento`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `modulos`
--
ALTER TABLE `modulos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `subroles`
--
ALTER TABLE `subroles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `usuarios_roles`
--
ALTER TABLE `usuarios_roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `usuarios_subroles`
--
ALTER TABLE `usuarios_subroles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `roles`
--
ALTER TABLE `roles`
  ADD CONSTRAINT `roles_ibfk_1` FOREIGN KEY (`modulo`) REFERENCES `modulos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `subroles`
--
ALTER TABLE `subroles`
  ADD CONSTRAINT `subroles_ibfk_1` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `subroles_ibfk_2` FOREIGN KEY (`modulo`) REFERENCES `modulos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `usuarios_roles`
--
ALTER TABLE `usuarios_roles`
  ADD CONSTRAINT `usuarios_roles_ibfk_1` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `usuarios_subroles`
--
ALTER TABLE `usuarios_subroles`
  ADD CONSTRAINT `usuarios_subroles_ibfk_2` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `usuarios_subroles_ibfk_3` FOREIGN KEY (`subrol_id`) REFERENCES `subroles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
