<?php


$sql = "
CREATE TABLE IF NOT EXISTS `ims_shopping_set` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `weid` int(11) DEFAULT NULL,
  `shop_name` varchar(50) DEFAULT NULL,
  `thumb` varchar(1000) DEFAULT NULL,
  `paytype1` tinyint(1) NOT NULL,
  `paytype2` tinyint(1) NOT NULL,
  `paytype3` tinyint(1) NOT NULL,
  `print_status` tinyint(1) NOT NULL,
  `print_type` tinyint(2) NOT NULL,
  `print_usr` varchar(50) NOT NULL,
  `print_nums` tinyint(3) NOT NULL,
  `print_bottom` varchar(30) NOT NULL,
  `sms_status` tinyint(1) NOT NULL,
  `sms_type` tinyint(2) NOT NULL COMMENT '0商家，1客户，2both',
  `sms_phone` varchar(20) NOT NULL,
  `sms_from` tinyint(2) NOT NULL DEFAULT '1' COMMENT '1是打印机自己发，2是短信平台',
  `sms_account` varchar(30) NOT NULL,
  `sms_secret` varchar(80) NOT NULL,
  `sms_text` varchar(200) NOT NULL,
  `sms_resgister` tinyint(1) NOT NULL DEFAULT '1',
  `sms_customer` tinyint(1) NOT NULL,
  `sms_verifytxt` varchar(70) NOT NULL,
  `sms_paytxt` varchar(70) NOT NULL,
  `sms_bosstxt` varchar(70) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
";
pdo_run($sql);

//字段长度
if(pdo_fieldexists('shopping_goods', 'thumb')) {
     pdo_query("ALTER TABLE  ".tablename('shopping_goods')." CHANGE `thumb` `thumb` varchar(255) DEFAULT '';");
}
//2014/11/07史中营新增
if(!pdo_fieldexists('shopping_cart', 'appointmenttime')) {
	pdo_query("ALTER TABLE ".tablename('shopping_cart')." ADD `appointmenttime` int(11) NOT NULL DEFAULT '0';");
}
if(!pdo_fieldexists('shopping_order', 'appointmenttime')) {
	pdo_query("ALTER TABLE ".tablename('shopping_order')." ADD `appointmenttime` int(11) NOT NULL DEFAULT '0';");
}

if(!pdo_fieldexists('shopping_address', 'province')) {
	pdo_query("ALTER TABLE ".tablename('shopping_address')." modify `province` varchar(30) DEFAULT NULL ;");
}
if(!pdo_fieldexists('shopping_address', 'city')) {
	pdo_query("ALTER TABLE ".tablename('shopping_address')." modify `city` varchar(30) DEFAULT NULL ;");
}
if(!pdo_fieldexists('shopping_address', 'area')) {
	pdo_query("ALTER TABLE ".tablename('shopping_address')." modify `area` varchar(30) DEFAULT NULL ;");
}
if(!pdo_fieldexists('shopping_address', 'address')) {
	pdo_query("ALTER TABLE ".tablename('shopping_address')." modify `address` varchar(300) DEFAULT NULL ;");
}