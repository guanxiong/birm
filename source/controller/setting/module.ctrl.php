<?php
/**
 * [WNS]Copyright (c) 2013 BIRM.CO
 */
defined('IN_IA') or exit('Access Denied');
$points = array();
$points['cover'] = array(
	'title' => '功能封面',
	'desc' => '功能封面是定义微站里一个独立功能的入口(手机端操作), 将呈现为一个图文消息, 点击后进入微站系统中对应的功能.'
);
$points['rule'] = array(
	'title' => '规则列表',
	'desc' => '规则列表是定义可重复使用或者可创建多次的活动的功能入口(管理后台Web操作), 每个活动对应一条规则. 一般呈现为图文消息, 点击后进入定义好的某次活动中.'
);
$points['menu'] = array(
	'title' => '管理中心导航菜单',
	'desc' => '管理中心导航菜单将会在管理中心生成一个导航入口(管理后台Web操作), 用于对模块定义的内容进行管理.'
);
$points['home'] = array(
	'title' => '微站首页导航图标',
	'desc' => '在微站的首页上显示相关功能的链接入口(手机端操作), 一般用于通用功能的展示.'
);
$points['profile']= array(
	'title' => '微站个人中心导航链接',
	'desc' => '在微站的个人中心上显示相关功能的链接入口(手机端操作), 一般用于个人信息, 或针对个人的数据的展示.'
);
$points['shortcut']= array(
	'title' => '微站快捷功能导航',
	'desc' => '在微站的快捷菜单上展示相关功能的链接入口(手机端操作), 仅在支持快捷菜单的微站模块上有效.'
);
include model('setting');
$do = !empty($_GPC['do']) ? $_GPC['do'] : 'display';

