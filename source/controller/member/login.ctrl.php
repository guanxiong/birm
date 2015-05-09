<?php
/**
 * [WDL] Copyright (c) 2013 B2CTUI.COM
 */
defined('IN_IA') or exit('Access Denied');
if(checksubmit()) {
	_login($_GPC['referer']);
}
cache_load('setting');
template('member/login');
