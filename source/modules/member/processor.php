<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

class MemberModuleProcessor extends WeModuleProcessor {
	public function respond() {
		global $_W;
		$config = $this->module['config']['rule'][$this->rule];
		if ($config['type'] == 'coupon') {
			return $this->respNews(array(
				'title' => '微优惠券',
				'description' => '加入会员享受优惠券！',
				'picurl' => !empty($config['picurl']) ? $_W['attachurl'] . $config['picurl'] : $_W['siteroot'] . '/source/modules/member/images/coupon.png',
				'url' => create_url('mobile/module/mycoupon', array('name' => 'member', 'weid' => $_W['weid'])),
			));
		} elseif ($config['type'] == 'card') {
			$member = pdo_fetch("SELECT id, cardsn FROM ".tablename('card_members')." WHERE from_user = :from_user", array(':from_user' => $this->message['from']));
			if (!empty($member)) {
				$description = '尊敬的会员您好，您的会员卡号为'.$member['cardsn']."，会员特权及活动点击进入查看。";
			} else {
				$description = '尊敬的用户您好，您尚未领取您的会员卡，享受更多特权及活动请点击领取会员卡。';
			}
			return $this->respNews(array(
				'title' => '微会员卡',
				'description' => $description,
				'picurl' => !empty($config['picurl']) ? $_W['attachurl'] . $config['picurl'] : $_W['siteroot'] . '/source/modules/member/images/card.png',
				'url' => !empty($member) ? create_url('mobile/channel', array('name' => 'home', 'weid' => $_W['weid'])) : create_url('mobile/module/card', array('name' => 'member', 'weid' => $_W['weid'])),
			));
		}
	}
}
