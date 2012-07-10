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
