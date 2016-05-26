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


/**
 * Enter description here...
 * @name 
 * 功能：Curl基础类库封装
 * Author: Tom(fxs_2008@sina.com)
 * Create: 
 * Last modified:
 */

class Http {
	const EXCL_HEAD = TRUE; //不含http头
	const INCL_HEAD = TRUE;  //含http头
	const CURL_TIMEOUT = 0;

	/**
	 * @name http_get
	 * @return json
	 * 功能：不包含HTTP头的GET请求
	 */
	static function http_get($target,$data_array = "") {
		return self::http($target, $method = "GET", $data_array, self::EXCL_HEAD);    //排除
	}

	/**
	 * @name http_get_withheader
	 * @return type json
	 * 功能：包含HTTP头的GET请求
	 */
	static function http_get_withheader($target, $data_array='') {
		return self::http($target, $method = "GET", $data_array, self::INCL_HEAD);
	}

	/**
	 * @name http_post_withheader
	 * @return type json
	 * 功能：包含HTTP头的POST请求
	 */
	static function http_post($target, $data_array = "") {
		return self::http($target, $method = "POST", $data_array, self::EXCL_HEAD);
	}

	/**
	 * @name http_post_withheader
	 * @return type json
	 * 功能：包含HTTP头的POST请求
	 */
	static function http_post_withheader($target, $data_array = "") {
		return self::http($target, $method = "POST", $data_array, self::INCL_HEAD);
	}

	/**
	 * @name http_post_form
	 * @return type json
	 * 功能：表单上传
	 */
	static function http_post_form($target, $data_array = "") {
		return self::http($target, $method = "POST", $data_array, self::EXCL_HEAD);
	}

	/**
	 * @name http_post_form
	 * @return type json
	 * 功能：HTTP头信息请求
	 */
	static  function http_header($target){
		return self::http($target, $method = "HEAD", $data_array = "", self::INCL_HEAD);
	}

