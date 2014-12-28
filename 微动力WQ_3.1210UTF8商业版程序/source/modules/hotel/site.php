<?php
/*
 * 
 *
 * 
 */
defined('IN_IA') or exit('Access Denied');

//ini_set('display_errors','on');
//error_reporting(E_ALL);

class HotelModuleSite extends WeModuleSite {	

	public function getProfileTiles() {
		
	}
	
	public function sendmail($cfghost,$cfgsecure,$cfgport,$cfgsendmail,$cfgsenduser,$cfgsendpwd,$body,$mailaddress) {
		
		include 'class.phpmailer.php';
		
		$mail             = new PHPMailer();
		
		$mail->CharSet    = "utf-8";
		
		$mail->IsSMTP();
		
		$mail->SMTPAuth   = true;                  // enable SMTP authentication
		
		$mail->SMTPSecure = $cfgsecure;               // sets the prefix to the servier
		
		$mail->Host       = $cfghost;        // sets the SMTP server                 
		
		$mail->Port       = $cfgport; 
		
		$mail->Username   = $cfgsenduser;       // 发件邮箱用户名
		
		$mail->Password   = $cfgsendpwd;         // 发件邮箱密码
		
		$mail->From       = $cfgsendmail;	   //发件邮箱
		
		$mail->FromName   = "";  				   //发件人名称
		
		$mail->Subject    = "预定通知"; //主题
		
		$mail->WordWrap   = 50; // set word wrap
		
		$mail->MsgHTML($body);
		
		$mail->AddAddress($mailaddress,'');  //收件人地址、名称
		
		$mail->IsHTML(true); // send as HTML
		
		$mail->Send();

	}
	

	public function doWebAddshop() {

		global $_GPC, $_W;

		$rid = intval($_GPC['rid']);
		
		$id = intval($_GPC['id']);

		if (!empty($id)) {

			$item = pdo_fetch("SELECT * FROM ".tablename('hotel_shop')." WHERE id = :id" , array(':id' => $id));

			if (empty($item)) {

				message('抱歉，房型不存在或是已经删除！', '', 'error');

			}

		}

		if (checksubmit('submit')) {

			if (empty($_GPC['style'])) {

				message('请输入房型！');

			}

			$data = array(

				'weid' => $_W['weid'],
				
				'rid' => $rid,

				'style' => $_GPC['style'],
				
				'oprice' => $_GPC['oprice'],
				
				'cprice' => $_GPC['cprice'],
				
				'thumb' => $_GPC['thumb'],

				'device' => htmlspecialchars_decode($_GPC['device']),

			);

			if (!empty($_FILES['thumb']['tmp_name'])) {

				file_delete($_GPC['thumb_old']);

				$upload = file_upload($_FILES['thumb']);

				if (is_error($upload)) {

					message($upload['message'], '', 'error');

				}

				$data['thumb'] = $upload['path'];

			}

			if (empty($id)) {

				pdo_insert('hotel_shop', $data);

			} else {

				pdo_update('hotel_shop', $data, array('id' => $id));

			}

			message('房型信息更新成功！', $this->createWebUrl('shop', array('id' => $_GPC['rid'])), 'success');

			

		}

		include $this->template('addshop');	

	}
	
	
	public function doWebShop(){
	
		global $_GPC, $_W;
		
		checklogin();
		
		$weid = $_W['account']['weid'];
		
		$rid = intval($_GPC['id']);
		
		$condition = '';
		
		if (!empty($_GPC['style'])) {
		
			$condition .= " AND style = '{$_GPC['style']}' ";
		
		}

		
		$pindex = max(1, intval($_GPC['page']));
		
		$psize = 20;

		$list = pdo_fetchall("SELECT * FROM ".tablename('hotel_shop')." WHERE weid = '{$_W['weid']}' AND rid = '$rid' $condition ORDER BY id DESC LIMIT ".($pindex - 1) * $psize.','.$psize);

		$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('hotel_shop') . " WHERE weid = '{$_W['weid']}' AND rid = '$rid' $condition");

		$pager = pagination($total, $pindex, $psize);
		
