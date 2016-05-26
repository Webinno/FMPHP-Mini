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


class Input {

	public $ip_address	= FALSE;
	public $user_agent	= FALSE;
	public $_enable_xss	= FALSE;
	public $_enable_csrf= FALSE;
	protected $headers	= array();
	protected $vars	= array();

	function __construct()
	{

	}

	function get($key = null)
	{
		if(isset($key)) {
			return isset($_GET[$key]) ? $_GET[$key] : NULL;
		} else {
			return $_GET;
		}
	}

	function post($key)
	{
		return isset($_POST[$key]) ? $_POST[$key] : NULL;
	}

	function request($key)
	{
		return isset($_REQUEST[$key]) ? $_REQUEST[$key] : NULL;
	}

	function cookie($key)
	{
		return isset($_COOKIE[$key]) ? $_COOKIE[$key] : NULL;
	}

	function get_gpc($key, $var='R')
	{
		switch($var) {
			case 'G': $var = & $_GET; break;
			case 'P': $var = & $_POST; break;
			case 'C': $var = & $_COOKIE; break;
			case 'R': $var = & $_REQUEST; break;
		}
		return isset($var[$k]) ? $var[$k] : NULL;
	}

	function set_param($key, $val)
	{
		$this->vars[key] = $val;
		return $this;
	}

	function get_param($key)
	{
		return isset($this->vars[key]) ? $this->vars[key] : null;
	}

	function param($key)
	{

	}

	function is_post()
	{

	}

	function is_get()
	{

	}
	//是否ajax请求
	function is_ajax_request()
	{
		return ($_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest');
	}

	//待测试， 是否命令行请求
	function is_cli_request() {
		return (bool) defined('STDIN');
	}
}
// --------------------------------------------------------------------