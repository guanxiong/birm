<?php
/**
 * 转发朋友圈积分兑奖
 * @author dongyue
 * @url http://bbs.we7.cc/
 */
defined ( 'IN_IA' ) or exit ( 'Access Denied' );
class GoshareModuleSite extends WeModuleSite {
	public $trantable='goshare_transmit';
	public $giftgrouptable='goshare_giftgroup';
	public $gifttable='goshare_gift';
	public $themetable='goshare_theme';
	public $membertable='goshare_member';
	public $convertable = 'goshare_convert';
	public $cookietable = 'goshare_cookie';
	
	/**
	 * 活动主题设置
	 */
	public function doWebThemeSet(){
		global $_W;
		global $_GPC; 
		$operation = ! empty ( $_GPC ['op'] ) ? $_GPC ['op'] : 'display';
		if (empty ( $_GPC ['op'] ) && $this->isThemesEmpty ()) {
			$operation = 'post';
		}
		
		if ($operation == 'post') {
			$themeid = intval ( $_GPC ['themeid'] );
			if (! empty ( $themeid )) {
				$theme = pdo_fetch ( "SELECT * FROM " . tablename ($this->themetable) . " WHERE id =" . $themeid );
				if (empty ( $theme )) {
					message ( '抱歉，主题不存在或是已经删除！', '', 'error' );
				}
			}
			$groups = pdo_fetchall ( "SELECT * FROM " . tablename ($this->giftgrouptable) . " WHERE weid = '{$_W['weid']}'" );
			if (checksubmit ( 'submit' )) {
				if (empty ( $_GPC ['themename'] )) {
					message ( '请输入活动主题名称' );
				}
				if (empty ( $_GPC ['headtitle'] )) {
					message ( '请输入网页标题' );
				}
				if (empty ( $_GPC ['themetitle'] )) {
					message ( '请输入主题名称' );
				}
				if (empty ( $_GPC ['undertaker'] )) {
					message ( '请输入主办方名称' );
				}
				if(empty ( $_GPC ['begintime'] )){
					message ( '请选择开始日期' );
				}
				if(empty ( $_GPC ['endtime'] )){
					message ( '请选择截止日期' );
				}
				if(strtotime($_GPC ['begintime'])-strtotime($_GPC ['endtime'] ) >= 0 ){
					message ( '开始日期不能晚于截止日期' );
				}
				if (empty ( $_GPC ['place'] )) {
					message ( '请输入兑奖地址' );
				}
				if (empty ( $_GPC ['tel'] )) {
					message ( '请输入兑奖电话' );
				}
				if ((empty ( $_GPC ['ad1'] ) && !empty ( $_GPC ['ad1content'] )) ||(!empty ( $_GPC ['ad1'] ) && empty ( $_GPC ['ad1content'] ))) {
					message ( '请确保广告一区标题和内容不为空' );
				}
				if ((empty ( $_GPC ['ad2'] ) && !empty ( $_GPC ['ad2content'] )) ||(!empty ( $_GPC ['ad2'] ) && empty ( $_GPC ['ad2content'] ))) {
					message ( '请确保广告二区标题和内容不为空' );
				}
				if ((empty ( $_GPC ['ad3'] ) && !empty ( $_GPC ['ad3content'] )) ||(!empty ( $_GPC ['ad3'] ) && empty ( $_GPC ['ad3content'] ))) {
					message ( '请确保广告三区标题和内容不为空' );
				}
				$groupid = $_GPC ['groupid'] ;
				$group;
				if (!empty ($groupid)) {
					$group = pdo_fetch ( "SELECT * FROM " . tablename ($this->giftgrouptable) . " WHERE id =" . $groupid );
				}
				$operation = ! empty ( $_GPC ['op'] ) ? $_GPC ['op'] : 'display';
				$data = array (
						'weid' => $_W ['weid'],
						'groupid' => $group [id],
						'groupname' => $group [groupname],
						'themename' => $_GPC ['themename'],
						'headtitle' => $_GPC ['headtitle'],
						'themetitle' => $_GPC ['themetitle'],
						'themelogo' => $_GPC ['themelogo'],
						'undertaker' => $_GPC ['undertaker'],
						'begintime' => $_GPC ['begintime'],
						'endtime' => $_GPC ['endtime'],
						'place' => $_GPC ['place'],
						'tel' => $_GPC ['tel'],
						'ad1' =>  $_GPC ['ad1'],
						'ad1content' => $_GPC ['ad1content'],
						'ad2' => $_GPC ['ad2'],
						'ad2content' => $_GPC ['ad2content'],
						'ad3' => $_GPC ['ad3'],
						'ad3content' =>  $_GPC ['ad3content'],
						'ad3pic' => $_GPC ['ad3pic'],
						'footpic' => $_GPC ['footpic'],
						'overtitle' => $_GPC ['overtitle'],
						'sharepic' => $_GPC ['sharepic'],
				);
				if (! empty ( $themeid )) {
					if($group [id] != $theme[groupid]){
						$resetname = '';
						pdo_update ($this->giftgrouptable, array ('themeid' => $themeid,'themename' => $_GPC ['themename']), array ('id' => $groupid) );
						pdo_update ($this->giftgrouptable, array ('themeid' => 0,'themename' => $resetname), array ('id' => $theme[groupid]) );
					}
					pdo_update ($this->themetable, $data, array (
							'id' => $themeid 
					) );
				} else {
					pdo_insert ($this->themetable, $data );
					$themeid = pdo_insertid();
					pdo_update ($this->giftgrouptable, array ('themeid' => $themeid,'themename' => $_GPC ['themename']), array ('id' => $groupid) );
				}
				message ( '更新成功！', $this->createWebUrl ( 'themeset', array (
						'op' => 'display' 
				) ), 'success' );
			}
		} else if ($operation == 'delete') {
			$themeid = intval ( $_GPC ['themeid'] );
			$row = pdo_fetch ( "SELECT id FROM " . tablename ($this->themetable) . " WHERE id = " . $themeid );
			if (empty ( $row )) {
				message ( '抱歉，主题不存在或是已经被删除！' );
			}
			pdo_delete ($this->themetable, array (
					'id' => $themeid 
			) );
			pdo_delete ($this->cookietable, array (
			'themeid' => $themeid,
			'weid' => $_W['weid']
			) );
			pdo_delete ($this->convertable, array (
			'themeid' => $themeid,
			'weid' => $_W['weid']
			) );
			pdo_delete ($this->trantable, array (
			'themeid' => $themeid,
			'weid' => $_W['weid']
			) );
			pdo_delete ($this->membertable, array (
			'themeid' => $themeid,
			'weid' => $_W['weid']
			) );
			message ( '删除成功！', referer (), 'success' );
		} else if ($operation == 'display') {
			$condition = '';
			$list = pdo_fetchall ( "SELECT * FROM " . tablename ($this->themetable) . " WHERE weid = '{$_W['weid']}' $condition ORDER BY id DESC" );
		}
		include $this->template ( 'themeset' );
	
	}
	
