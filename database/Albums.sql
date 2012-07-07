SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

CREATE TABLE IF NOT EXISTS `Albums` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL,
  `cover_albumphoto_id` int(11) unsigned NOT NULL,
  `handle` varchar(100) DEFAULT NULL,
  `handle_hash` binary(20) NOT NULL,
  `title` varchar(500) DEFAULT NULL,
  `caption` varchar(2000) DEFAULT NULL,
  `read_permissions` int(11) unsigned NOT NULL DEFAULT 3,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `num_views` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY (`user_id`),
  KEY (`handle_hash`),
  UNIQUE KEY `handle_user_key` (`handle_hash`, `user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

DROP TRIGGER IF EXISTS `albumstrigger`;

DELIMITER //
CREATE TRIGGER `albumstrigger` BEFORE INSERT ON `Albums`
 FOR EACH ROW SET
    NEW.handle_hash = UNHEX(SHA1(NEW.handle))
//
DELIMITER ;