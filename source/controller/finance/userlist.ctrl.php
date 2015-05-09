<?php 
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

 	$group = pdo_fetchall("SELECT * FROM ".tablename('members_group')."", array(), 'id');

				$member=pdo_fetchall("SELECT m.*,n.stattime,n.endtime FROM ".tablename('members')." as m left join ".tablename('members_status')." as n  ON m.uid=n.uid   WHERE n.status=0  order by n.endtime desc ");


template('finance/userlist');
