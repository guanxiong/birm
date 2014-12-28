<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * $sn: origins/source/modules/engine.php : v e555ac6ee0e2 : 2014/10/21 09:03:55 : Gorden $
 */
defined('IN_IA') or exit('Access Denied');

class WeEngine {
	private $token = '';
	private $modules = array();
	public $message = array();
	public $params = array();
	public $response = array();
	public $keyword = array();

	public function __construct() {
		global $_W;
		$this->token = $_W['account']['token'];
		$this->modules = array_keys($_W['account']['modules']);
		$this->modules[] = 'cover';
		$this->modules[] = 'welcome';
		$this->modules[] = 'default';
		$this->modules = array_unique($this->modules);
	}

	public function start() {
		global $_W;
		if(empty($this->token)) {
			exit('Access Denied');
		}
		if(!WeUtility::checkSign($this->token)) {
			exit('Access Denied');
		}
		if(strtolower($_SERVER['REQUEST_METHOD']) == 'get') {
			ob_clean();
			ob_start();
			exit($_GET['echostr']);
		}
		if(strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
			$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
			$this->message = WeUtility::parse($postStr);
			if (empty($this->message)) {
				WeUtility::logging('waring', 'Request Failed');
				exit('Request Failed');
			}
			if(in_array('fans', $this->modules)) {
				$this->subscribe(array($_W['account']['modules']['fans']));
			}
			$sessionid = md5($this->message['from'] . $this->message['to'] . $_W['weid']);
			session_id($sessionid);
			WeSession::$weid = $_W['weid'];
			WeSession::$from = $this->message['from'];
			WeSession::$expire = 3600;
			WeSession::start();

			WeUtility::logging('trace', $this->message);
			$pars = $this->matcher();
			$pars[] = array('module' => 'default', 'rule' => '-1');

			foreach($pars as $par) {
				if(empty($par['module'])) {
					continue;
				}
				$this->params = $par;
				$this->response = $this->process();
				if(is_array($this->response) && (($this->response['type'] == 'text' && !empty($this->response['content'])) || ($this->response['type'] == 'news' && !empty($this->response['items'])) || !in_array($this->type, array('text', 'news')))) {
					if(!empty($par['keyword'])) {
						$this->keyword = $par['keyword'];
						if ($_W['weid'] != $par['weid']) {
							$_W['weid'] = $par['weid'];
							$this->response = $this->process();
						}
					}
					break;
				}
			}
			WeUtility::logging('params', $this->params);
			WeUtility::logging('response', $this->response);
			$resp = WeUtility::response($this->response);
			$mapping = array(
				'[from]' => $this->message['from'],
				'[to]' => $this->message['to'],
				'[rule]' => $this->params['rule']
			);
			echo str_replace(array_keys($mapping), array_values($mapping), $resp);

			$subscribes = array();
			foreach($_W['account']['modules'] as $m) {
				if($m['name'] != 'fans' && in_array($m['name'], $this->modules) && is_array($m['subscribes']) && !empty($m['subscribes'])) {
					$subscribes[] = $m;
				}
			}
			if(!empty($subscribes)) {
				$this->subscribe($subscribes);
			}
			exit();
		}
		WeUtility::logging('waring', 'Request Failed');
		exit('Request Failed');
	}

	private function subscribe($subscribes) {
		global $_W;
		foreach($subscribes as $m) {
			$obj = WeUtility::createModuleReceiver($m['name']);
			$obj->message = $this->message;
			$obj->params = $this->params;
			$obj->response = $this->response;
			$obj->keyword = $this->keyword;
			$obj->module = $m;
			if (method_exists($obj, 'receive')) {
				$obj->receive();
			}
		}
	}

	private function matcher() {
		global $_W;
		$params = array();
		if($this->message['msgtype'] == 'event') {
			$event = strtolower($this->message['event']);
			if (method_exists($this, 'matcherEvent'.$event)) {
				$params = call_user_func(array($this, 'matcherEvent'.$event));
			} else {
				$params += $this->handler($event);
			}
			if(!empty($params)) {
				return (array)$params;
			}
		}
		if (!empty($_SESSION['__contextmodule']) && in_array($_SESSION['__contextmodule'], $this->modules)) {
			if($_SESSION['__contextexpire'] > TIMESTAMP) {
				if ($_SESSION['__contextpriority'] < 255 && $this->message['msgtype'] == 'text') {
					$params += $this->matcherText(intval($_SESSION['__contextpriority']));
				}
				$params[] = array('module' => $_SESSION['__contextmodule'], 'rule' => $_SESSION['__contextrule'], 'priority' => $_SESSION['__contextpriority'], 'context' => true);
				return $params;
			} else {
				unset($_SESSION);
				session_destroy();
			}
		}
		if ($this->message['msgtype'] != 'event' && method_exists($this, 'matcher'.$this->message['msgtype'])) {
			$params += call_user_func(array($this, 'matcher'.$this->message['msgtype']));
		} else {
			$params += $this->handler($this->message['msgtype']);
		}
		return $params;
	}
	
