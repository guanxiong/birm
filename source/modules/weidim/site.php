<?php
/**
 * 微预约模块
 *
 * [微鼎] Copyright (c) 2013 WEIDIM.COM
 */
defined('IN_IA') or exit('Access Denied');

class WeidimModuleSite extends WeModuleSite {

	public $table_reply  = 'weidim_reply';
	public $table_item   = 'weidim_item';
	public $table_order  = 'weidim_order';

	public function getProfileTiles() {

	}

	public function getWeidimTiles($keyword = '') {
		global $_GPC,$_W;
		$urls = array();
		$weid = $_W['account']['weid'];//当前公众号ID
		$list = pdo_fetchall("SELECT name, id FROM ".tablename('rule')." WHERE module = 'weidim' and weid='$weid'".(!empty($keyword) ? " AND name LIKE '%{$keyword}%'" : ''));
		if (!empty($list)) {
			foreach ($list as $row) {
				$urls[] = array('title'=>$row['name'], 'url'=> $this->createMobileUrl('weidim', array('id' => $row['id'])));
			}
		}
		return $urls;
	}

public function doMobileWeidim() {
		//处理在线预约表单填写提交。
		global $_GPC,$_W;

		$rid = $_GPC['id'];
      	if (!empty($rid)) {
			$reply = pdo_fetch("SELECT * FROM ".tablename($this->table_reply)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));
			$items = pdo_fetchall("SELECT * FROM ".tablename($this->table_item)." WHERE rid = :rid ORDER BY `orderid` ASC", array(':rid' => $rid));
 		}

		$fromuser = authcode(base64_decode($_GPC['from_user']), 'DECODE');
		if(empty($fromuser)){
			$fromuser= $this->message['from'];
		}
		if(!empty($fromuser)) {
			$list = pdo_fetch("SELECT * FROM ".tablename($this->table_order)." WHERE from_user = '".$fromuser."' and rid = '".$rid."' limit 1" );
		}
    			
      if($_GPC['action']=='setinfo'){
		  $fieldss=htmlspecialchars_decode($_GPC['jsonData']);
		  $fields = array();
		  $fields=json_decode($fieldss,true);
		  //print_r($fields);
		  for($i=0;$i<count($fields);$i++){
			$$fields[$i]['name']=$fields[$i]['value'];
		  }

     	$insert = array(
			'rid' => $rid,
			'field1' => $field1,
			'field2' => $field2,
			'field3' => $field3,
			'field4' => $field4,
			'field5' => $field5,
			'field6' => $field6,
			'field7' => $field7,
			'field8' => $field8,
			'field9' => $field9,
			'field10' => $field10,
			'field11' => $field11,
			'field12' => $field12,
			'field13' => $field13,
			'field14' => $field14,
			'field15' => $field15,
			'field100' => $field100,
			'from_user'=>$fromuser,
			'createtime'=>time()
			//'ip' => getip()
		);
		//print_r($insert);
		$name = pdo_fetch("SELECT * FROM ".tablename($this->table_order)." WHERE from_user = '".$fromuser."' and rid = '".$rid."' limit 1" );

			if (!empty($name)){
				pdo_update($this->table_order, $insert, array('from_user' => $fromuser,'rid' => $rid));
				$message= '更新数据1';
			} else {
				pdo_insert($this->table_order, $insert);
				$message= '插入数据2';
			}
		
        die(true);
      }
      	$title = '微预约-在线预约';	
		$loclurl=$this->createMobileUrl('weidim', array('id' => $rid, 'from_user' => $_GPC['from_user']));	
      	//$loclurl=create_url('mobile/module', array('do' => 'weidim', 'name' => 'weidim', 'id' => $rid, 'from_user' => $_GPC['from_user']));
   		
		if ($reply['status']) {
			$user_agent = $_SERVER['HTTP_USER_AGENT'];
				if (strpos($user_agent, 'MicroMessenger') === false) {
					echo "本页面仅支持微信访问!非微信浏览器禁止浏览!";
				} else { 
					include $this->template('weidim');
				}
		} else {
			echo '<h1>在线预约结束!</h1>';
			exit;			
		}
	}

	public function doWebWeidimlist($rid, $state) {		
		global $_GPC, $_W;
		checklogin();
		$weid = $_W['account']['weid'];//当前公众号ID
		$id = intval($_GPC['id']);
		if (checksubmit('delete')) {
			pdo_delete($this->table_order, " id IN ('".implode("','", $_GPC['select'])."')");
			message('删除成功！', create_url('site/module', array('do' => 'weidimlist', 'name' => 'weidim', 'id' => $id, 'page' => $_GPC['page'])));
		}
		$rules = pdo_fetchall('SELECT `id`,`name` FROM '.tablename('rule').' WHERE `module`=\'weidim\'');
		$pindex = max(1, intval($_GPC['page']));
		$psize = 15;
		//当前公众号的表单字段
		$items = pdo_fetchall('select `id`,`fieldname`,`orderid` from '.tablename($this->table_item).' where rid = :rid order by `orderid` asc', array(':rid' => $id) );
		$fields = array();
		foreach($items as $k=>$v){
			$fields[$v['orderid']] = field.$v['orderid'];
		}
		//print_r($fields);
		//取得预约列表
		//$list_order = pdo_fetchall('SELECT * FROM '.tablename($this->table_order).' WHERE rid= :rid order by `id` desc', array(':rid' => $id) );	
		$list_order = pdo_fetchall('SELECT * FROM '.tablename($this->table_order).' order by `id` desc' );		
		$items_total = count($items);
		$total = count($list_order);
		$pager = pagination($total, $pindex, $psize);
		include $this->template('weidimlist');

	}
	public function doWebstatus( $rid = 0) {
		global $_GPC;
		$rid = $_GPC['rid'];
		echo $rid;
		$insert = array(
			'status' => $_GPC['status']
		);
		
		pdo_update($this->table_reply,$insert,array('rid' => $rid));
		message('模块操作成功！', referer(), 'success');
	}
	public function doWebdos( $id = 0) {
		global $_GPC;
		$rid = $_GPC['rid'];
		$id = $_GPC['id'];
		echo $id;
		$insert = array(
			'status' => $_GPC['status']
		);
		
		pdo_update($this->table_order,$insert,array('id' => $id,'rid' => $rid));
		message('预约处理操作成功！', create_url('site/module/weidimlist', array('name' => 'weidim', 'id' => $rid, 'page' => $_GPC['page'])));
	}
	
		public function doWebdelete() {
			global $_GPC;
		$id = $_GPC['id'];

		pdo_delete($this->table_item, "id = ('". $id ."')");
		message('模块操作成功！', referer(), 'success');
		//return true;
	}
}