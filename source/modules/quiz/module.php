<?php
/**
 * 随堂测验模块定义
 *
 * @author daduing
 * @url http://www.we7.cc
 */
defined('IN_IA') or exit('Access Denied');

class QuizModule extends WeModule {

	public $tablename = 'quiz';
	public $table_question = 'quiz_question';

	public function fieldsFormDisplay($rid = 0) {
		global $_W;
      	if (!empty($rid)) {

			$reply = pdo_fetch("SELECT a.*,b.id AS qid,b.question,b.config,b.answer FROM ".tablename($this->tablename)." AS a INNER JOIN ".tablename($this->table_question)." AS b ON a.qid=b.id WHERE rid = :rid", array(':rid' => $rid));		
 		} 
		include $this->template('form');
	}
  	public function fieldsFormValidate($rid = 0) {
		global $_GPC, $_W;
		if(!isset($_GPC['qid']))return '最少选择一个题目！';
	}
  
  	public function fieldsFormSubmit($rid = 0) {
		global $_GPC, $_W;
 
        $id = intval($_GPC['reply_id']);
        //echo $id;
		$insert = array(
			'weid' => $_W['weid'],
			'rid' => $rid,
			'qid' => $_GPC['qid'],
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
		$replies = pdo_fetchall("SELECT id,rid FROM ".tablename('quiz_log')." WHERE rid = '$rid'");
		$deleteid = array();
		if (!empty($replies)) {
          	foreach ($replies as $index => $row) {
				$deleteid[] = $row['id'];
			}     
		}
		pdo_delete('quiz_log', "id IN ('".implode("','", $deleteid)."')");
		pdo_delete($this->tablename, "rid =".$rid.""); 
		return true;
	}
}