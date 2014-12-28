<?php
/**
 * 微团购模块定义
 *
 * @author 
 * @url 
 */
defined('IN_IA') or exit('Access Denied');
define('RES', "http://src.mmghome.com/");

class GrouponModule extends WeModule {
	public function fieldsFormDisplay($rid = 0) {
		//要嵌入规则编辑页的自定义内容，这里 $rid 为对应的规则编号，新增时为 0
	}

	public function fieldsFormValidate($rid = 0) {
		//规则编辑保存时，要进行的数据验证，返回空串表示验证无误，返回其他字符串将呈现为错误提示。这里 $rid 为对应的规则编号，新增时为 0
		return '';
	}

	public function fieldsFormSubmit($rid) {
		//规则验证无误保存入库时执行，这里应该进行自定义字段的保存。这里 $rid 为对应的规则编号
	}

	public function ruleDeleted($rid) {
		//删除规则时调用，这里 $rid 为对应的规则编号
	}

	public function __comm($f_name){
		global $_W,$_GPC;
		checklogin();
		$weid=$_W['weid'];
		$subcp = $_GPC['subcp']?$_GPC['subcp']:NULL;
		
		include_once  'web/wl'.strtolower(substr($f_name,2)).'.php';
	}	
 	
	public function doPayset(){
		$this->__comm(__FUNCTION__);
	}
	//团购商品列表
	public function doGoods(){
		$this->__comm(__FUNCTION__);
	}
	
		//团购商品设置
	public function dodelgoods(){
		$this->__comm(__FUNCTION__);
	}	
	
	
	//团购商品设置
	public function doupdategoods(){
		$this->__comm(__FUNCTION__);
	}	
	//团购最新通知
	public function donotice(){
		$this->__comm(__FUNCTION__);
	}	
	
	//团购订单管理
	public function doorder(){
		$this->__comm(__FUNCTION__);
	}	
	public function douser(){
		$this->__comm(__FUNCTION__);
	}		 
	public function doEticket (){
		$this->__comm(__FUNCTION__);
	}	
	public function doRefund (){
		$this->__comm(__FUNCTION__);
	}	
	public function doVerification (){
		$this->__comm(__FUNCTION__);
	}		
 	public function dochangeStatus(){
		global $_GPC;
		pdo_update('groupon_list',array('status'=>$_GPC['ck']),array('id'=>$_GPC['id'],'weid'=>$_GPC['weid']));
	}
	//
	public function message($error,$url='',$errno=-1){
		$data=array();
		$data['errno']=$errno;
		if(!empty($url)){
			$data['url']=$url;		
		}
		$data['error']=$error;		
		echo json_encode($data);
		exit;
	}
}