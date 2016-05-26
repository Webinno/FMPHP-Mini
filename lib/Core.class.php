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
 * 获取和设置配置参数 支持批量定义
 * @param string|array $name 配置变量
 * @param mixed $value 配置值
 * @param mixed $default 默认值
 * @return mixed
 */

function C($name = null, $value = null, $default = null) {
	static $_config = array ();
	// 无参数时获取所有
	if (empty ( $name )) {
		return $_config;
	}
	// 优先执行设置获取或赋值
	if (is_string ( $name )) {
		if (! strpos ( $name, '.' )) {
			$name = strtoupper ( $name );
			if (is_null ( $value ))
			return isset ( $_config [$name] ) ? $_config [$name] : $default;
			$_config [$name] = $value;
			return;
		}
		// 二维数组设置和获取支持
		$name = explode ( '.', $name );
		$name [0] = strtoupper ( $name [0] );
		if (is_null ( $value ))
		return isset ( $_config [$name [0]] [$name [1]] ) ? $_config [$name [0]] [$name [1]] : $default;
		$_config [$name [0]] [$name [1]] = $value;
		return;
	}
	// 批量设置
	if (is_array ( $name )) {
		$_config = array_merge ( $_config, array_change_key_case ( $name, CASE_UPPER ) );
		return;
	}
	return null; // 避免非法参数
}

/**
	 * 浏览器友好的变量输出
	 * 
	 * @param mixed $var变量
	 * @param boolean $echo是否输出 默认为True 如果为false 则返回输出字符串
	 * @param string $label标签 默认为空
	 * @param boolean $strict是否严谨 默认为true
	 * @return void string。。。。。。测试用
	 */
function p($var, $echo = true, $label = null, $strict = true) {
	$label = ($label === null) ? '' : rtrim ( $label ) . ' ';
	if (! $strict) {
		if (ini_get ( 'html_errors' )) {
			$output = print_r ( $var, true );
			$output = '<pre>' . $label . htmlspecialchars ( $output, ENT_QUOTES ) . '</pre>';
		} else {
			$output = $label . print_r ( $var, true );
		}
	} else {
		ob_start ();
		var_dump ( $var );
		$output = ob_get_clean ();
		if (! extension_loaded ( 'xdebug' )) {
			$output = preg_replace ( '/\]\=\>\n(\s+)/m', '] => ', $output );
			$output = '<pre>' . $label . htmlspecialchars ( $output, ENT_QUOTES ) . '</pre>';
		}
	}
	if ($echo) {
		echo ($output);
		return null;
	} else
	return $output;
}

/**
	 * 设置和获取统计数据
	 * 使用方法:
	 * <code>
	 * N('db',1); // 记录数据库操作次数
	 * N('read',1); // 记录读取次数
	 * echo N('db'); // 获取当前页面数据库的所有操作次数
	 * echo N('read'); // 获取当前页面读取次数
	 * </code>
	 * @param string $key标识位置
	 * @param integer $step步进值
	 * @return mixed
	 */
function N($key, $step = 0, $save = false) {
	static $_num = array ();
	if (! isset ( $_num [$key] )) {
		$_num [$key] = (false !== $save) ? S ( 'N_' . $key ) : 0;
	}
	if (empty ( $step ))
	return $_num [$key];
	else
	$_num [$key] = $_num [$key] + ( int ) $step;
	if (false !== $save) { // 保存结果
		S ( 'N_' . $key, $_num [$key], $save );
	}
}
/**
	 * 获取输入参数 支持过滤和默认值
	 * 使用方法:
	 * <code>
	 * I('id',0); 获取id参数 自动判断get或者post
	 * I('post.name','','htmlspecialchars'); 获取$_POST['name']
	 * I('get.'); 获取$_GET
	 * </code>
	 * @param string $name变量的名称 支持指定类型
	 * @param mixed $default不存在的时候默认值
	 * @param mixed $filter参数过滤方法
	 * @param mixed $datas要获取的额外数据源
	 * @return mixed
	 */
