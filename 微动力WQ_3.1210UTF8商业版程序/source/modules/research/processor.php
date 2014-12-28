<?php
/**
 * 预约与调查模块处理程序
 *
 * @author WeEngine Team
 * @url http://bbs.we7.cc
 */
defined('IN_IA') or exit('Access Denied');

class ResearchModuleProcessor extends WeModuleProcessor {
	public function respond() {
		global $_W;
		$rid = $this->rule;
		if($rid) {
			$reply = pdo_fetch("SELECT * FROM " . tablename('research_reply') . " WHERE rid = :rid", array(':rid' => $rid));
			if($reply) {
				$sql = 'SELECT * FROM ' . tablename('research') . ' WHERE `weid`=:weid AND `reid`=:reid';
				$activity = pdo_fetch($sql, array(':weid' => $_W['weid'], ':reid' => $reply['reid']));
				$news = array();
				$news[] = array(
					'title' => $activity['title'],
					'description' => strip_tags($activity['description']),
					'picurl' => $_W['attachurl'] . $activity['thumb'],
					'url' => create_url('mobile/module/research', array('name' => 'research', 'id' => $activity['reid'], 'weid' => $_W['weid']))
				);
				return $this->respNews($news);
			}
		}
		return null;
	}
}
