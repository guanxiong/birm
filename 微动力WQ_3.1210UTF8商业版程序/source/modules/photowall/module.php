<?php
/**
 * 照片墙模块定义
 *
 * @author 珊瑚海
 * @url 
 */
defined('IN_IA') or exit('Access Denied');

class PhotowallModule extends WeModule {
	public $tablename = 'photowall_reply';
	public function fieldsFormDisplay($rid = 0) {
		//要嵌入规则编辑页的自定义内容，这里 $rid 为对应的规则编号，新增时为 0
		global $_W;
		if (!empty($rid)) {
			$reply = pdo_fetch("SELECT * FROM ".tablename($this->tablename)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));
		} else {
			$reply = array(
				'title' => '摄影比赛',
				"start_picurl" => "./source/modules/photowall/template/style/start.jpg",
				'description' => '点击消息框旁边的+，发送图片即可参加比赛,点击本消息可以查看活动图片哦',
				'isshow' => 1,
				'isdes' => 1,
				'starttime' =>time(),
				'endtime' => time() + 86400*7,
				'ticket_information' => '兑奖请联系我们，电话 13xxxxxxxxx',
				'end_theme' => '您参加的活动已经结束，敬请期待我们下一次的活动！',
				'end_picurl' => "./source/modules/photowall/template/style/activity-end.jpg",
				'end_instruction' => '本次活动已经结束，点击查看活动的图片',
				'sendtimes' => '10',
				'daysendtimes' => '2',
				'status' => '1',
			);
		}
		include $this->template('form');
	}

	public function fieldsFormValidate($rid = 0) {
		//规则编辑保存时，要进行的数据验证，返回空串表示验证无误，返回其他字符串将呈现为错误提示。这里 $rid 为对应的规则编号，新增时为 0
		return '';
	}

	public function fieldsFormSubmit($rid) {
		//规则验证无误保存入库时执行，这里应该进行自定义字段的保存。这里 $rid 为对应的规则编号
		global $_GPC, $_W;
        $id = intval($_GPC['reply_id']);

        $insert = array(
            'rid' => $rid,
            'weid' => $_W['weid'],
            'title' => $_GPC['title'],
            'ticket_information' => $_GPC['ticket_information'],
            'description' => $_GPC['description'],
            //'start_picurl' => $_GPC['start_picurl'],
            'end_theme' => $_GPC['end_theme'],
            'end_instruction' => $_GPC['end_instruction'],
            //'end_picurl' => $_GPC['end_picurl'],
            'createtime' => time(),
            'copyright' => $_GPC['copyright'],
            'isshow' => intval($_GPC['isshow']),
			'isdes' => intval($_GPC['isdes']),
			'sendtimes' => intval($_GPC['sendtimes']),
			'daysendtimes' => intval($_GPC['daysendtimes']),
            'starttime' => strtotime($_GPC['datelimit-start']),
            'endtime' => strtotime($_GPC['datelimit-end'])
        );

        if (!empty($_GPC['start_picurl'])) {
            $insert['start_picurl'] = $_GPC['start_picurl'];
            file_delete($_GPC['start_picurl-old']);
        }

        if (!empty($_GPC['end_picurl'])) {
            $insert['end_picurl'] = $_GPC['end_picurl'];
            file_delete($_GPC['end_picurl-old']);
        }

        if (empty($id)) {
        	if ($insert['starttime'] <= time()) {
                $insert['status'] = 1;
            } else {
                $insert['status'] = 0;
            }
            $id = pdo_insert($this->tablename, $insert);
        } else {
            pdo_update($this->tablename, $insert, array('id' => $id));
        }
        return true;
	}

	public function ruleDeleted($rid) {
		//删除规则时调用，这里 $rid 为对应的规则编号
		pdo_delete('photowall_reply', array('rid' => $rid));
        pdo_delete('photowall_data', array('rid' => $rid));
        pdo_delete('photowall_comment', array('rid' => $rid));
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
        message('规则操作成功！', create_url('site/module/list', array('name' => 'photowall')), 'success');
    }

    public function dopicdelete() {
        global $_GPC, $_W;
        $id = intval($_GPC['id']);
        $pic = pdo_fetch("SELECT id FROM " . tablename('photowall_data') . " WHERE id = :id and weid=:weid", array(':id' => $id, ':weid' => $_W['weid']));
        if (empty($pic)) {
            message('抱歉，要删除的图片不存在或是已经被删除！');
        }
        if (pdo_delete('photowall_data', array('id' => $id))) {
            pdo_delete('photowall_comment', array('pid' => $id));
        }
        message('图片删除成功！', $this->createWebUrl('display', array('rid' => $_GPC['rid'])), 'success');
    }

    public function dopicedit() {
        global $_GPC, $_W;
        $id = intval($_GPC['id']);
        $rid = intval($_GPC['rid']);
        $list = pdo_fetch("SELECT * FROM " . tablename('photowall_data') . " WHERE id = :id and weid=:weid", array(':id' => $id, ':weid' => $_W['weid']));
        if (empty($list)) {
            message('抱歉，要修改的图片不存在或是已经被删除！');
        }
        include $this->template('picedit');
        //message('图片删除成功！', $this->createWebUrl('display', array('rid' => $_GPC['rid'])), 'success');
    }

    public function dodeleteAll() {
        global $_GPC, $_W;

        foreach ($_GPC['idArr'] as $k => $rid) {
            $rid = intval($rid);
            if ($rid == 0)
                continue;
            $rule = pdo_fetch("SELECT id, module FROM " . tablename('rule') . " WHERE id = :id and weid=:weid", array(':id' => $rid, ':weid' => $_W['weid']));
            if (empty($rule)) {
                $this->message('抱歉，要修改的规则不存在或是已经被删除！');
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
        }
        $this->message('规则操作成功！', '', 0);
    }

    public function dosetshow() {
        global $_GPC, $_W;
        $rid = intval($_GPC['rid']);
        $status = intval($_GPC['status']);

        if (empty($rid)) {
            message('抱歉，传递的参数错误！', '', 'error');
        }
        $temp = pdo_update('photowall_reply', array('status' => $status), array('rid' => $rid));
        message('状态设置成功！', create_url('site/module/list', array('name' => 'photowall')), 'success');
    }

}