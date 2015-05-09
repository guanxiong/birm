<?php
/*

*/
$fromuser = $_W['fans']['from_user'];
$fans = pdo_fetch("SELECT * FROM".tablename('fans')."WHERE from_user='{$fromuser}'");
$id = $_GPC['id'];
$row = pdo_fetch("SELECT * FROM".tablename('xcommunity_carpool')."WHERE id='{$id}'");
$op = $_GPC['op'];
$enable = $_GPC['enable'];
if ($op == 'update') {
	//echo $enable;exit;
	pdo_query("update ".tablename('xcommunity_carpool')." set enable='{$enable}' where id='{$id}'");
	if (empty($enable)) {
		message("成功关闭帖子",referer(),'success');
	}else{
		message("成功开启帖子",referer(),'success');
	}
}elseif($op == 'delete'){
	pdo_delete('xcommunity_carpool',array('id' => $id));
	message('删除成功',$this->createMobileUrl('carindex'),'success');
}
include $this->template('cardetail');