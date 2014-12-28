<?php
/**
 * 语音回复处理类
 * 
 * [WeLan System] Copyright (c) 2013 WeLan.CC
 */
defined('IN_IA') or exit('Access Denied');

class YqsModuleProcessor extends WeModuleProcessor {
	
	public function respond() {
		global $_W;
		$rid = $this->rule;
		$sql = "SELECT * FROM " . tablename('yqs_reply') . " WHERE `rid`=:rid LIMIT 1";
		$row = pdo_fetch($sql, array(':rid' => $rid));
		if (empty($row['id'])) {
			return array();
		}
		$title = pdo_fetchcolumn("SELECT name FROM ".tablename('rule')." WHERE id = :rid LIMIT 1", array(':rid' => $rid));
		return $this->respNews(array(
			'Title' => $title,
			'Description' => $row['description'],
			'PicUrl' => $_W['attachurl'] . $row['picture'],
			'Url' => $this->createMobileUrl('lottery', array('id' => $rid)),
		));
	}
}
