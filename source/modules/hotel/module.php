<?php
/*
 * 
 *
 *
 * 
 */
defined('IN_IA') or exit('Access Denied');

class HotelModule extends WeModule {

	public function fieldsFormDisplay($rid = 0) {

		global $_W;
		
		if (!empty($rid)) {
		
			$reply = pdo_fetch("SELECT * FROM ".tablename('hotel_reply')." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));				
 		} 
		
		include $this->template('form');
		
	}

	public function fieldsFormValidate($rid = 0) {

		return '';
		
	}

	public function fieldsFormSubmit($rid) {

		global $_GPC, $_W;
		
		$id = intval($_GPC['reply_id']);
		
		$insert = array(
		
			'rid' => $rid,
			
            'title' => $_GPC['title'],
			
			'shop' => $_GPC['shop'],
			
			'address' => $_GPC['address'],
			
			'phone' => $_GPC['phone'],
			
			'mail' => $_GPC['mail'],
			
			'ordermax' => $_GPC['ordermax'],
			
			'numsmax' => $_GPC['numsmax'],
			
			'daymax' => $_GPC['daymax'],
			
			'picture' => $_GPC['picture'],
			
			'description' => $_GPC['description'],
			
			'content' => htmlspecialchars_decode($_GPC['content']),
			
		);
		
		if (empty($id)) {
		
			pdo_insert('hotel_reply', $insert);
			
		} 
		else {
		
			if (!empty($_GPC['picture'])) {
			
				file_delete($_GPC['picture-old']);
				
			} 
			else {
			
				unset($insert['picture']);
				
			}

			pdo_update('hotel_reply', $insert, array('id' => $id));
			
		}

	}

	public function ruleDeleted($rid) {

		global $_W;
		
		$replies = pdo_fetchall("SELECT id, picture FROM ".tablename('hotel_reply')." WHERE rid = '$rid'");
		
		$deleteid = array();
		
		if (!empty($replies)) {
		
			foreach ($replies as $index => $row) {
			
				file_delete($row['picture']);
				
				$deleteid[] = $row['id'];
				
			}
			
		}
		
		pdo_delete('hotel_reply', "id IN ('".implode("','", $deleteid)."')");
		
		return true;
		
	}
	
	
	public function sendmail($cfghost,$cfgsecure,$cfgport,$cfgsendmail,$cfgsenduser,$cfgsendpwd,$mailaddress) {
		
		include 'class.phpmailer.php';
		
		$mail             = new PHPMailer();
		
		$mail->CharSet    = "utf-8";
		
		$body             = '邮箱测试';
		
		$mail->IsSMTP();
		
		$mail->SMTPAuth   = true;                  // enable SMTP authentication
		
		$mail->SMTPSecure = $cfgsecure;               // sets the prefix to the servier
		
		$mail->Host       = $cfghost;        // sets the SMTP server                 
		
		$mail->Port       = $cfgport; 
		
		$mail->Username   = $cfgsenduser;       // 发件邮箱用户名
		
		$mail->Password   = $cfgsendpwd;         // 发件邮箱密码
		
		$mail->From       = $cfgsendmail;	   //发件邮箱
		
		$mail->FromName   = "";  				   //发件人名称
		
		$mail->Subject    = "邮箱测试"; //主题
		
		$mail->WordWrap   = 50; // set word wrap
		
		$mail->MsgHTML($body);
		
		$mail->AddAddress($mailaddress,'');  //收件人地址、名称
		
		$mail->IsHTML(true); // send as HTML
		
		if(!$mail->Send()) {
		
		  $status = 0;
		
		} 
		
		else {
		
		  $status = 1;
		
		}
		
		return $status;

	}
	
	public function settingsDisplay($settings) {

		global $_GPC, $_W;

		if(checksubmit()) {
		
			if (empty($_GPC['sendmail'])||empty($_GPC['senduser'])||empty($_GPC['sendpwd'])) {

				message('请完整填写邮件配置信息', 'refresh', 'error');

			}
		
			if( $_GPC['host'] == 'smtp.qq.com' || $_GPC['host'] == 'smtp.gmail.com' ){
			
				$secure = 'ssl';
				
				$port = '465';
			
			}
			else{
			
				$secure = 'tls';
				
				$port = '25';
			
			}
			
			$result = $this->sendmail($_GPC['host'],$secure,$port,$_GPC['sendmail'],$_GPC['senduser'],$_GPC['sendpwd'],$_GPC['sendmail']);		

			$cfg = array(
				
				'host' => $_GPC['host'],
				
				'secure' => $secure,
				
				'port' => $port,
				
				'sendmail' => $_GPC['sendmail'],
				
				'senduser' => $_GPC['senduser'],
				
				'sendpwd' => $_GPC['sendpwd'],
				
				'status' => $result

			);
			

			if($result == 1){
			
				$this->saveSettings($cfg);
				
				message('邮箱配置成功', 'refresh');
			
			}
			else{
			
				message('邮箱配置信息有误', 'refresh', 'error');
			
			}
			
		}

		//if(!isset($settings['credit'])) {

		//	$settings['credit'] = '5';

		//}

		include $this->template('setting');

	}

}