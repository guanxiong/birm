<?php
/**
 * 幸运拆礼盒模块定义
 *
 */
defined('IN_IA') or exit('Access Denied');

class stonefish_chailiheModule extends WeModule {
	public $name = 'stonefish_chailiheModule';
	public $title = '幸运拆礼盒';
	public $table_reply  = 'stonefish_chailihe_reply';
	public $table_list   = 'stonefish_chailihe_userlist';	
	public $table_data   = 'stonefish_chailihe_data';
	public $table_gift   = 'stonefish_chailihe_gift';

	public function fieldsFormDisplay($rid = 0) {
		//要嵌入规则编辑页的自定义内容，这里 $rid 为对应的规则编号，新增时为 0
		global $_W;
		if (!empty($rid)) {
			$reply = pdo_fetch("SELECT * FROM ".tablename($this->table_reply)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));
			$award = pdo_fetchall("SELECT * FROM ".tablename($this->table_gift)." WHERE rid = :rid ORDER BY `id` ASC", array(':rid' => $rid));
			if (!empty($award)) {
				foreach ($award as &$pointer) {
					if (!empty($pointer['activation_code'])) {
						$pointer['activation_code'] = implode("\n", (array)iunserializer($pointer['activation_code']));
					}
				}
			}
 		}else{
		    $reply = array(
				'periodlottery' => 1,
				'maxlottery' => 1,
			);
		}
		$reply['start_time'] = empty($reply['start_time']) ? strtotime(date('Y-m-d H:i')) : $reply['start_time'];
		$reply['end_time'] = empty($reply['end_time']) ? strtotime("+1 week") : $reply['end_time'];
		$reply['music'] = !isset($reply['music']) ? "1" : $reply['music'];
		$reply['musicbg'] = empty($reply['musicbg']) ? "./source/modules/stonefish_chailihe/template/images/bg.mp3" : $reply['musicbg'];
		$reply['subscribe'] = !isset($reply['subscribe']) ? "0" : $reply['subscribe'];
		$reply['opensubscribe'] = !isset($reply['opensubscribe']) ? "0" : $reply['opensubscribe'];
		$reply['opentype'] = !isset($reply['opentype']) ? "0" : $reply['opentype'];	
		$reply['number_num'] = !isset($reply['number_num']) ? "1" : $reply['number_num'];	
		$reply['number_num_day'] = !isset($reply['number_num_day']) ? "1" : $reply['number_num_day'];	
	    $reply['share_shownum'] = !isset($reply['share_shownum']) ? "50" : $reply['share_shownum'];
		$reply['picture'] = empty($reply['picture']) ? "./source/modules/stonefish_chailihe/template/images/big_ads.jpg" : $reply['picture'];
		$reply['picbg01'] = empty($reply['picbg01']) ? "./source/modules/stonefish_chailihe/template/images/bg.jpg" : $reply['picbg01'];
		$reply['picbg02'] = empty($reply['picbg02']) ? "./source/modules/stonefish_chailihe/template/images/bg_common.jpg" : $reply['picbg02'];
		$reply['picbg03'] = empty($reply['picbg03']) ? "./source/modules/stonefish_chailihe/template/images/bg_myprize.jpg" : $reply['picbg03'];
		$reply['imgpic01'] = empty($reply['imgpic01']) ? "./source/modules/stonefish_chailihe/template/images/default_img.jpg" : $reply['imgpic01'];
		//$shouquan = base64_encode($_SERVER ['HTTP_HOST'].'anquan_ma_chailihe');		
		$picture = $reply['picture'];
		$picbg01 = $reply['picbg01'];
		$picbg02 = $reply['picbg02'];
		$picbg03 = $reply['picbg03'];
		$imgpic01 = $reply['imgpic01'];
		$imgpic02 = $reply['imgpic02'];
		$imgpic03 = $reply['imgpic03'];
		$imgpic04 = $reply['imgpic04'];
		$imgpic05 = $reply['imgpic05'];
		
