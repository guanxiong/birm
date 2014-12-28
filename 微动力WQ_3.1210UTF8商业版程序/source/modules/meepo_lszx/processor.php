<?php
/**
 * 撸神在线模块处理程序
 *
 * @author meepo
 * @url 
 */
defined('IN_IA') or exit('Access Denied');
//defined('IN_IA') or exit('Access Denied');
include_once IA_ROOT . '/source/modules/meepo_lszx/simple_html_dom.php';
class Meepo_lszxModuleProcessor extends WeModuleProcessor {
	public function respond() {
		global $_W;
		$content = $this->message['content'];
		$openid = $this->message['from'];
		$cookie_file = dirname(__FILE__).'COOKIE.txt'.$openid;
		
				if(preg_match("/^1\+(.*)\+(.*)/",$content,$arr)){
                      $heroname=$arr[1];//取得服务器
				      $username=$arr[2];//取得玩家名称
					 $back=$this->hero($heroname,$username,$cookie_file);
					 $news="温馨提示：\n查询：英雄十人榜。\n请回复：1+英雄名+玩家名称";
					 if(!empty($back)){
						 return $this->respText($back);
					 }else{
					   $reply="/抓狂查询不到您的排名，可能是您输入的英雄名【汉字】或者玩家名称有误，或者是您没有入围英雄十人榜！\n".$news;
					   return $this->respText($reply);
					 }
					 
				}elseif(preg_match("/2\+(.*)\+(.*)/",$content,$arr)){
				      $server=$arr[1];
				      $username=$arr[2];
					   $back=$this->zhandouli($server,$username,$cookie_file);
					   $news="温馨提示：\n查询：战斗力排行榜。\n请回复：2+服务器名称+玩家名称\n如：2+电信一+TNT丶武警";
					 if($back!=""){
						 return $this->respText($back);
					 }else{
					   $reply="/抓狂您输入的服务器名、玩家名称有错，或者您没有入围国内战斗力排行榜1000人大名单！\n请核实您的输入信息！\n".$news;
					   return $this->respText($reply);
					 }
				}elseif(preg_match("/3\+(.*)\+(.*)/",$content,$arr)){
					  $server=$arr[1];
				      $username=$arr[2];
					   $back=$this->paiwei($server,$username,$cookie_file);
					   $news="温馨提示：\n查询：排位赛高手榜。\n请回复：3+服务器名称+玩家名称\n如：3+电信一+TNT丶武警";
					 if(!empty($back)){
						 return $this->respText($back);
					 }else{
					   $reply="/抓狂请输入的服务器名或者玩家名称有误！\n".$news;
					   return $this->respText($reply);
					 }
				}elseif(preg_match("/4\+(.*)\+(.*)/",$content,$arr)){
					  $server=$arr[1];
				      $username=$arr[2];
					   $back=$this->updateall($server,$username,$cookie_file);
					   $news="温馨提示：\n查询：个人战绩\n请回复：4+服务器名称+玩家名称\n如：4+电信一+TNT丶武警";
					 if(!empty($back)){
						 foreach($back as $k=>$v){
							if(!empty($v)){
								$zhanji.="\n".$k."\n".$v;
							}else{
							   $v="/难过，木有查到您的该项数据！";
							   $zhanji.="\n".$k."\n".$v;
							}
						   
	            
						 }
						 $reply="亲爱的".$username."\n您的个人战绩如下所示：\n/鼓掌/鼓掌/鼓掌".$zhanji;
						 return $this->respText($reply);
					 }else{
					 $news="温馨提示：\n查询：个人战绩。\n请回复：4+服务器名称+玩家名称\n如：4+电信一+TNT丶武警";

					   $reply="/抓狂请输入的服务器名或者玩家名称有误！\n".$news;
					   return $this->respText($reply);
					 }
				}elseif(preg_match("/5\+(.*)\+(.*)/",$content,$arr)){
					  $server=$arr[1];
				      $username=$arr[2];
					   $back=$this->getzhanji($server,$username,$cookie_file);
					   $news="温馨提示：\n查询：最近四场比赛情况\n请回复：5+服务器名称+玩家名称\n如：5+电信一+TNT丶武警";
					 if(!empty($back)){
						 foreach($back as $k=>$v){
							if(!empty($v)){
								$zhanji.="\n".$k."\n".$v;
							}else{
							   $v="/难过，木有查到您的该项数据！";
							   $zhanji.="\n".$k."\n".$v;
							}
						   
	            
						 }
						 $reply="亲爱的".$username."\n您的最近4场比赛战况如下所示：\n/鼓掌".$zhanji;
						 return $this->respText($reply);
					 }else{
					 $news="温馨提示：\n查询：个人战绩。\n请回复：5+服务器名称+玩家名称\n如：5+电信一+TNT丶武警";

					   $reply="/抓狂请输入的服务器名或者玩家名称有误！\n".$news;
					   return $this->respText($reply);
					 }
				}else{
				 $news=array(title=>"L神在线",description=>"三大榜单，演绎L神传奇！\n英雄十人榜、请回复\n【1+英雄名+玩家名称】\n战斗力排行榜、请回复\n【2+游戏区+玩家名称】\n排位赛高手榜、请回复\n【3+游戏区+玩家名称】\n个人战绩、请回复\n【4+游戏区+玩家名称】\n\n最近比赛战绩、请回复\n【5+游戏区+玩家名称】\n例如：\n1+赵信+叫我官人丶\n2+电信一+丶夕阳\n3+网通一+Susanr灬项凌\n4+电信一+萌萌的西西最可爱\n5+电信一+萌萌的西西最可爱\n特别声明：\n英雄名：格式为：汉字【对不上可就查不到哦！】\n如：：赵信，盖伦，泰达米尔，阿木木\n电信游戏区\n请输入：如：电信一....\n网通游戏区\n请输入：如：网通一.... ",picurl=>$_W['siteroot']."/source/modules/meepo_lszx/template/title.jpg");
				 return $this->respNews($news);
				
				}
	

	}
/************************以上回复逻辑暂时这么写***********************************/
/*
*英雄十人榜
*
*/
private function hero($heroname,$username,$cookie_file){
	//print_r($heroname);
	//die();
	switch($heroname){
				case '赵信':
				$server1="xinzhao";
				break;
				case '盖伦':
				$server1="Garen";
				break;
				case '易':
				$server1="MasterYi";
				break;
				case '泰达米尔':
				$server1="Tryndamere";
				break;
				case '孙悟空':
				$server1="MonkeyKing";
				break;
				case '赛恩':
				$server1="Sion";
				break;
				case '薇恩':
				$server1="Vayne";
				break;
				case '阿木木':
				$server1="Amumu";
				break;
				case '艾希':
				$server1="Ashe";
				break;
				case '墨菲特':
				$server1="Malphite";
				break;
				case '崔丝塔娜':
				$server1="Tristana";
				break;
				case '凯南':
				$server1="Kennen";
				break;
				case '普朗克':
				$server1="Gangplank";
				break;
				case '拉莫斯':
				$server1="Rammus";
				break;
				case '伊泽瑞尔':
				$server1="Ezreal";
				break;
				case '凯特琳':
				$server1="Caitlyn";
				break;
				case '瑞兹':
				$server1="Ryze";
				break;
				case '厄运小姐':
				$server1="MissFortune";
				break;
				case '卡特琳娜':
				$server1="Katarina";
				break;
				case '阿卡丽':
				$server1="Akali";
				break;
				
				default:
				$server1="阿卡丽";
				break;
			}
	 $url='http://lolbox.duowan.com/heroTop10Players.php?serverName=&playerName=&hero='.$server1;
	$refer='http://lolbox.duowan.com/heroesRank.php?serverName=&playerName=';
	$html=$this->Curl_get($url,$cookie_file,$refer);
	$html=$this->Get_td_array($html);
	for($i=0;$i<count($html);$i++){
		if(trim($html[$i][1])==$username){
	$reply="/奋斗/奋斗亲爱的\n".trim($html[$i][1])."\n你在英雄十人榜【".trim($html[$i][2])."区】榜上有名哦！\n目前排行是第".$i."名哦！\n".$server1."\n英雄胜率:".trim($html[$i][3])."\n最近使用总场次".trim($html[$i][4])."\n段位 / 胜点为：".trim($html[$i][5])."\n请继续加油，早日登上撸神英雄十人榜前三甲";
		return $reply;
		}

	
	
	}
	unset($html);    
	@unlink($cookie_file);

 
}
/*
*
*战斗力排行榜
*
*/
private function zhandouli($server,$username,$cookie_file){

    $url='http://lolbox.duowan.com/zdlRankData.php?serverName='.urlencode($server);
	$refer='http://lolbox.duowan.com/zdlRank.php';
	$html=$this->Curl_get($url,$cookie_file,$refer);
	$html_array=json_decode($html,true);//得到了一个以为数组，含9929个元素
	
		$a=$html_array['data'];
		switch($server){
				case '电信一':
				$server1="艾欧尼亚";
				break;
				case '电信二':
				$server1="祖安";
				break;
				case '电信三':
				$server1="诺克萨斯";
				break;
				case '电信四':
				$server1="班德尔城";
				break;
				case '电信五':
				$server1="皮尔特沃夫";
				break;
				case '电信六':
				$server1="战争学院";
				break;
				case '电信七':
				$server1="巨神峰";
				break;
				case '电信八':
				$server1="雷瑟守备";
				break;
				case '电信九':
				$server1="裁决之地";
				break;
				case '电信十':
				$server1="黑色玫瑰";
				break;
				case '电信十一':
				$server1="暗影岛";
				break;
				case '电信十二':
				$server1="钢铁烈阳";
				break;
				case '电信十三':
				$server1="均衡教派";
				break;
				case '电信十四':
				$server1="水晶之痕";
				break;
				case '电信十五':
				$server1="影流";
				break;
				case '电信十六':
				$server1="守望之海";
				break;
				case '电信十七':
				$server1="征服之海";
				break;
				case '电信十八':
				$server1="卡拉曼达";
				break;
				case '电信十九':
				$server1="皮城警备";
				break;
				case '网通一':
				$server1="比尔吉沃特";
				break;
				case '网通二':
				$server1="德玛西亚";
				break;
				case '网通三':
				$server1="弗雷尔卓德";
				break;
				case '网通四':
				$server1="无畏先锋";
				break;
				case '网通五':
				$server1="恕瑞玛";
				break;
				case '网通六':
				$server1="扭曲丛林";
				break;
				case '教育一':
				$server1="教育网专区";
				
				break;
				default:
				$server1="教育网专区";
				break;
			}
		$b=count($html_array['data']);
		for($i=0;$i<=$b;$i++){
		if($a[$i]['pn']==$username){
			 
			 if((int)$i>501 && (int)$i<9929){
				 $count=$i+1;
                $reply="/奋斗/奋斗/奋斗亲爱的\n".$a[$i]['pn']."\n你在".$server1."【".$server."区】榜上有名哦！\n目前排行是第".$count."名哦！\n战斗力为：".$a[$i]['s']."\n请继续加油，早日登上撸神五百强！\n你前面的两位目前战斗力分别为：".$a[$i-1]['s']."和".$a[$i-2]['s']."\n后面两位战斗力分别为：".$a[$i+1]['s']."和".$a[$i+2]['s'];
		        return $reply;
			 }elseif((int)$i>101 && (int)$i<502){
				 $count=$i+1;
             $reply="/奋斗/奋斗亲爱的\n".$a[$i]['pn']."\n你在".$server1."【".$server."区】榜上有名哦！\n目前排行是第".$count."名哦！\n战斗力为：".$a[$i]['s']."\n你太厉害了，已经荣登战斗力排行榜五百强，请继续努力入围国内百强!";
		     return $reply;
		     }elseif((int)$i<102){
				 $count=$i+1;
             $reply="/色亲爱的\n".$a[$i]['pn']."\n你在".$server1."【".$server."区】榜上有名哦！\n目前排行是第".$count."名哦！\n战斗力为：".$a[$i]['s']."\n你才是真正的撸神，已经荣登战斗力排行榜百强，有木有胆量冲击国内三强？\n三强战斗力分别为：\n第一名的战斗力为：".$a[0]['s']."\n第二名的战斗力为：".$a[1]['s']."\n第三名战斗力为：".$a[2]['s'];
		     return $reply;
		     }
		 
		}
	}
	return "";
	unset($html_array);
	@unlink($cookie_file);

}
/*
*
*排位赛高手榜
*
*/
private function paiwei($server,$username,$cookie_file){
    $url='http://lolbox.duowan.com/rankScoreRankData.php?serverName='.urlencode($server).'&rankType=R_S_5';
	$refer='http://lolbox.duowan.com/rankScoreRank.php';
	$html=$this->Curl_get($url,$cookie_file,$refer);
	$html_array=json_decode($html,true);//得到了一个以为数组，含9929个元素
	
		$a=$html_array['data'];
		//print_r($a);
		$server1="";
		//print_r($a);
		$b=count($html_array['data']);
		for($i=0;$i<=$b;$i++){
		if($a[$i]['pn']==$username){
			 
			 
			// $i=$i+1;
			 if((int)$i>501 && (int)$i<9929){
				
				 $count=$i+1;
                $reply="/奋斗/奋斗亲爱的\n".$a[$i]['pn']."\n你在".$server1."【".$server."区】榜上有名哦！\n目前排行是第".$count."名哦！\n段位/胜点为：".$shengdian."\n请继续加油，早日登上5v5排位赛高手榜五百强！";
		        return $reply;
			 }elseif((int)$i>101 && (int)$i<502){
				
				 $count=$i+1;
             $reply="/奋斗亲爱的\n".$a[$i]['pn']."\n你在".$server1."【".$server."区】榜上有名哦！\n目前排行是第".$count."名哦！\n段位/胜点为：".$shengdian."\n你太厉害了，已经荣登5v5排位赛高手榜五百强，请继续努力入围国内百强!";
		     return $reply;
		     }elseif((int)$i<102){
				if($a[$i]['tier']=5 && $a[$i]['rank']=4){
					  $shengdian="钻石 / V / ".$a[$i]['league_points'];
				  }elseif($a[$i]['tier']=5 && $a[$i]['rank']=3){
					   $shengdian="钻石 / IV /  ".$a[$i]['league_points'];
				  
				  }elseif($a[$i]['tier']=5 && $a[$i]['rank']=2){
					   $shengdian="钻石 / III / ".$a[$i]['league_points'];
				  
				  }elseif($a[$i]['tier']=5 && $a[$i]['rank']=1){
					   $shengdian="钻石 / II / ".$a[$i]['league_points'];
				  
				  }elseif($a[$i]['tier']=5 && $a[$i]['rank']=0){
					   $shengdian="钻石 / I / ".$a[$i]['league_points'];
				  
				  }elseif($a[$i]['tier']=6 && $a[$i]['rank']=0){
					   $shengdian="最强王者 /  ".$a[$i]['league_points'];
				  
				  }
				 $count=$i+1;
             $reply="/色亲爱的\n".$a[$i]['pn']."\n你在".$server1."【".$server."区】榜上有名哦！\n目前排行是第".$count."名哦！\n段位/胜点为：".$shengdian."\n你才是真正的撸神，已经荣登5v5排位赛高手榜百强，壮胆量冲击国内三强！";
		     return $reply;
		     }
		 
		}
	}
	return "";
	unset($html_array);
	@unlink($cookie_file);
}
/*
*
*针对所有用户查询战绩方法
*
*/
private function updateall($server,$username,$cookie_file){
	$url='http://lolbox.duowan.com/playerDetail.php?serverName='.urlencode($server).'&playerName='.urlencode($username);
    $refer='http://lolbox.duowan.com/playerList.php';
	$html=$this->Curl_get($url,$cookie_file,$refer);
    if(empty($html)){
	
	   return "";
	}
	 $result = str_get_html($html);
	 //echo $html;
	$zhandouli=$result->find('.info');
	foreach($zhandouli as $v){
		$arr = preg_replace("/<p[^>]*?>/is","",$v);
		$arr = preg_replace("/<a[^>]*?>/is","",$arr);
		$arr = preg_replace("/<span[^>]*?>/is","",$arr);
		$arr = str_replace("</span>","",$arr);
		$arr = str_replace("</p>","",$arr);
		$arr = str_replace("</a>","",$arr);
		//去掉 HTML 标记
		$arr = preg_replace("'<[/!]*?[^<>]*?>'si","",$arr);
		//去掉空白字符
		$arr = preg_replace("'([rn])[s]+'","",$arr);
		//$arr = str_replace(" ","",$arr);
		$arr = str_replace(" ","",$arr);
		$qian=array(" ","　","\t","\n","\r");$hou=array("","","","","");
        $arr=str_replace($qian,$hou,$arr); 
	    preg_match("/法(.*)本/",$arr,$arr1);
        $shuju["\n/:rose个人战斗力"]=$arr1[1];
	   //print_r($arr11);
	
	}
	
	$tables = $result->find('table', 2)->find('tr');
	foreach($tables as $val){
		preg_match("/<td>\<img  src=(.*) title=[\"](.*)[\"] alt=(.*)><\/td>/",$val,$arr);
		if(count($arr)==4){
		
		$hero=$arr[2];
		}
		$herozhanji=$this->Get_td_array($val);
		foreach($herozhanji as $v){
			if(count($v)==4){
		      $shuju["\n/得意最近比赛以及战况一览"].="英雄名  ".$hero."\n模式：".trim($v[1])."\n结果：".trim($v[2])."\n时间：".trim($v[3])."\n\n";
		}
		  
		
		}
		
		
	    //echo $val;
	
	}
	$ret = $result->find('ul', 0)->find('li');
	foreach($ret as $v){
	   if(strpos($v,"次")){
		   preg_match("/<img src=(.*) alt=(.*) title=(.*)>/",$v,$arr);
		   preg_match("/(.*)次/",$arr[2],$arr);
		   $arr[0]=str_replace('"','',$arr[0]);
	       $shuju['/色最近常用英雄以及次数'].=$arr[0]."\n";
		   preg_match('/(.*)次/',$arr[0],$att);
		   $att[1] = str_replace(" ","",$att[1]);
           $att[1]= preg_replace('/\d/',"",$att[1]);
		   $truehero[]= $att[1];
		   //$truehero[]=$arr[0];
	   }
	
	}
   // print_r($truehero);
	$html=$this->Get_td_array($html);


	foreach($html as $v){
	 
	  if(count($v)==6){
			  if(strpos($v[0],"典")){
				$moshi=trim($v[0]);
			  }elseif(strpos($v[0],"乱")){
			  $moshi=trim($v[0]);
			  }elseif(strpos($v[0],"机")){
			  $moshi=trim($v[0]);
            
			  }elseif(strpos($v[0],"容")){
			  $moshi=trim($v[0]);
            
			  }
		 $data[$moshi].="总场次：".trim($v[1])." "."胜率：".trim($v[2])." "."胜场：".trim($v[3])." "."负场：".trim($v[4]);
	    
	  }
	  if(count($v)==8){
	       if(strpos($v[0],"5")){
				$moshi=trim($v[0]);
				//$data[$moshi]=trim($v[3]).trim($v[4]).trim($v[5]).trim($v[6].trim($v[7]);
			  }else{
				$moshi=trim($v[0]);
				
			  }
			$data[$moshi].="总场次：".trim($v[3])." "."胜率：".trim($v[4])." "."胜场：".trim($v[5])." "."负场：".trim($v[6]);
	  }
	
	}
	/*****************************取得最近八场比赛的id*******************************/
	
	 //print_r($b);
	$result1=array_merge($data,$shuju);
	//$result2=array_merge($result1,$b);
    unset($result);
	@unlink($cookie_file);
    return $result1;
	

}

private function getzhanji($server,$username,$cookie_file){
    /*****************************取得最近八场比赛的id*******************************/
	$url='http://lolbox.duowan.com/matchList.php?serverName='.urlencode($server).'&playerName='.urlencode($username);
    $html=$this->Curl_get($url,$cookie_file,$refer);
    if(empty($html)){
	
	   return "";
	}
	 $result = str_get_html($html);
    	$ret2 = $result->find('ul', 0)->find('li');
        foreach($ret2 as $v){
	    $arr = preg_replace("/<p[^>]*?>/is","",$v);
		$arr = preg_replace("/<a[^>]*?>/is","",$arr);
		$arr = preg_replace("/<img[^>]*?>/is","",$arr);
	    preg_match("/cli[0-9]{10}/",$arr,$arr1);
		$arr1[0] = str_replace("cli","",$arr1[0]);
        $id[]=$arr1[0];
	     
	                  }
    $c=array();
    $c[0]=$id[0];
	$c[1]=$id[1];
	$c[2]=$id[2];
	$c[3]=$id[3];
	//$c[4]=$id[4];
	//$c[5]=$id[];

	//print_r($c);
	/*****************************取得最近八场比赛的id*******************************/
    foreach($c as $v){
	$url = 'http://lolbox.duowan.com/ajaxMatchDetail.php?matchId='.$v.'&queueType=CUSTOM_GAME&serverName='.urlencode($server).'&playerName='.urlencode($username);
     $html=$this->Curl_get($url,$cookie_file);
	 $xiangqing = str_get_html($html);
    	$ret2 = $xiangqing->find('.mod-tips-content');
		
		foreach($ret2 as $v){
			//$b=$v->find('text');
			//echo $v;
		//for($i=0;$i<count($truehero);$i++){
			   $arr = preg_replace("/<div[^>]*?>/is","",$v);
				$arr = preg_replace("/<strong[^>]*?>/is","",$arr);
				$arr = preg_replace("/<ul[^>]*?>/is","",$arr);
				$arr = preg_replace("/<li[^>]*?>/is","",$arr);
				$arr = preg_replace("/<img[^>]*?>/is","",$arr);
				$arr = preg_replace("/<span[^>]*?>/is","",$arr);
				$arr = str_replace("</div>","",$arr);
				$arr = str_replace("</strong>","",$arr);
				$arr = str_replace("</li>","",$arr);
				$arr = str_replace("</ul>","",$arr);
				$arr = str_replace("</span>","",$arr);
				$arr = preg_replace("'<[/!]*?[^<>]*?>'si","",$arr);
				//去掉空白字符
				$arr = preg_replace("'([rn])[s]+'","",$arr);
				//$arr = str_replace(" ","",$arr);
				$arr = str_replace(" ","",$arr);
				$qian=array(" ","　","\t","\n","\r");$hou=array("","","","","");
				$arr=str_replace($qian,$hou,$arr);
				if(preg_match('/'.$username.'(.*)/',$arr)){
					   preg_match('/'.$username.'(.*)/',$arr,$att);
                          $b['最近四场比赛详细数据一览'."\n".'/:rose/:rose/:rose'].= $att[1]."\n------------------\n";
                       
			    }
				 
				 //echo "111111111111111111111111111111111111111";
		    //}
		}
	}
   // print_r($b);
   return $b;
     

}

public function Curl_get($url,$cookie_file,$url2=""){
		//curl_get
		$ch=curl_init();

		curl_setopt($ch, CURLOPT_HEADER, 0);

		curl_setopt($ch,CURLOPT_URL,$url);
         curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/35.0.1916.153 Safari/537.36");
		   //curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (compatible; MSIE 6.0; Windows NT 5.0)");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		
		curl_setopt($ch, CURLOPT_REFERER, $url2);
		curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
		curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);

