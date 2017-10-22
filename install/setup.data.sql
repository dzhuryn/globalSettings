DROP TABLE IF EXISTS `{PREFIX}module_settings_category`;
CREATE TABLE `{PREFIX}module_settings_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `caption` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{PREFIX}module_settings`;
CREATE TABLE `{PREFIX}module_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `index` int(11) DEFAULT '4',
  `description` varchar(255) DEFAULT NULL,
  `elements` tinytext,
  `category` int(11) DEFAULT NULL,
  `type` varchar(15) DEFAULT NULL,
  `value` text,
  `name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

INSERT INTO `{PREFIX}module_settings_category` (`caption`) VALUES ('Без категории');