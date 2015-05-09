<?php
/*
微小区-小区活动-首页
*/
//判断是否注册，只有注册后，才能进入
$from_user = $_W['fans']['from_user'];
$member = pdo_fetch("SELECT * FROM".tablename('xcommunity_member')."WHERE openid='{$from_user}'");
if(empty($member) || empty($member['status'])){
	$url1 = $this->createMobileUrl('home');
	$url  = $_W['siteroot'].$url1;
	header("Location:$url");exit;
}
$pindex = max(1, intval($_GPC['page']));
$psize  = 8;
$condition = '';
$list = pdo_fetchAll("SELECT * FROM".tablename('xcommunity_activity')."WHERE weid='{$_W['weid']}' order by status desc LIMIT ".($pindex - 1) * $psize.','.$psize);
$total = pdo_fetchcolumn("SELECT COUNT(*) FROM".tablename('xcommunity_activity')."WHERE weid='{$_W['weid']}'");
$pager  = pagination($total, $pindex, $psize);

include $this->template('activity');

