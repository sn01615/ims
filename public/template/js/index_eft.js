"use strict";
/**
 * @desc 消息列表js
 * 1、导航栏展开效果
 * 2、模拟下拉框
 * 3、动态高度
 * 4、全屏展开
 * 5、刷新时自动展开对应导航  2015-6-5
 * @author linpeiyan
 * @date 2015-02-10
 */
$(document).ready(function() {
	/*侧边栏菜单展开/收缩 --start--------------------------------------------*/
	$('.sidebar h2,.sidebar h3').attr('sideon', 'true'); //为每个菜单增加初始属性判断值
	$('.sidebar h2:first').attr('sideon','false');
	$('.sidebar h2:first i').addClass('active');
	$('.sidebar h2:first').next().show();
	$('.sidebar h2,.sidebar h3').on('click', function() {
		var $this = $(this);
		var sideon = $(this).attr('sideon'); //获取当前菜单的属性判断值
		if (sideon == 'true') {
			$this.next('ul').slideDown(200, function() { //展开菜单
				$this.attr('sideon', 'false'); //改变属性判断值为false
			});
			$this.find('.icon-right').addClass('active'); //箭头向下
		} else if (sideon == 'false') {
			$this.next('ul').slideUp(200, function() { //收缩菜单
				$this.attr('sideon', 'true'); //改变属性判断值为true
			});
			$this.find('.icon-right').removeClass('active'); //箭头向右
		}
	});
	//点击子菜单时显示背景色
	$('.sidebar').on('click', 'h3', function() {
		$('.sidebar h3').removeClass('action2F');
		$(this).addClass('action2F');
	});
	/*侧边栏菜单展开/收缩 --end----------------------------------------------*/
	/*刷新时侧边栏展开对应的导航--start------------------------------------------------*/
	window.autoUnfold = function(src) {
			$('.sidebar h2,.sidebar h3').attr('sideon', 'true');
			$('.sidebar h2:first i').removeClass('active');
			$('.sidebar h2').next('ul').hide();
			var ifrmaeUrl = src.split('?')[1];
			function autoSlideDownFun(obj1) {
				$(obj1).each(function(index, item) {
					if ($(item).attr('href') != 'javascript:;') {
						var nowUrl = $(item).attr('href').split('?')[1];
						if (nowUrl === ifrmaeUrl) {
							if (obj1 == '.sidebar .sideF3a') {
								$(item).parents('ul').prev().find('.icon-right').addClass('active');
								$(item).parent().addClass('action2F').parents('ul').css('display', 'block');
								$(item).parents('ul').prev().attr('sideon', 'false');
								if ($(item).parents('ul').prev().find('.icon-right').hasClass('active')) {
									clearInterval(timeSide);
								}
							} else if (obj1 == '.sidebar a') {
								$(item).parents('ul').siblings('h2').find('.icon-right').addClass('active');
								$(item).parent().addClass('action2F').parents('ul').css('display', 'block');
								$(item).parents('ul').siblings('h2').attr('sideon', 'false');
							}
						}
					}
				});
			}
			autoSlideDownFun('.sidebar .sideF3a');
			autoSlideDownFun('.sidebar a');
			var timeSide = setInterval(function() { //定时器，解决无法找到动态数据对象
				autoSlideDownFun('.sidebar .sideF3a');
			}, 200);

		}
		/*刷新时侧边栏展开对应的导航--end------------------------------------------------*/

	//模拟下拉框
	$('.comboBox').hover(function() {
			$(this).find('.selList').show();
		}, function() {
			$(this).find('.selList').hide();
		})
		//初始样式
		//首页侧边栏/主要内容动态高度
	defaultHeight();
	$(window).resize(function() {
		defaultHeight();
	});

	function defaultHeight() {
		var winHeight = $(window).height();
		var deHeight = parseInt(winHeight) - 45;
		var emailShowHeight = deHeight - 284;
		$('.wrap .sidebar,.wrap .main iframe,.emailDetails .left,.emailDetails .rightBox').css({
			height: deHeight
		});
		$('.emailDetails .leftShow').css({
			height: emailShowHeight
		})
	}
	/*全屏显示/收起全屏  start--------------------------------*/
	var mainReSize = true;
	$('.reFull').on('click', function() {
			if (mainReSize) {
				$('.sidebar').animate({
					marginLeft: -180
				}, 300);
				$('.main').animate({
					marginLeft: 0
				}, 300, function() {
					mainReSize = false;
				});
				$('.reFull').addClass('active');
			} else {
				$('.sidebar').animate({
					marginLeft: 0
				}, 300);
				$('.main').animate({
					marginLeft: 180
				}, 300, function() {
					mainReSize = true;
				});
				$('.reFull').removeClass('active');
			}
		});
		/*全屏显示/收起全屏  end--------------------------------*/
/**
 * @desc 登录后显示用户名
 * @author liaojianwen
 * @modify YangLong 2015-09-24 新用户显示引导页，换用匿名函数
 * @date 2015-03-04
 */
(function(){
	$.get("?r=api/GetUserName", function(data, status) {
		if (data.Ack == "Success") {
			$("#username").text(data.Body.userName);
			if(data.Body.showHelp>0){
				(function(){
					$('body').append('<div class="guide"><img src="public/template/img/yingdao.jpg")"><div class="guideClose" title="点击退出引导"></div></div>');
					$('.guideClose').on('click',function(){
						$(".guide").fadeOut(300);
					})
				}())
			}
		}
	});
})();
	//注销
	$("#logout").on('click', function() {
		$.get("?r=api/logout", function(data, status) {
			if (status == "success") {
				window.location = "?/r=Home/Login";
			}
		})
	});
	$(".sidebar ul li ul li a[target='main']").click(function(e) {
		window.back_url = $(this).attr('href');
	});
	// 添加标签-下拉框效果
	$('.addTagTC .tagColorList .iconTab').on('click', function() {
		var thisClass = $(this).attr('class');
		$('.addTagTC .defaultBox .iconTab').attr('class', thisClass);
	})

})
