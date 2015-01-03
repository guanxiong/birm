<?php
/**
 * [WNS] Copyright (c) 2013 BIRM.CO
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
	$uid = pdo_fetchcolumn("SELECT uid FROM ".tablename('wechats')." WHERE weid = :weid", array(':weid' => $_W['weid']));
	$founder = explode(',', $_W['config']['setting']['founder']);
	
	if (!empty($founder) && in_array($uid, $founder)) {
		$membermodules = pdo_fetchall("SELECT * FROM ".tablename('modules') . " ORDER BY issystem DESC, mid ASC", array(), 'mid');
	} else {
		$groupid = pdo_fetchcolumn("SELECT groupid FROM ".tablename('members')." WHERE uid = :uid", array(':uid' => $uid));
		//获取用户组权限
		$groupsmodules = pdo_fetch("SELECT modules FROM ".tablename('members_group')." WHERE id = :id", array(':id' => $groupid));
		if (!empty($groupsmodules['modules'])) {
			$groupsmodules['modules'] = iunserializer($groupsmodules['modules']);
		}
		$membermodules = pdo_fetchall("SELECT b.* FROM ".tablename('modules')." AS b LEFT JOIN ".tablename('members_permission')." AS a ON a.resourceid = b.mid WHERE a.uid = :uid AND b.name <> '' AND a.type = 1 OR b.issystem = 1 ".(!empty($groupsmodules['modules']) ? ' OR b.mid IN ('.implode(',', $groupsmodules['modules']).')' : '')." ORDER BY issystem DESC, mid ASC", array(':uid' => $uid), 'mid');
	}
	if (!empty($membermodules)) {
		$mymodules = pdo_fetchall("SELECT mid, enabled, settings FROM ".tablename('wechats_modules')." WHERE weid = '{$_W['weid']}' AND mid IN (".implode(",", array_keys($membermodules)).") ORDER BY enabled DESC, mid ASC", array(), 'mid');
	}
	if (!empty($mymodules)) {
		foreach ($mymodules as $mid => $row){
			if ($enabledOnly) {
				if ($row['enabled'] == 0 || empty($membermodules[$mid])) {
					unset($membermodules[$mid]);
					continue;
				}
			}
			$membermodules[$mid]['enabled'] = $row['enabled'];
			if(!empty($row['settings'])) {
				$membermodules[$mid]['config'] = iunserializer($row['settings']);
			}
		}
	}
	foreach ($membermodules as $mid => &$row) {
		if ($row['issystem'] == 1) {
			$row['enabled'] = 1;
		} elseif (!isset($row['enabled'])) {
			$row['enabled'] = 1;
		}
		if(empty($row['config'])) {
			$row['config'] = array();
		}
	}
	return $membermodules;
}

function account_template() {
	global $_W;
	$groupid = pdo_fetchcolumn("SELECT groupid FROM ".tablename('members')." WHERE uid = :uid", array(':uid' => $_W['account']['uid']));
	if ($_W['isfounder'] || $groupid == '-1') {
		$templates = pdo_fetchall("SELECT * FROM ".tablename('site_templates') . " ORDER BY id ASC");
	} elseif (empty($groupid)) {
		$templates = pdo_fetchall("SELECT * FROM ".tablename('site_templates') . " WHERE name = 'default'");
	} else {
		$wechatgroup = pdo_fetch("SELECT modules, templates FROM ".tablename('members_group')." WHERE id = '{$groupid}'");
		if (!empty($wechatgroup['templates'])) {
			$wechatgroup['templates'] = unserialize($wechatgroup['templates']);
		}
		$templates = pdo_fetchall("SELECT * FROM ".tablename('site_templates') . " WHERE name = 'default' ".(!empty($wechatgroup['templates']) ? " OR id IN (".implode(',', $wechatgroup['templates']).")" : '')." ORDER BY id ASC");
	}
	return $templates;
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
		$response = ihttp_request(WEIXIN_ROOT . '/home?t=home/index&lang=zh_CN&token='.$auth['token'], '', array('CURLOPT_REFERER' => 'https://mp.weixin.qq.com/', 'CURLOPT_COOKIE' => $auth['cookie']));
		if (is_error($response)) {
			return false;
		}
		if (strexists($response['content'], '登录超时')) {
			cache_delete('wxauth:'.$username.':token');
			cache_delete('wxauth:'.$username.':cookie');
		}
		return true;
	}
	$loginurl = WEIXIN_ROOT . '/cgi-bin/login?lang=zh_CN';	
	$post = array(
		'username' => $username,
		'pwd' => $password,
		'imgcode' => $imgcode,
		'f' => 'json',	
	);
	$response = ihttp_request($loginurl, $post, array('CURLOPT_REFERER' => 'https://mp.weixin.qq.com/'));
	if (is_error($response)) {
		return false;
	}
	$data = json_decode($response['content'], true);
	if ($data['base_resp']['ret'] == 0) {
		preg_match('/token=([0-9]+)/', $data['redirect_url'], $match);
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

function account_weixin_basic($username) {
	global $wechat;
	$response = account_weixin_http($username, WEIXIN_ROOT . '/cgi-bin/settingpage?t=setting/index&action=index&lang=zh_CN');
	if (is_error($response)) {
		return array();
	}
	$info = array();
	preg_match('/fakeid=([0-9]+)/', $response['content'], $match);
	$fakeid = $match[1];
	$image = account_weixin_http($username, WEIXIN_ROOT . '/misc/getheadimg?fakeid='.$fakeid);
	if (!is_error($image) && !empty($image['content'])) {
		$info['headimg'] = $image['content'];
	}
	$image = account_weixin_http($username, WEIXIN_ROOT . '/misc/getqrcode?fakeid='.$fakeid.'&style=1&action=download');
	if (!is_error($image) && !empty($image['content'])) {
		$info['qrcode'] = $image['content'];
	}
	preg_match('/(gh_[a-z0-9A-Z]+)/', $response['meta'], $match);
	$info['original'] = $match[1];
	preg_match('/名称([\s\S]+?)<\/li>/', $response['content'], $match);
	$info['name'] = trim(strip_tags($match[1]));
	preg_match('/微信号([\s\S]+?)<\/li>/', $response['content'], $match);
	$info['account'] = trim(strip_tags($match[1]));
	preg_match('/功能介绍([\s\S]+?)meta_content\">([\s\S]+?)<\/li>/', $response['content'], $match);
	$info['signature'] = trim(strip_tags($match[2]));
	if (strexists($response['content'], '服务号') || strexists($response['content'], '微信认证')) {
		$authcontent = account_weixin_http($username, WEIXIN_ROOT . '/advanced/advanced?action=dev&t=advanced/dev&lang=zh_CN');
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

function account_weixin_interface($username, $hash = '', $token = '') {
	$response = account_weixin_http($username, WEIXIN_ROOT . '/misc/skeyform?form=advancedswitchform&lang=zh_CN', array('flag' => '1', 'type' => '2'));
	if (is_error($response)) {
		return $response;
	}
	$response = json_decode($response['content'], true);
	if (!empty($response['base_resp']) && !empty($response['base_resp']['ret'])) {
		return error($response['base_resp']['ret'], $response['base_resp']['err_msg']);
	}
	$response = account_weixin_http($username, WEIXIN_ROOT . '/advanced/callbackprofile?t=ajax-response&lang=zh_CN', array('url' => $GLOBALS['_W']['siteroot'] . 'api.php?hash=' . $hash, 'callback_token' => $token));
	if (is_error($response)) {
		return $response;
	}
	$response = json_decode($response['content'], true);
	if (!empty($response['ret'])) {
		return error($response['ret'], $response['msg']);
	}
	return true;
}

function account_weixin_http($username, $url, $post = '') {
	global $_W;
	if (empty($_W['cache']['wxauth'][$username])) {
		cache_load('wxauth:'.$username.':');
	}
	$auth = $_W['cache']['wxauth'][$username];
	return ihttp_request($url . '&token=' . $auth['token'], $post, array('CURLOPT_COOKIE' => $auth['cookie'], 'CURLOPT_REFERER' => WEIXIN_ROOT . '/advanced/advanced?action=edit&t=advanced/edit&token='.$auth['token'],));
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

function account_yixin_token($account) {
	if(is_array($account['access_token']) && !empty($account['access_token']['token']) && !empty($account['access_token']['expire']) && $account['access_token']['expire'] > TIMESTAMP) {
		return $account['access_token']['token'];
	} else {
		if(empty($account['weid'])) {
			message('参数错误.');
		}
		if (empty($account['key']) || empty($account['secret'])) {
			message('请填写公众号的appid及appsecret, (需要你的号码为易信公众号)！', create_url('account/post', array('id' => $account['weid'])), 'error');
		}
		$url = "https://api.yixin.im/cgi-bin/token?grant_type=client_credential&appid={$account['key']}&secret={$account['secret']}";
		$content = ihttp_get($url);
		if(empty($content)) {
			message('获取易信公众号授权失败, 请稍后重试！');
		}
		$token = @json_decode($content['content'], true);
		if(empty($token) || !is_array($token)) {
			message('获取易信公众号授权失败, 请稍后重试！ 公众平台返回原始数据为: <br />' . $token);
		}
		if(empty($token['access_token']) || empty($token['expires_in'])) {
			message('解析易信公众号授权失败, 请稍后重试！');
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
	if (is_error($response)) {
		return false;
	}
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
	if (is_error($response)) {
		return array();
	}
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
