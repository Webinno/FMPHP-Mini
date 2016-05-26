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


//开启错误级别//
error_reporting(E_ALL ^ E_NOTICE);
ini_set( 'display_errors', 0);    // or ini_set( 'display_errors', "0n" );

if(!defined('ROOT'))
{
	define('ROOT', dirname(__FILE__) . '/');
}
define('BASEPATH', 'aaaa');

date_default_timezone_set("prc") ;
unset($GLOBALS, $_ENV, $HTTP_GET_VARS, $HTTP_POST_VARS, $HTTP_COOKIE_VARS, $HTTP_SERVER_VARS, $HTTP_ENV_VARS);
ini_set('magic_quotes_runtime', 0);    // Kill magic quotes

//安全配置
/*
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
header("X-Frame-Options:SAMEORIGIN");
*/
ini_set("session.cookie_httponly", 1);
ini_set("session.use_trans_sid", 0);
//ini_set("session.cookie_secure", 1);
		

require_once(ROOT . '/config/config.inc.php');
require_once(ROOT . '/lib/Core.class.php');
// 开启session
session_start();
//包含版本文件
require_once(ROOT . 'version.php');

header("Content-type:text/html;charset=utf-8");

if(!function_exists('autoload'))
{
	function __autoload($classname)
	{
		$filename = str_replace('_','/',$classname) . '.class.php';
		$filepath = CLS . $filename;
		if(file_exists($filepath))
		{
			return require($filepath);
		}
		$filepath = LIB . $filename;
		if(file_exists($filepath))
		{
			return require($filepath);
		}
		$filepath = WEBROOT . $filename;
		if(file_exists($filepath))
		{
			return require($filepath);
		}
	}
}
spl_autoload_register('__autoload');


//防钓鱼标签

//访问控制
$a = new Visitcontrol();
$a->islogin();


//注册 smarty 类
/*
$smarty = Template::GetTemplate();
$smarty->register_function('aaaa', 'aaaa');
*/

