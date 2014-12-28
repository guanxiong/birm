<?php
/**
 * 更新资料有礼模块
 *
 * [微鼎] Copyright (c) 2013 WEIDIM.COM
 */
defined('IN_IA') or exit('Access Denied');

class upfansdataModuleSite extends WeModuleSite {	
	
	public $table_reply  = 'upfansdata_reply';
	public $table_list   = 'upfansdata_list';
	public $table_fans   = 'fans';
	public $table_log    = 'credit_log';

	public function getProfileTiles() {
		
	}
	
	public function getHomeTiles($keyword = '') {
		$urls = array();
		$list = pdo_fetchall("SELECT name, id FROM ".tablename('rule')." WHERE module = 'upfansdata'".(!empty($keyword) ? " AND name LIKE '%{$keyword}%'" : ''));
		if (!empty($list)) {
			foreach ($list as $row) {
				$urls[] = array('title'=>$row['name'], 'url'=> $this->createMobileUrl('upfansdata', array('id' => $row['id'])));
			}
		}
		return $urls;
	}
	
	public function doMobileupfansdata() {
		//更新资料有礼分享页面显示。
		global $_GPC,$_W;
		$weid = $_W['weid'];//当前公众号ID
		$s = 0;
		if (empty($_GPC['rid'])) {
		$rid = $_GPC['id'];
		}else{
		$rid = $_GPC['rid'];
		}
		$foo = !empty($_GPC['foo']) ? $_GPC['foo'] : '';
      	if (!empty($rid)) {
			$reply = pdo_fetch("SELECT * FROM ".tablename($this->table_reply)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));
			$listupfansdata = pdo_fetchall("SELECT * FROM ".tablename($this->table_list)." WHERE weid = :weid and rid = '".$rid."' ORDER BY `id` DESC  limit 10", array(':weid' => $weid));			
			$count = pdo_fetch("SELECT count(id) as dd FROM ".tablename($this->table_list)." WHERE weid=".$weid." and rid = '".$rid."' order by `id` DESC ");
			$listtotal = $count['dd'];
	    }

			if (!empty($reply)) {
			    $reply['fields'] = iunserializer($reply['fields']);
		    } else {
			  message('系统出错！');
			  exit;
		    }
			$sql_info = "SELECT * FROM " . tablename($this->table_fans) . " WHERE  `id`=:id LIMIT 1";
            $info = pdo_fetch($sql_info, array(':id' => $_W['fans']['id']));
			$fansdata=array(
					'realname'          => $info['realname'],
					'nickname'          => $info['nickname'],
				    'mobile'            => $info['mobile'],
				    'qq'                => $info['qq'],
				    'avatar'            => $info['avatar'],
				    'gender'            => $info['gender'],
				    'birth'             => $info['birth'],
				    'birthyear'         => $info['birthyear'],
				    'birthmonth'        => $info['birthmonth'],
				    'birthmonth'        => $info['birthmonth'],
				    'reside'            => $info['reside'],
				    'resideprovince'    => $info['resideprovince'],
					'residecity'        => $info['residecity'],
				    'residedist'        => $info['residedist'],
				    'address'           => $info['address'],
				    'email'             => $info['email'],
				    'telephone'         => $info['telephone'],
				    'taobao'            => $info['taobao'],
				    'alipay'            => $info['alipay'],
				    'studentid'         => $info['studentid'],
				    'grade'             => $info['grade'],
				    'graduateschool'    => $info['graduateschool'],
				    'education'         => $info['education'],
				    'company'           => $info['company'],
				    'occupation'        => $info['occupation'],
				    'position'          => $info['position'],
				    'revenue'           => $info['revenue'],
				    'constellation'     => $info['constellation'],
					'zodiac'            => $info['zodiac'],
				    'nationality'       => $info['nationality'],
				    'height'            => $info['height'],
				    'weight'            => $info['weight'],
				    'bloodtype'         => $info['bloodtype'],
				    'idcard'            => $info['idcard'],
				    'zipcode'           => $info['zipcode'],
				    'site'              => $info['site'],
				    'affectivestatus'   => $info['affectivestatus'],
				    'lookingfor'        => $info['lookingfor'],
				    'bio'               => $info['bio'],
				    'interest'          => $info['interest'],				
				);				

