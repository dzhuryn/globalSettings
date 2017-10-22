DROP TABLE IF EXISTS `{PREFIX}module_settings_category`;
CREATE TABLE `{PREFIX}module_settings_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `caption` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;