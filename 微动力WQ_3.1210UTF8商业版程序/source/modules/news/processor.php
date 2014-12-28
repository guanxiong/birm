<?php
/**
 * 图文回复处理类
 * 
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

class NewsModuleProcessor extends WeModuleProcessor {
	public function respond() {
		global $_W;
		$rid = $this->rule;
		$sql = "SELECT id FROM " . tablename('news_reply') . " WHERE `rid`=:rid AND parentid = 0 ORDER BY RAND()";
		$main = pdo_fetch($sql, array(':rid' => $rid));
		if (empty($main['id'])) {
			return array();
		}
		$sql = "SELECT * FROM " . tablename('news_reply') . " WHERE id = :id OR parentid = :parentid ORDER BY parentid ASC, id ASC LIMIT 10";
		$commends = pdo_fetchall($sql, array(':id'=>$main['id'], ':parentid'=>$main['id']));
		$news = array();
		foreach($commends as $c) {
			$row = array();
			$row['title'] = $c['title'];
			$row['description'] = $c['description'];
			!empty($c['thumb']) && $row['picurl'] = $_W['attachurl'] . trim($c['thumb'], '/');
			$row['url'] = empty($c['url']) ? $this->createMobileUrl('detail', array('id' => $c['id'])) : $c['url'];
			$news[] = $row;
		}
		return $this->respNews($news);
	}
}
