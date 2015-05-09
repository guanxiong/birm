<?php
/**
 * [WDL] Copyright (c) 2013 B2CTUI.COM
 */
defined('IN_IA') or exit('Access Denied');
checklogin();
$post = $_GPC['__input'];

if(empty($post['no'])) {
	exit('error-receiver');
}
$sql = 'DELETE FROM ' . tablename('uni_verifycode') . ' WHERE `createtime`<' . (TIMESTAMP - (TIMESTAMP % 86400));
pdo_query($sql);

$sql = 'SELECT * FROM ' . tablename('uni_verifycode') . ' WHERE `receiver`=:receiver AND `uniacid`=:uniacid';
$pars = array();
$pars[':receiver'] = $post['no'];
$pars[':uniacid'] = $_W['uniacid'];
$row = pdo_fetch($sql, $pars);
$record = array();
if(!empty($row)) {
	if($row['total'] >= 5) {
		exit('error-limit');
	}
	$code = $row['verifycode'];
	$record['total']++;
} else {
	$code = random(6, true); 
	$record['uniacid'] = $_W['uniacid'];
	$record['receiver'] = $post['no'];
	$record['verifycode'] = $code;
	$record['total'] = 1;
	$record['createtime'] = TIMESTAMP;
}
	
if($post['type'] == 'email') {
	$content = "您的邮箱验证码为: {$code} 您正在使用{$_W['account']['name']}相关功能, 需要你进行身份确认.";
	$result = ihttp_email($post['no'], "{$_W['account']['name']}身份确认验证码", $content);
} else {
	require model('cloud');
	$content = "您的短信验证码为: {$code} 您正在使用{$_W['account']['name']}相关功能, 需要你进行身份确认.【微动力】";
	$result = cloud_sms_send($post['no'], $content);
}

if(is_error($result)) {
	header('error: ' . urlencode($result['message']));
	exit('error-fail');
} else {
	if(!empty($row)) {
		pdo_update('uni_verifycode', $record, array('id' => $row['id']));
	} else {
		pdo_insert('uni_verifycode', $record);
	}
}
exit('success');