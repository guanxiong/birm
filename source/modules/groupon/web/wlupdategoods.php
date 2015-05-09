<?php
/**
 * 微团购模块微站定义
 *
 * @author 
 * @url 
 */
	
		if ($_GPC['action']=='save') {
			$id= intval($_GPC['id']);

			$insert=array(
				'weid'=>$_W['weid'],
				'title'=>$_GPC['title'],
				'listorder'=>$_GPC['listorder'],
				'warning_stock'=>$_GPC['warning_stock'],
				'price'=>$_GPC['price'],
				'market_price'=>$_GPC['market_price'],
				'stock'=>$_GPC['stock'],
				'limit_num'=>$_GPC['limit_num'],
				'virtual_sales'=>$_GPC['virtual_sales'],
				'summary'=>$_GPC['summary'],
				'info'=>htmlspecialchars_decode($_GPC['info']),
				'tips'=>$_GPC['tips'],
				'random_refund'=>$_GPC['random_refund'],
				'expired_refund'=>$_GPC['expired_refund'],
				'is_view'=>$_GPC['is_view'],
				'store_key'=>$_GPC['store_key'],
				'email'=>$_GPC['email'],
				'create_time'=>time(),
				'password'=>$_GPC['password']
			);
			if(empty($_GPC['random_refund'])){$_GPC['random_refund']=0;}
			if(empty($_GPC['expired_refund'])){$_GPC['expired_refund']=0;}
			/*if(!empty($_GPC['phout_url'][0])){
				$insert['thumb_list']=implode('|',$_GPC['phout_url'][0]);
			}*/
			
			$cur_index = 0;
			if (! empty ( $_GPC ['attachment-new'] )) {
				foreach ( $_GPC ['attachment-new'] as $index => $row ) {
					if (empty ( $row )) {
						continue;
					}
					$hsdata [$index] = array ('description' => $_GPC ['description-new'] [$index], 'attachment' => $_GPC ['attachment-new'] [$index] );
				}
				$cur_index = $index + 1;
			}
			if (! empty ( $_GPC ['attachment'] )) {
				foreach ( $_GPC ['attachment'] as $index => $row ) {
					if (empty ( $row )) {
						continue;
					}
					$hsdata [$cur_index + $index] = array ('description' => $_GPC ['description'] [$index], 'attachment' => $_GPC ['attachment'] [$index] );
				}
			}
			$insert ['thumb_list'] = serialize ( $hsdata );
  			//处理时间
			list($starttime,$endtime)=explode('-',$_GPC['datetime']);
			$insert['starttime']=strtotime($starttime);
			$insert['endtime']=strtotime($endtime);
			
						//处理时间
			list($starttime,$endtime)=explode('-',$_GPC['valid_datetime']);
			$insert['valid_starttime']=strtotime($starttime);
			$insert['valid_endtime']=strtotime($endtime);
			if($id==0){
				$temp = pdo_insert('groupon_list', $insert);
			}else{
				$temp = pdo_update('groupon_list', $insert,array('id'=>$id));
			}
			if($temp==false){
				$this->message('抱歉，刚才添加的数据失败！','', -1);              
			}else{
				$this->message('团购商品添加数据成功！', create_url('site/module', array('do' => 'goods', 'name' => 'groupon')), 0);      
			}	
		}
		if($_GPC['id']>0){
			$groupon = pdo_fetch("SELECT * FROM ".tablename('groupon_list')." WHERE  weid=:weid  AND id={$_GPC['id']}" , array(':weid'=>$_W['weid']));				
		}
		if(empty($groupon)){
			$groupon=array(
				'id'=>0,
				'weid'=>$_W['id'],
				'listorder'=>0,
				'datetime'=>date('Y/m/d H:i').'-'.date('Y/m/d H:i',strtotime('+7 day')),
				'valid_datetime'=>date('Y/m/d H:i').'-'.date('Y/m/d H:i',strtotime('+7 day')),
			);
		}else{
			$groupon['datetime']=date('Y/m/d H:i',$groupon['starttime']).'-'.date('Y/m/d H:i',$groupon['endtime']);
			$groupon['valid_datetime']=date('Y/m/d H:i',$groupon['valid_starttime']).'-'.date('Y/m/d H:i',$groupon['valid_endtime']);
			$hslists = unserialize ( $groupon['thumb_list'] );
		}
		include $this->template('updategoods');			