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

class Func extends FM_Func{

	/**
 * 分页函数
 *
 * @param int $num:记录总数
 * @param int $perpage:每页记录
 * @param int $curpage:当前页
 * @param string $mpurl:路径url
 * @param unknown_type $hiddenFrom
 * @return string
 */
	static  function page($num, $perpage, $curpage, $mpurl, $hiddenFrom='') {
		//print_r(func_get_args());

		$multipage = '';
		//恢复
		$mpurl .= strpos($mpurl, '?') ? '&' : '?';

		if($num > $perpage) {
			$page = 10;
			$offset = 2;

			$pages = @ceil($num / $perpage);  //总页数

			if($page > $pages) {
				$from = 1;
				$to = $pages;
			} else {
				//如果总页数>10
				$from = $curpage - $offset;   //当前页-2
				$to = $from + $page - 1;    //当前页+10-3

				if($from < 1) {

					$to = $curpage + 1 - $from;
					$from = 1;
					if($to - $from < $page) {
						$to = $page;
					}
				} elseif($to > $pages) {
					$from = $pages - $page + 1;
					$to = $pages;
				}
			}

			if ($hiddenFrom) {
				$multipage = ($curpage - $offset > 1 && $pages > $page ? '<a href="#" '.js_page($hiddenFrom,1).'>首页</a> ' : '').
				($curpage > 1 ? ' <a href="#" '.js_page($hiddenFrom,$curpage - 1).'>‹‹上一页</a> ' : '');
				for($i = $from; $i <= $to; $i++) {
					$multipage .= $i == $curpage ? '<span>'.$i.'</span> ' :
					'<a href="#" '.js_page($hiddenFrom,$i).'>'.$i.'</a> ';
				}

				$multipage .= ($curpage < $pages ? '<a href="#" '.js_page($hiddenFrom,$curpage + 1).'>下一页››</a>' : '').
				($to < $pages ? '<a href="#" '.js_page($hiddenFrom,$pages).'> 末页</a>' : '');
				$multipage = $multipage ? '<div class="list-page">'.$multipage.'</div>' : '';
			} else {

				$multipage = ($curpage - $offset > 1 && $pages > $page ? '<a href="'.$mpurl.'page=1">首页</a> ' : '').
				($curpage > 1 ? ' <a href="'.$mpurl.'page='.($curpage - 1).'">‹‹上一页</a> ' : '');

				for($i = $from; $i <= $to; $i++) {
					$multipage .= $i == $curpage ? '<span>'.$i.'</span> ' :
					'<a href="'.$mpurl.'page='.$i.'">'.$i.'</a> ';
				}

				$multipage .= ($curpage < $pages ? '<a href="'.$mpurl.'page='.($curpage + 1).'">下一页››</a>' : '').
				($to < $pages ? '<a href="'.$mpurl.'page='.$pages.'"> 末页</a>' : '');
				$multipage = $multipage ? '<div class="list-page">'.$multipage.'</div>' : '';
			}
		}
		return $multipage;
	}

