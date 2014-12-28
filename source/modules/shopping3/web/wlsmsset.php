<?php
/**
 * 2014-2-24
 * 购物车 分类管理 
 * 支持二级分类 来自微擎
 * @author 微动力
 * @url
 */
if($_GPC['action']=='test'){
 
	$title="这里是测试平台，给你发送邮件";
	$content="微动力祝您生意兴隆，财源广进.";
	//$temp=$this->_sendmail($title,$content);
	if($temp==1){
		message('邮件发送成功，您的邮件设置成功', $this->createWebUrl('Mailset'), 'success');
	}else{
		message('邮件发送成功，您的邮件设置成功,错误原因:'.$temp);
	}
}
if (checksubmit('submit')) {
	$insert=array(
		'weid'=>$_W['weid'],
		'sms_status'=>trim($_GPC['sms_status']),
		'sms_type'=>trim($_GPC['sms_type']),
		'sms_from'=>trim($_GPC['sms_from']),
		'sms_secret'=>trim($_GPC['sms_secret']),
		'sms_phone'=>trim($_GPC['sms_phone']),
		'sms_text'=>trim($_GPC['sms_text']),
		'sms_resgister'=>intval($_GPC['sms_resgister']),
 	);
	if (empty($_GPC['id'])) {
		pdo_insert('shopping3_set', $insert);
	} else {
		pdo_update('shopping3_set', $insert, array('weid'=>$_W['weid'],'id' => $_GPC['id']));
	}
	message('短信数据保存成功', $this->createWebUrl('Smsset'), 'success');
}
$set = pdo_fetch("SELECT * FROM ".tablename('shopping3_set')." WHERE weid = :weid", array(':weid' => $_W['weid']));
if($set==false){
	$set=array(
		'id'=>0,
	);
}else{

}
include $this->template('web/smsset');