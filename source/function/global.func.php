<?php
/**
 * 公共函数
 *
 * [WDL] Copyright (c) 2013 B2CTUI.COM
 */
defined('IN_IA') or exit('Access Denied');

function ver_compare($version1, $version2) {
	if(strlen($version1) <> strlen($version2)) {
		$version1_tmp = explode('.', $version1);
		$version2_tmp = explode('.', $version2);
		if(strlen($version1_tmp[1]) == 1) {
			$version1 .= '0';
		}
		if(strlen($version2_tmp[1]) == 1) {
			$version2 .= '0';
		}
	}
	return version_compare($version1, $version2);
}

/**
 * 转义引号字符串
 * 支持单个字符与数组
 *
 * @param string or array $var
 * @return string or array
 *			 返回转义后的字符串或是数组
 */
function istripslashes($var) {
	if (is_array($var)) {
		foreach ($var as $key => $value) {
			$var[stripslashes($key)] = istripslashes($value);
		}
	} else {
		$var = stripslashes($var);
	}
	return $var;
}

/**
 * 转义字符串的HTML
 * @param string or array $var
 * @return string or array
 *			 返回转义后的字符串或是数组
 */
function ihtmlspecialchars($var) {
	if (is_array($var)) {
		foreach ($var as $key => $value) {
			$var[htmlspecialchars($key)] = ihtmlspecialchars($value);
		}
	} else {
		$var = str_replace('&amp;', '&', htmlspecialchars($var, ENT_QUOTES));
	}
	return $var;
}

/**
 * 写入cookie值
 * @param string $key
 *			 cookie名称
 * @param string $value
 *			 cookie值
 * @param int $maxage
 *			 cookie的生命周期,当前时间开始的$maxage秒
 * @return boolean
 */
function isetcookie($key, $value, $maxage = 0) {
	global $_W;
	$expire = $maxage != 0 ? time() + $maxage : 0;
	return setcookie($_W['config']['cookie']['pre'] . $key, $value, $expire, $_W['config']['cookie']['path'], $_W['config']['cookie']['domain']);
}

/**
 * 获取客户ip
 * @return string
 *			 返回IP地址
 *			 如果未获取到返回unknown
 */
function getip() {
	static $ip = '';
	$ip = $_SERVER['REMOTE_ADDR'];
	if(isset($_SERVER['HTTP_CDN_SRC_IP'])) {
		$ip = $_SERVER['HTTP_CDN_SRC_IP'];
	} elseif (isset($_SERVER['HTTP_CLIENT_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_CLIENT_IP'])) {
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	} elseif(isset($_SERVER['HTTP_X_FORWARDED_FOR']) AND preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $_SERVER['HTTP_X_FORWARDED_FOR'], $matches)) {
		foreach ($matches[0] AS $xip) {
			if (!preg_match('#^(10|172\.16|192\.168)\.#', $xip)) {
				$ip = $xip;
				break;
			}
		}
	}
	return $ip;
}

/**
 * 消息提示窗
 * @param string $msg
 * 提示消息内容
 *
 * @param string $redirect
 * 跳转地址
 *
 * @param string $type 提示类型
 * 		success		成功
 * 		error		错误
 * 		question	询问(问号)
 * 		attention	注意(叹号)
 * 		tips		提示(灯泡)
 * 		ajax		json
 */
