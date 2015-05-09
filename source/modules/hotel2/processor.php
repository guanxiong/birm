<?php
/**
 * 微酒店
 *
 * @author WeEngine Team & ewei
 * @url
 */

defined('IN_IA') or exit('Access Denied');
class Hotel2ModuleProcessor extends WeModuleProcessor {
    public function respond() {
        global $_W;
        $this->module['config']['picurl'] = $_W['attachurl'] . $this->module['config']['picurl'];
        return $this->respNews($this->module['config']);
    }
}
