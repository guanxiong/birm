
$(function(){
	window.sm = new sideMenu("aside", "container");
});

var sideMenu = (function(){
	var state = false;
	_sm = function(aside, container){
		this.aside = document.getElementById(aside);
		this.container = document.getElementById(container);
	}
	_sm.prototype = {
		toggle: function(){
			window.scrollTo(0,0);
			var that =this;
			that.aside.classList.toggle("on");
			that.container.classList.toggle("on");
			that.aside.setAttribute("style", "height:" + document.body.scrollHeight + "px");
			return that;
		}
	}

	return _sm;
})();



function getVCode(thi, evt, formId, teleName){
	if(formId){
		var form = document.getElementById(formId);
		var req = {
			telephone: $.trim(form[teleName].value)
		}
		if(!req.telephone){
			alert("请输入手机号", 1000);return;
		}
	}else{
		var req = {};
	}
	
	thi.setAttribute("disabled", "disabled");
	thi.value = "60秒后可重新获取";
	$.ajax({
		url: "data/getVCode.json",
		type:"post",
		data:req,
		dataType:"JSON",
		success: function(res){
			if(1 == res.result){
				var seconds = 60;//seconds
				var ticker = function(){
					setTimeout(function(){
						seconds --;
						if(seconds>0){
							thi.value = seconds+"秒后可重新获取";
							ticker();
						}else{
							thi.removeAttribute("disabled");
							thi.value = "获取验证码";
						}
					},1000);
				}
				ticker();
			}else{
				alert("失败", 1500);
			}
		}
	});

}


function confirm(text, fn1, fn2){
	var d = new iDialog();
	var args = {
		classList: "waiting confirm",
		title:"",
		close:"",
		content:text
	};
	args.btns = [
		{id:"", name:"确定", onclick:"fn.call();", fn: function(self){
			fn1&&fn1.call(this);
			self.die();
		}}
	];
	fn2&&args.btns.push({id:"", name:"取消", onclick:"fn.call();", fn: function(self){
			fn2&&fn2.call(this);
			self.die();
		}});
	d.open(args);
}


function loading(type){
	if(type){
		window.loader = new iDialog();
		window.loader.open({
			classList: "loading",
			title:"",
			close:"",
			content:''
		});
	}else{
		//setTimeout(function(){
			window.loader.die();
			delete window.loader;
		//}, 100);
	}
	
}
