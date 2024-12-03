CREATE TABLE `modulos` (
  `id` int(11) NOT NULL,
  `modulo` varchar(255) NOT NULL,
  `descripcion` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `role` varchar(255) NOT NULL,
  `modulo` int(11) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `activo` tinyint(4) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

CREATE TABLE `subroles` (
  `id` int(11) NOT NULL,
  `rol_id` int(11) DEFAULT NULL,
  `subrol` varchar(255) NOT NULL,
  `modulo` int(11) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `activo` tinyint(4) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

CREATE TABLE `usuarios_roles` (
  `id` int(11) NOT NULL,
  `dni` varchar(10) NOT NULL,
  `rol_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

CREATE TABLE `usuarios_subroles` (
  `id` int(11) NOT NULL,
  `dni` varchar(10) NOT NULL,
  `rol_id` int(11) NOT NULL,
  `subrol_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `module_id` (`modulo`);

--
ALTER TABLE `subroles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `rol_id` (`rol_id`),
  ADD KEY `module_id` (`modulo`);

ALTER TABLE `usuarios_roles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dni` (`dni`),
  ADD KEY `rol_id` (`rol_id`);

ALTER TABLE `usuarios_subroles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dni` (`dni`),
  ADD KEY `rol_id` (`rol_id`),
  ADD KEY `subrol_id` (`subrol_id`);

ALTER TABLE `modulos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;


ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `subroles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `usuarios_roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `usuarios_subroles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `roles`
  ADD CONSTRAINT `roles_ibfk_1` FOREIGN KEY (`modulo`) REFERENCES `modulos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `subroles`
  ADD CONSTRAINT `subroles_ibfk_1` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `subroles_ibfk_2` FOREIGN KEY (`modulo`) REFERENCES `modulos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `usuarios_roles`
  ADD CONSTRAINT `usuarios_roles_ibfk_1` FOREIGN KEY (`dni`) REFERENCES `personal` (`dni`),
  ADD CONSTRAINT `usuarios_roles_ibfk_2` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`);

ALTER TABLE `usuarios_subroles`
  ADD CONSTRAINT `usuarios_subroles_ibfk_1` FOREIGN KEY (`dni`) REFERENCES `personal` (`dni`),
  ADD CONSTRAINT `usuarios_subroles_ibfk_2` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `usuarios_subroles_ibfk_3` FOREIGN KEY (`subrol_id`) REFERENCES `subroles` (`id`);