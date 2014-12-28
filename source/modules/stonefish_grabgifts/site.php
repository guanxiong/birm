<?php
/**
 * 全民抢礼品模块定义
 *
 */
defined('IN_IA') or exit('Access Denied');

class stonefish_grabgiftsModuleSite extends WeModuleSite {	
	
	public $table_reply  = 'stonefish_grabgifts_reply';
	public $table_list   = 'stonefish_grabgifts_userlist';	
	public $table_data   = 'stonefish_grabgifts_data';
	public $table_gift   = 'stonefish_grabgifts_gift';

	public function doMobilelisthome() {
		//这个操作被定义用来呈现 微站首页导航图标
		$this->doMobilelistentry();	
	}
	
	public function getTiles($keyword = '') {
		global $_GPC,$_W;
		$weid = $_W['weid'];
		$urls = array();
		$list = pdo_fetchall("SELECT id FROM ".tablename('rule')." WHERE weid = ".$weid." and module = 'stonefish_grabgifts'".(!empty($keyword) ? " AND name LIKE '%{$keyword}%'" : ''));
		if (!empty($list)) {
			foreach ($list as $row) {
			    $reply = pdo_fetch("SELECT title FROM ".tablename($this->table_reply)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $row['id']));
				$urls[] = array('title'=>$reply['title'], 'url'=> $_W['siteroot'].$this->createMobileUrl('grabgifts', array('rid' => $row['id'])));
			}
		}
		return $urls;
	}
    //入口列表
	public function doMobilelistentry() {
		global $_GPC,$_W;
		$weid = $_W['weid'];
		$time = time();
		$from_user = $_W['fans']['from_user'];
		$page_from_user = base64_encode(authcode($from_user, 'ENCODE'));

		$cover_reply = pdo_fetch("SELECT * FROM ".tablename("cover_reply")." WHERE weid = :weid and module = 'stonefish_grabgifts'", array(':weid' => $weid));
		$reply = pdo_fetchall("SELECT * FROM ".tablename($this->table_reply)." WHERE weid = :weid and status = 1 and start_time<".$time."  and end_time>".$time." ORDER BY `end_time` DESC", array(':weid' => $weid));

		foreach ($reply as $mid => $replys) {
			$reply[$mid]['num'] = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename($this->table_list)." WHERE weid = :weid and rid = :rid", array(':weid' => $_W['weid'], ':rid' => $replys['rid']));
			$reply[$mid]['is'] = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename($this->table_list)." WHERE weid = :weid and rid = :rid and from_user = :from_user", array(':weid' => $weid, ':rid' => $replys['rid'], ':from_user' => $from_user));
			$picture = $replys['picture'];
			if (substr($picture,0,6)=='images'){
			    $reply[$mid]['picture'] = $_W['attachurl'] . $picture;
			}else{
			    $reply[$mid]['picture'] = $_W['siteroot'] . $picture;
			}
		}

		//查询参与情况
		$usernum = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename($this->table_list)." WHERE weid = :weid and from_user = :from_user", array(':weid' => $weid, ':from_user' => $from_user));

	    $user_agent = $_SERVER['HTTP_USER_AGENT'];
		if (strpos($user_agent, 'MicroMessenger') === false) {
			echo "本页面仅支持微信访问!非微信浏览器禁止浏览!";
			//include $this->template('listentry');
		} else { 
			include $this->template('listentry');
		}		
	}
	function get_share($weid,$rid,$from_user,$title) {
		if (!empty($rid)) {
			$reply = pdo_fetch("SELECT xuninum FROM ".tablename($this->table_reply)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));
		    $listtotal = pdo_fetchcolumn('SELECT COUNT(*) FROM '.tablename($this->table_list).' WHERE weid= :weid AND rid= :rid', array(':weid' => $weid,':rid' => $rid));//参与人数
			$listtotal = $listtotal+$reply['xuninum'];//总参与人数
        }		
		if (!empty($from_user)) {
		    $userinfo = pdo_fetch("SELECT nickname FROM ".tablename($this->table_list)." WHERE weid= :weid AND rid= :rid AND from_user= :from_user", array(':weid' => $weid,':rid' => $rid,':from_user' => $from_user));
			$nickname = $userinfo['nickname'];
		}
		$str = array('#参与人数#'=>$listtotal,'#参与人名#'=>$nickname);
		$result = strtr($title,$str);
        return $result;
    }
	public function doMobilegrabgifts() {
		//分享页面显示。
		global $_GPC,$_W;
		$weid = $_W['weid'];//当前公众号ID
		$rid = $_GPC['rid'];
		$from_user = $_W['fans']['from_user'];		
		if (empty($from_user)) {
			$from_user = authcode(base64_decode($_GPC['from_user']), 'DECODE');
		}
		$page_from_user = base64_encode(authcode($from_user, 'ENCODE'));
		$serverapp = $_W['account']['level'];	//是否为高级号
		$cfg = $this->module['config'];
	    $appid = $cfg['appid'];
		$secret = $cfg['secret'];
		//活动规则
      	if (!empty($rid)) {
			$reply = pdo_fetch("SELECT subscribe FROM ".tablename($this->table_reply)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));
		}
		if($serverapp!=2&&!empty($appid)&&$reply['subscribe']==1){
		    //重新授权
		    if(isset($_COOKIE["user_oauth2_avatar"])&&isset($_COOKIE["user_oauth2_nickname"])&&isset($_COOKIE["user_oauth2_openid"])&&isset($_COOKIE["user_putonghao_openid"])){
		        $grabgiftsviewurl = $_W['siteroot'].$this->createMobileUrl('grabgiftsview', array('rid' => $rid));
			    header("location:$grabgiftsviewurl");
			    exit;
		    }else{
			    $url = $_W['siteroot'].$this->createMobileUrl('oauth2', array('rid' => $rid,'putonghao' => $page_from_user));
				$oauth2_code = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$appid."&redirect_uri=".urlencode($url)."&response_type=code&scope=snsapi_userinfo&state=0#wechat_redirect";				
				header("location:$oauth2_code");
				exit;
		    }
		}
		//服务号直接判读是否可以直接显示活动页
		if(isset($_COOKIE["user_oauth2_avatar"])&&isset($_COOKIE["user_oauth2_nickname"])&&isset($_COOKIE["user_oauth2_openid"])){
		    $grabgiftsviewurl = $_W['siteroot'].$this->createMobileUrl('grabgiftsview', array('rid' => $rid));
			header("location:$grabgiftsviewurl");
			exit;
		}else{
			if(!empty($from_user)) {		    
				//取得openid后查询是否为高级号
				if ($serverapp==2) {//高级号查询是否关注
			   	 $profile = fans_search($from_user, array('follow'));
					if($profile['follow']==2){//已关注直接获取信息
				   		$access_token = account_weixin_token($_W['account']);
				    	$oauth2_url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=".$access_token."&openid=".$from_user."&lang=zh_CN";				
				  	    $content = ihttp_get($oauth2_url);
				   	    $info = @json_decode($content['content'], true);
				    	if(empty($info) || !is_array($info) || empty($info['openid'])  || empty($info['nickname']) ) {
				    		echo '<h1>获取微信公众号授权失败[无法取得info], 请稍后重试！ 公众平台返回原始数据为: <br />' . $content['meta'].'<h1>';
				    		exit;
				    	}else{
					  		$avatar = $info['headimgurl'];
			           		$nickname = $info['nickname'];
							//设置cookie信息
							setcookie("user_oauth2_avatar", $avatar, time()+3600*24*7);
							setcookie("user_oauth2_nickname", $nickname, time()+3600*24*7);
							setcookie("user_oauth2_openid", $from_user, time()+3600*24*7);
							$grabgiftsviewurl = $_W['siteroot'].$this->createMobileUrl('grabgiftsview', array('rid' => $rid));
							header("location:$grabgiftsviewurl");
							exit;
						}		            
					}else{//非关注直接跳转授权页
				  		$appid = $_W['account']['key'];
						$url = $_W['siteroot'].$this->createMobileUrl('oauth2', array('rid' => $rid));
				    	$oauth2_code = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$appid."&redirect_uri=".urlencode($url)."&response_type=code&scope=snsapi_userinfo&state=0#wechat_redirect";				
				    	header("location:$oauth2_code");
						exit;
					}	
				}else{//普通号直接跳转授权页
			    	if(!empty($appid)){//有借用跳转授权页没有则跳转普通注册页
				    	$url = $_W['siteroot'].$this->createMobileUrl('oauth2', array('rid' => $rid,'putonghao' => $page_from_user));
				    	$oauth2_code = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$appid."&redirect_uri=".urlencode($url)."&response_type=code&scope=snsapi_userinfo&state=0#wechat_redirect";				
				    	header("location:$oauth2_code");
						exit;
					}else{
				    	$reguser = $_W['siteroot'].$this->createMobileUrl('reguser', array('rid' => $rid));
				    	header("location:$reguser");
						exit;
					}
				}			
			}else{
		    	//取不到openid 直接跳转授权页
				if(!empty($appid)){//有借用跳转授权页没有则跳转普通
					$url = $_W['siteroot'].$this->createMobileUrl('oauth2', array('rid' => $rid));
					$oauth2_code = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$appid."&redirect_uri=".urlencode($url)."&response_type=code&scope=snsapi_userinfo&state=0#wechat_redirect";				
					header("location:$oauth2_code");
					exit;
				}else{
					$reguser = $_W['siteroot'].$this->createMobileUrl('reguser', array('rid' => $rid));
					header("location:$reguser");
					exit;
				}
			}
        }		
	}
	
	public function doMobilegrabgiftsview() {
		//关健词触发页面显示。
		global $_GPC,$_W;
		$weid = $_W['weid'];//当前公众号ID		
		$rid = $_GPC['rid'];
		$from_user = authcode(base64_decode($_GPC['from_user']), 'DECODE');
		$page_from_user = $_GPC['from_user'];
		$from_user_oauth2 = $_COOKIE["user_oauth2_openid"];
		$page_from_user_oauth2 = base64_encode(authcode($_COOKIE["user_oauth2_openid"], 'ENCODE'));
		if (empty($page_from_user)){
		    $from_user = $from_user_oauth2;
		    $page_from_user = $page_from_user_oauth2;	
		}
		$this->checkoauth2($rid,$from_user_oauth2);//查询是否有cookie信息
        //活动规则
      	if (!empty($rid)) {
			$reply = pdo_fetch("SELECT * FROM ".tablename($this->table_reply)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));			
			$number_num_day = $reply['number_num_day'];
			$picture = $reply['picture'];			
			$bgcolor = $reply['bgcolor'];				
			$share_shownum = $reply['share_shownum'];	
			
			if (substr($picture,0,6)=='images'){
			   $picture = $_W['attachurl'] . $picture;
			}else{
			   $picture = $_W['siteroot'] . $picture;
			}			
			
			if ($reply['status']==0) {
				$statpraisetitle = '<h1>活动暂停！请稍候再试！</h1>';
			}
			if (time()<$reply['start_time']) {//判断活动是否已经开始
				$statpraisetitle = '<h1>活动未开始！</h1>';
			}elseif (time()>$reply['end_time']) {//判断活动是否已经结束
				$statpraisetitle = '<h1>活动已结束！</h1>';
			}
 		}
		//查询自己是否参与活动
		if(!empty($from_user_oauth2)) {
		    $mygift = pdo_fetch("SELECT * FROM ".tablename($this->table_list)." WHERE weid = :weid and from_user = :from_user and rid = :rid", array(':weid' => $weid,':from_user' => $from_user_oauth2,':rid' => $rid));
			//此处更新一下分享量和邀请量
			if(!empty($mygift)){
			    $yql = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename($this->table_data)." WHERE weid = :weid and fromuser = :fromuser and rid = :rid and isin >= ".$reply['opensubscribe']."", array(':weid' => $weid,':fromuser' => $from_user_oauth2,':rid' => $rid));
			    $fxl = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename($this->table_data)." WHERE weid = :weid and fromuser = :fromuser and rid = :rid", array(':weid' => $weid,':fromuser' => $from_user_oauth2,':rid' => $rid));
				pdo_update($this->table_list,array('sharenum' => $fxl,'yaoqingnum' => $yql),array('id' => $mygift['id']));
			}	
		}
		//查询是否参与活动
		if(!empty($from_user)) {
		    $usergift = pdo_fetch("SELECT * FROM ".tablename($this->table_list)." WHERE weid = :weid and from_user = :from_user and rid = :rid", array(':weid' => $weid,':from_user' => $from_user,':rid' => $rid));
		}
		//分享资格与人气情况
		if(!empty($usergift)){
			$sharenum = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename($this->table_data)." WHERE weid = :weid and fromuser = :from_user and rid = :rid", array(':weid' => $weid,':from_user' => $from_user,':rid' => $rid));
            $total_pages = ceil($sharenum/$reply['share_shownum']);	
		}
		//奖品信息
		$listgift = pdo_fetchall('SELECT * FROM '.tablename($this->table_gift).' WHERE rid = :rid order by `break`', array(':rid' => $rid));
		$giftnum = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename($this->table_gift)." WHERE rid = :rid", array(':rid' => $rid));
		$yaoqingnum = $usergift['yaoqingnum'];
		$giftwidth =100/$giftnum-4;//奖品显示宽度
		//取奖品中最大的资格值 
		$listgiftzd = pdo_fetch('SELECT break FROM '.tablename($this->table_gift).' WHERE rid = :rid order by `break` desc', array(':rid' => $rid));
		$giftnumzd = $listgiftzd['break'];
		//现在的进度
		$jindu = $yaoqingnum/$giftnumzd*100;
		//每个奖品的位置以及是否有资格或领取
		foreach ($listgift as $mid => $listgifts) {
		    $listgift[$mid]['weizhi'] = $listgifts['break']/$giftnumzd*100;
			if($yaoqingnum>=$listgifts['break']){
			    $listgift[$mid]['zg'] = 1;
				$lingjiang=1;
				$lingjiangnum=$lingjiangnum+1;
			}else{
			    if($zgzuijin!=1){//最近的奖品需要人数
				    $zgnum = $listgifts['break']-$yaoqingnum;
				    $zggift = $listgifts['title'];
				    $zgzuijin = 1;
				}
			}
			if(strpos($usergift['grabgifts'],"|".$listgifts['id']."|")!==false){
			    $listgift[$mid]['lingjiang'] = 1;
			}
		}
		//每个奖品的位置
		//虚拟人数据配置
		$now = time();
		if($now-$reply['xuninum_time']>$reply['xuninumtime']){
		    pdo_update($this->table_reply, array('xuninum_time' => $now,'xuninum' => $reply['xuninum']+mt_rand($reply['xuninuminitial'],$reply['xuninumending'])), array('rid' => $rid));
		}
		//虚拟人数据配置
		//参与活动人数
		$total = $reply['xuninum'] + pdo_fetchcolumn('SELECT COUNT(*) FROM '.tablename($this->table_list).' WHERE weid=:weid and rid=:rid', array(':weid' => $weid,':rid' => $rid));
		//参与活动人数
		//查询分享标题以及内容变量
		$reply['sharetitle']= $this->get_share($weid,$rid,$from_user_oauth2,$reply['sharetitle']);
		$reply['sharecontent']= $this->get_share($weid,$rid,$from_user_oauth2,$reply['sharecontent']);
		//整理数据进行页面显示
		$myavatar = $_COOKIE["user_oauth2_avatar"];
		$mynickname = $_COOKIE["user_oauth2_nickname"];
		$shareurl = $_W['siteroot'].$this->createMobileUrl('shareuserview', array('rid' => $rid,'fromuser' => $page_from_user_oauth2));//分享URL
		$regurl = $_W['siteroot'].$this->createMobileUrl('reg', array('rid' => $rid));//关注或借用直接注册页
		$lingjiangurl = $_W['siteroot'].$this->createMobileUrl('lingjiang', array('rid' => $rid));//领奖URL
		$mygifturl = $_W['siteroot'].$this->createMobileUrl('grabgiftsview', array('rid' => $rid));//我的页面
		$shouquan = base64_encode($_SERVER ['HTTP_HOST'].'anquan_ma_grabgifts');
		
		$user_agent = $_SERVER['HTTP_USER_AGENT'];
		if (strpos($user_agent, 'MicroMessenger') === false) {
			echo "本页面仅支持微信访问!非微信浏览器禁止浏览!";
			//include $this->template('grabgifts');
		} else { 
			include $this->template('grabgifts');
		}

	}
	public function doMobilepagesharedata() {
	    global $_GPC,$_W;
		$weid = $_W['weid'];//当前公众号ID		
		$rid = $_GPC['rid'];
		$from_user = authcode(base64_decode($_GPC['from_user']), 'DECODE');
		$item_per_page = $_GPC['pagesnum'];  
		$page_number = $_GPC['page'];    
		if(!is_numeric($page_number)){  
   		 header('HTTP/1.1 500 Invalid page number!');  
    		exit();  
		}
		//活动规则
      	if (!empty($rid)) {
			$reply = pdo_fetch("SELECT opensubscribe,isvisits FROM ".tablename($this->table_reply)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));
		}
		$position = ($page_number * $item_per_page);  
		$sharedata = pdo_fetchall("SELECT a.from_user,a.fromuser,a.isin,a.visitorstime,a.avatar as vavatar,a.nickname as vnickname,b.sharenum,b.avatar,b.nickname,b.datatime FROM ".tablename($this->table_data)." as a left join ".tablename($this->table_list)." as b on a.from_user=b.from_user WHERE a.weid = :weid and a.fromuser = :fromuser and a.rid = :rid order by isin desc,visitorstime desc LIMIT ".$position.",". $item_per_page,array(':weid' => $weid,':fromuser' => $from_user,':rid' => $rid));		

		//output results from database 
		if (!empty($sharedata)){
		    foreach ($sharedata as $share_data) {							
				if($share_data['isin']!=4){
				    $share_data['datatime'] = $share_data['visitorstime'];
					$share_data['avatar'] = $share_data['vavatar'];
					$share_data['nickname'] = $share_data['vnickname'];
				}
				if ($share_data['isin']<=0){
					$isin = '<span>未参与</span>';
				$result = $result.'<div class="usr_list">';
				}else{
				    if ($share_data['isin']>=$reply['opensubscribe']){
				        $isin = '<span class="curr">+1</span>';
				    }else{
					    $isin = '<span class="curr">+0</span>';					       
				    }
					if($reply['isvisits']){
					    $result = $result.'<div class="usr_list"><a href="'.$this->createMobileUrl('shareuserdata', array('rid' => $rid,'isvisits' => $reply['isvisits'],'fromuser' => base64_encode(authcode($share_data['from_user'], 'ENCODE')))).'">';
					}else{
					    $result = $result.'<div class="usr_list"><a href="'.$this->createMobileUrl('grabgiftsview', array('rid' => $rid,'from_user' => base64_encode(authcode($share_data['from_user'], 'ENCODE')))).'">';
					}
				}				
 			    $result = $result.'<p><img src="'.$share_data['avatar'].'"/></p>';
 			    $result = $result.'<dl>';
 				$result = $result.'<dt>'.$share_data['nickname'].$isin.'</dt>';
 				$result = $result.'<dd><b>人气指数&nbsp;'.$share_data['sharenum'].'</b><span>'.date('Y-m-d H:i', $share_data['datatime']).'</span></dd>';
 			    if ($share_data['isin']==0){
				$result = $result.'</dl>';
				}else{
				$result = $result.'</dl></a>';
				}
 		        $result = $result.'</div>';
            }		
		}		
		print_r($result);
	
	}
	public function doMobilepagepaihangdata() {
	    global $_GPC,$_W;
		$weid = $_W['weid'];//当前公众号ID		
		$rid = $_GPC['rid'];
		$item_per_page = $_GPC['pagesnum'];  
		$page_number = $_GPC['page'];
		$baobiao_maxnum = $_GPC['maxnum'];
		if(!is_numeric($page_number)){  
   		 header('HTTP/1.1 500 Invalid page number!');  
    		exit();  
		} 
		//活动规则
      	if (!empty($rid)) {
			$reply = pdo_fetch("SELECT isvisits FROM ".tablename($this->table_reply)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));
		}
		$position = ($page_number * $item_per_page);  
		$paihang = pdo_fetchall("SELECT * FROM ".tablename($this->table_list)." WHERE weid = :weid and rid = :rid and yaoqingnum>=".$baobiao_maxnum." order by `sharenum` asc, `datatime`   LIMIT ".$position.",". $item_per_page,array(':weid' => $weid,':rid' => $rid));	

		//output results from database 
		if (!empty($paihang)){
		    foreach ($paihang as $share_data) {
			    if($reply['isvisits']){
					$result = $result.'<div class="usr_list"><a href="'.$this->createMobileUrl('shareuserdata', array('rid' => $rid,'isvisits' => $reply['isvisits'],'fromuser' => base64_encode(authcode($share_data['from_user'], 'ENCODE')))).'">';
				}else{
					$result = $result.'<div class="usr_list"><a href="'.$this->createMobileUrl('grabgiftsview', array('rid' => $rid,'from_user' => base64_encode(authcode($share_data['from_user'], 'ENCODE')))).'">';
				}
		        $result = $result.'<p><img src="'.$share_data['avatar'].'"/></p>';
		        $result = $result.'<dl>';
		        $result = $result.'<dt>'.$share_data['nickname'].'<span class="curr">+1</span></dt>';
		        $result = $result.'<dd><b>人气指数&nbsp;'.$share_data['sharenum'].'</b><span>'.date('Y-m-d H:i', $share_data['datatime']).'</span></dd>';
		        $result = $result.'</dl></a>';
		        $result = $result.'</div>';
            }		
		}		
		print_r($result);
	
	}
	public function doMobilepaihang() {
		//关健词触发页面显示。
		global $_GPC,$_W;
		$weid = $_W['weid'];//当前公众号ID		
		$rid = $_GPC['rid'];
		$from_user_oauth2 = $_COOKIE["user_oauth2_openid"];
		$page_from_user_oauth2 = base64_encode(authcode($_COOKIE["user_oauth2_openid"], 'ENCODE'));
		$this->checkoauth2($rid,$from_user_oauth2);//查询是否有cookie信息
        //活动规则
      	if (!empty($rid)) {
			$reply = pdo_fetch("SELECT * FROM ".tablename($this->table_reply)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));			
			$number_num_day = $reply['number_num_day'];
			$picture = $reply['picture'];			
			$bgcolor = $reply['bgcolor'];				
			$share_shownum = $reply['share_shownum'];	
			
			if (substr($picture,0,6)=='images'){
			   $picture = $_W['attachurl'] . $picture;
			}else{
			   $picture = $_W['siteroot'] . $picture;
			}			
			
			if ($reply['status']==0) {
				$statpraisetitle = '<h1>活动暂停！请稍候再试！</h1>';
			}
			if (time()<$reply['start_time']) {//判断活动是否已经开始
				$statpraisetitle = '<h1>活动未开始！</h1>';
			}elseif (time()>$reply['end_time']) {//判断活动是否已经结束
				$statpraisetitle = '<h1>活动已结束！</h1>';
			}
 		}
		//取奖品中最大的资格值 
		$listgiftzd = pdo_fetch('SELECT break FROM '.tablename($this->table_gift).' WHERE rid = :rid order by `break` desc', array(':rid' => $rid));
		$giftnumzd = $listgiftzd['break'];
		//查询自己是否参与活动
		if(!empty($from_user_oauth2)) {
		    $mygift = pdo_fetch("SELECT * FROM ".tablename($this->table_list)." WHERE weid = :weid and from_user = :from_user and rid = :rid", array(':weid' => $weid,':from_user' => $from_user_oauth2,':rid' => $rid));
			//当前排名
			$paimengqian = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename($this->table_list)." WHERE weid = :weid and rid = :rid and yaoqingnum>=".$giftnumzd." and sharenum>".$mygift['sharenum']."", array(':weid' => $weid,':rid' => $rid));
			//查询同分享排名数
		    $paimengtong = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename($this->table_list)." WHERE weid = :weid and rid = :rid and yaoqingnum>=".$giftnumzd." and sharenum=".$mygift['sharenum']." and datatime<".$mygift['datatime']."", array(':weid' => $weid,':rid' => $rid));
		    $paihangwei=$paimengqian+$paimengtong+1;//排名
			//当前排名
			
		}		
		//排行榜资格与人气情况
		if(!empty($rid)){
			$sharenum = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename($this->table_list)." WHERE weid = :weid and rid = :rid and yaoqingnum>=".$giftnumzd."", array(':weid' => $weid,':rid' => $rid));
            $total_pages = ceil($sharenum/$reply['biaobiaonum']);	
		}
		
		//奖品信息
		$listgift = pdo_fetchall('SELECT * FROM '.tablename($this->table_gift).' WHERE rid = :rid order by `break`', array(':rid' => $rid));
		$giftnum = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename($this->table_gift)." WHERE rid = :rid", array(':rid' => $rid));
		$giftwidth =100/$giftnum-4;//奖品显示宽度
				
		//整理数据进行页面显示		
		$myavatar = $_COOKIE["user_oauth2_avatar"];
		$mynickname = $_COOKIE["user_oauth2_nickname"];
		$shareurl = $_W['siteroot'].$this->createMobileUrl('shareuserview', array('rid' => $rid,'fromuser' => $page_from_user_oauth2));//分享URL
		$regurl = $_W['siteroot'].$this->createMobileUrl('reg', array('rid' => $rid));//关注或借用直接注册页
		$guanzhu = $reply['shareurl'];//没有关注用户跳转引导页
		$lingjiangurl = $_W['siteroot'].$this->createMobileUrl('lingjiang', array('rid' => $rid));//领奖URL
		$mygifturl = $_W['siteroot'].$this->createMobileUrl('grabgiftsview', array('rid' => $rid));//我的页面
				
		$user_agent = $_SERVER['HTTP_USER_AGENT'];
		if (strpos($user_agent, 'MicroMessenger') === false) {
			echo "本页面仅支持微信访问!非微信浏览器禁止浏览!";
			//include $this->template('paihang');
		} else { 
			include $this->template('paihang');
		}

	}
	public function doMobilereg() {
		//关健词触发页面显示。
		global $_GPC,$_W;
		$weid = $_W['weid'];//当前公众号ID		
		$rid = $_GPC['rid'];
		$from_user_oauth2 = $_COOKIE["user_oauth2_openid"];
		$page_from_user_oauth2 = base64_encode(authcode($_COOKIE["user_oauth2_openid"], 'ENCODE'));
		$from_user_putonghao = $_COOKIE["user_putonghao_openid"];
		$this->checkoauth2($rid,$from_user_oauth2);//查询是否有cookie信息
		//活动规则
      	if (!empty($rid)) {
			$reply = pdo_fetch("SELECT subscribe,shareurl,opensubscribe FROM ".tablename($this->table_reply)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));	
		}else{
		    echo "一般不会进入这里，进入这里说明有ＢＵＧ，请联系开发者";
			exit;
		}
		//判断是否为关注用户才能领取
		if($reply['subscribe']==1){
		    $serverapp = $_W['account']['level'];	//是否为高级号
		    if($serverapp!=2){
		        if(empty($from_user_putonghao)){
			        //跳转至关注引导页
			        $shareurl = $reply['shareurl'];
			        header("location:$shareurl");
			        exit;
			    }else{
				    $profile  = fans_search($from_user_putonghao, array('follow'));//借用的查询是否关注原公众号
				}
		    }else{
			    $profile  = fans_search($from_user_oauth2, array('follow'));//认证的查询是否关注本公众号
			}
		    if (empty($profile) Or $profile['follow']==0) {
			    //跳转至关注引导页
				$shareurl = $reply['shareurl'];
				header("location:$shareurl");
                exit;
		    }
		}
		//判断是否为关注用户才能领取
		//查询是否参与活动
		if(!empty($from_user_oauth2)) {
		    $usergift = pdo_fetch("SELECT * FROM ".tablename($this->table_list)." WHERE weid = :weid and from_user = :from_user and rid = :rid", array(':weid' => $weid,':from_user' => $from_user_oauth2,':rid' => $rid));			
		}
		if(empty($usergift)){
		    //认证号或借用号直接注册
		    $now = time();
		    $insertdata = array(
			    'weid'      => $weid,
			    'from_user' => $from_user_oauth2,
			    'rid'       => $rid,
			    'avatar'    => $_COOKIE["user_oauth2_avatar"],
			    'nickname'  => $_COOKIE["user_oauth2_nickname"],			    
 			    'sharetime' => $now,
			    'datatime'  => $now,
		    );
		    pdo_insert($this->table_list, $insertdata);		
		    //查询是否被邀请人员
		    $yaoqing = pdo_fetch("SELECT id,uid FROM ".tablename($this->table_data)." WHERE weid = :weid and from_user = :from_user and rid = :rid ORDER BY `visitorstime` asc", array(':weid' => $weid,':from_user' => $from_user_oauth2,':rid' => $rid));
			if (!empty($yaoqing)){//更新被邀请人员状态 是以时间为标准为邀请人加资格
			    pdo_update($this->table_data,array('isin' => 4),array('id' => $yaoqing['id']));
				$yaoqingren = pdo_fetch("SELECT yaoqingnum FROM ".tablename($this->table_list)." WHERE id = :id", array(':id' => $yaoqing['uid']));
				pdo_update($this->table_list,array('yaoqingnum' => $yaoqingren['yaoqingnum']+1),array('id' => $yaoqing['uid']));
				//查询所有其他邀请人并相互增加人气
				$yaoqingall = pdo_fetchall("SELECT id,uid FROM ".tablename($this->table_data)." WHERE weid = :weid and from_user = :from_user and rid = :rid and id!=".$yaoqing['id']." ORDER BY `visitorstime` asc", array(':weid' => $weid,':from_user' => $from_user_oauth2,':rid' => $rid));
				foreach ($yaoqingall as $row) {
					pdo_update($this->table_data,array('isin' => 2),array('id' => $row['id']));
				    if($reply['opensubscribe']==2){
					    $yaoqingren = pdo_fetch("SELECT yaoqingnum FROM ".tablename($this->table_list)." WHERE id = :id", array(':id' => $row['uid']));
				        pdo_update($this->table_list,array('yaoqingnum' => $yaoqingren['yaoqingnum']+1),array('id' => $row['uid']));					
					}
				}
			}
		    //注册完成转到自己的页面
			$mygifturl = $_W['siteroot'].$this->createMobileUrl('grabgiftsview', array('rid' => $rid));
			header("location:$mygifturl");
			exit;
		}else{
		    //直接转到自己的页面
			$mygifturl = $_W['siteroot'].$this->createMobileUrl('grabgiftsview', array('rid' => $rid));
			header("location:$mygifturl");
			exit;
		}
		
	}
	public function doMobilereguser() {
		//关健词触发页面显示。
		global $_GPC,$_W;
		$weid = $_W['weid'];//当前公众号ID		
		$rid = $_GPC['rid'];
		
		
		$user_agent = $_SERVER['HTTP_USER_AGENT'];
		if (strpos($user_agent, 'MicroMessenger') === false) {
			echo "本页面仅支持微信访问!非微信浏览器禁止浏览!";
			//include $this->template('reguser');
		} else { 
			include $this->template('reguser');
		}
	}
	public function doMobilelingjiang() {
		//关健词触发页面显示。
		global $_GPC,$_W;
		$weid = $_W['weid'];//当前公众号ID		
		$rid = $_GPC['rid'];
		$from_user_oauth2 = $_COOKIE["user_oauth2_openid"];
		$page_from_user_oauth2 = base64_encode(authcode($_COOKIE["user_oauth2_openid"], 'ENCODE'));
		$this->checkoauth2($rid,$from_user_oauth2);//查询是否有cookie信息
		//活动规则
      	if (!empty($rid)) {
			$reply = pdo_fetch("SELECT * FROM ".tablename($this->table_reply)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));			
			$number_num_day = $reply['number_num_day'];
			$picture = $reply['picture'];			
			$bgcolor = $reply['bgcolor'];				
			
			if (substr($picture,0,6)=='images'){
			   $picture = $_W['attachurl'] . $picture;
			}else{
			   $picture = $_W['siteroot'] . $picture;
			}			
			
			if ($reply['status']==0) {
				$statpraisetitle = '<h1>活动暂停！请稍候再试！</h1>';
			}
			if (time()<$reply['start_time']) {//判断活动是否已经开始
				$statpraisetitle = '<h1>活动未开始！</h1>';
			}elseif (time()>$reply['end_time']) {//判断活动是否已经结束
				$statpraisetitle = '<h1>活动已结束！</h1>';
			}
 		}
		//查询自己是否参与活动
		if(!empty($from_user_oauth2)) {
		    $usergift = pdo_fetch("SELECT * FROM ".tablename($this->table_list)." WHERE weid = :weid and from_user = :from_user and rid = :rid", array(':weid' => $weid,':from_user' => $from_user_oauth2,':rid' => $rid));
			$mygift = $usergift;
		}
		//奖品信息
		$listgift = pdo_fetchall('SELECT * FROM '.tablename($this->table_gift).' WHERE rid = :rid order by `break`', array(':rid' => $rid));
		$giftnum = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename($this->table_gift)." WHERE rid = :rid", array(':rid' => $rid));
		$yaoqingnum = $usergift['yaoqingnum'];
		//取奖品中最大的资格值 
		$listgiftzd = pdo_fetch('SELECT break FROM '.tablename($this->table_gift).' WHERE rid = :rid order by `break` desc', array(':rid' => $rid));
		$giftnumzd = $listgiftzd['break'];
		//是否有资格或领取
		foreach ($listgift as $mid => $listgifts) {
			if($yaoqingnum>=$listgifts['break']){
			    $listgift[$mid]['zg'] = 1;
				$lingjiang=1;
				$lingjiangnum=$lingjiangnum+1;
			}else{
			    if($zgzuijin!=1){//最近的奖品需要人数
				    $zgnum = $listgifts['break']-$yaoqingnum;
				    $zggift = $listgifts['title'];
				    $zgzuijin = 1;
				}
			}
			if(strpos($usergift['grabgifts'],"|".$listgifts['id']."|")!==false){
			    $listgift[$mid]['lingjiang'] = 1;
			}
		}
		//是否有资格或领取
		    //判断是否已输入用户资料
			$isrealname = 0;
			if($reply['isrealname']){
		        if($usergift['realname']!=''){
				    $isrealname = 1;
				}			
		    }else{
			    $isrealname = 1;
			}
			
			$ismobile = 0;
			if($reply['ismobile']){
		        if($usergift['mobile']!=''){
				    $ismobile = 1;
				}			
		    }else{
			    $ismobile = 1;
			}
			
			$isweixin = 0;
			if($reply['isweixin']){
		        if($usergift['nickname']!=''){
				    $isweixin = 1;
				}			
		    }else{
			    $isweixin = 1;
			}
			
			$isqqhao = 0;
			if($reply['isqqhao']){
		        if($usergift['qqhao']!=''){
				    $isqqhao = 1;
				}			
		    }else{
			    $isqqhao = 1;
			}
			
			$isemail = 0;
			if($reply['isemail']){
		        if($usergift['email']!=''){
				    $isemail = 1;
				}			
		    }else{
			    $isemail = 1;
			}
			
			$isaddress = 0;
			if($reply['isaddress']){
		        if($usergift['address']!=''){
				    $isaddress = 1;
				}			
		    }else{
			    $isaddress = 1;
			}
			$is_user=0;	
            if($isaddress==1&&$isemail==1&&$isqqhao==1&&$isweixin==1&&$ismobile==1&&$isrealname==1){
				$is_user=1;				
			}
		$mygifturl = $_W['siteroot'].$this->createMobileUrl('grabgiftsview', array('rid' => $rid));//我的页面
		$shareurl = $_W['siteroot'].$this->createMobileUrl('shareuserview', array('rid' => $rid,'fromuser' => $page_from_user_oauth2));//分享URL
		
		$user_agent = $_SERVER['HTTP_USER_AGENT'];
		if (strpos($user_agent, 'MicroMessenger') === false) {
			echo "本页面仅支持微信访问!非微信浏览器禁止浏览!";
			//include $this->template('lingjiang');
		} else { 
			include $this->template('lingjiang');
		}
	}
	public function doMobileawardget() {
		//关健词触发页面显示。
		global $_GPC,$_W;
		$weid = $_W['weid'];//当前公众号ID		
		$rid = $_GPC['rid'];
		$from_user_oauth2 = $_COOKIE["user_oauth2_openid"];
		$page_from_user_oauth2 = base64_encode(authcode($_COOKIE["user_oauth2_openid"], 'ENCODE'));
		$from_user_putonghao = $_COOKIE["user_putonghao_openid"];
		$this->checkoauth2($rid,$from_user_oauth2);//查询是否有cookie信息
		$giftid = $_GPC['giftid'];
		
		//活动规则
      	if (!empty($rid)) {
			$reply = pdo_fetch("SELECT * FROM ".tablename($this->table_reply)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));			
			$number_num_day = $reply['number_num_day'];
			$picture = $reply['picture'];			
			$bgcolor = $reply['bgcolor'];				
			
			if (substr($picture,0,6)=='images'){
			   $picture = $_W['attachurl'] . $picture;
			}else{
			   $picture = $_W['siteroot'] . $picture;
			}			
			
			if ($reply['status']==0) {
				$statpraisetitle = '<h1>活动暂停！请稍候再试！</h1>';
			}
			if (time()<$reply['start_time']) {//判断活动是否已经开始
				$statpraisetitle = '<h1>活动未开始！</h1>';
			}elseif (time()>$reply['end_time']) {//判断活动是否已经结束
				$statpraisetitle = '<h1>活动已结束！</h1>';
			}
 		}
		//查询自己是否参与活动
		if(!empty($from_user_oauth2)) {
		    $mygift = pdo_fetch("SELECT * FROM ".tablename($this->table_list)." WHERE weid = :weid and from_user = :from_user and rid = :rid", array(':weid' => $weid,':from_user' => $from_user_oauth2,':rid' => $rid));
			//取用户资料
			if(!empty($from_user_putonghao)){
		        $profile  = fans_search($from_user_putonghao, array('realname','mobile','nickname','qq','email','address'));
			}else{
			    $profile  = fans_search($from_user_oauth2, array('realname','mobile','nickname','qq','email','address'));
			}
		}
		    
		$mygifturl = $_W['siteroot'].$this->createMobileUrl('grabgiftsview', array('rid' => $rid));//我的页面
		$shareurl = $_W['siteroot'].$this->createMobileUrl('shareuserview', array('rid' => $rid,'fromuser' => $page_from_user_oauth2));//分享URL
		
		$user_agent = $_SERVER['HTTP_USER_AGENT'];
		if (strpos($user_agent, 'MicroMessenger') === false) {
			echo "本页面仅支持微信访问!非微信浏览器禁止浏览!";
			//include $this->template('awardget');
		} else { 
			include $this->template('awardget');
		}
		
	}
	public function doMobileawardgetpass() {
		//关健词触发页面显示。
		global $_GPC,$_W;
		$weid = $_W['weid'];//当前公众号ID		
		$rid = $_GPC['rid'];
		$from_user_oauth2 = $_COOKIE["user_oauth2_openid"];
		$page_from_user_oauth2 = base64_encode(authcode($_COOKIE["user_oauth2_openid"], 'ENCODE'));
		$this->checkoauth2($rid,$from_user_oauth2);//查询是否有cookie信息
		$giftid = $_GPC['giftid'];
		
		//活动规则
      	if (!empty($rid)) {
			$reply = pdo_fetch("SELECT * FROM ".tablename($this->table_reply)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));			
			$number_num_day = $reply['number_num_day'];
			$picture = $reply['picture'];			
			$bgcolor = $reply['bgcolor'];				
			
			if (substr($picture,0,6)=='images'){
			   $picture = $_W['attachurl'] . $picture;
			}else{
			   $picture = $_W['siteroot'] . $picture;
			}			
			
			if ($reply['status']==0) {
				$statpraisetitle = '<h1>活动暂停！请稍候再试！</h1>';
			}
			if (time()<$reply['start_time']) {//判断活动是否已经开始
				$statpraisetitle = '<h1>活动未开始！</h1>';
			}elseif (time()>$reply['end_time']) {//判断活动是否已经结束
				$statpraisetitle = '<h1>活动已结束！</h1>';
			}
 		}
		//查询自己是否参与活动
		if(!empty($from_user_oauth2)) {
		    $mygift = pdo_fetch("SELECT * FROM ".tablename($this->table_list)." WHERE weid = :weid and from_user = :from_user and rid = :rid", array(':weid' => $weid,':from_user' => $from_user_oauth2,':rid' => $rid));			
		}
		    
		$mygifturl = $_W['siteroot'].$this->createMobileUrl('grabgiftsview', array('rid' => $rid));//我的页面
		$shareurl = $_W['siteroot'].$this->createMobileUrl('shareuserview', array('rid' => $rid,'fromuser' => $page_from_user_oauth2));//分享URL
		
		$user_agent = $_SERVER['HTTP_USER_AGENT'];
		if (strpos($user_agent, 'MicroMessenger') === false) {
			echo "本页面仅支持微信访问!非微信浏览器禁止浏览!";
			//include $this->template('awardgetpass');
		} else { 
			include $this->template('awardgetpass');
		}		
	}
	
	public function doMobileAwardinfoget() {
		//分享页面显示。
		global $_GPC,$_W;
		$weid = $_W['weid'];//当前公众号ID		
		$rid = $_GPC['rid'];
		$giftid = $_GPC['giftid'];
		$from_user_oauth2 = $_COOKIE["user_oauth2_openid"];
		$page_from_user_oauth2 = base64_encode(authcode($_COOKIE["user_oauth2_openid"], 'ENCODE'));
		$from_user_putonghao = $_COOKIE["user_putonghao_openid"];
		$this->checkoauth2($rid,$from_user_oauth2);//查询是否有cookie信息
		$shouquan = $_GPC['shouquan'];
		$realname = $_GPC['realname'];
		$mobile = $_GPC['mobile'];
		$weixin = $_GPC['weixin'];
		$qqhao = $_GPC['qqhao'];
		$email = $_GPC['email'];
		$address = $_GPC['address'];
       // if($shouquan==base64_encode($_SERVER ['HTTP_HOST'].'anquan_ma_grabgifts')){
		    $userinfo = pdo_fetch('SELECT * FROM '.tablename($this->table_list).' WHERE from_user=:from_user', array(':from_user' => $from_user_oauth2));
		    if(!empty($userinfo)){		    
			    pdo_update($this->table_list,array('realname' => $realname,'mobile' => $mobile,'weixin' => $weixin,'qqhao' => $qqhao,'email' => $email,'address' => $address),array('id' => $userinfo['id']));			
		    }
		    if (!empty($rid)) {
			    $reply = pdo_fetch("SELECT * FROM ".tablename($this->table_reply)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));	
		        //同时更新到官方FANS表中
			    if(!empty($from_user_putonghao)){//借用号信息保存本公众号下。而不是借用的。
			        $from_user_oauth2 = $from_user_putonghao;
			    }
			    if($reply['isfans']){
			        if($reply['isrealname']){
				        fans_update($from_user_oauth2, array(
					        'realname' => $realname,					
		                ));
				    }
				    if($reply['ismobile']){
				        fans_update($from_user_oauth2, array(
					        'mobile' => $mobile,					
		                ));
				    }				
				    if($reply['isqqhao']){
				        fans_update($from_user_oauth2, array(
					        'qq' => $qqhao,					
		                ));
				    }
				    if($reply['isemail']){
				        fans_update($from_user_oauth2, array(
					        'email' => $email,					
		                ));
				    }
				    if($reply['isaddress']){
				        fans_update($from_user_oauth2, array(
					        'address' => $address,					
		                ));
				    }				
			    }
		    }
		    //查询奖品数量
		    $gift = pdo_fetch("SELECT total,total_winning FROM ".tablename($this->table_gift)." WHERE id = :id", array(':id' => $giftid));
		    if($gift['total']>$gift['total_winning']){			    
			    pdo_update($this->table_gift,array('total_winning' => $gift['total_winning']+1),array('id' => $giftid));
			    message('恭喜您！您的领奖资料已保存成功！', $_W['siteroot'].$this->createMobileUrl('lingjiang', array('rid' => $rid)),'success');
				exit;
		    }else{
			    message('您来晚了，奖品刚刚被领完了！', $_W['siteroot'].$this->createMobileUrl('lingjiang', array('rid' => $rid)),'error');
			    exit;
		    }
		    //查询奖品数量		
		//}
	}
	public function doMobileduijiangaward() {
		//关健词触发页面显示。
		global $_GPC,$_W;
		$weid = $_W['weid'];//当前公众号ID		
		$rid = $_GPC['rid'];
		$shouquan = $_GPC['shouquan'];
		$from_user_oauth2 = $_COOKIE["user_oauth2_openid"];
		$page_from_user_oauth2 = base64_encode(authcode($_COOKIE["user_oauth2_openid"], 'ENCODE'));
		$this->checkoauth2($rid,$from_user_oauth2);//查询是否有cookie信息
		$giftid = $_GPC['giftid'];
		$pass = $_GPC['awardpass'];//兑奖密码
		//if($shouquan==base64_encode($_SERVER ['HTTP_HOST'].'anquan_ma_grabgifts')){
		    $userinfo = pdo_fetch('SELECT * FROM '.tablename($this->table_list).' WHERE from_user=:from_user', array(':from_user' => $from_user_oauth2));
		    $gift = pdo_fetch('SELECT awardpass,total,total_winning FROM '.tablename($this->table_gift).' WHERE id=:id', array(':id' => $giftid));
		    if(!empty($gift)){
			    if($pass==$gift['awardpass']){
			        if($userinfo['grabgifts']!=''){
				        pdo_update($this->table_list,array('grabgifts' => $userinfo['grabgifts'].$giftid.'|'),array('id' => $userinfo['id']));				
				    }else{
				        pdo_update($this->table_list,array('grabgifts' => '|'.$giftid.'|'),array('id' => $userinfo['id']));
				    }
                    //增加中奖数量
				    pdo_update($this->table_gift,array('total_winning' => $gift['total_winning']+1),array('id' => $giftid));
				    message('恭喜兑奖成功！', ''.$_W['siteroot'].$this->createMobileUrl('lingjiang', array('rid' => $rid)).'', 'success');
				    exit;
			    }else{
			        message('抱歉，您输入的密码不对！', ''.$_W['siteroot'].$this->createMobileUrl('lingjiang', array('rid' => $rid)).'', 'error');
				    exit;
			    }
		    }else{
			    message('抱歉，信息出错,请联系管理员！', referer(), 'error');
		    }
       // }		
	}
	
	public function doMobilegiftsview() {
		//关健词触发页面显示。
		global $_GPC,$_W;
		$weid = $_W['weid'];//当前公众号ID		
		$rid = $_GPC['rid'];
		$id = $_GPC['id'];		
		$from_user_oauth2 = $_COOKIE["user_oauth2_openid"];
		$page_from_user_oauth2 = base64_encode(authcode($_COOKIE["user_oauth2_openid"], 'ENCODE'));
		$this->checkoauth2($rid,$from_user_oauth2);//查询是否有cookie信息
        //活动规则
      	if (!empty($rid)) {
			$reply = pdo_fetch("SELECT * FROM ".tablename($this->table_reply)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));			
			$number_num_day = $reply['number_num_day'];
			$picture = $reply['picture'];			
			$bgcolor = $reply['bgcolor'];				
			$share_shownum = $reply['share_shownum'];	
			
			if (substr($picture,0,6)=='images'){
			   $picture = $_W['attachurl'] . $picture;
			}else{
			   $picture = $_W['siteroot'] . $picture;
			}			
			
			if ($reply['status']==0) {
				$statpraisetitle = '<h1>活动暂停！请稍候再试！</h1>';
			}
			if (time()<$reply['start_time']) {//判断活动是否已经开始
				$statpraisetitle = '<h1>活动未开始！</h1>';
			}elseif (time()>$reply['end_time']) {//判断活动是否已经结束
				$statpraisetitle = '<h1>活动已结束！</h1>';
			}
 		}
		//查询自己是否参与活动
		if(!empty($from_user_oauth2)) {
		    $mygift = pdo_fetch("SELECT * FROM ".tablename($this->table_list)." WHERE weid = :weid and from_user = :from_user and rid = :rid", array(':weid' => $weid,':from_user' => $from_user_oauth2,':rid' => $rid));
		}
		
		//查询奖品详细
		if(!empty($id)) {
		    $giftinfo = pdo_fetch("SELECT * FROM ".tablename($this->table_gift)." WHERE  id = :id", array(':id' => $id));
		}
		
		//整理数据进行页面显示
		$mygifturl = $_W['siteroot'].$this->createMobileUrl('grabgiftsview', array('rid' => $rid));//我的页面
		$shareurl = $_W['siteroot'].$this->createMobileUrl('shareuserview', array('rid' => $rid,'fromuser' => $page_from_user_oauth2));//分享URL
		$regurl = $_W['siteroot'].$this->createMobileUrl('reg', array('rid' => $rid));//关注或借用直接注册页
		
		
		$user_agent = $_SERVER['HTTP_USER_AGENT'];
		if (strpos($user_agent, 'MicroMessenger') === false) {
			echo "本页面仅支持微信访问!非微信浏览器禁止浏览!";
			//include $this->template('giftsview');
		} else { 
			include $this->template('giftsview');
		}
	}
	public function doMobileshareuserview() {
	    global $_GPC,$_W;
		$weid = $_W['weid'];//当前公众号ID		
		$rid = $_GPC['rid'];
		$from_user = $_W['fans']['from_user'];
		$page_from_user = base64_encode(authcode($from_user, 'ENCODE'));
		$fromuser = authcode(base64_decode($_GPC['fromuser']), 'DECODE');
		$page_fromuser = $_GPC['fromuser'];
		$serverapp = $_W['account']['level'];	//是否为高级号
		$cfg = $this->module['config'];
	    $appid = $cfg['appid'];
		$secret = $cfg['secret'];
        if(isset($_COOKIE["user_oauth2_avatar"])&&isset($_COOKIE["user_oauth2_nickname"])&&isset($_COOKIE["user_oauth2_openid"])){
		    $grabgiftsviewurl = $_W['siteroot'].$this->createMobileUrl('shareuserdata', array('rid' => $rid,'fromuser' => $page_fromuser));
			header("location:$grabgiftsviewurl");
			exit;
		}else{
		if(!empty($from_user)) {
			//取得openid后查询是否为高级号
			if ($serverapp==2) {//高级号查询是否关注
			    $profile = fans_search($from_user, array('follow'));
				if($profile['follow']==2){//已关注直接获取信息
				    $access_token = account_weixin_token($_W['account']);
				    $oauth2_url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=".$access_token."&openid=".$from_user."&lang=zh_CN";				
				    $content = ihttp_get($oauth2_url);
				    $info = @json_decode($content['content'], true);
				    if(empty($info) || !is_array($info) || empty($info['openid'])  || empty($info['nickname']) ) {
				    	echo '<h1>获取微信公众号授权失败[无法取得info], 请稍后重试！ 公众平台返回原始数据为: <br />' . $content['meta'].'<h1>';
				    	exit;
				    }else{
					    $avatar = $info['headimgurl'];
			            $nickname = $info['nickname'];
						//设置cookie信息
						setcookie("user_oauth2_avatar", $avatar, time()+3600*24*7);
						setcookie("user_oauth2_nickname", $nickname, time()+3600*24*7);
						setcookie("user_oauth2_openid", $from_user, time()+3600*24*7);
						$grabgiftsviewurl = $_W['siteroot'].$this->createMobileUrl('shareuserdata', array('rid' => $rid,'fromuser' => $page_fromuser));
						header("location:$grabgiftsviewurl");
						exit;
					}		            
				}else{//非关注直接跳转授权页
				    $appid = $_W['account']['key'];
		            $url = $_W['siteroot'].$this->createMobileUrl('oauth2', array('rid' => $rid,'fromuser' => $page_fromuser));
				    $oauth2_code = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$appid."&redirect_uri=".urlencode($url)."&response_type=code&scope=snsapi_userinfo&state=0#wechat_redirect";				
				    header("location:$oauth2_code");
					exit;
				}	
			}else{//普通号直接跳转授权页
			    if(!empty($appid)){//有借用跳转授权页没有则跳转普通注册页
				    $url = $_W['siteroot'].$this->createMobileUrl('oauth2', array('rid' => $rid,'fromuser' => $page_fromuser));
				    $oauth2_code = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$appid."&redirect_uri=".urlencode($url)."&response_type=code&scope=snsapi_userinfo&state=0#wechat_redirect";				
				    header("location:$oauth2_code");
					exit;
				}else{
				    $reguser = $_W['siteroot'].$this->createMobileUrl('reguser', array('rid' => $rid));
				    header("location:$reguser");
					exit;
				}
			}			
		}else{
		    //取不到openid 直接跳转授权页
			if(!empty($appid)){//有借用跳转授权页没有则跳转普通
				$url = $_W['siteroot'].$this->createMobileUrl('oauth2', array('rid' => $rid,'fromuser' => $page_fromuser));
				$oauth2_code = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$appid."&redirect_uri=".urlencode($url)."&response_type=code&scope=snsapi_userinfo&state=0#wechat_redirect";				
				header("location:$oauth2_code");
				exit;
			}else{
				$reguser = $_W['siteroot'].$this->createMobileUrl('reguser', array('rid' => $rid));
				header("location:$reguser");
				exit;
			}
		}
		}
	
	}
	
	public function doMobileshareuserdata() {
		//关健词触发页面显示。
		global $_GPC,$_W;
		$weid = $_W['weid'];//当前公众号ID		
		$rid = $_GPC['rid'];
		$isvisits = $_GPC['isvisits'];//是否互访
		$fromuser = authcode(base64_decode($_GPC['fromuser']), 'DECODE');
		$page_fromuser = $_GPC['fromuser'];
		$from_user_oauth2 = $_COOKIE["user_oauth2_openid"];
		$page_from_user_oauth2 = base64_encode(authcode($_COOKIE["user_oauth2_openid"], 'ENCODE'));
		$this->checkoauth2($rid,$from_user_oauth2, $page_fromuser);//查询是否有cookie信息
		$visitorsip = getip();
		$now = time();
		//活动规则
      	if (!empty($rid)) {
			$reply = pdo_fetch("SELECT * FROM ".tablename($this->table_reply)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));
		}
		/*计算奖品数量
		$listpraise = pdo_fetchall('SELECT * FROM '.tablename($this->table_gift).' WHERE rid=:rid  order by `id`',array(':rid' => $rid));
		if (!empty($listpraise)) {
			foreach ($listpraise as $row) {
				$zigenum = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename($this->table_list)." WHERE weid = :weid and rid = :rid and yaoqingnum>= :yaoqingnum", array(':weid' => $weid, ':rid' => $rid, ':yaoqingnum' => $row['break']));
				if($row['total']>=$zigenum){
				    pdo_update($this->table_gift,array('total_winning' => $zigenum),array('id' => $row['id']));
				}else{
				    pdo_update($this->table_gift,array('total_winning' => $row['total']),array('id' => $row['id']));
					UPDATE ims_stonefish_grabgifts_gift SET total_winning = 0 where rid=''
				}
			}
		}
		//计算奖品数量*/
		//查询是否参与活动
		if(!empty($fromuser)) {
		    $usergift = pdo_fetch("SELECT * FROM ".tablename($this->table_list)." WHERE weid = :weid and from_user = :from_user and rid = :rid", array(':weid' => $weid,':from_user' => $fromuser,':rid' => $rid));
            $user_gift = pdo_fetch("SELECT * FROM ".tablename($this->table_list)." WHERE weid = :weid and from_user = :from_user and rid = :rid", array(':weid' => $weid,':from_user' => $from_user_oauth2,':rid' => $rid));
            if(!empty($usergift)){
			    //添加分享人气记录
				if($fromuser!=$from_user_oauth2){//自己不能给自己加人气
				    $sharedata = pdo_fetch("SELECT * FROM ".tablename($this->table_data)." WHERE weid = :weid and fromuser = :fromuser and rid = :rid and from_user = :from_user", array(':weid' => $weid,':fromuser' => $fromuser,':from_user' => $from_user_oauth2,':rid' => $rid));
					if(empty($sharedata)){//一个朋友只加一次人气	
					    $insertdata = array(
		                    'weid'           => $weid,
		                    'from_user'      => $from_user_oauth2,
							'fromuser'       => $fromuser,
							'avatar'         => $_COOKIE["user_oauth2_avatar"],                            
							'nickname'       => $_COOKIE["user_oauth2_nickname"],
		                    'rid'            => $rid,
 		                    'uid'            => $usergift['id'],
		                    'visitorsip'	 => $visitorsip,
		                    'visitorstime'   => $now
		                ); 
						pdo_insert($this->table_data, $insertdata);
						$dataid = pdo_insertid();//取id
						//给分享人添加人气量
						$sharenum = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename($this->table_data)." WHERE weid = :weid and fromuser = :fromuser and rid = :rid", array(':weid' => $weid,':fromuser' => $fromuser,':rid' => $rid));
						$updatelist = array(
		                    'sharenum'  => $sharenum,
		                    'sharetime' => $now
		                );
						pdo_update($this->table_list,$updatelist,array('id' => $usergift['id']));					
					    //是否为互访
						if($isvisits==1){
						    if (!empty($user_gift)){
							    pdo_update($this->table_data,array('isin' => 1),array('id' => $dataid));
							    if($reply['opensubscribe']<=1){
								    pdo_update($this->table_list,array('yaoqingnum' => $usergift['yaoqingnum']+1),array('id' => $usergift['id']));
							    }
							}else{
							    pdo_update($this->table_data,array('isin' => -1),array('id' => $dataid));
							}
						}else{
						    //查询是是否为参与活动人并第一次访问好友,如果是第一次为分享人添加邀请量					
					        if (!empty($user_gift)){
					            $one_user = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename($this->table_data)." WHERE weid = :weid and from_user = :from_user and rid = :rid", array(':weid' => $weid,':from_user' => $from_user_oauth2,':rid' => $rid));
						        if ($one_user==1){
						            pdo_update($this->table_data,array('isin' => 3),array('id' => $dataid));
							        if($reply['opensubscribe']<=3){
								        pdo_update($this->table_list,array('yaoqingnum' => $usergift['yaoqingnum']+1),array('id' => $usergift['id']));
								    }								
						        }else{
						            pdo_update($this->table_data,array('isin' => 2),array('id' => $dataid));
								    if($reply['opensubscribe']<=2){
								        pdo_update($this->table_list,array('yaoqingnum' => $usergift['yaoqingnum']+1),array('id' => $usergift['id']));
								    }
						        }
					        }else{
							    if($reply['opensubscribe']<=0){
								    pdo_update($this->table_list,array('yaoqingnum' => $usergift['yaoqingnum']+1),array('id' => $usergift['id']));
							    }
							}
					        //查询是是否为参与活动人并第一次访问好友,如果是第一次为分享人添加邀请量
						}
					}
				}
				//转分享人页
				$gifturl = $_W['siteroot'].$this->createMobileUrl('grabgiftsview', array('rid' => $rid,'from_user' => $page_fromuser));
				header("location:$gifturl");
				exit;
			}else{
			    //转自己页
			    $mygifturl = $_W['siteroot'].$this->createMobileUrl('grabgiftsview', array('rid' => $rid));
				header("location:$mygifturl");
				exit;
			}
		}else{
		//分享人出错。一般不会出现
		}		
		
	}
	
	public function doMobileoauth2() {
		global $_GPC,$_W;
		$weid = $_W['weid'];//当前公众号ID
		$rid = $_GPC['rid'];
		$fromuser = authcode(base64_decode($_GPC['fromuser']), 'DECODE');
		$page_fromuser = $_GPC['fromuser'];
		$putonghao = authcode(base64_decode($_GPC['putonghao']), 'DECODE');	
		$serverapp = $_W['account']['level'];	//是否为高级号
		//借用还是本身为认证号
		if ($serverapp==2) {
		    $appid = $_W['account']['key'];
		    $secret = $_W['account']['secret'];
		}else{
		    $cfg = $this->module['config'];
			$appid = $cfg['appid'];
			$secret = $cfg['secret'];
		}
		//用户不授权返回提示说明
		if ($_GPC['code']=="authdeny"){
		    $url = $_W['siteroot'].$this->createMobileUrl('oauth2shouquan', array('rid' => $rid));
			header("location:$url");
			exit;
		}
		//高级接口取未关注用户Openid
		if (isset($_GPC['code'])){
		    //第二步：获得到了OpenID		    			
		    $code = $_GPC['code'];			
		    $oauth2_code = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$appid."&secret=".$secret."&code=".$code."&grant_type=authorization_code";
		    $content = ihttp_get($oauth2_code);
		    $token = @json_decode($content['content'], true);
			if(empty($token) || !is_array($token) || empty($token['access_token']) || empty($token['openid'])) {
				echo '<h1>获取微信公众号授权'.$code.'失败[无法取得token以及openid], 请稍后重试！ 公众平台返回原始数据为: <br />' . $content['meta'].'<h1>';
				exit;
			}
		    $from_user = $token['openid'];
			$access_token = $token['access_token'];
			$oauth2_url = "https://api.weixin.qq.com/sns/userinfo?access_token=".$access_token."&openid=".$from_user."&lang=zh_CN";
			
			//使用全局ACCESS_TOKEN获取OpenID的详细信息			
			$content = ihttp_get($oauth2_url);
			$info = @json_decode($content['content'], true);
			if(empty($info) || !is_array($info) || empty($info['openid'])  || empty($info['nickname']) ) {
				echo '<h1>获取微信公众号授权失败[无法取得info], 请稍后重试！ 公众平台返回原始数据为: <br />' . $content['meta'].'<h1>';
				exit;
			}
		    $avatar = $info['headimgurl'];
		    $nickname = $info['nickname'];
			$page_from_user = base64_encode(authcode($from_user, 'ENCODE'));
		    //设置cookie信息
		    setcookie("user_oauth2_avatar", $avatar, time()+3600*24*7);
		    setcookie("user_oauth2_nickname", $nickname, time()+3600*24*7);
			setcookie("user_oauth2_openid", $from_user, time()+3600*24*7);
			if(!empty($putonghao)){
			    setcookie("user_putonghao_openid", $putonghao, time()+3600*24*7);
			}
			if(!empty($fromuser)){
			    $grabgiftsviewurl = $_W['siteroot'].$this->createMobileUrl('shareuserdata', array('rid' => $rid,'fromuser' => $page_fromuser));
			}else{
			    $grabgiftsviewurl = $_W['siteroot'].$this->createMobileUrl('grabgiftsview', array('rid' => $rid,'from_user' => $page_from_user));
			}		    
		    header("location:$grabgiftsviewurl");
			exit;
		}else{
			echo '<h1>不是高级认证号或网页授权域名设置出错!</h1>';
			exit;		
		}
	
	}
	
	public function doMobileoauth2shouquan() {
	    global $_GPC,$_W;
		$weid = $_W['weid'];//当前公众号ID
		$rid = $_GPC['rid'];
		//活动规则
		if (!empty($rid)) {
			$reply = pdo_fetch("SELECT shareurl FROM ".tablename($this->table_reply)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));
			$url = $reply['shareurl'];
	        header("location:$url");
			exit;
 		}
		
	}
	private function checkoauth2($rid,$oauth2, $page_fromuser) {//如果没有取得cookie信息	重新授权
        global $_W;
		$weid = $_W['weid'];//当前公众号ID
		$serverapp = $_W['account']['level'];	//是否为高级号
		$cfg = $this->module['config'];
	    $appid = $cfg['appid'];
		$secret = $cfg['secret'];		
		if(empty($oauth2)){
		    if ($serverapp==2) {//高级号
			    $appid = $_W['account']['key'];
			    $url = $_W['siteroot'].$this->createMobileUrl('oauth2', array('rid' => $rid,'fromuser' => $page_fromuser));
			    $oauth2_code = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$appid."&redirect_uri=".urlencode($url)."&response_type=code&scope=snsapi_userinfo&state=0#wechat_redirect";				
			    header("location:$oauth2_code");
				exit;
			}else{
			    if(!empty($appid)){//有借用跳转授权页没有则跳转普通注册页
				    $url = $_W['siteroot'].$this->createMobileUrl('oauth2', array('rid' => $rid,'fromuser' => $page_fromuser));
				    $oauth2_code = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$appid."&redirect_uri=".urlencode($url)."&response_type=code&scope=snsapi_userinfo&state=0#wechat_redirect";				
				    header("location:$oauth2_code");
					exit;
				}else{
				    $reguser = $_W['siteroot'].$this->createMobileUrl('reguser', array('rid' => $rid));
				    header("location:$reguser");
					exit;
				}
			}
		}
	}	
}