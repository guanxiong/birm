<?php
/**
 * 万能卡模块处理程序
 * * @author 石头鱼

 * @url http://www.00393.com/
 */
defined('IN_IA') or exit('Access Denied');

class sharecardsModuleProcessor extends WeModuleProcessor {
	public $name = 'sharecardsModuleProcessor';
	public $table_reply = 'sharecards_reply';

	public function isNeedInitContext() {
		return 0;
	}
	
	public function respond() {
		global $_W;
		$rid = $this->rule;
		$sql = "SELECT * FROM " . tablename($this->table_reply) . " WHERE `rid`=:rid LIMIT 1";
		$row = pdo_fetch($sql, array(':rid' => $rid));
		if (empty($row['id'])) {
			return array();
		}
		$now = time();
		if($now >= $row['start_time'] && $now <= $row['end_time']){
			return $this->respNews(array(
			'Title' => $row['title'],
			'Description' => htmlspecialchars_decode($row['description']),
			'PicUrl' => $_W['attachurl'] . $row['picture'],
			'Url' => $this->createMobileUrl('sharecards', array('id' => $rid)),
			));

		}else{
			return $this->respNews(array(
			'Title' => "活动已结束！",
			'Description' => "亲，活动已结束了！",
			'PicUrl' => $_W['attachurl'] . $row['picture'],
			//'Url' => $this->createMobileUrl('sharecards', array('id' => $rid)),
			));

		}		
	}

	public function isNeedSaveContext() {
		return false;
	}
}