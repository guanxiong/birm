<?php
/**
 * 贺卡模块定义
 *
 * @author 超级无聊
 * @url 
 */
defined('IN_IA') or exit('Access Denied');

class HekaModule extends WeModule {
	public $tablename = 'heka_reply';
	public function fieldsFormDisplay($rid = 0) {
		global $_W;
      	if (!empty($rid)) {
			$reply = pdo_fetch("SELECT * FROM ".tablename($this->tablename)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));		
 		} 
		include $this->template('form');
	}

	public function fieldsFormValidate($rid = 0) {
		//规则编辑保存时，要进行的数据验证，返回空串表示验证无误，返回其他字符串将呈现为错误提示。这里 $rid 为对应的规则编号，新增时为 0
		return '';
	}

	public function fieldsFormSubmit($rid) {
 		global $_GPC,$_W;
 		$id = intval($_GPC['reply_id']);
  
		$insert = array(
			'rid' => $rid,
			'title' => $_GPC['title'],
			'description' => $_GPC['description'],
			'picture' => $_GPC['picture'],
		);
		
	 
		if (empty($id)) {
			$id=pdo_insert($this->tablename, $insert);
		} else {
			if (!empty($_GPC['picture'])) {
				file_delete($_GPC['picture-old']);
			} else {
				unset($insert['picture']);
			}
			pdo_update($this->tablename, $insert, array('id' => $id));
		}
	}
	
	public function doList(){
 		global $_GPC,$_W;
		checklogin();
		$weid = $_W['weid'];	
		if (checksubmit('delete')) {
			pdo_delete('heka_list', " id  IN  ('".implode("','", $_GPC['select'])."')");
			message('删除成功！', create_url('site/module', array('do' => 'list', 'name' => 'heka','page' => $_GPC['page'])));
		}
		$pindex = max(1, intval($_GPC['page']));
		$psize = 50;
		$where = '';
		$sql = "SELECT * FROM ".tablename('heka_list')."  WHERE weid = $weid  ORDER BY create_time DESC LIMIT ".($pindex - 1) * $psize.",{$psize}";
		$list = pdo_fetchall($sql);
		if (!empty($list)) {
			$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('heka_list')." WHERE weid = $weid");
			$pager = pagination($total, $pindex, $psize);
		}
		include $this->template('list');
	}
	public function doDeleteImage() {
		global $_GPC;
		$id = intval($_GPC['id']);
		$sql = "SELECT id, picture FROM " . tablename($this->tablename) . " WHERE `id`=:id";
		$row = pdo_fetch($sql, array(':id'=>$id));
		if (empty($row)) {
			message('抱歉，回复不存在或是已经被删除！', '', 'error');
		}
		if (pdo_update($this->tablename, array('picture' => ''), array('id' => $id))) {
			file_delete($row['picture']);
		}
		message('删除图片成功！', '', 'success');
	}
	public function ruleDeleted($rid) {
		//删除规则时调用，这里 $rid 为对应的规则编号
		pdo_delete($this->tablename,array('rid'=>$rid));
		pdo_delete('heka_list',array('rid'=>$rid));
		
	}


}