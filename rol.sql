CREATE TABLE
    `roles` (
        `id` INT PRIMARY KEY AUTO_INCREMENT,
        `role` VARCHAR(255) NOT NULL,
        `modulo` VARCHAR(255) NOT NULL,
        `desc` VARCHAR(255),
        `activo` TINYINT (1) DEFAULT 1 -- Para indicar si el rol está activo o no
    );

CREATE TABLE
    `subroles` (
        `id` INT PRIMARY KEY AUTO_INCREMENT,
        `rol_id` INT, -- Referencia a roles
        `subrol` VARCHAR(255) NOT NULL,
        `modulo` VARCHAR(255) NOT NULL,
        `desc` VARCHAR(255),
        `activo` TINYINT (1) DEFAULT 1, -- Para indicar si el subrol está activo
        FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`)
    );

CREATE TABLE
    `personal` (
        `id` INT PRIMARY KEY AUTO_INCREMENT,
        `dni` INT NOT NULL UNIQUE -- Considerando que el DNI es único para cada persona
    );

CREATE TABLE
    `usuarios_roles` (
        `id` INT PRIMARY KEY AUTO_INCREMENT,
        `dni` INT NOT NULL,
        `rol_id` INT NOT NULL, -- Relación con roles
        FOREIGN KEY (`dni`) REFERENCES `personal` (`dni`),
        FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`)
    );

CREATE TABLE
    `usuarios_subroles` (
        `id` INT PRIMARY KEY AUTO_INCREMENT,
        `dni` INT NOT NULL,
        `rol_id` INT NOT NULL, -- Relación con roles
        `subrol_id` INT NOT NULL, -- Relación con subroles
        FOREIGN KEY (`dni`) REFERENCES `personal` (`dni`),
        FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`),
        FOREIGN KEY (`subrol_id`) REFERENCES `subroles` (`id`)
    );