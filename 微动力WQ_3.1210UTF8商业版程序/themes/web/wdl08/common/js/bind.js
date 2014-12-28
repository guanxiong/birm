/*
audiojs.events.ready(function() {
    var a = audiojs.createAll()
});
*/
//var rebind = $("#rebind").val();
var rebind = 1;		// 直接绑定
var cur_step = 1;
var wuid = "";
$(function() {
    setCenter($("#wrapper"));
   
    if (rebind) {
        $(".box").show()
    } else {
        loadImg()
    }
    
    formValidate();
    setEmptyText();
    doLogo();
    $(".toolbar .finish").click(function() {
        $("#extra_btn").click()
    });
    $(".toolbar .prev-step").click(function() {
        if (cur_step == 1) {} else {
            if (cur_step == 2) {
                $(".toolbar .prev-step").hide();
								if($(".second-step_1").css('display')== 'none'){
									$(".toolbar").hide();
								}
                $(".second-step").hide();
								$(".second-step_1").hide();
                $(".first-step").show();
                $(".indicator").removeClass("indicator_step2")
            } else {
                if (cur_step == 3) {
										if($(".second-step_1").css('display')== 'none'){
											$(".toolbar").hide();
										}
                    $(".third-step").hide();
                    $(".second-step").show();
										$(".second-step_1").hide();
                    $(".indicator").removeClass("indicator_step3").addClass("indicator_step2")
                }
            }
        }
        if (cur_step > 1) {
            cur_step -= 1
        }
    });
    $(".toolbar .next-step").click(function() {
        if (cur_step == 1) {
        	var step = $(this);
        	var step_wait = $('#step_wait');
        	step.hide();
        	step_wait.show();
        	
        	if ($('#wx_username').val() == '') {
        		alert('请填写公众好名称,如  微结盟');
        		step.show();
        		step_wait.hide();
        		return;
        	}
        	
        	var data = {
        			wxname : $('#wx_username').val(),
        			province : $('#province').val(),
        			city : $('#city').val(),
        			type : $('#type option:selected').val()
        	};
        	$.post(INSERT_URL, data, function (a) {
        		var res = eval('('+a+')');
        		if (res.status == 0) {
        			step.show();
            		step_wait.hide();
        			$("#account_btn").click();
        		}else {
        			alert(res.info);
        			step.show();
            		step_wait.hide();
        		}
        	});
        } else {
            if (cur_step == 2) {
                if (confirm("您确定已经按照图示在微信公众平台上绑定好url和token了吗？")) {
                   /* $(".second-step").hide();
										$(".second-step_1").hide();
                    $(".third-step").show();
                    $(".indicator").addClass("indicator_step3");
                    cur_step++*/
                	$(".second-step_1").hide();
                	thirdStep();
                }
            } else {
                if (cur_step == 3) {
                	thirdStep();
                }
            }
        }
    });
    $(".main .rebind-btn").click(function() {
        location.href = BIND_URL;
    });
    $(window).resize(function() {
        setCenter($("#wrapper"))
    })
});
function thirdStep() {
	$(".third-step").hide();
	$(".second-step").hide();
	$(".main .left").show();
	$(".main .right").show();
	$(".indicator").hide();
	$(".toolbar").hide();
	$(".main .rebind-btn").attr("data-id", wuid);
	$.post(ROOT_PATH + "/bind/saveExtra", {
			wx_botname: $("#wx_botname").val(),
			wx_welcome: $("#wx_welcome").val(),
			id: wuid
		},
		function(d) {},
	"json");
	$(".bind-validate .done-btn").addClass("done-disabled");
	var a = $(".bind-validate .done-btn");
	a.addClass("done-disabled");
	var b = 5;
	var c = setInterval(function() {
		if (--b > 0) {
			$(".bind-validate .counter").text(b + "s")
		} else {
			a.removeClass("done-disabled");
			$(".bind-validate .count-back").hide();
			clearInterval(c)
		}
	},
	1000);
}
function doLogo() {
    if (!BIND_LOGO_SHOW) {
        $(".huaer").hide()
    }
}
function formValidate() {
    $("#account_form").validate({
        rules: {
            wx_user_name: {
                required: true,
                maxlength: 30
            }
        },
        messages: {
            wx_user_name: {
                required: "请输入公众号名称！",
                maxlength: "最多只能输入30个字符！"
            }
        },
        showErrors: function(a, c) {
            if (c && c.length > 0) {
                $.each(c,
                function(d, f) {
                    var e = $(f.element);
                    e.addClass("error").attr("title", f.message).next(".error-text").show()
                })
            } else {
                var b = $(this.currentElements);
                b.removeClass("error").removeAttr("title").next(".error-text").hide()
            }
        },
        submitHandler: function() {
            $(".indicator").addClass("indicator_step2");
            $(".toolbar .prev-step").show();
						$(".toolbar").hide();
            $(".first-step").hide();
            $(".second-step").show();
            cur_step++;
            
            $.post(ROOT_PATH + "/bind/addAI", {
                wx_username: $("#wx_username").val(),
                uid: $("#uid").val(),
                wuid: wuid
            },
            function(a) {
                if (a.forbit) {
                    alert("您已经达到最大绑定配额了！");
                    location.href = ROOT_PATH + "/main/index.html";
                    return
                }
                if (a.success) {
                    $("#wx_api").text(BASE_HOST + "?bid=" + a.api);
                    $("#wx_token").text(a.token);
                    if ($("#wx_botname").val() == "") {
                        $("#wx_botname").val(a.botname)
                    }
                    if ($("#wx_welcome").html() == "") {
                        $("#wx_welcome").html(a.welcome)
                    }
                    $("#extra_form .wx-name").text(a.wx_username);
                    wuid = a.wuid
                }
            },
            "json");
            return false
        }
    });
    $("#extra_form").validate({
        rules: {
            wx_botname: {
                required: true
            },
            wx_welcome: {
                required: true
            }
        },
        messages: {
            wx_botname: {
                required: "请输入智能客服名称！"
            },
            wx_welcome: {
                required: "请输入欢迎语！"
            }
        },
        showErrors: function(a, c) {
            if (c && c.length > 0) {
                $.each(c,
                function(d, f) {
                    var e = $(f.element);
                    e.addClass("error").attr("title", f.message);
                    e.closest("td").find(".error-text").show()
                })
            } else {
                var b = $(this.currentElements);
                b.removeClass("error").removeAttr("title");
                b.closest("td").find(".error-text").hide()
            }
        },
        submitHandler: function() {
            return false
        }
    })
}

