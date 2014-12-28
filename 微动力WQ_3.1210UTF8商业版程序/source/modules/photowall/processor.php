<?php
/**
 * 照片墙模块处理程序
 *
 * @author 珊瑚海
 * @url 
 */
defined('IN_IA') or exit('Access Denied');

class PhotowallModuleProcessor extends WeModuleProcessor {
	public function respond() {
		global $_W;
        $rid = $this->rule;
        $sql = "SELECT * FROM " . tablename('photowall_reply') . " WHERE `rid`=:rid LIMIT 1";
        $row = pdo_fetch($sql, array(':rid' => $rid));
        if (!$this->inContext) {
        	if ($row == false) {
        		return $this->respText("活动已取消...");
        	}
        	if ($row['status'] == 0) {
        		return $this->respText("活动未开始或暂停中，请稍后...");
        	}
        	if ($row['starttime'] > time()) {
        		return $this->respText("活动未开始，请等待...");
        	}
        	$endtime = $row['endtime'] + 68399;
        	if ( $endtime < time()) {
        		return $this->respNews(array(
                    'Title' => $row['end_theme'],
                    'Description' => $row['end_instruction'],
                    'PicUrl' => $_W['attachurl'].$row['end_picurl'],
                    'Url' => $this->createMobileUrl('list', array('id' => $rid)),
                    ));
        	} else {
        		$this->beginContext(1800);
        		return $this->respNews(array(
                    'Title' => $row['title'],
                    'Description' => $row['description'],
                   	'PicUrl' => $_W['attachurl'].$row['start_picurl'],
                    'Url' => $this->createMobileUrl('list', array('id' => $rid)),
                    ));
        	}
        }else{
        	$word = $this->message['content'];
			if ($word == "退出" || $word == "t") {
				$this->endContext();
				return $this->respText('您已回到普通模式');
			}elseif ($this->message['type'] == 'image') {
				$from_user = $this->message['from'];
				$picdata = ihttp_get($this->message['picurl']);
				$picurl = "photos/".$_W['account']['account']."/{$from_user}".time().".jpg";
				$upload = file_write($picurl,$picdata['content']);
				$beginToday=mktime(0,0,0,date('m'),date('d'),date('Y'));
				$endToday=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
				$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('photowall_data')." WHERE weid = '{$_W['weid']}' AND from_user = '{$from_user}' AND rid = '{$rid}'");
				if (($row['sendtimes'] != '0') && ($total >= $row['sendtimes'])) {
					$this->endContext();
					return $this->respText('本次活动一共能提交'.$row['sendtimes'].'张照片，您提交的数据已经达到上限，感谢您的参与');
				}else{
					$daytotal = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('photowall_data')." WHERE weid = '{$_W['weid']}' AND from_user = '{$from_user}' AND rid = '{$rid}' AND time >= '{$beginToday}' AND time <= '{$endToday}'");
					if (($row['daysendtimes'] != '0') && ($daytotal >= $row['daysendtimes'])) {
						$this->endContext();
						return $this->respText('本次活动每天能提交'.$row['daysendtimes'].'张照片，您今天提交的数据已经达到上限，感谢您的参与');
					}else{
						if ($row['isdes'] == '1') {
							$this->beginContext(1800);
							$_SESSION['picurl'] = $picurl;
							$_SESSION['from'] = $from_user;
							$_SESSION['time'] = time();
							return $this->respText("您的图片我们已经收到啦，请及时回复该图片的描述哦！");
						}else{
							$data = array(
								'rid' => $rid,
								'from_user' => $from_user,
								'weid' => $_W['weid'],
								'url' => $picurl,
								'description' => '暂无描述',
								'status' => 1 - $row['isshow'],
								'time' => time(),
							);
							pdo_insert('photowall_data',$data);
							$this->endContext();
							return $this->respText("您的图片我们已经收到，谢谢您的使用！");
						}
						return $this->respText($row['daysendtimes']);
					}
					
				}
				//return $this->respText($total);
			}elseif(($this->message['type'] == 'text') && !empty($_SESSION['picurl'])){
				$picurl = $_SESSION['picurl'];
				$data = array(
					'rid' => $rid,
					'from_user' => $_SESSION['from'],
					'weid' => $_W['weid'],
					'url' => $_SESSION['picurl'],
					'description' => $word,
					'status' => 1 - $row['status'],
					'time' => $_SESSION['time'],
					);
				pdo_insert('photowall_data',$data);
				$this->endContext();
				return $this->respText("您的描述信息我们收到啦，数据我们已经收录，谢谢！");
			}else{
				return $this->respText('请点击消息框旁边的+，发送图片参加比赛');
			}
        }
        
    }
}