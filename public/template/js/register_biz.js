"use strict";
/**
 * @desc 1.验证输入的用户信息
 * 2.验证码显示
 * 3.注册提交
 * @author heguangquan
 * @date 2015-03-03
 */
$(document).ready(function(e) {
    create_code();
    var $oName = $("#username");
    var $oPassword1 = $("#password");
    var $oPassword2 = $("#conpassword");
    var $oRealName = $("#realname");
    var $oPhone = $("#phone");
    var $oEmail = $("#email");
    var msg = '';
    $(".mainBodyCon input[type=text]").val('');
    //检测用户名是否存在
    $oName.blur(function() {
        var userName = $(this).val();
        var $this = $(this);
        $this.next('span').html('');
        $this.removeClass("redBorder");
        $this.next('span').removeClass("success");
        $this.next('span').addClass("spanHint");
        $this.next('span').removeClass("redFont");
        $this.parent("p").attr("data-state", 0);
        if (userName !== '') {
            $.get('?r=api/CheckUser', {
                'username': userName
            }, function(result, state) {
                if (result.Ack == 'Success') {
                    $this.next('span').addClass("success");
                    $this.parent("p").attr("data-state", 1);
                } else {
                    $this.next('span').removeClass("spanHint");
                    $this.next('span').addClass('redFont');
                    $this.next('span').html(lang.register_biz.user_name_tit);
                }
            });
        } else {
            $this.next('span').addClass("redFont");
            $this.next('span').html(lang.register_biz.inp_login_acc);
        }
    });
    $oName.on('change', function() {
        var sNameVal = $.trim($oName.val());
        var rNotNum = /[^\w]+/g;
        sNameVal = sNameVal.replace(rNotNum, '');
        $oName.val(sNameVal);
    });

    $("input[type=text]").focus(function() {
        var id = $(this).attr('id');
        $(this).next('span').addClass("spanHint");
        $(this).next('span').removeClass('redFont');
        $(this).next('span').removeClass("success");
        $(this).removeClass("redBorder");
        if (id === 'username') {
            msg = lang.register_biz.inp_login_acc;
        } else if (id === 'realname') {
            msg = lang.register_biz.inp_name;
        } else if (id === 'phone') {
            msg = lang.register_biz.inp_phone_num;
        } else if (id === 'email') {
            msg = lang.register_biz.inp_email;
        } else if (id === 'verify') {
            $('.verifyerr').removeClass("redFont");
            $('.verifyerr').html(lang.register_biz.inp_verify)
        } else {}
        $(this).next('span').html(msg);
    })

    $("input[type=password]").focus(function() {
        var id = $(this).attr('id');
        $(this).next('span').addClass("spanHint");
        $(this).next('span').removeClass('redFont');
        $(this).next('span').removeClass("success");
        $(this).removeClass("redBorder");
        if (id === "password") {
            msg = lang.register_biz.pass_len_tit;
        } else {
            msg = lang.register_biz.inp_pass_again;
        }
        $(this).next('span').html(msg);
    });

    //检测密码
    $oPassword1.blur(function() {
        var passWord = $oPassword1.val().replace(/\s/g, '');
        var conPassWord = $oPassword2.val();
        var $this = $(this);
        $this.next('span').html('');
        $this.removeClass("redBorder");
        $this.next('span').removeClass("success");
        $this.next('span').addClass("spanHint");
        $this.next('span').addClass('redFont');
        $this.parent("p").attr("data-state", 0);
        if (passWord.length == 0) {
            $oPassword2.parent("p").attr("data-state", 0);
            $this.next('span').html(lang.register_biz.inp_pass_space);
        } else if (passWord.length < 6 || passWord.length > 20) {
            $this.next('span').removeClass("spanHint");
            $this.next('span').html(lang.register_biz.pass_len_tit);
            $oPassword2.parent("p").attr("data-state", 0);
        } else {
            $this.removeClass("redBorder");
            $this.next('span').removeClass("redFont");
            $this.next('span').addClass("success");
            $this.next('span').html('');
            $this.parent("p").attr("data-state", 1);
        }

        if (passWord !== conPassWord && conPassWord !== '') {
            $oPassword2.next('span').removeClass("spanHint");
            $oPassword2.next('span').addClass('redFont');
            $oPassword2.next('span').html(lang.register_biz.pass_unlike);
            $oPassword2.next('span').removeClass('success');
            $oPassword2.parent("p").attr("data-state", 0);
        }
    }).on('change', function() {
        $oPassword1.val($oPassword1.val().replace(/\s/g, ''));
    });
    //确认密码
    $oPassword2.blur(function() {
        var conPassWord = $oPassword2.val().replace(/\s/g, '');
        var passWord = $oPassword1.val();
        var $this = $(this);
        $this.next('span').html('');
        $this.removeClass("redBorder");
        $this.next('span').removeClass("success");
        $this.next('span').addClass("spanHint");
        $this.next('span').removeClass("redFont");
        $this.parent("p").attr("data-state", 0);
        if (passWord.length == 0) {
            $this.next('span').removeClass("spanHint");
            $this.next('span').addClass("redFont");
            $this.next('span').html(lang.register_biz.inp_pass);
        } else if (!conPassWord) {
            $this.next('span').removeClass("spanHint");
            $this.next('span').addClass('redFont');
            $this.next('span').html(lang.register_biz.inp_pass_space);
        } else if (conPassWord.length < 6 || conPassWord.length > 20) {
            $this.next('span').removeClass("spanHint");
            $this.next('span').addClass('redFont');
            $this.next('span').html(lang.register_biz.pass_len_tit);
        } else if (conPassWord !== passWord) {
            $this.next('span').removeClass("spanHint");
            $this.next('span').addClass('redFont');
            $this.next('span').html(lang.register_biz.pass_unlike);
        } else {
            $this.removeClass("redBorder");
            $this.next('span').addClass("success");
            $this.parent("p").attr("data-state", 1);
        }
    }).on('change', function() {
        $oPassword2.val($oPassword2.val().replace(/\s/g, ''));
    });

    //检测姓名格式
    $oRealName.blur(function() {
        var realName = $.trim($oRealName.val());
        var $this = $(this);
        $this.next('span').html('');
        $this.removeClass("redBorder");
        $this.next('span').removeClass("success");
        $this.next('span').addClass("spanHint");
        $this.next('span').removeClass("redFont");
        $this.parent("p").attr("data-state", 0);
        if (realName === '') {
            $this.next('span').removeClass("spanHint");
            $this.next('span').addClass("redFont");
            $this.next('span').html(lang.register_biz.inp_name);
        } else {
            $this.removeClass("redBorder");
            $this.next('span').addClass("success");
            $this.parent("p").attr("data-state", 1);
        }

    }).on('change', function() {
        $oRealName.val($.trim($oRealName.val()));
    });

    //检测电话格式
    $oPhone.blur(function() {
        var phone = $(this).val();
        var phoneFilter = /^(\(\d{3,8}\)|\d{3,8}-)?\d{3,15}$/;
        var $this = $(this);
        $this.next('span').html('');
        $this.removeClass("redBorder");
        $this.next('span').removeClass("success");
        $this.next('span').addClass("spanHint");
        $this.next('span').addClass('redFont');
        $this.parent("p").attr("data-state", 0);
        if (phone === '') {
            $this.next('span').html(lang.register_biz.inp_phone_num);
        } else if (!phoneFilter.test(phone)) {
            $this.next('span').removeClass("spanHint");
            $this.next('span').html(lang.register_biz.phone_format_error);
        } else {
            $this.removeClass("redBorder");
            $this.next('span').removeClass("redFont");
            $this.next('span').addClass("success");
            $this.parent("p").attr("data-state", 1);
        }

    }).on('change', function() {
        $oPhone.val($oPhone.val().replace(/\s/g, ''));
    });

    //检测电子邮箱格式
    $oEmail.blur(function() {
        var email = $(this).val();
        var emailFilter = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
        var $this = $(this);
        $this.next('span').html('');
        $this.removeClass("redBorder");
        $this.next('span').removeClass("success");
        $this.next('span').addClass("spanHint");
        $this.next('span').addClass('redFont');
        $this.parent("p").attr("data-state", 0);
        if (email === '') {
            $this.next('.spanHint').html(lang.register_biz.inp_email);
        } else if (!emailFilter.test(email)) {
            $this.next('span').removeClass("spanHint");
            $this.next('span').html(lang.register_biz.email_format_error);
        } else {
            $this.removeClass("redBorder");
            $this.next('span').removeClass("redFont");
            $this.next('span').addClass("success");
            $this.parent("p").attr("data-state", 1);
        }

    }).on('change', function() {
        $oEmail.val($oEmail.val().replace(/\s/g, ''));
    });
    $(':checkbox:checked').parents('tr').remove();

    //验证码
    $("#verify").blur(function() {
        var $this = $(this);
        var verifyCode = $this.val();
        $this.removeClass("redBorder");
        $(".verifyerr").html('');
        $('.verifyerr').removeClass("redFont");
        if (verifyCode === '') {
            $('.verifyerr').addClass("redFont");
            $(".verifyerr").html(lang.register_biz.inp_verify);
        }
    }).on('change', function() {
        $("#verify").val($(this).val().replace(/\s/g, ''));
    });;

    var $registration = []; //用于记录用户是否注册成功
    //用户注册
    var stime;
    $("#register").on('click', function() {
        var userName = $oName.val();
        var passWord = $oPassword1.val();
        var conPassWord = $oPassword2.val();
        var realName = $oRealName.val();
        var telePhone = $oPhone.val();
        var email = $oEmail.val();
        var verifyCode = $("#verify").val();
        var i = 1;
        var TitArr = [lang.register_biz.inp_login_acc, lang.register_biz.inp_pass, lang.register_biz.inp_pass, lang.register_biz.inp_name, lang.register_biz
            .inp_phone_num, lang.register_biz.email, lang.register_biz.inp_verify
        ];
        $('#inputS').find('input[type="text"],input[type="password"]').each(function(index, it) {
            var $item = $(it);
            if ($.trim($item.val()) === '') {
                $item.addClass("redBorder");
                $item.siblings('.spanHint').addClass('redFont');
                $item.siblings('.spanHint').html(TitArr[index]);
                if ($item.attr('id') == 'email') {
                    $('#email').next('span').removeClass("spanHint");
                    $('#email').next('span').addClass("redFont").html(lang.register_biz.email_format_error);
                }
                i = 0;
                return false;
            } else {
                $item.removeClass("redBorder");
                $item.siblings('.spanHint').removeClass('redFont');
            }
            switch ($item.attr('id')) {
                case 'username':
                    break;
                case 'password':
                    if (passWord.length < 6 || passWord.length > 20) {
                        i = 0;
                        return false;
                    };
                    break;
                case 'conpassword':
                    if ($("#password").val() != $("#conpassword").val()) {
                        $("#conpassword").siblings('.spanHint').addClass("redFont").html(lang.register_biz.pass_unlike);
                        i = 0;
                        return false;
                    };
                    break;
                case 'realname':
                case 'phone':
                    var phoneFilter = /^(\(\d{3,8}\)|\d{3,8}-)?\d{3,15}$/;
                    if (!phoneFilter.test(telePhone)) {
                        $('#phone').next('span').removeClass("spanHint");
                        $('#phone').next('span').addClass("redFont").html(lang.register_biz.phone_format_error);
                        i = 0;
                        return false;
                    };
                    break;
                case 'email':
                    var emailFilter = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
                    if (!emailFilter.test(email)) {
                        $('#email').next('span').removeClass("spanHint");
                        $('#email').next('span').addClass("redFont").html(lang.register_biz.email_format_error);
                    };
                    break;
                case 'verify':
                    break;
            }
        });

        $(".registerSuc").attr("display", "none");
        $(".mainBodyCon").attr("display", "block");
        if (i == 1) {
            $.get("?r=api/RegisterUser", {
                "username": userName,
                "realname": realName,
                "password": passWord,
                "email": email,
                "telephone": telePhone,
                'verifycode': verifyCode
            }, function(result) {
                if (result.Ack === 'Success') {
                    $registration['status'] = 'Success';
                    $registration['userName'] = userName;
                    $registration['passWord'] = passWord;
                    $registration['isverify'] = 1;
                    $(".registerSuc").css("display", "block");
                    $(".mainBodyCon").css("display", "none");
                    var s = 5;
                    stime = setInterval(function() {
                        if (s == 0) {
                            auto_login(userName, passWord, 1);
                            clearInterval(stime);
                        }
                        $(".registerSuc b").html(s--);
                    }, 1000);
                } else {
                    $('.verifyerr').addClass("redFont");
                    if (result.Error == 'verifyCode_err') {
                        $(".verifyerr").html(lang.register_biz.verify_error);
                    }
                    if (result.Error == 'You do not have permission to add users') {
                        $(".verifyerr").html(lang.register_biz.first_please + '<a href="javasctipt" id="ajax_logout">' + lang.register_biz.login_out +
                            '</a>');
                        $("#ajax_logout").click(function(e) {
                            $.get('?r=api/logout', function() {
                                alert(lang.register_biz.login_out_access);
                            });
                        });
                    }
                    if (result.Error == 'user_exists') {
                        alert(lang.register_biz.user_name_exist);
                    }
                    $("#verify").val('');
                    create_code();
                }
            });
        }
    });

    //登录到页面
    $("#login").on('click', function() {
        clearInterval(stime);
        if ($registration['status'] === 'Success') {
            auto_login($registration['userName'], $registration['passWord'], $registration['isverify']);
        }
    });

    /**
     * @desc 登录到页面
     * @author liaojianwen
     * @date 2015-03-20
     */
    function auto_login(userName, passWord, isverify) {
        $.post("?r=api/Login", {
            "username": userName,
            "password": passWord,
            "isverify": isverify
        }, function(result) {
            if (result.Ack == "Success") {
                location.href = "./";
            }
        });
    }

    //更换下一张验证码
    $("#next,#verifyCore").on('click', function() {
        create_code();
    });

    /**
     * @desc 刷新验证码
     * @author heguangquan
     * @date 2015-03-06
     */
    function create_code() {
        $('#verifyCore').attr('src', '?r=Api/getCode&' + Math.random() * 10000);
    }
})