CREATE TABLE IF NOT EXISTS `AlbumPhotoLikes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `albumphoto_id` int(11) unsigned NOT NULL,
  `album_id` int(11) unsigned NOT NULL,
  `photo_owner_id` int(11) unsigned NOT NULL,
  `liker_id` int(11) unsigned NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY (`albumphoto_id`),
  KEY (`album_id`),
  KEY (`photo_owner_id`),
  KEY (`liker_id`),
  UNIQUE KEY `likes_key` (`albumphoto_id`, `liker_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;