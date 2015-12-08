"use strict";
/**
 * @desc sku 分配管理页面
 * @author YangLong
 * @date 2015-11-21
 */
var pageInfo = {
    'page': 1,
    'pageSize': 20
};
$(document).ready(function(e) {
    loadlist();

    $("#add_sku_match_btn").on('click', '', function() {
        $("#ipt_sku").val('');

        // switch
        $("#add_sku_match_wd_tt").text(lang.skumatch_biz.add);
        $("#add_sku_match_submit").data('switch', 'add');

        $("#add_sku_match_wd").show();
    });

    $("#add_sku_match_close_btn").on('click', '', function() {
        $("#add_sku_match_wd").hide();
    });

    $("#add_sku_match_cancel").on('click', '', function() {
        $("#add_sku_match_wd").hide();
    });

    $("#import_excel_btn").on('click', '', function() {
        $("#filelist").empty();
        $("#import_excel_wd").show();
    });

    $("#import_excel_close_btn").on('click', '', function() {
        $("#import_excel_wd").hide();
    });

    // http://192.168.188.128/ims/?r=api/GetSellerUsers
    $.get('?r=api/GetSellerUsers', function(data, status) {
        if (status === 'success' && data.Ack === 'Success') {
            for (var i = 0; i < data.Body.length; i++) {
                var _ = data.Body[i];
                var _html = '';
                _html += '<option value="' + _.user_id + '">' + _.realname + '(' + _.username + ')' + '</option>';
                $("#seller_users_list").append(_html);
                $("#sku_default_user").append(_html);
            };
            $.get('?r=api/GetSkuDefaultUser', function(data, status) {
                if (status === 'success' && data.Ack === 'Success') {
                    $("#sku_default_user").find('option[value="' + data.Body + '"]')[0].selected = true;
                } else {
                    // hintShow('hint_f', lang.ajaxinfo.network_error_s);
                }
            });
        } else {
            hintShow('hint_f', lang.ajaxinfo.network_error_s);
        }
    });

    $("#sku_default_user").on('change', '', function() {
        var userid = $(this).find(':selected').val();
        if (userid == '') {
            return;
        };
        loading();
        $.get('?r=api/SetSkuDefaultUser', {
            'userid': userid
        }, function(data, status) {
            removeloading();
            if (status === 'success' && data.Ack === 'Success') {
                if (data.Body > 0) {
                    hintShow('hint_s', lang.skumatch_biz.edit_success);
                    $("#add_sku_match_wd").hide();
                    loadlist();
                } else {
                    hintShow('hint_w', lang.skumatch_biz.nochange);
                }
            } else {
                hintShow('hint_f', lang.ajaxinfo.network_error_s);
            }
        });
    });

    // AddSkuMatchInfo
    $("#add_sku_match_submit").on('click', '', function() {

        if ($("#add_sku_match_submit").data('switch') == 'edit') {

            if ($("#ipt_sku").val() == '') {
                hintShow('hint_f', lang.skumatch_biz.sku_cannot_empty);
                return;
            };

            if ($("#seller_users_list").find(':checked').val() == '') {
                hintShow('hint_f', lang.skumatch_biz.username_cannot_empty);
                return;
            };

            $.get('?r=api/EditSkuMatchInfo', {
                'sku': $("#ipt_sku").val(),
                'userid': $("#seller_users_list").find(':checked').val(),
                'sku0': $("#add_sku_match_submit").data('s_sku'),
                'userid0': $("#add_sku_match_submit").data('s_user_id')
            }, function(data, status) {
                if (status === 'success' && data.Ack === 'Success') {
                    if (data.Body > 0) {
                        hintShow('hint_s', lang.skumatch_biz.edit_success);
                        $("#add_sku_match_wd").hide();
                        loadlist();
                    } else {
                        hintShow('hint_w', lang.skumatch_biz.nochange);
                    }
                } else if (data.Error == 23000) {
                    hintShow('hint_w', lang.skumatch_biz.record_exist);
                } else {
                    hintShow('hint_f', lang.ajaxinfo.network_error_s);
                }
            });

        } else {

            if ($("#ipt_sku").val() == '') {
                hintShow('hint_f', lang.skumatch_biz.sku_cannot_empty);
                return;
            };

            if ($("#seller_users_list").find(':checked').val() == '') {
                hintShow('hint_f', lang.skumatch_biz.username_cannot_empty);
                return;
            };

            $.get('?r=api/AddSkuMatchInfo', {
                'sku': $("#ipt_sku").val(),
                'userid': $("#seller_users_list").find(':checked').val()
            }, function(data, status) {
                if (status === 'success' && data.Ack === 'Success') {
                    if (data.Body > 0) {
                        hintShow('hint_s', lang.skumatch_biz.add_success);
                        $("#add_sku_match_wd").hide();
                        $("#keywords").val('');
                        pageInfo.keywords = '';
                        loadlist();
                    } else {
                        hintShow('hint_w', lang.skumatch_biz.record_exist);
                    }
                } else {
                    hintShow('hint_f', lang.ajaxinfo.network_error_s);
                }
            });

        }

    });

    // console.log($(this).closest('tr').data('sd'));
    // DelSkuMatchInfo
    $("#skulist").on('click', '.sku_list_del_btn', function() {
        var _this = $(this);
        confirmFun(function() {
            $.get('?r=api/DelSkuMatchInfo', {
                'sku': _this.closest('tr').data('sd').SKU,
                'userid': _this.closest('tr').data('sd').user_id
            }, function(data, status) {
                if (status === 'success' && data.Ack === 'Success') {
                    if (data.Body > 0) {
                        hintShow('hint_s', lang.skumatch_biz.delete_success);
                    } else {
                        hintShow('hint_w', lang.skumatch_biz.record_no_found);
                    }
                    loadlist();
                } else {
                    hintShow('hint_f', lang.ajaxinfo.network_error_s);
                }
            });
        }, function() {}, lang.skumatch_biz.sure_delete);
    });

    // edit
    $("#skulist").on('click', '.sku_list_edit_btn', function() {
        $("#ipt_sku").val($(this).closest('tr').data('sd').SKU);
        $("#seller_users_list").find('[value="' + $(this).closest('tr').data('sd').user_id + '"]')[0].selected = true;

        $("#add_sku_match_submit").data('s_sku', $(this).closest('tr').data('sd').SKU);
        $("#add_sku_match_submit").data('s_user_id', $(this).closest('tr').data('sd').user_id);

        // switch
        $("#add_sku_match_wd_tt").text(lang.skumatch_biz.edit);
        $("#add_sku_match_submit").data('switch', 'edit');

        $("#add_sku_match_wd").show();
    });

    // search
    $("#search_btn").on('click', '', function() {
        var keywords = $("#keywords").val();

        if (keywords == '') {
            hintShow('hint_w', lang.skumatch_biz.search_keyword_cannot_empty);
            return;
        }

        pageInfo.keywords = keywords;
        loadlist();
    });

    // 批量删除
    $("#pldl_btn").on('click', '', function() {
        confirmFun(function() {
            var _po = [];
            $("#skulist").find('.ids:checked').each(function(index, el) {
                var _ = $(this).closest('tr').data('sd');
                _po.push({
                    'user_id': _.user_id,
                    'SKU': _.SKU
                });
            });
            _po = JSON.stringify(_po);
            $.post('?r=api/PlDelSkuMatchInfo', {
                'data': _po
            }, function(data, status, xhr) {
                if (status === 'success') {
                    if (data.Ack === 'Success') {
                        hintShow('hint_s', lang.skumatch_biz.delete_success);
                    } else {
                        hintShow('hint_f', lang.skumatch_biz.delete_failure);
                    }
                    loadlist();
                } else {
                    hintShow('hint_f', lang.ajaxinfo.network_error_s);
                }
            });
        }, function() {}, lang.skumatch_biz.sure_delete);
    });

    $("#submit_excel").on('click', '', function() {
        var filepath = $("#filelist").find('>div').data('src');
        if (!filepath) {
            hintShow('hint_w', lang.skumatch_biz.uploadfilefirst);
            return;
        }

        loading();
        // ParseSkuExcel
        $.get('?r=api/ParseSkuExcel', {
            'filepath': filepath
        }, function(data, status) {
            removeloading();
            if (status === 'success') {
                if (data.Ack === 'Success') {
                    var info = '';
                    info += '解析成功！<br/><br/>' + '成功统计：<br/>';
                    info += '新记录数：' + data.Body.success + '<br/>';
                    info += '已存在记录数：' + data.Body.success_empty + '<br/>';
                    info += '失败统计：<br/>' + '姓名为空：' + data.Body.error_name_empty + '<br/>';
                    info += '姓名未找到：' + data.Body.error_name_nofound + '<br/>';
                    info += 'SKU为空：' + data.Body.error_sku;
                    hintShow('hint_s', info);
                    $("#import_excel_wd").hide();
                } else {
                    hintShow('hint_f', lang.skumatch_biz.parse_failed);
                }
                loadlist();
            } else {
                hintShow('hint_f', lang.ajaxinfo.network_error_s);
            }
        });
    });

    // Excel 导入
    $("#drop-area-div").dmUploader({
        url: '?r=api/UploadFile',
        dataType: 'json',
        allowedTypes: 'application/vnd.*',
        maxFileSize: 1024 * 1024 * 10,
        fileName: 'file',
        extraData: {},
        onInit: function() {},
        onFallbackMode: function(message) {},
        onNewFile: function(id, file) {},
        onBeforeUpload: function(id) {},
        onComplete: function() {},
        onUploadProgress: function(id, percent) {
            // console.log(id, percent);
        },
        onUploadSuccess: function(id, data) {
            if (data.Ack == 'Success') {
                $("#filelist").html('<div></div>');
                $("#filelist").find('>div').data('src', data.Body.filepath).text(data.Body.filename);
            } else {
                hintShow('hint_f', lang.skumatch_biz.file_upload_failed);
                return false;
            }
        },
        onUploadError: function(id, message) {
            hintShow('hint_f', lang.skumatch_biz.file_upload_failed);
        },
        onFileTypeError: function(file) {
            hintShow('hint_f', lang.skumatch_biz.file_type_error);
        },
        onFileSizeError: function(file) {
            hintShow('hint_f', lang.skumatch_biz.file_size_error);
        },
        onFileExtError: function(file) {
            hintShow('hint_f', lang.skumatch_biz.file_ext_err);
        },
        onFilesMaxError: function(file) {
            hintShow('hint_f', lang.skumatch_biz.file_count_err);
        }
    });

    $('#ipt_sku').keypress(function(e) {
        if (e.charCode === 13) {
            $("#add_sku_match_submit").click();
        }
    });

    $('#keywords').keypress(function(e) {
        if (e.charCode === 13) {
            $("#search_btn").click();
        }
    });

});

