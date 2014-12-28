<?php
function travel_article_search($cid, $type = '', $psize = 20, $orderby = 'id DESC') {
	global $_GPC, $_W;
	$pindex = max(1, intval($_GPC['page']));
	$result = array();
	$condition = " WHERE weid = '{$_W['weid']}' AND ";
	if(!empty($cid)) {
		$category = pdo_fetch("SELECT parentid FROM ".tablename('article_category')." WHERE id = '{$cid}'");
		if (!empty($category['parentid'])) {
			$condition .= "ccate = '{$cid}'";
		} else {
			$condition .= "pcate = '{$cid}'";
		}
	}
	if(!empty($cid) && !empty($type)) $condition .= " OR ";
	if (!empty($type)) {
		$type = explode(',', $type);
		foreach ($type as $item) {
			$condition .= "type LIKE '%$item,%'";
		}
	}
	$sql = "SELECT * FROM ".tablename('travel'). $condition. ' ORDER BY '. $orderby;
	$result['list'] = pdo_fetchall($sql . " LIMIT " . ($pindex - 1) * $psize .',' .$psize);
	$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('travel') . $condition);
	$result['pager'] = pagination($total, $pindex, $psize);
	return $result;
}

function travel_category_name_search($id) {
	$category = pdo_fetch("SELECT name FROM ".tablename('article_category')." WHERE id = '$id'");
	return $category['name'];
}