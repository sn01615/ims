"use strict";
/**
 * @desc 1.店铺管理列表的添加\删除\启用\禁用\分页等
 * @author YangLong
 * @date 2015-02-27
 */
$(document).ready(function(e) {
    var L_pid, L_type, L_pageSize, L_page;

    // C_page为分页组件调用列表时传入的分页参数
    function loadlist(C_page) {

        // 分页相关处理
        if (C_page) {
            L_page = C_page.page;
            L_pageSize = C_page.pageSize;
        }

        // 清空列表
        $("#list_table tbody").empty();

        // 获取列表（带分页和页码，返回结果包含列表和记录总数以及分页信息）
        loading();
        $.get("?r=api/GetShopList", {
            'page': L_page,
            'pageSize': L_pageSize
        }, function(d, status) {
            removeloading();
            if (status == 'success') {
                if (d && typeof d.Body.list !== 'undefined' && typeof d.Body.page !== 'undefined') {
                    var pageInfo = d.Body.page,
                        T = d.Body.list;
                    // 判断是否有数据
                    if (typeof pageInfo.count !== 'undefined' && pageInfo.count == 0) {
                        $('<tr><td colspan="11" align="center">' + lang.shopset_biz.shop_list.no_data + '</td></tr>').appendTo("#list_table tbody");
                        $("#paginationNavBar").empty();
                        return;
                    }

                    var _date = new Date();
                    var _ts = _date.getTime();
                    // 遍历绑定数据行
                    for (var i in T) {
                        switch (T[i].status) {
                            case '1':
                                T[i].status = '<ddd class="greenFont">' + lang.shopset_biz.shop_list.shop_status.normal + '</ddd>';
                                T[i].status += ' / <a class="disable_btn fontBtn">' + lang.shopset_biz.shop_list.shop_status.disable + '</a>';
                                break;
                            case '2':
                                T[i].status = '<ddd class="xxxFont">' + lang.shopset_biz.shop_list.shop_status.disabled + '</ddd>';
                                T[i].status += ' / <a class="enable_btn fontBtn">' + lang.shopset_biz.shop_list.shop_status.enable + '</a>';
                                break;
                            case '3':
                                T[i].status = '<ddd class="yellowFont">' + lang.shopset_biz.shop_list.shop_status.unauthorized + '</ddd>';
                                T[i].status += ' / <a class="enable_btn fontBtn">' + lang.shopset_biz.shop_list.shop_status.re_authorized + '</a>';
                                break;
                            case '4':
                                T[i].status = '<ddd class="xxxFont">' + lang.shopset_biz.shop_list.shop_status.authorized_exp + '</ddd>';
                                T[i].status += ' / <a class="enable_btn fontBtn">' + lang.shopset_biz.shop_list.shop_status.re_authorized + '</a>';
                                break;
                        }

                        var _ets = '';
                        if (T[i].HardExpirationTime * 1000 - _ts > 3600 * 24 * 30 * 3) {
                            _ets = '<ddd class="greenFont">' + lang.shopset_biz.shop_list.ets.tip1 + '</ddd>';
                        } else if (T[i].HardExpirationTime * 1000 - _ts > 3600 * 24 * 30 * 1) {
                            _ets = '<ddd class="">' + lang.shopset_biz.shop_list.ets.tip2 + '</ddd>';
                        } else if (T[i].HardExpirationTime * 1000 - _ts > 0) {
                            _ets = '<ddd class="redFont">' + lang.shopset_biz.shop_list.ets.tip3 + '</ddd>';
                        } else if (T[i].HardExpirationTime == 0) {
                            _ets = '<ddd class="">' + lang.shopset_biz.shop_list.ets.tip4 + '</ddd>';
                        } else {
                            _ets = '<ddd class="redFont">' + lang.shopset_biz.shop_list.ets.tip5 + '</ddd>';
                        }
                        _ets += ' / <a class="fontBtn re_auth_btn">' + lang.shopset_biz.shop_list.ets.tip6 + '</a>'

                        $('<tr shop_id="' + T[i].shop_id + '"><td><input type="checkbox" value="' + T[i].shop_id + '" class="ids" /></td><td>' + (parseInt(i) + 1) + '</td><td>Ebay</td><td>' + T[i].site_name + '</td><td t="nkname" class="pointer" title="' + lang.shopset_biz.shop_list.list.title + '" data-s="' + T[i].nick_name + '">' + T[i].nick_name + '</td><td>' + intToLocalDate(T[i].updated_time, 1) + '</td><td>' + _ets + '</td><td>' + '<a class="fontBtn permission_setting">' + lang.shopset_biz.shop_list.list.seting + '</a>' + '</td><td>' + T[i].status + '</td><td><i class="icon-edit iconBtnH " title="' + lang.shopset_biz.shop_list.list.edit + '"></i></td>' + '<td><i class="icon-trash iconBtnH" title="' + lang.shopset_biz.shop_list.list.cancel + '"></i></td></tr>').appendTo("#list_table tbody");
                    }
                    // 分页(按钮更新)
                    refreshPaginationNavBar(pageInfo.page, pageInfo.pageSize, pageInfo.count, loadlist);
                } else {
                    alert(lang.ajaxinfo.internal_error, 2);
                }
            } else {
                alert(lang.ajaxinfo.network_error, 2);
            }
        });
    }

    // 打开时加载列表
    loadlist();

    // 店铺别名编辑功能绑定
    $("#list_table").on('click', 'td[t="nkname"]', function() {
        if ($(this).find('input').length == 0) {
            var nkname = $(this).text();
            $(this).html('<input name="" type="text" value="' + nkname + '" />');
            $(this).find('input').focus();
        }
    });
    // 编辑框失去焦点事件绑定
    $("#list_table").on('blur', 'td[t="nkname"]>input', function() {
        var _this = $(this);
        var _thistd = _this.closest('td');
        if (_this.data('ischange') == 1) {
            $.get('?r=api/SetShopNickname', {
                nickname: _this.val(),
                shopId: _this.closest('tr').attr('shop_id')
            }, function(data, textStatus) {
                if (textStatus == 'success' && data.Ack == 'Success') {
                    alert(lang.shopset_biz.save_suc, 1);
                    _this.data('ischange', 0);
                    _thistd.data('s', data.Body.nick_name);
                    _thistd.html(data.Body.nick_name);
                    if (window.top.shopList) {
                        window.top.shopList();
                    }
                } else {
                    if (data.Error == 'User authentication fails') {
                        hintShow('hint_w', lang.ajaxinfo.permission_denied);
                    } else if (data.Ack == "Warning") {
                        hintShow('hint_w', lang.ajaxinfo.permission_denied);
                    } else {
                        alert(lang.shopset_biz.save_err, 2);
                    }
                    _thistd.html(_thistd.data('s'));
                }
            });
        } else {
            // 数据没有改变
        }
        _this.parent().html(_this.val());
    });
    // 编辑框内容改变事件绑定
    $("#list_table").on('change', 'td[t="nkname"]>input', function() {
        $(this).data('ischange', 1);
    });

    // 列表内删除按钮事件绑定
    $("#list_table").on("click", '.icon-trash', function() {
        var _self = $(this);
        confirmFun(function() {
            loading();
            $.get("?r=api/SetShopDel", {
                'sids': _self.closest('tr').attr('shop_id')
            }, function(d, status) {
                removeloading();
                if (d.Error == 'User authentication fails') {
                    hintShow('hint_w', lang.ajaxinfo.permission_denied);
                } else {
                    if (d && typeof d.Ack !== 'undefined' && d.Ack == 'Success') {
                        _self.closest('tr').remove();
                        loadlist();
                        // 取消选择
                        $("#checkall")[0].checked = false;
                        parent.$('#SitesList>ul>li[sid=-1]').click(); // TODO
                    } else if (d.Ack == 'Warning') {
                        hintShow('hint_w', lang.ajaxinfo.permission_denied);
                    } else {
                        alert(lang.shopset_biz.del_err, 2);
                    }
                }
            });
        }, function() {}, lang.shopset_biz.sure);
    });

    // 列表内编辑按钮事件绑定
    $("#list_table").on("click", '.icon-edit', function() {
        $(this).closest('tr').find('td[t="nkname"]').click();
    });

    // 禁用账号功能绑定
    $("#list_table").on("click", '.disable_btn', function() {
        loading();
        $.get("?r=api/SetShopStatus", {
            'sids': $(this).closest('tr').attr('shop_id'),
            action: 'disable'
        }, function(d, status) {
            removeloading();
            if (status == 'success') {
                if (d && typeof d.Ack !== 'undefined' && d.Ack == 'Success') {
                    // 重新加载列表
                    loadlist();
                    // alert('禁用成功！',1);
                    parent.$('#SitesList>ul>li[sid=-1]').click();
                } else {
                    if (d.Error == 'User authentication fails') {
                        hintShow('hint_w', lang.ajaxinfo.permission_denied);
                    } else if (d.Ack == 'Warning') {
                        hintShow('hint_w', lang.ajaxinfo.permission_denied);
                    } else {
                        alert(lang.shopset_biz.disable_err, 2);
                    }
                }
            } else {
                alert(lang.ajaxinfo.network_error, 2);
            }
        });
    })

    // 启用账号功能绑定
    $("#list_table").on("click", '.enable_btn', function() {
        loading();
        $.get("?r=api/SetShopStatus", {
            'sids': $(this).closest('tr').attr('shop_id'),
            action: 'enable'
        }, function(d, status) {
            // 删除loading
            removeloading();
            if (status == 'success') {
                if (d && typeof d.Ack !== 'undefined' && d.Ack == 'Success') {
                    // 重新加载列表
                    loadlist();
                    // alert('启用成功！',1);
                    parent.$('#SitesList>ul>li[sid=-1]').click(); // TODO
                } else if (d.Ack == 'Warning') {
                    hintShow('hint_w', lang.ajaxinfo.permission_denied);
                } else {
                    alert(lang.shopset_biz.enable_err, 2);
                }
            } else {
                alert(lang.ajaxinfo.network_error, 2);
            }
        });
    })

    // 权限设置按钮事件绑定
    $("#list_table").on("click", '.permission_setting', function() {
        $("#permission_set_window_btn").data('shop_id', $(this).closest('tr').attr('shop_id'));
        $("#permission_set_window_ckbs").empty();
        $.get('?r=api/GetUsersByShopid', {
            shopid: $(this).closest('tr').attr('shop_id')
        }, function(data, status) {
            if (status == 'success') {
                if (data.Ack == 'Success') {
                    var _html = '';
                    for (var x in data.Body.users) {
                        var _sel = '';
                        for (var y in data.Body.ref) {
                            if (data.Body.ref[y].user_id == data.Body.users[x].user_id) {
                                _sel = ' checked="checked"'
                            }
                        }
                        _html += '<label><input type="checkbox"' + _sel + ' name="" value="' + data.Body.users[x].user_id + '">' + data.Body.users[x].username + '</label>';
                    }
                    $("#permission_set_window_ckbs").append(_html);
                } else {
                    alert(lang.ajaxinfo.permission_denied, 3);
                    $("#permission_set_window").hide();
                    // alert('',2);
                }
            } else {
                alert(lang.ajaxinfo.network_error, 2);
            }
        });
        $("#permission_set_window").show();
    })

    // 确定按钮
    $("#permission_set_window_btn").on('click', function(e) {
        var _set = [];
        $("#permission_set_window_ckbs").find('input').each(function(index, element) {
            _set.push([$(this).val(), $(this)[0].checked]);
        });
        $.post('?r=api/SetShopUsers', {
            set: _set,
            shopid: $(this).data('shop_id')
        }, function(data, status) {
            if (data.Error == 'User authentication fails') {
                hintShow('hint_w', lang.ajaxinfo.permission_denied);
                return;
            }
            if (status == 'success') {
                if (data.Ack == 'Success') {
                    $("#permission_set_window").hide();
                    hintShow('hint_s', lang.shopset_biz.set_user_right);
                } else if (data.Ack == 'Warning') {
                    $("#permission_set_window").hide();
                    hintShow('hint_w', lang.ajaxinfo.permission_denied);
                } else {
                    alert(lang.ajaxinfo.internal_error, 2);
                }
            }
        });
    });

    // 关闭用户列表弹窗
    $("#permission_set_window_closebtn").click(function(e) {
        $("#permission_set_window").hide();
    });

    // 重新授权按钮 囧 ( ╯□╰ )
    $("#list_table").on("click", '.re_auth_btn', function() {
        // $("#nickname").val();
        $("#addshop").click();
    })

    // 批量删除按钮事件绑定
    $("#pldelete").click(function(e) {

        // 判断是否有选择一个
        _ha = true;
        $("input.ids").each(function(index, element) {
            if ($(element)[0].checked) {
                _ha = false
            }
        });
        if (_ha) {
            alert(lang.shopset_biz.no_shop, 3);
            return;
        }

        confirmFun(function() {
            var ids = [];
            var trs = [];
            // 获取选中的checkbox的值放进数组
            $("input.ids").each(function(index, element) {
                if ($(element)[0].checked) {
                    ids.push($(element).val());
                    trs.push($(element).closest('tr'));
                }
            });
            ids = ids.join(",");
            loading();
            $.get("?r=api/SetShopDel", {
                'sids': ids
            }, function(d, status) {
                removeloading();
                if (d.Error == 'User authentication fails') {
                    hintShow('hint_w', lang.ajaxinfo.permission_denied);
                } else {
                    if (status == 'success') {
                        if (d && typeof d.Ack !== 'undefined' && d.Ack == 'Success') {
                            for (var i in trs) {
                                trs[i].remove();
                            }
                            loadlist();
                            $("#checkall")[0].checked = false;
                            parent.$('#SitesList>ul>li[sid=-1]').click(); // TODO
                        } else if (d.Ack == 'Warning') {
                            hintShow('hint_w', lang.ajaxinfo.permission_denied);
                        } else {
                            alert(lang.shopset_biz.del_err, 2);
                        }
                    } else {
                        alert(lang.ajaxinfo.network_error, 2);
                    }
                }
            });
        }, function() {}, lang.shopset_biz.sure);
    });

    // 显示添加店铺窗口
    $("#addshop").click(function(e) {
        // 隐藏全部按钮
        $(".ggg2").hide();
        // 隐藏nickname_box
        $("#nickname_box").hide();
        // 显示窗口
        $("#add_window").show();
        // 显示下一步按钮
        $("#add_next_btn").show();
        // 自动触发下一步按钮的点击事件，后台自动获取session
        $("#add_next_btn").trigger('click');
    });

    // 绑定关闭按钮事件
    $("#addnewshop_closebtn").click(function(e) {
        // 隐藏窗口
        $("#add_window").hide();
        // 重置站点选择框到第一个
        // $("#siteId").val($("#siteId option").eq(0).val())
        // 清空账号输入框内容
        $("#shopname").val('');
    });

    // 绑定下一步按钮事件
    $("#add_next_btn").click(function(e) {
        // 隐藏所有按钮
        $(".ggg2").hide();
        $("#info_box").show();
        // 显示下一步按钮(伪),为屏蔽细节，提高用户体验
        $("#add_info").html('<span class="subBtn ggg2" id="add_wait_btn" style="display: inline;">' + lang.shopset_biz.connect_server + '</span>').show();
        // 绑定(伪)下一步按钮事件
        $("#add_wait_btn").click(function(e) {
            // 提示用户等待
            $("#add_info").html('<span class="subBtn ggg2" id="add_wait_btn" style="display: inline;">' + lang.shopset_biz.connect_server + '</span>');
        });
        // 调用接口获取sessionID
        $.get("?r=api/GetEbaySessionID", function(d, status) {
            if (status == 'success') {
                // 判断是否成功获取sessionID
                if (d && typeof d.Ack !== 'undefined' && d.Ack == 'Success') {
                    // 隐藏全部按钮
                    $(".ggg2").hide();
                    // 填写跳转按钮
                    $("#add_login_btn a").attr('href', "https://signin.ebay.com/ws/eBayISAPI.dll?SignIn&RuName=" + d.data.RuName + "&SessID=" + d.data.SessionID);
                    // 显示跳转按钮
                    $("#add_login_btn").show();
                    // 绑定跳转按钮点击事件
                    $("#add_login_btn a").click(function(e) {
                        // 隐藏全部按钮
                        $(".ggg2").hide();
                        // 隐藏info_box
                        $("#info_box").hide();
                        // 显示nickname_box
                        $("#nickname_box").show();
                        // 清除事件
                        $("#add_ack_btn").replaceWith($("#add_ack_btn")[0].outerHTML);
                        // 绑定确认按钮事件
                        $("#add_ack_btn").click(function(e) {
                            /*if ($("#shopname").val() == '') {
                                alert('账号不能为空！',3);
                                return false;
                            }*/
                            $("#nickname").val($("#nickname").val().toString().replace(/\s+/g, ''));
                            if ($("#nickname").val() == '') {
                                alert(lang.shopset_biz.nick_name_no_null, 3);
                                return false;
                            }
                            // 构造saveTokenBySessionID需要的数据
                            var D = {
                                'sessionid': d.data.SessionID,
                                'account': $("#shopname").val(),
                                'nickname': $("#nickname").val(),
                                // 'siteid' : $("#siteId").val(),
                                '_rnd': loading()
                            };
                            // 调用API保存刚才获取的token
                            $.get("?r=api/saveTokenBySessionID", D, function(d, status) {
                                // 隐藏loading
                                removeloading();
                                // 判断接口是否调用成功
                                if (status = 'success') {
                                    if (typeof d.Ack !== 'undefined' && d.Ack == 'Success') {
                                        // 获取sessionID并，保存成功
                                        // alert('授权成功',1);
                                        // 隐藏窗口
                                        $("#add_window").hide();
                                        // 重置站点选择列表选择到第一个 @todo 是否有必要？
                                        // $("#siteId").val($("#siteId option").eq(0).val())
                                        // 将账号输入框清空
                                        $("#shopname").val('');
                                        // 重新加载账号列表
                                        // loadlist();
                                        parent.$('#SitesList>ul>li[sid=-1]').click();
                                    } else {
                                        // $("#add_window").fadeOut();
                                        // 服务器获取session ID失败 或 保存错误
                                        alert(lang.shopset_biz.authorized_err + '<br/><br/>' + d.error.error_message, 2);
                                    }
                                } else {
                                    // 接口调用失败
                                    $("#add_window").fadeOut();
                                    alert(lang.ajaxinfo.network_error + '\n:' + d.error.error_message, 2);
                                }
                            });
                        });
                        // 隐藏确认按钮
                        $("#add_ack_btn").show();
                    });
                } else if (d.Ack == 'Warning') {
                    // 获取sessionID失败，隐藏窗口
                    $("#add_window").fadeOut();
                    hintShow('hint_w', lang.ajaxinfo.permission_denied);
                } else {
                    // 获取sessionID失败，隐藏窗口
                    $("#add_window").fadeOut();
                    if (d.Error == 'User authentication fails') {
                        hintShow('hint_w', lang.ajaxinfo.permission_denied);
                    } else {
                        alert(lang.shopset_biz.connect_ebay_err, 2);
                    }
                }
            } else {
                // 网络错误，调用接口失败
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
});