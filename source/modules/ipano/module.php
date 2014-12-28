<?php
/**
 * 360全景
 *
 * 作者:迷失卍国度
 *
 * qq : 15595755
 */
defined('IN_IA') or exit('Access Denied');
define('CONTROLLER', 'ipano');

class IpanoModule extends WeModule {
	public $name = 'IpanoModule';
	public $title = '';
	public $ability = '';
	public $tablename = 'ipano_reply';

    public $action = '';//方法
    public $modulename = 'ipano';//模块标识
    public $actions_titles = array(
    );

    public function fieldsFormSubmit($rid = 0) {
        global $_GPC, $_W;
        $id = intval($_GPC['reply_id']);
        $data = array(
            'rid' => $rid,
            'weid' => $_W['weid'],
            'title' => $_GPC['title'],
            'type' => intval($_GPC['casetype']),
            'description' => $_GPC['description'],
            'picture' => $_GPC['picture'],
            'picture1' => $_GPC['picture1'],
            'picture2' => $_GPC['picture2'],
            'picture3' => $_GPC['picture3'],
            'picture4' => $_GPC['picture4'],
            'picture5' => $_GPC['picture5'],
            'picture6' => $_GPC['picture6'],
            'music' => $_GPC['music'],
            'description' => $_GPC['description'],
            'status' => 0,
            'dateline' => TIMESTAMP
        );
        if (!empty($_FILES['picture1']['tmp_name'])) {
            file_delete($_GPC['picture1_old']);
            $upload = file_upload($_FILES['picture1']);
            if (is_error($upload)) {
                message($upload['message'], '', 'error');
            }
            $data['picture1'] = $upload['path'];
        }

        if (!empty($_FILES['picture2']['tmp_name'])) {
            file_delete($_GPC['picture2_old']);
            $upload = file_upload($_FILES['picture2']);
            if (is_error($upload)) {
                message($upload['message'], '', 'error');
            }
            $data['picture2'] = $upload['path'];
        }

        if (!empty($_FILES['picture3']['tmp_name'])) {
            file_delete($_GPC['picture3_old']);
            $upload = file_upload($_FILES['picture3']);
            if (is_error($upload)) {
                message($upload['message'], '', 'error');
            }
            $data['picture3'] = $upload['path'];
        }

        if (!empty($_FILES['picture4']['tmp_name'])) {
            file_delete($_GPC['picture4_old']);
            $upload = file_upload($_FILES['picture4']);
            if (is_error($upload)) {
                message($upload['message'], '', 'error');
            }
            $data['picture4'] = $upload['path'];
        }

        if (!empty($_FILES['picture5']['tmp_name'])) {
            file_delete($_GPC['picture5_old']);
            $upload = file_upload($_FILES['picture5']);
            if (is_error($upload)) {
                message($upload['message'], '', 'error');
            }
            $data['picture5'] = $upload['path'];
        }

        if (!empty($_FILES['picture6']['tmp_name'])) {
            file_delete($_GPC['picture6_old']);
            $upload = file_upload($_FILES['picture6']);
            if (is_error($upload)) {
                message($upload['message'], '', 'error');
            }
            $data['picture6'] = $upload['path'];
        }

        if (checksubmit('submit')) {
            if (empty($id)) {
                pdo_insert($this->tablename, $data);
            } else {
                if (!empty($_GPC['picture'])) {
                    file_delete($_GPC['picture-old']);
                } else {
                    unset($data['picture']);
                }
                unset($data['dateline']);
                pdo_update($this->tablename, $data, array('id' => $id));
            }
        }
    }

	public function fieldsFormDisplay($rid = 0) {
        global $_W;
        if (!empty($rid)) {
            $reply = pdo_fetch("SELECT * FROM ".tablename($this->tablename)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));
        }
		include $this->template('ipano/form');
	}

	public function fieldsFormValidate($rid = 0) {
		return true;
	}

    //删除规则
    public function ruleDeleted($rid = 0) {
        global $_W;
        $replies = pdo_fetchall("SELECT id FROM ".tablename($this->tablename)." WHERE rid = '$rid'");
        $deleteid = array();
        if (!empty($replies)) {
            foreach ($replies as $index => $row) {
                $deleteid[] = $row['id'];
            }
        }
        pdo_delete($this->tablename, "id IN ('".implode("','", $deleteid)."')");
        return true;
    }
}
