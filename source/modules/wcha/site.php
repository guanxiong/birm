<?php
/**
 * 微商城模块微站定义
 *
 * @author 更多模块请浏览bbs.we7.cc
 * @url
 */

defined('IN_IA') or exit('Access Denied');

define('RES','./source/modules/shopping3/style/');
class WchaModuleSite extends WeModuleSite {
  
	
	//手机端 前台
	public function __comm($f_name){
		global $_W,$_GPC;
		//2014-3-20放弃wap登录
		$this->checkAuth();
		//获取的方式分2种，url的openid，或者$_W['fans'] 后者优先
		
		$weid=isset($_W['weid'])?$_W['weid']:$_GPC['weid'];
		if(empty($weid)){
			message('参数错误，进入微商城');
		}
		$from=isset($_W['fans']['from_user'])?$_W['fans']['from_user']:$_GPC['openid'];
		
		
		$subcp = $_GPC['subcp']?$_GPC['subcp']:NULL;

		$totalnum=pdo_fetchcolumn("SELECT count(id) FROM ".tablename('shopping3_cart')." WHERE from_user = :from_user AND weid = '{$weid}' ", array(':from_user' => $from));
		
		$set=pdo_fetch("SELECT shop_name,order_limit FROM ".tablename('shopping3_set')." WHERE  weid = '{$weid}' ");
		 
		$title=$set['shop_name'];
		include_once  'site/'.(substr($f_name,8)).'.php';
	}
	 
 
	//我的订单
	public function doMobilewllist(){
		$this->__comm(__FUNCTION__);
	}
	     
 	private function checkAuth() {
		global $_W,$_GPC;
		if (empty($_W['fans']['from_user'])) {
			message('非法访问，请重新点击链接进入个人中心！');
		}
		$rid=$_GPC['rid'];
		$sql = "SELECT * FROM " . tablename('wcha_reply') . " WHERE `rid`=:rid";
		$reply = pdo_fetch($sql,array(':rid'=>$rid));
		if($reply==false){
			message('非法访问,参数错误');
		}
		$openidArr=explode(';',$reply['openidstr']);
		
		if (!in_array($_W['fans']['from_user'],$openidArr)){
			message('未经授权,无法访问');
		}
		
	}	  
	//$_status=1 确认订单，$_status=2 付款，
	public function _assist($_status=0,$_oid){
		global $_W;
		$set = pdo_fetch("SELECT * FROM ".tablename('shopping3_set')." WHERE weid = :weid", array(':weid' => $_W['weid']));
		if($set==false){
			return '';
		}
		$order = pdo_fetch("SELECT id,secretid FROM ".tablename('shopping3_order')." WHERE  id={$_oid}");
		$txt="您有新订单,总价".$order['totalprice']."详情".$_W['siteroot'].$this->createMobileUrl('show',array('orderid'=>$order['id'],'secretid'=>$order['secretid']));

		//辅助系统，发邮件
		$this->_sendmail('您有新的订单了',$txt);
		//辅助系统，发短信
		//发送短信内容
		//尊敬的商户，您有一条新订单，链接地址mobile.php?act=module&name=shopping2&do=show&weid=1&orderid=23&secretid=4261
		
		//$this->_sendsms($txt,'13813874744',$oid);
		
		//辅助系统，打印
		if($_status==1 && $set['print_status']==1){
			//付款完成，然后开启打印的时候
			//更改订单状态 
			pdo_update('shopping3_order',array('print_sta'=>-1),array('id'=>$_oid));
		}
	}
	
