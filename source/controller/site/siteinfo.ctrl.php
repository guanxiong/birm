<?php 
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * $sn$
 */
defined('IN_IA') or exit('Access Denied');
if (checksubmit('submit')) {
	$siteinfo = array(
		'sitename' => $_GPC['sitename'],
		'keywords' => $_GPC['keywords'],
		'description' => $_GPC['description'],
		'footer' => $_GPC['footer'],
	);
	
	pdo_update('wechats', array('siteinfo' => iserializer($siteinfo)), array('weid' => $_W['weid']));
	message('站点信息更新成功！', create_url('site/siteinfo'), 'success');
}
$item = iunserializer($_W['account']['siteinfo']);
template('site/siteinfo');