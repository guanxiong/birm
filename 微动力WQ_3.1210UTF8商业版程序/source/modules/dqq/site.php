<?php
/**
 * 打气球抽奖模块
 *
 * [WeLan System] Copyright (c) 2013 WeLan.CC
 */
defined('IN_IA') or exit('Access Denied');

class DqqModuleSite extends WeModuleSite {
	public function getProfileTiles() {

	}
	
	public function getHomeTiles($keyword = '') {
		$urls = array();
		$list = pdo_fetchall("SELECT name, id FROM ".tablename('rule')." WHERE weid = '{$_W['weid']}' AND module = 'dqq'".(!empty($keyword) ? " AND name LIKE '%{$keyword}%'" : ''));
		if (!empty($list)) {
			foreach ($list as $row) {
				$urls[] = array('title'=>$row['name'], 'url'=> $this->createMobileUrl('lottery', array('id' => $row['id'])));
			}
		}
		return $urls;
	}

	public function doMobileLottery() {
		global $_GPC, $_W;
		$title = '打气球送积分';
		
		$useragent = addslashes($_SERVER['HTTP_USER_AGENT']);
		if(strpos($useragent, 'MicroMessenger') === false && strpos($useragent, 'Windows Phone') === false ){
			exit;
		}
		
	
		if (empty($_W['fans']['from_user'])) {
			//message('非法访问，请重新发送消息进入打气球页面！1');
		}
		
		$fromuser = $_W['fans']['from_user'];
		//$profile = fans_require($fromuser, array('realname', 'mobile', 'qq'), '需要完善资料后才能打气球.');
		$id = intval($_GPC['id']);
		$dqq = pdo_fetch("SELECT id, maxlottery, default_tips, rule FROM ".tablename('dqq_reply')." WHERE rid = '$id' LIMIT 1");
		if (empty($dqq)) {
			message('非法访问，请重新发送消息进入打气球页面！2');
		}
		$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('dqq_winner')." WHERE createtime > '".strtotime(date('Y-m-d'))."' AND from_user = '$fromuser' ");
		$member = fans_search($fromuser);
		$myaward = pdo_fetchall("SELECT award, description FROM ".tablename('dqq_winner')." WHERE from_user = '{$fromuser}'  AND rid = '$id' ORDER BY createtime DESC");

