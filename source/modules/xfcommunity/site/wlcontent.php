<?php
/*
二手商品详细页面

*/
$id = intval($_GPC['id']);
$op = $_GPC['op'];
$from_user = $_W['fans']['from_user'];
$item = pdo_fetch("SELECT * FROM".tablename('xcommunity_fled')."WHERE id='{$id}'");
if ($op == 'delete') {
	pdo_delete('xcommunity_fled',array('id' => $_GPC['id']));
	message('删除成功',$this->createMobileUrl('index'),'success');
}
include $this->template('content');