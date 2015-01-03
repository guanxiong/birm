<?php
/**
 * 砸蛋抽奖模块
 *
 * [WNS]更多模块请浏览：BBS.birm.co
 */
defined('IN_IA') or exit('Access Denied');

class BlessModuleSite extends WeModuleSite {

	public $table_reply  = 'bless_reply';
	public $table_card   = 'bless_card';

	public function getProfileTiles() {

	}

	public function getHomeTiles($keyword = '') {
		$urls = array();
		$list = pdo_fetchall("SELECT name, id FROM ".tablename('rule')." WHERE module = 'bless'".(!empty($keyword) ? " AND name LIKE '%{$keyword}%'" : ''));
		if (!empty($list)) {
			foreach ($list as $row) {
				$urls[] = array('title'=>$row['name'], 'url'=> $this->createMobileUrl('bless', array('id' => $row['id'])));
			}
		}
		return $urls;
	}

	public function doMobileBless() {
		//处理祝福卡表单提交。
		global $_GPC,$_W;
		if (empty($_GPC['rid'])) {
		$rid = $_GPC['id'];
		}else{
		$rid = $_GPC['rid'];
		}
      	if (!empty($rid)) {
			$reply = pdo_fetch("SELECT * FROM ".tablename($this->table_reply)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));
 		}
		$fromuser = $_W['fans']['from_user'];
		//$fromuser = authcode(base64_decode($_GPC['from_user']), 'DECODE');
		//$fromuser = $_GPC['from_user'];
		$blessimg=$_W['attachurl'].$reply['picture'];
		if(!empty($fromuser)) {
			$list = pdo_fetch("SELECT * FROM ".tablename($this->table_card)." WHERE from_user = '".$fromuser."' and rid = '".$rid."' limit 1" );
		}

      if($_GPC['action']=='setinfo'){
     	$insert = array(
			'rid' => $rid,
			'to_name' => $_GPC['to_name'],
			'from_name' => $_GPC['from_name'],
			'content' => $_GPC['content'],
			'from_user'=>$fromuser,
			'viewnum' => '1',
			'createtime'=>time()
		);
		$name = pdo_fetch("SELECT * FROM ".tablename($this->table_card)." WHERE from_user = '".$fromuser."' and rid = '".$rid."' limit 1" );
       	if (empty($fromuser)) {
				echo '非法参数!';
		} else {
				pdo_insert($this->table_card, $insert);
		}
		$data['id'] = '123';
		$data['msg'] = "提交成功";
		$data['error'] = '0';
		//message($data, '', 'ajax');
        die(true);
      }
      	$title = '微信送祝福-圣诞祝福';
		$loclurl=$this->createMobileUrl('bless', array('rid' => $rid));
		$loclurl2=$this->createMobileUrl('blessview', array('rid' => $rid));

      	//$loclurl=create_url('index/module', array('do' => 'bless', 'name' => 'bless', 'rid' => $rid, 'from_user' => $_GPC['from_user']));
		//$loclurl2=create_url('index/module', array('do' => 'blessview', 'name' => 'bless', 'rid' => $rid, 'from_user' => $_GPC['from_user']));

