<?php
/**
 * 一战到底模块微站定义
 *
 * @author 珊瑚海
 * @url #
 */
defined('IN_IA') or exit('Access Denied');

class FightingModuleSite extends WeModuleSite {

	public $tablename = 'fighting_reply';
	public $tablefans = 'fighting_fans';

	public function doWebPlaylist() {
		//这个操作被定义用来呈现 管理中心导航菜单
		global $_GPC,$_W;
		include model('rule');
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;
		$module='fighting';
		$list = rule_search("weid = '{$_W['weid']}'  AND module = '$module'",$params, $pindex, $psize, $total);
		$pager = pagination($total, $pindex, $psize);
		if (!empty($list)) {
			foreach($list as &$item) {
				$condition = "`rid`={$item['id']}";
				$item['keywords'] = rule_keywords_search($condition);
				
				$fighting = pdo_fetch("SELECT title,qutype,fansnum, viewnum,starttime,endtime,isshow FROM ".tablename('fighting_reply')." WHERE rid = :rid ", array(':rid' => $item['id']));
				$item['title']=$fighting['title'];
				$item['qutype']=$fighting['qutype'];
				$item['fansnum']=$fighting['fansnum'];
				$item['viewnum']=$fighting['viewnum'];
				$item['starttime']=date('Y/m/d H:i',$fighting['starttime']);
				$item['endtime']=date('Y/m/d H:i',$fighting['endtime']);
				$nowtime=time();
				if($fighting['starttime']>$nowtime){
					$item['status']='<span class="label label-red">未开始</span>';
					$item['show']=1;
				}elseif($fighting['endtime']<$nowtime){
					$item['status']='<span class="label label-blue">已结束</span>';
					$item['show']=0;
				}else{
					if($fighting['isshow']==1){
						$item['status']='<span class="label label-satgreen">已开始</span>';
						$item['show']=2;
					}else{
						$item['status']='<span class="label ">已暂停</span>';
						$item['show']=1;
					}
				}
			}
		}
		include $this->template('plays');
	}

	public function doWebAddplay(){
		global $_GPC,$_W;
		if($_GPC['action']=='save'){
			//关键字处理
			if(empty($_GPC['keyword'])){
				message('关键字不能为空，请重新填写！');
			}
			if(empty($_GPC['rid'])){
				$rid =$this->setkeyword($_GPC['keyword']);
			}else{
				$rid=$_GPC['rid'];
			}						
			$insert=array(
				'rid'=>$rid,
				'keyword' =>$_GPC['keyword'],
				'title' =>$_GPC['title'],
				'qutype' => $_GPC['qutype'],
				'description' =>$_GPC['description'],
				'reply1' =>$_GPC['reply1'],
				'reply2' =>$_GPC['reply2'],
				'reply3' =>$_GPC['reply3'],
				'reply4' =>$_GPC['reply4'],
				'reply5' =>$_GPC['reply5'],
				'qnum' =>$_GPC['qnum'],
				'tiao' =>$_GPC['tiao'],
				'fansnum'=>'0',
				'viewnum'=>'0',
				'isshow'=>'0',
				'tgkf' =>$_GPC['tgkf'],
				'ad' =>$_GPC['ad'],
				'isad' =>$_GPC['isad'],
			);
			//处理时间
			list($starttime,$endtime)=explode('-',$_GPC['time']);
			$insert['starttime']=strtotime($starttime);
			$insert['endtime']=strtotime($endtime);
			$temp = pdo_insert('fighting_reply', $insert);
			if($temp==false){
				$this->message_2('抱歉，刚才添加的数据失败！','', -1);              
			}else{
				$this->message_2('一战到底添加数据成功！', create_url('site/module/playlist', array('state' => '', 'name' => 'fighting')), 0);      
			}		
		}
		include $this->template('addplay');
	}

	public function FunctionName($value=''){

	}

