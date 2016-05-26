<?php       if ( ! defined('BASEPATH')) exit('No direct script access allowed');

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
 * Enter description here...
 * Author: Tom(fxs_2008@sina.com)
 * Create: 
 * Last modified:
 */

class Session {
	function __construct() {
		if (!session_id()) {
			session_start();
		}
	}

	function set ($key, $val) {
		if(!empty($key)) {
			$_SESSION[$key] = $val;
		}
	}

	function get ($key = null) {
		if(!empty($key)) {
			$rtn = isset($_SESSION[$key]) ? $_SESSION[$key] : null;
		}
		return $rtn ;
	}

	function get_all () {
		return $_SESSION;
	}

	function delete ($key) {
		if(!empty($key)) {
			if (isset($_SESSION[$key])) {
				unset ($_SESSION[$key]);
			}
		}
	}
}