	private function matcherEventClick() {
		$content = $this->message['eventkey'];
		if (!empty($content)) {
			$this->message['content'] = $content;
			return $this->matcherText();
		}
	}
	
	private function matcherEventSubscribe() {
		$params = array();
		if (isset($GLOBALS['_W']['account']['modules']['qrcode']) && !empty($this->message['eventkey']) && strexists($this->message['eventkey'], 'qrscene')) {
			list($temp, $sceneid) = explode('_', $this->message['eventkey']);
			$this->message['eventkey'] = $sceneid;
			return $this->matcherEventSCAN();
		}
		$params += $this->handler('subscribe');
		$params[] = array('module' => 'welcome');
		return $params;
	}
	
	private function matcherEventSCAN() {
		if (!isset($GLOBALS['_W']['account']['modules']['qrcode'])) {
			return array();
		}
		$sceneid = $this->message['eventkey'];
		if (!empty($sceneid)) {
			$row = pdo_fetch("SELECT id, keyword FROM ".tablename('qrcode')." WHERE qrcid = '{$sceneid}' AND weid = '{$GLOBALS['_W']['weid']}'");
			if (!empty($row['keyword'])) {
				$this->message['content'] = $row['keyword'];
				return $this->matcherText();
			}
		}
	}
	
	private function matcherEventLocation() {
		return $this->matcherEvent();
	}
	
	private function matcherEventScancode_waitmsg() {
		return $this->matcherEvent();
	}
	
	private function matcherEventScancode_push() {
		return $this->matcherEvent();
	}
	
	private function matcherEvent() {
		if (!empty($this->message['eventkey'])) {
			$this->message['content'] = strval($this->message['eventkey']);
			$this->message['type'] = 'text';
			$this->message['redirection'] = true;
			$this->message['source'] = $this->message['event'];
			return $this->matcherText();
		}
		return array();
	}
	
	private function matcherEventPic_sysphoto() {
		return $this->matcherPic();
	}
	
	private function matcherEventPic_weixin() {
		return $this->matcherPic();
	}
	
	private function matcherEventPic_photo_or_album() {
		return $this->matcherPic();
	}
	
	private function matcherPic() {
		if (!empty($this->message['sendpicsinfo']['count'])) {
			foreach ($this->message['sendpicsinfo']['piclist'] as $item) {
				pdo_insert('menu_event', array(
					'weid' => $GLOBALS['_W']['weid'],
					'keyword' => $this->message['eventkey'],
					'type' => $this->message['event'],
					'picmd5' => $item['PicMd5Sum'],
				));
			}
		}
		return true;
	}
	
	
	private function handler($type) {
		if (empty($type)) {
			return array();
		}
		global $_W;
		$params = array();
		$df = $_W['account']['default_message'];
		if(is_array($df) && isset($df[$type]) && in_array($df[$type], $this->modules)) {
			$params[] = array('module' => $df[$type], 'rule' => '-1');
		}
		return $params;
	}

	public function matcherText($order = -1) {
		$pars = array();
		$input = $this->message['content'];
		if (!isset($input)) {
			return $pars;
		}
		global $_W;
		$order = intval($order);
		$condition = "`status`=1 AND (`weid`='{$_W['weid']}' OR `weid`=0 ".(!empty($_W['account']['subwechats']) ? " OR `weid` IN ({$_W['account']['subwechats']})" : '').") AND `displayorder`>{$order}";
		$condition .= " AND (((`type` = '1' OR `type` = '2') AND `content` = :c1) OR (`type` = '4') OR (`type` = '3' AND :c2 REGEXP `content`) OR (`type` = '2' AND INSTR(:c3, `content`) > 0))";
		$params = array();
		$params[':c1'] = $input;
		$params[':c2'] = $input;
		$params[':c3'] = $input;
		$keywords = rule_keywords_search($condition, $params);
		if (empty($keywords)) {
			return $pars;
		}
		foreach($keywords as $kwd) {
			$params = array(
				'module' => $kwd['module'],
				'rule' => $kwd['rid'],
				'priority' => $kwd['displayorder'],
				'keyword' => $kwd,
				'weid' => $kwd['weid'],
			);
			$pars[] = $params;
		}
		return $pars;
	}