function message($msg, $redirect = '', $type = '') {
	global $_W;
	if ($type == 'auth') {
		checkauth();
		exit;
	}
	if($redirect == 'refresh') {
		$redirect = $_W['script_name'] . '?' . $_SERVER['QUERY_STRING'];
	}
	if($redirect == '') {
		$type = in_array($type, array('success', 'error', 'tips', 'ajax', 'sql')) ? $type : 'error';
	} else {
		$type = in_array($type, array('success', 'error', 'tips', 'ajax', 'sql')) ? $type : 'success';
	}
	if($_W['isajax'] || $type == 'ajax') {
		$vars = array();
		$vars['message'] = $msg;
		$vars['redirect'] = $redirect;
		$vars['type'] = $type;
		exit(json_encode($vars));
	}
	if (defined('IN_MOBILE')) {
		$message = "<script type=\"text/javascript\">alert('$msg');";
		$redirect && $message .= "location.href = \"{$redirect}\";";
		$message .= "</script>";
		include template('message', TEMPLATE_INCLUDEPATH);
		exit();
	}
	if (empty($msg) && !empty($redirect)) {
		header('Location: '.$redirect);
	}
	include template('common/message', TEMPLATE_INCLUDEPATH);
	exit();
}

/**
 * 生成token
 */
function token($specialadd = '') {
	global $_W;
	$hashadd = defined('IN_MANAGEMENT') ? 'for management' : '';
	return substr(md5($_W['config']['setting']['authkey'] . $hashadd . $specialadd), 8, 8);
}

function random($length, $numeric = 0) {
	$seed = base_convert(md5(microtime().$_SERVER['DOCUMENT_ROOT']), 16, $numeric ? 10 : 35);
	$seed = $numeric ? (str_replace('0', '', $seed).'012340567890') : ($seed.'zZ'.strtoupper($seed));
	if($numeric) {
		$hash = '';
	} else {
		$hash = chr(rand(1, 26) + rand(0, 1) * 32 + 64);
		$length--;
	}
	$max = strlen($seed) - 1;
	for($i = 0; $i < $length; $i++) {
		$hash .= $seed{mt_rand(0, $max)};
	}
	return $hash;
}

/**
 * 运行钩子
 * @param string $name 钩子名称
 * @param mixed $context 传递给钩子函数的上下文数据，引用传递
 * @return void
 */
function hooks($name, &$context = null) {

}

/**
 * 提交来源检查
 */
function checksubmit($var = 'submit', $allowget = 0) {
	global $_W, $_GPC;
	if (empty($_GPC[$var])) {
		return FALSE;
	}
	if ($allowget || (($_W['ispost'] && !empty($_W['token']) && $_W['token'] == $_GPC['token']) && (empty($_SERVER['HTTP_REFERER']) || preg_replace("/https?:\/\/([^\:\/]+).*/i", "\\1", $_SERVER['HTTP_REFERER']) == preg_replace("/([^\:]+).*/", "\\1", $_SERVER['HTTP_HOST'])))) {
		return TRUE;
	}
	return FALSE;
}

/**
 * 检查是否登录
 * @param boolean $redirect 是否自动跳转登录
 * @return boolean
 */
function checklogin() {
	global $_W;
	if (empty($_W['uid'])) {
		message('抱歉，您无权进行该操作，请先登录！', create_url('member/login'), 'error');
	}
	return true;
}

function checkaccount() {
	global $_W;
	if (empty($_W['weid']) || empty($_W['account'])) {
		message('请您从“管理公众号”或是从顶部“切换公众号”选择要操作的公众号！', '', 'error');
	}
}

function checkpermission($type, $target) {
	global $_W;
	if (!empty($_W['isfounder']) || empty($target)) {
		return true;
	}
	if ($type == 'wechats') {
		if (is_array($target)) {
			$account = $target;
		} else {
			$account = pdo_fetch("SELECT uid FROM ".tablename('wechats')." WHERE weid = '$target' LIMIT 1");
		}
		if ($account['uid'] != $_W['uid']) {
			return false;
		}
	}
	return true;
}