function I($name, $default = '', $filter = null, $datas = null) {
	if (strpos ( $name, '.' )) { // 指定参数来源
		list ( $method, $name ) = explode ( '.', $name, 2 );
	} else { // 默认为自动判断
		$method = 'param';
	}
	switch (strtolower ( $method )) {
		case 'get' :
			$input = & $_GET;
			break;
		case 'post' :
			$input = & $_POST;
			break;
		case 'put' :
			parse_str ( file_get_contents ( 'php://input' ), $input );
			break;
		case 'param' :
			switch ($_SERVER ['REQUEST_METHOD']) {
				case 'POST' :
					$input = $_POST;
					break;
				case 'PUT' :
					parse_str ( file_get_contents ( 'php://input' ), $input );
					break;
				default :
					$input = $_GET;
			}
			break;
		case 'path' :
			$input = array ();
			if (! empty ( $_SERVER ['PATH_INFO'] )) {
				$depr = C ( 'URL_PATHINFO_DEPR' );
				$input = explode ( $depr, trim ( $_SERVER ['PATH_INFO'], $depr ) );
			}
			break;
		case 'request' :
			$input = & $_REQUEST;
			break;
		case 'session' :
			$input = & $_SESSION;
			break;
		case 'cookie' :
			$input = & $_COOKIE;
			break;
		case 'server' :
			$input = & $_SERVER;
			break;
		case 'globals' :
			$input = & $GLOBALS;
			break;
		case 'data' :
			$input = & $datas;
			break;
		default :
			return NULL;
	}
	if ('' == $name) { // 获取全部变量
		$data = $input;
		array_walk_recursive ( $data, 'filter_exp' );
		$filters = isset ( $filter ) ? $filter : C ( 'DEFAULT_FILTER' );
		if ($filters) {
			if (is_string ( $filters )) {
				$filters = explode ( ',', $filters );
			}
			foreach ( $filters as $filter ) {
				$data = array_map_recursive ( $filter, $data ); // 参数过滤
			}
		}
	} elseif (isset ( $input [$name] )) { // 取值操作
		$data = $input [$name];
		is_array ( $data ) && array_walk_recursive ( $data, 'filter_exp' );
		$filters = isset ( $filter ) ? $filter : C ( 'DEFAULT_FILTER' );
		if ($filters) {
			if (is_string ( $filters )) {
				$filters = explode ( ',', $filters );
			} elseif (is_int ( $filters )) {
				$filters = array (
				$filters
				);
			}

			foreach ( $filters as $filter ) {
				if (function_exists ( $filter )) {
					$data = is_array ( $data ) ? array_map_recursive ( $filter, $data ) : $filter ( $data ); // 参数过滤
				} else {
					$data = filter_var ( $data, is_int ( $filter ) ? $filter : filter_id ( $filter ) );
					if (false === $data) {
						return isset ( $default ) ? $default : NULL;
					}
				}
			}
		}
	} else { // 变量默认值
		$data = isset ( $default ) ? $default : NULL;
	}
	return $data;
}
function array_map_recursive($filter, $data) {
	$result = array ();
	foreach ( $data as $key => $val ) {
		$result [$key] = is_array ( $val ) ? array_map_recursive ( $filter, $val ) : call_user_func ( $filter, $val );
	}
	return $result;
}
/**
	 * 区分大小写的文件存在判断
	 * @param string $filename文件地址
	 * @return boolean
	 */
function file_exists_case($filename) {
	if (is_file ( $filename )) {
		if (IS_WIN && APP_DEBUG) {
			if (basename ( realpath ( $filename ) ) != basename ( $filename ))
			return false;
		}
		return true;
	}
	return false;
}

/**
 * 页面跳转
 * 
 */

