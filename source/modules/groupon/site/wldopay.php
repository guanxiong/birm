<?php
/**
 * 保存订单
 *
 * @author 超级无聊
 * @url
 */		
	$tid=intval($_GPC['tid']);
	$num=intval($_GPC['num']);
	if($num<1){
		message('购买产品数量不能为0');
	}else{
		//判断产品是否存在
		$condition= " AND starttime<".time()." AND endtime>".time()." ";
		$row = pdo_fetch("SELECT price,stock,limit_num,email FROM ".tablename('groupon_list')." WHERE id={$tid} AND weid = '{$_W['weid']}' AND status = '1' $condition ORDER BY listorder DESC, id DESC");
		if($row==false){
			message('团购产品状态异常，无法参与团购。');
		}
		//比较库存
		if($num>$row['stock']){
			message('团购产品库存不足，无法参与团购。');
		}
		if($num>$row['limit_num'] && $row['limit_num']!=0){
			message('每个用户只能购买'.$row['limit_num'].'个产品。');
		}
		//保存订单
		$insert=array(
			'weid'=>$weid,
			'tid'=>$tid,
			'from_user'=>$from,
			'ordersn' => date('md') .sprintf("%04d", $_W['fans']['id']) .random(4, 1),
			'totalnum' => $num,
			'totalprice' => intval($num)*floatval($row['price']),
			'status' => 0,
			'sendtype' => 0,
			'paytype' => $_GPC['paytype'],
			'createtime' => TIMESTAMP,
		);
		if($_GPC['paytype']==3){
			$insert['secretsn']='A'.random(11,1);
			$insert['status']=1;
		}
		$temp=pdo_insert('groupon_order', $insert);
		if($temp==false){
			$this->message('订单失败，请稍候重试');
		}else{
			//0.52版本可以开启发邮件功能
			if(!empty($row['email'])){
				ihttp_email($row['email'],'您有新订单','您的产品'.$row['title'].'团购了'.$num.'份，还剩下'.$row['stock'].'份');
				}
			$orderid = pdo_insertid();	
			if($_GPC['paytype']==3){
				//选择了货到付款
				//跳转到详情页
				//$this->message('订单成功，您选择了货到付款，请初始消费券后，付款消费。',$this->createMobileUrl('wleshow', array('tid'=>$tid,'id' => $orderid)),0);
				$this->message('订单成功，您选择了货到付款，请初始消费券后，付款消费。',$this->createMobileUrl('wleticket'),0);
				
			}else{
				$this->message('订单成功',$this->createMobileUrl('wlpayment', array('orderid' => $orderid)),0);
			}
		}
	}
	
	