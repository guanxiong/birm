<?php
/**
 * 微PUB模块微站定义
 *
 * @author on3
 * @url 
 */
defined('IN_IA') or exit('Access Denied');
require_once IA_ROOT.'/source/modules/oauth2/o2.php';
require_once IA_ROOT.'/source/modules/oauth2/emoji.php';

class Jdg_pubModuleSite extends WeModuleSite {
	public $table_pub = 'jdg_pub';
	public $table_party = 'jdg_pub_party';
	public $table_photos = 'jdg_pub_photos';
	public $table_partyfans = 'jdg_pub_partyfans';
	public $table_partycomments = 'jdg_pub_partycomments';
	public $getUserInfoUrl = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token=%s&openid=%s&lang=zh_CN';
	public $table_chatfans = 'jdg_pub_chatfans';
	public $table_chatcomments = 'jdg_pub_chatcomments';
	public $table_wine = 'shopping_category';
	public $table_winegoods = 'shopping_goods';
	public $table_likeit = 'jdg_pub_photoslikeit';
	public $table_wineadmin="jdg_pub_wineadmin";

	public $pub_fans;

	function __construct(){
        global $_W;
		$user_agent = $_SERVER ['HTTP_USER_AGENT'];
		if (strpos ( $user_agent, 'MicroMessenger' ) === true) {
			$openid = $_W['fans']['from_user'];
			if(empty($openid)){
				$this->pub_fans = o2::getOuser();
				if(empty($this->pub_fans['openid'])){
					message('获取信息失败,请确保是认证服务号或者借用授权的AppId和AppSecret正确');
				}
				$fans = pdo_fetch('SELECT * FROM'.tablename('fans')." WHERE weid = :weid AND from_user = :from_user",array(':weid'=>$_W['weid'],':from_user'=>$this->pub_fans['openid']));
				$user = array(
						'nickname' => emoji_unified_to_html($this->pub_fans['nickname']),
						'gender' => $this->pub_fans['sex'],
						'residecity'=> $this->pub_fans['city'],
						'resideprovince' => $this->pub_fans['province'],
						'nationality' => $this->pub_fans['country'],
						'avatar' => $this->pub_fans['headimgurl']
						);
				if(empty($fans)){
					$user['createtime'] = TIMESTAMP;
					$user['weid'] = $_W['weid'];
					$user['from_user'] = $this->pub_fans['openid'];
					pdo_insert('fans',$user);
				}else{
					pdo_update('fans',$user,array('weid'=>$_W['weid'],'from_user'=>$this->pub_fans['openid']));
				}
			}else{
				$this->pub_fans =o2::getFans($openid);
			}
		}
		//print_r($this->pub_fans);
		//exit();
    }

	public function doWebPartypeople() {
		global $_W,$_GPC;
		if(empty($_GPC['id'])){
			message('缺失重要的参数','','error');
		}
		$list = pdo_fetchall('SELECT t1.createtime,t1.from_user,t1.user_name,t1.head_img,pub_name,title FROM'.tablename($this->table_partyfans)." AS t1 JOIN ".tablename($this->table_party)." AS t2 ON t1.pid = t2.id JOIN ".tablename($this->table_pub)." AS t3 ON t3.id=t2.pubid WHERE t1.weid = :weid AND pid = :pid",array(':weid'=>$_W['weid'],':pid'=>$_GPC['id']));
		$count = pdo_fetch('SELECT count(*) AS sum FROM'.tablename($this->table_partyfans)." WHERE weid = :weid AND pid = :pid",array(':weid'=>$_W['weid'],':pid'=>$_GPC['id']));
		include $this->template('partypeople');
	}
	//by瞻园
	public function doWebManagementgoods(){
		global $_W,$_GPC;
		
		$url = "site.php?act=module&op=display&name=shopping&do=goods&weid={$_W['weid']}";  
		echo "<script language='javascript' type='text/javascript'>";  
		echo "window.location.href='$url'";  
		echo "</script>"; 
			
		}

