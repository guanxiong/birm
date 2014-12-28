<?php
/**
 * 微客服
 *
 * @author BIN MODIFIED BY大路货 QQ:792454007
 * @url 
 */

defined('IN_IA') or exit('Access Denied');
include 'wechat.class.php';
class LxywecsModuleProcessor extends WeModuleProcessor
{
	private $_mError;
	private $_mWeObj;
	private $_times = 0;
	private  $cstable='lxy_wecs';
	private $modulename='lxywecs';
	
	public function respond()
	{
		global $_W;
		$weid=$_W['weid'];
		$fromuser=$this->message['from'];
		$frommessage=$this->message['content'];
		
		$reply = '';
		if(!$this->inContext)
		{
			$this->flushCustomer();//释放空余客服
			//检查客服表中客户=来源id并且繁忙
			$itemkefu = pdo_fetch("SELECT * FROM ".tablename($this->cstable)." WHERE weid=:weid and openid=:openid and busy=2", array(':weid' => $weid,':openid'=>$fromuser));
			//如果来源是客户
			if(empty($itemkefu))
				{
				$nickname = $this->allocation();
				if($nickname)
				{
					//分配成功
					$reply = "您好，系统已呼叫客服{$nickname}为您服务，从现在起您发送的所有消息系统将直接发送至客服，如需退出服务，请回复<退出客服>";
				}
				else
				{
					$reply = $this->_mError;
				}
			}
			elseif($itemkefu)
			{
				//来源是客服第一次回话
				
				$reply= $this->joincall();
			}
		}
		else
		{
			if($frommessage=='退出客服')
			{
				//释放客服
				$this->disallocation();
				$reply = "您已经退出本次客服对话！";
			}
			else
			{
				
				$reply = $this->dispatchMessage();
			}
		}
		return $this->respText($reply);
	}
	
	//将空余客服释放
	private function flushCustomer()
	{
		global $_W;
		
		$sql = 'SELECT from_user FROM ' . tablename('sessions') . ' WHERE weid=:weid and data='."''";		
		$params = array();
		$params[':weid'] = $_W['weid'];
		$row = pdo_fetchall($sql, $params);//取出已经过期的且data字段不为空的session记录
		foreach($row as $r)
		{
			//将用户会话已经超时的相关客服的工作状态设为接听状态
			$sql = 'Update ' . tablename($this->cstable) . 'set busy=1,csid=\'\' WHERE csid=:csid and weid=:weid and busy=2';
			pdo_fetch($sql,array(':csid'=>$r['from_user'],':weid'=>$_W['weid']));
		}
		//将session表中相关的记录对应也置为使用过状态
		//$sql = 'update ' . tablename('sessions') . ' set data=\'\' WHERE `expiretime`<=:time and data!='."''";
		//pdo_fetch($sql, $params);
	}
	