	/**
	 * 当前公众号下有无活动主题
	 * @return boolean
	 */
	private function isThemesEmpty() {
		global $_W;
		$result = pdo_fetch ( "SELECT count(*) as cnt FROM " . tablename ($this->themetable) . " WHERE weid = '{$_W['weid']}'" );
		return ($result ['cnt'] <= 0);
	}
	
	/**
	 * 当前公众号下有无奖品分组
	 * @return boolean
	 */
	private function isGiftGroupsEmpty() {
		global $_W;
		$result = pdo_fetch ( "SELECT count(*) as cnt FROM " . tablename ($this->giftgrouptable) . " WHERE weid = '{$_W['weid']}'" );
		return ($result ['cnt'] <= 0);
	}
	
	/**
	 * 当前分组下有无奖品
	 * @return boolean
	 */
	private function isGiftsEmpty() {
		global $_W;
		$result = pdo_fetch ( "SELECT count(*) as cnt FROM " . tablename ($this->gifttable) . " WHERE weid = '{$_W['weid']}'" );
		return ($result ['cnt'] <= 0);
	}
	
	/**
	 * 奖品管理
	 */
	public function doWebGiftSet(){
		global $_W;
		global $_GPC; 
		$operation = ! empty ( $_GPC ['op'] ) ? $_GPC ['op'] : 'display';
		if (empty ( $_GPC ['op'] ) && $this->isGiftsEmpty ()) {
			$operation = 'post';
		}
		
		if ($operation == 'post') {
			$giftid = intval ( $_GPC ['giftid'] );
			if (! empty ( $giftid )) {
				$gift = pdo_fetch ( "SELECT * FROM " . tablename ($this->gifttable) . " WHERE id =" . $giftid );
				if (empty ( $gift )) {
					message ( '抱歉，奖品不存在或是已经删除！', '', 'error' );
				}
			}
			$groupid = $_GPC ['groupid'] ;
			$tempgroup;
			if (!empty ($groupid)) {
					$tempgroup = pdo_fetch ( "SELECT * FROM " . tablename ($this->giftgrouptable) . " WHERE id =" . $groupid );
					$gift[groupid]= $tempgroup[id];
					$gift[groupname]= $tempgroup[groupname];
				}
			$groups = pdo_fetchall ( "SELECT * FROM " . tablename ($this->giftgrouptable) . " WHERE weid = '{$_W['weid']}'" );
			if (checksubmit ( 'submit' )) {
				if (empty ( $_GPC ['stdname'] )) {
					message ( '请输入奖品名称' );
				}
				if (empty ( $_GPC ['unit'] )) {
					message ( '请输入奖品的单位' );
				}
				if (empty ( $_GPC ['amount'] )) {
					message ( '请输入奖品数量' );
				}
				if (empty ( $_GPC ['needscore'] )) {
					message ( '请输入兑换奖品所需积分' );
				}
				$groupid = $_GPC ['groupid'] ;
				$group;
				if (!empty ($groupid)) {
					$group = pdo_fetch ( "SELECT * FROM " . tablename ($this->giftgrouptable) . " WHERE id =" . $groupid );
				}
				$operation = ! empty ( $_GPC ['op'] ) ? $_GPC ['op'] : 'display';
				$data = array (
						'weid' => $_W ['weid'],
						'stdname' => $_GPC ['stdname'],
						'unit' => $_GPC ['unit'],
						'left' =>! empty ($_GPC ['left']) ? $_GPC ['left'] : $_GPC ['amount'],
						'sort' => $_GPC ['sort'],
						'needscore' => $_GPC ['needscore'],
						'amount' => $_GPC ['amount'],
						'desc' => $_GPC ['desc'],
						'groupid'=>$group[id],
						'groupname'=>$group[groupname]
				);
				if (! empty ( $gift ) && !empty($giftid)) {
					pdo_update ($this->gifttable, $data, array (
							'id' => $giftid 
					) );
				} else {
					pdo_insert ($this->gifttable, $data );
				}
				message ( '更新成功！', $this->createWebUrl ( 'giftset', array (
						'op' => 'display' 
				) ), 'success' );
			}
		} else if ($operation == 'delete') {
			$giftid = intval ( $_GPC ['giftid'] );
			$row = pdo_fetch ( "SELECT id FROM " . tablename ($this->gifttable) . " WHERE id = " . $giftid );
			if (empty ( $row )) {
				message ( '抱歉，奖品不存在或是已经被删除！' );
			}
			pdo_delete ($this->gifttable, array (
					'id' => $giftid 
			) );
			message ( '删除成功！', referer (), 'success' );
		} else if ($operation == 'display') {
			$condition = '';
			$wsql;
			$giftgroupid = intval ( $_GPC ['giftgroupid'] );
			if(empty ( $giftgroupid )){
				$wsql = "SELECT * FROM " . tablename ($this->gifttable) . " WHERE weid = '{$_W['weid']}' ORDER BY sort ASC";
			}else{
				$wsql = "SELECT * FROM " . tablename ($this->gifttable) . " WHERE weid = '{$_W['weid']}' and groupid = '$giftgroupid' ORDER BY sort ASC";
			}
			$list = pdo_fetchall ( $wsql );
		} 
		include $this->template ( 'giftset' );
		
	}

