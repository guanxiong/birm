<?php




if(!pdo_fieldexists('brand_reply', 'new_pic')) {
	pdo_query("ALTER TABLE ".tablename('brand_reply')." ADD `new_pic` VARCHAR(200) NOT NULL;");
}
if(!pdo_fieldexists('brand_reply', 'news_content')) {
	pdo_query("ALTER TABLE ".tablename('brand_reply')." ADD `news_content` VARCHAR(500) NOT NULL;");
}




if(!pdo_fieldexists('brand', 'btnName')) {
    pdo_query("ALTER TABLE ".tablename('brand')." ADD btnName VARCHAR(20) DEFAULT NUL ;");
}

if(!pdo_fieldexists('brand', 'btnUrl')) {
    pdo_query("ALTER TABLE ".tablename('brand')." ADD btnUrl VARCHAR(100) DEFAULT NULL ;");
}

if(!pdo_fieldexists('brand', 'showMsg')) {
    pdo_query("ALTER TABLE ".tablename('brand')." ADD showMsg INT(1) DEFAULT 0 ;");
}





