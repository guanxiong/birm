<?php
/**
 * 自选股模块处理程序
 *
 * @author Godietion Koo
 * @url http://beidoulbs.com
 */
defined('IN_IA') or exit('Access Denied');

class StockModuleProcessor extends WeModuleProcessor {
	public function respond() {
		global $_W,$_GPC;
		$content = $this->message['content'];
		$response = array();
		$response['FromUserName'] = $this->message['to'];
		$response['ToUserName'] = $this->message['from'];
		$response['MsgType'] = 'news';
		$sql="select * from ".tablename('optionalstock')." where weid=:weid and userid=:userid order by id desc limit 10";
		$list=pdo_fetchall($sql,array(':weid'=>$_W['weid'],':userid'=>$this->message['from']));	
		$response['ArticleCount'] = count($list)+1;
		
		$response['Articles'][1] = array(
              	'Title' => $response['ArticleCount']>1?'--> 由此进入按股票代码查询':$_W['account']['name'],
        		'Description' =>'--> 由此进入按股票代码查询',
        		'PicUrl' => 'http://image.sinajs.cn/newchart/daily/n/sz399300.gif',
        		'Url' => $_W['siteroot'].$this->createMobileUrl('index',array('userid'=>$this->message['from'])),
        		'TagName' => 'item',
		);		
		$i=2;
		foreach($list as $form){			
			$response['Articles'][$i] = array(
	              'Title' => $form['stkname'].'['.$form['stkcode']."]"."\n\n 关注价格:".$form['stkprice']."\n 关注时间:".$form['addtime'],
	        	  'Description' =>'',
	        	  'PicUrl' =>'http://image.sinajs.cn/newchart/daily/n/'.$form['imgname'].'.gif',
	        	  'Url' =>$_W['siteroot'].$this->createMobileUrl('show',array('userid'=>$this->message['from'],'stkcode'=>$form['stkcode'])),
	        	  'TagName' => 'item',
			);	
			$i++;		
		}		
		return $response;
	}

}