<?php
/**
 * 游戏管家模块微站定义
 *
 * @author Yoby
 * @url 
 */
defined('IN_IA') or exit('Access Denied');
function get_timelinegame($pubtime) {
    $time = time ();
    /** 如果不是同一年 */
    if (idate ( 'Y', $time ) != idate ( 'Y', $pubtime )) {
        return date ( 'Y年m月d日', $pubtime );
    }
 
    /** 以下操作同一年的日期 */
    $seconds = $time - $pubtime;
    $days = idate ( 'z', $time ) - idate ( 'z', $pubtime );
 
    /** 如果是同一天 */
    if ($days == 0) {
        /** 如果是一小时内 */
        if ($seconds < 3600) {
            /** 如果是一分钟内 */
            if ($seconds < 60) {
                if (3 > $seconds) {
                    return '刚刚';
                } else {
                    return $seconds . '秒前';
                }
            }
            return intval ( $seconds / 60 ) . '分钟前';
        }
        return idate ( 'H', $time ) - idate ( 'H', $pubtime ) . '小时前';
    }
 
    /** 如果是昨天 */
    if ($days == 1) {
        return '昨天 ' . date ( 'H:i', $pubtime );
    }
 
    /** 如果是前天 */
    if ($days == 2) {
        return '前天 ' . date ( 'H:i', $pubtime );
    }
 
    /** 如果是7天内 */
    if ($days < 7) {
        return $days. '天前';
    }
 
    /** 超过7天 */
    return date ( 'n月j日 H:i', $pubtime );
}
 
class YobygameModuleSite extends WeModuleSite {

	public function doMobileFm() {
		//这个操作被定义用来呈现 功能封面
		global $_W,$_GPC;
		
		$src= $_W['siteroot'].'source/modules/yobygame/images/';
		$weixin = "搜索[". $_W['account']['name']."]关注我";
		$listt = pdo_fetchall("SELECT * FROM ".tablename('game')." WHERE weid = '{$_W['weid']}' and ist=1 and isok=1 ORDER BY id DESC");
		$gamen = intval($this->module['config']['gamen']);
		$gamew = $this->module['config']['gamew'];
		include $this->template('game');
	}

