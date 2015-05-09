<?php
/*
 * 转发有礼模块 share
 *
 * @author 微动力
 * @url 
 */
defined('IN_IA') or exit('Access Denied');

class forwardModuleProcessor extends WeModuleProcessor {
	public $name = 'forwardModuleProcessor';
	public $table_reply = 'forward_reply';
	public $table_list   = 'forward_list';

	public function isNeedInitContext() {
		return 0;
	}
	
	public function respond() {
		global $_W;
		$rid = $this->rule;
		$from= $this->message['from'];
		$tag = $this->message['content'];
		$weid = $_W['weid'];//当前公众号ID

				//$sharer = $this->check();
				$insert = array(
					'weid' => $weid,
				    'from_user' => $from,
					'sharetime' => time(),
				);
				//if(empty($sharer)){
				//pdo_insert($this->table_list, $insert);
				//}
				//推送分享图文内容
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
							'Url' => $this->createMobileUrl('share', array('id' => $rid, 'from_user' => base64_encode(authcode($this->message['from'], 'ENCODE')))),
						));
					}else{
						$message = "亲，转发有礼活动已结束了！";
						return $this->respText($message);				
					}
	}

	public function isNeedSaveContext() {
		return false;
	}
	private function check() {
		global $_W;
		$weid = $_W['weid'];//当前公众号ID
		$rid = $this->rule;
		$from= $this->message['from'];		
		$sharer = pdo_fetch("SELECT * FROM ".tablename($this->table_list)." WHERE from_user = '".$from."' and weid = '".$weid."' limit 1" );		
		return $sharer;

	}

}