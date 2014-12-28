<?php
/**
 * 万能卡模块处理程序
 * * @author 石头鱼

 * @url http://www.00393.com/
 */
defined('IN_IA') or exit('Access Denied');

class sharecardsModuleSite extends WeModuleSite {

	public $table_reply  = 'sharecards_reply';
	public $table_card   = 'sharecards_date';
	public $table_styles = 'sharecards_category';

	public function getProfileTiles() {

	}

	public function getHomeTiles($keyword = '') {
		$urls = array();
		$list = pdo_fetchall("SELECT name, id FROM ".tablename('rule')." WHERE module = 'sharecards'".(!empty($keyword) ? " AND name LIKE '%{$keyword}%'" : ''));
		if (!empty($list)) {
			foreach ($list as $row) {
				$urls[] = array('title'=>$row['name'], 'url'=> $this->createMobileUrl('sharecards', array('id' => $row['id'])));
			}
		}
		return $urls;
	}

	public function doMobileSharecards() {
		//处理万能卡表单提交。
		global $_GPC,$_W;		

		if (empty($_GPC['rid'])) {
		$rid = $_GPC['id'];
		}else{
		$rid = $_GPC['rid'];
		}
      	if (!empty($rid)) {
			$reply = pdo_fetch("SELECT * FROM ".tablename($this->table_reply)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));
			$category = pdo_fetchall("SELECT * FROM ".tablename($this->table_styles)." WHERE parentid = '".$reply['cid']."'  ORDER BY displayorder ASC,id ASC");
			$category_title = pdo_fetch("SELECT title FROM ".tablename($this->table_styles)." WHERE id = :rid ", array(':rid' => $reply['cid']));
 		}
		$fromuser = $_W['fans']['from_user'];
		//$fromuser = authcode(base64_decode($_GPC['from_user']), 'DECODE');
		//$fromuser = $_GPC['from_user'];
		$sharecardsimg=$_W['attachurl'].$reply['picture'];
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
      	$title = '微信万能卡';
		$loclurl=$this->createMobileUrl('sharecards', array('rid' => $rid));
		$loclurl2=$this->createMobileUrl('sharecardsview', array('rid' => $rid));

      	//$loclurl=create_url('index/module', array('do' => 'sharecards', 'name' => 'sharecards', 'rid' => $rid, 'from_user' => $_GPC['from_user']));
		//$loclurl2=create_url('index/module', array('do' => 'sharecardsview', 'name' => 'sharecards', 'rid' => $rid, 'from_user' => $_GPC['from_user']));

