"use strict";
/**
 * @desc 1.获取用户列表数据
 * 2.获取用户明细
 * 3.添加、编辑、删除用户
 * 4、验证用户信息
 * @author heguangquan
 * @date 2015-03-05
 */
$(document).ready(function(e) {
    inputFocusFun($('.dfVal'), lang.usermanage_biz.search_dfvalue); //调用搜索表单默认内容方法--linpeiyan
    //全局信息变量
    var global_info = {
        'page': undefined, // 分页页码
        'pageSize': undefined, // 分页大小
        'searchCon': undefined //查询SKU
    };

    var inputUserName = $("#userDetail input[name=loginname]");
    var inputPassWord = $("#userDetail input[name=password]");
    var inputName = $("#userDetail input[name=name]");
    var inputPhone = $("#userDetail input[name=telephone]");
    var inputEmail = $("#userDetail input[name=email]");

    UserList();
    /**
     * @desc 用户列表
     * @param pageInfo 分页
     * @author heguangquan,YangLong
     * @date 2015-03-02
     */
    function UserList(pageInfo) {
        if (pageInfo != undefined) {
            global_info.page = pageInfo.page;
            global_info.pageSize = pageInfo.pageSize;
        }
        var userObj = $("#userlist");
        $.ajax({
            url: "?r=api/GetUserList",
            data: {
                'page': global_info.page,
                'pageSize': global_info.pageSize,
                'searchCon': global_info.searchCon,
                '_rnd': loading()
            },
            success: function(result, state) {
                removeloading();
                var template = "";
                userObj.find("tr").remove();
                //绑定用户列表数据，判断网络是否出错
                if (state === 'success' && result.Ack === 'Success') {
                    var list = result.Body.list;
                    var pageInfo = result.Body.page;
                    var userCount = result.Body.count;
                    var serial = (pageInfo.page - 1) * pageInfo.pageSize;
                    if (list && list.length > 0) {
                        $.each(list, function(index, it) {
                            template += '<tr><td><input class="boxid" type="checkbox" name=""  data-id="' + it.user_id + '" /></td>' +
                                '<td>' + (serial + index + 1) + '</td><td>' + it.username + '</td><td>' + it.realname + '</td><td>' + (it.telephone ===
                                    null ? "" : it.telephone) + '</td>' + '<td><a class="fontBtn permission_setting_u">' + lang.usermanage_biz
                                .user_list.seting + '</a></td>' + '<td><i class="icon-edit iconBtnH editUser" title="' + lang.usermanage_biz
                                .user_list.edit + '"></i></td>' + '<td><i class="icon-trash iconBtnH singleDelete" title="' + lang.usermanage_biz
                                .user_list.cancel + '"></i></td></tr>';
                        });
                        refreshPaginationNavBar(pageInfo.page, pageInfo.pageSize, userCount, UserList);
                    } else {
                        template = '<tr><td colspan="8" align="center">' + lang.usermanage_biz.user_list.no_data + '</td></tr>';
                    }
                } else {
                    template = '<tr><td colspan="8" align="center">' + lang.usermanage_biz.internet_err + '</td></tr>';
                }
                userObj.append(template);
                //复选框全选
                allCheckBox($("#userchoose"), $(".boxid"));
                //关闭编辑与添加用户的窗口
                $("#cancelUser").on('click', function() {
                    $("#userDetail").hide();
                });
                //绑定单个删除用户事件
                $(".singleDelete").on('click', function() {
                    var userId = $(this).parents('tr').find('input').attr('data-id');
                    confirmFun(function() {
                        $.get('?r=api/deleteUser', {
                            'uid': userId,
                            '_rnd': loading()
                        }, function(result, state) {
                            removeloading();
                            if (result.Error == 'User authentication fails') {
                                hintShow('hint_w', lang.usermanage_biz.no_permission);
                            } else if (result.Error == "Can't delete the current user") {
                                hintShow('hint_w', lang.usermanage_biz.delete_user.del_yourself);
                            } else {
                                if (state === 'success') {
                                    if (result.Ack === 'Success') {
                                        hintShow('hint_s', lang.usermanage_biz.delete_user.del_suc);
                                        UserList(global_info);
                                    } else {
                                        hintShow('hint_f', lang.usermanage_biz.delete_user.del_err);
                                    }
                                } else {
                                    hintShow('hint_f', lang.usermanage_biz.internet_err);
                                }
                            }
                        });
                    }, function() {}, lang.usermanage_biz.delete_user.sure);
                });

                //绑定用户明细数据
                $(".editUser").on('click', function() {
                    var userId = $(this).parents('tr').find('input').attr('data-id');
                    $(".TCBody").attr('user-id', userId);
                    $(".TCBody").attr('data-type', 'edit');
                    //获取数据
                    $.get('?r=api/GetUserDetail', {
                        'userid': userId,
                        '_rnd': loading()
                    }, function(result, state) {
                        removeloading();
                        if (result.Error == 'User authentication fails') {
                            hintShow('hint_w', lang.usermanage_biz.no_permission);
                        } else {
                            //判断网络是否出错
                            if (state === 'success' && result.Ack === 'Success') {
                                var userDetail = result.Body.content;
                                $("#userDetail_title").html(lang.usermanage_biz.edit_user.edit);
                                $("#userDetail .TCBody input[name=loginname]").val(userDetail.username);
                                $("#userDetail .TCBody input[name=loginname]").attr('readonly', 'true');
                                $("#userDetail .TCBody input[name=password]").val('');
                                $("#userDetail .TCBody input[name=password]")[0].placeholder = lang.usermanage_biz.edit_user.pass_dfvalue;
                                $("#userDetail .TCBody input[name=name]").val(userDetail.realname);
                                $("#userDetail .TCBody input[name=telephone]").val(userDetail.telephone);
                                $("#userDetail .TCBody input[name=email]").val(userDetail.email);
                                $("#userDetail").slideDown(200);
                            } else {
                                hintShow('hint_f', lang.usermanage_biz.internet_err);
                            }
                        }
                    });

                });

                if (result.Body.sellerinfo.seller_id !== result.Body.sellerinfo.user_id) {
                    $("#addUser").hide();
                };

            }
        });
    }

    $("#userlist").on('click', '.permission_setting_u', function(e) {
        $("#set_user_shop_window_btn").data('userid', $(this).closest('tr').find('input').first().data('id'));
        $("#set_user_shop_window_ckbs").empty();
        $.get('?r=api/GetShopByUserid', {
            'userid': $(this).closest('tr').find('input').first().data('id')
        }, function(data, status) {
            if (status = 'success') {
                if (data.Ack == 'Success') {
                    var _html = '';
                    for (var x in data.Body.shops) {
                        var _sel = '';
                        for (var y in data.Body.ref) {
                            if (data.Body.ref[y].shop_id == data.Body.shops[x].shop_id) {
                                _sel = ' checked';
                            }
                        }
                        _html += '<label><input type="checkbox" value="' + data.Body.shops[x].shop_id + '"' + _sel + '>' + data.Body.shops[x].nick_name +
                            '</label>';
                    }
                    $("#set_user_shop_window_ckbs").append(_html);
                    if (data.Body.shops.length === 0) {
                        $("#set_user_shop_window_ckbs").append(lang.usermanage_biz.no_shop);
                    }
                } else if (data.Ack == 'Warning') {
                    $("#set_user_shop_window").hide();
                    hintShow('hint_w', lang.usermanage_biz.no_permission);
                } else {
                    hintShow('hint_w', lang.usermanage_biz.internel_err);
                }
            } else {
                hintShow('hint_f', lang.usermanage_biz.internet_err);
            }
        });
        $("#set_user_shop_window").show();
    });

    $("#set_user_shop_window_btn").on('click', function(e) {
        var _set = [];
        $("#set_user_shop_window_ckbs").find('input').each(function(index, element) {
            _set.push([$(this).val(), $(this)[0].checked]);
        });
        $.post('?r=api/SetUserShops', {
            set: _set,
            userid: $("#set_user_shop_window_btn").data('userid')
        }, function(data, status) {
            if (data.Error == 'User authentication fails') {
                hintShow('hint_w', lang.usermanage_biz.no_permission);
                return;
            }
            if (status == 'success') {
                if (data.Ack == 'Success') {
                    $("#set_user_shop_window").hide();
                    hintShow('hint_s', lang.usermanage_biz.set_shop_right);
                } else if (data.Ack == 'Warning') {
                    $("#set_user_shop_window").hide();
                    hintShow('hint_w', lang.usermanage_biz.internet_err);
                } else {
                    alert(lang.usermanage_biz.internel_err, 2);
                }
            }
        });
    })

    $("#set_user_shop_window_closebtn").on('click', function(e) {
        $("#set_user_shop_window").hide();
    })

    //绑定批量删除用户事件
    $("#allDelete").on('click', function() {
        var strUserId = getAllId($('#userlist tr td input[type=checkbox]:checked'));
        if (strUserId.length === 0) {
            hintShow('hint_w', lang.usermanage_biz.all_checked);
        } else {
            confirmFun(function() {
                $.get('?r=api/deleteUser', {
                    'uid': strUserId,
                    '_rnd': loading()
                }, function(result, state) {
                    removeloading();
                    if (result.Error == 'User authentication fails') {
                        hintShow('hint_w', lang.usermanage_biz.no_permission);
                    } else if (result.Error == "Can't delete the current user") {
                        hintShow('hint_w', lang.usermanage_biz.delete_user.del_youself);
                    } else {
                        if (state === 'success') {
                            if (result.Ack === 'Success') {
                                hintShow('hint_s', lang.usermanage_biz.delete_user.del_suc);
                                $("#userchoose").attr('checked', false);
                                UserList(global_info);
                            } else {
                                hintShow('hint_f', lang.usermanage_biz.delete_user.del_err);
                            }
                        } else {
                            hintShow('hint_f', lang.usermanage_biz.internet_err);
                        }
                    }
                });
            }, function() {}, lang.usermanage_biz.delete_user.sure);
        }
    });

    //绑定确定添加与修改用户信息事件
    $("#determine").on('click', function() {
        var userId = $(".TCBody").attr('user-id');
        var dataType = $(".TCBody").attr('data-type');
        var userName = inputUserName.val();
        var pass = inputPassWord.val();
        var realName = inputName.val();
        var phone = inputPhone.val();
        var email = inputEmail.val();
        $("#userDetail input[type=text],#userDetail input[type=password]").next('span').css('marginLeft', '5px').html('');

        //用户名验证
        if (userName === '') {
            inputUserName.next('span').addClass('redFont').html(lang.usermanage_biz.determine.verify_name);
            return;
        }
        //密码验证
        if (pass === '' && dataType === 'add') {
            inputPassWord.next('span').addClass('redFont').html(lang.usermanage_biz.determine.verify_pass);
            return;
        }
        if (pass !== '' && (pass.length < 6 || pass.length > 20)) {
            inputPassWord.next('span').addClass('redFont').html(lang.usermanage_biz.determine.pass_tip);
            return;
        }
        //姓名验证
        if (realName === '') {
            inputName.next('span').addClass('redFont').html(lang.usermanage_biz.determine.verify_realName);
            return;
        }

        //电话号码与邮箱验证正则
        var phoneFilter = /^(\(\d{3,8}\)|\d{3,8}-)?\d{3,15}$/;
        var emailFilter = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
        //电话号码验证
        if (!phoneFilter.test(phone) && phone !== '') {
            inputPhone.next('span').addClass('redFont').html(lang.usermanage_biz.determine.verify_phone);
            return;
        }
        //邮箱格式验证
        if (!emailFilter.test(email) && email !== '') {
            inputEmail.next('span').addClass('redFont').html(lang.usermanage_biz.determine.verify_email);
            return;
        }

        //编辑用户
        if (dataType === 'edit') {
            $.get('?r=api/EditUser', {
                'userid': userId,
                'username': userName,
                'password': pass,
                'realname': realName,
                'telephone': phone,
                'email': email,
                '_rnd': loading()
            }, function(result, state) {
                removeloading();
                //判断网络是否出错
                if (state === 'success') {
                    if (result.Ack === 'Success') {
                        $("#userDetail").slideUp(200);
                        hintShow('hint_s', lang.usermanage_biz.determine.verify_edit.edit_suc);
                        UserList(global_info);
                    } else {
                        hintShow('hint_f', lang.usermanage_biz.determine.verify_edit.edit_err);
                    }
                } else {
                    hintShow('hint_f', lang.usermanage_biz.internet_err);
                }
            });
            //添加用户
        } else if (dataType === 'add') {
            $.get('?r=api/AddUser', {
                'realname': realName,
                'username': userName,
                'password': pass,
                'telephone': phone,
                'email': email,
                '_rnd': loading()
            }, function(result, state) {
                removeloading();
                //判断网络是否出错
                if (state === 'success') {
                    if (result.Ack === 'Success') {
                        $("#userDetail").slideUp(200);
                        hintShow('hint_s', lang.usermanage_biz.determine.verify_add.add_suc);
                        setTimeout(function() {
                            window.location.reload();
                        }, 400);
                    } else {
                        inputUserName.next('span').addClass('redFont');
                        // TODO 写死的提示
                        inputUserName.next('span').html(lang.usermanage_biz.determine.verify_add.add_err);
                    }
                } else {
                    hintShow('hint_f', lang.usermanage_biz.internet_err);
                }
            });
        }
    });

    //绑定添加用户弹窗事件
    $("#addUser").on('click', function() {
        $("#userDetail_title").html(lang.usermanage_biz.determine.add_user);
        $("#userDetail .TCBody").attr('data-type', 'add');
        $("#userDetail .TCBody input[name=loginname]").val('');
        $("#userDetail .TCBody input[name=password]").val('');
        $("#userDetail .TCBody input[name=password]")[0].placeholder = '';
        $("#userDetail .TCBody input[name=name]").val('');
        $("#userDetail .TCBody input[name=telephone]").val('');
        $("#userDetail .TCBody input[name=email]").val('');
        $("#userDetail .TCBody input[name=loginname]").removeAttr("readonly");
        $("#userDetail").slideDown(200);
    });

    //绑定搜索功能事件
    $("#search").on('click', function() {
        global_info.searchCon = $("#searchName").val();
        UserList();
    });

    //绑定关闭事件，关闭是去掉所有提示
    $("#cancelUser").on('click', function() {
        inputUserName.next('span').html('');
        inputPassWord.next('span').html('');
        inputName.next('span').html('');
        inputPhone.next('span').html('');
        inputEmail.next('span').html('');
    });
})