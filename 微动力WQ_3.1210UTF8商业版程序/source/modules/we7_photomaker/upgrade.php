<?php
if(!pdo_fieldexists('we7_photomaker', 'adtype')) {
	pdo_query("ALTER TABLE ".tablename('we7_photomaker')." ADD `adtype` tinyint(1) NOT NULL AFTER `maxtotal`;");
}

if(!pdo_fieldexists('we7_photomaker', 'adurlh')) {
	pdo_query("ALTER TABLE ".tablename('we7_photomaker')." ADD `adurlh` varchar(255) NOT NULL AFTER `admsg`;");
	pdo_query("ALTER TABLE ".tablename('we7_photomaker')." ADD `adurlv` varchar(255) NOT NULL AFTER `adurlh`;");
}