function checkauth($redirect = true) {
	global $_W, $_GPC;
	if (empty($_W['fans']['from_user'])) {
		if ($redirect) {
			$site = $GLOBALS['site'];
			$account = $GLOBALS['_W']['account'];
			$rid = intval($_GPC['rid']);
			if (!empty($rid)) {
				$keywords = pdo_fetchall("SELECT content FROM ".tablename('rule_keyword')." WHERE rid = '{$rid}'");
			}
			if (!empty($GLOBALS['entry'])) {
				$rule = pdo_fetch("SELECT rid FROM ".tablename('cover_reply')." WHERE module = '{$GLOBALS['entry']['module']}' AND do = '{$GLOBALS['entry']['do']}' AND weid = '{$account['weid']}'");
				$keywords = pdo_fetchall("SELECT content FROM ".tablename('rule_keyword')." WHERE rid = '{$rule['rid']}'");
			}
			include template('auth', TEMPLATE_INCLUDEPATH);
		} else {
			message('非法访问，请重新点击链接进入个人中心！');
		}
		exit;
	}
}

/**
 * 返回完整数据表名(加前缀)
 * @param string $table
 * @return string
 */
function tablename($table) {
	return "`{$GLOBALS['_W']['config']['db']['tablepre']}{$table}`";
}

function router($controller, $action) {
	$controllerfile = IA_ROOT . '/source/controller/' . ($controller ? $controller . '/' : '') . $action . '.ctrl.php';
	if (file_exists($controllerfile)) {
		return $controllerfile;
	} else {
		trigger_error('Invalid Controller "'.$action.'"', E_USER_ERROR);
		return '';
	}
}

function model($model) {
	$file = IA_ROOT . '/source/model/' . $model . '.mod.php';
	if (file_exists($file)) {
		return $file;
	} else {
		trigger_error('Invalid Model ' . $model, E_USER_ERROR);
		return '';
	}
}
function func($func) {
	$file = IA_ROOT . '/source/function/' . $func . '.func.php';
	if (file_exists($file)) {
		return $file;
	} else {
		trigger_error('Invalid Function Helper ' . $func, E_USER_ERROR);
		return '';
	}
}

/**
 * 该函数从一个数组中取得若干元素。该函数测试（传入）数组的每个键值是否在（目标）数组中已定义；如果一个键值不存在，该键值所对应的值将被置为FALSE，或者你可以通过传入的第3个参数来指定默认的值。
 * @param array $items 需要筛选的键名定义
 * @param array $array 要进行筛选的数组
 * @param mixed $default 如果原数组未定义的键，则使用此默认值返回
 * @return array
 */
function array_elements($items, $array, $default = FALSE) {
	$return = array();
	if(!is_array($items)) {
		$items = array($items);
	}
	foreach($items as $item) {
		if(isset($array[$item])) {
			$return[$item] = $array[$item];
		} else {
			$return[$item] = $default;
		}
	}
	return $return;
}
/**
 * JSON编码,加上转义操作,适合于JSON入库
 *
 * @param string $value
 */
function ijson_encode($value) {
	if (empty($value)) {
		return false;
	}
	return addcslashes(json_encode($value), "\\\'\"");
}
/**
 * 序列化操作
 *
 * @param string $value
 */
function iserializer($value) {
	return serialize($value);
}
/**
 * 解序列化
 *
 * @param array $value
 */
function iunserializer($value) {
	if (empty($value)) {
		return '';
	}
	if (!is_serialized($value)) {
		return $value;
	}
	$result = unserialize($value);
	if ($result === false) {
		$temp = preg_replace('!s:(\d+):"(.*?)";!se', "'s:'.strlen('$2').':\"$2\";'", $value);
		return unserialize($temp);
	}
	return $result;
}

