<?php
if(!pdo_fieldexists('multisearch_research', 'remark')) {
	pdo_query("ALTER TABLE ".tablename('multisearch_research')." ADD `remark` VARCHAR( 1000 ) NOT NULL DEFAULT '' AFTER `data` ;");
}
if(!pdo_fieldexists('multisearch', 'noticeemail')) {
	pdo_query("ALTER TABLE ".tablename('multisearch')." ADD `noticeemail` VARCHAR( 255 ) NOT NULL DEFAULT  '';");
}
if(!pdo_fieldexists('multisearch', 'mobile')) {
	pdo_query("ALTER TABLE ".tablename('multisearch')." ADD `mobile` VARCHAR( 20 ) NOT NULL DEFAULT  '';");
}

if(!pdo_fieldexists('multisearch_fields', 'likesearch')) {
	pdo_query("ALTER TABLE ".tablename('multisearch_fields')." ADD `likesearch` TINYINT(1) NOT NULL DEFAULT '0' AFTER `search`;");
}
