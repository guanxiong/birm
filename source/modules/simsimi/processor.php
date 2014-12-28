<?php
/**
 * 小黄鸡处理类
 *
 * 
 */
defined('IN_IA') or exit('Access Denied');

class SimsimiModuleProcessor extends WeModuleProcessor {
	public $name = 'SimsimiRobotModuleProcessor';
	public $cookie_jar;

	public function respond() {
		global $_W;
        $result = $this->xiaojo();
        if (($result['code'] == '200') && ($result['content'])) {
            return $this->respText(urldecode($result['content']));
        }else {
        	$result2 = $this->ajaxsns();
            if (($result2['code'] == '200') && ($result2['content'])) {
            	$response = json_decode(urldecode($result2['content']),true);
            	return $this->respText($response['content']);
            }else{
            	$response = $this->module['config']['xjmrhh'];
            	return $this->respText($response);
            }
        }
	}

	private function xiaojo(){
		$data = array(
			'chat' => $this->message['content']."^".time(), 
			'db' => $this->module['config']['xjdb'],
			'pw' => $this->module['config']['xjpw'],
			'from' => $this->message['from'],
			'to' => $this->message['to'],
			);
        $api = "http://www.xiaojo.com/api5.php";
        $data = ihttp_post($api,$data);
        return $data;
    }

    private function ajaxsns(){
    	$msg = urlencode($this->message['content']);
    	$api = "http://api.ajaxsns.com/api.php?key=free&appid=0&msg=%s";
    	$dat = ihttp_get(sprintf($api,$msg));
    	return $dat;
    }

}