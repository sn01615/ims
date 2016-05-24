"use strict";
/**
 * @desc 加载return 明细页
 * @author liaojianwen
 * @date 2015-06-19
 */

$(document).ready(function(e) {
    //信息源更多客户信息弹窗选项卡-林培雁-2015-6-12
    $('.cusInfoTC h2 li').on('click', function(e) {
        var index = $(this).index('.cusInfoTC h2 li');
        $('.cusInfoTC h2 li').removeClass('active');
        $(this).addClass('active');
        $('.cusInfoTC .cusBox').hide();
        $('.cusInfoTC .cusBox').eq(index).show();
    })
    var global_info = {
        //全局变量  当前页面的caseid
        'returnid': $_GET['returnid'] ? $_GET['returnid'] : undefined,
        'status': $_GET['status'] ? $_GET['status'] : undefined, //状态
        'itemid': $_GET['itemid'] ? $_GET['itemid'] : undefined, //item NO 用于查询
        'buyer': $_GET['buyer'] ? $_GET['buyer'] : undefined, //客户       
        'itemId': undefined,
        'date': undefined
    };
    var img = [];
    var _USER = 0;
    //查询条件
    var returnStatus = getCookie('ReturnStaus');
    var $window_client_info = $("#window_client_info");
    if (returnStatus) {
        global_info.status = returnStatus;
    }
    var returnItem = getCookie('ReturnItemId');
    if (returnItem) {
        global_info.itemid = returnItem;
    }
    var cust = getCookie('ReturnCust');
    if (cust) {
        global_info.buyer = cust
    }
    var buyerLoginName = '';
    var closeTime = '';
    getReturnHistory();
    //获取退款号
    getOrdersRefund();
    getReturnDetail();
    getReturnAddr();
    /**
     * @desc 获取return明细
     * @author liaojianwen
     * @date 2015-06-19
     */
    function getReturnDetail(returnid) {
        if (typeof(returnid) !== 'undefined' || returnid !== '') {
            var global = {
                'returnid': global_info.returnid
            };
        }
        $.get('?r=api/GetReturnDetail', global_info, function(data, status) {
            if (status === 'success' && data.Ack === 'Success') {
                var seller;
                var buyer;
                var result = data.Body.list;
                global_info.itemId = result.D_iD_itemId;
                if (result.flag) {
                    _USER = 1;
                }
                //top 部分
                var topShow = '';
                if (result.S_state !== 'CLOSED') {
                    topShow = '<li><span class="leftName">' + lang.returndet_biz.top.deadline + '</span><span class="rightVal">' + intToLocalDate(
                        result.S_sRD_respondByDate, 3) + '</span></li>';
                } else {
                    topShow = '<li><span class="leftName">' + lang.returndet_biz.top.end_time + '</span><span class="rightVal">' + intToLocalDate(
                        result.closeTime, 3) + '</span></li>';
                }
                if (result.D_closeReason && result.S_state === 'CLOSED') {
                    topShow += '<li><span class="leftName">' + lang.returndet_biz.top.close_reason + '</span><span class="rightVal">' + ucfirst(result.D_closeReason) +
                        '</span></li>';

                }
                if (result.D_rSI_sT_trackingNumber) {
                    topShow += '<li><span class="leftName">' + lang.returndet_biz.top.courier + '</span><span class="rightVal"> ' + result.D_rSI_sT_carrierUsed +
                        '</span></li>' +
                        '<li><span class="leftName">' + lang.returndet_biz.top.track_num + '</span><span class="rightVal"> ' + result.D_rSI_sT_trackingNumber +
                        '</span></li>' +
                        '<li><span class="leftName">' + lang.returndet_biz.top.track_status + '</span><span class="rightVal"> ' + ucfirst(result.D_rSI_sT_deliveryStatus) +
                        '</span></li>';
                }
                if (result.S_eI_eBPCaseId) {
                    topShow += '<li><span class="leftName">' + lang.returndet_biz.top.case_id + '</span><span class="rigthVal"> ' + result.S_eI_eBPCaseId +
                        '</span></li>' +
                        '<li><span class="leftName">' + lang.returndet_biz.top.case_type + '</span><span class="rigthVal"> ' + result.S_eI_caseType +
                        '</span></li>';
                }
                if (topShow === '') {
                    $('#top').hide();
                } else {
                    $('#top').show();
                    $(topShow).appendTo("#top");
                }
                var returnId_id = result.returnId_id;
                var buyerLoginName = result.S_buyerLoginName;
                //left部分
                $('<li><span class="leftName">' + lang.returndet_biz.left.account_id + '</span><span class="rightVal">' + result.S_sellerLoginName +
                    '</span></li>' +
                    '<li><span class="leftName">' + lang.returndet_biz.left.return_id + '：</span><span class="rightVal">' + result.returnId_id +
                    '</span></li>' +
                    '<li><span class="leftName">' + lang.returndet_biz.left.create_date + '</span><span class="rightVal">' + intToLocalDate(result.creationDate,
                        3) + '</span></li>' +
                    '<li><span class="leftName">' + lang.returndet_biz.left.return_state + '</span><span class="rightVal">' + ucfirst(result.S_state) +
                    '</span></li>' +
                    '<li><span class="leftName">' + lang.returndet_biz.left.return_reason + '</span><span class="rightVal" data-reason="' + result.S_CI_reason +
                    '" id="requestreason">' + ucfirst(result.S_CI_reason) + '</span></li>').appendTo("#left");
                //right部分
                $('<li><span class="leftName">' + lang.returndet_biz.right.buyer_id +
                    '</span><span class="rightVal"><a class="fontLinkBtn" href="http://www.ebay.com/usr/' + result.S_buyerLoginName +
                    '" target="_blank">' + result.S_buyerLoginName + '</a></span></li>' +
                    '<li class="custaddr" style="display:none"></li>' +
                    '<li class="customerNote" style="display:none"></li>').appendTo("#right");
                $('<table class="table">' +
                    '<thead><tr>' +
                    '<th>' + lang.returndet_biz.right.photo + '</th>' +
                    '<th id="SKU">' + lang.returndet_biz.right.sku + '</th>' +
                    '<th>' + lang.returndet_biz.right.item_id + '</th>' +
                    '<th>' + lang.returndet_biz.right.purchase_quantity + '</th>' +
                    '<th>' + lang.returndet_biz.right.deal_price + '</th>' +
                    '<th>' + lang.returndet_biz.right.transaction_no + '</th>' +
                    '<th>' + lang.returndet_biz.right.order_time + '</th>' +
                    '<th>' + lang.returndet_biz.right.logistics + '</th>' +
                    '<th>' + lang.returndet_biz.right.ship_time + '</th>' +
                    '<th>' + lang.returndet_biz.right.feedback + '</th>' +
                    '</tr></thead>' +
                    '<tbody><tr data-itemid="' + result.D_iD_itemId + '">' +
                    '<td><img src="" alt="" /></td>' +
                    '<td><span></span></td>' +
                    '<td><a href="http://cgi.ebay.com/ws/eBayISAPI.dll?ViewItem&item=' + (_USER ? result.D_iD_itemId.replace(/(\d{8})\d{4}/,
                        '$1****') : result.D_iD_itemId) + '" target="_blank" class="fontLinkBtn">' + (_USER ? result.D_iD_itemId.replace(
                        /(\d{8})\d{4}/, '$1****') : result.D_iD_itemId) + '</a></td>' +
                    '<td>' + result.D_iD_returnQuantity + '</td>' +
                    '<td id="price">' + result.D_iD_itemPrice + '&nbsp' + result.D_iD_currencyId + '</td>' +
                    '<td id="transaction">' + result.D_iD_transactionId + '</td>' +
                    '<td id="OrderCreateTime"></td>' +
                    '<td id="carrier"></td>' +
                    '<td id="shipedTime"></td>' +
                    '<td class="evaluate"><i class="iconNone"></i></td>' +
                    '</tr></tbody>' +
                    '</table>').appendTo("#orderinfo");
                //部分退款处添加单位
                $('#cash input').after('<span>' + result.D_iD_currencyId + '</span>');

                //获取客户留言
                getEbayOrderNote(global_info.returnid);
                //获取图片
                getItemInfo(result.D_iD_itemPicUrl, result.D_iD_itemId);
                //获取评价信息
                getFeedbackInfo(global_info.returnid);
                //获取图片
                getPictureById(global_info.returnid);
                //获取上下页
                getRetrunPreNextID(global_info.returnid, result.creationDate);
                //获取备注
                getItemNoteList();
                var clientId = result.S_buyerLoginName;
                // 获取买家对卖家的所有评价
                (function() {
                    $.get('?r=api/GetBuyerToSellerFeedbackInfo', {
                        userId: clientId
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
                                    $window_client_info.find('span[t="bPositive"]').html(bPositiveNum).parent().show();
                                    $window_client_info.find('span[t="bNeutral"]').html(bNeutralNum).parent().show();
                                    $window_client_info.find('span[t="bNegative"]').html(bNegativeNum).parent().show();
                                } else {
                                    $window_client_info.find('span[t="bNegative"]').parent().html(lang.returndet_biz.no_feedback).show();
                                }
                            } else {}
                        } else {}
                    });
                })();



                (function() {
                    // 选项卡1

                    $window_client_info.find('span[t="id"]').html('<a href="http://www.ebay.com/usr/' + clientId +
                        '" target="_blank" class="fontLinkBtn">' + clientId + '</a>');
                })();

                (function() {
                    // Item图片显示
                    $.get("?r=api/GetEbayUserInfo", {
                        userid: clientId
                    }, function(data, status) {
                        if (status === 'success') {
                            if (data.Ack === 'Success') {
                                //                            if(data.Body.FeedbackScore!=''){
                                //                                $rval_1.find('span[t="sc"]').html('('+data.Body.FeedbackScore+' / '
                                //                                +data.Body.FeedbackRatingStar+')'+' 好评率:'+data.Body.PositiveFeedbackPercent).show();
                                //                            }
                                $window_client_info.find('span[t="regaddr"]').html(data.Body.Site);
                                if (data.Body.RegistrationDate > 0) {
                                    $window_client_info.find('span[t="RegistrationDate"]').html(intToLocalDate(data.Body.RegistrationDate,
                                        3)).parent().show();
                                }
                                if (data.Body.PositiveFeedbackPercent >= 0) {
                                    $window_client_info.find('span[t="PositiveFeedbackPercent"]').html(data.Body.PositiveFeedbackPercent).parent()
                                        .show();
                                }
                                if (data.Body.UniquePositiveFeedbackCount >= 0) {
                                    $window_client_info.find('span[t="Positive"]').html(data.Body.UniquePositiveFeedbackCount).parent().show();
                                }
                                if (data.Body.UniqueNeutralFeedbackCount >= 0) {
                                    $window_client_info.find('span[t="Neutral"]').html(data.Body.UniqueNeutralFeedbackCount).parent().show();
                                }
                                if (data.Body.UniqueNegativeFeedbackCount >= 0) {
                                    $window_client_info.find('span[t="Negative"]').html(data.Body.UniqueNegativeFeedbackCount).parent().show();
                                }
                                (function() {
                                    var RaddrStr = '';

                                    RaddrStr += data.Body.regaddr_Name + ', ';
                                    RaddrStr += data.Body.regaddr_Street + ', ';
                                    RaddrStr += data.Body.regaddr_Street1 + ', ';
                                    data.Body.regaddr_Street2.length <= 0 ? '' : RaddrStr += data.Body.regaddr_Street2 + ', ';
                                    RaddrStr += data.Body.regaddr_CityName + ', ';
                                    data.Body.regaddr_StateOrProvince.length <= 0 ? '' : RaddrStr += data.Body.regaddr_StateOrProvince +
                                        ', ';
                                    RaddrStr += data.Body.regaddr_PostalCode + ', ';
                                    RaddrStr += data.Body.regaddr_CountryName;
                                    data.Body.regaddr_Phone.length <= 0 ? '' : RaddrStr += lang.returndet_biz.register_info.telephone +
                                        data.Body.regaddr_Phone + ', ';

                                    if (RaddrStr.length > 12) {
                                        $("#window_client_info_l").append('<li style=""><span class="leftName">' + lang.returndet_biz.register_info
                                            .register_addr + '</span><span class="rightVal" style="width:370px;">' + RaddrStr +
                                            '</span></li>');
                                    }
                                })();

                            } else {
                                // hintShow('hint_f','服务器错误!');
                            }
                        } else {
                            hintShow('hint_f', lang.ajaxinfo.network_error);
                        }
                    });


                })();

                /**
                 * @desc 获取购物历史
                 * @author  liaojianwen
                 * @date 2015-07-31
                 */
                (function() {
                    $.get('?r=api/GetEbayTransactionsByUserId', {
                        BuyerUserID: clientId
                    }, function(data, textStatus) {
                        if (textStatus == 'success') {
                            if (data.Ack === 'Success') {
                                $("#buyHistory_div").find('tbody').empty();
                                if (data.Body.length > 0) {
                                    for (var x in data.Body) {
                                        var transStr = '';
                                        for (var y in data.Body[x].ExtTrans) {
                                            transStr += ('<div class="textleft tooltip_td" title="' + lang.returndet_biz.buy_his.transaction_time +
                                                intToLocalDate(data.Body[x].ExtTrans[y].ExternalTransactionTime, 3) + '<br />' + lang.returndet_biz
                                                .buy_his.payment + data.Body[x].ExtTrans[y].PaymentOrRefundAmount + data.Body[x].ExtTrans[
                                                    y].PaymentOrRefundAmount_currencyID + '' + '<br />' + lang.returndet_biz.buy_his.credit_amount +
                                                data.Body[x].ExtTrans[y].FeeOrCreditAmount + data.Body[x].ExtTrans[y].FeeOrCreditAmount_currencyID +
                                                '' + '<br />' + lang.returndet_biz.buy_his.transaction_status + data.Body[x].ExtTrans[y]
                                                .ExternalTransactionStatus + '' + '">' + data.Body[x].ExtTrans[y].ExternalTransactionID +
                                                '(' + data.Body[x].ExtTrans[y].PaymentOrRefundAmount + data.Body[x].ExtTrans[y].PaymentOrRefundAmount_currencyID +
                                                ')' + '</div>');
                                        }
                                        //                                        
                                        //                                        // 属性
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
                                                _feedback = '<i class="iconNone">' + lang.ajaxinfo.none + '</i>';
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
                                            addressStr += lang.returndet_biz.buy_his.phone + data.Body[x].Phone;
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
                                                    _shuxing += '<br/>' + data.Body[x].VariationSpecificsXML.xml.NameValueList[j].Name +
                                                        ':' + data.Body[x].VariationSpecificsXML.xml.NameValueList[j].Value;
                                                }
                                            } else {
                                                _shuxing += '<br/>' + data.Body[x].VariationSpecificsXML.xml.NameValueList.Name + ':' +
                                                    data.Body[x].VariationSpecificsXML.xml.NameValueList.Value;
                                            }
                                        }
                                        var sku_string = data.Body[x].Item_SKU != '' ? data.Body[x].Item_SKU : data.Body[x].Variation_SKU;
                                        sku_string = HTMLDecode(HTMLDecode(sku_string));
                                        $("#buyHistory_div").find('tbody').append('<tr data-key=' + x + '><td>' + data.Body[x].SellerUserID +
                                            '</td><td t="sku" style="word-break:break-all;">' + sku_string +
                                            '<br />ItemID:<a href="http://cgi.ebay.com/ws/eBayISAPI.dll?ViewItem&item=' + data.Body[x].Item_ItemID +
                                            '" target="_blank" class="fontLinkBtn">' + data.Body[x].Item_ItemID + '</a>' + (data.Body[x]
                                                .ProductName.length > 0 ? ('<br />Prodtct:' + HTMLDecode(HTMLDecode(data.Body[x].ProductName))) :
                                                '') + _shuxing + '</td><td t="pnum">' + data.Body[x].QuantityPurchased +
                                            '</td><td t="pjg">' + data.Body[x].TransactionPrice + data.Body[x].TransactionPrice_currencyID +
                                            '</td><td t="ptime" style="word-break:break-all;line-height:1.1em;">' + intToLocalDate(data
                                                .Body[x].CreatedDate, 8) + '</td><td t="pm" style="line-height:1.5em;">' + data.Body[x]
                                            .PaymentMethod + transStr + '</td>' + '<td><a href="javascript:;" title="' + addressStr +
                                            '" class="tooltip_td">' + lang.returndet_biz.buy_his.check + '</a></td>' + '<td>' +
                                            orderStatus + '</td>' + '<td class="evaluate">' + _feedback + '</td>' +
                                            '<td t="note" class="tooltip_td">..</td></tr>');

                                        (function() {
                                            // 获取item备注
                                            var _key = x;
                                            $.get('?r=api/GetItemNotes', {
                                                itemId: data.Body[x].Item_ItemID
                                            }, function(data, textStatus) {
                                                if (data.Ack == 'Success') {
                                                    var noteText = [];
                                                    for (var x in data.Body) {
                                                        noteText[x] = (lang.returndet_biz.buy_his.notes.author + data.Body[x].author_name +
                                                            '<br />' + lang.returndet_biz.buy_his.notes.content + data.Body[
                                                                x].text + '<br />' + lang.returndet_biz.buy_his.notes.cust +
                                                            data.Body[x].cust + '<br />' + lang.returndet_biz.buy_his.notes
                                                            .create_time + intToLocalDate(data.Body[x].create_time, 7));
                                                    }
                                                    $("#buyHistory_div").find('tr[data-key=' + _key + ']').find('td[t="note"]')
                                                        .html('<i class="icon-remark"></i>').attr('title', noteText.join('\n\n'));
                                                    //执行鼠标经过气泡提示方法
                                                    tooltip($('.tooltip_td'));
                                                } else if (data.Ack == 'Warning') {
                                                    $("#buyHistory_div").find('tr[data-key=' + _key + ']').find('td[t="note"]')
                                                        .html(lang.returndet_biz.buy_his.notes.no_note);
                                                } else {
                                                    $("#buyHistory_div").find('tr[data-key=' + _key + ']').find('td[t="note"]')
                                                        .html('✖');
                                                }
                                            });
                                        })();

                                        $(document).ready(function(e) {
                                            $(function() {
                                                //执行鼠标经过气泡提示方法
                                                tooltip($('.tooltip_td'));
                                            });
                                        });
                                    }
                                } else {
                                    $("#buyHistory_div").find('tbody').append('<tr><td colspan="10">' + lang.returndet_biz.buy_his.no_transaction +
                                        '</td></tr>');
                                }
                            } else {
                                // hintShow('hint_f','服务器错误!');
                            }
                        } else {
                            // hintShow('hint_f','网络错误!');
                        }
                    });
                })();

                /**
                 * @desc 消息历史
                 * @author lioajianwen
                 * @date 2015-07-31
                 */
                (function() {
                    var $his_msgs_ul = $("#his_msgs_ul");
                    var page = $his_msgs_ul.data('page');
                    // var noloading = false;
                    function loadlist(xcpage) {
                        $his_msgs_ul.empty();
                        $his_msgs_ul.html(
                            '<li style="text-align:center; padding:12px;"><img src="public/template/img/loader.gif" width="31" height="31"></li>'
                        );
                        // his_msgs_ul
                        $.get('?r=api/GetMsgsByUserID', {
                            UserID: clientId,
                            page: xcpage
                        }, function(data, textStatus) {
                            if (textStatus == 'success') {
                                if (data.Ack === 'Success') {
                                    $his_msgs_ul.empty();
                                    if (data.Body.length > 0) {
                                        for (var x in data.Body) {
                                            $his_msgs_ul.append('<li data-msgid="' + data.Body[x].msg_id + '" title="' + data.Body[x].Subject +
                                                '">' + '<span>' + intToLocalDate(data.Body[x].ReceiveDate, 9) + '</span><p>' + data.Body[
                                                    x].nick_name + ' | ' + ((data.Body[x].FolderID == 1) ? lang.returndet_biz.msg_his.send :
                                                    lang.returndet_biz.msg_his.receive) + data.Body[x].Subject + '</p></li>');
                                        }
                                        // noloading = true;
                                        $his_msgs_ul.find('li').eq(0).click();
                                    } else {
                                        $his_msgs_ul.html('<li>' + lang.returndet_biz.msg_his.no_msg + '</li>');
                                        $('#his_msg_box2').attr('style', '');
                                        $("#his_msg_box2").html('');
                                        if (page > 1) {
                                            hintShow('hint_w', lang.returndet_biz.msg_his.last_page);
                                            page--;
                                            loadlist(page);
                                        }
                                    }
                                } else {
                                    // hintShow('hint_f','服务器错误!');
                                }
                            } else {
                                // hintShow('hint_f','网络错误!');
                            }
                        });
                    }

                    loadlist(page);

                    $("#his_msgs_up").on('click', function() {
                        if (page > 1) {
                            page--;
                        } else {
                            hintShow('hint_w', lang.returndet_biz.msg_his.first_page);
                        }
                        loadlist(page);
                    });

                    $("#his_msgs_down").on('click', function() {
                        page++;
                        loadlist(page);
                    });

                    $("#his_msgs_ul").on('click', 'li', function() {
                        // his_msg_box
                        // noloading?null:loading();
                        // noloading = false;
                        $('#his_msgs_ul').find('li').each(function(index, element) {
                            $(this).removeClass('active');
                        });
                        if ($(this).data('msgid')) {
                            $(this).addClass('active');
                            $("#his_msg_box2").html(
                                '<div style="margin:50px auto; text-align:center;"><img src="public/template/img/loader.gif" width="31" height="31"></div>'
                            );
                            $.get('?r=api/getMsg', {
                                msgid: $(this).data('msgid')
                            }, function(data, textStatus) {
                                // 历史消息内容
                                removeloading();
                                if (textStatus == 'success') {
                                    if (data.Ack === 'Success') {

                                        var $msgtext = $('<div>' + data.Body.Text + '</div>');
                                        $msgtext.find('meta').remove();
                                        $msgtext.find('img').each(function(index, element) {
                                            if ($(element).attr('src') !== undefined && $(element).attr('src').indexOf(
                                                    'button') == -1 && $(element).attr('src').indexOf('ebaystatic') == -1) {
                                                if ($(element).parent('a').length == 0) {
                                                    $(element).replaceWith('<a>' + $(element)[0].outerHTML + '</a>');
                                                }
                                            }
                                        });
                                        $msgtext.find('img').each(function(index, element) {
                                            if ($(element).attr('src') !== undefined && $(element).attr('src').indexOf(
                                                    'button') == -1 && $(element).attr('src').indexOf('ebaystatic') == -1 &&
                                                $(element).attr('src').indexOf('/globalAssets/') == -1 && $(element).attr(
                                                    'src').indexOf('/icon/') == -1 && $(element).attr('src').indexOf(
                                                    '/roveropen/') == -1) {
                                                $(element).parent('a').attr('data-lightbox', 'imgGroup14');
                                                var imgurl = $(element).attr('src').toString().replace(
                                                    /(%24|\$)_\d+(?=\.(jpg|png|jpeg|gif|bmp|tif)\??)/i, '$_10');
                                                $(element).parent('a').attr('href', imgurl);
                                            }
                                        });
                                        $msgtext.find('a').each(function(index, element) {
                                            if ($(element).attr('href') !== undefined && $(element).attr('href').indexOf(
                                                    'http://') != -1) {
                                                $(element).attr('target', '_blank');
                                            }
                                        });

                                        $msgtext.find('>#Header').each(function(index, element) {
                                            $(element).hide();
                                        });

                                        $msgtext.find('>#Title').each(function(index, element) {
                                            $(element).hide();
                                        });

                                        $msgtext.find('>#ReferenceId').each(function(index, element) {
                                            $(element).hide();
                                        });

                                        $msgtext.find('>#Footer').each(function(index, element) {
                                            $(element).hide();
                                        });

                                        $msgtext.find('>#MarketSaftyTip').each(function(index, element) {
                                            $(element).hide();
                                        });

                                        $msgtext.find('#version3').each(function(index, element) {
                                            $(element).hide();
                                        });

                                        $msgtext.find('.FooterSeparator').hide().next('table').hide();

                                        $msgtext.find('>div').each(function(index, element) {
                                            $(element).addClass($(element).attr('id'));
                                            $(element).removeAttr('id');
                                        });

                                        $msgtext.find('>.RawHtmlText>div').each(function(index, element) {
                                            if ($(element).attr('id') != undefined) {
                                                $(element).addClass('L2' + $(element).attr('id'));
                                                $(element).removeAttr('id');
                                            };
                                        });

                                        $msgtext.find('img[src="http://q.ebaystatic.com/aw/pics/s.gif"]').closest('table').hide();
                                        $msgtext.find('img[src="http://q.ebaystatic.com/aw/pics/s.gif"]').remove();
                                        // $msgtext.find('img[src="http://p.ebaystatic.com/aw/pics/hk/buttons/btnRespond.gif"]').closest('div').hide();
                                        // $msgtext.find('img[src="http://p.ebaystatic.com/aw/pics/at/buttons/btnRespond.gif"]').closest('div').hide();
                                        var reg = /\/[a-zA-Z]{2}\/buttons\/btnRespond\.gif/g;
                                        $msgtext.find('img').each(function(index, element) {
                                            var res = $(element).attr('src').match(reg);
                                            if (res != null) {
                                                $(element).closest('div').hide();
                                            }
                                        });

                                        $("#his_msg_box2").html($msgtext);
                                        $("#his_msg_title").html(data.Body.Subject);
                                    } else {
                                        // hintShow('hint_f','服务器错误!');
                                    }
                                } else {
                                    // hintShow('hint_f','网络错误!');
                                }
                            })
                        }
                    });

                })();

            }

        })

    }

    /**
     * @desc 根据returnid 查询客户留言信息
     * @author liaojianwen
     * @date 2015-05-21
     */
    function getEbayOrderNote(returnid) {
        $.get('?r=api/GetEbayOrderNote&returnid=' + returnid, function(data, status) {
            if (status == 'success') {
                if (data.Ack == 'Success') {
                    if (data.Body.Note) {
                        $('.customerNote').html('<span class="leftName">' + lang.returndet_biz.order_note.cust_note + '</span><span>' + data.Body.Note +
                            '</span>').show();
                    }
                    var addr = data.Body.Address;
                    var orderLineItemId = '';
                    if (addr) {
                        var verify_sku = 1; //用于判断是否可以从订单中取出SKU
                        var name = addr.name ? addr.name : '';
                        var street1 = addr.street1 ? addr.street1 : '';
                        var street2 = addr.street2 ? addr.street2 : '';
                        var CityName = addr.CityName ? addr.CityName : '';
                        var StateOrProvince = addr.StateOrProvince ? addr.StateOrProvince : '';
                        var CountryName = addr.CountryName ? addr.CountryName : '';
                        var shipedTime = addr.ShipedTime ? intToLocalDate(addr.ShipedTime, 3) : '';
                        var shippingAddr = name + '&nbsp' + street1 + '&nbsp' + street2 + '&nbsp' + CityName + '&nbsp' + StateOrProvince + '&nbsp' +
                            CountryName
                        $('.custaddr').html('<span class="leftName">' + lang.returndet_biz.order_note.shipping_addr + '</span><span class="rightVal">' +
                            shippingAddr + '</span>').show();
                        $('#orderinfo').find('tr[data-itemid="' + global_info.itemId + '"] td').eq(1).find('span').html(addr.SKUS ? addr.SKUS : addr.SKU);
                        $('#shipedTime').html(shipedTime);
                        var shipingcost = addr.ActualShippingCost ? addr.ActualShippingCost + '&nbsp' + addr.ActualcurrencyID + '<br />' : '';
                        var carrier = addr.ShippingCarrierUsed ? addr.ShippingCarrierUsed + '<br/>' : '';
                        var trackingNumber = addr.ShipmentTrackingNumber ? '<a href="http://www.ec-firstclass.org/?track_number=' + addr.ShipmentTrackingNumber +
                            '" target="_blank" class="fontLinkBtn">' + addr.ShipmentTrackingNumber + '</a>' + '<br />' : '';
                        var shipdetail = carrier + trackingNumber + shipingcost + addr.ShippingService;
                        var transactionId = $("#transaction").text()
                        var transaction = '';
                        var courier = '';
                        if (addr.PaymentMethods) {
                            transaction += addr.PaymentMethods + '<br />' + transactionId + '<br />';
                        }
                        if (addr.PaymentTime) {
                            transaction += intToLocalDate(addr.PaymentTime, 3);
                        }
                        $('#transaction').html(transaction);
                        $('#carrier').html(shipdetail);
                        $('#OrderCreateTime').html(intToLocalDate(data.Body.OrderCreateTime, 3));
                        orderLineItemId = addr.OrderLineItemID;
                    }

                    var V = data.Body.variation;
                    var variation = '';
                    var productName = '';
                    if (orderLineItemId) {
                        $.get('?r=api/GetProductName&OLI=' + orderLineItemId, function(data, status) {
                            if (status === 'success' && data.Ack === 'Success') {
                                productName = data.Body.productName;
                                if (V || productName) {
                                    $('#SKU').after(lang.returndet_biz.order_note.item_info);
                                    if (productName) {
                                        variation += lang.returndet_biz.order_note.product_name + '<p>ProductName :' + productName + '</p>';
                                    }
                                    for (var w in V) {
                                        variation += '<p>' + V[w].Name + '：' + V[w].Value + '</p>';
                                    }
                                    $('#orderinfo').find('tr[data-itemid="' + global_info.itemId + '"] td').eq(1).after('<td>' + variation +
                                        '</td>')
                                }
                            } else {
                                if (V) {
                                    $('#SKU').after('<th>商品</th>');
                                    for (var w in V) {
                                        variation += '<p>' + V[w].Name + '：' + V[w].Value + '</p>';
                                    }
                                    $('#orderinfo').find('tr[data-itemid="' + global_info.itemId + '"] td').eq(1).after('<td>' + variation +
                                        '</td>')
                                }
                            }
                        });
                    } else {
                        if (V) {
                            $('#SKU').after('<th>商品</th>');
                            for (var w in V) {
                                variation += '<p>' + V[w].Name + '：' + V[w].Value + '</p>';
                            }
                            $('#orderinfo').find('tr[data-itemid="' + global_info.itemId + '"] td').eq(1).after('<td>' + variation + '</td>')
                        }
                    }

                    /**
                     * @desc 获取客户信息
                     * @author liaojianwen
                     * @date 2015-07-31
                     */
                    var orderId = data.Body.OrderId;
                    (function() {

                        if (orderId.length == 0) {
                            return;
                        }

                        // 如果有orderId

                        $.get("?r=api/GetEbayTransactionInfo", {
                            OrderLineItemID: orderId
                        }, function(data, status) {
                            if (status === 'success') {
                                if (data.Ack === 'Success') {
                                    for (var i = 0; i < data.Body.length; i++) {
                                        if (data.Body[i].Buyer_Email != '' && data.Body[i].Buyer_Email != 'Invalid Request') {
                                            $window_client_info.find('span[t="mail"]').html(data.Body[i].Buyer_Email);
                                        } else if (data.Body[i].Buyer_StaticAlias != '') {
                                            $window_client_info.find('span[t="mail"]').html(data.Body[i].Buyer_StaticAlias);
                                        }
                                        $window_client_info.find('span[t="name"]').html(data.Body[i].Buyer_UserFirstName + ' ' + data.Body[
                                            i].Buyer_UserLastName);
                                    }
                                } else {
                                    // hintShow('hint_f','服务器错误!');
                                }
                            } else {
                                hintShow('hint_f', '网络错误!');
                            }
                        });
                    })();
                }
            }
            //            //获取退款号
            //            getOrdersRefund();
        });

    }


    /**
     * @desc 获取订单退款金额
     * @author liaojianwen
     * @date 2015-07-17
     */
    function getOrdersRefund() {
        $.get('?r=api/GetOredersRefund&returnid=' + global_info.returnid, function(data, status) {
            if (status === 'success' && data.Ack === 'Success') {
                var refund = data.Body;
                if (refund) {
                    for (var k in refund) {
                        if (refund[k].externalPaymentTrxnId) {
                            var RefundTime = refund[k].creationDate ? intToLocalDate(refund[k].creationDate, 3) : '';
                            var ReferenceID = refund[k].externalPaymentTrxnId ? refund[k].externalPaymentTrxnId : '';
                        }
                    }
                    var topRefund = '';
                    if (ReferenceID) {
                        topRefund += '<li><span class="leftName">' + lang.returndet_biz.order_refund.paypal_transaction_id +
                            '</span><span class="rightVal">' + ReferenceID + '</span></li>';
                    }
                    if (RefundTime) {
                        topRefund += '<li><span class="leftName">' + lang.returndet_biz.order_refund.refund_time + '</span><span class="rightVal">' +
                            RefundTime + '</span></li>';
                    }
                    $('#top').show();
                    $(topRefund).appendTo("#top");

                }
            }
        });
    }
    /**
     * @desc 修改图片大小
     * @author liaojianwen
     * @date 2015-06-30
     */
    function getItemInfo(URL, itemid) {
        var $orderinfo = $('#orderinfo');
        var tr = $orderinfo.find('tr[data-itemid="' + itemid + '"]');
        var img = tr.find('td>img');
        img.attr('src', URL);
        img.replaceWith('<a data-lightbox="example-set-1" href="' + URL.replace(/\$_0\.JPG\?/i, '$_1.JPG?') + '">' + img[0].outerHTML + '</a>');
    }

    /**
     * @desc 根据return_id 查询评价信息
     * @author liaojianwen
     * @date 2015-06-23
     */
    function getFeedbackInfo(returnid) {
        //由transactionId 获取物品评价
        $.get('?r=api/GetFeedbackInfo&returnid=' + returnid, function(data, status) {
            if (status == 'success') {
                if (data.Ack == 'Success') {
                    switch (data.Body.CommentType) {
                        case 'Positive':
                            $('.evaluate i').attr('class', 'iconPos');
                            break;
                        case 'Negative':
                            $('.evaluate i').attr('class', 'iconNeg');
                            break;
                        case 'Neutral':
                            $('.evaluate i').attr('class', 'iconNeu');
                            break;
                        default:
                            $('.evaluate i').attr('class', 'iconNone');
                    }

                } else {
                    $('.evaluate i').attr('class', 'iconNone');
                }
            }
        });
    }

    /**
     * @desc 获取上、下页
     * @param returnid
     * @param creationDate
     * @return
     */
    function getRetrunPreNextID(returnid, creationDate) {
        global_info.date = creationDate;
        $.get('?r=api/GetReturnPreNextID', global_info, function(data, status) {
            if (status === 'success' && data.Ack === 'Success') {
                //上下翻页事件
                var prenextID = data.Body;
                if (typeof(prenextID.preID) === 'undefined' || prenextID.preID === '') {

                    $("#pre").attr('title', lang.returndet_biz.pre_next_id.first_page);
                    $("#pre").addClass('notOpBtn');
                    $("#pre").removeAttr('id');
                }
                if (typeof(prenextID.nextID) === 'undefined' || prenextID.nextID === '') {
                    $("#next").attr('title', lang.returndet_biz.pre_next_id.last_page);
                    $("#next").addClass('notOpBtn');
                    $("#next").removeAttr('id');
                }
                $("#pre").on("click", function() {
                    location.href = "?r=Home/ReturnDetail&returnid=" + prenextID.preID;
                });
                $("#next").on("click", function() {
                    location.href = "?r=Home/ReturnDetail&returnid=" + prenextID.nextID;
                })
            }
        });
    }


    //返回列表页
    $("#leftTopLeft").on('click', function() {
        if (typeof window.parent.returnlist_url == 'undefined') {
            window.parent.$("iframe[name='main']").attr("src", "?r=Home/MsgList&class=inbox");
        } else {
            window.parent.$("iframe[name='main']").attr("src", window.parent.returnlist_url);
        }
    });

    /**
     * @desc 加载return备注
     * @author liaojianwen
     * @date 2015-04-02
     * @modify 2015-04-21
     */
    function getItemNoteList(itemId) {
        if (typeof(itemId) != 'undefined' || itemId != '') {
            var itemId = global_info.itemId;
        }
        var caseId = global_info.caseid;
        setTimeout(function() {
            $.ajax({
                url: '?r=api/GetItemNoteList',
                data: {
                    "itemId": itemId,
                    "type": "return",
                    "returnid": global_info.returnid
                },
                success: function(data, state) {
                    if (data.Ack == 'Success' && state == 'success') {
                        var M = data.Body.list;
                        if (M && M.length > 0) {
                            $('#note li').remove();
                            for (var i in M) {
                                $('<li>' +
                                    '<span>' + M[i].text + '-----</span>' +
                                    '<span>' + intToLocalDate(M[i].create_time, 3) + '&nbsp;</span>' +
                                    '<b>' + M[i].author_name + '</b>' +
                                    '</li>').appendTo("#note");
                            }
                        }
                    }
                }
            })
        }, 800);
    }

    /**
     * @desc 添加return备注事件
     * @author liaojianwen
     * @date 2015-06-23
     */
    $("#sub-note").on('click', function() {
        var text = $("#note-text").val();
        if (text == '') {
            hintShow('hint_w', lang.returndet_biz.remark_no_null);
            return;
        }
        var item_id = global_info.itemId;
        $.ajax({
            url: '?r=api/AddItemNote&returnid=' + global_info.returnid + '&text=' + text,
            success: function(data, state) {
                if (data.Ack == 'Success') {
                    if (state == 'success') {
                        $("#note-text").val('');
                        getItemNoteList();
                    } else {
                        hintShow('hint_f', lang.ajaxinfo.network_error);
                    }
                }
            }
        })
    })

    /**
     * @desc 加载return对话历史
     * @author liaojianwen
     * @date 2015-06-24
     */
    function getReturnHistory(returnid) {
        if (typeof(returnid) !== 'undefined' || returnid !== '') {
            var returnid = global_info.returnid;
        }
        var note = ''
        $.ajax({
            url: '?r=api/GetReturnHistory&returnid=' + returnid,
            success: function(result) {
                if (result.Ack == 'Success') {
                    var M = result.Body.list;

                    //   对话部分
                    if (M && M.length > 0) {
                        closeTime = M[0].creationDate;
                        for (var i in M) {
                            if (M[i].note == '') {
                                note = '<i> &lt; ' + M[i].activity + ' &gt; </i><br />';
                            } else {
                                note = '<i> &lt; ' + M[i].activity + ' &gt; </i><br />' + M[i].note;
                            }
                            if (M[i].rma) {
                                note += '<br /><i>' + M[i].rma + '</i>';
                            }
                            if (M[i].sellerReturnAddr_name) {
                                note += '<br /><i>' + M[i].sellerReturnAddr_name + '</i><br /><i>' + M[i].sellerReturnAddr_street1 + '</i>&nbsp';
                                note += '<i>' + M[i].sellerReturnAddr_street2 + '</i><br /><i>' + M[i].sellerReturnAddr_city + '</i>&nbsp<i>' + M[i]
                                    .sellerReturnAddr_county + '</i>&nbsp';
                                note += '<i>' + M[i].sellerReturnAddr_stateOrProvince + '</i>&nbsp<i>' + M[i].sellerReturnAddr_country +
                                    '</i>&nbsp';
                                note += '<i>' + M[i].sellerReturnAddr_postalCode + '</i>';
                            }
                            if (M[i].author == 'SELLER') {
                                $('<li class="sellerMSG">' +
                                    '<div class="MSGC">' +
                                    '<p><b>' + intToLocalDate(M[i].creationDate, 3) + '</b>&nbsp;' + M[i].author +
                                    '<span class="note_md" data-note-md5=' + M[i].note_md5 + ' data-activity-md5=' + M[i].activityDetial_description_md5 +
                                    '></span></p>' +
                                    '<p>' + note + '</p>' +
                                    '</div></li>').appendTo("#history");
                            } else {
                                $('<li class="buyerMSG">' +
                                    '<div class="MSGC">' +
                                    '<p><b>' + intToLocalDate(M[i].creationDate, 3) + '</b>&nbsp;' + M[i].author + '<span data-note-md5=' + M[i]
                                    .note_md5 + ' data-activity-md5=' + M[i].activityDetial_description_md5 + '></span></p>' +
                                    '<p style="white-space:normal;">' + note + '</p>' +
                                    '</div></li>').appendTo("#history");
                            }
                        }
                        getReturnDeal();
                    }
                    getReturnOperator();
                } else {
                    //对话框部分
                    $('<li style="text-align:center;"><span>' + lang.returndet_biz.no_return_history + '</span></li>').appendTo("#history");
                }
            }
        })
    }

    /**
     * @desc 获取处理人
     * @author liaojianwen
     * @date 2015-07-15
     */
    function getReturnOperator() {
        $.get('?r=api/GetReturnOperator&returnid=' + global_info.returnid, function(data, status) {
            if (status === 'success' && data.Ack === 'Success') {
                var L = data.Body;
                $('.sellerMSG .note_md').each(function(index, obj) {
                    for (var j in L) {
                        if ($(obj).attr('data-activity-md5') == L[j].handle_type_md5 && $(obj).attr('data-note-md5') == L[j].note_md5) {
                            $(obj).after('&nbsp&nbsp <span>' + L[j].handle_user + '</span> ');
                        }
                    }
                })
            }

        });
    }

    /**
     *@desc 获取return 操作
     *@author liaojianwen
     *@date 2015-06-30 
     */
    function getReturnDeal() {
        var option = '';
        var $URL = '';
        $.get('?r=api/GetSellerOptions&returnid=' + global_info.returnid, function(data, status) {
            option += '<option value="" selected="">' + lang.returndet_biz.return_deal.options + '</option>';
            if (status === 'success' || data.Ack === 'Success') {
                var M = data.Body;
                for (var i in M) {
                    switch (M[i].actionType) {
                        case 'SELLER_APPROVE_REQUEST':
                            option += '<option value="approve" >' + lang.returndet_biz.return_deal.approve + '</option>';
                            break;
                        case 'SELLER_ISSUE_REFUND':
                            option += '<option value="issueRefund" >' + lang.returndet_biz.return_deal.issue_refund + '</option>';
                            break;
                        case 'SELLER_OFFER_PARTIAL_REFUND':
                            option += '<option value="issuePartRefund" >' + lang.returndet_biz.return_deal.issue_part_refund + '</option>';
                            break;
                        case 'SELLER_SEND_MESSAGE':
                            option += '<option value="msg" >' + lang.returndet_biz.return_deal.msg + '</option>';
                            break;
                        case 'SUBMIT_DOC':
                            //                          option += '<option value="doc">'+lang.returndet_biz.return_deal.docs+'</option>';//@todo API 上传图片的还有些问题
                            break;
                        case 'SELLER_ESCALATE':
                            option += '<option value="ebayHelp" >' + lang.returndet_biz.return_deal.ebay_help + '</option>';
                            break;
                        case 'SELLER_DECLINE_REQUEST':
                            option += '<option value="decline">' + lang.returndet_biz.return_deal.decline + '</option>';
                            break;

                    }
                    $URL = M[i].actionURL;
                }
                $('#return_deal').html(option);
                change();
                (function() {
                    $.get('?r=api/GetDocCount', {
                        'returnid': global_info.returnid
                    }, function(data, status) {
                        var pictureText;
                        if (status === 'success' && data.Ack === 'Success') {
                            if (data.Body[0].num > 0) {
                                pictureText = lang.returndet_biz.return_deal.pic_text1;
                            } else {
                                pictureText = lang.returndet_biz.return_deal.pic_text2;
                            }
                            var picture;
                            if (getCookie('username') == 'guest') {
                                picture = '<a target="_blank" style="color:#40A3CE;">' + pictureText + '</a>';
                            } else {
                                picture = '<a href="' + $URL + '"target="_blank" style="color:#40A3CE;">' + pictureText + '</a>';
                            }
                            $('#picture').html(picture);
                        }
                    });
                })();
            }

        })

    }

    /**
     * @desc return 操作方式的显示
     * @author liaojianwen
     * @date 2015-07-01
     * @return
     */
    function change() {
        var options = $('#return_deal').val();
        var RequestReason;
        switch (options) {
            case 'approve':
                $('#ebayHelp').hide();
                $('#cash').hide();
                $('#returnAddr').show();
                $('#contactMan').show();
                $('#RMA').show();
                RequestReason = $('#requestreason').data('reason');
                $('.tips').remove();
                switch (RequestReason) {
                    case 'NO_LONGER_NEED_ITEM':
                    case 'FOUND_BETTER_PRICE':
                    case 'NO_REASON':
                        $('#RMA').after('<small class="tips">' + lang.returndet_biz.chang.tip1 + '</small>');
                        break;
                    case 'WRONG_SIZE':
                        $('#RMA').after('<small class="tips">' + lang.returndet_biz.chang.tip2 + '</small>');
                        break;
                    case 'ARRIVED_DAMAGED':
                    case 'DEFECTIVE_ITEM':
                    case 'DIFFER_FROM_LISTING':
                    case 'FAKE_OR_COUNTERFEIT':
                    case 'MISSING_PARTS':
                        $('#RMA').after('<small class="tips">' + lang.returndet_biz.chang.tip3 + '</small>');
                        break;
                }
                $('#classbox').parent().hide();
                $('#text').hide();
                textOption.val('');
                $('#docs').hide();
                break;
            case 'issueRefund':
                $('#ebayHelp').hide();
                $('#cash').hide();
                $('#returnAddr').hide();
                $('#contactMan').hide();
                $('#RMA').hide();
                $('#classbox').parent().hide();
                $('#text').hide();
                textOption.val('');
                $('.tips').remove();
                $('#docs').hide();
                break;
            case 'issuePartRefund':
                $('#ebayHelp').hide();
                $('#cash').show();
                $('#returnAddr').hide();
                $('#contactMan').hide();
                $('#RMA').hide();
                $('#classbox').parent().show();
                $('#text').show();
                textOption.val('');
                $('.tips').remove();
                $('#docs').hide();
                break;
            case 'msg':
                $('#ebayHelp').hide();
                $('#cash').hide();
                $('#returnAddr').hide();
                $('#contactMan').hide();
                $('#RMA').hide();
                $('#classbox').parent().show();
                $('#text').show();
                textOption.val('');
                $('.tips').remove();
                $('#docs').hide();
                break;
            case 'ebayHelp':
                $('#ebayHelp').show();
                $('#cash').hide();
                $('#returnAddr').hide();
                $('#contactMan').hide();
                $('#RMA').hide();
                $('#classbox').parent().show();
                $('#text').show();
                textOption.val('');
                $('.tips').remove();
                $('#docs').hide();
                break;
            case 'doc':
                $('#ebayHelp').hide();
                $('#cash').hide();
                $('#returnAddr').hide();
                $('#contactMan').hide();
                $('#RMA').hide();
                $('#classbox').parent().hide();
                $('#text').hide();
                textOption.val('');
                $('.tips').remove();
                $('#docs').show();
                break;
            case 'decline':
                $('#ebayHelp').hide();
                $('#cash').hide();
                $('#returnAddr').hide();
                $('#contactMan').hide();
                $('#RMA').hide();
                $('#classbox').parent().show();
                $('#text').show();
                $('.tips').remove();
                $('#docs').hide();
                break;
            default:
                $('#ebayHelp').hide();
                $('#cash').hide();
                $('#returnAddr').hide();
                $('#contactMan').hide();
                $('#RMA').hide();
                $('#classbox').parent().hide();
                $('#text').hide();
                $('.tips').remove();
                $('#docs').hide();
                break;
        }

    }

    //切换处理方式
    $('#return_deal').on('change', function() {
        change();
    });
    //提交
    var textOption = $('#text');
    $('#deal').on('click', function() {
        var options = $('#return_deal').val();
        var RMA, returnAddr, PartAmount, text, reason, currencyId, param;
        var myDate = new Date();
        var Thours = myDate.getFullYear() + '-' + (myDate.getMonth() + 1) + '-' + myDate.getDate() + ' ' + myDate.getHours() + ':' + myDate.getMinutes() +
            ':' + myDate.getSeconds();
        switch (options) {
            case 'approve':
                RMA = $('#RMA input').val();
                param = {
                    'RMA': RMA,
                    'returnid': global_info.returnid
                };
                $.get('?r=api/ApproveReturn', param, function(data, status) {
                    if (status === 'success') {
                        if (data.Ack === 'Success') {
                            var uploadId = data.Body.returnRma;
                            (function() {
                                if (uploadId > 0) {
                                    stateBoxFun('show', lang.returndet_biz.deal.waiting);
                                    $.get('?r=api/GetReturnTrueAct', {
                                        'id': uploadId,
                                        'type': 'returnRma'
                                    }, function(result, return_status) {
                                        removeloading();
                                        if (return_status === 'success' && result.Ack === 'Success') {
                                            if (result.Body == 'success') {
                                                stateBoxFun('show', lang.returndet_biz.deal.synch_suc);
                                                addHistroyResponse(Thours, 'SELLER', 'SELLER_APPROVE_REQUEST', '');
                                                addHistroyResponse(Thours, 'SELLER', 'SELLER_PROVIDE_RMA', RMA);
                                                $('#RMA input').val('');
                                                setInterval(function() {
                                                    $('body .stateGB').remove();
                                                }, 2000);
                                            } else {
                                                hintShow('hint_f', lang.returndet_biz.deal.synch_err);
                                            }
                                        } else {
                                            hintShow('hint_f', lang.returndet_biz.deal.synch_err);
                                            stateBoxFun('hide', lang.returndet_biz.deal.synch_err);
                                        }

                                    })
                                } else {
                                    hintShow('hint_f', lang.returndet_biz.deal.synch_err);
                                }
                            })();
                        } else if (data.Error == 'User authentication fails') {
                            hintShow('hint_w', lang.ajaxinfo.permission_denied);
                        } else {
                            hintShow('hint_f', '其他错误，请联系管理员！');
                        }
                    }
                });
                break;
            case 'issueRefund':
                param = {
                    'returnid': global_info.returnid
                };
                $.get('?r=api/IssueReturnRefund', param, function(data, status) {
                    if (status === 'success') {
                        if (data.Ack === 'Success') {
                            var uploadId = data.Body.returnRefund;
                            (function() {
                                if (uploadId > 0) {
                                    stateBoxFun('show', lang.returndet_biz.deal.waiting);
                                    $.get('?r=api/GetReturnTrueAct', {
                                        'id': uploadId,
                                        'type': 'returnRefund'
                                    }, function(result, return_status) {
                                        removeloading();
                                        if (return_status === 'success' && result.Ack === 'Success') {
                                            if (result.Body == 'success') {
                                                stateBoxFun('show', lang.returndet_biz.deal.synch_suc);
                                                addHistroyResponse(Thours, 'SELLER', 'SELLER_ISSUE_REFUND', '');
                                                setInterval(function() {
                                                    $('body .stateGB').remove();
                                                }, 2000);
                                            } else {
                                                hintShow('hint_f', lang.returndet_biz.deal.synch_err);
                                            }
                                        } else {
                                            hintShow('hint_f', lang.returndet_biz.deal.synch_err);
                                            stateBoxFun('hide', lang.returndet_biz.deal.synch_err);
                                        }

                                    })
                                } else {
                                    hintShow('hint_f', lang.returndet_biz.deal.synch_err);
                                }
                            })();
                        } else if (data.Error == 'User authentication fails') {
                            hintShow('hint_w', lang.ajaxinfo.permission_denied);
                        } else {
                            hintShow('hint_f', '其他错误，请联系管理员！');
                        }
                    }

                });
                break;
            case 'issuePartRefund':
                PartAmount = $('#cash input').val();
                if (PartAmount == '') {
                    hintShow('hint_w', lang.returndet_biz.deal.refund_amount_null);
                    return;
                }
                if (isNaN(PartAmount)) {
                    hintShow('hint_w', lang.returndet_biz.deal.refund_amount_null);
                    return;
                }
                text = textOption.val();
                currencyId = $('#cash span').eq(1).html()
                param = {
                    'partamount': PartAmount,
                    'currencyId': currencyId,
                    'text': text,
                    'returnid': global_info.returnid
                };
                $.get('?r=api/IssueReturnPartRefund', param, function(data, status) {
                    if (status === 'success') {
                        if (data.Ack === 'Success') {
                            var uploadId = data.Body.returnPartRefund;
                            (function() {
                                if (uploadId > 0) {
                                    stateBoxFun('show', lang.returndet_biz.deal.waiting);
                                    $.get('?r=api/GetReturnTrueAct', {
                                        'id': uploadId,
                                        'type': 'returnPartRefund'
                                    }, function(result, return_status) {
                                        removeloading();
                                        if (return_status === 'success' && result.Ack === 'Success') {
                                            if (result.Body == 'success') {
                                                stateBoxFun('show', lang.returndet_biz.deal.synch_suc);
                                                addHistroyResponse(Thours, 'SELLER', 'SELLER_OFFER_PARTIAL_REFUND', text);
                                                textOption.val('');
                                                $('#cash input').val('');
                                                setInterval(function() {
                                                    $('body .stateGB').remove();
                                                }, 2000);
                                            } else {
                                                hintShow('hint_f', lang.returndet_biz.deal.synch_err);
                                            }
                                        } else {
                                            hintShow('hint_f', lang.returndet_biz.deal.synch_err);
                                            stateBoxFun('hide', lang.returndet_biz.deal.synch_err);
                                        }

                                    })
                                } else {
                                    hintShow('hint_f', lang.returndet_biz.deal.synch_err);
                                }
                            })();
                        } else if (data.Error == 'User authentication fails') {
                            hintShow('hint_w', lang.ajaxinfo.permission_denied);
                        } else {
                            hintShow('hint_f', '其他错误，请联系管理员！');
                        }
                    }
                });
                break;
            case 'msg':
                text = textOption.val();
                if (text == '') {
                    hintShow('hint_w', lang.returndet_biz.deal.msg_null + '请填写message');
                    return;
                }
                param = {
                    'text': text,
                    'returnid': global_info.returnid
                };
                $.get('?r=api/SendReturnMsg', param, function(data, status) {
                    if (status === 'success') {
                        if (data.Ack === 'Success') {
                            var uploadId = data.Body.returnMsg;
                            (function() {
                                if (uploadId > 0) {
                                    stateBoxFun('show', lang.returndet_biz.deal.waiting);
                                    $.get('?r=api/GetReturnTrueAct', {
                                        'id': uploadId,
                                        'type': 'returnMsg'
                                    }, function(result, return_status) {
                                        removeloading();
                                        if (return_status === 'success' && result.Ack === 'Success') {
                                            if (result.Body == 'success') {
                                                stateBoxFun('show', lang.returndet_biz.deal.synch_suc);
                                                addHistroyResponse(Thours, 'SELLER', 'SELLER_SEND_MESSAGE', text);
                                                textOption.val('');
                                                setInterval(function() {
                                                    $('body .stateGB').remove();
                                                }, 2000);
                                            } else {
                                                hintShow('hint_f', lang.returndet_biz.deal.synch_err);
                                            }
                                        } else {
                                            hintShow('hint_f', lang.returndet_biz.deal.synch_err);
                                            stateBoxFun('hide', lang.returndet_biz.deal.synch_err);
                                        }

                                    })
                                } else {
                                    hintShow('hint_f', lang.returndet_biz.deal.synch_err);
                                }

                            })();
                        } else if (data.Error == 'User authentication fails') {
                            hintShow('hint_w', lang.ajaxinfo.permission_denied);
                        } else {
                            hintShow('hint_f', '其他错误，请联系管理员！');
                        }
                    }

                });
                break;
            case 'ebayHelp':
                reason = $('#reasonList').val()
                text = textOption.val();
                param = {
                    'reason': reason,
                    'text': text,
                    'returnid': global_info.returnid
                };
                $.get('?r=api/ReturnAskHelp', param, function(data, status) {
                    if (status === 'success') {
                        if (data.Ack === 'Success') {
                            var uploadId = data.Body.returnEbayHelp;
                            (function() {
                                if (uploadId > 0) {
                                    stateBoxFun('show', lang.returndet_biz.deal.waiting);
                                    $.get('?r=api/GetReturnTrueAct', {
                                        'id': uploadId,
                                        'type': 'returnEbayHelp'
                                    }, function(result, return_status) {
                                        removeloading();
                                        if (return_status === 'success' && result.Ack === 'Success') {
                                            if (result.Body == 'success') {
                                                stateBoxFun('show', lang.returndet_biz.deal.synch_suc);
                                                addHistroyResponse(Thours, 'SELLER', 'SELLER_ESCALATE', text);
                                                textOption.val('');
                                                setInterval(function() {
                                                    $('body .stateGB').remove();
                                                }, 2000);

                                            } else {
                                                hintShow('hint_f', lang.returndet_biz.deal.synch_err);
                                            }
                                        } else {
                                            hintShow('hint_f', lang.returndet_biz.deal.synch_err);
                                            stateBoxFun('hide', lang.returndet_biz.deal.synch_err);
                                        }

                                    })
                                } else {
                                    hintShow('hint_f', lang.returndet_biz.deal.synch_err);
                                }

                            })();
                        } else if (data.Error == 'User authentication fails') {
                            hintShow('hint_w', lang.ajaxinfo.permission_denied);
                        } else {
                            hintShow('hint_f', '其他错误，请联系管理员！');
                        }
                    }

                });
                break;
            case 'decline':
                text = textOption.val();
                param = {
                    'text': text,
                    'returnid': global_info.returnid
                };
                $.get('?r=api/DeclineReturn', param, function(data, status) {
                    if (status === 'success') {
                        if (data.Ack === 'Success') {
                            var uploadId = data.Body.returnDecline;
                            (function() {
                                if (uploadId > 0) {
                                    stateBoxFun('show', lang.returndet_biz.deal.waiting);
                                    $.get('?r=api/GetReturnTrueAct', {
                                        'id': uploadId,
                                        'type': 'returnDecline'
                                    }, function(result, return_status) {
                                        removeloading();
                                        if (return_status === 'success' && result.Ack === 'Success') {
                                            if (result.Body == 'success') {
                                                stateBoxFun('show', lang.returndet_biz.deal.synch_suc);
                                                addHistoryResponse(Thours, 'SELLER', 'SELLER_DECLINE_REQUEST', text);
                                                textOption.val('');
                                                setInterval(function() {
                                                    $('body .stateGB').remove();
                                                }, 2000);
                                            } else {
                                                hintShow('hint_f', lang.returndet_biz.deal.synch_err);
                                            }
                                        } else {
                                            hintShow('hint_f', lang.returndet_biz.deal.synch_err);
                                            stateBoxFun('hide', lang.returndet_biz.deal.synch_err);
                                        }

                                    })
                                } else {
                                    hintShow('hint_f', lang.returndet_biz.deal.synch_err);
                                }

                            })();
                        } else if (data.Error == 'User authentication fails') {
                            hintShow('hint_w', lang.ajaxinfo.permission_denied);
                        } else {
                            hintShow('hint_f', '其他错误，请联系管理员！');
                        }
                    }
                });
                break;
            case 'doc': //$todo
                $.post('?r=api/SubmitDocs', {
                    'returnid': global_info.returnid,
                    'imgurl': img
                }, function(data, status) {
                    if (status === 'success') {
                        if (data.Ack === 'Success') {

                        } else if (data.Error == 'User authentication fails') {
                            hintShow('hint_w', lang.ajaxinfo.permission_denied);
                        }
                    }
                });
                break;
            default:
                hintShow('hint_w', lang.returndet_biz.deal.select_deal);
                break;
        }

    });
    /**
     * @desc 获取退货地址
     * @param returnid
     * @author liaojianwen
     * @date 2015-06-30
     */
    function getReturnAddr(returnid) {
        if (typeof(returnid) !== 'undefined' || returnid !== '') {
            var returnid = global_info.returnid;
        }
        $.get('?r=api/GetReturnAddr&returnid=' + returnid, function(data, status) {
            if (status === 'success' && data.Ack === 'Success') {
                var result = data.Body;
                var returnAddr = result.name + ' ' + result.street1 + ' ' + result.street2 + ' ' + result.city + ' ' + result.county + ' ' + result.stateOrProvince +
                    ' ' + result.country + ' ' + result.postalCode + ' ' + result.any
                $('#returnAddr span').eq(0).after('<span>' + returnAddr + '</span>');
                $('#contactMan span').eq(0).after('<span>' + result.name + '</span>');

            }
        })

    }

    /**
     * @desc 金额验证
     * @author linpeiyan
     * @date 2015-04-14
     */
    (function() {
        var partCash = /^[0-9]+(.[0-9]{1,2})?$/;
        var cashs = $('#cash input');
        cashs.on('blur', function() {
            var cost = cashs.val();
            if (isNaN(cost)) {
                $('#cash small').empty();
                $('<small>' + lang.returndet_biz.money_confirm + '</small>').appendTo('#cash').css({
                    'color': 'red'
                });
            } else {
                if (!partCash.test(cost)) {
                    $('#cash small').empty();
                    $('<small>' + lang.returndet_biz.money_confirm + '</small>').appendTo('#cash').css({
                        'color': 'red'
                    });
                } else {
                    var bb = parseFloat(cost.replace(/^0+/, 0));
                    cashs.val(bb); //除去多余的零
                    $('#cash small').css({
                        'color': '#666'
                    });
                }
            }
        });
    }());

    (function() {
        $("#more_btn").on('click', function() {
            $(parent.document).find('.reFull').click();
            $("#more_div").fadeIn();

        });
        $("#CloseMoreWindow").on('click', function() {
            $(parent.document).find('.reFull').click();
            $("#more_div").hide();
        });
    })();


    /**
     * @desc 添加回复内容到历史记录
     * @author liaojianwen
     * @date 2015-04-16
     */
    function addHistroyResponse(responseTime, user_id, activityOption, text) {
        var user_id = getCookie('username');
        if (text !== '') {
            var note = '<i> &lt; ' + activityOption + ' &gt; </i><br />' + text;
        } else {
            var note = '<i> &lt; ' + activityOption + ' &gt; </i><br />';
        }
        $('<li class="sellerMSG">' +
            '<div class="sellerMSG  MSGC">' +
            '<p><b>' + responseTime + '</b>&nbsp;&nbsp;' + user_id + '</p>' +
            '<p style="white-space:normal;">' + note + '</p>' +
            '</div></li>').prependTo("#history");
        $('#text').val('');
    }

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
                            $("#classbox select[pid=" + pid + "]").append('<option value="' + item.tp_list_id + '">' + item.title + '</option>');
                        });
                        $("#classbox select[pid=" + pid + "]").change(function(e) {
                            var fid = $(this).find('option:selected').val();
                            $(this).nextAll().remove();
                            $.each(listdata.Body.list, function(index, item) {
                                if (fid === item.tp_list_id) {
                                    $("#text").val(item.content);
                                }
                            });
                        });
                    }
                }
            });

        }
        $("#classbox").append('<select pid="' + pid + '"><option value="">' + lang.returndet_biz.template_select + '</option></select>');
        for (var i in obj) {
            $("#classbox select[pid=" + pid + "]").append('<option value="' + obj[i].tp_class_id + '">' + obj[i].classname + '</option>');
        }

        $("#classbox select[pid=" + pid + "]").change(function(e) {
            var npid = $(this).find('option:selected').val();
            $(this).nextAll().remove();
            if (npid > 0) {
                getSubClassHtml(npid, data);
            }

        });

    }
    $("#classbox").empty();
    $.get("?r=api/GetTpClassList", function(data, state) {
        if (state == 'success') {
            getSubClassHtml(0, data);

        } else {
            hintShow('hint_f', lang.ajaxinfo.network_error);
        }
    });

    /**
     * @desc 获取return 关联的图片
     * @author liaojianwen
     * @date 2015-10-10
     */
    function getPictureById(return_id) {
        $.get('?r=api/GetPictureById', {
            'id': return_id
        }, function(data, status) {
            if (status === 'success' && data.Ack === 'Success') {
                var M = data.Body;
                var files = '';
                for (var i in M) {
                    files += '<li><a href="' + M[i].path + '" data-lightbox="example-set-6"><img src="' + M[i].resizePath +
                        '" width="70px" height="60px"></a></li>';
                }
                $('#fileData').append(files);
            }
        })
    }
    $('#determine').on('click', function() {
        if ($('#imglistli').find('li').length == 0) {
            hintShow('hint_w', lang.returndet_biz.upload_pic.chose_pic);
            return;
        }

        $('.addImgTC').hide();

        $('.imglist_addwindow').empty();
        for (var i in img) {
            $('<li><i class="icon-close iconBtnS" title="' + lang.returdet_biz.upload_pic.del_pic + '"></i><img src="' + img[i] +
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
            extFilter: 'jpg;jpeg;png;gif;bmp;tif',
            //            maxFiles:5,
            maxFileSize: 1024 * 1024,
            dataType: 'json',
            fileName: 'Filedata',
            onInit: function() {},
            onFallbackMode: function(message) {},
            onNewFile: function(id, file) {},
            onBeforeUpload: function(id) {
                if (img.length >= 10) {
                    hintShow('hint_w', lang.returndet_biz.upload_pic.max_files);
                    $("#drop-area-div").find('input').attr('disabled', 'disabled');
                    return false;
                }
            },
            onComplete: function() {},
            onUploadProgress: function(id, percent) {},
            onUploadSuccess: function(id, data) {
                if (data.Ack == 'Success') {
                    img.push(data.Body.filepath);
                    $('<li><i class="icon-close iconBtnS" title="' + lang.returndet_biz.upload_pic.del_pic + '" data-id="' + id +
                        '"></i><img src="' + data.Body.filepath + '" width="70px" height="60px"></li>').appendTo('.imglist_addwindow');
                } else {
                    hintShow('hint_f', lang.returndet_biz.upload_pic.upload_err);
                    return false;
                }
            },
            onUploadError: function(id, message) {
                hintShow('hint_w', lang.returndet_biz.upload_pic.network_err + message);
            },
            onFileTypeError: function(file) {
                hintShow('hint_w', lang.returndet_biz.upload_pic.type_err);
            },
            onFileSizeError: function(file) {
                hintShow('hint_w', lang.returndet_biz.upload_pic.size_err);
            },
            onFileExtError: function(file) {
                hintShow('hint_w', lang.returndet_biz.upload_pic.file_ext_err);
            },
            onFilesMaxError: function(file) {
                hintShow('hint_w', lang.returndet_biz.upload_pic.file_count_err);
            }
        });
    })();
    // 点击添加附件事件
    $('#addFile').on('click', function() {
        $('.addImgTC').show();
        $('.imglist_addwindow').empty();
        for (var i in img) {
            $('<li><i class="icon-close iconBtnS" title="' + lang.returndet_biz.upload_pic.del_pic + '"></i><img src="' + img[i] +
                '" width="70px" height="60px"></li>').appendTo('.imglist_addwindow');
        }
    });
    // 点击关闭添加文件窗口
    $('#cancelUserPic').on('click', function() {
        $('.addImgTC').hide();
        $('.imglist_addwindow').empty();
        for (var i in img) {
            $('<li><i class="icon-close iconBtnS" title="' + lang.returndet_biz.upload_pic.del_pic + '"></i><img src="' + img[i] +
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

})