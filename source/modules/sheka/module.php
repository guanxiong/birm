<?php
/**
 * 贺卡模块处理程序
 *
 */
defined('IN_IA') or exit('Access Denied');

include_once IA_ROOT . '/source/modules/sheka/model.php';
class  ShekaModule extends WeModule {
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

	public function fieldsFormDisplay($rid = 0) {
		global $_W, $_GPC;
		if($rid) {
			$reply = pdo_fetch("SELECT * FROM " . tablename('sheka_reply') . " WHERE rid = :rid and weid = :weid", array(':rid' => $rid,':weid' => $_W['weid']));
		}
		include $this->template('form');
	}



	public function fieldsFormSubmit($rid) {
		global $_W, $_GPC;
		$cid = intval($_GPC['cid']);
		$record = array();
		$record['cid'] = $cid;
		$record['rid'] = $rid;
		$record['weid'] = $_W['weid'];
		$record['title'] = $this->turlar[$cid]['name'];
		$record['is_show'] = $_GPC['is_show'];

		$reply = pdo_fetch("SELECT * FROM " . tablename('sheka_reply') . " WHERE rid = :rid", array(':rid' => $rid));
		if($reply) {
			pdo_update('sheka_reply', $record, array('id' => $reply['id']));
		} else {
			pdo_insert('sheka_reply', $record);
		}
	}

	public function ruleDeleted($rid) {
		pdo_delete('sheka_reply', array('rid' => $rid));
	}

	public function doQuery() {
		global $_W, $_GPC;

		$ds = $this->turlar;
				foreach($ds as &$row) {
			$r = array();
			$r['name'] = $row['name'];
			$r['description'] = $row['name'];
			$r['id'] = $row['id'];
			$row['entry'] = $r;
		}
		include $this->template('query');
	}
    public function settingsDisplay($settings) {
        global $_GPC, $_W;
        if (checksubmit()) {
            $cfg = array(
                'name' => $_GPC['name'],
                'logo' => $_GPC['logo'],
                'appid' => $_GPC['appid'],
                'secret' => $_GPC['secret'],
                'url'=>$_GPC['url']
            );
            if ($this->saveSettings($cfg)) {
                message('保存成功', 'refresh');
            }
        }
        include $this->template('setting');
    }
	public function doList() {
			global $_W, $_GPC;

			$op = $_GPC['op'];
		if (empty($op)) $op = "display";
		if ($op == 'display') {
			$classid=intval($_GPC['classid']);
			$pindex = max(1, intval($_GPC['page']));
			$psize = 24;
			$condition = '';
			$params = array();
		    $condition .= " or weid = 0";
		    if (!empty($classid)){

		    $condition .= " and classid = '{$classid}'";

		    }
			$list = pdo_fetchall("SELECT * FROM ".tablename('sheka_list')." WHERE weid = '{$_W['weid']}' $condition ORDER BY  id DESC LIMIT ".($pindex - 1) * $psize.','.$psize);
			$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('sheka_list') . " WHERE  weid = '{$_W['weid']}' $condition");
			$pager = pagination($total, $pindex, $psize);
		}elseif ($op == 'post') {
					$id = intval($_GPC['id']);
					$zid = intval($_GPC['zid']);
			if ($id) {
						if (empty($_W['isfounder'])) {
				$item = pdo_fetch("SELECT * FROM " . tablename("sheka_list") . " WHERE  weid = 0 or weid=:weid and id = :id  ", array(
                    ':weid' => $_W['weid'],
                    ':id' => $id
				));}else{
				
						$item = pdo_fetch("SELECT * FROM " . tablename("sheka_list") . " WHERE  id = :id  ", array(
                    ':id' => $id
				));
				
				}
				
				
				if (empty($item)) {
					message('用户不存在或是已经被删除！');
				}
				
				if ($item['weid']==0) {
							if (empty($_W['isfounder'])) {
				message('抱歉，你没有权限！', '', 'error');
					}
					}
				
				$zhufu = pdo_fetch("SELECT * FROM " . tablename("sheka_zhufu") . " WHERE  cid = :cid  ", array(
                    ':cid' => $id
				));
			}else{
			$item['tempid']=0;
			
			}
					if (checksubmit('submit')) {
if (empty($_GPC['title'])) {message('标题不能为空，请输入标题！');}
				$insert = array(
				'weid' => $_W['weid'],
				'title' => $_GPC['title'],
				'classid' => $_GPC['classid'],
				'tempid' => $_GPC['tempid'],
				'thumb' => $_GPC['thumb'],
				'cardbg' => $_GPC['cardbg'],
				'music' => $_GPC['music'],
				'lang' => $_GPC['lang'],
				);
				
				$zinsert = array(
				'weid' => $_W['weid'],
				'lang' => $_GPC['lang'],
				'cardfrom' => $_GPC['cardfrom'],
				'cardto' => $_GPC['cardto'],
				'cardbody' => $_GPC['cardbody'],
				'cardto_left' => $_GPC['cardto_left'],
				'cardto_top' => $_GPC['cardto_top'],
				'cardbody_width' => $_GPC['cardbody_width'],
				'cardbody_left' => $_GPC['cardbody_left'],
				'cardbody_top' => $_GPC['cardbody_top'],
				'cardfrom_left' => $_GPC['cardfrom_left'],
				'cardfrom_top' => $_GPC['cardfrom_top'],
				'panel_top' => $_GPC['panel_top'],
				'panel_left' => $_GPC['panel_left'],
				'panel_width' => $_GPC['panel_width'],
				'panel_height' => $_GPC['panel_height'],
				'panel_color' => $_GPC['panel_color'],
				'panel_bg' => $_GPC['panel_bg'],
				'panel_alpha' => $_GPC['panel_alpha'],
				);
				
				if (empty($id)) {
					pdo_insert("sheka_list", $insert);
					$insertid = pdo_insertid();
					$zinsert['cid']=$insertid;
					pdo_insert("sheka_zhufu", $zinsert);
					
				} else {
					pdo_update("sheka_list", $insert, array(
                        'id' => $id
					));
					
			if ($zid) {
	    		pdo_update("sheka_zhufu", $zinsert, array(
                        'id' => $zid
					));
					}else{
					$zinsert['cid']=$id;
					pdo_insert("sheka_zhufu", $zinsert);
					}
				}	
				$thumb = explode('/',$insert['thumb']);	
				$asliname=IA_ROOT . '/resource/attachment/'.$insert['thumb'];
				//$newname=IA_ROOT . '/resource/attachment/'.$thumb[0].'/'.$thumb[1].'/'.$thumb[2].'/'.$thumb[3].'/s_'.$thumb[4];
				$newname=IA_ROOT . '/resource/attachment/'.$thumb[0].'/'.$thumb[1].'/'.$thumb[2].'/'.$thumb[3];
				img2thumb($asliname, $newname,75, 75,1);
			    message('修改成功！', $this->createWebUrl('list', array('op' => 'post','id' => $id)), 'success');
					}
		
		}
	    include $this->template('list');
	}



}
