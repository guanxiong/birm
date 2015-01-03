<?php
error_reporting(0);
@set_time_limit(0);
@set_magic_quotes_runtime(0);
ob_start();
define('IA_ROOT', str_replace("\\",'/', dirname(__FILE__)));
if($_GET['res']) {
	$res = $_GET['res'];
	$reses = tpl_resources();
	if(array_key_exists($res, $reses)) {
		if($res == 'css') {
			header('content-type:text/css');
		} else {
			header('content-type:image/png');
		}
		echo base64_decode($reses[$res]);
		exit();
	}
}

$actions = array('license', 'env', 'db', 'finish');
$action = $_COOKIE['action'];
$action = in_array($action, $actions) ? $action : 'license';
$ispost = strtolower($_SERVER['REQUEST_METHOD']) == 'post';

if(file_exists(IA_ROOT . '/data/install.lock') && $action != 'finish') {
	header('location: ./index.php');
}
header('content-type: text/html; charset=utf-8');
if($action == 'license') {
	if($ispost) {
		setcookie('action', 'env');
		header('location: ?refresh');
	}
	tpl_install_license();
}
if($action == 'env') {
	if($ispost) {
		setcookie('action', isset($_POST['continue']) ? 'db' : 'license');
		header('location: ?refresh');
	}
	$result = array();
	$result['env_os'] = PHP_OS;
	$result['env_version'] = PHP_VERSION;
	$result['env_server'] = $_SERVER['SERVER_SOFTWARE'];
	$result['env_pathroot'] = IA_ROOT;
	$result['env_uploadsize'] = @ini_get('file_uploads') ? ini_get('upload_max_filesize') : 'unknow';
	if(function_exists('disk_free_space')) {
		$result['env_diskspace'] = floor(disk_free_space(IA_ROOT) / (1024*1024)).'M';
	} else {
		$result['env_diskspace'] = 'unknow';
	}
	$tmp = function_exists('gd_info') ? gd_info() : array();
	$result['env_gd'] = empty($tmp['GD Version']) ? 'noext' : $tmp['GD Version'];
	
	$chk_func = array(
		array('method' => 'ini_get',			'name' => 'allow_url_fopen'), 
		array('method' => 'function_exists',	'name' => 'mysql_connect'),
		array('method' => 'function_exists',	'name' => 'file_get_contents'),
		array('method' => 'function_exists',	'name' => 'fsockopen'),
		array('method' => 'function_exists',	'name' => 'xml_parser_create'),
		array('method' => 'extension_loaded',	'name' => 'pdo_mysql'),
		array('method' => 'function_exists',	'name' => 'curl_init')
	);
	$result['iscontinue'] = true;
	foreach ($chk_func as $func) {
		$check[$func['name']] = $func['method']($func['name']) ? true : false;
		$result[$func['name']] = $func['method']($func['name']) ? '<font color=green>[√]On</font>' : '<font color=red>[×]Off</font>';
	}
	if (!empty($check['mysql_connect']) || !empty($check['pdo_mysql'])) {
		$result['iscontinue'] = true;
	} else {
		$result['iscontinue'] = false;
	}
	unset($check['mysql_connect']);unset($check['pdo_mysql']);
	
	foreach ($check as $condition) {
		if (empty($condition)) {
			$result['iscontinue'] = false;
		}
	}
	
	$result['chk_dir'] = array(
		'/',
	);
	foreach ($result['chk_dir'] as $dir) {
		if(!local_writeable(IA_ROOT . $dir)) {
			$result['chk_'.md5($dir)] = '<font color=red>[×]不可写</font>';
			$result['iscontinue'] = false;
		} else {
			$result['chk_'.md5($dir)] = '<font color=green>[√]可写</font>';
		}
	}
	tpl_install_check_env($result);
}
if($action == 'db') {
	if($ispost) {
		if(isset($_POST['back'])) {
			setcookie('action', 'env');
			header('location: ?refresh');
			exit();
		}

		$error_msg = '';
		
		$family = $_POST['family'] == 'x' ? 'x' : 'v';
		$dbhost = $_POST['dbhost'];
		$dbuser = $_POST['dbuser'];
		$dbpwd = $_POST['dbpwd'];
		$dbname = $_POST['dbname'];
		$dbprefix = $_POST['dbprefix'];
		$adminuser = $_POST['adminuser'];
		$adminpwd = $_POST['adminpwd'];
		$cookiepre = local_salt(4).'_';
		$authkey = local_salt(16).'_';
		$link = mysql_connect($dbhost, $dbuser, $dbpwd);
		if(empty($link)) {
			$error = mysql_error();
			$error_msg = "$error <br />";
		} else {
			mysql_query("SET character_set_connection=utf8, character_set_results=utf8, character_set_client=binary");
			mysql_query("SET sql_mode=''");
			if(mysql_errno()) {
				$error_msg = mysql_error() ." <br />";
			} else {
				$query = mysql_query("SHOW DATABASES LIKE  '{$dbname}';");
				if (!mysql_fetch_assoc($query)) {
					if(mysql_get_server_info() > '4.1') {
						mysql_query("CREATE DATABASE IF NOT EXISTS `{$dbname}` DEFAULT CHARACTER SET utf8", $link);
					} else {
						mysql_query("CREATE DATABASE IF NOT EXISTS `{$dbname}`", $link);
					}
				}
				$query = mysql_query("SHOW DATABASES LIKE  '{$dbname}';");
				if (!mysql_fetch_assoc($query)) {
					$error_msg .= "数据库不存在且创建数据库失败. <br />";
				}
				if(mysql_errno()) {
					$error_msg .= mysql_error() . " <br />";
				}
			}
		}
		if (empty($error_msg)) {
			//写入配置信息
			$dbport = explode(':', $dbhost);
			$dbport = !empty($dbport[1]) ? $dbport[1] : '3306';
			$config = local_config();
			$config = str_replace(array(
				'{dbhost}', '{dbuser}', '{dbpwd}', '{dbport}', '{dbname}', '{dbtablepre}', '{cookiepre}', '{authkey}', '{attachdir}'
			), array(
				"'$dbhost'", "'$dbuser'", "'$dbpwd'", "'$dbport'", $dbname, $dbprefix, $cookiepre, $authkey, 'resource/attachment/'
			), $config);

			mysql_select_db($dbname);
			$query = mysql_query("SHOW TABLES LIKE '{$dbprefix}%';");
			if (mysql_fetch_assoc($query)) {
				die('<script type="text/javascript">alert("您的数据库不为空，请重新建立数据库或是清空该数据库！");history.back();</script>');
			}
			if($_POST['install'] == 'remote') {
				$ins = remote_install();
				if(empty($ins) || !is_array($ins)) {
					die('<script type="text/javascript">alert("连接不到服务器, 请稍后重试！");history.back();</script>');
				}
				if($ins['error']) {
					die('<script type="text/javascript">alert("链接微新星更新服务器失败, 错误为: ' . $ins['error'] . '！");history.back();</script>');
				}
				if(empty($ins['scripts']) || !is_array($ins['scripts'])) {
					die('<script type="text/javascript">alert("此服务器未授权, 不能安装！");history.back();</script>');
				}
				$archive = remote_download($ins['attachments']);
				if(!$archive) {
					die('<script type="text/javascript">alert("未能下载程序包, 请确认你的安装程序目录有写入权限. 多次安装失败, 请访问论坛获取解决方案！");history.back();</script>');				
				}
				$fp = fopen($archive, 'r');
				if ($fp) {
					$buffer = '';
					while (!feof($fp)) {
						$buffer .= fgets($fp, 4096);
						if($buffer[strlen($buffer) - 1] == "\n") {
							$pieces = explode(':', $buffer);
							$path = base64_decode($pieces[0]);
							$dat = base64_decode($pieces[1]);
							$fname = IA_ROOT . $path;
							local_mkdirs(dirname($fname));
							file_put_contents($fname, $dat);
							$buffer = '';
						}
					}
					fclose($fp);
				}
				unlink($archive);
				mysql_close($link);
				$link = mysql_connect($dbhost, $dbuser, $dbpwd);
				mysql_query("SET character_set_connection=utf8, character_set_results=utf8, character_set_client=binary");
				mysql_query("SET sql_mode=''");
				mysql_select_db($dbname);

				$version = '';
				$release = '';
				foreach($ins['scripts'] as $sch) {
					$version = $sch['version'];
					$release = $sch['release'];
					runquery($sch['content']);
				}
				$verfile = IA_ROOT . '/source/version.inc.php';
				$verdat = <<<VER
<?php
/**
 * 版本号
 * 
 * [WNS]Copyright (c) 2013 BIRM.CO
 */

defined('IN_IA') or exit('Access Denied');

define('IMS_FAMILY', '{$ins['family']}');
define('IMS_VERSION', '{$version}');
define('IMS_RELEASE_DATE', '{$release}');
VER;
				file_put_contents($verfile, $verdat);
			} else {
				$verfile = IA_ROOT . '/source/version.inc.php';
				if(file_exists(IA_ROOT . '/index.php') && file_exists(IA_ROOT . '/setting.php') && file_exists($verfile)) {
					$sql = file_get_contents(IA_ROOT . '/data/install.sql');
					if(empty($sql)) {
						die('<script type="text/javascript">alert("安装包不正确, 数据安装脚本缺失.");history.back();</script>');
					}
					runquery($sql);
				} else {
					die('<script type="text/javascript">alert("你正在使用本地安装, 但未下载完整安装包, 请从微新星官网下载完整安装包后重试.");history.back();</script>');
				}
			}

			$salt = local_salt(8);
			$password = sha1("{$adminpwd}-{$salt}-{$authkey}");
			mysql_query("INSERT INTO {$dbprefix}members (username, password, salt, joindate) VALUES('{$adminuser}', '$password', '$salt', '".time()."')");
			$uid = mysql_insert_id();
			//新建默认公众号
			$wechat = array(
				'hash' => local_salt(5),
				'type' => '1',
				'uid' => $uid,
				'token' => local_salt(32),
				'access_token' => '',
				'name' => '默认公众号',
				'account' => '默认公众号',
				'original' => '',
				'signature' => '',
				'country' => '',
				'province' => '',
				'city' => '',
				'username' => '',
				'password' => '',
				'welcome' => '欢迎信息',
				'default' => '默认回复',
				'default_period' => '0',
				'lastupdate' => '',
				'key' => '',
				'secret' => '',
				'styleid' => '1',
			);
			mysql_query("INSERT INTO `{$dbprefix}wechats` (`".implode("`,`", array_keys($wechat))."`) VALUES ('".implode("','", $wechat)."')");
			local_mkdirs(IA_ROOT . '/data');
			file_put_contents(IA_ROOT . '/data/config.php', $config);
			touch(IA_ROOT . '/data/install.lock');
			setcookie('action', 'finish');
			header('location: ?refresh');
			exit();
		}
	}
	tpl_install_db($error_msg);
	
}
if($action == 'finish') {
	setcookie('action', '', -10);
	@unlink(IA_ROOT . '/data/install.sql');
	define('IN_SYS', true);
	require_once IA_ROOT . '/source/bootstrap.inc.php';
	require_once IA_ROOT . '/source/model/setting.mod.php';
	$_W['uid'] = $_W['isfounder'] = 1;
	cache_build_setting();
	cache_build_announcement();
	cache_build_modules();
	cache_build_fans_struct();
	cache_build_hook();
	tpl_install_finish();
}

