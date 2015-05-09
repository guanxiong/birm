<?php
if(pdo_fieldexists('egg_reply', 'periodlottery')) {
	pdo_query("ALTER TABLE `ims_lxy_bulletin_board_card` ADD `title` varchar(255) DEFAULT NULL;");
}

