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
class ImageFM {

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

	// 
	private $config  = array();
	//const   FONT_TYPE  = SYS . 'Lib/Image/font/simkai.ttf';

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
     * 取图像信息:如果文件名不存在， 取默认文件名
     * @param string $fileName 文件名
     * @access private
     * @return array
     */
	private function getImageInfo($fileName = NULL) {
		if ($fileName == NULL) {
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
	private function createSrcImage() {
		$this->imageResource = $this->createImageFromFile();
	}

	/**
     * 跟据文件创建图像GD 资源
     * @param string $fileName 文件名
     * @return gd resource
     */
	public function createImageFromFile($fileName = NULL) {
		if (!$fileName) {
			$fileName = $this->fileName;
			$imgType = $this->imageType;
		} else {
			$this->fileName = $fileName;
		}
		if (!is_readable($fileName) || !file_exists($fileName)) {
			throw new Exception('Unable to open file "' . $fileName . '"');
		}

		if (!isset($imgType) || !$imgType) {
			// $imageInfo = $this->getImageInfo($fileName);
			// $imgType = $imageInfo[2];
			$this->getSrcImageInfo();
			$imgType=	$this->imageType;
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
     * @param string $flag 按什么方式改变 0=长宽转换成参数指定的 1=按比例缩放，长宽约束在参数指定内，2=以宽为约束缩放，3=以高为约束缩放,5=背景图（未经测试）
     * @return string
     */
	public function resize($width, $height, $flag = 1) {
		global $cfg;

		$widthRatio = $width / $this->imageWidth;     //宽度比率： 新旧
		$heightRatio = $height / $this->imageHeight;  //高度比率：新旧

		switch ($flag) {
			case 1:    //比例不变：高和宽均不能超过指定尺寸
			if ($this->imageHeight < $height && $this->imageWidth < $width) {
				//不放大
				$endWidth = $this->imageWidth;
				$endHeight = $this->imageHeight;
				//return;
			} elseif (($this->imageHeight * $widthRatio) > $height) {
				$endWidth = ceil($this->imageWidth * $heightRatio);
				$endHeight = $height;
			} else {
				$endWidth = $width;
				$endHeight = ceil($this->imageHeight * $widthRatio);
			}
			break;

			case 2:	    //以宽为约束
			$endWidth = $width;
			$endHeight = ceil($this->imageHeight * $widthRatio);
			break;

			case 3:    //以高为约束
			$endWidth = ceil($this->imageWidth * $heightRatio);
			$endHeight = $height;
			break;

			case 5:      //未经测试
			if ($this->imageHeight > $height && $this->imageWidth > $width) {
				//都大，取最大
				$ratio = max($this->imageHeight / $height, $this->imageWidth / $width);
			} elseif ($this->imageHeight > $height) {  //高度缩， 宽度放, 以缩放主
				$ratio = $this->imageHeight / $height;
			} elseif ($this->imageWidth > $width) {
				$ratio = $this->imageWidth / $width;
			} else {
				$ratio = 1;
			}

			$endWidth = $this->imageWidth / $ratio;
			$endHeight = $this->imageHeight / $ratio;

			break;

			default:    //按要求缩放
			$endWidth = $width;
			$endHeight = $height;
			break;
		}

		if ($this->imageResource == NULL) {
			$this->createSrcImage();
		}
		if ($flag == 5) {
			//直接缩略
			$this->newResource = imagecreatefromjpeg('C:/Users/Tom Tang/Desktop/test_image/blank_thumb.jpg');
		} else {
			$this->newResource = imagecreatetruecolor($endWidth, $endHeight);
		}

		$this->newResType = $this->imageType;
		if ($flag == 5) {
			$dest_x = ($width - $endWidth) / 2;
			$dest_y = ($height - $endHeight) / 2;
			imagecopyresampled($this->newResource, $this->imageResource, $dest_x, $dest_y, 0, 0, $endWidth, $endHeight, $this->imageWidth, $this->imageHeight);
		} else {
			imagecopyresampled($this->newResource, $this->imageResource, 0, 0, 0, 0, $endWidth, $endHeight, $this->imageWidth, $this->imageHeight);
		}
		//bool imagecopyresampled ( resource $dst_image , resource $src_image , int $dst_x , int $dst_y , int $src_x , int $src_y , int $dst_w , int $dst_h , int $src_w , int $src_h )
	}

	/**
     * 给图像加水印
     * @param string $waterContent 水印内容可以是图像文件名，也可以是文字
     * @param int $pos 位置0-9可以是数组
     * @param int $textFont 字体大字，当水印内容是文字时有效
     * @param string $textColor 文字颜色，当水印内容是文字时有效
     * @return string
     */
	public function waterMark($waterContent, $pos = 0, $fontSize = 16,  $fontType = '',  $textColor = "#ffffff") {
		$isWaterImage = file_exists($waterContent);
		$flag = 0;

		if ($isWaterImage) {
			//图像水印
			$flag   = 1;
			$waterImgRes  = $this->createImageFromFile($waterContent);
			$waterImgInfo = $this->getImageInfo($waterContent);
			$waterWidth   = $waterImgInfo[0];
			$waterHeight  = $waterImgInfo[1];
		} else {
			//文本水印
			$flag   = 2;
			$waterText = $waterContent;

			$temp = @imagettfbbox($fontSize, 0, $fontType, $waterContent);

			//$temp = @imagettfbbox(ceil($textFont*2.5), 0, "./cour.ttf", $waterContent);
			if (isset($temp) && $temp) {
				$waterWidth  = abs($temp[2] - $temp[6]);
				$waterHeight = abs($temp[3] - $temp[7]);
			} else {
				$waterWidth  = 100;
				$waterHeight = 12;
			}
		}
		//取得资源信息
		$image_width  = 0;
		$image_height = 0;
		if ($this->newResource) {
			//已有处理，在新的资源上添加
			$image_width  = imagesx ($this->newResource);
			$image_height = imagesy ($this->newResource);
		} else {
			//未处理
			if ($this->imageResource == NULL) {
				$this->createSrcImage();
			}
			$image_width  = $this->imageWidth;
			$image_height = $this->imageHeight;
			$this->newResource = $this->imageResource;
			$this->newResType  = $this->imageType;
		}

		// for imagettftext
		if (is_string($pos) && preg_match('#^(\d*?)\*(\d*)$#', $pos)) {
			$posXY = explode('*', $pos);
			$posX  = $posXY[0];
			$posY  = $posXY[1];
		} else {
			if ($flag = 2) {

				switch ($pos) {
					case 0://随机
					$posX = rand(0, ($image_width - $waterWidth));
					$posY = rand(0, ($image_height - $waterHeight));

					break;
					case 1://1为顶端居左
					$posX = 0;
					$posY = 0 +  $waterHeight;
					break;
					case 2://2为顶端居中
					$posX = ($image_width - $waterWidth) / 2;
					$posY = 0 +  $waterHeight;
					break;
					case 3://3为顶端居右
					$posX = $image_width - $waterWidth;
					$posY = 0 +  $waterHeight;
					break;
					case 4://4为中部居左
					$posX = 0;
					$posY = ($image_height - $waterHeight) / 2;
					break;
					case 5://5为中部居中
					$posX = ($image_width - $waterWidth) / 2;
					$posY = ($image_height - $waterHeight) / 2;
					break;
					case 6://6为中部居右
					$posX = $image_width - $waterWidth;
					$posY = ($image_height - $waterHeight) / 2;
					break;
					case 7://7为底端居左
					$posX = 0;
					$posY = $image_height - $waterHeight/2;
					break;
					case 8://8为底端居中
					$posX = ($image_width - $waterWidth) / 2;
					$posY = $image_height - $waterHeight/2;
					break;
					case 9://9为底端居右
					$posX = $image_width - $waterWidth ;
					$posY = $image_height - $waterHeight/2 ;
					break;
					default://随机
					$posX = rand(0, ($image_width - $waterWidth));
					$posY = rand(0, ($image_height - $waterHeight));
					break;
				}
			} else {
				//for  imagestring 和图片水印
				switch ($pos) {
					case 0://随机
					$posX = rand(0, ($image_width - $waterWidth));
					$posY = rand(0, ($image_height - $waterHeight));
					break;
					case 1://1为顶端居左
					$posX = 0;
					$posY = 0 ;
					break;
					case 2://2为顶端居中
					$posX = ($image_width - $waterWidth) / 2;
					$posY = 0 ;
					break;
					case 3://3为顶端居右
					$posX = $image_width - $waterWidth;
					$posY = 0 ;
					break;
					case 4://4为中部居左
					$posX = 0;
					$posY = ($image_height - $waterHeight) / 2;
					break;
					case 5://5为中部居中
					$posX = ($image_width - $waterWidth) / 2;
					$posY = ($image_height - $waterHeight) / 2;
					break;
					case 6://6为中部居右
					$posX = $image_width - $waterWidth;
					$posY = ($image_height - $waterHeight) / 2;
					break;
					case 7://7为底端居左
					$posX = 0;
					$posY = $image_height - $waterHeight;
					break;
					case 8://8为底端居中
					$posX = ($image_width - $waterWidth) / 2;
					$posY = $image_height - $waterHeight;
					break;
					case 9://9为底端居右
					$posX = $image_width - $waterWidth - 20;
					$posY = $image_height - $waterHeight - 10;
					break;
					default://随机
					$posX = rand(0, ($image_width - $waterWidth));
					$posY = rand(0, ($image_height - $waterHeight));
					break;
				}
			}
		}

		imagealphablending($this->imageResource, true);
		if ($isWaterImage) {
			imagecopy($this->newResource, $waterImgRes, $posX, $posY, 0, 0, $waterWidth, $waterHeight);
		} else {
			$R = hexdec(substr($textColor, 1, 2));
			$G = hexdec(substr($textColor, 3, 2));
			$B = hexdec(substr($textColor, 5));

			$textColor = imagecolorallocate($this->newResource, $R, $G, $B);
			//imagestring($this->newResource, $textFont=5, $posX, $posY, $waterText, $textColor);
			imagettftext($this->newResource, $fontSize, 0, $posX, $posY, $textColor, $fontType, $waterText);

			//imagettftext ($this->imageResource, float $size , float $angle , int $x , int $y , int $color , string $fontfile , string $text )
			//array imagettftext ( resource $image , float $size , float $angle , int $x , int $y , int $color , string $fontfile , string $text )
		}
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
	public function captch($width, $height, $length = 4, $validType = 1, $textColor = '#000000', $backgroundColor = '#ffffff') {
		if ($validType == 1) {
			//$validString = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
			//$validLength = 52;
			//no i no l
			$validString = 'abcdefghjkmnopqrstuvwxyzABCDEFGHJKMNOPQRSTUVWXYZ';
			$validLength = 48;
		} elseif ($validType == 2) {
			//$validString = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
			//$validLength = 62;
			//no i no l no 1
			$validString = '023456789abcdefghjkmnopqrstuvwxyzABCDEFGHJKMNOPQRSTUVWXYZ';
			$validLength = 57;
		} else {
			$validString = '0123456789';
			$validLength = 10;
		}

		srand((int) time());
		$valid = '';
		for ($i = 0; $i < $length; $i++) {
			$valid .= $validString{rand(0, $validLength - 1)};
		}

		$this->newResource = imagecreate($width, $height);
		$bgR = hexdec(substr($backgroundColor, 1, 2));
		$bgG = hexdec(substr($backgroundColor, 3, 2));
		$bgB = hexdec(substr($backgroundColor, 5, 2));
		$backgroundColor = imagecolorallocate($this->newResource, $bgR, $bgG, $bgB);
		$white = ImageColorAllocate($this->newResource, 155, 155, 155);
		$tR = hexdec(substr($textColor, 1, 2));
		$tG = hexdec(substr($textColor, 3, 2));
		$tB = hexdec(substr($textColor, 5, 2));
		$textColor = imagecolorallocate($this->newResource, $tR, $tG, $tB);
		for ($i = 0; $i < strlen($valid); $i++) {
			imagestring($this->newResource, 5, $i * $width / $length + 3, 2, $valid[$i], $textColor);
		}

		//加入干扰线
		//int imageline(int im, int x1, int y1, int x2, int y2, int col);
		for ($i = 0; $i < rand(1, 2); $i++) {
			//imageline($this->newResource, rand(1, $width), rand(1, $height), rand(1, $width), rand(1, $height), $white);
		}

		for ($i = 0; $i < rand(1, 2); $i++) {
			//imageline($this->newResource, rand(1, $width), rand(1, $height), rand(1, $width), rand(1, $height), $white);
		}

		for ($i = 0; $i < 100; $i++) {   //加入干扰象素
			$randcolor = ImageColorallocate($img, rand(0, 255), rand(0, 255), rand(0, 255));
			imagesetpixel($img, rand() % 100, rand() % 50, $white);
			imagesetpixel($this->newResource, rand(1, $width), rand(1, $height), $white);
		}

		$this->newResType = IMAGETYPE_JPEG;
		return $valid;
	}

	/**
     * 显示输出图像
     * @return void
     */
	public function display($fileName = null, $quality = 75) {

		$imgType = $this->newResType;
		$imageSrc = $this->newResource;
		if (empty($fileName)) {
			$fileName = null;
		}

		switch ($imgType) {

			case IMAGETYPE_GIF:
				if (! $fileName) {
					header('Content-type: image/gif');
				}
				imagegif($imageSrc, $fileName, $quality);
				break;
			case IMAGETYPE_JPEG:

				if (! $fileName) {
					header('Content-type: image/jpeg');
				}

				imagejpeg($imageSrc, $fileName, $quality);
				break;
			case IMAGETYPE_PNG:
				if (! $fileName) {
					header('Content-type: image/png');
					imagepng($imageSrc);
				} else {
					imagepng($imageSrc, $fileName);
				}
				break;
			case IMAGETYPE_WBMP:
				if (!$fileName) {
					header('Content-type: image/wbmp');
				}
				imagewbmp($imageSrc, $fileName, $quality);
				break;
			case IMAGETYPE_XBM:
				if (!$fileName) {
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
     * @param int $fileNameType 文件名类型 0使用原文件名，1使用指定的文件名，2在原文件名加上后缀，3产生随机文件名, 用原文件名
     * @param string $folder 文件夹路径 为空为与原文件相同
     * @param string $param 参数$fileNameType为2时为文件名加后缀
     * @return void
     */
	public function save($fileNameType = 0, $folder = NULL, $param = '_miniature', $quality=75) {
		if ($folder == NULL) {
			$folder = dirname($this->fileName) . DIRECTORY_SEPARATOR;     //如果文件夹为空用当前文件夹
		}
		$path_info = pathinfo($this->fileName);


		$fileBasicName = $path_info['filename'];
		$fileExtName   = '.' . strtolower( $path_info['extension']);

		switch ($fileNameType) {

			case 1:
				//文件夹可变的原图
				$newFileName = $folder . basename($this->fileName);

				break;

			case 2:
				//参数图
				$newFileName = $folder . $fileBasicName . $param . $fileExtName;
				break;

			case 3:
				//随机图
				$tmp = date('YmdHis');
				$fileBasicName = $tmp;
				$i = 0;
				while (file_exists($folder . $fileBasicName . $fileExtName)) {
					$fileBasicName = $tmp . $i;
					$i++;
				}
				$newFileName = $folder . $fileBasicName . $fileExtName;
				break;

			case 4:
				//原图
				$newFileName = $this->fileName;
				break;
			default:
				//自定义
				$fileNameType = basename($fileNameType);
				$newFileName  = $folder . $fileNameType . $fileExtName;
				break;
		}
		$this->display($newFileName, $quality);
		return $newFileName;
	}

	/**
     * 剪切出选定区域
     *
     * @param string $srcimgurl  
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
	public function cutimg($srcimgurl, $endimgurl, $x, $y, $endimg_w, $endimg_h, $border_w, $border_h, $scale = 100, $fix = 0) {
		$path = dirname($endimgurl);
		if (!is_dir($path)) {
			if (!@mkdir($path, 0777)) {
				die("{$path} 此目录不能创建,文件创建失败");
			}
		}
		$ground_info = getimagesize($srcimgurl);
		switch ($ground_info[2]) {
			case 1:$im = imagecreatefromgif($srcimgurl);
			break;
			case 2:$im = imagecreatefromjpeg($srcimgurl);
			break;
			case 3:$im = imagecreatefrompng($srcimgurl);
			break;
			default:die("图片格式不允许$srcimgurl");
		}

		if ($fix) {//方便截取头像的一部分
			if ($ground_info[0] < $ground_info[1]) {//宽小于高， 竖向
				$border_w = $ground_info[0];
				$border_h = $endimg_h * $ground_info[0] / $endimg_w;
			} elseif ($ground_info[0] > $ground_info[1]) { //横向
				$border_w = $endimg_w * $ground_info[1] / $endimg_h;
				$border_h = $ground_info[1];
			} else { //正方形
				$border_w = $ground_info[0];
				$border_h = $ground_info[1];
			}
		}

		$newim = imagecreatetruecolor($endimg_w, $endimg_h);
		$x = ($x * 100) / $scale;
		$y = ($y * 100) / $scale;
		$border_width = ($border_w * 100) / $scale;
		$border_height = ($border_h * 100) / $scale;

		imagecopyresampled($newim, $im, 0, 0, $x, $y, $endimg_w, $endimg_h, $border_width, $border_height);
		//bool imagecopyresampled ( resource $dst_image , resource $src_image , int $dst_x , int $dst_y , int $src_x , int $src_y , int $dst_w , int $dst_h , int $src_w , int $src_h )

		if (function_exists("imagegif")) {
			switch ($ground_info[2]) {
				case 1:imagegif($newim, $endimgurl);
				break;
				case 2:imagejpeg($newim, $endimgurl);
				break;
				case 3:imagepng($newim, $endimgurl);
				break;
				default:die("errorMsg");
			}
		} elseif (function_exists("imagejpeg")) {
			imagejpeg($newim, $endimgurl);
		} else {
			imagepng($newim, $endimgurl);
		}
		imagedestroy($newim);
		imagedestroy($im);
	}

	/**
     * 用于显示缩略图
     *
     * @access  public
     * @param   string      $img    原始图片的路径
     * @param   string      $img    新图片名称
     * @param   int         $thumb_width  缩略图宽度
     * @param   int         $thumb_height 缩略图高度
     * @param   strint      $path         指定生成图片的目录名
     * @return  mix         如果成功返回缩略图的路径，失败则返回false
     */

	static function thumb($srcImage, $thumb_width = 0, $thumb_height = 0, $flag = 1) {
		$IMG = new ImageFM();
		$IMG->createImageFromFile($srcImage);
		$IMG->resize($thumb_width, $thumb_height, $flag);
		$IMG->display();
		unset($IMG);
	}


	/**
	 * 图像旋转
	 *
	 * @param unknown_type $angle : 90, 180, 270, 'vrt', 'hor'
	 * @param unknown_type $fileName
	 * @return unknown
	 */
	function rotate( $angle = 0, $fileName = null) {

		// Allowed rotation values
		$degs = array(90, 180, 270, 'vrt', 'hor');     //可以支持其他，但需添加，会有背景画布

		if ($angle == '' OR ! in_array($angle, $degs))
		{
			throw new \Exception('Rotate 参数不合法');
		}

		if (!empty($fileName)) {
			//生成新资源, 直接反转
			$this->fileName = $fileName;
		}
		if 	(!$this->newResource) {
			$this->imageResource = $this->createImageFromFile();
		}
		$this->newResType = $this->imageType;

		//  Rotate it!
		//原资源不存在
		if ($this->newResource) {
			//如果已缩放
			$white	= imagecolorallocate($this->newResource, 255, 255, 255);
			$this->newResource = imagerotate($this->newResource, $angle, $white);
			return 2;
		} else if($this->imageResource) {
			$white	= imagecolorallocate($this->imageResource, 255, 255, 255);
			$this->imageResource = imagerotate($this->imageResource, $angle, $white);
			$this->newResource =  $this->imageResource;
			if ($angle == 90 OR $angle == 270)
			{
				$orig_width = $this->imageWidth;
				$this->imageWidth	= $this->imageHeight;
				$this->imageHeight	= $orig_width;
			}
			else
			{
				// $this->width	= $this->orig_width;
				// $this->height	= $this->orig_height;
			}
			return 1;
		} else {
			return 0;
		}

		// Reassign the width and height
		/*
		if ($this->rotation_angle == 90 OR $this->rotation_angle == 270)
		{
		$this->width	= $this->orig_height;
		$this->height	= $this->orig_width;
		}
		else
		{
		$this->width	= $this->orig_width;
		$this->height	= $this->orig_height;
		}
		*/

		//imagedestroy($src_img);
		//@chmod($this->full_dst_path, FILE_WRITE_MODE);		// Set the file to 777
	}

	function explode_name($source_image)
	{
		$ext = strrchr($source_image, '.');
		$name = ($ext === FALSE) ? $source_image : substr($source_image, 0, -strlen($ext));
		return array('ext' => $ext, 'name' => $name);
	}
	
	function destroy() {

	}
}