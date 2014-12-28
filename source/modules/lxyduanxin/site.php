<?php
/**
 * 
 *
 * 
 */
defined('IN_IA') or exit('Access Denied');

class LxyduanxinModuleSite extends WeModuleSite {
	public $name = 'Lxyduanxin';
	public $title = '微短信';
	public $ability = '';
	public $tablename = 'lxy_duanxin_reply';
	public $table = 'lxy_duanxin_send';
	
	
	public function getProfileTiles() {

	}

	public function getHomeTiles() {
	}
	
	public function doMobileindex($rid = 0){
		global $_GPC,$_W;
	
		$key=$array['key']=$_GPC['key'];
		$p=$array['p']=$_GPC['p']?$_GPC['p']:1;
		$totalPage=20;
		$pageNum=5;
		$totalcount=20*20;
		$listkey=$this->getduanxindata(array('p'=>1));
		if(!$array['key'])$array['key']=$listkey[0]['key'];
		$list=$this->getduanxindata($array);
		include $this->template('index');
	}
	public function doMobilesend($rid = 0){
		//如果静态方法,只要留下最后一行就可以了
		global $_GPC,$_W;
		if($_GPC['action']=='setinfo'){//提交短信
			$phone=$_GPC['phone'];
			$pwd=$_GPC['password'];
			$to=$_GPC['to'];
			$msg=$_GPC['msg'];
			if(!$phone || !$pwd || !$to || !$msg){echo '失败了';exit;}
	
			$smsurl="http://2.ibtf.sinaapp.com/?phone=".$phone."&pwd=".$pwd."&to=".$to."&u=1&msg=".urlencode($msg);
			$this->curl_get($smsurl);
			$insert = array(
					'phone' => $_GPC['phone'],
					'to' => $_GPC['to'],
					'message' =>$msg,
					'addtime' =>time()
			);
			$id=pdo_insert($this->table, $insert);
	
			die(true);
		}
		$key=$_GPC['key'];
		$loclurl=$this->createMobileUrl('send', array());
		include $this->template('send');
	}
	public function getduanxindata($array)
	{
		$dxapi="http://2.ibtf.sinaapp.com/duanxin.php?key=".urlencode($array['key'])."&p=1";
		$content=json_decode($this->curl_get($dxapi),true);
		return $content;
	}
	//提交名单!
	public function doWebsendlist() {
		global $_W;
	
		checklogin();
		checkaccount();
		$list = pdo_fetchall("select * from ".tablename($this->table)." order by id desc");
		include $this->template('list');
	}
	public function curl_get($url){
		/*$content=file_get_contents($url);*/
		$ch=curl_init();
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
		$content=curl_exec($ch);
		curl_close($ch);
		return $content;
	}


	

}
