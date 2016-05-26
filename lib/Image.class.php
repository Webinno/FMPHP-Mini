<?php
/**
 * FMPHP-Mini (http://www.webinno.cn)
 *
 * An open source application development framework for PHP 5.4 or newer
 *
 * @package		FMPHP-Mini
 * @author		FMPHP Dev Team （QQ官方群：330488100）
 * @copyright	copyright (c) 2015 - 2016, 互联网创新实验室(http://www.webinno.cn)
 *
 * @license		
 * @link		http://www.webinno.cn/project/FMPHP-Mini
 * @link        https://github.com/Webinno/FMPHP-Mini
 * @since		Version 0.1
 * 
 * @filesource
 */


/**
 * Image 图片处理类
 * @package Util
 */
class Image {

	/**
	 * @var string $fileName 文件名
	 * @access private
	 */
	private $fileName = '';
	
	/**
	 * @var gd resource $imageResource 原图像
	 * @access private
	 */
	private $imageResource = NULL;
	
	/**
	 * @var int $imageWidth 原图像宽
	 * @access private
	 */
	private $imageWidth = NULL;
	
	/**
	 * @var int $imageHeight 原图像高
	 * @access private
	 */
	private $imageHeight = NULL;
	
	/**
	 * @var int $imageType 原图像类型
	 * @access private
	 */
	private $imageType = NULL;
	
	/**
	 * @var int $newResource 新图像
	 * @access private
	 */
	private $newResource = NULL;
	
	/**
	 * @var int $newResType 新图像类型
	 * @access private
	 */
	private $newResType = NULL;
	
	/**
	 * 构造函数
	 * @param string $fileName 文件名
     */
	public function __construct($fileName = NULL) {
		$this->fileName = $fileName;
		if ($this->fileName) {
			$this->getSrcImageInfo();
		}
	}
	
	/**
	 * 取源图像信息
	 * @access private
	 * @return void
	 */
	private function getSrcImageInfo() {
		$info = $this->getImageInfo();
		$this->imageWidth = $info[0];
		$this->imageHeight = $info[1];
		$this->imageType = $info[2];
	}

	/**
	 * 取图像信息
	 * @param string $fileName 文件名
	 * @access private
	 * @return array
	 */
	private function getImageInfo($fileName = NULL) {
		if ($fileName==NULL) {
			$fileName = $this->fileName;
		}
		$info = getimagesize($fileName);
		return $info;
	}

	/**
	 * 创建源图像GD 资源
	 * @access private
	 * @return void
	 */
	private function createSrcImage () {
		$this->imageResource = $this->createImageFromFile();
	}

