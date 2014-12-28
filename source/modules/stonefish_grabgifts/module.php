<?php
/**
 * 全民抢礼品模块定义
 *
 * @author 石头鱼
 * @url http://www.00393.com/
 */
defined('IN_IA') or exit('Access Denied');

class stonefish_grabgiftsModule extends WeModule {
	public $name = 'stonefish_grabgiftsModule';
	public $title = '全民抢礼品';
	public $table_reply  = 'stonefish_grabgifts_reply';
	public $table_list   = 'stonefish_grabgifts_userlist';	
	public $table_data   = 'stonefish_grabgifts_data';
	public $table_gift   = 'stonefish_grabgifts_gift';

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
		$reply['status'] = !isset($reply['status']) ? "1" : $reply['status'];
		$reply['subscribe'] = !isset($reply['subscribe']) ? "1" : $reply['subscribe'];
		$reply['opensubscribe'] = !isset($reply['opensubscribe']) ? "4" : $reply['opensubscribe'];		
	    $reply['share_shownum'] = !isset($reply['share_shownum']) ? "50" : $reply['share_shownum'];
		$reply['biaobiaonum'] = !isset($reply['biaobiaonum']) ? "50" : $reply['biaobiaonum'];
		$reply['picture'] = empty($reply['picture']) ? "./source/modules/stonefish_grabgifts/template/images/big_ads.jpg" : $reply['picture'];
		$reply['bgcolor'] = empty($reply['bgcolor']) ? "#eef3ef" : $reply['bgcolor'];
		$reply['textcolor'] = empty($reply['textcolor']) ? "#8d9695" : $reply['textcolor'];
		$reply['textcolort'] = empty($reply['textcolort']) ? "#f12500" : $reply['textcolort'];
		$reply['textcolorb'] = empty($reply['textcolorb']) ? "#cfd4d0" : $reply['textcolorb'];
		$reply['bgcolorbottom'] = empty($reply['bgcolorbottom']) ? "#ffffff" : $reply['bgcolorbottom'];
		$reply['bgcolorbottoman'] = empty($reply['bgcolorbottoman']) ? "#23cba8" : $reply['bgcolorbottoman'];
		$reply['textcolorbottom'] = empty($reply['textcolorbottom']) ? "#ffffff" : $reply['textcolorbottom'];
		$reply['bgcolorjiang'] = empty($reply['bgcolorjiang']) ? "#23cba8" : $reply['bgcolorjiang'];
		$reply['textcolorjiang'] = empty($reply['textcolorjiang']) ? "#ffffff" : $reply['textcolorjiang'];
		$reply['userinfo'] = empty($reply['userinfo']) ? "为了将奖品更快、更准确的送达您手中，请留下您的个人信息，谢谢!" : $reply['userinfo'];
		$reply['isrealname'] = !isset($reply['isrealname']) ? "1" : $reply['isrealname'];
		$reply['ismobile'] = !isset($reply['ismobile']) ? "1" : $reply['ismobile'];
		$reply['isfans'] = !isset($reply['isfans']) ? "1" : $reply['isfans'];
		$reply['copyrighturl'] = empty($reply['copyrighturl']) ? "http://".$_SERVER ['HTTP_HOST'] : $reply['copyrighturl'];	
		$reply['iscopyright'] = !isset($reply['iscopyright']) ? "0" : $reply['iscopyright'];	
		$reply['copyright'] = empty($reply['copyright']) ? $_W['account']['name'] : $reply['copyright'];
		$shouquan = base64_encode($_SERVER ['HTTP_HOST'].'anquan_ma_grabgifts');
		$reply['xuninum'] = !isset($reply['xuninum']) ? "500" : $reply['xuninum'];
		$reply['xuninumtime'] = !isset($reply['xuninumtime']) ? "86400" : $reply['xuninumtime'];
		$reply['xuninuminitial'] = !isset($reply['xuninuminitial']) ? "10" : $reply['xuninuminitial'];
		$reply['xuninumending'] = !isset($reply['xuninumending']) ? "50" : $reply['xuninumending'];
		$picture = $reply['picture'];	
		