		$fromuser = $_W['fans']['from_user'];
		$upfansdataip = getip();
		$now = time();
		//判断是否关注，没有关注提示用户关注
		    $sql='SELECT name,account FROM  '.tablename('wechats')."   WHERE weid = '".$weid."'";
			$rs=pdo_fetch($sql)	;
			$wechatname=$rs['name'];
			$wechataccount=$rs['account'];
			$sql='SELECT content FROM '.tablename('rule_keyword')." WHERE  rid = '".$rid."' ";
			$rpkeyword=pdo_fetchcolumn($sql)	;

			if(!empty($wechatname)||!empty($rpkeyword))
			{
				$meiyouguanzhu = "亲！请先关注公众号：{$wechatname} ID: {$wechataccount} 发送关键字:'{$rpkeyword}'收到回复后，再进入登记信息参与活动-{$wechatname}敬上！";
			}
			else 
			{
				$meiyouguanzhu = "您访问的分享异常,请联系公众号技术人员！";
			}
		

		//取得更新资料有礼数据
		if(!empty($fromuser)) {
			$list = pdo_fetch("SELECT * FROM ".tablename($this->table_list)." WHERE from_user = '".$fromuser."' and rid = '".$rid."' limit 1" );			
			if(!empty($list)){
			$count= pdo_fetch("SELECT count(id) as dd FROM ".tablename($this->table_list)." WHERE weid=".$weid." and rid = '".$rid."'");
			$upfansdatapm=$count['dd'];
			$s = 1;
			}
		}
		//整理数据进行页面显示		
		$imgurl=$_W['attachurl'] . $reply['picture'];
      	$title = $reply['title'];
		$loclurl=$_W['siteroot'].$this->createMobileUrl('upfansdata', array('rid' => $rid, 'from_user' => $_GPC['from_user']));		
		$regurl=$this->createMobileUrl('regupfansdata', array('fromuser' => $fromuser));
		$staturl=$_W['siteroot'].$this->createMobileUrl('stat', array('rid' => $rid,'fromuser' => $fromuser));

