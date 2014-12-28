
if ($.os.webkit ? false : true && $.os.fennec ? false : true && $.os.ie ? false : true && $.os.opera ? false : true) {
    $.os.webkit = true;
    $.feat.cssPrefix = $.os.webkit ? "Webkit" : "";
}



(function ($) {

    $.fn.popupbobing = function (opts) {
        return new popupbobing(this[0], opts);
    };
    var queue = [];
    var popupbobing = (function () {
        var popupbobing = function (containerEl, opts) {

            if (typeof containerEl === "string" || containerEl instanceof String) {
                this.container = document.getElementById(containerEl);
            } else {
                this.container = containerEl;
            }
            if (!this.container) {
                alert("Error finding container for popupbobing " + containerEl);
                return;
            }

            try {
                if (typeof (opts) === "string" || typeof (opts) === "number")
                    opts = {
                        message: opts,
                        cancelOnly: "true",
                        cancelText: "OK"
                    };
                this.id = id = opts.id = opts.id || $.uuid(); 
                var self = this;
                this.title = opts.suppressTitle ? "" : (opts.title || "Alert");
                this.message = opts.message || "";
                this.cancelText = opts.cancelText || "Cancel";
                this.cancelCallback = opts.cancelCallback || function () { };
                this.cancelClass = opts.cancelClass || "button";
                this.doneText = opts.doneText || "Done";
                this.doneCallback = opts.doneCallback || function (self) {
                   
                };
                this.doneClass = opts.doneClass || "button";
                this.cancelOnly = opts.cancelOnly || false;
                this.onShow = opts.onShow || function () { };
                this.autoCloseDone = opts.autoCloseDone !== undefined ? opts.autoCloseDone : true;

                queue.push(this);
                if (queue.length == 1)
                    this.show();
            } catch (e) {
                console.log("error adding popupbobing " + e);
            }

        };

        popupbobing.prototype = {
            id: null,
            title: null,
            message: null,
            cancelText: null,
            cancelCallback: null,
            cancelClass: null,
            doneText: null,
            doneCallback: null,
            doneClass: null,
            cancelOnly: false,
            onShow: null,
            autoCloseDone: true,
            supressTitle: false,
            show: function () {
                var self = this;
                var markup = '<div id="' + this.id + '" class="afpopupbobing hidden">' +
                            '<header>' + this.title + '</header>' +
                             '<div>' + this.message + '</div>' +
                             '<footer style="clear:both;">' +
                                 '<a href="javascript:;" class="' + this.cancelClass + '" id="cancel">' + this.cancelText + '</a>' +
                                 '<a href="javascript:;" class="' + this.doneClass + '" id="action">' + this.doneText + '</a>' +
                            ' </footer>' +
                         '</div></div>';
                $(this.container).append($(markup));

                var $el = $.query("#" + this.id);
                $el.bind("close", function () {
                    self.hide();
                });

                if (this.cancelOnly) {
                    $el.find('A#action').hide();
                    $el.find('A#cancel').addClass('center');
                }
                $el.find('A').each(function () {
                    var button = $(this);
                    button.bind('click', function (e) {
                        if (button.attr('id') == 'cancel') {
                            self.cancelCallback.call(self.cancelCallback, self);
                            self.hide();
                        } else {
                            self.doneCallback.call(self.doneCallback, self);
                            if (self.autoCloseDone)
                                self.hide();
                        }
                        e.preventDefault();
                    });
                });
                self.positionpopupbobing();
                $.blockUI(0.5);

                $el.bind("orientationchange", function () {
                    self.positionpopupbobing();
                });

              
                $el.find("header").show();
                $el.find("footer").show();
                setTimeout(function () {
                    $el.removeClass('hidden');
                    self.onShow(self);
                }, 50);
            },

            hide: function () {
                var self = this;
                $.query('#' + self.id).addClass('hidden');
                $.unblockUI();
                if (!$.os.ie && !$.os.android) {
                    setTimeout(function () {
                        self.remove();
                    }, 250);
                }
                else
                    self.remove();
            },

            remove: function () {
                var self = this;
                var $el = $.query("#" + self.id);
                $el.unbind("close");
                $el.find('BUTTON#action').unbind('click');
                $el.find('BUTTON#cancel').unbind('click');
                $el.unbind("orientationchange").remove();
                queue.splice(0, 1);
                if (queue.length > 0)
                    queue[0].show();
            },

            positionpopupbobing: function () {
                var popupbobing = $.query('#' + this.id);
                popupbobing.css("top", ((window.innerHeight / 2.5) + window.pageYOffset) - (popupbobing[0].clientHeight / 2) + "px");
                popupbobing.css("left", (window.innerWidth / 2) - (popupbobing[0].clientWidth / 2) + "px");
            }
        };

        return popupbobing;
    })();
    var uiBlocked = false;
    $.blockUI = function (opacity) {
        if (uiBlocked)
            return;
        opacity = opacity ? " style='opacity:" + opacity + ";'" : "";
        $.query('BODY').prepend($("<div id='mask'" + opacity + "></div>"));
        $.query('BODY DIV#mask').bind("touchstart", function (e) {
            e.preventDefault();
        });
        $.query('BODY DIV#mask').bind("touchmove", function (e) {
            e.preventDefault();
        });
        uiBlocked = true;
    };

    $.unblockUI = function () {
        uiBlocked = false;
        $.query('BODY DIV#mask').unbind("touchstart");
        $.query('BODY DIV#mask').unbind("touchmove");
        $("BODY DIV#mask").remove();
    };

})(af);

