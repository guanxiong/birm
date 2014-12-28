<?php
/*
 * 积分兑换模块
 *
 * 作者 【艮随】
 * 
 */

defined('IN_IA') or exit('Access Denied');

class ExchangeModuleProcessor extends WeModuleProcessor {
	
	public function respond() {
	
		global $_W;
		
		$rid = $this->rule;
		
		$sql = "SELECT * FROM " . tablename('exchange_reply') . " WHERE `rid`=:rid LIMIT 1";
		
		$row = pdo_fetch($sql, array(':rid' => $rid));
		
		if (empty($row['id'])) {
		
			return array();
		}
		
		$now = time();
		
		$start_time = $this->module['config']['start_time'];
		
		$start_time = strtotime($start_time);
		
		$end_time = $this->module['config']['end_time'];
		
		$end_time = strtotime($end_time);
		
		$date=date('Y-m-d');
		
		$date = strtotime($date);
		
		return $this->respNews(array(
		
		'Title' => $row['title'],
		
		'Description' => $row['description'],
		
		'PicUrl' => $_W['attachurl'] . $row['picture'],
		
		'Url' => $this->createMobileUrl('index', array('rid' => $rid)),
		
		));

	}

}