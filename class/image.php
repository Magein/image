<?php


class Image
{
    // 图片扩展
    private $ext = '';

    // 当前处理的图片资源
    private $resource = '';

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

    /**
     *
     * @param $image
     * @param $width
     * @param null $height
     * @return resource
     */
    private function thumb($image, $width, $height = null)
    {
        //因为PHP只能对资源进行操作，所以要对需要进行缩放的图片进行拷贝，创建为新的资源
        $resource = $this->getImageResource($image);

        //取得源图片的宽度和高度
        $size_src = getimagesize($image);
        $w = $size_src['0'];
        $h = $size_src['1'];

        //根据最大值为300，算出另一个边的长度，得到缩放后的图片宽度和高度
        if ($w > $h) {
            $w = $width;
            $h = $height ? $height : $h * ($width / $size_src['0']);
        } else {
            $h = $width;
            $w = $height ? $height : $w * ($width / $size_src['1']);
        }

        $image = imagecreatetruecolor($w, $h);

        //关键函数，参数（目标资源，源，目标资源的开始坐标x,y, 源资源的开始坐标x,y,目标资源的宽高w,h,源资源的宽高w,h）
        imagecopyresampled($image, $resource, 0, 0, 0, 0, $w, $h, $size_src['0'], $size_src['1']);

        return $image;
    }

    /**
     * @param $image
     * @return resource
     */
    private function circle($image)
    {
        //因为PHP只能对资源进行操作，所以要对需要进行缩放的图片进行拷贝，创建为新的资源
        $resource = $this->getImageResource($image);
        $w = imagesx($resource);
        $h = imagesy($resource);
        $w = min($w, $h);
        $h = $w;
        $img = imagecreatetruecolor($w, $h);
        //这一句一定要有
        imagesavealpha($img, true);
        //拾取一个完全透明的颜色,最后一个参数127为全透明
        $bg = imagecolorallocatealpha($img, 255, 255, 255, 127);
        imagefill($img, 0, 0, $bg);
        $r = $w / 2; //圆半径
        $y_x = $r; //圆心X坐标
        $y_y = $r; //圆心Y坐标
        for ($x = 0; $x < $w; $x++) {
            for ($y = 0; $y < $h; $y++) {
                $rgbColor = imagecolorat($resource, $x, $y);
                if (((($x - $r) * ($x - $r) + ($y - $r) * ($y - $r)) < ($r * $r))) {
                    imagesetpixel($img, $x, $y, $rgbColor);
                }
            }
        }
        return $img;

    }

    /**
     * @param $image
     * @return resource
     * @throws Exception
     */
    private function getImageResource($image)
    {
        if (is_resource($image)) {
            return $image;
        }

        $this->ext = strtolower(pathinfo($image, PATHINFO_EXTENSION));

        switch ($this->ext) {
            case 'png':
                $resource = imagecreatefrompng($image);
                break;
            case 'jpg':
            case 'jpeg':
                $resource = imagecreatefromjpeg($image);
                break;
            case 'gif':
                $resource = imagecreatefromgif($image);
                break;
            default:
                throw new Exception('The picture type is not supported for the time being');
                break;
        }

        return $resource;
    }

    /**
     * @param $dst_im
     * @param $src_im
     * @param int $dst_x
     * @param int $dst_y
     * @param int $src_x
     * @param int $src_y
     * @param int|null $src_w
     * @param int|null $src_h
     * @return resource
     */
    private function composeImage($dst_im, $src_im, $dst_x, $dst_y, $src_x = 0, $src_y = 0, $src_w = null, $src_h = null)
    {
        $dst_resource = $this->getImageResource($dst_im);

        $src_resource = $this->getImageResource($src_im);

        if ($src_w === null || $src_h = null) {
            $src_w = imagesx($src_resource);
            $src_h = imagesy($src_resource);
        }

        imagecopy($dst_resource, $src_resource, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h);

        $this->resource = $dst_resource;

        return $dst_resource;

    }

    /**
     * @param string $resource
     * @param string $ext
     * @throws Exception
     */
    private function output($resource = '', $ext = '')
    {
        $resource = $resource ? $resource : $this->resource;

        if (empty($resource)) {
            exit();
        }

        $ext = $ext ? $ext : $this->ext;
        ob_clean();
        switch ($ext) {
            case 'png':
                header('Content-type:image/png');
                imagepng($resource);
                break;
            case 'jpg':
            case 'jpeg':
                header('Content-type:image/jpeg');
                imagejpeg($resource);
                break;
            case 'gif':
                header('Content-type:image/gif');
                imagegif($resource);
                break;
            default:
                throw new Exception('The picture type is not supported for the time being');
                break;
        }
        exit();
    }
}
