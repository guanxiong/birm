<?php
/**
 * 首页
 *
 * @author 超级无聊
 * @url
 */
 	$tid = intval($_GPC['tid']);
	$num=intval($_GPC['num']);
	$groupon = pdo_fetch("SELECT * FROM ".tablename('groupon_list')." WHERE id={$tid} AND weid = '{$weid}' AND status = '1' ORDER BY listorder DESC, id DESC LIMIT 8");
 	if (empty($groupon)) {
		message('抱歉，商品不存在或是已经被删除！');
	}else{
		//2个判断，第一个判断，产品数量是否超过了个人限购量
		if($groupon['limit_num']<$num && $groupon['limit_num']!=0){
		//2个判断，第二个判断，产品数量是否超过了个人历史限购量
			message('抱歉，商品每人只能限购'+$groupon['limit_num']+'件！');
		}
			
		$ordernum = pdo_fetchcolumn("SELECT sum(totalnum) FROM ".tablename('groupon_order')." WHERE tid={$tid} AND weid = '{$weid}' AND ispay =1 AND from_user='{$from}'");
	 
		if($groupon['limit_num']<($ordernum+$num) && $groupon['limit_num']!=0){
		//2个判断，第二个判断，产品数量是否超过了个人历史限购量
			message('抱歉，商品每人只能限购'.$groupon['limit_num'].'件,您已经购买过'.$ordernum.'件！');
		}
	}
	$payset=pdo_fetch("SELECT * FROM ".tablename('groupon_set')." WHERE  weid = '{$_W['weid']}'");
 	include $this->template('wl_confirm');