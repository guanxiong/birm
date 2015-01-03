<?php
/**
 * 摇一摇抽奖模块
 *
 * [WNS]更多模块请浏览：BBS.birm.co
 */
defined('IN_IA') or exit('Access Denied');

class YyyonlineModule extends WeModule {
	public $tablename = 'yyyonline_reply';

	public function fieldsFormDisplay($rid = 0) {
		global $_W;
		if (!empty($rid)) {
			$reply = pdo_fetch("SELECT * FROM ".tablename($this->tablename)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));
			
		} else {
			$reply = array(
				'shaketimes' => 60,
				'shakespace' => 100,
				'shakestrong' => 3000,
				'ruletype'=>1,
				'clienttime'=>3,
				'shaketype'=>0,
			);
		}
		if(intval($reply['shaketype'])>2){
			$reply['shaketype']=0;
			
		}
		if(intval($reply['clienttime'])>10 || intval($reply['clienttime'])<2){
			$reply['clienttime']=3;
			
		}
		if(intval($reply['starttime'])==0){
			$reply['starttime']=time();
			
		}
		
		if(intval($reply['endtime'])<intval($reply['starttime'])){
			$reply['endtime']=intval($reply['starttime']) + 900;
			
		}
		if(intval($reply['shaketimes'])<5){
			$reply['shaketimes']=60;
			
		}
		if(intval($reply['shakespace'])<10){
			$reply['shakespace']=100;
			
		}
		if(intval($reply['shakestrong'])<10){
			$reply['shakestrong']=3000;
			
		}
		if(intval($reply['ruletype'])>0){
			$reply['ruletype']=1;
			
		}
		
		include $this->template('form');
	}

	public function fieldsFormValidate($rid = 0) {
		return true;
	}

	public function fieldsFormSubmit($rid = 0) {
		global $_GPC, $_W;
		$id = intval($_GPC['reply_id']);
		$insert = array(
			'rid' => $rid,
			'picture' => $_GPC['picture'],
			'clientpic' => $_GPC['clientpic'],
			'description' => $_GPC['description'],
			'shaketimes' => intval($_GPC['shaketimes']),
			'shakespace' => intval($_GPC['shakespace']),
			'shakestrong' => intval($_GPC['shakestrong']),
			'ruletype'=>intval($_GPC['ruletype']),
			'endtime' => strtotime($_GPC['endtime']),
			'starttime' => strtotime($_GPC['starttime']),
			'clienttime'=>intval($_GPC['clienttime']),
			'shaketype'=>intval($_GPC['shaketype']),
			'rule' => htmlspecialchars_decode($_GPC['rule']),
			
		);
		//var_dump($insert);
		//exit;
		if (empty($id)) {
			pdo_insert($this->tablename, $insert);
		} else {
			
			pdo_update($this->tablename, $insert, array('id' => $id));
		}
		
		
	}
	public function ruleDeleted($rid = 0) {
		global $_W;
		$replies = pdo_fetchall("SELECT id, picture FROM ".tablename($this->tablename)." WHERE rid = '$rid'");
		$deleteid = array();
		if (!empty($replies)) {
			foreach ($replies as $index => $row) {
				file_delete($row['picture']);
				
				$deleteid[] = $row['id'];
			}
		}
		pdo_delete($this->tablename, "id IN ('".implode("','", $deleteid)."')");
		return true;
	}
}
