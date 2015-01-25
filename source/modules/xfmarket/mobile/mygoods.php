<?php
	global $_W, $_GPC;
		$pindex= max(1, intval($_GPC['page']));
		$psize= 2;
		$list= pdo_fetchall("SELECT * FROM".tablename($this->goods)."WHERE openid='{$_W['fans']['from_user']}' LIMIT ".($pindex -1) * $psize.','.$psize);
		$total= pdo_fetchcolumn("SELECT COUNT(*) FROM".tablename($this->goods)."WHERE openid='{$_W['fans']['from_user']}'");
		$pager= pagination($total, $pindex, $psize);
		if($_GPC['op'] == 'delete') {
			pdo_delete($this->goods, array('id' => $_GPC['id']));
			message('删除成功', referer(), 'success');
		}
		include $this->template('mygoods');
	   
?>