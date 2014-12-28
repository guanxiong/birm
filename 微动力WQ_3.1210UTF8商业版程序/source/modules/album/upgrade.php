<?php
if(!pdo_fieldexists('album', 'type')) {
	pdo_query("ALTER TABLE ".tablename('album')." ADD `type` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER  `isview`;");
}

if (!pdo_indexexists('album_photo', 'idx_albumid')) {
	pdo_query("ALTER TABLE ".tablename('album_photo')." ADD INDEX `idx_albumid` ( `albumid` );");
}