if ($do == 'display') {
	$moduleids = array();
	$modules = pdo_fetchall("SELECT * FROM " . tablename('modules') . ' ORDER BY issystem DESC, `mid` ASC', array(), 'mid');
	if (!empty($modules)) {
		foreach ($modules as $mid => $module) {
			$manifest = setting_module_manifest($module['name']);
			if(!is_array($manifest) || empty($manifest)) {
				$ret = setting_module_manifest_compat($module['name']);
				if(!empty($ret) && !empty($ret['convert'])) {
					$manifest = $ret['convert'];
					$modules[$mid]['version_error'] = true;
				}
			}
			if(is_array($manifest) && version_compare($module['version'], $manifest['application']['version']) == -1) {
				$modules[$mid]['upgrade'] = 1;
			}
			$moduleids[] = $module['name'];
		}
	}
	$uninstallModules = array();
	$path = IA_ROOT . '/source/modules/';
	if (is_dir($path)) {
		$uninstallModules = array();
		if ($handle = opendir($path)) {
			while (false !== ($modulepath = readdir($handle))) {
				$manifest = setting_module_manifest($modulepath);
				if (is_array($manifest) && !empty($manifest['application']['identifie']) && !in_array($manifest['application']['identifie'], $moduleids)) {
					$m = setting_module_convert($manifest);
					if(!in_array(IMS_VERSION, $manifest['versions'])) {
						$m['version_error'] = true;
					}
					$uninstallModules[] = $m;
					$moduleids[] = $manifest['application']['identifie'];
				} else {
					$ret = setting_module_manifest_compat($modulepath);
					$manifest = $ret['meta'];
					if (is_array($manifest) && !empty($manifest['application']['identifie']) && !in_array($manifest['application']['identifie'], $moduleids)) {
						$m = $ret['convert'];
						$m['version_error'] = true;
						$uninstallModules[] = $m;
						$moduleids[] = $manifest['application']['identifie'];
					}
				}
			}
		}
	}
	template('setting/module');
} elseif ($do == 'permission') {
	$id = $_GPC['id'];
	$module = pdo_fetch("SELECT mid, name FROM " . tablename('modules') . " WHERE name = :name", array(':name' => $id));
	$isinstall = false;
	if(!empty($module)) {
		$module = $_W['modules'][$module['name']];
		$bindings = pdo_fetchall('SELECT * FROM ' . tablename('modules_bindings') . ' WHERE `module`=:module', array(':module' => $id));
		if(!empty($bindings)) {
			foreach($bindings as $entry) {
				$module[$entry['entry']][] = array_elements(array('title', 'do', 'direct', 'state'), $entry);
			}
		}
		$isinstall = true;
		$manifest = setting_module_manifest($module['name']);
		if(!is_array($manifest) || empty($manifest)) {
			$ret = setting_module_manifest_compat($id);
			if(!empty($ret) && !empty($ret['convert'])) {
				$manifest = $ret['convert'];
				$version_error = true;
			}
		}
		if(is_array($manifest) && version_compare($module['version'], $manifest['application']['version']) == -1) {
			$module['upgrade'] = 1;
		}
	} else {
		$manifest = setting_module_manifest($id);
		if(!is_array($manifest) || empty($manifest)) {
			$ret = setting_module_manifest_compat($id);
			if(!empty($ret) && !empty($ret['convert'])) {
				$manifest = $ret['convert'];
				$version_error = true;
			}
		}
		if(is_array($manifest) && !empty($manifest)) {
			$module = setting_module_convert($manifest);
			$module['subscribes'] = iunserializer($module['subscribes']);
			$module['handles'] = iunserializer($module['handles']);
			$module['cover'] = $module['cover'];
			$module['rule'] = $module['rule'];
			$module['menu'] = $module['menu'];
			$module['home'] = $module['home'];
			$module['profile'] = $module['profile'];
			$module['shortcut'] = $module['shortcut'];
		}
		$isinstall = false;
	}
	if(empty($module)) {
		message('你访问的模块不存在. 请更新缓存, 或者检查你的模块目录来排错, 或者联系你的模块开发商. ');
	}
	$module['isinstall'] = $isinstall;

	$mtypes = array();
	$mtypes['text'] = '文本消息(重要)';
	$mtypes['image'] = '图片消息';
	$mtypes['voice'] = '语音消息';
	$mtypes['video'] = '视频消息';
	$mtypes['location'] = '位置消息';
	$mtypes['link'] = '链接消息';
	$mtypes['subscribe'] = '粉丝开始关注';
	$mtypes['unsubscribe'] = '粉丝取消关注';
	$mtypes['click'] = '菜单消息';
	$modtypes = array();
	$modtypes['business'] = '主要业务';
	$modtypes['customer'] = '客户关系';
	$modtypes['activity'] = '营销及活动';
	$modtypes['services'] = '常用服务及工具';
	$modtypes['other'] = '其他';

	template('setting/permission');
} elseif ($do == 'install') {
	$id = $_GPC['id'];
	$modulepath = IA_ROOT . '/source/modules/' . $id . '/';
	$manifest = setting_module_manifest($id);
	if (empty($manifest)) {
		message('模块安装配置文件不存在或是格式不正确！', '', 'error');
	}
	manifest_check($id, $manifest);
	if (pdo_fetchcolumn("SELECT mid FROM " . tablename('modules') . " WHERE name = :name", array(':name' => $manifest['application']['identifie']))) {
		message('模块已经安装或是唯一标识已存在！', '', 'error');
	}
	if (!file_exists($modulepath . 'processor.php') && !file_exists($modulepath . 'module.php') && !file_exists($modulepath . 'receiver.php') && !file_exists($modulepath . 'site.php')) {
		message('模块缺少处理文件！', '', 'error');
	}
	$module = setting_module_convert($manifest);
	$sql = 'DELETE FROM ' . tablename('modules_bindings') . ' WHERE `module`=:module';
	pdo_query($sql, array(':module' => $manifest['application']['identifie']));
	$bindings = array_elements(array_keys($points), $module, false);
	foreach($points as $p => $row) {
		unset($module[$p]);
		if(is_array($bindings[$p]) && !empty($bindings[$p])) {
			foreach($bindings[$p] as $entry) {
				$entry['module'] = $manifest['application']['identifie'];
				$entry['entry'] = $p;
				pdo_insert('modules_bindings', $entry);
			}
		}
	}
	if (pdo_insert('modules', $module)) {
		cache_build_modules();
		if (strexists($manifest['install'], '.php')) {
			if (file_exists($modulepath . $manifest['install'])) {
				include_once $modulepath . $manifest['install'];
			}
		} else {
			pdo_run($manifest['install']);
		}
		message('模块安装成功！', create_url('setting/module'), 'success');
	} else {
		message('模块安装失败, 请联系模块开发者！');
	}
} elseif ($do == 'uninstall') {
	$id = $_GPC['id'];
	$module = pdo_fetch("SELECT mid, name FROM " . tablename('modules') . " WHERE name = :name", array(':name' => $id));
	if ($module['isrulefields'] && !isset($_GPC['confirm'])) {
		message('卸载模块时同时删除规则数据吗？<a href="'.create_url('setting/module/uninstall', array('id' => $_GPC['id'], 'confirm' => 1)).'">是</a> &nbsp;&nbsp;<a href="'.create_url('setting/module/uninstall', array('id' => $_GPC['id'], 'confirm' => 1)).'">否</a>', '', 'tips');
	} else {
		if (empty($module)) {
			message('模块已经被卸载或是不存在！', '', 'error');
		}
		if (!empty($module['issystem'])) {
			message('系统模块不能卸载！', '', 'error');
		}
		$modulepath = IA_ROOT . '/source/modules/' . $id . '/';
		$manifest = setting_module_manifest($module['name']);
		if (pdo_delete('modules', array('mid' => $module['mid']))) {
			pdo_delete('wechats_modules', array('mid' => $module['mid']));
			$sql = 'DELETE FROM ' . tablename('modules_bindings') . ' WHERE `module`=:module';
			pdo_query($sql, array(':module' => $manifest['application']['identifie']));
			$sql = 'DELETE FROM ' . tablename('site_nav') . ' WHERE `module`=:module';
			pdo_query($sql, array(':module' => $manifest['application']['identifie']));
			if ($_GPC['confirm'] == '1') {
				pdo_delete('rule', array('module' => $module['name']));
				pdo_delete('rule_keyword', array('module' => $module['name']));
			}
			cache_build_modules();
			if (!empty($manifest['uninstall'])) {
				if (strexists($manifest['uninstall'], '.php')) {
					if (file_exists($modulepath . $manifest['uninstall'])) {
						include_once $modulepath . $manifest['uninstall'];
					}
				} else {
					pdo_run($manifest['uninstall']);
				}
			}
			message('模块卸载成功！', create_url('setting/module'), 'success');
		} else {
			message('模块卸载失败, 请联系模块开发者！');
		}
	}
} elseif ($do == 'upgrade') {
	$id = $_GPC['id'];
	$module = pdo_fetch("SELECT mid, name FROM " . tablename('modules') . " WHERE name = :name", array(':name' => $id));
	if (empty($module)) {
		message('模块已经被卸载或是不存在！', '', 'error');
	}
	$modulepath = IA_ROOT . '/source/modules/' . $id . '/';
	$manifest = setting_module_manifest($module['name']);
	if (empty($manifest)) {
		message('模块安装配置文件不存在或是格式不正确！', '', 'error');
	}
	manifest_check($id, $manifest);
	if(version_compare($module['version'], $manifest['application']['version']) != -1) {
		message('已安装的模块版本不低于要更新的版本, 操作无效.');
	}
	if (!file_exists($modulepath . 'processor.php') && !file_exists($modulepath . 'module.php') && !file_exists($modulepath . 'receiver.php') && !file_exists($modulepath . 'site.php')) {
		message('模块缺少处理文件！', '', 'error');
	}
	$module = setting_module_convert($manifest);
	unset($module['name']);
	unset($module['id']);
	$bindings = array_elements(array_keys($points), $module, false);
	foreach($points as $p => $row) {
		unset($module[$p]);
		if(is_array($bindings[$p]) && !empty($bindings[$p])) {
			foreach($bindings[$p] as $entry) {
				$entry['module'] = $manifest['application']['identifie'];
				$entry['entry'] = $p;
				if($entry['title'] && $entry['do']) {
					$sql = 'SELECT * FROM ' . tablename('modules_bindings') . ' WHERE `module`=:module AND `entry`=:entry AND `title`=:title AND `do`=:do';
					$pars = array();
					$pars[':module'] = $manifest['application']['identifie'];
					$pars[':entry'] = $p;
					$pars[':title'] = $entry['title'];
					$pars[':do'] = $entry['do'];
					$rec = pdo_fetch($sql, $pars);
					if(!empty($rec)) {
						pdo_update('modules_bindings', $entry, array('eid' => $rec['eid']));
						continue;
					}
				} elseif ($entry['call']) {
					$sql = 'SELECT * FROM ' . tablename('modules_bindings') . ' WHERE `module`=:module AND `entry`=:entry AND `call`=:call';
					$pars = array();
					$pars[':module'] = $manifest['application']['identifie'];
					$pars[':entry'] = $p;
					$pars[':call'] = $entry['call'];
					$rec = pdo_fetch($sql, $pars);
					if(!empty($rec)) {
						pdo_update('modules_bindings', $entry, array('eid' => $rec['eid']));
						continue;
					}
				}
				pdo_insert('modules_bindings', $entry);
			}
		}
	}
	if(pdo_update('modules', $module, array('name' => $id))) {
		cache_build_modules();
		if (!empty($manifest['upgrade'])) {
			if (strexists($manifest['upgrade'], '.php')) {
				if (file_exists($modulepath . $manifest['upgrade'])) {
					include_once $modulepath . $manifest['upgrade'];
				}
			} else {
				pdo_run($manifest['upgrade']);
			}
		}
		message('模块更新成功！', referer(), 'success');
	} else {
		message('模块更新失败, 请联系模块提供商！');
	}
} elseif ($do == 'convert') {
	$id = $_GPC['id'];
	$manifest = setting_module_manifest($id);
	if (!empty($manifest) && is_array($manifest)) {
		message('模块安装配置文件与当前版本兼容, 不需要转换！', '', 'error');
	}
	$m = setting_module_manifest_compat($id);
	if(empty($m) || empty($m['meta']) || empty($m['convert']) || empty($m['manifest'])) {
		message('您的模块定义文件完全不兼容, 系统不能支持自动转换. 请联系模块开发者解决.');
	}
	if($_GPC['confirm'] == '1') {
		ob_clean();
		header('content-type: paint/xml');
		header('content-disposition: attachment; filename="manifest.xml"');
		exit($m['manifest']);
	} else {
		message("当前的模块支持自动转换版本. 将会把模块\"{$m['convert']['title']}\"从版本\"{$m['convert']['compact']}\"转换至当前版本\"" . IMS_VERSION . "\", 继续操作会提示下载新的版本配置文件, 请将生成的配置文件置于模块目录下覆盖后重新安装(转换后有Bug请联系模块开发者), 是否要继续？<a href=\"".create_url('setting/module/convert', array('id' => $_GPC['id'], 'confirm' => 1)).'">是</a> &nbsp;&nbsp;<a href="javascript:history.go(-1);">否</a>', '', 'tips');
	}
}

