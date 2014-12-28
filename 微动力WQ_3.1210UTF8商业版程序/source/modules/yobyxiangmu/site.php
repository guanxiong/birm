<?php
/**
 * 项目简易管理模块微站定义
 *
 * @author yoby
 * @url 
 */
defined('IN_IA') or exit('Access Denied');

class YobyxiangmuModuleSite extends WeModuleSite {

	public function doWebKehuziliao() {//客户资料管理方法
	global $_W,$_GPC;
		$op = !empty($_GPC['op']) ? $_GPC['op'] : 'display';//操作
		if('post' == $op){
			$id = intval($_GPC['id']);
			if(!empty($id)){
				//查找是否存在
				$item = pdo_fetch("SELECT * FROM ".tablename('yoby_kehu')." WHERE id = :id" , array(':id' => $id));
				if (empty($item)) {
					message('亲,数据不存在！', '', 'error');
				}
			}
			if(checksubmit('submit')){//检测是否post
				//验证
					if (empty($_GPC['username'])) {
					message('亲,姓名不能为空');
				}
				if (empty($_GPC['qq'])) {
					message('亲,QQ不能为空');
				}
				$weid = $_W['weid'];
				$username = $_GPC['username'];//姓名
				$address= $_GPC['address'];//地址
				$mobile = $_GPC['mobile'];//手机或电话
				$num = $_GPC['num'];//合作次数
				$isfinish = $_GPC['isfinish'];//合做结束否
				$weixin = $_GPC['weixin'];//点赞次数
				$qq = $_GPC['qq'];//排行榜
				$mode = $_GPC['mode'];//合作方式
				$logo= $_GPC['logo'];//图片地址
				$txt = $_GPC['txt'];//附加文本信息
				$createtime = time();//创建时间
				$data = array(
					'weid'=>$weid,
					'username'=>$username,
					'address'=>$address,
					'mobile'=>$mobile,
					'num'=>$num,
					'weixin'=>$weixin,
					'isfinish'=>$isfinish,
					'mode'=>$mode,
					'qq'=>$qq,
					'txt'=>$txt,
					'logo'=>$logo,
					'createtime'=>$createtime,
					
				);
				if(empty($id)){
					pdo_insert('yoby_kehu', $data);//添加数据
				message('客户资料添加成功！', $this->createWebUrl('kehuziliao', array('op' => 'display')), 'success');
				}else{
					//unset($data['createtime']);
					pdo_update('yoby_kehu', $data, array('id' => $id));
				message('客户资料更新成功！', $this->createWebUrl('kehuziliao', array('op' => 'display')), 'success');
				}
				
				
				
				
			
				
			}else{
				include $this->template('kehuziliao');
			}
		}else if('del' == $op){//删除数据
					$id = intval($_GPC['id']);
			$row = pdo_fetch("SELECT id FROM ".tablename('yoby_kehu')." WHERE id = :id", array(':id' => $id));
			if (empty($row)) {
				message('亲,客户资料'.$id.'不存在,不要乱动哦！');
			}
			pdo_delete('yoby_kehu', array('id' => $id));
			message('删除成功！', referer(), 'success');	
		}else if('display'==$op){
			$pindex = max(1, intval($_GPC['page']));
			$psize =20;//每页显示
			
			$condition = '';
			$list = pdo_fetchall("SELECT * FROM ".tablename('yoby_kehu')." WHERE weid = '{$_W['weid']}' $condition ORDER BY id DESC LIMIT ".($pindex - 1) * $psize.','.$psize);//分页
			$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('yoby_kehu') . " WHERE weid = '{$_W['weid']}'");
			$pager = pagination($total, $pindex, $psize);
			include $this->template('kehuziliao');
	
	}
	}
	public function doWebXiangmuguanli() {//项目管理
	global $_W,$_GPC;
		$op = !empty($_GPC['op']) ? $_GPC['op'] : 'display';//操作
		if('post' == $op){
			$id = intval($_GPC['id']);
			if(!empty($id)){
				//查找是否存在
				$item = pdo_fetch("SELECT * FROM ".tablename('yoby_xiangmu')." WHERE id = :id" , array(':id' => $id));
				if (empty($item)) {
					message('亲,数据不存在！', '', 'error');
				}
			}
			if(checksubmit('submit')){//检测是否post
				//验证
					if (empty($_GPC['appname'])) {
					message('亲,项目名称不能为空');
				}
				$weid = $_W['weid'];
				$appname = $_GPC['appname'];//项目名称
				$appen= $_GPC['appen'];//英文
				$appxuqiu = $_GPC['appxuqiu'];//手机或电话
				$txt = $_GPC['txt'];//附加文本信息
				$qq = $_GPC['qq'];//附加文本信息
				$createtime = time();//创建时间
				$vtime = strtotime($_GPC['vtime']);//交接时间
				$rmb = $_GPC['rmb'];//价格
				$data = array(
					'weid'=>$weid,
					'appname'=>$appname,
					'appen'=>$appen,
					'appxuqiu'=>$appxuqiu,
					'txt'=>$txt,
					'qq'=>$qq,
					'createtime'=>$createtime,
					'vtime'=>$vtime,
					'rmb'=>$rmb,
					
				);
				if(empty($id)){
					pdo_insert('yoby_xiangmu', $data);//添加数据
				message('项目需求添加成功！', $this->createWebUrl('xiangmuguanli', array('op' => 'display')), 'success');
				}else{
					//unset($data['createtime']);
					pdo_update('yoby_xiangmu', $data, array('id' => $id));
				message('项目需求更新成功！', $this->createWebUrl('xiangmuguanli', array('op' => 'display')), 'success');
				}
				
				
				
				
			
				
			}else{
				include $this->template('xiangmuguanli');
			}
		}else if('del' == $op){//删除数据
					$id = intval($_GPC['id']);
			$row = pdo_fetch("SELECT id FROM ".tablename('yoby_xiangmu')." WHERE id = :id", array(':id' => $id));
			if (empty($row)) {
				message('亲,项目需求'.$id.'不存在,不要乱动哦！');
			}
			pdo_delete('yoby_xiangmu', array('id' => $id));
			message('删除成功！', referer(), 'success');	
		}else if('display'==$op){
			$pindex = max(1, intval($_GPC['page']));
			$psize =20;//每页显示
			
			$condition = '';
			$list = pdo_fetchall("SELECT * FROM ".tablename('yoby_xiangmu')." WHERE weid = '{$_W['weid']}' $condition ORDER BY id DESC LIMIT ".($pindex - 1) * $psize.','.$psize);//分页
			$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('yoby_xiangmu') . " WHERE weid = '{$_W['weid']}'");
			$pager = pagination($total, $pindex, $psize);
			include $this->template('xiangmuguanli');
	
	}		
	}
	public function doWebShouhouguanli() {
		//这个操作被定义用来呈现 管理中心导航菜单
	}

}