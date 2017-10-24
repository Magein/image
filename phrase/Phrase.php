<?php

namespace Magein\phrase;

use Magein\Image;

class Phrase extends Image
{

    // 支付成功的分享背景图片
    private $paySuccess = 'static/image/paySuccess.png';
    // 回答成功的分享背景图片
    private $answerSuccess = 'static/image/answerSuccess.png';
    // 可接成语的背景图片
    private $phrase = 'static/image/phrase.png';
    // 字体文件
    private $fontFamily = 'static/font/PingFang.ttc';
    // 金钱图标
    private $moneyIcon = 'static/image/moneyIcon.png';
    // 箭头图标
    private $arrow = 'static/image/arrow.png';

    public function paySuccess($head, $number, $phrase, $qrCode)
    {
        // 背景图
        $this->background = $this->transImageResource($this->paySuccess);
        $backgroundWidth = imagesx($this->background);

        // 合成头像
        $this->transHead($head);
        $this->background = $this->imageCopy($this->getResource(), 320, 228);

        // 合成文字描述
        $description = $this->paySuccessDescription($number);
        $width = imagesx($description);
        $x = ($backgroundWidth - $width) / 2 + 20;
        $this->imageCopy($description, $x, 358);

        // 合成成语
        $phrase = $this->firstPhrase($phrase);
        $this->imageCopy($phrase, 275, 446);

        // 合成二维码
        $this->transQrCode($qrCode);
        $this->imageCopy($this->getResource(), 250, 556);

        $this->output();

    }

    public function answerSuccess($head, $qrCode, $phrases, $intro, $from)
    {
        // 背景图
        $this->background = $this->transImageResource($this->answerSuccess);
        $backgroundWidth = imagesx($this->background);
        $backgroundHeight = imagesy($this->background);

        // 合成头像
        $this->transHead($head);
        $this->background = $this->imageCopy($this->getResource(), 320, 148);

        // 合成二维码
        $this->transQrCode($qrCode);
        $this->background = $this->imageCopy($this->getResource(), 250, 356);

        // 处理成语链
        $next = array_pop($phrases);
        $phrases = $this->phraseConcat($phrases);
        $width = imagesx($phrases);
        $height = imagesy($phrases);
        $x = ($backgroundWidth - $width) / 2;
        $y = ($backgroundHeight - $height) / 2;
        $this->imageCopy($phrases, $x, $y);

        // 下一个可接成语
        $nextPhrase = $this->nextPhrase($next);
        $this->background = $this->imageCopy($nextPhrase, 275, 695);

        // 释义
        $intro = $this->phraseIntro($intro, $from);
        $this->imageCopy($intro, 50, 811);

        // 输出
        $this->output();
    }

    /**
     * 处理头像
     * @param $head
     * @param int $width
     */
    private function transHead($head, $width = 110)
    {
        $this->transImageResource($head);
        $this->thumb($this->getResource(), $width);
        $this->circle();
    }

    /**
     * 处理二维码
     * @param $qrCode
     * @param int $with
     */
    private function transQrCode($qrCode, $with = 250)
    {
        $this->transImageResource($qrCode);
        $this->thumb($this->getResource(), $with);
        $this->circle();
    }

    /**
     * @param $number
     * @return resource
     */
    private function paySuccessDescription($number)
    {
        /**
         * 宽度=图标宽度（46）+文字描述宽度（409）+间距(10)+数量的位数*10
         * 高度=文字高度(48)+上下间距(2*2)
         */
        $width = 465 + strlen($number) * 10;
        $image = $this->getCanvas($width, 52, [218, 85, 67]);
        // 字体颜色
        $color = imagecolorallocate($image, 251, 222, 176);
        $description = '我发起了' . $number . '个成语接龙红包';
        imagefttext($image, 26, 0, 56, 32, $color, $this->fontFamily, $description);

        // 小图标
        $icon = $this->transImageResource($this->moneyIcon);
        $width = imagesx($icon);
        $height = imagesy($icon);
        imagecopy($image, $icon, 0, 0, 0, 0, $width, $height);

        return $image;
    }

