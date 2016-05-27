"use strict";
/**
 * @desc 用户管理详情页
 * @author YangLong
 * @date 2015-09-07
 */
$(document).ready(function(e) {
    var img = [];
    $.get('?r=Api/GetEbayUserInfoByUid', {
        uid: $_GET['uid']
    }, function(data, status) {
        if (status === 'success' && data.Ack === 'Success') {
            var buyerId = data.Body.UserID;
            var base_info_ul = $('#base_info_ul');
            var email = data.Body.Email;
            var site = data.Body.Site;
            var registerDate = data.Body.RegistrationDate > 0 ? intToLocalDate(data.Body.RegistrationDate, 3) : '';
            base_info_ul.find('span[t="clientID"]').html(buyerId);
            base_info_ul.find('span[t="email"]').html(email);
            base_info_ul.find('span[t="site"]').html(site);
            base_info_ul.find('span[t="registerDate"]').html(registerDate);
            getCustNote(buyerId); //获取客户关联的备注
            orderList(buyerId) //购物历史列表
            getMsgList(buyerId); //消息列表
            caseList(buyerId); //case列表
            returnList(buyerId); //return列表
            /**
             * @desc 获取评价
             * @author liaojianwen
             * @date 2015-08-26
             */
            (function() {
                $.get('?r=api/GetBuyerToSellerFeedbackInfo', {
                    userId: buyerId
                }, function(data, textStatus) {
                    if (textStatus == 'success') {
                        if (data.Ack == 'Success') {
                            if (data.Body.length > 0) {
                                var bPositiveNum = 0;
                                var bNeutralNum = 0;
                                var bNegativeNum = 0;
                                for (var x in data.Body) {
                                    if (data.Body[x].CommentType == 'Positive') {
                                        bPositiveNum++;
                                    }
                                    if (data.Body[x].CommentType == 'Neutral') {
                                        bNeutralNum++;
                                    }
                                    if (data.Body[x].CommentType == 'Negative') {
                                        bNegativeNum++;
                                    }
                                }
                                base_info_ul.find('span[t="bPositive"]').html(bPositiveNum).parent().show();
                                base_info_ul.find('span[t="bNeutral"]').html(bNeutralNum).parent().show();
                                base_info_ul.find('span[t="bNegative"]').html(bNegativeNum).parent().show();
                            } else {
                                base_info_ul.find('span[t="bNegative"]').parent().html(lang.cusdet_biz.not_feedback).show();
                            }
                        } else {}
                    } else {}
                });
            })();

            (function() {
                // 选项卡1
                base_info_ul.find('span[t="clientID"]').html('<a href="http://www.ebay.com/usr/' + buyerId +
                    '" target="_blank" class="fontLinkBtn">' + buyerId + '</a>');
            })();

            //发送消息
            $('#sendMsg').on('click', function() {
                var isSendMyMsg = $('#isSendMyMSg').is(':checked');
                var itemId = $('#msgItem').text();
                var msgText = $('#msgText').val();
                var shopId = $('#sendMsg').data('shopid');
                if (msgText === '' || msgText == undefined) {
                    hintShow('hint_w', lang.cusdet_biz.msg_cannot_empty);
                    return;
                }
                if ($("#isSendMyMSg").is(':checked')) {
                    var SendMyEmail = true;
                } else {
                    var SendMyEmail = false;
                }
                $.post('?r=Api/GenerateMsgQueue', {
                    'shopid': shopId,
                    'content': msgText,
                    'imgurl': img,
                    'emailCopyToSender': SendMyEmail,
                    'buyerid': buyerId,
                    'itemid': itemId
                }, function(result, state) {
                    if (state === 'success') {
                        if (result.Error == 'User authentication fails') {
                            hintShow('hint_w', lang.ajaxinfo.permission_denied);
                            return;
                        }
                        if (result.Ack === 'Success') {
                            hintShow('hint_s', lang.cusdet_biz.msg_send_success);
                            $('#msgText').val('');
                            $('#isSendMyMSg').attr('checked', false);
                            $('.imglist_addwindow').empty();
                            $('#msg_length').html('1000');
                        } else {
                            hintShow('hint_f', lang.cusdet_biz.msg_send_failure);
                        }
                    } else {
                        hintShow('hint_f', lang.cusdet_biz.msg_send_network_error);
                    }
                });
            });
        }
    });


    $('#determine').on('click', function() {
        if ($('#imglistli').find('li').length == 0) {
            hintShow('hint_w', lang.cusdet_biz.please_select_picture);
            return;
        }

        $('.addImgTC').hide();

        $('.imglist_addwindow').empty();
        for (var i in img) {
            $('<li><i class="icon-close iconBtnS" title="' + lang.cusdet_biz.click_delete + '"></i><img src="' + img[i] +
                '" width="70px" height="60px"></li>').appendTo('.imglist_addwindow');
        }
        if (i >= 0 && img.length > 0) { //如果上传了图片以及图片数组长度大于0，则展示图片
            $('.imgShow').show();
        }
    });
    //图片上传
    (function() {
        $("#drop-area-div").dmUploader({
            url: '?r=api/Upload',
            allowedTypes: 'image/*',
            extFilter: 'jpg;png;gif;bmp;tif;jpeg',
            //            maxFiles:5,
            maxFileSize: 1024 * 1024,
            dataType: 'json',
            fileName: 'Filedata',
            onInit: function() {},
            onFallbackMode: function(message) {},
            onNewFile: function(id, file) {},
            onBeforeUpload: function(id) {
                if (img.length >= 5) {
                    hintShow('hint_w', lang.cusdet_biz.max_picture_tip);
                    $("#drop-area-div").find('input').attr('disabled', 'disabled');
                    return false;
                }
            },
            onComplete: function() {},
            onUploadProgress: function(id, percent) {},
            onUploadSuccess: function(id, data) {
                if (data.Ack == 'Success') {
                    img.push(data.Body.filepath);
                    $('<li><i class="icon-close iconBtnS" title="' + lang.cusdet_biz.click_delete + '" data-id="' + id + '"></i><img src="' +
                        data.Body.filepath + '" width="70px" height="60px"></li>').appendTo('.imglist_addwindow');
                } else {
                    hintShow('hint_f', lang.cusdet_biz.upload_error);
                    return false;
                }
            },
            onUploadError: function(id, message) {
                hintShow('hint_w', lang.cusdet_biz.upload_error_network + message);
            },
            onFileTypeError: function(file) {
                hintShow('hint_w', lang.cusdet_biz.picture_type_error);
            },
            onFileSizeError: function(file) {
                hintShow('hint_w', lang.cusdet_biz.picture_size_error);
            },
            onFileExtError: function(file) {
                hintShow('hint_w', lang.cusdet_biz.picture_ext_error);
            },
            onFilesMaxError: function(file) {
                hintShow('hint_w', lang.cusdet_biz.picture_num_error);
            }
        });
    })();
    // 点击添加附件事件
    $('#addFile').on('click', function() {
        $('.addImgTC').show();
        $('.imglist_addwindow').empty();
        for (var i in img) {
            $('<li><i class="icon-close iconBtnS" title="' + lang.cusdet_biz.click_delete + '"></i><img src="' + img[i] +
                '" width="70px" height="60px"></li>').appendTo('.imglist_addwindow');
        }
    });
    // 点击关闭添加文件窗口
    $('#cancelUserPic').on('click', function() {
        $('.addImgTC').hide();
        $('.imglist_addwindow').empty();
        for (var i in img) {
            $('<li><i class="icon-close iconBtnS" title="' + lang.cusdet_biz.click_delete + '"></i><img src="' + img[i] +
                '" width="70px" height="60px"></li>').appendTo('.imglist_addwindow');
        }
    });

    // 删除添加窗口的图片列表
    $('.imglist_addwindow').on('click', '.icon-close', function() {
        var picurl = $(this).parents('li').find('img').attr("src");
        img.splice($.inArray(picurl, img), 1);
        $(this).parent().remove();
        if (!img.length) {
            $('.imgShow').hide();
        }
        // 启用控件
        $("#drop-area-div").find('input').removeAttr('disabled');
    });


    var $oSendContent = $('#msgText');
    //判断msg回复字符串是否超过上限
    (function() {
        //敲键盘时候触发计算字数

        $oSendContent.on('input change', function() {
            var textlength = $oSendContent.val().replace(/[^\u0000-\u00ff]/g, "aaa").length;
            var text = $oSendContent.val();
            var length = (1000 - textlength);

            if (length < 0) {
                $("#sendMsg").attr("disabled", true);
                hintShow('hint_w', lang.cusdet_biz.chars_number_error);
                $('#msg_length').html(length);
            } else {
                $("#sendMsg").attr("disabled", false);
                $('#msg_length').html(length);
            }
        });
    })();

    //case 新开页点击进入明细
    $('#case_history').on('click', '.caseDetail', function() {
        var caseid = $(this).data('caseid');
        var caseType = $(this).data('casetype');
        switch (caseType) {
            case 'EBP_INR':
                caseType = 'ebp_inr';
                break;
            case 'EBP_SNAD':
                caseType = 'ebp_snad';
                break;
            case 'CANCEL_TRANSACTION':
                caseType = 'cancel';
                break;
            case 'UPI':
                caseType = 'upi';
                break;
        }
        window.open('?r=Home/DisputeDetail&caseid=' + caseid + '&type=' + caseType);
    });

    //return 新开页点击进入明细
    $('#return_history').on('click', '.returnDetail', function() {
        var returnid = $(this).data('returnid');
        window.open('?r=Home/ReturnDetail&returnid=' + returnid);
    });

    //关闭弹窗
    $('#close_btn').on('click', function() {
        $('#more_div').hide();
        $('#msgText').val('');
        $('.imglist_addwindow').empty();
        $('#imglistli').empty();
        img = [];
        $('#choose_pic').removeAttr('disabled');
    });
});