	public function doWebChatit(){
		global $_W,$_GPC;
		if($_GPC['foo']=='delete'){
			if(empty($_GPC['id'])){
				message('重要参数丢失了哦','','error');
			}
			pdo_delete($this->table_chatcomments,array('id'=>$_GPC['id']));
			message('真棒,删除成功了哦',referer(),'success');
		}
		if($_GPC['foo']=='change'){
			if(empty($_GPC['id'])){
				message('重要参数丢失了哦','','error');
			}
			pdo_update($this->table_chatcomments,array('isok'=>$_GPC['val']),array('weid'=>$_W['weid'],'id'=>$_GPC['id']));
			message('真棒,状态更新成功',referer(),'success');
		}
		$list = pdo_fetchall('SELECT t1.id,pub_name,user_name,createtime,from_user,t1.txt,isok FROM'.tablename($this->table_chatcomments)." AS t1 JOIN ".tablename($this->table_pub)." AS t2 ON t1.pubid = t2.id WHERE t1.weid = :weid",array(':weid'=>$_W['weid']));
		include $this->template('chatit');
	}
	private function getUserInfo($o){
		global $_W,$_GPC;
		checkaccount();
		if(empty($o)){
			message('重要参数丢失..','','error');
			exit();
		}
		if(empty($_W['account']['key'])||empty($_W['account']['secret'])){
			$user = pdo_fetch('SELECT from_user,weid,nickname,avatar FROM'.tablename('fans'). "WHERE weid = :weid AND from_user = :from_user",array(':from_user'=>$o,':weid'=>$_W['weid']));
			if(empty($user)){
				$user = array(
							'weid' => $_W['weid'],
							'from_user' => $o,
							'createtime'=> TIMESTAMP,
							);
				/*pdo_insert('fans',$user);*/
			}
			if(empty($user['nickname'])){
					$user['nickname'] = '匿名'.substr($o, -4);
				}
			if(empty($user['avatar'])){
					$user['avatar'] = './source/modules/jdg_pub/template/style/null_header.png';
				}
			return $user;
		}else{
			$access_token = account_weixin_token($_W['account']);
			$content = ihttp_get(sprintf($this->getUserInfoUrl,$access_token,$o));
			if($content['errcode']!=0){
				message(account_weixin_code($content['errcode']),'','error');
			}
			$record = @json_decode($content['content'], true);
			if ($record['subscribe'] == '1') {
				$user = array(
					'weid' => $_W['weid'],
					'from_user' => $record['openid'],
					'nickname' => $record['nickname'],
					'gender' => $record['sex'],
					'residecity'=> $record['city'],
					'resideprovince' => $record['province'],
					'nationality' => $record['country'],
					'avatar' => $record['headimgurl'],
					'createtime'=> $record['subscribe_time']
					);
               
				if (pdo_fetch("SELECT * FROM ".tablename('fans')." WHERE `from_user` = '{$record['openid']}'")) {
					pdo_update('fans', $user, array('from_user' => $record['openid']));
				}else{
					pdo_insert('fans',$user);
				}
			}
			if(empty($user['nickname'])){
					$user['nickname'] = '匿名'.substr($o, -4);
				}
			if(empty($user['avatar'])){
					$user['avatar'] = './source/modules/jdg_pub/template/style/null_header.png';
				}
			return $user;
		}
	}

	public function doMobileChatit(){
		global $_W,$_GPC;
	
		$user_agent = $_SERVER ['HTTP_USER_AGENT'];
		if (strpos ( $user_agent, 'MicroMessenger' ) === false) {
			echo "本页面仅支持微信访问!非微信浏览器禁止浏览!";
			exit ();
		}
		
		$id = $_GPC['id'];
		$config = $this->module['config']['ischeck'];
		$config = $config?$config:0;
		if(empty($id)||empty($_W['fans']['from_user'])){
			message('缺失重要的数据','','error');
		}
		$now = mktime(0,0,0);
		$record = pdo_fetch('SELECT COUNT(*) AS sum FROM'.tablename('jdg_pub_clock')."WHERE now = :now",array(':now'=>$now));
		if($record['sum']==0){
			pdo_query('TRUNCATE TABLE '.tablename($this->table_chatfans));
			pdo_query('TRUNCATE TABLE '.tablename($this->table_chatcomments));
			pdo_insert('jdg_pub_clock',array('createtime'=>TIMESTAMP,'now'=>$now));
		}
		$user = $this->getUserInfo($_W['fans']['from_user']);

		$ifin = pdo_fetch('SELECT * FROM'.tablename($this->table_chatfans)." WHERE weid = :weid AND from_user = :from_user AND pubid = :pubid",array(':weid'=>$_W['weid'],':from_user'=>$_W['fans']['from_user'],':pubid'=>$id));
		$list =  pdo_fetch('SELECT * FROM'.tablename($this->table_pub)." WHERE weid = :weid AND id = :id",array(':weid'=>$_W['weid'],':id'=>$id));
		if($_GPC['foo']=='inseat'){
			if(empty($_W['fans']['from_user'])){
				exit(json_encode(array('IsSuccess'=>false,'Message'=>'重要参数丢失了哦..')));
			}
			$phone = $_GPC['phone'];
			$wx = $_GPC['weixin'];
			$insert = array('weid'=>$_W['weid'],
				'pubid'=>$id,
				'from_user'=>$_W['fans']['from_user'],
				'user_name'=>$user['nickname'],
				'head_img'=>$user['avatar'],
				'phone'=>$phone,'wx'=>$wx,
				'createtime'=>TIMESTAMP);
			$record = pdo_fetch('SELECT * FROM'.tablename($this->table_chatfans)." WHERE weid = :weid AND from_user = :from_user",array(':weid'=>$_W['weid'],':from_user'=>$_W['fans']['from_user']));
			if(!empty($record)){
				exit(json_encode(array('IsSuccess'=>false,'Message'=>'您已经就坐了..')));
			}
			pdo_insert($this->table_chatfans,$insert);
			$id = pdo_insertid();
			if($id==0){
				exit(json_encode(array('IsSuccess'=>false,'Message'=>'系统正忙哦 休息一下再试..')));
			}else{
				exit(json_encode(array('IsSuccess'=>true)));
			}
		}else if($_GPC['foo']=='comment'){
			if(empty($_W['fans']['from_user'])){
				exit(json_encode(array('IsSuccess'=>false,'Message'=>'重要参数丢失了哦..')));
			}
			$insert = array('weid'=>$_W['weid'],
				'pubid'=>$id,
				'from_user'=>$_W['fans']['from_user'],
				'user_name'=>$user['nickname'],
				'head_img'=>$user['avatar'],
				'createtime'=>TIMESTAMP,
				'txt'=>$_GPC['content']);
			pdo_insert($this->table_chatcomments,$insert);
			$id = pdo_insertid();
			if($id==0){
				exit(json_encode(array('IsSuccess'=>false,'Message'=>'系统正忙哦 休息一下再试..')));
			}else{
				exit(json_encode(array('IsSuccess'=>true)));
			}
		}
		
		
		$updown = 0;
		
		$upindex = max(1, intval($_GPC['ipage']));
	
		$usize=6;
		$utotal = pdo_fetchcolumn('select COUNT(*) FROM'.tablename($this->table_chatfans)." WHERE weid = :weid AND pubid = :pubid",array(':weid'=>$_W['weid'],':pubid'=>$id));
		$list_upside = pdo_fetchall('SELECT * FROM'.tablename($this->table_chatfans)." WHERE weid = :weid AND pubid = :pubid LIMIT " . ($upindex - 1) * $usize . ',' . $usize,array(':weid'=>$_W['weid'],':pubid'=>$id));
//		$pager = pagination($total, $pindex, $bsize);
		$pindex = max(1, intval($_GPC['page']));
		$bsize=6;
		$total = pdo_fetchcolumn('select COUNT(*)  FROM'.tablename($this->table_chatcomments)." WHERE weid = :weid AND pubid = :pubid ORDER BY createtime DESC",array(':weid'=>$_W['weid'],':pubid'=>$id));
		$list_comment = pdo_fetchall('SELECT * FROM'.tablename($this->table_chatcomments)." WHERE weid = :weid AND pubid = :pubid ORDER BY createtime DESC LIMIT " . ($pindex - 1) * $bsize . ',' . $bsize,array(':weid'=>$_W['weid'],':pubid'=>$id));
		$pager = pagination($total, $pindex, $bsize);
		include $this->template('chatit');
	}

