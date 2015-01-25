<?php
/**
 * 幸运拆礼盒模块定义
 *
 */
defined('IN_IA') or exit('Access Denied');

class stonefish_chailiheModuleSite extends WeModuleSite {	
	
	public $table_reply  = 'stonefish_chailihe_reply';
	public $table_list   = 'stonefish_chailihe_userlist';	
	public $table_data   = 'stonefish_chailihe_data';
	public $table_gift   = 'stonefish_chailihe_gift';

	public function doMobilelisthome() {
		//这个操作被定义用来呈现 微站首页导航图标
		$this->doMobilelistentry();	
	}
	
	public function getTiles($keyword = '') {
		global $_GPC,$_W;
		$weid = $_W['weid'];
		$urls = array();
		$list = pdo_fetchall("SELECT name, id FROM ".tablename('rule')." WHERE weid = ".$weid." and module = 'stonefish_chailihe'".(!empty($keyword) ? " AND name LIKE '%{$keyword}%'" : ''));
		if (!empty($list)) {
			foreach ($list as $row) {
				$urls[] = array('title'=>$row['name'], 'url'=> $this->createMobileUrl('chailihe', array('rid' => $row['id'])));
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

		$reply = pdo_fetchall("SELECT * FROM ".tablename($this->table_reply)." WHERE weid = :weid and status = 1 and start_time<".$time."  and end_time>".$time." ORDER BY `end_time` DESC", array(':weid' => $weid));

		foreach ($reply as $mid => $replys) {
			$reply[$mid]['num'] = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename($this->table_list)." WHERE weid = :weid and rid = :rid", array(':weid' => $_W['weid'], ':rid' => $replys['rid']));
			$reply[$mid]['is'] = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename($this->table_list)." WHERE weid = :weid and rid = :rid and from_user = :from_user", array(':weid' => $weid, ':rid' => $replys['rid'], ':from_user' => $from_user));
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
	
	public function doMobilechailihe() {
		//关健词触发页面显示。
		global $_GPC,$_W;
		$weid = $_W['weid'];//当前公众号ID		
		$rid = $_GPC['rid'];
		$fromuser = $_W['fans']['from_user'];
		if (empty($fromuser)) {
		    $fromuser = $_GPC['fromuser'];
		}

      	if (!empty($rid)) {
			$reply = pdo_fetch("SELECT * FROM ".tablename($this->table_reply)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));			
			$number_num_day = $reply['number_num_day'];
			$picture = $reply['picture'];
			$picbg01 = $reply['picbg01'];			
			$share_shownum = $reply['share_shownum'];
			$music = $reply['music'];
			$musicbg = $reply['musicbg'];
			//幻灯片开始
			$imgpic01 = $reply['imgpic01'];
			$imgpic02 = $reply['imgpic02'];
			$imgpic03 = $reply['imgpic03'];
			$imgpic04 = $reply['imgpic04'];
			$imgpic05 = $reply['imgpic05'];
			if (substr($imgpic01,0,6)=='images'){
			   $imgpic01 = $_W['attachurl'] . $imgpic01;
			}
			if (substr($imgpic02,0,6)=='images'){
			   $imgpic02 = $_W['attachurl'] . $imgpic02;
			}
			if (substr($imgpic03,0,6)=='images'){
			   $imgpic03 = $_W['attachurl'] . $imgpic03;
			}
			if (substr($imgpic04,0,6)=='images'){
			   $imgpic04 = $_W['attachurl'] . $imgpic04;
			}
			if (substr($imgpic05,0,6)=='images'){
			   $imgpic05 = $_W['attachurl'] . $imgpic05;
			}
			//幻灯片结束
			if ($number_num_day==0){
			   $number_num_day = '每天不限次数领取';
			}else{
			   $number_num_day = '每天可领'.$number_num_day.'次';
			}
			if (substr($picture,0,6)=='images'){
			   $picture = $_W['attachurl'] . $picture;
			}else{
			   $picture = $_W['siteroot'] . $picture;
			}
			if (substr($picbg01,0,6)=='images'){
			   $picbg01 = $_W['attachurl'] . $picbg01;
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
		//判断是否还可以领取礼盒
		if(!empty($fromuser)) {
						
			$count = pdo_fetch("SELECT count(id) as dd FROM ".tablename($this->table_list)." WHERE weid=".$weid." AND rid= ".$rid." AND from_user='".$fromuser."'");
		    $giftnum = $count['dd'];//领取礼盒数总数

			$todaytimestamp = strtotime(date('Y-m-d'));
			$count = pdo_fetch("SELECT count(id) as dd FROM ".tablename($this->table_list)." WHERE weid=".$weid." AND rid= ".$rid." AND from_user='".$fromuser."' AND datatime >= '".$todaytimestamp."'");
		    $daygiftnum = $count['dd'];//领取礼盒数今日数


			$gift_num = $reply['number_num']-$giftnum;	//总数还有多少次机会
			$gift_num_day = $reply['number_num_day']-$daygiftnum;//今日还有多少次机会
			$giftlihe = 0;//默认没有机会
			if ($gift_num>=1) {	
                if($gift_num_day>=1){
				   	if($gift_num<$gift_num_day){
					    $giftlihe = $gift_num;
					}else{
					    $giftlihe = $gift_num_day;
					}
				}						
			}			
		}		
		//中奖用户列表
		$listshare = pdo_fetchall('SELECT a.*,b.lihetitle FROM '.tablename($this->table_list).' as a left join '.tablename($this->table_gift).' as b on a.liheid=b.id  WHERE a.zhongjiang >= 1 and a.weid= :weid AND a.rid = :rid order by `sharetime` desc LIMIT '.$share_shownum.'', array(':weid' => $weid,':rid' => $rid));

		$count = pdo_fetch("SELECT count(id) as dd FROM ".tablename($this->table_list)." WHERE weid=".$weid." AND rid= ".$rid."");
		$listtotal = $count['dd'];//总参与人数
		$count = pdo_fetch("SELECT count(id) as dd FROM ".tablename($this->table_gift)." WHERE rid= ".$rid."");
		$gifttotal = $count['dd'];//总礼盒数

		//整理数据进行页面显示
		//判断是否为关注用户才能领取
		if($reply['subscribe']==1){
		    $subscribe=0;//默认没有关注没有办法领取
		    $profile = fans_search($fromuser, array('follow'));
		    if ($profile['follow']==1) {
			    $subscribe=1;
		    }
		}else{
		    $subscribe=1;//默认没有关注可以领取
		}
		//判断是否为关注用户才能领取
      	$title = $reply['title'];
		$regurl= $_W['siteroot'].$this->createMobileUrl('reglihe', array('rid' => $rid,'fromuser' => $fromuser));
		$mylihe= $_W['siteroot'].$this->createMobileUrl('mylihe', array('rid' => $rid,'fromuser' => $fromuser));
		$shareurl = $_W['siteroot'].$this->createMobileUrl('shareuserview', array('rid' => $rid));//分享URL
		$guanzhu = $reply['shareurl'];//没有关注用户跳转引导页

		
		$user_agent = $_SERVER['HTTP_USER_AGENT'];
		if (strpos($user_agent, 'MicroMessenger') === false) {
			echo "本页面仅支持微信访问!非微信浏览器禁止浏览!";
			//include $this->template('chailihe');
		} else { 
			include $this->template('chailihe');
		}

	}
	public function doMobilereglihe() {
		//分享集赞分享页面显示。
		global $_GPC,$_W;
		$weid = $_W['weid'];//当前公众号ID
		$rid  = $_GPC['rid'];//当前规则ID		
		$fromuser = $_GPC['fromuser'];
		//活动规则
		if (!empty($rid)) {
			$reply = pdo_fetch("SELECT * FROM ".tablename($this->table_reply)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));
			$picture = $reply['picture'];
			$picbg02 = $reply['picbg02'];
			if (substr($picture,0,6)=='images'){
			   $picture = $_W['attachurl'] . $picture;
			}else{
			   $picture = $_W['siteroot'] . $picture;
			}
			if (substr($picbg02,0,6)=='images'){
			   $picbg02 = $_W['attachurl'] . $picbg02;
			}
		}
		//判断是否还可以领取礼盒
		if(!empty($fromuser)) {
			//取用户资料
			$profile  = fans_search($fromuser, array('follow','realname','mobile'));
			
			$count = pdo_fetch("SELECT count(id) as dd FROM ".tablename($this->table_list)." WHERE weid=".$weid." AND rid= ".$rid." AND from_user='".$fromuser."'");
		    $giftnum = $count['dd'];//领取礼盒数总数

			$todaytimestamp = strtotime(date('Y-m-d'));
			$count = pdo_fetch("SELECT count(id) as dd FROM ".tablename($this->table_list)." WHERE weid=".$weid." AND rid= ".$rid." AND from_user='".$fromuser."' AND datatime >= '".$todaytimestamp."'");
		    $daygiftnum = $count['dd'];//领取礼盒数今日数


			$gift_num = $reply['number_num']-$giftnum;	//总数还有多少次机会
			$gift_num_day = $reply['number_num_day']-$daygiftnum;//今日还有多少次机会
			$giftlihe = 0;//默认没有机会
			if ($gift_num>=1) {	
                if($gift_num_day>=1){
				   	if($gift_num<$gift_num_day){
					    $giftlihe = $gift_num;
					}else{
					    $giftlihe = $gift_num_day;
					}
				}						
			}
			//判断是否已领取过 判断是否弹出领取层
			$Needregister = 'true';
            if($giftnum>=1){
			    $Needregister = 'false';			
			}			
		}
		//判断是否为关注用户才能领取
		if($reply['subscribe']==1){
		    $subscribe=0;//默认没有关注没有办法领取
		    if ($profile['follow']==1) {
			    $subscribe=1;
		    }
		}else{
		    $subscribe=1;//默认没有关注可以领取
		}
		//判断是否为关注用户才能领取
		//礼盒列表
		$listlihe = pdo_fetchall('SELECT * FROM '.tablename($this->table_gift).'  WHERE rid = :rid order by `id`', array(':rid' => $rid));
		$shareurl = $_W['siteroot'].$this->createMobileUrl('shareuserview', array('rid' => $rid));
		$regurl = $_W['siteroot'].$this->createMobileUrl('reguser', array('rid' => $rid,'fromuser' => $fromuser));
		$mylihe = $_W['siteroot'].$this->createMobileUrl('mylihe', array('rid' => $rid,'fromuser' => $fromuser));
		$shouquan = base64_encode($_SERVER ['HTTP_HOST'].'anquan_ma_chailihe');
        $musicpath = './source/modules/stonefish_chailihe/template/images/music/';
        $telpass = '';//以后用于手机短信验证
		$guanzhu = $reply['shareurl'];//没有关注用户跳转引导页
		
		$user_agent = $_SERVER['HTTP_USER_AGENT'];
		if (strpos($user_agent, 'MicroMessenger') === false) {
			echo "本页面仅支持微信访问!非微信浏览器禁止浏览!";
			//include $this->template('reglihe');
		} else { 
			include $this->template('reglihe');
		}
	}

	public function doMobilereguser() {
		//分享页面显示。
		global $_GPC,$_W;
		$weid = $_W['weid'];//当前公众号ID
		$rid  = $_GPC['rid'];//当前规则ID		
		$fromuser = $_GPC['fromuser'];
		if(empty($_GPC['info-name'])){
		    $usrInfo = array('errno'=>1,'error'=>'请输入真实姓名！');
			$jsdata=json_encode($usrInfo);
		    echo $jsdata;
			exit;
		}
		if(empty($_GPC['info-tel'])){
		    $usrInfo = array('errno'=>1,'error'=>'请输入联系电话！');
			$jsdata=json_encode($usrInfo);
		    echo $jsdata;
			exit;
		}	
		if($_GPC['info-prize']==0){
		    $usrInfo = array('errno'=>1,'error'=>'请选择礼盒');
			$jsdata=json_encode($usrInfo);
		    echo $jsdata;
			exit;
		}
		//活动规则
		if (!empty($rid)) {
			$reply = pdo_fetch("SELECT * FROM ".tablename($this->table_reply)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));
		}
		//检查是否还有机会领取
		if(!empty($fromuser)) {			
			$count = pdo_fetch("SELECT count(id) as dd FROM ".tablename($this->table_list)." WHERE weid=".$weid." AND rid= ".$rid." AND from_user='".$fromuser."'");
		    $giftnum = $count['dd'];//领取礼盒数总数

			$todaytimestamp = strtotime(date('Y-m-d'));
			$count = pdo_fetch("SELECT count(id) as dd FROM ".tablename($this->table_list)." WHERE weid=".$weid." AND rid= ".$rid." AND from_user='".$fromuser."' AND datatime >= '".$todaytimestamp."'");
		    $daygiftnum = $count['dd'];//领取礼盒数今日数


			$gift_num = $reply['number_num']-$giftnum;	//总数还有多少次机会
			$gift_num_day = $reply['number_num_day']-$daygiftnum;//今日还有多少次机会
			$giftlihe = 0;//默认没有机会
			if ($gift_num>=1) {	
                if($gift_num_day>=1){
				   	if($gift_num<$gift_num_day){
					    $giftlihe = $gift_num;
					}else{
					    $giftlihe = $gift_num_day;
					}
				}						
			}					
		}
		if($giftlihe==0){
		    $usrInfo = array('errno'=>1,'error'=>'今天已没有机会领取了');
			$jsdata=json_encode($usrInfo);
		    echo $jsdata;
			exit;
		}else{
		    //注册礼盒
		    $now = time();
		    $insertdata = array(
			    'weid'      => $weid,
			    'from_user' => $fromuser,
			    'rid'       => $rid,
			    'avatar'    => $_GPC['avatar'],
			    'nickname'  => $_GPC['nickname'],
			    'realname'  => $_GPC['info-name'],
			    'mobile'   => $_GPC['info-tel'],
			    'liheid'    => $_GPC['info-prize'],
 			    'sharetime' => $now,
			    'datatime'  => $now
		    );
		    pdo_insert($this->table_list, $insertdata);		
		    $dataid = pdo_insertid();//取id
		    //同时更新到官方FANS表中
		    fans_update($fromuser, array(
					'realname' => $_GPC['info-name'],
					'mobile' => $_GPC['info-tel'],
		    ));
		    $usrInfo = array('errno'=>0,'path'=>$_W['siteroot'].$this->createMobileUrl('regliheshow', array('rid' => $rid,'fromuser' => $fromuser,'uid' => $dataid)));
		    $jsdata=json_encode($usrInfo);
		    echo $jsdata;
		    exit;
		}
		
	}
	public function doMobileregliheshow() {
		//分享集赞分享页面显示。
		global $_GPC,$_W;
		$weid = $_W['weid'];//当前公众号ID
		$rid  = $_GPC['rid'];//当前规则ID		
		$fromuser = $_GPC['fromuser'];
		$uid = $_GPC['uid'];
		//活动规则
		if (!empty($rid)) {
			$reply = pdo_fetch("SELECT * FROM ".tablename($this->table_reply)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));
			$picture = $reply['picture'];
			$picbg02 = $reply['picbg02'];
			if (substr($picture,0,6)=='images'){
			   $picture = $_W['attachurl'] . $picture;
			}else{
			   $picture = $_W['siteroot'] . $picture;
			}
			if (substr($picbg02,0,6)=='images'){
			   $picbg02 = $_W['attachurl'] . $picbg02;
			}
		}
		//礼盒粉丝信息
		$listuser = pdo_fetch('SELECT * FROM '.tablename($this->table_list).'  WHERE id = :id', array(':id' => $uid));//礼盒信息
		$listgift = pdo_fetch('SELECT * FROM '.tablename($this->table_gift).'  WHERE id = :id', array(':id' => $listuser['liheid']));
		
		$shareurl = $_W['siteroot'].$this->createMobileUrl('sharelihe', array('rid' => $rid,'fromuser' => $fromuser,'iid' => $uid));//分享URL
		$mylihe = $_W['siteroot'].$this->createMobileUrl('mylihe', array('rid' => $rid,'fromuser' => $fromuser));//我的礼盒
		$openlihe = $_W['siteroot'].$this->createMobileUrl('openlihe', array('rid' => $rid,'fromuser' => $fromuser,'info-prize' => $uid));//自己拆开礼盒

		$user_agent = $_SERVER['HTTP_USER_AGENT'];
		if (strpos($user_agent, 'MicroMessenger') === false) {
			echo "本页面仅支持微信访问!非微信浏览器禁止浏览!";
			//include $this->template('regliheshow');
		} else { 
			include $this->template('regliheshow');
		}

	}