	private function matcherImage() {
		$params = array();
		global $_W;
		if (!empty($this->message['picurl'])) {
			$response = ihttp_get($this->message['picurl']);
			if (!empty($response)) {
				$md5 = md5($response['content']);
				$event = pdo_fetch("SELECT keyword, type FROM ".tablename('menu_event')." WHERE picmd5 = '$md5' AND weid = '{$_W['weid']}'");
				if (!empty($event['keyword'])) {
					pdo_delete('menu_event', array('picmd5' => $md5, 'weid' => $_W['weid']));
					$this->message['content'] = $event['keyword'];
					$this->message['type'] = 'text';
					$this->message['redirection'] = true;
					$this->message['source'] = $event['type'];
					return $this->matcherText();
				}
			}
		}
		$df = $_W['account']['default_message'];
		if(is_array($df) && in_array($df['image'], $this->modules)) {
			$params[] = array('module' => $df['image'], 'rule' => '-1');
		}
		return $params;
	}

	private function matcherVoice() {
		$params = array();
		global $_W;
		$df = $_W['account']['default_message'];
		if(is_array($df) && in_array($df['voice'], $this->modules)) {
			$params[] = array('module' => $df['voice'], 'rule' => '-1');
		}
		return $params;
	}

	private function matcherVideo() {
		$params = array();
		global $_W;
		$df = $_W['account']['default_message'];
		if(is_array($df) && in_array($df['video'], $this->modules)) {
			$params[] = array('module' => $df['video'], 'rule' => '-1');
		}
		return $params;
	}

	private function matcherLocation() {
		$params = array();
		global $_W;
		$df = $_W['account']['default_message'];
		if(is_array($df) && in_array($df['location'], $this->modules)) {
			$params[] = array('module' => $df['location'], 'rule' => '-1');
		}
		return $params;
	}

	private function matcherLink() {
		$params = array();
		global $_W;
		$df = $_W['account']['default_message'];
		if(is_array($df) && in_array($df['link'], $this->modules)) {
			$params[] = array('module' => $df['link'], 'rule' => '-1');
		}
		return $params;
	}

	private function process() {
		global $_W;
		$response = false;
		if (empty($this->params['module']) || !in_array($this->params['module'], $this->modules)) {
			return false;
		}
		$processor = WeUtility::createModuleProcessor($this->params['module']);
		$processor->message = $this->message;
		$processor->rule = $this->params['rule'];
		$processor->priority = intval($this->params['priority']);
		$processor->module = $_W['account']['modules'][$this->params['module']];
		$processor->inContext = $this->params['context'] === true;
		$response = $processor->respond();
		if(empty($response)) {
			return false;
		}
		return $response;
	}
}

class WeSession {
	public static $weid;
	public static $from;
	public static $expire;

	public static function start() {
		$sess = new WeSession();
		session_set_save_handler(array(&$sess, 'open'), array(&$sess, 'close'), array(&$sess, 'read'), array(&$sess, 'write'), array(&$sess, 'destroy'), array(&$sess, 'gc'));
		session_start();
	}

	public function open() {
		return true;
	}

	public function close() {
		return true;
	}

	public function read($sessionid) {
		$sql = 'SELECT * FROM ' . tablename('sessions') . ' WHERE `sid`=:sessid AND `expiretime`>:time';
		$params = array();
		$params[':sessid'] = $sessionid;
		$params[':time'] = TIMESTAMP;
		$row = pdo_fetch($sql, $params);
		if(is_array($row) && !empty($row['data'])) {
			return $row['data'];
		}
		return false;
	}

	public function write($sessionid, $data) {
		$row = array();
		$row['sid'] = $sessionid;
		$row['weid'] = WeSession::$weid;
		$row['from_user'] = WeSession::$from;
		$row['data'] = $data;
		$row['expiretime'] = TIMESTAMP + WeSession::$expire;
		return pdo_insert('sessions', $row, true) == 1;
	}

	public function destroy($sessionid) {
		$row = array();
		$row['sid'] = $sessionid;
		return pdo_delete('sessions', $row) == 1;
	}

	public function gc($expire) {
		$sql = 'DELETE FROM ' . tablename('sessions') . ' WHERE `expiretime`<:expire';
		return pdo_query($sql, array(':expire' => TIMESTAMP)) == 1;
	}
}

class WeUtility {
	public static function rootPath() {
		static $path;
		if(empty($path)) {
			$path = dirname(__FILE__);
			$path = str_replace('\\', '/', $path);
		}
		return $path;
	}

