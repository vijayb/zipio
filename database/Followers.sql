SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

CREATE TABLE IF NOT EXISTS `Followers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `follower_id` int(11) unsigned NOT NULL,
  `album_owner_id` int(11) unsigned NOT NULL,
  `album_id` int(11) unsigned NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY (`follower_id`),
  KEY (`album_id`),
  KEY (`album_owner_id`),
  UNIQUE KEY `follower_key` (`follower_id`, `album_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
