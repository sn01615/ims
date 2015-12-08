"use strict";
/**
 * @desc 日期选择方法
 * @param obj(点击的对象)
 * @author linPeiYan
 * @date 2015-04-14
 */
function dateSelFun(obj) {
    obj.datetimepicker({
        format: "yyyy-mm-dd",
        weekStart: 1,
        minView: 2,
        autoclose: true,
        todayBtn: true,
        todayHighlight: true,
        forceParse: true,
        language: 'zh-CN'
    });
}

/**
 * @descr 选项卡效果
 * @param "str" 点击焦点对象组
 * @param "str" 切换内容对象组
 * @author linPeiYan
 * @date 2015-6-12
 */
function tabFun(obj1, obj2) {
    $(obj1).on('click', function(e) {
        var index = $(this).index(obj1);
        $(obj1).removeClass('active');
        $(this).addClass('active');
        $(obj2).hide();
        $(obj2).eq(index).show();
    })
}
/**
 * @descr 提示层类型显示
 * @param hintType('字符串') 提示类型  hint_s(操作成功); hint_f(操作失败); hint_w(警告)
 * @param hintText 提示文本
 * @author linPeiYan
 * @date 2015-02-12
 */
$(parent.document).find('.hint').remove();
var hintV = { //提示框判断值 
    'hintOn': false,
    'timeShow': ''
}

function hintShow(hintType, hintText) {
    hintV.hintOn = true;
    var $parDoc = $(parent.document);
    var $newE = $('<div class="hint"><span></span><div class="hintClose">×</div></div>');
    clearTimeout(hintV.timeShow);
    $parDoc.find('.hint').remove();
    $parDoc.find('body').append($newE);
    $newE.addClass(hintType);
    $parDoc.find('.hint span').html(hintText);
    $newE.animate({
        top: 0
    }, 300, function() {
        if (hintV.hintOn) {
            hintV.hintOn = false;
            autoHide();
        }
    });
}
//自动收起提示框（3秒）

function autoHide() {
    hintV.timeShow = setTimeout(function() {
        hintHide();
    }, 3000);
}
//手动收起提示框

function hintHide() {
    $(parent.document).find('.hint').animate({
        top: -100
    }, 300, function() {
        $(this).remove();
    });
}
//点击提示层关闭按钮
$(parent.document).find('body').on('click', '.hint .hintClose', function() {
    //clearTimeout(hintV.timeShow);
    hintHide();
});

/**
 * @descr 状态提示框  stateBoxFun()
 * @param type('字符串') 提示类型  show(显示); hide(移除); 注：如果类型是hide,那么状态提示框会在半秒内自动消失。
 * @param text('字符串') 提示文字
 * @author linPeiYan
 * @date 2015-07-27
 * 如：stateBoxFun('show','请稍等，正在处理中...');
 *     stateBoxFun('hide','已处理完毕');
 */
function stateBoxFun(type, text) {
    var $newE = $('<div class="TCGB stateGB" style="display:block;"><p class="stateBox">' + text + '</p></div>')
    $('body .stateGB').remove();
    $('body').append($newE);
    if (type == 'hide') {
        setTimeout(function() {
            $('body .stateGB').fadeOut('slow', function() {
                $('body .stateGB').remove();
            });
        }, 500)
    }
}

/**
 * @descr 模拟下拉框
 * @author linPeiYan
 * @date 2015-03-19
 */
$('.comboBox').hover(function() {
    $(this).find('.selList').show();
}, function() {
    $(this).find('.selList').hide();
})

/**
 * @descr 模拟确认框
 * @param confirmFun  1.fun1(确定--类型为：function); 2.fun2(取消--类型为：function); 3.确认框文本 类型：字符串;
 * @author linPeiYan
 * @date 2015-03-9
 */
function confirmFun(fun1, fun2, warnText) {
    var conBox = $('<div class="TCGB TC400 confirmBox" style="display:block;">' +
        '<div class="TCBox">' +
        '<h2>' + lang.common.warning + '：<span class="TCClose"><i class="icon-remove" id="close_btn"></i></span></h2>' +
        '<div class="TCBody">' +
        '<p class="warnText">' + warnText + '</p>' +
        '<p><button class="subBtn" id="confirmBtn">' + lang.com.OK + '</button> <button class="subBtn" id="cancelBtn">' + lang.com.CANCEL + '</button>' +
        '</p>' +
        '</div>' +
        '</div>' +
        '</div>');
    $('body').append(conBox);
    $('#confirmBtn').off('click').on('click', function() { //当点击确认按钮时执行fun1
        $('.confirmBox').remove();
        fun1();
    });
    $('#cancelBtn').off('click').on('click', function() { //当点击取消按钮时执行fun2
        $('.confirmBox').remove();
        fun2();
    })
    $('#close_btn').off('click').on('click', function() { //当点击关闭按钮时return
        $('.confirmBox').remove();
        return;
    });
}

