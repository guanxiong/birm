<?php
if(pdo_fieldexists('stonefish_grabgifts_gift', 'awardpass')) {
	pdo_query("ALTER TABLE ".tablename('stonefish_grabgifts_gift')." modify column `awardpass` varchar(500);");
}
if(pdo_fieldexists('stonefish_grabgifts_gift', 'activation_code')) {
	pdo_query("ALTER TABLE ".tablename('stonefish_grabgifts_gift')." modify column `activation_code` varchar(50);");
}
if(!pdo_fieldexists('stonefish_grabgifts_reply', 'textcolor')) {
	pdo_query("ALTER TABLE ".tablename('stonefish_grabgifts_reply')." ADD `textcolor` varchar(7) NOT NULL COMMENT '文字色' AFTER `bgcolor`;");
}
if(!pdo_fieldexists('stonefish_grabgifts_reply', 'textcolort')) {
	pdo_query("ALTER TABLE ".tablename('stonefish_grabgifts_reply')." ADD `textcolort` varchar(7) NOT NULL COMMENT '文字色' AFTER `textcolor`;");
}
if(!pdo_fieldexists('stonefish_grabgifts_reply', 'textcolorb')) {
	pdo_query("ALTER TABLE ".tablename('stonefish_grabgifts_reply')." ADD `textcolorb` varchar(7) NOT NULL COMMENT '文字色' AFTER `textcolort`;");
}
if(!pdo_fieldexists('stonefish_grabgifts_reply', 'bgcolorbottom')) {
	pdo_query("ALTER TABLE ".tablename('stonefish_grabgifts_reply')." ADD `bgcolorbottom` varchar(7) NOT NULL COMMENT '文字色' AFTER `textcolorb`;");
}
if(!pdo_fieldexists('stonefish_grabgifts_reply', 'bgcolorbottoman')) {
	pdo_query("ALTER TABLE ".tablename('stonefish_grabgifts_reply')." ADD `bgcolorbottoman` varchar(7) NOT NULL COMMENT '文字色' AFTER `bgcolorbottom`;");
}
if(!pdo_fieldexists('stonefish_grabgifts_reply', 'textcolorbottom')) {
	pdo_query("ALTER TABLE ".tablename('stonefish_grabgifts_reply')." ADD `textcolorbottom` varchar(7) NOT NULL COMMENT '文字色' AFTER `bgcolorbottoman`;");
}
if(!pdo_fieldexists('stonefish_grabgifts_reply', 'bgcolorjiang')) {
	pdo_query("ALTER TABLE ".tablename('stonefish_grabgifts_reply')." ADD `bgcolorjiang` varchar(7) NOT NULL COMMENT '文字色' AFTER `textcolorbottom`;");
}
if(!pdo_fieldexists('stonefish_grabgifts_reply', 'textcolorjiang')) {
	pdo_query("ALTER TABLE ".tablename('stonefish_grabgifts_reply')." ADD `textcolorjiang` varchar(7) NOT NULL COMMENT '文字色' AFTER `bgcolorjiang`;");
}
if(!pdo_fieldexists('stonefish_grabgifts_reply', 'xuninum')) {
	pdo_query("ALTER TABLE ".tablename('stonefish_grabgifts_reply')." ADD `xuninum` int(10) unsigned NOT NULL DEFAULT '50' COMMENT '虚拟人数' AFTER `status`;");
}
if(!pdo_fieldexists('stonefish_grabgifts_reply', 'xuninumtime')) {
	pdo_query("ALTER TABLE ".tablename('stonefish_grabgifts_reply')." ADD `xuninumtime` int(10) unsigned NOT NULL DEFAULT '86400' COMMENT '虚拟间隔时间' AFTER `xuninum`;");
}
if(!pdo_fieldexists('stonefish_grabgifts_reply', 'xuninuminitial')) {
	pdo_query("ALTER TABLE ".tablename('stonefish_grabgifts_reply')." ADD `xuninuminitial` int(10) unsigned NOT NULL DEFAULT '10' COMMENT '虚拟随机数值1' AFTER `xuninumtime`;");
}
if(!pdo_fieldexists('stonefish_grabgifts_reply', 'xuninumending')) {
	pdo_query("ALTER TABLE ".tablename('stonefish_grabgifts_reply')." ADD `xuninumending` int(10) unsigned NOT NULL DEFAULT '50' COMMENT '虚拟随机数值2' AFTER `xuninuminitial`;");
}
if(!pdo_fieldexists('stonefish_grabgifts_reply', 'xuninum_time')) {
	pdo_query("ALTER TABLE ".tablename('stonefish_grabgifts_reply')." ADD `xuninum_time` int(10) unsigned NOT NULL COMMENT '虚拟更新时间' AFTER `xuninumending`;");
}
if(!pdo_fieldexists('stonefish_grabgifts_reply', 'awarding')) {
	pdo_query("ALTER TABLE ".tablename('stonefish_grabgifts_reply')." ADD `awarding` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '兑奖地点选择' AFTER `ndrankstatusnum`;");
}
if(!pdo_fieldexists('stonefish_grabgifts_userlist', 'mikaid')) {
	pdo_query("ALTER TABLE ".tablename('stonefish_grabgifts_userlist')." ADD `mikaid` varchar(500) NOT NULL DEFAULT '' COMMENT '领取密卡列表' AFTER `grabgifts`;");
}
if(!pdo_fieldexists('stonefish_grabgifts_userlist', 'awardingid')) {
	pdo_query("ALTER TABLE ".tablename('stonefish_grabgifts_userlist')." ADD `awardingid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '兑奖地址ID' AFTER `ndranknums`;");
}
if(!pdo_fieldexists('stonefish_grabgifts_userlist', 'awardingtypeid')) {
	pdo_query("ALTER TABLE ".tablename('stonefish_grabgifts_userlist')." ADD `awardingtypeid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '兑奖地址区域ID' AFTER `awardingid`;");
}
pdo_query("CREATE TABLE IF NOT EXISTS `ims_stonefish_grabgifts_awarding` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `weid` int(10) unsigned NOT NULL COMMENT '公众号ID',
  `typeid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '区域ID',
  `shoptitle` varchar(50) NOT NULL DEFAULT '' COMMENT '兑奖店面名称',
  `address` varchar(512) NOT NULL DEFAULT '' COMMENT '兑奖地址',
  `tel` varchar(50) NOT NULL DEFAULT '' COMMENT '联系电话',
  `pass` varchar(20) NOT NULL DEFAULT '' COMMENT '兑奖密码',
  `images` varchar(512) NOT NULL DEFAULT '' COMMENT '广告或店面图',
  `carmap` varchar(50) NOT NULL COMMENT '地图导航',
  PRIMARY KEY (`id`)
) ENGINE = MYISAM DEFAULT CHARSET = utf8;");
pdo_query("CREATE TABLE IF NOT EXISTS `ims_stonefish_grabgifts_awardingtype` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `weid` int(10) unsigned NOT NULL COMMENT '公众号ID',
  `quyutitle` varchar(50) NOT NULL DEFAULT '' COMMENT '分类名称',
  `orderid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  PRIMARY KEY (`id`)
) ENGINE = MYISAM DEFAULT CHARSET = utf8;");
pdo_query("CREATE TABLE IF NOT EXISTS `ims_stonefish_grabgifts_giftmika` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '规则id',
  `giftid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '礼盒ID', 
  `from_user` varchar(50) NOT NULL DEFAULT '' COMMENT '用户openid',  
  `mika` varchar(50) NOT NULL COMMENT '密卡字符串',
  `activationurl` varchar(200) NOT NULL COMMENT '激活地址',
  `description` varchar(100) NOT NULL DEFAULT '' COMMENT '描述',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否领取1为领取过',
  PRIMARY KEY (`id`),
  KEY `indx_rid` (`rid`)
) ENGINE = MYISAM DEFAULT CHARSET = utf8;");