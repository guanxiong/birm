<?php

if(!pdo_fieldexists('xcommunity_report', 'print_sta')) {
	pdo_query("ALTER TABLE ".tablename('xcommunity_report')." ADD `print_sta` int( 3 ) NOT NULL ;");
}
$sql = "
	CREATE TABLE IF NOT EXISTS `ims_xcommunity_carpool` (
		  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
		  `weid` int(10) unsigned NOT NULL,
		  `openid` varchar(50) NOT NULL,
		  `start_position` varchar(100) NOT NULL,
		  `end_position` varchar(100) NOT NULL,
		  `startMinute` int(10) unsigned NOT NULL,
  		  `startSeconds` int(10) unsigned NOT NULL,
		  `license_number` varchar(100) NOT NULL,
		  `car_model` varchar(100) NOT NULL,
		  `car_brand` varchar(100) NOT NULL,
		  `content` varchar(300) NOT NULL,
		  `status` int(1) NOT NULL COMMENT '1是找乘客,2是找车主',
		  `enable` int(1) NOT NULL COMMENT '1开启,0关闭',
		  `createtime` int(10) unsigned NOT NULL,
		  PRIMARY KEY (`id`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
";
pdo_run($sql);