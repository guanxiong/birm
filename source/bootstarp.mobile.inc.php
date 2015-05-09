<?php
cache_load('modules');

$sql = "SELECT * FROM " . tablename('wechats') . " WHERE `weid`=:weid LIMIT 1";
$_W['account'] = pdo_fetch($sql, array(':weid' => $_GPC['weid']));
if(empty($_W['account'])) {
	exit('error mobile site');
}
$_W['account']['default_message'] = iunserializer($_W['account']['default_message']);
$_W['account']['access_token'] = iunserializer($_W['account']['access_token']);
$_W['account']['jsapi_ticket'] = iunserializer($_W['account']['jsapi_ticket']);
$_W['account']['payment'] = iunserializer($_W['account']['payment']);
$_W['account']['siteinfo'] = iunserializer($_W['account']['siteinfo']);
$_W['weid'] = $_W['account']['weid'];
$_W['uid'] = $_W['account']['uid'];
$_W['account']['modules'] = array();
$_W['isfounder'] = in_array($_W['uid'], (array)explode(',', $_W['config']['setting']['founder'])) ? true : false;

if(!empty($_W['account']['key']) && !empty($_W['account']['secret'])){
	require_once IA_ROOT . '/source/class/account.class.php';
	
	$acc = WeAccount::create($_W['weid']);
	$_W['account']['jssdkconfig'] = $acc->getJssdkConfig();
	$accountInfo = $acc->fetchAccountInfo();
	$_W['account']['access_token'] = $accountInfo['access_token'];
	$_W['account']['jsapi_ticket'] = $accountInfo['jsapi_ticket'];
}

$template = pdo_fetchcolumn("SELECT name FROM ".tablename('site_templates')." WHERE id = '{$_W['account']['styleid']}';");
$_W['account']['template'] = !empty($template) ? $template : 'default';

$_W['setting'] = (array)cache_load("setting");

$rs = pdo_fetchall("SELECT mid,settings,enabled FROM ".tablename('wechats_modules')." WHERE weid = '{$_W['weid']}'", array(), 'mid');
$accountmodules = array();
$disabledmodules = array();
foreach($rs as $k => &$m) {
	if(!$m['enabled']) {
		$disabledmodules[$m['mid']] = $m['mid'];
		continue;
	} else {
		$accountmodules[$m['mid']] = array(
			'mid' => $m['mid'],
			'config' => iunserializer($m['settings'])
		);
	}
}
if ($_W['isfounder']) {
	$membermodules = pdo_fetchall("SELECT mid, name, issystem FROM ".tablename('modules') . (!empty($disabledmodules) ? " WHERE mid NOT IN (".implode(',', array_keys($disabledmodules)).")" : '') . " ORDER BY issystem DESC, mid ASC", array(), 'mid');
} else {
	$membermodules = pdo_fetchall("SELECT resourceid FROM ".tablename('members_permission')." WHERE uid = :uid AND type = '1'".(!empty($disabledmodules) ? " AND resourceid NOT IN (".implode(',', array_keys($disabledmodules)).")" : '')." ORDER BY resourceid ASC", array(':uid' => $_W['uid']), 'resourceid');
}

if (!empty($_W['modules'])) {
	$groupid = pdo_fetchcolumn("SELECT groupid FROM ".tablename('members')." WHERE uid = :uid", array(':uid' => $_W['uid']));
	$groupsmodules = pdo_fetch("SELECT modules FROM ".tablename('members_group')." WHERE id = :id", array(':id' => $groupid));
	if (!empty($groupsmodules['modules'])) {
		$groupsmodules['modules'] = iunserializer($groupsmodules['modules']);
	}
	foreach ($_W['modules'] as $name => $module) {
		if (isset($membermodules[$module['mid']]) || !empty($module['issystem']) || in_array($module['mid'], (array)$groupsmodules['modules'])) {
			$modulesimple = array(
				'mid' => $module['mid'],
				'name' => $module['name'],
				'title' => $module['title'],
			);

			$_W['account']['modules'][$module['name']] = $module;
			if($accountmodules[$module['mid']]['config']) {
				$_W['account']['modules'][$module['name']]['config'] = $accountmodules[$module['mid']]['config'];
			}
		}
	}
}
unset($membermodules);
unset($_W['modules']);