		$sql = "SELECT a.award, b.realname FROM ".tablename('dqq_winner')." AS a
				LEFT JOIN ".tablename('fans')." AS b ON a.from_user = b.from_user WHERE b.mobile <> '' AND b.realname <> ''  AND a.rid = '$id' ORDER BY a.createtime DESC LIMIT 20";
		$otheraward = pdo_fetchall($sql);
		include $this->template('lottery');
	}

	public function doMobileGetAward() {
		global $_GPC, $_W;
		if (empty($_W['fans']['from_user'])) {
			//message('非法访问，请重新发送消息进入打气球页面！3');
		}
		
		$fromuser = $_W['fans']['from_user'];
		//$fromuser = 'oFTBbt4eMCKhiNUhXYHCp5CA1E80';
		$id = intval($_GPC['id']);
		$dqq = pdo_fetch("SELECT id, periodlottery, maxlottery, default_tips, misscredit, hitcredit FROM ".tablename('dqq_reply')." WHERE rid = '$id' LIMIT 1");
		if (empty($dqq)) {
			message('非法访问，请重新发送消息进入打气球页面！4');
		}
		$result = array('status' => -1, 'message' => '');
		if (!empty($dqq['periodlottery'])) {
			$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('dqq_winner')." WHERE createtime > '".strtotime(date('Y-m-d'))."' AND from_user = '$fromuser'  AND rid = '$id'");
			$lastdate = pdo_fetchcolumn("SELECT createtime FROM ".tablename('dqq_winner')." WHERE from_user = '$fromuser'  ORDER BY createtime DESC");
			if (($total >= intval($dqq['maxlottery'])) && strtotime(date('Y-m-d')) < strtotime(date('Y-m-d', $lastdate)) + $dqq['periodlottery'] * 86400) {
				$result['message'] = '没箭啦';
				message($result, '', 'ajax');
			}
		} else {
			$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('dqq_winner')." WHERE createtime > '".strtotime(date('Y-m-d'))."' AND from_user = '$fromuser' ");
			if (!empty($dqq['maxlottery']) && $total >= $dqq['maxlottery']) {
				$result['message'] = '今天没箭了';
				message($result, '', 'ajax');
			}
		}
		
		
		$gifts = pdo_fetchall("SELECT id, probalilty FROM ".tablename('dqq_award')." WHERE rid = '$id' ORDER BY probalilty ASC");
		//计算每个礼物的概率
		$probability = 0;
		$rate = 1;
		$award = array();
		foreach ($gifts as $name => $gift){
			if (empty($gift['probalilty'])) {
				continue;
			}
			if ($gift['probalilty'] < 1) {
				$temp = explode('.', $gift['probalilty']);
				$temp = pow(10, strlen($temp[1]));
				$rate = $temp < $rate ? $rate : $temp;
			}
			$probability = $probability + $gift['probalilty'] * $rate;
			$award[] = array('id' => $gift['id'], 'probalilty' => $probability);
		}
		$all = 100 * $rate;
		if($probability < $all){
			$award[] = array('title' => '','probalilty' => $all);
		}
		mt_srand((double) microtime()*1000000);
		$rand = mt_rand(1, $all);
		foreach ($award as $key => $gift){
			if(isset($award[$key - 1])){
				if($rand > $award[$key -1]['probalilty'] && $rand <= $gift['probalilty']){
					$awardid = $gift['id'];
					break;
				}
			}else{
				if($rand > 0 && $rand <= $gift['probalilty']){
					$awardid = $gift['id'];
					break;
				}
			}
		}
		
		$title = '';
		$result['message'] = '唉，没中';
		$data = array(
			'rid' => $id,
			'from_user' => $fromuser,
			'status' => empty($gift['inkind']) ? 1 : 0,
			'createtime' => TIMESTAMP,
		);
		$credit = array(
			'rid' => $id,
			'award' => (empty($awardid) ? '未中' : '中') . '奖励积分',
			'from_user' => $fromuser,
			'status' => 3,
			'description' => (empty($awardid) ? $dqq['misscredit'] : $dqq['hitcredit']),
			'createtime' => TIMESTAMP,
		);
		$weid=$_W['weid'];
		if (!empty($awardid)) {
			$gift = pdo_fetch("SELECT * FROM ".tablename('dqq_award')." WHERE rid = '$id' AND id = '$awardid'");
			
			if ($gift['total'] > 0) {
				$data['award'] = $gift['title'];
				$credit1=intval($gift['get_jf']);
				/*
				if (!empty($gift['inkind'])) {
					$data['description'] = $gift['description'];
					//pdo_query("UPDATE ".tablename('dqq_award')." SET total = total - 1 WHERE rid = '$id' AND id = '$awardid'");
					pdo_query("UPDATE ".tablename('fans')." SET credit1 = credit1 + $credit1  WHERE from_user = $fromuser AND weid = '$weid'");
				} else {
					//$gift['activation_code'] = iunserializer($gift['activation_code']);
					//$code = array_pop($gift['activation_code']);
					//pdo_query("UPDATE ".tablename('dqq_award')." SET total = total - 1, activation_code = '".iserializer($gift['activation_code'])."' WHERE rid = '$id' AND id = '$awardid'");
					
				}
				*/
				$sql="UPDATE ".tablename('fans')." SET credit1 = credit1 + $credit1  WHERE from_user = '$fromuser' AND weid = '$weid' limit 1";
				pdo_query($sql);
				
				$data['description'] = $gift['title'];
				$result['message'] = ''.$data['award'].'！' ;
				$result['status'] = 0;
			} else {
				$credit['description'] = $dqq['misscredit'];
				$credit['award'] = '未中奖励积分';
			}
		}
		!empty($credit['description']) && $result['message'] .= '<br />' . $credit['award'] . '：'. $credit['description'];
		$data['aid'] = $gift['id'];
		if (!empty($credit['description'])) {
			pdo_insert('dqq_winner', $credit);
		}
		pdo_insert('dqq_winner', $data);
		//$result['myaward'] = pdo_fetchall("SELECT award, description FROM ".tablename('dqq_winner')." WHERE from_user = '{$fromuser}' AND award <> '' AND rid = '$id' ORDER BY createtime DESC");
		message($result, '', 'ajax');
	}

	public function doMobileRegister() {
		global $_GPC, $_W;
		$title = '打气球领奖登记个人信息';
		if (!empty($_GPC['submit'])) {
			if (empty($_W['fans']['from_user'])) {
				//message('非法访问，请重新发送消息进入打气球页面！0');
			}
			$data = array(
				'realname' => $_GPC['realname'],
				'mobile' => $_GPC['mobile'],
				'qq' => $_GPC['qq'],
			);
			if (empty($data['realname'])) {
				die('<script>alert("请填写您的真实姓名！");location.reload();</script>');
			}
			if (empty($data['mobile'])) {
				die('<script>alert("请填写您的手机号码！");location.reload();</script>');
			}
			fans_update($_W['fans']['from_user'], $data);
			die('<script>alert("登记成功！");location.href = "'.$this->createMobileUrl('lottery', array('id' => $_GPC['id'])).'";</script>');
		}
		include $this->template('register');
	}

}
