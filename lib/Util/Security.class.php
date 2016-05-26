<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

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
 * Security Class
 * Tom
 * @link	
 */
class Util_Security {

	//禁止高速缓存
	static function no_cache () {
		header("Cache-Control: no-cache, no-store, must-revalidate");
		header("Pragma: no-cache");
		header("Expires: 0");
	}

	//重定向
	static  function redirect_filter ($url) {
		//删除不可见字符
		$url = self::remove_invisible_characters($url);

		//删除换行空格
		$url = str_replace ( array (
		"\n",
		"\r"
		), '', $url );

		$allow_urls = App::visit_control()->get_allow_redirect_urls ();
		//域内绝对路径
		if (in_array($url, $allow_urls)) {
			//外域
			return $url;
		}
		//删除重复的"/"或"\"
		$url = preg_replace("#\\\\+#", "\\\\", $url);
		$url = preg_replace("#/+#", "/", $url);
		if (strpos($url, "/") === 0 || strpos($url, "./") === 0 )
		{
			return $url;
		}
		else
		{
			die("不允许操作！");
		}
		//域外目录， 白名单制
	}

	//全局http_only 需要在session_start之前调用, 0 关闭, 1打开
	static function http_only ($flag = 1) {
		ini_set("session.cookie_httponly",$flag);
		//或者setcookie()的第七个参数设置为true
		//session_set_cookie_params(0, NULL, NULL, NULL, TRUE);
	}

	//全局 需要在session_start之前调用; 0 关闭, 1打开
	static function set_cookie_secure ($flag = 1) {
		ini_set("session.cookie_secure", $flag);
	}


	//拒绝被嵌入框架(iframe…)
	static function set_xFrameOptions ($val = 'SAMEORIGIN' ) {
		if (in_array($val, array('DENY', 'SAMEORIGIN', 'ALLOW-FROM'))) {
			header("X-Frame-Options:" . $val);
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Remove Invisible Characters
	 *
	 * This prevents sandwiching null characters
	 * between ascii characters, like Java\0script.
	 * from ci框架
	 * @param	string
	 * @param	bool
	 * @return	string
	 */
	static function remove_invisible_characters($str, $url_encoded = TRUE)
	{
		$non_displayables = array();

		// every control character except newline (dec 10),
		// carriage return (dec 13) and horizontal tab (dec 09)
		if ($url_encoded)
		{
			$non_displayables[] = '/%0[0-8bcef]/';	// url encoded 00-08, 11, 12, 14, 15
			$non_displayables[] = '/%1[0-9a-f]/';	// url encoded 16-31
		}

		$non_displayables[] = '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S';	// 00-08, 11, 12, 14-31, 127

		do
		{
			$str = preg_replace($non_displayables, '', $str, -1, $count);
		}
		while ($count);

		return $str;
	}

	static function session_destroy() {
		// 重置会话中的所有变量
		$_SESSION = array();
		// 如果要清理的更彻底，那么同时删除会话 cookie
		// 注意：这样不但销毁了会话中的数据，还同时销毁了会话本身
		if (ini_get("session.use_cookies")) {
			$params = session_get_cookie_params();
			setcookie(session_name(), '', time() - 42000,
			$params["path"], $params["domain"],
			$params["secure"], $params["httponly"]
			);
		}
		// 最后，销毁会话
		session_destroy();
	}
}
// END Security Class

/* End of file Security.php */
/* Location: */