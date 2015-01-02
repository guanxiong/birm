<?php
/**
 * [WeEngine System] 更多模块请浏览：bbs.we7.cc
 */
defined('IN_IA') or exit('Access Denied');

define('WEIXIN_ROOT', 'https://mp.weixin.qq.com');
define('YIXIN_ROOT', 'https://plus.yixin.im');

function account_search($uid = 0, $type = 0) {
	global $_W;
	$uid = empty($uid) ? $_W['uid'] : $uid;
	$condition = empty($type) ? '' : " AND type = '{$type}'";
	$condition .= $_W['isfounder'] ? '' : " AND uid = '{$uid}'";
	$sql = "SELECT * FROM " . tablename('wechats') . " WHERE 1 $condition ORDER BY `weid` DESC";
	return pdo_fetchall($sql, array(), 'weid');
}

function account_module($enabledOnly = true) {
	global $_W;
	
	if (!empty($_W['isfounder']) && $_W['isfounder']) {
		$membermodules = pdo_fetchall("SELECT mid, name, issystem FROM ".tablename('modules') . " ORDER BY issystem DESC, mid ASC", array(), 'mid');
	} else {
		$membermodules = pdo_fetchall("SELECT b.mid, b.name, b.issystem FROM ".tablename('modules')." AS b LEFT JOIN ".tablename('members_modules')." AS a ON a.mid = b.mid WHERE a.uid = :uid AND b.name <> '' OR b.issystem = 1 ORDER BY issystem DESC, mid ASC", array(':uid' => $_W['uid']), 'mid');
	}
	$mymodules = pdo_fetchall("SELECT mid, enabled, displayorder, settings FROM ".tablename('wechats_modules')." WHERE weid = '{$_W['weid']}' AND mid IN (".implode(",", array_keys($membermodules)).") ORDER BY enabled DESC, displayorder ASC, mid ASC", array(), 'mid');
	if (!empty($mymodules)) {
		foreach ($mymodules as $mid => $row){
			if ($enabledOnly) {
				if ($row['enabled'] == 0 || empty($membermodules[$mid])) {
					unset($membermodules[$mid]);
					continue;
				}
			}
			$membermodules[$mid]['enabled'] = $row['enabled'];
			$membermodules[$mid]['displayorder'] = $row['displayorder'];
			if(!empty($row['settings'])) {
				$membermodules[$mid]['config'] = iunserializer($row['settings']);
			}
		}
	}
	foreach ($membermodules as $mid => &$row) {
		if ($row['issystem'] == 1) {
			$row['enabled'] = 1;
			$row['displayorder'] = -1;
		} elseif (!isset($row['enabled'])) {
			$row['enabled'] = 1;
			$row['displayorder'] = 127;
		}
		if(empty($row['config'])) {
			$row['config'] = array();
		}
	}
	return $membermodules;
}

