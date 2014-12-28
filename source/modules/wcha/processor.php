<?php
/**
 * 微餐饮查单模块处理程序
 *
 * @author 超级无聊
 * @url 
 */
defined('IN_IA') or exit('Access Denied');

class WchaModuleProcessor extends WeModuleProcessor {
	public function respond() {
		global $_W;
		$rid=$this->rule;
		$sql = "SELECT * FROM " . tablename('wcha_reply') . " WHERE `rid`=:rid";
		$reply = pdo_fetch($sql,array(':rid'=>$rid));
		if($reply==false){
			return '';
		}
		$openidArr=explode(';',$reply['openidstr']);
 
		if (in_array($this->message['from'],$openidArr)){
			if($reply['wtype']==0){
				//查单
				$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('shopping3_order') . " WHERE weid = '{$_W['weid']}' and createtime>".mktime(0,0,0)." ");
				if($total>0){
					$list = pdo_fetchall("SELECT * FROM ".tablename('shopping3_order')." WHERE weid = '{$_W['weid']}' and createtime>".mktime(0,0,0)."  ORDER BY  id DESC LIMIT 9");
				}
				$news = array();
				$news []=array(
					'title'=>"您今天目前有".$total."条订单",
					'description'=>'',
					'thumb'=>'',
					'url' => $this->buildSiteUrl($this->createMobileUrl('wllist',array('rid'=>$rid,'status'=>1))),
				);
				foreach($list as $v){
					if($v['status']==0){
						$v['statusstr']='未下单';
					}elseif($v['status']==1){
						$v['statusstr']='未确认';
					}elseif($v['status']==2){
						$v['statusstr']='已确认';
					}elseif($v['status']==-1){
						$v['statusstr']='已取消';
					}
					if($v['ispay']==1){
						$v['ispaystr']='已付款';
					}else{
						$v['ispaystr']='未付款';
					}
					$news []=array(
						'title'=>$v['guest_name'].',总价：'.$v['totalprice'].'元 (数量：'.$v['totalnum'].')'.$v['statusstr'].','.$v['ispaystr'].' 订单时间:'.date('Y-m-d H:i:s',$v['createtime']),
						'description'=>'',
						'thumb'=>'',
						'url' => $this->buildSiteUrl($this->createMobileUrl('show',array('rid'=>$rid,'orderid'=>$v['id'],'secretid'=>$v['secretid']))),
					);
				}
				return $this->respNews($news);
			}else{
				//今天多少单子
				$totalnum = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('shopping3_order') . " WHERE weid = '{$_W['weid']}' and createtime>".mktime(0,0,0)." ");
				$totalnum1 = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('shopping3_order') . " WHERE weid = '{$_W['weid']}' and status=1 AND createtime>".mktime(0,0,0)." ");
				$totalnum2 = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('shopping3_order') . " WHERE weid = '{$_W['weid']}' and status=2 AND createtime>".mktime(0,0,0)." ");
				$totalnum3 = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('shopping3_order') . " WHERE weid = '{$_W['weid']}' and status=3 AND createtime>".mktime(0,0,0)." ");
				$totalnum_1 = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('shopping3_order') . " WHERE weid = '{$_W['weid']}' and status=-1 AND createtime>".mktime(0,0,0)." ");
				$return="今日共有".$totalnum."单\n";
				$return.="未确认订单：".$totalnum1."单\n";
				$return.="已确认订单：".$totalnum2."单\n";
				$return.="已完成订单：".$totalnum3."单\n";
				$return.="已取消订单：".$totalnum_1."单\n";
				$return.="其余订单为正式下单\n\n";
				
					
				$totalprice = pdo_fetchcolumn('SELECT sum(totalprice) FROM ' . tablename('shopping3_order') . " WHERE weid = '{$_W['weid']}' and status=3  and createtime>".mktime(0,0,0)." ");
				if($totalprice==false){
					$totalprice=0;
				}
				$return.="今日已完成销售额:\n".$totalprice."元\n";

				
				return $this->respText("营收统计\n".$return);
				
			}
		}else{
			return $this->respText("您没权限,请在后台授权\n您的openid为:\n".$this->message['from']);
		}
		
		//这里定义此模块进行消息处理时的具体过程, 请查看微动力文档来编写你的代码
	}
}