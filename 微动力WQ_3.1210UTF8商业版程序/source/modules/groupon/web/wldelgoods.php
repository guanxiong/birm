<?php
/**
 * 微团购模块微站定义
 *
 * @author 
 * @url 
 */
	
			$id= intval($_GPC['id']);

  $url = create_url('site/module', array('do' =>'Goods', 'name' =>'groupon'));
		if($_GPC['id']>0){

	    //$where=" AND a.tid='{$_GPC['id']}'  ";
		//$row = pdo_fetch("SELECT a.*  FROM ".tablename('groupon_order')."  as a WHERE  a.weid='".$_W['weid']."'".$where);
		//if ($row!==false){
		//message('已经有消费订单了，无法删除!',$url,'error');
		//} 
	    $groupon = pdo_fetch("delete  FROM ".tablename('groupon_list')." WHERE  weid=:weid  AND id={$_GPC['id']}" , array(':weid'=>$_W['weid'])); 
			  
			
			  
			message('删除成功!',$url);
 				
		}
			