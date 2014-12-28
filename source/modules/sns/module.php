<?php
/**
 * 互动社区模块
 *
 * [19.3cm qq81324093] Copyright (c) 2013 wangxinglin.com
 */
defined('IN_IA') or exit('Access Denied');

class SnsModule extends WeModule {
	public $name = 'snsModule';
	public $title = '互动交流';
	public $ability = '';
	public $tablename = 'sns';
	
	
	public function fieldsFormDisplay($rid = 0) {
		global $_W;
		if (!empty($rid)) {
			$reply = pdo_fetch("SELECT * FROM ".tablename($this->tablename)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));
			
		} 
		include $this->template('sns/form');
	}

	public function fieldsFormValidate($rid = 0) {
		return true;
	}

	public function fieldsFormSubmit($rid = 0) {
		global $_GPC, $_W;
		$id = intval($_GPC['reply_id']);
		$insert = array(
			'rid' => $rid,
			'bzuid'=>$_GPC['bzuid'],
			'weid'=>$_W['weid'],
			'picture' => $_GPC['picture'],
			'description' => htmlspecialchars_decode($_GPC['description']),
			'type' => $_GPC['type'],
			'rule' => $_GPC['rule'],
			'default_tips' => $_GPC['default_tips'],
			'send_tips' =>$_GPC['send_tips'],
			'jhcredit' => intval($_GPC['jhcredit']),
			'postcredit' => intval($_GPC['postcredit']),
			'replycredit' => intval($_GPC['replycredit']),
			'isshow' => intval($_GPC['isshow']),
		);
		if (empty($id)) {
			pdo_insert($this->tablename, $insert);
		} else {
			if (!empty($_GPC['picture'])) {
				file_delete($_GPC['picture-old']);
			} else {
				unset($insert['picture']);
			}
			pdo_update($this->tablename, $insert, array('id' => $id));
		}
		
		
		//模型参数设置完成
		
	}

	public function ruleDeleted($rid = 0) {
		global $_W;
		$replies = pdo_fetchall("SELECT id, picture FROM ".tablename($this->tablename)." WHERE rid = '$rid'");
		$deleteid = array();
		if (!empty($replies)) {
			foreach ($replies as $index => $row) {
				file_delete($row['picture']);
				$deleteid[] = $row['id'];
			}
		}
		pdo_delete($this->tablename, "id IN ('".implode("','", $deleteid)."')");
		return true;
	}
	public function se($expire = 3600){
		global $_W;
		$expire = intval($expire);
		
		session_start();
		
		$_SESSION['contextexpire'] = empty($expire) ? 0 : (TIMESTAMP + $expire);
		$_SESSION['contextexpiretime'] = $expire;
		if($_GPC['login']=="out"||$_SESSION['contextexpire'] < TIMESTAMP){
		unset($_SESSION);
		session_destroy();
			}
		
	
		}
	public function dologin(){
		global $_W ,$_GPC;
		
		//$_SESSION['user'] =  $_GPC['from_user'];
		$uid= intval($_GPC['uid']);
		$user=$this->doUserinfo($uid);
		
		$zt = pdo_fetch("SELECT lastvisit FROM ".tablename('fans_status')." WHERE uid ='{$uid}'  LIMIT 1");	
		$url=create_url('index/module', array('name' => 'sns', 'do' => 'list', 'id' => intval($_GPC['id'])));
		$wechat = pdo_fetch("SELECT * FROM ".tablename('wechats')." WHERE weid = '{$user['weid']}'");
		if((TIMESTAMP-$zt['lastvisit'])<60&&$zt){
			
			$this->se(1200);
			$_SESSION['user'] =  base64_encode(authcode($user['from_user'], 'ENCODE'));	
			pdo_delete('fans_status', array('uid'=>$uid));
			header("Location:$url "); 
			
			/*die('<script>alert("验证成功'.(TIMESTAMP-$zt['lastvisit']).'");location.href = "'.$url.'";</script>');*/
			exit; 
		}
		else{
			//exit('<div style="margin:80px auto; txt-align:center "><a href="http://weixin.qq.com/r/-XVIUFbEMQtOrRIz9yDv"><img src=http://800826.duapp.com/qrcode_1.jpg /></a><br /> 扫描上图,或者点击上图,关注<b>[广安吧]</b>后,发送:[微吧]参与讨论</div>');
			include $this->template('sns/login');
			}
			
			
		}
		
	function replaceHtmlAndJs($document)
	{
		$document = trim($document);
		if (strlen($document) <= 0)
		{
  		 return $document;
		}
		$search = array ("'<script[^>]*?>.*?</script>'si",  // 去掉 javascript
                  "'<[\/\!]*?[^<>]*?>'si",          // 去掉 HTML 标记
                  "'([\r\n])[\s]+'",                // 去掉空白字符
                  "'&(quot|#34);'i",                // 替换 HTML 实体
                  
                  );                    // 作为 PHP 代码运行

		$replace = array ("",
                   "",
				   "",
				   "",
                   );

		return @preg_replace ($search, $replace, $document);
	}		
	public function doUserinfo($uid) {//用户信息查询
		global $_GPC, $_W;
		$fromuser =empty($uid)? authcode(base64_decode($_GPC['from_user']), 'DECODE'):$uid;
		
		if (empty($fromuser)) {
			exit('非法参数');
		}
		if(eregi("^[0-9]+$",$fromuser))
		{
			$where="id ='".$fromuser."'";
		}
		else{
			$where="from_user ='".$fromuser."'";
			}
				
		$user = pdo_fetch("SELECT * FROM ".tablename('fans')." WHERE $where  LIMIT 1");
		if(!$user){
			$result = array('status' => '-1', 'message' => '未找到该用户数据');
		}
		//判断性别数据
							if($user['gender']==0){
								$user['sex']='保密';
								}
							elseif($user['gender']==1){
								$user['sex']='男';
								}
							else{	$user['sex']='女';}
							
		$result = array(
		'status' => 0, 
		'id'=>$user['id'],
		'from_user'=>$user['from_user'],
		'avatar'=>$user['avatar'],
		'nickname'=>empty($user['nickname'])?'匿名':$user['nickname'],
		'realname'=>$user['realname'],
		'mobile' =>$user['mobile'],
		'age' =>$user['age'],
		'wxusr'=>$user['wxusr'],
		'x'=>$user['x'],
		'y'=>$user['y'],
		'city'=>$user['city'],
		'sex'=>$user['sex'],
		'credit'=>$user['credit'],
		'qq'=>$user['qq'],
		'isblacklist'=>$user['isblacklist'],
		'isshow'=>$user['isshow'],
		'isjoin'=>$user['isjoin'],	
		'follow'=>$user['follow'],
		'weid'=>$user['weid'],
		'createtime'=>$user['createtime'],
		'message' => '成功查询',
		
		);
		if(empty($uid)){
		unset($result['from_user']);	
		message($result, '', 'ajax');
		exit;
		}
		else
		{return $result;
			}

	}
	public function doPmem($uid,$pmem=0,$id=0) {//用户权限相关
		global $_GPC, $_W;
		$fromuser =empty($uid)? authcode(base64_decode($_GPC['from_user']), 'DECODE'):$uid;
		if (empty($fromuser)) {
			exit('非法参数');
		}
		//是否有用户权限判断
		$user=$this->doUserinfo($fromuser);
		if(!$user){
			$result = array('status' => '-1', 'message' => '未找到用户数据');
		}
		//吧主权限判断
		if($pmem==1){
			$sns = pdo_fetch("SELECT id, bzuid FROM ".tablename('sns')." WHERE rid = '{$id}' LIMIT 1");
			$bazu=explode(',',$sns['bzuid']);
			if(in_array($user['id'],$bazu)){
				$result = array('status' => 1, 'message' => '恭喜您成为吧主');	
			}
			else{
			$result = array('status' => '-1', 'message' =>  '您不是管理，无权限');
			}
		}
		//普通用户权限判断
		else{
			if($user['isshow']==1){
			$result['message'] = '您还未通过审核，请联系管理员微信号wangnrg,帮助您审核通过!';
			$result['status'] ='-1';
			}
			elseif($user['isblacklist']==1){
			$result['message'] = '您已经被拉进小黑屋了!不能发表!';
			$result['status'] ='-1';
			}
			else
			{$result['status']=1;
			$result['message'] = '成功';
			}
		
		}
		if(empty($uid)){
		message($result, '', 'ajax');
		exit;
		}
		else
		{return $result;
			}

	}
	public function dopost() {
		global $_GPC, $_W;
		$fromuser=$_GPC['from_user']?authcode(base64_decode($_GPC['from_user']), 'DECODE'):$_W['fans']['from_user'];
		
		if (empty($fromuser)) {
			exit('非法参数');
		}
		$id = intval($_GPC['id']);
		$sns = pdo_fetch("SELECT id, type, default_tips, send_tips, rule, picture, postcredit,description FROM ".tablename('sns')." WHERE rid = '{$id}' LIMIT 1");
		if (empty($sns)) {
			exit('非法参数');
		}
		
		$fans=$this->doUserinfo($fromuser);
		$pmem=$this->doPmem($fromuser);
		if($fans['status']=='-1'){
			//message($result, create_url('index/module', array('name' => 'sns', 'do' => 'list', 'id' => intval($_GPC['id']), 'from_user' => $_GPC['from_user'])), 'ajax');}
			message($fans, create_url('mobile/module', array('name' => 'sns', 'do' => 'list', 'id' => intval($_GPC['id']))), 'ajax');
			exit;
			}
		if($pmem['status']=='-1'){
			//message($result, create_url('index/module', array('name' => 'sns', 'do' => 'list', 'id' => intval($_GPC['id']), 'from_user' => $_GPC['from_user'])), 'ajax');}
			message($pmem, create_url('mobile/module', array('name' => 'sns', 'do' => 'list', 'id' => intval($_GPC['id']))), 'ajax');
			exit;
			}
			
		$data = array(
			'rid' =>$_GPC['id'],
			'post_uid' =>$fromuser,
			'title' =>ihtmlspecialchars($_GPC['title']),
			'content' =>ihtmlspecialchars(emotion($_GPC['content'])),
			'post_time' =>TIMESTAMP,
			'weid' =>$fans['weid'],
			
		);
		if (empty($data['title'])) {
				$result['status'] = '-1'; 
				$result['message'] = '请填写标题！';
				
				message($result, create_url('mobile/module', array('name' => 'sns', 'do' => 'list', 'id' => intval($_GPC['id']))), 'ajax');
				exit;
				//message($result, $this->createMobileUrl('list',array('id'=>intval($_GPC['id']))), 'ajax');
			}
		if (empty($data['content'])||strlen($data['content'])<20) {
				$result['status'] = '-1'; 
				$result['message'] = '请填写您内容！且内容不能小于20个字符!';
				
				message($result, create_url('mobile/module', array('name' => 'sns', 'do' => 'list', 'id' => intval($_GPC['id']))), 'ajax');
				//message($result, $this->createMobileUrl('list',array('id'=>intval($_GPC['id']))), 'ajax');
				exit;
			}
		
		$data2['credit'] = $fans['credit']+$sns['postcredit'];
		
		pdo_insert('sns_post', $data);
		pdo_update('fans', $data2, array('from_user' => $fromuser));
		
		$result = array('status' => 0, 'message' => '发表成功!');
		message($result, create_url('mobile/module', array('name' => 'sns', 'do' => 'list', 'id' => intval($_GPC['id']))), 'ajax');
		//message($result, $this->createMobileUrl('list',array('id'=>intval($_GPC['id']))), 'ajax');
		}
	
	public function doReply() {
		global $_GPC, $_W;
		//评论用户ID
		//$replyid =authcode(base64_decode($_GPC['uid']), 'DECODE');
		$replyid=$_GPC['uid']?authcode(base64_decode($_GPC['uid']), 'DECODE'):$_W['fans']['from_user'];
		//被楼中评论的评论用户ID
		if(empty($_GPC['to_uid'])){
		$touid='';
		}
		else{
		$touid =$this->doUserinfo($_GPC['to_uid']);
		}
		$to_uid=$touid['from_user'];
		//主题作者ID
		//$postid =authcode(base64_decode($_GPC['postuid']), 'DECODE');
		$pid =$this->doUserinfo($_GPC['postuid']);
		//print_r($pid);exit;
		$postid =$pid['from_user'];
		
		//主题ID
		$id = intval($_GPC['id']);
		//版块规则ID
		$rid = intval($_GPC['rid']);
		$sns = pdo_fetch("SELECT id, type, default_tips, send_tips, rule, picture, replycredit,description FROM ".tablename('sns')." WHERE rid = '{$rid}' LIMIT 1");
		$post= pdo_fetch("SELECT * FROM ".tablename('sns_post')." WHERE post_id= '{$id}' AND  is_del='0' LIMIT 1");
		if (empty($replyid)||empty($postid)||empty($id)||empty($rid)) {
			exit('非法参数');
		}
		if (empty($sns)) {
			$result['status'] = '-1';
			$result['message'] = '找不到模块配置参数';
			message($result, create_url('mobile/module', array('name' => 'sns', 'do' => 'list', 'id' => $rid, 'from_user' => $replyid)), 'ajax');
			exit;
		}
		if (empty($post)) {
			$result['status'] = '-1';
			$result['message'] = '未找到你要回复的贴子，请确实贴子ID是否正确';
			message($result, create_url('mobile/module', array('name' => 'sns', 'do' => 'list', 'id' => $rid, 'from_user' => $replyid)), 'ajax');
			exit;
		}
		
		
		
		$fans =$this->doUserinfo($replyid);
		$result=$this->doPmem($replyid);
		if($fans['status']=='-1'){
			//message($result, create_url('index/module', array('name' => 'sns', 'do' => 'list', 'id' => intval($_GPC['id']), 'from_user' => $_GPC['from_user'])), 'ajax');}
			message($fans, create_url('mobile/module', array('name' => 'sns', 'do' => 'list', 'id' => intval($_GPC['id']))), 'ajax');
			exit;
			}
		
		if($result['status']=='-1'){
			message($result, create_url('mobile/module', array('name' => 'sns', 'do' => 'list', 'id' => $rid, 'from_user' => $replyid)), 'ajax');
			exit;
			}
		if($post['lock']==1){
			$result['status'] = '-1';
			$result['message'] = '此贴已经被锁定。请返回!';
			message($result, create_url('mobile/module', array('name' => 'sns', 'do' => 'list', 'id' => $rid, 'from_user' => $replyid)), 'ajax');
			exit;
		}
		
		$data = array(
			'post_id' =>$_GPC['id'],//贴子ID
			'post_uid' =>$postid,//贴子作者ID
			'to_reply_id' =>$_GPC['to_reply_id']?$_GPC['to_reply_id']:0,//回复评论的ID，这个是作楼中楼即评论中的评论
			'uid' =>$replyid,//评论者用户ID
			'to_uid' =>$to_uid?$to_uid:0,//被回复评论作者ID
			'content' =>ihtmlspecialchars(emotion($_GPC['content'])),
			'ctime' =>TIMESTAMP,
			'storey' =>$_GPC['storey'],//楼层
			'weid' =>$fans['weid'],
			'rid' =>$_GPC['rid'],
		);
		//插入内容开始
		if (empty($data['content'])||strlen($data['content'])<20) {
				$result['status'] = '-1';
				$result['message'] = '请填写您内容！且内容不能小于20个字符!';
				message($result, '', 'ajax');
				exit;
			}
		$data2['credit'] = $fans['credit']+$sns['replycredit'];
		$data3['reply_count']=$post['reply_count']+1;
		$data3['last_reply_time']=TIMESTAMP;
		$data3['last_reply_uid']=$replyid;
		pdo_insert('sns_reply', $data);
		pdo_update('fans', $data2, array('from_user' => $replyid));
		pdo_update('sns_post', $data3, array('post_id' => $id));
		
		$result = array(
		'status' => 0, 
		'uid' =>$data['uid'],
		'to_uid' =>$data['to_uid'],
		'to_reply_id' =>$data['to_reply_id'],
		'content' =>$data['content'],
		'ctime' => date('Y-m-d H:i:s', $data['ctime']),
		'storey'=>$data['storey'],
		'message' => '评论成功!');
		
		message($result, create_url('mobile/module', array('name' => 'sns', 'do' => 'view', 'id' => intval($_GPC['id']), 'rid' => intval($_GPC['rid']), 'from_user' => $replyid)), 'ajax');
		exit;
	}
	
	
	