	public static function checkSign($token) {
		$signkey = array($token, $_GET['timestamp'], $_GET['nonce']);
		sort($signkey, SORT_STRING);
		$signString = implode($signkey);
		$signString = sha1($signString);
		if($signString == $_GET['signature']){
			return true;
		}else{
			return false;
		}
	}

	public static function createModule($name) {
		$classname = ucfirst($name) . 'Module';
		if(!class_exists($classname)) {
			$file = IA_ROOT . "/source/modules/{$name}/module.php";
			if(!is_file($file)) {
				trigger_error('Module Definition File Not Found', E_USER_WARNING);
				return null;
			}
			require $file;
		}
		if(!class_exists($classname)) {
			trigger_error('Module Definition Class Not Found', E_USER_WARNING);
			return null;
		}
		$o = new $classname();
		$o->modulename = $name;
		$o->module = $GLOBALS['_W']['account']['modules'][$GLOBALS['_W']['modules'][$name]['mid']];
		$o->_saveing_params = array(
			'weid' => $GLOBALS['_W']['weid'],
			'mid' => $GLOBALS['_W']['modules'][$name]['mid'],
		);
		if($o instanceof WeModule) {
			return $o;
		} else {
			trigger_error('Module Class Definition Error', E_USER_WARNING);
			return null;
		}
	}

	public static function createModuleProcessor($name) {
		$classname = "{$name}ModuleProcessor";
		if(!class_exists($classname)) {
			$file = WeUtility::rootPath() . "/{$name}/processor.php";
			if(!is_file($file)) {
				trigger_error('ModuleProcessor Definition File Not Found '.$file, E_USER_WARNING);
				return null;
			}
			require $file;
		}
		if(!class_exists($classname)) {
			trigger_error('ModuleProcessor Definition Class Not Found', E_USER_WARNING);
			return null;
		}
		$o = new $classname();
		if($o instanceof WeModuleProcessor) {
			return $o;
		} else {
			trigger_error('ModuleProcessor Class Definition Error', E_USER_WARNING);
			return null;
		}
	}

	public static function createModuleReceiver($name) {
		$classname = "{$name}ModuleReceiver";
		if(!class_exists($classname)) {
			$file = WeUtility::rootPath() . "/{$name}/receiver.php";
			if(!is_file($file)) {
				trigger_error('ModuleReceiver Definition File Not Found '.$file, E_USER_WARNING);
				return null;
			}
			require $file;
		}
		if(!class_exists($classname)) {
			trigger_error('ModuleReceiver Definition Class Not Found', E_USER_WARNING);
			return null;
		}
		$o = new $classname();
		if($o instanceof WeModuleReceiver) {
			return $o;
		} else {
			trigger_error('ModuleReceiver Class Definition Error', E_USER_WARNING);
			return null;
		}
	}

	public static function createModuleSite($name) {
		$classname = "{$name}ModuleSite";
		if(!class_exists($classname)) {
			$file = WeUtility::rootPath() . "/{$name}/site.php";
			if(!is_file($file)) {
				trigger_error('ModuleSite Definition File Not Found '.$file, E_USER_WARNING);
				return null;
			}
			require $file;
		}
		if(!class_exists($classname)) {
			trigger_error('ModuleSite Definition Class Not Found', E_USER_WARNING);
			return null;
		}
		$o = new $classname();
		$o->module = $GLOBALS['_W']['account']['modules'][$GLOBALS['_W']['modules'][$name]['mid']];
		$o->weid = $GLOBALS['_W']['weid'];
		$o->inMobile = defined('IN_MOBILE');
		if($o instanceof WeModuleSite) {
			return $o;
		} else {
			trigger_error('ModuleReceiver Class Definition Error', E_USER_WARNING);
			return null;
		}
	}

	/**
	 * 分析请求数据
	 * @param string $request 接口提交的请求数据
	 * 具体数据格式与微信接口XML结构一致
	 *
	 * @return array 请求数据结构
	 */
	public static function parse($message) {
		$packet = array();
		if (!empty($message)){
			$obj = simplexml_load_string($message, 'SimpleXMLElement', LIBXML_NOCDATA);
			if($obj instanceof SimpleXMLElement) {
				$obj = json_decode(json_encode($obj),true);
				
				$packet['from'] = strval($obj['FromUserName']);
				$packet['to'] = strval($obj['ToUserName']);
				$packet['time'] = strval($obj['CreateTime']);
				$packet['type'] = strval($obj['MsgType']);
				$packet['event'] = strval($obj['Event']);

				foreach ($obj as $variable => $property) {
					if (is_array($property)) {
						$property = array_change_key_case($property);
					}
					$packet[strtolower($variable)] = $property;
				}
				if($packet['type'] == 'event') {
					$packet['type'] = $packet['event'];
					unset($packet['content']);
				}
			}
		}
		return $packet;
	}

