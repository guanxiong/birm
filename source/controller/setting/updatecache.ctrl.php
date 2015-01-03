<?php
/**
 * 更新系统配置
 * 更新模板缓存
 * 更新模块挂勾
 * ...
 * [WNS] Copyright (c) 2013 BIRM.CO
 */

include_once model('cache');
include_once model('setting');

if (checksubmit('submit')) {
	//cache_build_announcement();
	cache_build_template();
	cache_build_modules();
	cache_build_fans_struct();
	cache_build_setting();
	message('缓存更新成功！', create_url('setting/updatecache'));
} else {
	template('setting/updatecache');
}
