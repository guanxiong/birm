<?php
/**
 * [WNS] Copyright (c) 2013 BIRM.CO
 * $sn: origins/source/class/yixin.account.class.php : v 45ae3bc23184 : 2014/06/26 03:37:43 : RenChao $
 */
defined('IN_IA') or exit('Access Denied');

class YiXinAccount extends WeAccount {
	private $account = null;
	
	public function __construct($uniAccount) {
		$this->account = $uniAccount;
		if(empty($this->account)) {
			trigger_error('error uniAccount id, can not construct ' . __CLASS__, E_USER_WARNING);
		}
		$this->account['access_token'] = iunserializer($this->account['access_token']);
	}

	public function fetchAccountInfo() {
		return $this->account;
	}
	
	public function queryAvailableMessages() {
		$messages = array('text', 'image', 'voice', 'video', 'location', 'subscribe', 'unsubscribe');
		if(!empty($this->account['key']) && !empty($this->account['secret'])) {
			$messages[] = 'click';
			if(!empty($this->account['key'])) {
				$messages[] = 'qr';
				$messages[] = 'trace';
				$messages[] = 'enter';
			}
		}
		return $messages;
	}
	
	public function queryAvailablePackets() {
		$packets = array('text', 'music', 'news');
		if(!empty($this->account['key']) && !empty($this->account['secret'])) {
			if(!empty($this->account['key'])) {
				$packets[] = 'image';
				$packets[] = 'voice';
				$packets[] = 'video';
				$packets[] = 'link';
				$packets[] = 'card';
			}
		}
		return $packets;
	}	

	public function isMenuSupported() {
		return !empty($this->account['key']) && !empty($this->account['secret']);
	}

	private function menuResponseParse($content) {
		if(!is_array($content)) {
			return error(-1, '接口调用失败，请重试！' . (is_string($content) ? "易信公众平台返回元数据: {$content}" : ''));
		}
		$dat = $content['content'];
		$result = @json_decode($dat, true);
		if(is_array($result) && $result['errcode'] == '0') {
			return true;
		} else {
			if(is_array($result)) {
				return error(-1, "易信公众平台返回接口错误. \n错误代码为: {$result['errcode']} \n错误信息为: {$result['errmsg']} \n错误描述为: " . $this->error_code($result['errcode']));
			} else {
				return error(-1, '易信公众平台未知错误');
			}
		}
	}

	public function menuCreate($menu) {
		$dat = json_encode($menu);
		$dat = urldecode($dat);
		$token = $this->fetch_token();
		$url = "https://api.yixin.im/cgi-bin/menu/create?access_token={$token}";
		$content = ihttp_post($url, $dat);
		return $this->menuResponseParse($content);
	}

	public function menuDelete() {
		$token = $this->fetch_token();
		$url = "https://api.yixin.im/cgi-bin/menu/delete?access_token={$token}";
		$content = ihttp_get($url);
		return $this->menuResponseParse($content);
	}

	public function menuModify($menu) {
		return $this->menuCreate($menu);
	}

	public function menuQuery() {
		$token = $this->fetch_token();
		$url = "https://api.yixin.im/cgi-bin/menu/get?access_token={$token}";
		$content = ihttp_get($url);
		if(is_error($content)) {
			return error(-1, '接口调用失败，请重试！' . (is_string($content) ? "易信公众平台返回元数据: {$content}" : ''));
		}
		$dat = $content['content'];
		$result = @json_decode($dat, true);
		if(is_array($result)) {
			return $result;
		} else {
			if(is_array($result)) {
				return error(-1, "易信公众平台返回接口错误. \n错误代码为: {$result['errcode']} \n错误信息为: {$result['errmsg']} \n错误描述为: " . $this->error_code($result['errcode']));
			} else {
				return error(-1, '易信公众平台未知错误');
			}
		}
	}

