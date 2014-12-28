<?php
/**
 * 微喜帖
 *
 * @author 大路货
 * @url 
 */
defined('IN_IA') or exit('Access Denied');

class DialectModuleProcessor extends WeModuleProcessor {

	public function respond() {
		global $_W;
		$rid = $this->rule;
		$fromuser = $this->message['from'];
		if($rid) {
			$reply = pdo_fetch("SELECT * FROM " . tablename('feng_dialect') . " WHERE rid = :rid", array(':rid' => $rid));
			if($reply) {
				$news = array();
				$news[] = array(
					'title' => $reply['title'],
					'description' =>'',
					'picurl' =>$reply['photo'],
					'url' => $this->createMobileUrl('detail', array('id' => $reply['id'])),
				);
				return $this->respNews($news);
			}
		}
		return null;
	}
}

