<?php 
/**
 * 数据库相关操作
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
$dos = array('bom');
$do = in_array($do, $dos) ? $do : 'bom';

if($do == 'bom') {
	if(checksubmit()) {
		set_time_limit(0);
		$path = IA_ROOT;
		$tree = __tree($path);
		$ds = array();
		foreach($tree as $t) {
			$t = str_replace($path, '', $t);
			$t = str_replace('\\', '/', $t);
			if(preg_match('/^.*\.php$/', $t)) {
				$fname = $path . $t;
				$fp = fopen($fname, 'r');
				if(!empty($fp)) {
					$bom = fread($fp, 3);
					fclose($fp);
					if($bom == "\xEF\xBB\xBF") {
						$ds[] = $t;
					}
				}
			}
		}
	}
}

template('setting/bom');

function __tree($path) {
	$files = array();
	$ds = glob($path . '/*');
	if(is_array($ds)) {
		foreach($ds as $entry) {
			if(is_file($entry)) {
				$files[] = $entry;
			}
			if(is_dir($entry)) {
				$rs = __tree($entry);
				foreach($rs as $f) {
					$files[] = $f;
				}
			}
		}
	}
	return $files;
}

