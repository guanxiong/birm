<?php
/**
 * 微汽车模块定义
 *
 * @author 微新星
 * @url
 */
defined('IN_IA') or exit('Access Denied');

class We7carModuleSite extends WeModuleSite {


	public function __web($f_name){
		global $_W,$_GPC;
		checklogin();
		$weid=$_W['weid'];
		$op = $_GPC['op']?$_GPC['op']:'list';
		include_once  'web/wl'.strtolower(substr($f_name,5)).'.php';
	}

	//品牌管理
	public function doWebBrand(){
		$this->__web(__FUNCTION__);
	}

	//车系管理
	public function doWebSeries(){
		$this->__web(__FUNCTION__);
	}

	//品牌车型
	public function doWebType(){
		$this->__web(__FUNCTION__);
	}

	//客服管理
	public function doWebKefu(){
		$this->__web(__FUNCTION__);
	}
	public function doWebGuanhuai(){
		$this->__web(__FUNCTION__);
	}
	//留言管理
	public function doWebMessage(){
		$this->__web(__FUNCTION__);
	}
	//常用工具
	public function doWebTool(){
		$this->__web(__FUNCTION__);
	}
	//预约试驾
	public function doWebYuyue(){
		$this->__web(__FUNCTION__);
	}
	//前台
	public function doWebgetseries(){
		global $_W,$_GPC;
		$weid=$_W['weid'];
		$bid=$_GPC['bid'];
	   	$serieslist= pdo_fetchall("SELECT * FROM ".tablename('weicar_series')." WHERE weid=".$weid." and bid=".$bid." ");
		$data=array(
			'status'=>1,
			'list'=>$serieslist,
		);
		echo json_encode($data);
	}


	//删除图片
	public function doWebDeleteImage() {
		global $_GPC;
		$id = intval($_GPC['id']);
		$sql = "SELECT id, thumb FROM " . tablename($this->tablename) . " WHERE `id`=:id";
		$row = pdo_fetch($sql, array(':id'=>$id));
		if (empty($row)) {
			message('抱歉，回复不存在或是已经被删除！', '', 'error');
		}
		if (pdo_update($this->tablename, array('thumb' => ''), array('id' => $id))) {
			file_delete($row['thumb']);
		}
		message('删除图片成功！', '', 'success');
	}

	public function __mobile($f_name){
		global $_W,$_GPC;
		$weid=$_W['weid'];
		$op = $_GPC['op']?$_GPC['op']:'list';
		include_once  'mobile/wl'.strtolower(substr($f_name,8)).'.php';
	}
	//前台
	public function doMobileMessage(){
		$this->__mobile(__FUNCTION__);
	}
	//联系客服
	public function doMobileKefu(){
		$this->__mobile(__FUNCTION__);
	}
	//试驾预约
	public function doMobileYuyue(){
		$this->__mobile(__FUNCTION__);
	}
	//客户关怀
	public function doMobileGuanhuai(){
		$this->__mobile(__FUNCTION__);
	}
	//车系列
	public function doMobileSeries(){
		$this->__mobile(__FUNCTION__);
	}
	public function doMobileTool(){
		$this->__mobile(__FUNCTION__);
	}




}