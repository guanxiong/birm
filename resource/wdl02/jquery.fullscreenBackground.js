(function ($) {
    var parentElement = "";
    var optionsArr = {
        selector: "img",
        fillOnResize: true,
        defaultCss: true
    };

    $.fn.fullscreenBackground = function (options) {
        if(options) {
            $.extend(optionsArr, options );
        }

        this.each(function () {
            parentElement = this;
            if (optionsArr.defaultCss == true) {
                $("html,body").css({
                    width: "100%",
                    height: "100%"
                });

                $(parentElement).css({
                    height: "100%",
                    width: "100%",
                    overflow: "hidden",
                    //position: "fixed",
                    position: "relative",
                    top: "0px",
                    left: "0px",
                    zIndex: 1
                });
            }

            if (optionsArr.fillOnResize == true) {
                $(window).resize(function () {
                    fillBg(optionsArr.selector, parentElement);
                });
            }

            fillBg(optionsArr.selector, parentElement);
        });
    };

    function fillBg(selector, parentobj) {
        var windowHeight = $(window).height();
        var windowWidth = $(window).width();

        $(selector, parentobj).each(function () {
            var imgHeight = $(this).attr("height");
            var imgWidth = $(this).attr("width");

            var newWidth = windowWidth;
            var newHeight = (windowWidth / imgWidth) * imgHeight;
            var topMargin = ((newHeight - windowHeight) / 2) * -1;
            var leftMargin = 0;

            if (newHeight < windowHeight) {
                var newWidth = (windowHeight / imgHeight) * imgWidth;
                var newHeight = windowHeight;
                var topMargin = 0;
                var leftMargin = ((newWidth - windowWidth) / 2) * -1;
            }
            else if(windowWidth > imgWidth){
                var newWidth = imgWidth;
                var newHeight = imgHeight;
                var topMargin = (windowHeight - imgHeight) / 2;
                var leftMargin = (windowWidth - imgWidth) / 2;
            }
            $(this).css({
                height: newHeight + "px",
                width: newWidth + "px",
                marginLeft: leftMargin + "px",
                marginTop: topMargin + "px"
                //display: "block"
            });
            //以下是IE6，7，8的hack
            $(".bannerTool").css("height",windowHeight-89-70);
            if ($.browser.msie) {
                if ($.browser.version == "6.0" || $.browser.version == "7.0" || $.browser.version == "8.0"){
                    $(".bannerTool").css("height",windowHeight-89-80);
                    if(windowHeight<669){
                        $(".textBanner").hide();
                        $(".bannerTool shape").hide();
                    }
                    else{
                        $(".textBanner").show();
                        $(".bannerTool shape").show();
                    }
                    if(windowHeight<395){
                        $(".toolBottom").hide();
                    }
                    else{
                        $(".toolBottom").show();
                    }
                }
                if ($.browser.version == "6.0"){
                        if(windowWidth<1349){
                            $("#background-image").css("width","1349");
                            $(".navList").css("width","1349");
                            $(".nav").css("width","1349");
                        }
                        else{
                            $("#background-image").css("width","auto");
                            $(".navList").css("width","auto");
                            $(".nav").css("width","auto");
                        }
                    }
                }
        });
    }
})(jQuery);