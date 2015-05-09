<?php
/**
 * [WDL] Copyright (c) 2013 B2CTUI.COM
 */
defined('IN_IA') or exit('Access Denied');
checklogin();
$do = !empty($_GPC['do']) && in_array($_GPC['do'], array('profile', 'global')) ? $_GPC['do'] : '';
if($do == '') {
	$do = $_W['weid'] ? 'profile' : 'global';
}
$todaytimestamp = strtotime(date('Y-m-d'));
$monthtimestamp = strtotime(date('Y-m'));

checkaccount();
$modules = $_W['account']['modules'];
if (!empty($modules)) {
	foreach ($modules as $mid => $module) {
		if ($_W['modules'][$module['name']]['isrulefields']) {
			$modules[$mid]['response']['month'] = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('stat_msg_history')." WHERE weid = :weid AND module = :module AND createtime >= '$monthtimestamp'", array(':weid' => $_W['weid'], ':module' => $module['name']));
			$modules[$mid]['response']['today'] = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('stat_msg_history')." WHERE weid = :weid AND module = :module AND createtime >= '$todaytimestamp'", array(':weid' => $_W['weid'], ':module' => $module['name']));
			$modules[$mid]['rule'] = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('rule')." WHERE weid = :weid AND module = :module", array(':weid' => $_W['weid'], ':module' => $module['name']));
		}
	}
}
include model('rule');
if (is_array($_W['account']['default'])) {
	$wechat['default'] = rule_single($_W['account']['default']['id']);
	$wechat['defaultrid'] = $_W['account']['default']['id'];
}
if (is_array($_W['account']['welcome'])) {
	$wechat['welcome'] = rule_single($_W['account']['welcome']['id']);
	$wechat['welcomerid'] = $_W['account']['welcome']['id'];
}
	
$shorts = @iunserializer($_W['account']['shortcuts']);
if(!is_array($shorts)) {
	$shorts = array();
}
$shortcuts = array();
foreach($shorts as $shortcut) {
	$module = $_W['account']['modules'][$shortcut['mid']];
	if(!empty($module)) {
		$shortcut['title'] = $_W['modules'][$module['name']]['title'];
		$shortcut['image'] = './source/modules/' . $module['name'] . '/icon.jpg';
		$shortcuts[] = $shortcut;
	}
}
unset($shortcut);
template('home/welcome');
