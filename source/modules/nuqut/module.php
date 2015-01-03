<?php
/**
 * 微擎统计中心模块定义
 *
 * @author We7 Team
 * @url http://www.we7.cc
 */
defined('IN_IA') or exit('Access Denied');

class NuqutModule extends WeModule {

	public function settingsDisplay($settings) {
		global $_W, $_GPC;
		if(checksubmit()) {
			$cfg = array();
			$cfg['msg_history'] = $_GPC['msg_history'] == '1';
			$cfg['msg_maxday'] = intval($_GPC['msg_maxday']);
			$cfg['use_ratio'] = $_GPC['use_ratio'] == '1';
			if($this->saveSettings($cfg)) {
				message('保存成功', 'refresh');
			}
		}
		if(!isset($settings['msg_history'])) {
			$settings['msg_history'] = '1';
		}
		if(!isset($settings['msg_maxday'])) {
			$settings['msg_maxday'] = '0';
		}
		if(!isset($settings['use_ratio'])) {
			$settings['use_ratio'] = '1';
		}
		include $this->template('setting');
	}
	public function doHistory() {
		global $_W, $_GPC;

		$paylog=pdo_fetchall("SELECT * FROM ".tablename('members_paylog')." WHERE uid = '{$_W['uid']}' order by id desc");
		include $this->template('history');
	}

	public function doKartam() {
		global $_W, $_GPC;





				$member=pdo_fetch("SELECT m.*,n.stattime,n.endtime FROM ".tablename('members')." as m left join ".tablename('members_status')." as n  ON m.uid=n.uid  WHERE m.uid = '{$_W['uid']}' LIMIT 1");
	$accountnum =pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('wechats')." WHERE uid =  '{$_W['uid']}'");
		include $this->template('kartam');
	}
		public function doXufei() {
		global $_W, $_GPC;


if (checksubmit('submit')) {
$wedata=pdo_fetch("SELECT * FROM ".tablename('members_status')." WHERE uid = '{$_W['uid']}' LIMIT 1");
	$data = array();

			$vip=intval($_GPC['vip']);
			$period=intval($_GPC['period']);
			
			$vipprice=pdo_fetchcolumn("SELECT price FROM ".tablename('members_group')." WHERE id =  '{$vip}'");
			$vprice=number_format(($vipprice/30), 2, '.', '');
			$shouldpay=$vprice*$period;
			$member=pdo_fetch("SELECT * FROM ".tablename('members')." WHERE uid = '{$_W['uid']}' LIMIT 1");
		$groupprice=pdo_fetchcolumn("SELECT price FROM ".tablename('members_group')." WHERE id =  '{$member['groupid']}'");
			
						//原来折合多少金币
			$diffmoney=intval((($wedata['endtime']-time())/86400)*($groupprice/30));
			$lasttime=time()+$period*86400;
			if ($diffmoney>0){
			$money=intval($shouldpay-$diffmoney);
			}else{
				$money=intval($shouldpay);
			}
			
	
										if( $member['money']<$money){
											message('金额不足', create_url('site/module', array('do'=>'kartam','name'=>'nuqut')), 'error');
			
			}
							if($member['money']>0 && $member['money']>$money && $vip>0 && $period>=30){
								if($money>0)
						{
												$date['money']=abs($money);
						$date['usemoney']=abs($money);
						$sql = 'UPDATE ' . tablename('members') . " SET `money`=`money`-{$date['money']} WHERE  `uid`=:uid";
						$usesql = 'UPDATE ' . tablename('members') . " SET `usemoney`=`usemoney`+{$date['usemoney']} WHERE  `uid`=:uid";
												$vipsql = 'UPDATE ' . tablename('members') . " SET `groupid`='{$vip}' WHERE  `uid`=:uid";
			$pars = array();
			$pars[':uid'] = $_W['uid'];
			pdo_query($sql, $pars);
			pdo_query($usesql, $pars);
			pdo_query($vipsql, $pars);
								}
						else
						{
						$date['money']=abs($money);
						$date['usemoney']=abs($money);
						$sql = 'UPDATE ' . tablename('members') . " SET `money`=`money`+{$date['money']} WHERE  `uid`=:uid";
						$usesql = 'UPDATE ' . tablename('members') . " SET `usemoney`=`usemoney`-{$date['usemoney']} WHERE  `uid`=:uid";
									$vipsql = 'UPDATE ' . tablename('members') . " SET `groupid`='{$vip}' WHERE  `uid`=:uid";
			$pars = array();
			$pars[':uid'] = $_W['uid'];
			pdo_query($sql, $pars);
			pdo_query($usesql, $pars);
			pdo_query($vipsql, $pars);
						}
									if (!empty($wedata)) {
				$data['gid'] = intval($_GPC['vip']);
				$data['stattime'] =time();
				$data['endtime'] = $lasttime;
				pdo_update('members_status', $data, array('id' => $wedata['id']));
		} else {
			$data['gid'] = intval($_GPC['vip']);
				$data['stattime'] =time();
				$data['endtime'] = $lasttime;
			$data['uid'] = $_W['uid'];
			pdo_insert('members_status', $data);
		}
						
			
			}else{
									message('金额不足', create_url('site/module', array('do'=>'kartam','name'=>'nuqut')), 'error');
			}
			
											pdo_insert('members_paylog', array(
							'uid' => $_W['uid'],
							'money' => 0,
							'type' => 3,
							'msg' => "由".$member['username']."开通服务 ,等级：".$vip." 开始日期：".date('Y-m-d  h:i:s', time()) ."，到期时间".date('Y-m-d  h:i:s', $lasttime),
							'paytime' => TIMESTAMP,
						));



message('续费成功！', create_url('site/module', array('do'=>'kartam','name'=>'nuqut')), 'success');


}
$wechat = array();

$group=pdo_fetchall("SELECT * FROM ".tablename('members_group')."");
$list=pdo_fetch("SELECT * FROM ".tablename('members_status')." WHERE uid = '{$_W['uid']}' LIMIT 1");
$money=pdo_fetch("SELECT * FROM ".tablename('members')." WHERE uid = '{$_W['uid']}' LIMIT 1");
$vipname=pdo_fetchcolumn("SELECT name FROM ".tablename('members_group')." WHERE id =  '{$money['groupid']}'");
		include $this->template('xufei');
		
	}
			public function doMoney() {
		global $_W, $_GPC;
		$money=pdo_fetch("SELECT * FROM ".tablename('members')." WHERE uid = '{$_W['uid']}' LIMIT 1");
		include $this->template('money');
	}
}
