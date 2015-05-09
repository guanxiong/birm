
/*
 if (navigator.userAgent.toLowerCase().match(/MicroMessenger/i) != 'micromessenger' && navigator.userAgent.toLowerCase().match(/Windows Phone/i) != 'windows phone') {
    window.location.href = '/';
}
*/
KISSY.use('node,io', function(S, Node, IO) {
	var $ = Node.all;
	function loadImages(sources, callback) {
		var count = 0,
				images = {},
				imgNum = 0;
		for (src in sources) {
			imgNum++;
		}
		for (src in sources) {
			images[src] = new Image();
			images[src].onload = function() {
				if (++count >= imgNum) {
					callback(images);
				}
			}
			images[src].src = sources[src];
		}
	}
	loadImages(['../source/modules/broke/style/images/bg-loader.jpg', '../source/modules/broke/style/images/ico-logo.png', '../source/modules/broke/style/images/sales-bg-loader.jpg', '../source/modules/broke/style/images/ico-sales-logo.png'], function() {
		setTimeout(function() {
			$('.loader').addClass('fadeOut').hide();
			$('.user-loader').addClass('fadeOut').hide();
			$('.main-box').addClass('fadeIn');
			$('#loading-style').remove();
		}, 1000);
	});

	var REG = {
		name: /^[a-zA-Z\u4e00-\u9fa5]{2,12}$/,
		phone: /(^(([0\+]\d{2,3}-)?(0\d{2,3})-)(\d{7,8})(-(\d{3,}))?$)|(^0{0,1}1[3|4|5|6|7|8|9][0-9]{9}$)/,
		wxid: /^[a-zA-Z][a-zA-Z0-9_-]{5,19}$/,
		number: /^[+\-]?\d+(\.\d+)?$/,
        idCard:/^\d{15}$|^\d{18}$/
}

	//经纪人注册
	var submit_broker = $('#J_submitReg');
	var companyName = $('.company-name');
	var name = $('#name');
	var phone = $('#phone');
	var uid = $('#uid').val();
	var waid = $('#waid').val();
	var job = $('#job');
	var company = $('#company');
	var agree = $('#agree');
	var DATA = {}

    if (job.val() == '1') {
        companyName.show();
    } else {
        companyName.hide();
    }

	job.on('change', function() {
		if (job.val() == '1') {
			companyName.show();
		} else {
			companyName.hide();
		}
	});

	submit_broker.on('click', function() {
		//姓名
		if (name.length == 1) {
			var nv = S.trim(name.val());
			if (nv == '') {
				alert('姓名不能为空！');
				return false;
			} else if (name.length > 5) {
				alert('姓名不能超过5个字！');
				return false;
			} else if (!REG.name.test(nv)) {
				alert('请填写正确的姓名！');
				return false;
			}
			DATA.name = nv;
		}
		//手机
		if (phone.length == 1) {
			var pv = S.trim(phone.val());
			if (pv == '') {
				alert('手机号不能为空！');
				return false;
			} else if (!REG.phone.test(pv)) {
				alert('请填写正确的手机号！');
				return false;
			}
			DATA.phone = pv;
		}
		//职业
		if (job.length == 1) {
			var prv = job.val();
            var prCompany=S.trim(company.val());
			if (prv == '') {
				alert('请选择您的职业');
				return false;
			}else if (prv == '1') {
               if(prCompany==''){
                   alert('公司名称不能为空！');
                   return false;
               }
            }
			DATA.job = prv;
            DATA.company = prCompany;
		}
		//注册协议
		if (agree.prop('checked') == false) {
			alert('请同意注册协议');
			return false;
		}

		DATA.uid = uid;
		DATA.waid = waid;

		//请求
		IO.post('/source/modules/broke/mobile/register.php', DATA, function(data) {
			if (data.status == 200) {
				IO.post('/Home/Broker/register', DATA, function(data) {
					if (data.status == 200) {
						alert("注册成功");

						var return_url = $('#return_url').val();
						if (return_url == '') {
							location.href = '/Home/Broker/center';
						} else {
							location.href = return_url;
						}
					} else {
						alert("注册失败");
					}
				}, 'json');

			} else {
				if (data.status == 300) {
					alert(data.message);
				} else {
					alert('参数有误或系统异常，请稍后重试！');
				}
			}
		}, 'json');
	});

	//完善经纪人信息
	var complete = $('.confirm-btn');
    complete.on('click',function(){
        DATA.company = S.trim(company.val());
		DATA.waid = waid;
        DATA.uid = uid;
        //职业
        if (job.length == 1) {
            var prv = job.val();
            if (prv == 0) {
                alert('请选择您的职业');
                return false;
            }
            DATA.job = prv;
        }

        IO.post('/Home/Broker/complete', DATA, function(data) {
            if (data.status == 200) {
                alert("保存成功");
                location.href = '/Home/Broker/center';
            } else {
                alert("保存失败");
            }
        }, 'json');
    });

	//老业主注册
	var submit_owner = $('#submit_owner');
    var idC = $('#idCard');
	var DATA = {}

	submit_owner.on('click', function() {
		//姓名
		if (name.length == 1) {
			var nv = S.trim(name.val());
			if (nv == '') {
				alert('姓名不能为空！');
				return false;
			} else if (name.length > 5) {
				alert('姓名不能超过5个字！');
				return false;
			} else if (!REG.name.test(nv)) {
				alert('请填写正确的姓名！');
				return false;
			}
			DATA.name = nv;
		}
		//手机
		if (phone.length == 1) {
			var pv = S.trim(phone.val());
			if (pv == '') {
				alert('手机号不能为空！');
				return false;
			} else if (!REG.phone.test(pv)) {
				alert('请填写正确的手机号！');
				return false;
			}
			DATA.phone = pv;
		}
        //身份证号码验证
        if (idC.length == 1) {
            var idCard = S.trim(idC.val());
            if (idCard == '') {
                alert('身份证号码不能为空！');
                return false;
            } else if (!REG.idCard.test(idCard)) {
                alert('身份证号码位数不正确！');
                return false;
            }
            DATA.idCard = idCard;
        }


		//注册协议
		if (agree.prop('checked') == false) {
			alert('请同意注册协议');
			return false;
		}

		DATA.uid = uid;
		DATA.waid = waid;

		//请求
		IO.post('/Home/Index/editUser', DATA, function(data) {
			if (data.status == 200) {
				IO.post('/Home/Owner/register', DATA, function(data) {
					if (data.status == 200) {
						alert("注册成功");
						location.href = '/Home/Broker/center';
					} else {
						alert(data.message);
					}
				}, 'json');
			} else {
				if (data.status == 300) {
					alert(data.message);
				} else {
					alert('参数有误或系统异常，请稍后重试！');
				}
			}
		}, 'json');
	});

	//经理注册
	var submit_consultant = $('#J_submitMan');
	var code = $('#code');
	var DATA = {}

	submit_consultant.on('click', function() {
		//姓名
		if (name.length == 1) {
			var nv = S.trim(name.val());
			if (nv == '') {
				alert('姓名不能为空！');
				return false;
			} else if (!REG.name.test(nv)) {
				alert('请填写正确的姓名！');
				return false;
			}
			DATA.name = nv;
		}
		//手机
		if (phone.length == 1) {
			var pv = S.trim(phone.val());
			if (pv == '') {
				alert('手机号不能为空！');
				return false;
			} else if (!REG.phone.test(pv)) {
				alert('请填写正确的手机号！');
				return false;
			}
			DATA.phone = pv;
		}
		//邀请码
		if (code.length == 1) {
			var prv = code.val();
			if (prv == 0) {
				alert('请输入邀请码');
				return false;
			}
			DATA.code = prv;
		}
		//注册协议
		if (agree.prop('checked') == false) {
			alert('请同意注册协议');
			return false;
		}
		DATA.uid = uid;
		DATA.waid = waid;
		DATA.code = code.val();

		IO.post('/Home/Manager/check', DATA, function(data) {
			if (data.status == 200) {
				//请求
				IO.post('/Home/Index/editUser', DATA, function(data) {
					if (data.status == 200) {
						IO.post('/Home/Manager/register', DATA, function(data) {
							if (data.status == 200) {
								alert("注册成功");
								location.href = '/Home/Manager/newCustomer';
							} else {
								alert("注册失败");
							}
						}, 'json');

					} else {
						if (data.status == 300) {
							alert(data.message);
						} else {
							alert('参数有误或系统异常，请稍后重试！');
						}
					}
				}, 'json');
			} else {
				alert(data.message);
			}
		}, 'json');
	});


	//销售员注册
	var submit_manager = $('#J_submitCon');
	var DATA = {}

	submit_manager.on('click', function() {
		//姓名
		if (name.length == 1) {
			var nv = S.trim(name.val());
			if (nv == '') {
				alert('姓名不能为空！');
				return false;
			} else if (!REG.name.test(nv)) {
				alert('请填写正确的姓名！');
				return false;
			}
			DATA.name = nv;
		}
		//手机
		if (phone.length == 1) {
			var pv = S.trim(phone.val());
			if (pv == '') {
				alert('手机号不能为空！');
				return false;
			} else if (!REG.phone.test(pv)) {
				alert('请填写正确的手机号！');
				return false;
			}
			DATA.phone = pv;
		}
		//邀请码
		if (code.length == 1) {
			var prv = code.val();
			if (prv == 0) {
				alert('请输入邀请码');
				return false;
			}
			DATA.code = prv;
		}
		//注册协议
		if (agree.prop('checked') == false) {
			alert('请同意注册协议');
			return false;
		}
		DATA.uid = uid;
		DATA.waid = waid;
		DATA.code = code.val();

		IO.post('/Home/Consultant/check', DATA, function(data) {
			if (data.status == 200) {
				//请求
				IO.post('/Home/Index/editUser', DATA, function(data) {
					if (data.status == 200) {
						IO.post('/Home/Consultant/register', DATA, function(data) {
							if (data.status == 200) {
								alert("注册成功");
								location.href = '/Home/Consultant/newCustomer';
							} else {
								alert("注册失败");
							}
						}, 'json');

					} else {
						if (data.status == 300) {
							alert(data.message);
						} else {
							alert('参数有误或系统异常，请稍后重试！');
						}
					}
				}, 'json');
			} else {
				alert(data.message);
			}
		}, 'json');
	});

	//我要推荐提交
	var submitRec = $('#J_submitRec');
	var url = $('#submit_url').val();
	var floor = $('#floor');
	var DATA = {}

	submitRec.on('click', function() {
		//姓名
		if (name.length == 1) {
			var nv = S.trim(name.val());
			if (nv == '') {
				alert('姓名不能为空！');
				return false;
			} else if (!REG.name.test(nv)) {
				alert('请填写正确的姓名！');
				return false;
			}
			DATA.name = nv;
		}
		//手机
		if (phone.length == 1) {
			var pv = S.trim(phone.val());
			if (pv == '') {
				alert('手机号不能为空！');
				return false;
			} else if (!REG.phone.test(pv)) {
				alert('请填写正确的手机号！');
				return false;
			}
			DATA.phone = pv;
		}
		//意向产品
		if (floor.length == 1) {
			var prv = floor.val();
			if (prv == 0) {
				alert('请选择您意向的产品');
				return false;
			}
			DATA.floor = prv;
		}

		//请求
		IO.post(url, DATA, function(data) {
			if (data.status == 200) {
				window.location.href = data.url;
			} else {
				alert(data.message);
			}
		}, 'json');
	});


	//保存银行卡信息
	var saveCard = $('#J_saveCard');
	var url = $('#submit_url').val();
	var accountName=$('#bankAccount');
	var card = $('#cardCode');
	var bank = $('#bankName');
	var DATA = {}

	saveCard.on('click', function() {
		//银行卡号
		if (card.length == 1) {
			var num = S.trim(card.val());
			if (num == '') {
				alert('银行卡号不能为空！');
				return false;
			} else if (!REG.number.test(num)) {
				alert('请填写正确的银行号！');
				return false;
			}
			DATA.card = num;
		}
		//银行卡名称
		if (bank.length == 1) {
			var name = S.trim(bank.val());
			if (name == '') {
				alert('银行名称不能为空！');
				return false;
			}
			DATA.bank = name;
		}
		
		//户名
        if(accountName.length==1){
            var account=S.trim(accountName.val());
            if(account==''){
                alert('户名不能为空！');
                return false;
            }
            DATA.account=account;
        }

		//请求
		IO.post(url, DATA, function(data) {
			if (data.status == 200) {
				window.location.href = data.url;
			} else {
				alert(data.message);
			}
		}, 'json');

	});

	//客户详情操作
	var clientStates = $('.client-states');
	var pop = $('.pop');
	var popNote = $('.pop-note');
	var popPrice = $('.pop-price');
	var bg = $('.pop-bg');
	var cid = $('#cid').val();
	var zid = $('#zid').val();
    var statusUrl = $('#statusUrl').val();

	clientStates.on('click', 'a', function() {
		var self = $(this);
		var now_status = $('#now_status').val();
		var status = self.attr('status');
		var num_status = status - now_status;
		if (!self.hasClass('disable-step')) {

		} else {
			if (num_status == 1) {
				var DATA = {};
				DATA.customer_id = cid;
				DATA.zid = zid;
				DATA.waid = waid;

				if (self.hasClass('follow-up')) {
					bg.show();
					popNote.show();

					var intent = $('.intent');
					intent.on('click', function(result) {
						DATA.status = 2;
						DATA.intent = $(this).attr('data-type');
						//请求
						IO.post(statusUrl, DATA, function(data) {
							if (data.status == 200) {
								bg.hide();
								popNote.hide();
								$('#now_status').val(data.now_status);
								self.children().children().children('.data-time').html(data.create_time);
                                self.children().children('.option').html('操作人&nbsp;&nbsp;'+data.name);
								if (data.intent == 1) {
									$(".note").html('有意向客户')
								} else {
									$(".note").html('无意向客户')
								}
								self.removeClass('disable-step');
							} else {
								alert('操作失败');
							}
						}, 'json');
					});

				} else if (self.hasClass('floor-price')) {
					DATA.intent = 1;
					bg.show();
					popPrice.show();
					var save = $('#J_save');
					save.on('click', function(result) {

						var price = $('#price').val();
						if (price.length == '') {
							alert('房屋成交价格不能为空');
							return false;
						} else if (price != '' && !REG.number.test(price)) {
							alert('房屋成交价格必须为数字');
							return false;
						} else {
							DATA.price = price;
							DATA.status = 6;
							//请求
							IO.post(statusUrl, DATA, function(data) {
								if (data.status == 200) {
									bg.hide();
									pop.hide();
									$("#now_status").val(data.now_status);
									self.children().children().children('.data-time').html(data.create_time);
                                    self.children().children('.option').html('操作人&nbsp;&nbsp;'+data.name);
									self.removeClass('disable-step');
								} else {
									alert('操作失败');
								}
							}, 'json');
						}
					});
				} else {

					DATA.intent = 1;
					DATA.status = status;
					IO.post(statusUrl, DATA, function(data) {
						if (data.status == 200) {
							$("#now_status").val(data.now_status);
							self.children().children().children('.data-time').html(data.create_time);
                            self.children().children('.option').html('操作人&nbsp;&nbsp;&nbsp;'+data.name);
							self.removeClass('disable-step');
						} else {
							alert('操作失败');
						}
					}, 'json');
				}
			} else {
				alert('请先确认上步操作')
			}
		}
	});

	//关闭弹出层
	var cancel = $('.icon-cancel');
	cancel.on('click', function() {
		bg.hide();
		pop.hide();
	});


	//领取新客户
	var getCustomer = $('#getCus');
	var DATA = {};

	getCustomer.on('click', function() {

		//请求
		IO.post('/Home/Consultant/getNewCustomer', DATA, function(data) {
            if (data.status == 500) {
                alert('对不起，经理没有开启自动领取功能！');
            } else if (data.status == 200) {
				window.location.href = data.url;
			} else if (data.status == 100) {
				alert('新客户数超过10个，请跟进后继续领取。');
			} else if (data.status == 300) {
				alert('客户被领光了，下次早点来。');
			} else if (data.status == 400) {
				alert('领取失败，请重新领取。');
			}
		}, 'json');

	});

	var clients=$('.checkbox-btn');
    clients.on('click',function(){
        var is_pitch=$(this).children('.pitch').prop('checked');
        if(is_pitch==false){
            $(this).children('.pitch').prop("checked", true);
        }else{
            $(this).children('.pitch').prop("checked", false);
        }
    });

    $('.pitch').on('click',function(){
    	var is_pitch=$(this).prop('checked');
        if(is_pitch==false){
            $(this).prop("checked", true);
        }else{
            $(this).prop("checked", false);
        }
    });

    //经理分配
    var allot=$('.allot-list');
    allot.on('click','p',function(){
        var is_pitch=$(this).children('.radio').prop('checked');
        if(is_pitch==false){
            $(this).children('.radio').prop('checked', true);
        }else{
            $(this).children('.radio').prop('checked', false);
        }
    });

    var aSubmit=$('.allot-submit');
    var assignCus=$('#assignCus').val();
    aSubmit.on('click',function(){

        var selects = new Array();
        $('.radio').each(function(){
            if($(this).prop('checked')==true){
                selects.push($(this).val());
            }
        })

        var radio = $("input[name='person']:checked").val();
        if(selects.length>0){
            var DATA = {}
            DATA.zid = radio;
            DATA.cids = $('#cids').val();

            IO.post(assignCus, DATA, function(data) {
                if (data.status == 200) {
                    alert('分配成功');
                    window.location.href = data.url;
                } else{
                    alert('分配失败');
                }
            }, 'json');
        }else{
            alert('您还没有选择销售员！');
        };
    });
    var onOff =$('.onOff');
    var allowBtn =$('.manger-client-btn');
    var turnUrl=$('#turnUrl').val();
    var DATA = {}
    onOff.on('click',function(){
        if(onOff.prop('checked')==true){
            DATA.status_lq = true;
            IO.post(turnUrl, DATA, function(data) {
                if (data.status == 200) {
                    allowBtn.html('<a href="javascript:;" class="allow allow-no">分配</a>')
                    alert('开启成功');
                } else{
                    alert('开启失败');
                }
            }, 'json');

        }else{
            DATA.status_lq = false;
            IO.post(turnUrl, DATA, function(data) {
                if (data.status == 200) {
                    allowBtn.html('<a href="javascript:;" class="allow" id="J_allow">分配</a>')
                    alert('关闭成功');
                    var allow=$('#J_allow');
                    var assignUrl=$('#assignUrl').val();
                    allow.on('click',function(){
                        var cids = new Array();
                        $('.pitch').each(function(){
                            if($(this).prop('checked')){
                                cids.push($(this).val());
                            }
                        });
                        if(cids.length>0){
                            document.getElementById("customer_form").submit();
                        }else{
                            alert('您还没有选择客户！');
                        };
                    });
                } else{
                    alert('关闭失败');
                }
            }, 'json');
        }
    });

        var allow=$('#J_allow');
        var assignUrl=$('#assignUrl').val();
        allow.on('click',function(){
            var cids = new Array();
            $('.pitch').each(function(){
                if($(this).prop('checked')){
                    cids.push($(this).val());
                }
            });
            if(cids.length>0){
                document.getElementById("customer_form").submit();
            }else{
                alert('您还没有选择客户！');
            };
        });

});