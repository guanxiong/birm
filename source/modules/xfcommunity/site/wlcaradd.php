<?php
/*
拼车处理程序
*/
$op = !empty($_GPC['op']) ? $_GPC['op'] : 'passenger';
$data = array(
		'weid'           => $_W['weid'],
		'openid'         => $_W['fans']['from_user'],
		'start_position' => $_GPC['start_position'],
		'end_position'   => $_GPC['end_position'],
		'startMinute'    => $_GPC['startMinute'],
		'startSeconds'   => $_GPC['startSeconds'],
		'enable'         => 1,
		'content'        => $_GPC['content'],
		'createtime'     => TIMESTAMP,
	);
if ($op == 'passenger') {
	$data['license_number'] = $_GPC['license_number'];
	$data['car_model']      = $_GPC['car_model'];
	$data['car_brand']      = $_GPC['car_brand'];
	$data['status']         = 1;
}elseif ($op == 'carowner') {
	$data['status']         = 2;
}
if ($_W['ispost']) {
	if (empty($_GPC['id'])) {
		pdo_insert('xcommunity_carpool',$data);
	}else{
		pdo_update('xcommunity_carpool',$data,array('id' => $_GPC['id']));
	}
	message('发布成功',$this->createMobileUrl('carindex'),'success');
}


include $this->template('caradd');