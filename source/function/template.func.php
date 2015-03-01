<?php
/**
 * 模板操作
 * 
 * [WDL] Copyright (c) 2013 B2CTUI.COM
 */
defined('IN_IA') or exit('Access Denied');

/**
 * 导入全局变量，并直接显示模板页内容。
 * @var int
 */
define('TEMPLATE_DISPLAY', 0);

/**
 * 导入全局变量，并返回模板页内容的字符串
 * @var int
*/
define('TEMPLATE_FETCH', 1);

/**
 * 返回模板编译文件的包含路径
 * @var int
*/
define('TEMPLATE_INCLUDEPATH', 2);

function template($filename, $flag = TEMPLATE_DISPLAY) {
	global $_W;
	$paths = explode('/', $filename);
	if (defined('IN_MOBILE')) {
		$source = "{$_W['template']['source']}/mobile/{$_W['account']['template']}/".implode('/', $paths).".html";
		$compile = "{$_W['template']['compile']}/mobile/{$_W['account']['template']}/".implode('/', $paths).".tpl.php";
		if(!is_file($source)) {
			$source = "{$_W['template']['source']}/mobile/default/".implode('/', $paths).".html";
			$compile = "{$_W['template']['compile']}/mobile/default/".implode('/', $paths).".tpl.php";
		}
	} else {
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
		template_compile($source, $compile);
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

function template_compile($from, $to, $instance = false) {
	$path = dirname($to);
	if (!is_dir($path))
		mkdirs($path);
	$content = template_parse(file_get_contents($from), $instance);
	if(IMS_FAMILY == 'x' && !preg_match('/(footer|header|frame)+/', $from)) $content = str_replace('微动力', '系统', $content);
	file_put_contents($to, $content);
}

function template_parse($str, $instance = false) {
	$str = preg_replace('/<!--{(.+?)}-->/s', '{$1}', $str);
	if($instance) {
		$str = preg_replace('/{template\s+(.+?)}/', '<?php include $this->template($1, TEMPLATE_INCLUDEPATH);?>', $str);
	} else {
		$str = preg_replace('/{template\s+(.+?)}/', '<?php include template($1, TEMPLATE_INCLUDEPATH);?>', $str);
	}
	$str = preg_replace('/{php\s+(.+?)}/', '<?php $1?>', $str);
	$str = preg_replace('/{if\s+(.+?)}/', '<?php if($1) { ?>', $str);
	$str = preg_replace('/{else}/', '<?php } else { ?>', $str);
	$str = preg_replace('/{else ?if\s+(.+?)}/', '<?php } else if($1) { ?>', $str);
	$str = preg_replace('/{\/if}/', '<?php } ?>', $str);
	$str = preg_replace('/{loop\s+(\S+)\s+(\S+)}/', '<?php if(is_array($1)) { foreach($1 as $2) { ?>', $str);
	$str = preg_replace('/{loop\s+(\S+)\s+(\S+)\s+(\S+)}/', '<?php if(is_array($1)) { foreach($1 as $2 => $3) { ?>', $str);
	$str = preg_replace('/{\/loop}/', '<?php } } ?>', $str);
	$str = preg_replace('/{(\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)}/', '<?php echo $1;?>', $str);
	$str = preg_replace('/{(\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff\[\]\'\"\$]*)}/', '<?php echo $1;?>', $str);
	$str = preg_replace_callback('/{data\s+(.+?)}/s', "moduledata", $str);
	$str = preg_replace('/{\/data}/', '<?php } } ?>', $str);
	$str = preg_replace_callback('/<\?php([^\?]+)\?>/s', "template_addquote", $str);
	$str = preg_replace('/{([A-Z_\x7f-\xff][A-Z0-9_\x7f-\xff]*)}/s', '<?php echo $1;?>', $str);
	$str = str_replace('{##', '{', $str);
	$str = str_replace('##}', '}', $str);
	$str = "<?php defined('IN_IA') or exit('Access Denied');?>" . $str;
	return $str;
}
function template_addquote($matchs) {
	$code = "<?php {$matchs[1]}?>";
	$code = preg_replace('/\[([a-zA-Z0-9_\-\.\x7f-\xff]+)\](?![a-zA-Z0-9_\-\.\x7f-\xff\[\]]*[\'"])/s', "['$1']", $code);
	return str_replace('\\\"', '\"', $code);
}

/**
 * 
 * 此处变量为系统定义+自定义，系统定义在下方列出，如果模块有特殊参数需要
 * 则可在标签上自定义参数，在对应的func中使用即可。
 * 
 * func - 指定获取数据的函数，此函数定义在模块目录下的model.php文件中
 * module - 指定获取数据的模块。
 * assign - 指定该标签得到数据后，存入的变量名称。如果为空则存在与func同名的变量中，方便在下方的代码中使用。
 * item - 指定循环体内的迭代时的变量名。相当于`foreach ($foo as $i => $row)` 中 $row变量。
 * limit - 指定获取变量时条数。
 * return - 为true时，获取到数据后直接循环输出，为false时，获取到数据后作为变量返回。
 * 
 * @return string
 */
function moduledata($params = '') {
	if (empty($params[1])) {
		return '';
	}
	$params = explode(' ', $params[1]);
	if (empty($params)) {
		return '';
	}
	$data = array();
	foreach ($params as $row) {
		$row = explode('=', $row);
		$data[$row[0]] = str_replace(array("'", '"'), '', $row[1]);
	}
	
	$funcname = $data['func'];
	$assign = !empty($data['assign']) ? $data['assign'] : $funcname;
	$item = !empty($data['item']) ? $data['item'] : 'row';
	$data['limit'] = !empty($data['limit']) ? $data['limit'] : 1;
	if (empty($data['return']) || $data['return'] == 'false') {
		$return = false;
	} else {
		$return = true;
	}
	
	if (!empty($data['module'])) {
		$modulename = $data['module'];
		unset($data['module']);
	} else {
		list($modulename) = explode('_', $data['func']);
	}
	if (empty($modulename) || empty($funcname)) {
		return '';
	}
	$variable = var_export($data, true);
	$variable = preg_replace("/'(\\$[a-zA-Z_\x7f-\xff]*?)'/", '$1', $variable);
	$php = "<?php \${$assign} = modulefunc('$modulename', '{$funcname}', {$variable}); ";
	if (empty($return)) {
		$php .= "if(is_array(\${$assign})) { foreach(\${$assign} as \$i => \${$item}) { ";
	}
	$php .= "?>";
	return $php;
}

function modulefunc($modulename, $funcname, $params) {
	static $includes;
	
	$includefile = '';
	if (!function_exists($funcname)) {
		if (!isset($includes[$modulename])) {
			if (!file_exists(IA_ROOT . '/source/modules/'.$modulename.'/model.php')) {
				return '';
			} else {
				$includes[$modulename] = true;
				include_once IA_ROOT . '/source/modules/'.$modulename.'/model.php';
			}
		}
	}
	
	if (function_exists($funcname)) {
		return call_user_func_array($funcname, array($params));
	} else {
		return array();
	}
}
