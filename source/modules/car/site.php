<?php
/**
 * 微汽车模块定义
 *
 * @author 微动力
 * @url
 */
defined('IN_IA') or exit('Access Denied');

class CarModuleSite extends WeModuleSite {
	
	public function doWebIndex(){
		global $_GPC, $_W;
		$theone = pdo_fetch('SELECT * FROM '.tablename('we7car_set')." WHERE `weid` = :weid ",array(':weid' => $_W['weid']));
		if(!empty($theone['thumbArr'])){
			$theone['thumb_url']=explode('|',$theone['thumbArr']);
		}
		
		if(checksubmit('submit')) {
			$title = !empty($_GPC['title']) ? trim($_GPC['title']) : message('请填写公司名称');
			$shoplogo = !empty($_GPC['shop_logo']) ? $_GPC['shop_logo'] : message('请上传首页店标');
			$description = !empty($_GPC['description']) ? trim($_GPC['description']) : message('请填写公司简介');
			$address = !empty($_GPC['address']) ? trim($_GPC['address']) : message('请填写公司地址');
			$opentime = !empty($_GPC['opentime']) ? trim($_GPC['opentime']) : message('请填写营业时间');
			$pre_consult = !empty($_GPC['pre_consult']) ? trim($_GPC['pre_consult']) : message('请填写新车销售、咨询热线');
			$aft_consult = !empty($_GPC['aft_consult']) ? trim($_GPC['aft_consult']) : message('请填写售后预约热线');
			$thumb_url = !empty($_GPC['thumb_url']) ? $_GPC['thumb_url'] : message('请上传频道首页幻灯片');
			$typethumb = !empty($_GPC['typethumb']) ? $_GPC['typethumb'] : message('请上车型背景图');
			$yuyue1thumb = !empty($_GPC['yuyue1thumb']) ? $_GPC['yuyue1thumb'] : message('请上传预约试驾背景图');
			$yuyue2thumb = !empty($_GPC['yuyue2thumb']) ? $_GPC['yuyue2thumb'] : message('请上传预约保养背景图');
			$kefuthumb = !empty($_GPC['kefuthumb']) ? $_GPC['kefuthumb'] : message('请上传客服背景图');
			$messagethumb = !empty($_GPC['messagethumb']) ? $_GPC['messagethumb'] : message('请上传意见反馈背景图');
			$carethumb = !empty($_GPC['carethumb']) ? $_GPC['carethumb'] : message('请上传车主关怀背景图');
			$data = array(
				'weid' => $_W['weid'],
				'title' => $title,
				'shop_logo' => $shoplogo,
				'description' => $description,
				'address' => $address,
				'opentime' => $opentime,
				'pre_consult' => $pre_consult,
				'aft_consult' => $aft_consult,
				'typethumb' => $typethumb,
				'yuyue1thumb' => $yuyue1thumb,
				'yuyue2thumb' => $yuyue2thumb,
				'kefuthumb' => $kefuthumb,
				'messagethumb' => $messagethumb,
				'carethumb' => $carethumb
			);
			if(!empty($_GPC['thumb_url'])){
				$data['weicar_logo'] = $_GPC['thumb_url'][0];
				$data['thumbArr'] = implode('|',$_GPC['thumb_url']);
			}
				
			if(!empty($theone)) {
				$temp = pdo_update('we7car_set',$data,array('weid' => $_W['weid'],'id' => $theone['id']));
			} else {
				$temp = pdo_insert('we7car_set',$data);
			}
			if($temp === false) {
				message('更新首页设置失败！','', 'error');
			} else {
				message('更新首页设置成功！','', 'success');
			}
		}
		include $this->template('web/index');
	}
	public function doMobileIndex(){
		global $_GPC, $_W;
		
		$op = !empty($_GPC['op']) ? trim($_GPC['op']) : 'index';
		
		$theone = pdo_fetch('SELECT * FROM '.tablename('we7car_set')." WHERE `weid` = :weid ",array(':weid' => $_W['weid']));
		if($op == 'index') {
			$news_category = pdo_fetchall('SELECT * FROM '.tablename('we7car_news_category')." WHERE `weid` = :weid AND status = 1",array(':weid' => $_W['weid']));
			if(!empty($theone['thumbArr'])) {
				$theone['thumb_url'] = explode('|',$theone['thumbArr']);
			}
			include $this->template('index');
		} 
		if($op == 'about') {
			include $this->template('about');
		}
	}
		
	
	//汽车品牌管理
	public function doWebBrand(){
		global $_GPC, $_W;
		$op = $_GPC['op'] ? $_GPC['op'] : 'list';
		
		if($op == 'list') {
			$list = pdo_fetchall('SELECT * FROM '.tablename('we7car_brand')." WHERE `weid` = :weid ORDER BY listorder DESC",array(':weid' => $_W['weid']));
			if(checksubmit('submit')) {
				foreach ($_GPC['listorder'] as $key => $val) {
					pdo_update('we7car_brand', array('listorder' => intval($val)),array('id' => intval($key)));
				}
				message('更新品牌排序成功！', $this->createWebUrl('brand', array('op'=>'list')), 'success');
			}
			include $this->template('web/brand_list');
		}
		
		if($op == 'post') {
			$id = intval($_GPC['id']);
			if($id > 0) {
				$theone = pdo_fetch('SELECT * FROM '.tablename('we7car_brand')." WHERE  weid = :weid  AND id = :id" , array(':weid' => $_W['weid'],':id' => $id));
			} else {
				$theone = array('status' => 1,'listorder' => 0);
			}

			if (checksubmit('submit')) {
				$title = trim($_GPC['title']) ?  trim($_GPC['title']) : message('请填写品牌名称！');
				$logo = trim($_GPC['logo']) ?  trim($_GPC['logo']) : message('请上传品牌logo！');
				$description = trim($_GPC['description']) ?  trim($_GPC['description']) : message('请填写品牌简介！');
				$officialweb = trim($_GPC['officialweb']);
				$listorder = intval($_GPC['listorder']);
				$status = intval($_GPC['status']);
				$insert = array(
					'title' => $title,
					'logo' => $logo,
					'description' => $description,
					'officialweb' => $officialweb,
					'listorder' => $listorder,
					'status' => $status,
					'weid' => $_W['weid'],
					'createtime' => TIMESTAMP
				);
				if(empty($id)) {
					pdo_insert('we7car_brand', $insert);
					!pdo_insertid() ?	message('保存品牌数据失败, 请稍后重试.','error') : '';
				} else {
					if(pdo_update('we7car_brand', $insert,array('id' => $id)) === false){
						message('更新品牌数据失败, 请稍后重试.','error');
					}
				}
				message('更新品牌数据成功！', $this->createWebUrl('brand', array('op'=>'list')), 'success');
			}
			include $this->template('web/brand_post');
		}
		
		if($op == 'del') {
			$id = intval($_GPC['id']);
			$thumb = pdo_fetchcolumn("SELECT logo FROM ".tablename('we7car_brand')." WHERE id = :id", array(':id' => $id));
			file_delete($thumb);
			$temp = pdo_delete("we7car_brand",array("weid" => $_W['weid'],'id' => $id));
			if($temp==false){
				message('抱歉，删除数据失败！','', 'error');
			}else{
				pdo_delete("we7car_series",array("weid" => $_W['weid'],'bid' => $id));
				pdo_delete("we7car_type",array("weid" => $_W['weid'],'bid' => $id));
				message('删除数据成功！', $this->createWebUrl('brand', array('op'=>'list')), 'success');
			}
		}
	}
	
	//汽车车系管理
	public function doWebSeries(){
		global $_GPC, $_W;
		$op = $_GPC['op'] ? $_GPC['op'] : 'list';
		
		if($op == 'list') {
			if(checksubmit('submit')) {
				foreach ($_GPC['listorder'] as $key => $val) {
					pdo_update('we7car_series', array('listorder' => intval($val)),array('id' => intval($key)));
				}
				message('更新车系排序成功！', $this->createWebUrl('series', array('op'=>'list')), 'success');
			}
			$list = pdo_fetchall("SELECT * FROM ".tablename('we7car_series')." WHERE `weid` = :weid  ORDER BY `listorder` DESC",array(':weid' => $_W['weid']));
			$series = pdo_fetchall("SELECT * FROM ".tablename('we7car_brand')." WHERE `weid` = :weid  ORDER BY `listorder` DESC",array(':weid' => $_W['weid']));
			$seriesArr=array();
			foreach($series as $v){
				$seriesArr[$v['id']]=$v['title'];
			}
			include $this->template('web/series_list');
		} 
		
		if($op == 'post') {
			$id = intval($_GPC['id']);
			if($id > 0) {
				$theone = pdo_fetch("SELECT * FROM ".tablename('we7car_series')." WHERE  `weid` = :weid  AND `id` = :id AND `status` = 1  LIMIT 1" , array(':weid' => $_W['weid'],':id' => $id));
			} else {
				$theone = array('status' => 1,'listorder' => 0);
			}
			$brand = pdo_fetchall("SELECT title,id FROM ".tablename('we7car_brand')." WHERE `weid` = :weid AND `status` = 1  ORDER BY `listorder` DESC",array(':weid' => $_W['weid']));
			if (checksubmit('submit')) {
				$bid = intval($_GPC['bid']) ?  intval($_GPC['bid']) : message('请选择所属品牌！');
				$title = trim($_GPC['title']) ?  trim($_GPC['title']) : message('请填写车系名称！');
				$subtitle = trim($_GPC['subtitle']) ?  trim($_GPC['subtitle']) : message('请填写车系简称！');
				$thumb = trim($_GPC['thumb']) ?  trim($_GPC['thumb']) : message('请上传车系图片！');
				$description = trim($_GPC['description']) ?  trim($_GPC['description']) : message('请填写品牌简介！');
				$listorder = intval($_GPC['listorder']);
				$status = intval($_GPC['status']);
				$insert = array(
					'bid' => $bid,
					'title' => $title,
					'subtitle' => $subtitle,
					'thumb' => $thumb,
					'description' => $description,
					'listorder' => $listorder,
					'status' => $status,
					'weid' => $_W['weid'],
					'createtime' => TIMESTAMP
				);
				if(empty($id)) {
					pdo_insert('we7car_series', $insert);
					!pdo_insertid() ?	message('保存车系数据失败, 请稍后重试.','error') : '';
				} else {
					if(pdo_update('we7car_series', $insert,array('id' => $id)) === false){
						message('更新车系数据失败, 请稍后重试.','error');
					}
				}
				message('更新车系数据成功！', $this->createWebUrl('series', array('op'=>'list')), 'success');
			}
			include $this->template('web/series_post');			
		}

		if($op == 'del') {
			$id = intval($_GPC['id']);		
			$temp = pdo_delete("we7car_series",array("weid" => $_W['weid'],'id' => $id));
			if($temp==false){
				message('抱歉，删除数据失败！','', 'error');
			}else{
				pdo_delete("we7car_type",array("weid" => $_W['weid'],'sid' => $id));
				message('删除数据成功！', $this->createWebUrl('series', array('op'=>'list')), 'success');
			}
		}
	}
	
