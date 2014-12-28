function pubRecruitment(){
	var company = $("#company").val();
	var position = $("#position").val();
	var type = $("#type").val();
	var address = $("#address").val();
	var number = $("#number").val();
	var contact = $("#contact").val();
	var phone = $("#phone").val();
	var content = $("#content").val();
	if(company == ''){
		$("body").prepend("<dialog class='dialog'>招聘单位不能为空！！</dialog>");
   		$(".dialog").fadeIn(300).delay(1500).fadeOut(300,function(){this.remove();});
   		return false;
	}
	if(position == ''){
		$("body").prepend("<dialog class='dialog'>招聘职位不能为空！！</dialog>");
   		$(".dialog").fadeIn(300).delay(1500).fadeOut(300,function(){this.remove();});
   		return false;
	}
	if(contact == ''){
		$("body").prepend("<dialog class='dialog'>联系人不能为空！！</dialog>");
   		$(".dialog").fadeIn(300).delay(1500).fadeOut(300,function(){this.remove();});
   		return false;
	}
	if(phone == ''){
		$("body").prepend("<dialog class='dialog'>联系电话不能为空！！</dialog>");
   		$(".dialog").fadeIn(300).delay(1500).fadeOut(300,function(){this.remove();});
   		return false;
	}
	$.ajax({
		type:"POST",
		url:"ajax.php?action=pubRecruitment&company="+company+"&position="+position+"&type="+type+"&address="+address+"&number="+number+"&contact="+contact+"&phone="+phone+"&content="+content,
		dataType:'jsonp',
        jsonp:"callback",
		beforeSend:function(XMLHttpRequest){ 
			$(".form button").html("<img src='../images/weixin/loading.gif' />");
        },
		success:function(data){
			if (data.success){
				$(".form").hide();
				$("body").append('<div class="success">招聘信息发布成功！</div>');
				$(".success").fadeIn();
			}
			else{
				if (typeof data.error_msg !== 'undefined'){
					 $("body").prepend("<dialog class='dialog'>"+data.error_msg+"</dialog>");
   					$(".dialog").fadeIn(300).delay(1500).fadeOut(300,function(){this.remove();});
   					$(".form button").html("提交信息");
   					return false;
                }else{
					$("body").prepend("<dialog class='dialog'>发布时发生了一个错误, 请再试一次！</dialog>");
   					$(".dialog").fadeIn(300).delay(1500).fadeOut(300,function(){this.remove();});
   					$(".form button").html("提交信息");
   					return false;
                }
			}
		}
	});
}

