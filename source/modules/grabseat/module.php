<?php
/*
 *  凑一对模块 
 *
 *  [艮随] Copyright (c)
 */
defined('IN_IA') or exit('Access Denied');

class GrabseatModule extends WeModule {

	public function fieldsFormDisplay($rid = 0) {

		global $_W;
		
		if (!empty($rid)) {
		
			$reply = pdo_fetch("SELECT * FROM ".tablename('grabseat_reply')." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));				
 		} 
		
		$reply['start_time'] = empty($reply['start_time']) ? strtotime(date('Y-m-d')) : $reply['start_time'];
		
		$reply['end_time'] = empty($reply['end_time']) ? TIMESTAMP : $reply['end_time'] + 86399;
		
		include $this->template('form');
		
	}

	public function fieldsFormValidate($rid = 0) {

		return '';
		
	}

	public function fieldsFormSubmit($rid) {

		global $_GPC, $_W;
		
		$id = intval($_GPC['reply_id']);
		
		$insert = array(
		
			'rid' => $rid,
			
            'title' => $_GPC['title'],
			
			'picture' => $_GPC['picture'],
			
			'description' => $_GPC['description'],
			
			'content' => htmlspecialchars_decode($_GPC['content']),
			
			'showcontent' => htmlspecialchars_decode($_GPC['showcontent']),
			
			'fitcontent' => htmlspecialchars_decode($_GPC['fitcontent']),
			
		);
		
		if (empty($id)) {
		
			pdo_insert('grabseat_reply', $insert);
			
		} 
		else {
		
			if (!empty($_GPC['picture'])) {
			
				file_delete($_GPC['picture-old']);
				
			} 
			else {
			
				unset($insert['picture']);
				
			}

			pdo_update('grabseat_reply', $insert, array('id' => $id));
			
		}

	}

	public function ruleDeleted($rid) {

		global $_W;
		
		$replies = pdo_fetchall("SELECT id, picture FROM ".tablename('grabseat_reply')." WHERE rid = '$rid'");
		
		$deleteid = array();
		
		if (!empty($replies)) {
		
			foreach ($replies as $index => $row) {
			
				file_delete($row['picture']);
				
				$deleteid[] = $row['id'];
				
			}
			
		}
		
		pdo_delete('grabseat_reply', "id IN ('".implode("','", $deleteid)."')");
		
		return true;
		
	}
	
	public function settingsDisplay($settings) {

		global $_GPC, $_W;

		if(checksubmit()) {

			$cfg = array(

				'tablenum' => intval($_GPC['tablenum']),

				'start_time' => $_GPC['start_time'],

				'end_time' => $_GPC['end_time'],

			);

				$start_time = $cfg['start_time'];

				$start_time = strtotime($start_time);

				$end_time = $cfg['end_time'];

				$end_time = strtotime($end_time);

			if($start_time >= $end_time){

				message('开始时间不得晚于结束时间', 'refresh', 'error');

			}

			elseif($this->saveSettings($cfg)) {

				message('保存成功', 'refresh');

			}

		}

		if(!isset($settings['tablenum'])) {

			$settings['tablenum'] = '50';

		}

		if(!isset($settings['start_time'])) {

			$settings['start_time'] = '2014-02-14 17:21';

		}
		
		if(!isset($settings['end_time'])) {

			$settings['end_time'] = '2014-02-15 00:00';

		}

		include $this->template('setting');

	}

}