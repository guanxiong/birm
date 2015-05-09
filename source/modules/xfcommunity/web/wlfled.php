<?php
/*
二手交易

*/
$op = !empty($_GPC['op']) ? $_GPC['op'] :'sell';
if ($op == 'sell') {
	$pindex = max(1, intval($_GPC['page']));
	$psize  = 10;
	$list = pdo_fetchall("SELECT * FROM".tablename('xcommunity_fled')."WHERE status='1' AND weid='{$_W['weid']}' LIMIT ".($pindex - 1) * $psize.','.$psize);
	$total = pdo_fetchcolumn("SELECT COUNT(*) FROM".tablename('xcommunity_fled')."WHERE status='1' AND weid='{$_W['weid']}'");
	$pager  = pagination($total, $pindex, $psize);
}elseif($op == 'buy'){
	$pindex = max(1, intval($_GPC['page']));
	$psize  = 10;
	$list = pdo_fetchall("SELECT * FROM".tablename('xcommunity_fled')."WHERE status='2' AND weid='{$_W['weid']}' LIMIT ".($pindex - 1) * $psize.','.$psize);
	$total = pdo_fetchcolumn("SELECT COUNT(*) FROM".tablename('xcommunity_fled')."WHERE status='2' AND weid='{$_W['weid']}'");
	$pager  = pagination($total, $pindex, $psize);
}

if ($_GPC['foo'] == 'delete') {
	pdo_delete('xcommunity_fled',array('id' => $_GPC['id']));
	message('删除成功',referer(),'success');
}

include $this->template('fled');