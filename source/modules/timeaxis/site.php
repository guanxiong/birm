<?php
/**
 * 时光轴模块
 *
 * @author topone4tvs
 * @url 
 */
defined('IN_IA') or exit('Access Denied');

class TimeaxisModuleSite extends WeModuleSite {
	public function doMobileIndex(){
		global $_W, $_GPC;
		$timeid = $_GPC['tid'];
		$axisinf = pdo_fetch('SELECT title,bgcol,bgimg,items FROM '.tablename('timeaxis').' WHERE id=:tid AND weid=:wid',array(':tid'=>$timeid,':wid'=>$_W['weid']));
		$axisinf['items'] = unserialize($axisinf['items']);
		include $this->template('index');
	}

	public function doWebDeltime(){
		global $_W, $_GPC;
		$rid = pdo_fetch('SELECT rid FROM '.tablename('timeaxis_rep').' WHERE axisid='.$_GPC['id']);
		if(!empty($rid)){
			pdo_delete('rule_keyword','rid='.$rid['rid']);
			pdo_delete('rule','id='.$rid['rid']);
		}		
		pdo_delete('timeaxis','id='.$_GPC['id']);
		message('活动删除成功',referer(),'success');
	}

	public function doWebManage(){
		global $_W, $_GPC;
		$data = array();
		if($_W['ispost']){
			if(empty($_GPC['items'])){
				message('创建点内容吧！',referer(),'error',1);
			}
			$data['weid'] = $_W['weid'];
			$data['title'] = $_GPC['title'];
			$data['bgimg'] = $_GPC['bgimg'];
			$data['bgcol'] = $_GPC['bgcol'];
			$data['time'] = time();
			foreach ($_GPC['items']['type'] as $k => $val) {
				$data['items'][] = array(
						'type' => $_GPC['items']['type'][$k],
						'title' => $_GPC['items']['title'][$k],
						'direct' => $_GPC['items']['direct'][$k],
						'detail' => $_GPC['items']['detail'][$k]
					);
			}
			$data['items'] = serialize($data['items']);
			if(empty($_GPC['id'])){
				pdo_insert('timeaxis',$data);
			} else {
				pdo_update('timeaxis',$data,array('id'=>$_GPC['id']));
			}
			//WeUtility::logging('tips','items:'.$data['items']);
			message('活动创建成功','','success',1);
		}
		$timeinf = array();
		if(!empty($_GPC['id'])){
			$timeinf = pdo_fetch('SELECT * FROM '.tablename('timeaxis').' WHERE id=:id',array(':id'=>$_GPC['id']));
			$timeinf['items'] = unserialize($timeinf['items']);
			$timeinf['url'] = $this->createMobileUrl('index', array('weid' => $_W['weid'], 'tid' => $_GPC['id']));
		}
		include $this->template('manage');
	}

	public function doWebList(){
		global $_W, $_GPC;
		checklogin();
		$id = intval($_GPC['id']);
		if (checksubmit('delete') && !empty($_GPC['select'])) {
			pdo_delete('timeaxis', " id  IN  ('".implode("','", $_GPC['select'])."')");
			message('删除成功！', create_url('site/module', array('do' => 'list', 'name' => 'timeaxis')));
		}
		$pindex = max(1, intval($_GPC['page']));
		$psize = 10;
		$timelist = pdo_fetchall("SELECT * FROM ".tablename('timeaxis')." WHERE weid=:wid ORDER BY id ASC LIMIT ".($pindex - 1) * $psize.",{$psize}",array(':wid'=>$_W['weid']));
		//print_r($list);
		if (!empty($timelist)) {
			$total = pdo_fetchcolumn('SELECT COUNT(*) FROM '.tablename('timeaxis'));
			$pager = pagination($total, $pindex, $psize);
		}
		include $this->template('list');
	}

	public function doWebTimemake(){
		global $_W, $_GPC;
	}

	public function getAxisTitles(){
		global $_W;
		$axis = pdo_fetchall("SELECT id, title FROM ".tablename('timeaxis')." WHERE weid = '{$_W['weid']}'");
		if (!empty($axis)) {
			foreach ($axis as $row) {
				$urls[] = array('title' => $row['title'], 'url' => $this->createMobileUrl('index', array('tid' => $row['id'])));
			}
			return $urls;
		}
	}
}