function account_weixin_code($code) {
	$errors = array(
		'-1' => '系统繁忙',
		'0' => '请求成功',
		'40001' => '获取access_token时AppSecret错误，或者access_token无效',
		'40002' => '不合法的凭证类型',
		'40003' => '不合法的OpenID',
		'40004' => '不合法的媒体文件类型',
		'40005' => '不合法的文件类型',
		'40006' => '不合法的文件大小',
		'40007' => '不合法的媒体文件id',
		'40008' => '不合法的消息类型',
		'40009' => '不合法的图片文件大小',
		'40010' => '不合法的语音文件大小',
		'40011' => '不合法的视频文件大小',
		'40012' => '不合法的缩略图文件大小',
		'40013' => '不合法的APPID',
		'40014' => '不合法的access_token',
		'40015' => '不合法的菜单类型',
		'40016' => '不合法的按钮个数',
		'40017' => '不合法的按钮个数',
		'40018' => '不合法的按钮名字长度',
		'40019' => '不合法的按钮KEY长度',
		'40020' => '不合法的按钮URL长度',
		'40021' => '不合法的菜单版本号',
		'40022' => '不合法的子菜单级数',
		'40023' => '不合法的子菜单按钮个数',
		'40024' => '不合法的子菜单按钮类型',
		'40025' => '不合法的子菜单按钮名字长度',
		'40026' => '不合法的子菜单按钮KEY长度',
		'40027' => '不合法的子菜单按钮URL长度',
		'40028' => '不合法的自定义菜单使用用户',
		'40029' => '不合法的oauth_code',
		'40030' => '不合法的refresh_token',
		'40031' => '不合法的openid列表',
		'40032' => '不合法的openid列表长度',
		'40033' => '不合法的请求字符，不能包含\uxxxx格式的字符',
		'40035' => '不合法的参数',
		'40038' => '不合法的请求格式',
		'40039' => '不合法的URL长度',
		'40050' => '不合法的分组id',
		'40051' => '分组名字不合法',
		'41001' => '缺少access_token参数',
		'41002' => '缺少appid参数',
		'41003' => '缺少refresh_token参数',
		'41004' => '缺少secret参数',
		'41005' => '缺少多媒体文件数据',
		'41006' => '缺少media_id参数',
		'41007' => '缺少子菜单数据',
		'41008' => '缺少oauth code',
		'41009' => '缺少openid',
		'42001' => 'access_token超时',
		'42002' => 'refresh_token超时',
		'42003' => 'oauth_code超时',
		'43001' => '需要GET请求',
		'43002' => '需要POST请求',
		'43003' => '需要HTTPS请求',
		'43004' => '需要接收者关注',
		'43005' => '需要好友关系',
		'44001' => '多媒体文件为空',
		'44002' => 'POST的数据包为空',
		'44003' => '图文消息内容为空',
		'44004' => '文本消息内容为空',
		'45001' => '多媒体文件大小超过限制',
		'45002' => '消息内容超过限制',
		'45003' => '标题字段超过限制',
		'45004' => '描述字段超过限制',
		'45005' => '链接字段超过限制',
		'45006' => '图片链接字段超过限制',
		'45007' => '语音播放时间超过限制',
		'45008' => '图文消息超过限制',
		'45009' => '接口调用超过限制',
		'45010' => '创建菜单个数超过限制',
		'45015' => '回复时间超过限制',
		'45016' => '系统分组，不允许修改',
		'45017' => '分组名字过长',
		'45018' => '分组数量超过上限',
		'46001' => '不存在媒体数据',
		'46002' => '不存在的菜单版本',
		'46003' => '不存在的菜单数据',
		'46004' => '不存在的用户',
		'47001' => '解析JSON/XML内容错误',
		'48001' => 'api功能未授权',
		'50001' => '用户未授权该api',
	);
	return $errors[strval($code)];
}

function account_weixin_token($account) {
	if(is_array($account['access_token']) && !empty($account['access_token']['token']) && !empty($account['access_token']['expire']) && $account['access_token']['expire'] > TIMESTAMP) {
		return $account['access_token']['token'];
	} else {
		if(empty($account['weid'])) {
			message('参数错误.');
		}
		if (empty($account['key']) || empty($account['secret'])) {
			message('请填写公众号的appid及appsecret, (需要你的号码为微信服务号)！', create_url('account/post', array('id' => $account['weid'])), 'error');
		}
		$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$account['key']}&secret={$account['secret']}";
		$content = ihttp_get($url);
		if(empty($content)) {
			message('获取微信公众号授权失败, 请稍后重试！');
		}
		$token = @json_decode($content['content'], true);
		if(empty($token) || !is_array($token)) {
			message('获取微信公众号授权失败, 请稍后重试！ 公众平台返回原始数据为: <br />' . $token);
		}
		if(empty($token['access_token']) || empty($token['expires_in'])) {
			message('解析微信公众号授权失败, 请稍后重试！');
		}
		$record = array();
		$record['token'] = $token['access_token'];
		$record['expire'] = TIMESTAMP + $token['expires_in'];
		$row = array();
		$row['access_token'] = iserializer($record);
		pdo_update('wechats', $row, array('weid' => $account['weid']));
		return $record['token'];
	}
}

