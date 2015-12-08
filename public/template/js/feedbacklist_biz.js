"use strict";
/**
 * @desc 加载feedback 列表
 * @author liaojianwen
 * @date 2015-08-25
 */
$(document).ready(function(e){
    var global_info = {
        'page' : $_GET['page'] ? $_GET['page'] : undefined, // 页码
        'pageSize' : $_GET['pageSize'] ? $_GET['pageSize'] : undefined, // 分页大小
        'type' : $_GET['type'],// 类型
        'cust' : undefined,//客户
        'status' : undefined, // 状态
    };
    var global_item;
    var global_client;
    //列表
    feedbackList();
    var $window_client_info=$("#window_client_info");
    var img=[];
    
    function feedbackList(pageInfo) {
        if (pageInfo !== undefined) {
            global_info.page = pageInfo.page;
            global_info.pageSize = pageInfo.pageSize;
        }
        
        $.get('?r=api/GetFeedbackList',global_info,function(data,status){
            $('#feedbackList').find('tr').remove();
            if(status =='success' && data.Ack =='Success'){
                var M = data.Body.list;
                var lastSendTime;
                if(M && M.length > 0){
                    for(var i in M){
                        var type = '';
                        if(global_info.type === 'comprehensive'){
                            switch (M[i].CommentType) {
                                case 'Positive':
                                    type = 'iconPos';
                                    break;
                                case 'Negative':
                                    type = 'iconNeg';
                                    break;
                                case 'Neutral':
                                    type = 'iconNeu';
                                    break;
                                default :
                                    type = 'iconNone';
                            }
                        }else {
                            type = 'iconNone';
                        }
                        if(M[i].lastSendTime.length > 1){
                        	lastSendTime = intToLocalDate(M[i].lastSendTime,3);
                        } else {
                        	lastSendTime = lang.feedbacklist_biz.none;
                        }
                        $('<tr><td><input type="checkbox" class="ids" data-id="'+M[i].ebay_feedback_transaction_id+'" data-order-date="'+M[i].CreatedDate+'"/></td><td class="shop"><span>'+M[i].nick_name+'</span></td><td class="Client"><span>'+M[i].CommentingUser+
                                '</span></td><td class="itemInfo" data-itemid="'+M[i].ItemID+'"><span>'+M[i].ItemID+'</span></td><td class="evaluate" data-type="'+M[i].CommentType+'"><i class="'+type+'"></i><span>'+M[i].CommentText+
                                '</span></td><td class="CommentTime"><span>'+intToLocalDate(M[i].CommentTime,3)+'</span></td><td><span>'+lastSendTime+'</span></td><td title="'+lang.feedbacklist_biz.click_view_details+'"  class="feedbackDet" data-orderLineItemID="'+M[i].OrderLineItemID+'" data-isSendMsg="'+M[i].isSendMsg+
                                '"data-isResponse="'+M[i].isResponse+'" data-text="'+M[i].ResponseText+'" data-isChange="'+M[i].isChange+
                                '" data-FeedbackResponse="'+M[i].FeedbackResponse+'" data-requestOutDate ="'+M[i].isRequestOutDate+
                                '" data-feedbackOutDate="'+M[i].isFeedbackOuteDate+'" data-declineChange="'+M[i].isDeclineChange+'"><span><a class="fontBtn">'+lang.feedbacklist_biz.details+'</a></span></td></tr>').appendTo('#feedbackList');
                    }
                }
                
                var pageInfo = data.Body.page;
                if (typeof pageInfo !== 'undefined'){
                    refreshPaginationNavBar(pageInfo.page,pageInfo.pageSize,data.Body.count,feedbackList);//分页
                }
                
            }else{
                $('<tr><td colspan="7" align="center">'+lang.feedbacklist_biz.not_data+'</td></tr>').appendTo("#feedbackList");
                refreshPaginationNavBar(1,1,1,feedbackList);//分页
            }
        });
    }
    //全选
    allCheckBox($('#checkall'),$('.ids'));
    
    /**
     * @desc 搜索
     * @author liaojianwen
     * @date 2015-08-25
     */
    $('#searchList').on('click',function(){
        var cust = $('#cust').val();
        var status = $('#status').val();
        global_info.cust = cust;
        global_info.status = status;
        global_info.page = undefined;
        global_info.pageSize = undefined;
        feedbackList();
    });
    
    //查询绑定enter键
    document.onkeydown = function(event){
        if((event.keyCode || event.which) == 13){
            global_info.cust = $('#cust').val();
            global_info.status = $('#status').val();
            global_info.page = undefined;
            global_info.pageSize = undefined;
            feedbackList();
       }
    };
    //批量回复评价
    (function(){
        $('#batchDeal').on('click',function(){
            var allCheckBox = $('#feedbackList input[type=checkbox]:checked');
            var nStr_allId;
            var str_allId ='';
            var isresponse;
            var buyer;
            for (var i = 0; i < allCheckBox.length; i++) {
                str_allId += allCheckBox.eq(i).parents('tr').find('td[class="Client"] span').html() + ','
                isresponse = allCheckBox.eq(i).parents('tr').find('td[class="feedbackDet"]').attr('data-isResponse');
                buyer = allCheckBox.eq(i).parents('tr').find('td[class="Client"] span').html();
                if(isresponse == 1){
                     hintShow('hint_w',buyer+lang.feedbacklist_biz.reply_only_once);
                     return;
                }
            }
            var replyClient = str_allId.substr(0, str_allId.length - 1);
            if (replyClient.length == 0) {
                hintShow('hint_w',lang.feedbacklist_biz.check_box);
                return;
            } else {
                 $('#ReplyClient').html(replyClient);
                 $("#batchReply").show();
            }
        })
        $('#close_btn').on('click',function(){
            $('#ReplyClient').html('');
            $('#batchText').val('');
            $('#batchReply').hide();
        })
    })();
    
    
    if(global_info.type ==='negative' || global_info.type ==='neutral'){
    	$('#batchSend').show();
    } else {
    	$('#batchSend').hide();
    }
    //批量给客户发消息
    (function(){
        $('#batchSend').on('click',function(){
            var allCheckBox = $('#feedbackList input[type=checkbox]:checked');
            var nStr_allId;
            var str_allId ='';
            var isresponse;
            var buyer;
            for (var i = 0; i < allCheckBox.length; i++) {
                str_allId += allCheckBox.eq(i).parents('tr').find('td[class="Client"] span').html() + ','
                buyer = allCheckBox.eq(i).parents('tr').find('td[class="Client"] span').html();
                var createTime = allCheckBox.eq(i).data('order-date');
                var currentTime = Date.parse(new Date())/1000;
                var endTime = currentTime - 90*24*60*60;
                if(!!createTime && (createTime < endTime)){
                    hintShow('hint_w',buyer+lang.feedbacklist_biz.cannot_contact_the_buyer);
                    return;
                }
            }
            var replyClient = str_allId.substr(0, str_allId.length - 1);
            if (replyClient.length == 0) {
                hintShow('hint_w',lang.feedbacklist_biz.check_box);
                return;
            } else {
                 $('#MsgClient').html(replyClient);
                 $("#batchSendMsg").show();
            }
        })
        $('#close_batch').on('click',function(){
            $('#MsgClient').html('');
            $('#MsgText').val('');
            $('.imglist_addwindow').empty();
            img =[];
            $('.imgShow').hide();
            $('#batchSendMsg').hide();
            feedbackList();
        })
    })();
    /**
     * @desc 弹窗信息
     * @author liaojianwn
     * @date 2015-08-26
     */
    (function(){
        $('#feedbackList').on('click','.feedbackDet',function(){
            $(parent.document).find('.reFull').click(); //收缩侧边栏
            var $his_msgs_ul=$("#his_msgs_ul");
            var _feedback = $(this).parents('tr');
            var clientId = _feedback.find('td[class="Client"] span').html();
            $('#note').find('li').empty();//清空备注
            $('#temp_msg').find('select[pid="0"]').nextAll().remove();//清除模版
            $('#temp_feedback').find('select[pid="0"]').nextAll().remove();
            $('#temp_change').find('select[pid="0"]').nextAll().remove();
            $('#more_div').show();
            var orderId = _feedback.find('td[class="feedbackDet"]').attr('data-orderLineItemID');
            var shopId = _feedback.find('td[class="shop"]').text();
            var itemInfo = _feedback.find('td[class="itemInfo"] span').html();
            var commentText = _feedback.find('td[class="evaluate"]').text();
            var commentType = _feedback.find('td[class="evaluate"]').attr('data-type');
            var commentTime = _feedback.find('td[class="CommentTime"]').text();
            var itemid = _feedback.find('td[class="itemInfo"]').attr('data-itemid');
            var feedback_transaction_id = _feedback.find('input[class="ids"]').attr('data-id');
            var isSendMsg = _feedback.find('td[class="feedbackDet"]').attr('data-isSendMsg');
            var isResponse = _feedback.find('td[class="feedbackDet"]').attr('data-isResponse');
            var isChange = _feedback.find('td[class="feedbackDet"]').attr('data-isChange');
            var responseText = _feedback.find('td[class="feedbackDet"]').attr('data-text');
            var FeedbackResponse = _feedback.find('td[class="feedbackDet"]').attr('data-FeedbackResponse');
            var isDeclineChange = _feedback.find('td[class="feedbackDet"]').attr('data-declineChange');
            var isRequestOutDate = _feedback.find('td[class="feedbackDet"]').attr('data-requestOutDate');
            var isFeedbackOutDate = _feedback.find('td[class="feedbackDet"]').attr('data-feedbackOutDate');
            if(isSendMsg == 1){
                $('#msgStatus').attr('checked','checked');
                $('#msgStatus').attr('disabled',true);
            }
            if(isChange == 1){
                $('#requestStatus').attr('checked','checked');
                $('#requestStatus').attr('disabled',true);
            }
            if(isDeclineChange ==1){
                $('#declineChange').attr('checked','checked');
                $('#declineChange').attr('disabled',true);
            }
            if(isRequestOutDate == 1){
                $('#requestOutTime').attr('checked','checked');
                $('#requestOutTime').attr('disabled',true);
            }
            if(isFeedbackOutDate == 1){
                $('#feedbackOutTime').attr('checked','checked');
                $('#feedbackOutTime').attr('disabled',true);
            }
            if((isSendMsg == 1) && (isChange == 1) && (isDeclineChange ==1) && (isRequestOutDate == 1) && (isFeedbackOutDate == 1)){
                $('#saveStatus').attr('disabled',true);
                $('#saveStatus').addClass('btnGray');
            }
            $('#feedback_temp').show();
            $('#text_feedback').parents('p').show();
            $('#feedback_length').show();
            if(!!responseText || isResponse == 1){
                if(FeedbackResponse){
                    responseText = FeedbackResponse;
                }
                $('#feedback_temp').hide();
                $('#text_feedback').parents('p').hide();
                $('#feedback_length').hide();
                $('#feedback_length').after('<small>'+lang.feedbacklist_biz.cannot_reply+'</small><p>'+lang.feedbacklist_biz.reply_content+'<span>'+responseText+'</span></p>');
            }
            var type;
            global_item = itemid;
            global_client = clientId;
            getFeedbackNote(itemid,clientId);
            switch (commentType) {
                case 'Positive':
                    type = 'iconPos';
                    break;
                case 'Negative':
                    type = 'iconNeg';
                    break;
                case 'Neutral':
                    type = 'iconNeu';
                    break;
                default :
                    type = 'iconNone';
            }
            (function(){
                $('#orderList').find('tr').empty();
                $('#userId a').text(clientId);
                $('#userId a').attr('href','http://www.ebay.com/usr/'+clientId);
                $('<tr><td>'+shopId+'</td><td class="picture"></td><td id="itemVarition"></td><td id="itemQuan"></td><td id="orderCreateTime" data-order-time=""></td><td id="itemPrice"></td></tr>').appendTo('#orderList');
                $('.reviewsText').html('<i class="'+type+'"></i><span>'+commentText+' </span><small> '+commentTime+'</small>');
                $('.reviewsText').data('feedbackid',feedback_transaction_id);
                $.get('?r=api/GetItemURL',{'itemid':itemid},function(data,status){
                    if(status=='success' && data.Ack=='Success'){
                        var gallery_url =  data.Body.gallery_url;
                        $('.picture').html('<a href="'+gallery_url+'" data-lightbox="example-set-12"><img alt="" src="'+gallery_url.replace(/\_1\.JPG\?/i,'_0.JPG?')+'"></img></a>');
                    }
                })
            })();
            
            //获取订单明细
            var productVarition ='';
            $.get('?r=api/GetFeedbackOrder',{'lineid':orderId},function(data,status){
                if(status ==='success' && data.Ack ==='Success'){
                    var _obj = data.Body;
                    productVarition = _obj.ProductName ? _obj.ProductName +'<br />':'';
                    var V = _obj.VariationSpecifics;
                    for(var j in V){
                        productVarition += j +' : '+V[j]+'<br />'
                    }
                    var price = _obj.TransactionPrice ? _obj.TransactionPrice +'&nbsp'+_obj.TransactionPrice_currencyID +'<br />' :'';
                    price += (_obj.ActualShippingCost || _obj.ActualShippingCost > 0)? _obj.ActualShippingCost +'&nbsp'+_obj.ActualShippingCost_currencyID +'<br />':'';
                    var quantity  = _obj.QuantityPurchased
                    var order_time = _obj.CreatedTime;
                    $('#itemPrice').html(price)
                    $('#orderCreateTime').html('<span>'+intToLocalDate(order_time,3)+'</span>');
                    $('#orderCreateTime').data('order-time',order_time);
                    $('#itemQuan').html('<span>'+quantity+'</span>');
                    $('#itemVarition').html(itemInfo+'<br />'+productVarition);
                } else {
                    $('#itemVarition').html(itemInfo);
                }
                
            });
            
            /**
             * @desc 明细页赋值
             * @author liaojianwen
             * @date 2015-08-26
             */
            (function(){
                if(orderId.length==0){
                    return;
                }
                // 如果有orderId
               
                $.get("?r=api/GetEbayTransactionInfo",{OrderLineItemID:orderId},function(data,status){
                    if(status==='success'){
                        if(data.Ack==='Success'){
                            for(var i=0;i<data.Body.length;i++){
                                if(data.Body[i].Buyer_Email!=''&&data.Body[i].Buyer_Email!='Invalid Request'){
                                    $window_client_info.find('span[t="mail"]').html(data.Body[i].Buyer_Email);
                                }else if(data.Body[i].Buyer_StaticAlias!=''){
                                    $window_client_info.find('span[t="mail"]').html(data.Body[i].Buyer_StaticAlias);
                                }
                                $window_client_info.find('span[t="name"]').html(data.Body[i].Buyer_UserFirstName+' '+data.Body[i].Buyer_UserLastName);
                            }
                        } else {
                            // hintShow('hint_f','服务器错误!');
                        }
                    } else {
                        hintShow('hint_f',lang.ajaxinfo.network_error);
                    }
                });
            })();
            
            /**
             * @desc 获取评价
             * @author liaojianwen
             * @date 2015-08-26
             */
             (function(){
                 $.get('?r=api/GetBuyerToSellerFeedbackInfo',{userId:clientId},function(data,textStatus){
                     if(textStatus=='success'){
                         if(data.Ack=='Success'){
                             if(data.Body.length>0){
                                 var bPositiveNum=0;
                                 var bNeutralNum=0;
                                 var bNegativeNum=0;
                                 for(var x in data.Body){
                                     if(data.Body[x].CommentType=='Positive'){
                                         bPositiveNum++;
                                     }
                                     if(data.Body[x].CommentType=='Neutral'){
                                         bNeutralNum++;
                                     }
                                     if(data.Body[x].CommentType=='Negative'){
                                         bNegativeNum++;
                                     }
                                 }
                                 $window_client_info.find('span[t="bPositive"]').html(bPositiveNum).parent().show();
                                 $window_client_info.find('span[t="bNeutral"]').html(bNeutralNum).parent().show();
                                 $window_client_info.find('span[t="bNegative"]').html(bNegativeNum).parent().show();
                             }else{
                                 $window_client_info.find('span[t="bNegative"]').parent().html(lang.feedbacklist_biz.not_feedback).show();
                             }
                         }else{
                         }
                     }else{
                     }
                 });
             })();
                
             (function(){
                 // 选项卡1
                
                 $window_client_info.find('span[t="id"]').html('<a href="http://www.ebay.com/usr/'
                         +clientId+'" target="_blank" class="fontLinkBtn">'+clientId+'</a>');
             })();
             
             (function(){
                 // Item图片显示
                     $.get("?r=api/GetEbayUserInfo",{userid:clientId},function(data,status){
                         if(status==='success'){
                             if(data.Ack==='Success'){
                                 $window_client_info.find('span[t="regaddr"]').html(data.Body.Site);
                                 if(data.Body.RegistrationDate>0){
                                     $window_client_info.find('span[t="RegistrationDate"]').html(intToLocalDate(data.Body.RegistrationDate,3)).parent().show();
                                 }
                                 if(data.Body.PositiveFeedbackPercent>=0){
                                     $window_client_info.find('span[t="PositiveFeedbackPercent"]').html(data.Body.PositiveFeedbackPercent).parent().show();
                                 }
                                 if(data.Body.UniquePositiveFeedbackCount>=0){
                                     $window_client_info.find('span[t="Positive"]').html(data.Body.UniquePositiveFeedbackCount).parent().show();
                                 }
                                 if(data.Body.UniqueNeutralFeedbackCount>=0){
                                     $window_client_info.find('span[t="Neutral"]').html(data.Body.UniqueNeutralFeedbackCount).parent().show();
                                 }
                                 if(data.Body.UniqueNegativeFeedbackCount>=0){
                                     $window_client_info.find('span[t="Negative"]').html(data.Body.UniqueNegativeFeedbackCount).parent().show();
                                 }
                                 (function(){
                                     var RaddrStr='';
                                     
                                     RaddrStr+=data.Body.regaddr_Name+', ';
                                     RaddrStr+=data.Body.regaddr_Street+', ';
                                     RaddrStr+=data.Body.regaddr_Street1+', ';
                                     data.Body.regaddr_Street2.length<=0?'':RaddrStr+=data.Body.regaddr_Street2+', ';
                                     RaddrStr+=data.Body.regaddr_CityName+', ';
                                     data.Body.regaddr_StateOrProvince.length<=0?'':RaddrStr+=data.Body.regaddr_StateOrProvince+', ';
                                     RaddrStr+=data.Body.regaddr_PostalCode+', ';
                                     RaddrStr+=data.Body.regaddr_CountryName;
                                     data.Body.regaddr_Phone.length<=0?'':RaddrStr+='<br />'+lang.feedbacklist_biz.phone+': '+data.Body.regaddr_Phone+', ';
                                     
                                     if(RaddrStr.length>12){
                                         $("#window_client_info_l").append('<li style=""><span class="leftName">'+lang.feedbacklist_biz.reg_address+'：</span><span class="rightVal" style="width:370px;">'
                                             +RaddrStr+'</span></li>');
                                     }
                                 })();
                                 
                             } else {
                                 // hintShow('hint_f','服务器错误!');
                             }
                         } else {
                             hintShow('hint_f',lang.ajaxinfo.network_error);
                         }
                     });
             })();

             /**
              * @desc 获取购物历史
              * @author  liaojianwen
              * @date 2015-07-31
              */
             (function(){
                 $.get('?r=api/GetEbayTransactionsByUserId',{BuyerUserID:clientId},function(data,textStatus){
                     if(textStatus=='success'){
                         if(data.Ack ==='Success'){
                             $("#buyHistory_div").find('tbody').empty();
                             if(data.Body.length>0){
                                 for(var x in data.Body){
                                     var transStr='';
                                     for(var y in data.Body[x].ExtTrans){
                                         transStr+=('<div class="textleft tooltip_td" title="'+lang.feedbacklist_biz.transaction_time+'：'+intToLocalDate(data.Body[x].ExtTrans[y].ExternalTransactionTime,3)
                                             +'<br />'+lang.feedbacklist_biz.amount+data.Body[x].ExtTrans[y].PaymentOrRefundAmount+data.Body[x].ExtTrans[y].PaymentOrRefundAmount_currencyID+''
                                             +'<br />'+lang.feedbacklist_biz.FVF+data.Body[x].ExtTrans[y].FeeOrCreditAmount+data.Body[x].ExtTrans[y].FeeOrCreditAmount_currencyID+''
                                             +'<br />'+lang.feedbacklist_biz.transaction_status+data.Body[x].ExtTrans[y].ExternalTransactionStatus+''
                                             +'">'+data.Body[x].ExtTrans[y].ExternalTransactionID
                                             +'('+ data.Body[x].ExtTrans[y].PaymentOrRefundAmount+data.Body[x].ExtTrans[y].PaymentOrRefundAmount_currencyID +')'
                                             +'</div>');
                                     }
//                                     // 属性
                                     var _shuxing='';
                                     var _feedback='';
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
                                            default :
                                                _feedback = lang.feedbacklist_biz.none;
                                     }
                                     var addressStr = '';
                                     if(data.Body[x].CountryName !== null){
                                            addressStr+=data.Body[x].Name+', ';
                                            addressStr+=data.Body[x].Street1+', ';
                                            if(data.Body[x].Street2.length>0){
                                                addressStr+=data.Body[x].Street2+', ';
                                            }
                                            addressStr+=data.Body[x].CityName+', ';
                                            addressStr+=data.Body[x].StateOrProvince+', ';
                                            addressStr+=data.Body[x].PostalCode+', ';
                                            addressStr+=data.Body[x].CountryName;
                                            addressStr+='\n'+lang.feedbacklist_biz.phone+':'+data.Body[x].Phone;
                                     }
                                     
                                     var orderStatus = '';
                                     if(data.Body[x].OrderStatus){
                                         orderStatus=data.Body[x].OrderStatus;
                                     }
                                 
                                     if(
                                     typeof data.Body[x].VariationSpecificsXML.xml.NameValueList!=='undefined'&&
                                     typeof data.Body[x].VariationSpecificsXML.xml.NameValueList!=='string'&&
                                     (data.Body[x].VariationSpecificsXML.xml.NameValueList.length>0||(
                                         typeof data.Body[x].VariationSpecificsXML.xml.NameValueList.Name==='string'&&
                                         data.Body[x].VariationSpecificsXML.xml.NameValueList.Name.length>0
                                     ))){
                                         if(data.Body[x].VariationSpecificsXML.xml.NameValueList.length>0){
                                             for(var j=0;j<data.Body[x].VariationSpecificsXML.xml.NameValueList.length;j++){
                                                 _shuxing += '<br/>'+data.Body[x].VariationSpecificsXML.xml.NameValueList[j].Name+':'
                                                     +data.Body[x].VariationSpecificsXML.xml.NameValueList[j].Value;
                                             }
                                         }else{
                                             _shuxing += '<br/>'+data.Body[x].VariationSpecificsXML.xml.NameValueList.Name+':'
                                                 +data.Body[x].VariationSpecificsXML.xml.NameValueList.Value;
                                         }
                                     }
//                                     
                                     $("#buyHistory_div").find('tbody').append('<tr data-key='+x+'><td>'+data.Body[x].SellerUserID
                                             +'</td><td t="sku" style="word-break:break-all;">'
                                             +(data.Body[x].Item_SKU!=''?data.Body[x].Item_SKU:data.Body[x].Variation_SKU)
                                             +'<br />ItemID:<a href="http://cgi.ebay.com/ws/eBayISAPI.dll?ViewItem&item='+data.Body[x].Item_ItemID+'" target="_blank" class="fontLinkBtn">'+data.Body[x].Item_ItemID+'</a>'
                                             +(data.Body[x].ProductName.length>0 ? ('<br />Prodtct:'+HTMLDecode(HTMLDecode(data.Body[x].ProductName))) : '')
                                             +_shuxing
                                             +'</td><td t="pnum">'+data.Body[x].QuantityPurchased
                                             +'</td><td t="pjg">'+data.Body[x].TransactionPrice+data.Body[x].TransactionPrice_currencyID
                                             +'</td><td t="ptime" style="word-break:break-all;line-height:1.1em;">'
                                             +intToLocalDate(data.Body[x].CreatedDate,8)+'</td><td t="pm" style="line-height:1.5em;">'
                                             +data.Body[x].PaymentMethod+transStr+'</td>'+'<td><a href="javascript:;" title="'+addressStr+'" class="tooltip_td">'+lang.feedbacklist_biz.view+'</a></td>'
                                             +'<td>'+orderStatus+'</td>'+'<td class="evaluate">'+_feedback+'</td>'+'<td t="note" class="tooltip_td">..</td></tr>');
                                     
                                     (function(){
                                         // 获取item备注
                                    	 var _key = x;
                                         $.get('?r=api/GetItemNotes',{itemId:data.Body[x].Item_ItemID},function(data,textStatus){
                                             if(data.Ack=='Success'){
                                                 var noteText=[];
                                                 for(var x in data.Body){
                                                     noteText[x]=(lang.feedbacklist_biz.Notes.author+'：'+data.Body[x].author_name
                                                         +'<br />'+lang.feedbacklist_biz.Notes.content+'：'+data.Body[x].text
                                                         +'<br />'+lang.feedbacklist_biz.Notes.buyer+'：'+data.Body[x].cust
                                                         +'<br />'+lang.feedbacklist_biz.Notes.time+'：'+intToLocalDate(data.Body[x].create_time,7));
                                                 }
                                                 $("#buyHistory_div").find('tr[data-key='+_key+']').find('td[t="note"]').html('<i class="icon-remark"></i>').attr('title',noteText.join('\n\n'));
                                                 //执行鼠标经过气泡提示方法
                                                tooltip($('.tooltip_td'));
                                             }else if(data.Ack=='Warning'){
                                                 $("#buyHistory_div").find('tr[data-key='+_key+']').find('td[t="note"]').html(lang.feedbacklist_biz.none);
                                             }else{
                                                 $("#buyHistory_div").find('tr[data-key='+_key+']').find('td[t="note"]').html('✖');
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
                             }else{
                                 $("#buyHistory_div").find('tbody').append('<tr><td colspan="10">'+lang.feedbacklist_biz.not_data+'</td></tr>');
                             }
                         }else{
                         }
                     }else{
                     }
                 });
             })();
             
             /**
              * @desc 消息历史
              * @author lioajianwen
              * @date 2015-07-31
              */
             (function(){
                 var $his_msgs_ul=$("#his_msgs_ul");
                 var page = $his_msgs_ul.data('page');
                 // var noloading = false;
                 function loadlist(xcpage){
                     $his_msgs_ul.empty();
                     $his_msgs_ul.html('<li style="text-align:center; padding:12px;"><img src="public/template/img/loader.gif" width="31" height="31"></li>');
                     // his_msgs_ul
                     $.get('?r=api/GetMsgsByUserID',{UserID:clientId,page:xcpage},function(data,textStatus){
                         if(textStatus=='success'){
                             if(data.Ack ==='Success'){
                                 $his_msgs_ul.empty();
                                 if(data.Body.length>0){
                                     for(var x in data.Body){
                                         $his_msgs_ul.append('<li data-msgid="'+data.Body[x].msg_id+'" title="'+data.Body[x].Subject+'">'
                                            +'<span>'+intToLocalDate(data.Body[x].ReceiveDate,9)+'</span><p>'
                                            +data.Body[x].nick_name+' | '
                                             +((data.Body[x].FolderID==1)?lang.feedbacklist_biz.send+'：':lang.feedbacklist_biz.receive+'：')
                                             +data.Body[x].Subject+'</p></li>');
                                     }
                                     // noloading = true;
                                     $his_msgs_ul.find('li').eq(0).click();
                                 }else{
                                     $his_msgs_ul.html('<li>'+lang.feedbacklist_biz.not_msg+'</li>');
                                     $('#his_msg_box2').attr('style','');
                                     $("#his_msg_box2").html('');
                                     if(page>1){
                                         hintShow('hint_w',lang.feedbacklist_biz.last_page_tip);
                                         page--;
                                         loadlist(page);
                                     }
                                 }
                             }else{
                             }
                         }else{
                         }
                     });
                 }
                 
                 loadlist(page);
                 
                 $("#his_msgs_up").on('click',function(){
                     if(page>1){
                         page--;
                     }else{
                         hintShow('hint_w',lang.feedbacklist_biz.first_page_tip);
                     }
                     loadlist(page);
                 });
                 
                 $("#his_msgs_down").on('click',function(){
                     page++;
                     loadlist(page);
                 });
                 
             })();
             
             	//获取模版
                 (function(){
	                 function getSubClassHtml(pid,data){
	                     var obj = getSubClass(pid,data);
	                     if(obj.length==0){
	                         $.get("?r=api/GetTpList",{'pid':pid,'_rnd':loading()},function(listdata,state){
	                             removeloading();
	                             if(state === 'success'){
	                                 if(listdata.Body.list !==''){
	                                     $.each(listdata.Body.list,function(index,item){
	                                         $("#temp_msg select[pid="+pid+"]").append('<option value="'+item.tp_list_id+'">'+item.title+'</option>');
	                                     });
	                                     $("#temp_msg select[pid="+pid+"]").change(function(e) {
	                                         var fid=$(this).find('option:selected').val();
	                                         $(this).nextAll().remove();
	                                         $.each(listdata.Body.list,function(index,item){
	                                             if(fid===item.tp_list_id){
	                                                 $("#text_msg").val(item.content);
	                                             }
	                                         });
	                                     });
	                                 }
	                             }
	                         });
	                         
	                     }
	                     $("#temp_msg").append('<select pid="'+pid+'"><option value="">'+lang.feedbacklist_biz.please_select+'</option></select>');
	                     for(var i in obj){
	                         $("#temp_msg select[pid="+pid+"]").append('<option value="'+obj[i].tp_class_id+'">'+obj[i].classname+'</option>');
	                     }
	                     $("#temp_msg select[pid="+pid+"]").change(function(e) {
	                         var npid=$(this).find('option:selected').val();
	                         $(this).nextAll().remove();
	                         if(npid>0){
	                             getSubClassHtml(npid,data);
	                         }
	                     });
	                 }
	                 //获取信息模版方法
	                 $("#temp_msg").empty();
	                 $.get("?r=api/GetTpClassList",function(data,state){
	                     if(state=='success'){
	                         
	
	                         getSubClassHtml(0,data);
	                     } else {
	                         hintShow('hint_f',lang.ajaxinfo.network_error);
	                     }
	                 });
                 })();
        
              (function(){
                 function getSubClassHtml(pid,data){
                     var obj = getSubClass(pid,data);
                     if(obj.length==0){
                         $.get("?r=api/GetTpList",{'pid':pid,'_rnd':loading()},function(listdata,state){
                             removeloading();
                             if(state === 'success'){
                                 if(listdata.Body.list !==''){
                                     $.each(listdata.Body.list,function(index,item){
                                         $("#temp_feedback select[pid="+pid+"]").append('<option value="'+item.tp_list_id+'">'+item.title+'</option>');
                                     });
                                     $("#temp_feedback select[pid="+pid+"]").change(function(e) {
                                         var fid=$(this).find('option:selected').val();
                                         $(this).nextAll().remove();
                                         $.each(listdata.Body.list,function(index,item){
                                             if(fid===item.tp_list_id){
                                                 $("#text_feedback").val(item.content);
                                             }
                                         });
                                     });
                                 }
                             }
                         });
                         
                     }
                     $("#temp_feedback").append('<select pid="'+pid+'"><option value="">'+lang.feedbacklist_biz.please_select+'</option></select>');
                     for(var i in obj){
                         $("#temp_feedback select[pid="+pid+"]").append('<option value="'+obj[i].tp_class_id+'">'+obj[i].classname+'</option>');
                     }
                     $("#temp_feedback select[pid="+pid+"]").change(function(e) {
                         var npid=$(this).find('option:selected').val();
                         $(this).nextAll().remove();
                         if(npid>0){
                             getSubClassHtml(npid,data);
                         }
                     });
                 }
                 //获取信息模版方法
                 $("#temp_feedback").empty();
                 $.get("?r=api/GetTpClassList",function(data,state){
                     if(state=='success'){

                         getSubClassHtml(0,data);
                     } else {
                         hintShow('hint_f',lang.ajaxinfo.network_error);
                     }
                 });
             })();
        })
        
    })();
    
   /**
    * @desc 点击消息历史
    * @author liaojiannwen
    * @date 2015-08-27
    */ 
    $("#his_msgs_ul").on('click','li',function(){
        $('#his_msgs_ul').find('li').each(function(index, element) {
            $(this).removeClass('active');
        });
        if($(this).data('msgid')){
            $(this).addClass('active');
            $("#his_msg_box2").html('<div style="margin:50px auto; text-align:center;"><img src="public/template/img/loader.gif" width="31" height="31"></div>');
            $.get('?r=api/getMsg',{msgid:$(this).data('msgid')},function(data,textStatus){
                // 历史消息内容
                removeloading();
                if(textStatus=='success'){
                    if(data.Ack ==='Success'){
                        
                        var $msgtext=$('<div>'+data.Body.Text+'</div>');
                        $msgtext.find('meta').remove();
                        $msgtext.find('img').each(function(index, element) {
                            if($(element).attr('src')!==undefined && $(element).attr('src').indexOf('button')==-1 && $(element).attr('src').indexOf('ebaystatic')==-1){
                                if($(element).parent('a').length==0){
                                    $(element).replaceWith('<a>'+$(element)[0].outerHTML+'</a>');
                                }
                            }
                        });
                        $msgtext.find('img').each(function(index, element) {
                            if($(element).attr('src')!==undefined && $(element).attr('src').indexOf('button')==-1
                             && $(element).attr('src').indexOf('ebaystatic')==-1
                             && $(element).attr('src').indexOf('/globalAssets/')==-1
                             && $(element).attr('src').indexOf('/icon/')==-1
                             && $(element).attr('src').indexOf('/roveropen/')==-1){
                                $(element).parent('a').attr('data-lightbox','imgGroup14');
                                var imgurl = $(element).attr('src').toString().replace(/(%24|\$)_\d+(?=\.(jpg|png|jpeg|gif|bmp|tif)\??)/i,'$_10');
                                $(element).parent('a').attr('href', imgurl);
                            }
                        });
                        $msgtext.find('a').each(function(index, element) {
                            if($(element).attr('href')!==undefined && $(element).attr('href').indexOf('http://')!=-1){
                                $(element).attr('target','_blank');
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
                            if ($(element).attr('id')!=undefined) {
                                $(element).addClass('L2'+$(element).attr('id'));
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
                            if(res != null) {
                                $(element).closest('div').hide();
                            }
                        });
                        
                        $("#his_msg_box2").html($msgtext);
                        $("#his_msg_title").html(data.Body.Subject);
                    }else{
                    }
                }else{
                }
            })
        }
    });
    
    
    
    
    
    $('#cancelUser').on('click',function(){
        $(parent.document).find('.reFull').click();
        $('#more_div').hide();
        $('#note').find('li').empty()//清空备注
        $('#ReplyMe').removeAttr('checked');
        $('#text_msg').val('');
        $('#text_feedback').val('');
        $('#text_change').val('');
        $('#note').val('');
        $('.imglist_addwindow').empty();
        img =[];
        $('#choose_pic').removeAttr('disabled');
        $('.imgShow').hide();
        $('#feedback_length').nextAll().remove();
        $('#msgStatus').attr('checked',false);
        $('#requestStatus').attr('checked',false);
        $('#msgStatus').attr('disabled',false);
        $('#requestStatus').attr('disabled',false);
        $('#saveStatus').attr('disabled',false);
        $('#his_msg_title').html('');
        $('#saveStatus').removeClass('btnGray');
        $('#requestOutTime').attr('checked',false);
        $('#feedbackOutTime').attr('checked',false);
        $('#declineChange').attr('checked',false);
        $('#requestOutTime').attr('disabled',false);
        $('#feedbackOutTime').attr('disabled',false);
        $('#declineChange').attr('disabled',false);
        feedbackList();
        
    });
        
    /**
     * @desc 订单备注
     * @author liaojianwen
     * @date 2015-08-26
     */
    function getFeedbackNote(itemid,clientId){
        $.get('?r=api/GetFeedbackNote',{'itemid':itemid,'clientid':clientId},function(data,status){
            if(status=='success' && data.Ack == 'Success'){
                var M = data.Body;
                if(M && M.length > 0){
                    $('#note li').remove();
                    for(var i in M){
                        $('<li>'+
                        '<span>'+M[i].text+'-----</span>'+
                        '<span>'+intToLocalDate(M[i].create_time,3)+'&nbsp;</span>'+
                        '<b>'+M[i].author_name+'</b>'+
                        '</li>').appendTo("#note");
                    }
                }
            }
        })
    };
    
    
    /**
     * @desc 添加feedback备注事件
     * @author liaojianwen
     * @date 2015-08-26
     */
    (function(){
        $("#sub-note").on('click',function(){
            var text = $("#note-text").val();
            if(text == ''){
                hintShow('hint_w',lang.feedbacklist_biz.cannot_empty);
                return ;
            }
            var itemid = global_item;
            var clientId = global_client;
            $.ajax({
                url:'?r=api/AddFeedbackNote&itemid='+itemid+'&clientid='+clientId+'&text='+text,
                success:function(data,state){  
                    if (state == 'success'){
                        if(data.Ack == 'Success'){
                            $("#note-text").val('');
                            getFeedbackNote(itemid,clientId);
                        } else if(data.Error == 'User authentication fails'){
                            hintShow('hint_w',lang.ajaxinfo.permission_denied);
                        } else {
                            hintShow('hint_f',lang.ajaxinfo.network_error);
                        }
                    }
                }
            })
        })
    })();
    
    $('#determine').on('click',function(){
        if($('#imglistli').find('li').length==0){
            hintShow('hint_w',lang.feedbacklist_biz.please_select_picture);
            return;
        }
        
        $('.addImgTC').hide();
        
        $('.imglist_addwindow').empty();
        for(var i in img){
            $('<li><i class="icon-close iconBtnS" title="'+lang.feedbacklist_biz.click_delete+'"></i><img src="'+img[i]+'" width="70px" height="60px"></li>').appendTo('.imglist_addwindow');
        }
        if(i>=0 && img.length>0){ //如果上传了图片以及图片数组长度大于0，则展示图片
            $('.imgShow').show();
//            imgShowAn(-1,'true','隐藏');
        }
    });
    
    //图片上传
    (function(){
        $("#drop-area-div").dmUploader({
            url:'?r=api/Upload',
            allowedTypes:'image/*',
            extFilter:'jpg;png;gif;bmp;tif',
//            maxFiles:5,
            maxFileSize:1024*1024,
            dataType:'json',
            fileName:'Filedata',
            onInit: function(){},
            onFallbackMode: function(message){},
            onNewFile: function(id, file){},
            onBeforeUpload: function(id){
                if(img.length>=5){
                    hintShow('hint_w',lang.feedbacklist_biz.max_picture_tip);
                    $("#drop-area-div").find('input').attr('disabled','disabled');
                    return false;
                }
            },
            onComplete: function(){},
            onUploadProgress: function(id, percent){},
            onUploadSuccess: function(id, data){
                if(data.Ack == 'Success'){
                    img.push(data.Body.filepath);
                    $('<li><i class="icon-close iconBtnS" title="'+lang.feedbacklist_biz.click_delete+'" data-id="'+id+'"></i><img src="'
                        +data.Body.filepath+'" width="70px" height="60px"></li>').appendTo('.imglist_addwindow');
                }else{
                    hintShow('hint_f',lang.feedbacklist_biz.upload_error);
                    return false;
                }
            },
            onUploadError: function(id, message){
            	hintShow('hint_w',lang.feedbacklist_biz.upload_error_network+message);
            },
            onFileTypeError: function(file){
            	hintShow('hint_w',lang.feedbacklist_biz.picture_type_error);
            },
            onFileSizeError: function(file){
                hintShow('hint_w',lang.feedbacklist_biz.picture_size_error);
            },
            onFileExtError: function(file){
            	hintShow('hint_w',lang.feedbacklist_biz.picture_ext_error);
            },
            onFilesMaxError: function(file){
                hintShow('hint_w',lang.feedbacklist_biz.picture_num_error);
            }
        });
    })();
    // 点击添加附件事件
    $('#addFile,#addFiles').on('click',function(){
        $('.addImgTC').show();
        $('.imglist_addwindow').empty();
        for(var i in img){
            $('<li><i class="icon-close iconBtnS" title="'+lang.feedbacklist_biz.click_delete+'"></i><img src="'+img[i]+'" width="70px" height="60px"></li>').appendTo('.imglist_addwindow');
        }
    });
    // 点击关闭添加文件窗口
    $('#cancelUserPic').on('click',function(){
        $('.addImgTC').hide();
        $('.imglist_addwindow').empty();
        for(var i in img){
            $('<li><i class="icon-close iconBtnS" title="'+lang.feedbacklist_biz.click_delete+'"></i><img src="'+img[i]+'" width="70px" height="60px"></li>').appendTo('.imglist_addwindow');
        }
    });
    
    // 删除添加窗口的图片列表
    $('.imglist_addwindow').on('click','.icon-close',function(){
        var picurl = $(this).parents('li').find('img').attr("src");
        img.splice($.inArray(picurl,img),1);
        $(this).parent().remove();
        if(!img.length){
            $('.imgShow').hide();
        }
        // 启用控件
        $("#drop-area-div").find('input').removeAttr('disabled');
    });
    
    //绑定回复信息事件
    $('#sendMsg').on('click',function(){
        var msgBody = $('#text_msg').val();
        var feedbackID = $('.reviewsText').data('feedbackid');
        var createTime = $('#orderCreateTime').data('order-time');
        var currentTime = Date.parse(new Date())/1000;
        var endTime = currentTime - 90*24*60*60;
        if(!!createTime && (createTime < endTime)){
            hintShow('hint_w',lang.feedbacklist_biz.cannot_contact_the_buyer);
            return;
        }
        if(msgBody ==='' || msgBody==undefined){
            hintShow('hint_w',lang.feedbacklist_biz.msg_cannot_empty);
            return;
        }
        if($("#ReplyMe").is(':checked')){
            var SendMyEmail = true;
        } else {
            var SendMyEmail = false;
        }
        loading();
        $.post('?r=Api/GenerateContactMsgQueue',{'feedbackId':feedbackID,'content':msgBody,'imgurl':img,'emailCopyToSender':SendMyEmail},function(result,state){
            removeloading();
            if(state === 'success'){
                if(result.Error=='User authentication fails'){
                    hintShow('hint_w',lang.ajaxinfo.permission_denied);
                    return;
                }
                if(result.Ack==='Success'){
                    hintShow('hint_s',lang.feedbacklist_biz.msg_send_success);
                    $oSendContent.val('').change();
                    $('#ReplyMe').attr('checked',false);
                    $("#sendContent").addClass('sentIcon');
                    $('#msgStatus').attr('checked','checked');
                    $('#msgStatus').attr('disabled',true);
                    var s = 3;
                    img = [];
                    $('.imgShow').hide(200);
                    $('.imglist_addwindow').empty();
                    //3秒后隐藏发送图标
                    var stime = setInterval(function(){
                        if(s==0){
                            $("#sendContent").removeClass('sentIcon');
                            clearInterval(stime);
                        }
                        s--;
                    },1000);
                    
                }else{
                    hintShow('hint_f',lang.feedbacklist_biz.msg_send_failure);
                }
            }else{
                hintShow('hint_f',lang.feedbacklist_biz.msg_send_network_error);
            }
        });
    });
    
    /**
     * @desc 回复feedback
     * @author liaojianwen
     * @date 2015-08-28
     */
    $('#responseFeedback').on('click',function(){
        var response_text = $('#text_feedback').val();
        if(response_text == ''){
            hintShow('hint_w',lang.feedbacklist_biz.fb_msg_cannot_empty);
            return ;
        }
        var feedbackID = $('.reviewsText').data('feedbackid');
        $.get('?r=api/ResponseFeedback',{'fedID':feedbackID,'text':response_text},function(data,status){
            if(status=='success' && data.Ack=='Success'){
                 hintShow('hint_s',lang.feedbacklist_biz.reply_success);
                 var text = $('#text_feedback').val();
                $('#feedback_temp').hide();
                $('#text_feedback').parents('p').hide();
                $('#feedback_length').hide();
                $('#feedback_length').after('<small>'+lang.feedbacklist_biz.cannot_reply+'</small><p>'+lang.feedbacklist_biz.reply_content+'<span>'+text+'</span></p>');
            }
        });
        
    });
    
    /**
     * @批量回复feedback
     * @author liaojianwen
     * @date 2015-08-31
     */
    $('#saveReply').on('click',function(){
         var response_text = $('#batchText').val();
         var replyInfo=[];
         if(response_text == ''){
             hintShow('hint_w',lang.feedbacklist_biz.fb_msg_cannot_empty);
             return ;
         }
         $('#feedbackList input[type=checkbox]:checked').each(function(){
             replyInfo.push({
                 'feedbackId':$(this).attr('data-id'),
                 'text':$('#batchText').val()
             });
         });
        var global={
                'replyInfo':replyInfo
        };
        $.post('?r=api/BatchReply',global,function(data,status){
            if(status ==='success'){
            	if(data.Ack ==='Success'){
	                 hintShow('hint_s',lang.feedbacklist_biz.reply_success);
	                 $('#ReplyClient').html('');
	                 $('#batchText').val('');
	                 $('#batchReply').hide();
	                 feedbackList();
            	} else if(data.Error == 'User authentication fails'){
                    hintShow('hint_w',lang.ajaxinfo.permission_denied);
                }
            } 
        });
         
    });
    
    /**
     * @批量发消息给客户
     * @author liaojianwen
     * @date 2015-11-05
     */
    $('#saveMsg').on('click',function(){
         var response_text = $('#MsgText').val();
         var replyInfo=[];
         if(response_text == ''){
             hintShow('hint_w',lang.feedbacklist_biz.msg_cannot_empty);
             return ;
         }
         $('#feedbackList input[type=checkbox]:checked').each(function(){
             replyInfo.push({
                 'feedbackId':$(this).attr('data-id'),
                 'text':response_text,
                 'issendme':$('#isSendMe').prop('checked'),
                 'imgs':img
             });
         });
        var global={
                'msgInfo':replyInfo
        };
        loading();
        $.get('?r=api/BatchSendMsg',global,function(data,status){
        	removeloading();
        	if(status ==='success'){
        		if(data.Ack ==='Success'){
        			hintShow('hint_s',lang.feedbacklist_biz.msg_send_success);
                    $('#MsgText').val('');
                    $('.imglist_addwindow').empty();
                    img =[];
                    $('.imgShow').hide();
        		} else {
        			hintShow('hint_w',lang.feedbacklist_biz.msg_send_failure);
        		}
        	} else {
        		hintShow('hint_w',lang.feedbacklist_biz.msg_send_network_error);
        	}
        });
    });
    var $bSendContent = $('#MsgText');
    //判断msg回复字符串是否超过上限
    (function(){
        //敲键盘时候触发计算字数
        
    	$bSendContent.on('input change', function(){
            var textlength = $bSendContent.val().replace(/[^\u0000-\u00ff]/g,"aaa").length;
            var text = $bSendContent.val();
            var length = (1000 - textlength);
            if(length<0){
                $("#saveMsg").attr("disabled", true);
                hintShow('hint_w',lang.feedbacklist_biz.chars_number_error);
                $('#batch_msg_length').find('small').html(lang.feedbacklist_biz.input_number_l+'<span style="color: #f00;">'+length+'</span>'+lang.feedbacklist_biz.input_number_r);
            } else {
                $("#saveMsg").attr("disabled", false);
                $('#batch_msg_length').find('small').html(lang.feedbacklist_biz.input_number_l+'<span>'+length+'</span>'+lang.feedbacklist_biz.input_number_r);
            }
        });
    })();
    
    var $oSendContent = $('#text_msg');
    //判断msg回复字符串是否超过上限
    (function(){
        //敲键盘时候触发计算字数
        
        $oSendContent.on('input change', function(){
            var textlength = $oSendContent.val().replace(/[^\u0000-\u00ff]/g,"aaa").length;
            var text = $oSendContent.val();
            var length = (1000 - textlength);
            
            if(length<0){
                $("#sendMsg").attr("disabled", true);
                hintShow('hint_w',lang.feedbacklist_biz.chars_number_error);
                $('#msg_length').find('span').html(lang.feedbacklist_biz.input_number_l+'<b style="color: #f00;">'+length+'</b>'+lang.feedbacklist_biz.input_number_r);
            } else {
                $("#sendMsg").attr("disabled", false);
                $('#msg_length').find('span').html(lang.feedbacklist_biz.input_number_l+'<b>'+length+'</b>'+lang.feedbacklist_biz.input_number_r);
            }
        });
    })();
    
    var $oSendFeedback = $('#text_feedback');
    //判断feedback回复字符串是否超过上限
    (function(){
        //敲键盘时候触发计算字数
        
        $oSendFeedback.on('input change', function(){
            var textlength = $oSendFeedback.val().replace(/[^\u0000-\u00ff]/g,"a").length;
            var text = $oSendFeedback.val();
            var length = (80 - textlength);
            
            if(length<0){
                $("#responseFeedback").attr("disabled", true);
                hintShow('hint_w',lang.feedbacklist_biz.chars_number_error);
                $('#feedback_length').find('span').html(lang.feedbacklist_biz.input_number_l+'<b style="color: #f00;">'+length+'</b>'+lang.feedbacklist_biz.input_number_r);
            } else {
                $("#responseFeedback").attr("disabled", false);
                $('#feedback_length').find('span').html(lang.feedbacklist_biz.input_number_l+'<b>'+length+'</b>'+lang.feedbacklist_biz.input_number_r);
            }
        });
    })();
    
    var $oSendBatchFeedback = $('#batchText');
    //判断feedback回复字符串是否超过上限
    (function(){
        //敲键盘时候触发计算字数
        
        $oSendBatchFeedback.on('input change', function(){
            var textlength = $oSendBatchFeedback.val().replace(/[^\u0000-\u00ff]/g,"a").length;
            var text = $oSendBatchFeedback.val();
            var length = (80 - textlength);
            
            if(length<0){
                $("#saveReply").attr("disabled", true);
                hintShow('hint_w',lang.feedbacklist_biz.chars_number_error);
                $('#batch_length').find('small').html(lang.feedbacklist_biz.input_number_l+'<span style="color: #f00;">'+length+'</span>'+lang.feedbacklist_biz.input_number_r);
            } else {
                $("#saveReply").attr("disabled", false);
                $('#batch_length').find('small').html(lang.feedbacklist_biz.input_number_l+'<span>'+length+'</span>'+lang.feedbacklist_biz.input_number_r);
            }
        });
    })();
    
    /**
     * @desc 保存状态值
     * @author liaojianwen
     * @date 2015-09-02
     */
    (function(){
        $('#saveStatus').on('click',function(){
            var feedbackId = $('.reviewsText').data('feedbackid');
            var MsgStatus= !!$('#msgStatus').attr('checked');
            var RequestStatus = !!$('#requestStatus').attr('checked')
            var RequestOutDate = !!$('#requestOutTime').attr('checked');
            var FeedbackOutDate = !!$('#feedbackOutTime').attr('checked');
            var DeclineChange = !!$('#declineChange').attr('checked');
            if(!(MsgStatus || RequestStatus || RequestOutDate || FeedbackOutDate || DeclineChange)){
                hintShow('hint_w',lang.feedbacklist_biz.please_check_box)
                return;
            }
            var feedbackStatus = {'feedbackid':feedbackId,'msgstatus':MsgStatus,'reqstatus':RequestStatus,'reqtime':RequestOutDate,'fedtime':FeedbackOutDate,'dechange':DeclineChange};
                $.get('?r=api/SaveFeedbackStatus',feedbackStatus,function(data,status){
                    if(status=='success'){
                    	if(data.Ack ==='Success'){
	                        hintShow('hint_s',lang.shopset_biz.save_suc);
	                        if(MsgStatus == 1){
	                            $('#msgStatus').attr('disabled',true);
	                        }
	                        if(RequestStatus == 1){
	                            $('#requestStatus').attr('disabled',true);
	                        }
	                        if(RequestOutDate == 1){
	                            $('#requestOutTime').attr('disabled',true);
	                        }
	                        if(FeedbackOutDate == 1){
	                            $('#feedbackOutTime').attr('disabled',true);
	                        }
	                        if(DeclineChange == 1){
	                            $('#declineChange').attr('disabled',true);
	                        }
	                        if(MsgStatus && RequestStatus && RequestOutDate && FeedbackOutDate && DeclineChange){
	                            $('#saveStatus').addClass('btnGray');
	                        }
                    	} else if(data.Error == 'User authentication fails'){
                            hintShow('hint_w',lang.ajaxinfo.permission_denied);
                        }
                    }
                });
        });
    })();
    
    function getSubClass(pid,data){
        var result=[];
        for(var i in data){
            if(data[i].pid==pid){
                result.push(data[i]);
            }
        }
        return result;
    }
    
    /**
     * @desc 批量催款获取信息模板
     * @param int pid 模板父ID
     * @author liaojianwen
     * @date 2015-11-06
     */
    (function(){
        function getSubClassHtml(pid,data){
            var obj = getSubClass(pid,data);
            if(obj.length==0){
                $.get("?r=api/GetTpList",{'pid':pid,'_rnd':loading()},function(listdata,state){
                    removeloading();
                    if(state === 'success'){
                        if(listdata.Body.list !==''){
                            $.each(listdata.Body.list,function(index,item){
                                $("#temp_batch select[pid="+pid+"]").append('<option value="'+item.tp_list_id+'">'+item.title+'</option>');
                            });
                            $("#temp_batch select[pid="+pid+"]").change(function(e) {
                                var fid=$(this).find('option:selected').val();
                                $(this).nextAll().remove();
                                $.each(listdata.Body.list,function(index,item){
                                    if(fid===item.tp_list_id){
                                        $("#MsgText").val(item.content);
                                    }
                                });
                            });
                        }
                    }
                });
                
            }
            $("#temp_batch").append('<select pid="'+pid+'"><option value="">'+lang.feedbacklist_biz.please_select+'</option></select>');
            for(var i in obj){
                $("#temp_batch select[pid="+pid+"]").append('<option value="'+obj[i].tp_class_id+'">'+obj[i].classname+'</option>');
            }
            $("#temp_batch select[pid="+pid+"]").change(function(e) {
                var npid=$(this).find('option:selected').val();
                $(this).nextAll().remove();
                if(npid>0){
                    getSubClassHtml(npid,data);
                }
            });
        }
        //获取信息模版方法
        $("#temp_msg").empty();
        $.get("?r=api/GetTpClassList",function(data,state){
            if(state=='success'){
                getSubClassHtml(0,data);
            } else {
                hintShow('hint_f',lang.ajaxinfo.network_error);
            }
        });
    })();
    
})