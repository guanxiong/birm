<?php
/**
 * 中秋博饼模块处理程序
 *
 * @author Meepo
 * @url http://meepo.com.cn
 */
defined('IN_IA') or exit('Access Denied');

class MgamblemoonModuleProcessor extends WeModuleProcessor {
	public function respond() {
		$content = $this->message['content'];
		global $_W;
		$rid = $this->rule;
		$sql = "SELECT * FROM " . tablename('mgamblemoon_reply') . " WHERE `rid`=:rid LIMIT 1";
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