function local_writeable($dir) {
	$writeable = 0;
	if(!is_dir($dir)) {
		@mkdir($dir, 0777);
	}
	if(is_dir($dir)) {
		if($fp = fopen("$dir/test.txt", 'w')) {
			fclose($fp);
			unlink("$dir/test.txt");
			$writeable = 1;
		} else {
			$writeable = 0;
		}
	}
	return $writeable;
}

function local_salt($length = 8) {
	$result = '';
	while(strlen($result) < $length) {
		$result .= sha1(uniqid('', true));
	}
	return substr($result, 0, $length);
}

function local_config() {
	$cfg = <<<EOF
<?php
defined('IN_IA') or exit('Access Denied');

\$config = array();

\$config['db']['host'] = {dbhost};
\$config['db']['username'] = {dbuser};
\$config['db']['password'] = {dbpwd};
\$config['db']['port'] = {dbport};
\$config['db']['database'] = '{dbname}';
\$config['db']['charset'] = 'utf8';
\$config['db']['pconnect'] = 0;
\$config['db']['tablepre'] = '{dbtablepre}';

// --------------------------  CONFIG COOKIE  --------------------------- //
\$config['cookie']['pre'] = '{cookiepre}';
\$config['cookie']['domain'] = '';
\$config['cookie']['path'] = '/';

// --------------------------  CONFIG SETTING  --------------------------- //
\$config['setting']['charset'] = 'utf-8';
\$config['setting']['cache'] = 'mysql';
\$config['setting']['timezone'] = 'Asia/Shanghai';
\$config['setting']['memory_limit'] = '256M';
\$config['setting']['filemode'] = 0644;
\$config['setting']['authkey'] = '{authkey}';
\$config['setting']['founder'] = '1';
\$config['setting']['development'] = 0;
\$config['setting']['referrer'] = 0;
\$confi['setting']['copyright']['sitename'] = "微新星";


// --------------------------  CONFIG UPLOAD  --------------------------- //
\$config['upload']['image']['extentions'] = array('gif', 'jpg', 'jpeg', 'png');
\$config['upload']['image']['limit'] = 5000;
\$config['upload']['attachdir'] = '{attachdir}';

EOF;
	return trim($cfg);
}

function local_mkdirs($path) {   
	if(!is_dir($path)) {
		local_mkdirs(dirname($path));
		mkdir($path);   
	}   
	return is_dir($path);   
}

function runquery($sql) {
	global $link, $dbprefix;

	if(!isset($sql) || empty($sql)) return;

	$sql = str_replace("\r", "\n", str_replace(' ims_', ' '.$dbprefix, $sql));
	$sql = str_replace("\r", "\n", str_replace(' `ims_', ' `'.$dbprefix, $sql));
	$ret = array();
	$num = 0;
	foreach(explode(";\n", trim($sql)) as $query) {
		$ret[$num] = '';
		$queries = explode("\n", trim($query));
		foreach($queries as $query) {
			$ret[$num] .= (isset($query[0]) && $query[0] == '#') || (isset($query[1]) && isset($query[1]) && $query[0].$query[1] == '--') ? '' : $query;
		}
		$num++;
	}
	unset($sql);
	foreach($ret as $query) {
		$query = trim($query);
		if($query) {
			mysql_query($query, $link);
		}
	}
}

function __remote_install_headers($ch = '', $header = '') {
    static $hash;
    if(!empty($header)) {
        $pieces = explode(':', $header);
        if(trim($pieces[0]) == 'hash') {
            $hash = trim($pieces[1]);
        }
    }
    if($ch == '' && $header == '') {
        return $hash;
    }
    return strlen($header);
}

function remote_install() {
	global $family;
	$token = '';
	$pars = array();
	$pars['host'] = $_SERVER['HTTP_HOST'];
	$pars['version'] = '';
	$pars['release'] = '';
	$url = 'http://addons.we7.cc/gateway.php';
	$ch = curl_init($url); 
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $pars);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HEADERFUNCTION, '__remote_install_headers');
	$content = curl_exec($ch);
	curl_close($ch);
	$sign = __remote_install_headers();
	$ret = array(); 
	if($content) {
		$obj = simplexml_load_string($content, 'SimpleXMLElement', LIBXML_NOCDATA);
		$ret['version'] = strval($obj->version);
		$ret['release'] = strval($obj->release);
		$ret['family'] = strval($obj->family);
		$ret['announcement'] = strval($obj->announcement);
		$ret['error'] = strval($obj->error);
        if(empty($ret['error']) && $sign == md5($content . $token)) {
            if($obj->scripts) {
                $ret['scripts'] = array();
                foreach($obj->scripts->script as $schema) {
                    $attr = $schema->attributes();
                    $v = strval($attr['version']);
                    $r = strval($attr['release']);
                    $c = strval($schema);
                    $ret['scripts'][] = array(
                        'version' => $v,
                        'release' => $r,
                        'content' => $c
                    );
                }
            }
            if($obj->attachments) {
                $ret['attachments'] = array();
                foreach($obj->attachments->file as $file) {
                    $attr = $file->attributes();
                    $path = strval($attr['path']);
                    $sum = strval($attr['checksum']);
                    $entry = IA_ROOT . $path;
                    if(!is_file($entry) || md5_file($entry) != $sum) {
                        $ret['attachments'][] = $path;
                    }
                }
            }
        }
	}
	return $ret;
}

