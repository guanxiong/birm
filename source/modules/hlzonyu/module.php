<?php
/**
 * 抢礼品模块定义
 * [皓蓝] www.weixiamen.cn 5517286
 */
defined('IN_IA') or exit('Access Denied');

class hlzonyuModule extends WeModule {
	public $name = 'hlzonyuModule';
	public $title = '抢礼品';
	public $table_reply  = 'hlzonyu_reply';
	public $table_list   = 'hlzonyu_list';	
	public $table_data   = 'hlzonyu_data';

	public function fieldsFormDisplay($rid = 0) {
		//要嵌入规则编辑页的自定义内容，这里 $rid 为对应的规则编号，新增时为 0
		global $_W;
		if (!empty($rid)) {
			$reply = pdo_fetch("SELECT * FROM ".tablename($this->table_reply)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));				
 		} 
		$reply['start_time'] = empty($reply['start_time']) ? strtotime(date('Y-m-d H:i')) : $reply['start_time'];
		$reply['end_time'] = empty($reply['end_time']) ? strtotime("+1 week") : $reply['end_time'];
		$reply['type'] = !isset($reply['type']) ? "1" : $reply['type'];
		$reply['ndrankstatus'] = !isset($reply['ndrankstatus']) ? "0" : $reply['ndrankstatus'];
		$reply['btype'] = !isset($reply['btype']) ? "0" : $reply['btype'];
		$reply['ndrankstatusnum'] = empty($reply['ndrankstatusnum']) ? "3" : $reply['ndrankstatusnum'];
		
		$reply['zonyu_type'] = !isset($reply['zonyu_type']) ? "1" : $reply['zonyu_type'];
		$reply['zonyu_show'] = !isset($reply['zonyu_show']) ? "0" : $reply['zonyu_show'];
		$reply['zonyu_imgtext'] = !isset($reply['zonyu_imgtext']) ? "0" : $reply['zonyu_imgtext'];
		$reply['zonyu_rankshow'] = !isset($reply['zonyu_rankshow']) ? "0" : $reply['zonyu_rankshow'];
		$reply['zonyu_numtype'] = !isset($reply['zonyu_numtype']) ? "0" : $reply['zonyu_numtype'];		
		$reply['zonyu_shownum'] = empty($reply['zonyu_shownum']) ? "50" : $reply['zonyu_shownum'];
		$reply['zonyu_ranknum'] = empty($reply['zonyu_ranknum']) ? "50" : $reply['zonyu_ranknum'];
		$reply['zonyu_num'] = !isset($reply['zonyu_num']) ? "1" : $reply['zonyu_num'];
		$reply['picture'] = empty($reply['picture']) ? "./source/modules/hlzonyu/template/images/zanimg.jpg" : $reply['picture'];
		$reply['dingpic'] = empty($reply['dingpic']) ? "./source/modules/hlzonyu/template/images/top.jpg" : $reply['dingpic'];
		$reply['zanpic'] = empty($reply['zanpic']) ? "./source/modules/hlzonyu/template/images/product1.jpg" : $reply['zanpic'];
		$picture = $reply['picture'];
		$dingpic = $reply['dingpic'];
		$zanpic = $reply['zanpic'];
		$sharetitle=$reply['sharetitle'];
		if (substr($picture,0,6)=='images'){
		    $picture = $_W['attachurl'] . $picture;
		}
		if (substr($dingpic,0,6)=='images'){
		    $dingpic = $_W['attachurl'] . $dingpic;
		}
		if (substr($zanpic,0,6)=='images'){
			$zanpic = $_W['attachurl'] . $zanpic;
		}
		

