<?php
/**
 * 首页
 *
 * @author 微新星
 * @url
 */
	$tid = intval($_GPC['tid']);
	$id = intval($_GPC['id']);
	
	$groupon = pdo_fetch("SELECT * FROM ".tablename('groupon_list')." WHERE id={$tid} AND weid = '{$_W['weid']}' AND status = '1' ");

 	if (empty($groupon)) {
		message('抱歉，商品不存在或是已经被删除！');
	}
	
	$order = pdo_fetch("SELECT * FROM ".tablename('groupon_order')." WHERE id={$id} AND weid = '{$_W['weid']}'  ");
 	if (empty($order)) {
		message('抱歉，商品不存在或是已经被删除！');
	}
	
	if($groupon['valid_starttime']>TIMESTAMP){
		$order['tip1']="未开始";
		$order['tip2']=date('Y-m-d H:i:s',$groupon['valid_starttime'])."可以使用";
	}elseif ($groupon['valid_endtime']>TIMESTAMP){
		$rangtime=$groupon['valid_endtime']-TIMESTAMP; //开始与结束之间相差多少秒 
		$time = $rangtime/(86400);
		if($time==0){
			$time = $rangtime/3600;
			if($time==0){
				$order['tip1']="不足1小时";
			}else{
				$order['tip1']="剩余<br/><h>".ceil($time)."</h>小时";
			}
		}else{
			$order['tip1']="剩余<br/><y>".ceil($time)."</y>天";
		}
		$order['tip2']=date('Y-m-d H:i:s',$groupon['valid_endtime'])."团购券过期";
	}elseif ($groupon['valid_endtime']< TIMESTAMP){
		$order['tip1']="团购券已过期";
		$order['tip2']="团购券已过期";
	}
 	include $this->template('wl_eshow');