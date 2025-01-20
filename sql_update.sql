CREATE TABLE
    `updates` (
        `id` int (11) NOT NULL,
        `fecha` datetime NOT NULL `version` varchar(255) NOT NULL,
        `descripcion` varchar(255) NOT NULL,
    )
INSERT INTO
    `updates` (`id`, `fecha`, `version`, `descripcion`)
VALUES
    (
        1,
        '2025-01-13 08:00:00',
        '1.0.0',
        'Se realizaron múltiples implementaciones de diversos sistemas, se establece en el plan de desarrollo los módulos de: Depósito, Camilleros y Gestión de roles (Actualmente serán otorgados por el administrador) Estos, serán cruciales para un eficaz funcionamiento del hospital.'
    );

INSERT INTO
    `modulos` (`id`, `modulo`, `descripcion`, `estado`)
VALUES
    (
        10,
        "Arquitectura",
        "Este módulo es el encargado de gestionar la arquitectura del hospital, es decir, la estructura de los servicios que ofrece el hospital.",
        "Activo"
    );

INSERT INTO
    `roles` (
        `role`,
        `nombre`,
        `modulo`,
        `descripcion`,
        `estado`
    )
VALUES
    (
        "arquitectura",
        "Arquitectura",
        10,
        "Este rol es el encargado de gestionar la arquitectura del hospital, es decir, la estructura de los servicios que ofrece el hospital.",
        "Activo"
    );

CREATE TABLE
    `arquitectura` (
        `id` int (11) NOT NULL,
        `servicio` int (11) NOT NULL,
        `nombre` varchar(255) NOT NULL,
        `observaciones` longtext NOT NULL,
        `u_padre` int (11) DEFAULT NULL,
        `u_hijo` int (11) DEFAULT NULL,
        `estado` varchar(255) NOT NULL,
        PRIMARY KEY (`id`),
    )
ALTER TABLE `arquitectura` MODIFY `id` int (11) NOT NULL AUTO_INCREMENT;