/**/
$(function(){
	$("#upgrade_btn").click(function() {
		if (!$("#upgrade_btn").hasClass("disabled")) {
			$(".manual-bind").hide();
			$("#upgrade_btn").addClass('disabled');
			$('#submit_wait').show();
			if ($("#username").val()==""){
				alert("请输入微信公众平台帐号！");
				$("#username").focus();
				$("#upgrade_btn").removeClass('disabled');
				$('#submit_wait').hide();
				return
			}
			if ($("#password").val()==""){
				alert("请输入微信公众平台帐号密码！");
				$("#password").focus();
				$(".manual-bind").show();
				$("#upgrade_btn").removeClass('disabled');
				$('#submit_wait').hide();
				return
			}
			$.post(ONE_KEY_BIND_PATH, {
	    		account: $("#username").val(),
				password: $("#password").val(),
				callbackToken : token
//				url: $("#wx_api").text(),
//				token: $("#wx_token").text(),
//				imgcode: $("#imgcode").val(),
//				uid: $("#uid").val()
			},
	     	function(res) {
		      	if (res.status == 0) {
					/*cur_step = 3;
					$(".toolbar").show();
					$(".second-step").hide();
					$(".second-step_1").hide();
					$(".third-step").show();
					$(".indicator").addClass("indicator_step3");*/
		      		thirdStep();
		        }else if (res.status == 1) {
	        		alert(res.info);
	        		$("#username").val("");
	        		$("#password").val("");
	        		$(".manual-bind").show();
	        		$("#upgrade_btn").removeClass('disabled');
	    			$('#submit_wait').hide();
	        		return;
		        }else if (res.status == 2) {
		        	alert("绑定失败("+res.info+")，请联系客服或者手动绑定!");        
					cur_step=2;
					$(".toolbar").show();
					$(".second-step").hide();
					$(".second-step_1").show();
					$(".first-step").hide();
					$(".indicator").addClass("indicator_step2");
					return;
		        }
			},"json");
		}
  });
});

function qiehua(){
	$(".second-step").hide();
	$(".second-step_1").show();
	$(".toolbar").show();
}
/**/


