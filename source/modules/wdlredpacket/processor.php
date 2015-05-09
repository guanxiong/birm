<?php
/**
 * 春节压岁钱模块处理程序
 *
 * @author Wdl
 * @url http://Wdl.com.cn
 */
defined('IN_IA') or exit('Access Denied');

class wdlredpacketModuleProcessor extends WeModuleProcessor {
	public function respond() {
		$content = $this->message['content'];
		global $_W;
		$rid = $this->rule;
		$sql = "SELECT * FROM " . tablename('wdlredpacket_reply') . " WHERE `rid`=:rid LIMIT 1";
		$row = pdo_fetch($sql, array(':rid' => $rid));
		if (empty($row['id'])) {
			return array();
		}
		$title = pdo_fetchcolumn("SELECT name FROM ".tablename('rule')." WHERE id = :rid LIMIT 1", array(':rid' => $rid));
		return $this->respNews(array(
			'Title' => $title,
			'Description' => $row['description'],
			'PicUrl' => $_W['attachurl'] . $row['picture'],
			'Url' => $this->createMobileUrl('info', array('id' => $rid)),
		));
	}
}