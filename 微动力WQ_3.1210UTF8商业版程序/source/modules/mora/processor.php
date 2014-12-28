<?php
/**
 * 猜拳模块处理程序
 *
 * @author 微鼎
 * @url http://www.weidim.com/
 */
defined('IN_IA') or exit('Access Denied');

class MoraModuleProcessor extends WeModuleProcessor {
	public $name = 'MoraModuleProcessor';
	public $table_reply = 'mora_reply';
	public $table_list   = 'mora_list';

	public function isNeedInitContext() {
		return 0;
	}
	
	public function respond() {
		global $_W;
		$rid = $this->rule;
		$from= $this->message['from'];
		$arrtag = $this->message['content'];
		$sql = "SELECT * FROM " . tablename($this->table_reply) . " WHERE `rid`=:rid LIMIT 1";
		$row = pdo_fetch($sql, array(':rid' => $rid));
		$now = time();
		if($now >= $row['start_time'] && $now <= $row['end_time']){
			if ($this->inContext) {
				return $this->march();
			} else {
				return $this->begin();
			}
		}else{
			$message = "亲，小鸟猜拳活动已结束了！";
			return $this->respText($message);				
		}
	}
	
	private function begin() {
		global $_W;
		$rid = $this->rule;		
		$reply = pdo_fetch("SELECT * FROM ".tablename($this->table_reply)." WHERE rid = '{$rid}' LIMIT 1");
		if (empty($reply)) {
			return array();
		}
		$message = $reply['rule'];		
		$this->beginContext();		
		return $this->respText($message);
	}
	
	private function march() {
		global $_W, $engine;
		$rid = $this->rule;	
		$arrtag = $this->message['content'];
		if (!in_array($this->message['msgtype'], array('text', 'image'))) {
			return false;
		}		
		$reply = pdo_fetch("SELECT * FROM ".tablename($this->table_reply)." WHERE rid = '{$rid}' LIMIT 1");
		if($arrtag=="结束"){
			$message = "游戏结束，感谢参与！";
			$this->endContext();
			return $this->respText($message);		
		}else{
			//游戏进行中。。。
			//系统产生一个随机数，1、石头，2、剪刀，3、布
			$random=mt_rand(1,3);
			$randtag=array('00','/:@@','/:v','/:ok');
			if($arrtag=="/:@@") {
				$me=1;
				if($random==$me){
					$message = $randtag[1].",".$reply['draw'];
				}elseif($random==2){
					$message = $randtag[2].",".$reply['win'];
				}elseif($random==3){
					$message = $randtag[3].",".$reply['lose'];
				}
			}elseif($arrtag=="/:v"){
				$me=2;
				if($random==$me){
					$message = $randtag[2].",".$reply['draw'];
				}elseif($random==1){
					$message = $randtag[1].",".$reply['lose'];
				}elseif($random==3){
					$message = $randtag[3].",".$reply['win'];
				}
			}elseif($arrtag=="/:ok"){
				$me=3;
				if($random==$me){
					$message = $randtag[3].",".$reply['draw'];
				}elseif($random==1){
					$message = $randtag[1].",".$reply['win'];
				}elseif($random==2){
					$message = $randtag[2].",".$reply['lose'];
				}
			}else{
				$message = "你的输入有误，请重新出拳！";

			}
			return $this->respText($message);
		}		
		
	}

	public function isNeedSaveContext() {
		return false;
	}
	public function hookBefore() {
		global $_W, $engine;
	}

}