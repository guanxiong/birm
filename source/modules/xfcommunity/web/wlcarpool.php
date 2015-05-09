<?php
/*


*/

$list = pdo_fetchAll("SELECT * FROM".tablename('xcommunity_carpool')."WHERE weid='{$_W['weid']}'");

foreach ($list as $key => $value) {
	$fans = pdo_fetch("SELECT * FROM".tablename('fans')."WHERE from_user='{$value['openid']}'");
	$list[$key]['nickname'] = $fans['nickname'];
}
if ($_GPC['foo'] == 'delete' ) {
	pdo_delete('xcommunity_carpool',array('id' => $_GPC['id']));
	message('删除成功',referer(),'success');
}
//print_r($list);
include $this->template('carpool');