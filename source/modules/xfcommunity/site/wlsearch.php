<?php
/*

常用查询
*/
//判断是否注册，只有注册后，才能进入
$from_user = $_W['fans']['from_user'];
$member = pdo_fetch("SELECT * FROM".tablename('xcommunity_member')."WHERE openid='{$from_user}'");
if(empty($member) || empty($member['status'])){
	$url1 = $this->createMobileUrl('home');
	$url  = $_W['siteroot'].$url1;
	header("Location:$url");exit;
}
$list = pdo_fetchAll("SELECT * FROM".tablename("xcommunity_search")."WHERE status='1' AND weid='{$_W['weid']}'");
include $this->template('search');