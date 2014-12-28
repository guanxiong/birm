<?php
	/**
	 * 签到活动
	 *
	 * @author 福州程序员
	 * @url 
	 */
	defined('IN_IA') or exit('Access Denied');

	class CgtsigninModule extends WeModule {
		public $tablename = 'cgt_signin_reply';		
		public function fieldsFormDisplay($rid = 0) {
			global $_W;
			if (!empty($rid)) {
				$reply = pdo_fetch("SELECT * FROM ".tablename($this->tablename)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));
			}
		   $reply['start_time'] = empty($reply['start_time']) ? date('Y-m-d') : date("Y-m-d",$reply['start_time']);
		    $reply['end_time'] = empty($reply['end_time']) ?  date("Y-m-d",time()+ 86400) :  date("Y-m-d",$reply['end_time']);

			include $this->template('form');
		}
		
		public function fieldsFormValidate($rid = 0) {
			return true;
		}

		public function fieldsFormSubmit($rid = 0) {
           global $_GPC, $_W;
			$id = intval($_GPC['reply_id']);
			$credit= intval($_GPC['credit']);
			$insert = array(
			'rid' => $rid,
			'start_time' =>strtotime($_GPC['start_time']),
			'end_time' => strtotime($_GPC['end_time']),
			'awardrules' => $_GPC['awardrules'],
			'awardinfo' => $_GPC['awardinfo'],
			'days'=>$_GPC['days'],
			'credit'=>$credit
			);
			
		   if (!empty($_FILES['thumb']['tmp_name'])) {
					file_delete($_GPC['thumb_old']);
			
					$upload = file_upload($_FILES['thumb']);
					if (is_error($upload)) {
						message($upload['message'], '', 'error');
					}
					$insert['thumb'] = $upload['path'];
				}
			else{
			  $tmp_file=array(name=>"registration_top.jpg",
			  tmp_name=>"{$_SERVER['DOCUMENT_ROOT']}/source/modules/cgtsignin/template/style/images/registration_top.jpg");	
		
		     $upload = file_upload($tmp_file);
			if (is_error($upload)) {
						message($upload['message'], '', 'error');
					}
			  $insert['thumb'] = $upload['path'];
			}
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
	}
