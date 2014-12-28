function upyunPicUpload(domid,width,height,token){
	art.dialog.data('width', width);
	art.dialog.data('height', height);
	art.dialog.data('domid', domid);
	art.dialog.data('lastpic', $('#'+domid).val());
	art.dialog.open('?g=User&m=Upyun&a=upload&token='+token+'&width='+width,{lock:true,title:'上传图片',width:600,height:400,yesText:'关闭',background: '#000',opacity: 0.45});
}
function viewImg(domid){
	if($('#'+domid).val()){
		var html='<img src="'+$('#'+domid).val()+'" />';
	}else{
		var html='没有图片';
	}
	art.dialog({title:'图片预览',content:html,lock:true,background: '#000',opacity: 0.45});
}
function addLink(domid,iskeyword){
	art.dialog.data('domid', domid);
	art.dialog.open('?g=User&m=Link&a=insert&iskeyword='+iskeyword,{lock:true,title:'插入链接或关键词',width:600,height:400,yesText:'关闭',background: '#000',opacity: 0.45});
}
function chooseFile(domid,type){
	art.dialog.data('domid', domid);
	art.dialog.open('?g=User&m=Attachment&a=index&type='+type,{lock:true,title:'选择文件',width:600,height:400,yesText:'关闭',background: '#000',opacity: 0.45});
}