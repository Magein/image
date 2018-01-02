<?php

namespace Magein\image\library;

/**
 * Class Image
 */
class Image
{
    /**
     * 图片资源
     * @var resource
     */
    protected $resource = null;

    /**
     * Image constructor.
     * @param $image
     */
    public function __construct($image = null)
    {
        $this->init($image);
    }

    /**
     * @param $image
     */
    public function init($image)
    {
        if ($image !== null && !is_resource($image)) {
            $image = Resources::instance()->get($image);
        }

        $this->resource = $image;
    }

    /**
     * @return resource
     */
    public function get()
    {
        return $this->resource;
    }

    /**
     * @param null $extend
     */
    public function output($extend = null)
    {
        ob_clean();

        switch ($extend) {
            case 'png':
                header('Content-type:image/png');
                imagepng($this->resource);
                break;
            case 'gif':
                header('Content-type:image/gif');
                imagegif($this->resource);
                break;
            default:
                header('Content-type:image/jpeg');
                imagejpeg($this->resource);
                break;
        }

        imagedestroy($this->resource);

        exit();
    }

    /**
     * @param $filename
     * @param null $extend
     * @param int $quality
     * @return bool
     */
    public function save($filename, $extend = null, $quality = 100)
    {
        ob_clean();

        switch ($extend) {
            case 'png':
                imagepng($this->resource, $filename, $quality);
                break;
            case 'gif':
                imagegif($this->resource, $filename);
                break;
            default:
                imagejpeg($this->resource, $filename, $quality);
                break;
        }

        imagedestroy($this->resource);

        if (is_file($filename)) {
            return true;
        }

        return false;
    }

    /**
     * 略缩图
     * @param int $width
     * @param null $height
     * @return resource
     */
    public function thumb($width = 100, $height = null)
    {
        $resource_width = imagesx($this->resource);
        $resource_height = imagesy($this->resource);

        //算出另一个边的长度，得到缩放后的图片宽度和高度
        if ($resource_width > $resource_height) {
            $image_width = $width;
            $image_height = $height ? $height : $resource_height * ($width / $resource_width);
        } else {
            $image_height = $width;
            $image_width = $height ? $height : $resource_width * ($width / $resource_height);
        }

        // 缩放后的大小
        $resource = imagecreatetruecolor($image_width, $image_height);

        //目标资源，源，目标资源的开始坐标x,y, 源资源的开始坐标x,y,目标资源的宽高w,h,源资源的宽高w,h
        imagecopyresampled($resource, $this->resource, 0, 0, 0, 0, $image_width, $image_height, $resource_width, $resource_height);

        return $this->resource = $resource;
    }

    /**
     * 裁剪
     * @param int $x
     * @param int $y
     * @param int $width
     * @param int $height
     * @return resource
     */
    public function cut($x = 0, $y = 0, $width = 100, $height = 100)
    {
        $canvas = imagecrop($this->resource, ['x' => $x, 'y' => $y, 'width' => $width, 'height' => $height]);

        return $this->resource = $canvas;
    }

    /**
     * 圆形处理
     * @return resource
     */
    public function circle()
    {
        // 获取图片的大小
        $width = imagesx($this->resource);
        $height = imagesy($this->resource);
        $width = min($width, $height);
        $height = $width;

        // 创建一个全透明的背景图
        $resource = Resources::instance()->create($width, $height);

        // 将源图片的中的每一个像素取出来填充到创建的图片中（在圆半径内）
        $r = $width / 2; //圆半径
        for ($x = 0; $x < $width; $x++) {
            for ($y = 0; $y < $height; $y++) {
                $rgbColor = imagecolorat($this->resource, $x, $y);
                if (((($x - $r) * ($x - $r) + ($y - $r) * ($y - $r)) < ($r * $r))) {
                    imagesetpixel($resource, $x, $y, $rgbColor);
                }
            }
        }

        $this->resource = $resource;

        return $this->resource;
    }

    /**
     * 添加水印
     * @param $image
     * @param string $x
     * @param string $y
     * @return resource
     */
    public function water($image, $x = '100%', $y = '100%')
    {
        if (!is_resource($image)) {
            $image = Resources::instance()->get($image);
        }

        $resourceWidth = imagesx($this->resource);
        $resourceHeight = imagesy($this->resource);

        $imageWidth = imagesx($image);
        $imageHeight = imagesy($image);

        if (!is_int($x) && !is_int($y)) {

            $x = intval($x);
            $y = intval($y);

            $x = $x > 100 ? 100 : $x;
            $y = $y > 100 ? 100 : $x;

            $x = $resourceWidth * ($x / 100) - $imageWidth;
            $y = $resourceHeight * ($y / 100) - $imageHeight;
        }

        imagecopy($this->resource, $image, $x, $y, 0, 0, $imageWidth, $imageHeight);

        return $this->resource;
    }
}