/**
 * @descr 弹窗拖拽功能
 * @param 无需要参数;
 * @author linPeiYan
 * @date 2015-06-5
 */
(function dragFun() { //弹窗拖拽--全局方法
    var on = false,
        nowX = 0,
        nowY = 0,
        x = 0,
        y = 0,
        maxWidth = 0,
        maxHeight = 0,
        indexBox;

    $('.TCBox h2').on('mousedown', function(e) {
        on = true;
        indexBox = $(this).parents('.TCBox');
        maxWidth = $(window).width() - indexBox.width();
        maxHeight = $(window).height() - indexBox.height();
        nowX = parseInt(e.pageX - Math.floor($(this).parent().offset().left)); //算出当前标题内鼠标距标签内部左边距离
    })
    $('.TCBox h2 .TCClose').on('mousedown', function(e) { //关闭按钮阻止冒泡
        e.stopPropagation();
    })
    $(window).on('mouseup', function(e) {
        on = false;
    })
    $(window).on('mousemove', function(e) {
        x = e.pageX - nowX;
        y = e.pageY - ($(document).scrollTop() + 15);
        if (on) {
            indexBox.css({
                'top': Math.max(0, Math.min(y, maxHeight)),
                'left': Math.max(0, Math.min(x, maxWidth)),
                'marginLeft': 0
            });
            return false;
        }
    });
}());

/**
 * @desc 输入框默认文字
 * @param obj-表单对象
 * @param text-str-默认显示文本
 * @author linpeiyan
 * @date 2015-09-15
 */
function inputFocusFun(obj, text) {
    obj.css('color', '#999').html(text);
    obj.next('input[type="text"]').on('focus', function() {
        if (obj.html() == text) {
            obj.html('');
        }
    }).on('keydown', function() {
        obj.css('color', '#000');
    }).on('blur', function() {
        if (obj.next('input[type="text"]').val() == '') {
            obj.css('color', '#999').html(text);
        }
    })
};

/**
 * @desc 鼠标经过弹出气泡提示框
 * @param text-obj-节点对象(JQ)
 * @author linpeiyan
 * @date 2015-10-20
 * @例如：tooltip($('td'))   tooltip($('.abc'));
 */
function tooltip(obj) {
    var $tooltipNode = $('<div id="tooltip-y"><div id="tooltip-y-top" class="tooltip-arrows"></div><span></span></div>');
    $('#tooltip-y').remove();
    $('body').append($tooltipNode);
    var $tooltip = $('#tooltip-y');
    $.each(obj, function(index, item) {
        var $item = $(item);
        $item.data('title', $item.attr('title'));
        if ($item.data('title')) {
            $(item).hover(function(e) {
                var objY = $item.offset().top - $(window).scrollTop(),
                    objX = $item.offset().left,
                    nodeHeight = $item.outerHeight(),
                    nodeWidth = $item.outerWidth();
                $tooltip.css('marginTop', nodeHeight).fadeIn(0);
                $tooltipNode.find('span').html('').html($item.data('title'));
                $tooltipNode.css({
                    'top': objY,
                    'left': objX
                });
                var tooltipLeft = $tooltip.position().left + $tooltip.outerWidth(),
                    tooltipTop = $tooltip.offset().top + $tooltip.outerHeight(),
                    bodyWidth = $('body').outerWidth(),
                    bodyHeight = $(window).height();
                if (tooltipLeft > bodyWidth) {
                    var tooltipMarginLeft = tooltipLeft - bodyWidth;
                    $tooltip.css('marginLeft', -tooltipMarginLeft);
                } else {
                    $tooltip.css('marginLeft', 0);
                }
                var mouseX = objX - $tooltip.offset().left + ($item.outerWidth() / 2) - 6;
                $tooltip.find('.tooltip-arrows').stop().animate({
                    'left': mouseX
                }, 200);
                if (tooltipTop > bodyHeight) {
                    var tooltipMarginTop = tooltipTop - bodyHeight;
                    $tooltip.css('marginTop', -$tooltip.outerHeight());
                    $tooltip.find('.tooltip-arrows').attr('id', 'tooltip-y-bottom').css('top', $tooltip.outerHeight() - 2);
                } else {
                    $tooltip.css('marginTop', nodeHeight);
                    $tooltip.find('.tooltip-arrows').attr('id', 'tooltip-y-top');
                    $tooltip.find('.tooltip-arrows').attr('id', 'tooltip-y-top').css('top', -12);
                }
                $item.attr('title', '');
            }, function() {
                $tooltip.fadeOut(0);
            });
        }
    })
    $('body').on('mouseover', '#tooltip-y', function() {
        $tooltip.fadeIn(0);
    });
    $('body').on('mouseout', '#tooltip-y', function() {
        $tooltip.fadeOut(0);
    })
}


