<?php
/**
 * 2014-2-24
 * 购物车 分类管理 
 * 支持二级分类 来自微擎
 * @author 微动力
 * @url
 */
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
if($operation=='display'){
	$list = pdo_fetchall("SELECT * FROM ".tablename('shopping3_express')." WHERE weid = '{$_W['weid']}' ORDER BY displayorder DESC");
}elseif($operation=='post'){
	$id = intval($_GPC['id']);
	if (checksubmit('submit')) {
		if (empty($_GPC['express_name'])) {
			message('抱歉，请输入物流名称！');
		}
		/* if (empty($_GPC['express_price'])) {
			message('抱歉，请输入物流价格！');
		} */
		
		
		$data = array(
			'weid' => $_W['weid'],
			'express_name' => $_GPC['express_name'],
			'displayorder' => intval($_GPC['displayorder']),
			'express_price' =>floatval($_GPC['express_price']),
			'express_area' => $_GPC['express_area'],

		);
		if (!empty($id)) {
			pdo_update('shopping3_express', $data, array('id' => $id));
		} else {
			pdo_insert('shopping3_express', $data);
			$id = pdo_insertid();
		}
		message('更新物流成功！', $this->createWebUrl('express', array('op' => 'display')), 'success');
	}
	
	if($id==0){
		//添加
		$express=array(
			'displayorder'=>0,
		);
	}else{
		//修改
		$express = pdo_fetch("SELECT * FROM ".tablename('shopping3_express')." WHERE id = '$id'");
	}
	if (empty($express)) {
		message('抱歉，物流方式不存在！', $this->createWebUrl('express', array('op' => 'display')), 'error');
	}
}elseif ($operation == 'delete') {
	$id = intval($_GPC['id']);
	$express = pdo_fetch("SELECT id  FROM ".tablename('shopping3_express')." WHERE id = '$id' AND weid=".$_W['weid']."");
	if (empty($express)) {
		message('抱歉，物流方式不存在或是已经被删除！', $this->createWebUrl('express', array('op' => 'display')), 'error');
	}
	pdo_delete('shopping3_express', array('id' => $id));
	message('物流方式删除成功！', $this->createWebUrl('express', array('op' => 'display')), 'success');
}else{
	message('请求方式不存在');
}
include $this->template('web/express');