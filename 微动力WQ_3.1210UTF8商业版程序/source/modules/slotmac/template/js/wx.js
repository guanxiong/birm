var url = "{php echo create_url('mobile/module',array('name'=>'slotmac','do'=>'slotrecord','weid'=>$weid));}";
function tellcjok(fs){
	$.ajax({
		url: url,
		data:{ hid:$('#hdid').val()},
		type:'post',
		success:function(jx){
			if(jx && jx[0] != '0'){
				$('#zjtzdiv').slideDown('normal');
				window.lid = jx[1];
			}else{
				tusi('很遗憾，您没有中奖');
				setTimeout(function(){
					location.reload(true);
				},1888);
			}
		}
	});
}
function tellcjxxok(fs){
	var sjh = $.trim($('#tel').val());
	var un = $.trim($('#un').val());
	var wxun = $.trim($('#wxun').val());
	if(sjh!='' && un !=''){
		$.ajax({
			url: url,
			data:{ hid:$('#hdid').val(),sjh:sjh,un:un,wxun:wxun,'lid':window.lid},
			type:'post',
			success:function(jx){
				tusi('提交成功');
				setTimeout(function(){
					location.reload(true);
				},1888);
				
			}
		});
	}else{
		tusi('请填写完整信息');
		return false;
	}	
}