	//专用分页
	static function paginator ($num, $perpage, $curpage, $mpurl, $hiddenFrom='',$pageParams='') {
		//print_r(func_get_args());

		$multipage = '';
		//恢复
		$mpurl .= (strpos($mpurl, '?') !== false) ? '&' : '?';

		if($num > $perpage) {
			$page = 10;
			$offset = 2;

			$pages = @ceil($num / $perpage);  //总页数

			if($page > $pages) {
				$from = 1;
				$to = $pages;
			} else {
				//如果总页数>10
				$from = $curpage - $offset;   //当前页-2
				$to = $from + $page - 1;    //当前页+10-3

				if($from < 1) {

					$to = $curpage + 1 - $from;
					$from = 1;
					if($to - $from < $page) {
						$to = $page;
					}
				} elseif($to > $pages) {
					$from = $pages - $page + 1;
					$to = $pages;
				}
			}

			if ($hiddenFrom) {
				$multipage = ($curpage - $offset > 1 && $pages > $page ? '<a href="#" '.js_page($hiddenFrom,1).'>首页</a> ' : '').
				($curpage > 1 ? ' <a href="#" '.js_page($hiddenFrom,$curpage - 1).'>‹‹上一页</a> ' : '');
				for($i = $from; $i <= $to; $i++) {
					$multipage .= $i == $curpage ? '<span>'.$i.'</span> ' :
					'<a href="#" '.js_page($hiddenFrom,$i).'>'.$i.'</a> ';
				}

				$multipage .= ($curpage < $pages ? '<a href="#" '.js_page($hiddenFrom,$curpage + 1).'>下一页››</a>' : '').
				($to < $pages ? '<a href="#" '.js_page($hiddenFrom,$pages).'> 末页</a>' : '');
				$multipage = $multipage ? '<div class="list-page">'.$multipage.'</div>' : '';
			} else {

				$multipage = ($curpage - $offset > 1 && $pages > $page ? '<a href="'.$mpurl.$pageParams.'page=1">首页</a> ' : '').
				($curpage > 1 ? ' <span><a href="'.$mpurl.$pageParams.'page='.($curpage - 1).'">&lt;</a></span> ' : '');
				$multipage .= '<ul>';
				for($i = $from; $i <= $to; $i++) {
					$multipage .= $i == $curpage ? '<li class="active"><span>'.$i.'</span><li> ' :
					'<li><a href="'.$mpurl.$pageParams.'page='.$i.'">'.$i.'</a></li> ';
				}
				$multipage .= '</ul>';
				$multipage .= ($curpage < $pages ? '<span><a href="'.$mpurl.$pageParams.'page='.($curpage + 1).'">&gt;</a></span>' : '').
				($to < $pages ? '<a href="'.$mpurl.$pageParams.'page='.$pages.'"> 末页</a>' : '');

				$multipage .= '<p>共 ' . $pages . ' 页 到第<input type="text" class="pagenum" id="inputPageNum" />页<input type="button" class="pagebtn" value="确定" onclick="location.href=\''. $mpurl.$pageParams.'page=\'+document.getElementById(\'inputPageNum\').value;"/></p>';
				$multipage = $multipage ? '<div class="page">'.$multipage.'</div>' : '';
			}
		}
		return $multipage;
	}
    //只有上一页,下一页的分页
    static function posPaginator($curpage=1,$pageParams=''){
        $multipage = '';
        $curpage = (int)$curpage;
        $nextpage = $curpage+1;
        $prepage = $curpage-1;
        if($curpage > 1){
            $multipage .= '<span><a href="?'.$pageParams.'page='.$prepage.'" title="上一页"><<</a></span>';
        }
        $multipage .= '<span><a href="?'.$pageParams.'page='.$nextpage.'" title="下一页">>></a></span>';
        $multipage = '<div class="page">'.$multipage.'</div>';
        return $multipage;
    }
	/*

	<div class="page">
	<span> < </span>
	<ul>
	<li class="active"><a>1</a></li>
	<li><a>2</a></li>
	<li><a>3</a></li>
	<li><a>4</a></li>
	<li><a>5</a></li>
	</ul>
	<span> > </span>
	<p>共45页，到第<input type="text" class="pagenum" />页<input type="button" class="pagebtn" value="确定" /> </p>
	</div>

	*/
	/* by:Eiwen
	*
	* date:2014.11.06
	*/



	/**
	 * 判断是否SSL协议
	 * 
	 * @return boolean
	 */
	static function is_ssl() {
		if (isset ( $_SERVER ['HTTPS'] ) && ('1' == $_SERVER ['HTTPS'] || 'on' == strtolower ( $_SERVER ['HTTPS'] ))) {
			return true;
		} elseif (isset ( $_SERVER ['SERVER_PORT'] ) && ('443' == $_SERVER ['SERVER_PORT'])) {
			return true;
		}
		return false;
	}

	



	/**
	 * 操作错误跳转的快捷方法
	 * @access
	 * @param string $message错误信息
	 * @param string $jumpUrl页面跳转地址
	 * @param mixed $ajax是否为Ajax方式 当数字时指定跳转时间
	 * @return void
	 */
	static function error($message = '', $jumpUrl = '', $ajax = false) {
		//$this->dispatchJump ( $message, 0, $jumpUrl, $ajax );
	}

	/**
	 * 操作成功跳转的快捷方法
	 * @access
	 * @param string $message提示信息
	 * @param string $jumpUrl页面跳转地址
	 * @param mixed $ajax是否为Ajax方式 当数字时指定跳转时间
	 * @return void
	 */
	static  function success($message = '', $jumpUrl = '', $ajax = false) {
		//$this->dispatchJump ( $message, 1, $jumpUrl, $ajax );
	}