	//汽车车型
	public function doWebType(){
		global $_GPC, $_W;
		$op = $_GPC['op'] ? $_GPC['op'] : 'list';
		
		if($op == 'list') {
			$list = pdo_fetchall("SELECT * FROM ".tablename('we7car_type')." WHERE `weid` = :weid  ORDER BY `listorder` DESC",array(':weid' => $_W['weid']));
			
			foreach($list as &$li) {
				$val = pdo_fetch("SELECT id,title FROM ".tablename('we7car_album')." WHERE `weid` = :weid AND `type_id` = :type_id ORDER BY `displayorder` DESC",array(':weid' => $_W['weid'],':type_id' =>$li['id']));
				
				if(!empty($val)) {
					$li['album_id'] = $val['id'];
					$li['album_title'] = $val['title'];
				}
			}
				
			$brandArr=pdo_fetchall("SELECT id,title FROM ".tablename('we7car_brand')." WHERE `weid` = :weid   ORDER BY `listorder` DESC",array(':weid' => $_W['weid']));
			foreach($brandArr as $brand) {
				$branddata[$brand['id']] = $brand;
			}
			$seriesArr=pdo_fetchall("SELECT id,title FROM ".tablename('we7car_series')." WHERE  `weid` = :weid ORDER BY `listorder` DESC",array(':weid' => $_W['weid']));
			foreach($seriesArr as $series) {
				$seriesdata[$series['id']] = $series;
			}
			include $this->template('web/type_list');
		}
		
		if($op == 'post') {
			$id = intval($_GPC['id']);
			if($id>0){
				$theone = pdo_fetch("SELECT * FROM ".tablename('we7car_type')." WHERE  `weid` = :weid  AND `id` = :id" , array(':weid' => $_W['weid'],':id' => $id));
				if(!empty($theone['thumbArr'])){
					$theone['thumb_url']=explode('|',$theone['thumbArr']);
				}
				$series = pdo_fetchall("SELECT title,id FROM ".tablename('we7car_series')." WHERE  `weid` = :weid  AND `bid` = :bid  " , array(':weid' => $_W['weid'],':bid' => $theone['bid']));
			}
			if(empty($theone)){
				$theone=array(
						'listorder'=>0,
						'status'=>1,
				);
			}
			$brand = pdo_fetchall("SELECT id,title FROM ".tablename('we7car_brand')." WHERE `weid` = :weid  ORDER BY `listorder` DESC",array(':weid' => $_W['weid']));			$seriesArr=pdo_fetchall("SELECT id,bid,title FROM ".tablename('we7car_series')." WHERE  `weid` = :weid  ORDER BY `listorder` DESC",array(':weid' => $_W['weid']));
			if (checksubmit('submit')) {
				$title = trim($_GPC['title']) ?  trim($_GPC['title']) : message('请填写车型名称！');
				$bid = intval($_GPC['bid']) ?  intval($_GPC['bid']) : message('请选择所属品牌！');
				$sid = intval($_GPC['sid']) ?  intval($_GPC['sid']) : message('请选择所属车系！');
				$pyear = trim($_GPC['pyear']) ?  trim($_GPC['pyear']) : message('请选择年款！');
				$price1 = trim($_GPC['price1']) ?  trim($_GPC['price1']) : message('请填写车型指导价！');
				$price2 = trim($_GPC['price2']) ?  trim($_GPC['price2']) : message('请填写车型经销商价！');
				$output = trim($_GPC['output']) ?  trim($_GPC['output']) : message('请填写车型排量！');
				$gearnum = trim($_GPC['gearnum']) ?  trim($_GPC['gearnum']) : message('请填写车型档位个数！');
				$gear_box = trim($_GPC['gear_box']) ?  trim($_GPC['gear_box']) : message('请填写车型档位箱！');
				$thumb = $_GPC['thumb_url'] ? $_GPC['thumb_url'] : message('请上传车型图片！');
				$listorder = intval($_GPC['listorder']);
				$status = intval($_GPC['status']);
				$insert = array(
					'bid' => $bid,
					'sid' => $sid,
					'title' => $title,
					'pyear' => $pyear,
					'price1' => $price1,
					'price2' => $price2,
					'output' => $output,
					'gearnum' => $gearnum,
					'gear_box' => $gear_box,
					'output' => $output,
					'xiangceid'=>intval($_GPC['xiangceid']),
					'listorder' => $listorder,
					'status' => $status,
					'weid' => $_W['weid'],
					'createtime' => TIMESTAMP
				);
				if(!empty($_GPC['thumb_url'])){
					$insert['thumb']=$_GPC['thumb_url'][0];
					$insert['thumbArr']=implode('|',$_GPC['thumb_url']);
				}
				if(empty($id)) {
					pdo_insert('we7car_type', $insert);
					!pdo_insertid() ? message('保存车型数据失败, 请稍后重试.','error') : '';
				} else {
					if(pdo_update('we7car_type', $insert,array('id' => $id)) === false){
						message('更新车型数据失败, 请稍后重试.','error');
					}
				}
				message('更新车型数据成功！', $this->createWebUrl('type', array('op'=>'list')), 'success');
			}
			include $this->template('web/type_post');
		}
		
		if($op == 'getseries') {
			$bid = intval($_GPC['bid']);
			if($bid) {
				$series=pdo_fetchall("SELECT id,bid,title FROM ".tablename('we7car_series')." WHERE `weid` = :weid  AND `bid` = :bid ORDER BY `listorder` DESC",array(':weid' => $_W['weid'],':bid' => $bid));
			}
			$html="<option value='0'>请选择车系</option>";
			foreach($series as $val) {
				$html.="<option value='{$val['id']}'>{$val['title']}</option>";
			}
			exit($html);
		}
		
		if($op == 'del') {
			$id = intval($_GPC['id']);
			$thumb = pdo_fetch("SELECT thumb,thumbArr FROM ".tablename('we7car_type')." WHERE id = :id", array(':id' => $id));
			file_delete($thumb['thumb']);
			$thumbarr = explode('|',$thumb['thumbArr']);
			foreach ($thumbarr as $list) {
				file_delete($list);
			}
			$temp = pdo_delete("we7car_type",array("weid" => $_W['weid'],'id' => $id));
			if($temp==false){
				message('抱歉，删除数据失败！','', 'error');
			}else{
				message('删除数据成功！', $this->createWebUrl('series', array('op'=>'list')), 'success');
			}
		}
	}
	
