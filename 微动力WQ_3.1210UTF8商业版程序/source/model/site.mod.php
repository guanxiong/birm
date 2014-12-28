<?php
defined('IN_IA') or exit('Access Denied');

function site_system_menus() {
	global $_W;
	return array(
		array('title'=>'首页', 'url'=> 'mobile.php?act=channel&name=index&weid='.$_W['weid']),
		array('title'=>'个人中心', 'url'=> 'mobile.php?act=channel&name=home&weid='.$_W['weid']),
	);
}

function site_slide_search($params = array()) {
	global $_GPC, $_W;
	extract($params);
	$sql = "SELECT * FROM ".tablename('site_slide'). " WHERE weid = '{$_W['weid']}' ORDER BY displayorder DESC, id DESC LIMIT $limit";
	$list = pdo_fetchall($sql);
	if (!empty($list)) {
		foreach ($list as &$row) {
			$row['url'] = strexists($row['url'], 'http') ? $row['url'] : $_W['siteroot'] . $row['url'];
			$row['thumb'] = toimage($row['thumb']);
		}
	}
	return $list;
}
