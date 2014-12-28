<?php
/**
 * NOW大了模块微站定义
 *
 * @author yuexiage
 * @url 
 */
defined('IN_IA') or exit('Access Denied');

class NowbigModuleSite extends WeModuleSite {
	public $name = 'Nowbig';
	public $title = 'NOW大了';
	public $tablename = 'nowbig_reply';
	
	public function doMobileNowbig(){
		global $_GPC ,$_W;
		$fromuser = authcode(base64_decode($_GPC['from_user']), 'DECODE');
		$id = intval($_GPC['id']);
/* 		$tsmark = pdo_fetch("SELECT * FROM ".tablename($this->tablename)." WHERE rid = '".$id."' LIMIT 1");
		if (empty($tsmark)) {
			exit('非法参数！0');
		} */
		$posturl=create_url('mobile/module/ajax', array('name' => 'nowbig', 'id' => $id,'weid'=>$_GPC['weid'],'mod'=>'photo'));
		$siteurl=str_replace('resource/attachment/','',$_W['attachurl']);
		$attachurl=$siteurl.create_url('mobile/module/ajax', array('name' => 'nowbig', 'id' => $id,'weid'=>$_GPC['weid'],'mod'=>'img'));
		
		include $this->template('index');
	}
	
	public function doMobileAjax() {
		global $_GPC ,$_W;
		if($_GPC['mod']=='photo'){
/* 			$k=fopen("1.txt","w+");
			fwrite($k,$_GPC['photo']);
		
			$k=fopen("2.txt","w+");
			fwrite($k,$_GPC['photo0']);
			
			$k=fopen("3.txt","w+");
			fwrite($k,$_GPC['photo1']); */
			
			$sec = explode(" ", microtime());
		    $img_name="./source/modules/nowbig/template/img/".$sec['1'].".jpg";
			if($imga=explode(',',$_GPC['photo'])){
				$k=fopen($img_name,"w+");
				fwrite($k,base64_decode($imga['1']));
			}
			$insert=array(
				'img'=>$img_name,
				'from_user'=>$_GPC['from_user'],
				'mark'=>$sec['1']
			);
			$id=pdo_insert('nowbig_user', $insert);
			$result['ok'] = 1; //状态
			$result['p'] = $sec['1']; //标识号
			$result['lcode'] = '你NOW大了么？'; //标识号
			echo json_encode($result);
		}else if($_GPC['mod']=='img'){
			$img="./source/modules/nowbig/template/img/".$_GPC['p'].'.jpg';
			$siteroot =create_url('mobile/module/nowbig', array('name' => 'nowbig', 'id' => $_GPC['id'],'weid'=>$_GPC['weid']));
			include $this->template('mobile');
		}
		
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	public function doMobileCover() {
		//这个操作被定义用来呈现 功能封面
		include $this->template('index');
	}
	public function doWebMenu() {
		//这个操作被定义用来呈现 管理中心导航菜单
	}
	public function doMobileHome() {
		//这个操作被定义用来呈现 微站首页导航图标
	}
	public function doMobileProfile() {
		//这个操作被定义用来呈现 微站个人中心导航链接
	}
	public function doMobileShortcut() {
		//这个操作被定义用来呈现 微站快捷功能导航
	}

}