/**
 * @desc 联系客户
 * @author liaojianwen
 * @date 2015-09-22
 */
$('#buyHistory_div').find('tbody').on('click', '.iconSended', function() {
    $('#more_div').show();
    $('#shopAccount').html($(this).data('nickname'));
    $('#msgItem').html($(this).data('item'));
    $('#sendMsg').data('shopid', $(this).data('shopid'));
    (function() {
        //获取信息模版方法
        function getSubClass(pid, data) {
            var result = [];
            for (var i in data) {
                if (data[i].pid == pid) {
                    result.push(data[i]);
                }
            }
            return result;
        }

        /**
         * @desc 获取信息模板
         * @param int pid 模板父ID
         * @author lvjianfei
         * @date 2015-04-06
         */
        function getSubClassHtml(pid, data) {
            var obj = getSubClass(pid, data);
            if (obj.length == 0) {
                $.get("?r=api/GetTpList", {
                    'pid': pid,
                    '_rnd': loading()
                }, function(listdata, state) {
                    removeloading();
                    if (state === 'success') {
                        if (listdata.Body.list !== '') {
                            $.each(listdata.Body.list, function(index, item) {
                                $("#msgTemp select[pid=" + pid + "]").append('<option value="' + item.tp_list_id + '">' + item.title +
                                    '</option>');
                            });
                            $("#msgTemp select[pid=" + pid + "]").change(function(e) {
                                var fid = $(this).find('option:selected').val();
                                $(this).nextAll().remove();
                                $.each(listdata.Body.list, function(index, item) {
                                    if (fid === item.tp_list_id) {
                                        $("#msgText").val(item.content);
                                    }
                                });
                            });
                        }
                    }
                });

            }
            $("#msgTemp").append('<select pid="' + pid + '"><option value="">' + lang.cusdet_biz.please_select + '</option></select>');
            for (var i in obj) {
                $("#msgTemp select[pid=" + pid + "]").append('<option value="' + obj[i].tp_class_id + '">' + obj[i].classname + '</option>');
            }
            $("#msgTemp select[pid=" + pid + "]").change(function(e) {
                var npid = $(this).find('option:selected').val();
                $(this).nextAll().remove();
                if (npid > 0) {
                    getSubClassHtml(npid, data);
                }
            });
        }
        $("#msgTemp").empty();
        $.get("?r=api/GetTpClassList", function(data, state) {
            if (state == 'success') {
                getSubClassHtml(0, data);
            } else {
                hintShow('hint_f', lang.ajaxinfo.network_error);
            }
        });
    })();
});


