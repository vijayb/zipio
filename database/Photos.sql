CREATE TABLE IF NOT EXISTS `Photos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `latitude` float DEFAULT NULL,
  `longitude` float DEFAULT NULL,
  `s3_url` varchar(1000) DEFAULT NULL,
  `s3_url_hash` binary(20) NOT NULL,
  `num_views` int(11) NOT NULL,
  `caption` varchar(1000) DEFAULT NULL,
  `width` int(11) NOT NULL,
  `height` int(11) NOT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `s3_url_hash` (`s3_url_hash`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

DROP TRIGGER IF EXISTS `photostrigger`;

DELIMITER //
CREATE TRIGGER `photostrigger` BEFORE INSERT ON `Photos`
 FOR EACH ROW SET
    NEW.s3_url_hash = UNHEX(SHA1(NEW.s3_url))
//
DELIMITER ;
