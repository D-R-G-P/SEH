CREATE TABLE `error_report` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` varchar(10) NOT NULL,
  `description` longtext NOT NULL,
  `date` datetime NOT NULL CURRENT_TIMESTAMP,
  `estado` VARCHAR(255) NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;