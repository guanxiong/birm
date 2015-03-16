<?php
/**
 * 
 *
 * 
 */
defined('IN_IA') or exit('Access Denied');
include './source/modules/xfcommunity/model.php';
class XfcommunityModuleSite extends WeModuleSite {
	public $member          = 'xcommunity_member';
	public $region          = 'xcommunity_region';
	public $phone           = 'xcommunity_phone';
	public $announcement    = 'xcommunity_announcement';
	public $report          = 'xcommunity_report';
	public $reply           = 'xcommunity_reply';
	public $servicecategory = 'xcommunity_servicecategory';
	public $service         = 'xcommunity_service';
	public $property        = 'xcommunity_property';
	public $navExtension    = 'xcommunity_navExtension';
	public $comslide    	= 'xcommunity_slide';
	public $set 			= 'xcommunity_set';
	public function getProfileTiles() {
	}
	public function getHomeTiles() {
	}
	public function __construct(){

	}
	//后台小区信息
	public function doWebRegion() {
		global $_GPC,$_W;
		$op = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
		//显示小区信息
		if($op == "display"){
			$sql     = "select * from ".tablename($this->region)."where weid = '{$_W['weid']}'";
			$regions = pdo_fetchall($sql);
		}
		//删除小区信息
		if($op == 'delete'){
			$id = intval($_GPC['id']);
			pdo_delete($this->region, array('id' => $id));
			message('删除成功',referer(), 'success');
		}
		//添加和更新小区信息
		if(checksubmit('submit')){
			for ($i=0; $i <count($_GPC['titles']) ; $i++) { 
				$ids = $_GPC['ids'];
				$id  = trim(implode(',', $ids),',');
				$insert = array(
									'title'   =>  $_GPC['titles'][$i] ,
									'linkmen' =>  $_GPC['linkmen'][$i],
									'linkway' =>  $_GPC['linkways'][$i],
									'content' =>  $_GPC['contents'][$i],
									'weid'    =>  $_W['weid'],
				 			);
				if($ids[$i] !=NULL){
					pdo_update($this->region,$insert,array('id'=>$ids[$i]));
				}else{
					pdo_insert($this->region,$insert);
				}
			}
			message('更新信息成功',referer(), 'success');
		}
		include $this->template('region');
	}
	//后台公告
	public function doWebAnnouncement(){
		global $_GPC,$_W;
		$regionid = $_GPC['regionid'];
		$operation = $_GPC['op'];
		$id = $_GPC['id'];
		if($operation == 'display'){
			//公告搜索
			$title     = $_GPC['title'];
			$status    = $_GPC['status'];
			$starttime = strtotime($_GPC['starttime']);
			$endtime   = strtotime($_GPC['endtime']);
			if (!empty($starttime) && $starttime==$endtime) {
				$endtime = $endtime+86400-1;
			}
			if ($_W['ispost']) {
				if(!empty($status)){
					if (!empty($title)) {
						$st = trim(implode(',', $status),',');
						$sql = "select * from ".tablename($this->announcement)."where regionid=".$regionid." and weid = {$_W['weid']}";
						$sql.= " and status in "."(".$st.")"." and title="."'".$title."'"." and createtime between ".$starttime." and ".$endtime;
					}else{
						$st = trim(implode(',', $status),',');
						$sql = "select * from ".tablename($this->announcement)."where regionid=".$regionid." and weid = {$_W['weid']}";
						$sql.= " and status in "."(".$st.")"." and createtime between ".$starttime." and ".$endtime;
					}
					
				}else{
					if (!empty($title)) {
						$sql = "select * from ".tablename($this->announcement)."where regionid=".$regionid." and weid = {$_W['weid']}";
						$sql.= "  and title="."'".$title."'"." and createtime between ".$starttime." and ".$endtime;
					}else{
						$sql = "select * from ".tablename($this->announcement)."where regionid=".$regionid." and weid = {$_W['weid']}";
						$sql.= " and createtime between ".$starttime." and ".$endtime;
					}
				}
				$list = pdo_fetchall($sql);
			}else{
				//管理公告
				$pindex = max(1, intval($_GPC['page']));
				$psize  = 5;
				$sql    = "select * from ".tablename($this->announcement)."where regionid=".$regionid." and weid = {$_W['weid']} LIMIT ".($pindex - 1) * $psize.','.$psize;
				$list   = pdo_fetchall($sql);
				$total  = pdo_fetchcolumn('select count(*) from'.tablename($this->announcement)."where regionid=".$regionid." and weid = {$_W['weid']}");
				$pager  = pagination($total, $pindex, $psize);
			}	
		}
		if(!empty($id)){
			$sql = "select * from ".tablename($this->announcement)."where regionid=".$regionid." and id=".$id;
			$item = pdo_fetch($sql);
		}
		if($operation == 'post'){
			//添加公告
			if(checksubmit('submit')){
				$insert = array(
						'weid'       => $_W['weid'],
						'regionid'   =>$regionid,
						'title'      =>$_GPC['title'],
						'content'    =>htmlspecialchars_decode($_GPC['content']),
						'createtime' =>$_W['timestamp'],
						'starttime'  =>strtotime($_GPC['starttime']),
						'endtime'    =>strtotime($_GPC['endtime']),
						'status'     =>$_GPC['status'],
						'author'     =>$_W['account']['name'],
					);
				if(empty($id)){
					pdo_insert($this->announcement,$insert);
				}else{
					pdo_update($this->announcement,$insert,array('id'=>$id,'regionid'=>$regionid));
				}
				message('更新信息成功',referer(), 'success');
			}
		}
		if($operation == 'delete'){
			//删除公告
			pdo_delete($this->announcement,array('id'=>$id,'regionid'=>$regionid,'weid'=>$_W['weid']));
			message('删除成功',referer(), 'success');
		}
		include $this->template('announcement');
	}
	//后台住户
	public function doWebMember(){
		global $_GPC,$_W;
		$operation = $_GPC['op'];
		$regionid  = $_GPC['regionid'];
		$id        = $_GPC['id'];
		if ($operation == 'display') {
			if($_W['ispost']){
				$realname = trim($_GPC['realname']);
				$mobile   = trim($_GPC['mobile']);
				$type     = $_GPC['type'];
				if(empty($type)){
					if(empty($realname) && !empty($mobile)){
						$conditions = "where weid='{$_W['weid']}' and regionid=".$regionid." and mobile=".$mobile;
					}elseif (empty($mobile) && !empty($realname)) {
						$conditions = "where weid='{$_W['weid']}' and regionid=".$regionid." and realname="."'".$realname."'";
					}elseif (empty($realname) && empty($mobile)) {
						$conditions = "where weid='{$_W['weid']}' and regionid=".$regionid;
					}elseif (!empty($realname) && !empty($mobile)){
						$conditions = "where weid='{$_W['weid']}' and regionid=".$regionid." and mobile=".$mobile." and realname="."'".$realname."'";
					}
				}else{
					if(empty($realname) && !empty($mobile)){
						$conditions = "where weid='{$_W['weid']}' and regionid=".$regionid." and mobile=".$mobile." and type=".$type;
					}elseif (empty($mobile) && !empty($realname)) {
						$conditions = "where weid='{$_W['weid']}' and regionid=".$regionid." and realname="."'".$realname."'"." and type=".$type;
					}elseif (empty($realname) && empty($mobile)) {
						$conditions = "where weid='{$_W['weid']}' and regionid=".$regionid." and type=".$type;
					}elseif (!empty($realname) && !empty($mobile)){
						$conditions = "where weid='{$_W['weid']}' and regionid=".$regionid." and mobile=".$mobile." and realname="."'".$realname."'"." and type=".$type;
					}
				}
				$sql = "select * from".tablename($this->member)." $conditions";
				$list = pdo_fetchall($sql);

			}else{
				//显示住户信息
				$pindex = max(1, intval($_GPC['page']));
				$psize  = 5;
				$sql    = "select * from ".tablename($this->member)."where weid='{$_W['weid']}' and regionid=".$regionid." LIMIT ".($pindex - 1) * $psize.','.$psize;
				$list   = pdo_fetchall($sql);
				$total  = pdo_fetchcolumn('select count(*) from'.tablename($this->member)."where weid='{$_W['weid']}' and regionid=".$regionid);
				$pager  = pagination($total, $pindex, $psize);
			}
		}elseif($operation == 'post') {
			//查看住户信息
			$sql    = "select * from ".tablename($this->member)."where weid='{$_W['weid']}' and regionid=".$regionid." and id=".$id;
			$member = pdo_fetch($sql);
			//查看小区信息
			$sql_1   = "select * from".tablename($this->region);
			$regions = pdo_fetchall($sql_1);
			//print_r($item);exit;
			if(checksubmit('submit')){
				//修改用户信息
			$sql_2 = "select title from".tablename($this->region)."where id=".$_GPC['_regionid'];
			$item  = pdo_fetch($sql_2);
				$data = array(
					'realname'   =>$_GPC['realname'],
					'mobile'     =>$_GPC['mobile'],
					'type'       =>$_GPC['type'],
					'regionid'   =>$_GPC['_regionid'],
					'address'    =>$_GPC['address'],
					'remark'     =>$_GPC['remark'],
					'createtime' =>$_W['timestamp'],
					'regionname' =>$item['title'],
					);
				pdo_update($this->member,$data,array('id' => $id));
				message('修改成功',$this->createWebUrl('region'), 'success');
			}
				
		}elseif ($operation == 'delete') {
			//删除用户
			pdo_delete($this->member,array('id'=>$id,'weid'=>$_W['weid'],'regionid'=>$regionid));
			message('删除成功',referer(), 'success');
		}elseif($operation == 'verify'){
			//审核用户
			$status = $_GPC['status'];
			$data   = array('status' => $status);
			pdo_update($this->member,$data,array('regionid'=>$regionid,'id'=>$id));
			message('操作成功！',referer(), 'success');
		}elseif ($operation == 'warrant') {
			//授权管理员操作
			$manage_status = $_GPC['manage_status'];
			pdo_query("update ".tablename($this->member)." set manage_status =".$manage_status." where id=".$id." and regionid=".$regionid);
			message('操作成功!',referer(),'success');
		}
		include $this->template('member');
	}
	//后台报修
	public function doWebRepair(){
		global $_GPC,$_W;
		$operation = $_GPC['op'];
		$regionid  = $_GPC['regionid'];
		$id = $_GPC['id'];
		$categories = pdo_fetchall("select * from".tablename($this->servicecategory)."where parentid = 3 and weid='{$_W['weid']}'");
		//print_r($categories);exit;
		// $categories = array(
  //   			'1'=>'水暖',
  //   			'2'=>'公共设施',
  //   			'3'=>'电器设施',
  //   			);
		//获取报修表单提交的数据
		$data =array(
			'status'      => $_GPC['status'],
			'requirement' => $_GPC['requirement'],
			'resolver'    => $_W['username'],
			'resolvetime' => $_W['timestamp'],
			'resolve'     => $_GPC['status'],
			);
		//报修来往回复提交的数据
		$insert = array(
			'weid'       => $_W['weid'],
			'openid'     => $_W['fans']['from_user'],
			'reportid'   => $id,
			'isreply'    => 1,
			'content'    => $_GPC['reply'],
			'createtime' => $_W['timestamp'],
			);
		$id = $_GPC['id'];
		if($operation == 'display'){
			if($_W['ispost']){
				//搜索
					$category  = $_GPC['category'];
					$type      = $_GPC['type'];
					$regionid  = $_GPC['regionid'];
					$starttime = strtotime($_GPC['starttime']);
					$endtime   = strtotime($_GPC['endtime']);
					$status = $_GPC['status'];
					if (!empty($starttime) && $starttime==$endtime) {
					$endtime = $endtime+86400-1;
					}
					if (!empty($status)) {
						$st   = trim((implode(',', $_GPC['status'])),',');
						$sql  = "select a.id,a.category,b.realname,b.mobile,a.content,a.createtime,a.status from".tablename($this->report)."as a left join".tablename($this->member)."as b on a.openid=b.openid where a.weid='{$_W['weid']}' and a.category="."'".$category."'"." and a.type =".$type." and a.regionid=".$regionid;
						$sql .= " and a.status in "."(".$st.")"." "." and a.createtime between ".$starttime." "."and ".$endtime;
					}else{
						$sql  = "select a.id,a.category,b.realname,b.mobile,a.content,a.createtime,a.status from".tablename($this->report)."as a left join".tablename($this->member)."as b on a.openid=b.openid where a.weid='{$_W['weid']}' and a.category="."'".$category."'"." and a.type =".$type." and a.regionid=".$regionid;
						$sql .= " and a.createtime between ".$starttime." "."and ".$endtime;
					}	
					//print_r($sql);exit;
				 	$list = pdo_fetchall($sql);
			}else{
				//显示报修记录
				$pindex = max(1, intval($_GPC['page']));
				$psize  = 5;
				$sql    = "select a.id,a.category,b.realname,b.mobile,a.content,a.createtime,a.status from".tablename($this->report)."as a left join".tablename($this->member)."as b on a.openid=b.openid where a.weid='{$_W['weid']}' and a.regionid=".$regionid." and a.type = 1 LIMIT ".($pindex - 1) * $psize.','.$psize;
				$list   = pdo_fetchall($sql);
				$total  = pdo_fetchcolumn('select count(*) from'.tablename($this->report)."as a left join".tablename($this->member)."as b on a.openid=b.openid where a.weid='{$_W['weid']}' and a.regionid=".$regionid." and a.type = 1");
				$pager  = pagination($total, $pindex, $psize);
			 }
		}elseif ($operation == 'post') {
			//查出对于ID的报修记录
			$sql   = "select a.thumb1,a.thumb2,a.requirement,a.id,a.category,b.realname,b.mobile,a.content,a.createtime,a.status from".tablename($this->report)."as a left join".tablename($this->member)."as b on a.openid=b.openid where a.weid='{$_W['weid']}' and a.regionid=".$regionid." and a.id=".$id;
			$it    = pdo_fetch($sql);//print_r($item);
			$sql_1 = "select * from ".tablename($this->reply)."where reportid=".$id;
			$reply = pdo_fetchall($sql_1);
			//把报修记录和reply记录组成一个新的数组
			 $item = array();
			 $item = array(
					'id'          =>$it['id'] ,
					'requirement' =>$it['requirement'],
					'category'    =>$it['category'],
					'realname'    =>$it['realname'],
					'content'     =>$it['content'],
					'createtime'  =>$it['createtime'],
					'status'      =>$it['status'],
					'reply'       =>$reply,
					'thumb1'      =>$it['thumb1'],
					'thumb2'      =>$it['thumb2'],
			 	 );
			if ($_W['ispost']) {
				pdo_update($this->report,$data,array('id'=>$id));
				pdo_insert($this->reply,$insert);
				message('更新成功!',referer(),'success');
			}
		}elseif ($operation == 'delete') {
			pdo_delete($this->report,array('weid'=>$_W['weid'],'id'=>$id));
			message('删除成功！',referer(),'success');
		}
		include $this->template('repair');
	}
	//后台常用号码
	public function doWebPhone(){
		global $_GPC,$_W;
		$id = $_GPC['id'];
		$op = $_GPC['op'];
		if(checksubmit('submit')){
			//常用电话添加和修改
			for ($i=0; $i <count($_GPC['titles']) ; $i++) { 
					$ids       = $_GPC['ids'];
					$insert    = array(
						'title'    =>  $_GPC['titles'][$i] ,
						'weid'     =>  $_W['weid'],
						'phone'    =>  $_GPC['phones'][$i],
					);
				if($ids[$i] !=NULL){
					pdo_update($this->phone,$insert,array('id'=>$ids[$i]));
				}else{
					pdo_insert($this->phone,$insert);
				}
			}
			message('更新信息成功',referer(), 'success');
		}
		if ($op == 'delete') {
			//常用号码删除
			pdo_delete($this->phone,array('id'=>$id));
		}
		
		//常用号码显示
		$pindex = max(1, intval($_GPC['page']));
		$psize  = 10;
		$sql    = "select * from ".tablename($this->phone)."where weid = '{$_W['weid']}' LIMIT ".($pindex - 1) * $psize.','.$psize;
		$phones = pdo_fetchall($sql);
		$total  = pdo_fetchcolumn('select count(*) from'.tablename($this->phone)."where  weid = '{$_W['weid']}' ");
		$pager  = pagination($total, $pindex, $psize);
		
		include $this->template('phone');
	}
	//后台投诉
	public function doWebReport(){
		global $_W,$_GPC;
		$operation = $_GPC['op'];
		$regionid  = $_GPC['regionid'];
		$id        = $_GPC['id'];
		// $categories = array(
  //   			'1'=>'投诉类型1',
  //   			'2'=>'投诉类型2',
  //   			'3'=>'投诉类型3',
  //   			);
		
		$categories = pdo_fetchall("select * from".tablename($this->servicecategory)."where parentid = 4 and weid='{$_W['weid']}'");
	
		if($operation == 'display'){
			if($_W['ispost']){
				//搜索
				$category  = $_GPC['category'];
				$type      = 2;
				$regionid  = $_GPC['regionid'];
				$starttime = strtotime($_GPC['starttime']);
				$endtime   = strtotime($_GPC['endtime']);
				if (!empty($starttime) && $starttime==$endtime) {
					$endtime = $endtime+86400-1;
				}
				$status = $_GPC['status'];
				if(!empty($status)){
					$st   = trim((implode(',', $_GPC['status'])),',');
					//还无法修复,就是下面2条SQL

					$sql  = "select a.id,a.category,b.realname,b.mobile,a.content,a.createtime,a.status,a.resolver,a.resolve,a.resolvetime from".tablename($this->report)."as a left join".tablename($this->member)."as b on a.openid=b.openid where a.weid='{$_W['weid']}' and a.category='{$category}' and a.type ='{$type}' and a.regionid='{$regionid}'";
					$sql .= "and a.status in "."(".$st.")"." "." and a.createtime between ".$starttime." "."and ".$endtime;
					
				}else{
					$sql  = "select a.id,a.category,b.realname,b.mobile,a.content,a.createtime,a.status,a.resolver,a.resolve,a.resolvetime from".tablename($this->report)."as a left join".tablename($this->member)."as b on a.openid=b.openid where a.weid='{$_W['weid']}' and a.category='{$category}' and a.type ='{$type}' and a.regionid='{$regionid}' and a.createtime between ".$starttime." "."and ".$endtime;
				
				}
				$list = pdo_fetchall($sql);
			}else{
			//显示投诉记录
				$pindex = max(1, intval($_GPC['page']));
				$psize  = 5;
				$sql    = "select a.id,a.category,b.realname,b.mobile,a.content,a.createtime,a.status,a.resolver,a.resolve,a.resolvetime from".tablename($this->report)."as a left join".tablename($this->member)."as b on a.openid=b.openid where a.weid='{$_W['weid']}' and a.regionid=".$regionid." and a.type = 2 LIMIT ".($pindex - 1) * $psize.','.$psize;
				$list   = pdo_fetchall($sql);
				$total  = pdo_fetchcolumn('select count(*) from'.tablename($this->report)."as a left join".tablename($this->member)."as b on a.openid=b.openid where a.weid='{$_W['weid']}' and a.regionid=".$regionid." and a.type = 2");
				$pager  = pagination($total, $pindex, $psize);
			}
		}elseif ($operation == 'post') {
			//对应ID的投诉记录查看
			$sql  = "select a.id,a.category,b.realname,b.mobile,a.content,a.createtime,a.status,a.resolver,a.resolve,a.resolvetime from".tablename($this->report)."as a left join".tablename($this->member)."as b on a.openid=b.openid where a.weid='{$_W['weid']}' and a.regionid=".$regionid." and a.id=".$id;
			$item = pdo_fetch($sql);
			if($_W['ispost']){
				if (!empty($_GPC['resolve'])) {
					$resolver = empty($_GPC['resolver'])?$_W['username']:$_GPC['resolver'];
					$data = array(
					'status'      => 1,
					'resolve'     => $_GPC['resolve'],
					'resolver'    => $resolver,
					'resolvetime' => $_W['timestamp'],
					);
				pdo_update($this->report,$data,array('id'=>$id));
				message('处理成功！',referer(),'success');
				}		
			}
		}elseif($operation == 'delete'){
			pdo_delete($this->report,array('weid'=>$_W['weid'],'id' =>$id));
			message('删除成功！',referer(),'success');
		}
		include $this->template('report');
	}
	//后台分类添加
	public function doWebServiceCategory(){
		global $_GPC,$_W;
		$operation = !empty($_GPC['op']) ? $_GPC['op']:'display'; 
		$parentid  = $_GPC['parentid'];
		$id = $_GPC['id'];
		//echo $parentid;
		if ($operation == 'post') {
			//编辑分类信息
			if (!empty($id)) {
				$sql = "select * from".tablename($this->servicecategory)."where weid='{$_W['weid']}' and id=".$id;
				$category = pdo_fetch($sql);
			}
			//添加分类主ID
			if (!empty($parentid)) {
				$sql = "select name from".tablename($this->servicecategory)."where id=".$parentid;
				$parent = pdo_fetch($sql);
			}
			//提交
			if (checksubmit('submit')) {
				$data = array(
					'name'         => $_GPC['catename'],
					'parentid'     => 0,
					'displayorder' => $_GPC['displayorder'],
					'description'  => $_GPC['description'],
					'enabled'      => 1,
					'weid'         => $_W['weid'],
					);
				if (empty($parentid)) {
					if (empty($id)) {
						//添加主类
						pdo_insert($this->servicecategory,$data);
					}else{
						//更新
						$data['displayorder'] = $_GPC['displayorder'];
						$data['name']         = $_GPC['catename'];
						$data['description']  = $_GPC['description'];
						pdo_update($this->servicecategory,$data,array('id'=>$id,'weid'=>$_W['weid']));
					}					
				}else{
					//添加子类
					if(empty($id)){
							$data['parentid'] = $parentid;
							pdo_insert($this->servicecategory,$data);
					}else{
						//更新子类
						$data['parentid'] = $parentid;
						$data['displayorder'] = $_GPC['displayorder'];
						$data['name']         = $_GPC['catename'];
						$data['description']  = $_GPC['description'];
						pdo_update($this->servicecategory,$data,array('id'=>$id,'weid'=>$_W['weid']));
					}
				
				}
				message('更新成功',referer(),'success');
			}
		}elseif($operation == 'display'){
			//显示全部分类信息
			$sql      = "select * from".tablename($this->servicecategory)."where parentid= 0 ";
			$category = pdo_fetchall($sql);
			//print_r($category);
			$children = array();
			foreach ($category as $key => $value) {
				$sql  = "select *from".tablename($this->servicecategory)."where weid='{$_W['weid']}' and  parentid=".$value['id'];
				$list = pdo_fetchall($sql);
				//print_r($list);
				$children[$value['id']] = $list;
			}
				//print_r($children);
		}elseif ($operation == 'delete') {
			//删除分类信息
			pdo_delete($this->servicecategory,array('weid'=>$_W['weid'],'id'=>$id));
			message('删除成功',referer(),'success');
		}
		include $this->template('servicecategory');
	}
	//后台家政服务
	public function doWebHomemaking(){
		global $_GPC,$_W;
		$operation = $_GPC['op'];
		$regionid  = $_GPC['regionid'];
		$id        = $_GPC['id'];
		if ($operation == 'display') {
			if ($_W['ispost']) {
				//搜索
				$servicesmallcategory = $_GPC['servicesmallcategory'];
				$status               = $_GPC['status'];
				$starttime            = strtotime($_GPC['starttime']);
				$endtime              = strtotime($_GPC['endtime']);
				if (!empty($starttime) && $starttime==$endtime) {
					$endtime = $endtime+86400-1;
				}
				if (!empty($status)) {
					$st         = trim(implode(',', $status),',');
					$sql        = "select * from".tablename($this->service)."where weid='{$_W['weid']}' and servicecategory = 1 and regionid=".$regionid;
					$sql       .= " and servicesmallcategory=".$servicesmallcategory." and createtime between ".$starttime." and ".$endtime;
					$sql       .= " and status in"."(".$st.")";
					$list       = pdo_fetchall($sql);
					$sql_1      = "select * from ".tablename($this->servicecategory)."where parentid=1";
					$categories = pdo_fetchall($sql_1);
					$members    = array();
					foreach ($list as $key => $value) {
						$openid           = $value['openid'];
						$sql_2            = "select * from ".tablename($this->member)."where openid="."'".$openid."'";
						$member           = pdo_fetch($sql_2);
						$members[$openid] = $member;
					}
				}else{
					$sql        = "select * from".tablename($this->service)."where weid='{$_W['weid']}' and servicecategory = 1 and regionid=".$regionid;
					$sql       .= " and servicesmallcategory=".$servicesmallcategory." and createtime between ".$starttime." and ".$endtime;
					$list       = pdo_fetchall($sql);
					$sql_1      = "select * from ".tablename($this->servicecategory)."where parentid=1";
					$categories = pdo_fetchall($sql_1);
					$members    = array();
					foreach ($list as $key => $value) {
						$openid           = $value['openid'];
						$sql_2            = "select * from ".tablename($this->member)."where openid="."'".$openid."'";
						$member           = pdo_fetch($sql_2);
						$members[$openid] = $member;
					}
				}
			}else{
				$pindex = max(1, intval($_GPC['page']));
				$psize = 5;
				$sql        = "select * from ".tablename($this->service)."where weid = '{$_W['weid']}' and servicecategory = 1 and regionid=".$regionid." LIMIT ".($pindex - 1) * $psize.','.$psize;
				$list       = pdo_fetchall($sql);
				$total = pdo_fetchcolumn('select count(*) from'.tablename($this->service)."where weid = '{$_W['weid']}' and servicecategory = 1 and regionid=".$regionid);
				$pager = pagination($total, $pindex, $psize);
				$sql_1      = "select * from ".tablename($this->servicecategory)."where parentid=1";
				$categories = pdo_fetchall($sql_1);
				$members    = array();
				foreach ($list as $key => $value) {
					$openid           = $value['openid'];
					$sql_2            = "select * from ".tablename($this->member)."where openid="."'".$openid."'";
					$member           = pdo_fetch($sql_2);
					$members[$openid] = $member;
				}
			}
		}elseif($operation == 'post'){
			//编辑
			$sql        = "select * from".tablename($this->service)."where weid='{$_W['weid']}' and id=".$id." and $regionid=".$regionid;
			$item       = pdo_fetch($sql);
			$sql_1      = "select * from ".tablename($this->servicecategory)."where parentid=1";
			$categories = pdo_fetchall($sql_1);
			$sql_2      = "select * from".tablename($this->member)."where openid="."'".$item['openid']."'";
			$member     = pdo_fetch($sql_2);
			
			if(checksubmit('submit')){
				$data = array(
				'status'               => $_GPC['status'],
				'servicesmallcategory' => $_GPC['servicesmallcategory'],
				'contacttype'          => $_GPC['contacttype'],
				'requirement'          => $_GPC['requirement'],
				'remark'               => $_GPC['remark'],
				);
				pdo_update($this->service,$data,array('id' => $id,'weid' => $_W['weid']));
				message('修改成功',$this->createWebUrl('homemaking',array('op'=>'display','regionid'=>$regionid)),'success');
			}
		}elseif ($operation == 'delete') {
			//删除
    		pdo_delete($this->service,array('id' => $id));
    		message('家政服务信息删除成功。',referer(),'success');
		}
		include $this->template('homemaking');
	}
	//后台房屋租赁
	public function doWebHouselease(){
		global $_GPC,$_W;
		$operation = $_GPC['op'];
		$regionid  = $_GPC['regionid'];
		$id        = $_GPC['id'];
		if ($operation == 'display') {
			if ($_W['ispost']) {
				//搜索
				$servicesmallcategory = $_GPC['servicesmallcategory'];
				$status               = $_GPC['status'];
				$starttime            = strtotime($_GPC['starttime']);
				$endtime              = strtotime($_GPC['endtime']);
				if (!empty($starttime) && $starttime==$endtime) {
					$endtime = $endtime+86400-1;
				}
				if (!empty($status)) {
					$st         = trim(implode(',', $status),',');
					$sql        = "select * from".tablename($this->service)."where weid='{$_W['weid']}' and servicecategory = 2 and regionid=".$regionid;
					$sql       .= " and servicesmallcategory=".$servicesmallcategory." and createtime between ".$starttime." and ".$endtime;
					$sql       .= " and status in"."(".$st.")";
					$list       = pdo_fetchall($sql);
					$sql_1      = "select * from ".tablename($this->servicecategory)."where parentid=2";
					$categories = pdo_fetchall($sql_1);
					$members    = array();
					foreach ($list as $key => $value) {
						$openid           = $value['openid'];
						$sql_2            = "select * from ".tablename($this->member)."where openid="."'".$openid."'";
						$member           = pdo_fetch($sql_2);
						$members[$openid] = $member;
					}
				}else{
					$sql        = "select * from".tablename($this->service)."where weid='{$_W['weid']}' and servicecategory = 2 and regionid=".$regionid;
					$sql       .= " and servicesmallcategory=".$servicesmallcategory." and createtime between ".$starttime." and ".$endtime;
					$list       = pdo_fetchall($sql);
					$sql_1      = "select * from ".tablename($this->servicecategory)."where parentid=2";
					$categories = pdo_fetchall($sql_1);
					$members    = array();
					foreach ($list as $key => $value) {
						$openid           = $value['openid'];
						$sql_2            = "select * from ".tablename($this->member)."where openid="."'".$openid."'";
						$member           = pdo_fetch($sql_2);
						$members[$openid] = $member;
					}
				}
			}else{
					$pindex = max(1, intval($_GPC['page']));
					$psize  = 5;
					$sql    = "select * from ".tablename($this->service)."where weid = '{$_W['weid']}' and servicecategory = 2 and regionid=".$regionid." LIMIT ".($pindex - 1) * $psize.','.$psize;
					$list   = pdo_fetchall($sql);
					$total  = pdo_fetchcolumn('select count(*) from'.tablename($this->service)."where weid = '{$_W['weid']}' and servicecategory = 2 and regionid=".$regionid);
					$pager  = pagination($total, $pindex, $psize);
					$sql_1      = "select * from ".tablename($this->servicecategory)."where parentid=2";
					$categories = pdo_fetchall($sql_1);
					$members    = array();
					foreach ($list as $key => $value) {
						$openid           = $value['openid'];
						$sql_2            = "select * from ".tablename($this->member)."where openid="."'".$openid."'";
						$member           = pdo_fetch($sql_2);
						$members[$openid] = $member;
					}
				}
		}elseif($operation == 'post'){
			//编辑
			$sql        = "select * from".tablename($this->service)."where weid='{$_W['weid']}' and id=".$id." and $regionid=".$regionid;
			$item       = pdo_fetch($sql);
			$sql_1      = "select * from ".tablename($this->servicecategory)."where parentid=2";
			$categories = pdo_fetchall($sql_1);
			$sql_2      = "select * from".tablename($this->member)."where openid="."'".$item['openid']."'";
			$member     = pdo_fetch($sql_2);
			
			if(checksubmit('submit')){
				$data = array(
				'status'               => $_GPC['status'],
				'servicesmallcategory' => $_GPC['servicesmallcategory'],
				'contacttype'          => $_GPC['contacttype'],
				'requirement'          => $_GPC['requirement'],
				'remark'               => $_GPC['remark'],
				);
				pdo_update($this->service,$data,array('id' => $id,'weid' => $_W['weid']));
				message('修改成功',$this->createWebUrl('houselease',array('op'=>'display','regionid'=>$regionid)),'success');
			}
		}elseif ($operation == 'delete') {
			//删除
    		pdo_delete($this->service,array('id' => $id));
    		message('房屋租赁信息删除成功。',referer(),'success');
		}
		include $this->template('houselease');
	}
	//后台用户管理
	public function doWebManager(){
		global $_GPC,$_W;
		$op = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
		if($op == 'display'){
			
		}
		include $this->template('manager');
	}
	//后台广告管理
	public function doWebAdvertisement(){
		global $_GPC,$_W;
		$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';

		include $this->template('advertisement');
	}
	//后台物业团队介绍
	public  function doWebProperty(){
		global $_GPC,$_W;
		$operation = !empty($_GPC['op'])?$_GPC['op']:'display';
		$id        = intval($_GPC['id']);
		if ($operation == 'post') {
			if (!empty($id)) {
				$sql = "select * from".tablename($this->property)."where weid='{$_W['weid']}' and id=".$id;
				$item = pdo_fetch($sql);
			}
			$data = array(
					'weid'       => $_W['weid'],
					'title'      => $_GPC['title'],
					'mcommunity' => $_GPC['mcommunity'],
					'content'    => htmlspecialchars_decode($_GPC['content']),
					'createtime' => $_W['timestamp'],
				);
			
			if (!empty($_FILES['topPicture']['tmp_name'])) {
					file_delete($_GPC['topPicture-old']);
					$upload = file_upload($_FILES['topPicture']);
					if (is_error($upload)) {
						message($upload['message'], '', 'error');
					}
					$data['topPicture'] = $upload['path'];
				}
			if ($_W['ispost']) {
				if (empty($id)) {
					//echo strip_tags($_GPC['content']);exit;
					pdo_insert($this->property,$data);
					message('添加成功',$this->createWebUrl('property',array('op' => 'display')),'success');
				}else{
					pdo_update($this->property,$data,array('id' => $id));
					message('修改成功',referer(),'success');
				}
			}
		}elseif ($operation == 'display') {
			$sql = "select * from".tablename($this->property)."where weid='{$_W['weid']}'";
			$list = pdo_fetchall($sql);
		}elseif ($operation == 'delete') {
			pdo_delete($this->property,array('id' => $id));
			message('删除成功',$this->createWebUrl('property',array('op' => 'post')),'success');
		}

		include $this->template('property');
	}
	//后台导航扩展
	public function doWebnavExtension(){
		global $_W,$_GPC;
		$op = !empty($_GPC['op'])?$_GPC['op']:'display';
		$id = intval($_GPC['id']);
		if($op == 'post'){
			if (!empty($id)) {
				$sql  = "select * from".tablename($this->navExtension)."where id=".$id;
				$item = pdo_fetch($sql);
			}
			$data = array(
				'weid'    => $_GPC['weid'],
				'title'   => $_GPC['title'],
				'navurl'  => $_GPC['navurl'],
				'icon'    => $_GPC['icon'],
				'content' => $_GPC['content'],
			);
			if ($_W['ispost']) {
				if (empty($id)) {
					pdo_insert($this->navExtension,$data);
					message('添加成功',referer(),'success');
				}else{
					pdo_update($this->navExtension,$data,array('id' => $id));
					message('更新成功',referer(),'success');
				}
			}
		}elseif($op == 'display'){
			$pindex = max(1, intval($_GPC['page']));
			$psize  = 10;
			$sql    = "select * from".tablename($this->navExtension)."where weid='{$_W['weid']}' LIMIT ".($pindex - 1) * $psize.','.$psize;
			$list   = pdo_fetchall($sql);
			$total  = pdo_fetchcolumn('select count(*) from'.tablename($this->navExtension)."where weid='{$_W['weid']}'");
			$pager  = pagination($total, $pindex, $psize);
		}elseif ($op == 'delete') {
			pdo_delete($this->navExtension,array('id' => $id));
			message('删除成功',referer(),'success');
		}
		include $this->template('navExtension');
	}	
	//后台幻灯片设置
	public function doWebSlide(){
		global $_W,$_GPC;
		$op = !empty($_GPC['op'])?$_GPC['op']:'display';
		if ($op == 'display') {
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;
		$condition = '';
		$params = array();
		if (!empty($_GPC['keyword'])) {
			$condition .= " AND title LIKE :keyword";
			$params[':keyword'] = "%{$_GPC['keyword']}%";
		}

		$list = pdo_fetchall("SELECT * FROM ".tablename($this->comslide)." WHERE weid = '{$_W['weid']}' $condition ORDER BY displayorder DESC, id DESC LIMIT ".($pindex - 1) * $psize.','.$psize, $params);
		$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->comslide) . " WHERE weid = '{$_W['weid']}' $condition");
		$pager = pagination($total, $pindex, $psize);

		} elseif ($op == 'post') {
			$id = intval($_GPC['id']);
			if (!empty($id)) {
				$item = pdo_fetch("SELECT * FROM ".tablename($this->comslide)." WHERE id = :id" , array(':id' => $id));
				if (empty($item)) {
					message('抱歉，幻灯片不存在或是已经删除！', '', 'error');
				}
			}
			if (checksubmit('submit')) {
				if (empty($_GPC['title'])) {
					message('标题不能为空，请输入标题！');
				}
				$data = array(
					'weid' => $_W['weid'],
					'title' => $_GPC['title'],
					'url' => $_GPC['url'],
					'displayorder' => intval($_GPC['displayorder']),
				);
				if (!empty($_GPC['thumb'])) {
					$data['thumb'] = $_GPC['thumb'];
					file_delete($_GPC['thumb-old']);
				}
				if (empty($id)) {
					pdo_insert($this->comslide, $data);
				} else {
					pdo_update($this->comslide, $data, array('id' => $id));
				}
				message('幻灯片更新成功！', $this->createWebUrl('slide',array('op' => 'display')), 'success');
			}
		} elseif ($op == 'delete') {
			$id = intval($_GPC['id']);
			$row = pdo_fetch("SELECT id, thumb FROM ".tablename($this->comslide)." WHERE id = :id", array(':id' => $id));
			if (empty($row)) {
				message('抱歉，幻灯片不存在或是已经被删除！');
			}
			if (!empty($row['thumb'])) {
				file_delete($row['thumb']);
			}
			pdo_delete($this->comslide, array('id' => $id));
			message('删除成功！', referer(), 'success');
		}
		include $this->template('slide');
	}
	//前台会员修改解绑
	public function doMobileMember(){
		global $_GPC,$_W;
			
			$op = $_GPC['op'];
			$title = '个人信息';
			$types = array(
	    		'1'=>'业主',
	    		'2'=>'租户',
	    		);
		
				$sql    = "select * from ".tablename($this->member)." where openid='{$_W['fans']['from_user']}'";
				$member = pdo_fetch($sql);
				if (empty($member['status'])) {
					$url1 = $this->createMobileUrl('home');
					$url  = $_W['siteroot'].$url1;
						//echo $url ;
					header("Location:$url");exit;
				}else{
					$sql = "select * from ".tablename($this->region)."where weid='{$_W['weid']}'";
			    	$regions = pdo_fetchall($sql);
					if(checksubmit('submit')){
						$update = array('type' => $_GPC['type'], 
									'realname'   =>$_GPC['realname'],
									'mobile'     => $_GPC['mobile'],
									'address'    => $_GPC['address'],
									'remark'     => $_GPC['remark'],
									'weid'       => $_W['weid'],
									'openid'     => $_W['fans']['from_user'],
									'createtime' => $_W['timestamp'],
									'status'     => 1,
						);
						if(!empty($_GPC['id'])){			
					    	pdo_update($this->member,$update,array('openid' => $_W['fans']['from_user']));
					    	message('更新成功！',$this->createMobileUrl('home'),'success');
				    	}
				    	 //解绑信息
					    if($op =='unbind'){
					    	$data = array('status'=> 0);
					    	//print_r($data);exit;
					    	pdo_update($this->member,$data,array('openid' => $_W['fans']['from_user'],'regionid'=>$_GPC['regionid'],'weid'=>$_W['weid']));
					    	message('解绑成功！',$this->createMobileUrl('home'),'success');
					    }

			    	}		  
				}

	
				include $this->template('member');

	}
	//前台手机首页
    public function doMobileHome(){
    	global $_GPC,$_W;
    		
			$title        = $_W['account']['name'];
			$sql_1        = "select * from ".tablename($this->member)."where openid='{$_W['fans']['from_user']}'";
			$member       = pdo_fetch($sql_1);
			//导航
			$sql_2        = "select * from".tablename($this->navExtension)."where weid='{$_W['weid']}'";
			$navExtension = pdo_fetchall($sql_2);
	    	include $this->template('home');
 
    }
    //前台手机住户注册页面
    public function doMobileRegister(){
    	global $_GPC,$_W;
    	WeSession::$expire = 600;
    	WeSession::start();
		
    	//判断有没有开启短信注册
		$sms           = pdo_fetch("select * from".tablename($this->set)." where weid='{$_W['weid']}'");
		$sms_status    = $sms['sms_status'];
		$sms_resgister = $sms['sms_resgister'];
		//判断验证码是否正确
		if($sms_status == 1 && $sms_resgister==1 ){
	    	if($_W['ispost'] && empty($_SESSION['isstatus'])){
					
					$verifycode = $_GPC['verifycode'];
					//echo $_SESSION['code']."---".$verifycode;
					if($verifycode == $_SESSION['code']){
						$_SESSION['isstatus']=1;
						//$phone = $_SESSION['mobile'];
						message('验证成功！',$this->createMobileUrl('register'),'success');
						
					}else{
						message('验证码失效,请重新获取！',referer(),'success');
					}
			}
		}
		

		// print_r($_SESSION);
		//  exit;
		//个人信息
	    	$title = '用户注册';
	    	//获取小区信息
			$sql     = "select * from ".tablename($this->region)."where weid='{$_W['weid']}'";
			$regions = pdo_fetchall($sql);
	    	//print_r($regions);
	    	$types = array(
	    		'1'=>'业主',
	    		'2'=>'租户',
	    		);
	    	if(checksubmit('submit')){
	    		//获取当前选中的小区名称
				$sql_1 = "select title from".tablename($this->region)."where weid='{$_W['weid']}' and id=".$_GPC['regionid'];
				$item  = pdo_fetch($sql_1);
	    		//print_r($item);exit;

	    		//status=1  显示注册成功， status=0 解绑
	    		$insert =array(
					'weid'          =>$_W['weid'],
					'type'          =>$_GPC['type'],
					'realname'      =>$_GPC['realname'],
					'regionid'      =>$_GPC['regionid'],
					'openid'        =>$_W['fans']['from_user'],
					'mobile'        =>$_GPC['mobile'],
					'regionname'    =>$item['title'],
					'address'       =>$_GPC['address'],
					'remark'        =>$_GPC['remark'],
					'status'        =>1,
					'manage_status' =>0,
					'createtime'    =>$_W['timestamp'],
	    		);
	    		//判断是否存在用户
				$sql_2 = "select * from ".tablename($this->member)."where openid='{$_W['fans']['from_user']}'";
				
				$item  = pdo_fetch($sql_2);
				//print_r($item);
	    		if(!empty($item)){
	    			pdo_update($this->member,$insert,array('openid'=> $_W['fans']['from_user']));
	    		}else{
	    				//print_r($insert);exit;
	    				pdo_insert($this->member,$insert);
	    			
	    		}
	    		message('账户绑定成功,您可以正常操作提供的物业服务！',$this->createMobileUrl('home'),'success');
	    	}
	    	include $this->template('register');
		 
    }
    //前台手机公告页面
    public function doMobileAnnouncement(){
    	global $_GPC,$_W;
 			
	    	$title  = '小区公告';
	    	$op = !empty($_GPC['op'])?$_GPC['op']:'display';
	    	$id = intval($_GPC['id']);
	    	if($op == 'display'){
	    		
				$sql_1 = "select * from ".tablename($this->member)."where openid='{$_W['fans']['from_user']}'";
				$it    = pdo_fetch($sql_1);
				if(empty($it['status'])){
					$url1 = $this->createMobileUrl('home');
					$url = $_W['siteroot'].$url1;
					//echo $url ;
					header("Location:$url");exit;
				}else{
					//是否是管理员操作
					$sql_1 = "select manage_status from".tablename($this->member)."where openid = '{$_W['fans']['from_user']}'";
					$row = pdo_fetch($sql_1);
					//显示公告列表
					$pindex = max(1, intval($_GPC['page']));
					$psize = 10;
					if(empty($row['manage_status'])){
						$sql   = "select * from ".tablename($this->announcement)."where weid='{$_W['weid']}' and status = 1 and regionid=".$it['regionid']." LIMIT ".($pindex - 1) * $psize.','.$psize;
					}else{
						$sql   = "select * from ".tablename($this->announcement)."where weid='{$_W['weid']}' and regionid=".$it['regionid']." LIMIT ".($pindex - 1) * $psize.','.$psize;
					}
					$list  = pdo_fetchall($sql);
					if(empty($row['manage_status'])){
						$total = pdo_fetchcolumn('select count(*) from'.tablename($this->announcement)."where weid='{$_W['weid']}' and status = 1 and regionid=".$it['regionid']);
					}else{
						$total = pdo_fetchcolumn('select count(*) from'.tablename($this->announcement)."where weid='{$_W['weid']}' and regionid=".$it['regionid']);
					}
					$pager = pagination($total, $pindex, $psize);
					
					//print_r($item);
				}
	    	}elseif($op =='detail'){
				// $id    = $_GPC['id'];
				$sql_2 = "select * from ".tablename($this->announcement)."where weid='{$_W['weid']}' and id =".$id;
				$item  = pdo_fetch($sql_2);	
	    	}elseif ($op == 'delete') {
	    		pdo_delete($this->announcement,array('id' => $id ,'weid' => $_W['weid']));
	    		message('删除成功',referer(),'success');
	    	}elseif ($op == 'update') {
	    		//添加更新公告
	    		if(!empty($id)){
		    		$sql = "select * from".tablename($this->announcement)."where id=".$id;
		    		$item = pdo_fetch($sql);
	    		}
	    		//查小区编号
	    		$sql_2 = "select regionid from".tablename($this->member)." where openid='{$_W['fans']['from_user']}'";
	    		$region = pdo_fetch($sql_2);
	    		$data = array(
						'weid'       =>$_W['weid'],
						'regionid'   =>$region['regionid'],
						'title'      =>$_GPC['title'],
						'content'    =>htmlspecialchars_decode($_GPC['content']),
						'createtime' =>$_W['timestamp'],
						// 'starttime'  =>strtotime($_GPC['starttime']),
						// 'endtime'    =>strtotime($_GPC['endtime']),
						'status'     =>$_GPC['status'],
						'author'     =>$_W['account']['name'],
	    			);
	    		//print_r($data);exit;
	    		if($_W['ispost']){
	    			if (empty($id)) {
	    				pdo_insert($this->announcement,$data);
	    				message('发布成功',$this->createMobileUrl('announcement',array('op' => 'display' )),'success');
	    			}else{
			    		pdo_update($this->announcement,$data,array('id' => $id,'weid' => $_W['weid'] ));
			    		message('更新成功',$this->createMobileUrl('announcement',array('op' => 'display')),'success');
	    			}
	    		}
	    	}elseif($op == 'verify'){
	    		//公告状态
	    		$status = $_GPC['status'];
	    		$sql = "update".tablename($this->announcement)." set status=".$status." where id =".$id." and weid='{$_W['weid']}'";
	    		//print_r($sql);exit;
	    		pdo_query($sql);
	    		message('操作成功',referer(),'success');
	    	}
	    	include $this->template('announcement');
  
    }
    //前台手机常用电话页面
    public function doMobilePhone(){
    	global $_GPC,$_W;
    	
	    	$title = '便民号码';
			$sql_1  = "select * from ".tablename($this->member)."where openid='{$_W['fans']['from_user']}'";
			$it     = pdo_fetch($sql_1);
			if(empty($it['status'])){
					$url1 = $this->createMobileUrl('home');
					$url  = $_W['siteroot'].$url1;
						//echo $url ;
					header("Location:$url");exit;
			}else{
					$pindex = max(1, intval($_GPC['page']));
					$psize = 10;
					$sql    = "select * from ".tablename($this->phone)."where weid='{$_W['weid']}' LIMIT ".($pindex - 1) * $psize.','.$psize;
					$phones = pdo_fetchall($sql);
					$total = pdo_fetchcolumn('select count(*) from'.tablename($this->phone)."where weid='{$_W['weid']}'");
					$pager = pagination($total, $pindex, $psize);
			}
	    	include $this->template('phone');
    
    }
    //前台帮助中心
    public function doMobileHelp(){
    	global $_W,$_GPC;
	    	$title = '帮助中心';
	    	include $this->template('help');
    
    }
    //前台报修
    public function doMobileRepair(){
    	global $_GPC,$_W;
	    	$title = '报修服务';
	    	$op = !empty($_GPC['op'])?$_GPC['op']:'display';
	    	// $categories = array(
	    	// 		'1'=>'水暖',
	    	// 		'2'=>'公共设施',
	    	// 		'3'=>'电器设施',
	    	// 		);
	    	$categories = pdo_fetchall("select * from".tablename($this->servicecategory)."where parentid = 3 and weid='{$_W['weid']}'");
	    		//查小区编号
				$sql_1 = "select * from ".tablename($this->member)."where openid='{$_W['fans']['from_user']}'";
				$it    = pdo_fetch($sql_1);
				if(empty($it['status'])){
	    			$url1 = $this->createMobileUrl('home');
					$url  = $_W['siteroot'].$url1;
						//echo $url ;
					header("Location:$url");exit;
	    		}
	    		$data  = array(
					'openid'      => $_W['fans']['from_user'],
					'weid'        => $_W['weid'],
					'regionid'    => $it['regionid'],
					'type'        => 1,
					'category'    => $_GPC['category'],
					'content'     => $_GPC['content'],
					'createtime'  => $_W['timestamp'],
					'status'      => 0,
					'rank'        => 0,
					'comment'     => 0,
					'requirement' => '无',
					'resolve'     => '',
					'resolver'    => '',
					'resolvetime' => '',
	    			);
	    		//处理图片1
	    		if (!empty($_FILES['thumb1']['tmp_name'])) {
					file_delete($_GPC['thumb1-old']);
					$upload = file_upload($_FILES['thumb1']);
					if (is_error($upload)) {
						message($upload['message'], '', 'error');
					}
					$data['thumb1'] = $upload['path'];
				}
					
	    		//处理图片2
	    		if (!empty($_FILES['thumb2']['tmp_name'])) {
					file_delete($_GPC['thumb2-old']);
					$upload = file_upload($_FILES['thumb2']);
					if (is_error($upload)) {
						message($upload['message'], '', 'error');
					}
					$data['thumb2'] = $upload['path'];
				}
				//无线打印
    		 	$set = pdo_fetch("SELECT * FROM".tablename($this->set)."WHERE weid='{$_W['weid']}'");
    			if(!empty($set['print_status'])){
    				if (empty($set['print_type']) || $set['print_type'] == '2') {
    					$data['print_sta'] = -1;
    				}
    			}	
	    		//处理图片3
	   //  		if (!empty($_FILES['thumb3']['tmp_name'])) {
				// 	file_delete($_GPC['thumb3-old']);
				// 	$upload = file_upload($_FILES['thumb3']);
				// 	if (is_error($upload)) {
				// 		message($upload['message'], '', 'error');
				// 	}
				// 	$data['thumb3'] = $upload['path'];
				// }
				
	    	if($op == 'post'){
	    		
		    		if (checksubmit('submit')) {
		    		
		    			pdo_insert($this->report,$data);
		    			$id = pdo_insertid();
		    			//短信提醒
		    			$con = $_GPC['content'];
		    			$this->Resms($con);
		    			
		    			message('报修申请提交成功,请查看"我的报修"等待工作人员联系。',$this->createMobileUrl('repair',array('op'=>'post')),'success');
		    		}
	    		
	    	}elseif ($op == 'display') {
	    			$pindex = max(1, intval($_GPC['page']));
					$psize = 2;
		    		//通过Id查出回复记录，在一起组装一个新的二维数组
					$sql   = "select * from ".tablename($this->report)."where openid='{$_W['fans']['from_user']}' and weid='{$_W['weid']}' and type=1 LIMIT ".($pindex - 1) * $psize.','.$psize;
					$li    = pdo_fetchall($sql);
					$total = pdo_fetchcolumn('select count(*) from'.tablename($this->report)."where openid='{$_W['fans']['from_user']}' and weid='{$_W['weid']}' and type=1 ");
					$pager = pagination($total, $pindex, $psize);
					$list  = array();
					foreach ($li as $key => $it) {
						$sql_1 = 'select * from'.tablename($this->reply)."where weid='{$_W['weid']}' and reportid=".$it['id'];
						$reply = pdo_fetchall($sql_1);
						$list[] = array(
							'category'   => $it['category'],
							'content'    => $it['content'],
							'createtime' => $it['createtime'],
							'status'     => $it['status'],
							'reply'      => $reply,
							'id'		 => $it['id'],
							'thumb1'     => $it['thumb1'],
							'thumb2'     => $it['thumb2'],
							'thumb3'     => $it['thumb3'],
						);				
					}
					//print_r($list);
				
	    	}elseif ($op == 'resolve') {
	    		//业主完成报修申请
				$id   = $_GPC['id'];
				$sql  = "select * from".tablename($this->report)."where id=".$id." and weid='{$_W['weid']}'";
				$item = pdo_fetch($sql);
				//print_r($item);
				$update = array(
					'status'  => 1,
					'rank'    => $_GPC['rank'],
					'comment' => $_GPC['comment'],
					);
				if($_W['ispost']){
	    		pdo_update($this->report,$update,array('id' => $id));
	    		message('谢谢评价',$this->createMobileUrl('repair',array('op' => 'display')),'success');
	    	 }
	    	}elseif ($op == 'cancel') {
	    		//取消报修申请
	    		$id = $_GPC['id'];
	    		$data = array('status' => 2);
	    		pdo_update($this->report,$data,array('id'=>$id));
	    	}	
	    	include $this->template('repair');
   
    }
    //前台处理提交补充信息，isreply=0为前台提交，isreply=1为后台管理回复
    public function doMobileReply(){
    	global $_GPC,$_W;
    	$weid = $_GPC['weid'];
    	if(checksubmit('submit')){
    		$data = array(
				'weid'       =>$weid,
				'openid'     =>$_W['fans']['from_user'],
				'reportid'   =>$_GPC['id'],
				'isreply'    =>0,
				'content'    =>$_GPC['content'],
				'createtime' =>$_W['timestamp'],
    			);
    		pdo_insert($this->reply,$data);
    	} 
    	message('提交成功',referer(),'success');	
    }
    //前台导航
    public function doMobileNav(){
    	global $_GPC,$_W;
    	$member = pdo_fetch("SELECT * FROM".tablename($this->member)."WHERE openid='{$_W['fans']['from_user']}'");
    	if(empty($member['status'])){
			$url1 = $this->createMobileUrl('home');
			$url  = $_W['siteroot'].$url1;
				//echo $url ;
			header("Location:$url");exit;
		}
		include $this->template('nav');
    }
    //前台投诉
    public function doMobileReport(){
    	global $_GPC,$_W;
	    	$title = '投诉服务';
	    	$op = !empty($_GPC['op'])?$_GPC['op']:'display';
	    	// $categories = array(
	    	// 		'1'=>'投诉类型1',
	    	// 		'2'=>'投诉类型2',
	    	// 		'3'=>'投诉类型3',
	    	// 		);
	    	$categories = pdo_fetchall("select * from".tablename($this->servicecategory)."where parentid = 4 and weid='{$_W['weid']}'");

	    		//查小区编号
				$sql_1 = "select * from ".tablename($this->member)."where openid='{$_W['fans']['from_user']}'";
				$it    = pdo_fetch($sql_1);
	    		$data  = array(
					'openid'      => $_W['fans']['from_user'],
					'weid'        => $_W['weid'],
					'regionid'    => $it['regionid'],
					'type'        => 2,
					'category'    => $_GPC['category'],
					'content'     => $_GPC['content'],
					'createtime'  => $_W['timestamp'],
					'status'      => 0,
					'rank'        => 0,
					'comment'     => 0,
					'requirement' => '无',
					'resolve'     => '',
					'resolver'    => '',
					'resolvetime' => '',
	    			);
	    		//无线打印
    		 	$set = pdo_fetch("SELECT * FROM".tablename($this->set)."WHERE weid='{$_W['weid']}'");
    			if(!empty($set['print_status'])){
    				if ($set['print_type']=='1' || $set['print_type'] == '2') {
    					$data['print_sta'] = -1;
    				}
    			}	
	    		if(empty($it['status'])){
	    			$url1 = $this->createMobileUrl('home');
					$url  = $_W['siteroot'].$url1;
						//echo $url ;
					header("Location:$url");exit;
	    		}
	    	if($op == 'post'){
	    		
		    		if (checksubmit('submit')) {
		    			pdo_insert($this->report,$data);
		    			//短信提醒
		    			$con = $_GPC['content'];
						$this->Resms($con);
		    			message('投诉成功,请查看"我的投诉"等待工作人员联系。',$this->createMobileUrl('report',array('op'=>'post')),'success');
		    		}
	    		
	    	}elseif ($op == 'display') {
	    			$pindex = max(1, intval($_GPC['page']));
					$psize = 3;
		    		//投诉记录查询
					$sql  = "select * from ".tablename($this->report)."where openid='{$_W['fans']['from_user']}' and weid='{$_W['weid']}' and type=2 LIMIT ".($pindex - 1) * $psize.','.$psize;
					//print_r($sql);
					$list = pdo_fetchall($sql);
					//print_r($list);
					$total = pdo_fetchcolumn("select count(*) from".tablename($this->report)."where openid='{$_W['fans']['from_user']}' and weid='{$_W['weid']}' and type=2");
					$pager = pagination($total, $pindex, $psize);
				
	    	}elseif ($op == 'cancel') {
	    		//取消投诉
				$id   = $_GPC['id'];
				$data = array('status' => 2);
	    		pdo_update($this->report,$data,array('id'=>$id));
	    	}
	    	include $this->template('report');
  
    }
    //前台家政服务
    public function doMobileHomemaking(){
    	global $_W,$_GPC;
	    	$title = '家政服务';
	    	$op = $_GPC['op'];
	    	$id = $_GPC['id'];
	    	//查类型
	   		$sql = "select * from".tablename($this->servicecategory)."where parentid = 1 and weid='{$_W['weid']}'";
	    	$categories = pdo_fetchall($sql);
	    	//查对应的小区编号
	    	$sql  = "select * from".tablename($this->member)."where openid= '{$_W['fans']['from_user']}'";
			$it = pdo_fetch($sql);
			if (empty($it['status'])) {
	    			$url1 = $this->createMobileUrl('home');
					$url  = $_W['siteroot'].$url1;
						//echo $url ;
					header("Location:$url");exit;
	    		}
	    	if($op == 'post'){

	    		$data = array(
					'weid'                 => $_W['weid'],
					'openid'               => $_W['fans']['from_user'],
					'regionid'             => $it['regionid'],
					'servicesmallcategory' => $_GPC['servicesmallcategory'],
					'contacttype'          => $_GPC['contacttype'],
					'requirement'          => $_GPC['requirement'],
					'remark'               => $_GPC['remark'],
					'createtime'           => $_W['timestamp'],
					'status'               => 0,
					'servicecategory'      => 1,
	    		);
	   
		    		if(!empty($id)){
		    			$sql = "select * from".tablename($this->service)."where id=".$id;
		    			$item = pdo_fetch($sql);
		    			//print_r($item);exit;
		    		}
		    		if($_W['ispost']){
		    			if (empty($id)) {
		    				pdo_insert($this->service,$data);
		    				message('信息发布成功。',$this->createMobileUrl('homemaking',array('op' => 'display')),'success');
		    			}else{
		    				pdo_update($this->service,$data,array('id' => $id));
		    			}
		    			message('信息编辑成功。',$this->createMobileUrl('homemaking',array('op' => 'display')),'success');
		    		}
	
	    	}elseif ($op == 'display') {
	    			$pindex = max(1, intval($_GPC['page']));
					$psize = 2;
					$sql  = "select * from".tablename($this->service)."where weid = '{$_W['weid']}' and openid = '{$_W['fans']['from_user']}' and servicecategory = 1 and regionid=".$it['regionid']." LIMIT ".($pindex - 1) * $psize.','.$psize;
					$list = pdo_fetchall($sql);
					$total = pdo_fetchcolumn('select count(*) from'.tablename($this->service)."where weid = '{$_W['weid']}' and openid = '{$_W['fans']['from_user']}' and servicecategory = 1 and regionid=".$it['regionid']);
	    			$pager = pagination($total, $pindex, $psize);
	    	}elseif ($op == 'resolve') {
	    		$data = array(
	    			'status' => 1,
	    			);
	    		pdo_update($this->service,$data,array('id' => $id));
	    		message('家政服务信息删除成功。',referer(),'success');
	    	}elseif ($op == 'cancel') {
	    		$data = array(
	    			'status' => 2,
	    			);
	    		pdo_update($this->service,$data,array('id' => $id));

	    		message('家政服务信息取消成功。',referer(),'success');
	    	}
	    	include $this->template('homemaking');
 
    }
    //前台房屋租赁
   public function doMobileHouselease(){
   		global $_W,$_GPC;
	   		$title = '房屋租赁';
	    	$op = $_GPC['op'];
	    	$id = $_GPC['id'];
	    	//查类型
	   		$sql = "select * from".tablename($this->servicecategory)."where parentid = 2 and weid='{$_W['weid']}'";
	    	$categories = pdo_fetchall($sql);
	    	//查对应的小区编号
	    	$sql  = "select * from".tablename($this->member)."where openid= '{$_W['fans']['from_user']}'";
			$it = pdo_fetch($sql);
			if(empty($it['status'])){
	    			$url1 = $this->createMobileUrl('home');
					$url  = $_W['siteroot'].$url1;
						//echo $url ;
					header("Location:$url");exit;
	    		}
	    	$data = array(
					'weid'                 => $_W['weid'],
					'openid'               => $_W['fans']['from_user'],
					'regionid'             => $it['regionid'],
					'servicesmallcategory' => $_GPC['servicesmallcategory'],
					'contacttype'          => $_GPC['contacttype'],
					'requirement'          => $_GPC['requirement'],
					'remark'               => $_GPC['remark'],
					'createtime'           => $_W['timestamp'],
					'status'               => 0,
					'servicecategory'      => 2,
	    		);
	    	if($op == 'post'){
	    	
		    		if(!empty($id)){
		    			$sql = "select * from".tablename($this->service)."where id=".$id;
		    			$item = pdo_fetch($sql);
		    			//print_r($item);exit;
		    		}
					
		    		if($_W['ispost']){
		    			if (empty($id)) {
		    				//print_r($data);exit;
		    				pdo_insert($this->service,$data);
		    				message('信息发布成功。',$this->createMobileUrl('houselease',array('op' => 'display')),'success');
		    			}else{
		    				pdo_update($this->service,$data,array('id' => $id));
		    			}
		    			message('信息编辑成功。',$this->createMobileUrl('houselease',array('op' => 'display')),'success');
		    		}
	
	    	}elseif ($op == 'display') {
	    			$pindex = max(1, intval($_GPC['page']));
					$psize = 2;
					$sql  = "select * from".tablename($this->service)."where weid = '{$_W['weid']}' and openid='{$_W['fans']['from_user']}' and servicecategory = 2 and regionid=".$it['regionid']." LIMIT ".($pindex - 1) * $psize.','.$psize;;
					$list = pdo_fetchall($sql);
					$total = pdo_fetchcolumn('select count(*) from'.tablename($this->service)."where weid = '{$_W['weid']}' and openid='{$_W['fans']['from_user']}' and servicecategory = 2 and regionid=".$it['regionid']);
					$pager = pagination($total, $pindex, $psize);
	    		
	    	}elseif ($op == 'resolve') {
	    		$data = array(
	    			'status' => 1,
	    			);
	    		pdo_update($this->service,$data,array('id' => $id));
	    		message('房屋租赁信息删除成功。',referer(),'success');
	    	}elseif ($op == 'cancel') {
	    		$data = array(
	    			'status' => 2,
	    			);
	    		pdo_update($this->service,$data,array('id' => $id));
	    		message('房屋租赁信息取消成功。',referer(),'success');
	    	}
	   		include $this->template('houselease');

   }
   //前台团队介绍
   public function doMobileProperty(){
   		global $_W,$_GPC;
   		$title = '物业团队介绍';
   		//判断是否注册，只有注册后，才能进入
		$from_user = $_W['fans']['from_user'];
		$member = pdo_fetch("SELECT * FROM".tablename('xcommunity_member')."WHERE openid='{$from_user}'");
		if(empty($member) || empty($member['status'])){
			$url1 = $this->createMobileUrl('home');
			$url  = $_W['siteroot'].$url1;
			header("Location:$url");exit;
		}
	   		$sql = "select * from".tablename($this->property)."where weid='{$_W['weid']}'";
	   		$list = pdo_fetch($sql);
	   		include $this->template('property');

   }
   //前台幻灯片设置
   public function doMobileMslide(){

   		include $this->template('mslide');
   }
   //后台打印
   public function doWebPrintset(){
   		global $_GPC,$_W;
		if (checksubmit('submit')) {
			$insert=array(
				'weid'         =>$_W['weid'],
				'print_status' =>trim($_GPC['print_status']),
				'print_type'   =>trim($_GPC['print_type']),
				'print_usr'    =>trim($_GPC['print_usr']),
				'print_nums'   =>trim($_GPC['print_nums']),
				'print_bottom' =>trim($_GPC['print_bottom']),
		 	);
			if (empty($_GPC['id'])) {
				pdo_insert($this->set, $insert);
			} else {
				pdo_update($this->set, $insert, array('weid'=>$_W['weid'],'id' => $_GPC['id']));
			}
			message('打印机数据保存成功', $this->createWebUrl('Printset'), 'success');
		}
		$set = pdo_fetch("SELECT * FROM ".tablename($this->set)." WHERE weid = :weid", array(':weid' => $_W['weid']));
		if($set==false){
			$set=array(
				'id'=>0,
			);
		}else{

		}
   		include $this->template('printset');
   }

   //后台短信设置
   public function doWebSmsset(){
   		global $_W,$_GPC;
   		if($_GPC['action']=='test'){
 
			$title="这里是测试平台，给你发送邮件";
			$content="祝您生意兴隆，财源广进.";
			
			if($temp==1){
				message('邮件发送成功，您的邮件设置成功', $this->createWebUrl('smsset'), 'success');
			}else{
				message('邮件发送成功，您的邮件设置成功,错误原因:'.$temp);
			}
		}
		if (checksubmit('submit')) {
			$insert=array(
				'weid'            =>$_W['weid'],
				'sms_status'      =>trim($_GPC['sms_status']),
				'sms_type'        =>trim($_GPC['sms_type']),
				// 'sms_from'     =>trim($_GPC['sms_from']),
				'sms_secret'      =>trim($_GPC['sms_secret']),
				// 'sms_phone'    =>trim($_GPC['sms_phone']),
				// 'sms_text'     =>trim($_GPC['sms_text']),
				'sms_account'     =>trim($_GPC['sms_account']),
				'sms_account'     =>trim($_GPC['sms_account']),
				'sms_resgister'   =>intval($_GPC['sms_resgister']),
				// 'sms_customer' =>intval($_GPC['sms_customer']),
				'sms_verifytxt'   =>trim($_GPC['sms_verifytxt']),
				'sms_reportid'    =>trim($_GPC['sms_reportid']),
				'sms_resgisterid' =>trim($_GPC['sms_resgisterid']),
				// 'sms_paytxt'=>trim($_GPC['sms_paytxt']),
				// 'sms_bosstxt'=>trim($_GPC['sms_bosstxt']),
				
		 	);
		 	//print_r($insert);exit;
		if (empty($_GPC['id'])) {

			pdo_insert($this->set, $insert);
		} else {
			pdo_update($this->set, $insert, array('weid'=>$_W['weid'],'id' => $_GPC['id']));
		}
		message('短信数据保存成功', $this->createWebUrl('Smsset'), 'success');
	}
	$set = pdo_fetch("SELECT * FROM ".tablename($this->set)." WHERE weid = :weid", array(':weid' => $_W['weid']));
	if($set==false){
		$set=array(
			'id'=>0,
		);
	}
		if(empty($set['sms_verifytxt'])){
			$set['sms_verifytxt']='感谢您注册#app#，您的验证码是#code#【#company#】';
		}
   		include $this->template('smsset');
   }

 
	public  function doMobileVerifycode()
	{
		global $_GPC,$_W;
		WeSession::$expire = 600;	
		WeSession::start();
		//echo $_SESSION['mobile'];

		$sms    = pdo_fetch("select * from".tablename($this->set)."where weid='{$_W['weid']}'");
		$mobile = $_GPC['mobile'];
		$member = pdo_fetch("select * from".tablename($this->member)."where weid='{$_W['weid']}' and mobile=".$mobile);
		if (!empty($member)) {
			//已经注册
			message('已经注册');
		}
		if($mobile==$_SESSION['mobile']){
			$code=$_SESSION['code'];
		}else{
			$code= random(6,1);
			$_SESSION['mobile']=$mobile;
			$_SESSION['code']=$code;
		}
		//验证是否存在设置
		if($sms!=false){
			//验证是否开启
			if( $sms['sms_status']==1 && $sms['sms_resgister']==1 ){
				$mobile    = $_SESSION['mobile'];
				$tpl_id    = $sms['sms_resgisterid'];
				$company   = $this->module['config']['cname'];
				$tpl_value = urlencode("#code#=$code&#company#=$company");
				$appkey    = $sms['sms_account'];
				$params    = "mobile=".$mobile."&tpl_id=".$tpl_id."&tpl_value=".$tpl_value."&key=".$appkey;
				$url       = 'http://v.juhe.cn/sms/send';
				//print_r($url);exit;
				$content   = ihttp_post($url,$params);
				//print_r($content);

			}
		}	
	}
	public function Resms($con){
		global $_W,$_GPC;
		//报修投诉短信提醒
			$sms       = pdo_fetch("select * from".tablename($this->set)."where weid='{$_W['weid']}'");
			//print_r($sms);exit;
			if($sms['sms_status'] ==1 && $sms['sms_type'] == 1){
				
				//查小区物业电话
				
				$sql_1     = "select * from ".tablename($this->member)."where openid='{$_W['fans']['from_user']}'";
				$it        = pdo_fetch($sql_1);
				$mobile    = $it['mobile'];
				$sql       = "select * from".tablename($this->region)." where title="."'".$it['regionname']."'";
				$row       = pdo_fetch($sql);
				$phone     = $row['linkway'];
				$tpl_id    = $sms['sms_reportid'];
				$content   = $con;
				$company   = $this->module['config']['cname'];
				$tpl_value = urlencode("#content#=$content&#mobile#=$mobile&#company#=$company");
				$appkey    = $sms['sms_account'];
				$params    = "mobile=".$phone."&tpl_id=".$tpl_id."&tpl_value=".$tpl_value."&key=".$appkey;
				$url       = 'http://v.juhe.cn/sms/send';
				//print_r($url);exit;
				$content   = ihttp_post($url,$params);
				
			}
	}
	//GPRS无线打印
	public function doWebPrint(){
		global $_GPC,$_W;
		$usr=!empty($_GET['usr'])?$_GET['usr']:'355839028553370';
		//获取订单
		$set = pdo_fetch("SELECT * FROM ".tablename($this->set)." WHERE print_usr = :usr", array(':usr' =>$usr));
		if($set==false){
			exit;
		}
		$weid=$set['weid'];
		$item = pdo_fetch("SELECT * FROM ".tablename($this->report)." WHERE weid = :weid AND print_sta=-1  limit 1", array(':weid' => $weid));
		//没有新信息
		if($item==false){	
			exit;
		}
		if(intval($set['print_nums'])<1 || intval($set['print_nums'])>4){
			$set['print_nums']=1;
		}
		$member = pdo_fetch("SELECT * FROM ".tablename($this->member)." WHERE weid = :weid AND openid='{$item['openid']}'  limit 1", array(':weid' => $weid));
		$content.='类型:'.$item['category']."\n";
		$content.='内容:'.$item['content']."\n";
		$content.='所属小区:'.$member['regionname']."\n";
		$content.='地址:'.$member['address']."\n";
		$content.='业主:'.$member['realname']."\n";
		$content.='电话:'.$member['mobile']."\n";
		$content.='日期:'.date('Y-m-d H:i:s', $item['createtime'])."\n";
		$content=iconv("UTF-8","GB2312//IGNORE",$content);
		$setting='<setting>124:'.$set['print_nums'].'|134:0</setting>';
		$setting=iconv("UTF-8","GB2312//IGNORE",$setting);
		echo '<?xml version="1.0" encoding="GBK"?><r><id>'.$item['id'].'</id><time>'.$dtime.'</time><content>'.$content.'</content>'.$setting.'</r>';
		pdo_update($this->report,array('print_sta'=>0),array('id'=>$item['id']));
	}
	public function doMobileWurl(){
		global $_W,$_GPC;

		$sql = "select * from".tablename($this->property)."where weid='{$_W['weid']}'";
	   	$list = pdo_fetch($sql);
	   	//print_r($list);
	   	$url = $list['mcommunity'];
		header("Location:$url");exit;
	}
	
	//后台程序 web文件夹下
	public function __web($f_name){
		global $_W,$_GPC;
		//checklogin();
		$weid=$_W['weid'];
		//每个页面都要用的公共信息.后期考虑用缓存2014-2-7
		include_once  'web/wl'.strtolower(substr($f_name,5)).'.php';
	}
	//后台-小区活动
	public function doWebActivity() {
 		$this->__web(__FUNCTION__);
	}
	//后台-常用查询
	public function doWebSearch() {
		$this->__web(__FUNCTION__);
	}
	//后台-二手市场
	public function doWebFled(){
		$this->__web(__FUNCTION__);
	}
	//后台-小区拼车
	public function doWebCarpool(){
		$this->__web(__FUNCTION__);
	}
	//前台程序 site文件夹下
	public function __site($f_name){
		global $_W,$_GPC;
		//checklogin();
		$weid=$_W['weid'];
		//每个页面都要用的公共信息.后期考虑用缓存2014-2-7
		include_once  'site/wl'.strtolower(substr($f_name,8)).'.php';
	}
	//前台-小区活动首页
	public function doMobileActivity() {
 		$this->__site(__FUNCTION__);
	}
	//前台-小区活动详细页
	public function doMobileDetail(){
		$this->__site(__FUNCTION__);
	}
	//前台-小区活动报名页面
	public function doMobileRes(){
		$this->__site(__FUNCTION__);
	}
	//前台-小区常用查询
	public function doMobileSearch(){
		$this->__site(__FUNCTION__);
	}
	//前台-小区二手市场首页
	public function doMobileIndex(){
		$this->__site(__FUNCTION__);
	}
	//前台-小区二手市场转让和求购
	public function doMobileAdd(){
		$this->__site(__FUNCTION__);
	}
	//前台-小区二手市场商品详细页
	public function doMobileContent(){
		$this->__site(__FUNCTION__);
	}
	//前台-小区拼车首页
	public function doMobileCarIndex(){
		$this->__site(__FUNCTION__);
	}
	//前台-小区拼车找车主和找乘客
	public function doMobileCarAdd(){
		$this->__site(__FUNCTION__);
	}
	//前台-小区拼车详细信息
	public function doMobileCarDetail(){
		$this->__site(__FUNCTION__);
	}
}