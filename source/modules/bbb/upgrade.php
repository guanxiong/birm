<?php

if(!pdo_fieldexists('bbb_reply', 'start_time')) {
	pdo_query("ALTER TABLE ".tablename('bbb_reply')." ADD `start_time` int(10) NOT NULL DEFAULT '0';");
}
if(!pdo_fieldexists('bbb_reply', 'end_time')) {
	pdo_query("ALTER TABLE ".tablename('bbb_reply')." ADD `end_time` int(10) NOT NULL DEFAULT '1600000000';");
}
pdo_query("update  ".tablename('modules')." set `title`='摇骰子' where `name`='bbb' ;");