	//客服管理
	public function doWebKefu(){
		global $_GPC, $_W;
		$op = $_GPC['op'] ? $_GPC['op'] : 'list';
		$id = intval($_GPC['id']);
		if($op == 'list'){
			$list = pdo_fetchall("SELECT * FROM ".tablename('we7car_services')." WHERE `weid` = :weid ORDER BY `listorder` DESC",array(':weid' => $_W['weid']));
			if(checksubmit('submit')) {
				foreach ($_GPC['listorder'] as $key => $val) {
					pdo_update('we7car_services', array('listorder' => intval($val)),array('id' => intval($key)));
				}
				message('更新客服排序成功！', $this->createWebUrl('kefu', array('op'=>'list')), 'success');
			}
			include $this->template('web/kefu_list');
		}
		
		if($op == 'post') {
			if($id) {
				$theone = pdo_fetch("SELECT * FROM ".tablename('we7car_services')." WHERE `weid` = :weid AND `id` = :id ORDER BY `listorder` DESC",array(':weid' => $_W['weid'],':id' => $id));
			} else {
				$theone = array('status' => 1,'listorder' => 0);
			}
			if(checksubmit('submit')) {
				$kefuname = trim($_GPC['kefuname']) ?  trim($_GPC['kefuname']) : message('请填写客服姓名！');
				$headthumb = trim($_GPC['headthumb']) ?  trim($_GPC['headthumb']) : message('请上传客服图片！');
				$kefutel = trim($_GPC['kefutel']) ?  trim($_GPC['kefutel']) : message('请填写客服电话！');
				(!intval($_GPC['pre_sales']) && !intval($_GPC['aft_sales'])) ? message('请选择客服类型！') : '';
				$description = trim($_GPC['description']) ?  trim($_GPC['description']) : message('请填写客服简介！');
				$status = intval($_GPC['status']);
				$listorder = intval($_GPC['listorder']);
				$pre_sales = intval($_GPC['pre_sales']);
				$aft_sales = intval($_GPC['aft_sales']);
				
				$insert = array(
					'weid' => $_W['weid'],
					'kefuname' => $kefuname,
					'headthumb' => $headthumb,
					'kefutel' => $kefutel,
					'description' => $description,
					'status' => $status,
					'listorder' => $listorder,
					'pre_sales' => $pre_sales,
					'aft_sales' => $aft_sales,	
				);
				if(empty($id)) {
					pdo_insert('we7car_services', $insert);
					!pdo_insertid() ?	message('保存客服数据失败, 请稍后重试.','error') : '';
				} else {
					if(pdo_update('we7car_services', $insert,array('id' => $id)) === false){
						message('更新客服数据失败, 请稍后重试.','error');
					}
				}
				message('更新客服数据成功！', $this->createWebUrl('kefu', array('op'=>'list')), 'success');
			}
			include $this->template('web/kefu_post');
		}
		if($op == 'del') {
			$kefu= pdo_fetch("SELECT headthumb FROM ".tablename('we7car_services')." WHERE `weid` = :weid AND `id` = :id LIMIT 1",array(':weid' => $_W['weid'],':id' => $id));
			if(!empty($kefu['headthumb'])){
				file_delete($kefu['headthumb']);		
			}
			unset($kefu);
			$temp = pdo_delete("we7car_services",array("weid"=>$_W['weid'],'id'=>$id));
			if($temp==false){
				message('抱歉，删除数据失败！','', 'error');
			}else{
				message('删除数据成功！', $this->createWebUrl('kefu', array('op'=>'list')), 'success');
			}
		}
	}
	//预约试驾
	public function doWebYuyue(){
		global $_GPC, $_W;
		$op = $_GPC['op'] ? $_GPC['op'] : 'list';
		
		if($op == 'post') {
			$id = intval($_GPC['id']); //预约id
			if($id) {
				$item = pdo_fetch("SELECT * FROM ".tablename('we7car_order_set')." WHERE `weid` = :weid AND `id` = :id LIMIT 1",array(':weid' => $_W['weid'],':id' => $id));
			} else {
				$item = array('isshow' => 1,'yytype' => 1,'pertotal' => 1,'start_time' => TIMESTAMP,'end_time' => TIMESTAMP + 86400*7);
			}
			if($item) {
				$sql = 'SELECT * FROM ' . tablename('we7car_order_fields') . ' WHERE `sid` = :id ORDER BY fid ASC';
				$params = array();
				$params[':id'] = $id;
				$ds = pdo_fetchall($sql, $params);
			}

			if(checksubmit('submit')) {
				$title = trim($_GPC['title']) ?  trim($_GPC['title']) : message('请填写预约标题！');
				$description = trim($_GPC['description']) ?  trim($_GPC['description']) : message('请填写预约说明！');
				$mobile = trim($_GPC['mobile']) ?  trim($_GPC['mobile']) : message('请填写预约电话！');
				$topbanner = trim($_GPC['topbanner']) ?  trim($_GPC['topbanner']) : message('请上传订单头部图片！');
				$address = trim($_GPC['address']) ?  trim($_GPC['address']) : message('请填写预约地址！');
				$isshow = intval($_GPC['isshow']);
				$yytype = intval($_GPC['yytype']);
				$location_x = trim($_GPC['location_x']);
				$location_y = trim($_GPC['location_y']);
				$starttime = empty($_GPC['starttime']) ? TIMESTAMP : strtotime($_GPC['starttime']);
				$endtime = empty($_GPC['endtime']) ? TIMESTAMP : strtotime($_GPC['endtime']);
				$insert = array(
					'weid' => $_W['weid'],
					'title' => $title,
					'yytype' => $yytype,
					'description' => $description,			
					'start_time' => $starttime,
					'end_time' => $endtime,
					'address' => $address,
					'mobile' => $mobile,
					'location_x' => $location_x,
					'location_y' => $location_y,
					'topbanner' => $topbanner,
					'note' => trim($_GPC['note']),
					'pertotal' => intval($_GPC['pertotal']),
					'createtime' => TIMESTAMP,
					'isshow' => intval($_GPC['isshow'])			
				);
				//开启某个预约后，同类型的预约将被系统自动关闭
				if($isshow == '1') {
					pdo_update('we7car_order_set', array('isshow' => 0),array('weid' => $_W['weid'],'yytype' => $yytype));
				}
				
				if(empty($id)) {
					pdo_insert('we7car_order_set', $insert);
					!($id = pdo_insertid()) ?	message('保存预约数据失败, 请稍后重试.','','error') : '';
					//自定义字段
					if($_GPC['titles']) {
						$sql = 'DELETE FROM ' . tablename('we7car_order_fields') . ' WHERE `sid`=:sid';
						$params = array();
						$params[':sid'] = $id;
						pdo_query($sql, $params);
						foreach($_GPC['titles'] as $k => $v) {
							$field = array();
							$field['sid'] = $id;
							$field['title'] = trim($v);
							$field['type'] = $_GPC['types'][$k];
							$field['value'] = trim($_GPC['values'][$k]);
							$field['value'] = urldecode($field['value']);
							pdo_insert('we7car_order_fields', $field);
						}
					}
				} else {
					if(pdo_update('we7car_order_set', $insert,array('id' => $id)) === false){
						message('更新预约数据失败, 请稍后重试.','','error');
					}
					//自定义字段
					if($_GPC['titles']) {
						foreach($_GPC['titles'] as $k => $v) {
							$field = array();
							$field['sid'] = $id;
							$field['title'] = trim($v);
							$field['type'] = $_GPC['types'][$k];
							$field['value'] = trim($_GPC['values'][$k]);
							$field['value'] = urldecode($field['value']);
							pdo_update('we7car_order_fields', $field,array('fid' => $k));
						}
					}						
				}
				message('更新预约数据成功！', $this->createWebUrl('yuyue', array('op'=>'list')), 'success');
			}
			include $this->template('web/yuyue_post');
		}
		
		if($op == 'list') {
			$list = pdo_fetchall("SELECT * FROM ".tablename('we7car_order_set')."  WHERE `weid` = :weid ORDER BY `isshow` DESC,`id` DESC",array(':weid' => $_W['weid']));
			include $this->template('web/yuyue_list');
		}
		if($op == 'show') {
			$sid = intval($_GPC['id']);
			$pindex = max(1, intval($_GPC['page']));
			$psize = 50;
			$weid = $_W['weid'];
			if(intval($_GPC['so']) == 1) {
				$ystarttime = empty($_GPC['start']) ? strtotime('-1 month') : strtotime($_GPC['start']);
				$yendtime = empty($_GPC['end']) ? TIMESTAMP : strtotime($_GPC['end']) + 86399;
				$starttime = empty($_GPC['start1']) ? strtotime('-1 month') : strtotime($_GPC['start1']);
				$endtime = empty($_GPC['end1']) ? TIMESTAMP : strtotime($_GPC['end1']) + 86399;
				$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('we7car_order_list')." WHERE sid = :sid AND `dateline` > {$ystarttime} AND `dateline` <{$yendtime} AND `createtime` > {$starttime} AND `createtime` <{$endtime}", array(':sid' => $sid));
				$pager = pagination($total, $pindex, $psize);
				$list = pdo_fetchall("SELECT * FROM ".tablename('we7car_order_list')." as t1 JOIN (SELECT realname,mobile,from_user FROM ims_fans WHERE weid =$weid) as t2 on t1.from_user = t2.from_user WHERE `sid` = :sid AND `dateline` > {$ystarttime} AND `dateline` <{$yendtime} AND `createtime` > {$starttime} AND `createtime` <{$endtime} ORDER BY `createtime` DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize,array(':sid' => $sid));
			} else {
				$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('we7car_order_list')." WHERE sid = :sid", array(':sid' => $sid));
				$pager = pagination($total, $pindex, $psize);
				$list = pdo_fetchall("SELECT * FROM ".tablename('we7car_order_list')." as t1 JOIN (SELECT realname,mobile,from_user FROM ims_fans WHERE weid =$weid) as t2 on t1.from_user = t2.from_user WHERE `sid` = :sid ORDER BY `createtime` DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize,array(':sid' => $sid));
				$ystarttime = empty($_GPC['start']) ? strtotime('-1 month') : strtotime($_GPC['start']);
				$yendtime = empty($_GPC['end']) ? TIMESTAMP : strtotime($_GPC['end']) + 86399;
				$starttime = empty($_GPC['start1']) ? strtotime('-1 month') : strtotime($_GPC['start1']);
				$endtime = empty($_GPC['end1']) ? TIMESTAMP : strtotime($_GPC['end1']) + 86399;
			}
			include $this->template('web/yuyue_show');
		}
		
		if($op == 'del') {
			$id = intval($_GPC['id']);
			/*$thumb = pdo_fetchcolumn("SELECT thumb FROM ".tablename('we7car_brand')." WHERE id = :id", array(':id' => $id));
			file_delete($thumb);*/
				
			$temp = pdo_delete("we7car_order_list",array('id' => $id));
			if($temp == false){
				message('抱歉，删除订单数据失败！','', 'error');
			}else{
				pdo_delete("we7car_order_data",array('srid' => $id)); //删除自定义数据
				message('删除数据成功！',$this->createWebUrl('yuyue',array('op' => show,'id' => $sid)), 'success');
			}
		}
		
		if($op == 'showdetail') {
			$id = intval($_GPC['id']);
			$weid = $_W['weid'];
			$orderone = pdo_fetch("SELECT * FROM".tablename('we7car_order_list')." as t1 JOIN (SELECT realname,mobile,from_user FROM ims_fans WHERE weid =$weid) as t2 on t1.from_user = t2.from_user  WHERE `id` = :id LIMIT 1",array(':id' => $id));
			//获取自定义字段
			$fields  = pdo_fetchall("SELECT * FROM ".tablename('we7car_order_fields')." WHERE `sid` = :sid ORDER BY `fid` ASC",array(':sid' => $orderone['sid']));
			
			if(!empty($fields)) {
				//获取自定义字段的数据
				$fieldsdata  = pdo_fetchall("SELECT * FROM ".tablename('we7car_order_data')." WHERE `sid` = :sid AND `srid` = :srid ",array(':sid' => $orderone['sid'],':srid' => $id));
				if($fieldsdata){
					foreach($fieldsdata as $fielddata) {
						$orderone['data'][$fielddata['sfid']] = $fielddata['data'];
					}
				}
			}
			//获取预约的信息
			$reply = pdo_fetch("SELECT * FROM".tablename('we7car_order_set')." WHERE `id` = :id AND `weid` = :weid LIMIT 1",array(':id' => $orderone['sid'],':weid' => $_W['weid']));
			include $this->template('web/yuyue_showdetail');
		}
		
		if($op == 'status') {
			$id = intval($_GPC['id']);
			pdo_update('we7car_order_list', array('status' => intval($_GPC['status'])),array('id' => $id));
			message('更新订单状态成功.',$this->createWebUrl('yuyue',array('op' => 'show','id' => intval($_GPC['sid']))),'success');
		}
		
		if($op == 'yuyuedel') {
			$id = intval($_GPC['id']);
			$thumb = pdo_fetchcolumn("SELECT topbanner FROM ".tablename('we7car_order_set')." WHERE id = :id", array(':id' => $id));
			file_delete($thumb);
			$temp = pdo_delete("we7car_order_set",array('id' => $id,'weid' => $_W['weid']));
			if($temp == false) {
				message('抱歉，删除预约数据失败！','', 'error');
			} else {
				pdo_delete("we7car_order_fields",array('sid' => $id));
				pdo_delete("we7car_order_data",array('sid' => $id));
				pdo_delete("we7car_order_list",array('sid' => $id));
				message('删除预约成功！',$this->createWebUrl('yuyue',array('op' =>'list')), 'success');
			}
		}
}
	//车系列
	public function doMobileSeries(){
		global $_GPC, $_W;
		$op = $_GPC['op'] ? $_GPC['op'] : 'brand';
		$main_off = 1;
		$bid = intval($_GPC['bid']); //品牌id
		$company = pdo_fetch('SELECT * FROM '.tablename('we7car_set')." WHERE  weid = :weid LIMIT 1" , array(':weid' => $_W['weid']));
		if(!$bid) {
			$brand = pdo_fetchall('SELECT * FROM '.tablename('we7car_brand')." WHERE  weid = :weid AND `status` = 1 ORDER BY `listorder` DESC" , array(':weid' => $_W['weid']));	
		} else {
			$brand = pdo_fetch('SELECT * FROM '.tablename('we7car_brand')." WHERE  weid = :weid  AND id = :id" , array(':weid' => $_W['weid'],':id' => $bid));	
		}
	 	
		if($op=='series') {
			if($bid) {
				$series = pdo_fetchall("SELECT * FROM ".tablename('we7car_series')." WHERE weid = :weid AND `status` = 1 AND bid = :bid ORDER BY listorder DESC",array(':weid' => $_W['weid'],':bid' => $bid));
			}else{
				$series = pdo_fetchall("SELECT * FROM ".tablename('we7car_series')." WHERE weid = :weid AND `status` = 1  ORDER BY listorder DESC",array(':weid' => $_W['weid']));
			}
		}
		
		if($op=='type') {
			$sid = intval($_GPC['sid']);
			$serieone = pdo_fetch("SELECT * FROM ".tablename('we7car_series')." WHERE `weid` = :weid AND `id` = :sid ORDER BY `listorder` DESC",array(':weid' => $_W['weid'],':sid' => $sid));
			$types = pdo_fetchall("SELECT * FROM ".tablename('we7car_type')." WHERE `weid` = :weid AND `status` = 1  AND `sid` = :sid ORDER BY `listorder` DESC",array(':weid' => $_W['weid'],':sid' => $sid));
		}
		
		if($op=='typedetail') {
			$id = intval($_GPC['id']);
			$typeone = pdo_fetch("SELECT * FROM ".tablename('we7car_type')." WHERE `weid` = :weid AND `id` = :id ORDER BY `listorder` DESC",array(':weid' => $_W['weid'],':id' => $id));
			$album_id = pdo_fetch("SELECT id FROM ".tablename('we7car_album')." WHERE `weid` = :weid AND `type_id` = :type_id",array(':weid' => $_W['weid'],':type_id' => $id));
			if(!empty($album_id)) {
				$typeone['album_id'] = $album_id['id'];
			} 
			if(!empty($typeone['thumbArr'])){
				$typeone['thumb_url']=explode('|',$typeone['thumbArr']);
			}

		}
		include $this->template('series_series');	
	}
	//联系客服
	public function doMobileKefu(){
		global $_GPC, $_W;
		$op = $_GPC['op'] ? $_GPC['op'] : 'series';
		$list1 = pdo_fetchall("SELECT * FROM ".tablename('we7car_services')." WHERE `weid` = :weid AND `status` = 1  AND `pre_sales` = 1 ORDER BY `listorder` DESC",array(':weid' => $_W['weid']));
		$list2 = pdo_fetchall("SELECT * FROM ".tablename('we7car_services')." WHERE `weid` = :weid AND `status` = 1  AND `aft_sales` = 1 ORDER BY `listorder` DESC",array(':weid' => $_W['weid']));
		include $this->template('kefu_index');
	}
	//试驾预约/预约保养
	public function doMobileYuyue(){
		global $_GPC, $_W;
		checkauth();
		$op = trim($_GPC['op']);
		//获取select下的option
		$brands = pdo_fetchall('SELECT * FROM '.tablename('we7car_brand')." WHERE `weid` = :weid AND `status` = 1 ORDER BY listorder DESC",array(':weid' => $_W['weid']));
		if($op == 'getseries') {
			$bid = intval($_GPC['bid']);
			$ty = trim($_GPC['ty']);
			if($bid) {
				if($ty == 'series'){
					$datas=pdo_fetchall("SELECT id,bid,title FROM ".tablename('we7car_series')." WHERE `weid` = :weid AND `status` = 1 AND `bid` = :bid ORDER BY `listorder` DESC",array(':weid' => $_W['weid'],':bid' => $bid));
					$html="<option value='0'>请选择车系</option>";
					foreach($datas as $val) {
						$val['val'] = $val['id'].'='.$val['title'];
						$html.="<option value='{$val['val']}'>{$val['title']}</option>";
					}
				} elseif($ty == 'types') {
		
					$datas=pdo_fetchall("SELECT id,title FROM ".tablename('we7car_type')." WHERE `weid` = :weid AND `status` = 1 AND `sid` = :bid ORDER BY `listorder` DESC",array(':weid' => $_W['weid'],':bid' => $bid));
					$html="<option value='0'>请选择车型</option>";
					foreach($datas as $val) {
						$val['val'] = $val['id'].'='.$val['title'];
						$html.="<option value='{$val['val']}'>{$val['title']}</option>";
					}
				}
			}
			exit($html);
		}
		
		$lid = intval($_GPC['id']);//订单id
		$yytype = intval($_GPC['yytype']) ? intval($_GPC['yytype']) : intval($_GPC['__state']); //预约类型
		//获取预约信息
		$reply = pdo_fetch("SELECT * FROM ".tablename('we7car_order_set')." WHERE `weid` = :weid AND `yytype` = :yytype AND `isshow` = 1 ORDER BY `id` DESC LIMIT 1",array(':weid' => $_W['weid'],':yytype' => $yytype));
		if(!$reply) {
			message('抱歉，暂无预约信息.');
		}
		if ($reply['start_time'] > TIMESTAMP) {
			message('当前预约活动还未开始！');
		}
		if ($reply['end_time'] < TIMESTAMP) {
			message('当前预约活动已经结束！');
		}
		//获取预约的自定义字段
		if(!empty($reply)) {
			$sql = 'SELECT * FROM ' . tablename('we7car_order_fields') . ' WHERE `sid` = :sid ORDER BY fid ASC';
			$params = array();
			$params[':sid'] = $reply['id'];
			$ds = pdo_fetchall($sql, $params);
		}
		if(!empty($ds)) {
			foreach($ds as &$d) {
				if($d['type'] == 'select') {
					$d['option'] = explode('|',$d['value']);
				}
			}
			foreach($ds as $r) {
				$fields[$r['fid']] = $r;
			}
		}
		
		$user = fans_search($_W['fans']['from_user'],array('realname','mobile'));
		if(empty($user['realname']) || empty($user['mobile'])) {
			$userinfo = 0;
		}
		//获取某用户的预约次数
		$pertotal = 0;
		$pertotal = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('we7car_order_list')." WHERE sid = :sid AND from_user = :openid AND yytype = :yytype", array(':sid' => $reply['id'], ':openid' => $_W['fans']['from_user'],':yytype' => $yytype));
		if ($pertotal >= $reply['pertotal'] && $reply['pertotal']!=0) {
			$pererror = 1;
		}