		if (substr($picture,0,6)=='images'){
		    $picture = $_W['attachurl'] . $picture;
		}
		if (substr($picture,0,6)=='images'){
		    $picture = $_W['attachurl'] . $picture;
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
			'subscribe' => intval($_GPC['subscribe']),
			'opensubscribe' => intval($_GPC['opensubscribe']),			
			'bgcolor' => $_GPC['bgcolor'],
			'textcolor' => $_GPC['textcolor'],
			'textcolort' => $_GPC['textcolort'],
			'textcolorb' => $_GPC['textcolorb'],
			'bgcolorbottom' => $_GPC['bgcolorbottom'],
			'bgcolorbottoman' => $_GPC['bgcolorbottoman'],
			'textcolorbottom' => $_GPC['textcolorbottom'],
			'bgcolorjiang' => $_GPC['bgcolorjiang'],
			'textcolorjiang' => $_GPC['textcolorjiang'],
			'description' => $_GPC['description'],
			'content' => $_GPC['content'],	
			'start_time' => $start_time,
			'end_time' => $end_time,
			'biaobiaonum' => intval($_GPC['biaobiaonum']),
			'isvisits' => intval($_GPC['isvisits']),
			'status' => intval($_GPC['doings']),
			'share_shownum' => intval($_GPC['share_shownum']),			
			'shareurl' => $_GPC['shareurl'],
			'sharetitle' => $_GPC['sharetitle'],
			'sharecontent' => $_GPC['sharecontent'],	
			'userinfo' => $_GPC['userinfo'],
			'isrealname' => intval($_GPC['isrealname']),
			'ismobile' => intval($_GPC['ismobile']),
			'isweixin' => intval($_GPC['isweixin']),
			'isqqhao' => intval($_GPC['isqqhao']),
			'isemail' => intval($_GPC['isemail']),
			'isaddress' => intval($_GPC['isaddress']),
			'iscopyright' => intval($_GPC['iscopyright']),
			'isfans' => intval($_GPC['isfans']),
			'copyright' => $_GPC['copyright'],	
			'copyrighturl' => $_GPC['copyrighturl'],
			'xuninumtime' => $_GPC['xuninumtime'],
			'xuninuminitial' => $_GPC['xuninuminitial'],
			'xuninumending' => $_GPC['xuninumending'],
			'xuninum' => $_GPC['xuninum'],
		);
		
