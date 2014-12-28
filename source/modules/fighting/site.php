<?php
/**

 */
defined('IN_IA') or exit('Access Denied');

class FightingModuleSite extends WeModuleSite {
	public $tablename = 'fighting_setting';
	public function getProfileTiles() {

	}

	public function getHomeTiles() {
	}

public function doWeblist() {
		global $_GPC;
		$from_user = authcode(base64_decode($_GPC['from_user']), 'DECODE');  
    	$user = pdo_fetch("SELECT * FROM ".tablename('fans')." WHERE from_user = '".$from_user."' and weid=".$_GET['weid']." limit 1" );
    	if($_GPC['action']=='setinfo'){
    		$insert = array(
    			'nickname' => $_GPC['nickname'],
				'realname' => $_GPC['realname'],
				'mobile' => $_GPC['mobile'],
				'qq' => $_GPC['qq'],       
				'from_user'=>$from_user,
          		'weid'=>$_GET['weid'],
          		'createtime'=>time(),
            	'avatar'=>' ',
			);
			if ($user==false) {
				$id=pdo_insert('fans', $insert);
			} else {
				pdo_update('fans', $insert, array('from_user' => $from_user,'weid'=>$_GET['weid']));
			}
			die(true);
		}
		$title = '会员资料';	
      	$loclurl=create_url('site/module', array('do' => 'list', 'name' => 'fighting', 'id' => $rid,'weid'=>$_GET['weid'], 'from_user' => $_GPC['from_user']));
   		include $this->template('index');
	}

	public function doWebDetail() {
		global $_GPC, $_W;
		checklogin();
		$id = intval($_GPC['id']);
		if (checksubmit('delete') && !empty($_GPC['select'])) {
			pdo_delete('fighting_question_bank', " id  IN  ('".implode("','", $_GPC['select'])."')");
			message('删除成功！', create_url('site/module', array('do' => 'detail', 'name' => 'fighting')));
		}
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;
		$list = pdo_fetchall("SELECT * FROM ".tablename('fighting_question_bank')." ORDER BY id ASC LIMIT ".($pindex - 1) * $psize.",{$psize}");
		//print_r($list);
		if (!empty($list)) {
			$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('fighting_question_bank') . "");
			$pager = pagination($total, $pindex, $psize);			
			unset($row);
		}
		function an($answer,$num){
			$an_arr=str_split($answer);
			for($i=0;$i<$num;$i++){
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
			return $an;
		}
		include $this->template('detail');
	}

