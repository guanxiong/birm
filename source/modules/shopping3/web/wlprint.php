<?php
/**
 * 2014-2-24
 * 购物车 分类管理 
 * @author 微动力
 * @url
 */
	header("Content-type:text/html;charset=utf-8");
	$usr=!empty($_GET['usr'])?$_GET['usr']:'355839022928495';
	$ord=!empty($_GET['ord'])?$_GET['ord']:'no';
	$sgn=!empty($_GET['sgn'])?$_GET['sgn']:'no';
	if(isset($_GET['sta'])){
		$id=intval($_GPC['id']);
		$sta=intval($_GPC['sta']);	
		pdo_update('shopping3_order',array('print_sta'=>$sta),array('id'=>$id));
		exit;
	}

	//获取订单
	$set = pdo_fetch("SELECT * FROM ".tablename('shopping3_set')." WHERE weid = :weid limit 1", array(':weid' => $weid));
	if($set==false){
		exit;
	}
 
	$weid=$set['weid'];
	$item = pdo_fetch("SELECT * FROM ".tablename('shopping3_order')." WHERE weid = :weid AND print_sta=-1  limit 1", array(':weid' => $weid));
 	//没有新订单
	if($item==false){	
		exit;
	}
	$address=pdo_fetch("SELECT * FROM ".tablename('shopping3_address')." WHERE id = :id", array(':id' => $item['aid']));
 
	$goodsid = pdo_fetchall("SELECT goodsid, total FROM ".tablename('shopping3_order_goods')." WHERE orderid = '{$item['id']}'", array(), 'goodsid');
	
	$goods = pdo_fetchall("SELECT * FROM ".tablename('shopping3_goods')."  WHERE id IN ('".implode("','", array_keys($goodsid))."')");

	//$express = pdo_fetchall("SELECT id,express_name FROM ".tablename('shopping3_express')." WHERE weid = '{$_W['weid']}'",array(),'id');
	//$express['0']=array('express_name'=>'未选择');
	$item['goods'] = $goods;

 	if(!empty($set['print_top'])){
		$content="%10".$set['print_top']."\n";
	}else{
		$content='';
	}
	$content.='%00单号:'.$item['ordersn']."\n";
	//$content.='总数:'.$item['totalnum'].'  总价:'.$item['totalprice']."\n";
	///$content.='配送方式:'.$express[$item['sendtype']]['express_name']."\n";
	if($item['ispay']==1){
		$item['paystr']='[已支付]';
	}else{
		$item['paystr']='[未支付]';
	}
	//	if ($item['paytype'] == 1){
	//		$content.='付款:余额支付'.$item['paystr']."\n";
	//	}elseif ($item['paytype'] == 2){
	//		$content.='付款:在线支付'.$item['paystr']."\n";
	//	}elseif ($item['paytype'] == 3){
	//		$content.='付款:货到付款'.$item['paystr']."\n";
	//	}
	//$content.='下单日期:'.date('Y-m-d H:i:s', $item['createtime'])."\n";
	//$content.='预约时间:'.$item['time_day'].'日'.$item['time_hour'].'时'.$item['time_second']."分\n";
	//if(!empty($item['remark'])){
	//	$content.='备注:'.$item['remark']."\n";
	//}

	//$content.="%00\n名称              数量  单价 \n";
	//$content.="----------------------------\n";

	//$content=iconv("UTF-8","GB2312//IGNORE",$content);
	//foreach($item['goods'] as $v){
	//	$content1.=$this->_formatstr($v['title'],16).$this->_formatstr($goodsid[$v['id']]['total'],4,false).$this->_formatstr(number_format($v['marketprice'],1),7,false)."\n";
	//}
	//$content2="----------------------------\n";
	//$content2.="%10总数量:".$item['totalnum']."   总价:".number_format($item['totalprice'],1)."元\n%00";
	//if(!empty($item['guest_name'])){
	//	$content2.='姓名:'.$item['guest_name']."\n";
	//}
	//if(!empty($item['tel'])){
	//	$content2.='手机:'.$item['tel']."\n";
	//}
	//if(!empty($item['guest_address'])){
	//	$content2.='地址:'.$item['guest_address']."\n";
	//}
	//if(!empty($item['desk'])){
	//	$content2.='桌号:'.$item['desk']."\n";
	//}
	//if(!empty($set['print_bottom'])){
	//	$content2.="%10".$set['print_bottom']."\n%00";
	//}
	//$content2=iconv("UTF-8","GB2312//IGNORE",$content2);
 
	//$setting='<setting>124:'.$set['print_nums'].'|134:0</setting>';					
	//$setting=iconv("UTF-8","GB2312//IGNORE",$setting);
	/*echo '<?xml version="1.0" encoding="GBK"?><r><id>'.$item['id'].'</id><time>'.date('Y-m-d H:i:s', $item['createtime']).'</time><content>'.$content.$content1.$content2.'</content>'.$setting.'</r>';
	*/
	//订单号：	{$item['ordersn']}
	//价钱		{$item['totalprice']} 
	//配送方式 	{php echo $express[$item['sendtype']]['express_name']} 
	/*付款方式
		{if $item['paytype'] == 1}余额支付{/if}
		{if $item['paytype'] == 2}在线支付{/if}
		{if $item['paytype'] == 3}货到付款{/if}
	*/
	/*订单状态
		{if $item['status'] == 0}未确认{/if}
		{if $item['status'] == 1}已确认{/if}
		{if $item['status'] == 2}已付款{/if}
		{if $item['status'] == 3}已完成{/if}
		{if $item['status'] == -1}已关闭{/if}
	*/	
	//下单日期 {php echo date('Y-m-d H:i:s', $item['createtime'])}
	//备注     {$item['remark']}
	/*用户信息
	姓名 {$address['username']}
	手机 {$address['tel']}
	地址 {$address['address']}
	*/
	/*商品信息
			<table class="table table-hover">
			<thead class="navbar-inner">
				<tr>
					<th style="width:30px;">ID</th>
					<th style="min-width:150px;">商品标题</th>
					<th style="width:100px;">商品编号</th>
					<th style="width:100px;">商品条码</th>
					<th style="width:100px;">销售价/成本价</th>
					<th style="width:100px;">属性</th>
					<th style="width:100px;">数量</th>
					<th style="text-align:right; min-width:60px;">操作</th>
				</tr>
			</thead>
			{loop $item['goods'] $row}
			<tr>
				<td>{$row['id']}</td>
				<td>{if $category[$row['pcate']]['name']}<span class="text-error">[{$category[$row['pcate']]['name']}] </span>{/if}{if $children[$row['pcate']][$row['ccate']][1]}<span class="text-info">[{$children[$row['pcate']][$row['ccate']][1]}] </span>{/if}{$row['title']}</td>
				<td>{$row['goodssn']}</td>
				<td>{$row['productsn']}</td>
				<!--td>{$category[$row['pcate']]['name']} - {$children[$row['pcate']][$row['ccate']][1]}</td-->
				<td style="background:#f2dede;">{$row['marketprice']}元 / {$row['productprice']}元</td>
				<td>{if $row['status']}<span class="label label-success">上架</span>{else}<span class="label label-error">下架</span>{/if}&nbsp;<span class="label label-info">{if $row['type'] == 1}实体商品{else}虚拟商品{/if}</span></td>
				<td>{$goodsid[$row['id']]['total']}</td>
				<td style="text-align:right;">
					<a href="{php echo $this->createWebUrl('goods', array('id' => $row['id'], 'op' => 'post'))}">编辑</a>&nbsp;&nbsp;<a href="{php echo $this->createWebUrl('goods', array('id' => $row['id'], 'op' => 'delete'))}" onclick="return confirm('此操作不可恢复，确认删除？');return false;">删除</a>
				</td>
			</tr>
			{/loop}
		</table>
	*/	
	
	//微动力打印开始
	if($set['print_status']){
		$msg = formatMsg($item,$set);
		if($set['print_nums']){
			for($i=1;$i<=$set['print_nums'];$i++){
				$res = sendMsgToElink($msg, $set['apiKey'], $set['mKey'], $set['partnerId'], $set['machineCode']);
			}
		}
		echo $msg;
	}
	
	
	function httppost1($params) {
		$host = '42.121.115.77';
		$port = '8888';
		//需要提交的post数据
		$p = '';
		foreach ($params as $k => $v) {
			$p .= $k.'='.$v.'&';
		}
		$p = rtrim($p, '&');
		$header = "POST / HTTP/1.1\r\n";
		$header .= "Host:$host:$port\r\n";
		$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$header .= "Content-Length: " . strlen($p) . "\r\n";
		$header .= "Connection: Close\r\n\r\n";
		$header .= $p;
		$fp = fsockopen($host, $port);
		fputs($fp, $header);
		while (!feof($fp)) {
			$str = fgets($fp);
		}
		fclose($fp);
		return $str;
	}
	
	//向微动力提交信息 返回微动力返回标志
	function sendMsgToElink($msg,$apiKey,$mKey,$partner,$machine_code){
	
		$params = array(
				'partner'=>$partner,
				'machine_code'=>$machine_code,
				'content'=>$msg,
		);
	
		$sign = generateSign($params,$apiKey,$mKey);
		$params['sign'] = $sign;
	
		$return = httppost1($params);
		return $return;
	}
	
	//微动力签名算法
	function generateSign($params, $apiKey, $msign)
	{
	
		//所有请求参数按照字母先后顺序排序
		ksort($params);
		//定义字符串开始 结尾所包括的字符串
		$stringToBeSigned = $apiKey;
		//把所有参数名和参数值串在一起
		foreach ($params as $k => $v)
		{
			$stringToBeSigned .= urldecode($k.$v);
		}
		unset($k, $v);
		//把venderKey夹在字符串的两端
		$stringToBeSigned .= $msign;
		//使用MD5进行加密，再转化成大写
		return strtoupper(md5($stringToBeSigned));
	}
	
