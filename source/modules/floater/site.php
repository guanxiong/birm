<?php
/**
 * 通用表单模块订阅器
 *
 * @author Godietion Koo
 * @url http://beidoulbs.com/
 */
defined('IN_IA') or exit('Access Denied');

class FloaterModuleSite extends WeModuleSite {
	//移动端访问   
    public function doMobileWish() {
    	global $_GPC,$_W;
		$pindex = max(1, intval($_GPC['page']));
		$psize = 8;		
    	$list = pdo_fetchall("SELECT * FROM ".tablename('wish')."  ORDER BY id DESC LIMIT ".($pindex - 1) * $psize.','.$psize);
		$total = pdo_fetchcolumn('SELECT COUNT(1) FROM ' . tablename('wish'));
		$pager = pagination($total, $pindex, $psize);
        include $this->template('wish');
    }
    public function doMobileMakeAWish() {
    	global $_W,$_GPC;
    	if(checksubmit('submit')){
    		if (get_magic_quotes_gpc()) {
				$data=array(
    				'nickname'=>trim($_GPC['nickname']),
    				'qq'=>trim($_GPC['qq']),
    				'wishtype'=>trim($_GPC['wishtype']),	
    				'native'=>trim($_GPC['native']),		
    				'msg'=>trim($_GPC['msg']),	
					'userid'=>trim($_GPC['userid']),					
    			);
			} else {
				$data=array(
    				'nickname'=>addslashes(trim($_GPC['nickname'])),
    				'nickname'=>addslashes(trim($_GPC['nickname'])),
    				'qq'=>addslashes(trim($_GPC['qq'])),
    				'wishtype'=>addslashes(trim($_GPC['wishtype'])),	
    				'native'=>addslashes(trim($_GPC['native'])),		
    				'msg'=>addslashes(trim($_GPC['msg'])),	
					'userid'=>addslashes(trim($_GPC['userid'])),				
    			);
			}
			if(pdo_insert("wish", $data)){
    			header("location:".create_url('mobile/module',array('do' => 'post','weid'=>empty($_GPC['__weid'])?$_GPC['weid']:$_GPC['__weid'],'userid'=>$_GPC['userid'],'name' => 'floater')));    
			}
    	}else {
        	include $this->template('addwish');
    	}
    }
    public function doMobileShow() {
    	global $_W,$_GPC;
    	$row=pdo_fetch("SELECT * FROM ".tablename('wish')." WHERE id = :id", array(':id' => $_GPC['wishid']));
        include $this->template('show');
    } 
    public function doMobilePost(){
    	include $this->template('post');
    } 
}