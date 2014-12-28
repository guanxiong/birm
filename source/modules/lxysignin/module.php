<?php
	/**
	 * 签到活动
	 *
	 * @author 大路货
	 * @url 
	 */
	defined('IN_IA') or exit('Access Denied');

	class LxysigninModule extends WeModule {
		public $tablename = 'lxy_signin_reply';		
		public function fieldsFormDisplay($rid = 0) {
			global $_W;
			if (!empty($rid)) {
				$reply = pdo_fetch("SELECT * FROM ".tablename($this->tablename)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));
			}
				$reply['start_time'] = empty($reply['start_time']) ? strtotime(date('Y-m-d')) : $reply['start_time'];
				$reply['end_time'] = empty($reply['end_time']) ? TIMESTAMP : $reply['end_time'] + 86399;

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
			'overtime' => $_GPC['overtime'],
			'overnum' => $_GPC['overnum'],
			'awardrules' => $_GPC['awardrules'],
			'awardinfo' => $_GPC['awardinfo'],
			'days'=>$_GPC['days'],
			);
			if (empty($id)) {
			pdo_insert($this->tablename, $insert);
			}
			else {
			pdo_update($this->tablename, $insert, array('id' => $id));
			}
		}
		public function ruleDeleted($rid = 0) {
			pdo_delete($this->tablename, array('rid' => $rid));
		}
		public function settingsDisplay($settings) {
			global $_GPC, $_W;
			if(checksubmit()) {
				$cfg = array(
					'times' => intval($_GPC['times']),
					'start_time' => $_GPC['start_time'],
					'end_time' => $_GPC['end_time'],
					'credit' => intval($_GPC['credit']),
					'rank' => intval($_GPC['rank']),
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
			if(!isset($settings['times'])) {
				$settings['times'] = '1';
			}
			if(!isset($settings['start_time'])) {
				$settings['start_time'] = '08:30';
			}
			if(!isset($settings['end_time'])) {
				$settings['end_time'] = '22:00';
			}
			if(!isset($settings['credit'])) {
				$settings['credit'] = '1';
			}
			if(!isset($settings['rank'])) {
				$settings['rank'] = '10';
			}
				
			include $this->template('setting');
		}
	}