	public function doWebsetshow(){
		global $_GPC,$_W;
		$rid= intval($_GPC['rid']);
		if(empty($rid)){
			message('抱歉，传递的参数错误！','', 'error');              
		}
		$temp = pdo_update('fighting_reply',array('isshow'=>$_GPC['isshow']),array('rid'=>$rid));
		if($temp==false){
			message('抱歉，刚才操作数据失败！','', 'error');              
		}else{
			message('状态设置成功！', create_url('site/module/playlist', array('state' => '', 'name' => 'fighting')), 'success');    
		}	
	}

	public function doWebdelete(){
		global $_GPC,$_W;
		$rid= intval($_GPC['rid']);

		$rule = pdo_fetch("SELECT id, module FROM ".tablename('rule')." WHERE id = :id and weid=:weid", array(':id' => $rid,':weid'=>$_W['weid']));
		if (empty($rule)) {
			message('抱歉，要修改的规则不存在或是已经被删除！');
		}		
		if (pdo_delete('rule', array('id' => $rid))) {
			pdo_delete('rule_keyword', array('rid' => $rid));
			//删除统计相关数据
			pdo_delete('stat_rule', array('rid' => $rid));
			pdo_delete('stat_keyword', array('rid' => $rid));
		}	
		pdo_delete('fighting_reply',array('rid' => $rid));
		message('规则操作成功！', create_url('site/module/playlist', array('state' => '', 'name' => 'fighting')), 'success');			
	}
	public function doWebdeleteAll(){
		global $_GPC,$_W;
 
		foreach($_GPC['idArr'] as $k=>$rid){
 			$rid= intval($rid);
			if($rid==0) continue;
			$rule = pdo_fetch("SELECT id, module FROM ".tablename('rule')." WHERE id = :id and weid=:weid", array(':id' => $rid,':weid'=>$_W['weid']));
			if (empty($rule)) {
				$this->message_2('抱歉，要修改的规则不存在或是已经被删除！');
			}		
			if (pdo_delete('rule', array('id' => $rid))) {
				pdo_delete('rule_keyword', array('rid' => $rid));
				//删除统计相关数据
				pdo_delete('stat_rule', array('rid' => $rid));
				pdo_delete('stat_keyword', array('rid' => $rid));
			}	
			pdo_delete('fighting_reply',array('rid' => $rid));
		}
		$this->message_2('规则操作成功！','', 0);			
	}

