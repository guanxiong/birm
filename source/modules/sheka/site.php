<?php
/**
 * 贺卡模块处理程序
 *
 * @author nuqut
 * @url  heka.weibaza.com
 */
defined('IN_IA') or exit('Access Denied');
class ShekaModuleSite extends WeModuleSite {
private $turlar=array(
'1'=>array('id'=>1,'name'=>"生日卡",'ename'=>"shengri"),
'2'=>array('id'=>2,'name'=>"祝贺卡",'ename'=>"zhuhe"),
'3'=>array('id'=>3,'name'=>"爱情卡",'ename'=>"aiqing"),
'4'=>array('id'=>4,'name'=>"亲友卡",'ename'=>"qinyou"),
'5'=>array('id'=>5,'name'=>"心情卡",'ename'=>"xinqing"),
'6'=>array('id'=>6,'name'=>"感谢卡",'ename'=>"ganxie"),
'7'=>array('id'=>7,'name'=>"道歉卡",'ename'=>"daoqian"),
'8'=>array('id'=>8,'name'=>"打气卡",'ename'=>"weiwen"),
'9'=>array('id'=>9,'name'=>"会面卡",'ename'=>"baifang"),
'10'=>array('id'=>10,'name'=>"节日卡",'ename'=>"jieri"),
'11'=>array('id'=>11,'name'=>"商务定制",'ename'=>"dingzhi"),
'12'=>array('id'=>12,'name'=>"其他卡",'ename'=>"qita"),
);

private $slide=array(
'0'=>array('id'=>1,'name'=>"生日卡"),
'1'=>array('id'=>2,'name'=>"祝贺卡"),
'2'=>array('id'=>3,'name'=>"爱情卡"),
);
	public function __construct() {
		global $_W;
		$_W['settings']=$_W['account']['modules']['sheka']['config'];
			}

public function doMobileIndex(){
		global $_GPC, $_W;
		include $this->template('index');
	}
public function doMobileList(){
		global $_GPC, $_W;
		$classid = intval($_GPC['classid']);
       $list = pdo_fetchall("SELECT * FROM " . tablename('sheka_list') . "  where classid= '{$classid}'  and (weid = '{$_W['weid']}'  or weid =0)  ORDER BY id deSC");
		include $this->template('list');
	}
	public function doMobilePreview(){
			global $_GPC, $_W;
			$id = intval($_GPC['id']);
		$sql = "SELECT * FROM " . tablename('sheka_list') . " WHERE `id`=:id";
		$detail = pdo_fetch($sql, array(':id'=>$id));

		if (empty($detail['id'])) {
			exit;
		}
						include $this->template('preview');

					
	}
		public function doMobileTemp(){
					global $_GPC, $_W;
			$id = intval($_GPC['id']);
         $data = pdo_fetch("SELECT * FROM " . tablename('sheka_list') . " WHERE id = '{$id}' ");
			//include $this->template('temp');
				if ($data['tempid']==1) {
				include $this->template('temp/'.$data['id'].'');
				}else {
				$zhufu = pdo_fetch("SELECT * FROM " . tablename("sheka_zhufu") . " WHERE  cid = :cid  ", array(
                    ':cid' => $id
				));
						include $this->template('temp_'.$data['tempid'].'');
				}
		}

		
		public function doMobileSendform(){
					global $_GPC, $_W;
			$id = intval($_GPC['id']);
		$sql = "SELECT * FROM " . tablename('sheka_list') . " WHERE `id`=:id";
		$data = pdo_fetch($sql, array(':id'=>$id));
		if (empty($data['id'])) {
			exit;
		}
		 $zhufu = pdo_fetch("SELECT * FROM " . tablename('sheka_zhufu') . " WHERE cid = '{$id}' ");
		 $zhufulist = pdo_fetchall("SELECT * FROM " . tablename('sheka_zhufu') . " as z left join  " . tablename('sheka_list') . " as l   on  z.cid=l.id WHERE l.classid = '{$data['classid']}'   and  l.lang = '{$data['lang']}'  limit 0,10");
					include $this->template('sendform');

		}
				public function doMobileCardshow(){
					global $_GPC, $_W;
			$id = intval($_GPC['id']);
			$cardFrom = htmlspecialchars_decode($_GPC['cardFrom']);
			$cardTo = htmlspecialchars_decode($_GPC['cardTo']);
			$cardBody =htmlspecialchars_decode( $_GPC['cardBody']);
		$sql = "SELECT * FROM " . tablename('sheka_list') . " WHERE `id`=:id";
		$data = pdo_fetch($sql, array(':id'=>$id));
		if (empty($data['id'])) {
			exit;
		}
			include $this->template('cardshow');
		}
}