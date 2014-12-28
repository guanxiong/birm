<?php
/*
 * 
 *
 */
defined('IN_IA') or exit('Access Denied');

class HotelModuleProcessor extends WeModuleProcessor {

	public function isNeedInitContext() {
		return 0;
	}
	
	public function respond() {
	
		global $_GPC, $_W;
		
		$rid = $this->rule;
		
		$message = $this->message;

		$from = $message['from'];
		
		$profile = fans_search($from);
		
		$sql = "SELECT * FROM " . tablename('hotel_reply') . " WHERE `rid`=:rid LIMIT 1";
		
		$row = pdo_fetch($sql, array(':rid' => $rid));
		
		if (empty($row['id'])) {
		
			return array();
		}
		
		$now = time();
		
		$tablenum = $this->module['config']['tablenum'];
		
		//$reply = pdo_fetchall("SELECT * FROM ".tablename('hotel_order')." WHERE rid = :rid AND openid = :openid ORDER BY time DESC", array(':rid' => $rid, ':openid' => $from ));
		
		//print_r($cfghost);
		
		return $this->respNews(array(
		
		'Title' => $row['title'],
		
		'Description' => $tips.$row['description'],
		
		'PicUrl' => $row['picture'],
		
		'Url' => $this->createMobileUrl('index', array('rid' => $rid)),
		
		));

	}

	public function isNeedSaveContext() {
		return false;
	}
}