/**
 * @desc show loading
 * @param id
 * @author YangLong
 * @return string ''
 */
function loading(id) {
    $("body").append('<div id="loading' + (id ? id : '') + '" class="loading" style="display:none;"></div>');
    $("#loading" + (id ? id : '')).click(function(e) {
        $(this).remove();
    });
    setTimeout(function() {
        $("#loading" + (id ? id : '')).fadeIn("fast");
    }, 200);
    return '';
}

/**
 * @desc hide loading
 * @param id
 * @author YangLong
 * @return void
 */
function removeloading(id) {
    //try{
    $("#loading" + (id ? id : '')).remove();
    //}catch(err){}
}

/*下拉框模拟*/
$('.comboBox li').on('click', function() {
    $(this).parents('.comboBox').find('.defaultOp').text($(this).find('span').text());
});

// 全选/取消全选功能
$("#checkall").click(function(e) {
    if ($(this)[0].checked) {
        $(".ids").each(function(index, element) {
            $(element)[0].checked = true;
        });
    } else {
        $(".ids").each(function(index, element) {
            $(element)[0].checked = false;
        });
    }
});

/**
 * @desc 获取URL参数值，类似php的$_GET
 * @author YangLong
 * @date 2015-02-01
 */
var $_GET = (function() {
    var url = window.document.location.href.toString();
    var u = url.split("?");
    if (typeof(u[1]) == "string") {
        u = u[1].split("&");
        var get = {};
        for (var i in u) {
            var j = u[i].split("=");
            get[j[0]] = j[1];
        }
        return get;
    } else {
        return {};
    }
})();

/**
 * @desc 复选框全选
 * @author liaojianwen
 * @date 2015-03-02
 * @param object obj 事件操作对象（触发者如：全选框）
 * @param object obj1 事件操作对象（被触发者如：明细的复选框）
 */
//【封装】复选框全选-start
function allCheckBox(obj, obj1) {
    obj.click(function(e) {
        if ($(this)[0].checked) {
            obj1.each(function(index, element) {
                $(element)[0].checked = true;
            });
        } else {
            obj1.each(function(index, element) {
                $(element)[0].checked = false;
            });
        }
    });
}

//【封装】获取被选中的复选框当前行的ID封装--start
/**
 * @desc 获取勾选的复选框的id
 * @author liaojianwen
 * @date 2015-03-02
 * @param object obj 被勾选的复选框
 * @return  返回被勾选的复选框的明细的id;例如：1,2,3,4,5
 */
function getAllId(obj) { //参数为被勾选的复选框，即带有checked属性的复选框
    var str_allId = '';
    var $allCheckBox = obj; //例：$('.address-label table tbody tr input[type=checkbox]:checked');
    if ($allCheckBox.length == 0) {
        return '';
    }
    for (var i = 0; i < $allCheckBox.length; i++) {
        str_allId += $allCheckBox.eq(i).attr('data-id') + ','
    }
    var nStr_allId = str_allId.substr(0, str_allId.length - 1);
    return nStr_allId;
}

/**刷新分页导航条
 * @param int page 当前页码
 * @param int pageSize 当前分页大小
 * @param int total 查询总记录数
 * @param function eventHandler 分页导航条的按钮点击时要调用的处理函数
 */
