<?php
/**
 * 微团购模块微站定义
 *
 * @author 
 * @url 
 */
 if($subcp=="wapused"){
		$where=" AND a.id={$_GPC['goods']}  ";
		//$where.="AND a.secretsn='{$_GPC['sn']}'  ";
	    $where.="AND a.password='{$_GPC['password']}'  ";
	//	echo  "SELECT a.*  FROM ".tablename('groupon_list')."  as a WHERE  a.weid='".$_W['weid']."'".$where." ORDER BY a.id DESC ";
	//	$row = pdo_fetch("SELECT a.*  FROM ".tablename('groupon_order')."  as a WHERE  a.weid='".$_W['weid']."'".$where." ORDER BY a.id DESC ");
		
		$row = pdo_fetch( "SELECT password  FROM ".tablename('groupon_list')."  as a WHERE  a.weid='".$_W['weid']."'".$where." ORDER BY a.id DESC ");
		
		if($row==false){
			$data=array(
				'errno'=>7,
				'error'=>'商家密码错误',
			);	
           	die(json_encode($data));			
		}
		$where=" AND a.tid={$_GPC['goods']}  ";
		$where.="AND a.secretsn='{$_GPC['sn']}'  ";
		$row = pdo_fetch("SELECT a.*  FROM ".tablename('groupon_order')."  as a WHERE  a.weid='".$_W['weid']."'".$where." ORDER BY a.id DESC ");
		
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
	
	} else
	{
	
	$data=array(
						'errno'=>0,
						'error'=>'成功使用团购券',
					);	
					die(json_encode($data));
	}