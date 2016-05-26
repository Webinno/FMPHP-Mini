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

error_reporting(E_ALL);
ini_set("display_errors", 1);

header('Content-Type: text/javascript; charset=UTF-8');

// seconds, minutes, hours, days, years
$expires = 60*60*24*365*10;
header("Pragma: public");
header("Cache-Control: maxage=".$expires);
header('Expires: ' . gmdate('D, d M Y H:i:s', time()+$expires) . ' GMT');

$BaseIP = dirname( __FILE__ ).'/';
$paths = explode("&", $_SERVER['QUERY_STRING']);
//print_r($paths);
$js ='';
$defaultExt =array('js','css','jpg','tif','png');    // 允许合并的扩展名

foreach ( $paths as $key => $value){
	$value= preg_replace('/[.]{2,}/ism','',$value);    // 去除相对路径
	
	$subPath = $BaseIP . $value;
	$extend = pathinfo($value);
	$extend = isset($extend["extension"])? strtolower($extend["extension"]) : null;

	if( in_array($extend, $defaultExt) ){
		if( file_exists($subPath)){
			
			$file_content = file_get_contents($subPath);
			//chech the url path
			//$file_base    = dirname($value);
			//$file_content = preg_replace("#url\(['\"]?(?!http:/|/)([^)'\"]+)['\"]?\)#ims", "url({$file_base}/$1)", $file_content );
			
			//chech the url path
			$js .=  "\n\n /*  File $key: $value  */\n\n" . $file_content ;
		}else{
			echo "/*  File $key: $value  error path  */\n" ;
		}
	}else{
		echo "/*  File $key: $value  forbid path  */\n" ;
	}
}

echo $js;