		include $this->template('hlzonyu/form');
		
	}

	public function fieldsFormValidate($rid = 0) {
		//规则编辑保存时，要进行的数据验证，返回空串表示验证无误，返回其他字符串将呈现为错误提示。这里 $rid 为对应的规则编号，新增时为 0
		return '';
	}

	public function fieldsFormSubmit($rid) {
		//规则验证无误保存入库时执行，这里应该进行自定义字段的保存。这里 $rid 为对应的规则编号
		global $_GPC, $_W;
		$weid = $_W['weid'];
		$id = intval($_GPC['reply_id']);
		if (intval($_GPC['zonyu_num'])<1) {
			message('抱歉，助抢周期最少为１天！', '', 'error');
		}
		$credit  = intval($_GPC['credit']);
		$creditx = intval($_GPC['creditx']);
		if(!$creditx>$credit){
			$creditx=$credit+1;
		}
		
		$insert = array(
			'rid' => $rid,
			'weid' => $weid,
            'title' => $_GPC['title'],			
			'picture' => $_GPC['picture'],
			'zonyuurl' => $_GPC['zonyuurl'],
			'description' => $_GPC['description'],			
			'content' => $_GPC['content'],	
			'start_time' => strtotime($_GPC['start_time']),
			'end_time' => strtotime($_GPC['end_time']),
			'status' => intval($_GPC['status']),
			'type' => intval($_GPC['type']),
			'btype' => intval($_GPC['btype']),
			'credit' => $credit,
			'creditx' =>  $creditx,
			'zonyunum' => intval($_GPC['zonyunum']),
			'productprice' => intval($_GPC['productprice']),
			'email' => $_GPC['email'],
			'zhongjiang' => $_GPC['zhongjiang'],
			'dingpic' => $_GPC['dingpic'],
			'zanpic' => $_GPC['zanpic'],
			'ndrankstatus' => intval($_GPC['ndrankstatus']),
			'ndrankstatusnum' => intval($_GPC['ndrankstatusnum']),
			'zonyu_type' => intval($_GPC['zonyu_type']),
			'zonyu_show' => intval($_GPC['zonyu_show']),
			'zonyu_imgtext' => intval($_GPC['zonyu_imgtext']),
			'zonyu_rankshow' => intval($_GPC['zonyu_rankshow']),
			'zonyu_num' => intval($_GPC['zonyu_num']),
			'zonyu_shownum' => intval($_GPC['zonyu_shownum']),
			'zonyu_numtype' => intval($_GPC['zonyu_numtype']),
			'zonyu_ranknum' => intval($_GPC['zonyu_ranknum']),
			'shangjia' => 0,
			'sharetitle'=> $_GPC['sharetitle'],
			
		);
		if (empty($id)) {
			pdo_insert($this->table_reply, $insert);
		} else {			
			pdo_update($this->table_reply, $insert, array('id' => $id));
		}		

	}

	public function ruleDeleted($rid) {
		//删除规则时调用，这里 $rid 为对应的规则编号
		global $_W;		
		pdo_delete($this->table_reply, "rid = '".$rid."'");
		pdo_delete($this->table_list, "rid = '".$rid."'");
		pdo_delete($this->table_data, "rid = '".$rid."'");
		message('删除活动成功！', referer(), 'success');
		return true;
	}

	public function settingsDisplay($settings) {
		global $_GPC, $_W;
		if(checksubmit()) {
			$cfg = array();
			$cfg['appid'] = $_GPC['appid'];
			$cfg['secret'] = $_GPC['secret'];
			$cfg['ranklist'] = $_GPC['ranklist'];
			$cfg['bglist'] = $_GPC['bglist'];
			$cfg['rule'] = htmlspecialchars_decode($_GPC['rule']);
			$cfg['qj1'] = $_GPC['qj1'];
			$cfg['qj2'] = $_GPC['qj2'];
			$cfg['qj3'] = $_GPC['qj3'];
			$cfg['qj4'] = $_GPC['qj4'];
			$cfg['qj5'] = $_GPC['qj5'];
			$cfg['hs1'] = $_GPC['hs1'];
			$cfg['hs2'] = $_GPC['hs2'];
			$cfg['hs3'] = $_GPC['hs3'];
			$cfg['hs4'] = $_GPC['hs4'];
			$cfg['hs5'] = $_GPC['hs5'];
			$cfg['sjxz'] = intval($_GPC['sjxz']);
			$cfg['zk1'] = $_GPC['zk1'];
			$cfg['zk2'] = $_GPC['zk2'];	
			$cfg['xgzurl'] = $_GPC['xgzurl'];				
			if($this->saveSettings($cfg)) {
				message('保存成功', 'refresh');
			}
		}		
		include $this->template('setting');
	}

	public function dozonyulist() {		
		global $_GPC, $_W;
		checklogin();
		$weid = $_W['weid'];//当前公众号ID
		$id = intval($_GPC['id']);
		if (checksubmit('delete')) {
			pdo_delete($this->table_list, " id IN ('".implode("','", $_GPC['select'])."')");
			message('删除成功！', create_url('site/module', array('do' => 'zonyulist', 'name' => 'hlzonyu', 'id' => $id, 'page' => $_GPC['page'])));
		}
		$where = '';
		!empty($_GPC['keywordtel']) && $where .= " AND b.mobile LIKE '%{$_GPC['keywordtel']}%'";
		!empty($_GPC['keywordname']) && $where .= " AND b.realname LIKE '%{$_GPC['keywordname']}%'";
		!empty($_GPC['keywordid']) && $where .= " AND a.rid = '{$_GPC['keywordid']}'";

		$rules = pdo_fetchall('SELECT `id`,`name` FROM '.tablename('rule').' WHERE `module`=\'zonyu\'');
		$pindex = max(1, intval($_GPC['page']));
		$psize = 15;

		//取得抢礼品列表
		$list_zonyu = pdo_fetchall('SELECT a.*,b.realname,b.mobile FROM '.tablename($this->table_list).' as a left join '.tablename('fans').' as b on a.from_user=b.from_user  WHERE a.weid= :weid '.$where.' order by `id` desc LIMIT ' . ($pindex - 1) * $psize . ',' . $psize, array(':weid' => $weid) );
		$total = pdo_fetchcolumn('SELECT COUNT(*) FROM '.tablename($this->table_list).' as a left join '.tablename('fans').' as b on a.from_user=b.from_user WHERE a.weid= :weid '.$where.' ', array(':weid' => $weid));
		$pager = pagination($total, $pindex, $psize);
		include $this->template('list');

	}
	public function dozonyuranklist() {		
		global $_GPC, $_W;
		checklogin();
		$weid = $_W['weid'];//当前公众号ID
		$id = intval($_GPC['id']);
		$page = $_GPC['page'];
		if (empty($page)){
		  $page = 1;
		}
		if (checksubmit('delete')) {
			pdo_delete($this->table_list, " id IN ('".implode("','", $_GPC['select'])."')");
			message('删除成功！', create_url('site/module', array('do' => 'zonyuranklist', 'name' => 'hlzonyu', 'id' => $id, 'page' => $_GPC['page'])));
		}
		$reply = pdo_fetch("SELECT title FROM ".tablename($this->table_reply)." WHERE weid = :weid and rid = :rid", array(':weid' => $weid, ':rid' => $id));
		//
		$pindex = max(1, intval($_GPC['page']));
		$psize = 15;

		//取得抢礼品列表
		$list_zonyu = pdo_fetchall('SELECT a.*,b.realname,b.mobile FROM '.tablename($this->table_list).' as a left join '.tablename('fans').' as b on a.from_user=b.from_user  WHERE a.rid = '.$id.' and a.weid= :weid order by `zonyunum` desc,`zonyutime` asc LIMIT ' . ($pindex - 1) * $psize . ',' . $psize, array(':weid' => $weid) );
		$total = pdo_fetchcolumn('SELECT COUNT(*) FROM '.tablename($this->table_list).' WHERE rid = '.$id.' and weid= :weid order by `id` desc ', array(':weid' => $weid));
		$pager = pagination($total, $pindex, $psize);
		include $this->template('ranklist');

	}
	public function dozonyudatalist() {		
		global $_GPC, $_W;
		checklogin();
		$weid = $_W['weid'];//当前公众号ID
		$id = intval($_GPC['id']);
		$uid = intval($_GPC['uid']);
		$page = $_GPC['page'];
		if (empty($page)){
		  $page = 1;
		}
		if (!empty($uid)){
			$Where = " AND `uid` = $uid";		
		}
		if (checksubmit('delete')) {
			pdo_delete($this->table_data, " id IN ('".implode("','", $_GPC['select'])."')");
			message('删除成功！', create_url('site/module', array('do' => 'zonyudatalist', 'name' => 'hlzonyu', 'id' => $id, 'page' => $_GPC['page'])));
		}
		$reply = pdo_fetch("SELECT title FROM ".tablename($this->table_reply)." WHERE weid = :weid and rid = :rid", array(':weid' => $weid, ':rid' => $id));
		//
		$pindex = max(1, intval($_GPC['page']));
		$psize = 15;

		//取得抢礼品数据列表
		$list_zonyudata = pdo_fetchall('SELECT * FROM '.tablename($this->table_data).' WHERE weid= :weid and rid=:rid '.$Where.' order by `zonyutime` desc LIMIT ' . ($pindex - 1) * $psize . ',' . $psize, array(':weid' => $weid, ':rid' => $id) );	
		//查询分享人姓名电话开始
		foreach ($list_zonyudata as $mid => $list) {
		    $reply1 = pdo_fetch("SELECT from_user FROM ".tablename($this->table_list)." WHERE weid = :weid and rid = :rid and id = :id ", array(':weid' => $_W['weid'], ':rid' => $id, ':id' => $list['uid']));
		    $profile  = fans_search($reply1['from_user'], array('realname','mobile'));
			$list_zonyudata[$mid]['frealname'] = $profile['realname'];
			$list_zonyudata[$mid]['fmobile'] = $profile['mobile'];
			
		}
		//查询分享人姓名电话结束

		$total = pdo_fetchcolumn('SELECT COUNT(*) FROM '.tablename($this->table_data).' WHERE weid= :weid and rid=:rid '.$Where.' order by `zonyutime` desc ', array(':weid' => $weid, ':rid' => $id));
		$pager = pagination($total, $pindex, $psize);
		include $this->template('datalist');

	}
	public function dostatus( $rid = 0) {
		global $_GPC;
		$rid = $_GPC['rid'];
		echo $rid;
		$insert = array(
			'status' => $_GPC['status']
		);
		
		pdo_update($this->table_reply,$insert,array('rid' => $rid));
		message('模块操作成功！', referer(), 'success');
	}
	public function dodos( $id = 0) {
		global $_GPC;
		$rid = $_GPC['rid'];
		$id = $_GPC['id'];
		$zonyulist = $_GPC['ac'];
		echo $id;
		$insert = array(
			'status' => $_GPC['status']
		);
		
		pdo_update($this->table_list,$insert,array('id' => $id,'rid' => $rid));
		message('屏蔽操作成功！', create_url('site/module/'.$zonyulist.'', array('name' => 'hlzonyu', 'id' => $rid, 'page' => $_GPC['page'])));
	}	
	public function dodosjiang( $id = 0) {
		global $_GPC;
		$rid = $_GPC['rid'];
		$id = $_GPC['id'];
		$zonyulist = $_GPC['ac'];
		echo $id;
		$insert = array(
			'zhongjiang' => $_GPC['status']
		);
		
		pdo_update($this->table_list,$insert,array('id' => $id,'rid' => $rid));
		message('已成功发放奖品！', create_url('site/module/'.$zonyulist.'', array('name' => 'hlzonyu', 'id' => $rid, 'page' => $_GPC['page'])));
	}	
	public function dozonyudata() {		
		global $_GPC, $_W;
		checklogin();
		$weid = $_W['weid'];//当前公众号ID
		$id = intval($_GPC['id']);
		$uid = intval($_GPC['uid']);
		$rid = intval($_GPC['rid']);
		if (checksubmit('delete')) {
			pdo_delete($this->table_data, " id IN ('".implode("','", $_GPC['select'])."')");
			message('删除成功！', create_url('site/module/zonyudata', array('name' => 'hlzonyu', 'id' => $id, 'page' => $_GPC['page'])));
		}
		if (!empty($uid)){
			$Where = " AND `uid` = $uid";		
		}
		if (!empty($rid)){
			$Where = $Where." AND `rid` = $rid";		
		}
		$rules = pdo_fetchall('SELECT `id`,`name` FROM '.tablename('rule').' WHERE `module`=\'zonyu\'');
		$pindex = max(1, intval($_GPC['page']));
		$psize = 15;

		//取得分享点击详细数据
		$list_zonyudata = pdo_fetchall('SELECT * FROM '.tablename($this->table_data).' WHERE weid= :weid '.$Where.'  order by `zonyutime` desc LIMIT ' . ($pindex - 1) * $psize . ',' . $psize, array(':weid' => $weid) );
		//查询分享人姓名电话开始
		foreach ($list_zonyudata as $mid => $list) {
		    $reply1 = pdo_fetch("SELECT from_user FROM ".tablename($this->table_list)." WHERE weid = :weid and id = :id ", array(':weid' => $_W['weid'], ':id' => $list['uid']));
		    $profile  = fans_search($reply1['from_user'], array('realname','mobile'));
			$list_zonyudata[$mid]['frealname'] = $profile['realname'];
			$list_zonyudata[$mid]['fmobile'] = $profile['mobile'];
			
		}
		//查询分享人姓名电话结束
		$total = pdo_fetchcolumn('SELECT COUNT(*) FROM '.tablename($this->table_data).' WHERE weid= :weid '.$Where.'  order by `zonyutime` desc ', array(':weid' => $weid));
		$pager = pagination($total, $pindex, $psize);
		include $this->template('data');

	}
	public function dodeldata( $id = 0) {
		global $_GPC;
		$rid = $_GPC['rid'];
		$id = $_GPC['id'];
		if (!empty($id)) {
			pdo_delete($this->table_data, " id = ".$id);
			message('删除成功！', create_url('site/module/zonyudata', array('name' => 'hlzonyu', 'id' => $rid, 'page' => $_GPC['page'])));
		}		
		
	}
	public function dopaiming() {
		global $_W,$_GPC;
		$rid = $_GPC['rid'];
		$id = $_GPC['id'];
		$ndrank = $_GPC['ndrank'];
		$weid = $_W['weid'];//当前公众号ID		
		$reply = pdo_fetch("SELECT ndrankstatusnum,ndrankstatus FROM ".tablename($this->table_reply)." WHERE weid = :weid and rid = :rid", array(':weid' => $weid, ':rid' => $rid));
		$user = pdo_fetch("SELECT a.*,b.realname,b.mobile FROM ".tablename($this->table_list)." as a left join ".tablename('fans')." as b on a.from_user=b.from_user WHERE a.weid = :weid and a.id = :id", array(':weid' => $weid, ':id' => $id));
		$user['ndranknums'] = empty($user['ndranknums']) ? "10" : $user['ndranknums'];
		//生成排名序列
		if ($reply['ndrankstatus']==1){
		    for($i = 1; $i <= $reply['ndrankstatusnum']; $i++) {
                $usernd = pdo_fetch("SELECT * FROM ".tablename($this->table_list)." WHERE weid = :weid and id != :id and ndrank = :ndrank", array(':weid' => $weid, ':id' => $id, ':ndrank' => $i));
			    if (empty($usernd)){
			        $paimingnum .= $i.'|';
			    }
            }		
		    $paimingnum = substr($paimingnum,0,strlen($paimingnum)-1); 
		    $paimingnum = explode("|",$paimingnum);	
		}else{
			message('此活动没有开启内定排名，无法设置，正返回此活动设置，修改为开启内定排名即可！', create_url('rule/post', array('id' => $rid)),'error');
		}
		if ($_GPC['action']=='save') {
			$updata=array(
				'ndrank'=>intval($_GPC['ndrank']),
				'ndranknums'=>intval($_GPC['ndranknums']),
			);
			pdo_update($this->table_list, $updata, array('id' => $_GPC['id']));
			//计算内定排名开始
			if ($reply['ndrankstatus']==1){			
				//取真实排名第一的助抢数
				$zonyunum = 0;
				$listshare = pdo_fetch('SELECT zonyunum,from_user FROM '.tablename($this->table_list).'  WHERE weid= :weid AND rid = :rid and ndrank = 0 order by `zonyunum` desc LIMIT 1', array(':weid' => $weid,':rid' => $rid));
				if (!empty($listshare)){
				    $zonyunum = $listshare['zonyunum'];
				    //取内定排名人员
				    $lists = pdo_fetchall("SELECT id,ndranknums,zonyunum FROM ".tablename($this->table_list)." WHERE weid = '".$weid."' and rid= '".$rid."' and ndrank > 0 order by `ndrank` desc limit ".$reply['ndrankstatusnum']."" );
				    foreach ($lists as $list) {
				 	   if($list['zonyunum']<=$zonyunum){
						    $zonyunum = intval($zonyunum+mt_rand(1,$list['ndranknums']));
							$updata = array(
			        	       'zonyunum' => $zonyunum,
		            	    );
							pdo_update($this->table_list, $updata, array('id' => $list['id']));				
				   	 }
					}	
				}
			}		
			//计算内定排名结束
			message('内定排名 第'.intval($_GPC['ndrank']).'名 设置成功', create_url('site/module/zonyulist', array('name' => 'hlzonyu')));
		}
		include $this->template('rank');
	}
	
		
	
    //导出数据
	public function dodownload(){
		require_once 'download.php';
		//echo "过两日更新";
	}
	public function dodownloaddata(){
		require_once 'downloaddata.php';
		//echo "过两日更新";
	}

}