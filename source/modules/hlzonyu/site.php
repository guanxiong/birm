<?php
/**
 * 抢礼品模块定义
 * [皓蓝] www.weixiamen.cn 5517286
 */
defined('IN_IA') or exit('Access Denied');

class hlzonyuModuleSite extends WeModuleSite {	
	
	public $table_reply = 'hlzonyu_reply';
	public $table_list   = 'hlzonyu_list';	
	public $table_data   = 'hlzonyu_data';
	public $table_log   = 'hlzonyu_log';
	public $table_order   = 'hlzonyu_order';
	public function doMobilelisthome() {
		//这个操作被定义用来呈现 微站首页导航图标
		$this->doMobileindex();	
	}
	
	public function getProfileTiles() {
		
	}
	
	public function getzonyutiles() {
		global $_W;
		$time = time();
		$zonyus = pdo_fetchall("SELECT rid, title FROM ".tablename($this->table_reply)." WHERE status = 1 and start_time<".$time."  and end_time>".$time." and weid = '{$_W['weid']}'");
		if (!empty($zonyus)) {
			foreach ($zonyus as $row) {
				$urls[] = array('title' => $row['title'], 'url' => $this->createMobileUrl('hlzonyu', array('rid' => $row['rid'])));
			}
			return $urls;
		}
	}
	
	public function getHomeTiles($keyword = '') {
		$urls = array();
		$list = pdo_fetchall("SELECT name, id FROM ".tablename('rule')." WHERE module = 'hlzonyu'".(!empty($keyword) ? " AND name LIKE '%{$keyword}%'" : ''));
		if (!empty($list)) {
			foreach ($list as $row) {
				$urls[] = array('title'=>$row['name'], 'url'=> $this->createMobileUrl('zonyu', array('id' => $row['id'])));
			}
		}
		return $urls;
	}
	
	public function doMobileMybargin() {
		global $_GPC,$_W;
		$weid = $_W['weid'];
		$from_user = $_W['fans']['from_user'];
		$dayang=pdo_fetch("select * from ".tablename($this->table_list)." WHERE from_user='{$from_user}'  limit 1");
		if($dayang){
			$url = $_W['siteroot'].$this->createMobileUrl('zonyu', array('rid' => $dayang['rid']));
			header("location:$url");
		}else{
			message('你还还没有选择砍价的礼品或还没有朋友来帮忙砍价呢.<br/>请到首页选择后叫朋友来砍价',$this->createMobileUrl('index'),'success');
		}
	}
	
	public function doMobileMydayang() {
		global $_GPC,$_W;
		$weid = $_W['weid'];
		$from_user = $_W['fans']['from_user'];
		$dayang=pdo_fetch("select * from ".tablename($this->table_list)." WHERE from_user='{$from_user}'  limit 1");
		if($dayang){
			$url = $_W['siteroot'].$this->createMobileUrl('zonyu', array('rid' => $dayang['rid']));
			header("location:$url");
		}else{
			message('你还还没有选择需要`的礼品或你还没有朋友来`呢.<br/>请到首页选择后叫朋友来砍价',$this->createMobileUrl('index'),'success');
		}
	}
	
    //入口列表
	public function doMobileindex() {
		global $_GPC,$_W;
		$weid = $_W['weid'];
		$time = time();
		$from_user = $_W['fans']['from_user'];
		$staturl=$_W['siteroot'].$this->createMobileUrl('index', array());
		
		$description=pdo_fetchcolumn("SELECT description FROM ".tablename('cover_reply')." WHERE weid = :weid ", array(':weid' => $weid));
		
		//$reply = pdo_fetchall("SELECT * FROM ".tablename($this->table_reply)." WHERE weid = :weid and status = 1 and start_time<".$time."  and end_time>".$time." ORDER BY `end_time` DESC", array(':weid' => $weid));
		$reply = pdo_fetchall("SELECT * FROM ".tablename($this->table_reply)." WHERE weid = :weid and status = 1  ORDER BY `end_time` DESC", array(':weid' => $weid));
		
		$hasselect=0;
		foreach ($reply as $mid => $replys) {
			$reply[$mid]['num'] = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename($this->table_list)." WHERE weid = :weid and rid = :rid " , array(':weid' => $_W['weid'], ':rid' => $replys['rid']));
			$reply[$mid]['is'] = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename($this->table_list)." WHERE  from_user = :from_user and weid = :weid and rid = :rid ", array(':weid' => $_W['weid'], ':rid' => $replys['rid'], ':from_user' => $from_user));
			
