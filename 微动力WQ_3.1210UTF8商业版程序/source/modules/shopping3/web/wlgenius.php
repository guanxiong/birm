<?php
/**
 * 2014-2-24
 * 购物车 商品管理
 * 
 * @author 微动力
 * @url
 */
$table_goods=tablename('shopping3_goods');
$table_category=tablename('shopping3_category');
$table_genius=tablename('shopping3_genius');

$category = pdo_fetchall("SELECT * FROM ".$table_category." WHERE weid = '{$_W['weid']}' ORDER BY parentid ASC, displayorder DESC", array(), 'id');
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
if ($operation == 'post') {
	$id = intval($_GPC['id']);
	if (!empty($id)) {
		$item = pdo_fetch("SELECT * FROM ".$table_genius." WHERE weid=".$_W['weid']." AND id = :id" , array(':id' => $id));
		if (empty($item)) {
			message('抱歉，商品不存在或是已经删除！', '', 'error');
		}else{
	 
			if(!empty($item['dishes'])){
				$item['dishes']= iunserializer($item['dishes']);	
			}
		}
		 
	}else{
		$item=array(
			'displayorder'=>0,
			'status'=>1,
			'total'=>-1,
		);
	}
	if (checksubmit('submit')) {
		if (empty($_GPC['rens'])) {
			message('用餐人数不能为空.');
		}
		if (empty($_GPC['dishes'])) {
			message('套餐菜品不能为空');
		}
		$num=0;
		foreach($_GPC['dishes']['num'] as $row){
			$num+=$row;
		}
		$data = array(
			'weid' => intval($_W['weid']),
			'displayorder' => intval($_GPC['displayorder']),
			'rens' => $_GPC['rens'],
   			'status' => intval($_GPC['status']),
			'nums'=>$num,
			'sort'=>count($_GPC['dishes']['title']),
			'dishes' => iserializer($_GPC['dishes']),
		);
		//缩略图
		
 
		if (empty($id)) {
			pdo_insert('shopping3_genius', $data);
		} else {
 			pdo_update('shopping3_genius', $data, array('id' => $id));
		}
		message('智能选菜更新成功！', $this->createWebUrl('genius', array('op' => 'display')), 'success');
	}
} elseif ($operation == 'display') {
 
	$pindex = max(1, intval($_GPC['page']));
	$psize = 20;
	$condition = '';

	$list = pdo_fetchall("SELECT * FROM ".$table_genius." WHERE weid = '{$_W['weid']}' $condition ORDER BY status DESC, displayorder DESC, id DESC LIMIT ".($pindex - 1) * $psize.','.$psize);
	$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . $table_genius . " WHERE weid = '{$_W['weid']}'");
	$pager = pagination($total, $pindex, $psize);
} elseif ($operation == 'delete') {
	$id = intval($_GPC['id']);
	$row = pdo_fetch("SELECT id, thumb FROM ".$table_genius." WHERE id = :id", array(':id' => $id));
	if (empty($row)) {
		message('抱歉，商品不存在或是已经被删除！');
	}
	if (!empty($row['thumb'])) {
		file_delete($row['thumb']);
	}
	pdo_delete($table_genius, array('id' => $id));
	message('删除成功！', referer(), 'success');
}elseif ($operation == 'query') {
	if(!empty($_GPC['classid'])){
		$condition=" AND pcate=".$_GPC['classid']." ";
	}else{
		$condition="";
	}
	$pindex = max(1, intval($_GPC['page']));
	$psize = 20;
	$list = pdo_fetchall("SELECT id,title FROM ".$table_goods." WHERE weid = '{$_W['weid']}' AND status=1  $condition ORDER BY  displayorder DESC, id DESC LIMIT ".($pindex - 1) * $psize.','.$psize);
	$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . $table_goods . " WHERE weid = '{$_W['weid']}' AND status=1  $condition");
	//$pager = pagination($total, $pindex, $psize);
	
	include $this->template('web/genius_query');
	exit;
}
include $this->template('web/genius');