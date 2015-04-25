<?php
$sql = "
CREATE TABLE IF NOT EXISTS `ims_idish_area` (
        `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `weid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属帐号',
        `name` varchar(50) NOT NULL COMMENT '区域名称',
        `parentid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上级分类ID,0为第一级',
        `displayorder` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
        `dateline` int(10) unsigned NOT NULL DEFAULT '0',
        `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态',
        PRIMARY KEY (`id`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `ims_idish_store_setting` (
        `id` int(10) NOT NULL AUTO_INCREMENT,
        `weid` int(10) unsigned NOT NULL,
        `storeid` int(10) unsigned NOT NULL,
        `order_enable` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '订餐开启',
        `dateline` int(10) DEFAULT '0',
        PRIMARY KEY (`id`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
";
pdo_run($sql);

//字段长度
if(pdo_fieldexists('idish_setting', `storeid`)) {
     pdo_query("ALTER TABLE  ".tablename('idish_setting')." ADD  `storeid` int(10) unsigned NOT NULL DEFAULT '0',;");
}
//2015/04/07新增
if(!pdo_fieldexists(`idish_order`, `reply`)) {
	pdo_query("ALTER TABLE ".tablename(`ims_idish_order`)." ADD `reply` varchar(1000) NOT NULL DEFAULT '' COMMENT '回复',;");
}
if(!pdo_fieldexists(`idish_order`, `sign`)) {
	pdo_query("ALTER TABLE ".tablename(`idish_order`)." ADD `sign` tinyint(1) NOT NULL DEFAULT '0' COMMENT '-1拒绝，0未处理，1已处理',;");
}
if(!pdo_fieldexists(`idish_setting`,  `storeid`)) {
	pdo_query("ALTER TABLE ".tablename(`idish_setting`)." ADD `storeid` int(10) unsigned NOT NULL DEFAULT '0',;");
}