	public function doMobilePubcover() {
		global $_W,$_GPC;
		$user_agent = $_SERVER ['HTTP_USER_AGENT'];
		if (strpos ( $user_agent, 'MicroMessenger' ) === false) {
			echo "本页面仅支持微信访问!非微信浏览器禁止浏览!";
			exit ();
		}
		$list = pdo_fetch('SELECT * FROM'.tablename($this->table_pub)." WHERE weid = :weid",array(':weid'=>$_W['weid']));
		include $this->template('index');
	}
	public function doMobileImgUpload(){
		global $_W,$_GPC;
		
		$user_agent = $_SERVER ['HTTP_USER_AGENT'];
		if (strpos ( $user_agent, 'MicroMessenger' ) === false) {
			echo "本页面仅支持微信访问!非微信浏览器禁止浏览!";
			exit ();
		}
		
		if(empty($_W['fans']['from_user'])){
				exit(json_encode(array('IsActionSuccess'=>false,'ActionMsg'=>'请从微信端登陆后上传照片..')));
			}
			$id = $_GPC['pubid'];
			if(empty($id)){
				exit(json_encode(array('IsActionSuccess'=>false,'ActionMsg'=>'重要参数丢失了哦..')));
			}
		if(empty($_FILES['imgFile'])){
			exit(json_encode(array('IsActionSuccess'=>false,'ActionMsg'=>'上传的图片不能为空哦...')));
		}else{
			if ($_FILES['imgFile']['error'] != 0) {
				exit(json_encode(array('IsActionSuccess'=>false,'ActionMsg'=>'上传失败,请稍后再试..')));
			}
			$size = $_FILES['imgFile']['size'];
			if($size>2097152){
				exit(json_encode(array('IsActionSuccess'=>false,'ActionMsg'=>'上传的图片大小不能超过2M..')));
			}
			$_W['uploadsetting'] = array();
			$_W['uploadsetting']['image']['folder'] = '/images/' . $_W['weid'];
			$_W['uploadsetting']['image']['extentions'] = $_W['config']['upload']['image']['extentions'];
			$_W['uploadsetting']['image']['limit'] = $_W['config']['upload']['image']['limit'];
			$file = file_upload($_FILES['imgFile'], 'image');
			if (is_error($file)) {
				exit(json_encode(array('IsActionSuccess'=>false,'ActionMsg'=>$file['message'])));
			}
			$result['url'] = $file['url'];
			$result['error'] = 0;
			$result['filename'] = $file['path'];
			$result['url'] = $_W['attachurl'].$result['filename'];
			pdo_insert('attachment', array(
				'weid' => $_W['weid'],
				'uid' => $_W['uid'],
				'filename' => $_FILES['imgFile']['name'],
				'attachment' => $result['filename'],
				'type' => 1,
				'createtime' => TIMESTAMP,
			));
			$user = $this->getUserInfo($_W['fans']['from_user']);
			pdo_insert($this->table_photos, array(
				'weid' => $_W['weid'],
				'img_url' => $result['filename'],
				'smallimg_url' => $result['filename'],
				'createtime' => TIMESTAMP,
				'user_name' => $user['nickname'],
				'from_user'=>$_W['fans']['from_user'],
				'pubid'=>$id
			));
			$id = pdo_insertid();
			if($id==0){
				exit(json_encode(array('IsActionSuccess'=>false,'ActionMsg'=>'系统正忙..请喝杯茶等一等好吗..')));
			}else{
				exit(json_encode(array('IsActionSuccess'=>true)));
			}
		}
	}

