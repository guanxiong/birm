<?php
/**
 * 360全景
 *
 * 作者:迷失卍国度
 *
 * qq : 15595755
 */
defined('IN_IA') or exit('Access Denied');
class IpanoModuleSite extends WeModuleSite {
	
	public function doMobileIndex() {
        global $_W,$_GPC;
        $rid = intval($_GPC['rid']);
        $weid = intval($_GPC['weid']);
        $reply = pdo_fetch('select * from '.tablename('ipano_reply').' where rid=:rid', array(':rid'=>$rid));
        include $this->template('index');
	}

    public function doMobileGetImageXml() {
        global $_GPC, $_W;
        header('Content-Type: text/xml;');
        $site_root = $_W['site_root'];
        $rid = intval($_GPC['rid']);
        $type = 2;
        $reply = pdo_fetch('select * from '.tablename('ipano_reply').' where rid=:rid', array(':rid'=>$rid));
        $attachurl = $_W['attachurl'];

        if($reply['type'] == -1) {
            $str = '
<panorama id="">
<view fovmode="0" pannorth="0">
    <start pan="0" fov="70" tilt="0"/>
    <min pan="0" fov="5" tilt="-90"/>
    <max pan="360" fov="120" tilt="90"/>
</view>
<userdata title="360view" datetime="2011:11:03 09:41:07" description="description" copyright="copyright" tags="tags" author="author" source="source" comment="comment" info="info" longitude="0" latitude=""/>

<media/>
<input ';
            $str .=
                'tile0url="'.$attachurl.$reply['picture1'].'"
                tile1url="'.$attachurl.$reply['picture2'].'"
                tile2url="'.$attachurl.$reply['picture3'].'"
                tile3url="'.$attachurl.$reply['picture4'].'"
                tile4url="'.$attachurl.$reply['picture5'].'"
                tile5url="'.$attachurl.$reply['picture6'].'"
       tilesize="685"
       tilescale="1.014598540145985"/>
<autorotate speed="0.200" nodedelay="0.00" startloaded="1" returntohorizon="0.000" delay="5.00"/>
<control simulatemass="1" lockedmouse="0" lockedkeyboard="0" dblclickfullscreen="0" invertwheel="0" lockedwheel="0" invertcontrol="1" speedwheel="1" sensitivity="8"/>
</panorama>
        ';
        } else {
        if(!empty($reply)) $type = $reply['type'];
        $str = '
<panorama id="">
<view fovmode="0" pannorth="0">
    <start pan="0" fov="70" tilt="0"/>
    <min pan="0" fov="5" tilt="-90"/>
    <max pan="360" fov="120" tilt="90"/>
</view>
<userdata title="dddddddd" datetime="2011:11:03 09:41:07" description="description" copyright="copyright" tags="tags" author="author" source="source" comment="comment" info="info" longitude="0" latitude=""/>

<media/>
<input ';
        $str .=
       'tile0url="./source/modules/ipano/template/images/'.$type.'/1.jpeg"
       tile1url="./source/modules/ipano/template/images/'.$type.'/2.jpeg"
       tile2url="./source/modules/ipano/template/images/'.$type.'/3.jpeg"
       tile3url="./source/modules/ipano/template/images/'.$type.'/4.jpeg"
       tile4url="./source/modules/ipano/template/images/'.$type.'/5.jpeg"
       tile5url="./source/modules/ipano/template/images/'.$type.'/6.jpeg"
       tilesize="685"
       tilescale="1.014598540145985"/>
<autorotate speed="0.200" nodedelay="0.00" startloaded="1" returntohorizon="0.000" delay="5.00"/>
<control simulatemass="1" lockedmouse="0" lockedkeyboard="0" dblclickfullscreen="0" invertwheel="0" lockedwheel="0" invertcontrol="1" speedwheel="1" sensitivity="8"/>
</panorama>
        ';
        }
        echo $str;
    }
}