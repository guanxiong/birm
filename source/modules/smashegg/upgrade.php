<?php

if(!pdo_fieldexists('smashegg_reply', `c_pic_three`)) {
	pdo_query("ALTER TABLE ".tablename('smashegg_reply')." CHANGE `c_pic_three` `c_pic_three` varchar(200) DEFAULT NULL");
}
