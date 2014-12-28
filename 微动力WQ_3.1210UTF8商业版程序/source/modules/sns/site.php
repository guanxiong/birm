<?php
/**
 * 微吧管理模块微站定义
 *
 * @author 19.3cm
 * @url http://bbs.we7.cc/forum.php?mod=forumdisplay&fid=36&filter=typeid&typeid=1
 */
defined('IN_IA') or exit('Access Denied');

class SnsModuleSite extends WeModuleSite {
	public $name = 'snsModule';
	public $title = '互动交流';
	public $ability = '';
	public $tablename = 'sns';
	public function getProfileTiles() {
		global $_W;
		//这个操作被定义用来呈现微站个人中心上的管理链接，返回值为数组结构, 每个元素将被呈现为一个链接. 元素结构为 array('title'=>'标题', 'image'=>'图标', 'url'=>'链接目标', 'displayorder'=>'排序权重, 越高越往前')
      $menu=array(
      	array('title'=>'我的微贴', 'url'=> $this->createMobileUrl('mytie')),
      	);
		$list=pdo_fetchall("SELECT id,name FROM ".tablename('rule')." WHERE weid ='{$_W['weid']}' AND module='sns' ORDER BY id DESC ");
      	
		foreach($list as $m){
          			$menu[]=array('title'=>$m['name'],'url'=>$this->createMobileUrl('list',array('id'=>$m['id'])));
         
		}
		
		
		return $menu ;
	}
	public function getHomeTiles() {
		global $_W;
		//这个操作被定义用来呈现微站个人中心上的管理链接，返回值为数组结构, 每个元素将被呈现为一个链接. 元素结构为 array('title'=>'标题', 'image'=>'图标', 'url'=>'链接目标', 'displayorder'=>'排序权重, 越高越往前')
      $menu=array(
      	array('title'=>'我的微贴', 'url'=> $this->createMobileUrl('mytie')),
      	);
		$list=pdo_fetchall("SELECT id,name FROM ".tablename('rule')." WHERE weid ='{$_W['weid']}' AND module='sns' ORDER BY id DESC ");
      	
		foreach($list as $m){
          			$menu[]=array('title'=>$m['name'],'url'=>$this->createMobileUrl('list',array('id'=>$m['id'])));
         
		}
		
		
		return $menu ;
	}
	
	public function doMobileMytie() {
		global $_W, $_GPC;
		if (empty($_W['fans']['from_user'])){
			message('非法访问，请重新点击链接进入个人中心！');
		}
		$title = '我的微贴';
		echo "个人微贴正在建设中。敬请期待";
		
		//include $this->template('profile');
	}
	public function doMobileList() {
		//设置贴子列表
		global $_W, $_GPC;
		$id = intval($_GPC['id']);
		$fromuser=base64_encode(authcode($_W['fans']['from_user'], 'ENCODE'));
		
		$wechat = pdo_fetch("SELECT * FROM ".tablename('wechats')." WHERE weid = '{$_GPC['weid']}' LIMIT 1");
		if (empty($fromuser)||empty($id)) {
			
			include $this->template('login');
			exit;
				//message('非法访问，请重新发送：[微吧] 点击链接进入微吧！');
			}
		
		$sns = pdo_fetch("SELECT id, type, bzuid, default_tips, send_tips, rule, picture, description FROM ".tablename('sns')." WHERE rid = '{$id}' LIMIT 1");
		$title = pdo_fetchcolumn("SELECT name FROM ".tablename('rule')." WHERE id = :rid LIMIT 1", array(':rid' => $id));
		$type =$sns['type'];
		if (empty($sns)) {
			message('未找到您要找的模块！');
		} 
		$replytotal= pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('sns_reply') . " WHERE  rid = '{$id}' ");
		$user = $this->doUserinfo($_W['fans']['from_user']);
		
		if (empty($sns)) {
			message('未找到您的要找的信息。');
		} 
		$pmem=$this->doPmem($_W['fans']['from_user'],1,$id);
		$pindex = max(1, intval($_GPC['page']));
		$psize = 10;
		$where=intval($_GPC['jh'])==1 ?'AND digest=1' :'';
		
		$postlist = pdo_fetchall("SELECT * FROM ".tablename('sns_post')." WHERE rid = '{$id}' AND is_del=0  $where  ORDER BY top DESC ,post_time DESC, last_reply_time DESC LIMIT ".($pindex - 1) * $psize.','. $psize);
		