/**
 * @desc 添加customer备注事件
 * @author liaojianwen
 * @date 2015-08-26
 */
(function() {
    $("#sub-note").on('click', function() {
        var text = $("#note-text").val();
        if (text == '') {
            hintShow('hint_w', lang.cusdet_biz.note_cannot_empty);
            return;
        }
        var itemid = $('#itemoptions').find("option:selected").text();
        var clientId = $('#base_info_ul').find('span[t="clientID"]').text();
        if (itemid == '请选择item ID') {
            itemid = '';
        }
        $.ajax({
            url: '?r=api/AddFeedbackNote&itemid=' + itemid + '&clientid=' + clientId + '&text=' + text,
            success: function(data, state) {
                if (state == 'success') {
                    if (data.Ack == 'Success') {
                        $("#note-text").val('');
                        getCustNote(clientId);
                    } else if (data.Error == 'User authentication fails') {
                        hintShow('hint_w', lang.ajaxinfo.permission_denied);
                    } else {
                        hintShow('hint_f', lang.ajaxinfo.network_error);
                    }
                }
            }
        })
    })
})();

/**
 * @desc 订单备注
 * @author liaojianwen
 * @date 2015-08-26
 */
function getCustNote(clientId) {
    $.get('?r=api/GetCustNote', {
        'clientid': clientId
    }, function(data, status) {
        if (status == 'success' && data.Ack == 'Success') {
            var M = data.Body;
            var itemNote;
            if (M && M.length > 0) {
                $('#note li').remove();
                for (var i in M) {
                    if (M[i].item_id.length > 0) {
                        itemNote = 'item ID: ' + M[i].item_id;
                    } else {
                        itemNote = '';
                    }
                    $('<li>' + M[i].text +
                        '<p><small> ' + itemNote + ' &nbsp;' + M[i].author_name +
                        ' &nbsp;' + intToLocalDate(M[i].create_time, 3) + '</small></p></li>').appendTo("#note");
                }
            }
        }
    })
};