	public function doMobilePartyit() {
		global $_W,$_GPC;
		
		$user_agent = $_SERVER ['HTTP_USER_AGENT'];
		if (strpos ( $user_agent, 'MicroMessenger' ) === false) {
			echo "本页面仅支持微信访问!非微信浏览器禁止浏览!";
			exit ();
		}
		
		$id = $_GPC['id'];
		if(empty($id)){
			message('缺失重要的数据','','error');
		}
		$list =  pdo_fetch('SELECT * FROM'.tablename($this->table_pub)." WHERE weid = :weid AND id = :id",array(':weid'=>$_W['weid'],':id'=>$id));
		$listall = pdo_fetchall('SELECT * FROM'.tablename($this->table_party)." WHERE weid = :weid AND pubid = :pubid",array(':weid'=>$_W['weid'],':pubid'=>$id));
		include $this->template('partyit');
	}

	public function doMobilePartydetailit() {
		global $_W,$_GPC;
		
		$user_agent = $_SERVER ['HTTP_USER_AGENT'];
		if (strpos ( $user_agent, 'MicroMessenger' ) === false) {
			echo "本页面仅支持微信访问!非微信浏览器禁止浏览!";
			exit ();
		}
		
		if(empty($_W['fans']['from_user'])){
			message('请从微信端登陆页面','','error');
		}
		$user = $this->getUserInfo($_W['fans']['from_user']);
		$id = $_GPC['id'];
		$pid = $_GPC['pid'];
		$record = pdo_fetch('SELECT * FROM'.tablename($this->table_partyfans)." WHERE weid = :weid AND pid = :pid AND from_user = :from_user",array(':from_user'=>$_W['fans']['from_user'],':pid'=>$pid,':weid'=>$_W['weid']));
		if($_GPC['foo']=='reg'){
			if(empty($pid)){
				exit(json_encode(array('IsActionSuccess'=>false,'ActionMsg'=>'系统缺失重要参数')));
			}
			$record = pdo_fetch('SELECT * FROM'.tablename($this->table_partyfans)." WHERE weid = :weid AND pid = :pid AND from_user = :from_user",array(':from_user'=>$_W['fans']['from_user'],':pid'=>$pid,':weid'=>$_W['weid']));
			if(!empty($record)){
				exit(json_encode(array('IsActionSuccess'=>false,'ActionMsg'=>'您已经报过名咯..')));
			}
			pdo_insert($this->table_partyfans,array('weid'=>$_W['weid'],
				'pid'=>$pid,
				'user_name'=>$user['nickname'],
				'head_img'=>$user['avatar'],
				'from_user'=>$_W['fans']['from_user'],
				'createtime'=>TIMESTAMP));
			$id = pdo_insertid();
			if($id!=0){
				exit(json_encode(array('Status'=>0,'ActionMsg'=>'真棒,报名成功了耶...')));
			}else{
				exit(json_encode(array('IsActionSuccess'=>false,'ActionMsg'=>'系统好忙啊..休息一下嘛..')));
			}
		}elseif($_GPC['foo']=='comment'){
			if(empty($pid)){
				exit(json_encode(array('IsActionSuccess'=>false,'ActionMsg'=>'系统缺失重要参数')));
			}
			pdo_insert($this->table_partycomments,array('weid'=>$_W['weid'],
				'pid'=>$pid,'from_user'=>$_W['fans']['from_user'],
				'user_name'=>$user['nickname'],
				'head_img'=>$user['avatar'],
				'createtime'=>TIMESTAMP,
				'txt'=>$_GPC['content']));
			$id = pdo_insertid();
			if($id!=0){
				exit(json_encode(array('IsActionSuccess'=>true,'ActionMsg'=>'太好了,留言成功了')));
			}else{
				exit(json_encode(array('IsActionSuccess'=>false,'ActionMsg'=>'系统好忙啊..休息一下嘛..')));
			}
		}
		if(empty($id)||empty($pid)){
			message('缺失重要的数据','','error');
		}
		$list =  pdo_fetch('SELECT * FROM'.tablename($this->table_pub)." WHERE weid = :weid AND id = :id",array(':weid'=>$_W['weid'],':id'=>$id));
		$item = pdo_fetch('SELECT * FROM'.tablename($this->table_party)." WHERE weid = :weid AND id = :id AND pubid = :pubid",array(':weid'=>$_W['weid'],':id'=>$pid,':pubid'=>$id));
		$pfans =pdo_fetchall('SELECT * FROM'.tablename($this->table_partyfans)." WHERE weid = :weid AND pid = :pid",array(':pid'=>$pid,':weid'=>$_W['weid']));
		
		$pindex = max(1, intval($_GPC['page']));
		$psize = 6;
		$bsize=3;
		$total = pdo_fetchcolumn('select COUNT(*) FROM'.tablename($this->table_partycomments)." WHERE weid = :weid AND pid = :pid",array(':pid'=>$pid,':weid'=>$_W['weid']));
		$pcomments =pdo_fetchall('SELECT * FROM'.tablename($this->table_partycomments)." WHERE weid = :weid AND pid = :pid ORDER BY createtime DESC LIMIT " . ($pindex - 1) * $bsize . ',' . $bsize,array(':pid'=>$pid,':weid'=>$_W['weid']));
		$pager = pagination($total, $pindex, $bsize);
		include $this->template('partydetailit');
	}

