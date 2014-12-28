<?php
/**
 * 微小区模块微站定义
 *
 * @author WeEngine Team
 * @url http://we7.cc/
 */
defined('IN_IA') or exit('Access Denied');

/**
 * 微小区广告投放映射表
 * */
define('community_admap', 'community_admap');
/**
 * 微小区广告表
 * */
define('community_advertisement', 'community_advertisement');
/**
 * 微小区公告表
 * */
define('community_announcement', 'community_announcement');
/**
 * 微小区管理员表
 * */
define('community_manager', 'community_manager');
/**
 * 微小区用户表
 * */
define('community_member', 'community_member');
/**
 * 微小区便民电话表
 * */
define('community_phone', 'community_phone');
/**
 * 微小区小区表
 * */
define('community_region', 'community_region');
/**
 * 微小区信息回复表
 * */
define('community_reply', 'community_reply');
/**
 * 微小区投诉建议表
 * */
define('community_report', 'community_report');
/**
 * 微小区家政服务表
 * */
define('community_service', 'community_service');
/**
 * 微小区家政服务类型表
 * */
define('community_servicecategory', 'community_servicecategory');
/**
 * 微小区验证码表
 * */
define('community_verifycode', 'community_verifycode');
/**
 * 微小区快递公司表
 * */
define('community_express_company', 'community_express_company');
/**
 * 微小区快递公司费用报价表
 * */
define('community_express_fee', 'community_express_fee');
/**
 * 微小区快递单信息表
 * */
define('community_express_order', 'community_express_order');

/**
 * 菜单权限
 * */
define('modules_menus', 'modules_menus');

class CommunityModuleSite extends WeModuleSite {
	
	public function getMenuTiles() {
		global $_W, $_GPC;
		$menus = array();
		$list = pdo_fetchall("SELECT * FROM ".tablename(community_region)." WHERE weid=:weid ", array(':weid' => $_W['weid']));
		if (!empty($list)) {
			foreach ($list as $row) {
				$menus[] = array('title' => $row['title'], 'url' => $this->createWebUrl('region', array('op' => 'manage', 'regionid' => $row['id'])));
			}
		}
		return $menus;
	}
	
	private function getMobileTitle($mobilemethod){
		$titles = array(
			'home'=>'个人中心',
			'homemaking' => '家政服务',
			'houselease' => '房屋租赁',
			'announcement' => '公告信息',
			'repair' => '报修服务',
			'report' => '投诉建议',
			'phone' => '便民热线',
			'member' => '我的信息',
			'register' => '用户注册',
			'help' => '使用帮助',
			'express' => '快递服务'
		);
		return $titles[strtolower($mobilemethod)];
	}
	
	/**
	 * 获取需求服务状态的文字描述
	 * */
	private function getServiceStatusTitle($id){
		$statuses = array(0=>'未解决',1=>'已解决',2=>'已取销');
		$status = $statuses[$id];
		if(empty($status)) {
			$status = '未解决';
		}
		return $status;
	}
	
	/**
	 * 服务分类名获取分类
	 * */
	private function getServiceCategory($name){
		global $_W;
		$category = pdo_fetch("SELECT * FROM ".tablename(community_servicecategory)." WHERE weid=:weid AND name=:name ",array(':weid'=>$_W['weid'],':name'=>$name));
		return $category;
	}
	
	/**
	 * 获取服务分类子分类
	 * */
	private function getServiceCategories($parentid) {
		global $_W;
		$categories = pdo_fetchall("SELECT * FROM ".tablename(community_servicecategory)." WHERE weid=:weid AND parentid=:parentid ORDER BY displayorder ASC", array(':weid'=>$_W['weid'],':parentid'=>$parentid), 'id');
		return $categories;
	}
	