		$html=curl_exec($ch);

		curl_close($ch);

		return $html;
	
	}
public function Curl_post($url,$post,$cookie_file,$url2=""){
		//curl_post
		$ch=curl_init();

		curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/35.0.1916.153 Safari/537.36");
		//  curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (compatible; MSIE 6.0; Windows NT 5.0)");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 

		curl_setopt($ch, CURLOPT_POST, 1); 

		curl_setopt($ch, CURLOPT_POSTFIELDS, $post); 

		
       curl_setopt($ch, CURLOPT_REFERER, $url2);
		curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
		curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);

		$html=curl_exec($ch);

		curl_close($ch);

		return $html;
	
	}
	private function Get_td_array($table) {
		$table = preg_replace("/<table[^>]*?>/is","",$table);
		$table = preg_replace("/<tr[^>]*?>/si","",$table);
		$table = preg_replace("/<td[^>]*?>/si","",$table);
		$table = str_replace("</tr>","{tr}",$table);
		$table = str_replace("</td>","{td}",$table);
		//去掉 HTML 标记
		$table = preg_replace("'<[/!]*?[^<>]*?>'si","",$table);
		//去掉空白字符
		$table = preg_replace("'([rn])[s]+'","",$table);
		$table = str_replace(" ","",$table);
		$table = str_replace(" ","",$table);
	
		$table = explode('{tr}', $table);
		array_pop($table);
		foreach ($table as $key=>$tr) {
			$td = explode('{td}', $tr);
			$td = explode('{td}', $tr);
			array_pop($td);
			$td_array[] = $td;
		}
		return $td_array;
	}
    

}