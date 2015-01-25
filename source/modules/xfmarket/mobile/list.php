<?php
	
	  global $_GPC, $_W;
		//必须关注
		$this->checkAuth();
		//必须关注
		$pcate= intval($_GPC['pcate']);
		//分类显示
		$categorys= pdo_fetchall("SELECT * FROM".tablename('xfmarket_category')."WHERE weid='{$_W['weid']}' AND enabled='1'");
		//分享数据
		$rid= intval($_GPC['rid']);
		//if (!empty($rid)) {
		$reply= pdo_fetch("SELECT * FROM ".tablename('xfmarket_reply')." WHERE rid = :rid ", array(':rid' => $rid));
		$sharepic= $_W['attachurl'].$reply['picture'];
		$description= $reply['description'];
		$title= $reply['title'];
		//}
		if(!empty($_GPC['keyword'])) {
			$keyword= "%{$_GPC['keyword']}%";
			$condition= " AND title LIKE '{$keyword}'";
		}
		$st= '';
		if(!empty($this->module['config']['status'])) {
			$st= " AND status='1' ";

		}
		if(empty($pcate)) {
			$list= pdo_fetchall("SELECT * FROM ".tablename($this->goods)." WHERE weid='{$_W['weid']}' $st $condition");

		} else {
			$list= pdo_fetchall("SELECT * FROM".tablename($this->goods)."WHERE weid='{$_W['weid']}' AND pcate='{$pcate}' $st $condition");
		}

		include $this->template('list');
?>