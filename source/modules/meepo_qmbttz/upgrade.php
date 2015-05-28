<?php
$sql = "
CREATE TABLE IF NOT EXISTS `ims_meepo_qmbttz_set` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `weid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属帐号',
  `title` varchar(50) DEFAULT NULL,
  `share_title` varchar(200) DEFAULT '',
  `share_desc` varchar(300) DEFAULT '',
  `share_url` varchar(100) DEFAULT '',
  `copyright` varchar(300) NOT NULL ,
  `share_txt` varchar(500) DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;
";
pdo_run($sql);
