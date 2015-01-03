<?php
/**
 * 砸蛋抽奖模块
 *
 * [WNS]更多模块请浏览：BBS.birm.co
 */
defined('IN_IA') or exit('Access Denied');

class LxysigninModuleSite extends WeModuleSite {

	
	public function doMobileRegister() {
		global $_GPC, $_W;
		if (!empty($_GPC['submit'])) {
			if (empty($_W['fans']['from_user'])) {
				message('非法访问，请重新发送消息进入砸蛋页面！');			}

			$data = array(
				'nickname'=>$_GPC['nickname'],
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
			pdo_delete('lxy_signin_record', " id IN ('".implode("','", $_GPC['select'])."')");			
			message('删除成功！', $this->createWebUrl('display', array('id' => $id, 'page' => $_GPC['page'])));		}
		$pindex = max(1, intval($_GPC['page']));		
		$psize = 15;
		$signinlist = pdo_fetchall('SELECT * FROM '.tablename('lxy_signin_record').' WHERE weid= :weid order by `id` desc LIMIT ' . ($pindex - 1) * $psize . ',' . $psize, array(':weid' => $_W['weid']) );	
		$total = pdo_fetchcolumn('SELECT count(1) as totle FROM '.tablename('lxy_signin_record').' WHERE weid= :weid order by `id` desc ', array(':weid' => $_W['weid']) );
		$pager = pagination($total, $pindex, $psize);
		include $this->template('display');
	}


	public function doMobileSuccess() {
		include $this->template('success');
	}

}