	/**
	 * 按照响应内容组装响应数据
	 * @param array $packet 响应内容
	 *
	 * @return string
	 */
	public static function response($packet) {
		if (!is_array($packet)) {
			return $packet;
		}
		if(empty($packet['CreateTime'])) {
			$packet['CreateTime'] = time();
		}
		if(empty($packet['MsgType'])) {
			$packet['MsgType'] = 'text';
		}
		if(empty($packet['FuncFlag'])) {
			$packet['FuncFlag'] = 0;
		} else {
			$packet['FuncFlag'] = 1;
		}
		return self::array2xml($packet);
	}

	public static function logging($level = 'info', $message = '') {
		if(!DEVELOPMENT) {
			return true;
		}
		$filename = IA_ROOT . '/data/logs/' . date('Ymd') . '.log';
		mkdirs(dirname($filename));
		$content = date('Y-m-d H:i:s') . " {$level} :\n------------\n";
		if(is_string($message)) {
			$content .= "String:\n{$message}\n";
		}
		if(is_array($message)) {
			$content .= "Array:\n";
			foreach($message as $key => $value) {
				$content .= sprintf("%s : %s ;\n", $key, $value);
			}
		}
		if($message == 'get') {
			$content .= "GET:\n";
			foreach($_GET as $key => $value) {
				$content .= sprintf("%s : %s ;\n", $key, $value);
			}
		}
		if($message == 'post') {
			$content .= "POST:\n";
			foreach($_POST as $key => $value) {
				$content .= sprintf("%s : %s ;\n", $key, $value);
			}
		}
		$content .= "\n";

		$fp = fopen($filename, 'a+');
		fwrite($fp, $content);
		fclose($fp);
	}

	public static function array2xml($arr, $level = 1, $ptagname = '') {
		$s = $level == 1 ? "<xml>" : '';
		foreach($arr as $tagname => $value) {
			if (is_numeric($tagname)) {
				$tagname = $value['TagName'];
				unset($value['TagName']);
			}
			if(!is_array($value)) {
				$s .= "<{$tagname}>".(!is_numeric($value) ? '<![CDATA[' : '').$value.(!is_numeric($value) ? ']]>' : '')."</{$tagname}>";
			} else {
				$s .= "<{$tagname}>".self::array2xml($value, $level + 1)."</{$tagname}>";
			}
		}
		$s = preg_replace("/([\x01-\x08\x0b-\x0c\x0e-\x1f])+/", ' ', $s);
		return $level == 1 ? $s."</xml>" : $s;
	}
}

abstract class WeModule {
	public $modulename;
	public function fieldsFormDisplay($rid = 0) {
		return '';
	}
	public function fieldsFormValidate($rid = 0) {
		return '';
	}
	public function fieldsFormSubmit($rid) {
		//
	}
	public function ruleDeleted($rid) {
		return true;
	}
	public function settingsDisplay($settings) {
		//
	}
	protected function saveSettings($settings) {
		$weid = $this->_saveing_params['weid'];
		$mid = $this->_saveing_params['mid'];
		if(empty($weid) || empty($mid)) {
			message('访问出错, 请返回重试. ');
		}
		if (pdo_fetchcolumn("SELECT mid FROM ".tablename('wechats_modules')." WHERE mid = :mid AND weid = :weid", array(':mid' => $mid, ':weid' => $weid))) {
			return pdo_update('wechats_modules', array('settings' => iserializer($settings)), array('weid' => $weid, 'mid' => $mid)) === 1;
		} else {
			return pdo_insert('wechats_modules', array('settings' => iserializer($settings), 'mid' => $mid ,'weid' => $weid, 'enabled' => 1)) === 1;
		}
	}

	protected function createMobileUrl($do, $querystring = array()) {
		$querystring['name'] = strtolower($this->module['name']);
		$querystring['do'] = $do;
		$querystring['weid'] = $GLOBALS['_W']['weid'];
		return create_url('mobile/module', $querystring);
	}

	protected function createWebUrl($do, $querystring = array()) {
		$querystring['name'] = strtolower($this->module['name']);
		$querystring['do'] = $do;
		$querystring['weid'] = $GLOBALS['_W']['weid'];
		return create_url('site/module', $querystring);
	}