	public function doWebGamet() {
		//这个操作被定义用来呈现 管理中心导航菜单
		global $_W,$_GPC;
		
		$op = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
		if('post' == $op){
			$id = intval($_GPC['id']);
			if(!empty($id)){
				//查找是否存在
				$item = pdo_fetch("SELECT * FROM ".tablename('game_category')." WHERE id = :id" , array(':id' => $id));
				if (empty($item)) {
					message('亲,数据不存在！', '', 'error');
				}
			}
			if(checksubmit('submit')){//检测是否post
				//验证
					if (empty($_GPC['title'])) {
					message('亲,分类名称不能为空!');
				}
				
				$weid = $_W['weid'];
				$title = $_GPC['title'];//分类名称
				
				
				$data = array(
					'weid'=>$weid,
					'title'=>$title,
					
					
					
				);
				if(empty($id)){
					pdo_insert('game_category', $data);//添加数据
				message('游戏分类添加成功！', $this->createWebUrl('gamet', array('op' => 'display')), 'success');
				}else{
					
					pdo_update('game_category', $data, array('id' => $id));
				message('游戏分类更新成功！', $this->createWebUrl('gamet', array('op' => 'display')), 'success');
				}
			
			}else{
				include $this->template('t');
			}
		}else if('del' == $op){//删除数据
					$id = intval($_GPC['id']);
			$row = pdo_fetch("SELECT id FROM ".tablename('game_category')." WHERE id = :id", array(':id' => $id));
			if (empty($row)) {
				message('亲,分类'.$id.'不存在,不要乱动哦！');
			}
			pdo_delete('game_category', array('id' => $id));
			message('删除成功！', referer(), 'success');	
		}else if('display'==$op){
				$pindex = max(1, intval($_GPC['page']));
			//$num = $_GPC['num'];
			$psize =20;//每页显示
			$condition = '';
		
			
			$list = pdo_fetchall("SELECT * FROM ".tablename('game_category')." WHERE weid = '{$_W['weid']}' $condition ORDER BY id DESC LIMIT ".($pindex - 1) * $psize.','.$psize);//分页
			$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('game_category') . " WHERE weid = '{$_W['weid']}'");
			$pager = pagination($total, $pindex, $psize);
			include $this->template('t');
	
	}	
	}
	public function doWebGamem() {
		//这个操作被定义用来呈现 管理中心导航菜单
global $_W,$_GPC;
		
		$op = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
		if('post' == $op){
			$id = intval($_GPC['id']);
			if(!empty($id)){
				//查找是否存在
				$item = pdo_fetch("SELECT * FROM ".tablename('game')." WHERE id = :id" , array(':id' => $id));
				if (empty($item)) {
					message('亲,数据不存在！', '', 'error');
				}
			}
			if(checksubmit('submit')){//检测是否post
				//验证
					if (empty($_GPC['title'])) {
					message('亲,游戏名称不能为空!');
				}
					if (empty($_GPC['url'])) {
					message('亲,游戏地址不能为空!');
				}
					if (empty($_GPC['img'])) {
					message('亲,游戏图标不能为空!');
				}
				$weid = $_W['weid'];
				$title = $_GPC['title'];//分类名称
				$url=$_GPC["url"];
				$img=$_GPC['img'];
				$createtime = time();//创建时间
				$isok = $_GPC['isok'];
				$desc =$_GPC['desc'];
				$category = $_GPC['category'];
				$ist = $_GPC['ist'];//是否推荐
				
				$data = array(
					'weid'=>$weid,
					'title'=>$title,
					'url'=>$url,
					'img'=>$img,
					'createtime'=>$createtime,
					'isok'=>$isok,
					'desc'=>$desc,
					'category'=>$category,
					'ist'=>$ist,
	
					
				);
				if(empty($id)){
					pdo_insert('game', $data);//添加数据
				message('游戏添加成功！', $this->createWebUrl('gamem', array('op' => 'display')), 'success');
				}else{
					unset($data['createtime']);
					pdo_update('game', $data, array('id' => $id));
				message('游戏更新成功！', $this->createWebUrl('gamem', array('op' => 'display')), 'success');
				}
			
			}else{
				include $this->template('m');
			}
		}else if('del' == $op){//删除数据
					$id = intval($_GPC['id']);
			$row = pdo_fetch("SELECT id FROM ".tablename('game')." WHERE id = :id", array(':id' => $id));
			if (empty($row)) {
				message('亲,幻灯片'.$id.'不存在,不要乱动哦！');
			}
			pdo_delete('game', array('id' => $id));
			message('删除成功！', referer(), 'success');	
		}else if('display'==$op){
				$pindex = max(1, intval($_GPC['page']));
			//$num = $_GPC['num'];
			$psize =20;//每页显示
			$condition = '';
		
			
			$list = pdo_fetchall("SELECT * FROM ".tablename('game')." WHERE weid = '{$_W['weid']}' $condition ORDER BY id DESC LIMIT ".($pindex - 1) * $psize.','.$psize);//分页
			$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('game') . " WHERE weid = '{$_W['weid']}'");
			$pager = pagination($total, $pindex, $psize);
			include $this->template('m');
	
	}
	}
	public function doWebGameh() {
		//这个操作被定义用来呈现 管理中心导航菜单
global $_W,$_GPC;
		
		$op = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
		if('post' == $op){
			$id = intval($_GPC['id']);
			if(!empty($id)){
				//查找是否存在
				$item = pdo_fetch("SELECT * FROM ".tablename('game_img')." WHERE id = :id" , array(':id' => $id));
				if (empty($item)) {
					message('亲,数据不存在！', '', 'error');
				}
			}
			if(checksubmit('submit')){//检测是否post
				//验证
					if (empty($_GPC['img'])) {
					message('亲,幻灯片不能为空!');
				}
				
				$weid = $_W['weid'];
				$title = $_GPC['title'];//分类名称
				$url=$_GPC["url"];
				$img=$_GPC['img'];
				
				$data = array(
					'weid'=>$weid,
					'title'=>$title,
					'url'=>$url,
					'img'=>$img,
					
					
					
				);
				if(empty($id)){
					pdo_insert('game_img', $data);//添加数据
				message('幻灯片添加成功！', $this->createWebUrl('gameh', array('op' => 'display')), 'success');
				}else{
					
					pdo_update('game_img', $data, array('id' => $id));
				message('幻灯片更新成功！', $this->createWebUrl('gameh', array('op' => 'display')), 'success');
				}
			
			}else{
				include $this->template('h');
			}
		}else if('del' == $op){//删除数据
					$id = intval($_GPC['id']);
			$row = pdo_fetch("SELECT id FROM ".tablename('game_img')." WHERE id = :id", array(':id' => $id));
			if (empty($row)) {
				message('亲,幻灯片'.$id.'不存在,不要乱动哦！');
			}
			pdo_delete('game_img', array('id' => $id));
			message('删除成功！', referer(), 'success');	
		}else if('display'==$op){
				$pindex = max(1, intval($_GPC['page']));
			//$num = $_GPC['num'];
			$psize =20;//每页显示
			$condition = '';
		
			
			$list = pdo_fetchall("SELECT * FROM ".tablename('game_img')." WHERE weid = '{$_W['weid']}' $condition ORDER BY id DESC LIMIT ".($pindex - 1) * $psize.','.$psize);//分页
			$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('game_img') . " WHERE weid = '{$_W['weid']}'");
			$pager = pagination($total, $pindex, $psize);
			include $this->template('h');
	
	}
	}
	public function doMobileDh() {
		//这个操作被定义用来呈现 微站首页导航图标
		global $_W,$_GPC;
		
		$src= $_W['siteroot'].'source/modules/yobygame/images/';
		$weixin = "搜索\"". $_W['account']['name']."\"关注我";
		$listt = pdo_fetchall("SELECT * FROM ".tablename('game')." WHERE weid = '{$_W['weid']}' and ist=1 and isok=1 ORDER BY id DESC");
		$gamen = intval($this->module['config']['gamen']);
		$gamew = $this->module['config']['gamew'];
		include $this->template('game');
	}

public function doMobileAjaxn(){//统计点击次数
	global $_GPC,$_W;
		$id = intval($_GPC['id']);
		
			pdo_query("update ".tablename('game')." set num=num+1 where id=:id", array(':id' => $id));
			
		
}
}