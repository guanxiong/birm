<?php

if(!pdo_fieldexists('signup', 'pretotal')) {
	pdo_query("ALTER TABLE ".tablename('signup')." ADD `pretotal` INT( 10 ) UNSIGNED NOT NULL DEFAULT '1';");
}
//pdo_query("ALTER TABLE `ims_signup` CHANGE `description` `description` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '';");

if(!pdo_fieldexists('signup', 'noticeemail')) {
	pdo_query("ALTER TABLE ".tablename('signup')." ADD `noticeemail` VARCHAR( 50 ) NOT NULL DEFAULT '';");
}

if(!pdo_fieldexists('signup', 'endtime')) {
	pdo_query("ALTER TABLE ".tablename('signup')." ADD `endtime` INT( 10 ) UNSIGNED NOT NULL ;");
}

if (!pdo_fieldexists('signup', 'content')) {
	pdo_query("ALTER TABLE ".tablename('signup')." CHANGE `description` `content` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;");
	pdo_query("ALTER TABLE ".tablename('signup')." ADD `description` VARCHAR( 1000 ) NOT NULL DEFAULT '' AFTER `title` ;");
}