	/**
	 * 跟据文件创建图像GD 资源
	 * @param string $fileName 文件名
	 * @return gd resource
	 */
    public function createImageFromFile($fileName = NULL)
    {
		if (!$fileName) {
			$fileName = $this->fileName;
			$imgType = $this->imageType;
		}
        if (!is_readable($fileName) || !file_exists($fileName)) {
            throw new Exception('Unable to open file "' . $fileName . '"');
        }

		if (!$imgType) {
			$imageInfo = $this->getImageInfo($fileName);
			$imgType = $imageInfo[2];
		}
		
        switch ($imgType) {
		case IMAGETYPE_GIF:
			$tempResource = imagecreatefromgif($fileName);
			break;
		case IMAGETYPE_JPEG:
			$tempResource = imagecreatefromjpeg($fileName);
			break;
		case IMAGETYPE_PNG:
			$tempResource = imagecreatefrompng($fileName);
			break;
		case IMAGETYPE_WBMP:
			$tempResource = imagecreatefromwbmp($fileName);
			break;
		case IMAGETYPE_XBM:
			$tempResource = imagecreatefromxbm($fileName);
			break;
		default:
			throw new Exception('错误的图片格式，或图片有问题！');
        }
		return $tempResource;
    }
	/**
	 * 改变图像大小
	 * @param int $width 宽
	 * @param int $height 高
	 * @param string $flag 按什么方式改变 0=长宽转换成参数指定的 1=按比例缩放，长宽约束在参数指定内，2=以宽为约束缩放，3=以高为约束缩放
	 * @return string
	 */
	public function resizeImage($width, $height, $flag=1) {
		global $cfg;
		$widthRatio = $width/$this->imageWidth;
		$heightRatio = $height/$this->imageHeight;
		switch ($flag) {
		case 1:
			if ($this->imageHeight < $height && $this->imageWidth < $width) {
				$endWidth = $this->imageWidth;
				$endHeight = $this->imageHeight;
				//return;
			} elseif (($this->imageHeight * $widthRatio)>$height) {
				$endWidth = ceil($this->imageWidth * $heightRatio);
				$endHeight = $height;
			} else {
				$endWidth = $width;
				$endHeight = ceil($this->imageHeight * $widthRatio);
			}
			break;
		case 2:
			$endWidth = $width;
			$endHeight = ceil($this->imageHeight * $widthRatio);
			break;
		case 3:
			$endWidth = ceil($this->imageWidth * $heightRatio);
			$endHeight = $height;
			break;
		case 4:
			$endWidth2 = $width;
			$endHeight2 = $height;
			if ($this->imageHeight < $height && $this->imageWidth < $width) {
				$endWidth = $this->imageWidth;
				$endHeight = $this->imageHeight;
				//return;
			} elseif (($this->imageHeight * $widthRatio)<$height) {
				$endWidth = ceil($this->imageWidth * $heightRatio);
				$endHeight = $height;
			} else {
				$endWidth = $width;
				$endHeight = ceil($this->imageHeight * $widthRatio);
			}
			break;
		case 5:
			$endWidth2 = $width;
			$endHeight2 = $height;
			if ($this->imageHeight > $height && $this->imageWidth > $width) {
				//都大
				$ratio = max($this->imageHeight/$height,$this->imageWidth/$width);
			}elseif ($this->imageHeight > $height){
				$ratio = $this->imageHeight/$height;
			}elseif ( $this->imageWidth > $width){
				$ratio =$this->imageWidth/$width;
			}else{
				$ratio =1;
			}
			
			$endWidth = $this->imageWidth / $ratio;
			$endHeight = $this->imageHeight / $ratio;
			
			break;
		default:
			$endWidth = $width;
			$endHeight = $height;
			break;
		}
		if ($this->imageResource==NULL) {
			$this->createSrcImage();
		}
		if($flag == 5){
			//直接缩略
			$this->newResource = imagecreatefromjpeg($cfg['path']['data'].'blank_thumb.jpg');
		}elseif ($flag==4) {
			$this->newResource = imagecreatetruecolor($endWidth2,$endHeight2);
		} else {
			$this->newResource = imagecreatetruecolor($endWidth,$endHeight);
		}
		$this->newResType = $this->imageType;
		if($flag == 5){
			$dest_x = ($width-$endWidth)/2;
			$dest_y = ($height-$endHeight)/2;
			imagecopyresampled($this->newResource, $this->imageResource, $dest_x, $dest_y, 0, 0, $endWidth, $endHeight,$this->imageWidth,$this->imageHeight);
		}else{
			imagecopyresampled($this->newResource, $this->imageResource, 0, 0, 0, 0, $endWidth, $endHeight,$this->imageWidth,$this->imageHeight);
		}
	}

