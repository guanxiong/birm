<?php
/**
 * 微汽车模块处理程序
 *
 * @author 超级无聊
 * @url 
 */
defined('IN_IA') or exit('Access Denied');

class CarModuleProcessor extends WeModuleProcessor {
	public function respond() {
		$content = $this->message['content'];
		//这里定义此模块进行消息处理时的具体过程, 请查看微动力文档来编写你的代码
	}
}