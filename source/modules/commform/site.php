<?php
/**
 * 通用表单模块订阅器
 *
 * @author Godietion Koo
 * @url http://beidoulbs.com/
 */
defined('IN_IA') or exit('Access Denied');
require 'pinyin.php';
class CommformModuleSite extends WeModuleSite {

	//移动端访问   
    public function doMobileshowform() {
    	global $_W,$_GPC;
    	$id=$_GPC['fid'];
    	$fn=$_GPC['fn'];
    	$userid=$_GPC['userid'];
    	$sql="select * from ".tablename('defineformfields')." where formid=:formid order by zindex asc";
    	$list=pdo_fetchall($sql,array(':formid'=>$id));
    	$form=pdo_fetch("select * from ".tablename('defineform')." where id=:id",array(':id'=>$id));
		if(checksubmit('submit')){
		 	if(empty($userid)){
    			exit('参数传入缺失，请从微信端或者<a href="test.php" >调试工具</a>页面进入!');
    		}			
			$tabname="define_".$id.'_'.Pinyin($form['name']);
			$insert=array();
			/*check*/
			foreach ($list as $row){
				if($row['require']==1 && empty($_GPC[$row['fieldname']])){
					message("【<font color='red'><b>".$row['displayname']."</b></font>】 不可为空!");
				}	
				if(!empty($row['regex']) && !empty($_GPC[$row['fieldname']])){
					preg_match($row['regex'],$_GPC[$row['fieldname']],$chkresult);
					if(empty($chkresult)){
						message($row['displayname']."【<font color='red'><b>".(empty($row['errortip'])?"填写数据不符合校验规则，请再填写!":$row['errortip'])."</b></font>】");
					}
				}
			}
			/*check end*/			
			foreach ($list as $row){
				if(!empty($_GPC[$row['fieldname']])){
					$insert[$row['fieldname']]=$_GPC[$row['fieldname']];
				}
			}
			$insert['userid']=$userid;
			
	        if(pdo_insert($tabname,$insert)){
	       		message($form['successtip'],create_url('mobile/module',array('do' => 'showform','weid'=>empty($_GPC['__weid'])?$_GPC['weid']:$_GPC['__weid'],'userid'=>$userid,'name' => 'commform',fid=>$id,fn=>$fn)), 'success');
	        }else{
	       		message($form['failtip'],create_url('mobile/module',array('do' => 'showform','weid'=>empty($_GPC['__weid'])?$_GPC['weid']:$_GPC['__weid'],'userid'=>$userid,'name' => 'commform',fid=>$id,fn=>$fn)), 'success');
	        }
		}
		
		$options=array();
		foreach ($list as $lst){
			if($lst['inputtype']=='select'){
				$options[$lst['fieldname']]=explode("|", $lst['options']);
			}
		}
        include $this->template('showform');
    }

    public function doWebView() {
    	global $_GPC,$_W;
    	$fid=$_GPC['fid'];
    	$fn=$_GPC['fn'];
    	$tabname="define_".$fid.'_'.Pinyin($fn);
		$pindex = max(1, intval($_GPC['page']));
		$psize = 10;
		$condition = '';		
    	$table= pdo_fetchall("SELECT * FROM ".tablename('defineformfields')." WHERE formid = :formid",array(':formid'=>$fid));
    	$form= pdo_fetch("SELECT * FROM ".tablename('defineform')." WHERE id = :formid",array(':formid'=>$fid));
    	$colums=array();
    	foreach ($table as $tab){
    		$colums[$tab['fieldname']]=$tab['displayname'];
    	}
		$list = pdo_fetchall("SELECT * FROM ".tablename($tabname)."  ORDER BY id DESC LIMIT ".($pindex - 1) * $psize.','.$psize);
		$total = pdo_fetchcolumn('SELECT COUNT(1) FROM ' . tablename($tabname));
		$pager = pagination($total, $pindex, $psize);	    	
    	include $this->template('view');
    }     

    public function doWebAddForms() {
    	global $_GPC,$_W;
        if (checksubmit('submit')) {
    	    if(empty($_GPC['formname'])){    	    	
    	    	 message('表单名称不可以为空！');
    	    }else{		       
		        $data=Array(
			        'weid'=>trim($_W['weid']),
			        'name'=>trim($_GPC['formname']),
			        'intro'=>trim($_GPC['intro']),
			        'content'=>trim($_GPC['content']),
			        'time'=>TIMESTAMP,
			        'successtip'=>trim($_GPC['successtip']),
			        'failtip'=>trim($_GPC['failtip']),
			        'endtime'=>strtotime($_GPC['endtime']),
			        'logourl'=>trim($_GPC['logourl']),
		       		'bannerurl'=>trim($_GPC['bannerurl']),		        
		        ); 
		        if(pdo_insert('defineform', $data)){
		       		message('添加表单操作成功！', create_url('site/module', array('do' => 'showforms', 'name' => 'commform')), 'success');
		        }
    	    }
    	}else{
    		include $this->template('addforms');
    	}   	        
    }    
  
