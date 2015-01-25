<?php
if(!pdo_fieldexists('shopping3_set', 'partnerId')) {
	pdo_query("ALTER TABLE ".tablename('shopping3_set')."  ADD  `partnerId` VARCHAR( 200 ) NOT NULL;");
}
if(!pdo_fieldexists('shopping3_set', 'apiKey')) {
	pdo_query("ALTER TABLE ".tablename('shopping3_set')." ADD `apiKey` VARCHAR( 200 ) NOT NULL;");
}
 if(!pdo_fieldexists('shopping3_set', 'machineCode')) {
	pdo_query("ALTER TABLE ".tablename('shopping3_set')." ADD `machineCode` VARCHAR( 200 ) NOT NULL;");
}
if(!pdo_fieldexists('shopping3_set', 'mKey')) {
	pdo_query("ALTER TABLE ".tablename('shopping3_set')." ADD  `mKey` VARCHAR( 200 ) NOT NULL;");
}
 