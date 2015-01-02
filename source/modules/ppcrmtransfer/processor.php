<?php
/**
 * 多客服转接模块处理程序
 *
 * @author WeEngine Team
 * @url http://bbs.we7.cc
 */
defined('IN_IA') or exit('Access Denied');

class PpcrmtransferModuleProcessor extends WeModuleProcessor {
	public function respond() {
		$now = TIMESTAMP;
		$content = <<<DAT
<xml>
<ToUserName><![CDATA[{$this->message['from']}]]></ToUserName>
<FromUserName><![CDATA[{$this->message['to']}]]></FromUserName>
<CreateTime>{$now}</CreateTime>
<MsgType><![CDATA[transfer_customer_service]]></MsgType>
</xml>
DAT;
		exit(trim($content));
	}
}