	protected function template($filename, $flag = TEMPLATE_INCLUDEPATH) {
		global $_W;
		$mn = strtolower($this->modulename);
		$source = IA_ROOT . "/source/modules/{$mn}/template/{$filename}.html";
		$compile = "{$_W['template']['compile']}/web/{$_W['template']['current']}/modules/{$mn}/{$filename}.tpl.php";
		/**
		 * 此处为兼容0.41版本以前的写法
		 */
		if(!is_file($source)) {
			list($path, $file) = explode('/', $filename);
			$source = IA_ROOT . "/source/modules/$path/template/{$file}.html";
			$compile = "{$_W['template']['compile']}/web/{$_W['template']['current']}/modules/{$path}/{$file}.tpl.php";
		}
		/**
		 * end
		 */
		if(!is_file($source)) {
			$source = "{$_W['template']['source']}/web/{$_W['template']['current']}/{$filename}.html";
			$compile = "{$_W['template']['compile']}/web/{$_W['template']['current']}/{$filename}.tpl.php";
			if(!is_file($source)) {
				$source = "{$_W['template']['source']}/web/default/{$filename}.html";
				$compile = "{$_W['template']['compile']}/web/default/{$filename}.tpl.php";
			}
		}

		if(!is_file($source)) {
			exit("Error: template source '{$filename}' is not exist!");
		}
		if (DEVELOPMENT || !is_file($compile) || filemtime($source) > filemtime($compile)) {
			template_compile($source, $compile, true);
		}
		switch ($flag) {
			case TEMPLATE_DISPLAY:
			default:
				extract($GLOBALS, EXTR_SKIP);
				include $compile;
				break;
			case TEMPLATE_FETCH:
				extract($GLOBALS, EXTR_SKIP);
				ob_start();
				ob_clean();
				include $compile;
				$contents = ob_get_contents();
				ob_clean();
				return $contents;
				break;
			case TEMPLATE_INCLUDEPATH:
				return $compile;
				break;
			case TEMPLATE_CACHE:
				exit('暂未支持');
				break;
		}
	}
}

abstract class WeModuleProcessor {
	public $inContext;
	protected function beginContext($expire = 1800) {
		if($this->inContext) {
			return false;
		}
		$expire = intval($expire);
		WeSession::$expire = $expire;
		$_SESSION['__contextmodule'] = $this->module['name'];
		$_SESSION['__contextrule'] = $this->rule;
		$_SESSION['__contextexpire'] = TIMESTAMP + $expire;
		$_SESSION['__contextpriority'] = $this->priority;
		$this->inContext = true;
		return true;
	}
	protected function refreshContext($expire = 1800) {
		if(!$this->inContext) {
			return false;
		}
		$expire = intval($expire);
		WeSession::$expire = $expire;
		$_SESSION['__contextexpire'] = TIMESTAMP + $expire;
		return true;
	}
	protected function endContext() {
		unset($_SESSION['__contextmodule']);
		unset($_SESSION['__contextrule']);
		unset($_SESSION['__contextexpire']);
		unset($_SESSION['__contextpriority']);
		unset($_SESSION);
		session_destroy();
	}
	public $priority;
	public $message;
	public $rule;
	public $module;
	abstract function respond();
	protected function respText($content) {
		if(stripos($content,'http://') === false) {
			preg_match_all("/(mobile\.php(?:.*?))['|\"]/", $content, $urls);
			if (!empty($urls[1])) {
				foreach ($urls[1] as $url) {
					$content = str_replace($url, $this->buildSiteUrl($url), $content);
				}
			}
		}
		$content = str_replace("\r\n", "\n", $content);
		$response = array();
		$response['FromUserName'] = $this->message['to'];
		$response['ToUserName'] = $this->message['from'];
		$response['MsgType'] = 'text';
		$response['Content'] = htmlspecialchars_decode($content);
		return $response;
	}
	protected function respImage($mid) { 
		$response = array();
		$response['FromUserName'] = $this->message['to'];
		$response['ToUserName'] = $this->message['from'];
		$response['MsgType'] = 'image';
		$response['Image']['MediaId'] = $mid;
		return $response;
	}
	protected function respVoice($mid) {
		$response = array();
		$response['FromUserName'] = $this->message['to'];
		$response['ToUserName'] = $this->message['from'];
		$response['MsgType'] = 'voice';
		$response['Voice']['MediaId'] = $mid;
		return $response;
	}
	protected function respVideo(array $video) {
		$response = array();
		$response['FromUserName'] = $this->message['to'];
		$response['ToUserName'] = $this->message['from'];
		$response['MsgType'] = 'video';
		$response['Video']['MediaId'] = $video['video'];
		$response['Video']['ThumbMediaId'] = $video['thumb'];
		return $response;
	}
	protected function respMusic(array $music) {
		global $_W;
		$music = array_change_key_case($music);
		$response = array();
		$response['FromUserName'] = $this->message['to'];
		$response['ToUserName'] = $this->message['from'];
		$response['MsgType'] = 'music';
		$response['Music'] = array(
			'Title'	=> $music['title'],
			'Description' => $music['description'],
			'MusicUrl' => strpos($music['musicurl'], 'http://') === FALSE ? $_W['attachurl'] . $music['musicurl'] : $music['musicurl'],
		);
		if (empty($music['hqmusicurl'])) {
			$response['Music']['HQMusicUrl'] = $response['Music']['MusicUrl'];
		} else {
			$response['Music']['HQMusicUrl'] = strpos($music['hqmusicurl'], 'http://') === FALSE ? $_W['attachurl'] . $music['hqmusicurl'] : $music['hqmusicurl'];
		}
		if($music['thumb']) {
			$response['Music']['ThumbMediaId'] = $music['thumb'];
		}
		return $response;
	}
	protected function respNews(array $news) {
		$news = array_change_key_case($news);
		if (!empty($news['title'])) {
			$news = array($news);
		}
		$response = array();
		$response['FromUserName'] = $this->message['to'];
		$response['ToUserName'] = $this->message['from'];
		$response['MsgType'] = 'news';
		$response['ArticleCount'] = count($news);
		$response['Articles'] = array();
		foreach ($news as $row) {
			$response['Articles'][] = array(
				'Title' => $row['title'],
				'Description' => ($response['ArticleCount'] > 1) ? '' : $row['description'],
				'PicUrl' => !empty($row['picurl']) && !strexists($row['picurl'], 'http://') ? $GLOBALS['_W']['attachurl'] . $row['picurl'] : $row['picurl'],
				'Url' => $this->buildSiteUrl($row['url']),
				'TagName' => 'item',
			);
		}
		return $response;
	}

