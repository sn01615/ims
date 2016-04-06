"use strict";
/**
 * @desc 1.获取信息明细数据，绑定数据
 * 2.获取回复模板，绑定数据
 * 3.回复信息
 * 4.信息标记星标、处理。返回、上一封、下一封处理
 * @author heguangquan
 * @date 2015-03-05
 */
$(function() {
    var $oSendContent = $('#sendContent');
    var img = [];
    var global_itemID;
    var global = {
        'imgurl': undefined
    };
    var ResponseEnabled;
    // var is_read;

    /**
     * @desc 获取信息明细
     * @author heguangquan
     * @date 2015-03-05
     * @modify YangLong 2015-05-06 重构
     */
    (function() {
        var msgId = $("#emailDetail").attr('mid');
        var msgType = $('#emailDetail').attr('msg-Type');
        var $msgContent = $('#msgContent');
        var $itemText = $('#infoSource');
        var $topRight = $('#topRight');
        var $window_client_info = $("#window_client_info");
        var $rval_1 = $("#rval_1");

        // NEW
        (function() {

            function country_showtime(country, f_replace) {
                var timezone = [];
                timezone = country2timezone(country);
                if (timezone[0]) {
                    $("#msgHisContents").find('.utime').each(function(index, element) {
                        var _utime = moment.tz($(element).data('utime') * 1000, timezone[0]);
                        if (f_replace || $(element).html() == '') {
                            $(element).html(' [' + timezone[1] + lang.msgdetail_biz.time + ':' + _utime.format('YYYY-MM-DD hh:mm') + ']').show();
                        }
                    });
                }
            }

            // msgHisContents
            // ?r=api/GetMsgTexts&msgid=87770
            $.get('?r=api/GetMsgTexts', {
                msgid: msgId
            }, function(data, textStatus) {
                if (textStatus == 'success') {
                    if (data.Ack == 'Success') {
                        $("#t_subject").html(data.Body.Subject);
                        document.title = data.Body.Subject;
                        defaultHeight();

                        var orderId = '';

                        var clientId = '';

                        var OmsgId = msgId;

                        ResponseEnabled = data.Body.ResponseEnabled;

                        (function() {
                            if (data.Body.FolderID == 0) {
                                clientId = data.Body.Sender;
                            } else if (data.Body.FolderID == 1) {
                                clientId = data.Body.SendToName;
                            } else {
                                // TODO
                                clientId = data.Body.Sender;
                            }

                            (function() {
                                var _html;
                                // var clientId2=(data.Body.user_id?clientId.replace(/.{4}(.*)/,'****$1'):clientId);
                                var clientId2 = clientId;
                                var _lang = lang.msgdetail_biz.window_client_info;
                                _html =
                                    '<p><span class="leftName">BuyerUserID：</span><span class="rightVal"><a href="http://www.ebay.com/usr/' +
                                    clientId2 + '" target="_blank" class="fontLinkBtn">' + clientId2 +
                                    '</a> <span style="display:none" t="sc"></span></span></p>';
                                _html += '<p style="display:none" t="email"><span class="leftName">' + _lang.email +
                                    '</span><span class="rightVal"></span></p>';
                                _html += '<p style="display:none" t="paypalid"><span class="leftName">' + _lang.paypal_id +
                                    '</span><span class="rightVal"></span></p>';
                                _html += '<p style="display:none" t="n"><span class="leftName">' + _lang.buyer_name +
                                    '</span><span class="rightVal"></span></p>';
                                _html += '<p style="display:none" t="addr"><span class="leftName">' + _lang.address +
                                    '</span><span class="rightVal"></span></p>';
                                _html += '<p style="display:none" t="paypal_addr"><span class="leftName">' + _lang.pp_address +
                                    '</span><span class="rightVal"></span></p>';
                                _html += '<p style="display:none" t="phone"><span class="leftName">' + _lang.phone +
                                    '</span><span class="rightVal"></span></p>';
                                _html += '<p style="display:none" t="PayPalphone"><span class="leftName">' + _lang.pp_phone +
                                    '</span><span class="rightVal"></span></p>';
                                _html += '<p style="display:none" t="nt"><span class="leftName">' + _lang.order_msg +
                                    '</span><span class="rightVal"></span></p>';
                                $rval_1.html(_html);

                                $window_client_info.find('span[t="id"]').html('<a href="http://www.ebay.com/usr/' + clientId2 +
                                    '" target="_blank" class="fontLinkBtn">' + clientId2 + '</a>');
                            })();
                        })();

                        if (typeof data.Body.Contents !== 'undefined') {
                            var _chtml = '';
                            var _msgmd5s = [];
                            var _userId = '';
                            var _rcount = 0,
                                _scount = 0;
                            for (var x in data.Body.Contents) {
                                var _tempEm = document.createElement("div");
                                _tempEm.innerHTML = data.Body.Contents[x].effect_content;
                                // _tempEm.insertAdjacentHTML('beforeend','<vv><ff><hh><jj><kk>'+data.Body.Contents[x].effect_content);
                                // console.log(_tempEm.innerHTML);
                                if (data.Body.Contents[x].FolderID == 1) {
                                    _chtml += '<li class="sellerMSG"><div class="MSGC">' + '<p><b>[ ' + data.Body.Contents[x].Sender +
                                        ' ]</b>&nbsp;<span style="display:none" class="CSName" data-content_md5="' + data.Body.Contents[x].content_md5 +
                                        '"></span>&nbsp;<span>' + intToLocalDate(data.Body.Contents[x].ReceiveDate, 3) + '</span>' +
                                        '<small class="utime" style="display:none;" data-utime="' + data.Body.Contents[x].ReceiveDate +
                                        '"></small></p>' + '<div class="ef_content">' + _tempEm.innerHTML + '</div>' + data.Body.Contents[x].ImagePreview +
                                        '</div></li>';
                                    _msgmd5s.push(data.Body.Contents[x].content_md5);
                                    if (_userId == '') {
                                        _userId = data.Body.Contents[x].SendToName;
                                    }
                                    _scount++;
                                } else {
                                    _chtml += '<li class="buyerMSG"><div class="MSGC">';
                                    _chtml += '<p><b>' + data.Body.Contents[x].Sender + '</b>&nbsp;&nbsp;<span>';
                                    _chtml += intToLocalDate(data.Body.Contents[x].ReceiveDate, 3) + '</span>';
                                    _chtml += '<small class="utime" style="display:none;" data-utime="' + data.Body.Contents[x].ReceiveDate +
                                        '"></small>';
                                    // 不需处理按钮
                                    _chtml += ' <a href="javascript:;" data-msgid="' + data.Body.Contents[x].msg_id +
                                        '" class="set_handled fontLinkBtn"';
                                    if (data.Body.Contents[x].handled == 0) {} else {
                                        _chtml += ' style="display:none;"';
                                    }
                                    _chtml += '>';
                                    _chtml += lang.msgdetail_biz.not_action + '</a>';
                                    // 需处理按钮
                                    _chtml += ' <a href="javascript:;" data-msgid="' + data.Body.Contents[x].msg_id +
                                        '" class="set_not_handled fontLinkBtn"';
                                    if (data.Body.Contents[x].handled == 1) {} else {
                                        _chtml += ' style="display:none;"';
                                    }
                                    _chtml += '>';
                                    _chtml += lang.msgdetail_biz.need_action + '</a>';
                                    // _chtml += ' <a href="?r=home/MsgDetail&mids=' + data.Body.Contents[x].msg_id + '&class=member" style="font-size: 10px; color: #A0A0A0;">' + lang.msgdetail_biz.msg_jump + '</a>';
                                    _chtml += '</p>';
                                    _chtml += '<div class="ef_content">' + _tempEm.innerHTML + '</div>' + data.Body.Contents[x].ImagePreview +
                                        '</div></li>';
                                    _rcount++;
                                }

                                if (typeof data.Body.Contents[x].OrderId != 'undefined' && data.Body.Contents[x].OrderId != '' && orderId.length ==
                                    0) {
                                    orderId = data.Body.Contents[x].OrderId;
                                    OmsgId = data.Body.Contents[x].msg_id;
                                }

                                for (var y in data.Body.Contents[x].msgLabels) {
                                    if ($("#msg_labels").find('[data-labelid="' + data.Body.Contents[x].msgLabels[y].msg_label_id + '"]').length >
                                        0) {
                                        continue;
                                    }
                                    var _lhtml = '<div class="nui-tag nui-tag' + data.Body.Contents[x].msgLabels[y].label_color +
                                        '"> <span class="nui-tag-text">' + data.Body.Contents[x].msgLabels[y].label_title +
                                        '</span> <span class="nui-tag-close" title="' + lang.msgdetail_biz.delete_label + '" data-labelid="' +
                                        data.Body.Contents[x].msgLabels[y].msg_label_id + '"><b>x</b></span> </div>';
                                    $("#msg_labels").append(_lhtml);
                                }
                            }
                            $("#msgHisContents").html(_chtml);
                            $("#msgHisContents .MSGC .ef_content").find('font:last').css("font-weight", "bold");
                            // $("#msgHisContents .MSGC").find('font:last').css("font-weight","bold");
                            $("#rscount").html(lang.msgdetail_biz.msg_receive + ':' + _rcount + ' ' + lang.msgdetail_biz.msg_send + ':' +
                                _scount).show();
                            defaultHeight();

                            $("#msgHisContents").find('img').each(function(index, element) {
                                if ($(element).attr('src') !== undefined && $(element).attr('src').indexOf('button') == -1 && $(element)
                                    .attr('src').indexOf('ebaystatic') == -1 && $(element).attr('src').indexOf('/globalAssets/') == -1 &&
                                    $(element).attr('src').indexOf('/icon/') == -1 && $(element).attr('src').indexOf('/roveropen/') ==
                                    -1) {
                                    $(element).closest('a').attr('data-lightbox', 'imgGroup11');
                                    var imgurl = $(element).attr('src').toString().replace(
                                        /%24_\d+(?=\.(jpg|png|jpeg|gif|bmp|tif)\??)/i, '$_10');
                                    $(element).closest('a').attr('href', imgurl);
                                }
                            });

                            var reg = /\/[a-zA-Z]{2}\/buttons\/btnRespond\.gif|\/[a-zA-Z]{2}\/btnViewDetails\.gif/g;
                            $("#msgHisContents").find('img').each(function(index, element) {
                                var res = $(element).attr('src').match(reg);
                                if (res != null) {
                                    $(element).closest('div').hide();
                                }
                            });

                            // ?r=api/GetMsgTextsCS&md5s=a05e2a5e49382bfb60a9aedbfa61c575,eb33c01629e4ca7781437fa85d25e772&userId=tekono1
                            _msgmd5s = _msgmd5s.join();
                            $.post('?r=api/GetMsgTextsCS', {
                                md5s: _msgmd5s,
                                userId: _userId
                            }, function(data, textStatus) {
                                if (textStatus == 'success') {
                                    if (data.Ack == 'Success') {
                                        for (var x in data.Body) {
                                            $("#msgHisContents").find('.CSName').each(function(index, element) {
                                                if ($(element).data('content_md5') == data.Body[x].content_md5) {
                                                    $(element).html(data.Body[x].action_username).show();
                                                }
                                            });
                                        }
                                    } else {
                                        // hintShow('hint_f','服务器内部错误!请联系管理员');
                                    }
                                } else {
                                    // 网络错误
                                    hintShow('hint_f', lang.ajaxinfo.network_error_s);
                                }
                            });

                            // TODO XXX
                            for (var x in data.Body.Contents) {
                                if (data.Body.Contents[x].FolderID != 1) {
                                    $("#emailDetail").attr('mid', data.Body.Contents[x].msg_id);
                                    break;
                                }
                            }

                            // 时间显示问题
                            if (data.Body.regaddr_Country) {
                                country_showtime(data.Body.regaddr_Country, true);
                            };

                            // A标签新选项卡
                            $("#msgHisContents").find('a').each(function(index, element) {
                                if ($(element).attr('href') !== undefined && $(element).attr('href').indexOf('http://') != -1) {
                                    $(element).attr('target', '_blank');
                                }
                            });

                            // 下面留点空白
                            $("#msgHisContents").css("margin-bottom", "12px");
                            $("#msgHisContents").css("word-break", "break-all");
                        } else if (typeof data.Body.Content !== 'undefined') {

                            (function() {
                                var _lang = lang.msgdetail_biz.email_head;
                                var template = '<div class="emailHead">' + '<p class="fixP"><span class="spanMode span80 leftName">' +
                                    _lang.sender + '</span><span class="rightVal">' + data.Body.Sender + '</span></p>' +
                                    '<p class="fixP"><span class="spanMode span80 leftName">' + _lang.received +
                                    '</span><span class="rightVal">' + data.Body.SendToName + '</span></p>' +
                                    '<p class="fixP"><span class="spanMode span80 leftName">' + _lang.time +
                                    '</span><span class="rightVal">' + intToLocalDate(data.Body.ReceiveDate, 3) + '</span></p></div>';
                                $msgContent.append(template);
                            })();

                            $msgContent.append(data.Body.Content.Text);

                            $msgContent.find('img[src="http://q.ebaystatic.com/aw/pics/buttons/emails/btnViewDetails.gif"]').closest('td').hide();

                            $("#msgContent").find('img').each(function(index, element) {
                                if ($(element).attr('src') !== undefined && $(element).attr('src').indexOf('button') == -1 && $(element)
                                    .attr('src').indexOf('ebaystatic') == -1 && $(element).attr('src').indexOf('/globalAssets/') == -1 &&
                                    $(element).attr('src').indexOf('/icon/') == -1 && $(element).attr('src').indexOf('/roveropen/') ==
                                    -1) {
                                    $(element).parent('a').attr('data-lightbox', 'imgGroup12');
                                    var imgurl = $(element).attr('src').toString().replace(
                                        /(%24|\$)_\d+(?=\.(jpg|png|jpeg|gif|bmp|tif)\??)/i, '$_10');
                                    $(element).parent('a').attr('href', imgurl);
                                }
                            });

                            var reg = /\/[a-zA-Z]{2}\/buttons\/btnRespond\.gif|\/[a-zA-Z]{2}\/btnViewDetails\.gif/g;
                            $("#msgContent").find('img').each(function(index, element) {
                                var res = $(element).attr('src').match(reg);
                                if (res != null) {
                                    $(element).closest('div').hide();
                                }
                            });

                            // A标签新选项卡
                            $msgContent.find('a').each(function(index, element) {
                                if ($(element).attr('href') !== undefined && $(element).attr('href').indexOf('http://') != -1) {
                                    $(element).attr('target', '_blank');
                                }
                            });

                            $msgContent.find('>#Header').each(function(index, element) {
                                $(element).hide();
                            });

                            $msgContent.find('>#Title').each(function(index, element) {
                                $(element).hide();
                            });

                            $msgContent.find('>#ReferenceId').each(function(index, element) {
                                $(element).hide();
                            });

                            $msgContent.find('>#Footer').each(function(index, element) {
                                $(element).hide();
                            });

                            $msgContent.find('>#MarketSaftyTip').each(function(index, element) {
                                $(element).hide();
                            });

                            $msgContent.find('#version3').each(function(index, element) {
                                $(element).hide();
                            });

                            $msgContent.find('.FooterSeparator').hide().next('table').hide();

                            // 系统消息右侧产品图片显示
                            (function() {
                                if ($("#itemDetailsComponent").length > 0) {
                                    if ($("#itemDetailsComponent > table table td div table td div > a > img").attr('src').length > 0) {
                                        $("#info_item_img").find('img').attr('src', $(
                                            "#itemDetailsComponent > table table td div table td div > a > img").attr('src')).parent().show();
                                    }
                                }
                            })();

                            for (var y in data.Body.msgLabels) {
                                if ($("#msg_labels").find('[data-labelid="' + data.Body.Contents[x].msgLabels[y].msg_label_id + '"]').length >
                                    0) {
                                    continue;
                                }
                                if ($("#msg_labels").find('[data-labelid="' + data.Body.msgLabels[y].msg_label_id + '"]').length == 0) {
                                    var _lhtml = '<div class="nui-tag nui-tag' + data.Body.msgLabels[y].label_color +
                                        '"> <span class="nui-tag-text">' + data.Body.msgLabels[y].label_title +
                                        '</span> <span class="nui-tag-close" title="' + lang.msgdetail_biz.delete_label + '" data-labelid="' +
                                        data.Body.msgLabels[y].msg_label_id + '"><b>x</b></span> </div>';
                                    $("#msg_labels").append(_lhtml);
                                }
                            }
                        } else {
                            // nothing
                        }

                        // 获取买家对卖家的所有评价
                        (function() {
                            $.get('?r=api/GetBuyerToSellerFeedbackInfo', {
                                userId: data.Body.UserID
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
                                            $window_client_info.find('span[t="bNegative"]').parent().html(lang.msgdetail_biz.not_feedback)
                                                .show();
                                        }
                                    } else {
                                        // hintShow('hint_f','服务器内部错误!请联系管理员');
                                    }
                                } else {
                                    // 网络错误
                                    // hintShow('hint_f','网络错误!');
                                }
                            });
                        })();

                        (function() {
                            // ItemURL绑定
                            if (typeof data.Body.ItemUrl !== 'undefined' && data.Body.ItemUrl != '') {
                                $("#item_title_p").find('a').attr('href', data.Body.ItemUrl);
                            } else {
                                $("#item_title_p").find('a').attr('href', 'http://cgi.ebay.com/ws/eBayISAPI.dll?ViewItem&item=' + (data.Body
                                    .user_id ? data.Body.ItemID.replace(/(\d{8})\d{4}/, '$1****') : data.Body.ItemID));
                            }
                        })();

                        (function() {
                            var _postdata = {
                                BuyerUserID: data.Body.UserID
                            };
                            if (data.Body.EIASToken.length > 10) {
                                _postdata = {
                                    EIASToken: data.Body.EIASToken
                                };
                            }
                            $.get('?r=api/GetEbayTransactionsByUserId', _postdata, function(data, textStatus) {
                                if (textStatus == 'success') {
                                    if (data.Ack === 'Success') {
                                        $("#buyHistory_div").find('tbody').empty();
                                        if (data.Body.length > 0) {
                                            for (var x in data.Body) {
                                                var transStr = '';
                                                var _lang = lang.msgdetail_biz.transactions_info;
                                                for (var y in data.Body[x].ExtTrans) {
                                                    transStr += ('<div class="textleft tooltip_td" title="' + _lang.time +
                                                        intToLocalDate(data.Body[x].ExtTrans[y].ExternalTransactionTime, 3) +
                                                        '<br />' + _lang.amount + data.Body[x].ExtTrans[y].PaymentOrRefundAmount +
                                                        data.Body[x].ExtTrans[y].PaymentOrRefundAmount_currencyID + '' +
                                                        '<br />' + _lang.FVF + data.Body[x].ExtTrans[y].FeeOrCreditAmount +
                                                        data.Body[x].ExtTrans[y].FeeOrCreditAmount_currencyID + '' + '<br />' +
                                                        _lang.status + data.Body[x].ExtTrans[y].ExternalTransactionStatus + '' +
                                                        '">' + data.Body[x].ExtTrans[y].ExternalTransactionID + '(' + data.Body[
                                                            x].ExtTrans[y].PaymentOrRefundAmount + data.Body[x].ExtTrans[y].PaymentOrRefundAmount_currencyID +
                                                        ')' + '</div>');
                                                }

                                                // 属性
                                                var _shuxing = '';

                                                // console.log(data.Body[x].VariationSpecificsXML.xml.NameValueList);

                                                if (
                                                    typeof data.Body[x].VariationSpecificsXML.xml.NameValueList !== 'undefined' &&
                                                    typeof data.Body[x].VariationSpecificsXML.xml.NameValueList !== 'string' &&
                                                    (data.Body[x].VariationSpecificsXML.xml.NameValueList.length > 0 || (
                                                        typeof data.Body[x].VariationSpecificsXML.xml.NameValueList.Name ===
                                                        'string' &&
                                                        data.Body[x].VariationSpecificsXML.xml.NameValueList.Name.length > 0
                                                    ))) {
                                                    if (data.Body[x].VariationSpecificsXML.xml.NameValueList.length > 0) {
                                                        for (var j = 0; j < data.Body[x].VariationSpecificsXML.xml.NameValueList.length; j++) {
                                                            _shuxing += '<br/>' + data.Body[x].VariationSpecificsXML.xml.NameValueList[
                                                                j].Name + ':' + data.Body[x].VariationSpecificsXML.xml.NameValueList[
                                                                j].Value;
                                                        }
                                                    } else {
                                                        _shuxing += '<br/>' + data.Body[x].VariationSpecificsXML.xml.NameValueList.Name +
                                                            ':' + data.Body[x].VariationSpecificsXML.xml.NameValueList.Value;
                                                    }
                                                }

                                                var addressStr = '';
                                                addressStr += data.Body[x].Name + ', ';
                                                addressStr += data.Body[x].Street1 + ', ';
                                                if (data.Body[x].Street2 && data.Body[x].Street2.length > 0) {
                                                    addressStr += data.Body[x].Street2 + ', ';
                                                }
                                                addressStr += data.Body[x].CityName + ', ';
                                                addressStr += data.Body[x].StateOrProvince + ', ';
                                                addressStr += data.Body[x].PostalCode + ', ';
                                                addressStr += data.Body[x].CountryName;
                                                (!data.Body[x].Phone && data.Body[x].Phone == '') ? null: addressStr += '<br/>' +
                                                    lang.msgdetail_biz.phone + ':' + data.Body[x].Phone;

                                                var orderStatus = '';
                                                if (data.Body[x].OrderStatus) {
                                                    orderStatus = data.Body[x].OrderStatus;
                                                }

                                                var CommentTypeStr = '';
                                                if (data.Body[x].CommentType == 'Positive') {
                                                    CommentTypeStr = '<i class="iconPos"></i>';
                                                } else if (data.Body[x].CommentType == 'Neutral') {
                                                    CommentTypeStr = '<i class="iconNeu"></i>';
                                                } else if (data.Body[x].CommentType == 'Negative') {
                                                    CommentTypeStr = '<i class="iconNeg"></i>';
                                                } else {
                                                    CommentTypeStr = lang.msgdetail_biz.none;
                                                }

                                                $("#buyHistory_div").find('tbody').append('<tr data-key=' + x + '><td>' + data.Body[
                                                        x].SellerUserID + '</td><td t="sku" style="word-break:break-all;">' + (
                                                        data.Body[x].Item_SKU != '' ? HTMLDecode(HTMLDecode(data.Body[x].Item_SKU)) :
                                                        HTMLDecode(HTMLDecode(data.Body[x].Variation_SKU))) +
                                                    '<br />ItemID:<a href="http://cgi.ebay.com/ws/eBayISAPI.dll?ViewItem&item=' +
                                                    data.Body[x].Item_ItemID + '" target="_blank" class="fontLinkBtn">' + data.Body[
                                                        x].Item_ItemID + '</a>' + (data.Body[x].ProductName.length > 0 ? (
                                                        '<br />Prodtct:' + HTMLDecode(HTMLDecode(data.Body[x].ProductName))
                                                    ) : '') + _shuxing + '</td><td t="pnum">' + data.Body[x].QuantityPurchased +
                                                    '</td><td t="pjg">' + data.Body[x].TransactionPrice + data.Body[x].TransactionPrice_currencyID +
                                                    '</td><td t="ptime" style="word-break:break-all;line-height:1.1em;">' +
                                                    intToLocalDate(data.Body[x].CreatedDate, 8) +
                                                    '</td><td t="pm" style="line-height:1.5em;">' + data.Body[x].PaymentMethod +
                                                    transStr + '</td>' + (data.Body[x].AddressID ?
                                                        '<td><a href="javascript:;" title="' + addressStr +
                                                        '" class="tooltip_td">' + lang.msgdetail_biz.view + '</a></td>' :
                                                        '<td title="' + lang.msgdetail_biz.none + '">-</td>') + '<td>' +
                                                    orderStatus + '</td>' + '<td>' + CommentTypeStr + '</td>' +
                                                    '<td t="note" class="tooltip_td">..</td></tr>');

                                                (function() {
                                                    // 获取item备注
                                                    // Guest 无法正常显示TODO
                                                    var _key = x;
                                                    $.get('?r=api/GetItemNotes', {
                                                        itemId: data.Body[x].Item_ItemID
                                                    }, function(data, textStatus) {
                                                        if (data.Ack == 'Success') {
                                                            var noteText = [];
                                                            var _lang = lang.msgdetail_biz.Notes;
                                                            for (var x in data.Body) {
                                                                noteText[x] = (_lang.author + data.Body[x].author_name +
                                                                    '<br/>' + _lang.content + data.Body[x].text +
                                                                    '<br/>' + _lang.buyer + data.Body[x].cust +
                                                                    '<br/>' + _lang.time + intToLocalDate(data.Body[
                                                                        x].create_time, 7));
                                                            }
                                                            $("#buyHistory_div").find('tr[data-key=' + _key + ']').find(
                                                                    'td[t="note"]').html('<i class="icon-remark"></i>')
                                                                .attr('title', noteText.join('<br /><br />'));
                                                            //执行鼠标经过气泡提示方法
                                                            tooltip($('.tooltip_td'));
                                                        } else if (data.Ack == 'Warning') {
                                                            $("#buyHistory_div").find('tr[data-key=' + _key + ']').find(
                                                                'td[t="note"]').html(lang.msgdetail_biz.none);
                                                        } else {
                                                            $("#buyHistory_div").find('tr[data-key=' + _key + ']').find(
                                                                'td[t="note"]').html('✖');
                                                        }
                                                    });
                                                })();

                                                $(function() {
                                                    //执行鼠标经过气泡提示方法
                                                    tooltip($('.tooltip_td'));
                                                });
                                            }
                                        } else {
                                            $("#buyHistory_div").find('tbody').append('<tr><td colspan="10">' + lang.msgdetail_biz.not_data +
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

                        (function() {

                            if (orderId.length == 0) {

                                (function() {
                                    // 没有订单号的时候显示itemSite
                                    $.get('?r=api/GetItemInfoByItemId', {
                                        'msgid': msgId
                                    }, function(d, status) {
                                        if (status === 'success' && d.Ack === 'Success') {
                                            $("#info_item_location").find('.rightVal').html(d.Body.Site).parent().show();
                                        }
                                    });
                                })();

                                return;
                            }

                            // 如果有orderId

                            $.get("?r=api/GetEbayTransactionInfo", {
                                OrderLineItemID: orderId
                            }, function(data, status) {
                                if (status === 'success') {
                                    if (data.Ack === 'Success') {
                                        var $msg_orders_list = $("#msg_orders_list");
                                        $msg_orders_list.find('tbody').empty();
                                        for (var i = 0; i < data.Body.length; i++) {
                                            // TODO
                                            if (data.Body[i].Buyer_Email != '' && data.Body[i].Buyer_Email != 'Invalid Request') {
                                                $rval_1.find('p[t="email"]>.rightVal').html(data.Body[i].Buyer_Email).parent('p').show();
                                                $window_client_info.find('span[t="mail"]').html(data.Body[i].Buyer_Email);
                                            } else if (data.Body[i].Buyer_StaticAlias != '') {
                                                // $rval_1.find('p[t="p"]>.rightVal').html(data.Body[i].Buyer_StaticAlias).parent('p').show();
                                                $window_client_info.find('span[t="mail"]').html(data.Body[i].Buyer_StaticAlias);
                                            }
                                            $rval_1.find('p[t="n"]>.rightVal').html(data.Body[i].Buyer_UserFirstName + ' ' + data.Body[
                                                i].Buyer_UserLastName).parent('p').show();
                                            $window_client_info.find('span[t="name"]').html(data.Body[i].Buyer_UserFirstName + ' ' +
                                                data.Body[i].Buyer_UserLastName);
                                            // item title
                                            if (data.Body[i].Item_Title != '') {
                                                $("#item_title_p").find('a').html(data.Body[i].Item_Title);
                                            }
                                            // itemID
                                            if (data.Body[i].Item_ItemID != '') {
                                                $("#info_item_itemid").find('.rightVal').html(data.Body[i].Item_ItemID).parent().show();
                                            }

                                            // SKU
                                            var _sku = '';
                                            if (data.Body[i].Item_SKU != '') {
                                                _sku = 'SKU:' + HTMLDecode(HTMLDecode(data.Body[i].Item_SKU)) + '<br/>';
                                            }
                                            if (data.Body[i].Variation_SKU != '') {
                                                _sku = '<i title="' + lang.msgdetail_biz.multi_attr + '">SKU:' + HTMLDecode(
                                                    HTMLDecode(data.Body[i].Variation_SKU)) + '</i>' + '<br/>';
                                            }

                                            // 属性
                                            var _shuxing = [];

                                            if (
                                                typeof data.Body[i].VariationSpecificsXML.xml.NameValueList !== 'undefined' &&
                                                typeof data.Body[i].VariationSpecificsXML.xml.NameValueList !== 'string' &&
                                                (data.Body[i].VariationSpecificsXML.xml.NameValueList.length > 0 || (
                                                    typeof data.Body[i].VariationSpecificsXML.xml.NameValueList.Name ===
                                                    'string' &&
                                                    data.Body[i].VariationSpecificsXML.xml.NameValueList.Name.length > 0
                                                ))) {
                                                if (data.Body[i].VariationSpecificsXML.xml.NameValueList.length > 0) {
                                                    for (var j = 0; j < data.Body[i].VariationSpecificsXML.xml.NameValueList.length; j++) {
                                                        _shuxing.push(data.Body[i].VariationSpecificsXML.xml.NameValueList[j].Name +
                                                            ':' + data.Body[i].VariationSpecificsXML.xml.NameValueList[j].Value
                                                        );
                                                    }
                                                } else {
                                                    _shuxing.push(data.Body[i].VariationSpecificsXML.xml.NameValueList.Name + ':' +
                                                        data.Body[i].VariationSpecificsXML.xml.NameValueList.Value);
                                                }
                                            }

                                            _shuxing = _shuxing.join('<br/>');

                                            var _td = '<tr>';
                                            _td += '<td>' + (data.Body[i].user_id ? data.Body[i].OrderLineItemID.replace(
                                                /(\d{8})\d{4}/, '$1**** ') : data.Body[i].OrderLineItemID) + '</td>';
                                            _td += '<td style="line-height:1.5em;">';
                                            _td += _sku;
                                            _td += (data.Body[i].ProductName.length > 0 ? ('Product:' + HTMLDecode(HTMLDecode(data.Body[
                                                i].ProductName)) + '<br/>') : '');
                                            _td += _shuxing;
                                            _td += '</td>';
                                            _td += '<td>' + data.Body[i].QuantityPurchased + '</td>';
                                            _td += '<td>' + data.Body[i].TransactionPrice + data.Body[i].TransactionPrice_currencyID +
                                                '</td>';
                                            _td += '<td style="line-height:1.1em;">' + intToLocalDate(data.Body[i].CreatedDate, 3) +
                                                '</td>';
                                            _td += '</tr>';

                                            $msg_orders_list.append(_td);

                                            // TODO
                                            // console.log(data.Body[i].Item_AttributeArrayXML);

                                            $("#info_item_location").find('.rightVal').html(data.Body[i].Item_Site).parent().show();

                                            $msg_orders_list.show();
                                        }
                                    } else {
                                        // hintShow('hint_f','服务器错误!');
                                    }
                                } else {
                                    hintShow('hint_f', lang.ajaxinfo.network_error_s);
                                }
                            });

                            $.get("?r=api/GetEbayOrderInfo", {
                                OrderLineItemID: orderId
                            }, function(data, status) {
                                if (status === 'success') {
                                    if (data.Ack === 'Success') {
                                        var addrStr = '';
                                        addrStr += data.Body.Name + ', ';
                                        addrStr += data.Body.Street1 + ', ';
                                        addrStr += (typeof data.Body.Street2 !== 'undefined' && data.Body.Street2 !== null && data.Body
                                            .Street2.length > 0 ? (data.Body.Street2 + ', ') : '');
                                        addrStr += data.Body.CityName + ', ';
                                        addrStr += data.Body.StateOrProvince + ', ';
                                        addrStr += (typeof data.Body.PostalCode !== 'undefined' && data.Body.PostalCode !== null &&
                                            data.Body.PostalCode.length > 0 ? data.Body.PostalCode + ', ' : '');
                                        // +(typeof data.Body.Country!=='undefined'
                                        //     && data.Body.Country.length>0
                                        //     ?data.Body.Country:'')
                                        addrStr += (typeof data.Body.CountryName !== 'undefined' && data.Body.CountryName !== null &&
                                            data.Body.CountryName.length > 0 ? data.Body.CountryName : '');
                                        if (data.Body.Country) {
                                            country_showtime(data.Body.Country);
                                        };

                                        if (addrStr.length > 37) {
                                            // TODO
                                            $rval_1.find('p[t="addr"]>.rightVal').html(addrStr).parent('p').show();
                                        }
                                        if (typeof data.Body.Phone !== 'undefined' && data.Body.Phone != 'Invalid Request' && data.Body
                                            .Phone && data.Body.Phone.length > 0) {
                                            $rval_1.find('p[t="phone"]>.rightVal').html(data.Body.Phone).parent('p').show();
                                        }

                                        if (data.Body.BuyerCheckoutMessage != '' && data.Body.BuyerCheckoutMessage !== null) {
                                            $rval_1.find('p[t="nt"]>.rightVal').html(data.Body.BuyerCheckoutMessage).parent('p').show();
                                        }

                                        var transids = '';
                                        var _ShippingInfo = '<div>' + ((data.Body.ShippingServiceDetails === false) ?
                                                data.Body.ShippingService : data.Body.ShippingServiceDetails.Description) +
                                            '</div>';

                                        if (data.Body.ShippingServiceCost > 0) {
                                            // ShippingServiceCost
                                            _ShippingInfo += '<div>' + lang.msgdetail_biz.order_info.carriage + ':' + data.Body.ShippingServiceCost +
                                                data.Body.ShippingServiceCost_currencyID + '</div>';
                                        }

                                        if (data.Body.ExtTrans.length > 0) {
                                            var _lang = lang.msgdetail_biz.transactions_info;
                                            for (var i = 0; i < data.Body.ExtTrans.length; i++) {
                                                transids += ('<div class="textleft tooltip_td" title="' + _lang.time +
                                                    intToLocalDate(data.Body.ExtTrans[i].ExternalTransactionTime, 3) + '<br />' +
                                                    _lang.amount + data.Body.ExtTrans[i].PaymentOrRefundAmount + data.Body.ExtTrans[
                                                        i].PaymentOrRefundAmount_currencyID + '' + '<br />' + _lang.FVF + data.Body
                                                    .ExtTrans[i].FeeOrCreditAmount + data.Body.ExtTrans[i].FeeOrCreditAmount_currencyID +
                                                    '' + '<br />' + _lang.status + data.Body.ExtTrans[i].ExternalTransactionStatus +
                                                    '' + '">' + data.Body.ExtTrans[i].ExternalTransactionID + '(' + data.Body.ExtTrans[
                                                        i].PaymentOrRefundAmount + data.Body.ExtTrans[i].PaymentOrRefundAmount_currencyID +
                                                    ')' + '</div>');

                                                (function() {
                                                    // 如果已经有内容了就不去获取了
                                                    // if($rval_1.find('p[t="paypal_addr"]>.rightVal').html().length > 15){
                                                    //     return;
                                                    // }

                                                    $.get('?r=api/GetPaypalAddressByExtTransID', {
                                                        TransactionID: data.Body.ExtTrans[i].ExternalTransactionID
                                                    }, function(data, status) {
                                                        // console.log(data);
                                                        if (data.Ack == 'Success') {
                                                            if (data.Body.code == 200) {
                                                                if (data.Body.data.body.length > 0) {
                                                                    if (data.Body.data.body[0].BuyerEmail != '') {

                                                                        $('#window_client_info_r').find(
                                                                            'span[t="paypalid"]').find('span[x="' +
                                                                            data.Body.data.body[0].BuyerEmail +
                                                                            '"]').remove();
                                                                        $('#window_client_info_r').find(
                                                                            'span[t="paypalid"]').append(
                                                                            '<span x="' + data.Body.data.body[0].BuyerEmail +
                                                                            '">' + data.Body.data.body[0].BuyerEmail +
                                                                            '</span>').closest('li').show();
                                                                        $('#window_client_info_r').show();

                                                                        $rval_1.find('p[t="paypalid"]>.rightVal').find(
                                                                            'span[x="' + data.Body.data.body[0].BuyerEmail +
                                                                            '"]').remove();
                                                                        $rval_1.find('p[t="paypalid"]>.rightVal').append(
                                                                            '<span x="' + data.Body.data.body[0].BuyerEmail +
                                                                            '">' + data.Body.data.body[0].BuyerEmail +
                                                                            '</span>').parent('p').show();

                                                                        if (data.Body.data.body[0].Phone.length > 0) {
                                                                            $rval_1.find('p[t="PayPalphone"]>.rightVal')
                                                                                .append(data.Body.data.body[0].Phone +
                                                                                    ' ').parent('p').show();
                                                                        }

                                                                        if (data.Body.data.body[0].BuyerID != '') {
                                                                            var paypal_addrStr = '';
                                                                            paypal_addrStr += data.Body.data.body[0].BuyerName;
                                                                            paypal_addrStr += ', ' + data.Body.data.body[
                                                                                0].Street1;
                                                                            if (data.Body.data.body[0].Street2.length >
                                                                                0) {
                                                                                paypal_addrStr += ', ' + data.Body.data
                                                                                    .body[0].Street2;
                                                                            }
                                                                            paypal_addrStr += ', ' + data.Body.data.body[
                                                                                0].CityName;
                                                                            paypal_addrStr += ', ' + data.Body.data.body[
                                                                                0].Province;
                                                                            paypal_addrStr += ', ' + data.Body.data.body[
                                                                                0].PostCode;
                                                                            paypal_addrStr += ', ' + data.Body.data.body[
                                                                                0].CountryName;
                                                                            // $rval_1.find('p[t="paypal_addr"]>.rightVal').html(paypal_addrStr).parent('p').show();
                                                                            $rval_1.find('p[t="paypal_addr"]>.rightVal')
                                                                                .append(paypal_addrStr).parent('p').show();
                                                                        } else {

                                                                        }
                                                                    } else {

                                                                    }
                                                                } else {

                                                                }
                                                            } else {

                                                            }
                                                        } else {
                                                            // hintShow('hint_f','服务器错误!');
                                                        }
                                                    });
                                                })();

                                            }
                                        }

                                        if (data.Body.Trans.length > 0) {
                                            for (var i = 0; i < data.Body.Trans.length; i++) {
                                                if (typeof data.Body.Trans[i].ShippingDetailsXML !== 'undefined' && data.Body.Trans[
                                                        i].ShippingDetailsXML !== null && typeof data.Body.Trans[i].ShippingDetailsXML
                                                    .xml !== 'undefined' && typeof data.Body.Trans[i].ShippingDetailsXML.xml.ShipmentTrackingDetails !==
                                                    'undefined') {
                                                    _ShippingInfo += '<div>CarrierUsed:' + data.Body.Trans[i].ShippingDetailsXML.xml
                                                        .ShipmentTrackingDetails.ShippingCarrierUsed + '</div>';
                                                    _ShippingInfo += '<div>TrackingNumber:<br /><a href="#track_number=' + data.Body
                                                        .Trans[i].ShippingDetailsXML.xml.ShipmentTrackingDetails.ShipmentTrackingNumber +
                                                        '" t.arget="_blank" class="fontLinkBtn" id="open_tracking_details_btn" data-trackno="' +
                                                        data.Body.Trans[i].ShippingDetailsXML.xml.ShipmentTrackingDetails.ShipmentTrackingNumber +
                                                        '" data-carrier="' + data.Body.Trans[i].ShippingDetailsXML.xml.ShipmentTrackingDetails
                                                        .ShippingCarrierUsed + '">' + data.Body.Trans[i].ShippingDetailsXML.xml.ShipmentTrackingDetails
                                                        .ShipmentTrackingNumber + '</a></div>';
                                                }
                                            }
                                        }
                                        var $msg_payment = $("#msg_payment");
                                        $msg_payment.find('tbody').find('tr>td').eq(0).html(data.Body.PaymentMethod + transids);
                                        $msg_payment.find('tbody').find('tr>td').eq(1).html(_ShippingInfo);

                                        if (data.Body.ShippedTime == 0) {
                                            $msg_payment.find('tbody').find('tr>td').eq(2).html('-');
                                        } else {
                                            $msg_payment.find('tbody').find('tr>td').eq(2).html(intToLocalDate(data.Body.ShippedTime,
                                                3));
                                        }

                                        (function() {
                                            $.get('?r=api/GetEbayFeedbackInfoByOLID', {
                                                'OrderLineItemID': orderId
                                            }, function(d) {
                                                if (d.Ack == 'Failure') {
                                                    $msg_payment.find('tbody').find('tr>td').eq(3).html(lang.msgdetail_biz
                                                        .none);
                                                } else if (d.Ack == 'Success') {
                                                    var type;
                                                    switch (d.Body.CommentType) {
                                                        case 'Positive':
                                                            type = 'iconPos';
                                                            break;
                                                        case 'Negative':
                                                            type = 'iconNeg';
                                                            break;
                                                        case 'Neutral':
                                                            type = 'iconNeu';
                                                            break;
                                                        default:
                                                            type = 'iconNone';
                                                    }
                                                    $msg_payment.find('tbody').find('tr>td').eq(3).html('<i class="' +
                                                        type + '"></i>');
                                                }
                                            });
                                        })();

                                        $(document).ready(function(e) {
                                            $(function() {
                                                //执行鼠标经过气泡提示方法
                                                tooltip($('.tooltip_td'));
                                            });
                                        });

                                        $msg_payment.show();

                                    } else {
                                        // hintShow('hint_f','服务器错误!');
                                    }
                                } else {
                                    hintShow('hint_f', lang.ajaxinfo.network_error_s);
                                }
                            });
                        })();

                        (function() {
                            // ItemID显示
                            if (data.Body.ItemID != '') {
                                $("#info_item_itemid").find('.rightVal').html(data.Body.ItemID).parent().show();
                            }
                            // item title显示
                            $("#item_title_p").find('a').html(data.Body.ItemTitle);
                        })();

                        (function() {
                            // 获取用户信息
                            if (data.Body.Sender == 'eBay') {
                                return;
                            }

                            $.get("?r=api/GetEbayUserInfo", {
                                userid: clientId
                            }, function(data, status) {
                                if (status === 'success') {
                                    if (data.Ack === 'Success') {
                                        if (data.Body.FeedbackScore != '') {
                                            $rval_1.find('span[t="sc"]').html('(' + data.Body.FeedbackScore + ' / ' + data.Body.FeedbackRatingStar +
                                                ')' + ' ' + lang.msgdetail_biz.positive + ':' + data.Body.PositiveFeedbackPercent
                                            ).show();
                                        }
                                        $window_client_info.find('span[t="regaddr"]').html(data.Body.Site);
                                        if (data.Body.RegistrationDate > 0) {
                                            $window_client_info.find('span[t="RegistrationDate"]').html(intToLocalDate(data.Body.RegistrationDate,
                                                3)).parent().show();
                                        }
                                        if (data.Body.PositiveFeedbackPercent >= 0) {
                                            $window_client_info.find('span[t="PositiveFeedbackPercent"]').html(data.Body.PositiveFeedbackPercent)
                                                .parent().show();
                                        }
                                        if (data.Body.UniquePositiveFeedbackCount >= 0) {
                                            $window_client_info.find('span[t="Positive"]').html(data.Body.UniquePositiveFeedbackCount)
                                                .parent().show();
                                        }
                                        if (data.Body.UniqueNeutralFeedbackCount >= 0) {
                                            $window_client_info.find('span[t="Neutral"]').html(data.Body.UniqueNeutralFeedbackCount)
                                                .parent().show();
                                        }
                                        if (data.Body.UniqueNegativeFeedbackCount >= 0) {
                                            $window_client_info.find('span[t="Negative"]').html(data.Body.UniqueNegativeFeedbackCount)
                                                .parent().show();
                                        }

                                        (function() {
                                            var RaddrStr = '';

                                            RaddrStr += data.Body.regaddr_Name + ', ';
                                            RaddrStr += data.Body.regaddr_Street + ', ';
                                            RaddrStr += data.Body.regaddr_Street1 + ', ';
                                            data.Body.regaddr_Street2.length <= 0 ? '' : RaddrStr += data.Body.regaddr_Street2 +
                                                ', ';
                                            RaddrStr += data.Body.regaddr_CityName + ', ';
                                            data.Body.regaddr_StateOrProvince.length <= 0 ? '' : RaddrStr += data.Body.regaddr_StateOrProvince +
                                                ', ';
                                            RaddrStr += data.Body.regaddr_PostalCode + ', ';
                                            RaddrStr += data.Body.regaddr_CountryName;
                                            data.Body.regaddr_Phone.length <= 0 ? '' : RaddrStr += '<br />' + lang.msgdetail_biz
                                                .reg_phone + ': ' + data.Body.regaddr_Phone + ', ';

                                            if (RaddrStr.length > 12) {
                                                $("#window_client_info_l").append('<li style=""><span class="leftName">' + lang
                                                    .msgdetail_biz.reg_address +
                                                    '</span><span class="rightVal" style="width:370px;">' + RaddrStr +
                                                    '</span></li>');
                                            }
                                        })();

                                        (function() {
                                            // 屏蔽
                                            return;

                                            // 其他地址显示
                                            var pdata = {
                                                EIASToken: data.Body.EIASToken
                                            };
                                            if (data.Body.EIASToken == '') {
                                                pdata = {
                                                    userId: data.Body.UserID
                                                };
                                            }
                                            $.get('?r=api/GetUserAddress', pdata, function(data, textStatus) {
                                                if (textStatus == 'success') {
                                                    if (data.Ack == 'Success') {
                                                        for (var x in data.Body) {
                                                            var addrStr = data.Body[x].Name + ', ' + data.Body[x].Street1 +
                                                                ', ' + (data.Body[x].Street2.length > 0 ? (data.Body[x]
                                                                    .Street2 + ', ') : '') + data.Body[x].CityName +
                                                                ', ' + data.Body[x].StateOrProvince + ', ' + (typeof data
                                                                    .Body[x].PostalCode !== 'undefined' && data.Body[x]
                                                                    .PostalCode.length > 0 ? data.Body[x].PostalCode +
                                                                    ', ' : '')
                                                                // +(typeof data.Body[x].Country!=='undefined'
                                                                //     && data.Body[x].Country.length>0
                                                                //     ?data.Body[x].Country:'')
                                                                + (typeof data.Body[x].CountryName !== 'undefined' &&
                                                                    data.Body[x].CountryName.length > 0 ? data.Body[x].CountryName :
                                                                    '');
                                                            if (data.Body[x].Phone.length > 0) {
                                                                addrStr += ('<br />' + lang.msgdetail_biz.order_phone +
                                                                    data.Body[x].Phone);
                                                            }

                                                            $("#window_client_info_l").append(
                                                                '<li style=""><span class="leftName">' + lang.msgdetail_biz
                                                                .order_address + (+x + 1) +
                                                                '：</span><span class="rightVal" style="width:370px;">' +
                                                                addrStr + '</span></li>');
                                                        }
                                                    } else if (data.Ack == 'Warning') {

                                                    } else {
                                                        hintShow('hint_f', lang.ajaxinfo.internal_error_s);
                                                    }
                                                } else {
                                                    hintShow('hint_f', lang.ajaxinfo.network_error_s);
                                                }
                                            });
                                        })();

                                    } else {
                                        // hintShow('hint_f','服务器错误!');
                                    }
                                } else {
                                    hintShow('hint_f', lang.ajaxinfo.network_error_s);
                                }
                            });
                        })();

                        $.get('?r=api/GetMsgCaseInfo', {
                            msgid: OmsgId
                        }, function(data, status) {
                            // console.log(data,status);
                            if (data.Ack == 'Success' && status == 'success') {
                                var _chtml = '';
                                for (var x in data.Body.cases) {
                                    if (data.Body.cases[x].caseId_type != 'CANCEL_TRANSACTION') {
                                        _chtml += '<a href="?r=Home/DisputeDetail&caseid=' + data.Body.cases[x].case_id + '&type=' +
                                            data.Body.cases[x].caseId_type + '" target="_blank" class="fontLinkBtn">' + data.Body.cases[
                                                x].caseId_type + '</a> ';
                                    }
                                }
                                for (var x in data.Body.disputes) {
                                    if (data.Body.disputes[x].DisputeReason == 'TransactionMutuallyCanceled') {
                                        _chtml += '<a href="?r=Home/DisputeDetail&caseid=' + data.Body.disputes[x].disputes_id +
                                            '&type=cancel" target="_blank" class="fontLinkBtn">' + data.Body.disputes[x].DisputeReason +
                                            '</a> ';
                                    }
                                }
                                for (var x in data.Body.returns) {
                                    _chtml += '<a href="?r=Home/ReturnDetail&returnid=' + data.Body.returns[x].return_request_id +
                                        '" target="_blank">' + data.Body.returns[x].return_type + '</a> ';
                                }
                                $("#item_detail_div_br").append(_chtml);
                            }
                        });

                        // 隐藏备注输入框
                        if (data.Body.Sender == 'eBay') {
                            $('.remark').hide();
                        }

                    } else {
                        // hintShow('hint_f','服务器内部错误!请联系管理员');
                    }
                } else {
                    // 网络错误
                    hintShow('hint_f', lang.ajaxinfo.network_error_s);
                }
            });

            $("#msgHisContents").on('click', 'a.set_handled', function() {
                var _this = $(this);
                loading();
                $.get('?r=api/SetMsgHandled', {
                    'msgid': _this.data('msgid')
                }, function(data, status) {
                    removeloading();
                    if (status == 'success') {
                        if (data.Ack == 'Success') {
                            hintShow('hint_s', lang.msgdetail_biz.marked_handled);
                            _this.hide().parent().find('.set_not_handled').show();
                            try {
                                opener.window.refreshMsgList();
                            } catch (e) {}
                        } else {
                            hintShow('hint_f', lang.ajaxinfo.internal_error_s);
                        }
                    } else {
                        hintShow('hint_f', lang.ajaxinfo.network_error_s);
                    }
                });
            });
            $("#msgHisContents").on('click', 'a.set_not_handled', function() {
                var _this = $(this);
                loading();
                $.get('?r=api/SetMsgOnHand', {
                    'msgid': _this.data('msgid')
                }, function(data, status) {
                    removeloading();
                    if (status == 'success') {
                        if (data.Ack == 'Success') {
                            hintShow('hint_s', lang.msgdetail_biz.marked_not_handled);
                            _this.hide().parent().find('.set_handled').show();
                            try {
                                opener.window.refreshMsgList();
                            } catch (e) {}
                        } else {
                            hintShow('hint_f', lang.ajaxinfo.internal_error_s);
                        }
                    } else {
                        hintShow('hint_f', lang.ajaxinfo.network_error_s);
                    }
                });
            });

        })();

        //=========================鼠标移动到已添加的标签上显示删除标签按钮--linpeiyan--2015-8-25====================
        $('#msg_labels').on('mouseenter', '.nui-tag', function() {
            $(this).find('.nui-tag-close').stop().animate({
                width: 18
            }, 100);
        });

        $('#msg_labels').on('mouseleave', '.nui-tag', function() {
            $(this).find('.nui-tag-close').stop().animate({
                width: 0
            }, 100);
        });

        $('#msg_labels').on('click', '.nui-tag-close', function() {
            var gd = {
                labelid: $(this).data('labelid'),
                msgid: $("#emailDetail").attr('mid')
            }
            var _self = $(this);
            loading();
            $.get('?r=api/RemoveMsgLabel', gd, function(data, status) {
                removeloading();
                if (status == 'success' && data.Ack == 'Success') {
                    hintShow('hint_s', lang.msgdetail_biz.label_was_delete);
                    _self.closest('.nui-tag').remove();
                    opener.window.refreshMsgList();
                } else {
                    if (data.Error == 'User authentication fails') {
                        hintShow('hint_w', lang.ajaxinfo.permission_denied);
                    }
                }
            });
        })

        $.get('?r=api/ShowMessage&mids=' + msgId + '&c=' + msgType, {
            _rnd: loading()
        }, function(result, status) {
            // return;
            removeloading();
            if (result.Ack == 'Success') {
                var CC = result.Body.content;

                // $("#t_subject").html(CC.Subject);
                defaultHeight();

                var clientId = '';

                (function() {
                    if (CC.FolderID == 0) {
                        clientId = CC.Sender;
                    } else if (CC.FolderID == 1) {
                        clientId = CC.SendToName;
                    } else {
                        // TODO
                        clientId = CC.Sender;
                    }
                })();

                (function() {
                    global_itemID = CC.ItemID;
                })();

                (function() {
                    // Item图片显示
                    if (typeof CC.img !== 'undefined' && CC.img != '') {
                        var $info_item_img = $("#info_item_img");
                        $info_item_img.attr('href', CC.img);
                        $info_item_img.find('img').attr('src', CC.img);
                        $info_item_img.find('img').attr('alt', CC.img);
                        $info_item_img.show();
                    }

                    if (typeof CC.url !== 'undefined' && CC.url != '') {
                        $("#item_title_p").find('a').attr('href', CC.url);
                    }
                })();

                if (CC.OrderID !== '') {}

                (function() {
                    var msgRemark = '<div class="remark">' + '<textarea type="text" id="note-text"></textarea>' +
                        '<p><button class="subBtn" id="sub-note">' + lang.msgdetail_biz.save_btn + '</button></p>' + '<ul id="note"></ul>' +
                        '</div>';
                    $('#remarkS').html(msgRemark);
                })();

                (function() {
                    var star = +CC.is_star ? 'icon-star iconBtnS star' : 'icon-star-empty iconBtnS star';
                    var icon = '<i class="' + star + '" title="' + lang.msgdetail_biz.marked_star + '" id="star" data-id="' + CC.is_star +
                        '"></i>' + '<span class="noBgBtn" id="dispose" data-id="' + CC.handled + '" style="display:none">' + lang.msgdetail_biz
                        .not_action + '</span>';
                    $topRight.prepend(icon);
                    defaultHeight();
                })();

                (function() {
                    if ($_GET['class'] == 'sent') {
                        $(".EmailDetails").hide();
                    };
                    $(".ReferenceId").hide();
                    $(".Title").hide();
                    // $(".EmailDetails").appendTo($(".right>.rightBox").eq(0));
                })();

                (function() {
                    if (CC.Sender !== 'eBay') {
                        // 查询备注
                        getItemNoteList();
                    }
                })();

                (function() {
                    if (+CC.handled && msgType === 'pending') {
                        $('#dispose').show();
                    }
                })();

                (function() {
                    // 过时
                    if (CC.preId == undefined || CC.preId == '') {
                        $('#pre').attr('title', '没有上一封');
                        $('#pre').addClass('notOpBtn');
                        $('#pre').removeAttr('id');
                    }
                    if (CC.nexId == undefined || CC.nexId == '') {
                        $('#next').attr('title', '没有下一封');
                        $('#next').addClass('notOpBtn');
                        $('#next').removeAttr('id');
                    }
                })();

                (function() {
                    // 过时
                    // 上一封邮件
                    $('#pre').on('click', function() {
                        var mid = $(this).attr('data-id');
                        var msgType = $(this).attr('msg-type');
                        location.href = "?r=home/MsgDetail&mids=" + mid + "&class=" + msgType;
                    });
                    // 下一封邮件
                    $('#next').on('click', function() {
                        var mid = $(this).attr('data-id');
                        var msgType = $(this).attr('msg-type');
                        location.href = "?r=home/MsgDetail&mids=" + mid + "&class=" + msgType;
                    });
                })();

                //标记为星
                $('#star').on('click', function() {
                    var value = $(this).attr('data-id');
                    var getId = $('#emailDetail').attr('mid');
                    var urldata = {
                        'mids': getId,
                        '_rnd': loading()
                    };
                    if (value == '0') {
                        $.get('?r=api/SetMsgStar', urldata, function(data, status) {
                            removeloading();
                            if (data.Error == 'User authentication fails') {
                                hintShow('hint_w', lang.ajaxinfo.permission_denied);
                                return;
                            }
                            if (status === "success" && data.Ack === "Success") {
                                $('#star').attr("class", "icon-star iconBtnS star");
                                $('#star').attr("data-id", 1);
                                setCookie('star', 1, 1);
                                setCookie('StarMsgId', getId, 1);
                            }
                        });
                    } else if (value == '1') {
                        $.get("?r=api/CancelMegStar", urldata, function(data, status) {
                            removeloading();
                            if (data.Error == 'User authentication fails') {
                                hintShow('hint_w', lang.ajaxinfo.permission_denied);
                                return;
                            }
                            if (status === 'success' && data.Ack === 'Success') {
                                $('#star').attr("class", "icon-star-empty iconBtnS star");
                                $('#star').attr("data-id", 0);
                                setCookie('star', 2, 1);
                                setCookie('StarMsgId', getId, 1);
                            }
                        });
                    }
                })

                // 绑定标记处理事件
                $('#dispose').on('click', function() {
                    var getId = $('#emailDetail').attr('mid');
                    var getdata = {
                        'mids': getId,
                        '_rnd': loading(),
                        action: 'handle_yes'
                    };
                    $.get('?r=api/SetMsgHandleStatus', getdata, function(data, status) {
                        removeloading();
                        if (data.Error == 'User authentication fails') {
                            hintShow('hint_w', lang.ajaxinfo.permission_denied);
                            return;
                        }
                        if (status === 'success' && data.Ack === 'Success') {
                            try {
                                opener.window.refreshMsgList();
                            } catch (e) {}
                            $('#dispose').attr("data-id", 0);
                            $('#dispose').hide();
                            setCookie('dispose', 0, 30000);
                            setCookie('DisposeMsgId', getId, 1);
                            setInterval(function() {
                                window.close();
                            }, 200);
                        }
                    });
                });

                (function() {
                    // 发送失败时内容找回
                    if (+CC.send_status) {
                        hintShow('hint_w', lang.msgdetail_biz.last_msg_send_fail);
                        $.get('?r=api/getLastSendMsg&msgid=' + CC.msg_id, function(data, status) {
                            removeloading();
                            if (status === 'success' && data.Ack === 'Success') {
                                $("#sendContent").val(data.Body.msg_content);
                                if (typeof(data.Body.image_urls) !== 'undefined') {
                                    for (var x in data.Body.image_urls) {
                                        img.push(data.Body.image_urls[x]);
                                        $('<li><i class="icon-close iconBtnS" title="' + lang.msgdetail_biz.click_delete +
                                            '"></i><img src="' + data.Body.image_urls[x] + '" width="70px" height="60px"></li>').appendTo(
                                            '.imglist_addwindow');
                                    }
                                    if (img.length > 0) {
                                        global.imgurl = img;
                                        $('.imgShow').show();
                                        imgShowAn(-1, 'true', lang.msgdetail_biz.click_hide);
                                    }
                                }
                            }
                        });
                    }
                })();

                (function() {
                    $("#more_btn").on('click', function() {
                        $("#more_div").show();
                    });
                    $("#CloseMoreWindow").on('click', function() {
                        $("#more_div").hide();
                    });
                })();

                (function() {
                    if (CC.ItemTitle != '') {
                        $("#item_title_p").find('a').html(CC.ItemTitle);
                    }
                })();

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
                                                '">' + '<span>' + intToLocalDate(data.Body[x].ReceiveDate, 9) + '</span><p>' + data
                                                .Body[x].nick_name + ' | ' + ((data.Body[x].FolderID == 1) ? lang.msgdetail_biz.msg_his
                                                    .send : lang.msgdetail_biz.msg_his.received) + data.Body[x].Subject +
                                                '</p></li>');
                                        }
                                        // noloading = true;
                                        $his_msgs_ul.find('li').eq(0).click();
                                    } else {
                                        $his_msgs_ul.html('<li>没有？这个不可能！请联系管理员</li>');
                                        if (page > 1) {
                                            hintShow('hint_w', lang.msgdetail_biz.msg_his.last_page_tip);
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
                            hintShow('hint_w', lang.msgdetail_biz.msg_his.first_page_tip);
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

                                        $msgtext.find('#Header').each(function(index, element) {
                                            $(element).hide();
                                        });

                                        $msgtext.find('#Title').each(function(index, element) {
                                            $(element).hide();
                                        });

                                        $msgtext.find('>#ReferenceId').each(function(index, element) {
                                            $(element).hide();
                                        });

                                        $msgtext.find('>#Footer').each(function(index, element) {
                                            $(element).hide();
                                        });

                                        $msgtext.find('td').each(function(index, element) {
                                            $(element).removeAttr('width');
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
                            /*
                            $.get('?r=api/GetMsgTexts',{msgid:$(this).data('msgid')},function(data,textStatus){
                                // 历史消息内容
                                removeloading();
                                if(textStatus=='success'){
                                    if(data.Ack ==='Success'){
                                        $("#his_msg_box2").hide();
                                        $("#his_msg_box_ul").empty();
                                        
                                        console.log(data);
                                        
                                        var _html='';
                                        if(data.Body.Contents){
                                            for(var x in data.Body.Contents){
                                                if(data.Body.Contents[x].FolderID==1){
                                                    _html+='<li class="sellerMSG"><div class="MSGC">';
                                                    _html+='<p><b>linpeiyan</b>&nbsp;&nbsp;<span>2015/5/5 05:05</span><small>【德国时间 ：2015/5/5 05:05】</small></p>';
                                                    _html+='<p>Fuck! Fuck! Fuck!</p>';
                                                    _html+='</div></li>';
                                                }else{
                                                    _html+='<li class="buyerMSG"><div class="MSGC">';
                                                    _html+='<p><b>linpeiyan</b>&nbsp;&nbsp;<span>2015/5/5 05:05</span><small>【德国时间 ：2015/5/5 05:05】</small></p>';
                                                    _html+='<p>Fuck! Fuck! Fuck!</p>';
                                                    _html+='</div></li>';
                                                }
                                            }
                                        }else if(0){
                                            
                                        }else{
                                            hintShow('hint_f','数据异常!');
                                        }
                                        
                                        $("#his_msg_box_ul").append(_html);
                                    }else{
                                        hintShow('hint_f','服务器错误!');
                                    }
                                }else{
                                    hintShow('hint_f','网络错误!');
                                }
                            })
                            */
                    });

                })();

            } else {
                // xxx
                hintShow('hint_f', lang.ajaxinfo.internal_error_s);
            }
            //@todo 代码移到列表页处理
            //            //修改信息读取状态,后台处理，操作状态不作处理
            //            if (!+is_read){ 
            //                  setTimeout(function(){
            //                      var msgId = $('#emailDetail').attr('mid');
            //                      $.get('?r=api/SetMsgRead',{'mids':msgId});
            //                  },1500);
            //            }
        });
    })()


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
     * @author heguangquan
     * @date 2015-03-05
     */
    function getSubClassHtml(pid, data) {
        var obj = getSubClass(pid, data);
        if (obj.length == 0) {
            $.get('?r=api/GetTpList', {
                'pid': pid,
                '_rnd': loading()
            }, function(listdata, state) {
                removeloading();
                if (state === 'success') {
                    if (listdata.Body.list !== '') {
                        $.each(listdata.Body.list, function(index, item) {
                            $('#class_box select[pid=' + pid + ']').append('<option value="' + item.tp_list_id + '">' + item.title +
                                '</option>');
                        });
                        $('#class_box select[pid=' + pid + ']').change(function(e) {
                            var fid = $(this).find('option:selected').val();
                            $(this).nextAll().remove();
                            $.each(listdata.Body.list, function(index, item) {
                                if (fid === item.tp_list_id) {
                                    $oSendContent.val(item.content).change();
                                }
                            });
                        });
                    }
                }
            });
        }
        $('#class_box').append('<select pid="' + pid + '"><option value="">' + lang.msgdetail_biz.please_select + '</option></select>');
        for (var i in obj) {
            $('#class_box select[pid=' + pid + ']').append('<option value="' + obj[i].tp_class_id + '">' + obj[i].classname + '</option>');
        }
        $('#class_box select[pid=' + pid + ']').change(function(e) {
            var npid = $(this).find('option:selected').val();
            $(this).nextAll().remove();
            if (npid > 0) {
                getSubClassHtml(npid, data);
            }
        });
    }
    $('#class_box').empty();
    $.get('?r=api/GetTpClassList', function(data, state) {
        if (state == 'success') {

            getSubClassHtml(0, data);
        } else {
            hintShow('hint_f', lang.ajaxinfo.network_error_s);
        }
    });


    //判断msg回复字符串是否超过上限
    (function() {
        //敲键盘时候触发计算字数

        $oSendContent.on('input change', function() {
            var textlength = $oSendContent.val().replace(/[^\u0000-\u00ff]/g, "aaa").length;
            var text = $oSendContent.val();
            var length = (2000 - textlength);

            if (length < 0) {
                $("#sendMsg").attr("disabled", true);
                hintShow('hint_w', lang.msgdetail_biz.over_max_chars_length);
                $('#StrLength').find('span').html(lang.msgdetail_biz.input_max_tip_l + '<b style="color: #f00;">' + length + '</b>' + lang.msgdetail_biz
                    .input_max_tip_r);
            } else {
                $("#sendMsg").attr("disabled", false);
                $('#StrLength').find('span').html(lang.msgdetail_biz.input_max_tip_l + '<b>' + length + '</b>' + lang.msgdetail_biz.input_max_tip_r);
            }
        });
    })();


    //绑定回复信息事件
    $('#sendMsg').on('click', function() {

        if ($_GET['class'] == 'sys') {
            // hintShow('hint_w','系统消息不能回复!');
            // return;
        }

        if (ResponseEnabled !== '1') {
            hintShow('hint_w', lang.msgdetail_biz.the_msg_cannot_reply);
            return;
        }

        var msgBody = $oSendContent.val();
        if (msgBody === '' || msgBody == undefined) {
            hintShow('hint_w', lang.msgdetail_biz.msg_content_cannot_empty);
            return;
        }
        if ($("#MyEmail").is(':checked')) {
            var copy = true;
        } else {
            var copy = false;
        }
        global.imgurl = img;
        var mid = $('#emailDetail').attr('mid');
        var thisTr = $('#dispose');
        var value = thisTr.attr('data-id');
        var getdata = {
            'mids': mid,
            action: 'handle_yes'
        };
        loading();
        $.post('?r=Api/ReplyMsg', {
            'msgid': mid,
            'content': msgBody,
            'imgurl': global.imgurl,
            'copy': copy
        }, function(result, status) {
            // $.post('?r=Api/ReplyMsg2',{'msgid':mid,'content':msgBody,'imgurl':global.imgurl,'copy':copy},function(result,status){
            removeloading();
            if (status === 'success') {
                if (result.Error == 'User authentication fails') {
                    hintShow('hint_w', lang.ajaxinfo.permission_denied);
                    return;
                }
                if (result.Error == '这条消息不能回复') {
                    hintShow('hint_w', lang.msgdetail_biz.the_msg_cannot_reply);
                    return;
                }
                if (result.Ack === 'Success') {
                    $oSendContent.val('').change();
                    $("#sendContent").addClass('sentIcon');
                    setCookie('replied', 1, 1);
                    setCookie('RepliyMigId', mid, 1);
                    // 回复完将待处理标志去掉
                    if (value == '1') {
                        loading();
                        $.get('?r=api/SetMsgHandleStatus', getdata, function(data, status) {
                            removeloading();
                            if (data.Error == 'User authentication fails') {
                                hintShow('hint_w', lang.ajaxinfo.permission_denied);
                                return;
                            }
                            if (status === 'success' && data.Ack === 'Success') {
                                thisTr.attr("data-id", 0);
                                thisTr.hide();
                                setCookie('dispose', 0, 1);
                                setCookie('DisposeMsgId', mid, 1);
                            }
                        });
                    }
                    var s = 3;
                    global.imgurl = undefined;
                    img = [];
                    $('.imgShow').hide(200);
                    $('.imglist_addwindow').empty();
                    //3秒后隐藏发送图标
                    var stime = setInterval(function() {
                        if (s == 0) {
                            $("#sendContent").removeClass('sentIcon');
                            clearInterval(stime);
                        }
                        s--;
                    }, 1000);

                    (function() {
                        // 消息回复成功，正在同步
                        if (result.Body.Pk > 0) {
                            stateBoxFun('show', lang.msgdetail_biz.msg_syncing);
                            $.get('?r=api/GetMsgReplyStatus', {
                                qpk: result.Body.Pk
                            }, function(data, status) {
                                removeloading();
                                if (status === 'success' && data.Ack === 'Success') {
                                    //  hintShow('hint_s','消息回复成功！');
                                    stateBoxFun('show', lang.msgdetail_biz.msg_send_sync_success);
                                    var timeSec = 3;
                                    setInterval(function() {
                                        $('body .stateGB .stateBox').html(lang.msgdetail_biz.msg_send_sync_success + ',' +
                                            timeSec + lang.msgdetail_biz.close_window_tip2);
                                        timeSec--;
                                        if (timeSec <= 0) {
                                            window.close();
                                        }
                                    }, 1000);
                                } else if (data.Error.body) {
                                    hintShow('hint_f', lang.msgdetail_biz.msg_reply_fail + data.Error.body);
                                    stateBoxFun('hide', lang.msgdetail_biz.msg_reply_fail + data.Error.body);
                                    localStorage.reply_failure_id = mid;
                                } else {
                                    hintShow('hint_f', lang.msgdetail_biz.msg_reply_fail_unknown);
                                    stateBoxFun('hide', lang.msgdetail_biz.msg_reply_fail_unknown);
                                }
                            });
                        } else {
                            hintShow('hint_f', lang.msgdetail_biz.msg_reply_fail_unknown);
                        }
                    })();

                } else {
                    hintShow('hint_f', lang.msgdetail_biz.msg_reply_fail_500);
                }
            } else {
                hintShow('hint_f', lang.msgdetail_biz.msg_reply_fail_network_error);
            }
        });
    });

    // 返回列表页
    $('#leftTopLeft').on('click', function() {
        if (typeof window.parent.back_url === 'undefined') {
            window.parent.$("iframe[name='main']").attr("src", "?r=Home/MsgList&class=inbox");
        } else {
            window.parent.$("iframe[name='main']").attr("src", window.parent.back_url);
        }
    });

    /**
     * @desc 加载msg备注
     * @author liaojianwen
     * @date 2015-04-21
     */
    function getItemNoteList() {
        var itemId = global_itemID;
        var msgId = $('#emailDetail').attr('mid');
        $.ajax({
            url: '?r=api/GetItemNoteList',
            data: {
                'itemId': itemId,
                'type': 'msg',
                'msgId': msgId
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
        });
    }

    /**
     * @desc 添加msg备注事件
     * @author liaojianwen
     * @date 2015-04-21
     */
    $('#remarkS').on('click', '#sub-note', function() {
        var msgId = $("#emailDetail").attr('mid');
        var text = $("#note-text").val();
        if (text == '') {
            hintShow('hint_w', lang.msgdetail_biz.cannot_empty);
            return;
        }
        var item_id = global_itemID;
        $.post('?r=api/AddItemNote', {
            text: text,
            msgid: msgId
        }, function(data, status) {
            if (data.Error == 'User authentication fails') {
                hintShow('hint_w', lang.ajaxinfo.permission_denied);
                return;
            }
            if (status == 'success') {
                if (data.Ack == 'Success') {
                    $('#note-text').val('');
                    getItemNoteList();
                }
            } else {
                hintShow('hint_f', lang.ajaxinfo.network_error_s);
            }
        });
    })

    $('#determine').on('click', function() {
        if ($('#imglistli').find('li').length == 0) {
            hintShow('hint_w', lang.msgdetail_biz.please_select_picture);
            return;
        }

        $('.addImgTC').hide();

        $('.imglist_addwindow').empty();
        for (var i in img) {
            $('<li><i class="icon-close iconBtnS" title="' + lang.msgdetail_biz.click_delete + '"></i><img src="' + img[i] +
                '" width="70px" height="60px"></li>').appendTo('.imglist_addwindow');
        }
        if (i >= 0 && img.length > 0) { //如果上传了图片以及图片数组长度大于0，则展示图片
            $('.imgShow').show();
            imgShowAn(-1, 'true', lang.msgdetail_biz.click_hide);
        }
    });

    (function() {
        // YangLong
        $("#drop-area-div").dmUploader({
            url: '?r=api/Upload',
            allowedTypes: 'image/*',
            extFilter: 'jpg;png;gif;bmp;tif',
            extraData: {
                refid: $("#emailDetail").attr('mid'),
                src: 'msgid'
            },
            // maxFiles:5,
            maxFileSize: 1024 * 1024,
            dataType: 'json',
            fileName: 'Filedata',
            onInit: function() {},
            onFallbackMode: function(message) {},
            onNewFile: function(id, file) {},
            onBeforeUpload: function(id) {
                if (img.length >= 5) {
                    hintShow('hint_w', lang.msgdetail_biz.max_picture_tip);
                    $("#drop-area-div").find('input').attr('disabled', 'disabled');
                    return false;
                }
            },
            onComplete: function() {},
            onUploadProgress: function(id, percent) {},
            onUploadSuccess: function(id, data) {
                if (data.Ack == 'Success') {
                    img.push(data.Body.filepath);
                    $('<li><i class="icon-close iconBtnS" title="' + lang.msgdetail_biz.click_delete + '" data-id="' + id + '"></i><img src="' +
                        data.Body.filepath + '" width="70px" height="60px"></li>').appendTo('.imglist_addwindow');
                } else {
                    hintShow('hint_f', lang.msgdetail_biz.upload_error);
                    return false;
                }
            },
            onUploadError: function(id, message) {
                hintShow('hint_w', lang.msgdetail_biz.upload_error_network + message);
            },
            onFileTypeError: function(file) {
                hintShow('hint_w', lang.msgdetail_biz.picture_type_error);
            },
            onFileSizeError: function(file) {
                hintShow('hint_w', lang.msgdetail_biz.picture_size_error);
            },
            onFileExtError: function(file) {
                hintShow('hint_w', lang.msgdetail_biz.picture_ext_error);
            },
            onFilesMaxError: function(file) {
                hintShow('hint_w', lang.msgdetail_biz.picture_num_error);
            }
        });
    })();

    // 点击添加附件事件
    $('#addFile').on('click', function() {
        $('.addImgTC').show();
        $('.imglist_addwindow').empty();
        for (var i in img) {
            $('<li><i class="icon-close iconBtnS" title="' + lang.msgdetail_biz.click_delete + '"></i><img src="' + img[i] +
                    '" width="70px" height="60px"></li>')
                .appendTo('.imglist_addwindow');
        }
    });

    // 点击关闭添加文件窗口
    $('#cancelUser').on('click', function() {
        $('.addImgTC').hide();
        $('.imglist_addwindow').empty();
        for (var i in img) {
            $('<li><i class="icon-close iconBtnS" title="' + lang.msgdetail_biz.click_delete + '"></i><img src="' + img[i] +
                    '" width="70px" height="60px"></li>')
                .appendTo('.imglist_addwindow');
        }
    });

    // 删除添加窗口的图片列表
    $('.imglist_addwindow').on('click', '.icon-close', function() {
        var picurl = $(this).parents('li').find('img').attr("src");
        img.splice($.inArray(picurl, img), 1);
        $(this).parents('li').remove();
        if (!img.length) {
            $('.imgShow').hide();
        }
        // 启用控件
        $("#drop-area-div").find('input').removeAttr('disabled');
    });

    // 循环执行，每隔3秒钟执行一次 
    window.setInterval(function() {
        var stared = +getCookie('star');
        var msg_id = getCookie('StarMsgId');
        var msgId = $("#emailDetail").attr('mid');
        //标记标星
        if (msg_id == msgId) {
            if (stared === 1) {
                $("#star").removeClass('icon-star-empty').addClass('icon-star');
            } else if (stared === 2) {
                $("#star").removeClass('icon-star').addClass('icon-star-empty');
            }
        }
    }, 3000);

    $(window).resize(function(e) {
        defaultHeight();
    });

    $("#sendContent").on("focus", function() {
        defaultHeight();
    });

    setInterval(function() {
        defaultHeight();
    }, 1000);

    $(document).on('click', '#open_tracking_details_btn', function(e) {
        $('#logistics_window').show();
        // loading();
        // http://192.168.188.128/ims/?r=api/GetTrackingInfo&trackno=ETX150917A1M000247
        var trackno = $(this).data('trackno');
        var carrier = $(this).data('carrier');
        var _trackingurl;

        if (carrier == 'PostNL') {
            _trackingurl = 'http://www.postnl.post/details/?barcodes=' + trackno + '';
        } else if (carrier == 'USPS') {
            _trackingurl = 'https://tools.usps.com/go/TrackConfirmAction?qtc_tLabels1=' + trackno + '';
        } else if (carrier == 'Royal Mail') {
            _trackingurl = 'http://www.royalmail.com/trackdetails?trackNumber=' + trackno + '';
        } else if (carrier == 'China Post') {
            _trackingurl = 'http://track-chinapost.com/startairmail.php?code=' + trackno + '';
        } else if (carrier == 'UPS') {
            _trackingurl = 'https://wwwapps.ups.com/WebTracking/track?loc=en_US&HTMLVersion=5.0&USER_HISTORY_LIST=&trackNums=' + trackno +
                '&track.x=Track';
        } else if (carrier == 'YANWEN') {
            _trackingurl = 'http://track.yw56.com.cn/en-US?InputTrackNumbers=' + trackno + '';
        } else {
            _trackingurl = 'http://www.ec-firstclass.org/?track_number=' + trackno + '';
        }

        $('#logistics_detail_h3').html('<a href="' + _trackingurl + '" target="_blank">' + trackno + '</a>');
        $.get('?r=api/GetTrackingInfo', {
            'trackno': trackno
        }, function(data, status) {
            $('#logistics_detail_tr').empty();
            $('#logistics_package_detail').empty();
            if (data.Ack == 'Success') {
                for (var x in data.Body.trackingDetails) {
                    var _html = '';
                    var _detail = data.Body.trackingDetails[x];
                    var _detailTime = _detail.LocalTime.split(' ');
                    // fill zero
                    _detailTime[0] = _detailTime[0].split('/');
                    for (var y in _detailTime[0]) {
                        _detailTime[0][y].length == 1 ? _detailTime[0][y] = '0' + _detailTime[0][y] : null;
                    }
                    _detailTime[0] = _detailTime[0].join(':');
                    // fill zero
                    _detailTime[1] = _detailTime[1].split(':');
                    for (var y in _detailTime[1]) {
                        _detailTime[1][y].length == 1 ? _detailTime[1][y] = '0' + _detailTime[1][y] : null;
                    }
                    _detailTime[1] = _detailTime[1].join(':');
                    _html += '<tr><td>' + _detailTime[0] + '</td>';
                    _html += '<td>' + _detailTime[1] + '</td>';
                    _html += '<td>' + _detail.Location + '</td>';
                    _html += '<td>' + _detail.Description + '</td></tr>';
                    $('#logistics_detail_tr').append(_html);
                    if (x == 0) {
                        $('#logistics_detail_h3').append('&nbsp;&nbsp;&nbsp;<span class="fontBtn">' + _detail.Status + '</span>');
                        var index = 0;
                        if (_detail.Status == 'Delivered') {
                            index = 3;
                        } else if (_detail.Status == 'InDelivery') {
                            index = 2;
                        } else if (_detail.Status == 'Processing') {
                            index = 1;
                        } else {
                            index = 0;
                        }
                        $("#tracking_status").show().find('li').eq(index).addClass('active');
                    }
                }
                if (data.Body.packageDetails.ServiceCode) {
                    var _dt = data.Body.packageDetails;
                    var _html = '';
                    var _str = [];
                    _str.push(_dt.City);
                    _str.push(_dt.Province);
                    _str.push(_dt.Country);
                    _str = _str.join(', ');
                    _html += '<li><span>【' + lang.msgdetail_biz.logistics.shipped_to + '】：</span>' + _str + '</li>';
                    _html += '<li><span>【' + lang.msgdetail_biz.logistics.service + '】：</span>' + _dt.ServiceName + ' ' + _dt.ServiceCode +
                        '</li>';
                    _html += '<li><span>【CK1 ' + lang.msgdetail_biz.logistics.tracking_number + '】：</span>' + _dt.TrackingNumber + '</li>';
                    _html += '<li><span>【' + lang.msgdetail_biz.logistics.zip_code + '】：</span>' + _dt.PostCode + '</li>';
                    _html += '<li><span>【' + lang.msgdetail_biz.logistics.total_weight + '(kg)】：</span>' + (_dt.WeightForCharge !== null ? _dt.WeightForCharge /
                        1000 : 'unknown') + '</li>';
                    // _html += '<li><span>【View the proof】：</span>Deldkdkdf</li>';
                    $('#logistics_package_detail').html(_html);
                } else {
                    var _html = '<li>' + lang.msgdetail_biz.logistics.not_data + '<a href="' + _trackingurl + '" target="_blank">' + lang.msgdetail_biz
                        .logistics.click_view + '</a></li>';
                    $('#logistics_package_detail').html(_html);
                }
            } else {
                $('#logistics_detail_tr').append('<tr><td colspan="4">' + lang.msgdetail_biz.logistics.not_data + '<a href="' + _trackingurl +
                    '" target="_blank">' + lang.msgdetail_biz.logistics.click_view + '</a></td></tr>');
                $('#logistics_package_detail').append('<tr><td colspan="4">' + lang.msgdetail_biz.logistics.not_data + '<a href="' +
                    _trackingurl + '" target="_blank">' + lang.msgdetail_biz.logistics.click_view + '</a></td></tr>');
            }
        });
    });

    $("#logistics_window_close").on('click', '', function() {
        $('#logistics_window').hide();
    });
})