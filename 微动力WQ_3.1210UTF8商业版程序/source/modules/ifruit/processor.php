<?php
/**
 * 水果达人抽奖模块
 * 作者:迷失卍国度
 * qq : 15595755
 */
defined('IN_IA') or exit('Access Denied');

class IfruitModuleProcessor extends WeModuleProcessor {
	
	public $name = 'IfruitModuleProcessor';

	public function isNeedInitContext() {
		return 0;
	}
	
	public function respond() {
		global $_W;
        $rid = $this->rule;

		$sql = "SELECT * FROM " . tablename('ifruit_reply') . " WHERE `rid`=:rid LIMIT 1";
		$row = pdo_fetch($sql, array(':rid' => $rid));
		if (empty($row['id'])) {
			return array();
		}

        $state = 1;//活动状态
        if(TIMESTAMP < $row['starttime']){
            return $this->respText('此活动暂未开始,敬请关注!');
        } else if (TIMESTAMP > $row['endtime']){
            $state = 0;
        }

        //默认开始封面
        $picture_start_default = $_W['siteroot'].'source/modules/ifruit/template/images/fruit-game-start_640_320.jpg';
        //默认结束封面
        $picture_end_default = $_W['siteroot'].'source/modules/ifruit/template/images/fruit-game-end_640_320.jpg';

        //封面
        $picture = $picture_start_default;
        //链接
        $url = '#';

        if($state == 1){
            //进行中
            $title = $row['title'];
            $description = $row['description'];
            $picture = !empty($row['picture']) ? $_W['attachurl'] . $row['picture']: $picture_start_default;
            $url = $_W['siteroot'] . create_url('mobile/module', array('do' => 'wapindex', 'name' => 'ifruit', 'weid' => $row['weid'], 'rid' => $rid, 'from_user' => base64_encode(authcode($this->message['from'], 'ENCODE'))));
        } else {
            //已结束
            $title = $row['title_end'];
            $description = $row['description_end'];
            $picture = !empty($row['picture_end']) ? $_W['attachurl'] . $row['picture_end']: $picture_end_default;
            $url = '#';
        }

		$response['FromUserName'] = $this->message['to'];
		$response['ToUserName'] = $this->message['from'];
		$response['MsgType'] = 'news';
		$response['ArticleCount'] = 1;
		$response['Articles'] = array();
		$response['Articles'][] = array(
			'Title' => $title,
			'Description' => $description,
			'PicUrl' => $picture,
			'Url' => $url,
			'TagName' => 'item',
		);
		return $response;
	}

	public function isNeedSaveContext() {
		return false;
	}
}
