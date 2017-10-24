<?php

error_reporting(7);

spl_autoload_register(function () {
    require_once './class/Image.php';
    require_once './phrase/Phrase.php';
});

$instance = new \Magein\phrase\Phrase();

$head = './static/image/head.png';
$qrcode = './static/image/qrcode.png';
$number = 10;

if (isset($_GET['pay'])) {
    $instance->paySuccess($head, $number, '马到成功', $qrcode);
} else {
    $instance->answerSuccess($head, $qrcode, ['马到成功', '功成名就', '就事论事'], '萨迪as飞机开始了的念佛迪士尼', '掉撒福建省带你飞登录时间');
}