    public function doWebShowForms() {
    	global $_GPC,$_W;
		$pindex = max(1, intval($_GPC['page']));
		$psize = 10;
		$condition = '';
		
    	if(checksubmit('search')){
			if (!empty($_GPC['searchkey'])) {
				$condition .= " AND keyword LIKE '%{$_GPC['searchkey']}%'";
			}	        
    	}else if(checksubmit('del')){
    		$selects=$_GPC['select'];
    		if(!empty($selects)){
	    		foreach ($selects  as $sel) {
	    			if(!empty($sel)){
	    				pdo_delete('defineform', array('id'=>$sel));
	    			}
	    		}   
	    		message('删除表单成功！',referer(), 'success');    
    		}else{
    			message('请选择要删除的记录！');
    		}	    			
    	}
    	
		$list = pdo_fetchall("SELECT * FROM ".tablename('defineform')." WHERE weid = '{$_W['weid']}' $condition ORDER BY id ASC LIMIT ".($pindex - 1) * $psize.','.$psize);
		$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('defineform') . " WHERE weid = '{$_W['weid']}' $condition");
		$pager = pagination($total, $pindex, $psize);	    	
    	include $this->template('showforms');
    } 

    public function doWebModiForms() {
    	global $_GPC,$_W;
		$fid = intval($_GPC['fid']);
    	if(checksubmit('submit')){
    		#var_dump($_POST);
    		$data=Array(
			        'weid'=>$_W['weid'],
			        'name'=>$_GPC['formname'],
			        'intro'=>$_GPC['intro'],
			        'content'=>$_GPC['content'],
			        'successtip'=>$_GPC['successtip'],
			        'failtip'=>$_GPC['failtip'],
			        'endtime'=>strtotime($_GPC['endtime']),
			        'logourl'=>$_GPC['logourl'],
    				'bannerurl'=>trim($_GPC['bannerurl']),		
		    );
    		if(empty($_GPC['formname'])){    	    	
    	    	 message('表单名称不可以为空！');
    	    }elseif(empty($_GPC['endtime'])){    	    	
    	    	 message('截止日期不可为空！');
    	    }else if(pdo_update('defineform', $data, array('id'=>$fid))){
    	    	pdo_update('defineform', array('status'=>0), array('id'=>$fid));
    			message('修改表单成功！',create_url('site/module', array('do' => 'showforms', 'name' => 'commform')), 'success');    
    		}else{
    			message('跳转中...',create_url('site/module', array('do' => 'showforms', 'name' => 'commform')), 'success');    
    		}	
    	}
    	$list = pdo_fetchall("SELECT * FROM ".tablename('defineform')." WHERE weid = :weid and id=:id ORDER BY id ASC LIMIT 1",array(":weid"=>$_W['weid'],':id'=>$fid));   	
    	$row=array();
    	if(!empty($list)){
    		$row=$list[0];
    	}
    	include $this->template('editforms');
    }        

    public function doWebAddFormFields() {
    	global $_W,$_GPC;    	
    	if(checksubmit('submit')){
    		#var_dump($_GPC);
    		if(empty($_GPC['displayname'])){    	    	
    	    	 message('显示名称不可以为空！');
    	    }elseif(empty($_GPC['fieldname'])){    	    	
    	    	 message('字段名称不可以为空！');
    	    }else{		       
	    		$data=Array(
				        'formid'=>$_GPC['fid'],
				        'displayname'=>trim($_GPC['displayname']),
				        'fieldname'=>trim($_GPC['fieldname']),
				        'inputtype'=>trim($_GPC['inputtype']),
				        'options'=>trim($_GPC['options']),
				        'require'=>trim($_GPC['require']),
				        'display'=>trim($_GPC['display']),
				        'regex'=>trim($_GPC['regex']),
				        'zindex'=>trim($_GPC['zindex']),
			    		'errortip'=>trim($_GPC['errortip']),
	    				'time'=>TIMESTAMP,
			    ); 
		        if(pdo_insert('defineformfields', $data)){
		        	pdo_update('defineform', array('status'=>0), array('id'=>$_GPC['fid']));
		       		message('添加字段操作成功！', create_url('site/module', array('do' => 'showformfields', 'name' => 'commform','fid'=>$_GPC['fid'])), 'success');
		        }
    	    }		    		
    	}
    	include $this->template('addFormFields');
    }
 
    public function doWebShowFormFields() {
    	global $_GPC,$_W;  	
    	if(checksubmit('del')){
    		$selects=$_GPC['select'];
    		if(!empty($selects)){
	    		foreach ($selects  as $sel) {
	    			if(!empty($sel)){
	    				pdo_delete('defineformfields', array('id'=>$sel));
	    			}
	    		}   
	    		pdo_update('defineform', array('status'=>1), array('id'=>$_GPC['fieldid']));
	    		message('删除表单成功！',referer(), 'success');    
    		}else{
    			message('请选择要删除的记录！');
    		}	    			
    	}    	
		$pindex = max(1, intval($_GPC['page']));
		$psize = 10;
		$condition = '';    	
		$list = pdo_fetchall("SELECT * FROM ".tablename('defineformfields')." WHERE formid = '{$_GPC['fid']}' $condition ORDER BY zindex ASC LIMIT ".($pindex - 1) * $psize.','.$psize);
		$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('defineformfields') . " WHERE formid = '{$_GPC['fid']}' $condition");
		$pager = pagination($total, $pindex, $psize);
		$fn=$_GPC['fn'];
		include $this->template("showform");
    }

    public function doWebModiFormFields() {
    	global $_GPC,$_W;
    	if(checksubmit('submit')){
	        $data=Array(
				        'displayname'=>trim($_GPC['displayname']),
				        'fieldname'=>trim($_GPC['fieldname']),
				        'inputtype'=>trim($_GPC['inputtype']),
				        'options'=>trim($_GPC['options']),
				        'require'=>trim($_GPC['require']),
				        'display'=>trim($_GPC['display']),
				        'regex'=>trim($_GPC['regex']),
				        'zindex'=>trim($_GPC['zindex']),
			    		'errortip'=>trim($_GPC['errortip']),
	    				'time'=>TIMESTAMP,
			); 
    		if(empty($_GPC['displayname'])){    	    	
    	    	 message('显示名称不可以为空！');
    	    }elseif(empty($_GPC['fieldname'])){    	    	
    	    	 message('字段名称不可以为空！');
    	    }else if(pdo_update('defineformfields', $data, array('id'=>$_GPC['fieldid']))){
    			pdo_update('defineform', array('status'=>0), array('id'=>$_GPC['fieldid']));
    	    	message('修改表单成功！',create_url('site/module', array('do' => 'showformfields', 'name' => 'commform','fid'=>$_GPC['fid'],'fn'=>$_GPC['fn'])), 'success');    
    		}   		
    	}
    	    	
		$list = pdo_fetchall("SELECT * FROM ".tablename('defineformfields')." WHERE id = '{$_GPC['fieldid']}'  ORDER BY id ASC LIMIT 1");
        $row=array();
    	if(!empty($list)){
    		$row=$list[0];
    	}
		include $this->template('editFormFields');
    }  

    public function doWebCreateOrAlterForm(){
    	global $_W,$_GPC;
    	$f=pdo_fetch("select * from ".tablename('defineform')." where id='".$_GPC['fid']."'");       	
    	$tabname="define_".$f['id'].'_'.Pinyin($f["name"]);
    	$ddl="DROP TABLE IF EXISTS ".tablename($tabname).";";	
    	echo pdo_run($ddl);
    	$ddl="CREATE TABLE  ".tablename($tabname)."( `id` int(10) NOT NULL AUTO_INCREMENT comment '序号',`time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP comment '录入时间',`userid` varchar(30) not null DEFAULT '',  PRIMARY KEY (`id`))ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";
    	echo pdo_run($ddl);
    	$fields=pdo_fetchall("select * from ".tablename('defineformfields')." where formid='".$_GPC['fid']."'");
    	echo '<br>';
    	foreach ($fields as $field){
    		if($field['inputtype']=='areatext'){
    			$ddl= "alter table ".tablename($tabname)." add `".Pinyin($field['fieldname'])."` VARCHAR(500) DEFAULT '' COMMENT '".$field['displayname']."'";
    		}else{
    			$ddl= "alter table ".tablename($tabname)." add `".Pinyin($field['fieldname'])."` VARCHAR(50) DEFAULT '' COMMENT '".$field['displayname']."'";
    		}
    		pdo_run($ddl);
    	}
    	pdo_update('defineform', array('status'=>1), array('id'=>$f['id']));
    	message("通用表单已生成!",create_url('site/module', array('do' => 'showforms', 'name' => 'commform')),'sucess');
    }
}