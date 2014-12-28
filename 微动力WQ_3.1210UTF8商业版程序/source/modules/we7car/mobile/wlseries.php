<?php
/**
 * 品牌管理
 *
 * @author 超级无聊
 * @url 
 */
 //因为只考虑一个品牌
 $car_logo = pdo_fetchcolumn("SELECT logo FROM ".tablename('weicar_brand')." WHERE weid = ".$weid."  order by listorder desc");
if($op=='car'){
	
	if(count($list)<2){
		$tlist=pdo_fetchall("SELECT * FROM ".tablename('weicar_series')." WHERE weid = ".$weid."  order by listorder desc");		
	}else{
		$tlist=$brand;
	}
			
	$tid=empty($_GPC['tid'])?die('err'):$_GPC['tid'];	
	$car = pdo_fetch("SELECT * FROM ".tablename('weicar_type')." WHERE weid = ".$weid." and id=".$tid."");
	if(!empty($car['thumbArr'])){
		$car['thumb_url']=explode('|',$car['thumbArr']);	
	}	
 	
	
	
	//获取logo
 	include $this->template('series_car');	
}elseif($op=='type'){
	$sid=empty($_GPC['sid'])?die('err'):$_GPC['sid'];	
		

	$Series=pdo_fetch("SELECT thumb FROM ".tablename('weicar_series')." WHERE weid = ".$weid." and id=".$sid." ");		

	$list = pdo_fetchall("SELECT * FROM ".tablename('weicar_type')." WHERE weid = ".$weid." and sid=".$sid." order by listorder desc");
	include $this->template('series_type');	

}elseif($op=='list' || $op=='series'){
	if($_GPC['bid']){
		$list = pdo_fetchall("SELECT * FROM ".tablename('weicar_series')." WHERE weid = ".$weid." and bid=".$_GPC['bid']." order by listorder desc");		
	}else{
		//如果bid为空，则代表只有一个品牌
		$list = pdo_fetchall("SELECT * FROM ".tablename('weicar_series')." WHERE weid = ".$weid."  order by listorder desc");		
	}
 	
	include $this->template('series_series');	
}