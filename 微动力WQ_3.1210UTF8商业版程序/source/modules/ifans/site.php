<?php
/**
 * 粉丝模块模块微站定义
 *
 * @author 珊瑚海
 * @url http://www.vfanm.com/
 */
defined('IN_IA') or exit('Access Denied');

class IfansModuleSite extends WeModuleSite {
	public $gateway = array();
	public $atype;

	public function __construct(){
		global $_W;
		$this->atype = '';
		if($_W['account']['type'] == '1') {
			$this->atype = 'weixin';
			$this->gateway['get'] = "https://api.weixin.qq.com/cgi-bin/groups/get?access_token=%s";
			$this->gateway['create'] = "https://api.weixin.qq.com/cgi-bin/groups/create?access_token=%s";
			$this->gateway['update'] = "https://api.weixin.qq.com/cgi-bin/groups/update?access_token=%s";
			$this->gateway['getlist'] = "https://api.weixin.qq.com/cgi-bin/user/get?access_token=%s";
			$this->gateway['getlist2'] = "https://api.weixin.qq.com/cgi-bin/user/get?access_token=%s&next_openid=%s";
			$this->gateway['getuserinfo'] = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=%s&openid=%s&lang=zh_CN";
			$this->gateway['getgroupid'] = "https://api.weixin.qq.com/cgi-bin/groups/getid?access_token=%s";
			$this->gateway['send'] = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=%s";
			$this->gateway['file'] = "http://file.api.weixin.qq.com/cgi-bin/media/upload?access_token=%s&type=%s";
			$this->gateway['getfile'] = "http://file.api.weixin.qq.com/cgi-bin/media/get?access_token=%s&media_id=%s";
		}
		if($_W['account']['type'] == '2') {
			$this->atype = 'yixin';
			$this->gateway['get'] = "https://api.yixin.im/cgi-bin/groups/get?access_token=%s";
			$this->gateway['create'] = "https://api.yixin.im/cgi-bin/groups/create?access_token=%s";
			$this->gateway['update'] = "https://api.yixin.im/cgi-bin/groups/update?access_token=%s";
			$this->gateway['getlist'] = "https://api.yixin.im/cgi-bin/user/get?access_token=%s";
			$this->gateway['getlist2'] = "https://api.yixin.im/cgi-bin/user/get?access_token=%s&next_openid=%s";
			$this->gateway['getuserinfo'] = "https://api.yixin.im/cgi-bin/user/info?access_token=%s&openid=%s&lang=zh_CN";
			$this->gateway['getgroupid'] = "https://api.yixin.im/cgi-bin/groups/getid?access_token=%s";
			$this->gateway['send'] = "https://api.yixin.im/cgi-bin/message/custom/send?access_token=%s";
			$this->gateway['file'] = "http://file.api.yixin.im/cgi-bin/media/upload?access_token=%s&type=%s";
			$this->gateway['getfile'] = "http://file.api.yixin.im/cgi-bin/media/get?access_token=%s&media_id=%s";
		}
		$account_token = "account_{$this->atype}_token";
		$this->token = $account_token($_W['account']);
	}

