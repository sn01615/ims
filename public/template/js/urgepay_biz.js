"use strict"
/**
 * @desc 加载urgepay 列表
 * @author liaojianwen
 * @date 2015-10-31
 */
$(document).ready(function(e){
    var global_info = {
            'page' : $_GET['page'] ? $_GET['page'] : undefined, // 页码
            'pageSize' : $_GET['pageSize'] ? $_GET['pageSize'] : undefined, // 分页大小
            'cust' : undefined,//客户
        };
    var clickCount = 0;
    var shippingServiceSelected;
    var additionserviceCurrencyID;
    var selectedService = []
    orderlist();
    /**
     * @desc 获取订单信息列表
     * @author liaojianwen
     * @date 2015-10-31
     */
    function orderlist(pageInfo){
        if (pageInfo !== undefined) {
            global_info.page = pageInfo.page;
            global_info.pageSize = pageInfo.pageSize;
        }
        $.get('?r=api/getUpaidOrderList',global_info,function(data,status){
        	$("#orderlist").find('tr').remove();
        	if(status =='success'){
        		if(data.Ack =='Success'){
	        		var M = data.Body.list;
	        		var pageInfo = data.Body.page;
	        		var itemInfo;
	        		var V;
	        		if(M && M.length > 0){
		        		for(var i in M){
		        			itemInfo = M[i].item_id+'<br />'+M[i].title+'<br />';
		        			if(M[i].ProductName.length > 0){
		        				itemInfo += M[i].ProductName+'<br />';
		        			}
		        			V= M[i].VariationSpecifics
		        			for(var j in V){
		        				itemInfo += j +' : '+V[j]+'<br />'
		        			}
		        			var send_count = M[i].send_count ? M[i].send_count:0;
		        			$('<tr><td><a href="'+M[i].gallery_url+'" data-lightbox="example-set-12"><img src='+M[i].gallery_url+' width="70px" height="60px"></td><td>'+M[i].nick_name+'</td><td class="orderid" data-orderid='+M[i].ebay_orders_id+' data-siteid='+M[i].site_id+'>'+M[i].OrderID+'</td><td class="iteminfo">'+itemInfo+'</td><td>'+
		        					M[i].QuantityPurchased+'</td><td class="price" data-priceid="'+M[i].TransactionPrice_currencyID+'">'+M[i].TransactionPrice+'&nbsp'+M[i].TransactionPrice_currencyID+'</td><td>'+M[i].Subtotal+'&nbsp'+M[i].Subtotal_currencyID+'</td><td>'+
		        					intToLocalDate(M[i].CreatedTime,3)+'</td><td class="buyerid" data-token='+M[i].EIASToken+'>'+M[i].BuyerUserID+'</td><td>'+send_count+'</td><td class="orderdet" data-services="'+M[i].ShippingService+
		        					'" data-cost="'+M[i].ShippingServiceCost+'" data-currencyid="'+M[i].ShippingServiceCost_currencyID+'" data-adjust_amount="'+M[i].AdjustmentAmount+'" data-adjust_currencyid="'+M[i].AdjustmentAmount_currencyID+
		        					'"><span class="fontBtn">'+lang.urgepay_biz.det+'</span></td></tr>').appendTo('#orderlist');
		        		}
	        		}
					if (typeof pageInfo !== 'undefined'){
		            	refreshPaginationNavBar(pageInfo.page,pageInfo.pageSize,data.Body.count,orderlist);//分页
		            }
	        	} else {
					$('<tr><td colspan="11" align="center">'+lang.urgepay_biz.no_data+'</td></tr>').appendTo("#orderlist");
					refreshPaginationNavBar(1,1,1,orderlist);//分页	
	        	}
        	}else {
        		$('<tr><td colspan="11" align="center">'+lang.urgepay_biz.network_error_s+'</td></tr>').appendTo("#orderlist");
        		refreshPaginationNavBar(1,1,1,orderlist);//分页
        	}
        })
    }
    //查询
    $('#search').on('click',function(){
    	var cust = $('#custinfo').val();
        global_info.cust = cust;
        global_info.page = undefined;
        global_info.pageSize = undefined;
        orderlist();
    });
    
    //查询绑定enter键
    document.onkeydown = function(event){
        if((event.keyCode || event.which) == 13){
            global_info.cust = $('#custinfo').val();
            global_info.page = undefined;
            global_info.pageSize = undefined;
            orderlist();
       }
    }
    
    //点击详情
    $('#orderlist').on('click','.orderdet',function(){
    	var _orderlist = $(this).parent('tr');
    	var itemInfo = _orderlist.find('td[class="iteminfo"]').html();
    	var BuyerUserId = _orderlist.find('td[class="buyerid"]').text();
    	var price = _orderlist.find('td[class="price"]').html();
    	var orderId = _orderlist.find('td[class="orderid"]').text();
    	var ebay_orders_id = _orderlist.find('td[class="orderid"]').data('orderid');
    	var site_id = _orderlist.find('td[class="orderid"]').data('siteid');
    	var serviceSelected = _orderlist.find('td[class="orderdet"]').data('services');
    	var serviceCost = _orderlist.find('td[class="orderdet"]').data('cost');
    	var serviceCurrencyID = _orderlist.find('td[class="orderdet"]').data('currencyid');
    	var transaction_currencyID = _orderlist.find('td[class="price"]').data('priceid');
    	var adjustAmount = _orderlist.find('td[class="orderdet"]').data('adjust_amount');
    	var adjustCurrencyID = _orderlist.find('td[class="orderdet"]').data('adjust_currencyid');
    	$('#sendInvoices').data('orderid',orderId);
    	$('#sendInvoices').data('ebay-order-id',ebay_orders_id);
    	additionserviceCurrencyID = serviceCurrencyID ? serviceCurrencyID : transaction_currencyID;
//    	$('#serviceCost').val(serviceCost);
    	$('#discount').val(adjustAmount);
        $('#currencyID').html(serviceCurrencyID);
    	$('#dis_currencyID').html(adjustCurrencyID);
    	$('#buyerInfo').find('span[t="buyerid"]').text(BuyerUserId);
    	
    	//获取客户地址
    	$.get('?r=api/GetOrderAddr',{'buyerid':BuyerUserId},function(data,status){
    		if(status ==='success' && data.Ack ==='Success'){
    			var addr = data.Body;
    			var order_addr = addr.Name+'&nbsp';
    			if(addr.Street1.length > 0){
    				order_addr += addr.Street1+'&nbsp';
    			}
    			if(addr.Street2.length > 0){
    				order_addr += addr.Street2+'&nbsp';
    			}
    			if(addr.CityName.length > 0){
    				order_addr += addr.CityName+'&nbsp';
    			}
    			if(addr.StateOrProvince.length > 0){
    				order_addr += addr.StateOrProvince+'&nbsp';
    			}
    			if(addr.PostalCode.length > 0){
    				order_addr += addr.PostalCode+'&nbsp';
    			}
    			if(addr.Phone.length > 0){
    				order_addr += addr.Phone;
    			}
    			$('#buyerInfo').find('span[t="address"]').html(order_addr);
    		}
    	});
    	//获取订单明细表
    	$.get('?r=api/GetOrderTransaction',{'orderid':orderId},function(data,status){
    		$("#order_det").find('tr').remove();
    		if(status ==='success'){
    			if(data.Ack ==='Success'){
    				var T = data.Body;
    				var flag;
    				for(var k in T){
    					$('<tr><td>'+T[k].Item_ItemID+'</td><td>'+T[k].Item_Title+'</td><td>'+T[k].QuantityPurchased+
    						'</td><td>'+T[k].TransactionPrice+'&nbsp'+T[k].TransactionPrice_currencyID+'</td><td>'+T[k].Total+'&nbsp'+T[k].Total_currencyID+'</td></tr>').appendTo('#order_det');
    					flag = T[k].options;
    				}
    				
    				
    				var _service;
    				var length;
    				var shippingService;
    				var oop =[];
    				if(flag === 1){
    					_service = T[0].ShippingServiceOptions;
    				} else if (flag === 2){
    					_service = T[0].InternationalShippingServiceOption;
    				} else{
    					_service = '';
    				}
    				length = _service.length;
    				for(var i=0; i< length; i++){
    					oop.push({
    						'shippingService' :_service[i].ShippingService,
    						'shippingServiceCost' :_service[i].ShippingServiceCost,
    						'quantity' : T[0].QuantityPurchased
    						});
    					selectedService.push(oop);
    					oop = [];
    				}
    		        $.get('?r=api/GetEbayShipService',{'id':site_id,'flag':flag},function(data,status){
    			    	if(status ==='success' && data.Ack==='Success'){
    			    		var S  = data.Body;
    			    		var service ='<option value="">Select a postal service</option>';
    			    		shippingServiceSelected  ='<option value="">Select a postal service</option>';
    			    		if(_service.length > 0){
	    			    		for(var k in selectedService){
	    				    		for(var i in S){
	    				    			if(S[i].ShippingService === selectedService[k][0].shippingService){
	    				    				service +='<option value="'+S[i].ShippingService+'" selected="selected">'+S[i].Description+'</option>';
	    				    			} else {
	    				    				service +='<option value="'+S[i].ShippingService+'">'+S[i].Description+'</option>';
	    				    			}
	    				    			shippingServiceSelected += '<option value="'+S[i].ShippingService+'">'+S[i].Description+'</option>';
	    				    		}
	    				    		if(k == 0){
	    				    			$(service).appendTo('#shippingService');
	    				    			$('#serviceCost').val(selectedService[k][0].shippingServiceCost * selectedService[k][0].quantity);
	    				    		} else {
	    				    			clickCount ++;
	    				    		    var shippingService ='<tr>'+
	    				    		       '<td ><select name="">'+service+'</select></td>'+
	    				    		       '<td><input type="text" name="" value="" align="right" class="postalCharge" id="ServiceCost'+clickCount+'"></td>'+
	    				    		       '<td><span>'+additionserviceCurrencyID+'</span></td>'+
	    				    		       '<td><span class="fontLinkBtn removeService">remove</span></td>'+
	    				    	        '</tr>';
	    				    		    $('#shippingInfo').append(shippingService);
	    				    	    	$('#ServiceCost'+clickCount).val(selectedService[k][0].shippingServiceCost * selectedService[k][0].quantity);
	    				    		}
	    			    		}
    			    		} else {
    				    			for(var i in S){
    				    				shippingServiceSelected += '<option value="'+S[i].ShippingService+'">'+S[i].Description+'</option>';
    				    			}
    				    			$('#currencyID').html(T[0].TransactionPrice_currencyID);
    				    			$(shippingServiceSelected).appendTo('#shippingService');
    			    		}
    			    	}
        		        	oop=[];
        		        	selectedService=[];
    		        	});	

    				
    			} else {
    				$('<tr><td colspan="5" align="center">'+lang.urgepay_biz.no_data+'</td></tr>').appendTo("#order_det");
    			}
    		} else {
    			$('<tr><td colspan="5" align="center">'+lang.urgepay_biz.network_error_s+'</td></tr>').appendTo("#order_det");
    		}
    		
    	});
    	
    	$('#more_det').show();
    })
    
    //关闭det
    $('#window_close').on('click',function(){
    	$('#more_det').hide();
    	$('#buyerInfo').find('span[t="address"]').html('');
    	$('#buyerInfo').find('span[t="buyerid"]').text('');
    	$("#order_det").find('tr').remove();
    	$('#shippingInfo').find('tr').eq(0).nextAll().remove();
    	$('#shippingService').empty();
    	$('#instructions').val('');
    	$('.postalCharge').val('');
    	clickCount = 0;
    	orderlist();
    })
    
    //新增服务
    $('#add_more').on('click',function(){
    	if(clickCount > 3){
    		hintShow('hint_w',lang.urgepay_biz.add_tip);
    		return;
    	}
    	clickCount++;
    	var shippingService ='<tr>'+
	        '<td ><select name="">'+shippingServiceSelected+'</select></td>'+
	        '<td><input type="text" name="" value="" align="right" class="postalCharge"></td>'+
	        '<td><span>'+additionserviceCurrencyID+'</span></td>'+
	        '<td><span class="fontLinkBtn removeService">remove</span></td>'+
        '</tr>';
    	$('#shippingInfo').append(shippingService);
    	
    });
//    
    //删除服务
    $('#shippingInfo').on('click','.removeService',function(){
    	clickCount--;
    	$(this).parent().parent('tr').remove();
    });
    
    //发送Invoices
    $('#sendInvoices').on('click',function(){
    	var serviceOptions =[];
    	var option;
    	var charge;
    	var address = $('#buyerInfo').find('span[t="address"]').html();
    	if(address.length ==0){
			hintShow('hint_w',lang.urgepay_biz.addr_warning);
    		return;
    	}
    	var discounts = $('#discount').val();
    	var isSendMe = $('#isSendMe').prop('checked');
    	var instructions = $('#instructions').val();
    	var orderId = $('#sendInvoices').data('orderid');
    	var ebay_orders_id = $('#sendInvoices').data('ebay-order-id');
    	var i = 0;
    	if(isNaN(discounts)){
    		i = 0;
    		return;
    	}
    	$('.postalCharge').each(function(index,element){
    		option =$(element).parent().prev('td').find('select').val();
    		charge = $(element).val();
    	   	if(isNaN(charge)){
        		i = 0;
        		return;
        	}
    		if(option.length > 0){
    			if(charge.length > 0){
    	    		serviceOptions.push({
	        			'serviceOption':option,
	        			'serviceValue':charge,
	        			'currencyID':additionserviceCurrencyID
	        		});
    	    		i = 1;
    			} else {
    				i = 0;
    				hintShow('hint_w',lang.urgepay_biz.cost_tip);
    	    		return;
    			}
    		} else {
    			i = 0;
    			hintShow('hint_w',lang.urgepay_biz.select_service);
        		return;
    		}
    	})
    	if(i){
	    	var params ={'orderid':orderId,'serviceoptions':serviceOptions,'text':instructions,'discount':discounts,'currencyid':additionserviceCurrencyID,'issendme':isSendMe,'ebay_orders_id':ebay_orders_id};
	    	loading();
	    	$.get('?r=api/SendInvoice',params,function(data,status){
	    		removeloading();
	    		if(status==='success'){
	    			if(data.Ack==='Success'){
	    				hintShow('hint_s',lang.urgepay_biz.send_suc);
	    				$('#isSendMe').prop('checked',false);
	    				$('#instructions').val('');
	    				$('#shippingInfo').find('tr').eq(0).nextAll().remove();
	    			} else if(data.Error == 'User authentication fails'){
	                    hintShow('hint_w',lang.ajaxinfo.permission_denied);
	    			} else {
	    				hintShow('hint_f',data.Error);
	    			}
	    		} else {
	    			hintShow('hint_f',lang.urgepay_biz.network_error_s);
	    		}
	    	})
    	}
    });
    
    //验证物流服务费用
    $('#shippingInfo').on('blur','.postalCharge',function(){
    	var partCash = /^[0-9]+(.[0-9]{1,6})?$/;
    	var Cash =$(this).val();
    	if(isNaN($(this).val())){
    		$(this).parent().parent().find('small').remove();
        	$(this).parent().parent().append('<small class="redFont">'+lang.disputeDet_biz.input_amount2+'</small>');
    	}else {
	        if(!partCash.test($(this).val())){
	        	$(this).parent().parent().find('small').remove();
	        	$(this).parent().parent().append('<small class="redFont">'+lang.disputeDet_biz.input_amount2+'</small>');
	        	
	        }else{
	        	var ServiceCost = parseFloat(Cash.replace(/^0+/,0));//去掉小数点前没有意义的0
	        	$(this).val(ServiceCost);
	        	$(this).parent().parent().find('small').removeClass('redFont');
	        }
    	}
    });
    //验证折扣费用
    $('#discount').on('blur',function(){
    	 var partCash = /^[+-]?[0-9]+(.[0-9]{1,6})?$/;
    	 var cash = $(this).val();
    	 if(isNaN($(this).val())){
    		 $(this).parent().find('small').remove();
    		 $(this).parent().append('<small class="redFont">'+lang.disputeDet_biz.input_amount2+'</small>');
    	 } else {
	    	 if(!partCash.test($(this).val())){
	    		 $(this).parent().find('small').remove();
	    		 $(this).parent().append('<small class="redFont">'+lang.disputeDet_biz.input_amount2+'</small>');
	    	 } else {
	    		 var discount = parseFloat(cash.replace(/^0+/,0));//去掉小数点前没有意义的0
		         $(this).val(discount);
	    		 $(this).parent().find('small').removeClass('redFont');
	    	 }
    	 }
    });
    
    
   
    //判断msg回复字符串是否超过上限
    (function(){
        //敲键盘时候触发计算字数
    	var $bSendContent = $('#instructions');
    	$bSendContent.on('input change', function(){
            var textlength = $bSendContent.val().replace(/[^\u0000-\u00ff]/g,"a").length;
            var text = $bSendContent.val();
            var length = (500 - textlength);
            if(length<0){
                $("#sendInvoices").attr("disabled", true);
                hintShow('hint_w',lang.feedbacklist_biz.chars_number_error);
                $('#msgText').find('small').html(lang.urgepay_biz.input_number_l+'<span style="color: #f00;">'+length+'</span>'+lang.urgepay_biz.input_number_r);
            } else {
                $("#sendInvoices").attr("disabled", false);
                $('#msgText').find('small').html(lang.urgepay_biz.input_number_l+'<span>'+length+'</span>'+lang.urgepay_biz.input_number_r);
            }
        });
    })();
})