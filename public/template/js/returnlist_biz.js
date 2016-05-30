"use strict";
/**
 * @desc 加载return 列表
 * @author liaojianwen
 * @date 2015-06-18
 */
$(document).ready(function(e) {
	var global_info = {
		'page': $_GET['page'] ? $_GET['page'] : undefined, //页码
		'pageSize': $_GET['pageSize'] ? $_GET['pageSize'] : undefined, //分页大小
		'status': undefined, //状态
		'itemid': undefined, //item NO
		'buyer': undefined //客户
	};
	getReturnState();
	ReturnList();

	function ReturnList(pageInfo) {
		if (pageInfo !== undefined) {
			global_info.page = pageInfo.page;
			global_info.pageSize = pageInfo.pageSize;
		}
		$.get('?r=api/GetReturnList', global_info, function(data, status) {
			$("#ReturnList").find('tr').remove();
			if (status === 'success') {
				if (data.Ack === 'Success') {
					var M = data.Body.list;
					var pageInfo = data.Body.page;
					var cust;
					if (M && M.length > 0) {
						for (var i in M) {
							if (M[i].otherParty_role === 'BUYER') {
								cust = M[i].otherParty_userId;
							} else if (M[i].responseDue_party_role === 'BUYER') {
								cust = M[i].responseDue_party_userId;
							}
							$('<tr id="' + M[i].return_request_id + '"><td><span>' + M[i].returnId_id + '<span></td><td><span>' + M[i].item_id + '</span></td><td><span>' + M[i].nick_name +
								'</span></td><td><span>' + cust + '</span></td><td><span>' + ucfirst(M[i].S_CI_reason) + '</span></td><td><span>' + intToLocalDate(M[i].creationDate, 3) +
								'</span></td><td class="status"><span>' + ucfirst(M[i].S_state) + '</span></td><td><span>' + intToLocalDate(M[i].responseDue_respondByDate, 3) +
								'</span></td><td title="' + lang.returnlist_biz.title + '"  class="returndetail"><span><a  class="fontBtn">' + lang.returnlist_biz.more_detail +
								'</a></span></td></tr>').appendTo('#ReturnList');
						}
						if (typeof pageInfo !== 'undefined') {
							refreshPaginationNavBar(pageInfo.page, pageInfo.pageSize, data.Body.count, ReturnList); //分页
						}
					}
				} else {
					$('<tr><td colspan="9" align="center">' + lang.returnlist_biz.no_data + '</td></tr>').appendTo("#ReturnList");
					refreshPaginationNavBar(1, 1, 1, ReturnList); //分页
				}
			} else {
				$('<tr><td colspan="9" align="center">' + lang.returnlist_biz.network_err + '</td></tr>').appendTo("#CaseList");
				refreshPaginationNavBar(1, 1, 1, ReturnList); //分页
			}
		});
	}

	//搜索
	$('#searchReturn').on('click', function() {
		global_info.status = $('#status').val();
		global_info.itemid = $('#itemid').val();
		global_info.buyer = $('#buyer').val();
		global_info.page = 1;
		ReturnList();
	});

	//查询绑定enter键
	document.onkeydown = function(event) {
			if ((event.keyCode || event.which) == 13) {
				global_info.status = $('#status').val();
				global_info.itemid = $('#itemid').val();
				global_info.buyer = $('#buyer').val();
				global_info.page = 1;
				ReturnList();
			}
		}
		//返回return列表
	$("#ReturnList").on('click', '.returndetail', function() {
		var page = global_info.page === undefined ? '' : global_info.page;
		var pageSize = global_info.pageSize === undefined ? '' : global_info.pageSize;
		var returnid = $(this).parents('tr').attr('id');
		var status = $(this).parents('tr').find('td[class="status"]').text();
		window.parent.returnlist_url = '';
		window.parent.returnlist_url = location.href + '&page=' + page + '&pageSize=' + pageSize;
		if (typeof global_info.status !== 'undefined') {
			var ReturnStatus = global_info.status.replace(/\s/g, ''); //去掉空格的情况
			if (ReturnStatus) {
				setCookie('ReturnStaus', ReturnStatus);
			}
		} else {
			setCookie('ReturnStaus', '');
		}
		if (typeof global_info.itemid !== 'undefined' && global_info.itemid) {
			var ReturnItemId = global_info.itemid.replace(/\s/g, '');
			if (ReturnItemId) {
				setCookie('ReturnItemId', ReturnItemId);
			}
		} else {
			setCookie('ReturnItemId', '');
		}
		if (typeof global_info.buyer !== 'undefined' && global_info.buyer) {
			var ReturnCust = global_info.buyer.replace(/\s/g, '');
			if (ReturnCust) {
				setCookie('ReturnCust', ReturnCust);
			}
		} else {
			setCookie('ReturnCust', '');
		}
		location.href = '?r=Home/ReturnDetail&returnid=' + returnid;
	});

	/**
	 * @desc 获取列表状态
	 * @author liaojianwen
	 * @date 2015-07-02
	 */
	function getReturnState() {
		var options = '<option value=" "> </option>';
		$.get('?r=api/GetReturnState', function(data, status) {
			if (status === 'success' || data.Ack === 'Success') {
				var M = data.Body;
				for (var i in M) {
					options += '<option value="' + M[i].S_state + '">' + ucfirst(M[i].S_state) + '</option>';
				}
			}
			$('#status').html(options);
		})


	}
})