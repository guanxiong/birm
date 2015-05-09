<?php 
/**
 * 自动更新相关功能
 * [WDL] Copyright (c) 2013 B2CTUI.COM
 */
require model('cloud');
if(empty($_W['isfounder'])) {
	message('访问非法.');
}
if(empty($_W['setting']['site']['key']) || empty($_W['setting']['site']['token'])) {
	message("你的程序需要在微动力云服务平台注册你的站点资料, 来接入云平台服务后才能使用在线更新功能.", create_url('cloud/profile'), 'error');
}
$dos = array('upgrade', 'history');
$do = in_array($do, $dos) ? $do : 'upgrade';

if($do == 'upgrade') {
	set_time_limit(0);
	if(checksubmit('check-update')) {
		$upgrade = cloud_upgrade();
		if($upgrade['upgrade']) {
			message("检测到新版本: <strong>{$upgrade['version']} (Release {$upgrade['release']})</strong>, 请立即更新.", 'refresh');
		} else {
			message('检查结果: 恭喜, 你的程序已经是最新版本. ', 'refresh');
		}
		exit();
	}
	cache_load('upgrade');
	if (!empty($_W['cache']['upgrade'])) {
		$upgrade = iunserializer($_W['cache']['upgrade']);
	}
	if(empty($upgrade) ||  TIMESTAMP - $upgrade['lastupdate'] >= 3600 * 24 || $upgrade['upgrade']) {
		$upgrade = cloud_upgrade();
	}

	if(is_array($upgrade['attachments']) && $upgrade['attachments'][0] == 'error-client') {
		$clients = cloud_client_define();
		$upgrade['attachments'] = $clients;
	}
	if(!empty($upgrade) && !empty($upgrade['upgrade'])) {
		$offlineok = false;
		if(is_array($upgrade['attachments'])) {
			$upfile = IA_ROOT . '/data/upgrade.zip';
			if(is_file($upfile)) {
				$ret = cloud_upgrade_verify($upgrade['attachments']);
				if(is_array($ret) && !empty($ret['announcement'])) {
					$sign = md5(md5_file($upfile) . $_W['setting']['site']['token']);
					if($ret['announcement'] == $sign) {
						$offlineok = true;
					}
				}
			}
			if(!$offlineok) {
				@unlink($upfile);
			}
		}
		$hash = md5(json_encode($upgrade));
		if(checksubmit('do-download') && $_GPC['hash'] == $hash) {
			$pars = _cloud_build_params();
			$pars['archive'] = base64_encode(json_encode($upgrade['attachments']));
			$pars['standalone'] = '1';
			$form = '<form id="form1" action="http://addons.we7.cc/gateway.php" method="post">';
			foreach($pars as $key => $par) {
				$form .= "<input type=\"hidden\" name=\"{$key}\" value=\"{$par}\" />";
			}
			$form .= '</form><script type="text/javascript">document.getElementById(\'form1\').submit();</script>';
			exit($form);
		}
		if(checksubmit('do-update') && $_GPC['hash'] == $hash) {
			$bar = false;
			if($offlineok) {
				$bar = $upfile;
			} else {
				if($upgrade['attachments']) {
					$bar = cloud_upgrade_download($upgrade['attachments']);
					if(!$bar) {
						message('下载升级包失败, 请稍后重试.');
					}
				}
			}
			$failedCount = 0;
			if($upgrade['attachments'] && $bar) {
				$fp = fopen($bar, 'r');
				if ($fp) {
					$buffer = '';
					while (!feof($fp)) {
						$buffer .= fgets($fp, 4096);
						if($buffer[strlen($buffer) - 1] == "\n") {
							$pieces = explode(':', $buffer);
							$path = base64_decode($pieces[0]);
							$dat = base64_decode($pieces[1]);
							$fname = IA_ROOT . $path;
							mkdirs(dirname($fname));
							$ret = file_put_contents($fname, $dat);
							if($ret === false) {
								$failedCount++;
							}
							$buffer = '';
						}
					}
					fclose($fp);
				}
				@unlink($bar);
			}
			$updatefiles = array();
			if($upgrade['scripts']) {
				$updatedir = IA_ROOT . '/data/update/';
				mkdirs($updatedir);
				$cversion = IMS_VERSION;
				$crelease = IMS_RELEASE_DATE;
				foreach($upgrade['scripts'] as $script) {
					if($script['release'] <= $crelease) {
						continue;
					}
					$fname = "update({$crelease}-{$script['release']}).php";
					$crelease = $script['release'];
					if(empty($script['script'])) {
						$script['script'] = <<<DAT
<?php
require_once model('setting');
setting_upgrade_version('{$upgrade['family']}', '{$script['version']}', '{$script['release']}');
return true;
DAT;
					}
					$updatefile = $updatedir . $fname;
					file_put_contents($updatefile, $script['script']);
					$updatefiles[] = $updatefile;
				}
			}
			if(!empty($updatefiles)) {
				foreach($updatefiles as $file) {
					$evalret = include $file;
					if(!$evalret) {
						message('自动升级执行失败, 请联系开发人员解决.');
					}
					@unlink($file);
				}
				cache_build_fans_struct();
				cache_build_setting();
				cache_build_modules();
			}
			cache_delete('upgrade');
			message('升级成功.' . ($failedCount > 0 ? "但是其中存在 {$failedCount} 个文件没有更新成功(这可能是由于指定的位置没有写入权限, 你可以修正这个问题后重新运行升级程序), 重新运行更新程序来查看未更新成功的文件列表. " : '') . '<script type="text/javascript">window.top.closetips();</script>', 'refresh');
		}
	}
}
if($do == 'history') {
	$files = glob(IA_ROOT . '/data/update/*');
	$ds = array();
	if(is_array($files)) {
		foreach($files as $entry) {
			$fname = basename($entry);
			if(is_file($entry) && preg_match('/^update\(\d{12}\-\d{12}\)\.php$/', $fname)) {
				$code = str_replace(').php', '', str_replace('update(', '', $fname)); 
				$ds[$code] = array('title' => $code);
			}
		}
	}
	ksort($ds);
	foreach($ds as $k => $v) {
		$pieces = explode('-', $v['title']);
		if($pieces[0] < IMS_RELEASE_DATE || $pieces[1] < IMS_RELEASE_DATE) {
			$ds[$k]['error'] = true;
		}
		if($pieces[0] == IMS_RELEASE_DATE) {
			$ds[$k]['current'] = true;
		}
	}
	$foo = $_GPC['foo'];
	if($foo == 'manual') {
		$ver = $_GPC['version'];
		if($ds[$ver] && $ds[$ver]['current']) {
			$file = IA_ROOT . "/data/update/update({$ver}).php";
			$evalret = include $file;
			if(!$evalret) {
				message('自动升级执行失败, 请联系开发人员解决.');
			}
			cache_build_fans_struct();
			cache_build_setting();
			cache_build_modules();
		}
		cache_delete('upgrade');
		message('升级成功, 请删除此升级.', referer());
	}
	if($foo == 'delete') {
		$ver = $_GPC['version'];
		if($ds[$ver] && $ds[$ver]['error']) {
			$file = IA_ROOT . "/data/update/update({$ver}).php";
			@unlink($file);
		}
		message('执行成功.', referer());
	}
}
template('cloud/upgrade');
