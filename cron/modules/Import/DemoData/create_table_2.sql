CREATE TABLE `table_name` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` int(11) DEFAULT '0',
  `recordid` int(11) DEFAULT NULL,
  `potentialname` varchar(120) DEFAULT NULL,
  `related_to` varchar(250) DEFAULT NULL,
  `amount` decimal(25,8) DEFAULT NULL,
  `closingdate` varchar(250) DEFAULT NULL,
  `leadsource` varchar(200) DEFAULT NULL,
  `sales_stage` varchar(200) DEFAULT NULL,
  `assigned_user_id` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=200 DEFAULT CHARSET=utf8;