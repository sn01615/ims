"use strict";
/**
 * @desc 加载case详细信息，处理case的对话历史、case备注
 * @author lvjianfei
 * @date 2015-4-2
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
        'caseid': $_GET['caseid'] ? $_GET['caseid'] : undefined,
        'type': $_GET['type'] ? $_GET['type'] : undefined,
        'itemId': undefined
    };
    global_info.type = global_info.type.toLowerCase();
    var $window_client_info = $("#window_client_info");
    var verify_sku;
    //根据类别判断调用方法
    if (global_info.type == 'ebp_inr' || global_info.type == 'ebp_snad') {
        getCaseHistory();
    } else if (global_info.type == 'cancel') {
        getCancelDisputeDetail();
        getCancelDisputeMessage();
    } else {
        getCaseDisputeDetail();
        getCaseDisputeMessage();
    }
    //    getCaseHandleLog();
    //    getReturnRequest();

    /**
     * @desc 加载买家发起case详细信息
     * @author lvjianfei
     * @date 2015-04-02
     */
    function getCaseDetail(caseid) {
        if (typeof(caseid) !== 'undefined' || caseid !== '') {
            var global = {
                'caseid': global_info.caseid,
                'type': global_info.type
            };
        }
        $.get("?r=api/GetCaseDetail", global, function(data, status) {
            if (data.Ack == 'Success') {
                var _user_id = data.Body.user_id;
                var result = data.Body.list;
                var prenextID = data.Body.prenextID;
                var seller = result.user_role == 'SELLER' ? result.user_userId : result.otherParty_userId;
                var buyer = result.otherParty_role == 'BUYER' ? result.otherParty_userId : result.user_userId;
                var price = result.caseAmount / result.caseQuantity;
                price = price.toFixed(2);
                var status = $("#casedetail").attr('status');
                $window_client_info.find('span[t="id"]').html(buyer);
                global_info.itemId = result.i_itemId; ///
                //top部分
                if ((result.dRD_content == null || result.dRD_content == '') && (result.dRD_description == null || result.dRD_description == '') && (
                        result.decision == '' || result.decisionDate == 0)) {
                    $('#top').hide();
                } else {
                    var description = result.dRD_description;
                    if (description == null) {
                        description = '';
                    }
                    if (result.decision) {
                        description += ' &nbsp (' + result.decision + ')';
                    }
                    var decison = '<li><span class="leftName">' + lang.disputeDet_biz.diapute_decision + '：</span><span class="rightVal">' +
                        description + '</span></li>';
                    if (result.dRD_content) {
                        decison += '<li><span class="leftName">' + lang.disputeDet_biz.diapute_desc + '：</span><span class="rightVal">' + result.dRD_content +
                            '</span></li>';
                    }
                    if (+result.decisionDate) {
                        decison += '<li><span class="leftName">' + lang.disputeDet_biz.diapute_time + '：</span><span class="rightVal">' +
                            intToLocalDate(result.decisionDate, 3) + '</span></li>';
                    }

                    $(decison).appendTo("#top");
                }
                var content = result.dSI_content ? result.dSI_content === 'unknown' ? '' : result.dSI_content : '';
                if (!content) {
                    if (global_info.type == 'ebp_inr') {
                        content = result.s_EBPINRStatus;
                    } else {
                        content = result.s_EBPSNADStatus;
                    }
                }
                //left部分
                $('<li><span class="leftName">' + lang.disputeDet_biz.account + '：</span><span class="rightVal">' + seller + '</span></li>' +
                    '<li id="CaseId_id"><span class="leftName">' + lang.disputeDet_biz.dispute_id + '：</span><span class="rightVal">' + result.caseId_id +
                    '</span></li>' +
                    '<li><span class="leftName">' + lang.disputeDet_biz.dispute_start_time + '：</span><span class="rightVal">' + intToLocalDate(
                        result.creationDate, 3) + '</span></li>' +
                    '<li><span class="leftName">' + lang.disputeDet_biz.dispute_staus + '：</span><span class="rightVal">' + ucfirst(content) +
                    '</span></li>' +
                    '<li><span class="leftName">' + lang.disputeDet_biz.dispute_reason + '：</span><span class="rightVal">' + result.openReason +
                    '</span></li>' +
                    '<li><span class="leftName">' + lang.disputeDet_biz.buyer_description + '：</span><span class="rightVal">' + result.iBED_description +
                    '</span></li>').appendTo("#left");
                //right部分
                $('<li><span class="leftName">BuyerUserID：</span><span class="rightVal"><a class="fontLinkBtn" href="http://www.ebay.com/usr/' + buyer +
                    '" target="_blank">' + buyer + '</a></span></li>' +
                    //                      '<li><span>消费：</span><span>暂时没数据</span></li>'+
                    '<li class="custaddr" style="display:none"></li>' +
                    '<li class="customerNote" style="display:none"></li>').appendTo("#right");
                //订单信息部分
                $('<table class="table">' +
                    '<colgroup>' +
                    '<col width="70"/>' +
                    '<col />' +
                    '<col />' +
                    '<col width="120"/>' +
                    '<col width="100"/>' +
                    '<col width="120"/>' +
                    '<col />' +
                    '<col width="150"/>' +
                    ((result.sS_carrierUsed.length > 1) ? '<col />' : '') +
                    '<col width="150"/>' +
                    '</colgroup>' +
                    '<thead><tr>' +
                    '<th>' + lang.disputeDet_biz.order_info.picture + '</th>' +
                    '<th>' + lang.disputeDet_biz.order_info.SKU + '</th>' +
                    '<th>' + lang.disputeDet_biz.order_info.itemid + '</th>' +
                    '<th>' + lang.disputeDet_biz.order_info.price + '</th>' +
                    '<th>' + lang.disputeDet_biz.order_info.quantity + '</th>' +
                    '<th>' + lang.disputeDet_biz.order_info.amount + '</th>' +
                    ((result.i_transactionId.length > 0) ? '<th>TransactionID</th>' : '') +
                    '<th>' + lang.disputeDet_biz.order_info.transaction_time + '</th>' +
                    ((result.sS_carrierUsed.length > 1) ? '<th>' + lang.disputeDet_biz.order_info.carrier + '</th>' : '') +
                    ((result.sS_trackingNumber.length > 1) ? '<th>' + lang.disputeDet_biz.order_info.tracking_no + '</th>' : '') +
                    ((result.sS_deliveryDate.length > 1) ? '<th>' + lang.disputeDet_biz.order_info.ship_time + '</th>' : '') +
                    '<th>' + lang.disputeDet_biz.order_info.feedback + '</th>' +
                    '</tr></thead>' +
                    '<tbody><tr data-itemid="' + result.i_itemId + '">' +
                    '<td><img src="" alt="" /></td>' +
                    '<td><span></span></td>' +
                    '<td id="i_itemId"><span><a href="http://cgi.ebay.com/ws/eBayISAPI.dll?ViewItem&item=' + (_user_id ? result.i_itemId.replace(
                        /(\d{8})\d{4}/, '$1****') : result.i_itemId) + '" target="_blank" class="fontLinkBtn">' + (_user_id ? result.i_itemId.replace(
                        /(\d{8})\d{4}/, '$1****') : result.i_itemId) + '</a></span></td>' +
                    '<td id="Price"><span>' + price + '&nbsp' + result.currencyId + '</span></td>' +
                    '<td id="Quantity"><span>' + result.caseQuantity + '</span></td>' +
                    '<td id="totalPrice"><span>' + result.caseAmount + '&nbsp' + result.currencyId + '</span></td>' +
                    ((result.i_transactionId.length > 0) ? ('<td id="totalPrice"><span>' + result.i_transactionId + '</span></td>') : '') +
                    '<td id="transactionDate"><span>' + intToLocalDate(result.i_transactionDate, 3) + '</span></td>' +
                    ((result.sS_carrierUsed.length > 1) ? ('<td id="sS_carrierUsed"><span>' + result.sS_carrierUsed + '</span></td>') : '') +
                    ((result.sS_trackingNumber.length > 1) ? ('<td id="sS_trackingNumber"><span>' + result.sS_trackingNumber + '</span></td>') : '') +
                    ((result.sS_deliveryDate.length > 1) ? ('<td><span>' + (result.sS_deliveryDate !== "0" ? intToLocalDate(result.sS_deliveryDate,
                        3) : "") + '<span></td>') : '') +
                    '<td class="evaluate"><i class="iconNone"></i></td>' +
                    '</tr></tbody>' +
                    '</table>').appendTo("#orderinfo");

                $('<span>' + result.currencyId + '</span>').appendTo('#cash');
                //                    $("#history").find('i').each(function(index, element) {
                //                        if($(element).html().toString().indexOf('Seller provided tracking information for shipment.')>-1){
                //                            $(element).after('<br/><i>trackingNumber: <a href="http://www.ec-firstclass.org/?track_number='
                //                            +result.sS_trackingNumber+'" target="_blank">'
                //                            +result.sS_trackingNumber+'</a></i>');
                //                            if(typeof result.sS_carrierUsed!=='undefined'&&result.sS_carrierUsed!=''){
                //                                $(element).after('<br/><i>carrierUsed: '+result.sS_carrierUsed+'</i>');
                //                            }
                //                        }
                //                    });

                //获取客户留言
                getEbayOrderNote(global_info.caseid);
                // 由itemid获取item信息
                setTimeout(function() {
                    getItemInfo(global_info.caseid, result.i_itemId)
                }, 1000);
                //获取评价信息
                getFeedbackInfo(global_info.caseid);
                //获取客户more info
                getMoreUserInfo(buyer);
                //获取客户关联的交易
                getEbayTransactionsByUserId(buyer);
                //获取客户关联的msg
                getMsgByUserID(buyer);
                //获取客户好评数
                getPositived(buyer);
                // Case处理相关
                // @date 2015-04-13
                // 未收到货
                getReturnRequest();
                var options_list = '';
                var reaTexts = '<option value="" selected>select a reason</option>';
                if (result.caseId_type === 'EBP_INR') {
                    $('#case_dispose_select').find('option').remove();
                    options_list = '<option value="msg" selected>Send a message to the buyer</option>';
                    options_list += '<option value="trackingInfo" >Add tracking number</option>';
                    options_list += '<option value="shippingInfo" >Add shipping infomation</option>';
                    options_list += '<option value="refund" >Fully refund the buyer</option>';
                    options_list += '<option value="ebayHelp" >Ask ebay to step in</option>';
                    $('#case_dispose_select').append(options_list);
                    $('#model').show();
                    $('#text').show();
                    reaTexts += '<option value="BUYER_STILL_UNHAPPY_AFTER_REFUND">I refunded the buyer,but the buyer isn\'t satisfied</option>';
                    reaTexts += '<option value="ITEM_SHIPPED_WITH_TRACKING">I posted the item with tracking details</option>';
                    reaTexts += '<option value="TROUBLE_COMMUNICATION_WITH_BUYER">I\'m having trouble communicating with the buyer</option>';
                    reaTexts += '<option value="OTHER">Other</option>';
                }
                // 描述不符合
                if (result.caseId_type === 'EBP_SNAD') {
                    $('#case_dispose_select').find('option').remove();
                    options_list += '<option value="msg" selected>Send a message to the buyer</option>';
                    options_list += '<option value="fullRefund">Fully refund the buyer</option>';
                    options_list += '<option value="partRefund">Issue a partial refund</option>';
                    options_list += '<option value="returnRefund">Return the item for full refund</option>';
                    options_list += '<option value="ebayHelp" >Ask ebay to step in</option>';
                    $('#case_dispose_select').append(options_list);
                    $('#model').show();
                    $('#text').show();
                    reaTexts += '<option value="BUYER_STILL_UNHAPPY_AFTER_REFUND">I refunded the buyer,but the buyer isn\'t satisfied</option>';
                    reaTexts += '<option value="TROUBLE_COMMUNICATION_WITH_BUYER">I\'m having trouble communicating with the buyer</option>';
                    reaTexts += '<option value="OTHER">Other</option>';
                }
                // 取消订单
                if (result.caseId_type === 'CANCEL_TRANSACTION') {

                }
                var options = $('#case_dispose_select').val();
                var ebayhelp = '<p id="reasons"><span class="spanMode span100">' + lang.disputeDet_biz.text_reason +
                    '：</span><select name="" id="reasonList">' +
                    reaTexts + '</select></p>';
                var appealEbay = '<p id="appealCase"><span class="spanMode span100">' + lang.disputeDet_biz.text_reason +
                    '：</span><select name="" id="appealList">' +
                    '<option value="" selected> select a reason </option>' +
                    '<option value="DISAGREE_WITH_FINAL_DECISION"> DISAGREE_WITH_FINAL_DECISION </option>' +
                    '<option value="NEW_INFORMATION"> NEW_INFORMATION </option>' +
                    '<option value="OTHER"> OTHER </option>' +
                    '</select></p>';
                var appealStatus = '';
                if (result.caseId_type === 'EBP_SNAD') {
                    appealStatus = result.s_EBPSNADStatus;
                } else {
                    appealStatus = result.s_EBPINRStatus;
                }
                if (appealStatus == 'CASE_CLOSED_CS_RESPONDED' || appealStatus == 'CLOSED' || appealStatus == 'CS_CLOSED' || appealStatus == 'EXPIRED' ||
                    appealStatus == 'PAID' || appealStatus == 'YOU_CONTACTED_CS_ABOUT_CLOSED_CASE') {
                    $('<option value="appealEbay">appeal to ebay for help</option>').appendTo('#case_dispose_select');
                }
                //国家列表
                var returnAddr;
                if (result.caseId_type === 'EBP_SNAD') {
                    (function() {
                        setTimeout(function() {
                            var countryOptions = '<select name="country" id="country" style="width:160px;">';
                            $.get('?r=api/GetCountryList', function(data, status) {
                                if (status === "success") {
                                    var M = data.Body;
                                    if (M) {
                                        for (var i in M) {
                                            countryOptions += '<option value="' + i + '"' + (i === "US" ? 'selected ="selected"' :
                                                '') + '>' + M[i] + '</option>';
                                        }
                                    }
                                    countryOptions += '</select>';
                                    ///退货地址只显示在描述不符部分
                                    returnAddr = '<p class="returnAddr"> <span class="spanMode span100">' + lang.disputeDet_biz.address
                                        .country + '：</span>' + countryOptions + '</p>' +
                                        '<p class="returnAddr" id="state"> <span class="spanMode span100">' + lang.disputeDet_biz.address
                                        .state + '：</span><input type="text" name=""  /></p>' +
                                        '<p class="returnAddr" id="city"> <span class="spanMode span100">' + lang.disputeDet_biz.address
                                        .city + '：</span><input type="text" name="" id="" /></p>' +
                                        '<p class="returnAddr" id="street"> <span class="spanMode span100">' + lang.disputeDet_biz.address
                                        .street1 + '：</span><input type="text" name="" id="" /></p>' +
                                        '<p class="returnAddr" id="street2"> <span class="spanMode span100">' + lang.disputeDet_biz
                                        .address.street2 + '：</span><input type="text" name="" id="" /></p>' +
                                        '<p class="returnAddr" id="postcode"> <span class="spanMode span100">' + lang.disputeDet_biz
                                        .address.postcode + '：</span><input type="text" name="" id="" /></p>' +
                                        '<p class="returnAddr" id="name"> <span class="spanMode span100">' + lang.disputeDet_biz.address
                                        .contacts + '：</span><input type="text" name="" id="" /></p>' +
                                        '<p class="returnAddr" id="code"> <span class="spanMode span100">' + lang.disputeDet_biz.address
                                        .RMA + '：</span><input type="text" name="" id="" /></p>';
                                    var ret = countryOptions.replace('id="country"', 'id="country2"');
                                    $(ret).appendTo('#returnCountry');
                                }
                            });
                        }, 1000);
                    })();
                }

                //选项切换
                $('#case_dispose_select').on('change', function() {
                    options = $('#case_dispose_select').val();
                    switch (options) {
                        case 'msg':
                        case 'refund':
                            $('#carrier').hide();
                            $('#trackingNO').hide();
                            $('#shipdate').hide();
                            $('#cash').hide();
                            $('#reasons').remove();
                            $('.returnAddr').remove();
                            $('#returnaddr').hide();
                            $('#appealCase').remove();
                            break;
                        case 'trackingInfo':
                            $('#carrier').show();
                            $('#trackingNO').show();
                            $('#shipdate').hide();
                            $('#reasons').remove();
                            $('#returnaddr').hide();
                            $('#appealCase').remove();
                            break;
                        case 'shippingInfo':
                            $('#carrier').show();
                            $('#trackingNO').hide();
                            $('#shipdate').show();
                            $('#reasons').remove();
                            $('#returnaddr').hide();
                            $('#appealCase').remove();
                            break;
                        case 'fullRefund':
                            $('#cash').hide();
                            $('#reasons').remove();
                            $('.returnAddr').remove();
                            $('#returnaddr').hide();
                            $('#appealCase').remove();
                            break;
                        case 'returnRefund':
                            $('#cash').hide();
                            $('#reasons').remove();
                            $('#cash').after(returnAddr);
                            $('#returnaddr').hide();
                            $('#appealCase').remove();
                            //验证退货地址
                            columnsBlur($('#state input'), lang.disputeDet_biz.input_info.state);
                            columnsBlur($('#city input'), lang.disputeDet_biz.input_info.city);
                            columnsBlur($('#name input'), lang.disputeDet_biz.input_info.contacts);
                            columnsBlur($('#postcode input'), lang.disputeDet_biz.input_info.postcode);
                            columnsBlur($('#street input'), lang.disputeDet_biz.input_info.street1);
                            columnsBlur($('#code input'), lang.disputeDet_biz.input_info.RMA);
                            break;
                        case 'partRefund':
                            $('#cash').show();
                            $('#reasons').remove();
                            $('.returnAddr').remove();
                            $('#returnaddr').hide();
                            $('#appealCase').remove();
                            break;
                        case 'ebayHelp':
                            $('#carrier').hide();
                            $('#trackingNO').hide();
                            $('#shipdate').hide();
                            $('#cash').hide();
                            $('#cash').after(ebayhelp);
                            $('.returnAddr').remove();
                            $('#returnaddr').hide();
                            $('#appealCase').remove();
                            break;
                        case 'provideReturnInfo':
                            $('#carrier').hide();
                            $('#trackingNO').hide();
                            $('#shipdate').hide();
                            $('#cash').hide();
                            $('.returnAddr').remove();
                            $('#reasons').remove();
                            $('#returnaddr').show();
                            $('#appealCase').remove();
                            break;
                        case 'appealEbay':
                            $('#carrier').hide();
                            $('#trackingNO').hide();
                            $('#shipdate').hide();
                            $('#cash').hide();
                            $('#reasons').remove();
                            $('.returnAddr').remove();
                            $('#returnaddr').hide();
                            $('#cash').after(appealEbay);
                            break;

                    }
                    if (options === 'provideReturnInfo') {
                        $('#model').hide();
                        $('#text').hide();
                        $('#newAddr').show();
                    } else {
                        $('#model').show();
                        $('#text').show();
                        $('#newAddr').hide();
                    }
                });
                $('#returnaddr').on('click', function() {
                    $('#editAddr').show();
                })
                var retName = $('#returnName');
                var retStreet = $('#returnStreet1');
                var retStreet2 = $('#returnStreet2');
                var retCity = $('#returnCity');
                var retState = $('#returnState');
                var retZip = $('#returnZip');
                var retRMA = $('#returnRMA');
                //点击关闭添加case窗口按钮
                $('#close_btn').on('click', function() {
                    $('#editAddr').hide();
                });

                $('#saveAddr').on('click', function() {
                        var returnName = retName.val();
                        var returnStreet = retStreet.val();
                        var returnStreet2 = retStreet2.val();
                        var returnCity = retCity.val();
                        var returnState = retState.val();
                        var returnCountry = $('#country2').val();
                        var returnZip = retZip.val();
                        var returnRMA = retRMA.val()
                        if (returnState == lang.disputeDet_biz.input_info.state || returnState == '' || returnCity == lang.disputeDet_biz.input_info
                            .city || returnCity == '' || returnStreet == lang.disputeDet_biz.input_info.city || returnStreet == '' ||
                            returnZip == '' || returnZip == lang.disputeDet_biz.input_info.postcode || returnStreet == '' || returnStreet == lang.disputeDet_biz
                            .input_info.street1) {
                            hintShow('hint_w', lang.disputeDet_biz.cannot_empty);
                            return;
                        } else {
                            var Addr = returnName + ' ' + returnStreet + ' ' + returnStreet2 + ' ' + returnCity + ' ' + returnState + ' ' +
                                returnCountry + ' ' + returnZip
                            $('#newAddr').show().html('<p id="address"> <span class="spanMode span100">' + lang.disputeDet_biz.return_address +
                                '：</span><span>' + Addr + '</span></p><p id="RMA"> <span class="spanMode span100">RMA：</span><span>' +
                                returnRMA + '</span></p>');
                            $('#editAddr').hide();
                        }

                    })
                    ////提交
                $('#deal').on('click', function() {
                    var fileText = $('#text').val();
                    var myDate = new Date();
                    var Thours = myDate.getFullYear() + '-' + (myDate.getMonth() + 1) + '-' + myDate.getDate() + ' ' + myDate.getHours() + ':' +
                        myDate.getMinutes() + ':' + myDate.getSeconds();
                    var param = '';
                    var carrier = '';
                    var TrackingInfo = [];
                    switch (options) {
                        case 'msg':
                            if (fileText == '') {
                                hintShow('hint_w', lang.disputeDet_biz.cannot_empty);
                                return;
                            }
                            param = {
                                'text': fileText,
                                'type': result.caseId_type,
                                'case_id': global.caseid,
                                'caseId_id': result.caseId_id
                            };
                            $.get('?r=api/AddResponse', param, function(data, status) {
                                if (status == 'success' && data.Ack == 'Success') {
                                    hintShow('hint_s', lang.disputeDet_biz.action_success);
                                    addHistroyResponse(Thours, '', fileText, 'seller offered another solution');
                                }
                            });
                            break;
                        case 'trackingInfo':
                            var carrier = $('#carrier input').val();
                            var trackingNum = $('#trackingNO input').val();
                            if (carrier == lang.disputeDet_biz.input_carrier || carrier == '' || trackingNum == lang.disputeDet_biz.input_tracking_number ||
                                trackingNum == '') {
                                hintShow('hint_w', lang.disputeDet_biz.cannot_empty);
                                return;
                            }
                            if (fileText.length > 100) {
                                hintShow('hint_f', '内容长度不得超过100！');
                                return;
                            }
                            param = {
                                'carrier': carrier,
                                'num': trackingNum,
                                'text': fileText,
                                'type': result.caseId_type,
                                'case_id': global.caseid,
                                'caseId_id': result.caseId_id
                            };
                            $.get('?r=api/AddTrackingInfo', param, function(data, status) {
                                if (status == 'success' && data.Ack == 'Success') {
                                    hintShow('hint_s', lang.disputeDet_biz.action_success);
                                    TrackingInfo['trackingNum'] = trackingNum;
                                    TrackingInfo['carrier'] = carrier;
                                    $('#carrier input').val('');
                                    $('#trackingNO input').val('');
                                    addHistroyResponse(Thours, TrackingInfo, fileText,
                                        'Seller provided tracking information for shipment.');
                                }

                            });
                            break;
                        case 'shippingInfo':
                            var carrier = $('#carrier input').val();
                            var shipdate = $('#shipdate input').val();
                            if (carrier == lang.disputeDet_biz.input_carrier || carrier == '' || shipdate == lang.disputeDet_biz.input_shipment_date ||
                                shipdate == '') {
                                hintShow('hint_w', lang.disputeDet_biz.cannot_empty);
                                return;
                            }
                            param = {
                                'carrier': carrier,
                                'shipdate': get_unix_time(shipdate),
                                'text': fileText,
                                'type': result.caseId_type,
                                'case_id': global.caseid,
                                'caseId_id': result.caseId_id
                            };
                            $.get('?r=api/AddShippingInfo', param, function(data, status) {
                                if (status == 'success' && data.Ack == 'Success') {
                                    hintShow('hint_s', lang.disputeDet_biz.action_success);
                                    TrackingInfo['shipdate'] = shipdate;
                                    TrackingInfo['carrier'] = carrier;
                                    $('#carrier input').val('');
                                    $('#shipdate input').val('');
                                    addHistroyResponse(Thours, TrackingInfo, fileText, 'Seller provided shipping information.');
                                }
                            });
                            break;
                        case 'refund':
                        case 'fullRefund':
                            param = {
                                'type': result.caseId_type,
                                'case_id': global.caseid,
                                'text': fileText,
                                'caseId_id': result.caseId_id
                            }
                            $.get('?r=api/FullRefund', param, function(data, status) {
                                if (status == 'success' && data.Ack == 'Success') {
                                    hintShow('hint_s', lang.disputeDet_biz.action_success);
                                    addHistroyResponse(Thours, '', fileText, 'Seller issued full refund to buyer.');
                                }
                            });
                            break;
                        case 'partRefund':
                            var amount = $('#cash input').val();
                            if (amount == '' || amount == lang.disputeDet_biz.input_amount) {
                                hintShow('hint_w', lang.disputeDet_biz.action_success);
                                return;
                            }
                            param = {
                                'type': result.caseId_type,
                                'case_id': global.caseid,
                                'amount': amount,
                                'text': fileText,
                                'caseId_id': result.caseId_id
                            };
                            $.get('?r=api/PartialRefund', param, function(data, status) {
                                if (status == 'success' && data.Ack == 'Success') {
                                    hintShow('hint_s', lang.disputeDet_biz.action_success);
                                    $('cash input').val('');
                                    addHistroyResponse(Thours, '', fileText, 'Seller issued partial refund to buyer.');
                                }

                            });
                            break;
                        case 'returnRefund':
                            var country = $('#country').val();
                            var state = $('#state input').val();
                            var city = $('#city input').val();
                            var name = $('#name input').val();
                            var postcode = $('#postcode input').val();
                            var street = $('#street input').val();
                            var street2 = $('#street2 input').val();
                            var code = $('#code input').val();
                            param = {
                                'type': result.caseId_type,
                                'case_id': global.caseid,
                                'text': fileText,
                                'caseId_id': result.caseId_id,
                                'country': country,
                                'state': state,
                                'city': city,
                                'street': street,
                                'street2': street2,
                                'name': name,
                                'postcode': postcode,
                                'code': code
                            };
                            $.get('?r=api/ReturnItemRefund', param, function(data, status) {
                                if (status == 'success' && data.Ack == 'Success') {
                                    hintShow('hint_s', lang.disputeDet_biz.action_success);
                                    addHistroyResponse(Thours, '', fileText, 'Seller provide return address and issued a refund.');
                                }
                            });
                            break;
                        case 'ebayHelp':
                            var cause = $('#reasonList').val();
                            if (cause == '') {
                                hintShow('hint_w', lang.disputeDet_biz.reason_canot_empty);
                                return;
                            }
                            param = {
                                'type': result.caseId_type,
                                'case_id': global.caseid,
                                'text': fileText,
                                'reason': cause,
                                'caseId_id': result.caseId_id
                            };
                            $.get('?r=api/EbayHelp', param, function(data, status) {
                                if (status == 'success' && data.Ack == 'Success') {
                                    hintShow('hint_s', lang.disputeDet_biz.action_success);
                                    $('#reasonList').val('');
                                    addHistroyResponse(Thours, '', fileText, 'Seller ask ebay help.');
                                }
                            });
                            break;
                        case 'provideReturnInfo':
                            var returnName = $('#returnName').val();
                            var returnStreet = $('#returnStreet1').val();
                            var returnStreet2 = $('#returnStreet2').val();
                            var returnCity = $('#returnCity').val();
                            var returnState = $('#returnState').val();
                            var returnCountry = $('#country2').val();
                            var returnZip = $('#returnZip').val();
                            var returnRMA = $('#returnRMA').val()
                            if (returnState == lang.disputeDet_biz.input_info.state || returnState == '' || returnCity == lang.disputeDet_biz.input_info
                                .city || returnCity == '' ||
                                returnZip == '' || returnZip == lang.disputeDet_biz.input_info.postcode || returnStreet == '' || returnStreet ==
                                lang.disputeDet_biz.input_info.street1) {
                                hintShow('hint_w', lang.disputeDet_biz.reason_canot_empty);
                                return;
                            }
                            param = {
                                'type': result.caseId_type,
                                'case_id': global.caseid,
                                'name': returnName,
                                'street1': returnStreet,
                                'street2': returnStreet2,
                                'city': returnCity,
                                'state': returnState,
                                'country': returnCountry,
                                'ZIP': returnZip,
                                'RMA': returnRMA,
                                'caseId_id': result.caseId_id
                            };
                            $.get('?r=api/ProvideReturnInfo', param, function(data, status) {
                                if (status === 'success' && data.Ack === 'Success') {
                                    hintShow('hint_s', lang.disputeDet_biz.action_success);
                                    retName.val('');
                                    retStreet.val('');
                                    retStreet2.val('');
                                    retCity.val('');
                                    retState.val('');
                                    retZip.val('');
                                    retRMA.val('')
                                    $('#newAddr').show().html('');
                                    addHistroyResponse(Thours, '', '', 'Seller provide return adderess.');

                                }
                            });
                            break;
                        case 'appealEbay':
                            var appealVal = $('#appealList').val();
                            if (appealVal == '') {
                                hintShow('hint_w', lang.disputeDet_biz.reason_canot_empty);
                                return;
                            }
                            param = {
                                'type': result.caseId_type,
                                'case_id': global.caseid,
                                'appeal': appealVal,
                                'text': fileText,
                                'caseId_id': result.caseId_id
                            };
                            $.get('?r=api/AppealEbay', param, function(data, status) {
                                if (status === 'success' && data.Ack === 'Success') {
                                    hintShow('hint_s', lang.disputeDet_biz.action_success);
                                    $('#appealList').val('');
                                    addHistroyResponse(Thours, '', fileText, 'Seller appeal for help.');
                                }
                            });
                            break;
                    }
                });


                /**
                 * @desc 金额验证
                 * @author linpeiyan
                 * @date 2015-04-14
                 */
                (function() {
                    var partCash = /^[0-9]+(.[0-9]{1,2})?$/;
                    var cashs = $('#cash input');
                    cashs.blur(function() {
                        var cost = cashs.val();
                        if (isNaN(cost)) {
                            $('#cash small').empty();
                            $('<small>' + lang.disputeDet_biz.input_amount2 + '</small>').appendTo('#cash').css({
                                'color': 'red'
                            });
                        } else {
                            if (!partCash.test(cost)) {
                                $('#cash small').empty();
                                $('<small>' + lang.disputeDet_biz.input_amount2 + '</small>').appendTo('#cash').css({
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


                //验证承运商
                columnsBlur($('#carrier input'), lang.disputeDet_biz.input_carrier);
                //物流跟踪号验证
                columnsBlur($('#trackingNO input'), lang.disputeDet_biz.input_tracking_number);
                ///发货时间验证
                columnsBlur($('#shipdate input'), lang.disputeDet_biz.input_shipment_date);

                ///验证临时退货地址
                columnsBlur($('#returnName'), lang.disputeDet_biz.input_info.contacts);
                columnsBlur($('#returnStreet1'), lang.disputeDet_biz.input_info.street1);
                columnsBlur($('#returnCity'), lang.disputeDet_biz.input_info.city2);
                columnsBlur($('#returnState'), lang.disputeDet_biz.input_info.state);
                columnsBlur($('#returnCountry'), lang.disputeDet_biz.input_info.country);
                columnsBlur($('#returnZip'), lang.disputeDet_biz.input_info.postcode);



                //上下翻页事件
                if (typeof(prenextID.preID) === 'undefined' || prenextID.preID === '') {

                    $("#pre").attr('title', lang.disputeDet_biz.not_on_letter);
                    $("#pre").addClass('notOpBtn');
                    $("#pre").removeAttr('id');
                }
                if (typeof(prenextID.nextID) === 'undefined' || prenextID.nextID === '') {
                    $("#next").attr('title', lang.disputeDet_biz.not_on_under);
                    $("#next").addClass('notOpBtn');
                    $("#next").removeAttr('id');
                }
                $("#pre").on("click", function() {
                    location.href = "?r=Home/DisputeDetail&caseid=" + prenextID.preID + "&type=" + global_info.type;
                });
                $("#next").on("click", function() {
                    location.href = "?r=Home/DisputeDetail&caseid=" + prenextID.nextID + "&type=" + global_info.type;
                })
                getItemNoteList();

            } else {
                //top部分
                $('<li><span>' + lange.disputeDet_biz.not_msg + '</span></li>').appendTo("#top");
                //left部分
                $('<li><span>' + lange.disputeDet_biz.not_msg + '</span></li>').appendTo("#left");
                //right部分
                $('<li><span>' + lange.disputeDet_biz.not_msg + '</span></li>').appendTo("#right");
                //订单部分
                $('<li><span>' + lange.disputeDet_biz.not_msg + '</span></li>').appendTo("#order");
            }
        })
    }

    /**
     * @desc 字段验证函数
     * @author liaojianwen
     * @date 2015-04-16
     */
    function columnsBlur(obj, strText) {
        obj.blur(function() {
            if (!obj.val() || typeof(obj.val()) === "undefined") {
                obj.addClass('redBorder').val(strText).css({
                    'color': 'red',
                    'font-size': '12px'
                });
            } else {
                obj.removeClass('redBorder').css({
                    'color': '#666'
                });
            }
        });
        obj.focus(function() {
            if (obj.val() === strText) {
                obj.val('');
            }
        });
        if (obj[0].id === "dateSel") {
            $('.datetimepicker-days .table-condensed').on('click', $('.datetimepicker-days .table-condensed td'), function() {
                obj.removeClass('redBorder').css({
                    'color': '#666'
                });
            })
        }
    }

    /**
     * @desc 添加回复内容到历史记录
     * @author liaojianwen
     * @date 2015-04-16
     */
    function addHistroyResponse(responseTime, TrackingInfo, text, activity) {
        var user_id = getCookie('username');
        var expression = '<li class="sellerMSG">' +
            '<div class="sellerMSG  MSGC">' +
            '<p><b>' + responseTime + '</b>&nbsp;&nbsp;' + user_id + '</p>';
        if (activity === 'Seller provided tracking information for shipment.') {
            expression += '<p><i> &lt; ' + activity + ' &gt; </i><br /></p>' +
                '<i> CarrierUsed: ' + TrackingInfo['carrier'] + '</i><br />' +
                '<i> trackingNumber: ' + TrackingInfo['trackingNum'] + '</i><br />' +
                '<p style="white-space:normal;">' + text + '</p>' +
                '</div></li>';
        } else if (activity === 'Seller provided shipping information.') {
            expression += '<p><i> &lt; ' + activity + ' &gt; </i><br /></p>' +
                '<i> CarrierUsed: ' + TrackingInfo['carrier'] + '</i><br />' +
                '<i> ShipDate: ' + TrackingInfo['shipdate'] + '</i><br />' +
                '<p style="white-space:normal;">' + text + '</p>' +
                '</div></li>';
        } else {
            expression += '<p><i> &lt; ' + activity + ' &gt; </i><br /></p>' +
                '<p style="white-space:normal;">' + text + '</p>' +
                '</div></li>';
        }
        $(expression).prependTo("#history");
        $('#text').val('');
    }

    /**
     * @desc 加载卖家发起case详细信息
     * @author lvjianfei
     * @date 2015-04-02
     */
    function getCaseDisputeDetail(caseid) {
        if (typeof(caseid) !== 'undefined' || caseid !== '') {
            var global = {
                'caseid': global_info.caseid,
                'type': global_info.type
            };
        }
        $.get("?r=api/GetCaseDisputeDetail", global, function(data, status) {
            if (data.Ack == 'Success') {
                var _user_id = data.Body.user_id;
                var result = data.Body.list;
                var prenextID = data.Body.prenextID;
                var price = result.i_SS_CurrentPrice / result.i_Quantity;
                var status = $("#casedetail").attr('status');
                global_info.itemId = result.i_ItemID; ///
                //top部分//暂时先隐藏,后面有需求调整
                //                    $('<li><span>纠纷判决：</span><span>'+result.DisputeStatus+'</span></li>'
                //                    +'<li><span>判决描述：</span><span>'+result.DisputeExplanation+'</span></li>'
                //                    +'<li><span>判决时间：</span><span>'+intToLocalDate(result.DisputeModifiedTime)+'</span></li>').appendTo("#top");
                $('#top').hide();
                //left部分
                $('<li><span>' + lang.disputeDet_biz.account + '：</span><span>' + result.SellerUserID + '</span></li>' +
                    '<li><span>' + lang.disputeDet_biz.dispute_id + '：</span><span>' + result.DisputeID + '</span></li>' +
                    '<li><span>' + lang.disputeDet_biz.dispute_start_time + '：</span><span>' + intToLocalDate(result.DisputeCreatedTime, 3) +
                    '</span></li>' +
                    '<li><span>' + lang.disputeDet_biz.dispute_staus + '：</span><span>' + result.DisputeState + '</span></li>' +
                    '<li><span>' + lang.disputeDet_biz.dispute_reason + '：</span><span>' + result.DisputeReason + '</span></li>').appendTo("#left");
                //right部分
                $('<li><span class="leftName">BuyerUserID：</span><span>' + result.BuyerUserID + '</span></li>' +
                    //                      '<li><span>消费：</span><span>暂时没数据</span></li>'+
                    '<li class="custaddr" style="display:none"></li>' +
                    '<li class="customerNote"></li>').appendTo("#right");
                //订单信息部分
                $('<table class="table">' +
                    '<thead><tr>' +
                    '<th>' + lang.disputeDet_biz.order_info.picture + '</th>' +
                    '<th>' + lang.disputeDet_biz.order_info.SKU + '</th>' +
                    '<th>' + lang.disputeDet_biz.order_info.itemid + '</th>' +
                    '<th>' + lang.disputeDet_biz.order_info.price + '</th>' +
                    '<th>' + lang.disputeDet_biz.order_info.quantity + '</th>' +
                    (global.type == 'cancel' ? '<th>' + lang.disputeDet_biz.order_info.feedback2 + '</th>' : '') +
                    '</tr></thead>' +
                    '<tbody><tr data-itemid="' + result.i_ItemID + '">' +
                    '<td><img src="" alt="" /></td>' +
                    '<td><span></span></td>' +
                    '<td><a href="http://cgi.ebay.com/ws/eBayISAPI.dll?ViewItem&item=' + (_user_id ? result.i_ItemID.replace(/(\d{8})\d{4}/,
                        '$1****') : result.i_ItemID) + '" target="_blank" class="fontLinkBtn">' + (_user_id ? result.i_ItemID.replace(
                        /(\d{8})\d{4}/, '$1****') : result.i_ItemID) + '</a></td>' +
                    '<td>' + result.i_SS_CurrentPrice + '&nbsp' + result.i_SS_CP_currencyID + '</td>' +
                    '<td>' + result.i_Quantity + '</td>' +
                    (global.type == 'cancel' ? '<td class="evaluate"><i class="iconNone"></i></td>' : '') +
                    '</tr></tbody>' +
                    '</table>').appendTo("#orderinfo");
                //获取客户留言
                getEbayOrderNote(global_info.caseid);
                //获取产品信息

                setTimeout(function() {
                    getItemInfo(global_info.caseid, result.i_ItemID)
                }, 1000);
                //获取评价信息
                getFeedbackInfo(global_info.caseid);

                //获取客户more info
                getMoreUserInfo(result.BuyerUserID);
                //获取客户关联的交易
                getEbayTransactionsByUserId(result.BuyerUserID);
                //获取客户关联的msg
                getMsgByUserID(result.BuyerUserID);
                //获取评价数
                getPositived(result.BuyerUserID);

                //上下翻页事件
                if (typeof(prenextID.preID) === 'undefined' || prenextID.preID === '') {
                    $("#pre").attr('title', lang.disputeDet_biz.not_on_letter);
                    $("#pre").addClass('notOpBtn');
                    $("#pre").removeAttr('id');
                }
                if (typeof(prenextID.nextID) === 'undefined' || prenextID.nextID === '') {
                    $("#next").attr('title', lang.disputeDet_biz.not_on_under);
                    $("#next").addClass('notOpBtn');
                    $("#next").removeAttr('id');
                }
                $("#pre").on("click", function() {
                    location.href = "?r=Home/DisputeDetail&caseid=" + prenextID.preID + "&type=" + global_info.type;
                });
                $("#next").on("click", function() {
                    location.href = "?r=Home/DisputeDetail&caseid=" + prenextID.nextID + "&type=" + global_info.type;
                })
                getItemNoteList();

            } else {
                //top部分
                $('<li><span>' + lang.disputeDet_biz.not_msg + '</span></li>').appendTo("#top");
                //left部分
                $('<li><span>' + lang.disputeDet_biz.not_msg + '</span></li>').appendTo("#left");
                //right部分
                $('<li><span>' + lang.disputeDet_biz.not_msg + '</span></li>').appendTo("#right");
                //订单部分
                $('<li><span>' + lang.disputeDet_biz.not_msg + '</span></li>').appendTo("#order");
            }
        })

    }

    /**
     * @desc 获取request_id
     * @author liaojianwen
     * @date 2015-06-25
     */
    function getReturnRequest(caseid) {
        if (typeof(caseid) !== 'undefined' || caseid !== '') {
            caseid = global_info.caseid
        }
        $.get('?r=api/GetReturnRequest&caseid=' + caseid, function(data, status) {
            if (status === 'success' && data.Ack === 'Success') {
                var returnId_id = data.Body.returnId;
                $('<span>( RequestID : </span><span>' + returnId_id + ')</span>').appendTo('#CaseId_id')
                    //                  getReturnHistory(returnId_id);
                if (returnId_id) {
                    $('<option value="provideReturnInfo">Edit Return address</option>').appendTo('#case_dispose_select');
                }
            }
        });
    }

    /**
     * @desc 获取return 历史记录
     * @author liaojianwen
     * @date 2015-07-07
     */
    function getReturnHistory(returnId_id) {
        if (typeof(returnId_id) === 'undefined' || returnId_id === '') {
            return;
        }
        $.get('?r=api/GetReturn2Case&returnId_id=' + returnId_id, function(data, status) {
            if (status === 'success' && data.Ack === 'Success') {
                var M = data.Body;
                if (M && M.length > 0) {
                    for (var i in M) {
                        if (M[i].note == '') {
                            var note = '<i> &lt; ' + M[i].activity + ' &gt; </i><br />';
                        } else {
                            var note = '<i> &lt; ' + M[i].activity + ' &gt; </i><br />' + M[i].note;
                        }
                        if (M[i].sellerReturnAddr_name) {
                            note += '<br /><i>' + M[i].sellerReturnAddr_name + '</i><br /><i>' + M[i].sellerReturnAddr_street1 + '</i>&nbsp';
                            note += '<i>' + M[i].sellerReturnAddr_street2 + '</i><br /><i>' + M[i].sellerReturnAddr_city + '</i>&nbsp<i>' + M[i].sellerReturnAddr_county +
                                '</i>&nbsp';
                            note += '<i>' + M[i].sellerReturnAddr_stateOrProvince + '</i>&nbsp<i>' + M[i].sellerReturnAddr_country + '</i>&nbsp';
                            note += '<i>' + M[i].sellerReturnAddr_postalCode + '</i>';
                        }
                        if (M[i].author == 'SELLER') {
                            $('<li class="sellerMSG">' +
                                '<div class="MSGC">' +
                                '<p><b>' + intToLocalDate(M[i].creationDate, 3) + '</b>&nbsp;' + M[i].author + '</p>' +
                                '<p>' + note + '</p>' +
                                '</div></li>').appendTo("#history");
                        } else {
                            $('<li class="buyerMSG">' +
                                '<div class="MSGC">' +
                                '<p><b>' + intToLocalDate(M[i].creationDate, 3) + '</b>&nbsp;' + M[i].author + '</p>' +
                                '<p style="white-space:normal;">' + note + '</p>' +
                                '</div></li>').appendTo("#history");
                        }

                    }
                }
            }
        });
    }

    /**
     * @desc 根据case_id 查询客户留言信息
     * @author liaojianwen
     * @date 2015-05-21
     */
    function getEbayOrderNote(caseid) {
        $.get('?r=api/GetEbayOrderNote&caseid=' + caseid, function(data, status) {
            if (status == 'success') {
                if (data.Ack == 'Success') {
                    if (data.Body.Note) {
                        $('.customerNote').html('<span>' + lang.disputeDet_biz.buyer_msg + '：</span><span>' + data.Body.Note + '</span>').show();
                    }
                    var addr = data.Body.Address;
                    if (addr) {
                        verify_sku = 1; //用于判断是否可以从订单中取出SKU
                        var name = addr.name ? addr.name : '';
                        var street1 = addr.street1 ? addr.street1 : '';
                        var street2 = addr.street2 ? addr.street2 : '';
                        var CityName = addr.CityName ? addr.CityName : '';
                        var StateOrProvince = addr.StateOrProvince ? addr.StateOrProvince : '';
                        var CountryName = addr.CountryName ? addr.CountryName : '';
                        var shippingAddr = name + '&nbsp' + street1 + '&nbsp' + street2 + '&nbsp' + CityName + '&nbsp' + StateOrProvince + '&nbsp' +
                            CountryName
                        $('.custaddr').html('<span class="leftName">' + lang.disputeDet_biz.shipping_address + '：</span><span class="rightVal">' +
                            shippingAddr + '</span>').show();
                        $('#orderinfo').find('tr[data-itemid="' + global_info.itemId + '"] td').eq(1).find('span').html(addr.SKUS ? addr.SKUS : addr.SKU);
                        $('#Price').html('<span>' + (addr.CurrentPrice ? addr.CurrentPrice : 0) + '&nbsp' + addr.currencyID + '</span>');
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
                                hintShow('hint_f', lang.ajaxinfo.network_error);
                            }
                        });
                    })();


                }
            }
        });

    }
    /**
     * @desc 根据case_id 查询评价信息
     * @author liaojianwen
     * @date 2015-05-20
     */
    function getFeedbackInfo(caseid) {
        //由transactionId 获取物品评价
        $.get('?r=api/GetFeedbackInfo&caseid=' + caseid, function(data, status) {
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
     * @desc 根据case_id 查询产品信息
     * @author liaojianwen,YangLong
     * @date 2015-05-20
     */
    function getItemInfo(caseid, itemid) {
        $.get('?r=api/GetItemInfoByItemId&caseid=' + caseid, function(data, status) {
            if (status == 'success') {
                if (data.Ack == 'Success') {
                    var $orderinfo = $('#orderinfo');
                    var tr = $orderinfo.find('tr[data-itemid="' + itemid + '"]');
                    var img = tr.find('td>img');
                    img.attr('src', data.Body.PictureDetails.GalleryURL.replace(/\$_1\.JPG\?/i, '$_13.JPG?'));
                    img.replaceWith('<a data-lightbox="example-set-1" href="' + data.Body.PictureDetails.GalleryURL + '">' + img[0].outerHTML + '</a>');
                    if (!verify_sku) {
                        tr.find('td').eq(1).find('span').html(data.Body.SKU ? data.Body.SKU : data.Body.skus[0]);
                    }
                } else {
                    // 服务端错误
                }
            } else {
                // 网络错误
            }
        });
    }
    /**
     * @desc 加载卖家发起case对话历史
     * @author liaojianwen
     * @date 2015-08-20
     */
    function getCancelDisputeMessage(caseid) {
        if (typeof(caseid) !== 'undefined' || caseid !== '') {
            var case_id = global_info.caseid;
        }
        $.ajax({
            url: '?r=api/GetCancelDisputeMessage&disputeid=' + case_id,
            success: function(result) {
                if (result.Ack === 'Success') {
                    var M = result.Body.list;
                    //对话部分
                    if (M && M.length > 0) {
                        for (var i in M) {
                            if (M[i].MessageSource == 'Seller') {
                                $('<li class="sellerMSG">' +
                                    '<div class="sellerMSG  MSGC">' +
                                    '<p><b>' + intToLocalDate(M[i].MessageCreationTime, 3) + '</b>&nbsp;' + M[i].MessageSource + '</p>' +
                                    '<p style="white-space:normal;">' + (M[i].MessageText ? M[i].MessageText : '') + '</p>' +
                                    '</div></li>').appendTo("#history");

                            } else {
                                $('<li class="buyerMSG">' +
                                    '<div class="MSGC">' +
                                    '<p><b>' + intToLocalDate(M[i].MessageCreationTime, 3) + '</b>&nbsp;' + M[i].MessageSource + '</p>' +
                                    '<p>' + (M[i].MessageText ? M[i].MessageText : '') + '</p>' +
                                    '</div></li>').appendTo("#history");
                            }
                        }
                    } else {
                        //对话框部分
                        $('<li style="text-align:center;"><span>' + lang.disputeDet_biz.not_chat + '</span></li>').appendTo("#history");
                    }
                }
            }
        })
    }

    /**
     * @desc 加载卖家发起case对话历史
     * @author lvjianfei
     * @date 2015-04-02
     */
    function getCaseDisputeMessage(caseid) {
        if (typeof(caseid) !== 'undefined' || caseid !== '') {
            var case_id = global_info.caseid;
        }
        $.ajax({
            url: '?r=api/GetCaseDisputeMessage&caseid=' + case_id,
            success: function(result) {
                if (result.Ack === 'Success') {
                    var M = result.Body.list;
                    //对话部分
                    if (M && M.length > 0) {
                        for (var i in M) {
                            if (M[i].MessageSource == 'Seller') {
                                $('<li class="sellerMSG">' +
                                    '<div class="sellerMSG  MSGC">' +
                                    '<p><b>' + intToLocalDate(M[i].MessageCreationTime, 3) + '</b>&nbsp;' + M[i].MessageSource + '</p>' +
                                    '<p style="white-space:normal;">' + (M[i].MessageText ? M[i].MessageText : '') + '</p>' +
                                    '</div></li>').appendTo("#history");

                            } else {
                                $('<li class="buyerMSG">' +
                                    '<div class="MSGC">' +
                                    '<p><b>' + intToLocalDate(M[i].MessageCreationTime, 3) + '</b>&nbsp;' + M[i].MessageSource + '</p>' +
                                    '<p>' + (M[i].MessageText ? M[i].MessageText : '') + '</p>' +
                                    '</div></li>').appendTo("#history");
                            }
                        }
                    } else {
                        //对话框部分
                        $('<li style="text-align:center;"><span>' + lang.disputeDet_biz.not_chat + '</span></li>').appendTo("#history");
                    }
                }
            }
        })
    }

    /**
     * @desc 加载买家发起case对话历史
     * @author lvjianfei
     * @date 2015-04-02
     */
    function getCaseHistory(caseid) {
        if (typeof(caseid) !== 'undefined' || caseid !== '') {
            var case_id = global_info.caseid;
        }
        $.ajax({
            url: '?r=api/GetCaseHistory&caseid=' + case_id,
            success: function(result) {
                if (result.Ack == 'Success') {
                    var M = result.Body.list;
                    if (result.author_role == 'SELLER') {
                        var seller = result.author_userId;
                    }
                    //对话部分
                    if (M && M.length > 0) {
                        for (var i in M) {
                            if (M[i].note == '') {
                                var note = '<i> &lt; ' + M[i].activityDetial_description + ' &gt; </i><br />';
                            } else {
                                var note = '<i> &lt; ' + M[i].activityDetial_description + ' &gt; </i><br />' + (M[i].note ? M[i].note : '');
                            }
                            if (M[i].sellerReturnAddr_name) {
                                note += '<br /><i>' + M[i].sellerReturnAddr_name + '</i><br /><i>' + M[i].sellerReturnAddr_street1 + '</i>&nbsp';
                                note += '<i>' + M[i].sellerReturnAddr_street2 + '</i><br /><i>' + M[i].sellerReturnAddr_city + '</i>&nbsp<i>' + M[i]
                                    .sellerReturnAddr_county + '</i>&nbsp';
                                note += '<i>' + M[i].sellerReturnAddr_stateOrProvince + '</i>&nbsp<i>' + M[i].sellerReturnAddr_country +
                                    '</i>&nbsp';
                                note += '<i>' + M[i].sellerReturnAddr_postalCode + '</i>';
                            }
                            if (M[i].author_role == 'SELLER') {
                                $('<li class="sellerMSG">' +
                                    '<div class="MSGC">' +
                                    '<p><b>' + intToLocalDate(M[i].creationDate, 3) + '</b>&nbsp' + M[i].author_role +
                                    '<span class="note_md" data-note-md5=' + M[i].note_md5 + ' data-activity-md5=' + M[i].activityDetial_description_md5 +
                                    '></span></p>' +
                                    '<p>' + note + '</p>' +
                                    '</div></li>').appendTo("#history");
                            } else {
                                $('<li class="buyerMSG">' +
                                    '<div class="MSGC">' +
                                    '<p><b>' + intToLocalDate(M[i].creationDate, 3) + '</b>&nbsp' + M[i].author_role + '<span data-note-md5=' +
                                    M[i].note_md5 + ' data-activity-md5=' + M[i].activityDetial_description_md5 + '></span></p>' +
                                    '<p style="white-space:normal;">' + note + '</p>' +
                                    '</div></li>').appendTo("#history");
                            }
                        }
                    }
                    getCaseDetail();
                    getCaseOperator();
                    getCaseHandleLog();
                } else {
                    //对话框部分
                    $('<li style="text-align:center;"><span>' + lang.disputeDet_biz.not_chat + '</span></li>').appendTo("#history");
                }
            }
        })
    }

    /**
     * @desc 获取处理人
     * @author liaojianwen
     * @date 2015-07-15
     */
    function getCaseOperator() {
        $.get('?r=api/GetCaseOperator&caseid=' + global_info.caseid, function(data, status) {
            if (status === 'success' && data.Ack === 'Success') {
                var L = data.Body;
                $('.sellerMSG .note_md').each(function(index, obj) {
                    for (var j in L) {
                        if ($(obj).attr('data-note-md5') == L[j].note_md5 || $(obj).attr('data-activity-md5') == L[j].handle_type_md5) {
                            $(obj).after('&nbsp&nbsp <span>' + L[j].handle_user + '</span> ');
                        }
                    }
                })



            }

        });
    }

    /**
     * @desc 获取case处理记录
     * @author lvjianfei
     * @date 2015-04-20
     */
    function getCaseHandleLog() {
        var param = {
            'case_id': global_info.caseid,
            '_rnd': loading()
        }
        $.get("?r=api/GetCaseHandleLog", param, function(data, status) {
            removeloading();
            if (status === 'success' && data.Ack === 'Success') {
                var M = data.Body.list;
                $("#history").find('i').each(function(index, element) {
                    if ($(element).html().toString().indexOf('Seller provided shipping information.') > -1) {
                        for (var k in M) {
                            if (M[k].handle_type === 'addShippingInfo') {
                                $(element).after('<br /><i> CarrierUsed: ' + M[k].carrier + '</i><br /><i> ShipDate: ' + intToLocalDate(M[k].shipdate,
                                    3) + '</i>');
                            }
                        }
                    }
                    if ($(element).html().toString().indexOf('Seller provided tracking information for shipment.') > -1) {
                        for (var k in M) {
                            if (M[k].handle_type === 'addTrackingInfo') {
                                $(element).after('<br/><i>trackingNumber: <a href="http://www.ec-firstclass.org/?track_number=' + M[k].trackingNum +
                                    '" target="_blank" class="fontLinkBtn">' + M[k].trackingNum + '</a></i>');
                                $(element).after('<br/><i>carrierUsed: ' + M[k].carrier + '</i>');
                            }
                        }
                    }
                });
                if (M && M.length > 0) {
                    for (var i in M) {
                        switch (M[i].handle_type) {
                            case 'addResponse':
                                $('<tr><td><span>' + lang.disputeDet_biz.reply_message + '</span></td><td></td><td></td><td><span>' + intToLocalDate(M[
                                    i].create_time, 3) + '</span></td><td><span>' + M[i].handle_user + '</span></td></tr>').appendTo("#handle");
                                break;
                            case 'addTrackingInfo':
                                $('<tr><td><span>' + lang.disputeDet_biz.add_tracking_number + '</span></td><td><span>' + M[i].carrier +
                                    '</span></td><td><span>' + M[i].trackingNum +
                                    '</span></td><td><span>' + intToLocalDate(M[i].create_time, 3) + '</span></td><td><span>' + M[i].handle_user +
                                    '</span></td></tr>').appendTo("#handle");
                                break;
                            case 'addShippingInfo':
                                $('<tr><td><span>' + lang.disputeDet_biz.add_logistics_information + '</span></td><td><span>' + M[i].carrier +
                                    '</span></td><td><span>' + intToLocalDate(M[i].shipdate, 3) +
                                    '</span></td><td><span>' + intToLocalDate(M[i].create_time, 3) + '</span></td><td><span>' + M[i].handle_user +
                                    '</span></td></tr>').appendTo("#handle");
                                break;
                            case 'refund':
                            case 'fullRefund':
                                $('<tr><td><span>' + lang.disputeDet_biz.full_refund + '</span></td><td></td><td></td><td><span>' + intToLocalDate(M[i]
                                    .create_time, 3) + '</span></td><td><span>' + M[i].handle_user + '</span></td></tr>').appendTo("#handle");
                                break;
                            case 'partialRefund':
                                $('<tr><td><span>' + lang.disputeDet_biz.partial_refund + '</span></td><td><span>' + M[i].amount +
                                    '</span></td><td></td><td><span>' + intToLocalDate(M[i].create_time, 3) + '</span></td><td><span>' + M[i].handle_user +
                                    '</span></td></tr>').appendTo("#handle");
                                break;
                            case 'returnItemRefund':
                                $('<tr><td><span>' + lang.disputeDet_biz.returns_processing + '</span></td><td></td><td></td><td><span>' +
                                    intToLocalDate(M[i].create_time, 3) + '</span></td><td><span>' + M[i].handle_user + '</span></td></tr>').appendTo(
                                    "#handle");
                                break;
                            case 'ebayHelp':
                                $('<tr><td><span>' + lang.disputeDet_biz.escalate_to_ebay + '</span></td><td colspan="2"><span>' + M[i].reason +
                                    '</span></td><td><span>' + intToLocalDate(M[i].create_time, 3) + '</span></td><td><span>' + M[i].handle_user +
                                    '</span></td></tr>').appendTo("#handle");
                                break;
                        }
                    }
                } else {
                    $('<tr><td colspan="5"><span>' + lang.disputeDet_biz.not_yet + '</span></td></tr>').appendTo("#handle");
                }
            }
        })
    }

    /**
     * @desc 加载case备注
     * @author lvjianfei,liaojianwen
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
                    "type": "case",
                    "caseId": caseId
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
    //执行日期选择方法
    dateSelFun($('#dateSel'));

    /**
     * @desc 添加case备注事件
     * @author lvjianfei,liaojianwen
     * @date 2015-04-02
     * @modify 2015-04-21
     */
    $("#sub-note").on('click', function() {
        var text = $("#note-text").val();
        if (text == '') {
            hintShow('hint_w', lang.disputeDet_biz.cannot_empty);
            return;
        }
        var item_id = global_info.itemId;
        $.ajax({
            url: '?r=api/AddItemNote&caseid=' + global_info.caseid + '&text=' + text,
            success: function(data, state) {

                if (state == 'success') {
                    if (data.Ack == 'Success') {
                        $("#note-text").val('');
                        getItemNoteList();
                    } else if (data.Error == 'User authentication fails') {
                        hintShow('hint_w', lang.ajaxinfo.permission_denied);
                    } else {
                        hintShow('hint_f', lang.ajaxinfo.network_error);
                    }
                }
            }
        })
    })

    //返回列表页
    $("#leftTopLeft").on('click', function() {
        if (typeof window.parent.caselist_url == 'undefined') {
            window.parent.$("iframe[name='main']").attr("src", "?r=Home/MsgList&class=inbox");
        } else {
            window.parent.$("iframe[name='main']").attr("src", window.parent.caselist_url);
        }
    });
    //点击更多时，自己收缩侧边栏
    (function() {
        $("#more_btn").on('click', function() {
            $("#more_div").fadeIn();
            $(parent.document).find('.reFull').click();
        });
        $("#CloseMoreWindow").on('click', function() {
            $("#more_div").hide();
            $(parent.document).find('.reFull').click();
        });
    })();

    /**
     * @desc 我的处理 更多按钮事件 
     * @author : linpeiyan
     * @date 2015-04-14
     */
    $('.dealTable thead .more').on('click', function() {
        var $this = $(this);
        if ($this.attr('onBtn') == 'off') {
            $('.dealTable tbody tr').show('0', function() {
                $this.attr('onBtn', 'on');
            });
            $this.html(lang.disputeDet_biz.UI.hide).attr('title', lang.disputeDet_biz.UI.click_hide);
        } else if ($this.attr('onBtn') == 'on') {
            $('.dealTable tbody tr:first').siblings().hide('0', function() {
                $this.attr('onBtn', 'off');
            });
            $this.html(lang.disputeDet_biz.UI.more).attr('title', lang.disputeDet_biz.UI.click_more);
        }
    })

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
        $("#classbox").append('<select pid="' + pid + '"><option value="">' + lang.disputeDet_biz.please_select + '</option></select>');
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



    function getMoreUserInfo(clientId) {
        $window_client_info.find('span[t="id"]').html('<a href="http://www.ebay.com/usr/' + clientId + '" target="_blank" class="fontLinkBtn">' + clientId +
            '</a>');
        // Item图片显示
        $.get("?r=api/GetEbayUserInfo", {
            userid: clientId
        }, function(data, status) {
            if (status === 'success') {
                if (data.Ack === 'Success') {
                    $window_client_info.find('span[t="regaddr"]').html(data.Body.Site);
                    if (data.Body.RegistrationDate > 0) {
                        $window_client_info.find('span[t="RegistrationDate"]').html(intToLocalDate(data.Body.RegistrationDate, 3)).parent().show();
                    }
                    if (data.Body.PositiveFeedbackPercent >= 0) {
                        $window_client_info.find('span[t="PositiveFeedbackPercent"]').html(data.Body.PositiveFeedbackPercent).parent().show();
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
                        data.Body.regaddr_StateOrProvince.length <= 0 ? '' : RaddrStr += data.Body.regaddr_StateOrProvince + ', ';
                        RaddrStr += data.Body.regaddr_PostalCode + ', ';
                        RaddrStr += data.Body.regaddr_CountryName;
                        data.Body.regaddr_Phone.length <= 0 ? '' : RaddrStr += '<br />' + lang.disputeDet_biz.phone + ': ' + data.Body.regaddr_Phone +
                            ', ';

                        if (RaddrStr.length > 12) {
                            $("#window_client_info_l").append('<li style=""><span class="leftName">' + lang.disputeDet_biz.reg_address +
                                '：</span><span class="rightVal" style="width:370px;">' + RaddrStr + '</span></li>');
                        }
                    })();
                    (function() {
                        //作废
                        return
                        // 其他地址显示
                        $.get('?r=api/GetUserAddress', {
                            EIASToken: data.Body.EIASToken
                        }, function(data, textStatus) {
                            if (textStatus == 'success') {
                                if (data.Ack == 'Success') {
                                    for (var x in data.Body) {
                                        var addrStr = data.Body[x].Name + ', ' + data.Body[x].Street1 + ', ' + (data.Body[x].Street2.length >
                                                0 ? (data.Body[x].Street2 + ', ') : '') + data.Body[x].CityName + ', ' + data.Body[x].StateOrProvince +
                                            ', ' + (typeof data.Body[x].PostalCode !== 'undefined' && data.Body[x].PostalCode.length > 0 ?
                                                data.Body[x].PostalCode + ', ' : '')
                                            // +(typeof data.Body[x].Country!=='undefined'
                                            //     && data.Body[x].Country.length>0
                                            //     ?data.Body[x].Country:'')
                                            + (typeof data.Body[x].CountryName !== 'undefined' && data.Body[x].CountryName.length > 0 ?
                                                data.Body[x].CountryName : '');
                                        if (data.Body[x].Phone.length > 0) {
                                            addrStr += ('<br />' + lang.disputeDet_biz.phone + ': ' + data.Body[x].Phone);
                                        }
                                        $("#window_client_info_l").append('<li style=""><span class="leftName">' + lang.disputeDet_biz.shipping_address +
                                            (+x + 1) + '：</span><span class="rightVal" style="width:370px;">' + addrStr +
                                            '</span></li>');
                                    }
                                } else if (data.Ack == 'Warning') {

                                } else {
                                    //                                    hintShow('hint_f','服务器内部错误!');
                                }
                            } else {
                                hintShow('hint_f', lang.ajaxinfo.network_error);
                            }
                        });
                    })();

                } else {
                    // hintShow('hint_f','服务器错误!');
                }
            } else {
                hintShow('hint_f', lang.ajaxinfo.network_error);
            }
        });


    }


    /**
     * @desc 获取购物历史
     * @author  liaojianwen
     * @date 2015-07-31
     */
    function getEbayTransactionsByUserId(clientId) {
        $.get('?r=api/GetEbayTransactionsByUserId', {
            BuyerUserID: clientId
        }, function(data, textStatus) {
            if (textStatus == 'success') {
                if (data.Ack === 'Success') {
                    var paymentMethod;
                    $("#buyHistory_div").find('tbody').empty();
                    if (data.Body.length > 0) {
                        for (var x in data.Body) {
                            var transStr = '';
                            for (var y in data.Body[x].ExtTrans) {
                                transStr += ('<div class="textleft tooltip_td" title="' + lang.disputeDet_biz.transaction_info.time + '：' +
                                    intToLocalDate(data.Body[x].ExtTrans[y].ExternalTransactionTime, 5) + '<br />' + lang.disputeDet_biz.transaction_info
                                    .amount + '：' + data.Body[x].ExtTrans[y].PaymentOrRefundAmount + data.Body[x].ExtTrans[y].PaymentOrRefundAmount_currencyID +
                                    '' + '<br />' + lang.disputeDet_biz.transaction_info.FVF + '：' + data.Body[x].ExtTrans[y].FeeOrCreditAmount +
                                    data.Body[x].ExtTrans[y].FeeOrCreditAmount_currencyID + '' + '<br />' + lang.disputeDet_biz.transaction_info.status +
                                    '：' + data.Body[x].ExtTrans[y].ExternalTransactionStatus + '' + '">' + data.Body[x].ExtTrans[y].ExternalTransactionID +
                                    '(' + data.Body[x].ExtTrans[y].PaymentOrRefundAmount + data.Body[x].ExtTrans[y].PaymentOrRefundAmount_currencyID +
                                    ')' + '</div>');
                            }
                            // 属性
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
                                    _feedback = '<i class="iconNone"></i>';
                            }
                            var orderStatus = '';
                            if (data.Body[x].OrderStatus) {
                                orderStatus = data.Body[x].OrderStatus;
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
                                addressStr += '\nPhone:' + data.Body[x].Phone;
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
                                    _shuxing += '<br/>' + data.Body[x].VariationSpecificsXML.xml.NameValueList.Name + ':' + data.Body[x].VariationSpecificsXML
                                        .xml.NameValueList.Value;
                                }
                            }
                            paymentMethod = data.Body[x].PaymentMethod != 'None' ? data.Body[x].PaymentMethod : '';
                            var SkuStr = (data.Body[x].Item_SKU != '' ? HTMLDecode(HTMLDecode(data.Body[x].Item_SKU)) : HTMLDecode(HTMLDecode(data.Body[
                                x].Variation_SKU)));
                            $("#buyHistory_div").find('tbody').append('<tr data-key=' + x + '><td>' + data.Body[x].SellerUserID +
                                '</td><td t="sku" style="word-break:break-all;">' + SkuStr +
                                '<br />ItemID:<a href="http://cgi.ebay.com/ws/eBayISAPI.dll?ViewItem&item=' + data.Body[x]
                                .Item_ItemID +
                                '" target="_blank" class="fontLinkBtn">' + data.Body[x].Item_ItemID + '</a>' + (data.Body[x].ProductName.length > 0 ?
                                    ('<br />Prodtct:' + HTMLDecode(HTMLDecode(data.Body[x].ProductName))) : '') + _shuxing + '</td><td t="pnum">' +
                                data.Body[x].QuantityPurchased + '</td><td t="pjg">' + data.Body[x].TransactionPrice + data.Body[x].TransactionPrice_currencyID +
                                '</td><td t="ptime" style="word-break:break-all;line-height:1.1em;">' + intToLocalDate(data.Body[x].CreatedDate, 8) +
                                '</td><td t="pm" style="line-height:1.5em;">' + data.Body[x].PaymentMethod + transStr + '</td>' +
                                '<td><a href="javascript:;" title="' + addressStr + '" class="tooltip_td">' + lang.disputeDet_biz.view +
                                '</a></td>' + '<td>' + orderStatus + '</td>' + '<td class="evaluate">' + _feedback + '</td>' +
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
                                            noteText[x] = (lang.disputeDet_biz.Notes.author + '：' + data.Body[x].author_name + '<br />' +
                                                lang.disputeDet_biz.Notes.content + '：' + data.Body[x].text + '<br />' + lang.disputeDet_biz
                                                .Notes.buyer + '：' + data.Body[x].cust + '<br />' + lang.disputeDet_biz.Notes.time +
                                                '：' + intToLocalDate(data.Body[x].create_time, 7)) + '<br /><br />';
                                        }
                                        $("#buyHistory_div").find('tr[data-key=' + _key + ']').find('td[t="note"]').html(
                                            '<i class="icon-remark"></i>').attr('title', noteText.join('\n\n'));
                                        //执行鼠标经过气泡提示方法
                                        tooltip($('.tooltip_td'));
                                    } else if (data.Ack == 'Warning') {
                                        $("#buyHistory_div").find('tr[data-key=' + _key + ']').find('td[t="note"]').html(lang.disputeDet_biz
                                            .none);
                                    } else {
                                        $("#buyHistory_div").find('tr[data-key=' + _key + ']').find('td[t="note"]').html('✖');
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
                        $("#buyHistory_div").find('tbody').append('<tr><td colspan="9">' + lang.disputeDet_biz.not_find_data + '</td></tr>');
                    }
                } else {
                    // hintShow('hint_f','服务器错误!');
                }
            } else {
                // hintShow('hint_f',lang.ajaxinfo.network_error);
            }
        });
    }

    /**
     * @desc 消息历史
     * @author lioajianwen
     * @date 2015-07-31
     */
    function getMsgByUserID(clientId) {
        var $his_msgs_ul = $("#his_msgs_ul");
        var page = $his_msgs_ul.data('page');
        // var noloading = false;
        function loadlist(xcpage) {
            $his_msgs_ul.empty();
            $his_msgs_ul.html('<li style="text-align:center; padding:12px;"><img src="public/template/img/loader.gif" width="31" height="31"></li>');
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
                                $his_msgs_ul.append('<li data-msgid="' + data.Body[x].msg_id + '" title="' + data.Body[x].Subject + '">' + '<span>' +
                                    intToLocalDate(data.Body[x].ReceiveDate, 9) + '</span><p>' + data.Body[x].nick_name + ' | ' + ((data.Body[x].FolderID ==
                                        1) ? lang.disputeDet_biz.msg.send + '：' : lang.disputeDet_biz.msg.receive + '：') + data.Body[x].Subject +
                                    '</p></li>');
                            }
                            // noloading = true;
                            $his_msgs_ul.find('li').eq(0).click();
                        } else {
                            $his_msgs_ul.html('<li>' + lang.disputeDet_biz.msg.no_msg + '</li>');
                            $('#his_msg_box2').attr('style', '');
                            $("#his_msg_box2").html('');
                            if (page > 1) {
                                hintShow('hint_w', lang.disputeDet_biz.msg.is_last_page + '');
                                page--;
                                loadlist(page);
                            }
                        }
                    } else {
                        // hintShow('hint_f','服务器错误!');
                    }
                } else {
                    // hintShow('hint_f',lang.ajaxinfo.network_error);
                }
            });
        }

        loadlist(page);


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
                    '<div style="margin:50px auto; text-align:center;"><img src="public/template/img/loader.gif" width="31" height="31"></div>');
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
                                if ($(element).attr('src') !== undefined && $(element).attr('src').indexOf('button') == -1 && $(element)
                                    .attr('src').indexOf('ebaystatic') == -1) {
                                    if ($(element).parent('a').length == 0) {
                                        $(element).replaceWith('<a>' + $(element)[0].outerHTML + '</a>');
                                    }
                                }
                            });
                            $msgtext.find('img').each(function(index, element) {
                                if ($(element).attr('src') !== undefined && $(element).attr('src').indexOf('button') == -1 && $(element)
                                    .attr('src').indexOf('ebaystatic') == -1 && $(element).attr('src').indexOf('/globalAssets/') == -1 &&
                                    $(element).attr('src').indexOf('/icon/') == -1 && $(element).attr('src').indexOf('/roveropen/') ==
                                    -1) {
                                    $(element).parent('a').attr('data-lightbox', 'imgGroup14');
                                    var imgurl = $(element).attr('src').toString().replace(
                                        /(%24|\$)_\d+(?=\.(jpg|png|jpeg|gif|bmp|tif)\??)/i, '$_10');
                                    $(element).parent('a').attr('href', imgurl);
                                }
                            });
                            $msgtext.find('a').each(function(index, element) {
                                if ($(element).attr('href') !== undefined && $(element).attr('href').indexOf('http://') != -1) {
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
                        // hintShow('hint_f',lang.ajaxinfo.network_error);
                    }
                })
            }
        });
    }

    // 获取买家对卖家的所有评价
    function getPositived(clientId) {
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
                        $window_client_info.find('span[t="bNegative"]').parent().html(lang.disputeDet_biz.not_feedback).show();
                    }
                } else {}
            } else {}
        });
    }
    $("#his_msgs_up").on('click', function() {
        if (page > 1) {
            page--;
        } else {
            hintShow('hint_w', lang.disputeDet_biz.msg.is_last_page);
        }
        loadlist(page);
    });

    $("#his_msgs_down").on('click', function() {
        page++;
        loadlist(page);
    });
    /**
     * @desc 加载卖家发起case详细信息
     * @author liaojianwen
     * @date 2015-08-19
     */
    function getCancelDisputeDetail(caseid) {
        if (typeof(caseid) !== 'undefined' || caseid !== '') {
            var global = {
                'disputeid': global_info.caseid,
                'type': global_info.type
            };
        }
        $.get("?r=api/GetCancelDisputeDetail", global, function(data, status) {
            if (data.Ack == 'Success') {
                var _user_id = data.Body.user_id;
                var result = data.Body.list;
                var prenextID = data.Body.prenextID;
                //                    var status = $("#casedetail").attr('status');
                global_info.itemId = result.i_ItemID; ///
                //top部分//暂时先隐藏,后面有需求调整
                $('#top').hide();
                //left部分
                $('<li><span>' + lang.disputeDet_biz.account + '：</span><span>' + result.SellerUserID + '</span></li>' +
                    '<li><span>' + lang.disputeDet_biz.dispute_id + '：</span><span>' + result.DisputeID + '</span></li>' +
                    '<li><span>' + lang.disputeDet_biz.dispute_start_time + '：</span><span>' + intToLocalDate(result.DisputeCreatedTime, 3) +
                    '</span></li>' +
                    '<li><span>' + lang.disputeDet_biz.dispute_staus + '：</span><span>' + result.DisputeState + '</span></li>' +
                    '<li><span>' + lang.disputeDet_biz.dispute_reason + '：</span><span>' + result.DisputeReason + '</span></li>').appendTo("#left");
                //right部分
                $('<li><span class="leftName">BuyerUserID：</span><span>' + result.BuyerUserID + '</span></li>' +
                    //                      '<li><span>消费：</span><span>暂时没数据</span></li>'+
                    '<li class="custaddr" style="display:none"></li>' +
                    '<li class="customerNote"></li>').appendTo("#right");
                //订单信息部分
                $('<table class="table">' +
                    '<thead><tr>' +
                    '<th>' + lang.disputeDet_biz.order_info.picture + '</th>' +
                    '<th>' + lang.disputeDet_biz.order_info.SKU + '</th>' +
                    '<th>' + lang.disputeDet_biz.order_info.itemid + '</th>' +
                    '<th>' + lang.disputeDet_biz.order_info.price + '</th>' +
                    '<th>' + lang.disputeDet_biz.order_info.quantity + '</th>' +
                    (global.type == 'cancel' ? '<th>' + lang.disputeDet_biz.order_info.feedback + '</th>' : '') +
                    '</tr></thead>' +
                    '<tbody><tr data-itemid="' + result.i_ItemID + '">' +
                    '<td><img src="" alt="" /></td>' +
                    '<td><span></span></td>' +
                    '<td><a href="http://cgi.ebay.com/ws/eBayISAPI.dll?ViewItem&item=' + (_user_id ? result.i_ItemID.replace(/(\d{8})\d{4}/,
                        '$1****') : result.i_ItemID) + '" target="_blank" class="fontLinkBtn">' + (_user_id ? result.i_ItemID.replace(
                        /(\d{8})\d{4}/, '$1****') : result.i_ItemID) + '</a></td>' +
                    '<td>' + result.i_ss_CurrentPrice + '&nbsp' + result.i_ss_CurrentPrice_currencyID + '</td>' +
                    '<td>' + result.i_Quantity + '</td>' +
                    (global.type == 'cancel' ? '<td class="evaluate"><i class="iconNone"></i></td>' : '') +
                    '</tr></tbody>' +
                    '</table>').appendTo("#orderinfo");
                //获取客户留言
                getEbayOrderNote(global_info.caseid);
                //获取产品信息

                setTimeout(function() {
                    getItemInfo(global_info.caseid, result.i_ItemID)
                }, 1000);
                //获取评价信息
                getFeedbackInfo(global_info.caseid);

                //获取客户more info
                getMoreUserInfo(result.BuyerUserID);
                //获取客户关联的交易
                getEbayTransactionsByUserId(result.BuyerUserID);
                //获取客户关联的msg
                getMsgByUserID(result.BuyerUserID);
                //获取评价数
                getPositived(result.BuyerUserID);

                //上下翻页事件
                if (typeof(prenextID.preID) === 'undefined' || prenextID.preID === '') {
                    $("#pre").attr('title', lang.disputeDet_biz.not_on_letter);
                    $("#pre").addClass('notOpBtn');
                    $("#pre").removeAttr('id');
                }
                if (typeof(prenextID.nextID) === 'undefined' || prenextID.nextID === '') {
                    $("#next").attr('title', lang.disputeDet_biz.not_on_under);
                    $("#next").addClass('notOpBtn');
                    $("#next").removeAttr('id');
                }
                $("#pre").on("click", function() {
                    location.href = "?r=Home/DisputeDetail&caseid=" + prenextID.preID + "&type=" + global_info.type;
                });
                $("#next").on("click", function() {
                    location.href = "?r=Home/DisputeDetail&caseid=" + prenextID.nextID + "&type=" + global_info.type;
                })
                getItemNoteList();

            } else {
                //top部分
                $('<li><span>' + lang.disputeDet_biz.not_msg + '</span></li>').appendTo("#top");
                //left部分
                $('<li><span>' + lang.disputeDet_biz.not_msg + '</span></li>').appendTo("#left");
                //right部分
                $('<li><span>' + lang.disputeDet_biz.not_msg + '</span></li>').appendTo("#right");
                //订单部分
                $('<li><span>' + lang.disputeDet_biz.not_msg + '</span></li>').appendTo("#order");
            }
        })

    }

})