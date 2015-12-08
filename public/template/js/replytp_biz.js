"use strict";
/**
 * @desc 1.模板页列表加载\编辑\删除\添加\批量删除\分页等
 *       2.分类tree添加\删除\编辑\拖动等
 * @author YangLong
 * @date 2015-02-26
 */
$(document).ready(function(e) {

    // 定义父ID, 筛选类型, 分页大小, 当前分页
    var L_pid, L_type, L_pageSize, L_page;
    var ManageFalg = false;

    /**
     * @desc 获取列表并绑定
     * @param C_page分页参数
     * @author YangLong
     * @date 2015-02-26
     */
    function loadlist(C_page) {

        // 分页相关处理
        if (C_page) {
            L_page = C_page.page;
            L_pageSize = C_page.pageSize;
        }

        $("#list_table tbody").empty(); // 清空tbody
        var D = {
            'pid': L_pid,
            'type': L_type,
            'pageSize': L_pageSize,
            'page': L_page,
            '_rnd': loading()
        };
        // 获取模板列表
        $.get("?r=api/GetTpList", D, function(data, status) {
            // 删除loading
            removeloading();
            var pageInfo = data.Body.page,
                T = data.Body.list;
            // 判断是否有数据
            if (typeof pageInfo.count !== 'undefined' && pageInfo.count == 0) {
                $('<tr><td colspan="6" align="center">' + lang.replytp_biz.no_data + '</td></tr>').appendTo("#list_table tbody");
                $("#paginationNavBar").empty();
                return;
            }
            // 遍历绑定行
            for (var i in T) {
                $('<tr><td><input type="checkbox" value="' +
                    T[i].tp_list_id + '" class="ids" /></td><td>' +
                    (Number(i) + 1 + (pageInfo.page - 1) * pageInfo.pageSize) + '</td><td>' +
                    htmlEntities(T[i].classname) + '</td><td><span>' +
                    htmlEntities(T[i].title) + '</span></td><td><i class="icon-edit iconBtnS" tp_list_id="' +
                    T[i].tp_list_id + '" title="' + lang.replytp_biz.edit + '"></i></td><td><i class="icon-trash iconBtnS" tp_list_id="' +
                    T[i].tp_list_id + '" title="' + lang.replytp_biz.cancel + '"></i></td></tr>').appendTo("#list_table tbody");
            }
            // 删除按钮事件绑定
            $("#list_table tbody i.icon-trash").click(function(e) {
                var _self = $(this);
                // if(confirm('你确定要删除吗？')){}
                confirmFun(function() {
                    var D = {
                        'tid': _self.attr('tp_list_id'),
                        '_rnd': loading()
                    };
                    var tr = _self.closest('tr');
                    $.get("?r=api/DeteleTpList", D, function(data, status) {
                        // 删除loading
                        removeloading();
                        if (status == 'success') {
                            if (data.state == 1) {
                                alert(lang.replytp_biz.del_suc, 1);
                                tr.remove();
                                // 重载列表
                                loadlist();
                                // 取消选择
                                $("#checkall")[0].checked = false;
                            } else {
                                alert(lang.replytp_biz.del_err, 2);
                            }
                        } else {
                            alert(lang.ajaxinfo.network_error, 2);
                        }
                    });
                }, function() {}, lang.replytp_biz.sure);
            });
            // 编辑按钮事件绑定
            $("#list_table tbody i.icon-edit").click(function(e) {
                $('#add_window .TCtitle').text(lang.replytp_biz.edit_template);
                var D = {
                    'tid': $(this).attr('tp_list_id'),
                    '_rnd': loading()
                };
                // 重新获取数据，并绑定到编辑框，新鲜！！！囧rz
                $.get("?r=api/GetTp", D, function(data, status) {
                    // 删除loading
                    removeloading();
                    if (status == 'success') {
                        // 绑定新数据到编辑框
                        $("#add_window input[name='tp_list_id']").val(data.tp_list_id); // 主键写入
                        $("#add_window input[name='class_id']").val(data.class_id); // 分类ID写入
                        $("#add_window input[name='title']").val(data.title); // 标题写入
                        $("#add_window textarea[name='content']").val(data.content); // 内容写入
                        $("#add_window").show(); // 显示编辑框
                    } else {
                        $("#add_window").hide(); // 隐藏编辑框
                        alert(lang.replytp_biz.network_err, 2);
                    }
                });
            });
            // 分页按钮更新
            refreshPaginationNavBar(pageInfo.page, pageInfo.pageSize, pageInfo.count, loadlist);
        });
    }
    // 第一次列表加载
    var k = $.vakata.storage.get('jstree');
    try {
        k = JSON.parse(k);
        // 判断是否选择了一个分类
        if (k.state.core.selected.length == 0) {
            loadlist();
        }
    } catch (ex) {
        loadlist();
    }
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
            alert(lang.replytp_biz.no_template, 3);
            return;
        }

        // if(confirm('你确定要删除吗？',3)){}
        confirmFun(function() {
            // 遍历获取勾选的checkbox的值
            var ids = [];
            var trs = [];
            $("input.ids").each(function(index, element) {
                if ($(element)[0].checked) {
                    ids.push($(element).val());
                    trs.push($(element).closest('tr'));
                }
            });
            // 连接成字符串
            ids = ids.join(",");
            var D = {
                'tid': ids,
                '_rnd': loading()
            };
            $.get("?r=api/DeteleTpList", D, function(data, status) {
                // 删除loading
                removeloading();
                if (data) {
                    for (var i in trs) {
                        trs[i].remove();
                    }
                    // 翻到第一页
                    L_page = 1;
                    // 重新获取列表
                    loadlist();
                    // 取消选择
                    $("#checkall")[0].checked = false;
                } else {
                    alert(lang.replytp_biz.del_err, 2);
                }
            });
        }, function() {}, lang.replytp_biz.sure);
    });

    // 添加模板按钮事件
    $("#addTpBtn").click(function(e) {
        $('#add_window .TCtitle').text(lang.replytp_biz.add_template);
        $("#add_window input[name='tp_list_id']").val(0);
        $("#add_window input[name='title']").val('');
        $("#add_window textarea[name='content']").val('');
        $("#add_window").show();
        /**
         * @desc 获取模板分类列表，并绑定无限极联动组件
         * @author YangLong
         * @date 2015-02-26
         */
        $.get("?r=api/GetTpClassList&alternative=1", {
            '_rnd': loading()
        }, function(data, status) {
            // 删除loading
            removeloading();

            // 获取某个pid的所有子项目
            function getSubClass(pid) {
                var result = [];
                for (var i in data) {
                    if (data[i].parent == pid) {
                        result.push(data[i]);
                    }
                }
                return result;
            }

            // 获取某个id的所有pid
            function getPids(id) {
                var result = [];
                for (var i in data) {
                    if (data[i].id == id) {
                        result.push(data[i].id);
                        result = result.concat(getPids(data[i].parent));
                    }
                }
                return result;
            }

            // 填充方法
            function getSubClassHtml(pid, selector, r) {
                var obj = getSubClass(pid); // 获取数据
                if (obj.length == 0) {
                    return;
                }
                if (r === true) {
                    $(selector + ' select').remove();
                };
                $(selector).append('<select pid="' + pid + '"><option value="">' + lang.replytp_biz.choose + '</option></select> ');
                for (var i in obj) {
                    $(selector + " select[pid='" + pid + "']").append('<option value="' + obj[i].id + '">' + obj[i].text + '</option>');
                }
                $(selector + " select[pid='" + pid + "']").change(function(e) {
                    var npid = $(this).find('option:selected').val();
                    $(this).nextAll().remove();
                    $("#add_window input[name='class_id']").val(npid);
                    if (npid > 0) {
                        getSubClassHtml(npid, selector);
                    }
                    // 更新列表
                    L_pid = $(this).val();
                    L_page = 1; //重置到第一页
                    // loadlist();
                });
                // 弹出一个ID
                var selpids1 = selpids.pop();
                $(selector + " select[pid='" + pid + "']").val(selpids1).change();
            }

            if (status == 'success') {


                // 获取第一个选中项的所有父ID
                var selpids = [];
                try {
                    var k = $.vakata.storage.get('jstree');
                    k = JSON.parse(k);
                    selpids = getPids(k.state.core.selected[0]);
                } catch (ex) {}


                getSubClassHtml('#', '#xxLine2', true);

            } else {
                alert(lang.ajaxinfo.network_error, 2);
            }
        });
    });

    // 绑定关闭弹窗按钮事件
    $(".TCClose").click(function(e) {
        $(this).closest('.TCGB').hide();
    });

    // 绑定保存按钮事件
    $("#save_btn").click(function(e) {
        // 获取主键ID 获取分类ID
        var tid = $("#add_window input[name='tp_list_id']").val();
        var cid = $("#add_window input[name='class_id']").val();
        // 判断分类ID是否为空
        if (cid == undefined || cid == '') {
            alert(lang.replytp_biz.select_category, 3);
            $selectlast = $("#xxLine2").find('select').last();
            $selectlast.removeClass('redBorder').toggleClass('redBorder');
            setTimeout(function() {
                $selectlast.toggleClass('redBorder');
                setTimeout(function() {
                    $selectlast.toggleClass('redBorder');
                    setTimeout(function() {
                        $selectlast.toggleClass('redBorder');
                        setTimeout(function() {
                            $selectlast.toggleClass('redBorder');

                        }, 100);
                    }, 100);
                }, 100);
            }, 100);
            $selectlast.one('change', function() {
                $(this).removeClass('redBorder');
            });
            return;
        }
        // 获取标题和内容
        var title = $("#add_window input[name='title']").val();
        var content = $("#add_window textarea[name='content']").val();
        if (title == '') {
            alert(lang.replytp_biz.question_no_null, 3);
            return;
        }
        if (content == '') {
            alert(lang.replytp_biz.content_no_null, 3);
            return;
        }
        var D = {
            'tid': tid,
            'cid': cid,
            'title': title,
            'content': content,
            '_rnd': loading()
        };
        // ('cn'); // 新建classname
        // 保存新建或编辑的内容
        $.post("?r=api/TpEdit", D, function(data, status) {
            // 删除loading
            removeloading();
            if (status == 'success') {
                if (tid == 0) {
                    // 添加
                    if (data && typeof data.status !== undefined && data.status == 1) {
                        // 隐藏编辑框
                        $("#add_window").hide();
                        // 重载列表
                        loadlist();
                        alert(lang.replytp_biz.save_suc, 1);
                    } else {
                        alert(lang.replytp_biz.save_err, 2);
                    }
                } else {
                    // 编辑
                    if (data.status == 1) {
                        $("#add_window").hide();
                        var D = {
                            'tid': tid,
                            '_rnd': loading()
                        };
                        // 重新获取数据，并填充
                        $.get("?r=api/GetTp", D, function(data, status) {
                            // 删除loading
                            removeloading();
                            if (status == 'success') {
                                $("#list_table tbody i.icon-edit[tp_list_id=" + tid + "]").closest('tr').children('td').eq(2).text(data.classname);
                                $("#list_table tbody i.icon-edit[tp_list_id=" + tid + "]").closest('tr').children('td').eq(3).children('span').text(data.title);
                            } else {
                                alert(lang.ajaxinfo.network_error, 2);
                            }
                        });
                        // loadlist();
                        alert(lang.replytp_biz.save_suc, 1);
                    } else {
                        alert(lang.replytp_biz.save_err, 2);
                    }
                }
            } else {
                alert(lang.replytp_biz.save_err, 2);
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

    /**
     * @desc 左侧分类列表绑定
     * @author YangLong
     * @date 2015-02-27
     */
    $('#new_class_box')
        .jstree({
            'core': {
                'data': {
                    'url': '?r=api/GetTpClassList&alternative=1',
                    'data': function(node) {
                        return {
                            'pid': node.id
                        };
                    }
                },
                'check_callback': true,
                'themes': {
                    'responsive': false
                }
            },
            // 'plugins' : ['state','dnd','contextmenu','wholerow']
            'plugins': ['state', 'dnd', 'wholerow']
        })
        .on('delete_node.jstree', function(e, data) {
            // 绑定删除事件
            var D = {
                'cids': data.node.id,
                '_rnd': loading()
            };
            $.get("?r=api/DeleteTpClass", D, function(d, status) {
                removeloading();
                if (status == 'success') {
                    if (d && typeof d.Ack !== 'undefined') {
                        if (d.Ack == 'Success') {
                            alert(lang.replytp_biz.del_suc, 1);
                            loadlist();
                        } else {
                            alert(lang.replytp_biz.delete_error + d.error.error_message, 2);
                        }
                    } else {
                        alert(lang.replytp_biz.del_err, 2);
                    }
                } else {
                    alert(lang.replytp_biz.del_err, 2);
                }
            }).fail(function() {
                data.instance.refresh();
            });
        })
        .on('create_node.jstree', function(e, data) {
            // 绑定新建事件
            $.get('?r=api/TpClassEdit', {
                    'pid': data.node.parent,
                    'position': data.position,
                    'classname': data.node.text,
                    '_rnd': loading()
                }, function() {
                    removeloading();
                })
                .done(function(d) {
                    if (d.Error == 'User authentication fails') {
                        hintShow('hint_w', lang.ajaxinfo.permission_denied);
                        return;
                    }
                    // alert('添加成功！',1);
                    // 编辑分类名称弹窗
                    tpcedit(data);
                    data.instance.set_id(data.node, d.data.id);
                })
                .fail(function() {
                    alert(lang.replytp_biz.add_err, 2);
                    data.instance.refresh();
                });
        })
        .on('rename_node.jstree', function(e, data) {
            // 绑定编辑事件
            $.get('?r=api/TpClassEdit', {
                    'pid': data.node.parent,
                    'cid': data.node.id,
                    'classname': data.text,
                    '_rnd': loading()
                }, function() {
                    removeloading();
                })
                .done(function(d) {
                    if (d.Error == 'User authentication fails') {
                        hintShow('hint_w', lang.ajaxinfo.permission_denied);
                        return;
                    }
                    if (d.Ack == 'Success') {
                        alert(lang.replytp_biz.save_suc, 1);
                        data.instance.set_text(data.node, d.data.text);
                        $("#rename_close_btn").trigger("click");
                    } else {
                        alert(lang.replytp_biz.category_err, 3);
                    }
                })
                .fail(function() {
                    alert(lang.replytp_biz.rename_err, 2);
                    data.instance.refresh();
                });
        })
        .on('move_node.jstree', function(e, data) {
            $.get('?r=api/TpClassMove', {
                    'cid': data.node.id,
                    'pid': data.parent,
                    'position': data.position
                })
                .done(function(d) {
                    if (d.Error == 'User authentication fails') {
                        hintShow('hint_w', lang.ajaxinfo.permission_denied);
                        return;
                    }
                    data.instance.open_node(data.parent);
                    alert(lang.replytp_biz.save_suc, 1);
                })
                .fail(function() {
                    alert(lang.replytp_biz.rename_err, 2);
                    data.instance.refresh();
                });
        })
        /*.on('copy_node.jstree', function (e, data) {
            $.get('?operation=copy_node', { 'id' : data.original.id, 'parent' : data.parent, 'position' : data.position })
                .always(function () {
                    data.instance.refresh();
                });
        })*/
        .on('changed.jstree', function(e, data) {
            if (data && data.selected && data.selected.length) {
                // 获取选择的ID链接起来
                L_pid = data.selected.join();
                // 重置到第一页
                L_page = 1;
                // 当jstree发生改变后重载列表
                loadlist();
            }
        }).on('hover_node.jstree', function(e, data) {
            // 鼠标经过时显示图标
            // 判断是否开启编辑模式
            if (ManageFalg) {
                // 添加"编辑"、"删除"、"添加子类"图标
                $(this).find('ul>li[id=' + data.node.id + ']>a').append(
                    ' <i class="icon-edit iconBtnH cBtn" title="' + lang.replytp_biz.edit_category + '" style="display: inline-block;"></i>' +
                    ' <i class="icon-trash iconBtnH cBtn" title="' + lang.replytp_biz.del_category + '" style="display: inline-block;"></i>' +
                    '<i class="icon-plus iconBtnH cBtn" title="' + lang.replytp_biz.add_category + '" style="display: inline-block;"></i>');
                // 阻止以上3个按钮的冒泡
                $(this).find('ul>li[id=' + data.node.id + ']>a>i.cBtn').click(function(e) {
                    e.stopPropagation();
                });
                // 编辑图标事件绑定
                $(this).find('ul>li[id=' + data.node.id + ']>a>i.cBtn.icon-edit').click(function(e) {
                    // 编辑分类名称弹窗
                    tpcedit(data);
                });
                // 添加子类图标事件绑定
                $(this).find('ul>li[id=' + data.node.id + ']>a>i.cBtn.icon-plus').click(function(e) {
                    // 绑定添加事件
                    data.instance.create_node(data.node.id, {
                        'text': lang.replytp_biz.new_category
                    }, 'last', function() {
                        data.instance.open_node(data.node);
                    });
                });
                // 删除图标事件绑定
                $(this).find('ul>li[id=' + data.node.id + ']>a>i.cBtn.icon-trash').click(function(e) {
                    // 绑定删除事件
                    // if(confirm("")){}
                    confirmFun(function() {
                        data.instance.delete_node(data.node);
                    }, function() {}, lang.replytp_biz.confirm);
                });
            }
        }).on('dehover_node.jstree', function(e, data) {
            // 鼠标移除时 删除 3个图标
            $(this).find('ul>li>a>i.cBtn').remove();
        });

    // 分类名称编辑事件过程
    function tpcedit(data) {
        // 绑定修改事件
        $("#info_t001").text(lang.replytp_biz.rename);
        $("#info_l001").text(lang.replytp_biz.rename_as);
        $("#class_edit_box input[name='classname']").val(data.node.text);
        $("#class_edit_box").show();
        // 清除事件 @todo ...
        $("#save_btn_2").replaceWith($("#save_btn_2")[0].outerHTML);
        $("#save_btn_2").click(function(e) {
            // 修改显示的文本，触发set_text.jstree事件
            // set the text value of a node, trigger set_text.jstree event.
            var $input = $("#class_edit_box input[name='classname']");
            $input.val($input.val().toString().replace(/\s+/g, ''));
            if ($input.val() != '') {
                data.instance.rename_node(data.node, $input.val());
            } else {
                alert(lang.replytp_biz.category_err, 3);
            }
        });
    }

    // 管理按钮功能
    $("#classManage_btn").click(function(e) {
        if ($(this).attr('btnon') == 'off') {
            $(this).attr('btnon', 'on');
            $(this).text(lang.replytp_biz.exist_manage);
            ManageFalg = true;

            $("#other_btn_box1").hide();
            $("#other_btn_box2").show();
        } else {
            $(this).attr('btnon', 'off');
            $(this).text(lang.replytp_biz.manage);
            ManageFalg = false;

            $("#other_btn_box1").show();
            $("#other_btn_box2").hide();
        }
    });

    // 添加根分类事件绑定
    $("#other_btn_box2 a[d='add_root']").click(function(e) {
        $('#new_class_box').jstree('create_node', '#', {
            'text': lang.replytp_biz.new_category
        }, 'last', function() {});
    });

    // 全部展开
    $("#other_btn_box1 a[d='open_all']").click(function(e) {
        $('#new_class_box').jstree('open_all');
        $(this).hide();
        $("#other_btn_box1 a[d='close_all']").show();
    });

    // 全部关闭
    $("#other_btn_box1 a[d='close_all']").click(function(e) {
        $('#new_class_box').jstree('close_all');
        $(this).hide();
        $("#other_btn_box1 a[d='open_all']").show();
    });

    // 取消选择
    $("#other_btn_box1 a[d='unsel_all']").click(function(e) {
        // 取消全部选择
        $('#new_class_box').jstree('deselect_all');
        // pid置空
        L_pid = '';
        // 重置到第一页
        L_page = 1;
        // 当jstree发生改变后重载列表
        loadlist();
        // 取消选择
        $("#checkall")[0].checked = false;
    });

});