	/**
	 * 奖品组管理
	 */
	public function doWebGiftGroupSet(){
		global $_W;
		global $_GPC; 
		$operation = ! empty ( $_GPC ['op'] ) ? $_GPC ['op'] : 'display';
		if (empty ( $_GPC ['op'] ) && $this->isGiftGroupsEmpty ()) {
			$operation = 'post';
		}
		
		if ($operation == 'post') {
			$giftgroupid = intval ( $_GPC ['giftgroupid'] );
			if (! empty ( $giftgroupid )) {
				$giftgroup = pdo_fetch ( "SELECT * FROM " . tablename ($this->giftgrouptable) . " WHERE id =" . $giftgroupid );
				if (empty ( $giftgroup )) {
					message ( '抱歉，奖品分组不存在或是已经删除！', '', 'error' );
				}
			}
			if (checksubmit ( 'submit' )) {
				if (empty ( $_GPC ['groupname'] )) {
					message ( '请输入奖品分组名称' );
				}
				$operation = ! empty ( $_GPC ['op'] ) ? $_GPC ['op'] : 'display';
				$data = array (
						'weid' => $_W ['weid'],
						'groupname' => $_GPC ['groupname'],
						'groupstate' => 1
				);
				if (! empty ( $giftgroup )) {
					if( $_GPC ['groupname'] != $giftgroup[groupname]){
						pdo_update ($this->gifttable,array ('groupname' => $_GPC ['groupname'] ), array ('groupid' => $giftgroupid) );
					}
					pdo_update ($this->giftgrouptable, $data, array (
							'id' => $giftgroupid 
					) );
				} else {
					pdo_insert ($this->giftgrouptable, $data );
				}
				message ( '更新成功！', $this->createWebUrl ( 'giftgroupset', array (
						'op' => 'display' 
				) ), 'success' );
			}
		} else if ($operation == 'delete') {
			$giftgroupid = intval ( $_GPC ['giftgroupid'] );
			$row = pdo_fetch ( "SELECT id FROM " . tablename ($this->giftgrouptable) . " WHERE id = " . $giftgroupid );
			if (empty ( $row )) {
				message ( '抱歉，分组不存在或是已经被删除！' );
			}
			$themerow = pdo_fetch ( "SELECT * FROM " . tablename ($this->themetable) . " WHERE groupid = " . $giftgroupid );
			if($themerow){
				message ( '抱歉，奖品分组被主题【'.$themerow['themename'].'】引用，请先删除主题活动' );
			}
			pdo_delete ($this->gifttable, array (
			'groupid' => $giftgroupid
			) );
			pdo_delete ($this->giftgrouptable, array (
					'id' => $giftgroupid 
			) );
			message ( '删除成功！', referer (), 'success' );
		} else if ($operation == 'display') {
			$condition = '';
			$list = pdo_fetchall ( "SELECT * FROM " . tablename ($this->giftgrouptable) . " WHERE weid = {$_W['weid']} ORDER BY id DESC" );
		} else if($operation == 'gifts'){
			$condition = '';
			$wsql;
			$giftgroupid = intval ( $_GPC ['giftgroupid'] );
			$wsql = "SELECT * FROM " . tablename ($this->gifttable) . " WHERE weid = '{$_W['weid']}' and groupid = {$giftgroupid} ORDER BY sort ASC";
			$gifts = pdo_fetchall ( $wsql );
			if(empty($gifts)){
				message ( '该分组下没有奖品信息，将跳转到增加奖品页面！', $this->createWebUrl ( 'giftset', array ('op' => 'post','groupid'=>$giftgroupid) ), 'error' );
			}
		}
		include $this->template ( 'giftgroupset' );
	}
	
	/**
	 * 批量修改奖品信息
	 */
	public function doWebBatchGift(){
		global $_W, $_GPC;
		$groupid = $_GPC ['groupid'];
		if (! empty ( $_GPC ['giftarray'] )) {
			foreach ( $_GPC ['giftarray'] as $index => $row ) {
				if (empty ( $row )) {
					continue;
				}
				$data = array (
						'stdname' => $_GPC ['stdname'] [$index],
						'unit' => $_GPC ['unit'] [$index],
						'desc' => $_GPC ['desc'] [$index],
						'needscore' => $_GPC ['needscore'] [$index],
						'sort' => $_GPC ['sort'] [$index],
						'left' => $_GPC ['left'] [$index],
						'amount' => $_GPC ['amount'] [$index]
				);
				pdo_update ($this->gifttable, $data, array (
				'id' => $index
				) );
			}
		}
		message ( '更新成功！', $this->createWebUrl ( 'giftgroupset', array ('op' => 'gifts','giftgroupid' => $groupid) ), 'success' );
	}
	