$session = json_decode(base64_decode($_GPC['__msess']), true);
if(is_array($session)) {
	$row = fans_search($session['openid'], array('id', 'salt', 'weid', 'from_user', 'follow', 'createtime', 'nickname', 'avatar'));
	if(!empty($row) && $row['weid'] == $_W['weid']) {
		$hash = substr(md5("{$session['openid']}{$row['salt']}{$_W['config']['setting']['authkey']}"), 5, 5);
		if($session['hash'] == $hash) {
			unset($row['salt']);
			$_W['fans'] = $row;
		}
	}
	if (!empty($_W['account']['modules']['member'])) {
		$row = pdo_fetch("SELECT * FROM ".tablename('card_members')." WHERE from_user = :from_user AND weid = :weid", array(':from_user' => $session['openid'], ':weid' => $_W['weid']));
		$row1 = pdo_fetch("SELECT * FROM ".tablename('fans')." WHERE from_user = :from_user AND weid = :weid", array(':from_user' => $session['openid'], ':weid' => $_W['weid']));
		$cardlevel = pdo_fetch("SELECT * FROM ".tablename('card')." WHERE weid = '{$_W['weid']}'");
		if (!empty($row)) {
		if(!empty($row1['avatarfixed'])){$_W['fans']['avatar'] = $row1['avatarfixed'];}
			$_W['fans']['credit1'] = $row1['credit1'];
			$_W['fans']['credit2'] = $row['credit2'];
			$_W['fans']['cardsn'] = $row['cardsn'];
			if($row1['credit1']>=$cardlevel['Lv1']&&$row1['credit1']<=$cardlevel['Lv12']){$_W['fans']['card'] = 1;}
		elseif($row1['credit1']>$cardlevel['Lv2']&&$row1['credit1']<=$cardlevel['Lv22']){$_W['fans']['card'] = 2;}
		elseif($row1['credit1']>$cardlevel['Lv3']&&$row1['credit1']<=$cardlevel['Lv32']){$_W['fans']['card'] = 3;}
		elseif($row1['credit1']>$cardlevel['Lv4']&&$row1['credit1']<=$cardlevel['Lv42']){$_W['fans']['card'] = 4;}
		elseif($row1['credit1']>$cardlevel['Lv5']&&$row1['credit1']<=$cardlevel['Lv52']){$_W['fans']['card'] = 5;}
		elseif($row1['credit1']>$cardlevel['Lv6']&&$row1['credit1']<=$cardlevel['Lv62']){$_W['fans']['card'] = 6;}
		elseif($row1['credit1']>$cardlevel['Lv7']&&$row1['credit1']<=$cardlevel['Lv72']){$_W['fans']['card'] = 7;}
		elseif($row1['credit1']>$cardlevel['Lv8']&&$row1['credit1']<=$cardlevel['Lv82']){$_W['fans']['card'] = 8;}
		elseif($row1['credit1']>$cardlevel['Lv9']&&$row1['credit1']<=$cardlevel['Lv92']){$_W['fans']['card'] = 9;}
		
		}
	}
	if(empty($_W['fans'])) {
		isetcookie('__msess', false, -100);
	}
}
unset($session);

$_W['container'] = 'browser';
if(strexists(strtolower($_SERVER['HTTP_USER_AGENT']), 'micromessenger')) {
	$_W['container'] = 'wechat';
}
if(strexists(strtolower($_SERVER['HTTP_USER_AGENT']), 'yixin')) {
	$_W['container'] = 'yixin';
}
if(!in_array($_GPC['act'], array('auth', 'oauth')) && (empty($_W['fans']) || empty($_W['fans']['from_user']))) {
	$account = $_W['account'];
	if($account['level'] == 2 && $account['type'] == 1 && $_W['container'] == 'wechat') { //高级接口权限
		$callback = urlencode($_W['siteroot'] . 'mobile.php?act=oauth&weid=' . $_W['weid']);
        //将微信的一些参数过滤掉
        if(!empty($_SERVER['QUERY_STRING'])) {
            parse_str($_SERVER['QUERY_STRING'], $arr);
            unset($arr['from'], $arr['isappinstalled'], $arr['wxref']);
            $_SERVER['QUERY_STRING'] = http_build_query($arr);
        }
       	$state = base64_encode($_SERVER['QUERY_STRING']);
		$forward = "https://open.weixin.qq.com/connect/oauth2/authorize?appid={$account['key']}&redirect_uri={$callback}&response_type=code&scope=snsapi_base&state={$state}#wechat_redirect";
		header('location: ' . $forward);
		exit();
	}
}