		include $this->template('shop');
	
	}
	
	
	public function doWebDeleteshop() {

		global $_GPC;

		$id = intval($_GPC['id']);

		$item = pdo_fetch("SELECT * FROM ".tablename('hotel_shop')." WHERE id = :id" , array(':id' => $id));

		if (empty($item)) {

			message('抱歉，房型不存在或是已经删除！', '', 'error');

		}

		if (!empty($item['thumb'])) {

			file_delete($item['thumb']);

		}

		pdo_delete('hotel_shop', array('id' => $item['id']));

		message('删除成功！', referer(), 'success');

	}
	
	
	public function doWebMngorder(){
	
		global $_GPC, $_W;
		
		checklogin();
		
		$weid = $_W['account']['weid'];
		
		$rid = intval($_GPC['id']);
		
		$condition = '';
		
		if (!empty($_GPC['realname'])) {
		
			$condition .= " AND name = '{$_GPC['realname']}' ";
		
		}
		
		if (!empty($_GPC['mobile'])) {
		
			$condition .= " AND mobile = '{$_GPC['mobile']}' ";
		
		}
		
		$pindex = max(1, intval($_GPC['page']));
		
		$psize = 20;

		$list = pdo_fetchall("SELECT * FROM ".tablename('hotel_order')." WHERE weid = '{$_W['weid']}' AND rid = '$rid' $condition ORDER BY id DESC LIMIT ".($pindex - 1) * $psize.','.$psize);

		$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('hotel_order') . " WHERE weid = '{$_W['weid']}' AND rid = '$rid' $condition");

		$pager = pagination($total, $pindex, $psize);
		
		include $this->template('mngorder');
	
	}
	
	
	
	public function doWebEditorder() {

		global $_GPC, $_W;

		$rid = intval($_GPC['rid']);
		
		$id = intval($_GPC['id']);

		if (!empty($id)) {

			$item = pdo_fetch("SELECT * FROM ".tablename('hotel_order')." WHERE id = :id" , array(':id' => $id));

			if (empty($item)) {

				message('抱歉，房型不存在或是已经删除！', '', 'error');

			}

		}

		if (checksubmit('submit')) {

			$data = array(

				'status' => $_GPC['status'],
				
				'msg' => $_GPC['msg'],
				
				'mngtime' => time(),

			);

			pdo_update('hotel_order', $data, array('id' => $id));

			message('订单信息处理完成！', $this->createWebUrl('mngorder', array('id' => $rid)), 'success');

		}

		include $this->template('editorder');	

	}
	
	
	public function doWebDeleteorder() {

		global $_GPC;

		$id = intval($_GPC['id']);
		
		$item = pdo_fetch("SELECT * FROM ".tablename('hotel_order')." WHERE id = :id" , array(':id' => $id));

		if (empty($item)) {

			message('抱歉，订单不存在或是已经删除！', '', 'error');

		}

		pdo_delete('hotel_order', array('id' => $id));

		message('删除成功！', referer(), 'success');

	}
	
	
	public function doMobileIndex() {

		global $_GPC,$_W;

		$rid = $_GPC['rid'];
		
		$weid = $_W['weid'];
		
		$fromuser = $_W['fans']['from_user'];
		
		$row = pdo_fetch("SELECT * FROM ".tablename('hotel_reply')." WHERE rid = :rid ", array(':rid' => $rid ));
		
		$row['picture'] = $_W['attachurl'] . trim($row['picture'], '/');
		
		$style = pdo_fetchall("SELECT * FROM ".tablename('hotel_shop')." WHERE rid = :rid ", array(':rid' => $rid ));
		
		$orderinfo = pdo_fetchall("SELECT * FROM ".tablename('hotel_order')." WHERE rid = :rid AND openid = :openid ORDER BY time DESC", array(':rid' => $rid, ':openid' => $fromuser ));			
	
		$ordernum = count($orderinfo);
		
		$profile = fans_search($fromuser);
		
		$cfgcredit = $this->module['config']['credit'];
		
		include $this->template('index');
    
	}
	
	
	public function doMobileOrder() {

		global $_GPC,$_W;
		
		$id = $_GPC['id'];

		$rid = $_GPC['rid'];
		
		$weid = $_W['weid'];
		
		$style = pdo_fetch("SELECT * FROM ".tablename('hotel_shop')." WHERE id = :id AND rid = :rid ", array(':id' => $id ,':rid' => $rid ));

		$style['thumb'] = $_W['attachurl'] . trim($style['thumb'], '/');
		
		$reply = pdo_fetch("SELECT * FROM ".tablename('hotel_reply')." WHERE rid = :rid ", array(':rid' => $rid ));
		
		$save = $style['oprice'] - $style['cprice'];

 		$fromuser = $_W['fans']['from_user'];
		
		$date=date('Y-m-d');
		
		$date = strtotime($date);
		
		$orderinfo = pdo_fetchall("SELECT * FROM ".tablename('hotel_order')." WHERE rid = :rid AND openid = :openid ORDER BY time DESC", array(':rid' => $rid, ':openid' => $fromuser ));
		
		$ordered = pdo_fetchall("SELECT * FROM ".tablename('hotel_order')." WHERE rid = :rid AND openid = :openid AND time >= :date ORDER BY time DESC", array(':rid' => $rid, ':openid' => $fromuser, ':date' => $date ));
		
		$ordernum = count($ordered);
		
		$profile = fans_search($fromuser);
	
		$cfghost = $this->module['config']['host'];
		
		$cfgsecure = $this->module['config']['secure'];
		
		$cfgport = $this->module['config']['port'];
		
		$cfgsendmail = $this->module['config']['sendmail'];
		
		$cfgsenduser = $this->module['config']['senduser'];
		
		$cfgsendpwd = $this->module['config']['sendpwd'];
		
		$cfgstatus = isset($this->module['config']['status']) ? $this->module['config']['status'] : 0;
		
		for($i=1; $i<=$reply['daymax']; $i++){
	
			$d=time()+86400*$i;
			
			$dd[] = $d;
		
		}
		
		for($i=1; $i<=$reply['numsmax']; $i++){
	
			$n = $i;
			
			$nn[] = $n;
		
		}
		
		if (!empty($_GPC['submit'])) {
		
			if( $ordernum >= $reply['ordermax'] ){
			
				message('抱歉，每位客户每日只能提交'.$reply['ordermax'].'个订单！', 'refresh', 'error');
			
			}
				
			$data = array(

				'realname' => $_GPC['realname'],
			
				'mobile' => $_GPC['mobile'],

			);
			
			fans_update($fromuser, $data);
				
			$insert = array(
				
				'weid' => $weid,
				
				'rid' => $rid,
				
				'sid' => $id,
				
				'openid' => $fromuser,
				
				'shop' => $reply['shop'],
				
				'name' => $_GPC['realname'],
				
				'mobile' => $_GPC['mobile'],
				
				'btime' => $_GPC['btime'],
				
				'etime' => $_GPC['etime'],
				
				'style' => $_GPC['style'],
				
				'nums' => $_GPC['nums'],
				
				'oprice' => $_GPC['oprice'],
				
				'cprice' => $_GPC['cprice'],
				
				'info' => $_GPC['info'],
				
				'time' => time(),
				
			);
			
			$body = '预定人：'.$_GPC['realname'].'<br/><br/>预定时间：'.date('Y-m-d', $_GPC['btime']).'至'.date('Y-m-d', $_GPC['etime']).'<br/><br/>预定房型：'.$_GPC['style'].'<br/><br/>预定数量：'.$_GPC['nums'].'间<br/><br/>总价：'.$_GPC['cprice'].'元<br/><br/>联系电话：'.$_GPC['mobile'].'<br/><br/>备注：'.$_GPC['info'];
				
			pdo_insert('hotel_order', $insert);
			
			if( $cfgstatus == 1 ){
			
				$result = $this->sendmail($cfghost,$cfgsecure,$cfgport,$cfgsendmail,$cfgsenduser,$cfgsendpwd,$body,$reply['mail']);

			}

			die('<script>location.href = "'.$this->createMobileUrl('record', array('rid' => $_GPC['rid'])).'";</script>');

		}
		
		include $this->template('order');
    
	}


	public function doMobileRecord() {
	
		global $_GPC, $_W;
		
		$weid = $_W['account']['weid'];
		
		$rid = intval($_GPC['rid']);
		
		$fromuser = $_W['fans']['from_user'];
		
		$orderinfo = pdo_fetchall("SELECT * FROM ".tablename('hotel_order')." WHERE rid = :rid AND openid = :openid ORDER BY time DESC", array(':rid' => $rid, ':openid' => $fromuser ));			
	
		$ordernum = count($orderinfo);
		
		$pic = pdo_fetch("SELECT * FROM ".tablename('hotel_reply')." WHERE rid = :rid ", array(':rid' => $rid ));
		
		$pic['picture'] = $_W['attachurl'].$pic['picture'];
		
		if (!empty($rid)) {
		
			$reply = pdo_fetchall("SELECT * FROM ".tablename('hotel_order')." WHERE rid = :rid AND openid = :openid ORDER BY time DESC", array(':rid' => $rid, ':openid' => $fromuser ));			
 		
			$ordernum = count($reply);
		}
		
		include $this->template('record');

	}
	
	
	public function doMobileOrderdetail() {
	
		global $_GPC, $_W;
		
		$weid = $_W['account']['weid'];
		
		$rid = intval($_GPC['rid']);
		
		$id = intval($_GPC['id']);
		
		$fromuser = $_W['fans']['from_user'];
		
		$orderinfo = pdo_fetchall("SELECT * FROM ".tablename('hotel_order')." WHERE rid = :rid AND openid = :openid ORDER BY time DESC", array(':rid' => $rid, ':openid' => $fromuser ));			
	
		$ordernum = count($orderinfo);
		
		$profile = fans_search($fromuser);
		
		$detail = pdo_fetch("SELECT * FROM ".tablename('hotel_order')." WHERE rid = :rid AND id = :id ", array(':rid' => $rid, ':id' => $id ));
		
		$shopinfo = pdo_fetch("SELECT * FROM ".tablename('hotel_shop')." WHERE rid = :rid AND id = :id ", array(':rid' => $rid ,':id' => $detail['sid'] ));
		
		$shopinfo['thumb'] = $_W['attachurl'].$shopinfo['thumb'];
		
		$save = $shopinfo['oprice'] - $shopinfo['cprice'];
		
		$reply = pdo_fetch("SELECT * FROM ".tablename('hotel_reply')." WHERE rid = :rid ", array(':rid' => $rid ));
		
		for($i=1; $i<=$reply['daymax']; $i++){
	
			$d=time()+86400*$i;
			
			$dd[] = $d;
		
		}
		
		for($i=1; $i<=$reply['numsmax']; $i++){
	
			$n = $i;
			
			$nn[] = $n;
		
		}
		
		if (!empty($_GPC['submit'])) {
				
			$data = array(

				'realname' => $_GPC['realname'],
			
				'mobile' => $_GPC['mobile'],

			);
			
			fans_update($fromuser, $data);
				
			$insert = array(
				
				'name' => $_GPC['realname'],
				
				'mobile' => $_GPC['mobile'],
				
				'btime' => $_GPC['btime'],
				
				'etime' => $_GPC['etime'],
				
				'style' => $_GPC['style'],
				
				'nums' => $_GPC['nums'],
				
				'oprice' => $_GPC['oprice'],
				
				'cprice' => $_GPC['cprice'],
				
				'info' => $_GPC['info'],
				
				'time' => time(),

			);
				
			pdo_update('hotel_order', $insert, array('id' => $id));

			
			die('<script>location.href = "'.$this->createMobileUrl('record', array('rid' => $_GPC['rid'] )).'";</script>');

		}
		
		if (!empty($_GPC['delete'])) {
				
			pdo_delete('hotel_order', array('id' => $id));
	
			die('<script>location.href = "'.$this->createMobileUrl('record', array('rid' => $_GPC['rid'] )).'";</script>');

		}

		include $this->template('orderdetail');

	}
	
	public function doMobileOrderinfo() {
	
		global $_GPC, $_W;
		
		$weid = $_W['account']['weid'];
		
		$rid = intval($_GPC['rid']);
		
		$id = intval($_GPC['id']);
		
		$fromuser = $_W['fans']['from_user'];
		
		$orderinfo = pdo_fetchall("SELECT * FROM ".tablename('hotel_order')." WHERE rid = :rid AND openid = :openid ORDER BY time DESC", array(':rid' => $rid, ':openid' => $fromuser ));			
	
		$ordernum = count($orderinfo);
		
		$profile = fans_search($fromuser);
		
		$detail = pdo_fetch("SELECT * FROM ".tablename('hotel_order')." WHERE rid = :rid AND id = :id ", array(':rid' => $rid, ':id' => $id ));
		
		$pic = pdo_fetch("SELECT * FROM ".tablename('hotel_shop')." WHERE rid = :rid AND id = :id ", array(':rid' => $rid ,':id' => $detail['sid'] ));
		
		$pic['thumb'] = $_W['attachurl'].$pic['thumb'];
		
		$save = $detail['oprice'] - $detail['cprice'];
		
		if (!empty($_GPC['delete'])) {
				
			pdo_delete('hotel_order', array('id' => $id));
	
			die('<script>location.href = "'.$this->createMobileUrl('record', array('rid' => $_GPC['rid'] )).'";</script>');

		}
		
		include $this->template('orderinfo');

	}
	
	
	public function doMobileAbout() {
	
		global $_GPC, $_W;
		
		$weid = $_W['account']['weid'];
		
		$rid = intval($_GPC['rid']);
		
		$id = intval($_GPC['id']);
		
		$fromuser = $_W['fans']['from_user'];
		
		$orderinfo = pdo_fetchall("SELECT * FROM ".tablename('hotel_order')." WHERE rid = :rid AND openid = :openid ORDER BY time DESC", array(':rid' => $rid, ':openid' => $fromuser ));			
	
		$ordernum = count($orderinfo);
		
		$profile = fans_search($fromuser);
		
		$detail = pdo_fetch("SELECT * FROM ".tablename('hotel_reply')." WHERE rid = :rid ", array(':rid' => $rid ));

		$detail['picture'] = $_W['attachurl'].$detail['picture'];
		
		include $this->template('about');

	}
	
}