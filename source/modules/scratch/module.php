<?php
/**
 * 刮刮卡模块定义
 *
 * @author 微动力
 * @url 
 */
defined('IN_IA') or exit('Access Denied');

class ScratchModule extends WeModule {
	public $tablename = 'scratch_reply';
	public function fieldsFormDisplay($rid = 0) {
		global $_W;
      	if (!empty($rid)) {
			$reply = pdo_fetch("SELECT * FROM ".tablename($this->tablename)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));		
 		} 
		include $this->template('form');
	}

	public function fieldsFormValidate($rid = 0) {
		//规则编辑保存时，要进行的数据验证，返回空串表示验证无误，返回其他字符串将呈现为错误提示。这里 $rid 为对应的规则编号，新增时为 0
		return '';
	}

	public function fieldsFormSubmit($rid) {
 		global $_GPC,$_W;
 		$id = intval($_GPC['reply_id']);
  
		$insert = array(
			'rid' => $rid,
			'title' => $_GPC['title'],
			'description' => $_GPC['description'],
			'isshow' => $_GPC['isshow'],
		);
		
	 
		if (empty($id)) {
			$id=pdo_insert($this->tablename, $insert);
		} else {
			pdo_update($this->tablename, $insert, array('id' => $id));
		}
      	return true;				
	}

	public function ruleDeleted($rid) {
		//删除规则时调用，这里 $rid 为对应的规则编号
		//删除award
		pdo_delete('award', array('rid' => $rid));
		//删除fans
		pdo_delete('scratch_fans', array('rid' => $rid));
	
	}	
	public function doManage() {
	 	global $_GPC,$_W;
		include model('rule');
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;
		$params = array();
		$params[':weid'] = $_W['weid'];
		$params[':module'] = 'scratch';
		$list = rule_search('weid = :weid AND `module` = :module', $params, $pindex, $psize, $total);
		$pager = pagination($total, $pindex, $psize);
		
		if (!empty($list)) {
			foreach($list as &$item) {
				$condition = "`rid`={$item['id']}";
				$item['keywords'] = rule_keywords_search($condition);
				
				$scratch = pdo_fetch("SELECT fansnum, viewnum,starttime,endtime,isshow FROM ".tablename('scratch_reply')." WHERE rid = :rid ", array(':rid' => $item['id']));
				$item['fansnum']=$scratch['fansnum'];
				$item['viewnum']=$scratch['viewnum'];
				$item['starttime']=date('Y/m/d H:i',$scratch['starttime']);
				$item['endtime']=date('Y/m/d H:i',$scratch['endtime']);
				$nowtime=time();
				if($scratch['starttime']>$nowtime){
					$item['status']='<span class="label label-red">未开始</span>';
					$item['show']=1;
				}elseif($scratch['endtime']<$nowtime){
					$item['status']='<span class="label label-blue">已结束</span>';
					$item['show']=0;
				}else{
					if($scratch['isshow']==1){
						$item['status']='<span class="label label-satgreen">已开始</span>';
						$item['show']=2;
					}else{
						$item['status']='<span class="label ">已暂停</span>';
						$item['show']=1;
					}
				}
			}
		}
		include $this->template('manage');
	}

	public function dodelete(){
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
			//调用模块中的删除
			$module = module($rule['module']);
			if (method_exists($module, 'ruleDeleted')) {
				$module->ruleDeleted($rid);
			}
		}	
		pdo_delete('scratch_reply',array('rid' => $rid));
		message('规则操作成功！', create_url('site/module', array('do' => 'manage', 'name' => 'scratch')), 'success');			
	}
	public function dodeleteAll(){
		global $_GPC,$_W;
 
		foreach($_GPC['idArr'] as $k=>$rid){
 			$rid= intval($rid);
			if($rid==0) continue;
			$rule = pdo_fetch("SELECT id, module FROM ".tablename('rule')." WHERE id = :id and weid=:weid", array(':id' => $rid,':weid'=>$_W['weid']));
			if (empty($rule)) {
				$this->message('抱歉，要修改的规则不存在或是已经被删除！');
			}		
			if (pdo_delete('rule', array('id' => $rid))) {
				pdo_delete('rule_keyword', array('rid' => $rid));
				//删除统计相关数据
				pdo_delete('stat_rule', array('rid' => $rid));
				pdo_delete('stat_keyword', array('rid' => $rid));
				//调用模块中的删除
				$module = module($rule['module']);
				if (method_exists($module, 'ruleDeleted')) {
					$module->ruleDeleted($rid);
				}
			}	
			pdo_delete('scratch_reply',array('rid' => $rid));
		}
		$this->message('规则操作成功！','', 0);			
	}	
	public function doaddcard(){
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
			//计算获奖总人数
			$total_num=intval($_GPC['c_name_one'])+intval($_GPC['c_num_two'])+intval($_GPC['c_num_three'])+intval($_GPC['c_num_four'])+intval($_GPC['c_num_five'])+intval($_GPC['c_num_six']);
						
			$insert=array(
				'rid'=>$rid,
				'keyword' =>$_GPC['keyword'],
				'title' =>$_GPC['title'],
				'content' =>$_GPC['content'],
				'ticket_information' =>$_GPC['ticket_information'],
				'description' =>$_GPC['description'],
				'Repeat_lottery_reply' =>$_GPC['Repeat_lottery_reply'],
				'start_picurl' =>$_GPC['start_picurl'],
				'end_theme' =>$_GPC['end_theme'],
				'end_instruction' =>$_GPC['end_instruction'],
				'end_picurl' =>$_GPC['end_picurl'],
				'probability' =>$_GPC['probability'],
				'c_type_one' =>$_GPC['c_type_one'],
				'c_name_one' =>$_GPC['c_name_one'],
				'c_num_one' =>$_GPC['c_num_one'],
				'c_type_two' =>$_GPC['c_type_two'],
				'c_name_two' =>$_GPC['c_name_two'],
				'c_num_two' =>$_GPC['c_num_two'],
				'c_type_three' =>$_GPC['c_type_three'],
				'c_name_three' =>$_GPC['c_name_three'], 
				'c_num_three' =>$_GPC['c_num_three'], 
				'c_type_four' =>$_GPC['c_type_four'], 
				'c_name_four' =>$_GPC['c_name_four'], 
				'c_num_four' =>$_GPC['c_num_four'], 
				'c_type_five' =>$_GPC['c_type_five'], 
				'c_name_five' =>$_GPC['c_name_five'], 
				'c_num_five' =>$_GPC['c_num_five'], 
				'c_type_six' =>$_GPC['c_type_six'], 
				'c_name_six' =>$_GPC['c_name_six'], 
				'c_num_six' =>$_GPC['c_num_six'], 
				'award_times'=>$_GPC['award_times'],				
				'number_times' =>$_GPC['number_times'],
				'most_num_times' =>$_GPC['most_num_times'],
				'sn_code' =>$_GPC['sn_code'],
				'sn_rename' =>$_GPC['sn_rename'],
				'tel_rename' =>$_GPC['tel_rename'],	
				'show_num'=>$_GPC['show_num'],	
				'total_num'=>$total_num,				
				'createtime'=>time(),
				'copyright'=>$_GPC['copyright'],
				'share_title'=>$_GPC['share_title'],
				'share_desc'=>$_GPC['share_desc'],
				'share_url'=>$_GPC['share_url'],
				'share_txt'=>$_GPC['share_txt'],

			);
			//处理时间
			list($starttime,$endtime)=explode('-',$_GPC['time']);
			$insert['starttime']=strtotime($starttime);
			$insert['endtime']=strtotime($endtime);
			$temp = pdo_insert('scratch_reply', $insert);
			if($temp==false){
				$this->message('抱歉，刚才添加的数据失败！','', -1);              
			}else{
				$this->message('刮刮卡添加数据成功！', create_url('site/module', array('do' => 'manage', 'name' => 'scratch')), 0);      
			}		
		}
		include $this->template('addcard');
	}
	public function doUpdateCard (){
		global $_GPC,$_W;
		$rid= intval($_GPC['rid']);
		if(empty($rid)){
			$this->message('抱歉，传递的参数错误！');  
		}
		if($_GPC['action']=='update'){
			$id= intval($_GPC['id']);
			if(empty($id)){
				$this->message('抱歉，传递的参数错误！');  
			}	
			//计算获奖总人数
			$total_num=intval($_GPC['c_num_one'])+intval($_GPC['c_num_two'])+intval($_GPC['c_num_three'])+intval($_GPC['c_num_four'])+intval($_GPC['c_num_five'])+intval($_GPC['c_num_six']);
			 
			$insert=array(
 				'keyword' =>$_GPC['keyword'],
				'title' =>$_GPC['title'],
				'content' =>$_GPC['content'],
				'ticket_information' =>$_GPC['ticket_information'],
				'description' =>$_GPC['description'],
				'Repeat_lottery_reply' =>$_GPC['Repeat_lottery_reply'],
				'start_picurl' =>$_GPC['start_picurl'],
				'end_theme' =>$_GPC['end_theme'],
				'end_instruction' =>$_GPC['end_instruction'],
				'end_picurl' =>$_GPC['end_picurl'],
				'probability' =>$_GPC['probability'],
				'c_type_one' =>$_GPC['c_type_one'],
				'c_name_one' =>$_GPC['c_name_one'],
				'c_num_one' =>$_GPC['c_num_one'],
				'c_type_two' =>$_GPC['c_type_two'],
				'c_name_two' =>$_GPC['c_name_two'],
				'c_num_two' =>$_GPC['c_num_two'],
				'c_type_three' =>$_GPC['c_type_three'],
				'c_name_three' =>$_GPC['c_name_three'], 
				'c_num_three' =>$_GPC['c_num_three'], 
				'c_type_four' =>$_GPC['c_type_four'], 
				'c_name_four' =>$_GPC['c_name_four'], 
				'c_num_four' =>$_GPC['c_num_four'], 
				'c_type_five' =>$_GPC['c_type_five'], 
				'c_name_five' =>$_GPC['c_name_five'], 
				'c_num_five' =>$_GPC['c_num_five'], 
				'c_type_six' =>$_GPC['c_type_six'], 
				'c_name_six' =>$_GPC['c_name_six'], 
				'c_num_six' =>$_GPC['c_num_six'], 
				'award_times'=>$_GPC['award_times'],
				'number_times' =>$_GPC['number_times'],
				'most_num_times' =>$_GPC['most_num_times'],
				'sn_code' =>$_GPC['sn_code'],
				'sn_rename' =>$_GPC['sn_rename'],
				'tel_rename' =>$_GPC['tel_rename'],	
				'show_num'=>$_GPC['show_num'],	
				'total_num'=>$total_num,
				'createtime'=>time(),	
				'copyright'=>$_GPC['copyright'],
				'share_title'=>$_GPC['share_title'],
				'share_desc'=>$_GPC['share_desc'],
				'share_url'=>$_GPC['share_url'],
				'share_txt'=>$_GPC['share_txt'],				
			);
			//处理时间
			list($starttime,$endtime)=explode('-',$_GPC['time']);
			$insert['starttime']=strtotime($starttime);
			$insert['endtime']=strtotime($endtime);
			$temp = pdo_update('scratch_reply', $insert,array('id'=>$id,'rid'=>$rid));
			if($temp==false){
				$this->message('抱歉，刚才修改的数据失败！','', -1);              
			}else{
				$this->message('刮刮卡修改数据成功！', create_url('site/module', array('do' => 'manage', 'name' => 'scratch')), 0);      
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
		include $this->template('updatecard');
	}
	public function doawardlist(){
		global $_GPC,$_W;
		$rid= intval($_GPC['rid']);
		if(empty($rid)){
			message('抱歉，传递的参数错误！','', 'error');              
		}
		$where='';
		if(!empty($_GPC['status'])){
			$where.=' and status='.$_GPC['status'].'';
		}
		if($_GPC['type']=='sn_code'){
			$where.=" and award_sn like '%".$_GPC['keywords']."%' ";
		}

		$total=pdo_fetchcolumn ("SELECT count(id) FROM ".tablename('award')." WHERE rid = :rid and weid=:weid ".$where."", array(':rid' => $rid,':weid'=>$_W['weid']));		
		$pindex = max(1, intval($_GPC['page']));
		$psize = 12;		
		$pager = pagination($total, $pindex, $psize);		
		$start = ($pindex - 1) * $psize;
		$limit .= " LIMIT {$start},{$psize}";
		$list = pdo_fetchall("SELECT * FROM ".tablename('award')." WHERE rid = :rid and weid=:weid  ".$where." ORDER BY `id` DESC ".$limit , array(':rid' => $rid,':weid'=>$_W['weid']));				
		
		//一些参数的显示
		$num1 = pdo_fetchcolumn("SELECT total_num FROM ".tablename($this->tablename)." WHERE rid = :rid", array(':rid' => $rid));		
		$num2 = pdo_fetchcolumn("SELECT count(id) FROM ".tablename('award')." WHERE rid = :rid and status=1", array(':rid' => $rid));		
		$num3 = pdo_fetchcolumn("SELECT count(id) FROM ".tablename('award')." WHERE rid = :rid and status=2", array(':rid' => $rid));		
		
		include $this->template('awardlist');
	}	
	public function dodownload(){
		require_once 'download.php';
	}
	public function dosetshow(){
		global $_GPC,$_W;
		$rid= intval($_GPC['rid']);
		if(empty($rid)){
			message('抱歉，传递的参数错误！','', 'error');              
		}
		$temp = pdo_update('scratch_reply',array('isshow'=>$_GPC['isshow']),array('rid'=>$rid));
		if($temp==false){
			message('抱歉，刚才操作数据失败！','', 'error');              
		}else{
			message('状态设置成功！', create_url('site/module', array('do' => 'manage', 'name' => 'scratch')), 'success');    
		}	
	}
	public function dosetstatus(){
		global $_GPC,$_W;
		$id= intval($_GPC['id']);
		$status= intval($_GPC['status']);
		if(empty($id)){
			message('抱歉，传递的参数错误！','', 'error');              
		}
		$temp = pdo_update('award',array('status'=>$status),array('id'=>$id,'weid'=>$_W['weid']));
		if($temp==false){
			message('抱歉，刚才操作数据失败！','', 'error');              
		}else{
			message('状态设置成功！', create_url('site/module', array('do' => 'awardlist', 'name' => 'scratch','rid'=>$_GPC['rid'])), 'success');    
		}		
	}
	public function dogetphone(){
		global $_GPC,$_W;
		$rid= intval($_GPC['rid']);
		$fans=$_GPC['fans'];
		//
		$tel=pdo_fetchcolumn ("SELECT tel FROM ".tablename('scratch_fans')." WHERE rid = ".$rid." and  from_user='".$fans."'");						
		if($tel==false){
			echo '没有登记';
		}else{
			echo $tel;
		}
	}
	public function setkeyword($_kewords,$rid=0,$_module='scratch'){
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
				$this->message('规则操作失败, 请联系网站管理员！');
				
			}
		}else{
			//修改
		
		}
		return $rid;
	}
	 public function message($error,$url='',$errno=-1){
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