<?php

namespace Magein;
/**
 * Class Image
 */
class Image
{
    // 图片扩展
    protected $extend = '';
    /**
     * 图片资源
     * @var resource
     */
    protected $resource = '';
    /**
     * 图片合成的背景
     * @var string|resource
     */
    protected $background = '';

    /**
     * Image constructor.
     * @param null|string|resource $image
     */
    public function __construct($image = null)
    {
        if ($image) {
            $this->transImageResource($image);
        }
    }

    /**
     * @return resource
     * @throws \Exception
     */
    public function getResource()
    {
        if ($this->resource) {
            return $this->resource;
        } else {
            throw new \Exception('image resource is null');
        }
    }

    /**
     * 获取图片资源
     * @param string $image 图片资源，可以是图片字符串资源，http，https，图片路径（绝对地址或者物理地址）
     * @param string $extend 图片的扩展
     * @return resource
     */
    public function transImageResource($image, $extend = '')
    {
        if (is_resource($image)) {
            return $image;
        }
        if ($extend) {
            $this->extend = $extend;
        } else {
            $this->extend = strtolower(pathinfo($image, PATHINFO_EXTENSION));
        }
        switch ($this->extend) {
            case 'png':
                $this->resource = imagecreatefrompng($image);
                break;
            case 'jpg':
            case 'jpeg':
                $this->resource = imagecreatefromjpeg($image);
                break;
            case 'gif':
                $this->resource = imagecreatefromgif($image);
                break;
            case 'string':
                $this->resource = imagecreatefromstring($image);
                break;
            case 'http':
                $this->resource = imagecreatefromstring(file_get_contents($image));
                break;
            case 'https':
                $this->resource = imagecreatefromstring($this->curl($image));
                break;
            default:
                $this->resource = $this->transRemoteImage($image);
                break;
        }
        return $this->resource;
    }

    /**
     * 转化远程图片
     * @param $imageUrl
     * @return resource
     */
    private function transRemoteImage($imageUrl)
    {
        if (preg_match('/^http:/', $imageUrl)) {
            $resource = imagecreatefromstring(file_get_contents($imageUrl));
        } elseif (preg_match('/^https:/', $imageUrl)) {
            $resource = imagecreatefromstring($this->curl($imageUrl));
        } else {
            $resource = imagecreatefromstring($imageUrl);
        }
        return $resource;
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
     * @param resource $resource
     * @param string $extend
     */
    public function output($resource = null, $extend = '')
    {
        $resource = $resource ? $resource : $this->getResource();
        $extend = $extend ? $extend : $this->extend;
        ob_clean();
        switch ($extend) {
            case 'png':
                header('Content-type:image/png');
                imagepng($resource);
                break;
            case 'gif':
                header('Content-type:image/gif');
                imagegif($resource);
                break;
            default:
                header('Content-type:image/jpeg');
                imagejpeg($resource);
                break;
        }
        imagedestroy($resource);
        exit();
    }

    /**
     * @param string $filename
     * @param null $resource
     * @param string $extend
     */
    public function save($filename, $resource = null, $extend = '')
    {
        $resource = $resource ? $resource : $this->getResource();
        $extend = $extend ? $extend : $this->extend;
        ob_clean();
        switch ($extend) {
            case 'png':
                imagepng($resource, $filename);
                break;
            case 'gif':
                imagegif($resource, $filename);
                break;
            default:
                imagejpeg($resource, $filename);
                break;
        }
        imagedestroy($resource);
        exit();
    }

    /**
     * @param resource $resource
     * @param int $width
     * @param int|null $height
     * @return resource
     */
    public function thumb($resource = null, $width = 100, $height = null)
    {
        $resource = $resource ? $resource : $this->getResource();
        //取得源图片的宽度和高度
        $resource_width = imagesx($resource);
        $resource_height = imagesy($resource);
        //根据最大值为300，算出另一个边的长度，得到缩放后的图片宽度和高度
        if ($resource_width > $resource_height) {
            $image_width = $width;
            $image_height = $height ? $height : $resource_height * ($width / $resource_width);
        } else {
            $image_height = $width;
            $image_width = $height ? $height : $resource_width * ($width / $resource_height);
        }
        $this->resource = imagecreatetruecolor($image_width, $image_height);
        //关键函数，参数（目标资源，源，目标资源的开始坐标x,y, 源资源的开始坐标x,y,目标资源的宽高w,h,源资源的宽高w,h）
        imagecopyresampled($this->resource, $resource, 0, 0, 0, 0, $image_width, $image_height, $resource_width, $resource_height);
        return $this->resource;
    }

    /**
     * @param resource $resource
     * @return resource
     */
    public function circle($resource = null)
    {
        $resource = $resource ? $resource : $this->getResource();
        $width = imagesx($resource);
        $height = imagesy($resource);
        $width = min($width, $height);
        $height = $width;
        $this->resource = imagecreatetruecolor($width, $height);
        //这一句一定要有
        imagesavealpha($this->resource, true);
        //拾取一个完全透明的颜色,最后一个参数127为全透明
        $bg = imagecolorallocatealpha($this->resource, 255, 255, 255, 127);
        imagefill($this->resource, 0, 0, $bg);
        $r = $width / 2; //圆半径
        for ($x = 0; $x < $width; $x++) {
            for ($y = 0; $y < $height; $y++) {
                $rgbColor = imagecolorat($resource, $x, $y);
                if (((($x - $r) * ($x - $r) + ($y - $r) * ($y - $r)) < ($r * $r))) {
                    imagesetpixel($this->resource, $x, $y, $rgbColor);
                }
            }
        }
        return $this->resource;
    }

    /**
     * 创建画布
     * @param $width
     * @param $height
     * @param array $color
     * @return resource
     */
    public function getCanvas($width, $height, $color = [])
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
     * 合成图片，在实例化的时候可以把背景图片传入到构造函数中
     * @param resource $src_resource
     * @param $dst_x
     * @param $dst_y
     * @return mixed
     */
    public function imageCopy($src_resource, $dst_x, $dst_y)
    {
        imagecopy($this->background, $src_resource, $dst_x, $dst_y, 0, 0, imagesx($src_resource), imagesy($src_resource));
        return $this->background;
    }
}