function is_serialized( $data, $strict = true ) {
	if (!is_string($data)) {
		return false;
	}
	$data = trim($data);
	if ('N;' == $data) {
		return true;
	}
	if (strlen( $data ) < 4) {
		return false;
	}
	if (':' !== $data[1]) {
		return false;
	}
	if ($strict) {
		$lastc = substr($data, -1);
		if (';' !== $lastc && '}' !== $lastc) {
			return false;
		}
	} else {
		$semicolon = strpos($data, ';');
		$brace = strpos($data, '}');
		// Either ; or } must exist.
		if (false === $semicolon && false === $brace)
			return false;
		// But neither must be in the first X characters.
		if (false !== $semicolon && $semicolon < 3)
			return false;
		if (false !== $brace && $brace < 4)
			return false;
	}
	$token = $data[0];
	switch ($token) {
		case 's' :
			if ($strict) {
				if ( '"' !== substr( $data, -2, 1 )) {
					return false;
				}
			} elseif (false === strpos( $data, '"')) {
				return false;
			}
			// or else fall through
		case 'a' :
		case 'O' :
			return (bool) preg_match( "/^{$token}:[0-9]+:/s", $data );
		case 'b' :
		case 'i' :
		case 'd' :
			$end = $strict ? '$' : '';
			return (bool) preg_match( "/^{$token}:[0-9.E-]+;$end/", $data );
	}
	return false;
}

/**
 * 生成分页数据
 * @param int $currentPage 当前页码
 * @param int $totalCount 总记录数
 * @param string $url 要生成的 url 格式，页码占位符请使用 *，如果未写占位符，系统将自动生成
 * @param int $pageSize 分页大小
 * @return string 分页HTML
 */
