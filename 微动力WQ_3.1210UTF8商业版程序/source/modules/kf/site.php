<?php
/**
 * 会员注册
 *
 * @author 19.3CM
 * @QQ 81324093
 * 注：本模块同步微信资料暂时只支持高级接口权限
 */
defined('IN_IA') or exit('Access Denied');
include_once IA_ROOT . '/source/modules/19.3cm.php';
class kfModuleSite extends WeModuleSite {
	public $name = 'kfModule';
	public $title = '客服交流';
	public $ability = '';
	public $tablename = 'kf';
	//此为内部函数调用需要用到涵数，无需要改为WEB
	public function doUserinfo($uid,$type) {//用户信息查询
		global $_GPC, $_W;
		$fromuser =empty($uid)? $_GPC['from_user']:$uid;
		
		if (empty($fromuser)) {
			exit('非法参数');
		}
		if(preg_match("/^[0-9]+$/",$fromuser)&&$type<>3)
		{
			$where="fakeid ='".$fromuser."'";
			$user = pdo_fetch("SELECT * FROM ".tablename('wxuser')." WHERE $where  LIMIT 1");
		}
		elseif(preg_match("/^[0-9]+$/",$fromuser)&&$type==3){
			$where="id ='".$fromuser."'";
			$user = pdo_fetch("SELECT * FROM ".tablename('fans')." WHERE $where  LIMIT 1");
			}
		elseif(!preg_match("/^[0-9]+$/",$fromuser)&&$type==3){
			$where="from_user ='".$fromuser."'";
			$user = pdo_fetch("SELECT * FROM ".tablename('fans')." WHERE $where  LIMIT 1");
			}	
		else{
			$where="from_user ='".$fromuser."'";
			$user = pdo_fetch("SELECT * FROM ".tablename('wxuser')." WHERE $where  LIMIT 1");
			}
		
				
		//$user = pdo_fetch("SELECT * FROM ".tablename('wxuser')." WHERE $where  LIMIT 1");
		if(!$user['id']){
			$result = array('status' => -1, 'message' => 'NOT FIND THIS USER');
		}
		//增加判断用户当时发来消息数	
      else{
		$starttime = empty($_GPC['start']) ? strtotime(date('Y-m-d')) : strtotime($_GPC['start']);
		$endtime = empty($_GPC['end']) ? TIMESTAMP- 86399*2 : strtotime($_GPC['end']) ;
		$where = " AND createtime <= '$starttime' AND createtime > '$endtime'";
		$total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('stat_msg_history') . " WHERE weid = '{$_W['weid']}' AND module = 'kf'  AND from_user = '{$user['from_user']}' $where");
		
		$result = array(
		'status' => 1, 
		'id'=>$user['id'],
		'from_user'=>$user['from_user'],
		'avatar'=>$user['avatar']?$_W['attachurl'].$user['avatar']:'./resource/image/noavatar_middle.gif',
		'nickname'=>empty($user['nickname'])?'匿名':$user['nickname'],
		'fakeid'=>$user['fakeid'],
		'username'=>$user['username'],
		'city'=>$user['city'],
		'sex'=>$user['sex'],
		'qq'=>$user['qq'],
		'weid'=>$user['weid'],
		'ctime'=>$user['ctime'],
		'total'=>$total?$total:0,
		'groupid'=>$user['groupid'],
		'message' => '成功查询',
		
		);
    }
		if(empty($uid)){
		unset($result['from_user']);	
		message($result, '', 'ajax');
		}
		else
		{return $result;
			}

	}
	
	public function doWebuserList() {
		//设置在线用户列表
		checklogin();
		global $_W, $_GPC;
		$id = intval($_GPC['newonline']);
		$rid= intval($_GPC['id']);
		$newtime=intval($_GPC['newtime']);
		$condition = "AND status = '1' " ;
			if($id){//查询最新进入用户
			
				$condition .= "AND lasttime > {$newtime} " ;
				//读取现在最新一个在线用户ID
				$list = pdo_fetchall("SELECT * FROM ".tablename('kf_kflog')." WHERE weid = '{$_W['weid']}' $condition  ORDER BY lasttime DESC LIMIT 0,30 ");
				//print_r($list);exit;
				//判断有无最新,并且匹配是否是以前就有的数据
				if(!$list/*||in_array($list['uid'],$onlineusr)*/){
						$ulist['status']=-1;
						$ulist['message']='无最新在线用户';
						}
					else{
						
						foreach ($list as $key =>$row)
							{
								$ulist[]=$this->doUserinfo($list[$key]['oid'],3);//$_W['account']['type']原来为自动判断公号是否为高级号的变量
								$ulist[$key]['lastmessage']=$list[$key]['lasttime']?$list[$key]['lasttime']:TIMESTAMP;
							}
					}	
				
				}				
			
			else{//未最新用户数据
				$list = pdo_fetchall("SELECT * FROM ".tablename('kf_kflog')." WHERE weid = '{$_W['weid']}' $condition ORDER BY lasttime DESC LIMIT 0,30 ");
				
					if(empty($list)){
						$ulist['status']='-1';
						$ulist['message']='无在线用户';
					}
					else{
						foreach ($list as $key =>$row)
							{
								$ulist[]=$this->doUserinfo($list[$key]['oid'],3);//$_W['account']['type']原来为自动判断公号是否为高级号的变量
								$ulist[$key]['lastmessage']=$list[$key]['lasttime']?$list[$key]['lasttime']:TIMESTAMP;
							}
						}
						//print_r($ulist);exit;
						
				}
			
		
		message($ulist, create_url('site/module/userlist', array('name' => 'kf', 'type' => '3')), 'ajax');
		
	}
	//增加一个二维数据转一维函数
	public function array2to1($array1) { 
    foreach ($array1 as $v) {
					if (!$v) {
						continue;
					}
					$array2[] = $v[key($v)];
				}
    return $array2; 
	} 
	
	
	//判断用户离线
	public function doWeboutuser() {
		//设置离线用户列表
		checklogin();
		global $_W, $_GPC;
		$id = intval($_GPC['userid']);
		$rid= intval($_GPC['id']);
		$newtime=intval($_GPC['newtime']);
		$condition = "AND status =0 " ;
			if(!$id){//查询用户退出指令来判断设置用户离线状态
				$condition .= " AND lasttime>{$newtime}" ;
				/*用用户消息的方式判断离线用户
				$where = "AND type = 'usr' AND content='退出' OR content='再见' ";
				$starttime = empty($_GPC['start']) ? strtotime(date('Y-m-d')) : strtotime($_GPC['start']);
				$endtime = empty($_GPC['end']) ? TIMESTAMP : strtotime($_GPC['end']) + 86399;
				$where .= " AND ctime >= '$starttime' AND ctime < '$endtime'";
				$pindex = max(1, intval($_GPC['page']));
				$psize = 30;
				$outuser=pdo_fetchall("SELECT oid FROM ".tablename('kf_statmessage')." WHERE weid = '{$_W['weid']}' $where ORDER BY ctime DESC LIMIT ".($pindex - 1) * $psize.','. $psize);
				
				 此方式取消了。*/
				//print_r($outuser);
				$outuser=pdo_fetchall("SELECT * FROM ".tablename('kf_kflog')." WHERE weid = '{$_W['weid']}' $condition ORDER BY lasttime DESC LIMIT 0,30");
				
				if(empty($outuser)){
						$ulist['status']=-1;
						$ulist['message']='未有离线用户数据';
						}
					else{
						foreach ($outuser as $key => &$row)
						
						$ulist[]=$this->doUserinfo($outuser[$key]['oid'],3);//$_W['account']['type']原来为自动判断公号是否为高级号的变量
						$ulist[$key]['lastmessage']=$outuser[$key]['lasttime']?$outuser[$key]['lasttime']:TIMESTAMP;
					}
				
				}				
			
			else{//客服操作强制让用户下线
				$outusr=$this->doUserinfo($id,3);
				$status=pdo_update('kf_kflog', array('status'=>0,'lasttime'=>TIMESTAMP),array('oid'=>$outusr['from_user']));
				//$user['uid']=$id;
					if($status){
						$ulist['uid']=$id;
						$ulist['message']='已经移除用户在线状态';
					
					}
					else{
						$ulist['status']=-1;
						$ulist['message']='操作离线用户失败';
						}
						
				}
			
			
		
		message($ulist, create_url('site/module/userlist', array('name' => 'kf', 'type' => '3')), 'ajax');
		
	}
	
	public function doWebmessagelist() {//用户消息列表
		global $_GPC, $_W;
		checklogin();
		$rid= intval($_GPC['id']);
		
		$where = "AND type = 'usr'";
		$starttime = empty($_GPC['start']) ? strtotime(date('Y-m-d')) : strtotime($_GPC['start']);
		$endtime = empty($_GPC['end']) ? TIMESTAMP : strtotime($_GPC['end']) + 86399;
		$nowtime=TIMESTAMP;
		
		if($_GPC['newmessage']){
			
			$where .= " AND id > {$_GPC['maxid']} ";
			}
		else{	
			$where .= " AND ctime >= '$starttime' AND ctime < '$endtime'";
		}
		$pindex = max(1, intval($_GPC['page']));
		$psize = 30;
		//如果选择了用户后,只查询该用户发过来的消息
		if($_GPC['userid']) {
		$from_user=$this->doUserinfo($_GPC['userid'],3);//$_W['account']['type']原来为自动判断公号是否为高级号的变量
	
		$where .= " AND oid = '{$from_user['from_user']}'";
			if($_GPC['newmessage']){
				$list = pdo_fetchall("SELECT * FROM ".tablename('kf_statmessage')." WHERE weid = '{$_W['weid']}' $where ORDER BY ctime DESC LIMIT 0,30 ");
				}
				else{
				$list = pdo_fetchall("SELECT * FROM ".tablename('kf_statmessage')." WHERE weid = '{$_W['weid']}' $where ORDER BY ctime DESC LIMIT 0,30 ");
				}
		}
		else{
			if($_GPC['newmessage']){
				$list = pdo_fetchall("SELECT * FROM ".tablename('kf_statmessage')." WHERE weid = '{$_W['weid']}' $where ORDER BY ctime DESC LIMIT 0,30 ");
				}
			else{	
				$list = pdo_fetchall("SELECT * FROM ".tablename('kf_statmessage')." WHERE weid = '{$_W['weid']}' $where ORDER BY ctime DESC LIMIT ".($pindex - 1) * $psize.','. $psize);
			}
		}
		
		if (!empty($list)) {
			$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('kf_statmessage') . " WHERE weid = '{$_W['weid']}' $where");
			$pager = pagination($total, $pindex, $psize);
			
				foreach ($list as $index => &$history) {
				//这儿现在只高级接口，如果已经绑定就显示用户头像和昵称
					$condition = "AND from_user = '{$history['oid']}'";
					$sql ="SELECT wxusr,id,nickname,avatar FROM " . tablename('fans') . " WHERE weid = '{$_W['weid']}' $condition LIMIT 1 ";
					
					$wx = pdo_fetch($sql);
					
					if ($history['messagecat'] == 'link') {
						$history['content'] = iunserializer($history['content']);
						$history['content'] = '<a href="'.$history['content']['link'].'" target="_blank" title="'.$history['content']['description'].'">'.$history['content']['title'].'</a>';
					} 
					elseif ($history['messagecat'] == 'image') {
						$history['content'] = '<a href="'.$history['content'].'" target="_blank">查看图片</a>';
					} 
					elseif ($history['messagecat'] == 'location') {
						$history['content'] = iunserializer($history['content']);
						$history['content'] = '<a href="http://st.map.soso.com/api?size=800*600&center='.$history['content']['y'].','.$history['content']['x'].'&zoom=16&markers='.$history['content']['y'].','.$history['content']['x'].',1" target="_blank">查看方位</a>';
					} else {
						$history['content'] = emotion($history['content']);
					}
					$history['ctime']=date('Y-m-d H:i:s',$history['ctime']);
					
					if(!empty($wx)){
						$history['avatar']=$_W['attachurl'].$wx['avatar'];
						$history['fakeid']=$wx['fakeid']?$wx['fakeid']:$wx['id'];
						$history['nickname']=$wx['nickname'];
					}
					$history['total'] = $total;
					$info[]=$history;
				}
				
				
				
					
				
			
			//$info['total'] = $total;
			//$info['status'] = 1;
			//print_r($info);
		}
		else{
			$info['status'] = 0;
			$info['message']='没有新消息记录';
			
			
		}
		

		
		//$history['pager'] = $pager;
		
		message($info, create_url('site/module/messagelist', array('name' => 'kf', 'type' => '3')), 'ajax');
	
	}
	public function doWebChat() {
		//客服聊天界面
		global $_GPC, $_W;
		$where = "AND type = 'usr'";
		$list = pdo_fetch("SELECT id FROM ".tablename('kf_statmessage')." WHERE weid = '{$_W['weid']}' $where ORDER BY ctime DESC LIMIT 1");
		$newmessage=$_GPC['newmessage'];
		$userid=$_GPC['userid']?$_GPC['userid']:0;
		$maxid=$list['id']?$list['id']:2;	
		$rid= intval($_GPC['id']);
		$condition = "AND status = '1' " ;
		$list2 = pdo_fetchall("SELECT * FROM ".tablename('kf_kflog')." WHERE weid = '{$_W['weid']}' $condition ORDER BY lasttime DESC LIMIT 0,5 ");
		$newtime=$list2[0][lasttime]?$list2[0][lasttime]:TIMESTAMP;		
			
		include $this->template('chat');
	}
	public function doWebSend() {
		//消息推送
		global $_GPC, $_W;
		$message = $_GPC['message'];
  		$userid=intval($_GPC['fakeid']);
		$kfuid=$_GPC['kfid']?intval($_GPC['kfid']):'000'.$_W['uid'];
		//print_r($uid);exit;
		$rid= intval($_GPC['id']);
		$wx=pdo_fetch("SELECT * FROM ".tablename('fans')." WHERE weid = '{$_W['weid']}' AND id='{$userid}'  LIMIT 1");
		//查询发送消息对象是否客服
		$kf=pdo_fetch("SELECT uid FROM ".tablename('kf_kfuser')." WHERE weid = '{$_W['weid']}' AND uid='{$userid}'  LIMIT 1");
		//查询工单号
		$workid=pdo_fetch("SELECT workid FROM ".tablename('kf_work')." WHERE oid='{$wx['from_user']}' AND  weid = '{$_W['weid']}' ORDER BY ctime DESC  LIMIT 1 ");
		
		
  				 if(empty($userid)){
            			
						$info['stauts']=-1;
						$info['message']='用户ID为空！';
         		 }
 		 		elseif(empty($message))
                {
                  
				  $info['stauts']=-1;
				  $info['message']='内容为空！';
				  
                }
  				else{
					
						
						$msg=$this->dosendtxt('text', $message ,$wx['from_user']);
						$reply=$msg['message'];
						//print_r($msg);exit;
					
				//print_r($msg);exit;
                  if($msg['ret']=='0'){
					$kfmsg = array(
					'module'=>$_W['username'],
					'type'=>'wxlkf',
					'createtime'=>TIMESTAMP,
					'from_user'=>$wx['from_user'],
					'message'=>$message,
					'weid'=>$_W['weid'],
					);
					$data=pdo_insert('stat_msg_history', $kfmsg);
					if($kf){//如果是客服，设定工单号和后台管理员客服ID
						$workid['workid']='admin_'.$_W['weid'].'_'.$_W['username'];
						$kfuid=0;
						$type='admin';
					}else{
						
						$type=$_GPC['kfid']?'kf':'admin';
						}
					//新的方式记录客服发送消息
					$kfmessage=array('workid'=>$workid['workid'],'oid'=>$wx['from_user'],'content'=>$message,'type'=>$type,'messagecat'=>'text',ctime=>TIMESTAMP,'kfid'=>$kfuid,'weid'=>$_W['weid']);
					$data2=pdo_insert('kf_statmessage', $kfmessage);
					//新客服消息记录操作END
					
						if($data||$data2){
						$info['stauts']=1;
				  		$info['message']='发送给'.$wx['nickname'].'【'.$reply.'】';
						}
						else{
						$info['stauts']=1;
				  		$info['message']='发送给'.$wx['nickname'].'【'.$reply.'】，但系统烦忙。未存储到您的数据。';
						}
					
					}
					elseif($msg['ret']=='-6'||$msg['ret']=='-8'){
						$info['stauts']=-6;
				  		$info['message']='发送给'.$wx['nickname'].'【'.$reply.'】，请输入验证码。';
					
					}
					else{
						$info['stauts']=-1;
				  		$info['message']='发送给'.$wx['nickname'].'【'.$reply.'】';
					}
                 
                  
				}
				message($info, create_url('site/module/send', array('name' => 'kf', 'type' => '3','fakeid' => $userid)) , 'ajax');
	}
	
	public function doSendtxt($type='text',$message,$uid,$re='0') {
		//高级认证帐号消息推送接口
		global $_GPC, $_W;
		$token =wxl_weixin_token($_W['account'],$re);
		//文本内消息处理
		if($type=='text'){
		$url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token={$token}";
		$ms=array('touser'=>$uid, 'msgtype'=>$type,'text'=>array('content'=>urlencode($message)));
		}
		$dat = json_encode($ms);
		$dat = urldecode($dat);
		//print_r($dat);exit;
		$content = ihttp_post($url, $dat);
		
		if($content){
			$info=json_decode($content['content'], true);
			$message=account_weixin_code($info['errcode']);
			$msg=array('ret'=>$info['errcode'],'message'=>$message);
		}
		return $msg;
	}
	public function doWebjdsend() { 
		//聊天（非客服界面)
		global $_GPC, $_W;
		$message = $_GPC['message'];
  		$uid=$_GPC['fakeid']?$_GPC['fakeid']:$_GPC['oid'];
		
		$rid= intval($_GPC['id']);
			
		$wx=pdo_fetch("SELECT * FROM ".tablename('fans')." WHERE weid = '{$_W['weid']}' AND id='{$uid}'  LIMIT 1");
  				
				  if(empty($uid)){
            			message('用户ID为空！', create_url('site/module/jdchat', array('name' => 'kf', 'type' => '3','fakeid' => $uid,'id'=>$rid)) , 'error');
         		 }
 		 		elseif(empty($message))
                {
                  message('内容不能为空！', create_url('site/module/jdchat', array('name' => 'kf', 'type' => '3','fakeid' => $uid,'id'=>$rid)), 'error');
                }
  				else{
					$msg=$this->dosendtxt('text', $message ,$wx['from_user']);
					$reply=$msg['message'];
					if($msg['ret']=='0'){
					$kfmsg = array(
					'module'=>$_W['username'],
					'type'=>'wxlkf',
					'createtime'=>TIMESTAMP,
					'from_user'=>$wx['from_user'],
					'message'=>$message,
					'weid'=>$_W['weid'],
					);
					
					pdo_insert('stat_msg_history', $kfmsg);
					}
					message('发送给'.$wx['nickname'].'【'.$reply.'】', create_url('site/module/jdchat', array('name' => 'kf', 'type' => '3','fakeid' => $uid,'id'=>$rid)), 'success');
                 
                  
				}
	}
	public function doWebjdajax() { 
		global $_GPC, $_W;
		$data=array('status'=>0);
		$msghistory = pdo_fetchall("SELECT * FROM ".tablename('stat_msg_history')." WHERE  weid = {$_W['weid']}  and from_user='{$_GPC["from_user"]}' and id>{$_GPC["maxid"]} order by createtime desc limit 10");
		if($msghistory!=false){
			$data['status']=1;
			$data['data']=$msghistory;
		}
		die(json_encode($data));
	}
	public function doWebjdchat() { 
		//非客服界面的，简洁的纯聊天,无论用户有无进入客服系统。
		global $_GPC, $_W;
		checklogin();
		$rid= intval($_GPC['id']);
		$uid=$_GPC['fakeid']?$_GPC['fakeid']:$_GPC['oid'];
			
				$wx=pdo_fetch("SELECT * FROM ".tablename('fans')." WHERE weid = '{$_W['weid']}' AND id='{$uid}'  LIMIT 1");
			
			//$wx=pdo_fetch("SELECT * FROM ".tablename('fans')." WHERE weid = '{$_W['weid']}' AND id='{$uid}'  LIMIT 1");
			//$wx['fakeid']=$wx['id'];
		
		if(empty($uid)){
			$glist=$this->Groupdata();
		
		$towday=TIMESTAMP-2*86400;
		$alist = pdo_fetchall("SELECT * FROM ".tablename('stat_msg_history')." WHERE weid = '{$_W['weid']}'  AND  createtime>'{$towday}' GROUP BY from_user  ORDER BY createtime DESC ");
		
		foreach($alist as $row){
				if($row['from_user']){
					$wxlist[]=$this->doUserinfo($row['from_user'],3);
				}
			}
			//print_r($wxlist);exit;
			
			include $this->template('chatuserlist');
			exit;
		}
		elseif(empty($wx['from_user'])){
	
			message('对不起，用户'.$uid.'还未绑定，暂时无法显示其发送过来的内容。', create_url('site/module/jdchat', array('name' => 'kf', 'type' => '3', 'id'=>$rid)) , 'error');
		}
		else
		{
		$pindex = max(1, intval($_GPC['page']));
		$psize = intval($_GPC['psize'])?intval($_GPC['psize']): 20;
		$list = pdo_fetchall("SELECT * FROM ".tablename('stat_msg_history')." WHERE weid = '{$_W['weid']}'  AND  from_user ='{$wx['from_user']}'  ORDER BY createtime DESC LIMIT ".($pindex - 1) * $psize.','. $psize);
		if (!empty($list)) {
			 $total= pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('stat_msg_history') . " WHERE weid = '{$_W['weid']}' AND  from_user ='{$wx['from_user']}'");
			 $pager = pagination($total, $pindex, $psize);
			 $maxid=$list['0']['id']?$list['0']['id']:2;
			 //$pager = pagination($total, $pindex, $psize, create_url('send/kf',array('fakeid'=>$uid)), array('ajaxcallback'=>'ajaxpager'));
				//对消息内容进行一些处理。
				foreach ($list as $index => &$row) {
					if ($row['type'] == 'link') {
						$row['message'] = iunserializer($row['message']);
						$row['message'] = '<a href="'.$row['message']['link'].'" target="_blank" title="'.$row['message']['description'].'">'.$row['message']['title'].'</a>';
					} elseif ($row['type'] == 'image') {
						$row['message'] = '<a href="'.$row['message'].'" target="_blank">查看图片</a>';
					} elseif ($row['type'] == 'location') {
						$row['message'] = iunserializer($row['message']);
						$row['message'] = '<a href="http://st.map.soso.com/api?size=800*600&center='.$row['message']['y'].','.$row['message']['x'].'&zoom=16&markers='.$row['message']['y'].','.$row['message']['x'].',1" target="_blank">查看方位</a>';
					} else {
						$row['message'] = emotion($row['message']);
					}
					if (!empty($row['rid'])) {
						$rids[$row['rid']] = $row['rid'];
					}
			}
			//消息内容处理完成
			}
		}
		include $this->template('jdchat');
	
	}
	//工单管理
	public function doWebWork() {//工单管理
		global $_GPC, $_W;
		checklogin();
		$eid=intval($_GPC['eid']);
		$id = intval($_GPC['id']);
		if (checksubmit('delete') && !empty($_GPC['select'])) {
			pdo_delete('kf_work', " id  IN  ('".implode("','", $_GPC['select'])."')");
			message('删除成功！', create_url('site/module/work', array('type' => '3', 'name' => 'kf', 'eid' => $eid, 'page' => $_GPC['page'])));
		}
		if(!empty($_GPC['wid'])){
			//执行工单状态设置
			$status=$_GPC['rids']?0:1;
			$wid=intval($_GPC['wid']);
			$setzt=pdo_update('kf_work',array('status'=>$status),array('id'=>$wid));
			if($setzt){
				return true;
				}else{
				return false;	
					}
					
			}
		$where = '';
		$starttime = empty($_GPC['start']) ? strtotime(date('Y-m-d')) : strtotime($_GPC['start']);
		$endtime = empty($_GPC['end']) ? TIMESTAMP : strtotime($_GPC['end']) + 86399;
		$where .= " AND ctime >= '$starttime' AND ctime < '$endtime'";
		!empty($_GPC['keyword']) && $where .= " AND workid LIKE '%{$_GPC['keyword']}%' OR kfid LIKE '%{$_GPC['keyword']}%'";
		
		$pindex = max(1, intval($_GPC['page']));
		$psize = 30;
		$list = pdo_fetchall("SELECT * FROM ".tablename('kf_work')." WHERE weid = '{$_W['weid']}' $where ORDER BY ctime DESC LIMIT ".($pindex - 1) * $psize.','. $psize);
		if (!empty($list)) {
			foreach ($list as $index => &$work) {
			if($work['oid'])	{
				$user[$index]=$this->doUserinfo($work['oid'],3);
				$kflog[$index]=$this->doWebKflog($work['oid'],1);
				}
			if($work['kfid'])	{
				$kf[$index]=$this->doUserinfo($work['kfid'],3);
				}		
			}
			//print_r($kflog);exit;
		}
	
		$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('kf_work') . " WHERE weid = '{$_W['weid']}' $where");
		$pager = pagination($total, $pindex, $psize);
		
		include $this->template('work');
	}
	//客服聊天记录查询：1，按工单，2，按用户，3，按客服工号
	public function doWebWorkmessage() {
		//客服消息管理
		global $_GPC, $_W;
		checklogin();
		$id = intval($_GPC['id']);
		$eid = intval($_GPC['eid']);
		$oid=$_GPC['oid'];
		$workid=$_GPC['workid'];
		$kfid=intval($_GPC['kfid']);
		
		if (checksubmit('delete') && !empty($_GPC['select'])) {
			pdo_delete('kf_statmessage', " id  IN  ('".implode("','", $_GPC['select'])."')");
			message('删除成功！', create_url('site/module/workmessage', array('type' => '3', 'name' => 'kf', 'eid' => $eid, 'page' => $_GPC['page'])));
		}
		$where = '';
		$starttime = empty($_GPC['start']) ? strtotime(date('Y-m-d')) : strtotime($_GPC['start']);
		$endtime = empty($_GPC['end']) ? TIMESTAMP : strtotime($_GPC['end']) + 86399;
		if(empty($oid)&&empty($workid)&&empty($kfid)){
		$where .= " AND ctime >= '$starttime' AND ctime < '$endtime'";
		//print_r('test');exit;
		}
		!empty($_GPC['keyword']) && $where .= " AND content LIKE '%{$_GPC['keyword']}%' OR workid LIKE '%{$_GPC['keyword']}%' OR kfid LIKE '%{$_GPC['keyword']}%'";
		//客服和用户消息分类检索
		$_GPC['kf'] && $where .= " AND type='kf' ";
		$_GPC['usr'] && $where .= " AND type='usr' ";
		//按用户，按工单号，按客服号分别查询
		!empty($oid) && $where .= " AND oid= '{$oid}' ";
		!empty($workid) && $where .= " AND workid= '{$workid}' ";
		!empty($kfid) && $where .= " AND kfid= '{$kfid}' ";
		
		$pindex = max(1, intval($_GPC['page']));
		$psize = 30;
		$list = pdo_fetchall("SELECT * FROM ".tablename('kf_statmessage')." WHERE weid = '{$_W['weid']}' $where ORDER BY ctime DESC LIMIT ".($pindex - 1) * $psize.','. $psize);
		if (!empty($list)) {
			foreach ($list as $index => &$work) {
			//消息内容处理
			if ($work['messagecat'] == 'link') {
					$work['content'] = iunserializer($work['content']);
					$work['content'] = '<a href="'.$work['content']['link'].'" target="_blank" title="'.$work['content']['description'].'">'.$work['content']['title'].'</a>';
				} elseif ($work['messagecat'] == 'image') {
					$work['content'] = '<a href="'.$work['content'].'" target="_blank">查看图片</a>';
				} elseif ($work['messagecat'] == 'location') {
					$work['content'] = iunserializer($work['content']);
					$work['content'] = '<a href="http://st.map.soso.com/api?size=800*600&center='.$work['content']['y'].','.$work['content']['x'].'&zoom=16&markers='.$work['content']['y'].','.$work['content']['x'].',1" target="_blank">查看方位</a>';
				} else {
					$work['content'] = emotion($work['content']);
				}	
			if($work['oid'])	{
				$user[$index]=$this->doUserinfo($work['oid'],3);
				}
			if($work['kfid'])	{
				$kf[$index]=$this->doUserinfo($work['kfid'],3);
				}	
			}

		}
	
		$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('kf_statmessage') . " WHERE weid = '{$_W['weid']}' $where");
		$pager = pagination($total, $pindex, $psize);
		
		include $this->template('workmessage');
	}
	public function doWebKflog($oid,$cha){
		//客户来访日志
		global $_GPC, $_W;
		$ajax=$_GPC['ajaxkflog'];	
		$pindex = max(1, intval($_GPC['page']));
		$psize = 30;
		$kflog=pdo_fetchall("SELECT * FROM ".tablename('kf_kflog')." WHERE weid = '{$_W['weid']}' $where ORDER BY lasttime DESC LIMIT ".($pindex - 1) * $psize.','. $psize);
		$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('kf_kflog') . " WHERE weid = '{$_W['weid']}' $where");
		$pager = pagination($total, $pindex, $psize);
		
		if(!$oid&&!$cha&&$ajax){
		//如果非内部函数调用，且非查一个用户执行
		
		message($kflog, '', 'ajax');
		exit;
		}
		if($oid&&$cha){
			//内部调用某一个用户
			$where = "AND oid='{$oid}' ";
			$kflog=pdo_fetch("SELECT * FROM ".tablename('kf_kflog')." WHERE weid = '{$_W['weid']}' $where  LIMIT 1");
			return $kflog;
		}
		include $this->template('kflog');
		
	}
	
  	public function doWebManage() {//消息管理
		global $_GPC, $_W;
		checklogin();
		$id = intval($_GPC['id']);
		if (checksubmit('delete') && !empty($_GPC['select'])) {
			pdo_delete('stat_msg_history', " id  IN  ('".implode("','", $_GPC['select'])."')");
			message('删除成功！', create_url('site/module/manage', array('type' => '3', 'name' => 'kf', 'id' => $id, 'page' => $_GPC['page'])));
		}
		$where = '';
		$starttime = empty($_GPC['start']) ? strtotime(date('Y-m-d')) : strtotime($_GPC['start']);
		$endtime = empty($_GPC['end']) ? TIMESTAMP : strtotime($_GPC['end']) + 86399;
		$where .= " AND createtime >= '$starttime' AND createtime < '$endtime'";
		!empty($_GPC['keyword']) && $where .= " AND message LIKE '%{$_GPC['keyword']}%'";
		if($_GPC['kf']){
		 $mod=pdo_fetchall("SELECT name FROM ".tablename('modules')."  ORDER BY mid DESC");
		 foreach($mod as $v){
			$where.=" AND module <> '".$v['name']."'";
			}
			$where.=$where."AND module <> 'welcome' AND module <> 'default'";
		}
		else{
		$where .= " AND module = 'kf' ";
		}
		$pindex = max(1, intval($_GPC['page']));
		$psize = 50;
		$list = pdo_fetchall("SELECT * FROM ".tablename('stat_msg_history')." WHERE weid = '{$_W['weid']}' $where ORDER BY createtime DESC LIMIT ".($pindex - 1) * $psize.','. $psize);
		if (!empty($list)) {
			foreach ($list as $index => &$history) {
			//增加判断用户是否绑定ＦＩＤ，如果已经绑定就显示用户头像和昵称
			$condition = "AND from_user = '{$history['from_user']}'";
			
					$sql ="SELECT id,nickname,avatar FROM " . tablename('fans') . " WHERE weid = '{$_W['weid']}' $condition LIMIT 1 ";
					
			//$sql ="SELECT username,fakeid,nickname,avatar FROM " . tablename('wxuser') . " WHERE weid = '{$_W['weid']}' $condition LIMIT 1 ";
			$wx = pdo_fetch($sql);
			if ($history['type'] == 'link') {
			$history['message'] = iunserializer($history['message']);
			$history['message'] = '<a href="'.$history['message']['link'].'" target="_blank" title="'.$history['message']['description'].'">'.$history['message']['title'].'</a>';
			}
			elseif ($history['type'] == 'image') {
			$history['message'] = '<a href="'.$history['message'].'" target="_blank">查看图片</a>';
			}
			elseif ($history['type'] == 'location') {
			$history['message'] = iunserializer($history['message']);
			$history['message'] = '<a href="http://st.map.soso.com/api?size=800*600&center='.$history['message']['y'].','.$history['message']['x'].'&zoom=16&markers='.$history['message']['y'].','.$history['message']['x'].',1" target="_blank">查看方位</a>';
			} 
			else {
			$history['message'] = emotion($history['message']);
			}
			if (!empty($history['rid'])) {
			$rids[$history['rid']] = $history['rid'];
			}
			if(!empty($wx)){
			$history['from_user']='<a href="'.create_url('site/module/jdchat', array('name'=>'kf','fakeid' => $wx['fakeid'])).'" title="'.$wx['nickname'].'"><img width="45px" height="45px" src="'.$_W['attachurl'].$wx['avatar'].'" /></a><br />'.$wx['nickname'];
			}
			else{
			$history['from_user']='<a href="'.create_url('site/module/pip', array('name'=>'moli','id' => $history['id'])).'" title="'.$history['from_user'].'">'.$history['from_user'].'</a>';
			
			}
			if ($history['type'] == 'wxlkf') {
			$history['from_user'] = '<span style="color:red">客服:'.$history['module'].'的回复</span>';
			} 
			}

		}
	
	$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('stat_msg_history') . " WHERE weid = '{$_W['weid']}' $where");
	$pager = pagination($total, $pindex, $psize);
		
		include $this->template('manage');
	}
	//客服人员设置相关
		public function doWebkfset() {
			global $_GPC, $_W;
			checklogin();
			$set = intval($_GPC['set'])?intval($_GPC['set']):1;
			$id = intval($_GPC['uid']);
			$kfuser=pdo_fetch("SELECT * FROM ".tablename('kf_kfuser')." WHERE weid = '{$_W['weid']}' AND uid={$id}  LIMIT 1");
			$pindex = max(1, intval($_GPC['page']));
			$psize = 50;
			$list = pdo_fetchall("SELECT * FROM ".tablename('kf_kfuser')." WHERE weid = '{$_W['weid']}'   LIMIT ".($pindex - 1) * $psize.','. $psize);
			//添加客服
			if($set==1){
				$userinfo=pdo_fetch("SELECT id,from_user,nickname,avatar,groupid FROM ".tablename('fans')." WHERE weid = '{$_W['weid']}' AND id={$id}  LIMIT 1");
				$data = array(				
				'uid' =>$id,
				'oid' =>$userinfo['from_user'],
				'nickname' =>$userinfo['nickname'],
				'avatar' =>$userinfo['avatar'],
				'groupid' =>$userinfo['groupid'],
				'kfzt' =>0,
				'weid' =>$_W['weid'],				
				);
				
				if($kfuser){
					message('该用户已是客服人员了！', create_url('site/module/kfset', array('type' => '3', 'name' => 'kf', 'set' => 1, 'page' => $_GPC['page'])));
					exit;
				}
				
				
				if (checksubmit('submit')) {	
				if (empty($data['uid'])) {
					message('请填写UID！', create_url('site/module/kfset', array('type' => '3',  'name' => 'kf', 'set' => 1, 'page' => $_GPC['page'])));
					exit;
				}
				if(!$userinfo){
					message('未找到该用户，请核实后重填！', create_url('site/module/kfset', array('type' => '3',  'name' => 'kf', 'set' => 1, 'page' => $_GPC['page'])));
					exit;
				}
				if (empty($data['oid'])) {
					message('请填写OPENID！', create_url('site/module/kfset', array('type' => '3',  'name' => 'kf', 'set' => 1, 'page' => $_GPC['page'])));
					exit;
				}
				
				pdo_insert('kf_kfuser', $data);
				message('添加成功！', create_url('site/module/kfset', array('type' => '3',  'name' => 'kf', 'set' => 1, 'page' => $_GPC['page'])));			
				}
				
				if (checksubmit('del')&& !empty($_GPC['select'])) {			
					pdo_delete('kf_kfuser', " uid  IN  ('".implode("','", $_GPC['select'])."')");
					message('删除成功！', create_url('site/module/kfset', array('type' => '3',  'name' => 'kf', 'set' => 1, 'page' => $_GPC['page'])));	
						
				}
				include $this->template('listkf');
			}
			//编辑客服资料
			if($set==2){
				
				$data = array(				
				'uid' =>$id,
				'oid' =>$_GPC['oid'],
				'nickname' =>$_GPC['nickname'],
				'kfzt' =>$_GPC['kfzt'],
				'weid' =>$_W['weid'],				
				);
				if (checksubmit('edit')) {
				pdo_update('kf_kfuser', $data,array('uid'=>$id));
				message('编辑成功！', create_url('site/module/kfset', array('type' => '3',  'name' => 'kf', 'set' => 1, 'page' => $_GPC['page'])));
				}
				include $this->template('editkf');
			}
			
			
		}
	//将会员设置为客服人员
	public function doWebaddkf() {
			global $_GPC, $_W;
			checklogin();
			$uid = intval($_GPC['uid']);
			
			$pindex = max(1, intval($_GPC['page']));
			$psize = 50;
			$kfusr = pdo_fetch("SELECT * FROM ".tablename('kf_kfuser')." WHERE weid = '{$_W['weid']}' AND uid='{$uid}'   LIMIT 1 ");
			$fans=pdo_fetch("SELECT * FROM ".tablename('fans')." WHERE weid = '{$_W['weid']}' AND id='{$uid}'   LIMIT 1 ");
			if($kfusr){
				
				message('对不起，用户'.$uid.'已经是客服了', create_url('site/module/manage', array('name' => 'reg', 'type' => '3')) , 'error');
			}
			else if(!$fans){
				message('对不起，未找到ID:'.$uid.'的用户', create_url('site/module/manage', array('name' => 'reg', 'type' => '3'))  , 'error');
				}
			else{
				$kfuser=array(
				'uid'=>$fans['id'],
				'oid'=>$fans['from_user'],
				'nickname'=>$fans['nickname'],
				'avatar'=>$fans['avatar'],
				'groupid'=>$fans['groupid'],
				'weid'=>$_W['weid'],
				
				);
				$addusr=pdo_insert('kf_kfuser', $kfuser);
				if($addusr){
					message('用户:'.$fans['nickname'].'已经成功设置为客服人员', create_url('site/module/manage', array('name' => 'reg', 'type' => '3'))  , 'success');
					}
				
			}
			
		}
	
	//用户群组信息数据返回
	public function Groupdata($id=null) {
		global $_GPC, $_W;
		
		if(!$id){
		$gid = intval($_GPC['gid']);
		}
		if($gid){
			$list = pdo_fetch("SELECT * FROM ".tablename('fans_group')." WHERE weid = '{$_W['weid']}' AND id='{$gid}'   LIMIT 1");
		}else{
		
		$list = pdo_fetchall("SELECT * FROM ".tablename('fans_group')." WHERE weid = '{$_W['weid']}' $where   ORDER BY id DESC");
		}
		return $list;
	}
	//群发消息界面	
	public function doWebqunchat(){
		$glist=$this->Groupdata();
		
		include $this->template('qunfa_display');
			}
	//群发消息处理程序	
	//群发消息处理程序	
	public function doWebqunsend(){
		
		global $_W, $_GPC;
		checklogin();
		$message = $_GPC['message'];
		if(!empty($message)){
		
		$groupid = $_GPC['gid'] ? $_GPC['gid'] : 0;
		$page = intval($_GPC['page']);
		$page = empty($page) ? 0 : $page;
		$imgcode = $_GPC['imgcode'];
		$pagesize = intval($_GPC['pagesize']);
		$pagesize = empty($pagesize) ? 10 : $pagesize;
		//查出48小时内活动过的用户
		$towday=TIMESTAMP-2*86400;
		$alist = pdo_fetchall("SELECT * FROM ".tablename('stat_msg_history')." WHERE weid = '{$_W['weid']}'  AND  createtime>'{$towday}' GROUP BY from_user  ORDER BY createtime DESC ");
		//增加判断未匹配用户组
		if($groupid==-1){
			
				//不选择分组
				$userlist = pdo_fetchall("SELECT from_user FROM ".tablename('fans')." WHERE weid = '{$_W['weid']}'  ORDER BY id DESC  LIMIT ".$page.','. $pagesize);
				$userlist=array_intersect_assoc($alist,$userlist);
				//$total= pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('fans') . " WHERE weid = '{$_W['weid']}' ");
				$total=count($userlist);
				$pageCount=(($total/$pagesize)-1)>1?(($total/$pagesize)-1):1;
				//print_r('total'.$total.'pageCount'.$pageCount);exit;
			
			}
		else{
			
				//按分组发送
				$userlist = pdo_fetchall("SELECT from_user FROM ".tablename('fans')." WHERE weid = '{$_W['weid']}' AND groupid='{$groupid}' ORDER BY id DESC  LIMIT ".$page.','. $pagesize);
				//$total= pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('fans') . " WHERE weid = '{$_W['weid']}' AND groupid='{$groupid}'");
				$userlist=array_intersect_assoc($alist,$userlist);
				$pageCount=(($total/$pagesize)-1)>1?(($total/$pagesize)-1):1;
			
		}
		$tj='';
		if($page>=0 && $pageCount>=$page){
				if($groupid==-1){
					
					//高级接口
					$ms['type']='text';
					$ms['content']=$message;
						foreach ($userlist as $uid) {
							
							$reinfo=gjjk_send($ms, $uid);
							
								$info=json_decode($reinfo['content'], true);
								$ts=account_weixin_code($info['errcode']);
							if($info['errcode']){
								$tj.='发送给'.$uid['from_user'].'出错码：'.$info['errcode'].'错误：'.$ts.'<br />';
							}
							else{
								$tj.='发送给'.$uid['from_user'].'成功<br />';
								}
						}
				
				}
				else{
					
					//高级接口
					$ms['type']='text';
					$ms['content']=$message;
						foreach ($userlist as $uid) {
							
							$reinfo=gjjk_send($ms, $uid);
							
								$info=json_decode($reinfo['content'], true);
								$ts=account_weixin_code($info['errcode']);
							if($info['errcode']){	
								$tj.='发送给'.$uid['from_user'].'出错码：'.$info['errcode'].'错误：'.$ts.'<br />';
							}
							else{
								$tj.='发送给'.$uid['from_user'].'成功<br />';
								}
						}
				
					
				
			}
			if($pageCount==$page){
				message($tj.'本次群发任务结束！', create_url('site/module/qunchat',array('name'=>'kf')) , 'success');
			}
			
			else{
				
				message($tj.'请勿关闭浏览器还在群发中...！', create_url('site/module/qunsend/',array('name'=>'kf')).'&page='.($page+1).'&message='.$message.'&pagesize='.$pagesize.'&gid='.$groupid , 'success');
				
			}
			
			
		}else{
			message('参数错误！', create_url('site/module/qunchat',array('name'=>'kf')), 'error');
		}
	}else{
		message('需要发送的内容不能为空！', create_url('site/module/qunchat',array('name'=>'kf')), 'error');
	}
		
		}	
		
		
}
