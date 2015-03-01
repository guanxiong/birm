<?php
/**
 * [WDL] Copyright (c) 2013 B2CTUI.COM
 */
defined('IN_IA') or exit('Access Denied');

function mobile_styles() {
	global $_W;
	$styles = pdo_fetchall("SELECT variable, content FROM ".tablename('site_styles')." WHERE weid = '{$_W['weid']}' AND templateid = '{$_W['account']['styleid']}'", array(), 'variable');
	if (!empty($styles)) {
		foreach ($styles as $variable => $value) {
			if (strexists($value['content'], 'images/')) {
				$value['content'] = $_W['attachurl'] . $value['content'];
			}
			if (($variable == 'logo' || $variable == 'indexbgimg' || $variable == 'ucbgimg') && !strexists($value['content'], 'http://')) {
				$value['content'] = $_W['siteroot'] . 'themes/mobile/'.$_W['account']['template'].'/images/' . $value['content'];
			}
			$styles[$variable] = $value['content'];
		}
	}
	return $styles;
}

function mobile_nav($position) {
	global $_W;
	$navs = pdo_fetchall("SELECT id,name, description, url, icon, css, position, module FROM ".tablename('site_nav')." WHERE position = '$position' AND status = 1 AND weid = '{$_W['weid']}' ORDER BY displayorder ASC");
	if (!empty($navs)) {
		foreach ($navs as $index => &$row) {
			if (!strexists($row['url'], ':') && !strexists($row['url'], 'weid=')) {
				$row['url'] .= strexists($row['url'], '?') ?  '&weid='.$_W['weid'] : '?weid='.$_W['weid'];
			}
			$row['css'] = unserialize($row['css']);
			if ($row['position'] == '3') {
				unset($row['css']['icon']['font-size']);
			}
			$row['css']['icon']['style'] = "color:{$row['css']['icon']['color']};font-size:{$row['css']['icon']['font-size']}px;";
			$row['css']['name'] = "color:{$row['css']['name']['color']};";
		}
		unset($row);
	}
	return $navs;
}