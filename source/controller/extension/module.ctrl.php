<?php
/**
 * [WDL] Copyright (c) 2013 B2CTUI.COM
 * $sn: origins/source/controller/extension/module.ctrl.php : v 7851f7f37327 : 2014/09/17 06:35:28 : yanghf $
 */
defined('IN_IA') or exit('Access Denied');
$points = m_points();
require model('extension');
require model('cloud');

$dos = array('installed', 'check', 'prepared', 'install', 'upgrade', 'uninstall', 'cloud', 'designer', 'permission', 'convert');
$do = in_array($do, $dos) ? $do : 'installed';

if($do == 'installed') {
	$modules = pdo_fetchall("SELECT * FROM " . tablename('modules') . ' ORDER BY `issystem` DESC, `mid` ASC', array(), 'mid');
	if (!empty($modules)) {
		foreach ($modules as $mid => $module) {
			$manifest = ext_module_manifest($module['name']);
			if(!is_array($manifest) || empty($manifest)) {
				$ret = ext_module_manifest_compat($module['name']);
				if(!empty($ret) && !empty($ret['convert'])) {
					$manifest = $ret['convert'];
					$modules[$mid]['version_error'] = true;
				}
			}
			if(is_array($manifest) && version_compare($module['version'], $manifest['application']['version']) == '-1') {
				$modules[$mid]['upgrade'] = true;
			}
		}
	}
	template('extension/module');
}
if($do == 'check') {
	if($_W['isajax']) {
		$foo = $_GPC['foo'];
		if($foo == 'upgrade') {
			$mods = array();
			$ret = cloud_m_query();
			if(!empty($ret)) {
				foreach($ret as $k => $v) {
					$mods[$k] = array('from' => 'cloud', 'version' => $v['version']);
				}
			}
			if(!empty($mods)) {
				exit(json_encode($mods));
			}
		} else {
			$moduleids = array();
			$modules = pdo_fetchall("SELECT `name` FROM " . tablename('modules') . ' ORDER BY `issystem` DESC, `mid` ASC');
			if(!empty($modules)) {
				foreach($modules as $m) {
					$moduleids[] = $m['name'];
				}
			}
			$ret = cloud_m_query();
			if(!empty($ret)) {
				$cloudUninstallModules = array();
				foreach($ret as $k => $v) {
					if(!in_array($k, $moduleids)) {
						$v['name'] = $k;
						$cloudUninstallModules[] = $v;
						$moduleids[] = $k;
					}
				}
				exit(json_encode($cloudUninstallModules));
			}
		}
	}
	exit();
}
if($do == 'prepared') {
	if(empty($_W['isfounder'])) {
		message('您没有安装模块的权限', '', 'error');
	}
	
	$moduleids = array();
	$modules = pdo_fetchall("SELECT `name` FROM " . tablename('modules') . ' ORDER BY `issystem` DESC, `mid` ASC');
	if(!empty($modules)) {
		foreach($modules as $m) {
			$moduleids[] = $m['name'];
		}
	}
	$path = IA_ROOT . '/source/modules/';
	if (is_dir($path)) {
		$localUninstallModules = array();
		if ($handle = opendir($path)) {
			while (false !== ($modulepath = readdir($handle))) {
				$manifest = ext_module_manifest($modulepath);
				if(!is_array($manifest) || empty($manifest)) {
					$ret = ext_module_manifest_compat($modulepath);
					if(!empty($ret) && !empty($ret['convert'])) {
						$manifest = $ret['convert'];
					}
				}
				if (is_array($manifest) && !empty($manifest['application']['identifie']) && !in_array($manifest['application']['identifie'], $moduleids)) {
					$m = ext_module_convert($manifest);
					if(!in_array(IMS_VERSION, $manifest['versions'])) {
						$m['version_error'] = true;
					}
					$localUninstallModules[] = $m;
					$moduleids[] = $manifest['application']['identifie'];
				}
			}
		}
	}
	$initNG = true;
	template('extension/module');
}
if($do == 'permission') {
	$id = $_GPC['id'];
	$module = pdo_fetch("SELECT mid, name FROM " . tablename('modules') . " WHERE name = :name", array(':name' => $id));
	$isinstall = false;
	$from = '';
	if(!empty($module)) {
		$module = $_W['modules'][$module['name']];
		$bindings = pdo_fetchall('SELECT * FROM ' . tablename('modules_bindings') . ' WHERE `module`=:module', array(':module' => $id));
		if(!empty($bindings)) {
			foreach($bindings as $entry) {
				$module[$entry['entry']][] = array_elements(array('title', 'do', 'direct', 'state'), $entry);
			}
		}
		$manifest = ext_module_manifest($module['name']);
		if(!is_array($manifest) || empty($manifest)) {
			$ret = ext_module_manifest_compat($id);
			if(!empty($ret) && !empty($ret['convert'])) {
				$manifest = $ret['convert'];
				$version_error = true;
			}
		}
		if(is_array($manifest) && version_compare($module['version'], $manifest['application']['version']) == -1) {
			$module['upgrade'] = 1;
		}
		$isinstall = true;
		$from = 'installed';
	} else {
		require model('cloud');
		$define = cloud_m_info($id);
		if(!empty($define)) {
			$manifest = ext_module_manifest_parse($define);
			$from = 'cloud';
		}
		if(empty($manifest)) {
			$manifest = ext_module_manifest($id);
			if(!is_array($manifest) || empty($manifest)) {
				$ret = ext_module_manifest_compat($id);
				if(!empty($ret) && !empty($ret['convert'])) {
					$manifest = $ret['convert'];
					$version_error = true;
				}
			}
			$from = 'local';
		}
		if(is_array($manifest) && !empty($manifest)) {
			$module = ext_module_convert($manifest);
			$module['subscribes'] = iunserializer($module['subscribes']);
			$module['handles'] = iunserializer($module['handles']);
		}
	}
	if(empty($module)) {
		message('你访问的模块不存在. 或许你愿意去微动力云服务平台看看. ', 'http://v2.addons.we7.cc/search.php?q=' . $id);
	}
	$module['isinstall'] = $isinstall;
	$module['from'] = $from;

	$mtypes = m_msg_types();
	$modtypes = m_types();
	template('extension/permission');
}
if($do == 'install') {
	$id = $_GPC['id'];
	$modulepath = IA_ROOT . '/source/modules/' . $id . '/';
	$manifest = ext_module_manifest($id);
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
	$module = ext_module_convert($manifest);
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
		message('模块安装成功！', create_url('extension/module'), 'success');
	} else {
		message('模块安装失败, 请联系模块开发者！');
	}
}
if($do == 'uninstall') {
	if(empty($_W['isfounder'])) {
		message('您没有卸载模块的权限', '', 'error');
	}
	$id = $_GPC['id'];
	$module = pdo_fetch("SELECT mid, name FROM " . tablename('modules') . " WHERE name = :name", array(':name' => $id));
	if ($module['isrulefields'] && !isset($_GPC['confirm'])) {
		message('卸载模块时同时删除规则数据吗？<a href="'.create_url('extension/module/uninstall', array('id' => $_GPC['id'], 'confirm' => 1)).'">是</a> &nbsp;&nbsp;<a href="'.create_url('extension/module/uninstall', array('id' => $_GPC['id'], 'confirm' => 1)).'">否</a>', '', 'tips');
	} else {
		if (empty($module)) {
			message('模块已经被卸载或是不存在！', '', 'error');
		}
		if (!empty($module['issystem'])) {
			message('系统模块不能卸载！', '', 'error');
		}
		$modulepath = IA_ROOT . '/source/modules/' . $id . '/';
		$manifest = ext_module_manifest($module['name']);
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
			pdo_delete('cover_reply', array('module' => $module['name']));
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
			message('模块卸载成功！', create_url('extension/module'), 'success');
		} else {
			message('模块卸载失败, 请联系模块开发者！');
		}
	}
}
if($do == 'upgrade') {
	$id = $_GPC['id'];
	$module = pdo_fetch("SELECT mid, name FROM " . tablename('modules') . " WHERE name = :name", array(':name' => $id));
	if (empty($module)) {
		message('模块已经被卸载或是不存在！', '', 'error');
	}
	$modulepath = IA_ROOT . '/source/modules/' . $id . '/';
	$manifest = ext_module_manifest($module['name']);
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
	$module = ext_module_convert($manifest);
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
					//保存xml里面包含的do和title,最后删除数据库中废弃的do和title
					$delete_do[] = $entry['do'];
					$delete_title[] = $entry['title'];
					
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
					$delete_call[] = $entry['call'];
					
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
 			//删除不存在的do和title
			if(!empty($delete_do)) {
				pdo_query('DELETE FROM ' . tablename('modules_bindings') . " WHERE module = :module AND entry = :entry AND `call` = '' AND do NOT IN ('" . implode("','", $delete_do). "')", array(':module' => $manifest['application']['identifie'], ':entry' => $p));
				unset($delete_do);
			}
			if(!empty($delete_title)) {
				pdo_query('DELETE FROM ' . tablename('modules_bindings') . " WHERE module = :module AND entry = :entry AND `call` = '' AND title NOT IN ('" . implode("','", $delete_title). "')", array(':module' => $manifest['application']['identifie'], ':entry' => $p));
				unset($delete_title);
			}
			if(!empty($delete_call)) {
				pdo_query('DELETE FROM ' . tablename('modules_bindings') . " WHERE module = :module AND  entry = :entry AND do = '' AND title = '' AND `call` NOT IN ('" . implode("','", $delete_call). "')", array(':module' => $manifest['application']['identifie'], ':entry' => $p));
				unset($delete_call);
			}
  		}
	}
	
	if (!empty($manifest['upgrade'])) {
		if (strexists($manifest['upgrade'], '.php')) {
			if (file_exists($modulepath . $manifest['upgrade'])) {
				include_once $modulepath . $manifest['upgrade'];
			}
		} else {
			pdo_run($manifest['upgrade']);
		}
	}
	pdo_update('modules', $module, array('name' => $id));
	cache_build_modules();
	message('模块更新成功！', referer(), 'success');
}
if($do == 'cloud') {
	template('extension/module-cloud');
}
if($do == 'designer') {
	if(empty($_W['isfounder'])) {
		message('您没有设计模块的权限', '', 'error');
	}
	$available = array();
	$available['download'] = class_exists('ZipArchive');
	$available['create'] = @is_writable(IA_ROOT . '/source/modules');
	
	$mtypes = m_msg_types();
	$modtypes = m_types();
	$versions = array();
	$versions[] = '0.51';
	$versions[] = '0.52';
	
	$m = array();
	$m['platform'] = array();
	$m['platform']['subscribes'] = array();
	$m['platform']['handles'] = array();
	$m['site'] = array();
	$m['versions'] = array();
	if(checksubmit() && $available[$_GPC['method']]) {
		$m['application']['name'] = trim($_GPC['application']['name']);
		if(empty($m['application']['name']) || preg_match('/\*\/|\/\*|eval|\$\_/i', $m['application']['name'])) {
			message('请输入有效的模块名称. ');
		}
		$m['application']['identifie'] = trim($_GPC['application']['identifie']);
		if(empty($m['application']['identifie']) || !preg_match('/^[a-z][a-z\d_]+$/i', $m['application']['identifie'])) {
			message('必须输入模块标识符(仅支持字母和数字, 且只能以字母开头). ');
		}
		$m['application']['version'] = trim($_GPC['application']['version']);
		if(empty($m['application']['version']) || !preg_match('/^[\d\.]+$/i', $m['application']['version'])) {
			message('必须输入模块版本号(仅支持数字和句点). ');
		}
		$m['application']['ability'] = trim($_GPC['application']['ability']);
		if(empty($m['application']['ability'])) {
			message('必须输入模块功能简述. ');
		}
		$m['application']['type'] = array_key_exists($_GPC['application']['type'], $modtypes) ? $_GPC['application']['type'] : 'other';
		$m['application']['description'] = trim($_GPC['application']['description']);
		$m['application']['author'] = trim($_GPC['application']['author']);
		if(preg_match('/\*\/|\/\*|eval|\$\_/i', $m['application']['author'])) {
			message('请输入有效的模块作者');
		}
		$m['application']['url'] = trim($_GPC['application']['url']);
		if(preg_match('/\*\/|\/\*|eval|\$\_/i', $m['application']['url'])) {
			message('请输入有效的模块发布页');
		}
		$m['application']['setting'] = $_GPC['application']['setting'] == 'true';
		if(is_array($_GPC['subscribes'])) {
			foreach($_GPC['subscribes'] as $s) {
				if(array_key_exists($s, $mtypes)) {
					$m['platform']['subscribes'][] = $s;
				}
			}
		}
		if(is_array($_GPC['handles'])) {
			foreach($_GPC['handles'] as $s) {
				if(array_key_exists($s, $mtypes) && $s != 'unsubscribe') {
					$m['platform']['handles'][] = $s;
				}
			}
		}
		$m['platform']['rule'] = $_GPC['platform']['rule'] == 'true';
		if($m['platform']['rule']) {
			if(!in_array('text', $m['platform']['handles'])) {
				$m['platform']['handles'][] = 'text';
			}
		}
		$m['bindings'] = array();
		foreach($points as $p => $row) {
			if(!is_array($_GPC['bindings'][$p]['titles'])) {
				continue;
			}
			foreach($_GPC['bindings'][$p]['titles'] as $key => $t) {
				$entry = array();
				$entry['title'] = trim($t);
				$entry['do'] = $_GPC['bindings'][$p]['dos'][$key];
				$entry['state'] = $_GPC['bindings'][$p]['states'][$key] == 'true';
				$entry['direct'] = $_GPC['bindings'][$p]['directs'][$key];
				if(!empty($entry['title']) && preg_match('/^[a-z\d]+$/i', $entry['do'])) {
					$m['bindings'][$p][] = $entry;
				}
			}
		}
		if(is_array($_GPC['versions'])) {
			foreach($_GPC['versions'] as $ver) {
				if(in_array($ver, $versions)) {
					$m['versions'][] = $ver;
				}
			}
		}
		$m['install'] = trim($_GPC['install']);
		$m['uninstall'] = trim($_GPC['uninstall']);
		$m['upgrade'] = trim($_GPC['upgrade']);
		if($_FILES['icon'] && $_FILES['icon']['error'] == '0' && !empty($_FILES['icon']['tmp_name'])) {
			$m['icon'] = $_FILES['icon']['tmp_name'];
		}
		if($_FILES['preview'] && $_FILES['preview']['error'] == '0' && !empty($_FILES['preview']['tmp_name'])) {
			$m['preview'] = $_FILES['preview']['tmp_name'];
		}
		$manifest = manifest($m);
		$mDefine = define_module($m);
		$pDefine = define_processor($m);
		$rDefine = define_receiver($m);
		$sDefine = define_site($m);
		$ident = strtolower($m['application']['identifie']);
		if($_GPC['method'] == 'create') {
			$mRoot = IA_ROOT . "/source/modules/{$ident}";
			if(file_exists($mRoot)) {
				message("目标位置 {$mRoot} 已存在, 请更换标识或删除现有内容. ");
			}
			mkdirs($mRoot);
			f_write("{$mRoot}/manifest.xml", $manifest);
			if($mDefine) {
				f_write("{$mRoot}/module.php", $mDefine);
			}
			if($pDefine) {
				f_write("{$mRoot}/processor.php", $pDefine);
			}
			if($rDefine) {
				f_write("{$mRoot}/receiver.php", $rDefine);
			}
			if($sDefine) {
				f_write("{$mRoot}/site.php", $sDefine);
			}
			mkdirs("{$mRoot}/template");
			if($m['application']['setting']) {
				f_write("{$mRoot}/template/setting.html", "{template 'common/header'}\r\n这里定义页面内容\r\n{template 'common/footer'}");
			}
			if($m['icon']) {
				file_move($m['icon'], "{$mRoot}/icon.jpg");
			}
			if($m['preview']) {
				file_move($m['preview'], "{$mRoot}/preview.jpg");
			}
			message("生成成功. 请访问 {$mRoot} 继续实现你的模块.", 'refresh');
		}
		if($_GPC['method'] == 'download') {
			$fname = IA_ROOT . "/data/tmp.zip";
			$zip = new ZipArchive();
			$zip->open($fname, ZipArchive::CREATE);
			$zip->addFromString('manifest.xml', $manifest);
			if($mDefine) {
				$zip->addFromString('module.php', $mDefine);
			}
			if($pDefine) {
				$zip->addFromString('processor.php', $pDefine);
			}
			if($rDefine) {
				$zip->addFromString('receiver.php', $rDefine);
			}
			if($sDefine) {
				$zip->addFromString('site.php', $sDefine);
			}
			$zip->addEmptyDir('template');
			if($m['application']['setting']) {
				$zip->addFromString("template/setting.html", "{template 'common/header'}\r\n这里定义页面内容\r\n{template 'common/footer'}");
			}
			if($m['icon']) {
				$zip->addFile($m['icon'], 'icon.jpg');
				@unlink($m['icon']);
			}
			if($m['preview']) {
				$zip->addFile($m['preview'], 'preview.jpg');
				@unlink($m['preview']);
			}
			$zip->close();
			header('content-type: application/zip');
			header('content-disposition: attachment; filename="' . $ident . '.zip"');
			readfile($fname);
			@unlink($fname);
		}
	}
	template('extension/designer');
}
if($do == 'convert') {
	$id = $_GPC['id'];
	$manifest = ext_module_manifest($id);
	if (!empty($manifest) && is_array($manifest)) {
		message('模块安装配置文件与当前版本兼容, 不需要转换！', '', 'error');
	}
	$m = ext_module_manifest_compat($id);
	if(empty($m) || empty($m['meta']) || empty($m['convert']) || empty($m['manifest'])) {
		message('您的模块定义文件完全不兼容, 系统不能支持自动转换. 请联系模块开发者解决.');
	}
	if($_GPC['confirm'] == '1') {
		ob_clean();
		header('content-type: paint/xml');
		header('content-disposition: attachment; filename="manifest.xml"');
		exit($m['manifest']);
	} else {
		message("当前的模块支持自动转换版本. 将会把模块\"{$m['convert']['title']}\"从版本\"{$m['convert']['compact']}\"转换至当前版本\"" . IMS_VERSION . "\", 继续操作会提示下载新的版本配置文件, 请将生成的配置文件置于模块目录下覆盖后重新安装(转换后有Bug请联系模块开发者), 是否要继续？<a href=\"".create_url('extension/module/convert', array('id' => $_GPC['id'], 'confirm' => 1)).'">是</a> &nbsp;&nbsp;<a href="javascript:history.go(-1);">否</a>', '', 'tips');
	}
}