	public function doWebGetFans() {
		//这个操作被定义用来呈现 管理中心导航菜单
		global $_W,$_GPC;
		$settings = $this->module['config'];
		if (checksubmit('submit')) {
			$url = sprintf($this->gateway['getlist'], $this->token);
			$content = ihttp_get($url);
			$dat = $content['content'];
			$result = @json_decode($dat, true);
			if ($result['total']) {
				$gi = ceil($result['total']/10000);
				$fi = '0';
				$next = '';
				do {
					$con = ihttp_get(sprintf($this->gateway['getlist2'], $this->token,$next));
					$result2 = @json_decode($con['content'],true);
					$openids = $result2['data']['openid'];
					$next = $result2['next_openid'];
					$fi = $fi+1;
					foreach ($openids as $key => $vo) {
						$insert = array(
							'weid' => $_W['weid'],
							'from_user' => $vo,
							'createtime'=> time(),
							);
						if (pdo_fetch("SELECT * FROM ".tablename('fans')." WHERE `from_user` = '{$vo}'")) {
							pdo_update('fans', $insert, array('from_user' => $vo));
						}else{
							pdo_insert('fans',$insert);
						}
					}
				}
				while ($fi<$gi);
				message("关注者列表同步成功！<br />本次同步粉丝".$result['total']."个<br />接下来同步粉丝具体信息...<br />请勿关闭本窗口！！！",$this->createWebUrl('Getallfansinfo'), 'success');
			}else{
				message("公众平台返回接口错误. <br />错误代码为：{$result['errcode']}<br />错误信息为: {$result['errmsg']} <br />错误描述为: " . $this->account_code($result['errcode']));
			}
		}
		include $this->template('getfans');
	}
	public function doWebGetallfansinfo(){
		global $_GPC, $_W;
		$pindex = max(1, intval($_GPC['page']));
		$nindex = $pindex+1;
		$psize = 10;
		$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('fans')." WHERE follow = 1 AND weid = '{$_W['weid']}' ");
		$list = pdo_fetchall("SELECT id,from_user FROM ".tablename('fans')." WHERE `weid` = '{$_W['weid']}' AND `follow` = 1 ORDER BY id ASC LIMIT ".($pindex - 1) * $psize.",{$psize}");
		foreach ($list as $v) {
			$content = ihttp_get(sprintf($this->gateway['getuserinfo'], $this->token,$v['from_user']));
			$dat2 = $content['content'];
			$re = @json_decode($dat2, true);
			$data00['openid'] = $v['from_user'];
			$content3 = ihttp_post(sprintf($this->gateway['getgroupid'], $this->token),json_encode($data00));
			$groupid = @json_decode($content3['content'],true);
			$i++;
			if ($re['subscribe'] == '1') {
				$insert = array(
					'weid' => $_W['weid'],
					'from_user' => $re['openid'],
					'nickname' => $re['nickname'],
					'gender' => $re['sex'],
					'groupid' => $groupid['groupid'],
					'residecity'=> $re['city'],
					'resideprovince' => $re['province'],
					'nationality' => $re['country'],
					'avatar' => $re['headimgurl'],
					'createtime'=> $re['subscribe_time']
					);
				if (pdo_fetch("SELECT * FROM ".tablename('fans')." WHERE `from_user` = '{$re['openid']}'")) {
					pdo_update('fans', $insert, array('from_user' => $re['openid']));
				}else{
					pdo_insert('fans',$insert);
				}
			}
		}
		$num = $_GPC['num']+$i;
		if ($num < $total) {
			message("第".$pindex."页粉丝信息同步成功，当前同步成功".$num."个<br />总数".$total."<br />接下来同步第".$nindex."页，请勿关闭此页面",create_url('site/module/Getallfansinfo', array('name' => 'ifans','page' => $nindex,'num' => $num)),'success');
		}else{
			message("粉丝信息同步成功！",$this->createWebUrl('groups'), 'success');
		}
	}
	public function doWebGroups() {
		//这个操作被定义用来呈现 管理中心导航菜单
		global $_W,$_GPC;
		$url = sprintf($this->gateway['get'], $this->token);
		$content = ihttp_get($url);
		if(empty($content)) {
			message('接口调用失败，请重试！');
		}
		$dat = $content['content'];
		$result = @json_decode($dat, true);
		if($result['errcode']) {
			message("公众平台返回接口错误. <br />错误代码为：{$result['errcode']}<br />错误信息为: {$result['errmsg']} <br />错误描述为: " . account_weixin_code($result['errcode']));
		} else {
			$list = $result['groups'];
			$row = array();
			$row['groups'] = iserializer($list);
			pdo_update('wechats', $row, array('weid' => $_W['weid']));
			include $this->template('list');
			//print_r($list);
		}
	}
	public function doWebGroupdisplay(){
		global $_GPC, $_W;
		if (checksubmit('deleteselects') && !empty($_GPC['select'])) {
			pdo_delete('fans', " id  IN  ('".implode("','", $_GPC['select'])."')");
			message('删除成功！', 'refresh');
		}
		$groupid = $_GPC['groupid'];
		$pindex = max(1, intval($_GPC['page']));
		$psize = 10;
		$list = pdo_fetchall("SELECT * FROM ".tablename('fans')." WHERE `weid` = '{$_W['weid']}' AND `groupid` = ".$groupid." ORDER BY `id` DESC LIMIT ".($pindex - 1) * $psize.",{$psize}");
		if (!empty($list)) {
			$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('fans')." WHERE `weid` = '{$_W['weid']}' AND `groupid` = ".$groupid);
			$pager = pagination($total, $pindex, $psize);			
			unset($row);
		}
		include $this->template('groupdisplay');
	}
	public function doWebFans() {
		//这个操作被定义用来呈现 管理中心导航菜单
		global $_GPC, $_W;
		$pindex = max(1, intval($_GPC['page']));
		$psize = 10;
		$list = pdo_fetchall("SELECT * FROM ".tablename('fans')." WHERE `weid` = '{$_W['weid']}' ORDER BY `id` DESC LIMIT ".($pindex - 1) * $psize.",{$psize}");
		if (!empty($list)) {
			$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('fans')." WHERE `weid` = '{$_W['weid']}'");
			$pager = pagination($total, $pindex, $psize);			
			unset($row);
		}
		include $this->template('groupdisplay');
	}
	public function doWebAllSend() {
		//这个操作被定义用来呈现 管理中心导航菜单
		global $_W,$_GPC;
		$groups = iunserializer($_W['account']['groups']);
		if (checksubmit('submit')) {
			//print_r($_GPC);exit;
			if ($_GPC['gid'] == '-1') {
				message('请选择一个分组再进行发送');
			}
			if ($_GPC['sendmode'] == '1') {
				//即时发送模式
				$pagesize = $_GPC['pagesize'];
				$groupid = $_GPC['gid'];
				$msgtype = $_GPC['msgtype'];
				$data['message'] = $_GPC['message'];
				$data['pics'] = $_GPC['pics'];
				$data['voice'] = $_GPC['voice'];
				$data['pic'] = $_GPC['pic'];
				$data['video'] = $_GPC['video'];
				$data['music'] = $_GPC['music'];
				message("操作成功，即将开始执行群发！请勿关闭本窗口",$this->createWebUrl('GroupSend',array('pagesize'=>$pagesize,'groupid'=>$groupid,'msgtype'=>$msgtype,'data'=>$data,'page'=>'1')),"success");
			}else{
				if ($_GPC['msgtype'] == '1') {
					$data['send'] = $_GPC['message'];
				}elseif ($_GPC['msgtype'] == '2') {
					$data['send'] = $_GPC['pics'];
				}elseif ($_GPC['msgtype'] == '3') {
					$data['send'] = $_GPC['voice'];
				}elseif ($_GPC['msgtype'] == '4') {
					$data['send'] = $_GPC['pic'];
				}elseif ($_GPC['msgtype'] == '5') {
					$data['send'] = $_GPC['video'];
				}elseif ($_GPC['msgtype'] == '6') {
					$data['send'] = $_GPC['music'];
				}else{
					$data['send'] = NULL;
				}
				$list = pdo_fetchall("SELECT from_user FROM ".tablename('fans')." WHERE weid = '{$_W['weid']}' AND groupid='{$_GPC['gid']}' ORDER BY id DESC");
				//print_r($list);
				foreach ($list as $key => $value) {
					$insert = array(
						'from_user' => $value['from_user'],
						'msgtype' => $_GPC['msgtype'],
						'msg' => $data['send'],
						'status' => '0',
						'time' => time(),
						'send_time' => '0'
						);
					pdo_insert('ifans_groupsend',$insert);
				}
				message('操作成功！',$this->createWebUrl('AllSend'),"success");
			}
		}
		include $this->template('allsend');
	}

	public function doWebsend(){
		global $_W, $_GPC;
		if (checksubmit('submit')) {
			$data['touser'] = $_GPC['openid'];
			$data['msgtype'] = 'text';
			$data['text']['content'] = urlencode($_GPC['content']);
			$dat = json_encode($data);
			$dat = urldecode($dat);
			$url = sprintf($this->gateway['send'], $this->token);
			$content = ihttp_post($url, $dat);
			$dat = $content['content'];
			$result = @json_decode($dat, true);
			if ($result['errcode'] == '0') {
				message('发送消息成功！', referer(), 'success');
			}else{
				message("公众平台返回接口错误. <br />错误代码为：{$result['errcode']}<br />错误信息为: {$result['errmsg']} <br />错误描述为: " . account_weixin_code($result['errcode']));
			}
		}
		$openid = $_GPC['openid'];
		$list = pdo_fetch("SELECT * FROM ".tablename('fans')." WHERE `from_user` = '{$openid}'");
		include $this->template('send');
	}

	public function doWebAddgroup(){
		global $_W,$_GPC;
		if (checksubmit('submit')) {
			//print_r($_GPC);
			$name = $_GPC['groupname'];
			$group['name'] = urlencode($name);
			$data['group'] = $group;
			$dat = json_encode($data);
			$dat = urldecode($dat);
			$url = sprintf($this->gateway['create'], $this->token);
			$content = ihttp_post($url, $dat);
			$dat = $content['content'];
			$result = @json_decode($dat, true);
			if ($result['group']) {
				$group = $result['group'];
				message('创建分组成功，分组名为:'.$group['name'].'.分组id为:'.$group['id'], $this->createWebUrl('groups'), 'success');
			}else{
				message("公众平台返回接口错误. <br />错误代码为：{$result['errcode']}<br />错误信息为: {$result['errmsg']} <br />错误描述为: " . $this->account_code($result['errcode']));
			}
		}
		include $this->template('addgroup');
	}

	public function doWebEditgroup(){
		global $_W,$_GPC;
		if (checksubmit('submit')) {
			$group['name'] = urlencode($_GPC['groupname']);
			$group['id'] = intval($_GPC['id']);
			$data['group'] = $group;
			$dat = json_encode($data);
			$dat = urldecode($dat);
			$url = sprintf($this->gateway['update'], $this->token);
			$content = ihttp_post($url, $dat);
			$dat = $content['content'];
			$result = @json_decode($dat, true);
			if ($result['errcode'] == '0') {
				message('修改分组名称成功', $this->createWebUrl('groups'), 'success');
			}else{
				message("公众平台返回接口错误. <br />错误代码为：{$result['errcode']}<br />错误信息为: {$result['errmsg']} <br />错误描述为: " . $this->account_code($result['errcode']));
			}
		}
		$row['id'] = intval($_GPC['id']);
		$row['name'] = $_GPC['groupname'];
		include $this->template('editgroup');
	}

	public function doWebMedia(){
		global $_W,$_GPC;
		if (!empty($_W['ispost'])) {
			$ret = $_GPC['ret'] == 'true';
			$set = @json_decode(base64_decode($_GPC['dat']), true);
			$ree = pdo_fetch("SELECT * FROM ".tablename('medias')." WHERE id = '{$set}'");
			$data['mediatype'] = $ree['mediatype'];
			$data['mediaurl'] = $ree['mediaurl'];
			$file = '@'.IA_ROOT.'/resource/attachment/'.$data['mediaurl'];
			$url = sprintf($this->gateway['file'], $this->token , $data['mediatype']);
			$content = $this -> https_request($url, array('media' => $file));
			$con = json_decode($content,true);
			if ($con['media_id']) {
				$data['mediatype'] = $con['type'];
				$data['media_id'] = $con['media_id'];
				$data['createtime'] = $con['created_at'];
			}
			if (pdo_update('medias',array('media_id' => $data['media_id'],'createtime' => $data['createtime']),array('id'=> $set))) {
				exit('success');
			}
		}
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;
		$condition = '';
		if (!empty($_GPC['keyword'])) {
			$condition .= " AND title LIKE '%{$_GPC['keyword']}%'";
		}
		if (!empty($_GPC['mediatype'])) {
			$condition .= " AND mediatype = '{$_GPC['mediatype']}'";
		}
		$list = pdo_fetchall("SELECT * FROM ".tablename('medias')." WHERE weid = '{$_W['weid']}' $condition ORDER BY id DESC LIMIT ".($pindex - 1) * $psize.','.$psize);
		foreach ($list as &$value) {
			if (time()>=$value['createtime'] + 259200) {
				$value['status'] = '0';
			}else{
				$value['status'] = '1';
			}
		}
		$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('medias') . " WHERE weid = '{$_W['weid']}' $condition");
		$pager = pagination($total, $pindex, $psize);
		include $this->template('media');
	}

	public function doWebMediaPost(){
		global $_W,$_GPC;
		if (checksubmit('submit')) {
			$data['title'] = $_GPC['title'];
			$data['mediatype'] = $_GPC['mediatype'];
			$data['content'] = $_GPC['content'];
			if (!empty($_FILES['file']['tmp_name'])) {
				file_delete($_GPC['file_old']);
				$upload = file_upload($_FILES['file']);
				if (is_error($upload)) {
					message($upload['message'], '', 'error');
				}
				$data['mediaurl'] = $upload['path'];
			}
			$file = '@'.IA_ROOT.'/resource/attachment/'.$data['mediaurl'];
			$url = sprintf($this->gateway['file'], $this->token , $data['mediatype']);
			$content = $this -> https_request($url, array('media' => $file));
			$con = json_decode($content,true);
			if ($con['media_id']) {
				$data['mediatype'] = $con['type'];
				$data['media_id'] = $con['media_id'];
				$data['createtime'] = $con['created_at'];
			}else{
				message('上传错误，错误代码：'.$con['errcode'].'<br />错误描述为：'.$con['errmsg']);
			}
			if(pdo_insert('medias',$data)){
				message('素材上传成功！',$this->createWebUrl('media'),'success');
			}else{
				message('上传错误,数据入库错误');
			}
		}
		include $this->template('mediapost');
	}

	public function doWebGroupSend(){
		global $_W,$_GPC;
		$msgtype = $_GPC['msgtype'];
		$data = $_GPC['data'];
		$pindex = max(1, intval($_GPC['page']));
		$psize = $_GPC['pagesize'];
		$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('fans') . " WHERE weid = '{$_W['weid']}' AND groupid='{$_GPC['groupid']}'");
		$list = pdo_fetchall("SELECT from_user FROM ".tablename('fans')." WHERE weid = '{$_W['weid']}' AND groupid='{$_GPC['groupid']}' ORDER BY id DESC LIMIT ".($pindex - 1) * $psize.','.$psize);
		//print_r($list);
		$a='0';$b='0';$c='0';$d='0';
		foreach ($list as $key => $value) {
			if ($_GPC['msgtype'] == '1') {
				$status = $this->sendText($value['from_user'],$data['message'],'text');
			}elseif($_GPC['msgtype'] == '2'){
				$status = $this->sendPics($value['from_user'],$data['pics']);
			}elseif ($_GPC['msgtype'] == '3') {
				$status = $this->sendText($value['from_user'],$data['voice'],'voice');
			}elseif ($_GPC['msgtype'] == '4') {
				$status = $this->sendText($value['from_user'],$data['pic'],'image');
			}else{
				$status = '-1';
			}
			if ($status == '43004') {
				$a++;//未关注
			}elseif($status == '45015'){
				$b++;//48小时未联系
			}elseif($status == '0'){
				$c++;//成功
			}else{
				$d++;//未知状态导致的发送不成功
			}
		}
		$error = $psize-$c;
		if ($psize*$_GPC['page']<$total) {
			message("执行群发成功<br />发送成功：{$c}个<br />发送失败：{$error}个<br />已取消关注：{$a}个<br />48小时内未联系：{$b}个",$this->createWebUrl('GroupSend',array('pagesize'=>$psize,'groupid'=>$_GPC['groupid'],'msgtype'=>$_GPC['msgtype'],'data'=>$_GPC['data'],'page'=>$_GPC['page']+1)),"success");
		}else{
			message("执行群发成功",$this->createWebUrl('AllSend'),"success");
		}
	}

	public function doWebTime_send(){
		global $_GPC, $_W;
		//checklogin();
		echo "string";
	}

	public function sendText($from_user,$message,$type='text'){
		$data['touser'] = $from_user;
		if($type=='image'){
			$data['msgtype'] = 'image';
			$data['image']['media_id'] = $message;
		}elseif($type=='voice'){
			$data['msgtype'] = 'voice';
			$data['voice']['media_id'] = $message;
		}else{
			$data['msgtype'] = 'text';
			$data['text']['content'] = urlencode($message);
		}
		$dat = json_encode($data);
		$dat = urldecode($dat);
		$url = sprintf($this->gateway['send'], $this->token);
		$content = ihttp_post($url, $dat);
		$dat = $content['content'];
		$result = @json_decode($dat, true);
		if ($result['errcode'] == '0') {
			return $result['errcode'];
		}else{
			return $result['errcode'];
		}
	}

	public function sendPics($from_user,$message){
		global $_W;
		$data['touser'] = $from_user;
		$data['msgtype'] = 'news';
		$ids = str_replace('，', ',', str_replace(' ','',$message));
		$contents = pdo_fetchall("SELECT id,title,description,thumb FROM ".tablename('article')." WHERE id IN ({$ids})");
		foreach ($contents as $key => $value) {
			$articles[] = array(
				'title' => urlencode($value['title']),
				'description' =>  cutstr($value['description'], 300),
				'picurl' => empty($value['thumb'])?'':$_W['attachurl'].$value['thumb'],
				'url' => $_W['siteroot'].create_url('mobile/module/detail', array('name' => 'site', 'id' => $value['id'], 'weid' => $_W['weid'])),
				);				
			$i++;
			if ($i>8) {
				break;
			}
		}
		$data['news']['articles'] = $articles;
		$dat = json_encode($data);
		$dat = urldecode($dat);
		$url = sprintf($this->gateway['send'], $this->token);
		$content = ihttp_post($url, $dat);
		$dat = $content['content'];
		$result = @json_decode($dat, true);
		if ($result['errcode'] == '0') {
			return $result['errcode'];
		}else{
			return $result['errcode'];
		}
	}

	public function https_request($url, $data = null){
		$curl = curl_init();
    	curl_setopt($curl, CURLOPT_URL, $url);
    	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
    	if (!empty($data)){
    	    curl_setopt($curl, CURLOPT_POST, 1);
    	    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    	}
    	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    	$output = curl_exec($curl);
    	curl_close($curl);
    	return $output;
	}
}