	/**
	 * 给图像加水印
	 * @param string $waterContent 水印内容可以是图像文件名，也可以是文字
	 * @param int $pos 位置0-9可以是数组
	 * @param int $textFont 字体大字，当水印内容是文字时有效
	 * @param string $textColor 文字颜色，当水印内容是文字时有效
	 * @return string
	 */
	public function waterMark($waterContent, $pos = 0, $textFont=5, $textColor="#ffffff") {
		$isWaterImage = file_exists($waterContent);
		if ($isWaterImage) {
			$waterImgRes = $this->createImageFromFile($waterContent);
			$waterImgInfo = $this->getImageInfo($waterContent);
			$waterWidth = $waterImgInfo[0];
			$waterHeight = $waterImgInfo[1];
		} else {
			$waterText = $waterContent;
			//$temp = @imagettfbbox(ceil($textFont*2.5),0,"./cour.ttf",$waterContent);
			if ($temp) {
				$waterWidth = $temp[2]-$temp[6];
				$waterHeight = $temp[3]-$temp[7];
			} else {
				$waterWidth = 100;
				$waterHeight = 12;
			}
		}
		if ($this->imageResource==NULL) {
			$this->createSrcImage();
		}
		switch($pos) 
		{ 
		case 0://随机 
			$posX = rand(0,($this->imageWidth - $waterWidth)); 
			$posY = rand(0,($this->imageHeight - $waterHeight)); 
			break; 
		case 1://1为顶端居左 
			$posX = 0; 
			$posY = 0; 
			break; 
		case 2://2为顶端居中 
			$posX = ($this->imageWidth - $waterWidth) / 2; 
			$posY = 0; 
			break; 
		case 3://3为顶端居右 
			$posX = $this->imageWidth - $waterWidth; 
			$posY = 0; 
			break; 
		case 4://4为中部居左 
			$posX = 0; 
			$posY = ($this->imageHeight - $waterHeight) / 2; 
			break; 
		case 5://5为中部居中 
			$posX = ($this->imageWidth - $waterWidth) / 2; 
			$posY = ($this->imageHeight - $waterHeight) / 2; 
			break; 
		case 6://6为中部居右 
			$posX = $this->imageWidth - $waterWidth; 
			$posY = ($this->imageHeight - $waterHeight) / 2; 
			break; 
		case 7://7为底端居左 
			$posX = 0; 
			$posY = $this->imageHeight - $waterHeight; 
			break; 
		case 8://8为底端居中 
			$posX = ($this->imageWidth - $waterWidth) / 2; 
			$posY = $this->imageHeight - $waterHeight; 
			break; 
		case 9://9为底端居右 
			$posX = $this->imageWidth - $waterWidth-20; 
			$posY = $this->imageHeight - $waterHeight-10; 
			break; 
		default://随机 
			$posX = rand(0,($this->imageWidth - $waterWidth)); 
			$posY = rand(0,($this->imageHeight - $waterHeight)); 
			break;     
		}
		imagealphablending($this->imageResource, true);  
		if($isWaterImage) {
			imagecopy($this->imageResource, $waterImgRes, $posX, $posY, 0, 0, $waterWidth,$waterHeight);    
		} else { 
			$R = hexdec(substr($textColor,1,2)); 
			$G = hexdec(substr($textColor,3,2)); 
			$B = hexdec(substr($textColor,5)); 
			
			$textColor = imagecolorallocate($this->imageResource, $R, $G, $B);
			imagestring ($this->imageResource, $textFont, $posX, $posY, $waterText, $textColor);         
		}
		$this->newResource =  $this->imageResource;
		$this->newResType = $this->imageType;
	}
	
