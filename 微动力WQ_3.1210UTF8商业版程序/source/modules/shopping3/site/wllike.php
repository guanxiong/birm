<?php
/**
 * 我的订单
 *
 * @author 微动力
 * @url
 */
//print_r($_POST);
$id=$_GPC['id'];
$check=$_GPC['check'];
$like= pdo_fetch("SELECT id FROM ".tablename('shopping3_fans_like')." WHERE goodsid={$id} AND weid = '{$weid}'  and from_user='{$from}' ");

if($like==false){
	pdo_insert('shopping3_fans_like',array('weid'=>$weid,'checked'=>$check,'from_user'=>$from,'goodsid'=>$id,'create_time'=>time()));
}else{
	pdo_update('shopping3_fans_like',array('checked'=>$check),array('id'=>$like['id']));
}
echo '{"status":"1","msg":"","check":"'.$check.'"}';