function account_weixin_login($username = '', $password = '', $imgcode = '') {
	global $_W;
	if (empty($username) || empty($password)) {
		$username = $_W['account']['username'];
		$password = $_W['account']['password'];
	}
	$auth = cache_load('wxauth:'.$username.':');
	if (!empty($auth)) {
		$response = ihttp_request(WEIXIN_ROOT . '/cgi-bin/indexpage?t=wxm-index&lang=zh_CN', '', array('CURLOPT_NOBODY' => 1, 'CURLOPT_COOKIE' => $auth['cookie']));
		if (strpos($response['headers']['Location'], 'indexpage') !== FALSE || strexists($response['content'], 'logout?t')) {
			preg_match('/token=([0-9]+)/', $response['headers']['Location'], $match);
			cache_write('wxauth:'.$username.':token', $match[1]);
			return true;
		}
	}
	$loginurl = WEIXIN_ROOT . '/cgi-bin/login?lang=zh_CN';	
	$post = array(
		'username' => $username,
		'pwd' => $password,
		'imgcode' => $imgcode,
		'f' => 'json',	
	);
	$response = ihttp_request($loginurl, $post, array('CURLOPT_REFERER' => 'https://mp.weixin.qq.com/cgi-bin/loginpage?t=wxm2-login&lang=zh_CN'));
	$data = json_decode($response['content'], true);
	if ($data['ErrCode'] == 0) {
		preg_match('/token=([0-9]+)/', $data['ErrMsg'], $match);
		cache_write('wxauth:'.$username.':token', $match[1]);
		cache_write('wxauth:'.$username.':cookie', implode('; ', $response['headers']['Set-Cookie']));
	} else {
		switch ($data['ErrCode']) {
			case "-1":
				$msg = "系统错误，请稍候再试。";
				break;
			case "-2":
				$msg = "微信公众帐号或密码错误。";
				break;
			case "-3":
				$msg = "微信公众帐号密码错误，请重新输入。";
				break;
			case "-4":
				$msg = "不存在该微信公众帐户。";
				break;
			case "-5":
				$msg = "您的微信公众号目前处于访问受限状态。";
				break;
			case "-6":
				$msg = "登录受限制，需要输入验证码，稍后再试！";
				break;
			case "-7":
				$msg = "此微信公众号已绑定私人微信号，不可用于公众平台登录。";
				break;
			case "-8":
				$msg = "微信公众帐号登录邮箱已存在。";
				break;
			case "-200":
				$msg = "因您的微信公众号频繁提交虚假资料，该帐号被拒绝登录。";
				break;
			case "-94":
				$msg = "请使用微信公众帐号邮箱登陆。";
				break;
			case "10":
				$msg = "该公众会议号已经过期，无法再登录使用。";
				break;
			default:
				$msg = "未知的返回。";
		}
		message($msg, referer(), 'error');
		return false;
	}
	return true;
}

function account_weixin_basic() {
	global $wechat;
	$response = account_weixin_http($wechat['username'], WEIXIN_ROOT . '/cgi-bin/settingpage?t=setting/index&action=index&lang=zh_CN');
	$info = array();
	preg_match('/fakeid=([0-9]+)/', $response['content'], $match);
	$fakeid = $match[1];
	$image = account_weixin_http($wechat['username'], WEIXIN_ROOT . '/cgi-bin/getheadimg?fakeid='.$fakeid);
	file_write('headimg_'.$wechat['weid'].'.jpg', $image['content']);
	$image = account_weixin_http($wechat['username'], WEIXIN_ROOT . '/cgi-bin/getqrcode?fakeid='.$fakeid.'&style=1&action=download');
	file_write('qrcode_'.$wechat['weid'].'.jpg', $image['content']);
	preg_match('/(gh_[a-z0-9A-Z]+)/', $response['meta'], $match);
	$info['original'] = $match[1];
	preg_match_all("/meta_content\"\>([\s\S]+?)<\/li>/", $response['content'], $match);
	$info['name'] = trim(strip_tags($match[1][1]));
	$info['account'] = trim(strip_tags($match[1][4]));
	$info['signature'] = trim(strip_tags($match[1][8]));
	if (strexists($match[1][5], '服务号')) {
		$authcontent = account_weixin_http($wechat['username'], WEIXIN_ROOT . '/cgi-bin/advanced?action=dev&t=advanced/dev&lang=zh_CN');
		preg_match_all("/value\:\"(.*?)\"/", $authcontent['content'], $match);
		$info['key'] = $match[1][2];
		$info['secret'] = $match[1][3];
		unset($match);
	}
	preg_match_all("/(?:country|province|city): '(.*?)'/", $response['content'], $match);
	$info['country'] = trim($match[1][0]);
	$info['province'] = trim($match[1][1]);
	$info['city'] = trim($match[1][2]);
	return $info;
}