function pagination($tcount, $pindex, $psize = 15, $url = '', $context = array('before' => 5, 'after' => 4, 'ajaxcallback' => '')) {
	global $_W;
	$pdata = array(
		'tcount' => 0,
		'tpage' => 0,
		'cindex' => 0,
		'findex' => 0,
		'pindex' => 0,
		'nindex' => 0,
		'lindex' => 0,
		'options' => ''
	);
	if($context['ajaxcallback']) {
		$context['isajax'] = true;
	}

	$pdata['tcount'] = $tcount;
	$pdata['tpage'] = ceil($tcount / $psize);
	if($pdata['tpage'] <= 1) {
		return '';
	}
	$cindex = $pindex;
	$cindex = min($cindex, $pdata['tpage']);
	$cindex = max($cindex, 1);
	$pdata['cindex'] = $cindex;
	$pdata['findex'] = 1;
	$pdata['pindex'] = $cindex > 1 ? $cindex - 1 : 1;
	$pdata['nindex'] = $cindex < $pdata['tpage'] ? $cindex + 1 : $pdata['tpage'];
	$pdata['lindex'] = $pdata['tpage'];

	if($context['isajax']) {
		if(!$url) {
			$url = $_W['script_name'] . '?' . http_build_query($_GET);
		}
		$pdata['faa'] = 'href="javascript:;" onclick="p(\'' . $_W['script_name'] . $url . '\', \'' . $pdata['findex'] . '\', ' . $context['ajaxcallback'] . ')"';
		$pdata['paa'] = 'href="javascript:;" onclick="p(\'' . $_W['script_name'] . $url . '\', \'' . $pdata['pindex'] . '\', ' . $context['ajaxcallback'] . ')"';
		$pdata['naa'] = 'href="javascript:;" onclick="p(\'' . $_W['script_name'] . $url . '\', \'' . $pdata['nindex'] . '\', ' . $context['ajaxcallback'] . ')"';
		$pdata['laa'] = 'href="javascript:;" onclick="p(\'' . $_W['script_name'] . $url . '\', \'' . $pdata['lindex'] . '\', ' . $context['ajaxcallback'] . ')"';
	} else {
		if($url) {
			$pdata['faa'] = 'href="?' . str_replace('*', $pdata['findex'], $url) . '"';
			$pdata['paa'] = 'href="?' . str_replace('*', $pdata['pindex'], $url) . '"';
			$pdata['naa'] = 'href="?' . str_replace('*', $pdata['nindex'], $url) . '"';
			$pdata['laa'] = 'href="?' . str_replace('*', $pdata['lindex'], $url) . '"';
		} else {
			$_GET['page'] = $pdata['findex'];
			$pdata['faa'] = 'href="' . $_W['script_name'] . '?' . http_build_query($_GET) . '"';
			$_GET['page'] = $pdata['pindex'];
			$pdata['paa'] = 'href="' . $_W['script_name'] . '?' . http_build_query($_GET) . '"';
			$_GET['page'] = $pdata['nindex'];
			$pdata['naa'] = 'href="' . $_W['script_name'] . '?' . http_build_query($_GET) . '"';
			$_GET['page'] = $pdata['lindex'];
			$pdata['laa'] = 'href="' . $_W['script_name'] . '?' . http_build_query($_GET) . '"';
		}
	}

	$html = '<div class="pagination pagination-centered"><ul class="pagination pagination-centered">';
	if($pdata['cindex'] > 1) {
		$html .= "<li><a {$pdata['faa']} class=\"pager-nav\">首页</a></li>";
		$html .= "<li><a {$pdata['paa']} class=\"pager-nav\">&laquo;上一页</a></li>";
	}
	//页码算法：前5后4，不足10位补齐
	if(!$context['before'] && $context['before'] != 0) {
		$context['before'] = 5;
	}
	if(!$context['after'] && $context['after'] != 0) {
		$context['after'] = 4;
	}

	if($context['after'] != 0 && $context['before'] != 0) {
		$range = array();
		$range['start'] = max(1, $pdata['cindex'] - $context['before']);
		$range['end'] = min($pdata['tpage'], $pdata['cindex'] + $context['after']);
		if ($range['end'] - $range['start'] < $context['before'] + $context['after']) {
			$range['end'] = min($pdata['tpage'], $range['start'] + $context['before'] + $context['after']);
			$range['start'] = max(1, $range['end'] - $context['before'] - $context['after']);
		}
		for ($i = $range['start']; $i <= $range['end']; $i++) {
			if($context['isajax']) {
				$aa = 'href="javascript:;" onclick="p(\'' . $_W['script_name'] . $url . '\', \'' . $i . '\', ' . $context['ajaxcallback'] . ')"';
			} else {
				if($url) {
					$aa = 'href="?' . str_replace('*', $i, $url) . '"';
				} else {
					$_GET['page'] = $i;
					$aa = 'href="?' . http_build_query($_GET) . '"';
				}
			}
			$html .= ($i == $pdata['cindex'] ? '<li class="active"><a href="javascript:;">' . $i . '</a></li>' : "<li><a {$aa}>" . $i . '</a></li>');
		}
	}

	if($pdata['cindex'] < $pdata['tpage']) {
		$html .= "<li><a {$pdata['naa']} class=\"pager-nav\">下一页&raquo;</a></li>";
		$html .= "<li><a {$pdata['laa']} class=\"pager-nav\">尾页</a></li>";
	}
	$html .= '</ul></div>';
	return $html;
}

function toimage($src) {
	global $_W;
	if (empty($src)) {
		return '';
	}
	$t = strtolower($src);
	if (substr($t, 0, 6) == 'avatar') {
		return $_W['siteroot'] . "resource/image/avatar/" . $src;
	}
	if (substr($t, 0, 8) == './themes') {
		return $_W['siteroot'] . $src;
	}
	if (substr($t, 0, 1) == '.') {
		return $_W['siteroot'] . substr($src, 2);
	}
	if(!strexists($t, 'http://') && !strexists($t, 'https://')) {
		$src = $_W['attachurl'] . '/'.$src;
	}
	return $src;
}

/**
 * 构造错误数组
 *
 * @param int $errno 错误码，0为无任何错误。
 * @param string $message 错误信息，通知上层应用具体错误信息。
 * @return array
 */
function error($code, $msg = '') {
	return array(
		'errno' => $code,
		'message' => $msg,
	);
}

/**
 * 检测返回值是否产生错误
 *
 * 产生错误则返回true，否则返回false
 *
 * @param mixed $data   待检测的数据
 * @return boolean
 */