		if ($reply['status']) {
			include $this->template('bless');
		} else {
			echo '<h1>送祝福活动已结束!</h1>';
			exit;
		}
	}
	public function doMobileBlessview($rid, $state) {
		global $_GPC, $_W;
		$weid = $_W['account']['weid'];//当前公众号ID
		$rid = intval($_GPC['rid']);
		$id = intval($_GPC['id']);
		$back = intval($_GPC['back']);
      	if (!empty($rid)) {
			$reply = pdo_fetch("SELECT * FROM ".tablename($this->table_reply)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));
 		}
		$fromuser = $_W['fans']['from_user'];
		//$fromuser = authcode(base64_decode($_GPC['from_user']), 'DECODE');
		//取得祝福卡信息
		if(!empty($id)) {
			$list = pdo_fetch("SELECT * FROM ".tablename($this->table_card)." WHERE id = '".$id."' and rid = '".$rid."' limit 0,1" );
		}else{
			if(!empty($fromuser)) {
				$list = pdo_fetch("SELECT * FROM ".tablename($this->table_card)." WHERE from_user = '".$fromuser."' and rid = '".$rid."' order by id desc limit 0,1" );
			}
		}
		$to_name=$list['to_name'];
		$from_name=$list['from_name'];
		$content=$list['content'];
		$card_id=$list['id'];
		$fromuser=$fromuser;
		$cardbg=$_W['attachurl'].$reply['bgimage'];
		$blessimg=$_W['attachurl'].$reply['picture'];
		$blessurl=$_W['siteroot'].$this->createMobileUrl('blessview', array('rid' => $rid,'id' => $card_id, 'back' => 1));
		$reporturl=$_W['siteroot'].$this->createMobileUrl('report', array('rid' => $rid, 'id' => $card_id));
		//$blessurl=$_W['siteroot'].create_url('index/module', array('do' => 'blessview', 'name' => 'bless', 'rid' => $rid, 'id' => $card_id, 'back' => 1, 'from_user' => $_GPC['from_user']));
		//$reporturl=$_W['siteroot'].create_url('index/module', array('do' => 'report', 'name' => 'bless', 'rid' => $rid, 'id' => $card_id, 'from_user' => $_GPC['from_user']));

		$addviewnum=$list['viewnum']+1;
		$viewnum = array(
			'viewnum' => $addviewnum
		);
		pdo_update($this->table_card,$viewnum,array('id' => $id));
		if($list['status']){
			include $this->template('blessview');
		}else{
			echo '<h1>你浏览的祝福信息不存在或已被屏蔽!</h1>';
		}

	}
	public function doMobilereport( $rid = 0) {
		global $_GPC;
		$rid = $_GPC['rid'];
		$id = $_GPC['id'];
		if(!empty($id)) {
			$list = pdo_fetch("SELECT * FROM ".tablename($this->table_card)." WHERE id = '".$id."' and rid = '".$rid."' limit 0,1" );
		}
		$addsharenum=$list['sharenum']+1;
		$sharenum = array(
			'sharenum' => $addsharenum
		);
		pdo_update($this->table_card,$sharenum,array('id' => $id));

	}

	public function doWebBlesslist($rid, $state) {
		global $_GPC, $_W;
		checklogin();
		$weid = $_W['account']['weid'];//当前公众号ID
		$id = intval($_GPC['id']);
		if (checksubmit('delete')) {
			pdo_delete($this->table_card, " id IN ('".implode("','", $_GPC['select'])."')");
			message('删除成功！', create_url('index/module', array('do' => 'blesslist', 'name' => 'bless', 'id' => $id, 'page' => $_GPC['page'])));
		}
		$rules = pdo_fetchall('SELECT `id`,`name` FROM '.tablename('rule').' WHERE `module`=\'bless\'');
		$pindex = max(1, intval($_GPC['page']));
		$psize = 15;

		//取得祝福列表
		$list_bless = pdo_fetchall('SELECT * FROM '.tablename($this->table_card).' WHERE rid= :rid order by `id` desc LIMIT ' . ($pindex - 1) * $psize . ',' . $psize, array(':rid' => $id) );
		$listtotal = pdo_fetchall('SELECT * FROM '.tablename($this->table_card).' WHERE rid= :rid order by `id` desc ', array(':rid' => $id) );
		$total = count($listtotal);
		$pager = pagination($total, $pindex, $psize);
		include $this->template('blesslist');

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

		pdo_update($this->table_card,$insert,array('id' => $id,'rid' => $rid));
		message('屏蔽操作成功！', create_url('site/module', array('do' => 'blesslist', 'name' => 'bless', 'id' => $rid, 'page' => $_GPC['page'])));
	}
}