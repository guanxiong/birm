<?php
/*
微小区-二手交易首页
晓锋
*/
$status = !empty($_GPC['status'])?$_GPC['status']:'1';
//判断是否注册，只有注册后，才能进入
$from_user = $_W['fans']['from_user'];
$member = pdo_fetch("SELECT * FROM".tablename('xcommunity_member')."WHERE openid='{$from_user}'");
if(empty($member) || empty($member['status'])){
	$url1 = $this->createMobileUrl('home');
	$url  = $_W['siteroot'].$url1;
	header("Location:$url");exit;
}
$pindex = max(1, intval($_GPC['page']));
$psize = 8;
$condition = '';
if (!empty($_GPC['keyword'])) {
	$keyword = "%{$_GPC['keyword']}%";
	$condition = " AND title LIKE '{$keyword}'";
}
if ($status == '1') {

	$list = pdo_fetchAll("SELECT * FROM ".tablename('xcommunity_fled')."WHERE  status='1' AND weid='{$_W['weid']}' $condition LIMIT ".($pindex - 1) * $psize.','.$psize);
	$total = pdo_fetchcolumn("SELECT COUNT(*) FROM".tablename('xcommunity_fled')."WHERE  status='1' AND weid='{$_W['weid']}' $condition");
	$pager = pagination($total, $pindex, $psize);
}elseif($status == '2'){

	$list = pdo_fetchAll("SELECT * FROM".tablename('xcommunity_fled')."WHERE status='2' AND weid='{$_W['weid']}' $condition  LIMIT ".($pindex - 1) * $psize.','.$psize);
	$total = pdo_fetchcolumn("SELECT COUNT(*) FROM".tablename('xcommunity_fled')."WHERE status='2' AND weid='{$_W['weid']}' $condition ");
	$pager = pagination($total, $pindex, $psize);
}




include $this->template('index');