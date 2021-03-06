<?php
/**
 * 中秋博饼模块定义
 *
 * @author Meepo
 * @url http://meepo.com.cn
 */
defined('IN_IA') or exit('Access Denied');

class MgamblemoonModule extends WeModule {
	public $tablename = 'mgamblemoon_reply';
	
	public function fieldsFormDisplay($rid = 0) {
		global $_W;
		if (!empty($rid)) {
			$reply = pdo_fetch("SELECT * FROM ".tablename($this->tablename)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));
			
		} else {
			$reply = array(
				'periodlottery' => 1,
				'maxlottery' => 2,
				'start_time' => TIMESTAMP,
				'end_time' => TIMESTAMP+3600*24*7,
			);
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
			'title' => $_GPC['title'],
			'headurl' => $_GPC['headurl'],
			'panzi' => $_GPC['panzi'],
			'prace_times' => intval($_GPC['prace_times']),
			'description' => $_GPC['description'],
			'periodlottery' => 1,
			'maxlottery' => intval($_GPC['maxlottery']),
			'rule' => htmlspecialchars_decode($_GPC['rule']),
			'headpic' => $_GPC['headpic'],
			'guzhuurl' => $_GPC['guzhuurl'],
			'start_time'=>strtotime($_GPC['start_time']),
			'end_time'=>strtotime($_GPC['end_time']),
			
		);
		if(intval($insert['maxlottery'])<2){
			$insert['maxlottery']=2;
		
		}
		if (empty($id)) {
			pdo_insert($this->tablename, $insert);
		} else {
			if (!empty($_GPC['picture'])) {
				file_delete($_GPC['picture-old']);
			} else {
				unset($insert['picture']);
			}
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