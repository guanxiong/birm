(function() {
  var e, _EVENTS;

  require.config({
    urlArgs: "_v=0.13",
    baseUrl: '/source/modules/feng_testing/template/mobile/js',
    paths: {
      'zepto': 'zepto.min',
      'domReady': "domReady",
      'mustache': 'mustache',
      'cross_http': 'corssdomain_http',
      'weixin_api': "weixin_api"
    }
  });

  _EVENTS = {
    tap: 'tap'
  };

  try {
    document.domain = 'qq.com';
  } catch (_error) {
    e = _error;
  }

  if (!/(android)|(iphone)|(ipod)/i.test(navigator.userAgent)) {
    _EVENTS.tap = 'click';
  }

  require(['zepto', 'corssdomain_http', 'weixin_api'], function($, crossHttp, WeixinApi) {
    var host, pages, panshi, survery, trace, user, _load_share_data, _load_timeline_data;
    pages = {
      option: {
        'window': {
          'width': null
        },
        page: {
          number: null
        }
      },
      init: function() {
        this.option.window.width = $(window).width();
        this.option.page.number = $('.page').length;
        $('.pages').width(this.option.window.width * this.option.page.number);
        $('.page').width(this.option.window.width);
        $.fx.off = false;
        return this.page_item = $(".pages");
      },
      next: function(e) {
        var index, left, page, width;
        width = this.option.window.width;
        page = $(e.target).parents('.page').eq(0);
        index = $('.page').index(page);
        left = width * index + width;
        return this.page_item.animate({
          translateX: -1 * left + 'px'
        }, 300, 'ease-out', function() {});
      },
      start: function(e) {
        var home;
        home = $(e.target).parents('.page').eq(0);
        return home.animate({
          scale: '3,3',
          opacity: 0
        }, 500, 'ease-out', function() {
          return home.remove();
        });
      }
    };
    survery = {
      html: [],
      score: 0,
      subject_length: 0,
      subject_count: 0,
      result: {},
      answer: {},
      init: function() {
        var i, item, self, _i, _len, _ref,
          _this = this;
        self = this;
        if (window.page_config.subject != null) {
          this.subject_count = window.page_config.subject.length;
          this.subject_length = window.page_config.subject.length;
          _ref = window.page_config.subject;
          for (i = _i = 0, _len = _ref.length; _i < _len; i = ++_i) {
            item = _ref[i];
            this.creat_item(i + 1, item);
          }
          $('.home').after(this.html.join(''));
        }
        this.creat_result();
        $(document).on(_EVENTS.tap, '.option', function(e) {
          var optionid, score, subjectid;
          self.subject_count -= 1;
          score = parseInt($(this).attr('score'));
          optionid = $(this).attr('optionid');
          subjectid = $(this).parent().attr('subjectid');
          if (optionid) {
            self.answer[subjectid] = {
              selected: [optionid]
            };
          }
          self.score += score;
          if (self.subject_count === 0) {
            return self.end();
          }
        });
        $(document).on(_EVENTS.tap, '#share', function(e) {
          return _this.share();
        });
        $(document).on(_EVENTS.tap, '#again', function(e) {
          return _this.again();
        });
        $(document).on(_EVENTS.tap, '#weixin', function(e) {
          return _this["public"]();
        });
        // return $("#weixin").hide();
      },
      get_params: function() {
        return this.answer;
      },
      creat_item: function(i, data) {
        var html, option, _i, _len, _ref;
        var desc = '';
        if(data.desc){
          desc = data.desc;
          desc = desc.replace("\r\n","<br />");
        } 
        if(window.page_config.banner_link==null || window.page_config.banner_link==''){
          window.page_config.banner_link = '#';
        }
        html = [];
        html.push('<div class="page">\
                  <div class="page-content">\
                      <a href="'+ window.page_config.banner_link +'" class="banner"></a>\
                      <div class="progress"><div><span>' + i + '</span>/' + this.subject_length + '</div></div>\
                      <h2>' + data.title + '</h2>\
                      <p>' + desc + '</p>\
                      <ul class="options">');
        _ref = data.option;
        for (_i = 0, _len = _ref.length; _i < _len; _i++) {
          option = _ref[_i];
          html.push('<li class="next option" score="' + option.score + '">' + option.title + '</li>');
        }
        html.push('</ul></div></div>');
        return this.html.push(html.join(''));
      },
      creat_result: function() {
        var html;
        html = [];
        html.push('<a href="'+ window.page_config.banner_link +'" class="banner"></a>');
        if ((window.page_config.show_score != null) && window.page_config.show_score === false) {

        } else {
          html.push('<div class="score"><b>0</b>');
          if ((window.page_config.index != null) && (window.page_config.index.name != null)) {
            html.push('<span>你的<i>' + window.page_config.index.name + '</i>指数</span>');
          }
          html.push('</div>');
        }
        html.push('<p class="discription"></p>');
        html.push('<ul class="options">');
        html.push('<li id="share"><span class="share">给好友看看</span></li>');
        if (window.page_config.next.url != null) {
          html.push('<li id="again">' + window.page_config.next.title + '</li>');
        }
        if (window.page_config.weixin != null) {
          html.push('<li id="weixin">点击进入' + window.page_config.weixin.nickname + '微信公众号</li>');
        }
        var banner_ad = $('.banner_ad').html();
        if(banner_ad){
          html.push(banner_ad);
        }
        html.push('</ul>');
        return $('.result .page-content').html(html.join(''));
      },
      find_result: function() {
        var end, result, start, _i, _len, _ref, _results;
        _ref = window.page_config.result;
        _results = [];
        for (_i = 0, _len = _ref.length; _i < _len; _i++) {
          result = _ref[_i];
          start = result.range_start;
          end = result.range_end;
          if (this.score >= start && this.score <= end) {
            this.result = result;
            break;
          } else {
            _results.push(void 0);
          }
        }
        return _results;
      },
      share: function() {
        return $(".share_overmask").show();
      },
      again: function() {
        return window.location.href = window.page_config.next.url;
      },
      "public": function() {
        var nickname, username;
        try {
          if (window.page_config.weixin != null) {
            username = window.page_config.weixin.username;
            nickname = window.page_config.weixin.nickname;
            if (window.WeixinJSBridge != null) {
              WeixinJSBridge.invoke("profile", {
                "username": username,
                "nickname": nickname
              });
            }
          }
        } catch (_error) {
          e = _error;
          return alert(e.message);
        }
      },
      end: function() {
        this.find_result();
        $('.result .score b').html(this.score);
        $('.result .discription').html(this.result.summary);
        if ((this.result.conclusion != null) && this.result.conclusion !== '') {
          $('.result .discription').before('<div class="conclusion"><span>' + this.result.conclusion + '</span></div>');
        }
        // return panshi.post();
      },
      style: function() {
        var banner, color, cover, style_config, _style;
        style_config = window.page_config.style;
        color = style_config.color;
        if (style_config.color_action == null) {
          style_config.color_action = color;
        }
        if (style_config.color_result == null) {
          style_config.color_result = color;
        }
        cover = style_config.cover;
        banner = style_config.banner;
        _style = '<style type="text/css">';
        if (window.page_config.next.url === '') {
          _style += "#again{display:none;}";
        }
        _style += '\
          /*banner图*/\
          .page-content {background-image: url(' + banner + ');}\
          /*封面图*/\
          .home .page-content {background-image: url(' + cover + ');}\
          /* 主题色*/\
          .progress div,h2{color:' + color + ';}\
          .progress div,h2{border:5px solid ' + color + ';}\
          .options li,.home .start span{background-color:' + color + ';}\
          /*主题按下色*/\
          .options li:active,.home .start span:active{background-color:' + style_config.color_action + ';}\
          /*结果反色*/\
          .result .score{background-color:' + style_config.color_result + ';}\
          .result .score span i{color:' + style_config.color_result + ';}';
        _style += '</style>';
        return $("head").append($(_style));
      }
    };
    survery.style();
    _load_share_data = function() {
      var config, data;
      data = {};
      config = window.page_config;
      data.imgUrl = config.share.icon;
      data.link = config.publish_url;
      if (!data.link) {
        data.link = parent.window.location.href;
      }
      if (!((survery.result != null) && survery.result.summary)) {
        data.title = config.share.title;
        data.desc = config.share.abstract;
        return data;
      }
      if (config.index.name == null) {
        config.index.name = '';
      }
      if ((window.page_config.show_score != null) && window.page_config.show_score === false) {
        data.title = survery.result.conclusion + '，来回答几道题，看看你的测试结果！';
      } else {
        data.title = '我的' + config.index.name + '指数为:' + $('.score b').html() + '!' + config.share.abstract;
      }
      data.desc = survery.result.summary;
      return data;
    };
    _load_timeline_data = function() {
      var data;
      data = _load_share_data();
      data.desc = data.title;
      return data;
    };
    return $(function() {
      try {
        WeixinApi.ready(function(Api) {
          Api.shareToFriend(_load_share_data);
          Api.shareToTimeline(_load_timeline_data);
          return Api.shareToWeibo(_load_share_data);
        });
      } catch (_error) {
        e = _error;
        trace(e.message);
      }
      if (window.page_config.appid != null) {
        window.page_config.appid = 532001601;
      }
      // user.is_login();
      survery.init();
      pages.init();
      $(document).on(_EVENTS.tap, '.next', function(e) {
        return pages.next(e);
      });
      $(document).on(_EVENTS.tap, '.start', function(e) {
        try {
          return pages.start(e);
        } catch (_error) {
          e = _error;
          return alert(e);
        }
      });
      $(document).on(_EVENTS.tap, '.share_overmask', function() {
        return $(".share_overmask").hide();
      });
      return $(".page-loading-mask").animate({
        opacity: 0
      }, 500, 'ease-out', function() {
        return $(".page-loading-mask").remove();
      });
    });
  });

}).call(this);