SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

CREATE TABLE IF NOT EXISTS `AlbumPhotos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `photo_id` int(11) unsigned NOT NULL,
  `photo_owner_id` int(11) unsigned NOT NULL,
  `album_id` int(11) unsigned NOT NULL,
  `album_owner_id` int(11) unsigned NOT NULL,
  `visible` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY (`photo_id`),
  KEY (`album_id`),
  UNIQUE KEY `photo_album_key` (`photo_id`, `album_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
