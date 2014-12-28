<?php
/**
 * 微团购模块微站定义
 *
 * @author 
 * @url 
 */
	if($subcp=="check"){
	//weid:1 sn:1234 step:1 goods:1
		$where="AND a.tid={$_GPC['goods']}  ";
		$where.="AND a.secretsn='{$_GPC['sn']}'  ";
		//$where="AND a.weid={$_GPC['weid']}}  ";
		$row = pdo_fetch("SELECT a.*,b.mobile,c.title FROM ".tablename('groupon_order')."  as a left join  ".tablename('groupon_fans')." as b on a.from_user=b.from_user 
		left join   ".tablename('groupon_list')." as c on a.tid=c.id 
		WHERE  a.weid=:weid  ".$where." ORDER BY a.id DESC ".$limit , array(':weid'=>$_W['weid']));	
		if($row==false){
			//团购券不存在
			$data=array(
			'testing'=>-2,
			);
		}else{
			//已经使用过了
			if($row['used']==2){
				$data=array(
				'testing'=>1,
				'use'=>1,
				);
			}else{
				$data=array(
					'testing'=>1,
					'use'=>0,
					'sn_detail'=>array(
						'b'=>$row['mobile'],
						'c'=>$row['totalprice'],
						'd'=>$row['ordersn'],
						'e'=>$row['title'],
						),
				);
			}
		}
		//{"testing":1,"use":1,"sn_detail":{"b":"13813874744","c":"0.01","d":"1410827990027591415","e":"\u6d4b\u8bd5\u56e2\u8d2d"}}
		//{"testing":1,"use":0,"sn_detail":{"b":"13813874744","c":"0.01","d":"1410827990027591415","e":"\u6d4b\u8bd5\u56e2\u8d2d"}}
		die(json_encode($data));
	}elseif($subcp=="used"){
		$where=" AND a.tid={$_GPC['goods']}  ";
		$where.="AND a.secretsn='{$_GPC['sn']}'  ";
		//$where="AND a.weid={$_GPC['weid']}}  ";
		
		//$row = pdo_fetch("SELECT a.*  FROM ".tablename('groupon_order')."  as a WHERE  a.weid=:weid  ".$where." ORDER BY a.id DESC ".$limit , //array(':weid'=>$_W['weid']));	
		//echo "SELECT a.*  FROM ".tablename('groupon_order')."  as a WHERE  a.weid='".$_W['weid']."'".$where." ORDER BY a.id DESC ".$limit;
		$row = pdo_fetch("SELECT a.*  FROM ".tablename('groupon_order')."  as a WHERE  a.weid='".$_W['weid']."'".$where." ORDER BY a.id DESC ".$limit);
		
		if($row==false){
			$data=array(
				'errno'=>1,
				'error'=>'团购券不存在',
			);		
		}else{
			if($row['used']==2){
				$data=array(
					'errno'=>1,
					'error'=>'团购券已经使用',
				);	
			}else{
			
				$temp=pdo_update('groupon_order',array('used'=>2,'usedtime'=>time()),array('id'=>$row['id']));
				if($temp==false){
					$data=array(
						'errno'=>1,
						'error'=>'网络通信不稳定，稍后再试',
					);					
				}else{
					$data=array(
						'errno'=>0,
						'error'=>'成功使用团购券',
					);	
				}
			}
		}

		die(json_encode($data));		
	}else if($subcp=="wapused"){
		$where=" AND a.tid={$_GPC['goods']}  ";
		$where.="AND a.secretsn='{$_GPC['sn']}'  ";
	    $where.="AND a.password='{$_GPC['password']}'  ";
		$row = pdo_fetch("SELECT a.*  FROM ".tablename('groupon_order')."  as a WHERE  a.weid='".$_W['weid']."'".$where." ORDER BY a.id DESC ".$limit);
		
		if($row==false){
			$data=array(
				'errno'=>1,
				'error'=>'团购券不存在',
			);		
		}else{
			if($row['used']==2){
				$data=array(
					'errno'=>1,
					'error'=>'团购券已经使用',
				);	
			}else{
			
				$temp=pdo_update('groupon_order',array('used'=>2,'usedtime'=>time()),array('id'=>$row['id']));
				if($temp==false){
					$data=array(
						'errno'=>1,
						'error'=>'网络通信不稳定，稍后再试',
					);					
				}else{
					$data=array(
						'errno'=>0,
						'error'=>'成功使用团购券',
					);	
				}
			}
		}

		die(json_encode($data));
	
	} else {
		//列出所有使用过的
		$where='AND a.ispay=1 and a.used=2 ';
		
		//暂时没考虑过期的情况

		
		
		$total=pdo_fetchcolumn ("SELECT count(a.id) FROM ".tablename('groupon_order')."  as a left join ".tablename('groupon_fans')." as b on a.from_user=b.from_user WHERE  a.weid=:weid ".$where."", array(':weid'=>$_W['weid']));		
		$pindex = max(1, intval($_GPC['page']));
		$psize = 12;		
		$pager = pagination($total, $pindex, $psize);		
		$start = ($pindex - 1) * $psize;
		$limit .= " LIMIT {$start},{$psize}";
		$list = pdo_fetchall("SELECT a.*,b.mobile,c.title FROM ".tablename('groupon_order')."  as a left join  ".tablename('groupon_fans')." as b on a.from_user=b.from_user 
		left join   ".tablename('groupon_list')." as c on a.tid=c.id 
		WHERE  a.weid=:weid  ".$where." ORDER BY a.id DESC ".$limit , array(':weid'=>$_W['weid']));				
		

		//获取团购商品
		$goodslist = pdo_fetchall("SELECT id,title FROM ".tablename('groupon_list')." WHERE  weid=:weid  ", array(':weid'=>$_W['weid']));	
		
		include $this->template('verification');			
	}