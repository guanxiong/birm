<?php
/**
 * 全民抢礼品模块定义
 *
 * @author 石头鱼
 * @url http://www.00393.com/
 */
defined('IN_IA') or exit('Access Denied');

class stonefish_grabgiftsModuleProcessor extends WeModuleProcessor {
	public $name = 'stonefish_grabgiftsModuleProcessor';
	public $table_reply = 'stonefish_grabgifts_reply';
	public $table_list   = 'stonefish_grabgifts_userlist';

	public function isNeedInitContext() {
		return 0;
	}
	
	public function respond() {
		global $_W;
		$rid = $this->rule;
		$from= $this->message['from'];
		$tag = $this->message['content'];
		$weid = $_W['weid'];//当前公众号ID

				
				//推送分享图文内容
				$sql = "SELECT * FROM " . tablename($this->table_reply) . " WHERE `rid`=:rid LIMIT 1";
				$row = pdo_fetch($sql, array(':rid' => $rid));
					if (empty($row['id'])) {
						return array();
					}
					//查询是否被屏蔽
					$lists = pdo_fetch("SELECT status FROM ".tablename($this->table_list)." WHERE from_user = '".$from."' and weid = '".$weid."' and rid= '".$rid."' limit 1" );
					if(!empty($lists)){//查询是否有记录
					  if($lists['status']==0){
						$message = "亲，".$row['title']."活动中您可能有作弊行为已被管理员暂停了！请联系".$_W['account']['name']."";
						return $this->respText($message);					
					  }		
					  
					}
					$now = time();
					if($now >= $row['start_time'] && $now <= $row['end_time']){						
						if ($row['status']==0){
						    $message = "亲，".$row['title']."活动暂停了！";
						    return $this->respText($message);
						}else{
						    $picture = $row['picture'];
							if (substr($picture,0,6)=='images'){
			                    $picture = $_W['attachurl'] . $picture;
			                }else{
			                    $picture = $_W['siteroot'] . $picture;
			                }
						    return $this->respNews(array(
							    'Title' => $row['title'],
							    'Description' => htmlspecialchars_decode($row['description']),
							    'PicUrl' => $picture,
							    'Url' => $this->createMobileUrl('grabgifts', array('rid' => $rid, 'from_user' => base64_encode(authcode($this->message['from'], 'ENCODE')))),
						    ));
						}
					}else{
						$message = "亲，".$row['title']."活动没有开始或已结束了！";
						return $this->respText($message);				
					}
	}

	public function isNeedSaveContext() {
		return false;
	}

}