function is_error($data) {
	if (empty($data) || !is_array($data) || !array_key_exists('errno', $data) || (array_key_exists('errno', $data) && $data['errno'] == 0)) {
		return false;
	} else {
		return true;
	}
}
/**
 * 生成URL，统一生成方便管理
 * @param string $router
 * @param array $params
 * @return string
 */
function create_url($router, $params = array()) {
	list($module, $controller, $do) = explode('/', $router);
	$queryString = http_build_query($params, '', '&');
	return $module.'.php?act='.$controller . (empty($do) ? '' : '&do='.$do) . '&'. $queryString;
}
/**
 * 获取引用页
 */
function referer($default = '') {
	global $_GPC, $_W;

	$_W['referer'] = !empty($_GPC['referer']) ? $_GPC['referer'] : $_SERVER['HTTP_REFERER'];;
	$_W['referer'] = substr($_W['referer'], -1) == '?' ? substr($_W['referer'], 0, -1) : $_W['referer'];

	if(strpos($_W['referer'], 'member.php?act=login')) {
		$_W['referer'] = $default;
	}
	$_W['referer'] = $_W['referer'];
	$_W['referer'] = str_replace('&amp;', '&', $_W['referer']);
	$reurl = parse_url($_W['referer']);

	if(!empty($reurl['host']) && !in_array($reurl['host'], array($_SERVER['HTTP_HOST'], 'www.'.$_SERVER['HTTP_HOST'])) && !in_array($_SERVER['HTTP_HOST'], array($reurl['host'], 'www.'.$reurl['host']))) {
		$_W['referer'] = $_W['siteroot'];
	} elseif(empty($reurl['host'])) {
		$_W['referer'] = $_W['siteroot'].'./'.$_W['referer'];
	}
	return strip_tags($_W['referer']);
}

/**
 * 是否包含子串
 */

function strexists($string, $find) {
	return !(strpos($string, $find) === FALSE);
}
/**
 * 兼容其它平台环境
 * @param string $func 方法名
 * @param string $platform 平台名
 * @return boolean|string
 */
function platform($func = '', $platform = '') {
	global $_W;
	$platform = empty($platform) ? $_W['platform'] : $platform;
	$func = $func . $platform;
	if (empty($func) || empty($platform)) {
		return FALSE;
	}
	if (!function_exists($func)) {
		$file = IA_ROOT . '/source/function/'.$platform.'.func.php';
		if (!file_exists($file)) {
			return FALSE;
		}
		include_once $file;
	}
	if (!function_exists($func)) {
		return FALSE;
	}
	return $func;
}

function cutstr($string, $length, $havedot=0, $charset='') {
	global $_W;
	if(empty($charset)) {
		$charset = $_W['charset'];
	}
	if(strtolower($charset) == 'gbk') {
		$charset = 'gbk';
	} else {
		$charset = 'utf8';
	}
	if(istrlen($string, $charset) <= $length) {
		return $string;
	}
	if(function_exists('mb_strcut')) {
		$string = mb_substr($string, 0, $length, $charset);
	} else {
		$pre = '{%';
		$end = '%}';
		$string = str_replace(array('&amp;', '&quot;', '&lt;', '&gt;'), array($pre.'&'.$end, $pre.'"'.$end, $pre.'<'.$end, $pre.'>'.$end), $string);

		$strcut = '';
		$strlen = strlen($string);

		if($charset == 'utf8') {
			$n = $tn = $noc = 0;
			while($n < $strlen) {
				$t = ord($string[$n]);
				if($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
					$tn = 1; $n++; $noc++;
				} elseif(194 <= $t && $t <= 223) {
					$tn = 2; $n += 2; $noc++;
				} elseif(224 <= $t && $t <= 239) {
					$tn = 3; $n += 3; $noc++;
				} elseif(240 <= $t && $t <= 247) {
					$tn = 4; $n += 4; $noc++;
				} elseif(248 <= $t && $t <= 251) {
					$tn = 5; $n += 5; $noc++;
				} elseif($t == 252 || $t == 253) {
					$tn = 6; $n += 6; $noc++;
				} else {
					$n++;
				}
				if($noc >= $length) {
					break;
				}
			}
			if($noc > $length) {
				$n -= $tn;
			}
			$strcut = substr($string, 0, $n);
		} else {
			while($n < $strlen) {
				$t = ord($string[$n]);
				if($t > 127) {
					$tn = 2; $n += 2; $noc++;
				} else {
					$tn = 1; $n++; $noc++;
				}
				if($noc >= $length) {
					break;
				}
			}
			if($noc > $length) {
				$n -= $tn;
			}
			$strcut = substr($string, 0, $n);
		}
		$string = str_replace(array($pre.'&'.$end, $pre.'"'.$end, $pre.'<'.$end, $pre.'>'.$end), array('&amp;', '&quot;', '&lt;', '&gt;'), $strcut);
	}

	if($havedot) {
		$string = $string . "...";
	}

	return $string;
}

