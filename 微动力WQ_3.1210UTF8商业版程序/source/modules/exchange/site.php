<?php

/*
 * 积分兑换模块
 *
 * 作者 【艮随】
 * 
 */

defined('IN_IA') or exit('Access Denied');


class ExchangeModuleSite extends WeModuleSite {

	
	public function doWebDisplay(){
	
		global $_GPC, $_W;
		
		checklogin();
		
		$id = intval($_GPC['id']);
		
		if (checksubmit('delete')) {
		
			
			pdo_delete('exchange_record', " id IN ('".implode("','", $_GPC['select'])."')");
			
			message('删除成功！', $this->createWebUrl('display', array('id' => $id, 'page' => $_GPC['page'])));
		
		}

		$pindex = max(1, intval($_GPC['page']));
		
		$psize = 15;

		$exchangeinfo = pdo_fetchall('SELECT * FROM '.tablename('exchange_record').' WHERE rid= :rid ORDER BY `id` desc LIMIT ' . ($pindex - 1) * $psize . ',' . $psize, array(':rid' => $id ) );
		
		$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('exchange_record') . " WHERE weid = '{$_W['weid']}' AND rid = '$id' ");
		
		$pager = pagination($total, $pindex, $psize);
		
		include $this->template('display');
	
	}

	
	public function doMobileIndex() {

		global $_GPC, $_W;
		
		$from = $_W['fans']['from_user'];
		
		$rid = intval($_GPC['rid']);
		
		$weid = intval($_GPC['weid']);
		
		$date=date('Y-m-d');
		
		$date = strtotime($date);
		
		$now = time();
		
		$profile = fans_search($from);
		
		$sql = "SELECT * FROM " . tablename('exchange_reply') . " WHERE `rid`=:rid";

		$row = pdo_fetch($sql, array(':rid'=>$rid));

		$row['picture'] = $_W['attachurl'] . trim($row['picture'], '/');

		$title = $row['title'];
		
		//$newcredit = $profile['credit1'] - $row['price'];
		
		$numax = floor($profile['credit1']/$row['price']);
		
		$exchanged = pdo_fetchall("SELECT sum(nums) as enum FROM ".tablename('exchange_record')." WHERE rid = :rid ", array(':rid' => $rid ));
		
		$userexchangeinfo = pdo_fetchall("SELECT nums, cprice, time FROM ".tablename('exchange_record')." WHERE rid = :rid AND openid = :openid ", array(':rid' => $rid, ':openid' => $from ));
		
		$usertodayexchang = pdo_fetchall("SELECT * FROM " . tablename('exchange_record') . " WHERE rid = :rid AND openid = :openid AND `time` >= :date ", array(':rid' => $rid, ':openid' => $from, ':date' => $date));
		
		$usertodaynum = count($usertodayexchang);
		
		$allowexchange = $row['amount'] - $exchanged['0']['enum'];
		
		if($numax >= 1){
		
			for($i=1; $i<=$numax; $i++){
		
				$n = $i;
				
				$nn[] = $n;
			
			}
		
		}

		if (!empty($_GPC['submit'])) {
		
			if( $usertodaynum >= $row['times']){
			
				message('每天只能兑换'.$row['times'].'次哟~~', 'refresh', 'error');
			
			}
			
			if( $_GPC['nums'] <= $allowexchange ){
							
				$data = array(

					'realname' => $_GPC['realname'],
					
					'mobile' => $_GPC['mobile'],
					
					'credit1' => $profile['credit1'] - $_GPC['cprice'],

				);

				fans_update($from, $data);
				
				$insert = array(
				
					'weid' => $weid,
					
					'rid' => $rid,
					
					'openid' => $from,
					
					'name' => $_GPC['realname'],
					
					'mobile' => $_GPC['mobile'],
					
					'nums' => $_GPC['nums'],
					
					'cprice' => $_GPC['cprice'],
					
					'time' => $now,
				
				);
				
				if(pdo_insert('exchange_record', $insert)){
				
					$id = pdo_insertid();
				
				}
			
			}
			else{
			
				die('<script>location.href = "'.$this->createMobileUrl('error', array('rid' => $_GPC['rid'], 'id' => $id)).'";</script>');
			
			}
		
			die('<script>location.href = "'.$this->createMobileUrl('success', array('rid' => $_GPC['rid'], 'id' => $id)).'";</script>');

		}

		include $this->template('index');
		
	}


	public function doMobileSuccess() {
	
		global $_GPC, $_W;
		
		$from = $_W['fans']['from_user'];
		
		$rid = intval($_GPC['rid']);
		
		$id = intval($_GPC['id']);
		
		$weid = intval($_GPC['weid']);
		
		$sql = "SELECT * FROM " . tablename('exchange_reply') . " WHERE `rid`=:rid";

		$row = pdo_fetch($sql, array(':rid'=>$rid));
		
		$exchangeinfo = pdo_fetch("SELECT * FROM ".tablename('exchange_record')." WHERE id = :id ", array(':id' => $id ));
		
		$product = pdo_fetch("SELECT * FROM ".tablename('exchange_reply')." WHERE rid = :rid ", array(':rid' => $rid ));
		
		include $this->template('success');
	
	}


	public function doMobileerror() {

		include $this->template('error');
	
	}
}
