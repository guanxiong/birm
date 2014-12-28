<?php
if (!pdo_indexexists('scratchcard_reply', 'idx_rid')) {
	$sql = "ALTER TABLE ".tablename('scratchcard_reply')." ADD INDEX `idx_rid` ( `rid` );";
	pdo_query($sql);
}
if (!pdo_indexexists('scratchcard_winner', 'idx_createtime_fromuser')) {
	$sql = "ALTER TABLE ".tablename('scratchcard_winner')." ADD INDEX `idx_createtime_fromuser` ( `createtime` , `from_user` ), ADD INDEX `idx_fromuser_rid` (`from_user`,`rid`) ";
	pdo_query($sql);
}

if (!pdo_fieldexists('scratchcard_reply', 'background')) {
	pdo_query("ALTER TABLE ".tablename('scratchcard_reply')." ADD `background` VARCHAR(255) NOT NULL DEFAULT '' ;");
}
if(pdo_fieldexists('scratchcard_award', 'activation_code')) {
	pdo_query("ALTER TABLE ".tablename('scratchcard_award')." CHANGE `activation_code` `activation_code` text;");
}