
jQuery(document).ready(function() {

    /*
        Background slideshow
    */
    $.backstretch([
      "/tpl/Home/wdl08/common/assets/img/backgrounds/1.jpg"
    , "/tpl/Home/wdl08/common/assets/img/backgrounds/2.jpg"
    , "/tpl/Home/wdl08/common/assets/img/backgrounds/3.jpg"
    ], {duration: 3000, fade: 750});

    /*
        Tooltips
    */
    $('.links a.home').tooltip();
    $('.links a.blog').tooltip();

    /*
        Form validation
    */
    $('.register form').submit(function(){
        $(this).find("label[for='username']").html('username');
        $(this).find("label[for='password']").html('password');
        $(this).find("label[for='repassword']").html('repassword');
        $(this).find("label[for='email']").html('email');
        $(this).find("label[for='mp']").html('mp');
        ////
        var firstname = $(this).find('input#username').val();
        var lastname = $(this).find('input#password').val();
        var username = $(this).find('input#repassword').val();
        var email = $(this).find('input#email').val();
        var password = $(this).find('input#mp').val();
        if(firstname == '') {
            $(this).find("label[for='username']").append("<span style='display:none' class='red'> - 请输入您的用户名.</span>");
            $(this).find("label[for='username'] span").fadeIn('medium');
            return false;
        }
        if(lastname == '') {
            $(this).find("label[for='password']").append("<span style='display:none' class='red'> - 请输入您的密码.</span>");
            $(this).find("label[for='password'] span").fadeIn('medium');
            return false;
        }
        if(username == '') {
            $(this).find("label[for='repassword']").append("<span style='display:none' class='red'> - 请确认您的密码.</span>");
            $(this).find("label[for='repassword'] span").fadeIn('medium');
            return false;
        }
        if(email == '') {
            $(this).find("label[for='email']").append("<span style='display:none' class='red'> - 请输入您的电子邮件地址.</span>");
            $(this).find("label[for='email'] span").fadeIn('medium');
            return false;
        }
        if(password == '') {
            $(this).find("label[for='mp']").append("<span style='display:none' class='red'> - 请输入您的手机号码.</span>");
            $(this).find("label[for='mp'] span").fadeIn('medium');
            return false;
        }
    });


});

var _hmt = _hmt || [];
(function() {
  var hm = document.createElement("script");
  hm.src = "//hm.baidu.com/hm.js?24b3e272281688608a11b26fa534ced6";
  var s = document.getElementsByTagName("script")[0]; 
  s.parentNode.insertBefore(hm, s);
})();