	public function doMobilemylihe() {
		//分享集赞分享页面显示。
		global $_GPC,$_W;
		$weid = $_W['weid'];//当前公众号ID
		$rid  = $_GPC['rid'];//当前规则ID		
		$fromuser = $_GPC['fromuser'];

		//活动规则
		if (!empty($rid)) {
			$reply = pdo_fetch("SELECT * FROM ".tablename($this->table_reply)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));
			$picture = $reply['picture'];
			$picbg03 = $reply['picbg03'];
			if (substr($picture,0,6)=='images'){
			   $picture = $_W['attachurl'] . $picture;
			}else{
			   $picture = $_W['siteroot'] . $picture;
			}
			if (substr($picbg03,0,6)=='images'){
			   $picbg03 = $_W['attachurl'] . $picbg03;
			}
		}
		//我的礼盒信息
		$listlihe = pdo_fetchall('SELECT a.*,b.gift,b.break,b.total FROM '.tablename($this->table_list).' as a left join '.tablename($this->table_gift).' as b on a.liheid=b.id  WHERE a.weid= :weid AND a.rid = :rid and from_user = :from_user order by `id` desc', array(':weid' => $weid,':rid' => $rid,':from_user' => $fromuser));
		//判断是否还可以领取礼盒
		if(!empty($fromuser)) {			
			$count = pdo_fetch("SELECT count(id) as dd FROM ".tablename($this->table_list)." WHERE weid=".$weid." AND rid= ".$rid." AND from_user='".$fromuser."'");
		    $giftnum = $count['dd'];//领取礼盒数总数

			$todaytimestamp = strtotime(date('Y-m-d'));
			$count = pdo_fetch("SELECT count(id) as dd FROM ".tablename($this->table_list)." WHERE weid=".$weid." AND rid= ".$rid." AND from_user='".$fromuser."' AND datatime >= '".$todaytimestamp."'");
		    $daygiftnum = $count['dd'];//领取礼盒数今日数

			$gift_num = $reply['number_num']-$giftnum;	//总数还有多少次机会
			$gift_num_day = $reply['number_num_day']-$daygiftnum;//今日还有多少次机会
			$abovemax = 'true';//默认没有机会
			if ($gift_num>=1) {	
                if($gift_num_day>=1){
				   	$abovemax = 'false';
				}						
			}			
		}
		
