<?php
/**
 * 微短信模块定义
 *
 * @author xiaogg
 * @url 
 */
 defined('IN_IA') or exit('Access Denied');

class LxyduanxinModule extends WeModule {

	public $tablename = 'lxy_duanxin_reply';

	public function fieldsFormDisplay($rid = 0) {
		global $_GPC;
		if (!empty($rid)) {
			$reply = pdo_fetch("SELECT * FROM ".tablename($this->tablename)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));
			if($reply!=false)	$weddingimageArr=explode(",",$reply['weddingimage']);
		} 
		include $this->template('form');
	}
  	public function fieldsFormValidate($rid = 0) {
		return true;
	}
  

  	public function fieldsFormSubmit($rid = 0) {
		global $_GPC, $_W;
 
        $id = intval($_GPC['reply_id']);
		$insert = array(
			'rid' => $rid,
			'title' => $_GPC['title'],
			'description' => $_GPC['description'],
			'thumb' => $_GPC['picture'],
		);
      //处理图片
      	if (!empty($_GPC['picture'])) {
			file_delete($_GPC['picture-old']);
		} else {
			unset($insert['thumb']);
		}
		if (empty($id)) {
			$id=pdo_insert($this->tablename, $insert);
		} else {
			pdo_update($this->tablename, $insert, array('id' => $id));
		}
	}	
}