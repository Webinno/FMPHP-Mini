<?php
/**
 * 验证码
 */

require ('../init.inc.php');

/*
//生成随机数
$rand = '';
for($i=0;$i<4;$i++){

	$rand.=dechex(rand(1,15));
}

//创建图片
$img=imagecreate(80,30); //100是宽，30是高
//设置颜色
$black = imagecolorallocate($img, 255, 255, 255);//0,0,0代表RGB值，当第一次对图片赋值时，设置背景颜色
$white = imagecolorallocate($img, 0, 0, 255);
//加干扰线
for($i=0;$i<4;$i++){
	$co = imagecolorallocate($img,rand(0,255),rand(0,255),rand(0,255));
	imageline($img,rand(5,78),rand(1,29),rand(5,78),rand(1,29),$co);//画线函数
}
// 加噪点
for($i=0;$i<50;$i++){
	imagesetpixel($img,rand(5,78),rand(1,29),$co); //画点函数
}
imagestring($img,rand(1,6),rand(1,55),rand(1,10),$rand,$white);
//设置头部信息（输出格式）
header("Content-type:image/jpeg");
//转换编码格式
imagejpeg($img);
//把随机数写入session，用于和提交页面传递过来的值对比
$_SESSION['code']=$rand;
*/

$config =	array(
		'seKey'     =>  'LIFANGTONG',   // 验证码加密密钥
		'codeSet'   =>  '2345678abcdefhijkmnpqrstuvwxyzABCDEFGHJKLMNPQRTUVWXY',             // 验证码字符集合
		'expire'    =>  1800,            // 验证码过期时间（s）
		'useZh'     =>  false,           // 使用中文验证码
		'useImgBg'  =>  false,           // 使用背景图片
		'fontSize'  =>  25,              // 验证码字体大小(px)
		'useCurve'  =>  false,            // 是否画混淆曲线
		'useNoise'  =>  true,            // 是否添加杂点
		'imageH'    =>  0,               // 验证码图片高度
		'imageW'    =>  0,               // 验证码图片宽度
		'length'    =>  4,               // 验证码位数
		'fontttf'   =>  '',              // 验证码字体，不设置随机获取
		'bg'        =>  array(243, 251, 254),  // 背景颜色
		'reset'     =>  true,           // 验证成功后是否重置
);
$Verify = new Verify($config);
$Verify->entry();