function setEmptyText() {
    $("input[data-empty]").tipInput()
}
function aniText() {
    var a = $("#text");
    var b = parseInt(a.css("top"));
    if (b == -a.height()) {
        a.css("top", "0px")
    }
}
function setCenter(a) {
    var b = $(window).width(),
    d = $(window).height(),
    c = a.outerWidth(true),
    e = a.outerHeight(true);
    if (b > c) {
        a.css("left", (b - c) / 2 + "px")
    } else {
        a.css("left", "50px")
    }
    if (d - e >= 120) {
        a.css("top", (d - e) / 2)
    } else {
        if (d - e <= 90) {
            a.css("top", "45px")
        } else {
            a.css("top", "55px")
        }
    }
}
function loadImg() {
    $(".preload").one("load",
    function() {
        var a = $(window).width(),
        b = $(window).height();
        $(this).show().css({
            left: (a - $(this).width()) / 2,
            top: (b - $(this).height()) / 2
        });
        loadPreImg()
    }).attr("src", "/public/front/image/admin/bind/loading2.gif")
}
function loadPreImg() {
    var c = 4;
    var b = 0;
    var d = [];
    for (var a = 1; a <= c; a++) { (function(e) {
            $("#pre_" + e).one("load",
            function() {
                if (++b == c) {
                    $(".preload").hide();
                    $(".tip").show();
                    var j = $(".tip1"),
                    f = $(".circle", j),
                    h = $(".loader", j),
                    g = $(".p", j),
                    i = $(".progress", j).width();
                    h.animate({
                        width: "364px"
                    },
                    {
                        duration: 5000,
                        step: function() {
                            var k = h.width();
                            f.css("left", k);
                            g.text(Math.round(100 * k / i) + "%").css("left", k)
                        }
                    });
                    setTimeout(function() {
                        hide($("#pre_1"));
                        show($("#pre_2"))
                    },
                    7000);
                    setTimeout(function() {
                        h.stop();
                        h.animate({
                            width: "520px"
                        },
                        {
                            duration: 3000,
                            step: function() {
                                var k = h.width();
                                f.css("left", k);
                                g.text(Math.round(100 * k / i) + "%").css("left", k)
                            }
                        })
                    },
                    10000);
                    setTimeout(function() {
                        showBigTip()
                    },
                    15000)
                }
            }).attr("src", "/public/front/image/admin/bind/pre_" + e + ".png?v=20130428")
        })(a)
    }
}
function showBigTip() {
    $(".tip1").animate({
        opacity: 0
    },
    2000,
    function() {
        $(".tip1").hide()
    });
    $(".tip2").show();
    var d = $("#pre_3");
    d.css("top", $("#pre_3").height()).animate({
        top: 0,
        opacity: 1
    },
    3000,
    function() {
        $("#text_3").animate({
            opacity: 1
        },
        2000);
        setTimeout(function() {
            d.animate({
                left: "-=" + d.width() + "px",
                width: 0,
                opacity: 0
            },
            3000);
            $("#text_3").animate({
                opacity: 0
            },
            2000)
        },
        3000)
    });
    var c = $("#pre_4"),
    a = c.width(),
    b = c.height();
    c.css({
        left: "+=" + a / 2,
        top: b / 2,
        width: 0,
        height: 0
    });
    setTimeout(function() {
        c.animate({
            left: "-=" + a / 2,
            top: 0,
            width: a,
            height: b,
            opacity: 1
        },
        6000)
    },
    6000);
    setTimeout(function() {
        $("#text_4").animate({
            opacity: 1
        },
        2000)
    },
    10000);
    setTimeout(function() {
        c.animate({
            top: -1 * (c.offset().top + c.height())
        },
        4000);
        $("#text_4").animate({
            opacity: 0
        },
        2000)
    },
    15000);
    setTimeout(function() {
        var e = $("#text_5");
        setCenter(e);
        e.animate({
            opacity: 1
        },
        2000)
    },
    19000);
    setTimeout(function() {
        $("#text_5").animate({
            opacity: 0
        },
        2000,
        function() {
            $("#text_5").hide();
            $(".tip").hide();
            $box = $(".box").show();
            $(".main", $box).css("opacity", 0).animate({
                opacity: 1
            },
            3000);
            if (BIND_LOGO_SHOW) {
                $(".huaer", $box).css("opacity", 0).animate({
                    opacity: 1
                },
                3000)
            }
            $(".thank", $box).css("opacity", 0).animate({
                opacity: 1
            },
            3000);
            $(".next-step", $box).css("opacity", 0).animate({
                opacity: 1
            },
            3000);
            $("body").css("overflow", "auto")
        })
    },
    22000)
}
function show(a) {
    a.animate({
        opacity: 1
    },
    3000)
}
function hide(a) {
    a.animate({
        opacity: 0
    },
    2000)
}
function showDone() {
    if (!$(".bind-validate .done-btn").hasClass("done-disabled")) {
        $("#wrapper .box").hide();
        $("#wrapper .done").show();
    }
};