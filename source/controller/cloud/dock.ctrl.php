<?php 
/**
 * 自动更新相关功能
 * [WeEngine System] Copyright (c) 2013 BIRM.CO
 */
require model('cloud');
if(!empty($_W['setting']['site']['key']) && !empty($_W['setting']['site']['token'])) {
	$password = md5($_W['setting']['site']['key'] . $_W['setting']['site']['token']);
	if($password = $_POST['password']) {
		$resource = $_GPC['resource'];
		$upfile = IA_ROOT . '/data/upgrade.zip';
		if($_POST['index'] == '1') {
			@unlink($upfile);
		}
		$fp = fopen($upfile, 'a');
		fwrite($fp, $resource);
		fclose($fp);
		exit('success');
	}
}
