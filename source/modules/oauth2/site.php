<?php
/**
 * 权限借用模块微站定义
 *
 * @author on3
 * @url 
 */
defined('IN_IA') or exit('Access Denied');

require_once("o2.php");

class Oauth2ModuleSite extends WeModuleSite {

	public $table_host = 'ohost';
	public $table_oauther = 'oauther';
	public $table_oerror = 'oerrorlog';

	public function doWebOsetting() {
		global $_W,$_GPC;
		if(!$_W['isfounder']){
			header('Location:'.$this->createWebUrl('Osetter'));
		}
		$list = pdo_fetchall('SELECT * FROM'.tablename('wechats')." WHERE level=2");
		$host = o2::getHost();
		$foo = $_GPC['foo'];
		if($foo=='getit'){
			$weid = $_GPC['id'];
			if(empty($weid)){
				exit();
			}
			$item = pdo_fetch('SELECT * FROM'.tablename('wechats').' WHERE weid = :weid',array(':weid'=>$weid));
			if(empty($item)){
				exit();
			}
			$key = o2::blurStr($item['key'],3);
			$secret = o2::blurStr($item['secret'],3);
			return json_encode(array('key'=>$key,'secret'=>$secret));
		}
		if(checksubmit()){
			if($_W['isfounder']){
				$type = $_GPC['type']?intval($_GPC['type']):message('重要分类参数缺失','','error');
			if($type==2){//选择已有公众号
				$data = array('host'=>$_GPC['host']);
			}else{//自填公众号
				$data = array('key'=>$_GPC['appid_1'],'secret'=>$_GPC['appsecret_1']);
			}
			$data['content'] = $_GPC['content'];
			$data['type'] = $type;
			if(!empty($host)){
				pdo_update($this->table_host,$data);
			}else{
				pdo_insert($this->table_host,$data);
			}
			message('操作成功','','success');
			}else{
				message('您的权限不够,别乱搞...');
			}
		}
		include $this->template('oset');
	}

	public function doWebOsetter() {
		global $_W,$_GPC;
		$host = o2::getHost();
		$oauther =o2::getOauther($_W['weid']);
		$user = o2::getUserInfo($_W['account']['uid']);
		$poorer = $user['name']=='初级会员'?1:0;
		if(empty($host)){
			message('管理员未开通此功能...','','error');
		}
		if($host['type']==1){//自填
			if(empty($host['key'])||empty($host['secret'])){
				message('缺失重要的自定义参数','','error');
			}
			$item = array('key'=>$host['key'],'secret'=>$host['secret']);
		}else{//读取
			$oweid = $host['host'];
			$list = pdo_fetch('SELECT * FROM'.tablename('wechats')." WHERE weid = :weid",array(':weid'=>$oweid));
			if(!empty($list)){
				$item = array('key'=>o2::blurStr($list['key'],3),'secret'=>o2::blurStr($list['secret'],3));
			}else{
				message('缺失重要的宿主对象','','error');
			}
		}
		if(checksubmit()){
			$type = $_GPC['type'];//==1不借用..==2借用
			if($user['name']=='初级会员'&&$type==2){
				message('您的权限不够..','','error');
			}
			if($type==1){
					$key = !empty($_GPC['appid_1'])?$_GPC['appid_1']:message('重要参数丢失');
					$secret = !empty($_GPC['appsecret_1'])?$_GPC['appsecret_1']:message('重要参数丢失');
					$data = array('key'=>$key,'secret'=>$secret);
				}elseif($type==2){
				}else{
					message('缺失重要的分类参数','','error');
				}
				$data['type'] =$type;
			if(empty($oauther)){
				$data['createtime'] =TIMESTAMP;
				$data['weid'] = $_W['weid'];
				pdo_insert($this->table_oauther,$data);
			}else{
				pdo_update($this->table_oauther,$data,array('weid'=>$_W['weid']));
			}
			message('诶呦厉害啦..操作成功..','','success');
		}
		include $this->template('osetter');
	}

	public function doWebErrorlog() {
		global $_W,$_GPC;
		$pageIndex = max(1, intval($_GPC['page']));
		$pageSize = 30;
		/*$this->wechatText('萨比','o8QDijn0UWl6T3XnrdifarKVV-kw');*/
		$total = pdo_fetchcolumn("SELECT count(*) FROM ".tablename($this->table_oerror));
		if(!$_W['isfounder']){
			$condition = ' WHERE 1=1 AND weid = '.$_W['weid'];
		}else{
			$condition = ' AS t1 JOIN'.tablename('wechats').'AS t2 ON t1.weid = t2.weid';
		}
		$list = pdo_fetchall('SELECT * FROM'.tablename($this->table_oerror)." $condition LIMIT " . ($pageIndex - 1) * $pageSize . ',' . $pageSize);
		$pager = pagination($total, $pageIndex, $pageSize);
		include $this->template('errorlog');
	}

