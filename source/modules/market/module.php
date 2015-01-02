<?php
/**
 * 微生活模块定义
 *
 * @author 微新星
 * @url 
 */
defined('IN_IA') or exit('Access Denied');
define('RES', "http://src.mmghome.com/");

class MarketModule extends WeModule {
	public function fieldsFormDisplay($rid = 0) {
		//要嵌入规则编辑页的自定义内容，这里 $rid 为对应的规则编号，新增时为 0
	}

	public function fieldsFormValidate($rid = 0) {
		//规则编辑保存时，要进行的数据验证，返回空串表示验证无误，返回其他字符串将呈现为错误提示。这里 $rid 为对应的规则编号，新增时为 0
		return '';
	}

	public function fieldsFormSubmit($rid) {
		//规则验证无误保存入库时执行，这里应该进行自定义字段的保存。这里 $rid 为对应的规则编号
	}

	public function ruleDeleted($rid) {
		//删除规则时调用，这里 $rid 为对应的规则编号
	}
	//后台管理菜单
	public function doclasslist(){
		global $_GPC,$_W;
		$volist = pdo_fetchall("SELECT * FROM ".tablename('market_class')." WHERE  weid=:weid", array(':weid'=>$_W['weid']));
		include $this->template('class');
	}
	public function doclassEd(){
		global $_GPC,$_W;
		$id= intval($_GPC['id']);
		$weid=$_W['weid'];
		if ($_GPC['action']=='update') {
			$insert=array(
				'weid'=>$_W['weid'],
				'classname'=>$_GPC['classname'],
				'sort'=>$_GPC['sort'],
				'infos'=>$_GPC['infos'],
				'update_time'=>time(),
			);
			if($id==0){
				//添加
				$temp = pdo_insert('market_class', $insert);
				if($temp==false){
					$this->message('抱歉，刚才添加的数据失败！');              
				}else{
					$this->message('分类数据添加成功！', create_url('site/module', array('do' => 'classlist', 'name' => 'market')), 0);      
				}
			}else{
				//更新
				$temp = pdo_update('market_class', $insert,array('id'=>$id,'weid'=>$weid));
				if($temp==false){
					$this->message('抱歉，刚才修改的数据失败！');              
				}else{
					$this->message('分类数修改成功！', create_url('site/module', array('do' => 'classlist', 'name' => 'market')), 0);      
				}				
			}
		}
		if($id==0){
			//添加
			$row=array(
				'id'=>0,
				'sort'=>0,
			);
		}else{
			$row = pdo_fetch("SELECT * FROM ".tablename('market_class')." WHERE id = :id and weid=:weid", array(':id' => $id,':weid'=>$_W['weid']));
		}
		include $this->template('classed');
	}
	
	public function doclassDel(){
		global $_GPC,$_W;
		$id= intval($_GPC['id']);
		$weid=$_W['weid'];
		//判断是不是含有商户
		pdo_delete('market_class',array('id'=>$id,'weid'=>$weid));
		message('删除商户成功', create_url('site/module', array('do' => 'classlist', 'name' => 'market')), 'success');			
	}
	public function dodelbusiness(){
		global $_GPC,$_W;
		$id= intval($_GPC['id']);
		$weid=$_W['weid'];
		//判断是不是含有商户
		pdo_delete('market_business',array('id'=>$id,'weid'=>$weid));
		message('删除商户成功', create_url('site/module', array('do' => 'businesslist', 'name' => 'market')), 'success');			
	}
	
