<?php
/**
 * 微新星版本公告信息
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

if($_W['isfounder']) {
	cache_load('upgrade');
	if (!empty($_W['cache']['upgrade'])) {
		$upgrade = iunserializer($_W['cache']['upgrade']);
	}
	if(empty($_W['cache']['upgrade']) ||  TIMESTAMP - $upgrade['lastupdate'] >= 3600 * 24) {
		require model('setting');
		$upgrade = setting_upgrade();
	}
	if(empty($upgrade['message'])) {
		exit();
	}
	print('<div id="we7_tips"><div class="we7_tips"><div class="we7_tips_main">');
	print('<div class="we7_tips_header" style="padding-left:5px;"><a title="关闭" href="javascript:;" onclick="closetips()" class="we7_tips_close"></a><div class="we7_tips_title">系统提醒您</div></div>');
	print('<div class="we7_tips_content">');
	print($upgrade['message']);
	print('</div></div></div></div>');
}
