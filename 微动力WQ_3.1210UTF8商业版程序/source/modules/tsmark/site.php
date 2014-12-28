<?php
/**
 * 打气球抽奖模块
 *
 * [WeLan System] Copyright (c) 2013 WeLan.CC
 */
defined('IN_IA') or exit('Access Denied');

class TsMarkModuleSite extends WeModuleSite {
	public $name = 'tsmark';
	public $title = '跳骚市场';
	public $ability = '';
	public $tablename = 'tsmark_reply';
	public function getProfileTiles() {

	}
	
	public function getHomeTiles($keyword = '') {
		$urls = array();
		$list = pdo_fetchall("SELECT name, id FROM ".tablename('rule')." WHERE weid = '{$_W['weid']}' AND module = 'dqq'".(!empty($keyword) ? " AND name LIKE '%{$keyword}%'" : ''));
		if (!empty($list)) {
			foreach ($list as $row) {
				$urls[] = array('title'=>$row['name'], 'url'=> $this->createMobileUrl('lottery', array('id' => $row['id'])));
			}
		}
		return $urls;
	}

	public function doMobileLottery() {
		global $_GPC ,$_W;
		$fromuser = authcode(base64_decode($_GPC['from_user']), 'DECODE');
		$we = pdo_fetch("select b.weid from ims_tsmark_reply as a left join ims_rule  as b on a.rid = b.id where 1");
		$id = intval($_GPC['id']);
		$member = pdo_fetch("SELECT * FROM ".tablename('fans')." WHERE from_user = '{$fromuser}' AND `weid`='{$we['weid']}'");
		$tsmark = pdo_fetch("SELECT * FROM ".tablename($this->tablename)." WHERE rid = '".$id."' LIMIT 1");
		if (empty($tsmark)) {
			exit('非法参数！0');
		}
		if (empty($member['realname'])) {
			$from_user = authcode(base64_decode($_GPC['from_user']), 'DECODE');
			$user = pdo_fetch("SELECT * FROM ".tablename('fans')." WHERE from_user = '".$from_user."' and weid=".$we['weid']." limit 1");

			if($_GPC['action']=='setinfo'){
				$insert = array(
						'nickname' => $_GPC['nickname'],
						'realname' => $_GPC['realname'],
						'mobile' => $_GPC['mobile'],
						'qq' => $_GPC['qq'],
						//'sex' => $_GPC['sex'],
						//'age' => $_GPC['age'],
						'from_user'=>$from_user,
						'weid'=>$we['weid'],
						'createtime'=>time(),
				);
				$insert_winner = array(
						'rid' => $id,
						'playnum' => '1',
						'from_user' => $from_user,
						'status' => 1,
						'weid' => $we['weid'],
						'createtime' =>time()
				);
			
				if ($user==false) {
					$id=pdo_insert('fans', $insert);
					//$result = pdo_insert($this->table_winner,$insert_winner);
						
				} else {
					pdo_update('fans', $insert, array('from_user' => $from_user,'weid'=>$we['weid']));
				}
				die(true);
			}
			$title = '会员资料';
			$loclurl=create_url('mobile/module/lottery', array( 'name' => 'tsmark', 'id' => $id,'weid'=> $we['weid'], 'from_user' => $_GPC['from_user']));
			$loclurl_1=create_url('mobile/module/lottery', array( 'name' => 'tsmark', 'id' => $id,'weid'=> $we['weid'], 'from_user' => $_GPC['from_user']));
			include $this->template('register');
		}else{
			$p=isset($_GET['p'])?$_GET['p']:1;
			$from_user = authcode(base64_decode($_GPC['from_user']), 'DECODE');
			$title = '跳骚市场';
			
			
			
			$posturl=create_url('mobile/module/ajax', array('name' => 'tsmark', 'id' => $_GPC['id'], 'from_user' => $_GPC['from_user'],'weid'=>$_GPC['weid']));
			
			$sql="SELECT t.*,f.nickname,f.mobile FROM ".tablename('tsmark')." as t left join ".tablename('fans')." as f ON t.fromuser=f.from_user WHERE  t.rid=".$_GPC['id']." and t.fid=0 and t.isshow=1 ";
			
			
			if ($_GPC['search']!='') {
				$sql .= " AND LOWER( t.info ) LIKE '%".$_GPC['search']."%' ";
			}
				
			$sql .=" order by t.create_time desc ";
			
			$temp=pdo_fetchall($sql);
			$messagecount=count($temp);
			$pagenum=5;
			$totalpage=floor($messagecount/$pagenum)+1;
			$prow=($p-1)*$pagenum;
			
			$sql .=" limit $prow,$pagenum";
			
			$messagelist = pdo_fetchall( $sql);
			
			foreach($messagelist as $k=>$v){
				$messagelist[$k]['reply']=pdo_fetchall("SELECT t.*,f.nickname FROM ".tablename('tsmark')." as t left join ".tablename('fans')." as f ON t.fromuser=f.from_user WHERE  t.rid=".$_GPC['id']." and t.fid=".$v['id']." and t.isshow=1  limit 20" );
			}
			if($totalpage>$p){
				$nextpage=$_W['siteroot'] .create_url('mobile/module/lottery', array('name' => 'tsmark','weid'=>$_GPC['weid'],'search'=>$_GPC['search'], 'id' => $_GPC['id'],'from_user' => $_GPC['from_user'],'p'=>($p+1)));
			}else{
				$nextpage=$_W['siteroot'] .create_url('mobile/module/lottery', array('name' => 'tsmark','weid'=>$_GPC['weid'],'search'=>$_GPC['search'],  'id' => $_GPC['id'],'from_user' => $_GPC['from_user'],'p'=>$totalpage));
			}
			if($p>1){
				$prepage=$_W['siteroot'] .create_url('mobile/module/lottery', array('name' => 'tsmark','weid'=>$_GPC['weid'],'search'=>$_GPC['search'], 'id' => $_GPC['id'],'from_user' => $_GPC['from_user'],'p'=>($p-1)));
			}else{
				$prepage=$_W['siteroot'] .create_url('mobile/module/lottery', array('name' => 'tsmark','weid'=>$_GPC['weid'], 'search'=>$_GPC['search'], 'id' => $_GPC['id'],'from_user' => $_GPC['from_user'],'p'=>1));
			}
			include $this->template('index');
		}
	}
	  
