<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

$id = intval($_GPC['id']);
$row = pdo_fetch("SELECT weid, name FROM ".tablename('wechats')." WHERE weid = '$id'");
if (empty($row)) {
	message('抱歉，该众从号不存在或是已经被删除！', create_url('account/display'));
}
pdo_update('wechats', array('lastupdate' => '0'), array('weid' => $id));
message('同步成功！', create_url('account/post', array('id' => $id)));