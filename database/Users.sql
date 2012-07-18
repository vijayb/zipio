SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

CREATE TABLE IF NOT EXISTS `Users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `latitude` float DEFAULT NULL,
  `longitude` float DEFAULT NULL,
  `zipcode` varchar(20) DEFAULT NULL,
  `last_seen` datetime NOT NULL,
  `visits` int(11) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `utm` varchar(255) NOT NULL,
  `session_id` varchar(255) NOT NULL,
  `session_created` datetime NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `subscribed` tinyint(1) NOT NULL DEFAULT '0',
  `last_emailed` datetime DEFAULT NULL,
  `email_frequency` int(11) NOT NULL DEFAULT '86400',
  `referrer` int(11) DEFAULT NULL,
  `username_hash` binary(20) NOT NULL,
  `email_hash` binary(20) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email_hash` (`email_hash`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

DROP TRIGGER IF EXISTS `userstrigger`;

DELIMITER //
CREATE TRIGGER `userstrigger` BEFORE INSERT ON `Users`
    FOR EACH ROW BEGIN
        SET NEW.email_hash = UNHEX(SHA1(NEW.email));
        SET NEW.username_hash = UNHEX(SHA1(NEW.username));
    END;
//
DELIMITER ;

DELIMITER //
CREATE TRIGGER `userstrigger2` BEFORE UPDATE ON `Users`
    FOR EACH ROW BEGIN
        SET NEW.email_hash = UNHEX(SHA1(NEW.email));
        SET NEW.username_hash = UNHEX(SHA1(NEW.username));
    END;
//
DELIMITER ;