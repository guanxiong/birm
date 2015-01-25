<?php	
	  global $_W, $_GPC;
		$id= intval($_GPC['id']);
		$detail= pdo_fetch("SELECT * FROM".tablename($this->goods)."WHERE id='{$id}'");		 
		$title= $detail['title'];
		$_share_img= $_W['attachurl'].$detail['thumb1'];
		include $this->template('detail');
?>