/**
 * @desc 生成消息列表分页
 * @param buyerId
 * @param pageInfo
 * @author liaojianwen
 * @date 2015-09-24
 */
function getMsgList(buyerId, pageInfo) {
    var page;
    var pageSize;
    if (pageInfo != undefined) {
        page = pageInfo.page;
        pageSize = pageInfo.pageSize;
    } else {
        page = 1;
        pageSize = 20;
    }
    $.get('?r=api/GetMsgListByClientId', {
        'clientid': buyerId,
        'page': page,
        'pageSize': pageSize
    }, function(data, status) {
        if (status === 'success' && data.Ack === 'Success') {
            var M = data.Body.list;
            var userId;
            $('.sideTab').find('li').remove();
            for (var i in M) {
                $('.sideTab').append('<li data-msgid=' + M[i].msg_id + '><h3> <span class="name">' + M[i].nick_name + '</span> <span class="ject">' + M[i].Subject +
                    '</span><span class="reDate">' + intToLocalDate(M[i].ReceiveDate, 3) + '</span><i></i></h3></li> ');
            }
            page = data.Body.page.page;
            pageSize = data.Body.page.pagesize;
            if (data.Body.count == 0) {
                $('<li><span>' + lang.cusdet_biz.not_data + '</span></h3></li>').appendTo('.sideTab');
                SearchOrderPage(buyerId, 1, 1, 1, getMsgList); //分页
            } else {
                SearchOrderPage(buyerId, page, pageSize, data.Body.count, getMsgList); //分页
            }
        }
    })
}


