<?php
/**
 * 微生活模块微站定义
 *
 * @author 微新星
 * @url 
 */
defined('IN_IA') or exit('Access Denied');

class MarketModuleSite extends WeModuleSite {

	public function doWebClasslist() {
		//这个操作被定义用来呈现 管理中心导航菜单
	}
	public function doMobileIndex() {
		//这个操作被定义用来呈现 微站首页导航图标
	}
	public function doMobileCardlist() {
		//这个操作被定义用来呈现 微站个人中心导航链接
	}
	public function doMobileclasslist(){
		global $_GPC,$_W;
		$id= intval($_GPC['id']);
		$classlist = pdo_fetchall("SELECT * FROM ".tablename('market_class')." WHERE  weid=:weid", array(':weid'=>$_W['weid']));
		foreach ($classlist as &$v){
			$v['shoplist']=pdo_fetchall("SELECT id,shopname,logo FROM ".tablename('market_business')." WHERE  weid=".$_W['weid']." and classid=".$v['id']."");
		}
		
		include $this->template('classlist');		
	}
	public function doMobilestory(){
		global $_GPC,$_W;
		$id= intval($_GPC['id']);
		$row = pdo_fetch("SELECT * FROM ".tablename('market_business')." WHERE id = :id and weid=:weid", array(':id' => $id,':weid'=>$_W['weid']));
		if($row==false){
			message('非法入口，参数错误');
		}
		include $this->template('story');		
	}
	public function doMobilefans(){
		global $_GPC,$_W;
		$id= intval($_GPC['id']);
		$from_user=$_W['fans']['from_user'];
		$row = pdo_fetch("SELECT * FROM ".tablename('market_business')." WHERE id = :id and weid=:weid", array(':id' => $id,':weid'=>$_W['weid']));
		$card_fans = pdo_fetch("SELECT * FROM ".tablename('market_fans')." WHERE id =".$id." and weid=".$_W['weid']." and from_user='".$from_user."'");

		if($row==false){
			message('非法入口，参数错误');
		}
		if(!empty($row['bgcustom'])){$row['background']=$row['bgcustom'];}
		include $this->template('fans');
	}
	public function doMobilecardinfo(){
		global $_GPC,$_W;
		$id= intval($_GPC['id']);
		$row = pdo_fetch("SELECT * FROM ".tablename('market_business')." WHERE id = :id and weid=:weid", array(':id' => $id,':weid'=>$_W['weid']));
		if($row==false){
			message('非法入口，参数错误');
		}
 		include $this->template('cardinfo');
	}
	public function doMobileprivilege(){
		global $_GPC,$_W;
		$id= intval($_GPC['id']);
		$volist = pdo_fetchall("SELECT * FROM ".tablename('market_privilege')." WHERE  weid=:weid and id=:id", array(':id'=>$id,':weid'=>$_W['weid']));		
		include $this->template('privilege');		
	}
	public function doMobiledetail (){
		global $_GPC,$_W;
		$id= intval($_GPC['id']);
		$pid= intval($_GPC['pid']);
		$row = pdo_fetch("SELECT * FROM ".tablename('market_privilege')." WHERE id = :id and weid=:weid and pid=:pid and starttime<".time()." and endtime>".time()."", array(':pid' => $pid,':id' => $id,':weid'=>$_W['weid']));
		include $this->template('detail');		
	}
	public function doMobilegetvip (){
		global $_GPC,$_W;
		$id= intval($_GPC['id']);
		$fid= intval($_GPC['fid']);
		$from_user= $_GPC['from_user'];
		//判断用户是否已经领卡了
		$card_fans = pdo_fetch("SELECT id,card_num FROM ".tablename('market_fans')." WHERE id =".$id." and weid=".$_W['weid']." and from_user='".$from_user."'");
		if($card_fans!=false){
			//已经领卡了
			$data=array(
				'error'=>1,
				'msg'=>'已经领过了',
			);
			die(json_encode($data));
		}
		$row = pdo_fetch("SELECT id,card_num FROM ".tablename('market_business')." WHERE id = :id and weid=:weid", array(':id' => $id,':weid'=>$_W['weid']));
		if($row==false){
			$data=array(
				'error'=>2,
				'msg'=>'商户信息不存在，或已经删除',
			);
		}else{
			if(empty($row['card_num'])){
				$row['card_num']='100000';
			}
			//更新用户卡信息
			//搜索是否存在此卡号
			for($i=0;$i<3;$i++){
				$row['card_num']=intval($row['card_num'])+1;
				$is=pdo_fetch("SELECT id,card_num FROM ".tablename('market_fans')." WHERE id =".$id." and weid=".$_W['weid']." and card_num=".$row['card_num']);
				if($is==false){
					//保存数据
					$insert=array(
						'id'=>$id,
						'weid'=>$_GPC['weid'],
						'fid'=>$fid,
						'userName'=>$_GPC['userName'],
						'telephone'=>$_GPC['telephone'],
						'from_user'=>$_GPC['from_user'],
						'card_num'=>$row['card_num'],
						'create_time'=>time(),
					);
					$temp = pdo_insert('market_fans', $insert);
					if($temp==false){
						$data=array(
							'error'=>3,
							'msg'=>'数据保存失败，稍后重试。',
						);
					}else{
						$data=array(
							'error'=>0,
							'msg'=>'成功领取会员卡。',
						);
						//更新market_business
						pdo_update('market_business', array('card_num'=>$row['card_num']),array('id'=>$id));
					}
					break;
				}
			}
		}
		if(empty($data)){
			$data=array(
				'error'=>3,
				'msg'=>'数据操作失败，稍后重试。',
			);
		}
		echo (json_encode($data));
	}
	
}