	private function error_code($code) {
		$errors = array(
				'-1' => '系统繁忙',
				'0' => '请求成功',
				'40001' => '验证失败',
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
				'40029' => 'access_token超时',
				'40030' => 'refresh_token超时',
				'40035' => '不合法的参数',
				'40038' => '不合法的请求格式',
				'40039' => '不合法的URL长度',
				'40050' => '不合法的分组id',
				'40051' => '分组名字不合法',
				'40052' => '已有重复的分组名称',
				'41001' => '缺少access_token参数',
				'41002' => '缺少appid参数',
				'41003' => '缺少refresh_token参数',
				'41004' => '缺少secret参数',
				'41005' => '缺少多媒体文件数据',
				'41006' => '缺少media_id参数',
				'41007' => '缺少子菜单数据',
				'41009' => '缺少openid',
				'42001' => 'access_token超时',
				'43001' => '需要GET请求',
				'43002' => '需要POST请求',
				'43003' => '需要HTTPS请求',
				'43004' => '需要接收者关注',
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
				'45016' => '系统分组，不允许修改',
				'45017' => '分组名字过长',
				'45018' => '分组数量超过上限',
				'45019' => '分组名称为空',
				'45020' => '不存在的分组',
				'45021' => '群发消息量超过限制',
				'46001' => '不存在媒体数据',
				'46002' => '不存在的菜单版本',
				'46003' => '不存在的菜单数据',
				'46004' => '不存在的用户',
				'47001' => '解析JSON/XML内容错误',
				'48001' => 'api功能未授权',
				'50001' => '该用户没有关注此公众号',
				'50002' => '该用户不允许公众号获取他的好友关系',
				'50003' => '识别条形码失败',
				'50004' => '识别条形码失败',
				'50005' => '无法识别该条形码',
				'50006' => '无法打开所提供的图片链接地址',
				'50007' => 'next_openid未关注此公众号',
				'50008' => '生成二维码失败（错误的请求内容）',
				'50009' => '生成二维码失败',
				'50010' => '不合法的ticket',
				'51001' => '不合法的mobile',
				'52001' => '无权访问该动态或该动态不存在',
				'52029' => '发送的内容涉及敏感词',
				'60001' => '请求参数中缺少key',
				'60002' => '公众号不存在',
				'60003' => '公众号状态错误',
				'60004' => '用户在黑名单中' 
		);
		$code = strval($code);
		if($code == '40001') {
			$rec = array();
			$rec['access_token'] = '';
			pdo_update('wechats', $rec, array('weid' => $this->account['weid']));
			return '微信公众平台授权异常, 系统已修复这个错误, 请刷新页面重试.';
		}
		if($errors[$code]) {
			return $errors[$code];
		} else {
			return '未知错误';
		}
	}

	private function fetch_token() {
		if(is_array($this->account['access_token']) && !empty($this->account['access_token']['token']) && !empty($this->account['access_token']['expire']) && $this->account['access_token']['expire'] > TIMESTAMP) {
			return $this->account['access_token']['token'];
		} else {
			if (empty($this->account['key']) || empty($this->account['secret'])) {
				message('请填写公众号的appid及appsecret, (需要你的号码为易信服务号)！', create_url('account/post', array('id' => $this->account['weid'])), 'error');
			}
			$url = "https://api.yixin.im/cgi-bin/token?grant_type=client_credential&appid={$this->account['key']}&secret={$this->account['secret']}";
			$content = ihttp_get($url);
			if(is_error($content)) {
				message('获取微信公众号授权失败, 请稍后重试！错误详情: ' . $content['message']);
			}
			$token = @json_decode($content['content'], true);
			if(empty($token) || !is_array($token) || empty($token['access_token']) || empty($token['expires_in'])) {
				message('获取微信公众号授权失败, 请稍后重试！ 公众平台返回原始数据为: <br />' . $content['meta']);
			}
			$record = array();
			$record['token'] = $token['access_token'];
			$record['expire'] = TIMESTAMP + $token['expires_in'];
			$row = array();
			$row['access_token'] = iserializer($record);
			pdo_update('wechats', $row, array('weid' => $this->account['weid']));
			return $record['token'];
		}
	}
}
