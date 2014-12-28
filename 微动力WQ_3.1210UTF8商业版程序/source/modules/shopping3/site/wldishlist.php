<?php
/**
 * 购物车
 *
 * @author 微动力
 * @url
 */
 
 if($subcp=='cart'){
	$cart = pdo_fetchall("SELECT * FROM ".tablename('shopping3_cart')." WHERE  weid = '{$weid}' AND from_user = '{$_W['fans']['from_user']}'", array(), 'goodsid');
	$otalprice=0;
	$return=array();
	if (!empty($cart)) {
		$goods = pdo_fetchall("SELECT id, title, thumb, marketprice,productprice, unit, total FROM ".tablename('shopping3_goods')." WHERE id IN ('".implode("','", array_keys($cart))."')");
		if (!empty($goods)) {
			foreach ($goods as $row) {
				if (empty($cart[$row['id']]['total'])) {
					continue;
				}
	/* 			    {
        "dishes_id": "188",
        "description": null,
        "name": "蒜泥香油碟",
        "pic": "http://img.weimob.com/static/da/5a/77/image/20131101/20131101213612_51841.jpg",
        "price": "5.00",
        "selected_count": "1",
        "discount_price": ""
    } */		
				$return[]=array(
					'dishes_id'=>$row['id'],
					'description'=>$row['description'],
					'name'=>$row['title'],
					'pic'=>$row['thumb'],
					'price'=>empty($row['marketprice'])?$row['productprice']:$row['marketprice'],
					'selected_count'=>intval($cart[$row['id']]['total']),
					'discount_price'=>$row['marketprice'],
				);
				//购物车不考虑库存
				/* if ($row['total'] != -1 && $row['total'] < $cart[$row['id']]['total']) {
					message('抱歉，“'.$row['title'].'”此商品库存不足！', $this->createMobileUrl('wlcart'), 'error');
				} */
				//$price += (floatval($row['marketprice']) * intval($cart[$row['id']]['total']));
			}
		}
	}
	
	echo json_encode($return);
	exit;
 }elseif ($subcp=='clear'){
	//清空购物车
	pdo_delete('shopping3_cart', array('weid' => $weid, 'from_user' => $from));
	echo '1';
  }elseif ($subcp=='mylike'){
  
  
  }else{
	$curcategory = pdo_fetchall("SELECT * FROM ".tablename('shopping3_category')." WHERE weid = '{$weid}'  and parentid=0 order  BY displayorder DESC");
	$likearr= pdo_fetchall("SELECT goodsid,checked FROM ".tablename('shopping3_fans_like')." WHERE   weid ={$weid}  and from_user='{$from}' ",array(),'goodsid');
 	
	
	$cate=array();
	$return=array();
 	foreach($curcategory as $k=>$v){
		$cate=array(
		"id"=>$v['id'],
        "weid"=>$v['weid'],
        "name"=>$v['name'],
        "sort"=>$v['displayorder'],
        "flag"=>"0",
		);
		$goods = pdo_fetchall("SELECT * FROM ".tablename('shopping3_goods')." WHERE weid = '{$weid}'  and pcate=".$v['id']."");
		foreach($goods  as $k2=>$v2){
			$cate['dishes'][]=array(
				"id"=>$v2['id'],
				"weid"=>$v['weid'],
                "name"=>$v2['title'],
                "price"=>empty($v2['marketprice'])?$v2['productprice']:$v2['marketprice'],
                "discount_name"=>$v2['title'],
                "discount_price"=>empty($v2['marketprice'])?$v2['productprice']:$v2['marketprice'],
                "class_id"=>$v2['pcate'],
                "tag_id"=>"0",
                "pic"=>$v2['thumb'],
                "note"=>$v2['description'],
                "is_show"=>"1",
                "sort"=>"0",
                "ctime"=>"",
                "flag"=>"1",
                "tag_name"=>"",
                "check"=>($likearr[$v2['id']]['checked']==1)?"1":"0",
                "html_name"=>"",
				"unit"=>empty($v2['unit'])?"份":$v2['unit'],
			);
		}
		$return[]=$cate;
 	}
	echo json_encode($return);
}