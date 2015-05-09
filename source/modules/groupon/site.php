<?php
/**
 * 微团购模块微站定义
 *
 * @author 
 * @url 
 */
defined('IN_IA') or exit('Access Denied');
define('RES','./source/modules/groupon/style/');
class GrouponModuleSite extends WeModuleSite {
	public function __comm($f_name){
		global $_W,$_GPC;
		$this->checkAuth();
		$weid=isset($_W['weid'])?$_W['weid']:$_GPC['weid'];
		$from=isset($_W['fans']['from_user'])?$_W['fans']['from_user']:$_GPC['openid'];
		$subcp = $_GPC['subcp']?$_GPC['subcp']:NULL;

		include_once  'site/'.(substr($f_name,8)).'.php';
	}
	//团购首页
	public function doMobilewlindex(){	
		$this->__comm(__FUNCTION__);
	}
	//商品详细
	public function doMobilewldetail(){	
		$this->__comm(__FUNCTION__);
	}	
	
	public function doMobilewleticket(){	
		$this->__comm(__FUNCTION__);
	}	
	//团购券详情
	public function doMobilewleshow(){	
		$this->__comm(__FUNCTION__);
	}		
	 
	public function doMobilewlorder(){	
		$this->__comm(__FUNCTION__);
	}
	public function doMobilewlcheck(){	
		$this->__comm(__FUNCTION__);
	}	
	public function doMobilewlconfirm(){	
		$this->__comm(__FUNCTION__);
	}		 
	public function doMobilewlsetuser(){	
		$this->__comm(__FUNCTION__);
	}		  
	public function doMobilewlphonecode(){	
		$this->__comm(__FUNCTION__);
	}	
	//绑定+验证
	public function doMobilewlbindsubmit(){	
		$this->__comm(__FUNCTION__);
	}	
	//确认订单
	public function doMobilewldopay(){	
		$this->__comm(__FUNCTION__);
	}	
	public function doMobilewlpayment(){	
		$this->__comm(__FUNCTION__);
	}		
	public function doMobilewlorderdetail(){	
		$this->__comm(__FUNCTION__);
	}	
	
	public function doMobilewlverification(){	
		$this->__comm(__FUNCTION__);
	}	

	
	 
	private function checkAuth() {
		global $_W;
		/*if (empty($_W['fans']['from_user'])) {
			message('非法访问，请重新点击链接进入个人中心！');
		}*/
	}
	public function payResult($params) {
	
		$order=pdo_fetch("SELECT status,totalprice FROM ".tablename('groupon_order')." WHERE id = {$params['tid']}");
		$temp=pdo_update('groupon_order', array('status' => 2,'used'=>1,'ispay'=>1,'paytime'=>time(),'secretsn'=>random(12,1)), array('id' => $params['tid'],'status'=>0));
		//if($temp!=false){
			$this->_inventory($params['tid']);
		//}
		if ($params['from'] == 'return') {
			//更新库存
		
			//保存用户数据
			pdo_query('update '.tablename('groupon_fans').' set totalnum=totalnum+1,totalprice=totalprice+'.$order['totalprice'].',last_time='.time().'');
			include $this->template('wl_payresult');			
			//message('支付成功！', '../../' . $this->createMobileUrl('wleticket'), 'success');
		}
	}	
	//更新库存
	public function _inventory($_oid,$_do='reduce'){
		//订单
 		$_order = pdo_fetch("SELECT tid,totalnum FROM ".tablename('groupon_order')." WHERE id = {$_oid}");
		//产品
 		$_goods=pdo_fetch("SELECT stock,sell_nums FROM ".tablename('groupon_list')." WHERE id = {$_order['tid']}");

		if($_goods['stock']<0){
			pdo_update('groupon_list',array('sell_nums'=>$_goods['sell_nums']+$_order['totalnum']),array('id'=>$_goods['goodsid']));
		}else{
			pdo_update('groupon_list',array('sell_nums'=>($_goods['sell_nums']+$_order['totalnum']),'stock'=>($_goods['stock']-$_order['totalnum'])),array('id'=>$_order['tid']));
		}
	}	
	//检查库存
	public function _checkstock($_oid){
		$return =true;
 		$_order = pdo_fetchall("SELECT * FROM ".tablename('groupon_order')." WHERE id = {$_oid}");
		//产品
 		$_goods=pdo_fetch("SELECT title,total,sellnums FROM ".tablename('groupon_list')." WHERE id = {$_order['tid']}");
			if($_order['totalnum']>$_goods['stock']){
				//更改订单,删除订单
				pdo_delete('groupon_order',array('id'=>$_oid));
 				message($_goods['title'].'的库存不足，目前仅有'.$_goods['total'].'件,订单取消，请联系客服。',$this->createMobileUrl('wlmember',array('weid'=>$_GET['weid'])),'error');				
 				$return =false;
			}
		return true;
	}	
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