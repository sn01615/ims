"use strict";
/**
 * @desc 1.账号切换功能
 *       2.站点切换功能
 * @author YangLong
 * @date 2015-03-04
 */
$(document).ready(function(e) {
    //如果是新用户则显示引导页
    // 点击切换显示文本
    $('.comboBox .selList').on('click', '>li', function() {
        $(this).parents('.comboBox').find('.defaultOp em').text($(this).find('span').text());
    });

    siteList();
    shopList();
    countDispose();
    countFeedback();
    // 站点切换事件绑定
    $("#SitesList>.selList").on("click", '>li', function(e) {
        var D = {
            'siteId': $(this).attr('sid'),
            '_rnd': loading()
        };
        $.get("?r=api/SwitchSite", D, function(data, status) {
            removeloading();
            if (status == 'success') {
                // 刷新框架内的页
                if (window.frames['main'].location.href.indexOf('Home/DisputeDetail') > -1) {
                    var m = window.frames['main'].location.href.match(/&type=([a-zA-Z_]+)&?/);
                    if (m !== null) {
                        window.frames['main'].location.href = '?r=Home/DisputeList&type=' + m[1];
                    }
                } else if (window.frames['main'].location.href.indexOf('Home/ReturnDetail') > -1) {
                    window.frames['main'].location.href = '?r=Home/ReturnList';
                } else {
                    window.frames['main'].location.reload();
                    countDispose();
                    countFeedback();
                }
                shopList();
            } else {
                alert(lang.ajaxinfo.network_error, 2);
            }
        });
    });

    // 账号切换事件绑定
    $("#AccountList>.selList").on("click", '>li', function(e) {
        var D = {
            'accountId': $(this).attr('sid'),
            '_rnd': loading()
        };
        $.get("?r=api/SwitchAccount", D, function(data, status) {
            removeloading();
            if (status == 'success') {
                // 刷新框架内的页面
                if (window.frames['main'].location.href.indexOf('Home/DisputeDetail') > -1) {
                    var m = window.frames['main'].location.href.match(/&type=([a-zA-Z_]+)&?/);
                    if (m !== null) {
                        window.frames['main'].location.href = '?r=Home/DisputeList&type=' + m[1];
                    }
                } else if (window.frames['main'].location.href.indexOf('Home/ReturnDetail') > -1) {
                    window.frames['main'].location.href = '?r=Home/ReturnList';
                } else {
                    window.frames['main'].location.reload();
                    countDispose();
                    countFeedback();
                }
                // alert('切换成功！',1);
            } else {
                alert(lang.ajaxinfo.network_error, 2);
            }
        });
    });

    // 重写alert
    function alert(info, type) {
        switch (type) {
            case 'hint_s', 1:
                type = 'hint_s';
                break;
            case 'hint_f', 2:
                type = 'hint_f';
                break;
            case 'hint_w', 3:
                type = 'hint_w';
                break;
            default:
                type = 'hint_w';
        }
        hintShow(type, info);
    }

    $("#add_label").click(function(e) {
        e.stopPropagation();
        $("#input_label_name").val('');
        $("#add_label_window").show();
    });
    $("#add_label_window_close").click(function(e) {
        $("#add_label_window").hide();
    });
    $("#add_label_window_submit").click(function(e) {
        if ($("#input_label_name").val() == '') {
            alert(lang.index_biz.label_not_empty);
            return;
        }
        var postData = {
            labeltitle: $('#input_label_name').val(),
            labelcolor: $('#label_color').attr('cc')
        };
        $.post('?r=api/AddLabel', postData, function(data, status) {
            if (data.Error == 'User authentication fails') {
                hintShow('hint_w', lang.ajaxinfo.permission_denied);
                return;
            }
            if (status == 'success') {
                if (data.Ack == 'Success') {
                    hintShow('hint_s', lang.index_biz.label_add_success);
                    $("#add_label_window").hide();
                    getlabellist();
                } else if (data.Ack == 'Warning') {
                    hintShow('hint_w', lang.index_biz.label_add_error1);
                } else {
                    hintShow('hint_f', lang.index_biz.label_add_error2);
                }
            } else {
                hintShow('hint_f', lang.index_biz.label_add_error3);
            }
        });
    });
    $("#add_label_window_cancel").click(function(e) {
        $("#add_label_window").hide();
    });

    $("#input_label_name").change(function(e) {
        this.value = this.value.replace(/^\s+|\s+$/g, '');
    });

    $("#man_label").click(function(e) {
        e.stopPropagation();
        loading('man_label');
        getlabellist();
        removeloading('man_label');
        $("#man_label_window").show();
    });
    $("#man_label_window_close").click(function(e) {
        $("#man_label_window").hide();
    });

    $("#label_color_select li span").click(function(e) {
        $("#label_color").attr('cc', $(this).attr('cc'));
    });
    $("#man_label_window").find('.TCFoot .subBtn').click(function(e) {
        $("#man_label_window").hide();
    });;

    function getlabellist() {
        $.get('?r=api/GetLabelList', function(data, status) {
            if (data.Error == 'User authentication fails') {
                hintShow('hint_w', lang.ajaxinfo.permission_denied);
                return;
            }
            if (status == 'success') {
                if (data.Ack == 'Success') {
                    $('#nav_label_list').empty();
                    var _html = '';
                    var _html2 = '';
                    for (var x in data.Body) {
                        _html += '<h3><i class="iconTab nui-tag' + data.Body[x].label_color + '"></i><a href="?r=Home/MsgList&class=label&labelid=' +
                            data.Body[x].msg_label_id + '" target="main" class="sideF3a">' + data.Body[x].label_title + '</a></h3>';

                        _html2 += '<tr label_id="' + data.Body[x].msg_label_id + '"><td><div class="showName"><span class="iconTab nui-tag' + data.Body[
                                x].label_color + '"></span><b>' + data.Body[x].label_title + '</b></div>' +
                            '<div class="editName"><input class="label_title_edit_ipt" type="text" name="" id="" value="' + data.Body[x].label_title +
                            '" maxlength="12"></div></td><td>' + data.Body[x].msg_count + '</td>' + '<td><span class="fontLink label_rename">' + lang.index_biz
                            .label_rename + '</span>&nbsp;&nbsp;&nbsp;&nbsp;<span class="fontLink label_del">' + lang.index_biz.label_delete +
                            '</span></td></tr>';
                    }
                    $('#nav_label_list').html(_html);
                    $('#man_label_window_list').html(_html2);
                    if (window.frames['main'].refreshMsgList) {
                        window.frames['main'].refreshMsgList();
                    }
                    if (window.frames['main'].get_label_list) {
                        window.frames['main'].get_label_list();
                    }
                } else {
                    hintShow('hint_f', lang.ajaxinfo.internal_error);
                }
            } else {
                hintShow('hint_f', lang.ajaxinfo.network_error);
            }
        });
    }
    getlabellist();

    function getautolabellist() {
        $.get('?r=api/getAutoLabelList', function(data, status) {
            if (data.Error == 'User authentication fails') {
                hintShow('hint_w', lang.ajaxinfo.permission_denied);
                return;
            }

            if (status == 'success') {
                if (data.Ack == 'Success') {
                    $('#nav_auto_label_list').empty();
                    var _html = '';
                    for (var x in data.Body) {
                        _html += '<h3><a href="?r=Home/MsgList&class=autotag&tagid=' + data.Body[x].msg_auto_label_id;
                        _html += '" target="main" class="sideF3a">' + data.Body[x].auto_label_name + '</a></h3>';
                    }
                    $('#nav_auto_label_list').html(_html);
                } else {
                    hintShow('hint_f', lang.ajaxinfo.internal_error);
                }
            } else {
                hintShow('hint_f', lang.ajaxinfo.network_error);
            }
        });
    }
    getautolabellist();

    $('#man_label_window_list').on('click', '.label_rename', function(e) {
        $(this).closest('tr').find('.showName').hide();
        $(this).closest('tr').find('.editName .label_title_edit_ipt').show().focus();
    });
    $('#man_label_window_list').on('blur', '.label_title_edit_ipt', function(e) {
        $(this).closest('tr').find('.showName').show();
        $(this).closest('tr').find('.editName .label_title_edit_ipt').hide();
    });
    $('#man_label_window_list').on('change', '.label_title_edit_ipt', function(e) {
        var postdata = {
            labeltitle: $(this).val(),
            labelcolor: '',
            labelid: $(this).closest('tr').attr('label_id')
        };
        loading();
        var _this = $(this);
        $.post('?r=api/EditLabel', postdata, function(data, status) {
            removeloading();
            if (data.Error == 'User authentication fails') {
                _this.val(_this.closest('td').find('.showName b').text());
                hintShow('hint_w', lang.ajaxinfo.permission_denied);
                return;
            }
            if (status == 'success') {
                if (data.Ack == 'Success') {
                    _this.val(data.Body.label_title);
                    _this.closest('tr').find('.showName b').html(data.Body.label_title);
                    getlabellist();
                    window.frames["main"].reloadlist();
                } else if (data.Ack == 'Warning' && data.Body == 'exist') {
                    hintShow('hint_w', lang.index_biz.label_repeated);
                } else {
                    hintShow('hint_f', lang.ajaxinfo.internal_error);
                }
            } else {
                hintShow('hint_f', lang.ajaxinfo.network_error);
            }
        });
    });
    $('#man_label_window_list').on('click', '.label_del', function(e) {
        var postdata = {
            labelid: $(this).closest('tr').attr('label_id')
        };
        var _this = $(this);
        confirmFun(function() {
            loading();
            $.post('?r=api/DelLabel', postdata, function(data, status) {
                removeloading();
                if (data.Error == 'User authentication fails') {
                    hintShow('hint_w', lang.ajaxinfo.permission_denied);
                    return;
                }
                if (status == 'success') {
                    if (data.Ack == 'Success') {
                        _this.closest('tr').remove();
                        hintShow('hint_s', lang.index_biz.label_delete_success);
                        getlabellist();
                    } else {
                        hintShow('hint_f', lang.ajaxinfo.internal_error);
                    }
                } else {
                    hintShow('hint_f', lang.ajaxinfo.network_error);
                }
            });
        }, function() {

        }, lang.com.confirm_delete);
    });

    $("#lang_select").on('click', 'li', function() {
        loading();
        $.get('?r=api/SetLanguage', {
            'lang': $(this).data('lang')
        }, function(data, status) {
            removeloading();
            if (data.Error == 'User authentication fails') {
                hintShow('hint_w', lang.ajaxinfo.permission_denied);
                return;
            }
            if (status == 'success') {
                if (data.Ack == 'Success') {
                    window.location.reload();
                } else {
                    hintShow('hint_f', lang.ajaxinfo.internal_error);
                }
            } else {
                hintShow('hint_f', lang.ajaxinfo.network_error);
            }
        });
    });

});

