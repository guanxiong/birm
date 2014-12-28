<?php
/**
 * 品牌管理
 *
 * @author 超级无聊
 * @url 
 */
 	$tools = pdo_fetchcolumn  ("SELECT tools FROM ".tablename('weicar_set')." WHERE  weid=:weid  " , array(':weid'=>$_W['weid']));				
	if($tools===false){
		$toolsArr=array(
			'1'=>true,
			'2'=>true,
			'3'=>true,
			'4'=>true,
			'5'=>true,
		);
	}else{
		$toolsArr=array();
		$tools=explode(",",$tools);
		foreach($tools as $v){
			$toolsArr[$v]=true;
		}
	}
	include $this->template('tools_index');