	/**
	 * 生成验证码图片
	 * @param int $width 宽
	 * @param string $height 高
	 * @param int $length 长度
	 * @param int $validType 0=数字,1=字母,2=数字加字母
	 * @param string $textColor 文字颜色
	 * @param string $backgroundColor 背景颜色
	 * @return void
	 */
	public function imageValidate($width, $height, $length = 4, $validType = 2, $textColor = '#000000', $backgroundColor = '#ffffff') {
		if ($validType==1) {
			//$validString = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
			//$validLength = 52;
			//no i no l
			$validString = 'abcdefghjkmnopqrstuvwxyzABCDEFGHJKMNOPQRSTUVWXYZ';
			$validLength = 48;
		} elseif ($validType==2) {
			//$validString = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
			//$validLength = 62;
			//no i no l no 1
			$validString = '0123456789abcdefghjkmnopqrstuvwxyzABCDEFGHJKMNOPQRSTUVWXYZ';
			$validLength = 57;
		} else {
			$validString = '0123456789';
			$validLength = 10;
		}
		
		srand((int)time());
		$valid = '';
		for ($i=0; $i<$length; $i++) {
			$valid .= $validString{rand(0, $validLength-1)};
		}

		$this->newResource = imagecreate($width,$height);
		$bgR = hexdec(substr($backgroundColor,1,2));
		$bgG = hexdec(substr($backgroundColor,3,2));
		$bgB = hexdec(substr($backgroundColor,5,2));
		$backgroundColor = imagecolorallocate($this->newResource, $bgR, $bgG, $bgB);
		$white = ImageColorAllocate($this->newResource, 155, 155, 155);
		$tR = hexdec(substr($textColor,1,2));
		$tG = hexdec(substr($textColor,3,2));
		$tB = hexdec(substr($textColor,5,2));
		$textColor = imagecolorallocate($this->newResource, $tR, $tG, $tB);
		for ($i=0;$i<strlen($valid);$i++){ 
			imagestring($this->newResource,5,$i*$width/$length+3,2, $valid[$i],$textColor); 
		}


		//加入干扰线
		//int imageline(int im, int x1, int y1, int x2, int y2, int col);
		for ($i = 0; $i < rand(1,2); $i++) {
			//imageline($this->newResource, rand(1, $width), rand(1, $height), rand(1, $width), rand(1, $height), $white);
		}

		for ($i = 0; $i < rand(1,2); $i++) {
			//imageline($this->newResource, rand(1, $width), rand(1, $height), rand(1, $width), rand(1, $height), $white);
		}

		for($i=0;$i<100;$i++)   //加入干扰象素
		{
			$randcolor = ImageColorallocate($img,rand(0,255),rand(0,255),rand(0,255));
			imagesetpixel($img, rand()%100 , rand()%50 , $white);
			imagesetpixel($this->newResource, rand(1, $width) , rand(1, $height) , $white);
		}

		$this->newResType = IMAGETYPE_JPEG;
		return $valid;

	}
	
	/**
	 * 显示输出图像
	 * @return void
	 */
	public function display($fileName='', $quality=60) {
	
		$imgType = $this->newResType;
		$imageSrc = $this->newResource;
        switch ($imgType) {
		case IMAGETYPE_GIF:
			if ($fileName=='') {
				header('Content-type: image/gif');
			}
			imagegif($imageSrc, $fileName, $quality);
			break;
		case IMAGETYPE_JPEG:
			if ($fileName=='') {
				header('Content-type: image/jpeg');
			}
			imagejpeg($imageSrc, $fileName, $quality);
			break;
		case IMAGETYPE_PNG:
			if ($fileName=='') {
				header('Content-type: image/png');
				imagepng($imageSrc);
			} else {
				imagepng($imageSrc, $fileName);
			}
			break;
		case IMAGETYPE_WBMP:
			if ($fileName=='') {
				header('Content-type: image/wbmp');
			}
			imagewbmp($imageSrc, $fileName, $quality);
			break;
		case IMAGETYPE_XBM:
			if ($fileName=='') {
				header('Content-type: image/xbm');
			}
			imagexbm($imageSrc, $fileName, $quality);
			break;
		default:
			throw new Exception('Unsupport image type');
        }
		imagedestroy($imageSrc);
	}
	
