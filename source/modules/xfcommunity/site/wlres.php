<?php
/*
小区活动报名处理
*/
$rid = intval($_GPC['rid']);
$item = pdo_fetch("SELECT * FROM".tablename('xcommunity_activity')."WHERE id='{$rid}'");
if ($_W['ispost']) {
	$data = array(
			'weid'       => $_W['weid'],
			'openid'     => $_W['fans']['from_user'], 
			'truename'   => $_GPC['truename'],
			'sex'        => $_GPC['sex'],
			'mobile'     => $_GPC['mobile'],
			'num'        => $_GPC['num'],
			'rid'        => $_GPC['rid'],
			'createtime' => TIMESTAMP,
		);
	pdo_insert('xcommunity_res',$data);
	pdo_query("UPDATE ".tablename('xcommunity_activity')." SET resnumber=resnumber+'{$_GPC['num']}' WHERE id='{$rid}'");
	message('报名成功',$this->createMobileUrl('activity'),'success');
}

include $this->template('res');