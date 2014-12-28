<?php
 if(!pdo_fieldexists('vote_reply', 'isshow')) {
	pdo_query("ALTER TABLE ".tablename('vote_reply')." ADD `isshow` int(11) NOT NULL DEFAULT '0';");
}