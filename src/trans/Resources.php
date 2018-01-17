<?php

namespace magein\trans;

/**
 * 创建图片资源类型
 * 转化图片资源类型
 * Class IResource
 * @package Magein
 */
class Resources
{
    /**
     * @var Resources
     */
    private static $instance;

    /**
     * @return Resources
     */
    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * 获取图片资源，支持png、jpg、jpeg、git、string、http、https、物理地址等
     * @param $image
     * @param null $extend
     * @return null|resource
     */
    public function get($image, $extend = null)
    {
        if ($extend == null) {
            $extend = strtolower(pathinfo($image, PATHINFO_EXTENSION));
        }

        switch ($extend) {
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
            case 'string':
                $resource = imagecreatefromstring($image);
                break;
            case 'http':
                $resource = imagecreatefromstring(file_get_contents($image));
                break;
            case 'https':
                $resource = imagecreatefromstring($this->curl($image));
                break;
            default:
                $resource = $this->transRemoteImage($image);
                break;
        }

        if (is_resource($resource)) {
            return $resource;
        }

        return null;
    }

    /**
     * 转化远程图片
     * @param $imageUrl
     * @return null|resource
     */
    private function transRemoteImage($imageUrl)
    {
        if (preg_match('/^http:/', $imageUrl)) {
            $image = file_get_contents($imageUrl);
        } elseif (preg_match('/^https:/', $imageUrl)) {
            $image = $this->curl($imageUrl);
        } else {
            $image = $imageUrl;
        }

        if ($image) {
            return imagecreatefromstring($image);
        }

        return null;
    }

    /**
     * @param $imageUrl
     * @return mixed
     */
    private function curl($imageUrl)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $imageUrl);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

    /**
     * 创建一个资源类型的画布
     * @param int $width
     * @param int $height
     * @param array $color
     * @param int $alpha
     * @return resource
     */
    public function create($width = 100, $height = 100, $color = [], $alpha = 127)
    {
        // 创建画布
        $canvas = imagecreatetruecolor($width, $height);
        imagesavealpha($canvas, true);
        $bg = $this->transColor($canvas, $color, $alpha);
        // 填充背景色
        imagefill($canvas, 0, 0, $bg);

        return $canvas;
    }

    /**
     * 适用于汉字
     * @param $string
     * @param int $size 像素
     * @param int $fontFile
     * @param array $color
     * @return resource
     */
    public function createText($string, $size = 30, $fontFile = 100, $color = [255, 255, 255])
    {
        // 像素转化为磅值
        if (GD_VERSION > '2') {
            $size = Unit::transPixelSizeToPoint($size);
        }

        $box = imagettfbbox($size, 0, $fontFile, $string);

        // 计算宽高
        $width = abs($box[4]) + 2;
        $height = abs($box[5]) + $box[1] + 2;

        $canvas = $this->create($width, $height);

        $textColor = $this->transColor($canvas, $color, 0);

        imagettftext($canvas, $size, 0, 0, abs($box[5]), $textColor, $fontFile, $string);

        return $canvas;
    }

    /**
     * @param $canvas
     * @param array $color
     * @param int $alpha
     * @return int
     */
    private function transColor($canvas, array $color = [], $alpha = 127)
    {
        $red = isset($color[0]) ? $color[0] : 255;
        $green = isset($color[1]) ? $color[1] : 255;
        $blue = isset($color[2]) ? $color[2] : 255;

        return imagecolorallocatealpha($canvas, $red, $green, $blue, $alpha);
    }
}