	public function doWebedit(){
		global $_GPC, $_W;
		checklogin();
		$id = intval($_GPC['id']);
		$list = pdo_fetch("SELECT * FROM ".tablename('fighting_question_bank')." WHERE id = {$id}");
		function an($answer,$num){
			$an_arr=str_split($answer);
			for($i=0;$i<$num;$i++){
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
			return $an;
		}
		//unset($row);
		include $this->template('edit');
	}
	public function doWebeditok(){
		global $_GPC, $_W;
		checklogin();
		$id = intval($_GPC['id']);
		if ($_GPC['action'] = 'edit') {
			$answer = strtoupper($_GPC['answer']);
			if(strlen($answer)>1){
				$answers = str_split($answer);
				$an = '000000';
				$an_num = count($answers);
				for($i=0;$i<$an_num;$i++){
					if($answers[$i]==A){
						$an = substr_replace($an,1,0,1);
					}elseif ($answers[$i]==B) {
						$an = substr_replace($an,1,1,1);
					}elseif ($answers[$i]==C) {
						$an = substr_replace($an,1,2,1);
					}elseif ($answers[$i]==D) {
						$an = substr_replace($an,1,3,1);
					}elseif ($answers[$i]==E) {
						$an = substr_replace($an,1,4,1);
					}elseif ($answers[$i]==F) {
						$an = substr_replace($an,1,5,1);
					}else{
						$an = $an;
					}
				}
			}else{
				$answer = strtoupper($_GPC['answer']);
				if ($answer == 'A') {
					$an = '100000';
				}elseif ($answer == 'B') {
					$an = '010000';
				}elseif ($answer == 'C') {
					$an = '001000';
				}elseif ($answer == 'D') {
					$an = '000100';
				}elseif ($answer == 'E') {
					$an = '000010';
				}elseif ($answer == 'F') {
					$an = '000001';
				}else{
					$an = '000000';
				}
			}
			$insert = array(
				'figure' => $_GPC['figure'],
				'question' => $_GPC['question'],
				'option_num'=> $_GPC['option_num'],
				'optionA' => $_GPC['optionA'], 
				'optionB' => $_GPC['optionB'], 
				'optionC' => $_GPC['optionC'], 
				'optionD' => $_GPC['optionD'], 
				'optionE' => $_GPC['optionE'], 
				'optionF' => $_GPC['optionF'], 
				'answer' => $an, 
				'classify' => $_GPC['classify'],
			);
			//print_r($_GPC);
			pdo_update('fighting_question_bank', $insert,array('id' => $id));
			message('修改成功！', create_url('site/module', array('do' => 'detail', 'name' => 'fighting')));
			
		}
	}
	public function doWebaddquestion(){
		include $this->template('add');
	}
	public function doWebdelquestion(){
		global $_GPC, $_W;
		checklogin();
		$id = intval($_GPC['id']);
		pdo_delete('fighting_question_bank', " id=$id");
		message('删除成功！', create_url('site/module', array('do' => 'detail', 'name' => 'fighting')));
	}

	public function doWebaddok(){
		global $_GPC, $_W;
		checklogin();
		if ($_GPC['action'] = 'add') {
			$answer = strtoupper($_GPC['answer']);
			if(strlen($answer)>1){
				$answers = str_split($answer);
				$an = '000000';
				$an_num = count($answers);
				for($i=0;$i<$an_num;$i++){
					if($answers[$i]==A){
						$an = substr_replace($an,1,0,1);
					}elseif ($answers[$i]==B) {
						$an = substr_replace($an,1,1,1);
					}elseif ($answers[$i]==C) {
						$an = substr_replace($an,1,2,1);
					}elseif ($answers[$i]==D) {
						$an = substr_replace($an,1,3,1);
					}elseif ($answers[$i]==E) {
						$an = substr_replace($an,1,4,1);
					}elseif ($answers[$i]==F) {
						$an = substr_replace($an,1,5,1);
					}else{
						$an = $an;
					}
				}
			}else{
				$answer = strtoupper($_GPC['answer']);
				if ($answer == 'A') {
					$an = '100000';
				}elseif ($answer == 'B') {
					$an = '010000';
				}elseif ($answer == 'C') {
					$an = '001000';
				}elseif ($answer == 'D') {
					$an = '000100';
				}elseif ($answer == 'E') {
					$an = '000010';
				}elseif ($answer == 'F') {
					$an = '000001';
				}else{
					$an = '000000';
				}
			}
			$insert = array(
				'figure' => $_GPC['figure'],
				'question_types' =>$_GPC['question_types'],
				'question' => $_GPC['question'],
				'option_num' => $_GPC['option_num'],
				'optionA' => $_GPC['optionA'], 
				'optionB' => $_GPC['optionB'], 
				'optionC' => $_GPC['optionC'], 
				'optionD' => $_GPC['optionD'], 
				'optionE' => $_GPC['optionE'], 
				'optionF' => $_GPC['optionF'], 
				'answer' => $an, 
				'classify' => $_GPC['classify'],
			);
			//print_r($_GPC);
			$id=pdo_insert('fighting_question_bank', $insert);
			message('增加成功！', create_url('site/module', array('do' => 'detail', 'name' => 'fighting')));
			
		}
	}

	public function doWebShowPlay(){
		global $_GPC, $_W;
		checklogin();
		$id = intval($_GPC['id']);
		if (checksubmit('delete') && !empty($_GPC['select'])) {
			pdo_delete('fighting_setting', " id  IN  ('".implode("','", $_GPC['select'])."')");
			message('删除成功！', create_url('site/module', array('do' => 'detail', 'name' => 'fighting')));
		}
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;
		$list = pdo_fetchall("SELECT * FROM ".tablename('fighting_setting')." ORDER BY id ASC LIMIT ".($pindex - 1) * $psize.",{$psize}");
		//print_r($list);
		if (!empty($list)) {
			$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('fighting_setting') . "");
			$pager = pagination($total, $pindex, $psize);			
			unset($row);
		}
		include $this->template('plays');
	}

