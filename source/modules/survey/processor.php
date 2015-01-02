<?php
/**
 * 调研模块处理程序
 *
 * @author 更多模块请浏览bbs.we7.cc
 * @url http://bbs.we7.cc
 */
defined('IN_IA') or exit('Access Denied');

class SurveyModuleProcessor extends WeModuleProcessor {
	public function respond() {
		global $_W;
		$rid = $this->rule;
		if($rid) {
			$reply = pdo_fetch("SELECT * FROM " . tablename('survey_reply') . " WHERE rid = :rid", array(':rid' => $rid));
			if($reply) {
				$sql = 'SELECT * FROM ' . tablename('survey') . ' WHERE `weid`=:weid AND `sid`=:sid';
				$activity = pdo_fetch($sql, array(':weid' => $_W['weid'], ':sid' => $reply['sid']));
				$news = array();
				$news[] = array(
					'title' => $activity['title'],
					'description' => strip_tags($activity['description']),
					'picurl' => $_W['attachurl'] . $activity['thumb'],
					'url' => create_url('mobile/module/survey', array('name' => 'survey', 'id' => $activity['sid'], 'weid' => $_W['weid']))
				);
				return $this->respNews($news);
			}
		}
		return null;
	}
}
