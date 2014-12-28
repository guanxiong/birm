<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

function setting_save($data = '', $key = '', $weid = 0) {
	if (empty($data) && empty($key)) {
		return FALSE;
	}
	if (is_array($data) && empty($key)) {
		foreach ($data as $key => $value) {
			$record[] = "('$key', '".iserializer($value)."')";
		}
		if ($record) {
			$return = pdo_query("REPLACE INTO ".tablename('settings')." (`key`, `value`) VALUES " . implode(',', $record));
		}
	} else {
		$record = array();
		$record['key'] = $key;
		$record['value'] = iserializer($data);
		$return = pdo_insert('settings', $record, TRUE);
	}
	cache_build_setting();
	return $return;
}

function setting_load($key = '') {
	if (empty($key)) {
		$settings = pdo_fetchall('SELECT * FROM ' . tablename('settings'), array(), 'key');
		
	} else {
		$key = is_array($key) ? $key : array($key);
		$settings = pdo_fetchall('SELECT * FROM ' . tablename('settings') . " WHERE `key` IN ('".implode("','", $key)."')", array(), 'key');
	}
	if(is_array($settings)) {
		foreach($settings as $k => &$v) {
			$settings[$k] = iunserializer($v['value']);
		}
	}
	return $settings;
}

function setting_upgrade_version($family, $version, $release) {
	$verfile = IA_ROOT . '/source/version.inc.php';
	$verdat = <<<VER
<?php
/**
 * 版本号
 * 
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */

defined('IN_IA') or exit('Access Denied');

define('IMS_FAMILY', '{$family}');
define('IMS_VERSION', '{$version}');
define('IMS_RELEASE_DATE', '{$release}');
VER;
	file_put_contents($verfile, trim($verdat));
}
