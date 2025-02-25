INSERT INTO `subroles` (`rol_id`, `subrol`, `nombre`, `modulo`, `descripcion`, `estado`)
VALUES
    (12, 'administrador_roles', 'Administrador', 9, 'Otorga acceso a dar rol y subroles de administrador e inferiores', 'Activo'),
    (12, 'direccion_roles', 'Dirección', 9, '', 'Activo'),
    (12, 'jefe_servicio_roles', 'Jefe de Servicio', 9, '', 'Activo'),
    (12, 'arquitectura_roles', 'Arquitecto', 9, '', 'Activo'),
    (12, 'mantenimiento_base_roles', 'Mantenimiento Tickets', 9, '', 'Activo'),
    (12, 'mantenimiento_tickets_roles', 'Mantenimiento Tickets', 9, '', 'Activo'),
    (12, 'mantenimiento_arquitectura_roles', 'Arquitectura Tickets', 9, '', 'Activo'),
    (12, 'mantenimiento_informatica_roles', 'Informática Tickets', 9, '', 'Activo'),
    (12, 'mantenimiento_ingenieria_clinica_roles', 'Ingeniería Clínica Tickets', 9, '', 'Activo'),
    (12, 'mantenimiento_auditoria_roles', 'Auditoría Tickets', 9, 'Auditoría Tickets', 'Activo');

CREATE TABLE grupos_permisos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subrol_id INT NOT NULL,
    enabled_rol_id INT NOT NULL,
    enabled_subrol_id INT DEFAULT NULL,
    FOREIGN KEY (subrol_id) REFERENCES subroles(id) ON DELETE CASCADE,
    FOREIGN KEY (enabled_rol_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (enabled_subrol_id) REFERENCES subroles(id) ON DELETE CASCADE
);