var bobing = [];
bobing.remainder = null;
bobing.level = null;
bobing.status = 0;
bobing.num = new Array(6)
bobing.flagtime;


bobing.moveStart = function () {
    that = this;
    document.getElementById('bobigaudio').play()
    function animQueue1(num) {
        var pic = $("#pic" + num);
		pic.css("display",'block');
        pic.css3Animate({
            x: 150,
            y: 50,
            previous: false,
            time: "200ms",
            callback: function () {
                pic.css3Animate({
                    x: 20,
                    y: 50,
                    previous: false,
                    time: "250ms",
                    callback: function () {
                        pic.css3Animate({
                            x: 50,
                            y: 0,
                            previous: false,
                            time: "300ms",
                            callback: function () {
                                pic.css3Animate({
                                    x: 70,
                                    y: 40,
                                    previous: false,
                                    time: "350ms",
                                    callback: function () {
                                        pic.css3Animate({
                                            x: 80,
                                            y: 10,
                                            previous: false,
                                            time: "350ms",
                                            callback: function () {
                                                pic.css3Animate({
                                                    x: 80,
                                                    y: 0,
                                                    previous: false,
                                                    time: "400ms",
                                                    callback: function () {
                                                        pic.css3Animate({
                                                            x: 20,
                                                            y: 70,
                                                            previous: false,
                                                            time: "400ms",
                                                            callback: function () {
                                                                pic.css3Animate({
                                                                    x: 0,
                                                                    y: 0,
                                                                    previous: false,
                                                                    time: "500ms",
                                                                    callback: function () {

                                                                    }
                                                                });
                                                            }
                                                        });
                                                    }
                                                });
                                            }
                                        });
                                    }
                                });
                            }
                        });
                    }
                });
            }
        });
    };
    function animQueue2(num) {
        var pic = $("#pic" + num);
		pic.css("display",'block');
        pic.css3Animate({
            x: 0,
            y: 130,
            previous: false,
            time: "200ms",
            callback: function () {
                pic.css3Animate({
                    x: -100,
                    y: 30,
                    previous: false,
                    time: "200ms",
                    callback: function () {
                        pic.css3Animate({
                            x: 20,
                            y: 30,
                            previous: false,
                            time: "250ms",
                            callback: function () {
                                pic.css3Animate({
                                    x: 40,
                                    y: 80,
                                    previous: false,
                                    time: "50ms",
                                    callback: function () {
                                        pic.css3Animate({
                                            x: -90,
                                            y: 80,
                                            previous: false,
                                            time: "400ms",
                                            callback: function () {
                                                pic.css3Animate({
                                                    x: -30,
                                                    y: 130,
                                                    previous: false,
                                                    time: "500ms",
                                                    callback: function () {
                                                        pic.css3Animate({
                                                            x: 0,
                                                            y: 0,
                                                            previous: false,
                                                            time: "600ms",
                                                            callback: function () {

                                                            }
                                                        });
                                                    }
                                                });
                                            }
                                        });
                                    }
                                });
                            }
                        });
                    }
                });
            }
        });
    };
    function animQueue3(num) {
        var pic = $("#pic" + num);
		pic.css("display",'block');
        pic.css3Animate({
            x: -50,
            y: 20,
            previous: false,
            time: "200ms",
            callback: function () {
                pic.css3Animate({
                    x: -50,
                    y: -130,
                    previous: false,
                    time: "200ms",
                    callback: function () {
                        pic.css3Animate({
                            x: 40,
                            y: -100,
                            previous: false,
                            time: "300ms",
                            callback: function () {
                                pic.css3Animate({
                                    x: -70,
                                    y: 10,
                                    previous: false,
                                    time: "350ms",
                                    callback: function () {
                                        pic.css3Animate({
                                            x: -110,
                                            y: -60,
                                            previous: false,
                                            time: "350ms",
                                            callback: function () {
                                                pic.css3Animate({
                                                    x: 50,
                                                    y: -60,
                                                    previous: false,
                                                    time: "500ms",
                                                    callback: function () {
                                                        pic.css3Animate({
                                                            x: 0,
                                                            y: 0,
                                                            previous: false,
                                                            time: "450ms",
                                                            callback: function () {
                                                            }
                                                        });
                                                    }
                                                });
                                            }
                                        });
                                    }
                                });
                            }
                        });
                    }
                });
            }
        });
    };
    function animQueue4(num) {
        var pic = $("#pic" + num);
		pic.css("display",'block');
        pic.css3Animate({
            x: -150,
            y: -50,
            previous: false,
            time: "300ms",
            callback: function () {
                pic.css3Animate({
                    x: -120,
                    y: 80,
                    previous: false,
                    time: "300ms",
                    callback: function () {
                        pic.css3Animate({
                            x: -20,
                            y: 80,
                            previous: false,
                            time: "400ms",
                            callback: function () {
                                pic.css3Animate({
                                    x: -50,
                                    y: -60,
                                    previous: false,
                                    time: "400ms",
                                    callback: function () {
                                        pic.css3Animate({
                                            x: -90,
                                            y: 80,
                                            previous: false,
                                            time: "500ms",
                                            callback: function () {
                                                pic.css3Animate({
                                                    x: 0,
                                                    y: 0,
                                                    previous: false,
                                                    time: "550ms",
                                                    callback: function () {

                                                    }
                                                });
                                            }
                                        });
                                    }
                                });
                            }
                        });
                    }
                });
            }
        });
    };
    function animQueue5(num) {
        var pic = $("#pic" + num);
		pic.css("display",'block');
        pic.css3Animate({
            x: 30,
            y: 80,
            previous: false,
            time: "100ms",
            callback: function () {
                pic.css3Animate({
                    x: 80,
                    y: 20,
                    previous: false,
                    time: "200ms",
                    callback: function () {
                        pic.css3Animate({
                            x: 80,
                            y: -30,
                            previous: false,
                            time: "300ms",
                            callback: function () {
                                pic.css3Animate({
                                    x: 60,
                                    y: -60,
                                    previous: false,
                                    time: "300ms",
                                    callback: function () {
                                        pic.css3Animate({
                                            x: 40,
                                            y: -80,
                                            previous: false,
                                            time: "300ms",
                                            callback: function () {
                                                pic.css3Animate({
                                                    x: -10,
                                                    y: -80,
                                                    previous: false,
                                                    time: "300ms",
                                                    callback: function () {
                                                        pic.css3Animate({
                                                            x: -30,
                                                            y: -50,
                                                            previous: false,
                                                            time: "300ms",
                                                            callback: function () {
                                                                pic.css3Animate({
                                                                    x: -50,
                                                                    y: -20,
                                                                    previous: false,
                                                                    time: "300ms",
                                                                    callback: function () {
                                                                        pic.css3Animate({
                                                                            x: 0,
                                                                            y: 0,
                                                                            previous: false,
                                                                            time: "350ms",
                                                                            callback: function () {

                                                                            }
                                                                        });
                                                                    }
                                                                });
                                                            }
                                                        });
                                                    }
                                                });
                                            }
                                        });
                                    }
                                });
                            }
                        });
                    }
                });
            }
        });
    };
    function animQueue6(num) {
        var pic = $("#pic" + num);
		pic.css("display",'block');
        pic.css3Animate({
            x: 0,
            y: 50,
            previous: false,
            time: "150ms",
            callback: function () {
                pic.css3Animate({
                    x: 30,
                    y: 80,
                    previous: false,
                    time: "150ms",
                    callback: function () {
                        pic.css3Animate({
                            x: 80,
                            y: 80,
                            previous: false,
                            time: "200ms",
                            callback: function () {
                                pic.css3Animate({
                                    x: 110,
                                    y: 50,
                                    previous: false,
                                    time: "200ms",
                                    callback: function () {
                                        pic.css3Animate({
                                            x: 130,
                                            y: 20,
                                            previous: false,
                                            time: "200ms",
                                            callback: function () {
                                                pic.css3Animate({
                                                    x: 130,
                                                    y: -20,
                                                    previous: false,
                                                    time: "250ms",
                                                    callback: function () {
                                                        pic.css3Animate({
                                                            x: 90,
                                                            y: -60,
                                                            previous: false,
                                                            time: "350ms",
                                                            callback: function () {
                                                                pic.css3Animate({
                                                                    x: 60,
                                                                    y: -60,
                                                                    previous: false,
                                                                    time: "350ms",
                                                                    callback: function () {
                                                                        pic.css3Animate({
                                                                            x: 30,
                                                                            y: -30,
                                                                            previous: false,
                                                                            time: "400ms",
                                                                            callback: function () {
                                                                                pic.css3Animate({
                                                                                    x: 0,
                                                                                    y: 0,
                                                                                    previous: false,
                                                                                    time: "400ms",
                                                                                    callback: function () {

                                                                                    }
                                                                                });
                                                                            }
                                                                        });
                                                                    }
                                                                });
                                                            }
                                                        });
                                                    }
                                                });
                                            }
                                        });
                                    }
                                });
                            }
                        });
                    }
                });
            }
        });
    };
    
    var pic = function (time) {
       time = parseInt(time) + 100;
        var arr = new Array(6);
        for (var i = 0; i < 6; i++) {
            arr[i] = Math.floor(Math.random() * 6 + 1);
        }
		pic1.style.backgroundImage = "url(./source/modules/bbb/template/images/g1.png)";
        pic2.style.backgroundImage = "url(./source/modules/bbb/template/images/g1.png)";
        pic3.style.backgroundImage = "url(./source/modules/bbb/template/images/g1.png)";
        pic4.style.backgroundImage = "url(./source/modules/bbb/template/images/g1.png)";
        pic5.style.backgroundImage = "url(./source/modules/bbb/template/images/g1.png)";
        pic6.style.backgroundImage = "url(./source/modules/bbb/template/images/g1.png)";
		
        that.flagtime = setTimeout(function () { pic(time) }, time);
    }
    pic("400")

    $el = $("#bobigwan>div");
    $el.attr("style", "animation:rotate 3s;-moz-animation:rotate 3s;-webkit-animation:rotate 3s; -o-animation:rotate 3s;");
    window.setTimeout(function () {
       
        $el.removeAttr("style");
    }, 3000);

    animQueue1(1)
    animQueue2(2)
    animQueue3(3)
    animQueue4(4)
    animQueue5(5)
    animQueue6(6)
};
bobing.moveEnd = function () {
    window.clearTimeout(this.flagtime);
    for (var i = 0; i < 6; i++) {
        var id = "pic" + (1 + i);
        document.getElementById(id).style.backgroundImage = "url(./source/modules/bbb/template/images/d" + this.num[i] + ".png)";
    }
    window.setTimeout(function () { that.ending(); }, 500);

};
bobing.ending = function () {
    that = this;
    if (this.level>5) {
      $.query('body').popupbobing({
			id: "resultpopup",
			message: '恭喜,得到' + this.level + '积分',
		});
		$("#resultpopup").bind("click", function () { $("#resultpopup").trigger("close"); that.status == 0 });
    } else {
		$.query('body').popupbobing({
            id: "promptoutpopup",
            message: this.errmessage,
        });	
		$("#resultpopup").bind("click", function () { $("#resultpopup").trigger("close"); that.status == 0 });
    };
     
    this.theRemaining.innerHTML = this.remainder;
	this.mytotals.innerHTML = this.mytotal;
	this.userconts.innerHTML = this.usercont;
    


}