function account_txweibo_login($username, $password, $verify = '') {
	$cookie = cache_load("txwall:$username");
	if (!empty($cookie)) {
		$response = ihttp_request('http://t.qq.com', '', array(
			'CURLOPT_COOKIE' => $cookie,
			'CURLOPT_REFERER' => 'http://t.qq.com/',
			"User-Agent" => "Mozilla/5.0 (Windows NT 5.1; rv:13.0) Gecko/20100101 Firefox/13.0",
		));
		if (!strexists($response['content'], '登录框')) {
			return $cookie;
		}
	}
	$loginsign = '';

	$loginui = 'http://ui.ptlogin2.qq.com/cgi-bin/login?appid=46000101&s_url=http%3A%2F%2Ft.qq.com';
	$response = ihttp_request($loginui);
	preg_match('/login_sig:"(.*?)"/', $response['content'], $match);
	$loginsign = $match[1];

	$checkloginurl = 'http://check.ptlogin2.qq.com/check?uin='.$username.'&appid=46000101&r='.TIMESTAMP;
	$response = ihttp_request($checkloginurl);
	$cookie = implode('; ', $response['headers']['Set-Cookie']);
	preg_match_all("/'(.*?)'/", $response['content'], $match);
	list($needVerify, $verify1, $verify2) = $match[1];
	if (!empty($needVerify)) {
		if (empty($verify)) {
			return error(1, '请输入验证码！');
		}
		$verify1 = $verify;
		$cookie .= '; ' . cache_load('txwall:verify');
	}
	$verify2 = pack('H*', str_replace('\x', '', $verify2));
	$temp = md5($password, true);
	$temp = strtoupper(md5($temp . $verify2));
	$password = strtoupper(md5($temp . strtoupper($verify1)));
	$loginurl = "http://ptlogin2.qq.com/login?u={$username}&p={$password}&verifycode={$verify1}&login_sig={$loginsign}&low_login_enable=1&low_login_hour=720&aid=46000101&u1=http%3A%2F%2Ft.qq.com&ptredirect=1&h=1&from_ui=1&dumy=&fp=loginerroralert&g=1&t=1&dummy=&daid=6&";
	$response = ihttp_request($loginurl, '', array(
		'CURLOPT_COOKIE' => $cookie,
		'CURLOPT_REFERER' => 'http://t.qq.com/',
		"User-Agent" => "Mozilla/5.0 (Windows NT 5.1; rv:13.0) Gecko/20100101 Firefox/13.0",
	));
	$info = explode("'", $response['content']);
	if ($info[1] != 0) {
		return error('1', $info[9]);
	}
	$response = ihttp_request($info[5]);
	$cookie = implode('; ', $response['headers']['Set-Cookie']);
	cache_write("txwall:$username", $cookie);
	return $cookie;
}

function account_yixin_login($username = '', $password = '', $imgcode = '') {
	$auth = cache_load('yxauth:'.$username.':');
	if (!empty($auth)) {
		$response = ihttp_request(YIXIN_ROOT . '/index', '', array('CURLOPT_COOKIE' => $auth['cookie']));
		if ($response['code'] == '200') {
			return $auth['cookie'];
		}
	}
	$loginurl = YIXIN_ROOT . '/rest/login';
	$post = array(
		'account' => $username,
		'password' => $password,
		'captcha' => $imgcode,
		'loginType' => 'YiXinUserId',
	);
	$response = ihttp_request($loginurl, $post, array(
		'CURLOPT_REFERER' => 'https://plus.yixin.im/',
	));
	$data = json_decode($response['content'], true);
	if ($data['code'] == '200') {
		cache_write('yxauth:'.$username.':cookie', implode('; ', $response['headers']['Set-Cookie']));
		return implode('; ', $response['headers']['Set-Cookie']);
	} else {
		message($data['message'], referer(), 'error');
		return false;
	}
}

function account_yixin_basic($username) {
	global $wechat;
	$auth = cache_load('yxauth:'.$username.':');
	$response = ihttp_request(YIXIN_ROOT . '/set', '', array('CURLOPT_COOKIE' => $auth['cookie']));
	$info = array();
	preg_match('/icon\:\"(.*?)\"/', $response['content'], $match);
	$image = ihttp_request($match[1]);
	file_write('headimg_'.$wechat['weid'].'.jpg', $image['content']);
	preg_match('/qrCodeMix\:\"(.*?)\"/', $response['content'], $match);
	$image = ihttp_request($match[1]);
	file_write('qrcode_'.$wechat['weid'].'.jpg', $image['content']);
	preg_match('/signature\:\"(.*?)\"/', $response['content'], $match);
	$info['signature'] = $match[1];
	preg_match('/帐号名称<\/div>(.*?)<\/div>/', $response['content'], $match);
	$info['username'] = strip_tags($match[1]);
	return $info;
}