function formatMsg($result,$set){
	$msg = '';
	if($set['print_top']){
		$msg .= $set['print_top']."\r\n";
	}
	$msg .= "-----------------------------\r\n";
	$msg .= "订单编号：".$result['ordersn']."\r\n";
	switch ($result['order_type']){
		case '2':
			$msg .= '姓名：'.$result['guest_name']."\r\n";
			$msg .= '电话：'.$result['tel']."\r\n";
			$msg .= "就餐方式：预订\r\n";
			$msg .= '预订日期：'.date('Y-m-d H:i:s',$result['reservetime'])."\r\n";
			$msg .= '预订餐桌：'.$result['tablename']."\r\n";
			break;
		case '1':
			$msg .= '预订人：'.$result['guest_name']."\r\n";
			$msg .= '联系电话：'.$result['tel']."\r\n";
			$msg .= '联系地址：'.$result['guest_address']."\r\n";
			$msg .= "就餐方式：外卖\r\n";
			$msg .= '下单日期：'.date('Y-m-d H:i:s',$result['createtime'])."\r\n";
			break;
		default:;
	}
	$msg .= "-----------------------------\r\n";
	$msg .= "名称\t\t\t\t单价\t\t数量\t\t总价\r\n";
	foreach ($result['goods'] as $k=>$v){
		$c_name  = $v['title'] . str_repeat(' ', (7-strlen($v['title'])/3)*2);
		$c_price = str_pad($v['marketprice'], 5, " ", STR_PAD_RIGHT);
		$c_num = str_pad($v['sellnums'], 5, " ", STR_PAD_RIGHT);
		$msg .= $c_name.$c_price."\t".$c_num."\t".$v['marketprice']*$v['sellnums']."\r\n";
	}
	$msg .="-----------------------------\r\n";
	$msg .='总计：'.$result['totalprice'].'元'."\r\n";
	$msg .= '备注：'.$result['remark']."\r\n";

	if($set['print_bottom']){
		$msg .= chr(10).$set['print_bottom'];
	}
	return $msg;
}