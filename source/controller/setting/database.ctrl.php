<?php 
/**
 * 数据库相关操作
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
$dos = array('backup', 'restore', 'optimize', 'run');
$do = in_array($do, $dos) ? $do : 'backup';
$excepts = array(tablename('cache'), tablename('sessions'));
foreach($excepts as &$ex) {
	$ex = str_replace('`', '', $ex);
}
unset($ex);

if($do == 'backup') {
	if(checksubmit()) {
		$continue = dump_export();
		if(!empty($continue)) {
			isetcookie('__continue', base64_encode(json_encode($continue)));
			message('正在导出数据, 请不要关闭浏览器, 当前第 1 卷.', create_url('setting/database/backup'));
		} else {
			message('数据已经备份完成', 'refresh');
		}
	}
	if($_GPC['__continue']) {
		$ctu = json_decode(base64_decode($_GPC['__continue']), true);
		$continue = dump_export($ctu);
		if(!empty($continue)) {
			isetcookie('__continue', base64_encode(json_encode($continue)));
			message('正在导出数据, 请不要关闭浏览器, 当前第 ' . $ctu['series'] . ' 卷.', create_url('setting/database/backup'));
		} else {
			isetcookie('__continue', '', -1000);
			message('数据已经备份完成', 'refresh');
		}
	}
}
if($do == 'restore') {
	$ds = array();
	$path = IA_ROOT . '/data/backup/';
	if (is_dir($path)) {
		if ($handle = opendir($path)) {
			while (false !== ($bakdir = readdir($handle))) {
				if($bakdir == '.' || $bakdir == '..') {
					continue;
				}
				if(preg_match('/^(?P<time>\d{10})_[a-z\d]{8}$/i', $bakdir, $match)) {
					$time = $match['time'];
					for($i = 1;;) {
						$last = $path . $bakdir . "/volume-{$i}.sql";
						$i++;
						$next = $path . $bakdir . "/volume-{$i}.sql";
						if(!is_file($next)) {
							break;
						}
					}
					if(is_file($last)) {
						$fp = fopen($last, 'r');
						fseek($fp, -27, SEEK_END);
						$end = fgets($fp);
						fclose($fp);
						if($end == '----WeEngine MySQL Dump End') {
							$row = array();
							$row['bakdir'] = $bakdir;
							$row['time'] = $time;
							$row['volume'] = $i - 1;
							$ds[$bakdir] = $row;
							continue;
						}
					}
				}
				rmdirs($path . $bakdir);
			}
		}
	}

	if($_GPC['r']) {
		$r = $_GPC['r'];
		if($ds[$r]) {
			$row = $ds[$r];
			for($i = 1; $i <= $row['volume']; $i++) {
				$sql = file_get_contents($path . $row['bakdir'] . "/volume-{$i}.sql");
				pdo_run($sql);
			}
			message('成功恢复数据备份. 可能还需要你更新缓存.', create_url('setting/database/restore'));
		}
	}

	if($_GPC['d']) {
		$d = $_GPC['d'];
		if($ds[$d]) {
			rmdirs($path . $d);
			message('删除备份成功.', create_url('setting/database/restore'));
		}
	}
}
if($do == 'optimize') {
	$sql = "SHOW TABLE STATUS LIKE '{$_W['config']['db']['tablepre']}%'";
   	$tables = pdo_fetchall($sql);
	$totalsize = 0;
	$ds = array();
	foreach($tables as $ss) {
		if(!empty($ss) && !empty($ss['Data_free'])) {
			$row = array();
			$row['title'] = $ss['Name'];
			$row['type'] = $ss['Engine'];
			$row['rows'] = $ss['Rows'];
			$row['data'] = sizecount($ss['Data_length']);
			$row['index'] = sizecount($ss['Index_length']);
			$row['free'] = sizecount($ss['Data_free']);
			$ds[$row['title']] = $row;
		}
	}

	if(checksubmit()) {
		foreach($_GPC['select'] as $t) {
			if(!empty($ds[$t])) {
				$sql = "OPTIMIZE TABLE {$t}";
				pdo_fetch($sql);
			}
		}
		message('数据表优化成功.', 'refresh');
	}
}
if($do == 'run') {
	if(checksubmit()) {
		$sql = $_POST['sql'];
		pdo_run($sql);
		message('查询执行成功.', 'refresh');
	}
}

template('setting/database');

function dump_export($continue = array()) {
	global $_W, $excepts;

	$sql = "SHOW TABLE STATUS LIKE '{$_W['config']['db']['tablepre']}%'";
	$tables = pdo_fetchall($sql);
	if(empty($tables)) {
		return false;
	}
	if(empty($continue)) {
		do {
			$bakdir = IA_ROOT . '/data/backup/' . TIMESTAMP . '_' . random(8);
		} while(is_dir($bakdir));
		mkdirs($bakdir);
	} else {
		$bakdir = $continue['bakdir'];
	}

	$size = 300;
	$volumn = 1024 * 1024 * 2;

	$series = 1;
	if(!empty($continue)) {
		$series = $continue['series'];
	}
	$dump = '';
	$catch = false;
	if(empty($continue)) {
		$catch = true;
	}
	foreach($tables as $t) {
		$t = array_shift($t);
		if(!empty($continue) && $t == $continue['table']) {
			$catch = true;
		}
		if(!$catch || in_array($t, $excepts)) {
			continue;
		}
		if(!empty($dump)) {
			$dump .= "\n\n";
		}
		if($t != $continue['table']) {
			$dump .= "DROP TABLE IF EXISTS {$t};\n";
			$sql = "SHOW CREATE TABLE {$t}";
			$row = pdo_fetch($sql);
			$dump .= $row['Create Table'];
			$dump .= ";\n\n";
		}

		$fields = pdo_fetchall("SHOW FULL COLUMNS FROM {$t}", array(), 'Field');
		if(empty($fields)) {
			continue;
		}
		$index = 0;
		if(!empty($continue)) {
			$index = $continue['index'];
			$continue = array();
		}
		while(true) {
			$start = $index * $size;
			$sql = "SELECT * FROM {$t} LIMIT {$start}, {$size}";
			$rs = pdo_fetchall($sql);
			if(!empty($rs)) {
				$tmp = '';
				foreach($rs as $row) {
					$tmp .= '(';
					foreach($row as $k => $v) {
						$tmp .= "'" . dump_escape_mimic($v) . "',";
					}
					$tmp = rtrim($tmp, ',');
					$tmp .= "),\n";
				}
				$tmp = rtrim($tmp, ",\n");
				$dump .= "INSERT INTO {$t} VALUES \n{$tmp};\n";
				if(strlen($dump) > $volumn) {
					$bakfile = $bakdir . "/volume-{$series}.sql";
					$dump .= "\n\n";
					file_put_contents($bakfile, $dump);
					$series++;
					$ctu = array();
					$ctu['table'] = $t;
					$ctu['index'] = $index + 1;
					$ctu['series'] = $series;
					$ctu['bakdir'] = $bakdir;
					return $ctu;
				}
			}
			if(empty($rs) || count($rs) < $size) {
				break;
			}
			$index++;
		}
	}

	$bakfile = $bakdir . "/volume-{$series}.sql";
	$dump .= "\n\n----WeEngine MySQL Dump End";
	file_put_contents($bakfile, $dump);
	return false;
}

function dump_escape_mimic($inp) { 
	return str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $inp); 
}