/**
 * @desc 生成消息列表分页
 * @author  liaojianwen
 * @dete 2015-09-24
 */

function SearchOrderPage(buyerId, page, pageSize, total, eventHandler) {
    var $paginationNavBar = $("#pageCount"); // 分页导航条节点
    if (total === undefined || total === 0) {
        $paginationNavBar.empty();
        return;
    }
    var pageSize = 5; //默认每页显示数
    var pageCount = Math.ceil(total / pageSize); // 总页数
    var currentPage = page; // 当前页码
    var template = '';
    var tt = '';
    for (var i = 1; i <= pageCount; i++) {
        tt += '<option ' + (currentPage === i ? 'selected="selected"' : '') + ' value="' + i + '">' + i + '/' + pageCount + '</option>';
    }
    if (currentPage === 1) {
        // 在第一页，禁用前一页的按钮
        template += '<div class="pageBtnBox"><select name="" id="pageCli" data-pagesize data-page>' + tt + '</select>' +
            '<span class="preBtn pageBtn notOpBtn"><i class="icon-chevron-left"></i></span>' + '<span ' + (currentPage === pageCount ?
                'class="nextBtn pageBtn notOpBtn"' : ' data-page data-pagesize class="nextBtn pageBtn"') + '><i title="' + lang.common.next_page +
            '" class="icon-chevron-right"><a data-toggle="tooltip" data-page="' + (currentPage + 1) + '" data-pagesize="' + pageSize + '"></a></i></span></div>';
    } else {
        //不在第一页，显示前后一页的按钮
        template += '<div class="pageBtnBox"><select name="" id="" style="width:80px;" data-pagesize data-page>' + tt + '</select>' +
            '<span data-page data-pagesize class="preBtn pageBtn"><i title="' + lang.common.previous_page +
            '" class="icon-chevron-left"><a data-toggle="tooltip" data-page="' + (currentPage - 1) + '" data-pagesize="' + pageSize + '"></a></i></span>' + '<span ' +
            (currentPage === pageCount ? 'class="nextBtn pageBtn notOpBtn"' : ' data-page data-pagesize class="nextBtn pageBtn"') + '><i title="' + lang.common.next_page +
            '" class="icon-chevron-right"><a data-toggle="tooltip" data-page="' + (currentPage + 1) + '" data-pagesize="' + pageSize + '"></a></i></span></div>';
    }
    $paginationNavBar.html(template).show();
    // 页码按钮绑定事件
    $("span[data-page][data-pagesize]").on('click', function(event) {
        var $currentItem = $(this);
        var selectedPage = numberClean($currentItem.find("a").data("page"));
        var selectedPageSize = numberClean($currentItem.find("a").data("pagesize"));
        if (eventHandler != undefined && (typeof eventHandler == "function")) {
            eventHandler(buyerId, {
                'page': selectedPage,
                'pageSize': selectedPageSize
            });
        }
    });
    // 分页Size按钮绑定事件
    $("select[data-page][data-pagesize]").change(function(event) {
        var selectedPage = undefined;
        var selectedPage = numberClean($(this).children("option:selected").val());
        if (eventHandler != undefined && (typeof eventHandler == "function")) {
            eventHandler(buyerId, {
                'page': selectedPage,
                'pageSize': pageSize
            });
        }
    });

}
$('.sideTab').on('click', 'h3', function() {
    var $this = $(this);
    var msgid = $(this).parents('li').data('msgid');
    loading();
    $.get('?r=api/GetMsgTexts', {
        'msgid': msgid
    }, function(data, status) {
        removeloading();
        if (status === 'success' && data.Ack === 'Success') {
            var V = data.Body.Contents;
            var _html = '';
            $this.parents('li').find('ul').remove();
            for (var k in V) {
                if (V[k].FolderID == 1) {
                    _html += '<li class="sellerMSG"><div class="MSGC"><p><b>' + intToLocalDate(V[k].ReceiveDate, 3) +
                        '</b> &nbsp; SELLER</p><p><i> &lt;SELLER_SEND_MESSAGE &gt;</i>' +
                        '<br>' + V[k].effect_content + '</p></div></li>';
                } else {
                    _html += '<li class="buyerMSG"><div class="MSGC"><p><b>' + intToLocalDate(V[k].ReceiveDate, 3) + '</b> &nbsp; BUYER</p><p>' + V[k].effect_content +
                        '</p></div></li>';
                }
            }
            $this.siblings('.dialogBox').remove();
            $this.after('<ul class="dialogBox">' + _html + '</ul>');
            $('.sideTab li>ul').hide(0);
            $this.next('ul').show(0);
        }
    });

    $('.sideTab li>ul').hide(0);
    $(this).next('ul').show(0);
});

