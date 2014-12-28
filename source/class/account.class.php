<?php
/**
 * 公众号核心类
 *
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

abstract class WeAccount {

	/**
	 * 创建公众号操作对象
	 * @param int $uniAccountId
	 * @return WeAccount|NULL
	 */
	public static function create($uniAccountId) {
		$sql = 'SELECT * FROM ' . tablename('wechats') . ' WHERE `weid`=:weid';
		$pars = array();
		$pars[':weid'] = $uniAccountId;
		$account = pdo_fetch($sql, $pars);
		if(!empty($account)) {
			if($account['type'] == '1' || $type == 'weixin') {
				if(!class_exists('WeiXinAccount')) {
					require IA_ROOT . '/source/class/weixin.account.class.php';
				}
				return new WeiXinAccount($account);
			}
			if($account['type'] == '2' || $type == 'yixin') {
				if(!class_exists('YiXinAccount')) {
					require IA_ROOT . '/source/class/yixin.account.class.php';
				}
				return new YiXinAccount($account);
			}
		}
		return null;
	}
	
	/**
	 * 创建当前公众号操作对象
	 * @param array $uniAccount 统一公号对象 
	 */
	abstract public function __construct($uniAccount); 

	public function checkSign() {
		trigger_error('not supported.', E_USER_WARNING);
	}

	public function fetchAccountInfo() {
		trigger_error('not supported.', E_USER_WARNING);
	}

	/**
	 * 查询当前公号支持的统一消息类型, 当前支持的类型包括: text, image, voice, video, location, link, [subscribe, unsubscribe, qr, trace, click, view, enter]
	 * @return array 当前公号支持的消息类型集合
	 */
	public function queryAvailableMessages() {
		return array();
	}
	/**
	 * 查询当前公号支持的统一响应结构
	 * @return array 当前公号支持的响应结构集合, 当前支持的类型包括: text, image, voice, video, music, news, link, card
	 */
	public function queryAvailablePackets() {
		return array();
	}
	/**
	 * 分析消息内容, 参数为平台消息结构, 并返回统一消息结构
	 * @param array $message 统一消息结构, 见文档 todo
	 * @return bool 是否返回成功
	 */
	public function parse($message) {
		trigger_error('not supported.', E_USER_WARNING);
	}
	/**
	 * 响应消息内容, 参数为统一响应结构
	 * @param array $packet 统一响应结构, 见文档 todo
	 * @return bool 是否返回成功
	 */
	public function response($packet) {
		trigger_error('not supported.', E_USER_WARNING);
	}

	/**
	 * 查询当前公号是否支持消息推送
	 * @return bool 是否支持
	 */
	public function isPushSupported() {
		return false;
	}
	/*
	 * 向指定的用户推送消息
	 * @param string $uniid 指定用户(统一用户) todo
	 * @param array $packet 统一响应结构
	 * @return bool 是否成功
	 */
	public function push($uniid, $packet) {
		trigger_error('not supported.', E_USER_WARNING);
	}
	
	/**
	 * 查询当前公号是否支持群发消息
	 * @return boolean 是否支持
	 */
	public function isBroadcastSupported() {
		return false;
	}
	/**
	 * 向一组用户发送群发消息, 可选的可以指定是否要指定特定组
	 * @param array $packet 统一消息结构
	 * @param array $group 单独向一组用户群发
	 */
	public function broadcast($packet, $group = array()) {
		trigger_error('not supported.', E_USER_WARNING);
	}

	/**
	 * 查询当前公号是否支持菜单操作
	 * @return bool 是否支持
	 */
	public function isMenuSupported() {
		return false;
	}
	/**
	 * 创建菜单
	 * @param array $menu 统一菜单结构 todo
	 * @return bool 是否创建成功
	 */
	public function menuCreate($menu) {
		trigger_error('not supported.', E_USER_WARNING);
	}
	/**
	 * 删除菜单
	 * @return bool 是否删除成功
	 */
	public function menuDelete() {
		trigger_error('not supported.', E_USER_WARNING);
	}
	/**
	 * 修改菜单
	 * @param array $menu 统一菜单结构
	 * @return bool 是否修改成功
	 */
	public function menuModify($menu) {
		trigger_error('not supported.', E_USER_WARNING);
	}
	/**
	 * 查询菜单
	 * @return array 统一菜单结构
	 */
	public function menuQuery() {
		trigger_error('not supported.', E_USER_WARNING);
	}
	

	/**
	 * 查询当前公号粉丝管理的支持程度
	 * @return array 返回结果为支持的方法列表(fansGroupAll, fansGroupCreate, ...)
	 */
	public function queryFansActions() {
		return array();
	}
	/**
	 * 查询当前公号记录的分组信息
	 * @return array 统一分组结构集合
	 */
	public function fansGroupAll() {
		trigger_error('not supported.', E_USER_WARNING);
	}
	/**
	 * 在当前公号记录中创建一条分组信息
	 * @param array $group 统一分组结构 todo
	 * @return bool 是否执行成功
	 */
	public function fansGroupCreate($group) {
		trigger_error('not supported.', E_USER_WARNING);
	}
	/**
	 * 在当前公号记录中修改一条分组信息
	 * @param array $group 统一分组结构
	 * @return bool 是否执行成功
	 */
	public function fansGroupModify($group) {
		trigger_error('not supported.', E_USER_WARNING);
	}
	/**
	 * 将指定用户移至另一分组中
	 * @param string $uniid 指定用户(统一用户)
	 * @param array $group 统一分组结构
	 * @return bool 是否执行成功
	 */
	public function fansMoveGroup($uniid, $group) {
		trigger_error('not supported.', E_USER_WARNING);
	}
	/**
	 * 查询指定的用户所在的分组
	 * @param string $uniid 指定用户(统一用户)
	 * @return array $group 统一分组结构
	 */
	public function fansQueryGroup($uniid) {
		trigger_error('not supported.', E_USER_WARNING);
	}
	/**
	 * 查询指定的用户的基本信息
	 * @param string $uniid 指定用户(统一用户)
	 * @return array 统一粉丝信息结构 todo
	 */
	public function fansQueryInfo($uniid) {
		trigger_error('not supported.', E_USER_WARNING);
	}
	/**
	 * 查询当前公号的所有粉丝
	 * @return array 统一粉丝信息结构集合
	 */
	public function fansAll() {
		trigger_error('not supported.', E_USER_WARNING);
	}
	
	/**
	 * 查询当前公号地理位置追踪的支持情况
	 * @return array 返回结果为支持的方法列表(traceCurrent, traceHistory)
	 */
	public function queryTraceActions() {
		return array();
	}
	/**
	 * 追踪指定的用户的当前位置
	 * @param string $uniid 指定用户(统一用户)
	 * @return array 地理位置信息
	 */
	public function traceCurrent($uniid) {
		trigger_error('not supported.', E_USER_WARNING);
	}
	/**
	 * 追踪指定的用户的地理位置
	 * @param string $uniid 指定用户(统一用户)
	 * @param int $time 追踪的时间范围
	 * @return array 地理位置信息追踪集合
	 */
	public function traceHistory($uniid, $time) {
		trigger_error('not supported.', E_USER_WARNING);
	}
	
	/**
	 * 查询当前公号二维码支持情况
	 * @return array 返回结果为支持的方法列表(barCodeCreateDisposable, barCodeCreateFixed)
	 */
	public function queryBarCodeActions() {
		return array();
	}
	/**
	 * 生成临时的二维码 todo
	 * 
	 */
	public function barCodeCreateDisposable($barcode) {
		trigger_error('not supported.', E_USER_WARNING);
	}
	/**
	 * 生成永久的二维码 todo
	 */
	public function barCodeCreateFixed($barcode) {
		trigger_error('not supported.', E_USER_WARNING);
	}
}