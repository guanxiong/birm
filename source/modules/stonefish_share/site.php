<?php
/*
 * 分享集赞模块 share
 *
 */
defined('IN_IA') or exit('Access Denied');

class stonefish_shareModuleSite extends WeModuleSite {	
	
	public $table_reply = 'stonefish_share_reply';
	public $table_list   = 'stonefish_share_list';	
	public $table_data   = 'stonefish_share_data';
	public $table_log   = 'credit_log';

	public function getProfileTiles() {
		
	}
	
	public function getHomeTiles($keyword = '') {
        global $_GPC,$_W;
		$weid = $_W['weid'];//当前公众号ID
		$urls = array();
		$list = pdo_fetchall("SELECT name, id FROM ".tablename('rule')." WHERE weid = ".$weid." and module = 'stonefish_share'".(!empty($keyword) ? " AND name LIKE '%{$keyword}%'" : ''));
		if (!empty($list)) {
			foreach ($list as $row) {
				$urls[] = array('title'=>$row['name'], 'url'=> $this->createMobileUrl('share', array('id' => $row['id'])));
			}
		}
		return $urls;
	}
	
	public function doMobileShare() {
		//分享集赞分享页面显示。
		global $_GPC,$_W;
		$weid = $_W['weid'];//当前公众号ID
		$s = 0;
		$profile_s = 0;
		if (empty($_GPC['rid'])) {
		$rid = $_GPC['id'];
		}else{
		$rid = $_GPC['rid'];
		}
		//参数设置调用
		$cfg = $this->module['config'];
		$show = $cfg['share_show'];
		$shownum = $cfg['share_show_num'];
		if(empty($shownum)){
		    $shownum = 20;
		}
		$foo = !empty($_GPC['foo']) ? $_GPC['foo'] : '';
      	if (!empty($rid)) {
			$reply = pdo_fetch("SELECT * FROM ".tablename($this->table_reply)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));
			$listshare = pdo_fetchall('SELECT a.*,b.realname,b.mobile FROM '.tablename($this->table_list).' as a left join '.tablename('fans').' as b on a.from_user=b.from_user  WHERE a.weid= :weid AND a.rid = :rid order by `sharenum` desc LIMIT '.$shownum.'', array(':weid' => $weid,':rid' => $rid));
			$count = pdo_fetch("SELECT count(id) as dd FROM ".tablename($this->table_list)." WHERE weid=".$weid." AND rid= '".$rid."' order by `id` DESC ");
			$listtotal = $count['dd'];	
 		}

		$fromuser = $_W['fans']['from_user'];