		//if ($_GPC['shouquan']==$_GPC['we7_ValidCode_server']){
		    if (empty($id)) {
			    pdo_insert($this->table_reply, $insert);
		    } else {			
			    pdo_update($this->table_reply, $insert, array('id' => $id));
		    }
		//}
		if (!empty($_GPC['award-title'])) {
			foreach ($_GPC['award-title'] as $index => $title) {
				if (empty($title)) {
					continue;
				}
				$update = array(
					'title' => $title,
					'description' => $_GPC['award-description'][$index],					
					'total' => $_GPC['award-total'][$index],		
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
					'description' => $_GPC['award-description-new'][$index],					
					'inkind' => intval($_GPC['award-inkind-new'][$index]),
					'total' => intval($_GPC['award-total-new'][$index]),					
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
				//}
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
			message('删除成功！', create_url('site/module', array('do' => 'userlist', 'name' => 'stonefish_grabgifts', 'id' => $rid, 'page' => $_GPC['page'])));
		}
		$where = '';
		!empty($_GPC['keywordnickname']) && $where .= " AND nickname LIKE '%{$_GPC['keywordnickname']}%'";
		!empty($_GPC['keywordid']) && $where .= " AND rid = '{$_GPC['keywordid']}'";
		!empty($rid) && $where .= " AND rid = '{$rid}'";

		
		$pindex = max(1, intval($_GPC['page']));
		$psize = 15;

		//取得用户列表
		$list_praise = pdo_fetchall('SELECT * FROM '.tablename($this->table_list).' WHERE weid= :weid '.$where.' order by `id` desc LIMIT ' . ($pindex - 1) * $psize . ',' . $psize, array(':weid' => $weid) );
		$total = pdo_fetchcolumn('SELECT COUNT(*) FROM '.tablename($this->table_list).' WHERE weid= :weid '.$where.' ', array(':weid' => $weid));
		$pager = pagination($total, $pindex, $psize);
		include $this->template('userlist');

	}
	public function doRankinglist() {		
		global $_GPC, $_W;
		checklogin();
		$weid = $_W['weid'];//当前公众号ID
		$rid = intval($_GPC['id']);
		if (empty($page)){$page = 1;}
		$where = '';
		!empty($_GPC['keywordnickname']) && $where .= " AND nickname LIKE '%{$_GPC['keywordnickname']}%'";
		!empty($_GPC['keywordid']) && $where .= " AND rid = '{$_GPC['keywordid']}'";
		!empty($rid) && $where .= " AND rid = '{$rid}'";

		
		$pindex = max(1, intval($_GPC['page']));
		$psize = 15;

		//取得用户列表
		$list_praise = pdo_fetchall('SELECT * FROM '.tablename($this->table_list).' WHERE weid= :weid '.$where.' order by `sharenum` desc,`datatime` asc LIMIT ' . ($pindex - 1) * $psize . ',' . $psize, array(':weid' => $weid) );
		$total = pdo_fetchcolumn('SELECT COUNT(*) FROM '.tablename($this->table_list).' WHERE weid= :weid '.$where.' ', array(':weid' => $weid));
		$pager = pagination($total, $pindex, $psize);
		include $this->template('rankinglist');

	}
	
	public function dosharedata() {		
		global $_GPC, $_W;
		checklogin();
		$weid = $_W['weid'];//当前公众号ID
		$uid = intval($_GPC['uid']);		
		$rid = intval($_GPC['rid']);
		if(empty($rid)){
		    $rid = intval($_GPC['id']);
		}
		if (checksubmit('delete')) {
			pdo_delete($this->table_data, " id IN ('".implode("','", $_GPC['select'])."')");
			message('删除成功！', create_url('site/module/sharedata', array('name' => 'stonefish_grabgifts', 'id' => $id, 'page' => $_GPC['page'])));
		}
		if (!empty($uid)){
			$Where = " AND `uid` = $uid";		
		}
		if (!empty($rid)){
			$Where = $Where." AND `rid` = $rid";		
		}

		$pindex = max(1, intval($_GPC['page']));
		$psize = 15;

		//取得分享点击详细数据
		$list_praisedata = pdo_fetchall('SELECT * FROM '.tablename($this->table_data).' WHERE weid= :weid '.$Where.'  order by `visitorstime` desc LIMIT ' . ($pindex - 1) * $psize . ',' . $psize, array(':weid' => $weid) );
		//查询分享人姓名电话开始
		foreach ($list_praisedata as $mid => $list) {
		    $reply1 = pdo_fetch("SELECT nickname FROM ".tablename($this->table_list)." WHERE weid = :weid and id = :id ", array(':weid' => $_W['weid'], ':id' => $list['uid']));
			$list_praisedata[$mid]['fnickname'] = $reply1['nickname'];			
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
		//奖品信息最小值
		$listgift = pdo_fetchall('SELECT * FROM '.tablename($this->table_gift).' WHERE rid = :rid order by `break`', array(':rid' => $rid));
		$listgiftmin = pdo_fetch('SELECT break FROM '.tablename($this->table_gift).' WHERE rid = :rid order by `break`', array(':rid' => $rid));
        $giftnummin = $listgiftmin['break'];
		$where = '';
		!empty($_GPC['keywordnickname']) && $where .= " AND nickname LIKE '%{$_GPC['keywordnickname']}%'";
		!empty($_GPC['keywordtel']) && $where .= " AND mobile LIKE '%{$_GPC['keywordtel']}%'";
		!empty($_GPC['keywordname']) && $where .= " AND realname LIKE '%{$_GPC['keywordname']}%'";

		$pindex = max(1, intval($_GPC['page']));
		$psize = 15;

		//取得用户列表
		$list_praise = pdo_fetchall('SELECT * FROM '.tablename($this->table_list).' WHERE weid= :weid and yaoqingnum>= :yaoqingnum '.$where.' order by `id` desc LIMIT ' . ($pindex - 1) * $psize . ',' . $psize, array(':weid' => $weid,':yaoqingnum' => $giftnummin) );
		foreach ($list_praise as $mid => $list) {
		    $parses = '';
		    foreach ($listgift as $row) {
                if($list['yaoqingnum']>=$row['break']){
				    $style='';
					if(strpos($list['grabgifts'],"|".$row['id']."|")!==false){
			            $parses = $parses.'<img src="'.$_W['attachurl'].''.$row['awardpic'].'" width="50px;" title="已领取：'.$row['title'].'" style="padding:0 10px;-webkit-filter: grayscale(1); /* Webkit */ filter: gray; /* IE6-9 */ filter: grayscale(1); /* W3C */"/>';
					}else{
					    $parses = $parses.'<a href="'.create_url('site/module/dosjiang', array('name' => 'stonefish_grabgifts','rid' => $rid,'id' => $list['id'],'giftid' => $row['id'])).'"><img src="'.$_W['attachurl'].''.$row['awardpic'].'" width="50px;" title="未领取，请点击发放：'.$row['title'].'" style="padding:0 10px;'.$style.'"/></a>';
					}
				    
				}
			}
			$list_praise[$mid]['praiseinfo'] =$parses;//奖品信息
		}
		$total = pdo_fetchcolumn('SELECT COUNT(*) FROM '.tablename($this->table_list).' WHERE weid= :weid and yaoqingnum>= :yaoqingnum'.$where.' ', array(':weid' => $weid,':yaoqingnum' => $giftnummin));
		$pager = pagination($total, $pindex, $psize);
		include $this->template('prizedata');

	}
	
	public function doEventlist() {		
		global $_GPC, $_W;
		$weid = $_W['weid'];//当前公众号ID
		$str = file_get_contents("http://www.00393.com/we7_client/stonefish_grabgifts/help.html");
		$pindex = max(1, intval($_GPC['page']));
		$psize = 15;
		$list_praise = pdo_fetchall('SELECT * FROM '.tablename($this->table_reply).' WHERE weid= :weid order by `id` desc LIMIT ' . ($pindex - 1) * $psize . ',' . $psize, array(':weid' => $weid) );
		$pager = pagination($total, $pindex, $psize);
		
		if (!empty($list_praise)) {
			foreach ($list_praise as $mid => $list) {
				$count = pdo_fetch("SELECT count(id) as dd FROM ".tablename($this->table_list)." WHERE rid= ".$list['rid']."");
		        $list_praise[$mid]['user_znum'] = $count['dd'];//参与人数
				$count = pdo_fetch("SELECT count(id) as dd FROM ".tablename($this->table_data)." WHERE rid= ".$list['rid']."");
		        $list_praise[$mid]['share_znum'] = $count['dd'];//分享人数
				
				$listpraise = pdo_fetchall('SELECT * FROM '.tablename($this->table_gift).' WHERE rid=:rid  order by `id`',array(':rid' => $list['rid']));
				if (!empty($listpraise)) {
			         $praiseinfo = '';
					 foreach ($listpraise as $row) {
					   $zigenum = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename($this->table_list)." WHERE weid = :weid and rid = :rid and yaoqingnum>= :yaoqingnum", array(':weid' => $_W['weid'], ':rid' => $list['rid'], ':yaoqingnum' => $row['break']));
					   $praiseinfo = $praiseinfo.'奖品：'.$row['title'].'；总数为：'.$row['total'].'；已领奖品数为：'.$row['total_winning'].'；拥有奖品资格粉丝数：'.$zigenum.'；没有领取奖品粉丝数：'.($zigenum-$row['total_winning']).'；还剩：<b>'.($row['total']-$row['total_winning']).'</b>个奖品没有发放<br/>';
			        }
		        }
				$praiseinfo = substr($praiseinfo,0,strlen($praiseinfo)-5); 
				$list_praise[$mid]['praiseinfo'] = $praiseinfo;//奖品情况
			}
		}
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
		message('屏蔽操作成功！', create_url('site/module/'.$praiselist.'', array('name' => 'stonefish_grabgifts', 'id' => $rid, 'page' => $_GPC['page'])));
	}	
	public function dodosjiang( $id = 0) {
		global $_GPC;
		$id = $_GPC['id'];
		$rid = $_GPC['rid'];
		$giftid = $_GPC['giftid'];
		
		$userinfo = pdo_fetch('SELECT * FROM '.tablename($this->table_list).' WHERE id=:id', array(':id' => $id));
		$gift = pdo_fetch('SELECT total,total_winning FROM '.tablename($this->table_gift).' WHERE id=:id', array(':id' => $giftid));
		if($gift['total'] > $gift['total_winning']){
		    if($userinfo['grabgifts']!=''){
			    pdo_update($this->table_list,array('grabgifts' => $userinfo['grabgifts'].$giftid.'|'),array('id' => $id));				
		    }else{
			    pdo_update($this->table_list,array('grabgifts' => '|'.$giftid.'|'),array('id' => $id));
		    }
            //增加中奖数量
		    pdo_update($this->table_gift,array('total_winning' => $gift['total_winning']+1),array('id' => $giftid));
			message('已成功发放奖品！', create_url('site/module/prizedata', array('name' => 'stonefish_grabgifts', 'id' => $rid, 'page' => $_GPC['page'])));
			exit;
		}else{
		    message('奖品已发送完了，没有奖品了', create_url('site/module/prizedata', array('name' => 'stonefish_grabgifts', 'id' => $rid, 'page' => $_GPC['page'])),'error');
		}		
	}	
	
	public function dodeldata( $id = 0) {
		global $_GPC;
		$rid = $_GPC['rid'];
		$id = $_GPC['id'];
		if (!empty($id)) {
			pdo_delete($this->table_data, " id = ".$id);
			message('删除成功！', create_url('site/module/sharedata', array('name' => 'stonefish_grabgifts', 'id' => $rid, 'page' => $_GPC['page'])));
		}		
		
	}
	
	public function doAwarding(){
	    message('此功能正在开发中, 12月底之前发布更新包.');	
	}
	
	public function doAwardmika(){
	    message('此功能正在开发中, 12月底之前发布更新包.');	
	}
    //导出数据
	public function dodownload(){
		require_once 'download.php';
	}

}