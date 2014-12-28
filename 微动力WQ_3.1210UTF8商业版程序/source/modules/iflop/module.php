<?php
/**
 * 翻牌抽奖
 * 作者:迷失卍国度  ZombieSzy 
 * qq:15595755  / 214983937
 */
defined ( 'IN_IA' ) or exit ( 'Access Denied' );

class iflopModule extends WeModule {
	public $name = 'iflopModule';
	public $title = '';
	public $ability = '';
	public $tablename = 'iflop_reply';
	public $modulename = 'iflop'; //模块标识
	
	
	public function fieldsFormDisplay($rid = 0) {
		global $_W;
		if (! empty ( $rid )) {
			$reply = pdo_fetch ( "SELECT * FROM " . tablename ( $this->tablename ) . " WHERE rid = :rid ORDER BY `id` DESC", array (':rid' => $rid ) );
		}
		if (! $reply) {
			$reply = array (
				"title" => "幸运翻牌抽奖活动开始了!",
				"description" => "欢迎参加幸运翻牌抽奖活动", 
				'most_num_times' => 1, 
				'number_times' => 1, 
				'c_type_five' => "安慰奖",
			    'c_name_five' => "再接再厉",
			    'c_num_five' => 50, 
			    'c_num_six' => 70, 
			    'c_type_six' => "特别奖", 
			    'c_name_six' => "继续努力", 
			    'c_rate_one' => 0, 
			    'share_times' => 1, 'share_title' => "欢迎参加幸运翻牌抽奖活动", 
			    'share_desc' => "亲，欢迎参加幸运翻牌抽奖活动，祝您好运哦！！ 亲，需要绑定账号才可以参加哦", 
			    'share_txt' => "&lt;p&gt;1. 关注微信公众账号\"()\"&lt;/p&gt;&lt;p&gt;2. 发送消息\"翻牌抽奖\", 点击返回的消息即可参加&lt;/p&gt;" ,
			);
		}
		include $this->template ( 'form' );
	}
	
	public function fieldsFormSubmit($rid = 0) {
		global $_GPC, $_W;
		
		$id = intval ( $_GPC ['reply_id'] );
		echo $id;
		
		$data = array (
			 'rid' => $rid, 
			 'weid' => $_W ['weid'], 
			 'title' => $_GPC ['title'], 
			 'picture' => $_GPC ['picture'], 
			 'description' => $_GPC ['description'], 
			 'c_type_one' => $_GPC ['c_type_one'],
		 	 'c_name_one' => $_GPC ['c_name_one'], 
		 	 'c_num_one' => $_GPC ['c_num_one'], 
			 'c_type_two' => $_GPC ['c_type_two'],
			 'c_name_two' => $_GPC ['c_name_two'], 
			 'c_num_two' => $_GPC ['c_num_two'], 
			 'c_type_three' => $_GPC ['c_type_three'], 
			 'c_name_three' => $_GPC ['c_name_three'], 
			 'c_num_three' => $_GPC ['c_num_three'], 
			 'c_type_four' => $_GPC ['c_type_four'], 
			 'c_name_four' => $_GPC ['c_name_four'], 
			 'c_num_four' => $_GPC ['c_num_four'], 
			 'c_type_five' => "安慰奖",
			 'c_name_five' => "再接再厉",
			 'c_num_five' => $_GPC ['c_num_five'], 
			 'c_type_six' => "特别奖", 
			 'c_name_six' => "继续努力", 
			 'c_num_six' => $_GPC ['c_num_six'], 
			 'number_times' => $_GPC ['number_times'], 
			 'most_num_times' => $_GPC ['most_num_times'],
			 'share_times' => $_GPC ['share_times'], 
			 'dateline' => time (), 
			 'share_title' => $_GPC ['share_title'], 
			 'share_desc' => $_GPC ['share_desc'], 
			 'share_url' => $_GPC ['share_url'], 
			 'share_txt' => $_GPC ['share_txt'], 
			 'c_rate_one' => $_GPC ['c_rate_one'] 
		);
		
		// $data['total_num'] = intval($_GPC['c_num_one']) + intval($_GPC['c_num_two']) + intval($_GPC['c_num_three']) + intval($_GPC['c_num_four']) + intval($_GPC['c_num_five']) ;
		if (empty ( $id )) {
			$id = pdo_insert ( $this->tablename, $data );
		} else {
			if (! empty ( $_GPC ['picture'] )) {
				file_delete ( $_GPC ['picture-old'] );
			} else {
				unset ( $data ['picture'] );
			}
			unset ( $data ['dateline'] );
			pdo_update ( $this->tablename, $data, array ('id' => $id ) );
		}
		return true;
	}
	
