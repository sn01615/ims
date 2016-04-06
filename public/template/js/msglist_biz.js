"use strict";
/**
 * @desc 消息列表js
 *       1、显示列表
 *       2、标记已读、未读、删除、还原、标星、取消标星、标记待办、取消待办
 * @author liaojianwen
 * @date 2015-03-11
 */
$(document).ready(function(e) {
    inputFocusFun($('.dfVal'), lang.msglist_biz.search_dfvalue); //调用搜索表单默认内容方法--linpeiyan
    //动态调整表格宽度--linpeiyan--2015-9-17
    switch ($_GET['class']) {
        case 'sent':
            $('#td_f').css('width', '100');
            $('#td_s').css('width', '200');
            break;
        case 'sys':
            $('#td_f').css('width', '100');
            $('#td_s').css('width', '100');
            break;
        case 'star':
            $('#td_f').css('width', '200');
            $('#td_s').css('width', '200');
            break;
        case 'delete':
            $('#td_f').css('width', '200');
            $('#td_s').css('width', '200');
            break;
        default:
            $('#td_f').css('width', '200');
            $('#td_s').css('width', '100');
    }

    var msgid;
    var global_info = {
        'listType': $_GET['class'] ? $_GET['class'] : 'pending',
        'classID': $_GET['classID'],
        'page': $_GET['page'] ? $_GET['page'] : undefined,
        'pageSize': $_GET['pageSize'] ? $_GET['pageSize'] : undefined,
        'searchCon': undefined,
        'labelid': $_GET['labelid'] ? $_GET['labelid'] : undefined,
        'tagid': $_GET['tagid'] ? $_GET['tagid'] : undefined
    }
    if (global_info.listType === 'pending') {
        $('<li id = "undisposes"><span>' + lang.msglist_biz.no_process + '</span></li>').appendTo('.selList');
    }

    $("#aggregate_btn").on("change", function(e) {
        if ($(this)[0].checked) {
            global_info.aggregate = 1;
        } else {
            global_info.aggregate = 0;
        }
        global_info.page = 1;
        ShowList();
    });

    if (global_info.labelid || global_info.listType == 'star') {
        $("#aggregate_btn")[0].checked = false;
        global_info.aggregate = 0;
    } else {
        global_info.aggregate = 1;
    }

    ShowList();
    window.refreshMsgList = function() {
        ShowList({
            'page': global_info.page,
            'pageSize': global_info.pageSize
        });
    };

    (function() {
        window.parent.$("#nav_label_list").find('a').each(function(index, element) {
            $(element).css('font-weight', 'normal')
        });
        window.parent.$("#nav_label_list").find('a[href="?r=Home/MsgList&class=label&labelid=' + global_info.labelid + '"]').css('font-weight', 'bold');
    })();

    /**
     * @desc 邮件列表中功能实现的函数
     * @author liaojianwen
     * @date 2015-02-17
     * @param object obj 是要进行操作的对象
     * @param string urlStr 是url
     */
    function listAjax(obj, urlStr, type) {
        obj.on('click', function() {
            $('#markSel .selList').hide();
            var getAllId_str = getAllId($('#msg_list input[type=checkbox]:checked'));
            if (getAllId_str.length == 0) {
                hintShow('hint_w', lang.msglist_biz.all_checked);
            } else {
                if (global_info.listType == 'delete' && obj[0].id == 'delete') {
                    // 已删除页面删除
                    confirmFun(function() {
                        $.ajax({
                            url: urlStr + getAllId_str + "&ron=" + Math.random(),
                            success: function(data, status) {
                                if ((status == 'success' && data.Ack == 'Failure')) {
                                    hintShow('hint_f', lang.msglist_biz.failure);
                                    ShowList({
                                        'page': global_info.page,
                                        'pageSize': global_info.pageSize
                                    });
                                    $('#checkall').attr("checked", false);
                                } else {
                                    if (data.Error == 'User authentication fails') {
                                        hintShow('hint_w', lang.ajaxinfo.permission_denied);
                                    } else {
                                        hintShow('hint_s', lang.msglist_biz.success);
                                        ShowList({
                                            'page': global_info.page,
                                            'pageSize': global_info.pageSize
                                        });
                                        $('#checkall').attr("checked", false);
                                        CallEbayAPI(type);
                                    }
                                }
                            }
                        });
                    }, function() {}, lang.msglist_biz.del_confirm);
                } else {
                    if (obj[0].id == 'delete' || obj[0].id == 'revert') {
                        confirmFun(function() {
                            $.ajax({
                                url: urlStr + getAllId_str + "&ron=" + Math.random(),
                                success: function(data, status) {
                                    if (status == 'success' && data.Ack == 'Failure') {
                                        ShowList({
                                            'page': global_info.page,
                                            'pageSize': global_info.pageSize
                                        });
                                        $('#checkall').attr("checked", false);
                                    } else {
                                        if (data.Error == 'User authentication fails') {
                                            hintShow('hint_w', lang.ajaxinfo.permission_denied);
                                        } else {
                                            ShowList({
                                                'page': global_info.page,
                                                'pageSize': global_info.pageSize
                                            });
                                            $('#checkall').attr("checked", false);
                                            CallEbayAPI(type, getAllId_str);
                                            hintShow('hint_s', lang.msglist_biz.success);
                                        }
                                    }
                                }
                            });
                        }, function() {}, obj[0].id == 'delete' ? lang.msglist_biz.del_confirm : lang.msglist_biz.restore_confirm);
                    } else {
                        $.ajax({
                            url: urlStr + getAllId_str + "&ron=" + Math.random(),
                            success: function(data, status) {
                                if (status == 'success' && data.Ack == 'Failure') {
                                    ShowList({
                                        'page': global_info.page,
                                        'pageSize': global_info.pageSize
                                    });
                                    $('#checkall').attr("checked", false);
                                } else {
                                    if (data.Error == 'User authentication fails') {
                                        hintShow('hint_w', lang.ajaxinfo.permission_denied);
                                    } else {
                                        ShowList({
                                            'page': global_info.page,
                                            'pageSize': global_info.pageSize
                                        });
                                        $('#checkall').attr("checked", false);
                                        CallEbayAPI(type, getAllId_str);
                                    }
                                }
                            }
                        });
                    }
                }
            }
        })
    }

    /**
     * @desc 调用eBay API
     * @author liaojianwen
     * @date 2015-03-19
     */
    function CallEbayAPI(type, getAllId_str) {
        if (type == 'read') {
            $.get('?r=api/SetReadMyMessages', {
                'mids': getAllId_str
            }, function(data, status) {
                if (data.Error == 'User authentication fails') {
                    hintShow('hint_w', lang.ajaxinfo.permission_denied);
                }
            });
        } else if (type == 'unread') {
            $.get('?r=api/SetMsgNoReadByApi', {
                'mids': getAllId_str
            }, function(data, status) {
                if (data.Error == 'User authentication fails') {
                    hintShow('hint_w', lang.ajaxinfo.permission_denied);
                }
            });
            //        }else if(type =='delete'){
            //            $.get('?r=api/DeleteMyMessages',{'mids':getAllId_str},function(data,status){
            //                if(data.Error=='User authentication fails'){
            //                    hintShow('hint_w','你没有该操作的权限！');
            //                }else{
            //                    hintShow('hint_s','删除成功！');
            //                }
            //            });
        } else if (type == 'revert') {
            $.get('?r=api/RevertMyMessages', {
                'mids': getAllId_str
            }, function(data, status) {
                if (data.Error == 'User authentication fails') {
                    hintShow('hint_w', lang.ajaxinfo.permission_denied);
                } else {
                    hintShow('hint_s', lang.msglist_biz.restore_suc);
                }
            });
        }
    }

    //标记已读
    listAjax($('#read'), '?r=api/SetMsgRead&g=list&mids=', 'read');
    //标记未读
    listAjax($('#unread'), '?r=api/SetMsgNoRead&mids=', 'unread');
    //批量标记为星
    listAjax($('#stars'), '?r=api/SetMsgStar&mids=');
    //批量取消标星
    listAjax($('#unstars'), '?r=api/CancelMegStar&mids=');
    //批量不需处理
    listAjax($('#undisposes'), '?r=api/SetMsgHandleStatus&mids='); // TODO
    //还原
    listAjax($('#revert'), '?r=api/RevertMessages&mids=', 'revert');

    //删除页面
    if (global_info.listType == 'delete') {
        //彻底删除
        listAjax($('#delete'), '?r=api/SetMsgHide&mids=', '');
        $('#revert').show();
    } else {
        //删除
        listAjax($('#delete'), '?r=api/SetMsgDel&mids=', 'delete');
    }

    /**
     * @desc 显示邮件列表
     * @author liaojianwen
     * @date 2015-02-27
     * @param array pagaInfo 页面信息页码，页数
     */
    function ShowList(pageInfo) {
        if (pageInfo != undefined) {
            global_info.page = pageInfo.page;
            global_info.pageSize = pageInfo.pageSize;
        }

        loading();
        $.get('?r=api/GetMsgList', global_info, function(data, status) {
            removeloading();
            $("#msg_list").find('tr').remove();
            if (status == 'success' && data.Ack == 'Success') {
                var M = data.Body.list;
                if (M && M.length > 0) {
                    for (var i in M) {
                        var stared = +M[i].is_star ? 'icon-star iconBtnH star' : 'icon-star-empty  iconBtnH star';
                        var dispose = +M[i].handled ? 'icon-time starTimed iconBtnH dispose' : 'icon-time iconBtnH dispose';
                        var Replied = +M[i].send_status ? 'icon-warning-sign' : (+M[i].Read ? (+M[i].Replied ? 'icon-reply' : 'readed') :
                            'icon-envelope');
                        var starvalue = M[i].is_star;
                        var disposevalue = M[i].handled;
                        var type = +M[i].Read ? '' : 'unread';
                        var displayDate = intToLocalDate(M[i].ReceiveDate, 3);
                        var labelstr = '';
                        for (var j in M[i].labels) {
                            labelstr += '<div class="nui-tag nui-tag' + M[i].labels[j].label_color + '"> <span class="nui-tag-text">';
                            labelstr += M[i].labels[j].label_title + '</span> <span class="nui-tag-close" title="' + lang.msglist_biz.del_label;
                            labelstr += '" data-labelid="' + M[i].labels[j].msg_label_id + '"><b>x</b></span> </div>';
                        }

                        var _html;
                        _html = '<tr class="' + type + '" value="' + M[i].Read + '" data-id="' + M[i].msg_id + '">';
                        _html += '<td class ="checkbox"><input type="checkbox" class="ids" data-id="' + M[i].msg_id + '" /></td>';
                        _html += '<td class="status"><i class="' + Replied + '" eq="0"></i>' + (M[i].is_img > 0 ? '<i class="icon-acc"></i>' : '');
                        _html += (M[i].BuyerCheckoutMessage ? '<i class="icon-remark" title="' + M[i].BuyerCheckoutMessage + '"></i>' : '') + '</td>';
                        _html += '<td><span>' + M[i].Sender + '</span></td>' + '<td><span>' + (M[i].SendToName == '' ? M[i].RecipientUserID : M[i].SendToName);
                        _html += '</span></td>' + '<td class="pointer" title="' + M[i].Subject + '"><div class="labelTd">' + labelstr + '</div><span>' +
                            M[i].Subject + '</span></td>';
                        // _html+='<td class="labelTd"></td>';
                        _html += '<td title="' + lang.msglist_biz.add_label +
                            '" class="addLabelBtn iconTd"><i class="icon-tab iconBtnH star"></i></td>';
                        _html += '<td title="' + lang.msglist_biz.star + '" class="start iconTd" value="' + starvalue + '" ><i class="' + stared +
                            '" ></i></td>';
                        _html += '<td>' + displayDate + '</td>' + '</tr>';

                        $(_html).appendTo('#msg_list');
                    }

                } else {
                    $('<tr><td colspan="8" align="center">' + lang.msglist_biz.no_data + '</td></tr>').appendTo('#msg_list');
                }
                var pageInfo = data.Body.page;
                if (typeof pageInfo !== 'undefined') {
                    refreshPaginationNavBar(pageInfo.page, pageInfo.pageSize, data.Body.count, ShowList); //分页
                }

                //全选
                allCheckBox($('#checkall'), $('.ids'));

                //标记为星
                $('.start').on('click', function(e) {
                    e.stopPropagation();
                    var value = $(this).attr('value');
                    var result = 0;
                    var getId = $(this).parent().attr('data-id');
                    var dat = {
                        'mids': getId
                    };
                    if (value == "0") {
                        $(this).attr('value', '1');
                        $.get('?r=api/SetMsgStar', dat, function(data, result) {
                            if (data.Error == 'User authentication fails') {
                                hintShow('hint_w', lang.ajaxinfo.permission_denied);
                            } else {
                                ShowList({
                                    'page': global_info.page,
                                    'pageSize': global_info.pageSize
                                });
                                $('#checkall').attr("checked", false);
                                setCookie('star', 1, 1);
                                setCookie('StarMsgId', getId, 1);
                            }
                        });
                    } else if (value == "1") {
                        $(this).attr('value', '0');
                        $.get('?r=api/CancelMegStar', dat, function(data, result) {
                            if (data.Error == 'User authentication fails') {
                                hintShow('hint_w', lang.ajaxinfo.permission_denied);
                            } else {
                                ShowList({
                                    'page': global_info.page,
                                    'pageSize': global_info.pageSize
                                });
                                $('#checkall').attr("checked", false);
                                setCookie('star', 2, 1);
                                setCookie('StarMsgId', getId, 1);
                            }
                        });
                    }
                })

                //标记待处理 //TODO REMOVE IT XXX
                $('.disposeP').on('click', function(e) {
                    $.get('?r=api/SaveNotification');
                })
            } else if (status == 'success' && data.Ack == 'Failure') {
                $('<tr><td colspan="8" align="center">' + lang.msglist_biz.no_data + '</td></tr>').appendTo('#msg_list');
            } else {
                $('<tr><td colspan="8" align="center">' + lang.ajaxinfo.newtwork_error_s + '</td></tr>').appendTo('#msg_list');
            }
        });
    }

    var $msg_list = $('#msg_list');
    // 点击进入明细
    $msg_list.on('click', 'tr', function() {
        try {
            var _selectText = window.getSelection();
            if (_selectText.focusOffset > 0 && _selectText.type == 'Range') {
                return;
            }
        } catch (e) {}
        var thisTr = $(this).closest('tr').attr('data-id');
        var page = global_info.page === undefined ? '' : global_info.page;
        var pageSize = global_info.pageSize === undefined ? '' : global_info.pageSize;
        if (thisTr != undefined) {
            window.parent.back_url = '';
            window.parent.back_url = location.href + "&page=" + page + "&pageSize=" + pageSize;
            var WindowDetail = window.open('?r=home/MsgDetail&mids=' + thisTr + '&class=' + global_info.listType);
            // 修改信息读取状态,后台处理，操作状态不作处理
            var is_read = $(this).attr('value');
            if (!(+is_read)) {
                $.get('?r=api/SetMsgRead', {
                    'mids': thisTr,
                    'g': 'detail'
                }, function(data, status) {
                    if (status == 'success' && data.Ack == 'Success') {
                        ShowList({
                            'page': global_info.page,
                            'pageSize': global_info.pageSize
                        });
                    } else {
                        if (data.Error == 'User authentication fails') {
                            // hintShow('hint_w','你没有该操作的权限！');
                        }
                    }
                });
            }
        }
    });

    $msg_list.on('click', '.nui-tag', function(e) {
        e.stopPropagation();
    });

    $msg_list.on('click', '.labelTd .nui-tag-close', function(e) {
        var gd = {
            labelid: $(this).data('labelid'),
            msgid: $(this).closest('tr').data('id')
        }
        var _self = $(this);
        loading();
        $.get('?r=api/RemoveMsgLabel', gd, function(data, status) {
            removeloading();
            if (status == 'success' && data.Ack == 'Success') {
                hintShow('hint_s', lang.msglist_biz.label_deleted);
                _self.closest('.nui-tag').remove();
                ShowList();
            } else {
                if (data.Error == 'User authentication fails') {
                    hintShow('hint_w', lang.ajaxinfo.permission_denied);
                }
            }
        });
    })

    //=========================鼠标移动到已添加的标签上显示删除标签按钮--linpeiyan--2015-8-25====================
    $('#msg_list').on('mouseenter', '.nui-tag', function(e) {
        $(this).find('.nui-tag-close').stop().animate({
            width: 18
        }, 100);
    });

    $('#msg_list').on('mouseleave', '.nui-tag', function(e) {
        $(this).find('.nui-tag-close').stop().animate({
            width: 0
        }, 100);
    });

    //=========================点击添加标签弹-出标签选择弹窗--linpeiyan--2015-8-25=============================
    $msg_list.on('click', '.addLabelBtn', function(e) {
        msgid = $(this).closest('tr').data('id');
        e.stopPropagation();
        clearTimeout(_lh);
        var nTop = $(this).offset().top + 10;
        var nLeft = $(this).offset().left + 30;
        $('.labelTc').show(0).css({
            top: nTop,
            left: nLeft
        });
    })
    var _lh;
    $msg_list.on('mouseout', '.addLabelBtn *', function(e) {
        clearTimeout(_lh);
        _lh = setTimeout(function() {
            $('.labelTc').hide();
        }, 500);
    })
    $msg_list.on('mouseover', '.addLabelBtn *', function(e) {
        clearTimeout(_lh);
    })
    $('.labelTc').on('mouseover', '*', function() {
        clearTimeout(_lh);
    });
    $('.labelTc').on('mouseout', '*', function() {
        clearTimeout(_lh);
        _lh = setTimeout(function() {
            $('.labelTc').hide();
        }, 500);
    });

    //设置冒泡，使复选框不能触发点击进入明细的事件
    $msg_list.on('click', '.checkbox', function(e) {
        e.stopPropagation();
    });

    //绑定搜索功能事件
    $('#search').on('click', function() {
        global_info.searchCon = $('#searchName').val();
        global_info.page = 1;
        ShowList();
    });

    //查询绑定enter键 // 为什么是整个文档？
    document.onkeydown = function(event) {
        if ((event.keyCode || event.which) == 13) {
            global_info.searchCon = $('#searchName').val();
            global_info.page = 1;
            ShowList();
        }
    }

    // 循环执行，每隔1秒钟执行一次 
    setInterval(function() {
        var stared = +getCookie('star')
        var disposed = +getCookie('dispose');
        var replied = +getCookie('replied');
        var reply_failure_id = localStorage.reply_failure_id;
        localStorage.removeItem('reply_failure_id');
        //标记标星
        if (stared === 1) {
            $("#msg_list").find('tr[data-id="' + getCookie('StarMsgId') + '"]').find('.start i').removeClass('icon-star-empty').addClass('icon-star');
        } else if (stared === 2) {
            $("#msg_list").find('tr[data-id="' + getCookie('StarMsgId') + '"]').find('.start i').removeClass('icon-star').addClass('icon-star-empty');
            if (global_info.listType === 'star') {
                $("#msg_list").find('tr[data-id="' + getCookie('StarMsgId') + '"]').remove();
            }
        }
        //待办
        if (global_info.listType === undefined) {
            global_info.listType = 'pending';
        }
        if (disposed === 0 && global_info.listType === 'pending') {
            $("#msg_list").find('tr[data-id="' + getCookie('DisposeMsgId') + '"]').remove();
        }
        var $row = $("#msg_list").find('tr[data-id="' + getCookie('RepliyMigId') + '"]').find('.status i[eq="0"]');
        if (+reply_failure_id) {
            $row.attr('class', 'icon-warning-sign');
        };
        //标记回复
        if (+replied) {
            if (!$row.hasClass('icon-warning-sign')) {
                $row.attr('class', 'icon-reply');
            };
        }
    }, 1000);

    $('#searchName').keypress(function(e) {
        if (e.charCode === 13) {
            $("#search").click();
        }
    });

    (function() {
        $("#set_msg_label_sel").on("click", 'li', function(e) {
            var postdata = {
                labelid: $(this).attr('labelid'),
                msgid: msgid
            };
            if ($('#msg_list').find('tr[data-id="' + msgid + '"] .labelTd').find('[data-labelid="' + postdata.labelid + '"]').length > 0) {
                hintShow('hint_w', lang.msglist_biz.warning);
                return;
            }
            loading();
            $.post('?r=api/SetMsgLabel', postdata, function(data, status) {
                removeloading();
                if (data.Error == 'User authentication fails') {
                    hintShow('hint_w', lang.ajaxinfo.permission_denied);
                    return;
                }
                if (status == 'success') {
                    if (data.Ack == 'Success') {
                        $('.labelTc').hide();
                        hintShow('hint_s', lang.msglist_biz.set_suc);
                        var labelstr = '<div class="nui-tag nui-tag' + data.Body.label_color + '"> <span class="nui-tag-text">' + data.Body
                            .label_title + '</span> <span class="nui-tag-close" title="' + lang.msglist_biz.del_label + '" data-labelid="' +
                            data.Body.msg_label_id + '"><b>x</b></span> </div>';
                        if ($("#msg_list").find('tr[data-id="' + msgid + '"] .labelTd').find('[data-labelid="' + data.Body.msg_label_id +
                                '"]').length == 0) {
                            $("#msg_list").find('tr[data-id="' + msgid + '"] .labelTd').append(labelstr);
                        }
                        autowidth();
                    } else {
                        hintShow('hint_f', lang.ajaxinfo.internel_error);
                    }
                } else {
                    hintShow('hint_f', lang.ajaxinfo.network_error_s);
                }
            });
        });
    })();

    window.reloadlist = function() {
        ShowList({
            'page': global_info.page,
            'pageSize': global_info.pageSize
        });
    }

    $("#question_type").on('click', 'input[name="QuestionType"]', function() {
        global_info.QuestionType = $(this).val();
        ShowList();
    });

    $("#message_type").on('click', 'input[name="MessageType"]', function() {
        global_info.MessageType = $(this).val();
        ShowList();
    });

});

function autowidth() {
    var list = $("#msg_list");
    list.find('tr').each(function(index, el) {
        var td4 = $(el).find('td:eq(4)');
        td4.find('>span').width(td4.width() - td4.find('.labelTd').width() - 35);
    });
}

setInterval(function() {
    autowidth();
}, 1000)

$(window).resize(function() {
    autowidth();
});

function get_label_list() {
    loading();
    $.get('?r=api/GetLabelList', function(data, status) {
        removeloading();
        if (data.Error == 'User authentication fails') {
            hintShow('hint_w', lang.ajaxinfo.permission_denied);
            return;
        }
        if (status == 'success') {
            if (data.Ack == 'Success') {
                $("#set_msg_label_sel").empty();
                var _html = '';
                for (var x in data.Body) {
                    _html += '<li labelid="' + data.Body[x].msg_label_id + '"><span class="iconTab nui-tag' + data.Body[x].label_color + '"></span><b>' + data.Body[
                        x].label_title + '</b></li>';
                }
                $("#set_msg_label_sel").html(_html);
            } else {
                hintShow('hint_f', lang.ajaxinfo.internel_error);
            }
        } else {
            hintShow('hint_f', lang.ajaxinfo.network_error_s);
        }
    });
}

get_label_list();