	  public function doMobileAjax() {
	  	global $_GPC;
	  	$_GPC['weid']=$_GET['weid'];
	  	$from_user = authcode(base64_decode($_GPC['from_user']), 'DECODE');
	  	$message = pdo_fetch("SELECT * FROM ".tablename('tsmark')." WHERE fromuser = '".$from_user."' and rid=".$_GPC['id']." order by create_time desc limit 1" );
	  	//判断是否要审核留言
	  	$reply = pdo_fetch("SELECT isshow FROM ".tablename($this->tablename)." WHERE rid = ".$_GPC['id']." ORDER BY `id` DESC");
	  	$isshow=$reply['isshow'];
	  	$insert = array(
	  			'rid'=>$_GPC['id'],
	  			'weid'=>$_GPC['weid'],
	  			'info'=>$_GPC['info'],
	  			'fid'=>$_GPC['fid'],
	  			'img'=>$_GPC['img'],
	  			'fromuser'=>$from_user,
	  			'isshow'=>$isshow,
	  			'create_time'=>time(),
	  	);
	  	if($message==false){
	  		$id=pdo_insert('tsmark', $insert);
	  		$data['success']=true;
	  		$data['msg']='留言发表成功';
	  		if($isshow==0){$data['msg']=$data['msg'].',进入审核流程';}
	  	}else{
	  		if((time()-$message['create_time'])<5){
	  			$data['msg']='您的留言速度太快了';
	  			$data['success']=false;
	  		}else{
	  			$id=pdo_insert('tsmark', $insert);
	  			$data['success']=true;
	  			$data['msg']='留言发表成功';
	  			if($isshow==0){$data['msg']=$data['msg'].',进入审核流程';}
	  		}
	  	}
	  	echo json_encode($data);
	  }
	
	  /*
	   * 内容管理
	  */
	  public function doMobileManage() {
	  	global $_GPC, $_W;
	  	checklogin();
	  	$id = intval($_GPC['id']);
	  	if (checksubmit('verify') && !empty($_GPC['select'])) {
	  		pdo_update('tsmark', array('isshow' => 1, 'create_time' => TIMESTAMP), " id  IN  ('".implode("','", $_GPC['select'])."')");
	  		message('审核成功！', create_url('mobile/module/manage', array( 'name' => 'tsmark', 'id' => $id, 'page' => $_GPC['page'],'weid'=>$_GPC['__weid'])));
	  	}
	  	if (checksubmit('delete') && !empty($_GPC['select'])) {
	  		pdo_delete('tsmark', " id  IN  ('".implode("','", $_GPC['select'])."')");
	  		message('删除成功！', create_url('mobile/module/manage', array( 'name' => 'tsmark', 'id' => $id, 'page' => $_GPC['page'],'weid'=>$_GPC['__weid'])));
	  	}
	  	$isshow = isset($_GPC['isshow']) ? intval($_GPC['isshow']) : 0;
	  	$pindex = max(1, intval($_GPC['page']));
	  	$psize = 20;
	  
	  	$message = pdo_fetch("SELECT id, isshow, rid FROM ".tablename('tsmark_reply')." WHERE rid = '{$id}' LIMIT 1");
	  	
	  	$list = pdo_fetchall("SELECT t.*,f.nickname FROM ".tablename('tsmark')." as t left join ".tablename('fans')." as f ON t.fromuser=f.from_user WHERE t.rid = '{$message['rid']}' AND t.isshow = '$isshow' ORDER BY t.create_time DESC LIMIT ".($pindex - 1) * $psize.",{$psize}");
	  	if (!empty($list)) {
	  		$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('tsmark') . " WHERE rid = '{$message['rid']}' AND isshow = '$isshow'");
	  		$pager = pagination($total, $pindex, $psize);
	  
	  		foreach ($list as &$row) {
	  			$row['content'] = emotion($row['content']);
	  			$userids[] = $row['from_user'];
	  		}
	  		unset($row);
	  	}
	  	include $this->template('manage');
	  }
}
