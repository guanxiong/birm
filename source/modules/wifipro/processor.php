<?php
/**
 * wifi营销
 *
 * @author 珊瑚海
 * @url 
 * [WDL] Copyright (c) 2013 B2CTUI.COM
 */
defined('IN_IA') or exit('Access Denied');

class WifiproModuleProcessor extends WeModuleProcessor {    
	
	public $table_router = 'wifipro_info';
	public $table_reply = 'wifipro_reply';
	public $table_authlist = 'wifipro_authentication';
	public $error_info=array(
			'100003'=>'节点不存在',
			'100004'=>'新节点已存在',
			'100005'=>'节点id不存在',
			'100006'=>'节点id和节点名不符',
			'100007'=>'节点id无效',
			'200001'=>'accessID错误',
			'200002'=>'accessKey错误',
			'200003'=>'该用户无该节点操作权限',
			'200010'=>'Json请求消息错误',
			'200011'=>'微信接口传入参数错误',
			'300006'=>'登陆验证码错误',
			'300007'=>'登陆验证码失效',
			'300008'=>'三方app或者随机验证码地址错误',
			'300009'=>'三方app获取随机数参数错误',
			'310002'=>'Json参数accessID为空',
			'310003'=>'Json参数accessKey为空',
			'310004'=>'Json参数node_name为空',
			'310005'=>'Json参数new_node_name格式不合法',
			'310006'=>'Json参数description为空',
			'310007'=>'Json参数email格式不合法',
			'310008'=>'Json参数login_page为空',
			'310009'=>'Json参数portal_page为空',
			'310010'=>'Json参数not_dev_url为空',
			'310011'=>'Json参数welogin不合法',
			'310012'=>'Json参数wereject不合法',
			'310013'=>'Json参数probation_time不合法',
			'310014'=>'Json参数logintimeout不合法',
			'310015'=>'Json参数isportal不合法',
			'310016'=>'Json参数notwww不合法',
			'310017'=>'Json参数logintype不合法',
			'310018'=>'Json参数phone不合法',
			'310019'=>'Json参数whiltlist不合法',
			'310020'=>'Json参数type错误',
			'310021'=>'Json参数probation_url不合法',				
	);
	
	
	public function respond() {   	
    	
    	global $_W;
    	$rid = $this->rule;
    	$weid=$_W['weid'];
    	$sql = "SELECT * FROM " . tablename($this->table_reply) . " WHERE `rid`=:rid LIMIT 1";
    	$row = pdo_fetch($sql, array(':rid' => $rid));
    	$routerid=$row['routerid'];
    	if (empty($routerid)) {
    		return $this->respText("请确认您操作的路由器已经维护") ;
    	}
    	$rowrouter=pdo_fetch("SELECT * FROM " . tablename($this->table_router) ." WHERE `id`={$routerid} ");
    	if (empty($rowrouter)) {
    		return $this->respText("指定关联路由器已删除！") ;
    	}
    	if($rowrouter['status']==0)
    	{
    		return $this->respText("该该路由器未启用，请后台设置为启用！") ;
    	}
    	
    	$iurl=$rowrouter['iurl'];
    	$appid=$rowrouter['appid'];
    	$appkey=$rowrouter['appkey'];
    	$nodeid=$rowrouter['nodeid'];
    	$openid=$this->message['from'];
    	if(empty($iurl)||empty($appid)||empty($appkey)||empty($nodeid))
    	{
    		return $this->respText("路由器配置的接口参数为空请设置后重试！") ;
    	}
    	
    	$interfaceinfo=$this->GetRouterInter($iurl,$appid,$appkey,$nodeid,$openid);
    	$authdata=array(
    			'routerid'=>$routerid,
    			'fromuser'=>$openid,
    			'createtime'=>time(),
    			'weid'=>$weid,
    	);
    	if($interfaceinfo['flag']==1)//接受认证
    	{
    		
    		$urlText="<a href='{$interfaceinfo['result']}' >直接点击</a>";
    		$row['oktip']=str_replace('{url}',$urlText , $row['oktip']);
    		$row['oktip']=str_replace('{key}',$interfaceinfo['token'] , $row['oktip']);
    		$authdata['result']=1;
    		$authdata['resultmemo']='认证链接:'.$interfaceinfo['result'].' 验证码:'.$interfaceinfo['token'];
    		pdo_insert($this->table_authlist,$authdata);
    		return $this->respText($row['oktip']);
    	}
    	else 
    	{
    		$authdata['result']=0;
    		$authdata['resultmemo']=$interfaceinfo['result'];    		
    		pdo_insert($this->table_authlist,$authdata);
    		return $this->respText($interfaceinfo['result'].$interfaceinfo['debug']);
    	}
    	
    	
   }

   
   
   private function GetRouterInter($iur,$appid,$appkey,$nodeid,$openid)
   {
   	$arrResult=array(
   			'flag'=>0,
   			'result'=>'',
   			'token'=>'',
   			'debug'=>'',
   	);  	
   	
   	$url="{$iur}?appId={$appid}&appKey={$appkey}&nodeId={$nodeid}&openId={$openid}";  
   	
   	$rsp = ihttp_get($url);
   	$dat=$rsp['content'];
   	
   	//获取接口情况
   	if(!empty($dat))
   	{
   		$result=json_decode($dat,true) ;
   		if(!is_array($result))
   		{
   			$arrResult['flag']=0;
   			$arrResult['result']='接口返回参数不是JSON格式';
   			$arrResult['debug']=$dat;
   		
   		}
   		else
   		{
   			if($result['result']=='ok')//连接成功
   			{
   				$arrResult['flag']=1;
   				$arrResult['result']=$result['url'];
   				$arrResult['token']=$result['token'];
   			}
   			elseif ($result['result']=='error')//捕捉到失败
   			{
   				$arrResult['flag']=-1;
   				$arrResult['result']=$this->errcodetrans($result['errorCode']);
   			}
   			else
   			{
   				$arrResult['flag']=0;
   				$arrResult['result']='返回状态异常，请联系路由器接口提供方';
   			}
   		}
   	}
 	else 
   	{
   		$arrResult['flag']=0;
   		$arrResult['result']='接口无响应';   	
   		$arrResult['debug']=$url;
   	}

   	return $arrResult;
   	
   }
   
   private function  errcodetrans($errcode)
   {
   	if(array_key_exists("$errcode", $this->error_info)) 
   	{
   		$rsp="错误代码[{$errcode}] 原因:".$this->error_info[$errcode];
   		
   	}
   	else
   	{
   		$rsp="错误代码[{$errcode}],接口返回的错误代码在预期定义以外，请联系路由器厂家确认原因";
   	}
   	return $rsp;
   }
 
}