		//取得分享集赞数据
		if(!empty($fromuser)) {
			$list = pdo_fetch("SELECT * FROM ".tablename($this->table_list)." WHERE from_user = '".$fromuser."' and weid = '".$weid."' and rid= '".$rid."' limit 1" );			
			if(!empty($list)){
			$count= pdo_fetch("SELECT count(id) as dd FROM ".tablename($this->table_list)." WHERE weid=".$weid." and rid= '".$rid."' and sharenum >= ".$list['sharenum']);
			$sharepm=$count['dd'];
			$s = 1;
			$show = 1;
			}
		}	
		$sharenum = pdo_fetchcolumn("SELECT sharenum FROM ".tablename($this->table_list)." WHERE from_user = '".$fromuser."' and weid = '".$weid."' and rid= '".$rid."' limit 1");
		//整理数据进行页面显示
		//判断是否绑定
		$profile = fans_search($_W['fans']['from_user'], array('realname', 'mobile'));
		if (!empty($profile['realname']) AND !empty($profile['mobile'])) {
			$profile_s=1;
		}
		//判断是否绑定
		$imgurl=$_W['attachurl'] . $reply['picture'];
      	$title = $reply['title'];
		$loclurl=$_W['siteroot'].$this->createMobileUrl('share', array('rid' => $rid, 'from_user' => $_GPC['from_user']));		
		$regurl=$this->createMobileUrl('regshare', array('fromuser' => $fromuser));
		$staturl=$_W['siteroot'].$this->createMobileUrl('stat', array('rid' => $rid,'fromuser' => $fromuser));

		
		if ($reply['status']) {
			$user_agent = $_SERVER['HTTP_USER_AGENT'];
				if (strpos($user_agent, 'MicroMessenger') === false) {
					echo "本页面仅支持微信访问!非微信浏览器禁止浏览!";
					//include $this->template('share');
				} else { 
					include $this->template('share');
				}
		} else {
			echo '<h1>分享集赞活动已结束!</h1>';
			exit;			
		}

	}

	public function doMobileRegshare() {
		//分享集赞分享页面显示。
		global $_GPC,$_W;
		$weid = $_W['weid'];//当前公众号ID
		$rid  = $_GPC['rid'];//当前规则ID		
		$fromuser = $_W['fans']['from_user'];

		//取得分享集赞数据
		if(!empty($fromuser)) {
			//关注用户　注册转发
			$rs = pdo_fetch("SELECT id FROM ".tablename($this->table_list)." WHERE from_user = '".$fromuser."' and weid = '".$weid."' and rid = '".$rid."' limit 1" );			

			if(empty($rs['id'])){			
					fans_update($_W['fans']['from_user'], array(
					'realname' => $_GPC['realname'],
					'mobile' => $_GPC['mobile'],
				    ));
					$result='注册信息提交成功，立即分享吧！';
			}else{
					$result='您已注册过信息，可直接分享！';
			}			
		}else{
			//没有关注时提示用户
			$sql='SELECT name,account FROM  '.tablename('wechats')."   WHERE weid = '".$weid."'";
			$rs=pdo_fetch($sql)	;
			$wechatname=$rs['name'];
			$wechataccount=$rs['account'];
			$sql='SELECT content FROM '.tablename('rule_keyword')." WHERE  rid = '".$rid."' ";
			$rpkeyword=pdo_fetchcolumn($sql)	;

			if(!empty($wechatname)||!empty($rpkeyword))
			{
				$result = "亲！请先关注公众号：{$wechatname} ID: {$wechataccount} 发送关键字:'{$rpkeyword}'收到回复后，再进入登记信息参与活动-{$wechatname}敬上！";
			}
			else 
			{
				$result = "您访问的分享异常,请联系公众号技术人员！";
			}
		}
		echo $result;	
	}

	public function doMobileStat() {
		//分享集赞分享页面显示。
		global $_GPC,$_W;
		$weid = $_W['weid'];//当前公众号ID
		$rid = $_GPC['rid'];
		$fromuser =  $_GPC['fromuser'];
		$from_user = $_W['fans']['from_user'];
		$sql_info = "SELECT * FROM " . tablename('fans') . " WHERE `weid`=".$weid." and `from_user`=:from_user LIMIT 1";
        $info = pdo_fetch($sql_info, array(':from_user' => $fromuser));
		$credit1=$info['credit1'];
		$shareip = getip();
		$now = time();		
		if(!empty($rid)) {
		  $reply = pdo_fetch("SELECT * FROM ".tablename($this->table_reply)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));
		  $jumpurl = $reply['shareurl'];
		  $staturl=$_W['siteroot'].$this->createMobileUrl('stat', array('rid' => $rid,'fromuser' => $fromuser));
		  $credit = $reply['credit'];
		  $insertcredit = array(
				   'credit1' => $credit+$credit1
		    );
		  //积分记录相关
		  $creditlog = array(
			'weid'       => $weid,
			'from_user'  => $fromuser,
			'type'       => '1',
			'credit'     => $credit,
			'name'       => '分享集赞',
			'nametype'   => 'share',
			'content'    => $reply['title'],
			'ip'         => $shareip,
			'createtime' => $now
			);
		  //积分记录相关
		}
		//未关注用户用cookie作为唯一值		
		if(empty($from_user)) {
			
			if (!isset($_COOKIE["user_weigz_user"])){
		       setcookie("user_weigz_user", $now, time()+3600*24);
			   header("location:$staturl");
			   //重新加载
			   exit;
		    }
			$from_user = $_COOKIE['user_weigz_user'];
		}
		//分享人和查看人为同一人时，不参与加分直接跳转
		if($from_user==$fromuser){
		header("location:$jumpurl");
		}else{
		  //取得分享集赞数据
		  if(!empty($fromuser)) {
			$list = pdo_fetch("SELECT * FROM ".tablename($this->table_list)." WHERE from_user = '".$fromuser."' and weid = '".$weid."' and rid = '".$rid."' limit 1" );		
		  }

				if(!empty($list)){
					$shareid = $list['id'];
					//取得分享详细数据，判断浏览者是否是同一人24小时内同一IP访问
					$share_data = pdo_fetch("SELECT * FROM ".tablename($this->table_data)." WHERE uid = '".$shareid."' and rid = '".$rid."' and from_user = '".$from_user."' and weid = '".$weid."' and shareip= '".$shareip."' limit 1" );
					if(!empty($share_data)){
						$sid		=	$share_data['id'];
						$sharetime	=	$share_data['sharetime'];
						$updatetime	=	$now-$sharetime;
						//访问如果是在24小时后，更新分享数据，更新分享数
						if($updatetime > 24*3600){
							$updatedata = array(
								'viewnum'   => $share_data['viewnum']+1,
								'sharetime' => $now								
								);	
							$updatelist = array(
								'sharenum' => $list['sharenum']+1,
								'sharetime' => $now
								);	
							pdo_update($this->table_data,$updatedata,array('id' => $sid));
							pdo_update($this->table_list,$updatelist,array('id' => $shareid));
							pdo_update('fans', $insertcredit, array('from_user' => $fromuser));
							pdo_insert($this->table_log, $creditlog);//积分记录
						}
					}else{
							$insertdata = array(
								'weid'      => $weid,
								'from_user' => $from_user,
								'rid'       => $rid,
								'uid'       => $shareid,
								'shareip'	=> $shareip,
								'sharetime' => $now
								);	
							$updatelist = array(
								'sharenum' => $list['sharenum']+1,
								'sharetime' => $now
								);	
							pdo_insert($this->table_data, $insertdata);
							pdo_update($this->table_list,$updatelist,array('id' => $shareid));							
					        pdo_update('fans', $insertcredit, array('from_user' => $fromuser));
							pdo_insert($this->table_log, $creditlog);//积分记录
					}
				}else{					
					//添加分享集赞记录
					$insertlistdata = array(
						'weid'      => $weid,
						'from_user' => $fromuser,
						'rid'       => $rid,
						'sharenum'  => 1,
						'sharetime' => $now
					);
					pdo_insert($this->table_list, $insertlistdata);
					$uid = pdo_insertid();//取分享集赞记录id号
					//添加分享集赞记录
					//添加分享记录
					$insertdata = array(
						'weid'      => $weid,
						'from_user' => $from_user,
						'rid'       => $rid,
						'uid'       => $uid,
						'shareip'	=> $shareip,
						'sharetime' => $now
					);
					pdo_insert($this->table_data, $insertdata);
					//添加分享记录
					pdo_update('fans', $insertcredit, array('from_user' => $fromuser));
                    pdo_insert($this->table_log, $creditlog);//积分记录
				}			
          header("location:$jumpurl");
          //message('进入中，请稍后！', $jumpurl);
	      //print_r($from_user);
		}
	}

	public function doWebSharelist($rid, $state) {		
		global $_GPC, $_W;
		checklogin();
		$weid = $_W['weid'];//当前公众号ID
		$id = intval($_GPC['id']);
		if (checksubmit('delete')) {
			pdo_delete($this->table_list, " id IN ('".implode("','", $_GPC['select'])."')");
			message('删除成功！', create_url('site/module', array('do' => 'sharelist', 'name' => 'stonefish_share', 'id' => $id, 'page' => $_GPC['page'])));
		}
		$rules = pdo_fetchall('SELECT `id`,`name` FROM '.tablename('rule').' WHERE `module`=\'stonefish_share\'');
		$pindex = max(1, intval($_GPC['page']));
		$psize = 15;

		//取得分享集赞列表
		$list_share = pdo_fetchall('SELECT a.*,b.realname,b.mobile FROM '.tablename($this->table_list).' as a left join '.tablename('fans').' as b on a.from_user=b.from_user  WHERE a.weid= :weid order by `id` desc LIMIT ' . ($pindex - 1) * $psize . ',' . $psize, array(':weid' => $weid) );
		$total = pdo_fetchcolumn('SELECT COUNT(*) FROM '.tablename($this->table_list).' WHERE weid= :weid order by `id` desc ', array(':weid' => $weid));
		$pager = pagination($total, $pindex, $psize);
		include $this->template('sharelist');

	}
	public function doWebShareranklist($rid, $state) {		
		global $_GPC, $_W;
		checklogin();
		$weid = $_W['weid'];//当前公众号ID
		$id = intval($_GPC['id']);
		$page = $_GPC['page'];
		if (empty($page)){
		  $page = 1;
		}
		if (checksubmit('delete')) {
			pdo_delete($this->table_list, " id IN ('".implode("','", $_GPC['select'])."')");
			message('删除成功！', create_url('site/module', array('do' => 'shareranklist', 'name' => 'stonefish_share', 'id' => $id, 'page' => $_GPC['page'])));
		}
		$rules = pdo_fetchall('SELECT `id`,`name` FROM '.tablename('rule').' WHERE `module`=\'stonefish_share\'');
		$pindex = max(1, intval($_GPC['page']));
		$psize = 15;

		//取得分享集赞列表
		$list_share = pdo_fetchall('SELECT a.*,b.realname,b.mobile FROM '.tablename($this->table_list).' as a left join '.tablename('fans').' as b on a.from_user=b.from_user  WHERE a.rid = '.$id.' and a.weid= :weid order by `sharenum` desc LIMIT ' . ($pindex - 1) * $psize . ',' . $psize, array(':weid' => $weid) );
		$total = pdo_fetchcolumn('SELECT COUNT(*) FROM '.tablename($this->table_list).' WHERE rid = '.$id.' and weid= :weid order by `id` desc ', array(':weid' => $weid));
		$pager = pagination($total, $pindex, $psize);
		include $this->template('shareranklist');

	}
	public function doWebstatus( $rid = 0) {
		global $_GPC;
		$rid = $_GPC['rid'];
		echo $rid;
		$insert = array(
			'status' => $_GPC['status']
		);
		
		pdo_update($this->table_reply,$insert,array('rid' => $rid));
		message('模块操作成功！', referer(), 'success');
	}
	public function doWebdos( $id = 0) {
		global $_GPC;
		$rid = $_GPC['rid'];
		$id = $_GPC['id'];
		$sharelist = $_GPC['ac'];
		echo $id;
		$insert = array(
			'status' => $_GPC['status']
		);
		
		pdo_update($this->table_list,$insert,array('id' => $id,'rid' => $rid));
		message('屏蔽操作成功！', create_url('site/module/'.$sharelist.'', array('name' => 'stonefish_share', 'id' => $rid, 'page' => $_GPC['page'])));
	}	
	public function doWebSharedata($rid, $state) {		
		global $_GPC, $_W;
		checklogin();
		$weid = $_W['weid'];//当前公众号ID
		$id = intval($_GPC['id']);
		if (checksubmit('delete')) {
			pdo_delete($this->table_data, " id IN ('".implode("','", $_GPC['select'])."')");
			message('删除成功！', create_url('site/module/sharedata', array('name' => 'stonefish_share', 'id' => $id, 'page' => $_GPC['page'])));
		}
		$rules = pdo_fetchall('SELECT `id`,`name` FROM '.tablename('rule').' WHERE `module`=\'stonefish_share\'');
		$pindex = max(1, intval($_GPC['page']));
		$psize = 15;

		//取得分享点击详细数据
		$list_sharedata = pdo_fetchall('SELECT * FROM '.tablename($this->table_data).' WHERE weid= :weid order by `sharetime` desc LIMIT ' . ($pindex - 1) * $psize . ',' . $psize, array(':weid' => $weid) );	
		$total = pdo_fetchcolumn('SELECT COUNT(*) FROM '.tablename($this->table_data).' WHERE weid= :weid order by `sharetime` desc ', array(':weid' => $weid));
		$pager = pagination($total, $pindex, $psize);
		include $this->template('sharedata');

	}
	public function doWebdeldata( $id = 0) {
		global $_GPC;
		$rid = $_GPC['rid'];
		$id = $_GPC['id'];
		if (!empty($id)) {
			pdo_delete($this->table_data, " id = ".$id);
			message('删除成功！', create_url('site/module/sharedata', array('name' => 'stonefish_share', 'id' => $rid, 'page' => $_GPC['page'])));
		}		
		
	}
}