<?php 
/**
 * 获取用户公众号信息
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');
$account = pdo_fetchall("SELECT weid, name FROM ".tablename('wechats') . (empty($_W['isfounder']) ? " WHERE uid = '{$_W['uid']}'" : ''));
isetcookie('wechatloaded', '1');
message($account, '', 'ajax');