	/**
	 * 用户兑奖信息
	 */
	public function doWebMemberGift(){
		global $_W;
		global $_GPC; 
		$operation = ! empty ( $_GPC ['op'] ) ? $_GPC ['op'] : 'untake';
		if ($operation == 'untake') {
			$membergifts = pdo_fetchall ( "SELECT * FROM " . tablename ($this->convertable) . " WHERE istake = 0 and weid = '{$_W['weid']}' ORDER BY themeid DESC" );
			if (checksubmit ( 'submit' )){
				$keyword = $_GPC ['keyword'];
				if(!empty($keyword)){
					$type = $_GPC ['type'];
					if($type =='code'){
						$membergifts = pdo_fetchall ( "SELECT * FROM " . tablename ($this->convertable) . " WHERE code ='{$keyword}' and  istake = 0 and weid = '{$_W['weid']}' " );
					}else{
						$membergifts = pdo_fetchall ( "SELECT * FROM " . tablename ($this->convertable) . " WHERE codetime = '{$keyword}' and istake = 0 and weid = '{$_W['weid']}' " );
					}
				}
			}
		} else if ($operation == 'hastake') {
			$membergifts = pdo_fetchall ( "SELECT * FROM " . tablename ($this->convertable) . " WHERE istake = 1 and weid = '{$_W['weid']}' ORDER BY themeid DESC" );
		} else if($operation == 'convert'){
			$membergiftid= intval ( $_GPC ['membergiftid'] );
			$membergiftrow = pdo_fetch ( "SELECT * FROM " . tablename ($this->convertable) . " WHERE id = '{$membergiftid}'");
			if(!$membergiftrow){
				message ( '抱歉，找不到奖品信息！', '', 'error' );
			}
			pdo_update ($this->convertable,array ('istake' => 1) , array ('id' => $membergiftid) );
			message ( '奖品状态置为已领', $this->createWebUrl ( 'membergift', array ('op' => 'untake') ), 'success' );

		} else if ($operation == 'delete') {
			$membergiftid = intval ( $_GPC ['membergiftid'] );
			$row = pdo_fetch ( "SELECT id FROM " . tablename ($this->convertable) . " WHERE id = " . $membergiftid );
			if (empty ( $row )) {
				message ( '抱歉，信息不存在或是已经被删除！' );
			}
			pdo_delete ($this->convertable, array (
			'id' => $membergiftid
			) );
			message ( '删除成功！', referer (), 'success' );
		}
		include $this->template ( 'membergift' );
	
	
	}
	
	/**
	 * 手机端兑奖
	 */
	public function doMobileConvert(){
		global $_W, $_GPC;
		$themeid = intval($_GPC ['themeid']);
		if(empty($themeid) || $themeid <= 0){
			message ( '入口不正确' );
		}
		$openid = $_W ['fans'] ['from_user'];
		$giftid = intval($_GPC ['giftid']);
		if(empty($giftid) || $giftid <= 0){
			message ( '入口不正确' );
		}
		$cookid=$_COOKIE['GOSHARECOOKID']; 
		$member;
		if(empty ($openid) || $openid == null || strlen($openid) != 28){
			if(empty($cookid)){
				message ( '请关注我们再参加活动吧', $this->createMobileUrl ( 'index', array ('whoshare' => $openid,'themeid' => $themeid) ), 'error' );
			}else{
				$member = pdo_fetch ( "SELECT * FROM " . tablename ($this->membertable) . " WHERE cookieid = '{$cookid}'" );
				if(!$member){
					message ( '请关注我们再参加活动吧！', $this->createMobileUrl ( 'index', array ('whoshare' => $openid,'themeid' => $themeid) ), 'error' );
					}
				}
		}else{
			$member = pdo_fetch ( "SELECT * FROM " . tablename ($this->membertable) . " WHERE openid = '{$openid}'" );
		}
		$gift = pdo_fetch ( "SELECT * FROM " . tablename ($this->gifttable) . " WHERE id = '{$giftid}'" );
		if(!$gift || $gift[left] <= 0){
			message ( '对不起，奖品已经没有了！', $this->createMobileUrl ( 'index', array ('whoshare' => $openid,'themeid' => $themeid) ), 'error' );
		}
		if($member[score] >= $gift[needscore]){
			if($this->isFans()){
				//会员积分减去奖品所需分数
				$memberupdate = array ();
				$memberupdate ['score'] = $member[score] - $gift[needscore];
				$whereArr1 = array (
						'themeid' => $themeid,
						'weid' =>$_W['weid'],
						'openid' => $openid
				);
				pdo_update ($this->membertable, $memberupdate, $whereArr1);
		
				//奖品数目减一
				$giftupdate = array ();
				$giftupdate ['left'] = $gift[left] - 1;
				$whereArr2 = array (
						'id' => $gift[id]
				);
				pdo_update ($this->gifttable, $giftupdate, $whereArr2);
				$themetemp = pdo_fetch ( "SELECT * FROM " . tablename ($this->themetable) . " WHERE id = '{$themeid}'" );
				//生成会员奖品记录
				$convertable = array (
						'weid' => $_W ['weid'],
						'themeid' => $themeid,
						'themename' => $themetemp[themename],
						'openid' => $openid,
						'giftid' => $giftid,
						'giftname' => $gift[stdname],
						'code' => $this->makeRandomCode(),
						'codetime' => time(),
						'istake' => 0,
						'cookieid' =>$cookid
				);
				pdo_insert ($this->convertable, $convertable );
				message ( $gift[stdname].'兑换成功！赶快联系我们吧，逾期不领视为自动放弃哦！', $this->createMobileUrl ( 'index', array ('whoshare' => $openid,'themeid' => $themeid) ), 'success' );
			}else{
				message ( '亲，请从我们公众号界面进入兑奖页面吧！', $this->createMobileUrl ( 'index', array ('whoshare' => $openid,'themeid' => $themeid) ), 'error' );
			}
		}else{
			message ( '积分不够哦，让小伙伴帮你分享吧！', $this->createMobileUrl ( 'index', array ('whoshare' => $openid,'themeid' => $themeid) ), 'error' );
		}
	}
	
