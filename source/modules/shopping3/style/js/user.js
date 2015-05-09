

var user_obj={
	user_login_init:function(){
		$('#user_form .submit input').click(function(){
			if(global_obj.check_form($('*[notnull]'))){return false};
 			$(this).attr('disabled', true);
  			$.post(location.href,$('#user_form').serialize(),function(data){
 				if(data.status==1){
					global_obj.win_alert(data.msg, function(){
						window.location=data.jump_url;
					});
				}else{
					global_obj.win_alert('错误的用户名或密码，请重新登录！', function(){
						$('#user_form .submit input').attr('disabled', false)
						$('#user_form input[name=Password]').val('');
					});
				};
 			}, 'json');
 		});
	},
	
	user_create_init:function(){
		$('#user_form .submit input').click(function(){
			if($('#VCode').val()=='' && sms_resgister==1){
				global_obj.win_alert('请先获取手机验证码，才可以注册！', function(){});
				return false;
			}
			if(global_obj.check_form($('*[notnull]'))){return false};
			if($('#user_form input[name=Password]').val()!=$('#user_form input[name=ConfirmPassword]').val()){
				global_obj.win_alert('两次输入的密码不一致，请重新输入登录密码！', function(){
					$('#user_form input[name=Password]').val('').focus();
					$('#user_form input[name=ConfirmPassword]').val('');
				});
				return false;
			}
			
			$(this).attr('disabled', true);
			$.post(location.href, $('#user_form').serialize(), function(data){
				if(data.status==1){
					global_obj.win_alert('恭喜，注册成功！', function(){window.location=data.jump_url});
				}else if(data.status==2){
					global_obj.win_alert('请正确填写手机号码！', function(){
						$('#user_form .submit input').attr('disabled', false)
					});
				}else if(data.status==-1){
					global_obj.win_alert('请正确注册资料！', function(){
						$('#user_form .submit input').attr('disabled', false)
					});		
				}else if(data.status==-3){
					global_obj.win_alert('请正确输入您的验证码！', function(){
						$('#user_form .submit input').attr('disabled', false)
					})		
				}else if(data.status==-4){
					global_obj.win_alert('网络故障，请稍后在试！', function(){
						$('#user_form .submit input').attr('disabled', false)
					})						
				}else{
					global_obj.win_alert('对不起，此手机号已经注册，请勿重复注册！', function(){
						$('#user_form .submit input').attr('disabled', false)
					});
				};
			}, 'json');
		});
	},
	
	user_perfect_init:function(){
		$('#user_form .submit input').click(function(){
			if(global_obj.check_form($('*[notnull]'))){return false};
			$(this).attr('disabled', true);
			$.post('?', $('#user_form').serialize(), function(data){
				if(data.status==1){
					window.location=data.jump_url;
				};
			}, 'json');
		});
	}
}