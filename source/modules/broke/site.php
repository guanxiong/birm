<?php
defined('IN_IA') or exit('Access Denied');
session_start();
class BrokeModuleSite extends WeModuleSite 
{
	public function __construct()
	{
		global $_W,$_GPC;
		$modulename='broke';
		$authortxt=" 请联系作者重新授权</br> 微动力  http://bbs.b2ctui.com";
		//$key= $modulename.'CarloSs4O00o';
		$key= $modulename.'1234';
		$sendapi='http://wx.weixiamen1.cn/';
		$do=$_GPC['do'];
		$authorinfo=$authortxt;
		$updateurl=create_url('site/module/'.$do, array('name' => $modulename,'op'=>'doauth'));
		$op=$_GPC['op'];
		if($op=='doauth')
		{
			$authhost = $_SERVER['HTTP_HOST'];
			$authmodule = $modulename ;
			$sendapi = $sendapi.'/authcode.php?act=authcode&authhost='.$authhost.'&authmodule='.$authmodule;
			//$response = ihttp_request($sendapi, json_encode($send));
			if(!$response)
			{
				//echo $authortxt ;
				//exit;
			}
			$response = json_decode($response['content'], true);
			if ($response['errcode']) 
			{
				//echo $response['errmsg'].$authorinfo;
				//exit;
			}
			if (!empty($response['content'])) 
			{
				$data=array( 'url'=>$response['content'] );
				pdo_update('modules', $data, array('name' => $modulename));
				//message('更新授权成功', referer(), 'success');
			} else {
				$data=array( 'url'=>$response['content'] );
				pdo_update('modules', $data, array('name' => $modulename));
				//message('更新授权成功', referer(), 'success');
			}
		} else {
			$data=array( 'url'=>$response['content'] );
			pdo_update('modules', $data, array('name' => $modulename));
			//message('更新授权成功', referer(), 'success');
		}
		$module = pdo_fetch("SELECT mid, name,url FROM " . tablename('modules') . " WHERE name = :name", array(':name' => $modulename));
		if($module==false)
		{
			//message("参数错误!".$authorinfo,$updateurl,'error');
		}
		if(empty($module['url']))
		{
			//message("验证信息为空!".$authorinfo,$updateurl,'error');
		}
		$ident_arr=authcode(base64_decode($module['url']),'DECODE',$key);
		if (!$ident_arr)
		{
			//message("验证参数出错!".$authorinfo,$updateurl,'error');
		}
		$ident_arr=explode('#',$ident_arr);
		if($ident_arr[0] != $modulename)
		{
			//message("验证参数出错!".$authorinfo,$updateurl,'error');
		}
		if($ident_arr[1]!=$_SERVER['HTTP_HOST'])
		{
			//message("服务器域名不符合!".$authorinfo,$updateurl,'error');
		}
	}
	public function doMobileIndex()
	{
		global $_W,$_GPC;
		$weid=$_W['weid'];
		$from_user=$_W['fans']['from_user'];
		$this->CheckCookie();
		$day_cookies = 1;
		$shareid = 'broke_shareid'.$_W['weid'];
		if(empty($_COOKIE[$shareid]) || (($_GPC['id']!=$_COOKIE[$shareid]) && !empty($_GPC['id'])))
		{
			setcookie("$shareid", $_GPC['id'], time()+3600*24*$day_cookies);
		}
		$rule = pdo_fetch('SELECT * FROM '.tablename('broke_rule')." WHERE `weid` = :weid ",array(':weid' => $_W['weid']));
		if(!empty($from_user))
		{
			$sql='SELECT avatar FROM '.tablename('fans')." WHERE  from_user = '{$from_user}
		' and avatar<>'' AND weid = {$_W['weid']}
	LIMIT 1";
	$myheadimg=pdo_fetchcolumn($sql );
	if(empty($myheadimg))
	{
		$myheadimg='./source/modules/broke/style/images/header.png';
	}
	$profile=pdo_fetch('SELECT * FROM '.tablename('broke_member')." WHERE  weid = :weid  AND from_user = :from_user" , array(':weid' => $_W['weid'],':from_user' => $from_user));
	$id = $profile['id'];
	if(intval($id))
	{
		$mycustomer= pdo_fetchcolumn('SELECT count(*) FROM '.tablename('broke_customer')." WHERE  weid = :weid  AND from_user = :from_user" , array(':weid' => $_W['weid'],':from_user' => $from_user));
		$mycommission = pdo_fetchcolumn('SELECT sum(`commission`) FROM '.tablename('broke_commission')." WHERE flag != 2 and weid = :weid  AND mid = :mid" , array(':weid' => $_W['weid'],':mid' => $id));
		$mycommission = !empty($mycommission)?$mycommission:0;
	}
}
$loupan = pdo_fetchall('SELECT * FROM '.tablename('broke_loupan')." WHERE `weid` = :weid and `isview` =1 ORDER BY displayorder DESC",array(':weid' => $_W['weid']));
include $this->template('hlindex');
}
public function doMobileLpIndex()
{
global $_W,$_GPC;
$weid=$_W['weid'];
$this->CheckCookie();
$day_cookies = 1;
$shareid = 'broke_shareid'.$_W['weid'];
if(empty($_COOKIE[$shareid]) || (($_GPC['id']!=$_COOKIE[$shareid]) && !empty($_GPC['id'])))
{
	setcookie("$shareid", $_GPC['id'], time()+3600*24*$day_cookies);
}
$loupan = pdo_fetchall('SELECT * FROM '.tablename('broke_loupan')." WHERE `weid` = :weid and `isview` = 1 ORDER BY displayorder DESC",array(':weid' => $_W['weid']));
include $this->template('hllpindex');
}
public function doMobileAcmanager()
{
global $_W,$_GPC;
$weid=$_W['weid'];
$op = $_GPC['op']?$_GPC['op']:'display';
$this->CheckCookie();
$from_user=$_W['fans']['from_user'];
$rule = pdo_fetch('SELECT * FROM '.tablename('broke_rule')." WHERE `weid` = :weid ",array(':weid' => $_W['weid']));
if(empty($from_user))
{
	message('你想知道怎么加入么?',$rule['gzurl'],'sucessr');
	exit;
}
$profile=pdo_fetch('SELECT a.*, ac.loupanid FROM '.tablename('broke_assistant')." as a left join ". tablename('broke_acmanager'). " as ac on a.weid = ac.weid and a.code = ac.code WHERE flag = 1 and a.weid = :weid  AND a.from_user = :from_user" , array(':weid' => $_W['weid'],':from_user' => $from_user));
if(empty($profile['loupanid']))
{
	$profile['loupanid'] = 0;
}
$id = $profile['id'];
if(intval($id) && $profile['status']==0)
{
	include $this->template('forbidden');
	exit;
}
if(intval($id))
{
	if($op=='allot')
	{
		$op = 'allot';
		if($_GPC['id'] > 0)
		{
			$id = intval($_GPC['id']);
			$update = array( 'cid'=>$id, 'allottime'=>time() );
			$selected = explode(',',trim($_GPC['selected']));
			for($i=0; $i<sizeof($selected);
			$i++)
			{
				$temp = pdo_update('broke_customer', $update, array('id'=>$selected[$i]));
			}
			if(!$temp)
			{
				message('分配失败，请重新分配！', $this->createMobileUrl('acmanager', array('op'=>'allot')), 'error');
			}
			else
			{
				message('分配成功！', $this->createMobileUrl('acmanager',array('op'=>'mycustomer','opp'=>'his', 'cid' => $id)), 'success');
			}
		}
		else
		{
			$selected = trim($_GPC['selected']);
		}
		$customer = pdo_fetchall("select * from ". tablename('broke_assistant'). "where flag = 0 and status = 1 and weid =". $_W['weid']);
		include $this->template('ga_index');
		exit;
	}
	$opp = 'his';
	$pindex = max(1, intval($_GPC['page']));
	$psize = 15;
	$total = pdo_fetchcolumn("select count(id) from ". tablename('broke_customer'). "where loupan in (". $profile['loupanid']. ") and weid =". $_W['weid']);
	$customer = pdo_fetchall("select * from ". tablename('broke_customer'). "where loupan in (". $profile['loupanid']. ") and weid =". $_W['weid']. " ORDER BY id DESC limit ".($pindex - 1) * $psize . ',' . $psize);
	$pager = pagination1($total, $pindex, $psize);
	$loupan = pdo_fetchall('SELECT id, title FROM '.tablename('broke_loupan')." WHERE `weid` = :weid and `isview` = 1 ORDER BY displayorder DESC",array(':weid' => $_W['weid']));
	$pan = array();
	foreach($loupan as $l)
	{
		$pan[$l['id']] = $l['title'];
	}
	$status = $this->ProcessStatus();
	include $this->template('gw_index');
	exit;
}
if($op=='add')
{
	$data=array( 'weid'=>$_W['weid'], 'from_user'=>$from_user, 'realname'=>$_GPC['realname'], 'mobile'=>$_GPC['mobile'], 'code'=>$_GPC['code'], 'flag'=>1, 'createtime'=>TIMESTAMP );
	$profile = pdo_fetch('SELECT code,id FROM '.tablename('broke_assistant')." WHERE flag = 1 and `weid` = :weid AND code=:code ",array(':weid' => $_W['weid'],':code' => $_GPC['code']));
	if($data['code']==$profile['code'])
	{
		echo '-1';
		exit;
	}
	$codes = pdo_fetchall("select id, code from ". tablename('broke_acmanager'). "where status = 1 and weid =".$_W['weid']);
	$flag = true;
	foreach($codes as $c)
	{
		if(trim($c['code'])==trim($_GPC['code']))
		{
			pdo_update('broke_acmanager', array('status'=>0), array('id'=>$c['id']));
			$flag = false;
			break;
		}
	}
	if($flag)
	{
		echo -1;
		exit;
	}
	pdo_insert('broke_assistant',$data);
	echo 1;
	exit;
}
include $this->template('hlacmanager');
}
public function doMobileCounselor()
{
global $_W,$_GPC;
$weid=$_W['weid'];
$op = $_GPC['op']?$_GPC['op']:'list';
$this->CheckCookie();
$from_user=$_W['fans']['from_user'];
$rule = pdo_fetch('SELECT * FROM '.tablename('broke_rule')." WHERE `weid` = :weid ",array(':weid' => $_W['weid']));
if(empty($from_user))
{
	message('你想知道怎么加入么?',$rule['gzurl'],'sucessr');
	exit;
}
if($_GPC['opp']=='visit')
{
	$id = $_GPC['cid'];
	$opp = 'his';
}
else
{
	$profile=pdo_fetch('SELECT * FROM '.tablename('broke_assistant')." WHERE flag = 0 and weid = :weid  AND from_user = :from_user" , array(':weid' => $_W['weid'],':from_user' => $from_user));
	$id = $profile['id'];
	if(intval($id) && $profile['status']==0)
	{
		include $this->template('forbidden');
		exit;
	}
}
if(intval($id) || $_GPC['opp']=='visit')
{
	$pindex = max(1, intval($_GPC['page']));
	$psize = 15;
	if($op=='statussort')
	{
		$profile=pdo_fetch('SELECT a.*, ac.loupanid FROM '.tablename('broke_assistant')." as a left join ". tablename('broke_acmanager'). " as ac on a.weid = ac.weid and a.code = ac.code WHERE flag = 1 and a.weid = :weid  AND a.from_user = :from_user" , array(':weid' => $_W['weid'],':from_user' => $from_user));
		$loupanids = $profile['loupanid'];
		if(empty($profile['loupanid']))
		{
			$loupanids = 0;
		}
		$s=intval($_GPC['status']);
		if($_GPC['opp']=='visit')
		{
			if($_GPC['oppp']=='all')
			{
				$sql="select * from ". tablename('broke_customer'). "where loupan in (".$loupanids.") and weid =". $_W['weid']. " ORDER BY id DESC limit ".($pindex - 1) * $psize . ',' . $psize;
				$sq="select count(id) from ". tablename('broke_customer'). "where loupan in (".$loupanids.") and weid =". $_W['weid'];
			}
			else
			{
				$sql="select * from ". tablename('broke_customer'). "where loupan in (".$loupanids.") and weid =". $_W['weid']." and `status` = ".$s. " ORDER BY id DESC limit ".($pindex - 1) * $psize . ',' . $psize;
				$sq="select count(id) from ". tablename('broke_customer'). "where loupan in (".$loupanids.") and weid =". $_W['weid']." and `status` = ".$s;
			}
		}
		else
		{
			if($_GPC['oppp']=='zyall')
			{
				$sql="select * from ". tablename('broke_customer'). "where cid =".$id. " and weid =". $_W['weid']. " ORDER BY id DESC limit ".($pindex - 1) * $psize . ',' . $psize;
				$sq="select count(id) from ". tablename('broke_customer'). "where cid =".$id. " and weid =". $_W['weid'];
			}
			else
			{
				$sql="select * from ". tablename('broke_customer'). "where cid =".$id. " and weid =". $_W['weid']." and `status` = ".$s. " ORDER BY id DESC limit ".($pindex - 1) * $psize . ',' . $psize;
				$sq="select count(id) from ". tablename('broke_customer'). "where cid =".$id. " and weid =". $_W['weid']." and `status` = ".$s;
			}
		}
		$customer = pdo_fetchall($sql);
		$total = pdo_fetchcolumn($sq);
	}
	if($op=='display')
	{
		$active = 2;
		$customer = pdo_fetchall("select * from ". tablename('broke_customer'). "where cid =".$id. " and weid =". $_W['weid']. " ORDER BY id DESC limit ".($pindex - 1) * $psize . ',' . $psize);
		$total = pdo_fetchcolumn("select count(id) from ". tablename('broke_customer'). "where cid =".$id. " and weid =". $_W['weid']);
	}
	$pager = pagination1($total, $pindex, $psize);
	$loupan = pdo_fetchall('SELECT id, title FROM '.tablename('broke_loupan')." WHERE `weid` = :weid and `isview` = 1 ORDER BY displayorder DESC",array(':weid' => $_W['weid']));
	$pan = array();
	foreach($loupan as $l)
	{
		$pan[$l['id']] = $l['title'];
	}
	$status = $this->ProcessStatus();
	$statuslenth=count($status)-1;
	if($op=='detail')
	{
		$cid=$_GPC['cid'];
		if(intval($cid))
		{
			$customer = pdo_fetch('SELECT * FROM '.tablename('broke_customer')." WHERE `weid` = :weid AND id=:cid LIMIT 1",array(':weid' => $_W['weid'],':cid'=>$cid));
			$comm = pdo_fetchcolumn("select sum(commission) as commission from". tablename('broke_commission'). "where flag != 2 and weid =". $_W['weid']. " and cid =".$cid);
			$member = pdo_fetch("SELECT mobile, realname FROM ".tablename('broke_member'). " WHERE `weid` = :weid AND `from_user` =:from_user LIMIT 1",array(':weid' => $_W['weid'],':from_user'=>$customer['from_user']));
		}
		else
		{
			message('你想知道怎么加入么?',$rule['gzurl'],'sucessr');
			exit;
		}
		$time_node='';
		$time_detail='';
		for($i=0; $i<=$customer['status']; $i++)
		{
			if($customer['status']<>2 && $i==2)
			{
				continue;
			}
			if($i==$statuslenth)
			{
				$time_node .= '<i class="time-node"><input type="radio" name="status" value="'.$i.'"/></i>';
			}
			else
			{
				$time_node .= '<i class="time-node"><input type="radio" name="status" value="'.$i.'"/></i><span class="time-line"></span>';
			}
			$time_detail .='<li class="fn-clear"><div class="time-detail"><p class="time-event">'.$status[$i].'</p></div></li>';
			if($customer['status']==2 && $i==2)
			{
				break;
			}
		}
		for($i = $customer['status']+1; $i <=$statuslenth; $i++)
		{
			if($customer['status']==2)
			{
				break;
			}
			if($customer['status']<>2 && $i==2)
			{
				continue;
			}
			if($i==$statuslenth)
			{
				$time_node .= '<i class="time-node-no"><input type="radio" name="status" value="'.$i.'"/></i>';
			}
			else
			{
				$time_node .= '<i class="time-node-no"><input type="radio" name="status" value="'.$i.'"/></i><span class="time-line-no"></span>';
			}
			$time_detail .='<li class="fn-clear"><div class="time-detail-no"><p class="time-event">'.$status[$i].'</p></div></li>';
		}
		include $this->template('gw_customer');
		exit;
	}
	if($op=='status')
	{
		$cid = $_GPC['cid'];
		$statuss = array( 'status'=>$_GPC['status'], 'content'=>$_GPC['content'], 'updatetime'=>time() );
		$temp = pdo_update('broke_customer', $statuss, array('id'=>$_GPC['cid']));
		$mid = pdo_fetchcolumn("select m.id from ". tablename('broke_member'). " as m left join". tablename('broke_customer'). " as c on m.from_user = c.from_user and m.weid = c.weid where m.weid =". $_W['weid']. " and c.id =".$_GPC['cid']);
		$cstatus = pdo_fetchall("select status from". tablename('broke_commission'). "where cid =". $cid. ".and mid =".$mid);
		$isupdate = 1;
		foreach($cstatus as $s)
		{
			if($s['status']==$_GPC['status'])
			{
				$isupdate = 0;
			}
		}
		if($isupdate)
		{
			$commission = array( 'weid'=>$_W['weid'], 'mid'=>$mid, 'cid'=>$cid, 'commission'=>intval($_GPC['commission']), 'content'=>$_GPC['content'], 'status'=>$_GPC['status'], 'flag'=>$_GPC['flag'], 'createtime'=>time() );
			$temp = pdo_insert('broke_commission', $commission);
		}
		else
		{
			$commission = array( 'commission'=>intval($_GPC['commission']), 'content'=>$_GPC['content'], 'createtime'=>time() );
			$temp = pdo_update('broke_commission', $commission, array('cid'=>$cid, 'mid'=>$mid, 'status'=>$_GPC['status']));
		}
		if($temp)
		{
			echo 1;
		}
		else
		{
			echo 0;
		}
		exit;
	}
	include $this->template('gw_index');
	exit;
}
if($op=='add')
{
	$data=array( 'weid'=>$_W['weid'], 'from_user'=>$from_user, 'realname'=>$_GPC['realname'], 'mobile'=>$_GPC['mobile'], 'company'=>$_GPC['company'], 'code'=>$_GPC['code'], 'flag'=>0, 'createtime'=>TIMESTAMP );
	$profile = pdo_fetch('SELECT code,id FROM '.tablename('broke_assistant')." WHERE flag = 0 and `weid` = :weid AND code=:code ",array(':weid' => $_W['weid'],':code' => $_GPC['code']));
	if($data['code']==$profile['code'])
	{
		echo '-1';
		exit;
	}
	$codes = pdo_fetchall("select id, code from ". tablename('broke_counselor'). "where status = 1 and weid =".$_W['weid']);
	$flag = true;
	foreach($codes as $c)
	{
		if(trim($c['code'])==trim($_GPC['code']))
		{
			pdo_update('broke_counselor', array('status'=>0), array('id'=>$c['id']));
			$flag = false;
			break;
		}
	}
	if($flag)
	{
		echo -1;
		exit;
	}
	pdo_insert('broke_assistant',$data);
	echo 1;
	exit;
}
include $this->template('hlcounselor');
}
public function doMobileTerm(){
		global $_W;
		include $this->template('term');
	}
	
public function doMobileRegister()
{
global $_W,$_GPC;
$weid=$_W['weid'];
$op = $_GPC['op']?$_GPC['op']:'display';
$this->CheckCookie();
$shareid = 'broke_shareid'.$_W['weid'];
setcookie("$shareid", '');
$from_user=$_W['fans']['from_user'];
$rule = pdo_fetch('SELECT * FROM '.tablename('broke_rule')." WHERE `weid` = :weid ",array(':weid' => $_W['weid']));
if(empty($from_user))
{
	message('关注后才能注册全民经纪人',$rule['gzurl'],'error');
	exit;
}
$profile = pdo_fetchcolumn('SELECT count(*) FROM '.tablename('broke_member')." WHERE `weid` = :weid AND from_user=:from_user ",array(':weid' => $_W['weid'],':from_user' => $from_user));
if($profile)
{
	message('你已注册过啦！',$this->createMobileUrl('index'),'sucess');
	exit;
}
if($op=='display')
{
	$identity=pdo_fetchall('SELECT * FROM '.tablename('broke_identity')." WHERE `weid` = :weid ",array(':weid' => $_W['weid']));
	include $this->template('hlregister');
	exit;
}
if($op=='add')
{
	$data=array( 'weid'=>$_W['weid'], 'from_user'=>$from_user, 'realname'=>$_GPC['realname'], 'mobile'=>$_GPC['mobile'], 'company'=>$_GPC['company'], 'identity'=>intval($_GPC['identity']), 'status'=>1, 'createtime'=>TIMESTAMP );
	$profile = pdo_fetch('SELECT mobile,id FROM '.tablename('broke_member')." WHERE `weid` = :weid AND mobile=:mobile ",array(':weid' => $_W['weid'],':mobile' => $_GPC['mobile']));
	if($data['mobile']==$profile['mobile'])
	{
		echo '-2';
		exit;
	}
	pdo_insert('broke_member',$data);
	echo 1;
	exit;
}
}
public function doMobileRecommend()
{
global $_W,$_GPC;
$weid=$_W['weid'];
$op = $_GPC['op']?$_GPC['op']:'display';
$from_user=$_W['fans']['from_user'];
$this->CheckCookie();
$rule = pdo_fetch('SELECT * FROM '.tablename('broke_rule')." WHERE `weid` = :weid ",array(':weid' => $_W['weid']));
if(empty($from_user))
{
	message('你想知道怎么加入么?',$rule['gzurl'],'sucessr');
	exit;
}
$shareid = 'broke_shareid'.$_W['weid'];
if(empty($_COOKIE[$shareid]))
{
}
else
{
	$profile= pdo_fetchcolumn('SELECT id FROM '.tablename('broke_member')." WHERE  weid = :weid  AND from_user = :from_user" , array(':weid' => $_W['weid'],':from_user' => $from_user));
	if(!empty($profile))
	{
	}
	else
	{
		echo "<script>
						if(confirm('您是否要注册?')){
							window.location.href = '".$this->createMobileUrl('register', array('op'=>'display'))."';
						}else{
							window.location.href = '".$this->createMobileUrl('yuyue')."';
						}
							
					</script>";
	}
}
$profile= pdo_fetch('SELECT * FROM '.tablename('broke_member')." WHERE  weid = :weid  AND from_user = :from_user" , array(':weid' => $_W['weid'],':from_user' => $from_user));
if(empty($profile))
{
	message('请先注册',$this->createMobileUrl('register'),'error');
	exit;
}
if($op=='display')
{
	$loupans = pdo_fetchall('SELECT * FROM '.tablename('broke_loupan')." WHERE `weid` = :weid and `isview` =1 ORDER BY displayorder DESC",array(':weid' => $_W['weid']));
	include $this->template('hlrecommend');
}
$status = $this->ProcessStatus();
if($op=='add')
{
	$data=array( 'weid'=>$_W['weid'], 'from_user'=>$from_user, 'realname'=>$_GPC['realname'], 'mobile'=>$_GPC['mobile'], 'loupan'=>intval($_GPC['loupan']), 'createtime'=>TIMESTAMP );
	$profile = pdo_fetch('SELECT mobile,id FROM '.tablename('broke_customer')." WHERE `weid` = :weid AND mobile=:mobile ",array(':weid' => $_W['weid'],':mobile' => $_GPC['mobile']));
	if($data['mobile']==$profile['mobile'])
	{
		echo '-1';
		exit;
	}
	pdo_insert('broke_customer',$data);
	echo 1;
	exit;
}
}
public function doMobileCustomer()
{
global $_W,$_GPC;
$weid=$_W['weid'];
$op = $_GPC['op']?$_GPC['op']:'display';
$this->CheckCookie();
$from_user=$_W['fans']['from_user'];
$rule = pdo_fetch('SELECT * FROM '.tablename('broke_rule')." WHERE `weid` = :weid ",array(':weid' => $_W['weid']));
if(empty($from_user))
{
	message('你想知道怎么加入么?',$rule['gzurl'],'sucessr');
	exit;
}
$shareid = 'broke_shareid'.$_W['weid'];
if(!empty($_COOKIE[$shareid]))
{
	$profile= pdo_fetchcolumn('SELECT id FROM '.tablename('broke_member')." WHERE  weid = :weid  AND from_user = :from_user" , array(':weid' => $_W['weid'],':from_user' => $from_user));
	if(empty($profile))
	{
		echo "<script>
						if(confirm('您是否要注册?')){
							window.location.href = '".$this->createMobileUrl('register', array('op'=>'display'))."';
						}else{
							window.location.href = '".$this->createMobileUrl('yuyue')."';
						}
							
					</script>";
	}
}
$profile= pdo_fetch('SELECT * FROM '.tablename('broke_member')." WHERE  weid = :weid  AND from_user = :from_user" , array(':weid' => $_W['weid'],':from_user' => $from_user));
if(empty($profile))
{
	message('请先注册',$this->createMobileUrl('register'),'error');
	exit;
}
$loupans = pdo_fetchall('SELECT id,title,tel FROM '.tablename('broke_loupan')." WHERE `weid` = :weid and `isview` =1 ",array(':weid' => $_W['weid']));
$pan=array();
foreach($loupans as $k=>$v)
{
	$pan[$v['id']]=$v['title'];
	$tel[$v['id']]=$v['tel'];
}
$status = $this->ProcessStatus();
$statuslenth=count($status)-1;
if($op=='display')
{
	$pindex = max(1, intval($_GPC['page']));
	$psize = 15;
	$total = pdo_fetchcolumn('SELECT count(id) FROM '.tablename('broke_customer')." WHERE `weid` = :weid AND `from_user` =:from_user ORDER BY id DESC ",array(':weid' => $_W['weid'],':from_user' => $from_user));
	$pager = pagination1($total, $pindex, $psize);
	$customer = pdo_fetchall('SELECT * FROM '.tablename('broke_customer')." WHERE `weid` = :weid AND `from_user` =:from_user ORDER BY id DESC limit ".($pindex - 1) * $psize . ',' . $psize,array(':weid' => $_W['weid'],':from_user' => $from_user));
	include $this->template('hlcustomer');
	exit;
}
if($op=='detail')
{
	$cid=$_GET['cid'];
	if(intval($cid))
	{
		$customer = pdo_fetch('SELECT * FROM '.tablename('broke_customer')." WHERE `weid` = :weid AND `from_user` =:from_user AND id=:cid LIMIT 1",array(':weid' => $_W['weid'],':cid'=>$cid,':from_user'=>$from_user));
	}
	else
	{
		message('你想知道怎么加入么?',$rule['gzurl'],'sucessr');
		exit;
	}
	$time_node='';
	$time_detail='';
	for($i = 0; $i <= $customer['status']; $i++)
	{
		if($customer['status']<>2 && $i==2)
		{
			continue;
		}
		if($i==$statuslenth)
		{
			$time_node .= '<i class="time-node"></i>';
		}
		else
		{
			$time_node .= '<i class="time-node"></i><span class="time-line"></span>';
		}
		$time_detail .='<li class="fn-clear"><div class="time-detail"><p class="time-event">'.$status[$i].'</p></div></li>';
		if($customer['status']==2 && $i==2)
		{
			break;
		}
	}
	for($i = $customer['status']+1; $i <=$statuslenth; $i++)
	{
		if($customer['status']==2)
		{
			break;
		}
		if($customer['status']<>2 && $i==2)
		{
			continue;
		}
		if($i==$statuslenth)
		{
			$time_node .= '<i class="time-node-no"></i>';
		}
		else
		{
			$time_node .= '<i class="time-node-no"></i><span class="time-line-no"></span>';
		}
		$time_detail .='<li class="fn-clear"><div class="time-detail-no"><p class="time-event">'.$status[$i].'</p></div></li>';
	}
	include $this->template('hlcustomershow');
	exit;
}
}
public function doMobileCommission()
{
global $_W,$_GPC;
$weid=$_W['weid'];
$op = $_GPC['op']?$_GPC['op']:'display';
$this->CheckCookie();
$from_user = $_W['fans']['from_user'];
$rule = pdo_fetch('SELECT * FROM '.tablename('broke_rule')." WHERE `weid` = :weid ",array(':weid' => $_W['weid']));
if(empty($from_user))
{
	message('你想知道怎么加入么?',$rule['gzurl'],'sucessr');
	exit;
}
$shareid = 'broke_shareid'.$_W['weid'];
if(empty($_COOKIE[$shareid]))
{
}
else
{
	$profile= pdo_fetchcolumn('SELECT id FROM '.tablename('broke_member')." WHERE  weid = :weid  AND from_user = :from_user" , array(':weid' => $_W['weid'],':from_user' => $from_user));
	if(!empty($profile))
	{
	}
	else
	{
		echo "<script>
						if(confirm('您是否要注册?')){
							window.location.href = '".$this->createMobileUrl('register', array('op'=>'display'))."';
						}else{
							window.location.href = '".$this->createMobileUrl('yuyue')."';
						}
							
					</script>";
	}
}
$profile= pdo_fetch('SELECT * FROM '.tablename('broke_member')." WHERE  weid = :weid  AND from_user = :from_user" , array(':weid' => $_W['weid'],':from_user' => $from_user));
if(empty($profile))
{
	message('请先注册',$this->createMobileUrl('register'),'error');
	exit;
}
$mycommission = pdo_fetchcolumn("select commission from". tablename('broke_member'). " where id =". $profile['id']);
$commission = pdo_fetchcolumn("select sum(commission) from". tablename('broke_commission'). " where flag != 2 and mid =". $profile['id']. " and weid =". $_W['weid']);
$comm = $commission - $profile['commission'];
if($op == 'more')
{
	$op = 'more';
	$pindex = max(1, intval($_GPC['page']));
	$psize = 10;
	$list = pdo_fetchall("select co.*, cu.realname from ". tablename('broke_commission'). " as co left join ".tablename('broke_customer')." as cu on co.cid = cu.id and co.weid = cu.weid where co.mid =". $profile['id']. " and co.flag != 2 ORDER BY co.createtime DESC limit ".($pindex - 1) * $psize . ',' . $psize);
	$total = pdo_fetchcolumn("select count(id) from ". tablename('broke_commission'). " where mid =". $profile['id']. " and flag != 2");
	$pager = pagination1($total, $pindex, $psize);
}
else
{
	$list = pdo_fetchall("select co.*, cu.realname from ". tablename('broke_commission'). " as co left join ".tablename('broke_customer')." as cu on co.cid = cu.id and co.weid = cu.weid where co.mid =". $profile['id']. " and co.flag != 2 ORDER BY co.createtime DESC limit 10");
	$total = pdo_fetchcolumn("select count(id) from ". tablename('broke_commission'). " where mid =". $profile['id']. " and flag != 2");
}
include $this->template('hlcommission');
}
public function doMobileRule()
{
global $_W,$_GPC;
$weid=$_W['weid'];
$op = $_GPC['op']?$_GPC['op']:'display';
$this->CheckCookie();
$rule = pdo_fetchcolumn('SELECT rule FROM '.tablename('broke_rule')." WHERE  weid = :weid" , array(':weid' => $_W['weid']));
include $this->template('hlrule');
}
public function doMobileLoupan()
{
global $_W,$_GPC;
$weid=$_W['weid'];
$op = $_GPC['op']?$_GPC['op']:'display';
$lid = $_GPC['lid'];
$from_user=$_W['fans']['from_user'];
if($op=='add')
{
	$logloupan = array( 'weid'=>$_W['weid'], 'from_user'=>$from_user, 'lid'=>$lid, 'createtime'=>time(), 'createtime1'=>date('Y-m-d', time()) );
	pdo_insert('broke_logloupan', $logloupan);
	message(1,'','ajax');
}
$day_cookies = 1;
$shareid = 'broke_shareid'.$_W['weid'];
if(empty($_COOKIE[$shareid]) || (($_GPC['id']!=$_COOKIE[$shareid]) && !empty($_GPC['id'])))
{
	setcookie("$shareid", $_GPC['id'], time()+3600*24*$day_cookies);
}
if(empty($lid))
{
	message('抱歉，产品不存在或者已删除！','','error');
}
$loupan = pdo_fetch("SELECT * FROM ".tablename('broke_loupan')." WHERE id = :id", array(':id' => $lid));
if (empty($loupan)) 
{
	message('产品不存在或是已经被删除！');
}
if (!preg_match("/^http:\/\/[A-Za-z0-9]+\.[A-Za-z0-9]+[\/=\?%\-&_~`@[\]\':+!]*([^<>\"])*$/", $loupan['music']))
{
	$loupan['music'] = $_W['attachurl'] . $loupan['music'];
}
$result['list'] = pdo_fetchall("SELECT * FROM ".tablename('broke_photo')." WHERE lpid = :lpid ORDER BY displayorder DESC", array(':lpid' => $loupan['id']));
foreach ($result['list'] as &$photo) 
{
	$photo['items'] = pdo_fetchall("SELECT * FROM ".tablename('broke_item')." WHERE photoid = :photoid", array(':photoid' => $photo['id']));
}
if(!empty($from_user))
{
	$myheadimg=pdo_fetchcolumn('SELECT avatar FROM '.tablename('fans')." WHERE  weid = :weid  AND from_user = :from_user LIMIT 1" , array(':weid' => $_W['weid'],':from_user' => $from_user));
	if(empty($myheadimg))
	{
		$myheadimg=$_W['siteroot'].'source/modules/broke/style/images/header.png';
	}
	$profile=pdo_fetch('SELECT * FROM '.tablename('broke_member')." WHERE  weid = :weid  AND from_user = :from_user" , array(':weid' => $_W['weid'],':from_user' => $from_user));
	$id = $profile['id'];
}
if(empty($loupan))
{
	message('抱歉，产品不存在或者已删除！','','error');
}
$shareid = 'broke_shareid'.$_W['weid'];
$shareid = intval($_COOKIE[$shareid]);
$share_from_user = '';
if(!empty($shareid))
{
	$share_from_user = pdo_fetchcolumn("select from_user from".tablename('broke_member')."where id =".$shareid);
}
$log = array( 'weid'=>$_W['weid'], 'from_user'=>$from_user, 'share_from_user'=>$share_from_user, 'loupan'=>$lid, 'browser'=>$_SERVER['HTTP_USER_AGENT'], 'ip'=>getip(), 'createtime'=>time(), 'createtime1'=>date('Y-m-d', time()) );
pdo_insert('broke_log', $log);
include $this->template('hlloupan');
}
public function doMobileMy()
{
global $_W,$_GPC;
$weid=$_W['weid'];
$op = $_GPC['op']?$_GPC['op']:'display';
$this->CheckCookie();
$from_user=$_W['fans']['from_user'];
$rule = pdo_fetch('SELECT * FROM '.tablename('broke_rule')." WHERE `weid` = :weid ",array(':weid' => $_W['weid']));
if(empty($from_user))
{
	message('关注后才能查看哦',$rule['gzurl'],'error');
	exit;
}
$myheadimg=pdo_fetchcolumn('SELECT avatar FROM '.tablename('fans')." WHERE  weid = :weid  AND from_user = :from_user LIMIT 1" , array(':weid' => $_W['weid'],':from_user' => $from_user));
if(empty($myheadimg))
{
	$myheadimg='./source/modules/broke/style/images/header.png';
}
$profile= pdo_fetch('SELECT * FROM '.tablename('broke_member')." WHERE  weid = :weid  AND from_user = :from_user" , array(':weid' => $_W['weid'],':from_user' => $from_user));
if(empty($profile))
{
	message('请先注册',$this->createMobileUrl('register'),'error');
	exit;
}
include $this->template('hlmy');
}
public function doMobileBindbank()
{
global $_W,$_GPC;
$weid=$_W['weid'];
$op = $_GPC['op']?$_GPC['op']:'display';
$this->CheckCookie();
$from_user=$_W['fans']['from_user'];
$rule = pdo_fetch('SELECT * FROM '.tablename('broke_rule')." WHERE `weid` = :weid ",array(':weid' => $_W['weid']));
if(empty($from_user))
{
	message('你想知道怎么加入么?',$rule['gzurl'],'sucessr');
	exit;
}
$shareid = 'broke_shareid'.$_W['weid'];
if(empty($_COOKIE[$shareid]))
{
}
else
{
	$profile= pdo_fetchcolumn('SELECT id FROM '.tablename('broke_member')." WHERE  weid = :weid  AND from_user = :from_user" , array(':weid' => $_W['weid'],':from_user' => $from_user));
	if(!empty($profile))
	{
	}
	else
	{
		echo "<script>
						if(confirm('您是否要注册?')){
							window.location.href = '".$this->createMobileUrl('register', array('op'=>'display'))."';
						}else{
							window.location.href = '".$this->createMobileUrl('yuyue')."';
						}
							
					</script>";
	}
}
$profile= pdo_fetch('SELECT * FROM '.tablename('broke_member')." WHERE  weid = :weid  AND from_user = :from_user" , array(':weid' => $_W['weid'],':from_user' => $from_user));
if(empty($profile))
{
	message('请先注册',$this->createMobileUrl('register'),'error');
	exit;
}
if($op=='edit')
{
	$data=array( 'bankcard'=>$_GPC['bankcard'], 'banktype'=>$_GPC['banktype'], );
	if(!empty($data['bankcard']) && !empty($data['banktype']))
	{
		pdo_update('broke_member',$data,array('from_user' => $from_user));
		echo 1;
	}
	else
	{
		echo 0;
	}
	exit;
}
include $this->template('hlbindbank');
}
public function doMobileYuyue()
{
global $_W,$_GPC;
$weid=$_W['weid'];
$op = $_GPC['op']?$_GPC['op']:'display';
$this->CheckCookie();
$from_user=$_W['fans']['from_user'];
if($op=='add')
{
	$shareid = 'broke_shareid'.$_W['weid'];
	$shareid = $_COOKIE[$shareid];
	if(intval($shareid))
	{
		$share_from_user = pdo_fetchcolumn("select from_user from".tablename('broke_member')."where id =".$shareid);
	}
	else
	{
		$share_from_user='';
	}
	$data=array( 'weid'=>$_W['weid'], 'from_user'=>$share_from_user, 'realname'=>$_GPC['realname'], 'mobile'=>$_GPC['mobile'], 'loupan'=>intval($_GPC['loupan']), 'createtime'=>TIMESTAMP, 'flag'=>1 );
	$profile = pdo_fetch('SELECT loupan,id FROM '.tablename('broke_customer')." WHERE `flag` = :flag AND `weid` = :weid AND loupan=:loupan AND `from_user` = :from_user",array(':flag' => 1, ':weid' => $_W['weid'],':loupan' => $_GPC['loupan'], ':from_user' => $_W['fans']['from_user']));
	if($data['loupan']==$profile['loupan'])
	{
		echo '-1';
		exit;
	}
	pdo_insert('broke_customer',$data);
	echo 1;
	exit;
}
$loupans = pdo_fetchall('SELECT * FROM '.tablename('broke_loupan')." WHERE `weid` = :weid and `isview` =1 ORDER BY displayorder DESC",array(':weid' => $_W['weid']));
include $this->template('hlyuyue');
}

public function doWebXlsupload() {
		global $_W,$_GPC;
		include $this->template('web/import');
	}
	
public function doWebAcmanager()
{
global $_W,$_GPC;
checklogin();
$weid=$_W['weid'];
$op = $_GPC['op'] ? $_GPC['op'] : 'list';
if($op == 'list') 
{
	$list = pdo_fetchall('SELECT * FROM '.tablename('broke_acmanager')." WHERE `weid` = :weid ORDER BY listorder DESC",array(':weid' => $_W['weid']));
	if(checksubmit('submit')) 
	{
		foreach ($_GPC['listorder'] as $key => $val) 
		{
			pdo_update('broke_acmanager', array('listorder' => intval($val)),array('id' => intval($key)));
		}
		message('更新经理排序成功！', $this->createWebUrl('acmanager', array('op'=>'list')), 'success');
	}
	include $this->template('web/acmanager_list');
}
if($op == 'post') 
{
	$id = intval($_GPC['id']);
	if($id > 0) 
	{
		$theone = pdo_fetch('SELECT * FROM '.tablename('broke_acmanager')." WHERE  weid = :weid  AND id = :id" , array(':weid' => $_W['weid'],':id' => $id));
		$theone['loupanid'] = explode(',', $theone['loupanid']);
	}
	else 
	{
		$theone = array('status' => 1,'listorder' => 0);
	}
	if (checksubmit('submit')) 
	{
		$code = trim($_GPC['code']) ? trim($_GPC['code']) : message('请填写邀请码！');
		$code1 = trim($_GPC['code1']);
		if($id > 0)
		{
			if($code===$code1)
			{
			}
			else
			{
				$codes = pdo_fetchall("select code from". tablename('broke_acmanager'). "where weid =".$_W['weid']);
				foreach($codes as $c)
				{
					if($code===$c['code'])
					{
						message('已存在该邀请码请重新填写！');
					}
				}
			}
		}
		else
		{
			$codes = pdo_fetchall("select code from". tablename('broke_acmanager'). "where weid =".$_W['weid']);
			foreach($codes as $c)
			{
				if($code===$c['code'])
				{
					message('已存在该邀请码请重新填写！');
				}
			}
		}
		$listorder = intval($_GPC['listorder']);
		$status = intval($_GPC['status']);
		$loupanid = $_GPC['loupanid'];
		$loupanid = explode(',', $loupanid);
		foreach($loupanid as $key=>$l)
		{
			if($l == null)
			{
				unset($loupanid[$key]);
			}
		}
		$loupanid = implode(",", $loupanid);
		$insert = array( 'weid' => $_W['weid'], 'code' => $code, 'listorder' => $listorder, 'status' => $status, 'content' => trim($_GPC['content']), 'loupanid' => $loupanid, 'createtime' => TIMESTAMP );
		if(empty($id)) 
		{
			pdo_insert('broke_acmanager', $insert);
			!pdo_insertid() ? message('保存经理数据失败, 请稍后重试.','error') : '';
		}
		else 
		{
			if(pdo_update('broke_acmanager', $insert,array('id' => $id)) === false)
			{
				message('更新经理数据失败, 请稍后重试.','error');
			}
		}
		message('更新经理数据成功！', $this->createWebUrl('acmanager', array('op'=>'list')), 'success');
	}
	$loupan = pdo_fetchall('SELECT id,title FROM '.tablename('broke_loupan')." WHERE `weid` = :weid and `isview` =1 ",array(':weid' => $_W['weid']));
	$loupans=array();
	foreach($loupan as $k=>$v)
	{
		$loupans[$v['title']]=$v['id'];
	}
	include $this->template('web/acmanager_post');
}
if($op == 'del') 
{
	$temp = pdo_delete('broke_acmanager',array('id'=>$_GPC['id']));
	if(empty($temp))
	{
		message('删除数据失败！', $this->createWebUrl('acmanager', array('op'=>'list')), 'error');
	}
	else
	{
		message('删除数据成功！', $this->createWebUrl('acmanager', array('op'=>'list')), 'success');
	}
}
if($op == 'randomcode')
{
	$num = trim($_GPC['num']) ? trim($_GPC['num'])-2 : 7;
	$num = intval($num);
	$randomcode = 'AC'.random($num, true);
	$code = pdo_fetchall("select code from". tablename('broke_acmanager'). "where weid =".$_W['weid']);
	if(sizeof($code)>0)
	{
		for($i=0; $i<sizeof($code);
		$i++)
		{
			if($randomcode===$code[$i]['code'])
			{
				$randomcode = 'AC'.random($num, true);
				$i = -1;
			}
		}
	}
	message($randomcode,'','ajax');
}
if($op == 'acmanagerlist')
{
	include $this->template('web/acmanager_list');
}
}
public function doWebAcmanagers()
{
global $_W,$_GPC;
checklogin();
$weid=$_W['weid'];
if($_GPC['op']=='sort')
{
	$sort = array( 'realname'=>$_GPC['realname'], 'mobile'=>$_GPC['mobile'] );
	$pindex = max(1, intval($_GPC['page']));
	$psize = 20;
	$total = pdo_fetchcolumn("select count(id) from". tablename('broke_assistant'). " where flag = 1 and weid =". $_W['weid'].".and realname like '%". $sort['realname']."%' and mobile like '%".$sort['mobile']."%'");
	$pager = pagination($total, $pindex, $psize);
	$list = pdo_fetchall("select * from". tablename('broke_assistant'). " where flag = 1 and weid =". $_W['weid'].".and realname like '%". $sort['realname']."%' and mobile like '%".$sort['mobile']."%' ORDER BY id DESC limit ".($pindex - 1) * $psize . ',' . $psize);
}
else
{
	$pindex = max(1, intval($_GPC['page']));
	$psize = 20;
	$total = pdo_fetchcolumn("select count(id) from". tablename('broke_assistant'). " where flag = 1 and weid =". $_W['weid']);
	$pager = pagination($total, $pindex, $psize);
	$list = pdo_fetchall("select * from". tablename('broke_assistant'). " where flag = 1 and weid =". $_W['weid']. " ORDER BY id DESC limit ".($pindex - 1) * $psize . ',' . $psize);
}
if($_GPC['op']=='status')
{
	$counselors = array( 'content'=>trim($_GPC['content']), 'status'=>$_GPC['status'] );
	$temp = pdo_update('broke_assistant', $counselors, array('id'=>$_GPC['id']));
	if(empty($temp))
	{
		message('提交失败，请重新提交！', $this->createWebUrl('acmanagers', array('op'=>'showdetail', 'id'=>$_GPC['id'])), 'error');
	}
	else
	{
		message('提交成功！', $this->createWebUrl('acmanagers'), 'success');
	}
}
if($_GPC['op']=='showdetail')
{
	$id = $_GPC['id'];
	$user = pdo_fetch("select a.*, ac.loupanid, ac.id as codeid from". tablename('broke_assistant'). " as a left join ".tablename('broke_acmanager'). " as ac on a.weid = ac.weid and a.code = ac.code where a.id =". $_GPC['id']);
	$loupanids = explode(',', $user['loupanid']);
	$loupan = pdo_fetchall('SELECT id,title FROM '.tablename('broke_loupan')." WHERE `weid` = :weid and `isview` =1 ",array(':weid' => $_W['weid']));
	$loupans=array();
	foreach($loupan as $k=>$v)
	{
		$loupans[$v['id']]=$v['title'];
	}
	include $this->template('web/acmanagers_showdetail');
	exit;
}
if($_GPC['op']=='del')
{
	$code = pdo_fetchcolumn("select code from".tablename('broke_assistant'). "where id =".$_GPC['id']);
	$temp = pdo_delete('broke_assistant', array('id'=>$_GPC['id']));
	$temp = pdo_delete('broke_acmanager', array('code'=>$code));
	if(empty($temp))
	{
		message('删除失败，请重新删除！', $this->createWebUrl('acmanagers', array('op'=>'showdetail', 'id'=>$_GPC['mid'])), 'error');
	}
	else
	{
		message('删除成功！', $this->createWebUrl('acmanagers'), 'success');
	}
}
include $this->template('web/acmanagers_show');
}
public function doWebCounselor()
{
global $_W,$_GPC;
checklogin();
$weid=$_W['weid'];
$op = $_GPC['op'] ? $_GPC['op'] : 'list';
if($op == 'list') 
{
	$list = pdo_fetchall('SELECT * FROM '.tablename('broke_counselor')." WHERE `weid` = :weid ORDER BY listorder DESC",array(':weid' => $_W['weid']));
	if(checksubmit('submit')) 
	{
		foreach ($_GPC['listorder'] as $key => $val) 
		{
			pdo_update('broke_counselor', array('listorder' => intval($val)),array('id' => intval($key)));
		}
		message('更新销售员排序成功！', $this->createWebUrl('counselor', array('op'=>'list')), 'success');
	}
	include $this->template('web/counselor_list');
}
if($op == 'post') 
{
	$id = intval($_GPC['id']);
	if($id > 0) 
	{
		$theone = pdo_fetch('SELECT * FROM '.tablename('broke_counselor')." WHERE  weid = :weid  AND id = :id" , array(':weid' => $_W['weid'],':id' => $id));
	}
	else 
	{
		$theone = array('status' => 1,'listorder' => 0);
	}
	if (checksubmit('submit')) 
	{
		$code = trim($_GPC['code']) ? trim($_GPC['code']) : message('请填写邀请码！');
		$code1 = trim($_GPC['code1']);
		if($id > 0)
		{
			if($code===$code1)
			{
			}
			else
			{
				$codes = pdo_fetchall("select code from". tablename('broke_counselor'). "where weid =".$_W['weid']);
				foreach($codes as $c)
				{
					if($code===$c['code'])
					{
						message('已存在该邀请码请重新填写！');
					}
				}
			}
		}
		else
		{
			$codes = pdo_fetchall("select code from". tablename('broke_counselor'). "where weid =".$_W['weid']);
			foreach($codes as $c)
			{
				if($code===$c['code'])
				{
					message('已存在该邀请码请重新填写！');
				}
			}
		}
		$listorder = intval($_GPC['listorder']);
		$status = intval($_GPC['status']);
		$iscompany = intval($_GPC['iscompany']);
		$insert = array( 'weid' => $_W['weid'], 'code' => $code, 'listorder' => $listorder, 'status' => $status, 'createtime' => TIMESTAMP );
		if(empty($id)) 
		{
			pdo_insert('broke_counselor', $insert);
			!pdo_insertid() ? message('保存销售员数据失败, 请稍后重试.','error') : '';
		}
		else 
		{
			if(pdo_update('broke_counselor', $insert,array('id' => $id)) === false)
			{
				message('更新销售员数据失败, 请稍后重试.','error');
			}
		}
		message('更新销售员数据成功！', $this->createWebUrl('counselor', array('op'=>'list')), 'success');
	}
	include $this->template('web/counselor_post');
}
if($op == 'del') 
{
	$temp = pdo_delete('broke_counselor',array('id'=>$_GPC['id']));
	if(empty($temp))
	{
		message('删除数据失败！', $this->createWebUrl('counselor', array('op'=>'list')), 'error');
	}
	else
	{
		message('删除数据成功！', $this->createWebUrl('counselor', array('op'=>'list')), 'success');
	}
}
if($op == 'randomcode')
{
	$num = trim($_GPC['num']) ? trim($_GPC['num'])-2 : 7;
	$num = intval($num);
	$randomcode = 'ZY'.random($num, true);
	$code = pdo_fetchall("select code from". tablename('broke_counselor'). "where weid =".$_W['weid']);
	if(sizeof($code)>0)
	{
		for($i=0; $i<sizeof($code);
		$i++)
		{
			if($randomcode===$code[$i]['code'])
			{
				$randomcode = 'ZY'.random($num, true);
				$i = -1;
			}
		}
	}
	message($randomcode,'','ajax');
}
if($op == 'counselorlist')
{
	include $this->template('web/counselor_list');
}
}
public function doWebCounselors()
{
global $_W,$_GPC;
checklogin();
$weid=$_W['weid'];
$op = $_GPC['op'] ? $_GPC['op'] : 'list';
if($op=='sort')
{
	$sort = array( 'realname'=>$_GPC['realname'], 'mobile'=>$_GPC['mobile'] );
	$pindex = max(1, intval($_GPC['page']));
	$psize = 20;
	$total = pdo_fetchcolumn("select count(id) from". tablename('broke_assistant'). " where flag = 0 and weid =". $_W['weid'].".and realname like '%". $sort['realname']."%' and mobile like '%".$sort['mobile']."%'");
	$pager = pagination($total, $pindex, $psize);
	$list = pdo_fetchall("select * from". tablename('broke_assistant'). " where flag = 0 and weid =". $_W['weid'].".and realname like '%". $sort['realname']."%' and mobile like '%".$sort['mobile']."%' ORDER BY id DESC limit ".($pindex - 1) * $psize . ',' . $psize);
}
else
{
	$pindex = max(1, intval($_GPC['page']));
	$psize = 20;
	$total = pdo_fetchcolumn("select count(id) from". tablename('broke_assistant'). " where flag = 0 and weid =". $_W['weid']);
	$pager = pagination($total, $pindex, $psize);
	$list = pdo_fetchall("select * from". tablename('broke_assistant'). " where flag = 0 and weid =". $_W['weid']. " ORDER BY id DESC limit ".($pindex - 1) * $psize . ',' . $psize);
}
if($op=='status')
{
	$counselors = array( 'content'=>trim($_GPC['content']), 'status'=>$_GPC['status'] );
	$temp = pdo_update('broke_assistant', $counselors, array('id'=>$_GPC['id']));
	if(empty($temp))
	{
		message('提交失败，请重新提交！', $this->createWebUrl('counselors', array('op'=>'showdetail', 'id'=>$_GPC['id'])), 'error');
	}
	else
	{
		message('提交成功！', $this->createWebUrl('counselors'), 'success');
	}
}
if($_GPC['op']=='showdetail')
{
	$id = $_GPC['id'];
	$user = pdo_fetch("select * from". tablename('broke_assistant'). "where id =". $_GPC['id']);
	include $this->template('web/counselors_showdetail');
	exit;
}
if($op=='del')
{
	$code = pdo_fetchcolumn("select code from".tablename('broke_assistant'). "where id =".$_GPC['id']);
	$temp = pdo_delete('broke_assistant', array('id'=>$_GPC['id']));
	$c = pdo_fetchcolumn("select code from".tablename('broke_counselor'). "where weid =". $_W['weid']. " and code ='".$code. "'");
	if(!empty($c))
	{
		pdo_delete('broke_counselor', array('code'=>$c));
	}
	pdo_update('broke_customer', array('cid'=>0), array('cid'=>$_GPC['id']));
	if(empty($temp))
	{
		message('删除失败，请重新删除！', $this->createWebUrl('counselors', array('op'=>'showdetail', 'id'=>$_GPC['mid'])), 'error');
	}
	else
	{
		message('删除成功！', $this->createWebUrl('counselors'), 'success');
	}
}
if($op=='allot')
{
	$op = 'allot';
	if($_GPC['id'] > 0)
	{
		$id = intval($_GPC['id']);
		$update = array( 'cid'=>$id, 'allottime'=>time() );
		$selected = explode(',',trim($_GPC['selected']));
		for($i=0; $i<sizeof($selected);
		$i++)
		{
			$temp = pdo_update('broke_customer', $update, array('id'=>$selected[$i]));
		}
		if(!$temp)
		{
			message('分配失败，请重新分配！', $this->createWebUrl('customer'), 'error');
		}
		else
		{
			message('分配成功！', $this->createWebUrl('customer',array('op'=>'mycustomer','opp'=>'his', 'cid' => $id)), 'success');
		}
	}
	else
	{
		$selected = trim($_GPC['selected']);
	}
}
if($op=='mycustomer')
{
	exit;
}
include $this->template('web/counselors_show');
}
public function doWebCommission()
{
global $_W,$_GPC;
checklogin();
$weid=$_W['weid'];
$op = $_GPC['op'] ? $_GPC['op'] : 'display';
if($op == 'mycommission')
{
	$id = $_GPC['id'];
	$user = pdo_fetch("select * from ".tablename('broke_member'). " where id =". $id);
	$commission = pdo_fetchcolumn("select sum(commission) from". tablename('broke_commission'). " where flag != 2 and mid =". $id. " and weid =". $_W['weid']);
	$comm = $commission - $user['commission'];
	$list = pdo_fetchall("select * from ". tablename('broke_commission'). " where mid =". $id. " and flag = 2");
	include $this->template('web/mycommission_showdetail');
	exit;
}
if($op == 'send')
{
	$mid = $_GPC['mid'];
	if(intval($_GPC['commission']))
	{
		$com = intval($_GPC['commission']);
	}
	else
	{
		message('请输入合法数值！', '', 'error');
	}
	$send = array( 'weid'=>$_W['weid'], 'mid'=>$mid, 'commission'=>$com, 'flag'=>2, 'content'=>trim($_GPC['content']), 'createtime'=>time(), );
	$commission = pdo_fetchcolumn("select commission from ".tablename('broke_member'). " where id =". $mid);
	$comm = array( 'commission'=>$com+$commission );
	$temp = pdo_insert('broke_commission', $send);
	if(empty($temp))
	{
		message('充值失败，请重新充值！', $this->createWebUrl('commission', array('op'=>'mycommission', 'id'=>$mid)), 'error');
	}
	else
	{
		pdo_update('broke_member', $comm, array('id'=>$mid));
		message('充值成功！', $this->createWebUrl('commission', array('op'=>'mycommission', 'id'=>$mid)), 'success');
	}
}
if($op == 'display')
{
	$tjcommission = pdo_fetchcolumn("select sum(commission) from". tablename('broke_commission'). "where weid =". $_W['weid']. ".and flag = 0");
	$tjcommission = !empty($tjcommission)?$tjcommission:0;
	$yycommission = pdo_fetchcolumn("select sum(commission) from". tablename('broke_commission'). "where weid =". $_W['weid']. ".and flag = 1");
	$yycommission = !empty($yycommission)?$yycommission:0;
	$yjcommission = pdo_fetchcolumn("select sum(commission) from". tablename('broke_commission'). "where weid =". $_W['weid']. ".and flag = 2");
	$yjcommission = !empty($yjcommission)?$yjcommission:0;
}
include $this->template('web/commission_show');
}
public function doWebCustomer()
{
global $_W,$_GPC;
checklogin();
$weid=$_W['weid'];
$op= $operation = $_GPC['op']?$_GPC['op']:'display';
if($_GPC['op']=='sort')
{
	if($_GPC['loupan']=='')
	{
		$sort = array( 'realname'=>$_GPC['realname'], 'mobile'=>$_GPC['mobile'] );
		$list = pdo_fetchall("select c.*, m.id as mid from". tablename('broke_customer'). "as c left join".tablename('broke_member'). "as m on c.from_user = m.from_user and c.weid = m.weid where c.weid =". $_W['weid'].".and c.realname like '%". $sort['realname']."%' and c.mobile like '%".$sort['mobile']."%' ORDER BY id DESC");
		$commission = pdo_fetchall('SELECT cid,sum(commission) as commission FROM '.tablename('broke_commission')." WHERE `weid` = :weid group by cid",array(':weid' => $_W['weid']));
		$commissions = array();
		foreach($commission as $k=>$v)
		{
			$commissions[$v['cid']]=$v['commission'];
		}
	}
	else
	{
		$sort = array( 'realname'=>$_GPC['realname'], 'mobile'=>$_GPC['mobile'] );
		$loupan = pdo_fetchall('SELECT id,title FROM '.tablename('broke_loupan')." WHERE `weid` = :weid and `isview` =1 ",array(':weid' => $_W['weid']));
		$loupans=array();
		foreach($loupan as $k=>$v)
		{
			$loupans[$v['title']]=$v['id'];
		}
		$loupan = $loupans[$_GPC['loupan']];
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;
		$total = pdo_fetchcolumn("select count(c.id) from". tablename('broke_customer'). "as c left join".tablename('broke_member'). "as m on c.from_user = m.from_user and c.weid = m.weid where c.weid =". $_W['weid'].".and c.realname like '%". $sort['realname']."%' and c.mobile like '%".$sort['mobile']."%' and c.loupan =".$loupan);
		$pager = pagination($total, $pindex, $psize);
		$list = pdo_fetchall("select c.*, m.id as mid from". tablename('broke_customer'). "as c left join".tablename('broke_member'). "as m on c.from_user = m.from_user and c.weid = m.weid where c.weid =". $_W['weid'].".and c.realname like '%". $sort['realname']."%' and c.mobile like '%".$sort['mobile']."%' and c.loupan =".$loupan." ORDER BY id DESC limit ".($pindex - 1) * $psize . ',' . $psize);
		$commission = pdo_fetchall('SELECT cid,sum(commission) as commission FROM '.tablename('broke_commission')." WHERE `weid` = :weid group by cid",array(':weid' => $_W['weid']));
		$commissions = array();
		foreach($commission as $k=>$v)
		{
			$commissions[$v['cid']]=$v['commission'];
		}
	}
	$total = sizeof($list);
}
else
{
	$pindex = max(1, intval($_GPC['page']));
	$psize = 20;
	$total = pdo_fetchcolumn("select count(c.id) from". tablename('broke_customer'). "as c left join".tablename('broke_member'). "as m on c.from_user = m.from_user and c.weid = m.weid where c.weid =". $_W['weid']);
	$pager = pagination($total, $pindex, $psize);
	$list = pdo_fetchall("select c.*, m.id as mid from". tablename('broke_customer'). "as c left join".tablename('broke_member'). "as m on c.from_user = m.from_user and c.weid = m.weid where c.weid =". $_W['weid']. " ORDER BY id DESC limit ".($pindex - 1) * $psize . ',' . $psize);
	$commission = pdo_fetchall('SELECT cid,sum(commission) as commission FROM '.tablename('broke_commission')." WHERE `weid` = :weid group by cid",array(':weid' => $_W['weid']));
	$commissions = array();
	foreach($commission as $k=>$v)
	{
		$commissions[$v['cid']]=$v['commission'];
	}
}
if($_GPC['op']=='mycustomer')
{
	if($_GPC['opp']=='his')
	{
		$cid = $_GPC['cid'];
		$opp = 'his';
		$info = pdo_fetch("select id, weid, realname from". tablename('broke_assistant'). "where id =". $cid);
		$sort = array( 'realname'=>$_GPC['realname'], 'mobile'=>$_GPC['mobile'] );
		$list = pdo_fetchall("select * from". tablename('broke_customer'). "where cid ='". $cid. "' and weid =". $info['weid']. ".and realname like '%".$sort['realname']."%' and mobile like '%".$sort['mobile']."%'");
		$total = sizeof($list);
		$pager = '';
	}
	else
	{
		$id = $_GPC['id'];
		$info = pdo_fetch("select id, from_user, weid, realname from". tablename('broke_member'). "where id =". $_GPC['id']);
		$sort = array( 'realname'=>$_GPC['realname'], 'mobile'=>$_GPC['mobile'] );
		$list = pdo_fetchall("select * from". tablename('broke_customer'). "where from_user ='". $info['from_user']. "' and weid =". $info['weid']. ".and realname like '%".$sort['realname']."%' and mobile like '%".$sort['mobile']."%'");
		$total = sizeof($list);
		$pager = '';
	}
}
if($op=='cancel')
{
	$cid = pdo_fetchcolumn("select cid from". tablename('broke_customer'). "where id =".$_GPC['id']);
	$cancel = array( 'cid'=>0 );
	$temp = pdo_update('broke_customer', $cancel, array('id'=>$_GPC['id']));
	if(!$temp)
	{
		message('取消失败，请重新取消！', $this->createWebUrl('customer',array('op'=>'mycustomer','opp'=>'his', 'cid' => $cid)), 'error');
	}
	else
	{
		message('取消成功！', $this->createWebUrl('customer',array('op'=>'mycustomer','opp'=>'his', 'cid' => $cid)), 'success');
	}
}
if($_GPC['op']=='status')
{
	$ccid = $_GPC['cid'];
	$loupan = pdo_fetchcolumn("select loupan from". tablename('broke_customer'). "where id =". $_GPC['id']);
	$cid = $_GPC['id'];
	$mid = $_GPC['mid'];
	$cstatus = pdo_fetchall("select status from". tablename('broke_commission'). "where cid =". $cid. ".and mid =".$mid);
	$isupdate = 1;
	foreach($cstatus as $s)
	{
		if($s['status']==$_GPC['status'])
		{
			$isupdate = 0;
		}
	}
	if($isupdate)
	{
		$commission = array( 'weid'=>$_W['weid'], 'mid'=>$mid, 'cid'=>$cid, 'commission'=>intval($_GPC['commission']), 'content'=>$_GPC['content'], 'status'=>$_GPC['status'], 'flag'=>$_GPC['flag'], 'createtime'=>time() );
		$temp = pdo_insert('broke_commission', $commission);
	}
	else
	{
		$commission = array( 'commission'=>intval($_GPC['commission']), 'content'=>$_GPC['content'], 'createtime'=>time() );
		$temp = pdo_update('broke_commission', $commission, array('cid'=>$cid, 'mid'=>$mid, 'status'=>$_GPC['status']));
	}
	$status = array( 'status'=>$_GPC['status'], 'updatetime'=>time() );
	$temp = pdo_update('broke_customer', $status, array('id'=>$_GPC['id']));
	if(!empty($ccid))
	{
		if(empty($temp))
		{
			message('提交失败，请重新提交！', $this->createWebUrl('customer',array('op'=>'showdetail', 'opp'=>'showdetail', 'id' => $cid, 'cid'=>$ccid)), 'error');
		}
		else
		{
			message('提交成功！', $this->createWebUrl('customer',array('op'=>'mycustomer','opp'=>'his', 'cid' => $ccid)), 'success');
		}
	}
	else
	{
		if(empty($temp))
		{
			message('提交失败，请重新提交！', $this->createWebUrl('customer', array('op'=>'showdetail', 'id'=>$_GPC['id'], 'mid'=>$_GPC['mid'])), 'error');
		}
		else
		{
			message('提交成功！', $this->createWebUrl('customer', array('op'=>'mycustomer', 'id'=>$_GPC['mid'])), 'success');
		}
	}
}
$status = $this->ProcessStatus();
if($_GPC['op']=='showdetail')
{
	if($_GPC['opp']=='showdetail')
	{
		$cid = $_GPC['cid'];
		$id = $_GPC['id'];
		$realname = pdo_fetchcolumn("select m.realname from". tablename('broke_customer'). "as c left join".tablename('broke_member')."as m on c.from_user = m.from_user and c.weid = m.weid where c.id =". $id);
		$mid = pdo_fetchcolumn("select m.id from". tablename('broke_customer'). "as c left join".tablename('broke_member')."as m on c.from_user = m.from_user and c.weid = m.weid where c.id =". $id);
		$comm = pdo_fetchcolumn("select sum(commission) as commission from". tablename('broke_commission'). "where weid =". $_W['weid']. " and cid =". $_GPC['id']);
		$content = pdo_fetchall("select content, status from". tablename('broke_commission'). "where cid =". $_GPC['id']);
		$contents = array();
		foreach($content as $k=>$v)
		{
			$contents[$v['status']]=$v['content'];
		}
		$commission = pdo_fetchcolumn("select l.commission from". tablename('broke_loupan'). "as l left join".tablename('broke_customer')."as c on l.id=c.loupan and l.weid = c.weid where c.id =". $_GPC['id']);
		$user = pdo_fetch("select * from". tablename('broke_customer'). "where id =". $_GPC['id']);
	}
	else
	{
		$mid = $_GPC['mid'];
		$id = $_GPC['id'];
		$realname = pdo_fetchcolumn("select realname from". tablename('broke_member'). "where id =". $mid);
		$comm = pdo_fetchcolumn("select sum(commission) as commission from". tablename('broke_commission'). "where cid =". $_GPC['id']);
		$content = pdo_fetchall("select content, status from". tablename('broke_commission'). "where cid =". $_GPC['id']);
		$contents = array();
		foreach($content as $k=>$v)
		{
			$contents[$v['status']]=$v['content'];
		}
		$commission = pdo_fetchcolumn("select l.commission from". tablename('broke_loupan'). "as l left join".tablename('broke_customer')."as c on l.id=c.loupan and l.weid = c.weid where c.id =". $_GPC['id']);
		$user = pdo_fetch("select * from". tablename('broke_customer'). "where id =". $_GPC['id']);
	}
	$loupan = pdo_fetchall('SELECT id,title FROM '.tablename('broke_loupan')." WHERE `weid` = :weid and `isview` =1 ",array(':weid' => $_W['weid']));
	$loupans=array();
	foreach($loupan as $k=>$v)
	{
		$loupans[$v['id']]=$v['title'];
	}
	$list = pdo_fetchall("select * from ". tablename('broke_commission'). "where mid =". $mid. " and cid =".$id. " and weid =".$_W['weid']. " order by status");
	include $this->template('web/customer_showdetail');
	exit;
}
if($_GPC['op']=='del')
{
	if($_GPC['opp']=='delete')
	{
		$cid = pdo_fetchcolumn("select cid from". tablename('broke_customer'). "where id =".$_GPC['id']);
		$temp = pdo_delete('broke_customer', array('id'=>$_GPC['id']));
		if(empty($temp))
		{
			message('删除失败，请重新删除！', $this->createWebUrl('customer',array('op'=>'mycustomer','opp'=>'his', 'cid' => $cid)), 'error');
		}
		else
		{
			message('删除成功！', $this->createWebUrl('customer',array('op'=>'mycustomer','opp'=>'his', 'cid' => $cid)), 'success');
		}
	}
	else
	{
		$temp = pdo_delete('broke_customer', array('id'=>$_GPC['id']));
		if(empty($temp))
		{
			message('删除失败，请重新删除！', $this->createWebUrl('customer', array('op'=>'mycustomer', 'id'=>$_GPC['mid'])), 'error');
		}
		else
		{
			message('删除成功！', $this->createWebUrl('customer', array('op'=>'mycustomer', 'id'=>$_GPC['mid'])), 'success');
		}
	}
}
$loupan = pdo_fetchall('SELECT id,title FROM '.tablename('broke_loupan')." WHERE `weid` = :weid and `isview` =1 ",array(':weid' => $_W['weid']));
$loupans=array();
foreach($loupan as $k=>$v)
{
	$loupans[$v['id']]=$v['title'];
}
include $this->template('web/customer_show');
}
public function doWebIdentity()
{
global $_W,$_GPC;
checklogin();
$weid=$_W['weid'];
$op = $_GPC['op'] ? $_GPC['op'] : 'list';
if($op == 'list') 
{
	$list = pdo_fetchall('SELECT * FROM '.tablename('broke_identity')." WHERE `weid` = :weid ORDER BY listorder DESC",array(':weid' => $_W['weid']));
	if(checksubmit('submit')) 
	{
		foreach ($_GPC['listorder'] as $key => $val) 
		{
			pdo_update('broke_identity', array('listorder' => intval($val)),array('id' => intval($key)));
		}
		message('更新身份排序成功！', $this->createWebUrl('identity', array('op'=>'list')), 'success');
	}
	include $this->template('web/identity_list');
}
if($op == 'post') 
{
	$id = intval($_GPC['id']);
	if($id > 0) 
	{
		$theone = pdo_fetch('SELECT * FROM '.tablename('broke_identity')." WHERE  weid = :weid  AND id = :id" , array(':weid' => $_W['weid'],':id' => $id));
	}
	else 
	{
		$theone = array('status' => 1,'listorder' => 0, 'iscompany'=> 0);
	}
	if (checksubmit('submit')) 
	{
		$identity_name = trim($_GPC['identity_name']) ? trim($_GPC['identity_name']) : message('请填写身份名称！');
		$listorder = intval($_GPC['listorder']);
		$status = intval($_GPC['status']);
		$iscompany = intval($_GPC['iscompany']);
		$insert = array( 'weid' => $_W['weid'], 'identity_name' => $identity_name, 'iscompany' => $iscompany, 'listorder' => $listorder, 'status' => $status, 'createtime' => TIMESTAMP );
		if(empty($id)) 
		{
			pdo_insert('broke_identity', $insert);
			!pdo_insertid() ? message('保存身份数据失败, 请稍后重试.','error') : '';
		}
		else 
		{
			if(pdo_update('broke_identity', $insert,array('id' => $id)) === false)
			{
				message('更新身份数据失败, 请稍后重试.','error');
			}
		}
		message('更新身份数据成功！', $this->createWebUrl('identity', array('op'=>'list')), 'success');
	}
	include $this->template('web/identity_post');
}
if($op == 'del') 
{
	$temp = pdo_delete('broke_identity',array('id'=>$_GPC['id']));
	if(empty($temp))
	{
		message('删除数据失败！', $this->createWebUrl('identity', array('op'=>'list')), 'error');
	}
	else
	{
		message('删除数据成功！', $this->createWebUrl('identity', array('op'=>'list')), 'success');
	}
}
}
public function doWebLoupan()
{
global $_W,$_GPC;
checklogin();
$weid=$_W['weid'];
$op = $_GPC['op'] ? $_GPC['op'] : 'display';
if ($op == 'create') 
{
	$id = intval($_GPC['id']);
	if (!empty($id)) 
	{
		$item = pdo_fetch("SELECT * FROM ".tablename('broke_loupan')." WHERE id = :id" , array(':id' => $id));
		if (empty($item)) 
		{
			message('抱歉，产品不存在或是已经删除！', '', 'error');
		}
	}
	if (checksubmit('fileupload-delete')) 
	{
		$data = array();
		$data['thumb'] = '';
		pdo_update('broke_loupan', $data, array('id' => $id));
		message('封面删除成功！', '', 'success');
	}
	if (checksubmit('submit')) 
	{
		if (empty($_GPC['title'])) 
		{
			message('请输入产品名称！');
		}
		$data = array( 'weid' => $_W['weid'], 'title' => $_GPC['title'], 'music' => $_GPC['music'], 'open' => $_GPC['open'], 'ostyle' => $_GPC['ostyle'], 'icon' => $_GPC['icon'], 'share' => $_GPC['share'], 'content' => $_GPC['content'], 'tel' => $_GPC['tel'], 'province' => $_GPC['resideprovince'], 'city' => $_GPC['residecity'], 'dist' => $_GPC['residedist'], 'addr' => $_GPC['addr'], 'lng' => $_GPC['lng'], 'lat' => $_GPC['lat'], 'jw_addr' => $_GPC['jw_addr'], 'commission' => $_GPC['commission'], 'displayorder' => intval($_GPC['displayorder']), 'isloop' => intval($_GPC['isloop']), 'isview' => intval($_GPC['isview']), 'type' => intval($_GPC['type']), 'createtime' => TIMESTAMP, );
		if ($_GPC['mset'][0]) 
		{
			$data['mauto'] = 1;
		}
		else
		{
			$data['mauto'] = 0;
		}
		if ($_GPC['mset'][1]) 
		{
			$data['mloop'] = 1;
		}
		else
		{
			$data['mloop'] = 0;
		}
		if (!empty($_FILES['thumb']['tmp_name'])) 
		{
			file_delete($_GPC['thumb_old']);
			$upload = file_upload($_FILES['thumb']);
			if (is_error($upload)) 
			{
				message($upload['message'], '', 'error');
			}
			$data['thumb'] = $upload['path'];
		}
		if (empty($id)) 
		{
			pdo_insert('broke_loupan', $data);
		}
		else 
		{
			unset($data['createtime']);
			pdo_update('broke_loupan', $data, array('id' => $id));
		}
		message('产品更新成功！', $this->createWebUrl('loupan', array('op' => 'display')), 'success');
	}
	include $this->template('web/loupan');
}
elseif ($op == 'display') 
{
	$pindex = max(1, intval($_GPC['page']));
	$psize = 20;
	$condition = '';
	if (!empty($_GPC['keyword'])) 
	{
		$condition .= " AND title LIKE '%{$_GPC['keyword']}
	%'";
}
$list = pdo_fetchall("SELECT * FROM ".tablename('broke_loupan')." WHERE weid = '{$_W['weid']}
' $condition ORDER BY displayorder DESC, id DESC LIMIT ".($pindex - 1) * $psize.','.$psize);
$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('broke_loupan') . " WHERE weid = '{$_W['weid']}
' $condition");
$pager = pagination($total, $pindex, $psize);
if (!empty($list)) 
{
foreach ($list as &$row) 
{
$row['total'] = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('broke_photo')." WHERE lpid = :lpid", array(':lpid' => $row['id']));
}
}
include $this->template('web/loupan');
}
elseif ($op == 'photo') 
{
$id = intval($_GPC['lpid']);
$loupan = pdo_fetch("SELECT id, type FROM ".tablename('broke_loupan')." WHERE id = :id", array(':id' => $id));
if (empty($loupan)) 
{
message('产品不存在或是已经被删除！');
}
if (checksubmit('submit')) 
{
if (!empty($_GPC['item'])) 
{
if (!empty($_GPC['id'])) 
{
	$data = array( 'weid' => $_W['weid'], 'lpid' => intval($_GPC['lpid']), 'photoid' => intval($_GPC['photoid']), 'type' => $_GPC['type'], 'item' => $_GPC['item'], 'url' => $_GPC['url'], 'x' => $_GPC['x'], 'y' => $_GPC['y'], 'animation' => $_GPC['animation'], );
	pdo_update('broke_item', $data, array('id' => $_GPC['id']));
}
else
{
	$data = array( 'weid' => $_W['weid'], 'lpid' => intval($_GPC['lpid']), 'photoid' => intval($_GPC['photoid']), 'type' => $_GPC['type'], 'item' => $_GPC['item'], 'url' => $_GPC['url'], 'x' => $_GPC['x'], 'y' => $_GPC['y'], 'animation' => $_GPC['animation'], );
	pdo_insert('broke_item', $data);
}
}
if (!empty($_GPC['attachment-new'])) 
{
foreach ($_GPC['attachment-new'] as $index => $row) 
{
	if (empty($row)) 
	{
		continue;
	}
	$data = array( 'weid' => $_W['weid'], 'lpid' => intval($_GPC['lpid']), 'title' => $_GPC['title-new'][$index], 'url' => $_GPC['url-new'][$index], 'attachment' => $_GPC['attachment-new'][$index], 'displayorder' => $_GPC['displayorder-new'][$index], );
	pdo_insert('broke_photo', $data);
}
}
if (!empty($_GPC['attachment'])) 
{
foreach ($_GPC['attachment'] as $index => $row) 
{
	if (empty($row)) 
	{
		continue;
	}
	$data = array( 'weid' => $_W['weid'], 'lpid' => intval($_GPC['lpid']), 'title' => $_GPC['title'][$index], 'url' => $_GPC['url'][$index], 'attachment' => $_GPC['attachment'][$index], 'displayorder' => $_GPC['displayorder'][$index], );
	pdo_update('broke_photo', $data, array('id' => $index));
}
}
message('产品更新成功！', $this->createWebUrl('loupan', array('op' => 'photo', 'lpid' => $loupan['id'])));
}
$photos = pdo_fetchall("SELECT * FROM ".tablename('broke_photo')." WHERE lpid = :lpid ORDER BY displayorder DESC", array(':lpid' => $loupan['id']));
foreach ($photos as &$photo1) 
{
$photo1['items'] = pdo_fetchall("SELECT * FROM ".tablename('broke_item')." WHERE photoid = :photoid", array(':photoid' => $photo1['id']));
}
include $this->template('web/loupan');
}
elseif ($op == 'delete') 
{
$type = $_GPC['type'];
$id = intval($_GPC['id']);
if ($type == 'photo') 
{
if (!empty($id)) 
{
$item = pdo_fetch("SELECT * FROM ".tablename('broke_photo')." WHERE id = :id", array(':id' => $id));
if (empty($item)) 
{
	message('图片不存在或是已经被删除！');
}
pdo_delete('broke_photo', array('id' => $item['id']));
}
else 
{
$item['attachment'] = $_GPC['attachment'];
}
file_delete($item['attachment']);
}
elseif ($type == 'loupan') 
{
$loupan = pdo_fetch("SELECT id, thumb FROM ".tablename('broke_loupan')." WHERE id = :id", array(':id' => $id));
if (empty($loupan)) 
{
message('产品不存在或是已经被删除！');
}
$photos = pdo_fetchall("SELECT id, attachment FROM ".tablename('broke_photo')." WHERE lpid = :lpid", array(':lpid' => $id));
if (!empty($photos)) 
{
foreach ($photos as $row) 
{
	file_delete($row['attachment']);
}
}
pdo_delete('broke_loupan', array('id' => $id));
pdo_delete('broke_photo', array('lpid' => $id));
}
message('删除成功！', referer(), 'success');
}
elseif ($op == 'cover') 
{
$id = intval($_GPC['lpid']);
$attachment = $_GPC['thumb'];
if (empty($attachment)) 
{
message('抱歉，参数错误，请重试！', '', 'error');
}
$item = pdo_fetch("SELECT * FROM ".tablename('broke_loupan')." WHERE id = :id" , array(':id' => $id));
if (empty($item)) 
{
message('抱歉，产品不存在或是已经删除！', '', 'error');
}
pdo_update('loupan', array('thumb' => $attachment), array('id' => $id));
message('设置封面成功！', '', 'success');
}
}
public function doWebItem() 
{
global $_W,$_GPC;
checklogin();
$lpid = intval($_GPC['lpid']);
$photoid = intval($_GPC['photoid']);
$id = intval($_GPC['id']);
$photo = pdo_fetch("SELECT * FROM ".tablename('broke_photo')." WHERE id = :id", array(':id' => $photoid));
if (empty($photo)) 
{
message('产品不存在或是已经被删除！');
}
if (!empty($id)) 
{
$item = pdo_fetch("SELECT * FROM ".tablename('broke_item')." WHERE id = :id", array(':id' => $id));
}
include $this->template('web/item');
}
public function doWebQuery() 
{
global $_W,$_GPC;
checklogin();
$kwd = $_GPC['keyword'];
$sql = 'SELECT * FROM ' . tablename('broke_loupan') . ' WHERE `weid`=:weid AND `title` LIKE :title';
$params = array();
$params[':weid'] = $_W['weid'];
$params[':title'] = "%{$kwd}
%";
$ds = pdo_fetchall($sql, $params);
foreach($ds as &$row) 
{
$r = array();
$r['id'] = $row['id'];
$r['title'] = $row['title'];
$r['description'] = $row['content'];
$r['thumb'] = $row['thumb'];
$row['entry'] = $r;
}
include $this->template('web/query');
}
public function doWebMember()
{
global $_W,$_GPC;
checklogin();
$weid=$_W['weid'];
if($_GPC['op']=='showdetail')
{
$user = pdo_fetch("select * from". tablename('broke_member'). "where id =". $_GPC['id']);
$identity = pdo_fetchall('SELECT id,identity_name FROM '.tablename('broke_identity')." WHERE `weid` = :weid and `status` =:status ",array(':weid' => $_W['weid'], ':status'=>1));
$identitys=array();
foreach($identity as $k=>$v)
{
$identitys[$v['id']]=$v['identity_name'];
}
$info = pdo_fetch("select id, from_user, weid from". tablename('broke_member'). "where id =". $_GPC['id']);
$count = pdo_fetchcolumn("select count(id) from". tablename('broke_customer'). "where from_user ='". $info['from_user']. "' and weid =". $info['weid']);
include $this->template('web/member_showdetail');
exit;
}
if($_GPC['op']=='status')
{
$status = array( 'status'=>$_GPC['status'] );
$temp = pdo_update('broke_member', $status, array('id'=>$_GPC['id']));
if(empty($temp))
{
message('设置用户权限失败，请重新设置！', $this->createWebUrl('member', array('op'=>'showdetail', 'id'=>$_GPC['id'])), 'error');
}
else
{
message('设置用户权限成功！', $this->createWebUrl('member'), 'success');
}
}
if($_GPC['op']=='del')
{
$temp = pdo_delete('broke_member', array('id'=>$_GPC['id']));
if(empty($temp))
{
message('删除失败，请重新删除！', $this->createWebUrl('member'), 'error');
}
else
{
message('删除成功！', $this->createWebUrl('member'), 'success');
}
}
if($_GPC['op']=='sort')
{
$sort = array( 'realname'=>$_GPC['realname'], 'mobile'=>$_GPC['mobile'] );
$list = pdo_fetchall("select * from". tablename('broke_member')."where weid =".$_W['weid'].".and realname like '%".$sort['realname']. "%' and mobile like '%".$sort['mobile']. "%' ORDER BY id DESC");
$total = sizeof($list);
$commission = pdo_fetchall('SELECT mid, sum(commission) as commission FROM '.tablename('broke_commission')." WHERE flag = 2 and `weid` = :weid group by mid",array(':weid' => $_W['weid']));
$commissions = array();
foreach($commission as $k=>$v)
{
$commissions[$v['mid']]=$v['commission'];
}
}
else
{
$pindex = max(1, intval($_GPC['page']));
$psize = 20;
$total = pdo_fetchcolumn("select count(id) from". tablename('broke_member'). "where weid =".$_W['weid']);
$pager = pagination($total, $pindex, $psize);
$list = pdo_fetchall("select * from". tablename('broke_member'). "where weid =".$_W['weid']." ORDER BY id DESC limit ".($pindex - 1) * $psize . ',' . $psize);
$commission = pdo_fetchall('SELECT mid, sum(commission) as commission FROM '.tablename('broke_commission')." WHERE flag = 2 and `weid` = :weid group by mid",array(':weid' => $_W['weid']));
$commissions = array();
foreach($commission as $k=>$v)
{
$commissions[$v['mid']]=$v['commission'];
}
}
$identity = pdo_fetchall('SELECT id,identity_name FROM '.tablename('broke_identity')." WHERE `weid` = :weid and `status` =:status ",array(':weid' => $_W['weid'], ':status'=>1));
$identitys=array();
foreach($identity as $k=>$v)
{
$identitys[$v['id']]=$v['identity_name'];
}
include $this->template('web/member_show');
}
public function doWebRule()
{
global $_W,$_GPC;
checklogin();
$weid=$_W['weid'];
$id = intval($_GPC['id']);
$theone = pdo_fetch('SELECT * FROM '.tablename('broke_rule')." WHERE  weid = :weid" , array(':weid' => $_W['weid']));
if(empty($theone)) 
{
$theone['status']='到访,认筹,认购,签约,回款';
}
if (checksubmit('submit')) 
{
$insert = array( 'weid' => $_W['weid'], 'rule' => htmlspecialchars_decode($_GPC['rule']), 'terms' => htmlspecialchars_decode($_GPC['terms']), 'status' => trim($_GPC['status']), 'gzurl' => trim($_GPC['gzurl']), 'createtime' => TIMESTAMP );
if(empty($id)) 
{
if(empty($insert['status'])) 
{
$insert['status']='到访,认筹,认购,签约,回款';
}
pdo_insert('broke_rule', $insert);
!pdo_insertid() ? message('保存失败, 请稍后重试.','error') : '';
}
else 
{
if(pdo_update('broke_rule', $insert,array('id' => $id)) === false)
{
message('更新失败, 请稍后重试.','error');
}
}
message('更新成功！', $this->createWebUrl('rule'), 'success');
}
include $this->template('web/rule');
}
public function doWebStat()
{
global $_W,$_GPC;
checklogin();
$weid=$_W['weid'];
$op= $operation = $_GPC['op']?$_GPC['op']:'display';
$year = !empty($_GPC['year'])?$_GPC['year']:date('Y');
$month = !empty($_GPC['month'])?$_GPC['month']:date('m');
$selecttime = $year.'-'.$month.'-01 00:00:00';
$starttime = strtotime($selecttime);
$temptime = $selecttime;
$endtime = strtotime(date('Y-m-d 23:59:59', strtotime("$temptime +1 month -1 day")));
$loupan = pdo_fetchall('SELECT id,title FROM '.tablename('broke_loupan')." WHERE `weid` = :weid and `isview` =1 ",array(':weid' => $_W['weid']));
$loupans=array();
foreach($loupan as $k=>$v)
{
$loupans[$v['title']]=$v['id'];
}
$lid = $loupans[$_GPC['loupan']];
$condition = array( 'op'=>$op, 'year'=>$year, 'month'=>$month, 'loupan'=>$_GPC['loupan'] );
$pp=$_GPC['pp'];
if($op=='customer')
{
$logtype = 1;
if($pp=='xml')
{
$xml = simplexml_load_file('./source/modules/broke/style/graph/customer.xml');
if(empty($_GPC['loupan']))
{
$xml['subCaption'] = $year.'-'.$month;
$logs = pdo_fetchall("select count(id) as num, status from".tablename('broke_customer')."where flag = 0 and weid =".$_W['weid'].".and createtime >".$starttime.".and createtime <".$endtime.".group by status");
}
else
{
$xml['subCaption'] = $year.'-'.$month.$_GPC['loupan'];
$logs = pdo_fetchall("select count(id) as num, status from".tablename('broke_customer')."where flag = 0 and loupan =".$lid.".and weid =".$_W['weid'].".and createtime >".$starttime.".and createtime <".$endtime.".group by status");
}
foreach($logs as $key=>$log)
{
$aa[$log['status']] = $log['num'];
}
$k = 0;
foreach($xml as $a)
{
if(!empty($aa[$k]))
{
$a['value'] = $aa[$k];
}
else
{
$a['value'] = 0;
}
$k++;
}
header('Content-Type: text/xml');
echo $xml->asXML();
exit;
}
}
if($op=='yuyue')
{
$logtype = 2;
if($pp=='xml')
{
$xml = simplexml_load_file('./source/modules/broke/style/graph/yuyue.xml');
if(empty($_GPC['loupan']))
{
$xml['subCaption'] = $year.'-'.$month;
$logs = pdo_fetchall("select count(id) as num, status from".tablename('broke_customer')."where flag = 1 and weid =".$_W['weid'].".and createtime >".$starttime.".and createtime <".$endtime.".group by status");
}
else
{
$xml['subCaption'] = $year.'-'.$month.$_GPC['loupan'];
$logs = pdo_fetchall("select count(id) as num, status from".tablename('broke_customer')."where flag = 1 and loupan =".$lid.".and weid =".$_W['weid'].".and createtime >".$starttime.".and createtime <".$endtime.".group by status");
}
foreach($logs as $key=>$log)
{
$aa[$log['status']] = $log['num'];
}
$k = 0;
foreach($xml as $a)
{
if(!empty($aa[$k]))
{
$a['value'] = $aa[$k];
}
else
{
$a['value'] = 0;
}
$k++;
}
header('Content-Type: text/xml');
echo $xml->asXML();
exit;
}
}
if($op=='display')
{
$logtype = 3;
if($pp=='xml')
{
$xml = simplexml_load_file('./source/modules/broke/style/graph/log.xml');
if(empty($_GPC['loupan']))
{
$xml['caption'] = $year.'-'.$month.' 游客浏览产品总记录曲线';
$logs = pdo_fetchall("select count(id) as num,createtime1, count(distinct from_user) as num1 from".tablename('broke_log')."where weid =".$_W['weid'].".and createtime >".$starttime.".and createtime <".$endtime.".group by createtime1");
}
else
{
$xml['caption'] = $year.'-'.$month.' 游客浏览'.$_GPC['loupan'].'记录曲线';
$logs = pdo_fetchall("select count(id) as num,createtime1, count(distinct from_user) as num1 from".tablename('broke_log')."where loupan =".$lid.".and weid =".$_W['weid'].".and createtime >".$starttime.".and createtime <".$endtime.".group by createtime1");
}
$dates = array();
$n = 0;
for($m=1; $m<=31; $m++)
{
if($m<10)
{
$n = '0'.$m;
}
else
{
$n = $m;
}
$dates[$n] = $year.'-'.$month.'-'.$n;
}
foreach($logs as $log)
{
$key = substr($log['createtime1'], -2);
if($log['createtime1']==$dates[$key])
{
$aa[$key] = $log['num'];
$bb[$key] = $log['num1'];
}
else
{
$aa[$key] = 0;
$bb[$key] = 0;
}
}
$i = 1;
$k = 0;
foreach($xml->dataset[0]->set as $a)
{
if($i<10)
{
$k = '0'.$i;
}
else
{
$k = $i;
}
if(!empty($aa[$k]))
{
$a['value'] = $aa[$k];
}
else
{
$a['value'] = 0;
}
$i++;
}
$j = 1;
$l = 0;
foreach($xml->dataset[1]->set as $a)
{
if($j<10)
{
$l = '0'.$j;
}
else
{
$l = $j;
}
if(!empty($bb[$l]))
{
$a['value'] = $bb[$l];
}
else
{
$a['value'] = 0;
}
$j++;
}
header('Content-Type: text/xml');
echo $xml->asXML();
exit;
}
}
if($op=='logloupan')
{
$logtype = 4;
if($pp=='xml')
{
$xml = simplexml_load_file('./source/modules/broke/style/graph/logloupan.xml');
if(empty($_GPC['loupan']))
{
$xml['caption'] = $year.'-'.$month.' 转发产品总记录曲线';
$xml['subcaption'] = '';
$logs = pdo_fetchall("select count(id) as num,createtime1 from".tablename('broke_logloupan')."where weid =".$_W['weid'].".and createtime >".$starttime.".and createtime <".$endtime.".group by createtime1");
}
else
{
$xml['caption'] = $year.'-'.$month.' 转发'.$_GPC['loupan'].'记录曲线';
$xml['subcaption'] = '';
$logs = pdo_fetchall("select count(id) as num,createtime1 from".tablename('broke_logloupan')."where lid =".$lid.".and weid =".$_W['weid'].".and createtime >".$starttime.".and createtime <".$endtime.".group by createtime1");
}
$dates = array();
$n = 0;
for($m=1; $m<=31; $m++)
{
if($m<10)
{
$n = '0'.$m;
}
else
{
$n = $m;
}
$dates[$n] = $year.'-'.$month.'-'.$n;
}
foreach($logs as $log)
{
$key = substr($log['createtime1'], -2);
if($log['createtime1']==$dates[$key])
{
$aa[$key] = $log['num'];
}
else
{
$aa[$key] = 0;
}
}
$i = 1;
$k = 0;
foreach($xml as $a)
{
if($i<10)
{
$k = '0'.$i;
}
else
{
$k = $i;
}
if(!empty($aa[$k]))
{
$a['value'] = $aa[$k];
}
else
{
$a['value'] = 0;
}
$i++;
}
header('Content-Type: text/xml');
echo $xml->asXML();
exit;
}
}
include $this->template('web/stat');
}
public function doMobileUserinfo() 
{
global $_GPC,$_W;
$weid = $_W['weid'];
if ($_GPC['code']=="authdeny")
{
$url = $_W['siteroot'].$this->createMobileUrl('index', array());
header("location:$url");
exit('authdeny');
}
if (isset($_GPC['code']))
{
$appid = $_W['account']['key'];
$secret = $_W['account']['secret'];
$serverapp = $_W['account']['level'];
if ($serverapp!=2) 
{
$cfg = $this->module['config'];
$appid = $cfg['appid'];
$secret = $cfg['secret'];
if(empty($appid) || empty($secret))
{
return ;
}
}
$state = $_GPC['state'];
$rid = $_GPC['rid'];
$code = $_GPC['code'];
$oauth2_code = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$appid."&secret=".$secret."&code=".$code."&grant_type=authorization_code";
$content = ihttp_get($oauth2_code);
$token = @json_decode($content['content'], true);
if(empty($token) || !is_array($token) || empty($token['access_token']) || empty($token['openid'])) 
{
echo '<h1>获取微信公众号授权'.$code.'失败[无法取得token以及openid], 请稍后重试！ 公众平台返回原始数据为: <br />' . $content['meta'].'<h1>';
exit;
}
$from_user = $token['openid'];
$profile = fans_search($from_user, array('follow'));
if ($profile['follow']==1)
{
$state = 1;
}
else
{
$url = $_W['siteroot'].$this->createMobileUrl('userinfo', array());
$oauth2_code = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$appid."&redirect_uri=".urlencode($url)."&response_type=code&scope=snsapi_userinfo&state=0#wechat_redirect";
header("location:$oauth2_code");
}
if ($state==1)
{
$oauth2_url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$appid."&secret=".$secret."";
$content = ihttp_get($oauth2_url);
$token_all = @json_decode($content['content'], true);
if(empty($token_all) || !is_array($token_all) || empty($token_all['access_token'])) 
{
echo '<h1>获取微信公众号授权失败[无法取得access_token], 请稍后重试！ 公众平台返回原始数据为: <br />' . $content['meta'].'<h1>';
exit;
}
$access_token = $token_all['access_token'];
$oauth2_url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=".$access_token."&openid=".$from_user."&lang=zh_CN";
}
else
{
$access_token = $token['access_token'];
$oauth2_url = "https://api.weixin.qq.com/sns/userinfo?access_token=".$access_token."&openid=".$from_user."&lang=zh_CN";
}
$content = ihttp_get($oauth2_url);
$info = @json_decode($content['content'], true);
if(empty($info) || !is_array($info) || empty($info['openid']) || empty($info['nickname']) ) 
{
echo '<h1>获取微信公众号授权失败[无法取得info], 请稍后重试！<h1>';
exit;
}
if (!empty($info["headimgurl"])) 
{
$info['avatar']='avatar/'.$info["openid"].'.jpg';
}
else
{
$info['headimgurl']='avatar_11.jpg';
}
if ($serverapp == 2) 
{
$row = array( 'weid' => $_W['weid'], 'nickname'=>$info["nickname"], 'realname'=>$info["nickname"], 'gender' => $info['sex'], 'from_user' => $info['openid'] );
if(!empty($info["country"]))
{
$row['country']=$info["country"];
}
if(!empty($info["province"]))
{
$row['province']=$info["province"];
}
if(!empty($info["city"]))
{
$row['city']=$info["city"];
}
fans_update($_W['fans']['from_user'], $row);
pdo_update('fans', array('avatar'=>$info["headimgurl"]), array('from_user' => $_W['fans']['from_user']));
}
if($serverapp != 2 && !(empty($_W['fans']['from_user']))) 
{
$row = array( 'nickname'=> $info["nickname"], 'realname'=> $info["nickname"], 'gender' => $info['sex'] );
if(!empty($info["country"]))
{
$row['country']=$info["country"];
}
if(!empty($info["province"]))
{
$row['province']=$info["province"];
}
if(!empty($info["city"]))
{
$row['city']=$info["city"];
}
fans_update($_W['fans']['from_user'], $row);
pdo_update('fans', array('avatar'=>$info["headimgurl"]), array('from_user' => $_W['fans']['from_user']));
}
$oauth_openid="jjrops".$_W['weid'];
setcookie($oauth_openid, $info['openid'], time()+3600*240);
$url=$this->createMobileUrl('index');
header("location:$url");
exit;
}
else
{
echo '<h1>网页授权域名设置出错!</h1>';
exit;
}
}
private function CheckCookie() 
{
global $_W;
$oauth_openid="jjrops".$_W['weid'];
if (empty($_COOKIE[$oauth_openid])) 
{
$appid = $_W['account']['key'];
$secret = $_W['account']['secret'];
$serverapp = $_W['account']['level'];
if ($serverapp!=2) 
{
$cfg = $this->module['config'];
$appid = $cfg['appid'];
$secret = $cfg['secret'];
if(empty($appid) || empty($secret))
{
return ;
}
}
$url = $_W['siteroot'].$this->createMobileUrl('userinfo', array());
$oauth2_code = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$appid."&redirect_uri=".urlencode($url)."&response_type=code&scope=snsapi_userinfo&state=0#wechat_redirect";
header("location:$oauth2_code");
exit;
}
}
private function ProcessStatus() 
{
global $_W;
$rule = pdo_fetchcolumn('SELECT status FROM '.tablename('broke_rule')." WHERE `weid` = :weid ",array(':weid' => $_W['weid']));
$status = array( '0'=>'推荐', '1'=>'跟进', '2'=>'无意向', );
$b=explode(",",$rule);
$status=array_merge($status,$b);
return $status;
}
public function doWebUploadMusic() 
{
global $_W;
checklogin();
if (empty($_FILES['imgFile']['name'])) 
{
$result['message'] = '请选择要上传的音乐！';
exit(json_encode($result));
}
if ($_FILES['imgFile']['error'] != 0) 
{
$result['message'] = '上传失败，请重试！';
exit(json_encode($result));
}
if ($file = $this->fileUpload($_FILES['imgFile'], 'music')) 
{
if (!$file['success']) 
{
exit(json_encode($file));
}
$result['url'] = $_W['config']['upload']['attachdir'] . $file['path'];
$result['error'] = 0;
$result['filename'] = $file['path'];
exit(json_encode($result));
}
}
private function fileUpload($file, $type) 
{
global $_W;
set_time_limit(0);
$_W['uploadsetting'] = array();
$_W['uploadsetting']['music']['folder'] = 'music';
$_W['uploadsetting']['music']['extentions'] = array('mp3', 'wma', 'wav', 'amr');
$_W['uploadsetting']['music']['limit'] = 50000;
$result = array();
$upload = file_upload($file, 'music');
if (is_error($upload)) 
{
message($upload['message'], '', 'ajax');
}
$result['url'] = $upload['url'];
$result['error'] = 0;
$result['filename'] = $upload['path'];
return $result;
}
}
function pagination1($tcount, $pindex, $psize = 15, $url = '', $context = array('before' => 5, 'after' => 4, 'ajaxcallback' => '')) 
{
global $_W;
$pdata = array( 'tcount' => 0, 'tpage' => 0, 'cindex' => 0, 'findex' => 0, 'pindex' => 0, 'nindex' => 0, 'lindex' => 0, 'options' => '' );
if($context['ajaxcallback']) 
{
$context['isajax'] = true;
}
$pdata['tcount'] = $tcount;
$pdata['tpage'] = ceil($tcount / $psize);
if($pdata['tpage'] <= 1) 
{
return '';
}
$cindex = $pindex;
$cindex = min($cindex, $pdata['tpage']);
$cindex = max($cindex, 1);
$pdata['cindex'] = $cindex;
$pdata['findex'] = 1;
$pdata['pindex'] = $cindex > 1 ? $cindex - 1 : 1;
$pdata['nindex'] = $cindex < $pdata['tpage'] ? $cindex + 1 : $pdata['tpage'];
$pdata['lindex'] = $pdata['tpage'];
if($context['isajax']) 
{
if(!$url) 
{
$url = $_W['script_name'] . '?' . http_build_query($_GET);
}
$pdata['faa'] = 'href="javascript:;" onclick="p(\'' . $_W['script_name'] . $url . '\', \'' . $pdata['findex'] . '\', ' . $context['ajaxcallback'] . ')"';
$pdata['paa'] = 'href="javascript:;" onclick="p(\'' . $_W['script_name'] . $url . '\', \'' . $pdata['pindex'] . '\', ' . $context['ajaxcallback'] . ')"';
$pdata['naa'] = 'href="javascript:;" onclick="p(\'' . $_W['script_name'] . $url . '\', \'' . $pdata['nindex'] . '\', ' . $context['ajaxcallback'] . ')"';
$pdata['laa'] = 'href="javascript:;" onclick="p(\'' . $_W['script_name'] . $url . '\', \'' . $pdata['lindex'] . '\', ' . $context['ajaxcallback'] . ')"';
}
else 
{
if($url) 
{
$pdata['faa'] = 'href="?' . str_replace('*', $pdata['findex'], $url) . '"';
$pdata['paa'] = 'href="?' . str_replace('*', $pdata['pindex'], $url) . '"';
$pdata['naa'] = 'href="?' . str_replace('*', $pdata['nindex'], $url) . '"';
$pdata['laa'] = 'href="?' . str_replace('*', $pdata['lindex'], $url) . '"';
}
else 
{
$_GET['page'] = $pdata['findex'];
$pdata['faa'] = 'href="' . $_W['script_name'] . '?' . http_build_query($_GET) . '"';
$_GET['page'] = $pdata['pindex'];
$pdata['paa'] = 'href="' . $_W['script_name'] . '?' . http_build_query($_GET) . '"';
$_GET['page'] = $pdata['nindex'];
$pdata['naa'] = 'href="' . $_W['script_name'] . '?' . http_build_query($_GET) . '"';
$_GET['page'] = $pdata['lindex'];
$pdata['laa'] = 'href="' . $_W['script_name'] . '?' . http_build_query($_GET) . '"';
}
}
$html = '<div class="pagination pagination-centered"><ul>';
if($pdata['cindex'] > 1) 
{
$html .= "<li><a {$pdata['faa']}
class=\"pager-nav\">首页</a></li>";
$html .= "<li><a {$pdata['paa']}
class=\"pager-nav\">&laquo;上一页</a></li>";
}
if(!$context['before'] && $context['before'] != 0) 
{
$context['before'] = 5;
}
if(!$context['after'] && $context['after'] != 0) 
{
$context['after'] = 4;
}
if($context['after'] != 0 && $context['before'] != 0) 
{
$range = array();
$range['start'] = max(1, $pdata['cindex'] - $context['before']);
$range['end'] = min($pdata['tpage'], $pdata['cindex'] + $context['after']);
if ($range['end'] - $range['start'] < $context['before'] + $context['after']) 
{
$range['end'] = min($pdata['tpage'], $range['start'] + $context['before'] + $context['after']);
$range['start'] = max(1, $range['end'] - $context['before'] - $context['after']);
}
for ($i = $range['start']; $i <= $range['end']; $i++) 
{
if($context['isajax']) 
{
$aa = 'href="javascript:;" onclick="p(\'' . $_W['script_name'] . $url . '\', \'' . $i . '\', ' . $context['ajaxcallback'] . ')"';
}
else 
{
if($url) 
{
$aa = 'href="?' . str_replace('*', $i, $url) . '"';
}
else 
{
$_GET['page'] = $i;
$aa = 'href="?' . http_build_query($_GET) . '"';
}
}
}
}
if($pdata['cindex'] < $pdata['tpage']) 
{
$html .= "<li><a {$pdata['naa']}
class=\"pager-nav\">下一页&raquo;</a></li>";
$html .= "<li><a {$pdata['laa']}
class=\"pager-nav\">尾页</a></li>";
}
$html .= '</ul></div>';
return $html;
}
?>