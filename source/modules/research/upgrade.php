<?php

if(!pdo_fieldexists('research', 'pretotal')) {
	pdo_query("ALTER TABLE ".tablename('research')." ADD `pretotal` INT( 10 ) UNSIGNED NOT NULL DEFAULT '1';");
}
//pdo_query("ALTER TABLE `ims_research` CHANGE `description` `description` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '';");

if(!pdo_fieldexists('research', 'noticeemail')) {
	pdo_query("ALTER TABLE ".tablename('research')." ADD `noticeemail` VARCHAR( 50 ) NOT NULL DEFAULT '';");
}

if(!pdo_fieldexists('research', 'endtime')) {
	pdo_query("ALTER TABLE ".tablename('research')." ADD `endtime` INT( 10 ) UNSIGNED NOT NULL ;");
}

if (!pdo_fieldexists('research', 'content')) {
	pdo_query("ALTER TABLE ".tablename('research')." CHANGE `description` `content` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;");
	pdo_query("ALTER TABLE ".tablename('research')." ADD `description` VARCHAR( 1000 ) NOT NULL DEFAULT '' AFTER `title` ;");
}