			if ($reply[$mid]['start_time']> TIMESTAMP){
				$reply[$mid]['over']=0;
				
			}
			if ($reply[$mid]['end_time']< TIMESTAMP){
				$reply[$mid]['over']=2;
			}
			if($reply[$mid]['is']){
				$hasselect=1;
				$hasname=$replys['title'];
				break;
			}
			$reply[$mid]['lpurl']= $this->createMobileUrl('zonyu', array('rid' => $replys['rid']));
		}
		if($hasselect==1){
			foreach ($reply as $mid => $replys){
				
				$reply[$mid]['lpurl']= "javascript:alert('您已选择了".$hasname.",点击右上角【我的亲友团】，邀请朋友来帮您砍价吧!')";
				
			}
		}
		
		$dayan = pdo_fetchall("SELECT * FROM ".tablename($this->table_reply)." WHERE weid = :weid and status = 1 ORDER BY `end_time` DESC", array(':weid' => $weid));
		
		$hasselectd=0;
		foreach ($dayan as $mid => $replys) {
			$dayan[$mid]['num'] = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename($this->table_list)." WHERE weid = :weid and rid = :rid ", array(':weid' => $_W['weid'], ':rid' => $replys['rid']));
			$dayan[$mid]['is'] = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename($this->table_list)." WHERE weid = :weid and rid = :rid and from_user = :from_user ", array(':weid' => $_W['weid'], ':rid' => $replys['rid'], ':from_user' => $from_user));
			
			if ($dayan[$mid]['start_time']> TIMESTAMP){
				$dayan[$mid]['over']=0;
				
			}
			if ($dayan[$mid]['end_time']< TIMESTAMP){
				$dayan[$mid]['over']=2;
			}
			if($dayan[$mid]['is']){
				$hasselectd=1;
				$hasname=$replys['title'];
			}
			$dayan[$mid]['lpurl']= $this->createMobileUrl('zonyu', array('rid' => $replys['rid']));
		}
		
		if($hasselectd==1){
			
			$mtips='';
			$zonyunums = pdo_fetch("SELECT * FROM ".tablename($this->table_list)." WHERE  from_user = :from_user and weid = :weid  LIMIT 1", array(':weid' => $_W['weid'],  ':from_user' => $from_user));
			$zonyunum=$zonyunums['zonyunum'];
			$zonyureply = pdo_fetch("SELECT * FROM ".tablename($this->table_reply)." WHERE  rid = :rid and weid = :weid  LIMIT 1", array(':weid' => $_W['weid'], ':rid'=>$zonyunums['rid']));
			if($zonyunum==$zonyureply['zonyunum']){
				$mtips='亲，您选择的商品已砍到底价啦，赶紧 “我的砍价团页面” 点击“现在购买”下单，把您的商品带回家吧！';
			
			}else{
				$cfg = $this->module['config'];
				$sjxz = $cfg['sjxz'];
				if(($zonyunums['createtime']+($sjxz*3600)) < TIMESTAMP){
					$mtips='您的砍价时间已结束，您可以到 “我的砍价团页面”点击 “现在购买”下单，把您的商品带回家哦！';
				
				}
			
			}
			
			$zonyunum=$zonyureply['productprice']-$zonyunum;
			//var_dump($zonyunums);
			$zonyucount = pdo_fetchcolumn("SELECT count(*) FROM ".tablename($this->table_data)." WHERE  uid = :uid and weid = :weid  LIMIT 1", array(':weid' => $_W['weid'], ':uid' => $zonyunums['id']));
			$zonyucount=$zonyucount-1;
		}
		//查询参与情况
		
	    $user_agent = $_SERVER['HTTP_USER_AGENT'];
	    /* if (strpos($user_agent, 'MicroMessenger') === false) {
			echo "本页面仅支持微信访问!非微信浏览器禁止浏览!";
			exit;
	    } 
		*/
		$cfg = $this->module['config'];
		
		include $this->template('index');
			
	}
	
	public function doMobilezonyu() {
		//抢礼品分享页面显示。
		global $_GPC,$_W;
		$weid = $_W['weid'];//当前公众号ID
		$s = 0;
		$profile_s = 1;
		$fromuser = $_W['fans']['from_user'];
		//取得分享人的数据
		$profile = fans_search($fromuser, array('follow', 'realname', 'mobile','avatar','nickname'));
		
	if(!empty($fromuser)){
			$appid = $_W['account']['key'];
		    $secret = $_W['account']['secret'];
			$serverapp = $_W['account']['level'];	//是否为高级号
			if ($serverapp!=2) {//普通号
				$cfg = $this->module['config'];
			    $appid = $cfg['appid'];
			    $secret = $cfg['secret'];
			}//借用的
			if (empty($profile['avatar']) || empty($profile['nickname'])) {
				
				$url = $_W['siteroot'].$this->createMobileUrl('userinfo', array());
				$oauth2_code = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$appid."&redirect_uri=".urlencode($url)."&response_type=code&scope=snsapi_userinfo&state=0#wechat_redirect";				
				header("location:$oauth2_code");
				
				exit;
			}
		}
		
		if (empty($_GPC['rid'])) {
			$rid = $_GPC['id'];
		}else{
			$rid = $_GPC['rid'];
		}
		
		$foo = !empty($_GPC['foo']) ? $_GPC['foo'] : '';
      	if (!empty($rid)) {
			$reply = pdo_fetch("SELECT * FROM ".tablename($this->table_reply)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));			
			$zonyu_show = $reply['zonyu_show'];
			$zonyu_rankshow = $reply['zonyu_rankshow'];
			$zonyu_shownum = $reply['zonyu_shownum'];
			$zonyu_ranknum = $reply['zonyu_ranknum'];
			$zonyunum = $reply['zonyunum'];
			$dingpic = $reply['dingpic'];
			
			if (substr($dingpic,0,6)=='images'){
			   $dingpic = $_W['attachurl'] . $dingpic;
			}
			
			
			if ($reply['status']==0) {
				$statzonyutitle = '<h1>活动暂停！请稍候再试！</h1>';
			}
			if (time()<$reply['start_time']) {//判断活动是否已经开始
				$statzonyutitle = '<h1>活动未开始！</h1>';
			}elseif (time()>$reply['end_time']) {//判断活动是否已经结束
				$statzonyutitle = '<h1>活动已结束！</h1>';
			}
 		}
		

		
		$ztotal = 0;
		
		if(empty($fromuser)) {
		    if (isset($_COOKIE["user_oauth2_openid"])){
			    $fromuser = $_COOKIE["user_oauth2_openid"];
			}else{
				$sql='SELECT content FROM '.tablename('rule_keyword')." WHERE  rid = '".$rid."' ";
			    $rpkeyword=pdo_fetchcolumn($sql);
		         $weiguanzhu =  "亲！请先关注公众号：".$_W['account']['name']." 微信号: ".$_W['account']['account']."发送关键字:".$rpkeyword." 收到回复后，再进入登记信息参与本活动！";			
		    }
			
			
			$profile  = fans_search($fromuser, array('follow'));
			if ($profile['follow']==0){
				$sql='SELECT content FROM '.tablename('rule_keyword')." WHERE  rid = '".$rid."' ";
			    $rpkeyword=pdo_fetchcolumn($sql);
		        $weiguanzhu =  "亲！请先关注公众号：".$_W['account']['name']." 微信号: ".$_W['account']['account']."发送关键字:".$rpkeyword." 收到回复后，再进入登记信息参与本活动！";
			}
			
		}
		//计算内定排名开始
		/* 
		if ($reply['ndrankstatus']==1){			
			//取真实排名第一的`数
			$zonyunum = 0;
			$listshare = pdo_fetch('SELECT zonyunum,from_user FROM '.tablename($this->table_list).'  WHERE weid= :weid AND rid = :rid and ndrank = 0 order by `zonyunum` desc LIMIT 1', array(':weid' => $weid,':rid' => $rid));
			if (!empty($listshare)){
			    $zonyunum = $listshare['zonyunum'];
			    //取内定排名人员
			    $lists = pdo_fetchall("SELECT id,ndranknums,zonyunum FROM ".tablename($this->table_list)." WHERE weid = '".$weid."' and rid= '".$rid."' and ndrank > 0 order by `ndrank` desc limit ".$reply['ndrankstatusnum']."" );
			    foreach ($lists as $list) {
				    if($list['zonyunum']<=$zonyunum){
					    $zonyunum = intval($zonyunum+mt_rand(1,$list['ndranknums']));
						$updata = array(
			               'zonyunum' => $zonyunum,
		                );
						pdo_update($this->table_list, $updata, array('id' => $list['id']));				
				    }
				}	
			}
		}		 */
		//计算内定排名结束
		if(!empty($fromuser)) {
			$lists = pdo_fetch("SELECT id,status,zonyunum,zhongjiang,zonyutime FROM ".tablename($this->table_list)." WHERE from_user = '".$fromuser."' and weid = '".$weid."' and rid= '".$rid."' limit 1" );	
			if(!empty($lists)){
			   $uid= $lists['id'];
			   $zj = $lists['zhongjiang'];
			   $ztotal = $lists['zonyunum'];
			   $zonyutime = $lists['zonyutime'];
			   if ($lists['status']==0){
				   $statzonyutitle = '<h1>因作弊行为被管理员屏蔽，请您联系 '.$_W['account']['name'].' 管理员!</h1>'; 
			   }
			   $utotal=pdo_fetchcolumn("SELECT count(*) FROM ".tablename($this->table_data)." WHERE uid = '".$lists['id']."' and weid = '".$weid."' and rid= '".$rid."' limit 1" );
				$utotal=$utotal-1;
			
			}
			
			
		}
		//取得所有分享排名100
		$listshare = pdo_fetchall('SELECT a.*,b.realname,b.mobile FROM '.tablename($this->table_list).' as a left join '.tablename('fans').' as b on a.from_user=b.from_user  WHERE a.weid= :weid AND a.rid = :rid order by a.zonyunum desc,a.zonyutime asc LIMIT 10', array(':weid' => $weid,':rid' => $rid));
		
		$count= pdo_fetch("SELECT count(id) as dd FROM ".tablename($this->table_list)." WHERE weid=".$weid." and rid= ".$rid." and zonyunum >= ".$ztotal."");
		$sharepms=$count['dd'];//排名
		//查询同`排名数
		if ($zonyutime!='') {
		    $countt= pdo_fetch("SELECT count(id) as dd FROM ".tablename($this->table_list)." WHERE weid=".$weid." and rid= ".$rid." and zonyunum = ".$ztotal." and zonyutime>".$zonyutime."");
		    $sharepmt=$countt['dd'];//同`排名数
		}else{
		    $sharepmt=0;
		}

		$sharepm=$sharepms-$sharepmt;//排名

		$count = pdo_fetch("SELECT count(id) as dd FROM ".tablename($this->table_list)." WHERE weid=".$weid." AND rid= ".$rid."");
		$listtotal = $count['dd'];//总参与人数
		//取得抢礼品数据
		
		if(!empty($uid)) {
		
		    $list = pdo_fetchall('SELECT * FROM '.tablename($this->table_data).'  WHERE weid= :weid and uid = :uid and rid = :rid order by `viewnum` desc LIMIT 10', array(':weid' => $_W['weid'], ':uid' => $uid, ':rid' => $rid) );		
		}	
		//var_dump($list);
		//判断是否中奖
		if ($zj==1) {
			$zhongjiang = $reply['zhongjiang'];
			$zhongjiang_s = 1;			
		}
	
		//判断是否中奖
		//整理数据进行页面显示
		//判断是否绑定
		
		if (!empty($profile['realname']) AND !empty($profile['mobile']) ) {
			$profile_s=1;
		}
		//判断是否绑定
		if (strpos($reply['picture'], 'http') === false) {
			if(strpos($reply['picture'], 'modules') === false) {
					$imgurl=$_W['attachurl'] . $reply['picture'];
				}else{
					$imgurl=$reply['picture'];
				}
		}else{
			$imgurl=$reply['picture'];
		}
      	$title = $reply['title'];
		//$loclurl=$_W['siteroot'].$this->createMobileUrl('zonyu', array('rid' => $rid, 'from_user' => $_GPC['from_user']));		
		$regurl=$this->createMobileUrl('reg', array('fromuser' => $fromuser));
		$staturl=$_W['siteroot'].$this->createMobileUrl('statzonyu', array('rid' => $rid,'fromuser' => $fromuser));

		
		$user_agent = $_SERVER['HTTP_USER_AGENT'];
		
		/* if (strpos($user_agent, 'MicroMessenger') === false) {
			echo "本页面仅支持微信访问!非微信浏览器禁止浏览!";
			exit;
			
		}  */
		if (intval($reply['zonyunum'])>0){
			$allper=100-intval($ztotal*100/$reply['zonyunum']);
		}else{
			$allper=100;
			$allper=100;
		}
		$zonyunum=0;
		if($lists){
			
			
			$zonyunums = pdo_fetch("SELECT id,zonyunum FROM ".tablename($this->table_list)." WHERE  from_user = :from_user and weid = :weid and rid = :rid LIMIT 1", array(':weid' => $_W['weid'], ':rid' => $replys['rid'], ':from_user' => $from_user));
			$zonyunum=$zonyunums['zonyunum'];
			
			$zonyucount = pdo_fetchcolumn("SELECT count(*) FROM ".tablename($this->table_data)." WHERE  uid = :uid and weid = :weid and rid = :rid LIMIT 1", array(':weid' => $_W['weid'], ':rid' => $replys['rid'], ':uid' => $zonyunums['id']));

		}
		$orderlist=pdo_fetch("SELECT * FROM ".tablename('hlzonyu_order')." WHERE  from_user = :from_user and weid = :weid LIMIT 1 ",array(':weid' => $_W['weid'], ':from_user' => $fromuser));
		//var_dump($orderlist);
		$cfg = $this->module['config'];
		include $this->template('show');
	}

	public function doMobileReg() {
		//抢礼品分享页面显示。
		global $_GPC,$_W;
		$weid = $_W['weid'];//当前公众号ID
		$rid  = $_GPC['rid'];//当前规则ID		
		$fromuser = $_W['fans']['from_user'];
		//查询用户是否为关注用户
		if(!empty($fromuser)) {
		    $profile  = fans_search($fromuser, array('follow'));
		}else{
		    $result = "您访问的分享异常,请联系公众号技术人员！";
			echo $result;
			exit;
		}
		
		if ($profile['follow']==0){
		
		    //没有关注时提示用户
			$sql='SELECT content FROM '.tablename('rule_keyword')." WHERE  rid = '".$rid."' ";
			$rpkeyword=pdo_fetchcolumn($sql)	;

			if(!empty($rpkeyword)){
				$result = "亲！请先关注公众号：{$_W['account']['name']} ID: {$_W['account']['account']} 发送关键字:'{$rpkeyword}'收到回复后，再进入登记信息参与活动！";
			}else{
				$result = "您访问的分享异常,请联系公众号技术人员！";
			}
			echo $result;
			exit;		
		}
	
		//取得抢礼品数据
		if(!empty($fromuser)) {
			//关注用户　注册转发
			$rs = pdo_fetch("SELECT id FROM ".tablename($this->table_list)." WHERE from_user = '".$fromuser."' and weid = '".$weid."' and rid = '".$rid."' limit 1" );			

			if(empty($rs['id'])){			
					fans_update($fromuser, array(
					'realname' => $_GPC['realname'],
					'mobile' => $_GPC['mobile'],
				    ));
					$result='注册信息提交成功，立即分享吧！';
			}else{
					$result='您已注册过信息，可直接分享！';
			}			
		}
		echo $result;	
	}
	
	public function doMobilestatupdata() {
		//抢礼品分享页面显示。
		global $_GPC,$_W;
		$weid = $_W['weid'];//当前公众号ID
		$dataid = $_GPC['dataid'];
		$realname = $_GPC['realname'];

		//取得抢礼品数据
		if(!empty($dataid)) {
			pdo_update($this->table_data,array('realname' => $realname),array('id' => $dataid));
			$result='你的朋友已收到你的`!';						
		}else{
			$result = "您访问的分享".$dataid."异常,请联系公众号技术人员！";
		}
		echo $result;	
	}
	
	public function doMobileoauth(){
		global $_GPC,$_W;
		$weid      = $_W['weid'];//当前公众号ID
		$fromuser  = $_GPC['fromuser'];
		$rid       = $_GPC['rid'];//当前规则ID


		if(empty($rid)){
		    echo '<h1>分享rid出错，请联系管理员!</h1>';
			exit;
		}
		if(empty($fromuser)){
		    echo '<h1>分享人出错，请联系管理员!</h1>';
			exit;
		}
		
		//查询分享活动规则	查询是否开始或暂停
      	if (!empty($rid)) {
			$reply = pdo_fetch("SELECT * FROM ".tablename($this->table_reply)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));
			$zonyu_show = $reply['zonyu_show'];
			$zonyu_imgtext = $reply['zonyu_imgtext'];
			$zonyu_rankshow = $reply['zonyu_rankshow'];
			$zonyu_shownum = $reply['zonyu_shownum'];
			$zonyu_ranknum = $reply['zonyu_ranknum'];
			$zonyu_type = $reply['zonyu_type'];
			$dingpic = $reply['dingpic'];
			$zanpic = $reply['zanpic'];
			
			if (substr($dingpic,0,6)=='images'){
			   $dingpic = $_W['attachurl'] . $dingpic;
			}
			if (substr($zanpic,0,6)=='images'){
			   $zanpic = $_W['attachurl'] . $zanpic;
			}
			
			if ($reply['status']==0) {
				$statzonyutitle = '<h1>活动暂停！请稍候再试！</h1>';
			}
			if (time()<$reply['start_time']) {//判断活动是否已经开始
				$statzonyutitle = '<h1>活动未开始！</h1>';
			}elseif (time()>$reply['end_time']) {//判断活动是否已经结束
				$statzonyutitle = '<h1>活动已结束！</h1>';
			}
			
 		}
		
		//计算内定排名开始
		if ($reply['ndrankstatus']==1){			
			//取真实排名第一的`数
			$zonyunum = 0;
			$listshare = pdo_fetch('SELECT zonyunum,from_user FROM '.tablename($this->table_list).'  WHERE weid= :weid AND rid = :rid and ndrank = 0 order by `zonyunum` desc LIMIT 1', array(':weid' => $weid,':rid' => $rid));
			if (!empty($listshare)){
			    $zonyunum = $listshare['zonyunum'];
			    //取内定排名人员
			    $lists = pdo_fetchall("SELECT id,ndranknums,zonyunum FROM ".tablename($this->table_list)." WHERE weid = '".$weid."' and rid= '".$rid."' and ndrank > 0 order by `ndrank` desc limit ".$reply['ndrankstatusnum']."" );
			    foreach ($lists as $list) {
				    if($list['zonyunum']<=$zonyunum){
					    $zonyunum = intval($zonyunum+mt_rand(1,$list['ndranknums']));
						$updata = array(
			               'zonyunum' => $zonyunum,
		                );
						pdo_update($this->table_list, $updata, array('id' => $list['id']));				
				    }
				}	
			}
		}		
		//计算内定排名结束
		//取得分享人的数据查询是否屏蔽
		if(!empty($fromuser)) {
			$lists = pdo_fetch('SELECT id,status,zonyunum,zonyutime FROM '.tablename($this->table_list).' WHERE from_user = :fromuser and weid = :weid and rid = :rid limit 1', array(':fromuser' => $fromuser,':weid' => $weid,':rid' => $rid));
			if(!empty($lists)){
			   $uid= $lists['id'];
			   $ztotal = $lists['zonyunum'];
			   $zonyutime = $lists['zonyutime'];
			   if ($lists['status']==0){
				   $statzonyutitle = '<h1>因作弊行为被管理员屏蔽，请告知您的朋友联系 '.$_W['account']['name'].' 管理员!</h1>'; 
			   }			
			}
		}
		//取得所有分享排名100
		//$listshare = pdo_fetchall('SELECT a.*,b.realname,b.mobile FROM '.tablename($this->table_list).' as a left join '.tablename('fans').' as b on a.from_user=b.from_user  WHERE a.weid= :weid AND a.rid = :rid order by `a.zonyunum` desc LIMIT '.$zonyu_ranknum.'', array(':weid' => $weid,':rid' => $rid));

		$count= pdo_fetch("SELECT count(id) as dd FROM ".tablename($this->table_list)." WHERE weid=".$weid." and rid= ".$rid." and zonyunum >= ".$ztotal."");
		$sharepms=$count['dd'];//排名
		//查询同`排名数
		if ($zonyutime!='') {
		    $countt= pdo_fetch("SELECT count(id) as dd FROM ".tablename($this->table_list)." WHERE weid=".$weid." and rid= ".$rid." and zonyunum = ".$ztotal." and zonyutime>".$zonyutime."");
		    $sharepmt=$countt['dd'];//同`排名数
		}else{
		    $sharepmt=0;
		}

		$sharepm=$sharepms-$sharepmt;//排名


		$count = pdo_fetch('SELECT count(id) as dd FROM '.tablename($this->table_list).' WHERE weid=:weid and rid= :rid', array(':weid' => $weid,':rid' => $rid));
		$listtotal = $count['dd'];//总参与人数
		
		//取得抢礼品数据
		if(!empty($uid)) {
		    $list = pdo_fetchall('SELECT * FROM '.tablename($this->table_data).'  WHERE weid= :weid and uid = :uid and rid = :rid order by `zonyutime` desc LIMIT '.$zonyu_shownum.'', array(':weid' => $_W['weid'], ':uid' => $uid, ':rid' => $rid) );			
		}		
		//整理数据进行页面显示
		$profiles = fans_search($fromuser, array('realname', 'mobile'));
		if (strpos($reply['picture'], 'http') === false) {
			$imgurl=$_W['attachurl'] . $reply['picture'];
		}else{
			$imgurl=$reply['picture'];
		}
      	$title = $reply['title'];
		$staturl=$_W['siteroot'].$this->createMobileUrl('statzonyu', array('rid' => $rid,'fromuser' => $fromuser));
		$jumpurl = $reply['zonyuurl'];
	
	    
		$user_agent = $_SERVER['HTTP_USER_AGENT'];
	    /* if (strpos($user_agent, 'MicroMessenger') === false) {
			echo "本页面仅支持微信访问!非微信浏览器禁止浏览!";
			exit;
	    } */
		
			include $this->template('oauth2');
	    
	}
	
	public function doMobileoauth2() {
		global $_GPC,$_W;
		$weid = $_W['weid'];//当前公众号ID
		//用户不授权返回提示说明
		if ($_GPC['code']=="authdeny"){
		    $url = $_W['siteroot'].$this->createMobileUrl('oauth', array('rid' => $_GPC['rid'],'fromuser' => $_GPC['fromuser']));
			header("location:$url");
		}
		//高级接口取未关注用户Openid
		if (isset($_GPC['code'])){
		    //第二步：获得到了OpenID
		    $appid = $_W['account']['key'];
		    $secret = $_W['account']['secret'];
			$serverapp = $_W['account']['level'];	//是否为高级号
			if ($serverapp!=2) {//普通号
				$cfg = $this->module['config'];
			    $appid = $cfg['appid'];
			    $secret = $cfg['secret'];
			}//借用的
			$state = $_GPC['state'];//1为关注用户, 0为未关注用户
			
		    $rid = $_GPC['rid'];
			//查询活动时间
			$reply = pdo_fetch("SELECT end_time FROM ".tablename($this->table_reply)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));
			$end_time = $reply['end_time'];
			$day_cookies = round(($reply['end_time']-time())/3600/24);
			//还有多少天活动结束
		    $fromuser =  $_GPC['fromuser'];
		    $code = $_GPC['code'];			
		    $oauth2_code = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$appid."&secret=".$secret."&code=".$code."&grant_type=authorization_code";
		    $content = ihttp_get($oauth2_code);
		    $token = @json_decode($content['content'], true);
			if(empty($token) || !is_array($token) || empty($token['access_token']) || empty($token['openid'])) {
				echo '<h1>获取微信公众号授权'.$code.'失败[无法取得token以及openid], 请稍后重试！ 公众平台返回原始数据为: <br />' . $content['meta'].'<h1>';
				exit;
			}
		    $from_user = $token['openid'];
			//再次查询是否为关注用户
			$profile  = fans_search($from_user, array('follow'));			
			if ($profile['follow']==1){//关注用户直接获取信息	
			    $state = 1;
			}else{//未关注用户跳转到授权页
				$url = $_W['siteroot'].$this->createMobileUrl('oauth2', array('rid' => $rid,'fromuser' => $fromuser));
				$oauth2_code = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$appid."&redirect_uri=".urlencode($url)."&response_type=code&scope=snsapi_userinfo&state=0#wechat_redirect";				
				header("location:$oauth2_code");
			}
			//未关注用户和关注用户取全局access_token值的方式不一样
			if ($state==1){
			    $oauth2_url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$appid."&secret=".$secret."";
			    $content = ihttp_get($oauth2_url);
			    $token_all = @json_decode($content['content'], true);
			    if(empty($token_all) || !is_array($token_all) || empty($token_all['access_token'])) {
				    echo '<h1>获取微信公众号授权失败[无法取得access_token], 请稍后重试！ 公众平台返回原始数据为: <br />' . $content['meta'].'<h1>';
				    exit;
			    }
				$access_token = $token_all['access_token'];
				$oauth2_url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=".$access_token."&openid=".$from_user."&lang=zh_CN";
			}else{
			    $access_token = $token['access_token'];
				$oauth2_url = "https://api.weixin.qq.com/sns/userinfo?access_token=".$access_token."&openid=".$from_user."&lang=zh_CN";
			}
			
			//使用全局ACCESS_TOKEN获取OpenID的详细信息			
			$content = ihttp_get($oauth2_url);
			$info = @json_decode($content['content'], true);
			if(empty($info) || !is_array($info) || empty($info['openid'])  || empty($info['nickname']) ) {
				echo '<h1>获取微信公众号授权失败[无法取得info], 请稍后重试！ 公众平台返回原始数据为: <br />' . $content['meta'].'<h1>';
				exit;
			}
			
			if (!empty($info["headimgurl"])) {
				$info['avatar']='avatar/'.$info["openid"].'.jpg';
				//$filedata=GrabImage($info['headimg']);
				
				//file_write($info['avatar'], $filedata);
			}else{
				$info['headimgurl']='avatar_11.jpg';
			}
			if ($serverapp == 2) {//普通号
				$row = array(
					'weid' => $_W['weid'],
					'nickname'=>$info["nickname"],
					'realname'=>$info["nickname"],
					'gender' => $info['sex'],
					'from_user' => $info['openid']
				);
				if(!empty($info["country"])){
					$row['country']=$info["country"];
				}
				if(!empty($info["province"])){
					$row['province']=$info["province"];
				}
				if(!empty($info["city"])){
					$row['city']=$info["city"];
				}
				
				fans_update($_W['fans']['from_user'], $row);
				pdo_update('fans', array('avatar'=>$info["headimgurl"]), array('from_user' => $_W['fans']['from_user']));	
			}
			
			if($serverapp != 2  && !(empty($_W['fans']['from_user']))) {//普通号
				$row = array(
					'nickname'=> $info["nickname"],
					'realname'=> $info["nickname"],
					'gender'  => $info['sex']
				);
				if(!empty($info["country"])){
					$row['country']=$info["country"];
				}
				if(!empty($info["province"])){
					$row['province']=$info["province"];
				}
				if(!empty($info["city"])){
					$row['city']=$info["city"];
				}
				
				fans_update($_W['fans']['from_user'], $row);
				
				pdo_update('fans', array('avatar'=>$info["headimgurl"]), array('from_user' => $_W['fans']['from_user']));	
			}
		    $headimgurl = $info['headimgurl'];
			$nickname = $info['nickname'];
			//设置cookie信息
			setcookie("user_oauth2_headimgurl", $headimgurl, time()+3600*24*$day_cookies);
			setcookie("user_oauth2_nickname", $nickname, time()+3600*24*$day_cookies);
			setcookie("user_oauth2_openid", $from_user, time()+3600*24*$day_cookies);
			//取用户信息直接跳转
	    	$url = $_W['siteroot'].$this->createMobileUrl('statdo', array('rid' => $rid,'fromuser' => $fromuser,'from_user' => $from_user,'nickname' => $nickname,'headimgurl' => $headimgurl));
	        header("location:$url");
		}else{
			echo '<h1>不是高级认证号或网页授权域名设置出错!</h1>';
			exit;		
		}
	
	}
	
	
	public function doMobileuserinfo() {
		global $_GPC,$_W;
		$weid = $_W['weid'];//当前公众号ID
		
		//用户不授权返回提示说明
		if ($_GPC['code']=="authdeny"){
		    $url = $_W['siteroot'].$this->createMobileUrl('index', array());
			header("location:$url");
		}
		//高级接口取未关注用户Openid
		if (isset($_GPC['code'])){
		    //第二步：获得到了OpenID
		    $appid = $_W['account']['key'];
		    $secret = $_W['account']['secret'];
			$serverapp = $_W['account']['level'];	//是否为高级号
			if ($serverapp!=2) {//普通号
				$cfg = $this->module['config'];
			    $appid = $cfg['appid'];
			    $secret = $cfg['secret'];
			}//借用的
			$state = $_GPC['state'];//1为关注用户, 0为未关注用户
			
		    $rid = $_GPC['rid'];
			//查询活动时间
			$reply = pdo_fetch("SELECT end_time FROM ".tablename($this->table_reply)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));
			$end_time = $reply['end_time'];
			$day_cookies = round(($reply['end_time']-time())/3600/24);
			//还有多少天活动结束
		    $fromuser =  $_GPC['fromuser'];
		    $code = $_GPC['code'];			
		    $oauth2_code = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$appid."&secret=".$secret."&code=".$code."&grant_type=authorization_code";
		    $content = ihttp_get($oauth2_code);
		    $token = @json_decode($content['content'], true);
			if(empty($token) || !is_array($token) || empty($token['access_token']) || empty($token['openid'])) {
				echo '<h1>获取微信公众号授权'.$code.'失败[无法取得token以及openid], 请稍后重试！ 公众平台返回原始数据为: <br />' . $content['meta'].'<h1>';
				exit;
			}
		    $from_user = $token['openid'];
			//再次查询是否为关注用户
			$profile  = fans_search($from_user, array('follow'));	
	
			if ($profile['follow']==1){//关注用户直接获取信息	
			    $state = 1;
			}else{//未关注用户跳转到授权页
				$url = $_W['siteroot'].$this->createMobileUrl('userinfo', array());
				$oauth2_code = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$appid."&redirect_uri=".urlencode($url)."&response_type=code&scope=snsapi_userinfo&state=0#wechat_redirect";				
				header("location:$oauth2_code");
			}
			//未关注用户和关注用户取全局access_token值的方式不一样
			if ($state==1){
			    $oauth2_url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$appid."&secret=".$secret."";
			    $content = ihttp_get($oauth2_url);
			    $token_all = @json_decode($content['content'], true);
			    if(empty($token_all) || !is_array($token_all) || empty($token_all['access_token'])) {
				    echo '<h1>获取微信公众号授权失败[无法取得access_token], 请稍后重试！ 公众平台返回原始数据为: <br />' . $content['meta'].'<h1>';
				    exit;
			    }
				$access_token = $token_all['access_token'];
				$oauth2_url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=".$access_token."&openid=".$from_user."&lang=zh_CN";
			}else{
			    $access_token = $token['access_token'];
				$oauth2_url = "https://api.weixin.qq.com/sns/userinfo?access_token=".$access_token."&openid=".$from_user."&lang=zh_CN";
			}
			
			//使用全局ACCESS_TOKEN获取OpenID的详细信息			
			$content = ihttp_get($oauth2_url);
			$info = @json_decode($content['content'], true);
			if(empty($info) || !is_array($info) || empty($info['openid'])  || empty($info['nickname']) ) {
				echo '<h1>获取微信公众号授权失败[无法取得info], 请稍后重试！ 公众平台返回原始数据为: <br />' . $content['meta'].'<h1>';
				exit;
			}
		        			
			
			
			if (!empty($info["headimgurl"])) {
				$info['avatar']='avatar/'.$info["openid"].'.jpg';
				//$filedata=GrabImage($info['headimg']);
				
				//file_write($info['avatar'], $filedata);
			}else{
				$info['headimgurl']='avatar_11.jpg';
			}
			if ($serverapp == 2) {//普通号
				$row = array(
					'weid' => $_W['weid'],
					'nickname'=>$info["nickname"],
					'realname'=>$info["nickname"],
					
					'gender' => $info['sex'],
					'from_user' => $info['openid'],
					
				);
				if(!empty($info["country"])){
					$row['country']=$info["country"];
				}
				if(!empty($info["province"])){
					$row['province']=$info["province"];
				}
				if(!empty($info["city"])){
					$row['city']=$info["city"];
				}
				
				fans_update($_W['fans']['from_user'], $row);
				pdo_update('fans', array('avatar'=>$info["headimgurl"]), array('from_user' => $_W['fans']['from_user']));	
			}
			
			if($serverapp != 2  && !(empty($_W['fans']['from_user']))) {//普通号
				$row = array(
					'nickname'=>$info["nickname"],
					'realname'=>$info["nickname"],
					
					'gender' => $info['sex'],
				);
				if(!empty($info["country"])){
					$row['country']=$info["country"];
				}
				if(!empty($info["province"])){
					$row['province']=$info["province"];
				}
				if(!empty($info["city"])){
					$row['city']=$info["city"];
				}
				
				fans_update($_W['fans']['from_user'], $row);
				
				pdo_update('fans', array('avatar'=>$info["headimgurl"]), array('from_user' => $_W['fans']['from_user']));	
			}
			
			
				//设置cookie信息
				
				setcookie("oauth2_headimgurl", $info["headimgurl"], time()+3600*240);
				setcookie("oauth2_nickname", $info["nickname"], time()+3600*240);
				setcookie("oauth2_openid", $$info['openid'], time()+3600*240);
				$url=$this->createMobileUrl('index');
				header("location:$url");
			
		}else{
			echo '<h1>不是高级认证号或网页授权域名设置出错X!</h1>';
			exit;		
		}
	
	}
	
	
	
	public function doMobilestatzonyu() {
	    //抢礼品分享页面显示。
		global $_GPC,$_W;
		$weid = $_W['weid'];//当前公众号ID
		$fromuser =  $_GPC['fromuser'];
		$from_user = $_W['fans']['from_user'];
		$rid  = $_GPC['rid'];//当前规则ID
		$appid = $_W['account']['key'];
		$secret = $_W['account']['secret'];
		$serverapp = $_W['account']['level'];	//是否为高级号
		if ($serverapp!=2) {//普通号
			//查询是否有借用接口
			$cfg = $this->module['config'];
			$appid = $cfg['appid'];
			$secret = $cfg['secret'];
			if(!empty($secret)){
				//查询是否已授权过 授权过直接显示`
		        if (isset($_COOKIE["user_oauth2_openid"])){
		    	    $urlcookie = $_W['siteroot'].$this->createMobileUrl('statdo', array('rid' => $rid,'fromuser' => $fromuser,'from_user' => $_COOKIE["user_oauth2_openid"],'nickname' => $_COOKIE["user_oauth2_nickname"],'headimgurl' => $_COOKIE["user_oauth2_headimgurl"]));
	                header("location:$urlcookie");
		        }else{
		            $url = $_W['siteroot'].$this->createMobileUrl('oauth2', array('rid' => $rid,'fromuser' => $fromuser));
		            //需要授权跳转到授权页
			        $oauth2_code = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$appid."&redirect_uri=".urlencode($url)."&response_type=code&scope=snsapi_base&state=0#wechat_redirect";
		            header("location:$oauth2_code");
		        }			
			}else{
                if (isset($_COOKIE["user_nooauth2no_openid"])){
		    	    $urlcookie = $_W['siteroot'].$this->createMobileUrl('statdo', array('rid' => $rid,'fromuser' => $fromuser,'from_user' => $_COOKIE["user_nooauth2no_openid"]));
	                header("location:$urlcookie");
		        }else{
				    $now = time();
				    //查询活动时间
			        $reply = pdo_fetch("SELECT end_time FROM ".tablename($this->table_reply)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));
			        $end_time = $reply['end_time'];
			        $day_cookies = round(($reply['end_time']-time())/3600/24);
			        //还有多少天活动结束
				    setcookie("user_nooauth2no_openid", $now, time()+($day_cookies*24*3600), '/');
				    $urlnocookie = $_W['siteroot'].$this->createMobileUrl('statdo', array('rid' => $rid,'fromuser' => $fromuser,'from_user' => $now));
	                header("location:$urlnocookie");
			    }
			}
		
		}else{//高级号
		    //查询是否已授权过 授权过直接显示`
		    if (isset($_COOKIE["user_oauth2_openid"])){
		    	$urlcookie = $_W['siteroot'].$this->createMobileUrl('statdo', array('rid' => $rid,'fromuser' => $fromuser,'from_user' => $_COOKIE["user_oauth2_openid"],'nickname' => $_COOKIE["user_oauth2_nickname"],'headimgurl' => $_COOKIE["user_oauth2_headimgurl"]));
	            header("location:$urlcookie");
		    }else{
		    //查询是否关注用户
		        $profile  = fans_search($from_user, array('follow'));
		        $url = $_W['siteroot'].$this->createMobileUrl('oauth2', array('rid' => $rid,'fromuser' => $fromuser));
		        if ($profile['follow']==1){//关注用户不需要授权直接获取用户信息		
		        	 $oauth2_code = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$appid."&redirect_uri=".urlencode($url)."&response_type=code&scope=snsapi_base&state=1#wechat_redirect";
		        }else{//未关注用户需要授权跳转到授权页
			         $oauth2_code = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$appid."&redirect_uri=".urlencode($url)."&response_type=code&scope=snsapi_base&state=0#wechat_redirect";
		        }
		        header("location:$oauth2_code");
		    }
	    }

	}

	public function doMobilestatdo() {
	    //抢礼品分享页面显示。
		global $_GPC,$_W;
		
		if (!isset($_COOKIE["user_oauth2_openid"])){
			 $url = $_W['siteroot'].$this->createMobileUrl('index');
			//header("location:$url");
		}
		$weid      = $_W['weid'];//当前公众号ID
		$fromuser  = $_GPC['fromuser'];
		$from_user = $_GPC['from_user'];
		$rid       = $_GPC['rid'];//当前规则ID
		$headimgurl= $_GPC['headimgurl'];//用户头像
		$nickname  = $_GPC['nickname'];//用户昵称
		$serverapp = $_W['account']['level'];	//是否为高级号
		if ($serverapp!=2) {//普通号
			//查询是否有借用接口
			$cfg = $this->module['config'];
			$appid = $cfg['appid'];
			$secret = $cfg['secret'];
			if (!empty($appid)) {
				$serverappjie = 2;			
			}
		}

		if(empty($rid)){
		    echo '<h1>分享rid出错，请联系管理员!</h1>';
			exit;
		}
		if(empty($fromuser)){
		    echo '<h1>分享人出错，请联系管理员!</h1>';
			exit;
		}
		if(empty($from_user)){
		    echo '<h1>`人出错，请联系管理员!</h1>';
			exit;
		}
		//查询分享活动规则	查询是否开始或暂停
      	if (!empty($rid)) {
			$reply = pdo_fetch("SELECT * FROM ".tablename($this->table_reply)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));
			$zonyu_show = $reply['zonyu_show'];
			$zonyu_imgtext = $reply['zonyu_imgtext'];
			$zonyu_rankshow = $reply['zonyu_rankshow'];
			$zonyu_shownum = $reply['zonyu_shownum'];
			$zonyu_ranknum = $reply['zonyu_ranknum'];
			$zonyu_type = $reply['zonyu_type'];
			$dingpic = $reply['dingpic'];
			$zanpic = $reply['zanpic'];
			
			if (substr($dingpic,0,6)=='images'){
			   $dingpic = $_W['attachurl'] . $dingpic;
			}
			if (substr($zanpic,0,6)=='images'){
			   $zanpic = $_W['attachurl'] . $zanpic;
			}
			
			if ($reply['status']==0) {
				$statzonyutitle = '<h1>活动暂停！请稍候再试！</h1>';
			}
			if (time()<$reply['start_time']) {//判断活动是否已经开始
				$statzonyutitle = '<h1>活动未开始！</h1>';
			}elseif (time()>$reply['end_time']) {//判断活动是否已经结束
				$statzonyutitle = '<h1>活动已结束！</h1>';
			}
			
 		}
	
		//计算内定排名结束
		//查询是否为分享，如果是则直接跳转开始
		if($reply['type']==0){
			$profiles = fans_search($fromuser, array('realname', 'mobile'));
		    if (empty($profiles['realname'])){
			    $statzonyutitle="没有此用户!";
		    }
			if($from_user!=$fromuser And $statzonyutitle==''){
			    $zonyuip = getip();
		        $now = time();
		        //分享人积分
		        $profile1 = fans_search($fromuser, array('realname','credit1'));
		        $credit1=$profile1['credit1'];
		        $realname1=$profile1['realname'];
		        //分享人积分
		        $credit = rand($reply['credit'],$reply['creditx']);
		       
		        
		        //积分记录相关
				//取得抢礼品数据
		 	      $list = pdo_fetch("SELECT * FROM ".tablename($this->table_list)." WHERE from_user = '".$fromuser."' and weid = '".$weid."' and rid = '".$rid."' limit 1" );		       
			    	if(!empty($list)){
						if($reply['zonyu_numtype']==0){//只限一次直接跳转
						    $zonyuurl = $reply['zonyuurl'];
			                header("location:$zonyuurl");					
					    }
			    		$zonyuid = $list['id'];
				    	//取得分享详细数据，判断浏览者是否是同一人24小时内同一IP访问
				    	if($reply['zonyu_type']==0){//IP限制
				    		$zonyu_data = pdo_fetch("SELECT * FROM ".tablename($this->table_data)." WHERE uid = '".$zonyuid."' and rid = '".$rid."' and from_user = '".$from_user."' and weid = '".$weid."' and zonyuip= '".$zonyuip."' limit 1" );					
				    	}else{//真实限制
				    		$zonyu_data = pdo_fetch("SELECT * FROM ".tablename($this->table_data)." WHERE uid = '".$zonyuid."' and rid = '".$rid."' and from_user = '".$from_user."' and weid = '".$weid."'  limit 1" );					
					    }
					
					    if(!empty($zonyu_data)){
					    	$sid		=	$zonyu_data['id'];
					    	$zonyutime	=	$zonyu_data['zonyutime'];
					    	$updatetime	=	$now-$zonyutime;
					    	//访问如果是在24小时后，更新分享数据，更新分享数
					    	if($updatetime >= ($reply['zonyu_num']*24*3600)){
						    	$zannum = $list['zonyunum']+1;
						    	$updatedata = array(
							    	'viewnum'   => $zonyu_data['viewnum']+$credit,
							    	'zonyutime' => $now								
							    	);	
							    $updatelist = array(
							    	'zonyunum' => $list['zonyunum']+$credit,
							    	'zonyutime' => $now
							    	);							
							    pdo_update($this->table_data,$updatedata,array('id' => $sid));
							    pdo_update($this->table_list,$updatelist,array('id' => $zonyuid));
														    
							    //判断是否中奖
							    if($list['zhongjiang']==0 and ($list['zonyunum']+1)>=$reply['zonyunum']){
							    	$updatezj = array(
							    	'zhongjiang' => 1
							    	);							
							        pdo_update($this->table_list,$updatezj,array('from_user' => $fromuser));
							    	//发送邮件提醒$reply['email']
							    	//邮件提醒 下单提醒
								       ihttp_email($reply['email'], '抢礼品', '您的粉丝[ '.$realname1.' ]在'.$reply['title'].'活动中，集够 '.$reply['zonyunum'].' 个`，赢取了您设定的奖励，请您及时联系客户！');
								    //邮件提醒 下单提醒
							    }//判断是否中奖完成
						    }
					    }else{
						    	$zannum = $list['zonyunum']+1;							
						    	$insertdata = array(
						    		'weid'      => $weid,
							    	'from_user' => $from_user,
							    	'avatar'    => $headimgurl,
							    	'realname'  => $nickname,
							    	'rid'       => $rid,
							    	'uid'       => $zonyuid,
							    	'zonyuip'	=> $zonyuip,
							    	'zonyutime'=> $now,
									'viewnum'=>$credit,
							    	);	
							    $updatelist = array(
							    	'zonyunum' => $list['zonyunum']+1,
							    	'zonyutime' => $now
							    	);	
							    pdo_insert($this->table_data, $insertdata);
							    pdo_update($this->table_list,$updatelist,array('id' => $zonyuid));
								
							    //判断是否中奖
							    if($list['zhongjiang']==0 and ($list['zonyunum']+1)>=$reply['zonyunum']){
							    	$updatezj = array(
							    	'zhongjiang' => 1
							    	);							
							        pdo_update($this->table_list,$updatezj,array('from_user' => $fromuser));
							    	//邮件提醒 下单提醒
								       ihttp_email($reply['email'], '抢礼品', '您的粉丝[ '.$realname1.' ]在'.$reply['title'].'活动中，集够 '.$reply['zonyunum'].' 个`，赢取了您设定的奖励，请您及时联系客户！');
								    //邮件提醒 下单提醒
							    }//判断是否中奖完成
					    }
				    }
		            
			}
			 $zonyuurl = $reply['zonyuurl'];
			 header("location:$zonyuurl");		       
		}
		//查询是否为分享，如果是则直接跳转结束
		//取得分享人的数据查询是否屏蔽
		if(!empty($fromuser)) {
			$lists = pdo_fetch('SELECT id,status,zonyunum,zonyutime FROM '.tablename($this->table_list).' WHERE from_user = :fromuser and weid = :weid and rid = :rid limit 1', array(':fromuser' => $fromuser,':weid' => $weid,':rid' => $rid));
			if(!empty($lists)){
			   $uid= $lists['id'];
			   $ztotal = $lists['zonyunum'];
			   $zonyutime = $lists['zonyutime'];
			   if ($lists['status']==0){
				   $statzonyutitle = '<h1>因作弊行为被管理员屏蔽，请告知您的朋友联系 '.$_W['account']['name'].' 管理员!</h1>'; 
			   }
			}else{
			   $uid= 0;
			   $ztotal = 0;
			}
		}
		
		//取得所有分享排名100
		$listshare = pdo_fetchall('SELECT a.*,b.realname,b.mobile FROM '.tablename($this->table_list).' as a left join '.tablename('fans').' as b on a.from_user=b.from_user  WHERE a.weid= :weid  order by `zonyunum` desc,`zonyutime` asc LIMIT 10', array(':weid' => $weid));
		/*$count= pdo_fetch("SELECT count(id) as dd FROM ".tablename($this->table_list)." WHERE weid=".$weid." and rid= ".$rid." and zonyunum >= ".$ztotal."");
		$sharepms=$count['dd'];//排名
		//查询同`排名数
		if ($zonyutime!='') {
		    $countt= pdo_fetch("SELECT count(id) as dd FROM ".tablename($this->table_list)." WHERE weid=".$weid." and rid= ".$rid." and zonyunum = ".$ztotal." and zonyutime>".$zonyutime."");
		    $sharepmt=$countt['dd'];//同`排名数
		}else{
		    $sharepmt=0;
		}

		$sharepm=$sharepms-$sharepmt;//排名
		$count = pdo_fetch('SELECT count(id) as dd FROM '.tablename($this->table_list).' WHERE weid=:weid and rid= :rid', array(':weid' => $weid,':rid' => $rid));
		$listtotal = $count['dd'];//总参与人数
		*/
		//取得抢礼品数据
		if(!empty($uid)) {
		    $list = pdo_fetchall('SELECT * FROM '.tablename($this->table_data).'  WHERE weid= :weid and uid = :uid and rid = :rid order by `zonyutime` desc LIMIT 10', array(':weid' => $_W['weid'], ':uid' => $uid, ':rid' => $rid) );			
		}		
		//整理数据进行页面显示
		//判断是否为关注用户
		//$profiles = fans_search($fromuser, array('realname', 'avatar','mobile'));
		$profiles = pdo_fetch("SELECT realname,mobile,avatar FROM ".tablename('fans')." WHERE from_user ='".$fromuser."'");
		
		if (empty($profiles['realname'])){
			$statzonyutitle="没有此用户!";
		}
		$profile  = fans_search($from_user, array('realname', 'mobile'));
		
		
		if (strpos($reply['picture'], 'http') === false) {
			if(strpos($reply['picture'], 'modules') === false) {
					$imgurl=$_W['attachurl'] . $reply['picture'];
				}else{
					$imgurl=$reply['picture'];
				}
		}else{
			$imgurl=$reply['picture'];
		}
      	$title = $reply['title'];
		$regurl=$this->createMobileUrl('statupdata');
		$staturl=$_W['siteroot'].$this->createMobileUrl('statzonyu', array('rid' => $rid,'fromuser' => $fromuser));
		$jumpurl = $reply['zonyuurl'];
		
	    $user_agent = $_SERVER['HTTP_USER_AGENT'];
	    /* if (strpos($user_agent, 'MicroMessenger') === false) {
			echo "本页面仅支持微信访问!非微信浏览器禁止浏览!";
			exit;
	    } */
		
			$btype=0;
		
		
		$replys = pdo_fetchall("SELECT * FROM ".tablename($this->table_reply)." WHERE weid = :weid and status = 1  ORDER BY `id` DESC LIMIT 3", array(':weid' => $weid));
		
		$hasselect=0;
		foreach ($replys as $k => $v) {
			$reply[$k]['lpurl']= $this->createMobileUrl('zonyu', array('rid' => $v['rid']));
			
		}
		$profiles['avatar']=str_replace('./resource/attachment/http',"http",$profiles['avatar']);
		
		if (intval($reply['zonyunum'])>0){
			$allper=100-intval($ztotal*100/$reply['zonyunum']);
		}else{
			$allper=100;
		}
		$cfg = $this->module['config'];	
		include $this->template('do');
	}

	
	public function doMobileShare() {
		//抢礼品分享页面显示。
		global $_GPC,$_W;
		$weid = $_W['weid'];//当前公众号ID
		$result = array('status' => 0, 'message' => '', 'total' => 0, 'dataid' => 0);
		
		$rid = intval($_GPC['rid']);
		$fromuser =  $_GPC['fromuser'];
		
		$list = pdo_fetch("SELECT * FROM ".tablename($this->table_list)." WHERE from_user = '".$fromuser."' and weid = '".$weid."' limit 1" );		
		if(!empty($rid)) {
		  $reply = pdo_fetch("SELECT * FROM ".tablename($this->table_reply)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));
		}
		
		//$credit=rand(intval($reply['zonyunum']/10),intval($reply['zonyunum']/3));
		$cfg = $this->module['config'];
		$zk1 = $cfg['zk1'];
		$zk2 = $cfg['zk2'];
		$credit=rand(intval($zk1),intval($zk2));
		if(empty($list)){
			$insertlistdata = array(
				'weid'      => $weid,
				'from_user' => $fromuser,
				'rid'       => $rid,
				'zonyunum'  => $credit,
				'zonyutime' => TIMESTAMP,
				'createtime' => TIMESTAMP,
				
			);
			pdo_insert($this->table_list, $insertlistdata);
			$uid=pdo_insertid();
			$insertdata = array(
				'weid'      => $weid,
				'from_user' => $fromuser,
				'avatar'    => '',
				'realname'  => '自己',
				'rid'       => $rid,
				'uid'       => $uid,
				'zonyuip'	=> getip(),
				'zonyutime'=> TIMESTAMP,
				'viewnum'=>$credit,
				'content'=>'人品爆发，自己帮自己砍价成功！',
			);	
			pdo_insert($this->table_data, $insertdata);
			$result['message']='分享成功,自砍了'.$credit.'元!';
			//$result['message']=$credit;
		}else{
		
			$result['message']='分享成功!';
		}
		
		message($result, '', 'ajax');
	}
	
	public function doMobileStat() {
		//抢礼品分享页面显示。
		global $_GPC,$_W;
        $result = array('status' => 0, 'message' => '', 'total' => 0, 'dataid' => 0);
		$operation = $_GPC['op'];
		$rid = intval($_GPC['rid']);
		$fromuser =  $_GPC['fromuser'];
		$from_user = $_GPC['from_user'];
		$headimgurl= $_GPC['headimgurl'];//用户头像
		$nickname  = $_GPC['nickname'];//用户昵称
		$weid = $_W['weid'];//当前公众号ID	
		//分享人积分
		$profile1 = fans_search($fromuser, array('realname','credit1'));
		$credit1=$profile1['credit1'];
		$realname1=$profile1['realname'];
		//分享人积分
		$zonyuip = getip();
		$now = time();	//未关注用户用cookie作为唯一值		
		if(empty($from_user)) {			
			$result['message'] = '没有取得openid无法`';
            message($result, '', 'ajax');
		}
			
		if(!empty($rid)) {
			$list = pdo_fetch("SELECT * FROM ".tablename($this->table_list)." WHERE from_user = '".$fromuser."' and weid = '".$weid."' and rid = '".$rid."' limit 1" );		
		
			$reply = pdo_fetch("SELECT * FROM ".tablename($this->table_reply)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));
			$jumpurl = $reply['zonyuurl'];
			$staturl=$_W['siteroot'].$this->createMobileUrl('stat', array('rid' => $rid,'fromuser' => $fromuser));
			$credit = rand($reply['credit'],$reply['creditx']);
			
			
			if($credit==0){
				$credit = rand($reply['credit'],$reply['creditx']);
				if($credit==0){
					$credit = rand($reply['credit'],$reply['creditx']);
				}
			}
			
			if(($reply['zonyunum']-$list['zonyunum']) <$credit){
				$credit=$reply['zonyunum']-$list['zonyunum'];
			}
			if($reply['zonyunum']==$list['zonyunum']){
				$credit=0;	
				
			}
			
			
			
		}else{
			exit;
		}
		/*
		$array14=array('大哥，砍得有点少哦~弱弱问您砍价前洗手了么？小沃要代表蓝月亮，洗干净你！','伤不起真的伤不起~千万别告诉原PO您只帮TA砍了这么一丢丢价格，不然友尽了小沃也是无能为力...','什么？！只砍了这么点？！亲，息怒！！跟小沃一起深呼吸：这个世界是多么美好~空气是多么清新~');
		$array58=array('哟~手气好像还不错哦，是不是最近有扶老奶奶过马路呀~','一刀见血！果然好刀法~那么问题来了，敢问大侠师承何处？','目测成绩不错哦，据无关部门扯，左脚盘于头顶，右手捏兰花结，用额头猛敲屏幕，可能获得更高的砍价幅度。');
		$array911=array('哇！RP爆发了有木有~一刀砍掉这么多！老板娘又要心疼好半天，少买好几套衣服了!不带这么狠的呀~','同志！您是如何做到一刀砍掉这么多的？小沃代表您的朋友感谢您为TA作出的卓越贡献！小沃也会替您向TA发去贺电！','亲，请问您是毕业于新西方烹饪学院吗？不然刀工怎会如此了得，一刀除肉，毫不留情！老板要砸锅卖铁啦！');
		$fushu='啊呀！亲您的刀砍偏啦！砍到人啦~您的朋友需要为您支付2元创可贴费，您还是回家练练刀工，下次再来吧！';
				*/
				
				
		$cfg = $this->module['config'];
		$qj1 = $cfg['qj1'];
		$qj2 = $cfg['qj2'];
		$qj3 = $cfg['qj3'];
		$qj4 = $cfg['qj4'];
		$qj5 = $cfg['qj5'];
		$hs1 = $cfg['hs1'];
		$hs2 = $cfg['hs2'];
		$hs3 = $cfg['hs3'];
		$hs4 = $cfg['hs4'];
		$hs5 = $cfg['hs5'];
		
			
		if($credit>0 && $credit<$qj1){
			//$rands=rand(0 ,count($array14)-1);
			$result['message'] = '成功砍价'.$credit.'元!'.$hs1;
		}
		if($credit>$qj2 && $credit<$qj3){
			//$rands=rand(0 ,count($array14)-1);
			$result['message'] = '成功砍价'.$credit.'元!'.$hs2;
		}
		if($credit>$qj4 && $credit<$qj5){
			//$rands=rand(0 ,count($array14)-1);
			$result['message'] = '成功砍价'.$credit.'元!'.$hs3;
		}
		if($credit<0){
			
			$result['message'] = '帮倒忙 '.$credit.'元!'.$hs4;
		}
		if($credit==0){
			$credit=0;	
			$result['message']= $hs5;
			
		}
		
		//分享人和查看人为同一人时，不参与加分直接跳转
		if($from_user==$fromuser){
			$result['message'] = '自己不能帮自己抢呀！';
			message($result, '', 'ajax');
		}else{
		    //取得抢礼品数据
		    if(!empty($fromuser)) {
				$listdata = pdo_fetchcolumn("SELECT count(*) FROM ".tablename($this->table_data)." WHERE uid = '".$zonyuid."'  and from_user = '".$from_user."' and weid = '".$weid."' and rid = '".$rid."' limit 1" );		

		   }else{
		       $result['message'] = '系统出错，求人未知';
               message($result, '', 'ajax');
		    }
			
			if(!empty($list)){
				if($listdata){
					$zannum = $list['zonyunum'];
					$result['status'] = 0;
					$result['message'] = '您已帮砍过了，此活动每个用户只限一次!';
					$result['total'] = $zannum;
					message($result, '', 'ajax');					
				}
				$zonyuid = $list['id'];
				//取得分享详细数据，判断浏览者是否是同一人24小时内同一IP访问
				/*
				if($reply['zonyu_type']==0){//IP限制
					$zonyu_data = pdo_fetch("SELECT * FROM ".tablename($this->table_data)." WHERE uid = '".$zonyuid."'  and from_user = '".$from_user."' and weid = '".$weid."' and zonyuip= '".$zonyuip."' limit 1" );					
				}else{//真实限制
					$zonyu_data = pdo_fetch("SELECT * FROM ".tablename($this->table_data)." WHERE uid = '".$zonyuid."'  and from_user = '".$from_user."' and weid = '".$weid."'  limit 1" );					
				}
				*/
				$zonyu_data = pdo_fetch("SELECT * FROM ".tablename($this->table_data)." WHERE uid = '".$zonyuid."'  and from_user = '".$from_user."' and weid = '".$weid."'  limit 1" );					

				if(!empty($zonyu_data)){
					$sid		=	$zonyu_data['id'];
					$zonyutime	=	$zonyu_data['zonyutime'];
					$updatetime	=	$now-$zonyutime;
					//访问如果是在24小时后，更新分享数据，更新分享数
					if($updatetime >= ($reply['zonyu_num']*24*3600*30)){
						$zannum = $list['zonyunum']+$credit;
						$updatedata = array(
							'viewnum'   => $zonyu_data['viewnum']+$credit,
							'zonyutime' => $now								
							);	
						$updatelist = array(
							'zonyunum' => $list['zonyunum']+$credit,
							'zonyutime' => $now
							);							
						pdo_update($this->table_data,$updatedata,array('id' => $sid));
						$dataid = $sid;//取分享`人的id
						pdo_update($this->table_list,$updatelist,array('id' => $zonyuid));
						
						//判断是否中奖
						if($list['zhongjiang']==0 and ($list['zonyunum']+$credit)>=$reply['zonyunum']){
							$updatezj = array(
							'zhongjiang' => 1
							);							
							pdo_update($this->table_list,$updatezj,array('from_user' => $fromuser));
							//发送邮件提醒$reply['email']
							//邮件提醒 下单提醒
							  // ihttp_email($reply['email'], '抢礼品', '您的粉丝[ '.$realname1.' ]在'.$reply['title'].'活动中，集够 '.$reply['zonyunum'].' 个`，赢取了您设定的奖励，请您及时联系客户！');
							//邮件提醒 下单提醒
						}//判断是否中奖完成
					}else{
						//转化为时间
						$num = (($reply['zonyu_num']*24*3600*30)-$updatetime);
						$hour = floor($num/3600);
						$minute = floor(($num-3600*$hour)/60);
						$second = floor((($num-3600*$hour)-60*$minute)%60);
						$num = $hour.'小时';

						$zannum = $list['zonyunum'];
						$result['status'] = 0;
						$result['message'] = '您已帮砍了，不能再砍啦!';
						$result['total'] = $zannum;
						message($result, '', 'ajax');
					}
				}else{
					$cfg = $this->module['config'];
					$sjxz = $cfg['sjxz'];
					if(($list['zonyutime']+ ($sjxz*3600))> TIMESTAMP ){
						$zannum = $list['zonyunum']+$credit;							
						$insertdata = array(
							'weid'      => $weid,
							'from_user' => $from_user,
							'avatar'    => $headimgurl,
							'realname'  => $nickname,
							'rid'       => $rid,
							'uid'       => $zonyuid,
							'zonyuip'	=> $zonyuip,
							'zonyutime'=> $now,
							'viewnum'=>$credit,
							'content'=>$result['message'],
						);	
						$updatelist = array(
							'zonyunum' => $list['zonyunum']+$credit,
							'zonyutime' => $now
						);	
						
						pdo_insert($this->table_data, $insertdata);
						$dataid = pdo_insertid();//取分享`人的id
						pdo_update($this->table_list,$updatelist,array('id' => $zonyuid));							
						
						//判断是否中奖
						if($list['zhongjiang']==0 and ($list['zonyunum']+$credit)>=$reply['zonyunum']){
							$updatezj = array(
							'zhongjiang' => 1
							);							
							pdo_update($this->table_list,$updatezj,array('from_user' => $fromuser));
							//邮件提醒 下单提醒
							   //ihttp_email($reply['email'], '抢礼品', '您的粉丝[ '.$realname1.' ]在'.$reply['title'].'活动中，集够 '.$reply['zonyunum'].' 个`，赢取了您设定的奖励，请您及时联系客户！');
							//邮件提醒 下单提醒
						}//判断是否中奖完成
					}else{
						$cfg = $this->module['config'];
						$sjxz = $cfg['sjxz'];
						$result['status'] = 0;
						$result['message'] = '超过'.$sjxz.'小时,不能再砍啦!';
						$result['total'] = $zannum;
						message($result, '', 'ajax');
					
					}
				}
				
			}
			
			
			$result['status'] = 1;
			
			$result['total'] = $zannum;
			$result['dataid'] = $dataid;
			message($result, '', 'ajax');
		}
	}
	
	
	
	public function doMobileBuy(){
			global $_W,$_GPC;
			$data = array(
                'weid' => $_W['weid'],
                'from_user' => $_W['fans']['from_user'],
                'ordersn' => date('md') . random(4, 1),
                'price' => $goodsprice + $dispatchprice,
				'goodsprice' => $goodsprice,
                'status' => 0,
				'address'=>'',
				'sendtype' => intval($_GPC['sendtype']),
				'paytype' => '2',
                'goodstype' => intval($cart['type']),
                'remark' => $_GPC['remark'],
                'createtime' => TIMESTAMP,
            );
            pdo_insert('hlzonyu_order', $data);
			$orderid = pdo_insertid();
			message('提交订单成功，现在跳转至付款页面...', $this->createMobileUrl('pay', array('orderid' => $orderid)), 'success');
	}
	
	public function doMobilePay() {
        global $_W, $_GPC;
       checkauth();
        $orderid = intval($_GPC['orderid']);
        $order = pdo_fetch("SELECT * FROM " . tablename('hlzonyu_order') . " WHERE id = :id", array(':id' => $orderid));
        if ($order['status'] != '0') {
            message('抱歉，您的订单已经付款或是被关闭，请重新进入付款！', $this->createMobileUrl('index'), 'error');
        }
        if ($order['price'] == '0') {
                $this->payResult(array('tid' => $orderid, 'from' => 'return', 'type' => 'credit2'));
                  message('订单完成', create_url('mobile/module/index', array('name' => 'hlzonyu', 'weid' => $_W['weid'])), 'error');
            
            }
        if (checksubmit()) {
			if ($order['price'] == '0') {
                $this->payResult(array('tid' => $orderid, 'from' => 'return', 'type' => 'credit2'));
                exit;
            }
            if ($order['paytype'] == 1 && $_W['fans']['credit2'] < $order['price']) {
                message('抱歉，您帐户的余额不够支付该订单，请充值！', create_url('mobile/module/charge', array('name' => 'member', 'weid' => $_W['weid'])), 'error');
            }
            
        }
        $params['tid'] = $orderid;
        $params['user'] = $_W['fans']['from_user'];
        $params['fee'] = $order['price'];
        $params['title'] = $_W['account']['name'].$order['goodsname'];
        $params['ordersn'] = $order['ordersn'];
        $params['virtual'] = $order['goodstype'] == 2 ? true : false;
		
        include $this->template('pay');
    }
	
	public function payResult($params) {
        $fee = intval($params['fee']);
        $data = array('status' => $params['result'] == 'success' ? 1 : 0);
        if ($params['type'] == 'wechat') {
            $data['transid'] = $params['tag']['transaction_id'];
        }
        pdo_update('hlzonyu_order', $data, array('id' => $params['tid']));
        if ($params['from'] == 'return') {
            
            if ($params['type'] == 'credit2') {
                message('支付成功！', $this->createMobileUrl('index'), 'success');
            } else {
                message('支付成功！', '../../' . $this->createMobileUrl('index'), 'success');
            }
        }
    }
	
	private function getFeedbackType($type) {
        $types = array(1 => '维权', 2 => '告警');
        return $types[intval($type)];
    }

    private function getFeedbackStatus($status) {
        $statuses = array('未解决', '用户同意', '用户拒绝');
        return $statuses[intval($status)];
    }
	
	private function changeWechatSend($id, $status, $msg = '') {
        global $_W;
        $paylog = pdo_fetch("SELECT plid, openid, tag FROM " . tablename('paylog') . " WHERE tid = '{$id}' AND status = 1 AND type = 'wechat'");
        if (!empty($paylog['openid'])) {
            $paylog['tag'] = iunserializer($paylog['tag']);
            $send = array(
                'appid' => $_W['account']['payment']['wechat']['appid'],
                'openid' => $paylog['openid'],
                'transid' => $paylog['tag']['transaction_id'],
                'out_trade_no' => $paylog['plid'],
                'deliver_timestamp' => TIMESTAMP,
                'deliver_status' => $status,
                'deliver_msg' => $msg,
            );
            $sign = $send;
            $sign['appkey'] = $_W['account']['payment']['wechat']['signkey'];
            ksort($sign);
            foreach ($sign as $key => $v) {
                $key = strtolower($key);
                $string .= "{$key}={$v}&";
            }
            $send['app_signature'] = sha1(rtrim($string, '&'));
            $send['sign_method'] = 'sha1';
            $token = account_weixin_token($_W['account']);
            $sendapi = 'https://api.weixin.qq.com/pay/delivernotify?access_token=' . $token;
            $response = ihttp_request($sendapi, json_encode($send));
            $response = json_decode($response['content'], true);
            if (empty($response)) {
                message('发货失败，请检查您的公众号权限或是公众号AppId和公众号AppSecret！');
            }
            if (!empty($response['errcode'])) {
                message($response['errmsg']);
            }
        }
    }

    public function doMobileConfirm() {
        global $_W, $_GPC;
		checkauth();
		
		$rid = $_GPC['rid'];
		
		$from_user=$_W['fans']['from_user'];
		
      	if (!empty($rid)) {
			$reply = pdo_fetch("SELECT * FROM ".tablename($this->table_reply)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));			
		}	
		if($reply){
			$mybargain=pdo_fetch("select * from ".tablename($this->table_list)." WHERE from_user='{$from_user}' and rid={$rid} limit 1");
		}else{
			echo 'OK';
			exit;
		}
		//var_dump($mybargain);
		$order=array();
		$orders=pdo_fetch("select * from ".tablename('hlzonyu_order')." WHERE from_user='{$from_user}' AND rid='{$rid}' limit 1");
		
		
		if($orders){
			if ($orders['status'] == '0') {
				message('你已提交订单啦', $this->createMobileUrl('index', array()), 'success');
			}else{
				message('抱歉，您的订单已经付款或是被关闭！', $this->createMobileUrl('index'), 'error');
			}
		}else{
		$order['price']=intval($reply['productprice'])-intval($mybargain['zonyunum']);
			if($mybargain){
				
			}else{
				$order['price']=$reply['productprice'];
			}
			
			$order['rid']=$rid;
			$order['goodsname']=$reply['title'];
		}
		$profile=pdo_fetch('SELECT * FROM '.tablename('fans').' WHERE from_user=:from_user AND weid=:weid LIMIT 1',array(':from_user'=>$_W['fans']['from_user'],':weid'=>$_W['weid']));
	
		
		if(checksubmit('submit')) {
			
			$rdsn=rand(1000,9000);
			$order['address']=$_GPC['provance'].$_GPC['city'].$_GPC['area'].$_GPC['address'];
			$order['weid']=$_W['weid'];
			$order['from_user']=$_W['fans']['from_user'];
			$order['weid']=$_W['weid'];
			$order['status']=0;
			$order['ordersn']=date('Ymdhis').$rdsn;
			$order['paytype']=2;
			$order['createtime']=TIMESTAMP;
			$order['goodsprice']=$reply['productprice'];
			$order['remark']=$_GPC['remark'];
			$order['tjname']=$_GPC['tjname'];
			pdo_insert('hlzonyu_order',$order);
			$orderid=pdo_insertid();
			
			$upfans=array(
				'realname'=>$_GPC['realname'],
				'mobile'=>$_GPC['mobile'],
			);
			pdo_update('fans',$upfans,array('from_user'=>$_W['fans']['from_user']));
			
			//message('提交订单成功，现在跳转至付款页面...', $this->createMobileUrl('pay', array('orderid' => $orderid)), 'success');
			message('提交订单成功!', $this->createMobileUrl('index', array()), 'success');
			
		}
		
		
		include $this->template('confirm');
	}
	
	public function doWebOrder() {
        global $_W, $_GPC;
        $operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
        if ($operation == 'display') {
            $pindex = max(1, intval($_GPC['page']));
            $psize = 20;
            
            
            $list = pdo_fetchall("SELECT o.*,f.realname,f.nickname,f.mobile FROM " . tablename('hlzonyu_order') . " AS o left join " . tablename('fans') . " as f on o.from_user=f.from_user  WHERE o.weid = '{$_W['weid']}'  ORDER BY o.createtime DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
            $total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('hlzonyu_order') . " WHERE weid = '{$_W['weid']}' ");
            $pager = pagination($total, $pindex, $psize);
            
            
        } 
        include $this->template('order');
    }
	
	
	
}
	//$img=GrabImage("http://www.ccc.com/a.jpg","");
function GrabImage($url) {
   if($url==""):return false;endif;
   
   ob_start();
   readfile($url);
   $img = ob_get_contents();
   ob_end_clean();
  
   return $img;

}
