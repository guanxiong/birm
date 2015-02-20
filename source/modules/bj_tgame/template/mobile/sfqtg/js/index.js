// 首页跳转
$("#index_btn").on("tap", function() {
	$(this).parent().addClass("hide");
	$(".game").removeClass("hide");
	$(".guide").removeClass("hide");
	event.stopPropagation();
});

$(".guide").on("tap", function() {
	$(".guide").addClass("hide");
	prize_time();
});

/* 随机抽选数字，例如1-9随机选择3个数字——getRandom(3,9) */
function getRandom(count, totle) {
	var arr = [];
	var tmp;
	while (arr.length < count) {
		tmp = parseInt(Math.random() * totle);
		// 返回字符中indexof（string）中字串string在父串中首次出现的位置，从0开始！没有返回-1
		if (arr.indexOf(tmp) == -1) {
			arr.push(tmp);
		}
	}
	return arr;
}

var time;// 游戏时间
var interval;// 游戏运行的频率
var time_minus;
var time_out;

// 时间控制
function prize_time() {
	time_minus = setInterval(_countdown, 1000);
	time_out = setTimeout(_updateInterval, interval);
}
function _updateTime(minus) {
	time -= minus;
}
// 游戏时间更新
function _updateCount() {
	$("#game_time").text(time);
}
// 游戏时间减少
function _countdown() {

	_updateTime(1);

	if (time >= 0) {
		_updateCount();
		interval = time * 15;

		interval = Math.max(interval, 550);

	} else if (time < 0) {
		_showEnd();
		time_minus && clearInterval(time_minus);
		time_out && clearTimeout(time_out);
	}

}
// 随机显示卡牌,剩余时间越少，卡片的切换速度和数量的递增
function _updateInterval() {
	// 随机选择class 并随机为选择的li添加该class
	$("body").find(".game-main li").removeClass();
	var prize_config = [ "bird", "code", "code", "bird", "code", "code",
			"bird", "code", "card" ];

	if ((interval >= 800)) {
		var classname_arr = getRandom("1", "3");
		var li_arr = getRandom("1", "9");
		for ( var i = 0; i < 1; i++) {
			var li_randomName = prize_config[classname_arr[i]];
			var li_randomLi = $(".game-main li")[li_arr[i]];
			$("body").find(li_randomLi).addClass("ico-" + li_randomName);
		}
	}

	if ((interval >= 750) && (interval < 800)) {
		var classname_arr = getRandom("2", "3");
		var li_arr = getRandom("2", "9");
		for ( var i = 0; i < 2; i++) {
			var li_randomName = prize_config[classname_arr[i]];
			var li_randomLi = $(".game-main li")[li_arr[i]];
			$("body").find(li_randomLi).addClass("ico-" + li_randomName);
		}
	}

	if ((interval >= 650) && (interval < 750)) {
		var classname_arr = getRandom("3", "6");
		var li_arr = getRandom("3", "9");
		for ( var i = 0; i < 3; i++) {
			var li_randomName = prize_config[classname_arr[i]];
			var li_randomLi = $(".game-main li")[li_arr[i]];
			$("body").find(li_randomLi).addClass("ico-" + li_randomName);
		}
	}

	if ((interval >= 600) && (interval < 650)) {
		var classname_arr = getRandom("4", "9");
		var li_arr = getRandom("4", "9");
		for ( var i = 0; i < 4; i++) {
			var li_randomName = prize_config[classname_arr[i]];
			var li_randomLi = $(".game-main li")[li_arr[i]];
			$("body").find(li_randomLi).addClass("ico-" + li_randomName);
		}
	}

	if ((interval >= 550) && (interval < 600)) {
		var classname_arr = getRandom("6", "9");
		var li_arr = getRandom("6", "9");
		for ( var i = 0; i < 6; i++) {
			var li_randomName = prize_config[classname_arr[i]];
			var li_randomLi = $(".game-main li")[li_arr[i]];
			$("body").find(li_randomLi).addClass("ico-" + li_randomName);
		}
	}

	if ((interval < 50)) {
		var classname_arr = getRandom("8", "9");
		var li_arr = getRandom("8", "9");
		for ( var i = 0; i < 8; i++) {
			var li_randomName = prize_config[classname_arr[i]];
			var li_randomLi = $(".game-main li")[li_arr[i]];
			$("body").find(li_randomLi).addClass("ico-" + li_randomName);
		}
	}

	// console.info(interval)

	// 循环自身
	time_out = setTimeout(arguments.callee, interval);
}

