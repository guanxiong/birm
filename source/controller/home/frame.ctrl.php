<?php
/**
 * [WDL] Copyright (c) 2013 B2CTUI.COM
 * $sn: origins/source/controller/home/frame.ctrl.php : v 79463ca277ed : 2014/06/13 00:48:48 : RenChao $
 */

defined('IN_IA') or exit('Access Denied');
checklogin();
$do = !empty($_GPC['do']) && in_array($_GPC['do'], array('profile', 'global')) ? $_GPC['do'] : 'profile';
if(empty($_W['account']) || empty($do)) {
	$do = 'global';
}

if($_GPC['iframe']) {
	$iframe = str_replace('&amp;', '&', $_GPC['iframe']);
} else {
	$iframe = $do == 'profile' ? '?act=welcome' : create_url('account');
}
$wechats = account_search();
$types = array();
$types['server'] = '我的服务';
$types['business'] = '行业应用';
$types['wdlshow'] = '展示应用';
$types['customer'] = '客户关系';
$types['activity'] = '营销活动';
$types['wdlgame'] = '游戏应用';
$types['services'] = '常用工具';
$types['other'] = '其他';

$mset = array();
if($do == 'profile') {
	$mset['basic'] = array('title' => '基本设置');
	foreach($types as $k => $v) {
		$mset[$k] = array('title' => $v, 'menus' => array());
	}
	$ms = array();
	if (!empty($_W['account']['modules'])) {
		$bindings = pdo_fetchall('SELECT * FROM ' . tablename('modules_bindings')." ORDER BY eid ASC");
		foreach($_W['account']['modules'] as $m) {
			$row = array('name' => strtolower($m['name']));
			$mg = $_W['modules'][$row['name']];
			if(in_array($row['name'], array('basic', 'news', 'music', 'userapi'))) {
				continue;
			}
			if(!empty($bindings)) {
				foreach($bindings as $entry) {
					if(strtolower($entry['module']) == $row['name']) {
						$m[$entry['entry']][] = array_elements(array('eid', 'call', 'title', 'do', 'direct', 'state'), $entry);
					}
				}
			}

			$row['title'] = $mg['title'];
			$row['type'] = $mg['type'];
			$row['type'] = in_array($row['type'], array_keys($types)) ? $row['type'] : 'other';
			if(!empty($m['cover']) && is_array($m['cover'])) {
				foreach($m['cover'] as $opt) {
					if(!empty($opt['call'])) {
						$site = WeUtility::createModuleSite($row['name']);
						if(method_exists($site, $opt['call'])) {
							$ret = $site->$opt['call']();
							if(is_array($ret)) {
								foreach($ret as $et) {
									$row['items'][] = array($et['title'], $et['url']);
								}
							}
						}
					} else {
						$row['items'][] = array(
							$opt['title'],
							create_url("rule/cover", array('eid' => $opt['eid']))
						);
					}
				}
			}
			if(!empty($m['rule']) || $mg['isrulefields']) {
				$row['items'][] = array(
					'关键字触发列表',
					create_url('rule/display', array('module' => $row['name'])),
					'childItems' => array('<i class="icon-plus"></i>', create_url('rule/post', array('module' => $row['name']))),
				);
			}
			if(!empty($m['home']) || !empty($m['profile']) || !empty($m['shortcut'])) {
				$row['items'][] = array(
					'微站导航设置',
					create_url('site/nav', array('name' => $row['name'])),
				);
			}
			if(!empty($m['menu']) && is_array($m['menu'])) {
				foreach($m['menu'] as $opt) {
					if(!empty($opt['call'])) {
						$site = WeUtility::createModuleSite($row['name']);
						if(method_exists($site, $opt['call'])) {
							$ret = $site->$opt['call']();
							if(is_array($ret)) {
								foreach($ret as $et) {
									$row['items'][] = array($et['title'], $et['url']);
								}
							}
						}
					} else {
						$row['items'][] = array(
							$opt['title'],
							create_url("site/entry", array('eid' => $opt['eid']))
						);
					}
				}
			}
			if($mg['settings']) {
				$row['items'][] = array('参数设置', create_url('member/module/setting', array('mid' => $mg['mid'])));
			}
			$ms[strtolower($m['name'])] = $row;
		}
	}
	foreach($ms as $m) {
		if($m['items']) {
			$mset[$m['type']]['menus'][] = array('title' => $m['title'], 'items' => $m['items']);
		}
	}

	$menus = array();
	$menus[] = array(
		'title' => '自动回复',
		'items' => array(
			array(
				'文字回复',
				create_url('rule/display', array('module' => 'basic')),
				'childItems' => array('<i class="icon-plus"></i>', create_url('rule/post', array('module' => 'basic'))),
			),
			array(
				'图文回复',
				create_url('rule/display', array('module' => 'news')),
				'childItems' => array('<i class="icon-plus"></i>', create_url('rule/post', array('module' => 'news'))),
			),
			array(
				'音乐回复',
				create_url('rule/display', array('module' => 'music')),
				'childItems' => array('<i class="icon-plus"></i>', create_url('rule/post', array('module' => 'music'))),
			),
			array(
				'自定义接口回复',
				create_url('rule/display', array('module' => 'userapi')),
				'childItems' => array('<i class="icon-plus"></i>', create_url('rule/post', array('module' => 'userapi'))),
			),
			array('常用服务接入', create_url('site/module/switch', array('name' => 'userapi'))),
			array('自定义菜单', create_url('menu')),
			array('特殊回复', create_url('rule/system')),
		)
	);
	$sMenus = array(
		'title' => '微站功能',
		'items' => array(
			array('网站风格设置', create_url('site/style')),
			array('微站栏目设置', create_url('site/nav')),
			array('底部菜单设置', create_url('site/nav', array('position' => 3))),
			array('个人中心菜单设置', create_url('site/nav', array('position' => 2))),
			array('幻灯片设置', create_url('site/slide')),
			array('微站访问入口', create_url('site/cover')),
			array('微站文章设置', create_url('site/article')),
			array('页面信息设置', create_url('site/siteinfo')),
		)
	);
	$menus[] = $sMenus;

	$menus[] = array(
		'title' => '参数设置&其他',
		'items' => array(
			array('公众号资料', create_url('account/post', array('id' => 'current'))),
			//array('管理人员列表', create_url('account/worker')),
			array('模块列表', create_url('member/module')),
			array('支付参数', create_url('account/payment')),
			array('其他参数', create_url('account/advance')),
		)
	);
	$mset['basic']['menus'] = $menus;
}
if($do == 'global') {
	$menus = array();
	$menus[] = array('title' => array('公众号管理', create_url('account/display')));
	if (!empty($_W['isfounder'])) {
		$extends = array(
			'title' => '扩展',
			'items' => array()
		);
		$extends['items'][] = array('模块列表', create_url('extension/module'));
		$extends['items'][] = array('风格主题', create_url('extension/theme'));
		$extends['items'][] = array('常用服务', create_url('extension/service/display'));
		$menus[] = $extends;
		
		$user = array(
			'title' => '多用户管理',
			'items' => array()
		);
		$user['items'][] = array('资料设置', create_url('member/fields'));
		$user['items'][] = array('注册设置', create_url('setting/register'));
		$user['items'][] = array('用户管理', create_url('member/display'), 'childItems' => array('<i class="icon-plus"></i>', create_url('member/create')));
		$user['items'][] = array('用户组管理', create_url('member/group'), 'childItems' => array('<i class="icon-plus"></i>', create_url('member/group/post')));
		$menus[] = $user;
		
		$update = array(
		'title' => '一键更新',
		'items' => array()
		);
		$update['items'][] = array('立即更新', create_url('update/update'));
		$menus[] = $update;
		
		//$service = array(
			//'title' => '云服务',
			//'items' => array()
		//);
		//$service['items'][] = array('自动更新', create_url('cloud/upgrade'));
		//$service['items'][] = array('我的推广页面', create_url('cloud/promotion'));
		//$service['items'][] = array('站点注册资料', create_url('cloud/profile'));
		//$service['items'][] = array('云服务状态诊断', create_url('cloud/diagnose'));
		//$menus[] = $service;
	}
	$system = array(
		'title' => '系统管理',
		'items' => array(
			array('账户管理', create_url('setting/profile')),
		)
	);
	$system['items'][] = array('更新缓存', create_url('setting/updatecache'));
	if (!empty($_W['isfounder'])) {
		$system['items'][] = array('版权及站点设置', create_url('setting/copyright'));
		$system['items'][] = array('数据库', create_url('setting/database'));
		$system['items'][] = array('工具', create_url('setting/tools'));
		$system['items'][] = array('其它设置', create_url('setting/common'));
	}
	$menus[] = $system;
	$mset[] = array(
		'title' => '全局设置',
		'menus' => $menus
	);
}
template('home/frame');
