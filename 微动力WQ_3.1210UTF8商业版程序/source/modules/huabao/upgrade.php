<?php
if(!pdo_fieldexists('huabao', 'icon')) {
	pdo_query("ALTER TABLE ".tablename('huabao')." ADD `icon` VARCHAR(100) NOT NULL DEFAULT '' AFTER  `title`;");
}
if(!pdo_fieldexists('huabao', 'loading')) {
	pdo_query("ALTER TABLE ".tablename('huabao')." ADD `loading` VARCHAR(100) NOT NULL DEFAULT '' AFTER  `icon`;");
}
if(!pdo_fieldexists('huabao', 'mauto')) {
	pdo_query("ALTER TABLE ".tablename('huabao')." ADD `mauto` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1' AFTER  `music`;");
}
if(!pdo_fieldexists('huabao', 'mloop')) {
	pdo_query("ALTER TABLE ".tablename('huabao')." ADD `mloop` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1' AFTER  `mauto`;");
}
if(!pdo_fieldexists('huabao', 'isloop')) {
	pdo_query("ALTER TABLE ".tablename('huabao')." ADD `isloop` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' AFTER  `displayorder`;");
}
$sql = "
CREATE TABLE IF NOT EXISTS `ims_huabao_item` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `weid` int(10) unsigned NOT NULL,
  `huabaoid` int(10) unsigned NOT NULL,
  `photoid` int(10) unsigned NOT NULL,
  `type` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `item` varchar(1000) NOT NULL DEFAULT '',
  `url` varchar(100) NOT NULL DEFAULT '',
  `animation` varchar(20) NOT NULL DEFAULT '',
  `createtime` int(10) unsigned NOT NULL,
  KEY `idx_photoid` (`photoid`),
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
";
pdo_run($sql);
if(!pdo_fieldexists('huabao_item', 'x')) {
	pdo_query("ALTER TABLE ".tablename('huabao_item')." ADD `x` INT(3) NOT NULL DEFAULT '0' AFTER  `url`;");
}
if(!pdo_fieldexists('huabao_item', 'y')) {
	pdo_query("ALTER TABLE ".tablename('huabao_item')." ADD `y` INT(3) NOT NULL DEFAULT '0' AFTER  `x`;");
}
if(pdo_fieldexists('huabao', 'loading')) {
	pdo_query("ALTER TABLE ".tablename('huabao')." DROP `loading`;");
}
if((pdo_fieldexists('huabao', 'thumb'))&(!pdo_fieldexists('huabao', 'open'))) {
	pdo_query("ALTER TABLE ".tablename('huabao')." CHANGE `thumb`  `open` VARCHAR(100) NOT NULL DEFAULT '';");
}
if(!pdo_fieldexists('huabao', 'ostyle')) {
	pdo_query("ALTER TABLE ".tablename('huabao')." ADD `ostyle` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' AFTER `open`;");
}
if(!pdo_fieldexists('huabao', 'share')) {
	pdo_query("ALTER TABLE ".tablename('huabao')." ADD `share` VARCHAR(250) NOT NULL DEFAULT '' AFTER `icon`;");
}
if(!pdo_fieldexists('huabao', 'thumb')) {
	pdo_query("ALTER TABLE ".tablename('huabao')." ADD `thumb` VARCHAR(100) NOT NULL DEFAULT '' AFTER  `mloop`;");
}
if(pdo_fieldexists('huabao', 'share')) {
	pdo_query("ALTER TABLE ".tablename('huabao')." CHANGE `share`  `share` VARCHAR(250) NOT NULL DEFAULT '';");
}
if(pdo_fieldexists('huabao_item', 'url')) {
	pdo_query("ALTER TABLE ".tablename('huabao_item')." CHANGE `url`  `url` VARCHAR(250) NOT NULL DEFAULT '';");
}
if(pdo_fieldexists('huabao_item', 'y')) {
	pdo_query("ALTER TABLE ".tablename('huabao_item')." CHANGE `y`  `y` INT(3) NOT NULL DEFAULT '0';");
}