function loadlist(C_page) {

    // 分页相关处理
    if (C_page) {
        pageInfo.page = C_page.page;
        pageInfo.pageSize = C_page.pageSize;
    }

    // http://192.168.188.128/ims/?r=api/GetSkuList
    loading();
    $.get('?r=api/GetSkuList', pageInfo, function(data, status) {
        $("#skulist").empty();
        removeloading();
        if (status === 'success' && data.Ack === 'Success') {
            for (var i = 0; i < data.Body.list.length; i++) {
                var _ = data.Body.list[i];
                var _html = '';
                _html += '<tr>';
                _html += '<td><input type="checkbox" class="ids" /></td>';
                _html += '<td></td>';
                _html += '<td>' + _.realname + '(' + _.username + ')</td>';
                _html += '<td><i class="icon-edit iconBtnS sku_list_edit_btn"></i></td>';
                _html += '<td><i class="icon-trash iconBtnS sku_list_del_btn"></i></td>';
                _html += '</tr>';
                $("#skulist").append(_html);
                $("#skulist").find('tr:last').find('td:eq(1)').text(_.SKU);
                $("#skulist").find('tr:last').data('sd', _);
            };
            // 分页(按钮更新)
            refreshPaginationNavBar(pageInfo.page, pageInfo.pageSize, data.Body.count, loadlist);
            if (pageInfo.page > 1 && data.Body.list.length == 0) {
                pageInfo.page--;
                loadlist();
            }
        } else {
            hintShow('hint_f', lang.ajaxinfo.network_error_s);
        }
    });
}