<?php
/**
 *微吧
 * 
 * [qq 81324093] Copyright (c) 2013 19.3cm
 */
defined('IN_IA') or exit('Access Denied');

class snsModuleProcessor extends WeModuleProcessor {
	
	public $name = 'snsModuleProcessor';

	public function isNeedInitContext() {
		return 0;
	}
	
	public function respond() {
		global $_W;
		$rid = $this->rule;
		$sql = "SELECT * FROM " . tablename('sns') . " WHERE `rid`=:rid LIMIT 1";
		$row = pdo_fetch($sql, array(':rid' => $rid));
		if (empty($row['id'])) {
			return array();
		}
		$lastvisit=TIMESTAMP;
		$user = pdo_fetch("SELECT id,nickname FROM ".tablename('fans')." WHERE from_user ='{$this->message['from']}'  LIMIT 1");
		$data=array('uid'=>$user['id'],'lastvisit'=>$lastvisit,'type'=>'sns','weid'=>$_W['weid']);
		/*
		$zt = pdo_fetch("SELECT lastvisit FROM ".tablename('fans_status')." WHERE uid ={$user['id']}  LIMIT 1");
		if(!$zt){
		pdo_insert('fans_status', $data);
		}
		else
		{
			unset($data['uid']);
			pdo_update('fans_status', $data,array('uid'=>$user['id']));
		}
		*/
		$title = pdo_fetchcolumn("SELECT name FROM ".tablename('rule')." WHERE id = :rid LIMIT 1", array(':rid' => $rid));
		$url=create_url('mobile/module/list', array('name' => 'sns', 'type'=>$row['type'], 'id' => $rid, 'weid'=>$_W['weid']));
		$news = array();
		$news[] = array(
			'title' => $title,
			'description' => $row['description'],
			'picurl' => $_W['attachurl'] . $row['picture'],
			'url' => $url,
			
		);
		return $this->respNews($news);
		
		//print_r($user['nickname']);exit;
		//return $this->respText($user['nickname'].'<a href="'.$url.'">点此进入微吧，进行互动交流吧</a>');
		
	}
	
	public function isNeedSaveContext() {
		return false;
	}
}