	//分配客服
	private function allocation()
	{
		global $_W;
		
		//检查是否是客服自身申请客服
		$sql = 'select * from '.tablename($this->cstable).' WHERE openid=:openid and weid=:weid';
		$kefu = pdo_fetch($sql,array(':weid'=>$_W['weid'],':openid'=>$this->message['from']));
		if($kefu)
		{
			$this->_mError = "客服自己本身不能申请客服服务!";
			return false;
		}
		//查找空闲客服
		
		$sql = 'select * from '.tablename($this->cstable)." WHERE busy=1 and weid=:weid and UNIX_TIMESTAMP()<unix_timestamp(CONCAT(date_format(CURDATE(),'%Y-%m-%d'),' ',end_time)) and UNIX_TIMESTAMP()>unix_timestamp(CONCAT(date_format(CURDATE(),'%Y-%m-%d'),' ',start_time)) ";
		$kefu = pdo_fetch($sql,array(':weid'=>$_W['weid']));
		$kefuopenid=$kefu['openid'];
		$kehuopenid=$this->message['from'];
		if($kefu)
		{
			//有客服空闲情况
			$this->_times = 0;
			//获取客服昵称
			$kefunickname = $this->getNickNmae($kefuopenid);
			$this->_times = 0;
			//获取客户昵称
			$kehunickname = $this->getNickNmae($kehuopenid);
			
			//取不到两者任意一个昵称，然返回分配错误
			if((!$kefunickname)||(!$kehunickname))
			{
				return false;
			}
			
			$replykey = pdo_fetch("SELECT * FROM ".tablename('rule_keyword')." WHERE module='{$this->modulename}' and  weid = '{$_W['weid']}' and status=1 ");
			if(!$this->sendMessage($kefuopenid,"客户{$kehunickname}正在请求客服服务，请尽快响应，回复<{$replykey['content']}>进入对话"))
				return false;
			else
			{
				$this->beginContext();

	            /*
				$row = array();
				$row['sid'] = md5($kefuopenid . $this->message['to']);
				$row['weid'] = WeSession::$weid;
				$row['from_user'] = $kefuopenid;
				$row['data'] = '__contextmodule|s:7:"lxywecs";__contextrule|s:3:"145";__contextexpire|i:1394026511;__contextpriority|i:0;';
				$row['expiretime'] = TIMESTAMP + WeSession::$expire;
				pdo_insert('sessions', $row, true);
				*/
				$sql = 'Update ' . tablename($this->cstable) . 'set busy=2,csid=:csid WHERE openid=:openid and weid=:weid';
				pdo_fetch($sql,array(':openid'=>$kefuopenid,':weid'=>$_W['weid'],':csid'=>$kehuopenid));
			}
			return $kefunickname;
		}
		$this->_mError = "非客服工作时间范围或客服繁忙，请稍后再试!";
		return false;
	}

	private function joincall()
	{
		global $_W;
		$kefuopenid=$this->message['from'];
		//查找空闲客服
		$sql = 'select * from '.tablename($this->cstable).' WHERE busy=2 and weid=:weid and openid=:openid';
		$kefu = pdo_fetch($sql,array(':weid'=>$_W['weid'],':openid'=>$kefuopenid));
		$kehuopenid=$kefu['csid'];

		if($kefu)
		{

			
			$this->_times = 0;
			//获取客服昵称
			$kefunickname = $this->getNickNmae($kefuopenid);
			$this->_times = 0;
			//获取客户昵称
			$kehunickname = $this->getNickNmae($kehuopenid);
				
			//取不到两者任意一个昵称，然返回分配错误
			if((!$kefunickname)||(!$kehunickname))
			{
				return false;
			}

			
			if(!$this->sendMessage($kefuopenid,"您已响应{$kehunickname}的客服请求，关闭当前用户会话请回复<退出客服>"))
				return false;
			else
			{
				$this->beginContext();
				$this->sendMessage($kehuopenid,"谢谢您的等待，客服{$kefunickname}已接听您的请求！");
			}
			return '';
		}
		$this->_mError = "客服繁忙，请稍后再试!";
		return false;
	}
	
	//释放客服资源
	private function disallocation()
	{
		global $_W;
		$sql = 'select * from '.tablename($this->cstable).' WHERE busy=2 and weid=:weid and (openid=:from or csid=:from)';
		$kefu = pdo_fetch($sql,array(':weid'=>$_W['weid'],':from'=>$this->message['from']));
		$this->endContext();
		//$openid置入客服openid
		$openid = $kefu['openid'];
		if($openid == $this->message['from'])
		{
			//发消息过来的是客服，将$openid置为客户
			$openid = $kefu['csid'];
		}
		$this->sendMessage($openid ,"您已经退出本次客服对话！");
		//删除sesssion
		$sql = 'delete from ' . tablename('sessions') . ' WHERE weid=:weid and from_user=:openid';
		pdo_fetch($sql,array(':weid'=>$_W['weid'],':openid'=>$openid));
		//更新客服状态为在线状态
		$sql = 'Update ' . tablename($this->cstable) . 'set busy=1,csid=\'\' WHERE (openid=:openid or csid=:openid) and weid=:weid';
		pdo_fetch($sql,array(':openid'=>$openid,':weid'=>$_W['weid']));
	}
	
