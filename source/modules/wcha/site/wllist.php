<?php
/**
 * 我的订单
 *
 * @author 超级无聊
 * @url
 */
//$this->checklogin($from);
if(empty($_GPC['status']) && $_GPC['status']!=0){
	$_GPC['status']=1;
}$status=intval($_GPC['status']);


//搜索订单
 
 
	$pindex = max(1, intval($_GPC['page']));
	$psize = 10;
 	$condition="AND status ={$status}  AND  createtime>".mktime(0,0,0)." ";
	
	/* if($_GPC['ispay']==1){
		$condition.=" AND ispay=1 ";
	}elseif($_GPC['ispay']==0){
		$condition.=" AND ispay=0 ";
	} */
	
	$list = pdo_fetchall("SELECT * FROM ".tablename('shopping3_order')." WHERE weid = '{$weid}'  $condition ORDER BY createtime DESC, id DESC LIMIT ".($pindex - 1) * $psize.','.$psize);
	$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('shopping3_order') . " WHERE weid = '{$weid}' $condition");
	$pager='';
	if($pindex==1){
		$pager.="<font class='page_noclick'><<上一页</font>&nbsp;&nbsp;";
	}else{
		$_GET['page'] = $pindex-1;
		$pager.="<a href='" . $_W['script_name'] . "?" . http_build_query($_GET) . "' class='page_button'><<上一页</a>&nbsp;&nbsp;";
	}
	$totalpage= ceil($total / $psize);
	
	$pager.="<span class='fc_red'>".$pindex."</span> / ".$totalpage."&nbsp;&nbsp;";
	if($pindex==$totalpage){
		$pager.="<font class='page_noclick'>下一页>></font>&nbsp;&nbsp;";
	}else{
		$_GET['page'] = $pindex+1;
		$pager.="<a href='" . $_W['script_name'] . "?" . http_build_query($_GET) . "' class='page_button'>下一页>></a>&nbsp;&nbsp;";
	}
 
 
	include $this->template('wl_list');