	public function doMobileAboutit() {
		global $_W,$_GPC;
		$user_agent = $_SERVER ['HTTP_USER_AGENT'];
		if (strpos ( $user_agent, 'MicroMessenger' ) === false) {
			echo "本页面仅支持微信访问!非微信浏览器禁止浏览!";
			exit ();
		}
		$id = $_GPC['id'];
		if(empty($id)){
			message('缺失重要的数据','','error');
		}
		$list = pdo_fetch('SELECT * FROM'.tablename($this->table_pub)." WHERE weid = :weid AND id = :id",array(':weid'=>$_W['weid'],':id'=>$id));
		$map = 'http://api.map.baidu.com/marker?location='.$list['lat'].','.$list['lng'].'&title='.$list['pub_name'].'&content='.$list['address'].'&output=html';
		include $this->template('aboutit');
	}

	public function doMobileInviteit() {
		global $_W,$_GPC;
		
		$user_agent = $_SERVER ['HTTP_USER_AGENT'];
		if (strpos ( $user_agent, 'MicroMessenger' ) === false) {
			echo "本页面仅支持微信访问!非微信浏览器禁止浏览!";
			exit ();
		}
		
		$id = $_GPC['id'];
		if(empty($id)){
			message('缺失重要的数据','','error');
		}
		$list = pdo_fetch('SELECT * FROM'.tablename($this->table_pub)." WHERE weid = :weid AND id = :id",array(':weid'=>$_W['weid'],':id'=>$id));
		if(!empty($_GPC['foo'])){
			$foo = $_GPC['foo'];
			$url = $_W['siteroot'].$this->createMobileUrl('inviteit',array('id'=>$list['id'],'foo'=>$foo));
			if(empty($_W['fans']['from_user'])){
				message('请从微信端登陆页面','','error');
				exit();
			}
			$img_url1 = './source/modules/jdg_pub/template/style/invitation-%s.png';
			$img_url=sprintf($img_url1,$_GPC['foo']);
			$user = $this->getUserInfo($_W['fans']['from_user']);
			include $this->template('itit');
			exit();
		}
		include $this->template('inviteit');
	}

	public function doMobileShowpics() {
		global $_W,$_GPC;
		
		$user_agent = $_SERVER ['HTTP_USER_AGENT'];
		if (strpos ( $user_agent, 'MicroMessenger' ) === false) {
			echo "本页面仅支持微信访问!非微信浏览器禁止浏览!";
			exit ();
		}
		
		if($_GPC['foo']=='likeit'){
			if(empty($_GPC['pid'])){
				exit(json_encode(array('IsActionSuccess'=>false,'ActionMsg'=>'丢失重要参数..')));
			}
			if(empty($_W['fans']['from_user'])){
				exit(json_encode(array('IsActionSuccess'=>false,'ActionMsg'=>'请从微信端登陆页面..')));
			}
			$record = pdo_fetch('SELECT * FROM'.tablename($this->table_likeit)." WHERE from_user = :from_user AND pid = :pid",array(':pid'=>$_GPC['pid'],':from_user'=>$_W['fans']['from_user']));
			$re = pdo_fetch('SELECT *FROM'.tablename($this->table_photos)." WHERE weid = :weid AND id = :id",array(':weid'=>$_W['weid'],':id'=>$_GPC['pid']));
			if(!empty($record)){
				if($re['likeit']==0){
					exit(json_encode(array('IsActionSuccess'=>true)));
				}
				pdo_query('UPDATE '.tablename($this->table_photos).' SET likeit = likeit-1 WHERE weid = :weid AND id = :id',array(':id'=>intval($_GPC['pid']),':weid'=>$_W['weid']));
				pdo_delete($this->table_likeit,array('pid'=>$_GPC['pid'],'from_user'=>$_W['fans']['from_user']));
				exit(json_encode(array('IsActionSuccess'=>true)));
			}
			$insert = array('weid'=>$_W['weid'],'pid'=>$_GPC['pid'],'createtime'=>TIMESTAMP,'from_user'=>$_W['fans']['from_user']);
			pdo_query('UPDATE '.tablename($this->table_photos).' SET likeit = likeit+1 WHERE weid = :weid AND id = :id',array(':id'=>intval($_GPC['pid']),':weid'=>$_W['weid']));
			pdo_insert($this->table_likeit,$insert);
			$id = pdo_insertid();
			if($id==0){
				exit(json_encode(array('IsActionSuccess'=>false,'ActionMsg'=>'系统正忙..请喝杯茶等一等好吗..')));
			}else{
				exit(json_encode(array('IsActionSuccess'=>true)));
			}
		}
		$id = $_GPC['id'];
		if(empty($id)){
			message('缺失重要的数据','','error');
		}
		$pindex = max(1, intval($_GPC['page']));
		$psize = 6;
		$bsize=3;
	//	$list = pdo_fetchall("SELECT * FROM ".tablename('photos_data')." WHERE weid = '{$_W['weid']}' ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
		$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename($this->table_photos)." WHERE weid = '{$_W['weid']}' and pubid=".$id);	
		$pager = pagination($total, $pindex, $psize);
		$list = pdo_fetch('SELECT * FROM'.tablename($this->table_pub)." WHERE weid = :weid AND id = :id",array(':weid'=>$_W['weid'],':id'=>$id));
		/*$list = pdo_fetch('SELECT * FROM'.tablename($this->table_pub)." WHERE weid = ".$_W['weid']." AND id = ".$id." LIMIT " . ($pindex - 1) * $psize . ',' . $psize);*/
		$list_left = pdo_fetchall('SELECT * FROM'.tablename($this->table_photos) ." WHERE weid = ".$_W['weid']." AND pubid = ".$id." AND id%2=1 ORDER BY id DESC LIMIT " . ($pindex - 1) * $bsize . ',' . $bsize);
		$list_right = pdo_fetchall('SELECT * FROM'.tablename($this->table_photos). " WHERE weid = ".$_W['weid']." AND pubid = ".$id." AND id%2=0 ORDER BY id DESC LIMIT " . ($pindex - 1) * $bsize . ',' . $bsize);
// 		echo "SELECT * FROM ".tablename($this->table_photos) ." WHERE weid = ".$_W['weid']." AND pubid = ".$id." AND id%2=1 ORDER BY id DESC LIMIT " . ($pindex - 1) * $bsize . ',' . $bsize;

