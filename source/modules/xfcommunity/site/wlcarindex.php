<?php
/*

拼车

*/
if (empty($_W['fans']['from_user'])) {
	message('非法访问，请重新发送消息进入拼车页面！');
}
$fromuser = $_W['fans']['from_user'];
$profile = fans_require($fromuser, array('nickname','avatar','mobile', 'qq'), '需要完善资料后才能进入拼车页面.');
$fans = pdo_fetch("SELECT * FROM".tablename('fans')."WHERE from_user='{$fromuser}'");
$pindex = max(1, intval($_GPC['page']));
$psize = 15;
$status = $_GPC['status'];
$condition1 = '';
if ($status) {
	$condition1 = "AND status = '{$status}'";
}
$condition2 = '';
$keyword = $_GPC['keyword'];
if ($keyword) {
	$keyword = "%{$_GPC['keyword']}%";
	$condition2 = " AND start_position LIKE '{$keyword}' OR end_position LIKE '{$keyword}'";
}
$list = pdo_fetchAll("SELECT * FROM".tablename('xcommunity_carpool')."WHERE weid='{$_W['weid']}' $condition1 $condition2 LIMIT ".($pindex - 1) * $psize.','.$psize);
$total = pdo_fetchcolumn("SELECT COUNT(*) FROM".tablename('xcommunity_carpool')."WHERE weid='{$_W['weid']}' $condition1 $condition2 ");
$pager = pagination($total, $pindex, $psize);
include $this->template('carindex');