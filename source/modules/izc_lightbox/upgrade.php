<?php

if(!pdo_fieldexists('izc_lightbox_list', 'hits')) {
	pdo_query("ALTER TABLE ".tablename('izc_lightbox_list')." ADD `hits` int(11) NOT NULL DEFAULT '0';");
}
if(!pdo_fieldexists('izc_lightbox_list', 'shares')) {
	pdo_query("ALTER TABLE ".tablename('izc_lightbox_list')." ADD `shares` int(11) NOT NULL DEFAULT '0';");
}
if(!pdo_fieldexists('izc_lightbox_list', 'tongji')) {
	pdo_query("ALTER TABLE ".tablename('izc_lightbox_list')." ADD `tongji` VARCHAR( 1000 ) NOT NULL;");
}
if(!pdo_fieldexists('izc_lightbox_list', 'isyuyue')) {
	pdo_query("ALTER TABLE ".tablename('izc_lightbox_list')." ADD `isyuyue` tinyint( 1 ) NOT NULL DEFAULT '0';");
}
if(!pdo_fieldexists('izc_lightbox_list', 'iscomment')) {
	pdo_query("ALTER TABLE ".tablename('izc_lightbox_list')." ADD `iscomment` tinyint( 1 ) NOT NULL DEFAULT '0';");
}
if(!pdo_fieldexists('izc_lightbox_list', 'isdemo')) {
	pdo_query("ALTER TABLE ".tablename('izc_lightbox_list')." ADD `isdemo` tinyint( 1 ) NOT NULL DEFAULT '0';");
}
pdo_query("CREATE TABLE IF NOT EXISTS  `ims_izc_lightbox_comment` (
 `id` INT( 11 ) NOT NULL AUTO_INCREMENT ,
 `weid` INT( 11 ) NOT NULL ,
 `list_id` INT( 11 ) NOT NULL ,
 `from` VARCHAR( 10 ) NOT NULL ,
 `content` VARCHAR( 255 ) NOT NULL ,
 `create_time` INT( 10 ) NOT NULL ,
 `status` TINYINT( 1 ) NOT NULL ,
 `from_user` VARCHAR( 50 ) NOT NULL ,
PRIMARY KEY (  `id` )
) ENGINE = MYISAM DEFAULT CHARSET = utf8;");

$app=pdo_fetch("select * from ".tablename('izc_lightbox_app')." where iden='custom'");
if($app==false){
	$insert=array(
		'iden'=>'custom',
		'title'=>'自定义场景',
		'author'=>'izhice.com',
		'series'=>'智策',
		'isshow'=>1,
		'create_time'=>time(),
	);
	pdo_insert('izc_lightbox_app',$insert);
}
