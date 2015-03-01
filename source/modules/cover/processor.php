<?php
/**
 * 扩展封面回复模块处理程序
 *
 * @author WeEngine Team
 * @url 
 */
defined('IN_IA') or exit('Access Denied');

class CoverModuleProcessor extends WeModuleProcessor {
	public function respond() {
		global $_W;
		$content = $this->message['content'];
		//这里定义此模块进行消息处理时的具体过程, 请查看微动力文档来编写你的代码
		$reply = pdo_fetch('SELECT * FROM ' . tablename('cover_reply') . ' WHERE `rid`=:rid', array(':rid' => $this->rule));
		if(!empty($reply)) {
			$entry = pdo_fetch("SELECT eid FROM ".tablename('modules_bindings')." WHERE module = :module AND do = :do", array(':module' => $reply['module'], ':do' => $reply['do']));
			$news = array();
			$news[] = array(
				'title' => $reply['title'],
				'description' => $reply['description'],
				'picurl' => $reply['thumb'],
				'url' => empty($reply['url']) ? create_url('mobile/entry', array('eid' => $entry['eid'], 'weid' => $_W['weid'])) : $reply['url'],
			);
			return $this->respNews($news);
		}
		return '';
	}
}