function refreshPaginationNavBar(page, pageSize, total, eventHandler) {
    var $paginationNavBar = $("#paginationNavBar"); // 分页导航条节点
    if (total === undefined || total === 0) {
        $paginationNavBar.empty();
        return;
    }

    //默认每页显示数 TODO???
    if (pageSize > 0) {} else {
        pageSize = 20;
    }

    var pageCount = Math.ceil(total / pageSize); // 总页数
    var currentPage = page; // 当前页码
    var template = '';
    var tt = '';
    for (var i = 1; i <= pageCount; i++) {
        tt += '<option ' + (currentPage == i ? 'selected="selected"' : '') + ' value="' + i + '">' + i + '/' + pageCount + '</option>';
    }
    if (currentPage === 1) {
        // 在第一页，禁用前一页的按钮
        template += '<div class="pageBtnBox"><select name="" id="pageCli" data-pagesize data-page>' + tt + '</select>' + '<span class="preBtn pageBtn notOpBtn"><i class="icon-chevron-left"></i></span>' + '<span ' + (currentPage === pageCount ? 'class="nextBtn pageBtn notOpBtn"' : ' data-page data-pagesize class="nextBtn pageBtn"') + '><i title="' + lang.common.next_page + '" class="icon-chevron-right"><a data-toggle="tooltip" data-page="' + (currentPage + 1) + '" data-pagesize="' + pageSize + '"></a></i></span></div>';
    } else {
        //不在第一页，显示前后一页的按钮
        template += '<div class="pageBtnBox"><select name="" id="" data-pagesize data-page>' + tt + '</select>' + '<span data-page data-pagesize class="preBtn pageBtn"><i title="' + lang.common.previous_page + '" class="icon-chevron-left"><a data-toggle="tooltip" data-page="' + (currentPage - 1) + '" data-pagesize="' + pageSize + '"></a></i></span>' + '<span ' + (currentPage === pageCount ? 'class="nextBtn pageBtn notOpBtn"' : ' data-page data-pagesize class="nextBtn pageBtn"') + '><i title="' + lang.common.next_page + '" class="icon-chevron-right"><a data-toggle="tooltip" data-page="' + (currentPage + 1) + '" data-pagesize="' + pageSize + '"></a></i></span></div>';
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
        var selectedPage = numberClean($(this).children("option:selected").val());
        if (eventHandler != undefined && (typeof eventHandler == "function")) {
            eventHandler({
                'page': selectedPage,
                'pageSize': pageSize
            });
        }
    });
} /*<-- 刷新分页导航条*/


//js过滤用户提交的数字数据
function numberClean(source, defaultValue) {
    if (typeof(defaultValue) === 'undefined') {
        // 默认值
        defaultValue = 0;
    }
    var result;
    if (source && source != undefined) {
        if (isNaN(source)) {
            result = source.replace(/[\D]/g, '');
        } else {
            result = source;
        }
    } else {
        result = defaultValue;
    }
    return result;
}

/**
 * @desc 将时间戳转为本地日期
 * @author YangLong,liaojianwen
 * @param i 时间戳
 * @param n 返回值类型 1 日期 2 事件 其他 日期+事件
 * @date 2015-03-13
 */
function intToLocalDate(i, n) {
    var i = parseInt(i) * 1000;
    var d = new Date(i);
    var date = [];
    date['date'] = d.getFullYear() + '-' + ('0' + (d.getMonth() + 1)).substr(-2, 2) + '-' + ('0' + d.getDate()).substr(-2, 2);
    date['md'] = ('0' + (d.getMonth() + 1)).substr(-2, 2) + '-' + ('0' + d.getDate()).substr(-2, 2);
    date['time'] = d.getHours() + ':' + d.getMinutes() + ':' + d.getSeconds();
    date['ftime'] = ('0' + d.getHours()).substr(-2, 2) + ':' + ('0' + d.getMinutes()).substr(-2, 2) + ':' + ('0' + d.getSeconds()).substr(-2, 2);
    date['clock'] = ('0' + d.getHours()).substr(-2, 2) + ':' + ('0' + d.getMinutes()).substr(-2, 2);
    switch (n) {
        case 1:
            return date['date'];
            break;
        case 2:
            return date['time'];
            break;
        case 3:
            return date['date'] + '&nbsp;&nbsp;' + date['clock'];
            break;
        case 4:
            return date['date'] + '<br />' + date['time'];
            break;
        case 5:
            return date['date'] + ' ' + date['ftime'];
            break;
        case 6:
            return date['date'] + '<br />' + date['ftime'];
            break;
        case 7:
            return date['date'] + ' ' + date['clock'];
            break;
        case 8:
            return date['date'] + '<br />' + date['clock'];
            break;
        case 9:
            var _nd = new Date();
            if ((_nd.getTime() - d.getTime()) / 1000 < 3600 * 24 * 250) {
                return date['md'];
            } else {
                return date['date'];
            }
            break;
        default:
            return date['date'] + ' ' + date['time'];
            break;
    }
}