bobing.binding = function () {

	
    that = this;
    if (window.DeviceMotionEvent) {
        var speed = 10;
        var x = y = lastX = lastY = 0;
        window.addEventListener('devicemotion', function (e) {
            var acceleration = e.accelerationIncludingGravity;
            x = parseInt(acceleration.x);
            y = parseInt(acceleration.y);
            if (Math.abs(x - lastX) > speed && Math.abs(y - lastY) > speed && this.status == 0) {
                bobing.start();
                $(".afpopupbobing").trigger("close");
                lastX = lastY = 0;
            };
        });


    } else {
       
    }


}

bobing.start = function () {
    that = this;
    
        if (this.remainder > 0) {
            if (this.status == 0) {
                this.status = 1;
                this.level = null;
                form_data = eval('[{ username:"' + this.username + '"}]');
                $.post(bburl, form_data[0],
                      function (data) {
                          var queryString = data
                          arr = eval('(' + queryString + ')');
                          that.level = arr.level.key;
                          that.username = arr.user.name;
                          that.num = [arr.level.a, arr.level.b, arr.level.c, arr.level.d, arr.level.e, arr.level.f];
                          that.remainder = arr.user.num;
						  that.mytotal = arr.user.mytotal;
						  that.usercont = arr.user.usercont;
						 
						  that.errmessage = arr.errmessage;
                      })
                this.moveStart();
                window.setTimeout(function () {
                    if (that.level && that.username) {
                        that.status = 2;
                        that.moveEnd();
                        window.setTimeout(function () { that.status = 0; }, 1500);
                    } else if (that.username == null || that.username == "") {
                        that.status = 0;
                       that.num = [2, 3, 6, 1, 5, 2];
                        that.moveEnd();
                        $.query('body').popupbobing({
                            id: "promptoutpopup",
                            message: '登录超时'
                        });
                        $("#promptoutpopup").bind("click", function () { $("#promptoutpopup").trigger("close"); });
                    }else {
                        that.status = 0;
                       that.num = [2, 3, 6, 1, 5, 2];
                        that.moveEnd();
                        $.query('body').popupbobing({
                            id: "promptwwwpopup",
                            message: '网络出问题啦!'
                        });
                        $("#promptwwwpopup").bind("click", function () { $("#promptwwwpopup").trigger("close"); });
                    };

                }, 3000);
            }
           
        } else {
			var wannei = $("#wannei");
			wannei.css("display",'none');
            $.query('body').popupbobing({
                id: "promptnopopup",
                message: '今天次数用完,点右上角分享到朋友圈或发送给朋友,叫朋友来为你助威吧!'
            });
			//$("#promptnopopup").bind("click", function () { $("#promptnopopup").trigger("close"); });

        };
    

};


var initializebobing = function () {
    bobing.theRemaining = document.getElementById("theRemaining");
	bobing.mytotals = document.getElementById("mytotals");
	bobing.userconts = document.getElementById("usercont");
    bobing.binding();
   
}