function __remote_download_headers($ch = '', $header = '') {
    static $hash;
    if(!empty($header)) {
        $pieces = explode(':', $header);
        if(trim($pieces[0]) == 'hash') {
            $hash = trim($pieces[1]);
        }
    }
    if($ch == '' && $header == '') {
        return $hash;
    }
    return strlen($header);
}

function remote_download($archive) {
	$pars = array();
	$pars['host'] = $_SERVER['HTTP_HOST'];
	$pars['version'] = '';
	$pars['release'] = '';
	$pars['archive'] = base64_encode(json_encode($archive));
	$url = 'http://addons.we7.cc/gateway.php';
	$tmpfile = IA_ROOT . '/we7.zip';
	$fp = fopen($tmpfile, 'w+');
	if(!$fp) {
		return false;
	}
	$ch = curl_init($url); 
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $pars);
	curl_setopt($ch, CURLOPT_FILE, $fp);
	curl_setopt($ch, CURLOPT_HEADERFUNCTION, '__remote_download_headers');
	if(!curl_exec($ch)) {
		return false;
	}
	curl_close($ch);
	fclose($fp);
	$sign = __remote_download_headers();
	if(md5_file($tmpfile) == $sign) {
		return $tmpfile;
	}
	return false;
}

function tpl_frame() {
	global $action, $actions;
	$steps = array('许可协议', '环境检测', '参数配置', '安装完成');

	$contents = ob_get_contents();
	ob_clean();
	echo <<<EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="x-ua-compatible" content="ie=7" />
<title>微新星 - 微信公众平台自助开源引擎</title>
<link href="./resource/weidongli/css/install.css" rel="stylesheet" type="text/css" />

</head>
<body>
<div class="header">
	<div class="top">
		<div class="top-logo">
		</div>
		<div class="top-link">
			<ul>
				<li><a href="http://www.birm.co" target="_blank">微新星官方网站</a></li>
				<li><a href="http://bbs.birm.co" target="_blank">微新星官方论坛</a></li>
			</ul>
		</div>
	</div>
</div>
<div class="main">
	<div class="pleft">
		<dl class="setpbox t1">
			<dt>安装步骤</dt>
			<dd>
				<ul>
EOF;
	foreach ($steps as $index => $value) {
		$classname = $index == array_search($action, $actions) ? 'now' : 'succeed';
		echo '<li class="'.$classname.'">'.$value.'</li>';
	}
	echo <<<EOF
				</ul>
			</dd>
		</dl>
	</div>
	<div class="pright">
		{$contents}
	</div>
</div>

<div class="foot">
</div>
</body>
</html>
EOF;
}

function tpl_install_license() {
	echo <<<EOF
		<div class="pr-title"><h3>阅读许可协议</h3></div>
		<div class="pr-agreement">
				<p>版权所有 (c)2013，微新星团队保留所有权利。 </p>
				<p>感谢您选择微新星 - 微信公众平台自助开源引擎（以下简称WQ，WQ基于 PHP + MySQL的技术开发，全部源码开放。</p>
				<p>为了使你正确并合法的使用本软件，请你在使用前务必阅读清楚下面的协议条款：</p>
			<strong>一、本授权协议适用且仅适用于WQ任何版本，WQ官方对本授权协议的最终解释权。</strong>
			<strong>二、协议许可的权利 </strong>
				<p>1、您可以在完全遵守本最终用户授权协议的基础上，将本软件应用于非商业用途，而不必支付软件版权授权费用。 </p>
				<p>2、您可以在协议规定的约束和限制范围内修改 微新星 源代码或界面风格以适应您的网站要求。 </p>
				<p>3、您拥有使用本软件构建的网站全部内容所有权，并独立承担与这些内容的相关法律义务。 </p>
				<p>4、获得商业授权之后，您可以将本软件应用于商业用途，同时依据所购买的授权类型中确定的技术支持内容，自购买时刻起，在技术支持期限内拥有通过指定的方式获得指定范围内的技术支持服务。商业授权用户享有反映和提出意见的权力，相关意见将被作为首要考虑，但没有一定被采纳的承诺或保证。 </p>
			<strong>二、协议规定的约束和限制 </strong>
				<p>1、未获商业授权之前，不得将本软件用于商业用途（包括但不限于企业网站、经营性网站、以营利为目的或实现盈利的网站）。</p>
				<p>2、未经官方许可，不得对本软件或与之关联的商业授权进行出租、出售、抵押或发放子许可证。</p></p>
				<p>4、未经官方许可，禁止在 微新星 的整体或任何部分基础上以发展任何派生版本、修改版本或第三方版本用于重新分发。</p>
				<p>5、如果您未能遵守本协议的条款，您的授权将被终止，所被许可的权利将被收回，并承担相应法律责任。 </p>
			<strong>三、有限担保和免责声明 </strong>
				<p>1、本软件及所附带的文件是作为不提供任何明确的或隐含的赔偿或担保的形式提供的。 </p>
				<p>2、用户出于自愿而使用本软件，您必须了解使用本软件的风险，在尚未购买产品技术服务之前，我们不承诺对免费用户提供任何形式的技术支持、使用担保，也不承担任何因使用本软件而产生问题的相关责任。 </p>
				<p>3、电子文本形式的授权协议如同双方书面签署的协议一样，具有完全的和等同的法律效力。您一旦开始确认本协议并安装微新星，即被视为完全理解并接受本协议的各项条款，在享有上述条款授予的权力的同时，受到相关的约束和限制。协议许可范围以外的行为，将直接违反本授权协议并构成侵权，我们有权随时终止授权，责令停止损害，并保留追究相关责任的权力。</p>
				<p>4、如果本软件带有其它软件的整合API示范例子包，这些文件版权不属于本软件官方，并且这些文件是没经过授权发布的，请参考相关软件的使用许可合法的使用。</p>
		</div>
		<div class="btn-box">
			<form action="" method="post" onSubmit="return document.getElementById('readpact').checked ? true : (!!alert('您必须同意软件许可协议才能安装！') && false);">
				<input type="checkbox" id="readpact" value="" /><label for="readpact"><strong class="fc-690 fs-14">我已经阅读并同意此协议</strong></label>
				<input type="submit" class="btn-next" value="继续" />
			</form>
		</div>
	</div>
</div>
EOF;
	tpl_frame();
}