	protected function buildSiteUrl($url) {
		global $_W;
		if (!strexists($url, 'mobile.php')) {
			return $url;
		}

		$mapping = array(
			'[from]' => $this->message['from'],
			'[to]' => $this->message['to'],
			'[rule]' => $this->rule,
			'[weid]' => $GLOBALS['_W']['weid'],
		);
		$url = str_replace(array_keys($mapping), array_values($mapping), $url);

		$vars = array();
		$pass = array();
		$pass['fans'] = $this->message['from'];

		$row = fans_search($pass['fans'], array('salt'));
		if(!is_array($row) || empty($row['salt'])) {
			$row = array('salt' => '');
		}
		$pass['time'] = TIMESTAMP;
		$pass['hash'] = md5("{$pass['fans']}{$pass['time']}{$row['salt']}{$_W['config']['setting']['authkey']}");
		$auth = base64_encode(json_encode($pass));
		$vars['weid'] = $_W['weid'];
		$vars['__auth'] = $auth;
		$vars['forward'] = base64_encode($url);
		return $_W['siteroot'] . create_url('mobile/auth', $vars);
	}

	protected function createMobileUrl($do, $querystring = array()) {
		$querystring['name'] = strtolower($this->module['name']);
		$querystring['do'] = $do;
		$querystring['weid'] = $GLOBALS['_W']['weid'];
		return create_url('mobile/module', $querystring);
	}

	protected function createWebUrl($do, $querystring = array()) {
		$querystring['name'] = strtolower($this->module['name']);
		$querystring['do'] = $do;
		$querystring['weid'] = $GLOBALS['_W']['weid'];
		return create_url('site/module', $querystring);
	}
}

abstract class WeModuleReceiver {
	public $message;
	public $params;
	public $response;
	public $keyword;
	public $module;
	abstract function receive();
}

abstract class WeModuleSite {
	public $module;
	public $weid;
	public $inMobile;

	protected function pay($params = array()) {
		global $_W;
		if($params['fee'] <= 0) {
			message('支付错误, 金额小于0');
		}
		$params['module'] = $this->module['name'];
		$sql = 'SELECT * FROM ' . tablename('paylog') . ' WHERE `weid`=:weid AND `module`=:module AND `tid`=:tid';
		$pars  = array();
		$pars[':weid'] = $_W['weid'];
		$pars[':module'] = $params['module'];
		$pars[':tid'] = $pars['tid'];
		$log = pdo_fetch($sql, $pars);
		if(!empty($log) && $log['status'] == '1') {
			message('这个订单已经支付成功, 不需要重复支付.');
		}
		include $this->template('paycenter');
		exit;
	}