function account_weixin_saveuserinfo($createtime,$input,$fromusername) {	//聊天中采集资料
	if ($input=="更新") {	//用户主动更新资料时自动放弃，交给你api处理，避免重复采集两次
		return false;
	}
	global $_W;
	$uinfo = pdo_fetch("SELECT `id`,`fakeidtrue`,`fakeid`,`username` FROM ".tablename('fans')." WHERE `from_user` = '{$fromusername}' AND `weid` = '{$_W['weid']}'");
	//每次都去查数据库看是否需要开启采集，目前没想到其它更好的方案
	if ($uinfo['fakeid'] && $uinfo['fakeidtrue']<2) {	//采集到了，并且锁定了或者没有重复标记
		if ($uinfo['username']) {
			return true;
		} elseif ($wxinfo = account_weixin_getuserinfo($uinfo['fakeid'])) {	//之前只采集到了fakeid，采集到了资料
			$userinfo = array('weid' => $_W['weid'], 'to_user' => $_W['account']['original'], 'follow' => 1, );
			$wxinfo['updatetime'] = $createtime;
			$userinfo += $wxinfo;
			pdo_update('fans', $userinfo, array('from_user' => $fromusername, 'weid' => $_W['weid']));
			//再判断一次是否有重复吧，也可以不判断
			if (pdo_fetchcolumn("SELECT count(*) FROM `ims_fans` WHERE `fakeid`='{$wxinfo['fakeid']}' AND `weid`='{$_W['weid']}'")==1) {
				return true;
			}
			pdo_query("UPDATE `ims_fans` SET `fakeidtrue`=3 WHERE `fakeid`='{$wxinfo['fakeid']}' AND `weid`='{$_W['weid']}'");
		}
	} else {	//没采集到，或者采集到了，如果有重复也会重新采集，直到没有重复
		if ($fakeid = account_weixin_getfakeid($createtime,$input)) {	//匹配到了fakeid
			$userinfo = array('weid' => $_W['weid'], 'to_user' => $_W['account']['original'], 'follow' => 1, 'fakeidtrue' => 0, );
			$userinfo['fakeid'] = $fakeid;
			$userinfo['updatetime'] = $createtime;
			if ($wxinfo = account_weixin_getuserinfo($fakeid)) {	//采集到了资料
				
				$userinfo += $wxinfo;
			}
			if ($uinfo['id']) {
				pdo_update('fans', $userinfo, array('from_user' => $fromusername, 'weid' => $_W['weid']));
			} else {
				$userinfo['from_user'] = $fromusername; 
				$userinfo['createtime'] = $createtime-10;	//减10秒是为了是为了不干扰真正的首次关注
				pdo_insert('fans', $userinfo);
			}
			//先写入再判断是否有重复，我自认为能提交准确率
			if (pdo_fetchcolumn("SELECT count(*) FROM `ims_fans` WHERE `fakeid`='{$wxinfo['fakeid']}' AND `weid`='{$_W['weid']}'")==1) {
				return true;
			}
			pdo_query("UPDATE `ims_fans` SET `fakeidtrue`=3 WHERE `fakeid`='{$wxinfo['fakeid']}' AND `weid`='{$_W['weid']}'");
		}
	}
	return false;
}

