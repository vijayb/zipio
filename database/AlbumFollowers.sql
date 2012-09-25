CREATE TABLE IF NOT EXISTS `AlbumFollowers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL,
  `album_id` int(11) unsigned NOT NULL,
  `album_owner_id` int(11) unsigned NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY (`user_id`),
  KEY (`album_id`),
  KEY (`album_owner_id`),
  UNIQUE KEY `album_key` (`user_id`, `album_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;