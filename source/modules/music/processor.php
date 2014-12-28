<?php
/**
 * 语音回复处理类
 *
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

class MusicModuleProcessor extends WeModuleProcessor {
	public function respond() {
		global $_W;
		$rid = $this->rule;
		$sql = "SELECT * FROM " . tablename('music_reply') . " WHERE `rid`=:rid ORDER BY RAND()";
		$item = pdo_fetch($sql, array(':rid' => $rid));
		if (empty($item['id'])) {
			return array();
		}
		return $this->respMusic(array(
			'Title'	=> $item['title'],
			'Description' => $item['description'],
			'MusicUrl' => $item['url'],
			'HQMusicUrl' => $item['hqurl'],
		));
	}
}