function account_weixin_savesubuserinfo($createtime,$fromusername) {	//首次关注时采集
	global $_W;
	$userinfo = array('weid' => $_W['weid'], 'to_user' => $_W['account']['original'], 'follow' => 1,);
	$uinfo = pdo_fetch("SELECT `id`,`fakeidtrue`,`fakeid` FROM ".tablename('fans')." WHERE `from_user` = '{$fromusername}' AND `weid` = '{$_W['weid']}'");
	if ($uinfo['fakeidtrue']==1 && $uinfo['fakeid']) {	//以前关注过并且被锁定，且fakeid存在，避免锁定空的fakeid（几乎不可能，除非人工修改的时候误操作）
		if ($wxinfo = account_weixin_getuserinfo($uinfo['fakeid'])) {	//采集成功
			$wxinfo['updatetime'] = $createtime;
			$userinfo += $wxinfo;	//合并数组
		}
		pdo_update('fans', $userinfo, array('from_user' => $fromusername, 'weid' => $_W['weid']));	//模拟登录失败，只更新部分（主要是follow=>1）
		return true;
	} else {	//没有锁定，或者锁定空的fakeid都重新采集
		$userinfo['fakeidtrue'] = 0;	//先把fakeidtrue置2，表示首次关注的，无重复则置1
		if ($fakeid = account_weixin_getsubfakeid()) {	//获取到第一个用户
			$userinfo['fakeid'] = $fakeid;
			$userinfo['fakeidtrue'] = 1;	//先把fakeidtrue置1，有重复再置2
			$userinfo['updatetime'] = $createtime;
			if ($wxinfo = account_weixin_getuserinfo($fakeid)) {	//获取到用户资料
				$userinfo += $wxinfo;
			}
		}
		//采集失败，则主要把follow字段置1
		if ($uinfo['id']) {
			pdo_update('fans', $userinfo, array('from_user' => $fromusername, 'weid' => $_W['weid']));
		} else {
			$userinfo['from_user'] = $fromusername; 
			$userinfo['createtime'] = $createtime;
			pdo_insert('fans', $userinfo);
		}
		//下面的是查看前后是否有关注，如果有则把fakeidtrue置2，便于人工审核
		//先写入再判断是否重复的原因是故意拉长时间，增大同时关注时写入数据库的时间差，便于下面的判断，我自认为能提交成功率
		//此情况是采集成功的，如果同时关注的另外一个粉丝采集失败了，此次采集也就可能是采集成了那个失败的，但也被误锁定了。这个问题有待更好的解决方案
		//主要是同时关注时两次采集所花的时候可能大不一样，先关注的，可能会后入库，受很服务器状态，网络等很多因素影响
		if (pdo_fetchcolumn("SELECT count(*) FROM `ims_fans` WHERE `createtime`>$createtime-4 AND `createtime`<$createtime+4 AND `weid`='{$_W['weid']}'")>1) {
			pdo_query("UPDATE `ims_fans` SET `fakeidtrue`=2 WHERE `createtime`>$createtime-4 AND `createtime`<$createtime+4 AND `weid`='{$_W['weid']}'");
		} elseif ($wxinfo['fakeid']) {
			return true;
		}
	}
	return false;
}

function account_weixin_getuserinfo($fakeid) {		//通过fakeid获取其它资料
	if (account_weixin_login()) {
		global $_W;
		$username = $_W['account']['username'];
		if (empty($_W['cache']['wxauth'][$username])) {
			cache_load('wxauth:'.$username.':');
		}
		$auth = $_W['cache']['wxauth'][$username];
	
		$url = WEIXIN_ROOT . '/cgi-bin/getheadimg?token='.$auth['token'].'&fakeid='.$fakeid;
		$image = ihttp_request($url, '', array(
			'CURLOPT_COOKIE' => $auth['cookie'],
			'CURLOPT_PERERER' => WEIXIN_ROOT . '/cgi-bin/contactmanage?t=user/index&pagesize=10&pageidx=0&type=0&groupid=0&token='.$auth['token'].'&lang=zh_CN',
		));
		file_write("image/weixin/avatar/{$fakeid}".".jpg", $image['content']);
	
		$url = WEIXIN_ROOT . '/cgi-bin/getcontactinfo';
		$post = array(
			't' => 'ajax-getcontactinfo',
			'fakeid' => $fakeid,
			'token' => $auth['token'],
			'lang' => 'zh_CN',
		);
		$response = ihttp_request($url, $post, array(
			'CURLOPT_COOKIE' => $auth['cookie'],
			'CURLOPT_REFERER' => WEIXIN_ROOT . '/cgi-bin/contactmanage?t=user/index&pagesize=10&pageidx=0&type=0&groupid=0&token='.$auth['token'].'&lang=zh_CN',
		));
		$result = json_decode($response['content'], 1);
		if ($result['base_resp']['ret'] == 0) {
			$userinfo = array();
			$userinfo['nickname'] = preg_replace('/&(\S)*;/','',$result['contact_info']['nick_name']);
			$userinfo['nickname'] = str_replace('','',$userinfo['nickname']);
			$userinfo['nickname'] = preg_replace('/<span class="emoji emoji(\w*)"><\/span>/','',$userinfo['nickname']);
			$userinfo['nickname'] = empty($userinfo['nickname'])?$result['contact_info']['nick_name']:$userinfo['nickname'];
			$userinfo['avatar'] = "image/weixin/avatar/{$fakeid}".".jpg";
			$userinfo['fakeid'] = $result['contact_info']['fake_id'];
			$userinfo['gender'] = $result['contact_info']['gender'];
			$userinfo['remarkname'] = $result['contact_info']['remark_name'];
			$userinfo['username'] = $result['contact_info']['user_name'];
			$userinfo['bio'] = str_replace('<br/>','',$result['contact_info']['signature']);
			$userinfo['bio'] = preg_replace('/&(\S)*;/','',$userinfo['bio']);
			$userinfo['bio'] = preg_replace('/<span class="emoji emoji(\w)*"><\/span>/','',$userinfo['bio']);
			$userinfo['nationality'] = $result['contact_info']['country'];
			$userinfo['resideprovince'] = $result['contact_info']['province'];
			$userinfo['residecity'] = $result['contact_info']['city'];
			$userinfo['groupid'] = $result['contact_info']['group_id'];
			return $userinfo;
		}
	}
	return false;
}

