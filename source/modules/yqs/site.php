<?php
/**
 * 摇钱树抽奖模块
 *
 * [WeLan System] Copyright (c) 2013 WeLan.CC
 */
defined('IN_IA') or exit('Access Denied');

class YqsModuleSite extends WeModuleSite {
	public function getProfileTiles() {

	}
	
	public function getHomeTiles($keyword = '') {
		$urls = array();
		$list = pdo_fetchall("SELECT name, id FROM ".tablename('rule')." WHERE weid = '{$_W['weid']}' AND module = 'yqs'".(!empty($keyword) ? " AND name LIKE '%{$keyword}%'" : ''));
		if (!empty($list)) {
			foreach ($list as $row) {
				$urls[] = array('title'=>$row['name'], 'url'=> $this->createMobileUrl('lottery', array('id' => $row['id'])));
			}
		}
		return $urls;
	}

	public function doMobileLottery() {
		global $_GPC, $_W;
		$title = '摇钱树送积分';
		
		$useragent = addslashes($_SERVER['HTTP_USER_AGENT']);
		if(strpos($useragent, 'MicroMessenger') === false && strpos($useragent, 'Windows Phone') === false ){
			exit;
		}
	
	
		if (empty($_W['fans']['from_user'])) {
			//message('非法访问，请重新发送消息进入摇钱树页面！1');
		}
		
		$fromuser = $_W['fans']['from_user'];
		//$profile = fans_require($fromuser, array('realname', 'mobile', 'qq'), '需要完善资料后才能摇钱树.');
		$id = intval($_GPC['id']);
		$yqs = pdo_fetch("SELECT id, maxlottery, default_tips, rule FROM ".tablename('yqs_reply')." WHERE rid = '$id' LIMIT 1");
		if (empty($yqs)) {
			message('非法访问，请重新发送消息进入摇钱树页面！2');
		}
		$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('yqs_winner')." WHERE createtime > '".strtotime(date('Y-m-d'))."' AND from_user = '$fromuser' AND award <> ''");
		$member = fans_search($fromuser);
		$myaward = pdo_fetchall("SELECT award, description FROM ".tablename('yqs_winner')." WHERE from_user = '{$fromuser}' AND award <> '' AND rid = '$id' ORDER BY createtime DESC");

		$sql = "SELECT a.award, b.realname FROM ".tablename('yqs_winner')." AS a
				LEFT JOIN ".tablename('fans')." AS b ON a.from_user = b.from_user WHERE b.mobile <> '' AND b.realname <> '' AND a.award <> '' AND a.rid = '$id' ORDER BY a.createtime DESC LIMIT 20";
		$otheraward = pdo_fetchall($sql);
		include $this->template('lottery');
	}

	public function doMobileGetAward() {
		global $_GPC, $_W;
		if (empty($_W['fans']['from_user'])) {
			//message('非法访问，请重新发送消息进入摇钱树页面！3');
		}
		
		$fromuser = $_W['fans']['from_user'];
		//$fromuser = 'oFTBbt4eMCKhiNUhXYHCp5CA1E80';
		$id = intval($_GPC['id']);
		$yqs = pdo_fetch("SELECT id, periodlottery, maxlottery, default_tips, misscredit, hitcredit FROM ".tablename('yqs_reply')." WHERE rid = '$id' LIMIT 1");
		if (empty($yqs)) {
			message('非法访问，请重新发送消息进入摇钱树页面！4');
		}
		$result = array('status' => -1, 'message' => '');
		if (!empty($yqs['periodlottery'])) {
			$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('yqs_winner')." WHERE createtime > '".strtotime(date('Y-m-d'))."' AND from_user = '$fromuser'  AND rid = '$id'");
			$lastdate = pdo_fetchcolumn("SELECT createtime FROM ".tablename('yqs_winner')." WHERE from_user = '$fromuser'  ORDER BY createtime DESC");
			if (($total >= intval($yqs['maxlottery'])) && strtotime(date('Y-m-d')) < strtotime(date('Y-m-d', $lastdate)) + $yqs['periodlottery'] * 86400) {
				$result['message'] = '您还未到达可以再次摇钱树的时间。下次可摇时间为'.date('Y-m-d', strtotime(date('Y-m-d', $lastdate)) + $yqs['periodlottery'] * 86400);
				message($result, '', 'ajax');
			}
		} else {
			$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('yqs_winner')." WHERE createtime > '".strtotime(date('Y-m-d'))."' AND from_user = '$fromuser' ");
			if (!empty($yqs['maxlottery']) && $total >= $yqs['maxlottery']) {
				$result['message'] = $yqs['periodlottery'] ? '您已经超过当日摇钱树次数' : '您已经超过最大摇钱树次数';
				message($result, '', 'ajax');
			}
		}
		
		
		$gifts = pdo_fetchall("SELECT id, probalilty FROM ".tablename('yqs_award')." WHERE rid = '$id' ORDER BY probalilty ASC");
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
		$result['message'] = empty($yqs['default_tips']) ? '很遗憾,您没能中奖！' : $yqs['default_tips'];
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
			'description' => (empty($awardid) ? $yqs['misscredit'] : $yqs['hitcredit']),
			'createtime' => TIMESTAMP,
		);
		$weid=$_W['weid'];
		if (!empty($awardid)) {
			$gift = pdo_fetch("SELECT * FROM ".tablename('yqs_award')." WHERE rid = '$id' AND id = '$awardid'");
			
			if ($gift['total'] > 0) {
				$data['award'] = $gift['title'];
				$credit1=intval($gift['get_jf']);
				/*
				if (!empty($gift['inkind'])) {
					$data['description'] = $gift['description'];
					//pdo_query("UPDATE ".tablename('yqs_award')." SET total = total - 1 WHERE rid = '$id' AND id = '$awardid'");
					pdo_query("UPDATE ".tablename('fans')." SET credit1 = credit1 + $credit1  WHERE from_user = $fromuser AND weid = '$weid'");
				} else {
					//$gift['activation_code'] = iunserializer($gift['activation_code']);
					//$code = array_pop($gift['activation_code']);
					//pdo_query("UPDATE ".tablename('yqs_award')." SET total = total - 1, activation_code = '".iserializer($gift['activation_code'])."' WHERE rid = '$id' AND id = '$awardid'");
					
				}
				*/
				$sql="UPDATE ".tablename('fans')." SET credit1 = credit1 + $credit1  WHERE from_user = '$fromuser' AND weid = '$weid' limit 1";
				pdo_query($sql);
				
				$data['description'] = $gift['title'];
				$result['message'] = '恭喜您，得到“'.$data['award'].'”！' ;
				$result['status'] = 0;
			} else {
				$credit['description'] = $yqs['misscredit'];
				$credit['award'] = '未中奖励积分';
			}
		}
		!empty($credit['description']) && $result['message'] .= '<br />' . $credit['award'] . '：'. $credit['description'];
		$data['aid'] = $gift['id'];
		if (!empty($credit['description'])) {
			pdo_insert('yqs_winner', $credit);
		}
		pdo_insert('yqs_winner', $data);
		//$result['myaward'] = pdo_fetchall("SELECT award, description FROM ".tablename('yqs_winner')." WHERE from_user = '{$fromuser}' AND award <> '' AND rid = '$id' ORDER BY createtime DESC");
		message($result, '', 'ajax');
	}

	public function doMobileRegister() {
		global $_GPC, $_W;
		$title = '摇钱树领奖登记个人信息';
		if (!empty($_GPC['submit'])) {
			if (empty($_W['fans']['from_user'])) {
				//message('非法访问，请重新发送消息进入摇钱树页面！0');
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
