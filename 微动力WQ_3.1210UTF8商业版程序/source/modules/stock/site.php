<?php
/**
 * 通用表单模块订阅器
 *
 * @author Godietion Koo
 * @url http://beidoulbs.com/
 */
defined('IN_IA') or exit('Access Denied');
class StockModuleSite extends WeModuleSite {
	 public function doMobileshow() {
	 	global $_W,$_GPC;	 
	 	$userid=$_GPC['userid'];	
	 	$row=pdo_fetch("select * from".tablename('optionalstock').' where weid=:weid and userid=:userid and stkcode=:stkcode', array(':weid'=>$_W['weid'],':userid'=>$_GPC['userid'],':stkcode'=>$_GPC['stkcode']));
	 	$url="http://beidoulbs.com/stock.php?stkcode=".$_GPC['stkcode']."&token=beidoulbspublic";
	 	$datastr=file_get_contents($url);
	 	if(!empty($datastr)){
	 		$jsondata=json_decode($datastr,true);
	 		if($jsondata['resultcode']==200){
		 		$data=$jsondata['result'][0]['data'];
		 		$dpdata=$jsondata['result'][0]['dapandata'];
		 		$dpimg=$jsondata['result'][0]['gopicture'];
	 		}	 		
	 	}
	 	include $this->template('show');
	 }
	 
	 public function doMobileIndex(){
	 	global $_W,$_GPC; 
		header('Pragma: no-cache'); 
		$userid=$_GPC['userid'];		
	 	include $this->template('index');
	 }
	 
	 public function doMobileOptional(){
	 	global $_W,$_GPC;   
		header('Pragma: no-cache');   
	 	if(!empty($_GPC['opt'])&&$_GPC['opt']=='add'){
	 		$data=array(
	 			'weid'=>$_W['weid'],
	 			'userid'=>$_GPC['userid'],
	 			'stkcode'=>substr($_GPC['stkcode'],2),
	 			'imgname'=>$_GPC['stkcode'],
	 			'stkname'=>$_GPC['stkname'],
	 			'stkprice'=>$_GPC['stkprice'],
	 			'lastprice'=>$_GPC['lastprice'],
	 		);
	 		pdo_delete('optionalstock', array('weid'=>$_W['weid'],'userid'=>$_GPC['userid'],'stkcode'=>substr($_GPC['stkcode'],2)));
	 		if(pdo_insert('optionalstock', $data)){
	 			echo '添加成功!';
	 		}
	 	}else if(!empty($_GPC['opt'])&&$_GPC['opt']=='remove'){
	 		if(pdo_delete('optionalstock', array('weid'=>$_W['weid'],'userid'=>$_GPC['userid'],'stkcode'=>substr($_GPC['stkcode'],2)))){
	 			echo '已移除!';
			}
	 	}
	 }	 
}