/**
 * @desc 记住URL
 * @author YangLong
 * @date 2015-03-19
 */
try {
    if (window.top.location != window.self.location) {
        setCookie('remember_url', window.location.href);
    }
} catch (ex) {}

/**
 * @desc 设置cookies
 * @author YangLong
 * @date 2015-03-19
 */
function setCookie(c_name, value, expiredays) {
    var exdate = new Date();
    exdate.setDate(exdate.getDate() + expiredays);
    document.cookie = c_name + "=" + escape(value) + ((expiredays == null) ? "" : ";expires=" + exdate.toGMTString());
}

/**
 * @desc 获取cookies
 * @author YangLong
 * @date 2015-03-19
 */
function getCookie(c_name) {
    if (document.cookie.length > 0) {
        var c_start, c_end;
        c_start = document.cookie.indexOf(c_name + "=");
        if (c_start != -1) {
            c_start = c_start + c_name.length + 1;
            c_end = document.cookie.indexOf(";", c_start);
            if (c_end == -1) {
                c_end = document.cookie.length;
            }
            return unescape(document.cookie.substring(c_start, c_end));
        }
    }
    return "";
}

/**
 * @desc 时间格式转为时间戳
 * @author liaojianwen
 * @date 2015-04-20
 * @param dateStr
 * @return
 */
function get_unix_time(dateStr) {
    var newstr = dateStr.replace(/-/g, '/');
    var date = new Date(newstr);
    var time_str = date.getTime().toString();
    return time_str.substr(0, 10);
}

/**
 * @desc HTML解码，非安全
 * @param strEncodeHTML 需要解码的文档
 * @author YangLong
 * @date 2015-06-19
 * @return string 解码后的innerText
 */
function HTMLDecode(strEncodeHTML) {
    var div = document.createElement('div');
    div.innerHTML = strEncodeHTML;
    return div.innerText;
}

/**
 * @desc 首字母大写
 * @param str 需要转换的字符串
 * @author liaojianwen
 * @date 2015-08-10
 * @returns {String}
 */
function ucfirst(str) {
    var str = str.toLowerCase();
    while (str.indexOf("_") != -1) {
        str = str.replace("_", " ");
    }
    var result = str.substring(0, 1).toUpperCase() + str.substring(1);
    return result;
}

/**
 * @desc 将ebay国家转为时区
 * @author YangLong
 * @date 2015-10-22
 * @return string 时区字符串
 */
function country2timezone(country) {
    var timezone = [];
    switch (country) {
        case 'PT':
            // +0
            timezone[0] = 'GMT';
            timezone[1] = 'GMT';
            break;
        case 'BE':
        case 'HR':
        case 'DK':
        case 'SE':
        case 'IE':
        case 'NO':
        case 'MT':
        case 'SI':
        case 'DE':
        case 'GB':
            // +1
            timezone[0] = 'Europe/London';
            timezone[1] = '伦敦';
            break;
        case 'IL':
            // +2/+3
            timezone[0] = 'Asia/Jerusalem';
            timezone[1] = '以色列';
            break;
        case 'CY':
            // +2
            timezone[0] = 'ART';
            timezone[1] = 'ART/东欧时间';
            break;
        case 'FI':
            // +2/+3
            timezone[0] = 'Europe/Helsinki';
            timezone[1] = '芬兰';
            break;
        case 'RU':
            // +3 ??????????????
            timezone[0] = 'Africa/Addis_Ababa';
            timezone[1] = '+3';
            break;
        case 'PR':
        case 'CA':
        case 'US':
            // -5
            timezone[0] = 'America/New_York';
            timezone[1] = '纽约';
            break;
        case 'AU':
            // -5
            timezone[0] = 'Australia/Sydney';
            timezone[1] = '悉尼';
            break;
        default:
            timezone[0] = '';
            timezone[1] = '';
            break;
    }
    return timezone;
}

/**
 * @desc HTML编码
 * @param string unsafe string
 * @author YangLong
 * @date 2015-10-26
 * @return string safe string
 */
function htmlEntities(str) {
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

// 禁止按钮点击时文本被选中的效果
$('.subBtn').on('click', '', function() {
    window.getSelection ? window.getSelection().removeAllRanges() : document.selection.empty();
});