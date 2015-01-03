<?php

/**

 * 微相册模块处理程序

 *

 * @author WeNewstar Team

 * @url http://www.we7.cc

 */

defined('IN_IA') or exit('Access Denied');



class LxyecowzpModuleProcessor extends WeModuleProcessor {
	public $zwlist='lxy_ecowzp';	
	public $table_reply = 'lxy_ecowzp_reply';
	public function respond() {

		$content = $this->message['content'];

		//这里定义此模块进行消息处理时的具体过程, 请查看微新星文档来编写你的代码

		$reply = pdo_fetchall("SELECT * FROM ". tablename($this->table_reply)." WHERE rid = :rid", array(':rid' => $this->rule));

		if (!empty($reply)) {

			foreach ($reply as $row) {

				$hids[$row['hid']] = $row['hid'];

			}

			$hid = pdo_fetchall("SELECT id, title, thumb, content FROM ".tablename($this->zwlist)." WHERE id IN (".implode(',', $hids).")", array(), 'id');

			$response = array();

			foreach ($reply as $row) {

				$row = $hid[$row['hid']];

				$response[] = array(
					'title' => $row['title'],
					'description' =>trim(strip_tags($row['content'])),
					'picurl' =>$this->getpicurl($row['thumb']),
					'url' => $this->createMobileUrl('showindex', array('id' => $row['id'])),

				);

			}

			return $this->respNews($response);

		}

	}
	
	
	private  function getpicurl($url) {
		global $_W;
		if($url)
		{
			return $_W['attachurl'].$url;
		}
		else 
		{
			return $_W['siteroot'].'source/modules/lxyecowzp/template/images/wzp.jpg';
		}
	}	

}