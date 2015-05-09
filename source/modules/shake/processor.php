<?php
/**
 * 摇一摇中奖模块处理程序
 *
 * @author 更多模块请浏览bbs.birm.co
 * @url http://we7.cc
 */
defined('IN_IA') or exit('Access Denied');

class ShakeModuleProcessor extends WeModuleProcessor {
	public function respond() {
		$content = $this->message['content'];
		//这里定义此模块进行消息处理时的具体过程, 请查看微新星文档来编写你的代码
		$reply = pdo_fetch("SELECT * FROM ".tablename('shake_reply')." WHERE rid = :rid", array(':rid' => $this->rule));
		if (!empty($reply)) {
			$response[] = array(
				'title' => $reply['title'],
				'description' => $reply['description'],
				'picurl' => $reply['cover'],
				'url' => $this->buildSiteUrl($this->createMobileUrl('detail', array('rid' => $reply['rid']))),
			);
			return $this->respNews($response);
		}
	}
}