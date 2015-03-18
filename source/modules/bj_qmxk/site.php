
﻿<?php
defined('IN_IA') or exit('Access Denied');
session_start();
class bj_qmxkModuleSite extends WeModuleSite 
{
	public function __web($f_name)
	{
		global $_W,$_GPC;
		checklogin();
		$weid=$_W['weid'];
		$op= $operation = $_GPC['op']?$_GPC['op']:'display';
		include_once 'web/'.strtolower(substr($f_name,5)).'.php';
	}
	public function __mobile($f_name)
	{
		global $_W,$_GPC;
		$from_user =$this->getFromUser();
		$weid=$_W['weid'];
		$op = $_GPC['op']?$_GPC['op']:'display';
		include_once 'mobile/'.strtolower(substr($f_name,8)).'.php';
	}
	public function doWebAuth() 
	{
		global $_W,$_GPC;
		$authortxt=" 请联系作者重新授权</br> 网址：http://www.b2ctui.com";
		$modulename='bj_qmxk';
		$key= 'bj_qmxkcco1905cmodule';
		$sendapi='http://www.b2ctui.com/';
		$do=$_GPC['do'];
		$authorinfo=$authortxt;
		$updateurl=create_url('site/module/'.$do, array('name' => $modulename,'op'=>'doauth'));
		$op=$_GPC['op'];
		if($op=='doauth')
		{
			$authhost = $_SERVER['SERVER_NAME'];
			$authmodule = $modulename;
			$sendapi = $sendapi.'/authcode.php?act=authcode&authhost='.$authhost.'&authmodule='.$authmodule;
			$response = ihttp_request($sendapi, json_encode($send));
			if(!$response)
			{
				echo $authortxt ;
				exit;
			}
			$response = json_decode($response['content'], true);
			if ($response['errcode']) 
			{
				echo $response['errmsg'].$authorinfo;
				exit;
			}
			if (!empty($response['content'])) 
			{
				$data=array( 'url'=>$response['content'] );
				pdo_update('modules', $data, array('name' => $modulename));
				message('更新授权成功', referer(), 'success');
			}
		}
		$module = pdo_fetch("SELECT mid, name,url FROM " . tablename('modules') . " WHERE name = :name", array(':name' => $modulename));
		if($module==false)
		{
			message("参数错误!".$authorinfo,'','error');
		}
		if(empty($module['url']))
		{
			message("验证信息为空!".$authorinfo,'','error');
		}
		$ident_arr=authcode(base64_decode($module['url']),'DECODE',$key);
		if (!$ident_arr)
		{
			message("验证参数出错!".$authorinfo,'','error');
		}
		$ident_arr=explode('#',$ident_arr);
		if($ident_arr[0] != $modulename)
		{
			message("验证参数出错!".$authorinfo,'','error');
		}
		if($ident_arr[1]!=$_SERVER['SERVER_NAME'])
		{
			message("服务器域名不符合!".$authorinfo,'','error');
		}
	}
	public function doWebCategory() 
	{
		global $_W,$_GPC;
		//$this->doWebAuth();
		checklogin();
		$weid=$_W['weid'];
		$op= $operation = $_GPC['op']?$_GPC['op']:'display';
		$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
		if ($operation == 'display') 
		{
			if (!empty($_GPC['displayorder'])) 
			{
				foreach ($_GPC['displayorder'] as $id => $displayorder) 
				{
					pdo_update('bj_qmxk_category', array('displayorder' => $displayorder), array('id' => $id));
				}
				message('分类排序更新成功！', $this->createWebUrl('category', array('op' => 'display')), 'success');
			}
			$children = array();
			$category = pdo_fetchall("SELECT * FROM " . tablename('bj_qmxk_category') . " WHERE weid = '{$_W['weid']}
		' ORDER BY parentid ASC, displayorder DESC");
		foreach ($category as $index => $row) 
		{
			if (!empty($row['parentid'])) 
			{
				$children[$row['parentid']][] = $row;
				unset($category[$index]);
			}
		}
		include $this->template('category');
	}
	elseif ($operation == 'post') 
	{
		$parentid = intval($_GPC['parentid']);
		$id = intval($_GPC['id']);
		if (!empty($id)) 
		{
			$category = pdo_fetch("SELECT * FROM " . tablename('bj_qmxk_category') . " WHERE id = '$id'");
		}
		else 
		{
			$category = array( 'displayorder' => 0, );
		}
		if (!empty($parentid)) 
		{
			$parent = pdo_fetch("SELECT id, name FROM " . tablename('bj_qmxk_category') . " WHERE id = '$parentid'");
			if (empty($parent)) 
			{
				message('抱歉，上级分类不存在或是已经被删除！', $this->createWebUrl('post'), 'error');
			}
		}
		if (checksubmit('submit')) 
		{
			if (empty($_GPC['catename'])) 
			{
				message('抱歉，请输入分类名称！');
			}
			$data = array( 'weid' => $_W['weid'], 'name' => $_GPC['catename'], 'enabled' => intval($_GPC['enabled']), 'displayorder' => intval($_GPC['displayorder']), 'isrecommand' => intval($_GPC['isrecommand']), 'description' => $_GPC['description'], 'parentid' => intval($parentid), );
			if (!empty($_FILES['thumb']['tmp_name'])) 
			{
				file_delete($_GPC['thumb_old']);
				$upload = file_upload($_FILES['thumb']);
				if (is_error($upload)) 
				{
					message($upload['message'], '', 'error');
				}
				$data['thumb'] = $upload['path'];
			}
			if (!empty($id)) 
			{
				unset($data['parentid']);
				pdo_update('bj_qmxk_category', $data, array('id' => $id));
			}
			else 
			{
				pdo_insert('bj_qmxk_category', $data);
				$id = pdo_insertid();
			}
			message('更新分类成功！', $this->createWebUrl('category', array('op' => 'display')), 'success');
		}
		include $this->template('category');
	}
	elseif ($operation == 'delete') 
	{
		$id = intval($_GPC['id']);
		$category = pdo_fetch("SELECT id, parentid FROM " . tablename('bj_qmxk_category') . " WHERE id = '$id'");
		if (empty($category)) 
		{
			message('抱歉，分类不存在或是已经被删除！', $this->createWebUrl('category', array('op' => 'display')), 'error');
		}
		pdo_delete('bj_qmxk_category', array('id' => $id, 'parentid' => $id), 'OR');
		message('分类删除成功！', $this->createWebUrl('category', array('op' => 'display')), 'success');
	}
}
public function doWebSetGoodsProperty() 
{
	global $_GPC, $_W;
	//$this->doWebAuth();
	$id = intval($_GPC['id']);
	$type = $_GPC['type'];
	$data = intval($_GPC['data']);
	empty($data) ? ($data = 1) : $data = 0;
	if (!in_array($type, array('new', 'hot', 'recommand', 'discount', 'status'))) 
	{
		die(json_encode(array("result" => 0)));
	}
	if($_GPC['type']=='status')
	{
		pdo_update("bj_qmxk_goods", array($type => $data), array("id" => $id, "weid" => $_W['weid']));
	}
	else 
	{
		pdo_update("bj_qmxk_goods", array("is" . $type => $data), array("id" => $id, "weid" => $_W['weid']));
	}
	die(json_encode(array("result" => 1, "data" => $data)));
}
public function doWebGoods() 
{
	global $_GPC, $_W;
	//$this->doWebAuth();
	$category = pdo_fetchall("SELECT * FROM " . tablename('bj_qmxk_category') . " WHERE weid = '{$_W['weid']}
' ORDER BY parentid ASC, displayorder DESC", array(), 'id');
if (!empty($category)) 
{
	$children = '';
	foreach ($category as $cid => $cate) 
	{
		if (!empty($cate['parentid'])) 
		{
			$children[$cate['parentid']][$cate['id']] = array($cate['id'], $cate['name']);
		}
	}
}
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
if ($operation == 'post') 
{
	$id = intval($_GPC['id']);
	if (!empty($id)) 
	{
		$item = pdo_fetch("SELECT * FROM " . tablename('bj_qmxk_goods') . " WHERE id = :id", array(':id' => $id));
		if (empty($item)) 
		{
			message('抱歉，商品不存在或是已经删除！', '', 'error');
		}
		$allspecs = pdo_fetchall("select * from " . tablename('bj_qmxk_spec')." where goodsid=:id order by displayorder asc",array(":id"=>$id));
		foreach ($allspecs as &$s) 
		{
			$s['items'] = pdo_fetchall("select * from " . tablename('bj_qmxk_spec_item') . " where specid=:specid order by displayorder asc", array(":specid" => $s['id']));
		}
		unset($s);
		$params = pdo_fetchall("select * from " . tablename('bj_qmxk_goods_param') . " where goodsid=:id order by displayorder asc", array(':id' => $id));
		$piclist = unserialize($item['thumb_url']);
		$html = "";
		$options = pdo_fetchall("select * from " . tablename('bj_qmxk_goods_option') . " where goodsid=:id order by id asc", array(':id' => $id));
		$specs = array();
		if (count($options) > 0) 
		{
			$specitemids = explode("_", $options[0]['specs'] );
			foreach($specitemids as $itemid)
			{
				foreach($allspecs as $ss)
				{
					$items= $ss['items'];
					foreach($items as $it)
					{
						if($it['id']==$itemid)
						{
							$specs[] = $ss;
							break;
						}
					}
				}
			}
			$html = '<table  class="tb spectable" style="border:1px solid #ccc;"><thead><tr>';
			$len = count($specs);
			$newlen = 1;
			$h = array();
			$rowspans = array();
			for ($i = 0; $i < $len; $i++) 
			{
				$html.="<th>" . $specs[$i]['title'] . "</th>";
				$itemlen = count($specs[$i]['items']);
				if ($itemlen <= 0) 
				{
					$itemlen = 1;
				}
				$newlen*=$itemlen;
				$h = array();
				for ($j = 0; $j < $newlen; $j++) 
				{
					$h[$i][$j] = array();
				}
				$l = count($specs[$i]['items']);
				$rowspans[$i] = 1;
				for ($j = $i + 1; $j < $len; $j++) 
				{
					$rowspans[$i]*= count($specs[$j]['items']);
				}
			}
			$html .= '<th><div class="input-append input-prepend"><span class="add-on">库存</span><input type="text" class="span1 option_stock_all"  VALUE=""/><span class="add-on"><a href="javascript:;" class="icon-hand-down" title="批量设置" onclick="setCol(\'option_stock\');"></a></span></div></th>';
			$html.= '<th><div class="input-append input-prepend"><span class="add-on">销售价格</span><input type="text" class="span1 option_marketprice_all"  VALUE=""/><span class="add-on"><a href="javascript:;" class="icon-hand-down" title="批量设置" onclick="setCol(\'option_marketprice\');"></a></span></div><br/></th>';
			$html.='<th><div class="input-append input-prepend"><span class="add-on">市场价格</span><input type="text" class="span1 option_productprice_all"  VALUE=""/><span class="add-on"><a href="javascript:;" class="icon-hand-down" title="批量设置" onclick="setCol(\'option_productprice\');"></a></span></div></th>';
			$html.='<th><div class="input-append input-prepend"><span class="add-on">成本价格</span><input type="text" class="span1 option_costprice_all"  VALUE=""/><span class="add-on"><a href="javascript:;" class="icon-hand-down" title="批量设置" onclick="setCol(\'option_costprice\');"></a></span></div></th>';
			$html.='<th><div class="input-append input-prepend"><span class="add-on">重量(克)</span><input type="text" class="span1 option_weight_all"  VALUE=""/><span class="add-on"><a href="javascript:;" class="icon-hand-down" title="批量设置" onclick="setCol(\'option_weight\');"></a></span></div></th>';
			$html.='</tr>';
			for($m=0;$m<$len;$m++)
			{
				$k = 0;
				$kid = 0;
				$n=0;
				for($j=0;$j<$newlen;$j++)
				{
					$rowspan = $rowspans[$m];
					if( $j % $rowspan==0)
					{
						$h[$m][$j]=array("html"=> "<td rowspan='".$rowspan."'>".$specs[$m]['items'][$kid]['title']."</td>","id"=>$specs[$m]['items'][$kid]['id']);
					}
					else
					{
						$h[$m][$j]=array("html"=> "","id"=>$specs[$m]['items'][$kid]['id']);
					}
					$n++;
					if($n==$rowspan)
					{
						$kid++;
						if($kid>count($specs[$m]['items'])-1) 
						{
							$kid=0;
						}
						$n=0;
					}
				}
			}
			$hh = "";
			for ($i = 0; $i < $newlen; $i++) 
			{
				$hh.="<tr>";
				$ids = array();
				for ($j = 0; $j < $len; $j++) 
				{
					$hh.=$h[$j][$i]['html'];
					$ids[] = $h[$j][$i]['id'];
				}
				$ids = implode("_", $ids);
				$val = array("id" => "","title"=>"", "stock" => "", "costprice" => "", "productprice" => "", "marketprice" => "", "weight" => "");
				foreach ($options as $o) 
				{
					if ($ids === $o['specs']) 
					{
						$val = array("id" => $o['id'], "title"=>$o['title'], "stock" => $o['stock'], "costprice" => $o['costprice'], "productprice" => $o['productprice'], "marketprice" => $o['marketprice'], "weight" => $o['weight']);
						break;
					}
				}
				$hh .= '<td>';
				$hh .= '<input name="option_stock_' . $ids . '[]"  type="text" class="span1 option_stock option_stock_' . $ids . '" value="' . $val['stock'] . '"/></td>';
				$hh .= '<input name="option_id_' . $ids . '[]"  type="hidden" class="span1 option_id option_id_' . $ids . '" value="' . $val['id'] . '"/>';
				$hh .= '<input name="option_ids[]"  type="hidden" class="span1 option_ids option_ids_' . $ids . '" value="' . $ids . '"/>';
				$hh .= '<input name="option_title_' . $ids . '[]"  type="hidden" class="span1 option_title option_title_' . $ids . '" value="' . $val['title'] . '"/>';
				$hh .= '</td>';
				$hh .= '<td><input name="option_marketprice_' . $ids . '[]" type="text" class="span1 option_marketprice option_marketprice_' . $ids . '" value="' . $val['marketprice'] . '"/></td>';
				$hh .= '<td><input name="option_productprice_' . $ids . '[]" type="text" class="span1 option_productprice option_productprice_' . $ids . '" " value="' . $val['productprice'] . '"/></td>';
				$hh .= '<td><input name="option_costprice_' . $ids . '[]" type="text" class="span1 option_costprice option_costprice_' . $ids . '" " value="' . $val['costprice'] . '"/></td>';
				$hh .= '<td><input name="option_weight_' . $ids . '[]" type="text" class="span1 option_weight option_weight_' . $ids . '" " value="' . $val['weight'] . '"/></td>';
				$hh .="</tr>";
			}
			$html.=$hh;
			$html.="</table>";
		}
	}
	if (empty($category)) 
	{
		message('抱歉，请您先添加商品分类！', $this->createWebUrl('category', array('op' => 'post')), 'error');
	}
	if (checksubmit('submit')) 
	{
		if (empty($_GPC['goodsname'])) 
		{
			message('请输入商品名称！');
		}
		if (empty($_GPC['pcate'])) 
		{
			message('请选择商品分类！');
		}
		$data = array( 'weid' => intval($_W['weid']), 'displayorder' => intval($_GPC['displayorder']), 'title' => $_GPC['goodsname'], 'pcate' => intval($_GPC['pcate']), 'ccate' => intval($_GPC['ccate']), 'type' => intval($_GPC['type']), 'isrecommand' => intval($_GPC['isrecommand']), 'ishot' => intval($_GPC['ishot']), 'isnew' => intval($_GPC['isnew']), 'isdiscount' => intval($_GPC['isdiscount']), 'istime' => intval($_GPC['istime']), 'timestart' => strtotime($_GPC['timestart']), 'timeend' => strtotime($_GPC['timeend']), 'description' => $_GPC['description'], 'content' => htmlspecialchars_decode($_GPC['content']), 'goodssn' => $_GPC['goodssn'], 'unit' => $_GPC['unit'], 'createtime' => TIMESTAMP, 'total' => intval($_GPC['total']), 'totalcnf' => intval($_GPC['totalcnf']), 'marketprice' => $_GPC['marketprice'], 'weight' => $_GPC['weight'], 'costprice' => $_GPC['costprice'], 'productprice' => $_GPC['productprice'], 'productsn' => $_GPC['productsn'], 'credit' => intval($_GPC['credit']), 'maxbuy' => intval($_GPC['maxbuy']), 'commission' => intval($_GPC['commission']), 'commission2' => intval($_GPC['commission2']), 'commission3' => intval($_GPC['commission3']), 'hasoption' => intval($_GPC['hasoption']), 'sales' => intval($_GPC['sales']), 'status' => intval($_GPC['status']), );
		if (!empty($_FILES['thumb']['tmp_name'])) 
		{
			file_delete($_GPC['thumb_old']);
			$upload = file_upload($_FILES['thumb']);
			if (is_error($upload)) 
			{
				message($upload['message'], '', 'error');
			}
			$data['thumb'] = $upload['path'];
		}
		if (!empty($_FILES['xsthumb']['tmp_name'])) 
		{
			file_delete($_GPC['xsthumb_old']);
			$upload = file_upload($_FILES['xsthumb']);
			if (is_error($upload)) 
			{
				message($upload['message'], '', 'error');
			}
			$data['xsthumb'] = $upload['path'];
		}
		$cur_index = 0;
		if (!empty($_GPC['attachment-new'])) 
		{
			foreach ($_GPC['attachment-new'] as $index => $row) 
			{
				if (empty($row)) 
				{
					continue;
				}
				$hsdata[$index] = array( 'attachment' => $_GPC['attachment-new'][$index], );
			}
			$cur_index = $index + 1;
		}
		if (!empty($_GPC['attachment'])) 
		{
			foreach ($_GPC['attachment'] as $index => $row) 
			{
				if (empty($row)) 
				{
					continue;
				}
				$hsdata[$cur_index + $index] = array( 'attachment' => $_GPC['attachment'][$index] );
			}
		}
		$data['thumb_url'] = serialize($hsdata);
		if (empty($id)) 
		{
			pdo_insert('bj_qmxk_goods', $data);
			$id = pdo_insertid();
		}
		else 
		{
			unset($data['createtime']);
			pdo_update('bj_qmxk_goods', $data, array('id' => $id));
		}
		$totalstocks = 0;
		$param_ids = $_POST['param_id'];
		$param_titles = $_POST['param_title'];
		$param_values = $_POST['param_value'];
		$param_displayorders = $_POST['param_displayorder'];
		$len = count($param_ids);
		$paramids = array();
		for ($k = 0; $k < $len; $k++) 
		{
			$param_id = "";
			$get_param_id = $param_ids[$k];
			$a = array( "title" => $param_titles[$k], "value" => $param_values[$k], "displayorder" => $k, "goodsid" => $id, );
			if (!is_numeric($get_param_id)) 
			{
				pdo_insert("bj_qmxk_goods_param", $a);
				$param_id = pdo_insertid();
			}
			else 
			{
				pdo_update("bj_qmxk_goods_param", $a, array('id' => $get_param_id));
				$param_id = $get_param_id;
			}
			$paramids[] = $param_id;
		}
		if (count($paramids) > 0) 
		{
			pdo_query("delete from " . tablename('bj_qmxk_goods_param') . " where goodsid=$id and id not in ( " . implode(',', $paramids) . ")");
		}
		else
		{
			pdo_query("delete from " . tablename('bj_qmxk_goods_param') . " where goodsid=$id");
		}
		$files = $_FILES;
		$spec_ids = $_POST['spec_id'];
		$spec_titles = $_POST['spec_title'];
		$specids = array();
		$len = count($spec_ids);
		$specids = array();
		$spec_items = array();
		for ($k = 0; $k < $len; $k++) 
		{
			$spec_id = "";
			$get_spec_id = $spec_ids[$k];
			$a = array( "weid" => $_W['weid'], "goodsid" => $id, "displayorder" => $k, "title" => $spec_titles[$get_spec_id] );
			if (is_numeric($get_spec_id)) 
			{
				pdo_update("bj_qmxk_spec", $a, array("id" => $get_spec_id));
				$spec_id = $get_spec_id;
			}
			else 
			{
				pdo_insert("bj_qmxk_spec", $a);
				$spec_id = pdo_insertid();
			}
			$spec_item_ids = $_POST["spec_item_id_".$get_spec_id];
			$spec_item_titles = $_POST["spec_item_title_".$get_spec_id];
			$spec_item_shows = $_POST["spec_item_show_".$get_spec_id];
			$spec_item_oldthumbs = $_POST["spec_item_oldthumb_".$get_spec_id];
			$itemlen = count($spec_item_ids);
			$itemids = array();
			for ($n = 0; $n < $itemlen; $n++) 
			{
				$item_id = "";
				$get_item_id = $spec_item_ids[$n];
				$d = array( "weid" => $_W['weid'], "specid" => $spec_id, "displayorder" => $n, "title" => $spec_item_titles[$n], "show" => $spec_item_shows[$n] );
				$f = "spec_item_thumb_" . $get_item_id;
				$old = $spec_item_oldthumbs[$k];
				if (!empty($files[$f]['tmp_name'])) 
				{
					$upload = file_upload($files[$f]);
					if (is_error($upload)) 
					{
						message($upload['message'], '', 'error');
					}
					$d['thumb'] = $upload['path'];
				}
				else if (!empty($old)) 
				{
					$d['thumb'] = $old;
				}
				if (is_numeric($get_item_id)) 
				{
					pdo_update("bj_qmxk_spec_item", $d, array("id" => $get_item_id));
					$item_id = $get_item_id;
				}
				else 
				{
					pdo_insert("bj_qmxk_spec_item", $d);
					$item_id = pdo_insertid();
				}
				$itemids[] = $item_id;
				$d['get_id'] = $get_item_id;
				$d['id']= $item_id;
				$spec_items[] = $d;
			}
			if(count($itemids)>0)
			{
				pdo_query("delete from " . tablename('bj_qmxk_spec_item') . " where weid={$_W['weid']}
			and specid=$spec_id and id not in (" . implode(",", $itemids) . ")");
		}
		else
		{
			pdo_query("delete from " . tablename('bj_qmxk_spec_item') . " where weid={$_W['weid']}
		and specid=$spec_id");
	}
	pdo_update("bj_qmxk_spec", array("content" => serialize($itemids)), array("id" => $spec_id));
	$specids[] = $spec_id;
}
if( count($specids)>0)
{
	pdo_query("delete from " . tablename('bj_qmxk_spec') . " where weid={$_W['weid']}
and goodsid=$id and id not in (" . implode(",", $specids) . ")");
}
else
{
pdo_query("delete from " . tablename('bj_qmxk_spec') . " where weid={$_W['weid']}
and goodsid=$id");
}
$option_idss = $_POST['option_ids'];
$option_productprices = $_POST['option_productprice'];
$option_marketprices = $_POST['option_marketprice'];
$option_costprices = $_POST['option_costprice'];
$option_stocks = $_POST['option_stock'];
$option_weights = $_POST['option_weight'];
$len = count($option_idss);
$optionids = array();
for ($k = 0; $k < $len; $k++) 
{
$option_id = "";
$get_option_id = $_GPC['option_id_' . $ids][0];
$ids = $option_idss[$k];
$idsarr = explode("_",$ids);
$newids = array();
foreach($idsarr as $key=>$ida)
{
foreach($spec_items as $it)
{
	if($it['get_id']==$ida)
	{
		$newids[] = $it['id'];
		break;
	}
}
}
$newids = implode("_",$newids);
$a = array( "title" => $_GPC['option_title_' . $ids][0], "productprice" => $_GPC['option_productprice_' . $ids][0], "costprice" => $_GPC['option_costprice_' . $ids][0], "marketprice" => $_GPC['option_marketprice_' . $ids][0], "stock" => $_GPC['option_stock_' . $ids][0], "weight" => $_GPC['option_weight_' . $ids][0], "goodsid" => $id, "specs" => $newids );
$totalstocks+=$a['stock'];
if (empty($get_option_id)) 
{
pdo_insert("bj_qmxk_goods_option", $a);
$option_id = pdo_insertid();
}
else 
{
pdo_update("bj_qmxk_goods_option", $a, array('id' => $get_option_id));
$option_id = $get_option_id;
}
$optionids[] = $option_id;
}
if (count($optionids) > 0) 
{
pdo_query("delete from " . tablename('bj_qmxk_goods_option') . " where goodsid=$id and id not in ( " . implode(',', $optionids) . ")");
}
else
{
pdo_query("delete from " . tablename('bj_qmxk_goods_option') . " where goodsid=$id");
}
if ($totalstocks > 0) 
{
pdo_update("bj_qmxk_goods", array("total" => $totalstocks), array("id" => $id));
}
message('商品更新成功！', $this->createWebUrl('goods', array('op' => 'post', 'id' => $id)), 'success');
}
}
elseif ($operation == 'display') 
{
$pindex = max(1, intval($_GPC['page']));
$psize = 20;
$condition = '';
if (!empty($_GPC['keyword'])) 
{
$condition .= " AND title LIKE '%{$_GPC['keyword']}
%'";
}
if (!empty($_GPC['cate_2'])) 
{
$cid = intval($_GPC['cate_2']);
$condition .= " AND ccate = '{$cid}
'";
}
elseif (!empty($_GPC['cate_1'])) 
{
$cid = intval($_GPC['cate_1']);
$condition .= " AND pcate = '{$cid}
'";
}
if (isset($_GPC['status'])) 
{
$condition .= " AND status = '" . intval($_GPC['status']) . "'";
}
$list = pdo_fetchall("SELECT * FROM " . tablename('bj_qmxk_goods') . " WHERE weid = '{$_W['weid']}
' and deleted=0 $condition ORDER BY status DESC, displayorder DESC, id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('bj_qmxk_goods') . " WHERE weid = '{$_W['weid']}
'  and deleted=0 $condition");
$pager = pagination($total, $pindex, $psize);
}
elseif ($operation == 'delete') 
{
$id = intval($_GPC['id']);
$row = pdo_fetch("SELECT id, thumb FROM " . tablename('bj_qmxk_goods') . " WHERE id = :id", array(':id' => $id));
if (empty($row)) 
{
message('抱歉，商品不存在或是已经被删除！');
}
pdo_update("bj_qmxk_goods", array("deleted" => 1), array('id' => $id));
message('删除成功！', referer(), 'success');
}
elseif ($operation == 'productdelete') 
{
$id = intval($_GPC['id']);
pdo_delete('bj_qmxk_product', array('id' => $id));
message('删除成功！', '', 'success');
}
include $this->template('goods');
}
public function doWebOrder() 
{
global $_W, $_GPC;
//$this->doWebAuth();
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
if ($operation == 'display') 
{
$pindex = max(1, intval($_GPC['page']));
$psize = 20;
$status = !isset($_GPC['status']) ? 1 : $_GPC['status'];
$sendtype = !isset($_GPC['sendtype']) ? 0 : $_GPC['sendtype'];
$condition = '';
if (!empty($_GPC['keyword'])) 
{
$condition .= " AND title LIKE '%{$_GPC['keyword']}
%'";
}
if (!empty($_GPC['cate_2'])) 
{
$cid = intval($_GPC['cate_2']);
$condition .= " AND ccate = '{$cid}
'";
}
elseif (!empty($_GPC['cate_1'])) 
{
$cid = intval($_GPC['cate_1']);
$condition .= " AND pcate = '{$cid}
'";
}
if ($status != '-1') 
{
$condition .= " AND status = '" . intval($status) . "'";
}
if(!empty($_GPC['shareid']))
{
$shareid = $_GPC['shareid'];
$user = pdo_fetch("select * from ".tablename('bj_qmxk_member'). " where id = ".$shareid." and weid = ".$_W['weid']);
$condition .= " AND shareid = '". intval($_GPC['shareid']). "' AND createtime>=".$user['flagtime']." AND from_user<>'".$user['from_user']."'";
}
if (!empty($sendtype)) 
{
$condition .= " AND sendtype = '" . intval($sendtype) . "' AND status != '3'";
}
$list = pdo_fetchall("SELECT * FROM " . tablename('bj_qmxk_order') . " WHERE weid = '{$_W['weid']}
' $condition ORDER BY status ASC, createtime DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('bj_qmxk_order') . " WHERE weid = '{$_W['weid']}
' $condition");
$pager = pagination($total, $pindex, $psize);
if (!empty($list)) 
{
foreach ($list as $key=>$l)
{
$commission = pdo_fetch("select total,commission, commission2, commission3 from ".tablename('bj_qmxk_order_goods')." where orderid = ".$l['id']);
$list[$key]['commission'] = $commission['commission'] * $commission['total'];
$list[$key]['commission2'] = $commission['commission2'] * $commission['total'];
$list[$key]['commission3'] = $commission['commission3'] * $commission['total'];
}
}
if (!empty($list)) 
{
foreach ($list as &$row) 
{
!empty($row['addressid']) && $addressids[$row['addressid']] = $row['addressid'];
$row['dispatch'] = pdo_fetch("SELECT * FROM " . tablename('bj_qmxk_dispatch') . " WHERE id = :id", array(':id' => $row['dispatch']));
}
unset($row);
}
if (!empty($addressids)) 
{
$address = pdo_fetchall("SELECT * FROM " . tablename('bj_qmxk_address') . " WHERE id IN ('" . implode("','", $addressids) . "')", array(), 'id');
}
}
elseif ($operation == 'detail') 
{
$members = pdo_fetchall("select id, realname from ".tablename('bj_qmxk_member')." where weid = ".$_W['weid']." and status = 1");
$member = array();
foreach($members as $m)
{
$member[$m['id']] = $m['realname'];
}
$id = intval($_GPC['id']);
$item = pdo_fetch("SELECT * FROM " . tablename('bj_qmxk_order') . " WHERE id = :id", array(':id' => $id));
if (empty($item)) 
{
message("抱歉，订单不存在!", referer(), "error");
}
if (checksubmit('confirmsend')) 
{
if (!empty($_GPC['isexpress']) && empty($_GPC['expresssn'])) 
{
message('请输入快递单号！');
}
$item = pdo_fetch("SELECT transid FROM " . tablename('bj_qmxk_order') . " WHERE id = :id", array(':id' => $id));
if (!empty($item['transid'])) 
{
$this->changeWechatSend($id, 1);
}
pdo_update('bj_qmxk_order', array( 'status' => 2, 'remark' => $_GPC['remark'], 'express' => $_GPC['express'], 'expresscom' => $_GPC['expresscom'], 'expresssn' => $_GPC['expresssn'], ), array('id' => $id));
message('发货操作成功！', referer(), 'success');
}
if (checksubmit('cancelsend')) 
{
$item = pdo_fetch("SELECT transid FROM " . tablename('bj_qmxk_order') . " WHERE id = :id", array(':id' => $id));
if (!empty($item['transid'])) 
{
$this->changeWechatSend($id, 0, $_GPC['cancelreson']);
}
pdo_update('bj_qmxk_order', array( 'status' => 1, 'remark' => $_GPC['remark'], ), array('id' => $id));
message('取消发货操作成功！', referer(), 'success');
}
if (checksubmit('finish')) 
{
$this->setOrderCredit($id);
pdo_update('bj_qmxk_order', array('status' => 3, 'remark' => $_GPC['remark']), array('id' => $id));
message('订单操作成功！', referer(), 'success');
}
if (checksubmit('cancelpay')) 
{
pdo_update('bj_qmxk_order', array('status' => 0, 'remark' => $_GPC['remark']), array('id' => $id));
$this->setOrderStock($id, false);
message('取消订单付款操作成功！', referer(), 'success');
}
if (checksubmit('confrimpay')) 
{
pdo_update('bj_qmxk_order', array('status' => 1, 'paytype' => 2, 'remark' => $_GPC['remark']), array('id' => $id));
$this->setOrderStock($id);
message('确认订单付款操作成功！', referer(), 'success');
}
if (checksubmit('close')) 
{
$item = pdo_fetch("SELECT transid FROM " . tablename('bj_qmxk_order') . " WHERE id = :id", array(':id' => $id));
if (!empty($item['transid'])) 
{
$this->changeWechatSend($id, 0, $_GPC['reson']);
}
pdo_update('bj_qmxk_order', array('status' => -1, 'remark' => $_GPC['remark']), array('id' => $id));
message('订单关闭操作成功！', referer(), 'success');
}
if (checksubmit('open')) 
{
pdo_update('bj_qmxk_order', array('status' => 0, 'remark' => $_GPC['remark']), array('id' => $id));
message('开启订单操作成功！', referer(), 'success');
}
$dispatch = pdo_fetch("SELECT * FROM " . tablename('bj_qmxk_dispatch') . " WHERE id = :id", array(':id' => $item['dispatch']));
if (!empty($dispatch) && !empty($dispatch['express'])) 
{
$express = pdo_fetch("select * from " . tablename('bj_qmxk_express') . " WHERE id=:id limit 1", array(":id" => $dispatch['express']));
}
$item['user'] = pdo_fetch("SELECT * FROM " . tablename('bj_qmxk_address') . " WHERE id = {$item['addressid']}
");
$goods = pdo_fetchall("SELECT g.id, g.title, g.status,g.thumb, g.unit,g.goodssn,g.productsn,g.marketprice,o.total,g.type,o.optionname,o.optionid,o.price as orderprice FROM " . tablename('bj_qmxk_order_goods') . " o left join " . tablename('bj_qmxk_goods') . " g on o.goodsid=g.id  WHERE o.orderid='{$id}
'");
$item['goods'] = $goods;
}
include $this->template('order');
}
public function doWebOrdermy() 
{
global $_W, $_GPC;
//$this->doWebAuth();
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
if(empty($_GPC['from_user']))
{
message('请选择会员！', create_url('site/module', array('do' => 'charge','op'=>'list', 'name' => 'bj_qmxk','weid'=>$_W['weid'])), 'success');
exit;
}
if ($operation == 'display') 
{
$pindex = max(1, intval($_GPC['page']));
$psize = 20;
$status = !isset($_GPC['status']) ? 1 : $_GPC['status'];
$sendtype = !isset($_GPC['sendtype']) ? 0 : $_GPC['sendtype'];
$condition = '';
if (!empty($_GPC['keyword'])) 
{
$condition .= " AND title LIKE '%{$_GPC['keyword']}
%'";
}
if (!empty($_GPC['cate_2'])) 
{
$cid = intval($_GPC['cate_2']);
$condition .= " AND ccate = '{$cid}
'";
}
elseif (!empty($_GPC['cate_1'])) 
{
$cid = intval($_GPC['cate_1']);
$condition .= " AND pcate = '{$cid}
'";
}
if ($status != '-1') 
{
$condition .= " AND status = '" . intval($status) . "'";
}
if(!empty($_GPC['shareid']))
{
$shareid = $_GPC['shareid'];
$user = pdo_fetch("select * from ".tablename('bj_qmxk_member'). " where id = ".$shareid." and weid = ".$_W['weid']);
$condition .= " AND shareid = '". intval($_GPC['shareid']). "' AND createtime>=".$user['flagtime']." AND from_user<>'".$user['from_user']."'";
}
if (!empty($sendtype)) 
{
$condition .= " AND sendtype = '" . intval($sendtype) . "' AND status != '3'";
}
$list = pdo_fetchall("SELECT * FROM " . tablename('bj_qmxk_order') . " WHERE from_user = '{$_GPC['from_user']}
' AND weid = '{$_W['weid']}
'$condition ORDER BY status ASC, createtime DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('bj_qmxk_order') . " WHERE from_user = '{$_GPC['from_user']}
' AND weid = '{$_W['weid']}
'$condition");
$pager = pagination($total, $pindex, $psize);
if (!empty($list)) 
{
foreach ($list as $key=>$l)
{
$commission = pdo_fetch("select total,commission, commission2, commission3 from ".tablename('bj_qmxk_order_goods')." where orderid = ".$l['id']);
$list[$key]['commission'] = $commission['commission'] * $commission['total'];
$list[$key]['commission2'] = $commission['commission2'] * $commission['total'];
$list[$key]['commission3'] = $commission['commission3'] * $commission['total'];
}
}
if (!empty($list)) 
{
foreach ($list as &$row) 
{
!empty($row['addressid']) && $addressids[$row['addressid']] = $row['addressid'];
$row['dispatch'] = pdo_fetch("SELECT * FROM " . tablename('bj_qmxk_dispatch') . " WHERE id = :id", array(':id' => $row['dispatch']));
}
unset($row);
}
if (!empty($addressids)) 
{
$address = pdo_fetchall("SELECT * FROM " . tablename('bj_qmxk_address') . " WHERE id IN ('" . implode("','", $addressids) . "')", array(), 'id');
}
}
elseif ($operation == 'detail') 
{
$members = pdo_fetchall("select id, realname from ".tablename('bj_qmxk_member')." where weid = ".$_W['weid']." and status = 1");
$member = array();
foreach($members as $m)
{
$member[$m['id']] = $m['realname'];
}
$id = intval($_GPC['id']);
$item = pdo_fetch("SELECT * FROM " . tablename('bj_qmxk_order') . " WHERE id = :id", array(':id' => $id));
if (empty($item)) 
{
message("抱歉，订单不存在!", referer(), "error");
}
if (checksubmit('confirmsend')) 
{
if (!empty($_GPC['isexpress']) && empty($_GPC['expresssn'])) 
{
message('请输入快递单号！');
}
$item = pdo_fetch("SELECT transid FROM " . tablename('bj_qmxk_order') . " WHERE id = :id", array(':id' => $id));
if (!empty($item['transid'])) 
{
$this->changeWechatSend($id, 1);
}
pdo_update('bj_qmxk_order', array( 'status' => 2, 'remark' => $_GPC['remark'], 'express' => $_GPC['express'], 'expresscom' => $_GPC['expresscom'], 'expresssn' => $_GPC['expresssn'], ), array('id' => $id));
message('发货操作成功！', referer(), 'success');
}
if (checksubmit('cancelsend')) 
{
$item = pdo_fetch("SELECT transid FROM " . tablename('bj_qmxk_order') . " WHERE id = :id", array(':id' => $id));
if (!empty($item['transid'])) 
{
$this->changeWechatSend($id, 0, $_GPC['cancelreson']);
}
pdo_update('bj_qmxk_order', array( 'status' => 1, 'remark' => $_GPC['remark'], ), array('id' => $id));
message('取消发货操作成功！', referer(), 'success');
}
if (checksubmit('finish')) 
{
$this->setOrderCredit($id);
pdo_update('bj_qmxk_order', array('status' => 3, 'remark' => $_GPC['remark']), array('id' => $id));
message('订单操作成功！', referer(), 'success');
}
if (checksubmit('cancelpay')) 
{
pdo_update('bj_qmxk_order', array('status' => 0, 'remark' => $_GPC['remark']), array('id' => $id));
$this->setOrderStock($id, false);
message('取消订单付款操作成功！', referer(), 'success');
}
if (checksubmit('confrimpay')) 
{
pdo_update('bj_qmxk_order', array('status' => 1, 'paytype' => 2, 'remark' => $_GPC['remark']), array('id' => $id));
$this->setOrderStock($id);
message('确认订单付款操作成功！', referer(), 'success');
}
if (checksubmit('close')) 
{
$item = pdo_fetch("SELECT transid FROM " . tablename('bj_qmxk_order') . " WHERE id = :id", array(':id' => $id));
if (!empty($item['transid'])) 
{
$this->changeWechatSend($id, 0, $_GPC['reson']);
}
pdo_update('bj_qmxk_order', array('status' => -1, 'remark' => $_GPC['remark']), array('id' => $id));
message('订单关闭操作成功！', referer(), 'success');
}
if (checksubmit('open')) 
{
pdo_update('bj_qmxk_order', array('status' => 0, 'remark' => $_GPC['remark']), array('id' => $id));
message('开启订单操作成功！', referer(), 'success');
}
$dispatch = pdo_fetch("SELECT * FROM " . tablename('bj_qmxk_dispatch') . " WHERE id = :id", array(':id' => $item['dispatch']));
if (!empty($dispatch) && !empty($dispatch['express'])) 
{
$express = pdo_fetch("select * from " . tablename('bj_qmxk_express') . " WHERE id=:id limit 1", array(":id" => $dispatch['express']));
}
$item['user'] = pdo_fetch("SELECT * FROM " . tablename('bj_qmxk_address') . " WHERE id = {$item['addressid']}
");
$goods = pdo_fetchall("SELECT g.id, g.title, g.status,g.thumb, g.unit,g.goodssn,g.productsn,g.marketprice,o.total,g.type,o.optionname,o.optionid,o.price as orderprice FROM " . tablename('bj_qmxk_order_goods') . " o left join " . tablename('bj_qmxk_goods') . " g on o.goodsid=g.id  WHERE o.orderid='{$id}
'");
$item['goods'] = $goods;
}
include $this->template('ordermy');
}
private function setOrderStock($id = '', $minus = true) 
{
$goods = pdo_fetchall("SELECT g.id, g.title, g.thumb, g.unit, g.marketprice,g.total as goodstotal,o.total,o.optionid,g.sales FROM " . tablename('bj_qmxk_order_goods') . " o left join " . tablename('bj_qmxk_goods') . " g on o.goodsid=g.id  WHERE o.orderid='{$id}
'");
foreach ($goods as $item) 
{
if ($minus) 
{
if (!empty($item['optionid'])) 
{
pdo_query("update " . tablename('bj_qmxk_goods_option') . " set stock=stock-:stock where id=:id", array(":stock" => $item['total'], ":id" => $item['optionid']));
}
$data = array();
if (!empty($item['goodstotal']) && $item['goodstotal'] != -1) 
{
$data['total'] = $item['goodstotal'] - $item['total'];
}
$data['sales'] = $item['sales'] + $item['total'];
pdo_update('bj_qmxk_goods', $data, array('id' => $item['id']));
}
else 
{
if (!empty($item['optionid'])) 
{
pdo_query("update " . tablename('bj_qmxk_goods_option') . " set stock=stock+:stock where id=:id", array(":stock" => $item['total'], ":id" => $item['optionid']));
}
$data = array();
if (!empty($item['goodstotal']) && $item['goodstotal'] != -1) 
{
$data['total'] = $item['goodstotal'] + $item['total'];
}
$data['sales'] = $item['sales'] - $item['total'];
pdo_update('bj_qmxk_goods', $data, array('id' => $item['id']));
}
}
}
public function doWebNotice() 
{
global $_GPC, $_W;
$operation = empty($_GPC['op']) ? 'display' : $_GPC['op'];
$operation = in_array($operation, array('display')) ? $operation : 'display';
$pindex = max(1, intval($_GPC['page']));
$psize = 50;
$starttime = empty($_GPC['starttime']) ? strtotime('-1 month') : strtotime($_GPC['starttime']);
$endtime = empty($_GPC['endtime']) ? TIMESTAMP : strtotime($_GPC['endtime']) + 86399;
$where .= " WHERE `weid` = :weid AND `createtime` >= :starttime AND `createtime` < :endtime";
$paras = array( ':weid' => $_W['weid'], ':starttime' => $starttime, ':endtime' => $endtime );
$keyword = $_GPC['keyword'];
if (!empty($keyword)) 
{
$where .= " AND `feedbackid`=:feedbackid";
$paras[':feedbackid'] = $keyword;
}
$type = empty($_GPC['type']) ? 0 : $_GPC['type'];
$type = intval($type);
if ($type != 0) 
{
$where .= " AND `type`=:type";
$paras[':type'] = $type;
}
$status = empty($_GPC['status']) ? 0 : intval($_GPC['status']);
$status = intval($status);
if ($status != -1) 
{
$where .= " AND `status` = :status";
$paras[':status'] = $status;
}
$total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('bj_qmxk_feedback') . $where, $paras);
$list = pdo_fetchall("SELECT * FROM " . tablename('bj_qmxk_feedback') . $where . " ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize, $paras);
$pager = pagination($total, $pindex, $psize);
$transids = array();
foreach ($list as $row) 
{
$transids[] = $row['transid'];
}
if (!empty($transids)) 
{
$sql = "SELECT * FROM " . tablename('bj_qmxk_order') . " WHERE weid='{$_W['weid']}
' AND transid IN ( '" . implode("','", $transids) . "' )";
$orders = pdo_fetchall($sql, array(), 'transid');
}
$addressids = array();
foreach ($orders as $transid => $order) 
{
$addressids[] = $order['addressid'];
}
$addresses = array();
if (!empty($addressids)) 
{
$sql = "SELECT * FROM " . tablename('bj_qmxk_address') . " WHERE weid='{$_W['weid']}
' AND id IN ( '" . implode("','", $addressids) . "' )";
$addresses = pdo_fetchall($sql, array(), 'id');
}
foreach ($list as &$feedback) 
{
$transid = $feedback['transid'];
$order = $orders[$transid];
$feedback['order'] = $order;
$addressid = $order['addressid'];
$feedback['address'] = $addresses[$addressid];
}
include $this->template('notice');
}
public function getCartTotal() 
{
global $_W;
$from_user = $this->getFromUser();
$cartotal = pdo_fetchcolumn("select sum(total) from " . tablename('bj_qmxk_cart') . " where weid = '{$_W['weid']}
' and from_user='".$from_user."'");
return empty($cartotal) ? 0 : $cartotal;
}
private function getFeedbackType($type) 
{
$types = array(1 => '维权', 2 => '告警');
return $types[intval($type)];
}
private function getFeedbackStatus($status) 
{
$statuses = array('未解决', '用户同意', '用户拒绝');
return $statuses[intval($status)];
}
public function doMobilePhb()
{
global $_W,$_GPC;
$from_user =$this->getFromUser();
$weid=$_W['weid'];
$op = $_GPC['op']?$_GPC['op']:'display';
$month = date('m', strtotime("-1 month"));
$premonth = strtotime(date('Y-m-1 00:00:00', strtotime("-1 month")));
$temptime = date('Y-m-1 00:00:00', strtotime("-1 month"));
$premonthed = strtotime(date('Y-m-d 23:59:59', strtotime("$temptime +1 month -1 day")));
$pindex = max(1, intval($_GPC['page']));
$psize = 15;
$commission = pdo_fetchall("select sum(c.commission) as commission, m.realname, m.mobile from ".tablename('bj_qmxk_commission')." as c left join ".tablename('bj_qmxk_member')." as m on c.weid = m.weid and c.mid = m.id where c.flag = 0 and m.realname !='' and c.weid = ".$_W['weid']." and c.createtime >= ".$premonth." and c.createtime <= ".$premonthed." group by c.mid order by sum(c.commission) desc, c.createtime desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
$total = pdo_fetchcolumn("select count(distinct c.mid) from ".tablename('bj_qmxk_commission')." as c left join ".tablename('bj_qmxk_member')." as m on c.weid = m.weid and c.mid = m.id where c.flag = 0 and c.weid = ".$_W['weid']." and m.realname !='' and c.createtime >= ".$premonth." and c.createtime <= ".$premonthed);
$pager = pagination1($total, $pindex, $psize);
include $this->template('phb');
}
public function doMobileFansIndex()
{
global $_W,$_GPC;
$from_user =$this->getFromUser();
$weid=$_W['weid'];
$op = $_GPC['op']?$_GPC['op']:'display';
$profile = pdo_fetch('SELECT * FROM '.tablename('bj_qmxk_member')." WHERE  weid = :weid  AND from_user = :from_user" , array(':weid' => $_W['weid'],':from_user' => $from_user));
if(!empty($profile))
{
$count1 = pdo_fetchcolumn("select count(*) from (select from_user from ".tablename('bj_qmxk_order')." where  shareid = ".$profile['id'].'  group by from_user'.") x");
$count1_2 = pdo_fetchcolumn("select count(mber.id) from ".tablename('bj_qmxk_member')." mber where mber.shareid = ".$profile['id']." and mber.from_user not in (select orders.from_user from ".tablename('bj_qmxk_order')." orders where  orders.shareid = ".$profile['id']." group by from_user)");
$count1=$count1+$count1_2;
if($count1>0) 
{
$countall = pdo_fetch("select id from ".tablename('bj_qmxk_member')." where shareid = ".$profile['id']);
$count2=0;
$count3=0;
foreach ($countall as &$citem)
{
$tcount2 = pdo_fetchcolumn("select count(id) from ".tablename('bj_qmxk_member')." where shareid = ".$citem);
$count2=$count2+$tcount2;
$count2all = pdo_fetch("select id from ".tablename('bj_qmxk_member')." where shareid = ".$citem);
foreach ($count2all as &$citem2)
{
$tcount3 = pdo_fetchcolumn("select count(*) from (select from_user from ".tablename('bj_qmxk_order')." where  shareid = ".$citem2.' and shareid!='.$citem.' and shareid!='.$profile['id'].' group by from_user'.") y" );
$count3=$count3+$tcount3;
}
}
}
else 
{
$count1=0;
$count2=0;
$count3=0;
}
$count1=$count1+$count2+$count3;
}
else 
{
$count1=0;
}
$id = $profile['id'];
if(intval($profile['id']) && $profile['status']==0)
{
include $this->template('forbidden');
exit;
}
if(empty($profile))
{
$rule = pdo_fetch('SELECT * FROM '.tablename('bj_qmxk_rules')." WHERE `weid` = :weid ",array(':weid' => $_W['weid']));
$profile =fans_search($from_user, array('realname'));
$cfg = $this->module['config'];
$ydyy = $cfg['ydyy'];
include $this->template('register');
exit;
}
$theone = pdo_fetch('SELECT * FROM '.tablename('bj_qmxk_rules')." WHERE  weid = :weid" , array(':weid' => $_W['weid']));
if($theone['promotertimes'] == 0 && $profile['flag'] == 0)
{
$isorder = pdo_fetch('SELECT * FROM '.tablename('bj_qmxk_order')." WHERE status= '3' AND  weid = :weid  AND from_user = :from_user" , array(':weid' => $_W['weid'],':from_user' => $from_user));
if(!$isorder)
{
message('您还未通过分销员审核，请先购买一笔订单才能成为分销员！', $this->createMobileUrl('list',array('mid'=>$id)), 'success');
}
else
{
pdo_update('bj_qmxk_member', array('flag' => 1), array('id' => $profile['id']));
$profile['flag'] = 1;
}
}
else
{
if(empty($profile['flagtime'])||$profile['flag']!=1) 
{
pdo_update('bj_qmxk_member', array('flagtime'=>TIMESTAMP), array('id' => $profile['id']));
}
pdo_update('bj_qmxk_member', array('flag' => 1), array('id' => $profile['id']));
}
$myheadimg = pdo_fetch('SELECT avatar,credit1 FROM '.tablename('fans')." WHERE  weid = :weid  AND from_user = :from_user LIMIT 1" , array(':weid' => $_W['weid'],':from_user' => $from_user));
$share = "bj_qmxkshareQrcode".$_W['weid'];
if($_COOKIE[$share] != $_W['weid']."share".$id)
{
include "mobile/phpqrcode.php";
$value = $_W['siteroot'].$this->createMobileUrl('list',array('mid'=>$id));
$errorCorrectionLevel = "L";
$matrixPointSize = "4";
$imgname = "share$id.png";
$imgurl = "source/modules/bj_qmxk/style/images/share/$imgname";
QRcode::png($value, $imgurl, $errorCorrectionLevel, $matrixPointSize);
setCookie($share, $_W['weid']."share".$id, time()+3600*24);
}
$commtime = pdo_fetch("select commtime, promotertimes from ".tablename('bj_qmxk_rules')." where weid = ".$_W['weid']);
$commissioningpe =0;
if(empty($commtime) && $commtime['commtime']<=0)
{
$commtime = array();
$commtime['commtime']=0;
}
$moneytime = time()-3600*24*$commtime['commtime'];
$userx = pdo_fetch("select * from ".tablename('bj_qmxk_member')." where from_user = '".$from_user."'");
$commissioningpe = pdo_fetchcolumn("SELECT sum((g.commission*g.total)) FROM " .tablename('bj_qmxk_order')." as o left join ".tablename('bj_qmxk_order_goods')." as g on o.id = g.orderid and o.weid = g.weid WHERE o.shareid = ".$id." and o.weid = ".$_W['weid']." and (g.status = 0 or g.status = 1) and o.status >= 3 and o.from_user != '".$from_user."' and  g.createtime>=".$userx['flagtime']);
if(empty($commissioningpe)) 
{
$commissioningpe =0;
}
include $this->template('fshome');
}
public function doMobileRegister()
{
global $_W,$_GPC;
$from_user =$this->getFromUser();
$weid=$_W['weid'];
$op = $_GPC['op']?$_GPC['op']:'display';
$profile = pdo_fetch('SELECT * FROM '.tablename('bj_qmxk_member')." WHERE `weid` = :weid AND from_user=:from_user ",array(':weid' => $_W['weid'],':from_user' => $from_user));
$id = $profile['id'];
if($op=='display')
{
$opp = $_GPC['opp'];
$rule = pdo_fetch('SELECT * FROM '.tablename('bj_qmxk_rules')." WHERE `weid` = :weid ",array(':weid' => $_W['weid']));
$fans = fans_search($from_user, array('realname'));
if(empty($profile['realname']))
{
$profile['realname']=$fans['realname'];
}
$cfg = $this->module['config'];
$ydyy = $cfg['ydyy'];
include $this->template('register');
exit;
}
if(!empty($profile))
{
$data=array( 'realname'=>$_GPC['realname'], 'mobile'=>$_GPC['mobile'], 'pwd'=>$_GPC['password'], 'bankcard'=>$_GPC['bankcard'], 'banktype'=>$_GPC['banktype'], 'alipay'=>$_GPC['alipay'], 'wxhao'=>$_GPC['wxhao'], );
pdo_update('bj_qmxk_member',$data, array('id'=>$profile['id']));
echo 2;
exit;
}
if($op=='add')
{
$shareid = 'bj_qmxk_sid07'.$_W['weid'];
$seid=$_COOKIE[$shareid];
if(empty($seid)) 
{
$seid=0;
}
$theone = pdo_fetch('SELECT * FROM '.tablename('bj_qmxk_rules')." WHERE  weid = :weid" , array(':weid' => $_W['weid']));
if($theone['promotertimes'] == 1)
{
$data=array( 'weid'=>$_W['weid'], 'from_user'=> $from_user, 'realname'=>$_GPC['realname'], 'mobile'=>$_GPC['mobile'], 'pwd'=>$_GPC['password'], 'alipay'=>$_GPC['alipay'], 'wxhao'=>$_GPC['wxhao'], 'commission'=>0, 'createtime'=>TIMESTAMP, 'flagtime'=>TIMESTAMP, 'shareid'=> $seid, 'status'=>1, 'flag'=>1 );
}
else
{
$data=array( 'weid'=>$_W['weid'], 'from_user'=> $from_user, 'realname'=>$_GPC['realname'], 'mobile'=>$_GPC['mobile'], 'pwd'=>$_GPC['password'], 'alipay'=>$_GPC['alipay'], 'wxhao'=>$_GPC['wxhao'], 'commission'=>0, 'createtime'=>TIMESTAMP, 'flagtime'=>TIMESTAMP, 'shareid'=> $seid, 'status'=>1, 'flag'=>0 );
}
$profile = pdo_fetch('SELECT from_user,id FROM '.tablename('bj_qmxk_member')." WHERE `weid` = :weid AND from_user=:from_user ",array(':weid' => $_W['weid'],':from_user' => $from_user));
if($data['from_user']==$profile['from_user'])
{
echo '-2';
exit;
}
pdo_insert('bj_qmxk_member',$data);
$theone = pdo_fetch('SELECT * FROM '.tablename('bj_qmxk_rules')." WHERE  weid = :weid" , array(':weid' => $_W['weid']));
echo 1;
exit;
}
}
public function doMobileCommission()
{
global $_W,$_GPC;
$from_user =$this->getFromUser();
$weid=$_W['weid'];
$op = $_GPC['op']?$_GPC['op']:'display';
$profile = pdo_fetch('SELECT * FROM '.tablename('bj_qmxk_member')." WHERE weid = :weid  AND from_user = :from_user" , array(':weid' => $_W['weid'],':from_user' => $from_user));
$id = $profile['id'];
if(intval($profile['id']) && $profile['status']==0)
{
include $this->template('forbidden');
exit;
}
if(empty($profile))
{
message('请先注册',$this->createMobileUrl('register'),'error');
exit;
}
if($op=='display')
{
$commtime = pdo_fetch("select commtime, promotertimes from ".tablename('bj_qmxk_rules')." where weid = ".$_W['weid']);
$commissioningpe =0;
if(empty($commtime) && $commtime['commtime']<=0)
{
$commtime = array();
$commtime['commtime']=0;
}
$moneytime = time()-3600*24*$commtime['commtime'];
$userx = pdo_fetch("select * from ".tablename('bj_qmxk_member')." where from_user = '".$from_user."'");
$commissioningpe = pdo_fetchcolumn("SELECT sum((g.commission*g.total)) FROM " .tablename('bj_qmxk_order')." as o left join ".tablename('bj_qmxk_order_goods')." as g on o.id = g.orderid and o.weid = g.weid WHERE o.shareid = ".$id." and o.weid = ".$_W['weid']." and (g.status = 0 or g.status = 1) and o.status >= 3 and o.from_user != '".$from_user."' and  g.createtime>=".$userx['flagtime']);
if(empty($commissioningpe)) 
{
$commissioningpe =0;
}
$commissioning = pdo_fetchcolumn("select sum(commission) from ".tablename('bj_qmxk_commission')." where flag = 0 and mid = ".$profile['id']." and weid = ".$_W['weid']);
$commissioning = empty($commissioning)?0:$commissioning;
$commissioned = $profile['commission'];
$total = pdo_fetchcolumn("select count(id) from ". tablename('bj_qmxk_commission'). " where mid =". $profile['id']. " and flag = 0");
if($_GPC['opp'] == 'more')
{
$opp = 'more';
$pindex = max(1, intval($_GPC['page']));
$psize = 15;
$list = pdo_fetchall("select co.isshare,co.commission, co.createtime, og.orderid, og.goodsid, og.total,oo.ordersn from ". tablename('bj_qmxk_commission'). " as co left join ".tablename('bj_qmxk_order_goods')." as og on co.ogid = og.id and co.weid = og.weid left join ".tablename('bj_qmxk_order')." as oo on oo.id = og.orderid and co.weid = og.weid where co.mid =". $profile['id']. " and co.flag = 0 ORDER BY co.createtime DESC limit ".($pindex - 1) * $psize . ',' . $psize);
$pager = pagination1($total, $pindex, $psize);
}
else
{
$list = pdo_fetchall("select co.isshare,co.commission, co.createtime, og.orderid, og.goodsid, og.total,oo.ordersn from ". tablename('bj_qmxk_commission'). " as co left join ".tablename('bj_qmxk_order_goods')." as og on co.ogid = og.id and co.weid = og.weid left join ".tablename('bj_qmxk_order')." as oo on oo.id = og.orderid and co.weid = og.weid where co.mid =". $profile['id']. " and co.flag = 0 ORDER BY co.createtime DESC limit 10");
}
$addresss = pdo_fetchall("select id, realname from ".tablename('bj_qmxk_address')." where weid = ".$_W['weid']);
$address = array();
foreach($addresss as $adr)
{
$address[$adr['id']] = $adr['realname'];
}
$goods = pdo_fetchall("select id, title from ".tablename('bj_qmxk_goods')." where weid = ".$_W['weid']);
$good = array();
foreach($goods as $g)
{
$good[$g['id']] = $g['title'];
}
}
if($op=='commapply')
{
$commtime = pdo_fetch("select commtime, promotertimes from ".tablename('bj_qmxk_rules')." where weid = ".$_W['weid']);
if(empty($commtime) && $commtime['commtime']<0)
{
message("此功能还未开放，请耐心等待...");
}
$moneytime = time()-3600*24*$commtime['commtime'];
$pindex = max(1, intval($_GPC['page']));
$psize = 15;
$user = pdo_fetch("select * from ".tablename('bj_qmxk_member')." where from_user = '".$from_user."'");
$list = pdo_fetchall("SELECT o.createtime, g.commission, g.total, g.goodsid, g.id,o.ordersn FROM " .tablename('bj_qmxk_order')." as o left join ".tablename('bj_qmxk_order_goods')." as g on o.id = g.orderid and o.weid = g.weid WHERE o.shareid = ".$id." and o.weid = ".$_W['weid']." and g.status = 0 and o.status >= 3 and o.from_user != '".$from_user."' and g.createtime < ".$moneytime." and g.createtime>=".$user['flagtime']." ORDER BY o.createtime DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
$total = pdo_fetchcolumn("SELECT count(g.id) FROM " .tablename('bj_qmxk_order')." as o left join ".tablename('bj_qmxk_order_goods')." as g on o.id = g.orderid and o.weid = g.weid WHERE o.shareid = ".$id." and o.weid = ".$_W['weid']." and o.status = 3 and g.createtime < ".$moneytime." and g.createtime>=".$user['flagtime']);
if($profile['flag']==0)
{
if($total>=$commtime['promotertimes'])
{
pdo_update('bj_qmxk_member', array('flag'=>1), array('id'=>$profile['id']));
$profile['flag'] = 1;
}
}
$pager = pagination1($total, $pindex, $psize);
$goods = pdo_fetchall("select id, title from ".tablename('bj_qmxk_goods'). " where weid = ".$_W['weid']. " and status = 1");
$good = array();
foreach($goods as $g)
{
$good[$g['id']] = $g['title'];
}
include $this->template('commapply');
exit;
}
if($op=='applyed')
{
if($profile['flag']==0)
{
message('申请佣金失败！');
}
$isbank = pdo_fetch("select id, bankcard, banktype from ".tablename('bj_qmxk_member')." where weid = ".$_W['weid']." and from_user = '".$from_user."'");
if(empty($isbank['bankcard']) || empty($isbank['banktype']))
{
message('请先完善银行卡信息！', $this->createMobileUrl('bankcard', array('id'=>$isbank['id'], 'opp'=>'complated')), 'error');
}
$update = array( 'status'=>1, 'applytime'=>time() );
$selected = explode(',',trim($_GPC['selected']));
for($i=0; $i<sizeof($selected);
$i++)
{
$temp = pdo_update('bj_qmxk_order_goods', $update, array('id'=>$selected[$i]));
}
if(!$temp)
{
message('申请失败，请重新申请！', $this->createMobileUrl('commission', array('op'=>'commapply')), 'error');
}
else
{
message('申请成功！', $this->createMobileUrl('commission'), 'success');
}
}
include $this->template('commission');
}
public function doMobileBankcard()
{
global $_W,$_GPC;
$from_user =$this->getFromUser();
$weid=$_W['weid'];
$op = $_GPC['op']?$_GPC['op']:'display';
$rule = pdo_fetch('SELECT * FROM '.tablename('bj_qmxk_rule')." WHERE `weid` = :weid ",array(':weid' => $_W['weid']));
if(empty($from_user))
{
message('你想知道怎么加入么?',$rule['gzurl'],'sucessr');
exit;
}
$profile= pdo_fetch('SELECT * FROM '.tablename('bj_qmxk_member')." WHERE  weid = :weid  AND from_user = :from_user" , array(':weid' => $_W['weid'],':from_user' => $from_user));
if(intval($profile['id']) && $profile['status']==0)
{
include $this->template('forbidden');
exit;
}
if(empty($profile))
{
message('请先注册',$this->createMobileUrl('register'),'error');
exit;
}
if($op=='edit')
{
$data=array( 'bankcard'=>$_GPC['bankcard'], 'banktype'=>$_GPC['banktype'], 'alipay'=>$_GPC['alipay'], 'wxhao'=>$_GPC['wxhao'] );
if(!empty($data['bankcard']) && !empty($data['banktype']))
{
pdo_update('bj_qmxk_member',$data,array('from_user' => $from_user));
if($_GPC['opp']=='complated')
{
echo 3;
exit;
}
echo 1;
}
else
{
echo 0;
}
exit;
}
include $this->template('bankcard');
}
public function doMobileFansorder()
{
global $_W,$_GPC;
$from_user =$this->getFromUser();
$weid=$_W['weid'];
$op = $_GPC['op']?$_GPC['op']:'display';
$profile = pdo_fetch('SELECT * FROM '.tablename('bj_qmxk_member')." WHERE  weid = :weid  AND from_user = :from_user" , array(':weid' => $_W['weid'],':from_user' => $from_user));
$id = $profile['id'];
if(intval($profile['id']) && $profile['status']==0)
{
include $this->template('forbidden');
exit;
}
if(empty($profile))
{
message('请先注册',$this->createMobileUrl('register'),'error');
exit;
}
$pindex = max(1, intval($_GPC['page']));
$psize = 20;
$list = pdo_fetchall("SELECT o.createtime,o.ordersn,o.status, g.commission, g.total, g.goodsid FROM " . tablename('bj_qmxk_order') . " as o left join ".tablename('bj_qmxk_order_goods')." as g on o.id = g.orderid and o.weid = g.weid WHERE o.shareid = ".$id." and o.weid = ".$_W['weid']." and o.from_user<>'".$profile['from_user']."' ORDER BY o.createtime DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
$goods = pdo_fetchall("select id, title from ".tablename('bj_qmxk_goods'). " where weid = ".$_W['weid']. " and status = 1");
$good = array();
foreach($goods as $g)
{
$good[$g['id']] = $g['title'];
}
$total = pdo_fetchcolumn('SELECT COUNT(id) FROM ' .tablename('bj_qmxk_order'). " WHERE weid = ".$_W['weid']." AND shareid = ".$id);
$pager = pagination1($total, $pindex, $psize);
include $this->template('fansorder');
}
public function doMobileRule()
{
global $_W,$_GPC;
$from_user =$this->getFromUser();
$weid=$_W['weid'];
$op = $_GPC['op']?$_GPC['op']:'display';
$rule = pdo_fetchcolumn('SELECT rule FROM '.tablename('bj_qmxk_rules')." WHERE weid = :weid" , array(':weid' => $_W['weid']));
$id = pdo_fetchcolumn('SELECT id FROM '.tablename('bj_qmxk_member')." WHERE weid = :weid AND from_user = :from_user" , array(':weid' => $_W['weid'],':from_user' => $from_user));
include $this->template('rule');
}
public function doWebfansmanager()
{
global $_W,$_GPC;
//$this->doWebAuth();
checklogin();
$weid=$_W['weid'];
$op= $operation = $_GPC['op']?$_GPC['op']:'display';
if($op=='display')
{
$pindex = max(1, intval($_GPC['page']));
$psize = 20;
$list = pdo_fetchall("select qmxk.*,fans.credit1 from ".tablename('bj_qmxk_member'). " qmxk left join ".tablename('fans'). " fans on qmxk.from_user=fans.from_user where qmxk.flag = 1 and qmxk.weid = ".$_W['weid']." ORDER BY qmxk.id DESC limit ".($pindex - 1) * $psize . ',' . $psize);
$total = pdo_fetchcolumn("select count(id) from". tablename('bj_qmxk_member'). "where flag = 1 and weid =".$_W['weid']);
;
$pager = pagination1($total, $pindex, $psize);
$commissions = pdo_fetchall("select mid, sum(commission) as commission from ".tablename('bj_qmxk_commission')." where weid = ".$_W['weid']." and flag = 0 group by mid");
$commission = array();
foreach($commissions as $c)
{
$commission[$c['mid']] = $c['commission'];
}
}
if($op=='nocheck')
{
$pindex = max(1, intval($_GPC['page']));
$psize = 20;
$list = pdo_fetchall("select qmxk.*,fans.credit1 from ".tablename('bj_qmxk_member'). " qmxk left join ".tablename('fans'). " fans on qmxk.from_user=fans.from_user where qmxk.flag = 0 and qmxk.weid = ".$_W['weid']." ORDER BY qmxk.id DESC limit ".($pindex - 1) * $psize . ',' . $psize);
$total = pdo_fetchcolumn("select count(id) from". tablename('bj_qmxk_member'). "where flag = 0 and weid =".$_W['weid']);
;
$pager = pagination1($total, $pindex, $psize);
include $this->template('fansmanagered');
exit;
}
if($op=='sort')
{
$sort = array( 'realname'=>$_GPC['realname'], 'mobile'=>$_GPC['mobile'] );
if($_GPC['opp']=='nocheck')
{
$status = 0;
}
else 
{
$status = 1;
}
$list = pdo_fetchall("select * from". tablename('bj_qmxk_member')."where flag = ".$status." and weid =".$_W['weid'].".and realname like '%".$sort['realname']. "%' and mobile like '%".$sort['mobile']. "%' ORDER BY id DESC");
$commissions = pdo_fetchall("select mid, sum(commission) as commission from ".tablename('bj_qmxk_commission')." where weid = ".$_W['weid']." and flag = 0 group by mid");
$commission = array();
foreach($commissions as $c)
{
$commission[$c['mid']] = $c['commission'];
}
if($_GPC['opp']=='nocheck')
{
include $this->template('fansmanagered');
exit;
}
}
if($op=='delete')
{
$temp = pdo_delete('bj_qmxk_member', array('id'=>$_GPC['id']));
if(empty($temp))
{
if($_GPC['opp']=='nocheck')
{
message('删除失败，请重新删除！', $this->createWebUrl('fansmanager', array('op'=>'nocheck')), 'error');
}
else 
{
message('删除失败，请重新删除！', $this->createWebUrl('fansmanager'), 'error');
}
}
else
{
if($_GPC['opp']=='nocheck')
{
message('删除成功！', $this->createWebUrl('fansmanager', array('op'=>'nocheck')), 'success');
}
else 
{
message('删除成功！', $this->createWebUrl('fansmanager'), 'success');
}
}
}
if($op=='detail')
{
$id = $_GPC['id'];
$user = pdo_fetch("select * from ".tablename('bj_qmxk_member'). " where id = ".$id);
if($_GPC['opp']=='nocheck')
{
include $this->template('fansmanagered_detail');
}
else 
{
include $this->template('fansmanager_detail');
}
exit;
}
if($op=='status')
{
$status = array( 'status'=>$_GPC['status'], 'flag'=>$_GPC['flag'], 'content'=>trim($_GPC['content']) );
if($_GPC['opp']=='nocheck'&&$_GPC['flag']==1)
{
$status ['flagtime']=TIMESTAMP;
}
$temp = pdo_update('bj_qmxk_member', $status, array('id'=>$_GPC['id']));
if(empty($temp))
{
if($_GPC['opp']=='nocheck')
{
message('设置用户权限失败，请重新设置！', $this->createWebUrl('fansmanager', array('op'=>'detail', 'opp'=>'nocheck', 'id'=>$_GPC['id'])), 'error');
}
else 
{
message('设置用户权限失败，请重新设置！', $this->createWebUrl('fansmanager', array('op'=>'detail', 'id'=>$_GPC['id'])), 'error');
}
}
else
{
if($_GPC['opp']=='nocheck')
{
message('设置用户权限成功！', $this->createWebUrl('fansmanager', array('op'=>'nocheck')), 'success');
}
else 
{
message('设置用户权限成功！', $this->createWebUrl('fansmanager'), 'success');
}
}
}
if($op=='recharge')
{
$id = $_GPC['id'];
if($_GPC['opp']=='recharged')
{
if(!is_numeric($_GPC['commission']))
{
message('佣金请输入合法数字！', '', 'error');
}
$recharged = array( 'weid'=>$_W['weid'], 'mid'=>$id, 'flag'=>1, 'content'=>trim($_GPC['content']), 'commission'=>$_GPC['commission'], 'createtime'=>time() );
$temp = pdo_insert('bj_qmxk_commission', $recharged);
$commission = pdo_fetchcolumn("select commission from ".tablename('bj_qmxk_member'). " where id = ".$id);
if(empty($temp))
{
message('充值失败，请重新充值！', $this->createWebUrl('fansmanager', array('op'=>'recharge', 'id'=>$_GPC['id'])), 'error');
}
else
{
pdo_update('bj_qmxk_member', array('commission'=>$commission+$_GPC['commission']), array('id'=>$id));
message('充值成功！', $this->createWebUrl('fansmanager', array('op'=>'recharge', 'id'=>$_GPC['id'])), 'success');
}
}
$user = pdo_fetch("select * from ".tablename('bj_qmxk_member'). " where id = ".$id);
$commission = pdo_fetchcolumn("select sum(commission) from ".tablename('bj_qmxk_commission')." where mid = ".$id." and flag = 0 and weid = ".$_W['weid']);
$commission = empty($commission)?0:$commission;
$commission = $commission - $user['commission'];
$commissions = pdo_fetchall("select * from ".tablename('bj_qmxk_commission')." where mid = ".$id." and weid = ".$_W['weid']." and flag = 1");
include $this->template('fansmanager_recharge');
exit;
}
include $this->template('fansmanager');
}
public function doWebCommission()
{
global $_W,$_GPC;
//$this->doWebAuth();
checklogin();
$weid=$_W['weid'];
$op= $operation = $_GPC['op']?$_GPC['op']:'display';
$members = pdo_fetchall("select id, realname, mobile from ".tablename('bj_qmxk_member')." where weid = ".$_W['weid']." and status = 1");
$member = array();
foreach($members as $m)
{
$member['realname'][$m['id']] = $m['realname'];
$member['mobile'][$m['id']] = $m['mobile'];
}
if($op=='display')
{
if($_GPC['opp']=='check')
{
$shareid = $_GPC['shareid'];
$user = pdo_fetch("select realname, mobile from ".tablename('bj_qmxk_member')." where id = ".$_GPC['shareid']);
$info = pdo_fetch("select og.id, og.total, og.price, og.status, og.commission, og.commission2,og.commission3, og.applytime, og.content, g.title from ".tablename('bj_qmxk_order_goods')." as og left join ".tablename('bj_qmxk_goods')." as g on og.goodsid = g.id and og.weid = g.weid where og.id = ".$_GPC['id']);
include $this->template('applying_detail');
exit;
}
if($_GPC['opp']=='checked')
{
$checked = array( 'status'=>$_GPC['status'], 'checktime'=>time(), 'content'=>trim($_GPC['content']) );
$temp = pdo_update('bj_qmxk_order_goods', $checked, array('id'=>$_GPC['id']));
if(empty($temp))
{
message('审核失败，请重新审核！', $this->createWebUrl('commission', array('opp'=>'check', 'shareid'=>$_GPC['shareid'], 'id'=>$_GPC['id'])), 'error');
}
else
{
message('审核成功！', $this->createWebUrl('commission'), 'success');
}
}
if($_GPC['opp']=='sort')
{
$sort = array( 'realname'=>$_GPC['realname'], 'mobile'=>$_GPC['mobile'] );
$shareid = "select id from ".tablename('bj_qmxk_member')." where weid = ".$_W['weid']." and realname like '%".$sort['realname']."%' and mobile like '%".$sort['mobile']."%'";
$list = pdo_fetchall("select o.shareid, o.status, g.id, g.applytime from ".tablename('bj_qmxk_order')." as o left join ".tablename('bj_qmxk_order_goods'). " as g on o.id = g.orderid and o.weid = g.weid where o.weid = ".$_W['weid']." and g.status = 1 and o.shareid in (".$shareid.") ORDER BY o.id desc");
$total = sizeof($list);
}
else
{
$pindex = max(1, intval($_GPC['page']));
$psize = 20;
$list = pdo_fetchall("select o.shareid, o.status, g.id, g.applytime from ".tablename('bj_qmxk_order'). " as o left join ".tablename('bj_qmxk_order_goods'). " as g on o.id = g.orderid and o.weid = g.weid where o.weid = ".$_W['weid']." and g.status = 1 ORDER BY o.id DESC limit ".($pindex - 1) * $psize . ',' . $psize);
$total = pdo_fetchcolumn("select count(o.id) from ".tablename('bj_qmxk_order')." as o left join ".tablename('bj_qmxk_order_goods'). " as g on o.id = g.orderid and o.weid = g.weid where o.weid = ".$_W['weid']." and g.status = 1");
$pager = pagination1($total, $pindex, $psize);
}
include $this->template('applying');
exit;
}
if($op=='applyed')
{
if($_GPC['opp']=='jieyong')
{
$shareid = $_GPC['shareid'];
$user = pdo_fetch("select id, realname, mobile,shareid from ".tablename('bj_qmxk_member')." where id = ".$_GPC['shareid']);
$info = pdo_fetch("select og.id, og.total, og.price, og.status, og.commission, og.commission2,og.commission3, og.applytime, og.content, g.title from ".tablename('bj_qmxk_order_goods')." as og left join ".tablename('bj_qmxk_goods')." as g on og.goodsid = g.id and og.weid = g.weid where og.id = ".$_GPC['id']);
$commissions = pdo_fetchall("select * from ".tablename('bj_qmxk_commission')." where ogid = ".$_GPC['id'].' and mid='.$_GPC['shareid']);
$commission = pdo_fetchcolumn("select sum(commission) from ".tablename('bj_qmxk_commission')." where isshare!=1 and ogid = ".$_GPC['id'].' and mid='.$_GPC['shareid']);
$commission = empty($commission)?0:$commission;
if(!empty($user['shareid'])) 
{
$commission2 = pdo_fetchcolumn("select sum(commission) from ".tablename('bj_qmxk_commission')." where isshare=1 and ogid = ".$_GPC['id'].' and mid='.$user['shareid']);
$commission2 = empty($commission2)?0:$commission2;
$user2 = pdo_fetch("select id, realname, mobile,shareid from ".tablename('bj_qmxk_member')." where id = ".$user['shareid']);
if(!empty($user2['shareid'])) 
{
$commission3 = pdo_fetchcolumn("select sum(commission) from ".tablename('bj_qmxk_commission')." where isshare=1 and ogid = ".$_GPC['id'].' and mid='.$user2['shareid']);
$commission3 = empty($commission3)?0:$commission3;
$user3 = pdo_fetch("select id, realname, mobile,shareid from ".tablename('bj_qmxk_member')." where id = ".$user2['shareid']);
}
else 
{
$commission3 =0;
}
}
else 
{
$commission2 =0;
}
include $this->template('applyed_detail');
exit;
}
if($_GPC['opp']=='jieyonged')
{
if($_GPC['status']==2)
{
if(!is_numeric($_GPC['commission'])||!is_numeric($_GPC['commission2'])||!is_numeric($_GPC['commission3']))
{
message('佣金请输入合法数字！', '', 'error');
}
$shareid = $_GPC['shareid'];
$ogid = $_GPC['id'];
$commission = array( 'weid'=>$_W['weid'], 'mid'=>$shareid, 'ogid'=>$ogid, 'commission'=>$_GPC['commission'], 'content'=>trim($_GPC['content']), 'isshare'=>0, 'createtime'=>time() );
if($_GPC['commission']>0) 
{
$temp = pdo_insert('bj_qmxk_commission', $commission);
}
$user = pdo_fetch("select id,shareid from ".tablename('bj_qmxk_member')." where id = ".$_GPC['shareid']);
if(!empty($user['shareid'])) 
{
$user2 = pdo_fetch("select id from ".tablename('bj_qmxk_member')." where flag=1 and id = ".$user['shareid']);
if(!empty($user2))
{
if(!empty($_GPC['commission2']))
{
$commission2 = array( 'weid'=>$_W['weid'], 'mid'=>$user['shareid'], 'ogid'=>$ogid, 'commission'=>$_GPC['commission2'], 'content'=>trim($_GPC['content']), 'isshare'=>1, 'createtime'=>time() );
if($_GPC['commission2']>0) 
{
pdo_insert('bj_qmxk_commission', $commission2);
}
}
}
}
if(!empty($user2['id'])) 
{
$nuser2 = pdo_fetch("select shareid from ".tablename('bj_qmxk_member')." where id = ".$user2['id']);
}
if(!empty($nuser2['shareid'])) 
{
$nuser3 = pdo_fetch("select id from ".tablename('bj_qmxk_member')." where flag=1 and id = ".$nuser2['shareid']);
if(!empty($nuser3))
{
if(!empty($_GPC['commission3']))
{
$commission3 = array( 'weid'=>$_W['weid'], 'mid'=>$nuser2['shareid'], 'ogid'=>$ogid, 'commission'=>$_GPC['commission3'], 'content'=>trim($_GPC['content']), 'isshare'=>1, 'createtime'=>time() );
if($_GPC['commission3']>0) 
{
pdo_insert('bj_qmxk_commission', $commission3);
}
}
}
}
if($_GPC['commission']>0&&!empty($shareid)) 
{
$recharged = array( 'weid'=>$_W['weid'], 'mid'=>$shareid, 'flag'=>1, 'content'=>trim($_GPC['content']), 'commission'=>$_GPC['commission'], 'createtime'=>time() );
$temp = pdo_insert('bj_qmxk_commission', $recharged);
if(empty($temp))
{
message('充值失败，请重新充值！', $this->createWebUrl('commission', array('op'=>'applyed', 'opp'=>'jieyong', 'shareid'=>$_GPC['shareid'], 'id'=>$_GPC['id'])), 'error');
}
else
{
$commission = pdo_fetchcolumn("select commission from ".tablename('bj_qmxk_member'). " where id = ".$shareid);
pdo_update('bj_qmxk_member', array('commission'=>$commission+$_GPC['commission']), array('id'=>$shareid));
}
}
if($_GPC['commission2']>0&&!empty($user['shareid'])) 
{
$recharged = array( 'weid'=>$_W['weid'], 'mid'=>$user['shareid'], 'flag'=>1, 'content'=>trim($_GPC['content']), 'commission'=>$_GPC['commission2'], 'createtime'=>time() );
$temp = pdo_insert('bj_qmxk_commission', $recharged);
if(empty($temp))
{
message('充值失败，请重新充值！', $this->createWebUrl('commission', array('op'=>'applyed', 'opp'=>'jieyong', 'shareid'=>$_GPC['shareid'], 'id'=>$_GPC['id'])), 'error');
}
else
{
$commission = pdo_fetchcolumn("select commission from ".tablename('bj_qmxk_member'). " where id = ".$user['shareid']);
pdo_update('bj_qmxk_member', array('commission'=>$commission+$_GPC['commission2']), array('id'=>$user['shareid']));
}
}
if($_GPC['commission3']>0&&!empty($nuser2['shareid'])) 
{
$recharged = array( 'weid'=>$_W['weid'], 'mid'=>$nuser2['shareid'], 'flag'=>1, 'content'=>trim($_GPC['content']), 'commission'=>$_GPC['commission3'], 'createtime'=>time() );
$temp = pdo_insert('bj_qmxk_commission', $recharged);
if(empty($temp))
{
message('充值失败，请重新充值！', $this->createWebUrl('commission', array('op'=>'applyed', 'opp'=>'jieyong', 'shareid'=>$_GPC['shareid'], 'id'=>$_GPC['id'])), 'error');
}
else
{
$commission = pdo_fetchcolumn("select commission from ".tablename('bj_qmxk_member'). " where id = ".$nuser2['shareid']);
pdo_update('bj_qmxk_member', array('commission'=>$commission+$_GPC['commission3']), array('id'=>$nuser2['shareid']));
}
}
message('充值成功！', $this->createWebUrl('commission', array('op'=>'applyed', 'opp'=>'jieyong', 'shareid'=>$_GPC['shareid'], 'id'=>$_GPC['id'])), 'success');
}
else
{
$checked = array( 'status'=>$_GPC['status'], 'content'=>trim($_GPC['content']) );
$temp = pdo_update('bj_qmxk_order_goods', $checked, array('id'=>$_GPC['id']));
if(empty($temp))
{
message('提交失败，请重新提交！', $this->createWebUrl('commission', array('op'=>'applyed', 'opp'=>'jieyong', 'shareid'=>$_GPC['shareid'], 'id'=>$_GPC['id'])), 'error');
}
else
{
message('提交成功！', $this->createWebUrl('commission', array('op'=>'applyed')), 'success');
}
}
}
if($_GPC['opp']=='sort')
{
$sort = array( 'realname'=>$_GPC['realname'], 'mobile'=>$_GPC['mobile'] );
$shareid = "select id from ".tablename('bj_qmxk_member')." where weid = ".$_W['weid']." and realname like '%".$sort['realname']."%' and mobile like '%".$sort['mobile']."%'";
$list = pdo_fetchall("select o.shareid, o.status, g.id, g.checktime from ".tablename('bj_qmxk_order'). " as o left join ".tablename('bj_qmxk_order_goods'). " as g on o.id = g.orderid and o.weid = g.weid where o.weid = ".$_W['weid']." and g.status = 2 and o.shareid in (".$shareid.") ORDER BY o.id desc");
$total = sizeof($list);
}
else
{
$pindex = max(1, intval($_GPC['page']));
$psize = 20;
$list = pdo_fetchall("select o.shareid, o.status, g.id, g.checktime from ".tablename('bj_qmxk_order')." as o left join ".tablename('bj_qmxk_order_goods')." as g on o.id = g.orderid and o.weid = g.weid where o.weid = ".$_W['weid']." and g.status = 2 ORDER BY g.checktime DESC limit ".($pindex - 1) * $psize . ',' . $psize);
$total = pdo_fetchcolumn("select count(o.id) from ".tablename('bj_qmxk_order'). " as o left join ".tablename('bj_qmxk_order_goods'). " as g on o.id = g.orderid and o.weid = g.weid where o.weid = ".$_W['weid']." and g.status = 2");
$pager = pagination1($total, $pindex, $psize);
}
include $this->template('applyed');
exit;
}
if($op=='invalid')
{
if($_GPC['opp']=='delete')
{
$delete = array( 'status'=>-2 );
$temp = pdo_update('bj_qmxk_order_goods', $delete, array('id'=>$_GPC['id']));
if(empty($temp))
{
message('删除失败，请重新删除！', $this->createWebUrl('commission', array('op'=>'invalid')), 'error');
}
else
{
message('删除成功！', $this->createWebUrl('commission', array('op'=>'invalid')), 'success');
}
}
if($_GPC['opp']=='detail')
{
$shareid = $_GPC['shareid'];
$user = pdo_fetch("select realname, mobile from ".tablename('bj_qmxk_member')." where id = ".$_GPC['shareid']);
$info = pdo_fetch("select og.id, og.total, og.price, og.status, og.checktime, og.content, g.title from ".tablename('bj_qmxk_order_goods')." as og left join ".tablename('bj_qmxk_goods')." as g on og.goodsid = g.id and og.weid = g.weid where og.id = ".$_GPC['id']);
include $this->template('invalid_detail');
exit;
}
if($_GPC['opp']=='invalided')
{
$invalided = array( 'status'=>$_GPC['status'], 'content'=>trim($_GPC['content']) );
$temp = pdo_update('bj_qmxk_order_goods', $invalided, array('id'=>$_GPC['id']));
if(empty($temp))
{
message('提交失败，请重新提交！', $this->createWebUrl('commission', array('op'=>'invalid', 'opp'=>'detail', 'shareid'=>$_GPC['shareid'], 'id'=>$_GPC['id'])), 'error');
}
else
{
message('提交成功！', $this->createWebUrl('commission', array('op'=>'invalid')), 'success');
}
}
if($_GPC['opp']=='sort')
{
$sort = array( 'realname'=>$_GPC['realname'], 'mobile'=>$_GPC['mobile'] );
$shareid = "select id from ".tablename('bj_qmxk_member')." where weid = ".$_W['weid']." and realname like '%".$sort['realname']."%' and mobile like '%".$sort['mobile']."%'";
$list = pdo_fetchall("select o.shareid, o.status, g.id, g.checktime from ".tablename('bj_qmxk_order'). " as o left join ".tablename('bj_qmxk_order_goods'). " as g on o.id = g.orderid and o.weid = g.weid where o.weid = ".$_W['weid']." and g.status = -1 and o.shareid in (".$shareid.") ORDER BY o.id desc");
$total = sizeof($list);
}
else
{
$pindex = max(1, intval($_GPC['page']));
$psize = 20;
$list = pdo_fetchall("select o.shareid, o.status, g.id, g.checktime from ".tablename('bj_qmxk_order'). " as o left join ".tablename('bj_qmxk_order_goods'). " as g on o.id = g.orderid and o.weid = g.weid where o.weid = ".$_W['weid']." and g.status = -1 ORDER BY o.id DESC limit ".($pindex - 1) * $psize . ',' . $psize);
$pager = pagination1($total, $pindex, $psize);
$total = pdo_fetchcolumn("select count(o.id) from ".tablename('bj_qmxk_order'). " as o left join ".tablename('bj_qmxk_order_goods'). " as g on o.id = g.orderid and o.weid = g.weid where o.weid = ".$_W['weid']." and g.status = -1");
}
include $this->template('invalid');
exit;
}
}
public function doWebOutCommission()
{
global $_W,$_GPC;
//$this->doWebAuth();
checklogin();
$weid=$_W['weid'];
$op= $operation = $_GPC['op']?$_GPC['op']:'display';
$starttime = strtotime($_GPC['start_time']);
$endtime = strtotime($_GPC['end_time']);
$info = pdo_fetch("select og.id, og.total, og.price, og.status, og.commission, og.applytime, og.content, g.title from ".tablename('bj_qmxk_order_goods')." as og left join ".tablename('bj_qmxk_goods')." as g on og.goodsid = g.id and og.weid = g.weid WHERE og.createtime>= ".$starttime." AND og.createtime<=".$endtime." ");
$commissionList = pdo_fetchall("SELECT c.*,m.realname,m.mobile,m.bankcard,m.alipay,m.wxhao FROM `ims_bj_qmxk_commission` AS c LEFT JOIN `ims_bj_qmxk_member` AS m ON c.mid=m.id WHERE c.createtime>=".$starttime." AND c.createtime<=".$endtime." AND c.isout = 0 AND c.flag = 0 AND c.weid=".$_W['weid']."  " );
if(empty($commissionList))
{
message('已没有需要导出的数据了！');
exit;
}
$list = array();
foreach($commissionList as $k=>$v)
{
$ogid = $v['ogid'];
$info = pdo_fetch("select og.id, og.checktime, og.content from ".tablename('bj_qmxk_order_goods')." as og left join ".tablename('bj_qmxk_goods')." as g on og.goodsid = g.id and og.weid = g.weid where og.id = ".$ogid);
pdo_update('bj_qmxk_commission', array('isout'=>1), array('id'=>$v['id']));
$list[$k]['realname'] = $v['realname'];
$list[$k]['mobile'] = $v['mobile'];
$list[$k]['bankcard'] = $v['bankcard'];
$list[$k]['alipay'] = $v['alipay'];
$list[$k]['wxhao'] = $v['wxhao'];
$list[$k]['checktime'] = date('Y-m-d H:m:s' ,$info['checktime']);
$list[$k]['commissiontotal'] = $v['commission'];
$list[$k]['content'] = $info['content'];
}
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
if (PHP_SAPI == 'cli') die('This example should only be run from a Web Browser');
require_once './source/modules/public/Classes/PHPExcel.php';
$objPHPExcel = new PHPExcel();
$objPHPExcel->getProperties()->setCreator("火池网络") ->setLastModifiedBy("火池网络") ->setTitle("Office 2007 XLSX Test Document") ->setSubject("Office 2007 XLSX Test Document") ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.") ->setKeywords("office 2007 openxml php") ->setCategory("Test result file");
$objPHPExcel->setActiveSheetIndex(0) ->setCellValue('A1', '真实姓名') ->setCellValue('B1', '手机号码') ->setCellValue('C1', '审核时间') ->setCellValue('D1', '申请佣金') ->setCellValue('E1', '银行卡号') ->setCellValue('F1', '支付宝号') ->setCellValue('G1', '微信号码') ->setCellValue('H1', '备注');
foreach($list as $i=>$v)
{
$i = $i+2;
$objPHPExcel->setActiveSheetIndex(0) ->setCellValue('A'.$i, $v['realname']) ->setCellValue('B'.$i, $v['mobile']) ->setCellValue('C'.$i, $v['checktime']) ->setCellValue('D'.$i, $v['commissiontotal']) ->setCellValue('E'.$i,' '.$v['bankcard'].' ') ->setCellValue('F'.$i,' '.$v['alipay'].' ') ->setCellValue('G'.$i,' '.$v['wxhao'].' ') ->setCellValue('H'.$i, $v['content']);
}
$objPHPExcel->getActiveSheet()->getStyle('A1:H1')->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(12);
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(18);
$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(30);
$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(30);
$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(30);
$time=time();
$objPHPExcel->getActiveSheet()->setTitle('微商城佣金充值'.$time);
$objPHPExcel->setActiveSheetIndex(0);
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="moon_'.$time.'.xlsx"');
header('Cache-Control: max-age=0');
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save('php://output');
exit;
}
public function doWebRules()
{
global $_W,$_GPC;
//$this->doWebAuth();
checklogin();
$weid=$_W['weid'];
$op= $operation = $_GPC['op']?$_GPC['op']:'display';
$theone = pdo_fetch('SELECT * FROM '.tablename('bj_qmxk_rules')." WHERE  weid = :weid" , array(':weid' => $_W['weid']));
$id = $theone['id'];
if (checksubmit('submit')) 
{
$clickcredit = $_GPC['clickcredit'];
if(!is_numeric($clickcredit))
{
message('请输入合法数字！');
}
$insert = array( 'weid' => $_W['weid'], 'clickcredit' => $clickcredit, 'rule' => htmlspecialchars_decode($_GPC['rule']), 'terms' => htmlspecialchars_decode($_GPC['terms']), 'commtime' => 0, 'promotertimes' => $_GPC['promotertimes'], 'createtime' => TIMESTAMP );
if(empty($id)) 
{
pdo_insert('bj_qmxk_rules', $insert);
!pdo_insertid() ? message('保存失败, 请稍后重试.','error') : '';
}
else 
{
if(pdo_update('bj_qmxk_rules', $insert,array('id' => $id)) === false)
{
message('更新失败, 请稍后重试.','error');
}
}
message('更新成功！', $this->createWebUrl('rules'), 'success');
}
include $this->template('rules');
}
public function doMobilelist() 
{
global $_GPC, $_W;
$from_user = $this->getFromUser();
$day_cookies = 15;
$shareid = 'bj_qmxk_sid07'.$_W['weid'];
if((($_GPC['mid']!=$_COOKIE[$shareid]) && !empty($_GPC['mid'])))
{
$this->shareClick($_GPC['mid']);
setcookie($shareid, $_GPC['mid'], time()+3600*24*$day_cookies);
}
$pindex = max(1, intval($_GPC['page']));
$psize = 4;
$condition = '';
if (!empty($_GPC['ccate'])) 
{
$cid = intval($_GPC['ccate']);
$condition .= " AND ccate = '{$cid}
'";
$_GPC['pcate'] = pdo_fetchcolumn("SELECT parentid FROM " . tablename('bj_qmxk_category') . " WHERE id = :id", array(':id' => intval($_GPC['ccate'])));
}
elseif (!empty($_GPC['pcate'])) 
{
$cid = intval($_GPC['pcate']);
$condition .= " AND pcate = '{$cid}
'";
}
if (!empty($_GPC['keyword'])) 
{
$condition .= " AND title LIKE '%{$_GPC['keyword']}
%'";
}
$children = array();
$category = pdo_fetchall("SELECT * FROM " . tablename('bj_qmxk_category') . " WHERE weid = '{$_W['weid']}
' and enabled=1 ORDER BY parentid ASC, displayorder DESC", array(), 'id');
foreach ($category as $index => $row) 
{
if (!empty($row['parentid'])) 
{
$children[$row['parentid']][$row['id']] = $row;
unset($category[$index]);
}
}
$recommandcategory = array();
foreach ($category as &$c) 
{
if ($c['isrecommand'] == 1) 
{
$c['list'] = pdo_fetchall("SELECT * FROM " . tablename('bj_qmxk_goods') . " WHERE weid = '{$_W['weid']}
' and deleted=0 AND status = '1'  and pcate='{$c['id']}
'  ORDER BY displayorder DESC, sales DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
$c['total'] = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('bj_qmxk_goods') . " WHERE weid = '{$_W['weid']}
'  and deleted=0  AND status = '1' and pcate='{$c['id']}
'");
$c['pager'] = pagination($c['total'], $pindex, $psize, $url = '', $context = array('before' => 0, 'after' => 0, 'ajaxcallback' => ''));
$recommandcategory[] = $c;
}
if (!empty($children[$c['id']])) 
{
foreach ($children[$c['id']] as &$child) 
{
if ($child['isrecommand'] == 1) 
{
$child['list'] = pdo_fetchall("SELECT * FROM " . tablename('bj_qmxk_goods') . " WHERE weid = '{$_W['weid']}
'  and deleted=0 AND status = '1'  and pcate='{$c['id']}
' and ccate='{$child['id']}
'  ORDER BY displayorder DESC, sales DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
$child['total'] = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('bj_qmxk_goods') . " WHERE weid = '{$_W['weid']}
'  and deleted=0  AND status = '1' and pcate='{$c['id']}
' and ccate='{$child['id']}
' ");
$child['pager'] = pagination($child['total'], $pindex, $psize, $url = '', $context = array('before' => 0, 'after' => 0, 'ajaxcallback' => ''));
$recommandcategory[] = $child;
}
}
unset($child);
}
}
unset($c);
$carttotal = $this->getCartTotal();
$advs = pdo_fetchall("select * from " . tablename('bj_qmxk_adv') . " where enabled=1 and weid= '{$_W['weid']}
'  order by displayorder asc");
foreach ($advs as &$adv) 
{
if (substr($adv['link'], 0, 5) != 'http:') 
{
$adv['link'] = "http://" . $adv['link'];
}
}
unset($adv);
$rpindex = max(1, intval($_GPC['rpage']));
$rpsize = 6;
$condition = ' and isrecommand=1';
$rlist = pdo_fetchall("SELECT * FROM " . tablename('bj_qmxk_goods') . " WHERE weid = '{$_W['weid']}
'  and deleted=0 AND status = '1' $condition ORDER BY displayorder DESC, sales DESC ");
$cfg = $this->module['config'];
if(empty($cfg['indexss'])) 
{
$cfg['indexss']=5;
}
$islist = pdo_fetchall("SELECT * FROM " . tablename('bj_qmxk_goods') . " WHERE weid = '{$_W['weid']}
'  and deleted=0 AND status = '1' and istime='1' ORDER BY displayorder DESC, sales DESC limit {$cfg['indexss']}
");
$logo = $cfg['logo'];
$ydyy = $cfg['ydyy'];
$description = $cfg['description'];
include $this->template('list');
}
public function doMobilelistmore_rec() 
{
global $_GPC, $_W;
$pindex = max(1, intval($_GPC['page']));
$psize = 6;
$condition = ' and isrecommand=1 ';
$list = pdo_fetchall("SELECT * FROM " . tablename('bj_qmxk_goods') . " WHERE weid = '{$_W['weid']}
'  and deleted=0 AND status = '1' $condition ORDER BY displayorder DESC, sales DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
include $this->template('list_more');
}
public function doMobilelistmore() 
{
global $_GPC, $_W;
$pindex = max(1, intval($_GPC['page']));
$psize = 6;
$condition = '';
if (!empty($_GPC['ccate'])) 
{
$cid = intval($_GPC['ccate']);
$condition .= " AND ccate = '{$cid}
'";
$_GPC['pcate'] = pdo_fetchcolumn("SELECT parentid FROM " . tablename('bj_qmxk_category') . " WHERE id = :id", array(':id' => intval($_GPC['ccate'])));
}
elseif (!empty($_GPC['pcate'])) 
{
$cid = intval($_GPC['pcate']);
$condition .= " AND pcate = '{$cid}
'";
}
$list = pdo_fetchall("SELECT * FROM " . tablename('bj_qmxk_goods') . " WHERE weid = '{$_W['weid']}
' AND status = '1' $condition ORDER BY displayorder DESC, sales DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
include $this->template('list_more');
}
public function doMobilelist2() 
{
global $_GPC, $_W;
$from_user = $this->getFromUser();
$pindex = max(1, intval($_GPC["page"]));
$psize = 10;
$condition = '';
if (!empty($_GPC['ccate'])) 
{
$cid = intval($_GPC['ccate']);
$condition .= " AND ccate = '{$cid}
'";
$_GPC['pcate'] = pdo_fetchcolumn("SELECT parentid FROM " . tablename('bj_qmxk_category') . " WHERE id = :id", array(':id' => intval($_GPC['ccate'])));
}
elseif (!empty($_GPC['pcate'])) 
{
$cid = intval($_GPC['pcate']);
$condition .= " AND pcate = '{$cid}
'";
}
if (!empty($_GPC['keyword'])) 
{
$condition .= " AND title LIKE '%{$_GPC['keyword']}
%'";
}
$sort = empty($_GPC['sort']) ? 0 : $_GPC['sort'];
$sortfield = "displayorder asc";
$sortb0 = empty($_GPC['sortb0']) ? "desc" : $_GPC['sortb0'];
$sortb1 = empty($_GPC['sortb1']) ? "desc" : $_GPC['sortb1'];
$sortb2 = empty($_GPC['sortb2']) ? "desc" : $_GPC['sortb2'];
$sortb3 = empty($_GPC['sortb3']) ? "asc" : $_GPC['sortb3'];
if ($sort == 0) 
{
$sortb00 = $sortb0 == "desc" ? "asc" : "desc";
$sortfield = "createtime " . $sortb0;
$sortb11 = "desc";
$sortb22 = "desc";
$sortb33 = "asc";
}
else if ($sort == 1) 
{
$sortb11 = $sortb1 == "desc" ? "asc" : "desc";
$sortfield = "sales " . $sortb1;
$sortb00 = "desc";
$sortb22 = "desc";
$sortb33 = "asc";
}
else if ($sort == 2) 
{
$sortb22 = $sortb2 == "desc" ? "asc" : "desc";
$sortfield = "viewcount " . $sortb2;
$sortb00 = "desc";
$sortb11 = "desc";
$sortb33 = "asc";
}
else if ($sort == 3) 
{
$sortb33 = $sortb3 == "asc" ? "desc" : "asc";
$sortfield = "marketprice " . $sortb3;
$sortb00 = "desc";
$sortb11 = "desc";
$sortb22 = "desc";
}
$sorturl = $this->createMobileUrl('list2', array("keyword" => $_GPC['keyword'], "pcate" => $_GPC['pcate'], "ccate" => $_GPC['ccate']));
if (!empty($_GPC['isnew'])) 
{
$condition .= " AND isnew = 1";
$sorturl.="&isnew=1";
}
if (!empty($_GPC['ishot'])) 
{
$condition .= " AND ishot = 1";
$sorturl.="&ishot=1";
}
if (!empty($_GPC['isdiscount'])) 
{
$condition .= " AND isdiscount = 1";
$sorturl.="&isdiscount=1";
}
if (!empty($_GPC['istime'])) 
{
$condition .= " AND istime = 1 and " . time() . ">=timestart and " . time() . "<=timeend";
$sorturl.="&istime=1";
}
$children = array();
$category = pdo_fetchall("SELECT * FROM " . tablename('bj_qmxk_category') . " WHERE weid = '{$_W['weid']}
' and enabled=1 ORDER BY parentid ASC, displayorder DESC", array(), 'id');
foreach ($category as $index => $row) 
{
if (!empty($row['parentid'])) 
{
$children[$row['parentid']][$row['id']] = $row;
unset($category[$index]);
}
}
$list = pdo_fetchall("SELECT * FROM " . tablename('bj_qmxk_goods') . " WHERE weid = '{$_W['weid']}
'  and deleted=0 AND status = '1' $condition ORDER BY $sortfield  ");
foreach ($list as &$r) 
{
if ($r['istime'] == 1) 
{
$arr = $this->time_tran($r['timeend']);
$r['timelaststr'] = $arr[0];
$r['timelast'] = $arr[1];
}
}
unset($r);
$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('bj_qmxk_goods') . " WHERE weid = '{$_W['weid']}
'  and deleted=0  AND status = '1' $condition");
$pager = pagination($total, $pindex, $psize, $url = '', $context = array('before' => 0, 'after' => 0, 'ajaxcallback' => ''));
$carttotal = $this->getCartTotal();
$cfg = $this->module['config'];
$ydyy = $cfg['ydyy'];
$logo = $cfg['logo'];
$description = $cfg['description'];
include $this->template('list2');
}
public function doMobilelistCategory() 
{
global $_GPC, $_W;
$from_user = $this->getFromUser();
$category = pdo_fetchall("SELECT * FROM " . tablename('bj_qmxk_category') . " WHERE weid = '{$_W['weid']}
' and enabled=1 ORDER BY parentid ASC, displayorder DESC", array(), 'id');
foreach ($category as $index => $row) 
{
if (!empty($row['parentid'])) 
{
$children[$row['parentid']][$row['id']] = $row;
unset($category[$index]);
}
}
$carttotal = $this->getCartTotal();
$cfg = $this->module['config'];
$ydyy = $cfg['ydyy'];
include $this->template('list_category');
}
public function doMobiletuiguang() 
{
global $_GPC, $_W;
$carttotal = $this->getCartTotal();
$share = "bj_qmxkshareQrcode".$_W['weid'];
$gid = $_GPC['gid'];
$from_user = $this->getFromUser();
$goods = pdo_fetch("SELECT * FROM " . tablename('bj_qmxk_goods') . " WHERE id = :id", array(':id' => $gid));
$rule = pdo_fetchcolumn('SELECT rule FROM '.tablename('bj_qmxk_rules')." WHERE weid = :weid" , array(':weid' => $_W['weid']));
$profile = pdo_fetch('SELECT * FROM '.tablename('bj_qmxk_member')." WHERE  weid = :weid  AND from_user = :from_user" , array(':weid' => $_W['weid'],':from_user' => $from_user));
$id = $profile['id'];
if(intval($profile['id']) && $profile['status']==0)
{
include $this->template('forbidden');
exit;
}
if(empty($profile))
{
$rule = pdo_fetch('SELECT * FROM '.tablename('bj_qmxk_rules')." WHERE `weid` = :weid ",array(':weid' => $_W['weid']));
include $this->template('register');
exit;
}
$cfg = $this->module['config'];
$logo = $cfg['logo'];
$description = $cfg['description'];
include $this->template('tgym');
}
function time_tran($the_time) 
{
$timediff = $the_time - time();
$days = intval($timediff / 86400);
if (strlen($days) <= 1) 
{
$days = "0" . $days;
}
$remain = $timediff % 86400;
$hours = intval($remain / 3600);
;
if (strlen($hours) <= 1) 
{
$hours = "0" . $hours;
}
$remain = $remain % 3600;
$mins = intval($remain / 60);
if (strlen($mins) <= 1) 
{
$mins = "0" . $mins;
}
$secs = $remain % 60;
if (strlen($secs) <= 1) 
{
$secs = "0" . $secs;
}
$ret = "";
if ($days > 0) 
{
$ret.=$days . " 天 ";
}
if ($hours > 0) 
{
$ret.=$hours . ":";
}
if ($mins > 0) 
{
$ret.=$mins . ":";
}
$ret.=$secs;
return array("倒计时 " . $ret, $timediff);
}
public function doMobileMyfans() 
{
global $_W, $_GPC;
$from_user = $this->getFromUser();
$profile = pdo_fetch('SELECT * FROM '.tablename('bj_qmxk_member')." WHERE  weid = :weid  AND from_user = :from_user" , array(':weid' => $_W['weid'],':from_user' => $from_user));
$count1 = pdo_fetchcolumn("select count(*) from (select from_user from ".tablename('bj_qmxk_order')." where  shareid = ".$profile['id'].'  group by from_user'.") x");
$count1_2 = pdo_fetchcolumn("select count(mber.id) from ".tablename('bj_qmxk_member')." mber where mber.shareid = ".$profile['id']." and mber.from_user not in (select orders.from_user from ".tablename('bj_qmxk_order')." orders where  orders.shareid = ".$profile['id']." group by from_user)");
$count1=$count1+$count1_2;
if($count1>0) 
{
$countall = pdo_fetch("select id from ".tablename('bj_qmxk_member')." where shareid = ".$profile['id']);
$count2=0;
$count3=0;
foreach ($countall as &$citem)
{
$tcount2 = pdo_fetchcolumn("select count(id) from ".tablename('bj_qmxk_member')." where shareid = ".$citem);
$count2=$count2+$tcount2;
$count2all = pdo_fetch("select id from ".tablename('bj_qmxk_member')." where shareid = ".$citem);
foreach ($count2all as &$citem2)
{
$tcount3 = pdo_fetchcolumn("select count(*) from (select from_user from ".tablename('bj_qmxk_order')." where  shareid = ".$citem2.' and shareid!='.$citem.' and shareid!='.$profile['id'].' group by from_user'.") y" );
$count3=$count3+$tcount3;
}
}
}
else 
{
$count1=0;
$count2=0;
$count3=0;
}
include $this->template('myfans');
}
public function doMobileMyCart() 
{
global $_W, $_GPC;
$from_user = $this->getFromUser();
$op = $_GPC['op'];
if ($op == 'add') 
{
$goodsid = intval($_GPC['id']);
$total = intval($_GPC['total']);
$total = empty($total) ? 1 : $total;
$optionid = intval($_GPC['optionid']);
$goods = pdo_fetch("SELECT id, type, total,marketprice,maxbuy FROM " . tablename('bj_qmxk_goods') . " WHERE id = :id", array(':id' => $goodsid));
if (empty($goods)) 
{
$result['message'] = '抱歉，该商品不存在或是已经被删除！';
message($result, '', 'ajax');
}
$marketprice = $goods['marketprice'];
if (!empty($optionid)) 
{
$option = pdo_fetch("select marketprice from " . tablename('bj_qmxk_goods_option') . " where id=:id limit 1", array(":id" => $optionid));
if (!empty($option)) 
{
$marketprice = $option['marketprice'];
}
}
$row = pdo_fetch("SELECT id, total FROM " . tablename('bj_qmxk_cart') . " WHERE from_user = :from_user AND weid = '{$_W['weid']}
' AND goodsid = :goodsid  and optionid=:optionid", array(':from_user' => $from_user, ':goodsid' => $goodsid,':optionid'=>$optionid));
if ($row == false) 
{
$data = array( 'weid' => $_W['weid'], 'goodsid' => $goodsid, 'goodstype' => $goods['type'], 'marketprice' => $marketprice, 'from_user' => $from_user, 'total' => $total, 'optionid' => $optionid );
pdo_insert('bj_qmxk_cart', $data);
}
else 
{
$t = $total + $row['total'];
if (!empty($goods['maxbuy'])) 
{
if ($t > $goods['maxbuy']) 
{
$t = $goods['maxbuy'];
}
}
$data = array( 'marketprice' => $marketprice, 'total' => $t, 'optionid' => $optionid );
pdo_update('bj_qmxk_cart', $data, array('id' => $row['id']));
}
$carttotal = $this->getCartTotal();
$result = array( 'result' => 1, 'total' => $carttotal );
die(json_encode($result));
}
else if ($op == 'clear') 
{
pdo_delete('bj_qmxk_cart', array('from_user' => $from_user, 'weid' => $_W['weid']));
die(json_encode(array("result" => 1)));
}
else if ($op == 'remove') 
{
$id = intval($_GPC['id']);
pdo_delete('bj_qmxk_cart', array('from_user' => $from_user, 'weid' => $_W['weid'], 'id' => $id));
die(json_encode(array("result" => 1, "cartid" => $id)));
}
else if ($op == 'update') 
{
$id = intval($_GPC['id']);
$num = intval($_GPC['num']);
$sql = "update " . tablename('bj_qmxk_cart') . " set total=$num where id=:id";
pdo_query($sql, array(":id" => $id));
die(json_encode(array("result" => 1)));
}
else 
{
$list = pdo_fetchall("SELECT * FROM " . tablename('bj_qmxk_cart') . " WHERE  weid = '{$_W['weid']}
' AND from_user = '".$from_user."'");
$totalprice = 0;
if (!empty($list)) 
{
foreach ($list as &$item) 
{
$goods = pdo_fetch("SELECT  title, thumb, marketprice, unit, total,maxbuy FROM " . tablename('bj_qmxk_goods') . " WHERE id=:id limit 1", array(":id" => $item['goodsid']));
$option = pdo_fetch("select title,marketprice,stock from " . tablename("bj_qmxk_goods_option") . " where id=:id limit 1", array(":id" => $item['optionid']));
if ($option) 
{
$goods['title'] = $goods['title'];
$goods['optionname'] = $option['title'];
$goods['marketprice'] = $option['marketprice'];
$goods['total'] = $option['stock'];
}
$item['goods'] = $goods;
$item['totalprice'] = (floatval($goods['marketprice']) * intval($item['total']));
$totalprice += $item['totalprice'];
}
unset($item);
}
include $this->template('cart');
}
}
public function doMobileConfirm() 
{
global $_W,$_GPC;
$from_user =$this->getFromUser();
$weid=$_W['weid'];
$op = $_GPC['op']?$_GPC['op']:'display';
$totalprice = 0;
$allgoods = array();
$id = intval($_GPC['id']);
$optionid = intval($_GPC['optionid']);
$total = intval($_GPC['total']);
if (empty($total)) 
{
$total = 1;
}
$direct = false;
$returnurl = "";
if (!empty($id)) 
{
$item = pdo_fetch("select id,thumb,ccate,title,weight,marketprice,total,type,totalcnf,sales,unit,istime,timeend from " . tablename("bj_qmxk_goods") . " where id=:id limit 1", array(":id" => $id));
if ($item['istime'] == 1) 
{
if (time() > $item['timeend']) 
{
message('抱歉，商品限购时间已到，无法购买了！', referer(), "error");
}
}
if (!empty($optionid)) 
{
$option = pdo_fetch("select title,marketprice,weight,stock from " . tablename("bj_qmxk_goods_option") . " where id=:id limit 1", array(":id" => $optionid));
if ($option) 
{
$item['optionid'] = $optionid;
$item['title'] = $item['title'];
$item['optionname'] = $option['title'];
$item['marketprice'] = $option['marketprice'];
$item['weight'] = $option['weight'];
}
}
$item['stock'] = $item['total'];
$item['total'] = $total;
$item['totalprice'] = $total * $item['marketprice'];
$allgoods[] = $item;
$totalprice+= $item['totalprice'];
if ($item['type'] == 1) 
{
$needdispatch = true;
}
$direct = true;
$returnurl = $this->createMobileUrl("confirm", array("id" => $id, "optionid" => $optionid, "total" => $total));
}
if (!$direct) 
{
$list = pdo_fetchall("SELECT * FROM " . tablename('bj_qmxk_cart') . " WHERE  weid = '{$_W['weid']}
' AND from_user = '".$from_user."'");
if (!empty($list)) 
{
foreach ($list as &$g) 
{
$item = pdo_fetch("select id,thumb,ccate,title,weight,marketprice,total,type,totalcnf,sales,unit from " . tablename("bj_qmxk_goods") . " where id=:id limit 1", array(":id" => $g['goodsid']));
$option = pdo_fetch("select title,marketprice,weight,stock from " . tablename("bj_qmxk_goods_option") . " where id=:id limit 1", array(":id" => $g['optionid']));
if ($option) 
{
$item['optionid'] = $g['optionid'];
$item['title'] = $item['title'];
$item['optionname'] = $option['title'];
$item['marketprice'] = $option['marketprice'];
$item['weight'] = $option['weight'];
}
$item['stock'] = $item['total'];
$item['total'] = $g['total'];
$item['totalprice'] = $g['total'] * $item['marketprice'];
$allgoods[] = $item;
$totalprice+= $item['totalprice'];
if ($item['type'] == 1) 
{
$needdispatch = true;
}
}
unset($g);
}
$returnurl = $this->createMobileUrl("confirm");
}
if (count($allgoods) <= 0) 
{
header("location: " . $this->createMobileUrl('myorder'));
exit();
}
$dispatch = pdo_fetchall("select id,dispatchname,firstprice,firstweight,secondprice,secondweight from " . tablename("bj_qmxk_dispatch") . " WHERE weid = {$_W['weid']}
order by displayorder desc");
foreach ($dispatch as &$d) 
{
$weight = 0;
foreach ($allgoods as $g) 
{
$weight+=$g['weight'] * $g['total'];
}
$price = 0;
if ($weight <= $d['firstweight']) 
{
$price = $d['firstprice'];
}
else 
{
$price = $d['firstprice'];
$secondweight = $weight - $d['firstweight'];
if ($secondweight % $d['secondweight'] == 0) 
{
$price+= (int) ( $secondweight / $d['secondweight'] ) * $d['secondprice'];
}
else 
{
$price+= (int) ( $secondweight / $d['secondweight'] + 1 ) * $d['secondprice'];
}
}
$d['price'] = $price;
}
unset($d);
if (checksubmit('submit')) 
{
$address = pdo_fetch("SELECT * FROM " . tablename('bj_qmxk_address') . " WHERE id = :id", array(':id' => intval($_GPC['address'])));
if (empty($address)) 
{
message('抱歉，请您填写收货地址！');
}
$goodsprice = 0;
foreach ($allgoods as $row) 
{
if ($item['stock'] != -1 && $row['total'] > $item['stock']) 
{
message('抱歉，“' . $row['title'] . '”此商品库存不足！', $this->createMobileUrl('confirm'), 'error');
}
$goodsprice+= $row['totalprice'];
}
$dispatchid = intval($_GPC['dispatch']);
$dispatchprice = 0;
foreach ($dispatch as $d) 
{
if ($d['id'] == $dispatchid) 
{
$dispatchprice = $d['price'];
}
}
$shareId = $this->getShareId();
$data = array( 'weid' => $_W['weid'], 'from_user' => $from_user, 'ordersn' => date('md') . random(4, 1), 'price' => $goodsprice + $dispatchprice, 'dispatchprice' => $dispatchprice, 'goodsprice' => $goodsprice, 'status' => 0, 'sendtype' => intval($_GPC['sendtype']), 'dispatch' => $dispatchid, 'paytype' => '2', 'goodstype' => intval($cart['type']), 'remark' => $_GPC['remark'], 'addressid' => $address['id'], 'createtime' => TIMESTAMP, 'shareid' => $shareId );
pdo_insert('bj_qmxk_order', $data);
$orderid = pdo_insertid();
foreach ($allgoods as $row) 
{
if (empty($row)) 
{
continue;
}
$d = array( 'weid' => $_W['weid'], 'goodsid' => $row['id'], 'orderid' => $orderid, 'total' => $row['total'], 'price' => $row['marketprice'], 'createtime' => TIMESTAMP, 'optionid' => $row['optionid'] );
$o = pdo_fetch("select title from ".tablename('bj_qmxk_goods_option')." where id=:id limit 1",array(":id"=>$row['optionid']));
if(!empty($o))
{
$d['optionname'] = $o['title'];
}
$ccate = $row['ccate'];
$commission = pdo_fetchcolumn( " SELECT commission FROM ".tablename('bj_qmxk_goods')."  WHERE id=".$row['id']);
$commission2 = pdo_fetchcolumn( " SELECT commission2 FROM ".tablename('bj_qmxk_goods')."  WHERE id=".$row['id']);
$commission3 = pdo_fetchcolumn( " SELECT commission3 FROM ".tablename('bj_qmxk_goods')."  WHERE id=".$row['id']);
if($commission == false || $commission == null || $commission <0)
{
$commission = $this->module['config']['globalCommission'];
}
if($commission2 == false || $commission2 == null || $commission2 <0)
{
$commission2 = $this->module['config']['globalCommission2'];
}
if($commission3 == false || $commission3 == null || $commission3 <0)
{
$commission3 = $this->module['config']['globalCommission3'];
}
$commissionTotal = $row['marketprice'] * $commission /100;
$d['commission'] = $commissionTotal;
$commissionTotal2 = $commissionTotal * $commission2 /100;
$d['commission2'] = $commissionTotal2;
$commissionTotal3 = $commissionTotal2 * $commission3 /100;
$d['commission3'] = $commissionTotal3;
pdo_insert('bj_qmxk_order_goods', $d);
}
if (!$direct) 
{
pdo_delete("bj_qmxk_cart", array("weid" => $_W['weid'], "from_user" => $from_user));
}
$this->setOrderStock($orderid);
die("<script>alert('提交订单成功,现在跳转到付款页面...');location.href='" . $this->createMobileUrl('pay', array('orderid' => $orderid)) . "';</script>");
}
$carttotal = $this->getCartTotal();
$profile = fans_search($from_user, array('resideprovince', 'residecity', 'residedist', 'address', 'realname', 'mobile'));
$row = pdo_fetch("SELECT * FROM " . tablename('bj_qmxk_address') . " WHERE isdefault = 1 and openid = :openid limit 1", array(':openid' => $from_user));
include $this->template('confirm');
}
public function setOrderCredit($orderid, $add = true) 
{
$order = pdo_fetch("SELECT * FROM " . tablename('bj_qmxk_order') . " WHERE id = :id limit 1", array(':id' => $orderid));
if (empty($order)) 
{
return;
}
$ordergoods = pdo_fetchall("SELECT goodsid, total FROM " . tablename('bj_qmxk_order_goods') . " WHERE orderid = '{$orderid}
'", array(), 'goodsid');
if (!empty($ordergoods)) 
{
$goods = pdo_fetchall("SELECT id, title, thumb, marketprice, unit, total,credit FROM " . tablename('bj_qmxk_goods') . " WHERE id IN ('" . implode("','", array_keys($ordergoods)) . "')");
}
if (!empty($goods)) 
{
$credits = 0;
foreach ($goods as $g) 
{
$credits+=$g['credit'];
}
$fans = fans_search($order['from_user'], array("credit1"));
if (!empty($fans)) 
{
if ($add) 
{
$new_credit = $credits + $fans['credit1'];
}
else 
{
$new_credit = $fans['credit1'] - $credits;
if ($new_credit <= 0) 
{
$new_credit = 0;
}
}
fans_update($order['from_user'], array("credit1" => $new_credit));
}
}
}
public function doMobilePay() 
{
global $_W, $_GPC;
$from_user =$this->getFromUser();
$orderid = intval($_GPC['orderid']);
$order = pdo_fetch("SELECT * FROM " . tablename('bj_qmxk_order') . " WHERE id = :id", array(':id' => $orderid));
if ($order['status'] != '0') 
{
message('抱歉，您的订单已经付款或是被关闭，请重新进入付款！', $this->createMobileUrl('myorder'), 'error');
}
if (checksubmit('codsubmit')) 
{
$ordergoods = pdo_fetchall("SELECT goodsid, total,optionid FROM " . tablename('bj_qmxk_order_goods') . " WHERE orderid = '{$orderid}
'", array(), 'goodsid');
if (!empty($ordergoods)) 
{
$goods = pdo_fetchall("SELECT id, title, thumb, marketprice, unit, total,credit FROM " . tablename('bj_qmxk_goods') . " WHERE id IN ('" . implode("','", array_keys($ordergoods)) . "')");
}
if (!empty($this->module['config']['noticeemail'])) 
{
$address = pdo_fetch("SELECT * FROM " . tablename('bj_qmxk_address') . " WHERE id = :id", array(':id' => $order['addressid']));
$body = "<h3>购买商品清单</h3> <br />";
if (!empty($goods)) 
{
foreach ($goods as $row) 
{
$body .= "名称：{$row['title']}
，数量：{$ordergoods[$row['id']]['total']}
<br />";
}
}
$body .= "<br />总金额：{$order['price']}
元 （已付款）<br />";
$body .= "<h3>购买用户详情</h3> <br />";
$body .= "真实姓名：$address[realname] <br />";
$body .= "地区：$address[province] - $address[city] - $address[area]<br />";
$body .= "详细地址：$address[address] <br />";
$body .= "手机：$address[mobile] <br />";
ihttp_email($this->module['config']['noticeemail'], '微商城订单提醒', $body);
}
pdo_update('bj_qmxk_order', array('status' => '1', 'paytype' => '3'), array('id' => $orderid));
$this->sendMobilePayMsg($order,$goods,"货到付款",$ordergoods);
message('订单提交成功，请您收到货时付款！', $this->createMobileUrl('myorder'), 'success');
}
if (checksubmit()) 
{
if ($order['paytype'] == 1 && $_W['fans']['credit2'] < $order['price']) 
{
message('抱歉，您帐户的余额不够支付该订单，请充值！', create_url('mobile/module/charge', array('name' => 'member', 'weid' => $_W['weid'])), 'error');
}
if ($order['price'] == '0') 
{
$this->payResult(array('tid' => $orderid, 'from' => 'return', 'type' => 'credit2'));
$this->sendMobilePayMsg($order,$goods,"余额付款",$ordergoods);
exit;
}
}
$params['tid'] = $orderid;
$params['user'] = $from_user;
$params['fee'] = $order['price'];
$params['title'] = $_W['account']['name'];
$params['ordersn'] = $order['ordersn'];
$params['virtual'] = $order['goodstype'] == 2 ? true : false;
include $this->template('pay');
}
private function sendMobilePayMsg($order,$goods,$paytype,$ordergoods) 
{
$address = pdo_fetch("SELECT * FROM " . tablename('bj_qmxk_address') . " WHERE id = :id", array(':id' => $order['addressid']));
include 'messagetemplate/pay.php';
if (!empty($template_id)) 
{
$this->sendtempmsg($template_id, '', $data, '#FF0000');
}
}
public function doMobileContactUs() 
{
global $_W;
$cfg = $this->module['config'];
include $this->template('contactus');
}
public function doMobileMyOrder() 
{
global $_W, $_GPC;
$from_user = $this->getFromUser();
$op = $_GPC['op'];
if ($op == 'confirm') 
{
$orderid = intval($_GPC['orderid']);
$order = pdo_fetch("SELECT * FROM " . tablename('bj_qmxk_order') . " WHERE id = :id AND from_user = :from_user", array(':id' => $orderid, ':from_user' => $from_user ));
if (empty($order)) 
{
message('抱歉，您的订单不存在或是已经被取消！', $this->createMobileUrl('myorder'), 'error');
}
pdo_update('bj_qmxk_order', array('status' => 3), array('id' => $orderid, 'from_user' => $from_user ));
message('确认收货完成！', $this->createMobileUrl('myorder'), 'success');
}
else if ($op == 'detail') 
{
$orderid = intval($_GPC['orderid']);
$item = pdo_fetch("SELECT * FROM " . tablename('bj_qmxk_order') . " WHERE weid = '{$_W['weid']}
' AND from_user = '".$from_user."' and id='{$orderid}
' limit 1");
if (empty($item)) 
{
message('抱歉，您的订单不存或是已经被取消！', $this->createMobileUrl('myorder'), 'error');
}
$goodsid = pdo_fetchall("SELECT goodsid,total FROM " . tablename('bj_qmxk_order_goods') . " WHERE orderid = '{$orderid}
'", array(), 'goodsid');
$goods = pdo_fetchall("SELECT g.id, g.title, g.thumb, g.unit, g.marketprice,o.total,o.optionid FROM " . tablename('bj_qmxk_order_goods') . " o left join " . tablename('bj_qmxk_goods') . " g on o.goodsid=g.id  WHERE o.orderid='{$orderid}
'");
foreach ($goods as &$g) 
{
$option = pdo_fetch("select title,marketprice,weight,stock from " . tablename("bj_qmxk_goods_option") . " where id=:id limit 1", array(":id" => $g['optionid']));
if ($option) 
{
$g['title'] = "[" . $option['title'] . "]" . $g['title'];
$g['marketprice'] = $option['marketprice'];
}
}
unset($g);
$dispatch = pdo_fetch("select id,dispatchname from " . tablename('bj_qmxk_dispatch') . " where id=:id limit 1", array(":id" => $item['dispatch']));
include $this->template('order_detail');
}
else 
{
$pindex = max(1, intval($_GPC['page']));
$psize = 20;
$status = intval($_GPC['status']);
$where = " weid = '{$_W['weid']}
' AND from_user = '".$from_user."'";
;
if ($status == 2) 
{
$where.=" and ( status=1 or status=2 )";
}
else 
{
$where.=" and status=$status";
}
$list = pdo_fetchall("SELECT * FROM " . tablename('bj_qmxk_order') . " WHERE $where ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize, array(), 'id');
$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('bj_qmxk_order') . " WHERE weid = '{$_W['weid']}
' AND from_user = '".$from_user."'");
$pager = pagination($total, $pindex, $psize);
if (!empty($list)) 
{
foreach ($list as &$row) 
{
$goods = pdo_fetchall("SELECT g.id, g.title, g.thumb, g.unit, g.marketprice,o.total,o.optionid FROM " . tablename('bj_qmxk_order_goods') . " o left join " . tablename('bj_qmxk_goods') . " g on o.goodsid=g.id  WHERE o.orderid='{$row['id']}
'");
foreach ($goods as &$item) 
{
$option = pdo_fetch("select title,marketprice,weight,stock from " . tablename("bj_qmxk_goods_option") . " where id=:id limit 1", array(":id" => $item['optionid']));
if ($option) 
{
$item['title'] = "[" . $option['title'] . "]" . $item['title'];
$item['marketprice'] = $option['marketprice'];
}
}
unset($item);
$row['goods'] = $goods;
$row['total'] = $goodsid;
$row['dispatch'] = pdo_fetch("select id,dispatchname from " . tablename('bj_qmxk_dispatch') . " where id=:id limit 1", array(":id" => $row['dispatch']));
}
}
$carttotal = $this->getCartTotal();
$fans = pdo_fetch('SELECT * FROM '.tablename('fans')." WHERE  weid = :weid and from_user=:from_user" , array(':weid' => $_W['weid'],':from_user' => $from_user));
include $this->template('order');
}
}
private function shareClick($mid) 
{
global $_W, $_GPC;
$fromuser = $this->getFromUser();
$share = pdo_fetch("SELECT * FROM " . tablename('bj_qmxk_share_history') . " WHERE sharemid =:mid and from_user=:from_user and weid=:weid", array(':mid' => $mid,':from_user' =>$fromuser,':weid' => $_W['weid']));
$member = pdo_fetch('SELECT * FROM ' . tablename('bj_qmxk_member') . " WHERE weid = '{$_W['weid']}
' AND id = '{$mid}
'");
if(empty($share)) 
{
if((!empty($member))) 
{
$data = array( 'weid' => $_W['weid'], 'from_user' => $fromuser, 'sharemid' => $mid );
pdo_insert('bj_qmxk_share_history', $data);
pdo_update('bj_qmxk_member', array('clickcount' => $member['clickcount']+1), array('id' => $mid));
$theone = pdo_fetch('SELECT * FROM '.tablename('bj_qmxk_rules')." WHERE  weid = :weid" , array(':weid' => $_W['weid']));
if((!empty($theone['clickcredit']))) 
{
$fans = pdo_fetch('SELECT * FROM '.tablename('fans')." WHERE  weid = :weid and from_user=:from_user" , array(':weid' => $_W['weid'],':from_user' => $member['from_user']));
if((!empty($fans))) 
{
pdo_update('fans', array('credit1' => $fans['credit1']+$theone['clickcredit']), array('id' => $fans[id]));
}
}
}
}
}
public function doMobileDetail() 
{
global $_W, $_GPC;
$from_user = $this->getFromUser();
$day_cookies = 15;
$shareid = 'bj_qmxk_sid07'.$_W['weid'];
if((($_GPC['mid']!=$_COOKIE[$shareid]) && !empty($_GPC['mid'])))
{
$this->shareClick($_GPC['mid']);
setcookie($shareid, $_GPC['mid'], time()+3600*24*$day_cookies);
}
$goodsid = intval($_GPC['id']);
$goods = pdo_fetch("SELECT * FROM " . tablename('bj_qmxk_goods') . " WHERE id = :id", array(':id' => $goodsid));
$ccate = intval($goods['ccate']);
$commission = pdo_fetchcolumn( " SELECT commission FROM ".tablename('bj_qmxk_goods')." WHERE id=".$goodsid." " );
$member = pdo_fetch( " SELECT * FROM ".tablename('bj_qmxk_member')." WHERE from_user='".$from_user."' AND weid=".$_W['weid']." " );
if($commission == false || $commission == null || $commission <0)
{
$commission = $this->module['config']['globalCommission'];
}
if (empty($goods)) 
{
message('抱歉，商品不存在或是已经被删除！');
}
if ($goods['istime'] == 1) 
{
if (time() < $goods['timestart']) 
{
message('抱歉，还未到购买时间, 暂时无法购物哦~', referer(), "error");
}
if (time() > $goods['timeend']) 
{
message('抱歉，商品限购时间已到，不能购买了哦~', referer(), "error");
}
}
pdo_query("update " . tablename('bj_qmxk_goods') . " set viewcount=viewcount+1 where id=:id and weid='{$_W['weid']}
' ", array(":id" => $goodsid));
$piclist = array(array("attachment" => $goods['thumb']));
if ($goods['thumb_url'] != 'N;') 
{
$urls = unserialize($goods['thumb_url']);
if (is_array($urls)) 
{
$piclist = array_merge($piclist, $urls);
}
}
$marketprice = $goods['marketprice'];
$productprice= $goods['productprice'];
$stock = $goods['total'];
$allspecs = pdo_fetchall("select * from " . tablename('bj_qmxk_spec') . " where goodsid=:id order by displayorder asc", array(':id' => $goodsid));
foreach ($allspecs as &$s) 
{
$s['items'] = pdo_fetchall("select * from " . tablename('bj_qmxk_spec_item') . " where  `show`=1 and specid=:specid order by displayorder asc", array(":specid" => $s['id']));
}
unset($s);
$options = pdo_fetchall("select id,title,thumb,marketprice,productprice,costprice, stock,weight,specs from " . tablename('bj_qmxk_goods_option') . " where goodsid=:id order by id asc", array(':id' => $goodsid));
$specs = array();
if (count($options) > 0) 
{
$specitemids = explode("_", $options[0]['specs'] );
foreach($specitemids as $itemid)
{
foreach($allspecs as $ss)
{
$items= $ss['items'];
foreach($items as $it)
{
if($it['id']==$itemid)
{
$specs[] = $ss;
break;
}
}
}
}
}
if (!empty($goods['hasoption'])) 
{
$options = pdo_fetchall("SELECT * FROM " . tablename('bj_qmxk_goods_option') . " WHERE goodsid=:goodsid order by thumb asc,displayorder asc", array(":goodsid" => $goods['id']));
foreach ($options as $o) 
{
if ($marketprice >= $o['marketprice']) 
{
$marketprice = $o['marketprice'];
}
if ($productprice >= $o['productprice']) 
{
$productprice = $o['productprice'];
}
if ($stock <= $o['stock']) 
{
$stock = $o['stock'];
}
}
}
$params = pdo_fetchall("SELECT * FROM " . tablename('bj_qmxk_goods_param') . " WHERE goodsid=:goodsid order by displayorder asc", array(":goodsid" => $goods['id']));
$carttotal = $this->getCartTotal();
$rmlist = pdo_fetchall("SELECT * FROM " . tablename('bj_qmxk_goods') . " WHERE weid = '{$_W['weid']}
'  and deleted=0 AND status = '1' and ishot='1' ORDER BY displayorder DESC, sales DESC limit 4 ");
$cfg = $this->module['config'];
$ydyy = $cfg['ydyy'];
include $this->template('detail');
}
public function doMobileCheck() 
{
global $_W;
checkauth();
}
public function doMobileAddress() 
{
global $_W, $_GPC;
$from_user = $this->getFromUser();
$from = $_GPC['from'];
$returnurl = urldecode($_GPC['returnurl']);
$operation = $_GPC['op'];
if ($operation == 'post') 
{
$id = intval($_GPC['id']);
$data = array( 'weid' => $_W['weid'], 'openid' => $from_user, 'realname' => $_GPC['realname'], 'mobile' => $_GPC['mobile'], 'province' => $_GPC['province'], 'city' => $_GPC['city'], 'area' => $_GPC['area'], 'address' => $_GPC['address'], );
if (empty($_GPC['realname']) || empty($_GPC['mobile']) || empty($_GPC['address'])) 
{
message('请输完善您的资料！');
}
if (!empty($id)) 
{
unset($data['weid']);
unset($data['openid']);
pdo_update('bj_qmxk_address', $data, array('id' => $id));
message($id, '', 'ajax');
}
else 
{
pdo_update('bj_qmxk_address', array('isdefault' => 0), array('weid' => $_W['weid'], 'openid' => $from_user));
$data['isdefault'] = 1;
pdo_insert('bj_qmxk_address', $data);
$profile = fans_search($from_user, array('realname', 'mobile'));
if(empty($profile['realname'])|| empty($profile['mobile']))
{
fans_update($from_user, array("mobile" => $_GPC['mobile']));
}
$id = pdo_insertid();
if (!empty($id)) 
{
message($id, '', 'ajax');
}
else 
{
message(0, '', 'ajax');
}
}
}
elseif ($operation == 'default') 
{
$id = intval($_GPC['id']);
pdo_update('bj_qmxk_address', array('isdefault' => 0), array('weid' => $_W['weid'], 'openid' =>$from_user));
pdo_update('bj_qmxk_address', array('isdefault' => 1), array('id' => $id));
message(1, '', 'ajax');
}
elseif ($operation == 'detail') 
{
$id = intval($_GPC['id']);
$row = pdo_fetch("SELECT id, realname, mobile, province, city, area, address FROM " . tablename('bj_qmxk_address') . " WHERE id = :id", array(':id' => $id));
message($row, '', 'ajax');
}
elseif ($operation == 'remove') 
{
$id = intval($_GPC['id']);
if (!empty($id)) 
{
$address = pdo_fetch("select isdefault from " . tablename('bj_qmxk_address') . " where id='{$id}
' and weid='{$_W['weid']}
' and openid='".$from_user."' limit 1 ");
if (!empty($address)) 
{
pdo_update("bj_qmxk_address", array("deleted" => 1, "isdefault" => 0), array('id' => $id, 'weid' => $_W['weid'], 'openid' => $from_user));
if ($address['isdefault'] == 1) 
{
$maxid = pdo_fetchcolumn("select max(id) as maxid from " . tablename('bj_qmxk_address') . " where weid='{$_W['weid']}
' and openid='".$from_user."' limit 1 ");
if (!empty($maxid)) 
{
pdo_update('bj_qmxk_address', array('isdefault' => 1), array('id' => $maxid, 'weid' => $_W['weid'], 'openid' => $from_user));
die(json_encode(array("result" => 1, "maxid" => $maxid)));
}
}
}
}
die(json_encode(array("result" => 1, "maxid" => 0)));
}
else 
{
$profile = fans_search($from_user, array('resideprovince', 'residecity', 'residedist', 'address', 'realname', 'mobile'));
$address = pdo_fetchall("SELECT * FROM " . tablename('bj_qmxk_address') . " WHERE deleted=0 and openid = :openid", array(':openid' => $from_user));
$carttotal = $this->getCartTotal();
include $this->template('address');
}
}
private function checkAuth() 
{
global $_W;
checkauth();
}
private function changeWechatSend($id, $status, $msg = '') 
{
global $_W;
$paylog = pdo_fetch("SELECT plid, openid, tag FROM " . tablename('paylog') . " WHERE tid = '{$id}
' AND status = 1 AND type = 'wechat'");
if (!empty($paylog['openid'])) 
{
$paylog['tag'] = iunserializer($paylog['tag']);
$send = array( 'appid' => $_W['account']['payment']['wechat']['appid'], 'openid' => $paylog['openid'], 'transid' => $paylog['tag']['transaction_id'], 'out_trade_no' => $paylog['plid'], 'deliver_timestamp' => TIMESTAMP, 'deliver_status' => $status, 'deliver_msg' => $msg, );
$sign = $send;
$sign['appkey'] = $_W['account']['payment']['wechat']['signkey'];
ksort($sign);
foreach ($sign as $key => $v) 
{
$key = strtolower($key);
$string .= "{$key}
={$v}
&";
}
$send['app_signature'] = sha1(rtrim($string, '&'));
$send['sign_method'] = 'sha1';
$token = $this->get_weixin_token();
if(empty($token)) 
{
return;
}
$sendapi = 'https://api.weixin.qq.com/pay/delivernotify?access_token=' . $token;
$response = ihttp_request($sendapi, json_encode($send));
$response = json_decode($response['content'], true);
if (empty($response)) 
{
message('发货失败，请检查您的公众号权限或是公众号AppId和公众号AppSecret！');
}
if (!empty($response['errcode'])) 
{
message($response['errmsg']);
}
}
}
public function payResult($params) 
{
$fee = intval($params['fee']);
$data = array('status' => $params['result'] == 'success' ? 1 : 0);
if ($params['type'] == 'wechat') 
{
$data['transid'] = $params['tag']['transaction_id'];
}
pdo_update('bj_qmxk_order', $data, array('id' => $params['tid']));
if ($params['from'] == 'return') 
{
if (!empty($this->module['config']['noticeemail'])) 
{
$order = pdo_fetch("SELECT price, from_user FROM " . tablename('bj_qmxk_order') . " WHERE id = '{$params['tid']}
'");
$ordergoods = pdo_fetchall("SELECT goodsid, total FROM " . tablename('bj_qmxk_order_goods') . " WHERE orderid = '{$params['tid']}
'", array(), 'goodsid');
$goods = pdo_fetchall("SELECT id, title, thumb, marketprice, unit, total FROM " . tablename('bj_qmxk_goods') . " WHERE id IN ('" . implode("','", array_keys($ordergoods)) . "')");
$address = pdo_fetch("SELECT * FROM " . tablename('bj_qmxk_address') . " WHERE id = :id", array(':id' => $order['addressid']));
$body = "<h3>购买商品清单</h3> <br />";
if (!empty($goods)) 
{
foreach ($goods as $row) 
{
$body .= "名称：{$row['title']}
，数量：{$ordergoods[$row['id']]['total']}
<br />";
}
}
$body .= "<br />总金额：{$order['price']}
元 （已付款）<br />";
$body .= "<h3>购买用户详情</h3> <br />";
$body .= "真实姓名：{$address['realname']}
<br />";
$body .= "地区：{$address['province']}
- {$address['city']}
- {$address['area']}
<br />";
$body .= "详细地址：{$address['address']}
<br />";
$body .= "手机：{$address['mobile']}
<br />";
ihttp_email($this->module['config']['noticeemail'], '微商城订单提醒', $body);
}
if ($params['type'] == 'credit2') 
{
message('支付成功！', $this->createMobileUrl('myorder'), 'success');
}
else 
{
message('支付成功！', '../../' . $this->createMobileUrl('myorder'), 'success');
}
}
}
public function doWebOption() 
{
$tag = random(32);
global $_GPC;
include $this->template('option');
}
public function doWebSpec() 
{
global $_GPC;
$spec = array( "id" => random(32), "title" => $_GPC['title'] );
include $this->template('spec');
}
public function doWebSpecItem() 
{
global $_GPC;
$spec = array( "id" => $_GPC['specid'] );
$specitem = array( "id" => random(32), "title" => $_GPC['title'], "show" => 1 );
include $this->template('spec_item');
}
public function doWebParam() 
{
$tag = random(32);
global $_GPC;
include $this->template('param');
}
public function doWebExpress() 
{
global $_W, $_GPC;
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
if ($operation == 'display') 
{
$list = pdo_fetchall("SELECT * FROM " . tablename('bj_qmxk_express') . " WHERE weid = '{$_W['weid']}
' ORDER BY displayorder DESC");
}
elseif ($operation == 'post') 
{
$id = intval($_GPC['id']);
if (checksubmit('submit')) 
{
if (empty($_GPC['express_name'])) 
{
message('抱歉，请输入物流名称！');
}
$data = array( 'weid' => $_W['weid'], 'displayorder' => intval($_GPC['express_name']), 'express_name' => $_GPC['express_name'], 'express_url' => $_GPC['express_url'], 'express_area' => $_GPC['express_area'], );
if (!empty($id)) 
{
unset($data['parentid']);
pdo_update('bj_qmxk_express', $data, array('id' => $id));
}
else 
{
pdo_insert('bj_qmxk_express', $data);
$id = pdo_insertid();
}
message('更新物流成功！', $this->createWebUrl('express', array('op' => 'display')), 'success');
}
$express = pdo_fetch("SELECT * FROM " . tablename('bj_qmxk_express') . " WHERE id = '$id' and weid = '{$_W['weid']}
'");
}
elseif ($operation == 'delete') 
{
$id = intval($_GPC['id']);
$express = pdo_fetch("SELECT id  FROM " . tablename('bj_qmxk_express') . " WHERE id = '$id' AND weid=" . $_W['weid'] . "");
if (empty($express)) 
{
message('抱歉，物流方式不存在或是已经被删除！', $this->createWebUrl('express', array('op' => 'display')), 'error');
}
pdo_delete('bj_qmxk_express', array('id' => $id));
message('物流方式删除成功！', $this->createWebUrl('express', array('op' => 'display')), 'success');
}
else 
{
message('请求方式不存在');
}
include $this->template('express', TEMPLATE_INCLUDEPATH, true);
}
public function doWebDispatch() 
{
global $_W, $_GPC;
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
if ($operation == 'display') 
{
$list = pdo_fetchall("SELECT * FROM " . tablename('bj_qmxk_dispatch') . " WHERE weid = '{$_W['weid']}
' ORDER BY displayorder DESC");
}
elseif ($operation == 'post') 
{
$id = intval($_GPC['id']);
if (checksubmit('submit')) 
{
$data = array( 'weid' => $_W['weid'], 'displayorder' => intval($_GPC['dispatch_name']), 'dispatchtype' => intval($_GPC['dispatchtype']), 'dispatchname' => $_GPC['dispatchname'], 'express' => $_GPC['express'], 'firstprice' => $_GPC['firstprice'], 'firstweight' => $_GPC['firstweight'], 'secondprice' => $_GPC['secondprice'], 'secondweight' => $_GPC['secondweight'], 'description' => $_GPC['description'] );
if (!empty($id)) 
{
pdo_update('bj_qmxk_dispatch', $data, array('id' => $id));
}
else 
{
pdo_insert('bj_qmxk_dispatch', $data);
$id = pdo_insertid();
}
message('更新配送方式成功！', $this->createWebUrl('dispatch', array('op' => 'display')), 'success');
}
$dispatch = pdo_fetch("SELECT * FROM " . tablename('bj_qmxk_dispatch') . " WHERE id = '$id' and weid = '{$_W['weid']}
'");
$express = pdo_fetchall("select * from " . tablename('bj_qmxk_express') . " WHERE weid = '{$_W['weid']}
' ORDER BY displayorder DESC");
}
elseif ($operation == 'delete') 
{
$id = intval($_GPC['id']);
$dispatch = pdo_fetch("SELECT id  FROM " . tablename('bj_qmxk_dispatch') . " WHERE id = '$id' AND weid=" . $_W['weid'] . "");
if (empty($dispatch)) 
{
message('抱歉，配送方式不存在或是已经被删除！', $this->createWebUrl('dispatch', array('op' => 'display')), 'error');
}
pdo_delete('bj_qmxk_dispatch', array('id' => $id));
message('配送方式删除成功！', $this->createWebUrl('dispatch', array('op' => 'display')), 'success');
}
else 
{
message('请求方式不存在');
}
include $this->template('dispatch', TEMPLATE_INCLUDEPATH, true);
}
public function doWebAdv() 
{
global $_W, $_GPC;
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
if ($operation == 'display') 
{
$list = pdo_fetchall("SELECT * FROM " . tablename('bj_qmxk_adv') . " WHERE weid = '{$_W['weid']}
' ORDER BY displayorder DESC");
}
elseif ($operation == 'post') 
{
$id = intval($_GPC['id']);
if (checksubmit('submit')) 
{
$data = array( 'weid' => $_W['weid'], 'advname' => $_GPC['advname'], 'link' => $_GPC['link'], 'enabled' => intval($_GPC['enabled']), 'displayorder' => intval($_GPC['displayorder']) );
if (!empty($_GPC['thumb'])) 
{
$data['thumb'] = $_GPC['thumb'];
file_delete($_GPC['thumb-old']);
}
if (!empty($id)) 
{
pdo_update('bj_qmxk_adv', $data, array('id' => $id));
}
else 
{
pdo_insert('bj_qmxk_adv', $data);
$id = pdo_insertid();
}
message('更新幻灯片成功！', $this->createWebUrl('adv', array('op' => 'display')), 'success');
}
$adv = pdo_fetch("select * from " . tablename('bj_qmxk_adv') . " where id=:id and weid=:weid limit 1", array(":id" => $id, ":weid" => $_W['weid']));
}
elseif ($operation == 'delete') 
{
$id = intval($_GPC['id']);
$adv = pdo_fetch("SELECT id  FROM " . tablename('bj_qmxk_adv') . " WHERE id = '$id' AND weid=" . $_W['weid'] . "");
if (empty($adv)) 
{
message('抱歉，幻灯片不存在或是已经被删除！', $this->createWebUrl('adv', array('op' => 'display')), 'error');
}
pdo_delete('bj_qmxk_adv', array('id' => $id));
message('幻灯片删除成功！', $this->createWebUrl('adv', array('op' => 'display')), 'success');
}
else 
{
message('请求方式不存在');
}
include $this->template('adv', TEMPLATE_INCLUDEPATH, true);
}
public function doMobileAjaxdelete() 
{
global $_GPC;
$delurl = $_GPC['pic'];
if (file_delete($delurl)) 
{
echo 1;
}
else 
{
echo 0;
}
}
public function doWebAward() 
{
global $_W;
global $_GPC;
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
if ($operation == 'post') 
{
$award_id = intval($_GPC['award_id']);
if (!empty($award_id)) 
{
$item = pdo_fetch("SELECT * FROM ".tablename('bj_qmxk_credit_award')." WHERE award_id = :award_id" , array(':award_id' => $award_id));
if (empty($item)) 
{
message('抱歉，兑换商品不存在或是已经删除！', '', 'error');
}
}
if (checksubmit('submit')) 
{
if (empty($_GPC['title'])) 
{
message('请输入兑换商品名称！');
}
if (empty($_GPC['credit_cost'])) 
{
message('请输入兑换商品需要消耗的积分数量！');
}
if (empty($_GPC['price'])) 
{
message('请输入商品实际价值！');
}
$credit_cost = intval($_GPC['credit_cost']);
$price = intval($_GPC['price']);
$amount = intval($_GPC['amount']);
$data = array( 'weid' => $_W['weid'], 'title' => $_GPC['title'], 'logo' => $_GPC['logo'], 'deadline' => $_GPC['deadline'], 'amount' => $amount, 'credit_cost' => $credit_cost, 'price' => $price, 'content' => $_GPC['content'], 'createtime' => TIMESTAMP, );
if (!empty($award_id)) 
{
pdo_update('bj_qmxk_credit_award', $data, array('award_id' => $award_id));
}
else 
{
pdo_insert('bj_qmxk_credit_award', $data);
}
message('商品更新成功！', create_url('site/module/award', array('name' => 'bj_qmxk', 'op' => 'display')), 'success');
}
}
else if ($operation == 'delete') 
{
$award_id = intval($_GPC['award_id']);
$row = pdo_fetch("SELECT award_id FROM ".tablename('bj_qmxk_credit_award')." WHERE award_id = :award_id", array(':award_id' => $award_id));
if (empty($row)) 
{
message('抱歉，商品'.$award_id.'不存在或是已经被删除！');
}
pdo_delete('bj_qmxk_credit_award', array('award_id' => $award_id));
message('删除成功！', referer(), 'success');
}
else if ($operation == 'display') 
{
$condition = '';
$list = pdo_fetchall("SELECT * FROM ".tablename('bj_qmxk_credit_award')." WHERE weid = '{$_W['weid']}
' $condition ORDER BY createtime DESC");
}
include $this->template('credit_award');
}
public function doWebCredit() 
{
global $_W;
global $_GPC;
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
if ($operation == 'delete') 
{
$id = intval($_GPC['id']);
$row = pdo_fetch("SELECT id FROM ".tablename('bj_qmxk_credit_request')." WHERE id = :id", array(':id' => $id));
if (empty($row)) 
{
message('抱歉，编号为'.$id.'的兑换请求不存在或是已经被删除！');
}
pdo_delete('bj_qmxk_credit_request', array('id' => $id));
message('删除成功！', referer(), 'success');
}
else if ($operation == 'display') 
{
$condition = '';
$sql = "SELECT * FROM ".tablename('bj_qmxk_credit_award')." as t1,".tablename('bj_qmxk_credit_request')."as t2 WHERE t1.award_id=t2.award_id AND t1.weid = '{$_W['weid']}
' ORDER BY t2.createtime DESC";
$list = pdo_fetchall($sql);
$ar = pdo_fetchall($sql, array(), 'from_user');
$fans = fans_search(array_keys($ar), array('realname', 'mobile', 'credit1', 'residedist'));
}
include $this->template('credit_request');
}
public function doMobileAward() 
{
global $_W, $_GPC;
$from_user = $this->getFromUser();
$award_list = pdo_fetchall("SELECT * FROM ".tablename('bj_qmxk_credit_award')." WHERE weid = '{$_W['weid']}
' and NOW() < deadline and amount > 0");
$profile = fans_search($from_user);
include $this->template('credit_award_new');
}
public function doMobileFillInfo() 
{
global $_W, $_GPC;
$from_user = $this->getFromUser();
$award_id = intval($_GPC['award_id']);
$profile = fans_search($from_user);
$award_info = pdo_fetch("SELECT * FROM ".tablename('bj_qmxk_credit_award')." WHERE award_id = $award_id AND weid = '{$_W['weid']}
'");
include $this->template('credit_fillinfo_new');
}
public function doMobileCredit() 
{
global $_W, $_GPC;
$from_user = $this->getFromUser();
$award_id = intval($_GPC['award_id']);
if (!empty($_GPC['award_id'])) 
{
$fans = fans_search($from_user , array('credit1'));
$award_info = pdo_fetch("SELECT * FROM ".tablename('bj_qmxk_credit_award')." WHERE award_id = $award_id AND weid = '{$_W['weid']}
'");
if ($fans['credit1'] >= $award_info['credit_cost'] && $award_info['amount'] > 0) 
{
$data = array( 'amount' => $award_info['amount'] - 1 );
pdo_update('bj_qmxk_credit_award', $data, array('weid' => $_W['weid'], 'award_id' => $award_id));
$data = array( 'weid' => $_W['weid'], 'from_user' => $from_user , 'award_id' => $award_id, 'createtime' => TIMESTAMP );
pdo_insert('bj_qmxk_credit_request', $data);
$data = array( 'realname' => $_GPC['realname'], 'mobile' => $_GPC['mobile'], 'credit1' => $fans['credit1'] - $award_info['credit_cost'], 'residedist' => $_GPC['residedist'], );
fans_update($from_user , $data);
message('积分兑换成功！', create_url('mobile/module/mycredit', array('weid' => $_W['weid'], 'name' => 'bj_qmxk', 'do' => 'mycredit','op' => 'display')), 'success');
}
else 
{
message('积分不足或商品已经兑空，请重新选择商品！<br>当前商品所需积分:'.$award_info['credit_cost'].'<br>您的积分:'.$fans['credit1'] . '. 商品剩余数量:' . $award_info['amount'] . '<br><br>小提示：<br>每日签到，在线订票，宾馆预订可以赚取积分', create_url('mobile/module/award', array('weid' => $_W['weid'], 'name' => 'bj_qmxk')), 'error');
}
}
else 
{
message('请选择要兑换的商品！', create_url('mobile/module/award', array('weid' => $_W['weid'], 'name' => 'bj_qmxk')), 'error');
}
}
public function doMobileSearch() 
{
global $_GPC, $_W;
$keyword = $_GPC['keyword'];
$url = $_W['siteroot'].$this->createMobileUrl('list2', array('name' =>'bj_qmxk','weid'=>$_W['weid'], 'keyword'=>$keyword, 'sort'=>1));
header("location:$url");
$cfg = $this->module['config'];
$ydyy = $cfg['ydyy'];
include $this->template('list2');
}
public function doMobileMycredit() 
{
global $_W, $_GPC;
$from_user = $this->getFromUser();
$award_list = pdo_fetchall("SELECT * FROM ".tablename('bj_qmxk_credit_award')." as t1,".tablename('bj_qmxk_credit_request')."as t2 WHERE t1.award_id=t2.award_id AND from_user='".$from_user."' AND t1.weid = '{$_W['weid']}
' ORDER BY t2.createtime DESC");
$profile = fans_search($from_user);
$user = pdo_fetchall('SELECT * FROM '.tablename('bj_qmxk_member')." WHERE  weid = :weid  AND from_user = :from_user" , array(':weid' => $_W['weid'],':from_user' => $from_user));
include $this->template('credit_mycredit_new');
}
public function doMobileZhifu() 
{
global $_GPC,$_W;
$pindex = max(1, intval($_GPC['page']));
$psize = 30;
$weid=$_W['weid'];
$from_user = $this->getFromUser();
$cfg = $this->module['config'];
$zhifucommission = $cfg['zhifuCommission'];
$profile = pdo_fetch('SELECT * FROM '.tablename('bj_qmxk_member')." WHERE  weid = :weid  AND from_user = :from_user" , array(':weid' => $_W['weid'],':from_user' => $from_user));
$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('paylog')." WHERE  openid='".$from_user."' AND `weid` = ".$_W['weid']);
$pager = pagination($total, $pindex, $psize);
$list = pdo_fetchall("SELECT * FROM ".tablename('paylog')." WHERE openid='".$from_user."' AND weid=".$_W['weid']." ORDER BY plid DESC LIMIT " . ($pindex - 1) * $psize . "," . $psize);
include $this->template('dakuan');
}
public function doWebZhifu() 
{
global $_GPC,$_W;
$pindex = max(1, intval($_GPC['page']));
$psize = 20;
$weid=$_W['weid'];
$from_user=$_W['fans']['from_user'];
$op = trim($_GPC['op']) ? trim($_GPC['op']) :'list';
$cfg = $this->module['config'];
$zhifucommission = $cfg['zhifuCommission'];
if(!$zhifucommission)
{
message('请先在参数设置，设置佣金打款限额！', $this->createWebUrl('Commission'), 'success');
}
if(empty($_GPC['mobile']))
{
$mobile = 0;
}
else
{
$mobile = $_GPC['mobile'];
}
if($op=='list')
{
if($_GPC['submit'] == '搜索')
{
$list = pdo_fetchall("select * from ".tablename('bj_qmxk_member'). " where mobile = ".$mobile." and status = 1 and flag = 1 and (commission - zhifu) >= ".$zhifucommission." and weid = ".$_W['weid']);
$total=count($list);
include $this->template('zhifu');
exit();
}
if(intval($_GPC['so']) == 1) 
{
$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('bj_qmxk_member')." WHERE status = 1 and flag = 1 and (commission - zhifu) >= ".$zhifucommission." and weid = :weid ", array(':weid' => $_W['weid']));
$pager = pagination($total, $pindex, $psize);
$list = pdo_fetchall("SELECT * FROM ".tablename('bj_qmxk_member')."  WHERE weid=".$_W['weid']."  AND status = 1 and flag = 1 and (commission - zhifu) >= ".$zhifucommission." ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . "," . $psize);
}
else 
{
$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('bj_qmxk_member')." WHERE status = 1 and flag = 1 and (commission - zhifu) >= ".$zhifucommission." AND `weid` = :weid", array(':weid' => $_W['weid']));
$pager = pagination($total, $pindex, $psize);
$list = pdo_fetchall("SELECT * FROM ".tablename('bj_qmxk_member')." WHERE weid=".$_W['weid']."  AND status = 1 and flag = 1 and (commission - zhifu) >= ".$zhifucommission." ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . "," . $psize);
}
include $this->template('zhifu');
}
if($op=='post')
{
if(empty($_GPC['from_user']))
{
message('请选择会员！', create_url('site/module', array('do' => 'zhifu','op'=>'list', 'name' => 'bj_qmxk','weid'=>$_W['weid'])), 'success');
}
if(checksubmit())
{
$chargenum=intval($_GPC['chargenum']);
if($chargenum)
{
pdo_query("update ".tablename('bj_qmxk_member')." SET zhifu=zhifu+'".$chargenum."' WHERE from_user='".$_GPC['from_user']."' AND  weid=".$_W['weid']."  ");
$paylog=array( 'type'=>'zhifu', 'weid'=>$weid, 'openid'=>$_GPC['from_user'], 'tid'=>date('Y-m-d H:i:s'), 'fee'=>$chargenum, 'module'=>'bj_qmxk', 'tag'=>' 后台打款'.$chargenum.'元' );
pdo_insert('paylog',$paylog);
}
}
$from_user = $_GPC['from_user'];
$profile = pdo_fetch('SELECT * FROM '.tablename('bj_qmxk_member')." WHERE  weid = :weid  AND from_user = :from_user" , array(':weid' => $_W['weid'],':from_user' => $from_user));
if(!$profile)
{
message('请选择会员！', create_url('site/module', array('do' => 'zhifu','op'=>'list', 'name' => 'bj_qmxk','weid'=>$_W['weid'])), 'success');
}
$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('paylog')." WHERE  openid='".$_GPC['from_user']."' AND `weid` = ".$_W['weid']);
$pager = pagination($total, $pindex, $psize);
$list = pdo_fetchall("SELECT * FROM ".tablename('paylog')." WHERE openid='".$_GPC['from_user']."' AND weid=".$_W['weid']." ORDER BY plid DESC LIMIT " . ($pindex - 1) * $psize . "," . $psize);
$mlist=pdo_fetchall("SELECT `name`,`title` FROM ".tablename('modules'));
$mtype=array();
foreach($mlist as $k=>$v)
{
$mtype[$v['name']]= $v['title'];
}
include $this->template('zhifu_post');
}
}
public function doWebCharge() 
{
global $_GPC,$_W;
$pindex = max(1, intval($_GPC['page']));
$psize = 20;
$weid=$_W['weid'];
$from_user=$_W['fans']['from_user'];
$op = trim($_GPC['op']) ? trim($_GPC['op']) :'list';
if($op=='list')
{
if($_GPC['submit'] == '搜索')
{
$list = pdo_fetchall("SELECT * FROM ".tablename('fans')."  WHERE weid=".$_W['weid']."  AND mobile = '".$_GPC['mobile']."'  LIMIT 20");
$total=count($list);
include $this->template('charge');
exit();
}
if(intval($_GPC['so']) == 1) 
{
$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('fans')." WHERE weid = :weid  AND mobile<>'' ", array(':weid' => $_W['weid']));
$pager = pagination($total, $pindex, $psize);
$list = pdo_fetchall("SELECT * FROM ".tablename('fans')."  WHERE weid=".$_W['weid']."  AND mobile<>'' ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . "," . $psize);
}
else 
{
$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('fans')." WHERE `weid` = :weid  AND mobile<>''", array(':weid' => $_W['weid']));
$pager = pagination($total, $pindex, $psize);
$list = pdo_fetchall("SELECT * FROM ".tablename('fans')." WHERE weid=".$_W['weid']."  AND mobile<>'' ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . "," . $psize);
}
include $this->template('charge');
}
if($op=='post')
{
if(empty($_GPC['from_user']))
{
message('请选择会员！', create_url('site/module', array('do' => 'charge','op'=>'list', 'name' => 'bj_qmxk','weid'=>$_W['weid'])), 'success');
}
if(checksubmit())
{
$chargenum=intval($_GPC['chargenum']);
if($chargenum)
{
pdo_query("update ".tablename('fans')." SET credit2=credit2+'".$chargenum."' WHERE from_user='".$_GPC['from_user']."' AND  weid=".$_W['weid']."  ");
$paylog=array( 'type'=>'charge', 'weid'=>$weid, 'openid'=>$_GPC['from_user'], 'tid'=>date('Y-m-d H:i:s'), 'fee'=>$chargenum, 'module'=>'bj_qmxk', 'tag'=>' 后台充值'.$chargenum.'元' );
pdo_insert('paylog',$paylog);
}
}
$profile=fans_search($_GPC['from_user']);
if(!$profile)
{
message('请选择会员！', create_url('site/module', array('do' => 'charge','op'=>'list', 'name' => 'bj_qmxk','weid'=>$_W['weid'])), 'success');
}
$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('paylog')." WHERE  openid='".$_GPC['from_user']."' AND `weid` = ".$_W['weid']);
$pager = pagination($total, $pindex, $psize);
$list = pdo_fetchall("SELECT * FROM ".tablename('paylog')." WHERE openid='".$_GPC['from_user']."' AND weid=".$_W['weid']." ORDER BY plid DESC LIMIT " . ($pindex - 1) * $psize . "," . $psize);
$mlist=pdo_fetchall("SELECT `name`,`title` FROM ".tablename('modules'));
$mtype=array();
foreach($mlist as $k=>$v)
{
$mtype[$v['name']]= $v['title'];
}
include $this->template('charge_post');
}
}
public function doMobileXoauth() 
{
global $_W,$_GPC;
$weid = $_W['weid'];
if ($_GPC['code']=="authdeny")
{
exit();
}
if (isset($_GPC['code']))
{
$appid = $_W['account']['key'];
$secret = $_W['account']['secret'];
$serverapp = $_W['account']['level'];
if ($serverapp==2) 
{
if(empty($appid) || empty($secret))
{
return ;
}
}
$state = $_GPC['state'];
$code = $_GPC['code'];
$oauth2_code = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$appid."&secret=".$secret."&code=".$code."&grant_type=authorization_code";
$content = ihttp_get($oauth2_code);
$token = @json_decode($content['content'], true);
if(empty($token) || !is_array($token) || empty($token['access_token']) || empty($token['openid'])) 
{
echo '<h1>获取微信公众号授权'.$code.'失败[无法取得token以及openid], 请稍后重试！ 公众平台返回原始数据为: <br />' . $content['meta'].'<h1>';
exit;
}
$from_user = $token['openid'];
$profile = fans_search($from_user, array('follow'));
if ($profile['follow']==1)
{
$state = 1;
}
if ($state==1 && $serverapp == 2)
{
$access_token =$this->get_weixin_token();
$oauth2_url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=".$access_token."&openid=".$from_user."&lang=zh_CN";
}
else
{
$access_token = $token['access_token'];
$oauth2_url = "https://api.weixin.qq.com/sns/userinfo?access_token=".$access_token."&openid=".$from_user."&lang=zh_CN";
}
$content = ihttp_get($oauth2_url);
$info = @json_decode($content['content'], true);
if(empty($info) || !is_array($info) || empty($info['openid']) || empty($info['nickname']) ) 
{
echo '<h1>获取微信公众号授权失败[无法取得info], 请稍后重试！<h1>';
exit;
}
if ($serverapp == 2) 
{
$row = array( 'weid' => $_W['weid'], 'nickname'=>$info["nickname"], 'realname'=>$info["nickname"], 'gender' => $info['sex'] );
if(!empty($info["country"]))
{
$row['country']=$info["country"];
}
if(!empty($info["province"]))
{
$row['province']=$info["province"];
}
if(!empty($info["city"]))
{
$row['city']=$info["city"];
}
fans_update($from_user, $row);
if(!empty($info["headimgurl"]))
{
pdo_update('fans', array('avatar'=>$info["headimgurl"]), array('from_user' => $from_user));
}
}
if($serverapp != 2 && !(empty($from_user))) 
{
$row = array( 'nickname'=> $info["nickname"], 'realname'=> $info["nickname"], 'gender' => $info['sex'] );
if(!empty($info["country"]))
{
$row['country']=$info["country"];
}
if(!empty($info["province"]))
{
$row['province']=$info["province"];
}
if(!empty($info["city"]))
{
$row['city']=$info["city"];
}
fans_update($from_user, $row);
if(!empty($info["headimgurl"]))
{
pdo_update('fans', array('avatar'=>$info["headimgurl"]), array('from_user' => $from_user));
}
}
$oauth_openid="bj_qmxkt2015011506".$_W['weid'];
setcookie($oauth_openid, $from_user, time()+3600*(24*5));
$url=$_COOKIE["xoauthURL"];
header("location:$url");
exit;
}
else
{
echo '<h1>网页授权域名设置出错!</h1>';
exit;
}
}
function GrabImage($url,$filename="") 
{
if($url=="") return false;
if($filename=="") 
{
$ext=strrchr($url);
if($ext!=".gif" && $ext!=".jpg" && $ext!=".png") return false;
$filename=date("YmdHis").$ext;
}
ob_start();
readfile($url);
$img = ob_get_contents();
ob_end_clean();
$size = strlen($img);
$fp2=@fopen($filename, "a");
fwrite($fp2,$img);
fclose($fp2);
return $filename;
}
private function getShareId() 
{
global $_W, $_GPC;
$from_user = $this->getFromUser();
$profile = pdo_fetch('SELECT * FROM '.tablename('bj_qmxk_member')." WHERE  weid = :weid  AND from_user = :from_user" , array(':weid' => $_W['weid'],':from_user' => $from_user));
$shareid = 'bj_qmxk_sid07'.$_W['weid'];
if(empty($profile['shareid'])) 
{
if(!empty($_COOKIE[$shareid])) 
{
if($profile['id']!=$_COOKIE[$shareid]) 
{
pdo_update('bj_qmxk_member', array('shareid'=>$_COOKIE[$shareid]), array('from_user' => $from_user,':weid' => $_W['weid']));
return $_COOKIE[$shareid];
}
}
return 0;
}
else 
{
return $profile['shareid'];
}
}
private function setmid($fuser) 
{
global $_W,$_GPC;
if (empty($_COOKIE["mid"])) 
{
$profile = pdo_fetch('SELECT * FROM '.tablename('bj_qmxk_member')." WHERE  weid = :weid  AND from_user = :from_user" , array(':weid' => $_W['weid'],':from_user' => $fuser));
if(!empty($profile['id'])) 
{
setcookie("mid",$profile['id']);
}
}
}
private function get_weixin_token() 
{
global $_W, $_GPC;
$account=$_W['account'];
if(is_array($account['access_token']) && !empty($account['access_token']['token']) && !empty($account['access_token']['expire']) && $account['access_token']['expire'] > TIMESTAMP) 
{
return $account['access_token']['token'];
}
else 
{
if(empty($account['weid'])) 
{
message('参数错误.');
}
$appid = $account['key'];
$secret = $account['secret'];
if (empty($appid) || empty($secret)) 
{
message('请填写公众号的appid及appsecret, (需要你的号码为微信服务号)！', create_url('account/post', array('id' => $account['weid'])), 'error');
}
$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$appid}
&secret={$secret}
";
$content = ihttp_get($url);
if(empty($content)) 
{
message('获取微信公众号授权失败, 请稍后重试！');
}
$token = @json_decode($content['content'], true);
if(empty($token) || !is_array($token)) 
{
message('获取微信公众号授权失败, 请稍后重试！ 公众平台返回原始数据为: <br />' . $token);
}
if(empty($token['access_token']) || empty($token['expires_in'])) 
{
message('解析微信公众号授权失败, 请稍后重试！');
}
$record = array();
$record['token'] = $token['access_token'];
$record['expire'] = TIMESTAMP + $token['expires_in'];
$row = array();
$row['access_token'] = iserializer($record);
pdo_update('wechats', $row, array('weid' => $account['weid']));
return $record['token'];
}
}
private function getFromUser() 
{
global $_W,$_GPC;
if(false) 
{
return $_W['fans']['from_user'];
}
$oauth_openid="bj_qmxkt2015011506".$_W['weid'];
$serverapp = $_W['account']['level'];
if ($serverapp==2) 
{
$appid = $_W['account']['key'];
$secret = $_W['account']['secret'];
if(empty($appid) || empty($secret))
{
checkauth();
$this->setmid($_W['fans']['from_user']);
return $_W['fans']['from_user'];
}
}
else 
{
checkauth();
$this->setmid($_W['fans']['from_user']);
return $_W['fans']['from_user'];
}
if (empty($_COOKIE[$oauth_openid])) 
{
$url = $_W['siteroot'].$this->createMobileUrl('xoauth');
setcookie("xoauthURL", "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]", time()+3600*(24*5));
$oauth2_code = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$appid."&redirect_uri=".urlencode($url)."&response_type=code&scope=snsapi_userinfo&state=0#wechat_redirect";
header("location:$oauth2_code");
exit;
}
else 
{
$this->setmid($_COOKIE[$oauth_openid]);
return $_COOKIE[$oauth_openid];
}
}
public function sendtempmsg($template_id, $url, $data, $topcolor) 
{
global $_W,$_GPC;
$from_user =$this->getFromUser();
$tokens =$this->get_weixin_token();
if(empty($tokens)) 
{
return;
}
$postarr = '{"touser":"'.$from_user.'","template_id":"'.$template_id.'","url":"'.$url.'","topcolor":"'.$topcolor.'","data":'.$data.'}';
$res = ihttp_post('https://api.weixin.qq.com/cgi-bin/message/template/send?access_token='.$tokens,$postarr);
return true;
}
}
function pagination1($tcount, $pindex, $psize = 15, $url = '', $context = array('before' => 5, 'after' => 4, 'ajaxcallback' => '')) 
{
global $_W;
$pdata = array( 'tcount' => 0, 'tpage' => 0, 'cindex' => 0, 'findex' => 0, 'pindex' => 0, 'nindex' => 0, 'lindex' => 0, 'options' => '' );
if($context['ajaxcallback']) 
{
$context['isajax'] = true;
}
$pdata['tcount'] = $tcount;
$pdata['tpage'] = ceil($tcount / $psize);
if($pdata['tpage'] <= 1) 
{
return '';
}
$cindex = $pindex;
$cindex = min($cindex, $pdata['tpage']);
$cindex = max($cindex, 1);
$pdata['cindex'] = $cindex;
$pdata['findex'] = 1;
$pdata['pindex'] = $cindex > 1 ? $cindex - 1 : 1;
$pdata['nindex'] = $cindex < $pdata['tpage'] ? $cindex + 1 : $pdata['tpage'];
$pdata['lindex'] = $pdata['tpage'];
if($context['isajax']) 
{
if(!$url) 
{
$url = $_W['script_name'] . '?' . http_build_query($_GET);
}
$pdata['faa'] = 'href="javascript:;" onclick="p(\'' . $_W['script_name'] . $url . '\', \'' . $pdata['findex'] . '\', ' . $context['ajaxcallback'] . ')"';
$pdata['paa'] = 'href="javascript:;" onclick="p(\'' . $_W['script_name'] . $url . '\', \'' . $pdata['pindex'] . '\', ' . $context['ajaxcallback'] . ')"';
$pdata['naa'] = 'href="javascript:;" onclick="p(\'' . $_W['script_name'] . $url . '\', \'' . $pdata['nindex'] . '\', ' . $context['ajaxcallback'] . ')"';
$pdata['laa'] = 'href="javascript:;" onclick="p(\'' . $_W['script_name'] . $url . '\', \'' . $pdata['lindex'] . '\', ' . $context['ajaxcallback'] . ')"';
}
else 
{
if($url) 
{
$pdata['faa'] = 'href="?' . str_replace('*', $pdata['findex'], $url) . '"';
$pdata['paa'] = 'href="?' . str_replace('*', $pdata['pindex'], $url) . '"';
$pdata['naa'] = 'href="?' . str_replace('*', $pdata['nindex'], $url) . '"';
$pdata['laa'] = 'href="?' . str_replace('*', $pdata['lindex'], $url) . '"';
}
else 
{
$_GET['page'] = $pdata['findex'];
$pdata['faa'] = 'href="' . $_W['script_name'] . '?' . http_build_query($_GET) . '"';
$_GET['page'] = $pdata['pindex'];
$pdata['paa'] = 'href="' . $_W['script_name'] . '?' . http_build_query($_GET) . '"';
$_GET['page'] = $pdata['nindex'];
$pdata['naa'] = 'href="' . $_W['script_name'] . '?' . http_build_query($_GET) . '"';
$_GET['page'] = $pdata['lindex'];
$pdata['laa'] = 'href="' . $_W['script_name'] . '?' . http_build_query($_GET) . '"';
}
}
$html = '<div class="pagination pagination-centered"><ul>';
if($pdata['cindex'] > 1) 
{
$html .= "<li><a {$pdata['faa']}
class=\"pager-nav\">首页</a></li>";
$html .= "<li><a {$pdata['paa']}
class=\"pager-nav\">&laquo;上一页</a></li>";
}
if(!$context['before'] && $context['before'] != 0) 
{
$context['before'] = 5;
}
if(!$context['after'] && $context['after'] != 0) 
{
$context['after'] = 4;
}
if($context['after'] != 0 && $context['before'] != 0) 
{
$range = array();
$range['start'] = max(1, $pdata['cindex'] - $context['before']);
$range['end'] = min($pdata['tpage'], $pdata['cindex'] + $context['after']);
if ($range['end'] - $range['start'] < $context['before'] + $context['after']) 
{
$range['end'] = min($pdata['tpage'], $range['start'] + $context['before'] + $context['after']);
$range['start'] = max(1, $range['end'] - $context['before'] - $context['after']);
}
for ($i = $range['start']; $i <= $range['end']; $i++) 
{
if($context['isajax']) 
{
$aa = 'href="javascript:;" onclick="p(\'' . $_W['script_name'] . $url . '\', \'' . $i . '\', ' . $context['ajaxcallback'] . ')"';
}
else 
{
if($url) 
{
$aa = 'href="?' . str_replace('*', $i, $url) . '"';
}
else 
{
$_GET['page'] = $i;
$aa = 'href="?' . http_build_query($_GET) . '"';
}
}
}
}
if($pdata['cindex'] < $pdata['tpage']) 
{
$html .= "<li><a {$pdata['naa']}
class=\"pager-nav\">下一页&raquo;</a></li>";
$html .= "<li><a {$pdata['laa']}
class=\"pager-nav\">尾页</a></li>";
}
$html .= '</ul></div>';
return $html;
}
function haha($hehe)
{
$phone = $hehe;
$mphone = substr($phone,3,6);
$lphone = str_replace($mphone,"****",$phone);
return $lphone;
}
function hehe($string = null) 
{
$name = $string;
preg_match_all("/./us", $string, $match);
if(count($match[0])>7)
{
$mname = '';
for($i=0; $i<7; $i++)
{
$mname = $mname.$match[0][$i];
}
$name = $mname.'..';
}
return $name;
}
function img_url($img = '') 
{
global $_W;
if (empty($img)) 
{
return "";
}
if (substr($img, 0, 6) == 'avatar') 
{
return $_W['siteroot'] . "resource/image/avatar/" . $img;
}
if (substr($img, 0, 8) == './themes') 
{
return $_W['siteroot'] . $img;
}
if (substr($img, 0, 1) == '.') 
{
return $_W['siteroot'] . substr($img, 2);
}
if (substr($img, 0, 5) == 'http:') 
{
return $img;
}
return $_W['attachurl'] . $img;
}
unset($zym_3);
?>