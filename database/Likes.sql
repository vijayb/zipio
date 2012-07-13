CREATE TABLE IF NOT EXISTS `Likes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `albumphoto_id` int(11) unsigned NOT NULL,
  `user_id` int(11) unsigned NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY (`albumphoto_id`),
  KEY (`user_id`),
  UNIQUE KEY `likes_key` (`albumphoto_id`, `user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;