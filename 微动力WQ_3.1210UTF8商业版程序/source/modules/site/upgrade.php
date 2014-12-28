<?php
if(!pdo_fieldexists('article', 'iscommend')) {
	pdo_query("ALTER TABLE ".tablename('article')." ADD `iscommend` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `title` ;");
	pdo_query("ALTER TABLE ".tablename('article')." ADD `ishot` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `iscommend` ;");
	pdo_query("ALTER TABLE ".tablename('article')." ADD `description` VARCHAR( 1000 ) NOT NULL DEFAULT '' AFTER `title`;");
}

if(!pdo_fieldexists('article', 'type')) {
	pdo_query("ALTER TABLE ".tablename('article')." ADD `type` VARCHAR( 10 ) NOT NULL DEFAULT '' ;");
}

if(!pdo_fieldexists('article_category', 'template')) {
	pdo_query("ALTER TABLE ".tablename('article_category')." ADD `template` VARCHAR(300) NOT NULL DEFAULT '' COMMENT '分类模板' AFTER `description`;");
}

if(!pdo_fieldexists('article', 'template')) {
	pdo_query("ALTER TABLE ".tablename('article')." ADD `template` VARCHAR(300) NOT NULL DEFAULT '' COMMENT '内容模板' AFTER `ccate`;");
}

if(!pdo_fieldexists('article', 'displayorder')) {
	pdo_query("ALTER TABLE ".tablename('article')." ADD `displayorder` int(10) unsigned NOT NULL DEFAULT '0' AFTER `author`;");
}

if(!pdo_fieldexists('article', 'linkurl')) {
	pdo_query("ALTER TABLE ".tablename('article')." ADD `linkurl` varchar(500) NOT NULL DEFAULT '' AFTER `displayorder`;");
}

if(!pdo_fieldexists('article_category', 'linkurl')) {
	pdo_query("ALTER TABLE ".tablename('article_category')." ADD `linkurl` VARCHAR( 500 ) NOT NULL DEFAULT '';");
}

if(!pdo_fieldexists('article_category', 'templatefile')) {
	pdo_query("ALTER TABLE ".tablename('article_category')." ADD `templatefile` VARCHAR( 100 ) NOT NULL DEFAULT '' AFTER `template`");
}

if(!pdo_fieldexists('article_category', 'ishomepage')) {
	pdo_query("ALTER TABLE ".tablename('article_category')." ADD `ishomepage` TINYINT( 1 ) NOT NULL DEFAULT '0'");
}

if(!pdo_fieldexists('article_category', 'icontype')) {
	pdo_query("ALTER TABLE ".tablename('article_category')." ADD `icontype` tinyint(1) unsigned NOT NULL");
}

if(!pdo_fieldexists('article_category', 'css')) {
	pdo_query("ALTER TABLE ".tablename('article_category')." ADD `css` varchar(500) NOT NULL");
}
