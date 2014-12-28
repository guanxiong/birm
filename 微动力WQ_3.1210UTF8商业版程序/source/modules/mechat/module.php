<?php
/**
 * 美洽客服接入模块定义
 *
 * @author Yokit QQ:182860914
 * @url http://bbs.we7.cc/forum.php?mod=forumdisplay&fid=36&filter=typeid&typeid=1
 */
defined('IN_IA') or exit('Access Denied');

class MechatModule extends WeModule {
	public $tablename = 'mechat';
	public $gateway = array();
	public $atype;
	
	public function __construct(){
		global $_W;
		$this->atype='';
		if($_W['account']['type'] == '1') {
			$this->atype = 'weixin';
			$this->gateway['get'] = "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=";
			$this->gateway['create'] = "https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=%s";
		}
		if($_W['account']['type'] == '2') {
			$this->atype = 'yixin';
			$this->gateway['get'] = "";
			$this->gateway['create'] = "";
		}
		//$this->gateway['mechat'] = "http://open.mobilechat.im/cgi-bin/token?grant_type=client_credential&appid=%s&secret=%s";
		//$this->account_token = 'account_'.$atype.'_token';
	}
	
	public function doDisplay() {
		//这个操作被定义用来呈现主导航栏上扩展菜单，每个模块只能呈现一个扩展菜单，有更多选项，请在页面内使用标签页来表示
		global $_W,$_GPC;
		$mechat=array();
		if(checksubmit('submit')){
			$file = IA_ROOT . '/source/modules/mechat/function.php';
			
			if (!file_exists($file)) {
				return array();
			}
			
			include_once($file);
			
			$sql = "SELECT * FROM " . tablename($this->tablename) . " WHERE `weid`=:weid";
			$row = pdo_fetch( $sql, array(':weid'=>$_W['weid']) );
			if($row){
				$dat=iunserializer($row["cdata"]);
				$pass=$dat["pass"];
				if($_GPC['mechat-pass']!=$pass){
					$pass=md5($_GPC['mechat-pass']);
				}
				$mechat=array(
							"name" => $_GPC['mechat-user'],
							"pass" => $pass,
							"appid" => $_GPC['mechat-appid'],
							"appsecret" => $_GPC['mechat-appsecret'],
							);
				$access_token = array("token" => $dat["access_token"]["token"], "expire" => $dat["access_token"]["expire"]);
				$update=array(
							"name" => $_GPC['mechat-user'],
							"cdata" => iserializer($mechat),
							"access_token" => $access_token
							);
				pdo_update($this->tablename, $update, array('weid' => $_W['weid']));
			}else{
				$pass=md5($_GPC['mechat-pass']);
				$mechat=array(
							"name" => $_GPC['mechat-user'],
							"pass" => $pass,
							"appid" => $_GPC['mechat-appid'],
							"appsecret" => $_GPC['mechat-appsecret']
							);
				$access_token = array("token" => "", "expire" => "");
				pdo_insert($this->tablename, array("weid" => $_W['weid'], "name" => $_GPC['mechat-user'], "cdata" => iserializer($mechat), "access_token" => $access_token, "createtime" => TIMESTAMP) );
			}
			//exit(json_encode($_W));
			$dat = array(
						"unit" => $_GPC['mechat-user'],
						"password" => $pass,
						"wxAppid" => $_W['account']['key'],
						"wxAppsecret" => $_W['account']['secret']
						);
			
			$actoken = account_mechat_token( array("weid" => $_W['weid'], "access_token" => $access_token, "appid" => $_GPC['mechat-appid'], "appsecret" => $_GPC['mechat-appsecret']) );
			$url = sprintf("http://open.mobilechat.im/cgi-bin/weixin/bind?access_token=%s", $actoken);
			
			$content = ihttp_post($url, $dat);
			
			$dat2 = $content['content'];
			$result = @json_decode($dat2, true);
			
			if($result["errcode"]=="0"){
				message('恭喜，微信服务号与美洽企业帐号绑定成功！', create_url('index/module/display', array('name' => 'mechat')), 'success');
			}else{
				message("微信服务号与美洽企业帐号绑定错误. <br />参数： ".json_encode($dat)."<br />错误代码为: {$result['errcode']} <br />错误信息为: {$result['errmsg']}");
			}
		}
		$sql = "SELECT * FROM " . tablename($this->tablename) . " WHERE `weid`=:weid";
		$row = pdo_fetch( $sql, array(':weid'=>$_W['weid']) );
		if($row){
			$mechat["name"]=$row["name"];
			$dat=iunserializer($row["cdata"]);
			$mechat["pass"]=$dat["pass"];
			$mechat["appid"]=$dat["appid"];
			$mechat["appsecret"]=$dat["appsecret"];
		}
		include $this->template('display');
	}

}