	public function doWebUpdateplay($value='')	{
		global $_GPC,$_W;
		$rid= intval($_GPC['rid']);
		if(empty($rid)){
			$this->message_2('抱歉，传递的参数错误！');  
		}
		if($_GPC['action']=='update'){
			$id= intval($_GPC['id']);
			if(empty($id)){
				$this->message_2('抱歉，传递的参数错误！');  
			}			 
			$insert=array(
 				'rid'=>$rid,
				'keyword' =>$_GPC['keyword'],
				'qutype' => $_GPC['qutype'],
				'title' =>$_GPC['title'],
				'description' =>$_GPC['description'],
				'reply1' =>$_GPC['reply1'],
				'reply2' =>$_GPC['reply2'],
				'reply3' =>$_GPC['reply3'],
				'reply4' =>$_GPC['reply4'],
				'reply5' =>$_GPC['reply5'],
				'qnum' =>$_GPC['qnum'],
				'tiao' =>$_GPC['tiao'],
				'tgkf' =>$_GPC['tgkf'],
				'ad' =>$_GPC['ad'],
				'isad' =>$_GPC['isad'],				
			);
			//处理时间
			list($starttime,$endtime)=explode('-',$_GPC['time']);
			$insert['starttime']=strtotime($starttime);
			$insert['endtime']=strtotime($endtime);
			$temp = pdo_update('fighting_reply', $insert,array('id'=>$id,'rid'=>$rid));
			
			if($temp==false){
				$this->message_2('抱歉，刚才修改的数据失败！','', -1);              
			}else{
				$this->message_2('一战到底修改数据成功！', create_url('site/module/playlist', array('state' => '', 'name' => 'fighting')), 0);      
			}
		}
		$reply = pdo_fetch("SELECT * FROM ".tablename($this->tablename)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));		
		if($reply==false){
			message('抱歉，活动已经被删除了！');  
		}else{
			include model('rule');
			$keyArr= rule_keywords_search("`rid`={$rid}");
			foreach($keyArr as $k=>$v){
				$keyArr[$k]=$v['content'];
			}
			$keyArr=array_reverse($keyArr);
 			$reply['keyword']=implode(' ',$keyArr);
		}
		//获取关键字
		include $this->template('updateplay');
		
	}

	public function doWebQuestions() {
		//这个操作被定义用来呈现 管理中心导航菜单
		global $_GPC, $_W;
		checklogin();
		$id = intval($_GPC['id']);
		if (checksubmit('delete') && !empty($_GPC['select'])) {
			pdo_delete('fighting_question_bank', " id  IN  ('".implode("','", $_GPC['select'])."')");
			message('删除成功！', create_url('site/module/questions', array('name' => 'fighting')));
		}
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;
		$list = pdo_fetchall("SELECT * FROM ".tablename('fighting_question_bank')." WHERE `weid` = ".$_W['weid']." OR `weid` = 0 ORDER BY id ASC LIMIT ".($pindex - 1) * $psize.",{$psize}");
		//print_r($list);
		if (!empty($list)) {
			$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('fighting_question_bank') . "WHERE `weid` = ".$_W['weid']." OR `weid` = 0");
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

	public function doWebQuestionedit(){
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
	public function doWebQuestioneditok(){
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
				'weid' => $_GPC['weid'],
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
			message('修改成功！', create_url('site/module/questions', array('name' => 'fighting')));
			
		}
	}
	public function doWebaddquestion(){
		global $_W;
		include $this->template('add');
	}
	public function doWebdelquestion(){
		global $_GPC, $_W;
		checklogin();
		$id = intval($_GPC['id']);
		$aa = pdo_fetch("SELECT weid FROM ".tablename('fighting_question_bank')."WHERE id =".$id);
		if (($aa['weid'] == $_W['weid']) || ($_W['isfounder'] == true)) {
			message('删除成功！', create_url('site/module/questions', array('name' => 'fighting')));
		}else{
			message("删除失败，没有权限！", create_url('site/module/questions', array('name' => 'fighting')),'error');
		}
		//pdo_delete('fighting_question_bank', " id=$id");
		//message('删除成功！', create_url('site/module/questions', array('name' => 'fighting')));
	}

	public function doWebQuestionaddok(){
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
				'weid' => $_GPC['weid'],
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
			message('增加成功！', create_url('site/module/questions', array('name' => 'fighting')));
			
		}
	}

	public function setkeyword($_kewords,$rid=0,$_module='fighting'){
		global $_W;
		if($rid==0){
 			//新添加,第一步添加到rule表中，第二步添加关键字
			$rule = array(
				'weid' => $_W['weid'],
				'cid' => '',
				'name' =>$_kewords,
				'module' => $_module,
				'status' => 1,
				'displayorder'=>0,
			);
		
			$result = pdo_insert('rule', $rule);
			$rid = pdo_insertid();
			if (!empty($rid)) {
				//更新，添加，删除关键字
				$rows = array();
				$rowtpl = array(
					'rid' => $rid,
					'weid' => $_W['weid'],
					'module' => $rule['module'],
					'status' => $rule['status'],
					'displayorder' => $rule['displayorder'],
				);

				if(!empty($_kewords)) {
					$_kewords=str_replace(' ',',',$_kewords);
					$kwds = explode(',', trim($_kewords));
					foreach($kwds as $kwd) {
						$kwd = trim($kwd);
						if(empty($kwd)) {
							continue;
						}
						$rowtpl['content'] = $kwd;
						$rowtpl['type'] = 1; 
						$rows[md5($rowtpl['type'] . $rowtpl['content'])] = $rowtpl;
					}
				}
				foreach($rows as $krow) {
					$result = pdo_insert('rule_keyword', $krow);
				}
			} else {
				$this->message_2('规则操作失败, 请联系网站管理员！');
				
			}
		}else{
			//修改
		
		}
		return $rid;
	}

	public function message_2($error,$url='',$errno=-1){
		$data=array();
		$data['errno']=$errno;
		if(!empty($url)){
			$data['url']=$url;		
		}
		$data['error']=$error;		
		echo json_encode($data);
		exit;
	 }
}