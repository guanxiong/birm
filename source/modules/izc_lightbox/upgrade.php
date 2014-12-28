<?php
	
if(!pdo_fieldexists('izc_lightbox_items', 'video')) {
	pdo_query("ALTER TABLE ".tablename('izc_lightbox_items')." ADD `video` varchar(300) NOT NULL DEFAULT ''");
}
if(!pdo_fieldexists('izc_lightbox_items', 'video_thumb')) {
	pdo_query("ALTER TABLE ".tablename('izc_lightbox_items')." ADD `video_thumb` varchar(300) NOT NULL DEFAULT ''");
}
if(!pdo_fieldexists('izc_lightbox_items', 'lng')) {
	pdo_query("ALTER TABLE ".tablename('izc_lightbox_items')." ADD `lng` double unsigned NOT NULL DEFAULT '0'");
}
if(!pdo_fieldexists('izc_lightbox_items', 'lat')) {
	pdo_query("ALTER TABLE ".tablename('izc_lightbox_items')." ADD `lat` double unsigned NOT NULL DEFAULT '0'");
}
if(!pdo_fieldexists('izc_lightbox_items', 'address')) {
	pdo_query("ALTER TABLE ".tablename('izc_lightbox_items')." ADD `address` varchar(300) NOT NULL DEFAULT ''");
}
if(!pdo_fieldexists('izc_lightbox_items', 'tel')) {
	pdo_query("ALTER TABLE ".tablename('izc_lightbox_items')." ADD `tel` varchar(50) NOT NULL DEFAULT ''");
}
if(!pdo_fieldexists('izc_lightbox_items', 'wechat')) {
	pdo_query("ALTER TABLE ".tablename('izc_lightbox_items')." ADD `wechat` varchar(300) NOT NULL DEFAULT ''");
}
if(!pdo_fieldexists('izc_lightbox_items', 'map_thumb')) {
	pdo_query("ALTER TABLE ".tablename('izc_lightbox_items')." ADD `map_thumb` varchar(300) NOT NULL DEFAULT ''");
}