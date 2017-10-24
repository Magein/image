### 图片处理类
    1. 生成略缩图
    2. 圆形处理
    3. 获取图片resource类型资源
    
### 获取图片资源

     可以将http、https、物理路径、图片字符串类型的图片源转化成resource类型。 
     
    
     使用gd库操作图片大部分需要使用resource类型的图片源。 
       
     1. 构造函数
        $imageUrl='';
        $image=new Image($imageUrl);
        $image->getResource();
        
     2. transImageResource方法
        $image->transImageResource();
        
### 远程图片处理
     远程图片处理： 
        1. 处理http://xxx.com/xx.ext或者https://xxx.com/xx.ext
            ext可以是png、jpg、gif等明确的文件格式
            直接使用imagecreatefrompng()等函数处理
            
        2. 处理http:/xxx.com/xxx或者https://xxx.com/xx
            没有指明文件格式的，可以使用php的curl请求，打印头编码查看Content-type:xx 的类型
        
        3. 处理图片字符串可以使用imagecreatefromstring
        
### 合成文字
    注意：imagefttext中字体的大小使用的是磅值（point）
     
    1. 创建画布,大小100px*100px
        $canvas=$image->getCanvas(100,100);
        
    2. 创建文字
        $textResource=imagefttext($canvas,24,0,40,40,'字体文件路径','马到成功');
    
### 合成图片    
    imagecopy($dst_resource,$src_resource,$dst_x,$dst_y,$src_x,$src_y,$src_w,$src_y);
    
    将图片处理成resource类型使用imagecopy函数处理即可
    
### 图片输出或保存

    调用$image->output()输出
    调用$image->save()保存
     
    配合header输出到浏览器
     header('Content-type:image/png')
     imagepng($resource);
     
    保存图片
     imagepng($resource,$filename);
     
    保存图片大小
     imagepng($resource,$filename,20);
     第三个参数是图片的质量，数字越大，保真度越高，对应的文件越大
        
### 注意释放内存
    
    输出，保存后记得释放内存
    
    imagedestroy($resource);     
    
    
                
        
    
