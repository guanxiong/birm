function dialogshow(url, title, options, events) {
        var modalobj = $('#modal-message');
        var defaultoptions = {'remote' : '', 'show' : true};
        var defaultevents = {};
        var option = $.extend({}, defaultoptions, options);
        var events = $.extend({}, defaultevents, events);

        if(modalobj.length == 0) {
            $(document.body).append('<div id="modal-message" class="modal hide fade" tabindex="-1" role="dialog" aria-hidden="true" style="position:absolute;"></div>');
            var modalobj = $('#modal-message');
        }
        html = '<div class="aui_state_lock aui_state_focus" style="position: absolute; top: 0px; display: block; width: 100%; z-index: 1994;"><div class="aui_outer"><table class="aui_border"><tbody><tr><td class="aui_nw"></td><td class="aui_n"></td><td class="aui_ne"></td></tr><tr><td class="aui_w"></td><td class="aui_c"><div class="aui_inner"><table class="aui_dialog"><tbody><tr><td colspan="2" class="aui_header"><div class="aui_titleBar"><div class="aui_title" style="cursor: move; display: block;">标注商铺位置</div></div></td></tr><tr><td class="aui_icon" style="display: none;"><div class="aui_iconBg" style="background-image: none; background-position: initial initial; background-repeat: initial initial;"></div></td><td class="aui_main"><div class="aui_content" style="padding: 5px 0px;"><iframe style="zoom: 0.6;"  height="'+option['height']+'" src="'+url+'" frameBorder="0" width="99.6%"></iframe></div></td></tr><tr><td colspan="2" class="aui_footer"><div class="aui_buttons"></div></td></tr></tbody></table></div></td><td class="aui_e"></td></tr><tr><td class="aui_sw"></td><td class="aui_s"></td><td class="aui_se" style="cursor: se-resize;"></td></tr></tbody></table></div></div>';
        modalobj.html(html);
        if (typeof option['width'] != 'undeinfed' && option['width'] > 0) {
            modalobj.css({'width' : option['width'], 'marginLeft' : 0 - option['width'] / 2});
        }
        if (typeof option['height'] != 'undeinfed' && option['height'] > 0) {
            modalobj.find('.modal-body').css({'max-height' : option['height']});
        }

        modalobj.on('hidden', function(){modalobj.remove();});
        if (typeof events['confirm'] == 'function') {
            modalobj.find('.confirm', modalobj).on('click', events['confirm']);
        }
        return modalobj.modal(option);
    }

    function maps() {
        x=$('#locationx').val();
        y=$('#locationy').val();
        dialogshow('./source/modules/card/assist/location.php?x='+x+'&y='+y, '地图详情', {'width': '720', 'height': '700'}, {'hide': 'saveMenuAction', 'shown': 'loadRules'});
    }