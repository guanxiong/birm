<?php
/**
 * 智能选餐
 *
 * @author 微新星
 * @url
 */
	if($subcp=='disheslist'){
		include $this->template('wl_disheslist');
	}elseif($subcp=='bynum'){
		//获取菜单
		$list = pdo_fetchall("SELECT * FROM ".tablename('shopping3_genius')." WHERE weid = '{$weid}' AND rens={$_GPC['combo_nums']}  ORDER BY  displayorder DESC");
		foreach($list as $k=>$item){
			$item['dishes']= iunserializer($item['dishes']);
		 
			if(!empty($item['dishes'])){
				$idstr=implode(",",$item['dishes']['id']);
			}
			$goods = pdo_fetchall("SELECT id,weid,title,thumb,marketprice,productprice,total FROM ".tablename('shopping3_goods')." WHERE weid = '{$weid}' AND status=1  $condition ORDER BY  displayorder DESC",array(),'id');
			
			foreach($item['dishes']['id'] as $k1=>$v1){
				$goods[$v1]['total']=$item['dishes']['num'][$k1];
				$item["goods"][]=$goods[$v1];
			}
			unset($item['dishes']);
			$data[]=$item;
		}
		echo json_encode($data);
	}else{
		$rens = pdo_fetchall("SELECT rens FROM ".tablename('shopping3_genius')." WHERE weid = '{$weid}' 	group by  rens order by rens asc");
		$rensArr=array();
		foreach($rens as $v){
			$rensArr[]=$v['rens'];
		}
		include $this->template('wl_genius');
	}
 