function manifest_check($id, $m) {
	if(is_string($m)) {
		message('模块配置项定义错误, 具体错误内容为: <br />' . $m);
	}
	if(!in_array(IMS_VERSION, $m['versions'])) {
		message('模块与微新星版本不兼容. ');
	}
	if(empty($m['application']['name'])) {
		message('模块名称未定义. ');
	}
	if(empty($m['application']['identifie']) || !preg_match('/^[a-z][a-z\d]+$/i', $m['application']['identifie'])) {
		message('模块标识符未定义或格式错误(仅支持字母和数字, 且只能以字母开头). ');
	}
	if(strtolower($id) != strtolower($m['application']['identifie'])) {
		message('模块名称定义与模块路径名称定义不匹配. ');
	}
	if(empty($m['application']['version']) || !preg_match('/^[\d\.]+$/i', $m['application']['version'])) {
		message('模块版本号未定义(仅支持数字和句点). ');
	}
	if(empty($m['application']['ability'])) {
		message('模块功能简述未定义. ');
	}
	if($m['platform']['isrulefields'] && !in_array('text', $m['platform']['handles'])) {
		message('模块功能定义错误, 嵌入规则必须要能够处理文本类型消息. ');
	}
	if((!empty($m['cover']) || !empty($m['rule'])) && !$m['platform']['isrulefields']) {
		message('模块功能定义错误, 存在封面或规则功能入口绑定时, 必须要嵌入规则. ');
	}
	global $points;
	foreach($points as $p => $row) {
		if(is_array($m[$p])) {
			foreach($m[$p] as $o) {
				if(trim($o['title']) == ''  || !preg_match('/^[a-z\d]+$/i', $o['do']) && empty($o['call'])) {
					message($row['title'] . ' 扩展项功能入口定义错误, (操作标题[title], 入口方法[do])格式不正确.');
				}
			}
		}
	}
	if(!is_array($m['versions'])) {
		message('兼容版本格式错误. ');
	}
}

