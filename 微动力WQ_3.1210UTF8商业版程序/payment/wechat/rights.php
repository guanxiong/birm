<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * $sn$
 */
$input = file_get_contents('php://input');
/*
$input = "
<xml><OpenId><![CDATA[oWkAwtz6I3Cxm5KD924Qkmtl6cxE]]></OpenId>
<AppId><![CDATA[wxa0ab09ff1cd1b49b]]></AppId>
<TimeStamp>1397358158</TimeStamp>
<MsgType><![CDATA[request]]></MsgType>
<FeedBackId>13200121263314497669</FeedBackId>
<TransId><![CDATA[1218504801201404133203605474]]></TransId>
<Reason><![CDATA[测试]]></Reason>
<Solution><![CDATA[测试]]></Solution>
<ExtInfo><![CDATA[ 18635132526]]></ExtInfo>
<AppSignature><![CDATA[981c7cc2ea00f97d444c692be9163a912b6a6ebd]]></AppSignature>
<SignMethod><![CDATA[sha1]]></SignMethod>
</xml>
";
*/
$obj = simplexml_load_string($input, 'SimpleXMLElement', LIBXML_NOCDATA);
if($obj instanceof SimpleXMLElement && !empty($obj->FeedBackId)) {
	$data = array(
		'openid' => trim($obj->OpenId),
		'appid' => trim($obj->AppId),
		'timestamp' => trim($obj->TimeStamp),
		'msgtype' => trim($obj->MsgType),
		'feedbackid' => trim($obj->FeedBackId),
		'transid' => trim($obj->TransId),
		'reason' => trim($obj->Reason),
		'solution' => trim($obj->Solution),
		'extinfo' => trim($obj->ExtInfo),
		'appsignature' => trim($obj->AppSignature),
		'signmethod' => trim($obj->SignMethod),
	);
	if (!empty($obj->PicInfo) && !empty($obj->PicInfo->item)) {
		foreach ($obj->PicInfo->item as $item) {
			$data['picinfo'][] = trim($item->PicUrl);
		}
	}
	require '../../source/bootstrap.inc.php';
	WeUtility::logging('pay-rights', $input);
	$wechat = pdo_fetch("SELECT weid, payment, `key`, secret FROM ".tablename('wechats')." WHERE `key` = :key", array(':key' => $data['appid']));
	$_W['weid'] = $wechat['weid'];
	if (empty($wechat['payment'])) {
		exit('failed');
	}
	$wechat['payment'] = iunserializer($wechat['payment']);
	$data['appkey'] = $wechat['payment']['wechat']['signkey'];
	if (!checkSign($data)) {
		exit('failed');
	}
	if ($data['msgtype'] == 'request') {
		$insert = array(
			'weid' => $_W['weid'],
			'openid' => $data['openid'],
			'feedbackid' => $data['feedbackid'],
			'transid' => $data['transid'],
			'reason' => $data['reason'],
			'solution' => $data['solution'],
			'remark' => $data['extinfo'],
			'createtime' => $data['timestamp'],
			'status' => 0,
		);
		pdo_insert('shopping_feedback', $insert);
		exit('success');
	} elseif ($data['msgtype'] == 'confirm') {
		pdo_update('shopping_feedback', array('status' => 1), array('feedbackid' => $data['feedbackid']));
		exit('success');
	} elseif ($data['msgtype'] == 'reject') {
		pdo_update('shopping_feedback', array('status' => 2), array('feedbackid' => $data['feedbackid']));
		exit('success');
	} else {
		exit('failed');
	}
}

function checkSign($data) {
	$string = '';
	$keys = array('appid', 'timestamp', 'openid', 'appkey');
	sort($keys);
	foreach($keys as $key) {
		$v = $data[$key];
		$key = strtolower($key);
		$string .= "{$key}={$v}&";
	}
	$string = sha1(rtrim($string, '&'));
	if ($data['appsignature'] == $string) {
		return true;
	} else {
		return false;
	}
}
