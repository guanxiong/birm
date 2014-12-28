<?php
function cache_build_template() {
	//更新模板
	rmdirs(IA_ROOT . '/data/tpl', true);
}

function cache_build_setting() {
	global $_W;
	$sql = 'SELECT * FROM ' . tablename('settings');
	$setting = pdo_fetchall($sql, array(), 'key');
	if(is_array($setting)) {
		foreach($setting as $k => $v) {
			$setting[$v['key']] = iunserializer($v['value']);
		}
		cache_write("setting", $setting);
	}
}

/**
 * 更新模块缓存
 */
function cache_build_modules() {
	$modules = pdo_fetchall("SELECT * FROM " . tablename('modules') . ' ORDER BY `mid` ASC', array(), 'name');
	$pMenus = array();
	$sMenus = array();
	if (!empty($modules)) {
		foreach ($modules as $mid => &$module) {
			if (!empty($module['subscribes'])) {
				$module['subscribes'] = iunserializer($module['subscribes']);
			}
			if (!empty($module['handles'])) {
				$module['handles'] = iunserializer($module['handles']);
			}
			if (!empty($module['options'])) {
				$module['options'] = iunserializer($module['options']);
			}
			if (!empty($module['platform_menus'])) {
				$module['platform_menus'] = iunserializer($module['platform_menus']);
				if(is_array($module['platform_menus']) && !empty($module['platform_menus'])) {
					foreach($module['platform_menus'] as $row) {
						$pMenus[] = array(
							'title' => $row['title'],
							'do' => $row['do'],
							'name' => $module['name']
						);
					}
				}
			}
			if (!empty($module['site_menus'])) {
				$module['site_menus'] = iunserializer($module['site_menus']);
				if(is_array($module['site_menus']) && !empty($module['site_menus'])) {
					foreach($module['site_menus'] as $row) {
						$sMenus[] = array(
							'title' => $row['title'],
							'do' => $row['do'],
							'name' => $module['name']
						);
					}
				}
			}
		}
	}
	cache_write('menus:platform', $pMenus);
	cache_write('menus:site', $sMenus);
	cache_write('modules', $modules);
}

function cache_build_announcement() {
	$response = ihttp_get('http://www.we7.cc/api/v1/announcement.php');
	$response['content'] = json_decode($response['content'], TRUE);
	$cache = array(
		'status' => $response['status'],
		'content' => $response['content'],
		'lastupdate' => TIMESTAMP,
	);
	cache_write('announcement', $cache);
}

function cache_build_hook() {
	global $_W;
	if (empty($_W['modules'])) {
		return false;
	}
	foreach ($_W['modules'] as $mid => $module) {
		$file = IA_ROOT . "/source/modules/{$module['name']}/processor.php";
		if (!file_exists($file)) {
			continue;
		}
		include_once $file;
	}

	$classes = get_declared_classes();
	$classnames = $hooks =array();
	$namekey = 'ModuleProcessor';
	$namekeyLen = strlen($namekey);
	
	foreach($classes as $classname) {
		if(substr($classname, -$namekeyLen) == $namekey) {
			$classnames[] = $classname;
		}
	}
	foreach($classnames as $index => $classname) {
		$methods = get_class_methods($classname);
		foreach($methods as $funcname) {
			preg_match('/hook(.*)/', $funcname, $match);
			if (empty($match[1])) {
				continue;
			}
			$hookname = strtolower($match[1]);
			$modulename = strtolower(str_replace($namekey, '', $classname));
			if (in_array($modulename, array_keys($_W['modules']))) {
				$hooks[$hookname][] = array($modulename, $funcname);
			}
		}
	}
	if (!empty($hooks)) {
		cache_write("hooks", $hooks);
	}
}

function cache_build_fans_struct() {
	$struct = array();
	$result = pdo_fetchall("SHOW COLUMNS FROM ".tablename('fans'));
	if (!empty($result)) {
		foreach ($result as $row) {
			$struct[] = $row['Field'];
		}
		cache_write('fansfields', $struct);
	}
	return $struct;
}
