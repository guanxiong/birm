<?php
/**
 * 图片魔方模块微站定义
 *
 * @author 智策技术
 * @url 
 */
defined('IN_IA') or exit('Access Denied');

class Izc_lightboxModuleSite extends WeModuleSite {

	public function doMobileshow() {
		global $_GPC, $_W;
		$weid = intval($_W['weid']);
		if(empty($weid)){
			message('抱歉，参数错误！','', 'error');              
		}
		$id=intval($_GPC['id']);
		if(empty($id)){
			message('抱歉，参数错误！','', 'error');              
		}
		$list = pdo_fetch("SELECT * FROM".tablename('izc_lightbox_list')." WHERE `id`=:id  AND `weid`=:weid",array(':weid'=>$weid,':id'=>$id));
		if(empty($list)){
			$list = pdo_fetch("SELECT * FROM".tablename('izc_lightbox_list')." WHERE `weid`=$weid ORDER BY `id` DESC ");
			if(empty($list)){
				message('不存在');
			}
		}
		$items = pdo_fetchall("SELECT * FROM".tablename('izc_lightbox_items')." WHERE `boxid`=:boxid ORDER BY `index`" ,array(':boxid'=>$list['id']));
		include $this->template('index');
	}
	
	public function doWebmanager(){
		global $_W,$_GPC;
		$weid = $_W['account']['weid'];
		$foo = !empty($_GPC['foo']) ? $_GPC['foo'] : 'display';
		$list=array(
				'cover'=>$_W['siteroot'].'source/modules/izc_lightbox/default/00.jpg',
				'thumb'=>$_W['siteroot'].'source/modules/izc_lightbox/default/01.jpg',
				'share_title'=>'快来感受一下我们的华丽吧',
				'cover_title'=>'擦起来 还等什么!!!',
				'share_txt'=>'感受一段新的体验',
				'share_thumb'=>$_W['siteroot'].'source/modules/izc_lightbox/default/000.png',
				'share_cover'=>$_W['siteroot'].'source/modules/izc_lightbox/default/11.jpg',
				'share_button'=>$_W['siteroot'].'source/modules/izc_lightbox/resource/fx.jpg',
				'share_tips'=>$_W['siteroot'].'source/modules/izc_lightbox/resource/19/img/weixin-share-guide.png',
				);
		if($foo=='create'){//创建相册&修改相册属性信息
			$id  = intval($_GPC['id']);
			 
			if(!empty($id)){
				$sql = "SELECT * FROM ".tablename('izc_lightbox_list')." WHERE `id`=$id";
				$list = pdo_fetch($sql);
				if(empty($list)){
					message('参数错误 相册不存在或已被删除','','error');
				}
			}
			if(checksubmit()){
				if (empty($_GPC['reply_title'])) {
						message('回复文字不能为空');
				}
			
				if (empty($_GPC['title'])) {
						message('请输入相册名称！');
				}
				$data = array(
					'weid' =>$_W['account']['weid'],
					'title'=>$_GPC['title'],
					'cover'=>$_GPC['cover'],
					'cover_title'=>$_GPC['cover_title'],
					'thumb'=>$_GPC['thumb'],
					'music'=>$_GPC['music'],
					'share_title'=>$_GPC['share_title'],
					'share_cover'=>$_GPC['share_cover'],
					'share_thumb'=>$_GPC['share_thumb'],
					'share_button'=>$_GPC['share_button'],
					'share_txt'=>$_GPC['share_txt'],
					'share_tips'=>$_GPC['share_tips'],
					'reply_title'=>$_GPC['reply_title'],
					'reply_thumb'=>$_GPC['reply_thumb'],					
					'reply_description'=>$_GPC['reply_description'],
					);
				if(!empty($data['reply_thumb'])&&!strexists($data['reply_thumb'], 'http://')) {
					$data['reply_thumb'] = $_W['attachurl'] .$data['reply_thumb'];
				}
				if(!empty($data['cover'])&&!strexists($data['cover'], 'http://')) {
					$data['cover'] = $_W['attachurl'] .$data['cover'];
				}
				if(!empty($data['thumb'])&&!strexists($data['thumb'], 'http://')) {
					$data['thumb'] = $_W['attachurl'] .$data['thumb'];
				} 					
				if(!empty($data['share_thumb'])&&!strexists($data['share_thumb'], 'http://')) {
					$data['share_thumb'] = $_W['attachurl'] .$data['share_thumb'];
				} 					
				if(!empty($data['share_cover'])&&!strexists($data['share_cover'], 'http://')) {
					$data['share_cover'] = $_W['attachurl'] .$data['share_cover'];
				} 					
				if(!empty($data['share_button'])&&!strexists($data['share_button'], 'http://')) {
					$data['share_button'] = $_W['attachurl'] .$data['share_button'];
				} 					
				if(!empty($data['share_tips'])&&!strexists($data['share_tips'], 'http://')) {
					$data['share_tips'] = $_W['attachurl'] .$data['share_tips'];
				}  				
				if(empty($id)){
					pdo_insert('izc_lightbox_list', $data);
					message('新建成功',$this->createWeburl('manager',array('foo' => 'display')),'success');
 				}else{
					pdo_update('izc_lightbox_list',$data,array('id'=>$id));
					message('修改成功',$this->createWeburl('manager',array('foo' => 'display')),'success');
				}
			}
		}elseif($foo=='display'){
			$pindex = max(1, intval($_GPC['page']));
			$psize = 20;
			$condition = '';
			if (!empty($_GPC['keyword'])) {
				$condition .= " AND title LIKE '%{$_GPC['keyword']}%'";
			}
			$list = pdo_fetchall("SELECT * FROM ".tablename('izc_lightbox_list')." WHERE weid = '{$_W['weid']}' $condition ORDER BY  id DESC LIMIT ".($pindex - 1) * $psize.','.$psize);
			$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('izc_lightbox_list') . " WHERE weid = '{$_W['weid']}' $condition");
			$pager = pagination($total, $pindex, $psize);
		}elseif($foo='delete'){
			$id = intval($_GPC['id']);
			if(empty($id)){
				message('参数错误','','error');
			}
			$sql = "SELECT * FROM".tablename('izc_lightbox_list')."WHERE `id`=$id";
			$list = pdo_fetchall($sql);
			if(empty($list)){
				message('场景不存在','','error');
			}
			$items = pdo_fetchall("SELECT id, attachment FROM ".tablename('izc_lightbox_items')." WHERE boxid = :boxid", array(':boxid' => $id));
			if (!empty($items)) {
				foreach ($items as $row) {
					file_delete($row['attachment']);
				}
			}
			pdo_delete('izc_lightbox_list', array('id' => $id));
			pdo_delete('izc_lightbox_items', array('boxid' => $id));			
			message('删除成功！', referer(), 'success');
		}
		include $this->template('manager');
	}
	
	public function doWebQuery() {
		global $_W, $_GPC;
		$kwd = $_GPC['keyword'];
		$params = array();
		$params[':weid'] = $_W['weid'];
		if(!empty($kwd)){
			$sql = 'SELECT id,reply_title,reply_thumb,reply_description FROM ' . tablename("izc_lightbox_list") . ' WHERE `weid`=:weid AND `title` LIKE :title';
			$params[':reply_title'] = "%{$kwd}%";
		}else{
			$sql = 'SELECT id,reply_title,reply_thumb,reply_description FROM ' . tablename("izc_lightbox_list") . ' WHERE `weid`=:weid';
		}
		$ds = pdo_fetchall($sql, $params);
		 
		foreach($ds as $k=>$row) {
			$r = array();
			$r['title'] = $row['reply_title'];
			$r['description'] = $row['reply_description'];
			$r['thumb'] = $row['reply_thumb'];
			$r['mid'] = $row['id'];
			$ds[$k]['entry'] = $r;
		}
		include $this->template('query');
	}
	public function doWebitemer(){
		global $_W,$_GPC;
		$id =intval($_GPC['id']);
		$weid = $_W['account']['weid'];
		if($_GPC['foo']=='delete'){
				$picid = $_GPC['id'];
				pdo_delete('izc_lightbox_items',array('id' => $picid));
				file_delete($_GPC['attachment']);
				message('删除成功！',referer(),'success');
			}
		$list = pdo_fetch("SELECT * FROM".tablename('izc_lightbox_list')." WHERE `id`=:id",array(':id'=>$id));
		if(empty($list)){
			message('参数错误','','error');
		}
		$items = pdo_fetchall("SELECT * FROM".tablename('izc_lightbox_items')." WHERE `boxid`=:boxid AND `weid` = :weid ORDER BY `index`",array(':boxid'=>$id,':weid'=>$weid));
		if(checksubmit()){
				if (!empty($_GPC['attachment-new'])) {
					foreach ($_GPC['attachment-new'] as $k => $v) {
						$data = array(
							'weid' => $weid,
							'boxid' => intval($_GPC['id']),
							'attachment' => $_GPC['attachment-new'][$k],
 							'index'=>intval($_GPC['index-new'][$k]),
						);
						pdo_insert('izc_lightbox_items', $data);
					}
				}
				if(!empty($_GPC['attachment'])){
					foreach ($_GPC['attachment'] as $k => $v) {
						pdo_update('izc_lightbox_items',array('index'=>$_GPC['index'][$k]),array('id'=>$k));
					}
				}
				$itype = $_GPC['itype'];
				foreach ($itype as $k => $v) {
					switch ($v) {
						case 1:
						pdo_update('izc_lightbox_items' ,array('video'=>'','lat'=>0,'lng'=>0,'video_thumb'=>'','address'=>'','tel'=>'','wechat'=>'','map_thumb'=>''),array('id'=>$k,'weid'=>$_W['account']['weid']));
							break;
						case 2:
						pdo_update('izc_lightbox_items' ,array('video'=>'','lat'=>0,'lng'=>0,'video_thumb'=>'','address'=>'','tel'=>'','wechat'=>'','map_thumb'=>''),array('id'=>$k,'weid'=>$_W['account']['weid']));
							pdo_update('izc_lightbox_items' ,array('video'=>$_GPC['video'][$k],'video_thumb'=>$_GPC['video_thumb'][$k]),array('id'=>$k,'weid'=>$_W['account']['weid']));
							break;
						case 3:
						pdo_update('izc_lightbox_items' ,array('video'=>'','lat'=>0,'lng'=>0,'video_thumb'=>'','address'=>'','tel'=>'','wechat'=>'','map_thumb'=>''),array('id'=>$k,'weid'=>$_W['account']['weid']));
						pdo_update('izc_lightbox_items' ,array('lng'=>$_GPC['lng'][$k],'lat'=>$_GPC['lat'][$k],'address'=>$_GPC['address'][$k],'tel'=>$_GPC['tel'][$k],'wechat'=>$_GPC['wechat'][$k],'map_thumb'=>$_GPC['map_thumb'][$k]),array('id'=>$k,'weid'=>$_W['account']['weid']));
							break;
					}
				}
				
			message('操作成功',$this->createWeburl('itemer',array('id'=>$id)));
		}
		include $this->template('itemer');
	}
 
	public function doWebhelper(){
		global $_W;
		include $this->template('helper');
	}

	public function doWebeditsingler(){
		global $_W,$_GPC;
		$id = intval($_GPC['id']);
		$attachment = $_GPC['attachment'];
		if(empty($id)){
			message('参数错误,请联系管理员','','error');
		}
		$item=pdo_fetch('SELECT * FROM'.tablename('izc_lightbox_items')."WHERE `id`=$id");
		if(empty($item)){
			message('您要修改的场景内页不存在','','error');
		}
		if(checksubmit()){
			switch ($_GPC['itype']) {
				case 1:
				pdo_update('izc_lightbox_items' ,array('video'=>'','lat'=>0,'lng'=>0,'video_thumb'=>'','address'=>'','tel'=>'','wechat'=>'','map_thumb'=>''),array('id'=>$id,'weid'=>$_W['account']['weid']));
					break;
				case 2:
				pdo_update('izc_lightbox_items' ,array('video'=>'','lat'=>0,'lng'=>0,'video_thumb'=>'','address'=>'','tel'=>'','wechat'=>'','map_thumb'=>''),array('id'=>$id,'weid'=>$_W['account']['weid']));
					pdo_update('izc_lightbox_items' ,array('video'=>$_GPC['video'],'video_thumb'=>$_GPC['video_thumb']),array('id'=>$id,'weid'=>$_W['account']['weid']));
					break;
				case 3:
					pdo_update('izc_lightbox_items' ,array('video'=>'','lat'=>0,'lng'=>0,'video_thumb'=>'','address'=>'','tel'=>'','wechat'=>'','map_thumb'=>''),array('id'=>$id,'weid'=>$_W['account']['weid']));
					pdo_update('izc_lightbox_items' ,array('lng'=>$_GPC['lng'],'lat'=>$_GPC['lat'],'address'=>$_GPC['address1'],'tel'=>$_GPC['tel'],'wechat'=>$_GPC['wechat'],'map_thumb'=>$_GPC['map_thumb']),array('id'=>$id,'weid'=>$_W['account']['weid']));
					break;
					}
				message('操作成功',$this->createWeburl('itemer',array('id'=>$item['boxid'])),'success');
		}
		if($item['lng']==0||$item['lat']==0){
			$item['lng']=118.792496;
			$item['lat']=32.026304;
		}
		include $this->template('editer');
	}
}