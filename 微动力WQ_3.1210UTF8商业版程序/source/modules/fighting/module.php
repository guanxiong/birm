<?php
/**
 * 一战到底模块定义
 *
 * @author 珊瑚海
 * @url http://bbs.we7.cc/forum.php?mod=forumdisplay&fid=36
 */
defined('IN_IA') or exit('Access Denied');

class FightingModule extends WeModule {

	public $tablename = 'fighting_setting';

	public function fieldsFormDisplay($rid = 0) {
		global $_W;
      	if (!empty($rid)) {
			$reply = pdo_fetch("SELECT * FROM ".tablename($this->tablename)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));		
 		} 
		include $this->template('form');
	}
  	public function fieldsFormValidate($rid = 0) {
		
        return true;
	}
  
  	public function fieldsFormSubmit($rid = 0) {
		global $_GPC, $_W;
 
        $id = intval($_GPC['reply_id']);
        //echo $id;
		$insert = array(
			'rid' => $rid,
			'title' => $_GPC['title'],
			'description' => $_GPC['description'],
			'qnum' => $_GPC['qnum'],
			'tgkf' =>$_GPC['tgkf'],
			'tiao' =>$_GPC['tiao'],
			'status_fighting' =>$_GPC['status_fighting'],
			'answertime' =>$_GPC['answertime'],
			'start' =>strtotime($_GPC['start']),
			'end' =>strtotime($_GPC['end']),
			'reply1' =>$_GPC['reply1'],
			'reply2' =>$_GPC['reply2'],
			'reply3' =>$_GPC['reply3'],
			'reply4' =>$_GPC['reply4'],
			'reply5' =>$_GPC['reply5'],
		);
		//print_r($insert);
		if (empty($id)) {
			$id=pdo_insert($this->tablename, $insert);
		} else {
			pdo_update($this->tablename, $insert, array('id' => $id));
		}
      	return true;
	}
   	public function ruleDeleted($rid = 0) {
		global $_W;
		$replies = pdo_fetchall("SELECT id,rid FROM ".tablename($this->tablename)." WHERE rid = '$rid'");
		$deleteid = array();
		if (!empty($replies)) {
          	foreach ($replies as $index => $row) {
				$deleteid[] = $row['id'];
			}     
		}
		pdo_delete($this->tablename, "id IN ('".implode("','", $deleteid)."')");
		return true;
	}
	
}