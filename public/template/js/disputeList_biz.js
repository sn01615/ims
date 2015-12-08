"use strict";
/**
 * @desc 1.加载case列表
 * @author lvjianfei
 * @date 2015-3-31
 */
$(document).ready(function(e) {
    var global_info = {
        'page': $_GET['page'] ? $_GET['page'] : undefined, //页码
        'pageSize': $_GET['pageSize'] ? $_GET['pageSize'] : undefined, //分页大小
        'type': $_GET['type'], //获取case类别
        'status': undefined, //状态
        'itemid': undefined, //item NO
        'buyer': undefined, //客户
        'query': undefined
    };
    //根据类别判断调用方法
    //  if(global_info.type == 'upi' || global_info.type == 'cancel'){
    if (global_info.type == 'upi') {
        CaseDisputeList();
    } else if (global_info.type == 'cancel') {
        DisputeList();
    } else {
        CaseList();
    }

    /**
     * @desc 读取case列表
     * @author lvjianfei
     * @date 2015-3-31
     */
    function CaseList(pageInfo) {
        if (pageInfo !== undefined) {
            global_info.page = pageInfo.page;
            global_info.pageSize = pageInfo.pageSize;
        }
        loading();
        $.get("?r=api/GetCaseList", global_info, function(data, status) {
            removeloading();
            $("#CaseList").find('tr').remove();
            if (status == 'success' && data.Ack == 'Success') {
                var M = data.Body.list;
                var pageInfo = data.Body.page;
                if (M && M.length > 0) {
                    for (var i in M) {
                        var seller = M[i].user_role == 'SELLER' ? M[i].user_userId : M[i].otherParty_userId;
                        var buyer = M[i].otherParty_role == 'BUYER' ? M[i].otherParty_userId : M[i].user_userId;
                        $('<tr id="' + M[i].case_id + '"><td class="caseId_id"><span>' + M[i].caseId_id + '</span></td><td class="i_itemId"><span>' + M[i].i_itemId +
                            '</span></td><td class="seller"><span>' + seller + '</span></td><td class="buyer"><span>' + buyer + '</span></td><td id="caseId_type"><span>' + M[i].caseId_type + '</span></td><td id="create_time"><span>' + intToLocalDate(M[i].creationDate, 3) +
                            '</span></td><td class="status"><span>' + M[i].status + '</span></td><td title="' + lang.disputeList_biz.click_view_details + '"  class="casedetail"><span><a  class="fontBtn">' + lang.disputeList_biz.details + '</a></span></td></tr>').appendTo("#CaseList");
                    }
                }
                if (typeof pageInfo !== 'undefined') {
                    refreshPaginationNavBar(pageInfo.page, pageInfo.pageSize, data.Body.count, CaseList); //分页
                }
            } else {
                $('<tr><td colspan="8" align="center">' + lang.disputeList_biz.not_data + '</td></tr>').appendTo("#CaseList");
                refreshPaginationNavBar(1, 1, 1, CaseDisputeList); //分页
            }
        })
    }

    /**
     * @desc 读取卖家发起case列表
     * @author lvjianfei
     * @date 2015-3-31
     */
    function CaseDisputeList(pageInfo) {
        if (pageInfo !== undefined) {
            global_info.page = pageInfo.page;
            global_info.pageSize = pageInfo.pageSize;
        }
        loading();
        $.get("?r=api/GetCaseDisputeList", global_info, function(data, status) {
            removeloading();
            $("#CaseList").find('tr').remove();
            if (status == 'success' && data.Ack == 'Success') {
                var M = data.Body.list;
                var pageInfo = data.Body.page;
                if (M && M.length > 0) {
                    for (var i in M) {
                        $('<tr id="' + M[i].case_id + '"><td class="ids">' + M[i].DisputeID + '</td><td class="i_ItemID">' + M[i].i_ItemID +
                            '</td><td class="seller">' + M[i].SellerUserID + '</td><td class="buyer">' + M[i].BuyerUserID + '</td><td id="caseId_type">' + M[i].DisputeReason + '</td><td id="create_time">' + intToLocalDate(M[i].DisputeCreatedTime, 3) +
                            '</td><td class="status">' + M[i].status + '</td><td title="' + lang.disputeList_biz.click_view_details + '"  class="casedetail"><span><a class="fontBtn">' + lang.disputeList_biz.details + '</a></span></td></tr>').appendTo("#CaseList");
                    }
                }
                if (typeof pageInfo !== 'undefined') {
                    refreshPaginationNavBar(pageInfo.page, pageInfo.pageSize, data.Body.count, CaseDisputeList); //分页
                }
            } else {
                $('<tr><td colspan="8" align="center">' + lang.disputeList_biz.not_data + '</td></tr>').appendTo("#CaseList");
                refreshPaginationNavBar(1, 1, 1, CaseDisputeList); //分页
            }
        })
    }

    /**
     * @desc 获取cancel dispute 列表
     * @author liaojianwen
     * @date 2015-08-20
     */
    function DisputeList(pageInfo) {
        if (global_info.type == 'cancel') {
            $('#add_case_btn').show();
        }
        if (pageInfo !== undefined) {
            global_info.page = pageInfo.page;
            global_info.pageSize = pageInfo.pageSize;
        }
        loading();
        $.get('?r=api/GetCancelDisputeList', global_info, function(data, status) {
            removeloading();
            $("#CaseList").find('tr').remove();
            if (status == 'success' && data.Ack == 'Success') {
                var M = data.Body.list;
                var pageInfo = data.Body.page;
                if (M && M.length > 0) {
                    for (var i in M) {
                        $('<tr id="' + M[i].disputes_id + '"><td class="ids">' + M[i].DisputeID + '</td><td class="i_ItemID">' + M[i].i_ItemID +
                            '</td><td class="seller">' + M[i].SellerUserID + '</td><td class="buyer">' + M[i].BuyerUserID + '</td><td id="caseId_type">' + M[i].DisputeReason + '</td><td id="create_time">' + intToLocalDate(M[i].DisputeCreatedTime, 3) +
                            '</td><td class="status">' + M[i].status + '</td><td title="' + lang.disputeList_biz.click_view_details + '" class="casedetail"><span><a class="fontBtn">' + lang.disputeList_biz.details + '</a></span></td></tr>').appendTo("#CaseList");
                    }
                }
                if (typeof pageInfo !== 'undefined') {
                    refreshPaginationNavBar(pageInfo.page, pageInfo.pageSize, data.Body.count, DisputeList); //分页
                }
            } else {
                $('<tr><td colspan="8" align="center">' + lang.disputeList_biz.not_data + '</td></tr>').appendTo("#CaseList");
                refreshPaginationNavBar(1, 1, 1, CaseDisputeList); //分页
            }

        });
    }

    if (global_info.type == 'upi') {
        //绑定查询事件
        $('#searchCase').on('click', function() {
            global_info.status = $('#status').val();
            global_info.itemid = $('#itemid').val();
            global_info.buyer = $('#buyer').val();
            global_info.query = 'query';
            global_info.page = 1;
            CaseDisputeList();
        });
        //查询绑定enter键
        document.onkeydown = function(event) {
            if ((event.keyCode || event.which) == 13) {
                global_info.status = $('#status').val();
                global_info.itemid = $('#itemid').val();
                global_info.buyer = $('#buyer').val();
                global_info.query = 'query';
                global_info.page = 1;
                CaseDisputeList();
            }
        }
    } else if (global_info.type == 'cancel') {
        //绑定查询事件
        $('#searchCase').on('click', function() {
            global_info.status = $('#status').val();
            global_info.itemid = $('#itemid').val();
            global_info.buyer = $('#buyer').val();
            global_info.query = 'query';
            global_info.page = 1;
            DisputeList();
        });
        //查询绑定enter键
        document.onkeydown = function(event) {
            if ((event.keyCode || event.which) == 13) {
                global_info.status = $('#status').val();
                global_info.itemid = $('#itemid').val();
                global_info.buyer = $('#buyer').val();
                global_info.query = 'query';
                global_info.page = 1;
                DisputeList();
            }
        }

    } else {
        //绑定查询事件
        $('#searchCase').on('click', function() {
            global_info.status = $('#status').val();
            global_info.itemid = $('#itemid').val();
            global_info.buyer = $('#buyer').val();
            global_info.query = 'query';
            global_info.page = 1;
            CaseList();
        });

        //查询绑定enter键
        document.onkeydown = function(event) {
            if ((event.keyCode || event.which) == 13) {
                global_info.status = $('#status').val();
                global_info.itemid = $('#itemid').val();
                global_info.buyer = $('#buyer').val();
                global_info.query = 'query';
                global_info.page = 1;
                CaseList();
            }
        }

    }
    //返回买家发起case列表
    $("#CaseList").on('click', '.casedetail', function() {
        var page = global_info.page === undefined ? '' : global_info.page;
        var pageSize = global_info.pageSize === undefined ? '' : global_info.pageSize;
        var case_id = $(this).parents('tr').attr('id');
        var status = $(this).parents('tr').find('td[class="status"]').text();
        window.parent.caselist_url = '';
        window.parent.caselist_url = location.href + '&page=' + page + '&pageSize=' + pageSize;
        location.href = '?r=Home/DisputeDetail&caseid=' + case_id + '&type=' + global_info.type + '&status=' + status;
    })

    /**
     * @desc 获取当前用户的店铺名
     * @author lvjianfei
     * @data 2015-4-22
     */
    function getShopName() {
        $.get('?r=api/GetShopName', function(data, status) {
            if (data.Ack == 'Success' && status == 'success') {
                var M = data.Body.list;
                var shopname = '<select name="" id="Myshop_select">';
                if (M && M.length > 0) {
                    for (var i in M) {
                        shopname += '<option data-id="' + M[i].shop_id + '" value="' + M[i].seller_id + '"' + (i == 0 ? 'selected ="selected"' : '') + ' >' + M[i].nick_name + '</option>';
                    }
                    shopname += '</select>';
                }
                $('#MyShop').append(shopname);
            }
        })
    }

    //点击添加case按钮
    $('#add_case_btn').on('click', function() {
        $('#add_case').attr('class', 'TCGB TC400 addDptTC');
        $(parent.document).find('.reFull').click(); //全屏展开--linpeiyan--2015-9-17
        $('#add_case').show();
        getShopName();
    });
    //点击关闭添加case窗口按钮
    $('#add_case_close_btn').on('click', function() {
        $(parent.document).find('.reFull').click(); //取消全屏
        $('#add_case').hide();
        $('#Myshop_select').remove();
        $('#search_case').hide();
        $('#sure_case').hide();
        $('#ItemID').val('');
        $('#BuyerUserID').val('');
    });
    //点击添加case搜索按钮
    $('#search_Btn').on('click', function() {
        var buyerid = $('#BuyerUserID').val();
        var itemid = $('#ItemID').val();
        var sellerid = $('#Myshop_select').val();
        var shop_id = $('#Myshop_select').find('option:selected').attr('data-id');
        var shop_name = $('#Myshop_select').find('option:selected').text();
        var page = 1;
        var pageSize = 20;
        searchOrders(page, pageSize);
        /**
         * @desc 获取订单信息
         * @author liaojianwen
         * @date 2015-07-17
         */
        function searchOrders(pageInfo) {
            page = pageInfo.page ? pageInfo.page : page;
            pageSize = pageInfo.pageSize ? pageInfo.pageSize : pageSize;
            var global_order = {
                'SellerId': sellerid,
                'BuyerUserID': buyerid,
                'ItemID': itemid,
                'shopId': shop_id,
                'page': (page != 0 ? page : 8),
                'pageSize': pageSize ? pageSize : undefined
            }
            $.get('?r=api/SearchItem', global_order, function(data, status) {
                if (status == 'success') {
                    if (data.Ack == 'Success') {
                        var M = data.Body.list;
                        $('#select_case').empty();
                        $("#search_case").show();
                        $('#add_case').attr('class', 'TCGB TC1200 addDptTC');
                        var Order_sku;
                        var V;
                        if (M && M.length > 0) {
                            for (var i in M) {
                                Order_sku = M[i].Item_SKU ? M[i].Item_SKU : M[i].Variation_SKU;
                                var productVarition = 'ItemID:' + M[i].ItemID + '<br />';
                                if (Order_sku.length > 0) {
                                    productVarition += 'SKU:' + Order_sku + '<br />';
                                }
                                V = M[i].VariationSpecifics;
                                for (var j in V) {
                                    productVarition += j + ' : ' + V[j] + '<br />'
                                }
                                if (M[i].ProductName.length > 0) {
                                    productVarition += M[i].ProductName;
                                }
                                var gallery = M[i].gallery_url;
                                var picture = '<a data-lightbox="example-set-1" href="' + gallery + '" ><img src=' + gallery + ' alt=""></a>'
                                $('<tr><td>' + shop_name + '</td><td>' + picture + '</td><td style="text-align: left">' + M[i].OrderID + '</td><td style="text-align: left">' + productVarition + '</td><td>' + M[i].QuantityPurchased + '</td><td>' + M[i].TransactionPrice + '&nbsp' + M[i].TransactionPrice_currencyID + '</td><td>' + intToLocalDate(M[i].created_time, 3) + '</td><td>' + M[i].BuyerUserID + '</td><td><input type="checkbox" name="choose_case" data-id="' + M[i].TransactionID + '" data-orderItemId="' + M[i].OrderLineItemID + '"/></a></td></tr>').appendTo('#select_case');
                            }
                        }
                        page = data.Body.page.page;
                        pageSize = data.Body.page.pagesize;
                        if (data.Body.count == 0) {
                            $('<tr><td colspan="9">' + lang.disputeList_biz.not_data + '</td></tr>').appendTo('#select_case');
                            SearchOrderPage(1, 1, 1, searchOrders); //分页
                        } else {
                            SearchOrderPage(page, pageSize, data.Body.count, searchOrders); //分页
                        }
                    } else {
                        $('#select_case').empty();
                        $("#search_case").show();
                        $('#add_case').attr('class', 'TCGB TC1200 addDptTC');
                        $('<tr><td colspan="8">' + lang.disputeList_biz.query_fails + '</td></tr>').appendTo('#select_case');
                    }
                }
            });

        }

    });

    //选择case按钮
    $("#select_case").on("click", 'input[name="choose_case"]', function() {
        $("#sure_case").show();
    });
    $("#sub_add_case").on("click", function() {
        addCase();
    });
    //选择取消订单原因
    $('#BuyerHasNotPaid').on('click', function() {
        $('#sure_case select').remove();
        $('<select name="" id="Refund_Reason_select">' +
            '<option value="BuyerHasNotResponded">BuyerHasNotResponded</option>' +
            '<option value="BuyerNotClearedToPay">BuyerNotClearedToPay</option>' +
            '<option value="BuyerNotPaid">BuyerNotPaid</option>' +
            '<option value="BuyerRefusedToPay">BuyerRefusedToPay</option>' +
            '<option value="OtherExplanation">OtherExplanation</option>' +
            '<option value="SellerDoesntShipToCountry">SellerDoesntShipToCountry</option>' +
            '<option value="ShippingAddressNotConfirmed">ShippingAddressNotConfirmed</option></select>').appendTo('#RefundReason');
    });
    $('#TransactionMutuallyCanceled').on('click', function() {
        $('#sure_case select').remove();
        $('<select name="" id="Refund_Reason_select">' +
            '<option value="BuyerNoLongerWantsItem">BuyerNoLongerWantsItem</option>' +
            '<option value="BuyerPurchasingMistake">BuyerPurchasingMistake</option>' +
            '<option value="BuyerReturnedItemForRefund">BuyerReturnedItemForRefund</option>' +
            '<option value="OtherExplanation">OtherExplanation</option>' +
            '<option value="SellerDoesntShipToCountry">SellerDoesntShipToCountry</option>' +
            '<option value="SellerRanOutOfStock">SellerRanOutOfStock</option>' +
            '<option value="ShippingAddressNotConfirmed">ShippingAddressNotConfirmed</option>' +
            '<option value="UnableToResolveTerms">UnableToResolveTerms</option></select>').appendTo('#RefundReason');
    });

    /**
     * @desc 添加case事件
     * @date 201-04-22
     * @author lvjianfei
     */
    function addCase() {
        var arrlength;
        var itemid = [];
        var transaction = [];
        var shopid = [];
        var reason = [];
        var explanation = [];
        var res = [];
        var OrderInfo = [];
        if ($("#Refund_Reason_select").val().length == 0) {
            hintShow('hint_w', lang.disputeList_biz.select_dispute_type);
            return;
        }
        $("#select_case input[name='choose_case']:checked").each(function() {
            OrderInfo.push({
                'shopid': $('#Myshop_select option:checked').attr('data-id'),
                'DisputeReason': $("#sure_case input[name='type']:checked").attr('id'),
                'DisputeExplanation': $("#Refund_Reason_select").val(),
                'ItemID': $(this).parents('tr').find('td[class="ItemID"]').text(),
                'OrderLineItemID': $(this).attr('data-orderitemid'),
                'TransactionID': $(this).attr('data-id')
            });
        });

        var global = {
            'orderInfo': OrderInfo
        };

        $.post("?r=api/AddCase", global, function(data, status) {
            for (var i in data) {
                if (data[i].Ack === 'Success' && 　status[i] === 'Success') {
                    res.push('Success');
                } else {
                    res.push('False');
                }
            };
            if ($.inArray(res, 'False') >= 0) {
                hintShow('hint_w', lang.disputeList_biz.submit_fails);
            } else {
                hintShow('hint_s', lang.disputeList_biz.submit_fails);
            };
        });
    };

    /**
     * @desc 添加dispute时查询订单的分页
     * @author  liaojianwen
     * @dete 2015-07-23
     */
    function SearchOrderPage(page, pageSize, total, eventHandler) {
        var $paginationNavBar = $("#pageCount"); // 分页导航条节点
        if (total === undefined || total === 0) {
            $paginationNavBar.empty();
            return;
        }
        var pageSize = 20; //默认每页显示数
        var pageCount = Math.ceil(total / pageSize); // 总页数
        var currentPage = page; // 当前页码
        var template = '';
        var tt = '';
        for (var i = 1; i <= pageCount; i++) {
            tt += '<option ' + (currentPage === i ? 'selected="selected"' : '') + ' value="' + i + '">' + i + '/' + pageCount + '</option>';
        }
        if (currentPage === 1) {
            // 在第一页，禁用前一页的按钮
            template += '<div class="pageBtnBox"><select name="" id="pageCli" data-pagesize data-page>' + tt + '</select>' + '<span class="preBtn pageBtn notOpBtn"><i class="icon-chevron-left"></i></span>' + '<span ' + (currentPage === pageCount ? 'class="nextBtn pageBtn notOpBtn"' : ' data-page data-pagesize class="nextBtn pageBtn"') + '><i title="' + lang.disputeList_biz.next_page + '" class="icon-chevron-right"><a data-toggle="tooltip" data-page="' + (currentPage + 1) + '" data-pagesize="' + pageSize + '"></a></i></span></div>';
        } else {
            //不在第一页，显示前后一页的按钮
            template += '<div class="pageBtnBox"><select name="" id="" style="width:80px;" data-pagesize data-page>' + tt + '</select>' + '<span data-page data-pagesize class="preBtn pageBtn"><i title="' + lang.disputeList_biz.previous_page + '" class="icon-chevron-left"><a data-toggle="tooltip" data-page="' + (currentPage - 1) + '" data-pagesize="' + pageSize + '"></a></i></span>' + '<span ' + (currentPage === pageCount ? 'class="nextBtn pageBtn notOpBtn"' : ' data-page data-pagesize class="nextBtn pageBtn"') + '><i title="' + lang.disputeList_biz.next_page + '" class="icon-chevron-right"><a data-toggle="tooltip" data-page="' + (currentPage + 1) + '" data-pagesize="' + pageSize + '"></a></i></span></div>';
        }
        $paginationNavBar.html(template).show();
        // 页码按钮绑定事件
        $("span[data-page][data-pagesize]").on('click', function(event) {
            $('#checkall').attr("checked", false); //去掉全选
            var $currentItem = $(this);
            var selectedPage = numberClean($currentItem.find("a").data("page"));
            var selectedPageSize = numberClean($currentItem.find("a").data("pagesize"));
            if (eventHandler != undefined && (typeof eventHandler == "function")) {
                eventHandler({
                    'page': selectedPage,
                    'pageSize': selectedPageSize
                });
            }
        });
        // 分页Size按钮绑定事件
        $("select[data-page][data-pagesize]").change(function(event) {
            $('#checkall').attr("checked", false); //去掉全选
            var selectedPage = undefined;
            var selectedPage = numberClean($(this).children("option:selected").val());
            if (eventHandler != undefined && (typeof eventHandler == "function")) {
                eventHandler({
                    'page': selectedPage,
                    'pageSize': pageSize
                });
            }
        });
    }
})