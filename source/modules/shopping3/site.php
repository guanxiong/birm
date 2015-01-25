<?php
/**
 * 微商城模块微站定义
 *
 * @author 更多模块请浏览bbs.b2ctui.com
 * @url
 */

defined('IN_IA') or exit('Access Denied');

define('RES','./source/modules/shopping3/style/');
class Shopping3ModuleSite extends WeModuleSite {
	public function __construct(){
		
    }
	
	function ext_template_manifest($modulename,$tpl) {
	$manifest = array();
	$filename = IA_ROOT . "/source/modules/{$modulename}/template/mobile/" . $tpl . '/manifest.xml';

   
	if (!file_exists($filename)) {
		return array();
	}
	$xml = str_replace(array('&'), array('&amp;'), file_get_contents($filename));
	$xml = @simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
	if (empty($xml)) {
		return array();
	}
	$manifest['name'] = strval($xml->identifie);
	if(empty($manifest['name']) || $manifest['name'] != $tpl) {
		return array();
	}
	$manifest['title'] = strval($xml->title);
	if(empty($manifest['title'])) {
		return array();
	}
	$manifest['description'] = strval($xml->description);
	$manifest['author'] = strval($xml->author);
	$manifest['url'] = strval($xml->url);
	
	if($xml->settings->item) {
		foreach($xml->settings->item as $msg) {
			$attrs = $msg->attributes();
			$manifest['settings'][trim(strval($attrs['variable']))] = trim(strval($attrs['content']));
		}
	}
	return $manifest;
}

    public function doWebstyle() {
      global $_GPC, $_W;
      $setting =$this->module['config'];
      $setting["template"]=trim($_GPC["template"]);
      $module=WeUtility::createModule($this->module['name']);
      $module->saveTemplate($setting);

	}

	public function doWebTemplate() {
	 	global $_GPC, $_W;
	    $modulename=$this->module['name'];
	    $template=$this->module['config']['template'];

        $path = IA_ROOT . "/source/modules/{$modulename}/template/mobile/";

	    if (is_dir($path)) {
		  if ($handle = opendir($path)) {
	      while (false !== ($modulepath = readdir($handle))) {
	      	if ($modulepath=="." || $modulepath=="..")
	      	  continue;
			$manifest = $this->ext_template_manifest($modulename,$modulepath);
            if(!empty($manifest)) {
			      if  ($manifest['name']== $template){
                     $manifest['on']=1;
                   }
					$templateids[] = $manifest;
				
				}
			}
		  }
	    }
	  include $this->template('template');
	}

		protected function template($filename, $flag = TEMPLATE_INCLUDEPATH) {
		global $_W,$_GPC;
		$mn = strtolower($this->module['name']);
		$template=empty($this->module['config']['template'])?"default":$this->module['config']['template'];

		if (!empty($_GPC['template'])){
            $template=trim($_GPC['template']);
		}


		
		if($this->inMobile) {
			$source = IA_ROOT . "/source/modules/{$mn}/template/mobile/{$template}/{$filename}.html";
			$compile = "{$_W['template']['compile']}/mobile/modules/{$mn}/{$template}/{$filename}.tpl.php";
           if(!is_file($source)) {
			  exit("Error222: template source '{$filename}' is not exist!");
		     }

		    if (DEVELOPMENT || !is_file($compile) || filemtime($source) > filemtime($compile)) {
			  template_compile($source, $compile, true);
		    }
		    return $compile;
		
		} else {
		  return parent::template($filename); 
		}
    }

    public function doWebPreview(){
    	//'from_user' => base64_encode(authcode("fromuser", 'ENCODE'))
       $url=create_url('mobile/module', array('name'=>'shopping3',
                        'do'=>'wlhome','weid'=>$GLOBALS['_W']['weid']));
       $processor=WeUtility::createModuleProcessor($this->module['name']);
       $url=$processor->index();
       header('Location: '.$url);

	}
	
