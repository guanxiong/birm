<?php
/**
 * 婚庆定制活动
 *
 * @author  shizhongying
 * 
 * @url
 */
defined('IN_IA') or exit('Access Denied');
define('APP_PUBLIC', '/source/modules/activity/');
class ActivityModule extends WeModule {

    public $title = '推广会议议程';
    
    public $table_activity  = 'activity';
    public $table_reply  = 'activity_reply';
    
    public function fieldsFormDisplay($rid = 0) {
        global $_W;
        if($rid) {
            $reply = pdo_fetch("SELECT * FROM " . tablename($this->table_reply) . " WHERE rid = :rid", array(':rid' => $rid));
            $sql = 'SELECT * FROM ' . tablename($this->table_activity) . ' WHERE `weid`=:weid AND `id`=:aid';
            $activity = pdo_fetch($sql, array(':weid' => $_W['weid'], ':aid' => $reply['aid']));
            
            $showpicurl=$this->getpicurl($activity['ac_pic']);
        }
    
        include $this->template('form');
    }
    
    
    
    
    
    
    public function fieldsFormValidate($rid = 0) {
        global $_W, $_GPC;
        $aid = intval($_GPC['activity']);
        if(!empty($aid)) {
            $sql = 'SELECT * FROM ' . tablename($this->table_activity) . " WHERE `id`=:aid";
            $params = array();
            $params[':aid'] = $aid;
            $activity = pdo_fetch($sql, $params);
            return ;
            if(!empty($activity)) {
                return '';
            }
        }
        return '没有选择合适的推广活动';
    }
    
    private  function getpicurl($url){
        global $_W;
        if($url){
            return $_W['attachurl'].$url;
        }else{
            return $_W['siteroot'].'source/modules/ebook/images/tuisong.jpg';
        }
    
    
    }
    
    public function fieldsFormSubmit($rid) {
        global $_GPC;
        $aid = intval($_GPC['activity']);
        $record = array();
        $record['aid'] =  $aid;
        $record['rid'] = $rid;
        $reply = pdo_fetch("SELECT * FROM " . tablename($this->table_reply) . " WHERE rid = :rid", array(':rid' => $rid));
        if($reply) {
            pdo_update($this->table_reply, $record, array('id' => $reply['id']));
        } else {
            pdo_insert($this->table_reply, $record);
        }
    }
    
    public function ruleDeleted($rid) {
        pdo_delete($this->table_reply, array('rid' => $rid));
    }
	
   

}