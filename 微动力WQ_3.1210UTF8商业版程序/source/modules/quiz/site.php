<?php
/**
 * 随堂测验模块处理程序
 *
 * @author daduing
 * @url http://www.we7.cc
 */
defined('IN_IA') or exit('Access Denied');

class QuizModuleSite extends WeModuleSite {
	public $tablename = 'quiz';
	public $tablename_log = 'quiz_log';
	public $tablename_question = 'quiz_question';

	public function doWebQuery() {
		global $_W, $_GPC;
		$kwd = $_GPC['keyword'];
		$sql = 'SELECT * FROM ' . tablename($this->tablename_question) . ' WHERE `question` LIKE :question';
		$params = array();
		//$params[':weid'] = $_W['weid'];
		$params[':question'] = "%{$kwd}%";
		$ds = pdo_fetchall($sql, $params);
		foreach($ds as &$row) {
			$r = array();
			$r['qid'] = $row['id'];
			$r['question'] = $row['question'];
			$r['config'] = $row['config'];
			$r['answer'] = $row['answer'];
			$row['entry'] = $r;
		}
		include $this->template('query');
	}

	public function doWebshow(){
		$this->doMobileshow();
	}

	public function doMobileshow(){
		global $_W, $_GPC;
		$rid = $_GPC['id'];
		$sql = "SELECT * FROM " . tablename($this->tablename_log) . " WHERE `rid`=:rid";
		$info = pdo_fetchall($sql, array(':rid' => $rid));
		$sql = "SELECT * FROM " . tablename($this->tablename) . " WHERE `rid`=:rid LIMIT 1";
		$quiz = pdo_fetch($sql, array(':rid' => $rid));
		$sql = "SELECT * FROM " . tablename($this->tablename_question) . " WHERE `id`=:id";
		$q = pdo_fetch($sql, array(':id' => $quiz['qid']));
		//var_dump($q);
		$arr = array();
		foreach($info as $key => $value){
			$arr[] = $info[$key]['chk_answer'];
		}
		$per = array_count_values($arr);
		ksort($per);
		//var_dump($per);
		$total_count = sizeof($info);
		$config = $this->get_config($q);
		//foreach($per as $key => $value){
			//$str .= '选项'.$key.'次数'.$value;

		//}
		//var_dump($str);
		//var_dump($per);
		include $this->template('show');
	}
	public function get_config($question){
		//单选题 多选题 判断题
		$result = '';
		if($question['type']==1 || $question['type']==2 || $question['type']==3){
			$detail=explode("\r\n",$question['config']);
			foreach( $detail AS $key=>$value){
				if($value===''){
					continue;
				}
				$result .= ($key+1).'、'.$value."\n";
			}			
		}
		//填空 排序
		elseif($question['type']==4 || $question['type']==5){
			$result = $question['config'];
		}
		//其它题目无选项
		else{
			$result = '';
		}
		return $result;
	}
}