	//后台程序 web文件夹下
	public function __web($f_name){
		global $_W,$_GPC;
		checklogin();
		$weid=$_W['weid'];
		//每个页面都要用的公共信息.后期考虑用缓存2014-2-7
		include_once  'web/wl'.strtolower(substr($f_name,5)).'.php';
	}
	//后台-分类管理
	public function doWebCategory() {
 		$this->__web(__FUNCTION__);
	}
	//后台-商品管理
	public function doWebGoods() {
		$this->__web(__FUNCTION__);
	}
 	//后台-订单管理
	public function doWebOrder() {
		$this->__web(__FUNCTION__);
	}

 	//后台-基本设置
	public function doWebShopset(){
		$this->__web(__FUNCTION__);
	}
 	//后台-邮件功能设置
	public function doWebmailset(){
		$this->__web(__FUNCTION__);
	}	
 	//后台-打印机功能设置
	public function doWebprintset(){
		$this->__web(__FUNCTION__);
	}		
 	//后台-短信参数功能设置
	public function doWebsmsset(){
		$this->__web(__FUNCTION__);
	}	 
	//后台，智能选餐
	public function doWebGenius(){
		$this->__web(__FUNCTION__);
	}	
	//后台，导出Excel
	public function doWebdownload(){
		$this->__web(__FUNCTION__);
	}	 
	public function doWeborderset(){
		$this->__web(__FUNCTION__);
	}	 	
	
	//接打印机，不需要登录认证
	public function doWebPrint(){
		global $_W,$_GPC;
 		$weid=$_W['weid'];
		include_once  'web/wlprint.php';
	}
 
	
	//手机端 前台
	public function __comm($f_name){
		global $_W,$_GPC;
		//2014-3-20放弃wap登录
		$this->checkAuth();
		//获取的方式分2种，url的openid，或者$_W['fans'] 后者优先
		
		$weid=isset($_W['weid'])?$_W['weid']:$_GPC['weid'];
		if(empty($weid)){
			message('参数错误，进入微餐饮');
		}
		$from=isset($_W['fans']['from_user'])?$_W['fans']['from_user']:$_GPC['openid'];
		
		
		$subcp = $_GPC['subcp']?$_GPC['subcp']:NULL;

		$totalnum=pdo_fetchcolumn("SELECT count(id) FROM ".tablename('shopping3_cart')." WHERE from_user = :from_user AND weid = '{$weid}' ", array(':from_user' => $from));
		
		$set=pdo_fetch("SELECT shop_name,order_limit FROM ".tablename('shopping3_set')." WHERE  weid = '{$weid}' ");
		 
		$title=$set['shop_name'];
		include_once  'site/'.strtolower(substr($f_name,8)).'.php';
	}
	 
	//获取菜单
	public function doMobilewlhome(){	
		$this->__comm(__FUNCTION__);
	}
	//获取菜单
	public function doMobilewldishlist(){	
		$this->__comm(__FUNCTION__);
	}
	//获取菜单
	public function doMobilewladdorder(){	
		$this->__comm(__FUNCTION__);
	}
	//获取菜单
	public function doMobilewllike(){	
		$this->__comm(__FUNCTION__);
	}	
	//获取菜单
	public function doMobilewlmylike(){	
		$this->__comm(__FUNCTION__);
	}		
	//商城首页
	public function doMobilewlindex(){	
		$this->__comm(__FUNCTION__);
	}
	//商品列表
	public function doMobilewllist(){
		$this->__comm(__FUNCTION__);
	}
 
	//商品列表
	public function doMobilewldetail() {
		$this->__comm(__FUNCTION__);
	}
	//商品详情
	public function doMobilewldescription() {
		$this->__comm(__FUNCTION__);
	}
			
	//购物车
	public function doMobilewlcart(){
		$this->__comm(__FUNCTION__);
	}
	//提交订单
	public function doMobilewlorder(){
		$this->__comm(__FUNCTION__);	
	}
	//我的订单
	public function doMobilewlmember(){
		$this->__comm(__FUNCTION__);
	}
	//注册
	public function doMobilewllogin(){
		$this->__comm(__FUNCTION__);
	}
	//注册
	public function doMobilewlregister(){
		$this->__comm(__FUNCTION__);
	}	
	public function doMobilewlupdatecart() {
		$this->__comm(__FUNCTION__);
	} 
	//智能选餐
	public function doMobilewlgenius() {
		$this->__comm(__FUNCTION__);
	} 	
	