function tpl_install_check_env($result = array()) {
	extract($result);
	$chk_dir_html = '';
	foreach ($chk_dir as $dir) {
		$status = 'chk_'.md5($dir);
		$chk_dir_html .= '
		 <tr>
			<td>'.$dir.'</td>
			<td><font color=green>[√]可写</font></td>
			<td>'.$$status.'</td>
		</tr>';	
	}
echo <<<EOF
<div class="pr-title"><h3>服务器信息</h3></div>
<table width="726" border="0" align="center" cellpadding="0" cellspacing="0" class="twbox">
	<tr>
		<th width="300" align="center"><strong>参数</strong></th>
		<th width="424"><strong>值</strong></th>
	</tr>
	<tr>
		<td><strong>服务器操作系统</strong></td>
		<td>{$env_os}</td>
	</tr>
	<tr>
		<td><strong>服务器解译引擎</strong></td>
		<td>{$env_server}</td>
	</tr>
	<tr>
		<td><strong>PHP版本</strong></td>
		<td>{$env_version}</td>
	</tr>
	<tr>
		<td><strong>系统安装目录</strong></td>
		<td>{$env_pathroot}</td>
	</tr>
	<tr>
		<td><strong>磁盘空间</strong></td>
		<td>{$env_diskspace}</td>
	</tr>
	<tr>
		<td><strong>附件上传</strong></td>
		<td>{$env_uploadsize}</td>
	</tr>
	<tr>
		<td><strong>GD 库</strong></td>
		<td>{$env_gd}</td>
	</tr>
</table>
<div class="pr-title"><h3>程序依赖性检查</h3></div>
<table width="726" border="0" align="center" cellpadding="0" cellspacing="0" class="twbox">
	<tr>
		<th width="200" align="center"><strong>需开启的变量或函数</strong></th>
		<th width="80"><strong>要求</strong></th>
		<th width="400"><strong>实际状态及建议</strong></th>
	</tr>
	<tr>
		<td>mysql_connect()</td>
		<td align="center">On </td>
		<td>{$mysql_connect} <small>(mysql_connect必须支持)</small></td>
	</tr>
	<tr>
		<td>PDO</td>
		<td align="center">On </td>
		<td>{$pdo_mysql} <small>(pdo必须支持)</small></td>
	</tr>
	<tr>
		<td>allow_url_fopen</td>
		<td align="center">On </td>
		<td>{$allow_url_fopen} <small></small></td>
	</tr>
	<tr>
		<td>file_get_contents()</td>
		<td align="center">On</td>
		<td>{$file_get_contents} <small></small></td>
	</tr>
	<tr>
		<td>xml_parser_create()</td>
		<td align="center">On</td>
		<td>{$xml_parser_create} <small></small></td>
	</tr>
	<tr>
		<td>fsockopen()</td>
		<td align="center">On</td>
		<td>{$fsockopen} <small></small></td>
	</tr>
	<tr>
		<td>curl</td>
		<td align="center">On</td>
		<td>{$curl_init} <small></small></td>
	</tr>
</table>
<div class="notice">系统环境要求必须满足下列所有条件，否则系统或系统部份功能将无法使用。</div>

<div class="pr-title"><h3>目录权限检测</h3></div>
<table width="726" border="0" align="center" cellpadding="0" cellspacing="0" class="twbox">
	<tr>
		<th width="300" align="center"><strong>目录名</strong></th>
		<th width="212"><strong>所需状态</strong></th>
		<th width="212"><strong>当前状态</strong></th>
	</tr>
	{$chk_dir_html}
</table>
<div class="notice">
	系统要求微新星整个安装目录必须可写, 才能使用微新星所有功能.
</div>
<div class="btn-box">
	<form action="" method="post">
	<input type="submit" name="back" class="btn-back" value="" />
EOF;

if (!empty($iscontinue)) {
	echo <<<EOF
	<input type="submit" name="continue" class="btn-next" value="" />
	</form>
</div>
EOF;
} else {
	echo <<<EOF
	<font color=red>请先解决环境依赖，以便继续进行安装</font>
</div>
EOF;
}
	tpl_frame();
}

function tpl_install_db($error_msg = '') {
	if (!empty($error_msg)) {
		echo <<<EOF
		<div class="pr-title"><h3>错误信息</h3></div>
		<table width="726" border="0" align="center" cellpadding="0" cellspacing="0" class="twbox">
			<tr>
				<td><div style="color:red;">{$error_msg}</div></td>
			</tr>
		</table>	
EOF;
	}
	$insTypes = array();
	if(file_exists(IA_ROOT . '/index.php') && file_exists(IA_ROOT . '/setting.php')) {
		$insTypes['local'] = ' checked="checked"';
	} else {
		$insTypes['remote'] = ' checked="checked"';
	}
	$disabled = empty($insTypes['local']) ? ' disabled="disabled"' : '';
	echo <<<EOF
	<form action="" method="post" onSubmit="return check(this)">
	<div class="pr-title"><h3>安装选项</h3></div>
	<table width="726" border="0" align="center" cellpadding="0" cellspacing="0" class="twbox">
		<tr>
			<td class="onetd"><strong>请选择安装方式：</strong></td>
			<td>
				
				<input name="install" type="radio" value="local" {$insTypes['local']}{$disabled}/> 离线安装 
				<p><small>在线安装能够直接安装最新版本微新星系统, 如果在线安装困难, 请下载离线安装包后使用本地安装.</small></p>
				<p><small>离线安装包可能不是最新程序, 如果你不确定, 可以现在访问官网重新下载一份最新的.^_^</small></p>
			</td>
		</tr>
	</table>
	<div class="pr-title"><h3>数据库设定</h3></div>
	<table width="726" border="0" align="center" cellpadding="0" cellspacing="0" class="twbox">
		<tr>
			<td class="onetd"><strong>数据库主机：</strong></td>
			<td><input name="dbhost" id="dbhost" type="text" value="localhost" class="input-txt" />
			<small>一般为localhost</small></td>
		</tr>
		<tr>
			<td class="onetd"><strong>数据库用户：</strong></td>
			<td><input name="dbuser" id="dbuser" type="text" value="root" class="input-txt" /></td>
		</tr>
		<tr>
			<td class="onetd"><strong>数据库密码：</strong></td>
			<td>
			  <input name="dbpwd" id="dbpwd" type="text" class="input-txt" />
			</td>
		</tr>
		<tr>
			<td class="onetd"><strong>数据表前缀：</strong></td>
			<td><input name="dbprefix" id="dbprefix" type="text" value="ims_" class="input-txt" />
			<small>如无特殊需要,请不要修改</small></td>
		</tr>
		<tr>
			<td class="onetd"><strong>数据库名称：</strong></td>
			<td>
				<input name="dbname" id="dbname" type="text" value="b2ctui" class="input-txt" />
			</td>
		</tr>
	</table>

	<div class="pr-title"><h3>管理员初始密码</h3></div>
	<table width="726" border="0" align="center" cellpadding="0" cellspacing="0" class="twbox">
		<tr>
			<td class="onetd"><strong>管理员帐号：</strong></td>
			<td>
				<input name="adminuser" type="text" value="admin" class="input-txt" />
				<p><small>只能用'0-9'、'a-z'、'A-Z'、'.'、'@'、'_'、'-'、'!'以内范围的字符</small></p>
			</td>
		</tr>
		<tr>
			<td class="onetd"><strong>管理员密码：</strong></td>
			<td><input name="adminpwd" type="password" value="" class="input-txt" /></td>
		</tr>
		<tr>
			<td class="onetd"><strong>确认密码：</strong></td>
			<td><input name="confirmpwd" type="password" value="" class="input-txt" /></td>
		</tr>
	</table>

	<div class="btn-box">
		<input type="submit" name="back" class="btn-back" value="" onClick="window.chk = false;" />
		<input type="submit" id="install" name="continue" class="btn-next" value="" onClick="window.chk = true;" />
  </div>
	</form>
	<script type="text/javascript">
	function check(form) {
		if(!window.chk) {
			return true;
		}
		if (!form['dbhost'].value) {
			alert('请填写数据库主机地址！');
			form['dbhost'].focus();
			form['install'].disabled = '';
			return false;
		}
		if (!form['dbuser'].value) {
			alert('请填写数据库用户！');
			form['dbuser'].focus();
			form['install'].disabled = '';
			return false;
		}
		if (!form['dbpwd'].value) {
			alert('请填写数据库密码！');
			form['dbpwd'].focus();
			form['install'].disabled = '';
			return false;
		}
		if (!form['dbprefix'].value) {
			alert('请填写数据库表前缀！');
			form['dbprefix'].focus();
			form['install'].disabled = '';
			return false;
		}
		if (!form['dbname'].value) {
			alert('请填写数据库名称！');
			form['dbname'].focus();
			form['install'].disabled = '';
			return false;
		}
		if (!form['adminuser'].value) {
			alert('请填写管理员帐号！');
			form['adminuser'].focus();
			form['install'].disabled = '';
			return false;
		}
		if (!form['adminpwd'].value) {
			alert('请填写管理员帐号密码！');
			form['adminpwd'].focus();
			form['install'].disabled = '';
			return false;
		}
		if (form['adminpwd'].value != form['confirmpwd'].value) {
			alert('您两次输入的管理员帐号密码不相同，请检查！');
			form['adminpwd'].focus();
			form['install'].disabled = '';
			return false;
		}
		document.getElementById('install').disabled = 'disabled';
		return true;
	}
	</script>
EOF;
	tpl_frame();
}

