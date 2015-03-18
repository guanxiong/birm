var _search;
var condition = new Dictionary();
$(function(){
	var winheight = $(document).height();
	$('.showBg,.show_bg').css('height',winheight);
	//下拉切换
	dropList();
	searchClick();
	orderChange();
	handDrop();
	liDrop();
	
	var keyword = $.trim($("#keyword").val());
	var buildId = $.trim($("#buildId").val());
	if(keyword != ""){
		condition.put("keyword",keyword);
	}
	if(buildId != ""){
		condition.put("buildId",buildId);
	}
});


function dropList(){
	$("#listMore").click(function(){
		var nowClass = $(this).attr("class");
		if(nowClass == "down"){
			$(this).removeClass("down").addClass("up");
			$("#listMore .txt").html("收起更多查询条件");
			$(".drop").css("display","block");
		}else{
			$(this).removeClass("up").addClass("down");
			$("#listMore .txt").html("展开更多查询条件");
			$(".drop").css("display","none");
		}
	});
}


function searchClick(){
	var firstLiId = $('#criteriaList li').eq(0).attr('id');
    //绑定筛选条件
    $('#criteriaList span').click(function(){
    	var _this     = $(this);
    	var dataCategory = _this.attr("data-category");
    	var _parent   = _this.parent();
    	var _pparent  = _parent.parent();
    	var aSpan     = _parent.find('span');
    	var firstNode = aSpan.eq(0);
    	if (_this.text() == firstNode.text()) {
            return false;
        }
    	condition.put(dataCategory,_this.text());
    	//重置offset
    	$("#offset").val("1");
    	//给hidden域设置值
        var value = _this.attr("data-value");
        var category = _this.attr("data-category");
        $("#"+category).val(value);
    	
    	var flagRemove = false;
    	if(aSpan.filter('.hide').length>0)
		{
    		flagRemove = true;
		}
        
        var cNode = _this.clone(true);
        aSpan.attr('class','');
        _this.attr('class','hide');
        
        firstNode.before(cNode);
        
        if(flagRemove)
    	{
        	firstNode.remove();
    	}
        
        if(!window.flagInit)
        {
       	 	searchData(true);
        }
        
        var cssHeight = '28px';
        if(firstLiId==_pparent.attr('id'))
    	{
        	cssHeight = '56px';
    	}
        
        _pparent.css("height",cssHeight);
        _pparent.find('em').attr('class','arrowDown');
        
    });
}


function orderChange(){
	$("#search_condition_order").change(function(){
		$("#order").val($(this).val());
		//重置offset
    	$("#offset").val("1");
		searchData(true);
	});
}


function searchData(flag){
	
	var region = $("#region").val();
	var price = $("#price").val();
	var room = $("#room").val();	
	var area = $("#area").val();
	var useage = $("#useage").val();
	var year = $("#year").val();
	var offset = $("#offset").val();
	var order = $("#order").val();
	var keyword = $("#keyword").val();
	var buildId = $("#buildId").val();
	
	$("#loadingPic").css("display","block");
	
	//alert("region "+region + "  price "+price+"  room " +room+"  area "+area +"   useage "+useage+ "   year  "+year+"   offset "+offset+"  order "+order);
	
	$.post("index.php?i={$_W['weid']}&c=entry&do=check&m=zam_findlx&wxref=mp.weixin.qq.com#wechat_redirect",{"action":type,"region":region,"price":price,"room":room,"area":area,"useage":useage,"year":year,"offset":offset,"city":cityId,"order":order,"keyword":keyword,"buildId":buildId},function(retStr){
		retStr = retStr.replace(/^\s*/gi,"").replace(/\s*$/gi,"");
		window.flagRequesting = false;
		$("#loadingPic").css("display","none");
		if(retStr == ""){
			//请求结果为空有两种情况,一种是选择条件后没有搜索到数据,第二种是下拉加载没有更多数据
			if($("#offset").val()>1){
				//没有更多数据了
//				$("#show_tips .show_tips p").text("没有更多数据了");
//				$("#show_tips").show();
			}else{
				$("#newHouseList .linked").remove();
				$("#total").html("0").css("color","#818181");
				$("#searchcondition").html(createCondition());
				$("#orderNone").css("display","block");
			}
		}else{
			//是否需要清空现在数据的两种情况,有条件切换,要清空,其他直接append
			if(flag){
				$("#newHouseList .linked").remove();
			}
			$("#orderNone").css("display","none");
			var ret = JSON.parse(retStr);
			$("#newHouseList").append(ret.data);
			$("#total").html(ret.total).css("color","#FF9600");
		}
	});
}



function handDrop(){
	window.flagRequesting = false;
    $(window).on("scroll",function(){
        var sTop = parseFloat(window.screen.height) + parseFloat(document.body.scrollTop),
        bodyH = parseFloat($("body").height());
        if(sTop >= bodyH) {
            if(window.flagRequesting)
            {
                return;
            }
            //正在加载图片
            //this.loadingpic.show();
            window.flagRequesting = true;
            var nowOffset = $("#offset").val();
            $("#offset").val(parseInt(nowOffset)+1);
            searchData(false);
        };
    })
}

//点击li展示更多选项
function liDrop(){
	$("#criteriaList em").click(function(){
		var nowClass = $(this).attr("class");
		if("arrowDown" == nowClass){
			$(this).attr("class","arrowUp");
			$(this).parent("li").css("height","auto");
		}else{
			$(this).attr("class","arrowDown");
			if($(this).prev().html() == "区域"){
				$(this).parent("li").css("height","56px");
			}else{
				$(this).parent("li").css("height","28px");
			}
		}
	});
}

function createCondition(){
	var keyArr = ["region","price","room","area","useage","year","keyword"];
	var conditionStr = '';
	for(var i=0;i<keyArr.length;i++){
		if(condition.get(keyArr[i]) != "undefined" && condition.get(keyArr[i]) != undefined && condition.get(keyArr[i]) != "不限"){
			conditionStr += " " + condition.get(keyArr[i]);
		}
	}
	return conditionStr.trim();
}

function jumpCityMap(){
	window.location.href = "http://wap.haofang.net/citylist.jsp";
}