		if (checksubmit('submit')) {
		//取得更新资料有礼数据开始
		$data=array(
					'from_user' => $fromuser,					
					'upfansdatatime'	    => time(),
			        'weid'	    => $_GPC['weid'],			        
                    'rid'       => $rid,
			        'credit'    => $reply['credit'],
				);
		$result='提交失败';//error
		$result=$fromuser;
		if(!empty($data['from_user'])) {

			
			//更新粉丝资料
			$fansinfo = pdo_fetch("SELECT * FROM ".tablename($this->table_reply)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));
			$fansinfo['fields'] = iunserializer($fansinfo['fields']);
			$jumpurl = $fansinfo['upfansdataurl'];
			$data1 = array();
			if (!empty($fansinfo['fields'])) {
				foreach ($fansinfo['fields'] as $row) {
					if (!empty($row['require']) && empty($_GPC[$row['bind']])) {
						message('请输入'.$row['title'].'！');
					}
					$data1[$row['bind']] = $_GPC[$row['bind']];
				}
			}

			fans_update($_W['fans']['from_user'], $data1);//更新粉丝资料
			//给粉丝送积分
			$insertcredit = array(
				   'credit1' => $fansdata['credit1']+$fansinfo['credit']
		    );
			$zongjifen = $fansdata['credit1']+$fansinfo['credit'];
			
			$rs = pdo_fetch("SELECT id,upfansdatatime FROM ".tablename($this->table_list)." WHERE from_user = '".$fromuser."' and weid = '".$weid."' and rid = '".$rid."' limit 1" );
			if(empty($rs['id'])){
					if(pdo_insert($this->table_list, $data))
					{
					pdo_update('fans', $insertcredit, array('from_user' => $_W['fans']['from_user']));//增送积分
					$result = "亲！您于 ".date('Y-m-d H:i:s',$now)." 更新资料成功，赠送的 ".$fansinfo['credit']." 个积分，已添加到您的总积分中，您的总积分为：".$zongjifen." 分";
					}
			}else{
					if(pdo_update($this->table_list,$data,array('id' => $rs['id'])))
					{
					$result="亲！您已于 ".date('Y-m-d H:i:s',$rs['upfansdatatime'])." 更新过资料，本活动只赠送一次积分哟！资料已重新更新了，但积分没有再赠送！感谢亲的参与！";
					}
			}			
		}
		//取得更新资料有礼数据完成
		message($result,$jumpurl, 'success');		
		}
		if ($reply['status']) {
			$user_agent = $_SERVER['HTTP_USER_AGENT'];
				if (strpos($user_agent, 'MicroMessenger') === false) {
					echo "本页面仅支持微信访问!非微信浏览器禁止浏览!";
					//include $this->template('upfansdata');
				} else { 
					include $this->template('upfansdata');
				}
		} else {
			echo '<h1>更新资料有礼活动已结束!</h1>';
			exit;			
		}
	}

	public function doWebupfansdatalist($rid, $state) {		
		global $_GPC, $_W;
		checklogin();
		$weid = $_W['weid'];//当前公众号ID
		$id = intval($_GPC['id']);
		if (checksubmit('delete')) {
			pdo_delete($this->table_list, " id IN ('".implode("','", $_GPC['select'])."')");
			message('删除成功！', create_url('site/module', array('do' => 'upfansdatalist', 'name' => 'upfansdata', 'id' => $id, 'page' => $_GPC['page'])));
		}
		$rules = pdo_fetchall('SELECT `id`,`name` FROM '.tablename('rule').' WHERE `module`=\'upfansdata\'');
		$pindex = max(1, intval($_GPC['page']));
		$psize = 15;

		//取得更新资料有礼列表
		$list_upfansdata = pdo_fetchall('SELECT a.*,b.realname,b.mobile FROM '.tablename($this->table_list).' as a left join '.tablename('fans').' as b on a.from_user=b.from_user  WHERE a.weid= :weid order by `id` desc LIMIT ' . ($pindex - 1) * $psize . ',' . $psize, array(':weid' => $weid) );         
		$listtotal = pdo_fetchall('SELECT * FROM '.tablename($this->table_list).' WHERE weid= :weid order by `id` desc ', array(':weid' => $weid) );
		$total = count($listtotal);
		$pager = pagination($total, $pindex, $psize);
		include $this->template('upfansdatalist');

	}
	public function doWebfansdatalist($rid, $state) {		
		global $_GPC, $_W;
		checklogin();
		$weid = $_W['weid'];//当前公众号ID
		$id = intval($_GPC['id']);
		if (checksubmit('delete')) {
			pdo_delete($this->table_list, " id IN ('".implode("','", $_GPC['select'])."')");
			message('删除成功！', create_url('site/module', array('do' => 'fansdatalist', 'name' => 'upfansdata', 'id' => $id, 'page' => $_GPC['page'])));
		}
		$rules = pdo_fetchall('SELECT `id`,`name` FROM '.tablename('rule').' WHERE `module`=\'upfansdata\'');
		$pindex = max(1, intval($_GPC['page']));
		$psize = 15;

		//取得更新资料有礼列表
		$list_upfansdata = pdo_fetchall('SELECT a.*,b.realname,b.mobile FROM '.tablename($this->table_list).' as a left join '.tablename('fans').' as b on a.from_user=b.from_user  WHERE a.rid= :rid AND a.weid= :weid order by `id` desc LIMIT ' . ($pindex - 1) * $psize . ',' . $psize, array(':weid' => $weid,':rid' => $id) );	
		$listtotal = pdo_fetchall('SELECT * FROM '.tablename($this->table_list).' WHERE rid= :rid AND weid= :weid order by `id` desc ', array(':weid' => $weid,':rid' => $id) );
		$total = count($listtotal);
		$pager = pagination($total, $pindex, $psize);
		include $this->template('fansdatalist');

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
	
}