	/**
	 * 家政服务
	 * */
	public function doWebHomemaking(){
		global $_W, $_GPC;
		
		$regionid = $this->checkWebAuth();
		
		$operation = empty($_GPC['op']) ? 'display' : $_GPC['op'];
		$operation = in_array($operation, array('display', 'post', 'delete')) ? $operation : 'display';
		
		$pcate = $this->getServiceCategory('家政服务');
		if (empty($pcate)){
			message("请添加[家政服务].",$this->createWebUrl('serviceCategory'),'error');
		}
		$categories = $this->getServiceCategories($pcate['id']);
		if (empty($categories)){
			message("请添加[家政服务]的下级分类.",$this->createWebUrl('serviceCategory'),'error');
		}
		if($operation == 'display') {
			
			$starttime = empty($_GPC['starttime']) ? strtotime('-1 month') : strtotime($_GPC['starttime']);
			$endtime = empty($_GPC['endtime']) ? TIMESTAMP : strtotime($_GPC['endtime']) + 86399;
			
			$pindex = max(1, intval($_GPC['page']));
			$psize = 50;

			$where = " WHERE weid=:weid AND regionid=:regionid AND createtime>=:starttime AND createtime<:endtime AND servicecategory=:servicecategory";
			$paras = array(
				':weid' => $_W['weid'],
				':regionid' => $regionid,
				':starttime' => $starttime,
				':endtime' => $endtime,
				':servicecategory' => $pcate['id']
			);
			if (!empty($_GPC['servicesmallcategory'])) {
				$where .= ' AND `servicesmallcategory`=:servicesmallcategory';
				$paras[':servicesmallcategory'] = $_GPC['servicesmallcategory']; 
			}
			
			$status = empty($_GPC['status']) ? array(0, 1, 2) : $_GPC['status'] ;
			if (count($status) > 0 && count($status)<3) {
				$where .= " AND `status` in (".implode(',', $status).")";
			}
			
			$list = pdo_fetchall("SELECT * FROM ".tablename(community_service)." $where ORDER BY status ASC, id DESC LIMIT ".($pindex - 1) * $psize.','.$psize, $paras);
			$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename(community_service). $where , $paras);
			$pager = pagination($total, $pindex, $psize);
			
			if (!empty($list)) {
				$members = array();
				foreach ($list as $row) {
					$members[$row['openid']] = $row['openid'];
				}
				$members = pdo_fetchall("SELECT realname, mobile, openid FROM ".tablename('community_member')." WHERE openid IN ('".implode("','", $members)."') AND weid = '{$_W['weid']}'", array(), 'openid');
			}
			
		} elseif($operation == 'delete') {
			
			pdo_delete(community_service, array('id'=>intval($_GPC['id']),'regionid'=>$regionid,'weid'=>$_W['weid']));
			message('删除成功.', referer(), 'success');
			
		} elseif ($operation == 'post') {
			
			$id = intval($_GPC['id']);
			$item = pdo_fetch("SELECT * FROM ".tablename(community_service)." WHERE id=:id AND regionid=:regionid AND weid=:weid", array(':id'=>$id,':weid'=>$_W['weid'],'regionid'=>$regionid));
			if (empty($item)) {
				message('抱歉，查看的服务需求不存在或是已经被删除！');
			}
			$member = pdo_fetch("SELECT * FROM ".tablename(community_member)." WHERE weid=:weid AND openid=:openid", array(':weid'=>$_W['weid'], ':openid'=>$item['openid']));
			if (checksubmit('submit')) {
				$data = array(
					'servicesmallcategory' => $_GPC['servicesmallcategory'],
					'requirement' => $_GPC['requirement'],
					'remark' => $_GPC['remark'],
					'contacttype' => $_GPC['contacttype'],
					'contactdesc' => $_GPC['contactdesc'][intval($_GPC['contacttype'])],
					'status' => intval($_GPC['status']),
				);
				pdo_update('community_service', $data, array('id' => $id,'weid'=>$_W['weid'],'regionid'=>$regionid));
				message('更新成功！', $this->createWebUrl('homemaking', array('regionid' => $regionid)), 'success');
			}
		}
		include $this->template('homemaking');
	}
	
	/**
	 * 家政服务 homemaking
	 * */
	public function doMobileHomemaking(){
		global $_GPC, $_W;
		
		$member = $this->checkAuth();
		
		$title = $this->getMobileTitle('homemaking');
		
		$pcate = $this->getServiceCategory('家政服务');
		if (empty($pcate)){
			message("服务分类[家政服务]不存在.", $this->createMobileUrl('home'), 'error');
		}
		$categories = $this->getServiceCategories($pcate['id']);
		if (empty($categories)){
			message("[家政服务]的下级分类不存在.", $this->createMobileUrl('home'), 'error');
		}
		
		$op = empty($_GPC['op']) ? 'display' : $_GPC['op'];
		$op = in_array($op, array('display','delete','post','cancel','resolve')) ? $op : 'display';
		
		if($op == 'display'){
		
			$pindex = max(1, intval($_GPC['page']));
			$psize = 50;
			$paras = array(':weid' => $_W['weid'], ':openid' => $_W['fans']['from_user'],':servicecategory'=>$pcate['id']);
			$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename(community_service)." WHERE `weid`=:weid AND `openid` = :openid ", $paras);
			$list = pdo_fetchall("SELECT * FROM ".tablename(community_service)." WHERE `weid`=:weid AND `openid`=:openid AND `servicecategory`=:servicecategory ORDER BY `id` DESC LIMIT ".($pindex - 1) * $psize.','.$psize, $paras);
			$pager = pagination($total, $pindex, $psize);
			//TODO: 后期调用广告.
			$ads = $this->getAds($member['regionid']);
		
		} elseif ($op == 'post') {
		
			if (checksubmit('submit')) {
		
				if (empty($_GPC['servicesmallcategory'])) {
					message('抱歉，请选择服务类型！');
				}
				if (empty($_GPC['requirement'])) {
					message('抱歉，请填写说明需求内容！');
				}
				$data = array(
					'servicecategory' => $pcate['id'],
					'servicesmallcategory' => $_GPC['servicesmallcategory'],
					'requirement' => $_GPC['requirement'],
					'remark' => $_GPC['remark'],
					'contacttype' => intval($_GPC['contacttype']),
					'contactdesc' => $_GPC['contactdesc'][intval($_GPC['contacttype'])]
				);
				$id = intval($_GPC['id']);
				if (empty($id)) {
					$data['weid'] = $_W['weid'];
					$data['openid'] = $_W['fans']['from_user'];
					$data['regionid'] = $member['regionid'];
					$data['createtime'] = TIMESTAMP;
					$data['status'] = 0;
					pdo_insert('community_service', $data);
					$msg = '信息发布成功。';
				} else {
					pdo_update('community_service', $data, array('id'=>$id,'weid'=>$_W['weid'],'openid'=>$member['openid']));
					$msg = '信息编辑成功。';
				}
				message($msg, $this->createMobileUrl('homemaking'));
		
			} else {
				$id = intval($_GPC['id']);
				if(!empty($id)){
					$sql = "SELECT * FROM ".tablename(community_service)." WHERE weid=:weid AND openid=:openid AND id=:id";
					$paras = array(':weid'=>$_W['weid'], ':openid'=>$_W['fans']['from_user'], ':id'=>$id);
					$item = pdo_fetch($sql, $paras);
				}
			}
		
		} elseif ($op == 'delete') {
				
			pdo_delete(community_service, array('id' => intval($_GPC['id']), 'weid'=>$_W['weid'], 'openid'=>$member['openid']));
			message('家政服务信息删除成功！', $this->createMobileUrl('homemaking'));
		
		} elseif ($op == 'resolve') {
				
			pdo_update(community_service,array('status'=>1), array('id' => intval($_GPC['id']), 'weid'=>$member['weid'], 'openid'=>$member['openid']));
			message('家政服务信息完成！', $this->createMobileUrl('homemaking'));
		
		} elseif ($op == 'cancel') {
				
			pdo_update(community_service,array('status'=>2), array('id' => intval($_GPC['id']), 'weid'=>$member['weid'], 'openid'=>$member['openid']));
			message('家政服务信息取消成功！', $this->createMobileUrl('homemaking'));
				
		}
		include $this->template('homemaking');
	}
	
	/**
	 * 房屋租赁	houselease
	 * */
	public function doWebHouselease(){
		global $_W, $_GPC;
		
		$this->checkWebAuth();
		
		$operation = empty($_GPC['op']) ? 'display' : $_GPC['op'];
		$operation = in_array($operation, array('display', 'post', 'delete')) ? $operation : 'display';
		
		$regionid = intval($_GPC['regionid']);
		
		$serviceCategoryName = '房屋租赁';
		$pcate = $this->getServiceCategory('房屋租赁');
		if (empty($pcate)){
			message("请添加[房屋租赁]的一级分类.",$this->createWebUrl('serviceCategory'),'error');
		}
		$categories = $this->getServiceCategories($pcate['id']);
		if (empty($categories)){
			message("请添加[房屋租赁]的下级分类.",$this->createWebUrl('serviceCategory'),'error');
		}
		
		if($operation == 'display') {
				
			$starttime = empty($_GPC['starttime']) ? strtotime('-1 month') : strtotime($_GPC['starttime']);
			$endtime = empty($_GPC['endtime']) ? TIMESTAMP : strtotime($_GPC['endtime']) + 86399;
				
			$pindex = max(1, intval($_GPC['page']));
			$psize = 50;
		
			$where = " WHERE `weid`=:weid AND `regionid`=:regionid AND `createtime` >= :starttime AND createtime < :endtime AND `servicecategory`=:servicecategory";
			$paras = array(
				':weid' => $_W['weid'],
				':regionid' => $regionid,
				':starttime' => $starttime,
				':endtime' => $endtime,
				':servicecategory' => $pcate['id']
			);
			if (!empty($_GPC['servicesmallcategory'])) {
				$where .= ' AND `servicesmallcategory`=:servicesmallcategory';
				$paras[':servicesmallcategory'] = $_GPC['servicesmallcategory'];
			}
				
			$status = empty($_GPC['status']) ? array(0, 1, 2) : $_GPC['status'] ;
			if (count($status) > 0 && count($status)<3) {
				$where .= " AND `status` in (".implode(',', $status).")";
			}
				
			$list = pdo_fetchall("SELECT * FROM ".tablename(community_service)." $where ORDER BY status ASC, id DESC LIMIT ".($pindex - 1) * $psize.','.$psize, $paras);
			$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename(community_service). $where , $paras);
			$pager = pagination($total, $pindex, $psize);
				
			if (!empty($list)) {
				$members = array();
				foreach ($list as $row) {
					$members[$row['openid']] = $row['openid'];
				}
				$members = pdo_fetchall("SELECT realname, mobile, openid FROM ".tablename(community_member)." WHERE openid IN ('".implode("','", $members)."') AND weid = '{$_W['weid']}'", array(), 'openid');
			}
				
		} elseif($operation == 'delete') {
				
			pdo_delete(community_service, array('id'=>intval($_GPC['id']),'weid'=>$_W['weid'],'regionid'=>$regionid));
			message('删除成功.', referer(), $this->createWebUrl('houselease'),'success');
				
		} elseif ($operation == 'post') {
				
			$id = intval($_GPC['id']);
			$item = pdo_fetch("SELECT * FROM ".tablename(community_service)." WHERE id=:id AND weid=:weid AND regionid=:regionid", array(':id'=>$id,':weid'=>$_W['weid'],':regionid'=>$regionid));
			if (empty($item)) {
				message("抱歉，查看的房屋租赁信息不存在或是已经被删除");
			}
			$member = pdo_fetch("SELECT * FROM ".tablename(community_member)." WHERE weid=:weid AND openid=:openid", array(':weid'=>$_W['weid'], ':openid'=>$item['openid']));
			if (checksubmit('submit')) {
				$data = array(
					'servicesmallcategory' => $_GPC['servicesmallcategory'],
					'requirement' => $_GPC['requirement'],
					'remark' => $_GPC['remark'],
					'contacttype' => $_GPC['contacttype'],
					'contactdesc' => $_GPC['contactdesc'][intval($_GPC['contacttype'])],
					'status' => intval($_GPC['status']),
				);
				pdo_update(community_service, $data, array('id' => $id,'weid'=>$_W['weid'],'regionid'=>$regionid));
				message("更新房屋租赁信息成功！", $this->createWebUrl('houselease', array('regionid' => $regionid)), 'success');
			}
		}
		include $this->template('houselease');
	}
	
	/**
	 * 房屋租赁
	 * */
	public function doMobileHouselease(){
		global $_GPC, $_W;
		
		$member = $this->checkAuth();
		
		$title = $this->getMobileTitle('houselease');
		
		$pcate = $this->getServiceCategory('房屋租赁');
		if (empty($pcate)){
			message("不存在类型为[房屋租赁]的服务分类,请与管理员联系.", $this->createMobileUrl('home'), 'error');
		}
		$categories = $this->getServiceCategories($pcate['id']);
		if (empty($categories)){
			message("不存在[房屋租赁]的下级分类.", $this->createMobileUrl('home'), 'error');
		}
		
		$op = empty($_GPC['op']) ? 'display' : $_GPC['op'];
		$op = in_array($op, array('display','delete','post','resolve','cancel')) ? $op : 'display';
		
		if($op == 'display'){
			
			$pindex = max(1, intval($_GPC['page']));
			$psize = 50;
			$paras = array(':weid' => $_W['weid'], ':openid' => $_W['fans']['from_user'],':servicecategory'=>$pcate['id']);
			$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename(community_service)." WHERE `weid` = :weid AND `openid` = :openid ", $paras);
			$list = pdo_fetchall("SELECT * FROM ".tablename(community_service)." WHERE `weid` = :weid AND `openid` = :openid AND servicecategory=:servicecategory ORDER BY `id` DESC LIMIT ".($pindex - 1) * $psize.','.$psize, $paras);
			$pager = pagination($total, $pindex, $psize);
		
			$ads = $this->getAds($member['regionid']);
				
		} elseif ($op == 'post') {
				
			if (checksubmit('submit')) {
		
				if (empty($_GPC['servicesmallcategory'])) {
					message('抱歉，请选择服务类型！');
				}
				if (empty($_GPC['requirement'])) {
					message('抱歉，请填写说明需求内容！');
				}
				$data = array(
					'servicesmallcategory' => $_GPC['servicesmallcategory'],
					'requirement' => $_GPC['requirement'],
					'remark' => $_GPC['remark'],
					'contacttype' => intval($_GPC['contacttype']),
					'contactdesc' => $_GPC['contactdesc'][intval($_GPC['contacttype'])]
				);
				$id = intval($_GPC['id']);
				if (empty($id)) {
					$data['weid'] = $_W['weid'];
					$data['openid'] = $_W['fans']['from_user'];
					$data['regionid'] = $member['regionid'];
					$data['createtime'] = TIMESTAMP;
					$data['status'] = 0;
					$data['servicecategory'] = $pcate['id'];
					pdo_insert(community_service, $data);
					$msg = '信息发布成功。';
				} else {
					pdo_update(community_service, $data, array('id'=>$id,'regionid'=>$member['regionid'], 'weid'=>$member['weid'], 'openid'=>$member['openid']));
					$msg = '信息编辑成功。';
				}
				message($msg, $this->createMobileUrl('houselease'));
		
			} else {
				$id = intval($_GPC['id']);
				if(!empty($id)){
					$sql = "SELECT * FROM ".tablename(community_service)." WHERE weid=:weid AND openid=:openid AND id=:id";
					$paras = array(':weid'=>$_W['weid'], ':openid'=>$_W['fans']['from_user'], ':id'=>$id);
					$item = pdo_fetch($sql, $paras);
				}
			}
				
		} elseif ($op == 'resolve') {
				
			pdo_update(community_service, array('status'=>1), array('id' => intval($_GPC['id']), 'weid'=>$member['weid'], 'openid'=>$member['openid']));
			message('删除成功！',  $this->createMobileUrl('houselease'), 'success');
		
		} elseif ($op == 'delete') {
			
			pdo_delete(community_service, array('id' => intval($_GPC['id']), 'regionid'=>$member['regionid'], 'weid'=>$member['weid'], 'openid'=>$member['openid']));
			message('服务需求删除成功！', $this->createMobileUrl('houselease'));
		
		} elseif ($op == 'cancel') {
			
			pdo_update(community_service, array('status'=>2), array('id' => intval($_GPC['id']), 'regionid'=>$member['regionid'], 'weid'=>$member['weid'], 'openid'=>$member['openid']));
			message('房屋租赁信息取消成功！', $this->createMobileUrl('houselease'));
		}
		include $this->template('houselease');
	}
	
	/**
	 * 生活服务 Web
	 * */
	public function doWebServiceCategory(){
		global $_GPC, $_W;
		
		$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
		$operation = !in_array($operation, array('post', 'display', 'delete')) ? 'display' : $operation;
		
		if ($operation == 'display') {
			if (!empty($_GPC['displayorder'])) {
				foreach ($_GPC['displayorder'] as $id => $displayorder) {
					pdo_update('community_servicecategory', array('displayorder' => $displayorder), array('id' => $id));
				}
				message('分类排序更新成功！', $this->createWebUrl('serviceCategory', array('op' => 'display')), 'success');
			}
			$children = array();
			$category = pdo_fetchall("SELECT * FROM ".tablename('community_servicecategory')." WHERE weid = '{$_W['weid']}' ORDER BY parentid ASC, displayorder ASC");
			foreach ($category as $index => $row) {
				if (!empty($row['parentid'])){
					$children[$row['parentid']][] = $row;
					unset($category[$index]);
				}
			}
			include $this->template('servicecategory');
		} elseif ($operation == 'post') {
			$parentid = intval($_GPC['parentid']);
			$id = intval($_GPC['id']);
			if(!empty($id)) {
				$category = pdo_fetch("SELECT * FROM ".tablename('community_servicecategory')." WHERE id = '$id'");
			} else {
				$category = array('displayorder' => 0);
			}
			if (!empty($parentid)) {
				$parent = pdo_fetch("SELECT id, name FROM ".tablename('community_servicecategory')." WHERE id = '$parentid'");
				if (empty($parent)) {
					message('抱歉，上级分类不存在或是已经被删除！', $this->createWebUrl('post'), 'error');
				}
			}
			if (checksubmit('submit')) {
				if (empty($_GPC['catename'])) {
					message('抱歉，请输入分类名称！');
				}
				$data = array(
					'weid' => $_W['weid'],
					'name' => $_GPC['catename'],
					'displayorder' => intval($_GPC['displayorder']),
					'parentid' => intval($parentid),
				);
				if (!empty($id)) {
					unset($data['parentid']);
					pdo_update('community_servicecategory', $data, array('id' => $id));
				} else {
					pdo_insert('community_servicecategory', $data);
					$id = pdo_insertid();
				}
				message('更新分类成功！', $this->createWebUrl('serviceCategory'), 'success');
			}
			include $this->template('servicecategory');
		} elseif ($operation == 'delete') {
			$id = intval($_GPC['id']);
			$category = pdo_fetch("SELECT id, parentid FROM ".tablename('community_servicecategory')." WHERE id = '$id'");
			if (empty($category)) {
				message('抱歉，分类不存在或是已经被删除！', $this->createWebUrl('serviceCategory', array('op' => 'display')), 'error');
			}
			pdo_delete('community_servicecategory', array('id' => $id, 'parentid' => $id), 'OR');
			message('分类删除成功！', $this->createWebUrl('serviceCategory'), 'success');
		}
	}
	
	private function getAds($regionid){
		global $_W, $_GPC;
		$sql = "SELECT adid FROM ".tablename('community_admap')." WHERE regionid=:regionid";
		$ads = pdo_fetchall($sql, array(':regionid' => $regionid),'adid');
		
		if(!empty($ads)){
			$adids = array_keys($ads);
			$sql = "SELECT * FROM ".tablename('community_advertisement')." WHERE `starttime` <= '{$_W['timestamp']}' AND `endtime` > '{$_W['timestamp']}' AND `status` = '1' AND `weid` ='{$_W['weid']}' AND `id` in ('" . implode("','", $adids) . "')";
			unset($ads);
			$ads = pdo_fetchall($sql);
			return $ads;
		}
		return array();
	}
	
	public function doWebAdvertisement(){
		// SELECT `id`, `weid`,`url`, `content`, `remark`, `createtime`, `creator`, `starttime`, `endtime`, `status` FROM `ims_community_advertisement`
		global $_W, $_GPC;
		
		$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
		$operation = in_array($operation, array('display','post','delete')) ? $operation : 'display';
		
		if($operation == 'display') {
			
			$pindex = max(1, intval($_GPC['page']));
			$psize = 50;
			
			$starttime = empty($_GPC['starttime']) ? strtotime('-1 month') : strtotime($_GPC['starttime']);
			$endtime = empty($_GPC['endtime']) ? TIMESTAMP : strtotime($_GPC['endtime']) + 86399;
			
			$where .= " WHERE `weid`=:weid AND `createtime`>=:starttime AND `createtime`<:endtime";
			$paras = array(
				':weid' => $_W['weid'],
				':starttime' => $starttime,
				':endtime' => $endtime
			);
			if(!empty($_GPC['keyword'])) {
				$where .= " AND `content` like :content";
				$paras[':content'] = "%{$_GPC['keyword']}%";
			}
			$status = empty($_GPC['status']) ? array(0,1) : $_GPC['status'];
			if (count($status)==1) {
				$where .= " AND `status` = :status";
				$paras[':status'] = $status[0];
			}
			$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('community_advertisement').$where, $paras);
			$list = pdo_fetchall("SELECT * FROM ".tablename('community_advertisement').$where." ORDER BY id DESC LIMIT ".($pindex - 1) * $psize.','.$psize, $paras);
			$pager = pagination($total, $pindex, $psize);
			
			$regions = pdo_fetchall("SELECT * FROM ".tablename('community_region')." WHERE weid=:weid", array(':weid'=>$_W['weid']), 'id');
			foreach ($list as &$ad) {
				$admaps = pdo_fetchall("SELECT regionid FROM ".tablename('community_admap')." WHERE adid=:adid", array(':adid'=>$ad['id']));
				if (!empty($admaps)) {
					$regionnames = array();
					foreach ($admaps as $admap) {
						$regionnames[]=$regions[$admap['regionid']]['title'];
					}
					$ad['region']= implode(',', $regionnames);
				}
			}
			
		} elseif($operation == 'post') {
			
			if(checksubmit()) {
				
				if(empty($_GPC['content'])) {
					message('未填写广告内容.');
				}
				$id = intval($_GPC['id']);
				if(empty($id)) {
					$item = array(
						'url' => $_GPC['url'],
						'content' => $_GPC['content'],
						'starttime' => strtotime($_GPC['starttime']),
						'endtime' => strtotime($_GPC['endtime'])+86399,
						'status' => empty($_GPC['status']) ? '0' : '1',
						'weid' => $_W['weid'],
						'createtime' => TIMESTAMP,
						'creator' => $_W['username']
					);
					pdo_insert('community_advertisement', $item);
					$id = pdo_insertid();
					
					$region = empty($_GPC['region']) ? array() : $_GPC['region'];
					if (is_array($region) && count($region)>0) {
						foreach ($region as $regionid){
							pdo_insert('community_admap', array('weid'=>$_W['weid'],'adid'=>$id,'regionid'=>intval($regionid)));
						}
					}
					message('新增广告成功.', $this->createWebUrl('advertisement', array('op' => 'display')));
					
				} else {
					
					$item = array(
						'url' => $_GPC['url'],
						'content' => $_GPC['content'],
						'starttime' => strtotime($_GPC['starttime']),
						'endtime' => strtotime($_GPC['endtime'])+86399,
						'status' => empty($_GPC['status']) ? '0' : '1',
					);
					pdo_update('community_advertisement', $item, array('id' => $id, 'weid'=>$_W['weid']));
					pdo_delete('community_admap',array('weid'=>$_W['weid'],'adid'=>$id));
					
					$region = empty($_GPC['region']) ? array() : $_GPC['region'];
					if (is_array($region) && count($region)>0) {
						foreach ($region as $regionid){
							pdo_insert('community_admap', array('weid'=>$_W['weid'],'adid'=>$id,'regionid'=>intval($regionid)));
						}
					}
					message('修改广告成功.', $this->createWebUrl('advertisement'));
				}
				
			} else {
				$id = intval($_GPC['id']);
				if (!empty($id)) {
					$item = pdo_fetch("SELECT * FROM ".tablename('community_advertisement')." WHERE `id`=:id ", array(':id'=>$id));
				}
				if (empty($item)) {
					$item = array( 
						'creator' => $_W['username'], 
						'createtime' => TIMESTAMP,
						'starttime' => TIMESTAMP, 
						'endtime' => TIMESTAMP + 86399 ,
						'status' => 1
					);
				} else {
					$regionids = pdo_fetchall("SELECT regionid FROM ".tablename('community_admap')." WHERE adid=:adid AND weid=:weid", array(':adid'=>$item['id'], ':weid'=>$_W['weid']),'regionid');
					$regionids = array_keys($regionids);
				}
				$regions = $this->getRegions();
			}
			
		} elseif($operation == 'delete') {
			$id = intval($_GPC['id']);
			if(!empty($id)){
				pdo_delete('community_advertisement', array( 'id' => $id, 'weid'=>$_W['weid']));
				pdo_delete('community_admap', array('adid'=>$id, 'weid'=>$_W['weid']));
			}
			message('广告删除成功.', referer(), 'success');
		}
		include $this->template('advertisement');
	}
	
	public function doWebAnnouncement() {
		global $_W, $_GPC;
		
		$this->checkWebAuth();
		
		$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
		$operation = in_array($operation, array('display','post','delete')) ? $operation : 'display';
		
		$regionid = intval($_GPC['regionid']);
		
		if($operation == 'display') {
			
			$pindex = max(1, intval($_GPC['page']));
			$psize = 50;
			
			$starttime = empty($_GPC['starttime']) ? strtotime('-1 month') : strtotime($_GPC['starttime']);
			$endtime = empty($_GPC['endtime']) ? TIMESTAMP : strtotime($_GPC['endtime']) + 86399;
			
			$where .= " WHERE `weid`=:weid AND `regionid`=:regionid AND `createtime`>=:starttime AND `createtime`<:endtime";
			$paras = array(
				':weid' => $_W['weid'],
				':regionid' => $regionid,
				':starttime' => $starttime,
				':endtime' => $endtime
			);
			if(!empty($_GPC['keyword'])) {
				$where .= " AND `title` like :title";
				$paras[':title'] = "%{$_GPC['keyword']}%";
			}
			$status = empty($_GPC['status']) ? array(0,1) : $_GPC['status'];
			if (count($status)==1) {
				$where .= " AND `status` = :status";
				$paras[':status'] = $status[0];
			}
			
			$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('community_announcement').$where, $paras);
			$list = pdo_fetchall("SELECT * FROM ".tablename('community_announcement').$where." ORDER BY id DESC LIMIT ".($pindex - 1) * $psize.','.$psize, $paras);
			$pager = pagination($total, $pindex, $psize);
			
		} elseif($operation == 'post') {
			
			if(checksubmit('submit')) {
				if(empty($_GPC['title'])) {
					message('未填写公告标题.');
				}
				if(empty($_GPC['content'])) {
					message('未填写公告内容.');
				}
				$item = array( 
					'title' => $_GPC['title'], 
					'content' => $_GPC['content'], 
					'starttime' => strtotime($_GPC['starttime']), 
					'endtime' => strtotime($_GPC['endtime']) + 86399,
					'status' => empty($_GPC['status']) ? '0' : '1', 
				);
				$id = intval($_GPC['id']);
				if(empty($id)) {
					$item['author'] = $_W['username'];
					$item['createtime'] = TIMESTAMP;
					$item['weid'] = $_W['weid'];
					$item['regionid'] = $regionid;
					pdo_insert('community_announcement', $item);
					message('保存成功.', $this->createWebUrl('announcement', array('regionid' => $regionid)));
				} else {
					pdo_update('community_announcement', $item, array('id'=>$id,'weid'=>$_W['weid'],'regionid'=>$regionid));
					message('修改成功.', $this->createWebUrl('announcement', array('regionid' => $regionid)));
				}
			} else {
				$id = intval($_GPC['id']);
				if (!empty($id)) {
					$item = pdo_fetch("SELECT * FROM ".tablename('community_announcement')." where `id`=:id ", array(':id'=>$id));
				}
				if (empty($item)) {
					$item = array( 
						'author' => $_W['username'], 
						'starttime' => TIMESTAMP, 
						'endtime' => TIMESTAMP,
						'status' => 1,
						'regionid' => $regionid
					);
				}
			}
		} elseif($operation == 'delete') {
			$id = intval($_GPC['id']);
			if(!empty($id)){
				pdo_delete('community_announcement', array('id'=>$id, 'weid'=>$_W['weid'],'regionid'=>$regionid));
			} 
			message('删除成功.', referer(), 'success');
		}
		include $this->template('announcement');
	}
	
	public function doMobileAnnouncement() {
		global $_GPC, $_W;
		
		$member = $this->checkAuth();
		
		$title = $this->getMobileTitle('announcement');
		
		$op = empty($_GPC['op']) ? 'display' : $_GPC['op'];
		$op = in_array($op, array('display','detail')) ? $op : 'display';
		
		if($op == 'display'){
			
			$pindex = max(1, intval($_GPC['page']));
			$psize = 50;
			$where = " WHERE `weid` = :weid AND `regionid`=:regionid AND `status`=:status AND `starttime`<=:starttime AND `endtime`>:endtime";
			$paras = array(
				':weid'=> $member['weid'],
				':regionid' => $member['regionid'],
				':starttime' => TIMESTAMP,
				':endtime' => TIMESTAMP,
				':status' => 1
			);
			$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('community_announcement').$where, $paras);
			$list = pdo_fetchall("SELECT * FROM ".tablename('community_announcement')."$where ORDER BY id DESC LIMIT ".($pindex - 1) * $psize.','.$psize, $paras);
			$pager = pagination($total, $pindex, $psize);
			
		}else if ($op == 'detail') {
			$id = intval($_GPC['id']);
			if(!empty($id)){
				$item = pdo_fetch("SELECT * FROM ".tablename('community_announcement')." WHERE `id`=:id AND `weid`=:weid AND `regionid`=:regionid", array(':id'=>$id,':weid'=>$_W['weid'],':regionid'=>$member['regionid']));
			}
			if (empty($item)){
				message('未找到指定的公告.',createMobileUrl('announcement'));
			}
		}
		include $this->template('announcement');
	}
	
	/**
	 * 获取报修类型.
	 * */
	private function getRepairCategories(){
		return array('所有','水暖','公共设施','电器设备');
	}
	
	/**
	 * 获取报修评价
	 * */
	private function getRepairRank($id){
		$ranks = array(0=>'', 1=>'满意',2=>'一般',3=>'差劲');
		$iid = intval($id);
		return $ranks[$iid];
	}
	
	public  function doWebRepair(){
		global $_GPC, $_W;
		
		$this->checkWebAuth();
		
		$operation = empty($_GPC['op']) ? 'display' : $_GPC['op'];
		$operation = in_array($operation, array('display','delete','post')) ? $operation : 'display';
		
		$regionid = intval($_GPC['regionid']);
		
		$categories = $this->getRepairCategories();
		
		if($operation == 'display') {
			$pindex = max(1, intval($_GPC['page']));
			$psize = 50;
			
			$starttime = empty($_GPC['starttime']) ? strtotime('-1 month') : strtotime($_GPC['starttime']);
			$endtime = empty($_GPC['endtime']) ? TIMESTAMP : strtotime($_GPC['endtime']) + 86399;
			
			$where = " WHERE `weid` = :weid AND `type` = '1' AND `regionid` = :regionid AND createtime >= :starttime AND createtime < :endtime";
			$paras = array(
				':weid' => $_W['weid'],
				':regionid' => $regionid,
				':starttime' => $starttime,
				':endtime' => $endtime,
			);
			
			if(!empty($_GPC['category']) && $_GPC['category']!='所有') {
				$where .= " AND `category` = :category  ";
				$paras[':category'] = $_GPC['category'];
			}
			$status = empty($_GPC['status']) ? array(0, 1, 2) : $_GPC['status'] ;
			if (count($status) > 0 && count($status) < 3) {
				$in = implode(',', $status);
				$where .= " AND `status` in ( $in )";
			}
			$list = pdo_fetchall("SELECT * FROM ".tablename('community_report')." $where ORDER BY id DESC LIMIT ".($pindex - 1) * $psize.','.$psize,$paras);
			$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('community_report'). $where, $paras);
			$pager = pagination($total, $pindex, $psize);
			
			if (!empty($list)) {
				$members = array();
				foreach ($list as $row) {
					$members[$row['openid']] = $row['openid'];
				}
				$members = pdo_fetchall("SELECT realname, mobile, openid FROM ".tablename('community_member')." WHERE `openid` IN ('".implode("','", $members)."') AND `weid` = :weid AND `regionid`=:regionid", array(':weid'=>$_W['weid'],':regionid'=>$regionid), 'openid');
			}
			
		} elseif($operation == 'post') {
			
			$id = intval($_GPC['id']);
			$item = pdo_fetch("SELECT * FROM ".tablename('community_report')." WHERE `id`=:id AND `type` = '1' AND `weid`=:weid AND `regionid`=:regionid", array(':id' => $id,':weid'=>$_W['weid'],':regionid'=>$regionid));
			if (empty($item)) {
				message('抱歉，查看的报修申请不存在或是已经被删除！');
			}
			
			$member = pdo_fetch("SELECT * FROM ".tablename('community_member')." WHERE `weid` = :weid AND `openid`=:openid", array(':weid'=>$_W['weid'],':openid'=> $item['openid']));
			
			if (checksubmit('submit')) {
				$data = array(
					'category' => $_GPC['category'],
					'requirement' => $_GPC['requirement'],
					'content' => $_GPC['content'],
					'status' => $_GPC['status'],
				);
			
				//管理员可以更改报修复申请
				pdo_update('community_report', $data, array('id'=>$id,'weid'=>$_W['weid'],'regionid'=>$regionid));
				
				if (!empty($_GPC['reply'])) {
					pdo_insert('community_reply', array(
						'weid' => $_W['weid'],
						'openid' => $item['openid'],
						'reportid' => $id,
						'isreply' => '1',
						'content' => $_GPC['reply'],
						'createtime' => TIMESTAMP,
					));
					pdo_update('community_report', array('newmsg' => 0), array('id' => $id,'weid'=>$_W['weid'],'regionid'=>$regionid));
				}
				
				message('更新报修记录信息成功！', referer(), 'success');
			}
			if (intval($item['newmsg'])==2) {
				pdo_update('community_report', array('newmsg'=>0), array('id'=>$id,'weid'=>$_W['weid'],'regionid'=>$regionid));;
			}
			$item['reply'] = pdo_fetchall("SELECT * FROM ".tablename('community_reply')." WHERE reportid = :reportid ORDER BY id ASC", array(':reportid' => $id));

		} elseif($operation == 'delete') {
			$id = intval($_GPC['id']);
			$item = pdo_fetch("SELECT * FROM ".tablename('community_report')." WHERE `id`=:id AND `weid`=:weid AND `regionid`=:regionid", array(':id' => $id,':weid'=>$_W['weid'],':regionid'=>$regionid));
			if (empty($item)) {
				message('抱歉，查看的报修申请不存在或是已经被删除！');
			}
			pdo_delete('community_report', array('id'=>$id,'weid'=>$_W['weid'],'regionid'=>$regionid));
			pdo_delete('community_reply', array('reportid'=>$id,'weid'=>$_W['weid'],'regionid'=>$regionid));
			message('删除成功.', referer());
		}
	
		include $this->template('repair');
	}
	
	public  function doMobileRepair(){
		global $_GPC, $_W;
		
		$member = $this->checkAuth();
		$title = $this->getMobileTitle('repair');
		
		$op = empty($_GPC['op']) ? 'display' : $_GPC['op'];
		$op = in_array($op, array('display','resolve','cancel','post', 'delete')) ? $op : 'display';
		
		$categories = $this->getRepairCategories();
		
		if ($op == 'resolve') {
			
			$paras  = array(
				':id' => intval($_GPC['id']),
				':weid' => $_W['weid'],
				':openid' => $_W['fans']['from_user']
			);
			$item = pdo_fetch("SELECT * FROM ".tablename('community_report')." WHERE `id`=:id AND `type`='1' AND `weid`=:weid AND `openid`=:openid ",  $paras);
			if (empty($item)) {
				message('未找到指定的报修申请.');
			}
			
			if (checksubmit('submit')) {
				$paras = array(
					'id' => intval($_GPC['id']),
					'weid' => $_W['weid'],
					'openid' => $_W['fans']['from_user'],
				);
				$data = array(
					'status' => 1,
					'comment' => $_GPC['comment'],
					'rank' => intval($_GPC['rank']),
					'newmsg' => 2
				);
				pdo_update('community_report',$data, $paras);
				message('已确认该条申请完成，感谢您对我们的评价！', $this->createMobileUrl('repair'));
			}
			
		} elseif ($op == 'cancel') {
			
			$paras  = array(
				':id' => intval($_GPC['id']),
				':weid' => $_W['weid'],
				':openid' => $_W['fans']['from_user']
			);
			$item = pdo_fetch("SELECT * FROM ".tablename('community_report')." WHERE `id`=:id AND `type`='1' AND `weid`=:weid AND `openid`=:openid ",  $paras);
			if (empty($item)) {
				message('未找到指定的报修申请.');
			}
			
			$paras = array(
				'id' => intval($_GPC['id']),
				'weid' => $_W['weid'],
				'openid' => $_W['fans']['from_user'],
			);
			pdo_update('community_report', array('status' => 2), $paras);
			message('报修申请取消成功！', $this->createMobileUrl('repair'));
			
		} elseif ($op == 'delete') {
			
			$paras  = array(
				':id' => intval($_GPC['id']),
				':weid' => $_W['weid'],
				':openid' => $_W['fans']['from_user']
			);
			$item = pdo_fetch("SELECT * FROM ".tablename('community_report')." WHERE `id`=:id AND `type`='1' AND `weid`=:weid AND `openid`=:openid ",  $paras);
			if (empty($item)) {
				message('未找到指定的报修申请.');
			}
			
			$paras = array(
				'id' => intval($_GPC['id']),
				'weid' => $_W['weid'],
				'openid' => $_W['fans']['from_user'],
			);
			pdo_delete('community_report', $paras);
			pdo_delete('community_reply', array('reportid'=>intval($_GPC['id'])));
			
			message('报修申请取消成功！', $this->createMobileUrl('repair'));
			
		} elseif( $op == 'display'){
			
			$pindex = max(1, intval($_GPC['page']));
			$psize = 50;
			
			$paras = array();
			$paras[':weid'] = $weid = $_W['weid'];
			$paras[':openid'] = $from = $_W['fans']['from_user'];
			
			$total = pdo_fetchcolumn("SELECT count(*) FROM ".tablename('community_report')." WHERE `weid`=:weid AND `openid`=:openid AND type='1'", $paras);
			$list = pdo_fetchall("SELECT * FROM ".tablename('community_report')." WHERE `weid`=:weid AND `openid`=:openid AND type='1' ORDER BY `id` DESC LIMIT ".($pindex - 1) * $psize.','.$psize, $paras, 'id');
			$pager = pagination($total, $pindex, $psize);
			
			if (!empty($list)) {
				foreach ($list as &$row) {
					$row['reply'] = pdo_fetchall("SELECT content, isreply FROM ".tablename('community_reply')." WHERE `reportid`=:reportid ORDER BY id ASC", array(':reportid' => $row['id']));
				}
			}
	
		} elseif ($op == 'post') {
			if (checksubmit('submit')) {
				$data = array(
					'weid' => $_W['weid'],
					'regionid' => $member['regionid'],
					'openid' => $_W['fans']['from_user'],
					'type' => 1,
					'category' => $_GPC['category'],
					'createtime' => TIMESTAMP,
					'requirement' => $_GPC['requirement'],
					'content' => $_GPC['content'],
					'status' => 0
				);
				pdo_insert('community_report', $data);
				message('报修申请提交成功，请查看“我的报修”等待工作人员联系。', $this->createMobileUrl('repair', array('op'=>'display')));
			} else {
				$categories = $this->getRepairCategories();
			}
		}
		include $this->template('repair');
	}
	
	public function doMobileReply() {
		global $_W, $_GPC;
		
		$member = $this->checkAuth();
		
		$type = intval($_GPC['type']);
		if ($type == '1') {
			$url = $this->createMobileUrl('repair', array('page' => $_GPC['page']), 'success');
			$title = '报修';
		} else {
			$url = $this->createMobileUrl('report', array('page' => $_GPC['page']), 'success');
			$title = '投诉';
		}
		
		$id = intval($_GPC['id']);
		
		$sql = "SELECT * FROM ".tablename('community_report')." WHERE weid=:weid AND id=:id LIMIT 1";
		$item = pdo_fetch($sql, array(':weid'=>$_W['weid'], ':id'=>$id));
		if (empty($item)) {
			message('补充失败,未找到相关'.$title);
		}
		
		if (!empty($id)) {
			pdo_insert('community_reply', array(
				'weid' => $_W['weid'],
				'openid' => $_W['fans']['from_user'],
				'reportid' => $id,
				'isreply' => '0',
				'content' => $_GPC['content'],
				'createtime' => TIMESTAMP,
			));
			pdo_update('community_report', array('newmsg' => 1), array('id' => $id));
		}
		
		message('补充报修申请成功！相关工作人员会马上回复！', $url );
	}

	private function getReportCategories(){
		return array('所有类型','建议','投诉');
	}
	
	public function doWebReport() {
		global $_GPC, $_W;
		
		$this->checkWebAuth();
		
		$operation = empty($_GPC['op']) ? 'display' : $_GPC['op'];
		$operation = in_array($operation, array('post','display','delete')) ? $operation : 'display';
		
		$regionid = intval($_GPC['regionid']);
		$categories = $this->getReportCategories();
		
		if($operation == 'display') {
			
			$starttime = empty($_GPC['starttime']) ? strtotime('-1 month') : strtotime($_GPC['starttime']);
			$endtime = empty($_GPC['endtime']) ? TIMESTAMP : strtotime($_GPC['endtime']) + 86399;
			
			$pindex = max(1, intval($_GPC['page']));
			$psize = 50;

			$where = " WHERE `weid`=:weid AND type='2' AND `regionid`=:regionid AND `createtime`>=:starttime AND createtime<:endtime";
			$paras = array(
				':weid' => $_W['weid'],
				':regionid' => $regionid,
				':starttime' => $starttime,
				':endtime' => $endtime
			);

			$_GPC['category'] = empty($_GPC['category']) ? $categories[0] : $_GPC['category'];
			if($_GPC['category']!=$categories[0]) {
				$where .= " AND `category`=:category  ";
				$paras[':category'] = $_GPC['category'];
			}
			
			$status = empty($_GPC['status']) ? array(0, 1) : $_GPC['status'] ;
			if (count($status) == 1) {
				$where .= " AND `status` = :status";
				$paras[':status'] = $status[0];
			}
			
			$list = pdo_fetchall("SELECT * FROM ".tablename('community_report')." $where ORDER BY status ASC, id DESC LIMIT ".($pindex - 1) * $psize.','.$psize, $paras);
			$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('community_report'). $where , $paras);
			$pager = pagination($total, $pindex, $psize);
			
			if (!empty($list)) {
				$members = array();
				foreach ($list as $row) {
					$members[$row['openid']] = $row['openid'];
				}
				$members = pdo_fetchall("SELECT realname, mobile, openid FROM ".tablename('community_member')." WHERE openid IN ('".implode("','", $members)."') AND weid = '{$_W['weid']}'", array(), 'openid');
			}
		} elseif($operation == 'delete') {
			
			pdo_delete('community_report', array('id'=>intval($_GPC['id']),'weid'=>$_W['weid'],'regionid'=>$_GPC['regionid']));
			pdo_delete('community_reply', array('reportid'=>intval($_GPC['id'])));
			message('删除成功.', referer(), 'success');
			
		} elseif ($operation == 'post') {
			
			$id = intval($_GPC['id']);
			$item = pdo_fetch("SELECT * FROM ".tablename('community_report')." WHERE `id`=:id AND `weid`=:weid ", array(':id' => $id,':weid'=>$_W['weid']));
			if (empty($item)) {
				message('抱歉，查看的投诉申请不存在或是已经被删除！');
			}
			
			if (checksubmit()) {
				$data = array(
					'category' => $_GPC['category'],
					'content' => $_GPC['content'],
					'resolve' => $_GPC['resolve'],
					'resolver' => $_GPC['resolver'],
					'status' => intval($_GPC['status']),
				);
				if (!empty($data['resolve'])) {
					$data['status'] = 1;
					$data['resolvetime'] = TIMESTAMP;
				}
				pdo_update('community_report', $data, array('id'=>intval($_GPC['id']),'weid'=>$_W['weid'],'regionid'=>$_GPC['regionid']));
				message('更新投诉记录信息成功！', $this->createWebUrl('report', array('op' => 'display', 'regionid' => $regionid)), 'success');
			}
			$member = pdo_fetch("SELECT * FROM ".tablename('community_member')." WHERE `weid` = :weid AND `openid`=:openid", array(':weid'=>$item['weid'],':openid'=> $item['openid']));
			$item['reply'] = pdo_fetchall("SELECT * FROM ".tablename('community_reply')." WHERE reportid = :reportid ORDER BY id ASC", array(':reportid' => $id));
		}
		include $this->template('report');
	}
	
	public function doMobileReport(){
		global $_GPC, $_W;
		
		$member = $this->checkAuth();
		
		$title = $this->getMobileTitle('report');
		
		$op = empty($_GPC['op']) ? 'display' : $_GPC['op'];
		$op = in_array($op, array('display','delete', 'post', 'cancel')) ? $op : 'display';
		
		$categories = $this->getReportCategories();
		
		$id = intval($_GPC['id']);
		if($op == 'display'){
			$pindex = max(1, intval($_GPC['page']));
			$psize = 50;
			$paras = array(':weid' => $_W['weid'], ':openid' => $_W['fans']['from_user']);
			$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('community_report')." WHERE `weid` = :weid AND `openid` = :openid AND type = '2'", $paras);
			$list = pdo_fetchall("SELECT * FROM ".tablename('community_report')." WHERE `weid` = :weid AND `openid` = :openid AND type = '2' ORDER BY `id` DESC LIMIT ".($pindex - 1) * $psize.','.$psize, $paras);
			$pager = pagination($total, $pindex, $psize);
		} elseif ($op == 'post') {
			if (checksubmit('submit')) {
				if (empty($_GPC['content'])) {
					message('抱歉，请输入您要投诉的具体内容！');
				}
				$data = array(
					'category' => $_GPC['category'],
					'content' => $_GPC['content'],
					'weid' => $_W['weid'],
					'openid' => $_W['fans']['from_user'],
					'type' => 2,
					'regionid' => $member['regionid'],
					'createtime' => TIMESTAMP,
				);
				pdo_insert('community_report', $data);
				message('投诉申请提交成功，请查看“我的投诉”等待工作人员回复。', $this->createMobileUrl('report', array('op'=>'display')));
			}
			unset($categories[0]);
		} elseif ($op == 'cancel') {
			$paras = array(
				'id' => $id,
				'weid' => $_W['weid'],
				'openid' => $_W['fans']['from_user'],
			);
			pdo_update('community_report', array('status' => 2), $paras);
			message('投诉申请取消成功！', $this->createMobileUrl('report'));
		} elseif ($op == 'delete') {
			$paras = array(
				'id' => $id,
				'weid' => $_W['weid'],
				'openid' => $_W['fans']['from_user'],
			);
			pdo_delete('community_report', $paras);
			message('删除成功！', $this->createMobileUrl('report'));
		}
		include $this->template('report');
	}

	public function doWebPhone(){
		global $_W, $_GPC;
		
		$this->checkWebAuth();
		
		$op = !empty($_GPC['op']) ? $_GPC['op'] :'display';
		$op = in_array($op, array('display', 'delete')) ? $op : 'display';
		
		$regionid = intval($_GPC['regionid']);

		if ($op == 'delete') {
			$id = intval($_GPC['id']);
			if (!empty($id)){
				pdo_delete('community_phone',array('id'=>$id,'weid'=>$_W['weid'],'regionid'=>$regionid));	
			}
			
			$op = 'display';
			
		} elseif($op == 'display') {
			if (checksubmit('submit')) {
				$phones = $_GPC['phones'];
				$titles = $_GPC['titles'];
				$ids = $_GPC['ids'];
				
				foreach ($phones as $key => $value) {
					$id = intval($ids[$key]);
					$data = array(
						'title' => $titles[$key],
						'phone' => $phones[$key],
						'weid' => $_W['weid'],
						'regionid' => $regionid,
					);
					if ($id == 0) {
						pdo_insert('community_phone', $data);
					} else {
						pdo_update('community_phone', $data, array('id'=>$id,'weid'=>$_W['weid'],'regionid'=>$regionid));
					}
				}
				message('便民电话更新成功！', referer(), 'success');
			}
		}
		
		$sql = "SELECT * FROM ".tablename('community_phone')." where `weid`=:weid AND `regionid`=:regionid";
		$paras = array(':weid'=>$_W['weid'], ':regionid'=>$regionid);
		$phones = pdo_fetchall($sql, $paras);
		
		include $this->template('phone');
	}
	
	public function doMobilePhone(){
		global $_GPC, $_W;
		
		$member = $this->checkAuth();
		
		$title = $this->getMobileTitle('phone');
		
		$sql = "SELECT * FROM ".tablename('community_phone')." WHERE `weid`=:weid AND `regionid`=:regionid";
		$paras = array(':weid'=>$_W['weid'], ':regionid'=>$member['regionid']);
		$phones = pdo_fetchall($sql, $paras);
		include $this->template('phone');
	}
	
	public function doWebRegion(){
	
		global  $_W, $_GPC;
		
		$op = !empty($_GPC['op']) ? $_GPC['op'] :'display';
		$op = in_array($op, array('display', 'delete', 'manage')) ? $op : 'display';
		
		$weid = intval($_W['weid']);
		if ($op == 'delete') {
			$id = intval($_GPC['id']);
			if (empty($id)){
				message('未找到指定小区信息.');
			} else {
				pdo_delete('community_region',array('id'=>$id));
			}
			$op = 'display';
		
		} elseif($op == 'display') {
			
			if (checksubmit()) {
				
				$ids = $_GPC['ids'];
				$titles = $_GPC['titles'];
				$linkmen = $_GPC['linkmen'];
				$linkways = $_GPC['linkways'];
				$contents = $_GPC['contents'];
				foreach ($titles as $key => $title) {
					$id = intval($ids[$key]);
					$data = array(
						'linkman' => $linkmen[$key],
						'linkway' => $linkways[$key],
						'title' => $titles[$key],
						'content' => $contents[$key]
					);
					if (empty($id)) {
						$data['weid'] = $_W['weid'];
						pdo_insert('community_region', $data);
					} else {
						pdo_update('community_region', $data, array('id'=>$id));
					}
				}
				header("Location: ".$this->createWebUrl('region'));
				exit;
			}
			
		} elseif ($op == 'manage') {
			$id = intval($_GPC['regionid']);
			$region = $this->getRegion($id);
			
			//获取待办的报修申请
			$repaircount = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('community_report')." WHERE type = '1' AND weid = '{$_W['weid']}' AND regionid = '{$id}' AND status = '0'");
			//获取待办的投诉申请
			$reportcount = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('community_report')." WHERE type = '2' AND weid = '{$_W['weid']}' AND regionid = '{$id}' AND status = '0'");
			//待回复事项
			$reply = pdo_fetchall("SELECT * FROM ".tablename('community_report')." WHERE newmsg = '1' AND weid = '{$_W['weid']}'", array(), 'id');
			if (!empty($reply)) {
				foreach ($reply as &$row) {
					$row['message'] = pdo_fetch("SELECT * FROM ".tablename('community_reply')." WHERE reportid = {$row['id']} AND isreply = '0' ORDER BY id DESC"); 
				}
			}
		}
		$regions = $this->getRegions();
		include $this->template('region');
	}
	
	/**
	 * 住户类型
	 * */
	private function getMemberType($type){
		$types = $this->getMemberTypes();
		return $types[$type];
	}
	private function getMemberTypes(){
		return array(1=>'业主',2=>'租户');
	}
	
	private $cummunityMemberTypes = array(1=>'业主',2=>'租户');
	
	public function doWebMember() {
		global $_GPC, $_W;
		$this->checkWebAuth();
		
		$operation = empty($_GPC['op']) ? 'display' : $_GPC['op'];
		$operation = in_array($operation, array('display','delete','post', 'verify','audit')) ? $operation : 'display';
		
		$types = $this->getMemberTypes();
		$regionid = intval($_GPC['regionid']);
		
		if($operation == 'display') {
			
			$pindex = max(1, intval($_GPC['page']));
			$psize = 50;

			$where = " WHERE `weid` = :weid AND `regionid` = :regionid ";
			$paras = array(
				':weid' => $_W['weid'],
				':regionid' => $regionid,
			);
			
			if(!empty($_GPC['type'])) {
				$where .= " AND `type`=:type ";
				$paras[':type'] = $_GPC['type'];
			}
			if(!empty($_GPC['realname'])) {
				$where .= " AND `realname` like :realname ";
				$paras[':realname'] = '%'.$_GPC['realname'].'%';
			}
			if(!empty($_GPC['mobile'])) {
				$where .= " AND `mobile` like :mobile ";
				$paras[':mobile'] = '%'.$_GPC['mobile'].'%';
			}
			
			$list = pdo_fetchall("SELECT * FROM ".tablename('community_member')." $where ORDER BY `id` DESC LIMIT ".($pindex - 1) * $psize.','.$psize, $paras);
			$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('community_member')." $where", $paras);
			$pager = pagination($total, $pindex, $psize);
			
		} elseif($operation == 'audit') {
			
			if (checksubmit('auditall')) {
				$sql = "UPDATE ".tablename('community_member')." SET status=1 WHERE weid=:weid AND regionid=:regionid AND status=0 ";
				pdo_query($sql, array(':weid'=>$_W['weid'],':regionid'=>$regionid));
				message('审核成功。',$this->createWebUrl('member',array('regionid'=>$regionid, 'op'=>'display')));
			}
			
			if (checksubmit('auditselected')) {
				$ids = $_GPC['id'];
				foreach ($ids as &$id) {
					$id= intval($id);
				}
				if (!empty($ids)) {
					$sql = "UPDATE ".tablename('community_member')." SET status=1 WHERE weid=:weid AND regionid=:regionid AND status=0 AND id in (".implode(',', $ids).")";
					pdo_query($sql, array(':weid'=>$_W['weid'],':regionid'=>$regionid));
				}
				
				message('审核成功。',$this->createWebUrl('member',array('regionid'=>$regionid,'op'=>'audit')));
			}
			
			$pindex = max(1, intval($_GPC['page']));
			$psize = 50;
			
			$where = " WHERE `weid` = :weid AND `regionid` = :regionid AND `status`=0 ";
			$paras = array(
				':weid' => $_W['weid'],
				':regionid' => $regionid,
			);
			
			$list = pdo_fetchall("SELECT * FROM ".tablename('community_member')." $where ORDER BY `id` DESC LIMIT ".($pindex - 1) * $psize.','.$psize, $paras);
			$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('community_member')." $where", $paras);
			$pager = pagination($total, $pindex, $psize);
			
			$needAudit = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('community_member')." WHERE `weid`=:weid AND `regionid`=:regionid AND `status`=0", $paras = array(':weid' => $_W['weid'],':regionid' => $regionid));
			
		} elseif( $operation=='delete' ) {
	
			$paras = array(
				'id' => intval($_GPC['id']),
				'weid' => $_W['weid'],
			);
			pdo_delete('community_member', $paras);
			if (empty($_GPC['ref'])) {
				message('保存成功.', $this->createWebUrl('member', array('regionid'=>$regionid)),'success');
			}
			message('删除成功.', $this->createWebUrl('member', array('regionid'=>$regionid,'op'=>'audit')),'success');
			
		} elseif( $operation=='post' ) {
			if (checksubmit('submit')) {
				$data = array(
					'type' => $_GPC['type'],
					'realname' => $_GPC['realname'],
					'mobile' => $_GPC['mobile'],
					'address' => $_GPC['address'],
					'remark' => $_GPC['remark'],
					'regionid' => $_GPC['_regionid'],
				);
				
				$paras = array('id' => intval($_GPC['id']),'weid'=>$_W['weid']);
				pdo_update('community_member', $data, $paras);
				if (empty($_GPC['ref'])) {
					message('保存成功.', $this->createWebUrl('member', array('regionid'=>$regionid)),'success');
				}
				message('保存成功.', $this->createWebUrl('member', array('regionid'=>$regionid,'op'=>'audit')),'success');
			}
			
			$sql = "SELECT * FROM ".tablename('community_member')." WHERE `id`=:id AND weid=:weid";
			$paras = array(':id' => intval($_GPC['id']),':weid'=>$_W['weid']);
			$member = pdo_fetch($sql, $paras);
			if (empty($member)) {
				message('未找到指定用户信息.', $this->createWebUrl('member', array( 'regionid'=>$regionid)),'error');
			}
			$regions = $this->getRegions();
		} elseif ($operation == 'verify') {
			$status = intval($_GPC['status']);
			$id = intval($_GPC['id']);
			pdo_update('community_member', array('status' => $status), array('id' => $id,'weid'=>$_W['weid']));
			message('更改用户状态成功！', referer(), 'success');
		}
		include $this->template('member');
	}
	
	public function doMobileMember() {
		global $_GPC, $_W;
		
		$member = $this->checkAuth();
		
		$title = $this->getMobileTitle('member');
		
		$operation = empty($_GPC['op']) ? 'post' : $_GPC['op'];
		$operation = in_array($operation, array('post','unbind')) ? $operation : 'post';
		
		$types = $this->getMemberTypes();
		
		if( $operation=='post' ) {
			
			if (checksubmit('submit')) {
				$data = array(
					'type' => $_GPC['type'],
					'realname' => $_GPC['realname'],
					'mobile' => $_GPC['mobile'],
					'address' => $_GPC['address'],
					'remark' => $_GPC['remark'],
				);
				$paras = array('weid' => $_W['weid'],'openid'=>$_W['fans']['from_user']);
				pdo_update('community_member', $data, $paras);
				message('保存成功.', $this->createMobileUrl('home', array( 'op' => 'display','regionid'=>$regionid)),'success');
			}
			
			$regions = $this->getRegions();
			
		} elseif($operation == 'unbind') {
			if (checksubmit('submit')) {
				$paras = array('weid' => $_W['weid'],'openid'=>$_W['fans']['from_user']);
				pdo_delete('community_member', $paras);
				message('解绑成功.', $this->createMobileUrl('home', array( 'op' => 'display','regionid'=>$regionid)),'success');
			}
		}
		include $this->template('member');
	}
	
	public function doWebManager() {
		global  $_W, $_GPC;
		
		$op = !empty($_GPC['op']) ? $_GPC['op'] :'display';
		$op = in_array($op, array('display', 'post')) ? $op : 'display';
		
		$memberids = array();
		$sql = 'SELECT * FROM '.tablename('wechats_members')." WHERE weid={$_W['weid']}";
		$allmembers = pdo_fetchall($sql);
		unset($sql);
		if (!empty($allmembers)) {
			foreach ($allmembers as $m) {
				$memberids[] = $m['memberid'];
			};
		}
		
		if($op == 'display') {
			
			$pindex = max(1, intval($_GPC['page']));
			$psize = 20;
			$where = ' WHERE uid in ('.implode(',', $memberids).')';
				
			if (!empty($_GPC['username'])) {
				$where .= " AND `username` LIKE '%{$_GPC['username']}%'";
			}
			$sql = 'SELECT * FROM '.tablename('members').$where." LIMIT ".($pindex - 1) * $psize .','.$psize;
			$members = pdo_fetchall($sql);
			$total = pdo_fetchcolumn('SELECT COUNT(*) FROM '.tablename('members').$where);
			$pager = pagination($total, $pindex, $psize);
	
		} elseif ($op == 'post') {
	
			if (checksubmit()) {
				$memberid = intval($_GPC['uid']);
				if (empty($memberid)) {
					message('未指定管理员');
				}
				$menus = $_GPC['menu'];
				if (empty($menus)) {
					message('未选择任何权限');
				}
				pdo_delete(modules_menus, array('acid'=>$_W['weid'], 'memberid'=>$memberid,'module'=>'community'));
				
				foreach ($menus as $menu) {
					$tmp = explode(',', $menu);
					$data = array(
						'acid' => $_W['weid'],
						'memberid' => $memberid,
						'module' => 'community',
						'do' => $tmp[0],
						'state' => $tmp[1]
					);
					pdo_insert(modules_menus, $data);
				}
				message('编辑成功.',$this->createWebUrl('manager', array('op'=>'display')));
			}
			
			$sql = "SELECT * FROM ".tablename('members')." WHERE `uid`=:uid";
			$member = pdo_fetch($sql, array(':uid'=>intval($_GPC['uid'])));
			if (empty($member) || !in_array($member['uid'], $memberids)) {
				message('未指定管理员');
			}
			
			$sql = "SELECT * FROM ".tablename(modules_menus)." WHERE memberid=:memberid AND acid=:acid AND module='community'";
			$mymenus = pdo_fetchall($sql, array(':memberid'=>$member['uid'],':acid'=>$_W['weid']));
			
			$menus = array();
			foreach ($mymenus as $menu) {
				$key = $menu['do'].'&'.$menu['state'];
				$menus[$key] = $menu;
			}
			unset($mymenus);
			$menukeys = array_keys($menus);
			
			$allmenus = array();
			$bindings = pdo_fetchall('SELECT * FROM '.tablename('modules_bindings')." WHERE module='community' AND entry='menu'");
			
			foreach ($bindings as $binding) {
				if(empty($binding['call'])){
					$allmenus[] = array(
						'do' => $binding['do'],
						'state' => $binding['state'],
						'title' => $binding['title'],
						'url' => $this->createWebUrl($binding['do']) //todo: 
					);
				} else {
					$call = $binding['call'];
					if (method_exists($this, $call)) {
						$callmenus = $this->$call();
						if (!empty($callmenus)) {
							foreach ($callmenus as $mymenu) {
								if(empty($mymenu['url'])){
									continue;
								}
								$url_result = parse_url($mymenu['url']);
								if (empty($url_result) || empty($url_result['query'])) {
									continue;
								}
								$query = $url_result['query'];
								$qarr = explode('&', $query);
								$qarr2 = array();
								foreach ($qarr as $ele) {
									$r2 = explode('=', $ele);
									$qarr2[$r2[0]] = $r2[1];
								}
								unset($qarr2['act']);
								unset($qarr2['name']);
								unset($qarr2['act']);
								$menu = array();
								$menu['do'] = $qarr2['do'];
								unset($qarr2['do']);
								ksort($qarr2);
								$menu['state'] = http_build_query($qarr2);
								$menu['module'] = 'community';
								$menu['memberid'] = $member['uid'];
								$menu['acid'] = $_W['weid'];
								$menu['title'] = $mymenu['title'];
								$menu['url'] = $this->createWebUrl($menu['do']).'&'.$menu['state'];
								$allmenus[] = $menu;
							}
						}
					}
				}
			}
			unset($menu);
		}
		include $this->template('manager');
	}
	
	public function doMobileHome(){
		global $_GPC, $_W;
		$member = $this->checkAuth();
		$title = $this->getMobileTitle('home');
		include $this->template('home');
	}
	
	public function doMobileRegister() {
		global $_GPC, $_W;
		
		$title = $this->getMobileTitle('register');
		$member = $this->getMember();
		
		if (!empty($member)) {
			header('Location: '.$this->createMobileUrl('home'));
			exit;
		}
		$step = intval($_GPC['step']);
		$step = !empty($step) ? $step : (!empty($this->module['config']['verifymobile']) ? 1 : 2);
		if ($step == 1 && empty($this->module['config']['verifymobile'])) {
			$step = 2;
		}
		$verify = pdo_fetch("SELECT * FROM ".tablename('community_verifycode')." WHERE weid = '{$_W['weid']}' AND openid = :openid", array(':openid' => $_W['fans']['from_user']));
		
		if (!empty($verify['status'])) {
			$step = '2';
		}
		if (checksubmit('submit')) {
			$data = array(
				'weid' => $_W['weid'],
				'regionid' => intval($_GPC['regionid']),
				'openid'=>$_W['fans']['from_user'],
				'type'=> intval($_GPC['type']),
				'realname'=>$_GPC['realname'],
				'mobile' => $_GPC['mobile'],
				'address' => $_GPC['address'],
				'remark' => $_GPC['remark'],
				'createtime'=>TIMESTAMP,
			);
			
			if (!empty($verify['status'])) {
				$data['mobile'] = $verify['mobile'];
			}
			if ($step == '1') {
				$verify = pdo_fetch("SELECT * FROM ".tablename('community_verifycode')." WHERE weid = '{$_W['weid']}' AND openid = :openid AND mobile = :mobile", array(':openid' => $_W['fans']['from_user'], ':mobile' => $data['mobile']));
				if ($verify['verifycode'] != $_GPC['verifycode']) {
					message('抱歉，您输入的验证码错误，请您重新输入或是重新发送验证短信！');
				}
				pdo_update('community_verifycode', array('status' => 1), array('id' => $verify['id']));
				$step = 2;
			} else {
				if (empty($data['realname'])) {
					message('请填写您的真实姓名！', $this->createMobileUrl('register', array('step' => 2)), 'error');
				}
				if (empty($data['mobile'])) {
					message('请填写您的手机号码！', $this->createMobileUrl('register', array('step' => 2)), 'error');
				}
				$region = $this->getRegion(intval($_GPC['regionid']));
				if (empty($region)) {
					message('您选择的小区不属于该物业，请重新选择！', $this->createMobileUrl('register'), 'error');
				}
				$data['regionname'] = $region['title'];
				
				if (empty($data['address'])) {
					message('请填写您的楼栋信息！',referer());
				}
				$data['status'] = !empty($this->module['config']['verify']) ? 0 : 1;
				
				if (!empty($this->module['config']['verifymobile'])) {
					$verify = pdo_fetch("SELECT * FROM ".tablename('community_verifycode')." WHERE openid = :openid AND mobile = :mobile", array(':openid' => $_W['fans']['from_user'], ':mobile' => $data['mobile']));
					if ($verify['verifycode'] != $_GPC['verifycode']) {
						message('抱歉，您输入的验证码错误，请您重新输入或是重新发送验证短信！');
					}
				}
				$mobileexists = pdo_fetch("SELECT mobile FROM ".tablename('community_member')." WHERE mobile = :mobile AND weid = '{$_W['weid']}'", array(':mobile' => $data['mobile']));
				if (!empty($mobileexists)) {
					message('抱歉，此手机已经被注册，请您更换手机号或是联系管理员！');
				}
				
				pdo_insert('community_member', $data);
				if (!empty($this->module['config']['verify'])) {
					message('成功绑定业主信息，请您等待物业审核您提交的信息，方可使用提供的物业相关服务！', $this->createMobileUrl('home'), 'success');
				} else {
					message('成功绑定业主信息，您可以正常使用提供的相关服务！', $this->createMobileUrl('home'));
				}
			}
		}
		$sql = "SELECT * FROM ".tablename('community_member').' WHERE `weid`=:weid AND `openid`=:openid AND `regionid`=:regionid';
		$paras = array(
			':weid' => $_W['weid'],
			':openid' => $_W['fans']['from_user'],
			':regionid' => $_GPC['regionid'],
		);
		
		$types = $this->getMemberTypes();
		$regions = $this->getRegions();
		
		include $this->template('register');
	}
	
	public function doMobileHelp() {
		global $_W, $_GPC;
		$member = $this->checkAuth();
		$title = $this->getMobileTitle('help');
		include $this->template('help');
	}
	
	public function doMobileVerifyCode() {
		global $_W, $_GPC;
		$result = array('status' => 0, 'message' => '');
		$mobile = $_GPC['mobile'];
		if (empty($mobile)) {
			$result['message'] = '请输入您的手机号码';
			message($result, '', 'ajax');
		}
		$member = pdo_fetch("SELECT id, mobile FROM ".tablename('community_member')." WHERE mobile = :mobile AND weid = '{$_W['weid']}'", array(':mobile' => $mobile));
		if (!empty($member['id'])) {
			$result['message'] = '您输入的手机号已经被注册，请更换手机号或是联系管理员。';
			message($result, '', 'ajax');
		}
		//删除昨天的验证码
		pdo_query("DELETE FROM ".tablename('community_verifycode')." WHERE createtime < '".(strtotime(date('Y-m-d', $_W['timestamp'])) - 1)."'");
		
		$verify = pdo_fetch("SELECT * FROM ".tablename('community_verifycode')." WHERE openid = :openid AND mobile = :mobile AND weid = '{$_W['weid']}'", array(':openid' => $_W['fans']['from_user'], ':mobile' => $mobile));
		if ($verify['total'] >= 5) {
			$result['message'] = '您已经达到今日发送验证码的最大次数，请耐心等待短信。';
			message($result, '', 'ajax');
		}
		if (empty($verify)) {
			$verify = array(
				'weid' => $_W['weid'],
				'openid' => $_W['fans']['from_user'],
				'mobile' => $_GPC['mobile'],
				'verifycode' => random(6, 1),
				'createtime' => TIMESTAMP,
			);
			pdo_insert('community_verifycode', $verify);
		} else {
			pdo_update('community_verifycode', array('total' => $verify['total'] + 1), array('id' => $verify['id']));
		}
		//$url = "http://sms.bechtech.cn/Api/send/data/json?accesskey=2214&secretkey=68045f42e39265ed9c23511aadd5872f90953de2&mobile=%s&content=%s";
		$url = "http://sms.bechtech.cn/Api/send/data/json?accesskey=&secretkey=&mobile=%s&content=%s";
		$content = '尊敬的业主您好，您正在申请使用'.($this->module['config']['title'] ? $this->module['config']['title'] : '您小区的').'物业服务，您的注册验证码为：'.$verify['verifycode'].'。为了保证您的帐户安全，验证短信请勿转发给其他人。【世纪联城】';
		$url = sprintf($url, $mobile, urlencode($content));
		$response = ihttp_request($url);
		if (is_error($response)) {
			$result['message'] = '发送验证码失败，请联系物业相关人员';
			message($result, '', 'ajax');
		}
		$response['content'] = json_decode($response['content'], true);
		if ($response['content']['result'] != '01') {
			$result['message'] = '发送验证码失败，请联系物业相关人员';
			message($result, '', 'ajax');
		}
		$result['status'] = '1';
		message($result, '', 'ajax');
	}
	
	/**
	 * 服务端权限验证
	 * 返回当前所辖 $regionid
	 * */
	private function checkWebAuth() {
		global $_W, $_GPC;
		$regionid = intval($_GPC['regionid']);
		if (empty($regionid)) {
			message('请先选择您要操作的小区！');
		}
		if (!in_array($regionid, $this->getRegionIdsByUid())) {
			message('您没有操作此小区权限,选择您要操作的小区！');
		}
		return $regionid;
	}
	
	/**
	 * 客户端权限验证
	 * 返回当前用户信息 $member (community_member)
	 * */
	private function checkAuth() {
		global $_W, $_GPC;
	
		$member = $this->getMember();
		
		if (empty($member)) {
			$this->do = 'register';
			$this->doMobileRegister();
			exit;
		}
		if (empty($member['status'])) {
			$title = $this->getMobileTitle('home');
			include $this->template('home');
			exit;
		}
		return $member;
	}
	
	/**
	 * 微小区中获取当前粉丝信息.
	 * id, type, realname, mobile, regionname, address, remark, status, createtime
	 * */
	private function getMember($openid = '') {
		global  $_W, $_GPC;
		$paras = array();
		$paras[':weid'] = $_W['weid'];
		if (!empty($openid)){
			$paras[':openid'] = $openid;
		}else {
			$paras[':openid'] = $_W['fans']['from_user'];
		}
		$member = pdo_fetch("SELECT * FROM ".tablename('community_member')." WHERE `weid` = :weid AND `openid` = :openid", $paras);
		return $member;
	}
	
	/**
	 * 获取当前公众号下所有小区
	 * */
	private function getRegions(){
		global $_W, $_GPC;
		$sql = "SELECT * FROM ".tablename('community_region').' WHERE `weid`=:weid';
		$regions = pdo_fetchall($sql, array(':weid'=>$_W['weid']), 'id');
		return $regions;
	}
	
	/**
	 * 获取当前公众号指定小区
	 * */
	private function getRegion($id){
		global $_W, $_GPC;
		$sql = "SELECT * FROM ".tablename('community_region')." WHERE `weid`=:weid AND `id`=:id ";
		$region = pdo_fetch($sql, array(':id'=>$id, ':weid'=>$_W['weid']));
		return $region;
	}
	
	/**
	 * 获取指定用户或当前用户所辖的小区 id
	 * */
	private function getRegionIdsByUid($uid = 0){
		global  $_W, $_GPC;
		if (empty($uid)){
			$uid = $_W['uid'];
		}
		$regions = pdo_fetchcolumn("SELECT regions FROM ".tablename('community_manager')." WHERE `weid`=:weid AND `uid`=:uid", array(':weid'=>$_W['weid'],':uid'=>$_W['uid']));
		if (empty($regions)) {
			return array();
		}
		$regions = iunserializer($regions);
		if (empty($regions)) {
			return array();
		} else {
			return $regions;
		} 
	}
	
	/**
	 * 快递公司设置
	 * */
	public function doWebExpressCompany(){
		global $_W, $_GPC;
		
		$operation = empty($_GPC['op']) ? 'display' : $_GPC['op'];
		$operation = in_array($operation, array('display', 'delete')) ? $operation : 'display';
		
		if($operation == 'display') {
			
			if(checksubmit()){
				$olds = $_GPC['olds'];
				$names = $_GPC['names'];
				$codes = $_GPC['codes'];
				$regs = $_GPC['regs'];
				$notes = $_GPC['notes'];
				if(empty($olds) && empty($names)){
					message('没有任何快递公司,请添加.');
				}
				if (!empty($olds)) {
					foreach ($olds as $old) {
						$id = $old['id'];
						unset($old['id']);
						pdo_update(community_express_company, $old, array('id'=>$id));
					}
				}
				$news = array();
				for ($i = 0; $i < count($names); $i++) {
					$news[] = array(
						'name' => $names[$i],
						'code' => $codes[$i],
						'reg' => $regs[$i],
						'note' => $notes[$i],
						'weid' => $_W['weid']
					);
				}
				foreach ($news as $new) {
					pdo_insert(community_express_company, $new);
				}
				message('保存成功.','','success');
			}
			$list = pdo_fetchall('SELECT * FROM '.tablename(community_express_company)." WHERE `weid`=:weid", array(':weid'=>$_W['weid']));
			
		} elseif($operation == 'delete') {
			
			$id = intval($_GPC['id']);
			$express = pdo_fetch('SELECT * FROM '.tablename(community_express_company)." WHERE weid=:weid AND id=:id", array(':weid'=>$_W['weid'], ':id'=>$id));
			if(empty($express)){
				message('快递未找到或已删除.', referer(), 'success');
			}
			$count = pdo_fetchcolumn('SELECT COUNT(*) FROM '.tablename(community_express_fee)." WHERE weid=:weid AND code=:code", array(':weid'=>$_W['weid'], ':code'=>$express['code']));
			if($count > 0){
				message('无法删除快递,快递费用未删除.');
			}
			pdo_delete(community_express_company, array('id'=>intval($_GPC['id']), 'weid'=>$_W['weid']));
			message('删除成功.', referer(), 'success');
		}
		include $this->template('expressCompany');
	}
	
	public function doWebExpressFee(){
		global $_W, $_GPC;
		
		$operation = empty($_GPC['op']) ? 'display' : $_GPC['op'];
		$operation = in_array($operation, array('display', 'delete')) ? $operation : 'display';
		
		if($operation == 'display') {
			
			if(checksubmit()){
				
				$id = intval($_GPC['id']);
				$express = pdo_fetch('SELECT * FROM '.tablename(community_express_company).' WHERE weid=:weid AND id=:id', array(':weid'=>$_W['weid'], ':id'=>$id));
				if (empty($express)) {
					message('未找到指定快递公司.');
				}
				$fees = $_GPC['fees'];
				if (!empty($fees)) {
					foreach ($fees as $key => &$fee) {
						pdo_update(community_express_fee, $fee, array('id'=>$key));
					}
				}
				$content = $_GPC['content'];
				if (!empty($content)) {
					$pieces = explode("\r\n", $content);
					$index = 0;
					$newfees = array();
					foreach ($pieces as $piece) {
						if (empty($piece)) {
							message('批量新增快递费用失败,第'.$index.'行有错误.');
						}
						$values = explode(':', $piece);
						if (count($values) != 6) {
							message('批量新增快递费用失败,第'.$index.'行有错误.');
						}
						$newfee = array();
						$newfee['province'] = trim($values[0]);
						$newfee['city'] = trim($values[1]);
						$newfee['district'] = trim($values[2]);
						$newfee['pricefirst'] = floatval($values[3]);
						$newfee['weightfirst'] = floatval($values[4]);
						$newfee['priceaddition'] = floatval($values[5]);
						$newfees[] = $newfee;
						
						$index++;
					}
					
					foreach ($newfees as $newfee) {
						$newfee['weid'] = $_W['weid'];
						$newfee['code'] = $express['code'];
						pdo_insert(community_express_fee, $newfee);
					};
				}
				message('快递费用编辑成功.',referer(),'success');
				
			} else {
				$id = intval($_GPC['id']);
				$express = pdo_fetch('SELECT * FROM '.tablename(community_express_company).' WHERE weid=:weid AND id=:id', array(':weid'=>$_W['weid'], ':id'=>$id));
				if (empty($express)) {
					message('未找到指定快递.',referer());;
				}
				$list = pdo_fetchall('SELECT * FROM '.tablename(community_express_fee).' WHERE weid=:weid AND code=:code', array(':weid'=>$_W['weid'],':code'=>$express['code']));
			}
			
		} elseif($operation == 'delete') {
			$id = intval($_GPC['id']);
			pdo_delete(community_express_fee, array('id'=>intval($_GPC['id']), 'weid'=>$_W['weid']));
			message('删除成功.', referer(), 'success');
		}
		
		include $this->template('expressFee');
	}
	
	/**
	 * 快递后台
	 * */
	public function doWebExpress(){
		global $_W, $_GPC;
		
		$regionid = $this->checkWebAuth();
		
		$operation = empty($_GPC['op']) ? 'display' : $_GPC['op'];
		$operation = in_array($operation, array('display', 'post', 'delete')) ? $operation : 'display';
		
		if($operation == 'display') {
				
			$coms = $this->getExpressCompanys();
			$types = $this->getExpressTypes();
			$statuses = $this->getExpressStatuses();
			
			$starttime = empty($_GPC['starttime']) ? strtotime('-1 month') : strtotime($_GPC['starttime']);
			$endtime = empty($_GPC['endtime']) ? TIMESTAMP : strtotime($_GPC['endtime']) + 86399;
				
			$pindex = max(1, intval($_GPC['page']));
			$psize = 50;
		
			$where = " WHERE weid=:weid AND regionid=:regionid AND createtime>=:starttime AND createtime<:endtime";
			$paras = array(
				':weid' => $_W['weid'],
				':regionid' => $regionid,
				':starttime' => $starttime,
				':endtime' => $endtime,
			);
			
			$status = intval($_GPC['status']);
			if (!empty($status)) {
				$where .= " AND status=:status";
				$paras[':status'] = $status;
			}
			
			$sn = $_GPC['sn'];
			if(!empty($sn)){
				$where .= " AND sn=:sn";
				$paras[':sn'] = $sn;
			}
			
			
			$list = pdo_fetchall("SELECT * FROM ".tablename(community_express_order)." $where ORDER BY status ASC, id DESC LIMIT ".($pindex - 1) * $psize.','.$psize, $paras);
			$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename(community_express_order). $where , $paras);
			$pager = pagination($total, $pindex, $psize);
			
		} elseif($operation == 'delete') {
			
			$id = intval($_GPC['id']);
			if(!empty($id)){
				$item = $this->getExpressOrder($id);
			}
			if (empty($item)) {
				message('抱歉，查看的服务需求不存在或是已经被删除！');
			}
			if ($item != 3) {
				message('未签收订单无法删除。');
			}
			
			pdo_delete(community_express_order, array('id'=>intval($_GPC['id']),'weid'=>$_W['weid']));
			message('删除成功.', referer(), 'success');
			
		} elseif ($operation == 'post') {
				
			if (checksubmit('submit')) {
				
				$id = intval($_GPC['id']);
				$item = pdo_fetch('SELECT * FROM '.tablename(community_express_order)." WHERE id=:id AND weid=:weid AND regionid=:regionid",array(':id'=>$id,':weid'=>$_W['weid'],':regionid'=>$regionid));
				if(empty($item)){
					message('未找到指定快递单', $this->createWebUrl('express', array('op'=>'display','regionid'=>$regionid,'weid'=>$_W['weid'])));
				} else {
					
					$data = array(
						'status' => intval($_GPC['status']),
						'realname'=>$_GPC['realname'],
						'mobile' => $_GPC['mobile'],
						'type' => $_GPC['type'],
						'detail' => $_GPC['detail'],
						'province' => $_GPC['province'],
						'city' => $_GPC['city'],
						'district' => $_GPC['district'],
						'express_code' => $_GPC['express_code'],
						'weight_estimate' => $_GPC['weight_estimate'],
						'price_estimate' => $_GPC['price_estimate'],
						'sn' => $_GPC['sn'],
					);
					pdo_update(community_express_order, $data, array('id' => $id,'weid'=>$_W['weid'], 'regionid'=>$regionid));
					message('更新成功！', $this->createWebUrl('express', array('op'=>'display','regionid'=>$regionid,'weid'=>$_W['weid'])), 'success');
				}
				
			} else {
				
				$id = intval($_GPC['id']);
				if(!empty($id)){
					$item = pdo_fetch('SELECT * FROM '.tablename(community_express_order)." WHERE id=:id AND weid=:weid",array(':id'=>$id,':weid'=>$_W['weid']));
				}
				
				if (empty($item)) {
					message('未找到指定快递单');
				}
				
				$member = $this->getMember($item['openid']);
				
				$coms = $this->getExpressCompanys();
				$types = $this->getExpressTypes();
			}
		}
		include $this->template('express');
	}
	
	/**
	 * 获取快递
	 * */
	private function getExpressOrder($id, $openid=''){
		global $_W, $_GPC;
		$where = ' WHERE id=:id AND weid=:weid AND openid=:openid';
		$params = array(':id'=>$id,':weid'=>$_W['weid']);
		if (!empty($openid)) {
			$params[':openid'] = $openid;
		} else {
			$params[':openid'] = $_W['fans']['from_user'];
		}
		$item = pdo_fetch("SELECT * FROM ".tablename(community_express_order).$where, $params);
		return $item;
	}

	/**
	 * 快递前台
	 * */
	public function doMobileExpress(){
		global $_W, $_GPC;
		
		checkauth();
		
		$title = $this->getMobileTitle('express');
		
		$op = empty($_GPC['op']) ? 'post' : $_GPC['op'];
		$op = in_array($op, array('display', 'post', 'delete')) ? $op : 'post';
		
		if($op == 'display') {
			
			$this->checkAuth();
			
			$coms = $this->getExpressCompanys();
			$types = $this->getExpressTypes();
			$statuses = $this->getExpressStatuses();
			
			$starttime = empty($_GPC['starttime']) ? strtotime('-1 month') : strtotime($_GPC['starttime']);
			$endtime = empty($_GPC['endtime']) ? TIMESTAMP : strtotime($_GPC['endtime']) + 86399;
				
			$pindex = max(1, intval($_GPC['page']));
			$psize = 50;
		
			$where = " WHERE weid=:weid AND openid=:openid AND createtime>=:starttime AND createtime<:endtime";
			$paras = array(
				':weid' => $_W['weid'],
				':openid' => $_W['fans']['from_user'],
				':starttime' => $starttime,
				':endtime' => $endtime,
			);
			
			$status = intval($_GPC['status']);
			if (!empty($status)) {
				$where .= ' AND status=:status';
				$paras[':status'] = $status;
			}
			
			$type = intval($_GPC['type']);
			if (!empty($type)) {
				$where .= ' AND type=:type';
				$paras[':type'] = $type;
			}
			
			$list = pdo_fetchall("SELECT * FROM ".tablename(community_express_order)." $where ORDER BY status ASC, id DESC LIMIT ".($pindex - 1) * $psize.','.$psize, $paras);
			$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename(community_express_order). $where , $paras);
			$pager = pagination($total, $pindex, $psize);
			
			include $this->template('express');
			
		} elseif($op == 'delete') {
			
			$id = intval($_GPC['id']);
			if(!empty($id)){
				$item = $this->getExpressOrder($id);
			}
			if (empty($item)) {
				message('抱歉，查看的服务需求不存在或是已经被删除！');
			}
			if ($item != 3) {
				message('未签收订单无法删除。');
			}
			
			pdo_delete(community_express_order, array('id'=>intval($_GPC['id']),'weid'=>$_W['weid'], 'openid'=>$_W['fans']['from_user']));
			message('删除成功.', referer(), 'success');
			
		} elseif ($op == 'post') {
				
			if (checksubmit('submit')) {
				
				$id = intval($_GPC['id']);
				$item = pdo_fetch('SELECT * FROM '.tablename(community_express_order)." WHERE id=:id AND weid=:weid AND openid=:openid",array(':id'=>$id,':weid'=>$_W['weid'],'openid'=>$_W['fans']['from_user']));
				if(empty($item)){
					
					$data = array(
						'weid' => $_W['weid'],
						'openid' => $_W['fans']['from_user'],
						'createtime' => TIMESTAMP,
						'status' => 1,
						'realname'=>$_GPC['realname'],
						'mobile' => $_GPC['mobile'],
						'type' => $_GPC['type'],
						'detail' => $_GPC['detail'],
						'province' => $_GPC['province'],
						'city' => $_GPC['cityt'],
						'district' => $_GPC['district'],
						'express_code' => $_GPC['express_code'],
						'weight_estimate' => $_GPC['weight_estimate'],
						'price_estimate' => $_GPC['price_estimate'],
					);
					
					pdo_insert(community_express_order, $data);
					message('快递预定成功！', $this->createMobileUrl('express', array('op'=>'display')), 'success');
				} else {
					
					$data = array(
						'status' => 1,
						'realname'=>$_GPC['realname'],
						'mobile' => $_GPC['mobile'],
						'type' => $_GPC['type'],
						'detail' => $_GPC['detail'],
						'province' => $_GPC['province'],
						'city' => $_GPC['city'],
						'district' => $_GPC['district'],
						'express_code' => $_GPC['express_code'],
						'weight_estimate' => $_GPC['weight_estimate'],
						'price_estimate' => $_GPC['price_estimate'],
					);
					pdo_update(community_express_order, $data, array('id' => $id,'weid'=>$_W['weid']));
					message('更新成功！', $this->createMobileUrl('express',array('op'=>'display')), 'success');
				}
				
			} else {
				
				$id = intval($_GPC['id']);
				if(!empty($id)){
					$item = $this->getExpressOrder($id);
				}
				
				$coms = $this->getExpressCompanys();
				$types = $this->getExpressTypes();
				if (empty($item)) {
					$member = $this->getMember();
					if(!empty($member)){
						$item['realname'] = $member['realname'];
						$item['mobile'] = $member['mobile'];
					}
				}
			}
			include $this->template('express');
		}
	}
	
	/**
	 * 微站端获取指定省市县的快递费用. ajax
	 * */
	public function doMobileExpressFee(){
		global $_W, $_GPC;
		
		$params = array();
		$params[':weid'] = $_W['weid'];
		$params[':province'] = $_GPC['province'];
		$params[':city'] = $_GPC['city'];
		$params[':district'] = $_GPC['district'];
		$params[':code'] = $_GPC['code'];

		$fee = pdo_fetch("SELECT * FROM ".tablename(community_express_fee)." WHERE weid=:weid AND code=:code AND province=:province AND city=:city AND district=:district ", $params);
		if (empty($fee)) {
			unset($params[':district']);
			$fee = pdo_fetch("SELECT * FROM ".tablename(community_express_fee)." WHERE weid=:weid AND code=:code AND province=:province AND city=:city ", $params);
		}
		if (empty($fee)) {
			unset($params[':city']);
			$fee = pdo_fetch("SELECT * FROM ".tablename(community_express_fee)." WHERE weid=:weid AND code=:code AND province=:province ", $params);
		}
		if (empty($fee)) {
			$result = array('errmsg'=>'当前快递不到达');
			exit(json_encode($result));
		}
		$weight = floatval($_GPC['weight']);
		$weight = ceil($weight);
		if ($weight <= 0){
			$weight = 1;
		}
		$price = $fee['pricefirst'] + ($weight - $fee['weightfirst']) * $fee['priceaddition'];
		$result = array('price' => $price);
		exit(json_encode($result));
	}
	
	private function getExpressCompanys(){
		global $_W,$_GPC;
		$coms = pdo_fetchall('SELECT * FROM '.tablename(community_express_company)." WHERE weid=:weid", array(':weid'=>$_W['weid']), 'code');
		return $coms;
	}
	
	private function getExpressTypes(){
		return array('1'=>'文件','2'=>'数码电器','3'=>'食品','4'=>'液体','5'=>'其他');
	}
	
	private function getExpressType($id){
		//return $this->getExpressTypes()[$id];
	}
	
	private function getExpressStatuses(){
		return array('1'=>'预约','2'=>'揽收','3'=>'签收');
	}
	
	private function getExpressStatus($status){
		//return $this->getExpressStatuses()[$status];
	}
}