	public function payResult($ret) {
		global $_W;
		if($ret['from'] == 'return') {
			if ($ret['type'] == 'credit2') {
				message('已经成功支付', create_url('mobile/channel', array('name' => 'index', 'weid' => $_W['weid'])));
			} else {
				message('已经成功支付', '../../' . create_url('mobile/channel', array('name' => 'index', 'weid' => $_W['weid'])));
			}
		}
	}

	protected function payResultQuery($tid) {
		$sql = 'SELECT * FROM ' . tablename('paylog') . ' WHERE `module`=:module AND `tid`=:tid';
		$params = array();
		$params[':module'] = $this->module['name'];
		$params[':tid'] = $tid;
		$log = pdo_fetch($sql, $params);
		$ret = array();
		if(!empty($log)) {
			$ret['weid'] = $log['weid'];
			$ret['result'] = $log['status'] == '1' ? 'success' : 'failed';
			$ret['type'] = $log['type'];
			$ret['from'] = 'query';
			$ret['tid'] = $log['tid'];
			$ret['user'] = $log['openid'];
			$ret['fee'] = $log['fee'];
		}
		return $ret;
	}

	protected function createMobileUrl($do, $querystring = array()) {
		$querystring['name'] = strtolower($this->module['name']);
		$querystring['do'] = $do;
		$querystring['weid'] = $this->weid;
		return create_url('mobile/module', $querystring);
	}

	protected function createWebUrl($do, $querystring = array()) {
		$querystring['name'] = strtolower($this->module['name']);
		$querystring['do'] = $do;
		$querystring['weid'] = $this->weid;
		return create_url('site/module', $querystring);
	}

	protected function template($filename, $flag = TEMPLATE_INCLUDEPATH) {
		global $_W;
		$mn = strtolower($this->module['name']);
		if($this->inMobile) {
			$source = "{$_W['template']['source']}/mobile/{$_W['account']['template']}/{$mn}/{$filename}.html";
			$compile = "{$_W['template']['compile']}/mobile/{$_W['account']['template']}/{$mn}/{$filename}.tpl.php";
			if (!is_file($source)) {
				$source = "{$_W['template']['source']}/mobile/default/{$mn}/{$filename}.html";
				$compile = "{$_W['template']['compile']}/mobile/default/{$mn}/{$filename}.tpl.php";
			}
			if (!is_file($source)) {
				$source = IA_ROOT . "/source/modules/{$mn}/template/mobile/{$filename}.html";
				$compile = "{$_W['template']['compile']}/mobile/modules/{$mn}/{$filename}.tpl.php";
			}
			if(!is_file($source)) {
				$source = "{$_W['template']['source']}/mobile/{$_W['account']['template']}/{$filename}.html";
				$compile = "{$_W['template']['compile']}/mobile/{$_W['account']['template']}/{$filename}.tpl.php";
			}
			if(!is_file($source)) {
				$source = "{$_W['template']['source']}/mobile/default/{$filename}.html";
				$compile = "{$_W['template']['compile']}/mobile/default/{$filename}.tpl.php";
			}
		} else {
			$source = "{$_W['template']['source']}/web/{$_W['account']['template']}/modules/{$mn}/{$filename}.html";
			$compile = "{$_W['template']['compile']}/web/{$_W['account']['template']}/modules/{$mn}/{$filename}.tpl.php";
			if (!is_file($source)) {
				$source = "{$_W['template']['source']}/web/default/modules/{$mn}/{$filename}.html";
				$compile = "{$_W['template']['compile']}/web/default/modules/{$mn}/{$filename}.tpl.php";
			}
			if (!is_file($source)) {
				$source = IA_ROOT . "/source/modules/{$mn}/template/{$filename}.html";
				$compile = "{$_W['template']['compile']}/web/{$_W['account']['template']}/modules/{$mn}/{$filename}.tpl.php";
			}
			if(!is_file($source)) {
				$source = "{$_W['template']['source']}/web/{$_W['account']['template']}/{$filename}.html";
				$compile = "{$_W['template']['compile']}/web/{$_W['account']['template']}/{$filename}.tpl.php";
			}
			if(!is_file($source)) {
				$source = "{$_W['template']['source']}/web/default/{$filename}.html";
				$compile = "{$_W['template']['compile']}/web/default/{$filename}.tpl.php";
			}
		}
		if(!is_file($source)) {
			exit("Error: template source '{$filename}' is not exist!");
		}

		if (DEVELOPMENT || !is_file($compile) || filemtime($source) > filemtime($compile)) {
			template_compile($source, $compile, true);
		}
		return $compile;
	}
}