	/**
	 * 保存图像
	 * @param int $fileNameType 文件名类型 0使用原文件名，1使用指定的文件名，2在原文件名加上后缀，3产生随机文件名
	 * @param string $folder 文件夹路径 为空为与原文件相同
	 * @param string $param 参数$fileNameType为2时为文件名加后缀
	 * @return void
	 */
	public function save($fileNameType = 0, $folder = NULL, $param = '_miniature') {
		if ($folder==NULL) {
			$folder = dirname($this->fileName).DIRECTORY_SEPARATOR;
			
		}
		$fileExtName = FileSystem::fileExt($this->fileName, true);
		$fileBesicName = FileSystem::getBasicName($this->fileName, false);
		switch ($fileNameType) {
			case 1:
				//$newFileName = $folder.$param;
				$newFileName = $folder.basename($this->fileName);
				//var_dump($newFileName);
				break;
			case 2:
				$newFileName = $folder.$fileBesicName.$param.$fileExtName;
				break;
			case 3:
				$tmp = date('YmdHis');
				$fileBesicName = $tmp;
				$i = 0;
				while (file_exists($folder.$fileBesicName.$fileExtName)) {
					$fileBesicName = $tmp.$i;
					$i++;
				}
				$newFileName = $folder.$fileBesicName.$fileExtName;
				break;
			default:
				$newFileName = $this->fileName;
				break;
		}
		$this->display($newFileName);
		return $newFileName;
	}
	/**
	 * 保存图像2
	 * @param int $fileNameType 文件名类型 0使用原文件名，1使用指定的文件名，2在原文件名加上后缀，3产生随机文件名
	 * @param string $folder 文件夹路径 为空为与原文件相同
	 * @param string $param 参数$fileNameType为2时为文件名加后缀
	 * @return void
	 */
	public function save_two($fileNameType = 0, $folder = NULL, $param = '_miniature') {
		if ($folder==NULL) {
			$folder = dirname($this->fileName).DIRECTORY_SEPARATOR;
			
		}
		
		$newFileName = $folder.basename($this->fileName);
		$this->display($newFileName);
		return $newFileName;
	}
	/**
	 * 剪切出选定区域
	 *
	 * @param string $srcimgurl  ԭͼ
	 * @param string $endimgurl 处理过的图
	 * @param int $x 坐标原点X
	 * @param int $y 坐标原点Y
	 * @param int $endimg_w 最终图宽
	 * @param int $endimg_h 最终图高
	 * @param int $border_w 末坐标X
	 * @param int $border_h 末坐标Y
	 * @param int $scale 原图缩放情况百分比
	 * @param int $fix 是否自动取值
	 */
	public function cutimg($srcimgurl,$endimgurl,$x,$y,$endimg_w,$endimg_h,$border_w,$border_h,$scale=100,$fix=0){
		$path = dirname ($endimgurl);
		if (!is_dir($path)) {
			if(!@mkdir ($path, 0777)){
				die ("{$path} 此目录不能创建,文件创建失败");
			}
		}
		$ground_info = getimagesize($srcimgurl);
		switch($ground_info[2]){ 
			case 1:$im = imagecreatefromgif($srcimgurl);break; 
			case 2:$im = imagecreatefromjpeg($srcimgurl);break; 
			case 3:$im = imagecreatefrompng($srcimgurl);break; 
			default:die("图片格式不允许$srcimgurl"); 
	    }
		if($fix){//方便截取头像的一部分
			if($ground_info[0]<$ground_info[1]){
				$border_w=$ground_info[0];
				$border_h=$endimg_h*$ground_info[0]/$endimg_w;
			}elseif($ground_info[0]>$ground_info[1]){
				$border_h=$ground_info[1];
				$border_w=$endimg_w*$ground_info[1]/$endimg_h;
			}else{
				$border_w=$ground_info[0];
				$border_h=$ground_info[1];
			}
		}
		$newim = imagecreatetruecolor($endimg_w, $endimg_h);
		$x=($x*100)/$scale;
		$y=($y*100)/$scale;
		$border_width=($border_w*100)/$scale;
		$border_height=($border_h*100)/$scale;
		imagecopyresampled($newim, $im, 0,0, $x,$y, $endimg_w, $endimg_h, $border_width, $border_height );
		if(function_exists("imagegif")){
			switch($ground_info[2]){ 
				case 1:imagegif($newim,$endimgurl);break;
				case 2:imagejpeg($newim,$endimgurl);break;
				case 3:imagepng($newim,$endimgurl);break;
				default:die("errorMsg"); 
			}
		}elseif(function_exists("imagejpeg")){
			imagejpeg($newim,$endimgurl);
		}else{
			imagepng($newim,$endimgurl);
		}
		imagedestroy ($newim);
		imagedestroy ($im);
	}
	