	public function doMobilewlpayment(){
		global $_W, $_GPC;
		$this->checkAuth();
		$orderid = intval($_GPC['orderid']);
		//考虑看库存
		$temp=$this->_checkstock($orderid);
		if($temp==false){
			message('订单中某些产品库存不足，订单已取消，请联系客服。', $this->createMobileUrl('wlmember',array('weid'=>$_GET['weid'])), 'error');
		}
		//更新付款方式
	
		
		$order = pdo_fetch("SELECT * FROM ".tablename('shopping3_order')." WHERE id = :id", array(':id' => $orderid));
		if ($order['status'] != '0') {
			message('抱歉，您的订单已经付款或是被关闭，请重新进入付款！', $this->createMobileUrl('wlorder'), 'error');
		}
		if($_GPC['paytype']==3){
			pdo_update('shopping3_order', array('paytype' => $_GPC['paytype'],'status'=>1), array('id' => $orderid));
				//计算库存
			$this->_inventory($orderid);
			$this->_assist(1,$orderid);
			//选择现金支付，跳转到会员页面
			message('您选择现金支付，您的订单我们正在处理中！', $this->createMobileUrl('wlmember'));
		}else{
			pdo_update('shopping3_order', array('paytype' => $_GPC['paytype']), array('id' => $orderid));
		}

		if (checksubmit('paytype1')) {
			if ($order['paytype'] != 1) {
				message('抱歉，您的支付方式不正确，请重新提交订单！', $this->createMobileUrl('wlorder'), 'error');
			}
			if ($_W['fans']['credit2'] < $order['totalprice']) {
				message('抱歉，您帐户的余额不够支付该订单，请充值！', create_url('mobile/module/charge', array('name' => 'member', 'weid' => $_W['weid'])), 'error');
			}
			if (pdo_update('card_members', array('credit2' => $profile['credit2'] - $order['totalprice']), array('from_user' => $_W['fans']['from_user']))) {
				pdo_update('shopping3_order', array('status' => 2), array('id' => $orderid));
				message('余额付款成功！', $this->createMobileUrl('wlorder'), 'success');
			} else {
				message('余额付款失败，请重试！', $this->createMobileUrl('wlorder'), 'error');
			}
		}
		if (checksubmit()) {
 			$params['tid'] = $orderid;
			$params['user'] = $_W['fans']['from_user'];
			$params['fee'] = $order['totalprice'];
			$params['ordersn'] = $order['ordersn'];
			$params['title'] = $_W['account']['name'];
			$this->pay($params);
		}
		include $this->template('pay');
	}
	 


 

	public function doMobilePay() {
		global $_W, $_GPC;
		$this->checkAuth();
		$orderid = intval($_GPC['orderid']);
		$order = pdo_fetch("SELECT * FROM ".tablename('shopping3_order')." WHERE id = :id", array(':id' => $orderid));
		if ($order['status'] != '0') {
			message('抱歉，您的订单已经付款或是被关闭，请重新进入付款！', $this->createMobileUrl('myorder'), 'error');
		}
		if (checksubmit('paytype1')) {
			if ($order['paytype'] != 1) {
				message('抱歉，您的支付方式不正确，请重新提交订单！', $this->createMobileUrl('myorder'), 'error');
			}
			if ($_W['fans']['credit2'] < $order['totalprice']) {
				message('抱歉，您帐户的余额不够支付该订单，请充值！', create_url('mobile/module/charge', array('name' => 'member', 'weid' => $_W['weid'])), 'error');
			}
			if (pdo_update('card_members', array('credit2' => $profile['credit2'] - $order['totalprice']), array('from_user' => $_W['fans']['from_user']))) {
				pdo_update('shopping3_order', array('status' => 1), array('id' => $orderid));
				message('余额付款成功！', $this->createMobileUrl('myorder'), 'success');
			} else {
				message('余额付款失败，请重试！', $this->createMobileUrl('myorder'), 'error');

			}

		}

		if (checksubmit()) {
			$params['tid'] = $orderid;
			$params['user'] = $_W['fans']['from_user'];
			$params['fee'] = $order['totalprice'];
			$params['ordersn'] = $order['ordersn'];
			$params['title'] = $_W['account']['name'];
			$this->pay($params);
		}
		include $this->template('pay');
	}

