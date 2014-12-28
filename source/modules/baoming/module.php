<?php
/**
 * 九个一报名系统模块
 * 2013-11-02
 * BY:拥抱
 */
defined('IN_IA') or exit('Access Denied');

class BaomingModule extends WeModule {
	public $name = 'Baoming';
	public $title = '九个一报名系统';
	public $ability = '';
	public $tablename = 'baoming_reply';
	public $table = 'baoming_list';
	public function fieldsFormDisplay($rid = 0) {
		global $_W;

      	if (!empty($rid)) {
			$reply = pdo_fetch("SELECT * FROM ".tablename($this->tablename)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));		
 		} 
		$starttime = empty($reply['start_time']) ? strtotime(date('Y-m-d')) : $reply['start_time'];
		$endtime = empty($reply['end_time']) ? TIMESTAMP : $reply['end_time'] + 86399;
		include $this->template('baoming/form');
	}
  	public function fieldsFormValidate($rid = 0) {
		
        return true;
	}
  
  	public function fieldsFormSubmit($rid = 0) {
		global $_GPC,$_W;
		//时间处理
$where = '';
$starttime = empty($_GPC['start']) ? strtotime(date('Y-m-d')) : strtotime($_GPC['start']);
$endtime = empty($_GPC['end']) ? TIMESTAMP : strtotime($_GPC['end']);
$where .= " AND createtime >= '$starttime' AND createtime < '$endtime'";		
        $id = intval($_GPC['reply_id']);
		$insert = array(
			'rid' => $rid,
			'title' => $_GPC['title'],
			'description' => $_GPC['description'],
			'thumb' => $_GPC['picture'],
			'bgimage' => $_GPC['bgimage'],
			'qi' => $_GPC['qi'],
			'start_time' => $starttime,
			'end_time' => $endtime,
			'status' => $_GPC['status']
		);
      //处理图片
      	if (!empty($_GPC['picture'])) {
			file_delete($_GPC['picture-old']);
		} else {
			unset($insert['thumb']);
		}
      	if (!empty($_GPC['bgimage'])) {
			file_delete($_GPC['bgimage-old']);
		} else {
			unset($insert['bgimage']);
		}
		if (empty($id)) {
			$id=pdo_insert($this->tablename, $insert);
		} else {
			pdo_update($this->tablename, $insert, array('id' => $id));
		}
      	return true;
	}
   	public function ruleDeleted($rid = 0) {
		global $_W;
		$replies = pdo_fetchall("SELECT id,rid FROM ".tablename($this->tablename)." WHERE rid = '$rid'");
		$deleteid = array();
		if (!empty($replies)) {
          	foreach ($replies as $index => $row) {
				$deleteid[] = $row['id'];
			}
			pdo_delete('context_keycode', "rid =".$rid."");          
		}
		pdo_delete($this->tablename, "id IN ('".implode("','", $deleteid)."')");
		return true;
	}
	public function dolist($rid = 0) {
		global $_GPC,$_W;

		$rid = $_GPC['id'];
      	if (!empty($rid)) {
			$reply = pdo_fetch("SELECT * FROM ".tablename($this->tablename)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));		
 		}
		$fromuser = authcode(base64_decode($_GPC['from_user']), 'DECODE');
		if(!empty($fuomuser)) {
			$list = pdo_fetch("SELECT * FROM ".tablename($this->table)." WHERE from_user = '".$fromuser."' limit 1" );
		}
    			
      if($_GPC['action']=='setinfo'){
     	$insert = array(
			'rid' => $rid,
			'name' => $_GPC['username'],
			'tel' => $_GPC['tel'],
			'qq' => $_GPC['qq'],
			'from_user'=>$fromuser,
			'ip' => getip()
		);
		$name = pdo_fetch("SELECT * FROM ".tablename($this->table)." WHERE name = '".$_GPC['username']."' limit 1" );
       	if (empty($fuomuser)) {
			
			if (empty($name)) {
				$id=pdo_insert($this->table, $insert);
			} else {
				echo '用户已存在!';
			}
		} else {
			if ($list=='false'){
				pdo_update('user', $insert, array('from_user' => $fromuser));
			} else {
				pdo_insert($this->table, $insert);
			}
		}
        die(true);
      }
      	$title = '报名页面';	
      	$loclurl=create_url('index/module', array('do' => 'list', 'name' => 'baoming', 'id' => $rid, 'from_user' => $_GPC['from_user']));
   		
		if ($reply['status']) {
			include $this->template('baoming/index');
		} else {
			echo '<h1>报名结束!</h1>';
			exit;			
		}
	}
	public function dostatus( $rid = 0) {
		global $_GPC;
		$rid = $_GPC['rid'];
		echo $rid;
		$insert = array(
			'status' => $_GPC['status']
		);
		
		pdo_update($this->tablename,$insert,array('rid' => $rid));
		message('模块操作成功！', referer(), 'success');
	}
	//报名名单!
	public function dobaominglist() {
		global $_W;

		checklogin();
		checkaccount();
		// select a.rid, name, qq, tel, ip, b.qi from ims_baoming_list as a left join ims_baoming_reply as b on a.rid = b.rid where 1 order by a.id desc
		//$list = pdo_fetchall("SELECT * FROM ".tablename($this->table)." order by id desc");
		$list = pdo_fetchall("select a.rid, name, qq, tel, ip, b.qi from ".tablename($this->table)." as a left join ".tablename($this->tablename)." as b on a.rid = b.rid where 1 order by a.id desc");
		include $this->template('baoming/list');
	}
  
}