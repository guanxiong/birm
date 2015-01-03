<?php
/**
 * 关键字回复模块处理程序
 *
 * @author 高速出行助手
 * @url http://ssplay.cn
 */
defined('IN_IA') or exit('Access Denied');

class RespondModuleProcessor extends WeModuleProcessor {
	public function respond() {
		$content = $this->message['content'];
		//这里定义此模块进行消息处理时的具体过程, 请查看微新星文档来编写你的代码


		return $this->respText('已收到');
	}
}