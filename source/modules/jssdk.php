<?php
/**
* 
* @param 微信jssdk类
* Author 微动力
* 
*
*/
defined('IN_IA') or exit('Access Denied');
if (!function_exists('dump')) {
function dump($arr){
	echo '<pre>'.print_r($arr,TRUE).'</pre>';
}
}

abstract class jssdk extends WeModuleSite
{
public static function get_curl($url){
	$ch=curl_init($url);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
	$data  =  curl_exec($ch);
	curl_close($ch);
	return json_decode($data,1);
}
public static function post_curl($url,$post=''){
	$ch=curl_init($url);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
	$data  =  curl_exec($ch);
	curl_close($ch);
	return json_decode($data,1);
}
/**
* 
* @param 生成随机字符串
* 
* @return
*/
  private function get_randstr($length = 16) {
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    $str = "";
    for ($i = 0; $i < $length; $i++) {
      $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
    }
    return $str;
  }
  /**
  * 存储全局 jsapi_ticket
  * 
  * @return
  */
public static function get_jsapi_ticket() {
	global $_W;
	$item = pdo_fetch("SELECT jsapi_ticket FROM ".tablename('wechats')." WHERE weid = :weid" , array(':weid' => $weid));
	$item = json_decode(iunserializer($item['jsapi_ticket']),1);
	$jsapi_ticket =isset($_W['account']['jsapi_ticket']['ticket'])? $_W['account']['jsapi_ticket']['ticket']:$item['ticket'];
	$expire_time =isset($_W['account']['jsapi_ticket']['expire'])?$_W['account']['jsapi_ticket']['expire']:$item['expire'];
	
    if ($expire_time < time()) {
      $access_token =self::get_access_token();
      $url = "http://api.weixin.qq.com/cgi-bin/ticket/getticket?type=1&access_token=$access_token";
      $arr =self::get_curl($url);
      if(empty($arr['ticket']) || empty($arr['expires_in'])) {
				message('获取微信公众号授权失败, 请稍后重试！ 公众平台返回原始数据为: <br />' .$arr['errcode']. $arr['errmsg']);
			}
       	$record = array();
	$record['ticket'] = $arr['ticket'];//保存全局票据
	$record['expire'] =time() + $arr['expires_in'];//保存过期时间
	$row = array();
	$row['jsapi_ticket'] = iserializer($record);//序列化保存
	pdo_update('wechats', $row, array('weid' => $_W['weid']));
         $_W['account']['jsapi_ticket']['ticket'] = $record['ticket'];//写入全局
      	$_W['account']['jsapi_ticket']['expire'] = $record['expire'];
      	$ticket = $record['ticket'];
    } else {
      $ticket =$jsapi_ticket;
    }

    return $ticket;
  } 
  
/**
* 全局票据access_token
*/
public static function get_access_token(){
	global $_W;
	if( !empty($_W['account']['access_token']['token']) && !empty($_W['account']['access_token']['expire']) && $_W['account']['access_token']['expire'] >time()){//存在且不过期
		return $_W['account']['access_token']['token'];
	}else{
	if (empty( $_W['account']['key']) || empty( $_W['account']['secret'])) {
				message('请填写公众号的appid及appsecret, (需要你的号码为微信服务号)！', create_url('account/post', array('id' => $_W['weid'])), 'error');
			}
$appid = $_W['account']['key'];
$apps= $_W['account']['secret'];
$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$appid}&secret={$apps}";
  $arr = self::get_curl($url);
  if(empty($arr['access_token']) || empty($arr['expires_in'])) {
				message('获取微信公众号授权失败, 请稍后重试！ 公众平台返回原始数据为: <br />' .$arr['errcode']. $arr['errmsg']);
			}
			$record = array();
			$record['token'] = $arr['access_token'];//保存全局票据
			$record['expire'] =time() + $arr['expires_in'];//保存过期时间
			$row = array();
			$row['access_token'] = iserializer($record);//序列化保存
			pdo_update('wechats', $row, array('weid' => $_W['weid']));
    return $record['token'];
	}
} 
/**
* 获得签名
* 
* @return
*/
public static function get_sign() {
  	global $_W;
    $jsapi_ticket = self::get_jsapi_ticket();
    $url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    $timestamp = time();
    $nonceStr =self::get_randstr();

    // 这里参数的顺序要按照 key 值 ASCII 码升序排序
    $string = "jsapi_ticket=$jsapi_ticket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";

    $signature = sha1($string);

    $signPackage = array(
      "appId"     =>  $_W['account']['key'],
      "nonceStr"  => $nonceStr,
      "timestamp" => $timestamp,
      "url"       => $url,
      "signature" => $signature,
      "rawString" => $string
    );
    return $signPackage; 
  }
}
?>