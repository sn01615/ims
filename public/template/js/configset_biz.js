"use strict";
/**
 * @desc 系统设置
 * @author liaojianwen
 * @date 2015-11-23
 */
$(document).ready(function(e) {
    (function() {
        $.get("?r=api/getConfig", function(data, status) {
            if (status === 'success' && data.Ack === 'Success') {
                var M = data.Body;
                for (var i in M) {
                    if (M[i].config_name === 'taskAssign') {
                        $('.dispatch input').each(function(index, element) {
                            if ($(element).val() === M[i].config_value) {
                                $(element).prop("checked", "checked");
                            }
                        });
                    }
                }
            }
        });
    })();

    // 保存设置
    $('#saveconfig').on('click', function() {
        $.post('?r=api/SaveConfig', $('#nosubmit_form').serialize(), function(data, status) {
            if (status === 'success') {
                if (data.Ack === 'Success') {
                    hintShow('hint_s', '保存成功！');
                } else if (data.error === 'User authentication fails') {
                    hintShow('hint_w', lang.ajaxinfo.permission_denied);
                } else {
                    hintShow('hint_f', data.Error);
                }
            } else {
                hintShow('hint_f', lang.ajaxinfo.network_error_s);
            }
        })
    })
})