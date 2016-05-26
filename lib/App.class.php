<?php      if ( ! defined('BASEPATH')) exit('No direct script access allowed');

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

class App {
	//注册input类
	static	function input () {
		static $input = null;
		if (is_object($input)) {
			return $input;
		} else {
			$input = new Input();
			return $input;
		}
	}
	//注册session类
	static	function session () {
		static $session = null;
		if (is_object($session)) {
			return $session;
		} else {
			$session = new Session();
			return $session;
		}
	}

	//注册session类
	static	function visit_control () {
			static $obj = null;
		if (is_object($obj)) {
			return $obj;
		} else {
			$obj = 	new Visitcontrol();
			return $obj;
		}
	}
}