function account_weixin_getfakeid($createtime,$input) {		//通过输入和时间双重判断匹配fakeid
	if ($latestmsg = account_weixin_getlatestmsg(50)) {
		$matchnum = 0;	//初始化匹配到的消息条数
		foreach ($latestmsg as $msg) {
			if ($msg['date_time'] == $createtime) {
				if ($msg['content'] == $input) {
					$matchmsg = $msg;
					$matchnum++;
					if ($matchnum>1) {	//如果匹配到了多条，也就是两个不同的用户同时输入了同样的内容，则放弃些次采集，提交准确率
						return false;
					}
				}
			}
		}
		if ($matchnum==1) {		//匹配到1条，则返回该消息发送者的fakeid
			return $matchmsg['fakeid'];
		}
	}
	return false;
}

function account_weixin_getsubfakeid() {	//获取用户管理第一个用户
	if (account_weixin_login()) {	//token cookies有效，模拟登录成功
		global $_W;
		$username = $_W['account']['username'];
		if (empty($_W['cache']['wxauth'][$username])) {
			cache_load('wxauth:'.$username.':');
		}
		$auth = $_W['cache']['wxauth'][$username];
		$url = WEIXIN_ROOT . '/cgi-bin/contactmanage?t=user/index&pagesize=10&pageidx=0&type=0&groupid=0&token='.$auth['token'].'&lang=zh_CN';
		$response = ihttp_request($url, '', array(
			'CURLOPT_COOKIE' => $auth['cookie'],
			'CURLOPT_PERERER' => $url,
		));
		if (preg_match_all('/"contacts":(.*)}\).contacts/iUs', $response['content'], $match)) {	//采集到第一页粉丝
			$user = json_decode($match[1][0], true);	//第一个粉丝资料
			return $user[0]['id'];
		}
	}
	return false;
}

function account_weixin_getlatestmsg($count = 20) {		//获取最新一页消息
	if (account_weixin_login()) {	//成功登录
		global $_W;
		$username = $_W['account']['username'];
		if (empty($_W['cache']['wxauth'][$username])) {
			cache_load('wxauth:'.$username.':');
		}
		$auth = $_W['cache']['wxauth'][$username];
		$url = WEIXIN_ROOT . '/cgi-bin/message?t=message/list&count='.$count.'&day=7&token='.$auth['token'].'&lang=zh_CN';
		$response = ihttp_request($url, '', array(
			'CURLOPT_COOKIE' => $auth['cookie'],
			'CURLOPT_PERERER' => $url,
		));
		if (preg_match_all('/"msg_item":(.*)}\).msg_item/iUs', $response['content'], $match)) {		//获取消息成功
			$latestmsg = json_decode($match[1][0], true);
			return $latestmsg;
		}
	}
	return false;
}

function account_weixin_getusermsg($fakeid) {	//通过fakeid获取微信后台用户最近聊天记录
	if (account_weixin_login()) {
		global $_W;
		$username = $_W['account']['username'];
		if (empty($_W['cache']['wxauth'][$username])) {
			cache_load('wxauth:'.$username.':');
		}
		$auth = $_W['cache']['wxauth'][$username];
		$url = WEIXIN_ROOT . "/cgi-bin/singlesendpage?token=".$auth['token']."&lang=zh_CN&tofakeid=$fakeid&f=json&action=sync&lastmsgfromfakeid=$fakeid&lastmsgid=1&createtime=1";
		$response = ihttp_request($url, '', array(
			'CURLOPT_COOKIE' => $auth['cookie'],
			'CURLOPT_PERERER' => WEIXIN_ROOT . "/cgi-bin/singlesendpage?t=message/send&action=index&tofakeid=$fakeid&token=".$auth['token']."&lang=zh_CN",
		));
		$result = json_decode($response['content'], 1);
		return $result;
	}
	return false;
}

