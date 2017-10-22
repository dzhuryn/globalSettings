DROP TABLE IF EXISTS `{PREFIX}module_settings_category`;
CREATE TABLE `mjsk_evoshop_orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `short_txt` text NOT NULL,
  `content` text,
  `price` varchar(255) NOT NULL,
  `currency` varchar(255) NOT NULL,
  `date` datetime DEFAULT NULL,
  `sentdate` datetime DEFAULT NULL,
  `note` text NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(255) NOT NULL,
  `payment` varchar(128) NOT NULL,
  `delivery` text,
  `discount` text,
  `tracking_num` varchar(32) DEFAULT NULL,
  `status` int(11) NOT NULL,
  `userid` int(11) NOT NULL,
  `managerid` int(11) NOT NULL,
  `1c_exchange` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;