	public function dobusinesslist(){
		global $_GPC,$_W;
		$classid= intval($_GPC['classid']);
		$where="WHERE  weid=".$_W['weid']." and classid=".$classid."";
		$total=pdo_fetchcolumn ("SELECT count(id) FROM ".tablename('market_business')." ".$where."");		

		$pindex = max(1, intval($_GPC['page']));
		$psize = 15;		
		$start = ($pindex - 1) * $psize;		
		$pager = pagination($total, $pindex, $psize);
  		$limit .= " LIMIT {$start},{$psize}";
		$volist = pdo_fetchall("SELECT * FROM ".tablename('market_business')." ".$where."  ".$limit);
		include $this->template('business');
	}
	public function dobusinessEd1(){
		global $_GPC,$_W;
		$id= intval($_GPC['id']);
		$weid=$_W['weid'];	
		if ($_GPC['action']=='save') {
			if($_GPC['rid']==0){
				$rid =$this->setkeyword($_GPC['keyword']);
			}else{
				$rid=$_GPC['rid'];
			}
			$insert=array(
				'weid'=>$_W['weid'],
				'rid'=>$rid,
				'classid'=>$_GPC['classid'],
				'keyword'=>$_GPC['keyword'],
				'title'=>$_GPC['title'],
				'picurl'=>$_GPC['picurl'],
				'infos'=>$_GPC['infos'],
				'outlink'=>$_GPC['outlink'],				
				'update_time'=>time(),
			);
			if($id==0){
				//添加
				$temp = pdo_insert('market_business', $insert);
				if($temp==false){
					$this->message('抱歉，刚才添加的商户数据失败！');              
				}else{
					$this->message('商户数据添加成功！', create_url('site/module', array('do' => 'businesslist', 'name' => 'market','classid'=>$_GPC['classid'])), 0);      
				}
			}else{
				//更新
				$temp = pdo_update('market_business', $insert,array('id'=>$id,'weid'=>$weid));
				if($temp==false){
					$this->message('抱歉，刚才修改的商户数据失败！');              
				}else{
					$this->message('商户数修改成功！', create_url('site/module', array('do' => 'businesslist', 'name' => 'market','classid'=>$_GPC['classid'])), 0);      
				}				
			}
		}		
		if($id==0){
			//添加
			$row=array(
				'rid'=>0,
				'id'=>0,
				'sort'=>0,
				'classid'=>$_GPC['classid'],
			);
		}else{
			$row = pdo_fetch("SELECT * FROM ".tablename('market_business')." WHERE id = :id and weid=:weid", array(':id' => $id,':weid'=>$_W['weid']));
			include model('rule');
			$keyArr= rule_keywords_search("`rid`={$row['rid']}");
			foreach($keyArr as $k=>$v){
				$keyArr[$k]=$v['content'];
			}
 			$row['keyword']=implode(' ',$keyArr);
		}
		include $this->template('businessed1');
	}
	public function dobusinessEd2(){
		global $_GPC,$_W;
		$id= intval($_GPC['id']);
		if($id==0){
			message('请先设置商户基本设置', $this->createWebUrl('businessEd1'), 'error');
		}
		$weid=$_W['weid'];	
		if ($_GPC['action']=='save') {
			$insert=array(
				'shopname'=>$_GPC['shopname'],
				'logo'=>$_GPC['logo'],
				'sort' => $_GPC['sort'],
				'description' =>$_GPC['description'],
				'address' =>$_GPC['address'],
				'tel' =>$_GPC['tel'],
				'lng' =>$_GPC['lng'],
				'lat' =>$_GPC['lat'],		
				'update_time'=>time(),
			);
			//更新
			$temp = pdo_update('market_business', $insert,array('id'=>$id,'weid'=>$weid));
			if($temp==false){
				$this->message('抱歉，刚才修改的商户数据失败！');              
			}else{
				$this->message('商户数据修改成功！', create_url('site/module', array('do' => 'businessEd2', 'name' => 'market','classid'=>$_GPC['classid'],'id'=>$id)), 0);      
			}				
		}		 
		$row = pdo_fetch("SELECT * FROM ".tablename('market_business')." WHERE id = :id and weid=:weid", array(':id' => $id,':weid'=>$_W['weid']));
		include $this->template('businessed2');
	}
	public function dobusinessEd3(){
		global $_GPC,$_W;
		$id= intval($_GPC['id']);
		if($id==0){
			message('请先设置商户基本设置', $this->createWebUrl('businessEd1'), 'error');
		}
		$weid=$_W['weid'];	
		if ($_GPC['action']=='save') {
			$insert=array(
				'card_name'=>$_GPC['card_name'],
				'color'=>$_GPC['color'],
				'background' => $_GPC['background'],
				'bgcustom' =>$_GPC['bgcustom'],
				'card_logo' =>$_GPC['card_logo'],
				'font_color' =>$_GPC['font_color'],
				'info' =>$_GPC['info'],
				'update_time'=>time(),
			);
			//更新
			$temp = pdo_update('market_business', $insert,array('id'=>$id,'weid'=>$weid));
			if($temp==false){
				$this->message('抱歉，刚才修改的商户数据失败！');              
			}else{
				$this->message('商户数据修改成功！', create_url('site/module', array('do' => 'businessEd3', 'name' => 'market','classid'=>$_GPC['classid'],'id'=>$id)), 0);      
			}				
		}		 
		$row = pdo_fetch("SELECT * FROM ".tablename('market_business')." WHERE id = :id and weid=:weid", array(':id' => $id,':weid'=>$_W['weid']));
		if($row==false){
			$row=array(
			'color'=>'#000',
			'background'=>RES.'card/images/card_bg21.png',
			'card_logo'=>RES.'img/logo.png',
			'font_color'=>'#000',
			);
		}
		include $this->template('businessed3');
	}
	public function dobusinessEd4(){
		global $_GPC,$_W;
		$id= intval($_GPC['id']);
		if($id==0){
			message('请先设置商户基本设置', $this->createWebUrl('businessEd1'), 'error');
		}
		$volist = pdo_fetchall("SELECT * FROM ".tablename('market_privilege')." WHERE  weid=:weid and id=:id", array(':id'=>$id,':weid'=>$_W['weid']));
		include $this->template('businessed4');
	}
	public function doEdPrivilege (){
		global $_GPC,$_W;
		$id= intval($_GPC['id']);
		$weid=$_W['weid'];	
		$pid= intval($_GPC['pid']);
		if($id==0){
			message('请先设置商户基本设置', $this->createWebUrl('businessEd1'), 'error');
		}
		if ($_GPC['action']=='save') {
			$insert=array(
				'weid'=>$_W['weid'],
				'id'=>$id,
				'title'=>$_GPC['title'],
				'use_info'=>$_GPC['use_info'],
				'is_show'=>$_GPC['is_show'],
				'update_time'=>time(),
			);
			list($starttime,$endtime)=explode('-',$_GPC['time']);
			$insert['starttime']=strtotime($starttime);
			$insert['endtime']=strtotime($endtime);			
			if($pid==0){
				//添加
				$temp = pdo_insert('market_privilege', $insert);
				if($temp==false){
					$this->message('抱歉，刚才添加的优惠信息失败！');              
				}else{
					$this->message('商户优惠信息添加成功！', create_url('site/module', array('do' => 'businessEd4', 'name' => 'market','classid'=>$_GPC['classid'],'id'=>$_GPC['id'])), 0);      
				}
			}else{
				//更新
				$temp = pdo_update('market_privilege', $insert,array('pid'=>$pid,'weid'=>$weid));
				if($temp==false){
					$this->message('抱歉，刚才修改的优惠信息失败！');              
				}else{
					$this->message('优惠信息修改成功！', create_url('site/module', array('do' => 'businessEd4', 'name' => 'market','classid'=>$_GPC['classid'],'id'=>$_GPC['id'])), 0);      
				}				
			}
		}
		if($pid!=0){
			$row = pdo_fetch("SELECT * FROM ".tablename('market_privilege')." WHERE id = :id and weid=:weid and pid=:pid", array(':pid' => $pid,':id' => $id,':weid'=>$_W['weid']));
		}
		
		if($row==false){
			//添加
			$row=array(
				'id'=>$id,
				'pid'=>0,
				'is_show'=>1,
				'time'=>date('Y/m/d H:i').'-'.date('Y/m/d H:i',strtotime('+7 day')),
			);
		}else{
			$row['time']=date('Y/m/d H:i',$row['starttime']).'-'.date('Y/m/d H:i',$row['endtime']);
		}
		
		include $this->template('EdPrivilege');
	}
	
	public function dodelPrivilege(){
		global $_GPC,$_W;
		$id= intval($_GPC['id']);
		$weid=$_W['weid'];
		//判断是不是含有商户
		pdo_delete('market_privilege',array('id'=>$id,'weid'=>$weid));
		message('删除优惠信息成功', create_url('site/module', array('do' => 'businessEd4', 'name' => 'market','classid'=>$_GPC['classid'],'id'=>$_GPC['id'])), 'success');			
	}
	
	public function setkeyword($_kewords,$rid=0,$_module='market'){
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