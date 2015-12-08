"use strict";
/**
 * @desc 邮件详情区动态高度
 * @author linpeiyan
 * @date 2015-02-16
 */
function defaultHeight() {
	var winHeight = $(window).height();
	var deHeight = parseInt(winHeight) - 30;
	var emailShowHeight = deHeight - 214 - $('.leftTop').height();
	$('.emailDetails .left,.emailDetails .right').css({
		height: deHeight
	});
	$('.emailDetails .leftShow').css({
		height: emailShowHeight
	});
	$('.emailDetails .left').css({
		width: $('body').width() - 655
	})
}
defaultHeight();
$(window).resize(function() {
	defaultHeight();
});

//邮件详情上传图片展示区隐藏/展开
$('.imgShow .rightBtn').on('click', function() {
	var $this = $(this);
	var boxWidth = $('.imgShow').width() + 12;
	if ($this.attr('on') == 'true') {
		imgShowAn(-boxWidth, 'false', '展开');
	} else {
		imgShowAn(-4, 'true', '隐藏');
	}
})

function imgShowAn(rightNum, onV, tit) {
	$('.imgShow').animate({
		right: rightNum
	}, 300, function() {
		$('.imgShow .rightBtn').attr('on', onV);
		$('.imgShow .rightBtn').html(tit);
	})
}
//信息源更多客户信息弹窗选项卡-林培雁-2015-6-12
$('.cusInfoTC h2 li').on('click', function(e) {
	var index = $(this).index('.cusInfoTC h2 li');
	$('.cusInfoTC h2 li').removeClass('active');
	$(this).addClass('active');
	$('.cusInfoTC .cusBox').hide();
	$('.cusInfoTC .cusBox').eq(index).show();
})