<?php
/**
 * 抢礼品模块定义
 * [皓蓝] www.weixiamen.cn 5517286
 */
defined('IN_IA') or exit('Access Denied');

class hlzonyuModuleProcessor extends WeModuleProcessor {
	public $name = 'hlzonyuModuleProcessor';
	public $table_reply  = 'hlzonyu_reply';
	public $table_list   = 'hlzonyu_list';

	public function isNeedInitContext() {
		return 0;
	}
	
	public function respond() {
		global $_W;
		$rid = $this->rule;
		$from= $this->message['from'];
		$tag = $this->message['content'];
		$weid = $_W['weid'];//当前公众号ID

				//$zonyur = $this->check();
				$insert = array(
					'weid' => $weid,
				    'from_user' => $from,
					'zonyutime' => time(),
				);
				//if(empty($zonyur)){
				//pdo_insert($this->table_list, $insert);
				//}
				//推送分享图文内容
				$sql = "SELECT * FROM " . tablename($this->table_reply) . " WHERE `rid`=:rid LIMIT 1";
				$row = pdo_fetch($sql, array(':rid' => $rid));
					if (empty($row['id'])) {
						return array();
					}
					//查询是否被屏蔽
					$lists = pdo_fetch("SELECT status,zonyunum,zhongjiang FROM ".tablename($this->table_list)." WHERE from_user = '".$from."' and weid = '".$weid."' and rid= '".$rid."' limit 1" );
					if(!empty($lists)){//查询是否有记录
					  if($lists['status']==0){
						$message = "亲，".$row['title']."活动中您可能有作弊行为已被管理员暂停了！请联系".$_W['account']['name']."";
						return $this->respText($message);					
					  }
					
					  //查询是否中奖
					  if($lists['zhongjiang']==1){
					   $zhongjiang = "亲！恭喜中奖了，请点击查看！";
					  }
					}
					$now = time();
					if($now >= $row['start_time'] && $now <= $row['end_time']){						
						if ($row['status']==0){
						    $message = "亲，".$row['title']."活动暂停了！";
						    return $this->respText($message);
						}else{
						    return $this->respNews(array(
							    'Title' => $row['title'],
							    'Description' => htmlspecialchars_decode($row['description']).$zhongjiang,
							    'PicUrl' => $_W['attachurl'] . $row['picture'],
							    'Url' => $this->createMobileUrl('zonyu', array('id' => $rid)),
						    ));
						}
					}else{
						$message = "亲，".$row['title']."活动没有开始或已结束了！";
						return $this->respNews(array(
							    'Title' => $row['title'],
							    'Description' => "活动没有开始或已结束了！",
							    'PicUrl' => $_W['attachurl'] . $row['picture'],
							    'Url' => $this->createMobileUrl('zonyu', array('id' => $rid)),
						    ));				
					}
	}

	public function isNeedSaveContext() {
		return false;
	}


}