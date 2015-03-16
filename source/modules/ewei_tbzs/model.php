<?php
//发现了time,请自行验证这套程序是否有时间限制.

?>
<?php
defined('IN_IA') or exit('Access Denied');
require_once("qiniu/io.php");
require_once("qiniu/rs.php");
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
function get_item_taobao($itemid = '',$intoshop = false,$taobaourl = '',$pcate=0,$ccate = 0) 
{
	global $_W;
	$g = pdo_fetch("select * from ".tablename('ewei_tbzs_goods')." where taobaoid=:taobaoid limit 1",array(":taobaoid"=>$itemid));
	if($g)
	{
		if($intoshop)
		{
			return array("result"=>1,"goodsid"=>$g['goodsid']);
		}
		else
		{
			return array("result"=>1,"goodsid"=>$g['id']);
		}
	}
	$url = get_info_url($itemid);
	$response = ihttp_get($url);
	if (!isset($response['content'])) 
	{
		return array("result"=>'0',"error"=>'未从淘宝获取到商品信息!');
	}
	$content = $response['content'];
	if(strexists($response['content'],"ERRCODE_QUERY_DETAIL_FAIL"))
	{
		return array("result"=>'0',"error"=>'宝贝不存在!');
	}
	$arr = json_decode($content, true);
	$data = $arr['data'];
	$itemInfoModel = $data['itemInfoModel'];
	$item = array();
	$item['itemId'] = $itemInfoModel['itemId'];
	$item['title'] = $itemInfoModel['title'];
	$item['pics'] = $itemInfoModel['picsPath'];
	$params = array();
	if (isset($data['props'])) 
	{
		$props = $data['props'];
		foreach ($props as $pp) 
		{
			$params[] = array( "title" => $pp['name'], "value" => $pp['value'] );
		}
	}
	$item['params'] = $params;
	$specs = array();
	$options = array();
	if (isset($data['skuModel'])) 
	{
		$skuModel = $data['skuModel'];
		if (isset($skuModel['skuProps'])) 
		{
			$skuProps = $skuModel['skuProps'];
			foreach ($skuProps as $prop) 
			{
				$spec_items = array();
				foreach ($prop['values'] as $spec_item) 
				{
					$spec_items[] = array( 'valueId' => $spec_item['valueId'], 'title' => $spec_item['name'], "thumb" => !empty($spec_item['imgUrl']) ? $spec_item['imgUrl'] : '' );
				}
				$spec = array( "propId" => $prop['propId'], "title" => $prop['propName'], "items" => $spec_items );
				$specs[] = $spec;
			}
		}
		if (isset($skuModel['ppathIdmap'])) 
		{
			$ppathIdmap = $skuModel['ppathIdmap'];
			foreach ($ppathIdmap as $key => $skuId) 
			{
				$option_specs = array();
				$m = explode(";", $key);
				foreach ($m as $v) 
				{
					$mm = explode(":", $v);
					$option_specs[] = array( "propId" => $mm[0], "valueId" => $mm[1] );
				}
				$options[] = array( "option_specs" => $option_specs, "skuId" => $skuId, "stock" => 0, "marketprice" => 0, "specs" => "" );
			}
		}
	}
	$item['specs'] = $specs;
	$stack = $data['apiStack'][0]['value'];
	$value = json_decode($stack, true);
	$item1 = array();
	$data1 = $value['data'];
	$itemInfoModel1 = $data1['itemInfoModel'];
	$item['total'] = $itemInfoModel1['quantity'];
	$item['sales'] = $itemInfoModel1['totalSoldQuantity'];
	if (isset($data1['skuModel'])) 
	{
		$skuModel1 = $data1['skuModel'];
		if (isset($skuModel1['skus'])) 
		{
			$skus = $skuModel1['skus'];
			foreach ($skus as $key => $val) 
			{
				$sku_id = $key;
				foreach ($options as &$o) 
				{
					if ($o['skuId'] === $sku_id) 
					{
						$o['stock'] = $val['quantity'];
						foreach ($val['priceUnits'] as $p) 
						{
							$o['marketprice'] = $p['price'];
						}
						$titles = array();
						foreach ($o['option_specs'] as $osp) 
						{
							foreach ($specs as $sp) 
							{
								if ($sp['propId'] == $osp['propId']) 
								{
									foreach ($sp['items'] as $spitem) 
									{
										if ($spitem['valueId'] == $osp['valueId']) 
										{
											$titles[] = $spitem['title'];
										}
									}
								}
							}
						}
						$o['title'] = $titles;
					}
				}
				unset($o);
			}
		}
	}
	else
	{
		$mprice = 0 ;
		foreach ($itemInfoModel1['priceUnits'] as $p) 
		{
			$mprice = $p['price'];
		}
		$item['marketprice'] =$mprice;
	}
	$item['options'] = $options;
	$item['content'] = array();
	$url = get_detail_url($itemid);
	$response = ihttp_get($url);
	$item['content'] = $response;
	return save_goods($item,$intoshop,$taobaourl);
}
function save_goods($item = array(),$intoshop = false,$taobaourl = '',$pcate=0,$ccate = 0) 
{
	global $_W;
	$config = pdo_fetch("select * from ".tablename('ewei_tbzs_set')." where weid=:weid limit 1",array(":weid"=>$_W['weid']));
	if(empty($config))
	{
		$config = tbzs_config();
	}
	$data = array( "weid" => $_W['weid'], "taobaoid" => $item['itemId'], "taobaourl" => $taobaourl, "title" => $item['title'], "total" => $item['total'], "marketprice"=>$item['marketprice'], "pcate" => $pcate, "ccate" => $ccate, "sales" => $item['sales'], "createtime"=>time(), "updatetime"=>time() );
	$thumb_url = array();
	$pics = $item['pics'];
	$piclen = count($pics);
	if($piclen>0)
	{
		$data['thumb'] = save_image($pics[0],$config );
		if($piclen>1)
		{
			for($i=1;$i<$piclen;$i++)
			{
				$thumb_url[]= array( "attachment"=> save_image($pics[$i] ,$config ) );
			}
		}
	}
	$data['thumb_url'] = serialize($thumb_url);
	$goods = pdo_fetch("select * from " . tablename('ewei_tbzs_goods') . " where taobaoid=:taobaoid", array(":taobaoid" => $item['itemId']));
	if (empty($goods)) 
	{
		pdo_insert("ewei_tbzs_goods", $data);
		$goodsid = pdo_insertid();
	}
	else 
	{
		$goodsid = $goods['id'];
		unset($data['createtime']);
		pdo_update("ewei_tbzs_goods", $data,array("id"=>$goodsid));
	}
	$goods_params = pdo_fetchall("select * from " . tablename('ewei_tbzs_goods_param') . " where goodsid=:goodsid ", array(":goodsid" => $goodsid));
	$params = $item['params'];
	$paramids = array();
	$displayorder = 0;
	foreach ($params as $p) 
	{
		$oldp = pdo_fetch("select * from " . tablename('ewei_tbzs_goods_param') . " where goodsid=:goodsid and title=:title limit 1", array(":goodsid" => $goodsid, ":title" => $p['title']));
		$paramid = 0;
		$d = array( "goodsid" => $goodsid, "title" => $p['title'], "value" => $p['value'], "displayorder" => $displayorder );
		if (empty($oldp)) 
		{
			pdo_insert("ewei_tbzs_goods_param", $d);
			$paramid = pdo_insertid();
		}
		else 
		{
			pdo_update("ewei_tbzs_goods_param", $d, array("id" => $oldp['id']));
			$paramid = $oldp['id'];
		}
		$paramids[] = $paramid;
		$displayorder++;
	}
	if (count($paramids) > 0) 
	{
		pdo_query("delete from " . tablename('ewei_tbzs_goods_param') . " where goodsid=:goodsid and id not in (" . implode(",", $paramids) . ")", array(":goodsid" => $goodsid));
	}
	else 
	{
		pdo_query("delete from " . tablename('ewei_tbzs_goods_param') . " where goodsid=:goodsid ", array(":goodsid" => $goodsid));
	}
	$specs = $item['specs'];
	$specids = array();
	$displayorder = 0;
	$newspecs = array();
	foreach ($specs as $spec) 
	{
		$oldspec = pdo_fetch("select * from " . tablename('ewei_tbzs_spec') . " where goodsid=:goodsid and propId=:propId limit 1", array(":goodsid" => $goodsid, ":propId" => $spec['propId']));
		$specid = 0;
		$d_spec = array( "weid" => $_W['weid'], "goodsid" => $goodsid, "title" => $spec['title'], "displayorder" => $displayorder, "propId" => $spec['propId'] );
		if (empty($oldspec)) 
		{
			pdo_insert("ewei_tbzs_spec", $d_spec);
			$specid = pdo_insertid();
		}
		else 
		{
			pdo_update("ewei_tbzs_spec", $d_spec, array("id" => $oldspec['id']));
			$specid = $oldspec['id'];
		}
		$d_spec['id'] = $specid;
		$specids[] = $specid;
		$displayorder++;
		$spec_items = $spec['items'];
		$spec_itemids = array();
		$displayorder_item = 0;
		$newspecitems = array();
		foreach ($spec_items as $spec_item) 
		{
			$d = array( "weid" => $_W['weid'], "specid" => $specid, "title" => $spec_item['title'], "thumb" => save_image($spec_item['thumb'],$config), "valueId" => $spec_item['valueId'], "show" => 1, "displayorder" => $displayorder_item );
			$oldspecitem = pdo_fetch("select * from " . tablename('ewei_tbzs_spec_item') . " where specid=:specid and valueId=:valueId limit 1", array(":specid" => $specid, ":valueId" => $spec_item['valueId']));
			$spec_item_id = 0;
			if (empty($oldspecitem)) 
			{
				pdo_insert("ewei_tbzs_spec_item", $d);
				$spec_item_id = pdo_insertid();
			}
			else 
			{
				pdo_update("ewei_tbzs_spec_item", $d, array("id" => $oldspecitem['id']));
				$spec_item_id = $oldspecitem['id'];
			}
			$displayorder_item++;
			$spec_itemids[] = $spec_item_id;
			$d['id'] = $spec_item_id;
			$newspecitems[] = $d;
		}
		$d_spec['items'] = $newspecitems;
		$newspecs[] = $d_spec;
		if (count($spec_itemids) > 0) 
		{
			pdo_query("delete from " . tablename('ewei_tbzs_spec_item') . " where specid=:specid and id not in (" . implode(",", $spec_itemids) . ")", array(":specid" => $specid));
		}
		else 
		{
			pdo_query("delete from " . tablename('ewei_tbzs_spec_item') . " where specid=:specid ", array(":specid" => $specid));
		}
		pdo_update("ewei_tbzs_spec", array("content" => serialize($spec_itemids)), array("id" => $oldspec['id']));
	}
	if (count($specids) > 0) 
	{
		pdo_query("delete from " . tablename('ewei_tbzs_spec') . " where goodsid=:goodsid and id not in (" . implode(",", $specids) . ")", array(":goodsid" => $goodsid));
	}
	else 
	{
		pdo_query("delete from " . tablename('ewei_tbzs_spec') . " where goodsid=:goodsid ", array(":goodsid" => $goodsid));
	}
	$minprice = 0;
	$options = $item['options'];
	if (count($options) > 0) 
	{
		$minprice = $options[0]['marketprice'];
	}
	$optionids = array();
	$displayorder = 0;
	foreach ($options as $o) 
	{
		$option_specs = $o['option_specs'];
		$ids = array();
		$valueIds = array();
		foreach ($option_specs as $os) 
		{
			foreach ($newspecs as $nsp) 
			{
				foreach ($nsp['items'] as $nspitem) 
				{
					if ($nspitem['valueId'] == $os['valueId']) 
					{
						$ids[] = $nspitem['id'];
						$valueIds[] = $nspitem['valueId'];
					}
				}
			}
		}
		$ids = implode("_", $ids);
		$valueIds = implode("_", $valueIds);
		$do = array( "displayorder" => $displayorder, "goodsid" => $goodsid, "title" => implode('+', $o['title']), "specs" => $ids, "stock" => $o['stock'], "marketprice" => $o['marketprice'], "skuId" => $o['skuId'] );
		if($minprice>$o['marketprice'])
		{
			$minprice =$o['marketprice'];
		}
		$oldoption = pdo_fetch("select * from " . tablename('ewei_tbzs_goods_option') . " where goodsid=:goodsid and skuId=:skuId limit 1", array(":goodsid" => $goodsid, ":skuId" => $o['skuId']));
		$option_id = 0;
		if (empty($oldoption)) 
		{
			pdo_insert("ewei_tbzs_goods_option", $do);
			$option_id = pdo_insertid();
		}
		else 
		{
			pdo_update("ewei_tbzs_goods_option", $do, array("id" => $oldoption['id']));
			$option_id = $oldoption['id'];
		}
		$displayorder++;
		$optionids[] = $option_id;
	}
	if (count($optionids) > 0) 
	{
		pdo_query("delete from " . tablename('ewei_tbzs_goods_option') . " where goodsid=:goodsid and id not in (" . implode(",", $optionids) . ")", array(":goodsid" => $goodsid));
	}
	else 
	{
		pdo_query("delete from " . tablename('ewei_tbzs_goods_option') . " where goodsid=:goodsid ", array(":goodsid" => $goodsid));
	}
	$response = $item['content'];
	$content = $response['content'];
	preg_match_all("/<img.*?src=[\\\'| \\\"](.*?(?:[\.gif|\.jpg]?))[\\\'|\\\"].*?[\/]?>/", $content, $imgs);
	if (isset($imgs[1])) 
	{
		foreach ($imgs[1] as $img) 
		{
			$im = array( "taobao" => $img, "system" => save_image($img,$config) );
			if(!strexists($im['system'], 'http://') && !strexists($im['system'], 'https://')) 
			{
				$im['system'] = $_W['attachurl'] . $im['system'];
			}
			$images[] = $im;
		}
	}
	preg_match("/tfsContent : \'(.*)\'/", $content, $html);
	$html = iconv("GBK","UTF-8",$html[1]);
	if(isset($images))
	{
		foreach ($images as $img) 
		{
			$html = str_replace($img['taobao'], $img['system'], $html);
		}
	}
	$hasoption = 0;
	if(count($options)>0)
	{
		$hasoption = 1;
	}
	$status = $intoshop?"1":"0";
	$d = array("content" => $html,"hasoption"=>$hasoption,"status"=>$status);
	if($minprice>0)
	{
		$d["marketprice"] =$minprice;
	}
	pdo_update("ewei_tbzs_goods",$d, array("id" => $goodsid) );
	if($intoshop) 
	{
		return copy_goods($goodsid);
	}
	return array("result"=>'1',"goodsid"=>$goodsid);
}
function save_image($url = '',array $config) 
{
	return saveToLocal($url);
	global $_W;
	if( $config['allow']!=1)
	{
		$config = tbzs_config();
	}
	if($config['upload']==0)
	{
		return saveToLocal($url);
	}
	return saveToQiniu($url,$config);
}
function get_info_url($itemid) 
{
	return "http://hws.m.taobao.com/cache/wdetail/5.0/?id=" . $itemid;
}
function get_detail_url($itemid) 
{
	return 'http://hws.m.taobao.com/cache/wdesc/5.0/?id=' . $itemid;
}
function copy_goods($goodsid = '0')
{
	$goods = pdo_fetch("select * from ".tablename('ewei_tbzs_goods')." where id=:id limit 1",array(":id"=>$goodsid));
	if(empty($goods))
	{
		return array("result"=>'0',"error"=>'获取的商品未保存到宝贝仓库!');
	}
	$shop_goods = pdo_fetch("select * from ".tablename('shopping_goods')." where id=:id limit 1",array(":id"=>$goods['goodsid']));
	$goods['goodsid'] = $goodsid;
	unset($goods['id']);
	unset($goods['goodsid']);
	unset($goods['updatetime']);
	unset($goods['taobaoid']);
	unset($goods['taobaourl']);
	$shop_goodsid = 0;
	if(empty($shop_goods))
	{
		pdo_insert("shopping_goods",$goods);
		$shop_goodsid = pdo_insertid();
	}
	else
	{
		pdo_update("shopping_goods",$goods,array("id"=>$shop_goods['id']));
		$shop_goodsid = $shop_goods['id'];
	}
	$paramids = array();
	$params = pdo_fetchall("select * from ".tablename("ewei_tbzs_goods_param")." where goodsid=:goodsid order by displayorder asc",array(":goodsid"=>$goodsid));
	foreach($params as &$p)
	{
		$tbzs_id = $p['id'];
		$tbzs_paramid = $p['paramid'];
		unset($p['paramid']);
		unset($p['id']);
		$paramid = 0;
		$shop_param = pdo_fetch("select * from ".tablename("shopping_goods_param")." where id=:paramid limit 1",array(":paramid"=>$tbzs_paramid));
		if(empty($shop_param))
		{
			$p['goodsid'] = $shop_goodsid;
			pdo_insert("shopping_goods_param",$p);
			$paramid = pdo_insertid();
			pdo_update("ewei_tbzs_goods_param",array("paramid"=>$paramid),array("id"=>$tbzs_id));
		}
		else
		{
			pdo_update("shopping_goods_param",$p,array("id"=>$shop_param['id']));
			$paramid = $shop_param['id'];
		}
		$paramids[] = $paramid;
	}
	if(count($paramids)>0)
	{
		pdo_query("delete from ".tablename('shopping_goods_param')." where goodsid={$shop_goodsid}
	and id not in (".implode(",",$paramids)." )");
}
else
{
	pdo_query("delete from ".tablename('shopping_goods_param')." where goodsid={$shop_goodsid}
");
}
$specids = array();
$specs = pdo_fetchall("select * from ".tablename("ewei_tbzs_spec")." where goodsid=:goodsid order by displayorder asc",array(":goodsid"=>$goodsid));
foreach($specs as &$spec)
{
$tbzs_id =$spec['id'];
$tbzs_specid =$spec['specid'];
unset($spec['specid']);
unset($spec['propId']);
unset($spec['id']);
$specid = 0;
$shop_spec = pdo_fetch("select * from ".tablename("shopping_spec")." where id=:specid limit 1",array(":specid"=>$tbzs_specid));
if(empty($shop_spec))
{
	$spec['goodsid'] = $shop_goodsid;
	pdo_insert("shopping_spec",$spec);
	$specid = pdo_insertid();
	pdo_update("ewei_tbzs_spec",array("specid"=>$specid),array("id"=>$tbzs_id));
}
else
{
	pdo_update("shopping_spec",$spec,array("id"=>$shop_spec['id']));
	$specid = $shop_spec['id'];
}
$specids[] = $specid;
$spec_itemids = array();
$spec_items= pdo_fetchall("select * from ".tablename('ewei_tbzs_spec_item')." where specid=:specid order by displayorder asc",array(":specid"=>$tbzs_id));
foreach($spec_items as &$spec_item)
{
	$tbzs_itemid = $spec_item['id'];
	$tbzs_spec_itemid = $spec_item['spec_item_id'];
	unset($spec_item['spec_item_id']);
	unset($spec_item['valueId']);
	unset($spec_item['id']);
	$spec_itemid = 0;
	$shop_spec_item = pdo_fetch("select * from ".tablename("shopping_spec_item")." where id=:specitemid limit 1",array(":specitemid"=>$tbzs_spec_itemid));
	if(empty($shop_spec_item))
	{
		$spec_item['specid'] =$specid;
		pdo_insert("shopping_spec_item",$spec_item);
		$spec_itemid = pdo_insertid();
		pdo_update("ewei_tbzs_spec_item",array("spec_item_id"=>$specid),array("id"=>$tbzs_itemid));
	}
	else
	{
		pdo_update("shopping_spec_item",$spec_item,array("id"=>$shop_spec_item['id']));
		$spec_itemid = $shop_spec_item['id'];
	}
	$spec_itemids[] = $spec_itemid;
}
unset($spec_item);
if(count($spec_itemids)>0)
{
	pdo_query("delete from ".tablename('shopping_spec_item')." where specid={$specid}
and id not in (".implode(",",$spec_itemids)." )");
}
else
{
pdo_query("delete from ".tablename('shopping_spec_item')." where specid={$specid}
");
}
}
unset($spec);
if(count($specids)>0)
{
pdo_query("delete from ".tablename('shopping_spec')." where goodsid={$shop_goodsid}
and id not in (".implode(",",$specids)." )");
}
else
{
pdo_query("delete from ".tablename('shopping_spec')." where goodsid={$shop_goodsid}
");
}
$options = pdo_fetchall("select * from ".tablename("ewei_tbzs_goods_option")." where goodsid=:goodsid order by displayorder asc",array(":goodsid"=>$goodsid));
$optionids = array();
foreach($options as &$option)
{
$tbzs_id = $option['id'];
$tbzs_optionid = $option['optionid'];
unset($option['optionid']);
unset($option['skuId']);
unset($option['id']);
$optionid = 0;
$shop_option = pdo_fetch("select * from ".tablename("shopping_goods_option")." where id=:optionid limit 1",array(":optionid"=>$tbzs_optionid));
if(empty($shop_option))
{
$option['goodsid'] =$shop_goodsid;
pdo_insert("shopping_goods_option",$option);
$optionid = pdo_insertid();
pdo_update("ewei_tbzs_goods_option",array("optionid"=>$optionid),array("id"=>$tbzs_id));
}
else
{
pdo_update("shopping_goods_option",$option,array("id"=>$shop_option['id']));
$optionid = $shop_option['id'];
}
$optionids[] = $optionid;
}
if(count($optionids)>0)
{
pdo_query("delete from ".tablename('shopping_goods_option')." where goodsid={$shop_goodsid}
and id not in (".implode(",",$optionids)." )");
}
else
{
pdo_query("delete from ".tablename('shopping_goods_option')." where goodsid={$shop_goodsid}
");
}
pdo_update("ewei_tbzs_goods",array("status"=>1,"goodsid"=>$shop_goodsid),array("id"=>$goodsid));
return array("result"=>'1',"goodsid"=>$shop_goodsid);
}
function saveToLocal($url) 
{
global $_W;
set_time_limit(0);
if (empty($url))
{
return '';
}
$ext = strrchr($url, ".");
if ($ext != ".jpeg" && $ext != ".gif" && $ext != ".jpg" && $ext != ".png" )
{
return '';
}
$apath = $_W['config']['upload']['attachdir'];
$path ="images/tbzs/".$_W['weid']."/" . date('Y-m/');
mkdirs(IA_ROOT . "/". $apath. $path);
do 
{
$filename = random(30) . $ext;
}
while(file_exists(IA_ROOT . "/". $apath. $path.'/'. $filename));
$path.= $filename;
$data = file_get_contents($url);
$fp2 = @fopen(IA_ROOT . "/". $apath. $path, "w");
fwrite($fp2, $data);
fclose($fp2);
return $path;
}
function saveToQiniu($url = '',array $config)
{
if(empty($config['access_key']) || empty($config['secret_key']) || empty($config['bucket']))
{
return saveToLocal($url);
}
set_time_limit(0);
if (empty($url))
{
return '';
}
$ext = strrchr($url, ".");
if ($ext != ".jpeg" && $ext != ".gif" && $ext != ".jpg" && $ext != ".png" )
{
return "";
}
$filename = random(30) . $ext;
$contents = @file_get_contents($url);
$storename = $filename;
$bu = $config['bucket'].":".$storename;
$accessKey = $config['access_key'];
$secretKey = $config['secret_key'];
Qiniu_SetKeys($accessKey, $secretKey);
$putPolicy = new Qiniu_RS_PutPolicy($bu);
$upToken = $putPolicy->Token(null);
$putExtra = new Qiniu_PutExtra();
$putExtra->Crc32 = 1;
list($ret, $err) = Qiniu_Put($upToken, $storename, $contents, $putExtra);
if(!empty($err))
{
return "";
}
return "http://".$config['bucket'].".qiniudn.com/".$ret['key'];
}
function json_format($data, $indent = null) 
{
array_walk_recursive($data, 'json_formatProtect');
$data = json_encode($data);
$data = urldecode($data);
$ret = '';
$pos = 0;
$length = strlen($data);
$indent = isset($indent) ? $indent : '    ';
$newline = "\n";
$prevchar = '';
$outofquotes = true;
for ($i = 0; $i <= $length; $i++) 
{
$char = substr($data, $i, 1);
if ($char == '"' && $prevchar != '\\') 
{
$outofquotes = !$outofquotes;
}
elseif (($char == '}' || $char == ']') && $outofquotes) 
{
$ret .= $newline;
$pos --;
for ($j = 0; $j < $pos; $j++) 
{
$ret .= $indent;
}
}
$ret .= $char;
if (($char == ',' || $char == '{' || $char == '[') && $outofquotes) 
{
$ret .= $newline;
if ($char == '{' || $char == '[') 
{
$pos ++;
}
for ($j = 0; $j < $pos; $j++) 
{
$ret .= $indent;
}
}
$prevchar = $char;
}
return $ret;
}
function json_formatProtect(&$val) 
{
if ($val !== true && $val !== false && $val !== null) 
{
$val = urlencode($val);
}
}
function get_pageno_url($url = '',$pageNo = 1)
{
$url.= "/search.htm?pageNo=".$pageNo;
return $url;
}
function get_total_page($url = '',$taobao =false)
{
if(empty($url))
{
return array("totalpage"=>0);
}
$content = get_page_content($url);
$str = "";
if($taobao)
{
$str="/<span class=\"page-info\">(.*)</span>/";
}
else
{
$str = "/<b class=\"ui-page-s-len\">(.*)<\/b>/";
}
preg_match($str, $content, $p);
if(is_array($p))
{
$pages = explode("/",$p[1]);
return array("totalpage"=>$pages[1]);
}
return array("totalpage"=>0);
}
function get_page_content($url = '', $pageNo =1)
{
if(empty($url))
{
return array("totalpage"=>0);
}
$url = get_pageno_url($url,$pageNo);
$url = getRealURL($url);
$response = ihttp_get($url);
if(!isset($response['content']))
{
return array("result"=>0);
}
return $response['content'];
}
function getRealURL($url)
{
if(function_exists("stream_context_set_default"))
{
stream_context_set_default ( array( 'http' => array( 'method' => 'HEAD' ) ) );
}
$header = get_headers($url,1);
if (strpos($header[0],'301') || strpos($header[0],'302')) 
{
if(is_array($header['Location'])) 
{
return $header['Location'][count($header['Location'])-1];
}
else
{
return $header['Location'];
}
}
else 
{
return $url;
}
}
function get_page_items($pageContent = '')
{
$str='/data-id="(.*)"/U';
preg_match_all($str, $pageContent, $items);
if(isset($items[1]))
{
return $items[1];
}
return array();
}
function check_auth()
{
global $_W;
/*$domain = $_SERVER['SERVER_NAME'];
//$response = ihttp_get("http://www.hreset.com/m.php?s={$domain}
//*tbzs");
//if($response['code']==200) 
//{
//if(!isset($response['content']) || $response['content']!='1')
//{
//message("域名未授权，请联系开发者,您的域名: {$domain}
//!","","error");
}
}
else 
{
message("授权服务器响应错误，请联系开发者! 您的域名: {$domain}
</br> 服务器返回信息: ".json_encode($response),"","error");
}*/
}

?>