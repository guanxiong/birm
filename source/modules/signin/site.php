<?php
	/**
	 * 签到模块
	 * 作者：艮随
	 */
defined('IN_IA') or exit('Access Denied');

class SigninModuleSite extends WeModuleSite {

	
	public function doMobileRegister() {

		global $_GPC, $_W;


		if (!empty($_GPC['submit'])) {

			if (empty($_W['fans']['from_user'])) {

				message('非法访问，请重新发送消息进入砸蛋页面！');

			}

			$data = array(

				'realname' => $_GPC['realname'],

				'mobile' => $_GPC['mobile'],

				'gender' => $_GPC['gender'],

			);

			fans_update($_W['fans']['from_user'], $data);

			die('<script>location.href = "'.$this->createMobileUrl('success').'";</script>');
		}

		include $this->template('register');
	}
	
	public function doWebDisplay(){
	
		global $_GPC, $_W;
		
		checklogin();
		
		$id = intval($_GPC['id']);

		if (checksubmit('delete')) {
		
			pdo_delete('signin_record', " id IN ('".implode("','", $_GPC['select'])."')");
			
			message('删除成功！', $this->createWebUrl('display', array('id' => $id, 'page' => $_GPC['page'])));
		}

		$pindex = max(1, intval($_GPC['page']));
		
		$psize = 15;

		$signinlist = pdo_fetchall('SELECT * FROM '.tablename('signin_record').' WHERE weid= :weid order by `id` desc LIMIT ' . ($pindex - 1) * $psize . ',' . $psize, array(':weid' => $_W['weid']) );	
		
		$signintotal = pdo_fetchall('SELECT * FROM '.tablename('signin_record').' WHERE weid= :weid order by `id` desc ', array(':weid' => $_W['weid']) );
		
		$total = count($signintotal);
		
		$pager = pagination($total, $pindex, $psize);
		
		include $this->template('display');
	
	}


	public function doMobileSuccess() {


		include $this->template('success');
	}

}
