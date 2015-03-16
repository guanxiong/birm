<?php
/*
小区活动



*/
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$id = intval($_GPC['id']);
if($operation == 'post'){
	if (!empty($id)) {
		$item = pdo_fetch("SELECT * FROM".tablename('xcommunity_activity')."WHERE id='{$id}'");
	}
	$starttime = strtotime($_GPC['starttime']);
	$endtime   = strtotime($_GPC['endtime']);
	if (!empty($starttime) && $starttime==$endtime) {
		$endtime = $endtime+86400-1;
	}
	$data = array(
			'weid'       => $_W['weid'],
			'title'      => $_GPC['title'],
			'starttime'  => $starttime,
			'endtime'    => $endtime,
			'enddate'    => $_GPC['enddate'],
			'picurl'     => $_GPC['picurl'],
			'number'     => !empty($_GPC['number'])?$_GPC['number']:'1',
			'content'    => htmlspecialchars_decode($_GPC['content']),
			'status'     => $_GPC['status'],
			'createtime' => TIMESTAMP,
		);
	if ($_W['ispost']) {
		if (empty($_GPC['id'])) {
			pdo_insert('xcommunity_activity',$data);
		}else{
			pdo_update('xcommunity_activity',$data,array('id' => $_GPC['id']));
		}
		message('更新成功',referer(),'success');
	}
}elseif($operation == 'display'){
	$pindex = max(1, intval($_GPC['page']));
	$psize  = 20;
	$condition = '';
	$params = array();
	if (!empty($_GPC['keyword'])) {
		$condition .= " AND title LIKE :keyword";
		$params[':keyword'] = "%{$_GPC['keyword']}%";
	}
	$list = pdo_fetchAll("SELECT * FROM".tablename('xcommunity_activity')."WHERE weid='{$_W['weid']}' $condition LIMIT ".($pindex - 1) * $psize.','.$psize);
	$total =pdo_fetchcolumn("SELECT COUNT(*) FROM".tablename('xcommunity_activity')."WHERE weid='{$_W['weid']}'");
	$pager  = pagination($total, $pindex, $psize);
}elseif($operation == 'delete'){
	pdo_delete('xcommunity_activity',array('id' => $id));
	message('删除成功',referer(),'success');
}elseif ($operation == 'res') {
	$pindex = max(1, intval($_GPC['page']));
	$psize  = 20;
	$condition = '';
	$params = array();
	if (!empty($_GPC['keyword'])) {
		$condition .= " AND title LIKE :keyword";
		$params[':keyword'] = "%{$_GPC['keyword']}%";
	}
	$list = pdo_fetchAll("SELECT * FROM".tablename('xcommunity_res')." WHERE weid='{$_W['weid']}' $condition LIMIT ".($pindex - 1) * $psize.','.$psize);
	$total = pdo_fetchcolumn("SELECT COUNT(*) FROM".tablename('xcommunity_res')."WHERE weid='{$_W['weid']}'");
	$pager  = pagination($total, $pindex, $psize);
	$row = array();
	foreach ($list as $key => $value) {
		$rows = pdo_fetch("SELECT * FROM".tablename('xcommunity_activity')."WHERE id='{$value['rid']}'");
		$row[]= array(
				'truename'   => $value['truename'],
				'mobile'     => $value['mobile'],
				'num'        => $value['num'],
				'sex'        => $value['sex'],
				'createtime' => $value['createtime'],
				'title'      => $rows['title'],
				'id'         => $value['id'],
			);
	}
	if (checksubmit('delete')) {
		pdo_delete('xcommunity_res', " id  IN  ('".implode("','", $_GPC['select'])."')");
		message('删除成功！',referer(),'success');
	}
}
include $this->template('activity');