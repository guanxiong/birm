<?php
/*
小区--二手市场转让和求购处理

*/
$op = $_GPC['op'];
//查分类
$categories = pdo_fetchall("select * from".tablename('xcommunity_servicecategory')."where parentid = 5 and weid='{$_W['weid']}'");
//print_r($categories);
if ($op == 'sell') {
	if (!empty($_GPC['id'])) {
		$good = pdo_fetch("SELECT * FROM".tablename('xcommunity_fled')."WHERE id='{$_GPC['id']}'");
	}
	$data = array(
			'weid'        => $_GPC['weid'],
			'openid'      => $_W['fans']['from_user'],
			'rolex'       => $_GPC['rolex'],
			'title'       => $_GPC['title'],
			'category'    => $_GPC['category'],
			'yprice'      => $_GPC['yprice'],
			'zprice'      => $_GPC['zprice'],
			'description' => $_GPC['description'],
			'realname'    => $_GPC['realname'],
			'mobile'      => $_GPC['mobile'],
			'createtime'  => TIMESTAMP,
			'status'      => 1,
		);
	//处理图片1
		if (!empty($_FILES['thumb1']['tmp_name'])) {
			file_delete($_GPC['thumb1-old']);
			$upload = file_upload($_FILES['thumb1']);
			if (is_error($upload)) {
				message($upload['message'], '', 'error');
			}
			$data['thumb1'] = $upload['path'];
		}
			
		//处理图片2
		if (!empty($_FILES['thumb2']['tmp_name'])) {
			file_delete($_GPC['thumb2-old']);
			$upload = file_upload($_FILES['thumb2']);
			if (is_error($upload)) {
				message($upload['message'], '', 'error');
			}
			$data['thumb2'] = $upload['path'];
		}
			
		//处理图片3
		if (!empty($_FILES['thumb3']['tmp_name'])) {
			file_delete($_GPC['thumb3-old']);
			$upload = file_upload($_FILES['thumb3']);
			if (is_error($upload)) {
				message($upload['message'], '', 'error');
			}
			$data['thumb3'] = $upload['path'];
		}
		//处理图片4
		if (!empty($_FILES['thumb4']['tmp_name'])) {
			file_delete($_GPC['thumb4-old']);
			$upload = file_upload($_FILES['thumb4']);
			if (is_error($upload)) {
				message($upload['message'], '', 'error');
			}
			$data['thumb4'] = $upload['path'];
		}
		if ($_W['ispost']) {
			if (empty($_GPC['id'])) {
				pdo_insert('xcommunity_fled',$data);
			}else{
				pdo_update('xcommunity_fled',$data,array('id' => $_GPC['id']));
			}
			message('发布成功',$this->createMobileUrl('index',array('status' => '1')),'success');
		}
}elseif ($op == 'buy') {
	if (!empty($_GPC['id'])) {
		$good = pdo_fetch("SELECT * FROM".tablename('xcommunity_fled')."WHERE id='{$_GPC['id']}'");
	}
	$data = array(
			'weid'        => $_W['weid'],
			'openid'      => $_W['fans']['from_user'],
			'title'       => $_GPC['title'],
			'description' => $_GPC['description'],
			'zprice'      => $_GPC['zprice'],
			'rolex'       => $_GPC['rolex'],
			'category'    => $_GPC['category'],
			'realname'    => $_GPC['realname'],
			'mobile'      => $_GPC['mobile'],
			'createtime'  => TIMESTAMP,
			'status'      => 2,
		);
	if ($_W['ispost']) {
		if (empty($_GPC['id'])) {
			pdo_insert('xcommunity_fled',$data);
		}else{
			pdo_update('xcommunity_fled',$data,array('id' => $_GPC['id']));
		}
		message('发布成功',$this->createMobileUrl('index',array('status' => '2')),'success');
	}
}

include $this->template('add');