	/**
	 * Cookie 设置、获取、删除
	 * @param string $name cookie名称
	 * @param mixed $value cookie值
	 * @param mixed $options cookie参数
	 * @return mixed
	 */
	static  function cookie($name='', $value='', $option=null) {
		// 默认设置
		$config = array(
		'prefix'    =>  C('COOKIE_PREFIX'), // cookie 名称前缀
		'expire'    =>  C('COOKIE_EXPIRE'), // cookie 保存时间
		'path'      =>  C('COOKIE_PATH'), // cookie 保存路径
		'domain'    =>  C('COOKIE_DOMAIN'), // cookie 有效域名
		'httponly'  =>  C('COOKIE_HTTPONLY'), // httponly设置
		);
		// 参数设置(会覆盖黙认设置)
		if (!is_null($option)) {
			if (is_numeric($option))
			$option = array('expire' => $option);
			elseif (is_string($option))
			parse_str($option, $option);
			$config     = array_merge($config, array_change_key_case($option));
		}
		if(!empty($config['httponly'])){
			ini_set("session.cookie_httponly", 1);
		}
		// 清除指定前缀的所有cookie
		if (is_null($name)) {
			if (empty($_COOKIE))
			return;
			// 要删除的cookie前缀，不指定则删除config设置的指定前缀
			$prefix = empty($value) ? $config['prefix'] : $value;
			if (!empty($prefix)) {// 如果前缀为空字符串将不作处理直接返回
				foreach ($_COOKIE as $key => $val) {
					if (0 === stripos($key, $prefix)) {
						setcookie($key, '', time() - 3600, $config['path'], $config['domain']);
						unset($_COOKIE[$key]);
					}
				}
			}
			return;
		}elseif('' === $name){
			// 获取全部的cookie
			return $_COOKIE;
		}
		$name = $config['prefix'] . str_replace('.', '_', $name);
		if ('' === $value) {
			if(isset($_COOKIE[$name])){
				$value =    $_COOKIE[$name];
				if(0===strpos($value,'think:')){
					$value  =   substr($value,6);
					return array_map('urldecode',json_decode(MAGIC_QUOTES_GPC?stripslashes($value):$value,true));
				}else{
					return $value;
				}
			}else{
				return null;
			}
		} else {
			if (is_null($value)) {
				setcookie($name, '', time() - 3600, $config['path'], $config['domain']);
				unset($_COOKIE[$name]); // 删除指定cookie
			} else {
				// 设置cookie
				if(is_array($value)){
					$value  = 'think:'.json_encode(array_map('urlencode',$value));
				}
				$expire = !empty($config['expire']) ? time() + intval($config['expire']) : 0;
				setcookie($name, $value, $expire, $config['path'], $config['domain']);
				$_COOKIE[$name] = $value;
			}
		}
	}

	/**
	 * 获取客户端IP地址
	 * @param integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
	 * @param boolean $adv 是否进行高级模式获取（有可能被伪装）
	 * @return mixed
	 */
	static function get_client_ip() {
		if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown"))
			$ip = getenv("HTTP_CLIENT_IP");
		else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown"))
			$ip = getenv("HTTP_X_FORWARDED_FOR");
		else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
			$ip = getenv("REMOTE_ADDR");
        else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
            $ip = $_SERVER['REMOTE_ADDR'];
		else
			$ip = "unknown";
		return($ip);
	}

	/**
	 * 发送HTTP状态
	 * @param integer $code 状态码
	 * @return void
	 */
	static function send_http_status($code) {
		static $_status = array(
		// Success 2xx
		200 => 'OK',
		// Redirection 3xx
		301 => 'Moved Permanently',
		302 => 'Moved Temporarily ',  // 1.1
		// Client Error 4xx
		400 => 'Bad Request',
		403 => 'Forbidden',
		404 => 'Not Found',
		// Server Error 5xx
		500 => 'Internal Server Error',
		503 => 'Service Unavailable',
		);
		if(isset($_status[$code])) {
			header('HTTP/1.1 '.$code.' '.$_status[$code]);
			// 确保FastCGI模式下正常
			header('Status:'.$code.' '.$_status[$code]);
		}
	}

	/**
	 * 加密解密authcode
	 * $string：字符串
	 * $operation：DECODE表示解密，其它表示加密
	 * $key：密匙
	 * $expiry：密文有效期
	 * 
	 */

