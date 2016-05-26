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
 * 访问控制
 * @author Eiwen
 * $urldata 访问的url例外
 * $dirdata 是访问url文件所在上一级目录
 */
class Visitcontrol {
	//允许直接访问列表
	private  $urldata = array(
	'/login/dologin.php',

	);

	//允许直接访问目录， 仅所有文件均可访问时才加该项
	private $dirdata = array(
	'/zhuanti/aaa/'
	);

	//重定向过滤： 允许的外部url，才能跳转
	private $_allow_redirect_urls = array(
	'http://www.webinno.cn/'
	);


	//访问时间控制   5秒限制访问10次
	private function visit(){
		$isarray = array(
		'/fund/interface/'
		);
		$allow_sep = 5; //时间数
		$post_num  = 10; //次数

		//是否在允许目录内
		foreach ($isarray as $i){
			if (strpos($_SERVER['PHP_SELF'], $i) !== false) {    //查看是否等于0
				return true;
			}
		}

		if (isset($_SESSION["post_sep"])) {
			//是否5秒内
			if (time() - $_SESSION["post_sep"] < $allow_sep) {
				if (isset($_SESSION["post_num"])) {
					if ($_SESSION["post_num"] > $post_num) {
						ob_start(); //这一段是防止已输出的错误
						header('HTTP/1.1 503 Service Temporarily Unavailable');
						header('Status: 503 Service Temporarily Unavailable');
						header('Retry-After:1200'); //通知搜索引擎改日再来
						exit('小伙子不用撸这么快，有伤身体！');
					}else {
						$_SESSION["post_num"] = $_SESSION["post_num"] + 1;
					}
				}else {
					$_SESSION["post_num"] = 0;
				}
			}else {
				$_SESSION["post_sep"] = time();
				$_SESSION["post_num"] = 0;
			}
		}else {
			$_SESSION["post_sep"] = time();
			$_SESSION["post_num"] = 0;
		}
	}

	function islogin(){
		//是否在允许目录
		foreach ($this->dirdata as $i){
			if (strpos($_SERVER['PHP_SELF'], $i) !== false) {    //查看是否等于0
				$this->visit();
				return true;
			}
		}
		//查看是否在允许文件列表
		if (in_array($_SERVER['PHP_SELF'], $this->urldata)) {
			$this->visit();
			return true;
		}
		$url = substr($_SERVER['PHP_SELF'], 0, -3)."do";
		// 查看是否登录
		if (empty($_SESSION['LOGIN_COOKIE'])) {
			head_jump('/?return='.$url);
			exit();
		}
		return true;
	}

	function get_allow_redirect_urls (){
		return $this->_allow_redirect_urls;
	}
}