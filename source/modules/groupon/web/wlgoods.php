<?php
/**
 * 微团购模块微站定义
 *
 * @author 
 * @url 
 */
		$where='';
		if(!empty($_GPC['status'])){
			$where.=' and status='.$_GPC['status'].'';
		}

		$total=pdo_fetchcolumn ("SELECT count(id) FROM ".tablename('groupon_list')." WHERE  weid=:weid ".$where."", array(':weid'=>$_W['weid']));		
		$pindex = max(1, intval($_GPC['page']));
		$psize = 12;		
		$pager = pagination($total, $pindex, $psize);		
		$start = ($pindex - 1) * $psize;
		$limit .= " LIMIT {$start},{$psize}";
		$list = pdo_fetchall("SELECT * FROM ".tablename('groupon_list')." WHERE  weid=:weid  ".$where." ORDER BY `id` DESC ".$limit , array(':weid'=>$_W['weid']));				
		
		include $this->template('goods');		
