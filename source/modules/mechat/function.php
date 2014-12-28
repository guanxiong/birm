<?php
/**
 * 美洽客服接入模块定义
 *
 * @author Yokit QQ:182860914
 * @url http://bbs.we7.cc/forum.php?mod=forumdisplay&fid=36&filter=typeid&typeid=1
 */
defined('IN_IA') or exit('Access Denied');

function account_mechat_token($acmechat) {
	
	if(is_array($acmechat['access_token']) && !empty($acmechat['access_token']['token']) && !empty($acmechat['access_token']['expire']) && $acmechat['access_token']['expire'] > TIMESTAMP) {
		return $acmechat['access_token']['token'];
	} else {
		if(empty($acmechat['weid'])) {
			message('参数错误.');
		}
		if (empty($acmechat['appid']) || empty($acmechat['appsecret'])) {
			message('请填写开发者的appid及appsecret, (需要你的号码为美洽开发者)！', create_url('index/module/display', array('name' => 'mechat')), 'error');
		}
		$url = "http://open.mobilechat.im/cgi-bin/token?grant_type=client_credential&appid={$acmechat['appid']}&secret={$acmechat['appsecret']}";
		$content = ihttp_get($url);
		if(empty($content)) {
			message('获取美洽开发者授权失败, 请稍后重试！');
		}
		$token = @json_decode($content['content'], true);
		
		/*$insert = array('cdata' => json_encode($token));
		pdo_insert('test', $insert);*/
		
		if(empty($token) || !is_array($token)) {
			message('获取美洽开发者授权失败, 请稍后重试！ 公众平台返回原始数据为: <br />' . $token);
		}
		if(empty($token['access_token']) || empty($token['expire_in'])) {
			message("解析美洽开发者授权失败. <br />URL：{$url}<br />错误代码为: {$token['errcode']} <br />错误信息为: {$token['errmsg']}");
		}
		$record = array();
		$record['token'] = $token['access_token'];
		$record['expire'] = TIMESTAMP + $token['expires_in'];
		$row = array();
		$row['access_token'] = iserializer($record);
		pdo_update('mechat', $row, array('weid' => $acmechat['weid']));
		return $record['token'];
	}

}