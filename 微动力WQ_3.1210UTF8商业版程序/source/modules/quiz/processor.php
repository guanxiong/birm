<?php
/**
 * 随堂测验模块处理程序
 *
 * @author daduing
 * @url http://www.we7.cc
 */
defined('IN_IA') or exit('Access Denied');

class QuizModuleProcessor extends WeModuleProcessor {
	public $tablename = 'quiz';
	public $tablename_log = 'quiz_log';
	public $tablename_question = 'quiz_question';
	public $picurl = 'bt.jpg';
	public function respond() {
		global $_W;
		$rid = $this->rule;
		$content = trim($this->message['content']);
		$from_user = $this->message['from'];
		$info = fans_search($from_user);
		if(!$this->inContext) {//无上下文
			//将参与者写入表
			$sql = "SELECT * FROM " . tablename($this->tablename_log) . " WHERE `rid`=:rid AND `fid`=:fid LIMIT 1";
			$f = pdo_fetch($sql, array(':rid' => $rid,':fid' => $info['id']));
			if($f['chk_answer'])return $this->respText("对不起，你已参加过答题。<a href='".$this->createMobileUrl('show', array('id' => $rid))."'>点击查看详情</a>。");
			$sql = "SELECT * FROM " . tablename($this->tablename) . " WHERE `rid`=:rid LIMIT 1";
			$row = pdo_fetch($sql, array(':rid' => $rid));
			isset($_SESSION['q'])?$q = $_SESSION['q']:$_SESSION['q'] = $q = $this->question($row['qid']);
			$pre_txt = "回复数字选择答案，0退出\n======";
			$qtxt = $this->get_question($q);
			$q_config = $this->get_config($q);

			$this->beginContext();
			return $this->respText($pre_txt."\n".$qtxt."\n".$q_config);		
		}
		//上下文
		if($content == '0'){
			$this->endContext();
			session_destroy();
			return $this->respText("感谢参与，您已回到普通模式！\n回复 ? 获得帮助。");
		}

		//增加0选项
		$q_config_count = $this->get_config_count($_SESSION['q']);
		$q_config_count[] = '0';
		if(!in_array($content,$q_config_count))return $this->respText('错误的选项，请按提示输入。');
		//选择后正式进入
		pdo_insert($this->tablename_log,array('fid' => $info['id'],'rid' => $rid));
		pdo_run("UPDATE ".tablename($this->tablename_log)." SET `chk_answer`=".$content." WHERE `fid`=".$info['id']." AND `rid`=".$rid);
		if($content == $this->get_answer($_SESSION['q'])){
			$title = "回答正确！";
		}else{
			$title = "回答错误！";
		}
		$this->endContext();
		session_destroy();
		return $this->respText($title."<a href='".$this->createMobileUrl('show', array('id' => $rid))."'>点击查看详情</a>。");
	}
	public function question($id =0){
		//按id取问题
		$sql_question = "select * from ".tablename($this->tablename_question)." WHERE `id`=".$id." limit 1";
		$question = pdo_fetch($sql_question);
		return $question;
	}
	public function get_question($question){
		return $question['question'];
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
	public function get_config_count($question){
		//单选题 多选题 判断题
		$result = array();
		if($question['type']==1 || $question['type']==2 || $question['type']==3){
			$detail=explode("\r\n",$question['config']);
			foreach( $detail AS $key=>$value){
				if($value===''){
					continue;
				}
				$result[$key] = ($key + 1).'';
				//$result .= ($key+1).'、'.$value."\n";
			}			
		}
		//填空 排序
		elseif($question['type']==4 || $question['type']==5){
			$result = array('1');
		}
		//其它题目无选项
		else{
			$result = array();
		}
		return $result;
	}
	public function get_answer($question){
		//单选题
		//判断题
		if($question['type']==1 || $question[type]==3){
			$answer = ord(strtolower($question['answer']))-ord('a')+1;
		}
		//多选题
		elseif($question[type]==2){
			/*$detail=explode("\r\n",$question['answer']);
			foreach( $detail AS $key=>$value){
				if($value===''){
					continue;
				}
				$black=strlen($value)>20?'<br>':'&nbsp;&nbsp;&nbsp;';
				$ckk=in_array(chr(97+$key),$_detail)?" checked ":" ";
			}*/
		}
		//填空题
		elseif($question[type]==4){
		//排序题
		}elseif($question[type]==5){
		//简答题
		}elseif($question[type]==7){
		//问答题,作文题
		}elseif($question[type]==8||$question[type]==9){
		//填空题
		}else{
		}
		return $answer;
	}
}