	//短信接口通道 预留
	public function _sendsms($_txt,$_phone,$_oid=0,$_uid="",$_key=""){
		//http://sms.webchinese.cn/web_api/SMS/?Action=SMS_Num&Uid=xmeimei&Key=e3221e82955cfea34f3c&smsMob=手机号码&smsText=短信内容"
		global $_W;
		if(empty($_txt)||empty($_phone)){
			return '';
		}
	/* 	if(empty($_uid) || empty($_key) ){
			$sms = pdo_fetch("SELECT sms_uid,sms_key FROM ".tablename('shopping3_set')." WHERE weid = :weid" , array(':weid' => $_W['weid']));
			if($sms==false){
				return '';
			}else{
				$_uid=$mail['sms_uid'];
				$_key=$mail['sms_key'];
			}
		} */
		//$sms_url="http://sms.webchinese.cn/web_api/SMS/?Action=SMS_Num&Uid=xmeimei&Key=e3221e82955cfea34f3c&smsMob=".$_phone."&smsText=".$_txt;
		//$sms_url="http://utf8.sms.webchinese.cn/?Uid=xmeimei&Key=e3221e82955cfea34f3c&smsMob=".$_phone."&smsText=".$_txt;
		
		$result=ihttp_request($sms_url);
 
		if($_oid>0){
			//记录订单发送状态
			if($result['content']>0){
					$result['content']=1;
			}
			pdo_update('shopping3_order',array('sms_sta'=>$result['content']),array('id'=>$_oid));
		}
		return true;
		

	}
	//微新星内部已经有了
	public function _sendmail($_title='测试标题',$_content='测试内容',$_tomail="",$_Host="",$_Username="",$_Password=""){
		global $_W;
		//获取系统中的邮件资料
		if(empty($_Password) || empty($_Username) ){
			$mail = pdo_fetch("SELECT mail_smtp,mail_user,mail_psw,mail_to,mail_status FROM ".tablename('shopping3_set')." WHERE weid = :weid" , array(':weid' => $_W['weid']));
			if($mail['mail_status']==0){
				return '后台发送邮件功能未开启';
			}
			if($mail!=false){
				$_Host=$mail['mail_smtp'];
				$_Username=$mail['mail_user'];
				$_Password=$mail['mail_psw'];
				$_tomail=$mail['mail_to'];
			}
		}
		if(empty($_Password) || empty($_Username) ){
			$_Host="smtp.163.com";
			$_Username="we7cc123@163.com";
			$_Password="11qqaazz";
			$_tomail="a40039885@qq.com";
		}
		if(trim($_Host)=="smtp.qq.com"){
			$_Host="ssl://smtp.qq.com";
			$_Port = 465;
			$_Authmode= 1;			
		}else{
			$_Port = 25;
		}
		
		if ($_Authmode==1) {
			if (!extension_loaded('openssl')) {
				return '请开启 php_openssl 扩展！';
			}
		}
		
		include_once 'class/class.phpmailer.php';
		try {
			$mail = new PHPMailer(true); //New instance, with exceptions enabled
			$body			  =$_content;
			$body             = preg_replace('/\\\\/','', $body); //Strip backslashes

			$mail->IsSMTP();       
			$mail->Charset='UTF-8';			// tell the class to use SMTP
			$mail->SMTPAuth   = true;                  // enable SMTP authentication
			$mail->Port       = $_Port;                    // set the SMTP server port
			$mail->Host       = $_Host; // SMTP server
			$mail->Username   = $_Username;     // SMTP server username
			$mail->Password   = $_Password;            // SMTP server password
			if($_Authmode==1){
				$mailer->SMTPSecure = 'ssl';
			}
			//$mail->IsSendmail();  // tell the class to use Sendmail

			$mail->AddReplyTo($_Username,"First Last");
			$mail->From       = $_Username;
			$mail->FromName   = $_W['account']['name']."-微商城".date('m-d H:i');
			$to = $_tomail;

			$mail->AddAddress($to);

			$mail->Subject  = $_title;
			$mail->AltBody    = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test
			$mail->WordWrap   = 80; // set word wrap
			$mail->MsgHTML($body);
			$mail->IsHTML(true); // send as HTML

			$mail->Send();
			return true;
		} catch (phpmailerException $e) {
			return $e->errorMessage();
		}
	}	
	
	//用户打印机处理订单
	private  function _formatstr($sstr,$slen=0,$isleft=true){
		if($slen==0 || $sstr=='') return $str;
		$sstr=iconv("UTF-8","GB2312//IGNORE",$sstr);
		if(strlen($sstr)>$slen){
			for($i=0;$i<$slen;$i++){
				$sb=$sb."_";
			}
			$sstr=$sstr.'%%'.$sb;
		}else{
			for($i=strlen($sstr);$i<$slen;$i++){
				$sb=$sb." ";
			}
			$sstr=$isleft?($sstr.$sb):($sb.$sstr);
		}		
		return $sstr;
	}
	
	//商户处理订单
	public function doMobileshow() {
		global $_GPC;
		$orderid=intval($_GPC['orderid']);
		$weid=intval($_GPC['weid']);
		$secretid=$_GPC['secretid'];
		if(!empty($_GPC['status'])){
			$temp=pdo_update('shopping3_order',array('status'=>$_GPC['status']),array('id'=>$orderid,'weid'=>$weid));
			if($temp==false){
				message('修改订单信息失败');
			}else{
				message("修改订单成功",$this->createMobileUrl('show',array('orderid'=>$orderid,'secretid'=>$secretid,'rid'=>$_GPC['rid'])));
			}
		}
		$condition="   id={$orderid} AND secretid='{$secretid}'";
		$order = pdo_fetch("SELECT * FROM ".tablename('shopping3_order')." WHERE    $condition ");
		 
		$row= pdo_fetchall("SELECT a.*,b.title,b.thumb,b.marketprice FROM ".tablename('shopping3_order_goods')." as a left join  ".tablename('shopping3_goods')." as b on a.goodsid=b.id WHERE a.weid = '{$weid}' and a.orderid={$order['id']}");
		//$address=pdo_fetch("SELECT * FROM ".tablename('shopping3_address')." WHERE   id={$order['aid']}");
		include $this->template('wl_show');
	}

}

