<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

$id = intval($_GPC['id']);
$row = pdo_fetch("SELECT weid, name, uid FROM ".tablename('wechats')." WHERE weid = '$id'");
if (!checkpermission('wechats', $row)) {
	message('抱歉，您没有权限操作该公众号！');
}
if (empty($row)) {
	message('抱歉，该公众号不存在或是已经被删除！', create_url('account/display'));
}
cache_write('weid:' . $_W['uid'], $row['weid']);
isetcookie('__weid', $row['weid'], 7 * 46800);
message($row['name'], '', 'success');