/**
 * @desc 获取购物历史
 * @author  liaojianwen
 * @date 2015-07-31
 */
function orderList(buyerId) {
    $.get('?r=api/GetEbayTransactionsByUserId', {
        BuyerUserID: buyerId
    }, function(data, textStatus) {
        if (textStatus == 'success') {
            if (data.Ack === 'Success') {
                $("#buyHistory_div").find('tbody').empty();
                if (data.Body.length > 0) {
                    for (var x in data.Body) {
                        var transStr = '';
                        for (var y in data.Body[x].ExtTrans) {
                            transStr += ('<div class="textleft tooltip_td" title="' + lang.cusdet_biz.transaction_time + '：' + intToLocalDate(data.Body[x].ExtTrans[
                                    y].ExternalTransactionTime, 3) + '<br />' + lang.cusdet_biz.transaction_amount + '：' + data.Body[x].ExtTrans[y].PaymentOrRefundAmount +
                                data.Body[x].ExtTrans[y].PaymentOrRefundAmount_currencyID + '' + '<br />' + lang.cusdet_biz.final_value_fees + '：' + data.Body[
                                    x].ExtTrans[y].FeeOrCreditAmount + data.Body[x].ExtTrans[y].FeeOrCreditAmount_currencyID + '' + '<br />' + lang.cusdet_biz
                                .transaction_status + '：' + data.Body[x].ExtTrans[y].ExternalTransactionStatus + '' + '">' + data.Body[x].ExtTrans[y].ExternalTransactionID +
                                '(' + data.Body[x].ExtTrans[y].PaymentOrRefundAmount + data.Body[x].ExtTrans[y].PaymentOrRefundAmount_currencyID + ')' +
                                '</div>');
                        }
                        //                                     // 属性
                        var _shuxing = '';
                        var _feedback = '';
                        switch (data.Body[x].CommentType) {
                            case 'Positive':
                                _feedback = '<i class="iconPos"></i>';
                                break;
                            case 'Negative':
                                _feedback = '<i class="iconNeg"></i>';
                                break;
                            case 'Neutral':
                                _feedback = '<i class="iconNeu"></i>';
                                break;
                            default:
                                _feedback = '<i class="iconNone">' + lang.cusdet_biz.none + '</i>';
                        }
                        var addressStr = '';
                        if (data.Body[x].CountryName !== null) {
                            addressStr += data.Body[x].Name + ', ';
                            addressStr += data.Body[x].Street1 + ', ';
                            if (data.Body[x].Street2.length > 0) {
                                addressStr += data.Body[x].Street2 + ', ';
                            }
                            addressStr += data.Body[x].CityName + ', ';
                            addressStr += data.Body[x].StateOrProvince + ', ';
                            addressStr += data.Body[x].PostalCode + ', ';
                            addressStr += data.Body[x].CountryName;
                            if (data.Body[x].Phone) {
                                addressStr += '<br />Phone:' + data.Body[x].Phone;
                            }
                        }
                        var orderStatus = '';
                        if (data.Body[x].OrderStatus) {
                            orderStatus = data.Body[x].OrderStatus;
                        }

                        if (
                            typeof data.Body[x].VariationSpecificsXML.xml.NameValueList !== 'undefined' &&
                            typeof data.Body[x].VariationSpecificsXML.xml.NameValueList !== 'string' &&
                            (data.Body[x].VariationSpecificsXML.xml.NameValueList.length > 0 || (
                                typeof data.Body[x].VariationSpecificsXML.xml.NameValueList.Name === 'string' &&
                                data.Body[x].VariationSpecificsXML.xml.NameValueList.Name.length > 0
                            ))) {
                            if (data.Body[x].VariationSpecificsXML.xml.NameValueList.length > 0) {
                                for (var j = 0; j < data.Body[x].VariationSpecificsXML.xml.NameValueList.length; j++) {
                                    _shuxing += '<br/>' + data.Body[x].VariationSpecificsXML.xml.NameValueList[j].Name + ':' + data.Body[x].VariationSpecificsXML
                                        .xml.NameValueList[j].Value;
                                }
                            } else {
                                _shuxing += '<br/>' + data.Body[x].VariationSpecificsXML.xml.NameValueList.Name + ':' + data.Body[x].VariationSpecificsXML.xml.NameValueList
                                    .Value;
                            }
                        }
                        //                                     
                        var sku_str = HTMLDecode(HTMLDecode(data.Body[x].Item_SKU != '' ? data.Body[x].Item_SKU : data.Body[x].Variation_SKU));
                        $("#buyHistory_div").find('tbody').append('<tr data-key=' + x + '><td>' + data.Body[x].SellerUserID +
                            '</td><td t="sku" style="word-break:break-all;">' + sku_str +
                            '<br />ItemID:<a href="http://cgi.ebay.com/ws/eBayISAPI.dll?ViewItem&item=' + data.Body[x].Item_ItemID +
                            '" target="_blank" class="fontLinkBtn">' + data.Body[x].Item_ItemID + '</a>' + (data.Body[x].ProductName.length > 0 ? (
                                '<br />Prodtct:' + HTMLDecode(HTMLDecode(data.Body[x].ProductName))) : '') + _shuxing + '</td><td t="pnum">' + data.Body[x]
                            .QuantityPurchased + '<hr />' + data.Body[x].TransactionPrice + data.Body[x].TransactionPrice_currencyID +
                            '</td><td t="ptime" style="word-break:break-all;line-height:1.1em;">' + intToLocalDate(data.Body[x].CreatedDate, 8) +
                            '</td><td t="pm" style="line-height:1.5em;">' + data.Body[x].PaymentMethod + transStr + '</td>' + '<td title="' + addressStr +
                            '" class="tooltip_td"><a href="javascript:;">' + lang.cusdet_biz.view + '</a></td>' + '<td>' + orderStatus + '</td>' +
                            '<td t="note" class="tooltip_td">..</td>' + '<td class="evaluate">' + _feedback + '</td>' + '<td><i class="iconSended" title="' +
                            lang.cusdet_biz.click_contact_buyer + '" data-item=' + data.Body[x].Item_ItemID + ' data-nickname=' + data.Body[x].SellerUserID +
                            ' data-shopid=' + data.Body[x].shop_id + '></td></tr>');
                        var itemlist = [];
                        itemlist.push(data.Body[x].Item_ItemID);
                        (function() {
                            // 获取item备注
                            var _key = x;
                            $.get('?r=api/GetItemNotes', {
                                itemId: data.Body[x].Item_ItemID
                            }, function(data, textStatus) {
                                if (data.Ack == 'Success') {
                                    var noteText = [];
                                    for (var x in data.Body) {
                                        noteText[x] = (lang.cusdet_biz.author + '：' + data.Body[x].author_name + '\n' + lang.cusdet_biz.content +
                                            '：' + data.Body[x].text + '\n' + lang.cusdet_biz.buyer + '：' + data.Body[x].cust + '\n' + lang.cusdet_biz
                                            .time + '：' + intToLocalDate(data.Body[x].create_time, 7));
                                    }
                                    $("#buyHistory_div").find('tr[data-key=' + _key + ']').find('td[t="note"]').html('<i class="icon-remark"></i>')
                                        .attr('title', noteText.join('\n\n'));
                                } else if (data.Ack == 'Warning') {
                                    $("#buyHistory_div").find('tr[data-key=' + _key + ']').find('td[t="note"]').html(lang.cusdet_biz.none);
                                } else {
                                    $("#buyHistory_div").find('tr[data-key=' + _key + ']').find('td[t="note"]').html('✖');
                                }
                            });
                        })();
                        var user_email = $('#base_info_ul').find('span[t="email"]').html();
                        if (user_email.length == 0) {
                            $('#base_info_ul').find('span[t="email"]').html(data.Body[x].Buyer_Email);
                        }
                        $(document).ready(function(e) {
                            $(function() {
                                tooltip($(".tooltip_td"));
                                /*$(".tooltip_td").tooltip({
                                    track: true,
                                    content: function() {
                                        var element = $(this);
                                        return element.attr('title');
                                    }
                                });*/
                            });
                        });
                    }

                    var options = '<option value=" ">' + lang.cusdet_biz.select_item_id + '</option>';
                    for (var k in itemlist) {
                        options += '<option value=" ">' + itemlist[k] + '</option>';
                    }
                    $('#itemoptions').append(options);
                } else {
                    $("#buyHistory_div").find('tbody').append('<tr><td colspan="10">' + lang.cusdet_biz.not_find_data + '</td></tr>');
                }
            } else {}
        } else {}
    });
};

