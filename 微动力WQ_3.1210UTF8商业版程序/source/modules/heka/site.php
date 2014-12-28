<?php
/**
 * 贺卡模块微站定义
 *
 * @author 超级无聊
 * @url
 */
defined('IN_IA') or exit('Access Denied');

class HekaModuleSite extends WeModuleSite {
	public $tablename = 'heka_reply';

	public function getHomeTiles() {
		global $_W;
		$urls = array();
		$list = pdo_fetchall("SELECT name, id FROM ".tablename('rule')." WHERE weid = '{$_W['weid']}' AND module = 'heka'");
		if (!empty($list)) {
			foreach ($list as $row) {
				$urls[] = array('title'=>$row['name'], 'url'=> $this->createMobileUrl('index', array('id' => $row['id'])));
			}
		}
		return $urls;
	}

	public function doMobileindex(){
		global $_GPC, $_W;
		include $this->template('index');
	}

	public function doMobileshow(){
		global $_GPC, $_W;
		if(!empty($_GPC['cid'])){
			$show = pdo_fetch("SELECT * FROM ".tablename('heka_list')." WHERE id = :cid ORDER BY `id` DESC", array(':cid' => $_GPC['cid']));
		}
		if($show==false){
			$show=array(
				'id'=>0,
				'title'=>'收卡人',
				'author'=>'署名',
			);
		}else{
			pdo_update('heka_list',array('hits'=>intval($show['hits'])+1),array('id'=>$show['id']));
		}
		$card=$_GPC['card'];
		include $this->template($card);
	}

	public function doMobileset(){
		global $_GPC, $_W;
		$_GPC['title']=urldecode($_GPC['title']);
		$_GPC['content']=urldecode($_GPC['content']);
		$_GPC['author']=urldecode($_GPC['author']);
		$_GPC['cardName']=urldecode($_GPC['cardName']);
		$insert=array(
			'rid'=>$_GPC['id'],
			'weid'=>$_GPC['weid'],
			'title'=>$_GPC['title'],
			'card'=>$_GPC['card'],
			'content'=>$_GPC['content'],
			'author'=>$_GPC['author'],
			'cardName'=>$_GPC['cardName'],
			'from_user'=>$_W['fans']['from_user'],
			'create_time'=>time(),
		);
		$temp=pdo_insert('heka_list', $insert);
		if($temp==false){
			$this->_message(0,'保存数据失败');
		}else{
			$id=pdo_insertid();
			$this->_message($id,'保存数据成功',1,$_GPC['author']);
		}
	}
	public function doMobileshare(){
		global $_GPC;
		$id=$_GPC['cid'];
		$show = pdo_fetch("SELECT share FROM ".tablename('heka_list')." WHERE id = :cid  ORDER BY `id` DESC", array(':cid' => $id));
		if($show!=false){
			if(empty($show['share'])){
				$show['share']=0;
			}
			pdo_update('heka_list',array('share'=>intval($show['share'])+1),array('id'=>$id));
		}
		print_r($show);
	}

	public function _message($_id,$_msg,$_state=0,$_username=''){
		$_data=array(
			'id'=>$_id,
			'msg'=>$_msg,
			'state'=>$_state,
		);
		if(!empty($_username)){
			$_data['username']=$_username;
		}
		echo json_encode($_data);
	}

}