function jump($url, $msg='',$time='0'){
	$url = Security::redirect_filter($url);

	//用html方法实现页面延迟跳转
	echo "<html>";
	echo "<head>";
	echo "<meta http-equiv=refresh content=$time;url='$url'>";
	echo "</head>";
	echo "<body>";
	echo $msg."</br>";
	echo "页面将在" .$time. "秒后自动跳转...</br>";
	echo "<a href=".$url.">如果没有跳转，请点这里跳转</a>";
	echo "</body>";
	echo "</html>";
}
/**
 * HEAD跳转
 *
 */
function head_jump($url){
	$url = Security::redirect_filter($url);
	header("Location: ".$url);
	exit;
}
/**
 * 去除字符串中的空格
 * @param unknown $str
 * @return mixed
 */
function qukong($str){
	$qian=array(" ","　","\t","\n","\r");
	$hou=array("","","","","");
	return str_replace($qian,$hou,$str);
}


//加载
function T($class){
	static $tables = array();

	if (empty($class)) {
		return false;
	}
	if (isset($tables[$class]) && is_object($tables[$class])) {
		return $tables[$class];
	} else {
		$DB =  Common::getDB();
		//print_r($DB);
		//var_dump($class);

		$tables[$class] = new $class($DB);
	}

	return $tables[$class];
}
/*
* 读取二进制文件转成base64
*/
function readCert($publicKeyContent){
	//如果证书内容中没有包含：-----BEGIN CERTIFICATE-----，需要添加证书标记的头尾
	if(strpos($publicKeyContent,'-----BEGIN CERTIFICATE-----')===false)
	{
		$publicKeyContent='-----BEGIN CERTIFICATE-----'.PHP_EOL
		.chunk_split(base64_encode($publicKeyContent), 64, PHP_EOL)
		.'-----END CERTIFICATE-----'.PHP_EOL;
	}
	return  $publicKeyContent;
}



/**
     * 字符截取
     * @param $string
     * @param $length
     * @param $dot
     */
 function str_cut($string, $length, $dot = '...')
{
	$result = '';
	$string = html_entity_decode(trim(strip_tags($string)), ENT_QUOTES, 'UTF-8');
	$strlen = strlen($string);
	for ($i = 0; (($i < $strlen) && ($length > 0)); $i++){
		if ($number = strpos(str_pad(decbin(ord(substr($string, $i, 1))), 8, '0', STR_PAD_LEFT), '0')){
			if ($length < 1.0){
				break;
			}
			$result .= substr($string, $i, $number);
			$length -= 1.0;
			$i += $number - 1;
		}else{
			$result .= substr($string, $i, 1);
			$length -= 0.5;
		}
	}
	$result = htmlspecialchars($result, ENT_QUOTES, 'UTF-8');
	if ($i < $strlen){
		$result .= $dot;
	}
	return $result;
}

/**
	 * 获取utf8字符串长度
	 *
	 * @param string $string 字符串
	 *
	 * @retur intval 长度
	 */
public static function str_length($string = null) {
	preg_match_all("/./us", $string, $match);
	return count($match[0]);
}



/**
	 * 数组排序
	 *
	 */
function array_sort($arr,$keys,$type='asc'){
	$keysvalue = $new_array = array();
	foreach ($arr as $k=>$v){
		$keysvalue[$k] = $v[$keys];
	}
	if($type == 'asc'){
		asort($keysvalue);
	}else{
		arsort($keysvalue);
	}
	reset($keysvalue);
	foreach ($keysvalue as $k=>$v){
		$new_array[$k] = $arr[$k];
	}
	return $new_array;
}

/**
	 * 获取客户端IP地址
	 */