	/**
	 * 是否为当前公众号关注者
	 * @return unknown
	 */
	private function isFans(){
		global $_W;
		$memberfansql = "select * from " . tablename ('fans') . " where weid = '{$_W['weid']}' and from_user = '{$_W ['fans'] ['from_user']}' and follow = 1 ";
		$fans = pdo_fetch ( $memberfansql );
		return $fans;
	}
	
	private function makeRandomCode( $length = 6 ){
		// 密码字符集，可任意添加你需要的字符
		$chars = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h',
				'i', 'j', 'k', 'l','m', 'n', 'o', 'p', 'q', 'r', 's',
				't', 'u', 'v', 'w', 'x', 'y','z', 'A', 'B', 'C', 'D',
				'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L','M', 'N', 'O',
				'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y','Z',
				'0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
	
		// 在 $chars 中随机取 $length 个数组元素键名
		$keys = array_rand($chars, $length);
		$password = '';
		for($i = 0; $i < $length; $i++){
		// 将 $length 个数组元素连接成字符串
			$password .= $chars[$keys[$i]];
		}
		return $password;
	}
	
	public function makeNewCookieid(){
		$cookid;
		do {
			$cookid = $this->makeRandomCode(28);
			$dbcookie = pdo_fetch ( "select * from " . tablename ($this->cookietable) . " where cookieid = '{$cookid}'   ");
		} while (!empty($dbcookie));
		return $cookid;
	}
	
