<?php

if(!pdo_fieldexists('shopping3_set', 'sms_user')) {
	pdo_query("ALTER TABLE ".tablename('shopping3_set')." ADD `sms_user` varchar(50) NOT NULL DEFAULT ''");
}

if(!pdo_fieldexists('shopping3_fans', 'sex')) {
	pdo_query("ALTER TABLE ".tablename('shopping3_fans')." ADD  `sex` TINYINT( 1 ) NOT NULL ;");
}
if(!pdo_fieldexists('shopping3_goods', 'label')) {
	pdo_query("ALTER TABLE ".tablename('shopping3_goods')." ADD `label` varchar(2) NOT NULL DEFAULT ''");
}

if(!pdo_fieldexists('shopping3_set', 'address_list')) {
	pdo_query("ALTER TABLE ".tablename('shopping3_set')." ADD `address_list` varchar(500) NOT NULL DEFAULT ''");
}
if(!pdo_fieldexists('shopping3_set', 'desk_list')) {
	pdo_query("ALTER TABLE ".tablename('shopping3_set')." ADD `desk_list` varchar(1000) NOT NULL DEFAULT ''");
}
if(!pdo_fieldexists('shopping3_set', 'room_list')) {
	pdo_query("ALTER TABLE ".tablename('shopping3_set')." ADD `room_list` varchar(1000) NOT NULL DEFAULT ''");
}
if(!pdo_fieldexists('shopping3_order', 'nums')) {
	pdo_query("ALTER TABLE ".tablename('shopping3_order')." ADD `nums` tinyint(4) NOT NULL");
}

if(!pdo_fieldexists('shopping3_set', 'ordretype1')) {
	pdo_query("ALTER TABLE ".tablename('shopping3_set')." ADD `ordretype1` tinyint(2) NOT NULL");
	pdo_query("UPDATE  ".tablename('shopping3_set')." SET  `ordretype1` = 1");
}
if(!pdo_fieldexists('shopping3_set', 'ordretype2')) {
	pdo_query("ALTER TABLE ".tablename('shopping3_set')." ADD `ordretype2` tinyint(2) NOT NULL");
	pdo_query("UPDATE  ".tablename('shopping3_set')." SET  `ordretype2` = 1");
}
if(!pdo_fieldexists('shopping3_set', 'ordretype3')) {
	pdo_query("ALTER TABLE ".tablename('shopping3_set')." ADD `ordretype3` tinyint(2) NOT NULL");
	pdo_query("UPDATE  ".tablename('shopping3_set')." SET  `ordretype3` = 0");
}
if(!pdo_fieldexists('shopping3_fans', 'status')) {
	pdo_query("ALTER TABLE ".tablename('shopping3_fans')." ADD `status` tinyint(1) NOT NULL default 1");
}
if(!pdo_fieldexists('shopping3_set', 'yy_start_time')) {
	pdo_query("ALTER TABLE ".tablename('shopping3_set')." ADD `yy_start_time` varchar(5) NOT NULL DEFAULT '00:00'");
}
if(!pdo_fieldexists('shopping3_set', 'yy_end_time')) {
	pdo_query("ALTER TABLE ".tablename('shopping3_set')." ADD `yy_end_time` varchar(5) NOT NULL DEFAULT '23:59'");
}
