<?php
/**08x8-b17v-7028
 * meepo找老乡模块微站定义
 *
 * @author meepo_zam
 * @url http://bbs.we7.cc/
 */
defined('IN_IA') or exit('Access Denied');
define('RES', './source/modules/zam_findlx/template/mobile/');
class Zam_findlxModuleSite extends WeModuleSite {
	/*****
	**列表
	*/
    public function doMobilelist(){
	    global $_GPC, $_W;
		$weid = $_W['weid'];
		if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') ) {
			
	    }else{
		  die('请通过微信打开！');
		}
	
		$openid = $_W['fans']['from_user'];
		 $item = $this->getuserinfo();
		 $wcfg = $this->module['config'];
		if(empty($item['avatar'])){
		  die('请先关注本平台公众号，回复找老乡！');
		}
		$sql = "SELECT * FROM ".tablename('zam_userinfo')." WHERE  weid=:weid AND username!=:username ORDER BY createtime DESC LIMIT 0,5";
		$all = pdo_fetchall($sql,array(':weid'=>$weid,':username'=>''));
		 include $this->template('list');

	}
	/*
	*聊天窗
	**/
	public function doMobilechat(){
	     global $_GPC, $_W;
		 $weid = $_W['weid'];
		 $openid = $_W['fans']['from_user'];
		 if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') ) {
			
	    }else{
		  die('请通过微信打开！');
		}
		 $item = $this->getuserinfo();
		 if(empty($item['avatar'])){
		  die('请先关注本平台公众号，请先在微信聊天窗口回复找老乡！');
		 }
		 $toopenid = $_GPC['toopenid'];
		 $sql = "SELECT * FROM ".tablename('zam_userinfo')." WHERE openid=:openid AND weid=:weid";
		$res = pdo_fetch($sql,array(":openid"=>$toopenid,':weid'=>$weid));
		$msgid = $res['msgid'];
        $fakeid = $res['fakeid'];
		 if(empty($msgid)|| empty($fakeid) || empty($toopenid)){
		    die('参数错误，请先在微信聊天窗口回复找老乡！');
		 }
		 if(!empty($_POST)){
		                 $quickreplyid = $_POST['msgid'];
						 $fakeid2 = $_POST['fakeid'];
						 $content = $_POST['content'];
						 $openid2 = $_POST['toopenid'];
						 //print_r($_POST);
						 if(empty($quickreplyid)|| empty($fakeid2) || empty($content)){
						     //message('参数错误，请重新回复找老乡');
							 message('参数错误，请重新回复找老乡', referer());
						 }else{
							 if($res['isblacklist']==1 ){
							    message('发送失败，对方已被管理员拉入黑名单',referer(),error);
							 }
							 if($item['isblacklist']==1 ){
							   message('发送失败，你已被管理员拉入黑名单',referer(),error);
							 }
							 
							   
										  //load()->func('communication');
										  $cfg = pdo_fetch("SELECT * FROM ".tablename('zam_cookie')." WHERE weid=:weid",array('weid'=>$weid));
																$token = $cfg['token'];
																$cookie = iunserializer($cfg['cookie']);
										  $loginurl = 'https://mp.weixin.qq.com/cgi-bin/singlesend?t=ajax-response&f=json&token='.$token.'&lang=zh_CN';
										  $refer = 'https://mp.weixin.qq.com/cgi-bin/message?t=message/list&count=20&day=7&token='.$token.'&lang=zh_CN';
										  $post = array(
											'token' => $token,
											'lang' =>'zh_CN',
											'f' => 'json',
											'ajax' => '1',	
											'random' => '0.08272588928230107',
											'mask' => 'false',
											  'tofakeid' =>$fakeid2,
											'imgcode' => '',
											   'type' =>'1',
											'content' =>$item['username']."发来消息说: \n".$content."\n温馨提示：\n直接回复【@老乡姓名@任意内容】即可回复老乡的信息！",
											  'quickreplyid'=>$quickreplyid
										  );
					
										 $response = ihttp_request($loginurl, $post, array('CURLOPT_REFERER' =>$refer, 'CURLOPT_COOKIE' =>$cookie));	
										 $data = json_decode($response['content'], true);
										 if ($data['base_resp']['ret'] == 0 && $data['base_resp']['err_msg']=='ok') {
											 pdo_insert('zam_chatlog',array('openid'=>$openid,'toopenid'=>$openid2,'content'=>$content,'username'=>$res['username'],'tousername'=>$item['username'],'createtime'=>time(),'weid'=>$weid));

											 pdo_update('zam_userinfo',array('chattime'=>$item['chattime']+1),array('openid'=>$openid,'weid'=>$weid));
										      message('发送成功',referer(),sucess);
										 }else{
                                            $this->monilogin();
										    message('发送失败，可能是对方长时间未在线，已经被提出对话，请私信其微信聊天窗回复找老乡',referer(),error);
										 
										 }
				
									
						 
						 
			 }
		 }
	     include $this->template('chat');
	}
    /*
	*查找
	**/
	public function doMobilecheck(){
	     global $_GPC, $_W;
		 $weid = $_W['weid'];
		 $openid = $_W['fans']['from_user'];
		 $item = $this->getuserinfo();
		 if(empty($item['avatar'])){
		  die('请先关注本平台公众号，请先在微信聊天窗口回复找老乡！');
		 }
		
		 if(empty($_POST)){
		      echo '';
		 }else{
			$pindex = max(1, intval($_GPC['offset']));
			
			 $psize = 5;
			    $condition .= "weid=:weid AND username!=:username";
				$paras[':weid'] = $weid;
				$paras[':username'] = '';
				//$paras[':endtime'] = $endtime;
		    if(!empty($_GPC['keyword'])){
				$condition .= " AND nickname LIKE '%{$item['nickname']}%'";
			}
			
			if($_GPC['region'] == '1') {
				$condition .= " AND province = '{$item['province']}'";
			}elseif($_GPC['region'] == '2'){
			    $condition .= " AND province = '{$item['province']}' AND city = '{$item['city']}'";
			}elseif($_GPC['region'] == '3'){
			    $condition .= " AND province = '{$item['province']}' AND city = '{$item['city']}' AND area = '{$item['area']}'";
			}
			if(!empty($_GPC['price'])){
				 $condition .= " AND sex = '{$_GPC['price']}'";
			}
			if($_GPC['order'] == '1') {
				$condition .= " ORDER BY jointime DESC";
			}elseif($_GPC['order'] == '2'){
			    $condition .= "  ORDER BY chattime DESC";
			}else{
			   $condition .= " ORDER BY createtime DESC";
			}
			$sql = "select * from ".tablename('zam_userinfo')." where $condition  "
					. "LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
			$stores = pdo_fetchall($sql,$paras);
			$result_str = '';
					foreach($stores as $row) {
						$result_str .='<div class="linked"><div class="item">';
			            $result_str .='<a href="'.$this->createMobileUrl('chat',array('toopenid'=>$row['openid'])).'">';
			            $result_str .='<div class="li-item">
							  <div class="img"><img src="'.$_W['attachurl'].$row['avatar'].'"></div>
							  <div class="w_100">
								 <h3>姓名：'.$row['username'].'</h3>
								 <div class="intro">
									 <p>地址：'.$row['province'].$row['city'].$row['area'].'</p>
									 <p>专业班级：'.$row['banji'].'</p>
									 <p>联系方式：'.$row['phone'].'</p>
									<p>注册时间：'. date('Y-m-d', $row[createtime]).'</p>';	
									
									 
								$result_str .=' </div> </div></div></a></div></div>';
					}	
					if ($result_str == '' || empty($stores)) {
						echo '';
					} else {
						echo $result_str;
					}
		 }
	    
	}

	/*
	*聊天提交
	*/
    public function doMobilechatajax(){
	     global $_GPC, $_W;
		 $weid = $_W['weid'];
		 $openid = $_W['fans']['from_user'];
		 $item = $this->getuserinfo();
		 if(empty($item['avatar'])){
		  die('请先关注本平台公众号，请先在微信聊天窗口回复找老乡！');
		 }
		 $msgid = $_POST['msgid'];
		 $fakeid = $_POST['fakeid'];
		 $content = $_POST['content'];
		 if(empty($msgid)|| empty($fakeid) || empty($content)){
		   echo '0';
		 }else{
			   
		                  //load()->func('communication');
						  $cfg = pdo_fetch("SELECT * FROM ".tablename('zam_cookie')." WHERE weid=:weid",array('weid'=>$weid));
		                                        $token = $cfg['token'];
		                                        $cookie = iunserializer($cfg['cookie']);
												
												
													
                          $loginurl = 'https://mp.weixin.qq.com/cgi-bin/singlesend?t=ajax-response&f=json&token='.$token.'&lang=zh_CN';
												//$loginurl = 'https://mp.weixin.qq.com/cgi-bin/login?lang=zh_CN';
					      //$post = 'token='.$token.'&lang=zh_CN&f=json&ajax=1&random=0.08272588928230107&mask=false&tofakeid='.$fakeid.'&imgcode=&type=1&content='.$content.'&quickreplyid='.$msgid;
					      $refer = 'https://mp.weixin.qq.com/cgi-bin/message?t=message/list&count=20&day=7&token='.$token.'&lang=zh_CN';	$post = array(
							'token' => $token,
							'lang' =>'zh_CN',
							'f' => 'json',
							'ajax' => '1',	
							'random' => '0.08272588928230107',
							'mask' => 'false',
							  'tofakeid' =>$fakeid,
							'imgcode' => '',
							   'type' =>'1',
							'content' => $content,
							  'quickreplyid'=>$msgid
						  );
	
	                     $response = ihttp_request($loginurl, $post, array('CURLOPT_REFERER' =>$refer, 'CURLOPT_COOKIE' => $cookie));		
				        echo '1';
					
		 
		 
		 }
		 
	     
	}
	
	/*****
	**注册
	*/
	public function doMobileregister(){
	    global $_GPC, $_W;
		$weid = $_W['weid'];
		$openid = $_W['fans']['from_user'];
        $item = $this->getuserinfo();
        if(empty($item['avatar'])){
		  die('请先关注本平台公众号，回复找老乡！');
		}
		if(checksubmit('submit')){
			
			$data = array(
				'username' => $_GPC['username'],
				'province' => $_GPC['addr_prov'],
				'city' => $_GPC['addr_city'],
				'area' => $_GPC['addr_area'],
				'phone' => $_GPC['tel'],
				'banji' => $_GPC['banji'],
				'createtime' => TIMESTAMP,
			);
			if (empty($id)) {
					pdo_update('zam_userinfo',$data,array('id'=>$item['id'],'weid'=>$weid));
					message('个人信息保存成功',$this->createMobileUrl('list'),success);
				
			}
		}
		 include $this->template('index');
	}
	private function getuserinfo(){
        global $_W,$_GPC;
		$weid = $_W['weid'];
		$openid = $_W['fans']['from_user'];
		$sql = "SELECT * FROM ".tablename('zam_userinfo')." WHERE openid=:openid AND weid=:weid";
		$res = pdo_fetch($sql,array(":openid"=>$openid,':weid'=>$weid));
		return $res;

  }
  /*
  * 管理注册的人
  */
  public function doWebManage() {
		global $_GPC, $_W;
         $weid = $_W['weid'];
		/**** 0.6 ****/
		checklogin();

		
		$id = intval($_GPC['id']);
		if(!empty($id) || !empty($_GPC['openid'])){
			if($_GPC['switch']==1){
		      pdo_update('zam_userinfo',array('isblacklist'=>1),array('id'=>$id,'openid'=>$_GPC['openid']));
			  message('添加黑名单成功！', $this->createWebUrl('manage', array('page' => $_GPC['page'])),sucess);
			}else{
			   pdo_update('zam_userinfo',array('isblacklist'=>0),array('id'=>$id,'openid'=>$_GPC['openid']));
			  message('解除黑名单成功！', $this->createWebUrl('manage', array('page' => $_GPC['page'])),sucess);
			}
		}
		//$isshow = isset($_GPC['isshow']) ? intval($_GPC['isshow']) : 0;
		if (checksubmit('delete') && !empty($_GPC['select'])) {
			foreach ($_GPC['select'] as $row) {
				$sql = 'DELETE FROM'.tablename('zam_userinfo')." WHERE id=:id AND weid=:weid";
			    pdo_query($sql, array(':id' =>$row,':weid'=>$weid));
			    //pdo_delete('weixin_wall_num',array('num'=>1),array('lastmessageid'=>0));
			
			}
			message('删除成功！', $this->createWebUrl('manage', array('page' => $_GPC['page'])),sucess);
		}
		
		$condition = "AND username!=''";
		
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;
        $total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('zam_userinfo') . " WHERE  weid = '{$weid}' {$condition}");
	    $pager = pagination($total, $pindex, $psize);
		//$wall = pdo_fetch("SELECT id, isshow, rid FROM ".tablename('weixin_wall')." WHERE rid = '{$id}' AND weid = '{$weid}' LIMIT 1");
		$list = pdo_fetchall("SELECT * FROM ".tablename('zam_userinfo')." WHERE  weid = '{$weid}' {$condition} ORDER BY createtime DESC LIMIT ".($pindex - 1) * $psize.",{$psize}");

		

		include $this->template('manage');
	}
	/*
	*管理聊天内容
	*
	*/
	public function doWebnews() {
		global $_GPC, $_W;
         $weid = $_W['weid'];
		/**** 0.6 ****/
		//checklogin();

		if(!empty($_GPC['openid'])){
			if($_GPC['switch']==1){
		      pdo_update('zam_userinfo',array('isblacklist'=>1),array('weid'=>$weid,'openid'=>$_GPC['openid']));
			  message('添加黑名单成功！', $this->createWebUrl('manage', array('page' => $_GPC['page'])),sucess);
			}else{
			   pdo_update('zam_userinfo',array('isblacklist'=>0),array('weid'=>$weid,'openid'=>$_GPC['openid']));
			  message('解除黑名单成功！', $this->createWebUrl('manage', array('page' => $_GPC['page'])),sucess);
			}
		}
		
		//$isshow = isset($_GPC['isshow']) ? intval($_GPC['isshow']) : 0;
		if (checksubmit('delete') && !empty($_GPC['select'])) {
			foreach ($_GPC['select'] as $row) {
				$sql = 'DELETE FROM'.tablename('zam_chatlog')." WHERE id=:id AND weid=:weid";
			    pdo_query($sql, array(':id' =>$row,':weid'=>$weid));
			    //pdo_delete('weixin_wall_num',array('num'=>1),array('lastmessageid'=>0));
			
			}
			message('删除成功！', $this->createWebUrl('news', array('page' => $_GPC['page'])),sucess);
		}
		
		$condition = " ";
		
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;
        $total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('zam_chatlog') . " WHERE  weid = '{$weid}' {$condition}");
	    $pager = pagination($total, $pindex, $psize);
		//$wall = pdo_fetch("SELECT id, isshow, rid FROM ".tablename('weixin_wall')." WHERE rid = '{$id}' AND weid = '{$weid}' LIMIT 1");
		$list = pdo_fetchall("SELECT * FROM ".tablename('zam_chatlog')." WHERE  weid = '{$weid}' {$condition} ORDER BY createtime DESC LIMIT ".($pindex - 1) * $psize.",{$psize}");
		

		include $this->template('managenews');
	}
