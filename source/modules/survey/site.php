<?php
/**
 * 调研模块微站定义
 *
 * @author 更多模块请浏览bbs.birm.co
 * @url http://bbs.we7.cc
 */
defined('IN_IA') or exit('Access Denied');

class SurveyModuleSite extends WeModuleSite {

	public function getHomeTiles() {
		global $_W;
		$urls = array();
		$list = pdo_fetchall("SELECT title, sid FROM ".tablename('survey')." WHERE weid = '{$_W['weid']}'");
		if (!empty($list)) {
			foreach ($list as $row) {
				$urls[] = array('title'=>$row['title'], 'url'=> $this->createMobileUrl('survey', array('id' => $row['sid'])));
			}
		}
		return $urls;
	}
	//用
	public function doWebQuery() {
		global $_W, $_GPC;
		$kwd = $_GPC['keyword'];
		$sql = 'SELECT * FROM ' . tablename('survey') . ' WHERE `weid`=:weid AND `title` LIKE :title AND suggest_status = 1 ORDER BY sid DESC LIMIT 0,8';
		$params = array();
		$params[':weid'] = $_W['weid'];
		$params[':title'] = "%{$kwd}%";
		$ds = pdo_fetchall($sql, $params);
		foreach($ds as &$row) {
			$r = array();
			$r['title'] = $row['title'];
			$r['description'] = cutstr(strip_tags($row['description']), 50);
			$r['thumb'] = $row['thumb'];
			$r['sid'] = $row['sid'];
			$row['entry'] = $r;
		}
		include $this->template('query');
	}

	public function doWebDetail() {
		global $_W, $_GPC;
		$srid = intval($_GPC['id']);
		$sql = 'SELECT * FROM ' . tablename('survey_rows') . " WHERE `srid`=:srid";
		$params = array();
		$params[':srid'] = $srid;
		$row = pdo_fetch($sql, $params);
		$user = fans_search($row['openid'],array('realname','mobile'));
		$row['realname'] = $user['realname'];
		$row['mobile'] = $user['mobile'];
		if(empty($row)) {
			message('访问非法.');
		}
		$sql = 'SELECT * FROM ' . tablename('survey') . ' WHERE `weid`=:weid AND `sid`=:sid';
		$params = array();
		$params[':weid'] = $_W['weid'];
		$params[':sid'] = $row['sid'];
		$activity = pdo_fetch($sql, $params);
		if(empty($activity)) {
			message('非法访问.');
		}
		$sql = 'SELECT * FROM ' . tablename('survey_fields') . ' WHERE `sid`=:sid ORDER BY `sfid`';
		$params = array();
		$params[':sid'] = $row['sid'];
		$fields = pdo_fetchall($sql, $params);
		if(empty($fields)) {
			message('非法访问.');
		}
		$ds = array();
		$fids = array();
		foreach($fields as $f) {
			$ds[$f['sfid']]['fid'] = $f['title'];
			$ds[$f['sfid']]['type']= $f['type'];
			$fids[] = $f['sfid'];
		}

		$fids = implode(',', $fids);
		$row['fields'] = array();
		$sql = 'SELECT * FROM ' . tablename('survey_data') . " WHERE `sid`=:sid AND `srid`='{$row['srid']}' AND `sfid` IN ({$fids})";
		$fdatas = pdo_fetchall($sql, $params);
		foreach($fdatas as $fd) {
			if($ds[$fd['sfid']]['type'] == 'checkbox') {
				$a[$fd['sfid']][] = $fd['data'];
				$row['fields'][$fd['sfid']] = implode(',',$a[$fd['sfid']]);
			} else {
				$row['fields'][$fd['sfid']] = $fd['data'];	
			}
		}

		include $this->template('detail');
	}

