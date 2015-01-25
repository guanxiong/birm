<?php
	
	 global $_W, $_GPC;
	 $categorys= pdo_fetchall("SELECT * FROM".tablename('xfmarket_category')."WHERE weid='{$_W['weid']}'");
	 $data= array('weid' => $_W['weid'], 
			'openid' => $_W['fans']['from_user'], 
			'title' => $_GPC['title'], 
			'rolex' => $_GPC['rolex'], 
			'price' => $_GPC['price'], 
			'realname' => $_GPC['realname'], 
			'sex' => $_GPC['sex'], 
			'mobile' => $_GPC['mobile'], 
			'description' => $_GPC['description'], 
			'createtime' => TIMESTAMP, 
			'pcate' => $_GPC['pcate'], 'status' => 0);
			
		
	require_once dirname(__FILE__).'/../phpthumb/ThumbLib.inc.php';
			
	//处理图片1
	$name = time(); 
	if(!empty($_FILES['thumb1']['tmp_name'])) {
		file_delete($_GPC['thumb1-old']);
		$upload= file_upload($_FILES['thumb1']);
		if(is_error($upload)) {
			message($upload['message'], '', 'error');
		}
		$data['thumb1']= $upload['path'];  
	}

	try { 
		$thumb1 = PhpThumbFactory::create($_W['attachurl'].$data['thumb1']); 
		$realpath = substr($data['thumb1'], 0, strrpos($data['thumb1'], '/')+1); 
		$thumb1->adaptiveResize(180, 240);
		$thumb1->save("resource/attachment/$realpath"."thumb".$name.".jpg");
		$data['thumb1_cover'] = $realpath."thumb".$name.".jpg";  
	} catch (Exception $e) { 
		// handle error here however you'd like 
	}  

	//处理图片2
	if(!empty($_FILES['thumb2']['tmp_name'])) {
		file_delete($_GPC['thumb2-old']);
		$upload= file_upload($_FILES['thumb2']);
		if(is_error($upload)) {
			message($upload['message'], '', 'error');
		}
		$data['thumb2']= $upload['path']; 
	}

	try{  
		$thumb2 = PhpThumbFactory::create($_W['attachurl'].$data['thumb2']);
		$realpath2 = substr($data['thumb2'], 0, strrpos($data['thumb2'], '/')+1); 
		$thumb2->adaptiveResize(180, 240);
		$thumb2->save("resource/attachment/$realpath2"."thumb2".$name.".jpg");
		$data['thumb2_cover'] = $realpath2."thumb2".$name.".jpg";   
	} catch (Exception $e) {  } 

	//处理图片3
	if(!empty($_FILES['thumb3']['tmp_name'])) {
		file_delete($_GPC['thumb3-old']);
		$upload= file_upload($_FILES['thumb3']);
		if(is_error($upload)) {
			message($upload['message'], '', 'error');
		}
		$data['thumb3']= $upload['path'];   
	}

	try { 
		$thumb3 = PhpThumbFactory::create($_W['attachurl'].$data['thumb3']); 
		$realpath3 = substr($data['thumb3'], 0, strrpos($data['thumb3'], '/')+1); 
		$thumb3->adaptiveResize(180, 240);
		$thumb3->save("resource/attachment/$realpath3"."thumb3".$name.".jpg");
		$data['thumb3_cover'] = $realpath3."thumb3".$name.".jpg";
	} catch (Exception $e) { 
		// handle error here however you'd like 
	}  

	//处理图片4
	if(!empty($_FILES['thumb4']['tmp_name'])) {
		file_delete($_GPC['thumb4-old']);
		$upload= file_upload($_FILES['thumb4']);
		if(is_error($upload)) {
			message($upload['message'], '', 'error');
		}
		$data['thumb4']= $upload['path']; 
	}

	try{ 
		$thumb4 = PhpThumbFactory::create($_W['attachurl'].$data['thumb4']); 
		$realpath4 = substr($data['thumb4'], 0, strrpos($data['thumb4'], '/')+1); 
		$thumb4->adaptiveResize(180, 240);
		$thumb4->save("resource/attachment/$realpath4"."thumb4".$name.".jpg");
		$data['thumb4_cover'] = $realpath4."thumb4".$name.".jpg";
	} catch (Exception $e) { } 
	  

	if(!empty($_GPC['id'])) {
		$good= pdo_fetch("SELECT * FROM".tablename($this->goods)."WHERE id='{$_GPC['id']}'");
	}
	if($_W['ispost']) {
		if(empty($_GPC['id'])) {
			pdo_insert($this->goods, $data);
			message('发布成功', $this->createMobileUrl('list'), 'success');
		} else {
			pdo_update($this->goods, $data, array('id' => $_GPC['id']));
			message('更新成功', $this->createMobileUrl('list'), 'success');
		}
	}
	include $this->template('add');
?>