    /**
     * 箭头左边距=10
     * 箭头宽度=34
     * 成语宽度=128，高度45
     * 成语字体大小=32
     * @param $text
     * @param int $width
     * @param int $height
     * @return resource
     * @throws \Exception
     */
    private function phraseConcat($text, $width = 128, $height = 45)
    {
        if (!is_array($text)) {
            $text = [$text];
        }
        $count = count($text);
        if ($count < 1) {
            throw new \Exception('phrase number lt 2');
        }
        if ($count > 4) {
            $text = array_slice($text, $count - 4, 4);
            $count = 4;
        }
        // 每个成语之间的间距=左右边距(10*2)+箭头宽度(34)
        $padding = 54;
        // 总长度=字体长度+边距
        $totalWidth = $width * $count + ($count - 1) * $padding;
        $image = $this->getCanvas($totalWidth, $height, [205, 83, 60]);
        // 字体颜色
        $color = imagecolorallocate($image, 240, 203, 196);
        // 箭头
        $arrow = $this->transImageResource($this->arrow);
        $arrow_width = imagesx($arrow);
        $arrow_height = imagesy($arrow);
        foreach ($text as $key => $item) {
            // 成语的偏移量
            $offset = $key * $width + $key * $padding;
            imagefttext($image, 24, 0, $offset, 32, $color, $this->fontFamily, $item);
            // 箭头的偏移量
            $offset = ($key + 1) * $width + $key * $padding + 10;
            imagecopy($image, $arrow, $offset, 12, 0, 0, $arrow_width, $arrow_height);
        }
        return $image;
    }

    /**
     * @param $text
     * @param int $width
     * @param int $height
     * @return resource
     */
    private function nextPhrase($text, $width = 128, $height = 45)
    {
        $phrase = $this->transImageResource($this->phrase);
        $image = $this->getCanvas($width, $height, [255, 122, 95]);
        // 字体颜色
        $color = imagecolorallocate($image, 255, 255, 255);
        // 32px换成磅：24
        imagefttext($image, 24, 0, 0, 35, $color, $this->fontFamily, $text);
        // 居中
        $x = (imagesx($phrase) - $width) / 2;
        $y = ((imagesy($phrase)) - $height) / 2;
        imagecopy($phrase, $image, $x, $y, 0, 0, imagesx($image), imagesy($image));
        return $phrase;
    }

    /**
     * 成语说明
     * @param $intro
     * @param $from
     * @return resource
     */
    private function phraseIntro($intro, $from)
    {
        $slice = function ($string, $len) {
            $content = [];
            $i = 0;
            while (true) {
                if ($i >= 2) {
                    break;
                }
                $offset = $len * $i;
                $substr = mb_substr($string, $offset, $len);
                if ($substr) {
                    if ($i == 1 && mb_strlen($substr) > $len - 3) {
                        $substr = mb_substr($string, $offset, $len - 3) . '...';
                    }
                    $content[] = $substr;
                } else {
                    break;
                }
                $i++;
            }
            return $content;
        };
        $image = $this->getCanvas(650, 380);
        // 字体颜色
        $color = imagecolorallocate($image, 53, 53, 53);
        $text[] = '释义:';
        $text[] = $intro ? $intro : '暂无';
        $key = 0;
        foreach ($text as $item) {
            $item = $slice($item, 20);
            foreach ($item as $value) {
                /**
                 * 字体大小：28px换成点为21
                 * x，y 计算是左下角的位置
                 * 172 是手动调出来的   pt跟px 换算没有一个固定的换算单位
                 */
                $offset = ($key + 1) * 60;
                imagefttext($image, 21, 0, 40, $offset, $color, $this->fontFamily, $value);
                $key++;
            }
        }
        $text = [];
        $text[] = '出处:';
        $text[] = $from ? $from : '暂无';
        $key = 0;
        foreach ($text as $item) {
            $item = $slice($item, 20);
            foreach ($item as $value) {
                /**
                 * 字体大小：28px换成点为21
                 * x，y 计算是左下角的位置
                 * 172 是手动调出来的   pt跟px 换算没有一个固定的换算单位
                 */
                $offset = ($key + 1) * 60 + 172;
                imagefttext($image, 21, 0, 40, $offset, $color, $this->fontFamily, $value);
                $key++;
            }
        }
        return $image;
    }

    /**
     * 第一个成语
     * @param $phrase
     * @return resource
     */
    private function firstPhrase($phrase)
    {
        $image = $this->getCanvas(200, 72, [218, 85, 67]);
        // 字体颜色
        $color = imagecolorallocate($image, 251, 222, 176);
        imagefttext($image, 37, 0, 0, 40, $color, $this->fontFamily, $phrase);
        return $image;
    }

    public function imageCopy($src_resource, $dst_x, $dst_y)
    {
        imagecopy($this->background, $src_resource, $dst_x, $dst_y, 0, 0, imagesx($src_resource), imagesy($src_resource));

        $this->resource = $this->background;

        return $this->resource;
    }
}