		if (substr($picture,0,6)=='images'){
		    $picture = $_W['attachurl'] . $picture;
		}
		if (substr($picbg01,0,6)=='images'){
		    $picbg01 = $_W['attachurl'] . $picbg01;
		}
		if (substr($picbg02,0,6)=='images'){
		    $picbg02 = $_W['attachurl'] . $picbg02;
		}
		if (substr($picbg03,0,6)=='images'){
		    $picbg03 = $_W['attachurl'] . $picbg03;
		}		
		if (substr($imgpic01,0,6)=='images'){
			$imgpic01 = $_W['attachurl'] . $imgpic01;
		}
		if (substr($imgpic02,0,6)=='images'){
			$imgpic02 = $_W['attachurl'] . $imgpic02;
		}
		if (substr($imgpic03,0,6)=='images'){
			$imgpic03 = $_W['attachurl'] . $imgpic03;
		}
		if (substr($imgpic04,0,6)=='images'){
			$imgpic04 = $_W['attachurl'] . $imgpic04;
		}
		if (substr($imgpic05,0,6)=='images'){
			$imgpic05 = $_W['attachurl'] . $imgpic05;
		}		

		include $this->template('form');
		
	}

	public function fieldsFormValidate($rid = 0) {
		//规则编辑保存时，要进行的数据验证，返回空串表示验证无误，返回其他字符串将呈现为错误提示。这里 $rid 为对应的规则编号，新增时为 0
		return '';
	}

	public function fieldsFormSubmit($rid) {
		//规则验证无误保存入库时执行，这里应该进行自定义字段的保存。这里 $rid 为对应的规则编号
		global $_GPC, $_W;
		$weid = $_W['weid'];
		$id = intval($_GPC['reply_id']);
		list($start_time,$end_time)=explode(' - ',$_GPC['activitytime']);
		$start_time=strtotime($start_time);
		$end_time=strtotime($end_time);
		$insert = array(
			'rid' => $rid,
			'weid' => $weid,
            'title' => $_GPC['title'],			
			'picture' => $_GPC['picture'],
			'music' => $_GPC['music'],
			'musicbg' => $_GPC['musicbg'],
			'subscribe' => $_GPC['subscribe'],
			'opensubscribe' => $_GPC['opensubscribe'],
			'opentype' => $_GPC['opentype'],
			'picbg01' => $_GPC['picbg01'],
			'picbg02' => $_GPC['picbg02'],
			'picbg03' => $_GPC['picbg03'],			
			'description' => $_GPC['description'],
			'activityinfo' => $_GPC['activityinfo'],
			'content' => $_GPC['content'],	
			'start_time' => $start_time,
			'end_time' => $end_time,
			'status' => intval($_GPC['status']),			
			'share_shownum' => $_GPC['share_shownum'],
			'openshare' => $_GPC['openshare'],
			'shareurl' => $_GPC['shareurl'],
			'sharetitle' => $_GPC['sharetitle'],
			'sharecontent' => $_GPC['sharecontent'],			
			'number_num' => $_GPC['number_num'],
			'number_num_day' => $_GPC['number_num_day'],			
			'imgpic01' => $_GPC['imgpic01'],
			'imgpic02' => $_GPC['imgpic02'],
			'imgpic03' => $_GPC['imgpic03'],
			'imgpic04' => $_GPC['imgpic04'],
			'imgpic05' => $_GPC['imgpic05'],
		);
		
		//if ($_GPC['shouquan']==$_GPC['we7_ValidCode_server']){
		    if (empty($id)) {
			    pdo_insert($this->table_reply, $insert);
		    } else {			
			    pdo_update($this->table_reply, $insert, array('id' => $id));
		    }
	//	}
		if (!empty($_GPC['award-title'])) {
			foreach ($_GPC['award-title'] as $index => $title) {
				if (empty($title)) {
					continue;
				}
				$update = array(
					'title' => $title,
					'lihetitle' => $_GPC['award-lihetitle'][$index],
					'description' => $_GPC['award-description'][$index],
					'probalilty' => $_GPC['award-probalilty'][$index],
					'total' => $_GPC['award-total'][$index],
					'gift' => $_GPC['award-gift'][$index],
					'giftVoice' => $_GPC['award-giftVoice'][$index],
					'break' => $_GPC['award-break'][$index],
					'awardpic' => $_GPC['awardpic'][$index],
					'awardpass' => $_GPC['award-pass'][$index],
					'activation_code' => '',
					'activation_url' => '',
				);
				if (empty($update['inkind']) && !empty($_GPC['award-activation-code'][$index])) {
					$activationcode = explode("\n", $_GPC['award-activation-code'][$index]);
					$update['activation_code'] = iserializer($activationcode);
					$update['total'] = count($activationcode);
					$update['activation_url'] = $_GPC['award-activation-url'][$index];
				}
			//	if ($_GPC['shouquan']==$_GPC['we7_ValidCode_server']){
				    pdo_update($this->table_gift, $update, array('id' => $index));
			//	}				
			}
		}
		//处理添加
		if (!empty($_GPC['award-title-new'])) {
			foreach ($_GPC['award-title-new'] as $index => $title) {
				if (empty($title)) {
					continue;
				}
				$insert = array(
					'rid' => $rid,
					'title' => $title,
					'lihetitle' => $_GPC['award-lihetitle-new'][$index],					
					'description' => $_GPC['award-description-new'][$index],
					'probalilty' => $_GPC['award-probalilty-new'][$index],
					'inkind' => intval($_GPC['award-inkind-new'][$index]),
					'total' => intval($_GPC['award-total-new'][$index]),
					'gift' => $_GPC['award-gift-new'][$index],
					'giftVoice' => $_GPC['award-giftVoice-new'][$index],
					'break' => $_GPC['award-break-new'][$index],
					'awardpic' => $_GPC['awardpic-new'][$index],
					'awardpass' => $_GPC['award-pass-new'][$index],
					'activation_code' => '',
					'activation_url' => '',
				);
				
				
				$files =$_FILES;
				$f = 'awardpic-new'.$index;
            	$old = $_GPC['awardpic-new'.$index];      
            	if (!empty($files[$f]['tmp_name'])) {                    
                    $upload = file_upload($files[$f]);
                    if (is_error($upload)) {
                        message($upload['message'], '', 'error');
                    }
					$insert['awardpic'] = $upload['path'];
            
            	}else if(!empty($old)){
					$insert['awardpic'] = $old;
            	}

				if (empty($insert['inkind'])) {
					$activationcode = explode("\n", $_GPC['award-activation-code-new'][$index]);
					$insert['activation_code'] = iserializer($activationcode);
					$insert['total'] = count($activationcode);
					$insert['activation_url'] = $_GPC['award-activation-url-new'][$index];
				}
				//if ($_GPC['shouquan']==$_GPC['we7_ValidCode_server']){
				    pdo_insert($this->table_gift, $insert);
			//	}
			}
		}

	}

	public function ruleDeleted($rid) {
		//删除规则时调用，这里 $rid 为对应的规则编号
		global $_W;		
		pdo_delete($this->table_reply, "rid = '".$rid."'");
		pdo_delete($this->table_list, "rid = '".$rid."'");
		pdo_delete($this->table_data, "rid = '".$rid."'");
		pdo_delete($this->table_gift, "rid = '".$rid."'");
		message('删除活动成功！', referer(), 'success');
		return true;
	}

	public function settingsDisplay($settings) {
		global $_GPC, $_W;
		if(checksubmit()) {
			$cfg = array();
			$cfg['appid'] = $_GPC['appid'];
			$cfg['secret'] = $_GPC['secret'];
			if($this->saveSettings($cfg)) {
				message('保存成功', 'refresh');
			}
		}		
		include $this->template('setting');
	}

	public function douserlist() {		
		global $_GPC, $_W;
		checklogin();
		$weid = $_W['weid'];//当前公众号ID
		$rid = intval($_GPC['id']);
		if (checksubmit('delete')) {
			pdo_delete($this->table_list, " id IN ('".implode("','", $_GPC['select'])."')");
			message('删除成功！', create_url('site/module', array('do' => 'userlist', 'name' => 'stonefish_chailihe', 'id' => $id, 'page' => $_GPC['page'])));
		}
		$where = '';
		!empty($_GPC['keywordtel']) && $where .= " AND a.mobile LIKE '%{$_GPC['keywordtel']}%'";
		!empty($_GPC['keywordname']) && $where .= " AND a.realname LIKE '%{$_GPC['keywordname']}%'";
		!empty($_GPC['keywordid']) && $where .= " AND a.rid = '{$_GPC['keywordid']}'";
		!empty($rid) && $where .= " AND a.rid = '{$rid}'";

		$rules = pdo_fetchall('SELECT `id`,`name` FROM '.tablename('rule').' WHERE `module`=\'stonefish_chailihe\'');
		$pindex = max(1, intval($_GPC['page']));
		$psize = 15;

		//取得用户列表
		$list_praise = pdo_fetchall('SELECT a.*,b.lihetitle FROM '.tablename($this->table_list).' as a left join '.tablename($this->table_gift).' as b on a.liheid=b.id  WHERE a.weid= :weid '.$where.' order by `id` desc LIMIT ' . ($pindex - 1) * $psize . ',' . $psize, array(':weid' => $weid) );
		$total = pdo_fetchcolumn('SELECT COUNT(*) FROM '.tablename($this->table_list).' as a left join '.tablename($this->table_gift).' as b on a.liheid=b.id  WHERE a.weid= :weid '.$where.' ', array(':weid' => $weid));
		$pager = pagination($total, $pindex, $psize);
		include $this->template('userlist');

	}
	
	public function dosharedata() {		
		global $_GPC, $_W;
		checklogin();
		$weid = $_W['weid'];//当前公众号ID
		$id = intval($_GPC['id']);
		$uid = intval($_GPC['uid']);
		$rid = intval($_GPC['id']);
		if (checksubmit('delete')) {
			pdo_delete($this->table_data, " id IN ('".implode("','", $_GPC['select'])."')");
			message('删除成功！', create_url('site/module/sharedata', array('name' => 'stonefish_chailihe', 'id' => $id, 'page' => $_GPC['page'])));
		}
		if (!empty($uid)){
			$Where = " AND `uid` = $uid";		
		}
		if (!empty($rid)){
			$Where = $Where." AND `rid` = $rid";		
		}
		$rules = pdo_fetchall('SELECT `id`,`name` FROM '.tablename('rule').' WHERE `module`=\'stonefish_chailihe\'');
		$pindex = max(1, intval($_GPC['page']));
		$psize = 15;

		//取得分享点击详细数据
		$list_praisedata = pdo_fetchall('SELECT * FROM '.tablename($this->table_data).' WHERE weid= :weid '.$Where.'  order by `visitorstime` desc LIMIT ' . ($pindex - 1) * $psize . ',' . $psize, array(':weid' => $weid) );
		//查询分享人姓名电话开始
		foreach ($list_praisedata as $mid => $list) {
		    $reply1 = pdo_fetch("SELECT realname,mobile FROM ".tablename($this->table_list)." WHERE weid = :weid and id = :id ", array(':weid' => $_W['weid'], ':id' => $list['uid']));
			$list_praisedata[$mid]['frealname'] = $reply1['realname'];
			$list_praisedata[$mid]['fmobile'] = $reply1['mobile'];			
		}
		//查询分享人姓名电话结束
		$total = pdo_fetchcolumn('SELECT COUNT(*) FROM '.tablename($this->table_data).' WHERE weid= :weid '.$Where.'  order by `visitorstime` desc ', array(':weid' => $weid));
		$pager = pagination($total, $pindex, $psize);
		include $this->template('sharedata');

	}
	
	public function doprizedata() {		
		global $_GPC, $_W;
		checklogin();
		$weid = $_W['weid'];//当前公众号ID
		$rid = intval($_GPC['id']);
		if (checksubmit('delete')) {
			pdo_delete($this->table_list, " id IN ('".implode("','", $_GPC['select'])."')");
			message('删除成功！', create_url('site/module', array('do' => 'prizedata', 'name' => 'stonefish_chailihe', 'id' => $id, 'page' => $_GPC['page'])));
		}
		$where = '';
		!empty($_GPC['keywordtel']) && $where .= " AND a.mobile LIKE '%{$_GPC['keywordtel']}%'";
		!empty($_GPC['keywordname']) && $where .= " AND a.realname LIKE '%{$_GPC['keywordname']}%'";
		!empty($_GPC['keywordid']) && $where .= " AND a.rid = '{$_GPC['keywordid']}'";
		!empty($rid) && $where .= " AND a.rid = '{$rid}'";

		$rules = pdo_fetchall('SELECT `id`,`name` FROM '.tablename('rule').' WHERE `module`=\'stonefish_chailihe\'');
		$pindex = max(1, intval($_GPC['page']));
		$psize = 15;

		//取得用户列表
		$list_praise = pdo_fetchall('SELECT a.*,b.lihetitle FROM '.tablename($this->table_list).' as a left join '.tablename($this->table_gift).' as b on a.liheid=b.id  WHERE a.zhongjiang>0 and a.weid= :weid '.$where.' order by `id` desc LIMIT ' . ($pindex - 1) * $psize . ',' . $psize, array(':weid' => $weid) );
		$total = pdo_fetchcolumn('SELECT COUNT(*) FROM '.tablename($this->table_list).' as a left join '.tablename($this->table_gift).' as b on a.liheid=b.id  WHERE a.zhongjiang>0 and a.weid= :weid '.$where.' ', array(':weid' => $weid));
		$pager = pagination($total, $pindex, $psize);
		include $this->template('userlist');

	}
	
	public function doEventlist() {		
		global $_GPC, $_W;
		$weid = $_W['weid'];//当前公众号ID
		$str = file_get_contents("http://www.00393.com/we7_client/stonefish_chailihe/help.html");
		
		include $this->template('event');

	}
		
	public function dostatus( $rid = 0) {
		global $_GPC;
		$rid = $_GPC['rid'];
		echo $rid;
		$insert = array(
			'status' => $_GPC['status']
		);
		
		pdo_update($this->table_reply,$insert,array('rid' => $rid));
		message('模块操作成功！', referer(), 'success');
	}
	public function dodos( $id = 0) {
		global $_GPC;
		$rid = $_GPC['rid'];
		$id = $_GPC['id'];
		$praiselist = $_GPC['ac'];
		echo $id;
		$insert = array(
			'status' => $_GPC['status']
		);
		
		pdo_update($this->table_list,$insert,array('id' => $id,'rid' => $rid));
		message('屏蔽操作成功！', create_url('site/module/'.$praiselist.'', array('name' => 'stonefish_chailihe', 'id' => $rid, 'page' => $_GPC['page'])));
	}	
	public function dodosjiang( $id = 0) {
		global $_GPC;
		$rid = $_GPC['rid'];
		$id = $_GPC['id'];
		$praiselist = $_GPC['ac'];
		echo $id;
		$insert = array(
			'zhongjiang' => $_GPC['status']
		);
		
		pdo_update($this->table_list,$insert,array('id' => $id,'rid' => $rid));
		message('已成功发放奖品！', create_url('site/module/'.$praiselist.'', array('name' => 'stonefish_chailihe', 'id' => $rid, 'page' => $_GPC['page'])));
	}	
	
	public function dodeldata( $id = 0) {
		global $_GPC;
		$rid = $_GPC['rid'];
		$id = $_GPC['id'];
		if (!empty($id)) {
			pdo_delete($this->table_data, " id = ".$id);
			message('删除成功！', create_url('site/module/sharedata', array('name' => 'stonefish_chailihe', 'id' => $rid, 'page' => $_GPC['page'])));
		}		
		
	}
	
    //导出数据
	public function dodownload(){
		require_once 'download.php';
	}

}