		if (!empty($postlist)) {
			 $posttotal= pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('sns_post') . " WHERE  rid = '{$id}' AND is_del=0  ");
			 $pager = pagination($posttotal, $pindex, $psize);
	
	}
	foreach($postlist as $row){
		$member[]=pdo_fetch("SELECT * FROM ".tablename('fans')." WHERE from_user = '{$row['post_uid']}'  LIMIT 1");
		
	}
      include $this->template('list');
	}
	public function doMobileView($replyid=0) {//贴子查看
		global $_GPC, $_W;
		$fromuser= base64_encode(authcode($_W['fans']['from_user'], 'ENCODE'));
		$id = intval($_GPC['id']);
		$rid = intval($_GPC['gid']);
		if (empty($fromuser)||empty($id)||empty($rid)) {
				message('非法访问，请重新发送：[微吧] 点击链接进入微吧！');
				}
		$title = pdo_fetchcolumn("SELECT name FROM ".tablename('rule')." WHERE id = :rid LIMIT 1", array(':rid' => $rid));
		$sns =pdo_fetch("SELECT * FROM ".tablename('sns')." WHERE rid = '{$rid}' LIMIT 1");
		$fans =$this->doUserinfo($_W['fans']['from_user']);
		$post= pdo_fetch("SELECT * FROM ".tablename('sns_post')." AS a LEFT JOIN ".tablename('fans')." AS b ON a.post_uid=b.from_user WHERE a.post_id= '{$id}' AND a.is_del=0   LIMIT 1");
		$title=$post['title'];
		//$postuid=base64_encode(authcode($post['post_uid'], 'ENCODE'));
		$postuid=$post['id'];
		if(empty($sns)){
			echo '对不起找不到你要找的互动模块';
			}
		if(empty($fans)){
			echo '对不起找不到你该用户信息';
			print_r($fans);
			}
		if (empty($post)){
			echo '对不起找不到该贴子，可能已经被删了';
			}
		
			$postdata['read_count']=$post['read_count']+1;
			pdo_update('sns_post', $postdata, array('post_id' => $id));
		//开始主题贴回复相关查询
		$pindex = max(1, intval($_GPC['page']));
		$psize = 10;
		$pmem=$this->doPmem($_W['fans']['from_user'],1,$rid);
		
		$where=$replyid==0?'AND storey<>0':'AND reply_id='.$replyid ;
		
		$reply = pdo_fetchall("SELECT * FROM ".tablename('sns_reply')." WHERE  post_id = '{$id}' AND rid = {$rid} AND is_del=0   $where ORDER BY ctime ASC LIMIT ".($pindex - 1) * $psize.','. $psize);
		if (!empty($reply)) {
			$replytotal= pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('sns_reply') . " WHERE post_id = '{$id}' AND rid = '{$rid}' AND is_del=0   $where");	
			$pager = pagination($replytotal, $pindex, $psize);
			//判断该回复是否为楼中楼
				
					foreach ($reply as $key=>$row)
					{
					$storey=$row['storey']+1;
					
					$reply2=$this->dolistreply($row['to_reply_id'],$id,$rid,$pindex,$psize);
					$userinfo[]=$this->doUserinfo($row['uid']);
					
					
					}
					//print_r($userinfo);exit;
			}
		else{
			$storey=2;
			}
			
      include $this->template('view');
	}
	public function dolistreply($replyid,$id,$rid,$pindex,$psize) {//评论楼中楼
	global $_GPC, $_W;
	$where=$replyid==0?'AND storey=0':'AND reply_id='.$replyid ;
	//$where='AND reply_id='.$replyid ;
	$reply = pdo_fetchall("SELECT * FROM ".tablename('sns_reply')." AS a LEFT JOIN ".tablename('fans')." AS b ON a.uid=b.from_user WHERE  a.post_id = '{$id}' AND a.rid = {$rid}  $where ORDER BY a.ctime ASC LIMIT ".($pindex - 1) * $psize.','. $psize);
	/*foreach ($reply as $key=>$row)
					{
					$row['user']=$this->doUserinfo($row['uid']);
					//print_r($userinfo);exit;	
					}
	*/
	return $reply;
	}
	
	public function doMobilebzshow() {
		global $_W,$_GPC;
		$rid = intval($_GPC['rid']);
		$fromuser=base64_encode(authcode($_W['fans']['from_user'], 'ENCODE'));
		$user=$this->doUserinfo($_W['fans']['from_user']);
		if (empty($fromuser)||empty($rid)) {
			message('非法访问，请重新发送：[微吧] 点击链接进入微吧！');
		}
		$pmem=$this->doPmem($_W['fans']['from_user'],1,$rid);
		if($pmem['status']==-1){
			exit('对不起您没权限进入管理中心！');
		}
		$pindex = max(1, intval($_GPC['page']));
		$psize = 10;
		$admin=intval($_GPC['admin']);
		if($admin==1){
			//$list= pdo_fetchall("SELECT * FROM ".tablename('sns_post')." AS a LEFT JOIN ".tablename('fans')." AS b ON a.post_uid=b.from_user WHERE a.is_del='1' AND a.rid='{$rid}' ORDER BY a.post_time DESC  LIMIT".($pindex - 1) * $psize.",{$psize}");
			$list = pdo_fetchall("SELECT * FROM ".tablename('sns_post')." WHERE rid = '{$rid}' AND is_del=1   ORDER BY top DESC ,last_reply_time, post_time DESC LIMIT ".($pindex - 1) * $psize.','. $psize);
			if (!empty($list)) {
			 $posttotal= pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('sns_post') . " WHERE  rid = '{$id}' AND is_del=1  ");
			 $pager = pagination($posttotal, $pindex, $psize);
	
				}
	
		}
		//上面回收站的贴子查询完毕
		//开始进行回贴管理
		elseif($admin==2){
			$list=pdo_fetchall("SELECT * FROM ".tablename('sns_reply')." WHERE   rid = {$rid} AND is_del=1  ORDER BY ctime ASC LIMIT ".($pindex - 1) * $psize.','. $psize);
		if (!empty($reply)) {
			$total= pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('sns_reply') . " WHERE rid = '{$rid}' AND is_del=1");	
			 $pager = pagination($total, $pindex, $psize);
		}
			}
		else{
			//下面查询黑名单用户
			$list= pdo_fetchall("SELECT * FROM ".tablename('fans')." WHERE isblacklist = 1 AND weid='{$user['weid']}' ORDER BY createtime DESC LIMIT ".($pindex - 1) * $psize.",{$psize}");
			if (!empty($list)) {
			 $total= pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('fans') . " WHERE  isblacklist = 1 AND weid='{$user['weid']}' ");
			 $pager = pagination($total, $pindex, $psize);
	
				}
	
			}
		//print_r($list);	
		include $this->template('bzadmin');
		
		}
	
	public function doUserinfo($uid) {//用户信息查询
		global $_GPC, $_W;
		$fromuser =$uid? $uid :authcode(base64_decode($_GPC['from_user']), 'DECODE');
		
		if (empty($fromuser)) {
			$wechat = pdo_fetch("SELECT * FROM ".tablename('wechats')." WHERE weid = '{$_GPC['weid']}'");
			include $this->template('login');
			exit;
			//exit('非法参数');
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
			$result = array('status' => -1, 'message' => '未找到该用户数据');
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
		'credit1'=>$user['credit1'],
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
		}
		else
		{return $result;
			}

	}
	public function doPmem($uid,$pmem=0,$id=0) {//用户权限相关
		global $_GPC, $_W;
		$fromuser =$uid? $uid :authcode(base64_decode($_GPC['from_user']), 'DECODE');
		if (empty($fromuser)) {
			exit('非法参数');
		}
		//是否有用户权限判断
		$user=$this->doUserinfo($fromuser);
		if(!$user){
			$result = array('status' => -1, 'message' => '未找到用户数据');
		}
		//吧主权限判断
		if($pmem==1){
			$sns = pdo_fetch("SELECT id, bzuid FROM ".tablename('sns')." WHERE rid = '{$id}' LIMIT 1");
			$bazu=explode(',',$sns['bzuid']);
			if(in_array($user['id'],$bazu)){
				$result = array('status' => 1, 'message' => '恭喜您成为吧主');	
			}
			else{
			$result = array('status' => -1, 'message' =>  '您不是管理，无权限');
			}
		}
		//普通用户权限判断
		else{
			if($user['isshow']==1){
			$result['message'] = '您还未通过审核，请联系管理员微信号wangnrg,帮助您审核通过!';
			$result['status'] =-1;
			}
			elseif($user['isblacklist']==1){
			$result['message'] = '您已经被拉进小黑屋了!不能发表!';
			$result['status'] =-1;
			}
			else
			{$result['status']=1;
			$result['message'] = '成功';
			}
		
		}
		if(empty($uid)){
		message($result, '', 'ajax');
		}
		else
		{return $result;
			}

	}
	//如果检测不到用户ＯＩＤ传递过来显示此页面
	public function doMobilelogin(){
		global $_W ,$_GPC;
		
		//$_SESSION['user'] =  $_GPC['from_user'];
		$uid= intval($_GPC['uid']);
		$user=$this->doUserinfo($uid);
		
		$zt = pdo_fetch("SELECT lastvisit FROM ".tablename('fans_status')." WHERE uid ='{$uid}'  LIMIT 1");	
		$url= $this->createMobileUrl('list',array('id'=>$rid,'from_user' => $replyid));
		$wechat = pdo_fetch("SELECT * FROM ".tablename('wechats')." WHERE weid = '{$user['weid']}'");
		
			//exit('<div style="margin:80px auto; txt-align:center "><a href="http://weixin.qq.com/r/-XVIUFbEMQtOrRIz9yDv"><img src=http://800826.duapp.com/qrcode_1.jpg /></a><br /> 扫描上图,或者点击上图,关注<b>[广安吧]</b>后,发送:[微吧]参与讨论</div>');
			include $this->template('login');
		
			
			
		}
	
	//数据处理ＡＪＡＸ和后台管理操作
	//发贴
	public function doMobilepost() {
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
			message($fans,$this->createMobileUrl('list',array('id'=>$rid,'from_user' => $replyid)), 'ajax');
			exit;
			}
		if($pmem['status']=='-1'){
			//message($result, create_url('index/module', array('name' => 'sns', 'do' => 'list', 'id' => intval($_GPC['id']), 'from_user' => $_GPC['from_user'])), 'ajax');}
			message($pmem, $this->createMobileUrl('list',array('id'=>$rid,'from_user' => $replyid)), 'ajax');
			exit;
			}
			
		$data = array(
			'rid' =>$_GPC['id'],
			'post_uid' =>$fromuser,
			'title' =>ihtmlspecialchars($_GPC['title']),
			'content' =>htmlspecialchars_decode(emotion($_GPC['content'])),
			'post_time' =>TIMESTAMP,
			'weid' =>$fans['weid'],
			
		);
		if (empty($data['title'])) {
				$result['status'] = '-1'; 
				$result['message'] = '请填写标题！';
				
				message($result, $this->createMobileUrl('list',array('id'=>$rid,'from_user' => $replyid)), 'ajax');
				exit;
				//message($result, $this->createMobileUrl('list',array('id'=>$rid,'from_user' => $replyid)), 'ajax');
			}
		if (empty($data['content'])||strlen($data['content'])<20) {
				$result['status'] = '-1'; 
				$result['message'] = '请填写您内容！且内容不能小于20个字符!';
				
				message($result, $this->createMobileUrl('list',array('id'=>$rid,'from_user' => $replyid)), 'ajax');
				//message($result, $this->createMobileUrl('list',array('id'=>$rid,'from_user' => $replyid)), 'ajax');
				exit;
			}
		
		$data2['credit1'] = $fans['credit1']+$sns['postcredit'];
		
		pdo_insert('sns_post', $data);
		pdo_update('fans', $data2, array('from_user' => $fromuser));
		
		$result = array('status' => 0, 'message' => '发表成功!');
		message($result, $this->createMobileUrl('list',array('id'=>$rid,'from_user' => $replyid)), 'ajax');
		//message($result, $this->createMobileUrl('list',array('id'=>$rid,'from_user' => $replyid)), 'ajax');
		}
	//回贴操作
	public function doMobilehuifu() {
		global $_GPC, $_W;
		//print_r('test');exit;
		//评论用户ID
		//$replyid =authcode(base64_decode($_GPC['uid']), 'DECODE');
		$replyid=$_GPC['uid']?authcode(base64_decode($_GPC['uid']), 'DECODE'):$_W['fans']['from_user'];
		if (empty($replyid)) {
			$result['status'] = '-1';
			$result['message'] = '未取到发表用户ＩＤ';
			message($result, $this->createMobileUrl('list',array('id'=>$rid,'from_user' => $replyid)), 'ajax');
			exit;
			}
			
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
		
			
			if (empty($postid)||empty($id)||empty($rid)){
				$result['status'] = '-1';
				$result['message'] = '非法参数';
				message($result,$this->createMobileUrl('list',array('id'=>$rid,'from_user' => $replyid)), 'ajax');
				exit;
				}
			
		
		if (empty($sns)) {
			$result['status'] = '-1';
			$result['message'] = '找不到模块配置参数';
			message($result, $this->createMobileUrl('list',array('id'=>$rid,'from_user' => $replyid)), 'ajax');
			exit;
		}
		if (empty($post)) {
			$result['status'] = '-1';
			$result['message'] = '未找到你要回复的贴子，请确实贴子ID是否正确';
			message($result, $this->createMobileUrl('list',array('id'=>$rid,'from_user' => $replyid)), 'ajax');
			exit;
		}
		
		
		
		$fans =$this->doUserinfo($replyid);
		$result=$this->doPmem($replyid);
		if($fans['status']=='-1'){
			//message($result, create_url('index/module', array('name' => 'sns', 'do' => 'list', 'id' => intval($_GPC['id']), 'from_user' => $_GPC['from_user'])), 'ajax');}
			message($fans, $this->createMobileUrl('list',array('id'=>$rid,'from_user' => $replyid)), 'ajax');
			exit;
			}
		
		if($result['status']=='-1'){
			message($result, $this->createMobileUrl('list',array('id'=>$rid,'from_user' => $replyid)), 'ajax');
			exit;
			}
		if($post['lock']==1){
			$result['status'] = '-1';
			$result['message'] = '此贴已经被锁定。请返回!';
			message($result, $this->createMobileUrl('list',array('id'=>$rid,'from_user' => $replyid)), 'ajax');
			exit;
		}
		
		$data = array(
			'post_id' =>$_GPC['id'],//贴子ID
			'post_uid' =>$postid,//贴子作者ID
			'to_reply_id' =>$_GPC['to_reply_id']?$_GPC['to_reply_id']:0,//回复评论的ID，这个是作楼中楼即评论中的评论
			'uid' =>$replyid,//评论者用户ID
			'to_uid' =>$to_uid?$to_uid:0,//被回复评论作者ID
			'content' =>htmlspecialchars_decode(emotion($_GPC['content'])),
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
		$data2['credit1'] = $fans['credit1']+$sns['replycredit'];
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
		
		echo json_encode($result); 
		//message($result, $this->createMobileUrl('view',array('id'=>$rid, 'rid' => intval($_GPC['rid']),'from_user' => $replyid)), 'ajax');
		exit;
	}
	//版主管理
	
	public function doMobilebzadmin() {
		global $_W,$_GPC;
		$weid=intval($_GPC['weid']);
		$id = intval($_GPC['id']);
		$rid = intval($_GPC['rid']);
		$fromuser = authcode(base64_decode($_GPC['from_user']), 'DECODE');
		
		$blackid=intval($_GPC['blackid']);
		//几项管理操作提交命令
		$admin=intval($_GPC['admin']);
		if (empty($rid)) {

			$url= $this->createMobileUrl('login',array('id'=>intval($_GPC['id'])));
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
		//取后台用户权限做后台管理操作
		//$membermodules = pdo_fetch("SELECT b.mid, b.name, b.issystem FROM ".tablename('modules')." AS b LEFT JOIN ".tablename('members_modules')." AS a ON a.mid = b.mid WHERE a.uid = :uid AND b.name <> 'sns'  LIMIT 1", array(':uid' => $_W['uid']));
			if($_W['isfounder']||$_W['uid']){
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
			$ding=pdo_update('sns_post', array('top' => intval($_GPC['top'])), array('post_id' => $id));
			if($ding){
				$result['status']=1;
				$result['message'] = '置顶操作成功';
			}else{
				$result['status']=-1;
				$result['message'] = '置顶操作失败';
				}
			
		}
		if($admin==2)
		{
			$user=$this->doUserinfo($post['post_uid']);
			pdo_update('sns_post', array('digest' => intval($_GPC['digest'])), array('post_id' => $id));
			$result['status']=1;
			if(intval($_GPC['digest'])==1)
			{
				$jh=pdo_update('fans', array('credit1' => $user['credit1']+$sns['jhcredit']), array('from_user' => $post['post_uid']));
				if($jh){
					$result['message'] = '加精华操作成功';
					}else{
						$result['message'] = '加精华操作失败';
						}
			}
			else{
				$deljh=pdo_update('fans', array('credit1' => $user['credit1']-$sns['jhcredit']), array('from_user' => $post['post_uid']));
			if($deljh){
					$result['message'] = '取消精华操作成功';
					}else{
						$result['message'] = '取消精华操作失败';
						}
			
				}
			
			
		}
		if($admin==3)
		{
			
			pdo_update('sns_post', array('is_del' => intval($_GPC['is_del'])), array('post_id' => $id));
			pdo_update('sns_reply', array('is_del' => intval($_GPC['is_del'])), array('post_id' => $id));
			
			$result['status']=1;
			if(intval($_GPC['is_del'])){
				$user=$this->doUserinfo($post['post_uid']);
				$del=pdo_update('fans', array('credit1' => $user['credit1']-$sns['postcredit']), array('from_user' => $post['post_uid']));
				if($del){
				$result['message'] = '删贴操作成功';
				}else{$result['message'] = '删贴操作失败';}
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
			pdo_update('fans', array('credit1' => $user['credit1']-$sns['replycredit']), array('from_user' => $reply['uid']));
			$result['status']=1;
			$result['message'] = '删/恢复评论操作成功';
			
		}
		message($result,'', 'ajax');	
	}
	//后台管理操作开始
	public function doWebManage() {
		global $_GPC, $_W;
		checklogin();
		
		$id = intval($_GPC['id']);
		$reply=intval($_GPC['reply']);
		//检测用户对该模块是否有权限
		//$membermodules = pdo_fetch("SELECT b.mid, b.name, b.issystem FROM ".tablename('modules')." AS b LEFT JOIN ".tablename('members_modules')." AS a ON a.mid = b.mid WHERE a.uid = :uid AND b.name <> 'sns'  LIMIT 1", array(':uid' => $_W['uid']));
		//开始取版主ID，主要用于加精置顶等操作。
		$sns = pdo_fetch("SELECT id, bzuid FROM ".tablename('sns')." WHERE rid = '{$id}' LIMIT 1");
      	if($_W['isfounder']||$_W['uid']){
			//print_r($membermodules);exit;
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
				message('评论恢复成功！', create_url('site/module/manage', array('state' => '', 'name' => 'sns', 'id' => $id, 'reply'=>1, 'page' => $_GPC['page'])));
			}
			else{
			pdo_update('sns_post', array('is_del' => 0), " post_id  IN  ('".implode("','", $_GPC['select'])."')");
			message('贴子恢复成功！', create_url('site/module/manage', array('state' => '', 'name' => 'sns', 'id' => $id, 'page' => $_GPC['page'])));
			}
		}
		if (checksubmit('black') && !empty($_GPC['select'])) {
			if($reply)
			{pdo_update('sns_reply', array('is_del' => 1), " reply_id  IN  ('".implode("','", $_GPC['select'])."')");
			message('评论拉黑成功！', create_url('site/module/manage', array('state' => '', 'name' => 'sns', 'id' => $id, 'reply'=>1, 'page' => $_GPC['page'])));
			}
			else{
			pdo_update('sns_post', array('is_del' => 1), " post_id  IN  ('".implode("','", $_GPC['select'])."')");
			
			message('贴子拉黑成功！', create_url('site/module/manage', array('state' => '', 'name' => 'sns', 'id' => $id, 'page' => $_GPC['page'])));
			}
		}
		if (checksubmit('delete') && !empty($_GPC['select'])) {
			if($reply)
			{pdo_delete('sns_reply', " reply_id  IN  ('".implode("','", $_GPC['select'])."')");
			message('评论彻底删除成功！', create_url('site/module/manage', array('state' => '', 'name' => 'sns', 'id' => $id, 'reply'=>1, 'page' => $_GPC['page'])));
			}
			else{
			pdo_delete('sns_post', " post_id  IN  ('".implode("','", $_GPC['select'])."')");
			
			message('贴子彻底删除成功！', create_url('site/module/manage', array('state' => '', 'name' => 'sns', 'id' => $id, 'page' => $_GPC['page'])));
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
		//print_r('test');exit;
		include $this->template('manage');
	}

	//贴吧分类列表
	public function doWebsnslist(){
		global $_GPC, $_W;
		checklogin();
		$id = intval($_GPC['id']);
		if (checksubmit('delete') && !empty($_GPC['select'])) {
			pdo_delete($this->tablename, " id  IN  ('".implode("','", $_GPC['select'])."')");
			message('删除成功！', create_url('site/module/snslist', array('state' => '', 'name' => 'sns')));
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
		include $this->template('snslist');
	}
 
}