		if($lid) {
			//得到某个订单
			$order = pdo_fetch("SELECT * FROM ".tablename('we7car_order_list')." WHERE `id` = :id  AND `yytype` = :yytype LIMIT 1",array(':id' => $lid,':yytype' => $yytype));
			$order['brand_val'] = $order['brand'].'='.$order['brand_cn'];
			$order['series_val'] = $order['serie'].'='.$order['serie_cn'];
			$order['type_val'] = $order['type'].'='.$order['type_cn'];
			$order['realname'] = $user['realname'];
			$order['mobile'] = $user['mobile'];
			$order['dateline'] = $order['dateline'] ? date('Y-m-d', $order['dateline']) : date('Y-m-d');
			//初始化车系和车型
			$eseries = pdo_fetchall('SELECT id,title FROM '.tablename('we7car_series')." WHERE `weid` = :weid AND `bid` = :bid AND `status` = 1 ORDER BY listorder DESC",array(':weid' => $_W['weid'],':bid' => $order['brand']));
			$etypes = pdo_fetchall('SELECT id,title FROM '.tablename('we7car_type')." WHERE `weid` = :weid AND `sid` = :sid AND `status` = 1 ORDER BY listorder DESC",array(':weid' => $_W['weid'],':sid' => $order['serie']));
			
			if(!empty($ds)) {
				//如果有自定义字段
				$fieldsdata  = pdo_fetchall("SELECT * FROM ".tablename('we7car_order_data')." WHERE `srid` = :srid ",array(':srid' => $lid));
				if($fieldsdata){
					foreach($fieldsdata as $fielddata) {
						$order['data'][$fielddata['sfid']] = $fielddata['data'];
					}
				}
			}
		} else {
			$order['dateline'] = date('Y-m-d');
		}
		
