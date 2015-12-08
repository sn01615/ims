"use strict";
/**
 * @desc 客户管理列表页功能
 * @author YangLong
 * @date 2015-09-06
 */
$(document).ready(function(e) {
    var _data={};
    function loadlist(_pageinfo){
        _data.page=_pageinfo?_pageinfo.page:1;
        _data.pageSize=_pageinfo?_pageinfo.pageSize:20;
        loading();
        $.get('?r=api/GetEbayUserList',_data,function(data,status){
            function undulpicate(array){
                for(var i=0;i<array.length;i++) {
                    for(var j=i+1;j<array.length;j++) {
                        if(array[i]===array[j]) {
                            array.splice(j,1);
                            j--;
                        }
                    }
                }
                return array;
            }
            removeloading();
            if(status=='success'){
                if(data.Ack=='Success'){
                    $('#cus_list').empty();
                    var _html='';
                    for(var x in data.Body.list){
                        var _addstr='';
                        _addstr+=data.Body.list[x].regaddr_Name+', ';
                        _addstr+=data.Body.list[x].regaddr_Street1+', ';
                        if(data.Body.list[x].regaddr_Street2){
                            _addstr+=data.Body.list[x].regaddr_Street2+', ';
                        }
                        _addstr+=data.Body.list[x].regaddr_CityName+', ';
                        if(data.Body.list[x].regaddr_StateOrProvince){
                            _addstr+=data.Body.list[x].regaddr_StateOrProvince+', ';
                        }
                        _addstr+=data.Body.list[x].regaddr_PostalCode+', ';
                        _addstr+=data.Body.list[x].regaddr_CountryName;
                        
                        var _casestr='';
                        for(var y in data.Body.list[x]._count_cases){
                            _casestr+=data.Body.list[x]._count_cases[y].caseId_type+': '+data.Body.list[x]._count_cases[y].num+'<br />';
                        }
                        for(var y in data.Body.list[x]._count_disputes){
                            _casestr+=data.Body.list[x]._count_disputes[y].DisputeReason+': '+data.Body.list[x]._count_disputes[y].num+'<br />';
                        }
                        if(_casestr==''){
                            _casestr='-';
                        }
                        
                        var _namestr=[];
                        for(var y in data.Body.list[x].usernames){
                            _namestr.push(data.Body.list[x].usernames[y].Buyer_UserFirstName+' '+data.Body.list[x].usernames[y].Buyer_UserLastName);
                        }
                        _namestr=undulpicate(_namestr);
                        _namestr=_namestr.join('<br />');
                        
                        _html+='<tr>';
                        _html+='<td>'+data.Body.list[x].UserID+'</td>';
                        _html+='<td>'+_namestr+'</td>';
                        _html+='<td title="'+(_addstr.length>10?_addstr:'')+'" class="tooltip_td">'+(_addstr.length>10?_addstr:'')+'</td>';
                        _html+='<td><span title="orders">'+data.Body.list[x]._count_orders+'<span> / <span title="transactions">'+data.Body.list[x]._count_trans+'</span></td>';
                        _html+='<td>'+_casestr+'</td>';
                        _html+='<td><span title="'+lang.cusmanage_biz.neutral+'">'+data.Body.list[x]._count_Neutral+'</span> / <span title="'+lang.cusmanage_biz.negative+'">'+data.Body.list[x]._count_Negative+'</span></td>';
                        _html+='<td><a class="fontBtn" href="?r=Home/CusDetails&uid='+data.Body.list[x].uid+'">'+lang.cusdet_biz.details+'</a></td>';
                        _html+='</tr>';
                    }
                    $('#cus_list').append(_html);
                    refreshPaginationNavBar(data.Body.pageInfo.page,data.Body.pageInfo.pageSize,data.Body.count,loadlist);
                    if(data.Body.list.length==0){
                        $('#cus_list').append('<tr><td colspan="7">'+lang.cusdet_biz.not_find_data+'</td></tr>');
                    }
                    tooltip($(".tooltip_td"));
                    /*$( ".tooltip_td" ).tooltip({
                        track: true,
                        content: function() {
                            var element = $( this );
                            return element.attr('title');
                        }
                    });*/
                }else{
                    // hintShow('hint_f','服务器内部错误!');
                }
            }else{
                hintShow('hint_f',lang.ajaxinfo.network_error);
            }
        });
    }
    loadlist();
    
    $("#search_btn").on('click',function(e){
        _data.searchType= $("#search_type").val();
        _data.keyword=$("#keyword").val();
        loadlist();
    });
    //查询绑定enter键
    document.onkeydown = function(event){
        if((event.keyCode || event.which) == 13){
            _data.searchType=$("#search_type").val();
            _data.keyword=$("#keyword").val();
            loadlist();
       }
    }
    
});
