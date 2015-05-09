<?php 
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');
	$uid= intval($_GPC['uid']);
	if ($uid){
		$where="where uid=".$uid;
		}
	
$sql = "SELECT * FROM " . tablename('members_paylog')."   ".$where." order by id desc";
$paylog = pdo_fetchall($sql);
foreach($paylog   as  $key=>$vo){
$paylog[$key]['username']=pdo_fetchcolumn("SELECT username FROM ".tablename('members')." WHERE uid = '{$vo['uid']}' LIMIT 1");

}

template('finance/paylog');
