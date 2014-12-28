<?php
if(!pdo_fieldexists('lxy_marry_list', 'sendtitle')) {
	pdo_query("ALTER TABLE ".tablename('lxy_marry_list')." ADD `sendtitle` VARCHAR(255) NOT NULL DEFAULT '' , ADD `senddescription` VARCHAR(500) NOT NULL DEFAULT '' ;");
}