function account_weixin_http($username, $url, $post = '') {
	global $_W;
	if (empty($_W['cache']['wxauth'][$username])) {
		cache_load('wxauth:'.$username.':');
	}
	$auth = $_W['cache']['wxauth'][$username];
	return ihttp_request($url . '&token=' . $auth['token'], $post, array('CURLOPT_COOKIE' => $auth['cookie'],'CURLOPT_PERERER' => WEIXIN_ROOT . '/cgi-bin/contactmanage?t=user/index'));
}

function account_weixin_userlist($pindex = 0, $psize = 1, &$total = 0) {
	global $_W;
	$url = WEIXIN_ROOT . '/cgi-bin/contactmanagepage?t=wxm-friend&lang=zh_CN&type=0&keyword=&groupid=0&pagesize='.$psize.'&pageidx='.$pindex;
	$response = account_weixin_http($_W['account']['username'], $url);
	$html = $response['content'];
	preg_match('/PageCount \: \'(\d+)\'/', $html, $match);
	$total = $match[1];
	preg_match_all('/"fakeId" : "([0-9]+?)"/', $html, $match);
	return $match[1];
}

function account_weixin_send($uid, $message = '') {
	if (account_weixin_login()) {
	global $_W;
	$username = $_W['account']['username'];
	if (empty($_W['cache']['wxauth'][$username])) {
		cache_load('wxauth:'.$username.':');
	}
	$auth = $_W['cache']['wxauth'][$username];
	$url = WEIXIN_ROOT . '/cgi-bin/singlesend?t=ajax-response&lang=zh_CN';
	$post = array(
		'ajax' => 1,
		'content' => $message,
		'error' => false,
		'tofakeid' => $uid,
		'token' => $auth['token'],
		'type' => 1,
	);
	$response = ihttp_request($url, $post, array(
		'CURLOPT_COOKIE' => $auth['cookie'],
		'CURLOPT_REFERER' => WEIXIN_ROOT . '/cgi-bin/singlemsgpage?token='.$auth['token'].'&fromfakeid='.$uid.'&msgid=&source=&count=20&t=wxm-singlechat&lang=zh_CN',
	));
	return $response['content'];
}
}

function account_weixin_changegroupid($fakeid, $togroupid = 2, $groupid = 0) {	//通过fakeid移动分组
	if (account_weixin_login()) {
		global $_W;
		$username = $_W['account']['username'];
		if (empty($_W['cache']['wxauth'][$username])) {
			cache_load('wxauth:'.$username.':');
		}
		$auth = $_W['cache']['wxauth'][$username];
		$url = WEIXIN_ROOT . '/cgi-bin/modifycontacts';
		$post = array(
			'contacttype' => $togroupid,
			'tofakeidlist' => $fakeid,
			'token' => $auth['token'],
			'lang' => 'zh_CN',
			'action' => 'modifycontacts',
			't' => 'ajax-putinto-group'
		);
		$response = ihttp_request($url, $post, array(
			'CURLOPT_COOKIE' => $auth['cookie'],
			'CURLOPT_REFERER' => WEIXIN_ROOT . '/cgi-bin/contactmanage?t=user/index&pagesize=10&pageidx=0&type=0&groupid='.$groupid.'&token='.$auth['token'].'&lang=zh_CN',
		));
		$result = json_decode($response['content'], 1);
		return $result;
	}
	return false;
}

function account_weixin_remark($fakeid, $remark, $groupid = 0) {	//备注
	if (account_weixin_login()) {
		global $_W;
		$username = $_W['account']['username'];
		if (empty($_W['cache']['wxauth'][$username])) {
			cache_load('wxauth:'.$username.':');
		}
		$auth = $_W['cache']['wxauth'][$username];
		$url = WEIXIN_ROOT . '/cgi-bin/modifycontacts';
		$post = array(
			'remark' => $remark,
			'tofakeuin' => $fakeid,
			'token' => $auth['token'],
			'lang' => 'zh_CN',
			'action' => 'setremark',
			't' => 'ajax-response'
		);
		$response = ihttp_request($url, $post, array(
			'CURLOPT_COOKIE' => $auth['cookie'],
			'CURLOPT_REFERER' => WEIXIN_ROOT . '/cgi-bin/contactmanage?t=user/index&pagesize=10&pageidx=0&type=0&groupid='.$groupid.'&token='.$auth['token'].'&lang=zh_CN',
		));
		$result = json_decode($response['content'], 1);
		return $result;
	}
	return false;
}

?>