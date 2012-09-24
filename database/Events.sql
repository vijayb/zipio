CREATE TABLE IF NOT EXISTS `Events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `actor_id` int(11) unsigned NOT NULL,
  `action_type` int(11) unsigned NOT NULL,
  `object_id` int(11) unsigned NOT NULL,
  `album_id` int(11) unsigned DEFAULT NULL,
  `albumphoto_id` int(11) unsigned DEFAULT NULL,
  `comment_id` int(11) unsigned DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY (`actor_id`),
  KEY (`action_type`),
  KEY (`object_id`),
  KEY (`album_id`),
  KEY (`albumphoto_id`),
  KEY (`comment_id`),
  UNIQUE KEY `event_key` (`actor_id`, `action_type`, `object_id`)
  ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;