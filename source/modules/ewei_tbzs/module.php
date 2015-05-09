<?php
//加密方式：php源码混淆类加密。微动吧可以使用VIP版本。
//此程序由在线逆向还原，零壹贰科技技术支持
?>
<?php
defined('IN_IA') or exit('Access Denied');
require_once("config.php");
require_once("model.php");
class Ewei_tbzsModule extends WeModule 
{
	public function __construct() 
	{
	}
	public function fieldsFormDisplay($rid = 0) 
	{
	}
	public function fieldsFormValidate($rid = 0) 
	{
		return true;
	}
	public function fieldsFormSubmit($rid = 0) 
	{
		return true;
	}
	public function ruleDeleted($rid = 0) 
	{
		return true;
	}
	public function doSingle() 
	{
		global $_W, $_GPC;
		if (!$_W['ispost']) 
		{
			check_auth();
		}
		if ($_W['ispost']) 
		{
			set_time_limit(0);
			$ret = array();
			$url = $_GPC['url'];
			$pcate = intval($_GPC['pcate']);
			$ccate = intval($_GPC['ccate']);
			$into_shop = intval($_GPC['into_shop']);
			if (is_numeric($url)) 
			{
				$itemid = $url;
			}
			else 
			{
				preg_match("/id\=(\d+)/i", $url, $matches);
				if (isset($matches[1])) 
				{
					$itemid = $matches[1];
				}
			}
			if (empty($itemid)) 
			{
				die(json_encode(array("result" => 0, "error" => "未获取到 itemid!")));
			}
			die(json_encode(get_item_taobao($itemid, ($into_shop == '1'), $_GPC['url'], $pcate, $ccate)));
		}
		$category = pdo_fetchall("SELECT * FROM " . tablename('shopping_category') . " WHERE weid = '{$_W['weid']}
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
	include $this->template('single');
}
public function doMany() 
{
	global $_W;
	if (!$_W['ispost']) 
	{
		check_auth();
	}
	$category = pdo_fetchall("SELECT * FROM " . tablename('shopping_category') . " WHERE weid = '{$_W['weid']}
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
include $this->template('many');
}
public function doWhole() 
{
set_time_limit(0);
global $_W, $_GPC;
if (!$_W['ispost']) 
{
	check_auth();
}
$op = $_GPC['op'];
$url = "http://" . $_GPC['url'];
$istaobao = !strexists($url, ".tmall.com");
if ($op == 'get_total_page') 
{
	die(json_encode(get_total_page($url, $istaobao)));
}
elseif ($op == 'get_page_items') 
{
	$pageNo = intval($_GPC['pageNo']);
	$pageContent = get_page_content($url, $pageNo);
	$items = get_page_items($pageContent);
	$ret = array( "items" => $items );
	die(json_encode($ret));
}
else 
{
	$category = pdo_fetchall("SELECT * FROM " . tablename('shopping_category') . " WHERE weid = '{$_W['weid']}
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
include $this->template('whole');
}
}
public function doSysset() 
{
global $_W, $_GPC;
if (!$_W['ispost']) 
{
check_auth();
}
$set = pdo_fetch("select * from " . tablename('ewei_tbzs_set') . " where weid=:weid limit 1", array(':weid' => $_W['weid']));
$config = tbzs_config();
if ($_W['ispost']) 
{
$data = array( "weid" => $_W['weid'], "upload" => intval($_GPC['upload']), "access_key" => $_GPC['access_key'], "secret_key" => $_GPC['secret_key'], "bucket" => $_GPC['bucket'], "auto" => intval($_GPC['auto']) );
if (!empty($set)) 
{
	pdo_update('ewei_tbzs_set', $data, array("id" => $set['id']));
}
else 
{
	pdo_insert('ewei_tbzs_set', $data);
}
message("设置保存成功!", referer(), "success");
}
include $this->template('sysset');
}
public function doGoods() 
{
global $_GPC, $_W;
if (!$_W['ispost']) 
{
check_auth();
}
$category = pdo_fetchall("SELECT * FROM " . tablename('shopping_category') . " WHERE weid = '{$_W['weid']}
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
$item = pdo_fetch("SELECT * FROM " . tablename('ewei_tbzs_goods') . " WHERE id = :id", array(':id' => $id));
if (empty($item)) 
{
	message('抱歉，商品不存在或是已经删除！', '', 'error');
}
$allspecs = pdo_fetchall("select * from " . tablename('ewei_tbzs_spec') . " where goodsid=:id order by displayorder asc", array(":id" => $id));
foreach ($allspecs as &$s) 
{
	$s['items'] = pdo_fetchall("select * from " . tablename('ewei_tbzs_spec_item') . " where specid=:specid order by displayorder asc", array(":specid" => $s['id']));
}
unset($s);
$params = pdo_fetchall("select * from " . tablename('ewei_tbzs_goods_param') . " where goodsid=:id order by displayorder asc", array(':id' => $id));
$piclist = unserialize($item['thumb_url']);
$html = "";
$options = pdo_fetchall("select * from " . tablename('ewei_tbzs_goods_option') . " where goodsid=:id order by id asc", array(':id' => $id));
$specs = array();
if (count($options) > 0) 
{
	$specitemids = explode("_", $options[0]['specs']);
	foreach ($specitemids as $itemid) 
	{
		foreach ($allspecs as $ss) 
		{
			$items = $ss['items'];
			foreach ($items as $it) 
			{
				if ($it['id'] == $itemid) 
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
	for ($m = 0; $m < $len; $m++) 
	{
		$k = 0;
		$kid = 0;
		$n = 0;
		for ($j = 0; $j < $newlen; $j++) 
		{
			$rowspan = $rowspans[$m];
			if ($j % $rowspan == 0) 
			{
				$h[$m][$j] = array("html" => "<td rowspan='" . $rowspan . "'>" . $specs[$m]['items'][$kid]['title'] . "</td>", "id" => $specs[$m]['items'][$kid]['id']);
			}
			else 
			{
				$h[$m][$j] = array("html" => "", "id" => $specs[$m]['items'][$kid]['id']);
			}
			$n++;
			if ($n == $rowspan) 
			{
				$kid++;
				if ($kid > count($specs[$m]['items']) - 1) 
				{
					$kid = 0;
				}
				$n = 0;
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
		$val = array("id" => "", "title" => "", "stock" => "", "costprice" => "", "productprice" => "", "marketprice" => "", "weight" => "");
		foreach ($options as $o) 
		{
			if ($ids === $o['specs']) 
			{
				$val = array("id" => $o['id'], "title" => $o['title'], "stock" => $o['stock'], "costprice" => $o['costprice'], "productprice" => $o['productprice'], "marketprice" => $o['marketprice'], "weight" => $o['weight']);
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
if (checksubmit('submit')) 
{
if (empty($_GPC['goodsname'])) 
{
	message('请输入商品名称！');
}
$data = array( 'weid' => intval($_W['weid']), 'displayorder' => intval($_GPC['displayorder']), 'title' => $_GPC['goodsname'], 'description' => $_GPC['description'], 'content' => htmlspecialchars_decode($_GPC['content']), 'goodssn' => $_GPC['goodssn'], 'unit' => $_GPC['unit'], 'createtime' => TIMESTAMP, 'total' => intval($_GPC['total']), 'totalcnf' => intval($_GPC['totalcnf']), 'marketprice' => $_GPC['marketprice'], 'weight' => $_GPC['weight'], 'costprice' => $_GPC['costprice'], 'productprice' => $_GPC['productprice'], 'credit' => intval($_GPC['credit']), 'hasoption' => intval($_GPC['hasoption']) );
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
	pdo_insert('ewei_tbzs_goods', $data);
	$id = pdo_insertid();
}
else 
{
	unset($data['createtime']);
	pdo_update('ewei_tbzs_goods', $data, array('id' => $id));
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
		pdo_insert("ewei_tbzs_goods_param", $a);
		$param_id = pdo_insertid();
	}
	else 
	{
		pdo_update("ewei_tbzs_goods_param", $a, array('id' => $get_param_id));
		$param_id = $get_param_id;
	}
	$paramids[] = $param_id;
}
if (count($paramids) > 0) 
{
	pdo_query("delete from " . tablename('ewei_tbzs_goods_param') . " where goodsid=$id and id not in ( " . implode(',', $paramids) . ")");
}
else 
{
	pdo_query("delete from " . tablename('ewei_tbzs_goods_param') . " where goodsid=$id");
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
		pdo_update("ewei_tbzs_spec", $a, array("id" => $get_spec_id));
		$spec_id = $get_spec_id;
	}
	else 
	{
		pdo_insert("ewei_tbzs_spec", $a);
		$spec_id = pdo_insertid();
	}
	$spec_item_ids = $_POST["spec_item_id_" . $get_spec_id];
	$spec_item_titles = $_POST["spec_item_title_" . $get_spec_id];
	$spec_item_shows = $_POST["spec_item_show_" . $get_spec_id];
	$spec_item_oldthumbs = $_POST["spec_item_oldthumb_" . $get_spec_id];
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
			pdo_update("ewei_tbzs_spec_item", $d, array("id" => $get_item_id));
			$item_id = $get_item_id;
		}
		else 
		{
			pdo_insert("ewei_tbzs_spec_item", $d);
			$item_id = pdo_insertid();
		}
		$itemids[] = $item_id;
		$d['get_id'] = $get_item_id;
		$d['id'] = $item_id;
		$spec_items[] = $d;
	}
	if (count($itemids) > 0) 
	{
		pdo_query("delete from " . tablename('ewei_tbzs_spec_item') . " where weid={$_W['weid']}
	and specid=$spec_id and id not in (" . implode(",", $itemids) . ")");
}
else 
{
	pdo_query("delete from " . tablename('ewei_tbzs_spec_item') . " where weid={$_W['weid']}
and specid=$spec_id");
}
pdo_update("ewei_tbzs_spec", array("content" => serialize($itemids)), array("id" => $spec_id));
$specids[] = $spec_id;
}
if (count($specids) > 0) 
{
pdo_query("delete from " . tablename('ewei_tbzs_spec') . " where weid={$_W['weid']}
and goodsid=$id and id not in (" . implode(",", $specids) . ")");
}
else 
{
pdo_query("delete from " . tablename('ewei_tbzs_spec') . " where weid={$_W['weid']}
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
$idsarr = explode("_", $ids);
$newids = array();
foreach ($idsarr as $key => $ida) 
{
foreach ($spec_items as $it) 
{
if ($it['get_id'] == $ida) 
{
$newids[] = $it['id'];
break;
}
}
}
$newids = implode("_", $newids);
$a = array( "title" => $_GPC['option_title_' . $ids][0], "productprice" => $_GPC['option_productprice_' . $ids][0], "costprice" => $_GPC['option_costprice_' . $ids][0], "marketprice" => $_GPC['option_marketprice_' . $ids][0], "stock" => $_GPC['option_stock_' . $ids][0], "weight" => $_GPC['option_weight_' . $ids][0], "goodsid" => $id, "specs" => $newids );
$totalstocks+=$a['stock'];
if (empty($get_option_id)) 
{
pdo_insert("ewei_tbzs_goods_option", $a);
$option_id = pdo_insertid();
}
else 
{
pdo_update("ewei_tbzs_goods_option", $a, array('id' => $get_option_id));
$option_id = $get_option_id;
}
$optionids[] = $option_id;
}
if (count($optionids) > 0) 
{
pdo_query("delete from " . tablename('ewei_tbzs_goods_option') . " where goodsid=$id and id not in ( " . implode(',', $optionids) . ")");
}
else 
{
pdo_query("delete from " . tablename('ewei_tbzs_goods_option') . " where goodsid=$id");
}
if ($totalstocks > 0) 
{
pdo_update("ewei_tbzs_goods", array("total" => $totalstocks), array("id" => $id));
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
if (isset($_GPC['status'])) 
{
$condition .= " AND status = '" . intval($_GPC['status']) . "'";
}
$list = pdo_fetchall("SELECT * FROM " . tablename('ewei_tbzs_goods') . " WHERE weid = '{$_W['weid']}
' and deleted=0 $condition ORDER BY status DESC, displayorder DESC, id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('ewei_tbzs_goods') . " WHERE weid = '{$_W['weid']}
'  and deleted=0 $condition");
$pager = pagination($total, $pindex, $psize);
}
elseif ($operation == 'delete') 
{
$id = intval($_GPC['id']);
$row = pdo_fetch("SELECT id, thumb FROM " . tablename('ewei_tbzs_goods') . " WHERE id = :id", array(':id' => $id));
if (empty($row)) 
{
message('抱歉，商品不存在或是已经被删除！');
}
pdo_update("ewei_tbzs_goods", array("deleted" => 1), array('id' => $id));
message('删除成功！', referer(), 'success');
}
else if ($operation == 'copy') 
{
$id = $_GPC['id'];
$idArr = $_GPC['idArr'];
if (empty($id) && empty($idArr)) 
{
$this->message("参数错误!");
}
if (!empty($id)) 
{
copy_goods($id);
message("宝贝导入成功!",referer(),"success");
}
else if (!empty($idArr)) 
{
foreach ($_GPC['idArr'] as $k => $id) 
{
$id = intval($id);
copy_goods($id);
}
$this->message('宝贝已经导入成功！', '', 0);
}
}
include $this->template('goods');
}
public function message($error, $url = '', $errno = -1) 
{
$data = array();
$data['errno'] = $errno;
if (!empty($url)) 
{
$data['url'] = $url;
}
$data['error'] = $error;
echo json_encode($data);
exit;
}
}
?>