/*
*
*微信登陆
*/
	public function doWebset(){
	    global $_GPC, $_W;
		$weid = $_W['weid'];
		
		$wechat = $this->module['config'];
		
		$CANSHU = $this->getimgver($wechat['user']);
		if(!empty($_POST)){
            
			$username = $_POST['username'];
			$pwd = $_POST['pwd'];
			$verify = $_POST['verify'];
			$codecookie = $_POST['cookie'];
			$res = $this->login($username,$pwd,$verify,$codecookie);
			if(!$res){
			   message('登录失败，请核实您输入的信息！');
			}else{
				$cfg = pdo_fetch("SELECT * FROM ".tablename('zam_cookie')." WHERE weid=".$weid);
			   if(!empty($cfg)){
			   pdo_update('zam_cookie',array('cookie'=>$res[0],'cookies'=>$res[1],'token'=>$res[2]),array('weid'=>$weid));
			   }else{
			    pdo_insert('zam_cookie',array('cookie'=>$res[0],'cookies'=>$res[1],'token'=>$res[2],'weid'=>$weid));
			   
			   }
			}
		    message('恭喜您，登录成功！',referer(),sucess);
		}
        include $this->template('set');
	}

	public function getimgver($username)//获取微信公众平台登录验证码 以及登录所需cookie
	{
		$rand = time().rand(100,999);
		    $url = "https://mp.weixin.qq.com/cgi-bin/verifycode?username=$username&r=".$rand;
			$response = ihttp_request($url);
	        return $imgcode= array(
			//'imgcodeurl' =>$imgcodeurl,
	                'imgcodeurl'=>$url,
			        'cookie' => $response['headers']['Set-Cookie'],
	        );

   }
   public function login($username,$pwd,$verify='',$codecookie=''){
    $loginurl = 'https://mp.weixin.qq.com/cgi-bin/login?lang=zh_CN';	
	$post = array(
		'username' => $username,
		'pwd' => md5($pwd),
		'imgcode' => $verify,
		'f' => 'json',	
	);
	//load()->func('communication');
	$response = ihttp_request($loginurl, $post, array('CURLOPT_REFERER' => 'https://mp.weixin.qq.com/'));
	
	if (is_error($response)) {
		return false;
	}
	$data = json_decode($response['content'], true);
	if ($data['base_resp']['ret'] == 0) {
		preg_match('/token=([0-9]+)/', $data['redirect_url'], $match);//token=82628120
		
		$token = trim($match[1]);//取得token
        $cookienew =  implode('; ', $response['headers']['Set-Cookie']);      
	
                $cookienew = iserializer($cookienew);
		$cookienews = 'Meepo';
		$back = array($cookienew,$cookienews,$token);
         
		return $back;
	}else{
	   return false;
	}
   }
    public function login2($username,$pwd,$verify='',$codecookie=''){
    $loginurl = 'https://mp.weixin.qq.com/cgi-bin/login?lang=zh_CN';	
	$post = array(
		'username' => $username,
		'pwd' => $pwd,
		'imgcode' => $verify,
		'f' => 'json',	
	);
	//load()->func('communication');
	$response = ihttp_request($loginurl, $post, array('CURLOPT_REFERER' => 'https://mp.weixin.qq.com/'));
	if (is_error($response)) {
		return false;
	}
	$data = json_decode($response['content'], true);
	if ($data['base_resp']['ret'] == 0) {
		preg_match('/token=([0-9]+)/', $data['redirect_url'], $match);//token=82628120
		
		$token = trim($match[1]);//取得token
        $cookienew =  implode('; ', $response['headers']['Set-Cookie']);      
	
                $cookienew = iserializer($cookienew);
		$cookienews = 'Meepo';
		$back = array($cookienew,$cookienews,$token);
         
		return $back;
	}else{
	   return false;
	}
   }
public function monilogin(){
	    global $_W,$_GPC;
		$weid = $_W['weid'];
        $wechat = $this->module['config'];
		$pass = md5($wechat['pass']);
		$res=$this->login2($wechat['user'],$pass);
		
		pdo_update('zam_cookie',array('cookie'=>$res[0],'cookies'=>$res[1],'token'=>$res[2]),array('weid'=>$weid));
        

 }

}