<?php
/**
 * 列表
 *
 * @author 超级无聊
 * @url
 */		
	$cateid=$_GPC['cateid'];
	if($cateid==0){
		//全部列表
		$pindex = max(1, intval($_GPC['page']));
		$psize = 10;
		$condition='';
 		$list = pdo_fetchall("SELECT * FROM ".tablename('shopping1_goods')." WHERE weid = '{$_W['weid']}' AND status = '1' $condition ORDER BY displayorder DESC, id DESC LIMIT ".($pindex - 1) * $psize.','.$psize);
		$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('shopping1_goods') . " WHERE weid = '{$_W['weid']}' AND status = '1' $condition");
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
		
		include $this->template('wl_list1');
	}else{
		//获取当前分类
		$curcategory = pdo_fetch("SELECT * FROM ".tablename('shopping1_category')." WHERE weid = '{$_W['weid']}'  and id={$cateid}");
		if($curcategory==false){
			message('分类错误，或者分类已经删除！');
		}
		if($curcategory['parentid']!=0){		
			$upcateory=pdo_fetch("SELECT * FROM ".tablename('shopping1_category')." WHERE weid = '{$_W['weid']}'  and id={$curcategory['parentid']}");
			if($upcateory==false){
				message('分类错误，或者分类已经删除！');
			}
			$catename='ccate';
		}else{
			$catename='pcate';
		}		
		
		//获取分类列表
		$pindex = max(1, intval($_GPC['page']));
		$psize = 10;
		$condition='';
		$condition .= " AND ".$catename." = '{$curcategory['id']}'";
		$list = pdo_fetchall("SELECT * FROM ".tablename('shopping1_goods')." WHERE weid = '{$_W['weid']}' AND status = '1' $condition ORDER BY displayorder DESC, id DESC LIMIT ".($pindex - 1) * $psize.','.$psize);

		$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('shopping1_goods') . " WHERE weid = '{$_W['weid']}' AND status = '1' $condition");
		$pager = pagination($total, $pindex, $psize, $url = '', $context = array('before' => 0, 'after' => 0, 'ajaxcallback' => ''));
		
		include $this->template('wl_list2');
	}