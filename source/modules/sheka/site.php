<?php
/**
 * 贺卡模块处理程序
 *
 * @author nuqut
 * @url  heka.weibaza.com
 */
defined('IN_IA') or exit('Access Denied');
class ShekaModuleSite extends WeModuleSite {
private $turlar=array(
'1'=>array('id'=>1,'name'=>"生日卡",'ename'=>"shengri"),
'2'=>array('id'=>2,'name'=>"祝贺卡",'ename'=>"zhuhe"),
'3'=>array('id'=>3,'name'=>"爱情卡",'ename'=>"aiqing"),
'4'=>array('id'=>4,'name'=>"亲友卡",'ename'=>"qinyou"),
'5'=>array('id'=>5,'name'=>"心情卡",'ename'=>"xinqing"),
'6'=>array('id'=>6,'name'=>"感谢卡",'ename'=>"ganxie"),
'7'=>array('id'=>7,'name'=>"道歉卡",'ename'=>"daoqian"),
'8'=>array('id'=>8,'name'=>"打气卡",'ename'=>"weiwen"),
'9'=>array('id'=>9,'name'=>"会面卡",'ename'=>"baifang"),
'10'=>array('id'=>10,'name'=>"节日卡",'ename'=>"jieri"),
'11'=>array('id'=>11,'name'=>"商务定制",'ename'=>"dingzhi"),
'12'=>array('id'=>12,'name'=>"其他卡",'ename'=>"qita"),
);

private $slide=array(
'0'=>array('id'=>1,'name'=>"生日卡"),
'1'=>array('id'=>2,'name'=>"祝贺卡"),
'2'=>array('id'=>3,'name'=>"爱情卡"),
);

   public $appId;
	public $appSecret;
	public function __construct() {
		global $_W;
		$_W['settings']=$_W['account']['modules']['sheka']['config'];
		if ($_W['account']['level']!=0){
		  $this->appSecret = $_W['account']['secret'];
		  $this->appId =$_W['account']['key'];
		}
		
		if (empty($this->appId)){
			$this->appId=$_W['settings']["appid"];
		}
		if (empty($this->secret)){
			$this->appSecret=$_W['settings']["secret"];
		}
			
		
}



	
	//获取Ticket
	private function getJsApiTicket() {
		global $_W;
		$data = array();
		$wechat = pdo_fetch("SELECT access_token FROM ".tablename('wechats')." WHERE weid = {$_W['weid']}");
		$AccessToken = iunserializer($wechat['access_token']);
		$now = time();
		
		if($AccessToken['expire']<$now || !$AccessToken['ticket']){//失效时,从服务器获取最新的access_token和ticket

			$res = ihttp_get("https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$this->appId&secret=$this->appSecret");
			$content = @json_decode($res['content'], true);
			$access_token = $content['access_token'];
			
			$res1 = ihttp_get("https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=$access_token");
		
			$content1 = @json_decode($res1['content'], true);
			$ticket = $content1['ticket'];

			$data['token'] = $access_token;
			$data['expire'] = $now+7000;//是7200秒失效,这里取7000
			$data['ticket'] = $ticket;

			pdo_update("wechats",array('access_token'=>serialize($data)),array('weid'=>$_W['weid']));
		}else{
			$ticket = $AccessToken['ticket'];
			
		}
		return $ticket;
	}

public function doMobileIndex(){
		global $_GPC, $_W;
		 $jsapiTicket = $this->getJsApiTicket();
		 $timestamp = time();
		 $nonceStr = random(16);
		 $wurl = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
		 $signature = sha1("jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$wurl");
		include $this->template('index');
	}
public function doMobileList(){
		global $_GPC, $_W;
		$classid = intval($_GPC['classid']);
       $list = pdo_fetchall("SELECT * FROM " . tablename('sheka_list') . "  where classid= '{$classid}'  and (weid = '{$_W['weid']}'  or weid =0)  ORDER BY id deSC");
		
		 $jsapiTicket = $this->getJsApiTicket();
		 $timestamp = time();
		 $nonceStr = random(16);
		 $wurl = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
		 $signature = sha1("jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$wurl");
		
		include $this->template('list');
	}
	public function doMobilePreview(){
			global $_GPC, $_W;
			$id = intval($_GPC['id']);
		$sql = "SELECT * FROM " . tablename('sheka_list') . " WHERE `id`=:id";
		$detail = pdo_fetch($sql, array(':id'=>$id));

		if (empty($detail['id'])) {
			exit;
		}
						include $this->template('preview');

					
	}
		public function doMobileTemp(){
					global $_GPC, $_W;
			$id = intval($_GPC['id']);
         $data = pdo_fetch("SELECT * FROM " . tablename('sheka_list') . " WHERE id = '{$id}' ");
			//include $this->template('temp');
				if ($data['tempid']==1) {
				include $this->template('temp/'.$data['id'].'');
				}else {
				$zhufu = pdo_fetch("SELECT * FROM " . tablename("sheka_zhufu") . " WHERE  cid = :cid  ", array(
                    ':cid' => $id
				));
						include $this->template('temp_'.$data['tempid'].'');
				}
		}

		
		public function doMobileSendform(){
					global $_GPC, $_W;
			$id = intval($_GPC['id']);
		$sql = "SELECT * FROM " . tablename('sheka_list') . " WHERE `id`=:id";
		$data = pdo_fetch($sql, array(':id'=>$id));
		if (empty($data['id'])) {
			exit;
		}
		 $zhufu = pdo_fetch("SELECT * FROM " . tablename('sheka_zhufu') . " WHERE cid = '{$id}' ");
		 $zhufulist = pdo_fetchall("SELECT * FROM " . tablename('sheka_zhufu') . " as z left join  " . tablename('sheka_list') . " as l   on  z.cid=l.id WHERE l.classid = '{$data['classid']}'   and  l.lang = '{$data['lang']}'  limit 0,10");
					include $this->template('sendform');

		}
				public function doMobileCardshow(){
					global $_GPC, $_W;
			$id = intval($_GPC['id']);
			$cardFrom = htmlspecialchars_decode($_GPC['cardFrom']);
			$cardTo = htmlspecialchars_decode($_GPC['cardTo']);
			$cardBody =htmlspecialchars_decode( $_GPC['cardBody']);
		$sql = "SELECT * FROM " . tablename('sheka_list') . " WHERE `id`=:id";
		$data = pdo_fetch($sql, array(':id'=>$id));
		if (empty($data['id'])) {
			exit;
		 }
		 $jsapiTicket = $this->getJsApiTicket();
		 $timestamp = time();
		 $nonceStr = random(16);
		 $wurl = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
		 $signature = sha1("jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$wurl");
		 include $this->template('cardshow');
		}
}