/**
 * @desc case 历史
 * @author liaojianwen
 * @date 2015-09-22
 */
function caseList(buyerId) {
    $.get('?r=api/GetCaseListByUserId', {
        BuyerUserID: buyerId
    }, function(data, status) {
        if (status === 'success' && data.Ack === 'Success') {
            $('#case_history').find('tbody').empty();
            var openReason;
            for (var i in data.Body) {
                switch (data.Body[i].caseId_type) {
                    case 'CANCEL_TRANSACTION':
                    case 'UPI':
                        openReason = data.Body[i].DisputeReason;
                        break;
                    case 'EBP_INR':
                    case 'EBP_SNAD':
                        openReason = data.Body[i].openReason;
                        break;
                }

                $('#case_history').find('tbody').append('<tr><td>' + data.Body[i].nick_name + '</td><td>' + data.Body[i].i_itemId + '</td><td> ' + openReason +
                    '</td><td> ' + intToLocalDate(data.Body[i].creationDate, 8) +
                    '</td><td>' + ucfirst(data.Body[i].status) + '</td><td><a href="javascript:;" class="fontBtn caseDetail" data-caseid=' + data.Body[i].case_id +
                    ' data-casetype=' + data.Body[i].caseId_type + '> ' + lang.cusdet_biz.details + ' </a></td></tr>');
            }
        } else {
            $('#case_history').find('tbody').append('<tr><td colspan="6">' + lang.cusdet_biz.not_find_data + '</td></tr>');
        }
    })
};

/**
 * @desc return 历史
 * @author liaojianwen
 * @date 2015-09-22
 */
function returnList(buyerId) {
    $.get('?r=api/GetReturnListByUserId', {
        BuyerUserID: buyerId
    }, function(data, status) {
        if (status === 'success' && data.Ack === 'Success') {
            $('#return_history').find('tbody').empty();
            for (var j in data.Body) {
                $('#return_history').find('tbody').append('<tr><td>' + data.Body[j].nick_name + '</td><td>' + data.Body[j].D_iD_itemId + '</td><td>' + ucfirst(
                        data.Body[j].S_CI_reason) + '</td><td>' + intToLocalDate(data.Body[j].S_CI_creationDate, 8) + '</td><td>' + data.Body[j].S_state +
                    '</td><td><a href="javascript:;" class="fontBtn returnDetail" data-returnid=' + data.Body[j].return_request_id + '> ' + lang.cusdet_biz
                    .details + ' </a></td></tr>')
            }
        } else {
            $('#return_history').find('tbody').append('<tr><td colspan="6">' + lang.cusdet_biz.not_find_data + '</td></tr>')
        }
    })
};
// end file