	private function dispatchMessage()
	{
		global $_W;
		//找到建立连接的客服信息
		$sendid=$this->message['from'];
		$sql = 'select * from '.tablename($this->cstable).' WHERE busy=2 and weid=:weid and (openid=:from or csid=:from)';
		$kefu = pdo_fetch($sql,array(':weid'=>$_W['weid'],':from'=>$sendid));
		
		if($kefu)
		{
			//置入客服openid
			$openid = $kefu['openid'];
			if($openid == $sendid)
			{
				$openid = $kefu['csid'];
			}
			$type = $this->message['type'];
			$content = '';
			if($type == 'text')
			{
				$content = $this->message['content'];
			}
			else
			{
				$content = $this->message['mediaid'];
			}
			if(!$this->sendMessage($openid,$content,$type))
			{
				$this->disallocation();
				return '由于微信的限制，客服系统无法服务';
			}
			
			$this->refreshContext();
		}
		else
		{
			$this->disallocation();
			return '由于未知错误，您已经退出了客服系统，请与管理员联系';
		}
	}
	
	private function sendMessage($openid,$content,$type='text')
	{
		$content_type = 'content';
		if($type!='text')
		{
			$content_type = 'media_id';
		}
		$weObj = $this->_mWeObj!=null?$this->_mWeObj:$this->getWeObj();
		$msg = array();
		$msg['touser'] = $openid;
		$msg['msgtype'] = $type;
		$msg[$type] = array($content_type=>$content);
		$ret = $weObj->sendCustomMessage($msg);
		if(!$ret&&($weObj->errCode == '40001' || $weObj->errCode == '42001'|| $weObj->errCode == '41001')&&$this->_times<3)
		{
			echo "12345";
			$this->_times++;
			$this->updateAccessToken();
			return $this->sendMessage($openid,$content,$type);
		}
		else
		{
			return true;
		}
		$this->_mError="由于微信的限制，目前无法提供客服服务";
		return false;
	}
	
	private function getWeObj()
	{
		if($this->_mWeObj)
			return $this->_mWeObj;
		global $_W;
		$sql = 'select `key`,`secret`,`access_token` from '.tablename('wechats').' WHERE weid=:weid';
		$data = pdo_fetch($sql,array(':weid'=>$_W['weid']));
		$options = array('appid'=>$data['key'],'appsecret'=>$data['secret'],'access_token'=>$data['access_token']);
		$weObj = new Wechat($options);
		$this->_mWeObj = $weObj;
		return $weObj;
	}
	
	private function updateAccessToken()
	{
		global $_W;
		$weObj = $this->_mWeObj!=null?$this->_mWeObj:$this->getWeObj();
		$access_token = $weObj->checkAuth();
		if($access_token)
		{
			$sql = 'update '.tablename('wechats').' set access_token=:access_token WHERE weid=:weid';
			pdo_fetch($sql,array(':weid'=>$_W['weid'],':access_token'=>$access_token));
		}
	}
	
	private function getNickNmae($openid)
	{
		global $_W;
		$sql = 'select nickname from '.tablename('fans').' WHERE from_user=:openid and weid=:weid';
		$data = pdo_fetch($sql,array(':weid'=>$_W['weid'],':openid'=>$openid));
		if($data)
		{
			if($data['nickname']!='')
			{
				return $data['nickname'];
			}
			else
			{
				return $this->updateNickName($openid);
			}
		}
		$this->_mError = "未知错误!";
		return false;
		
	}
	
	private function updateNickName($openid)
	{
		global $_W;
		$weObj = $this->_mWeObj!=null?$this->_mWeObj:$this->getWeObj();
		$userinfo = $weObj->getUserInfo($openid);
		if($userinfo)
		{
			$nickname = $userinfo['nickname'];
			$sql = 'update '.tablename('fans').' set nickname=:nickname WHERE from_user=:openid and weid=:weid';
			pdo_fetch($sql,array(':weid'=>$_W['weid'],':openid'=>$openid,':nickname'=>$nickname));
			return $nickname;
		}
		if(($weObj->errCode == '40001' || $weObj->errCode == '42001' || $weObj->errCode == '41001')&&$this->_times<3)
		{
			$this->_times++;
			$this->updateAccessToken();
			return $this->updateNickName($openid);
		}
		$this->_mError="由于微信的限制，目前无法提供客服服务";
		return false;
	}
	
	
	
	
}