
DROP DATABASE IF EXISTS families;
CREATE DATABASE families DEFAULT CHARACTER SET utf8;

USE families;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE TABLE IF NOT EXISTS `family_pairs` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `man_id` int(11) NOT NULL,
    `woman_id` int(11) NOT NULL,
    `lft` int(11) NOT NULL,
    `rgt` int(11) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `man_id` (`man_id`),
    UNIQUE KEY `woman_id` (`woman_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `persons` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(50) NOT NULL,
    `sex` enum('man','woman') NOT NULL,
    `parents_pair_id` int(11) DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE VIEW `mans` AS
    SELECT `id`, `name`, `parents_pair_id`
        FROM `persons` WHERE (`sex` = 'man');

CREATE VIEW `womans` AS
    SELECT `id`, `name`, `parents_pair_id`
        FROM `persons` WHERE (`sex` = 'woman');