	  /**
     * 创建图片的缩略图
     *
     * @access  public
     * @param   string      $img    原始图片的路径
     * @param   string      $img    新图片名称
     * @param   int         $thumb_width  缩略图宽度
     * @param   int         $thumb_height 缩略图高度
     * @param   strint      $path         指定生成图片的目录名
     * @return  mix         如果成功返回缩略图的路径，失败则返回false
     */
    function make_thumb($img, $filename,$thumb_width = 0, $thumb_height = 0, $path = '', $bgcolor='')
    {
         $gd = $this->gd_version(); //获取 GD 版本。0 表示没有 GD 库，1 表示 GD 1.x，2 表示 GD 2.x
         if ($gd == 0)
         {
             $this->error_msg = '没有GD库';
             return false;
         }
        /* 检查缩略图宽度和高度是否合法 */
        if ($thumb_width == 0 && $thumb_height == 0)
        {
            return false;
        }

        /* 检查原始文件是否存在及获得原始文件的信息 */
        $org_info = @getimagesize($img);
        if (!$org_info)
        {
            return false;
        }

        if (!$this->check_img_function($org_info[2]))
        {
            return false;
        }

        $img_org = $this->img_resource($img, $org_info[2]);
		
        /* 原始图片以及缩略图的尺寸比例 */
        $scale_org      = $org_info[0] / $org_info[1];
      

        /* 创建缩略图的标志符 */
        if ($gd == 2)
        {
            $img_thumb  = imagecreatetruecolor($thumb_width, $thumb_height);
        }
        else
        {
            $img_thumb  = imagecreate($thumb_width, $thumb_height);
        }

        /* 背景颜色 */
        if (empty($bgcolor))
        {
            $bgcolor = $this->bgcolor;
        }
        $bgcolor = trim($bgcolor,"#");
        sscanf($bgcolor, "%2x%2x%2x", $white, $white, $white);
        $clr = imagecolorallocate($img_thumb, $white, $white, $white);
        imagefilledrectangle($img_thumb, 0, 0, $thumb_width, $thumb_height, $clr);
        $widthRatio = $thumb_width/$org_info[0];
        $heightRatio = $thumb_height/$org_info[1];
        	if ($org_info[1] < $thumb_height && $org_info[0] < $thumb_width) {
        			$lessen_width = $org_info[0];
        			$lessen_height = $org_info[1];
        	} elseif (($org_info[1] * $widthRatio)>$thumb_height) {
        			$lessen_width = ceil($org_info[0] * $heightRatio);
        			$lessen_height = $thumb_height;
        	} else {
        			$lessen_width = $thumb_width;
        			$lessen_height = ceil($org_info[1] * $widthRatio);
        		}
        $dst_x = ($thumb_width  - $lessen_width)  / 2;
        $dst_y = ($thumb_height - $lessen_height) / 2;

        /* 将原始图片进行缩放处理 */
        if ($gd == 2)
        {
            imagecopyresampled($img_thumb, $img_org, $dst_x, $dst_y, 0, 0, $lessen_width, $lessen_height, $org_info[0], $org_info[1]);
        }
        else
        {
            imagecopyresized($img_thumb, $img_org, $dst_x, $dst_y, 0, 0, $lessen_width, $lessen_height, $org_info[0], $org_info[1]);
        }

        /* 创建当月目录 */
        if (empty($path))
        {
            $dir = ROOT_PATH . $this->images_dir . '/' . date('Ym').'/';
        }
        else
        {
            $dir = $path;
        }

        /* 如果目标目录不存在，则创建它 */
        if (!file_exists($dir))
        {
       	 if (!is_dir($dir) && $dir!='./' && $dir!='../') {
				$dirname = '';
				$folders = explode('/',$dir);
				foreach ($folders as $folder) 
				{
					$dirname .= $folder . '/';
					if ($folder!='' && $folder!='.' && $folder!='..' && !is_dir($dirname)) 
					{
						mkdir($dirname);
						$arr['error'] .= $dirname.'   ';
					}
				}
				chmod($dir,0777);
			}
        }
        /* 生成文件 */
        if (function_exists('imagejpeg'))
        {
            $filename .= '';
            imagejpeg($img_thumb, $dir . $filename);
        }
        elseif (function_exists('imagegif'))
        {
            $filename .= '';
            imagegif($img_thumb, $dir . $filename);
        }
        elseif (function_exists('imagepng'))
        {
            $filename .= '';
            imagepng($img_thumb, $dir . $filename);
        }
        else
        {
            return false;
        }
        imagedestroy($img_thumb);
        imagedestroy($img_org);

        //确认文件是否生成
        if (file_exists($dir . $filename))
        {
            return true;
        }
        else
        {
            return false;
        }
    }
 	/**
     * 获得服务器上的 GD 版本
     *
     * @access      public
     * @return      int         可能的值为0，1，2
     */
    static function gd_version()
    {
        static $version = -1;

        if ($version >= 0)
        {
            return $version;
        }

        if (!extension_loaded('gd'))
        {
            $version = 0;
        }
        else
        {
            // 尝试使用gd_info函数
            if (PHP_VERSION >= '4.3')
            {
                if (function_exists('gd_info'))
                {
                    $ver_info = gd_info();
                    preg_match('/\d/', $ver_info['GD Version'], $match);
                    $version = $match[0];
                }
                else
                {
                    if (function_exists('imagecreatetruecolor'))
                    {
                        $version = 2;
                    }
                    elseif (function_exists('imagecreate'))
                    {
                        $version = 1;
                    }
                }
            }
            else
            {
                if (preg_match('/phpinfo/', ini_get('disable_functions')))
                {
                    /* 如果phpinfo被禁用，无法确定gd版本 */
                    $version = 1;
                }
                else
                {
                  // 使用phpinfo函数
                   ob_start();
                   phpinfo(8);
                   $info = ob_get_contents();
                   ob_end_clean();
                   $info = stristr($info, 'gd version');
                   preg_match('/\d/', $info, $match);
                   $version = $match[0];
                }
             }
        }

        return $version;
     }
 /**
     * 根据来源文件的文件类型创建一个图像操作的标识符
     *
     * @access  public
     * @param   string      $img_file   图片文件的路径
     * @param   string      $mime_type  图片文件的文件类型
     * @return  resource    如果成功则返回图像操作标志符，反之则返回错误代码
     */
    function img_resource($img_file, $mime_type)
    {
        switch ($mime_type)
        {
            case 1:
            case 'image/gif':
                $res = imagecreatefromgif($img_file);
                break;

            case 2:
            case 'image/pjpeg':
            case 'image/jpeg':
                $res = imagecreatefromjpeg($img_file);
                break;

            case 3:
            case 'image/x-png':
            case 'image/png':
                $res = imagecreatefrompng($img_file);
                break;

            default:
                return false;
        }

        return $res;
    }
 	/**
     * 检查图片处理能力
     *
     * @access  public
     * @param   string  $img_type   图片类型
     * @return  void
     */
    function check_img_function($img_type)
    {
        switch ($img_type)
        {
            case 'image/gif':
            case 1:

                if (PHP_VERSION >= '4.3')
                {
                    return function_exists('imagecreatefromgif');
                }
                else
                {
                    return (imagetypes() & IMG_GIF) > 0;
                }
            break;

            case 'image/pjpeg':
            case 'image/jpeg':
            case 2:
                if (PHP_VERSION >= '4.3')
                {
                    return function_exists('imagecreatefromjpeg');
                }
                else
                {
                    return (imagetypes() & IMG_JPG) > 0;
                }
            break;

            case 'image/x-png':
            case 'image/png':
            case 3:
                if (PHP_VERSION >= '4.3')
                {
                     return function_exists('imagecreatefrompng');
                }
                else
                {
                    return (imagetypes() & IMG_PNG) > 0;
                }
            break;

            default:
                return false;
        }
    }
	/**
	 * 得到原始图片的宽度
	 * Enter description here ...
	 */
	public function getImageWidth(){
		return $this->imageWidth;
	}
	
	/**
	 * 得到原始图片的高度
	 * Enter description here ...
	 */
	public function getImageHeight(){
		return $this->imageHeight;
	}
	
	public function resizeImageNew($width, $height) {
		$this->newResource = imagecreatetruecolor($width,$height);
		$this->createSrcImage();
		imagecopyresampled($this->newResource, $this->imageResource, 0, 0, 0, 0, $width, $height,$this->imageWidth,$this->imageHeight);
		$this->newResType = $this->imageType;
	}
}
?>
