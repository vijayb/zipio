CREATE TABLE IF NOT EXISTS `Comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `commenter_id` int(11) unsigned NOT NULL,
  `comment` text NOT NULL,
  `albumphoto_id` int(11) unsigned NOT NULL,
  `album_id` int(11) unsigned NOT NULL,
  `album_owner_id` int(11) unsigned NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY (`albumphoto_id`),
  KEY (`album_id`),
  KEY (`album_owner_id`),
  KEY (`commenter_id`),
  KEY (`created`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;