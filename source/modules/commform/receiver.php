<?php
/**
 * 通用表单模块订阅器
 *
 * @author Godietion Koo
 * @url http://beidoulbs.com/
 */
defined('IN_IA') or exit('Access Denied');

class CommformModuleReceiver extends WeModuleReceiver {
	public function receive() {
		$type = $this->message['type'];
		//这里定义此模块进行消息订阅时的, 消息到达以后的具体处理过程, 请查看微新星文档来编写你的代码
	}
}