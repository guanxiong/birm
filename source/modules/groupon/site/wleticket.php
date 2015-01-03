<?php
/**
 * 首页
 *
 * @author 超级无聊
 * @url
 */
 	$type=$_GPC['type'];
	
 
	$pindex = max(1, intval($_GPC['page']));
	$psize = 10;
	$condition='';
	$condition="AND  a.from_user='{$from}'";
	if($type=='used'){
		$condition.=" AND  a.used =2 ";
	}elseif($type=='expired'){
		$condition.=" AND b.valid_endtime<".time()." AND a.used=1";
	}else{
		$condition.=" AND ( a.used=1 or (a.used=0  AND a.status=1) )";		
	}
	$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('groupon_order') . " as a 
	left join ".tablename('groupon_list')." as b on a.tid=b.id
	WHERE a.weid = '{$weid}' $condition");
 
	$list = pdo_fetchall("SELECT a.*,b.title,b.price,b.thumb_list,b.valid_endtime,valid_starttime FROM ".tablename('groupon_order')." as a 
	left join ".tablename('groupon_list')." as b on a.tid=b.id
	WHERE a.weid = '{$weid}'  $condition ORDER BY a.createtime DESC, a.id DESC LIMIT ".($pindex - 1) * $psize.','.$psize);
 
	$pager='';
	if($pindex==1){
		$pager.="<font class='page_noclick'><<上一页</font>&nbsp;&nbsp;";
	}else{
		$_GET['page'] = $pindex-1;
		$pager.="<a href='" . $_W['script_name'] . "?" . http_build_query($_GET) . "' class='page_button'><<上一页</a>&nbsp;&nbsp;";
	}
	$totalpage= ceil($total / $psize);
	
	$pager.="<span class='fc_red'>".$pindex."</span> / ".$totalpage."&nbsp;&nbsp;";
	if($pindex==$totalpage){
		$pager.="<font class='page_noclick'>下一页>></font>&nbsp;&nbsp;";
	}else{
		$_GET['page'] = $pindex+1;
		$pager.="<a href='" . $_W['script_name'] . "?" . http_build_query($_GET) . "' class='page_button'>下一页>></a>&nbsp;&nbsp;";
	}
	foreach($list as $k=>$row){
		if($row['valid_starttime']>TIMESTAMP){
			$list[$k]['tip1']="未开始";
			$list[$k]['tip2']=date('Y-m-d H:i:s',$row['valid_starttime'])."可以使用";
		}elseif ($row['valid_endtime']>TIMESTAMP){
			$rangtime=$row['valid_endtime']-TIMESTAMP; //开始与结束之间相差多少秒 
			$time = $rangtime/(86400);
			if($time==0){
				$time = $rangtime/3600;
				if($time==0){
					$list[$k]['tip1']="不足1小时";
				}else{
					$list[$k]['tip1']="剩余<br/><h>".ceil($time)."</h>小时";
				}
			}else{
				$list[$k]['tip1']="剩余<br/><y>".ceil($time)."</y>天";
			}
			$list[$k]['tip2']=date('Y-m-d H:i:s',$row['valid_endtime'])."过期";
		}elseif ($row['valid_endtime']< TIMESTAMP){
			$list[$k]['tip1']="已过期";
			$list[$k]['tip2']="已过期";
		}
	}
	    include $this->template('wl_eticket');