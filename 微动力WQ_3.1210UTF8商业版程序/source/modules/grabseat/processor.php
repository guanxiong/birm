<?php
/*
 *  凑一对模块 
 *
 *  [艮随] Copyright (c)
 */
defined('IN_IA') or exit('Access Denied');

class GrabseatModuleProcessor extends WeModuleProcessor {

	public function isNeedInitContext() {
		return 0;
	}
	
	public function respond() {
	
		global $_GPC, $_W;
		
		$rid = $this->rule;
		
		$message = $this->message;

		$from = $message['from'];
		
		$sql = "SELECT * FROM " . tablename('grabseat_reply') . " WHERE `rid`=:rid LIMIT 1";
		
		$row = pdo_fetch($sql, array(':rid' => $rid));
		
		if (empty($row['id'])) {
		
			return array();
		}
		
		$now = time();
		
		$start_time = $this->module['config']['start_time'];
		
		$start_time = strtotime($start_time);
		
		$end_time = $this->module['config']['end_time'];
		
		$end_time = strtotime($end_time);
		
		$tablenum = $this->module['config']['tablenum'];
		
		if($now >= $start_time && $now <= $end_time){
		//if($now){
		
			$graber = pdo_fetch("SELECT * FROM ".tablename('grabseat_record')." WHERE rid = :rid AND graberopenid = :from_user", array(':rid' => $rid, ':from_user' => $from ));
			
			$fiter = pdo_fetch("SELECT * FROM ".tablename('grabseat_record')." WHERE rid = :rid AND fiteropenid = :from_user", array(':rid' => $rid, ':from_user' => $from ));
		
			if($graber['graberopenid']){
			
				if($graber['fiteropenid']){
										
					return $this->respNews(array(
					
					'Title' => '恭喜！成功凑一对！',
					
					'Description' => $row['description'],
					
					'PicUrl' => $_W['attachurl'] . $row['picture'],
					
					'Url' => $this->createMobileUrl('fitseat', array('id' => $graber['id'], 'rid' => $graber['rid'])),
					
					));

				}
				else{
					
					return $this->respNews(array(
					
					'Title' => '还没有人来赴约哦~',
					
					'Description' => $row['description'],
					
					'PicUrl' => $_W['attachurl'] . $row['picture'],
					
					'Url' => $this->createMobileUrl('showseat', array('id' => $graber['id'], 'rid' => $graber['rid'])),
					
					));
				
				}
				
			}
			else{
			
				if($fiter){

					return $this->respNews(array(
					
					'Title' => '恭喜！成功凑一对！',
					
					'Description' => $tip."\n"."\n"."\n".$row['description'],
					
					'PicUrl' => $_W['attachurl'] . $row['picture'],
					
					'Url' => $this->createMobileUrl('fitseat', array('id' => $fiter['id'], 'rid' => $fiter['rid'])),
					
					));
				
				}
				else{
				
					$fiter = pdo_fetchall("SELECT count(fiteropenid)as fitertotal FROM ".tablename('grabseat_record')." WHERE rid = :rid ", array(':rid' => $rid ));

					if($fiter['0']['fitertotal'] >= $tablenum){
					
						return $this->respText('来晚一步，情侣座已经抢完了。');
					
					}
					else{
					
						return $this->respNews(array(
						
						'Title' => $row['title'],
						
						'Description' => $row['description'],
						
						'PicUrl' => $_W['attachurl'] . $row['picture'],
						
						'Url' => $this->createMobileUrl('grabseat', array('rid' => $rid)),
						
						));

					}

				}
			
			}
			
		}
		else{
		
			return $this->respText('不在活动时间哦');
		
		}
		
	}

	public function isNeedSaveContext() {
		return false;
	}
}