function tpl_install_finish() {
	echo <<<EOF
<div class="pr-title"><h3>安装完成</h3></div>
<div class="install-msg">
	恭喜您!已成功安装“微新星 - 微信公众平台自助开源引擎”系统，您现在可以:
	<br />
</div>
<div class="over-link fs-14">
	<a href="./index.php">访问网站首页</a>
</div>
EOF;
	tpl_frame();
}

function tpl_resources() {
	static $res = array(
		'css' => 'KnttYXJnaW46MDtwYWRkaW5nOjA7fQ0KYm9keXttYXJnaW46MDsgcGFkZGluZzowOyBmb250LXNpemU6MTRweDtsaW5lLWhlaWdodDoxLjY7Zm9udC1mYW1pbHk6Is6iyO3RxbraIixIZWx2ZXRpY2EsIsvOzOUiLEFyaWFsLFRhaG9tYTtiYWNrZ3JvdW5kOiNGOUY5Rjk7fQ0KdWx7bGlzdC1zdHlsZTpub25lO30NCmF7Y29sb3I6IzA2Qzt9DQphOmhvdmVye2NvbG9yOiM0NzhmY2U7dGV4dC1kZWNvcmF0aW9uOm5vbmU7fQ0KLmZjLTY5MHtjb2xvcjojNDc4ZmNlO30NCi5mcy0xNHtmb250LXNpemU6MTRweDt9DQouaGVhZGVye2JhY2tncm91bmQ6IzQ3OEZDRTsgYm9yZGVyLWJvdHRvbToycHggIzg0QkNFMiBzb2xpZDt9DQoudG9we3dpZHRoOjkwMHB4O2hlaWdodDo4NXB4O292ZXJmbG93OmhpZGRlbjttYXJnaW46MCBhdXRvO30NCi50b3AgLnRvcC1sb2dve3dpZHRoOjQ1MHB4O2hlaWdodDo4MHB4O2Zsb2F0OmxlZnQ7cGFkZGluZy1sZWZ0OjEwcHg7b3ZlcmZsb3c6aGlkZGVuOyBiYWNrZ3JvdW5kOiB1cmwoP3Jlcz1sb2dvKSBuby1yZXBlYXQgbGVmdCBjZW50ZXJ9DQoudG9wIC50b3AtbG9nbyBoMXtmb250LXNpemU6MDtsaW5lLWhlaWdodDoxMDAwJTt9DQoudG9wIC50b3AtbGlua3tvdmVyZmxvdzpoaWRkZW47fQ0KLnRvcCAudG9wLWxpbmsgdWx7ZmxvYXQ6cmlnaHQ7b3ZlcmZsb3c6aGlkZGVuOyBtYXJnaW4tdG9wOjM1cHg7fQ0KLnRvcCAudG9wLWxpbmsgdWwgbGl7ZmxvYXQ6bGVmdDsgbWFyZ2luLWxlZnQ6MTBweDt9DQoudG9wIC50b3AtbGluayB1bCBsaSBhe2Rpc3BsYXk6aW5saW5lLWJsb2NrO3RleHQtZGVjb3JhdGlvbjpub25lO2NvbG9yOiNGRkY7IGZvbnQtc2l6ZToxNHB4OyBwYWRkaW5nOjhweCAyMHB4OyBiYWNrZ3JvdW5kOiM4NEJDRTI7fQ0KLm1haW57d2lkdGg6OTAwcHg7bWFyZ2luOjE0cHggYXV0byAwO30NCi5tYWluIC5wbGVmdHt3aWR0aDoxNjhweDtmbG9hdDpsZWZ0O2N1cnNvcjpkZWZhdWx0O3BhZGRpbmctdG9wOjZweDt9DQoubWFpbiAucHJpZ2h0e3dpZHRoOjcyMHB4O2Zsb2F0OnJpZ2h0O30NCi5wci10aXRsZXt3aWR0aDo3MjBweDtoZWlnaHQ6MjJweDtvdmVyZmxvdzpoaWRkZW47Ym9yZGVyLWJvdHRvbTozcHggIzg0YmNlMiBzb2xpZDttYXJnaW46OHB4IGF1dG8gMDt9DQoucHItdGl0bGUgaDN7d2lkdGg6MTU4cHg7aGVpZ2h0OjI1cHg7bGluZS1oZWlnaHQ6MjVweDtvdmVyZmxvdzpoaWRkZW47ZGlzcGxheTpibG9jaztmb250LXNpemU6MTJweDt0ZXh0LWluZGVudDoxMHB4O2JhY2tncm91bmQ6Izg0YmNlMjtsZXR0ZXItc3BhY2luZzoycHg7Y29sb3I6I0ZGRjtmb250LXdlaWdodDo3MDA7fQ0KLnQxe3dpZHRoOjE2MnB4O30NCi50MSBkdHt3aWR0aDoxNjJweDtoZWlnaHQ6MzVweDtsaW5lLWhlaWdodDozNXB4O2JhY2tncm91bmQ6IzQ3OGZjZTtmb250LXdlaWdodDo3MDA7Y29sb3I6I0ZGRjt0ZXh0LWluZGVudDoyNXB4O2xldHRlci1zcGFjaW5nOjJweDtmb250LXNpemU6MTRweDt9DQoudDEgZGR7d2lkdGg6MTU4cHg7YmFja2dyb3VuZC1jb2xvcjojRkFGREY5O2JvcmRlcjoycHggc29saWQgIzQ3OGZjZTt9DQoudDEgZGQgdWx7Ym9yZGVyOjFweCBzb2xpZCAjRkZGO292ZXJmbG93OmhpZGRlbjtiYWNrZ3JvdW5kOnVybCg/cmVzPXN0ZXAtaWNvLWJnKSAyMHB4IDIxcHggbm8tcmVwZWF0O3BhZGRpbmc6MTBweCAwO30NCi50MSBkZCB1bCBsaXtoZWlnaHQ6NDBweDtsaW5lLWhlaWdodDozNnB4O3RleHQtaW5kZW50OjUycHg7ZGlzcGxheTpibG9jaztjb2xvcjojODg4O2ZvbnQtc2l6ZToxNHB4O30NCi50MSBkZCB1bCBsaS5zdWNjZWVke2NvbG9yOiM0NzhmY2U7YmFja2dyb3VuZDp1cmwoP3Jlcz1pY28tc3RlcC1zdWNjZWVkKSAyM3B4IDE0cHggbm8tcmVwZWF0O30NCi50MSBkZCB1bCBsaS5ub3d7Y29sb3I6I0Y5MDtmb250LXdlaWdodDo3MDA7YmFja2dyb3VuZDp1cmwoP3Jlcz1pY28tc3RlcC1ub3cpIDIzcHggMTRweCBuby1yZXBlYXQ7fQ0KLmluc3RhbGwtbXNne2NvbG9yOiM3Nzc7bGluZS1oZWlnaHQ6MzFweDtmb250LXNpemU6MTRweDtvdmVyZmxvdzpoaWRkZW47Y2xlYXI6Ym90aDtwYWRkaW5nOjEwcHggMjBweDt9DQoucHItYWdyZWVtZW50e2xpbmUtaGVpZ2h0OjIxcHg7Y29sb3I6IzY2NjtoZWlnaHQ6NDAwcHg7b3ZlcmZsb3cteTpzY3JvbGw7cGFkZGluZzoxNnB4O30NCi5wci1hZ3JlZW1lbnQgc3Ryb25ne2Rpc3BsYXk6YmxvY2s7Y29sb3I6IzMzMztsaW5lLWhlaWdodDoyN3B4O21hcmdpbi10b3A6NnB4O30NCi5wci1hZ3JlZW1lbnQgcHt0ZXh0LWluZGVudDozMHB4O30NCi5idG4tYm94e21hcmdpbi10b3A6MTVweDtib3JkZXItdG9wOjFweCBzb2xpZCAjREREO3ZlcnRpY2FsLWFsaWduOm1pZGRsZTtwYWRkaW5nOjEwcHggNnB4O30NCi5idG4tbmV4dHt3aWR0aDoxMDRweDtoZWlnaHQ6MzRweDtib3JkZXI6bm9uZTtiYWNrZ3JvdW5kOnVybCg/cmVzPWJ1dC1uZXh0KSBuby1yZXBlYXQ7Y3Vyc29yOnBvaW50ZXI7bWFyZ2luLWxlZnQ6MTBweDtvdmVyZmxvdzpoaWRkZW47Zm9udC1zaXplOjA7bGluZS1oZWlnaHQ6MTAwcHg7fQ0KLmJ0bi1iYWNre3dpZHRoOjEwNHB4O2hlaWdodDozNHB4O2JvcmRlcjpub25lO2JhY2tncm91bmQ6dXJsKD9yZXM9YnV0LWJhY2spIG5vLXJlcGVhdDtjdXJzb3I6cG9pbnRlcjtvdmVyZmxvdzpoaWRkZW47Zm9udC1zaXplOjA7bGluZS1oZWlnaHQ6MTAwcHg7fQ0KI3JlYWRwYWN0e21hcmdpbi10b3A6LTRweDttYXJnaW4tcmlnaHQ6NHB4O30NCi5vdmVyLWxpbmt7bGluZS1oZWlnaHQ6NDFweDtvdmVyZmxvdzpoaWRkZW47Y2xlYXI6Ym90aDtwYWRkaW5nOjAgNDBweDt9DQoub3Zlci1saW5rIGF7bGluZS1oZWlnaHQ6MTRweDtiYWNrZ3JvdW5kOiNGQUZBRkE7Y29sb3I6IzMzMztkaXNwbGF5OmJsb2NrO2Zsb2F0OmxlZnQ7bWFyZ2luLXJpZ2h0OjIwcHg7dGV4dC1kZWNvcmF0aW9uOm5vbmU7Ym9yZGVyLWNvbG9yOiNFRUUgI0NDQyAjQ0NDICNFRUU7Ym9yZGVyLXN0eWxlOnNvbGlkO2JvcmRlci13aWR0aDoxcHg7cGFkZGluZzo2cHggMjBweDt9DQoub2xpbmsgYXtsaW5lLWhlaWdodDoxNHB4O2JhY2tncm91bmQ6I0VBRjRERDtjb2xvcjojMzMzO2Rpc3BsYXk6YmxvY2s7bWFyZ2luLWxlZnQ6OHB4O2Zsb2F0OmxlZnQ7d2lkdGg6NTVweDttYXJnaW4tcmlnaHQ6MjBweDt0ZXh0LWRlY29yYXRpb246bm9uZTtib3JkZXItY29sb3I6I0VFRSAjQ0NDICNDQ0MgI0VFRTtib3JkZXItc3R5bGU6c29saWQ7Ym9yZGVyLXdpZHRoOjFweDtwYWRkaW5nOjJweCAyMHB4O30NCi53YWl0cGFnZXt0b3A6MDtsZWZ0OjA7ZmlsdGVyOkFscGhhKG9wYWNpdHk9NzApOy1tb3otb3BhY2l0eTowLjc7cG9zaXRpb246YWJzb2x1dGU7ei1pbmRleDoxMDAwMDtiYWNrZ3JvdW5kOnVybCg/cmVzPWxvYWRpbmcpICNhYmFiYWIgbm8tcmVwZWF0IGNlbnRlciAyMDBweDt3aWR0aDoxMDAlO2hlaWdodDoyNTAwcHg7ZGlzcGxheTpub25lO30NCi5kaXZwcmV7ZmlsdGVyOnByb2dpZDpEWEltYWdlVHJhbnNmb3JtLk1pY3Jvc29mdC5BbHBoYUltYWdlTG9hZGVyKHNpemluZ01ldGhvZD1zY2FsZSk7fQ0KLm1vZHVsZXNlbHt3aWR0aDoxMjBweDttYXJnaW4tcmlnaHQ6OHB4O2Zsb2F0OmxlZnQ7fQ0KLm5vdGljZXtwYWRkaW5nLWxlZnQ6OHB4O2hlaWdodDoyNXB4O2xpbmUtaGVpZ2h0OjI1cHg7b3ZlcmZsb3c6aGlkZGVuO2NvbG9yOnJlZDtiYWNrZ3JvdW5kOiNmZmUyN2M7Ym9yZGVyOjFweCAjZmZjMDAwIGRhc2hlZDttYXJnaW4tdG9wOjNweDt9DQppbnB1dHt2ZXJ0aWNhbC1hbGlnbjptaWRkbGU7bWFyZ2luLXJpZ2h0OjNweDtmb250LXNpemU6MTJweDt9DQp0ZXh0YXJlYXt2ZXJ0aWNhbC1hbGlnbjp0b3A7Zm9udC1zaXplOjEycHg7bGluZS1oZWlnaHQ6MTU2JTtib3JkZXI6MXB4IHNvbGlkICNBQUE7bGV0dGVyLXNwYWNpbmc6MXB4O3dvcmQtYnJlYWs6YnJlYWstYWxsO292ZXJmbG93LXk6YXV0bztwYWRkaW5nOjNweDt9DQouaW5wdXQtdHh0e2JvcmRlcjoxcHggc29saWQgI0FBQTtmb250LXNpemU6MTJweDtjb2xvcjojMDAwO3dpZHRoOjIwMHB4O3BhZGRpbmc6NHB4IDhweCA0cHggNnB4O30NCi50ZXh0aXB0X29ue2JvcmRlcjoxcHggc29saWQgI0Y5MDt9DQpocntoZWlnaHQ6MXB4O2xpbmUtaGVpZ2h0OjFweDtvdmVyZmxvdzpoaWRkZW47Ym9yZGVyLXRvcDoxcHggc29saWQgI0U2RTZFNjtib3JkZXItd2lkdGg6MXB4IDAgMDt9DQpocjplbXB0eXttYXJnaW46OHB4IDAgN3B4IWltcG9ydGFudDt9DQpzbWFsbHtmb250LXNpemU6MTJweDt9DQoubW9uY29sb3IgdGR7YmFja2dyb3VuZDojRkZDO30NCi50d2JveHtib3JkZXI6MXB4IHNvbGlkICM0NzhmY2U7Ym9yZGVyLXRvcDowO3dpZHRoOjcyMHB4O2ZvbnQtc2l6ZToxMnB4O292ZXJmbG93OmhpZGRlbjt9DQoudHdib3ggdGhlYWQgdHIgdGR7YmFja2dyb3VuZDp1cmwoP3Jlcz1ib2R5LXRpdGxlLWJnKSAtMXB4IC0xcHggcmVwZWF0LXg7aGVpZ2h0OjMxcHg7bGluZS1oZWlnaHQ6MzFweDt0ZXh0LWluZGVudDoxMHB4O30NCi50d2JveCB0aGVhZCB0ciB0ZCBzdHJvbmd7bGV0dGVyLXNwYWNpbmc6MnB4O21hcmdpbi1yaWdodDoxNHB4O2NvbG9yOiNGRkY7Zm9udC1zaXplOjE0cHg7fQ0KLnR3Ym94IHRoZWFkIHRyIHRkIHNwYW57Y29sb3I6I0NEQTt9DQoudHdib3ggdGhlYWQgdHIgdGQgcHtoZWlnaHQ6MzFweDtkaXNwbGF5OmlubGluZTtmbG9hdDpyaWdodDtvdmVyZmxvdzpoaWRkZW47bWFyZ2luOi0zMXB4IDEwcHggMCAwO30NCi50d2JveCB0aGVhZCB0ciB0ZCBwICp7ZmxvYXQ6cmlnaHQ7fQ0KLnR3Ym94IHRoZWFkIHRyIHRkIGEudGhsaW5re2NvbG9yOiNGRkY7fQ0KLnR3Ym94IHRoZWFkIHRyIHRkIGEudGhsaW5rOmhvdmVye2NvbG9yOiNGRjA7dGV4dC1kZWNvcmF0aW9uOm5vbmU7fQ0KLnR3Ym94IHRib2R5e292ZXJmbG93OmhpZGRlbjt0ZXh0LWFsaWduOmxlZnQ7fQ0KLnR3Ym94IHRib2R5IHRyIHRoe2JhY2tncm91bmQ6I2RhZWNmOTtjb2xvcjojNDc4ZmNlO2xpbmUtaGVpZ2h0OjIxcHg7aGVpZ2h0OjIxcHg7dGV4dC1pbmRlbnQ6MzBweDtmb250LXdlaWdodDo0MDA7Ym9yZGVyLWJvdHRvbToxcHggc29saWQgIzQ3OGZjZTtsZXR0ZXItc3BhY2luZzoycHg7fQ0KLnR3Ym94IHRib2R5IHRyIHRke2JvcmRlci1ib3R0b206MXB4IHNvbGlkICNGMkYyRjI7Y29sb3I6IzMzMzt2ZXJ0aWNhbC1hbGlnbjp0b3A7cGFkZGluZzo3cHg7fQ0KLnR3Ym94IHRib2R5IHRyIHRkIHB7bGluZS1oZWlnaHQ6MjFweDt9DQoudHdib3ggdGJvZHkgdHIgdGQgaW1ne3ZlcnRpY2FsLWFsaWduOnRvcDttYXJnaW46MCAxMHB4IDVweCAwO30NCi50d2JveCB0Ym9keSB0ciB0ZCBzbWFsbHtjb2xvcjojODg4O30NCi50d2JveCB0Zm9vdCB0ciB0ZHtsaW5lLWhlaWdodDoyNXB4O3RleHQtYWxpZ246Y2VudGVyO3BhZGRpbmc6MTBweDt9DQoudHdib3ggdGZvb3QgdHIgdGQgcHtsaW5lLWhlaWdodDoyMXB4O21hcmdpbi1ib3R0b206MTBweDt9DQppbnB1dC5idXR7aGVpZ2h0OjI2cHg7cGFkZGluZy1sZWZ0OjZweDtwYWRkaW5nLXJpZ2h0OjZweDtsaW5lLWhlaWdodDoyNnB4O2ZvbnQtd2VpZ2h0OjcwMDtsZXR0ZXItc3BhY2luZzoxcHg7Y29sb3I6I0ZGRjtiYWNrZ3JvdW5kLWNvbG9yOiNGQzM7fQ0KLm9uZXRke3dpZHRoOjEyMHB4O3RleHQtYWxpZ246cmlnaHQ7bGluZS1oZWlnaHQ6MjVweDt9DQppbnB1dCxidXR0b24sc2VsZWN0LC50d2JveCB0Ym9keSB0ciB0ZCBwIHN0cm9uZyBpbWd7dmVydGljYWwtYWxpZ246bWlkZGxlO30=',
		'logo' => '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0p8zAAAAIGNIUk0AAHolAACAgwAA+f8AAIDpAAB1MAAA6mAAADqYAAAXb5JfxUYAAAMAUExURQAAAP///0eMy8jP18jR2tni68fP18jQ2Nbd5JCTlvL1+PL09i5chiRIaUaMykWKx0SIxUSHxEOGwkKDvkGCvECAuT9+tj58sz16sDt1qjp0qDlypTdunzZsnDVqmjVqmTRoljRnljFijjFhjS9eiC1agixYfytWfCdOcSZMbkeNzEaLyUWJxkSHw0OFwEOEv0KCvUGBukB/uEB/tz98tD57sj56sT15rjx3rDx2qzpypjlxozhvoTdtnjZrmzVpmDRnlTNlkjJjjy5bgyxXfipTeClRdSNFZCFBXh89WTlwoiZLbEKBuUaGwEyNx1OUz0N2pU2IvUh/sEZ7q1CLwUJynz9umD5qkztmjDdegTRYeF6b0jFQbS9NaFiPwU9+qF6MtUBeeXSp2VZ7njtVbFJ1lWmVvm6cxWiQtGaLrGSHp158l157lV15k1x3kHGSsFt1jVp0i4arzVhwhoKkxJi/4lZrfmyEmqXH52V5jHyUqpezzZ+71HiMn5atwrrU7JOnuZKltpGjs7HG2ZChscXb756tu9Hi8pyos87c6bzI08vX4snU3snT3MjS2+jx+Tt2qjp0pzNmkzJkkDBgiitVekeLx0eMxUSFukqEt1OGs0ZsjVqEqUZmgk9vi4W03WKEomCAnFpziW2LpX2duWl/koqlvIWbrpSqvaO0w4+eq8DR4Ky7yMzZ5MvW38fO1OXs8tXb4Nzq9cfQ13h8f/P4/OTp7eru8ePn6kODrvH09vDz9UaHr0qNtkh4l02QskuMq0yNpjxvflCSpkB1hEB0efX29lWSgEyCb1WETWScS6GioFSAGXChG4nGGozJHJDMH5PPIpbQJI/IJ5bNL4OtMZrGRaTTTavVWJnSJpzVKaDYLKLaLqXcMKjeMqDTO5G+OLXiU7niZMHnbqLAYl1fWa3hNrLlOtjwo6CugKi1ikxTOq6trNbV1f7+/vz8/Pn5+fHx8e7u7uzs7Ovr6+jo6OXl5eTk5OHh4d7e3tzc3Nra2s3NzcPDw7i4uEeNzLIA9S0AAAEAdFJOU////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////wBT9wclAAAm2UlEQVR42uydd3xUVfr/3+femRSYEBSBZGbuTBpqoogoUkRx96skIgoJXbfp7rJrF12kSVGRKhas6Ioo0osBETFgVxARwYL0TKaHBFEhIZnJzL3n98fMpGD5+tXdn7uvVx5ewL333Hva557nPPWOkLTSfzcprVPw306idRW2rsJWal2FrdQKYSsjbaVWCFupFcJW+iVkap2CX5VUiyb84uc+bfPJ2lYIf20EHYqa5Px5GEpvcrY3TbZC+KuSxVGZY05u4/15EKaklpvsnlYIf1XSlIHndNd/9i749dNtdqqtEP66arl6zYhf8PjWi7er1laJ9Fclf+CXIEjfqxVVbYXw112Fzl+qFKr+Vkb660LoA2DssQ7zAFi/jmLWAVA8GMZXA2j3Azzc/n3g0k5X8/z7iXLwKs5WCH9dclYBHNveO3b65Xa6sD1RuH4CQPy86p8A2wcy88EWy7iuFcL/GHqg5tKr44e9Aabe/34c2H0AybGTY+uJX516f1wubZ26/xAavxTfq5/HjrtMAmAeML4aLXHLIoBF6xNMNLEdts7dfwY9/Arsro6fHM/Pv+7VGC99ZTux1Rbevj3BbLd/SSuE/3n0BXTnweON5+fGmOo6KP7fFPzWyfuPoGOH6N1ld2zXg+RByTFWOv4VBg1uhfC/gg5B8eAbth+KnYXTmck5g1k/gd5zgIdDTAJmAqSE4PhM4JzBPwyh+q4JVB2il+mtk/v/iwYOpuuxOISvAMyOsdFFQNUrALwEdL/u86by790L1Xe2f/zx6Znp6e3S0zNP//jj7W+prbP7b6dcuHse3PWXLvSeFL82aDDjtzdjo8mJPTKlsTyuGsqW6y+5rVDaAkgBcNKo1+t/27oW/13UIakqJo/mxgB59fNJxDWLSTATYoA+HGIS67+Mc8+HQ/Fy2PrnjLrmEKpvtu0U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',
		'step-ico-bg' => 'iVBORw0KGgoAAAANSUhEUgAAAA0AAACtCAIAAADklWLFAAAAA3NCSVQICAjb4U/gAAAACXBIWXMAAArwAAAK8AFCrDSYAAAAFnRFWHRDcmVhdGlvbiBUaW1lADEwLzI0LzA4KQ6r+wAAAB90RVh0U29mdHdhcmUATWFjcm9tZWRpYSBGaXJld29ya3MgOLVo0ngAAAF/SURBVFiF7dnNjpswFIbhzwc7/iGBTBTU+7+8UUZMEhJsY2O6IEqrSq3STRfTc5bmETIY6V0gpjkCADDl4Kch5LEshQQZ6exmt5FmvSpWdxlPcQ61brSyArSgxOTv8aor07ru4S7jKZfU1kcBwk+zoFzuH5JU6zo55RDncNh+E6CU4xA+c0mS1M68Kanb+tjf3qccpJ+GWjcruvq+MQetXEj3y/jRuqOSutaNnwYKedTKAhjCuTGHrW4VqZ3eN/YwhDMArWzII5WlrNvKJWnlnpvT0uaSAAhQWQqRoAUFgCQZ0/h0Po2S1Po0JEga6WLyRtU783YeT3PJdrP16X4Ln607AojJG+nELV7O/vS7511Q+tv73navvr9Xz+Nxg9Z1e9tdfZ9T0mRySlff7223oh8OwHrkM+ZYwoz5ufKr+/OwY8fuf3JTDgAqVJpMheq5sg734zHcj7/8rtixY/f1HfeD+wFwP9ixY8f94H5wP9ixY/dvHPfjK/ZDvPh/6zshsOXwcSvgiAAAAABJRU5ErkJggg==',
		'but-back' => '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',
		'but-next' => 'R0lGODlhaAAhAPcAAAAAAP////X5/uv0/erz/PX5/Vmk52iz9mOq6WCl4mKn5Geu7Wmw72Om4muy8Wqx8Gyz8mqv7Gmt6muw7Wqu622y72yx7m+08W6z8Gmq5G6w63S59nO49XO39Ha7+HW38nS28Xa483e06nOt4Xu58Hu473+884C99IO874a/8oO77YC36InC9YS66onA8Ye97o7F9ozC85HH+Iq+7JHH94/D8ZHF85bL+p/P+prJ857N953M9qLN86XP9a3T9azS9LHX+bDW+K/V967U9rbX9cPf+cHd98Hd9r/b9MTg+czj+Mvi99Xo+dTn+Njr/Nfq+9bp+uHv/ODu+9/t+uv0/EePzlGd31Ke4VKe4FOf4VOe4FKd31Wh41Sg4lai5Fik5lej5Vqm6Vml6Fag4Fun6l2p7Fyo616q7WCs71+r7mGt8GOv8mKu8WWx9GSw82ay9Wez9may9GGo5mCn5Wi09mOr6WKq6GKp52m09mSs6mWt62Sp5WOo5Gy29mWq5mSo42ar52it6Ges52mt6Wms5nC18Wuu53G28m2v6Wqr5HK383Cz7nS59XG07nK173i8+He793e69XSy526p3G6p23e1636/+H6/93m37Xq47ny68H688oG/9YG67IC563+46YO87oC56oW+8IK77IfA8oa/8YK56YG454nC9IjB84S764O66YrD9Ya97YW87Ii/74e+7oa97IS66IrB8YnA8I3E9IzD84vC8oa76Y/G9o7F9ZHJ+ZDH95PK+ozB7ou/7IzA7ZTK+Y6/6o/A6pbG75bF7p7P+ZvL9KXQ9aTP9KvR8qrQ8anP8K7U9a3T9KzS87DW96/V9rbX9LXW87rc+bfY9cDd9r/c9cTh+sPg+cLf+M3l+szk+cvj+M7m+9Xo+Njr+9fq+tbp+eDu+uHw/ODv++r0/PX6/vT5/UeNzAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAEAAOkALAAAAABoACEAAAj/AAMEqEKwoMGDCBMqXMiwocOHCwUOHIHjRq+LGDNq3Mixo8ePIEOKxHgDx4gqE3dZeuShpcuXMGPKnEmzps2bOF0+srTrZBVjl/rgoUO0qNGjSJMqXcq0qdOnRfH0uWSMYDBIB+Bo3cq1q9evYMOKHUu2bNcDkIIRlLHhjdu3cOPKnUu3rt27ePPO3SCDIA1GcQILHky4sOHDiBMrXszYMCMaBHlxaEO5suXLmDNr3sy5s+fPmTnwIpirg5vTqFOrXs2aEyfWkYCwnu3G9erYtHPrRt0hF0EYitYIH068uPHj2JwIp6ZjOKsAx6Ov8aa8+HPp2LMPVwSDoK5DbMKL/x9Pvnz5EAFYhAcXhIU3NiwCwAcHwfx49CfC7wDHn1wA/vzlZ9+ABB6iC0G1FKLGggw26OCDD0LzBINPQPNBACagEoAaT0wIYYNJJKEGKg6gAs2JSQRw4okffOjii2oUUgtBtlyAxo045qjjjjqCcE4qD0SxyRNCoLFNNqkEkOQmPOaYyjkgPHBOkTgm2eSVWO54gS0ExYBBGmCGKeaYZI5ZxBNpRHMOA09Ek8YxRZASADJulilmFG5GMwApHXYYRQB9dmjnoISmgUEMBN1SwRmMNuroo5A+OoBEQ5wRzhApZNpDAJmmoEmkjDoiUQA2ODLEqaiiGsU5oLbq6hkV3P9C0CwWlGHrrbjmqmuuJJDQzAALlAFFM6OOCsWut5ZCgjbH2poDN8EOW0Yj5ySD7LXYlmHBLAS5MIEZ4IYr7rjkkrvIOTWAC4UP4VIRQLnwihLAIuEuUs45JaxrBhRQwOvvv+BO4AJBtERAxsEIJ6zwwgsroQTCUDhzsDMCQZEJwwrrYY7EByshsTN6RHywHhiXbPLBEdBC0CsUhOHyyzDHLHPMPARgRDXiYCLOD2FgEsAPAVQjQB4zw9zNzzhjgoIAUhAtDg9FRy11zBS8QhAsEoih9dZcd+1110QIwEQ3z2jAxDN1mEMEKAGIwYQUdXy9tRRiH1G2GHU8o3UAoMj/7fffXUsAC0EvDGLA4YgnrvjijCfOhDTjjGOACgEYgEABKjTOOOXFFqv555oP8gJBrQTyxemop6766qyfbgciTChjjR1fjBKA662zjkglo/Q+yhIBjOOL77kXn3sgrRAUiyBgNO/889BHDz0wynxDQADVK+N8JwFIH/0dxFSPDvbOExMAMdaL4P367DsvSCwEuQKIF/TXb//9+N8/xTfT/GKIF9+4hicGWIxvDHCA+aOfHNDxjWX4j36e+EYAikG/ZQRgGQnMoAbpBwhXEEQVfuCCCEdIwhKa8IQkbELnRoXCE85gCgFoQihIGAp0NGEOLcwhCv2gCoK0YA9dCKIQ14dIxCIa8YhITKIQCYGETxhREsxQohSJuIcWEGQVfMiCFrfIxS568YtgDKMYx0jGMn6RD6sgiCkUcIU2uvGNcIyjHOdIxzra8Y54lKMCTEEQXPxBC1gIpCAHSchCGvKQiEykIhfJSEJq4Q+4IMgwEjGGLVjhkpjMpCY3yclOevKToAylKDG5hTEkYhgEmcQKMtCABLjylbCMpSxnScta2vKWuMzlKxuQgRVMAiVVoIQwZHGKYhrzmMhMpjKXycxmOvOZ0DSmLIRBCZQIBCLYzKY2t8lNawYEADs=',
		'ico-step-now' => 'iVBORw0KGgoAAAANSUhEUgAAAAcAAAAHCAIAAABLMMCEAAAAA3NCSVQICAjb4U/gAAAACXBIWXMAAArwAAAK8AFCrDSYAAAAFnRFWHRDcmVhdGlvbiBUaW1lADEwLzI5LzA422RzJgAAAB90RVh0U29mdHdhcmUATWFjcm9tZWRpYSBGaXJld29ya3MgOLVo0ngAAACcSURBVAiZLc6hEcIwFAbg/yVp73J4IlqNAleFLwuwADtU0QVq6A4sAAbXGagBFI67mmI5yFHyEgQs8N1HL/eQXROda7J90OYzKzhdKNk1cVtiWkBI8hy35QCQP8xpskK3g9JwFukyXLeCbA8hoTRGYygNIcn2ImgDz3AWzzucheegDb1v+/i4/rnwjEs9ZJXiJB+A6LT5H7KKk/wLQ1pACnhby/oAAAAASUVORK5CYII=',
		'ico-step-succeed' => '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',
	);
	return $res;
}
