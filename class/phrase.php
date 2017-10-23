<?php

class phrase extends Image
{
// 使用的背景图
    private $backgroundImage = '';

    // 支付成功的分享背景图片
    private $paySuccessShareBackgroundImage = './image/paySuccessShare.png';

    // 回答成功的分享背景图片
    private $answerSuccessShareBackgroundImage = './image/answerSuccessShare.png';

    // 可接成语的背景图片
    private $phraseBackgroundImage = './image/phrase.png';

    // 字体文件
    private $fontFamily = './image/PingFang.ttc';

    // 金钱图标
    private $moneyIcon = './image/moneyIcon.png';

    /**
     * 回答成功后的分享图片
     * @param $headImageSrc
     * @param $qrCode
     * @param $phrases
     * @param $intro
     * @param $from
     */
    public function answerSuccess($headImageSrc, $qrCode, $phrases, $intro, $from)
    {
        $this->backgroundImage = $this->answerSuccessShareBackgroundImage;

        // 背景图
        list($backgroundWidth, $backgroundHeight) = getimagesize($this->backgroundImage);

        // 合成头像
        $image = $this->appendHeadImage($headImageSrc, 320, 70);


        // 合成二维码
        $image = $this->composeImage($image, $qrCode, 250, 348);

        // 处理成语链
        $next = array_pop($phrases);
        $phrases = $this->phraseConcat($phrases);
        $width = imagesx($phrases);
        $height = imagesy($phrases);
        $x = ($backgroundWidth - $width) / 2;
        $y = ($backgroundHeight - $height) / 2;
        $image = $this->composeImage($image, $phrases, $x, $y);

        // 下一个可接成语
        $nextPhrase = $this->nextPhrase($this->phraseBackgroundImage, $next);
        $image = $this->composeImage($image, $nextPhrase, 260, 700);

        // 释义
        $intro = $this->phraseIntro($intro, $from);
        $this->composeImage($image, $intro, 30, 823);

        // 输出
        $this->output();
    }

    /**
     * 支付成功后分享图片
     * @param $headImageSrc
     * @param $number
     * @param $phrase
     * @param $qrCode
     */
    public function paySuccess($headImageSrc, $number, $phrase, $qrCode)
    {
        $this->backgroundImage = $this->paySuccessShareBackgroundImage;

        // 背景图
        list($backgroundWidth, $backgroundHeight) = getimagesize($this->backgroundImage);

        // 合成头像
        $image = $this->appendHeadImage($headImageSrc, 320, 143);

        // 合成描述
        $description = $this->paySuccessDescription($number);
        $width = imagesx($description);
        $x = ($backgroundWidth - $width) / 2 + 20;
        $image = $this->composeImage($image, $description, $x, 273);

        // 合成第一个成语
        $phrase = $this->firstPhrase($phrase);
        $image = $this->composeImage($image, $phrase, 300, 361);

        // 合成二维码
        $this->composeImage($image, $qrCode, 250, 476);

        // 输出
        $this->output();

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
        $image = $this->getTextBackground($width, 52, [218, 85, 67]);

        // 字体颜色
        $color = imagecolorallocate($image, 251, 222, 176);

        $description = '我发起了' . $number . '个成语接龙红包';

        imagefttext($image, 24, 0, 56, 32, $color, $this->fontFamily, $description);

        // 小图标
        $icon = $this->getImageResource($this->moneyIcon);
        $width = imagesx($icon);
        $height = imagesy($icon);
        imagecopy($image, $icon, 0, 0, 0, 0, $width, $height);

        return $image;
    }


    /**
     * 第一个成语
     * @param $phrase
     * @return resource
     */
    private function firstPhrase($phrase)
    {
        $image = $this->getTextBackground(200, 72, [218, 85, 67]);

        // 字体颜色
        $color = imagecolorallocate($image, 251, 222, 176);

        imagefttext($image, 30, 0, 0, 32, $color, $this->fontFamily, $phrase);

        return $image;
    }

    /**
     * @param $headImageSrc
     * @param int $x
     * @param int $y
     * @param int $thumb
     * @return resource
     */
    private function appendHeadImage($headImageSrc, $x = 320, $y = 70, $thumb = 110)
    {
        // 处理头像，大小110，圆形
        $head = $this->thumb($headImageSrc, $thumb);
        $head = $this->circle($head);
        $image = $this->composeImage($this->backgroundImage, $head, $x, $y);
        return $image;
    }

    /**
     * @param int $width
     * @param int $height
     * @param array $color
     * @return resource
     */
    private function getTextBackground($width = 128, $height = 45, $color = [])
    {
        $image = imagecreatetruecolor($width, $height);
        imagesavealpha($image, true);
        //拾取一个完全透明的颜色,最后一个参数127为全透明
        $bg = imagecolorallocatealpha($image, 255, 255, 255, 127);
        imagefill($image, 0, 0, $bg);

        // 设置背景色
        $color = imagecolorallocate($image, isset($color[0]) ? $color[0] : 255, isset($color[1]) ? $color[1] : 255, isset($color[2]) ? $color[2] : 255);
        imagefilledrectangle($image, 0, 0, $width, $height, $color);

        return $image;
    }

    /**
     * @param $background
     * @param $text
     * @param int $width
     * @param int $height
     * @return resource
     */
    private function nextPhrase($background, $text, $width = 128, $height = 45)
    {
        $background = $this->getImageResource($background);

        $image = $this->getTextBackground($width, $height, [255, 122, 95]);

        // 字体颜色
        $color = imagecolorallocate($image, 255, 255, 255);

        // 32px换成磅：24
        imagefttext($image, 24, 0, 0, 32, $color, $this->fontFamily, $text);

        // 居中
        $x = (imagesx($background) - $width) / 2;
        $y = ((imagesy($background)) - $height) / 2;
        imagecopy($background, $image, $x, $y, 0, 0, imagesx($image), imagesy($image));

        return $background;
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

        $image = $this->getTextBackground(690, 390);

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
     * 箭头左边距=10
     * 箭头宽度=34
     * 成语宽度=128，高度45
     * 成语字体大小=32
     * @param $text
     * @param int $width
     * @param int $height
     * @return resource
     * @throws Exception
     */
    private function phraseConcat($text, $width = 128, $height = 45)
    {
        if (!is_array($text)) {
            $text = [$text];
        }

        $count = count($text);

        if ($count < 1) {
            throw new Exception('phrase number lt 2');
        }

        if ($count > 4) {
            $text = array_slice($text, $count - 4, 4);
            $count = 4;
        }

        // 每个成语之间的间距=左右边距(10*2)+箭头宽度(34)
        $padding = 54;

        // 总长度=字体长度+边距
        $totalWidth = $width * $count + ($count - 1) * $padding;
        $image = $this->getTextBackground($totalWidth, $height, [205, 83, 60]);

        // 字体颜色
        $color = imagecolorallocate($image, 240, 203, 196);

        // 箭头
        $arrow = $this->getImageResource('./image/arrow.png');
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


}