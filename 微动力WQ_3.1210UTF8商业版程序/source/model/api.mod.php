<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

/**
 * 检查签名
 */
function wx_checksign($signature, $timestamp, $nonce, $token) {
	$signkey = array($token, $timestamp, $nonce);
	sort($signkey);
	$signString = implode($signkey);
	$signString = sha1($signString);

	if( $signString == $signature ){
		return true;
	}else{
		return false;
	}
}

/**
 * 分析请求数据
 * @param string $message 接口提交的请求数据
 * @return array 请求数据结构
 *  - from 请求用户
 *  - to 目标用户
 *  - time 请求时间
 *  - type 请求类型, 目前包括 text: 普通文本请求, hello: 加关注, location: 位置信息
 *	  - text 类型
 *		  - content 请求内容
 *	  - hello 类型
 *		  无附加内容
 *	  - location 类型
 *		  - x 纬度
 *		  - y 经度
 *		  - scale 缩放精度
 *		  - label 位置信息描述
 */
function wx_parse($message) {
	$packet = array();
	if (!empty($message)){		 
		$obj = simplexml_load_string($message, 'SimpleXMLElement', LIBXML_NOCDATA);
		if($obj instanceof SimpleXMLElement) {
			$packet['from'] = strval($obj->FromUserName);
			$packet['to'] = strval($obj->ToUserName);
			$packet['time'] = strval($obj->CreateTime);
			$packet['type'] = strval($obj->MsgType);
			if($packet['type'] == 'text') {
				$packet['content'] = strval($obj->Content);
				if($packet['content'] == 'Hello2BizUser') {
					$packet['type'] = 'hello';
					unset($packet['content']);
				}
			}
			if($packet['type'] == 'location') {
				$packet['x'] = strval($obj->Location_X);
				$packet['y'] = strval($obj->Location_Y);
				$packet['scale'] = strval($obj->Scale);
				$packet['label'] = strval($obj->Label);
			}
		}
	}
	return $packet;
}

/**
 * 按照响应内容组装响应数据
 * @param array $struct 响应内容
 * @return string
 */
function wx_response($struct) {
	$response = "<xml><ToUserName><![CDATA[{$struct['to']}]]></ToUserName><FromUserName><![CDATA[{$struct['from']}]]></FromUserName><CreateTime>{$struct['time']}</CreateTime><MsgType><![CDATA[{$struct['type']}]]></MsgType><Content><![CDATA[{$struct['content']}]]></Content>";
	if($struct['type'] == 'news' && is_array($struct['items'])) {
		$response .= "<ArticleCount>" . count($struct['items']) . "</ArticleCount><Articles>";
		foreach($struct['items'] as $s) {
			$response .= "<item><Title><![CDATA[{$s['title']}]]></Title><Description><![CDATA[{$s['description']}]]></Description><PicUrl><![CDATA[{$s['picurl']}]]></PicUrl><Url><![CDATA[{$s['url']}]]></Url></item>";
		}
		$response .= "</Articles>";
	}
	$response .= "<FuncFlag>0</FuncFlag></xml>";
	return $response;
}
