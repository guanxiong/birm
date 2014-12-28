<?php
/**
 * 
 *
 * 
 */
defined('IN_IA') or exit('Access Denied');
class ChaxunModuleSite extends WeModuleSite {
	public function getProfileTiles() {

	}
	public function getHomeTiles() {
	}
	public function doWebDisplay(){
		global $_GPC, $_W;
		$op = !empty($_GPC['op'])?$_GPC['op']:'display';
		$id = intval($_GPC['id']);
		if($op == 'post'){
			if (!empty($id)) {
				$sql = "select * from".tablename('chax')."where weid='{$_W['weid']}' and id=".$id;
				$item = pdo_fetch($sql);
			}
			$data = array(
					'weid' 		   => $_W['weid'],
					'title'        => $_GPC['title'],
					'url'          => $_GPC['url'],
					'displayorder' => intval($_GPC['displayorder']),
					'status'       => intval($_GPC['status']),
				);
			if ($_W['ispost']) {
				if (!empty($_FILES['fileicon']['tmp_name'])) {
						file_delete($_GPC['topPicture-old']);
						$upload = file_upload($_FILES['fileicon']);
						if (is_error($upload)) {
							message($upload['message'], '', 'error');
						}
						$data['fileicon'] = $upload['path'];
					}
				if(empty($id)){
					pdo_insert('chax',$data);
				}else{
					pdo_update('chax',$data,array('id' => $id));
				}
				message('更新成功',referer(),'success');
			}
			
		}elseif ($op == 'display') {
				$sql = "select * from ".tablename('chax')."where weid='{$_W['weid']}'";
				$row = pdo_fetchall($sql);
		}elseif ($op == 'delete') {
			pdo_delete('chax',array('id' => $id,'weid' => $_W['weid']));
			message('删除成功',referer(),'success');
		}elseif ($op == 'edit') {
			$status = intval($_GPC['status']);
			if ( $status == 1) {
				$sql = "update".tablename('chax')." set status = 0 where weid= '{$_W['weid']}' and id=".$id;
				pdo_query($sql);
			}else{
				$sql = "update".tablename('chax')." set status = 1 where weid= '{$_W['weid']}' and id=".$id;
				pdo_query($sql);
			}
			message('更新成功',referer(),'success');
		}
		include $this->template('display');
	}
	public function doMobileIndex(){
		global $_W,$_GPC;
		$sql = "select * from".tablename('chax')."where weid='{$_W['weid']}' and status =1";
		$navs = pdo_fetchall($sql);
		include $this->template('index');
	}
}

?>