function setting_module_manifest_compat($modulename) {
	$manifest = array();
	$filename = IA_ROOT . '/source/modules/' . $modulename . '/manifest.xml';
	if (!file_exists($filename)) {
		return array();
	}
	$xml = str_replace(array('&'), array('&amp;'), file_get_contents($filename));
	$xml = @simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
	if (empty($xml)) {
		return array();
	}
	$dom = new DOMDocument();
	@$dom->load($filename);
	if(@$dom->schemaValidateSource(setting_module_manifest_validate_050())) {
		$attributes = $xml->attributes();
		$manifest['versions'] = explode(',', strval($attributes['versionCode']));
		if(is_array($manifest['versions'])) {
			foreach($manifest['versions'] as &$v) {
				$v = trim($v);
				if(empty($v)) {
					unset($v);
				}
			}
		}
		$manifest['version'] = '0.5';
		$manifest['install'] = strval($xml->install);
		$manifest['uninstall'] = strval($xml->uninstall);
		$manifest['upgrade'] = strval($xml->upgrade);
		$attributes = $xml->application->attributes();
		$manifest['application'] = array(
			'name' => trim(strval($xml->application->name)),
			'identifie' => trim(strval($xml->application->identifie)),
			'version' => trim(strval($xml->application->version)),
			'ability' => trim(strval($xml->application->ability)),
			'description' => trim(strval($xml->application->description)),
			'author' => trim(strval($xml->application->author)),
			'url' => trim(strval($xml->application->url)),
			'setting' => trim(strval($attributes['setting'])) == 'true',
		);
		$rAttrs = array();
		if($xml->platform && $xml->platform->rule) {
			$rAttrs = $xml->platform->rule->attributes();
		}
		$mAttrs = array();
		if($xml->platform && $xml->platform->menus) {
			$mAttrs = $xml->platform->menus->attributes();
		}
		$manifest['platform'] = array(
			'subscribes' => array(),
			'handles' => array(),
			'isrulefields' => trim(strval($rAttrs['embed'])) == 'true',
			'options' => array(),
			'ismenus' => trim(strval($mAttrs['embed'])) == 'true',
			'menus' => array()
		);
		if($xml->platform->subscribes->message) {
			foreach($xml->platform->subscribes->message as $msg) {
				$attrs = $msg->attributes();
				$manifest['platform']['subscribes'][] = trim(strval($attrs['type']));
			}
		}
		if($xml->platform->handles->message) {
			foreach($xml->platform->handles->message as $msg) {
				$attrs = $msg->attributes();
				$manifest['platform']['handles'][] = trim(strval($attrs['type']));
			}
		}
		if($manifest['platform']['isrulefields'] && $xml->platform->rule->option) {
			foreach($xml->platform->rule->option as $msg) {
				$attrs = $msg->attributes();
				$manifest['platform']['options'][] = array('title' => trim(strval($attrs['title'])), 'do' => trim(strval($attrs['do'])), 'state' => trim(strval($attrs['state'])));
			}
		}
		if($manifest['platform']['ismenus'] && $xml->platform->menus->menu) {
			foreach($xml->platform->menus->menu as $msg) {
				$attrs = $msg->attributes();
				$manifest['platform']['menus'][] = array('title' => trim(strval($attrs['title'])), 'do' => trim(strval($attrs['do'])));
			}
		}
		$hAttrs = array();
		if($xml->site && $xml->site->home) {
			$hAttrs = $xml->site->home->attributes();
		}
		$pAttrs = array();
		if($xml->site && $xml->site->profile) {
			$pAttrs = $xml->site->profile->attributes();
		}

		$mAttrs = array();
		if($xml->site && $xml->site->menus) {
			$mAttrs = $xml->site->menus->attributes();
		}
		$manifest['site'] = array(
			'home' => trim(strval($hAttrs['embed'])) == 'true',
			'profile' => trim(strval($pAttrs['embed'])) == 'true',
			'ismenus' => trim(strval($mAttrs['embed'])) == 'true',
			'menus' => array()
		);
		if($manifest['site']['ismenus'] && $xml->site->menus->menu) {
			foreach($xml->site->menus->menu as $msg) {
				$attrs = $msg->attributes();
				$manifest['site']['menus'][] = array('title' => trim(strval($attrs['title'])), 'do' => trim(strval($attrs['do'])));
			}
		}
	} else {
		$attributes = $xml->attributes();
		$manifest['version'] = strval($attributes['versionCode']);
		$manifest['install'] = strval($xml->install);
		$manifest['uninstall'] = strval($xml->uninstall);
		$manifest['upgrade'] = strval($xml->upgrade);
		$attributes = $xml->application->attributes();
		$manifest['application'] = array(
			'name' => strval($xml->application->name),
			'identifie' => strval($xml->application->identifie),
			'version' => strval($xml->application->version),
			'ability' => strval($xml->application->ability),
			'description' => strval($xml->application->description),
			'author' => strval($xml->application->author),
			'setting' => strval($attributes['setting']) == 'true',
		);
		$hooks = @(array)$xml->hooks->children();
		if (!empty($hooks['hook'])) {
			foreach ((array)$hooks['hook'] as $hook) {
				$manifest['hooks'][strval($hook['name'])] = strval($hook['name']);
			}
		}
		$menus = @(array)$xml->menus->children();
		if (!empty($menus['menu'])) {
			foreach ((array)$menus['menu'] as $menu) {
				$manifest['menus'][] = array(strval($menu['name']), strval($menu['value']));
			}
		}
	}

	$ret = array();
	$ret['meta'] = $manifest;
	$ret['meta']['compact'] = $manifest['version'];
	global $points;
	if($ret['meta']['compact'] == '0.41' || $ret['meta']['compact'] == '0.4') {
		//Compact 0.41
		$ret['convert'] = setting_module_convert($manifest);
		$ret['convert']['compact'] = $manifest['version'];
		$ret['convert']['type'] = 'other';
		foreach($points as $p => $row) {
			$ret['convert'][$p] = array();
		}

		$handles = iunserializer($ret['convert']['handles']);
		if($ret['meta']['hooks'] && $ret['meta']['hooks']['rule']) {
			$handles[] = 'text';
			$ret['convert']['isrulefields'] = true;
		}
		$ret['convert']['handles'] = iserializer($handles);
		if(is_array($ret['meta']['menus'])) {
			foreach($ret['meta']['menus'] as $row) {
				$opt = array();
				$opt['title'] = $row[0];
				$urls = parse_url($row[1]);
				parse_str($urls['query'], $vars);
				$opt['do'] = $vars['do'];
				$opt['state'] = $vars['state'];
				if(!empty($opt['title']) && !empty($opt['do'])) {
					$ret['convert']['rule'][] = $opt;
				}
			}
		}

		$m = $ret['convert'];
		$m['install'] = $manifest['install'];
		$m['uninstall'] = $manifest['uninstall'];
		$m['upgrade'] = $manifest['upgrade'];
		$m['handles'] = iunserializer($m['handles']);
		$versions = IMS_VERSION;
		$setting = $m['settings'] ? 'true' : 'false';
		$handles = '';
		foreach($m['handles'] as $h) {
			$handles .= "\r\n\t\t\t<message type=\"{$h}\" />";
		}
		$rule = $m['isrulefields'] ? 'true' : 'false';
		$bindings = '';
		foreach($points as $p => $row) {
			if(is_array($m[$p]) && !empty($m[$p])) {
				$piece = "\r\n\t\t<{$p}{$calls[$p]}>";
				foreach($m[$p] as $entry) {
					if(!empty($entry['title']) && !empty($entry['do'])) {
						$direct = $entry['direct'] ? 'true' : 'false';
						$piece .= "\r\n\t\t\t<entry title=\"{$entry['title']}\" do=\"{$entry['do']}\" state=\"{$entry['state']}\" direct=\"{$direct}\" />";
					}
				}
				$piece .= "\r\n\t\t</{$p}>";
				$bindings .= $piece;
			}
		}
		$tpl = <<<TPL
<?xml version="1.0" encoding="utf-8"?>
<manifest xmlns="http://www.we7.cc" versionCode="{$versions}">
	<application setting="{$setting}">
		<name><![CDATA[{$m['title']}]]></name>
		<identifie><![CDATA[{$m['name']}]]></identifie>
		<version><![CDATA[{$m['version']}]]></version>
		<type><![CDATA[{$manifest['application']['type']}]]></type>
		<ability><![CDATA[{$m['ability']}]]></ability>
		<description><![CDATA[{$m['description']}]]></description>
		<author><![CDATA[{$m['author']}]]></author>
		<url><![CDATA[{$m['url']}]]></url>
	</application>
	<platform>
		<handles>{$handles}
		</handles>
		<rule embed="{$rule}" />
	</platform>
	<bindings>{$bindings}
	</bindings>
	<install><![CDATA[{$m['install']}]]></install>
	<uninstall><![CDATA[{$m['uninstall']}]]></uninstall>
	<upgrade><![CDATA[{$m['upgrade']}]]></upgrade>
</manifest>
TPL;
		$ret['manifest'] = ltrim($tpl);
		return $ret;
	}
	if($ret['meta']['compact'] == '0.5') {
		// Compact 0.5
		$ret['convert'] = setting_module_convert($manifest);
		$ret['convert']['compact'] = $manifest['version'];
		$ret['convert']['type'] = 'other';
		foreach($points as $p => $row) {
			$ret['convert'][$p] = array();
		}
		if(is_array($manifest['platform']['options'])) {
			foreach($manifest['platform']['options'] as $opt) {
				$entry = array();
				$entry['title'] = $opt['title'];
				$entry['do'] = $opt['do'];
				$entry['state'] = $opt['state'];
				if(!empty($entry['title']) && !empty($entry['do'])) {
					$ret['convert']['rule'][] = $entry;
				}
			}
		}
		if(is_array($manifest['platform']['menus'])) {
			foreach($manifest['platform']['menus'] as $opt) {
				$entry = array();
				$entry['title'] = $opt['title'];
				$entry['do'] = $opt['do'];
				$entry['state'] = $opt['state'];
				if(!empty($entry['title']) && !empty($entry['do'])) {
					$ret['convert']['menu'][] = $entry;
				}
			}
		}
		if(is_array($manifest['site']['menus'])) {
			foreach($manifest['site']['menus'] as $opt) {
				$entry = array();
				$entry['title'] = $opt['title'];
				$entry['do'] = $opt['do'];
				$entry['state'] = $opt['state'];
				if(!empty($entry['title']) && !empty($entry['do'])) {
					$ret['convert']['menu'][] = $entry;
				}
			}
		}
		$calls = array();
		if(!empty($manifest['site']['home'])) {
			$calls['home'] = ' call="getHomeTiles"';
			$ret['convert']['home'][] = array('call' => 'getHomeTiles');
		}
		if(!empty($manifest['site']['profile'])) {
			$calls['profile'] = ' call="getProfileTiles"';
			$ret['convert']['profile'][] = array('call' => 'getProfileTiles');
		}

		$m = $ret['convert'];
		$versions = IMS_VERSION;
		$setting = $m['settings'] ? 'true' : 'false';
		$subscribes = '';
		foreach($manifest['platform']['subscribes'] as $s) {
			$subscribes .= "\r\n\t\t\t<message type=\"{$s}\" />";
		}
		$handles = '';
		foreach($manifest['platform']['handles'] as $h) {
			$handles .= "\r\n\t\t\t<message type=\"{$h}\" />";
		}
		$rule = $m['isrulefields'] ? 'true' : 'false';
		$bindings = '';
		foreach($points as $p => $row) {
			if(is_array($m[$p]) && !empty($m[$p])) {
				$piece = "\r\n\t\t<{$p}{$calls[$p]}>";
				foreach($m[$p] as $entry) {
					if(!empty($entry['title']) && !empty($entry['do'])) {
						$direct = $entry['direct'] ? 'true' : 'false';
						$piece .= "\r\n\t\t\t<entry title=\"{$entry['title']}\" do=\"{$entry['do']}\" state=\"{$entry['state']}\" direct=\"{$direct}\" />";
					}
				}
				$piece .= "\r\n\t\t</{$p}>";
				$bindings .= $piece;
			}
		}
		$tpl = <<<TPL
<?xml version="1.0" encoding="utf-8"?>
<manifest xmlns="http://www.we7.cc" versionCode="{$versions}">
	<application setting="{$setting}">
		<name><![CDATA[{$m['title']}]]></name>
		<identifie><![CDATA[{$m['name']}]]></identifie>
		<version><![CDATA[{$m['version']}]]></version>
		<type><![CDATA[{$manifest['application']['type']}]]></type>
		<ability><![CDATA[{$m['ability']}]]></ability>
		<description><![CDATA[{$m['description']}]]></description>
		<author><![CDATA[{$m['author']}]]></author>
		<url><![CDATA[{$m['url']}]]></url>
	</application>
	<platform>
		<subscribes>{$subscribes}
		</subscribes>
		<handles>{$handles}
		</handles>
		<rule embed="{$rule}" />
	</platform>
	<bindings>{$bindings}
	</bindings>
	<install><![CDATA[{$m['install']}]]></install>
	<uninstall><![CDATA[{$m['uninstall']}]]></uninstall>
	<upgrade><![CDATA[{$m['upgrade']}]]></upgrade>
</manifest>
TPL;
		$ret['manifest'] = ltrim($tpl);
		return $ret;
	}
	return array();
}

