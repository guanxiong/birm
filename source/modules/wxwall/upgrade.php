<?php
if(!pdo_fieldexists('wxwall_reply', 'logo')) {
	pdo_query("ALTER TABLE ".tablename('wxwall_reply')." ADD `logo` VARCHAR( 255 ) NOT NULL DEFAULT '' AFTER `isshow`;");
	pdo_query("ALTER TABLE ".tablename('wxwall_reply')." ADD `background` VARCHAR( 255 ) NOT NULL DEFAULT '' AFTER `logo`;");
}