function manifest_check($id, $m) {
	if(is_string($m)) {
		message('模块配置项定义错误, 具体错误内容为: <br />' . $m);
	}
	if(!in_array(IMS_VERSION, $m['versions'])) {
		message('模块与微动力版本不兼容. ');
	}
	if(empty($m['application']['name'])) {
		message('模块名称未定义. ');
	}
	if(empty($m['application']['identifie']) || !preg_match('/^[a-z][a-z\d_]+$/i', $m['application']['identifie'])) {
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

function m_points() {
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
	return $points;
}

function m_msg_types() {
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
	return $mtypes;
}

function m_types() {
	$modtypes = array();
	$modtypes['business'] = '主要业务';
	$modtypes['customer'] = '客户关系';
	$modtypes['activity'] = '营销及活动';
	$modtypes['services'] = '常用服务及工具';
	$modtypes['other'] = '其他';
	return $modtypes;
}

function manifest($m) {
	$versions = implode(',', $m['versions']);
	$setting = $m['application']['setting'] ? 'true' : 'false';
	$subscribes = '';
	foreach($m['platform']['subscribes'] as $s) {
		$subscribes .= "\r\n\t\t\t<message type=\"{$s}\" />";
	}
	$handles = '';
	foreach($m['platform']['handles'] as $h) {
		$handles .= "\r\n\t\t\t<message type=\"{$h}\" />";
	}
	$rule = $m['platform']['rule'] ? 'true' : 'false';
	$bindings = '';
	global $points;
	foreach($points as $p => $row) {
		if(is_array($m['bindings'][$p]) && !empty($m['bindings'][$p])) {
			$piece = "\r\n\t\t<{$p}>";
			foreach($m['bindings'][$p] as $entry) {
				$direct = $entry['direct'] ? 'true' : 'false';
				$piece .= "\r\n\t\t\t<entry title=\"{$entry['title']}\" do=\"{$entry['do']}\" state=\"{$entry['state']}\" direct=\"{$direct}\" />";

			}
			$piece .= "\r\n\t\t</{$p}>";
			$bindings .= $piece;
		}
	}
	$tpl = <<<TPL
<?xml version="1.0" encoding="utf-8"?>
<manifest xmlns="http://www.we7.cc" versionCode="{$versions}">
	<application setting="{$setting}">
		<name><![CDATA[{$m['application']['name']}]]></name>
		<identifie><![CDATA[{$m['application']['identifie']}]]></identifie>
		<version><![CDATA[{$m['application']['version']}]]></version>
		<type><![CDATA[{$m['application']['type']}]]></type>
		<ability><![CDATA[{$m['application']['ability']}]]></ability>
		<description><![CDATA[{$m['application']['description']}]]></description>
		<author><![CDATA[{$m['application']['author']}]]></author>
		<url><![CDATA[{$m['application']['url']}]]></url>
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
	return ltrim($tpl);
}

function define_module($m) {
	$name = ucfirst($m['application']['identifie']);

	$rule = '';
	if($m['platform']['rule']) {
		$rule = <<<TPL
	public function fieldsFormDisplay(\$rid = 0) {
		//要嵌入规则编辑页的自定义内容，这里 \$rid 为对应的规则编号，新增时为 0
	}

	public function fieldsFormValidate(\$rid = 0) {
		//规则编辑保存时，要进行的数据验证，返回空串表示验证无误，返回其他字符串将呈现为错误提示。这里 \$rid 为对应的规则编号，新增时为 0
		return '';
	}

	public function fieldsFormSubmit(\$rid) {
		//规则验证无误保存入库时执行，这里应该进行自定义字段的保存。这里 \$rid 为对应的规则编号
	}

	public function ruleDeleted(\$rid) {
		//删除规则时调用，这里 \$rid 为对应的规则编号
	}

TPL;
	}

	$setting = '';
	if($m['application']['setting']) {
		$setting = <<<TPL
	public function settingsDisplay(\$settings) {
		//点击模块设置时将调用此方法呈现模块设置页面，\$settings 为模块设置参数, 结构为数组。这个参数系统针对不同公众账号独立保存。
		//在此呈现页面中自行处理post请求并保存设置参数（通过使用\$this->saveSettings()来实现）
		if(checksubmit()) {
			//字段验证, 并获得正确的数据\$dat
			\$this->saveSettings(\$dat);
		}
		//这里来展示设置项表单
		include \$this->template('settings');
	}

TPL;
	}

	$tpl = <<<TPL
<?php
/**
 * {$m['application']['name']}模块定义
 *
 * @author {$m['application']['author']}
 * @url {$m['application']['url']}
 */
defined('IN_IA') or exit('Access Denied');

class {$name}Module extends WeModule {
{$rule}
{$setting}
}
TPL;
	return ltrim($tpl);
}

function define_processor($m) {
	$name = ucfirst($m['application']['identifie']);
	$tpl = '';
	if($m['platform']['handles']) {
	$tpl = <<<TPL
<?php
/**
 * {$m['application']['name']}模块处理程序
 *
 * @author {$m['application']['author']}
 * @url {$m['application']['url']}
 */
defined('IN_IA') or exit('Access Denied');

class {$name}ModuleProcessor extends WeModuleProcessor {
	public function respond() {
		\$content = \$this->message['content'];
		//这里定义此模块进行消息处理时的具体过程, 请查看微动力文档来编写你的代码
	}
}
TPL;
	}
	return ltrim($tpl);
}

function define_receiver($m) {
	$name = ucfirst($m['application']['identifie']);
	$tpl = '';
	if($m['platform']['subscribes']) {
	$tpl = <<<TPL
<?php
/**
 * {$m['application']['name']}模块订阅器
 *
 * @author {$m['application']['author']}
 * @url {$m['application']['url']}
 */
defined('IN_IA') or exit('Access Denied');

class {$name}ModuleReceiver extends WeModuleReceiver {
	public function receive() {
		\$type = \$this->message['type'];
		//这里定义此模块进行消息订阅时的, 消息到达以后的具体处理过程, 请查看微动力文档来编写你的代码
	}
}
TPL;
	}
	return ltrim($tpl);
}

function define_site($m) {
	global $points;
	$name = ucfirst($m['application']['identifie']);
	$tpl = '';

	$dos = '';
	if(is_array($m['bindings']) && !empty($m['bindings'])) {
		foreach($points as $p => $row) {
			if(!empty($m['bindings'][$p]) && in_array($p, array('rule', 'menu'))) {
				foreach($m['bindings'][$p] as $opt) {
					$dName = ucfirst($opt['do']);
					$dos .= <<<TPL
	public function doWeb{$dName}() {
		//这个操作被定义用来呈现 {$row['title']}
	}

TPL;
				}
			}
			if(!empty($m['bindings'][$p]) && in_array($p, array('cover', 'home', 'profile', 'shortcut'))) {
				foreach($m['bindings'][$p] as $opt) {
					$dName = ucfirst($opt['do']);
					$dos .= <<<TPL
	public function doMobile{$dName}() {
		//这个操作被定义用来呈现 {$row['title']}
	}

TPL;
				}
			}
		}
		$tpl = <<<TPL
<?php
/**
 * {$m['application']['name']}模块微站定义
 *
 * @author {$m['application']['author']}
 * @url {$m['application']['url']}
 */
defined('IN_IA') or exit('Access Denied');

class {$name}ModuleSite extends WeModuleSite {

{$dos}
}
TPL;
	}

	return ltrim($tpl);
}

function f_write($filename, $data) {
	global $_W;
	mkdirs(dirname($filename));
	file_put_contents($filename, $data);
	@chmod($filename, $_W['config']['setting']['filemode']);
	return is_file($filename);
}
