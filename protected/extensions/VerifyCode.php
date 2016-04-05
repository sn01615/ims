<?php

/**
 * @desc 验证码类
 * @author liaojianwen
 * @date 2015-03-06
 */
class VerifyCode
{

    /**
     * @desc 生成验证码图片
     * @author liaojianwen
     * @date 2015-03-05
     * @modify 2015-03-12 YangLong 方法静态化
     */
    public static function getCode($num = 4, $size = 20, $width = 0, $height = 0)
    {
        ! $width && $width = $num * $size * 4 / 5 + 26;
        ! $height && $height = $size + 20;
        // 去掉了 0 1 O l 等
        $str = "23456789abcdefghijkmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVW";
        $code = '';
        for ($i = 0; $i < $num; $i ++) {
            $code .= $str[mt_rand(0, strlen($str) - 1)];
        }
        // 画图像
        $im = imagecreatetruecolor($width, $height);
        // 定义要用到的颜色
        $back_color = imagecolorallocate($im, 235, 236, 237);
        $boer_color = imagecolorallocate($im, 204, 204, 204);
        $text_color = imagecolorallocate($im, mt_rand(0, 200), mt_rand(0, 120), mt_rand(0, 120));
        // 画背景
        imagefilledrectangle($im, 0, 0, $width, $height, $back_color);
        // 画边框
        imagerectangle($im, 0, 0, $width - 1, $height - 1, $boer_color);
        // 画干扰线
        for ($i = 0; $i < 5; $i ++) {
            $font_color = imagecolorallocate($im, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
            imagearc($im, mt_rand(- $width, $width), mt_rand(- $height, $height), mt_rand(30, $width * 2), mt_rand(20, $height * 2), mt_rand(0, 360), mt_rand(0, 360), $font_color);
        }
        // 画干扰点
        for ($i = 0; $i < 50; $i ++) {
            $font_color = imagecolorallocate($im, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
            imagesetpixel($im, mt_rand(0, $width), mt_rand(0, $height), $font_color);
        }
        
        // 画验证码
        imagefttext($im, $size, 5, 12, $size + 12, $text_color, './public/template/font/BELL.TTF', $code);
        Yii::app()->session['VerifyCode'] = $code;
        header('Content-type: image/png');
        imagepng($im);
        imagedestroy($im);
    }
}