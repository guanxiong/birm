<?php
if(!pdo_fieldexists('lxy_buildpro_full_view', 'quanjinglink')) {
	pdo_query("ALTER TABLE ".tablename('lxy_buildpro_full_view')."  ADD  `quanjinglink` varchar(500) DEFAULT NULL COMMENT '全景外链';");
}
if(!pdo_fieldexists('lxy_buildpro_full_view', 'pic_xia')) {
	pdo_query("ALTER TABLE ".tablename('lxy_buildpro_full_view')." ADD `pic_xia` varchar(1023) DEFAULT NULL;");
}
 if(!pdo_fieldexists('lxy_buildpro_full_view', 'sort')) {
	pdo_query("ALTER TABLE ".tablename('lxy_buildpro_full_view')." ADD `sort` int(11) DEFAULT NULL;");
}
if(!pdo_fieldexists('lxy_buildpro_full_view', 'status')) {
	pdo_query("ALTER TABLE ".tablename('lxy_buildpro_full_view')." ADD  `status` tinyint(4) DEFAULT '1';");
}
 