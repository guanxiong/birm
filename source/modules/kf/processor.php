<?php
/**
 * 客服系统.
 * 
 * [19.3cm ] Copyright (c) 2013 QQ 81324093
 */
defined('IN_IA') or exit('Access Denied');
include_once IA_ROOT . '/source/modules/19.3cm.php';
class kfModuleProcessor extends WeModuleProcessor {
	
	public $name = 'kfModuleProcessor';
	public function respond() {
		global $_W;
		$rid = $this->rule;
		$sql = "SELECT * FROM " . tablename('kf') . " WHERE `rid`=:rid LIMIT 1";
		$row = pdo_fetch($sql, array(':rid' => $rid));
		$message = $this->message;
		$key=$message['content'];
		$lastmessage=TIMESTAMP;
		$user = pdo_fetch("SELECT id,nickname,from_user FROM ".tablename('fans')." WHERE from_user ='{$this->message['from']}'  LIMIT 1");
		$zt = pdo_fetch("SELECT * FROM ".tablename('kf_kflog')." WHERE oid='{$this->message['from']}' AND weid='{$_W['weid']}' LIMIT 1");
		
		
		//搜索自动应答设置
		$kfwenda = pdo_fetchall("SELECT id,name,displayorder,description FROM ".tablename('article_category')." WHERE parentid ='{$row['cateid']}' AND weid='{$_W['weid']}'");
		//$kfwenda=0;//暂时设定不用自动应答
		//增加查询是否是客服，方便后面设置上下文时长
		$kfuid = pdo_fetch("SELECT uid,formoid,oid FROM ".tablename('kf_kfuser')." WHERE weid = '{$_W['weid']}' AND oid='{$this->message['from']}'   LIMIT 1 ");
		
		if((!$kfwenda||$row['cateid']==0)&&($key!='人工'||$key!='客服管理')){
			//无自动应答，自动开始上下文锁定．
			$kfwenda=0;
			$this->beginContext($row['timeout']);
			
			//END
			
		}
					
		if(!$this->inContext) {
	  			//人工服务开始前
				$tips=$this->kfbefore($kfuid['uid'],$user,$kfwenda,$row['wechattype'],$row['timeout']);
				//print_r($tips);exit;
				if(is_array($tips)){
					return $this->respNews($tips);
				}else{
					return $this->respText($tips);
				}
			
		}else{
			//开始上下文会话
			$kfu = pdo_fetch("SELECT * FROM ".tablename('kf_kfuser')." WHERE uid='{$user['id']}' AND  weid = '{$_W['weid']}'   LIMIT 1 ");
			if($key=='客服管理'){
				//print_r($kfu);pdo_debug();exit;
				if($kfu){
					$_SESSION['kfid']=$kfu['uid'];
					$_SESSION['kfzt']=1;
					
					//设置客服状态,1：空闲，2：锁定，0：离线，但不对外（空OID）发送消息
					if($kfu['formoid']){
						pdo_update('kf_kfuser', array('kfzt'=>2),array('uid'=>$_SESSION['kfid']));
						$workid=pdo_fetch("SELECT workid FROM ".tablename('kf_work')." WHERE oid='{$kfu['formoid']}' AND  weid = '{$_W['weid']}' ORDER BY ctime DESC  LIMIT 1 ");
						$_SESSION['workid']=$workid['workid'];
					}else{
						pdo_update('kf_kfuser', array('kfzt'=>1,'formoid'=>0),array('uid'=>$_SESSION['kfid']));
					}
					//增加客服在线状态
					if($row['wechattype']){
						$data=array('uid'=>$user['id'],'oid'=>$this->message['from'],'wxusr'=>$user['nickname'],'lastmessage'=>$lastmessage,'type'=>'kf','weid'=>$_W['weid']);
					}else{
						$data=array('uid'=>$user['fakeid'],'oid'=>$this->message['from'],'wxusr'=>$user['nickname'],'lastmessage'=>$lastmessage,'type'=>'kf','weid'=>$_W['weid']);
					}
					/*客户人员就不放入在线用户了
             		 //下面执行插入用户在线状态数据。
					
					if(!$zt){
						pdo_insert('fans_status', $data);
					}
					else
					{
						$joinuid=$data['uid'];
						unset($data['uid']);
						pdo_update('fans_status', $data,array('uid'=>$joinuid));
					}
					$onlineuser=pdo_fetchall("SELECT uid FROM ".tablename('fans_status')." WHERE  WEID='{$_W['weid']}' AND type='kf' ");
					cache_write('kf:'.$_W['weid'], $onlineuser);
					//客服在线状态增加完成
					*/
					return $this->respText('亲爱的'.$user['nickname'].'您已经进入客服管理平台，请保持在线，等待用户发送消息过来。若已经下班，请输入：[下线]退出客服管理');
					
				} else{//pdo_debug();exit;
					return $this->respText('亲爱的'.$user['nickname'].'您不是客服人员，不能进行此操作！');
				}
			}
			if($key=='再见'&&$_SESSION['kfid']&&$kfuid){
					//更新客服的状态为在线非锁定(当然暂时只有高级号才有这操作)
					$kfusr = pdo_fetch("SELECT * FROM ".tablename('kf_kfuser')." WHERE weid = '{$_W['weid']}' AND uid='{$_SESSION['kfid']}'   LIMIT 1 ");
					gjjk_send($message,$kfusr['formoid']);
					pdo_update('kf_kfuser', array('kfzt'=>1,'formoid'=>0),array('uid'=>$_SESSION['kfid']));
					$_SESSION['kfzt']=1;
					unset($_SESSION['workid']);
					return $this->respText('亲爱的'.$user['nickname'].'您已经断开和该用户的对话状态！');
				
			}
			if($key=='下线'&&$_SESSION['kfid']&&$kfuid){
					$kfusr = pdo_fetch("SELECT * FROM ".tablename('kf_kfuser')." WHERE weid = '{$_W['weid']}' AND uid='{$_SESSION['kfid']}'   LIMIT 1 ");
					if($kfuid){
						gjjk_send($message,$kfusr['formoid']);
						pdo_update('kf_kfuser', array('kfzt'=>0,'formoid'=>0),array('uid'=>$user['id']));
						$this->endContext();
						return $this->respText('亲爱的'.$user['nickname'].'您已经进下线成功。');
					}else{
						return $this->respText('亲爱的'.$user['nickname'].'您不是客服人员，不能进行此操作！');
					}	
					
			}
		
			//END高级帐号的客服管理
		
			if(($key=='人工'&&!$_SESSION['RG'])/*||$row['cateid']==0&&$key=='客服'*/){
				//$this->beginContext(1200); 
				//若无自动应答，或者用户选择了输入人工，则执行下面的操作
				//作了高级认证号权限和非权限号判断来处理用户信息记录
				$_SESSION['RG']=1;
				$workid='kf'.$_W['weid'].'_'.random(3).TIMESTAMP.random(5,1);
				$_SESSION['workid']=$workid;
				//查询该用户有没有在日志表中有记录，未用增加，有更新来访次数
				$kflog = pdo_fetch("SELECT * FROM ".tablename('kf_kflog')." WHERE oid ='{$this->message['from']}' AND weid='{$_W['weid']}' LIMIT 1");
				
				if($kflog){
					$datalog['num']=$kflog['num']+1;
					$datalog['lasttime']=$lastmessage;
					$datalog['bak']='第'.($kflog['num']+1).'来访';
					$datalog['status']=1;
					pdo_update('kf_kflog', $datalog,array('oid'=>$this->message['from'],'weid'=>$_W['weid']));
				}else{
					$datalog['oid']=$this->message['from'];
					$datalog['num']=1;
					$datalog['cattype']='new';//默认为新客户分类，具体客户类别在模块设置里面设置
					$datalog['bak']='初次来访';
					$datalog['lasttime']=$lastmessage;
					$datalog['weid']=$_W['weid'];
					$datalog['status']=1;
					pdo_insert('kf_kflog', $datalog);
				}
				
				if($row['wechattype']){
					$data=array('uid'=>$user['id'],'oid'=>$this->message['from'],'wxusr'=>$user['nickname'],'lastmessage'=>$lastmessage,'type'=>'kf','weid'=>$_W['weid']);
				}else{
					$data=array('uid'=>$user['fakeid'],'oid'=>$this->message['from'],'wxusr'=>$user['nickname'],'lastmessage'=>$lastmessage,'type'=>'kf','weid'=>$_W['weid']);
				}
				
				/*原来的用户在线状态取消
              	//下面执行插入用户在线状态数据。
				//$zt = pdo_fetch("SELECT uid,lastmessage,type FROM ".tablename('fans_status')." WHERE uid ='{$data['uid']}' AND weid='{$_W['weid']}' LIMIT 1");
				if(!$zt){
					pdo_insert('fans_status', $data);
				}else{
					$joinuid=$data['uid'];
					unset($data['uid']);
					pdo_update('fans_status', $data,array('uid'=>$joinuid));
				}
				$onlineuser=pdo_fetchall("SELECT uid FROM ".tablename('fans_status')." WHERE  WEID='{$_W['weid']}' AND type='kf' ");
				cache_write('kf:'.$_W['weid'], $onlineuser);
				*/
				//高级帐号才执行此操作
				if($row['wechattype']){
				//查询在线客服
				$kfuser=pdo_fetchall("SELECT * FROM ".tablename('kf_kfuser')." WHERE  WEID='{$_W['weid']}' AND kfzt=1 limit 0,30 ");
			
				if(!$kfuser){
					//无客服时返回：分是客服进入还是非客服进入时回复不同内容
					if(!empty($kfuid)){
						$tips="\n请发送[ 客服管理 ],进行客服工作.";
						}else{
						$tips="\n对不起暂时没有客服空闲，请等待，\n或者请发送：【退出】，作其他操作。下次再来";
						}
						//$_SESSION['kfid']=$user['id'];	
						$workdata=array('workid'=>$workid,'oid'=>$this->message['from'],'kfid'=>$kfuid,'status'=>0,'weid'=>$_W['weid'],'ctime'=>TIMESTAMP);	
					}
					
				else{
					//有客服时回复
					foreach($kfuser as $u)
						{
							$kf[]=$u['uid'];
						}
                  	// print_r($kf);
					$kfid=$kf[array_rand($kf)];
                  
					$tips=' 工号：'.$kfid."为您服务,请问有什么需要我帮您的呢？\n\n如果您觉得不需要人工服务，请发送：【退出】";	
					//设定客服状态为锁定服务状态
					
					$datakf=array('kfzt'=>2,'formoid'=>$user['from_user']);
					pdo_update('kf_kfuser', $datakf,array('uid'=>$kfid));
					$_SESSION['kfid']=$kfid;
					$message['type']='text';
					$message['content']="用户:".$user['nickname']."请求您人工帮助,快给他打招呼吧\n如果回复内容出现其他系统自动回复，非提示回户用户成功！\n请重新发送：[ 客服 ]  再发送 [ 客服管理 ] \n 就能正常给用户回复内容了。 ";
					$kfusr = pdo_fetch("SELECT * FROM ".tablename('kf_kfuser')." WHERE weid = '{$_W['weid']}' AND uid='{$kfid}'   LIMIT 1 ");
					//print_r($kfuser.'OID:'.$kfusr['oid']);
					$reinfo=gjjk_send($message,$kfusr['oid']);
					$workdata=array('workid'=>$workid,'oid'=>$this->message['from'],'kfid'=>$kfid,'status'=>0,'weid'=>$_W['weid'],'ctime'=>TIMESTAMP);
					
					}
				pdo_insert('kf_work', $workdata);
				
				}
				//END高级权限号操作
				
				return $this->respText('亲爱的'.$user['nickname'].$tips);
			}
				
			if($key=='退出'||($key=='再见'&&!$kfuid))
			{
				if($_SESSION['RG']){
					$datalog['lasttime']=TIMESTAMP;
					$datalog['status']=0;
					$status=pdo_update('kf_kflog', $datalog,array('oid'=>$this->message['from']));
					
				}else{
					$status=1;
					}
				if($status){
					
					//更新客服的状态为在线非锁定(当然暂时只有高级号才有这操作)
					if($_SESSION['RG']&&!$kfuid){
						//不是客服才执行退出命令
						pdo_update('kf_kfuser', array('kfzt'=>1,'formoid'=>0),array('uid'=>$_SESSION['kfid']));
						$kfusr = pdo_fetch("SELECT * FROM ".tablename('kf_kfuser')." WHERE weid = '{$_W['weid']}' AND uid='{$_SESSION['kfid']}'   LIMIT 1 ");
						gjjk_send($message,$kfusr['oid']);
					
					}
				
					$this->endContext();
				
					return $this->respText($row['send_tips']);
				}else{
                  	//$this->endContext();
                	return $this->respText('退出操作失败,重新发送[退出]');
                }
		
			}
			
			//高级帐号才执行此操作
			//以下执行的是进入人工或者客服管理模式后，用户间发送消息处理
			if($_SESSION['kfid']){
				$kfusr = pdo_fetch("SELECT * FROM ".tablename('kf_kfuser')." WHERE weid = '{$_W['weid']}' AND uid='{$_SESSION['kfid']}'   LIMIT 1 ");
				$usr=pdo_fetch("SELECT nickname,id,from_user FROM ".tablename('fans')." WHERE weid = '{$_W['weid']}' AND from_user='{$kfusr['formoid']}'   LIMIT 1 ");
				if($kfusr['uid']==$user['id']){
					//如果发来消息的OID和客服OID相等就为客服人员发送的消息
					if($kfusr['formoid']){
						if(empty($_SESSION['workid'])){
						$workid=pdo_fetch("SELECT workid FROM ".tablename('kf_work')." WHERE oid='{$kfusr['formoid']}' AND  weid = '{$_W['weid']}' ORDER BY ctime DESC  LIMIT 1 ");
						$_SESSION['workid']=$workid['workid'];
						}
						$message['content']=$kfusr['nickname']."说:".$message['content'];
						$reinfo=gjjk_send($message,$kfusr['formoid']);
						$ts='发送给用户:'.$usr['nickname'].'的消息>';
						//更新工单状态为受理
						pdo_update('kf_work',array('status'=>1),array('workid'=>$_SESSION['workid']));
						
					}else{
						//如果暂时没有客户咨询，回复
						$ts='暂时未有客户咨询，请等待……';
					}				
					//如果发送成功就插入客服消息状态到历史消息记录
					$histroy=array(
						'weid' => $_W['weid'],
						'module' => $kfusr['nickname'].'工号'.$kfusr['uid'],
						'from_user' => $kfusr['formoid'],
						'rid' => intval($this->params['rule']),
						'kid' => $this->keyword['id'],
						'type' => 'wxlkf',
						'createtime' => $this->message['time'],
						);
					$kfmessage=array('workid'=>$_SESSION['workid'],'oid'=>$kfusr['formoid'],'type'=>'kf','messagecat'=>$this->message['type'],ctime=>$this->message['time'],'kfid'=>$kfusr['uid'],'weid' => $_W['weid']);
					
				}else{
				//用户发送给客服的消息	
					$message['content']=$user['nickname']."说:".$message['content'];
					$reinfo=gjjk_send($message,$kfusr['oid']);
					$ts='发送给客服:'.$kfusr['uid'].'的消息>';
					$kfmessage=array('workid'=>$_SESSION['workid'],'oid'=>$this->message['from'],'type'=>'usr','messagecat'=>$this->message['type'],ctime=>$this->message['time'],'kfid'=>$kfusr['uid'],'weid'=>$_W['weid']);
				}
			
				//转化消息结口发送消息结果。
				if($reinfo){
					$info=json_decode($reinfo['content'], true);
					$message2=account_weixin_code($info['errcode']);
					$msg=array('ret'=>$info['errcode'],'message'=>$message2);
				
					if(!$info['errcode']){
						//$msg=array('ret'=>0,'message'=>'成功');
						//转化其他消息类（非文本）型内容
						switch ($this->message['type']) {
							case 'image':
							$content = $this->message['picurl'];
							break;
							case 'location':
							$content = iserializer(array('x' => $this->message['location_x'], 'y' => $this->message['location_y']));
							break;
							case 'link':
							$content = iserializer(array('title' => $this->message['title'], 'description' => $this->message['description'], 'link' => $this->message['link']));
							break;
							case 'event':
							$content = iserializer(array('event' => $this->message['event'], 'key' => $this->message['eventkey']));
							break;
							default:
							$content = $this->message['content'];
						}
						
						$kfmessage['content']=$content;
						pdo_insert('kf_statmessage', $kfmessage);
						//下面为照顾旧版插入客服消息到历史消息记录表。
						if(!empty($histroy)&&is_array($histroy)){
							$histroy['message']=$content;
							pdo_insert('stat_msg_history',$histroy);
						}	
						
					}
				}
				
				
				if($_SESSION['RG']||$_SESSION['kfzt']){
					//用户已经在客服或者人工状态且ＳＥＳＳＩＯＮ未超时回复
					//print($ts.$msg['message']);
					return $this->respText($ts.$msg['message']);
				}
				
				//增加判断超时，执行用户在线信息.
				if ((!empty($row['timeout']) && $row['timeout'] > 0 && TIMESTAMP - $zt['lasttime'] >= $row['timeout'])) {
					$this->endContext();
					$datalog['lasttime']=TIMESTAMP;
					$datalog['status']=0;
					$status=pdo_update('kf_kflog', $datalog,array('oid'=>$this->message['from']));
					if($status){
						//更新客服的状态为在线非锁定(当然暂时只有高级号才有这操作)
						pdo_update('kf_kfuser', array('kfzt'=>1,'formoid'=>0),array('uid'=>$_SESSION['kfid']));
					}
					return $this->respText('由于您长时间未操作，请重新发送：客服  重新进入系统！');
				}
			
				//用户超时
				return $this->respText('请发送[人工]或者[客服管理]后再进行操作');
				
			}//用户在客服或者人工服务状态下操作完成．
			//如果未进入该状态．发送提示为以下操作
			//如果直接输入客服，需要作的应答．
			//print_r($kfwenda);
			$tips2=$this->kfbefore($kfuid['uid'],$user,$kfwenda,$row['wechattype'],$row['timeout']);
			if(is_array($tips2)){
				return $this->respNews($tips2);
			}else{
				return $this->respText($tips2);
			}			
			
		}
	}	
	
	
	public function kfbefore($kfuid,$reg=1,$kfwenda='',$chattype=1,$timeout='7200') {
			global $_W;
		 	if(!$reg){
			 //判断用户注册否
				if($chattype){
					$user=gj_getuserinfo($this->message['from']);
					if(!empty($user['from_user'])){
							$adduser=pdo_insert('fans', $user);
						}
					if(!$adduser){	
						$tips='未找到您的信息，请点此：<a target="_blank" href="'. create_url('mobile/module', array('name' => 'fans', 'do' => 'profile', 'weid'=>$_W['weid'])).'">会员信息登记</a>您的信息';
						}else{
						$tips=	"\n找人工，请发送：【人工】\n如果不想找人工请发送：【退出】";
							}
						
					}else{
						$tips='您还未绑定微信号，请发送：[绑定]，后再来[人工客服]。谢谢！';
					
					}
					return $tips;
					
				}
				//判断是否是客服人员
					if($kfuid){
						$kfinfo="\n客服人员，请发送：【客服管理】";
						if(!$this->inContext){$this->beginContext($timeout);}
					}else{
						$kfinfo="\n找人工，请发送：【人工】\n如果不想找人工请发送：【退出】";
						if(!$this->inContext){$this->beginContext($timeout);}
					}
					
					//如果客服自动应答有数据
					if(!empty($kfwenda)&&is_array($kfwenda)){
						$rid = $this->rule;
						$sql = "SELECT * FROM " . tablename('kf') . " WHERE `rid`=:rid LIMIT 1";
						$row = pdo_fetch($sql, array(':rid' => $rid));
						$kfnews = array();
						$kfnews[0]=array(
							'title'=>'帮助菜单点击进入[]'.$kfinfo,
							'description'=>'在线帮助，点此进入查询相关帮助说明',
							'picurl' => $_W['attachurl'] . trim($row['picture'], '/'),
							'url'=>create_url('mobile/channel', array('name' => 'list', 'cid' => $row['cateid'],'weid'=>$_W['weid'])),
							);/**/
						foreach ($kfwenda as $v)
							{
								$kfmenu = array();
								$kfmenu['title'] = $v['displayorder'].':'.$v['name'];
								$kfcmenu['description'] = $v['description'];
								$kfmenu['picurl'] = '';
								$kfmenu['url'] =create_url('mobile/channel', array('name' => 'list', 'cid' => $v['id'],'weid'=>$_W['weid']));
								$kfnews[] = $kfmenu;
							}
						$tips=$kfnews;		
					}else{
						//无客服自动应答
						$tips=$kfinfo;	
					}
					
				
		
		return $tips;
	}
	
}
