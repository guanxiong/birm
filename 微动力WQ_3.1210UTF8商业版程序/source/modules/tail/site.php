<?php
/**
 * 小尾巴模块微站定义
 *
 * @author 超级无聊
 * @url 
 */
defined('IN_IA') or exit('Access Denied');
class TailModuleSite extends WeModuleSite {
	public function doWebSetting() {
		global $_GPC, $_W;
		$set = pdo_fetch("SELECT * FROM ".tablename('tail_set')." WHERE weid = :weid", array(':weid' => $_W['weid']));
		if(checksubmit()) {
			$insert=array(
				'isshow'=>intval($_GPC['isshow']),
				'itype'=>intval($_GPC['itype']),
			);
			if($set==false){
				$insert['weid']=$_W['weid'];
				$temp=pdo_insert('tail_set',$insert);
			}else{
				$temp=pdo_update('tail_set',$insert,array('id'=>$set['id']));
			}
			if($temp===false){
				message('数据保存失败');
			}else{
				message('数据保存成功',$this->createWebUrl('setting'));
			}
		}
		if($set==false){
			$set=array(
				'isshow'=>1,
				'itype'=>1,
			);
		}
		include $this->template('setting');	}
	public function doWebList() {
		global $_GPC, $_W;
		$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
		if($operation=='post'){
			$id=intval($_GPC['id']);
			if(!empty($id)){
				$item=pdo_fetch("SELECT * FROM ".tablename('tail_list')." WHERE weid = :weid AND id=:id", array(':weid' => $_W['weid'],':id'=>$id));
			}
			if(checksubmit()) {
				$insert=array(
					'weid'=>$_W['weid'],
					'displayorder'=>intval($_GPC['displayorder']),
					'module'=>$_GPC['module'],
				);
				if($_GPC['module']=='basic'){
					$insert['content']=$_GPC['basic-content'];
				}elseif($_GPC['module']=='news'){
					$insert['title']=$_GPC['title'];
					$insert['description'] = $_GPC['description'];
					$insert['thumb'] = $_GPC['thumb'];
					$insert['url'] = $_GPC['url'];
				}
			
				if(empty($id)){
					$temp=pdo_insert('tail_list',$insert);
				}else{
					unset($insert['weid']);
					$temp=pdo_update('tail_list',$insert,array('weid'=>$_W['weid'],'id'=>$id));
				}
				if($temp==false){
					message('数据保存失败');
				}else{
					message('数据保存成功',$this->createWebUrl('list'));
				}
			}
			if(empty($item)){
				$item=array(
					'displayorder'=>0,
					'module'=>'basic',
				);
			}
		} elseif ($operation == 'delete') {

			$id = intval($_GPC['id']);
			$row = pdo_fetch("SELECT id, thumb FROM ".tablename('tail_list')." WHERE id = :id AND weid=".$_W['weid']."", array(':id' => $id));
			if (empty($row)) {
				message('抱歉，小尾巴不存在或是已经被删除！');
			}
			
			pdo_delete('tail_list', array('id' => $id));
			message('删除成功！', referer(), 'success');
		}else{
			$pindex = max(1, intval($_GPC['page']));
			$psize = 20;
			$condition = '';
			$list = pdo_fetchall("SELECT * FROM ".tablename('tail_list')." WHERE weid = '{$_W['weid']}' $condition ORDER BY displayorder DESC, id DESC LIMIT ".($pindex - 1) * $psize.','.$psize);
			$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('tail_list') . " WHERE weid = '{$_W['weid']}' $condition");
			$pager = pagination($total, $pindex, $psize);
		
		}
		include $this->template('list');	
		

	}

}