function get_client_ip() {
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
	 * 判断当前人类型
	 *
	 * @param string $user_code  类型参数
	 *
	 * return int  1买卖 2租赁 3综合 4店经理 5区经理 6管理员
	 */
/*
public static function get_user_code($user_code = '') {
if(empty($user_code)){
$user_code = Yii::app()->session['currentPositionCode'];
}
//OPER_01_03  (综合经纪人)   OPER_01 买卖经纪人  OPER_01_02 (租赁经纪人)  OPER_02  (店经理) DEFAULT_POSITION (客服)
if($user_code == 'OPER_01'){
$result = '1';
}else if($user_code == 'OPER_01_02'){
$result = '2';
}else if($user_code == 'OPER_01_03'){
$result = '3';
}else if($user_code == 'OPER_02'){
$result = '4';
}else if($user_code == 'DEFAULT_POSITION' || $user_code == 'FUNC_CRM_01_01' || $user_code == 'FUNC_ERA_01_01'){
$result = '5';
}else{
$result = '99';
}
return $result;
}
*/


/**
	 * 高亮字符串
	 * @param $string 待高亮处理字符串
	 * @param $highlight_left  需要高亮的字符串左
	 * @param $highlight_right 需要高亮的字符串右
	 * @param $class  高亮的样式
	 * @
	 */
function highlight ($string, $highlight_left, $highlight_right, $class='black'){
	if (!empty ($highlight_left)) {
		$highlightKeywords = preg_split ("/[\s,]+/", $highlight_left);
		if (isset ($highlightKeywords)) {
			foreach ($highlightKeywords as &$kw){
				$kw = preg_quote ($kw);
			}
			$highlightKeywords = array_unique ($highlightKeywords);
			$highlightPattern = "-(" . join ("|", $highlightKeywords) . ")-i";
			//$highlightReplacement = "<u>$1</u>";
			$highlightReplacement = "$1<strong class=\"".$class."\">";
			$string = preg_replace ($highlightPattern, $highlightReplacement, $string);
		}
	}
	if (!empty ($highlight_right)) {
		$highlightKeywords = preg_split ("/[\s,]+/", $highlight_right);
		if (isset ($highlightKeywords)) {
			foreach ($highlightKeywords as &$kw){
				$kw = preg_quote ($kw);
			}
			$highlightKeywords = array_unique ($highlightKeywords);
			$highlightPattern = "-(" . join ("|", $highlightKeywords) . ")-i";
			//$highlightReplacement = "<u>$1</u>";
			$highlightReplacement = "</strong>$1";
			$string = preg_replace ($highlightPattern, $highlightReplacement, $string);
		}
	}
	return $string;
}

/**
	 * 获取图片地址
	 * @param string $imageurl 图片原始路径
	 * @param int    $new_type 新图大小 1小 80x60, 2中 200x150, 3中大 400x300, 4大 800x600
     * @param int    $old_type 老图大小 1小 thumb120_  2大 wm_
	 *
     * return string
	 */
function getImageurl($imageurl, $new_type=1, $old_type=1){
	if($new_type == 1){
		$wei = '.80x60.jpg';
	}else if($new_type == 2){
		$wei = '.200x150.jpg';
	}else if($new_type == 3){
		$wei = '.400x300.jpg';
	}else if($new_type == 4){
		$wei = '.800x600.jpg';
	}

	if($old_type == 1){
		$old_wei = 'thumb120_';
		$luo_wei = '.200x150.jpg';
	}else if($old_type == 2){
		$old_wei = 'wm_';
		$luo_wei = '.800x600.jpg';
	}
	if(!empty($imageurl)){
		$is_loupan = strpos($imageurl, "hdpic");
		if($is_loupan > 0){
			$image_new_url = Yii::app()->params['imgoldPrefix'].$imageurl.$luo_wei;
		}else{
			$is_new = strpos($imageurl, "SalesMgr-Web");
			if($is_new !== 0){
				$image_new_url = Yii::app()->params['imgoldPrefix'].$imageurl.$wei;
			}else{
				$old_file = str_replace("###", $old_wei, $imageurl);
				$image_new_url = Yii::app()->params['imgoldPrefix'].$old_file;
			}
		}
	}else{
		$image_new_url = "/images/fyxq_skt.jpg";
	}

	return $image_new_url;
}
