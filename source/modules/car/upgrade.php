<?php
pdo_run($manifest['install']);

if(pdo_fieldexists('we7car_care', 'from_user')) {
	pdo_query("ALTER TABLE ".tablename('we7car_care')." CHANGE `from_user` `from_user` INT(10) UNSIGNED NOT NULL;");
}
if(pdo_fieldexists('we7car_order_list', 'from_user')) {
	pdo_query("ALTER TABLE ".tablename('we7car_order_list')." CHANGE `from_user` `from_user` INT(10) UNSIGNED NOT NULL;");
}
if(!pdo_fieldexists('we7car_care', 'car_mobile')) {
	pdo_query("ALTER TABLE ".tablename('we7car_order_list')." ADD `car_mobile` varchar(15) NOT NULL;");
}
if(!pdo_fieldexists('we7car_set', 'shop_logo')) {
	pdo_query("ALTER TABLE ".tablename('we7car_set')." ADD `shop_logo` varchar(200) NOT NULL;");
}
if(!pdo_fieldexists('we7car_set', 'typethumb')) {
	pdo_query("ALTER TABLE ".tablename('we7car_set')." ADD `typethumb` varchar(100) NOT NULL;");
}
if(!pdo_fieldexists('we7car_set', 'yuyue1thumb')) {
	pdo_query("ALTER TABLE ".tablename('we7car_set')." ADD `yuyue1thumb` varchar(100) NOT NULL;");
}
if(!pdo_fieldexists('we7car_set', 'yuyue2thumb')) {
	pdo_query("ALTER TABLE ".tablename('we7car_set')." ADD `yuyue2thumb` varchar(100) NOT NULL;");
}
if(!pdo_fieldexists('we7car_set', 'kefuthumb')) {
	pdo_query("ALTER TABLE ".tablename('we7car_set')." ADD `kefuthumb` varchar(100) NOT NULL;");
}
if(!pdo_fieldexists('we7car_set', 'messagethumb')) {
	pdo_query("ALTER TABLE ".tablename('we7car_set')." ADD `messagethumb` varchar(100) NOT NULL;");
}
if(!pdo_fieldexists('we7car_set', 'carethumb')) {
	pdo_query("ALTER TABLE ".tablename('we7car_set')." ADD `carethumb` varchar(100) NOT NULL;");
}