	public function doMobileClear() {
		global $_W, $_GPC;
		$this->checkAuth();
		//清空购物车
		pdo_delete('shopping3_cart', array('weid' => $_W['weid'], 'from_user' => $_W['fans']['from_user']));
		message('清空购物车成功！', $this->createMobileUrl('list'), 'success');
	}

  

	private function checkAuth() {
	     global $_W;
		//if (empty($_W['fans']['from_user'])) {
		//	message('非法访问，请重新点击链接进入个人中心！');
		//}
	}


	public function payResult($params) {
		
		$order=pdo_fetch("SELECT status FROM ".tablename('shopping3_order')." WHERE id = {$params['tid']}");
		if($order['status']!=1){
			//计算库存
			$this->_inventory($params['tid']);
			$this->_assist(1,$params['tid']);
		}
		//付款后，将订单转为状态1
		pdo_update('shopping3_order', array('status'=>1,'ispay'=>1), array('id' => $params['tid']));
		
		if ($params['from'] == 'return') {
			message('支付成功！', '../../' . $this->createMobileUrl('wlmember'), 'success');
		}

	}

	//更新库存
	public function _inventory($_oid,$_do='reduce'){
 		$_goodslist = pdo_fetchall("SELECT * FROM ".tablename('shopping3_order_goods')." WHERE orderid = {$_oid}");
		
		foreach($_goodslist as $row){
			$_goods=pdo_fetch("SELECT total,sellnums FROM ".tablename('shopping3_goods')." WHERE id = {$row['goodsid']}");
			if($_goods['total']<0){
				pdo_update('shopping3_goods',array('sellnums'=>$_goods['sellnums']+$row['total']),array('id'=>$row['goodsid']));
			}else{
				$temp=pdo_update('shopping3_goods',array('sellnums'=>($_goods['sellnums']+$row['total']),'total'=>($_goods['total']-$row['total'])),array('id'=>$row['goodsid']));
			}
		}
	}	
	//检查库存
	public function _checkstock($_oid){
		$return =true;
 		$_goodslist = pdo_fetchall("SELECT * FROM ".tablename('shopping3_order_goods')." WHERE orderid = {$_oid}");
		$_nostock=array();
		foreach($_goodslist as $row){
			$_goods=pdo_fetch("SELECT title,total,sellnums FROM ".tablename('shopping3_goods')." WHERE id = {$row['goodsid']}");
			if($row['total']>$_goods['total'] && $_goods['total']!=-1){
				//更改订单
				pdo_update('shopping3_order',array('status'=>-1),array('id'=>$_oid));
				message($_goods['title'].'的库存不足，目前仅有'.$_goods['total'].'件,订单取消，请联系客服。',$this->createMobileUrl('wlmember',array('weid'=>$_GET['weid'])),'error');				
 				$return =false;
			}
		}
		return true;
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
		//尊敬的商户，您有一条新订单，链接地址mobile.php?act=module&name=shopping3&do=show&weid=1&orderid=23&secretid=4261
		
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
	//微动力内部已经有了
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
			$mail->FromName   = $_W['account']['name']."-微信订餐".date('m-d H:i');
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
				message("修改订单成功",$this->createMobileUrl('show',array('orderid'=>$orderid,'secretid'=>$secretid)));
			}
		}
		$condition="   id={$orderid} AND secretid='{$secretid}'";
		$order = pdo_fetch("SELECT * FROM ".tablename('shopping3_order')." WHERE    $condition ");
		 
		$row= pdo_fetchall("SELECT a.*,b.title,b.thumb,b.marketprice FROM ".tablename('shopping3_order_goods')." as a left join  ".tablename('shopping3_goods')." as b on a.goodsid=b.id WHERE a.weid = '{$weid}' and a.orderid={$order['id']}");
		//$address=pdo_fetch("SELECT * FROM ".tablename('shopping3_address')." WHERE   id={$order['aid']}");
		include $this->template('wl_show');
	}

}

