<?php
/**
 * 一战到底模块处理程序
 *
 * @author 珊瑚海
 * @url http://bbs.we7.cc/forum.php?mod=forumdisplay&fid=36
 */
defined('IN_IA') or exit('Access Denied');

class FightingModuleProcessor extends WeModuleProcessor {
	public function respond() {
		global $_W;
		$rid = $this->rule;
		$sql = "SELECT * FROM " . tablename('fighting_reply') . " WHERE `rid`=:rid LIMIT 1";
		$row = pdo_fetch($sql, array(':rid' => $rid));
		//print_r($row);
		$content = trim($this->message['content']);
		$from_user = $this->message['from'];
		$year=((int)date('Y',time()));//取得年份
		$month=((int)date('m',time()));//取得月份
		$day=((int)date('d',time()));//取得几号
		function getNickName($from_user){
			$sql_infos = "SELECT * FROM " . tablename('fans') . " WHERE `from_user`=:from_user LIMIT 1";
			$infos = pdo_fetch($sql_info, array(':from_user' => $from_user));
			return $infos['nickname'];
		}
		function getCredit($from_user){
			$sql_infos = "SELECT * FROM " . tablename('fans') . " WHERE `from_user`=:from_user LIMIT 1";
			$infos = pdo_fetch($sql_info, array(':from_user' => $from_user));
			return $infos['credit1'];
		}
		//print_r($info);
		$start = ((int)mktime(0,0,0,$month,$day,$year));
		if(!$this->inContext) {
			$response['FromUserName'] = $this->message['to'];
			$response['ToUserName'] = $this->message['from'];
			$sql_info = "SELECT * FROM " . tablename('fans') . " WHERE `from_user`=:from_user LIMIT 1";
			$info = pdo_fetch($sql_info, array(':from_user' => $from_user));
			$sql_fighting = "SELECT * FROM " . tablename('fighting_fans') . " WHERE `from_user`=:from_user AND `fid`=".$row['id']." ORDER BY id DESC LIMIT 1";
			$sql_fighting = pdo_fetch($sql_fighting, array(':from_user' => $from_user));
			if (!$info['nickname']) {//判断是否绑定
				return $this->respText($row['reply1']."<a target=\"_blank\" href=\"mobile.php?act=module&do=profile&name=fans&weid={$_W['weid']}\">点击此处登记资料</a>，然后重新回复".$row['keyword']."开始答题");
			}elseif (time()<$row['starttime']) {//判断活动是否已经开始
				$row['reply2'] = str_replace('{nickname}', $info['nickname'] , $row['reply2']);
				$row['reply2'] = str_replace('{credit}', $info['credit1'] , $row['reply2']);
				$row['reply2'] = str_replace('{title}', $row['title'] , $row['reply2']);
				$row['reply2'] = str_replace('{num}', $row['qnum'] , $row['reply2']);
				$row['reply2'] = str_replace('{title}', $row['title'] , $row['reply2']);
				$row['reply2'] = str_replace('{keyword}', $row['keyword'] , $row['reply2']);
				$row['reply2'] = str_replace('{description}', $row['description'] , $row['reply2']);
				$row['reply2'] = str_replace('{tgkf}', $row['tgkf'] , $row['reply2']);
				return $this->respText($row['reply2']);
			}elseif (time()>$row['endtime']) {//判断活动是否已经结束
				$row['reply3'] = str_replace('{nickname}', $info['nickname'] , $row['reply3']);
				$row['reply3'] = str_replace('{credit}', $info['credit1'] , $row['reply3']);
				$row['reply3'] = str_replace('{title}', $row['title'] , $row['reply3']);
				$row['reply3'] = str_replace('{keyword}', $row['keyword'] , $row['reply3']);
				$row['reply3'] = str_replace('{description}', $row['description'] , $row['reply3']);
				$row['reply3'] = str_replace('{num}', $row['qnum'] , $row['reply3']);
				$row['reply3'] = str_replace('{tgkf}', $row['tgkf'] , $row['reply3']);
				return $this->respText($row['reply3']);
			}elseif ($row['isshow'] == '0') {//判断活动是否已经暂停
				$row['reply5'] = str_replace('{nickname}', $info['nickname'] , $row['reply5']);
				$row['reply5'] = str_replace('{credit}', $info['credit1'] , $row['reply5']);
				$row['reply5'] = str_replace('{title}', $row['title'] , $row['reply5']);
				$row['reply5'] = str_replace('{keyword}', $row['keyword'] , $row['reply5']);
				$row['reply5'] = str_replace('{description}', $row['description'] , $row['reply5']);
				$row['reply5'] = str_replace('{num}', $row['qnum'] , $row['reply5']);
				$row['reply5'] = str_replace('{tgkf}', $row['tgkf'] , $row['reply5']);
				return $this->respText($row['reply5']);
			}elseif($sql_fighting['last_time']>=$start){//判断是否已经答过题
				$list = pdo_fetchall("SELECT * FROM ".tablename('fighting_fans')." WHERE `last_time`>=".$start." AND `fid`=".$row['id']." ORDER BY last_credit DESC LIMIT 5");//获取日排名
				foreach ($list as $key => $value) {
					$k=$key+1;
					$ripaiming .= "\n{$k}、{$value['nickname']} 得分：{$value['last_credit']}\n";
				}
				$response['MsgType'] = 'text';
				$response['Content'] = "您今天在".date('H:i:s',$sql_fighting['last_time'])."已经答过题啦，明天再来吧！".$ripaiming;
				return $response;
			}else{
				$row['reply4'] = str_replace('{nickname}', $info['nickname'] , $row['reply4']);
				$row['reply4'] = str_replace('{credit}', $info['credit1'] , $row['reply4']);
				$row['reply4'] = str_replace('{title}', $row['title'] , $row['reply4']);
				$row['reply4'] = str_replace('{keyword}', $row['keyword'] , $row['reply4']);
				$row['reply4'] = str_replace('{description}', $row['description'] , $row['reply4']);
				$row['reply4'] = str_replace('{num}', $row['qnum'] , $row['reply4']);
				$row['reply4'] = str_replace('{tgkf}', $row['tgkf'] , $row['reply4']);
				pdo_update('fighting_reply', array('viewnum'=>$row['viewnum']+1),array('id'=>$row['id']));
				$this->beginContext(1800);
				$_SESSION['qutype'] = $row['qutype'];
				$_SESSION['rid']=$row['rid'];
				$_SESSION['fid']=$row['id'];
				$_SESSION['qnum']=$row['qnum'];
				$_SESSION['qnum_2']=$row['qnum']+1;
				$_SESSION['tiao']=$row['tiao'];
				$_SESSION['tgkf']=$row['tgkf'];
				$_SESSION['answertime']=$row['answertime'];
				$_SESSION['ad'] = $row['ad'];
				$_SESSION['isad'] = $row['isad'];
        		$_SESSION['num']=1;
        		$_SESSION['creditnew'] = 0;
        		$_SESSION['right']=0;
        		$_SESSION['wrong']=0;
        		$_SESSION['tiao']=0;
        		$_SESSION['fansID']=$info['id'];
        		$_SESSION['credit']=$info['credit1'];
        		$_SESSION['nickname']=$info['nickname'];
        		$_SESSION['fansnum'] = $row['fansnum'];
        		return $this->respText($row['reply4']);			
			}
		}else{
			$response = array();
        	$response['FromUserName'] = $this->message['to'];
        	$response['ToUserName'] = $this->message['from'];
			if ( $content == '退出') {
        		$response['MsgType'] = 'text';
        		$response['Content'] = "您已回到普通模式！";
        		$this->endContext();
        		session_destroy();
        		return $response;
        	}else{
        		function requireQuestions(){
        			global $_W;
        			if ($_SESSION['qutype'] == '1') {
        				$sql_question = "SELECT * FROM ".tablename('fighting_question_bank')." AS t1 JOIN (SELECT ROUND(RAND() * ((SELECT MAX(id) FROM ".tablename('fighting_question_bank').")-(SELECT MIN(id) FROM ".tablename('fighting_question_bank')."))+(SELECT MIN(id) FROM ".tablename('fighting_question_bank').")) AS id) AS t2 WHERE t1.id >= t2.id AND weid = {$_W['weid']} ORDER BY t1.id LIMIT 1";
        			}else{
        				$sql_question = "SELECT * FROM ".tablename('fighting_question_bank')." AS t1 JOIN (SELECT ROUND(RAND() * ((SELECT MAX(id) FROM ".tablename('fighting_question_bank').")-(SELECT MIN(id) FROM ".tablename('fighting_question_bank')."))+(SELECT MIN(id) FROM ".tablename('fighting_question_bank').")) AS id) AS t2 WHERE t1.id >= t2.id ORDER BY t1.id LIMIT 1";
        			}
        			$question = pdo_fetch($sql_question);
        			//print_r($question);
        			switch($question[option_num]){
        				case '2':
        				$str="问题：\n{$question[question]}\n选项：\nA、{$question[optionA]} B、{$question[optionB]}";
						break;
						case '3':
						$str="问题：\n{$question[question]}\n选项：\nA、{$question[optionA]} B、{$question[optionB]} C、{$question[optionC]}";
						break;
						case '4':
						$str="问题：\n{$question[question]}\n选项：\nA、{$question[optionA]} B、{$question[optionB]} C、{$question[optionC]} D、{$question[optionD]}";
						break;
						case '5':
						$str="问题：\n{$question[question]}\n选项：\nA、{$question[optionA]} B、{$question[optionB]} C、{$question[optionC]} D、{$question[optionD]} E、{$question[optionE]}";
						break;
						case '6':
						$str="问题：\n{$question[question]}\n选项：\nA、{$question[optionA]} B、{$question[optionB]} C、{$question[optionC]} D、{$question[optionD]} E、{$question[optionE]} F、{$question[optionF]}";
						break;
					}
					//获取答案
					$an_arr=str_split($question[answer]);
					for($i=0;$i<$question[option_num];$i++){
						if($an_arr[$i]==1){
							switch($i){
								case '0':
								$an.=A;
								break;
								case '1':
								$an.=B;
								break;
								case '2':
								$an.=C;
								break;
								case '3':
								$an.=D;
								break;
								case '4':
								$an.=E;
								break;
								case '5':
								$an.=F;
								break;
							}
						}
					}
					if ($_SESSION['isad'] == '1') {
						$result['question'] = "分值为【".$question['figure']."】分的".$str."\n———————————-——\n回答请输入 X 如 A 或 ACD ！离开请输入 退出 题目原始id为<".$question[id].">如有错误请反馈给本平台，谢谢\n".$_SESSION['ad'];
					}else{
						$result['question'] = "分值为【".$question['figure']."】分的".$str."\n———————————-——\n回答请输入 X 如 A 或 ACD ！离开请输入 退出 题目原始id为<".$question[id].">如有错误请反馈给本平台，谢谢";
					}
					$result['answer'] = $an;
					$result['figure'] = $question['figure'];
					return $result;
				}
				$result = requireQuestions();
        		//print_r($result);
				if ($_SESSION['num']==1) {
					$response['MsgType'] = 'text';
					$response['Content'] = $_SESSION['num']."、".$result['question'];
					$_SESSION['num']++;
					$_SESSION['rightanswer'] = $result['answer'];
					$_SESSION['figure'] = $result['figure'];
					return $response;
				}elseif(($_SESSION['num']>=1)&&($_SESSION['num']<=$_SESSION['qnum'])&&($content!="跳过")){
					$keyword = str_replace(' ', '', $content);
					$keyword = str_replace('答案', '', $keyword);
					$keyword = strtoupper($keyword);
					if ($keyword == $_SESSION['rightanswer']) {
						$_SESSION['right']++;
						$info =  "回答正确，加".$_SESSION['figure']."分！\n下一题：\n";
						$_SESSION['creditnew'] = $_SESSION['creditnew']+$_SESSION['figure'];
					}else{
						$info =  "回答错误，正确答案为".$_SESSION['rightanswer']."\n下一题：\n";
						$_SESSION['wrong']++;
					}
					$response['MsgType'] = 'text';
					$response['Content'] = $info.$_SESSION['num']."、".$result['question'];
					$_SESSION['num']++;
					$_SESSION['rightanswer'] = $result['answer'];
					$_SESSION['figure'] = $result['figure'];
					return $response;
				}elseif (($content=="跳过")&&($_SESSION['tiao']) == 1) {
					$info = "您已经跳过上一题，本次跳过扣除积分【".$_SESSION['tgkf']."】！";
					$response['MsgType'] = 'text';
					$_SESSION['num']--;
					$_SESSION['tiao']++;
					$response['Content'] = $info.$_SESSION['num']."、".$result['question'];
					$_SESSION['num']++;
					$_SESSION['rightanswer'] = $result['answer'];
					$_SESSION['figure'] = $result['figure'];
					$_SESSION['creditnew'] = $_SESSION['creditnew'] - $_SESSION['tgkf'];
					return $response;
				}elseif (($content=="跳过")&&($_SESSION['tiao']) == 0) {
					$info = "您好，本次活动不允许跳过，请继续回答上一题，谢谢";
					$response['MsgType'] = 'text';
					$response['Content'] = $info;
					return $response;
				}elseif($_SESSION['num']==$_SESSION['qnum_2']){
					$keyword = str_replace(' ', '', $content);
					$keyword = str_replace('答案', '', $keyword);
					$keyword = strtoupper($keyword);
					if ($keyword == $_SESSION['rightanswer']) {
						$_SESSION['right']++;
						$info =  "回答正确，加".$_SESSION['figure']."分！\n";
						$_SESSION['creditnew'] = $_SESSION['creditnew']+$_SESSION['figure'];
					}else{
						$info =  "回答错误，正确答案为".$_SESSION['rightanswer']."\n";
						$_SESSION['wrong']++;
						//print_r($_SESSION);
					}
					//print_r($_SESSION);
					$insert1 = array(
						'rid' => $_SESSION['rid'],
						'fid' => $_SESSION['fid'],
						'fansID'=>$_SESSION['fansID'],
						'from_user' => $from_user,
						'nickname' =>$_SESSION['nickname'],
						'todayannum'=>$_SESSION['right']+$_SESSION['wrong']+$_SESSION['tiao'],
						'rightannum'=>$_SESSION['right'],
						'wrongannum'=>$_SESSION['wrong'],
						'last_time' => time(),
						'last_credit' => $_SESSION['creditnew'],
						);
					$add = pdo_insert('fighting_fans', $insert1);
					$insert2 = array(
						'from_user' => $from_user,
						'credit1' => $_SESSION['credit']+$_SESSION['creditnew'],
						);
					$addjf = pdo_update('fans', $insert2, array('from_user' => $from_user));
					pdo_update('fighting_reply', array('fansnum'=>$_SESSION['fansnum']+1),array('id'=>$_SESSION['fid']));
					$_SESSION['num']++;
					$list = pdo_fetchall("SELECT * FROM ".tablename('fighting_fans')." WHERE `last_time`>=".$start." AND `fid`=".$_SESSION['fid']." ORDER BY last_credit DESC LIMIT 5");//获取日排名
					foreach ($list as $key => $value) {
						$k=$key+1;
						$ripaiming .= "\n{$k}、{$value['nickname']} 得分：{$value['last_credit']}\n";
					}
					$response['MsgType'] = 'text';
					$response['Content'] = $info."您已答完".$_SESSION['qnum']."道题，共获得积分".$_SESSION['creditnew']."\n答对".$_SESSION['right']."题，\n答错".$_SESSION['wrong']."题，\n跳过".$_SESSION['tiao']."题".$ripaiming;
					$this->endContext();
        			session_destroy();
					return $response;
				}else{
					$response['MsgType'] = 'text';
					$response['Content'] = "wrong...";
					return $response;
				}
			}
		}
	}
}