try {
    if (getCookie('remember_url').length > 0) {
        $("iframe[name='main']").attr("src", getCookie('remember_url'));
        $(document).ready(function(e) {
            window.autoUnfold(getCookie('remember_url'));
        });
    } else {
        $("iframe[name='main']").attr("src", '?r=Home/MsgList&class=pending');
    }
} catch (ex) {}

// 循环执行，每隔30秒钟执行一次 
setInterval(function() {
    if (window.frames['main'].location.href.indexOf('Home/MsgList') > -1) {
        countDispose();
    } else {
        $.get('?r=api');
    }
}, 30000);

/**
 * @desc 查询待处理数
 * @author liaojianwen
 * @date 2015-06-11
 */
function countDispose() {
    $.get('?r=api/GetMsgHandCount', function(data, status) {
        if (status === 'success' && data.Ack === 'Success') {
            var x = data.Body[0] ? +data.Body[0] : 0;
            var y = data.Body[1] ? +data.Body[1] : 0;
            // $('#disposeCount b').text('('+data.Body[0]+'/'+(+data.Body[0]+(+data.Body[1]))+')');
            $('#disposeCount b').text('(' + ((+x) + (+y)) + ')');
        }
    });
}

/**
 * @desc 查询feedback条数
 * @author liaojianwen
 * @date 2015-09-06
 */
