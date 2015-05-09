<?php
if(!pdo_fieldexists('xfmarket_goods', 'thumb1_cover')) {
	pdo_query("ALTER TABLE ".tablename('xfmarket_goods')." ADD `thumb1_cover` varchar(200)  DEFAULT ''; ");
}
if(!pdo_fieldexists('xfmarket_goods', 'thumb2_cover')) {
	pdo_query("ALTER TABLE  ".tablename('xfmarket_goods')." ADD `thumb2_cover` varchar(200)   DEFAULT '';");
}
if(!pdo_fieldexists('xfmarket_goods', 'thumb3_cover')) {
	pdo_query("ALTER TABLE ".tablename('xfmarket_goods')." ADD `thumb3_cover` varchar(200)  DEFAULT ''; ");
}
if(!pdo_fieldexists('xfmarket_goods', 'thumb4_cover')) {
	pdo_query("ALTER TABLE ".tablename('xfmarket_goods')." ADD `thumb4_cover` varchar(200)  DEFAULT ''; ");
}