	public function doMobileEntry(){
		global $_W,$_GPC;
		/*$data = array('first'=>array('value'=>urlencode('测试'),'color'=>'#FF0000'),
			'keyword1'=>array('value'=>urlencode('测试'),'color'=>'#FF0000'),
			'keyword2'=>array('value'=>urlencode('测试'),'color'=>'#FF0000'),
			'remark'=>array('value'=>urlencode('测试'),'color'=>'#FF0000'),
			);
		$content =  $this->createTplData($data,'o8QDijihhQFMFPd-t1ruryFuVr4k','sUiz5Dd2l-LBgAxrs1XmqlaZn1ZrjerIg9Xfm2Kw7iU','www.jiaduiguo.net');
		$callback =  o2::tplMsg($content);
		print_r($callback);
		exit();*/
		$user = o2::getOuser();
		print_r($user);
		exit();
		/*require_once IA_ROOT.'/source/modules/oauth2/o2.php';
		require_once IA_ROOT.'/source/modules/oauth2/emoji.php';
		$user = o2::getOuser();*/
	}

	public function doWebOautherlist(){
		global $_W,$_GPC;
		$pageIndex = max(1, intval($_GPC['page']));
		$pageSize = 30;
		$total = pdo_fetchcolumn("SELECT count(*) FROM ".tablename($this->table_oauther));
		if(!$_W['isfounder']){
			$condition = ' WHERE 1=1 AND weid = '.$_W['weid'];
		}else{
			$condition = ' JOIN'.tablename('wechats').'AS t2 ON t1.weid = t2.weid';
		}
		$list = pdo_fetchall('SELECT * FROM'.tablename($this->table_oauther)." AS t1 $condition LIMIT " . ($pageIndex - 1) * $pageSize . ',' . $pageSize);
		$pager = pagination($total, $pageIndex, $pageSize);
		include $this->template('oautherlist');
	}

	private function wechatText($msg,$o) {
		global $_GPC, $_W;
		$token = account_weixin_token($_W['account']);
		/*$token =$_W['account']['access_token']['token'];*/
		if(empty($token)){
			$token = account_weixin_token($_W['account']);
		}
		if(empty($msg)){
			message('诶呦,厉害啦..消息还能为空了?');
		}
		if(empty($o)){
			message('诶呦,厉害啦..发送对象还能为空了?');
		}
		$sendurl = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=%s";
		$url =  sprintf($sendurl,$token);
		$dat = urldecode(json_encode(array('touser'=>$o, 'msgtype'=>'text','text'=>array('content'=>urlencode($msg)))));
		$data=ihttp_post($url,$dat);
		if($data['code']!=200){
			message('网络不畅..请稍后再尝试...');
		}
		$content = json_decode($data['content'],true);
		if($content['errcode']!=0){
				message(account_weixin_code($content['errcode']));
				exit();
		}else{
				return 'ok';
		}
	}
	
	public function createTplData($data,$o,$id,$url,$color='#FF0000'){
		return array('touser'=>$o,'template_id'=>$id,'url'=>$url,'topcolor'=>$color,'data'=>$data);
	}
/*function account_weixin_token($account) {
	if(is_array($account['access_token']) && !empty($account['access_token']['token']) && !empty($account['access_token']['expire']) && $account['access_token']['expire'] > TIMESTAMP) {
		return $account['access_token']['token'];
	} else {
		if(empty($account['weid'])) {
			message('参数错误.');
		}
		if (empty($account['key']) || empty($account['secret'])) {
			message('请填写公众号的appid及appsecret, (需要你的号码为微信服务号)！', create_url('account/post', array('id' => $account['weid'])), 'error');
		}
		$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$account['key']}&secret={$account['secret']}";
		$content = ihttp_get($url);
		if(empty($content)) {
			message('获取微信公众号授权失败, 请稍后重试！');
		}
		$token = @json_decode($content['content'], true);
		if(empty($token) || !is_array($token)) {
			message('获取微信公众号授权失败, 请稍后重试！ 公众平台返回原始数据为: <br />' . $token);
		}
		if(empty($token['access_token']) || empty($token['expires_in'])) {
			message('解析微信公众号授权失败, 请稍后重试！');
		}
		$record = array();
		$record['token'] = $token['access_token'];
		$record['expire'] = TIMESTAMP + $token['expires_in'];
		$row = array();
		$row['access_token'] = iserializer($record);
		pdo_update('wechats', $row, array('weid' => $account['weid']));
		return $record['token'];
	}
}*/
}