var card = {
    id: "",
    OpenId: "",
    code: "",
    WeChatId: "",
    MemberShipId: "",
    //加载银行信息
    loadBank: function (name, bankcard, bankname) {
        var $name = $(name);
        var $bankcard = $(bankcard);
        var $bankname = $(bankname);
        $.ajax({
            type: "POST",
            url: "/tools/tool.ashx",
            data: { action: "loadBank", shipid: encodeURIComponent(card.MemberShipId) },
            dataType: "json",
            //提交服务器成功
            success: function (data) {
                if (data.BankNiChen != "")
                    $name.val(data.BankNiChen);
                if (data.BankCard != "")
                    $bankcard.val(data.BankCard);
                if (data.BankName != "")
                    $bankname.val(data.BankName);
            },
            //提交服务器错误
            error: function (xml, text, thrown) {
            }
        });
    },
    //更新银行信息
    updateBank: function (btn, name, bankcard, bankname) {
        var $sub = $(btn);
        var $name = $(name);
        var $bankcard = $(bankcard);
        var $bankname = $(bankname);
        if ($sub.length > 0 && $bankcard.length > 0) {
            $sub.live("click", function () {
                n = $name.val();
                bcard = $bankcard.val();
                bname = $bankname.val();
                if (n == "") {
                    TopBox.alert("户名不能为空!");
                } else if (!/^[\u4E00-\u9FA5]+$/.test(n)) {
                    TopBox.alert("户名必须为中文!");
                } else if (bcard == "") {
                    TopBox.alert("银行卡号不能为空!");
                } else if (bname == "") {
                    TopBox.alert("银行名称不能为空!");
                }
                else {
                    $.ajax({
                        type: "POST",
                        url: "/tools/tool.ashx",
                        data: { action: "updateBank", shipid: encodeURIComponent(card.MemberShipId), name: encodeURIComponent(n), bankcard: encodeURIComponent(bcard), bankname: encodeURIComponent(bname) },
                        dataType: "text",
                        //提交服务器成功
                        success: function (d) {
                            if (d == "1") {
                                window.location.href = './commission.aspx?code=' + card.code;
                            } else {
                                TopBox.alert("更新银行信息失败.");
                            }
                        },
                        //提交服务器错误
                        error: function (xml, text, thrown) {
                        }
                    });

                }
            });
        }
    },
    //添加会员卡信息
    add: function (btn, name, phone) {
        var $sub = $(btn);
        var $name = $(name);
        var $phone = $(phone);
        if ($sub.length > 0 && $phone.length > 0) {
            $sub.live("click", function () {
                var reg = /^1[3458]\d{9}$/;

                n = $name.val();
                p = $phone.val();
                if (n == "") {
                    TopBox.alert("姓名不能为空!");
                } else if (!/^[\u4E00-\u9FA5]+$/.test(n)) {
                    TopBox.alert("姓名必须为中文!");
                } else if (!reg.test(p)) {
                    TopBox.alert("手机格式不正确!");
                } else {
                    $.ajax({
                        type: "POST",
                        url: "/tools/tool.ashx",
                        data: { action: "addcard", mtype: 1, name: encodeURIComponent(n), phone: p, op: encodeURIComponent(card.OpenId) },
                        dataType: "text",
                        //提交服务器成功
                        success: function (d) {
                            if (d == "1") {
                                $name.val("");
                                $phone.val("");
                                TopBox.alert("会员卡申请成功.", function () { window.location.href = './card.aspx?code=' + card.code; });
                            } else if (d == "0") {
                                TopBox.alert("会员卡申请失败.");
                            } else if (d == "-2") {
                                TopBox.alert("请勿重复注册会员卡.");
                            } else {
                                TopBox.alert("信息填写不正确.");
                            }
                        },
                        //提交服务器错误
                        error: function (xml, text, thrown) {
                        }
                    });

                }
            });
        }
    },
    //加载达人注册信息
    loadDaren: function (btn, name, phone, num, total) {
        var $sub = $(btn);
        var $name = $(name);
        var $phone = $(phone);
        var $num = $(num);
        var $total = $(total);
        $.ajax({
            type: "POST",
            url: "/tools/tool.ashx",
            data: { action: "loadDaren", shipid: encodeURIComponent(card.MemberShipId) },
            dataType: "json",
            //提交服务器成功
            success: function (data) {
                if ($name.val() != undefined && data.Name != "") {
                    $name.text(data.Name);
                    if ($name.val() == "")
                        $name.val(data.Name);
                }
                if ($phone.val() != undefined && data.Phone != "") {
                    $phone.text(data.Phone);
                    if ($phone.val() == "")
                        $phone.val(data.Phone);
                }
                if ($num.val() != undefined)
                    $num.text(data.RecommendedNum);
                if ($total.val() != undefined)
                    $total.text(data.TotalCommission);
                if (data.Name != undefined && $sub.val() != undefined)
                    $sub.text("保存");
            },
            //提交服务器错误
            error: function (xml, text, thrown) {
            }
        });
    },
    //添加达人注册
    addDaren: function (btn, A1, name, phone) {
        var $sub = $(btn);
        var $A1 = $(A1);
        var $name = $(name);
        var $phone = $(phone);
        if ($sub.length > 0 && $phone.length > 0) {
            $sub.live("click", function () {
                var reg = /^1[3458]\d{9}$/;

                n = $name.val();
                p = $phone.val();
                if (n == "") {
                    TopBox.alert("姓名不能为空!");
                } else if (!/^[\u4E00-\u9FA5]+$/.test(n)) {
                    TopBox.alert("姓名必须为中文!");
                } else if (!reg.test(p)) {
                    TopBox.alert("手机格式不正确!");
                } else {
                    $sub.hide(); $A1.show();
                    $.ajax({
                        type: "POST",
                        url: "/tools/tool.ashx",
                        data: { action: "addcard", mtype: 3, name: encodeURIComponent(n), phone: p, op: encodeURIComponent(card.OpenId), shipid: encodeURIComponent(card.MemberShipId) },
                        dataType: "text",
                        //提交服务器成功
                        success: function (d) {
                            if (d == "1") {
                                TopBox.alert("达人申请成功.", function () { window.location.href = card.url; });
                            } else if (d == "0") {
                                $sub.show(); $A1.hide();
                                TopBox.alert("达人申请失败.");
                            } else if (d == "-2") {
                                $sub.show(); $A1.hide();
                                TopBox.alert("请勿重复注册达人.");
                            } else {
                                $sub.show(); $A1.hide();
                                TopBox.alert("信息填写不正确.");
                            }
                        },
                        //提交服务器错误
                        error: function (xml, text, thrown) {
                        }
                    });

                }
            });
        }
    },
    //添加经纪人信息
    Brokeradd: function (btn, A1, name, phone, BrokerType, UnitNumber, Broker) {
        var $sub = $(btn);
        var $A1 = $(A1);
        var $name = $(name);
        var $phone = $(phone);
        var $BrokerType = $(BrokerType);
        var $UnitNumber = $(UnitNumber);
        var $Broker = $(Broker);
        if ($sub.length > 0 && $phone.length > 0) {
            $sub.live("click", function () {
                var reg = /^1[3458]\d{9}$/;

                n = $name.val();
                p = $phone.val();
                t = $BrokerType.val();
                u = $UnitNumber.val();
                b = $Broker.val();
                if (n == "") {
                    TopBox.alert("姓名不能为空!");
                } else if (!/^[\u4E00-\u9FA5]+$/.test(n)) {
                    TopBox.alert("姓名必须为中文!");
                } else if (!reg.test(p)) {
                    TopBox.alert("手机格式不正确!");
                }
                else if (t == 2 && u == "") {
                    TopBox.alert("请填写单元号!");
                }
                else if (t == 3 && b == "") {
                    TopBox.alert("请填写中介公司名!");
                }
                else {
                    $sub.hide(); $A1.show();
                    $.ajax({
                        type: "POST",
                        url: "/tools/tool.ashx",
                        data: { action: "addcard", mtype: 2, name: encodeURIComponent(n), phone: p, op: encodeURIComponent(card.OpenId), type: t, unitnumber: u, broker: b },
                        dataType: "text",
                        //提交服务器成功
                        success: function (d) {
                            if (d == "1") {
                                $name.val("");
                                $phone.val("");
                                TopBox.alert("经纪人申请成功.", function () { window.location.href = './BrokerIndex.aspx?code=' + card.code; });
                            } else if (d == "0") {
                                $sub.show(); $A1.hide();
                                TopBox.alert("经纪人申请失败.");
                            } else if (d == "-2") {
                                $sub.show(); $A1.hide();
                                TopBox.alert("请勿重复注册经纪人.");
                            } else {
                                $sub.show(); $A1.hide();
                                TopBox.alert("信息填写不正确.");
                            }
                        },
                        //提交服务器错误
                        error: function (xml, text, thrown) {
                        }
                    });

                }
            });
        }
    },
    
    //推荐客户
    BrokerClientAdd: function (btn, name, phone) {
        var $sub = $(btn);
        var $name = $(name);
        var $phone = $(phone);
        if ($sub.length > 0 && $phone.length > 0) {
            $sub.live("click", function () {
                var reg = /^1[3458]\d{9}$/;

                n = $name.val();
                p = $phone.val();
                if (n == "") {
                    TopBox.alert("姓名不能为空!");
                } else if (!reg.test(p)) {
                    TopBox.alert("手机格式不正确!");
                }
                else {
                    $.ajax({
                        type: "POST",
                        url: "/tools/tool.ashx?aa=cc",
                        data: { action: "addBrokerClient", name: encodeURIComponent(n), phone: p, op: encodeURIComponent(card.OpenId) },
                        dataType: "text",
                        //提交服务器成功
                        success: function (d) {
                            if (d == "1") {
                                $name.val("");
                                $phone.val("");
                                window.location.href = './client.aspx?code=' + card.code;
                            } else if (d == "0") {
                                TopBox.alert("推荐客户失败.");
                            } else if (d == "-1") {
                                TopBox.alert("客户己存在！");
                            } else {
                                TopBox.alert("信息填写不正确.");
                            }
                        },
                        //提交服务器错误
                        error: function (xml, text, thrown) {
                        }
                    });

                }
            });
        }
    },
    Sign: function (openid) {

        $.ajax({
            type: "POST",
            url: "/tools/tool.ashx",
            data: { action: "Sign", op: encodeURIComponent(openid) },
            dataType: "text",
            //提交服务器成功
            success: function (d) {
                if (d == "0") {
                    TopBox.alert("今天已经签过到.");
                } else if (d == "-1" || d == "-2") {
                    TopBox.alert("请先注册会员卡.");
                } else {
                    TopBox.alert("签到成功.");
                    if ($("#point").length > 0) {
                        var point = parseInt($("#point").html());
                        var val = parseInt(d);

                        $("#point").html(point + val);
                        $("#signNum").html(parseInt($("#signNum").html()) + 1);
                    }
                }
            },
            //提交服务器错误
            error: function (xml, text, thrown) {
            }
        });
    }
}