		if ($reply['status']) {
			include $this->template('sharecards');
		} else {
			echo '<h1>活动已结束!</h1>';
			exit;
		}
	}
	public function doMobileSharecardsview($rid, $state) {
		global $_GPC, $_W;
		$weid = $_W['account']['weid'];//当前公众号ID
		$rid = intval($_GPC['rid']);
		$id = intval($_GPC['id']);
		$back = intval($_GPC['back']);
      	if (!empty($rid)) {
			$reply = pdo_fetch("SELECT * FROM ".tablename($this->table_reply)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));
			$category_title = pdo_fetch("SELECT title FROM ".tablename($this->table_styles)." WHERE id = :rid ", array(':rid' => $reply['cid']));
 		}
		$fromuser = $_W['fans']['from_user'];
		//$fromuser = authcode(base64_decode($_GPC['from_user']), 'DECODE');
		//取得万能卡信息
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
		$sharecardsimg=$_W['attachurl'].$reply['picture'];
		$sharecardsurl=$_W['siteroot'].$this->createMobileUrl('sharecardsview', array('rid' => $rid,'id' => $card_id, 'back' => 1));
		$reporturl=$_W['siteroot'].$this->createMobileUrl('report', array('rid' => $rid, 'id' => $card_id));
		//$sharecardsurl=$_W['siteroot'].create_url('index/module', array('do' => 'sharecardsview', 'name' => 'sharecards', 'rid' => $rid, 'id' => $card_id, 'back' => 1, 'from_user' => $_GPC['from_user']));
		//$reporturl=$_W['siteroot'].create_url('index/module', array('do' => 'report', 'name' => 'sharecards', 'rid' => $rid, 'id' => $card_id, 'from_user' => $_GPC['from_user']));

		$addviewnum=$list['viewnum']+1;
		$viewnum = array(
			'viewnum' => $addviewnum
		);
		pdo_update($this->table_card,$viewnum,array('id' => $id));
		if($list['status']){
			include $this->template('sharecardsview');
		}else{
			echo '<h1>你浏览的卡片不存在或已被屏蔽!</h1>';
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
	//分享活动列表开始
	public function doWebSharecardslist($rid, $state) {
		global $_GPC, $_W;
		checklogin();
		$weid = $_W['account']['weid'];//当前公众号ID
		$id = intval($_GPC['id']);
		if (checksubmit('delete')) {
			pdo_delete($this->table_card, " id IN ('".implode("','", $_GPC['select'])."')");
			message('删除成功！', create_url('site/module', array('do' => 'sharecardslist', 'name' => 'sharecards', 'id' => $id, 'page' => $_GPC['page'])));
		}
		$sharecard_info = pdo_fetch("SELECT title FROM ".tablename($this->table_reply)." WHERE rid = :rid ", array(':rid' => $id));
		$rules = pdo_fetchall('SELECT `id`,`name` FROM '.tablename('rule').' WHERE `module`=\'sharecards\'');
		$pindex = max(1, intval($_GPC['page']));
		$psize = 15;

		//取得活动列表
		$list_sharecards = pdo_fetchall('SELECT * FROM '.tablename($this->table_card).' WHERE rid= :rid order by `id` desc LIMIT ' . ($pindex - 1) * $psize . ',' . $psize, array(':rid' => $id) );
		$listtotal = pdo_fetchall('SELECT * FROM '.tablename($this->table_card).' WHERE rid= :rid order by `id` desc ', array(':rid' => $id) );
		$total = count($listtotal);
		$pager = pagination($total, $pindex, $psize);
		include $this->template('sharecardslist');

	}
	//分享活动列表结束
	//分享活动状态开始
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
	//分享活动状态结束
	//屏蔽开始
	public function doWebdos( $id = 0) {
		global $_GPC;
		$rid = $_GPC['rid'];
		$id = $_GPC['id'];
		echo $id;
		$insert = array(
			'status' => $_GPC['status']
		);

		pdo_update($this->table_card,$insert,array('id' => $id,'rid' => $rid));
		message('屏蔽操作成功！', create_url('site/module', array('do' => 'sharecardslist', 'name' => 'sharecards', 'id' => $rid, 'page' => $_GPC['page'])));
	}
	//屏蔽结束
	//预设词分类设置开始
	public function doWebSharecardscategory() {
		global $_W, $_GPC;
		checklogin();
		$foo = !empty($_GPC['foo']) ? $_GPC['foo'] : 'display';
		if ($foo == 'display') {
			if (!empty($_GPC['displayorder'])) {
				foreach ($_GPC['displayorder'] as $id => $displayorder) {
					pdo_update($this->table_styles, array('displayorder' => $displayorder), array('id' => $id));
				}
				message('类型或预设词排序更新成功！', 'refresh', 'success');
			}
			$children = array();
			$category = pdo_fetchall("SELECT * FROM ".tablename($this->table_styles)." WHERE weid = '{$_W['weid']}' ORDER BY parentid ASC");
			foreach ($category as $index => $row) {
				if (!empty($row['parentid'])){
					$children[$row['parentid']][] = $row;
					unset($category[$index]);
				}
			}
			include $this->template('category');
		} elseif ($foo == 'post') {
			$parentid = intval($_GPC['parentid']);
			$id = intval($_GPC['id']);
			if(!empty($id)) {
				$category = pdo_fetch("SELECT * FROM ".tablename($this->table_styles)." WHERE id = '$id'");
			} else {
				$category = array(
					'displayorder' => 0,
				);
			}
			if (!empty($parentid)) {
				$parent = pdo_fetch("SELECT id, title FROM ".tablename($this->table_styles)." WHERE id = '$parentid'");
				if (empty($parent)) {
					message('抱歉，类型不存在或是已经被删除！', $this->createWebUrl('sharecardscategory', array('foo' => 'display')), 'error');
				}
			}			
			if (checksubmit('submit')) {
				if (empty($_GPC['cname'])) {
					message('抱歉，请输入类型或预设词名称！');
				}
				$data = array(
					'weid' => $_W['weid'],
					'title' => $_GPC['cname'],
					'displayorder' => intval($_GPC['displayorder']),
					'parentid' => intval($parentid),
					'description' => $_GPC['description'],
				);
				if (!empty($id)) {
					unset($data['parentid']);
					pdo_update($this->table_styles, $data, array('id' => $id));
				} else {
					pdo_insert($this->table_styles, $data);
					$id = pdo_insertid();
				}				
				message('更新类型或预设词成功！', $this->createWebUrl('sharecardscategory'), 'success');
			}
			include $this->template('category');
		} elseif ($foo == 'fetch') {
			$category = pdo_fetchall("SELECT id, title FROM ".tablename($this->table_styles)." WHERE parentid = '".intval($_GPC['parentid'])."' ORDER BY id ASC");
			message($category, '', 'ajax');
		} elseif ($foo == 'delete') {
			$id = intval($_GPC['id']);
			$category = pdo_fetch("SELECT id, parentid FROM ".tablename($this->table_styles)." WHERE id = '$id'");
			if (empty($category)) {
				message('抱歉，类型或预设词不存在或是已经被删除！', $this->createWebUrl('sharecardscategory'), 'error');
			}			
			pdo_delete($this->table_styles, array('id' => $id, 'parentid' => $id), 'OR');
			message('类型或预设词删除成功！', $this->createWebUrl('sharecardscategory'), 'success');
		}
	}
	//预设词分类设置结束
}