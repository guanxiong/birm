<?php
/**
 * 微餐饮模块微站定义
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
	//后台-会员管理
	public function doWebMember() {
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
	public function doWebordertype(){
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

		$this->checkAuth();

		$weid=isset($_W['weid'])?$_W['weid']:$_GPC['weid'];
		if($_GPC['do']=='wlcart'){
			$set=pdo_fetch("SELECT shop_name,order_limit,address_list,room_list,desk_list,sms_status,sms_resgister,ordretype1,ordretype2,ordretype3,yy_start_time,yy_end_time FROM ".tablename('shopping3_set')." WHERE  weid = '{$weid}' ");
		}else{
			$set=pdo_fetch("SELECT shop_name,order_limit,sms_status,sms_resgister,ordretype1,ordretype2,ordretype3 FROM ".tablename('shopping3_set')." WHERE  weid = '{$weid}' ");
		}
 		//获取的方式分2种，url的openid，或者$_W['fans'] 后者优先，sms_resgister=0关闭
		if($_GPC['do']!='reg'&&$set['sms_resgister']==1){
			$fans=pdo_fetch("select * from ".tablename("shopping3_fans")." where weid=:weid AND  from_user=:from_user",array(':weid'=>$_W['weid'],':from_user'=>$_W['fans']['from_user']));
			if($fans==false){
				header('Location: '.$this->createMobileurl('reg'));
				exit;
			}
		}

		if(empty($weid)){
			message('参数错误，进入微餐饮');
		}
		$from=isset($_W['fans']['from_user'])?$_W['fans']['from_user']:$_GPC['openid'];

		$subcp = $_GPC['subcp']?$_GPC['subcp']:NULL;

		$totalnum=pdo_fetchcolumn("SELECT sum(total) FROM ".tablename('shopping3_cart')." WHERE from_user = :from_user AND weid = '{$weid}' ", array(':from_user' => $from));


		$title=$set['shop_name'];
		include_once  'site/'.strtolower(substr($f_name,8)).'.php';
	}
	//首次认证
	public function doMobilereg(){
		$this->__comm(__FUNCTION__);
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
		//if (checksubmit()) {
		$params['tid'] = $orderid;
		$params['user'] = $_W['fans']['from_user'];
		$params['fee'] = $order['totalprice'];
		$params['title'] = $_W['account']['name'];
		$params['ordersn'] = $order['ordersn'];
		$params['virtual'] = $order['goodstype'] == 2 ? true : false;
		$bootstrap_type = 3;
		include $this->template('header');
		$this->pay($params);
		include $this->template('footerbar');
		//}
		//include $this->template('pay');
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
		if (empty($_W['fans']['from_user'])) {
			message('非法访问，请重新点击链接进入个人中心！');
		}
	}


	public function payResult($params) {

		$order=pdo_fetch("SELECT status FROM ".tablename('shopping3_order')." WHERE id = {$params['tid']}");
		//付款后，将订单转为状态1
		if($params['type']=='credit2'){
			pdo_update('shopping3_order', array('status'=>1,'ispay'=>1,'paytype'=>1), array('id' => $params['tid']));
		}else{
			pdo_update('shopping3_order', array('status'=>1,'ispay'=>1,'paytype'=>2), array('id' => $params['tid']));
		}

		if($order['status']!=1){
			//计算库存
			$this->_inventory($params['tid']);
			$this->_assist(1,$params['tid']);
		}


		if ($params['from'] == 'return') {
			//strai若果使用了优惠券对优惠券进行处理
			$file = IA_ROOT . "/source/modules/izc_strcoupon/advcard.mod.php";
			if(is_file($file)) {
				require $file;
				global $_GPC;
				$_GPC['name'] = 'shopping3';
				//有时间判断下
				$res = coupon_fans_used($params['tid'],1);
			}
			message('支付成功！',$this->createMobileUrl('wlmember'), 'success');
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
		$order = pdo_fetch("SELECT * FROM ".tablename('shopping3_order')." WHERE  id={$_oid}");
		//<1B40><1B40><1B40><1D2111><1B6101>喜乐乐餐厅<0D0A><1B6100><1D2100><0D0A>顾客昵称：何先生<0D0A>联系电话：15884975988<0D0A>配送地址：佛山科学技术学院<0D0A>订单详情：<0D0A>辣子鸡丁 X 1	 ￥28.00<0D0A>水煮鱼   X 1	 ￥68.00<0D0A>米饭     X 1	 ￥2.00<0D0A>热干面   X 1	 ￥6.00<0D0A>烧饼     X 1	 ￥5.00<0D0A>可乐     X 1	 ￥5.00<0D0A>水果沙拉 X 1	 ￥22.00<0D0A>小皮蛋   X 1	 ￥16.00<0D0A>总计：￥152.00元<0D0A>备注：不要加辣，多点饭<0D0A><0D0A><0D0A><0D0A><0D0A><0D0A><0D0A><0D0A>

 		$orderlist=pdo_fetchall("SELECT  a.total as nums,b.title,b.marketprice,b.productprice FROM ".tablename('shopping3_order_goods')." as a  left join ".tablename('shopping3_goods')." as b on a.goodsid=b.id  WHERE a.orderid = '{$order['id']}'");

		$txt="<h1>您有新订单</h1>";
		$txt.="<table style='min-width:320px;'><tr><td>菜名</td><td>单价</td><td>数量</td><td>总价</td></tr>";
		$print_order="<1D2100>------------------------<0D0A>订单详情：<0D0A>";
		foreach($orderlist as $v){
			$txt.="<tr><td>".$v['title']."</td><td>".$v['marketprice']."</td><td>".$v['nums']."</td><td>".$v['marketprice']*$v['nums']."</td></tr>";
			$print_order.=$this->_formatstr2($v['title'],22)."X".$v['nums']." ￥".$v['marketprice']."<0D0A>";
		}
		$txt.="<tr><td style='color:red'>总计</td><td> </td><td>".$order['totalnum']." </td><td>".$order['totalprice']."</td></tr>";
		$print_order.="总计：".$order['totalnum']."件     ￥".$order['totalprice']."<0D0A>------------------------<0D0A>";

		$txt.="</table>";
		$txt.="<br>---------------";
		$txt.="<br>订单单号:".$order['ordersn'];
		$status='';
		if($order['paytype'] == 1){
			$paystatus="余额支付";
		}elseif($order['paytype'] == 2){
			$paystatus="在线支付";
		}elseif($order['paytype'] == 3){
			$paystatus="现金支付";
		}
		if($order['ispay']==1){
			$paystatus.="[已付款]";
		}else{
			$paystatus.="[未付款]";
		}

		$txt.="<br>付款状态:".$paystatus;
		$status='';
		if($order['print_sta'] == -1){
			$status='未打印';
		}else{
			$status='已打印';
		}

 		$txt.="<br>打印状态:".$status;
		if($order['order_type']==2){
			$txt.="<br>订单类型:店内下单";
 		}elseif($order['order_type']==3){
			$txt.="<br>订单类型:到店自提";
 		}else{
			$txt.="<br>订单类型:外卖外送";
 		}

		$txt.="<br>下单时间:".date('Y/m/d H:i:s', $order['createtime']);

		$txt.="<br>就餐时间:".$order['time_day'].' '.$order['time_hour'].":".$order['time_second'];

		$txt.="<br>备注:".$order['remark'];


		$txt.="<br>---------------";
		$txt.="<br>顾客昵称:".$order['guest_name'].($order['sex']==1?'先生':'女士');

		$txt.="<br>联系电话:".$order['tel'];

		if($order['order_type']==2){
			$txt.="<br>店内桌号:".$order['desk'];
			$txt.="<br>就餐人数:".$order['nums'];
		}elseif($order['order_type']==1){
			$txt.="<br>配送地址:".$order['guest_address'];
		}
		$txt.="<br>---------------";

		$txt.="<br>总计:".$order['totalprice']."<br>详情".$_W['siteroot'].$this->createMobileUrl('show',array('orderid'=>$order['id'],'secretid'=>$order['secretid']));

		//辅助系统，发邮件
		$this->_sendmail('您有新的订单了',$txt);
		//辅助系统，发短信
		//发送短信内容
		//尊敬的商户，您有一条新订单，链接地址mobile.php?act=module&name=shopping3&do=show&weid=1&orderid=23&secretid=4261
		if($set['print_type']==1&&$set['print_status']==1){
			$print_str.="订单单号:".$order['ordersn']."\r\n";
			$print_str.="付款状态:".$paystatus."\r\n";
			$print_str.="下单时间:".date('Y/m/d H:i:s', $order['createtime'])."\r\n";
			$print_str.="预约时间:".$order['time_day'].' '.$order['time_hour'].":".$order['time_second']."\r\n";
			if($order['order_type']==2){
				$print_str.="订单类型:店内下单\r\n";
				if(!empty($order['desk'])){
					$print_str.="店内桌号:".$order['desk']."\r\n";
				}
				if($order['nums']>0){
					$print_str.="就餐人数:".$order['nums']."人\r\n";
				}
			}elseif($order['order_type']==3){
				$print_str.="订单类型:到店自提\r\n";
				$print_str.="顾客昵称:".$order['guest_name'].($order['sex']==1?'先生':'女士')."\r\n";
				$print_str.="联系电话:".$order['tel']."\r\n";
 			}else{
				$print_str.="订单类型:外卖外送\r\n";
				$print_str.="顾客昵称:".$order['guest_name'].($order['sex']==1?'先生':'女士')."\r\n";
				$print_str.="联系电话:".$order['tel']."\r\n";
				$print_str.="配送地址:".$order['guest_address']."\r\n";
			}
			if(!empty($print_order)){
				$print_str.=$print_order;
			}
			if(!empty($order['remark'])){
				$print_str.="备注:".$order['remark']."\r\n";
			}
			//云打印
			$_set=array(
				'id'=>$_oid,
				'siteroot'=>$_W['siteroot'],
				'partnerId'=>$set['partnerId'],
				'machineCode'=>$set['machineCode'],
				'mKey'=>$set['mKey'],
				'apiKey'=>$set['apiKey'],
				'print_nums'=>$set['print_nums'],
				'print_top'=>$set['print_top'],
				'print_bottom'=>$set['print_bottom'],
			);
			$this->_sendprint($print_str,$_set);
		}
		//这里是短信设置
		if($set['sms_type']==1&&!empty($set['sms_phone'])){
			$smsstr='';
			$smsstr.="订单号:".$order['ordersn']."\n";
			//$smsstr.="付款状态:".$paystatus."\n";

			foreach($orderlist as $v){
 				$smsstr.=$v['title']."X".$v['nums']."￥".$v['marketprice']."\n";
			}
			if($order['order_type']==2){
				$smsstr.="订单类型:店内下单\n";
				if(!empty($order['desk'])){
					$smsstr.="店内桌号:".$order['desk']."\n";
				}
				if($order['nums']>0){
					$smsstr.="就餐人数:".$order['nums']."人\n";
				}
			}elseif($order['order_type']==3){
				$smsstr.="订单类型:到店自提\n";
				$smsstr.="顾客昵称:".$order['guest_name'].($order['sex']==1?'先生':'女士')."\n";
				$smsstr.="联系电话:".$order['tel']."\n";
 			}else{
				$smsstr.="订单类型:外卖外送\n";
				$smsstr.="顾客昵称:".$order['guest_name'].($order['sex']==1?'先生':'女士')."\n";
				$smsstr.="联系电话:".$order['tel']."\n";
				$smsstr.="配送地址:".$order['guest_address']."\n";
			}
			if(!empty($order['remark'])){
				$smsstr.="备注:".$order['remark']."";
			}

 			$this->_sendsms($smsstr,$set['sms_phone'],0,$set['sms_user'],$set['sms_secret']);
		}
		//$this->_sendsms($txt,'13813874744',$oid);

		//辅助系统，打印
		if($_status==1 && $set['print_status']==1){
			//付款完成，然后开启打印的时候
			//更改订单状态
			pdo_update('shopping3_order',array('print_sta'=>-1),array('id'=>$_oid));
		}
	}

	//打印机
	public function _sendprint($_str,$_set){
		//if(empty($_set['print_usr'])){
		//	return '';
		//}
		$print_nums=intval($_set['print_nums']);
		if($print_nums<0){
			$print_nums=1;
		}
		if($print_nums>3){
			$print_nums=4;
		}

		$_str='<1B40><1B40><1B40><1D2111><1B6101>'.$_set['print_top'].'<0D0A><1B6100><1D2100><0D0A>'.$_str;
		$_str=$_str.'<1D2100><0D0A>'.$_set['print_bottom'].'<0D0A><0D0A><1D2100><0D0A><0D0A><0D0A><0D0A>';
		/*$printArr=explode(',',$_set['print_usr']);
		$data = array(
			'dingdanID'=>$_set['id'],//$dingdanID, //订单号
			//'dayinjisn'=>'60103377',//.$dayinjisn, //打印机ID号
			'dingdan'=>$_str, //订单内容
			'pages'=>$print_nums, //联数
			'replyURL'=>$_set['siteroot'].'print.php',
			); //回复确认URL

		$str='';
		foreach($data as $k=>$v){
			$str.='&'.$k.'='.$v;
		}

		foreach($printArr as $v){
			$str1=$str.'&dayinjisn='.$v;
			$result = $this->postData('http://115.28.15.113:60002', $str1);
		}*/

		if($print_nums){
			for($i=1;$i<=$print_nums;$i++){
				$res = $this->sendMsgToElink($str1, $_set['apiKey'], $_set['mKey'], $_set['partnerId'], $_set['machineCode']);
				//echo $msg .'----'.$res;
			}
		}
	}
	//短信接口通道 预留
	public function _sendsms($_txt,$_phone,$_oid=0,$_uid="",$_key=""){
		//http://sms.webchinese.cn/web_api/SMS/?Action=SMS_Num&Uid=xmeimei&Key=e3221e82955cfea34f3c&smsMob=手机号码&smsText=短信内容"
		global $_W;
		if(empty($_txt)||empty($_phone)){
			return '';
		}

		if(empty($_uid) || empty($_key) ){
			$sms = pdo_fetch("SELECT sms_user,sms_secret FROM ".tablename('shopping3_set')." WHERE weid = :weid" , array(':weid' => $_W['weid']));
			if($sms==false){
				return '';
			}else{
				$_uid=$sms['sms_user'];
				$_key=$sms['sms_secret'];
			}
		}
		$sms_url="http://big.smsbao.com/sms.action?u=".$_uid."&p=".md5($_key)."&m=".$_phone."&c=".urlencode($_txt);
		$result=ihttp_request($sms_url);

		if($result['code']==200){
			$r=$result['content'];
			if($r==30){
				$msg='密码错误 ';
			}elseif($r==40){
				$msg='账号不存在 ';
			}elseif($r==41){
				$msg='余额不足 ';
			}elseif($r==42){
				$msg='帐号过期 ';
			}elseif($r==43){
				$msg='IP地址限制 ';
			}elseif($r==50){
				$msg='内容含有敏感词';
			}elseif($r==51){
				$msg='手机号码不正确';
			}else{
				$msg='发送成功';
			}
		}else{


		}
		if($_oid>0){
			//记录订单发送状态
			if(!empty($msg)){
				pdo_update('shopping3_order',array('sms_sta'=>$msg),array('id'=>$_oid));
			}
		}else{
			//记录用户发送时间
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
			$mail->FromName   = $_W['account']['name']."-微餐饮".date('m-d H:i');
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
	private  function _formatstr2($sstr,$slen=0,$isleft=true){
		if($slen==0 || $sstr=='') return $str;
		//$sstr=iconv("UTF-8","GB2312//IGNORE",$sstr);
		if(strlen($sstr)>$slen){
			for($i=0;$i<$slen;$i++){
				$sb=$sb." ";
			}
			$sstr=$sstr.'<0D0A>'.$sb;
		}else{
			for($i=strlen($sstr)-1;$i<=$slen;$i++){
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
				if($_GPC['status']==2){
					$set=pdo_fetch("SELECT shop_name,sms_status,shop_tel FROM ".tablename('shopping3_set')." WHERE  weid = '{$weid}' ");
					if($set['sms_status']==1){
						//查询订单
						$order = pdo_fetch("SELECT sms_sta,guest_name,ordersn,paytype,tel FROM ".tablename('shopping3_order')." WHERE id = :id", array(':id' => $orderid ));
						if($order!=false){
							if(empty($order['sms_sta'])){
								if($order['paytype'] == 1){
									$paystatus="余额支付";
								}elseif($order['paytype'] == 2){
									$paystatus="在线支付";
								}elseif($order['paytype'] == 3){
									$paystatus="现金支付";
								}
								//确定订单发短信
								$txt="尊敬的顾客,您的微信订单已确认.付款方式为".$paystatus.".如有疑问,请联系电话:".$set['shop_tel'].'['.$set['shop_name'].']';
								$this->_sendsms($txt,$order['tel'],$id);
							}
						}
					}

				}
				message("修改订单成功",$this->createMobileUrl('show',array('orderid'=>$orderid,'secretid'=>$secretid)));
			}
		}
		$condition="   id={$orderid} AND secretid='{$secretid}'";
		$order = pdo_fetch("SELECT * FROM ".tablename('shopping3_order')." WHERE    $condition ");

		$row= pdo_fetchall("SELECT a.*,b.title,b.thumb,b.marketprice FROM ".tablename('shopping3_order_goods')." as a left join  ".tablename('shopping3_goods')." as b on a.goodsid=b.id WHERE a.weid = '{$weid}' and a.orderid={$order['id']}");
		//$address=pdo_fetch("SELECT * FROM ".tablename('shopping3_address')." WHERE   id={$order['aid']}");
		include $this->template('wl_show');
	}

	public function postData($url, $data){
        $ch = curl_init();
        $timeout = 300;
		//curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转 （很重要）
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_REFERER, "http://115.28.15.113/");   //构造来路
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		//ob_start();
        $handles = curl_exec($ch);  //获取返回结果
		//$result = ob_get_contents() ;
        //ob_end_clean();
		//close connection
        curl_close($ch);
        //return $result;
		return $handles;
    }
	public function doWebpay() {
		include $this->template('pay');
	}

	private function httppost1($params) {
		$host = '114.215.116.141';
		$port = '8888';
		//需要提交的post数据
		$p = '';
		foreach ($params as $k => $v) {
			$p .= $k.'='.$v.'&';
		}
		$p = rtrim($p, '&');
		$header = "POST / HTTP/1.1\r\n";
		$header .= "Host:$host:$port\r\n";
		$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$header .= "Content-Length: " . strlen($p) . "\r\n";
		$header .= "Connection: Close\r\n\r\n";
		$header .= $p;
		$fp = fsockopen($host, $port);
		fputs($fp, $header);
		while (!feof($fp)) {
			$str = fgets($fp);
		}
		fclose($fp);
		return $str;
	}

	//向易联提交信息 返回易联返回标志
	private function sendMsgToElink($msg,$apiKey,$mKey,$partner,$machine_code){

		$params = array(
				'partner'=>$partner,
				'machine_code'=>$machine_code,
				'content'=>$msg,
		);

		$sign = $this->generateSign($params,$apiKey,$mKey);
		$params['sign'] = $sign;

		$return = $this->httppost1($params);
		return $return;
	}

	//易联签名算法
	private function generateSign($params, $apiKey, $msign)
	{

		//所有请求参数按照字母先后顺序排序
		ksort($params);
		//定义字符串开始 结尾所包括的字符串
		$stringToBeSigned = $apiKey;
		//把所有参数名和参数值串在一起
		foreach ($params as $k => $v)
		{
			$stringToBeSigned .= urldecode($k.$v);
		}
		unset($k, $v);
		//把venderKey夹在字符串的两端
		$stringToBeSigned .= $msign;
		//使用MD5进行加密，再转化成大写
		return strtoupper(md5($stringToBeSigned));
	}
}
