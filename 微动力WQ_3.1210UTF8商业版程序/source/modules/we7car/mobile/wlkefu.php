<?php
/**
 * 品牌管理
 *
 * @author 超级无聊
 * @url 
 */
	$list1 = pdo_fetchall("SELECT * FROM ".tablename('weicar_kefu')." WHERE weid = ".$weid."  and pre_sales=1 order by listorder desc");
	$list2 = pdo_fetchall("SELECT * FROM ".tablename('weicar_kefu')." WHERE weid = ".$weid."  and aft_sales=1 order by listorder desc");
 		
 	include $this->template('kefu_index');	