	static function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {
		$ckey_length = 4;
		$key = md5($key ? $key : $GLOBALS['discuz_auth_key']);
		$keya = md5(substr($key, 0, 16));
		$keyb = md5(substr($key, 16, 16));
		$keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length):
		substr(md5(microtime()), -$ckey_length)) : '';
		$cryptkey = $keya.md5($keya.$keyc);
		$key_length = strlen($cryptkey);
		$string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) :
		sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
		$string_length = strlen($string);
		$result = '';
		$box = range(0, 255);
		$rndkey = array();
		for($i = 0; $i <= 255; $i++) {
			$rndkey[$i] = ord($cryptkey[$i % $key_length]);
		}
		for($j = $i = 0; $i < 256; $i++) {
			$j = ($j + $box[$i] + $rndkey[$i]) % 256;
			$tmp = $box[$i];
			$box[$i] = $box[$j];
			$box[$j] = $tmp;
		}
		for($a = $j = $i = 0; $i < $string_length; $i++) {
			$a = ($a + 1) % 256;
			$j = ($j + $box[$a]) % 256;
			$tmp = $box[$a];
			$box[$a] = $box[$j];
			$box[$j] = $tmp;
			$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
		}
		if($operation == 'DECODE') {
			if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) &&
			substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
				return substr($result, 26);
			} else {
				return '';
			}
		} else {
			return $keyc.str_replace('=', '', base64_encode($result));
		}
	}

	/**
	 * 加密encrypt
	 * $string：需要加密解密的字符串
	 * $operation：判断是加密还是解密，E表示加密，D表示解密
	 * 
	 * $key：密匙
	 * 
	 */
	static function encrypt($string,$operation,$key=''){
		$key=md5($key);
		$key_length=strlen($key);
		$string=$operation=='D'?base64_decode($string):substr(md5($string.$key),0,8).$string;
		$string_length=strlen($string);
		$rndkey=$box=array();
		$result='';
		for($i=0;$i<=255;$i++){
			$rndkey[$i]=ord($key[$i%$key_length]);
			$box[$i]=$i;
		}
		for($j=$i=0;$i<256;$i++){
			$j=($j+$box[$i]+$rndkey[$i])%256;
			$tmp=$box[$i];
			$box[$i]=$box[$j];
			$box[$j]=$tmp;
		}
		for($a=$j=$i=0;$i<$string_length;$i++){
			$a=($a+1)%256;
			$j=($j+$box[$a])%256;
			$tmp=$box[$a];
			$box[$a]=$box[$j];
			$box[$j]=$tmp;
			$result.=chr(ord($string[$i])^($box[($box[$a]+$box[$j])%256]));
		}
		if($operation=='D'){
			if(substr($result,0,8)==substr(md5(substr($result,8).$key),0,8)){
				return substr($result,8);
			}else{
				return'';
			}
		}else{
			return str_replace('=','',base64_encode($result));
		}
	}


	/**
	 * session 管理
	 * $name 名称
	 * $data 名称值 ,$data值等于null 时删除session
	 * 返回数组形式
	 */
	static  function session($name, $data){
		if (isset($name)) {
			$_SESSION[$name] = $data;
		}
		if ($data == null) {
			unset($_SESSION[$name]);
		}
		return $_SESSION;
	}

	static function urlencode($str) {
		return urlencode($str);
	}

	static function urldecode($str) {
		return urldecode($str);
	}
	function getUriInfo() {
		$uri = $_SERVER['REQUEST_URI'];
		return pathinfo($uri);
	}


	static function setDebug($flag = null) {
		static $open = false;
		if (isset($flag)) {
			if ($flag === 0) {
				$open = false;
			} else if ($flag === 1) {
				$open = true;
			}
			return $open;
		} else {
			return $open;
		}
	}
	/**
	 * 
	 * @param unknown_type $var 变量
	 * @param unknown_type $flag 是否强制输出
	 * @param unknown_type $format  输出格式
	 */

	static function Debug( $var, $flag = 0, $format =0) {
		$output = 0;
		if (defined('DEBUG_MODE') && DEBUG_MODE ) {
			if (isset($_GET['mode']) && $_GET['mode'] == 'debug') {
				$output = 1;
			} else if ($flag == 1) {
				$output = 1;
			}
			// 强制输出:post
			if(self::setDebug()) {
				$output = 1;
			}
		}

		if ($output) {
			switch ($format) {
				case 1:
					var_dump($var);
					break;
				case 2:
					echo $var;
					break;
				default:
					print_r($var);
			}
		}
	}



	static function error_log($message, $error_type = "error" )
	{


		$log_threshold =  LOG_THRESHOLD;
		$log_file      =  LOG_FIlE;
		$log_email     =  LOG_EMAIL;
		$log_file     .= date('Y-m-d') . '-'  . $error_type . '.log';

		if (is_array($message)) {
			$message = var_export($message, true);
		}


		//打开日志开关
		if ($log_threshold == 0) {
			return;
		}


		$message = date('Y-m-d H:i:s') . ' : ' .  $message;
		$message = "-------------------------------------------------------------------------------------------\n" . $message . "\n\n";
		if ($log_threshold == 1 ) { // 文件
			$rtn = error_log ($message, 3, $log_file);
			return $rtn;
		}

		if ($log_threshold == 2) { // email
			$rtn = error_log ($message, 1, $log_email);
			return $rtn;
		}

		if ($log_threshold == 3) { // 控制台
			$rtn = error_log ($message, 0 );
			return $rtn;
		}
		//$rtn = error_log($message, 0);
		return $rtn;
	}

}



