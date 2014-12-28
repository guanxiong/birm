<?php
/**
 * 送祝福模块处理程序
 *
 * @author 微鼎
 * @url http://www.weidim.com/
 */
defined('IN_IA') or exit('Access Denied');

class BlessModuleProcessor extends WeModuleProcessor {
	public $name = 'BlessModuleProcessor';
	public $table_reply = 'bless_reply';

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
			'Url' => $this->createMobileUrl('bless', array('id' => $rid)),
			));

		}else{
			return $this->respNews(array(
			'Title' => "送祝福活动已结束！",
			'Description' => "亲，送祝福活动已结束了！",
			'PicUrl' => $_W['attachurl'] . $row['picture'],
			'Url' => $this->createMobileUrl('bless', array('id' => $rid)),
			));

		}		
	}

	public function isNeedSaveContext() {
		return false;
	}
}