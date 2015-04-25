<?php
/**
 * 2014-2-24
 * 购物车 分类管理 
 * 支持二级分类 来自微动力
 * @author 微动力
 * @url
 */
if($_GPC['action']=='test'){
 
	$title="这里是测试平台，给你发送邮件";
	$content="祝您生意兴隆，财源广进.";
	$temp=$this->_sendmail($title,$content);
	if($temp==1){
		message('邮件发送成功，您的邮件设置成功', $this->createWebUrl('Mailset'), 'success');
	}else{
		message('邮件发送成功，您的邮件设置成功,错误原因:'.$temp);
	}
}if (checksubmit('submit')) {
	$insert=array(
		'weid'=>$_W['weid'],
		'mail_smtp'=>trim($_GPC['mail_smtp']),
		'mail_to'=>trim($_GPC['mail_to']),
		'mail_user'=>trim($_GPC['mail_user']),
		'mail_psw'=>trim($_GPC['mail_psw']),
		'mail_status'=>$_GPC['mail_status'],
	);
	if (empty($_GPC['id'])||$_GPC['id']==0) {
		$temp=pdo_insert('shopping3_set', $insert);
	} else {
		$temp=pdo_update('shopping3_set', $insert, array('weid'=>$_W['weid'],'id' => $_GPC['id']));
	}
	if($temp==false){
		message('邮件数据保存失败');
	}else{
		message('邮件数据保存成功', $this->createWebUrl('Mailset'), 'success');
	}
}
$set = pdo_fetch("SELECT * FROM ".tablename('shopping3_set')." WHERE weid = :weid order by id desc", array(':weid' => $_W['weid']));
if($set==false){
	$set=array(
		'id'=>0,
	);
}else{

}
include $this->template('web/mailset');