function countFeedback() {
    $.get('?r=api/GetFeedbackCount', function(data, status) {
        if (status === 'success' && data.Ack === 'Success') {
            $('#fedComprehensive b').text('(' + data.Body.comprehensive.count + ')');
            $('#fedPositive b').text('(' + data.Body.positive.count + ')');
            $('#fedNeutral b').text('(' + data.Body.neutral.count + ')');
            $('#fedNegative b').text('(' + data.Body.negative.count + ')');
        }
    });
}

/**
 * @desc 站点列表数据项绑定
 * @author YangLong
 * @deate 2015-03-04
 */
function siteList() {
    $.get("?r=api/GetSiteList&filter=1", function(data, status) {
        // removeloading();
        if (status == 'success') {
            $("#SitesList>.selList").empty();
            $("#SitesList>.selList").append('<li sid="-1">' + lang.index_biz.all_site + '</li>');
            for (var i in data.Body.sites) {
                $("#SitesList>.selList").append('<li sid="' + i + '"><span>' + data.Body.sites[i] + '</span>' +
                    (true ? '' : '<b>10</b>') + '</li>'); // @todo 计数
            }
            if (typeof data.Body.current !== 'undefined') {
                setTimeout(function() {
                    $("#SitesList>.selList").find('li[sid="' + data.Body.current + '"]').click();
                }, 200);
            }
        } else {
            alert(lang.ajaxinfo.network_error, 2);
        }
    });
}

/**
 * @desc 账号列表数据项绑定
 * @author YangLong
 * @deate 2015-03-04
 */
function shopList() {
    $.get("?r=api/GetShopList", function(data, status) {
        if (status == 'success') {
            $("#AccountList>.selList").empty();
            $("#AccountList>.selList").append('<li sid="">' + lang.index_biz.all_account + '</li>');
            $("#AccountList").find('.defaultOp em').text(lang.index_biz.text_account);
            for (var i in data.Body.list) {
                if (data.Body.list[i].status != 2) {
                    $("#AccountList>.selList").append('<li sid="' + data.Body.list[i].shop_id + '"><span>' + data.Body.list[i].nick_name + (data.Body.list[i].status ==
                            2 ? '(' + lang.index_biz.shop_disable + ')' : '') + '</span>' +
                        (true ? '' : '<b>10</b>') + '</li>'); // @todo 计数
                }
            }
            if (typeof data.Body.current !== 'undefined') {
                setTimeout(function() {
                    $(parent.document).find('#AccountList .defaultBox em').text($("#AccountList>.selList").find('li[sid=' + data.Body.current + ']').text());
                }, 200);
            }
        } else {
            alert(lang.ajaxinfo.network_error, 2);
        }
    });
}