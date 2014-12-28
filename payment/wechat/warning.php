<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * $sn$
 */
require '../../source/bootstrap.inc.php';
$input = file_get_contents('php://input');
/*
$input = "
<xml>
<AppId><![CDATA[wxa0ab09ff1cd1b49b]]></AppId>
<ErrorType>1001</ErrorType>
<Description><![CDATA[错诨描述]]></Description>
<AlarmContent><![CDATA[错诨详情]]></AlarmContent>
<TimeStamp>1393860740</TimeStamp>
<AppSignature><![CDATA[f8164781a303f4d5a944a2dfc68411a8c7e4fbea]]></AppSignature>
<SignMethod><![CDATA[sha1]]></SignMethod>
</xml>
";
*/
$obj = simplexml_load_string($input, 'SimpleXMLElement', LIBXML_NOCDATA);
if($obj instanceof SimpleXMLElement && !empty($obj->FeedBackId)) {
	$data = array(
		'appid' => trim($obj->AppId),
		'timestamp' => trim($obj->TimeStamp),
		'errortype' => trim($obj->ErrorType),
		'description' => trim($obj->Description),
		'alarmcontent' => trim($obj->AlarmContent),
		'appsignature' => trim($obj->AppSignature),
		'signmethod' => trim($obj->SignMethod),
	);
	require '../../source/bootstrap.inc.php';
	WeUtility::logging('pay-warning', $input);
}
exit('success');