var val;
var txt1 = "我是超级收银员，你敢挑战么";// 分享出去的标题
var txt2 = "0";
var share_txt = "超级收银员就是我，不服来挑战";// 分享出去的描述
// 分数控制，触摸到鸟和卡片的头像扣5分
$(".game-main li").on("tap", function() {
	var gameMainLi = $(this);
	gameMainLi.addClass("on");
	if (gameMainLi.hasClass("ico-bird")) {
		_updateTime(5);
	} else if (gameMainLi.hasClass("ico-card")) {
		_updateTime(5);
	} else if (gameMainLi.hasClass("ico-code")) {
		val += 1;
		$("#game_order").text(val);
	}
});
// 时间到，显示结果
var flag;
function _showEnd() {
	if (flag == 1) {
		return false;
	}
	flag = 1;
	$(".game-main li").removeClass();
	$(".state").removeClass("hide");
	var money = "";
/*	$.ajax({
		url : $("#baseURL").val() + "/sfgame/sfgame!PlayGame.action",
		data : {
			'score' : val,
			'openid' : $("#openid").val()
		},
		type : 'post',
		dataType : 'json',
		success : function(result) {
			if ('0' == result.code) {// 超游戏次数限制
				location.href = $("#baseURL").val()+ "/sfgame/sfgame!info.action?openid="+ $("#openid").val();
			} else if ("error" == result.code) {
				alert("系统错误!");
			} else {
				var isOver = result.code.split("@")[2];
				if (isOver == "0") {
					money = result.code.split("@")[0];
					$(".state-order").text(money);
					// 分享到朋友圈的文案
					txt2 = Math.min((((val / 80) * 100) + (Math.random() * 99 / 100)).toFixed(2), 100);
					txt2=parseInt(txt2);
					if (val >= 30) {
						$(".state").addClass("show-suc");
						title = "有比我更强的么，来PK！";// 分享出去的标题
						desc = "我玩超级收派员游戏,60秒得了" + money + "元红包,打败全球" + txt2+ "%的收派员，你敢挑战么？";// 分享出去的描述
						$(".state-order").css("left", "240px");
					} else {
						
						$(".state").addClass("show-fail");
						imgUrl = result.code.split("@")[1];
						title = "我是水货收派员，谁来替我报仇";
						desc = "我玩超级收派员游戏,60秒得了" + money + "元红包,被全球"+ (100 - txt2) + "%的收派员打败/(ㄒoㄒ)/~~，谁来替我报仇";
						$(".state-order").css("left", "227px");
					}
				}else{
					$(".state").addClass("show-finish");
					title = "超级收派员";// 分享出去的标题
					desc = "我已挑战得到高分，你也来试试吧！";// 分享出去的描述
					imgUrl = result.code.split("@")[1];
				}
			}
		}
	});*/

$.getJSON(getBaseUS(),{ score: val} );

}
// 页面初始化
function reset() {
	val = 0;
	time = 60;
	interval = 900;
	time_minus = 0;
	time_out = 0;
	$("#game_order").text(0);
	$("#game_time").text(60);
	$(".state-order").text(0);
	$("#index").attr("class", "index");
	$("#game").attr("class", "game hide");
	$("#guide").attr("class", "guide hide");
	$("#state").attr("class", "state hide");
}
reset();

// 禁止页面滑动
$(document).on("touchmove", function(e) {
	e.preventDefault();
});
// js资源加载完，去掉loading
setTimeout(function() {
	$("#popload").addClass("hide");
}, 2000);