	static  function http($target, $method, $data_array, $flag, $is_login = false){
		$message = array();

		$client_ip = Func::getIP();
		$for_ip = getenv("HTTP_X_FORWARDED_FOR");

		$cookie_core_sys = App::session()->get('LOGIN_COOKIE');   // session中取登录名

		$ch = curl_init();
		$query_string = '';

		$user_name =  empty($_SESSION['username']) ? 'No Login' : $_SESSION['username'];
		$message['datetime']    = date('Y-m-d H:i:s') . ' | ' . $client_ip  . ' | ' . $user_name;
		$message['php_uri']     = $_SERVER['REQUEST_URI'];
		$message['php_uri_ref'] = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';

		if (is_array($data_array)){
			$query_string = http_build_query($data_array);
			$message['curl_uri'] = $target . '?' . $query_string;
		} else {
			$message['curl_uri'] = $target . '?' .  $data_array;
		}
		$message['method'] = $method;
		# HEAD method configuration
		if($method == 'HEAD'){
			curl_setopt($ch, CURLOPT_HEADER, TRUE);                           // No http head
			curl_setopt($ch, CURLOPT_NOBODY, TRUE);                           // Return body， 关闭body
		}else{
			# GET method configuration
			if($method == 'GET') {
				if (isset($query_string))
				$target = $target . "?" . $query_string;
				curl_setopt($ch, CURLOPT_HTTPGET, TRUE);
				curl_setopt($ch, CURLOPT_POST, FALSE);
			}
			# POST method configuration
			if($method == 'POST') {
				if (isset($query_string))
				curl_setopt($ch, CURLOPT_POSTFIELDS, $query_string);
				curl_setopt($ch, CURLOPT_POST, TRUE);
				curl_setopt($ch, CURLOPT_HTTPGET, FALSE);
			}
		}

		$ip_arr = array("X-FORWARDED-FOR:" . $for_ip, "CLIENT-IP:" . $client_ip);

		curl_setopt($ch, CURLOPT_AUTOREFERER, 0);
		//curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file_path);
		//curl_setopt($ch, CURLOPT_COOKIEJAR,  $cookie_file_path);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $ip_arr);//IP
		curl_setopt($ch, CURLOPT_COOKIE, $cookie_core_sys);
		curl_setopt($ch, CURLOPT_HEADER, TRUE);                               // Include head as needed
		curl_setopt($ch, CURLOPT_NOBODY, FALSE);                              // Return body
		curl_setopt($ch, CURLOPT_TIMEOUT, self::CURL_TIMEOUT);                // Timeout
		curl_setopt($ch, CURLOPT_URL, $target);                               // Target site
		curl_setopt($ch, CURLOPT_VERBOSE, FALSE);                             // Minimize logs
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);                      // No certificate
		//if (ini_get('open_basedir') == '' && ini_get('safe_mode' == 'Off')){
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);                    // 使用自动跳转
		//}
		curl_setopt($ch, CURLOPT_MAXREDIRS, 4);                               // Limit redirections to four
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);                       // Return in string

		curl_setopt($ch, CURLINFO_HEADER_OUT, TRUE);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Length: ' . strlen($query_string)));
		# Create return array
		$data = curl_exec($ch);

		//$b = curl_getinfo($ch,CURLINFO_HEADER_OUT );
		//print_r($b);

		$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		$http_code   = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		$return_array['HEADER'] = substr($data, 0, $header_size);
		$return_array['FILE']   = substr($data, $header_size );
		$return_array['STATUS'] = curl_getinfo($ch);
		$return_array['ERROR']  = curl_error($ch);

		curl_close($ch);

		//curl错误日志
		/*
		if (!empty($return_array['ERROR'])) {
		Func::error_log($return_array, 'Curl_error');
		}
		*/

		$message['http_code']  = $http_code;
		$message['curl_error'] = $return_array['ERROR'];
		$message['result']  = json_decode($return_array['FILE'], true);

		$return_array['INFO'] = $message;

		//$message['info']['request_header'] = $b;
		//$message['info']['desc']    = $return_array;


		Func::debug($return_array);    //调试时输出变量


		if($http_code == 302){//302后从新请求
			Security::session_destroy();
			Func::error_log($return_array, '302');    //302日志
			head_jump('/');
			exit;
		}else{
			$is_se_login = json_decode($return_array['FILE'],true);

			if($is_se_login['result'] == '999'){//为999时已经退出
				Security::session_destroy();
				if(self::isAjax()){
					if(is_array($data_array)&&isset($data_array['j_username'])){
						$string_lo = $_SERVER["QUERY_STRING"];
						if($string_lo == 'r=public/dologin&username='.$data_array['j_username']){
							return $return_array;
						}else{
							exit('<div style="display:none;"><script> window.location.href("/index.php?r=public/login"); </script></div>');
							//exit('<div style="display:none;"><script> window.location.reload(); </script></div>');
						}
					}else{
						return $return_array;
					}
				}else{
					//跳到登录页
					//Yii::app()->runController('public/Login');exit;
					//exit('<div style="display:none;"><script> window.location.href("/index.php"); </script></div>');
					head_jump('/');
					exit;
				}
			}else{
				if ($http_code != 200) { //例外处理

					Func::error_log($return_array, 'Java_error');    //Java日志
					if(self::isAjax()){
						echo '纳尼！怎么出错了！';
						//P($_SERVER);
					}else {
						head_jump('/error/500.do');
					}
					exit;
				}
				//处理成功日志
				//Func::error_log($message, 'op');
				return $return_array;
			}
		}
		exit;
	}

	static function isAjax() {
		if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) ) {
			if('xmlhttprequest' == strtolower($_SERVER['HTTP_X_REQUESTED_WITH']))
			return true;
		}
		if(!empty($_POST['ajax']) || !empty($_GET['ajax'])) {
			// 判断Ajax方式提交
			return true;
		}
		return false;
	}

	//统一登陆
	function http_login($target, $data_array) {
		return self::http($target, $method = "POST" , $data_array, self::INCL_HEAD, True);
	}

	private function http_test($data_array) {
		// $url_main  = Yii::app()->params['mainUrl'];
		$result_main = self::http_login_mand($url_main);   //登录首页
		preg_match_all('|Set-Cookie: (.*);|U', $result_main['HEADER'], $matches);
		$cookies = implode('; ', $matches[1]);
		Yii::app()->session['LOGIN_COOKIE'] = $cookies.';'.Yii::app()->session['LOGIN_LTPA'];
		self::http($url_com.'j_security_check', $method = "POST", $data_array);
	}

	private function http_login_mand($url) {
		$query_string = '';
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_COOKIE, Yii::app()->session['LOGIN_COOKIE']);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $query_string);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_HTTPGET, false);
		curl_setopt($curl, CURLOPT_HEADER, true);   // Include head as needed
		curl_setopt($curl, CURLOPT_NOBODY, true);  // Return body

		$data = curl_exec($curl);
		$header_size = curl_getinfo($curl,CURLINFO_HEADER_SIZE);
		$resulta['HEADER'] = substr($data, 0, $header_size);
		$resulta['FILE']   = substr($data, $header_size );
		$resulta['STATUS'] = curl_getinfo($curl);
		$resulta['ERROR']  = curl_error($curl);
		return $resulta;
	}
}



/*
//
if($http_code == 302){//链接2个cookie
//$is_login = strpos($target, "toLogin");
//if($is_login !== false){
preg_match_all('|Set-Cookie: (.*);|U', $return_array['HEADER'], $matches);
$cookies = implode('; ', $matches[1]);
if($comm !== false){
Yii::app()->session['LOGIN_COMM'] = $cookies.'; '.Yii::app()->session['LOGIN_COMM'];
}else{
Yii::app()->session['LOGIN_COOKIE'] = $cookies.'; '.Yii::app()->session['LOGIN_COOKIE'];
self::http_test($data_array);
}
}
//
if($return_array['STATUS']['redirect_url'] == ''){
preg_match('/Location:(.*?)\n/', $return_array['HEADER'], $url_matches);
$url = trim(array_pop($url_matches));
return self::http($url, $method = "POST" , $data_array);exit;
}else{
return self::http($return_array['STATUS']['redirect_url'], $method = "POST" , $data_array, 'GET');exit;
}
*/