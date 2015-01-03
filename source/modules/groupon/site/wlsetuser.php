<?php
/**
 * 详情
 *
 * @author 超级无聊
 * @url
 */
		$users = fans_search($_W['fans']['from_user'], array('realname', 'mobile'));
	include $this->template('wl_setuser');