	public function fieldsFormValidate($rid = 0) {
		return true;
	}
	
	//删除规则
	public function ruleDeleted($rid = 0) {
		pdo_delete('iflop_award', array('rid' => $rid));
        pdo_delete('iflop_award', array('rid' => $rid));
        pdo_delete('iflop_winner', array('rid' => $rid));
	}
	
	public function doManage() {
		global $_GPC, $_W;
		include model ( 'rule' );
		$pindex = max ( 1, intval ( $_GPC ['page'] ) );
		$psize = 20;
		$sql = "weid = :weid AND `module` = :module";
		$params = array ();
		$params [':weid'] = $_W ['weid'];
		$params [':module'] = 'iflop';
		
		if (isset ( $_GPC ['keywords'] )) {
			$sql .= ' AND `name` LIKE :keywords';
			$params [':keywords'] = "%{$_GPC['keywords']}%";
		}
		$list = rule_search ( $sql, $params, $pindex, $psize, $total );
		$pager = pagination ( $total, $pindex, $psize );
		
		if (! empty ( $list )) {
			foreach ( $list as &$item ) {
				$condition = "`rid`={$item['id']}";
				$item ['keywords'] = rule_keywords_search ( $condition );
				$bigwheel = pdo_fetch ( "SELECT fansnum, viewnum FROM " . tablename ( 'iflop_reply' ) . " WHERE rid = :rid ", array (':rid' => $item ['id'] ) );
				$item ['fansnum'] = $bigwheel ['fansnum'];
				$item ['viewnum'] = $bigwheel ['viewnum'];
			}
		}
		include $this->template ( 'manage' );
	}
	
	
   public function dodelete() {
        global $_GPC, $_W;
        $rid = intval($_GPC['rid']);
        $rule = pdo_fetch("SELECT id, module FROM " . tablename('rule') . " WHERE id = :id and weid=:weid", array(':id' => $rid, ':weid' => $_W['weid']));
        if (empty($rule)) {
            message('抱歉，要修改的规则不存在或是已经被删除！');
        }
        if (pdo_delete('rule', array('id' => $rid))) {
            pdo_delete('rule_keyword', array('rid' => $rid));
            //删除统计相关数据
            pdo_delete('stat_rule', array('rid' => $rid));
            pdo_delete('stat_keyword', array('rid' => $rid));
            //调用模块中的删除
            $module = WeUtility::createModule($rule['module']);
            if (method_exists($module, 'ruleDeleted')) {
                $module->ruleDeleted($rid);
            }
        }
        message('规则操作成功！', create_url('site/module/manage', array('name' => 'iflop')), 'success');
    }
	
	public function dodeleteAll() {
		global $_GPC, $_W;
		
		foreach ( $_GPC ['idArr'] as $k => $rid ) {
			$rid = intval ( $rid );
			if ($rid == 0)
				continue;
			$rule = pdo_fetch ( "SELECT id, module FROM " . tablename ( 'rule' ) . " WHERE id = :id and weid=:weid", array (':id' => $rid, ':weid' => $_W ['weid'] ) );
			if (empty ( $rule )) {
				$this->message ( '抱歉，要修改的规则不存在或是已经被删除！' );
			}
			if (pdo_delete ( 'rule', array ('id' => $rid ) )) {
				pdo_delete ( 'rule_keyword', array ('rid' => $rid ) );
				//删除统计相关数据
				pdo_delete ( 'stat_rule', array ('rid' => $rid ) );
				pdo_delete ( 'stat_keyword', array ('rid' => $rid ) );
				//调用模块中的删除
				$module = WeUtility::createModule ( $rule ['module'] );
				if (method_exists ( $module, 'ruleDeleted' )) {
					$module->ruleDeleted ( $rid );
				}
			}
		}
		$this->message ( '规则操作成功！', '', 0 );
	}
	
