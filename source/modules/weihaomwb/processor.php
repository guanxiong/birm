<?php
/**
 * 别踩白块儿游戏模块处理程序
 * 别踩抽奖模块
 *
 * [皓蓝] www.weixiamen.cn 5517286
 */

defined('IN_IA') or exit('Access Denied');

class Weihaom_wbModuleProcessor extends WeModuleProcessor {
	public function respond() {
		$content = $this->message['content'];
		//这里定义此模块进行消息处理时的具体过程, 请查看微动力文档来编写你的代码
		$reply = pdo_fetch("SELECT * FROM ".tablename('weihaomwb_reply')." WHERE rid = :rid", array(':rid' => $this->rule));
		if (!empty($reply)) {
			$response[] = array(
				'title' => $reply['title'],
				'description' => $reply['description'],
				'picurl' => $reply['cover'],
				'url' => $this->createMobileUrl('index', array('rid' => $reply['rid'])),
			);
			return $this->respNews($response);
		}
	}
}