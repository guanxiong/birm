<?php
/**
 * 二手房
 *
 * @author 
 * @url
 */

defined('IN_IA') or exit('Access Denied');
include_once IA_ROOT . '/source/modules/shouse/model.php';
class ShouseModuleSite extends WeModuleSite {

	//后台
	public function doWebTurlar(){
		global $_GPC,$_W;
		$operation = !empty($_GPC['operation']) ? $_GPC['operation'] : 'post';
		if ($operation == 'post') {
			
		}
		include $this->template('turlar');
	}

	public function doWebCategory(){
		global $_GPC,$_W;
		$foo = !empty($_GPC['foo']) ? $_GPC['foo'] : 'post';
		if ($foo == 'post') {
			$parentid = intval($_GPC['parentid']);
			$id = intval($_GPC['id']);
			if ($id) {
				$category = pdo_fetch("SELECT * FROM".tablename('hewer_category')."WHERE id = :id", array(':id' => $id));
			}
			if (!empty($parentid)) {
				$parent = pdo_fetch("SELECT id,name FROM ".tablename('hewer_category')." WHERE id = '{$parentid}'");
				if (empty($parent)) {
					message('抱歉，上级分类不存在或是已经被删除！', $this->createWebUrl('category', array('foo' => 'display')), 'error');
				}
			}
			$data = array(
					'weid'         => $_W['weid'],
					'parentid'     => intval($parentid),
					'displayorder' => $_GPC['displayorder'],
					'name'         => $_GPC['cname'],
					'description'  => $_GPC['description'],
					'linkurl'      => $_GPC['linkurl'],
				);
			if ($_W['ispost']) {
				if (empty($id)) {
					pdo_insert('hewer_category',$data);
					$id = pdo_insertid();
					message('添加成功',referer(),'success');
				}else{
					unset($data['parentid']);
					pdo_update('hewer_category',$data,array('id' => $id));
					message('更新成功',referer(),'success');

				}
			}
		}elseif ($foo == 'display') {
			if (!empty($_GPC['displayorder'])) {
				foreach ($_GPC['displayorder'] as $id => $displayorder) {
					pdo_update('hewer_category', array('displayorder' => $displayorder), array('id' => $id));
				}
				message('分类排序更新成功！', 'refresh', 'success');
			}
			$children = array();
			$category = pdo_fetchall("SELECT * FROM ".tablename('hewer_category')." WHERE weid = '{$_W['weid']}' ORDER BY parentid ASC, displayorder ASC, id ASC ");
			foreach ($category as $index => $row) {
				if (!empty($row['parentid'])){
					$children[$row['parentid']][] = $row;
					unset($category[$index]);
				}
			}
		}elseif ($foo == 'delete') {
			$id = intval($_GPC['id']);
			$category = pdo_fetch("SELECT id, parentid, nid FROM ".tablename('hewer_category')." WHERE id = '$id'");
			if (empty($category)) {
				message('抱歉，分类不存在或是已经被删除！', $this->createWebUrl('category'), 'error');
			}
			// $navs = pdo_fetchall("SELECT icon, id FROM ".tablename('hewer_category')." WHERE id IN (SELECT nid FROM ".tablename('article_category')." WHERE id = {$id} OR parentid = '$id')", array(), 'id');
			// if (!empty($navs)) {
			// 	foreach ($navs as $row) {
			// 		file_delete($row['icon']);
			// 	}
			// 	pdo_query("DELETE FROM ".tablename('hewer_category')." WHERE id IN (".implode(',', array_keys($navs)).")");
			// }
			pdo_delete('hewer_category', array('id' => $id, 'parentid' => $id), 'OR');
			message('分类删除成功！', $this->createWebUrl('category'), 'success');
		} 

		include $this->template('category');
	}

	public function doWebHouse(){
		global $_GPC,$_W;
		$op = !empty($_GPC['op']) ? $_GPC['op'] : 'list';
		$list = pdo_fetchall("SELECT * FROM".tablename('hewer_house')."WHERE weid='{$_W['weid']}'");
		foreach ($list as $key => $value) {
			$gulluk = pdo_fetch("SELECT * FROM".tablename('hewer_gulluk')."WHERE id='{$value['gid']}'");
			$list[$key]['region_name'] = $gulluk['title'];
		}
		if ($op == 'isgood') {
			$hsid = intval($_GPC['hsid']);
			$isgood = intval($_GPC['isgood']);
			$hgood = pdo_fetch("SELECT * FROM".tablename('hewer_house_isgood')."WHERE hsid='{$hsid}'");
			if (!$hgood) {
				$data = array(
						'weid' => $_W['weid'],
						'hsid' => $hsid,
						'isgood' => $isgood,
						'createtime' => TIMESTAMP,
					);
				if($_W['isajax']){
					pdo_insert("hewer_house_isgood",$data);
				}
			}else{
				pdo_query("UPDATE ".tablename('hewer_house_isgood')." SET isgood = '{$isgood}' WHERE hsid='{$hsid}'");
			}


		}
		include $this->template('house');
	}

