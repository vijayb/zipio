CREATE TABLE IF NOT EXISTS `CommentLikes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `comment_id` int(11) unsigned NOT NULL,
  `albumphoto_id` int(11) unsigned NOT NULL,
  `album_id` int(11) unsigned NOT NULL,
  `commenter_id` int(11) unsigned NOT NULL,
  `liker_id` int(11) unsigned NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY (`albumphoto_id`),
  KEY (`album_id`),
  KEY (`commenter_id`),
  KEY (`liker_id`),
  UNIQUE KEY `likes_key` (`comment_id`, `liker_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;