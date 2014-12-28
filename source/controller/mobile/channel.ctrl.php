<?php
/**
 * 微站频道
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */

defined('IN_IA') or exit('Access Denied');

if (!empty($_GPC['styleid'])) {
	$_W['account']['styleid'] = $_GPC['styleid'];
	$_W['account']['template'] = pdo_fetchcolumn("SELECT name FROM ".tablename('site_templates')." WHERE id = '{$_W['account']['styleid']}'");
}
$_W['styles'] = mobile_styles();
$_W['account']['quickmenu'] = iunserializer($_W['account']['quickmenu']);
$_W['quickmenu']['template'] = !empty($_W['account']['quickmenu']['template']) ? '../quick/' . $_W['account']['quickmenu']['template'] : '../quick/default';
$_W['quickmenu']['menus'] = mobile_nav(3);

$channel = array('index', 'home', 'list', 'detail', 'album', 'photo');
$name = $_GPC['name'] && in_array($_GPC['name'], $channel) ? $_GPC['name'] : 'index';

if ($name == 'index') {
	include model('site');
	$position = 1;
	$navs = mobile_nav($position);
	$cover = pdo_fetch("SELECT description, title, thumb FROM ".tablename('cover_reply')." WHERE weid = :weid AND module = 'wesite'", array(':weid' => $_W['weid']));
	$_share_content = $cover['description'];
	$title = $cover['title'];
	$_share_img = $cover['thumb'];
} elseif ($name == 'home') {
	$title = '个人中心';
	$position = 2;
	if (empty($_W['uid']) && empty($_W['fans']['from_user'])) {
		message('非法访问，请重新点击链接进入个人中心！');
	}
	$navs = mobile_nav($position);
	if (!empty($navs)) {
		foreach ($navs as $row) {
			$menus[$row['module']][] = $row;
		}
		foreach ($menus as $module => $row) {
			if (count($row) <= 2) {
				$menus['other'][$module] = $row;
				unset($menus[$module]);
			}
		}
	}
} elseif ($name == 'list') {
	header("Location: ".create_url('mobile/module/list', array('name' => 'site', 'weid' => $_W['weid'], 'cid' => $_GPC['cid'])));
	exit;
} elseif ($name == 'detail') {
	header("Location: ".create_url('mobile/module/detail', array('name' => 'site', 'weid' => $_W['weid'], 'id' => $_GPC['id'])));
	exit;
} elseif ($name == 'album') {
	header("Location: ".create_url('mobile/module/list', array('name' => 'album', 'weid' => $_W['weid'])));
	exit;
} elseif ($name == 'photo') {
	header("Location: ".create_url('mobile/module/detail', array('name' => 'album', 'weid' => $_W['weid'], 'id' => $_GPC['id'])));
	exit;
}

template($name);