		//计算礼盒状态开始
		foreach ($listlihe as $row) {
			$break = $row['break']-$row['sharenum'];//还需要多少全拆开
			if($break<=0){
			    $break = 0;			
			}
			//是否打过开
			$openlihe = 'false';
			if($row['openlihe']==1){
                 $openlihe = 'true';
			}
			//是否打过开
			//是否被领完
			$rc = 'false';
			$count = pdo_fetch("SELECT count(id) as dd FROM ".tablename($this->table_list)." WHERE weid=".$weid." AND zhongjiang>=1 AND rid= ".$rid." AND liheid='".$row['liheid']."'");
		    $zgiftnum = $count['dd'];//领取礼盒数中奖数
			if($zgiftnum>$row['total'] and $row['openlihe']==0 and $break==0){
                 $rc = 'true';
			}
			//是否被领完
			if($row['break']==0){//不需要朋友帮拆则直接自己拆开
			    $prize = $prize.'{h:1,r:0,i:'.$openlihe.',rc:'.$rc.',my:1},';
			}else{
			    $prize = $prize.'{h:'.$row['sharenum'].',r:'.$break.',i:'.$openlihe.',rc:'.$rc.',my:0},';
			}
		}
		// i:true=>打开过 false=>未打开过
        // rc:true=>被领完了 false=>未被领完
		$prize = substr($prize,0,strlen($prize)-1);
		//计算礼盒状态完成
		$shareurl = $_W['siteroot'].$this->createMobileUrl('sharelihe', array('rid' => $rid,'fromuser' => $fromuser));//分享URL
		//还可以再领一个
		$againreglihe = $_W['siteroot'].$this->createMobileUrl('reglihe', array('rid' => $rid,'fromuser' => $fromuser));
		//打开礼盒
		$openliheurl = $_W['siteroot'].$this->createMobileUrl('openlihe', array('rid' => $rid,'fromuser' => $fromuser));
		$shouquan = base64_encode($_SERVER ['HTTP_HOST'].'anquan_ma_chailihe');
		//查看礼盒奖品
		$viewliheurl = $_W['siteroot'].$this->createMobileUrl('viewlihe', array('rid' => $rid,'fromuser' => $fromuser));

