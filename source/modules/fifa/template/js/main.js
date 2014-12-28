// 'use strict';

// ready

function goUrl(url){
    window.location.href = url;
}

document.addEventListener('DOMContentLoaded', function() {
	// 导航
	(function() {
		
		var oDropdown = document.querySelector('.dropdown-menu');
		var oCover = document.querySelector('.cover');
		
		

		var aNavMain = document.querySelectorAll('.nav-main');
		for (var i = 0; i < aNavMain.length; i++) {
			activeIcon(aNavMain[i]);
		}
		activeIcon(document.querySelector('.footer-bar'));

		function activeIcon(obj) {
			var a = obj.querySelectorAll('a');
			for (var i = 0; i < a.length; i++) {
				a[i].index = i;
				if (a.length == 3) {
					a = obj.querySelectorAll('a.icon');
					a[i].ontouchstart = function() {
						a[this.index].style.backgroundPosition = getComputedStyle(a[this.index], false).backgroundPosition.split(' ')[0] + ' -25px';
					};
					a[i].ontouchend = function() {
						a[this.index].style.backgroundPosition = getComputedStyle(a[this.index], false).backgroundPosition.split(' ')[0] + ' 0';
					}
				} else {
					var icon = obj.querySelectorAll('.icon');
					a[i].ontouchstart = function() {
						a[this.index].classList.add('active');
						icon[this.index].style.backgroundPosition = getComputedStyle(icon[this.index], false).backgroundPosition.split(' ')[0] + ' -25px';
					}
					a[i].ontouchend = function() {
						a[this.index].classList.remove('active');
						icon[this.index].style.backgroundPosition = getComputedStyle(icon[this.index], false).backgroundPosition.split(' ')[0] + ' 0';
					}
				}
			}
		}
	})();




	function dialog(btn, content, end) {
		var aOpenBtn = document.getElementsByName(btn);
		var oOpenBox = document.getElementsByName(content)[0] || null;
		for (var i = 0; i < aOpenBtn.length; i++) {
			aOpenBtn[i].onclick = function() {
				if (!document.querySelector('.dialog')) {
					var oDialog = document.createElement('div'),
						oBackdrop = document.createElement('div');
					oDialog.className = 'dialog';
					oBackdrop.className = 'dialog-backdrop';
					_s();
					document.body.appendChild(oDialog);
					document.body.appendChild(oBackdrop);
				} else {
					var oDialog = document.querySelector('.dialog'),
						oBackdrop = document.querySelector('.dialog-backdrop');
					_s();
					oDialog.classList.remove('hide');
					oBackdrop.classList.remove('hide');
				}
				var aClose = [];
				aClose = oDialog.querySelectorAll('.btn');
				for (var j = 0; j < aClose.length; j++) {
					aClose[j].onclick = function() {
						addClass(oDialog, 'hide');
						addClass(oBackdrop, 'hide');
						end && end();
					}
				}

				function _s() {
					if (!oOpenBox) {
						oDialog.innerHTML = '<div class="dialog-head">系统提示</div><div class="dialog-body">' + content + '</div>';
					} else {
						oDialog.innerHTML = oOpenBox.innerHTML;
					}
				}
			}
		}
	}
	


	// 解决个人中心表单输入bug
	document.body.ontouchstart = function() {
		var aInput = document.querySelectorAll('.j_input') || null;
		if (aInput) {
			for (var i = 0; i < aInput.length; i++) {
				// aInput[i].blur();
				aInput[i].onfocus = function() {
					document.querySelector('.footer').style.display = 'none';
				}
				aInput[i].onblur = function() {
					document.querySelector('.footer').style.display = 'block';
				}
			}
		}
	}
}, false);

function saveUserData() {
    var user_name = $("[name='name']");
    var mobile = $("[name='mobile']");
//    var age = $("[name='age']");
    if (user_name.val().length < 1) {
        alert('请填写姓名!');
        user_name.focus();
        return false;
    }
    if (mobile.val().length < 1) {
        alert('请填写手机!');
        mobile.focus();
        return false;
    }
//    if (age.val().length < 1) {
//        alert('请填写生日!');
//        age.focus();
//        return false;
//    }
    var data = {name: user_name.val(), mobile: mobile.val()};
    $.ajax({
        url: "ajax.php?act=user_data",
        data: data,
        type: 'post',
        dataType: 'json',
        success: function(data) {
            alert(data.info);
        },
        cache: false
    });
}

function game_guess() {
    
    var whowin = $("[radio='check']").val();
	
    var data = {whowin: whowin};
	
    $.ajax({
        url: GAMEGUESSURL,
        data: data,
        type: 'post',
        dataType: 'json',
        success: function(data) {
            alert(data.info);
            if (data.data == 'is_userinfo') {
                window.location.href = "ajax.php?act=user_data";
            }
        },
        cache: false
    });

}

