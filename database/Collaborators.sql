CREATE TABLE IF NOT EXISTS `Collaborators` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `album_id` int(11) unsigned NOT NULL,
  `collaborator_id` int(11) unsigned NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY (`album_id`),
  KEY (`collaborator_id`),
  UNIQUE KEY `collaborator_key` (`collaborator_id`, `album_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;