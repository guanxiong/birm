<?php
/**
 * 
 *
 * 
 */
defined('IN_IA') or exit('Access Denied');
class PanoramaModuleSite extends WeModuleSite {
	public $manage = 'panorama_manage';
	public function getProfileTiles() {
	}
	public function getHomeTiles() {
	}
	public function doWebPanoramaset(){
		global $_W,$_GPC;
		if ($_W['ispost']){
			
		}else{
			$sql  = "select * from ".tablename($this->manage)."where weid='{$_W['weid']}'";
			$list = pdo_fetchall($sql);
		}
		include $this->template('index');
	}
	public function doWebSet(){
		global $_W,$_GPC;
		$op = $_GPC['op'];
		$data = array(
					'weid'      => $_W['weid'],
					'title'     => $_GPC['title'],
					'frontpic'  => $_GPC['frontpic'],
					'rightpic'  => $_GPC['rightpic'],
					'backpic'   => $_GPC['backpic'],
					'leftpic'   => $_GPC['leftpic'],
					'toppic'    => $_GPC['toppic'],
					'bottompic' => $_GPC['bottompic'],
					'keyword'   => $_GPC['keyword'],
					'time'      => $_W['timestamp'] ,
					'taxis'     => $_GPC['taxis'],
					'intro'     => $_GPC['intro'],
					);
		if($op == 'post'){
				if($_W['ispost']){
						pdo_insert($this->manage,$data);
						message('添加成功',referer(),'success');
					}else{
						$set              =array();
						$baseUrl          ='./source/modules/panorama/img/';
						$set['frontpic']  =$baseUrl.'0.jpg';
						$set['rightpic']  =$baseUrl.'1.jpg';
						$set['backpic']   =$baseUrl.'2.jpg';
						$set['leftpic']   =$baseUrl.'3.jpg';
						$set['toppic']    =$baseUrl.'4.jpg';
						$set['bottompic'] =$baseUrl.'5.jpg';		
				}
		}elseif($op == 'update'){
			$id  = $_GPC['id'];
			$sql = "select * from".tablename($this->manage)."where id=".$id." and weid='{$_W['weid']}'";
			//print_r($sql);
			$set = pdo_fetch($sql);
			if ($_W['ispost']) {
				pdo_update($this->manage, $data, array('id' => $id));
				message('修改成功',referer(),'success');
			}

		}
		include $this->template('set');
	}
	public function doWebDelete(){
		global $_W,$_GPC;
		$id = $_GPC['id'];
		if(!empty($id)){
			pdo_delete($this->manage,array('weid' => $_W['weid'],'id' => $id));
			message('删除成功',referer(),'success');
		}
	}
	public function doMobileIndex(){
		global $_GPC,$_W;
		$sql  = "select * from".tablename($this->manage)."where weid='{$_W['weid']}'";
		$list = pdo_fetchall($sql);
		include $this->template('index');
	}
	public function doMobileItem(){
		global $_W,$_GPC;
		$id   = $_GPC['id'];
		$sql  = "select * from".tablename($this->manage)."where id=".$id." and weid='{$_W['weid']}'";
		$item = pdo_fetch($sql);
		//print_r($item);
		if (isset($_GPC['o'])){
			
			header("Content-type: text/xml");
			echo '<?xml version="1.0" encoding="UTF-8"?><panorama id="" hideabout="1"><view fovmode="0" pannorth="0"><start pan="5.5" fov="80" tilt="1.5"/><min pan="0" fov="80" tilt="-90"/><max pan="360" fov="80" tilt="90"/></view><userdata title="" datetime="2013:05:23 21:01:02" description="" copyright="" tags="" author="" source="" comment="" info="" longitude="" latitude=""/><hotspots width="180" height="20" wordwrap="1"><label width="180" backgroundalpha="1" enabled="1" height="20" backgroundcolor="0xffffff" bordercolor="0x000000" border="1" textcolor="0x000000" background="1" borderalpha="1" borderradius="1" wordwrap="1" textalpha="1"/><polystyle mode="0" backgroundalpha="0.2509803921568627" backgroundcolor="0x0000ff" bordercolor="0x0000ff" borderalpha="1"/></hotspots><media/><input tilesize="700" tilescale="1.014285714285714" tile0url="'.$item['frontpic'].'" tile1url="'.$item['rightpic'].'" tile2url="'.$item['backpic'].'" tile3url="'.$item['leftpic'].'" tile4url="'.$item['toppic'].'" tile5url="'.$item['bottompic'].'"/><autorotate speed="0.200" nodedelay="0.00" startloaded="1" returntohorizon="0.000" delay="5.00"/><control simulatemass="1" lockedmouse="0" lockedkeyboard="0" dblclickfullscreen="0" invertwheel="0" lockedwheel="0" invertcontrol="1" speedwheel="1" sensitivity="8"/></panorama>';

		}else {
			include $this->template('item');
		}
	}
}