	public function doWebAdminlist(){
		global $_GPC,$_W;
		$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'post';
		if ($operation == 'post') {
			$dukanlar = pdo_fetchAll("SELECT * FROM".tablename('hewer_dukanlar')."WHERE weid='{$_W['weid']}'");
		}
		include $this->template('hizmatqi');
	}

	public function doWebDukan(){
		global $_GPC,$_W;
		$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'post';
		if ($operation == 'post') {
			if ($_GPC['id']) {
				 $item = pdo_fetch("SELECT * FROM".tablename('hewer_dukanlar')."WHERE id = :id", array(':id' => $_GPC['id']));
			}
			$data = array( 
					'weid'       => $_W['weid'],
					'title'      => $_GPC['title'],
					'content'    => $_GPC['content'],
					'phone'      => $_GPC['phone'],
					'tel'        => $_GPC['tel'],
					'url'        => $_GPC['url'],
					'qq'         => $_GPC['qq'],
					'province'   => $_GPC['province'],
					'city'       => $_GPC['city'],
					'dist'       => $_GPC['dist'],
					'address'    => $_GPC['address'],
					'lng'        => $_GPC['lng'],
					'lat'        => $_GPC['lat'],
					'createtime' => $_GPC['createtime'],
				);
			if (!empty($_FILES['thumb']['tmp_name'])) {
				file_delete($_GPC['thumb-old']);
				$upload = file_upload($_FILES['thumb']);
				if (is_error($upload)) {
					message($upload['message'], '', 'error');
				}
				$data['thumb'] = $upload['path'];
			}
			if ($_W['ispost']) {
				if (empty($_GPC['id'])) {
					pdo_insert('hewer_dukanlar',$data);
					message('添加成功',referer(),'success');
				}else{
					pdo_update('hewer_dukanlar',$data,array('id' => $_GPC['id']));
					message('更新成功',referer(),'success');
				}
			}
		}elseif ($operation =='display') {
			$pindex = max(1, intval($_GPC['page']));
			$psize = 20;
			$sql   = "SELECT * FROM".tablename('hewer_dukanlar')."WHERE weid='{$_W['weid']}' ORDER BY id DESC LIMIT ".($pindex - 1) * $psize.','.$psize;
			$list  = pdo_fetchAll($sql);
			$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('hewer_dukanlar') . " WHERE weid = '{$_W['weid']}' ");
			$pager = pagination($total, $pindex, $psize);
		}elseif ($operation == 'delete') {
			pdo_delete('hewer_dukanlar',array('id' => $_GPC['id']));
			message('删除成功',referer(),'success');
		}
		include $this->template('dukanlar');
	}

	public function doWebGulluk(){
		global $_GPC,$_W;
		$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'post';
		if ($operation == 'post') {
			if ($_GPC['id']) {
				$item = pdo_fetch("SELECT * FROM".tablename('hewer_gulluk')."WHERE id = :id", array(':id' => $_GPC['id']));

			}
			$data = array(
					'weid' => $_W['weid'],
					'title' => $_GPC['title'],
					'thumb' => $_GPC['thumb'],
					'dizhi' => $_GPC['dizhi'],
					'huanjing' => $_GPC['huanjing'],
					'zhoubian' => $_GPC['zhoubian'],
					'jiaotong' => $_GPC['jiaotong'],
					'lng' => $_GPC['lng'],
					'lat' => $_GPC['lat'],

				);
			if ($_W['ispost']) {
				if (empty($_GPC['id'])) {
					pdo_insert('hewer_gulluk',$data);
					message('添加成功',referer(),'success');
				}else{
					pdo_update('hewer_gulluk',$data,array('id' => $_GPC['id']));
					message('更新成功',referer(),'success');

				}
			}
		}elseif($operation == 'display'){
			$category = pdo_fetchAll("SELECT * FROM".tablename('hewer_gulluk')."WHERE weid='{$_W['weid']}'");
		}
		include $this->template('gulluk');
	}
	public function doWebArticle(){
		global $_GPC,$_W;
		$foo = !empty($_GPC['foo']) ? $_GPC['foo'] : 'post';
		$category = pdo_fetchall("SELECT * FROM ".tablename('hewer_category')." WHERE weid = '{$_W['weid']}' ORDER BY parentid ASC, displayorder ASC, id ASC ", array(), 'id');
		if (!empty($category)) {
			$children = '';
			foreach ($category as $cid => $cate) {
				if (!empty($cate['parentid'])) {
					$children[$cate['parentid']][] = array($cate['id'], $cate['name']);
				}
			}
		}
		if ($foo == 'display') {
			$pindex = max(1, intval($_GPC['page']));
			$psize = 20;
			$condition = '';
			$params = array();
			if (!empty($_GPC['keyword'])) {
				$condition .= " AND title LIKE :keyword";
				$params[':keyword'] = "%{$_GPC['keyword']}%";
			}

			if (!empty($_GPC['cate_2'])) {
				$cid = intval($_GPC['cate_2']);
				$condition .= " AND ccate = '{$cid}'";
			} elseif (!empty($_GPC['cate_1'])) {
				$cid = intval($_GPC['cate_1']);
				$condition .= " AND pcate = '{$cid}'";
			}

			$list = pdo_fetchall("SELECT * FROM ".tablename('hewer')." WHERE weid = '{$_W['weid']}' $condition ORDER BY displayorder DESC, id DESC LIMIT ".($pindex - 1) * $psize.','.$psize, $params);
			$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('hewer') . " WHERE weid = '{$_W['weid']}'");
			$pager = pagination($total, $pindex, $psize);

		} elseif ($foo == 'post') {
			$id = intval($_GPC['id']);

			if (!empty($id)) {
				$item = pdo_fetch("SELECT * FROM ".tablename('hewer')." WHERE id = :id" , array(':id' => $id));
				$item['type'] = explode(',', $item['type']);
				if (empty($item)) {
					message('抱歉，文章不存在或是已经删除！', '', 'error');
				}
			}
			if (checksubmit('fileupload-delete')) {
				file_delete($_GPC['fileupload-delete']);
				pdo_update('article', array('thumb' => ''), array('id' => $id));
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
					'pcate' => intval($_GPC['cate_1']),
					'ccate' => intval($_GPC['cate_2']),
					'template' => $_GPC['template'],
					'title' => $_GPC['title'],
					'description' => $_GPC['description'],
					'content' => htmlspecialchars_decode($_GPC['content']),
					'source' => $_GPC['source'],
					'author' => $_GPC['author'],
					'displayorder' => intval($_GPC['displayorder']),
					'linkurl' => $_GPC['linkurl'],
					'createtime' => TIMESTAMP,
				);
				if (!empty($_GPC['thumb'])) {
					$data['thumb'] = $_GPC['thumb'];
					file_delete($_GPC['thumb-old']);
				} elseif (!empty($_GPC['autolitpic'])) {
					$match = array();
					preg_match('/attachment\/(.*?)(\.gif|\.jpg|\.png|\.bmp)/', $_GPC['content'], $match);
					if (!empty($match[1])) {
						$data['thumb'] = $match[1].$match[2];
					}
				}
				if (empty($id)) {
					pdo_insert('hewer', $data);
				} else {
					unset($data['createtime']);
					pdo_update('hewer', $data, array('id' => $id));
				}
				message('文章更新成功！', $this->createWebUrl('article', array('foo' => 'display')), 'success');
			} 
		} elseif ($foo == 'delete') {
			$id = intval($_GPC['id']);
			$row = pdo_fetch("SELECT id, thumb FROM ".tablename('hewer')." WHERE id = :id", array(':id' => $id));
			if (empty($row)) {
				message('抱歉，文章不存在或是已经被删除！');
			}
			if (!empty($row['thumb'])) {
				file_delete($row['thumb']);
			}
			pdo_delete('hewer', array('id' => $id));
			message('删除成功！', referer(), 'success');
		}
		include $this->template('article');
	}

	public function doWebSlide(){
		global $_GPC,$_W;
		$op = !empty($_GPC['op']) ? $_GPC['op'] : 'post';
		if ($op == 'display') {
			$pindex = max(1, intval($_GPC['page']));
			$psize = 20;
			$condition = '';
			$params = array();
			if (!empty($_GPC['keyword'])) {
				$condition .= " AND title LIKE :keyword";
				$params[':keyword'] = "%{$_GPC['keyword']}%";
			}

		$list = pdo_fetchall("SELECT * FROM ".tablename('hewer_slide')." WHERE weid = '{$_W['weid']}' $condition ORDER BY displayorder DESC, id DESC LIMIT ".($pindex - 1) * $psize.','.$psize, $params);
		$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('hewer_slide') . " WHERE weid = '{$_W['weid']}' $condition");
		$pager = pagination($total, $pindex, $psize);

		} elseif ($op == 'post') {
			$id = intval($_GPC['id']);
			if (!empty($id)) {
				$item = pdo_fetch("SELECT * FROM ".tablename('hewer_slide')." WHERE id = :id" , array(':id' => $id));
				if (empty($item)) {
					message('抱歉，幻灯片不存在或是已经删除！', '', 'error');
				}
			}
			if (checksubmit('submit')) {
				if (empty($_GPC['title'])) {
					message('标题不能为空，请输入标题！');
				}
				$data = array(
					'weid' => $_W['weid'],
					'title' => $_GPC['title'],
					'url' => $_GPC['url'],
					'displayorder' => intval($_GPC['displayorder']),
				);
				if (!empty($_GPC['thumb'])) {
					$data['thumb'] = $_GPC['thumb'];
					file_delete($_GPC['thumb-old']);
				}
				if (empty($id)) {
					pdo_insert('hewer_slide', $data);
				} else {
					pdo_update('hewer_slide', $data, array('id' => $id));
				}
				message('幻灯片更新成功！', $this->createWebUrl('slide',array('op' => 'display')), 'success');
			}
		} elseif ($op == 'delete') {
			$id = intval($_GPC['id']);
			$row = pdo_fetch("SELECT id, thumb FROM ".tablename('hewer_slide')." WHERE id = :id", array(':id' => $id));
			if (empty($row)) {
				message('抱歉，幻灯片不存在或是已经被删除！');
			}
			if (!empty($row['thumb'])) {
				file_delete($row['thumb']);
			}
			pdo_delete('hewer_slide', array('id' => $id));
			message('删除成功！', referer(), 'success');
		}
		include $this->template('slide');
	}

	public function doWebLookhouse(){
		global $_GPC,$_W;
		$op = !empty($_GPC['op']) ? $_GPC['op'] : 'display';

		include $this->template('lookhouse');
	}

	//微信端
	public function doMobileIndex(){
		global $_GPC,$_W;
		$user = pdo_fetch("SELECT * FROM".tablename('hewer_hizmatqi')."WHERE openid='{$_W['fans']['from_user']}'");

		if (!$user) {
			message('您还未注册,请先注册!',$this->createMobileUrl('reg'),'error');
		}
		//幻灯
		$slide = pdo_fetchAll("SELECT * FROM".tablename('hewer_slide')."WHERE weid='{$_W['weid']}'");
		//推荐
		$row = pdo_fetchall("SELECT * FROM".tablename('hewer_house_isgood')."WHERE isgood='1'");
		$hot = array();
		foreach ($row as $key => $value) {
			$house = pdo_fetch("SELECT * FROM".tablename('hewer_house')."WHERE hsid='{$value['hsid']}'");
			$region = pdo_fetch("SELECT * FROM".tablename('hewer_gulluk')."WHERE id='{$value['gid']}'");

			$hot[] = array(
					'hsid' => $house['hsid'],
					'cate_id' => $house['cate_id'],
					'title' => $house['title'],
					'jiage' => $house['jiage'],
					'housenow' => $house['housenow'],
					'houseall' => $house['houseall'],
					'hrs_lan'  => $house['hrs_lan'],
					'mianji'   => $house['mianji'],
					'direction' => $house['direction'],
					'house_zx'  => $house['house_zx'],
					'picstyle'  => $house['picstyle'],
					'wupin'     => $house['wupin'],
					'linkman'   => $house['linkman'],
					'yil' => $house['yil'],
					'tel' => $house['tel'],
					'thumb' => $house['thumb'],
					'jinsi' => $house['jinsi'],
					'region_name' => $region['title'],

				);
		}
		//购房咨询
		$list = pdo_fetchall("SELECT * FROM".tablename('hewer')."WHERE weid='{$_W['weid']}' ");

		include $this->template('index');
	}
	public function doMobileHouse(){
		global $_W,$_GPC;
		$cate_id = intval($_GPC['cate']);
		$condition = '';
		if ($cate_id) {
			$condition = "AND cate_id='{$cate_id}'";
		}
		$list = pdo_fetchAll("SELECT * FROM".tablename('hewer_house')."WHERE weid='{$_W['weid']}' $condition");
		foreach ($list as $key => $value) {
			$region = pdo_fetch("SELECT * FROM".tablename('hewer_gulluk')."WHERE id='{$value['gid']}'");
			$list[$key]['region_name'] = $region['title'];
		}
		$count = pdo_fetchcolumn("SELECT COUNT(*) FROM".tablename('hewer_house')."WHERE weid='{$_W['weid']}' $condition");
		//print_r($list);exit;
		include $this->template('house');
	}
	public function doMobileHouseAdd(){
		global $_GPC,$_W;
		$id = intval($_GPC['id']);
		$region = pdo_fetchAll("SELECT * FROM".tablename('hewer_gulluk')."WHERE weid='{$_W['weid']}'");
		if ($_W['ispost']) {
			$data = array(
					'weid'      => $_W['weid'],
					'cate_id'   => $_GPC['cate_id'],
					'title'     => $_GPC['title'],
					'gid'       => $_GPC['gid'],
					'housenow'  => $_GPC['housenow'],
					'houseall'  => $_GPC['houseall'],
					'yil'       => $_GPC['yil'],
					'hrs_lan'   => $_GPC['hrs_lan'],
					'mianji'    => $_GPC['mianji'],
					'jiage'     => $_GPC['jiage'],
					'direction' => $_GPC['direction'],
					'house_zx'  => $_GPC['house_zx'],
					'picstyle'  => $_GPC['picstyle'],
					'content'   => $_GPC['content'],
					'linkman'   => $_GPC['linkman'],
					'jinsi'     => $_GPC['jinsi'],
					'tel'       => $_GPC['tel'],
					'wupin'     => implode(',', $_GPC['wupin']),
					'openid'    => $_W['fans']['from_user'],
				);
			if (!empty($_FILES['thumb']['tmp_name'])) {
				file_delete($_GPC['thumb-old']);
				$upload = file_upload($_FILES['thumb']);
				if (is_error($upload)) {
					message($upload['message'], '', 'error');
				}
				$data['thumb'] = $upload['path'];
			}
			if (empty($id)) {
				pdo_insert('hewer_house',$data);
				message('发布成功',$this->createMobileUrl('house'),'success');
			}else{
				pdo_update('hewer_house',$data,array('id' => $id ));
				message('更新成功',$this->createMobileUrl('house'),'success');
			}
		}
		include $this->template('addhouse');
	}
	public function doMobileHdetail(){
		global $_W,$_GPC;
		$hsid = intval($_GPC['hsid']);
		if (!$hsid) {
			message('该信息不存在');exit();
		}
		$detail = pdo_fetch("SELECT * FROM".tablename('hewer_house')."WHERE hsid='{$hsid}'");
		$region = pdo_fetch("SELECT * FROM".tablename('hewer_gulluk')."WHERE id='{$detail['gid']}'");

		include $this->template('hdetail');
	}

	public function doMobilenews(){
		global $_W,$_GPC;
		$pcate = intval($_GPC['pcate']);
		$categorylist = pdo_fetchall("SELECT * FROM".tablename('hewer_category')."WHERE weid='{$_W['weid']}'");
		$condition = '';
		if ($pcate) {
			$condition = "AND pcate='{$pcate}'";
		}
		$list = pdo_fetchall("SELECT * FROM".tablename('hewer')."WHERE weid='{$_W['weid']}' $condition");

		include $this->template('news');
	}

	public function doMobileDetail(){
		global $_W,$_GPC;
		$id = intval($_GPC['id']);
		if (!$id) {
			message('不存在该条文章');
		}
		$detail = pdo_fetch("SELECT * FROM".tablename('hewer')."WHERE id='{$id}'");
		include $this->template('detail');
	}
	public function doMobileMember(){
		global $_GPC,$_W;
		$foo = $_GPC['foo'];
		$user = pdo_fetch("SELECT * FROM".tablename('hewer_hizmatqi')."WHERE openid='{$_W['fans']['from_user']}'");
		
		if ($foo == 'about') {
			include $this->template('about');
		}else{
			include $this->template('member');
		}
	}
	public function doMobileReg(){
		global $_W,$_GPC;
		$data = array(
			'openid' => $_W['fans']['from_user'],
			'realname'  => $_GPC['realname'],
			'nickname'  => $_GPC['nickname'],
			'mobile'    => $_GPC['mobile'],
			);
		if ($_W['ispost']) {
			if ($_GPC['id']) {
				pdo_update('hewer_hizmatqi',$data,array('id' => $_GPC['id']));
			}else{
				pdo_insert('hewer_hizmatqi',$data);
				message('注册成功',$this->createMobileUrl('index'),'success');
			}
		}
		if ($_GPC['foo'] == 'my') {
			$member = pdo_fetch("SELECT * FROM".tablename('hewer_hizmatqi')."WHERE openid='{$_W['fans']['from_user']}'");
		}
		include $this->template('reg');
	}















}