function istrlen($string, $charset='') {
	global $_W;
	if(empty($charset)) {
		$charset = $_W['charset'];
	}
	if(strtolower($charset) == 'gbk') {
		$charset = 'gbk';
	} else {
		$charset = 'utf8';
	}
	if(function_exists('mb_strlen')) {
		return mb_strlen($string, $charset);
	} else {
		$n = $noc = 0;
		$strlen = strlen($string);

		if($charset == 'utf8') {

			while($n < $strlen) {
				$t = ord($string[$n]);
				if($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
					$n++; $noc++;
				} elseif(194 <= $t && $t <= 223) {
					$n += 2; $noc++;
				} elseif(224 <= $t && $t <= 239) {
					$n += 3; $noc++;
				} elseif(240 <= $t && $t <= 247) {
					$n += 4; $noc++;
				} elseif(248 <= $t && $t <= 251) {
					$n += 5; $noc++;
				} elseif($t == 252 || $t == 253) {
					$n += 6; $noc++;
				} else {
					$n++;
				}
			}

		} else {

			while($n < $strlen) {
				$t = ord($string[$n]);
				if($t>127) {
					$n += 2; $noc++;
				} else {
					$n++; $noc++;
				}
			}

		}

		return $noc;
	}
}

function emotion($message = '', $size = '24px') {
	$emotions = array(
		"/::)","/::~","/::B","/::|","/:8-)","/::<","/::$","/::X","/::Z","/::'(",
		"/::-|","/::@","/::P","/::D","/::O","/::(","/::+","/:--b","/::Q","/::T",
		"/:,@P","/:,@-D","/::d","/:,@o","/::g","/:|-)","/::!","/::L","/::>","/::,@",
		"/:,@f","/::-S","/:?","/:,@x","/:,@@","/::8","/:,@!","/:!!!","/:xx","/:bye",
		"/:wipe","/:dig","/:handclap","/:&-(","/:B-)","/:<@","/:@>","/::-O","/:>-|",
		"/:P-(","/::'|","/:X-)","/::*","/:@x","/:8*","/:pd","/:<W>","/:beer","/:basketb",
		"/:oo","/:coffee","/:eat","/:pig","/:rose","/:fade","/:showlove","/:heart",
		"/:break","/:cake","/:li","/:bome","/:kn","/:footb","/:ladybug","/:shit","/:moon",
		"/:sun","/:gift","/:hug","/:strong","/:weak","/:share","/:v","/:@)","/:jj","/:@@",
		"/:bad","/:lvu","/:no","/:ok","/:love","/:<L>","/:jump","/:shake","/:<O>","/:circle",
		"/:kotow","/:turn","/:skip","/:oY","/:#-0","/:hiphot","/:kiss","/:<&","/:&>"
	);
	foreach ($emotions as $index => $emotion) {
		$message = str_replace($emotion, '<img style="width:'.$size.';vertical-align:middle;" src="http://res.mail.qq.com/zh_CN/images/mo/DEFAULT2/'.$index.'.gif" />', $message);
	}
	return $message;
}

