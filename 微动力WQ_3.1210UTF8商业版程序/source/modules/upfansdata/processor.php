<?php
/**
 * 送祝福模块处理程序
 *
 * @author 微鼎
 * @url http://www.weidim.com/
 */
defined('IN_IA') or exit('Access Denied');

class upfansdataModuleProcessor extends WeModuleProcessor {
	public $name = 'upfansdataModuleProcessor';
	public $table_reply = 'upfansdata_reply';
	public $table_list   = 'upfansdata_list';

	public function isNeedInitContext() {
		return 0;
	}
	
	public function respond() {
		global $_W;
		$rid = $this->rule;
		$from= $this->message['from'];
		$tag = $this->message['content'];
		$weid = $_W['weid'];//当前公众号ID

				//$upfansdatar = $this->check();
				$insert = array(
					'weid' => $weid,
				    'from_user' => $from,
					'upfansdatatime' => time(),
				);
				//if(empty($upfansdatar)){
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
							'Url' => $this->createMobileUrl('upfansdata', array('id' => $rid, 'from_user' => base64_encode(authcode($this->message['from'], 'ENCODE')))),
						));
					}else{
						$message = "亲，更新资料有礼活动已结束了！";
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
		$upfansdatar = pdo_fetch("SELECT * FROM ".tablename($this->table_list)." WHERE from_user = '".$from."' and weid = '".$weid."' limit 1" );		
		return $upfansdatar;

	}

}