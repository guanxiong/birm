<?php
/**
 * 微团购模块微站定义
 *
 * @author 
 * @url 
 */
 //floatval
 //sprintf
	$where='';
	if(!empty($_GPC['mobile'])){
		$where.=" and mobile like '%".$_GPC['mobile']."%'";
	}	
	
	$total=pdo_fetchcolumn ("SELECT count(id) FROM ".tablename('groupon_fans')."  Where weid=".$weid." ".$where);		
	$pindex = max(1, intval($_GPC['page']));
	$psize = 20;		
	$pager = pagination($total, $pindex, $psize);		
	$start = ($pindex - 1) * $psize;
	$limit .= " LIMIT {$start},{$psize}";
	$list = pdo_fetchall("SELECT * FROM ".tablename('groupon_fans')." Where weid=".$weid." ".$where." order by id desc ".$limit );			
	
	include $this->template('user');			