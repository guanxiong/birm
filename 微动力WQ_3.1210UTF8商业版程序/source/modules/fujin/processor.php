<?php
/**
 * 附近搜索模块处理程序
 *
 * @author topone4tvs
 * @url 
 */
defined('IN_IA') or exit('Access Denied');
require IA_ROOT . '/source/modules/fujin/lib/map.php';

class FujinModuleProcessor extends WeModuleProcessor {
	public function respond() {
		global $_W;
		//是否初次进入会话
		if(!$this->inContext) {
			$content = $this->message['content'];
			
			//记录搜索关键字
			$_SESSION['src'] = str_replace('附近', '', $content);
			if( '' == $_SESSION['src'] ){
				return $this->respText('请正确发送您的请求：附近+‘搜索内容’，如附近饭店');
			}
			$this->beginContext();
			return $this->respText('点击下方的“+”，发送您的地理位置。然后我们会将您要的结果反馈给您的！');
		} else {
			$srckey = $_SESSION['src'];
			$msgtp 	= $this->message['type'];
			if('location' == $msgtp){		
				$x = $this->message['location_x'];
				$y = $this->message['location_y'];
				$_SESSION['x'] = $x;
				$_SESSION['y'] = $y;
				$srcArr	 = catchEntitiesFromLocation($srckey,$x,$y,2000);
				$array   = json_decode($srcArr,true);
	            $map     = array();
	            foreach ($srcArr as $key => $vo) {
	                $map[] = array(
			                    'title' => $vo['title'],
			                    'description' => $key,
			                    'picurl' => $_W['siteroot'].'/source/modules/fujin/template/homelogo.jpg',
			                    'url' => $vo['url']
	                );
	            }
				//结束会话
				$this->endContext();
				return $this->respNews($map);
			}elseif('text' == $msgtp){
				$content = $this->message['content'];
				if( strpos($content, '附近') !== false ){
					$_SESSION['src'] = str_replace('附近', '', $content);
					$msg = '点击下方的“+”，发送您的地理位置。然后我们会将您要的结果反馈给您的！';
				}else{
					$this->endContext();
					$msg = '请正确发送您的要求！';
				}
				return $this->respText($msg);
			}else{
				$this->endContext();
				return $this->respText('请正确发送您的要求！');
			}
		}
	}
}