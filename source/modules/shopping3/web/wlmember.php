<?php
/**
 * 2014-11-22
 * 会员 会员管理 
 * @author strai
 * @url
 */
$op = $_GPC['op']?$_GPC['op']:"display";
//
if($op=='display'){
	$pindex = intval($_GPC['page'])?intval($_GPC['page']):1;
	$psize = 10;
	$params[':weid'] = $_W['weid'];
	$limit = " limit ".($pindex-1)*$psize.",".$pindex*$psize;
	if (isset($_GPC['keywords'])) {
		$sql = ' AND `username` LIKE :keywords';
		$params[':keywords'] = "%{$_GPC['keywords']}%";
	}
	if (isset($_GPC['keywords1'])) {
		$sql .= ' AND `phone` LIKE :keywords1';
		$params[':keywords1'] = "%{$_GPC['keywords1']}%";
	}
	$total = pdo_fetchcolumn("SELECT count(*) FROM ".tablename('shopping3_fans')."where weid = :weid ".$sql,$params);
	$list = pdo_fetchall("SELECT * FROM ".tablename('shopping3_fans')." where weid = :weid ".$sql.$limit,$params);
	$pager = pagination($total, $pindex, $psize);
}elseif($op=='change'){
	$id = intval($_GPC['id']);
	$status = intval($_GPC['status'])==1?0:1;
	if($id){
		$res = pdo_update('shopping3_fans',array('status'=>$status),array('id'=>$id));
	}
	die;
}
include $this->template('web/member');