		$user_agent = $_SERVER['HTTP_USER_AGENT'];
		if (strpos($user_agent, 'MicroMessenger') === false) {
			echo "本页面仅支持微信访问!非微信浏览器禁止浏览!";
			//include $this->template('mylihe');
		} else { 
			include $this->template('mylihe');
		}	
	}
	public function doMobilesharelihe() {
		//分享页面显示。
		global $_GPC,$_W;
		$weid = $_W['weid'];//当前公众号ID
		$rid  = $_GPC['rid'];//当前规则ID		
		$fromuser = $_GPC['fromuser'];
		$from_user = $_W['fans']['from_user'];
		if(empty($from_user)){
		    $from_user = $_GPC['from_user'];
		}
		$opentype = $_GPC['opentype'];//礼盒打开方式0为访问,1为点击
		if(empty($opentype)){
		    $opentype = 0;//默认为访问即可拆开礼盒
		}
		$openlihe_is = 0;//默认没有拆过礼盒
		$uid = $_GPC['iid'];//分享ID
		$visitorsip = getip();
		$now = time();
		//活动规则
		if (!empty($rid)) {
			$reply = pdo_fetch("SELECT * FROM ".tablename($this->table_reply)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));
			$picture = $reply['picture'];
			$picbg02 = $reply['picbg02'];
			if (substr($picture,0,6)=='images'){
			   $picture = $_W['attachurl'] . $picture;
			}else{
			   $picture = $_W['siteroot'] . $picture;
			}
			if (substr($picbg02,0,6)=='images'){
			   $picbg02 = $_W['attachurl'] . $picbg02;
			}
		}
		//查询是否需要关注才能帮其拆礼盒
		if($reply['opensubscribe']==1){
		    //取用户资料
			$profile  = fans_search($from_user, array('follow'));
			if ($profile['follow']!=1) {
			    //没有关注用户跳转引导页
				$openshare = $reply['openshare'];
		        header("location:$openshare");
		    }
		
		}
		if(empty($from_user)){
		    //取不到openid 开启借用模式取opendid
			if ($serverapp!=2) {//普通号
			    //查询是否有借用接口
			    $cfg = $this->module['config'];
			    $appid = $cfg['appid'];
			    $secret = $cfg['secret'];
				if(!empty($secret)){
				    //取openid值 
				    $url = $_W['siteroot'].$this->createMobileUrl('oauth2', array('rid' => $rid,'iid' => $uid,'fromuser' => $fromuser,'viewtype' => 'sharelihe'));
				    $oauth2_code = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$appid."&redirect_uri=".urlencode($url)."&response_type=code&scope=snsapi_base&state=0#wechat_redirect";
		            header("location:$oauth2_code");
                }else{
				    $from_user = 'qi'.base64_encode($visitorsip.'opendid').'ip';//以IP地址为唯一值
				}
			}
		}
		//礼盒粉丝信息
		$listuser = pdo_fetch('SELECT * FROM '.tablename($this->table_list).'  WHERE id = :id', array(':id' => $uid));//礼盒信息
		$listgift = pdo_fetch('SELECT * FROM '.tablename($this->table_gift).'  WHERE id = :id', array(':id' => $listuser['liheid']));
		
		//添加拆礼盒记录
		if($fromuser!=$from_user){//自己不能给自己拆礼盒
		    $sharedata = pdo_fetch("SELECT * FROM ".tablename($this->table_data)." WHERE uid = '".$uid."' and rid = '".$rid."' and from_user = '".$from_user."' and weid = '".$weid."'  limit 1" );
		    if(empty($sharedata)){//一个朋友只能拆一次礼盒			    
		        $insertdata = array(
		            'weid'           => $weid,
		            'from_user'      => $from_user,
		            'avatar'         => $headimgurl,
		            'nickname'       => $nickname,
		            'rid'            => $rid,
 		            'uid'            => $uid,
		            'visitorsip'	 => $visitorsip,
		            'visitorstime'   => $now
		        );
				if($opentype==$reply['opentype']){
				    pdo_insert($this->table_data, $insertdata);
					$openlihe_is = 1;//已拆过礼盒
				}		        

		        $updatelist = array(
		            'sharenum'  => $listuser['sharenum']+1,
		            'sharetime' => $now
		        );
				if($opentype==$reply['opentype']){
		             pdo_update($this->table_list,$updatelist,array('id' => $uid));
				}
		    }else{
			    $openlihe_is = 1;//已拆过礼盒
			}
		}else{
		    //跳转到自己的礼盒信息处
		    $mylihe = $_W['siteroot'].$this->createMobileUrl('mylihe', array('rid' => $rid,'fromuser' => $fromuser));//我的礼盒
		    header("location:$mylihe");
		}
		//添加拆礼盒记录完成

		//拆礼盒用户信息
		$count = pdo_fetch("SELECT count(id) as dd FROM ".tablename($this->table_data)." WHERE weid=".$weid." AND rid= ".$rid." AND uid='".$uid."'");
		$chainum = $count['dd'];//多少个朋友帮你拆过
		//first第一个/last最后一个/done成功拆开/opened拆开中
		$openedstyle = 'opened';
		$rest = $listgift['break']-$chainum;
		if($chainum==0){
		   $openedstyle = 'first';		   
		}
		if(($listgift['break']-$chainum)==1){
		   $openedstyle = 'last';
		}
		if($openlihe_is==1){
		    $openedstyle = 'opened';
		}
		if($listgift['break']<=$chainum){
		   $openedstyle = 'done';
		   $rest = 0;
		}
		//拆礼盒用户信息

		$shareurl = $_W['siteroot'].$this->createMobileUrl('sharelihe', array('rid' => $rid,'iid' => $uid,'fromuser' => $fromuser));//分享URL
		$reglihe =  $_W['siteroot'].$this->createMobileUrl('shareuserview', array('rid' => $rid));//分享URL
		$openlihe = $_W['siteroot'].$this->createMobileUrl('sharelihe', array('rid' => $rid,'iid' => $uid,'fromuser' => $fromuser,'opentype' => 1));//点击打开礼盒
		
		$user_agent = $_SERVER['HTTP_USER_AGENT'];
		if (strpos($user_agent, 'MicroMessenger') === false) {
			echo "本页面仅支持微信访问!非微信浏览器禁止浏览!";
			//include $this->template('sharelihe');
		} else { 
			include $this->template('sharelihe');
		}	
	}
	public function doMobileopenlihe() {
		//分享页面显示。
		global $_GPC,$_W;
		$weid = $_W['weid'];//当前公众号ID
		$rid  = $_GPC['rid'];//当前规则ID		
		$fromuser = $_GPC['fromuser'];
		$uid = $_GPC['info-prize'];//礼盒分享人ID


		//活动规则
		if (!empty($rid)) {
			$reply = pdo_fetch("SELECT * FROM ".tablename($this->table_reply)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));
			$picture = $reply['picture'];
			if (substr($picture,0,6)=='images'){
			   $picture = $_W['attachurl'] . $picture;
			}else{
			   $picture = $_W['siteroot'] . $picture;
			}			
		}
		//判断是否还可以领取礼盒
		if(!empty($fromuser)) {		
			
			$count = pdo_fetch("SELECT count(id) as dd FROM ".tablename($this->table_list)." WHERE weid=".$weid." AND rid= ".$rid." AND from_user='".$fromuser."'");
		    $giftnum = $count['dd'];//领取礼盒数总数

			$todaytimestamp = strtotime(date('Y-m-d'));
			$count = pdo_fetch("SELECT count(id) as dd FROM ".tablename($this->table_list)." WHERE weid=".$weid." AND rid= ".$rid." AND from_user='".$fromuser."' AND datatime >= '".$todaytimestamp."'");
		    $daygiftnum = $count['dd'];//领取礼盒数今日数


			$gift_num = $reply['number_num']-$giftnum;	//总数还有多少次机会
			$gift_num_day = $reply['number_num_day']-$daygiftnum;//今日还有多少次机会
			$giftlihe = 0;//默认没有机会
			if ($gift_num>=1) {	
                if($gift_num_day>=1){
				   	if($gift_num<$gift_num_day){
					    $giftlihe = $gift_num;
					}else{
					    $giftlihe = $gift_num_day;
					}
				}						
			}			
		}
		
		//礼盒信息
		$lihegift = pdo_fetch('SELECT a.zhongjiang,a.openlihe,b.* FROM '.tablename($this->table_list).' as a left join '.tablename($this->table_gift).' as b on a.liheid=b.id  WHERE a.id=:id', array(':id' => $uid));
		$count = pdo_fetch("SELECT count(id) as dd FROM ".tablename($this->table_list)." WHERE weid=".$weid." AND zhongjiang>=1 AND rid= ".$rid." AND liheid='".$lihegift['id']."'");
		$zgiftnum = $count['dd'];//领取礼盒数中奖数
		//中奖记录
		$probalilty = $lihegift['probalilty'];
		$probaliltyno = 100-$lihegift['probalilty'];
		$zhongjiang = $lihegift['zhongjiang'];
        if ($zgiftnum<=$lihegift['total']){
		    if ($zhongjiang==0){
		        $prize_arr = array(   
  		          '0' => array('id'=>0,'prize'=>'NO中奖','v'=>$probaliltyno),   
  		          '1' => array('id'=>1,'prize'=>'YES中奖','v'=>$probalilty), 
		        ); 
		        foreach ($prize_arr as $key => $val) {   
   		            $arr[$val['id']] = $val['v'];   
		        }   
		        $zhongjiang = $this->get_rand($arr); //根据概率获取奖项id			
		        pdo_update($this->table_list,array('zhongjiang' => $zhongjiang),array('id' => $uid));
		    }
		}
		pdo_update($this->table_list,array('openlihe' => 1),array('id' => $uid));

		$lihegift['awardpic'] = empty($lihegift['awardpic']) ? "./source/modules/stonefish_chailihe/template/images/award.jpg" : $lihegift['awardpic'];
		$awardpic = $lihegift['awardpic'];
		if (substr($awardpic,0,6)=='images'){
			   $awardpic = $_W['attachurl'] . $awardpic;
		}else{
			   $awardpic = $_W['siteroot'] . $awardpic;
		}
		
		
		$againreglihe = $_W['siteroot'].$this->createMobileUrl('reglihe', array('rid' => $rid,'fromuser' => $fromuser));
		$shareurl = $_W['siteroot'].$this->createMobileUrl('shareuserview', array('rid' => $rid));
		$liheduijiang = $_W['siteroot'].$this->createMobileUrl('duijiang', array('rid' => $rid,'fromuser' => $fromuser));
		$mylihe = $_W['siteroot'].$this->createMobileUrl('mylihe', array('rid' => $rid,'fromuser' => $fromuser));
		
		
		$user_agent = $_SERVER['HTTP_USER_AGENT'];
		if (strpos($user_agent, 'MicroMessenger') === false) {
			echo "本页面仅支持微信访问!非微信浏览器禁止浏览!";
			//include $this->template('openlihe');
		} else { 
			include $this->template('openlihe');
		}	
	}
	function get_rand($proArr) {   
        $result = '';    
        //概率数组的总概率精度   
        $proSum = array_sum($proArr);    
        //概率数组循环   
        foreach ($proArr as $key => $proCur) {   
            $randNum = mt_rand(1, $proSum);   
            if ($randNum <= $proCur) {   
                $result = $key;   
                break;   
            } else {   
                $proSum -= $proCur;   
            }         
        }   
        unset ($proArr);    
        return $result;   
    }

	public function doMobileviewlihe() {
		//分享页面显示。
		global $_GPC,$_W;
		$weid = $_W['weid'];//当前公众号ID
		$rid  = $_GPC['rid'];//当前规则ID		
		$fromuser = $_GPC['fromuser'];
		$uid = $_GPC['info-prize2'];//礼盒分享人ID


		//活动规则
		if (!empty($rid)) {
			$reply = pdo_fetch("SELECT * FROM ".tablename($this->table_reply)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));
			$picture = $reply['picture'];
			if (substr($picture,0,6)=='images'){
			   $picture = $_W['attachurl'] . $picture;
			}else{
			   $picture = $_W['siteroot'] . $picture;
			}			
		}
		//判断是否还可以领取礼盒
		if(!empty($fromuser)) {		
			
			$count = pdo_fetch("SELECT count(id) as dd FROM ".tablename($this->table_list)." WHERE weid=".$weid." AND rid= ".$rid." AND from_user='".$fromuser."'");
		    $giftnum = $count['dd'];//领取礼盒数总数

			$todaytimestamp = strtotime(date('Y-m-d'));
			$count = pdo_fetch("SELECT count(id) as dd FROM ".tablename($this->table_list)." WHERE weid=".$weid." AND rid= ".$rid." AND from_user='".$fromuser."' AND datatime >= '".$todaytimestamp."'");
		    $daygiftnum = $count['dd'];//领取礼盒数今日数


			$gift_num = $reply['number_num']-$giftnum;	//总数还有多少次机会
			$gift_num_day = $reply['number_num_day']-$daygiftnum;//今日还有多少次机会
			$giftlihe = 0;//默认没有机会
			if ($gift_num>=1) {	
                if($gift_num_day>=1){
				   	if($gift_num<$gift_num_day){
					    $giftlihe = $gift_num;
					}else{
					    $giftlihe = $gift_num_day;
					}
				}						
			}			
		}
		
		//中奖礼盒信息
		$lihegift = pdo_fetch('SELECT a.zhongjiang,b.* FROM '.tablename($this->table_list).' as a left join '.tablename($this->table_gift).' as b on a.liheid=b.id  WHERE a.id=:id', array(':id' => $uid));
		$zhongjiang = $lihegift['zhongjiang'];

		$lihegift['awardpic'] = empty($lihegift['awardpic']) ? "./source/modules/stonefish_chailihe/template/images/award.jpg" : $lihegift['awardpic'];
		$awardpic = $lihegift['awardpic'];
		if (substr($awardpic,0,6)=='images'){
			   $awardpic = $_W['attachurl'] . $awardpic;
		}else{
			   $awardpic = $_W['siteroot'] . $awardpic;
		}
		
		
		$againreglihe = $_W['siteroot'].$this->createMobileUrl('reglihe', array('rid' => $rid,'fromuser' => $fromuser));
		$shareurl = $_W['siteroot'].$this->createMobileUrl('shareuserview', array('rid' => $rid));
		$liheduijiang = $_W['siteroot'].$this->createMobileUrl('duijiang', array('rid' => $rid,'fromuser' => $fromuser));
		$mylihe = $_W['siteroot'].$this->createMobileUrl('mylihe', array('rid' => $rid,'fromuser' => $fromuser));
		
		
		$user_agent = $_SERVER['HTTP_USER_AGENT'];
		if (strpos($user_agent, 'MicroMessenger') === false) {
			echo "本页面仅支持微信访问!非微信浏览器禁止浏览!";
			//include $this->template('openlihe');
		} else { 
			include $this->template('openlihe');
		}	
	}
	
	public function doMobileduijiang() {
		//分享页面显示。
		global $_GPC,$_W;
		$weid = $_W['weid'];//当前公众号ID
		$rid  = $_GPC['rid'];//当前规则ID		
		$fromuser = $_GPC['fromuser'];
		$uid = $_GPC['info-prize'];//礼盒分享人ID
		$pass = $_GPC['info-pass'];//兑奖密码

		$lihegift = pdo_fetch('SELECT b.awardpass FROM '.tablename($this->table_list).' as a left join '.tablename($this->table_gift).' as b on a.liheid=b.id  WHERE a.id=:id', array(':id' => $uid));
		if(!empty($lihegift)){
			if($pass==$lihegift['awardpass']){
			    pdo_update($this->table_list,array('zhongjiang' => 2),array('id' => $uid));
			}else{
			//密码不对
			}			
		
		}else{
			//礼盒信息出错		
		}	

		$openliheurl = $_W['siteroot'].$this->createMobileUrl('openlihe', array('rid' => $rid,'fromuser' => $fromuser,'info-prize' => $uid));
		header("location:$openliheurl");
	}

	public function doMobileshareuserview() {
		//分享页面显示。
		global $_GPC,$_W;
		$weid = $_W['weid'];//当前公众号ID
		$rid  = $_GPC['rid'];//当前规则ID
        $from_user = $_W['fans']['from_user'];
		$serverapp = $_W['account']['level'];	//是否为高级号
		if(!empty($from_user)) {		    
			//取得openid跳转出去
			$shareurl = $_W['siteroot'].$this->createMobileUrl('chailihe', array('rid' => $rid,'fromuser' => $from_user));//分享URL
			header("location:$shareurl");
		}else{
		    //取不到openid 开启借用模式取opendid
			if ($serverapp!=2) {//普通号
			    //查询是否有借用接口
			    $cfg = $this->module['config'];
			    $appid = $cfg['appid'];
			    $secret = $cfg['secret'];
				if(!empty($secret)){
				    //取openid值 
				    $url = $_W['siteroot'].$this->createMobileUrl('oauth2', array('rid' => $rid));
				    $oauth2_code = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$appid."&redirect_uri=".urlencode($url)."&response_type=code&scope=snsapi_base&state=0#wechat_redirect";
		            header("location:$oauth2_code");
                }else{
				    if (!empty($rid)) {
			            $reply = pdo_fetch("SELECT shareurl FROM ".tablename($this->table_reply)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));			
		            }
					$shareurl = $reply['shareurl'];
		            header("location:$shareurl");
				}
			}
		}		
		
	}
	public function doMobileoauth2() {
	    global $_GPC,$_W;
		$weid = $_W['weid'];//当前公众号ID
		$serverapp = $_W['account']['level'];	//是否为高级号
		$viewtype = $_GPC['viewtype'];
		$fromuser = $_GPC['fromuser'];
		$iid = $_GPC['iid'];
		$rid = $_GPC['rid'];
		if (isset($_GPC['code'])){
			if ($serverapp!=2) {//普通号
				$cfg = $this->module['config'];
			    $appid = $cfg['appid'];
			    $secret = $cfg['secret'];
			}//借用的
		    $code = $_GPC['code'];			
		    $oauth2_code = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$appid."&secret=".$secret."&code=".$code."&grant_type=authorization_code";
		    $content = ihttp_get($oauth2_code);
		    $token = @json_decode($content['content'], true);
			if(empty($token) || !is_array($token) || empty($token['access_token']) || empty($token['openid'])) {
				echo '<h1>获取微信公众号授权'.$code.'失败[无法取得token以及openid], 请稍后重试！ 公众平台返回原始数据为: <br />' . $content['meta'].'<h1>';
				exit;
			}
		    $from_user = $token['openid'];
			if(!empty($viewtype)){
			    $shareurl = $_W['siteroot'].$this->createMobileUrl('sharelihe', array('rid' => $rid,'iid' => $iid,'fromuser' => $fromuser,'from_user' => $from_user));
			}else{
			    $shareurl = $_W['siteroot'].$this->createMobileUrl('chailihe', array('rid' => $rid,'fromuser' => $from_user));
			}
	        
		    header("location:$shareurl");
		}else{
			echo '<h1>借用高级认证号输入错误或网页授权域名设置出错!</h1>';
			exit;		
		}	
	}
	
	
}