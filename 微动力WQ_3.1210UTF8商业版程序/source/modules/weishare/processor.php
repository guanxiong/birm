<?php
/**
 * @url 
 */
defined ( 'IN_IA' ) or exit ( 'Access Denied' );

class WeiShareModuleProcessor extends WeModuleProcessor {
	
	public $name = 'EbookModuleProcessor';
	
	
	
	public $table_reply  = 'weishare_reply';
	
	public $table_share='weishare';
	
	public $weishare_user = "weishare_user";
	
	
	
	
	public function respond() {
		global $_W;
		$rid = $this->rule;
		
		
		
		$fromuser = $this->message ['from'];
		if ($rid) {
			$reply = pdo_fetch ( "SELECT * FROM " . tablename ( $this->table_reply ) . " WHERE rid = :rid", array (':rid' => $rid ) );
			
			
			if ($reply) {
				
			   
			    
			   
			    
			    
			    
			    
				$news = array ();
				$news [] = array ('title' => $reply['new_title'], 'description' => $reply['new_desc'], 'picurl' => $this->getpicurl ( $reply ['new_pic'] ), 'url' => $this->createMobileUrl ( 'share',array ('id' => $reply ['sid'] ) )  );
				return $this->respNews ( $news );
			}
		}
		return null;
	}
	
	private function getpicurl($url) {
		global $_W;
		
		return $_W ['attachurl'] . $url;
		
	}
}

