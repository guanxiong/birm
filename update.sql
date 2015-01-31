alter table `ims_wechats` add `EncodingAESKey` varchar(43) NOT NULL;
--
-- 下面两条：安装1230完整包的可以不用导入，否则会提示重复！
--
alter table `ims_wechats` add  KEY `idx_parentid` (`parentid`);
alter table `ims_wechats` add  KEY `idx_key` (`key`);

-- 0126
 alter table `ims_wechats`  add  `jsapi_ticket` varchar(1000) NOT NULL;
 alter table `ims_card_members` change  `credit1` `credit1` varchar(15) NOT NULL DEFAULT '0';
 alter table `ims_card_members` change  `credit2` `credit2` varchar(15) NOT NULL DEFAULT '0';
 alter table `ims_paylog` change `plid`  `plid` bigint(20) unsigned NOT NULL AUTO_INCREMENT;
 
 CREATE TABLE IF NOT EXISTS `ims_menu_event` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `weid` int(10) unsigned NOT NULL,
  `keyword` varchar(30) NOT NULL,
  `type` varchar(30) NOT NULL COMMENT '事件类型',
  `picmd5` varchar(32) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `uniacid` (`weid`),
  KEY `picmd5` (`picmd5`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

