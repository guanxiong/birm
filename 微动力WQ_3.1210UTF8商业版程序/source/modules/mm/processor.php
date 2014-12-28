<?php
/**
 * 美女报时模块处理程序
 *
 * @author Yoby
 * @url 
 */
defined('IN_IA') or exit('Access Denied');

class MmModuleProcessor extends WeModuleProcessor {
	public function respond() {
	global $_W,$_GPC;
		$content = $this->message['content'];
$sql = "select * from ".tablename('mm')." order by RAND() limit 1";
$rs = pdo_fetch($sql);
				$news[] = array(
				'title' =>'Hi,我叫'.$rs['title'],
				'description' =>'亲,请点击我查看我的详细信息...',
				'picurl' =>$_W['attachurl'] . trim($detail['thumb'], '/').$rs['img'],
				//sae   'picurl' =>$rs['img'],
				'url' =>'mobile.php?act=module&id='.$rs['id'].'&weid='.$rs['weid'].'&name=mm&do=detail'
);
return $this->respNews($news);


	}
}