	public function doWebPlayEdit() {
		global $_GPC,$_W;
		checklogin();
		$id = intval($_GPC['id']);
		if (checksubmit('edit') && !empty($_GPC['id'])) {
			$insert = array(
				'title' => $_GPC['title'],
				'qnum' => $_GPC['qnum'],
				'tiao' => $_GPC['tiao'], 
				'tgkf' => $_GPC['tgkf'], 
				'answertime' => $_GPC['answertime'],
				'status_fighting' => $_GPC['status_fighting'], 
				'start' =>strtotime($_GPC['start']),
				'end' =>strtotime($_GPC['end']),
				'description' => $_GPC['description'],
				'reply1' => $_GPC['reply1'],
				'reply2' => $_GPC['reply2'],
				'reply3' => $_GPC['reply3'],
				'reply4' => $_GPC['reply4'],
				'reply5' => $_GPC['reply5'],
			);
			//print_r($insert);
			pdo_update($this->tablename, $insert,array('id' => $id));
			message('修改成功！', 'refresh');
		}
      	if (!empty($id)) {
			$reply = pdo_fetch("SELECT * FROM ".tablename($this->tablename)." WHERE id = :id ORDER BY `id` DESC", array(':id' => $id));		
 		} 
		include $this->template('playedit');
	}

	public function doWebPlayers() {
		global $_GPC,$_W;
		checklogin();
		$id = intval($_GPC['id']);
		//echo $id;
		if (checksubmit('delete') && !empty($_GPC['select'])) {
			pdo_delete('fighting', " id  IN  ('".implode("','", $_GPC['select'])."')");
			message('删除成功！', create_url('site/module', array('do' => 'Players', 'name' => 'fighting')));
		}
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;
		$list = pdo_fetchall("SELECT * FROM ".tablename('fighting')." WHERE `rid` = ".$id." ORDER BY `id` ASC LIMIT ".($pindex - 1) * $psize.",{$psize}");
		//print_r($list);
		if (!empty($list)) {
			$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('fighting') . "");
			$pager = pagination($total, $pindex, $psize);			
			unset($row);
		}
		include $this->template('players');
	}

	public function doWebEditPlayer(){
		global $_GPC,$_W;
		checklogin();
		$id = intval($_GPC['id']);
		if (checksubmit('edit') && !empty($_GPC['id'])) {
			$insert = array(
				'nickname' => $_GPC['nickname'],
				'lastcredit' => $_GPC['lastcredit'],
			);
			//print_r($insert);
			pdo_update('fighting', $insert,array('id' => $id));
			message('修改成功！', 'refresh');
		}
		$list = pdo_fetch("SELECT * FROM ".tablename('fighting')." WHERE id = :id ORDER BY `id` DESC", array(':id' => $id));
		//print_r($list);	
		include $this->template('editplayer');
	}
	

	public function doMobileRegister() {
		global $_GPC, $_W;
	
		if (!empty($_GPC['submit'])) {
	
			if (empty($_W['fans']['from_user'])) {
	
				message('非法访问，请重新发送消息进入砸蛋页面！');
	
			}
			
			$data = array(
					'nickname'=>$_GPC['nickname'],	
					'realname' => $_GPC['realname'],	
					'mobile' => $_GPC['mobile'],	
					'gender' => $_GPC['gender'],	
			);
	
			fans_update($_W['fans']['from_user'], $data);
	
			die('<script>location.href = "'.$this->createMobileUrl('success').'";</script>');
		}

//		$nickname = fans_search($_W['fans']['from_user'],'nickname');
//		echo $_W['fans']['from_user'].'-'.$nickname;
	//	return ;
		if (!empty($_W['fans']['nickname'])) {
			message('您已经登记过信息了');
				
		}
		include $this->template('register');
	}
	public function doMobileSuccess() {
	
		include $this->template('success');
	}
}