		if(checksubmit('submit')) {
			$sid = intval($reply['id']); //某条预约的id
			if($pererror == 1 && !$lid){
				message("没人可预约{$reply['pertotal']}次.");
			}
			if(!$sid) {
				message('预约信息获取失败.');
			}
			//更新粉丝的手机号和姓名
			if($userinfo == '0') {
				fans_update($_W['fans']['from_user'],array('realname' => trim($_GPC['realname']),'mobile' => trim($_GPC['tel'])));
			}				
			$barr = explode('=',trim($_GPC['brand']));
			$sarr = explode('=',trim($_GPC['serie']));
			$tarr = explode('=',trim($_GPC['types']));
			$insert = array(
				'sid' => $sid,
				'from_user' => $_W['fans']['from_user'],
				'dateline' => strtotime($_GPC['dateline']),
				'yytype' => intval($_GPC['yytype']),
				'brand' => $barr[0],
				'brand_cn' => $barr[1],
				'serie' => $sarr[0],
				'serie_cn' => $sarr[1],
				'type' => $tarr[0],
				'type_cn' => $tarr[1],		
				'note' => trim($_GPC['note']),
				'createtime' => TIMESTAMP
			);
			foreach($_GPC as $key => $value) {
				if(strexists($key, 'field_')) {
					$sfid = intval(str_replace('field_', '', $key));
					$field = $fields[$sfid];
					if($sfid && $field) {
							$entry = array();
							$entry['sid'] = $sid;
							$entry['srid'] = 0;
							$entry['sfid'] = $sfid;
							$entry['createtime'] = TIMESTAMP;
							$entry['data'] = strval($value);
							$datas[] = $entry;
					}
				}	
			}
			if(!$lid){
				if(pdo_insert('we7car_order_list', $insert) != 1) {
					message('保存失败.');
				}
				$rid = pdo_insertid();
				if(empty($rid)) {
					message('保存失败.');
				}
				foreach($datas as &$r) {
					$r['srid'] = $rid;
					pdo_insert('we7car_order_data', $r);
				}
			} else {
				if(pdo_update('we7car_order_list', $insert,array('id' => $lid)) != 1) {
					message('更新订单失败.');
				}
				foreach($datas as &$r) {
					$r['srid'] = $lid;
					pdo_update('we7car_order_data', $r,array('sfid' => $r['sfid'],'srid' => $lid));
				}
			}
			message('成功', $this->createMobileUrl('mybook',array('yytype' => $insert['yytype'])),'success');
		}
		include $this->template('yuyue');
	}	
	
	public function doMobileMybook(){
		global $_GPC, $_W;
		checkauth();
		$user = fans_search($_W['fans']['from_user'],array('realname','mobile'));
		$op = trim($_GPC['op']);
		$yytype = intval($_GPC['yytype']);
		$reply = pdo_fetch("SELECT * FROM ".tablename('we7car_order_set')." WHERE `weid` = :weid AND `yytype` = :yytype AND `isshow` = 1 ORDER BY `id` DESC LIMIT 1",array(':weid' => $_W['weid'],':yytype' => $yytype));
		$results  = pdo_fetchall("SELECT * FROM ".tablename('we7car_order_list')." WHERE `from_user` = :from_user AND `sid` = :sid ORDER BY `createtime` DESC",array(':sid' => $reply['id'],':from_user' => $_W['fans']['from_user'] ));
		foreach($results as &$result) {
			$result['realname'] = $user['realname'];
			$result['mobile'] = $user['mobile'];
		}
		//获取自定义字段
		$fields  = pdo_fetchall("SELECT * FROM ".tablename('we7car_order_fields')." WHERE `sid` = :sid ORDER BY `fid` ASC",array(':sid' => $reply['id']));
		
		if(!empty($fields)) {
			foreach($results as &$result) {
			//获取自定义字段的数据
				$fieldsdata  = pdo_fetchall("SELECT * FROM ".tablename('we7car_order_data')." WHERE `sid` = :sid AND `srid` = :srid ",array(':sid' => $reply['id'],':srid' => $result['id']));
				if($fieldsdata){
					foreach($fieldsdata as $fielddata) {
						$result['data'][$fielddata['sfid']] = $fielddata['data'];
					}	
				}
			}
		}
		
		$id = intval($_GPC['id']);
		if($op == 'del' && $id) {
			$temp = pdo_delete("we7car_order_list",array('id' => $id,'sid' => intval($_GPC['sid'])));
			if($temp==false){
				message('抱歉，删除数据失败！',$this->createMobileUrl('mybook',array('yytype' => $yytype)), 'error');
			}else{
				pdo_delete("we7car_order_data",array('srid' => $id)); //删除自定义数据
				message('删除数据成功！', $this->createMobileUrl('mybook',array('yytype' => $yytype)), 'success');
			}
		}
		include $this->template('yuyue_mybook');
	}
	
	public function doWebGuanhuai(){
		global $_GPC, $_W;
		$op = trim($_GPC['op']) ? trim($_GPC['op']) :'set';
		$pindex = max(1, intval($_GPC['page']));
		$psize = 50;
		if($op == 'save') {
			$insert=array(
				'weid' => $_W['weid'],
				'guanhuai_thumb' => $_GPC['guanhuai_thumb'],
				'create_time' => TIMESTAMP,
			);
			$temp = pdo_update('we7car_set', $insert,array('weid' => $_W['weid']));
			if($temp==false){
				$temp = pdo_insert('we7car_set', $insert);
			}
			if($temp==false){
				message('抱歉，操作数据失败！','', 'error');
			}else{
				message('更新设置数据成功！', create_url('site/module', array('do' => 'Guanhuai','op'=>'set', 'name' => 'car','weid'=>$weid)), 'success');
			}		
		}
		if($op == 'set') {
			$row = pdo_fetch  ("SELECT guanhuai_thumb FROM ".tablename('we7car_set')." WHERE  weid = :weid  " , array(':weid' => $_W['weid']));
			include $this->template('web/guanhuai_set');
		}
		if($op == 'car') {
			if(intval($_GPC['so']) == 1) {
				$starttime = empty($_GPC['start']) ? strtotime('-1 month') : strtotime($_GPC['start']);
				$endtime = empty($_GPC['end']) ? TIMESTAMP : strtotime($_GPC['end']) + 86399;
				$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('we7car_care')." WHERE weid = :weid  AND `createtime` > {$starttime} AND `createtime` < {$endtime}", array(':weid' => $_W['weid']));
				$pager = pagination($total, $pindex, $psize);
				$list = pdo_fetchall("SELECT * FROM ".tablename('we7car_care')."  WHERE `weid` = :weid  AND `createtime` > {$starttime} AND `createtime` < {$endtime} ORDER BY `createtime` DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize,array(':weid' => $_W['weid']));
			} else {
				$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('we7car_care')." WHERE `weid` = :weid", array(':weid' => $_W['weid']));
				$pager = pagination($total, $pindex, $psize);
				$list = pdo_fetchall("SELECT * FROM ".tablename('we7car_care')."  WHERE `weid` = :weid ORDER BY `createtime` DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize,array(':weid' => $_W['weid']));
				$starttime = empty($_GPC['start']) ? strtotime('-1 month') : strtotime($_GPC['start']);
				$endtime = empty($_GPC['end']) ? TIMESTAMP : strtotime($_GPC['end']) + 86399;
			}	
			include $this->template('web/car_show');
		}
		if($op == 'del') {
			$id = intval($_GPC['id']);
			$thumb = pdo_fetchcolumn("SELECT car_photo FROM ".tablename('we7car_care')." WHERE id = :id", array(':id' => $id));
			file_delete($thumb);
			$temp = pdo_delete("we7car_care",array("weid" => $_W['weid'],'id' => $id));
			if($temp==false){
				message('抱歉，删除数据失败！','', 'error');
			}else{
				message('删除数据成功！', $this->createWebUrl('guanhuai', array('op'=>'car')), 'success');
			}
		}
		if($op == 'showdetail') {
			$id = intval($_GPC['id']);
			$carone = pdo_fetch("SELECT * FROM".tablename('we7car_care')." WHERE `id` = :id LIMIT 1",array(':id' => $id));
			include $this->template('web/car_showdetail');
		}
	}
	
	//常用工具
	public function doWebTool(){
		global $_GPC, $_W;
		$op = trim($_GPC['op']);
		if($op == 'save') {
			$insert = array(
					'weid' => $_W['weid'],
					'tools' => $_GPC['tools'],
					'create_time' => time(),
			);
			
			$temp = pdo_update('we7car_set', $insert,array('weid' => $_W['weid']));
			if($temp == false){
				$temp = pdo_insert('we7car_set', $insert);
			}
			if($temp == false){
				message('抱歉，更新设置数据失败！','', 'error');
			}else{
				message('更新设置数据成功！', create_url('site/module', array('do' => 'Message','op' => 'set', 'name' => 'car','weid' => $_W['weid'])), 'success');
			}
		}
		$tools = pdo_fetchcolumn  ("SELECT tools FROM ".tablename('we7car_set')." WHERE  weid = :weid  " , array(':weid' => $_W['weid']));
		if($tools === false){
			$toolsArr=array(
					'1' => true,
					'2' => true,
					'3' => true,
					'4' => true,
					'5' => true,
			);
		}else{
			$toolsArr = array();
			$tools = explode(",",$tools);
			foreach($tools as $v) {
				$toolsArr[$v] = true;
			}
		}
		include $this->template('web/tool_set');
	}
	//客户关怀
	public function doMobileGuanhuai(){
		global $_GPC, $_W;
		$op = trim($_GPC['op']) ? trim($_GPC['op']) : 'index';
		checkauth();
		$user = fans_search($_W['fans']['from_user'],array('realname','mobile'));
		$weid = $_W['weid'];
		$car = pdo_fetch("SELECT * FROM ".tablename('we7car_care')." WHERE weid = :weid AND from_user = :from_user LIMIT 1", array(':weid' => $_W['weid'],':from_user' => $_W['fans']['from_user']));
		if(!empty($car)) {
			$car['brand_val'] = $car['brand_id'].'='.$car['brand_cn'];
			$car['series_val'] = $car['series_id'].'='.$car['series_cn'];
			$car['type_val'] = $car['type_id'].'='.$car['type_cn'];
			$car_insurance_nextDate = $car['car_insurance_lastDate'] + 86400*90;
			$car_care_nextDate = $car['car_care_nextDate'] + 86400*365;
			$insurance_days = floor(($car_insurance_nextDate - TIMESTAMP)/86400);
			$care_days = floor(($car_insurance_nextDate - TIMESTAMP)/86400);
			$car['car_startTime'] = $car['car_startTime'] ? date('Y-m-d', $car['car_startTime']) : date('Y-m-d');
			$car['car_insurance_lastDate'] = $car['car_insurance_lastDate'] ? date('Y-m-d', $car['car_insurance_lastDate']) : date('Y-m-d');
			$car['car_care_lastDate'] = $car['car_care_lastDate'] ? date('Y-m-d', $car['car_care_lastDate']) : date('Y-m-d');
		} else {
			$car['car_startTime'] = date('Y-m-d');
			$car['car_insurance_lastDate'] = date('Y-m-d');
			$car['car_care_lastDate'] = date('Y-m-d');
		}
		if($user) {
			$car['car_userName'] = $user['realname'];
			$car['car_mobile'] = $user['mobile'];
		}
		
		//获取车主关怀顶部图片
		$banner = pdo_fetchcolumn("SELECT guanhuai_thumb FROM ".tablename('we7car_set')." WHERE  weid = :weid  " , array(':weid' => $_W['weid']));
		if($op == 'caredit') {
			$brands = pdo_fetchall("SELECT id,title FROM ".tablename('we7car_brand')." WHERE `weid` = :weid AND `status` = 1 AND `status` = 1 ORDER BY `listorder` DESC",array(':weid' => $_W['weid']));
			$eseries = pdo_fetchall('SELECT id,title FROM '.tablename('we7car_series')." WHERE `weid` = :weid AND `bid` = :bid AND `status` = 1 ORDER BY listorder DESC",array(':weid' => $_W['weid'],':bid' => $car['brand_id']));
			$etypes = pdo_fetchall('SELECT id,title FROM '.tablename('we7car_type')." WHERE `weid` = :weid AND `sid` = :sid AND `status` = 1 ORDER BY listorder DESC",array(':weid' => $_W['weid'],':sid' => $car['series_id']));
				
			if(checksubmit('submit')) {
				$brand = explode('=',$_GPC['brand']);
				$series = explode('=',$_GPC['serie']);
				$types = explode('=',$_GPC['types']);
				$insert=array(
						'weid' => $_W['weid'],
						'from_user' => $_W['fans']['from_user'],
						'brand_id' => intval($brand[0]),
						'brand_cn' => trim($brand[1]),
						'series_id' => trim($series[0]),
						'series_cn' => trim($series[1]),
						'type_id' => trim($types[0]),
						'type_cn' => trim($types[1]),
						'car_note' => trim($_GPC['car_note']),
						'car_no' => trim($_GPC['car_no']),
						'car_userName' => trim($_GPC['car_userName']),
						'car_mobile' => trim($_GPC['car_mobile']),
						'car_startTime' => strtotime($_GPC['car_startTime']),
						'car_insurance_lastDate' => strtotime($_GPC['car_insurance_lastDate']),
						'car_insurance_lastCost' => trim($_GPC['car_insurance_lastCost']),
						'car_care_mileage' => trim($_GPC['car_care_mileage']),
						'car_care_lastDate' => strtotime($_GPC['car_care_lastDate']),
						'car_care_lastCost' => trim($_GPC['car_care_lastCost']),
						'car_insurance_lastDate' => strtotime($_GPC['car_insurance_lastDate']),
						'createtime' => TIMESTAMP
				);
				if (!empty($_FILES['car_photo']['tmp_name'])) {
					$upload = file_upload($_FILES['car_photo']);
					if (is_error($upload)) {
						message($upload['message']);
					}
					$insert['car_photo'] = $upload['path'];
				}
				if(empty($car['id'])) {
					$temp = pdo_insert('we7car_care',$insert);
				} else {
					$temp = pdo_update('we7car_care', $insert, array('id' => $car['id']));
				}
				if($temp == false){
					message('抱歉，更新爱车数据失败！', create_url('mobile/module', array('do' => 'Guanhuai', 'name' => 'car','op'=>'caredit','from_user' => $_W['fans']['from_user'])), 'error');
				}else{
					message('更新爱车数据成功！', create_url('mobile/module', array('do' => 'Guanhuai', 'name' => 'car','op'=>'index','weid'=>$weid,'from_user'=>$_W['fans']['from_user'])), 'success');
				}
			}	
			include $this->template('guanhuai_caredit');
		}		
		if($op == 'index') {
			include $this->template('guanhuai_index');
		}
	}
	//留言管理
	public function doWebMessage(){
		global $_GPC, $_W;
		$op = trim($_GPC['op']) ? trim($_GPC['op']) : 'list';
		if($op == 'list') {
			$isshow = isset($_GPC['isshow']) ? intval($_GPC['isshow']) : '2';
			$pindex = max(1, intval($_GPC['page']));
			$psize = 20;
			if($isshow == '2') {
				//==2表示显示全部
				$total = pdo_fetchcolumn('SELECT COUNT(*) FROM '.tablename('we7car_message_list') . " WHERE weid = :weid AND fid = 0",array(':weid' => $_W['weid']));
				$list = pdo_fetchall("SELECT * FROM ".tablename('we7car_message_list')." WHERE weid = :weid AND fid = 0 ORDER BY id DESC LIMIT ".($pindex - 1) * $psize.",{$psize}",array(':weid' => $_W['weid']));
			} else {
				$total = pdo_fetchcolumn('SELECT COUNT(*) FROM '.tablename('we7car_message_list') . " WHERE weid = :weid AND isshow = :isshow AND fid = 0",array(':weid' => $_W['weid'],':isshow' => $isshow));
				$list = pdo_fetchall("SELECT * FROM ".tablename('we7car_message_list')." WHERE weid = :weid AND isshow = :isshow AND fid = 0 ORDER BY id DESC LIMIT ".($pindex - 1) * $psize.",{$psize}",array(':weid' => $_W['weid'],':isshow' => $isshow));
			}
			$pager = pagination($total, $pindex, $psize);
			$message = pdo_fetch("SELECT id, isshow, weid FROM ".tablename('we7car_message_set')." WHERE weid = '{$weid}' LIMIT 1");
			
			if(!empty($list)) {
				foreach ($list as &$row) {
					$row['content'] = emotion($row['content']);
					$row['reply'] = pdo_fetchall("SELECT * FROM ".tablename('we7car_message_list')." WHERE weid = :weid  AND fid = :fid ORDER BY id DESC LIMIT ".($pindex - 1) * $psize.",{$psize}",array(':weid' => $_W['weid'],':fid' => $row['id']));
					unset($row);
				}
			}
			if (checksubmit('delete') && (!empty($_GPC['select']) || !empty($_GPC['select1']))) {
				if(!empty($_GPC['select'])) {
					pdo_delete('we7car_message_list', " fid  IN  ('".implode("','", $_GPC['select'])."')");
					pdo_delete('we7car_message_list', " id  IN  ('".implode("','", $_GPC['select'])."')");
				}
				if(!empty($_GPC['select1'])) {
					pdo_delete('we7car_message_list', " id  IN  ('".implode("','", $_GPC['select1'])."')");
				}
				message('删除成功！', create_url('site/module', array('do' => 'Message', 'name' => 'car', 'weid' => $weid, 'page' => $_GPC['page'],'isshow'=>$_GPC['isshow'])));
			}
			if (checksubmit('verify') && (!empty($_GPC['select']) || !empty($_GPC['select1']))) {
				$isshow = intval($_GPC['isshow']);
				if(!empty($_GPC['select'])) {
					pdo_update('we7car_message_list', array('isshow' => $isshow), " id  IN  ('".implode("','", $_GPC['select'])."')");
				}
				if(!empty($_GPC['select1'])) {
					pdo_update('we7car_message_list', array('isshow' => $isshow), " id  IN  ('".implode("','", $_GPC['select1'])."')");
				}
				message('审核成功！', create_url('site/module', array('do' => 'Message', 'name' => 'car', 'weid' => $weid, 'page' => $_GPC['page'],'isshow'=>$_GPC['isshow'])));
			}
			include $this->template('web/message_list');
		}		
		if($op == 'set') {
			if(checksubmit('submit')) {
				$id = intval($_GPC['id']);
				$title = !empty($_GPC['title']) ? trim($_GPC['title']) : message('请填写意见反馈的显示名称');
				$thumb = !empty($_GPC['thumb']) ? trim($_GPC['thumb']) : message('请上传意见反馈的头部图片');
				//保存数据
				$insert=array(
						'weid' => $_W['weid'],
						'title' => $title,
						'thumb' => $thumb,
						'status' => intval($_GPC['status']),
						'isshow' => intval($_GPC['isshow']),
						'create_time' => TIMESTAMP
				);
				if($id == 0){
					$temp = pdo_insert('we7car_message_set', $insert);
				}else{
					$temp = pdo_update('we7car_message_set', $insert,array('id' => $id));
				}
				if($temp==false){
					message('抱歉，更新设置数据失败！','', 'error');
				}else{
					message('更新设置数据成功！', create_url('site/module', array('do' => 'Message','op'=>'set', 'name' => 'car','weid' =>$_W['weid'])), 'success');
				}
				
			}	
			$theone = pdo_fetch("SELECT * FROM ".tablename('we7car_message_set')." WHERE  weid = :weid  " , array(':weid' => $_W['weid']));
			//数据为空，赋值
			if(empty($theone)){
				$theone=array(
						'status'=>1,
						'isshow'=>1,
				);
			}
			include $this->template('web/message_set');
		}
	}	
	
	public function doMobileMessage(){
		global $_GPC, $_W;
		checkauth();
		$op = trim($_GPC['op']) ? trim($_GPC['op']) : 'list';
		$set = pdo_fetch("SELECT * FROM ".tablename('we7car_message_set')." WHERE weid = :weid ORDER BY `id` DESC LIMIT 1", array(':weid' => $_W['weid']));
		if($set == false){
			$set = array(
					'status' => 1,
					'isshow' => 1,
			);
		}
		if($op == 'list') {
			if($set['status'] == 0){
				message('留言墙尚未开启,请耐心等待');
			}
			$total = pdo_fetchcolumn ("SELECT count(*) FROM ".tablename('we7car_message_list')." WHERE fid = 0 AND isshow = 1 AND weid = :weid",array(':weid' => $_W['weid']));
			$pindex = max(1, intval($_GPC['page']));
			$psize = 20;
			$pager = pagination($total, $pindex, $psize);
			$messagelist = pdo_fetchall("SELECT * FROM ".tablename('we7car_message_list')." WHERE  weid = :weid and fid=0 and isshow=1  ORDER BY create_time DESC  LIMIT " . ($pindex - 1) * $psize . ',' . $psize,array(':weid' => $_W['weid']));
			foreach($messagelist as  &$v){
				$v['reply'] = pdo_fetchall("SELECT * FROM ".tablename('we7car_message_list')." WHERE  weid = :weid AND fid = :fid AND isshow=1  LIMIT 20",array(':weid' => $_W['weid'],':fid' => $v['id']));
			}
			//获取fans表中的username
			$nickname = pdo_fetchcolumn("SELECT `nickname` FROM ".tablename('fans')." WHERE weid = :weid AND `from_user` = :from_user LIMIT 1",array(':from_user' => $_W['fans']['from_user'],':weid' => $_W['weid']));
			include $this->template('message_list');
		} 
		if($op == 'ajax') {
			if(empty($_W['fans']['from_user'])){ 
				$data['msg'] = '登陆过期，请重新从微信进入!';
				$data['success'] = false;
			}else{
				$message = pdo_fetch("SELECT * FROM ".tablename('we7car_message_list')." WHERE from_user = :from_user AND weid = :weid ORDER BY id DESC limit 1",array(':weid' => $_W['weid'],':from_user' => $_W['fans']['from_user']));
				$insert = array(
						'weid' => $_W['weid'],
						'nickname' => trim($_GPC['nickname']),
						'info' => trim($_GPC['info']),
						'fid' => intval($_GPC['fid']),
						'from_user' => $_W['fans']['from_user'],
						'isshow' => $set['isshow'],
						'create_time' => TIMESTAMP
				);
				if(empty($insert['nickname'])){
					$insert['nickname'] = pdo_fetchcolumn("SELECT nickname FROM ".tablename('fans')." WHERE weid = :weid  AND  from_user = :from_user LIMIT 1",array(':weid' => $_W['weid'],':from_user' => $_W['fans']['from_user']));
				}
			
				if(empty($message)) {
					$id = pdo_insert('we7car_message_list', $insert);
					$data['success'] = true;
					$data['msg'] = '留言发表成功';
					if($set['isshow'] == 0){$data['msg'] = $data['msg'].',进入审核流程';}
				} else {
					if((TIMESTAMP - $message['create_time']) < 5){
						$data['msg'] = '您的留言太过频繁，请5秒后留言';
						$data['success'] = false;
					}else{
						$id = pdo_insert('we7car_message_list', $insert);
						$data['success'] = true;
						$data['msg'] = '留言发表成功';
						if($set['isshow'] == 0){$data['msg'] = $data['msg'].',进入审核流程';}
					}
				}
			}
			echo json_encode($data);
		}
	}
	public function doMobileTool(){
		global $_GPC, $_W;
	 	$tools = pdo_fetchcolumn  ("SELECT tools FROM ".tablename('we7car_set')." WHERE  weid=:weid  " , array(':weid' => $_W['weid']));				
		if($tools === false){
			$toolsArr = array(
				'1' => true,
				'2' => true,
				'3' => true,
				'4' => true,
				'5' => true,
			);
		}else{
			$toolsArr = array();
			$tools = explode(",",$tools);
			foreach($tools as $v){
				$toolsArr[$v] = true;
			}
			
		}
		include $this->template('tools_index');
	}
	public function doWebNews(){
		global $_GPC, $_W;
		$op = trim($_GPC['op']) ? trim($_GPC['op']) : 'news_list';
		if($op == 'post') {
			$id = intval($_GPC['id']);
			if($id) {
				$categoryone = pdo_fetch("SELECT * FROM ".tablename('we7car_news_category')." WHERE  weid = :weid  AND id = :id limit 1",array(':weid' => $_W['weid'],':id' => $id));
			} else {
				$categoryone = array('displayorder' => 0,'status' => 1);
			}
			if(checksubmit('submit')) {
				$title = !empty($_GPC['title']) ? trim($_GPC['title']) : message('请填写分类标题');
				$description = !empty($_GPC['description']) ? trim($_GPC['description']) : message('请填写分类描述');
				$thumb = !empty($_GPC['thumb']) ? trim($_GPC['thumb']) : message('请上传分类图片');
				$displayorder = intval($_GPC['displayorder']);
				$data = array(
					'title' => $title,
					'description' => $description,
					'displayorder' => $displayorder,
					'weid' => $_W['weid'],
					'thumb' => $thumb,
					'status' => intval($_GPC['status'])
				);
				if(!$id) {
					$temp = pdo_insert('we7car_news_category',$data);
				} else {
					$temp = pdo_update('we7car_news_category',$data,array('id' => $id,'weid' => $_W['weid']));
				}
				if($temp === false) {
					message('更新分类数据失败','','error');
				} else {
					message('更新分类数据成功',$this->createWebUrl('news',array('op' => 'list')),'success');
				}
			}
			include $this->template('web/category');
		}
		if($op == 'list') {
			$categorys = pdo_fetchall("SELECT * FROM ".tablename('we7car_news_category')." WHERE  weid = :weid  ORDER BY displayorder DESC ",array(':weid' => $_W['weid']));
			if (!empty($_GPC['displayorder'])) {
				foreach ($_GPC['displayorder'] as $id => $displayorder) {
					pdo_update('we7car_news_category', array('displayorder' => $displayorder), array('id' => $id));
				}
				message('分类排序更新成功！', 'refresh', 'success');
			}
			include $this->template('web/category');
		}
		if($op == 'del') {
			$id = intval($_GPC['id']);
			$thumb = pdo_fetchcolumn("SELECT thumb FROM ".tablename('we7car_news_category')." WHERE id = :id", array(':id' => $id));
			file_delete($thumb);
			$temp = pdo_delete("we7car_news_category",array("weid" => $_W['weid'],'id' => $id));
			if($temp==false){
				message('抱歉，删除分类数据失败！','', 'error');
			}else{
				message('删除分类数据成功！', $this->createWebUrl('news', array('op'=>'list')), 'success');
			}
		}
		
		if($op == 'add_news') {
			$categorys = pdo_fetchall("SELECT * FROM ".tablename('we7car_news_category')." WHERE  weid = :weid  ORDER BY displayorder DESC ",array(':weid' => $_W['weid']));
			$template = pdo_fetchall("SELECT * FROM ".tablename('site_templates')." ORDER BY `id` ASC");
			//var_dump($template);
			$id = intval($_GPC['id']);
			$cateid = intval($_GPC['cateid']);
			if (!empty($id)) {
				$newsone = pdo_fetch("SELECT * FROM ".tablename('we7car_news')." WHERE id = :id" , array(':id' => $id));
				$newsone['type'] = explode(',', $newsone['type']);
				if (empty($newsone)) {
					message('抱歉，文章不存在或是已经删除！', '', 'error');
				}
			}
			if (checksubmit('fileupload-delete')) {
				file_delete($_GPC['fileupload-delete']);
				pdo_update('we7car_news', array('thumb' => ''), array('id' => $id));
				message('删除成功！', referer(), 'success');
			}
			if (checksubmit('submit')) {
				if (empty($_GPC['title'])) {
					message('标题不能为空，请输入标题！');
				}
				$data = array(
						'weid' => $_W['weid'],
						'iscommend' => intval($_GPC['option']['commend']),
						'ishot' => intval($_GPC['option']['hot']),
						'category_id' => intval($_GPC['category_id']),
						'title' => trim($_GPC['title']),
						'template' => trim($_GPC['template']),
						'description' => trim($_GPC['description']),
						'content' => htmlspecialchars_decode($_GPC['content']),
						'source' => $_GPC['source'],
						'author' => $_GPC['author'],
						'createtime' => TIMESTAMP,
				);
				if (!empty($_FILES['thumb']['tmp_name'])) {
					file_delete($_GPC['thumb_old']);
					$upload = file_upload($_FILES['thumb']);
					if (is_error($upload)) {
						message($upload['message'], '', 'error');
					}
					$data['thumb'] = $upload['path'];
				} elseif (!empty($_GPC['autolitpic'])) {
					$match = array();
					preg_match('/attachment\/(.*?)(\.gif|\.jpg|\.png|\.bmp)/', $_GPC['content'], $match);
					if (!empty($match[1])) {
						$data['thumb'] = $match[1].$match[2];
					}
				}
				if (empty($id)) {
					pdo_insert('we7car_news', $data);
				} else {
					unset($data['createtime']);
					pdo_update('we7car_news', $data, array('id' => $id));
				}
				message('文章更新成功！', $this->createWebUrl('news', array('op' => 'news_list')), 'success');
			}
			include $this->template('web/news');
		}
		if($op == 'news_list') {
			$categorys = pdo_fetchall("SELECT * FROM ".tablename('we7car_news_category')." WHERE  weid = :weid  ORDER BY displayorder DESC ",array(':weid' => $_W['weid']));
			$pindex = max(1, intval($_GPC['page']));
			$psize = 20;
			$condition = '';
			$params = array();
			if (!empty($_GPC['keyword'])) {
				$condition .= " AND title LIKE :keyword";
				$params[':keyword'] = "%{$_GPC['keyword']}%";
			}
			$category_id = intval($_GPC['category_id']);
			if ($category_id > 0) {
					
				$condition .= " AND category_id = '{$category_id}'";
			}
			$list = pdo_fetchall("SELECT * FROM ".tablename('we7car_news')." WHERE weid = '{$_W['weid']}' $condition ORDER BY id DESC LIMIT ".($pindex - 1) * $psize.','.$psize, $params);
			foreach($list as &$li) {
				$category = pdo_fetch('SELECT id,title FROM ' . tablename('we7car_news_category') . " WHERE id = :id AND weid = '{$_W['weid']}'",array(':id' => $li['category_id']));
				$li['category_title'] = $category['title'];
				$li['category_id'] = $category['id'];
			}
			$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('we7car_news') . " WHERE weid = '{$_W['weid']}' $condition",$params);
			$pager = pagination($total, $pindex, $psize);
			include $this->template('web/news');
		}
		if($op == 'del_news') {
			$id = intval($_GPC['id']);
			$row = pdo_fetch("SELECT id, thumb FROM ".tablename('we7car_news')." WHERE id = :id", array(':id' => $id));
			if (empty($row)) {
				message('抱歉，文章不存在或是已经被删除！');
			}
			if (!empty($row['thumb'])) {
				file_delete($row['thumb']);
			}
			pdo_delete('we7car_news', array('id' => $id));
			message('删除成功！', referer(), 'success');
		}
	}
	public function doMobileNews(){
		global $_GPC, $_W;
		$op = !empty($_GPC['op']) ? trim($_GPC['op']) : 'news_list';
		if($op == 'news_list') {
			$category_id = intval($_GPC['category_id']);
			$category = pdo_fetch('SELECT * FROM ' . tablename('we7car_news_category') . " WHERE weid = '{$_W['weid']}' AND id = :id LIMIT 1",array(':id' => $category_id));
			$pindex = max(1, intval($_GPC['page']));
			$psize = 1;
			$news = pdo_fetchall("SELECT * FROM ".tablename('we7car_news')." WHERE weid = '{$_W['weid']}' AND category_id = :category_id ORDER BY ishot DESC,createtime DESC LIMIT ".($pindex - 1) * $psize.','.$psize, array(':category_id' => $category_id));
			$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('we7car_news') . " WHERE weid = '{$_W['weid']}' AND category_id = :category_id ",array(':category_id' => $category_id));
			$pager = pagination($total, $pindex, $psize);
				
		}
		if($op == 'news_detail') {
			$id = intval($_GPC['id']);
			$new = pdo_fetch("SELECT * FROM ".tablename('we7car_news')." WHERE weid = :weid AND id = :id  LIMIT 1", array(':weid' => $_W['weid'],':id' => $id));
			$new = istripslashes($new);
			$new['thumb'] = $_W['attachurl'] . trim($new['thumb'], '/');
			//独立选择内容模板
			if(!empty($new['template'])) {
				$_W['account']['template'] = $new['template'];
			}
			//var_dump($new);
		}
		include $this->template('news');
	}
	//汽车相册
	public function doWebAlbum() {
		global $_W, $_GPC;
		$op = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
		if($op == 'getseries') {
			$bid = intval($_GPC['bid']);
			$ty = trim($_GPC['ty']);
			if($bid) {
				if($ty == 'series'){
					$datas=pdo_fetchall("SELECT id,bid,title FROM ".tablename('we7car_series')." WHERE `weid` = :weid AND `status` = 1 AND `bid` = :bid ORDER BY `listorder` DESC",array(':weid' => $_W['weid'],':bid' => $bid));
					$html="<option value='0'>请选择车系</option>";
					foreach($datas as $val) {
						$html.="<option value='{$val['id']}'>{$val['title']}</option>";
					}
				} elseif($ty == 'types') {
		
					$datas=pdo_fetchall("SELECT id,title FROM ".tablename('we7car_type')." WHERE `weid` = :weid AND `status` = 1 AND `sid` = :bid ORDER BY `listorder` DESC",array(':weid' => $_W['weid'],':bid' => $bid));
					$html="<option value='0'>请选择车型</option>";
					foreach($datas as $val) {
						$html.="<option id='type_".$val['id']."' value='{$val['id']}'>{$val['title']}</option>";
					}
				}
			}
			exit($html);
		}
		if ($op == 'create') {
			$brands = pdo_fetchall('SELECT id,title FROM '.tablename('we7car_brand')." WHERE `weid` = :weid AND status = 1 ORDER BY listorder DESC",array(':weid' => $_W['weid']));
			$series = pdo_fetchall('SELECT id,title FROM '.tablename('we7car_series')." WHERE `weid` = :weid AND status = 1 ORDER BY listorder DESC",array(':weid' => $_W['weid']));
			$types = pdo_fetchall('SELECT id,title FROM '.tablename('we7car_type')." WHERE `weid` = :weid AND status = 1 ORDER BY listorder DESC",array(':weid' => $_W['weid']));
			
			$bid = intval($_GPC['bid']);
			$sid = intval($_GPC['sid']);
			$tid = intval($_GPC['tid']);
			$id = intval($_GPC['id']);
			if (!empty($id)) {
				$item = pdo_fetch("SELECT * FROM ".tablename('we7car_album')." WHERE id = :id" , array(':id' => $id));
				if (empty($item)) {
					message('抱歉，相册不存在或是已经删除！', '', 'error');
				}
			}
			if (checksubmit('submit')) {
				if (empty($_GPC['title'])) {
					message('请输入相册名称！');
				}
				$data = array(
						'weid' => $_W['weid'],
						'type_id' => intval($_GPC['type_id']),
						'title' => $_GPC['title'],
						'content' => $_GPC['content'],
						'displayorder' => intval($_GPC['displayorder']),
						'isview' => intval($_GPC['isview']),
						'type' => intval($_GPC['type']),
						'createtime' => TIMESTAMP,
				);
				if (!empty($_FILES['thumb']['tmp_name'])) {
					file_delete($_GPC['thumb_old']);
					$upload = file_upload($_FILES['thumb']);
					if (is_error($upload)) {
						message($upload['message'], '', 'error');
					}
					$data['thumb'] = $upload['path'];
				}
				if (empty($id)) {
					pdo_insert('we7car_album', $data);
				} else {
					unset($data['createtime']);
					pdo_update('we7car_album', $data, array('id' => $id));
				}
				message('相册更新成功！', $this->createWebUrl('album', array('op' => 'display')), 'success');
			}
			include $this->template('web/album');
		} elseif ($op == 'display') {
			$pindex = max(1, intval($_GPC['page']));
			$psize = 20;
			$condition = '';
			if (!empty($_GPC['keyword'])) {
				$condition .= " AND title LIKE '%{$_GPC['keyword']}%'";
			}
	
			$list = pdo_fetchall("SELECT * FROM ".tablename('we7car_album')." WHERE weid = '{$_W['weid']}' $condition ORDER BY displayorder DESC, id DESC LIMIT ".($pindex - 1) * $psize.','.$psize);
			$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('we7car_album') . " WHERE weid = '{$_W['weid']}' $condition");
			$pager = pagination($total, $pindex, $psize);
			if (!empty($list)) {
				foreach ($list as &$row) {
					$row['total'] = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('we7car_album_photo')." WHERE albumid = :albumid", array(':albumid' => $row['id']));
				}
			}
			include $this->template('web/album');
		} elseif ($op == 'photo') {
			$id = intval($_GPC['albumid']);
			$album = pdo_fetch("SELECT id, type FROM ".tablename('we7car_album')." WHERE id = :id", array(':id' => $id));
			if (empty($album)) {
				message('相册不存在或是已经被删除！');
			}
			if (checksubmit('submit')) {
				if (!empty($_GPC['attachment-new'])) {
					foreach ($_GPC['attachment-new'] as $index => $row) {
						if (empty($row)) {
							continue;
						}
						$data = array(
								'weid' => $_W['weid'],
								'albumid' => intval($_GPC['albumid']),
								'title' => $_GPC['title-new'][$index],
								'description' => $_GPC['description-new'][$index],
								'attachment' => $_GPC['attachment-new'][$index],
								'displayorder' => $_GPC['displayorder-new'][$index],
						);
						pdo_insert('we7car_album_photo', $data);
					}
				}
				if (!empty($_GPC['attachment'])) {
					foreach ($_GPC['attachment'] as $index => $row) {
						if (empty($row)) {
							continue;
						}
						$data = array(
								'weid' => $_W['weid'],
								'albumid' => intval($_GPC['albumid']),
								'title' => $_GPC['title'][$index],
								'description' => $_GPC['description'][$index],
								'attachment' => $_GPC['attachment'][$index],
								'displayorder' => $_GPC['displayorder'][$index],
						);
						pdo_update('we7car_album_photo', $data, array('id' => $index));
					}
				}
				message('相册更新成功！', $this->createWebUrl('album', array('op' => 'photo', 'albumid' => $album['id'])));
			}
			if($album['type'] == 0) {
				$photos = pdo_fetchall("SELECT * FROM ".tablename('we7car_album_photo')." WHERE albumid = :albumid ORDER BY displayorder DESC", array(':albumid' => $album['id']));
			} else {
				$photos = pdo_fetchall("SELECT * FROM ".tablename('we7car_album_photo')." WHERE albumid = :albumid ORDER BY displayorder ASC", array(':albumid' => $album['id']));
			}
			include $this->template('web/album');
		} elseif ($op == 'delete') {
			$type = $_GPC['type'];
			$id = intval($_GPC['id']);
			if ($type == 'photo') {
				if (!empty($id)) {
					$item = pdo_fetch("SELECT * FROM ".tablename('we7car_album_photo')." WHERE id = :id", array(':id' => $id));
					if (empty($item)) {
						message('图片不存在或是已经被删除！');
					}
					pdo_delete('we7car_album_photo', array('id' => $item['id']));
				} else {
					$item['attachment'] = $_GPC['attachment'];
				}
				file_delete($item['attachment']);
			} elseif ($type == 'album') {
				$album = pdo_fetch("SELECT id, thumb FROM ".tablename('we7car_album')." WHERE id = :id", array(':id' => $id));
				if (empty($album)) {
					message('相册不存在或是已经被删除！');
				}
				$photos = pdo_fetchall("SELECT id, attachment FROM ".tablename('we7car_album_photo')." WHERE albumid = :albumid", array(':albumid' => $id));
				if (!empty($photos)) {
					foreach ($photos as $row) {
						file_delete($row['attachment']);
					}
				}
				file_delete($album['thumb']);
				pdo_delete('we7car_album', array('id' => $id));
				pdo_delete('we7car_album_photo', array('albumid' => $id));
			}
			message('删除成功！', referer(), 'success');
		} elseif ($op == 'cover') {
			$id = intval($_GPC['albumid']);
			$attachment = $_GPC['thumb'];
			if (empty($attachment)) {
				message('抱歉，参数错误，请重试！', '', 'error');
			}
			$item = pdo_fetch("SELECT * FROM ".tablename('we7car_album')." WHERE id = :id" , array(':id' => $id));
			if (empty($item)) {
				message('抱歉，相册不存在或是已经删除！', '', 'error');
			}
			pdo_update('we7car_album', array('thumb' => $attachment), array('id' => $id));
			message('设置封面成功！', '', 'success');
		}
	}
	public function doMobilealbum() {
		global $_W, $_GPC;
		$_W['styles'] = mobile_styles();
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;
		$result['list'] = pdo_fetchall("SELECT * FROM ".tablename('we7car_album')." WHERE weid = '{$_W['weid']}' AND isview = '1' ORDER BY displayorder DESC LIMIT " . ($pindex - 1) * $psize .',' .$psize);
		$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('we7car_album') . " WHERE isview = '1'");
		$result['pager'] = pagination($total, $pindex, $psize);
		include $this->template('album_list');
	}
	
	public function doMobileDetail() {
		global $_W, $_GPC;
		$id = intval($_GPC['id']);
		$album = pdo_fetch("SELECT * FROM ".tablename('we7car_album')." WHERE id = :id", array(':id' => $id));
		if (empty($album)) {
			message('相册不存在或是已经被删除！');
		}
		$result['list'] = pdo_fetchall("SELECT * FROM ".tablename('we7car_album_photo')." WHERE albumid = :albumid ORDER BY displayorder DESC", array(':albumid' => $album['id']));
		$url = $this->createMobileUrl('detail', array('id' => $id));
		//360全景
		if($album['type'] == 1 && $_GPC['gettype'] == 'xml') {
			$result['list'] = pdo_fetchall("SELECT * FROM ".tablename('we7car_album_photo')." WHERE albumid = :albumid ORDER BY displayorder ASC", array(':albumid' => $album['id']));
			header("Content-type: text/xml");
			$xml =  '<?xml version="1.0" encoding="UTF-8"?>
			<panorama id="" hideabout="1">
				<view fovmode="0" pannorth="0">
					<start pan="5.5" fov="80" tilt="1.5"/>
					<min pan="0" fov="80" tilt="-90"/>
					<max pan="360" fov="80" tilt="90"/>
				</view>
				<userdata title="" datetime="2013:05:23 21:01:02" description="" copyright="" tags="" author="" source="" comment="" info="" longitude="" latitude=""/>
				<hotspots width="180" height="20" wordwrap="1">
					<label width="180" backgroundalpha="1" enabled="1" height="20" backgroundcolor="0xffffff" bordercolor="0x000000" border="1" textcolor="0x000000" background="1" borderalpha="1" borderradius="1" wordwrap="1" textalpha="1"/>
					<polystyle mode="0" backgroundalpha="0.2509803921568627" backgroundcolor="0x0000ff" bordercolor="0x0000ff" borderalpha="1"/>
				</hotspots>
				<media/>
				<input tilesize="700" tilescale="1.014285714285714" tile0url="'.$_W['attachurl'] . $result['list']['0']['attachment'].'" tile1url="'.$_W['attachurl'] . $result['list']['1']['attachment'].'" tile2url="'.$_W['attachurl'] . $result['list']['2']['attachment'].'" tile3url="'.$_W['attachurl'] . $result['list']['3']['attachment'].'" tile4url="'.$_W['attachurl'] . $result['list']['4']['attachment'].'" tile5url="'.$_W['attachurl'] . $result['list']['5']['attachment'].'"/>
				<autorotate speed="0.200" nodedelay="0.00" startloaded="1" returntohorizon="0.000" delay="5.00"/>
				<control simulatemass="1" lockedmouse="0" lockedkeyboard="0" dblclickfullscreen="0" invertwheel="0" lockedwheel="0" invertcontrol="1" speedwheel="1" sensitivity="8"/>
			</panorama>';
			return $xml;
		}
		include $this->template('album_detail');
	}
	
}