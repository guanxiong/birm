<?php
/**
 * 情侣去死
 *
 * 疯狂却不失细腻——厦门火池网络		www.weixiamen.cn
 */
defined('IN_IA') or exit('Access Denied');

class hlqixiModuleSite extends WeModuleSite {
	
	public function doMobileIndex() {
		global $_GPC, $_W;
		$useragent = addslashes($_SERVER['HTTP_USER_AGENT']);
		if(strpos($useragent, 'MicroMessenger') === false && strpos($useragent, 'Windows Phone') === false ){
			//echo " 404,亲,那里来回那里去哦!";
			//exit;
		}
		$id = intval($_GPC['id']);
		$title = pdo_fetchcolumn("SELECT name FROM ".tablename('rule')." WHERE id = :rid LIMIT 1", array(':rid' => $id));
		
		$fromuser = $_W['fans']['from_user'];
		
		
		$hlqixi = pdo_fetch("SELECT * FROM ".tablename('hlqixi_reply')." WHERE rid = '$id' LIMIT 1");
		if (empty($hlqixi)) {
			message('非法访问，请重新发送消息进入抽奖页面！');
		}
		if(!empty($fromuser)){
			$profile = fans_require($fromuser, array('realname', 'mobile'), '需要完善资料后才能抽奖.');
		}
		
		$iserror=0;
		
		include $this->template('index');
	}

	

}