		if(empty($list_left)){
			$listall = $list_right;
		}else if(empty($list_right)){
			$listall = $list_left;
		}else if(!empty($list_left)&&!empty($list_right)){
			$listall = pdo_fetchall('SELECT * FROM'.tablename($this->table_photos). " WHERE weid = ".$_W['weid']." AND pubid = ".$id." ORDER BY id DESC");
		}else{
			$listall=array();
		}
		include $this->template('showpics');
	}

	public function doWebPubit(){
		global $_W,$_GPC;
		$item = pdo_fetch('SELECT * FROM '.tablename($this->table_pub)." WHERE weid = :weid",array(':weid'=>$_W['weid']));
		if(checksubmit()){
			$data = array('lng'=>$_GPC['lng'],
				'lat'=>$_GPC['lat'],
				'pub_name'=>$_GPC['pub_name'],
				'address'=>$_GPC['address1'],
				'weid'=>$_W['weid'],
				'tel'=>$_GPC['tel'],
				'header_img'=>$_GPC['header_img'],
				'background_img'=>$_GPC['background_img'],
				'txt'=>$_GPC['txt']);
			if(empty($item)){
				pdo_insert($this->table_pub,$data);
				//by 瞻园
				$id = pdo_insertid();
				  $upkey = pdo_fetchcolumn("select count(*) from information_schema.tables where table_name = 'ims_jcard_associations'");	   						
				if($upkey==1){
				
					$associations = pdo_fetch("SELECT * FROM ".tablename('jcard_associations')." WHERE assname='抢红包'");
				  $datat=array(
				  "assurl"=>'partyit',
				   "assname"=>'微夜店', 
				   "dataline"=>time(),
					"rid"=>$id,
					"asspic"=>0,
					 "medoul"=>'jdg_pub',  
				  );
				  if(empty($associations)){
						pdo_insert('jcard_associations', $datat);
				  }else{
						$p_uptatf = pdo_update('jcard_associations',$datat,array('id'=>$associations['id']));
						
					}
				  }
				
				message('新建PUB成功!',referer(),'success');
			}else{
			  
				pdo_update($this->table_pub,$data,array('weid'=>$_W['weid']));
				message('更新PUB信息成功',referer(),'success');
			}
		}
		if(empty($item)){
			$setting = array('address'=>'江苏省南京市秦淮区瞻园路126号');
			$item = array('lng'=>118.792726,'lat'=>32.026415);
		}
		include $this->template('pubit');
	}

	public function doWebPartyit(){
		global $_W,$_GPC;
		$foo = $_GPC['foo']?$_GPC['foo']:'display';
		$pubid = pdo_fetchcolumn('SELECT id FROM'.tablename($this->table_pub)." WHERE weid = :weid",array(':weid'=>$_W['weid']));
		$id = $_GPC['id'];
		if(empty($pubid)){
				message('请先新建PUB信息',$this->createWebUrl('pubit'),'success');
				exit();
			}
		if($foo=='create'){
			$item = pdo_fetch('SELECT * FROM' .tablename($this->table_party)." WHERE id = :id AND weid = :weid",array(':id'=>$id,':weid'=>$_W['weid']));
			$timerange =array('starttime'=>$item['begintime'],'endtime'=>$item['endtime']);
			if(checksubmit()){
				$pubid = pdo_fetchcolumn('SELECT id FROM'.tablename($this->table_pub)." WHERE weid = :weid",array(':weid'=>$_W['weid']));
				if(empty($pubid)){
					message('请先设置PUB信息',$this->createWebUrl('pubit'),'error');
				}
				$data = array('title'=>$_GPC['title'],
					'weid'=>$_GPC['weid'],
					'cover'=>$_GPC['cover'],
					'txt'=>$_GPC['txt'],
					'createtime'=>TIMESTAMP,
					'begintime'=>strtotime($_GPC['timerange-start']),
					'endtime'=>strtotime($_GPC['timerange-end']),
					'pubid'=>$pubid);
				if(empty($id)){
					pdo_insert($this->table_party,$data);
					$id = pdo_insertid();
					message('活动已新建完成',$this->createWebUrl('partyit'),'success');
				}else{
					pdo_update($this->table_party,$data,array('id'=>$id));
					message('活动已更新完成',referer(),'success');
				}
			}
		}else if($foo=='delete'){
			if(empty($id)){
				message('缺失重要的数据','','error');
			}
			pdo_delete($this->table_party,array('weid'=>$_W['weid'],'id'=>$id));
			message('删除成功',referer(),'success');
		}else{
			$list = pdo_fetchall('SELECT * FROM'.tablename($this->table_party)." WHERE weid = :weid",array(':weid'=>$_W['weid']));
		}
		include $this->template('partyit');
	}
	public function doWebDeleteall(){
		global $_W,$_GPC;
		$id = $_GPC['id'];
		pdo_delete($this->table_pub,array('weid'=>$_W['weid'],'id'=>$id));
		pdo_delete($this->table_party,array('weid'=>$_W['weid'],'pubid'=>$id));
		pdo_delete($this->table_photos,array('weid'=>$_W['weid'],'pubid'=>$id));
		pdo_delete('jdg_pub_chatfans',array('weid'=>$_W['weid'],'pubid'=>$id));
		pdo_delete('jdg_pub_chatcomments',array('weid'=>$_W['weid'],'pubid'=>$id));
		/*添加更多删除语句..*/
			message('已经成功删除所有信息',referer(),'success');
	}
	public function doWebPhotoit(){
		global $_W,$_GPC;
		$list = pdo_fetchall('SELECT t1.id,user_name,t1.createtime,t1.img_url,t1.from_user,likeit,pub_name FROM'.tablename($this->table_photos)." AS t1 JOIN ".tablename($this->table_pub)." AS t2 ON t1.pubid = t2.id WHERE t1.weid = :weid",array(':weid'=>$_W['weid']));
		/*print_r($list);*/
		/*exit();*/
		if($_GPC['foo']=='delete'){
			if(empty($_GPC['id'])){
				message('丢失重要参数','','error');
			}
			$photo = pdo_fetch('SELECT * FROM'.tablename($this->table_photos)." WHERE id =:id",array(':id'=>$_GPC['id']));
			file_delete($photo['img_url']);
			$id =pdo_delete($this->table_photos,array('id'=>$_GPC['id'],'weid'=>$_W['weid']));
			if($id==false){
				message('系统正忙哦','','error');
			}else{
				message('删除成功了哦',referer(),'success');
			}

		}
		include $this->template('photoit');
	}
	public function doMobileWineark(){
		global $_W,$_GPC;
		
		$user_agent = $_SERVER ['HTTP_USER_AGENT'];
		if (strpos ( $user_agent, 'MicroMessenger' ) === false) {
			echo "本页面仅支持微信访问!非微信浏览器禁止浏览!";
			exit ();
		}
		
		$wineid = $_GPC['wineid'];
		$id = $_GPC['id'];
		if(empty($id)){
			message('缺失重要的数据','','error');
		}
		
		$list = pdo_fetch('SELECT * FROM'.tablename($this->table_pub)." WHERE weid = :weid AND id = :id",array(':weid'=>$_W['weid'],':id'=>$id));
		
		$pindex = max(1, intval($_GPC['page']));
	
		$psize = 6;
		$bsize=6;
	
		$list_left = pdo_fetchall('SELECT * FROM'.tablename($this->table_photos) ." WHERE weid = ".$_W['weid']." AND pubid = ".$id." AND id%2=1 ORDER BY id DESC LIMIT " . ($pindex - 1) * $bsize . ',' . $bsize);
		if($wineid==1){
			$total = pdo_fetchcolumn('select COUNT(*) from ' .tablename($this->table_winegoods)." isg,(SELECT id FROM " .tablename($this->table_wine)." isc where isc.parentid = (SELECT id FROM " .tablename($this->table_wine)." sc WHERE  sc.name=:name and sc.weid=:weid )) ishca where isg.ccate=ishca.id and isg.status=1 and isg.deleted=0 ",array(':name'=>'夜店服务',':weid'=>$_W['weid']));			
			$Winlist =  pdo_fetchall('select isg.* from ' .tablename($this->table_winegoods)." isg,(SELECT id FROM " .tablename($this->table_wine)." isc where isc.parentid = (SELECT id FROM " .tablename($this->table_wine)." sc WHERE  sc.name=:name and sc.weid=:weid )) ishca where isg.ccate=ishca.id and isg.status=1 and isg.deleted=0 LIMIT " . ($pindex - 1) * $bsize . ',' . $bsize,array(':name'=>'夜店服务',':weid'=>$_W['weid']));		
		
			$pager = pagination($total, $pindex, $bsize);
			
		}else if($wineid==2){
			$total = pdo_fetchcolumn('select COUNT(*) from ' .tablename($this->table_winegoods)." isg,(SELECT id FROM " .tablename($this->table_wine)." sc WHERE  sc.name=:name and sc.weid=:weid ) ishca where isg.ccate=ishca.id and isg.status=1 and isg.deleted=0 ",array(':name'=>'小吃',':weid'=>$_W['weid']));
			$Winlist = pdo_fetchall('select isg.* from ' .tablename($this->table_winegoods)." isg,(SELECT id FROM " .tablename($this->table_wine)." sc WHERE  sc.name=:name and sc.weid=:weid ) ishca where isg.ccate=ishca.id and isg.status=1 and isg.deleted=0 LIMIT " . ($pindex - 1) * $bsize . ',' . $bsize,array(':name'=>'小吃',':weid'=>$_W['weid']));
					$pager = pagination($total, $pindex, $bsize);
		}else{
			$total = pdo_fetchcolumn('select COUNT(*) from ' .tablename($this->table_winegoods)." isg,(SELECT id FROM " .tablename($this->table_wine)." sc WHERE  sc.name=:name and sc.weid=:weid ) ishca where isg.ccate=ishca.id and isg.status=1 and isg.deleted=0 ",array(':name'=>'酒水',':weid'=>$_W['weid']));
			$Winlist = pdo_fetchall('select isg.* from ' .tablename($this->table_winegoods)." isg,(SELECT id FROM " .tablename($this->table_wine)." sc WHERE  sc.name=:name and sc.weid=:weid ) ishca where isg.ccate=ishca.id and isg.status=1 and isg.deleted=0 LIMIT " . ($pindex - 1) * $bsize . ',' . $bsize,array(':name'=>'酒水',':weid'=>$_W['weid']));
			$pager = pagination($total, $pindex, $psize);
		}
			
		include $this->template('wineark');
	}

