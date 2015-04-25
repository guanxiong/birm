<?php
/**
 * 2014-2-24
 * 购物车 商品管理
 * 
 * @author 微动力
 * @url
 */$category = pdo_fetchall("SELECT * FROM ".tablename('shopping3_category')." WHERE weid = '{$_W['weid']}' ORDER BY parentid ASC, displayorder DESC", array(), 'id');
if (!empty($category)) {
	$children = '';
	foreach ($category as $cid => $cate) {
		if (!empty($cate['parentid'])) {
			$children[$cate['parentid']][$cate['id']] = array($cate['id'], $cate['name']);
		}
	}
}
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
if ($operation == 'post') {
	$id = intval($_GPC['id']);
	if (!empty($id)) {
		$item = pdo_fetch("SELECT * FROM ".tablename('shopping3_goods')." WHERE id = :id" , array(':id' => $id));
		if (empty($item)) {
			message('抱歉，商品不存在或是已经删除！', '', 'error');
		}else{
			if(!empty($item['thumb_url'])){
				$item['thumbArr']=explode('|',$item['thumb_url']);	
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
		if (empty($_GPC['goodsname'])) {
			message('请输入商品名称！');
		}
		if (empty($_GPC['pcate'])) {
			message('请选择商品分类！');
		}
		if (empty($_GPC['marketprice'])) {
			message('请输入商品优惠价');
		}
		if (empty($_GPC['productprice'])) {
			message('请输入商品原价');
		}
		$data = array(
			'weid' => intval($_W['weid']),
			'displayorder' => intval($_GPC['displayorder']),
			'title' => $_GPC['goodsname'],
			'pcate' => intval($_GPC['pcate']),
			'ccate' => intval($_GPC['ccate']),
			'type' => intval($_GPC['type']),
			'status' => intval($_GPC['status']),
			'isindex' => intval($_GPC['isindex']),
			'description' => $_GPC['description'],
			'content' => htmlspecialchars_decode($_GPC['content']),
			'productsn' => $_GPC['productsn'],
			'goodssn' => $_GPC['goodssn'],
			'marketprice' => $_GPC['marketprice'],
			'productprice' => $_GPC['productprice'],
			'total' => intval($_GPC['total']),
			'unit' => $_GPC['unit'],
			'label'=> $_GPC['label'],
			'createtime' => TIMESTAMP,
			'sellnums'=>$_GPC['sellnums'],
		);
		//缩略图
		
		
		if(!empty($_GPC['thumb_url'])){
			$data['thumb']=$_GPC['thumb_url'][0];  
			$data['thumb_url']=implode('|',$_GPC['thumb_url']);
		}else{
			$data['thumb']=NULL;  
			$data['thumb_url']=NULL;
		}
		if (empty($id)) {
			pdo_insert('shopping3_goods', $data);
		} else {
			unset($data['createtime']);
			pdo_update('shopping3_goods', $data, array('id' => $id));
		}
		message('商品更新成功！', $this->createWebUrl('goods', array('op' => 'display')), 'success');
	}
} elseif ($operation == 'display') {
	if (!empty($_GPC['displayorder'])) {
		foreach ($_GPC['displayorder'] as $id => $displayorder) {
			pdo_update('shopping3_goods', array('displayorder' => $displayorder), array('id' => $id));
		}
		message('商品排序更新成功！', $this->createWebUrl('Goods', array('op' => 'display')), 'success');
	}
	$pindex = max(1, intval($_GPC['page']));
	$psize = 20;
	$condition = '';
	if (!empty($_GPC['keyword'])) {
		$condition .= " AND title LIKE '%{$_GPC['keyword']}%'";
	}

	if (!empty($_GPC['cate_2'])) {
		$cid = intval($_GPC['cate_2']);
		$condition .= " AND ccate = '{$cid}'";
	} elseif (!empty($_GPC['cate_1'])) {
		$cid = intval($_GPC['cate_1']);
		$condition .= " AND pcate = '{$cid}'";
	}

	if (isset($_GPC['status'])) {
		$condition .= " AND status = '".intval($_GPC['status'])."'";
	}

	$list = pdo_fetchall("SELECT * FROM ".tablename('shopping3_goods')." WHERE weid = '{$_W['weid']}' $condition ORDER BY status DESC, displayorder DESC, id DESC LIMIT ".($pindex - 1) * $psize.','.$psize);
	$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('shopping3_goods') . " WHERE weid = '{$_W['weid']}'");
	$pager = pagination($total, $pindex, $psize);
} elseif ($operation == 'delete') {
	$id = intval($_GPC['id']);
	$row = pdo_fetch("SELECT id, thumb FROM ".tablename('shopping3_goods')." WHERE id = :id", array(':id' => $id));
	if (empty($row)) {
		message('抱歉，商品不存在或是已经被删除！');
	}
	if (!empty($row['thumb'])) {
		file_delete($row['thumb']);
	}
	pdo_delete('shopping3_goods', array('id' => $id));
	message('删除成功！', referer(), 'success');
}
include $this->template('web/goods');