function setting_module_manifest_validate_050() {
	$xsd = <<<TPL
<?xml version="1.0" encoding="utf-8"?>
<xs:schema xmlns="http://www.we7.cc" xmlns:xs="http://www.w3.org/2001/XMLSchema">
	<xs:element name="manifest">
		<xs:complexType>
			<xs:sequence>
				<xs:element name="application" minOccurs="1" maxOccurs="1">
					<xs:complexType>
						<xs:sequence>
							<xs:element name="name" type="xs:string"  minOccurs="1" maxOccurs="1" />
							<xs:element name="identifie" type="xs:string"  minOccurs="1" maxOccurs="1" />
							<xs:element name="version" type="xs:string"  minOccurs="1" maxOccurs="1" />
							<xs:element name="type" type="xs:string"  minOccurs="0" maxOccurs="1" />
							<xs:element name="ability" type="xs:string"  minOccurs="1" maxOccurs="1" />
							<xs:element name="description" type="xs:string"  minOccurs="1" maxOccurs="1" />
							<xs:element name="author" type="xs:string"  minOccurs="1" maxOccurs="1" />
							<xs:element name="url" type="xs:string"  minOccurs="1" maxOccurs="1" />
						</xs:sequence>
						<xs:attribute name="setting" type="xs:boolean" />
					</xs:complexType>
				</xs:element>
				<xs:element name="platform" minOccurs="0" maxOccurs="1">
					<xs:complexType>
						<xs:sequence>
							<xs:element name="subscribes" minOccurs="0" maxOccurs="1">
								<xs:complexType>
									<xs:sequence>
										<xs:element name="message" minOccurs="0" maxOccurs="unbounded">
											<xs:complexType>
												<xs:attribute name="type" type="xs:string" />
											</xs:complexType>
										</xs:element>
									</xs:sequence>
								</xs:complexType>
							</xs:element>
							<xs:element name="handles" minOccurs="0" maxOccurs="1">
								<xs:complexType>
									<xs:sequence>
										<xs:element name="message" minOccurs="0" maxOccurs="unbounded">
											<xs:complexType>
												<xs:attribute name="type" type="xs:string" />
											</xs:complexType>
										</xs:element>
									</xs:sequence>
								</xs:complexType>
							</xs:element>
							<xs:element name="rule" minOccurs="0" maxOccurs="1">
								<xs:complexType>
									<xs:sequence>
										<xs:element name="option" minOccurs="0" maxOccurs="unbounded">
											<xs:complexType>
												<xs:attribute name="title" type="xs:string" />
												<xs:attribute name="do" type="xs:string" />
												<xs:attribute name="state" type="xs:string" />
											</xs:complexType>
										</xs:element>
									</xs:sequence>
									<xs:attribute name="embed" type="xs:boolean" />
									<xs:attribute name="single" type="xs:boolean" />
								</xs:complexType>
							</xs:element>
							<xs:element name="menus" minOccurs="0" maxOccurs="1">
								<xs:complexType>
									<xs:sequence>
										<xs:element name="menu" minOccurs="0" maxOccurs="unbounded">
											<xs:complexType>
												<xs:attribute name="title" type="xs:string" />
												<xs:attribute name="do" type="xs:string" />
											</xs:complexType>
										</xs:element>
									</xs:sequence>
									<xs:attribute name="embed" type="xs:boolean" />
								</xs:complexType>
							</xs:element>
						</xs:sequence>
					</xs:complexType>
				</xs:element>
				<xs:element name="site" minOccurs="0" maxOccurs="1">
					<xs:complexType>
						<xs:sequence>
							<xs:element name="home" minOccurs="1" maxOccurs="1">
								<xs:complexType>
									<xs:attribute name="embed" type="xs:boolean" />
								</xs:complexType>
							</xs:element>
							<xs:element name="profile" minOccurs="1" maxOccurs="1">
								<xs:complexType>
									<xs:attribute name="embed" type="xs:boolean" />
								</xs:complexType>
							</xs:element>
							<xs:element name="menus" minOccurs="1" maxOccurs="1">
								<xs:complexType>
									<xs:sequence>
										<xs:element name="menu" minOccurs="0" maxOccurs="unbounded">
											<xs:complexType>
												<xs:attribute name="title" type="xs:string" />
												<xs:attribute name="do" type="xs:string" />
											</xs:complexType>
										</xs:element>
									</xs:sequence>
									<xs:attribute name="embed" type="xs:boolean" />
								</xs:complexType>
							</xs:element>
						</xs:sequence>
					</xs:complexType>
				</xs:element>
				<xs:element name="install" type="xs:string" minOccurs="1" maxOccurs="1"/>
				<xs:element name="uninstall" type="xs:string" minOccurs="1" maxOccurs="1" />
				<xs:element name="upgrade" type="xs:string" minOccurs="1" maxOccurs="1" />
			</xs:sequence>
			<xs:attribute name="versionCode" type="xs:string" />
		</xs:complexType>
	</xs:element>
</xs:schema>
TPL;
	return trim($xsd);
}

