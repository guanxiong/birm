<?php
/**
 * 微团购模块微站定义
 *
 * @author 
 * @url 
 */
 
 if($_GPC['op']=='detail'){
	if(empty($_GPC['oid'])){
		message('参数错误，无法继续操作!');
	}
	$row = pdo_fetch("SELECT a.*,b.mobile FROM ".tablename('groupon_order')." as a left join  ".tablename('groupon_fans')." as b on a.from_user=b.from_user WHERE   a.weid=".$weid." AND  a.id=".$_GPC['oid']." " );		
	include $this->template('detail');			
}else{
	$where='';
 
	if(!empty($_GPC['buy_user'])){
		$where.=" and a.from_user='".$_GPC['buy_user']."'";
	}
	if(!empty($_GPC['sn'])){
		$where.=" and a.ordersn like '%".$_GPC['sn']."%'";
	}
	if(!empty($_GPC['mobile'])){
		$where.=" and b.mobile like '%".$_GPC['mobile']."%'";
	}	
	if(!empty($_GPC['deal_time'])){
		$where.=" and a.paytime > ".strtotime('-'.$_GPC['deal_time'].' day')."";
	}		
	$where.=" AND	ispay=1";
	$where.=' and a.status=-1';

	$total=pdo_fetchcolumn ("SELECT count(a.id) FROM ".tablename('groupon_order')."  as a left join ".tablename('groupon_fans')." as b on a.from_user=b.from_user WHERE   a.weid=:weid ".$where."", array(':weid'=>$_W['weid']));		
	$pindex = max(1, intval($_GPC['page']));
	$psize = 12;		
	$pager = pagination($total, $pindex, $psize);		
	$start = ($pindex - 1) * $psize;
	$limit .= " LIMIT {$start},{$psize}";
	$list = pdo_fetchall("SELECT a.*,b.mobile FROM ".tablename('groupon_order')."  as a left join  ".tablename('groupon_fans')." as b on a.from_user=b.from_user WHERE  a.weid=:weid  ".$where." ORDER BY a.id DESC ".$limit , array(':weid'=>$_W['weid']));				
	include $this->template('refund');			

}
