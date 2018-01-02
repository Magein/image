<?php

error_reporting(7);

spl_autoload_register(function ($class) {
    $filename = './library/' . end(explode('\\', $class)) . '.php';
    require_once $filename;
});

/**
 *
 * 微信头像：https://wx.qlogo.cn/mmopen/vi_32/DYAIOgq83er8oAjzMKR34dHHICzJ0bzSRibp1lb9J1ynVM9ckibkUMTZe8Jco9Kou0LYpKUxTCyqclpSIL18NVaA/0
 *
 * 远程连接可以直接使用 imagecreatefromjpeg 得到 resource 类型的值 imagecreatefrompng获取不到,想要获取链接中输出的图片类型
 *
 * 可以使用curl请求，然后打印出头信息 查看 Content-Type: xxx的值
 *   打印头信息：curl_setopt($ch, CURLOPT_HEADER, false);
 *
 * 如果使用imagecreatefromstring 需要得到 图片字符串才能转化
 *  https://  可以使用curl去获取（https需要证书验证，使用file_get_contents报错的话，可以直接用curl跳过证书验证）
 *  http://   可以用file_get_contents获取图片字符串
 *
 * 请求微信二维码返回的字符串类型需要用 imagecreatefromstring 去转化成 resource类型的值
 *
 *
 */

use Magein\image\library\Image;
use \Magein\image\library\Resources;

$fontFile = './static/font/msyhbd.ttc';
$fontFile = './static/font/msyh.ttc';
$string = 'magein';
$size = 30;
$canvas = Resources::instance()->createText($string, $size, $fontFile, [220, 220, 255]);

//var_dump($canvas);
//die();

$head = 'https://wx.qlogo.cn/mmopen/vi_32/DYAIOgq83er8oAjzMKR34dHHICzJ0bzSRibp1lb9J1ynVM9ckibkUMTZe8Jco9Kou0LYpKUxTCyqclpSIL18NVaA/0';
$head = './static/image/timg.jpg';
$image = new Image($head);
//$image->save('./test.jpg', 'jpg', 10);
//$image->cut(0, 0, 300, 300);
//$image->thumb();
//$image->circle();
//$image->water('./static/image/magein.png', 500, 600);
//$image->water($canvas);
$image->output();