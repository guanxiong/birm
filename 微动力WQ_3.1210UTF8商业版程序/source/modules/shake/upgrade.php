<?php
if(!pdo_fieldexists('shake_reply', 'logo')) {
	pdo_query("ALTER TABLE ".tablename('shake_reply')." ADD `logo` VARCHAR( 255 ) NOT NULL DEFAULT '' AFTER `background`; ");
}