	public function doMobileList() {
		global $_W, $_GPC;
		$condition = '';
		$list = pdo_fetchall ( "SELECT * FROM " . tablename ($this->themetable) . " WHERE weid = '{$_W['weid']}' $condition ORDER BY id DESC" );
		include $this->template('list');
	}
	
	
	public function doMobileIndex() {
		global $_GPC, $_W;
		$themeid = intval($_GPC ['themeid']);
		if(empty($themeid) || $themeid <= 0){
			message ( '入口不正确' );
		}
		$whoshare = $_W ['fans'] ['from_user'];
		$cookid = '';
		if(!$whoshare){//获取不到openid
			if (!empty($_COOKIE['GOSHARECOOKID'])){//cook不为空，说明不是首次进入
				$cookid=$_COOKIE['GOSHARECOOKID'];
			}else{//为空
				$cookid = $this->makeNewCookieid();
				setcookie("GOSHARECOOKID",$cookid,time()+30*7*24*3600);
			}
			$sqlcook = "select * from " . tablename ($this->cookietable) . " where cookieid = '{$cookid}' and themeid = {$themeid}  and weid = {$_W['weid']} ";
			$dbcookie = pdo_fetch ($sqlcook);
			if(!$dbcookie){
				$cookieinfo = array (
						'themeid' => $themeid,
						'weid'=>$_W['weid'],
						'cookieid' => $cookid
				);
				pdo_insert ($this->cookietable, $cookieinfo );
			}
		}else{//可以获取openid
			$dbcookie = pdo_fetch ( "select * from " . tablename ($this->cookietable) . " where openid = '{$whoshare}' and themeid = {$themeid}  and weid = {$_W['weid']} ");
			if($dbcookie){
				if (!empty($_COOKIE['GOSHARECOOKID'])){
					$cookid=$_COOKIE['GOSHARECOOKID'];
					if(!empty($dbcookie[cookieid]) && $dbcookie[cookieid] != $cookid){
						$cookid = $dbcookie[cookieid];
						setcookie("GOSHARECOOKID",$cookid,time()+30*7*24*3600);
					}
					
				}else{//为空
					$cookid = $dbcookie[cookieid];
					setcookie("GOSHARECOOKID",$cookid,time()+30*7*24*3600);
				}
			}else{//db null 
				if (!empty($_COOKIE['GOSHARECOOKID'])){
					$cookid=$_COOKIE['GOSHARECOOKID'];
				}else{
					$cookid = $this->makeNewCookieid();
					setcookie("GOSHARECOOKID",$cookid,time()+30*7*24*3600);
				}
				$cookieinfo = array (
						'themeid' => $themeid,
						'weid'=>$_W['weid'],
						'openid' => $whoshare,
						'cookieid' => $cookid
				);
				pdo_insert ($this->cookietable, $cookieinfo );
			}
			$cookresult = pdo_fetch ( "SELECT count(*) as cnt FROM " . tablename ($this->cookietable) . " WHERE weid = '{$_W['weid']}' and themeid = {$themeid} and cookieid = '{$cookid}' " );
			if($cookresult ['cnt'] > 0){
				$cookresult2 = pdo_fetch ( "SELECT count(*) as cnt FROM " . tablename ($this->cookietable) . " WHERE weid = '{$_W['weid']}' and themeid = {$themeid} and openid = '{$whoshare}' " );
				if($cookresult2 ['cnt'] != $cookresult ['cnt']){
					$ckarrayinfo  = array(
							'openid' => $whoshare
					);
					$whereArrck = array (
							'themeid' => $themeid,
							'weid' =>$_W['weid'],
							'cookieid' => $cookid
					);
					pdo_update ($this->cookietable, $ckarrayinfo, $whereArrck);
				}
			}
			$memberresult = pdo_fetch ( "SELECT count(*) as cnt FROM " . tablename ($this->membertable) . " WHERE weid = '{$_W['weid']}' and themeid = {$themeid} and cookieid = '{$cookid}' " );
				
			if($memberresult ['cnt'] > 0 ){
				$memberresult2 = pdo_fetch ( "SELECT count(*) as cnt FROM " . tablename ($this->membertable) . " WHERE weid = '{$_W['weid']}' and themeid = {$themeid} and openid = '{$whoshare}' " );
				if($memberresult2 ['cnt'] != $memberresult ['cnt']){
					$ckarrayinfo  = array(
							'openid' => $whoshare
					);
					$whereArrck = array (
							'themeid' => $themeid,
							'weid' =>$_W['weid'],
							'cookieid' => $cookid
					);
					pdo_update ($this->membertable, $ckarrayinfo, $whereArrck);
				}
				
			}
			$tranrresult = pdo_fetch ( "SELECT count(*) as cnt FROM " . tablename ($this->trantable) . " WHERE weid = '{$_W['weid']}' and themeid = {$themeid} and cookieid = '{$cookid}'  " );
			if($tranrresult ['cnt'] > 0 ){
				$tranrresult2 = pdo_fetch ( "SELECT count(*) as cnt FROM " . tablename ($this->trantable) . " WHERE weid = '{$_W['weid']}' and themeid = {$themeid} and openid = '{$whoshare}'  " );
				if($tranrresult2 ['cnt'] != $tranrresult ['cnt']){
					$ckarrayinfo  = array(
							'openid' => $whoshare
					);
					$whereArrck = array (
							'themeid' => $themeid,
							'weid' =>$_W['weid'],
							'cookieid' => $cookid
					);
					pdo_update ($this->trantable, $ckarrayinfo, $whereArrck);
				}
			}
			$tranrresult = pdo_fetch ( "SELECT count(*) as cnt FROM " . tablename ($this->trantable) . " WHERE weid = '{$_W['weid']}' and themeid = {$themeid} and helpcookid = '{$cookid}'  " );
			if($tranrresult ['cnt'] > 0){
				$tranrresult2 = pdo_fetch ( "SELECT count(*) as cnt FROM " . tablename ($this->trantable) . " WHERE weid = '{$_W['weid']}' and themeid = {$themeid} and helpid = '{$whoshare}'  " );
				if($tranrresult ['cnt'] != $tranrresult2 ['cnt']){
					$ckarrayinfo  = array(
							'helpid' => $whoshare
					);				
					$whereArrck = array (
							'themeid' => $themeid,
							'weid' =>$_W['weid'],
							'helpcookid' => $cookid
					);
					pdo_update ($this->trantable, $ckarrayinfo, $whereArrck);
				}
			}
		}
		
		$addshare = $_GPC ['whoshare'];
		$addsharecookie = $_GPC ['whosharecookie'];
		if (empty($addshare) && empty($addsharecookie)) {
			$addshare = $whoshare;
		}
		if ($addsharecookie == null) {
			$addsharecookie = $_COOKIE['GOSHARECOOKID'];
		}
		
		
		$theme = pdo_fetch ( "select * from " . tablename ($this->themetable) . " where id = {$themeid}  and weid = {$_W['weid']} ");
		$giftlist = pdo_fetchall ( "SELECT * FROM " . tablename ($this->gifttable) . " where groupid = {$theme[groupid]} and weid = {$_W['weid']} ORDER BY sort ASC " );
		$member = pdo_fetch( "select * from " . tablename ($this->membertable) . " where themeid = {$themeid}  and weid = {$_W['weid']}  and (cookieid ='{$_COOKIE['GOSHARECOOKID']}' or openid ='{$whoshare}') ");

		$memgiftlist = pdo_fetchall ( "SELECT * FROM " . tablename ($this->convertable) . " where themeid = {$themeid} and ( cookieid ='{$_COOKIE['GOSHARECOOKID']}' or openid = '{$whoshare}') and weid = {$_W['weid']} " );
		$showflower = 0;
		if(!$member){
			$member = array (
						'score' => 0
				);
		}else{
			if($member[score] >= 20){
				$showflower = 20;
			}else{
				$showflower = $member[score];
			}
		}
		$conten1 = explode("##",$theme[ad1content]);
		$conten2 = explode("##",$theme[ad2content]);
		$begintime = date("Y-m-d",strtotime($theme[begintime]));
		$endtime = date("Y-m-d",strtotime($theme[endtime]));
		$now=date("Y-m-d");
		$showtitle = "";
		if(strtotime($theme[endtime]) < strtotime($now)){
			$showtitle = $theme[overtitle];
			$leftdays = 0;
			$lefthour = 0;
			$leftmm = 0;
		}else{
			$showtitle = $theme[themetitle];
			$nowtime = time();
			$lefttime =  strtotime($theme[endtime]) - $nowtime - 28800;
			$leftdays = intval($lefttime/3600/24);
			$lefthour = date('H',$lefttime);
			$leftmm = date('i',$lefttime);
		}
		include $this->template ( 'share' );
	}