	public function doawardlist() {
		global $_GPC, $_W;
		$rid = intval ( $_GPC ['rid'] );
		if (empty ( $rid )) {
			message ( '抱歉，传递的参数错误！', '', 'error' );
		}
		$where = '';
		$params = array (':rid' => $rid, ':weid' => $_W ['weid'] );
		if (! empty ( $_GPC ['status'] )) {
			$where .= ' and a.status=:status';
			$params [':status'] = $_GPC ['status'];
		}
		if (! empty ( $_GPC ['keywords'] )) {
			$where .= ' and f.tel like :tel ';
			$params [':tel'] = "%{$_GPC['keywords']}%";
		}
		
		$total = pdo_fetchcolumn ( "SELECT count(a.id) FROM " . tablename ( 'iflop_award' ) . " a left join " . tablename ( 'iflop_winner' ) . " f on a.fansID=f.fansID  WHERE a.rid = :rid and a.weid=:weid " . $where . "", $params );
		$pindex = max ( 1, intval ( $_GPC ['page'] ) );
		$psize = 12;
		$pager = pagination ( $total, $pindex, $psize );
		$start = ($pindex - 1) * $psize;
		$limit .= " LIMIT {$start},{$psize}";
		$list = pdo_fetchall ( "SELECT a.* FROM " . tablename ( 'iflop_award' ) . " a left join " . tablename ( 'iflop_winner' ) . " f on a.fansID=f.fansID WHERE a.rid = :rid and a.weid=:weid  " . $where . " ORDER BY a.id DESC " . $limit, $params );
		
		
		include $this->template ( 'awardlist' );
	}
	
	public function dogetphone() {
		global $_GPC, $_W;
		$rid = intval ( $_GPC ['rid'] );
		$fans = $_GPC ['fans'];
		$tel = pdo_fetchcolumn ( "SELECT tel FROM " . tablename ( 'iflop_winner' ) . " WHERE rid = " . $rid . " and  from_user='" . $fans . "'" );
		if ($tel == false) {
			echo '没有登记';
		} else {
			echo $tel;
		}
	}
	
	public function dogetsharetimes() {
		global $_GPC, $_W;
		$rid = intval ( $_GPC ['rid'] );
		$fans = $_GPC ['fans'];
		$share_times = pdo_fetchcolumn ( "SELECT share_times FROM " . tablename ( 'iflop_winner' ) . " WHERE rid = " . $rid . " and  from_user='" . $fans . "'" );
		if ($share_times == false) {
			echo 0;
		} else {
			echo $share_times;
		}
	}
	
	public function dosetstatus() {
		global $_GPC, $_W;
		$id = intval ( $_GPC ['id'] );
		$status = intval ( $_GPC ['status'] );
		if (empty ( $id )) {
			message ( '抱歉，传递的参数错误！', '', 'error' );
		}
		$p = array ('status' => $status );
		if ($status == 2) {
			$p ['consumetime'] = TIMESTAMP;
		}
		$temp = pdo_update ( 'iflop_award', $p, array ('id' => $id, 'weid' => $_W ['weid'] ) );
		if ($temp == false) {
			message ( '抱歉，刚才操作数据失败！', '', 'error' );
		} else {
			message ( '状态设置成功！', create_url ( 'site/module/awardlist', array ('name' => 'iflop', 'rid' => $_GPC ['rid'] ) ), 'success' );
		}
	}
	
  	public function message($error, $url = '', $errno = -1) {
        $data = array();
        $data['errno'] = $errno;
        if (!empty($url)) {
            $data['url'] = $url;
        }
        $data['error'] = $error;
        echo json_encode($data);
        exit;
    }

}