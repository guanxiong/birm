<?php
/**
 * 砸蛋抽奖模块
 *
 * [WDL] Copyright (c) 2013 B2CTUI.COM
 */
defined('IN_IA') or exit('Access Denied');

class CydModuleSite extends WeModuleSite {

	public function doMobileLottery() {
		global $_GPC, $_W;

		$id = intval($_GPC['id']);
		$title = '茶叶蛋';
		$from_user = $_W['fans']['from_user'];
		
		if (!empty($from_user)) {
			//message('非法访问，请重新发送消息进入抽奖页面！');
			fans_require($from_user, array('realname', 'mobile', 'qq'), '需要完善资料后才能砸蛋.');
		}
		
//		$realname = pdo_fetchcolumn("SELECT realname FROM". tablename('fans'). "WHERE from_user ='". $from_user. "'");

		
		
		// if(empty($realname)){
			// include $this->template('register');
			// return;
		// }

		include $this->template('lottery');
	}

	public function doMobileRegister() {
		global $_GPC, $_W;
		$title = '茶叶蛋登记个人信息';
		if (!empty($_GPC['submit'])) {

			$data = array(
				'realname' => $_GPC['realname'],
				'mobile' => $_GPC['mobile'],
				'qq' => $_GPC['qq'],
			);

			if (empty($data['realname'])) {
				die('<script>alert("请填写您的真实姓名！");location.reload();</script>');
			}
			if (empty($data['mobile'])) {
				die('<script>alert("请填写您的手机号码！");location.reload();</script>');
			}
			fans_update($_W['fans']['from_user'], $data);
			die('<script>alert("登记成功！");location.href = "'.$this->createMobileUrl('lottery', array('id' => $_GPC['id'])).'";</script>');
		}
		include $this->template('register');
	}

	public function doMobilePrompt(){
		global $_GPC, $_W;

		// if (empty($_W['fans']['from_user'])) {
			// message('非法访问，请重新发送消息进入砸蛋页面！');
		// }
		
		$fromuser = $_W['fans']['from_user'];
		$id = intval($_GPC['id']);

		$data = array(
			'rid' => $id,
			'from_user' => $fromuser,
			'status' => empty($gift['inkind']) ? 1 : 0,
			'createtime' => TIMESTAMP,
		);
		
		pdo_insert('cyd_winner', $data);
		
		$user_num = pdo_InsertId();
		$user_name = pdo_fetchcolumn("SELECT realname FROM ".tablename('fans'). "WHERE from_user = '". $fromuser. "'");
		
		
		include $this->template('prompt');
	}
}