/*
	public function dobzadmin() {
		global $_W,$_GPC;
		$weid=intval($_GPC['weid']);
		$id = intval($_GPC['id']);
		$rid = intval($_GPC['rid']);
		$fromuser = authcode(base64_decode($_GPC['from_user']), 'DECODE');
		
		$blackid=intval($_GPC['blackid']);
		//几项管理操作提交命令
		$admin=intval($_GPC['admin']);
		if (empty($rid)) {

			$url=create_url('index/module', array('name' => 'sns', 'do' => 'login', 'id' => intval($_GPC['id'])));
				header("Location:$url ");
		}
		$sns = pdo_fetch("SELECT id, jhcredit, replycredit,postcredit FROM ".tablename('sns')." WHERE rid = '{$rid}' LIMIT 1");
		if(!empty($id)){
		$post= pdo_fetch("SELECT post_id,post_uid FROM ".tablename('sns_post')." WHERE post_id= '{$id}' AND is_del=0   LIMIT 1");
		if(!$post){
			$result['status']='-1';
			$result['message'] = '找不到该贴，可能已经被删除';
			}	
		}
		$membermodules = pdo_fetch("SELECT b.mid, b.name, b.issystem FROM ".tablename('modules')." AS b LEFT JOIN ".tablename('members_modules')." AS a ON a.mid = b.mid WHERE a.uid = :uid AND b.name <> 'sns'  LIMIT 1", array(':uid' => $_W['uid']));
		if($_W['isfounder']||!$membermodules){
			$result['status']=1;
			}else{
		$pmem=$this->doPmem($fromuser,1,$rid);
		
		if($pmem['status']==1){
			$result['status']=1;
		}
		else{
			$result['status']='-1';
			$result['message'] = '您没有此项操作权限';
			}
		}
		//顶，取顶
		if($admin==1)
		{
			pdo_update('sns_post', array('top' => intval($_GPC['top'])), array('post_id' => $id));
			
			$result['status']=1;
			$result['message'] = '操作成功';
			
		}
		if($admin==2)
		{
			$user=$this->doUserinfo($post['post_uid']);
			pdo_update('sns_post', array('digest' => intval($_GPC['digest'])), array('post_id' => $id));
			$result['status']=1;
			if(intval($_GPC['digest'])==1)
			{
			pdo_update('fans', array('credit' => $user['credit']+$sns['jhcredit']), array('from_user' => $post['post_uid']));
			
			$result['message'] = '加精华操作成功';}
			else{
				pdo_update('fans', array('credit' => $user['credit']-$sns['jhcredit']), array('from_user' => $post['post_uid']));
			
			$result['message'] = '取消精华操作成功';
				}
			
			
		}
		if($admin==3)
		{
			
			pdo_update('sns_post', array('is_del' => intval($_GPC['is_del'])), array('post_id' => $id));
			pdo_update('sns_reply', array('is_del' => intval($_GPC['is_del'])), array('post_id' => $id));
			
			$result['status']=1;
			if(intval($_GPC['is_del'])){
				$user=$this->doUserinfo($post['post_uid']);
				pdo_update('fans', array('credit' => $user['credit']-$sns['postcredit']), array('from_user' => $post['post_uid']));
			$result['message'] = '删贴操作成功';
			}
			else{$result['message'] = '恢复删贴操作成功';}
			
		}
		if($admin==4)
		{
			pdo_update('fans', array('isblacklist' => intval($_GPC['black'])), array('id' => $blackid));
			$result['status']=1;
			if(intval($_GPC['black'])){
			$result['message'] = '关小黑屋操作成功';
			}
			else{$result['message'] = '恢复用户操作成功';}
			
		}
		if($admin==5)
		{
			pdo_update('sns_post', array('lock' => intval($_GPC['lock'])), array('post_id' => $id));
			$result['status']=1;
			$result['message'] = '锁贴/解锁操作成功';
			
		}
		if($admin==6)
		{	
			$reply=pdo_fetch("SELECT uid FROM ".tablename('sns_reply')." WHERE reply_id= '{$id}' LIMIT 1");
			pdo_update('sns_reply', array('is_del' => intval($_GPC['is_del'])), array('reply_id' => $id));
			$user=$this->doUserinfo($reply['uid']);
			pdo_update('fans', array('credit' => $user['credit']-$sns['replycredit']), array('from_user' => $reply['uid']));
			$result['status']=1;
			$result['message'] = '删/恢复评论操作成功';
			
		}
		message($result,'', 'ajax');	
	}
	//贴吧分类列表
	public function dosnslist(){
		global $_GPC, $_W;
		checklogin();
		$id = intval($_GPC['id']);
		if (checksubmit('delete') && !empty($_GPC['select'])) {
			pdo_delete($this->tablename, " id  IN  ('".implode("','", $_GPC['select'])."')");
			message('删除成功！', create_url('index/module', array('do' => 'snslist', 'name' => 'sns')));
		}
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;
		$list = pdo_fetchall("SELECT a.* ,b.name, b.status FROM ".tablename($this->tablename)." AS a LEFT JOIN  ".tablename('rule')." AS b ON a.rid=b.id WHERE a.weid='{$_W['weid']}'  ORDER BY a.id ASC LIMIT ".($pindex - 1) * $psize.",{$psize}");
		
		if (!empty($list)) {
			$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->tablename) . "");
			$pager = pagination($total, $pindex, $psize);	
			foreach($list as $k=> $row){
			  //$rule = pdo_fetch("SELECT  name,status  FROM ".tablename('rule')." WHERE id='{$row['rid']}' LIMIT 1");
              //$row['title']=$rule['name'];
               $posttotal[$k]= pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('sns_post') . " WHERE  rid = '{$row['rid']}' AND is_del=0  ");
              //print_r($row['title']);
				
				}		
			unset($row);
		}
		include $this->template('sns/snslist');
	}
	//后台管理
	 
  	public function doManage() {
		global $_GPC, $_W;
		checklogin();
		
		$id = intval($_GPC['id']);
		$reply=intval($_GPC['reply']);
		$membermodules = pdo_fetch("SELECT b.mid, b.name, b.issystem FROM ".tablename('modules')." AS b LEFT JOIN ".tablename('members_modules')." AS a ON a.mid = b.mid WHERE a.uid = :uid AND b.name <> 'sns'  LIMIT 1", array(':uid' => $_W['uid']));
		//开始取版主ID，主要用于加精置顶等操作。
		$sns = pdo_fetch("SELECT id, bzuid FROM ".tablename('sns')." WHERE rid = '{$id}' LIMIT 1");
      	if($_W['isfounder']||!$membermodules){
			print_r($membermodules);
			}else{
      	if(!$sns['bzuid']){
			exit('未设定版主ID。请去规则管理里面设定版主ID。');
			}
		$bazu=explode(',',$sns['bzuid']);
		$bazuid=$this->doUserinfo($bazu[0]);
		$fromuser=base64_encode(authcode($bazuid['from_user'], 'ENCODE'));
        }
		//取版主ID完成
		if (checksubmit('verify') && !empty($_GPC['select'])) {
			if($reply)
			{
				pdo_update('sns_reply', array('is_del' => 0), " reply_id  IN  ('".implode("','", $_GPC['select'])."')");
				message('评论恢复成功！', create_url('index/module', array('do' => 'manage', 'name' => 'sns', 'id' => $id, 'reply'=>1, 'page' => $_GPC['page'])));
			}
			else{
			pdo_update('sns_post', array('is_del' => 0), " post_id  IN  ('".implode("','", $_GPC['select'])."')");
			message('贴子恢复成功！', create_url('index/module', array('do' => 'manage', 'name' => 'sns', 'id' => $id, 'page' => $_GPC['page'])));
			}
		}
		if (checksubmit('black') && !empty($_GPC['select'])) {
			if($reply)
			{pdo_update('sns_reply', array('is_del' => 1), " reply_id  IN  ('".implode("','", $_GPC['select'])."')");
			message('评论拉黑成功！', create_url('index/module', array('do' => 'manage', 'name' => 'sns', 'id' => $id, 'reply'=>1, 'page' => $_GPC['page'])));
			}
			else{
			pdo_update('sns_post', array('is_del' => 1), " post_id  IN  ('".implode("','", $_GPC['select'])."')");
			
			message('贴子拉黑成功！', create_url('index/module', array('do' => 'manage', 'name' => 'sns', 'id' => $id, 'page' => $_GPC['page'])));
			}
		}
		if (checksubmit('delete') && !empty($_GPC['select'])) {
			if($reply)
			{pdo_delete('sns_reply', " reply_id  IN  ('".implode("','", $_GPC['select'])."')");
			message('评论彻底删除成功！', create_url('index/module', array('do' => 'manage', 'name' => 'sns', 'id' => $id, 'reply'=>1, 'page' => $_GPC['page'])));
			}
			else{
			pdo_delete('sns_post', " post_id  IN  ('".implode("','", $_GPC['select'])."')");
			
			message('贴子彻底删除成功！', create_url('index/module', array('do' => 'manage', 'name' => 'sns', 'id' => $id, 'page' => $_GPC['page'])));
			}
		}
		$isshow = isset($_GPC['isshow']) ? intval($_GPC['isshow']) : 0;
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;

		if($reply){
			//评论查询
			
		$list = pdo_fetchall("SELECT * FROM ".tablename('sns_reply')." WHERE weid = '{$_W['weid']}' AND is_del = '$isshow' ORDER BY ctime DESC LIMIT ".($pindex - 1) * $psize.",{$psize}");
		if (!empty($list)) {
			$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('sns_reply') . " WHERE weid = '{$_W['weid']}' AND is_del = '$isshow'");
			$pager = pagination($total, $pindex, $psize);

			foreach ($list as &$row) {
				$user[]=$this->doUserinfo($row['uid']);
			}
		}
			
		}
		else{
			//贴子查询
		
		$list = pdo_fetchall("SELECT * FROM ".tablename('sns_post')." WHERE weid = '{$_W['weid']}' AND is_del = '$isshow' ORDER BY post_time DESC LIMIT ".($pindex - 1) * $psize.",{$psize}");
		if (!empty($list)) {
			$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('sns_post') . " WHERE weid = '{$_W['weid']}' AND is_del = '$isshow'");
			$pager = pagination($total, $pindex, $psize);

			foreach ($list as &$row) {
				$user[]=$this->doUserinfo($row['post_uid']);
			}
		}
		
		}
		include $this->template('sns/manage');
	}

*/
	
}