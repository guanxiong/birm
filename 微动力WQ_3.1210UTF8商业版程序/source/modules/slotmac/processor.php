<?php

/**
 * 通用表单模块处理程序
 *
 * @author Godietion Koo
 * @url http://beidoulbs.com
 */
defined('IN_IA') or exit('Access Denied');
        
class SlotmacModuleProcessor extends WeModuleProcessor {
    public function respond() {    
        global $_W;
        $weid = $_W['weid'];
        $website = $_W['config']['site']['add'];
        
        $slotinf = pdo_fetch('SELECT repactive,reptitle,repinfo,repimg FROM '.tablename('slotmac_rep').' WHERE weid=:weid ORDER BY id DESC', array(':weid'=>$weid));
        $url = $website.'/mobile.php?act=module&do=slotmac&name=slotmac&weid='.$weid.'&macid='.$slotinf['repactive'];
           
        return $this->respNews(array(
                'Title' => $slotinf['reptitle'],
                'Description' => $slotinf['repinfo'],
                'PicUrl' => $slotinf['repimg'],
                'Url' => $url
            ));
	}
}