"use strict";

//登录页动画效果--linpeiyan--2015-9-24
$(function() {
    $(window).scroll(function() {
        var winScrollTop = $(window).scrollTop();
        if (winScrollTop > 600) {
            $('#back-to-top').fadeIn(300);
            $('.bottomHIcon').attr('class', 'bottomHIcon active');
            setTimeout(function() {
                $('.bottomHIcon.active').css('opacity', '1');
            }, 2000)
        } else {
            $('#back-to-top').fadeOut(300);
            $('.bottomHIcon.active').css('opacity', '0');
            $('.bottomHIcon').removeClass('active');
        }
    });
});

/**
 * @desc 登录页面js
 * @author liaojianwen
 * @date 2015-03-02
 */
// 判断登录页是否处于框架内，在则跳出框架@YangLong
if (window.top.location != window.self.location) {
    window.top.location = window.self.location;
}
$(document).ready(function(e) {
    var $username = $("input[name=username]");
    var $pass = $("input[name=password]");
    var $code = $("#verifycode");
    var strCookie = document.cookie;
    //将多cookie切割为多个名/值对 
    var arrCookie = strCookie.split("; ");
    var userId, checked;
    //遍历cookie数组，处理每个cookie对 
    for (var i = 0; i < arrCookie.length; i++) {
        var arr = arrCookie[i].split("=");
        //找到名称为userId的cookie，并返回它的值 
        if ("checked" == arr[0]) {
            checked = arr[1];
        } else if ("username" == arr[0]) {
            userId = arr[1];
            break;
        }
    }
    if (checked == 'true') {
        $username.val(userId);
        $("#remindName").attr('checked', true);
    } else {
        //获取当前时间 
        var date = new Date();
        //将date设置为过去的时间 
        date.setTime(date.getTime() - 10000);
        //将userId这个cookie删除 
        document.cookie = "expires=" + date.toGMTString();
        $("#remindName").attr('checked', false);
    }
    //登录绑定enter键
    document.onkeydown = function(event) {
        if ((event.keyCode || event.which) == 13) {
            check_login();
        }
    }
    $("#loginIn").on('click', function() {
        check_login();
    });

    /**
     * @desc 登录验证
     * @author liaojianwen
     * @date 2015-03-16
     */
    function check_login() {
        var $remind = $("#remindName")[0].checked
            //获取当前时间 
        var date = new Date();
        var expiresDays = 10;
        //将date设置为10天以后的时间 
        date.setTime(date.getTime() + expiresDays * 24 * 3600 * 1000);
        //将userId和userName两个cookie设置为10天后过期
        document.cookie = "checked=" + $remind;
        document.cookie = "username=" + $username.val();
        document.cookie = "expires=" + date.toGMTString();
        $(".hintP").hide();
        if (!$username.val()) {
            $(".hintP").show();
            $(".hintP").text(lang.login_biz.amount_cannot_empty);
            $code.val('');
        } else if (!$pass.val()) {
            $(".hintP").show();
            $(".hintP").text(lang.login_biz.password_cannot_empty);
            $code.val('');
        } else if (!$code.val()) {
            $(".hintP").show();
            $(".hintP").text(lang.login_biz.ver_code_cannot_empty)
        } else if ($pass.val().length < 6 || $pass.val().length > 20) {
            $(".hintP").show();
            $(".hintP").text(lang.login_biz.password_length_tip)
        } else {
            if ($("#user").val() == 'guest') {
                var urlStr = "demo/?r=Api/login";
            } else {
                var urlStr = "?r=Api/login";
            }
            var param = {
                'username': $username.val(),
                'password': $pass.val(),
                'code': $code.val()
            };
            $.ajax({
                url: urlStr,
                data: param,
                type: 'post',
                success: function(data, status) {
                    if (status != 'success') {
                        $(".hintP").show();
                        $(".hintP").text(lang.ajaxinfo.network_error);
                        $code.val('');
                        create_code();
                    }
                    if (data.Ack == 'Success') {
                        if ($("#user").val() == 'guest') {
                            window.location = "demo/?r=Home";
                        } else {
                            window.location = "?r=Home";
                        }
                    } else if (data.Ack == 'NameFail') {
                        $(".hintP").show();
                        $(".hintP").text(lang.login_biz.amount_error);
                        $code.val('');
                        create_code();
                    } else if (data.Ack == 'PwdFail') {
                        $(".hintP").show();
                        $(".hintP").text(lang.login_biz.password_error);
                        $code.val('');
                        create_code();
                    } else if (data.Ack == 'CodeFail') {
                        $(".hintP").show();
                        $(".hintP").text(lang.login_biz.vcode_error);
                        $code.val('');
                        create_code();
                    }
                }
            });
        }
    }
    create_code();

    //更换下一张验证码
    $("#next,#code").on('click', function() {
        create_code();
    });

    /**
     * @desc 刷新验证码
     * @author liaojianwen
     * @date 2015-03-05
     */
    function create_code() {
        $('#code').attr('src', '?r=Api/getCode&' + Math.random() * 10000);
    }

    // 防止循环重定向
    setCookie('remember_url', '');

    $('#register').on('click', function() {
        window.location = "?r=home/Register";
    })

    $('#backToTop').on('click', function() {
        window.location = "#top";
    })
});