function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {
	$ckey_length = 4;
	$key = md5($key != '' ? $key : $GLOBALS['_W']['config']['setting']['authkey']);
	$keya = md5(substr($key, 0, 16));
	$keyb = md5(substr($key, 16, 16));
	$keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';

	$cryptkey = $keya.md5($keya.$keyc);
	$key_length = strlen($cryptkey);

	$string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
	$string_length = strlen($string);

	$result = '';
	$box = range(0, 255);

	$rndkey = array();
	for($i = 0; $i <= 255; $i++) {
		$rndkey[$i] = ord($cryptkey[$i % $key_length]);
	}

	for($j = $i = 0; $i < 256; $i++) {
		$j = ($j + $box[$i] + $rndkey[$i]) % 256;
		$tmp = $box[$i];
		$box[$i] = $box[$j];
		$box[$j] = $tmp;
	}

	for($a = $j = $i = 0; $i < $string_length; $i++) {
		$a = ($a + 1) % 256;
		$j = ($j + $box[$a]) % 256;
		$tmp = $box[$a];
		$box[$a] = $box[$j];
		$box[$j] = $tmp;
		$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
	}

	if($operation == 'DECODE') {
		if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
			return substr($result, 26);
		} else {
			return '';
		}
	} else {
		return $keyc.str_replace('=', '', base64_encode($result));
	}

}

function sizecount($size) {
	if($size >= 1073741824) {
		$size = round($size / 1073741824 * 100) / 100 . ' GB';
	} elseif($size >= 1048576) {
		$size = round($size / 1048576 * 100) / 100 . ' MB';
	} elseif($size >= 1024) {
		$size = round($size / 1024 * 100) / 100 . ' KB';
	} else {
		$size = $size . ' Bytes';
	}
	return $size;
}

/**
 * 将一个数组转换为 XML 结构的字符串
 * @param array $arr 要转换的数组
 * @param int $level 节点层级, 1 为 Root.
 * @return string XML 结构的字符串
 */
function array2xml($arr, $level = 1) {
	$s = $level == 1 ? "<xml>" : '';
	foreach($arr as $tagname => $value) {
		if (is_numeric($tagname)) {
			$tagname = $value['TagName'];
			unset($value['TagName']);
		}
		if(!is_array($value)) {
			$s .= "<{$tagname}>".(!is_numeric($value) ? '<![CDATA[' : '').$value.(!is_numeric($value) ? ']]>' : '')."</{$tagname}>";
		} else {
			$s .= "<{$tagname}>" . array2xml($value, $level + 1)."</{$tagname}>";
		}
	}
	$s = preg_replace("/([\x01-\x08\x0b-\x0c\x0e-\x1f])+/", ' ', $s);
	return $level == 1 ? $s."</xml>" : $s;
}


function utf8_bytes($cp){

	if ($cp > 0x10000){
		# 4 bytes
		return	chr(0xF0 | (($cp & 0x1C0000) >> 18)).
		chr(0x80 | (($cp & 0x3F000) >> 12)).
		chr(0x80 | (($cp & 0xFC0) >> 6)).
		chr(0x80 | ($cp & 0x3F));
	}else if ($cp > 0x800){
		# 3 bytes
		return	chr(0xE0 | (($cp & 0xF000) >> 12)).
		chr(0x80 | (($cp & 0xFC0) >> 6)).
		chr(0x80 | ($cp & 0x3F));
	}else if ($cp > 0x80){
		# 2 bytes
		return	chr(0xC0 | (($cp & 0x7C0) >> 6)).
		chr(0x80 | ($cp & 0x3F));
	}else{
		# 1 byte
		return chr($cp);
	}
}