	public function doWebManage() {
		global $_W, $_GPC;
		$sid = intval($_GPC['id']);
		$sql = 'SELECT * FROM ' . tablename('survey') . ' WHERE `weid`=:weid AND `sid`=:sid';
		$params = array();
		$params[':weid'] = $_W['weid'];
		$params[':sid'] = $sid;
		$activity = pdo_fetch($sql, $params);
		if(empty($activity)) {
			message('非法访问.');
		}
		$sql = 'SELECT * FROM ' . tablename('survey_fields') . ' WHERE `sid`=:sid ORDER BY displayorder ASC,sfid ASC';
		$params = array();
		$params[':sid'] = $sid;
		//$params[':type'] = 'textarea';
		$fields = pdo_fetchall($sql, $params);
		if(empty($fields)) {
			message('非法访问.');
		}
		$ds = array();
		foreach($fields as $f) {
			$ds[$f['sfid']] = $f['title'];
		}

		$starttime = empty($_GPC['start']) ? strtotime('-1 month') : strtotime($_GPC['start']);
		$endtime = empty($_GPC['end']) ? TIMESTAMP : strtotime($_GPC['end']) + 86399;
		$select = array();
		if (!empty($_GPC['select'])) {
			foreach ($_GPC['select'] as $field) {
				if (isset($ds[$field])) {
					$select[] = $field;
				}
			}
		}
		$sfid = implode(',',$select);
		$total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('survey_rows') . " WHERE `sid`=:sid AND `createtime` > {$starttime} AND `createtime` < {$endtime}", array(':sid' => $sid));
		if(!empty($sfid)) {
			$datas = pdo_fetchall("select * from ".tablename('survey_fields')." WHERE `sid`=:sid AND `sfid` IN ({$sfid})",array(':sid' => $sid));
		} else {
			$datas = pdo_fetchall("select * from ".tablename('survey_fields')." WHERE `sid`=:sid AND (`type`='checkbox' OR `type`='radio')",array(':sid' => $sid));
		}
		foreach($datas as $key => $field) {
			$datas[$key]['title'] = "'".$field['title']."'";
			if(in_array($field['type'],array('radio','checkbox'))) {
				$value = explode("\r\n",$field['value']);
				foreach($value as $val) {
					$num = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('survey_data') . " WHERE `sid`=:sid AND `sfid`=:sfid AND `createtime` > {$starttime} AND `createtime` < {$endtime} AND `data`=:data", array(':sfid' => $field['sfid'],':sid' => $sid,':data' => $val));
					if($field['type'] == 'radio') {
						$datas[$key]['str'] .= $a."\"".$val."({$num}人)"."\"";
					} else {
						$datas[$key]['str'] .= $a."\"".$val."({$num}次)"."\"";
					}
					$datas[$key]['values'][] = $val;
					$datas[$key]['nums'] .= ($total == 0 ? 0 : $a.(round($num/$total*100,2)));
					$a=',';
				}
				$a = '';
			}
		}
		include $this->template('manage');
	}
	
	public function doWebManagelist() {
		global $_W, $_GPC;
		$sid = intval($_GPC['id']);
		$sql = 'SELECT * FROM ' . tablename('survey') . ' WHERE `weid`=:weid AND `sid`=:sid';
		$params = array();
		$params[':weid'] = $_W['weid'];
		$params[':sid'] = $sid;
		$activity = pdo_fetch($sql, $params);
		if(empty($activity)) {
			message('非法访问.');
		}
		$sql = 'SELECT * FROM ' . tablename('survey_fields') . ' WHERE `sid`=:sid ORDER BY `displayorder` ASC,`sfid` ASC';
		$params = array();
		$params[':sid'] = $sid;
		$fields = pdo_fetchall($sql, $params);
		if(empty($fields)) {
			message('非法访问.');
		}
		$ds = array();
		foreach($fields as $f) {
			$ds[$f['sfid']] = $f['title'];
		}

		$starttime = empty($_GPC['start']) ? strtotime('-1 month') : strtotime($_GPC['start']);
		$endtime = empty($_GPC['end']) ? TIMESTAMP : strtotime($_GPC['end']) + 86399;
		$select = array();
		if (!empty($_GPC['select'])) {
			foreach ($_GPC['select'] as $field) {
				if (isset($ds[$field])) {
					$select[] = $field;
				}
			}
		}
		
		$sta = array();
		foreach($fields as $f) {
			$sta[$f['sfid']]['type']= $f['type'];
		}
		
		$pindex = max(1, intval($_GPC['page']));
		$psize = 50;
		$sql = 'SELECT * FROM ' . tablename('survey_rows') . " WHERE `sid`=:sid AND `createtime` > {$starttime} AND `createtime` < {$endtime} ORDER BY `createtime` DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
		$params = array();
		$params[':sid'] = $sid;

		$total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('survey_rows') . " WHERE `sid`=:sid AND `createtime` > {$starttime} AND `createtime` < {$endtime}", $params);
		$pager = pagination($total, $pindex, $psize);

		$list = pdo_fetchall($sql, $params);
		foreach($list as &$r) {
			$user = fans_search($r['openid'],array('realname','mobile'));
			$r['realname'] = $user['realname'];
			$r['mobile'] = $user['mobile'];
		}
		if($select) {
			$fids = implode(',', $select);
			foreach($list as &$r) {
				$r['fields'] = array();
				$sql = 'SELECT * FROM ' . tablename('survey_data') . " WHERE `sid`=:sid AND `srid`='{$r['srid']}' AND `sfid` IN ({$fids})";
				$fdatas = pdo_fetchall($sql, $params);
				foreach($fdatas as $fd) {
					if($sta[$fd['sfid']]['type'] == 'checkbox') {
						$a[$fd['srid']][$fd['sfid']][] = $fd['data']; 
						$r['fields'][$fd['sfid']] = implode(' ',$a[$fd['srid']][$fd['sfid']]);
					} else {
						$r['fields'][$fd['sfid']] = $fd['data'];
					}		
				}
			}
		}	
		include $this->template('managelist');
	}
	//用
	public function doWebDisplay() {
		global $_W, $_GPC;
		$keyword = trim($_GPC['keyword']);
		$sta = isset($_GPC['status']) ? intval($_GPC['status']) : 1;
		if(empty($keyword)) {
			$sql = 'SELECT * FROM ' . tablename('survey') ." WHERE `weid`=:weid AND `status`=:status" ;
		} else {
			$sql = 'SELECT * FROM ' . tablename('survey') ." WHERE `weid`=:weid AND `status`=:status AND `title` LIKE '%{$keyword}%'" ;
		}
		$ds = pdo_fetchall($sql, array(':weid' => $_W['weid'],':status' => $sta));
		foreach($ds as &$item) {
			$item['isstart'] = $item['starttime'] > 0;
			$item['switch'] = $item['status'];
			$item['link'] =  $this->createMobileUrl('survey', array('id' => $item['sid']));
		}
		include $this->template('display');
		if($_W['ispost']) {
			$sid = intval($_GPC['sid']);
			$switch = intval($_GPC['switch']);
			$sql = 'UPDATE ' . tablename('survey') . ' SET `status`=:status WHERE `sid`=:sid';
			$params = array();
			$params[':status'] = $switch;
			$params[':sid'] = $sid;
			pdo_query($sql, $params);
			exit();
		}
	}
	//用
	public function doWebDelete() {
		global $_W, $_GPC;
		$sid = intval($_GPC['id']);
		if($sid > 0) {
			$params = array();
			$params[':sid'] = $sid;
			$sql = 'DELETE FROM ' . tablename('survey') . ' WHERE `sid`=:sid';
			pdo_query($sql, $params);
			$sql = 'DELETE FROM ' . tablename('survey_rows') . ' WHERE `sid`=:sid';
			pdo_query($sql, $params);
			$sql = 'DELETE FROM ' . tablename('survey_fields') . ' WHERE `sid`=:sid';
			pdo_query($sql, $params);
			$sql = 'DELETE FROM ' . tablename('survey_data') . ' WHERE `sid`=:sid';
			pdo_query($sql, $params);
			$sql = 'DELETE FROM ' . tablename('survey_reply') . ' WHERE `sid`=:sid';
			pdo_query($sql, $params);
			message('操作成功.', referer());
		}
		message('非法访问.');
	}
	//用
	public function doWebSurveyDelete() {
		global $_W, $_GPC;
		$id = intval($_GPC['id']);
		if (!empty($id)) {
			pdo_delete('survey_rows', array('srid' => $id));
			pdo_delete('survey_data', array('srid' => $id));
		}
		message('操作成功.', referer());
	}
	//用
	public function doWebPost() {
		global $_W, $_GPC;
		$sid = intval($_GPC['id']); //调研id
		$hasData = false;
		if($sid) {
			$sql = 'SELECT COUNT(*) FROM ' . tablename('survey_rows') . ' WHERE `sid`=' . $sid;
			if(pdo_fetchcolumn($sql) > 0) {
				$hasData = true;
			}
		}
		if(checksubmit()) {
			$recrod = array();
			$recrod['title'] = trim($_GPC['title']) ? trim($_GPC['title']) : message('请填写调研标题.');
			$recrod['weid'] = $_W['weid'];
			$recrod['description'] = trim($_GPC['description']) ? trim($_GPC['description']) : message('请填写调研简介.');
			$recrod['content'] = trim($_GPC['content']) ? trim($_GPC['content']) : message('请填写调研内容.');
			$recrod['information'] = trim($_GPC['information']) ? trim($_GPC['information']) : message('请填写调研提交成功提示信息.');
			$recrod['thumb'] = trim($_GPC['thumb']);
			$recrod['pertotal'] = intval($_GPC['pertotal']) ? intval($_GPC['pertotal']) : 1;
			$recrod['status'] = intval($_GPC['status']);
			$recrod['suggest_status'] = intval($_GPC['suggest_status']);
			$recrod['inhome'] = intval($_GPC['inhome']);
			$recrod['starttime'] = strtotime($_GPC['starttime']);
			$recrod['endtime'] = strtotime($_GPC['endtime']);
			if(empty($sid)) {
				$recrod['status'] = 1;
				$recrod['createtime'] = TIMESTAMP;
				pdo_insert('survey', $recrod);
				$sid = pdo_insertid();
				if(!$sid) {
					message('保存调研失败, 请稍后重试.','error');
				}
			} else {
				if(pdo_update('survey', $recrod, array('sid' => $sid)) === false) {
					message('保存调研失败, 请稍后重试.');
				}
			}
			if(!$hasData) {
				$sql = 'DELETE FROM ' . tablename('survey_fields') . ' WHERE `sid`=:sid';
				$params = array();
				$params[':sid'] = $sid;
				pdo_query($sql, $params);
				foreach($_GPC['titles'] as $k => $v) {
					$field = array();
					$field['sid'] = $sid;
					$field['title'] = trim($v);
					$field['type'] = $_GPC['type'][$k];
					$field['essential'] = intval($_GPC['essentials'][$k]);
					$field['value'] = trim($_GPC['options'][$k]);
					$field['value'] = urldecode($field['value']);
					$field['description'] = $_GPC['descriptions'][$k];
					$field['displayorder'] = intval($_GPC['displayorder'][$k]);
					pdo_insert('survey_fields', $field);
				}
			}
			message('保存调研成功.', $this->createWebUrl('display', array('id' => $row['sid'])));
		}

		$types = array();
		$types['textarea'] = '文本(textarea)';
		$types['radio'] = '单选(radio)';
		$types['checkbox'] = '多选(checkbox)';

		if($sid) {
			$sql = 'SELECT * FROM ' . tablename('survey') . ' WHERE `weid`=:weid AND `sid`=:sid';
			$params = array();
			$params[':weid'] = $_W['weid'];
			$params[':sid'] = $sid;
			$activity = pdo_fetch($sql, $params);
			$activity['starttime'] && $activity['starttime'] = date('Y-m-d H:i:s', $activity['starttime']);
			$activity['endtime'] && $activity['endtime'] = date('Y-m-d H:i:s', $activity['endtime']);

			if($activity) {
				$sql = 'SELECT * FROM ' . tablename('survey_fields') . ' WHERE `sid` = :sid ORDER BY displayorder ASC,sfid ASC';
				$params = array();
				$params[':sid'] = $sid;
				$ds = pdo_fetchall($sql, $params);
			}
		}
		include $this->template('post');
	}

	public function doMobileSurvey() {
		global $_W, $_GPC;
		//$main_off = 1;	
		$sid = intval($_GPC['id']);
		$sql = 'SELECT * FROM ' . tablename('survey') . ' WHERE `weid`=:weid AND `sid`=:sid';
		$params = array();
		$params[':weid'] = $_W['weid'];
		$params[':sid'] = $sid;
		$activity = pdo_fetch($sql, $params);
		$activity['content']=htmlspecialchars_decode($activity['content']);
		$title = $activity['title'];
		if($activity['status'] != '1') {
			message('当前调研活动已经停止.');
		}
		if(!$activity) {
			message('非法访问.');
		}
		if ($activity['starttime'] > TIMESTAMP) {
			message('当前调研活动还未开始！');
		}
		if ($activity['endtime'] < TIMESTAMP) {
			message('当前调研活动已经结束！');
		}
		$sql = 'SELECT * FROM ' . tablename('survey_fields') . ' WHERE `sid`=:sid ORDER BY `displayorder` ASC,sfid ASC';
		$params = array();
		$params[':sid'] = $sid;
		$ds = pdo_fetchall($sql, $params);
		if(!$ds) {
			message('非法访问.');
		}
		$pertotal = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('survey_rows')." WHERE sid = :sid AND openid = :openid", array(':sid' => $sid, ':openid' => $_W['fans']['from_user']));
		if ($pertotal >= $activity['pertotal']) {
			$pererror = 1;
		}
		$user = fans_search($_W['fans']['from_user'],array('realname','mobile'));
		if(empty($user['realname']) || empty($user['mobile'])) {
			$userinfo = 0;
		}
		if(checksubmit()) {
			if ($pertotal >= $activity['pertotal']) {
				message('抱歉!每人只能提交'.$activity['pertotal']."次！", referer(), 'error');
			}
			//更新粉丝的手机号和姓名
			if($userinfo == '0') {
				fans_update($_W['fans']['from_user'],array('realname' => trim($_GPC['username']),'mobile' => trim($_GPC['telephone'])));
			}
			$row = array();
			$row['sid'] = $sid;
			$row['openid'] = $_W['fans']['from_user'];
			$row['suggest'] = trim($_GPC['suggest']);
			$row['createtime'] = TIMESTAMP;
			$datas = array();
			$fields = array();
			foreach($ds as $r) {
				$fields[$r['sfid']] = $r;
			}
			foreach($_GPC as $key => $value) {
				if(strexists($key, 'field_')) {
					$sfid = intval(str_replace('field_', '', $key));
					$field = $fields[$sfid];
					if($sfid && $field) {
						if(in_array($field['type'], array('textarea', 'radio'))) {
							$entry = array();
							$entry['sid'] = $sid;
							$entry['srid'] = 0;
							$entry['sfid'] = $sfid;
							$entry['createtime'] = TIMESTAMP;
							$entry['data'] = strval($value);
							$datas[] = $entry;
						}
						if(in_array($field['type'], array('checkbox'))) {
							$value = explode("||",$value);
							if(!is_array($value))
								continue;							
							foreach ($value as $k => $v) {
								$entry['sid'] = $sid;
								$entry['srid'] = 0;
								$entry['sfid'] = $sfid;
								$entry['createtime'] = TIMESTAMP;
								$entry['data'] = strval($v);
								$datas[] = $entry;
							}
						}
					}
				}
				
			}
			if(empty($datas)) {
				message('非法访问.', '', 'error');
			}
			if(pdo_insert('survey_rows', $row) != 1) {
				message('保存失败.');
			}
			$srid = pdo_insertid();
			if(empty($srid)) {
				message('保存失败.');
			}
			foreach($datas as &$r) {
				$r['srid'] = $srid;
				pdo_insert('survey_data', $r);
			}
			if(empty($activity['starttime'])) {
				$record = array();
				$record['starttime'] = TIMESTAMP;
				pdo_update('survey', $record, array('sid' => $sid));
			}
			message($activity['information'], 'refresh');
		}
		foreach($ds as &$r) {
			if($r['value']) {
				$r['options'] = explode("\r\n", $r['value']);
				
			}
		}
		
		include $this->template('submit');
	}

	public function doMobileMysurvey() {
		global $_W, $_GPC;
		$operation = !empty($_GPC['op']) ? $_GPC['op'] : '';
		if ($operation == 'display') {
			$rows = pdo_fetchall("SELECT * FROM ".tablename('survey_rows')." WHERE openid = :openid", array(':openid' => $_W['fans']['from_user']));
			if (!empty($rows)) {
				foreach ($rows as $row) {
					$sids[$row['sid']] = $row['sid'];
				}
				$survey = pdo_fetchall("SELECT * FROM ".tablename('survey')." WHERE sid IN (".implode(',', $sids).")", array(), 'sid');
			}
		} elseif ($operation == 'detail') {
			$id = intval($_GPC['id']);
			$row = pdo_fetch("SELECT * FROM ".tablename('survey_rows')." WHERE openid = :openid AND rerid = :rerid", array(':openid' => $_W['fans']['from_user'], ':rerid' => $id));
			if (empty($row)) {
				message('我的预约不存在或是已经被删除！');
			}
			$survey = pdo_fetch("SELECT * FROM ".tablename('survey')." WHERE sid = :sid", array(':sid' => $row['sid']));
			$survey['fields'] = pdo_fetchall("SELECT a.title, a.type, b.data FROM ".tablename('survey_fields')." AS a LEFT JOIN ".tablename('survey_data')." AS b ON a.refid = b.refid WHERE a.sid = :sid AND b.rerid = :rerid", array(':sid' => $row['sid'], ':rerid' => $id));
		}
		include $this->template('survey');
	}
}