public function doMobilewine(){
		global $_W,$_GPC;
		
		$user_agent = $_SERVER ['HTTP_USER_AGENT'];
		if (strpos ( $user_agent, 'MicroMessenger' ) === false) {
			echo "本页面仅支持微信访问!非微信浏览器禁止浏览!";
			exit ();
		}
		
		$wineid = $_GPC['wineid'];
		$weid = $_W['weid'];
		$id = $_GPC['id'];
		if(empty($id)){
			message('缺失重要的数据','','error');
		}
		$list = pdo_fetch('SELECT * FROM'.tablename($this->table_pub)." WHERE weid = :weid AND id = :id",array(':weid'=>$_W['weid'],':id'=>$id));
		$fansid=$_W['fans']['from_user'];
		$status = pdo_fetch("select fansid from ".tablename($this->table_wineadmin)."where 1=1 and fansid = '{$fansid}'");	
		$select = pdo_fetch("select id,content from ims_jdg_pub_rule where 1=1 and weid={$weid}");
		
		if($status){
		$result = pdo_fetch("select snid,FROM_UNIXTIME(creattime)creattime,(CASE  status when 0 then '暂未存酒' else '已经存酒'end)status from".tablename($this->table_wineadmin)."where 1=1 and fansid='{$fansid}'");
		
		include $this->template('winetrue');
		
		}else{
			include $this->template('wine');
		}
	}
	
	public function doMobiles(){
		global $_W,$_GPC;
		
		
		$weid = $_GPC['weid'];
		
		$list = pdo_fetch('SELECT * FROM'.tablename($this->table_pub)." WHERE weid = :weid AND id = :id",array(':weid'=>$_W['weid'],':id'=>$id));
		$time = date('Y-m-d H:i:s',time());
		$time1=time();
		$fansid=$_W['fans']['from_user'];
		
		$name = $this->getUserInfo($fansid);
		$username = $name['nickname'];
		
		$status = pdo_fetch("select fansid from ".tablename($this->table_wineadmin)." where 1=1 and fansid = :fansid and weid = :weid",array(':fansid'=>$fansid,':weid'=>$_W['weid']));	
		if(!empty($status)){
			
			exit;
		}
		while (true) {
			$rand =rand(100001,999998);
			$isok = pdo_fetch('SELECT COUNT(*) AS num from'.tablename($this->table_wineadmin)." WHERE snid = :snid" ,array(':snid'=>$rand));
			if($isok['num']==0){
				break;
			}
		}
		$status = pdo_query("insert into ".tablename($this->table_wineadmin)."(snid,fansid,creattime,name,weid) values('{$rand}','{$fansid}',{$time1},'{$username}','{$weid}')");
		
		
		if($status==FALSE){
			}else{
				
			$jd['div'] = "
            <br /></br></br><div id='deposit-card'><ul><li><p>存酒卡号：</p><p>{$rand}</p></li><li><p>使用说明：</p><p>请前往前台，报存酒卡号进行存酒。</p> </li>
                    <li>
                        <p>状　　态：</p>
                        <p><span style='color:blue;'>等待存酒</span></p>
                    </li>
                    <li>
                        <p>申请时间：</p>
                        <p>{$time}</p>
                    </li>                         
				</ul>
			</div>";
		$jd['IsSuccess']=true;
		$jd['code']=$rand;
			echo json_encode($jd);
		}
	
		 
		 
		
	}
}