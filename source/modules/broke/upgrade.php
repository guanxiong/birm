<?php
$sql="
CREATE TABLE IF NOT EXISTS `ims_broke_protect` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `cname` varchar(50) NOT NULL DEFAULT '',
  `mobile` varchar(50) NOT NULL,
  `createtime` int(10) NOT NULL DEFAULT '0',
  `weid` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;";
pdo_query($sql);

if(pdo_fieldexists('fans', 'avatar')) {
	pdo_query("ALTER TABLE  ".tablename('fans')." CHANGE `avatar` `avatar` varchar(255) NOT NULL DEFAULT '';");
	//pdo_query("update  ".tablename('fans')." set `avatar`='' where `avatar` LIKE 'http%';");
}

if(!pdo_fieldexists('broke_acmanager', 'loupanid')) {
	pdo_query("ALTER TABLE ".tablename('broke_acmanager')." ADD `loupanid` varchar(200) NOT NULL;");
}

if(!pdo_fieldexists('broke_customer', 'content')) {
	pdo_query("ALTER TABLE ".tablename('broke_customer')." ADD `content` text;");
}
if(!pdo_fieldexists('broke_loupan', 'jw_addr')) {
	pdo_query("ALTER TABLE ".tablename('broke_loupan')." ADD `jw_addr` varchar(255) NOT NULL DEFAULT '';");
}
if(!pdo_fieldexists('broke_member', 'commission')) {
	pdo_query("ALTER TABLE ".tablename('broke_member')." ADD `commission` int(10) unsigned NOT NULL DEFAULT '0';");
}

if(!pdo_fieldexists('broke_rule', 'gzurl')) {
	pdo_query("ALTER TABLE ".tablename('broke_rule')." ADD `gzurl` varchar(255) NOT NULL DEFAULT '';");
	
}
if(!pdo_fieldexists('broke_assistant', 'pwd')) {
	pdo_query("ALTER TABLE ".tablename('broke_assistant')." ADD `pwd` varchar(50) NOT NULL DEFAULT '';");
	
}
if(!pdo_fieldexists('broke_member', 'commission')) {
	pdo_query("ALTER TABLE ".tablename('broke_member')." ADD `commission` int(10) NOT NULL DEFAULT '0';");
	
}

if(!pdo_fieldexists('broke_loupan', 'status')) {
	pdo_query("ALTER TABLE ".tablename('broke_loupan')." ADD `status` tinyint(1) NOT NULL DEFAULT '1';");
	
}

if(pdo_fieldexists('broke_member', 'tjmid')) {
	pdo_query("ALTER TABLE ".tablename('broke_member')." CHANGE `tjmid` `tjmid` int(10) NOT NULL DEFAULT '0';");
}
if(!pdo_fieldexists('broke_member', 'tjmid')) {
	pdo_query("ALTER TABLE ".tablename('broke_member')." ADD `tjmid` int(10) NOT NULL DEFAULT '0';");
	
}
if(pdo_fieldexists('fans', 'avatar')) {
	pdo_query("ALTER TABLE ".tablename('fans')." CHANGE `avatar` `avatar` varchar(255) NOT NULL DEFAULT '';");
}

if(!pdo_fieldexists('broke_commission', 'opid')) {
	pdo_query("ALTER TABLE ".tablename('broke_commission')." ADD `opid` int(10) unsigned DEFAULT '0' COMMENT '操作员ID经理或销售或管理员';");

}
if(!pdo_fieldexists('broke_rule', 'teamfy')) {
	pdo_query("ALTER TABLE ".tablename('broke_rule')." ADD `teamfy` int(10) unsigned NOT NULL DEFAULT '0';");

}

if(!pdo_fieldexists('broke_commission', 'opname')) {
	pdo_query("ALTER TABLE ".tablename('broke_commission')." ADD `opname` varchar(50) NOT NULL DEFAULT '';");

}


?>