	public function doMobileShareCallback() {
		global $_GPC, $_W;
		$addshare = $_GPC ['addshare'];
		$addsharecookie = $_GPC ['addsharecookie'];
		$cookid=$_COOKIE['GOSHARECOOKID'];
		$openid = $_W ['fans'] ['from_user'];
		$themeid = intval($_GPC ['themeid']);
		if(empty($themeid) || $themeid <= 0){
			return "入口不正确";
		}
		if(!empty($openid)){//openid
			if ($addshare == $openid) { // 自己
				$memsql = "select * from " . tablename ($this->membertable) . " where weid = :weid and themeid = :themeid and ( openid = :openid or cookieid = :cookieid ) ";
				$paras = array ();
				$paras [':themeid'] = $themeid;
				$paras [':weid'] = $_W['weid'];
				$paras [':openid'] = $openid;
				$paras [':cookieid'] = $cookid;
				$memberdata = pdo_fetch ( $memsql , $paras );
				if (!$memberdata) {
					$memberinfo = array (
							'themeid' => $themeid,
							'weid'=>$_W['weid'],
							'openid' => $addshare,
							'score' => 0,
							'cookieid' =>$cookid
					);
					pdo_insert ($this->membertable, $memberinfo );
					return "恭喜！参加活动生效，快喊小伙伴帮忙转发吧！";
				}else{
					return "这一次分享一定会有收获的！";
				}
			} else { // 好友
				$desql = "select * from " . tablename ($this->trantable) . " where weid = :weid and themeid = :themeid and ( openid = :openid or cookieid = :cookieid  ) and ( helpid = :helpid or helpcookid = :helpcookid ) ";
				$paras = array ();
				$paras [':themeid'] = $themeid;
				$paras [':weid'] = $_W['weid'];
				$paras [':openid'] = $addshare;
				$paras [':cookieid'] = $addsharecookie;
				$paras [':helpid'] = $openid;
				$paras [':helpcookid'] = $cookid;
				$sharedata = pdo_fetch ($desql , $paras );
				if ($sharedata) {
					return "谢谢啦，您已经帮好友分享过啦！";
				} else {
					$detailinfo = array (
							'openid' => $addshare,
							'cookieid'=>$addsharecookie,
							'helpid' => $openid,
							'helpcookid'=>$cookid,
							'themeid' => $themeid,
							'weid'=>$_W['weid']
					);
					pdo_insert ($this->trantable, $detailinfo );
					$membersql = "select * from " . tablename ($this->membertable) . " where weid = :weid and themeid = :themeid and ( openid = :openid or cookieid = :cookieid  ) ";
					$paras = array ();
					$paras [':themeid'] = $themeid;
					$paras [':weid'] = $_W['weid'];
					$paras [':openid'] = $addshare;
					$paras [':cookieid'] = $addsharecookie;
					$memberinfo = pdo_fetch ( $membersql, $paras );
					if ($memberinfo) {
						$arrayinfo = array ();
						$arrayinfo ['score'] = $memberinfo ['score'] + 1;
						if(empty($addshare)){
							$whereArr = array (//'cookieid' => $addsharecookie
									'themeid' => $themeid,
									'weid' =>$_W['weid'],
									'cookieid' => $addsharecookie
							);
							$res = pdo_update ($this->membertable, $arrayinfo, $whereArr);
						}else{
							$whereArr = array (//'cookieid' => $addsharecookie
									'themeid' => $themeid,
									'weid' =>$_W['weid'],
									'openid' => $addshare
							);
							$res = pdo_update ($this->membertable, $arrayinfo, $whereArr);
						}
						$paras [':openid'] = $openid;
						$memberinfo2 = pdo_fetch ( $membersql, $paras );
						if(!$memberinfo2){
							$membersql2 = "select * from " . tablename ('fans') . " where weid = :weid and from_user = :from_user and follow = 1 ";
							$paras2 = array (
									'weid'=>$_W['weid'],
									'from_user' => $openid
							);
							$fans = pdo_fetch ( $membersql2, $paras2 );
							if($fans){
								$meinfo = array (
										'themeid' => $themeid,
										'weid'=>$_W['weid'],
										'openid' => $openid,
										'score' => 0,
										'cookieid' =>$cookid
								);
								pdo_insert ($this->membertable, $meinfo );
								return "恭喜！帮好友分享成功,也让你的好友帮你分享吧！";
							}else{
								return "帮好友分享成功！关注本公众号你也可以参加哟！";
							}
						}else{
							return "恭喜！帮好友分享成功,也让你的好友帮你分享吧！";
						}
					}else{
						return "好像出了点问题哦，找不到好友的活动记录哦";
					}
				}
			} 
		}else{//获取不到openid
			if(!empty($addshare)){
				if($addsharecookie == $cookid){//自己
					$memsql = "select * from " . tablename ($this->membertable) . " where weid = :weid and themeid = :themeid and cookieid = :cookieid  ";
					$paras = array ();
					$paras [':themeid'] = $themeid;
					$paras [':weid'] = $_W['weid'];
					$paras [':cookieid'] = $cookid;
					
					$memberdata = pdo_fetch ( $memsql , $paras );
					if (!$memberdata) {
						$memberinfo = array (
								'themeid' => $themeid,
								'weid'=>$_W['weid'],
								'openid' => 'openid',
								'score' => 0,
								'cookieid' =>$cookid
						);
						pdo_insert ($this->membertable, $memberinfo );
						return "恭喜！参加活动生效，快喊小伙伴帮忙分享吧！";
					}else{
						return "这一次分享肯定有收获的！";
					}
				}else{//好友
					$desql = "select * from " . tablename ($this->trantable) . " where weid = :weid and themeid = :themeid and openid = :openid and helpcookid = :helpcookid ";
					$paras = array ();
					$paras [':themeid'] = $themeid;
					$paras [':weid'] = $_W['weid'];
					$paras [':openid'] = $addshare;
					$paras [':helpcookid'] = $cookid;
					$sharedata = pdo_fetch ($desql , $paras );
					if ($sharedata) {
						return "谢谢啦，您已经帮好友转分享啦！";
					}else{
						$detailinfo = array (
								'openid' => $addshare,
								'cookieid'=>$addsharecookie,
								'helpid' => 'helpid',
								'helpcookid'=>$cookid,
								'themeid' => $themeid,
								'weid'=>$_W['weid']
						);
						pdo_insert ($this->trantable, $detailinfo );
						$membersql = "select * from " . tablename ($this->membertable) . " where weid = :weid and themeid = :themeid and openid = :openid ";
						$paras = array ();
						$paras [':themeid'] = $themeid;
						$paras [':weid'] = $_W['weid'];
						$paras [':openid'] = $addshare;
						$memberinfo = pdo_fetch ( $membersql, $paras );
						if ($memberinfo) {
							$arrayinfo = array ();
							$arrayinfo ['score'] = $memberinfo ['score'] + 1;
							$whereArr = array (
									'themeid' => $themeid,
									'weid' =>$_W['weid'],
									'openid' => $addshare
							);
							pdo_update ($this->membertable, $arrayinfo, $whereArr);
							$membersql2 = "select * from " . tablename ($this->membertable) . " where weid = :weid and themeid = :themeid and cookieid = :cookieid ";
							$paras2 = array ();
							$paras2 [':themeid'] = $themeid;
							$paras2 [':weid'] = $_W['weid'];
							$paras2 [':cookieid'] = $cookid;
							$memberinfo2 = pdo_fetch ( $membersql2, $paras2 );//查自己
							if(!$memberinfo2){
								$meinfo = array (
											'themeid' => $themeid,
											'weid'=>$_W['weid'],
											'openid' => 'openid',
											'score' => 0,
											'cookieid' =>$cookid
								);
								pdo_insert ($this->membertable, $meinfo );

							}
							return "恭喜！帮好友分享成功,也让你的好友帮你分享吧！";
						}else{
							return "好像出了点问题哦，找不到好友的活动记录哦";
						}
					}
				}
				
			}else{//addshare、openid为null
				if($addsharecookie == $cookid){//自己
					$memsql = "select * from " . tablename ($this->membertable) . " where weid = :weid and themeid = :themeid and cookieid = :cookieid  ";
					$paras = array ();
					$paras [':themeid'] = $themeid;
					$paras [':weid'] = $_W['weid'];
					$paras [':cookieid'] = $cookid;
						
					$memberdata = pdo_fetch ( $memsql , $paras );
					if (!$memberdata) {
						$memberinfo = array (
								'themeid' => $themeid,
								'weid'=>$_W['weid'],
								'openid' => 'openid',
								'score' => 0,
								'cookieid' =>$cookid
						);
						pdo_insert ($this->membertable, $memberinfo );
						return "恭喜！参加活动生效，快喊小伙伴帮忙分享吧！";
					}else{
						return "这一次分享肯定有收获的！";
					}
				}else{//好友$addsharecookie != $cookid
					$desql = "select * from " . tablename ($this->trantable) . " where weid = :weid and themeid = :themeid and cookieid = :cookieid and helpcookid = :helpcookid ";
					$paras = array ();
					$paras [':themeid'] = $themeid;
					$paras [':weid'] = $_W['weid'];
					$paras [':cookieid'] = $addsharecookie;
					$paras [':helpcookid'] = $cookid;
					$sharedata = pdo_fetch ($desql , $paras );
					if ($sharedata) {
						return "谢谢啦，您已经帮好友转分享啦！";
					}else{
						$detailinfo = array (
								'openid' => 'openid',
								'cookieid'=>$addsharecookie,
								'helpid' => 'helpid',
								'helpcookid'=>$cookid,
								'themeid' => $themeid,
								'weid'=>$_W['weid']
						);
						pdo_insert ($this->trantable, $detailinfo );
						$membersql = "select * from " . tablename ($this->membertable) . " where weid = :weid and themeid = :themeid and cookieid = :cookieid ";
						$paras = array ();
						$paras [':themeid'] = $themeid;
						$paras [':weid'] = $_W['weid'];
						$paras [':cookieid'] = $addsharecookie;
						$memberinfo = pdo_fetch ( $membersql, $paras );
						if ($memberinfo) {
							$arrayinfo = array ();
							$arrayinfo ['score'] = $memberinfo ['score'] + 1;
							$whereArr = array (
									'themeid' => $themeid,
									'weid' =>$_W['weid'],
									'cookieid' => $addsharecookie
							);
							pdo_update ($this->membertable, $arrayinfo, $whereArr);
							$membersql2 = "select * from " . tablename ($this->membertable) . " where weid = :weid and themeid = :themeid and cookieid = :cookieid ";
							$paras2 = array ();
							$paras2 [':themeid'] = $themeid;
							$paras2 [':weid'] = $_W['weid'];
							$paras2 [':cookieid'] = $cookid;
							$memberinfo2 = pdo_fetch ( $membersql2, $paras2 );//查自己
							if(!$memberinfo2){
								$meinfo = array (
										'themeid' => $themeid,
										'weid'=>$_W['weid'],
										'openid' => 'openid',
										'score' => 0,
										'cookieid' =>$cookid
								);
								pdo_insert ($this->membertable, $meinfo );
				
							}
							return "恭喜！帮好友分享成功,也让你的好友帮你分享吧！";
						}else{
							return "好像出了点问题哦，找不到好友的活动记录哦";
						}
					}
				}
			}
			
		}
	}

	/**
	 * 调查问卷入口
	 */
	public function doMobileSurvey(){
		global $_W;
		global $_GPC;
		$qu1= '以下哪个成语不是90后的杰作?hou';
		$res1= '最美不过夕阳红，温馨又从容来自后台';
		include $this->template ( 'index' );
	}
	
	

	
	public function geturl($type=1){
		switch ($type)
		{
			case 1:
				$img_url='./source/modules/goshare/template/upload/themelogo.jpg';
				break;
			case 2:
				$img_url='./source/modules/goshare/template/upload/ad3pic.jpg';
				break;
			case 3:
				$img_url='source/modules/lxymarry/template/img/open_pic.jpg';
				break;
			default:
				$img_url='./source/modules/goshare/template/upload/themelogo.jpg';
		}
		return $img_url;
	}
	
}