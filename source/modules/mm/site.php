<?php
/**
 * 美女报时模块微站定义
 *
 * @author Yoby
 * @url 
 */
defined('IN_IA') or exit('Access Denied');

class MmModuleSite extends WeModuleSite {

	public function doWebMmphoto() {
		global $_W,$_GPC;
		$op = !empty($_GPC['op']) ? $_GPC['op'] : 'display';//操作
		if('post' == $op){//添加/更新照片
			$id = intval($_GPC['id']);//获得照片id
			if(!empty($id)){//修改图片
				//查找是否存在
				$item = pdo_fetch("SELECT * FROM ".tablename('mm')." WHERE id = :id" , array(':id' => $id));
				if (empty($item)) {
					message('亲,数据不存在！', '', 'error');
				}
			}
			if(checksubmit('submit')){//检测是否post
				//验证
					if (empty($_GPC['title'])) {
					message('亲,姓名不能为空');
				}
					if (empty($_GPC['img'])) {
					message('亲,记得上图哦');
				}
				if (empty($_GPC['tou'])) {
					message('亲,传头像');
				}
				$weid = $_W['weid'];
				$title = $_GPC['title'];//姓名
				$address= $_GPC['address'];//地址
				$paddress = $_GPC['paddress'];//拍照地址
				$age = $_GPC['age'];//年龄
				$work = $_GPC['work'];//工作
				$weixin = $_GPC['weixin'];//点赞次数
				$qq = $_GPC['qq'];//排行榜
				$tou = $_GPC['tou'];//被踩次数
				$img = $_GPC['img'];//图片地址
				$txt = $_GPC['txt'];//附加文本信息
				$createtime = time();//创建时间
				$data = array(
					'weid'=>$weid,
					'title'=>$title,
					'address'=>$address,
					'paddress'=>$paddress,
					'age'=>$age,
					'weixin'=>$weixin,
					'work'=>$work,
					'tou'=>$tou,
					'qq'=>$qq,
					'txt'=>$txt,
					'img'=>$img,
					'createtime'=>$createtime,
					
				);
				if(empty($id)){
					pdo_insert('mm', $data);//添加数据
				message('图片添加成功！', $this->createWebUrl('mmphoto', array('op' => 'display')), 'success');
				}else{
					unset($data['createtime']);
					pdo_update('mm', $data, array('id' => $id));
				message('图片更新成功！', $this->createWebUrl('mmphoto', array('op' => 'display')), 'success');
				}
				
				
				
				
			
				
			}else{
				include $this->template('mmphoto');
			}
		}else if('del' == $op){//删除数据
					$id = intval($_GPC['id']);
			$row = pdo_fetch("SELECT id FROM ".tablename('mm')." WHERE id = :id", array(':id' => $id));
			if (empty($row)) {
				message('亲,你的照片'.$id.'不存在,不要乱动哦！');
			}
			pdo_delete('mm', array('id' => $id));
			message('删除成功！', referer(), 'success');	
		}else if('display'==$op){
			$pindex = max(1, intval($_GPC['page']));
			$psize =20;//每页显示
			
			$condition = '';
			$list = pdo_fetchall("SELECT * FROM ".tablename('mm')." WHERE weid = '{$_W['weid']}' $condition ORDER BY id DESC LIMIT ".($pindex - 1) * $psize.','.$psize);//分页
			$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('mm') . " WHERE weid = '{$_W['weid']}'");
			$pager = pagination($total, $pindex, $psize);
			include $this->template('mmphoto');
		}
			
			
			
			
			
			
	}
	public function doMobileDetail() {//手机显示图片详细信息
		global $_GPC, $_W;
		$id = intval($_GPC['id']);
		$sql = "SELECT * FROM " . tablename('mm') . " WHERE `id`=:id";
		$detail = pdo_fetch($sql, array(':id'=>$id));
		$detail = istripslashes($detail);
		$detail['thumb'] = $_W['attachurl'] . trim($detail['thumb'], '/');
		$title = $detail['title'];
		include $this->template('detail');
	}
	
	public function doMobileZan(){//点赞功能
		global $_GPC,$_W;
		$id = intval($_GPC['id']);
		if($_W['isajax'] ){			
			$data = array('zan'=>intval($_GPC['zan'])+1);
pdo_update('mm', $data, array('id' => $id));//更新点赞		
			$detail = pdo_fetch("select zan from ".tablename('mm')."  where id=".$id);
			echo json_encode(array('info'=>'0','zan'=>$detail['zan']));
		}
	}
public function doMobileTopn(){//排行榜功能
    global $_GPC,$_W;
    $pindex = max(1, intval($_GPC['page']));
			$psize =5;//每页显示
			$condition = '';
			$list = pdo_fetchall("SELECT id,tou,title,address,zan,tops FROM ".tablename('mm')." WHERE weid = '{$_W['weid']}' $condition ORDER BY zan DESC LIMIT ".($pindex - 1) * $psize.','.$psize);//分页
			$detail['thumb'] = $_W['attachurl'] . trim($detail['thumb'], '/');
			$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('mm') . " WHERE weid = '{$_W['weid']}'");
			$pager = pagination($total, $pindex, $psize);
			include $this->template('topn');
} 
}