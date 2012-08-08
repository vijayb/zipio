CREATE TABLE IF NOT EXISTS `AlbumPhotos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `photo_id` int(11) unsigned NOT NULL,
  `photo_owner_id` int(11) unsigned NOT NULL,
  `album_id` int(11) unsigned NOT NULL,
  `album_owner_id` int(11) unsigned NOT NULL,
  `visible` int(11) NOT NULL DEFAULT '1',
  `filtered` int(11) NOT NULL DEFAULT '0',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `caption` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY (`photo_id`),
  KEY (`album_id`),
  UNIQUE KEY `photo_album_key` (`photo_id`, `album_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;