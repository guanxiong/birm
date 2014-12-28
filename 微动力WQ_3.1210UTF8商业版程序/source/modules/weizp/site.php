<?php
defined('IN_IA') or exit('Access Denied');

class WeizpModuleSite extends WeModuleSite {
		
		public function doMobileindex(){
			global $_W,$_GPC;
			checkauth();
			$rid = $_GPC['rid'];
			$from_user = $_W['fans']['from_user'];
			if(empty($rid)){
				exit('活动不存在');
			}
			if(checksubmit('submit')){
				$updata = array(
					'author'=>$_GPC['author'],
					'phone'=>$_GPC['phone'],
					'qq'=>$_GPC['qq']
				);
				pdo_update('weizp_author',$updata,array('from_user'=>$from_user));
				message('提交成功，正在跳转至作品上传页面',$this->createMobileUrl('submitzp',array('rid'=>$rid)),'success');
			}
			$reply = pdo_fetch("SELECT * FROM ".tablename('weizp_reply')." WHERE rid = :rid", array(':rid' => $rid));
			$author = pdo_fetch("SELECT * FROM ".tablename('weizp_author')." WHERE from_user = :from_user AND rid = :rid", array(':rid' => $rid,':from_user'=>$from_user));
			include $this->template('index');	
		}
		public function doMobilesubmitzp(){
			global $_W,$_GPC;
			checkauth();
			$rid = $_GPC['rid'];
			$from_user = $_W['fans']['from_user'];
			if(checksubmit('submit')){
				
				$insert = array(
					'rid'=>$rid,
					'weid'=>$_W['weid'],
					'from_user'=>$from_user,
					'title'=>$_GPC['title'],
					'description'=>$_GPC['content'],
					'createtime'=>time(),
					'images'=>serialize($_GPC['picIds'])
				);
				if(pdo_insert('weizp_album',$insert)){
					$res = array('errCode'=>0,'message'=>'作品提交成功，谢谢参与','jumpURL'=>'/'.$this->createMobileUrl('submitzp',array('rid'=>$rid)));
				}else{
					$res = array('errCode'=>1,'message'=>'作品提交失败，请尝试重新提交','jumpURL'=>'/'.$this->createMobileUrl('submitzp',array('rid'=>$rid)));
				}
				exit(json_encode($res));
			}
			include $this->template('submit');	
		}
		public function doMobileimgupload(){
			global $_W,$_GPC;	
			if(!empty($_GPC['pic'])){
				$is = pdo_insert('weizp_images',array('file'=>$_GPC['pic']));
				$id = pdo_insertid();
				if(empty($is)){
				 exit(json_encode(array(
					  'errCode'=>1,
					  'message'=>'上传出现错误',
					  'data'=>array('id'=>$_GPC['t'],'picId'=>$id)
				  )));

				}else{
				  exit(json_encode(array(
					  'errCode'=>0,
					  'message'=>'作品上传成功',
					  'data'=>array('id'=>$_GPC['id'],'picId'=>$id)
				  )));
				}
			}
			
		}
		public function doMobileMy(){
			global $_W,$_GPC;
			checkauth();
			$rid = $_GPC['rid'];
			$from_user = $_W['fans']['from_user'];
			$list = pdo_fetchall("SELECT * FROM ".tablename('weizp_album')." WHERE rid=:rid AND from_user=:from_user",array('rid'=>$rid,'from_user'=>$from_user));
			foreach($list as $k=>$v){
				foreach($v as $sk=>$sv){
				  if($sk=='images'){
				  $tmp = unserialize($sv);
				  $images = array();
					  foreach($tmp as $ssv){
						  $images[]['id'] = $ssv;
						  $images[]['file'] = pdo_fetchcolumn("SELECT file FROM ".tablename('weizp_images'). "WHERE id='{$ssv}'");
					  }
				  $sv = $images;
				  }
				  $v[$sk] = $sv;
				}
				$list[$k] = $v;
			}
			//var_dump($list);
			
			include $this->template('my');
		}

		

}